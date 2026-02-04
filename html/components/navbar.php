<?php
// Shared Navigation Component for MinC
$current_page = basename($_SERVER['PHP_SELF']);

// Determine base paths for navigation
$is_in_html = in_array($current_page, ['product.php', 'product_detail.php', 'user-cart.php', 'checkout.php', 'order-success.php', 'profile.php']);
$base_path = $is_in_html ? '../' : './';
$html_path = $is_in_html ? '' : 'html/';
?>

<!-- Navigation -->
<nav class="bg-white shadow-md fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="<?php echo $base_path; ?>index.php" onclick="scrollToTop(event)" class="text-3xl font-bold text-gray-900">MinC</a>
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="<?php echo $base_path; ?>index.php#about-us" class="nav-link-custom text-gray-700 font-medium">About Us</a>
                <a href="<?php echo $html_path; ?>product.php" class="nav-link-custom text-gray-700 font-medium">Products</a>
                <a href="<?php echo $base_path; ?>index.php#categories" class="nav-link-custom text-gray-700 font-medium">Categories</a>
                <a href="<?php echo $base_path; ?>index.php#contact-us" class="nav-link-custom text-gray-700 font-medium">Contact</a>
                <a id="dashboardLink" href="<?php echo $base_path; ?>app/frontend/dashboard.php" class="nav-link-custom text-gray-700 font-medium flex items-center hidden"><i class="fas fa-chart-line mr-2"></i>Dashboard</a>
                <a id="profileLink" href="<?php echo $html_path; ?>profile.php" class="nav-link-custom text-gray-700 font-medium flex items-center hidden"><i class="fas fa-user-circle mr-2"></i>Profile</a>
                <a id="orderLink" href="<?php echo $html_path; ?>user-cart.php" class="nav-link-custom text-gray-700 font-medium flex items-center hidden"><i class="fas fa-shopping-cart mr-2"></i>Order</a>
                <button id="loginBtn" class="btn-primary-custom text-white px-4 py-2 rounded-lg font-medium ml-4" onclick="openLoginModal()">Login</button>
                <button id="logoutBtn" class="hidden btn-primary-custom text-white px-4 py-2 rounded-lg font-medium ml-4" onclick="handleLogout()">Logout</button>
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
            <a href="<?php echo $base_path; ?>index.php#about-us" class="block text-gray-700 font-medium py-2">About Us</a>
            <a href="<?php echo $html_path; ?>product.php" class="block text-gray-700 font-medium py-2">Products</a>
            <a href="<?php echo $base_path; ?>index.php#categories" class="block text-gray-700 font-medium py-2">Categories</a>
            <a href="<?php echo $base_path; ?>index.php#contact-us" class="block text-gray-700 font-medium py-2">Contact</a>
            <a id="dashboardLinkMobile" href="<?php echo $base_path; ?>app/frontend/dashboard.php" class="block text-gray-700 font-medium py-2 flex items-center hidden"><i class="fas fa-chart-line mr-2"></i>Dashboard</a>
            <a id="profileLinkMobile" href="<?php echo $html_path; ?>profile.php" class="block text-gray-700 font-medium py-2 flex items-center hidden"><i class="fas fa-user-circle mr-2"></i>Profile</a>
            <a id="orderLinkMobile" href="<?php echo $html_path; ?>user-cart.php" class="block text-gray-700 font-medium py-2 flex items-center hidden"><i class="fas fa-shopping-cart mr-2"></i>Order</a>
            <button id="loginBtnMobile" class="w-full btn-primary-custom text-white px-4 py-2 rounded-lg font-medium mt-4" onclick="openLoginModal()">Login</button>
            <button id="logoutBtnMobile" class="hidden w-full btn-primary-custom text-white px-4 py-2 rounded-lg font-medium mt-4" onclick="handleLogout()">Logout</button>
        </div>
    </div>
    
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
                        <div class="relative">
                            <input type="password" id="loginPassword" required class="w-full px-4 py-3 pr-24 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                            <button type="button" id="toggleLoginPassword" onclick="togglePasswordVisibility('loginPassword', 'toggleLoginPassword')" class="absolute inset-y-0 right-0 px-3 text-sm text-gray-600 hover:text-[#08415c]">Show</button>
                        </div>
                    </div>
                    <button type="submit" class="w-full btn-primary-custom text-white py-3 rounded-lg font-semibold">
                        Login
                    </button>
                </form>
                <p class="text-center mt-6 text-gray-600">
                    Don't have an account? 
                    <button type="button" onclick="showRegister()" class="text-[#08415c] font-semibold hover:text-[#0a5273]">Register</button>
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
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Delivery Address</label>
                        <textarea id="registerAddress" required rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]" placeholder="Where should we deliver your auto parts?"></textarea>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Password</label>
                        <div class="relative">
                            <input type="password" id="registerPassword" required minlength="8" class="w-full px-4 py-3 pr-24 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                            <button type="button" id="toggleRegisterPassword" onclick="togglePasswordVisibility('registerPassword', 'toggleRegisterPassword')" class="absolute inset-y-0 right-0 px-3 text-sm text-gray-600 hover:text-[#08415c]">Show</button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Password must be at least 8 characters long and include a letter, number, and special character.</p>
                    </div>
                    <button type="submit" class="w-full btn-primary-custom text-white py-3 rounded-lg font-semibold">
                        Register
                    </button>
                </form>
                <p class="text-center mt-6 text-gray-600">
                    Already have an account? 
                    <button type="button" onclick="showLogin()" class="text-[#08415c] font-semibold hover:text-[#0a5273]">Login</button>
                </p>
            </div>
        </div>
    </div>
</nav>

<style>
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
</style>

<script>
    const BASE_PATH = '<?php echo $base_path; ?>';
    const HTML_PATH = '<?php echo $html_path; ?>';

    // Check session on navbar load
    function checkNavbarSession() {
        fetch(BASE_PATH + 'backend/auth.php?api=status&t=' + Date.now(), { cache: 'no-store' })
            .then(response => response.json())
            .then(data => {
                if (data.logged_in) {
                    updateNavbarUI(true, data.user.user_level_id);
                } else {
                    updateNavbarUI(false);
                }
            })
            .catch(error => console.error('Session check error:', error));
    }

    function updateNavbarUI(isLoggedIn, userLevelId = null) {
        const loginBtn = document.getElementById('loginBtn');
        const loginBtnMobile = document.getElementById('loginBtnMobile');
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutBtnMobile = document.getElementById('logoutBtnMobile');
        const profileLink = document.getElementById('profileLink');
        const profileLinkMobile = document.getElementById('profileLinkMobile');
        const orderLink = document.getElementById('orderLink');
        const orderLinkMobile = document.getElementById('orderLinkMobile');
        const dashboardLink = document.getElementById('dashboardLink');
        const dashboardLinkMobile = document.getElementById('dashboardLinkMobile');

        if (isLoggedIn) {
            // Show authenticated elements
            if (loginBtn) loginBtn.classList.add('hidden');
            if (loginBtnMobile) loginBtnMobile.classList.add('hidden');
            if (logoutBtn) logoutBtn.classList.remove('hidden');
            if (logoutBtnMobile) logoutBtnMobile.classList.remove('hidden');
            if (profileLink) profileLink.classList.remove('hidden');
            if (profileLinkMobile) profileLinkMobile.classList.remove('hidden');
            if (orderLink) orderLink.classList.remove('hidden');
            if (orderLinkMobile) orderLinkMobile.classList.remove('hidden');
            
            // Show dashboard only for IT Personnel (1) and Owner (2)
            if (userLevelId && userLevelId <= 2) {
                if (dashboardLink) dashboardLink.classList.remove('hidden');
                if (dashboardLinkMobile) dashboardLinkMobile.classList.remove('hidden');
            } else {
                if (dashboardLink) dashboardLink.classList.add('hidden');
                if (dashboardLinkMobile) dashboardLinkMobile.classList.add('hidden');
            }
        } else {
            // Show unauthenticated elements
            if (loginBtn) loginBtn.classList.remove('hidden');
            if (loginBtnMobile) loginBtnMobile.classList.remove('hidden');
            if (logoutBtn) logoutBtn.classList.add('hidden');
            if (logoutBtnMobile) logoutBtnMobile.classList.add('hidden');
            if (profileLink) profileLink.classList.add('hidden');
            if (profileLinkMobile) profileLinkMobile.classList.add('hidden');
            if (orderLink) orderLink.classList.add('hidden');
            if (orderLinkMobile) orderLinkMobile.classList.add('hidden');
            if (dashboardLink) dashboardLink.classList.add('hidden');
            if (dashboardLinkMobile) dashboardLinkMobile.classList.add('hidden');
        }
    }

    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }
    
    function scrollToTop(event) {
        if (window.location.pathname.includes('index.php') || window.location.pathname.endsWith('/')) {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function openLoginModal() {
        const modal = document.getElementById('loginModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeLoginModal() {
        const modal = document.getElementById('loginModal');
        if (modal) modal.classList.add('hidden');
        // Clear form fields
        document.getElementById('loginEmail').value = '';
        document.getElementById('loginPassword').value = '';
    }

    function togglePasswordVisibility(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        if (!input || !button) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        button.textContent = isPassword ? 'Hide' : 'Show';
    }

    function isStrongPassword(password) {
        if (password.length < 8) return false;
        if (password === '123456') return false;
        if (!/[A-Za-z]/.test(password)) return false;
        if (!/\d/.test(password)) return false;
        if (!/[^A-Za-z0-9]/.test(password)) return false;
        return true;
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

        try {
            const response = await fetch(BASE_PATH + 'backend/login.php', {
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
                closeLoginModal();
                window.location.href = data.redirect || (BASE_PATH + 'index.php');
            } else {
                alert('Login failed: ' + data.message);
            }
        } catch (error) {
            console.error('Login error:', error);
            alert('An error occurred during login');
        }
    }

    async function handleRegister(e) {
        e.preventDefault();
        
        const fname = document.getElementById('registerFname').value;
        const lname = document.getElementById('registerLname').value;
        const email = document.getElementById('registerEmail').value;
        const address = document.getElementById('registerAddress').value.trim();
        const password = document.getElementById('registerPassword').value;

        if (!isStrongPassword(password)) {
            alert('Password must be at least 8 characters long and include a letter, number, and special character.');
            return;
        }

        try {
            const response = await fetch(BASE_PATH + 'backend/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    fname: fname,
                    lname: lname,
                    email: email,
                    address: address,
                    password: password
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Registration successful! Please login with your account.');
                showLogin();
                document.getElementById('loginEmail').value = email;
            } else {
                alert('Registration failed: ' + data.message);
            }
        } catch (error) {
            console.error('Register error:', error);
            alert('An error occurred during registration');
        }
    }

    function handleLogout() {
        if (confirm('Are you sure you want to logout?')) {
            fetch(BASE_PATH + 'backend/logout.php', {
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success || data.message === 'Logged out successfully') {
                        updateNavbarUI(false);
                        alert('Logged out successfully');
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    updateNavbarUI(false);
                    alert('Logged out');
                });
        }
    }

    // Check session when navbar loads
    document.addEventListener('DOMContentLoaded', checkNavbarSession);
</script>
