<?php
require_once 'config/config.php';

echo "=== ADDING TEST CART ITEMS ===\n\n";

// Set up session as if user is logged in
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'customer';
$_SESSION['user_email'] = 'admin@smarttechbeauty.com';
$_SESSION['user_name'] = 'Test Customer';

try {
    $pdo = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    // First clear any existing cart items
    $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
    $stmt->execute([$userId]);
    echo "✅ Cleared existing cart items\n";
    
    // Get some active products
    $stmt = $pdo->query('SELECT id, name, price, stock FROM products WHERE is_active = 1 AND stock > 0 LIMIT 3');
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        echo "❌ No active products with stock found!\n";
        exit;
    }
    
    echo "Adding products to cart:\n";
    
    foreach($products as $i => $product) {
        $quantity = $i + 1; // 1, 2, 3 quantities for variety
        
        $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $product['id'], $quantity, $product['price']]);
        
        echo "✅ Added: {$product['name']} x$quantity @ R{$product['price']} each\n";
    }
    
    // Verify cart contents
    echo "\n--- CART VERIFICATION ---\n";
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();
    
    $subtotal = 0;
    echo "Cart contents:\n";
    foreach($cartItems as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $subtotal += $itemTotal;
        echo "- {$item['name']} x{$item['quantity']} = R" . number_format($itemTotal, 2) . "\n";
    }
    
    $vat = $subtotal * 0.15;
    $total = $subtotal + $vat;
    
    echo "\nOrder Summary:\n";
    echo "Subtotal: R" . number_format($subtotal, 2) . "\n";
    echo "VAT (15%): R" . number_format($vat, 2) . "\n";
    echo "Total: R" . number_format($total, 2) . "\n";
    
    echo "\n🎉 SUCCESS! Cart is now ready for checkout testing.\n";
    echo "\nNext steps:\n";
    echo "1. Login as admin@smarttechbeauty.com / password\n";
    echo "2. Go to checkout.php\n";
    echo "3. Should now show cart items instead of redirecting\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>