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
    'navn' => array('required' => true),
    'Stilling' => array('required' => true)
));

if ($validation->passed()) {
    // Get and sanitize input
    $navn = trim(Input::get('navn'));
    $stilling = trim(Input::get('Stilling'));
    $protek = trim(Input::get('protek'));
    $program = trim(Input::get('program'));
    $epost = trim(Input::get('epost'));
    $tlf = trim(Input::get('tlf'));

    try {
        $db->insert('medarb', array(
            'navn' => $navn,
            'stilling' => $stilling,
            'protek' => $protek,
            'program' => $program,
            'epost' => $epost,
            'tlf' => $tlf,
            'img' => 'user.png'
        ));

        Session::flash('success', 'Medarbeider er nå lagt til.');
        Redirect::to('medarb.php');
    } catch (Exception $e) {
        Session::flash('error', 'Det oppstod en feil ved opprettelse av medarbeider.');
        Redirect::to('ny_medarb.php');
    }
} else {
    $error_str = '';
    foreach ($validate->errors() as $error) {
        $error_str .= $error . '<br />';
    }
    Session::flash('error', $error_str);
    Redirect::to('ny_medarb.php');
}
