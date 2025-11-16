<?php
// Simple upload handler for GCash payment proofs
session_start();
header('Content-Type: application/json');

// Optional: require login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

if (!isset($_FILES['proof']) || !is_array($_FILES['proof'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['proof'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload error code: ' . $file['error']]);
    exit;
}

// Basic validation
$allowed = ['jpg','jpeg','png','webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) { // 5MB
    http_response_code(400);
    echo json_encode(['error' => 'File too large']);
    exit;
}

$baseDir = __DIR__ . '/uploads/payments';
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0777, true);
}

$filename = 'pay_' . uniqid() . '.' . $ext;
$targetPath = $baseDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
    exit;
}

// Path to store in DB (relative to HTML/ root)
$relativePath = 'uploads/payments/' . $filename;

echo json_encode(['success' => true, 'path' => $relativePath]);
