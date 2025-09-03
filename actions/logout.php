<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear the session array
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to the login page with a confirmation message
    echo '<script type="text/javascript">
        alert("You have been logged out.");
        window.location = "../signin.php";
        </script>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout | De Chavez Waterhaus</title>
    <link rel="stylesheet" href="Designs/login-style.css">
</head>
<body>
    <section class="hero-section">
        <div class="wrapper">
            <h2>Logout</h2>
            <p style="color:white;">Are you sure you want to logout?</p>
            <form method="POST" action="logout.php">
                <br>
                <button type="submit">Yes, Logout</button>
                <br>
                <a href="index.php">No, Go Back</a>
            </form>
        </div>
    </section>

    <script>
        const header = document.querySelector("header");
        const hamburgerBtn = document.querySelector("#hamburger-btn");
        const closeMenuBtn = document.querySelector("#close-menu-btn");

        // Toggle mobile menu on hamburger button click
        hamburgerBtn.addEventListener("click", () => header.classList.toggle("show-mobile-menu"));

        // Close mobile menu on close button click
        closeMenuBtn.addEventListener("click", () => hamburgerBtn.click());
    </script>
</body>
</html>
