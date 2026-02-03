<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

// Require CSRF token
Token::require();

$db = DB::getInstance();

// Get and sanitize input
$usid = trim(Input::get('usid'));
$navn = trim(Input::get('outletName'));
$usadr = trim(Input::get('outletAddress'));
$tlf = trim(Input::get('outletPhone'));
$kontakt = trim(Input::get('outletContact'));
$active = (int) Input::get('outletActive');

// Validate required fields
if (empty($navn)) {
    Session::flash('error', 'Navn er påkrevd.');
    Redirect::to('app_bingo_utsalg.php');
}

try {
    // Insert using prepared statements
    $db->insert('us_outlets', array(
        'name' => $navn,
        'address' => $usadr,
        'phone' => $tlf,
        'contact_person' => $kontakt,
        'active' => $active
    ));

    // Get the last inserted ID using a query
    $lastIdResult = $db->query("SELECT LAST_INSERT_ID() as id");
    if ($lastIdResult->count()) {
        $lastId = $lastIdResult->first()->id;

        // Update bingo_utsalg with app_id
        if (!empty($usid)) {
            $db->query("UPDATE bingo_utsalg SET app_id = ? WHERE usid = ?", array($lastId, $usid));
        }
    }

    Session::flash('success', 'App utsalgssted er nå lagt til.');
    Redirect::to('app_bingo_utsalg.php');
} catch (Exception $e) {
    Session::flash('error', 'Det oppstod en feil ved opprettelse av utsalgssted.');
    Redirect::to('app_bingo_utsalg.php');
}
