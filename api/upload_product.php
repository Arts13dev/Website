<?php
require_once '../config/config.php';
header('Content-Type: application/json');

// Check if user is admin
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }
    
    // Validate required fields
    $name = sanitize_input($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    
    if (empty($name) || $price <= 0 || $stock < 0 || $category_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields correctly']);
        exit;
    }
    
    // Handle optional fields
    $short_description = sanitize_input($_POST['short_description'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $brand = sanitize_input($_POST['brand'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Generate slug from name
    $slug = generate_slug($name);
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['image'];
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($uploadedFile['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
            exit;
        }
        
        if ($uploadedFile['size'] > MAX_FILE_SIZE) {
            echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size is 5MB.']);
            exit;
        }
        
        // Generate unique filename
        $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $filename = uniqid('product_') . '.' . $extension;
        $uploadPath = PRODUCTS_IMAGES_DIR . $filename;
        
        // Create directory if it doesn't exist
        if (!file_exists(dirname($uploadPath))) {
            mkdir(dirname($uploadPath), 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
            $imagePath = $uploadPath;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Product image is required']);
        exit;
    }
    
    // Insert product into database
    $pdo = getDBConnection();
    
    // Check if slug exists and make it unique
    $originalSlug = $slug;
    $counter = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        if (!$stmt->fetch()) break;
        $slug = $originalSlug . '-' . $counter++;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO products (
            name, slug, description, short_description, price, stock, 
            category_id, brand, image, featured, is_active, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW()
        )
    ");
    
    if ($stmt->execute([
        $name, $slug, $description, $short_description, $price, $stock,
        $category_id, $brand, $imagePath, $featured
    ])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Product added successfully!',
            'product_id' => $pdo->lastInsertId()
        ]);
    } else {
        // Delete uploaded image if database insertion failed
        if ($imagePath && file_exists($imagePath)) {
            unlink($imagePath);
        }
        echo json_encode(['success' => false, 'message' => 'Failed to save product to database']);
    }
    
} catch (Exception $e) {
    error_log("Upload product error: " . $e->getMessage());
    
    // Clean up uploaded file if there was an error
    if (isset($imagePath) && $imagePath && file_exists($imagePath)) {
        unlink($imagePath);
    }
    
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding the product']);
}
?>