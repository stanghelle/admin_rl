<?php
require_once 'core/init.php';

// Require authentication
Auth::requireApiAuth();

$db = DB::getInstance();

$uid = filter_input(INPUT_POST, 'uid', FILTER_VALIDATE_INT);

if (!$uid) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

try {
    $result = $db->delete('medarb', ['id', '=', $uid]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Delete failed']);
}
