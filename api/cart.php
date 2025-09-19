<?php
require_once '../config/config.php';
header('Content-Type: application/json');

// Handle different cart operations
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $pdo = getDBConnection();
    
    switch ($action) {
        case 'add':
            addToCart($pdo);
            break;
        case 'update':
            updateCart($pdo);
            break;
        case 'remove':
            removeFromCart($pdo);
            break;
        case 'get':
            getCart($pdo);
            break;
        case 'clear':
            clearCart($pdo);
            break;
        case 'sync':
            syncLocalCart($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Cart API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}

function addToCart($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $productId = intval($input['product_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 1);
    $price = floatval($input['price'] ?? 0);
    
    if ($productId <= 0 || $quantity <= 0 || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        return;
    }
    
    // Get user ID or session ID
    $userId = is_logged_in() ? $_SESSION['user_id'] : null;
    $sessionId = !$userId ? session_id() : null;
    
    // Check if item already exists in cart
    $stmt = $pdo->prepare("
        SELECT * FROM cart 
        WHERE product_id = ? AND (user_id = ? OR session_id = ?)
    ");
    $stmt->execute([$productId, $userId, $sessionId]);
    $existingItem = $stmt->fetch();
    
    if ($existingItem) {
        // Update quantity
        $newQuantity = $existingItem['quantity'] + $quantity;
        $stmt = $pdo->prepare("
            UPDATE cart 
            SET quantity = ?, price = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$newQuantity, $price, $existingItem['id']]);
    } else {
        // Add new item
        $stmt = $pdo->prepare("
            INSERT INTO cart (user_id, session_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $sessionId, $productId, $quantity, $price]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Item added to cart']);
}

function updateCart($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $cartId = intval($input['cart_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 0);
    
    if ($cartId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart ID']);
        return;
    }
    
    // Get user ID or session ID
    $userId = is_logged_in() ? $_SESSION['user_id'] : null;
    $sessionId = !$userId ? session_id() : null;
    
    if ($quantity <= 0) {
        // Remove item
        $stmt = $pdo->prepare("
            DELETE FROM cart 
            WHERE id = ? AND (user_id = ? OR session_id = ?)
        ");
        $stmt->execute([$cartId, $userId, $sessionId]);
    } else {
        // Update quantity
        $stmt = $pdo->prepare("
            UPDATE cart 
            SET quantity = ?, updated_at = NOW() 
            WHERE id = ? AND (user_id = ? OR session_id = ?)
        ");
        $stmt->execute([$quantity, $cartId, $userId, $sessionId]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Cart updated']);
}

function removeFromCart($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $cartId = intval($input['cart_id'] ?? 0);
    
    if ($cartId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart ID']);
        return;
    }
    
    // Get user ID or session ID
    $userId = is_logged_in() ? $_SESSION['user_id'] : null;
    $sessionId = !$userId ? session_id() : null;
    
    $stmt = $pdo->prepare("
        DELETE FROM cart 
        WHERE id = ? AND (user_id = ? OR session_id = ?)
    ");
    $stmt->execute([$cartId, $userId, $sessionId]);
    
    echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
}

function getCart($pdo) {
    // Get user ID or session ID
    $userId = is_logged_in() ? $_SESSION['user_id'] : null;
    $sessionId = !$userId ? session_id() : null;
    
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.image, p.stock, p.slug 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE (c.user_id = ? OR c.session_id = ?) AND p.is_active = 1
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId, $sessionId]);
    $cartItems = $stmt->fetchAll();
    
    $subtotal = array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cartItems));
    
    $vat = $subtotal * 0.15; // 15% VAT
    $total = $subtotal + $vat;
    
    echo json_encode([
        'success' => true,
        'items' => $cartItems,
        'summary' => [
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $total,
            'item_count' => array_sum(array_column($cartItems, 'quantity'))
        ]
    ]);
}

function clearCart($pdo) {
    // Get user ID or session ID
    $userId = is_logged_in() ? $_SESSION['user_id'] : null;
    $sessionId = !$userId ? session_id() : null;
    
    $stmt = $pdo->prepare("
        DELETE FROM cart 
        WHERE user_id = ? OR session_id = ?
    ");
    $stmt->execute([$userId, $sessionId]);
    
    echo json_encode(['success' => true, 'message' => 'Cart cleared']);
}

function syncLocalCart($pdo) {
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $localCart = $input['cart'] ?? [];
    
    if (empty($localCart)) {
        echo json_encode(['success' => true, 'message' => 'No items to sync']);
        return;
    }
    
    $userId = $_SESSION['user_id'];
    $sessionId = session_id();
    
    // First, merge any existing session cart with user cart
    $stmt = $pdo->prepare("
        UPDATE cart SET user_id = ?, session_id = NULL 
        WHERE session_id = ? AND user_id IS NULL
    ");
    $stmt->execute([$userId, $sessionId]);
    
    // Then add items from local storage
    foreach ($localCart as $item) {
        $productId = intval($item['id'] ?? 0);
        $quantity = intval($item['quantity'] ?? 0);
        $price = floatval($item['price'] ?? 0);
        
        if ($productId > 0 && $quantity > 0 && $price > 0) {
            // Check if item already exists
            $stmt = $pdo->prepare("
                SELECT id, quantity FROM cart 
                WHERE product_id = ? AND user_id = ?
            ");
            $stmt->execute([$productId, $userId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update quantity (add to existing)
                $newQuantity = $existing['quantity'] + $quantity;
                $stmt = $pdo->prepare("
                    UPDATE cart SET quantity = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$newQuantity, $existing['id']]);
            } else {
                // Add new item
                $stmt = $pdo->prepare("
                    INSERT INTO cart (user_id, product_id, quantity, price) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $productId, $quantity, $price]);
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Cart synchronized']);
}
?>