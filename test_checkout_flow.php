<?php
require_once 'config/config.php';

echo "=== TESTING COMPLETE CHECKOUT FLOW ===\n\n";

// Set up session as logged-in user
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'customer';
$_SESSION['user_email'] = 'admin@smarttechbeauty.com';
$_SESSION['user_name'] = 'Test Customer';

try {
    $pdo = getDBConnection();
    
    echo "--- STEP 1: CHECK USER LOGIN ---\n";
    echo "User ID: " . $_SESSION['user_id'] . "\n";
    echo "Is logged in: " . (is_logged_in() ? '✅ YES' : '❌ NO') . "\n";
    
    echo "\n--- STEP 2: CHECK CART ITEMS ---\n";
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.is_active = 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll();
    
    if (empty($cartItems)) {
        echo "❌ Cart is empty! Checkout will redirect to cart.\n";
        echo "Run add_test_cart_items.php first.\n";
        exit;
    }
    
    echo "✅ Cart has " . count($cartItems) . " items:\n";
    foreach($cartItems as $item) {
        echo "- {$item['name']} x{$item['quantity']} @ R{$item['price']}\n";
    }
    
    echo "\n--- STEP 3: SIMULATE CART API CALL ---\n";
    // Simulate the cart API call that checkout.php makes
    $subtotal = array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cartItems));
    
    $vat = $subtotal * 0.15;
    $total = $subtotal + $vat;
    
    $apiResponse = [
        'success' => true,
        'items' => $cartItems,
        'summary' => [
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $total,
            'item_count' => array_sum(array_column($cartItems, 'quantity'))
        ]
    ];
    
    echo "✅ Cart API would return:\n";
    echo "- Items: " . count($apiResponse['items']) . "\n";
    echo "- Total: R" . number_format($apiResponse['summary']['total'], 2) . "\n";
    
    echo "\n--- STEP 4: CHECK STOCK AVAILABILITY ---\n";
    $stockOk = true;
    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['stock']) {
            echo "❌ Insufficient stock for {$item['name']}. Requested: {$item['quantity']}, Available: {$item['stock']}\n";
            $stockOk = false;
        } else {
            echo "✅ {$item['name']}: Stock OK ({$item['quantity']}/{$item['stock']})\n";
        }
    }
    
    echo "\n--- STEP 5: CHECK USER PROFILE ---\n";
    $user = get_logged_in_user();
    if ($user) {
        echo "✅ User profile loaded:\n";
        echo "- Name: " . ($user['fullName'] ?? 'Not set') . "\n";
        echo "- Email: " . ($user['email'] ?? 'Not set') . "\n";
        echo "- Phone: " . ($user['phone'] ?? 'Not set') . "\n";
        echo "- Address: " . ($user['address'] ?? 'Not set') . "\n";
        echo "- City: " . ($user['city'] ?? 'Not set') . "\n";
    } else {
        echo "❌ Could not load user profile\n";
    }
    
    echo "\n--- CHECKOUT READINESS SUMMARY ---\n";
    echo "User logged in: " . (is_logged_in() ? '✅' : '❌') . "\n";
    echo "Cart has items: " . (!empty($cartItems) ? '✅' : '❌') . "\n";
    echo "Stock available: " . ($stockOk ? '✅' : '❌') . "\n";
    echo "User profile loaded: " . ($user ? '✅' : '❌') . "\n";
    
    if (is_logged_in() && !empty($cartItems) && $stockOk && $user) {
        echo "\n🎉 CHECKOUT SHOULD WORK!\n";
        echo "\n✅ All requirements met. Checkout.php should:\n";
        echo "1. NOT redirect to cart.php\n";
        echo "2. Display cart items\n";
        echo "3. Show order summary\n";
        echo "4. Allow order placement\n";
    } else {
        echo "\n❌ CHECKOUT WILL HAVE ISSUES\n";
        echo "Fix the failed checks above.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>