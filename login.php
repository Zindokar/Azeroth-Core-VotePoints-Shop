<?php
// Define a constant to prevent multiple inclusions of init.php
define('INIT_INCLUDED', true);

// Include init.php directly here since it's the first file
require_once 'includes/init.php';

// Redirect if already logged in
$authMiddleware->requireGuest();

$errors = [];
$success = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !$auth->validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'CSRF token validation failed';
    } else {
        // Sanitize input
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? ''; // Don't sanitize password
        $remember = isset($_POST['remember']) && $_POST['remember'] === '1';
        
        // Attempt login
        $result = $auth->login($username, $password, $remember);
        
        if ($result['success']) {
            // Redirect to intended page or home
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Generate CSRF token
$csrfToken = $auth->generateCsrfToken();

// Set page title
$pageTitle = 'Login';

// Include header - but we need to modify it for login page
// since we don't want to show user-specific navigation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= Security::preventXSS($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= Security::preventXSS($success) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username or Email</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>