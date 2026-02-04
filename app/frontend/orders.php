<?php
/**
 * Orders Management Frontend
 * File: C:\xampp\htdocs\MinC_Project\app\frontend\orders.php
 */

// Authentication and user data
include_once '../../backend/auth.php';
include_once '../../database/connect_database.php';

// Validate session
$validation = validateSession();
if (!$validation['valid']) {
    header('Location: ../../index.php?error=' . $validation['reason']);
    exit;
}

// Check if user has permission to access order management
// Only IT Personnel (1), Owner (2), and Manager (3) can access
if (!isManagementLevel()) {
    $_SESSION['error_message'] = 'Access denied. You do not have permission to access this page.';
    header('Location: dashboard.php');
    exit;
}

// Get current user data
$user_data = [
    'id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['full_name'] ?? $_SESSION['fname'] ?? 'Guest User',
    'user_type' => $_SESSION['user_type_name'] ?? 'User',
    'user_level_id' => $_SESSION['user_level_id'] ?? null
];

// Set custom title for this page
$custom_title = 'Order Management - MinC Project';

// Update user array to match app.php format
$user = [
    'full_name' => $user_data['name'],
    'user_type' => $user_data['user_type'],
    'is_logged_in' => isset($user_data['id'])
];

// Fetch orders data
try {
    // Get orders with customer and item information
    $orders_query = "
        SELECT 
            o.order_id,
            o.order_number,
            o.tracking_number,
            o.customer_id,
            o.customer_phone,
            c.first_name,
            c.last_name,
            CONCAT(c.first_name, ' ', c.last_name) as customer_name,
            c.email as customer_email,
            c.customer_type,
            o.subtotal,
            o.shipping_fee,
            o.total_amount,
            o.payment_method,
            o.payment_status,
            o.order_status,
            o.shipping_address,
            o.shipping_city,
            o.shipping_province,
            o.shipping_postal_code,
            o.delivery_date,
            o.notes,
            o.created_at,
            o.updated_at,
            COUNT(oi.order_item_id) as total_items,
            SUM(oi.quantity) as total_quantity
        FROM orders o
        INNER JOIN customers c ON o.customer_id = c.customer_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        GROUP BY o.order_id
        ORDER BY o.created_at DESC
    ";
    $orders_result = $pdo->query($orders_query);
    $orders = $orders_result->fetchAll(PDO::FETCH_ASSOC);

    // Calculate statistics
    $total_orders = count($orders);
    $total_revenue = array_sum(array_column($orders, 'total_amount'));
    $pending_orders = count(array_filter($orders, function($o) { return $o['order_status'] === 'pending'; }));
    $completed_orders = count(array_filter($orders, function($o) { return $o['order_status'] === 'delivered'; }));

} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error loading data: ' . $e->getMessage();
    $orders = [];
    $total_orders = 0;
    $total_revenue = 0;
    $pending_orders = 0;
    $completed_orders = 0;
}

// Additional styles for order management specific elements
$additional_styles = '
.order-status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-pending {
    background-color: #fef3c7;
    color: #92400e;
}

.status-confirmed {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-processing {
    background-color: #e0e7ff;
    color: #4338ca;
}

.status-shipped {
    background-color: #ddd6fe;
    color: #6b21a8;
}

.status-delivered {
    background-color: #dcfce7;
    color: #166534;
}

.status-cancelled {
    background-color: #fef2f2;
    color: #991b1b;
}

.payment-status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
}

.payment-pending {
    background-color: #fef3c7;
    color: #92400e;
}

.payment-paid {
    background-color: #dcfce7;
    color: #166534;
}

.payment-failed {
    background-color: #fef2f2;
    color: #991b1b;
}

.payment-refunded {
    background-color: #f3f4f6;
    color: #6b7280;
}

.payment-method-badge {
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 500;
    background-color: #f3f4f6;
    color: #374151;
}

.table-hover tbody tr:hover {
    background-color: rgba(249, 250, 251, 0.8);
}

/* Fixed table container and table width styles */
.desktop-table {
    width: 100%;
    overflow-x: auto;
}

.desktop-table table {
    width: 100%;
    min-width: 100%;
    table-layout: auto;
}

