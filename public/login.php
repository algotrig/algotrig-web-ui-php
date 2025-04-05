<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config_loader.php';

use KiteConnect\KiteConnect;

// Load configuration
$config = loadAppConfig(__DIR__ . '/../algotrig.ini');

session_start();

// Initialize KiteConnect
try {
    $kite = new KiteConnect($config['zerodha']['api_key']);
} catch (Exception $e) {
    error_log("KiteConnect initialization failed: " . $e->getMessage());
    die("Failed to initialize trading connection. Please try again later.");
}

// Handle authentication callback
if (isset($_GET['request_token'])) {
    try {
        $user = $kite->generateSession($_GET['request_token'], $config['zerodha']['secret']);
        
        // Set session variables
        $_SESSION['access_token'] = $user->access_token;
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['login_time'] = time();
        
        // Log successful login
        error_log("User {$user->user_id} logged in successfully");
        
        // Redirect to home page
        header('Location: /');
        exit;
    } catch (Exception $e) {
        error_log("Authentication failed: " . $e->getMessage());
        die("Authentication failed. Please try again.");
    }
}

// Redirect to Zerodha login page
$loginUrl = $kite->getLoginURL();
header("Location: " . $loginUrl);
exit; 