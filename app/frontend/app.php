<?php
// Configuration and session management
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuration array - you can move this to a separate config file
$config = [
    'site_name' => 'MinC Auto Supply',
    'site_short' => 'MinC',
    'version' => '1.0.0',
    'year' => date('Y')
];

// User data - replace with your authentication system
if (!isset($pdo)) {
    include_once '../../database/connect_database.php';
}

// Get current user data from database
$user = [
    'full_name' => 'Guest User',
    'user_type' => 'User',
    'is_logged_in' => false,
    'user_id' => null,
    'email' => null,
    'contact_num' => null
];

if (isset($_SESSION['user_id'])) {
    try {
        $user_query = "
            SELECT 
                u.user_id,
                CONCAT(u.fname, ' ', COALESCE(CONCAT(u.mname, ' '), ''), u.lname) as full_name,
                u.fname,
                u.mname,
                u.lname,
                u.email,
                u.contact_num,
                ul.user_type_name as user_type,
                u.user_status,
                u.user_level_id
            FROM users u
            LEFT JOIN user_levels ul ON u.user_level_id = ul.user_level_id
            WHERE u.user_id = :user_id AND u.user_status = 'active'
        ";
        
        $stmt = $pdo->prepare($user_query);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
if ($user_data) {
    $user = [
        'full_name' => trim($user_data['full_name']),
        'first_name' => $user_data['fname'],
        'middle_name' => $user_data['mname'],
        'last_name' => $user_data['lname'],
        'user_type' => $user_data['user_type'],
        'is_logged_in' => true,
        'user_id' => $user_data['user_id'],
        'email' => $user_data['email'],
        'contact_num' => $user_data['contact_num'],
        'user_status' => $user_data['user_status'],
        'user_level_id' => $user_data['user_level_id']
    ];
    
    // Also update session with latest data
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['fname'] = $user['first_name'];
    $_SESSION['user_type_name'] = $user['user_type'];
    $_SESSION['user_level_id'] = $user['user_level_id'];
}
    } catch (PDOException $e) {
        error_log("Error fetching user data in app.php: " . $e->getMessage());
        // Keep default values if database query fails
    }
}

// Current page detection
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Page titles mapping
$page_titles = [
    'dashboard' => 'Dashboard',
    'inventory' => 'Inventory Management',
    'products' => 'Products',
    'orders' => 'Orders',
    'customers' => 'Customers',
    'suppliers' => 'Suppliers',
    'reports' => 'Reports'
];
$page_title = $page_titles[$current_page] ?? 'Dashboard';
$document_title = isset($custom_title) ? $custom_title : $page_title . ' - ' . $config['site_name'];

