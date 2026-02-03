<?php
/**
 * Add new program entry
 * Supports both prg_pdf and program_oversikt tables
 */
require_once __DIR__ . '/../core/init.php';

// Require authentication
Auth::requireApiAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate inputs
$table = $_POST['table'] ?? 'prg_pdf';
$dagid = filter_input(INPUT_POST, 'dagid', FILTER_VALIDATE_INT);
$kl = trim($_POST['kl'] ?? '');
$program = trim($_POST['program'] ?? '');

// Whitelist allowed tables
$allowedTables = ['prg_pdf', 'program_oversikt'];
if (!in_array($table, $allowedTables)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid table', 'success' => false]);
    exit;
}

if (!$dagid || $dagid < 1 || $dagid > 9) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid day ID', 'success' => false]);
    exit;
}

if (empty($kl) || empty($program)) {
    http_response_code(400);
    echo json_encode(['error' => 'Time and program are required', 'success' => false]);
    exit;
}

try {
    $db = DB::getInstance();

    // Check if sort_order column exists by trying to query it
    $hasSortOrder = true;
    try {
        $db->query("SELECT sort_order FROM {$table} LIMIT 1");
        if ($db->error()) {
            $hasSortOrder = false;
        }
    } catch (Exception $e) {
        $hasSortOrder = false;
    }

    // Build insert data
    $insertData = [
        'dagid' => $dagid,
        'kl' => $kl,
        'program' => $program
    ];

    // Only add sort_order if the column exists
    if ($hasSortOrder) {
        $maxOrderResult = $db->query("SELECT COALESCE(MAX(sort_order), -1) + 1 as next_order FROM {$table} WHERE dagid = ?", [$dagid]);
        $nextOrder = ($maxOrderResult && $maxOrderResult->count()) ? $maxOrderResult->first()->next_order : 0;
        $insertData['sort_order'] = $nextOrder;
    }

    // Insert the new program
    $result = $db->insert($table, $insertData);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Program added successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add program', 'success' => false]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'success' => false]);
}
