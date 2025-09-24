<?php
require_once 'config/config.php';

echo "=== DIAGNOSTIC REPORT ===\n\n";

try {
    $pdo = getDBConnection();
    echo "✅ Database connection: OK\n\n";
    
    // Check admin users
    echo "--- ADMIN USERS ---\n";
    $stmt = $pdo->query('SELECT id, fullName, email, role FROM users WHERE role = "admin"');
    $admins = $stmt->fetchAll();
    echo "Admin users found: " . count($admins) . "\n";
    foreach($admins as $admin) {
        echo "ID: {$admin['id']}, Name: {$admin['fullName']}, Email: {$admin['email']}\n";
    }
    
    // Test password hashing (admin password should be admin123)
    echo "\n--- PASSWORD TEST ---\n";
    if (!empty($admins)) {
        $admin = $admins[0];
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$admin['id']]);
        $hashedPassword = $stmt->fetchColumn();
        
        $testPasswords = ['admin123', 'admin', 'password'];
        foreach($testPasswords as $testPass) {
            if (password_verify($testPass, $hashedPassword)) {
                echo "✅ Admin password is: $testPass\n";
                break;
            }
        }
        echo "Stored hash: " . substr($hashedPassword, 0, 30) . "...\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n--- API FILES CHECK ---\n";
$apiFiles = [
    'api/upload_product.php',
    'api/admin_products.php', 
    'api/get_stats.php',
    'api/get_categories.php',
    'api/delete_product.php',
    'api/cart.php'
];

foreach($apiFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS\n";
    } else {
        echo "❌ $file - MISSING\n";
    }
}

echo "\n--- REQUIRED DIRECTORIES ---\n";
$dirs = ['uploads', 'uploads/products', 'admin', 'api', 'config', 'includes'];
foreach($dirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ $dir/ - EXISTS\n";
    } else {
        echo "❌ $dir/ - MISSING\n";
    }
}

echo "\n--- SESSION & CONFIG TEST ---\n";
echo "Session status: " . session_status() . "\n";
echo "Site name: " . SITE_NAME . "\n";
echo "Products images dir: " . PRODUCTS_IMAGES_DIR . "\n";

echo "\n--- CART ITEMS TEST ---\n";
// Check if there are cart items for testing checkout
try {
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM cart');
    $cartCount = $stmt->fetchColumn();
    echo "Cart items in database: $cartCount\n";
    
    if ($cartCount > 0) {
        $stmt = $pdo->query('SELECT c.*, p.name FROM cart c JOIN products p ON c.product_id = p.id LIMIT 3');
        $cartItems = $stmt->fetchAll();
        echo "Sample cart items:\n";
        foreach($cartItems as $item) {
            echo "- User ID: {$item['user_id']}, Product: {$item['name']}, Qty: {$item['quantity']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error checking cart: " . $e->getMessage() . "\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
?>