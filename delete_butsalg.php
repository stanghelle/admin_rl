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
    Redirect::to('bingo_utsalg.php');
}

try {
    $result = $db->delete('bingo_utsalg', ['id', '=', $id]);

    if ($result) {
        Session::flash('success', 'Utsalgssted har blitt fjernet fra oversikten.');
    } else {
        Session::flash('error', 'Kunne ikke fjerne utsalgssted.');
    }
} catch (Exception $e) {
    Session::flash('error', 'En uventet feil oppstod ved fjerning av utsalgssted.');
}

Redirect::to('bingo_utsalg.php');
