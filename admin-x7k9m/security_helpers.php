<?php
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    $filename = preg_replace('/\.{2,}/', '.', $filename);
    return $filename;
}

function validateImageUpload($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'No file uploaded'];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['valid' => false, 'error' => 'File too large'];
    }

    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_EXTENSIONS)) {
        return ['valid' => false, 'error' => 'Invalid file extension'];
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['valid' => false, 'error' => 'File is not a valid image'];
    }

    return ['valid' => true, 'extension' => $ext];
}

function safeDirPath($baseDir, $subPath) {
    $realBase = realpath($baseDir);
    $realPath = realpath($baseDir . '/' . $subPath);
    if ($realPath === false || strpos($realPath, $realBase) !== 0) {
        return false;
    }
    return $realPath;
}
