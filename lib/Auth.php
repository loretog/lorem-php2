<?php

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
            $_SESSION['user_role'] = $user['role'];
            session_regenerate_id(true);
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
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
    }

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
                header('Location: ' . SITE_URL . '/login');
                exit();
            }
        }
        $_SESSION['last_activity'] = time();

        if ($this->isLoggedIn() && !$this->isSessionValid()) {
            $this->logout();
            header('Location: ' . SITE_URL . '/login');
            exit();
        }
    }

    private function isSessionValid(): bool
    {
        return $_SESSION['user_agent'] === ($_SERVER['HTTP_USER_AGENT'] ?? '');
    }

    public function checkAccess($requiredRole = 'user')
    {
        if (!$this->isLoggedIn() || $_SESSION['user_role'] !== $requiredRole) {
            header('Location: ' . SITE_URL . '/login');
            exit();
        }
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