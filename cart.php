<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .cart-animation {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="home.php" class="text-gray-500 hover:text-purple-600 inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-600 ml-1 md:ml-2 font-medium">Shopping Cart</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Cart Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Shopping Cart</h2>
                        <p class="text-sm text-gray-600 mt-1">Review your items before checkout</p>
                    </div>
                    
                    <!-- Cart Items Container -->
                    <div id="cartItemsContainer" class="divide-y divide-gray-200">
                        <!-- Cart items will be loaded here -->
                        <div class="p-8 text-center">
                            <div class="animate-pulse">
                                <div class="w-16 h-16 bg-gray-200 rounded-full mx-auto mb-4"></div>
                                <div class="h-4 bg-gray-200 rounded w-32 mx-auto mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded w-24 mx-auto"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommended Products -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">You might also like</h3>
                    </div>
                    <div id="recommendedProducts" class="p-6">
                        <!-- Recommended products will be loaded here -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="animate-pulse">
                                <div class="bg-gray-200 h-48 rounded-lg mb-3"></div>
                                <div class="h-4 bg-gray-200 rounded mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded w-20"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-24">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Order Summary</h3>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <!-- Subtotal -->
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Subtotal</span>
                            <span id="subtotal" class="font-medium">R0.00</span>
                        </div>
                        
                        <!-- Shipping -->
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium text-green-600">Free</span>
                        </div>
                        
                        <!-- VAT -->
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">VAT (15%)</span>
                            <span id="vat" class="font-medium">R0.00</span>
                        </div>
                        
                        <hr class="border-gray-200">
                        
                        <!-- Total -->
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold">Total</span>
                            <span id="total" class="text-lg font-bold text-purple-600">R0.00</span>
                        </div>
                        
                        <!-- Discount Code -->
                        <div class="pt-4">
                            <div class="flex space-x-2">
                                <input type="text" id="discountCode" placeholder="Discount code" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <button onclick="applyDiscount()" 
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                                    Apply
                                </button>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="pt-6 space-y-3">
                            <?php if (is_logged_in()): ?>
                                <button onclick="proceedToCheckout()" id="checkoutBtn"
                                        class="w-full py-3 px-4 bg-gradient-to-r from-purple-500 to-blue-600 text-white rounded-lg hover:from-purple-600 hover:to-blue-700 font-medium transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                    Proceed to Checkout
                                </button>
                            <?php else: ?>
                                <a href="login.php"
                                   class="w-full py-3 px-4 bg-gradient-to-r from-purple-500 to-blue-600 text-white rounded-lg hover:from-purple-600 hover:to-blue-700 font-medium transition-all duration-200 shadow-md hover:shadow-lg text-center block">
                                    Login to Checkout
                                </a>
                            <?php endif; ?>
                            
                            <button onclick="continueShopping()"
                                    class="w-full py-3 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                                Continue Shopping
                            </button>
                        </div>
                        
                        <!-- Trust Badges -->
                        <div class="pt-6 border-t border-gray-200">
                            <div class="flex items-center justify-center space-x-4 text-xs text-gray-500">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Secure Checkout
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    Safe Payment
                                </div>
                            </div>
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
            loadCartItems();
            loadRecommendedProducts();
        });

        function loadCartItems() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const container = document.getElementById('cartItemsContainer');
            
            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="p-8 text-center cart-animation">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Your cart is empty</h3>
                        <p class="text-gray-600 mb-6">Looks like you haven't added any items to your cart yet.</p>
                        <button onclick="continueShopping()" class="px-6 py-3 bg-gradient-to-r from-purple-500 to-blue-600 text-white rounded-lg hover:from-purple-600 hover:to-blue-700 font-medium transition-all duration-200">
                            Start Shopping
                        </button>
                    </div>
                `;
                updateOrderSummary();
                return;
            }

            container.innerHTML = cart.map((item, index) => `
                <div class="p-6 cart-animation">
                    <div class="flex items-center space-x-4">
                        <!-- Product Image -->
                        <div class="flex-shrink-0">
                            <img src="${item.image || 'https://placehold.co/100x100'}" 
                                 alt="${item.name}" 
                                 class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                        </div>
                        
                        <!-- Product Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-medium text-gray-800 truncate">${item.name}</h3>
                            <p class="text-sm text-gray-600 mt-1">Price: R${item.price.toFixed(2)}</p>
                            
                            <!-- Quantity Controls -->
                            <div class="flex items-center mt-3 space-x-3">
                                <div class="flex items-center border border-gray-300 rounded-lg">
                                    <button onclick="updateQuantity(${index}, -1)" 
                                            class="p-2 hover:bg-gray-100 rounded-l-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <span class="px-4 py-2 font-medium">${item.quantity}</span>
                                    <button onclick="updateQuantity(${index}, 1)" 
                                            class="p-2 hover:bg-gray-100 rounded-r-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <button onclick="removeItem(${index})" 
                                        class="text-red-600 hover:text-red-800 font-medium text-sm transition-colors">
                                    Remove
                                </button>
                            </div>
                        </div>
                        
                        <!-- Item Total -->
                        <div class="text-right">
                            <p class="text-lg font-semibold text-gray-800">R${(item.price * item.quantity).toFixed(2)}</p>
                        </div>
                    </div>
                </div>
            `).join('');

            updateOrderSummary();
        }

        function updateQuantity(index, change) {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            cart[index].quantity += change;
            
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCartItems();
            updateCartCount(); // Update header cart count
        }

        function removeItem(index) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                cart.splice(index, 1);
                localStorage.setItem('cart', JSON.stringify(cart));
                loadCartItems();
                updateCartCount(); // Update header cart count
            }
        }

        function updateOrderSummary() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const vat = subtotal * 0.15; // 15% VAT
            const total = subtotal + vat;
            
            document.getElementById('subtotal').textContent = `R${subtotal.toFixed(2)}`;
            document.getElementById('vat').textContent = `R${vat.toFixed(2)}`;
            document.getElementById('total').textContent = `R${total.toFixed(2)}`;
            
            // Disable checkout button if cart is empty
            const checkoutBtn = document.getElementById('checkoutBtn');
            if (checkoutBtn) {
                checkoutBtn.disabled = cart.length === 0;
            }
        }

        function applyDiscount() {
            const code = document.getElementById('discountCode').value.trim();
            if (!code) {
                alert('Please enter a discount code');
                return;
            }
            
            // Mock discount validation
            const validCodes = {
                'SAVE10': 0.10,
                'WELCOME15': 0.15,
                'STUDENT20': 0.20
            };
            
            if (validCodes[code.toUpperCase()]) {
                const discount = validCodes[code.toUpperCase()];
                alert(`Discount code applied! You saved ${(discount * 100)}%`);
                // TODO: Apply actual discount logic
            } else {
                alert('Invalid discount code');
            }
        }

        function loadRecommendedProducts() {
            // Load featured/recommended products
            fetch('products.php?api=1&limit=3')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('recommendedProducts');
                if (data.success && data.products.length > 0) {
                    // Get random 3 products for recommendations
                    const recommended = data.products.sort(() => 0.5 - Math.random()).slice(0, 3);
                    
                    container.innerHTML = `
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            ${recommended.map(product => `
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <a href="products.php?id=${product.id}">
                                        <img src="${product.image || 'https://placehold.co/200x200'}" 
                                             alt="${product.name}" 
                                             class="w-full h-32 object-cover rounded-lg mb-3">
                                        <h4 class="font-semibold text-gray-800 mb-2">${product.name}</h4>
                                        <p class="text-purple-600 font-bold">R${parseFloat(product.price).toFixed(2)}</p>
                                    </a>
                                    <button class="add-to-cart-btn w-full mt-2 bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors"
                                            data-product-id="${product.id}"
                                            data-product-name="${product.name}"
                                            data-product-price="${product.price}"
                                            data-product-image="${product.image || ''}">
                                        Add to Cart
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    `;

                    // Attach event listeners to new add to cart buttons
                    attachAddToCartListeners();
                } else {
                    container.innerHTML = '<p class="text-gray-500 text-center">No recommendations available.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading recommended products:', error);
            });
        }

        function attachAddToCartListeners() {
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                if (!button.classList.contains('listener-attached')) {
                    button.addEventListener('click', function() {
                        const product = {
                            id: this.dataset.productId,
                            name: this.dataset.productName,
                            price: parseFloat(this.dataset.productPrice),
                            image: this.dataset.productImage
                        };

                        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
                        const existingProductIndex = cart.findIndex(item => item.id === product.id);

                        if (existingProductIndex > -1) {
                            cart[existingProductIndex].quantity++;
                        } else {
                            product.quantity = 1;
                            cart.push(product);
                        }

                        localStorage.setItem('cart', JSON.stringify(cart));
                        updateCartCount();
                        loadCartItems(); // Reload cart items
                        
                        this.textContent = 'Added!';
                        this.classList.add('bg-green-600');
                        this.classList.remove('bg-purple-600');
                        
                        setTimeout(() => {
                            this.textContent = 'Add to Cart';
                            this.classList.remove('bg-green-600');
                            this.classList.add('bg-purple-600');
                        }, 2000);
                    });
                    button.classList.add('listener-attached');
                }
            });
        }

        function continueShopping() {
            window.location.href = 'products.php';
        }

        function proceedToCheckout() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            // TODO: Implement checkout process
            alert('Checkout functionality will be implemented soon!');
        }
    </script>
</body>
</html>