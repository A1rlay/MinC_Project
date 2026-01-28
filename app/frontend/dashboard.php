<?php
/**
 * Auto Supply Parts - Admin Dashboard
 * Online Shopping System for Automotive Parts & Accessories
 * Path: C:\xampp\htdocs\MinC_Project\app\frontend\dashboard.php
 */

include_once '../../backend/auth.php';
include_once '../../database/connect_database.php';

// Fetch current user data (from session)
$user_data = [
    'name' => $_SESSION['full_name'] ?? $_SESSION['fname'] ?? 'Admin',
    'user_type' => $_SESSION['user_type_name'] ?? 'Administrator'
];

// Page title
$custom_title = 'Dashboard - AutoSupply Pro';

// === DASHBOARD STATISTICS (Templated - Replace with real queries later) ===
try {
    // Today's Sales
    $today_sales = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(order_date) = CURDATE() AND status IN ('completed','shipped')")->fetchColumn();

    // Total Orders Today
    $today_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(order_date) = CURDATE()")->fetchColumn();

    // Pending Orders
    $pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

    // Low Stock Products (< 10 units)
    $low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity < 10 AND status = 'active'")->fetchColumn();

    // Total Revenue (All Time)
    $total_revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status IN ('completed','shipped')")->fetchColumn();

    // Total Customers
    $total_customers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();

    // New Customers Today
    $new_customers_today = $pdo->query("SELECT COUNT(*) FROM customers WHERE DATE(created_at) = CURDATE()")->fetchColumn();

    // Top Selling Categories (Last 30 Days)
    $top_categories = $pdo->query("
        SELECT c.category_name, SUM(oi.quantity) as units_sold
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        JOIN categories c ON p.category_id = c.category_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
          AND o.status IN ('completed','shipped')
        GROUP BY c.category_id, c.category_name
        ORDER BY units_sold DESC
        LIMIT 6
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Monthly Sales Trend (Last 6 Months)
    $monthly_sales = $pdo->query("
        SELECT 
            DATE_FORMAT(order_date, '%Y-%m') as month,
            DATE_FORMAT(order_date, '%M %Y') as month_name,
            COALESCE(SUM(total_amount), 0) as revenue
        FROM orders 
        WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
          AND status IN ('completed','shipped')
        GROUP BY DATE_FORMAT(order_date, '%Y-%m')
        ORDER BY month ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Recent Orders
    $recent_orders = $pdo->query("
        SELECT o.order_id, o.order_number, o.total_amount, o.status, o.order_date,
               CONCAT(c.first_name, ' ', c.last_name) as customer_name
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        ORDER BY o.order_date DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Order Status Distribution
    $status_distribution = $pdo->query("
        SELECT 
            status,
            COUNT(*) as count,
            CASE 
                WHEN status = 'completed' OR status = 'shipped' THEN '#10B981'
                WHEN status = 'pending' THEN '#F59E0B'
                WHEN status = 'processing' THEN '#3B82F6'
                WHEN status = 'cancelled' THEN '#EF4444'
                ELSE '#6B7280'
            END as color
        FROM orders
        GROUP BY status
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Fallback values if queries fail
    $today_sales = $today_orders = $pending_orders = $low_stock = 0;
    $total_revenue = $total_customers = $new_customers_today = 0;
    $top_categories = $monthly_sales = $recent_orders = $status_distribution = [];
    error_log("Dashboard query error: " . $e->getMessage());
}

// Encode for Chart.js
$monthly_sales_json = json_encode($monthly_sales);
$top_categories_json = json_encode($top_categories);
$status_distribution_json = json_encode($status_distribution);

// Custom styles (retained + auto-parts themed)
$additional_styles = '
<style>
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
    .quick-action-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
    .order-item:hover { background: rgba(249,250,251,0.9); transform: translateX(5px); }
    .chart-container { height: 300px; width: 100%; }
</style>';

// Start output buffering for content
ob_start();
?>

<!-- Welcome Section -->
<div class="professional-card rounded-xl p-6 mb-6 animate-fadeIn">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                Welcome back, <?= htmlspecialchars(explode(' ', $user_data['name'])[0]) ?>! 
            </h2>
            <p class="text-gray-600">Here's your AutoSupply Pro store performance today.</p>
        </div>
        <div class="hidden md:block">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center animate-float">
                <i class="fas fa-car text-white text-3xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="stat-card professional-card rounded-xl p-6 hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Today's Sales</p>
                <p class="text-3xl font-bold text-gray-900">₱<?= number_format($today_sales, 2) ?></p>
                <p class="text-xs text-green-600 mt-2"><?= $today_orders ?> orders today</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl">
                <i class="fas fa-peso-sign text-white text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card professional-card rounded-xl p-6 hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Pending Orders</p>
                <p class="text-3xl font-bold text-gray-900"><?= $pending_orders ?></p>
                <p class="text-xs text-amber-600 mt-2">Requires attention</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl">
                <i class="fas fa-clock text-white text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card professional-card rounded-xl p-6 hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Low Stock Items</p>
                <p class="text-3xl font-bold text-gray-900"><?= $low_stock ?></p>
                <p class="text-xs text-red-600 mt-2">Restock needed</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl">
                <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card professional-card rounded-xl p-6 hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Revenue</p>
                <p class="text-3xl font-bold text-gray-900">₱<?= number_format($total_revenue, 2) ?></p>
                <p class="text-xs text-blue-600 mt-2">All time</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl">
                <i class="fas fa-chart-line text-white text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="chart-card p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Sales Trend (Last 6 Months)</h3>
        <div class="chart-container">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <div class="chart-card p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Status Distribution</h3>
        <div class="chart-container">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Quick Actions & Recent Orders -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 professional-card rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button onclick="location.href='products.php'" class="quick-action-btn flex flex-col items-center p-6 rounded-xl bg-gradient-to-br from-blue-50 to-cyan-100 border border-blue-200">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-box text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium">Manage Products</span>
            </button>
            <button onclick="location.href='orders.php'" class="quick-action-btn flex flex-col items-center p-6 rounded-xl bg-gradient-to-br from-green-50 to-emerald-100 border border-green-200">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-shopping-cart text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium">View Orders</span>
            </button>
            <button onclick="location.href='categories.php'" class="quick-action-btn flex flex-col items-center p-6 rounded-xl bg-gradient-to-br from-purple-50 to-indigo-100 border border-purple-200">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-tags text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium">Categories</span>
            </button>
            <button onclick="location.href='customers.php'" class="quick-action-btn flex flex-col items-center p-6 rounded-xl bg-gradient-to-br from-orange-50 to-amber-100 border border-orange-200">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium">Customers</span>
            </button>
        </div>
    </div>

    <div class="professional-card rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Recent Orders</h3>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            <?php if ($recent_orders): ?>
                <?php foreach ($recent_orders as $order): ?>
                    <div class="order-item flex justify-between items-center p-4 rounded-lg border border-gray-100 hover:bg-gray-50 transition">
                        <div>
                            <p class="font-medium text-gray-800">#<?= $order['order_number'] ?></p>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($order['customer_name']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">₱<?= number_format($order['total_amount'], 2) ?></p>
                            <span class="text-xs px-2 py-1 rounded-full 
                                <?= $order['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($order['status'] == 'pending' ? 'bg-amber-100 text-amber-800' : 
                                   ($order['status'] == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-500 py-8">No recent orders</p>
            <?php endif; ?>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200">
            <a href="orders.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center justify-center">
                View all orders <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';

    // Sales Trend Line Chart
    const salesData = <?= $monthly_sales_json ?>;
    const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesData.map(d => d.month_name),
            datasets: [{
                label: 'Revenue',
                data: salesData.map(d => d.revenue),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 6
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // Order Status Doughnut Chart
    const statusData = <?= $status_distribution_json ?>;
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1)),
            datasets: [{
                data: statusData.map(s => s.count),
                backgroundColor: statusData.map(s => s.color),
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>

<?php
$dashboard_content = ob_get_clean();
$content = $dashboard_content;
include 'app.php'; // This loads your main layout
?>