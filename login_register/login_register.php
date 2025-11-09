<?php
session_start();
require_once '../HTML/config.php'; // ✅ Connects to your Katakumi database

// ===================== REGISTER =====================
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
        header('Location: purdex.php');
        exit();
    }

    // Insert new user into database (✅ includes username + phone)
    $insert = $conn->prepare("INSERT INTO users (name, username, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->bind_param("ssssss", $name, $username, $email, $phone, $password, $role);

    if ($insert->execute()) {
        // ✅ Registration successful — show login form instead of auto-login
        $_SESSION['success_message'] = 'Registration successful! You can now log in.';
        $_SESSION['active_form'] = 'login';
        header('Location: purdex.php');
        exit();
    } else {
        $_SESSION['register_error'] = 'Registration failed. Please try again.';
        $_SESSION['active_form'] = 'register';
        header('Location: purdex.php');
        exit();
    }
}

// ===================== LOGIN =====================
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hardcoded Admin Account
    $adminEmail = 'admin@gmail.com';
    $adminPassword = 'Katakumi123';

    if ($email === $adminEmail && $password === $adminPassword && $role === 'admin') {
        $_SESSION['user_id'] = 0;
        $_SESSION['name'] = 'Administrator';
        $_SESSION['email'] = $adminEmail;
        $_SESSION['role'] = 'admin';
        header('Location: ../admin/admin_page.php');
        exit();
    }

    // Regular user login
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {
            // Store user session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? '';

            if ($user['role'] === 'admin') {
                header('Location: ../admin/admin_page.php');
            } else {
                header('Location: ../HTML/index.php');
            }
            exit();
        } else {
            $_SESSION['login_error'] = 'Incorrect password!';
        }
    } else {
        $_SESSION['login_error'] = 'Email or role not found!';
    }

    $_SESSION['active_form'] = 'login';
    header('Location: purdex.php');
    exit();
}
?>
