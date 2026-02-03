<?php
/**
 * Core Initialization File
 * Sets up the application environment, autoloading, and authentication
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Environment configuration
$isProduction = false; // Set to true in production

if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Global configuration
$GLOBALS['config'] = array(
    'mysql' => array(
        'host'     => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'db'       => 'radio'
    ),
    'remember' => array(
        'cookie_name'  => 'hash',
        'cookie_expiry' => 604800 // 7 days
    ),
    'session' => array(
        'session_name' => 'user',
        'token_name'   => 'token'
    ),
    'site' => array(
        'name' => 'RL Admin'
    ),
    'security' => array(
        'csrf_enabled' => true,
        'session_lifetime' => 3600 // 1 hour
    )
);

// Autoload classes
function autoload($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('autoload');

// Include functions
require_once __DIR__ . '/../functions/sanitize.php';

// Initialize authentication system
Auth::init();

// Cookie policy notification
if (!Cookie::exists('cookie_policy')) {
    Cookie::put('cookie_policy', '1', 604800);
    Session::flash('info', 'Vi bruker cookies til å lagre informasjon på din enhet. <a href="http://en.wikipedia.org/wiki/HTTP_cookie" target="_blank">Hva er cookies?</a>');
}

// Check for users that have requested to be remembered
if (Cookie::exists(Config::get('remember/cookie_name')) && !Auth::check()) {
    $hash = Cookie::get(Config::get('remember/cookie_name'));
    $hashCheck = DB::getInstance()->get('users_session', array('hash', '=', $hash));

    if ($hashCheck->count()) {
        $user = new User($hashCheck->first()->user_id);
        if ($user->data()->enabled == 1) {
            $user->login();
            Auth::regenerateSession();
        } else {
            Session::flash('error', 'Det ser ut til at din konto er sperret. Ta kontakt med oss for nærmere detaljer.');
            Cookie::delete(Config::get('remember/cookie_name'));
            Redirect::to('index.php');
        }
    }
}

// Check if logged in user is still enabled
if (Auth::check()) {
    if (Auth::user()->data()->enabled == 0) {
        Auth::logout();
        Session::flash('error', 'Det ser ut til at din konto er sperret. Ta kontakt med oss for nærmere detaljer.');
        Redirect::to('index.php');
    }
}

// Set database charset
DB::getInstance()->query("SET CHARACTER SET utf8");

// Set timezone
date_default_timezone_set("Europe/Oslo");

// Create global $user variable for backward compatibility
$user = Auth::user();
