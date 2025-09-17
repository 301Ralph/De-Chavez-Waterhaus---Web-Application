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

$userID = (int) $_SESSION['userID'];
$userName = $_SESSION['userName'] ?? "Customer";

// ✅ Fetch active orders (exclude completed/canceled)
$query = "SELECT orderID, order_date, total_amount, status 
          FROM `order` 
          WHERE customerID = ? AND status NOT IN ('completed', 'canceled', 'cancelled') 
          ORDER BY order_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders | De Chavez Waterhaus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
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
      </div>

      <div class="dash-content">
          <div class="card shadow-sm border-0">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">🛒 My Active Orders</h5>
              </div>
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="table table-hover align-middle">
                          <thead class="table-light">
                              <tr>
                                  <th>Order #</th>
                                  <th>Date</th>
                                  <th>Amount</th>
                                  <th>Status</th>
                                  <th>Actions</th>
                              </tr>
                          </thead>
                          <tbody>
<?php
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $status = strtolower($row['status']);
        if ($status === 'pending') $statusClass = 'warning';
        elseif ($status === 'processing') $statusClass = 'info';
        elseif ($status === 'out for delivery') $statusClass = 'primary';
        else $statusClass = 'secondary';

        echo "<tr>
                <td>#".htmlspecialchars($row['orderID'])."</td>
                <td>".date("M d, Y h:i A", strtotime($row['order_date']))."</td>
                <td>₱".number_format((float)$row['total_amount'], 2)."</td>
                <td><span class='badge bg-{$statusClass} text-capitalize'>".htmlspecialchars($row['status'])."</span></td>
                <td>
                    <a href='order_details.php?id={$row['orderID']}' class='btn btn-sm btn-outline-primary'>
                        <i class='bi bi-eye'></i> View
                    </a>";

        // Cancel button only for pending orders
        if ($status === 'pending') {
            echo " <a href='cancel_order.php?id={$row['orderID']}' 
                      class='btn btn-sm btn-outline-danger'
                      onclick=\"return confirm('Cancel this order?');\">
                      <i class='bi bi-x-circle'></i> Cancel
                   </a>";
        }

        // Track button for processing / out for delivery
        if ($status === 'processing' || $status === 'out for delivery') {
            echo " <a href='track_order.php?id={$row['orderID']}' 
                      class='btn btn-sm btn-outline-success'>
                      <i class='bi bi-geo-alt'></i> Track
                   </a>";
        }

        echo "</td></tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center text-muted'>No active orders right now.</td></tr>";
}
$stmt->close();
?>
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
