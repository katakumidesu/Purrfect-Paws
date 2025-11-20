<?php
session_start();
require_once '../HTML/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register/purdex.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../HTML/gallery.php');
    exit;
}

$userId = intval($_SESSION['user_id']);
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['gallery_message'] = 'Please select an image to upload.';
    header('Location: ../HTML/gallery.php');
    exit;
}

$uploadDir = __DIR__ . '/../HTML/uploads/gallery/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$ext = strtolower($ext);
$allowed = ['jpg','jpeg','png','gif','webp'];
if (!in_array($ext, $allowed, true)) {
    $_SESSION['gallery_message'] = 'Invalid image type. Please upload JPG, PNG, GIF, or WebP.';
    header('Location: ../HTML/gallery.php');
    exit;
}

$filename = 'g_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    $_SESSION['gallery_message'] = 'Failed to save uploaded image.';
    header('Location: ../HTML/gallery.php');
    exit;
}

// Relative path used by the site
$imagePath = 'uploads/gallery/' . $filename;

// Ensure table exists then insert pending row
$conn->query("CREATE TABLE IF NOT EXISTS user_gallery_posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $conn->prepare('INSERT INTO user_gallery_posts (user_id, image_path, description, status) VALUES (?,?,?,\'pending\')');
if ($stmt) {
    $stmt->bind_param('iss', $userId, $imagePath, $description);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        $_SESSION['gallery_message'] = 'Your post was submitted and is pending approval.';
    } else {
        $_SESSION['gallery_message'] = 'Failed to save your post.';
    }
} else {
    $_SESSION['gallery_message'] = 'Failed to prepare gallery insert.';
}

header('Location: ../HTML/gallery.php');
exit;
