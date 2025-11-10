<?php
session_start();
header('Content-Type: application/json');

require_once '../HTML/config.php';

// Ensure logged in
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Not authenticated']);
  exit;
}

$user_id = intval($_SESSION['user_id']);
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// Ensure table exists (safe no-op if already there)
$conn->query("CREATE TABLE IF NOT EXISTS user_addresses (
  address_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  fullname VARCHAR(255) DEFAULT '',
  phone VARCHAR(64) DEFAULT '',
  label VARCHAR(32) DEFAULT 'Home',
  address_line VARCHAR(255) DEFAULT '',
  barangay VARCHAR(128) DEFAULT '',
  city VARCHAR(128) DEFAULT '',
  province VARCHAR(128) DEFAULT '',
  postal_code VARCHAR(32) DEFAULT '',
  is_default TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

function clean($s){ return htmlspecialchars(trim((string)$s), ENT_QUOTES, 'UTF-8'); }

if ($action === 'list') {
  $rows = [];
  $sql = "SELECT address_id, fullname, phone, label, address_line, barangay, city, province, postal_code, is_default FROM user_addresses WHERE user_id=? ORDER BY is_default DESC, created_at DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $user_id);
  if ($stmt->execute()) {
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
  }
  echo json_encode($rows);
  exit;
}

if ($action === 'create') {
  $fullname = clean($_POST['fullname'] ?? '');
  $phone = clean($_POST['phone'] ?? '');
  $label = clean($_POST['label'] ?? 'Home');
  $address_line = clean($_POST['address_line'] ?? '');
  $barangay = clean($_POST['barangay'] ?? '');
  $city = clean($_POST['city'] ?? '');
  $province = clean($_POST['province'] ?? '');
  $postal_code = clean($_POST['postal_code'] ?? '');
  $is_default = isset($_POST['is_default']) && $_POST['is_default'] == '1' ? 1 : 0;

  // if setting default, unset others
  if ($is_default) {
    $stmt = $conn->prepare('UPDATE user_addresses SET is_default=0 WHERE user_id=?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
  }

  $stmt = $conn->prepare('INSERT INTO user_addresses (user_id, fullname, phone, label, address_line, barangay, city, province, postal_code, is_default) VALUES (?,?,?,?,?,?,?,?,?,?)');
  $stmt->bind_param('issssssssi', $user_id, $fullname, $phone, $label, $address_line, $barangay, $city, $province, $postal_code, $is_default);
  if (!$stmt->execute()) {
    http_response_code(400);
    echo json_encode(['error' => 'Failed to create address']);
    exit;
  }
  echo json_encode(['ok' => true, 'address_id' => $stmt->insert_id]);
  exit;
}

if ($action === 'update') {
  $address_id = intval($_POST['address_id'] ?? 0);
  if ($address_id <= 0) { echo json_encode(['error' => 'Missing address_id']); exit; }

  $fullname = clean($_POST['fullname'] ?? '');
  $phone = clean($_POST['phone'] ?? '');
  $label = clean($_POST['label'] ?? 'Home');
  $address_line = clean($_POST['address_line'] ?? '');
  $barangay = clean($_POST['barangay'] ?? '');
  $city = clean($_POST['city'] ?? '');
  $province = clean($_POST['province'] ?? '');
  $postal_code = clean($_POST['postal_code'] ?? '');
  $is_default = isset($_POST['is_default']) && $_POST['is_default'] == '1' ? 1 : 0;

  if ($is_default) {
    $stmt = $conn->prepare('UPDATE user_addresses SET is_default=0 WHERE user_id=?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
  }

  $stmt = $conn->prepare('UPDATE user_addresses SET fullname=?, phone=?, label=?, address_line=?, barangay=?, city=?, province=?, postal_code=?, is_default=? WHERE address_id=? AND user_id=?');
  $stmt->bind_param('ssssssssiii', $fullname, $phone, $label, $address_line, $barangay, $city, $province, $postal_code, $is_default, $address_id, $user_id);
  if (!$stmt->execute()) { echo json_encode(['error' => 'Failed to update address']); exit; }
  echo json_encode(['ok' => true]);
  exit;
}

if ($action === 'delete') {
  $address_id = intval($_POST['address_id'] ?? 0);
  if ($address_id <= 0) { echo json_encode(['error' => 'Missing address_id']); exit; }
  $stmt = $conn->prepare('DELETE FROM user_addresses WHERE address_id=? AND user_id=?');
  $stmt->bind_param('ii', $address_id, $user_id);
  if (!$stmt->execute()) { echo json_encode(['error' => 'Failed to delete']); exit; }
  echo json_encode(['ok' => true]);
  exit;
}

if ($action === 'set_default') {
  $address_id = intval($_POST['address_id'] ?? 0);
  if ($address_id <= 0) { echo json_encode(['error' => 'Missing address_id']); exit; }
  $stmt = $conn->prepare('UPDATE user_addresses SET is_default=0 WHERE user_id=?');
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $stmt = $conn->prepare('UPDATE user_addresses SET is_default=1 WHERE address_id=? AND user_id=?');
  $stmt->bind_param('ii', $address_id, $user_id);
  $stmt->execute();
  echo json_encode(['ok' => true]);
  exit;
}

echo json_encode(['error' => 'Unknown action']);
