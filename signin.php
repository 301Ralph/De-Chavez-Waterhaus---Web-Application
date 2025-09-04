<?php
session_start();
include 'includes/connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign In - De Chavez Waterhaus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="assets/css/signin.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="icon" href="assets/images/logo.png" type="image/png" />
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <img src="assets/images/logo.png" alt="Logo" style="height: 40px; width: auto;" />
                <span>De Chavez Waterhaus</span>
            </a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#products">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="video-background">
    <video autoplay muted loop class="bg-video" playsinline>
      <source src="assets/videos/BG.mp4" type="video/mp4" />
      Your browser does not support the video tag.
    </video>
    <div class="overlay"></div>
</div>

<!-- Main Sign In Section -->
<section class="hero-section">
  <div class="wrapper">
    <form action="actions/signin_action.php" method="post">
      <h2 style="font-family: 'Poppins', sans-serif;">Sign In to Your Account</h2>

      <div class="input-field">
        <input
          type="email"
          id="email"
          name="email"
          required
          autocomplete="email"
          />
        <label for="email">Email Address</label>
      </div>

      <div class="input-field position-relative">
        <input
          type="password"
          id="password"
          name="password"
          required
          autocomplete="current-password"
        />
        <label for="password">Password</label>
        <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;" onclick="togglePassword()">
          <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
        </span>
      </div>
      <div class="text-end mb-3">
        <a href="forgot_password.php" class="text-decoration-none text-light">Forgot Password?</a>
      </div>

      <button type="submit">Sign In</button>
      <div class="text-center mt-3">
        <a href="auth/google_login.php" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2">
          <i class="fab fa-google"></i> Sign in with Google
        </a>
      </div>


      <div class="register">
        <p>Don't have an account? <a href="signup.php">Register here</a></p>
      </div>

      <?php if (isset($_GET['error'])): ?>
        <div class="error-box">
          <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
      <?php endif; ?>

    </form>
  </div>
</section>



<footer class="bg-dark text-white py-4 fade-in-up">
    <div class="container text-center">
        <img src="assets/images/logo.png" alt="Logo" style="height: 40px;" class="mb-2" />
        <p class="mb-1">De Chavez Waterhaus – Pure Water, Trusted Service</p>
        <div class="mb-2">
            <a href="index.php#home" class="text-white text-decoration-none mx-2">Home</a>
            <a href="index.php#products" class="text-white text-decoration-none mx-2">Products</a>
            <a href="index.php#about" class="text-white text-decoration-none mx-2">About Us</a>
            <a href="index.php#faq" class="text-white text-decoration-none mx-2">FAQ</a>
            <a href="index.php#contact" class="text-white text-decoration-none mx-2">Contact</a>
        </div>
        <small>&copy; <?php echo date("Y"); ?> De Chavez Waterhaus. All rights reserved.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword() {
  const password = document.getElementById("password");
  const icon = document.getElementById("togglePasswordIcon");
  if (password.type === "password") {
    password.type = "text";
    icon.classList.remove("bi-eye-slash");
    icon.classList.add("bi-eye");
  } else {
    password.type = "password";
    icon.classList.remove("bi-eye");
    icon.classList.add("bi-eye-slash");
  }
}
</script>
</body>
</html>
