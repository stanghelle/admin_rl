<?php
/**
 * Logout Handler
 * Securely logs out the user and destroys the session
 */
require_once 'core/init.php';

// Perform logout
Auth::logout();

// Set success message
Session::flash('success', 'Du er nå logget ut.');

// Redirect to login page
Redirect::to('index.php');
