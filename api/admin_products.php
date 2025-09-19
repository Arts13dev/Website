<?php
require_once '../config/config.php';
header('Content-Type: application/json');

// Check if user is admin
require_admin();

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    error_log("Admin products API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading products',
        'products' => []
    ]);
}
?>