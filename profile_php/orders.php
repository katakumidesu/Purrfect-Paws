<?php
// profile_php/orders.php
// Returns the logged-in user's orders as JSON for the purchases UI.

declare(strict_types=1);
header('Content-Type: application/json');

require_once __DIR__ . '/../html/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Check that the orders table exists
$tbl = $conn->query("SHOW TABLES LIKE 'orders'");
if (!$tbl || $tbl->num_rows === 0) {
    echo json_encode(['orders' => []]);
    exit;
}

// Columns detection (some installs may miss certain fields)
$cols = [
    'id' => false,
    'order_id' => false,
    'total' => false,
    'total_amount' => false,
    'amount' => false,
    'status' => false,
    'created_at' => false,
    'date' => false,
    'order_date' => false,
    'updated_at' => false,
];
$res = $conn->query("SHOW COLUMNS FROM orders");
if ($res) {
    while ($c = $res->fetch_assoc()) {
        $name = strtolower($c['Field']);
        if (array_key_exists($name, $cols)) $cols[$name] = true;
    }
    $res->close();
}

$idCol = $cols['id'] ? 'id' : ($cols['order_id'] ? 'order_id' : 'id');
// Prefer total, then total_amount, then amount
if ($cols['total']) {
    $totalCol = 'total';
} elseif ($cols['total_amount']) {
    $totalCol = 'total_amount';
} elseif ($cols['amount']) {
    $totalCol = 'amount';
} else {
    $totalCol = 'NULL as total';
}
$statusCol = $cols['status'] ? 'status' : "'to_pay' as status";
$createdColExpr = $cols['created_at'] ? 'created_at' : ($cols['date'] ? 'date' : ($cols['order_date'] ? 'order_date' : 'NOW()'));
$updatedColExpr = $cols['updated_at'] ? 'updated_at' : 'NOW()';

// Fetch orders for the current user. Always alias to consistent keys.
$sql = "SELECT $idCol AS id, $totalCol AS total, $statusCol AS status, $createdColExpr AS created_at, $updatedColExpr AS updated_at FROM orders WHERE user_id = ? ORDER BY $idCol DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['orders' => []]);
    exit;
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$rs = $stmt->get_result();
$orders = [];
while ($row = $rs->fetch_assoc()) {
    $orders[] = [
        'id' => (int)$row['id'],
        'total' => isset($row['total']) ? (float)$row['total'] : 0.0,
        'status' => strtolower((string)$row['status']),
        'created_at' => (string)$row['created_at'],
        'updated_at' => (string)$row['updated_at'],
        'items' => [], // optional: can be filled by joining order_items if exists
    ];
}
$stmt->close();

// Optional: if order_items table exists, attach items per order
$oiExists = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($oiExists && $oiExists->num_rows > 0 && count($orders) > 0) {
    $ids = implode(',', array_map('intval', array_column($orders, 'id')));
    $oiCols = $conn->query("SHOW COLUMNS FROM order_items");
    $hasName=false; $hasQty=false; $hasPrice=false; $hasImage=false; $hasOrderId=false;
    if ($oiCols) {
        while ($c = $oiCols->fetch_assoc()) {
            $f = strtolower($c['Field']);
            if ($f==='name') $hasName=true;
            if ($f==='quantity' || $f==='qty') $hasQty=true;
            if ($f==='price') $hasPrice=true;
            if ($f==='image' || $f==='image_url') $hasImage=true;
            if ($f==='order_id') $hasOrderId=true;
        }
        $oiCols->close();
    }
    if ($hasOrderId) {
        $qtyCol = $hasQty ? 'quantity' : '1 as quantity';
        $nameCol = $hasName ? 'name' : "'' as name";
        $priceCol = $hasPrice ? 'price' : '0 as price';
        $imageCol = $hasImage ? 'image' : "'' as image";
        $oiSql = "SELECT order_id, $nameCol, $qtyCol, $priceCol, $imageCol FROM order_items WHERE order_id IN ($ids)";
        $oiRes = $conn->query($oiSql);
        $byOrder = [];
        while ($it = $oiRes && $oiRes->fetch_assoc() ? $oiRes->fetch_assoc() : null) { /* no-op: we need while separately */ }
        if ($oiRes) {
            $oiRes->data_seek(0);
            while ($it = $oiRes->fetch_assoc()) {
                $oid = (int)$it['order_id'];
                $byOrder[$oid] = $byOrder[$oid] ?? [];
                $byOrder[$oid][] = [
                    'name' => (string)$it['name'],
                    'quantity' => (int)($it['quantity'] ?? 1),
                    'price' => (float)($it['price'] ?? 0),
                    'image' => (string)($it['image'] ?? ''),
                ];
            }
            $oiRes->close();
        }
        foreach ($orders as &$o) {
            $o['items'] = $byOrder[$o['id']] ?? [];
        }
        unset($o);
    }
}

// Normalize statuses to frontend expected keys
foreach ($orders as &$o) {
    $st = strtolower($o['status']);
    if ($st === 'to_ship' || $st === 'shipping' || $st === 'shipped') {
        $o['status'] = 'to_ship';
    } elseif ($st === 'to_receive' || $st === 'delivered') {
        $o['status'] = 'to_receive';
    } elseif ($st === 'completed' || $st === 'success') {
        $o['status'] = 'completed';
    } elseif ($st === 'cancelled' || $st === 'canceled') {
        $o['status'] = 'cancelled';
    } else {
        $o['status'] = 'to_pay';
    }
}
unset($o);

echo json_encode(['orders' => $orders]);
