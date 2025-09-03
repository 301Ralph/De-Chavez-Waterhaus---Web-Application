<?php
include '../includes/connection.php';
session_start();

// Check if the user is logged in and is customer
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script type="text/javascript">
        alert("Access denied. Customers only.");
        window.location = "../login.php";
        </script>';
    exit();
}

$userID = $_SESSION['userID'];

// Fetch orders and associated order items
$order = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';
$ordersQuery = "
    SELECT o.orderID, o.order_date, o.status, o.payment_method, o.total_amount, o.reason,
           GROUP_CONCAT(CONCAT(p.ProductName, ' (', oi.quantity, ')') SEPARATOR '<br>') AS products,
           GROUP_CONCAT(p.ImageURL SEPARATOR ',') as images,
           GROUP_CONCAT(oi.quantity SEPARATOR ',') as quantities,
           GROUP_CONCAT(oi.unit_price SEPARATOR ',') as unit_prices
    FROM orders o
    JOIN order_items oi ON o.orderID = oi.orderID
    JOIN product p ON oi.productID = p.ProductID
    WHERE o.customerID = $userID
    GROUP BY o.orderID
    ORDER BY o.order_date $order";
$ordersResult = $conn->query($ordersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | De Chavez Waterhaus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #f8f9fa;
            padding-top: 20px;
            transition: width 0.3s;
        }
        .sidebar.close {
            width: 80px;
        }
        .sidebar .logo {
            display: flex;
            align-items: center;
            padding: 0 20px;
            margin-bottom: 20px;
        }
        .sidebar .logo img {
            width: 40px;
            border-radius: 50%;
        }
        .sidebar .logo .logo_name {
            font-size: 22px;
            font-weight: 600;
            margin-left: 10px;
            transition: opacity 0.3s;
        }
        .sidebar.close .logo .logo_name {
            opacity: 0;
        }
        .sidebar .nav-links {
            list-style: none;
            padding: 0;
        }
        .sidebar .nav-links li {
            width: 100%;
        }
        .sidebar .nav-links li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s;
        }
        .sidebar .nav-links li a:hover {
            background-color: #e2e6ea;
        }
        .sidebar .nav-links li a i {
            font-size: 24px;
            min-width: 45px;
        }
        .sidebar .nav-links li a .link-name {
            font-size: 18px;
            transition: opacity 0.3s;
        }
        .sidebar.close .nav-links li a .link-name {
            opacity: 0;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        .sidebar.close ~ .content {
            margin-left: 80px;
        }
        .sidebar-toggle {
            font-size: 26px;
            cursor: pointer;
        }
        .order-table {
            margin-top: 20px;
        }
        .product-images img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 5px;
        }
        .notification {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <div class="logo">
            <img src="../images/logo.png" alt="Logo">
            <span class="logo_name">De Chavez</span>
        </div>

        <ul class="nav-links">
            <li><a href="customer_dashboard.php">
                <i class="bi bi-house-door"></i>
                <span class="link-name">Home</span>
            </a></li>
            <li><a href="products.php">
                <i class="bi bi-box"></i>
                <span class="link-name">Products</span>
            </a></li>
            <li><a href="orders.php">
                <i class="bi bi-cart"></i>
                <span class="link-name">Orders</span>
            </a></li>
            <li><a href="order_history.php">
                <i class="bi bi-clock-history"></i>
                <span class="link-name">Order History</span>
            </a></li>
            <li><a href="ticket.php">
                <i class="bi bi-inbox"></i>
                <span class="link-message ">Tickets</span>
            </a></li>
            <li><a href="notification.php">
                <i class="bi bi-bell"></i>
                <span class="link-name">Notification</span>
            </a></li>
            <li><a href="profile.php">
                <i class="bi bi-person"></i>
                <span class="link-name">Profile</span>
            </a></li>
            <li><a href="../logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span class="link-name">Logout</span>
            </a></li>
        </ul>
    </nav>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <i class="bi bi-list sidebar-toggle"></i>
            <h1>Order History</h1>
            <a href="order_history.php?order=<?php echo $order == 'ASC' ? 'desc' : 'asc'; ?>" class="btn btn-primary">
                Sort by Date <?php echo $order == 'ASC' ? 'Descending' : 'Ascending'; ?>
            </a>
        </div>

        <div class="order-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Products</th>
                        <th>Quantity</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Payment Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $ordersResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></td>
                            <td>
                                <?php 
                                $products = explode('<br>', $order['products']);
                                $images = explode(',', $order['images']);
                                $quantities = explode(',', $order['quantities']);
                                foreach ($products as $index => $product) {
                                    echo "<div class='product-images'><img src='../images/" . $images[$index] . "' alt='Product Image'></div>";
                                    echo $product . "<br>";
                                }
                                ?>
                            </td>
                            <td><?php echo array_sum($quantities); ?></td>
                            <td>
                                <?php
                                $unitPrices = explode(',', $order['unit_prices']);
                                $totalAmount = 0;
                                foreach ($unitPrices as $index => $unitPrice) {
                                    $totalAmount += $unitPrice * $quantities[$index];
                                }
                                echo number_format($totalAmount, 2);
                                ?>
                            </td>
                            <td><?php echo ucfirst($order['status']); ?></td>
                            <td><?php echo ucfirst($order['payment_method']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.querySelector(".sidebar");
            const toggle = document.querySelector(".sidebar-toggle");

            toggle.addEventListener("click", () => {
                sidebar.classList.toggle("close");
            });

            const deleteButtons = document.querySelectorAll(".delete-notification");

            deleteButtons.forEach(button => {
                button.addEventListener("click", function() {
                    const notificationID = this.getAttribute("data-notification-id");
                    fetch(`delete_notification.php?id=${notificationID}`)
                        .then(response => response.text())
                        .then(data => {
                            if (data === "success") {
                                this.parentElement.remove();
                            } else {
                                alert("Error deleting notification.");
                            }
                        });
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
