<?php
/**
 * Auto Supply Parts - Admin Dashboard
 * Online Shopping System for Automotive Parts & Accessories
 * Path: C:\xampp\htdocs\MinC_Project\app\frontend\dashboard.php
 */

include_once '../../backend/auth.php';
include_once '../../database/connect_database.php';

// Validate session and permissions (match other admin pages)
$validation = validateSession();
if (!$validation['valid']) {
    header('Location: ../../index.php?error=' . $validation['reason']);
    exit;
}

if (!isManagementLevel()) {
    $_SESSION['error_message'] = 'Access denied. You do not have permission to access this page.';
    header('Location: ../../index.php');
    exit;
}

// Fetch current user data (from session)
$user_data = [
    'name' => $_SESSION['full_name'] ?? $_SESSION['fname'] ?? 'Admin',
    'user_type' => $_SESSION['user_type_name'] ?? 'Administrator'
];

// Page title
$custom_title = 'Dashboard - MinC Project';
$current_page = 'dashboard';

// === DASHBOARD STATISTICS ===
$currentSalesMonth = new DateTimeImmutable('first day of this month');
$requestedSalesMonth = trim((string)($_GET['sales_month'] ?? $currentSalesMonth->format('Y-m')));
$selectedSalesMonth = DateTimeImmutable::createFromFormat('!Y-m', $requestedSalesMonth);

if (!$selectedSalesMonth || $selectedSalesMonth->format('Y-m') !== $requestedSalesMonth || $selectedSalesMonth > $currentSalesMonth) {
    $selectedSalesMonth = $currentSalesMonth;
}

$selectedSalesMonthKey = $selectedSalesMonth->format('Y-m');
$selectedSalesMonthLabel = $selectedSalesMonth->format('F Y');
$selectedSalesMonthStart = $selectedSalesMonth->format('Y-m-01');
$selectedSalesMonthEndExclusive = $selectedSalesMonth->modify('+1 month')->format('Y-m-01');
$selectedSalesMonthVisibleEnd = $selectedSalesMonthKey === $currentSalesMonth->format('Y-m')
    ? date('Y-m-d')
    : $selectedSalesMonth->format('Y-m-t');
$selectedSalesMonthVisibleEndDate = new DateTimeImmutable($selectedSalesMonthVisibleEnd);
$selectedSalesMonthSubtext = $selectedSalesMonthKey === $currentSalesMonth->format('Y-m')
    ? 'Showing daily sales from ' . $selectedSalesMonth->format('F j') . ' to ' . $selectedSalesMonthVisibleEndDate->format('F j, Y')
    : 'Showing daily sales for the full month of ' . $selectedSalesMonthLabel;

$buildDashboardUrl = static function (array $overrides = []): string {
    $query = $_GET;
    unset($query['refresh']);

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
            continue;
        }

        $query[$key] = $value;
    }

    $queryString = http_build_query($query);
    return $queryString === '' ? 'dashboard.php' : 'dashboard.php?' . $queryString;
};

$previousSalesMonthUrl = $buildDashboardUrl([
    'sales_month' => $selectedSalesMonth->modify('-1 month')->format('Y-m')
]);
$nextSalesMonth = $selectedSalesMonth->modify('+1 month');
$canViewNextSalesMonth = $nextSalesMonth <= $currentSalesMonth;
$nextSalesMonthUrl = $canViewNextSalesMonth
    ? $buildDashboardUrl(['sales_month' => $nextSalesMonth->format('Y-m')])
    : null;
$dashboardCacheKey = 'dashboard_metrics_v4_' . $selectedSalesMonthKey;
$dashboardCacheTtl = 45; // seconds
$dashboardDateLabel = date('F j, Y');
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';
$cachedDashboard = $_SESSION[$dashboardCacheKey] ?? null;
$canUseCache = !$forceRefresh
    && is_array($cachedDashboard)
    && isset($cachedDashboard['generated_at'])
    && (time() - (int)$cachedDashboard['generated_at']) < $dashboardCacheTtl;

