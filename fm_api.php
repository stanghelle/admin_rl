<?php
/**
 * File Manager API
 * Handles: scan, upload, delete, rename, newfolder
 */

require_once 'core/init.php';

// Require authentication for file operations
Auth::requireApiAuth();

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$baseDir = 'files';
$maxFileSize = 50 * 1024 * 1024; // 50MB

// Create files directory if not exists
if (!file_exists($baseDir)) {
    mkdir($baseDir, 0755, true);
}

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'scan':
            scanFiles();
            break;
        case 'upload':
            uploadFiles();
            break;
        case 'delete':
            deleteFile();
            break;
        case 'rename':
            renameFile();
            break;
        case 'newfolder':
            createFolder();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Scan directory
 */
function scanFiles() {
    global $baseDir;

    $result = [
        'name' => basename($baseDir),
        'type' => 'folder',
        'path' => $baseDir,
        'items' => scanDirectory($baseDir),
        'modified' => file_exists($baseDir) ? filemtime($baseDir) : time()
    ];

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

/**
 * Recursive directory scanner
 */
function scanDirectory($dir) {
    $files = [];

    if (!is_dir($dir) || !is_readable($dir)) {
        return $files;
    }

    $items = scandir($dir);
    if ($items === false) {
        return $files;
    }

    foreach ($items as $item) {
        // Skip hidden files and dots
        if ($item[0] === '.') {
            continue;
        }

        $path = $dir . '/' . $item;

        if (is_dir($path)) {
            $files[] = [
                'name' => $item,
                'type' => 'folder',
                'path' => $path,
                'items' => scanDirectory($path),
                'modified' => filemtime($path)
            ];
        } else {
            $files[] = [
                'name' => $item,
                'type' => 'file',
                'path' => $path,
                'size' => filesize($path),
                'modified' => filemtime($path),
                'extension' => strtolower(pathinfo($item, PATHINFO_EXTENSION))
            ];
        }
    }

    // Sort: folders first, then files
    usort($files, function($a, $b) {
        if ($a['type'] !== $b['type']) {
            return $a['type'] === 'folder' ? -1 : 1;
        }
        return strcasecmp($a['name'], $b['name']);
    });

    return $files;
}

/**
 * Upload files
 */
function uploadFiles() {
    global $baseDir, $maxFileSize;

    if (!isset($_FILES['file'])) {
        throw new Exception('No file uploaded');
    }

    $targetDir = isset($_POST['path']) ? $_POST['path'] : $baseDir;

    // Security: ensure target is within base directory
    $realBase = realpath($baseDir);
    $realTarget = realpath($targetDir);

    if ($realTarget === false) {
        // Directory doesn't exist, use base
        $targetDir = $baseDir;
    } elseif ($realBase && strpos($realTarget, $realBase) !== 0) {
        throw new Exception('Invalid upload path');
    }

    // Make sure target directory exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $uploadedFiles = [];
    $files = $_FILES['file'];

    // Handle both single and multiple file uploads
    if (!is_array($files['name'])) {
        $files = [
            'name' => [$files['name']],
            'tmp_name' => [$files['tmp_name']],
            'size' => [$files['size']],
            'error' => [$files['error']]
        ];
    }

    for ($i = 0; $i < count($files['name']); $i++) {
        $fileName = $files['name'][$i];
        $tmpName = $files['tmp_name'][$i];
        $fileSize = $files['size'][$i];
        $error = $files['error'][$i];

        if ($error !== UPLOAD_ERR_OK) {
            continue;
        }

        if ($fileSize > $maxFileSize) {
            throw new Exception("File '$fileName' exceeds maximum size limit");
        }

        // Sanitize filename
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($fileName));

        // Ensure unique filename
        $targetPath = $targetDir . '/' . $fileName;
        $counter = 1;

        while (file_exists($targetPath)) {
            $pathInfo = pathinfo($fileName);
            $ext = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
            $newName = $pathInfo['filename'] . '_' . $counter . $ext;
            $targetPath = $targetDir . '/' . $newName;
            $counter++;
        }

        if (move_uploaded_file($tmpName, $targetPath)) {
            $uploadedFiles[] = [
                'name' => basename($targetPath),
                'path' => $targetPath,
                'size' => $fileSize
            ];
        }
    }

    if (empty($uploadedFiles)) {
        throw new Exception('No files were uploaded successfully');
    }

    echo json_encode([
        'success' => true,
        'message' => count($uploadedFiles) . ' file(s) uploaded successfully',
        'files' => $uploadedFiles
    ]);
}