// Get unread notification count for the current user
// Get unread notification count for the current user
$unread_notifications = 0;
if ($user['is_logged_in'] && isset($user['user_id'])) {
    try {
        // Check if notifications table exists first
        $table_check = $pdo->query("SHOW TABLES LIKE 'notifications'");
        
        if ($table_check->rowCount() > 0) {
            $notification_query = "
                SELECT COUNT(*) as unread_count 
                FROM notifications 
                WHERE recipient_id = :user_id AND is_read = 0
            ";
            
            $notification_stmt = $pdo->prepare($notification_query);
            $notification_stmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
            $notification_stmt->execute();
            $notification_result = $notification_stmt->fetch(PDO::FETCH_ASSOC);
            
            $unread_notifications = (int)($notification_result['unread_count'] ?? 0);
        }
    } catch (PDOException $e) {
        error_log("Error fetching notification count: " . $e->getMessage());
        $unread_notifications = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($document_title); ?></title>
    <link rel="icon" type="image/png" href="../../resources/images/favicon.ico">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
 <style>
    /* MinC Brand Colors - Dark Blue Theme */
    .minc-dark-blue { background-color: #1e3a8a; }
    .minc-blue { background-color: #3b82f6; }
    .minc-light-blue { background-color: #60a5fa; }
    .minc-accent { background-color: #2563eb; }
    
    /* Professional card design */
    .professional-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    /* Glassmorphism sidebar - Dark Blue */
    .glassmorphism {
        background: linear-gradient(135deg, 
            rgba(30, 58, 138, 0.95) 0%, 
            rgba(37, 99, 235, 0.95) 50%, 
            rgba(30, 58, 138, 0.95) 100%
        );
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-right: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
    }
    
    /* Water background animation - Blue tones */
    .water-bg {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, 
            rgba(59, 130, 246, 0.1) 0%,
            rgba(37, 99, 235, 0.1) 25%,
            rgba(30, 64, 175, 0.1) 50%,
            rgba(96, 165, 250, 0.1) 75%,
            rgba(59, 130, 246, 0.1) 100%
        );
        background-size: 400% 400%;
        animation: waterFlow 15s ease-in-out infinite;
        opacity: 0.3;
    }
    
    @keyframes waterFlow {
        0%, 100% { background-position: 0% 50%; }
        25% { background-position: 100% 25%; }
        50% { background-position: 100% 100%; }
        75% { background-position: 0% 75%; }
    }
    
    .dashboard-bg {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
    }
    
    /* Blue accent elements */
    .blue-accent {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    }
    
    .blue-hover:hover {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.25);
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.6s ease-out forwards;
    }
    
    /* Professional hover effects */
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }
    
    /* Active link indicator - Blue theme */
    .active-indicator {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 0;
        background: linear-gradient(135deg, #60a5fa, #3b82f6);
        border-radius: 0 2px 2px 0;
        transition: height 0.3s ease;
    }
    
    .active-link .active-indicator {
        height: 24px;
    }
    
/* Sidebar collapsed styles */
.sidebar-collapsed {
    width: 64px !important;
}

/* Ensure content area takes full available width */
#content {
    width: 100%;
    min-width: 0; /* Allow content to shrink below its minimum content size */
}

    .sidebar-collapsed .full-logo-wrapper {
        opacity: 0;
    }

    .sidebar-collapsed .small-logo-wrapper {
        opacity: 1;
    }

    .sidebar-collapsed .link-text,
    .sidebar-collapsed .sidebar-heading,
    .sidebar-collapsed .user-info {
        opacity: 0;
        width: 0;
        overflow: hidden;
    }

    .sidebar-collapsed .nav-icon {
        margin: 0 auto;
    }

    .sidebar-collapsed #toggle-icon {
        transform: rotate(180deg);
    }
    
    /* Custom scrollbar - Blue theme */
    #sidebar::-webkit-scrollbar {
        width: 6px;
    }

    #sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 3px;
        margin: 8px 0;
    }

    #sidebar::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, 
            rgba(59, 130, 246, 0.4) 0%, 
            rgba(37, 99, 235, 0.4) 50%,
            rgba(96, 165, 250, 0.4) 100%
        );
        border-radius: 3px;
        transition: all 0.3s ease;
    }

    #sidebar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, 
            rgba(59, 130, 246, 0.7) 0%, 
            rgba(37, 99, 235, 0.7) 50%,
            rgba(96, 165, 250, 0.7) 100%
        );
        width: 8px;
    }

    #sidebar {
        scrollbar-width: thin;
        scrollbar-color: rgba(59, 130, 246, 0.4) rgba(255, 255, 255, 0.05);
    }

    #sidebar::-webkit-scrollbar {
        width: 0px;
        transition: width 0.3s ease;
    }

    #sidebar:hover::-webkit-scrollbar {
        width: 6px;
    }
    
    /* Updated Topbar Styles - Dark Blue */
    .minc-topbar {
        background-color: #1e3a8a;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .user-dropdown {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }
    
    /* Professional button styles - Blue theme */
    .btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        color: white;
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    }
    
    /* Status indicators */
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    
    .status-active { background-color: #22c55e; }
    .status-pending { background-color: #f59e0b; }
    .status-completed { background-color: #3b82f6; }
    
    /* Mobile responsiveness */
    @media (max-width: 1024px) {
        #content {
            margin-left: 0 !important;
        }
        
        #sidebar {
            position: fixed;
            z-index: 50;
        }
    }

    /* Desktop - sidebar should push content */
    @media (min-width: 1024px) {
        #content {
            transition: margin-left 0.3s ease-in-out;
        }
        
        #sidebar {
            position: fixed;
            left: 0;
            transform: translateX(0) !important;
        }
    }

    /* Alert styles */
    .alert-success {
        background-color: rgb(240 253 244);
        border-color: rgb(187 247 208);
        color: rgb(22 101 52);
    }
    
    .alert-error {
        background-color: rgb(254 242 242);
        border-color: rgb(254 202 202);
        color: rgb(153 27 27);
    }
    
    /* Nav link hover effects - Blue theme */
    .nav-link:hover .nav-icon {
        transform: scale(1.1);
    }
    
    .nav-link.active-link .nav-icon {
        background: rgba(96, 165, 250, 0.2) !important;
        color: #60a5fa !important;
    }
    
    /* Additional custom styles */
    <?php if (isset($additional_styles)): ?>
    <?php echo $additional_styles; ?>
    <?php endif; ?>
