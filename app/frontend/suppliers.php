<?php
/**
 * Suppliers Management Frontend
 * File: d:\XAMPP\htdocs\pages\MinC_Project\app\frontend\suppliers.php
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
    $_SESSION['error_message'] = 'Access denied. Only admin and employee accounts can manage suppliers.';
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
$custom_title = 'Suppliers Management - MinC Project';

// Check if suppliers table exists, if not create mock data
try {
    $suppliers_query = "
        SELECT 
            supplier_id,
            supplier_name,
            contact_person,
            email,
            phone,
            address,
            city,
            province,
            status,
            created_at
        FROM suppliers
        WHERE status = 'active'
        ORDER BY supplier_name ASC
    ";
    $suppliers_result = $pdo->query($suppliers_query);
    $suppliers = $suppliers_result ? $suppliers_result->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (PDOException $e) {
    // Table might not exist, use empty array
    $suppliers = [];
}

// Custom styles
$additional_styles = '
<style>
    .supplier-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .supplier-card:hover {
        border-color: #08415c;
        box-shadow: 0 8px 20px rgba(8, 65, 92, 0.15);
        transform: translateY(-2px);
    }

    .supplier-row:hover {
        background-color: rgba(8, 65, 92, 0.05);
    }

    .action-btn {
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: scale(1.1);
    }
</style>';

// Suppliers management content
ob_start();
?>

<!-- Page Header -->
<div class="professional-card rounded-xl p-6 mb-6 animate-fadeIn">
    <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div class="mb-4 md:mb-0">
            <h2 class="text-2xl font-bold text-[#08415c] mb-2 flex items-center">
                <i class="fas fa-truck text-teal-600 mr-3"></i>
                Suppliers Management
            </h2>
            <p class="text-gray-600">
                Manage supplier information and contacts
            </p>
        </div>
        <button onclick="openAddSupplierModal()" class="px-4 py-2 bg-gradient-to-r from-[#08415c] to-[#0a5273] text-white rounded-lg hover:shadow-lg transition flex items-center">
            <i class="fas fa-plus mr-2"></i>Add Supplier
        </button>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="professional-card rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Suppliers</p>
                <p class="text-2xl font-bold text-[#08415c]"><?= count($suppliers) ?></p>
            </div>
            <div class="p-3 bg-gradient-to-br from-[#08415c] to-[#0a5273] text-white rounded-lg">
                <i class="fas fa-people-carry text-xl"></i>
            </div>
        </div>
    </div>

    <div class="professional-card rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Active Suppliers</p>
                <p class="text-2xl font-bold text-green-600"><?= count(array_filter($suppliers, function($s) { return $s['status'] === 'active'; })) ?></p>
            </div>
            <div class="p-3 bg-gradient-to-br from-green-500 to-green-700 text-white rounded-lg">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
    </div>

    <div class="professional-card rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Suppliers in City</p>
                <p class="text-2xl font-bold text-blue-600"><?= count(array_unique(array_column($suppliers, 'city'))) ?></p>
            </div>
            <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-lg">
                <i class="fas fa-map-marker-alt text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Suppliers List -->
<div class="professional-card rounded-xl p-6">
    <h3 class="text-lg font-bold text-[#08415c] mb-4">Supplier Directory</h3>
    
    <?php if (!empty($suppliers)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Supplier Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Contact Person</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr class="supplier-row hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($supplier['supplier_name']) ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($supplier['contact_person'] ?? 'N/A') ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($supplier['email'] ?? 'N/A') ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($supplier['phone'] ?? 'N/A') ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($supplier['city'] ?? 'N/A') ?>, <?= htmlspecialchars($supplier['province'] ?? 'N/A') ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $supplier['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= ucfirst($supplier['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button class="action-btn text-blue-600 hover:text-blue-900 transition">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn text-red-600 hover:text-red-900 transition">
                                    <i class="fas fa-trash"></i>
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
            <p class="text-gray-500">No suppliers added yet</p>
            <button onclick="openAddSupplierModal()" class="mt-4 px-4 py-2 bg-[#08415c] text-white rounded-lg hover:bg-[#0a5273] transition">
                Add First Supplier
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Add Supplier Modal -->
<div id="addSupplierModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-[#08415c] mb-4">Add New Supplier</h3>
        <form id="addSupplierForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Name *</label>
                <input type="text" name="supplier_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#08415c] focus:border-transparent" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                <input type="text" name="contact_person" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#08415c] focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#08415c] focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="tel" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#08415c] focus:border-transparent">
            </div>
            <div class="flex space-x-3 justify-end mt-6">
                <button type="button" onclick="closeAddSupplierModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-[#08415c] text-white rounded-lg hover:bg-[#0a5273] transition">
                    Add Supplier
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddSupplierModal() {
        document.getElementById('addSupplierModal').classList.remove('hidden');
    }

    function closeAddSupplierModal() {
        document.getElementById('addSupplierModal').classList.add('hidden');
    }

    document.getElementById('addSupplierForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        showAlertModal('Supplier management module is ready. Backend API endpoints need to be created for full functionality.', 'info', 'Suppliers');
        closeAddSupplierModal();
    });
</script>

<?php
$suppliers_content = ob_get_clean();
$content = $suppliers_content;
$current_page = 'suppliers';
include 'app.php';
?>
