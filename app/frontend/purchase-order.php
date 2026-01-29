<?php
/**
 * Purchase Order Management Frontend
 * File: d:\XAMPP\htdocs\pages\MinC_Project\app\frontend\purchase-order.php
 */

include_once '../../backend/auth.php';
include_once '../../database/connect_database.php';

// Validate session
$validation = validateSession();
if (!$validation['valid']) {
    header('Location: ../../index.php?error=' . $validation['reason']);
    exit;
}

// Check if user has permission
if (!isITStaff() && !isOwner()) {
    $_SESSION['error_message'] = 'Access denied. Only IT Personnel and Owner can manage purchase orders.';
    header('Location: dashboard.php');
    exit;
}

// Get current user data
$user_data = [
    'id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['full_name'] ?? $_SESSION['fname'] ?? 'Guest User',
    'user_type' => $_SESSION['user_type_name'] ?? 'User'
];

// Set custom title
$custom_title = 'Purchase Orders - MinC Project';

// Fetch purchase orders
try {
    $po_query = "
        SELECT 
            po_id,
            po_number,
            supplier_id,
            order_date,
            expected_delivery_date,
            total_amount,
            status,
            created_at
        FROM purchase_orders
        ORDER BY order_date DESC
        LIMIT 100
    ";
    $po_result = $pdo->query($po_query);
    $purchase_orders = $po_result ? $po_result->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (PDOException $e) {
    // Table might not exist
    $purchase_orders = [];
}

// Get statistics
$total_orders = count($purchase_orders);
$pending_orders = count(array_filter($purchase_orders, function($o) { return $o['status'] === 'pending'; }));
$completed_orders = count(array_filter($purchase_orders, function($o) { return $o['status'] === 'completed'; }));
$total_po_amount = array_sum(array_column($purchase_orders, 'total_amount'));

// Custom styles
$additional_styles = '
<style>
    .po-status-pending {
        background-color: #FEF3C7;
        color: #92400E;
    }

    .po-status-completed {
        background-color: #D1FAE5;
        color: #065F46;
    }

    .po-status-cancelled {
        background-color: #FEE2E2;
        color: #991B1B;
    }

    .po-row:hover {
        background-color: rgba(8, 65, 92, 0.05);
    }

    .po-header {
        background: linear-gradient(135deg, rgba(8, 65, 92, 0.1) 0%, rgba(10, 82, 115, 0.1) 100%);
    }
</style>';

// Purchase order content
ob_start();
?>

<!-- Page Header -->
<div class="professional-card rounded-xl p-6 mb-6 animate-fadeIn">
    <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div class="mb-4 md:mb-0">
            <h2 class="text-2xl font-bold text-[#08415c] mb-2 flex items-center">
                <i class="fas fa-file-invoice-dollar text-teal-600 mr-3"></i>
                Purchase Orders
            </h2>
            <p class="text-gray-600">
                Manage supplier purchase orders and deliveries
            </p>
        </div>
        <button onclick="openCreatePOModal()" class="px-4 py-2 bg-gradient-to-r from-[#08415c] to-[#0a5273] text-white rounded-lg hover:shadow-lg transition flex items-center">
            <i class="fas fa-plus mr-2"></i>Create PO
        </button>
    </div>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="professional-card rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Orders</p>
                <p class="text-2xl font-bold text-[#08415c]"><?= $total_orders ?></p>
            </div>
            <div class="p-3 bg-gradient-to-br from-[#08415c] to-[#0a5273] text-white rounded-lg">
                <i class="fas fa-clipboard-list text-xl"></i>
            </div>
        </div>
    </div>

    <div class="professional-card rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Pending</p>
                <p class="text-2xl font-bold text-amber-600"><?= $pending_orders ?></p>
            </div>
            <div class="p-3 bg-gradient-to-br from-amber-500 to-amber-700 text-white rounded-lg">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
        </div>
    </div>

    <div class="professional-card rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Completed</p>
                <p class="text-2xl font-bold text-green-600"><?= $completed_orders ?></p>
            </div>
            <div class="p-3 bg-gradient-to-br from-green-500 to-green-700 text-white rounded-lg">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
    </div>

    <div class="professional-card rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Amount</p>
                <p class="text-2xl font-bold text-[#08415c]">₱<?= number_format($total_po_amount, 0) ?></p>
            </div>
            <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-700 text-white rounded-lg">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Purchase Orders Table -->
<div class="professional-card rounded-xl p-6">
    <h3 class="text-lg font-bold text-[#08415c] mb-4">Purchase Orders List</h3>
    
    <?php if (!empty($purchase_orders)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="po-header">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">PO Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Order Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Expected Delivery</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($purchase_orders as $po): ?>
                        <tr class="po-row hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-[#08415c]"><?= htmlspecialchars($po['po_number']) ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-600"><?= date('M d, Y', strtotime($po['order_date'])) ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-600"><?= date('M d, Y', strtotime($po['expected_delivery_date'])) ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-semibold text-gray-900">₱<?= number_format($po['total_amount'], 2) ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    <?php
                                    if ($po['status'] === 'pending') {
                                        echo 'po-status-pending';
                                    } elseif ($po['status'] === 'completed') {
                                        echo 'po-status-completed';
                                    } else {
                                        echo 'po-status-cancelled';
                                    }
                                    ?>">
                                    <?= ucfirst($po['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button class="text-blue-600 hover:text-blue-900 transition">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="text-green-600 hover:text-green-900 transition">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500">No purchase orders found</p>
            <button onclick="openCreatePOModal()" class="mt-4 px-4 py-2 bg-[#08415c] text-white rounded-lg hover:bg-[#0a5273] transition">
                Create First PO
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Create PO Modal -->
<div id="createPOModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl p-6 max-w-lg w-full mx-4 max-h-96 overflow-y-auto">
        <h3 class="text-lg font-bold text-[#08415c] mb-4">Create Purchase Order</h3>
        <form id="createPOForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                <select name="supplier_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#08415c] focus:border-transparent" required>
                    <option value="">Select a supplier...</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Order Date *</label>
                <input type="date" name="order_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#08415c] focus:border-transparent" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery Date *</label>
                <input type="date" name="delivery_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#08415c] focus:border-transparent" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount *</label>
                <input type="number" name="total_amount" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#08415c] focus:border-transparent" required>
            </div>
            <div class="flex space-x-3 justify-end mt-6">
                <button type="button" onclick="closeCreatePOModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-[#08415c] text-white rounded-lg hover:bg-[#0a5273] transition">
                    Create PO
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreatePOModal() {
        document.getElementById('createPOModal').classList.remove('hidden');
    }

    function closeCreatePOModal() {
        document.getElementById('createPOModal').classList.add('hidden');
    }

    document.getElementById('createPOForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        alert('Purchase order creation is ready. Backend API endpoints need to be created for full functionality.');
        closeCreatePOModal();
    });
</script>

<?php
$purchase_order_content = ob_get_clean();
$content = $purchase_order_content;
$current_page = 'purchase-order';
include 'app.php';
?>
