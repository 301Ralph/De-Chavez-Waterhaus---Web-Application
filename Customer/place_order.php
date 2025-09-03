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

// Check if the customer is verified
$userID = $_SESSION['userID'];
$customerQuery = "SELECT isVerified FROM customers WHERE userID = $userID";
$customerResult = $conn->query($customerQuery);
$customer = $customerResult->fetch_assoc();

if (!$customer['isVerified']) {
    echo '<script type="text/javascript">
        alert("You need to verify your account to place orders.");
        window.location = "order.php";
        </script>';
    exit();
}

// Process the order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productID = $_POST['productID'];
    $quantity = $_POST['quantity'];
    $deliveryDate = $_POST['delivery_date'];
    $paymentMethod = $_POST['payment_method'];

    // Check if quantity is within limits
    if ($quantity < 6 || $quantity > 100) {
        echo '<script type="text/javascript">
            alert("Quantity must be between 6 and 100.");
            window.location = "order.php";
            </script>';
        exit();
    }

    // Fetch the unit price of the product
    $productQuery = "SELECT Price FROM product WHERE ProductID = $productID";
    $productResult = $conn->query($productQuery);
    $product = $productResult->fetch_assoc();
    $unitPrice = $product['Price'];
    $totalAmount = $unitPrice * $quantity;

    // Insert order into database
    $orderQuery = "INSERT INTO orders (customerID, order_date, total_amount, status, payment_method) VALUES ($userID, NOW(), $totalAmount, 'pending', '$paymentMethod')";
    if ($conn->query($orderQuery) === TRUE) {
        $orderID = $conn->insert_id;

        // Insert order items with unit price
        $orderItemQuery = "INSERT INTO order_items (orderID, productID, quantity, unit_price) VALUES ($orderID, $productID, $quantity, $unitPrice)";
        if ($conn->query($orderItemQuery) === TRUE) {
            // Insert delivery information
            $deliveryQuery = "INSERT INTO deliveries (orderID, delivery_date, userID, status) VALUES ('$orderID', '$deliveryDate', '$userID', 'scheduled')";
            if ($conn->query($deliveryQuery) === TRUE) {
                // Create notification message
                $message = "You've placed an order Successfully!";

                // Insert notification
                $notificationQuery = "INSERT INTO notifications (userID, message) VALUES (?, ?)";
                $stmt = $conn->prepare($notificationQuery);
                $stmt->bind_param("is", $userID, $message);
                $stmt->execute();
                $stmt->close();

                echo '<script type="text/javascript">
                    alert("Order placed successfully.");
                    window.location = "order_history.php";
                    </script>';
            } else {
                echo "Error: " . $deliveryQuery . "<br>" . $conn->error;
            }
        } else {
            echo "Error: " . $orderItemQuery . "<br>" . $conn->error;
        }
    } else {
        echo "Error: " . $orderQuery . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
