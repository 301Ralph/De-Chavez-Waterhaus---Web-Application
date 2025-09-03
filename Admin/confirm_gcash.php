<?php
include '../includes/connection.php';
session_start();

// Check if the user is logged in and is admin
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    echo '<script type="text/javascript">
        alert("Access denied. Admins only.");
        window.location = "../login.php";
        </script>';
    exit();
}

$orderID = $_GET['orderID'];
$status = $_POST['status'];
$reason = $_POST['reason'] ?? '';

// Update order status
$updateQuery = "UPDATE orders SET status = '$status' WHERE orderID = $orderID";
if ($conn->query($updateQuery) === TRUE) {
    // Create notification message
    $message = ($status === 'confirmed') ? "Your order has been confirmed." : "Your order has been rejected. Reason: $reason";

    // Insert notification
    $orderQuery = "SELECT customerID FROM orders WHERE orderID = $orderID";
    $orderResult = $conn->query($orderQuery);
    $order = $orderResult->fetch_assoc();
    $userID = $order['customerID'];

    $notificationQuery = "INSERT INTO notifications (userID, message) VALUES (?, ?)";
    $stmt = $conn->prepare($notificationQuery);
    $stmt->bind_param("is", $userID, $message);
    $stmt->execute();
    $stmt->close();

    echo '<script type="text/javascript">
        alert("Order status updated successfully.");
        window.location = "order_management.php";
        </script>';
} else {
    echo "Error: " . $updateQuery . "<br>" . $conn->error;
}

$conn->close();
?>
