<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
include '../includes/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get form inputs
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = $_POST['password'];

// Validate name
if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
    header("Location: ../signup.php?error=" . urlencode("Name must contain letters and spaces only."));
    exit();
}

// Validate password strength
$errors = [];
if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
if (!preg_match("/[A-Z]/", $password)) $errors[] = "Password must contain at least one uppercase letter.";
if (!preg_match("/[a-z]/", $password)) $errors[] = "Password must contain at least one lowercase letter.";
if (!preg_match("/\d/", $password)) $errors[] = "Password must contain at least one number.";
if (!preg_match("/[@$!%*?&]/", $password)) $errors[] = "Password must contain at least one special character (@$!%*?&).";

if (!empty($errors)) {
    header("Location: ../signup.php?error=" . urlencode(implode(" ", $errors)));
    exit();
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    header("Location: ../signup.php?error=" . urlencode("This email is already registered."));
    exit();
}
$stmt->close();

// Generate verification token
$token = bin2hex(random_bytes(16));
$verify_link = "http://localhost/DeChavezWatersation/actions/verify_email.php?token=" . $token;

// Send verification email
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.sendgrid.net';
    $mail->SMTPAuth = true;
    $mail->Username = 'apikey';
    $mail->Password = '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';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('aiacc401@gmail.com', 'De Chavez Waterstation');
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = "Verify your email address";
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2 style='color:#0050ff;'>Welcome to De Chavez Waterhaus, $name!</h2>
            <p>Thanks for signing up. Please verify your email to complete your registration:</p>
            <p style='margin:20px 0;'>
                <a href='$verify_link' 
                   style='background:#0050ff;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;'>
                   Verify Email
                </a>
            </p>
            <p>If the button doesn’t work, copy and paste this link in your browser:</p>
            <p><a href='$verify_link'>$verify_link</a></p>
            <br>
            <p style='font-size:12px;color:#666;'>If you didn’t request this, you can ignore this email.</p>
        </div>";

    if ($mail->send()) {
        // Insert user only if email sent successfully
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, verification_token, is_verified, role) VALUES (?, ?, ?, ?, 0, 'customer')");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $token);
        $stmt->execute();
        $stmt->close();

        header("Location: ../signup.php?success=" . urlencode("We sent a verification link to your email. Please verify before signing in."));
        exit();
    } else {
        header("Location: ../signup.php?error=" . urlencode("We could not send a verification email. Please try again later."));
        exit();
    }

} catch (Exception $e) {
    header("Location: ../signup.php?error=" . urlencode("Mailer Error: " . $mail->ErrorInfo));
    exit();
}
