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

// Fetch customer details
$customersQuery = "SELECT userID, Firstname, Lastname, Email, ContactNumber, verification_status FROM customers";
$customersResult = $conn->query($customersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management | De Chavez Waterhaus</title>
    <link rel="stylesheet" href="../Designs/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: cyan;
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
        </div>

        <div class="dash-content">
            <div class="title">
                <i class="bi bi-people"></i>
                <span class="text">Customer Management</span>
            </div>

            <div class="customer-list">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Verification Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $customersResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['Firstname'] . ' ' . $row['Lastname']; ?></td>
                                <td><?php echo $row['Email']; ?></td>
                                <td><?php echo $row['ContactNumber']; ?></td>
                                <td><?php echo ucfirst($row['verification_status']); ?></td>
                                <td>
                                    <?php if ($row['verification_status'] == 'pending') { ?>
                                        <a href="verify_customer.php?userID=<?php echo $row['userID']; ?>" class="btn btn-success btn-sm">
                                            <i class="bi bi-check-circle"></i> Verify
                                        </a>
                                    <?php } elseif ($row['verification_status'] == 'approved') { ?>
                                        <span class="btn btn-secondary btn-sm disabled">
                                            <i class="bi bi-check-circle"></i> Verified
                                        </span>
                                    <?php } ?>
                                    <a href="delete_customer.php?userID=<?php echo $row['userID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this customer?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <script>
        let sidebar = document.querySelector("nav");
        let closeBtn = document.querySelector(".sidebar-toggle");

        closeBtn.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });
    </script>
</body>
</html>