</style>
    <link rel="stylesheet" href="components/extension-projects.css">
    <link rel="stylesheet" href="components/terminal-report.css">
</head>
<body class="dashboard-bg font-sans flex min-h-screen overflow-x-hidden">
<!-- Background decorative elements -->
<div class="fixed inset-0 overflow-hidden pointer-events-none">
    <div class="absolute top-20 -left-20 w-64 h-64 bg-blue-200/30 rounded-full filter blur-3xl animate-float"></div>
    <div class="absolute bottom-10 -right-20 w-80 h-80 bg-blue-300/20 rounded-full filter blur-3xl animate-float" style="animation-delay: -2s;"></div>
</div>

    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div id="content" class="flex-1 transition-all duration-300 ease-in-out ml-0 lg:ml-64" style="width: calc(100% - 0px);">
<!-- Updated Top Navigation Bar -->
<nav class="minc-topbar text-white shadow-lg sticky top-0 z-20 no-print">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Left Side with Icon -->
            <div class="flex items-center">
                <!-- Mobile Menu Button -->
                <button id="mobile-toggle" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-lg bg-white/10 text-white hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-200 mr-3">
                    <i class="fas fa-bars menu-icon"></i>
                </button>
                
                <!-- Icon and Title -->
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-car text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">MinC Auto Supply</h1>
                        <p class="text-sm opacity-80"><?php echo htmlspecialchars($page_title); ?></p>
                    </div>
                </div>
            </div>
                    
                    <!-- Right Side -->
                    <div class="flex items-center space-x-4">
                        <!-- Search (Hidden on mobile) -->
                        <div class="relative hidden md:block">
                            <input type="text" placeholder="Search..." class="bg-white/10 text-white placeholder-white/60 rounded-xl py-2.5 pl-10 pr-4 w-64 focus:outline-none focus:ring-2 focus:ring-white/30 focus:bg-white/20 transition-all duration-200 border border-white/20">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-white/60"></i>
                        </div>
                        
<!-- Enhanced Notification Bell -->
<div class="relative">
    <a href="notification_system.php" class="notification-bell-link flex items-center p-2 rounded-xl bg-white/10 hover:bg-white/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/30 group" title="<?php echo $unread_notifications > 0 ? "You have {$unread_notifications} unread notifications" : 'No unread notifications'; ?>">
        <i class="fas fa-bell text-white group-hover:scale-110 transition-transform duration-200"></i>
        <?php if ($unread_notifications > 0): ?>
            <span class="notification-bell-count absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[20px] h-5 text-xs font-bold leading-none text-white bg-red-600 rounded-full px-1.5 py-0.5 transform animate-pulse shadow-lg">
                <?php echo $unread_notifications > 99 ? '99+' : $unread_notifications; ?>
            </span>
        <?php else: ?>
            <span class="notification-bell-count hidden absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[20px] h-5 text-xs font-bold leading-none text-white bg-red-600 rounded-full px-1.5 py-0.5 transform shadow-lg"></span>
        <?php endif; ?>
    </a>
