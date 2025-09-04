<?php
session_start();
require_once '../vendor/autoload.php';
include '../includes/connection.php'; // DB connection

$client = new Google_Client();
$client->setClientId('581912215958-52tqrg1q7kt12omes7dnvjh23nb38bb2.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/DeChavezWatersation/auth/google_callback.php'); 
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['code'])) {
    // Exchange code for access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        // Get user info
        $oauth = new Google_Service_Oauth2($client);
        $googleUser = $oauth->userinfo->get();

        $googleId = $googleUser->id;
        $email    = $googleUser->email;
        $name     = $googleUser->name;

        // First: check if this Google account is already linked
        $stmt = $conn->prepare("SELECT * FROM users WHERE provider='google' AND provider_id=? LIMIT 1");
        $stmt->bind_param("s", $googleId);
        $stmt->execute();
        $byProvider = $stmt->get_result();

        if ($byProvider->num_rows > 0) {
            // Existing Google-linked account → log in
            $user = $byProvider->fetch_assoc();

            // Update last login
            $stmt = $conn->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();

            $_SESSION['user'] = $user;
        } else {
            // Check if email already exists from local signup
            $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $byEmail = $stmt->get_result();

            if ($byEmail->num_rows > 0) {
                // Upgrade/link existing account with Google
                $user = $byEmail->fetch_assoc();

                $stmt = $conn->prepare("
                    UPDATE users
                       SET provider='google',
                           provider_id=?,
                           email_verified_at = IFNULL(email_verified_at, NOW()),
                           last_login_at = NOW()
                     WHERE id = ?
                     LIMIT 1
                ");
                $stmt->bind_param("si", $googleId, $user['id']);
                $stmt->execute();

                $user['provider'] = 'google';
                $user['provider_id'] = $googleId;
                if (is_null($user['email_verified_at'])) {
                    $user['email_verified_at'] = date('Y-m-d H:i:s');
                }

                $_SESSION['user'] = $user;
            } else {
                // Brand new Google user → insert
                $stmt = $conn->prepare("
                    INSERT INTO users (name, email, provider, provider_id, email_verified_at, created_at, last_login_at)
                    VALUES (?, ?, 'google', ?, NOW(), NOW(), NOW())
                ");
                $stmt->bind_param("sss", $name, $email, $googleId);
                $stmt->execute();

                $newUserId = $conn->insert_id;

                $_SESSION['user'] = [
                    'id'              => $newUserId,
                    'name'            => $name,
                    'email'           => $email,
                    'provider'        => 'google',
                    'provider_id'     => $googleId,
                    'email_verified_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        // Redirect to dashboard (or homepage)
        header("Location: ../customer/customer_dashboard.php");
        exit();
    } else {
        // If token fetch fails
        header("Location: ../signin.php?error=" . urlencode('Google login failed.'));
        exit();
    }
} else {
    // No code in URL
    header("Location: ../signin.php?error=" . urlencode('Invalid Google response.'));
    exit();
}
