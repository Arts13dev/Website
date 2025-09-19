<?php
require_once 'config/config.php';

// Handle API requests for products
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo = getDBConnection();
        
        // Single product request
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ? AND p.is_active = 1
            ");
            $stmt->execute([$_GET['id']]);
            $product = $stmt->fetch();
            
            if ($product) {
                echo json_encode(['success' => true, 'product' => $product]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
            }
            exit;
        }
        
        // Multiple products request with filters
        $where = ['p.is_active = 1'];
        $params = [];
        
        // Category filter
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $where[] = 'c.slug = ?';
            $params[] = $_GET['category'];
        }
        
        // Search filter
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)';
            $searchTerm = '%' . $_GET['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Price range filter
        if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
            $where[] = 'p.price >= ?';
            $params[] = $_GET['min_price'];
        }
        
        if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
            $where[] = 'p.price <= ?';
            $params[] = $_GET['max_price'];
        }
        
        // Build query
        $sql = "
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.featured DESC, p.sort_order ASC, p.created_at DESC
        ";
        
        // Add limit for pagination
        $limit = min(intval($_GET['limit'] ?? 50), 100); // Max 100 products per request
        $offset = intval($_GET['offset'] ?? 0);
        $sql .= " LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'products' => $products]);
        
    } catch (Exception $e) {
        error_log("Products API error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error loading products']);
    }
    exit;
}

// Regular page view - display products page
try {
    $pdo = getDBConnection();
    
    // Get categories for filter
    $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    $categories = $stmt->fetchAll();
    
    // Get featured products for initial display
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1 
        ORDER BY p.featured DESC, p.sort_order ASC, p.created_at DESC 
        LIMIT 12
    ");
    $stmt->execute();
    $initialProducts = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Error loading products page: " . $e->getMessage());
    $categories = [];
    $initialProducts = [];
}

// Check if this is a single product view
$singleProduct = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ? AND p.is_active = 1
        ");
        $stmt->execute([$_GET['id']]);
        $singleProduct = $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error loading single product: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $singleProduct ? htmlspecialchars($singleProduct['name']) . ' - ' : 'Products - '; ?><?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Work+Sans%3Awght%40400%3B500%3B700%3B900" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?php echo $singleProduct ? htmlspecialchars($singleProduct['short_description'] ?? '') : 'Browse our collection of tech and beauty products'; ?>">
