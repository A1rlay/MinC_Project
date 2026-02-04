<?php
// Shared Navigation Component for MinC
$current_page = basename($_SERVER['PHP_SELF']);

// Determine base paths for navigation
$is_in_html = in_array($current_page, ['product.php', 'product_detail.php', 'user-cart.php', 'checkout.php', 'order-success.php', 'profile.php', 'my-orders.php']);
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
                <a id="cartLink" href="<?php echo $html_path; ?>user-cart.php" class="nav-link-custom text-gray-700 font-medium flex items-center hidden"><i class="fas fa-shopping-cart mr-2"></i>Cart</a>
                <a id="orderLink" href="<?php echo $html_path; ?>my-orders.php" class="nav-link-custom text-gray-700 font-medium flex items-center hidden"><i class="fas fa-shopping-bag mr-2"></i>My Orders</a>
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
            <a id="cartLinkMobile" href="<?php echo $html_path; ?>user-cart.php" class="block text-gray-700 font-medium py-2 flex items-center hidden"><i class="fas fa-shopping-cart mr-2"></i>Cart</a>
            <a id="orderLinkMobile" href="<?php echo $html_path; ?>my-orders.php" class="block text-gray-700 font-medium py-2 flex items-center hidden"><i class="fas fa-shopping-bag mr-2"></i>My Orders</a>
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
                <p class="text-center mt-3">
                    <button type="button" onclick="showForgotPassword()" class="text-sm text-[#08415c] font-semibold hover:text-[#0a5273]">Forgot password?</button>
                </p>
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
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Delivery Address</label>
                        <textarea id="registerAddress" required rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]" placeholder="Where should we deliver your auto parts?"></textarea>
                    </div>
                    <button type="submit" class="w-full btn-primary-custom text-white py-3 rounded-lg font-semibold">
                        Continue
                    </button>
                </form>

                <div id="otpStep" class="hidden mt-6">
                    <h3 class="text-xl font-bold text-[#08415c] mb-2">Enter Verification Code</h3>
                    <p class="text-sm text-gray-600 mb-4">Enter the 6-digit OTP sent to your email.</p>
                    <form id="otpFormElement" onsubmit="handleVerifyOtp(event)">
                        <div class="mb-3">
                            <input type="text" id="otpCode" maxlength="6" inputmode="numeric" pattern="\d{6}" placeholder="000000" class="w-full tracking-[0.4em] text-center text-xl px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                        </div>
                        <button type="submit" class="w-full btn-primary-custom text-white py-3 rounded-lg font-semibold">
                            Verify OTP
                        </button>
                    </form>
                    <button type="button" onclick="handleResendOtp()" class="w-full mt-3 text-[#08415c] font-semibold hover:text-[#0a5273]">
                        Resend code
                    </button>
                </div>

                <div id="passwordStep" class="hidden mt-6">
                    <h3 class="text-xl font-bold text-[#08415c] mb-2">Create Password</h3>
                    <p class="text-sm text-gray-600 mb-4">Set your account password after OTP verification.</p>
                    <form id="passwordFormElement" onsubmit="handleSetPassword(event)">
                        <div class="mb-2">
                            <label class="block text-gray-700 font-medium mb-2">Password</label>
                            <div class="relative">
                                <input type="password" id="registerPassword" required minlength="8" class="w-full px-4 py-3 pr-24 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                                <button type="button" id="toggleRegisterPassword" onclick="togglePasswordVisibility('registerPassword', 'toggleRegisterPassword')" class="absolute inset-y-0 right-0 px-3 text-sm text-gray-600 hover:text-[#08415c]">Show</button>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Password must be at least 8 characters long and include a letter, number, and special character.</p>
                        </div>
                        <button type="submit" class="w-full btn-primary-custom text-white py-3 rounded-lg font-semibold mt-2">
                            Submit
                        </button>
                    </form>
                </div>

                <p class="text-center mt-6 text-gray-600">
                    Already have an account?
                    <button type="button" onclick="showLogin()" class="text-[#08415c] font-semibold hover:text-[#0a5273]">Login</button>
                </p>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative">
            <button onclick="closeForgotPasswordModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <h2 class="text-3xl font-bold mb-3 text-[#08415c]">Forgot Password</h2>
            <p class="text-sm text-gray-600 mb-5">Enter your email and we will send a recovery link.</p>
            <form id="forgotPasswordFormElement" onsubmit="handleForgotPassword(event)">
                <div class="mb-5">
                    <label class="block text-gray-700 font-medium mb-2">Email</label>
                    <input type="email" id="forgotEmail" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
                </div>
                <button type="submit" class="w-full btn-primary-custom text-white py-3 rounded-lg font-semibold">
                    Send Recovery Link
                </button>
            </form>
            <p class="text-center mt-6 text-gray-600">
                <button type="button" onclick="backToLoginFromForgot()" class="text-[#08415c] font-semibold hover:text-[#0a5273]">Back to Login</button>
            </p>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const BASE_PATH = '<?php echo $base_path; ?>';

    function showAlertModal(message, icon = 'info', title = 'Notice') {
        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                icon,
                title,
                text: String(message ?? ''),
                confirmButtonColor: '#08415c'
            });
        }
        alert(message);
        return Promise.resolve();
    }

    async function showConfirmModal(message, title = 'Please Confirm') {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                icon: 'question',
                title,
                text: String(message ?? ''),
                showCancelButton: true,
                confirmButtonColor: '#08415c',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Confirm'
            });
            return !!result.isConfirmed;
        }
        return confirm(message);
    }
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
        const cartLink = document.getElementById('cartLink');
        const cartLinkMobile = document.getElementById('cartLinkMobile');
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
            if (cartLink) cartLink.classList.remove('hidden');
            if (cartLinkMobile) cartLinkMobile.classList.remove('hidden');
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
            if (cartLink) cartLink.classList.add('hidden');
            if (cartLinkMobile) cartLinkMobile.classList.add('hidden');
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
        showLogin();
    }

    let pendingRegistrationEmail = '';

    function closeLoginModal() {
        const modal = document.getElementById('loginModal');
        if (modal) modal.classList.add('hidden');
        // Clear form fields
        document.getElementById('loginEmail').value = '';
        document.getElementById('loginPassword').value = '';
        resetRegistrationFlow();
        document.getElementById('loginForm').classList.remove('hidden');
    }

    function openForgotPasswordModal() {
        const modal = document.getElementById('forgotPasswordModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeForgotPasswordModal() {
        const modal = document.getElementById('forgotPasswordModal');
        if (modal) modal.classList.add('hidden');
        const forgotEmailInput = document.getElementById('forgotEmail');
        if (forgotEmailInput) forgotEmailInput.value = '';
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
        resetRegistrationFlow();
    }

    function showLogin() {
        document.getElementById('registerForm').classList.add('hidden');
        document.getElementById('loginForm').classList.remove('hidden');
        resetRegistrationFlow();
    }

    function showForgotPassword() {
        closeLoginModal();
        openForgotPasswordModal();
    }

    function backToLoginFromForgot() {
        const forgotEmail = document.getElementById('forgotEmail').value;
        closeForgotPasswordModal();
        openLoginModal();
        if (forgotEmail) {
            document.getElementById('loginEmail').value = forgotEmail;
        }
    }

    function resetRegistrationFlow() {
        pendingRegistrationEmail = '';
        const registerForm = document.getElementById('registerFormElement');
        const otpStep = document.getElementById('otpStep');
        const passwordStep = document.getElementById('passwordStep');
        const otpCode = document.getElementById('otpCode');
        const registerPassword = document.getElementById('registerPassword');
        const registerFields = ['registerFname', 'registerLname', 'registerEmail', 'registerAddress'];

        if (registerForm) registerForm.classList.remove('hidden');
        if (otpStep) otpStep.classList.add('hidden');
        if (passwordStep) passwordStep.classList.add('hidden');
        if (otpCode) otpCode.value = '';
        if (registerPassword) registerPassword.value = '';

        registerFields.forEach((id) => {
            const field = document.getElementById(id);
            if (field) field.disabled = false;
        });
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
                showAlertModal('Login failed: ' + data.message, 'error', 'Login Failed');
            }
        } catch (error) {
            console.error('Login error:', error);
            showAlertModal('An error occurred during login', 'error', 'Login Error');
        }
    }

    async function handleRegister(e) {
        e.preventDefault();
        
        const fname = document.getElementById('registerFname').value;
        const lname = document.getElementById('registerLname').value;
        const email = document.getElementById('registerEmail').value;
        const addressInput = document.getElementById('registerAddress');
        const address = addressInput ? addressInput.value.trim() : '';

        if (!address) {
            showAlertModal('Delivery address is required.', 'warning', 'Missing Address');
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
                    address: address
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                pendingRegistrationEmail = email;
                document.getElementById('registerFormElement').classList.add('hidden');
                document.getElementById('otpStep').classList.remove('hidden');
                document.getElementById('passwordStep').classList.add('hidden');
                document.getElementById('otpCode').focus();
                showAlertModal(data.message || 'OTP sent to your email.', 'success', 'OTP Sent');
            } else {
                showAlertModal('Registration failed: ' + data.message, 'error', 'Registration Failed');
            }
        } catch (error) {
            console.error('Register error:', error);
            showAlertModal('An error occurred during registration', 'error', 'Registration Error');
        }
    }

    async function handleForgotPassword(e) {
        e.preventDefault();
        const email = document.getElementById('forgotEmail').value;

        try {
            const response = await fetch(BASE_PATH + 'backend/request_password_reset.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();
            showAlertModal(data.message || 'If this email exists, a recovery link has been sent.', 'info', 'Password Recovery');
            closeForgotPasswordModal();
            openLoginModal();
            document.getElementById('loginEmail').value = email;
        } catch (error) {
            console.error('Forgot password error:', error);
            showAlertModal('An error occurred while requesting password reset.', 'error', 'Password Reset Error');
        }
    }

    async function handleVerifyOtp(e) {
        e.preventDefault();

        const email = pendingRegistrationEmail || document.getElementById('registerEmail').value;
        const otp = (document.getElementById('otpCode').value || '').trim();

        if (!/^\d{6}$/.test(otp)) {
            showAlertModal('Please enter a valid 6-digit OTP.', 'warning', 'Invalid OTP');
            return;
        }

        try {
            const response = await fetch(BASE_PATH + 'backend/verify_registration_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, otp })
            });

            const data = await response.json();
            if (!data.success) {
                showAlertModal(data.message || 'OTP verification failed.', 'error', 'OTP Verification Failed');
                return;
            }

            document.getElementById('otpStep').classList.add('hidden');
            document.getElementById('passwordStep').classList.remove('hidden');
            document.getElementById('registerPassword').focus();
        } catch (error) {
            console.error('OTP verification error:', error);
            showAlertModal('An error occurred while verifying OTP.', 'error', 'OTP Error');
        }
    }

    async function handleResendOtp() {
        const email = pendingRegistrationEmail || document.getElementById('registerEmail').value;
        if (!email) {
            showAlertModal('Please enter your email first.', 'warning', 'Missing Email');
            return;
        }

        try {
            const response = await fetch(BASE_PATH + 'backend/resend_verification_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();
            if (!data.success) {
                showAlertModal(data.message || 'Failed to resend OTP.', 'error', 'Resend Failed');
                return;
            }

            showAlertModal(data.message || 'OTP resent successfully.', 'success', 'OTP Resent');
        } catch (error) {
            console.error('Resend OTP error:', error);
            showAlertModal('An error occurred while resending OTP.', 'error', 'Resend Error');
        }
    }

    async function handleSetPassword(e) {
        e.preventDefault();

        const email = pendingRegistrationEmail || document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;

        if (!isStrongPassword(password)) {
            showAlertModal('Password must be at least 8 characters long and include a letter, number, and special character.', 'warning', 'Weak Password');
            return;
        }

        try {
            const response = await fetch(BASE_PATH + 'backend/complete_registration.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();
            if (!data.success) {
                showAlertModal(data.message || 'Failed to set password.', 'error', 'Password Setup Failed');
                return;
            }

            showAlertModal(data.message || 'Registration complete. You can now login.', 'success', 'Registration Complete');
            showLogin();
            document.getElementById('loginEmail').value = email;
        } catch (error) {
            console.error('Set password error:', error);
            showAlertModal('An error occurred while setting password.', 'error', 'Password Error');
        }
    }

    async function handleLogout() {
        let isConfirmed = false;

        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#08415c',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout'
            });
            isConfirmed = !!result.isConfirmed;
        } else {
            isConfirmed = await showConfirmModal('Are you sure you want to logout?', 'Logout');
        }

        if (!isConfirmed) {
            return;
        }

        try {
            const response = await fetch(BASE_PATH + 'backend/logout.php', {
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            let data = {};
            try {
                data = await response.json();
            } catch (e) {
                data = {};
            }

            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Logout failed');
            }

            updateNavbarUI(false);

            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Logged Out',
                    text: 'You have been logged out successfully.',
                    confirmButtonColor: '#08415c',
                    timer: 1200
                });
            }

            const currentPath = window.location.pathname.toLowerCase();
            const shouldRedirectHome =
                currentPath.endsWith('/html/profile.php') ||
                currentPath.endsWith('/html/user-cart.php') ||
                currentPath.endsWith('/html/my-orders.php') ||
                currentPath.endsWith('/html/checkout.php');

            if (shouldRedirectHome) {
                window.location.href = BASE_PATH + 'index.php';
                return;
            }

            window.location.reload();
        } catch (error) {
            console.error('Logout error:', error);
            updateNavbarUI(false);
            window.location.reload();
        }
    }

    window.globalHandleLogout = handleLogout;
    window.globalHandleRegister = handleRegister;
    window.globalHandleLogin = handleLogin;

    // Check session when navbar loads
    document.addEventListener('DOMContentLoaded', checkNavbarSession);
</script>
