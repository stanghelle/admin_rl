<?php
/**
 * Kanban Columns API
 * Handles CRUD operations for Kanban columns/lists
 */

require_once 'config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get all columns with their tasks
            $boardId = isset($_GET['board_id']) ? (int)$_GET['board_id'] : 1;

            // Get columns
            $db->query("SELECT * FROM kanban_columns WHERE board_id = {$boardId} ORDER BY position ASC");

            if ($db->error()) {
                throw new Exception('Database error fetching columns');
            }

            $columns = $db->results();
            $result = [];

            if ($columns) {
                foreach ($columns as $column) {
                    // Get tasks for this column
                    $columnId = (int)$column->id;
                    $db->query("SELECT * FROM kanban_tasks WHERE column_id = {$columnId} ORDER BY position ASC");

                    $columnData = [
                        'id' => (int)$column->id,
                        'board_id' => (int)$column->board_id,
                        'name' => $column->name,
                        'position' => (int)$column->position,
                        'color' => $column->color,
                        'tasks' => []
                    ];

                    $tasks = $db->results();
                    if ($tasks) {
                        foreach ($tasks as $task) {
                            $labels = $task->labels ? json_decode($task->labels, true) : [];
                            $columnData['tasks'][] = [
                                'id' => (int)$task->id,
                                'column_id' => (int)$task->column_id,
                                'title' => $task->title,
                                'description' => $task->description,
                                'priority' => $task->priority,
                                'position' => (int)$task->position,
                                'due_date' => $task->due_date,
                                'assigned_to' => $task->assigned_to,
                                'labels' => $labels
                            ];
                        }
                    }

                    $result[] = $columnData;
                }
            }

            echo json_encode($result);
            break;

        case 'POST':
            // Create new column
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Column name is required']);
                exit;
            }

            $boardId = isset($data['board_id']) ? (int)$data['board_id'] : 1;
            $name = addslashes($data['name']);
            $color = isset($data['color']) ? addslashes($data['color']) : '#6c757d';

            // Get max position
            $db->query("SELECT COALESCE(MAX(position), -1) + 1 as next_pos FROM kanban_columns WHERE board_id = {$boardId}");
            $result = $db->first();
            $nextPos = $result ? (int)$result->next_pos : 0;

            // Insert new column
            $db->query("INSERT INTO kanban_columns (board_id, name, position, color) VALUES ({$boardId}, '{$name}', {$nextPos}, '{$color}')");

            if ($db->error()) {
                throw new Exception('Failed to create column');
            }

            // Get the inserted ID
            $newId = getDB()->lastInsertId();

            echo json_encode([
                'id' => (int)$newId,
                'board_id' => $boardId,
                'name' => $data['name'],
                'position' => (int)$nextPos,
                'color' => $data['color'] ?? '#6c757d',
                'tasks' => []
            ]);
            break;

        case 'PUT':
            // Update column
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Column ID is required']);
                exit;
            }

            $id = (int)$data['id'];
            $name = addslashes($data['name']);
            $color = isset($data['color']) ? addslashes($data['color']) : '#6c757d';

            $db->query("UPDATE kanban_columns SET name = '{$name}', color = '{$color}' WHERE id = {$id}");

            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            // Delete column
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Column ID is required']);
                exit;
            }

            // Delete tasks first
            $db->query("DELETE FROM kanban_tasks WHERE column_id = {$id}");
            // Delete column
            $db->query("DELETE FROM kanban_columns WHERE id = {$id}");

            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
