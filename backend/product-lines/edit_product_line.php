<?php
/**
 * Edit Product Line Backend
 * File: C:\xampp\htdocs\MinC_Project\backend\product-lines\edit_product_line.php
 */

session_start();
require_once '../../database/connect_database.php';
require_once '../auth.php';

// Validate session
$validation = validateSession();
if (!$validation['valid']) {
    $_SESSION['error_message'] = 'Session expired. Please login again.';
    header('Location: ../../index.php');
    exit;
}

// Check management level permission
if (!isManagementLevel()) {
    $_SESSION['error_message'] = 'Access denied. Insufficient permissions.';
    header('Location: ../../app/frontend/product-lines.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $product_line_id = intval($_POST['product_line_id'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $product_line_name = trim($_POST['product_line_name'] ?? '');
        $product_line_slug = trim($_POST['product_line_slug'] ?? '');
        $product_line_description = trim($_POST['product_line_description'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        
        // Validate required fields
        if (empty($product_line_id)) {
            throw new Exception('Product line ID is required.');
        }
        
        if (empty($category_id)) {
            throw new Exception('Category is required.');
        }
        
        if (empty($product_line_name)) {
            throw new Exception('Product line name is required.');
        }
        
        if (empty($product_line_slug)) {
            throw new Exception('Product line slug is required.');
        }
        
        // Get current product line data
        $current_query = "SELECT * FROM product_lines WHERE product_line_id = ?";
        $current_stmt = $pdo->prepare($current_query);
        $current_stmt->execute([$product_line_id]);
        $current_data = $current_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$current_data) {
            throw new Exception('Product line not found.');
        }
        
        // Validate category exists
        $check_category = $pdo->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $check_category->execute([$category_id]);
        if (!$check_category->fetch()) {
            throw new Exception('Selected category does not exist.');
        }
        
        // Check if slug already exists (excluding current product line)
        $check_slug = $pdo->prepare("
            SELECT product_line_id 
            FROM product_lines 
            WHERE product_line_slug = ? AND product_line_id != ?
        ");
        $check_slug->execute([$product_line_slug, $product_line_id]);
        if ($check_slug->fetch()) {
            throw new Exception('Product line slug already exists. Please use a different slug.');
        }
        
        // Handle image upload
        $product_line_image = $current_data['product_line_image'];
        $old_image_path = null;
        
        if (isset($_FILES['product_line_image']) && $_FILES['product_line_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../assets/images/product-lines/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_tmp = $_FILES['product_line_image']['tmp_name'];
            $file_name = $_FILES['product_line_image']['name'];
            $file_size = $_FILES['product_line_image']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validate file type
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($file_ext, $allowed_extensions)) {
                throw new Exception('Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed.');
            }
            
            // Validate file size (2MB max)
            if ($file_size > 2097152) {
                throw new Exception('File size too large. Maximum size is 2MB.');
            }
            
            // Store old image path for deletion
            if ($product_line_image) {
                $old_image_path = $upload_dir . $product_line_image;
            }
            
            // Generate unique filename
            $product_line_image = 'product_line_' . time() . '_' . uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $product_line_image;
            
            // Move uploaded file
            if (!move_uploaded_file($file_tmp, $upload_path)) {
                throw new Exception('Failed to upload image.');
            }
        }
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Update product line
        $update_query = "
            UPDATE product_lines SET
                category_id = ?,
                product_line_name = ?,
                product_line_slug = ?,
                product_line_description = ?,
                product_line_image = ?,
                display_order = ?,
                status = ?,
                updated_at = NOW()
            WHERE product_line_id = ?
        ";
        
        $stmt = $pdo->prepare($update_query);
        $stmt->execute([
            $category_id,
            $product_line_name,
            $product_line_slug,
            $product_line_description,
            $product_line_image,
            $display_order,
            $status,
            $product_line_id
        ]);
        
        // Delete old image if new one was uploaded
        if ($old_image_path && file_exists($old_image_path)) {
            unlink($old_image_path);
        }
        
        // Log audit trail
        $audit_query = "
            INSERT INTO audit_trail (
                user_id,
                session_username,
                action,
                entity_type,
                entity_id,
                old_value,
                new_value,
                change_reason,
                timestamp,
                ip_address,
                user_agent,
                system_id
            ) VALUES (?, ?, 'UPDATE', 'product_line', ?, ?, ?, 'Updated product line information', NOW(), ?, ?, 'minc_system')
        ";
        
        $old_value = json_encode([
            'category_id' => $current_data['category_id'],
            'product_line_name' => $current_data['product_line_name'],
            'product_line_slug' => $current_data['product_line_slug'],
            'product_line_description' => $current_data['product_line_description'],
            'product_line_image' => $current_data['product_line_image'],
            'display_order' => $current_data['display_order'],
            'status' => $current_data['status']
        ]);
        
        $new_value = json_encode([
            'category_id' => $category_id,
            'product_line_name' => $product_line_name,
            'product_line_slug' => $product_line_slug,
            'product_line_description' => $product_line_description,
            'product_line_image' => $product_line_image,
            'display_order' => $display_order,
            'status' => $status
        ]);
        
        $audit_stmt = $pdo->prepare($audit_query);
        $audit_stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['full_name'] ?? $_SESSION['fname'],
            $product_line_id,
            $old_value,
            $new_value,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success_message'] = 'Product line updated successfully!';
        header('Location: ../../app/frontend/product-lines.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Delete newly uploaded image if exists and there was an error
        if (isset($upload_path) && file_exists($upload_path)) {
            unlink($upload_path);
        }
        
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        header('Location: ../../app/frontend/product-lines.php');
        exit;
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method.';
    header('Location: ../../app/frontend/product-lines.php');
    exit;
}
?>