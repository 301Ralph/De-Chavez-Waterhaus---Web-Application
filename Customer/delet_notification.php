<?php
include '../includes/connection.php';
session_start();

if (!isset($_SESSION['userID'])) {
    echo "error";
    exit();
}

$userID = $_SESSION['userID'];
$notificationID = $_GET['id'];

// Check if the notification belongs to the logged-in user
$checkQuery = "SELECT * FROM notifications WHERE notificationID = $notificationID AND userID = $userID";
$checkResult = $conn->query($checkQuery);

if ($checkResult->num_rows > 0) {
    $deleteQuery = "DELETE FROM notifications WHERE notificationID = $notificationID";
    if ($conn->query($deleteQuery) === TRUE) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "error";
}

$conn->close();
?>
