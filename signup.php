<?php
include 'includes/connection.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign Up - De Chavez Waterhaus</title>
    
    <!-- Styles and Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="assets/css/signin.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="icon" href="assets/images/logo.png" type="image/png" />
    <style>
        #togglePassword {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        }

    </style>
</head>
<body>

<!-- Navbar -->
<header>
    <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
        <img src="assets/images/logo.png" alt="Logo" style="height: 40px;" />
        <span>De Chavez Waterhaus</span>
        </a>

        <!-- Hamburger -->
        <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu -->
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

<!-- Video Background -->
<div class="video-background">
    <video autoplay muted loop class="bg-video" playsinline>
        <source src="assets/videos/BG.mp4" type="video/mp4" />
        Your browser does not support the video tag.
    </video>
    <div class="overlay"></div>
</div>

<!-- Signup Form -->
<section class="hero-section">
    <div class="wrapper">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="actions/signup_action.php" method="post" class="mt-3" id="signupForm">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required 
                       pattern="^[A-Za-z\s]+$" 
                       title="Name should contain letters and spaces only.">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required autocomplete="email">
            </div>
            <div class="mb-3 position-relative">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" 
                        required autocomplete="new-password" minlength="8">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                    <i class="fa fa-eye"></i>
                    </button>
                </div>
                <ul id="passwordRequirements" class="small mt-2 list-unstyled">
                    <li id="reqLength" class="text-danger">❌ At least 8 characters</li>
                    <li id="reqUpper" class="text-danger">❌ At least 1 uppercase letter</li>
                    <li id="reqLower" class="text-danger">❌ At least 1 lowercase letter</li>
                    <li id="reqNumber" class="text-danger">❌ At least 1 number</li>
                    <li id="reqSpecial" class="text-danger">❌ At least 1 special character (@$!%*?&)</li>
                </ul>
            </div>



            <button type="submit" class="btn btn-primary w-100">Create account</button>

            <div class="text-center mt-3">
                <a href="auth/google_login.php" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="fab fa-google"></i> Continue with Google
                </a>
            </div>

            <div class="text-center mt-3">
                <p class="mb-0">Already have an account? <a href="signin.php">Sign In</a></p>
            </div>
        </form>
    </div>
</section>

<!-- Footer -->
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const passwordField = document.getElementById("password");
  const togglePassword = document.getElementById("togglePassword");

  // Toggle visibility
  togglePassword.addEventListener("click", () => {
    const type = passwordField.type === "password" ? "text" : "password";
    passwordField.type = type;
    togglePassword.innerHTML = type === "password" 
        ? '<i class="fa fa-eye"></i>' 
        : '<i class="fa fa-eye-slash"></i>';
  });

  // Live requirements check
  passwordField.addEventListener("input", () => {
    const value = passwordField.value;

    document.getElementById("reqLength").className = value.length >= 8 ? "text-success" : "text-danger";
    document.getElementById("reqLength").innerHTML = (value.length >= 8 ? "✅" : "❌") + " At least 8 characters";

    document.getElementById("reqUpper").className = /[A-Z]/.test(value) ? "text-success" : "text-danger";
    document.getElementById("reqUpper").innerHTML = (/[A-Z]/.test(value) ? "✅" : "❌") + " At least 1 uppercase letter";

    document.getElementById("reqLower").className = /[a-z]/.test(value) ? "text-success" : "text-danger";
    document.getElementById("reqLower").innerHTML = (/[a-z]/.test(value) ? "✅" : "❌") + " At least 1 lowercase letter";

    document.getElementById("reqNumber").className = /\d/.test(value) ? "text-success" : "text-danger";
    document.getElementById("reqNumber").innerHTML = (/\d/.test(value) ? "✅" : "❌") + " At least 1 number";

    document.getElementById("reqSpecial").className = /[@$!%*?&]/.test(value) ? "text-success" : "text-danger";
    document.getElementById("reqSpecial").innerHTML = (/[@$!%*?&]/.test(value) ? "✅" : "❌") + " At least 1 special character (@$!%*?&)";
  });
</script>

</body>
</html>
