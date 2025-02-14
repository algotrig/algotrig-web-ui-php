<?php 

session_set_cookie_params([
    'lifetime' => 28800, // 8 hours
    'path' => '/',
    'domain' => '', 
    'secure' => true, // Required for 'None' to work
    'httponly' => true, // Prevents JavaScript from accessing the session
    'samesite' => 'None' // Allows cross-site cookies
]);

session_start();

	define("API_KEY","004twwh7tdmvkwgk");
	define("SECRET","89aivmhz2z9q9eqo0fy0dy1yy3e8xuw3");
	//define("ACCESS_TOKEN","yi2dcSVdgZNip7tX3Zmv4RPs78igSS63");
	
	require_once __DIR__ . '/vendor/autoload.php';

    use KiteConnect\KiteConnect;
	
	$kite = new KiteConnect(API_KEY);
	
	if(isset($_GET['request_token'])) {
		$req_token = $_GET['request_token'];
		try {
			$user = $kite->generateSession($req_token, SECRET);
			echo "Authentication successful. <br /><pre>";
			// Set session variable
			$_SESSION['access_token'] = $user->access_token;
			// Redirect to another page
			header('Location: /');
			exit(0); // Ensure that no further code is executed after the redirect
			//print_r($user);
			//echo "</pre>";
		} catch(Exception $e) {
			echo "Authentication failed: ".$e->getMessage();
			throw $e;
		}
		exit(0);
	}
	
	header("Location: https://kite.zerodha.com/connect/login?api_key=" . API_KEY);