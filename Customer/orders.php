<?php
include '../includes/connection.php';
session_start();

// Check login and role
if (!isset($_SESSION['userID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo '<script>
        alert("Access denied. Customers only.");
        window.location = "../signin.php";
    </script>';
    exit();
}

$userID = (int) $_SESSION['userID'];
$userName = isset($_SESSION['userName']) ? $_SESSION['userName'] : "Customer";

// Fetch orders (using your table name `order`)
$orders = [];
if ($stmt = $conn->prepare("
    SELECT orderID, order_date, total_amount, status
    FROM `order`
    WHERE customerID = ?
    ORDER BY order_date DESC
")) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | De Chavez Waterhaus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css"> <!-- shared CSS -->
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="logo">
            <img src="../assets/images/Logo.png" alt="Logo">
            <span class="logo_name">De Chavez Waterhaus</span>
        </div>
        <ul class="nav-links">
            <li><a href="customer_dashboard.php"><i class="bi bi-house-door"></i><span class="link-name">Home</span></a></li>
            <li><a href="products.php"><i class="bi bi-box"></i><span class="link-name">Products</span></a></li>
            <li><a href="orders.php" class="active"><i class="bi bi-cart"></i><span class="link-name">Orders</span></a></li>
            <li><a href="order_history.php"><i class="bi bi-clock-history"></i><span class="link-name">Order History</span></a></li>
            <li><a href="ticket.php"><i class="bi bi-inbox"></i><span class="link-name">Tickets</span></a></li>
            <li><a href="notification.php"><i class="bi bi-bell"></i><span class="link-name">Notification</span></a></li>
            <li><a href="profile.php"><i class="bi bi-person"></i><span class="link-name">Profile</span></a></li>
            <li><a href="../actions/logout.php"><i class="bi bi-box-arrow-right"></i><span class="link-name">Logout</span></a></li>
        </ul>
    </nav>

    <!-- Content -->
    <div class="content">
        <div class="topbar">
            <i class="bi bi-list sidebar-toggle" id="sidebarToggle"></i>
            <h1 class="h3 fw-bold">📦 My Orders</h1>
        </div>

        <div class="dash-content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Your Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Order #</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($orders) > 0): ?>
                                    <?php foreach ($orders as $order): ?>
                                        <?php
                                            $status = strtolower($order['status']);
                                            if ($status === 'completed') $statusClass = 'success';
                                            elseif ($status === 'pending') $statusClass = 'warning';
                                            elseif ($status === 'canceled' || $status === 'cancelled') $statusClass = 'danger';
                                            else $statusClass = 'secondary';

                                            $orderNum = htmlspecialchars($order['orderID']);
                                            $dateStr = $order['order_date'] ? date("M d, Y h:i A", strtotime($order['order_date'])) : '-';
                                            $amount = isset($order['total_amount']) ? '₱' . number_format((float)$order['total_amount'], 2) : '-';
                                            $statusLabel = ucfirst($status);
                                        ?>
                                        <tr>
                                            <td>#<?= $orderNum ?></td>
                                            <td><?= $dateStr ?></td>
                                            <td><?= $amount ?></td>
                                            <td><span class="badge bg-<?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                            <td>
                                                <?php if ($status === 'pending'): ?>
                                                    <form action="cancel_order.php" method="POST" onsubmit="return confirm('Cancel this order?');">
                                                        <input type="hidden" name="orderID" value="<?= $orderNum ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>No Action</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No orders found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.querySelector(".sidebar");
        const toggle = document.getElementById("sidebarToggle");

        toggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            document.querySelector(".content").classList.toggle("active");
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
