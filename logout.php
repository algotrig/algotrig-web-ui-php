<?php
session_start(); // Start the session

// Unset all session variables
session_unset(); 

// Destroy the session
session_destroy();

// Delete the session cookie (optional, but recommended)
if (isset($_COOKIE[session_name()])) {
	$session_name = session_name();
    setcookie($session_name, '', time() - 3600, '/');
}

?>
<html>
<head>
	<title>ALGO TRIG - Logout</title>
</head>
<body>
<p>Current time: <?php echo date('d-m-Y H:i:s A'); ?></p>
<button><a href="/login.php">Login</a></button>
</body>
</html>