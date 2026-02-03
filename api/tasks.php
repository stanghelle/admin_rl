<?php
/**
 * Tasks API
 * Handles CRUD operations for Kanban tasks/cards
 * Now with authentication and CSRF protection
 */

require_once 'auth.php';

// Require authentication for all task operations
requireApiAuth();

// Get database connection
$conn = getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Require CSRF token for state-changing operations
if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    requireCsrfToken();
}

switch ($method) {
    case 'GET':
        // Get single task or all tasks
        $id = $_GET['id'] ?? null;

        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
            $stmt->execute([$id]);
            $task = $stmt->fetch();

            if (!$task) {
                jsonError('Task not found', 404);
            }

            $task['labels'] = json_decode($task['labels'], true) ?? [];
            jsonResponse($task);
        } else {
            $columnId = $_GET['column_id'] ?? null;

            if ($columnId) {
                $stmt = $conn->prepare("SELECT * FROM tasks WHERE column_id = ? ORDER BY position ASC");
                $stmt->execute([$columnId]);
            } else {
                $stmt = $conn->query("SELECT * FROM tasks ORDER BY column_id, position ASC");
            }

            $tasks = $stmt->fetchAll();
            foreach ($tasks as &$task) {
                $task['labels'] = json_decode($task['labels'], true) ?? [];
            }
            jsonResponse($tasks);
        }
        break;

    case 'POST':
        // Create new task
        $data = getJsonInput();

        if (empty($data['title']) || empty($data['column_id'])) {
            jsonError('Title and column_id are required', 400);
        }

        // Get max position in column
        $stmt = $conn->prepare("SELECT COALESCE(MAX(position), -1) + 1 as next_pos FROM tasks WHERE column_id = ?");
        $stmt->execute([$data['column_id']]);
        $nextPos = $stmt->fetch()['next_pos'];

        $stmt = $conn->prepare("
            INSERT INTO tasks (column_id, title, description, priority, position, due_date, assigned_to, labels)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['column_id'],
            $data['title'],
            $data['description'] ?? '',
            $data['priority'] ?? 'medium',
            $nextPos,
            $data['due_date'] ?? null,
            $data['assigned_to'] ?? null,
            json_encode($data['labels'] ?? [])
        ]);

        jsonResponse([
            'id' => $conn->lastInsertId(),
            'column_id' => $data['column_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'priority' => $data['priority'] ?? 'medium',
            'position' => $nextPos,
            'due_date' => $data['due_date'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'labels' => $data['labels'] ?? []
        ], 201);
        break;

    case 'PUT':
        // Update task
        $data = getJsonInput();

        if (empty($data['id'])) {
            jsonError('Task ID is required', 400);
        }

        // Check if this is a move operation (drag & drop)
        if (isset($data['column_id']) && isset($data['position'])) {
            // Update position and column
            $stmt = $conn->prepare("UPDATE tasks SET column_id = ?, position = ? WHERE id = ?");
            $stmt->execute([$data['column_id'], $data['position'], $data['id']]);

            jsonResponse(['success' => true, 'action' => 'moved']);
        } else {
            // Full update
            $stmt = $conn->prepare("
                UPDATE tasks
                SET title = ?, description = ?, priority = ?, due_date = ?, assigned_to = ?, labels = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['title'],
                $data['description'] ?? '',
                $data['priority'] ?? 'medium',
                $data['due_date'] ?? null,
                $data['assigned_to'] ?? null,
                json_encode($data['labels'] ?? []),
                $data['id']
            ]);

            jsonResponse(['success' => true, 'action' => 'updated']);
        }
        break;

    case 'DELETE':
        // Delete task
        $id = $_GET['id'] ?? null;

        if (!$id) {
            jsonError('Task ID is required', 400);
        }

        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);

        jsonResponse(['success' => true]);
        break;

    default:
        jsonError('Method not allowed', 405);
}
