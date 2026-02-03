<?php
/**
 * Kanban Reorder API
 * Handles task reordering during drag & drop
 */

require_once 'config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['task_id']) || !isset($data['new_column_id']) || !isset($data['new_position'])) {
    http_response_code(400);
    echo json_encode(['error' => 'task_id, new_column_id, and new_position are required']);
    exit;
}

$taskId = (int)$data['task_id'];
$newColumnId = (int)$data['new_column_id'];
$newPosition = (int)$data['new_position'];

try {
    // Get current task info
    $db->query("SELECT column_id, position FROM kanban_tasks WHERE id = {$taskId}");
    $task = $db->first();

    if (!$task) {
        http_response_code(404);
        echo json_encode(['error' => 'Task not found']);
        exit;
    }

    $oldColumnId = (int)$task->column_id;
    $oldPosition = (int)$task->position;

    // Step 1: Remove task from its current position (set position to -1 temporarily)
    $db->query("UPDATE kanban_tasks SET position = -1 WHERE id = {$taskId}");

    // Step 2: Close the gap in the old column
    $db->query("UPDATE kanban_tasks SET position = position - 1
                WHERE column_id = {$oldColumnId} AND position > {$oldPosition}");

    // Step 3: Make space in the new column
    $db->query("UPDATE kanban_tasks SET position = position + 1
                WHERE column_id = {$newColumnId} AND position >= {$newPosition}");

    // Step 4: Place the task in its new position
    $db->query("UPDATE kanban_tasks SET column_id = {$newColumnId}, position = {$newPosition} WHERE id = {$taskId}");

    echo json_encode([
        'success' => true,
        'task_id' => $taskId,
        'old_column_id' => $oldColumnId,
        'old_position' => $oldPosition,
        'new_column_id' => $newColumnId,
        'new_position' => $newPosition
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
