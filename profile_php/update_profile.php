<?php
session_start();
require_once '../HTML/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = $_POST['gender'] ?? 'Other';
$birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;

// Validate required fields
if (empty($name) || empty($username) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Name, username, and email are required']);
    exit();
}

$newImage = null;
$imageError = null;

if (!empty($_FILES['profile_image']['name'])) {
    $targetDir = "../HTML/uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = uniqid() . "_" . basename($_FILES['profile_image']['name']);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $validTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Check for PHP upload errors first for clearer messages
    if (isset($_FILES['profile_image']['error']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
        switch ($_FILES['profile_image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $imageError = 'File too large. Max size is 5MB.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $imageError = 'Upload was partial. Please try again.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $imageError = 'No file uploaded.';
                break;
            default:
                $imageError = 'Upload failed. Please try again.';
        }
    } elseif (!in_array($imageFileType, $validTypes)) {
        // Validate extension
        $imageError = 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.';
    } elseif (!isset($_FILES['profile_image']['size']) || $_FILES['profile_image']['size'] > $maxSize) {
        $imageError = 'File too large. Max size is 5MB.';
    } else {
        // Optionally validate MIME using finfo for extra safety
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $_FILES['profile_image']['tmp_name']);
                finfo_close($finfo);
                $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp','image/pjpeg'];
                if ($mime && !in_array($mime, $allowedMimes)) {
                    $imageError = 'Unsupported image format.';
                }
            }
        }

        // Move the uploaded file if no error so far
        if (!$imageError) {
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
                $imageError = 'Failed to upload image. Please try again.';
            } else {
                $newImage = $fileName;
            }
        }
    }
}

// Check if image upload had errors (but don't fail the whole update)
if ($imageError && !$newImage) {
    // Image upload failed, but continue with other updates
}

// Build SQL query - check which columns exist
$columns = [];
$params = [];
$types = '';

// Always update these
$columns[] = "name = ?";
$params[] = $name;
$types .= 's';

$columns[] = "username = ?";
$params[] = $username;
$types .= 's';

$columns[] = "email = ?";
$params[] = $email;
$types .= 's';

// Check if phone column exists
$checkPhone = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
if ($checkPhone && $checkPhone->num_rows > 0) {
    $columns[] = "phone = ?";
    $params[] = $phone;
    $types .= 's';
}

// Check if gender column exists
$checkGender = $conn->query("SHOW COLUMNS FROM users LIKE 'gender'");
if ($checkGender && $checkGender->num_rows > 0) {
    $columns[] = "gender = ?";
    $params[] = $gender;
    $types .= 's';
}

// Check if birthdate column exists
$checkBirthdate = $conn->query("SHOW COLUMNS FROM users LIKE 'birthdate'");
if ($checkBirthdate && $checkBirthdate->num_rows > 0) {
    $columns[] = "birthdate = ?";
    $params[] = $birthdate;
    $types .= 's';
}

// Check if profile_image column exists
$checkProfileImage = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
if ($checkProfileImage && $checkProfileImage->num_rows > 0 && $newImage) {
    $columns[] = "profile_image = ?";
    $params[] = $newImage;
    $types .= 's';
}

// Check if updated_at column exists
$checkUpdated = $conn->query("SHOW COLUMNS FROM users LIKE 'updated_at'");
if ($checkUpdated && $checkUpdated->num_rows > 0) {
    $columns[] = "updated_at = NOW()";
}

$sql = "UPDATE users SET " . implode(", ", $columns) . " WHERE user_id = ?";
$params[] = $user_id;
$types .= 'i';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // ✅ Update the session data immediately
    $_SESSION['name'] = $name;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    if ($checkPhone && $checkPhone->num_rows > 0) {
        $_SESSION['phone'] = $phone;
    }
    if ($checkGender && $checkGender->num_rows > 0) {
        $_SESSION['gender'] = $gender;
    }
    if ($checkBirthdate && $checkBirthdate->num_rows > 0) {
        $_SESSION['birthdate'] = $birthdate;
    }
    if ($newImage) {
        $_SESSION['profile_image'] = $newImage;
    }

    $response = [
        'success' => true,
        'message' => 'Profile updated successfully!',
        'new_image' => $newImage ? "../HTML/uploads/$newImage" : null
    ];
    
    if ($imageError && !$newImage) {
        $response['image_warning'] = $imageError;
    }
    
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $stmt->error]);
}

$stmt->close();