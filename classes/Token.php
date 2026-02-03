<?php
/**
 * Token Class
 * Enhanced CSRF protection with secure token generation
 */
class Token {
    private static $tokenName = 'csrf_token';
    private static $tokenExpiry = 3600; // 1 hour

    /**
     * Generate a new CSRF token
     * @return string The generated token
     */
    public static function generate() {
        $token = bin2hex(random_bytes(32));
        $tokenData = [
            'token' => $token,
            'expires' => time() + self::$tokenExpiry
        ];

        Session::put(self::$tokenName, $tokenData);
        return $token;
    }

    /**
     * Get the current token (or generate one if none exists)
     * @return string The current CSRF token
     */
    public static function get() {
        if (!Session::exists(self::$tokenName)) {
            return self::generate();
        }

        $tokenData = Session::get(self::$tokenName);

        // Check if token has expired
        if ($tokenData['expires'] < time()) {
            return self::generate();
        }

        return $tokenData['token'];
    }

    /**
     * Check if the provided token is valid
     * @param string $token The token to validate
     * @param bool $regenerate Whether to regenerate the token after validation
     * @return bool True if valid, false otherwise
     */
    public static function check($token, $regenerate = true) {
        if (!Session::exists(self::$tokenName) || empty($token)) {
            return false;
        }

        $tokenData = Session::get(self::$tokenName);

        // Check expiration
        if ($tokenData['expires'] < time()) {
            Session::delete(self::$tokenName);
            return false;
        }

        // Constant-time comparison to prevent timing attacks
        $valid = hash_equals($tokenData['token'], $token);

        if ($valid && $regenerate) {
            // Regenerate token after successful use (one-time use)
            self::generate();
        }

        return $valid;
    }

    /**
     * Validate token from POST request
     * Automatically checks $_POST['token'] or $_POST['csrf_token']
     * @return bool True if valid
     */
    public static function validate() {
        $token = $_POST['token'] ?? $_POST['csrf_token'] ?? '';
        return self::check($token);
    }

    /**
     * Output a hidden input field with the CSRF token
     * @return string HTML input element
     */
    public static function input() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::get()) . '">';
    }

    /**
     * Output a meta tag with the CSRF token (for AJAX requests)
     * @return string HTML meta element
     */
    public static function meta() {
        return '<meta name="csrf-token" content="' . htmlspecialchars(self::get()) . '">';
    }

    /**
     * Require valid token or die with error
     * Use at the top of form processing scripts
     */
    public static function require() {
        if (!self::validate()) {
            if (Auth::isAjax()) {
                Auth::jsonError('Invalid security token. Please refresh and try again.', 403);
            } else {
                Session::flash('error', 'Ugyldig sikkerhetstoken. Vennligst prøv igjen.');
                Redirect::to($_SERVER['HTTP_REFERER'] ?? 'dashboard.php');
                exit;
            }
        }
    }
}
