<?php
include '../includes/connection.php';
session_start();

$email = htmlspecialchars($_POST['email']);
$password = htmlspecialchars($_POST['password']);

// Check in customers table
$sql = "SELECT * FROM customers WHERE Email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['Password'])) {
        $_SESSION['userID'] = $row['userID'];
        $_SESSION['userName'] = $row['Firstname'];
        $_SESSION['role'] = 'customer'; 

        header("Location: ../Customer/customer_dashboard.php");
        exit();
    } else {
        $_SESSION['login_error'] = 'Incorrect password. Please try again.';
        header("Location: ../signin.php");
        exit();
    }
}

// Check in admins table
$sql = "SELECT * FROM admins WHERE Email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['Password'])) {
        $_SESSION['userID'] = $row['adminID'];
        $_SESSION['userName'] = $row['Firstname'];
        $_SESSION['role'] = 'admin'; // Set role as admin

        header("Location: ../Admin/admin_dashboard.php");
        exit();
    } else {
        $_SESSION['login_error'] = 'Incorrect password. Please try again.';
        header("Location: ../signin.php");
        exit();
    }
} else {
    $_SESSION['login_error'] = 'No account found with that email address.';
    header("Location: ../signin.php");
    exit();
}

$conn->close();
?>
