<?php
/**
 * Registration Backend
 * Path: C:\xampp\htdocs\MinC_Project\backend\register.php
 * Starts customer registration with email OTP flow.
 */

session_start();

// Include database connection
require_once __DIR__ . '/../database/connect_database.php';

// Set response header to JSON
header('Content-Type: application/json');

function normalizeName($value) {
    $value = preg_replace('/\s+/', ' ', trim((string)$value));
    return ucwords(strtolower($value), " -'");
}

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
if (!isset($input['fname']) || !isset($input['lname']) || !isset($input['email']) || !isset($input['address'])) {
    echo json_encode([
        'success' => false,
        'message' => 'First name, last name, email, and address are required'
    ]);
    exit;
}

$fname = trim($input['fname']);
$lname = trim($input['lname']);
$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
$address = trim((string)($input['address'] ?? ''));

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

if ($fname === '' || $lname === '') {
    echo json_encode([
        'success' => false,
        'message' => 'First name and last name are required'
    ]);
    exit;
}

$fname = normalizeName($fname);
$lname = normalizeName($lname);

if ($address === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Delivery address is required'
    ]);
    exit;
}

try {
    // Email verification table must exist for OTP flow
    $checkTableStmt = $pdo->query("SHOW TABLES LIKE 'email_verification_tokens'");
    $emailVerificationEnabled = $checkTableStmt->rowCount() > 0;

    if (!$emailVerificationEnabled) {
        echo json_encode([
            'success' => false,
            'message' => 'Email verification is not configured. Please run SETUP_DATABASE.sql first.'
        ]);
        exit;
    }

    $checkColumnStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_email_verified'");
    if ($checkColumnStmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => "Database is missing 'is_email_verified' column. Please run SETUP_DATABASE.sql first."
        ]);
        exit;
    }

    $userIdColumnStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'user_id'");
    $userIdColumn = $userIdColumnStmt->fetch(PDO::FETCH_ASSOC);
    if (!$userIdColumn || stripos((string)($userIdColumn['Extra'] ?? ''), 'auto_increment') === false) {
        echo json_encode([
            'success' => false,
            'message' => "Database schema issue: users.user_id must be AUTO_INCREMENT. Please run SETUP_DATABASE.sql."
        ]);
        exit;
    }

    $tokenIdColumnStmt = $pdo->query("SHOW COLUMNS FROM email_verification_tokens LIKE 'token_id'");
    $tokenIdColumn = $tokenIdColumnStmt->fetch(PDO::FETCH_ASSOC);
    if (!$tokenIdColumn || stripos((string)($tokenIdColumn['Extra'] ?? ''), 'auto_increment') === false) {
        echo json_encode([
            'success' => false,
            'message' => "Database schema issue: email_verification_tokens.token_id must be AUTO_INCREMENT. Please run SETUP_DATABASE.sql."
        ]);
        exit;
    }

    require_once __DIR__ . '/../library/EmailService.php';
    require_once __DIR__ . '/../library/TokenGenerator.php';

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT user_id, user_level_id, is_email_verified FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    $newUserId = null;
    $isExistingPending = false;

    if ($existingUser) {
        // Allow continuation only for pending customer registration
        if ((int)$existingUser['user_level_id'] === 4 && (int)$existingUser['is_email_verified'] === 0) {
            $isExistingPending = true;
            $newUserId = (int)$existingUser['user_id'];

            $updatePendingStmt = $pdo->prepare("
                UPDATE users 
                SET fname = :fname, lname = :lname, address = :address, user_status = 'inactive', updated_at = NOW()
                WHERE user_id = :user_id
            ");
            $updatePendingStmt->execute([
                ':fname' => $fname,
                ':lname' => $lname,
                ':address' => $address,
                ':user_id' => $newUserId
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Email already registered'
            ]);
            exit;
        }
    } else {
        // Create pending account with temporary random password
        $temporaryPassword = password_hash(TokenGenerator::generateToken(24), PASSWORD_DEFAULT);

        $insertStmt = $pdo->prepare("
            INSERT INTO users (fname, lname, email, password, address, user_level_id, user_status, is_email_verified, created_at, updated_at) 
            VALUES (:fname, :lname, :email, :password, :address, 4, 'inactive', 0, NOW(), NOW())
        ");

        $insertStmt->execute([
            ':fname' => $fname,
            ':lname' => $lname,
            ':email' => $email,
            ':password' => $temporaryPassword,
            ':address' => $address
        ]);

        $newUserId = (int)$pdo->lastInsertId();
        if ($newUserId <= 0) {
            $lookupStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email ORDER BY user_id DESC LIMIT 1");
            $lookupStmt->execute([':email' => $email]);
            $newUserId = (int)($lookupStmt->fetchColumn() ?: 0);
        }
    }

    if ($newUserId <= 0) {
        throw new Exception('User ID generation failed. users.user_id may not be AUTO_INCREMENT.');
    }

    // Invalidate previous active OTP codes for this user
    $invalidateStmt = $pdo->prepare("
        UPDATE email_verification_tokens 
        SET expires_at = NOW(), is_used = 1, verified_at = NOW()
        WHERE user_id = :user_id 
          AND is_used = 0
          AND expires_at > NOW()
    ");
    $invalidateStmt->execute([':user_id' => $newUserId]);

    // Generate OTP (6 digits), valid for 10 minutes
    $otpCode = TokenGenerator::generateVerificationCode();
    // Keep OTP user-friendly (6 digits) but store a unique token string to satisfy DB uniqueness.
    $tokenStorage = $otpCode . '-' . substr(TokenGenerator::generateToken(8), 0, 16);
    $tokenHash = TokenGenerator::hashToken($otpCode);
    $expiresAt = date('Y-m-d H:i:s', time() + (10 * 60));

    $tokenStmt = $pdo->prepare("
        INSERT INTO email_verification_tokens (user_id, token, token_hash, email, expires_at) 
        VALUES (:user_id, :token, :token_hash, :email, :expires_at)
    ");

    $tokenStmt->execute([
        ':user_id' => $newUserId,
        ':token' => $tokenStorage,
        ':token_hash' => $tokenHash,
        ':email' => $email,
        ':expires_at' => $expiresAt
    ]);

    // Send OTP email
    $emailService = new EmailService();
    $emailSent = $emailService->sendOtpVerificationEmail(
        $email,
        trim($fname . ' ' . $lname),
        $otpCode,
        10
    );

    // Keep pending registration context in session for smooth password step
    $_SESSION['registration_pending_email'] = $email;
    $_SESSION['registration_pending_user_id'] = $newUserId;

    // Log registration start in audit trail
    logAuditTrail(
        $pdo,
        $newUserId,
        $fname,
        $isExistingPending ? 'registration_otp_resent' : 'registration_started',
        'user',
        $newUserId,
        null,
        [
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email,
            'address' => $address,
            'otp_sent' => $emailSent
        ],
        $isExistingPending ? 'Pending registration continued and OTP resent' : 'Customer registration started with OTP verification'
    );

    echo json_encode([
        'success' => true,
        'message' => $emailSent
            ? 'Verification code sent. Please check your email.'
            : 'Account created, but OTP email could not be sent right now. Please try resend.',
        'email' => $email,
        'user_id' => $newUserId,
        'email_sent' => $emailSent,
        'otp_expires_in_seconds' => 600
    ]);
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());

    if (($_SERVER['REMOTE_ADDR'] ?? '') === '127.0.0.1' || ($_SERVER['REMOTE_ADDR'] ?? '') === '::1') {
        echo json_encode([
            'success' => false,
            'message' => 'Registration database error: ' . $e->getMessage()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred during registration. Please try again later.'
        ]);
    }
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close connections
closeConnections();
?>
