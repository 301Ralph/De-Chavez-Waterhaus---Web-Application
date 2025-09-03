<?php
include '../includes/connection.php';
session_start();

// Check if the user is logged in and is a customer
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script type="text/javascript">
        alert("Access denied. Customers only.");
        window.location = "../login.php";
        </script>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderID = $_POST['orderID'];
    $userID = $_SESSION['userID'];

    // Check if the order belongs to the logged-in customer and is in 'accepted' status
    $orderQuery = "SELECT * FROM orders WHERE orderID = $orderID AND customerID = $userID AND status = 'accepted'";
    $orderResult = $conn->query($orderQuery);

    if ($orderResult->num_rows > 0) {
        // Update the order status to 'canceled'
        $updateQuery = "UPDATE orders SET status = 'canceled' WHERE orderID = $orderID";
        if ($conn->query($updateQuery) === TRUE) {
            echo '<script type="text/javascript">
                alert("Order canceled successfully.");
                window.location = "orders.php";
                </script>';
        } else {
            echo '<script type="text/javascript">
                alert("Failed to cancel the order.");
                window.location = "orders.php";
                </script>';
        }
    } else {
        echo '<script type="text/javascript">
            alert("Order cannot be canceled.");
            window.location = "orders.php";
            </script>';
    }
}
?>
