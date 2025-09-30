<?php
include '../includes/connection.php';
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Fetch old password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($old, $hashed_password)) {
        $_SESSION['error_password'] = "Old password is incorrect.";
    } elseif ($new !== $confirm) {
        $_SESSION['error_password'] = "New passwords do not match.";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/", $new)) {
        $_SESSION['error_password'] = "Password must be at least 8 characters, with upper, lower, and number.";
    } else {
        $new_hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $new_hashed, $userID);
        if ($stmt->execute()) {
            $_SESSION['success_password'] = "Password changed successfully.";
        } else {
            $_SESSION['error_password'] = "Failed to update password.";
        }
        $stmt->close();
    }
}

header("Location: ../customer/profile.php");
exit();
