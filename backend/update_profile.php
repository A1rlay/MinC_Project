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

    $columnsStmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = array_column($columnsStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    $hasProfilePictureColumn = in_array('profile_picture', $columns, true);

    $deliveryColumnDefinitions = [
        'barangay' => "VARCHAR(120) NULL AFTER address",
        'city' => "VARCHAR(100) NULL AFTER barangay",
        'province' => "VARCHAR(100) NULL AFTER city",
        'postal_code' => "VARCHAR(20) NULL AFTER province"
    ];
    foreach ($deliveryColumnDefinitions as $columnName => $columnDefinition) {
        if (!in_array($columnName, $columns, true)) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN {$columnName} {$columnDefinition}");
                $columns[] = $columnName;
            } catch (Exception $schemaError) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Database schema mismatch: missing delivery address fields.'
                ]);
                exit;
            }
        }
    }

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
    if (!is_array($input)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request payload'
        ]);
        exit;
    }

    // Validate required fields
    $fname = isset($input['fname']) ? trim($input['fname']) : null;
    $lname = isset($input['lname']) ? trim($input['lname']) : null;
    $hasContactNumKey = array_key_exists('contact_num', $input);
    $hasAddressKey = array_key_exists('address', $input);
    $hasBarangayKey = array_key_exists('barangay', $input);
    $hasCityKey = array_key_exists('city', $input);
    $hasProvinceKey = array_key_exists('province', $input);
    $hasPostalCodeKey = array_key_exists('postal_code', $input);

    $contact_num = $hasContactNumKey ? trim((string)$input['contact_num']) : null;
    $address = $hasAddressKey ? preg_replace('/\s+/', ' ', trim((string)$input['address'])) : null;
    $barangay = $hasBarangayKey ? preg_replace('/\s+/', ' ', trim((string)$input['barangay'])) : null;
    $city = $hasCityKey ? preg_replace('/\s+/', ' ', trim((string)$input['city'])) : null;
    $province = $hasProvinceKey ? preg_replace('/\s+/', ' ', trim((string)$input['province'])) : null;
    $postal_code = $hasPostalCodeKey ? trim((string)$input['postal_code']) : null;

    $normalizeName = function ($value) {
        $value = preg_replace('/\s+/', ' ', trim((string)$value));
        return ucwords(strtolower($value), " -'");
    };
    $normalizePhilippineMobile = function ($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }
        $compact = preg_replace('/[\s\-\(\)]/', '', $value);
        if (strpos($compact, '+') === 0) {
            $compact = substr($compact, 1);
        }
        if (preg_match('/^09\d{9}$/', $compact)) {
            return $compact;
        }
        if (preg_match('/^63\d{10}$/', $compact)) {
            return '0' . substr($compact, 2);
        }
        return null;
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

    if (mb_strlen($fname) < 2 || mb_strlen($fname) > 50) {
        echo json_encode([
            'success' => false,
            'message' => 'First name must be between 2 and 50 characters'
        ]);
        exit;
    }

    if (mb_strlen($lname) < 2 || mb_strlen($lname) > 50) {
        echo json_encode([
            'success' => false,
            'message' => 'Last name must be between 2 and 50 characters'
        ]);
        exit;
    }

    // Validate contact number format if provided (accepts 09xxxxxxxxx and +63/63 formats)
    if ($hasContactNumKey && $contact_num !== '') {
        $normalizedContact = $normalizePhilippineMobile($contact_num);
        if ($normalizedContact === null) {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid contact number format. Use 09XXXXXXXXX or +63XXXXXXXXXX'
            ]);
            exit;
        }
        $contact_num = $normalizedContact;
    } elseif ($hasContactNumKey && $contact_num === '') {
        $contact_num = null;
    }

    if ($hasContactNumKey && $contact_num && !preg_match('/^09\d{9}$/', $contact_num)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid contact number format'
        ]);
        exit;
    }

    if ($hasAddressKey && $address === '') {
        $address = null;
    }
    if ($hasBarangayKey && $barangay === '') {
        $barangay = null;
    }
    if ($hasCityKey && $city === '') {
        $city = null;
    }
    if ($hasProvinceKey && $province === '') {
        $province = null;
    }
    if ($hasPostalCodeKey && $postal_code === '') {
        $postal_code = null;
    }

    if ($address !== null && (mb_strlen($address) < 10 || mb_strlen($address) > 255)) {
        echo json_encode([
            'success' => false,
            'message' => 'Complete address must be between 10 and 255 characters'
        ]);
        exit;
    }

    if ($barangay !== null && (mb_strlen($barangay) < 2 || mb_strlen($barangay) > 120)) {
        echo json_encode([
            'success' => false,
            'message' => 'Barangay must be between 2 and 120 characters'
        ]);
        exit;
    }

    if ($city !== null && (mb_strlen($city) < 2 || mb_strlen($city) > 100)) {
        echo json_encode([
            'success' => false,
            'message' => 'City must be between 2 and 100 characters'
        ]);
        exit;
    }

    if ($province !== null && (mb_strlen($province) < 2 || mb_strlen($province) > 100)) {
        echo json_encode([
            'success' => false,
            'message' => 'Province must be between 2 and 100 characters'
        ]);
        exit;
    }

    if ($postal_code !== null) {
        $postalCodeInt = (int)$postal_code;
        $postalCodeValid = preg_match('/^\d{4}$/', $postal_code) === 1
            && $postalCodeInt >= 2000
            && $postalCodeInt <= 2100;
        if (!$postalCodeValid) {
            echo json_encode([
                'success' => false,
                'message' => 'Postal code must be a 4-digit value between 2000 and 2100'
            ]);
            exit;
        }
    }

    $deliveryInputTouched = $hasAddressKey || $hasBarangayKey || $hasCityKey || $hasProvinceKey || $hasPostalCodeKey;
    $hasAnyDeliveryValue = ($address !== null) || ($barangay !== null) || ($city !== null) || ($province !== null) || ($postal_code !== null);

    if ($deliveryInputTouched && $hasAnyDeliveryValue) {
        if (!$address || !$barangay || !$city || !$province) {
            echo json_encode([
                'success' => false,
                'message' => 'Complete address, barangay, city, and province are required when saving delivery information'
            ]);
            exit;
        }
    }

    // Get current user data
    $currentQuery = "SELECT fname, lname, contact_num, address, barangay, city, province, postal_code FROM users WHERE user_id = :user_id";
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

    if ($hasContactNumKey) {
        $updateQuery .= ", contact_num = :contact_num";
        $params[':contact_num'] = $contact_num;
    }

    if ($hasAddressKey) {
        $updateQuery .= ", address = :address";
        $params[':address'] = $address;
    }
    if ($hasBarangayKey) {
        $updateQuery .= ", barangay = :barangay";
        $params[':barangay'] = $barangay;
    }
    if ($hasCityKey) {
        $updateQuery .= ", city = :city";
        $params[':city'] = $city;
    }
    if ($hasProvinceKey) {
        $updateQuery .= ", province = :province";
        $params[':province'] = $province;
    }
    if ($hasPostalCodeKey) {
        $updateQuery .= ", postal_code = :postal_code";
        $params[':postal_code'] = $postal_code;
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
        'address' => $currentUser['address'],
        'barangay' => $currentUser['barangay'] ?? null,
        'city' => $currentUser['city'] ?? null,
        'province' => $currentUser['province'] ?? null,
        'postal_code' => $currentUser['postal_code'] ?? null
    ]);

    $newValue = json_encode([
        'fname' => $fname,
        'lname' => $lname,
        'contact_num' => $contact_num,
        'address' => $address,
        'barangay' => $barangay,
        'city' => $city,
        'province' => $province,
        'postal_code' => $postal_code
    ]);

    try {
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
    } catch (Exception $auditError) {
        error_log('Audit log failed in update_profile.php: ' . $auditError->getMessage());
    }

    // Get updated user data
    $fetchQuery = $hasProfilePictureColumn
        ? "SELECT user_id, fname, lname, email, contact_num, address, barangay, city, province, postal_code, profile_picture FROM users WHERE user_id = :user_id"
        : "SELECT user_id, fname, lname, email, contact_num, address, barangay, city, province, postal_code, NULL AS profile_picture FROM users WHERE user_id = :user_id";
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
