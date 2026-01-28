<?php
/**
 * Authentication & Session Management
 * Path: C:\xampp\htdocs\MinC_Project\backend\auth.php
 * 
 * This file handles:
 * 1. Session validation for protected pages
 * 2. Role-based access control functions
 * 3. Works with both regular pages and API endpoints
 * 4. Supports both admin and customer sessions
 */

// Only start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuration
$timeout_duration = 7200; // 2 hours in seconds

/**
 * Validate session and handle authentication
 * @param bool $redirect Whether to redirect on failure (false for API calls)
 * @param bool $admin_only Whether to restrict access to admin users only
 * @return array Validation result
 */
function validateSession($redirect = true, $admin_only = false) {
    global $timeout_duration;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
        if ($redirect) {
            header('Location: ../../index.php?error=unauthorized');
            exit;
        }
        return [
            'valid' => false,
            'reason' => 'unauthorized'
        ];
    }
    
    // Check if admin-only access is required
    if ($admin_only && !isManagementLevel()) {
        if ($redirect) {
            header('Location: ../../index.php?error=access_denied');
            exit;
        }
        return [
            'valid' => false,
            'reason' => 'access_denied'
        ];
    }
    
    // Check session timeout
    if (isset($_SESSION['login_time'])) {
        $elapsed_time = time() - $_SESSION['login_time'];
        
        if ($elapsed_time > $timeout_duration) {
            session_unset();
            session_destroy();
            if ($redirect) {
                header('Location: ../../index.php?error=session_expired');
                exit;
            }
            return [
                'valid' => false,
                'reason' => 'session_expired'
            ];
        }
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    return ['valid' => true];
}

/**
 * Role-based Access Control Functions
 * Based on user_levels table structure
 */

/**
 * Check if user is IT Personnel (user_level_id = 1)
 */
function isITStaff() {
    return isset($_SESSION['user_level_id']) && $_SESSION['user_level_id'] == 1;
}

/**
 * Check if user is Owner (user_level_id = 2)
 */
function isOwner() {
    return isset($_SESSION['user_level_id']) && $_SESSION['user_level_id'] == 2;
}

/**
 * Check if user is Manager (user_level_id = 3)
 */
function isManager() {
    return isset($_SESSION['user_level_id']) && $_SESSION['user_level_id'] == 3;
}

/**
 * Check if user is Consumer (user_level_id = 4)
 */
function isConsumer() {
    return isset($_SESSION['user_level_id']) && $_SESSION['user_level_id'] == 4;
}

/**
 * Check if user is Admin (IT Personnel or Owner)
 * Combines IT Personnel and Owner roles for admin-level access
 */
function isAdmin() {
    return isITStaff() || isOwner();
}

/**
 * Check if user has management level access (IT Personnel, Owner, or Manager)
 */
function isManagementLevel() {
    return isITStaff() || isOwner() || isManager();
}

/**
 * Get user level name
 */
function getUserLevelName() {
    if (!isset($_SESSION['user_type_name'])) {
        return 'Unknown';
    }
    return $_SESSION['user_type_name'];
}

/**
 * Check if user has specific permission level
 * @param int $required_level The minimum user_level_id required
 * @return bool True if user has required level or higher
 */
function hasPermissionLevel($required_level) {
    if (!isset($_SESSION['user_level_id'])) {
        return false;
    }
    // Lower user_level_id = higher privileges (1 = IT Personnel is highest)
    return $_SESSION['user_level_id'] <= $required_level;
}

/**
 * Check if specific user level
 * @param int $level_id The user_level_id to check
 * @return bool True if user has this exact level
 */
function hasUserLevel($level_id) {
    if (!isset($_SESSION['user_level_id'])) {
        return false;
    }
    return $_SESSION['user_level_id'] == $level_id;
}

/**
 * API endpoint to check session status
 * Usage: auth.php?api=status
 */
if (isset($_GET['api']) && $_GET['api'] === 'status') {
    header('Content-Type: application/json');
    
    // Include database connection for validation
    require_once __DIR__ . '/../database/connect_database.php';
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'logged_in' => false,
            'message' => 'Not logged in'
        ]);
        exit;
    }
    
    try {
        // Verify user still exists and is active
        $stmt = $pdo->prepare("
            SELECT 
                u.user_id, 
                u.fname, 
                u.lname,
                u.email,
                u.user_status,
                u.user_level_id,
                ul.user_type_name
            FROM users u
            INNER JOIN user_levels ul ON u.user_level_id = ul.user_level_id
            WHERE u.user_id = :user_id
            LIMIT 1
        ");
        
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if user exists and is active
        if (!$user || $user['user_status'] !== 'active') {
            // Clear invalid session
            session_unset();
            session_destroy();
            
            echo json_encode([
                'logged_in' => false,
                'message' => 'Session expired or account deactivated'
            ]);
            exit;
        }
        
        // Check session timeout
        if (isset($_SESSION['login_time'])) {
            $elapsed_time = time() - $_SESSION['login_time'];
            
            if ($elapsed_time > $timeout_duration) {
                session_unset();
                session_destroy();
                
                echo json_encode([
                    'logged_in' => false,
                    'message' => 'Session expired'
                ]);
                exit;
            }
        }
        
        // Session is valid - return user info
        echo json_encode([
            'logged_in' => true,
            'user' => [
                'user_id' => $user['user_id'],
                'name' => trim($user['fname'] . ' ' . $user['lname']),
                'email' => $user['email'],
                'user_level_id' => $user['user_level_id'],
                'user_type_name' => $user['user_type_name'],
                'is_admin' => in_array($user['user_level_id'], [1, 2, 3])
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Session check error: " . $e->getMessage());
        
        echo json_encode([
            'logged_in' => false,
            'message' => 'Session validation error'
        ]);
    }
    
    closeConnections();
    exit;
}

// Only auto-validate for non-API requests
// API endpoints should call validateSession(false) manually
$current_file = basename($_SERVER['PHP_SELF']);
$api_files = ['get_user.php', 'get_notification_count.php']; // Add other API files here

// Don't auto-validate for API endpoints
if (!in_array($current_file, $api_files)) {
    // This is a regular page, validate with redirect
    // validateSession(); // Commented out - let individual pages call it
}
?>