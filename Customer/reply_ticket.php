<?php
include '../includes/connection.php';
session_start();

// ✅ Ensure user is logged in as customer
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script>
        alert("Access denied.");
        window.location = "../signin.php";
    </script>';
    exit();
}

$userID = (int) $_SESSION['userID'];

if (isset($_POST['reply_ticket'])) {
    $ticketID = (int) $_POST['ticketID'];
    $message = trim($_POST['message']);

    if ($message === "") {
        echo '<script>
            alert("Message cannot be empty.");
            window.location = "view_ticket.php?ticketID=' . $ticketID . '";
        </script>';
        exit();
    }

    // ✅ Insert reply
    $stmt = $conn->prepare("INSERT INTO messages (ticketID, senderID, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $ticketID, $userID, $message);

    if ($stmt->execute()) {
        echo '<script>
            window.location = "view_ticket.php?ticketID=' . $ticketID . '";
        </script>';
    } else {
        echo '<script>
            alert("Error sending reply. Try again.");
            window.location = "view_ticket.php?ticketID=' . $ticketID . '";
        </script>';
    }
    $stmt->close();
} else {
    header("Location: ticket.php");
    exit();
}
