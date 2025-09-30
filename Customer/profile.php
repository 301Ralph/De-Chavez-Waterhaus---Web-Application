<?php
include '../includes/connection.php';
session_start();

// Check if logged in as customer
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script>alert("Access denied. Customers only."); window.location = "../login.php";</script>';
    exit();
}

$userID = (int) $_SESSION['userID'];

// Fetch user info
$query = "SELECT name, email, phone, verification_file, is_verified, created_at 
          FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Determine verification status
if ($user['is_verified'] == 1) {
    $verificationStatus = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>';
} elseif (!empty($user['verification_file'])) {
    $verificationStatus = '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Under Review</span>';
} else {
    $verificationStatus = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Unverified</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile | De Chavez Waterhaus</title>
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
          <li><a href="notification.php"><i class="bi bi-bell"></i><span class="link-name">Notification</span></a></li>
          <li><a href="profile.php" class="active"><i class="bi bi-person"></i><span class="link-name">Profile</span></a></li>
          <li><a href="../logout.php"><i class="bi bi-box-arrow-right"></i><span class="link-name">Logout</span></a></li>
      </ul>
  </nav>

  <!-- Content -->
  <div class="content">
      <div class="d-flex justify-content-between align-items-center mb-4">
          <i class="bi bi-list sidebar-toggle"></i>
          <h1 class="h3 mb-0">My Profile</h1>
      </div>

      <!-- Profile Card -->
      <div class="card shadow-sm border-0">
          <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="card-title mb-0"><i class="bi bi-person-circle"></i> Account Details</h5>
                  <?php echo $verificationStatus; ?>
              </div>

              <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
              <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
              <p><strong>Phone:</strong> <?php echo $user['phone'] ? htmlspecialchars($user['phone']) : '<span class="text-muted">Not set</span>'; ?></p>
              <p><strong>Member Since:</strong> <?php echo date("M d, Y", strtotime($user['created_at'])); ?></p>

              <!-- Actions -->
              <div class="mt-3">
                  <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                      <i class="bi bi-pencil-square"></i> Edit Profile
                  </button>
                  <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                      <i class="bi bi-lock"></i> Change Password
                  </button>

                  <?php if (!empty($user['verification_file']) && $user['is_verified'] == 0) { ?>
                      <button class="btn btn-warning btn-sm" disabled>
                          <i class="bi bi-hourglass-split"></i> Under Review
                      </button>
                  <?php } elseif ($user['is_verified'] == 1) { ?>
                      <button class="btn btn-success btn-sm" disabled>
                          <i class="bi bi-check-circle"></i> Verified
                      </button>
                  <?php } else { ?>
                      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#verifyModal">
                          <i class="bi bi-upload"></i> Verify Account
                      </button>
                  <?php } ?>
              </div>
          </div>
      </div>
  </div>

  <!-- Edit Profile Modal -->
  <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form action="update_profile.php" method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required pattern="^[A-Za-z\s]+$" title="Name should only contain letters and spaces.">
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" pattern="^09\d{9}$" title="Enter a valid PH number (e.g., 09123456789).">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Change Password Modal -->
  <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form action="change_password.php" method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Change Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required minlength="6" title="Password must be at least 6 characters.">
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Password</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Verify Modal -->
  <div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form action="verify_account.php" method="POST" enctype="multipart/form-data" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Upload Verification Document</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Please upload a valid ID or proof of identity (JPG, PNG, PDF, max 2MB).</p>
          <input type="file" name="verification_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
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
