<?php
include '../includes/connection.php';
session_start();

// ✅ Ensure user is logged in and is customer
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script>
        alert("Access denied. Customers only.");
        window.location = "../signin.php";
    </script>';
    exit();
}

$userID = (int) $_SESSION['userID'];

if (isset($_POST['create_ticket'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if ($subject === "" || $message === "") {
        echo '<script>
            alert("Please fill out all fields.");
            window.location = "ticket.php";
        </script>';
        exit();
    }

    // ✅ Insert into tickets table
    $stmt = $conn->prepare("INSERT INTO tickets (customerID, subject, status, created_at) VALUES (?, ?, 'open', NOW())");
    $stmt->bind_param("is", $userID, $subject);
    if ($stmt->execute()) {
        $ticketID = $stmt->insert_id;
        $stmt->close();

        // ✅ Insert first message into messages table
        $stmtMsg = $conn->prepare("INSERT INTO messages (ticketID, senderID, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmtMsg->bind_param("iis", $ticketID, $userID, $message);
        $stmtMsg->execute();
        $stmtMsg->close();

        echo '<script>
            alert("Ticket created successfully!");
            window.location = "ticket.php";
        </script>';
    } else {
        echo '<script>
            alert("Error creating ticket. Please try again.");
            window.location = "ticket.php";
        </script>';
    }
} else {
    // If accessed directly without form submit
    header("Location: ticket.php");
    exit();
}
