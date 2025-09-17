<?php
include '../includes/connection.php';
session_start();

// ✅ Check login and role
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script>
        alert("Access denied. Customers only.");
        window.location = "../signin.php";
    </script>';
    exit();
}

$userID = (int) $_SESSION['userID'];

// ✅ Handle new ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($subject) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO tickets (customerID, subject, status, created_at) VALUES (?, ?, 'open', NOW())");
        $stmt->bind_param("is", $userID, $subject);
        if ($stmt->execute()) {
            $ticketID = $stmt->insert_id;

            // insert first message
            $stmtMsg = $conn->prepare("INSERT INTO ticket_messages (ticketID, customerID, message, created_at) VALUES (?, ?, ?, NOW())");
            $stmtMsg->bind_param("iis", $ticketID, $userID, $message);
            $stmtMsg->execute();
            $stmtMsg->close();

            header("Location: ticket.php?success=" . urlencode("Ticket created successfully!"));
            exit();
        }
        $stmt->close();
    }
}

// ✅ Handle close ticket
if (isset($_GET['close']) && is_numeric($_GET['close'])) {
    $ticketID = (int) $_GET['close'];
    $stmt = $conn->prepare("UPDATE tickets SET status='closed' WHERE ticketID = ? AND customerID = ?");
    $stmt->bind_param("ii", $ticketID, $userID);
    $stmt->execute();
    $stmt->close();
    header("Location: ticket.php?success=" . urlencode("Ticket closed successfully!"));
    exit();
}

// ✅ Fetch all tickets for this customer
$stmt = $conn->prepare("SELECT ticketID, subject, status, created_at FROM tickets WHERE customerID = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userID);
$stmt->execute();
$tickets = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets | De Chavez Waterhaus</title>
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
            <li><a href="orders.php"><i class="bi bi-cart"></i><span class="link-name">Orders</span></a></li>
            <li><a href="order_history.php"><i class="bi bi-clock-history"></i><span class="link-name">Order History</span></a></li>
            <li><a href="ticket.php" class="active"><i class="bi bi-inbox"></i><span class="link-name">Tickets</span></a></li>
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
            <div class="container-fluid">

                <!-- Alerts -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php elseif (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <!-- Create Ticket -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle text-primary"></i> Create New Ticket</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea name="message" rows="4" class="form-control" required></textarea>
                            </div>
                            <button type="submit" name="create_ticket" class="btn btn-primary">
                                <i class="bi bi-send"></i> Submit Ticket
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Ticket List -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-inbox"></i> My Tickets</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($tickets && $tickets->num_rows > 0): ?>
                                    <?php while ($row = $tickets->fetch_assoc()): ?>
                                        <?php
                                            $status = strtolower($row['status']);
                                            $badge = $status === 'open' ? 'warning' : 'secondary';
                                        ?>
                                        <tr>
                                            <td>#<?php echo $row['ticketID']; ?></td>
                                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                            <td><span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                            <td><?php echo date("M d, Y h:i A", strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <a href="view_ticket.php?id=<?php echo $row['ticketID']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <?php if ($status === 'open'): ?>
                                                    <a href="ticket.php?close=<?php echo $row['ticketID']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Close this ticket?');">
                                                        <i class="bi bi-x-circle"></i> Close
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">No tickets found.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div> <!-- end container -->
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
