<?php
session_start();
include '../includes/connection.php';

if (!isset($_GET['token'])) {
    die("Invalid reset link.");
}

$token = $_GET['token'];

// Fetch user by token only
$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Invalid reset link.");
}

$user = $result->fetch_assoc();

// Check expiry in PHP
if (strtotime($user['reset_token_expiry']) < time()) {
    die("⏰ This reset link has expired. Please request a new one.");
}

$success = "";
$error = "";

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "❌ Passwords do not match.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user['id']);
        $stmt->execute();

        $success = "🎉 Password reset successfully! Redirecting to Sign In...";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - De Chavez Waterhaus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    body, html { height: 100%; margin: 0; font-family: 'Poppins', sans-serif; }
    .bg-video {
      position: fixed; right: 0; bottom: 0;
      min-width: 100%; min-height: 100%;
      z-index: -1; object-fit: cover; filter: brightness(0.6);
    }
    .overlay {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.4); z-index: -1;
    }
    .card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px; padding: 30px;
      box-shadow: 0px 5px 20px rgba(0,0,0,0.3);
    }
    .input-group-text { cursor: pointer; }
    .requirements { font-size: 0.85rem; margin-top: 5px; }
    .requirements span { display: block; }
    .valid { color: green; }
    .invalid { color: red; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

  <!-- Background video -->
  <video autoplay muted loop class="bg-video">
    <source src="../assets/videos/BG.mp4" type="video/mp4">
  </video>
  <div class="overlay"></div>

  <!-- Reset Password Card -->
  <div class="card p-4 shadow" style="max-width:400px; width:100%;">
    <h3 class="mb-3 text-center">Reset Password</h3>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success text-center">
        <?php echo $success; ?><br>
        <a href="../signin.php" class="btn btn-success btn-sm mt-3">Sign In</a>
      </div>
      <script>
        setTimeout(() => { window.location.href = "../signin.php"; }, 5000);
      </script>
    <?php else: ?>
      <form method="post" id="resetForm">
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <div class="input-group">
            <input type="password" name="password" id="password" class="form-control" required minlength="8">
            <span class="input-group-text" onclick="togglePassword('password', this)">
              <i class="fa fa-eye"></i>
            </span>
          </div>
          <div class="requirements mt-2" id="passwordRequirements">
            <span id="req-length" class="invalid">❌ At least 8 characters</span>
            <span id="req-uppercase" class="invalid">❌ At least 1 uppercase letter</span>
            <span id="req-number" class="invalid">❌ At least 1 number</span>
            <span id="req-special" class="invalid">❌ At least 1 special character</span>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <div class="input-group">
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="8">
            <span class="input-group-text" onclick="togglePassword('confirm_password', this)">
              <i class="fa fa-eye"></i>
            </span>
          </div>
          <div id="matchMessage" class="requirements"></div>
        </div>
        <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>Update Password</button>
      </form>
    <?php endif; ?>
  </div>

  <script>
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    const reqLength = document.getElementById('req-length');
    const reqUpper = document.getElementById('req-uppercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    const matchMessage = document.getElementById('matchMessage');
    const submitBtn = document.getElementById('submitBtn');

    function togglePassword(fieldId, el) {
        const input = document.getElementById(fieldId);
        const icon = el.querySelector("i");
        if (input.type === "password") {
        input.type = "text"; 
        icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
        input.type = "password"; 
        icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }

    function checkRequirements() {
        const val = password.value;
        let valid = true;

        // Length
        if (val.length >= 8) {
        reqLength.textContent = "✅ At least 8 characters";
        reqLength.className = "valid";
        } else {
        reqLength.textContent = "❌ At least 8 characters";
        reqLength.className = "invalid";
        valid = false;
        }

        // Uppercase
        if (/[A-Z]/.test(val)) {
        reqUpper.textContent = "✅ At least 1 uppercase letter";
        reqUpper.className = "valid";
        } else {
        reqUpper.textContent = "❌ At least 1 uppercase letter";
        reqUpper.className = "invalid";
        valid = false;
        }

        // Number
        if (/[0-9]/.test(val)) {
        reqNumber.textContent = "✅ At least 1 number";
        reqNumber.className = "valid";
        } else {
        reqNumber.textContent = "❌ At least 1 number";
        reqNumber.className = "invalid";
        valid = false;
        }

        // Special char
        if (/[^A-Za-z0-9]/.test(val)) {
        reqSpecial.textContent = "✅ At least 1 special character";
        reqSpecial.className = "valid";
        } else {
        reqSpecial.textContent = "❌ At least 1 special character";
        reqSpecial.className = "invalid";
        valid = false;
        }

        // Confirm password check
        if (confirm.value !== "" && password.value === confirm.value) {
        matchMessage.textContent = "✅ Passwords match";
        matchMessage.className = "requirements valid";
        } else if (confirm.value !== "") {
        matchMessage.textContent = "❌ Passwords do not match";
        matchMessage.className = "requirements invalid";
        valid = false;
        } else {
        matchMessage.textContent = "";
        valid = false;
        }

        // Enable/disable button
        submitBtn.disabled = !valid;
    }

    password.addEventListener('input', checkRequirements);
    confirm.addEventListener('input', checkRequirements);
    </script>

</body>
</html>
