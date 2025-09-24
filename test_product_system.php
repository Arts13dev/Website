<?php
require_once 'config/config.php';

echo "=== TESTING UNIFIED PRODUCT MANAGEMENT SYSTEM ===\n\n";

// Test 1: Check session functionality
echo "--- SESSION TEST ---\n";
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_email'] = 'admin@smarttechbeauty.com';
$_SESSION['user_name'] = 'Administrator';

echo "Session user ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Session role: " . ($_SESSION['user_role'] ?? 'NOT SET') . "\n";
echo "Is admin: " . (is_admin() ? 'YES' : 'NO') . "\n";

// Test 2: Check database and categories
echo "\n--- DATABASE TEST ---\n";
try {
    $pdo = getDBConnection();
    
    // Check categories
    $stmt = $pdo->query('SELECT id, name FROM categories WHERE is_active = 1');
    $categories = $stmt->fetchAll();
    echo "Active categories: " . count($categories) . "\n";
    foreach($categories as $cat) {
        echo "- ID: {$cat['id']}, Name: {$cat['name']}\n";
    }
    
    // Check products
    $stmt = $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1');
    $productCount = $stmt->fetchColumn();
    echo "Active products: $productCount\n";
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

// Test 3: Check CSRF functionality
echo "\n--- CSRF TEST ---\n";
$token = generate_csrf_token();
echo "CSRF token generated: " . ($token ? 'YES' : 'NO') . "\n";
echo "CSRF token valid: " . (verify_csrf_token($token) ? 'YES' : 'NO') . "\n";

// Test 4: Check file paths and constants
echo "\n--- FILE PATHS TEST ---\n";
echo "Products images dir: " . PRODUCTS_IMAGES_DIR . "\n";
echo "Max file size: " . (MAX_FILE_SIZE / 1024 / 1024) . "MB\n";
echo "Allowed types: " . implode(', ', ALLOWED_IMAGE_TYPES) . "\n";

// Test 5: Check admin products page exists and is accessible
echo "\n--- FILE ACCESS TEST ---\n";
$adminFiles = [
    'admin/products.php',
    'admin/index.php'
];

foreach($adminFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS and READABLE\n";
    } else {
        echo "❌ $file - MISSING\n";
    }
}

// Test 6: Simulate admin access check
echo "\n--- ADMIN ACCESS TEST ---\n";
try {
    require_admin(); // This should not throw error since we set admin session
    echo "✅ Admin access check PASSED\n";
} catch (Exception $e) {
    echo "❌ Admin access check FAILED: " . $e->getMessage() . "\n";
}

echo "\n=== UNIFIED PRODUCT SYSTEM TEST COMPLETE ===\n";
?>