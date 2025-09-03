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

// Fetch orders and associated delivery information
$ordersQuery = "
    SELECT orders.orderID, customers.Firstname, customers.Lastname, orders.order_date, orders.status, orders.gcash_receipt, deliveries.delivery_date 
    FROM orders 
    LEFT JOIN deliveries ON orders.orderID = deliveries.orderID 
    LEFT JOIN customers ON orders.customerID = customers.userID
    WHERE orders.status = 'pending'";
$ordersResult = $conn->query($ordersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | De Chavez Waterhaus</title>
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
                <li><a href="settings.php">
                    <i class="bi bi-gear"></i>
                    <span class="link-name">Settings</span>
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
        </div>

        <div class="dash-content">
            <div class="title">
                <i class="bi bi-cart"></i>
                <span class="text">Order Management</span>
            </div>

            <div class="order-list">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Delivery Date</th>
                            <th>GCash Receipt</th>
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
                                    <?php if ($row['gcash_receipt']) { ?>
                                        <button class="btn btn-info btn-sm view-receipt-btn" data-receipt="<?php echo $row['gcash_receipt']; ?>">
                                            View Receipt
                                        </button>
                                    <?php } else { ?>
                                        No Receipt
                                    <?php } ?>
                                </td>
                                <td>
                                    <a href="update_order_status.php?orderID=<?php echo $row['orderID']; ?>&status=accepted" class="btn btn-success btn-sm">
                                        <i class="bi bi-check-circle"></i> Accept
                                    </a>
                                    <button class="btn btn-danger btn-sm reject-btn" data-id="<?php echo $row['orderID']; ?>">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Reject Order Modal -->
    <div class="modal fade" id="rejectOrderModal" tabindex="-1" role="dialog" aria-labelledby="rejectOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectOrderModalLabel">Reject Order</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="rejectOrderForm" action="update_order_status.php" method="post">
                        <input type="hidden" id="rejectOrderID" name="orderID">
                        <div class="form-group">
                            <label for="rejectReason">Reason for Rejection</label>
                            <textarea class="form-control" id="rejectReason" name="reason" rows="3" required></textarea>
                        </div>
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-danger">Reject Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View GCash Receipt Modal -->
    <div class="modal fade" id="viewReceiptModal" tabindex="-1" role="dialog" aria-labelledby="viewReceiptModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReceiptModalLabel">GCash Receipt</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="gcashReceiptImage" src="" class="img-fluid" alt="GCash Receipt">
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

        // JavaScript to handle reject order button click
        document.querySelectorAll('.reject-btn').forEach(button => {
            button.addEventListener('click', function() {
                let orderID = this.getAttribute('data-id');
                document.getElementById('rejectOrderID').value = orderID;
                $('#rejectOrderModal').modal('show');
            });
        });

        // JavaScript to handle view receipt button click
        document.querySelectorAll('.view-receipt-btn').forEach(button => {
            button.addEventListener('click', function() {
                let receiptUrl = this.getAttribute('data-receipt');
                document.getElementById('gcashReceiptImage').src = receiptUrl;
                $('#viewReceiptModal').modal('show');
            });
        });
    </script>
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
