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

$userID = $_GET['userID'];

// Fetch customer details
$query = "SELECT * FROM customers WHERE userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo '<script>alert("Customer not found."); window.location = "customer_management.php";</script>';
    exit();
}

// Prevent resubmission if already approved or rejected
if ($user['verification_status'] != 'pending') {
    echo '<script>alert("Verification already completed."); window.location = "customer_management.php";</script>';
    exit();
}

// Handle verification action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $reason = htmlspecialchars($_POST['reason']);

    if ($action == 'approve') {
        $status = 'approved';
    } elseif ($action == 'reject') {
        $status = 'rejected';
    }

    $updateQuery = "UPDATE customers SET verification_status = ?, verification_reason = ? WHERE userID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $status, $reason, $userID);
    if ($updateStmt->execute()) {
        echo '<script>alert("Customer verification status updated."); window.location = "customer_management.php";</script>';
    } else {
        echo '<script>alert("Error updating verification status.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Customer | De Chavez Waterhaus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .close-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify Customer</h2>
        <div class="card p-4 position-relative">
            <span class="close-btn" onclick="window.location.href='customer_management.php'">&times;</span>
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['Firstname']) . ' ' . htmlspecialchars($user['Lastname']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['ContactNumber']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['Address']); ?></p>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <p><strong>Verification Status:</strong> <?php echo htmlspecialchars($user['verification_status']); ?></p>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <?php if (isset($user['VerificationFile']) && !empty($user['VerificationFile'])): ?>
                        <p><strong>Uploaded ID:</strong></p>
                        <img src="<?php echo htmlspecialchars($user['VerificationFile']); ?>" alt="Uploaded ID" class="img-fluid">
                    <?php else: ?>
                        <p>No ID uploaded.</p>
                    <?php endif; ?>
                </div>
            </div>
            <form method="post" action="verify_customer.php?userID=<?php echo $userID; ?>">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="reason" class="form-label">Reason (for rejection):</label>
                        <textarea class="form-control" id="reason" name="reason"></textarea>
                    </div>
                </div>
                <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
                <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
