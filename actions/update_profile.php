<?php
include '../includes/connection.php';
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    // Validation
    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $_SESSION['error_profile'] = "Name must contain only letters and spaces.";
    } elseif (!preg_match("/^(09\d{9}|\+639\d{9})$/", $phone)) {
        $_SESSION['error_profile'] = "Invalid PH phone number format.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $phone, $userID);
        if ($stmt->execute()) {
            $_SESSION['success_profile'] = "Profile updated successfully.";
        } else {
            $_SESSION['error_profile'] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}

header("Location: ../customer/profile.php");
exit();
