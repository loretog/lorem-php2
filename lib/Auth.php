<?php

require_once __DIR__ . '/../permissions.php';

class Auth
{
    private static $instance = null;

    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }



    public function login($username, $password)
    {
        $user = Database::get()->prepare("SELECT * FROM users WHERE username = ?");
        $user->execute([$username]);
        $user = $user->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = !empty($user['role']) ? $user['role'] : 'logged_in';
            session_regenerate_id(true);
            error_log('Login successful - User ID: ' . $user['id'] . ' Role: ' . $user['role']);
            error_log('Session data after login: ' . print_r($_SESSION, true));
            return true;
        }
        return false;
    }

    public function logout()
    {
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params["path"],
                    'domain' => $params["domain"],
                    'secure' => $params["secure"],
                    'httponly' => $params["httponly"],
                    'samesite' => 'Lax'
                ]
            );
        }
        
        session_destroy();
    }

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        error_log('Session validation check - User Agent Match: ' . ($_SESSION['user_agent'] === ($_SERVER['HTTP_USER_AGENT'] ?? '') ? 'true' : 'false'));
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
        $this->checkSessionValidity();
    }

    private function checkSessionValidity()
    {
        $maxLifetime = 1800; // 30 minutes
        
        if ($this->isLoggedIn() && isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $maxLifetime) {
                $this->logout();
                if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== parse_url(SITE_URL.'/login', PHP_URL_PATH)) {
                    header('Location: ' . SITE_URL . '/login');
                    exit();
                }
            }
        }
        $_SESSION['last_activity'] = time();

        if ($this->isLoggedIn() && !$this->isSessionValid()) {
            $this->logout();
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $basePath = parse_url(SITE_URL, PHP_URL_PATH);
            $normalizedPath = preg_replace('#^'.preg_quote($basePath, '#').'#', '', $currentPath) ?: '/';

            if ($normalizedPath !== '/login') {
                header('Location: ' . SITE_URL . '/login');
                exit();
            }
        }
    }

    private function isSessionValid(): bool
    {
        return $_SESSION['user_agent'] === ($_SERVER['HTTP_USER_AGENT'] ?? '');
    }

    public function checkAccess($allowedRoles = ['logged_in'])
    {
        if (DEBUG) {
            error_log('Checking access for role: ' . $this->getUserRole());
            error_log('Allowed roles: ' . print_r($allowedRoles, true));
            error_log('Session status: ' . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'));
            error_log('Current URI: ' . ($_SERVER['REQUEST_URI'] ?? ''));
        }
    
    if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        // Allow wildcard access without authentication
        if (in_array('*', $allowedRoles)) {
            return;
        }

        $userRole = $this->getUserRole();
        
        global $VALID_ROLES;
        if (!in_array($userRole, $VALID_ROLES)) {
            if (DEBUG) {
                error_log("Invalid role detected: $userRole");
            }
            $this->logout();
            header('Location: ' . SITE_URL . '/login');
            exit();
        }

        if (!$this->isLoggedIn() || !$this->isRoleAllowed($userRole, $allowedRoles)) {
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $basePath = parse_url(SITE_URL, PHP_URL_PATH);
            $normalizedPath = preg_replace('#^'.preg_quote($basePath, '#').'#', '', $currentPath) ?: '/';

            if ($normalizedPath !== '/login') {
                header('Location: ' . SITE_URL . '/login');
                exit();
            }
        }
    }

    private function isRoleAllowed($userRole, $allowedRoles)
    {
        if (DEBUG) {
            error_log('Checking role hierarchy for: ' . $userRole);
        }
        global $ROLE_HIERARCHY;

        $effectiveRoles = $this->getEffectiveRoles($userRole);
        foreach ($effectiveRoles as $role) {
            if (in_array(strtolower($role), array_map('strtolower', $allowedRoles))) {
                return true;
            }
        }
        return false;
    }

    private function getEffectiveRoles($role)
    {
        global $ROLE_HIERARCHY;
        $roles = [];
        $stack = [strtolower($role)];
        $visited = [];

        while (!empty($stack)) {
            $current = array_shift($stack);
            
            if (!in_array($current, $visited)) {
                $visited[] = $current;
                $roles[] = $current;
                
                if (isset($ROLE_HIERARCHY[$current])) {
                    foreach ($ROLE_HIERARCHY[$current] as $inherited) {
                        $lowerInherited = strtolower($inherited);
                        if (!in_array($lowerInherited, $visited)) {
                            array_unshift($stack, $lowerInherited);
                        }
                    }
                }
            }
        }
        return array_unique($roles);
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function getUserRole()
    {
        return $_SESSION['user_role'] ?? null;
    }
}