</head>
<body class="bg-gray-50" style='font-family: "Work Sans", sans-serif;'>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-6 py-8">
        <?php if ($singleProduct): ?>
            <!-- Single Product View -->
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li><a href="home.php" class="text-gray-500 hover:text-blue-600">Home</a></li>
                    <li><span class="mx-2">/</span><a href="products.php" class="text-gray-500 hover:text-blue-600">Products</a></li>
                    <li><span class="mx-2">/</span><span class="text-gray-700"><?php echo htmlspecialchars($singleProduct['name']); ?></span></li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Product Image -->
                <div>
                    <img src="<?php echo htmlspecialchars($singleProduct['image'] ?: 'https://placehold.co/600x600/e8b4b7/333333?text=No+Image'); ?>" 
                         alt="<?php echo htmlspecialchars($singleProduct['name']); ?>" 
                         class="w-full rounded-lg shadow-lg">
                </div>
                
                <!-- Product Details -->
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($singleProduct['name']); ?></h1>
                    
                    <?php if ($singleProduct['category_name']): ?>
                        <p class="text-blue-600 mb-2">Category: <?php echo htmlspecialchars($singleProduct['category_name']); ?></p>
                    <?php endif; ?>
                    
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-blue-600"><?php echo format_price($singleProduct['price']); ?></span>
                        <?php if ($singleProduct['compare_price'] && $singleProduct['compare_price'] > $singleProduct['price']): ?>
                            <span class="text-xl text-gray-500 line-through ml-2"><?php echo format_price($singleProduct['compare_price']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($singleProduct['short_description']): ?>
                        <p class="text-gray-600 text-lg mb-6"><?php echo htmlspecialchars($singleProduct['short_description']); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($singleProduct['description']): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Description</h3>
                            <div class="text-gray-600"><?php echo nl2br(htmlspecialchars($singleProduct['description'])); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Product Details -->
                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                        <?php if ($singleProduct['brand']): ?>
                            <div><strong>Brand:</strong> <?php echo htmlspecialchars($singleProduct['brand']); ?></div>
                        <?php endif; ?>
                        <?php if ($singleProduct['model']): ?>
                            <div><strong>Model:</strong> <?php echo htmlspecialchars($singleProduct['model']); ?></div>
                        <?php endif; ?>
                        <?php if ($singleProduct['color']): ?>
                            <div><strong>Color:</strong> <?php echo htmlspecialchars($singleProduct['color']); ?></div>
                        <?php endif; ?>
                        <?php if ($singleProduct['size']): ?>
                            <div><strong>Size:</strong> <?php echo htmlspecialchars($singleProduct['size']); ?></div>
                        <?php endif; ?>
                        <div><strong>Stock:</strong> <span class="<?php echo $singleProduct['stock'] > 0 ? 'text-green-600' : 'text-red-600'; ?>"><?php echo $singleProduct['stock'] > 0 ? $singleProduct['stock'] . ' available' : 'Out of stock'; ?></span></div>
                        <?php if ($singleProduct['sku']): ?>
                            <div><strong>SKU:</strong> <?php echo htmlspecialchars($singleProduct['sku']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Add to Cart -->
                    <button class="add-to-cart-btn w-full bg-blue-600 text-white text-lg font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors <?php echo $singleProduct['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                            data-product-id="<?php echo $singleProduct['id']; ?>"
                            data-product-name="<?php echo htmlspecialchars($singleProduct['name']); ?>"
                            data-product-price="<?php echo $singleProduct['price']; ?>"
                            data-product-image="<?php echo htmlspecialchars($singleProduct['image'] ?: ''); ?>"
                            <?php echo $singleProduct['stock'] <= 0 ? 'disabled' : ''; ?>>
                        <?php echo $singleProduct['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                    </button>
                </div>
            </div>

        <?php else: ?>
            <!-- Products Listing View -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Our Products</h1>
                <p class="text-gray-600">Discover our amazing collection of tech and beauty products</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar Filters -->
                <aside class="lg:w-64 bg-white p-6 rounded-lg shadow-sm h-fit">
                    <h3 class="text-lg font-semibold mb-4">Filters</h3>
                    
                    <!-- Search -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" id="search-input" placeholder="Search products..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <!-- Categories -->
                    <?php if (!empty($categories)): ?>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="category-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['slug']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Price Range -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" id="min-price" placeholder="Min" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <input type="number" id="max-price" placeholder="Max" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <button id="apply-filters" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                        Apply Filters
                    </button>
                    <button id="clear-filters" class="w-full mt-2 border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50 transition-colors">
                        Clear Filters
                    </button>
                </aside>

                <!-- Products Grid -->
                <div class="flex-1">
                    <div id="products-loading" class="text-center py-8 hidden">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">Loading products...</p>
                    </div>
                    
                    <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($initialProducts as $product): ?>
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                                <a href="products.php?id=<?php echo $product['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($product['image'] ?: 'https://placehold.co/300x300/e8b4b7/333333?text=No+Image'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="w-full h-48 object-cover">
                                </a>
                                <div class="p-4">
                                    <a href="products.php?id=<?php echo $product['id']; ?>">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-2 hover:text-blue-600 transition-colors"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    </a>
                                    <?php if ($product['short_description']): ?>
                                        <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars(substr($product['short_description'], 0, 80)) . (strlen($product['short_description']) > 80 ? '...' : ''); ?></p>
                                    <?php endif; ?>
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-lg font-bold text-blue-600"><?php echo format_price($product['price']); ?></span>
                                        <?php if ($product['stock'] <= 0): ?>
                                            <span class="text-xs text-red-600 font-medium">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="add-to-cart-btn w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors <?php echo $product['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                            data-product-id="<?php echo $product['id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                            data-product-price="<?php echo $product['price']; ?>"
                                            data-product-image="<?php echo htmlspecialchars($product['image'] ?: ''); ?>"
                                            <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div id="no-products" class="text-center py-12 hidden">
                        <p class="text-gray-500">No products found. Try adjusting your filters.</p>
                    </div>
                    
                    <!-- Load More Button -->
                    <div class="text-center mt-8">
                        <button id="load-more" class="bg-gray-800 text-white px-8 py-3 rounded-lg hover:bg-gray-900 transition-colors">
                            Load More Products
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        let currentOffset = <?php echo count($initialProducts); ?>;
        let isLoading = false;

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

        <?php if (!$singleProduct): ?>
        // Products listing functionality
        function loadProducts(reset = false) {
            if (isLoading) return;
            isLoading = true;

            const loading = document.getElementById('products-loading');
            const grid = document.getElementById('products-grid');
            const noProducts = document.getElementById('no-products');

            loading.classList.remove('hidden');

            const params = new URLSearchParams({
                api: '1',
                limit: 12,
                offset: reset ? 0 : currentOffset
            });

            // Add filters
            const search = document.getElementById('search-input').value;
            const category = document.getElementById('category-filter').value;
            const minPrice = document.getElementById('min-price').value;
            const maxPrice = document.getElementById('max-price').value;

            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (minPrice) params.append('min_price', minPrice);
            if (maxPrice) params.append('max_price', maxPrice);

            fetch(`products.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    loading.classList.add('hidden');
                    
                    if (data.success) {
                        if (reset) {
                            grid.innerHTML = '';
                            currentOffset = 0;
                        }

                        if (data.products.length === 0 && reset) {
                            noProducts.classList.remove('hidden');
                            grid.classList.add('hidden');
                        } else {
                            noProducts.classList.add('hidden');
                            grid.classList.remove('hidden');
                            
                            data.products.forEach(product => {
                                const productHTML = createProductHTML(product);
                                grid.insertAdjacentHTML('beforeend', productHTML);
                            });

                            currentOffset += data.products.length;
                        }

                        // Reattach event listeners
                        attachAddToCartListeners();
                    }
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                    loading.classList.add('hidden');
                })
                .finally(() => {
                    isLoading = false;
                });
        }

        function createProductHTML(product) {
            const imageUrl = product.image || 'https://placehold.co/300x300/e8b4b7/333333?text=No+Image';
            const shortDesc = product.short_description ? 
                (product.short_description.length > 80 ? 
                    product.short_description.substring(0, 80) + '...' : 
                    product.short_description) : '';
            
            return `
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <a href="products.php?id=${product.id}">
                        <img src="${imageUrl}" alt="${product.name}" class="w-full h-48 object-cover">
                    </a>
                    <div class="p-4">
                        <a href="products.php?id=${product.id}">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2 hover:text-blue-600 transition-colors">${product.name}</h3>
                        </a>
                        ${shortDesc ? `<p class="text-sm text-gray-600 mb-2">${shortDesc}</p>` : ''}
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-lg font-bold text-blue-600">R ${parseFloat(product.price).toFixed(2)}</span>
                            ${product.stock <= 0 ? '<span class="text-xs text-red-600 font-medium">Out of Stock</span>' : ''}
                        </div>
                        <button class="add-to-cart-btn w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors ${product.stock <= 0 ? 'opacity-50 cursor-not-allowed' : ''}"
                                data-product-id="${product.id}"
                                data-product-name="${product.name}"
                                data-product-price="${product.price}"
                                data-product-image="${product.image || ''}"
                                ${product.stock <= 0 ? 'disabled' : ''}>
                            ${product.stock > 0 ? 'Add to Cart' : 'Out of Stock'}
                        </button>
                    </div>
                </div>
            `;
        }

        // Event listeners
        document.getElementById('apply-filters').addEventListener('click', () => loadProducts(true));
        document.getElementById('clear-filters').addEventListener('click', () => {
            document.getElementById('search-input').value = '';
            document.getElementById('category-filter').value = '';
            document.getElementById('min-price').value = '';
            document.getElementById('max-price').value = '';
            loadProducts(true);
        });
        document.getElementById('load-more').addEventListener('click', () => loadProducts(false));

        // Search on Enter key
        document.getElementById('search-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                loadProducts(true);
            }
        });
        <?php endif; ?>

        // Attach event listeners to add to cart buttons
        function attachAddToCartListeners() {
            document.querySelectorAll('.add-to-cart-btn:not(.listener-attached)').forEach(button => {
                button.addEventListener('click', addToCart);
                button.classList.add('listener-attached');
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', attachAddToCartListeners);
    </script>
</body>
</html>