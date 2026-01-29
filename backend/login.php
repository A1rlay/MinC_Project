<?php
/**
 * Login Authentication Backend
 * Path: C:\xampp\htdocs\MinC_Project\backend\login.php
 * Handles user authentication and audit trail logging
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
if (!isset($input['email']) || !isset($input['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
    exit;
}

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

try {
    // Fetch user from database with user level information
    $stmt = $pdo->prepare("
        SELECT 
            u.user_id, 
            u.fname, 
            u.mname, 
            u.lname, 
            u.email, 
            u.password, 
            u.username,
            u.user_status,
            u.user_level_id,
            ul.user_type_name
        FROM users u
        INNER JOIN user_levels ul ON u.user_level_id = ul.user_level_id
        WHERE u.email = :email
        LIMIT 1
    ");
    
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user exists
    if (!$user) {
        // Log failed login attempt
        error_log("Failed login attempt for email: " . $email);
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }
    
    // Check if user is active
    if ($user['user_status'] !== 'active') {
        echo json_encode([
            'success' => false,
            'message' => 'Your account has been deactivated. Please contact administrator.'
        ]);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Log failed login attempt
        error_log("Failed login attempt for user ID: " . $user['user_id']);
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }
    
// Verify password
if (!password_verify($password, $user['password'])) {
    // Log failed login attempt
    error_log("Failed login attempt for user ID: " . $user['user_id']);
    
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
    exit;
}

// MODIFIED: Determine if user is admin or customer
// IT Personnel (1), Owner (2), Manager (3) -> Admin/Dashboard access
// Consumer (4) -> Customer/Landing page access
$isAdmin = in_array($user['user_level_id'], [1, 2, 3]);

// Login successful - Create session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['email'] = $user['email'];
$_SESSION['fname'] = $user['fname'];
$_SESSION['mname'] = $user['mname'];
$_SESSION['lname'] = $user['lname'];
$_SESSION['username'] = $user['username'] ?? $user['fname'];
$_SESSION['user_level_id'] = $user['user_level_id'];
$_SESSION['user_type_name'] = $user['user_type_name'];
$_SESSION['login_time'] = time();
$_SESSION['is_admin'] = $isAdmin;

// Create full name for display
$fullName = trim($user['fname'] . ' ' . ($user['mname'] ?? '') . ' ' . $user['lname']);
$_SESSION['full_name'] = $fullName;

// Regenerate session ID for security
session_regenerate_id(true);

// Log successful login in audit trail
logAuditTrail(
    $pdo,
    $user['user_id'],
    $user['username'] ?? $user['fname'],
    'login',
    'user',
    $user['user_id'],
    null,
    [
        'email' => $user['email'],
        'user_level' => $user['user_type_name'],
        'login_time' => date('Y-m-d H:i:s'),
        'user_type' => $isAdmin ? 'admin' : 'customer'
    ],
    'User logged in successfully'
);

// Determine redirect based on user level
// Use relative path based on the base URL
$baseUrl = dirname($_SERVER['PHP_SELF']);
// Go up two directories from /backend/login.php
$baseUrl = str_replace('/backend', '', $baseUrl);

$redirectUrl = $isAdmin 
    ? $baseUrl . '/app/frontend/dashboard.php' 
    : $baseUrl . '/index.php';

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'user_id' => $user['user_id'],
        'name' => $fullName,
        'email' => $user['email'],
        'user_level' => $user['user_type_name'],
        'user_level_id' => $user['user_level_id'],
        'is_admin' => $isAdmin
    ],
    'redirect' => $redirectUrl
]);
    
} catch (PDOException $e) {
    // ENHANCED ERROR LOGGING - This will show the actual error
    error_log("Login PDO Error: " . $e->getMessage());
    error_log("Login Error Code: " . $e->getCode());
    error_log("Login Error File: " . $e->getFile() . " Line: " . $e->getLine());
    
    // For development only - shows detailed error (REMOVE IN PRODUCTION)
    if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1') {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'debug' => [
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ]);
    } else {
        // Production error message
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred during login. Please try again later.'
        ]);
    }
} catch (Exception $e) {
    // Catch any other type of error
    error_log("Login General Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred: ' . $e->getMessage()
    ]);
}

// Close connections
closeConnections();
?>