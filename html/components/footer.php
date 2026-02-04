<?php
// Shared Footer Component for MinC
// Include this file at the bottom of your PHP pages
?>

<!-- Footer -->
<footer class="bg-gray-900 text-gray-300 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="col-span-1">
                <h3 class="text-white text-2xl font-bold mb-4">MinC</h3>
                <p class="text-sm">Quality computer parts and components for your needs.</p>
            </div>
            
            <!-- Quick Links -->
            <div class="col-span-1">
                <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?php echo strpos(basename($_SERVER['PHP_SELF']), 'product') !== false ? '../index.php' : 'index.php'; ?>" class="hover:text-white transition">Home</a></li>
                    <li><a href="<?php echo strpos(basename($_SERVER['PHP_SELF']), 'product') !== false ? './product.php' : 'html/product.php'; ?>" class="hover:text-white transition">Products</a></li>
                    <li><a href="<?php echo strpos(basename($_SERVER['PHP_SELF']), 'product') !== false ? '../index.php#about-us' : 'index.php#about-us'; ?>" class="hover:text-white transition">About</a></li>
                    <li><a href="<?php echo strpos(basename($_SERVER['PHP_SELF']), 'product') !== false ? '../index.php#contact-us' : 'index.php#contact-us'; ?>" class="hover:text-white transition">Contact</a></li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="col-span-1">
                <h4 class="text-white font-semibold mb-4">Contact</h4>
                <ul class="space-y-2 text-sm">
                    <li><i class="fas fa-envelope mr-2"></i><a href="mailto:info@minc.com" class="hover:text-white transition">info@minc.com</a></li>
                    <li><i class="fas fa-phone mr-2"></i>+1 (555) 123-4567</li>
                    <li><i class="fas fa-map-marker-alt mr-2"></i>123 Tech Street, City, Country</li>
                </ul>
            </div>
            
            <!-- Follow Us -->
            <div class="col-span-1">
                <h4 class="text-white font-semibold mb-4">Follow Us</h4>
                <div class="flex space-x-4">
                    <a href="https://www.facebook.com/ritzmoncar.autoparts?rdid=GbXdvmSnoK5FnqUs&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1AR2ZrWwrF#" target="_blank" rel="noopener noreferrer" class="hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Divider -->
        <div class="border-t border-gray-700 mt-8 pt-8">
            <div class="flex justify-between items-center flex-col md:flex-row space-y-4 md:space-y-0">
                <p class="text-sm">&copy; 2025-2026 MinC Computer Parts. All rights reserved.</p>
                <div class="flex space-x-6 text-sm">
                    <a href="#" class="hover:text-white transition">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition">Terms of Service</a>
                    <a href="#" class="hover:text-white transition">Sitemap</a>
                </div>
            </div>
        </div>
    </div>
</footer>
