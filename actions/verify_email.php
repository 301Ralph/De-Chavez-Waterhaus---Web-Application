<?php
session_start();
include '../includes/connection.php';

$message = "";
$type = "danger"; // default error style

if (!isset($_GET['token'])) {
    $message = "❌ Invalid verification link.";
} else {
    $token = urldecode($_GET['token']);

    // Look up user by token
    $stmt = $conn->prepare("SELECT * FROM users WHERE verification_token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['is_verified'] == 1) {
            $message = "✅ Your email is already verified. <a href='../signin.php' class='btn btn-sm btn-outline-primary ms-2'>Sign In</a>";
            $type = "info";
        } else {
            // ✅ Update record
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            if ($stmt->execute()) {
                $message = "🎉 Email verified successfully!<br>You can now <a href='../signin.php' class='btn btn-sm btn-outline-success ms-2'>Sign In</a>";
                $type = "success";
            } else {
                $message = "❌ Error verifying email. Please try again.";
            }
        }
    } else {
        $message = "❌ Invalid or expired verification link.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Verification - De Chavez Waterstation</title>
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
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0px 5px 20px rgba(0,0,0,0.3);
      text-align: center;
    }
    h2 {
      font-weight: bold;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
  <video autoplay muted loop class="bg-video">
    <source src="../assets/videos/BG.mp4" type="video/mp4">
  </video>

  <div class="card shadow" style="max-width: 500px; width: 100%;">
    <h2 class="mb-4">Email Verification</h2>
    <div class="alert alert-<?php echo $type; ?>" role="alert">
      <?php echo $message; ?>
    </div>
  </div>
</body>
</html>
