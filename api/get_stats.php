<?php
require_once '../config/config.php';
header('Content-Type: application/json');

// Check if user is admin
require_admin();

$type = $_GET['type'] ?? '';

try {
    $pdo = getDBConnection();
    
    switch ($type) {
        case 'products':
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
            $result = $stmt->fetch();
            echo json_encode(['success' => true, 'count' => $result['count']]);
            break;
            
        case 'orders':
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
            $result = $stmt->fetch();
            echo json_encode(['success' => true, 'count' => $result['count'] ?? 0]);
            break;
            
        case 'customers':
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
            $result = $stmt->fetch();
            echo json_encode(['success' => true, 'count' => $result['count']]);
            break;
            
        case 'revenue':
            $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status = 'paid'");
            $result = $stmt->fetch();
            echo json_encode(['success' => true, 'total' => number_format($result['total'], 2)]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid stat type']);
    }
    
} catch (Exception $e) {
    error_log("Get stats API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading statistics']);
}
?>