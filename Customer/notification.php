<?php
include '../includes/connection.php';
session_start();

// Check if the user is logged in and is a customer
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script>alert("Access denied. Customers only."); window.location = "../login.php";</script>';
    exit();
}

$userID = $_SESSION['userID'];

// Handle delete single notification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteNotification'])) {
    $notificationID = (int) $_POST['notificationID'];
    $deleteQuery = "DELETE FROM notifications WHERE id = $notificationID AND user_id = $userID";
    $conn->query($deleteQuery);
    header("Location: notification.php");
    exit();
}

// Handle delete all notifications
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteAll'])) {
    $deleteAllQuery = "DELETE FROM notifications WHERE user_id = $userID";
    $conn->query($deleteAllQuery);
    header("Location: notification.php");
    exit();
}

// Handle mark as read (single)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['markAsRead'])) {
    $notificationID = (int) $_POST['notificationID'];
    $updateQuery = "UPDATE notifications SET is_read = 1 WHERE id = $notificationID AND user_id = $userID";
    $conn->query($updateQuery);
    header("Location: notification.php");
    exit();
}

// Handle mark all as read
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['markAllAsRead'])) {
    $updateAllQuery = "UPDATE notifications SET is_read = 1 WHERE user_id = $userID AND is_read = 0";
    $conn->query($updateAllQuery);
    header("Location: notification.php");
    exit();
}

// Fetch notifications
$notificationsQuery = "
    SELECT id, message, is_read, created_at 
    FROM notifications 
    WHERE user_id = $userID
    ORDER BY created_at DESC
";
$notificationsResult = $conn->query($notificationsQuery);

// Function to auto-detect links based on message keywords
function getNotificationLink($message) {
    $messageLower = strtolower($message);
    if (strpos($messageLower, 'order') !== false) {
        return "orders.php";
    } elseif (strpos($messageLower, 'ticket') !== false) {
        return "ticket.php";
    } elseif (strpos($messageLower, 'profile') !== false) {
        return "profile.php";
    } else {
        return "#";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="logo">
            <img src="../images/logo.png" alt="Logo">
            <span class="logo_name">De Chavez</span>
        </div>

        <ul class="nav-links">
            <li><a href="customer_dashboard.php"><i class="bi bi-house-door"></i><span class="link-name">Home</span></a></li>
            <li><a href="products.php"><i class="bi bi-box"></i><span class="link-name">Products</span></a></li>
            <li><a href="orders.php"><i class="bi bi-cart"></i><span class="link-name">Orders</span></a></li>
            <li><a href="order_history.php"><i class="bi bi-clock-history"></i><span class="link-name">Order History</span></a></li>
            <li><a href="ticket.php"><i class="bi bi-inbox"></i><span class="link-name">Tickets</span></a></li>
            <li><a href="notification.php" class="active"><i class="bi bi-bell"></i><span class="link-name">Notification</span></a></li>
            <li><a href="profile.php"><i class="bi bi-person"></i><span class="link-name">Profile</span></a></li>
            <li><a href="../logout.php"><i class="bi bi-box-arrow-right"></i><span class="link-name">Logout</span></a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <i class="bi bi-list sidebar-toggle"></i>
            <h1 class="h3 mb-0">Notifications</h1>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0"><i class="bi bi-bell me-2"></i>Your Notifications</h5>
                    <?php if ($notificationsResult->num_rows > 0) { ?>
                        <div class="d-flex gap-2">
                            <!-- Mark All as Read -->
                            <form method="POST" class="m-0">
                                <button type="submit" name="markAllAsRead" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-check2-all"></i> Mark All as Read
                                </button>
                            </form>
                            <!-- Delete All -->
                            <form method="POST" class="m-0" onsubmit="return confirm('Delete ALL notifications? This cannot be undone.');">
                                <button type="submit" name="deleteAll" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Delete All
                                </button>
                            </form>
                        </div>
                    <?php } ?>
                </div>

                <?php if ($notificationsResult->num_rows > 0) { ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($notification = $notificationsResult->fetch_assoc()) { 
                                    $link = getNotificationLink($notification['message']); ?>
                                    <tr class="<?php echo $notification['is_read'] ? '' : 'table-warning'; ?>">
                                        <td>
                                            <?php if ($link !== "#") { ?>
                                                <a href="<?php echo $link; ?>" class="text-decoration-none fw-semibold">
                                                    <?php echo htmlspecialchars($notification['message']); ?>
                                                </a>
                                            <?php } else { ?>
                                                <?php echo htmlspecialchars($notification['message']); ?>
                                            <?php } ?>
                                        </td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></td>
                                        <td class="text-center">
                                            <!-- Mark as Read -->
                                            <?php if (!$notification['is_read']) { ?>
                                                <form action="notification.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="notificationID" value="<?php echo $notification['id']; ?>">
                                                    <button type="submit" name="markAsRead" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-check2-circle"></i> Mark as Read
                                                    </button>
                                                </form>
                                            <?php } ?>
                                            <!-- Delete Single -->
                                            <form action="notification.php" method="POST" onsubmit="return confirm('Delete this notification?');" class="d-inline">
                                                <input type="hidden" name="notificationID" value="<?php echo $notification['id']; ?>">
                                                <button type="submit" name="deleteNotification" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>No notifications yet.
                    </div>
                <?php } ?>
            </div>
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
