<?php
/**
 * Update User Profile Backend
 * Updates authenticated user's profile details
 * File: backend/update_profile.php
 */

// Prevent any output before JSON
ob_start();

// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clear any previous output
ob_clean();

// Set JSON header immediately
header('Content-Type: application/json');

// Disable error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(0);

try {
    // Include files
    require_once '../database/connect_database.php';
    require_once 'auth.php';

    // Validate session
    $validation = validateSession(false);
    if (!$validation['valid']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Session invalid: ' . ($validation['reason'] ?? 'unknown')
        ]);
        exit;
    }

    // Get user ID from session
    $user_id = $_SESSION['user_id'] ?? 0;

    if (!$user_id) {
        echo json_encode([
            'success' => false, 
            'message' => 'User ID not found in session'
        ]);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $fname = isset($input['fname']) ? trim($input['fname']) : null;
    $lname = isset($input['lname']) ? trim($input['lname']) : null;
    $contact_num = isset($input['contact_num']) ? trim($input['contact_num']) : null;
    $address = isset($input['address']) ? trim($input['address']) : null;

    $normalizeName = function ($value) {
        $value = preg_replace('/\s+/', ' ', trim((string)$value));
        return ucwords(strtolower($value), " -'");
    };

    if ($fname !== null) {
        $fname = $normalizeName($fname);
    }
    if ($lname !== null) {
        $lname = $normalizeName($lname);
    }

    if (!$fname || !$lname) {
        echo json_encode([
            'success' => false, 
            'message' => 'First name and last name are required'
        ]);
        exit;
    }

    // Validate contact number format if provided
    if ($contact_num && !preg_match('/^[\d\s\-\+\(\)]+$/', $contact_num)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid contact number format'
        ]);
        exit;
    }

    // Get current user data
    $currentQuery = "SELECT fname, lname, contact_num, address FROM users WHERE user_id = :user_id";
    $currentStmt = $pdo->prepare($currentQuery);
    $currentStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $currentStmt->execute();
    $currentUser = $currentStmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        echo json_encode([
            'success' => false, 
            'message' => 'User not found'
        ]);
        exit;
    }

    // Prepare update query
    $updateQuery = "UPDATE users SET fname = :fname, lname = :lname";
    $params = [
        ':fname' => $fname,
        ':lname' => $lname
    ];

    if ($contact_num !== null) {
        $updateQuery .= ", contact_num = :contact_num";
        $params[':contact_num'] = $contact_num;
    }

    if ($address !== null) {
        $updateQuery .= ", address = :address";
        $params[':address'] = $address;
    }

    $updateQuery .= " WHERE user_id = :user_id";
    $params[':user_id'] = $user_id;

    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute($params);

    // Log audit trail
    $oldValue = json_encode([
        'fname' => $currentUser['fname'],
        'lname' => $currentUser['lname'],
        'contact_num' => $currentUser['contact_num'],
        'address' => $currentUser['address']
    ]);

    $newValue = json_encode([
        'fname' => $fname,
        'lname' => $lname,
        'contact_num' => $contact_num,
        'address' => $address
    ]);

    $auditQuery = "INSERT INTO audit_trail (user_id, session_username, action, entity_type, entity_id, old_value, new_value, change_reason, ip_address, user_agent) 
                   VALUES (:user_id, :session_username, :action, :entity_type, :entity_id, :old_value, :new_value, :change_reason, :ip_address, :user_agent)";
    
    $auditStmt = $pdo->prepare($auditQuery);
    $auditStmt->execute([
        ':user_id' => $user_id,
        ':session_username' => $_SESSION['fname'] . ' ' . $_SESSION['lname'],
        ':action' => 'update_profile',
        ':entity_type' => 'user',
        ':entity_id' => $user_id,
        ':old_value' => $oldValue,
        ':new_value' => $newValue,
        ':change_reason' => 'User updated own profile information',
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);

    // Get updated user data
    $fetchQuery = "SELECT user_id, fname, lname, email, contact_num, address, profile_picture FROM users WHERE user_id = :user_id";
    $fetchStmt = $pdo->prepare($fetchQuery);
    $fetchStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $fetchStmt->execute();
    $updatedUser = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'data' => $updatedUser
    ]);

} catch (Exception $e) {
    error_log('Error in update_profile.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while updating profile'
    ]);
}

// Flush output buffer
ob_end_flush();
?>
