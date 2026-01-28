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
    
    // Insert new user as Consumer (user_level_id = 4)
    $stmt = $pdo->prepare("
        INSERT INTO users (fname, lname, email, password, user_level_id, user_status, created_at) 
        VALUES (:fname, :lname, :email, :password, 4, 'active', NOW())
    ");
    
    $stmt->execute([
        ':fname' => $fname,
        ':lname' => $lname,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);
    
    $newUserId = $pdo->lastInsertId();
    
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
            'user_status' => 'active'
        ],
        'Customer self-registration'
    );
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please login.',
        'user_id' => $newUserId
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