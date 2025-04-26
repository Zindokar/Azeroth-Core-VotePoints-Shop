<?php
// Prevent direct access to this file
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    // Redirect to homepage or display error
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Ensure init.php is included only once
if (!defined('INIT_INCLUDED')) {
    require_once __DIR__ . '/init.php';
}

// Check if title is set, otherwise use default
$pageTitle = isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME;

// Get current user if logged in
$user = $auth->isLoggedIn() ? $auth->getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/wow-icon-32x32.png" type="image/x-icon">
    <link rel="shortcut icon" href="assets/images/wow-icon-32x32.png" type="image/x-icon">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?= APP_NAME ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="checkout.php">Cart</a>
                    </li>
                    <?php if ($user): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?= Security::preventXSS($user['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="history.php">Purchases</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>