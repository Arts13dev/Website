<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link
      rel="stylesheet"
      as="style"
      onload="this.rel='stylesheet'"
      href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Work+Sans%3Awght%40400%3B500%3B700%3B900"
    />

    <title>About Us | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64," />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Learn about <?php echo SITE_NAME; ?> - Your trusted partner for tech and beauty products." />
</head>
<body class="bg-white" style='font-family: "Work Sans", "Noto Sans", sans-serif;'>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <main class="px-40 flex flex-1 justify-center py-5">
        <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
            <div class="flex flex-col gap-8 p-4 md:p-8 lg:p-12">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-[#181111]">About <?php echo SITE_NAME; ?></h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-4">
                        <h2 class="text-xl md:text-2xl font-bold text-[#181111]">Our Story</h2>
                        <p class="text-base text-[#181111] leading-relaxed">
                            Founded in 2024, <?php echo SITE_NAME; ?> was born from a simple idea: to create a single destination where the worlds of cutting-edge technology and premium beauty products could meet. We believe that modern life is a blend of digital innovation and personal well-being, and our store is a reflection of that philosophy.
                        </p>
                        <p class="text-base text-[#181111] leading-relaxed">
                            We started with a small selection of curated smartphones and laptops, quickly realizing there was a demand for high-quality, ethically sourced hair products. Since then, we've grown our collection to include a diverse range of weaves, catering to different styles and preferences.
                        </p>
                    </div>
                    <div class="w-full bg-center bg-no-repeat aspect-video bg-cover rounded-lg" style="background-image: url('https://images.unsplash.com/photo-1542831371-29b0f74f9713?auto=format&fit=crop&w=800&q=80');"></div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                    <div class="w-full bg-center bg-no-repeat aspect-video bg-cover rounded-lg md:order-2" style="background-image: url('https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="flex flex-col gap-4 md:order-1">
                        <h2 class="text-xl md:text-2xl font-bold text-[#181111]">Our Mission</h2>
                        <p class="text-base text-[#181111] leading-relaxed">
                            Our mission is to provide an unbeatable selection of products, all while ensuring an exceptional shopping experience. We are committed to transparency, quality, and customer satisfaction. We carefully vet every product in our catalog to ensure it meets our high standards.
                        </p>
                        <ul class="list-disc pl-5 text-base text-[#181111] leading-relaxed">
                            <li><strong>Quality First:</strong> We only partner with trusted suppliers and brands.</li>
                            <li><strong>Customer-Centric:</strong> Your happiness is our top priority.</li>
                            <li><strong>Innovation & Style:</strong> Stay ahead with the latest in technology and beauty trends.</li>
                        </ul>
                    </div>
                </div>

                <!-- Our Values Section -->
                <div class="mt-12">
                    <h2 class="text-2xl md:text-3xl font-bold text-center text-[#181111] mb-8">Our Core Values</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="text-center">
                            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Quality Assurance</h3>
                            <p class="text-gray-600">Every product is carefully tested and verified to meet our high standards.</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Fast Delivery</h3>
                            <p class="text-gray-600">Quick and reliable delivery to get your products to you as soon as possible.</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Customer Love</h3>
                            <p class="text-gray-600">We prioritize customer satisfaction and build lasting relationships.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact CTA Section -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-8 rounded-lg text-center mt-12">
                    <h2 class="text-2xl font-bold mb-4">Ready to Experience the Difference?</h2>
                    <p class="mb-6">Join thousands of satisfied customers who trust us for their tech and beauty needs.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="products.php" class="bg-white text-blue-600 px-6 py-3 rounded-lg hover:bg-gray-100 transition-colors font-semibold">
                            Shop Now
                        </a>
                        <a href="contact.php" class="border border-white text-white px-6 py-3 rounded-lg hover:bg-white hover:text-blue-600 transition-colors font-semibold">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>