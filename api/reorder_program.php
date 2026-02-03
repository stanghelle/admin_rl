<?php
/**
 * Reorder Program Items API
 * Handles drag and drop sorting for program_oversikt and prg_pdf tables
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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Try POST data
    $input = $_POST;
}

// Validate required fields
$table = $input['table'] ?? '';
$items = $input['items'] ?? [];
$dagid = isset($input['dagid']) ? (int)$input['dagid'] : null;

// Whitelist allowed tables
$allowedTables = ['program_oversikt', 'prg_pdf'];
if (!in_array($table, $allowedTables)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid table']);
    exit;
}

if (empty($items) || !is_array($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'No items to reorder']);
    exit;
}

try {
    $db = DB::getInstance();

    // Check if sort_order column exists
    $hasSortOrder = true;
    try {
        $db->query("SELECT sort_order FROM {$table} LIMIT 1");
        if ($db->error()) {
            $hasSortOrder = false;
        }
    } catch (Exception $e) {
        $hasSortOrder = false;
    }

    if (!$hasSortOrder) {
        // Column doesn't exist - return success but note that migration is needed
        echo json_encode([
            'success' => true,
            'message' => 'Sort order column not found. Run database migration to enable sorting.',
            'items_updated' => 0,
            'migration_needed' => true
        ]);
        exit;
    }

    // Update the sort order for each item
    $position = 0;
    foreach ($items as $itemId) {
        $itemId = (int)$itemId;
        if ($itemId > 0) {
            $sql = "UPDATE {$table} SET sort_order = ? WHERE id = ?";
            $db->query($sql, [$position, $itemId]);
            $position++;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Order updated successfully',
        'items_updated' => $position
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
