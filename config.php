<?php
if (!defined('INIT_INCLUDED') && basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    // Redirect to homepage or display error
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'acore');
define('DB_PASS', 'acore');
define('DB_NAME', 'acore_auth');

// Application configuration
define('APP_NAME', 'Frozen Shop');
define('APP_URL', 'http://localhost/frozen_shop');
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('CSRF_TOKEN_SECRET', bin2hex(random_bytes(32))); // Generate a random token for CSRF protection

// Security settings
define('PASSWORD_COST', 12); // Higher is more secure but slower

// Session settings (these will be applied in init.php before session_start)
define('SESSION_COOKIE_HTTPONLY', 1); // Prevent JavaScript access to session cookie
define('SESSION_USE_ONLY_COOKIES', 1); // Force sessions to only use cookies
define('SESSION_COOKIE_SECURE', 0); // Set to 1 if using HTTPS
define('SESSION_COOKIE_SAMESITE', 'Strict'); // Prevent CSRF attacks
define('SESSION_GC_MAXLIFETIME', SESSION_LIFETIME); // Set session garbage collection lifetime

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>