/**
 * Delete file or folder
 */
function deleteFile() {
    global $baseDir;

    $input = json_decode(file_get_contents('php://input'), true);
    $path = isset($input['path']) ? $input['path'] : '';

    if (empty($path)) {
        throw new Exception('Path is required');
    }

    // Security check
    $realBase = realpath($baseDir);
    $realPath = realpath($path);

    if ($realPath === false) {
        throw new Exception('File or folder not found');
    }

    if ($realPath === $realBase) {
        throw new Exception('Cannot delete root folder');
    }

    if ($realBase && strpos($realPath, $realBase) !== 0) {
        throw new Exception('Invalid path');
    }

    // Delete
    if (is_dir($realPath)) {
        deleteDirectory($realPath);
    } else {
        if (!unlink($realPath)) {
            throw new Exception('Failed to delete file');
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Deleted successfully'
    ]);
}

/**
 * Recursively delete directory
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }

    rmdir($dir);
}

/**
 * Rename file or folder
 */
function renameFile() {
    global $baseDir;

    $input = json_decode(file_get_contents('php://input'), true);
    $oldPath = isset($input['oldPath']) ? $input['oldPath'] : '';
    $newName = isset($input['newName']) ? $input['newName'] : '';

    if (empty($oldPath) || empty($newName)) {
        throw new Exception('Path and new name are required');
    }

    // Sanitize name
    $newName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $newName);

    if (empty($newName)) {
        throw new Exception('Invalid name');
    }

    // Security check
    $realBase = realpath($baseDir);
    $realOldPath = realpath($oldPath);

    if ($realOldPath === false) {
        throw new Exception('File or folder not found');
    }

    if ($realBase && strpos($realOldPath, $realBase) !== 0) {
        throw new Exception('Invalid path');
    }

    // Build new path
    $parentDir = dirname($realOldPath);
    $newPath = $parentDir . '/' . $newName;

    // Check if already exists
    if (file_exists($newPath) && $realOldPath !== $newPath) {
        throw new Exception('A file or folder with this name already exists');
    }

    if (!rename($realOldPath, $newPath)) {
        throw new Exception('Failed to rename');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Renamed successfully',
        'newPath' => str_replace($realBase . '/', $baseDir . '/', $newPath)
    ]);
}

/**
 * Create new folder
 */
function createFolder() {
    global $baseDir;

    $input = json_decode(file_get_contents('php://input'), true);
    $parentPath = isset($input['path']) ? $input['path'] : $baseDir;
    $folderName = isset($input['name']) ? $input['name'] : '';

    if (empty($folderName)) {
        throw new Exception('Folder name is required');
    }

    // Sanitize name
    $folderName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $folderName);

    // Security check
    $realBase = realpath($baseDir);
    $realParent = realpath($parentPath);

    if ($realParent === false) {
        $parentPath = $baseDir;
    } elseif ($realBase && strpos($realParent, $realBase) !== 0) {
        throw new Exception('Invalid path');
    }

    $newFolderPath = $parentPath . '/' . $folderName;

    if (file_exists($newFolderPath)) {
        throw new Exception('Folder already exists');
    }

    if (!mkdir($newFolderPath, 0755, true)) {
        throw new Exception('Failed to create folder');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Folder created successfully',
        'path' => $newFolderPath
    ]);
}
