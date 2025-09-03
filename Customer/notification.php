<?php
include '../includes/connection.php';
session_start();

// Check if the user is logged in and is a customer
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script type="text/javascript">
        alert("Access denied. Customers only.");
        window.location = "../login.php";
        </script>';
    exit();
}

$userID = $_SESSION['userID'];

// Fetch notifications for the logged-in customer
$notificationsQuery = "
    SELECT notificationID, message, created_at 
    FROM notifications 
    WHERE userID = $userID
    ORDER BY created_at DESC
";
$notificationsResult = $conn->query($notificationsQuery);

if (!$notificationsResult) {
    die("Query failed: " . $conn->error);
}

// Handle deletion of a notification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteNotification'])) {
    $notificationID = $_POST['notificationID'];
    $deleteQuery = "DELETE FROM notifications WHERE notificationID = $notificationID AND userID = $userID";
    if ($conn->query($deleteQuery) === TRUE) {
        echo '<script type="text/javascript">
            alert("Notification deleted successfully.");
            window.location = "notification.php";
            </script>';
    } else {
        echo '<script type="text/javascript">
            alert("Failed to delete notification.");
            window.location = "notification.php";
            </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | De Chavez Waterhaus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Designs/custom-style.css">
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
            <h1>Notifications</h1>
        </div>

        <h2>Your Notifications</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($notification = $notificationsResult->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $notification['message']; ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($notification['created_at'])); ?></td>
                            <td>
                                <form action="notification.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                    <input type="hidden" name="notificationID" value="<?php echo $notification['notificationID']; ?>">
                                    <button type="submit" name="deleteNotification" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const sidebar = document.querySelector(".sidebar");
        const toggle = document.querySelector(".sidebar-toggle");

        toggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
