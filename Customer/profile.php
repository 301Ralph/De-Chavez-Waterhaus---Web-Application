<?php
include '../includes/connection.php';
session_start();

// Check if the user is logged in and is customer
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script type="text/javascript">
        alert("Access denied. Customers only.");
        window.location = "../login.php";
        </script>';
    exit();
}

// Fetch user profile details from the customers table
$userID = $_SESSION['userID'];
$query = "SELECT * FROM customers WHERE userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);

    $updateQuery = "UPDATE customers SET Firstname = ?, Lastname = ?, Email = ?, ContactNumber = ?, Address = ? WHERE userID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssssi", $firstname, $lastname, $email, $phone, $address, $userID);
    if ($updateStmt->execute()) {
        $_SESSION['userName'] = $firstname;
        echo '<script>alert("Profile updated successfully."); window.location = "profile.php";</script>';
    } else {
        echo '<script>alert("Error updating profile.");</script>';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = htmlspecialchars($_POST['current_password']);
    $new_password = htmlspecialchars($_POST['new_password']);
    $confirm_password = htmlspecialchars($_POST['confirm_password']);

    if (password_verify($current_password, $user['Password'])) {
        if ($new_password === $confirm_password) {
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $passwordQuery = "UPDATE customers SET Password = ? WHERE userID = ?";
            $passwordStmt = $conn->prepare($passwordQuery);
            $passwordStmt->bind_param("si", $new_password_hashed, $userID);
            if ($passwordStmt->execute()) {
                echo '<script>alert("Password changed successfully."); window.location = "profile.php";</script>';
            } else {
                echo '<script>alert("Error changing password.");</script>';
            }
        } else {
            echo '<script>alert("New password and confirm password do not match.");</script>';
        }
    } else {
        echo '<script>alert("Current password is incorrect.");</script>';
    }
}

// Handle account verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_account'])) {
    $target_dir = "../uploads/";

    // Create uploads directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["id_file"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["id_file"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo '<script>alert("File is not an image.");</script>';
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["id_file"]["size"] > 500000) {
        echo '<script>alert("Sorry, your file is too large.");</script>';
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png" && $imageFileType != "gif") {
        echo '<script>alert("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");</script>';
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo '<script>alert("Sorry, your file was not uploaded.");</script>';
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["id_file"]["tmp_name"], $target_file)) {
            $verifyQuery = "UPDATE customers SET verification_status = 'pending', VerificationFile = ? WHERE userID = ?";
            $verifyStmt = $conn->prepare($verifyQuery);
            $verifyStmt->bind_param("si", $target_file, $userID);
            if ($verifyStmt->execute()) {
                echo '<script>alert("Account verification requested successfully. Your status is pending."); window.location = "profile.php";</script>';
            } else {
                echo '<script>alert("Error verifying account.");</script>';
            }
        } else {
            echo '<script>alert("Sorry, there was an error uploading your file.");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | De Chavez Waterhaus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h1>Customer Dashboard</h1>
        </div>

        <div class="dash-content">
            <div class="profile-content">
                <h2>Profile Information</h2>
                <div class="card p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo $user['Firstname'] . ' ' . $user['Lastname']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?php echo $user['Email']; ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Phone:</strong> <?php echo $user['ContactNumber']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Address:</strong> <?php echo $user['Address']; ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p><strong>Joined Date:</strong> <?php echo date("F j, Y", strtotime($user['registration_date'])); ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
                            <?php if ($user['verification_status'] == 'approved'): ?>
                                <button type="button" class="btn btn-success" disabled>Account Verified</button>
                            <?php elseif ($user['verification_status'] == 'pending'): ?>
                                <button type="button" class="btn btn-warning" disabled>Verification Pending</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#verifyAccountModal">Verify Account</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="profile.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $user['Firstname']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $user['Lastname']; ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['Email']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user['ContactNumber']; ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" required><?php echo $user['Address']; ?></textarea>
                            </div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="profile.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Verify Account Modal -->
    <div class="modal fade" id="verifyAccountModal" tabindex="-1" aria-labelledby="verifyAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verifyAccountModalLabel">Verify Account</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="profile.php" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="id_file" class="form-label">Upload Valid ID</label>
                                <input type="file" class="form-control" id="id_file" name="id_file" required>
                            </div>
                        </div>
                        <button type="submit" name="verify_account" class="btn btn-primary">Submit for Verification</button>
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
