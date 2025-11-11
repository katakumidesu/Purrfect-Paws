<?php
// crud/update_order_status.php
// JSON API to update an order's status (e.g., TO_PAY -> TO_SHIP)
// Expected POST params: order_id (int), action (string: to_ship | cancel | custom)
// Returns JSON { success: bool, message: string }

declare(strict_types=1);

header('Content-Type: application/json');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/../html/config.php'; // provides $conn (MySQLi) and session

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function respond($ok, $msg, $extra = []) {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

// Basic auth check: require admin. Adjust to your auth scheme.
$isAdmin = isset($_SESSION['is_admin']) ? (bool)$_SESSION['is_admin'] : false;
if (!$isAdmin) {
    // If you use roles, check them here.
    respond(false, 'Unauthorized');
}

// Validate inputs
$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$action  = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($orderId <= 0 || $action === '') {
    respond(false, 'Missing required parameters');
}

// Map actions to target statuses
$allowedActions = [
    'to_ship' => 'TO_SHIP',
    'cancel'  => 'CANCELLED',
    // Add more actions as needed: 'complete' => 'COMPLETED', etc.
];

if (!isset($allowedActions[$action])) {
    respond(false, 'Invalid action');
}

$targetStatus = $allowedActions[$action];

// Define allowed transitions. Key: current_status, Value: array of allowed next statuses
$transitions = [
    'TO_PAY'    => ['TO_SHIP', 'CANCELLED'],
    'TO_SHIP'   => ['TO_RECEIVE', 'CANCELLED'],
    'TO_RECEIVE'=> ['COMPLETED'],
    'COMPLETED' => [],
    'CANCELLED' => [],
];

// Fetch current order status and ownership for safety
$sql = 'SELECT id, status FROM orders WHERE id = ? LIMIT 1';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    respond(false, 'Failed to prepare statement');
}
$stmt->bind_param('i', $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$order) {
    respond(false, 'Order not found');
}

$currentStatus = strtoupper((string)($order['status'] ?? ''));

if (!isset($transitions[$currentStatus])) {
    respond(false, 'Invalid current status');
}

if (!in_array($targetStatus, $transitions[$currentStatus], true)) {
    respond(false, 'Transition not allowed from ' . $currentStatus . ' to ' . $targetStatus);
}

// Perform the update with optional timestamps
$now = date('Y-m-d H:i:s');

$setCols = 'status = ?, updated_at = ?';
$params = [$targetStatus, $now, $orderId];
$types  = 'ssi';

// If moving to TO_SHIP, set approved_at if column exists. We'll attempt optional column update.
$includeApprovedAt = false;
$columnsRes = $conn->query("SHOW COLUMNS FROM orders LIKE 'approved_at'");
if ($columnsRes && $columnsRes->num_rows > 0 && $targetStatus === 'TO_SHIP') {
    $setCols .= ', approved_at = ?';
    $params = [$targetStatus, $now, $now, $orderId];
    $types  = 'sssi';
    $includeApprovedAt = true;
}

$updateSql = "UPDATE orders SET $setCols WHERE id = ?";
$updateStmt = $conn->prepare($updateSql);
if (!$updateStmt) {
    respond(false, 'Failed to prepare update');
}
$updateStmt->bind_param($types, ...$params);
$ok = $updateStmt->execute();
$aff = $updateStmt->affected_rows;
$updateStmt->close();

if (!$ok || $aff <= 0) {
    respond(false, 'No changes made');
}

respond(true, 'Order updated', [
    'order_id' => $orderId,
    'from' => $currentStatus,
    'to' => $targetStatus,
    'approved_at_set' => $includeApprovedAt,
]);
