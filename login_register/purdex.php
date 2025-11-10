<?php
session_start();

$activeForm = $_SESSION['active_form'] ?? 'login';
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$successMessage = $_SESSION['success_message'] ?? '';

unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['success_message'], $_SESSION['active_form']);

function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function showSuccess($msg) {
    return !empty($msg) ? "<p class='success-message'>$msg</p>" : '';
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register | Purrfect Paws</title>
    <link rel="stylesheet" href="../HTML/css/login_style.css">
</head>
<body>
    <div class="container">
        <!-- LOGIN FORM -->
        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="login_register.php" method="post">
                <h2>Login</h2>
                <?= showError($errors['login']); ?>
                <?= showSuccess($successMessage); ?>
                
                <input type="email" name="email" class="email" placeholder="Email" required>
                <input type="password" name="password" class="password" placeholder="Password" required>
                <button type="submit" name="login" class="login">Login</button>
                <p>Don't have an Account? <a href="#" onclick="showForm('register-form')">Register</a></p>
            </form>
        </div>

        <!-- REGISTER FORM -->
        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
            <form action="login_register.php" method="post">
                <h2>Register</h2>
                <?= showError($errors['register']); ?>
                <?= showSuccess($successMessage); ?>

                <input type="text" name="name" class="name" placeholder="Full Name" required>
                <input type="text" name="username" class="username" placeholder="Username" required>
                <input type="email" name="email" class="email" placeholder="Email" required>
                
                <!-- ✅ Philippine phone number pattern -->
                <input 
                    type="tel" 
                    name="phone" 
                    class="phone" 
                    placeholder="09xxxxxxxxx" 
                    pattern="^(09|\+639)\d{9}$" 
                    title="Enter a valid PH number (e.g., 09123456789 or +639123456789)"
                    required>

                <input type="password" name="password" class="password" placeholder="Password" required>
                
                <button type="submit" name="register" class="register">Register</button>
                <p>Already have an Account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>
    </div>

    <script src="../js/login_script.js"></script>
</body>
</html>