.desktop-table th,
.desktop-table td {
    min-width: 120px;
}

.desktop-table th:first-child,
.desktop-table td:first-child {
    min-width: 180px;
}

.desktop-table th:nth-child(2),
.desktop-table td:nth-child(2) {
    min-width: 200px;
}

.desktop-table th:nth-child(3),
.desktop-table td:nth-child(3) {
    min-width: 140px;
}

.desktop-table th:nth-child(4),
.desktop-table td:nth-child(4) {
    min-width: 140px;
}

.desktop-table th:nth-child(5),
.desktop-table td:nth-child(5) {
    min-width: 140px;
}

.desktop-table th:last-child,
.desktop-table td:last-child {
    min-width: 100px;
}

@media (max-width: 768px) {
    .mobile-card {
        display: block !important;
    }
    
    .desktop-table {
        display: none !important;
    }
}

@media (min-width: 769px) {
    .mobile-card {
        display: none !important;
    }
    
    .desktop-table {
        display: block !important;
        width: 100%;
    }
}

.professional-card.table-container {
    padding: 0;
    overflow: hidden;
}

.professional-card.table-container .desktop-table {
    margin: 0;
}

.stat-card {
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.timeline-item {
    position: relative;
    padding-left: 30px;
}

.timeline-item::before {
    content: "";
    position: absolute;
    left: 7px;
    top: 25px;
    bottom: -20px;
    width: 2px;
    background-color: #e5e7eb;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-dot {
    position: absolute;
    left: 0;
    top: 8px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background-color: #10b981;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #10b981;
}
';

// Order management content
ob_start();
?>

<!-- Page Header -->
<div class="professional-card rounded-xl p-6 mb-6 animate-fadeIn">
    <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div class="mb-4 md:mb-0">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                <i class="fas fa-shopping-cart text-green-600 mr-3"></i>
                Order Management
            </h2>
            <p class="text-gray-600">
                View and track all customer orders and their status.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="exportOrders()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium flex items-center transition-colors duration-200">
                <i class="fas fa-download mr-2"></i>
                Export Data
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="professional-card rounded-xl p-6 stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Orders</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo $total_orders; ?></p>
            </div>
            <div class="p-4 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-lg">
                <i class="fas fa-shopping-cart text-white text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="professional-card rounded-xl p-6 stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Revenue</p>
                <p class="text-3xl font-bold text-green-600">₱<?php echo number_format($total_revenue, 2); ?></p>
            </div>
            <div class="p-4 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg">
                <i class="fas fa-peso-sign text-white text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="professional-card rounded-xl p-6 stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Pending Orders</p>
                <p class="text-3xl font-bold text-yellow-600"><?php echo $pending_orders; ?></p>
            </div>
            <div class="p-4 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl shadow-lg">
                <i class="fas fa-clock text-white text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="professional-card rounded-xl p-6 stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Completed</p>
                <p class="text-3xl font-bold text-purple-600"><?php echo $completed_orders; ?></p>
            </div>
            <div class="p-4 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                <i class="fas fa-check-circle text-white text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="professional-card rounded-xl p-6 mb-6 animate-fadeIn">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="search_orders" class="block text-sm font-medium text-gray-700 mb-2">Search Orders</label>
            <div class="relative">
                <input type="text" id="search_orders" placeholder="Search by order number or customer..." 
                       class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
        
        <div>
            <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
            <select id="status_filter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        
        <div>
            <label for="payment_status_filter" class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
            <select id="payment_status_filter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                <option value="">All Payment Status</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="failed">Failed</option>
                <option value="refunded">Refunded</option>
            </select>
        </div>
        
        <div>
            <label for="payment_method_filter" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
            <select id="payment_method_filter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                <option value="">All Methods</option>
                <option value="cod">Cash on Delivery</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="gcash">GCash</option>
                <option value="paymaya">PayMaya</option>
            </select>
        </div>
    </div>
</div>

<!-- Orders Table/Cards -->
<div class="professional-card table-container rounded-xl overflow-hidden animate-fadeIn">
    <!-- Desktop Table View -->
    <div class="desktop-table">
        <table class="w-full table-hover">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Details</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-shopping-cart text-4xl mb-4 text-gray-300"></i>
                            <p>No orders found.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr class="order-row" 
                            data-order-id="<?php echo (int)$order['order_id']; ?>"
                            data-order-number="<?php echo strtolower($order['order_number']); ?>" 
                            data-customer="<?php echo strtolower($order['customer_name']); ?>"
                            data-status="<?php echo $order['order_status']; ?>"
                            data-payment-status="<?php echo $order['payment_status']; ?>"
                            data-payment-method="<?php echo $order['payment_method']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    #<?php echo htmlspecialchars($order['order_number']); ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>
                                </div>
                                <?php if ($order['tracking_number']): ?>
                                    <div class="text-xs text-blue-600 mt-1">
                                        <i class="fas fa-shipping-fast mr-1"></i><?php echo htmlspecialchars($order['tracking_number']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($order['customer_name']); ?>
                                </div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $order['total_items']; ?> item(s)</div>
                                <div class="text-xs text-gray-500"><?php echo $order['total_quantity']; ?> qty</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">₱<?php echo number_format($order['total_amount'], 2); ?></div>
                                <div class="text-xs text-gray-500">
                                    Subtotal: ₱<?php echo number_format($order['subtotal'], 2); ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Shipping: ₱<?php echo number_format($order['shipping_fee'], 2); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="mb-2">
                                    <span class="payment-method-badge">
                                        <?php 
                                        $payment_methods = [
                                            'cod' => 'COD',
                                            'bank_transfer' => 'Bank Transfer',
                                            'gcash' => 'GCash',
                                            'paymaya' => 'PayMaya'
                                        ];
                                        echo $payment_methods[$order['payment_method']] ?? ucfirst($order['payment_method']);
                                        ?>
                                    </span>
                                </div>
                                <span class="payment-status-badge payment-<?php echo $order['payment_status']; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="order-status-badge status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                                <?php if ($order['delivery_date']): ?>
                                    <div class="text-xs text-gray-500 mt-2">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?php echo date('M d, Y', strtotime($order['delivery_date'])); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition-colors duration-200" 
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="mobile-card p-4 space-y-4">
        <?php if (empty($orders)): ?>
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-shopping-cart text-4xl mb-4 text-gray-300"></i>
                <p>No orders found.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="bg-white border border-gray-200 rounded-xl p-4 order-card" 
                     data-order-id="<?php echo (int)$order['order_id']; ?>"
                     data-order-number="<?php echo strtolower($order['order_number']); ?>" 
                     data-customer="<?php echo strtolower($order['customer_name']); ?>"
                     data-status="<?php echo $order['order_status']; ?>"
                     data-payment-status="<?php echo $order['payment_status']; ?>"
                     data-payment-method="<?php echo $order['payment_method']; ?>">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="font-medium text-gray-900">#<?php echo htmlspecialchars($order['order_number']); ?></h4>
                            <p class="text-xs text-gray-500"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                        <span class="order-status-badge status-<?php echo $order['order_status']; ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Customer</p>
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($order['customer_phone']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Total Amount</p>
                            <p class="text-sm font-bold text-green-600">₱<?php echo number_format($order['total_amount'], 2); ?></p>
                            <p class="text-xs text-gray-500"><?php echo $order['total_items']; ?> item(s)</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Payment Method</p>
                            <span class="payment-method-badge">
                                <?php 
                                $payment_methods = [
                                    'cod' => 'COD',
                                    'bank_transfer' => 'Bank Transfer',
                                    'gcash' => 'GCash',
                                    'paymaya' => 'PayMaya'
                                ];
                                echo $payment_methods[$order['payment_method']] ?? ucfirst($order['payment_method']);
                                ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Payment Status</p>
                            <span class="payment-status-badge payment-<?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)" 
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200" 
                                title="View Details">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center p-4" style="backdrop-filter: blur(10px);">
    <div class="professional-card rounded-xl max-w-5xl w-full max-h-screen overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-800">Order Details</h3>
                <button type="button" onclick="closeOrderDetails()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="orderDetailsContent">
                <!-- Content will be loaded dynamically -->
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
                    <p class="text-gray-600 mt-4">Loading order details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Initialize everything on page load
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM loaded, initializing filters");
    initializeFilters();
});

// Filter initialization
function initializeFilters() {
    const searchInput = document.getElementById("search_orders");
    const statusFilter = document.getElementById("status_filter");
    const paymentStatusFilter = document.getElementById("payment_status_filter");
    const paymentMethodFilter = document.getElementById("payment_method_filter");

    function applyFilters() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : "";
        const selectedStatus = statusFilter ? statusFilter.value : "";
        const selectedPaymentStatus = paymentStatusFilter ? paymentStatusFilter.value : "";
        const selectedPaymentMethod = paymentMethodFilter ? paymentMethodFilter.value : "";
        
        const orderRows = document.querySelectorAll(".order-row");
        const orderCards = document.querySelectorAll(".order-card");

        function filterElement(element) {
            const orderNumber = element.getAttribute("data-order-number") || "";
            const customer = element.getAttribute("data-customer") || "";
            const status = element.getAttribute("data-status") || "";
            const paymentStatus = element.getAttribute("data-payment-status") || "";
            const paymentMethod = element.getAttribute("data-payment-method") || "";

            const matchesSearch = searchTerm === "" || 
                                orderNumber.includes(searchTerm) || 
                                customer.includes(searchTerm);
            
            const matchesStatus = selectedStatus === "" || status === selectedStatus;
            const matchesPaymentStatus = selectedPaymentStatus === "" || paymentStatus === selectedPaymentStatus;
            const matchesPaymentMethod = selectedPaymentMethod === "" || paymentMethod === selectedPaymentMethod;

            const isVisible = matchesSearch && matchesStatus && matchesPaymentStatus && matchesPaymentMethod;
            element.style.display = isVisible ? "" : "none";
        }

        orderRows.forEach(filterElement);
        orderCards.forEach(filterElement);
    }

    if (searchInput) searchInput.addEventListener("input", applyFilters);
    if (statusFilter) statusFilter.addEventListener("change", applyFilters);
    if (paymentStatusFilter) paymentStatusFilter.addEventListener("change", applyFilters);
    if (paymentMethodFilter) paymentMethodFilter.addEventListener("change", applyFilters);
}

