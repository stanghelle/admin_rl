<?php
/**
 * Reorder API
 * Handles task reordering during drag & drop
 */

require_once 'config.php';

$conn = getConnection();
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

$taskId = $data['task_id'];
$newColumnId = $data['new_column_id'];
$newPosition = $data['new_position'];

try {
    $conn->beginTransaction();

    // Get current task info
    $stmt = $conn->prepare("SELECT column_id, position FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task) {
        throw new Exception('Task not found');
    }

    $oldColumnId = $task['column_id'];
    $oldPosition = $task['position'];

    if ($oldColumnId == $newColumnId) {
        // Moving within the same column
        if ($oldPosition < $newPosition) {
            // Moving down
            $stmt = $conn->prepare("
                UPDATE tasks
                SET position = position - 1
                WHERE column_id = ? AND position > ? AND position <= ?
            ");
            $stmt->execute([$oldColumnId, $oldPosition, $newPosition]);
        } else {
            // Moving up
            $stmt = $conn->prepare("
                UPDATE tasks
                SET position = position + 1
                WHERE column_id = ? AND position >= ? AND position < ?
            ");
            $stmt->execute([$oldColumnId, $newPosition, $oldPosition]);
        }
    } else {
        // Moving to different column
        // Update old column positions
        $stmt = $conn->prepare("
            UPDATE tasks
            SET position = position - 1
            WHERE column_id = ? AND position > ?
        ");
        $stmt->execute([$oldColumnId, $oldPosition]);

        // Update new column positions
        $stmt = $conn->prepare("
            UPDATE tasks
            SET position = position + 1
            WHERE column_id = ? AND position >= ?
        ");
        $stmt->execute([$newColumnId, $newPosition]);
    }

    // Update the task itself
    $stmt = $conn->prepare("UPDATE tasks SET column_id = ?, position = ? WHERE id = ?");
    $stmt->execute([$newColumnId, $newPosition, $taskId]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'task_id' => $taskId,
        'old_column_id' => $oldColumnId,
        'new_column_id' => $newColumnId,
        'new_position' => $newPosition
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
