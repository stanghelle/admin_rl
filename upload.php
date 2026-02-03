<?php
/**
 * Image Upload Handler
 * Uploads program images and updates the database
 */
require_once 'core/init.php';

// Require authentication for file uploads
Auth::requireApiAuth();

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['imageFile']) || $_FILES['imageFile']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No image received or an error occurred.']);
    exit;
}

// Validate program ID
$userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid program ID']);
    exit;
}

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$fileType = mime_content_type($_FILES['imageFile']['tmp_name']);
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only images allowed.']);
    exit;
}

// Validate file size (max 5MB)
$maxSize = 5 * 1024 * 1024;
if ($_FILES['imageFile']['size'] > $maxSize) {
    echo json_encode(['status' => 'error', 'message' => 'File too large. Maximum 5MB allowed.']);
    exit;
}

$fileTmpPath = $_FILES['imageFile']['tmp_name'];
$originalName = basename($_FILES['imageFile']['name']);
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// Generate secure filename
$fileName = Hash::salt() . '.' . $extension;
$uploadFileDir = '../img/prg/';
$destPath = $uploadFileDir . $fileName;

// Ensure the uploads directory exists
if (!is_dir($uploadFileDir)) {
    mkdir($uploadFileDir, 0755, true);
}

if (move_uploaded_file($fileTmpPath, $destPath)) {
    try {
        $db = DB::getInstance();

        // Update the database with the new image filename
        $sql = "UPDATE program SET img = ? WHERE id = ?";
        $db->query($sql, [$fileName, $userId]);

        if (!$db->error()) {
            $response = [
                'status' => 'success',
                'message' => 'Image updated successfully.',
                'newImagePath' => $destPath,
                'fileName' => $fileName
            ];
        } else {
            // Remove uploaded file if database update fails
            @unlink($destPath);
            $response = ['status' => 'error', 'message' => 'Database update failed.'];
        }
    } catch (Exception $e) {
        @unlink($destPath);
        $response = ['status' => 'error', 'message' => 'Database error.'];
    }
} else {
    $response = ['status' => 'error', 'message' => 'File upload failed.'];
}

echo json_encode($response);
?>
