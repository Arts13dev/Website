<?php
require_once 'config/config.php';

// Fetch featured products from database
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.featured = 1 AND p.is_active = 1 
        ORDER BY p.sort_order ASC, p.created_at DESC 
        LIMIT 6
    ");
    $stmt->execute();
    $featuredProducts = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching featured products: " . $e->getMessage());
    $featuredProducts = [];
}

// Get site statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $productCount = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
    $customerCount = $stmt->fetch()['total'] ?? 0;
} catch (Exception $e) {
    $productCount = 0;
    $customerCount = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo SITE_NAME; ?> - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Work+Sans%3Awght%40400%3B500%3B700%3B900" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?php echo SITE_NAME; ?> - Your one-stop shop for the latest tech and finest beauty products.">
</head>
<body class="bg-gray-50" style='font-family: "Work Sans", sans-serif;'>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto p-8">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800">Welcome to <?php echo SITE_NAME; ?></h1>
            <p class="text-lg text-gray-600 mt-2">Your one-stop shop for the latest tech and finest beauty products.</p>
            <?php if (!is_logged_in()): ?>
                <div class="mt-6">
                    <a href="register.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors mr-4">Get Started</a>
                    <a href="products.php" class="border border-blue-600 text-blue-600 px-6 py-3 rounded-lg hover:bg-blue-50 transition-colors">Browse Products</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo number_format($productCount); ?>+</div>
                <div class="text-gray-600">Quality Products</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                <div class="text-3xl font-bold text-green-600 mb-2"><?php echo number_format($customerCount); ?>+</div>
                <div class="text-gray-600">Happy Customers</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                <div class="text-3xl font-bold text-purple-600 mb-2">24/7</div>
                <div class="text-gray-600">Customer Support</div>
            </div>
        </div>

        <!-- Featured Products Section -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-3xl font-semibold mb-6 text-center">Featured Products</h2>
            
            <?php if (empty($featuredProducts)): ?>
                <!-- Fallback static products if no featured products in database -->
                <div id="featured-products-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Static Product 1 -->
                    <div class="border rounded-lg p-4 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                        <a href="products.php">
                            <img src="https://th.bing.com/th/id/OIP.Nr5o-4imX58J751vpOFtxAHaEx?w=270&h=180&c=7&r=0&o=7&dpr=1.1&pid=1.7&rm=3" alt="Smartphone" class="w-full h-56 object-cover rounded-md mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Latest Smartphone</h3>
                            <p class="text-gray-600 text-sm mt-2">High-performance smartphone with latest features</p>
                        </a>
                        <a href="products.php" class="mt-auto w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 font-semibold mt-4">
                            View Products
                        </a>
                    </div>

                    <!-- Static Product 2 -->
                    <div class="border rounded-lg p-4 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                        <a href="products.php">
                            <img src="https://tse4.mm.bing.net/th/id/OIP.GF9UfiFmXMBYLBuh-__nRgHaFP?r=0&rs=1&pid=ImgDetMain&o=7&rm=3" alt="Laptop" class="w-full h-56 object-cover rounded-md mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Powerful Laptop</h3>
                            <p class="text-gray-600 text-sm mt-2">High-performance laptop for work and gaming</p>
                        </a>
                        <a href="products.php" class="mt-auto w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 font-semibold mt-4">
                            View Products
                        </a>
                    </div>

                    <!-- Static Product 3 -->
                    <div class="border rounded-lg p-4 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                        <a href="products.php">
                            <img src="https://th.bing.com/th?q=Baddie+Hairstyles+with+Weave&w=120&h=120&c=1&rs=1&qlt=90&r=0&cb=1&dpr=1.1&pid=InlineBlock&mkt=en-ZA&cc=ZA&setlang=en&adlt=moderate&t=1&mw=247" alt="Weave" class="w-full h-56 object-cover rounded-md mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Luxury Hair Weave</h3>
                            <p class="text-gray-600 text-sm mt-2">Premium quality hair extensions and weaves</p>
                        </a>
                        <a href="products.php" class="mt-auto w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 font-semibold mt-4">
                            View Products
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Dynamic products from database -->
                <div id="featured-products-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="border rounded-lg p-4 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                            <a href="products.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars($product['image'] ?: 'https://placehold.co/300x400/e8b4b7/333333?text=No+Image'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="w-full h-56 object-cover rounded-md mb-4">
                                <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <?php if ($product['short_description']): ?>
                                    <p class="text-gray-600 text-sm mt-2"><?php echo htmlspecialchars($product['short_description']); ?></p>
                                <?php endif; ?>
                                <p class="text-xl font-bold text-blue-600 mt-3"><?php echo format_price($product['price']); ?></p>
                            </a>
                            <button class="add-to-cart-btn mt-auto w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 font-semibold mt-4"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-product-price="<?php echo $product['price']; ?>"
                                    data-product-image="<?php echo htmlspecialchars($product['image'] ?: ''); ?>">
                                Add to Cart
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-8">
                <a href="products.php" class="bg-gray-800 text-white px-8 py-3 rounded-lg hover:bg-gray-900 transition-colors">
                    View All Products
                </a>
            </div>
        </div>

        <!-- Features Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Free Shipping</h3>
                <p class="text-gray-600">Free shipping on orders over R500</p>
            </div>
            
            <div class="text-center">
                <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Secure Payment</h3>
                <p class="text-gray-600">100% secure payment processing</p>
            </div>
            
            <div class="text-center">
                <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">24/7 Support</h3>
                <p class="text-gray-600">Round-the-clock customer support</p>
            </div>
        </div>
    </main>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Add to cart functionality
        function addToCart(event) {
            const button = event.currentTarget;
            const product = {
                id: button.dataset.productId,
                name: button.dataset.productName,
                price: parseFloat(button.dataset.productPrice),
                image: button.dataset.productImage
            };

            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingProductIndex = cart.findIndex(item => item.id === product.id);

            if (existingProductIndex > -1) {
                cart[existingProductIndex].quantity++;
            } else {
                product.quantity = 1;
                cart.push(product);
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            
            // Show success message
            button.textContent = 'Added!';
            button.classList.add('bg-green-600');
            button.classList.remove('bg-blue-600');
            
            setTimeout(() => {
                button.textContent = 'Add to Cart';
                button.classList.remove('bg-green-600');
                button.classList.add('bg-blue-600');
            }, 2000);
        }

        // Attach event listeners to add to cart buttons
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                button.addEventListener('click', addToCart);
            });
        });
    </script>
</body>
</html>