<?php
/**
 * Add new program to prg_pdf table
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
$dagid = filter_input(INPUT_POST, 'dagid', FILTER_VALIDATE_INT);
$kl = trim($_POST['kl'] ?? '');
$program = trim($_POST['program'] ?? '');

// Debug: log what we received
$debug = [
    'dagid' => $dagid,
    'kl' => $kl,
    'program' => $program
];

if (!$dagid || $dagid < 1 || $dagid > 9) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid day ID', 'success' => false, 'debug' => $debug]);
    exit;
}

if (empty($kl) || empty($program)) {
    http_response_code(400);
    echo json_encode(['error' => 'Time and program are required', 'success' => false, 'debug' => $debug]);
    exit;
}

try {
    $db = DB::getInstance();

    // Get the max sort_order for this day
    $maxOrderResult = $db->query("SELECT COALESCE(MAX(sort_order), -1) + 1 as next_order FROM prg_pdf WHERE dagid = ?", [$dagid]);

    if ($maxOrderResult && $maxOrderResult->count() > 0) {
        $nextOrder = (int)$maxOrderResult->first()->next_order;
    } else {
        $nextOrder = 0;
    }

    // Build insert data
    $insertData = [
        'dagid' => $dagid,
        'kl' => $kl,
        'program' => $program,
        'sort_order' => $nextOrder
    ];

    // Insert the new program
    $result = $db->insert('prg_pdf', $insertData);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Program added successfully'
        ]);
    } else {
        // Get more details about the error
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to add program: ' . $db->errorMessage(),
            'success' => false,
            'debug' => [
                'insertData' => $insertData,
                'dbError' => $db->error(),
                'dbErrorMessage' => $db->errorMessage()
            ]
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'success' => false,
        'trace' => $e->getTraceAsString()
    ]);
}
