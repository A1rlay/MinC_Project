<?php
/**
 * Registration Backend
 * Path: C:\xampp\htdocs\MinC_Project\backend\register.php
 * Handles customer registration (Consumer level)
 */

session_start();

// Include database connection
require_once __DIR__ . '/../database/connect_database.php';

// Set response header to JSON
header('Content-Type: application/json');

// Function to log audit trail
function logAuditTrail($pdo, $userId, $username, $action, $entityType, $entityId, $oldValue = null, $newValue = null, $changeReason = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO audit_trail 
            (user_id, session_username, action, entity_type, entity_id, old_value, new_value, change_reason, ip_address, user_agent, system_id) 
            VALUES 
            (:user_id, :session_username, :action, :entity_type, :entity_id, :old_value, :new_value, :change_reason, :ip_address, :user_agent, :system_id)
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':session_username' => $username,
            ':action' => $action,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':old_value' => $oldValue ? json_encode($oldValue) : null,
            ':new_value' => $newValue ? json_encode($newValue) : null,
            ':change_reason' => $changeReason,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':system_id' => 'minc_system'
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Audit trail error: " . $e->getMessage());
        return false;
    }
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['fname']) || !isset($input['lname']) || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required'
    ]);
    exit;
}

$fname = trim($input['fname']);
$lname = trim($input['lname']);
$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
$password = $input['password'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters long'
    ]);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered'
        ]);
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if email verification system is set up (email_verification_tokens table exists)
    $checkTableStmt = $pdo->query("SHOW TABLES LIKE 'email_verification_tokens'");
    $emailVerificationEnabled = $checkTableStmt->rowCount() > 0;
    
    if ($emailVerificationEnabled) {
        // Load email service and token generator
        require_once __DIR__ . '/../library/EmailService.php';
        require_once __DIR__ . '/../library/TokenGenerator.php';
    }
    
    // Insert new user as Consumer (user_level_id = 4)
    if ($emailVerificationEnabled) {
        // Email verification is enabled - user is unverified
        $stmt = $pdo->prepare("
            INSERT INTO users (fname, lname, email, password, user_level_id, user_status, is_email_verified, created_at) 
            VALUES (:fname, :lname, :email, :password, 4, 'active', 0, NOW())
        ");
    } else {
        // Fallback: Email verification not set up - user is verified immediately
        $stmt = $pdo->prepare("
            INSERT INTO users (fname, lname, email, password, user_level_id, user_status, created_at) 
            VALUES (:fname, :lname, :email, :password, 4, 'active', NOW())
        ");
    }
    
    $stmt->execute([
        ':fname' => $fname,
        ':lname' => $lname,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);
    
    $newUserId = $pdo->lastInsertId();
    $emailSent = false;
    
    // If email verification is enabled, generate and send verification token
    if ($emailVerificationEnabled) {
        try {
            // Generate verification token
            $token = TokenGenerator::generateToken(32);
            $tokenHash = TokenGenerator::hashToken($token);
            $expiresAt = date('Y-m-d H:i:s', time() + (24 * 60 * 60)); // 24 hours
            
            // Store verification token in database
            $tokenStmt = $pdo->prepare("
                INSERT INTO email_verification_tokens (user_id, token, token_hash, email, expires_at) 
                VALUES (:user_id, :token, :token_hash, :email, :expires_at)
            ");
            
            $tokenStmt->execute([
                ':user_id' => $newUserId,
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
                $fname . ' ' . $lname,
                $verificationLink,
                $token
            );
        } catch (Exception $e) {
            // Log email/token error but don't fail registration
            error_log("Email verification token/send error: " . $e->getMessage());
            $emailSent = false;
        }
    }
    
    
    // Log registration in audit trail
    logAuditTrail(
        $pdo,
        $newUserId,
        $fname,
        'create',
        'user',
        $newUserId,
        null,
        [
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email,
            'user_level_id' => 4,
            'user_status' => 'active',
            'is_email_verified' => $emailVerificationEnabled ? 0 : 1,
            'email_verification_enabled' => $emailVerificationEnabled,
            'email_sent' => $emailSent
        ],
        'Customer self-registration' . ($emailVerificationEnabled ? ' with email verification' : '')
    );
    
    // Return success response
    if ($emailVerificationEnabled) {
        $message = $emailSent 
            ? 'Registration successful! Please check your email to verify your account.'
            : 'Registration successful! A verification email will be sent shortly.';
        $emailVerified = false;
    } else {
        $message = 'Registration successful! You can now login.';
        $emailVerified = true;
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'user_id' => $newUserId,
        'email_verified' => $emailVerified,
        'email_verification_enabled' => $emailVerificationEnabled,
        'email_sent' => $emailSent
    ]);
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during registration. Please try again later.'
    ]);
}

// Close connections
closeConnections();
?>