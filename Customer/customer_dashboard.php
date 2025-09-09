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

/*
 * Fetch counts using prepared statements to avoid SQL errors.
 * Your orders table is named `order` with fields orderID, customerID, order_date, total_amount, status
 */

// total orders
$orderCount = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) FROM `order` WHERE customerID = ?")) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->bind_result($orderCount);
    $stmt->fetch();
    $stmt->close();
}

// completed orders (history)
$historyCount = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) FROM `order` WHERE customerID = ? AND status = ?")) {
    $statusCompleted = 'completed';
    $stmt->bind_param("is", $userID, $statusCompleted);
    $stmt->execute();
    $stmt->bind_result($historyCount);
    $stmt->fetch();
    $stmt->close();
}

// unread notifications (notifications table assumed created earlier)
$notifCount = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0")) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->bind_result($notifCount);
    $stmt->fetch();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | De Chavez Waterhaus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css"> <!-- your shared dashboard CSS -->
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="logo">
            <img src="../assets/images/Logo.png" alt="Logo">
            <span class="logo_name">De Chavez Waterhaus</span>
        </div>
        <ul class="nav-links">
            <li><a href="customer_dashboard.php" class="active"><i class="bi bi-house-door"></i><span class="link-name">Home</span></a></li>
            <li><a href="products.php"><i class="bi bi-box"></i><span class="link-name">Products</span></a></li>
            <li><a href="orders.php"><i class="bi bi-cart"></i><span class="link-name">Orders</span></a></li>
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
            <h1 class="h3 fw-bold">Customer Dashboard</h1>
        </div>

        <div class="dash-content">
            <div class="row g-4">
                <!-- Welcome -->
                <div class="col-12">
                    <div class="card shadow-sm border-0 p-4 text-center">
                        <h4 class="mb-0">👋 Welcome back, <span class="fw-bold text-primary"><?php echo htmlspecialchars($userName); ?></span>!</h4>
                    </div>
                </div>

                <!-- Stat cards -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1">Total Orders</h6>
                            <h2 class="fw-bold text-primary mb-0"><?php echo (int)$orderCount; ?></h2>
                        </div>
                        <i class="bi bi-cart-check-fill text-primary fs-1"></i>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1">Completed Orders</h6>
                            <h2 class="fw-bold text-success mb-0"><?php echo (int)$historyCount; ?></h2>
                        </div>
                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1">Unread Notifications</h6>
                            <h2 class="fw-bold text-danger mb-0"><?php echo (int)$notifCount; ?></h2>
                        </div>
                        <i class="bi bi-bell-fill text-danger fs-1"></i>
                    </div>
                </div>
            </div>
                      <!-- Favorite Products / Quick Reorder -->
          <div class="row g-4 mt-1">
              <div class="col-12">
                  <div class="card shadow-sm border-0">
                      <div class="card-header bg-white d-flex justify-content-between align-items-center">
                          <h5 class="mb-0">⭐ Favorite Products</h5>
                          <a href="products.php" class="btn btn-sm btn-outline-primary">Browse All</a>
                      </div>
                      <div class="card-body">
                          <div class="row g-3">
<?php
// Fetch top 3 most ordered products by this customer
$query = "
    SELECT p.productID, p.ProductName, p.price, COUNT(oi.productID) as total_orders
    FROM order_items oi
    INNER JOIN product p ON oi.productID = p.productID
    INNER JOIN `order` o ON oi.orderID = o.orderID
    WHERE o.customerID = ?
    GROUP BY p.productID, p.ProductName, p.price
    ORDER BY total_orders DESC
    LIMIT 3
";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            echo "
            <div class='col-md-4'>
                <div class='card h-100 border-0 shadow-sm'>
                    <div class='card-body text-center'>
                        <h6 class='fw-bold'>".htmlspecialchars($row['ProductName'])."</h6>
                        <p class='text-muted mb-1'>₱".number_format((float)$row['price'], 2)."</p>
                        <small class='text-muted'>Ordered ".(int)$row['total_orders']."x</small>
                        <div class='mt-3'>
                            <a href='products.php?reorder=".urlencode($row['productID'])."' class='btn btn-sm btn-primary'>
                                <i class='bi bi-arrow-repeat'></i> Reorder
                            </a>
                        </div>
                    </div>
                </div>
            </div>";
        }
    } else {
        echo "<div class='col-12 text-center text-muted'>No favorite products yet.</div>";
    }
    $stmt->close();
}
?>
                          </div>
                      </div>
                  </div>
              </div>
          </div>


            <!-- Recent Orders -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">📋 Recent Orders</h5>
                            <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
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
                                        </tr>
                                    </thead>
                                    <tbody>
<?php
// Use prepared statement to fetch recent orders (limit 5)
if ($stmt = $conn->prepare("SELECT orderID, order_date, total_amount, status FROM `order` WHERE customerID = ? ORDER BY order_date DESC LIMIT 5")) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            // map status to bootstrap badge class
            $status = strtolower($row['status'] ?? '');
            if ($status === 'completed') $statusClass = 'success';
            elseif ($status === 'pending') $statusClass = 'warning';
            elseif ($status === 'canceled' || $status === 'cancelled') $statusClass = 'danger';
            else $statusClass = 'secondary';

            $orderNum = htmlspecialchars($row['orderID']);
            $dateStr = $row['order_date'] ? date("M d, Y h:i A", strtotime($row['order_date'])) : '-';
            $amount = isset($row['total_amount']) ? '₱' . number_format((float)$row['total_amount'], 2) : '-';
            $statusLabel = htmlspecialchars(ucfirst($status));

            echo "<tr>
                    <td>#{$orderNum}</td>
                    <td>{$dateStr}</td>
                    <td>{$amount}</td>
                    <td><span class='badge bg-{$statusClass} text-capitalize'>{$statusLabel}</span></td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4' class='text-center text-muted'>No recent orders found.</td></tr>";
    }
    $stmt->close();
} else {
    // fallback if prepare failed
    echo "<tr><td colspan='4' class='text-center text-danger'>Unable to load recent orders.</td></tr>";
}
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end recent orders -->

        </div> <!-- end dash-content -->
    </div> <!-- end content -->

    <script>
        const sidebar = document.querySelector(".sidebar");
        const toggle = document.getElementById("sidebarToggle") || document.querySelector(".sidebar-toggle");

        toggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            // adjust main content margin by toggling .content active class if needed
            document.querySelector(".content").classList.toggle("active");
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
