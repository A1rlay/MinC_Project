<?php
session_start();
require_once '../database/connect_database.php';

// Check if cart has items
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

// Get cart
if ($user_id) {
    $stmt = mysqli_prepare($connection, "SELECT cart_id FROM cart WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
} else {
    $stmt = mysqli_prepare($connection, "SELECT cart_id FROM cart WHERE session_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $session_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header('Location: user-cart.php');
    exit;
}

$cart_id = mysqli_fetch_assoc($result)['cart_id'];

// Get cart items count
$stmt = mysqli_prepare($connection, "SELECT COUNT(*) as count FROM cart_items WHERE cart_id = ?");
mysqli_stmt_bind_param($stmt, "i", $cart_id);
mysqli_stmt_execute($stmt);
$count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];

if ($count === 0) {
    header('Location: user-cart.php');
    exit;
}

// Get user data if logged in
$user_data = null;
if ($user_id) {
    $stmt = mysqli_prepare($connection, "SELECT fname, lname, email, contact_num FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MinC Computer Parts</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/ca30ddfff9.js" crossorigin="anonymous"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .hero-gradient {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 50%, #08415c 100%);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(8, 65, 92, 0.4);
        }

        .btn-primary-custom:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 1rem;
            position: relative;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }

        .step:first-child::before {
            left: 50%;
        }

        .step:last-child::before {
            right: 50%;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 1;
            margin-bottom: 0.5rem;
        }

        .step.active .step-number {
            background: #08415c;
            color: white;
        }

        .step.completed .step-number {
            background: #10b981;
            color: white;
        }

        #loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
        }

        #loader.active {
            display: flex;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Loader -->
    <div id="loader" class="flex items-center justify-center">
        <div class="text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-[#08415c] mx-auto mb-4"></div>
            <p class="text-[#08415c] font-semibold">Processing...</p>
        </div>
    </div>

    <!-- Navigation Component -->
    <?php include 'components/navbar.php'; ?>

    <!-- Checkout Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Progress Steps -->
        <div class="step-indicator mb-8">
            <div class="step active" id="step1-indicator">
                <div class="step-number">1</div>
                <div class="text-sm font-medium">Contact Info</div>
            </div>
            <div class="step" id="step2-indicator">
                <div class="step-number">2</div>
                <div class="text-sm font-medium">Shipping</div>
            </div>
            <div class="step" id="step3-indicator">
                <div class="step-number">3</div>
                <div class="text-sm font-medium">Payment</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2">
                <form id="checkoutForm" class="bg-white rounded-xl shadow-lg p-6">
                    
                    <!-- Step 1: Contact Information -->
                    <div id="step1" class="form-section active">
                        <h2 class="text-2xl font-bold text-[#08415c] mb-6">Contact Information</h2>
                        
                        <?php if (!$user_id): ?>
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-info-circle text-[#08415c] mr-2"></i>
                                Already have an account? 
                                <a href="javascript:void(0)" onclick="openLoginModal()" class="text-[#08415c] font-semibold hover:underline">Sign in</a>
                                to checkout faster
                            </p>
                        </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">First Name *</label>
                                <input type="text" id="firstName" required 
                                       value="<?php echo $user_data ? htmlspecialchars($user_data['fname']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Last Name *</label>
                                <input type="text" id="lastName" required 
                                       value="<?php echo $user_data ? htmlspecialchars($user_data['lname']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-gray-700 font-medium mb-2">Email Address *</label>
                            <input type="email" id="email" required 
                                   value="<?php echo $user_data ? htmlspecialchars($user_data['email']) : ''; ?>"
                                   <?php echo $user_data ? 'readonly' : ''; ?>
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c] <?php echo $user_data ? 'bg-gray-50' : ''; ?>">
                            <p class="text-sm text-gray-500 mt-1">Order confirmation will be sent to this email</p>
                        </div>

                        <div class="mt-4">
                            <label class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                            <input type="tel" id="phone" required 
                                   value="<?php echo $user_data && $user_data['contact_num'] ? htmlspecialchars($user_data['contact_num']) : ''; ?>"
                                   placeholder="09XX XXX XXXX"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="button" onclick="nextStep(2)" class="btn-primary-custom text-white px-8 py-3 rounded-lg font-semibold">
                                Continue to Shipping
                                <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Shipping Information -->
                    <div id="step2" class="form-section">
                        <h2 class="text-2xl font-bold text-[#08415c] mb-6">Shipping Address</h2>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Complete Address *</label>
                            <textarea id="address" required rows="3"
                                      placeholder="House/Unit No., Street Name, Barangay"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">City/Municipality *</label>
                                <input type="text" id="city" required 
                                       placeholder="e.g., Angeles City"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Province *</label>
                                <select id="province" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                                    <option value="">Select Province</option>
                                    <option value="Pampanga">Pampanga</option>
                                    <option value="Tarlac">Tarlac</option>
                                    <option value="Bulacan">Bulacan</option>
                                    <option value="Nueva Ecija">Nueva Ecija</option>
                                    <option value="Bataan">Bataan</option>
                                    <option value="Zambales">Zambales</option>
                                    <option value="Metro Manila">Metro Manila</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-gray-700 font-medium mb-2">Postal Code</label>
                            <input type="text" id="postalCode" 
                                   placeholder="e.g., 2009"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                        </div>

                        <div class="mt-6">
                            <label class="block text-gray-700 font-medium mb-2">Delivery Notes (Optional)</label>
                            <textarea id="notes" rows="2"
                                      placeholder="Special instructions for delivery..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]"></textarea>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <button type="button" onclick="prevStep(1)" class="bg-gray-100 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back
                            </button>
                            <button type="button" onclick="nextStep(3)" class="btn-primary-custom text-white px-8 py-3 rounded-lg font-semibold">
                                Continue to Payment
                                <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Payment Method -->
                    <div id="step3" class="form-section">
                        <h2 class="text-2xl font-bold text-[#08415c] mb-6">Payment Method</h2>

                        <div class="space-y-4">
                            <label class="block p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-[#08415c] transition">
                                <input type="radio" name="paymentMethod" value="cod" checked class="mr-3">
                                <span class="font-semibold"><i class="fas fa-money-bill-wave text-green-600 mr-2"></i>Cash on Delivery (COD)</span>
                                <p class="text-sm text-gray-600 ml-8 mt-1">Pay when you receive your order</p>
                            </label>

                            <label class="block p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-[#08415c] transition">
                                <input type="radio" name="paymentMethod" value="bank_transfer" class="mr-3">
                                <span class="font-semibold"><i class="fas fa-university text-blue-600 mr-2"></i>Bank Transfer</span>
                                <p class="text-sm text-gray-600 ml-8 mt-1">Transfer to our bank account</p>
                            </label>

                            <label class="block p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-[#08415c] transition">
                                <input type="radio" name="paymentMethod" value="gcash" class="mr-3">
                                <span class="font-semibold"><i class="fas fa-mobile-alt text-blue-500 mr-2"></i>GCash</span>
                                <p class="text-sm text-gray-600 ml-8 mt-1">Pay via GCash mobile wallet</p>
                            </label>

                            <label class="block p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-[#08415c] transition">
                                <input type="radio" name="paymentMethod" value="paymaya" class="mr-3">
                                <span class="font-semibold"><i class="fas fa-credit-card text-green-500 mr-2"></i>PayMaya</span>
                                <p class="text-sm text-gray-600 ml-8 mt-1">Pay via PayMaya digital wallet</p>
                            </label>
                        </div>

                        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                For Bank Transfer, GCash, and PayMaya, payment instructions will be sent to your email after placing the order.
                            </p>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <button type="button" onclick="prevStep(2)" class="bg-gray-100 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back
                            </button>
                            <button type="submit" id="placeOrderBtn" class="btn-primary-custom text-white px-8 py-3 rounded-lg font-semibold">
                                <i class="fas fa-check mr-2"></i>
                                Place Order
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Order Summary (Sticky) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-4">
                    <h3 class="text-xl font-bold text-[#08415c] mb-4">Order Summary</h3>
                    
                    <div id="orderItems" class="space-y-3 mb-4 max-h-60 overflow-y-auto">
                        <!-- Will be populated by JavaScript -->
                    </div>

                    <hr class="my-4">

                    <div class="space-y-2">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal:</span>
                            <span class="font-semibold">₱<span id="summarySubtotal">0.00</span></span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Shipping:</span>
                            <span class="font-semibold">₱<span id="summaryShipping">0.00</span></span>
                        </div>
                        <hr class="my-2">
                        <div class="flex justify-between text-lg font-bold text-[#08415c]">
                            <span>Total:</span>
                            <span>₱<span id="summaryTotal">0.00</span></span>
                        </div>
                    </div>

                    <div class="mt-6 p-3 bg-green-50 rounded-lg">
                        <p class="text-sm text-green-800">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Your payment information is secure
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal (if needed) -->
    <div id="loginModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative">
            <button onclick="closeLoginModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>

            <h2 class="text-3xl font-bold mb-6 text-[#08415c]">Sign In</h2>
            <form onsubmit="handleLogin(event)">
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Email</label>
                    <input type="email" id="loginEmail" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">Password</label>
                    <input type="password" id="loginPassword" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                </div>
                <button type="submit" class="w-full btn-primary-custom text-white py-3 rounded-lg font-semibold">
                    Login
                </button>
            </form>
        </div>
    </div>

    <script>
        // Global variables
        let cartItems = [];
        let subtotal = 0;
        const SHIPPING_FEE = 150;
        const FREE_SHIPPING_THRESHOLD = 5000;
        let currentStep = 1;

        // Format currency
        function formatPeso(amount) {
            return parseFloat(amount).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Escape HTML
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

        // Load cart data
        async function loadCart() {
            try {
                const response = await fetch('../backend/cart/cart_get.php');
                const data = await response.json();

                if (data.success) {
                    cartItems = data.cart_items || [];
                    subtotal = parseFloat(data.subtotal) || 0;
                    
                    displayOrderSummary();
                } else {
                    window.location.href = 'user-cart.php';
                }
            } catch (error) {
                console.error('Error loading cart:', error);
                window.location.href = 'user-cart.php';
            }
        }

        // Display order summary
        function displayOrderSummary() {
            const container = document.getElementById('orderItems');
            
            container.innerHTML = cartItems.map(item => `
                <div class="flex gap-3">
                    <img src="../assets/images/products/${item.product_image || 'placeholder.jpg'}" 
                         alt="${escapeHtml(item.product_name)}" 
                         class="w-16 h-16 object-cover rounded"
                         onerror="this.src='../assets/images/website-images/placeholder.jpg'">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-800">${escapeHtml(item.product_name)}</p>
                        <p class="text-xs text-gray-500">Qty: ${item.quantity}</p>
                        <p class="text-sm font-bold text-[#08415c]">₱${formatPeso(item.item_total)}</p>
                    </div>
                </div>
            `).join('');

            updateSummary();
        }

        // Update summary
        function updateSummary() {
            const shippingFee = subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_FEE;
            const total = subtotal + shippingFee;

            document.getElementById('summarySubtotal').textContent = formatPeso(subtotal);
            document.getElementById('summaryShipping').textContent = shippingFee === 0 ? 'FREE' : formatPeso(shippingFee);
            document.getElementById('summaryTotal').textContent = formatPeso(total);
        }

        // Next step
        function nextStep(step) {
            // Validate current step
            if (step === 2) {
                const firstName = document.getElementById('firstName').value.trim();
                const lastName = document.getElementById('lastName').value.trim();
                const email = document.getElementById('email').value.trim();
                const phone = document.getElementById('phone').value.trim();

                if (!firstName || !lastName || !email || !phone) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Information',
                        text: 'Please fill in all required fields',
                        confirmButtonColor: '#08415c'
                    });
                    return;
                }

                // Validate email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Email',
                        text: 'Please enter a valid email address',
                        confirmButtonColor: '#08415c'
                    });
                    return;
                }

                // Validate phone (Philippine format)
                const phoneRegex = /^(09|\+639)\d{9}$/;
                if (!phoneRegex.test(phone.replace(/\s/g, ''))) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Phone Number',
                        text: 'Please enter a valid Philippine mobile number',
                        confirmButtonColor: '#08415c'
                    });
                    return;
                }
            }

            if (step === 3) {
                const address = document.getElementById('address').value.trim();
                const city = document.getElementById('city').value.trim();
                const province = document.getElementById('province').value;

                if (!address || !city || !province) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Information',
                        text: 'Please fill in all required shipping fields',
                        confirmButtonColor: '#08415c'
                    });
                    return;
                }
            }

            // Update step indicators
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.getElementById(`step${currentStep}-indicator`).classList.remove('active');
            document.getElementById(`step${currentStep}-indicator`).classList.add('completed');

            currentStep = step;

            document.getElementById(`step${currentStep}`).classList.add('active');
            document.getElementById(`step${currentStep}-indicator`).classList.add('active');

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Previous step
        function prevStep(step) {
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.getElementById(`step${currentStep}-indicator`).classList.remove('active');

            currentStep = step;

            document.getElementById(`step${currentStep}`).classList.add('active');
            document.getElementById(`step${currentStep}-indicator`).classList.add('active');
            document.getElementById(`step${currentStep}-indicator`).classList.remove('completed');

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Handle checkout form submission
        document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();
            const city = document.getElementById('city').value.trim();
            const province = document.getElementById('province').value;
            const postalCode = document.getElementById('postalCode').value.trim();
            const notes = document.getElementById('notes').value.trim();
            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;

            // Confirm order
            const result = await Swal.fire({
                title: 'Confirm Order',
                html: `
                    <div class="text-left">
                        <p class="mb-2"><strong>Name:</strong> ${firstName} ${lastName}</p>
                        <p class="mb-2"><strong>Email:</strong> ${email}</p>
                        <p class="mb-2"><strong>Phone:</strong> ${phone}</p>
                        <p class="mb-2"><strong>Address:</strong> ${address}, ${city}, ${province}</p>
                        <p class="mb-2"><strong>Payment:</strong> ${paymentMethod.toUpperCase()}</p>
                        <p class="mt-4 text-lg"><strong>Total: ₱${document.getElementById('summaryTotal').textContent}</strong></p>
</div>
`,
icon: 'question',
showCancelButton: true,
confirmButtonColor: '#08415c',
cancelButtonColor: '#d33',
confirmButtonText: 'Yes, place order',
cancelButtonText: 'Cancel'
});
if (!result.isConfirmed) return;

        // Show loader
        document.getElementById('loader').classList.add('active');
        document.getElementById('placeOrderBtn').disabled = true;

        try {
            const response = await fetch('../backend/checkout/process_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    customer: {
                        first_name: firstName,
                        last_name: lastName,
                        email: email,
                        phone: phone
                    },
                    shipping: {
                        address: address,
                        city: city,
                        province: province,
                        postal_code: postalCode
                    },
                    payment_method: paymentMethod,
                    notes: notes
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Order Placed Successfully!',
                    html: `
                        <p class="mb-4">Your order number is: <strong>${data.order_number}</strong></p>
                        <p>A confirmation email has been sent to ${email}</p>
                    `,
                    confirmButtonColor: '#08415c',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = `order-success.php?order=${data.order_number}`;
                });
            } else {
                throw new Error(data.message || 'Failed to place order');
            }
        } catch (error) {
            console.error('Checkout error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Order Failed',
                text: error.message || 'An error occurred while placing your order. Please try again.',
                confirmButtonColor: '#08415c'
            });
        } finally {
            document.getElementById('loader').classList.remove('active');
            document.getElementById('placeOrderBtn').disabled = false;
        }
    });

    // Login modal functions
    function openLoginModal() {
        document.getElementById('loginModal').classList.remove('hidden');
    }

    function closeLoginModal() {
        document.getElementById('loginModal').classList.add('hidden');
    }

    async function handleLogin(e) {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        
        Swal.fire({
            title: 'Logging in...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        try {
            const response = await fetch('../backend/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Login Successful!',
                    text: 'Redirecting...',
                    confirmButtonColor: '#08415c',
                    timer: 1000
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: data.message,
                    confirmButtonColor: '#08415c'
                });
            }
        } catch (error) {
            console.error('Login error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during login.',
                confirmButtonColor: '#08415c'
            });
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        loadCart();
    });
</script>
</body>
</html>