<?php
/**
 * Update prg_pdf table fields via AJAX
 */
require_once 'core/init.php';

// Require authentication for API calls
Auth::requireApiAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate inputs
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$field = $_POST['field'] ?? '';
$value = $_POST['content'] ?? '';

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

// Whitelist allowed fields to prevent SQL injection via field name
$allowedFields = ['kl', 'program', 'navn', 'tek', 'dag', 'uke'];
if (!in_array($field, $allowedFields)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid field']);
    exit;
}

try {
    $db = DB::getInstance();

    // Use the whitelisted field name in query (safe because we validated it)
    $sql = "UPDATE prg_pdf SET {$field} = ? WHERE id = ?";
    $db->query($sql, [$value, $id]);

    if (!$db->error()) {
        echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error updating record']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
