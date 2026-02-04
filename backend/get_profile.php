<?php
/**
 * Get User Profile Backend
 * Retrieves authenticated user's profile data
 * File: backend/get_profile.php
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
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Session expired. Please login again.'
        ]);
        exit;
    }

    $user_id = (int)$_SESSION['user_id'];

    // Build a safe profile query based on available columns
    $columnsStmt = $pdo->query("SHOW COLUMNS FROM users");
    $availableColumns = array_column($columnsStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

    $selectParts = [
        "user_id",
        "fname",
        "lname",
        "email",
        "contact_num",
        "address",
        "user_level_id",
        "user_status",
        "created_at"
    ];

    if (in_array('profile_picture', $availableColumns, true)) {
        $selectParts[] = "profile_picture";
    } else {
        $selectParts[] = "NULL AS profile_picture";
    }

    $query = "SELECT " . implode(", ", $selectParts) . " FROM users WHERE user_id = :user_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'success' => false, 
            'message' => 'User profile not found'
        ]);
        exit;
    }

    // Build profile picture URL
    if ($user['profile_picture']) {
        $user['profile_picture_url'] = '/pages/MinC_Project/Assets/images/profiles/' . $user['profile_picture'];
    } else {
        $user['profile_picture_url'] = '/pages/MinC_Project/Assets/images/default-avatar.png';
    }

    echo json_encode([
        'success' => true,
        'data' => $user
    ]);

} catch (Exception $e) {
    error_log('Error in get_profile.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while retrieving profile'
    ]);
}

// Flush output buffer
ob_end_flush();
?>
