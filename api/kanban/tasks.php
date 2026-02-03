<?php
/**
 * Kanban Tasks API
 * Handles CRUD operations for Kanban tasks/cards
 */

require_once 'config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get single task or all tasks
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

            if ($id) {
                $db->query("SELECT * FROM kanban_tasks WHERE id = {$id}");
                $task = $db->first();

                if (!$task) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Task not found']);
                    exit;
                }

                $labels = $task->labels ? json_decode($task->labels, true) : [];

                echo json_encode([
                    'id' => (int)$task->id,
                    'column_id' => (int)$task->column_id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'priority' => $task->priority,
                    'position' => (int)$task->position,
                    'due_date' => $task->due_date,
                    'assigned_to' => $task->assigned_to,
                    'labels' => $labels
                ]);
            } else {
                $columnId = isset($_GET['column_id']) ? (int)$_GET['column_id'] : null;

                if ($columnId) {
                    $db->query("SELECT * FROM kanban_tasks WHERE column_id = {$columnId} ORDER BY position ASC");
                } else {
                    $db->query("SELECT * FROM kanban_tasks ORDER BY column_id, position ASC");
                }

                $tasks = $db->results();
                $result = [];

                if ($tasks) {
                    foreach ($tasks as $task) {
                        $labels = $task->labels ? json_decode($task->labels, true) : [];
                        $result[] = [
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

                echo json_encode($result);
            }
            break;

        case 'POST':
            // Create new task
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['title']) || empty($data['column_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Title and column_id are required']);
                exit;
            }

            $columnId = (int)$data['column_id'];
            $title = addslashes($data['title']);
            $description = isset($data['description']) ? addslashes($data['description']) : '';
            $priority = isset($data['priority']) ? addslashes($data['priority']) : 'medium';
            $dueDate = isset($data['due_date']) && $data['due_date'] ? "'" . addslashes($data['due_date']) . "'" : 'NULL';
            $assignedTo = isset($data['assigned_to']) && $data['assigned_to'] ? "'" . addslashes($data['assigned_to']) . "'" : 'NULL';
            $labels = isset($data['labels']) ? addslashes(json_encode($data['labels'])) : '[]';

            // Get max position in column
            $db->query("SELECT COALESCE(MAX(position), -1) + 1 as next_pos FROM kanban_tasks WHERE column_id = {$columnId}");
            $result = $db->first();
            $nextPos = $result ? (int)$result->next_pos : 0;

            // Insert new task
            $db->query("INSERT INTO kanban_tasks (column_id, title, description, priority, position, due_date, assigned_to, labels)
                        VALUES ({$columnId}, '{$title}', '{$description}', '{$priority}', {$nextPos}, {$dueDate}, {$assignedTo}, '{$labels}')");

            if ($db->error()) {
                throw new Exception('Failed to create task');
            }

            // Get the inserted ID
            $newId = getDB()->lastInsertId();

            echo json_encode([
                'id' => (int)$newId,
                'column_id' => $columnId,
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'priority' => $data['priority'] ?? 'medium',
                'position' => (int)$nextPos,
                'due_date' => $data['due_date'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'labels' => $data['labels'] ?? []
            ]);
            break;

        case 'PUT':
            // Update task
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Task ID is required']);
                exit;
            }

            $id = (int)$data['id'];

            // Check if this is a move operation (drag & drop)
            if (isset($data['column_id']) && isset($data['position']) && !isset($data['title'])) {
                $columnId = (int)$data['column_id'];
                $position = (int)$data['position'];

                $db->query("UPDATE kanban_tasks SET column_id = {$columnId}, position = {$position} WHERE id = {$id}");
                echo json_encode(['success' => true, 'action' => 'moved']);
            } else {
                // Full update
                $title = addslashes($data['title']);
                $description = isset($data['description']) ? addslashes($data['description']) : '';
                $priority = isset($data['priority']) ? addslashes($data['priority']) : 'medium';
                $dueDate = isset($data['due_date']) && $data['due_date'] ? "'" . addslashes($data['due_date']) . "'" : 'NULL';
                $assignedTo = isset($data['assigned_to']) && $data['assigned_to'] ? "'" . addslashes($data['assigned_to']) . "'" : 'NULL';
                $labels = isset($data['labels']) ? addslashes(json_encode($data['labels'])) : '[]';

                $db->query("UPDATE kanban_tasks SET title = '{$title}', description = '{$description}', priority = '{$priority}',
                            due_date = {$dueDate}, assigned_to = {$assignedTo}, labels = '{$labels}' WHERE id = {$id}");
                echo json_encode(['success' => true, 'action' => 'updated']);
            }
            break;

        case 'DELETE':
            // Delete task
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Task ID is required']);
                exit;
            }

            $db->query("DELETE FROM kanban_tasks WHERE id = {$id}");

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
