<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

// Require CSRF token
Token::require();

$db = DB::getInstance();

// Validate input
$validate = new Validate();
$validation = $validate->check($_POST, array(
	'email' => array('required' => true),
	'password' => array('required' => true, 'min' => 6),
	'name' => array('required' => true)
));

if ($validation->passed()) {
	// Get and sanitize input
	$email = trim(Input::get('email'));
	$password = Input::get('password');
	$name = trim(Input::get('name'));
	$stilling = trim(Input::get('stilling'));
	$tlf = trim(Input::get('tlf'));

	// Check if email already exists
	$existingUser = $db->get('users', array('email', '=', $email));
	if ($existingUser->count()) {
		Session::flash('error', 'En bruker med denne e-postadressen finnes allerede.');
		Redirect::to('new_user.php');
	}

	// Create secure password hash using bcrypt
	$hashedPassword = Hash::make($password);

	// Insert using prepared statements (DB class handles this securely)
	try {
		$db->insert('users', array(
			'email' => $email,
			'password' => $hashedPassword,
			'salt' => '', // No longer needed with bcrypt
			'name' => $name,
			'tlf' => $tlf,
			'stilling' => $stilling,
			'image' => 'user.png',
			'enabled' => 1,
			'activated' => 1
		));

		Session::flash('success', 'Bruker er nå lagt til.');
		Redirect::to('users.php');
	} catch (Exception $e) {
		Session::flash('error', 'Det oppstod en feil ved opprettelse av bruker.');
		Redirect::to('new_user.php');
	}
} else {
	$error_str = '';
	foreach ($validate->errors() as $error) {
		$error_str .= $error . '<br />';
	}
	Session::flash('error', $error_str);
	Redirect::to('new_user.php');
}
