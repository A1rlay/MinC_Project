<?php
session_start();
require_once '../database/connect_database.php';

// Get order number from URL
$order_number = isset($_GET['order']) ? trim($_GET['order']) : null;

if (!$order_number) {
    header('Location: ../index.php');
    exit;
}

// Get order details
try {
    $stmt = $pdo->prepare("
        SELECT o.*, c.first_name, c.last_name, c.email
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_number = ?
    ");
    $stmt->execute([$order_number]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: ../index.php');
        exit;
    }
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT * FROM order_items WHERE order_id = ?
    ");
    $stmt->execute([$order['order_id']]);
    $order_items = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Error fetching order: " . $e->getMessage());
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - MinC Computer Parts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/ca30ddfff9.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(8, 65, 92, 0.4);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation Component -->
    <?php include 'components/navbar.php'; ?>

    <!-- Success Content -->
    <div class="max-w-4xl mx-auto px-4 py-12">
        <!-- Success Icon -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-4">
                <i class="fas fa-check-circle text-5xl text-green-600"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Order Placed Successfully!</h1>
            <p class="text-xl text-gray-600">Thank you for your purchase</p>
        </div>

        <!-- Order Details Card -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
            <div class="border-b pb-6 mb-6">
                <h2 class="text-2xl font-bold text-[#08415c] mb-4">Order Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Order Number</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($order['order_number']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Order Date</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Payment Method</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo strtoupper($order['payment_method']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Amount</p>
                        <p class="text-lg font-semibold text-[#08415c]">₱<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="border-b pb-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Customer Information</h3>
                <p class="text-gray-700"><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                <p class="text-gray-700"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p class="text-gray-700"><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
            </div>

            <!-- Shipping Address -->
            <div class="border-b pb-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Shipping Address</h3>
                <p class="text-gray-700"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                <p class="text-gray-700"><?php echo htmlspecialchars($order['shipping_city'] . ', ' . $order['shipping_province']); ?></p>
                <?php if ($order['shipping_postal_code']): ?>
                <p class="text-gray-700"><?php echo htmlspecialchars($order['shipping_postal_code']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Order Items -->
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Order Items</h3>
                <div class="space-y-3">
                    <?php foreach ($order_items as $item): ?>
                    <div class="flex justify-between items-center py-3 border-b last:border-b-0">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></p>
                            <p class="text-sm text-gray-600">Qty: <?php echo $item['quantity']; ?> × ₱<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-[#08415c]">₱<?php echo number_format($item['subtotal'], 2); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Totals -->
                <div class="mt-6 space-y-2">
                    <div class="flex justify-between text-gray-700">
                        <span>Subtotal:</span>
                        <span class="font-semibold">₱<?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>Shipping:</span>
                        <span class="font-semibold"><?php echo $order['shipping_fee'] > 0 ? '₱' . number_format($order['shipping_fee'], 2) : 'FREE'; ?></span>
                    </div>
                    <div class="flex justify-between text-xl font-bold text-[#08415c] pt-2 border-t">
                        <span>Total:</span>
                        <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Instructions -->
        <?php if ($order['payment_method'] !== 'cod'): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-3">
                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                Payment Instructions
            </h3>
            <p class="text-gray-700 mb-2">Payment instructions have been sent to your email: <strong><?php echo htmlspecialchars($order['email']); ?></strong></p>
            <p class="text-gray-600 text-sm">Please complete your payment within 24 hours to confirm your order.</p>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="../index.php" class="btn-primary-custom text-white px-8 py-3 rounded-lg font-semibold text-center">
                <i class="fas fa-home mr-2"></i>
                Back to Home
            </a>
            <button onclick="window.print()" class="bg-gray-100 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                <i class="fas fa-print mr-2"></i>
                Print Order
            </button>
        </div>
    </div>

    <!-- Footer Component -->
    <?php include 'components/footer.php'; ?>
</body>
</html>