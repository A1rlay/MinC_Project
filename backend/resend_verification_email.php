<?php
/**
 * Resend Verification Email Endpoint
 * Path: backend/resend_verification_email.php
 * 
 * Allows users to request a new verification email if the first one expires
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../database/connect_database.php';
require_once __DIR__ . '/../library/EmailService.php';
require_once __DIR__ . '/../library/TokenGenerator.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$email = isset($input['email']) ? filter_var($input['email'], FILTER_SANITIZE_EMAIL) : '';

if (empty($email)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Email address is required'
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

try {
    // Find user by email
    $stmt = $pdo->prepare("
        SELECT user_id, fname, lname, is_email_verified 
        FROM users 
        WHERE email = :email 
        LIMIT 1
    ");
    
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // For security, don't reveal if email exists
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No account found with this email address'
        ]);
        exit;
    }
    
    // Check if email is already verified
    if ($user['is_email_verified'] == 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'This email address is already verified. You can login to your account.'
        ]);
        exit;
    }
    
    // Check if there's a recent unused token (avoid spam)
    $recentToken = $pdo->prepare("
        SELECT token_id, created_at 
        FROM email_verification_tokens 
        WHERE user_id = :user_id 
        AND is_used = 0 
        AND expires_at > NOW()
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    
    $recentToken->execute([':user_id' => $user['user_id']]);
    $existingToken = $recentToken->fetch(PDO::FETCH_ASSOC);
    
    // If a valid token exists and was created less than 5 minutes ago, don't send new one
    if ($existingToken) {
        $timeSinceCreation = time() - strtotime($existingToken['created_at']);
        if ($timeSinceCreation < 300) { // 5 minutes
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => 'Please wait before requesting a new verification email. A verification email was recently sent.',
                'retryAfter' => 300 - $timeSinceCreation
            ]);
            exit;
        }
    }
    
    // Invalidate previous tokens
    $invalidateStmt = $pdo->prepare("
        UPDATE email_verification_tokens 
        SET expires_at = NOW() 
        WHERE user_id = :user_id 
        AND is_used = 0 
        AND expires_at > NOW()
    ");
    
    $invalidateStmt->execute([':user_id' => $user['user_id']]);
    
    // Generate new verification token
    $token = TokenGenerator::generateToken(32);
    $tokenHash = TokenGenerator::hashToken($token);
    $expiresAt = date('Y-m-d H:i:s', time() + (24 * 60 * 60)); // 24 hours
    
    // Store new verification token
    $tokenStmt = $pdo->prepare("
        INSERT INTO email_verification_tokens (user_id, token, token_hash, email, expires_at) 
        VALUES (:user_id, :token, :token_hash, :email, :expires_at)
    ");
    
    $tokenStmt->execute([
        ':user_id' => $user['user_id'],
        ':token' => $token,
        ':token_hash' => $tokenHash,
        ':email' => $email,
        ':expires_at' => $expiresAt
    ]);
    
    // Build verification link
    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $verificationLink = $baseUrl . '/backend/verify_email.php?token=' . urlencode($token);
    
    // Send verification email
    $emailService = new EmailService();
    $emailSent = $emailService->sendVerificationEmail(
        $email,
        $user['fname'] . ' ' . $user['lname'],
        $verificationLink,
        $token
    );
    
    // Log to audit trail
    $auditStmt = $pdo->prepare("
        INSERT INTO audit_trail 
        (user_id, session_username, action, entity_type, entity_id, old_value, new_value, change_reason, ip_address, user_agent, system_id) 
        VALUES 
        (:user_id, :session_username, :action, :entity_type, :entity_id, :old_value, :new_value, :change_reason, :ip_address, :user_agent, :system_id)
    ");
    
    $auditStmt->execute([
        ':user_id' => $user['user_id'],
        ':session_username' => $user['fname'] . ' ' . $user['lname'],
        ':action' => 'resend_verification_email',
        ':entity_type' => 'user',
        ':entity_id' => $user['user_id'],
        ':old_value' => null,
        ':new_value' => json_encode(['email' => $email, 'email_sent' => $emailSent]),
        ':change_reason' => 'User requested verification email resend',
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ':system_id' => 'minc_system'
    ]);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $emailSent 
            ? 'Verification email has been sent to ' . htmlspecialchars($email) . '. Please check your inbox.'
            : 'If an account with this email exists, a verification email will be sent shortly.',
        'email_sent' => $emailSent
    ]);
    
} catch (PDOException $e) {
    error_log("Resend verification email error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request. Please try again later.'
    ]);
}

// Close connection
$pdo = null;
?>
