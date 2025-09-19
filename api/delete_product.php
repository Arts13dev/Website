<?php
require_once '../config/config.php';
header('Content-Type: application/json');

// Check if user is admin
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = intval($input['product_id'] ?? 0);
    
    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    $pdo = getDBConnection();
    
    // Get product image path before deletion
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Delete product from database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    
    if ($stmt->execute([$productId])) {
        // Delete image file if it exists
        if ($product['image'] && file_exists($product['image'])) {
            unlink($product['image']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete product from database']);
    }
    
} catch (Exception $e) {
    error_log("Delete product error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the product']);
}
?>