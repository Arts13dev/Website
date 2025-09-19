<?php
require_once 'config/config.php';

echo "=== Testing Bug Fixes ===\n\n";

try {
    $pdo = getDBConnection();
    echo "✅ Database connection: OK\n";
    
    // Test categories table
    $stmt = $pdo->query('SELECT COUNT(*) FROM categories');
    $count = $stmt->fetchColumn();
    echo "✅ Categories count: $count\n";
    
    // Test products table structure
    $stmt = $pdo->query('DESCRIBE products');
    $columns = $stmt->fetchAll();
    $hasCategoryId = false;
    $hasCategory = false;
    
    foreach($columns as $col) {
        if($col['Field'] === 'category_id') {
            $hasCategoryId = true;
        }
        if($col['Field'] === 'category') {
            $hasCategory = true;
        }
    }
    
    if ($hasCategoryId) {
        echo "✅ products.category_id column exists\n";
    } else {
        echo "❌ products.category_id column missing\n";
    }
    
    if ($hasCategory) {
        echo "⚠️  products.category column still exists (legacy)\n";
    }
    
    // Test JOIN query
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1 
        LIMIT 1
    ");
    $stmt->execute();
    echo "✅ JOIN query with categories: OK\n";
    
    // Test file constants
    echo "\n=== Configuration Constants ===\n";
    echo "PRODUCTS_IMAGES_DIR: " . PRODUCTS_IMAGES_DIR . "\n";
    echo "MAX_FILE_SIZE: " . (MAX_FILE_SIZE / 1024 / 1024) . "MB\n";
    echo "ALLOWED_IMAGE_TYPES: " . implode(', ', ALLOWED_IMAGE_TYPES) . "\n";
    
    // Test CSRF functions
    $token = generate_csrf_token();
    $isValid = verify_csrf_token($token);
    echo "\n=== CSRF Protection ===\n";
    echo "CSRF token generation: " . ($token ? "✅ OK" : "❌ FAILED") . "\n";
    echo "CSRF token validation: " . ($isValid ? "✅ OK" : "❌ FAILED") . "\n";
    
    // Test upload directory
    echo "\n=== Upload Directories ===\n";
    $uploadDir = PRODUCTS_IMAGES_DIR;
    echo "Upload directory: $uploadDir\n";
    echo "Directory exists: " . (is_dir($uploadDir) ? "✅ YES" : "❌ NO") . "\n";
    echo "Directory writable: " . (is_writable($uploadDir) ? "✅ YES" : "❌ NO") . "\n";
    
    // Count sample images
    $imageCount = count(glob($uploadDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE));
    echo "Sample images available: $imageCount\n";
    
    echo "\n=== All Tests Completed ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>