if ($canUseCache) {
    $today_sales = (float)($cachedDashboard['today_sales'] ?? 0);
    $today_orders = (int)($cachedDashboard['today_orders'] ?? 0);
    $pending_orders = (int)($cachedDashboard['pending_orders'] ?? 0);
    $low_stock = (int)($cachedDashboard['low_stock'] ?? 0);
    $total_revenue = (float)($cachedDashboard['total_revenue'] ?? 0);
    $sales_trend_series = $cachedDashboard['sales_trend_series'] ?? [];
    $recent_orders = $cachedDashboard['recent_orders'] ?? [];
    $status_distribution = $cachedDashboard['status_distribution'] ?? [];
} else {
try {
    // Consolidated order stats in a single query to reduce DB round-trips
    $order_stats = $pdo->query("
        SELECT
            COALESCE(SUM(CASE
                WHEN created_at >= CURDATE()
                 AND created_at < (CURDATE() + INTERVAL 1 DAY)
                 AND order_status IN ('confirmed','processing','shipped','delivered')
                THEN total_amount ELSE 0 END), 0) AS today_sales,
            SUM(CASE
                WHEN created_at >= CURDATE()
                 AND created_at < (CURDATE() + INTERVAL 1 DAY)
                THEN 1 ELSE 0 END) AS today_orders,
            SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) AS pending_orders,
            COALESCE(SUM(CASE
                WHEN order_status IN ('confirmed','processing','shipped','delivered')
                THEN total_amount ELSE 0 END), 0) AS total_revenue
        FROM orders
    ")->fetch(PDO::FETCH_ASSOC) ?: [];

    $today_sales = (float)($order_stats['today_sales'] ?? 0);
    $today_orders = (int)($order_stats['today_orders'] ?? 0);
    $pending_orders = (int)($order_stats['pending_orders'] ?? 0);
    $total_revenue = (float)($order_stats['total_revenue'] ?? 0);

    // Low stock products (< 10 units)
    $low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity < 10 AND status = 'active'")->fetchColumn();

    // Monthly Sales Trend (Selected Month, zero-filled by day)
    $salesTrendStatement = $pdo->prepare("
        SELECT
            DATE(created_at) AS sale_date,
            COALESCE(SUM(total_amount), 0) AS revenue,
            COUNT(*) AS orders_count
        FROM orders
        WHERE created_at >= :sales_month_start
          AND created_at < :sales_month_end_exclusive
          AND order_status IN ('confirmed','processing','shipped','delivered')
        GROUP BY DATE(created_at)
        ORDER BY sale_date ASC
    ");
    $salesTrendStatement->execute([
        ':sales_month_start' => $selectedSalesMonthStart,
        ':sales_month_end_exclusive' => $selectedSalesMonthEndExclusive
    ]);
    $dailySalesRaw = $salesTrendStatement->fetchAll(PDO::FETCH_ASSOC);

    $dailySalesIndex = [];
    foreach ($dailySalesRaw as $row) {
        $dailySalesIndex[$row['sale_date']] = [
            'revenue' => (float)$row['revenue'],
            'orders_count' => (int)$row['orders_count']
        ];
    }

    $sales_trend_series = [];
    for ($cursor = $selectedSalesMonth; $cursor <= $selectedSalesMonthVisibleEndDate; $cursor = $cursor->modify('+1 day')) {
        $dateKey = $cursor->format('Y-m-d');
        $row = $dailySalesIndex[$dateKey] ?? ['revenue' => 0.0, 'orders_count' => 0];
        $sales_trend_series[] = [
            'date' => $dateKey,
            'day_name' => $cursor->format('D'),
            'display_label' => $cursor->format('M j'),
            'day_number' => (int)$cursor->format('j'),
            'revenue' => (float)$row['revenue'],
            'orders_count' => (int)$row['orders_count']
        ];
    }

    // Recent Orders
    $recent_orders = $pdo->query("
        SELECT o.order_id, o.order_number, o.total_amount, o.order_status, o.created_at,
               CONCAT(c.first_name, ' ', c.last_name) as customer_name
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
        ORDER BY o.created_at DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Order Status Distribution
    $status_distribution = $pdo->query("
        SELECT 
            order_status,
            COUNT(*) as count,
            CASE 
                WHEN order_status IN ('delivered', 'shipped') THEN '#10B981'
                WHEN order_status = 'pending' THEN '#F59E0B'
                WHEN order_status IN ('confirmed', 'processing') THEN '#3B82F6'
                WHEN order_status = 'cancelled' THEN '#EF4444'
                ELSE '#6B7280'
            END as color
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY order_status
    ")->fetchAll(PDO::FETCH_ASSOC);

    $_SESSION[$dashboardCacheKey] = [
        'generated_at' => time(),
        'today_sales' => $today_sales,
        'today_orders' => $today_orders,
        'pending_orders' => $pending_orders,
        'low_stock' => $low_stock,
        'total_revenue' => $total_revenue,
        'sales_trend_series' => $sales_trend_series,
        'recent_orders' => $recent_orders,
        'status_distribution' => $status_distribution
    ];

} catch (Exception $e) {
    // Fallback values if queries fail
    $today_sales = $today_orders = $pending_orders = $low_stock = 0;
    $total_revenue = 0;
    $sales_trend_series = $recent_orders = $status_distribution = [];
    error_log("Dashboard query error: " . $e->getMessage());
}
}

// Encode for Chart.js
$sales_trend_series_json = json_encode($sales_trend_series);
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

    .stat-card .icon-box-warning {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
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

    .month-nav-btn {
        border: 1px solid rgba(8, 65, 92, 0.14);
        background: #ffffff;
        color: var(--primary-color);
        transition: all 0.2s ease;
    }

    .month-nav-btn:hover {
        background: rgba(8, 65, 92, 0.06);
        border-color: rgba(8, 65, 92, 0.24);
    }

    .month-nav-btn[aria-disabled="true"] {
        opacity: 0.45;
        cursor: not-allowed;
        pointer-events: none;
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
            <p class="text-gray-600">Here is your MinC Auto Supply store performance for <?= htmlspecialchars($dashboardDateLabel) ?>.</p>
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
                <p class="text-xs text-[#0a5273] mt-2"><?= htmlspecialchars($dashboardDateLabel) ?> • <?= $today_orders ?> orders</p>
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
                <p class="text-3xl font-bold text-[#dc2626]"><?= $low_stock ?></p>
                <p class="text-xs text-[#b91c1c] mt-2">Restock needed</p>
            </div>
            <div class="p-4 icon-box-warning rounded-xl">
                <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card professional-card rounded-xl p-6 hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Revenue</p>
                <p class="text-3xl font-bold text-[#08415c]">₱<?= number_format($total_revenue, 2) ?></p>
                <p class="text-xs text-[#0a5273] mt-2">As of <?= htmlspecialchars($dashboardDateLabel) ?></p>
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
        <div class="flex flex-col gap-4 mb-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-[#08415c] section-title">Sales Trend By Month</h3>
                <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($selectedSalesMonthSubtext) ?></p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                <a href="<?= htmlspecialchars($previousSalesMonthUrl) ?>" class="month-nav-btn inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium">
                    <i class="fas fa-chevron-left text-xs"></i>
                    Previous
                </a>
                <form method="GET" action="dashboard.php" class="flex items-center gap-2">
                    <?php foreach ($_GET as $queryKey => $queryValue): ?>
                        <?php if ($queryKey === 'sales_month' || $queryKey === 'refresh'): ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <input type="hidden" name="<?= htmlspecialchars((string)$queryKey) ?>" value="<?= htmlspecialchars((string)$queryValue) ?>">
                    <?php endforeach; ?>
                    <input
                        type="month"
                        name="sales_month"
                        value="<?= htmlspecialchars($selectedSalesMonthKey) ?>"
                        max="<?= htmlspecialchars($currentSalesMonth->format('Y-m')) ?>"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-[#08415c] focus:border-[#08415c] focus:outline-none focus:ring-2 focus:ring-[#08415c]/20"
                    >
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#08415c] px-4 py-2 text-sm font-medium text-white hover:bg-[#0a5273] transition">
                        View
                    </button>
                </form>
                <?php if ($nextSalesMonthUrl): ?>
                    <a href="<?= htmlspecialchars($nextSalesMonthUrl) ?>" class="month-nav-btn inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium">
                        Next
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                <?php else: ?>
                    <span class="month-nav-btn inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium" aria-disabled="true">
                        Next
                        <i class="fas fa-chevron-right text-xs"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>
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
                                <?= $order['order_status'] == 'delivered' || $order['order_status'] == 'shipped' ? 'bg-green-100 text-green-800' : 
                                   ($order['order_status'] == 'pending' ? 'bg-amber-100 text-amber-800' : 
                                   ($order['order_status'] == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) ?>">
                                <?= ucfirst($order['order_status']) ?>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        return;
    }
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.animation = false;

    const renderCharts = () => {
        // Sales Trend Line Chart
        const salesData = <?= $sales_trend_series_json ?>;
        const salesCanvas = document.getElementById('salesTrendChart');
        if (salesCanvas) {
            const salesCtx = salesCanvas.getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: salesData.map(d => d.display_label),
                    datasets: [{
                        label: 'Revenue',
                        data: salesData.map(d => d.revenue),
                        borderColor: '#08415c',
                        backgroundColor: 'rgba(8, 65, 92, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: salesData.length > 20 ? 2 : 4,
                        pointHoverRadius: salesData.length > 20 ? 4 : 6,
                        pointBackgroundColor: '#08415c',
                        pointBorderColor: '#0a5273'
                    }]
                },
                options: {
                    animation: false,
                    responsive: true,
                    maintainAspectRatio: false,
                    parsing: false,
                    normalized: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (tooltipItems) => {
                                    const point = salesData[tooltipItems[0]?.dataIndex] || null;
                                    return point ? `${point.day_name}, ${point.display_label}` : '';
                                },
                                label: (context) => {
                                    const point = salesData[context.dataIndex] || { orders_count: 0, revenue: 0 };
                                    return `PHP ${Number(point.revenue || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} � ${point.orders_count} order${point.orders_count === 1 ? '' : 's'}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 10
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => {
                                    return 'PHP ' + Number(value || 0).toLocaleString('en-PH', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }
                            }
                        }
                    }
                }
            });
        }

        // Order Status Doughnut Chart
        const statusData = <?= $status_distribution_json ?>;
        const statusCanvas = document.getElementById('statusChart');
        if (statusCanvas) {
            const statusCtx = statusCanvas.getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusData.map(s => s.order_status.charAt(0).toUpperCase() + s.order_status.slice(1)),
                    datasets: [{
                        data: statusData.map(s => s.count),
                        backgroundColor: statusData.map(s => s.color),
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    animation: false,
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    parsing: false,
                    normalized: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    };

    if ('requestIdleCallback' in window) {
        requestIdleCallback(renderCharts, { timeout: 300 });
    } else {
        setTimeout(renderCharts, 0);
    }
});
</script>

<?php
$dashboard_content = ob_get_clean();
$content = $dashboard_content;
include 'app.php'; // This loads your main layout
?>

