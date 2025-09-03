<?php
session_start();
require_once '../vendor/autoload.php';

// Initialize Google Client
$client = new Google_Client();
$client->setClientId('581912215958-52tqrg1q7kt12omes7dnvjh23nb38bb2.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/DeChavezWatersation/auth/google_callback.php'); 
// 👆 Change this to your actual domain in production
$client->addScope('email');
$client->addScope('profile');

// Redirect to Google OAuth consent screen
header('Location: ' . $client->createAuthUrl());
exit();
