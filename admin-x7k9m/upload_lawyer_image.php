<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Check if this is a bio image upload
if (!isset($_POST['bio_image'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid upload type']);
    exit;
}

if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image_file'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid image type. Only JPG, PNG, WebP, GIF allowed.']);
    exit;
}

if ($file['size'] > MAX_UPLOAD_SIZE) {
    echo json_encode(['success' => false, 'error' => 'Image too large. Maximum size is 2MB.']);
    exit;
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'bio-' . time() . '-' . uniqid() . '.' . $ext;
$filepath = UPLOAD_DIR . '/' . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $imagePath = '../../wp-content/uploads/lawyers/' . $filename;
    echo json_encode(['success' => true, 'url' => $imagePath]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save image']);
}
