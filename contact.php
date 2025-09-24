<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - SmartTech & Beauty</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .contact-animation {
            animation: fadeInUp 0.6s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
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
                        <span class="text-gray-600 ml-1 md:ml-2 font-medium">Contact</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header Section -->
        <div class="text-center mb-12 contact-animation">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Contact Us</h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                We're here to help! Get in touch with our customer service team.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 contact-animation">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Send us a Message</h2>
                
                <form id="contactForm" class="space-y-6">
                    <div id="messageDiv" class="hidden p-4 rounded-lg"></div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="firstName" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" id="firstName" name="firstName" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                        </div>
                        <div>
                            <label for="lastName" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" id="lastName" name="lastName" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <select id="subject" name="subject" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                            <option value="">Select a subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="order">Order Support</option>
                            <option value="technical">Technical Support</option>
                            <option value="billing">Billing Question</option>
                            <option value="return">Returns & Refunds</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea id="message" name="message" rows="6" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                  placeholder="Please describe your inquiry in detail..."></textarea>
                    </div>

                    <button type="submit" id="submitBtn"
                            class="w-full bg-gradient-to-r from-purple-500 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-purple-600 hover:to-blue-700 font-medium transition-all duration-200 shadow-md hover:shadow-lg">
                        <span id="submitText">Send Message</span>
                        <svg id="submitLoader" class="animate-spin -mr-1 ml-3 h-5 w-5 text-white hidden inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Contact Information -->
            <div class="space-y-8 contact-animation">
                <!-- Store Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Get in Touch</h2>
                    
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">Visit Our Store</h3>
                                <p class="text-gray-600">27 Gately<br>East London, Eastern Cape 5201<br>South Africa</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">Call Us</h3>
                                <p class="text-gray-600">
                                    <a href="tel:+27123456789" class="hover:text-purple-600 transition-colors">+27 78 453 4532</a><br>
                                    <span class="text-sm">Mon-Fri: 9AM-6PM</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">Email Us</h3>
                                <p class="text-gray-600">
                                    <a href="mailto:info@smarttechbeauty.co.za" class="hover:text-purple-600 transition-colors">smart@techbeauty.co.za</a><br>
                                    <span class="text-sm">We respond within 24 hours</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Hours -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Business Hours</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Monday - Friday</span>
                            <span class="font-medium text-gray-800">9:00 AM - 6:00 PM</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Saturday</span>
                            <span class="font-medium text-gray-800">9:00 AM - 4:00 PM</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600">Sunday</span>
                            <span class="font-medium text-gray-800">Closed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">SmartTech & Beauty</h3>
                    <p class="text-gray-400 mb-4">Your one-stop shop for the latest tech and finest beauty products.</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="About.html" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="products.php" class="text-gray-400 hover:text-white transition-colors">Products</a></li>
                        <li><a href="faq.html" class="text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Contact Info</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li>📧 smart@techbeauty.co.za</li>
                        <li>📞 +27 78 453 4532</li>
                        <li>📍 27 Gately, East London, South Africa</li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-700 pt-6 text-center text-gray-400">
                <p>© 2025 SmartTech & Beauty Store. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const messageDiv = document.getElementById('messageDiv');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoader = document.getElementById('submitLoader');
            
            // Get form data
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.textContent = 'Sending...';
            submitLoader.classList.remove('hidden');
            messageDiv.classList.add('hidden');
            
            // Simulate form submission (replace with actual endpoint)
            setTimeout(() => {
                // Show success message
                messageDiv.innerHTML = '<p class="text-green-700">Thank you for your message! We\'ll get back to you within 24 hours.</p>';
                messageDiv.className = 'p-4 rounded-lg bg-green-50 border border-green-200';
                messageDiv.classList.remove('hidden');
                
                // Reset form
                this.reset();
                
                // Reset button state
                submitBtn.disabled = false;
                submitText.textContent = 'Send Message';
                submitLoader.classList.add('hidden');
                
                // Scroll to message
                messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 1500);
        });
    </script>
</body>
</html>