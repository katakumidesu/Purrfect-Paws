<?php
session_start();
require_once '../HTML/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Validate file type
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
    exit;
}

// Validate file size
if ($file['size'] > $maxSize) {
    echo json_encode(['error' => 'File size exceeds 5MB limit.']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = '../HTML/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = time() . '_' . uniqid() . '.' . $fileExtension;
$filePath = $uploadDir . $fileName;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filePath)) {
    // Return relative path from admin/php directory
    echo json_encode([
        'success' => true,
        'image_url' => '../HTML/uploads/' . $fileName,
        'message' => 'Image uploaded successfully'
    ]);
} else {
    echo json_encode(['error' => 'Failed to upload file']);
}

