<?php
session_start();
require_once '../HTML/config.php'; // DB connection

header('Content-Type: application/json');

// Allow GET requests for categories (temporary - remove session check for testing)
// Uncomment below when session is properly set up
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     echo json_encode(['error' => 'Unauthorized']);
//     exit;
// }

// Handle both GET and POST requests
$action = $_GET['action'] ?? '';
$data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
}

switch ($action) {

    // ===== CATEGORIES =====
    case 'get_categories':
        try {
            // Check if categories table exists
            $checkTable = $conn->query("SHOW TABLES LIKE 'categories'");
            if ($checkTable && $checkTable->num_rows > 0) {
                // Try category_name first (most common)
                $sql = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
                $res = $conn->query($sql);
                
                // If that fails, try with just checking what columns exist
                if (!$res) {
                    // Try alternative column name
                    $sql = "SELECT category_id, name AS category_name FROM categories ORDER BY name ASC";
                    $res = $conn->query($sql);
                }
                
                if ($res && $res->num_rows > 0) {
                    $categories = [];
                    while($row=$res->fetch_assoc()) {
                        $categories[] = $row;
                    }
                    echo json_encode($categories);
                } else {
                    echo json_encode([]);
                }
            } else {
                // Table doesn't exist
                echo json_encode([]);
            }
        } catch (Exception $e) {
            // If any error occurs, return empty array
            echo json_encode([]);
        }
        break;

    // ===== PRODUCTS =====
    case 'get_products':
        try {
            // Check if 'rating' column exists first to avoid exceptions on strict mysqli
            $col = $conn->query("SHOW COLUMNS FROM products LIKE 'rating'");
            $hasRating = $col && $col->num_rows > 0;
            if ($col) { $col->close(); }

            // Optional filter: only return items with stock > 0 (for shop)
            $availableOnly = isset($_GET['available_only']) && $_GET['available_only'] == '1';
            $stockCol = $conn->query("SHOW COLUMNS FROM products LIKE 'stock'");
            $hasStockCol = $stockCol && $stockCol->num_rows > 0;
            if ($stockCol) { $stockCol->close(); }

            // Category column on products may no longer exist; detect it first
            $catCol = $conn->query("SHOW COLUMNS FROM products LIKE 'category_id'");
            $hasCategoryIdCol = $catCol && $catCol->num_rows > 0;
            if ($catCol) { $catCol->close(); }

            $selectCategoryId = $hasCategoryIdCol ? 'p.category_id' : 'NULL AS category_id';

            // Check if categories table still exists; if not, skip the JOIN completely
            $catTable = $conn->query("SHOW TABLES LIKE 'categories'");
            $hasCategoriesTable = $catTable && $catTable->num_rows > 0;
            if ($catTable) { $catTable->close(); }

            $selectRating = $hasRating ? ', p.rating' : '';

            if ($hasCategoriesTable) {
                // Normal path when categories table is present
                $sql = "SELECT p.product_id, p.name, p.description, p.price, p.stock,
                               COALESCE(p.image_url, '') AS image_url,
                               COALESCE(c.category_name, 'Uncategorized') AS category_name,
                               " . $selectCategoryId . $selectRating . "
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.category_id
                        " . (($availableOnly && $hasStockCol) ? "WHERE CAST(TRIM(p.stock) AS SIGNED) > 0" : "") . "
                        ORDER BY p.product_id DESC";
            } else {
                // Fallback when categories table has been removed: no JOIN, hardcode category label
                $sql = "SELECT p.product_id, p.name, p.description, p.price, p.stock,
                               COALESCE(p.image_url, '') AS image_url,
                               'Uncategorized' AS category_name,
                               " . $selectCategoryId . $selectRating . "
                        FROM products p
                        " . (($availableOnly && $hasStockCol) ? "WHERE CAST(TRIM(p.stock) AS SIGNED) > 0" : "") . "
                        ORDER BY p.product_id DESC";
            }

            $res = $conn->query($sql);
            if (!$res) {
                echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
                break;
            }

            $products = [];
            while ($row = $res->fetch_assoc()) {
                $row['category_name'] = $row['category_name'] ?? 'Uncategorized';
                $row['image_url'] = $row['image_url'] ?? '';
                if ($hasRating) {
                    if (isset($row['rating']) && $row['rating'] !== null && $row['rating'] !== '') {
                        $r = floatval($row['rating']);
                        $r = max(0, min(5, round($r * 2) / 2));
                        $row['rating'] = $r;
                    } else {
                        $row['rating'] = null;
                    }
                } else {
                    $row['rating'] = null;
                }
                $products[] = $row;
            }
            echo json_encode($products);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error fetching products: ' . $e->getMessage()]);
        }
        break;

    case 'add_product':
        $category_id = !empty($data['category_id']) ? $data['category_id'] : null;
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? 0;
        $stock = $data['stock'] ?? 0;
        $image_url = $data['image_url'] ?? '';
        $rating = isset($data['rating']) && $data['rating'] !== '' ? floatval($data['rating']) : null;
        
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdis", $category_id, $name, $description, $price, $stock, $image_url);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt->close();
            // If rating provided and column exists, update it separately to be schema-safe
            if ($rating !== null) {
                $col = $conn->query("SHOW COLUMNS FROM products LIKE 'rating'");
                if ($col && $col->num_rows > 0) {
                    $rating = max(0, min(5, round($rating * 2) / 2));
                    $upd = $conn->prepare("UPDATE products SET rating=? WHERE product_id=?");
                    $upd->bind_param("di", $rating, $newId);
                    $upd->execute();
                    $upd->close();
                }
            }
            echo json_encode(['success'=>true,'product_id'=>$newId]);
        } else {
            $err = $stmt->error;
            $stmt->close();
            echo json_encode(['error' => 'Failed to add product: ' . $err]);
        }
        break;

    case 'edit_product':
        $product_id = $data['product_id'] ?? 0;
        $category_id = !empty($data['category_id']) ? $data['category_id'] : null;
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? 0;
        $stock = $data['stock'] ?? 0;
        $image_url = $data['image_url'] ?? '';
        $rating = isset($data['rating']) && $data['rating'] !== '' ? floatval($data['rating']) : null;
        
        $stmt = $conn->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image_url=? WHERE product_id=?");
        $stmt->bind_param("issdisi", $category_id, $name, $description, $price, $stock, $image_url, $product_id);
        $ok = $stmt->execute();
        $err = $stmt->error;
        $stmt->close();
        
        if ($ok) {
            // Update rating if provided and column exists
            if ($rating !== null) {
                $col = $conn->query("SHOW COLUMNS FROM products LIKE 'rating'");
                if ($col && $col->num_rows > 0) {
                    $rating = max(0, min(5, round($rating * 2) / 2));
                    $upd = $conn->prepare("UPDATE products SET rating=? WHERE product_id=?");
                    $upd->bind_param("di", $rating, $product_id);
                    $upd->execute();
                    $upd->close();
                }
            }
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['error' => 'Failed to update product: ' . $err]);
        }
        break;

    case 'delete_product':
        $product_id = $data['product_id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['error' => 'Failed to delete product: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    // ===== USERS =====
    case 'get_users':
        try {
            // Try to get username and phone columns, fallback if they don't exist
            $sql = "SELECT user_id, name, COALESCE(username, '') AS username, email, COALESCE(phone, '') AS phone, role, created_at 
                    FROM users 
                    ORDER BY user_id ASC";
            $res = $conn->query($sql);
            if (!$res) {
                // If columns don't exist, try without them
                $sql = "SELECT user_id, name, email, role, created_at 
                        FROM users 
                        ORDER BY user_id ASC";
                $res = $conn->query($sql);
            }
            $users = [];
            if ($res) {
                while($row=$res->fetch_assoc()) {
                    $row['username'] = $row['username'] ?? '';
                    $row['phone'] = $row['phone'] ?? '';
                    $users[] = $row;
                }
            }
            echo json_encode($users);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error fetching users: ' . $e->getMessage()]);
        }
        break;

    case 'add_user':
        try {
            $name = $data['name'] ?? '';
            $username = $data['username'] ?? '';
            $email = $data['email'] ?? '';
            $phone = $data['phone'] ?? '';
            $password = $data['password'] ?? '';
            // Force role to 'user' - admin role is only for hardcoded admin
            $role = 'user';
            
            if (empty($name) || empty($email) || empty($password)) {
                echo json_encode(['error' => 'Name, email, and password are required']);
                break;
            }
            
            // Check if email already exists
            $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            $result = $checkEmail->get_result();
            if ($result->num_rows > 0) {
                echo json_encode(['error' => 'Email already exists']);
                $checkEmail->close();
                break;
            }
            $checkEmail->close();
            
            // Check if username already exists (if username is provided and column exists)
            if (!empty($username)) {
                $checkUsername = $conn->prepare("SELECT username FROM users WHERE username = ?");
                if ($checkUsername) {
                    $checkUsername->bind_param("s", $username);
                    $checkUsername->execute();
                    $result = $checkUsername->get_result();
                    if ($result->num_rows > 0) {
                        echo json_encode(['error' => 'Username already exists']);
                        $checkUsername->close();
                        break;
                    }
                    $checkUsername->close();
                }
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Check which columns exist
            $checkUsername = $conn->query("SHOW COLUMNS FROM users LIKE 'username'");
            $hasUsername = $checkUsername && $checkUsername->num_rows > 0;
            
            $checkPhone = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
            $hasPhone = $checkPhone && $checkPhone->num_rows > 0;
            
            // Build query based on available columns
            if ($hasUsername && $hasPhone) {
                $stmt = $conn->prepare("INSERT INTO users (name, username, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $name, $username, $email, $phone, $password_hash, $role);
            } else if ($hasUsername) {
                $stmt = $conn->prepare("INSERT INTO users (name, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $name, $username, $email, $password_hash, $role);
            } else if ($hasPhone) {
                $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $name, $email, $phone, $password_hash, $role);
            } else {
                $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $password_hash, $role);
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success'=>true,'user_id'=>$stmt->insert_id]);
            } else {
                echo json_encode(['error' => 'Failed to add user: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error adding user: ' . $e->getMessage()]);
        }
        break;

    case 'edit_user':
        try {
            $user_id = $data['user_id'] ?? 0;
            $name = $data['name'] ?? '';
            $username = $data['username'] ?? '';
            $email = $data['email'] ?? '';
            $phone = $data['phone'] ?? '';
            $password = $data['password'] ?? '';
            // Force role to 'user' - admin role is only for hardcoded admin
            // Get current role from database to prevent changing it
            $getCurrentUser = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
            $getCurrentUser->bind_param("i", $user_id);
            $getCurrentUser->execute();
            $currentUser = $getCurrentUser->get_result()->fetch_assoc();
            $getCurrentUser->close();
            // Keep existing role (should always be 'user', but preserve it just in case)
            $role = $currentUser['role'] ?? 'user';
            // Prevent changing to admin role
            if ($role === 'admin') {
                // Don't allow editing the hardcoded admin through this interface
                echo json_encode(['error' => 'Cannot modify admin account through this interface']);
                break;
            }
            
            if (empty($name) || empty($email)) {
                echo json_encode(['error' => 'Name and email are required']);
                break;
            }
            
            // Check if email is taken by another user
            $checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $checkEmail->bind_param("si", $email, $user_id);
            $checkEmail->execute();
            $result = $checkEmail->get_result();
            if ($result->num_rows > 0) {
                echo json_encode(['error' => 'Email already taken by another user']);
                $checkEmail->close();
                break;
            }
            $checkEmail->close();
            
            // Check if username is taken by another user (if username is provided and column exists)
            if (!empty($username)) {
                $checkUsername = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
                if ($checkUsername) {
                    $checkUsername->bind_param("si", $username, $user_id);
                    $checkUsername->execute();
                    $result = $checkUsername->get_result();
                    if ($result->num_rows > 0) {
                        echo json_encode(['error' => 'Username already taken by another user']);
                        $checkUsername->close();
                        break;
                    }
                    $checkUsername->close();
                }
            }
            
            // Check which columns exist
            $checkUsername = $conn->query("SHOW COLUMNS FROM users LIKE 'username'");
            $hasUsername = $checkUsername && $checkUsername->num_rows > 0;
            
            $checkPhone = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
            $hasPhone = $checkPhone && $checkPhone->num_rows > 0;
            
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                // Update with password
                if ($hasUsername && $hasPhone) {
                    $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, phone=?, role=?, password_hash=? WHERE user_id=?");
                    $stmt->bind_param("ssssssi", $name, $username, $email, $phone, $role, $password_hash, $user_id);
                } else if ($hasUsername) {
                    $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, role=?, password_hash=? WHERE user_id=?");
                    $stmt->bind_param("sssssi", $name, $username, $email, $role, $password_hash, $user_id);
                } else if ($hasPhone) {
                    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, role=?, password_hash=? WHERE user_id=?");
                    $stmt->bind_param("sssssi", $name, $email, $phone, $role, $password_hash, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=?, password_hash=? WHERE user_id=?");
                    $stmt->bind_param("ssssi", $name, $email, $role, $password_hash, $user_id);
                }
            } else {
                // Update without password
                if ($hasUsername && $hasPhone) {
                    $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, phone=?, role=? WHERE user_id=?");
                    $stmt->bind_param("sssssi", $name, $username, $email, $phone, $role, $user_id);
                } else if ($hasUsername) {
                    $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, role=? WHERE user_id=?");
                    $stmt->bind_param("ssssi", $name, $username, $email, $role, $user_id);
                } else if ($hasPhone) {
                    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, role=? WHERE user_id=?");
                    $stmt->bind_param("ssssi", $name, $email, $phone, $role, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE user_id=?");
                    $stmt->bind_param("sssi", $name, $email, $role, $user_id);
                }
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success'=>true]);
            } else {
                echo json_encode(['error' => 'Failed to update user: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error updating user: ' . $e->getMessage()]);
        }
        break;

    case 'delete_user':
        try {
            $user_id = $data['user_id'] ?? 0;
            
            // Check if user is admin - prevent deletion
            $checkUser = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
            $checkUser->bind_param("i", $user_id);
            $checkUser->execute();
            $user = $checkUser->get_result()->fetch_assoc();
            $checkUser->close();
            
            if ($user && $user['role'] === 'admin') {
                echo json_encode(['error' => 'Cannot delete admin account']);
                break;
            }
            
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                echo json_encode(['success'=>true]);
            } else {
                echo json_encode(['error' => 'Failed to delete user: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error deleting user: ' . $e->getMessage()]);
        }
        break;

    // ===== ORDERS =====
    case 'create_order':
        try {
            // Accept user_id from session or (fallback) request body to survive cookie issues during redirect
            $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : intval($data['user_id'] ?? 0);
            if (!$user_id) { echo json_encode(['error'=>'Not logged in']); break; }
            $items = $data['items'] ?? [];
            $total = floatval($data['total'] ?? 0);
            // Create tables if not exist
            $conn->query("CREATE TABLE IF NOT EXISTS orders (
                order_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                date DATETIME DEFAULT CURRENT_TIMESTAMP,
                total DECIMAL(10,2) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'to_pay'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $conn->query("CREATE TABLE IF NOT EXISTS order_items (
                item_id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                quantity INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                image_url VARCHAR(255) DEFAULT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            // Insert order (schema-safe: handle existing tables missing 'total' or 'status')
            $conn->begin_transaction();
            $hasTotal = $conn->query("SHOW COLUMNS FROM orders LIKE 'total'");
            $hasStatus = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
            $existsTotal = $hasTotal && $hasTotal->num_rows > 0; if ($hasTotal) { $hasTotal->close(); }
            $existsStatus = $hasStatus && $hasStatus->num_rows > 0; if ($hasStatus) { $hasStatus->close(); }

            if ($existsTotal && $existsStatus) {
                $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?,?, 'to_pay')");
                $stmt->bind_param("id", $user_id, $total);
            } elseif ($existsTotal && !$existsStatus) {
                $stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?,?)");
                $stmt->bind_param("id", $user_id, $total);
            } elseif (!$existsTotal && $existsStatus) {
                $stmt = $conn->prepare("INSERT INTO orders (user_id, status) VALUES (?, 'to_pay')");
                $stmt->bind_param("i", $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO orders (user_id) VALUES (?)");
                $stmt->bind_param("i", $user_id);
            }

            if (!$stmt->execute()) { $conn->rollback(); echo json_encode(['error'=>'Failed to create order: '.$stmt->error]); $stmt->close(); break; }
            $order_id = $stmt->insert_id; $stmt->close();
            // Insert items
            if (is_array($items)) {
                $it = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price, image_url) VALUES (?,?,?,?,?)");
                foreach ($items as $itRow) {
                    $name = (string)($itRow['name'] ?? '');
                    $qty = intval($itRow['quantity'] ?? 1);
                    $price = floatval($itRow['price'] ?? 0);
                    $img = (string)($itRow['image'] ?? '');
                    $it->bind_param("isids", $order_id, $name, $qty, $price, $img);
                    if (!$it->execute()) { $it->close(); $conn->rollback(); echo json_encode(['error'=>'Failed to insert order item']); break 2; }
                }
                $it->close();
            }

            // Deduct stock atomically if products table exists
            $tblExists = $conn->query("SHOW TABLES LIKE 'products'");
            if ($tblExists && $tblExists->num_rows > 0 && is_array($items) && count($items)>0) {
                // Detect columns
                $cols = $conn->query("SHOW COLUMNS FROM products");
                $nameCol = 'name'; $stockCol = 'stock'; $idCol = 'id';
                if ($cols){
                    $hasName=false; $hasProdName=false; $hasId=false; $hasProdId=false; $hasStock=false;
                    while($c=$cols->fetch_assoc()){
                        $f = strtolower($c['Field']);
                        if ($f==='name') $hasName=true;
                        if ($f==='product_name') $hasProdName=true;
                        if ($f==='id') $hasId=true;
                        if ($f==='product_id') $hasProdId=true;
                        if ($f==='stock') $hasStock=true;
                    }
                    $cols->close();
                    if ($hasProdName && !$hasName) $nameCol = 'product_name';
                    if ($hasProdId && !$hasId) $idCol = 'product_id';
                    if (!$hasStock) { /* no stock column, skip deduction */ }
                }
                // Aggregate quantities by product name
                $need = [];
                foreach ($items as $itRow){
                    $n = (string)($itRow['name'] ?? '');
                    $q = intval($itRow['quantity'] ?? 1);
                    if ($n!==''){ $need[$n] = ($need[$n] ?? 0) + max(1,$q); }
                }
                // Check and deduct per product
                $insufficient = [];
                foreach ($need as $n=>$q){
                    if ($stockCol !== 'stock') { continue; } // stock not present
                    // Lock row
                    $sel = $conn->prepare("SELECT $idCol, $stockCol FROM products WHERE $nameCol=? LIMIT 1 FOR UPDATE");
                    if (!$sel){ continue; }
                    $sel->bind_param("s", $n);
                    $sel->execute();
                    $res = $sel->get_result();
                    $row = $res? $res->fetch_assoc(): null; $sel->close();
                    if (!$row){ continue; } // product not found -> skip deduction
                    $pid = intval($row[$idCol] ?? 0);
                    $stock = intval($row[$stockCol] ?? 0);
                    if ($stock < $q){ $insufficient[] = ['product'=>$n,'needed'=>$q,'stock'=>$stock]; }
                    else {
                        $upd = $conn->prepare("UPDATE products SET $stockCol = $stockCol - ? WHERE $idCol = ?");
                        if ($upd){ $upd->bind_param("ii", $q, $pid); $okU = $upd->execute(); $upd->close(); if (!$okU){ $insufficient[] = ['product'=>$n,'needed'=>$q,'stock'=>$stock,'error'=>'update_failed']; } }
                    }
                }
                if (count($insufficient)>0){ $conn->rollback(); echo json_encode(['error'=>'insufficient_stock','details'=>$insufficient]); break; }
            }

            $conn->commit();
            echo json_encode(['success'=>true,'order_id'=>$order_id]);
        } catch (Exception $e) { echo json_encode(['error'=>'Error creating order: '.$e->getMessage()]); }
        break;

    case 'get_orders':
        try {
            // Create tables if not exist (safe)
            $conn->query("CREATE TABLE IF NOT EXISTS orders (
                order_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                date DATETIME DEFAULT CURRENT_TIMESTAMP,
                total DECIMAL(10,2) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'to_pay'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $conn->query("CREATE TABLE IF NOT EXISTS order_items (
                item_id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                quantity INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                image_url VARCHAR(255) DEFAULT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            // Figure out which timestamp column exists: 'date' or 'created_at'
            $hasDate = $conn->query("SHOW COLUMNS FROM orders LIKE 'date'");
            $hasCreated = $conn->query("SHOW COLUMNS FROM orders LIKE 'created_at'");
            $dateExpr = 'o.date'; // default
            if (!($hasDate && $hasDate->num_rows > 0)) {
                $dateExpr = ($hasCreated && $hasCreated->num_rows > 0) ? 'o.created_at' : 'NULL';
            }
            if ($hasDate) { $hasDate->close(); }
            if ($hasCreated) { $hasCreated->close(); }

            // If orders.total doesn't exist, compute from order_items instead
            $hasTotal = $conn->query("SHOW COLUMNS FROM orders LIKE 'total'");
            $totalExpr = ($hasTotal && $hasTotal->num_rows > 0)
                ? 'o.total'
                : '(SELECT COALESCE(SUM(oi.quantity * oi.price),0) FROM order_items oi WHERE oi.order_id = o.order_id)';
            if ($hasTotal) { $hasTotal->close(); }

            // Ensure we always return a status; if column missing, default to 'to_pay'
            $hasStatus = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
            $statusExpr = ($hasStatus && $hasStatus->num_rows > 0) ? 'o.status' : "'to_pay'";
            if ($hasStatus) { $hasStatus->close(); }
            // Normalized status expression for SELECT (treat NULL/empty as 'to_pay')
            $statusRaw = "TRIM(COALESCE($statusExpr,''))";
            $statusLc = "LOWER($statusRaw)";
            $statusNormExpr = "CASE
                WHEN $statusRaw = '' THEN 'to_pay'
                WHEN $statusLc IN ('processing') THEN 'to_receive'
                WHEN $statusLc IN ('delivered') THEN 'completed'
                WHEN $statusLc IN ('shipped') THEN 'to_ship'
                WHEN REPLACE($statusLc,' ','_') IN ('to_ship','toship') OR $statusLc IN ('ship','shipping') THEN 'to_ship'
                WHEN REPLACE($statusLc,' ','_') IN ('to_pay','topay') THEN 'to_pay'
                WHEN $statusLc IN ('completed','complete') THEN 'completed'
                WHEN $statusLc IN ('cancelled','canceled','cancel') THEN 'cancelled'
                ELSE REPLACE($statusLc,' ','_')
            END";

            // Determine user column (user_id or customer_id)
            $hasUserId = $conn->query("SHOW COLUMNS FROM orders LIKE 'user_id'");
            $hasCustomerId = $conn->query("SHOW COLUMNS FROM orders LIKE 'customer_id'");
            $userCol = 'o.user_id';
            if (!($hasUserId && $hasUserId->num_rows > 0) && ($hasCustomerId && $hasCustomerId->num_rows > 0)) {
                $userCol = 'o.customer_id';
            }
            if ($hasUserId) { $hasUserId->close(); }
            if ($hasCustomerId) { $hasCustomerId->close(); }

            // Ensure user_addresses table exists so we can join default address (safe no-op if already there)
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

            // Optional filters: user_id and status
            $wheres = [];
            if (isset($_GET['user_id']) && $_GET['user_id'] !== '') {
                $uid = intval($_GET['user_id']);
                $wheres[] = "$userCol = $uid";
            }
            if (isset($_GET['status']) && $_GET['status'] !== '') {
                $st = strtolower(trim($_GET['status']));
                $allowed = ['to_pay','to_ship','to_receive','completed','cancelled'];
                if (in_array($st, $allowed, true)) {
                    $wheres[] = "$statusNormExpr = '" . $conn->real_escape_string($st) . "'";
                }
            }
            $whereSql = count($wheres) ? ('WHERE ' . implode(' AND ', $wheres)) : '';

            $sql = "SELECT o.order_id,
                           $userCol AS user_id,
                           $dateExpr AS date,
                           $totalExpr AS total,
                           $statusNormExpr AS status,
                           COALESCE(u.name,'User') AS customer,
                           ua.address_line,
                           ua.barangay,
                           ua.city,
                           ua.province,
                           ua.postal_code
                    FROM orders o
                    LEFT JOIN users u ON u.user_id = ($userCol)
                    LEFT JOIN user_addresses ua ON ua.user_id = ($userCol) AND ua.is_default = 1
                    $whereSql
                    ORDER BY o.order_id DESC";
            $res = $conn->query($sql);
            if (!$res) { echo json_encode(['error'=>'DB error: '.$conn->error]); break; }
            $orders = [];
            while($row=$res->fetch_assoc()) { $row['total']=floatval($row['total']); $orders[]=$row; }
            // Load items per order
            if (!empty($orders)){
                $ids = implode(',', array_map('intval', array_column($orders,'order_id')));
                $itemsRes = $conn->query("SELECT order_id, product_name, quantity, price, image_url FROM order_items WHERE order_id IN ($ids)");
                $byOrder = [];
                while($r=$itemsRes->fetch_assoc()){ $byOrder[$r['order_id']][] = $r; }
                foreach($orders as &$o){ $o['items'] = $byOrder[$o['order_id']] ?? []; }
            }
            echo json_encode($orders);
        } catch (Exception $e) { echo json_encode(['error'=>'Error fetching orders: '.$e->getMessage()]); }
        break;

    case 'update_order_status':
        try {
            $order_id = intval($data['order_id'] ?? 0);
            $status = $data['status'] ?? '';
            $allowed = ['to_pay','to_ship','to_receive','completed','cancelled'];
            if (!$order_id || !in_array($status,$allowed)) { echo json_encode(['error'=>'Invalid input']); break; }
            // Ensure status column exists; add if missing
            $hasStatus = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
            if (!($hasStatus && $hasStatus->num_rows > 0)) {
                $conn->query("ALTER TABLE orders ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'to_pay'");
            }
            if ($hasStatus) { $hasStatus->close(); }
            // Check order exists first
            $check = $conn->prepare("SELECT status FROM orders WHERE order_id=?");
            $check->bind_param("i", $order_id);
            $check->execute();
            $existsRes = $check->get_result();
            $existsRow = $existsRes ? $existsRes->fetch_assoc() : null;
            $check->close();
            if (!$existsRow) { echo json_encode(['error'=>'Order not found','order_id'=>$order_id]); break; }
            $prevStatusRaw = isset($existsRow['status']) ? strtolower(trim((string)$existsRow['status'])) : '';

            // Determine target DB value for status (handle ENUM variants like 'To Ship')
            $target = $status;
            $enumOpts = [];
            $colInfo = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
            if ($colInfo && $colInfo->num_rows > 0){
                $info = $colInfo->fetch_assoc();
                $colInfo->close();
                if (isset($info['Type']) && stripos($info['Type'], 'enum(') === 0){
                    // Extract enum options
                    if (preg_match("/enum\((.*)\)/i", $info['Type'], $m)){
                        $opts = array_map(function($s){ return trim($s, "'\" "); }, explode(',', $m[1]));
                        $enumOpts = $opts;
                        $norm = function($s){ $s = strtolower(trim($s)); $s = preg_replace('/[^a-z]/','_', $s); $s = preg_replace('/_+/', '_', $s); return $s; };
                        $want = $norm($status);
                        foreach($opts as $opt){ if ($norm($opt) === $want){ $target = $opt; break; } }
                        // If no exact match, try fuzzy by keyword
                        if ($target === $status){
                            $kw = [
                                'to_pay' => 'pay',
                                'to_ship' => 'ship',
                                'to_receive' => 'receive',
                                'completed' => 'complete',
                                'cancelled' => 'cancel'
                            ][$status] ?? '';
                            if ($kw){
                                foreach($opts as $opt){ if (stripos($opt, $kw)!==false){ $target = $opt; break; } }
                            }
                            // Special mappings to align with your ENUMs
                            if ($status === 'to_receive'){
                                // Prefer 'processing' if available (To Receive stage)
                                foreach($opts as $opt){ if (strcasecmp($opt, 'processing')===0){ $target = $opt; break; } }
                                // If still unchanged and 'received' exists, use it; DO NOT auto-pick 'delivered'
                                if ($target === $status){
                                    foreach($opts as $opt){ if (strcasecmp($opt, 'received')===0){ $target = $opt; break; } }
                                }
                            } elseif ($status === 'completed') {
                                // Prefer 'delivered' if available for Completed stage
                                foreach($opts as $opt){ if (strcasecmp($opt, 'delivered')===0){ $target = $opt; break; } }
                            }
                        }
                    }
                }
            }

            // Perform update and verify write
            $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
            $stmt->bind_param("si", $target, $order_id);
            $ok = $stmt->execute();
            $affected = $stmt->affected_rows; // may be 0 if same value
            $err = $stmt->error;
            $stmt->close();

            // Sync with delivery table: upsert delivery status for this order
            try {
                // Check if a delivery row already exists
                $checkDel = $conn->prepare("SELECT delivery_id FROM delivery WHERE order_id = ? LIMIT 1");
                if ($checkDel) {
                    $checkDel->bind_param("i", $order_id);
                    $checkDel->execute();
                    $resDel = $checkDel->get_result();
                    $rowDel = $resDel ? $resDel->fetch_assoc() : null;
                    $checkDel->close();

                    $now = date('Y-m-d H:i:s');
                    // Map internal status to friendly delivery text
                    $apiStatus = strtoupper($status);
                    if ($status === 'to_receive') {
                        $apiStatus = 'ORDER IS ON THE WAY';
                    } elseif ($status === 'completed') {
                        $apiStatus = 'ORDER HAS BEEN DELIVERED';
                    }

                    if ($rowDel) {
                        // Update existing delivery row
                        $updDel = $conn->prepare("UPDATE delivery SET status = ?, delivery_date = ? WHERE delivery_id = ?");
                        if ($updDel) {
                            $delId = intval($rowDel['delivery_id']);
                            $updDel->bind_param("ssi", $apiStatus, $now, $delId);
                            $updDel->execute();
                            $updDel->close();
                        }
                    } else {
                        // Insert new delivery row with minimal data
                        $insDel = $conn->prepare("INSERT INTO delivery (order_id, status, delivery_date) VALUES (?, ?, ?)");
                        if ($insDel) {
                            $insDel->bind_param("iss", $order_id, $apiStatus, $now);
                            $insDel->execute();
                            $insDel->close();
                        }
                    }
                }
            } catch (Exception $de) {
                // Fail silently for delivery sync so main status update still works
            }

            // Re-read current status
            $check2 = $conn->prepare("SELECT status FROM orders WHERE order_id=?");
            $check2->bind_param("i", $order_id);
            $check2->execute();
            $res2 = $check2->get_result();
            $row2 = $res2 ? $res2->fetch_assoc() : null;
            $check2->close();
            $current = $row2 && isset($row2['status']) ? $row2['status'] : null;

            // If already equal (idempotent), continue to unified success path (allows restock on cancel)
            $alreadyEqual = (is_string($current) && strtolower(trim($current)) === strtolower($target));
            if ($alreadyEqual) {
                // fall through to final verification block to trigger restock logic
            }

            // If not equal and first update didn't take effect, try common variants (for ENUM schemas)
            if ($ok && $affected === 0) {
                $variants = [$target, strtoupper(str_replace('_',' ', $status)), strtolower(str_replace('_',' ', $status)), ucwords(str_replace('_',' ', $status))];
                if ($status === 'to_receive') { array_unshift($variants, 'processing', 'received'); }
                if ($status === 'completed') { array_unshift($variants, 'delivered', 'complete', 'completed', 'done'); }
                $did = false; $lastErr = '';
                foreach ($variants as $v) {
                    $u = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
                    if ($u){
                        $u->bind_param("si", $v, $order_id);
                        $uok = $u->execute();
                        $lastErr = $u->error; $u->close();
                        // Re-read
                        $c = $conn->prepare("SELECT status FROM orders WHERE order_id=?");
                        $c->bind_param("i", $order_id);
                        $c->execute(); $r = $c->get_result(); $rw = $r? $r->fetch_assoc(): null; $c->close();
                        $cur = $rw && isset($rw['status']) ? $rw['status'] : null;
                        if (is_string($cur) && strtolower(trim($cur)) === strtolower(str_replace(' ','_', $status))) { $did = true; break; }
                        if (is_string($cur) && strtolower(trim($cur)) === strtolower($v)) { $did = true; break; }
                    }
                }
                if ($did) { echo json_encode(['success'=>true,'order_id'=>$order_id,'status'=>$target,'note'=>'Saved via variant']); break; }
                // If still not equal, error out with current value
                echo json_encode(['error'=>'Update failed','order_id'=>$order_id,'current_status'=>$current, 'tried'=>$variants, 'enum_options'=>$enumOpts]);
                break;
            }

            // Final verification: re-read and only succeed if matches target (also handles idempotent case)
            $vchk = $conn->prepare("SELECT status FROM orders WHERE order_id=?");
            $vchk->bind_param("i", $order_id);
            $vchk->execute(); $vres = $vchk->get_result(); $vrow = $vres? $vres->fetch_assoc(): null; $vchk->close();
            $vcur = $vrow && isset($vrow['status']) ? $vrow['status'] : null;
            if (is_string($vcur) && strtolower(trim($vcur)) === strtolower($target)) {
                // If status transitioned to cancelled and wasn't previously cancelled, restock items
                $vcurNorm = strtolower(trim((string)$vcur));
                $prevNorm = strtolower(trim((string)$prevStatusRaw));
                $restockedCount = 0; $unmatched = [];
                if (in_array($vcurNorm, ['cancelled','canceled','cancel'], true) && !in_array($prevNorm, ['cancelled','canceled','cancel'], true)) {
                    // Restock from order_items -> products
                    $tblProd = $conn->query("SHOW TABLES LIKE 'products'");
                    $tblItems = $conn->query("SHOW TABLES LIKE 'order_items'");
                    if ($tblProd && $tblProd->num_rows > 0 && $tblItems && $tblItems->num_rows > 0) {
                        // Detect product columns
                        $cols = $conn->query("SHOW COLUMNS FROM products");
                        $nameCol = 'name'; $stockCol = 'stock'; $idCol = 'id';
                        if ($cols){
                            $hasName=false; $hasProdName=false; $hasId=false; $hasProdId=false; $hasStock=false;
                            while($c=$cols->fetch_assoc()){
                                $f = strtolower($c['Field']);
                                if ($f==='name') $hasName=true;
                                if ($f==='product_name') $hasProdName=true;
                                if ($f==='id') $hasId=true;
                                if ($f==='product_id') $hasProdId=true;
                                if ($f==='stock') $hasStock=true;
                            }
                            $cols->close();
                            if ($hasProdName && !$hasName) $nameCol = 'product_name';
                            if ($hasProdId && !$hasId) $idCol = 'product_id';
                        }
                        // Collect order items and restock
                        $oi = $conn->prepare("SELECT product_name, quantity FROM order_items WHERE order_id=?");
                        if ($oi){
                            $oi->bind_param("i", $order_id);
                            $oi->execute();
                            $rs = $oi->get_result();
                            if ($rs){
                                while ($row = $rs->fetch_assoc()){
                                    $pname = (string)($row['product_name'] ?? '');
                                    $q = max(1, intval($row['quantity'] ?? 0));
                                    if ($pname !== '' && $hasStock){
                                        $upd = $conn->prepare("UPDATE products SET $stockCol = $stockCol + ? WHERE $nameCol = ?");
                                        if ($upd){
                                            $upd->bind_param("is", $q, $pname);
                                            if ($upd->execute() && $upd->affected_rows > 0) { $restockedCount += $q; }
                                            else { $unmatched[] = ['name'=>$pname, 'qty'=>$q]; }
                                            $upd->close();
                                        } else {
                                            $unmatched[] = ['name'=>$pname, 'qty'=>$q, 'err'=>'prepare_failed'];
                                        }
                                    }
                                }
                                $rs->free();
                            }
                            $oi->close();
                        }
                    }
                }
                echo json_encode(['success'=>true,'order_id'=>$order_id,'status'=>$target,'restocked_count'=>$restockedCount,'unmatched'=>$unmatched]);
            } else {
                echo json_encode(['error'=>'Failed to update status (post-verify mismatch)', 'order_id'=>$order_id,'current_status'=>$vcur, 'enum_options'=>$enumOpts]);
            }
        } catch (Exception $e) { echo json_encode(['error'=>'Error updating order: '.$e->getMessage()]); }
        break;

    // ===== PRODUCT RATINGS (from customer reviews) =====
    case 'add_rating':
        try {
            $productName = trim((string)($data['product_name'] ?? ''));
            $stars = isset($data['stars']) ? floatval($data['stars']) : 0;
            $review = trim((string)($data['text'] ?? ''));
            $userId = isset($data['user_id']) ? intval($data['user_id']) : 0;
            $username = trim((string)($data['username'] ?? ''));

            if ($productName === '' || $stars <= 0) {
                echo json_encode(['error' => 'Invalid rating input']);
                break;
            }

            // Create table if not exists (simple, schema-safe)
            $conn->query("CREATE TABLE IF NOT EXISTS product_ratings (
                rating_id INT AUTO_INCREMENT PRIMARY KEY,
                product_name VARCHAR(255) NOT NULL,
                user_id INT NULL,
                username VARCHAR(255) NULL,
                stars TINYINT NOT NULL,
                review TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $stars = max(1, min(5, intval(round($stars))));

            $ins = $conn->prepare("INSERT INTO product_ratings (product_name, user_id, username, stars, review) VALUES (?,?,?,?,?)");
            if ($ins) {
                $ins->bind_param("sisis", $productName, $userId, $username, $stars, $review);
                $ok = $ins->execute();
                $err = $ins->error;
                $ins->close();
                if (!$ok) {
                    echo json_encode(['error' => 'Failed to save rating: '.$err]);
                    break;
                }
            }

            // Compute new average rating for this product
            $avg = 0.0; $count = 0;
            $stmt = $conn->prepare("SELECT AVG(stars) AS avg_rating, COUNT(*) AS cnt FROM product_ratings WHERE product_name = ?");
            if ($stmt) {
                $stmt->bind_param("s", $productName);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $row = $res->fetch_assoc()) {
                    $avg = floatval($row['avg_rating'] ?? 0);
                    $count = intval($row['cnt'] ?? 0);
                }
                $stmt->close();
            }

            // Update products.rating column if it exists and matching product row is found
            if ($avg > 0) {
                $col = $conn->query("SHOW COLUMNS FROM products LIKE 'rating'");
                if ($col && $col->num_rows > 0) {
                    $col->close();
                    $avgNorm = max(0, min(5, round($avg * 2) / 2));
                    // Try by exact name match first
                    $upd = $conn->prepare("UPDATE products SET rating=? WHERE name=?");
                    if ($upd) {
                        $upd->bind_param("ds", $avgNorm, $productName);
                        $upd->execute();
                        $upd->close();
                    }
                } elseif ($col) {
                    $col->close();
                }
            }

            echo json_encode(['success'=>true,'avg_rating'=>$avg,'count'=>$count]);
        } catch (Exception $e) {
            echo json_encode(['error'=>'Error saving rating: '.$e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['error'=>'Invalid action']);
}
