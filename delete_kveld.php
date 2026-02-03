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
    Session::flash('error', 'Ugyldig ID.');
    Redirect::to('kveld.php');
}

try {
    // Delete using prepared statement
    $result = $db->delete('kveld', ['id', '=', $id]);

    if ($result) {
        Session::flash('success', 'Innlegget har blitt slettet fra systemet.');
    } else {
        Session::flash('error', 'Kunne ikke slette innlegget.');
    }
} catch (Exception $e) {
    Session::flash('error', 'En uventet feil oppstod ved sletting av innlegget.');
}

Redirect::to('kveld.php');
