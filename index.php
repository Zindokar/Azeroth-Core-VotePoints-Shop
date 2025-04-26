<?php
// Define a constant to prevent multiple inclusions of init.php
define('INIT_INCLUDED', true);

// Include init.php directly here since it's the first file
require_once 'includes/init.php';

// Require authentication
$authMiddleware->requireAuth();

// Get current user
$user = $auth->getCurrentUser();

// Set page title
$pageTitle = 'Shop';

// Include header
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Welcome, <?= Security::preventXSS($user['username']) ?>!</h3>
                </div>
                <div class="card-body">
                    <p>You can buy World of Warcraft items with Vote Points.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>