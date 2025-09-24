<?php
require_once 'config/config.php';

echo "=== TESTING CART API ENDPOINT ===\n\n";

// Set up session as logged-in user
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'customer';
$_SESSION['user_email'] = 'admin@smarttechbeauty.com';
$_SESSION['user_name'] = 'Test Customer';

echo "--- TESTING API/CART.PHP?ACTION=GET ---\n";

// Simulate the exact HTTP request that checkout.php makes
try {
    // Since we can't make HTTP requests easily from CLI, we'll include the API file directly
    // and capture its output
    
    $_GET['action'] = 'get'; // Set the action parameter
    
    // Start output buffering to capture the API response
    ob_start();
    
    // Include the cart API file
    include 'api/cart.php';
    
    // Get the output
    $apiOutput = ob_get_clean();
    
    echo "API Response:\n";
    echo $apiOutput . "\n";
    
    // Parse the JSON response
    $responseData = json_decode($apiOutput, true);
    
    if ($responseData === null) {
        echo "❌ Failed to parse JSON response\n";
        echo "Raw output: " . $apiOutput . "\n";
    } else {
        echo "\n--- PARSED API RESPONSE ---\n";
        echo "Success: " . ($responseData['success'] ? 'YES' : 'NO') . "\n";
        
        if ($responseData['success']) {
            echo "Items count: " . count($responseData['items']) . "\n";
            
            echo "\nItems details:\n";
            foreach($responseData['items'] as $item) {
                echo "- {$item['name']} x{$item['quantity']} @ R{$item['price']} = R" . 
                     number_format($item['price'] * $item['quantity'], 2) . "\n";
            }
            
            echo "\nSummary:\n";
            echo "- Subtotal: R" . number_format($responseData['summary']['subtotal'], 2) . "\n";
            echo "- VAT: R" . number_format($responseData['summary']['vat'], 2) . "\n";
            echo "- Total: R" . number_format($responseData['summary']['total'], 2) . "\n";
            echo "- Item count: " . $responseData['summary']['item_count'] . "\n";
            
            echo "\n✅ Cart API is working correctly!\n";
            echo "✅ Checkout.php will receive proper data!\n";
            
        } else {
            echo "❌ API returned success=false\n";
            if (isset($responseData['message'])) {
                echo "Error message: " . $responseData['message'] . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error testing API: " . $e->getMessage() . "\n";
}

echo "\n=== API TEST COMPLETE ===\n";
?>