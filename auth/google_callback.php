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
        $email = $googleUser->email;
        $name = $googleUser->name;

        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Existing user → log them in
            $_SESSION['user'] = $result->fetch_assoc();
        } else {
            // New user → insert
            $stmt = $conn->prepare("INSERT INTO users (name, email, google_id) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $googleId);
            $stmt->execute();

            $_SESSION['user'] = [
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId
            ];
        }

        // Redirect to dashboard (or homepage)
        header("Location: ../dashboard.php");
        exit();
    } else {
        // If token fetch fails
        header("Location: ../signin.php?error=Google Login failed");
        exit();
    }
} else {
    // No code in URL
    header("Location: ../signin.php?error=Invalid Google response");
    exit();
}
