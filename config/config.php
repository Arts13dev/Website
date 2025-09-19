<?php
/**
 * Main Configuration File for SmartTech & Beauty Store
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Africa/Johannesburg');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Application settings
define('SITE_NAME', 'SmartTech & Beauty Store');
define('SITE_URL', 'http://localhost/Website'); // XAMPP environment
define('UPLOADS_DIR', 'uploads/');
define('PRODUCTS_IMAGES_DIR', 'uploads/products/');

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Create uploads directories if they don't exist
if (!file_exists(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}
if (!file_exists(PRODUCTS_IMAGES_DIR)) {
    mkdir(PRODUCTS_IMAGES_DIR, 0755, true);
}

// Helper functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generate_slug($string) {
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}

function format_price($price) {
    return 'R ' . number_format($price, 2);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_admin() {
    if (!is_admin()) {
        redirect('login.php');
    }
}

function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching current user: " . $e->getMessage());
        return null;
    }
}

// Check session timeout
function check_session_timeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        redirect('login.php');
    }
    $_SESSION['last_activity'] = time();
}

// CSRF protection
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Auto-load classes
spl_autoload_register(function ($class_name) {
    $directories = [
        __DIR__ . '/../classes/',
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// Check session timeout on every request
if (is_logged_in()) {
    check_session_timeout();
}
?>