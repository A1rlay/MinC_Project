<?php
session_start();
require_once '../../database/connect_database.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validate required fields
$required = ['customer', 'payment_method', 'delivery_method'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$delivery_method = strtolower(trim((string)$data['delivery_method']));
if (!in_array($delivery_method, ['shipping', 'pickup'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid delivery method']);
    exit;
}

$payment_method = strtolower(trim((string)$data['payment_method']));
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$is_guest_checkout = !$user_id;

// Validate customer data
$customer = $data['customer'];
$requiredCustomer = ['first_name', 'last_name', 'email', 'phone'];
foreach ($requiredCustomer as $field) {
    if (empty($customer[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing customer information: $field"]);
        exit;
    }
}

// Validate shipping data (required only for shipping)
if ($delivery_method === 'shipping') {
    if (!isset($data['shipping'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required field: shipping']);
        exit;
    }

    $shipping = $data['shipping'];
    $requiredShipping = ['address', 'city', 'province'];
    foreach ($requiredShipping as $field) {
        if (empty($shipping[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing shipping information: $field"]);
            exit;
        }
    }

    $allowedCities = [
        'Angeles City', 'Mabalacat City', 'San Fernando City', 'Apalit', 'Arayat',
        'Bacolor', 'Candaba', 'Floridablanca', 'Guagua', 'Lubao', 'Masantol',
        'Mexico', 'Minalin', 'Porac', 'San Luis', 'San Simon', 'Santa Ana',
        'Santa Rita', 'Santo Tomas', 'Sasmuan'
    ];
    if (trim((string)$shipping['province']) !== 'Pampanga' || !in_array(trim((string)$shipping['city']), $allowedCities, true)) {
        echo json_encode(['success' => false, 'message' => 'Shipping is only available within Pampanga municipalities.']);
        exit;
    }
} else {
    $shipping = [
        'address' => 'Pickup at Store',
        'city' => 'Pickup',
        'province' => 'Pickup',
        'postal_code' => null
    ];
}

// Validate payment method
$validPaymentMethods = ['cod', 'bank_transfer', 'gcash', 'paymaya'];
if (!in_array($payment_method, $validPaymentMethods, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment method']);
    exit;
}

if ($is_guest_checkout) {
    if ($delivery_method !== 'pickup') {
        echo json_encode(['success' => false, 'message' => 'Guest checkout only supports pickup orders.']);
        exit;
    }
    if ($payment_method !== 'cod') {
        echo json_encode(['success' => false, 'message' => 'Guest checkout only supports COD payment.']);
        exit;
    }
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get user ID if logged in
    $user_id = $user_id ?: null;
    $session_id = session_id();
    
    // Get cart
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE session_id = ?");
        $stmt->execute([$session_id]);
    }
    
    $cart = $stmt->fetch();
    if (!$cart) {
        throw new Exception('Cart not found');
    }
    
    $cart_id = $cart['cart_id'];
    
    // Get cart items
    $stmt = $pdo->prepare("
        SELECT ci.*, p.product_name, p.product_code, p.stock_quantity
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cart_id]);
    $cart_items = $stmt->fetchAll();
    
    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }
    
    // Check stock availability
    foreach ($cart_items as $item) {
        if ($item['quantity'] > $item['stock_quantity']) {
            throw new Exception("Insufficient stock for product: {$item['product_name']}");
        }
    }
    
    // Calculate totals
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $FREE_SHIPPING_THRESHOLD = 1000;
    $SHIPPING_FEE = 150;
    $shipping_fee = $delivery_method === 'shipping'
        ? ($subtotal >= $FREE_SHIPPING_THRESHOLD ? 0 : $SHIPPING_FEE)
        : 0;
    $total_amount = $subtotal + $shipping_fee;
    
    // Create or get customer
    $stmt = $pdo->prepare("
        SELECT customer_id FROM customers 
        WHERE email = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$customer['email']]);
    $existing_customer = $stmt->fetch();
    
    if ($existing_customer) {
        $customer_id = $existing_customer['customer_id'];
        
        // Update customer information
        if ($delivery_method === 'shipping') {
            $stmt = $pdo->prepare("
                UPDATE customers SET
                    first_name = ?,
                    last_name = ?,
                    phone = ?,
                    address = ?,
                    city = ?,
                    province = ?,
                    postal_code = ?,
                    updated_at = NOW()
                WHERE customer_id = ?
            ");
            $stmt->execute([
                $customer['first_name'],
                $customer['last_name'],
                $customer['phone'],
                $shipping['address'],
                $shipping['city'],
                $shipping['province'],
                $shipping['postal_code'] ?? null,
                $customer_id
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE customers SET
                    first_name = ?,
                    last_name = ?,
                    phone = ?,
                    updated_at = NOW()
                WHERE customer_id = ?
            ");
            $stmt->execute([
                $customer['first_name'],
                $customer['last_name'],
                $customer['phone'],
                $customer_id
            ]);
        }
    } else {
        // Create new customer
        $customer_type = $user_id ? 'registered' : 'guest';

        if ($delivery_method === 'shipping') {
            $stmt = $pdo->prepare("
                INSERT INTO customers (
                    user_id, first_name, last_name, email, phone,
                    address, city, province, postal_code, customer_type
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $customer['first_name'],
                $customer['last_name'],
                $customer['email'],
                $customer['phone'],
                $shipping['address'],
                $shipping['city'],
                $shipping['province'],
                $shipping['postal_code'] ?? null,
                $customer_type
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO customers (
                    user_id, first_name, last_name, email, phone,
                    address, city, province, postal_code, customer_type
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $customer['first_name'],
                $customer['last_name'],
                $customer['email'],
                $customer['phone'],
                null,
                null,
                null,
                null,
                $customer_type
            ]);
        }
        
        $customer_id = $pdo->lastInsertId();
    }
    
    // Generate order number
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            customer_id, customer_phone, order_number, subtotal, shipping_fee, total_amount,
            payment_method, payment_status, order_status,
            shipping_address, shipping_city, shipping_province, shipping_postal_code,
            notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?)
    ");
    $notes = $data['notes'] ?? null;
    if ($delivery_method === 'pickup') {
        $pickup_date = $data['pickup_date'] ?? null;
        $pickup_time = $data['pickup_time'] ?? null;
        if ($pickup_date || $pickup_time) {
            $pickup_parts = array_filter([$pickup_date, $pickup_time]);
            $pickup_text = 'Pickup: ' . implode(' ', $pickup_parts);
            $notes = $notes ? ($notes . ' | ' . $pickup_text) : $pickup_text;
        }
    }

    $stmt->execute([
        $customer_id,
        $customer['phone'],
        $order_number,
        $subtotal,
        $shipping_fee,
        $total_amount,
        $payment_method,
        $shipping['address'],
        $shipping['city'],
        $shipping['province'],
        $shipping['postal_code'] ?? null,
        $notes
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Create order items and update stock
    $stmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id, product_id, product_name, product_code,
            quantity, price, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $updateStock = $pdo->prepare("
        UPDATE products 
        SET stock_quantity = stock_quantity - ? 
        WHERE product_id = ?
    ");
    
    foreach ($cart_items as $item) {
        $item_subtotal = $item['price'] * $item['quantity'];
        
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['product_name'],
            $item['product_code'],
            $item['quantity'],
            $item['price'],
            $item_subtotal
        ]);
        
        // Update product stock
        $updateStock->execute([
            $item['quantity'],
            $item['product_id']
        ]);
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cart_id]);
    
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ?");
    $stmt->execute([$cart_id]);
    
    // Log audit trail if user is logged in
    if ($user_id) {
        $stmt = $pdo->prepare("
            INSERT INTO audit_trail (
                user_id, session_username, action, entity_type, entity_id,
                new_value, change_reason, ip_address, user_agent, system_id
            ) VALUES (?, ?, 'CREATE', 'order', ?, ?, 'Order placed', ?, ?, 'minc_system')
        ");
        
        $username = $_SESSION['username'] ?? 'Guest';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $new_value = json_encode([
            'order_number' => $order_number,
            'total_amount' => $total_amount,
            'payment_method' => $payment_method,
            'items_count' => count($cart_items)
        ]);
        
        $stmt->execute([
            $user_id,
            $username,
            $order_id,
            $new_value,
            $ip_address,
            $user_agent
        ]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Send confirmation email (optional - implement if needed)
    // sendOrderConfirmationEmail($customer['email'], $order_number);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_number' => $order_number,
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Order processing error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
