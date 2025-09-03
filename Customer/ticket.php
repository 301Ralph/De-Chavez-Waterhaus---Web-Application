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
$ticketsQuery = "SELECT * FROM tickets WHERE customerID = '$userID'";
$ticketsResult = $conn->query($ticketsQuery);

// Handle new ticket creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_ticket'])) {
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $ticketQuery = "INSERT INTO tickets (customerID, subject, status, created_at) VALUES ('$userID', '$subject', 'open', NOW())";
    if ($conn->query($ticketQuery) === TRUE) {
        $ticketID = $conn->insert_id;
        $messageQuery = "INSERT INTO messages (ticketID, sender, message) VALUES ('$ticketID', 'customer', '$message')";
        $conn->query($messageQuery);

        echo '<script type="text/javascript">
            alert("Ticket created successfully.");
            window.location = "ticket.php";
            </script>';
    } else {
        echo '<script type="text/javascript">
            alert("Failed to create ticket.");
            window.location = "ticket.php";
            </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets | De Chavez Waterhaus</title>
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
        .ticket {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px;
            border-radius: 5px;
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
            <h1>Tickets</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                Create Ticket
            </button>
        </div>

        <h2>Your Tickets</h2>
        <div class="tickets">
            <?php while ($ticket = $ticketsResult->fetch_assoc()) { ?>
                <div class="ticket">
                    <h5>Subject: <?php echo $ticket['subject']; ?> (<?php echo ucfirst($ticket['status']); ?>)</h5>
                    <p><strong>Created at:</strong> <?php echo $ticket['created_at']; ?></p>
                    <a href="view_ticket.php?ticketID=<?php echo $ticket['ticketID']; ?>" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i> View
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Create Ticket Modal -->
    <div class="modal fade" id="createTicketModal" tabindex="-1" aria-labelledby="createTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTicketModalLabel">Create New Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="ticket.php" method="POST">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" name="create_ticket">Create Ticket</button>
                    </form>
                </div>
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