</div>
                        
                        <!-- User Profile Dropdown -->
                        <div class="relative">
                            <button id="user-menu-button" class="flex items-center p-2 rounded-xl bg-white/10 hover:bg-white/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/30">
<div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mr-3" title="<?php echo htmlspecialchars($user['full_name']); ?>">
    <?php 
    // Show user initials if available
    if (!empty($user['full_name']) && $user['full_name'] !== 'Guest User') {
        $name_parts = explode(' ', trim($user['full_name']));
        $initials = '';
        foreach ($name_parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(substr($part, 0, 1));
                if (strlen($initials) >= 2) break; // Limit to 2 initials
            }
        }
        echo '<span class="text-white text-xs font-semibold">' . htmlspecialchars($initials) . '</span>';
    } else {
        echo '<i class="fas fa-user text-white text-sm"></i>';
    }
    ?>
</div>

<div class="text-right">
    <p class="font-semibold text-sm" title="<?php echo htmlspecialchars($user['full_name']); ?>">
        <?php 
        // Truncate long names for better display
        $display_name = strlen($user['full_name']) > 20 
            ? substr($user['full_name'], 0, 17) . '...' 
            : $user['full_name'];
        echo htmlspecialchars($display_name); 
        ?>
    </p>
<p class="text-xs opacity-80 capitalize" title="User ID: <?php echo htmlspecialchars($user['user_id'] ?? 'N/A'); ?>">
    <?php echo htmlspecialchars($user['user_type']); ?>
</p>
</div>
                                <i class="fas fa-chevron-down ml-2 text-sm"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div id="user-menu" class="absolute right-0 mt-2 w-56 user-dropdown rounded-xl py-2 z-50 hidden">
                                <!-- User Info Header -->
<div class="px-4 py-3 border-b border-gray-200">
    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['full_name']); ?></p>
    <p class="text-sm text-gray-500 capitalize"><?php echo htmlspecialchars($user['user_type']); ?></p>
    <p class="text-xs text-gray-400 mt-1">
        <i class="fas fa-id-badge mr-1"></i>User ID: <?php echo htmlspecialchars($user['user_id'] ?? 'N/A'); ?>
    </p>
    <p class="text-xs text-gray-400">
        <i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($user['email'] ?: 'No email'); ?>
    </p>
    <?php if (!empty($user['contact_num'])): ?>
    <p class="text-xs text-gray-400">
        <i class="fas fa-phone mr-1"></i><?php echo htmlspecialchars($user['contact_num']); ?>
    </p>
    <?php endif; ?>
</div>
                                <!-- Menu Items -->
                                <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-user mr-3 w-4"></i>My Profile
                                </a>
                                <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-cog mr-3 w-4"></i>Account Settings
                                </a>
                                <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-question-circle mr-3 w-4"></i>Help & Support
                                </a>
                                
                                <hr class="my-2">
                                
<a href="../../backend/logout.php" class="flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
    <i class="fas fa-sign-out-alt mr-3 w-4"></i>Sign Out
</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="p-6">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-6 p-4 alert-success border rounded-xl flex items-center animate-fadeIn">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mb-6 p-4 alert-error border rounded-xl flex items-center animate-fadeIn">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
         <!-- Page specific content goes here -->
<?php if (isset($content) && !empty($content)): ?>
    <?php echo $content; ?>
<?php elseif (isset($content_file) && file_exists($content_file)): ?>
    <?php include $content_file; ?>
<?php else: ?>
    <!-- Default content or include specific page content -->
    <div class="professional-card rounded-xl p-6 animate-fadeIn">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Welcome to <?php echo htmlspecialchars($config['site_name']); ?></h3>
        <p class="text-gray-600">This is the main content area. Include your page-specific content here.</p>
    </div>
