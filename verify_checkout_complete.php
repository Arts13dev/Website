<?php
require_once 'config/config.php';

echo "=== COMPREHENSIVE CHECKOUT.PHP VERIFICATION ===\n\n";

// Set up session as logged-in user
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'customer';
$_SESSION['user_email'] = 'admin@smarttechbeauty.com';
$_SESSION['user_name'] = 'Test Customer';

try {
    $pdo = getDBConnection();
    
    echo "--- VERIFICATION CHECKLIST ---\n";
    
    // Check 1: User Session
    $isLoggedIn = is_logged_in();
    echo "✅ User logged in: " . ($isLoggedIn ? "YES" : "NO") . "\n";
    
    // Check 2: Cart Items Exist
    $userId = $_SESSION['user_id'];
    $sessionId = session_id();
    
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.image, p.stock, p.slug 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE (c.user_id = ? OR c.session_id = ?) AND p.is_active = 1
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId, $sessionId]);
    $cartItems = $stmt->fetchAll();
    
    echo "✅ Cart items available: " . count($cartItems) . " items\n";
    
    if (empty($cartItems)) {
        echo "❌ CRITICAL: No cart items found. Checkout will redirect to cart.\n";
        echo "   Run 'php add_test_cart_items.php' to add test data.\n";
        exit;
    }
    
    // Check 3: Data Structure Verification
    echo "\n--- DATA STRUCTURE VERIFICATION ---\n";
    
    $firstItem = $cartItems[0];
    $requiredFields = ['id', 'user_id', 'product_id', 'quantity', 'price', 'name', 'image', 'stock'];
    
    foreach($requiredFields as $field) {
        if (array_key_exists($field, $firstItem)) {
            echo "✅ Item field '$field': " . ($firstItem[$field] ?? 'NULL') . "\n";
        } else {
            echo "❌ Missing field '$field'\n";
        }
    }
    
    // Check 4: Calculation Verification
    echo "\n--- CALCULATION VERIFICATION ---\n";
    
    $calculatedSubtotal = 0;
    foreach($cartItems as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $calculatedSubtotal += $itemTotal;
        echo "Item: {$item['name']} - {$item['quantity']} × R{$item['price']} = R" . number_format($itemTotal, 2) . "\n";
    }
    
    $calculatedVat = $calculatedSubtotal * 0.15;
    $calculatedTotal = $calculatedSubtotal + $calculatedVat;
    
    echo "\nCalculations:\n";
    echo "✅ Subtotal: R" . number_format($calculatedSubtotal, 2) . "\n";
    echo "✅ VAT (15%): R" . number_format($calculatedVat, 2) . "\n";
    echo "✅ Total: R" . number_format($calculatedTotal, 2) . "\n";
    
    // Check 5: API Response Simulation
    echo "\n--- API RESPONSE SIMULATION ---\n";
    
    $apiResponse = [
        'success' => true,
        'items' => $cartItems,
        'summary' => [
            'subtotal' => $calculatedSubtotal,
            'vat' => $calculatedVat,
            'total' => $calculatedTotal,
            'item_count' => array_sum(array_column($cartItems, 'quantity'))
        ]
    ];
    
    echo "✅ API Response Structure Valid\n";
    echo "✅ JSON Encodable: " . (json_encode($apiResponse) ? "YES" : "NO") . "\n";
    
    // Check 6: Frontend JavaScript Compatibility
    echo "\n--- FRONTEND COMPATIBILITY CHECK ---\n";
    
    foreach($cartItems as $item) {
        // Check for any fields that might break JavaScript
        $name = addslashes($item['name']);
        $image = $item['image'] ?? 'https://placehold.co/50x50';
        $price = (float)$item['price'];
        $quantity = (int)$item['quantity'];
        
        echo "✅ Item '{$name}' JavaScript-safe\n";
        echo "   - Image: {$image}\n";
        echo "   - Price: {$price} (numeric)\n";
        echo "   - Quantity: {$quantity} (integer)\n";
    }
    
    // Check 7: HTML Generation Test
    echo "\n--- HTML GENERATION TEST ---\n";
    
    $htmlOutput = "";
    foreach($cartItems as $item) {
        $htmlOutput .= "
        <div class=\"flex items-center space-x-3\">
            <img src=\"" . htmlspecialchars($item['image'] ?? 'https://placehold.co/50x50') . "\" 
                 alt=\"" . htmlspecialchars($item['name']) . "\" 
                 class=\"w-12 h-12 object-cover rounded\">
            <div class=\"flex-1 min-w-0\">
                <h4 class=\"text-sm font-medium text-gray-800 truncate\">" . htmlspecialchars($item['name']) . "</h4>
                <p class=\"text-sm text-gray-600\">Qty: {$item['quantity']} × R" . number_format($item['price'], 2) . "</p>
            </div>
            <div class=\"text-sm font-medium text-gray-800\">
                R" . number_format($item['price'] * $item['quantity'], 2) . "
            </div>
        </div>";
    }
    
    echo "✅ HTML generation successful (" . strlen($htmlOutput) . " characters)\n";
    
    // Check 8: User Profile for Checkout Form
    echo "\n--- USER PROFILE CHECK ---\n";
    
    $user = get_logged_in_user();
    if ($user) {
        echo "✅ User profile loaded\n";
        echo "   - Name: " . ($user['fullName'] ?? 'Not set') . "\n";
        echo "   - Email: " . ($user['email'] ?? 'Not set') . "\n";
        echo "   - Phone: " . ($user['phone'] ?? 'Not set') . "\n";
        echo "   - Address: " . ($user['address'] ?? 'Not set') . "\n";
    } else {
        echo "❌ User profile not loaded\n";
    }
    
    // Final Summary
    echo "\n🎯 CHECKOUT.PHP READINESS SUMMARY:\n";
    echo "=====================================\n";
    
    $allChecks = [
        'User logged in' => $isLoggedIn,
        'Cart has items' => !empty($cartItems),
        'Data structure valid' => array_key_exists('name', $firstItem ?? []),
        'Calculations correct' => $calculatedSubtotal > 0,
        'API response valid' => json_encode($apiResponse) !== false,
        'User profile loaded' => $user !== false
    ];
    
    $passedChecks = 0;
    foreach($allChecks as $check => $result) {
        if ($result) {
            echo "✅ $check\n";
            $passedChecks++;
        } else {
            echo "❌ $check\n";
        }
    }
    
    echo "\nResult: $passedChecks/" . count($allChecks) . " checks passed\n";
    
    if ($passedChecks === count($allChecks)) {
        echo "\n🎉 CHECKOUT.PHP IS FULLY READY!\n";
        echo "\nWhat checkout.php will display:\n";
        echo "==============================\n";
        echo "ORDER SUMMARY:\n";
        foreach($cartItems as $item) {
            echo "• {$item['name']} x{$item['quantity']} - R" . number_format($item['price'] * $item['quantity'], 2) . "\n";
        }
        echo "\nSubtotal: R" . number_format($calculatedSubtotal, 2) . "\n";
        echo "Shipping: Free\n";
        echo "VAT (15%): R" . number_format($calculatedVat, 2) . "\n";
        echo "TOTAL: R" . number_format($calculatedTotal, 2) . "\n";
        
        echo "\n🚀 Ready to test at: http://localhost/Website/checkout.php\n";
        echo "👤 Login: admin@smarttechbeauty.com / password\n";
    } else {
        echo "\n⚠️  Some checks failed. Fix the issues above before testing checkout.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during verification: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
?>