// View order details
function viewOrderDetails(orderId) {
    console.log("Viewing order details for ID:", orderId);
    const modal = document.getElementById("orderDetailsModal");
    const content = document.getElementById("orderDetailsContent");
    
    if (!modal) {
        console.error("Order details modal not found");
        return;
    }
    
    modal.classList.remove("hidden");
    document.body.style.overflow = 'hidden';
    
    // Show loading state
    content.innerHTML = `
        <div class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
            <p class="text-gray-600 mt-4">Loading order details...</p>
        </div>
    `;
    
    // Fetch order details
    fetch(`../../backend/order-management/get_order.php?id=${orderId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Order data received:", data);
            
            if (data.success) {
                displayOrderDetails(data.order, data.items);
            } else {
                content.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                        <p class="text-gray-600">${escapeHtml(data.message || 'Failed to load order details')}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            content.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                    <p class="text-gray-600">Error loading order details: ${escapeHtml(error.message)}</p>
                    <button onclick="viewOrderDetails(${orderId})" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Retry
                    </button>
                </div>
            `;
        });
}

// Display order details
function displayOrderDetails(order, items) {
    const content = document.getElementById("orderDetailsContent");
    
    let itemsHtml = '';
    if (items && items.length > 0) {
        itemsHtml = items.map(item => `
            <div class="bg-gray-50 rounded-lg p-4 mb-3">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">${escapeHtml(item.product_name)}</p>
                        ${item.product_code ? `<p class="text-sm text-gray-500">Code: ${escapeHtml(item.product_code)}</p>` : ''}
                        <div class="mt-2 flex items-center gap-4">
                            <span class="text-sm text-gray-600">Qty: ${item.quantity}</span>
                            <span class="text-sm text-gray-600">Price: ₱${formatNumber(item.price)}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-900">₱${formatNumber(item.subtotal)}</p>
                    </div>
                </div>
            </div>
        `).join('');
    } else {
        itemsHtml = '<p class="text-gray-500 text-center py-8">No items found</p>';
    }
    
    content.innerHTML = `
        <div class="space-y-6">
            <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900">#${escapeHtml(order.order_number)}</h4>
                        <p class="text-sm text-gray-600 mt-1">Placed on ${formatDateTime(order.created_at)}</p>
                        ${order.tracking_number ? `
                            <div class="mt-2 inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                <i class="fas fa-shipping-fast mr-2"></i>
                                Tracking: ${escapeHtml(order.tracking_number)}
                            </div>
                        ` : ''}
                    </div>
                    <div class="text-right">
                        <span class="order-status-badge status-${order.order_status}">
                            ${capitalizeFirst(order.order_status)}
                        </span>
                        <div class="mt-2">
                            <span class="payment-status-badge payment-${order.payment_status}">
                                ${capitalizeFirst(order.payment_status)}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h5 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-user mr-2 text-green-600"></i>Customer Information
                    </h5>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500">Name</p>
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(order.customer_name)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(order.customer_email)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Phone</p>
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(order.customer_phone)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Customer Type</p>
                            <span class="type-badge type-${order.customer_type}">
                                ${capitalizeFirst(order.customer_type)}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h5 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>Shipping Information
                    </h5>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500">Address</p>
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(order.shipping_address)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">City</p>
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(order.shipping_city)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Province</p>
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(order.shipping_province)}</p>
                        </div>
                        ${order.shipping_postal_code ? `
                            <div>
                                <p class="text-xs text-gray-500">Postal Code</p>
                                <p class="text-sm font-medium text-gray-900">${escapeHtml(order.shipping_postal_code)}</p>
                            </div>
                        ` : ''}
                        ${order.delivery_date ? `
                            <div>
                                <p class="text-xs text-gray-500">Expected Delivery</p>
                                <p class="text-sm font-medium text-gray-900">${formatDate(order.delivery_date)}</p>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h5 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-credit-card mr-2 text-green-600"></i>Payment Information
                </h5>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Payment Method</p>
                        <span class="payment-method-badge mt-1">${getPaymentMethodLabel(order.payment_method)}</span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Payment Status</p>
                        <span class="payment-status-badge payment-${order.payment_status} mt-1">${capitalizeFirst(order.payment_status)}</span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Payment Receipt</p>
                        ${order.payment_receipt
                            ? `<a href="${escapeHtml(order.payment_receipt)}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:underline">View receipt</a>`
                            : `<p class="text-sm text-gray-500">No receipt uploaded</p>`}
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Subtotal</p>
                        <p class="text-sm font-medium text-gray-900">₱${formatNumber(order.subtotal)}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Shipping Fee</p>
                        <p class="text-sm font-medium text-gray-900">₱${formatNumber(order.shipping_fee)}</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total Amount</span>
                        <span class="text-2xl font-bold text-green-600">₱${formatNumber(order.total_amount)}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h5 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-cogs mr-2 text-green-600"></i>Order Actions
                </h5>
                <div class="flex flex-wrap gap-2">${getOrderActionButtons(order)}</div>
                <p class="text-xs text-gray-500 mt-3">Cancellation is only allowed before processing. Refund is only for paid and eligible orders.</p>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h5 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-box mr-2 text-green-600"></i>Order Items (${items.length})
                </h5>
                ${itemsHtml}
            </div>
            
            ${order.notes ? `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <h5 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-sticky-note mr-2 text-yellow-600"></i>Order Notes
                    </h5>
                    <p class="text-sm text-gray-700">${escapeHtml(order.notes)}</p>
                </div>
            ` : ''}
        </div>
    `;
}

function getOrderActionButtons(order) {
    const buttons = [];
    const orderId = Number(order.order_id);

    if (order.order_status === 'pending') {
        buttons.push(`<button onclick="handleOrderAction(${orderId}, 'confirm_order')" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Confirm Order</button>`);
    }
    if (order.order_status === 'confirmed') {
        buttons.push(`<button onclick="handleOrderAction(${orderId}, 'process_order')" class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Mark Processing</button>`);
    }
    if (order.order_status === 'processing') {
        buttons.push(`<button onclick="handleOrderAction(${orderId}, 'ship_order')" class="px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">Mark Shipped</button>`);
    }
    if (order.order_status === 'shipped') {
        buttons.push(`<button onclick="handleOrderAction(${orderId}, 'deliver_order')" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">Mark Delivered</button>`);
    }
    if (order.payment_status === 'pending' && order.order_status !== 'cancelled') {
        buttons.push(`<button onclick="handleOrderAction(${orderId}, 'mark_paid')" class="px-3 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">Complete Payment</button>`);
    }
    if (order.order_status === 'pending' || order.order_status === 'confirmed') {
        buttons.push(`<button onclick="handleOrderAction(${orderId}, 'cancel_order', true)" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Cancel Order</button>`);
    }
    if (order.payment_status === 'paid' && (order.order_status === 'cancelled' || order.order_status === 'delivered')) {
        buttons.push(`<button onclick="handleOrderAction(${orderId}, 'refund_payment', true)" class="px-3 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 text-sm">Process Refund</button>`);
    }

    return buttons.length ? buttons.join('') : '<span class="text-sm text-gray-500">No available actions for current state.</span>';
}

