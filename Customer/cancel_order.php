<?php
include '../includes/connection.php';
session_start();

// ✅ Check login & role
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script>
        alert("Access denied. Customers only.");
        window.location = "../signin.php";
    </script>';
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $orderID = (int) $_GET['id'];
    $userID = (int) $_SESSION['userID'];

    // ✅ Check if order belongs to customer and is still pending
    $stmt = $conn->prepare("SELECT status FROM `order` WHERE orderID = ? AND customerID = ? LIMIT 1");
    $stmt->bind_param("ii", $orderID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $order = $result->fetch_assoc();
        if (strtolower($order['status']) === 'pending') {
            // ✅ Cancel the order
            $update = $conn->prepare("UPDATE `order` SET status = 'canceled' WHERE orderID = ? AND customerID = ?");
            $update->bind_param("ii", $orderID, $userID);
            if ($update->execute()) {
                echo '<script>
                    alert("Order canceled successfully.");
                    window.location = "orders.php";
                </script>';
            } else {
                echo '<script>
                    alert("Failed to cancel the order. Please try again.");
                    window.location = "orders.php";
                </script>';
            }
            $update->close();
        } else {
            echo '<script>
                alert("This order cannot be canceled anymore.");
                window.location = "orders.php";
            </script>';
        }
    } else {
        echo '<script>
            alert("Order not found.");
            window.location = "orders.php";
        </script>';
    }
    $stmt->close();
} else {
    echo '<script>
        alert("Invalid request.");
        window.location = "orders.php";
    </script>';
}
?>
