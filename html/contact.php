<?php
session_start();

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

$feedback = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error = 'Please fill in your name, email, and message.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $mail = new PHPMailer(true);
        try {
            // SMTP settings (Gmail example)
            $mail->isSMTP();
            $mail->Host       = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = '811cf1db6db5c5';      // TODO: change to your Gmail
            $mail->Password   = 'b52d7808bc74ee'; // TODO: change to your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 2525;

            // From / To
            $mail->setFrom('no-reply@purrfectpaws.test', 'Purrfect Paws');
            $mail->addAddress('ppaws1027@gmail.com');
            $mail->addReplyTo($email, $name);

            // Content
            $mail->isHTML(false);
            $mail->Subject = 'New Contact Message - Purrfect Paws';

            $body  = "Name: {$name}\n";
            $body .= "Email: {$email}\n";
            $body .= "Phone: {$phone}\n\n";
            $body .= "Message:\n{$message}\n";

            $mail->Body = $body;

            $mail->send();
            $feedback = 'Thank you! Your message has been sent.';
            $name = $email = $phone = $message = '';
        } catch (Exception $e) {
            $error = 'Sorry, we could not send your message. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="keywords" content="Purrfect Paws, contact, cats, Cagayan de Oro City">
  <meta name="description" content="Contact Purrfect Paws if you have any questions about our products or services.">
  <title>Contact Us | Purrfect Paws</title>
  <link rel="stylesheet" href="css/kumi.css">
  <script src="https://kit.fontawesome.com/df5d6157cf.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

  <!-- Navigation Bar -->
  <nav class="navbar">
    <div class="navdiv">
      <div class="logo"><a href="index.php">Purrfect Paws</a></div>
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="product.php">Shop</a></li>
        <li><a href="gallery.php">Gallery</a></li>
      </ul>

      <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
          <?php
          $profileImage = isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])
            ? "../HTML/uploads/" . htmlspecialchars($_SESSION['profile_image'])
            : "../HTML/uploads/default.png";
          ?>
          <div class="user-menu">
            <img src="<?= $profileImage ?>" alt="User" class="user-icon">
            <span class="username"><?= htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
            <div class="dropdown">
              <a href="../profile_php/profile.php">My Account</a>
              <a href="../profile_php/profile.php#purchases">My Purchase</a>
              <a href="../login_register/logout.php">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="../login_register/purdex.php"><button><i class="fa-solid fa-cat"></i> Login</button></a>
        <?php endif; ?>

        <a href="cart.php" class="cart-icon" style="position:relative;">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="cart-badge" style="display:none;">0</span>
        </a>
      </div>
    </div>
  </nav>

  <!-- Contact Us Content -->
  <main style="max-width:800px;margin:40px auto 60px;padding:0 16px;">
    <h1 style="font-size:32px;margin-bottom:10px;">Contact Us</h1>
    <p style="margin-bottom:24px;">If you have any queries regarding the products we sell, please get in touch with us by using the contact form below.</p>

    <?php if ($feedback): ?>
      <div style="margin-bottom:16px;padding:10px 12px;border-radius:4px;background:#d1fae5;color:#065f46;font-size:14px;">
        <?= htmlspecialchars($feedback) ?>
      </div>
    <?php elseif ($error): ?>
      <div style="margin-bottom:16px;padding:10px 12px;border-radius:4px;background:#fee2e2;color:#b91c1c;font-size:14px;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="" style="display:flex;flex-direction:column;gap:12px;">
      <input type="text" name="name" placeholder="Name" value="<?= htmlspecialchars($name ?? '') ?>" required
             style="padding:10px 12px;border-radius:4px;border:1px solid #d1d5db;font-size:14px;">

      <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email ?? '') ?>" required
             style="padding:10px 12px;border-radius:4px;border:1px solid #d1d5db;font-size:14px;">

      <input type="text" name="phone" placeholder="Phone Number" value="<?= htmlspecialchars($phone ?? '') ?>"
             style="padding:10px 12px;border-radius:4px;border:1px solid #d1d5db;font-size:14px;">

      <textarea name="message" rows="6" placeholder="Message" required
                style="padding:10px 12px;border-radius:4px;border:1px solid #d1d5db;font-size:14px;resize:vertical;">
<?= htmlspecialchars($message ?? '') ?></textarea>

      <div style="display:flex;justify-content:flex-end;margin-top:8px;">
        <button type="submit" style="min-width:90px;padding:8px 20px;border:none;border-radius:999px;background:#1d4ed8;color:#fff;font-weight:600;cursor:pointer;">
          Send
        </button>
      </div>
    </form>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-logo">
      <img src="images/da458a49866cd4f697e076e5d2e2099f-removebg-preview.png" alt="cat">
    </div>

    <div class="footer-container">
      <div class="footer-col">
        <h4>Information</h4>
        <ul>
          <li><a href="about.php">About</a></li>
          <li><a href="contact.php">Contact Us</a></li>
          <li><a href="gallery.php">Blogs</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Shopping</h4>
        <ul>
          <li><a href="product.php">Products</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Contact</h4>
        <p style="font-weight:bold;">431 Captain E. Jabulin St<br>
           ppaws1027@gmail.com<br>
           0961 9400 663<br>
        </p>
      </div>

      <div class="footer-col">
        <h4>Follow Us</h4>
        <div class="social-icons">
          <a href="https://www.instagram.com/ppaws1027/"><i class="fa-brands fa-instagram"></i></a>
          <a href="#"><i class="fa-brands fa-facebook"></i></a>
          <a href="https://x.com/PurrfectPaw1027"><i class="fa-brands fa-twitter"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    window.PURR_USER_ID = <?= json_encode((string)($_SESSION['user_id'] ?? 'anon')) ?>;
  </script>
  <script src="../js/cart.js?v=user-ns"></script>
</body>
</html>
