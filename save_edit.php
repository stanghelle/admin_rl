<?php
/**
 * Save inline edits for program_oversikt
 */
require_once 'core/init.php';

// Require authentication for API calls
Auth::requireApiAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['id']) || !isset($_POST['field']) || !isset($_POST['value'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// Validate inputs
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$field = $_POST['field'];
$value = $_POST['value'];

if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

// Whitelist allowed fields to prevent SQL injection via field name
$allowedFields = ['kl', 'navn', 'tek', 'dag', 'uke', 'tittel', 'beskrivelse', 'content'];
if (!in_array($field, $allowedFields)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid field']);
    exit;
}

try {
    $db = DB::getInstance();

    // Use the whitelisted field name in query (safe because we validated it)
    $sql = "UPDATE program_oversikt SET {$field} = ? WHERE id = ?";
    $db->query($sql, [$value, $id]);

    if (!$db->error()) {
        echo json_encode(['status' => 'success', 'message' => 'Update successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>
