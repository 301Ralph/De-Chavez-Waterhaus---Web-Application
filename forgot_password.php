<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - De Chavez Waterhaus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/signin.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="video-background">
  <video autoplay muted loop class="bg-video" playsinline>
    <source src="assets/videos/BG.mp4" type="video/mp4" />
  </video>
  <div class="overlay"></div>
</div>

<section class="hero-section">
  <div class="wrapper">
    <h2>Forgot Password</h2>
    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>

    <form action="actions/forgot_password_action.php" method="post">
      <div class="input-field">
        <input type="email" name="email" id="email" required autocomplete="email">
        <label for="email">Enter your email</label>
      </div>
      <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
    </form>
    <div class="register mt-3">
      <p>Back to <a href="signin.php">Sign In</a></p>
    </div>
  </div>
</section>
</body>
</html>
