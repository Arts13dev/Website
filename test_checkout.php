<?php
require_once 'config/config.php';

echo "=== CHECKOUT ISSUE DIAGNOSIS ===\n\n";

// Set up test session
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'customer';
$_SESSION['user_email'] = 'admin@smarttechbeauty.com';
$_SESSION['user_name'] = 'Test Customer';

echo "--- SESSION CHECK ---\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Is logged in: " . (is_logged_in() ? 'YES' : 'NO') . "\n";

try {
    $pdo = getDBConnection();
    echo "\n--- CART STATUS ---\n";
    
    // Check total cart items in database
    $stmt = $pdo->query('SELECT COUNT(*) FROM cart');
    $totalCartItems = $stmt->fetchColumn();
    echo "Total cart items in database: $totalCartItems\n";
    
    // Check cart items for current user
    $userId = $_SESSION['user_id'];
    $sessionId = session_id();
    
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM cart WHERE user_id = ? OR session_id = ?');
    $stmt->execute([$userId, $sessionId]);
    $userCartItems = $stmt->fetchColumn();
    echo "Cart items for current user: $userCartItems\n";
    
    // Check available products
    $stmt = $pdo->query('SELECT id, name, price, stock FROM products WHERE is_active = 1 LIMIT 3');
    $products = $stmt->fetchAll();
    echo "\nAvailable products for adding to cart:\n";
    foreach($products as $product) {
        echo "- ID: {$product['id']}, Name: {$product['name']}, Price: R{$product['price']}, Stock: {$product['stock']}\n";
    }
    
    // Test the cart API
    echo "\n--- CART API TEST ---\n";
    if (!empty($products)) {
        $testProduct = $products[0];
        
        // Simulate adding item to cart
        echo "Testing add to cart for: {$testProduct['name']}\n";
        
        // Add item to cart directly in database
        $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $testProduct['id'], 1, $testProduct['price']]);
        echo "✅ Added test item to cart\n";
        
        // Check cart again
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM cart WHERE user_id = ? OR session_id = ?');
        $stmt->execute([$userId, $sessionId]);
        $newCartCount = $stmt->fetchColumn();
        echo "Cart items after adding: $newCartCount\n";
    }
    
    // Test cart API get function
    echo "\n--- TESTING CART API ---\n";
    
    // Simulate cart API call
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.image, p.stock, p.slug 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE (c.user_id = ? OR c.session_id = ?) AND p.is_active = 1
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId, $sessionId]);
    $cartItems = $stmt->fetchAll();
    
    if (!empty($cartItems)) {
        echo "✅ Cart API would return " . count($cartItems) . " items\n";
        $subtotal = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cartItems));
        
        $vat = $subtotal * 0.15;
        $total = $subtotal + $vat;
        
        echo "Subtotal: R" . number_format($subtotal, 2) . "\n";
        echo "VAT: R" . number_format($vat, 2) . "\n";
        echo "Total: R" . number_format($total, 2) . "\n";
        
        echo "\nCart items:\n";
        foreach($cartItems as $item) {
            echo "- {$item['name']} x{$item['quantity']} @ R{$item['price']} each\n";
        }
    } else {
        echo "❌ Cart is empty - this is why checkout redirects to cart!\n";
    }
    
    // Check checkout requirements
    echo "\n--- CHECKOUT REQUIREMENTS CHECK ---\n";
    echo "User logged in: " . (is_logged_in() ? '✅ YES' : '❌ NO') . "\n";
    echo "Cart has items: " . (!empty($cartItems) ? '✅ YES' : '❌ NO') . "\n";
    echo "Products are active: " . (!empty($products) ? '✅ YES' : '❌ NO') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
?>