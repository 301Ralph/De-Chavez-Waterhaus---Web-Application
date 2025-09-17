<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
include '../includes/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(16));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Save token in DB
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $stmt->bind_param("ssi", $token, $expires, $user['id']);
        $stmt->execute();

        // Build reset link
        $reset_link = $_ENV['APP_URL'] . "/actions/reset_password.php?token=" . $token;

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.sendgrid.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'apikey'; // SendGrid requires this literal value
            $mail->Password = $_ENV['SENDGRID_API_KEY']; // ✅ from .env
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($user['email'], $user['name']);

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            $mail->Body = "
                Hello " . htmlspecialchars($user['name']) . ",<br><br>
                We received a request to reset your password.<br>
                <a href='$reset_link'>Click here to reset your password</a><br><br>
                This link will expire in 1 hour.<br><br>
                If you did not request this, please ignore this email.
            ";

            $mail->send();
            header("Location: ../forgot_password.php?success=" . urlencode("We sent a reset link to your email."));
            exit();
        } catch (Exception $e) {
            header("Location: ../forgot_password.php?error=" . urlencode("Could not send reset email. Try again."));
            exit();
        }
    } else {
        header("Location: ../forgot_password.php?error=" . urlencode("No account found with that email."));
        exit();
    }
}
