<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/config_loader.php';

// Load configuration
$config = loadAppConfig(__DIR__ . '/../algotrig.ini');

session_start();

// Log user logout if user was logged in
if (isset($_SESSION['user_id'])) {
    error_log("User {$_SESSION['user_id']} logged out");
}

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>ALGO TRIG - Logout</title>
</head>
<body>
<p>Logged out successfully.</p>
<p>Current time: <?php echo date('d-m-Y H:i:s A'); ?></p>
<button><a href="/login.php">Login</a></button>
</body>
</html>
