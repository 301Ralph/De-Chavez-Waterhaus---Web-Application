<?php
include '../includes/connection.php';
session_start();

// ✅ Ensure only customers can access
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script>
        alert("Access denied. Customers only.");
        window.location = "../signin.php";
    </script>';
    exit();
}

$userID = (int) $_SESSION['userID'];

// ✅ Get ticket ID
if (!isset($_GET['ticketID'])) {
    header("Location: ticket.php");
    exit();
}
$ticketID = (int) $_GET['ticketID'];

// ✅ Fetch ticket details
$stmt = $conn->prepare("SELECT t.*, u.name AS customer_name 
                        FROM tickets t
                        JOIN users u ON t.customerID = u.id
                        WHERE t.ticketID = ? AND t.customerID = ?");
$stmt->bind_param("ii", $ticketID, $userID);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ticket) {
    echo '<script>
        alert("Ticket not found.");
        window.location = "ticket.php";
    </script>';
    exit();
}

// ✅ Fetch messages
$msgStmt = $conn->prepare("SELECT m.*, u.name 
                           FROM messages m
                           JOIN users u ON m.senderID = u.id
                           WHERE m.ticketID = ?
                           ORDER BY m.created_at ASC");
$msgStmt->bind_param("i", $ticketID);
$msgStmt->execute();
$messages = $msgStmt->get_result();
$msgStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Ticket - Customer Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h3>Ticket: <?= htmlspecialchars($ticket['subject']); ?></h3>
    <p>Status: <b><?= ucfirst($ticket['status']); ?></b> | Created at: <?= $ticket['created_at']; ?></p>
    <hr>

    <div class="card mb-3">
        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
            <?php while ($msg = $messages->fetch_assoc()): ?>
                <div class="mb-3">
                    <strong><?= htmlspecialchars($msg['name']); ?></strong> 
                    <small class="text-muted">(<?= $msg['created_at']; ?>)</small>
                    <p><?= nl2br(htmlspecialchars($msg['message'])); ?></p>
                </div>
                <hr>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- ✅ Reply form -->
    <form action="reply_ticket.php" method="POST">
        <input type="hidden" name="ticketID" value="<?= $ticketID; ?>">
        <div class="mb-3">
            <textarea name="message" class="form-control" rows="3" placeholder="Type your reply..." required></textarea>
        </div>
        <button type="submit" name="reply_ticket" class="btn btn-primary">Send Reply</button>
        <a href="ticket.php" class="btn btn-secondary">Back to Tickets</a>
    </form>
</div>
</body>
</html>
