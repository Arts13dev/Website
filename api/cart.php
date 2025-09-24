<?php
require_once '../config/config.php';

header('Content-Type: application/json');

// User must be logged in to interact with the server-side cart
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];
$pdo = getDBConnection();

switch ($action) {
    case 'sync':
        handleSync($pdo, $userId);
        break;
    case 'get':
        handleGet($pdo, $userId);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}

function handleSync($pdo, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $items = $input['items'] ?? [];

    if (!is_array($items)) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart data.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Clear the user's existing server-side cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);

        // 2. Insert the new cart items from localStorage
        $stmt = $pdo->prepare("
            INSERT INTO cart (user_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            if (isset($item['id'], $item['quantity'], $item['price'])) {
                $stmt->execute([
                    $userId,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Cart synchronized successfully.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Cart sync error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to sync cart.']);
    }
}

function handleGet($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.product_id, c.quantity, c.price, p.name, p.image 
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND p.is_active = 1
        ");
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
            return;
        }

        $subtotal = array_reduce($items, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
        $vat = $subtotal * 0.15;
        $total = $subtotal + $vat;

        echo json_encode([
            'success' => true,
            'items' => $items,
            'summary' => [
                'subtotal' => $subtotal,
                'vat' => $vat,
                'total' => $total,
            ]
        ]);

    } catch (Exception $e) {
        error_log("Get cart error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to retrieve cart.']);
    }
}
?>