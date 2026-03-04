<?php
/**
 * Add Product Line Backend
 * File: C:\xampp\htdocs\MinC_Project\backend\product-lines\add_product_line.php
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
        $slugify = static function ($value) {
            $slug = strtolower(trim((string)$value));
            $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
            $slug = trim($slug, '-');
            return $slug !== '' ? $slug : 'product-line';
        };

        $generateUniqueSlug = static function ($pdo, $baseSlug) {
            $slug = $baseSlug;
            $counter = 2;
            while (true) {
                $check = $pdo->prepare("SELECT product_line_id FROM product_lines WHERE product_line_slug = ? LIMIT 1");
                $check->execute([$slug]);
                if (!$check->fetch()) {
                    return $slug;
                }
                $slug = $baseSlug . '-' . $counter;
                $counter++;
                if ($counter > 9999) {
                    throw new Exception('Unable to generate a unique product line slug.');
                }
            }
        };

        // Get form data
        $category_id = intval($_POST['category_id'] ?? 0);
        $product_line_name = trim($_POST['product_line_name'] ?? '');
        $product_line_description = trim($_POST['product_line_description'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);
        
        // Validate required fields
        if (empty($category_id)) {
            throw new Exception('Category is required.');
        }
        
        if (empty($product_line_name)) {
            throw new Exception('Product line name is required.');
        }

        $baseSlug = $slugify($product_line_name);
        $product_line_slug = $generateUniqueSlug($pdo, $baseSlug);
        
        // Validate category exists
        $check_category = $pdo->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $check_category->execute([$category_id]);
        if (!$check_category->fetch()) {
            throw new Exception('Selected category does not exist.');
        }
        
        // Handle image upload
        $product_line_image = null;
        if (isset($_FILES['product_line_image']) && $_FILES['product_line_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../Assets/images/product-lines/';
            
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
        
        // Insert product line
        $insert_query = "
            INSERT INTO product_lines (
                category_id,
                product_line_name, 
                product_line_slug, 
                product_line_description, 
                product_line_image, 
                display_order, 
                status,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
        ";
        
        $stmt = $pdo->prepare($insert_query);
        $stmt->execute([
            $category_id,
            $product_line_name,
            $product_line_slug,
            $product_line_description,
            $product_line_image,
            $display_order
        ]);
        
        $product_line_id = $pdo->lastInsertId();
        
        // Log audit trail
        $audit_query = "
            INSERT INTO audit_trail (
                user_id,
                session_username,
                action,
                entity_type,
                entity_id,
                new_value,
                change_reason,
                timestamp,
                ip_address,
                user_agent,
                system_id
            ) VALUES (?, ?, 'CREATE', 'product_line', ?, ?, 'Added new product line', NOW(), ?, ?, 'minc_system')
        ";
        
        $new_value = json_encode([
            'category_id' => $category_id,
            'product_line_name' => $product_line_name,
            'product_line_slug' => $product_line_slug,
            'product_line_description' => $product_line_description,
            'product_line_image' => $product_line_image,
            'display_order' => $display_order,
            'status' => 'active'
        ]);
        
        $audit_stmt = $pdo->prepare($audit_query);
        $audit_stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['full_name'] ?? $_SESSION['fname'],
            $product_line_id,
            $new_value,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success_message'] = 'Product line added successfully!';
        header('Location: ../../app/frontend/product-lines.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Delete uploaded image if exists
        if (isset($product_line_image) && file_exists('../../Assets/images/product-lines/' . $product_line_image)) {
            unlink('../../Assets/images/product-lines/' . $product_line_image);
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
