<?php
require_once '../config/config.php';
header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    $categories = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    error_log("Get categories API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading categories',
        'categories' => []
    ]);
}
?>