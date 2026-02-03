<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

// Require CSRF token for delete operations
Token::require();

$db = DB::getInstance();

// Validate ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    Session::flash('error', 'Ugyldig bruker-ID.');
    Redirect::to('bapp_users.php');
}

try {
    $result = $db->delete('us_users', ['id', '=', $id]);

    if ($result) {
        Session::flash('success', 'Brukeren har blitt slettet fra systemet.');
    } else {
        Session::flash('error', 'Kunne ikke slette brukeren.');
    }
} catch (Exception $e) {
    Session::flash('error', 'En uventet feil oppstod ved sletting av brukeren.');
}

Redirect::to('bapp_users.php');
