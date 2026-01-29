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

// Custom styles (retained + auto-parts themed - matching home page)
$additional_styles = '
<style>
    :root {
        --primary-color: #08415c;
        --primary-dark: #0a5273;
        --primary-light: #1a6d9e;
    }
    
    body {
        font-family: "Inter", sans-serif;
    }
    
    .stat-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,0.95) 100%);
        border: 1px solid rgba(8, 65, 92, 0.1);
        transition: all 0.3s ease;
    }
    
    .stat-card:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 20px 40px rgba(8, 65, 92, 0.15);
        border-color: rgba(8, 65, 92, 0.2);
    }
    
    .stat-card .icon-box {
        background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
    }
    
    .quick-action-btn {
        background: linear-gradient(135deg, rgba(248,250,252,0.95) 0%, rgba(255,255,255,0.95) 100%);
        border: 2px solid rgba(8, 65, 92, 0.1);
        transition: all 0.3s ease;
    }
    
    .quick-action-btn:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 12px 30px rgba(8, 65, 92, 0.15);
        border-color: rgba(8, 65, 92, 0.3);
    }
    
    .quick-action-btn .icon-circle {
        background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
    }
    
    .order-item {
        border: 1px solid rgba(8, 65, 92, 0.1);
        transition: all 0.3s ease;
    }
    
    .order-item:hover { 
        background: rgba(248,250,252,0.9);
        transform: translateX(5px);
        border-color: rgba(8, 65, 92, 0.2);
    }
    
    .chart-container { 
        height: 300px; 
        width: 100%; 
    }
    
    .chart-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,0.95) 100%);
        border: 1px solid rgba(8, 65, 92, 0.1);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    
    .professional-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,0.95) 100%);
        border: 1px solid rgba(8, 65, 92, 0.1);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    
    .section-title {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-slideInUp {
        animation: slideInUp 0.6s ease-out forwards;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
</style>';

// Start output buffering for content
ob_start();
?>

<!-- Welcome Section -->
<div class="professional-card rounded-xl p-6 mb-6 animate-slideInUp">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[#08415c] mb-2">
                Welcome back, <?= htmlspecialchars(explode(' ', $user_data['name'])[0]) ?>! 
            </h2>
            <p class="text-gray-600">Here's your MinC Auto Supply store performance today.</p>
        </div>
        <div class="hidden md:block">
            <div class="w-16 h-16 rounded-xl flex items-center justify-center animate-float" style="background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);">
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
                <p class="text-3xl font-bold text-[#08415c]">₱<?= number_format($today_sales, 2) ?></p>
                <p class="text-xs text-[#0a5273] mt-2"><?= $today_orders ?> orders today</p>
            </div>
            <div class="p-4 icon-box rounded-xl">
                <i class="fas fa-peso-sign text-white text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card professional-card rounded-xl p-6 hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Pending Orders</p>
                <p class="text-3xl font-bold text-[#08415c]"><?= $pending_orders ?></p>
                <p class="text-xs text-[#0a5273] mt-2">Requires attention</p>
            </div>
            <div class="p-4 icon-box rounded-xl">
                <i class="fas fa-clock text-white text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card professional-card rounded-xl p-6 hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Low Stock Items</p>
                <p class="text-3xl font-bold text-[#08415c]"><?= $low_stock ?></p>
                <p class="text-xs text-[#0a5273] mt-2">Restock needed</p>
            </div>
            <div class="p-4 icon-box rounded-xl">
                <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card professional-card rounded-xl p-6 hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Revenue</p>
                <p class="text-3xl font-bold text-[#08415c]">₱<?= number_format($total_revenue, 2) ?></p>
                <p class="text-xs text-[#0a5273] mt-2">All time</p>
            </div>
            <div class="p-4 icon-box rounded-xl">
                <i class="fas fa-chart-line text-white text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="chart-card p-6">
        <h3 class="text-lg font-semibold text-[#08415c] mb-4 section-title">Sales Trend (Last 6 Months)</h3>
        <div class="chart-container">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <div class="chart-card p-6">
        <h3 class="text-lg font-semibold text-[#08415c] mb-4 section-title">Order Status Distribution</h3>
        <div class="chart-container">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Quick Actions & Recent Orders -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 professional-card rounded-xl p-6">
        <h3 class="text-lg font-semibold text-[#08415c] mb-6 section-title">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button onclick="location.href='products.php'" class="quick-action-btn flex flex-col items-center p-6 rounded-xl border">
                <div class="w-12 h-12 icon-circle rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-box text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-[#08415c]">Products</span>
            </button>
            <button onclick="location.href='orders.php'" class="quick-action-btn flex flex-col items-center p-6 rounded-xl border">
                <div class="w-12 h-12 icon-circle rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-shopping-cart text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-[#08415c]">Orders</span>
            </button>
            <button onclick="location.href='categories.php'" class="quick-action-btn flex flex-col items-center p-6 rounded-xl border">
                <div class="w-12 h-12 icon-circle rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-tags text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-[#08415c]">Categories</span>
            </button>
            <button onclick="location.href='customers.php'" class="quick-action-btn flex flex-col items-center p-6 rounded-xl border">
                <div class="w-12 h-12 icon-circle rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-[#08415c]">Customers</span>
            </button>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="grid grid-cols-2 gap-3">
                <button onclick="location.href='generate_report.php'" class="flex items-center justify-center p-3 bg-gradient-to-r from-[#08415c] to-[#0a5273] text-white rounded-lg font-medium hover:shadow-lg transition">
                    <i class="fas fa-file-alt mr-2"></i>View Reports
                </button>
                <button onclick="location.href='products.php'" class="flex items-center justify-center p-3 border-2 border-[#08415c] text-[#08415c] rounded-lg font-medium hover:bg-[#08415c] hover:text-white transition">
                    <i class="fas fa-warehouse mr-2"></i>Inventory
                </button>
            </div>
        </div>
    </div>

    <div class="professional-card rounded-xl p-6">
        <h3 class="text-lg font-semibold text-[#08415c] mb-6 section-title">Recent Orders</h3>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            <?php if ($recent_orders): ?>
                <?php foreach ($recent_orders as $order): ?>
                    <div class="order-item flex justify-between items-center p-4 rounded-lg border">
                        <div>
                            <p class="font-medium text-[#08415c]">#<?= $order['order_number'] ?></p>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($order['customer_name']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-[#08415c]">₱<?= number_format($order['total_amount'], 2) ?></p>
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
            <a href="orders.php" class="text-sm text-[#08415c] hover:text-[#0a5273] font-medium flex items-center justify-center">
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
                borderColor: '#08415c',
                backgroundColor: 'rgba(8, 65, 92, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 6,
                pointBackgroundColor: '#08415c',
                pointBorderColor: '#0a5273'
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