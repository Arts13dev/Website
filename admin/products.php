<?php
require_once '../config/config.php';

// Check if user is logged in and is admin
require_admin();

// Handle product operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }
        
        $pdo = getDBConnection();
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add' || $action === 'edit') {
            $name = sanitize_input($_POST['name'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
            $stock = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT);
            $category_id = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $productId = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
            
            // Validation
            if (empty($name) || $price === false || $stock === false || $category_id === false || $category_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
                exit;
            }
            
            if ($price < 0 || $stock < 0) {
                echo json_encode(['success' => false, 'message' => 'Price and stock must be positive numbers']);
                exit;
            }
            
            // Handle image upload
            $imagePath = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../' . PRODUCTS_IMAGES_DIR;
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ALLOWED_IMAGE_TYPES;
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    echo json_encode(['success' => false, 'message' => 'Only ' . implode(', ', $allowedExtensions) . ' files are allowed']);
                    exit;
                }
                
                if ($_FILES['image']['size'] > MAX_FILE_SIZE) {
                    echo json_encode(['success' => false, 'message' => 'Image size should not exceed ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB']);
                    exit;
                }
                
                $fileName = uniqid('product_') . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = PRODUCTS_IMAGES_DIR . $fileName;
                    
                    // Delete old image if editing
                    if ($action === 'edit' && $productId) {
                        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                        $stmt->execute([$productId]);
                        $oldImage = $stmt->fetchColumn();
                        if ($oldImage && file_exists($oldImage)) {
                            try {
                                unlink($oldImage);
                            } catch (Exception $e) {
                                error_log("Failed to delete old image: " . $e->getMessage());
                            }
                        }
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
                    exit;
                }
            }
            
            if ($action === 'add') {
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, description, price, stock, category_id, image, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $description, $price, $stock, $category_id, $imagePath, $isActive]);
                echo json_encode(['success' => true, 'message' => 'Product added successfully']);
                
            } else if ($action === 'edit' && $productId) {
                if ($imagePath) {
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?, is_active = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $description, $price, $stock, $category_id, $imagePath, $isActive, $productId]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, is_active = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $description, $price, $stock, $category_id, $isActive, $productId]);
                }
                echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
            }
            
        } else if ($action === 'delete') {
            $productId = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$productId) {
                echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
                exit;
            }
            
            // Get image path to delete file
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $image = $stmt->fetchColumn();
            
            // Delete product
            $stmt = $pdo->prepare("UPDATE products SET is_active = 0, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$productId]);
            
            // Delete image file with proper error handling
            if ($image && file_exists($image)) {
                try {
                    unlink($image);
                } catch (Exception $e) {
                    error_log("Failed to delete product image: " . $e->getMessage());
                    // Continue anyway - product deletion is more important
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        }
        
    } catch (Exception $e) {
        error_log("Product operation error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred']);
    }
    exit;
}

// Get products with search and pagination
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    $pdo = getDBConnection();
    
    // Build query
    $whereClause = "WHERE p.is_active = 1";
    $params = [];
    
    if ($search) {
        $whereClause .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
    }
    
    // Get total count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereClause");
    $stmt->execute($params);
    $totalProducts = $stmt->fetchColumn();
    $totalPages = ceil($totalProducts / $limit);
    
    // Get products with category names
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $whereClause 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Get categories for dropdown
    $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Products fetch error: " . $e->getMessage());
    $products = [];
    $categories = [];
    $totalProducts = 0;
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Admin Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-bold text-purple-600">
                        <?php echo SITE_NAME; ?> Admin
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md font-medium">
                        Dashboard
                    </a>
                    <a href="products.php" class="bg-purple-100 text-purple-600 px-3 py-2 rounded-md font-medium">
                        Products
                    </a>
                    <a href="orders.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md font-medium">
                        Orders
                    </a>
                    <a href="users.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md font-medium">
                        Users
                    </a>
                    <a href="../home.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md font-medium">
                        View Site
                    </a>
                    <a href="../logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Products Management</h1>
                <p class="text-gray-600">Manage your product catalog</p>
            </div>
            <button onclick="openProductModal()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                Add New Product
            </button>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm mb-6 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search products..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                    Search
                </button>
                <?php if ($search): ?>
                    <a href="products.php" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors text-center">
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Products Count -->
        <div class="mb-4">
            <p class="text-gray-600">
                Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products
                <?php if ($search): ?>
                    for "<?php echo htmlspecialchars($search); ?>"
                <?php endif; ?>
            </p>
        </div>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Products Found</h3>
                <p class="text-gray-600 mb-6">
                    <?php echo $search ? 'No products match your search criteria.' : 'Start by adding your first product.'; ?>
                </p>
                <button onclick="openProductModal()" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors">
                    Add Product
                </button>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img src="<?php echo $product['image'] ? htmlspecialchars($product['image']) : 'https://placehold.co/60x60'; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="w-12 h-12 object-cover rounded">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : ''); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($product['category_name'] ?? 'No Category'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        R<?php echo number_format($product['price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">
                                            <?php echo $product['stock']; ?>
                                        </span>
                                        <?php if ($product['stock'] <= 5): ?>
                                            <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Low Stock
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php echo $product['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)"
                                                    class="text-blue-600 hover:text-blue-900">
                                                Edit
                                            </button>
                                            <button onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')"
                                                    class="text-red-600 hover:text-red-900">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center mt-6">
                    <nav class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                               class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                               class="px-3 py-2 border rounded-lg <?php echo $i === $page ? 'bg-purple-600 text-white border-purple-600' : 'bg-white border-gray-300 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                               class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 id="modalTitle" class="text-2xl font-bold text-gray-900">Add Product</h2>
                        <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form id="productForm" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="product_id" id="productId" value="">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                                <input type="text" name="name" id="productName" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price (R) *</label>
                                <input type="number" name="price" id="productPrice" step="0.01" min="0" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity *</label>
                                <input type="number" name="stock" id="productStock" min="0" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                                <select name="category_id" id="productCategoryId" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" id="productDescription" rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
                                <input type="file" name="image" id="productImage" accept="image/*"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <p class="text-xs text-gray-500 mt-1">Max file size: 5MB. Supported formats: JPG, PNG, GIF</p>
                                <div id="imagePreview" class="mt-2 hidden">
                                    <img id="previewImg" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg">
                                </div>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" id="productActive" checked
                                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-900">Active (visible to customers)</span>
                                </label>
                            </div>
                        </div>
                        
                        <div id="error-message" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg"></div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeProductModal()"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" id="submitBtn"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                Add Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isEditing = false;

        // Image preview functionality
        document.getElementById('productImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('imagePreview').classList.add('hidden');
            }
        });

        function openProductModal() {
            isEditing = false;
            document.getElementById('modalTitle').textContent = 'Add Product';
            document.getElementById('formAction').value = 'add';
            document.getElementById('submitBtn').textContent = 'Add Product';
            document.getElementById('productForm').reset();
            document.getElementById('productActive').checked = true;
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('error-message').classList.add('hidden');
            document.getElementById('productModal').classList.remove('hidden');
        }

        function editProduct(product) {
            isEditing = true;
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('submitBtn').textContent = 'Update Product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productCategoryId').value = product.category_id;
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('productActive').checked = product.is_active == 1;
            
            if (product.image) {
                document.getElementById('previewImg').src = product.image;
                document.getElementById('imagePreview').classList.remove('hidden');
            } else {
                document.getElementById('imagePreview').classList.add('hidden');
            }
            
            document.getElementById('error-message').classList.add('hidden');
            document.getElementById('productModal').classList.remove('hidden');
        }

        function closeProductModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        function deleteProduct(productId, productName) {
            if (confirm(`Are you sure you want to delete "${productName}"?`)) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('product_id', productId);
                
                fetch('products.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    alert('An error occurred while deleting the product');
                });
            }
        }

        // Form submission
        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const errorDiv = document.getElementById('error-message');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>Processing...';
            errorDiv.classList.add('hidden');
            
            const formData = new FormData(this);
            
            fetch('products.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = isEditing ? 'Update Product' : 'Add Product';
                }
            })
            .catch(error => {
                console.error('Form submission error:', error);
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = isEditing ? 'Update Product' : 'Add Product';
            });
        });

        // Close modal when clicking outside
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProductModal();
            }
        });
    </script>
</body>
</html>