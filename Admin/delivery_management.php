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

// Fetch orders with statuses that can be managed
$ordersQuery = "
    SELECT orders.orderID, customers.Firstname, customers.Lastname, orders.order_date, orders.status, deliveries.delivery_date 
    FROM orders 
    LEFT JOIN deliveries ON orders.orderID = deliveries.orderID 
    LEFT JOIN customers ON orders.customerID = customers.userID
    WHERE orders.status IN ('accepted', 'in_progress', 'out_for_delivery')";
$ordersResult = $conn->query($ordersQuery);

// Fetch delivered orders for the modal
$deliveredOrdersQuery = "
    SELECT customers.Firstname, customers.Lastname, orders.order_date, deliveries.delivery_date 
    FROM orders 
    LEFT JOIN deliveries ON orders.orderID = deliveries.orderID 
    LEFT JOIN customers ON orders.customerID = customers.userID
    WHERE orders.status = 'delivered'";
$deliveredOrdersResult = $conn->query($deliveredOrdersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Management | De Chavez Waterhaus</title>
    <link rel="stylesheet" href="../Designs/admin-style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: cyan;
        }
        .table {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        .table th, .table td {
            padding: 1rem;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .modal-content {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        .modal-header, .modal-body {
            padding: 1rem;
        }
        .modal-header {
            background-color: #f8f9fa;
        }
        .btn[disabled] {
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo-name">
            <div class="logo-image">
                <img src="../images/logo.png" alt="Logo">
            </div>
            <span class="logo_name">De Chavez</span>
        </div>

        <div class="menu-items">
            <ul class="nav-links">
                <li><a href="admin_dashboard.php">
                    <i class="bi bi-house-door"></i>
                    <span class="link-name">Dashboard</span>
                </a></li>
                <li><a href="order_management.php">
                    <i class="bi bi-cart"></i>
                    <span class="link-name">Orders</span>
                </a></li>
                <li><a href="delivery_management.php">
                    <i class="bi bi-truck"></i>
                    <span class="link-name">Delivery</span>
                </a></li>
                <li><a href="product_management.php">
                    <i class="bi bi-box"></i>
                    <span class="link-name">Products</span>
                </a></li>
                <li><a href="customer_management.php">
                    <i class="bi bi-people"></i>
                    <span class="link-name">Customers</span>
                </a></li>
                <li><a href="manage_ticket.php">
                    <i class="bi bi-inbox"></i>
                    <span class="link-name">Tickets</span>
                </a></li>
            </ul>
            
            <ul class="logout-mode">
                <li><a href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="link-name">Logout</span>
                </a></li>
            </ul>
        </div>
    </nav>

    <section class="dashboard">
        <div class="top">
            <i class="bi bi-list sidebar-toggle"></i>
            <button class="btn btn-info btn-sm ml-auto" data-toggle="modal" data-target="#deliveredHistoryModal">
                <i class="bi bi-clock-history"></i> Delivered History
            </button>
        </div>

        <div class="dash-content">
            <div class="title">
                <i class="bi bi-truck"></i>
                <span class="text">Delivery Management</span>
            </div>

            <div class="order-list">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Delivery Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $ordersResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['Firstname'] . ' ' . $row['Lastname']; ?></td>
                                <td><?php echo $row['order_date']; ?></td>
                                <td><?php echo $row['status']; ?></td>
                                <td><?php echo $row['delivery_date']; ?></td>
                                <td>
                                    <a href="update_order_status.php?orderID=<?php echo $row['orderID']; ?>&status=in_progress" class="btn btn-warning btn-sm <?php echo $row['status'] != 'accepted' ? 'disabled' : ''; ?>">
                                        <i class="bi bi-arrow-right-circle"></i> In Progress
                                    </a>
                                    <a href="update_order_status.php?orderID=<?php echo $row['orderID']; ?>&status=out_for_delivery" class="btn btn-info btn-sm <?php echo $row['status'] != 'in_progress' ? 'disabled' : ''; ?>">
                                        <i class="bi bi-truck"></i> Out for Delivery
                                    </a>
                                    <a href="update_order_status.php?orderID=<?php echo $row['orderID']; ?>&status=delivered" class="btn btn-success btn-sm <?php echo $row['status'] != 'out_for_delivery' ? 'disabled' : ''; ?>">
                                        <i class="bi bi-check-circle"></i> Delivered
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Delivered History Modal -->
    <div class="modal fade" id="deliveredHistoryModal" tabindex="-1" role="dialog" aria-labelledby="deliveredHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deliveredHistoryModalLabel">Delivered History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Order Date</th>
                                <th>Delivery Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $deliveredOrdersResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $row['Firstname'] . ' ' . $row['Lastname']; ?></td>
                                    <td><?php echo $row['order_date']; ?></td>
                                    <td><?php echo $row['delivery_date']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let sidebar = document.querySelector("nav");
        let closeBtn = document.querySelector(".sidebar-toggle");

        closeBtn.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });
    </script>
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
