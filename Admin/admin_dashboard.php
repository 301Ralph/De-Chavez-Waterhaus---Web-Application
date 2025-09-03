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

// Initialize variables
$today = date('Y-m-d');
$dailyOrders = $scheduledDeliveries = $pendingOrders = 0;
$totalAccounts = $verifiedAccounts = $unverifiedAccounts = 0;

// Fetch today's orders count
$ordersQuery = "SELECT COUNT(*) as daily_orders FROM orders WHERE DATE(order_date) = '$today'";
$ordersResult = $conn->query($ordersQuery);
if ($ordersResult) {
    $dailyOrders = $ordersResult->fetch_assoc()['daily_orders'];
}

// Fetch today's deliveries count
$deliveriesQuery = "SELECT COUNT(*) as scheduled_deliveries FROM deliveries WHERE DATE(delivery_date) = '$today'";
$deliveriesResult = $conn->query($deliveriesQuery);
if ($deliveriesResult) {
    $scheduledDeliveries = $deliveriesResult->fetch_assoc()['scheduled_deliveries'];
}

// Fetch pending orders count
$pendingOrdersQuery = "SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'";
$pendingOrdersResult = $conn->query($pendingOrdersQuery);
if ($pendingOrdersResult) {
    $pendingOrders = $pendingOrdersResult->fetch_assoc()['pending_orders'];
}

// Fetch total accounts count
$totalAccountsQuery = "SELECT COUNT(*) as total_accounts FROM customers";
$totalAccountsResult = $conn->query($totalAccountsQuery);
if ($totalAccountsResult) {
    $totalAccounts = $totalAccountsResult->fetch_assoc()['total_accounts'];
}

// Fetch verified accounts count
$verifiedAccountsQuery = "SELECT COUNT(*) as verified_accounts FROM customers WHERE isVerified = 1";
$verifiedAccountsResult = $conn->query($verifiedAccountsQuery);
if ($verifiedAccountsResult) {
    $verifiedAccounts = $verifiedAccountsResult->fetch_assoc()['verified_accounts'];
}

// Calculate unverified accounts count
$unverifiedAccounts = $totalAccounts - $verifiedAccounts;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | De Chavez Waterhaus</title>
    <link rel="stylesheet" href="../Designs/admin-style.css">
    <!-- Bootstrap Icons CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart', 'bar']});
      google.charts.setOnLoadCallback(drawCharts);

      function drawCharts() {
        // Data for accounts chart
        var accountData = new google.visualization.DataTable();
        accountData.addColumn('string', 'Type');
        accountData.addColumn('number', 'Count');
        accountData.addRows([
          ['Total Accounts', <?php echo $totalAccounts; ?>],
          ['Verified Accounts', <?php echo $verifiedAccounts; ?>],
          ['Unverified Accounts', <?php echo $unverifiedAccounts; ?>]
        ]);

        var accountOptions = {
          'title': 'Accounts Overview',
          'width': 600,
          'height': 400,
          'legend': { position: 'none' },
          'annotations': {
            'alwaysOutside': true,
            'textStyle': {
              'fontSize': 12,
              'color': '#000',
              'auraColor': 'none'
            }
          }
        };

        var accountChart = new google.visualization.BarChart(document.getElementById('account_chart_div'));
        accountChart.draw(accountData, accountOptions);
      }
    </script>
    <style>
        body {
            background-color: cyan;
        }
        .charts {
            display: flex;
            flex-direction: column;
            margin-top: 30px;
        }
        .chart-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .chart-container > div {
            flex: 1;
            margin: 0 10px;
        }
        .title, .charts div > div {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
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
                <i class="bi bi-speedometer2"></i>
                <span class="text">Dashboard</span>
            </div>

            <div class="boxes">
                <div class="box box1">
                    <i class="bi bi-cart-check"></i>
                    <span class="text">Today's Order</span>
                    <span class="number"><?php echo $dailyOrders; ?></span>
                </div>
                <div class="box box2">
                    <i class="bi bi-truck"></i>
                    <span class="text">Scheduled Deliveries</span>
                    <span class="number"><?php echo $scheduledDeliveries; ?></span>
                </div>
                <div class="box box3">
                    <i class="bi bi-hourglass-split"></i>
                    <span class="text">Pending Orders</span>
                    <span class="number"><?php echo $pendingOrders; ?></span>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts">
                <div class="chart-container">
                    <div>
                        <div>Accounts Overview</div>
                        <div id="account_chart_div"></div>
                    </div>
                </div>
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
