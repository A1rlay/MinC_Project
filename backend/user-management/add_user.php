<?php
/**
 * Add User Backend with Audit Trail
 * File: C:\xampp\htdocs\MinC_Project\backend\user-management\add_user.php
 */

session_start();
require_once '../../database/connect_database.php';
require_once '../auth.php';

// Validate session
$validation = validateSession();
if (!$validation['valid']) {
    $_SESSION['error_message'] = 'Session invalid. Please login again.';
    header('Location: ../../app/frontend/user-management.php');
    exit;
}

// Check if user has permission (only IT Personnel, Owner, and Manager)
if (!isManagementLevel()) {
    $_SESSION['error_message'] = 'Access denied. You do not have permission to add users.';
    header('Location: ../../app/frontend/user-management.php');
    exit;
}

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Invalid request method.';
    header('Location: ../../app/frontend/user-management.php');
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get and sanitize input data
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $mname = !empty($_POST['mname']) ? trim($_POST['mname']) : null;
    $email = trim($_POST['email'] ?? '');
    $username = !empty($_POST['username']) ? trim($_POST['username']) : null;
    $contact_num = !empty($_POST['contact_num']) ? trim($_POST['contact_num']) : null;
    $password = $_POST['password'] ?? '';
    $user_level_id = intval($_POST['user_level_id'] ?? 0);

    // Validate required fields
    if (empty($fname) || empty($lname) || empty($email) || empty($password) || $user_level_id === 0) {
        throw new Exception('Please fill in all required fields.');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format.');
    }

    // Check if email already exists
    $email_check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $email_check->execute([$email]);
    if ($email_check->fetch()) {
        throw new Exception('Email already exists.');
    }

    // Check if username already exists (if provided)
    if (!empty($username)) {
        $username_check = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $username_check->execute([$username]);
        if ($username_check->fetch()) {
            throw new Exception('Username already exists.');
        }
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $insert_query = "
        INSERT INTO users (
            fname, mname, lname, email, username, 
            contact_num, password, user_level_id, user_status, created_at
        ) VALUES (
            :fname, :mname, :lname, :email, :username, 
            :contact_num, :password, :user_level_id, 'active', NOW()
        )
    ";

    $stmt = $pdo->prepare($insert_query);
    $stmt->execute([
        ':fname' => $fname,
        ':mname' => $mname,
        ':lname' => $lname,
        ':email' => $email,
        ':username' => $username,
        ':contact_num' => $contact_num,
        ':password' => $hashed_password,
        ':user_level_id' => $user_level_id
    ]);

    $new_user_id = $pdo->lastInsertId();

    // Prepare audit trail data
    $new_value = [
        'fname' => $fname,
        'mname' => $mname,
        'lname' => $lname,
        'email' => $email,
        'username' => $username,
        'contact_num' => $contact_num,
        'user_level_id' => (string)$user_level_id,
        'user_status' => 'active'
    ];

    // Insert audit trail
    $audit_query = "
        INSERT INTO audit_trail (
            user_id, session_username, action, entity_type, entity_id,
            old_value, new_value, timestamp, ip_address, user_agent
        ) VALUES (
            :user_id, :session_username, :action, :entity_type, :entity_id,
            NULL, :new_value, NOW(), :ip_address, :user_agent
        )
    ";

    $audit_stmt = $pdo->prepare($audit_query);
    $audit_stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':session_username' => $_SESSION['username'] ?? $_SESSION['full_name'] ?? 'System',
        ':action' => 'create',
        ':entity_type' => 'user',
        ':entity_id' => $new_user_id,
        ':new_value' => json_encode($new_value),
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success_message'] = 'User added successfully!';
    header('Location: ../../app/frontend/user-management.php');
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['error_message'] = 'Error adding user: ' . $e->getMessage();
    header('Location: ../../app/frontend/user-management.php');
    exit;
}