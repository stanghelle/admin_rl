<?php
/**
 * Auth Class
 * Centralized authentication management with enhanced security
 */
class Auth {
    private static $_user = null;
    private static $_initialized = false;

    /**
     * Initialize the auth system
     * Should be called once at the start of each request
     */
    public static function init() {
        if (self::$_initialized) {
            return;
        }

        self::$_initialized = true;
        self::$_user = new User();

        // Enhance session security
        self::secureSession();
    }

    /**
     * Apply security settings to the session
     */
    private static function secureSession() {
        // Only set cookie params if session is not already active
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session cookie parameters
            $cookieParams = [
                'lifetime' => 0, // Session cookie (expires when browser closes)
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ];

            if (PHP_VERSION_ID >= 70300) {
                session_set_cookie_params($cookieParams);
            } else {
                session_set_cookie_params(
                    $cookieParams['lifetime'],
                    $cookieParams['path'],
                    $cookieParams['domain'],
                    $cookieParams['secure'],
                    $cookieParams['httponly']
                );
            }
        }

        // Validate session fingerprint to prevent session hijacking
        self::validateSessionFingerprint();
    }

    /**
     * Create and validate session fingerprint
     */
    private static function validateSessionFingerprint() {
        $fingerprint = self::generateFingerprint();

        if (Session::exists('fingerprint')) {
            if (Session::get('fingerprint') !== $fingerprint) {
                // Possible session hijacking - destroy session
                self::destroySession();
                return;
            }
        } else {
            Session::put('fingerprint', $fingerprint);
        }
    }

    /**
     * Generate a fingerprint based on user's browser info
     */
    private static function generateFingerprint() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        return hash('sha256', $userAgent . $acceptLanguage);
    }

    /**
     * Regenerate session ID (should be called after login)
     */
    public static function regenerateSession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            Session::put('fingerprint', self::generateFingerprint());
            Session::put('last_regeneration', time());
        }
    }

    /**
     * Get the current user object
     */
    public static function user() {
        if (!self::$_initialized) {
            self::init();
        }
        return self::$_user;
    }

    /**
     * Check if user is logged in
     */
    public static function check() {
        return self::user()->isLoggedIn();
    }

    /**
     * Check if user is a guest (not logged in)
     */
    public static function guest() {
        return !self::check();
    }

    /**
     * Get user ID if logged in
     */
    public static function id() {
        if (self::check()) {
            return self::user()->data()->id;
        }
        return null;
    }

    /**
     * Require authentication - redirect to login if not authenticated
     * Use this at the top of protected pages
     */
    public static function requireLogin($redirectTo = 'index.php') {
        if (self::guest()) {
            Session::flash('error', 'Du må logge på for å bruke denne funksjonen.');
            Redirect::to($redirectTo);
            exit;
        }

        // Check if account is still enabled
        if (self::user()->data()->enabled == 0) {
            self::logout();
            Session::flash('error', 'Din konto er deaktivert. Ta kontakt med administrator.');
            Redirect::to($redirectTo);
            exit;
        }
    }

    /**
     * Require specific permission
     */
    public static function requirePermission($permission, $redirectTo = 'dashboard.php') {
        self::requireLogin();

        if (!self::user()->hasPermission($permission)) {
            Session::flash('error', 'Du har ikke tilgang til denne funksjonen.');
            Redirect::to($redirectTo);
            exit;
        }
    }

    /**
     * Require that user is NOT logged in (for login/register pages)
     */
    public static function requireGuest($redirectTo = 'dashboard.php') {
        if (self::check()) {
            Redirect::to($redirectTo);
            exit;
        }
    }

    /**
     * Attempt login with credentials
     */
    public static function attempt($email, $password, $remember = false) {
        $result = self::user()->login($email, $password, $remember);

        if ($result) {
            // Regenerate session ID to prevent session fixation
            self::regenerateSession();

            // Log the login
            self::logActivity('login', 'User logged in');
        }

        return $result;
    }

    /**
     * Logout the current user
     */
    public static function logout() {
        if (self::check()) {
            self::logActivity('logout', 'User logged out');
            self::user()->logout();
        }

        self::destroySession();
    }

    /**
     * Destroy the current session completely
     */
    private static function destroySession() {
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

    /**
     * Log user activity (for audit trail)
     */
    public static function logActivity($action, $description = '') {
        if (!self::check()) {
            return;
        }

        try {
            $db = DB::getInstance();
            $db->insert('auth_log', [
                'user_id' => self::id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => self::getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // Silently fail - don't break the app if logging fails
            // The auth_log table might not exist yet
        }
    }

    /**
     * Get client IP address
     */
    private static function getClientIP() {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Check if the current request is an AJAX request
     */
    public static function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Send JSON error response for AJAX requests
     */
    public static function jsonError($message, $code = 401) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message, 'authenticated' => false]);
        exit;
    }

    /**
     * Require login for API/AJAX requests
     */
    public static function requireApiAuth() {
        if (self::guest()) {
            self::jsonError('Authentication required', 401);
        }
    }
}
