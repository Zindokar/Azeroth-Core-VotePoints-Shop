<?php
class AuthMiddleware {
    private $auth;
    private $redirectUrl;
    
    public function __construct(Auth $auth, $redirectUrl = 'login.php') {
        $this->auth = $auth;
        $this->redirectUrl = $redirectUrl;
    }
    
    public function requireAuth() {
        if (!$this->auth->isLoggedIn()) {
            header('Location: ' . $this->redirectUrl . '?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
    
    public function requireGuest() {
        if ($this->auth->isLoggedIn()) {
            header('Location: index.php');
            exit;
        }
    }
    
    public function requireCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !$this->auth->validateCsrfToken($_POST['csrf_token'])) {
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }
    }
}
?>