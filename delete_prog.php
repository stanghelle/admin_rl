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
    Redirect::to('prg_pdf.php');
}

try {
    $result = $db->delete('prg_pdf', ['id', '=', $id]);

    if ($result) {
        Session::flash('success', 'Programmet har blitt fjernet fra programoversikten.');
    } else {
        Session::flash('error', 'Kunne ikke fjerne programmet.');
    }
} catch (Exception $e) {
    Session::flash('error', 'En uventet feil oppstod ved fjerning av programmet.');
}

Redirect::to('prg_pdf.php');
