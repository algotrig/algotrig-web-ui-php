<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/config_loader.php';

// Load configuration
$config = loadAppConfig(__DIR__ . '/../algotrig.ini');

// Start session with secure parameters
session_set_cookie_params([
    'lifetime' => $config['session']['lifetime'],
    'path' => $config['session']['path'],
    'domain' => $config['session']['domain'],
    'secure' => $config['session']['secure'],
    'httponly' => $config['session']['httponly'],
    'samesite' => $config['session']['samesite']
]);

session_start();

// Log user logout if user was logged in
if (isset($_SESSION['user_id'])) {
    error_log("User {$_SESSION['user_id']} logged out");
}

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $config['session']['path'],
        $config['session']['domain'],
        boolval($config['session']['secure']),
        boolval($config['session']['httponly'])
    );
}

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
