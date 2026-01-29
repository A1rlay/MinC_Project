<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - MinC Computer Parts</title>

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

        .nav-link-custom {
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link-custom:hover {
            color: #08415c;
        }

        .nav-link-custom::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: #08415c;
            transition: width 0.3s ease;
        }

        .nav-link-custom:hover::after {
            width: 100%;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(8, 65, 92, 0.4);
        }

        .product-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(8, 65, 92, 0.3);
            border-color: #08415c;
        }

        .category-badge {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
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

        .quantity-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            border-color: #08415c;
            background: #08415c;
            color: white;
        }

        .main-image {
            max-height: 500px;
            object-fit: contain;
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Loader -->
    <div id="loader" class="flex items-center justify-center">
        <div class="text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-[#08415c] mx-auto mb-4"></div>
            <p class="text-[#08415c] font-semibold">Loading product...</p>
        </div>
    </div>

    <!-- Navigation Component -->
    <?php include 'components/navbar.php'; ?>

    <!-- Breadcrumb -->
    <section class="bg-gray-100 mt-20 py-6 px-4">
        <div class="max-w-7xl mx-auto">
            <nav class="text-gray-600 text-sm" id="breadcrumb">
                <a href="../index.php" class="hover:text-[#08415c] transition">Home</a>
                <span class="mx-2">/</span>
                <a href="product.php" class="hover:text-[#08415c] transition">Products</a>
                <span class="mx-2">/</span>
                <span class="text-gray-800" id="breadcrumb-product">Loading...</span>
            </nav>
        </div>
    </section>

    <!-- Product Detail Section -->
    <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div id="product-container" class="grid md:grid-cols-2 gap-12 mb-16">
                <!-- Content will be loaded dynamically -->
            </div>

            <!-- Related Products -->
            <div id="related-products-section" class="hidden">
                <h2 class="text-3xl font-bold text-[#08415c] mb-8">Related Products</h2>
                <div id="related-products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Related products will be loaded here -->
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Component -->
    <?php include 'components/footer.php'; ?>

    <!-- Login Modal (Same as product.php) -->
    <div id="loginModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative">
            <button onclick="closeLoginModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>

            <div id="loginForm">
                <h2 class="text-3xl font-bold mb-6 text-[#08415c]">Welcome Back</h2>
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
                <p class="text-center mt-6 text-gray-600">
                    Don't have an account? 
                    <button onclick="showRegister()" class="text-[#08415c] font-semibold hover:text-[#0a5273]">Register</button>
                </p>
            </div>

            <div id="registerForm" class="hidden">
                <h2 class="text-3xl font-bold mb-6 text-[#08415c]">Create Account</h2>
                <form onsubmit="handleRegister(event)">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">First Name</label>
                        <input type="text" id="registerFname" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Last Name</label>
                        <input type="text" id="registerLname" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Email</label>
                        <input type="email" id="registerEmail" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Password</label>
                        <input type="password" id="registerPassword" required minlength="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                    </div>
                    <button type="submit" class="w-full btn-primary-custom text-white py-3 rounded-lg font-semibold">
                        Register
                    </button>
                </form>
                <p class="text-center mt-6 text-gray-600">
                    Already have an account? 
                    <button onclick="showLogin()" class="text-[#08415c] font-semibold hover:text-[#0a5273]">Login</button>
                </p>
            </div>
        </div>
    </div>

<script>
    // Global variables
    let currentProduct = null;
    let quantity = 1;

    // Cart initialization
    function initializeCart() {
        fetch('../backend/cart/cart_get.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.cart_count);
                }
            })
            .catch(error => console.error('Error loading cart:', error));
    }

    // Get URL parameter
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        const results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Format currency to Philippine Peso
    function formatPeso(amount) {
        return '₱' + parseFloat(amount).toLocaleString('en-PH', {
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

    // Load product details
    async function loadProductDetail() {
        showLoader();
        
        const productId = getUrlParameter('id');

        if (!productId) {
            hideLoader();
            showError('No product specified');
            return;
        }

        try {
            const response = await fetch(`../backend/get_product_detail.php?product_id=${productId}`);
            const data = await response.json();

            if (data.success) {
                currentProduct = data.product;
                displayProductDetail(data.product);
                displayRelatedProducts(data.related_products);
                updateBreadcrumb(data.product);
            } else {
                showError(data.message || 'Product not found');
            }
        } catch (error) {
            console.error('Error loading product:', error);
            showError('An error occurred while loading product details');
        } finally {
            hideLoader();
        }
    }

    // Display product details
    function displayProductDetail(product) {
        const imagePath = product.product_image 
            ? `../Assets/images/products/${product.product_image}` 
            : '../Assets/images/website-images/placeholder.svg';

        const stockBadge = product.stock_quantity > 0 
            ? `<span class="text-green-600 font-semibold"><i class="fas fa-check-circle mr-1"></i>In Stock (${product.stock_quantity} available)</span>`
            : `<span class="text-red-600 font-semibold"><i class="fas fa-times-circle mr-1"></i>Out of Stock</span>`;

        const container = document.getElementById('product-container');
        container.innerHTML = `
            <!-- Product Image -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <img src="${imagePath}" 
                     alt="${escapeHtml(product.product_name)}" 
                     class="w-full main-image rounded-lg"
                     onerror="this.src='../Assets/images/website-images/placeholder.svg'">
            </div>

            <!-- Product Info -->
            <div>
                <div class="mb-4">
                    <span class="category-badge text-white px-4 py-2 rounded-full text-sm font-semibold inline-block mb-4">
                        ${escapeHtml(product.product_line_name)}
                    </span>
                    ${product.is_featured ? '<span class="bg-yellow-500 text-white px-4 py-2 rounded-full text-sm font-semibold inline-block mb-4 ml-2"><i class="fas fa-star mr-1"></i>Featured</span>' : ''}
                </div>

                <h1 class="text-4xl font-bold text-[#08415c] mb-4">${escapeHtml(product.product_name)}</h1>
                
                ${product.product_code ? `<p class="text-gray-600 mb-4">Product Code: <span class="font-semibold">${escapeHtml(product.product_code)}</span></p>` : ''}

                <div class="mb-6">
                    ${stockBadge}
                </div>

                <div class="mb-6">
                    <span class="text-5xl font-bold text-[#08415c]">${formatPeso(product.price)}</span>
                </div>

                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-3">Product Description</h3>
                    <p class="text-gray-700 leading-relaxed">
                        ${escapeHtml(product.product_description || 'High-quality auto part designed for optimal performance and durability.')}
                    </p>
                </div>

                <!-- Quantity Selector -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-3">Quantity</label>
                    <div class="flex items-center space-x-4">
                        <button onclick="decreaseQuantity()" class="quantity-btn rounded-lg font-bold text-xl">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="quantity" value="1" min="1" max="${product.stock_quantity}" 
                               class="w-20 text-center text-xl font-bold border-2 border-gray-300 rounded-lg py-2"
                               onchange="updateQuantity(this.value)">
                        <button onclick="increaseQuantity(${product.stock_quantity})" class="quantity-btn rounded-lg font-bold text-xl">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-4">
                    ${product.stock_quantity > 0 ? `
                    <button onclick="addToCart()" class="w-full btn-primary-custom text-white py-4 rounded-lg font-bold text-lg">
                        <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
                    </button>
                    <button onclick="buyNow()" class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-lg font-bold text-lg transition">
                        <i class="fas fa-bolt mr-2"></i>Buy Now
                    </button>
                    ` : `
                    <button disabled class="w-full bg-gray-400 text-white py-4 rounded-lg font-bold text-lg cursor-not-allowed">
                        <i class="fas fa-times-circle mr-2"></i>Out of Stock
                    </button>
                    `}
                </div>

                <!-- Additional Info -->
                <div class="mt-8 border-t pt-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-shield-alt text-[#08415c] mr-2"></i>
                            Quality Guaranteed
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-shipping-fast text-[#08415c] mr-2"></i>
                            Fast Shipping
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-undo text-[#08415c] mr-2"></i>
                            Easy Returns
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-headset text-[#08415c] mr-2"></i>
                            24/7 Support
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Display related products
    function displayRelatedProducts(products) {
        if (!products || products.length === 0) {
            return;
        }

        const section = document.getElementById('related-products-section');
        const grid = document.getElementById('related-products-grid');
        
        section.classList.remove('hidden');
        grid.innerHTML = '';

        products.forEach(product => {
            const imagePath = product.product_image 
                ? `../Assets/images/products/${product.product_image}` 
                : '../Assets/images/website-images/placeholder.svg';

            const card = document.createElement('div');
            card.className = 'bg-white rounded-xl shadow-lg overflow-hidden product-card cursor-pointer';
            card.onclick = () => window.location.href = `product_detail.php?id=${product.product_id}`;
            card.innerHTML = `
                <div class="relative h-48 bg-gray-100 overflow-hidden">
                    <img src="${imagePath}" 
                         alt="${escapeHtml(product.product_name)}" 
                         class="w-full h-full object-cover"
                         onerror="this.src='../Assets/images/website-images/placeholder.svg'">
                </div>
                <div class="p-4">
                    <h4 class="text-lg font-bold text-[#08415c] mb-2 line-clamp-2">${escapeHtml(product.product_name)}</h4>
                    <p class="text-2xl font-bold text-[#08415c]">${formatPeso(product.price)}</p>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    // Update breadcrumb
    function updateBreadcrumb(product) {
        const breadcrumb = document.getElementById('breadcrumb');
        breadcrumb.innerHTML = `
            <a href="../index.php" class="hover:text-[#08415c] transition">Home</a>
            <span class="mx-2">/</span>
            <a href="product.php" class="hover:text-[#08415c] transition">Products</a>
            <span class="mx-2">/</span>
            <a href="product.php?id=${product.category_id}" class="hover:text-[#08415c] transition">${escapeHtml(product.category_name)}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800">${escapeHtml(product.product_name)}</span>
        `;
    }

    // Quantity controls
    function increaseQuantity(max) {
        if (quantity < max) {
            quantity++;
            document.getElementById('quantity').value = quantity;
        }
    }

    function decreaseQuantity() {
        if (quantity > 1) {
            quantity--;
            document.getElementById('quantity').value = quantity;
        }
    }

    function updateQuantity(value) {
        const val = parseInt(value);
        if (val > 0 && val <= currentProduct.stock_quantity) {
            quantity = val;
        } else {
            document.getElementById('quantity').value = quantity;
        }
    }

    // Add to cart
    async function addToCart() {
        if (!currentProduct) return;

        try {
            const response = await fetch('../backend/cart/cart_add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: currentProduct.product_id,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (data.success) {
                updateCartCount(data.cart_count);
                Swal.fire({
                    icon: 'success',
                    title: 'Added to Cart',
                    text: `${quantity} x ${currentProduct.product_name} added to cart!`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to add item to cart',
                    confirmButtonColor: '#08415c'
                });
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while adding to cart',
                confirmButtonColor: '#08415c'
            });
        }
    }

    // Buy now
    function buyNow() {
        addToCart();
        // Redirect to checkout page (to be implemented)
        Swal.fire({
            icon: 'info',
            title: 'Checkout',
            text: 'Checkout feature coming soon!',
            confirmButtonColor: '#08415c'
        });
    }

    // Update cart count
    function updateCartCount(count) {
        document.querySelectorAll('.cart-count').forEach(el => el.textContent = count || 0);
    }

    // Loader functions
    function showLoader() {
        document.getElementById('loader').classList.add('active');
    }

    function hideLoader() {
        document.getElementById('loader').classList.remove('active');
    }

    // Show error
    function showError(message) {
        const container = document.getElementById('product-container');
        container.innerHTML = `
            <div class="col-span-2 text-center py-12">
                <i class="fas fa-exclamation-triangle text-6xl text-red-400 mb-4"></i>
                <p class="text-xl text-gray-600 font-medium mb-4">${escapeHtml(message)}</p>
                <a href="product.php" class="btn-primary-custom text-white px-6 py-3 rounded-lg font-semibold inline-block">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Products
                </a>
            </div>
        `;
    }

    // Mobile menu toggle
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
    }

    // Modal functions
    function openLoginModal() {
        document.getElementById('loginModal').classList.remove('hidden');
    }

    function closeLoginModal() {
        document.getElementById('loginModal').classList.add('hidden');
    }

    function showRegister() {
        document.getElementById('loginForm').classList.add('hidden');
        document.getElementById('registerForm').classList.remove('hidden');
    }

    function showLogin() {
        document.getElementById('registerForm').classList.add('hidden');
        document.getElementById('loginForm').classList.remove('hidden');
    }

    // Handle login
    async function handleLogin(e) {
        e.preventDefault();
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        
        Swal.fire({
            title: 'Logging in...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        try {
            const response = await fetch('../backend/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Login Successful!',
                    text: `Welcome back, ${data.user.name}!`,
                    confirmButtonColor: '#08415c',
                    timer: 2000
                }).then(() => {
                    if (data.user.user_level_id <= 3) {
                        window.location.href = data.redirect;
                    } else {
                        closeLoginModal();
                        checkSession();
                        initializeCart();
                    }
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
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during login.',
                confirmButtonColor: '#08415c'
            });
        }
    }

    // Handle register
    async function handleRegister(e) {
        e.preventDefault();
        const fname = document.getElementById('registerFname').value;
        const lname = document.getElementById('registerLname').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        
        Swal.fire({
            title: 'Creating account...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        try {
            const response = await fetch('../backend/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ fname, lname, email, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Registration Successful!',
                    text: 'Your account has been created. Please login.',
                    confirmButtonColor: '#08415c'
                }).then(() => {
                    showLogin();
                    document.getElementById('loginEmail').value = email;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: data.message,
                    confirmButtonColor: '#08415c'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during registration.',
                confirmButtonColor: '#08415c'
            });
        }
    }

    // Check session
    function checkSession() {
        fetch('../backend/auth.php?api=status')
            .then(response => response.json())
            .then(data => {
                if (data.logged_in) {
                    updateUIForLoggedInUser(data.user);
                }
            })
            .catch(error => console.error('Session check error:', error));
    }

    // Update UI for logged in user
    function updateUIForLoggedInUser(user) {
        const userSection = document.getElementById('userSection');
        userSection.innerHTML = `
            <div class="relative">
                <button id="userMenuButton" onclick="toggleUserMenu()" class="flex items-center space-x-2 text-gray-700 hover:text-[#08415c] transition">
                    <i class="fas fa-user-circle text-2xl"></i>
                    <span class="font-medium">${escapeHtml(user.name)}</span>
                    <i class="fas fa-chevron-down text-sm"></i>
                </button>
                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50">
                    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user mr-2"></i>Profile
                    </a>
                    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-shopping-bag mr-2"></i>Orders
                    </a>
                    ${user.user_level_id <= 3 ? `
                    <a href="../app/frontend/dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>` : ''}
                    <hr class="my-2">
                    <button onclick="handleLogout()" class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </div>
            </div>
        `;
    }

    // Toggle user menu
    function toggleUserMenu() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('hidden');
    }

    // Handle logout
    async function handleLogout() {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to logout?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#08415c',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('../backend/logout.php');
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Logged Out',
                        text: 'You have been logged out successfully.',
                        confirmButtonColor: '#08415c',
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                }
            } catch (error) {
                console.error('Logout error:', error);
            }
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userMenuButton = document.getElementById('userMenuButton');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userMenuButton && userDropdown) {
            const isClickInside = userMenuButton.contains(event.target) || userDropdown.contains(event.target);
            
            if (!isClickInside && !userDropdown.classList.contains('hidden')) {
                userDropdown.classList.add('hidden');
            }
        }
    });

    // Initialize page
    document.addEventListener('DOMContentLoaded', () => {
        checkSession();
        loadProductDetail();
        initializeCart();
    });
</script>
</body>
</html>
