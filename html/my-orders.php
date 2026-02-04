<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/MinC_Project/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - MinC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/ca30ddfff9.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #08415c 0%, #0a5273 50%, #08415c 100%); }
        .status-badge { padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <section class="hero-gradient mt-20 py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-2">My Orders</h1>
            <p class="text-blue-100 text-lg">Track your order and payment status</p>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 py-10">
        <div id="loadingState" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-3xl text-[#08415c] mb-3"></i>
            <p class="text-gray-600">Loading your orders...</p>
        </div>

        <div id="emptyState" class="hidden bg-white rounded-xl shadow-lg p-10 text-center">
            <i class="fas fa-box-open text-5xl text-gray-300 mb-4"></i>
            <p class="text-xl text-gray-700 font-semibold mb-2">No orders yet</p>
            <p class="text-gray-500 mb-6">Once you place an order, it will appear here.</p>
            <a href="product.php" class="inline-block bg-[#08415c] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#0a5273] transition">Shop Now</a>
        </div>

        <div id="ordersList" class="hidden grid gap-4"></div>
    </main>

    <?php include 'components/footer.php'; ?>

    <script>
        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
        }

        function formatPeso(amount) {
            return Number(amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        function orderStatusClass(status) {
            const map = {
                pending: 'bg-yellow-100 text-yellow-800',
                confirmed: 'bg-blue-100 text-blue-800',
                processing: 'bg-indigo-100 text-indigo-800',
                shipped: 'bg-purple-100 text-purple-800',
                delivered: 'bg-green-100 text-green-800',
                cancelled: 'bg-red-100 text-red-800'
            };
            return map[status] || 'bg-gray-100 text-gray-700';
        }

        function paymentStatusClass(status) {
            const map = {
                pending: 'bg-yellow-100 text-yellow-800',
                paid: 'bg-green-100 text-green-800',
                failed: 'bg-red-100 text-red-800',
                refunded: 'bg-gray-100 text-gray-700'
            };
            return map[status] || 'bg-gray-100 text-gray-700';
        }

        async function loadOrders() {
            const loading = document.getElementById('loadingState');
            const empty = document.getElementById('emptyState');
            const list = document.getElementById('ordersList');

            try {
                const response = await fetch('../backend/get_user_orders.php', { cache: 'no-store' });
                const data = await response.json();

                loading.classList.add('hidden');

                if (!data.success) {
                    empty.classList.remove('hidden');
                    return;
                }

                const orders = data.orders || [];
                if (!orders.length) {
                    empty.classList.remove('hidden');
                    return;
                }

                list.innerHTML = orders.map((order) => `
                    <article class="bg-white rounded-xl shadow-lg p-5">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-3">
                            <div>
                                <h2 class="text-lg font-bold text-[#08415c]">#${escapeHtml(order.order_number)}</h2>
                                <p class="text-xs text-gray-500">Placed: ${formatDate(order.created_at)}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="status-badge ${orderStatusClass(order.order_status)}">${escapeHtml(order.order_status)}</span>
                                <span class="status-badge ${paymentStatusClass(order.payment_status)}">Payment: ${escapeHtml(order.payment_status)}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                            <div><p class="text-gray-500">Items</p><p class="font-semibold">${order.total_items || 0}</p></div>
                            <div><p class="text-gray-500">Qty</p><p class="font-semibold">${order.total_quantity || 0}</p></div>
                            <div><p class="text-gray-500">Payment Method</p><p class="font-semibold">${escapeHtml(order.payment_method || 'N/A')}</p></div>
                            <div><p class="text-gray-500">Shipping Fee</p><p class="font-semibold">₱${formatPeso(order.shipping_fee)}</p></div>
                            <div><p class="text-gray-500">Total</p><p class="font-bold text-[#08415c]">₱${formatPeso(order.total_amount)}</p></div>
                        </div>

                        ${order.tracking_number ? `<p class="text-sm text-blue-700 mt-3"><i class="fas fa-truck mr-2"></i>Tracking: ${escapeHtml(order.tracking_number)}</p>` : ''}
                        ${order.notes ? `<p class="text-sm text-gray-600 mt-2"><strong>Notes:</strong> ${escapeHtml(order.notes)}</p>` : ''}
                    </article>
                `).join('');

                list.classList.remove('hidden');
            } catch (e) {
                loading.classList.add('hidden');
                empty.classList.remove('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', loadOrders);
    </script>
</body>
</html>

