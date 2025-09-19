<?php
require_once 'config/config.php';

// Require user to be logged in
require_login();

// Get current user
$currentUser = get_logged_in_user();
if (!$currentUser) {
    redirect('login.php');
}

// Get user's orders
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT o.*, 
               COUNT(oi.id) as item_count,
               GROUP_CONCAT(oi.product_name SEPARATOR ', ') as product_names
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Orders fetch error: " . $e->getMessage());
    $orders = [];
}

// Handle order details request
if (isset($_GET['order_id']) && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'details') {
    header('Content-Type: application/json');
    
    try {
        $orderId = (int)$_GET['order_id'];
        
        // Get order details
        $stmt = $pdo->prepare("
            SELECT * FROM orders 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        
        // Get order items
        $stmt = $pdo->prepare("
            SELECT oi.*, p.image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => $items
        ]);
        
    } catch (Exception $e) {
        error_log("Order details error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error loading order details']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - <?php echo SITE_NAME; ?></title>
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
                <li><span class="mx-2">/</span><span class="text-gray-700">My Orders</span></li>
            </ol>
        </nav>

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
            <a href="home.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                Continue Shopping
            </a>
        </div>

        <?php if (empty($orders)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Orders Yet</h3>
                <p class="text-gray-600 mb-6">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                <a href="home.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors inline-block">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Orders List -->
            <div class="space-y-4">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-4 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        Order #<?php echo htmlspecialchars($order['order_number']); ?>
                                    </h3>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        <?php 
                                        switch($order['status']) {
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'processing': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'shipped': echo 'bg-purple-100 text-purple-800'; break;
                                            case 'delivered': echo 'bg-green-100 text-green-800'; break;
                                            case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        <?php 
                                        switch($order['payment_status']) {
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'paid': echo 'bg-green-100 text-green-800'; break;
                                            case 'failed': echo 'bg-red-100 text-red-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        Payment: <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600">
                                    <div>
                                        <span class="font-medium text-gray-900">Date:</span>
                                        <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-900">Items:</span>
                                        <?php echo $order['item_count']; ?> item(s)
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-900">Total:</span>
                                        R<?php echo number_format($order['total_amount'], 2); ?>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-900">Payment:</span>
                                        <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?>
                                    </div>
                                </div>
                                
                                <div class="mt-2">
                                    <span class="font-medium text-gray-900 text-sm">Products:</span>
                                    <span class="text-sm text-gray-600">
                                        <?php 
                                        $products = $order['product_names'];
                                        if (strlen($products) > 100) {
                                            echo htmlspecialchars(substr($products, 0, 100)) . '...';
                                        } else {
                                            echo htmlspecialchars($products);
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-4 lg:mt-0 lg:ml-6 flex flex-col sm:flex-row gap-2">
                                <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)"
                                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                                
                                <?php if ($order['status'] === 'delivered'): ?>
                                    <button class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                                        Reorder
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Order Details Modal -->
    <div id="orderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 id="modalTitle" class="text-2xl font-bold text-gray-900">Order Details</h2>
                        <button onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div id="orderDetails" class="space-y-6">
                        <!-- Order details will be loaded here -->
                        <div class="animate-pulse">
                            <div class="h-4 bg-gray-200 rounded w-1/4 mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        function viewOrderDetails(orderId) {
            document.getElementById('orderModal').classList.remove('hidden');
            document.getElementById('orderDetails').innerHTML = `
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                </div>
            `;
            
            fetch(`orders.php?action=details&order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayOrderDetails(data.order, data.items);
                } else {
                    document.getElementById('orderDetails').innerHTML = `
                        <div class="text-center py-8">
                            <p class="text-red-600">${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading order details:', error);
                document.getElementById('orderDetails').innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-red-600">Error loading order details</p>
                    </div>
                `;
            });
        }

        function displayOrderDetails(order, items) {
            document.getElementById('modalTitle').textContent = `Order #${order.order_number}`;
            
            const statusColors = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'processing': 'bg-blue-100 text-blue-800',
                'shipped': 'bg-purple-100 text-purple-800',
                'delivered': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800'
            };

            const paymentStatusColors = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'paid': 'bg-green-100 text-green-800',
                'failed': 'bg-red-100 text-red-800'
            };
            
            document.getElementById('orderDetails').innerHTML = `
                <!-- Order Summary -->
                <div class="border-b pb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-3">Order Information</h3>
                            <div class="space-y-2 text-sm">
                                <div><span class="font-medium">Order Date:</span> ${new Date(order.created_at).toLocaleDateString()}</div>
                                <div><span class="font-medium">Status:</span> 
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColors[order.status] || 'bg-gray-100 text-gray-800'}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                                </div>
                                <div><span class="font-medium">Payment Status:</span> 
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${paymentStatusColors[order.payment_status] || 'bg-gray-100 text-gray-800'}">Payment ${order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}</span>
                                </div>
                                <div><span class="font-medium">Payment Method:</span> ${order.payment_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</div>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-3">Shipping Information</h3>
                            <div class="space-y-1 text-sm">
                                <div class="font-medium">${order.shipping_name}</div>
                                <div>${order.shipping_email}</div>
                                ${order.shipping_phone ? `<div>${order.shipping_phone}</div>` : ''}
                                <div>${order.shipping_address}</div>
                                <div>${order.shipping_city}, ${order.shipping_postal_code}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Order Items</h3>
                    <div class="space-y-3">
                        ${items.map(item => `
                            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                                <img src="${item.image || 'https://placehold.co/60x60'}" 
                                     alt="${item.product_name}" 
                                     class="w-15 h-15 object-cover rounded">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">${item.product_name}</h4>
                                    <p class="text-sm text-gray-600">Quantity: ${item.quantity}</p>
                                    <p class="text-sm text-gray-600">Price: R${parseFloat(item.price).toFixed(2)}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">R${parseFloat(item.total).toFixed(2)}</p>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <!-- Order Total -->
                <div class="border-t pt-4">
                    <div class="space-y-2 max-w-md ml-auto">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>R${parseFloat(order.subtotal).toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>VAT (15%):</span>
                            <span>R${parseFloat(order.tax_amount).toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Shipping:</span>
                            <span>R${parseFloat(order.shipping_amount).toFixed(2)}</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between font-semibold text-lg">
                            <span>Total:</span>
                            <span class="text-purple-600">R${parseFloat(order.total_amount).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
    </script>
</body>
</html>