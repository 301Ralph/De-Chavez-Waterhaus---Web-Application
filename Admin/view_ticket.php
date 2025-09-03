<?php
include '../includes/connection.php';
session_start();

// Check if the user is logged in and has the right role
if (!isset($_SESSION['userID'])) {
    echo '<script type="text/javascript">
        alert("Access denied.");
        window.location = "../login.php";
        </script>';
    exit();
}

$userID = $_SESSION['userID'];
$role = $_SESSION['role'];
$ticketID = $_GET['ticketID'];

// Fetch ticket and messages
$ticketQuery = "
    SELECT tickets.subject, tickets.status, tickets.created_at, customers.Firstname, customers.Lastname 
    FROM tickets 
    LEFT JOIN customers ON tickets.customerID = customers.userID
    WHERE tickets.ticketID = '$ticketID'";
$ticketResult = $conn->query($ticketQuery);

if (!$ticketResult || $ticketResult->num_rows == 0) {
    echo '<script type="text/javascript">
        alert("Ticket not found.");
        window.location = "manage_ticket.php";
        </script>';
    exit();
}

$ticket = $ticketResult->fetch_assoc();

$messagesQuery = "SELECT * FROM messages WHERE ticketID = '$ticketID' ORDER BY sent_at ASC";
$messagesResult = $conn->query($messagesQuery);

// Handle new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $message = $_POST['message'];
    $sender = $role;

    $messageQuery = "INSERT INTO messages (ticketID, sender, message) VALUES ('$ticketID', '$sender', '$message')";
    if ($conn->query($messageQuery) === TRUE) {
        echo '<script type="text/javascript">
            alert("Message sent successfully.");
            window.location = "view_ticket.php?ticketID='.$ticketID.'";
            </script>';
    } else {
        echo '<script type="text/javascript">
            alert("Failed to send message.");
            window.location = "view_ticket.php?ticketID='.$ticketID.'";
            </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ticket | De Chavez Waterhaus</title>
    <link rel="stylesheet" href="../Designs/admin-style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
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
        .ticket-details {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .message.customer {
            background-color: #f1f1f1;
        }
        .message.admin {
            background-color: #e2e6ea;
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
    </nav>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <i class="bi bi-list sidebar-toggle"></i>
            <h1>View Ticket</h1>
        </div>

        <div class="ticket-details">
            <h5>Subject: <?php echo $ticket['subject']; ?> (<?php echo ucfirst($ticket['status']); ?>)</h5>
            <p><strong>Customer Name:</strong> <?php echo $ticket['Firstname'] . ' ' . $ticket['Lastname']; ?></p>
            <p><strong>Submitted:</strong> <?php echo $ticket['created_at']; ?></p>
        </div>

        <h2>Messages</h2>
        <div class="messages">
            <?php while ($message = $messagesResult->fetch_assoc()) { ?>
                <div class="message <?php echo $message['sender']; ?>">
                    <p><?php echo $message['message']; ?></p>
                    <small><strong><?php echo ucfirst($message['sender']); ?>:</strong> <?php echo $message['sent_at']; ?></small>
                </div>
            <?php } ?>
        </div>

        <h2 class="mt-5">Send Message</h2>
        <form action="view_ticket.php?ticketID=<?php echo $ticketID; ?>" method="post">
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" name="send_message">Send Message</button>
        </form>
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
