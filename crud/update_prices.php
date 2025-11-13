<?php
// One-time batch price update by product name
// Visit this script in a browser once, then delete the file.

declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../HTML/config.php';

$map = [
  'flower cat tree' => 1050,
  '3-in-1 Interactive Cat Toy' => 250,
  'Cat Frog Bed' => 334,
  'Cupcake Cat Tree' => 1120,
  'Mushroom Cat Scratcher' => 600,
  'Hollow Plastic Ball' => 120,
  'Spaceship Litter Box' => 800,
  'Cat Tumbler Ball Toy' => 200,
  'Exercise Wheel For Cat' => 2050,
  'Foldable Cat Carrier Bag' => 900,
  'Cute Mushroom Raised Cat Bowl' => 450,
  'Pink Ceramic Raised Cat Bowl' => 230,
  'Three Tier Flower Cat Tree' => 1115,
  'Banana Cat Bed' => 400,
  'Flower Shaped Cat Bed' => 450,
  'Cute Cartoon Ceramic Cat Bowl' => 400,
  'Cat Carrier' => 600,
  'Cat Litter Box' => 780,
  'Cat Food Bowl' => 440,
  'Wiggly Worm Cat Teaser Wand' => 40,
  'Cat Tree' => 900,
  'cat bed' => 400,
  'Cat Mouse Toy' => 100,
  'Cat Scratch Post' => 500,
];

$results = [];
try {
  // Ensure price column exists
  $col = $conn->query("SHOW COLUMNS FROM products LIKE 'price'");
  if (!($col && $col->num_rows > 0)) {
    echo json_encode(['error' => "products.price column not found"], JSON_PRETTY_PRINT);
    exit;
  }
  if ($col) $col->close();

  // Fetch all products to match by normalized name
  $all = [];
  $rs = $conn->query("SELECT product_id, name FROM products");
  while ($rs && ($row = $rs->fetch_assoc())) {
    $all[] = $row;
  }
  if ($rs) $rs->close();

  // Helper to normalize names (lowercase, remove non-alphanumerics)
  $norm = function(string $s): string {
    $s = trim(mb_strtolower($s));
    $s = preg_replace('/[^a-z0-9]+/u', '', $s);
    return $s ?? '';
  };

  // Index products by normalized name
  $index = [];
  foreach ($all as $row) {
    $index[$norm($row['name'] ?? '')] = (int)$row['product_id'];
  }

  $upd = $conn->prepare("UPDATE products SET price=? WHERE product_id=?");
  if (!$upd) { echo json_encode(['error' => 'Prepare failed: '.$conn->error]); exit; }

  foreach ($map as $name => $price) {
    $target = $norm((string)$name);
    $pid = $index[$target] ?? 0;
    if ($pid > 0) {
      $p = floatval($price);
      $upd->bind_param('di', $p, $pid);
      $ok = $upd->execute();
      $results[] = [
        'name' => $name,
        'matched_product_id' => $pid,
        'price_set' => $p,
        'ok' => $ok,
        'affected_rows' => $upd->affected_rows,
        'error' => $ok ? null : $upd->error,
      ];
    } else {
      $results[] = [
        'name' => $name,
        'matched_product_id' => null,
        'price_set' => (float)$price,
        'ok' => false,
        'affected_rows' => 0,
        'error' => 'No product name matched (check exact DB name)'
      ];
    }
  }
  $upd->close();

  echo json_encode([
    'success' => true,
    'note' => 'Name matching is case-insensitive and ignores spaces/punctuation',
    'products_in_db' => array_map(function($r){ return $r['name']; }, $all),
    'results' => $results
  ], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
  echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
