<?php
require_once 'config/config.php';

// Require user to be logged in
require_login();

// Get current user
$currentUser = get_logged_in_user();
if (!$currentUser) {
    redirect('login.php');
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $pdo = getDBConnection();
        
        // Get cart items
        $stmt = $pdo->prepare("
            SELECT c.*, p.name, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND p.is_active = 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cartItems = $stmt->fetchAll();
        
        if (empty($cartItems)) {
            echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
            exit;
        }
        
        // Validate input
        $shippingName = sanitize_input($_POST['shipping_name'] ?? '');
        $shippingEmail = sanitize_input($_POST['shipping_email'] ?? '');
        $shippingPhone = sanitize_input($_POST['shipping_phone'] ?? '');
        $shippingAddress = sanitize_input($_POST['shipping_address'] ?? '');
        $shippingCity = sanitize_input($_POST['shipping_city'] ?? '');
        $shippingPostalCode = sanitize_input($_POST['shipping_postal_code'] ?? '');
        $paymentMethod = sanitize_input($_POST['payment_method'] ?? '');
        
        if (empty($shippingName) || empty($shippingEmail) || empty($shippingAddress) || 
            empty($shippingCity) || empty($shippingPostalCode) || empty($paymentMethod)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            exit;
        }
        
        // Check stock availability
        foreach ($cartItems as $item) {
            if ($item['quantity'] > $item['stock']) {
                echo json_encode(['success' => false, 'message' => "Insufficient stock for {$item['name']}. Only {$item['stock']} available."]);
                exit;
            }
        }
        
        // Calculate totals
        $subtotal = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cartItems));
        
        $taxAmount = $subtotal * 0.15; // 15% VAT
        $shippingAmount = 0; // Free shipping
        $totalAmount = $subtotal + $taxAmount + $shippingAmount;
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Generate order number
        $orderNumber = 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                order_number, user_id, status, payment_status, payment_method,
                subtotal, tax_amount, shipping_amount, total_amount,
                shipping_name, shipping_email, shipping_phone, 
                shipping_address, shipping_city, shipping_postal_code
            ) VALUES (
                ?, ?, 'pending', 'pending', ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?
            )
        ");
        
        $stmt->execute([
            $orderNumber, $_SESSION['user_id'], $paymentMethod,
            $subtotal, $taxAmount, $shippingAmount, $totalAmount,
            $shippingName, $shippingEmail, $shippingPhone,
            $shippingAddress, $shippingCity, $shippingPostalCode
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Add order items and update stock
        foreach ($cartItems as $item) {
            // Add order item
            $stmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id, product_id, product_name, quantity, price, total
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderId, $item['product_id'], $item['name'], 
                $item['quantity'], $item['price'], 
                $item['price'] * $item['quantity']
            ]);
            
            // Update product stock
            $stmt = $pdo->prepare("
                UPDATE products 
                SET stock = stock - ?, sales_count = sales_count + ? 
                WHERE id = ?
            ");
            $stmt->execute([$item['quantity'], $item['quantity'], $item['product_id']]);
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_number' => $orderNumber,
            'order_id' => $orderId
        ]);
        
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollback();
        }
        error_log("Checkout error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while processing your order']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="home.php" class="text-gray-500 hover:text-purple-600">Home</a></li>
                <li><span class="mx-2">/</span><a href="cart.php" class="text-gray-500 hover:text-purple-600">Cart</a></li>
                <li><span class="mx-2">/</span><span class="text-gray-700">Checkout</span></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Checkout Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Checkout</h2>
                
                <form id="checkoutForm" class="space-y-6">
                    <!-- Shipping Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Shipping Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                <input type="text" name="shipping_name" required
                                       value="<?php echo htmlspecialchars($currentUser['fullName']); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" name="shipping_email" required
                                       value="<?php echo htmlspecialchars($currentUser['email']); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="tel" name="shipping_phone"
                                       value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                                <input type="text" name="shipping_city" required
                                       value="<?php echo htmlspecialchars($currentUser['city'] ?? ''); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                                <textarea name="shipping_address" rows="3" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code *</label>
                            <input type="text" name="shipping_postal_code" required
                                   value="<?php echo htmlspecialchars($currentUser['postal_code'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Payment Method</h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="radio" name="payment_method" value="cash_on_delivery" required
                                       class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500">
                                <span class="ml-3">Cash on Delivery</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="radio" name="payment_method" value="bank_transfer" required
                                       class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500">
                                <span class="ml-3">Bank Transfer</span>
                            </label>
                            
                            <label class="flex items-center opacity-50">
                                <input type="radio" name="payment_method" value="credit_card" disabled
                                       class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500">
                                <span class="ml-3">Credit Card (Coming Soon)</span>
                            </label>
                        </div>
                    </div>

                    <div id="error-message" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg"></div>
                    
                    <button type="submit" id="place-order-btn"
                            class="w-full bg-gradient-to-r from-purple-500 to-blue-600 text-white py-3 px-4 rounded-lg hover:from-purple-600 hover:to-blue-700 font-medium transition-all duration-200 shadow-md hover:shadow-lg">
                        Place Order
                    </button>
                </form>
            </div>

            <!-- Order Summary -->
            <div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h3>
                    
                    <div id="cart-items" class="space-y-3 mb-6">
                        <!-- Cart items will be loaded here -->
                        <div class="animate-pulse">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gray-200 rounded"></div>
                                <div class="flex-1">
                                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                                    <div class="h-3 bg-gray-200 rounded w-24"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span id="subtotal">R0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="text-green-600">Free</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">VAT (15%)</span>
                            <span id="vat">R0.00</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between font-semibold text-lg">
                            <span>Total</span>
                            <span id="total" class="text-purple-600">R0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadCartSummary();
            
            document.getElementById('checkoutForm').addEventListener('submit', function(e) {
                e.preventDefault();
                placeOrder();
            });
        });

        function loadCartSummary() {
            fetch('api/cart.php?action=get')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayCartItems(data.items, data.summary);
                } else {
                    window.location.href = 'cart.php';
                }
            })
            .catch(error => {
                console.error('Error loading cart:', error);
                window.location.href = 'cart.php';
            });
        }

        function displayCartItems(items, summary) {
            const container = document.getElementById('cart-items');
            
            if (items.length === 0) {
                window.location.href = 'cart.php';
                return;
            }

            container.innerHTML = items.map(item => `
                <div class="flex items-center space-x-3">
                    <img src="${item.image || 'https://placehold.co/50x50'}" 
                         alt="${item.name}" 
                         class="w-12 h-12 object-cover rounded">
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-800 truncate">${item.name}</h4>
                        <p class="text-sm text-gray-600">Qty: ${item.quantity} × R${parseFloat(item.price).toFixed(2)}</p>
                    </div>
                    <div class="text-sm font-medium text-gray-800">
                        R${(item.price * item.quantity).toFixed(2)}
                    </div>
                </div>
            `).join('');

            document.getElementById('subtotal').textContent = `R${summary.subtotal.toFixed(2)}`;
            document.getElementById('vat').textContent = `R${summary.vat.toFixed(2)}`;
            document.getElementById('total').textContent = `R${summary.total.toFixed(2)}`;
        }

        function placeOrder() {
            const btn = document.getElementById('place-order-btn');
            const errorDiv = document.getElementById('error-message');
            
            btn.disabled = true;
            btn.innerHTML = '<div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>Processing...';
            errorDiv.classList.add('hidden');

            const formData = new FormData(document.getElementById('checkoutForm'));
            
            fetch('checkout.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order placed successfully! Order number: ' + data.order_number);
                    window.location.href = 'orders.php';
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = 'Place Order';
                }
            })
            .catch(error => {
                console.error('Checkout error:', error);
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = 'Place Order';
            });
        }
    </script>
</body>
</html>