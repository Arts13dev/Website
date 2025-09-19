<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<header class="bg-white shadow-md">
    <nav class="container mx-auto px-6 py-3">
        <div class="flex justify-between items-center">
            <a href="home.php" class="text-2xl font-bold text-gray-800"><?php echo SITE_NAME; ?></a>
            <div class="hidden md:flex items-center space-x-6">
                <a href="home.php" class="text-gray-800 hover:text-blue-600 transition-colors">Home</a>
                <a href="products.php" class="text-gray-800 hover:text-blue-600 transition-colors">Products</a>
                <a href="about.php" class="text-gray-800 hover:text-blue-600 transition-colors">About</a>
                <a href="contact.php" class="text-gray-800 hover:text-blue-600 transition-colors">Contact</a>
            </div>
            <div class="flex items-center space-x-4">
                <?php if (is_logged_in()): ?>
                    <!-- Logged in user menu -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-gray-800 hover:text-blue-600 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <?php if (is_admin()): ?>
                                <a href="admin_dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Dashboard</a>
                            <?php endif; ?>
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Profile</a>
                            <a href="orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Orders</a>
                            <a href="wishlist.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Wishlist</a>
                            <div class="border-t border-gray-100"></div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Guest user menu -->
                    <a href="login.php" class="text-gray-800 hover:text-blue-600 transition-colors">Login</a>
                    <a href="register.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Sign Up</a>
                <?php endif; ?>
                
                <!-- Shopping Cart -->
                <a href="cart.php" class="relative text-gray-800 hover:text-blue-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19"></path>
                    </svg>
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                </a>

                <!-- Mobile menu button -->
                <button id="mobile-menu-button" class="md:hidden text-gray-800 hover:text-blue-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4">
            <div class="flex flex-col space-y-2">
                <a href="home.php" class="text-gray-800 hover:text-blue-600 transition-colors py-2">Home</a>
                <a href="products.php" class="text-gray-800 hover:text-blue-600 transition-colors py-2">Products</a>
                <a href="about.php" class="text-gray-800 hover:text-blue-600 transition-colors py-2">About</a>
                <a href="contact.php" class="text-gray-800 hover:text-blue-600 transition-colors py-2">Contact</a>
                <?php if (!is_logged_in()): ?>
                    <div class="border-t pt-2 mt-2">
                        <a href="login.php" class="text-gray-800 hover:text-blue-600 transition-colors py-2 block">Login</a>
                        <a href="register.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors inline-block mt-2">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<script>
// Mobile menu toggle
document.getElementById('mobile-menu-button').addEventListener('click', function() {
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenu.classList.toggle('hidden');
});

// Update cart count
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    document.getElementById('cart-count').textContent = totalItems;
}

// Initialize cart count
document.addEventListener('DOMContentLoaded', updateCartCount);
</script>