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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<script>
        alert("Invalid order ID.");
        window.location = "orders.php";
    </script>';
    exit();
}

$orderID = (int) $_GET['id'];
$userID  = (int) $_SESSION['userID'];

// ✅ Fetch order details
$stmt = $conn->prepare("SELECT orderID, order_date, total_amount, status 
                        FROM `order` 
                        WHERE orderID = ? AND customerID = ?");
$stmt->bind_param("ii", $orderID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $order = $result->fetch_assoc();
} else {
    echo '<script>
        alert("Order not found.");
        window.location = "orders.php";
    </script>';
    exit();
}
$stmt->close();

// ✅ Define progress steps
$steps = ["pending", "processing", "out for delivery", "completed"];
$currentStatus = strtolower($order['status']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Track Order #<?php echo $order['orderID']; ?> | De Chavez Waterhaus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
  <style>
    .stepper {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    .step {
        text-align: center;
        flex: 1;
        position: relative;
    }
    .step::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 50%;
        height: 4px;
        width: 100%;
        background: #dee2e6;
        z-index: -1;
    }
    .step:first-child::before {
        display: none;
    }
    .circle {
        height: 30px;
        width: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        margin-bottom: 10px;
    }
    .completed {
        background: #0d6efd;
        color: white;
    }
    .pending {
        background: #dee2e6;
        color: #6c757d;
    }
  </style>
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
                  <h5 class="mb-0">🚚 Tracking Order #<?php echo $order['orderID']; ?></h5>
                  <a href="orders.php" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
              </div>
              <div class="card-body">
                  <p><strong>Date:</strong> <?php echo date("M d, Y h:i A", strtotime($order['order_date'])); ?></p>
                  <p><strong>Amount:</strong> ₱<?php echo number_format((float)$order['total_amount'], 2); ?></p>
                  <p><strong>Current Status:</strong> <span class="badge bg-primary text-capitalize"><?php echo htmlspecialchars($order['status']); ?></span></p>
                  
                  <!-- Stepper -->
                  <div class="stepper">
                      <?php foreach ($steps as $index => $step): 
                          $isCompleted = array_search($currentStatus, $steps) >= $index;
                      ?>
                          <div class="step">
                              <div class="circle <?php echo $isCompleted ? 'completed' : 'pending'; ?>">
                                  <?php echo $index + 1; ?>
                              </div>
                              <div class="text-capitalize"><?php echo $step; ?></div>
                          </div>
                      <?php endforeach; ?>
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
