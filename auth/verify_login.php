<?php
session_start();
require '../vendor/autoload.php';
include '../includes/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If no user data, redirect
if (!isset($_SESSION['pending_login'])) {
    header("Location: ../signin.php?error=Session expired, please sign in again.");
    exit();
}

$user = $_SESSION['pending_login'];

// Handle resend request
if (isset($_GET['resend'])) {
    $cooldown = 60; // seconds
    if (isset($_SESSION['last_code_sent']) && time() - $_SESSION['last_code_sent'] < $cooldown) {
        $remaining = $cooldown - (time() - $_SESSION['last_code_sent']);
        header("Location: verify_login.php?error=Please wait $remaining seconds before resending.");
        exit();
    }

    // Generate new code
    $verificationCode = rand(100000, 999999);
    $_SESSION['login_verification_code'] = $verificationCode;
    $_SESSION['last_code_sent'] = time();

    // Send email via PHPMailer (SendGrid SMTP)
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.sendgrid.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'apikey';
        $mail->Password = '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'; // replace with your SendGrid API Key
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("aiacc401@gmail.com", "De Chavez Waterhaus");
        $mail->addAddress($user['email'], $user['name']);

        $mail->isHTML(true);
        $mail->Subject = "🔐 Login Verification Code - De Chavez Waterhaus";
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='UTF-8'>
          <style>
            body { font-family: Arial, sans-serif; background-color: #f9f9f9; margin:0; padding:0; }
            .container { max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
            h2 { color: #0d6efd; text-align: center; }
            .code-box { font-size: 28px; font-weight: bold; text-align: center; background: #f1f5ff; padding: 15px; border-radius: 6px; margin: 20px 0; letter-spacing: 5px; color: #0d6efd; }
            p { color: #333; font-size: 15px; line-height: 1.6; }
            .footer { font-size: 12px; color: #888; text-align: center; margin-top: 20px; }
          </style>
        </head>
        <body>
          <div class='container'>
            <h2>De Chavez Waterhaus</h2>
            <p>Hello <strong>{$user['name']}</strong>,</p>
            <p>We received a request to sign in to your account. Please use the verification code below to complete your login:</p>
            
            <div class='code-box'>$verificationCode</div>
            
            <p><b>Note:</b> This code will expire shortly for your security. If you did not try to log in, please ignore this email or contact our support team immediately.</p>
            
            <div class='footer'>
              &copy; " . date("Y") . " De Chavez Waterhaus. All rights reserved.<br>
              This is an automated message, please do not reply.
            </div>
          </div>
        </body>
        </html>
        ";

        $mail->send();
        header("Location: verify_login.php?success=New code sent to your email.");
        exit();
    } catch (Exception $e) {
        header("Location: verify_login.php?error=Could not resend code. Try again.");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredCode = $_POST['code'];

    if (isset($_SESSION['login_verification_code']) && $enteredCode == $_SESSION['login_verification_code']) {
        // Success → set session user
        $_SESSION['user'] = $user;
        unset($_SESSION['pending_login']);
        unset($_SESSION['login_verification_code']);
        header("Location: ../customer/customer_dashboard.php");
        exit();
    } else {
        header("Location: verify_login.php?error=Invalid verification code.");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify Login - De Chavez Waterhaus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body, html {
      height: 100%;
      margin: 0;
    }
    .bg-video {
      position: fixed;
      right: 0;
      bottom: 0;
      min-width: 100%;
      min-height: 100%;
      z-index: -1;
      object-fit: cover;
      filter: brightness(0.6);
    }
    .card {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0px 5px 20px rgba(0,0,0,0.3);
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <video autoplay muted loop class="bg-video">
      <source src="../assets/videos/BG.mp4" type="video/mp4">
    </video>
  <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
    <h4 class="text-center mb-3">Verify Your Login</h4>
    <p class="text-muted text-center">Enter the 6-digit code sent to <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>

    <form method="post" class="mb-3">
      <div class="mb-3">
        <input type="text" name="code" class="form-control text-center" placeholder="Enter code" maxlength="6" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Verify</button>
    </form>

    <div class="text-center">
      <a href="verify_login.php?resend=1" id="resendBtn" class="btn btn-link">Resend Code</a>
      <small id="cooldownMsg" class="text-muted d-block"></small>
    </div>
  </div>

<script>
  // Client-side cooldown (sync with server-side check)
  let cooldown = 60; // seconds
  let resendBtn = document.getElementById("resendBtn");
  let cooldownMsg = document.getElementById("cooldownMsg");

  <?php if (isset($_SESSION['last_code_sent'])): ?>
    let lastSent = <?php echo $_SESSION['last_code_sent']; ?>;
    let now = Math.floor(Date.now() / 1000);
    let diff = now - lastSent;
    if (diff < cooldown) {
      let remaining = cooldown - diff;
      disableResend(remaining);
    }
  <?php endif; ?>

  function disableResend(seconds) {
    resendBtn.classList.add("disabled");
    cooldownMsg.innerText = "You can resend in " + seconds + "s";
    let interval = setInterval(() => {
      seconds--;
      if (seconds <= 0) {
        resendBtn.classList.remove("disabled");
        cooldownMsg.innerText = "";
        clearInterval(interval);
      } else {
        cooldownMsg.innerText = "You can resend in " + seconds + "s";
      }
    }, 1000);
  }
</script>
</body>
</html>