<?php endif; ?>
        </main>
    </div>

    <!-- JavaScript for enhanced functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const toggleBtn = document.getElementById('toggle-sidebar');
            const mobileToggle = document.getElementById('mobile-toggle');
            const overlay = document.getElementById('sidebar-overlay');
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenu = document.getElementById('user-menu');
            
            // Enhanced User Menu Toggle
            if (userMenuButton && userMenu) {
                userMenuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('hidden');
                });

                // Close menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                        userMenu.classList.add('hidden');
                    }
                });

                // Auto-hide menu after 5 seconds of inactivity
                let menuTimeout;
                userMenuButton.addEventListener('click', function() {
                    clearTimeout(menuTimeout);
                    if (!userMenu.classList.contains('hidden')) {
                        menuTimeout = setTimeout(function() {
                            userMenu.classList.add('hidden');
                        }, 5000);
                    }
                });
            }
            
            // Desktop sidebar toggle
// Desktop sidebar toggle
if (toggleBtn) {
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-collapsed');
        
        if (sidebar.classList.contains('sidebar-collapsed')) {
            content.style.marginLeft = '64px';
            content.style.width = 'calc(100% - 64px)';
        } else {
            content.style.marginLeft = '250px';
            content.style.width = 'calc(100% - 250px)';
        }
    });
}
            
            // Mobile sidebar toggle
            if (mobileToggle) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                    overlay.classList.toggle('opacity-0');
                    overlay.classList.toggle('pointer-events-none');
                    
                    if (!sidebar.classList.contains('-translate-x-full')) {
                        overlay.classList.remove('opacity-0', 'pointer-events-none');
                    } else {
                        overlay.classList.add('opacity-0', 'pointer-events-none');
                    }
                });
            }
            
            // Close sidebar when clicking overlay
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('opacity-0', 'pointer-events-none');
                });
            }
            
            // Close sidebar on mobile when clicking a link
            const navLinks = sidebar.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1024) {
                        sidebar.classList.add('-translate-x-full');
                        overlay.classList.add('opacity-0', 'pointer-events-none');
                    }
                });
            });

  
// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) {
        if (sidebar.classList.contains('sidebar-collapsed')) {
            content.style.marginLeft = '64px';
            content.style.width = 'calc(100% - 64px)';
        } else {
            content.style.marginLeft = '250px';
            content.style.width = 'calc(100% - 250px)';
        }
    } else {
        content.style.marginLeft = '0';
        content.style.width = '100%';
    }
});
            
// Initialize margin and width on page load
if (window.innerWidth >= 1024) {
    content.style.marginLeft = '250px';
    content.style.width = 'calc(100% - 250px)';
} else {
    content.style.marginLeft = '0';
    content.style.width = '100%';
}
            
            // Enhanced search functionality (optional)
            const searchInput = document.querySelector('input[placeholder="Search..."]');
            if (searchInput) {
                searchInput.addEventListener('focus', function() {
                    this.style.width = '300px';
                });
                
                searchInput.addEventListener('blur', function() {
                    if (!this.value) {
                        this.style.width = '256px';
                    }
                });
            }
        });

        // Real-time notification count update
function updateNotificationCount() {
    fetch('get_notification_count.php')
        .then(response => response.json())
        .then(data => {
            const notificationBell = document.querySelector('.notification-bell-count');
            if (notificationBell) {
                if (data.count > 0) {
                    notificationBell.textContent = data.count > 99 ? '99+' : data.count;
                    notificationBell.classList.remove('hidden');
                    notificationBell.classList.add('animate-pulse');
                } else {
                    notificationBell.classList.add('hidden');
                }
            }
        })
        .catch(error => {
            console.log('Error updating notification count:', error);
        });
}

// Update notification count every 30 seconds
if (<?php echo $user['is_logged_in'] ? 'true' : 'false'; ?>) {
    setInterval(updateNotificationCount, 30000);
}
    </script>
    
    <!-- Additional JavaScript -->
    <?php if (isset($additional_js)): ?>
    <script>
    <?php echo $additional_js; ?>
    </script>
    <?php endif; ?>
</body>
</html>