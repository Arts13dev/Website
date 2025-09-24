<?php
require_once 'config/config.php';

echo "=== TESTING CHECKOUT.PHP DATA RETRIEVAL ===\n\n";

// Set up session as logged-in user (same as what checkout.php expects)
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'customer';
$_SESSION['user_email'] = 'admin@smarttechbeauty.com';
$_SESSION['user_name'] = 'Test Customer';

echo "--- STEP 1: SIMULATE CHECKOUT.PHP SESSION ---\n";
echo "User ID: " . $_SESSION['user_id'] . "\n";
echo "Is logged in: " . (is_logged_in() ? 'YES' : 'NO') . "\n";
echo "Session ID: " . session_id() . "\n";

echo "\n--- STEP 2: TEST CART API CALL (same as checkout.php) ---\n";

try {
    // Simulate the exact same call that checkout.php makes
    // This replicates the fetch('api/cart.php?action=get') call
    
    $pdo = getDBConnection();
    
    // Get user ID or session ID (same logic as cart API)
    $userId = is_logged_in() ? $_SESSION['user_id'] : null;
    $sessionId = !$userId ? session_id() : null;
    
    echo "Looking for cart items with:\n";
    echo "- User ID: " . ($userId ?? 'NULL') . "\n";
    echo "- Session ID: " . ($sessionId ?? 'NULL') . "\n";
    
    // Execute the same query as getCart() function in cart API
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.image, p.stock, p.slug 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE (c.user_id = ? OR c.session_id = ?) AND p.is_active = 1
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId, $sessionId]);
    $cartItems = $stmt->fetchAll();
    
    echo "\n--- STEP 3: CART ITEMS RETRIEVED ---\n";
    echo "Items found: " . count($cartItems) . "\n";
    
    if (empty($cartItems)) {
        echo "❌ NO CART ITEMS FOUND!\n";
        echo "This means checkout.php will redirect to cart.php\n";
        
        // Check if there are ANY cart items in database
        $stmt = $pdo->query("SELECT COUNT(*) FROM cart");
        $totalItems = $stmt->fetchColumn();
        echo "\nTotal cart items in database: $totalItems\n";
        
        if ($totalItems > 0) {
            echo "⚠️  Cart items exist but not for current session/user\n";
            $stmt = $pdo->query("SELECT user_id, session_id, COUNT(*) as count FROM cart GROUP BY user_id, session_id");
            $groups = $stmt->fetchAll();
            echo "Cart distribution:\n";
            foreach($groups as $group) {
                echo "- User: " . ($group['user_id'] ?? 'NULL') . ", Session: " . ($group['session_id'] ?? 'NULL') . ", Items: {$group['count']}\n";
            }
        }
        exit;
    }
    
    echo "✅ Cart items found! Details:\n";
    foreach($cartItems as $item) {
        echo "- ID: {$item['id']}, Product: {$item['name']}, Qty: {$item['quantity']}, Price: R{$item['price']}\n";
        echo "  User ID: " . ($item['user_id'] ?? 'NULL') . ", Session: " . ($item['session_id'] ?? 'NULL') . "\n";
    }
    
    echo "\n--- STEP 4: CALCULATIONS (same as cart API) ---\n";
    
    // Same calculation logic as getCart() function
    $subtotal = array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cartItems));
    
    $vat = $subtotal * 0.15; // 15% VAT
    $total = $subtotal + $vat;
    
    $summary = [
        'subtotal' => $subtotal,
        'vat' => $vat,
        'total' => $total,
        'item_count' => array_sum(array_column($cartItems, 'quantity'))
    ];
    
    echo "Calculations:\n";
    echo "- Subtotal: R" . number_format($summary['subtotal'], 2) . "\n";
    echo "- VAT (15%): R" . number_format($summary['vat'], 2) . "\n";
    echo "- Total: R" . number_format($summary['total'], 2) . "\n";
    echo "- Item count: " . $summary['item_count'] . "\n";
    
    echo "\n--- STEP 5: API RESPONSE SIMULATION ---\n";
    
    // This is what the API would return
    $apiResponse = [
        'success' => true,
        'items' => $cartItems,
        'summary' => $summary
    ];
    
    echo "API Response structure:\n";
    echo "- success: " . ($apiResponse['success'] ? 'true' : 'false') . "\n";
    echo "- items count: " . count($apiResponse['items']) . "\n";
    echo "- summary keys: " . implode(', ', array_keys($apiResponse['summary'])) . "\n";
    
    echo "\n--- STEP 6: CHECKOUT DISPLAY SIMULATION ---\n";
    
    echo "Checkout.php would display:\n\n";
    echo "ORDER SUMMARY:\n";
    echo "=============\n";
    foreach($cartItems as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        echo "🛍️  {$item['name']}\n";
        echo "    Qty: {$item['quantity']} × R" . number_format($item['price'], 2) . " = R" . number_format($itemTotal, 2) . "\n";
        echo "    Image: " . ($item['image'] ?: 'No image') . "\n\n";
    }
    
    echo "💰 TOTALS:\n";
    echo "   Subtotal: R" . number_format($summary['subtotal'], 2) . "\n";
    echo "   Shipping: Free\n";
    echo "   VAT (15%): R" . number_format($summary['vat'], 2) . "\n";
    echo "   -------------------------\n";
    echo "   TOTAL: R" . number_format($summary['total'], 2) . "\n";
    
    echo "\n🎉 SUCCESS: Checkout.php will display cart items properly!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>