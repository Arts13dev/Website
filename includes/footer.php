<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<!-- Enhanced Footer -->
<footer class="bg-gray-800 text-white mt-12">
    <div class="container mx-auto px-6 py-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Company Info -->
            <div>
                <h3 class="text-lg font-bold mb-4"><?php echo SITE_NAME; ?></h3>
                <p class="text-gray-400">Your one-stop shop for the latest tech and finest beauty products.</p>
                <div class="mt-4">
                    <p class="text-gray-400 text-sm">📍 123 Tech Street, Beauty City, SA</p>
                    <p class="text-gray-400 text-sm">📞 +27 123 456 789</p>
                    <p class="text-gray-400 text-sm">✉️ support@smarttechbeauty.co.za</p>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                <ul>
                    <li class="mb-2"><a href="about.php" class="hover:text-blue-400 transition-colors">About Us</a></li>
                    <li class="mb-2"><a href="contact.php" class="hover:text-blue-400 transition-colors">Contact</a></li>
                    <li class="mb-2"><a href="products.php" class="hover:text-blue-400 transition-colors">All Products</a></li>
                    <?php if (is_logged_in()): ?>
                        <li class="mb-2"><a href="orders.php" class="hover:text-blue-400 transition-colors">My Orders</a></li>
                        <li class="mb-2"><a href="profile.php" class="hover:text-blue-400 transition-colors">My Profile</a></li>
                    <?php else: ?>
                        <li class="mb-2"><a href="login.php" class="hover:text-blue-400 transition-colors">Login</a></li>
                        <li class="mb-2"><a href="register.php" class="hover:text-blue-400 transition-colors">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Follow Us -->
            <div>
                <h3 class="text-lg font-bold mb-4">Follow Us</h3>
                <div class="flex space-x-4 mb-4">
                    <a href="#" class="text-gray-400 hover:text-white" title="Facebook">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white" title="Twitter">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white" title="Instagram">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.024.06 1.378.06 3.808s-.012 2.784-.06 3.808c-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.024.048-1.378.06-3.808.06s-2.784-.013-3.808-.06c-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.048-1.024-.06-1.378-.06-3.808s.012-2.784.06-3.808c.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 016.08 2.525c.636-.247 1.363-.416 2.427-.465C9.53 2.013 9.884 2 12.315 2zm-1.161 1.943h-.001c-1.063 0-1.358.006-3.678.05-1.006.046-1.57.2-2.04.38a2.953 2.953 0 00-1.08 1.08c-.18.47-.334 1.034-.38 2.04-.044 2.32-.05 2.618-.05 3.678s.006 1.358.05 3.678c.046 1.006.2 1.57.38 2.04a2.953 2.953 0 001.08 1.08c.47.18 1.034.334 2.04.38 2.32.044 2.618.05 3.678.05s1.358-.006 3.678-.05c1.006-.046 1.57-.2 2.04-.38a2.953 2.953 0 001.08-1.08c.18-.47.334-1.034.38-2.04.044-2.32.05-2.618.05-3.678s-.006-1.358-.05-3.678c-.046-1.006-.2-1.57-.38-2.04a2.953 2.953 0 00-1.08-1.08c-.47-.18-1.034-.334-2.04-.38-2.32-.044-2.618-.05-3.678-.05zM12 6.865a5.135 5.135 0 100 10.27 5.135 5.135 0 000-10.27zm0 8.37a3.235 3.235 0 110-6.47 3.235 3.235 0 010 6.47zM16.635 5.85a1.25 1.25 0 100 2.5 1.25 1.25 0 000-2.5z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
                
                <!-- Newsletter Signup -->
                <div>
                    <h4 class="text-md font-semibold mb-2">Newsletter</h4>
                    <p class="text-gray-400 text-sm mb-3">Subscribe for updates and special offers!</p>
                    <form id="newsletter-form" class="flex">
                        <input type="email" id="newsletter-email" placeholder="Your email" 
                               class="flex-1 px-3 py-2 bg-gray-700 text-white rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-r-lg transition-colors">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="mt-8 border-t border-gray-700 pt-6 text-center text-gray-400">
            <p>© <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved.</p>
            <p class="text-sm mt-2">
                <a href="#" class="hover:text-blue-400 transition-colors">Privacy Policy</a> | 
                <a href="#" class="hover:text-blue-400 transition-colors">Terms of Service</a> | 
                <a href="#" class="hover:text-blue-400 transition-colors">Return Policy</a>
            </p>
        </div>
    </div>
</footer>

<script>
// Newsletter subscription
document.getElementById('newsletter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('newsletter-email').value;
    
    if (email) {
        // Here you would typically send the email to your backend
        alert('Thank you for subscribing to our newsletter!');
        document.getElementById('newsletter-email').value = '';
    }
});

// Scroll to top button (optional)
window.addEventListener('scroll', function() {
    const scrollTop = document.getElementById('scroll-top');
    if (scrollTop) {
        if (window.pageYOffset > 300) {
            scrollTop.classList.remove('hidden');
        } else {
            scrollTop.classList.add('hidden');
        }
    }
});
</script>

<!-- Scroll to top button (optional) -->
<button id="scroll-top" class="hidden fixed bottom-4 right-4 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition-colors z-50" 
        onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
    </svg>
</button>