<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinC - Auto Parts Store</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/ca30ddfff9.js" crossorigin="anonymous"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 for beautiful alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 50%, #08415c 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 50%, rgba(255,255,255,0.1) 0%, transparent 50%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(8, 65, 92, 0.3);
            border-color: #08415c;
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
        
        .category-badge {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(8, 65, 92, 0.4);
        }
        
        .feature-icon {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
        }
        
        .category-navbar {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Navigation -->
    <nav class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="#" class="text-3xl font-bold text-gray-900">MinC</a>
                </div>
                
<!-- Desktop Menu -->
<div class="hidden md:flex items-center space-x-8">
    <a href="#about-us" class="nav-link-custom text-gray-700 font-medium">About Us</a>
    <a href="html/product.php" class="nav-link-custom text-gray-700 font-medium">Products</a>
    <a href="#categories" class="nav-link-custom text-gray-700 font-medium">Categories</a>
    <a href="#contact-us" class="nav-link-custom text-gray-700 font-medium">Contact</a>
</div>
                
                <!-- Right Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <!-- Cart -->
<!-- Cart -->
<a href="html/user-cart.php" class="relative text-gray-700 hover:text-[#08415c] transition">
    <i class="fas fa-shopping-cart text-2xl"></i>
    <span class="absolute -top-2 -right-2 bg-[#08415c] text-white text-xs rounded-full h-5 w-5 flex items-center justify-center cart-count">0</span>
</a>
                    
                    <!-- Login/User Info -->
                    <div id="userSection">
                        <button onclick="openLoginModal()" class="btn-primary-custom text-white px-6 py-2 rounded-lg font-medium">
                            Login
                        </button>
                    </div>
                </div>
                
                <!-- Mobile Menu Button -->
                <button class="md:hidden text-gray-700" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        
<!-- Mobile Menu -->
<div id="mobileMenu" class="hidden md:hidden bg-white border-t">
    <div class="px-4 py-4 space-y-3">
        <a href="#about-us" class="block text-gray-700 font-medium py-2">About Us</a>
        <a href="html/product.php" class="block text-gray-700 font-medium py-2">Products</a>
        <a href="#categories" class="block text-gray-700 font-medium py-2">Categories</a>
        <a href="#contact-us" class="block text-gray-700 font-medium py-2">Contact</a>
        <button onclick="openLoginModal()" class="w-full btn-primary-custom text-white px-6 py-2 rounded-lg font-medium">
            Login
        </button>
    </div>
</div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero-gradient mt-20 py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="text-white relative z-10">
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                        Premium Auto Parts at Your Fingertips
                    </h1>
                    <p class="text-xl mb-8 text-blue-100">
                        Quality parts, unbeatable prices, and fast delivery for your vehicle needs
                    </p>
<div class="flex flex-wrap gap-4">
    <a href="html/product.php" class="bg-white text-[#08415c] px-8 py-3 rounded-lg font-semibold hover:shadow-xl transition transform hover:scale-105 inline-block text-center">
        Shop Now
    </a>
    <a href="#about-us" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-[#08415c] transition inline-block text-center">
        Learn More
    </a>
</div>
                </div>
                
                <div class="hidden md:block relative z-10">
                    <img src="assets/images/website-images/slider-1.webp" alt="Auto Parts" class="w-full rounded-2xl shadow-2xl transform hover:scale-105 transition duration-300">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-8 rounded-xl hover:shadow-xl transition border-2 border-transparent hover:border-[#08415c]">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-star text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-[#08415c]">High Quality</h3>
                    <p class="text-gray-600">Premium products from trusted manufacturers</p>
                </div>
                
                <div class="text-center p-8 rounded-xl hover:shadow-xl transition border-2 border-transparent hover:border-[#08415c]">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shipping-fast text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-[#08415c]">Fast Delivery</h3>
                    <p class="text-gray-600">Quick shipping to your doorstep</p>
                </div>
                
                <div class="text-center p-8 rounded-xl hover:shadow-xl transition border-2 border-transparent hover:border-[#08415c]">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-dollar-sign text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-[#08415c]">Best Prices</h3>
                    <p class="text-gray-600">Competitive rates on all products</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section id="about-us" class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-bold mb-6 text-[#08415c]">About MinC</h2>
                    <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                        At MinC we offer an extensive selection of auto parts, truck parts and automotive accessories, so you can easily find the quality parts you need at the lowest price.
                    </p>
                    <p class="text-lg text-gray-700 leading-relaxed">
                        Explore our wide inventory to find both brand new car parts and second-hand parts for your vehicle.
                    </p>
                </div>
                
                <div class="hidden md:block">
                    <img src="assets/images/website-images/about.png" alt="About MinC" class="w-full rounded-2xl shadow-xl transform hover:scale-105 transition duration-300">
                </div>
            </div>
        </div>
    </section>
    
 <!-- Categories Section -->
<section id="categories" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-[#08415c] mb-4">Shop by Category</h2>
            <p class="text-xl text-gray-600">Browse our extensive collection of auto parts</p>
        </div>
        
        <!-- Dynamic Categories Container -->
        <div id="categoriesContainer">
            <!-- Loading State -->
            <div id="loadingState" class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-[#08415c] mb-4"></i>
                <p class="text-gray-600">Loading categories...</p>
            </div>
        </div>
    </div>
</section>
    
    <!-- Footer -->
    <footer id="contact-us" class="bg-[#08415c] text-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-12">
                <div>
                    <h3 class="text-2xl font-bold mb-6">MinC</h3>
                    <p class="text-blue-200 mb-4">Your trusted partner for quality auto parts and accessories.</p>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-4">Contact Us</h4>
                    <ul class="space-y-3 text-blue-200">
                        <li><i class="fas fa-map-marker-alt mr-2"></i> Angeles City, Pampanga</li>
                        <li><i class="fas fa-phone mr-2"></i> 0921-949-8978</li>
                        <li><i class="fas fa-envelope mr-2"></i> MinC@gmail.com</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-3 text-blue-200">
                        <li><a href="#about-us" class="hover:text-white transition">About Us</a></li>
                        <li><a href="#categories" class="hover:text-white transition">Categories</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-[#0a5273] w-10 h-10 rounded-full flex items-center justify-center hover:bg-white hover:text-[#08415c] transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-[#0a5273] w-10 h-10 rounded-full flex items-center justify-center hover:bg-white hover:text-[#08415c] transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="bg-[#0a5273] w-10 h-10 rounded-full flex items-center justify-center hover:bg-white hover:text-[#08415c] transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-blue-900 mt-12 pt-8 text-center text-blue-200">
                <p>&copy; 2025 MinC. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Login Modal -->
    <div id="loginModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative">
            <button onclick="closeLoginModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>
            
            <div id="loginForm">
                <h2 class="text-3xl font-bold mb-6 text-[#08415c]">Welcome Back</h2>
                <form id="loginFormElement" onsubmit="handleLogin(event)">
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
                <form id="registerFormElement" onsubmit="handleRegister(event)">
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
        // Check session on page load
window.onload = function() {
    checkSession();
    loadCategories();
    initializeCart();
    
    // Check for URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    
    if (error === 'unauthorized') {
        Swal.fire({
            icon: 'warning',
            title: 'Unauthorized Access',
            text: 'Please login to access that page.',
            confirmButtonColor: '#08415c'
        });
    } else if (error === 'session_expired') {
        Swal.fire({
            icon: 'info',
            title: 'Session Expired',
            text: 'Your session has expired. Please login again.',
            confirmButtonColor: '#08415c'
        });
    } else if (error === 'access_denied') {
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: 'You do not have permission to access that area.',
            confirmButtonColor: '#08415c'
        });
    }
};
        
        function checkSession() {
            fetch('backend/auth.php?api=status')
                .then(response => response.json())
                .then(data => {
                    if (data.logged_in) {
                        updateUIForLoggedInUser(data.user);
                    }
                })
                .catch(error => console.error('Session check error:', error));
        }

        // Cart initialization
function initializeCart() {
    fetch('backend/cart/cart_get.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.cart_count);
            }
        })
        .catch(error => console.error('Error loading cart:', error));
}

// Update cart count
function updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(el => el.textContent = count || 0);
}
        
function updateUIForLoggedInUser(user) {
    const userSection = document.getElementById('userSection');
    userSection.innerHTML = `
        <div class="relative">
            <button id="userMenuButton" onclick="toggleUserMenu()" class="flex items-center space-x-2 text-gray-700 hover:text-[#08415c] transition">
                <i class="fas fa-user-circle text-2xl"></i>
                <span class="font-medium">${user.name}</span>
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
                <a href="app/frontend/dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
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

// Add these NEW functions right after updateUIForLoggedInUser function
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('hidden');
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
        
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
        
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
        
        async function handleLogin(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // Show loading
            Swal.fire({
                title: 'Logging in...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            try {
                const response = await fetch('backend/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
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
                        // Check user level and redirect accordingly
                        // IT Personnel (1), Owner (2), Manager (3) -> Dashboard
                        // Consumer (4) -> Stay on landing page with session
                        if (data.user.user_level_id <= 3) {
                            window.location.href = data.redirect;
                        } else {
                            // Consumer stays on landing page
                            closeLoginModal();
                            checkSession(); // Update UI
                            window.location.reload();
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
                console.error('Login error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred during login. Please try again.',
                    confirmButtonColor: '#08415c'
                });
            }
        }
        
        async function handleRegister(e) {
            e.preventDefault();
            
            const fname = document.getElementById('registerFname').value;
            const lname = document.getElementById('registerLname').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            
            // Show loading
            Swal.fire({
                title: 'Creating account...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            try {
                const response = await fetch('backend/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        fname: fname,
                        lname: lname,
                        email: email,
                        password: password
                    })
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
                        // Pre-fill email in login form
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
                console.error('Registration error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred during registration. Please try again.',
                    confirmButtonColor: '#08415c'
                });
            }
        }
        
        async function handleLogout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to logout?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#08415c',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('backend/logout.php');
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
            });
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // Close mobile menu if open
                    const mobileMenu = document.getElementById('mobileMenu');
                    if (!mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                    }
                }
            });
        });

        // Fetch and render categories
async function loadCategories() {
    try {
        const response = await fetch('backend/get_landing_data.php');
        const data = await response.json();
        
        if (data.success && data.categories) {
            renderCategories(data.categories);
        } else {
            showCategoryError('Failed to load categories');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        showCategoryError('An error occurred while loading categories');
    }
}

function renderCategories(categories) {
    const container = document.getElementById('categoriesContainer');
    
    if (!categories || categories.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-600 text-lg">No categories available at the moment</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    categories.forEach((category, index) => {
        // Add margin bottom except for last category
        const marginClass = index < categories.length - 1 ? 'mb-16' : '';
        
        html += `
            <div class="${marginClass}">
                <h3 class="text-3xl font-bold mb-8 text-[#08415c]">${escapeHtml(category.category_name)}</h3>
                <div class="grid md:grid-cols-3 gap-8">
        `;
        
        // Render product lines for this category
        if (category.product_lines && category.product_lines.length > 0) {
            category.product_lines.forEach(productLine => {
                const imagePath = productLine.product_line_image 
                    ? `assets/images/product-lines/${productLine.product_line_image}` 
                    : 'assets/images/website-images/placeholder.jpg';
                
                html += `
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                        <img src="${imagePath}" 
                             alt="${escapeHtml(productLine.product_line_name)}" 
                             class="w-full h-48 object-cover"
                             onerror="this.src='assets/images/website-images/placeholder.jpg'">
                        <div class="p-6">
                            <h4 class="text-xl font-bold mb-2 text-[#08415c]">${escapeHtml(productLine.product_line_name)}</h4>
                            <p class="text-gray-600 mb-4">${escapeHtml(productLine.product_line_description || 'Quality products for your vehicle')}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-box mr-1"></i>
                                    ${productLine.product_count || 0} Products
                                </span>
                                <a href="html/product.php?id=${category.category_id}&c_id=${productLine.product_line_id}" 
                                   class="inline-flex items-center text-[#08415c] font-semibold hover:text-[#0a5273] transition">
                                    View Products <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            html += `
                <div class="col-span-3 text-center py-8 text-gray-500">
                    <i class="fas fa-info-circle text-3xl mb-2"></i>
                    <p>No product lines available in this category yet</p>
                </div>
            `;
        }
        
        html += `
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function showCategoryError(message) {
    const container = document.getElementById('categoriesContainer');
    container.innerHTML = `
        <div class="text-center py-12">
            <i class="fas fa-exclamation-triangle text-6xl text-red-400 mb-4"></i>
            <p class="text-gray-600 text-lg mb-4">${escapeHtml(message)}</p>
            <button onclick="loadCategories()" class="btn-primary-custom text-white px-6 py-3 rounded-lg font-semibold">
                <i class="fas fa-redo mr-2"></i>Try Again
            </button>
        </div>
    `;
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Load categories when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
});
    </script>
</body>
</html>