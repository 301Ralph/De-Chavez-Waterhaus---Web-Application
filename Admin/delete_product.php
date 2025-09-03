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

$productID = $_GET['productID'];

// First, delete all related order items
$deleteOrderItemsQuery = "DELETE FROM order_items WHERE productID = $productID";
if ($conn->query($deleteOrderItemsQuery) === TRUE) {
    // Now delete the product
    $deleteProductQuery = "DELETE FROM product WHERE ProductID = $productID";
    if ($conn->query($deleteProductQuery) === TRUE) {
        echo '<script type="text/javascript">
            alert("Product deleted successfully.");
            window.location = "product_management.php";
            </script>';
    } else {
        echo "Error deleting product: " . $conn->error;
    }
} else {
    echo "Error deleting order items: " . $conn->error;
}
?>
