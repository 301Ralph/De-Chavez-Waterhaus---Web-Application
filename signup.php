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
</head>
<body>

<!-- Navbar (Bootstrap-based, consistent with signin) -->
<header>
    <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
        <img src="assets/images/logo.png" alt="Logo" style="height: 40px;" />
        <span>De Chavez Waterhaus</span>
        </a>
        
        <!-- Hamburger Button -->
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

        <!-- Collapsible Menu -->
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

<!-- Signup Form Section -->
<section class="hero-section">
    <div class="wrapper">
        <form action="actions/signup_action.php" method="POST">
            <h2>Register an Account</h2>

            <div class="input-field">
                <input type="text" name="firstname" required placeholder=" " />
                <label>First Name</label>
            </div>

            <div class="input-field">
                <input type="text" name="lastname" required placeholder=" " />
                <label>Last Name</label>
            </div>

            <div class="input-field">
                <input type="email" name="email" required placeholder=" " />
                <label>Email Address</label>
            </div>

            <div class="input-field">
                <input type="text" name="contact" required placeholder=" " />
                <label>Contact Number</label>
            </div>

            <div class="input-field">
                <input type="text" name="address" required placeholder=" " />
                <label>Address</label>
            </div>

            <div class="input-field">
                <input type="password" name="password" required placeholder=" " />
                <label>Password</label>
            </div>

            <div class="input-field">
                <input type="password" name="confirm_password" required placeholder=" " />
                <label>Confirm Password</label>
            </div>

            <button type="submit">Sign Up</button>

            <div class="register">
                <p>Already have an account? <a href="signin.php">Sign In</a></p>
            </div>
        </form>
    </div>
</section>

<!-- Footer (reuse from signin page) -->
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
</body>
</html>