async function handleOrderAction(orderId, action, askReason = false) {
    let reason = '';

    const actionLabel = {
        confirm_order: 'confirm this order',
        process_order: 'mark this order as processing',
        ship_order: 'mark this order as shipped',
        deliver_order: 'mark this order as delivered',
        mark_paid: 'mark payment as completed',
        cancel_order: 'cancel this order',
        refund_payment: 'process a refund'
    }[action] || 'update this order';

    if (!confirm(`Are you sure you want to ${actionLabel}?`)) return;
    if (askReason) reason = prompt('Optional note/reason:') || '';

    try {
        const response = await fetch('../../backend/order-management/update_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, action, reason })
        });

        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Failed to update order');
        }

        showNotification(data.message || 'Order updated successfully', 'success');
        setTimeout(() => window.location.reload(), 700);
    } catch (error) {
        console.error('Order action error:', error);
        showNotification(error.message || 'Failed to update order', 'error');
    }
}

// Close order details modal
function closeOrderDetails() {
    const modal = document.getElementById("orderDetailsModal");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = '';
    }
}

// Export orders data
function exportOrders() {
    console.log("Exporting orders...");
    
    const exportBtn = event.target.closest('button');
    const originalContent = exportBtn.innerHTML;
    exportBtn.disabled = true;
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...';
    
    const exportUrl = '../../backend/order-management/export_orders.php';
    
    fetch(exportUrl, {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Export failed');
        }
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `orders_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        exportBtn.disabled = false;
        exportBtn.innerHTML = originalContent;
        
        showNotification('Orders exported successfully!', 'success');
    })
    .catch(error => {
        console.error('Export error:', error);
        exportBtn.disabled = false;
        exportBtn.innerHTML = originalContent;
        showNotification('Failed to export orders. Please try again.', 'error');
    });
}

// Helper function to show notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-600' : 
        type === 'error' ? 'bg-red-600' : 
        'bg-blue-600'
    } text-white animate-fadeIn`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-3"></i>
            <span>${escapeHtml(message)}</span>
        </div>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Helper functions
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatNumber(number) {
    return parseFloat(number).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function capitalizeFirst(string) {
    if (!string) return '';
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function getPaymentMethodLabel(method) {
    const methods = {
        'cod': 'Cash on Delivery',
        'bank_transfer': 'Bank Transfer',
        'gcash': 'GCash',
        'paymaya': 'PayMaya'
    };
    return methods[method] || capitalizeFirst(method);
}

// Close modal when clicking outside
document.addEventListener("click", function(event) {
    const modal = document.getElementById("orderDetailsModal");
    if (event.target === modal) {
        closeOrderDetails();
    }
});

// Close modal on ESC key
document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        closeOrderDetails();
    }
});
</script>
<?php
$order_management_content = ob_get_clean();// Set the content for app.php
$content = $order_management_content;// Include the app.php layout
include 'app.php';
?>


