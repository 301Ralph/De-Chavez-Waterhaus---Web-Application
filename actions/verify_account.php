<?php
include '../includes/connection.php';
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Check if already uploaded
$stmt = $conn->prepare("SELECT verification_file FROM users WHERE id=?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($verification_file);
$stmt->fetch();
$stmt->close();

if ($verification_file) {
    $_SESSION['error_verify'] = "You have already submitted verification. You cannot upload again.";
    header("Location: ../customer/profile.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['verification_file'])) {
    $file = $_FILES['verification_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_ext = ["jpg", "jpeg", "png", "pdf"];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $size = $file['size'];

        if (!in_array($ext, $allowed_ext)) {
            $_SESSION['error_verify'] = "Invalid file type. Only JPG, PNG, PDF allowed.";
        } elseif ($size > 2 * 1024 * 1024) {
            $_SESSION['error_verify'] = "File size exceeds 2MB.";
        } else {
            $new_name = "verify_" . $userID . "_" . time() . "." . $ext;
            $upload_dir = "../uploads/verifications/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $target = $upload_dir . $new_name;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $stmt = $conn->prepare("UPDATE users SET verification_file=?, is_verified=0 WHERE id=?");
                $stmt->bind_param("si", $new_name, $userID);
                if ($stmt->execute()) {
                    $_SESSION['success_verify'] = "Verification submitted. Please wait for admin approval.";
                } else {
                    $_SESSION['error_verify'] = "Database error. Try again later.";
                }
                $stmt->close();
            } else {
                $_SESSION['error_verify'] = "Failed to upload file.";
            }
        }
    } else {
        $_SESSION['error_verify'] = "File upload error.";
    }
}

header("Location: ../customer/profile.php");
exit();
