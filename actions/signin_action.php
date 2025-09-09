<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
include '../includes/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if account exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            if ($user['is_verified'] != 1) {
                header("Location: ../signin.php?error=" . urlencode("Please verify your email before signing in."));
                exit();
            }

            // Temporarily hold login
            $_SESSION['pending_login'] = $user;

            // Generate 6-digit code
            $verificationCode = rand(100000, 999999);
            $_SESSION['login_verification_code'] = $verificationCode;
            $_SESSION['last_code_sent'] = time();

            // Send verification code via PHPMailer + SendGrid SMTP
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.sendgrid.net';
                $mail->SMTPAuth = true;
                $mail->Username = 'apikey'; // must be "apikey"
                $mail->Password = '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'; // your real API key
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('aiacc401@gmail.com', 'De Chavez Waterhaus');
                $mail->addAddress($user['email'], $user['name']);

                $mail->isHTML(true);
                $mail->Subject = "Your Login Verification Code";
                $mail->Body    = "Hello " . htmlspecialchars($user['name']) . ",<br><br>" .
                                "Your login verification code is: <b>$verificationCode</b><br><br>" .
                                "If this wasn't you, please ignore this email.";

                if ($mail->send()) {
                    header("Location: ../auth/verify_login.php");
                    exit();
                } else {
                    header("Location: ../signin.php?error=" . urlencode("Could not send verification code. Try again."));
                    exit();
                }
            } catch (Exception $e) {
                header("Location: ../signin.php?error=" . urlencode("Mailer Error: " . $mail->ErrorInfo));
                exit();
            }
        } else {
            header("Location: ../signin.php?error=" . urlencode("Invalid password."));
            exit();
        }
    } else {
        header("Location: ../signin.php?error=" . urlencode("No account found with that email."));
        exit();
    }
}
