<?php
require_once 'config/config.php';

// Require admin access
require_admin();

// Get admin user info
$currentUser = get_logged_in_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Active sidebar link styling */
        .sidebar-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateX(4px);
        }
        /* Content panel visibility */
        .content-panel {
            display: none;
        }
        .content-panel.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        /* Loading spinner */
        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Image preview */
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 flex min-h-screen">

    <!-- Sidebar Navigation -->
    <aside class="w-72 bg-white shadow-2xl min-h-screen border-r border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Admin Panel</h1>
                    <p class="text-sm text-gray-500"><?php echo SITE_NAME; ?></p>
                </div>
            </div>
        </div>
        
        <nav class="p-4 space-y-2">
            <a href="#" class="sidebar-link flex items-center space-x-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-gray-50 active" data-target="dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="#" class="sidebar-link flex items-center space-x-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-gray-50" data-target="products">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <span class="font-medium">Products</span>
            </a>
            <a href="#" class="sidebar-link flex items-center space-x-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-gray-50" data-target="orders">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span class="font-medium">Orders</span>
            </a>
            <a href="#" class="sidebar-link flex items-center space-x-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-gray-50" data-target="customers">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <span class="font-medium">Customers</span>
            </a>
            <a href="#" class="sidebar-link flex items-center space-x-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-gray-50" data-target="settings">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="font-medium">Settings</span>
            </a>
        </nav>
        
        <div class="absolute bottom-4 left-4 right-4">
            <div class="bg-gradient-to-r from-purple-500 to-blue-600 rounded-lg p-4 text-white">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-sm font-medium"><?php echo htmlspecialchars($currentUser['fullName']); ?></span>
                </div>
                <div class="mt-2 flex space-x-2">
                    <a href="home.php" class="text-xs opacity-75 hover:opacity-100 transition-opacity">View Site</a>
                    <span class="text-xs opacity-50">|</span>
                    <a href="logout.php" class="text-xs opacity-75 hover:opacity-100 transition-opacity">Logout</a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-y-auto">
        <!-- Dashboard Panel -->
        <div id="dashboard" class="content-panel active">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Dashboard Overview</h2>
                <p class="text-gray-600">Welcome back! Here's what's happening with your store.</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-lg bg-blue-50">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Products</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-products">Loading...</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-lg bg-green-50">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Orders</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-orders">Loading...</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-lg bg-purple-50">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Customers</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-customers">Loading...</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-lg bg-orange-50">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-revenue">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Panel -->
        <div id="products" class="content-panel">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Product Management</h2>
                <p class="text-gray-600">Add, edit, and manage your product inventory.</p>
            </div>
            
            <!-- Add Product Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 mb-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Add New Product</h3>
                <form id="addProductForm" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                        </div>
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price (R) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                            <textarea id="short_description" name="short_description" rows="2"
                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                      placeholder="Brief product description (max 500 characters)"></textarea>
                        </div>
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity *</label>
                            <input type="number" id="stock" name="stock" min="0" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select id="category" name="category_id" required
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                            <input type="text" id="brand" name="brand"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                        </div>
                        <div class="flex items-center">
                            <label for="featured" class="flex items-center cursor-pointer">
                                <input type="checkbox" id="featured" name="featured" value="1"
                                       class="w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Featured Product</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Product Image Upload -->
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Product Image *</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-purple-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                        <span>Upload a file</span>
                                        <input id="image" name="image" type="file" accept="image/*" required class="sr-only">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                            </div>
                        </div>
                        <div id="image-preview" class="mt-4 hidden">
                            <img id="preview-img" class="image-preview mx-auto" alt="Preview">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-gradient-to-r from-purple-500 to-blue-600 text-white px-8 py-3 rounded-lg font-medium hover:from-purple-600 hover:to-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                            Add Product
                        </button>
                    </div>
                </form>
                <div id="form-message" class="mt-4 text-center"></div>
            </div>
            
            <!-- Existing Products -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">Existing Products</h3>
                    <button onclick="loadProducts()" class="text-purple-600 hover:text-purple-700 font-medium">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="products-table-body" class="bg-white divide-y divide-gray-200">
                            <!-- Product rows will be loaded here -->
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <div class="loader mx-auto"></div>
                                    <p class="mt-2">Loading products...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Other panels (Orders, Customers, Settings) placeholder -->
        <div id="orders" class="content-panel">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Order Management</h2>
            <p class="text-gray-600">Order management functionality will be implemented here.</p>
        </div>

        <div id="customers" class="content-panel">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Customer Management</h2>
            <p class="text-gray-600">Customer management functionality will be implemented here.</p>
        </div>

        <div id="settings" class="content-panel">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Settings</h2>
            <p class="text-gray-600">Settings functionality will be implemented here.</p>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('.sidebar-link');
            const panels = document.querySelectorAll('.content-panel');

            // Navigation Logic
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all links and panels
                    links.forEach(l => l.classList.remove('active'));
                    panels.forEach(p => p.classList.remove('active'));

                    // Add active class to clicked link and corresponding panel
                    const targetId = this.dataset.target;
                    this.classList.add('active');
                    document.getElementById(targetId).classList.add('active');

                    // Load content based on panel
                    switch(targetId) {
                        case 'dashboard':
                            loadDashboardStats();
                            break;
                        case 'products':
                            loadProducts();
                            loadCategories();
                            break;
                    }
                });
            });

            // Image preview functionality
            const imageInput = document.getElementById('image');
            const imagePreview = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');

            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Product form submission
            const addProductForm = document.getElementById('addProductForm');
            const formMessage = document.getElementById('form-message');

            addProductForm.addEventListener('submit', function(e) {
                e.preventDefault();
                formMessage.innerHTML = '<div class="flex items-center justify-center"><div class="loader mr-2"></div>Uploading product...</div>';
                formMessage.className = 'mt-4 text-center text-blue-600';

                const formData = new FormData(this);
                
                fetch('api/upload_product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        formMessage.innerHTML = `<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">${data.message}</div>`;
                        formMessage.className = 'mt-4 text-center';
                        addProductForm.reset();
                        imagePreview.classList.add('hidden');
                        loadProducts();
                        loadDashboardStats();
                    } else {
                        formMessage.innerHTML = `<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">Error: ${data.message}</div>`;
                        formMessage.className = 'mt-4 text-center';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    formMessage.innerHTML = `<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">An unexpected error occurred. Please try again.</div>`;
                    formMessage.className = 'mt-4 text-center';
                });
            });

            // Initial load
            loadDashboardStats();
            loadCategories();
        });

        // Dashboard functions
        function loadDashboardStats() {
            // Load total counts
            Promise.all([
                fetch('api/get_stats.php?type=products'),
                fetch('api/get_stats.php?type=orders'),
                fetch('api/get_stats.php?type=customers'),
                fetch('api/get_stats.php?type=revenue')
            ]).then(responses => Promise.all(responses.map(r => r.json())))
            .then(data => {
                if (data[0].success) document.getElementById('total-products').textContent = data[0].count;
                if (data[1].success) document.getElementById('total-orders').textContent = data[1].count;
                if (data[2].success) document.getElementById('total-customers').textContent = data[2].count;
                if (data[3].success) document.getElementById('total-revenue').textContent = `R${data[3].total}`;
            })
            .catch(error => {
                console.error('Error loading dashboard stats:', error);
                document.getElementById('total-products').textContent = '0';
                document.getElementById('total-orders').textContent = '0';
                document.getElementById('total-customers').textContent = '0';
                document.getElementById('total-revenue').textContent = 'R0.00';
            });
        }

        function loadProducts() {
            const tableBody = document.getElementById('products-table-body');
            fetch('api/admin_products.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.products.length > 0) {
                    tableBody.innerHTML = data.products.map(product => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img src="${product.image || 'https://placehold.co/60x60/e8b4b7/333333?text=No+Image'}" alt="${product.name}" class="h-16 w-16 object-cover rounded-lg">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">${product.name}</div>
                                <div class="text-sm text-gray-500">${product.category_name || 'No category'}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">R${parseFloat(product.price).toFixed(2)}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${product.stock < 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                                    ${product.stock}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${product.featured == 1 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'}">
                                    ${product.featured == 1 ? 'Yes' : 'No'}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="editProduct(${product.id})" class="text-purple-600 hover:text-purple-900">Edit</button>
                                <button onclick="deleteProduct(${product.id})" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No products found.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
                tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error loading products.</td></tr>';
            });
        }

        function loadCategories() {
            fetch('api/get_categories.php')
            .then(response => response.json())
            .then(data => {
                const categorySelect = document.getElementById('category');
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                
                if (data.success && data.categories.length > 0) {
                    data.categories.forEach(category => {
                        categorySelect.innerHTML += `<option value="${category.id}">${category.name}</option>`;
                    });
                }
            })
            .catch(error => {
                console.error('Error loading categories:', error);
            });
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch('api/delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadProducts();
                        loadDashboardStats();
                    } else {
                        alert('Failed to delete product: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the product.');
                });
            }
        }

        function editProduct(productId) {
            // Edit functionality will be implemented
            alert('Edit functionality will be implemented soon!');
        }
    </script>
</body>
</html>