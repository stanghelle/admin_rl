<?php
/**
 * Columns API
 * Handles CRUD operations for Kanban columns/lists
 */

require_once 'config.php';

$conn = getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all columns with their tasks
        $boardId = $_GET['board_id'] ?? 1;

        $stmt = $conn->prepare("
            SELECT c.*,
                   COALESCE(
                       JSON_ARRAYAGG(
                           IF(t.id IS NOT NULL,
                               JSON_OBJECT(
                                   'id', t.id,
                                   'title', t.title,
                                   'description', t.description,
                                   'priority', t.priority,
                                   'position', t.position,
                                   'due_date', t.due_date,
                                   'assigned_to', t.assigned_to,
                                   'labels', t.labels
                               ),
                               NULL
                           )
                       ),
                       '[]'
                   ) as tasks
            FROM columns_list c
            LEFT JOIN tasks t ON c.id = t.column_id
            WHERE c.board_id = ?
            GROUP BY c.id
            ORDER BY c.position ASC
        ");
        $stmt->execute([$boardId]);
        $columns = $stmt->fetchAll();

        // Parse tasks JSON and filter nulls
        foreach ($columns as &$column) {
            $tasks = json_decode($column['tasks'], true);
            $column['tasks'] = array_filter($tasks, fn($t) => $t !== null);
            usort($column['tasks'], fn($a, $b) => $a['position'] - $b['position']);
        }

        echo json_encode($columns);
        break;

    case 'POST':
        // Create new column
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Column name is required']);
            exit;
        }

        $boardId = $data['board_id'] ?? 1;

        // Get max position
        $stmt = $conn->prepare("SELECT COALESCE(MAX(position), -1) + 1 as next_pos FROM columns_list WHERE board_id = ?");
        $stmt->execute([$boardId]);
        $nextPos = $stmt->fetch()['next_pos'];

        $stmt = $conn->prepare("INSERT INTO columns_list (board_id, name, position, color) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $boardId,
            $data['name'],
            $nextPos,
            $data['color'] ?? '#6c757d'
        ]);

        echo json_encode([
            'id' => $conn->lastInsertId(),
            'name' => $data['name'],
            'position' => $nextPos,
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

        $stmt = $conn->prepare("UPDATE columns_list SET name = ?, color = ? WHERE id = ?");
        $stmt->execute([
            $data['name'],
            $data['color'] ?? '#6c757d',
            $data['id']
        ]);

        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Delete column
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Column ID is required']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM columns_list WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
