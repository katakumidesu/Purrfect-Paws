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

            $selectRating = $hasRating ? ', p.rating' : '';
            $sql = "SELECT p.product_id, p.name, p.description, p.price, p.stock,
                           COALESCE(p.image_url, '') AS image_url,
                           COALESCE(c.category_name, 'Uncategorized') AS category_name,
                           p.category_id" . $selectRating . "
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    " . (($availableOnly && $hasStockCol) ? "WHERE CAST(TRIM(p.stock) AS SIGNED) > 0" : "") . "
                    ORDER BY p.product_id DESC";

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

            if (!$stmt->execute()) { echo json_encode(['error'=>'Failed to create order: '.$stmt->error]); $stmt->close(); break; }
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
                    $it->execute();
                }
                $it->close();
            }
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

            $userFilter = '';
            if (isset($_GET['user_id']) && $_GET['user_id'] !== '') {
                $uid = intval($_GET['user_id']);
                $userFilter = "WHERE o.user_id = $uid";
            }
            $sql = "SELECT o.order_id, o.user_id, $dateExpr AS date, $totalExpr AS total, $statusExpr AS status, COALESCE(u.name,'User') AS customer
                    FROM orders o LEFT JOIN users u ON u.user_id = o.user_id
                    $userFilter
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
            $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
            $stmt->bind_param("si", $status, $order_id);
            $ok = $stmt->execute();
            $affected = $stmt->affected_rows;
            $err = $stmt->error;
            $stmt->close();
            if ($ok && $affected > 0) {
                echo json_encode(['success'=>true,'order_id'=>$order_id,'status'=>$status]);
            } else if ($ok && $affected === 0) {
                // Fetch current status to report back
                $q = $conn->prepare("SELECT status FROM orders WHERE order_id=?");
                $q->bind_param("i", $order_id);
                $q->execute();
                $res = $q->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $q->close();
                echo json_encode(['error'=>'No rows updated','order_id'=>$order_id,'current_status'=>$row['status'] ?? null]);
            } else {
                echo json_encode(['error'=>'Failed to update status: '.$err]);
            }
        } catch (Exception $e) { echo json_encode(['error'=>'Error updating order: '.$e->getMessage()]); }
        break;


    default:
        echo json_encode(['error'=>'Invalid action']);
}
