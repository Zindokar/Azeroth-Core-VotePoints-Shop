<?php
// Prevent direct access to this file
if (!defined('INIT_INCLUDED') && basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    // Redirect to homepage or display error
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Include configuration
require_once __DIR__ . '/../config.php';

// Configure session before starting it
ini_set('session.cookie_httponly', SESSION_COOKIE_HTTPONLY);
ini_set('session.use_only_cookies', SESSION_USE_ONLY_COOKIES);
ini_set('session.cookie_secure', SESSION_COOKIE_SECURE);
ini_set('session.cookie_samesite', SESSION_COOKIE_SAMESITE);
ini_set('session.gc_maxlifetime', SESSION_GC_MAXLIFETIME);

// Start the session
session_start();

// Load classes
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/AuthMiddleware.php';

// Initialize database
$database = new Database();

// Initialize authentication
$auth = new Auth($database);

// Initialize middleware
$authMiddleware = new AuthMiddleware($auth);
?>