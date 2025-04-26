<?php
class Auth {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // this already exist: https://github.com/azerothcore/acore-cms-wp-plugin/blob/main/src/Manager/Auth/Repository/AccountRepository.php
    private function CalculateSRP6Verifier($username, $password, $salt) 
    {
        // algorithm constants
        $g = gmp_init(7);
        $N = gmp_init('894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7', 16);

        // calculate first hash
        $h1 = sha1(strtoupper($username . ':' . $password), TRUE);

        // calculate second hash
        $h2 = sha1($salt.$h1, TRUE);

        // convert to integer (little-endian)
        $h2 = gmp_import($h2, 1, GMP_LSW_FIRST);

        // g^h2 mod N
        $verifier = gmp_powm($g, $h2, $N);

        // convert back to a byte array (little-endian)
        $verifier = gmp_export($verifier, 1, GMP_LSW_FIRST);

        // pad to 32 bytes, remember that zeros go on the end in little-endian!
        $verifier = str_pad($verifier, 32, chr(0), STR_PAD_RIGHT);

        return $verifier;
    }

    public function verifyAccount($username, $password) {
        $stmt = $this->db->prepare("SELECT salt FROM account WHERE username = ?");
        $stmt->execute([$username]);  // Fixed: Only pass one parameter
        $salt = $stmt->fetch();

        if ($salt === false || empty($salt['salt'])) {
            return false;
        }

        $salt = $salt['salt'];
        $verifier = $this->CalculateSRP6Verifier($username, $password, $salt);
        $stmt = $this->db->prepare("SELECT id, username FROM account WHERE username = ? AND verifier = ?");
        $stmt->execute([$username, $verifier]);
        $user = $stmt->fetch();

        if ($user === false || empty($user['id'])) {
            return false;
        }

        return $user;
    }
    
    public function login($username, $password, $remember = false) {
        // Validate input
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }
        
        try {

            $user = $this->verifyAccount($username, $password);

            if ($user === false) {
                return ['success' => false, 'message' => 'Login failed: Incorrect login data provided.'];
            }
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            $_SESSION['ip_address'] = Security::getIP(); // Store the IP in session
            
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Store session in database for better security
            $this->storeSession($user['id']);
            
            return ['success' => true, 'message' => 'Login successful', 'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    private function storeSession($userId) {
        $sessionId = session_id();
        $ipAddress = Security::getIP(); // Using the new getIP method
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        // Determine IP version and set appropriate field
        $ipv4Address = null;
        $ipv6Address = null;
        
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipv4Address = $ipAddress;
        } elseif (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipv6Address = $ipAddress;
        }
        
        // Delete any existing sessions for this session ID
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        
        // Store new session
        $stmt = $this->db->prepare("INSERT INTO user_sessions (user_id, session_id, ipv4_address, ipv6_address, user_agent, expires_at) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $sessionId, $ipv4Address, $ipv6Address, $userAgent, $expiresAt]);
    }
    
    public function logout() {
        // Delete session from database
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE session_id = ?");
            $stmt->execute([session_id()]);
        }
        
        // Unset all session variables
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        return ['success' => true, 'message' => 'Logout successful'];
    }
    
    public function isLoggedIn() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Check if session has expired
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            $this->logout();
            return false;
        }
        
        // Verify session in database
        $stmt = $this->db->prepare("SELECT * FROM user_sessions WHERE user_id = ? AND session_id = ? AND expires_at > NOW()");
        $stmt->execute([$_SESSION['user_id'], session_id()]);
        
        if ($stmt->rowCount() === 0) {
            $this->logout();
            return false;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email']
        ];
    }
    
    public function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
}
?>