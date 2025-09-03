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

// Check if order ID and new status are provided
if ((isset($_GET['orderID']) && isset($_GET['status'])) || (isset($_POST['orderID']) && isset($_POST['status']))) {
    $orderID = isset($_GET['orderID']) ? $_GET['orderID'] : $_POST['orderID'];
    $newStatus = isset($_GET['status']) ? $_GET['status'] : $_POST['status'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';

    // Update the order status
    $updateQuery = "UPDATE orders SET status = ?, reason = ? WHERE orderID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssi", $newStatus, $reason, $orderID);

    if ($stmt->execute()) {
        // Fetch the user ID of the customer who placed the order
        $orderQuery = "SELECT customerID FROM orders WHERE orderID = ?";
        $stmtOrder = $conn->prepare($orderQuery);
        $stmtOrder->bind_param("i", $orderID);
        $stmtOrder->execute();
        $result = $stmtOrder->get_result();
        $order = $result->fetch_assoc();
        $userID = $order['customerID'];

        // Create a notification message based on the new status
        if ($newStatus == 'accepted') {
            $message = "Your order has been accepted.";

            // Update the delivery date to the current date if the status is accepted
            $currentDate = date('Y-m-d H:i:s');
            $deliveryUpdateQuery = "UPDATE deliveries SET delivery_date = ? WHERE orderID = ?";
            $stmtDelivery = $conn->prepare($deliveryUpdateQuery);
            $stmtDelivery->bind_param("si", $currentDate, $orderID);
            $stmtDelivery->execute();
        } elseif ($newStatus == 'rejected') {
            $message = "Your order has been rejected. Reason: $reason";
        } else {
            $message = "Your order status has been updated to $newStatus.";
        }

        // Insert notification
        $notificationQuery = "INSERT INTO notifications (userID, message) VALUES (?, ?)";
        $stmtNotification = $conn->prepare($notificationQuery);
        $stmtNotification->bind_param("is", $userID, $message);
        $stmtNotification->execute();

        if ($newStatus == 'accepted') {
            // Redirect to delivery management for accepted orders
            echo '<script type="text/javascript">
                alert("Order accepted successfully.");
                window.location = "delivery_management.php";
                </script>';
        } elseif ($newStatus == 'rejected') {
            // Stay on the order management page for rejected orders
            echo '<script type="text/javascript">
                alert("Order rejected successfully.");
                window.location = "order_management.php";
                </script>';
        } else {
            // Stay on the order management page for other statuses
            echo '<script type="text/javascript">
                alert("Order status updated successfully.");
                window.location = "order_management.php";
                </script>';
        }
    } else {
        echo '<script type="text/javascript">
            alert("Failed to update order status.");
            window.location = "order_management.php";
            </script>';
    }
} else {
    echo '<script type="text/javascript">
        alert("Invalid request.");
        window.location = "order_management.php";
        </script>';
}
?>
