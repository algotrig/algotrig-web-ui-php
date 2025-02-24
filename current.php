<?php
	session_start();
	
	$ini_data = parse_ini_file('algotrig.ini', true);
	define("API_KEY",$ini_data['zerodha']['API_KEY']);
	// define("SECRET",$ini_data['zerodha']['SECRET']);
	
	require_once __DIR__ . '/vendor/autoload.php';

    use KiteConnect\KiteConnect;
	
	$kite = new KiteConnect(API_KEY, $_SESSION['access_token']);
	
	// Get the list of positions.
	$positions = $kite->getPositions();
	
	// Get the list of holdings.
	$holdings = $kite->getHoldings();
	
	echo "<pre>";
	print_r($positions);
	print_r($holdings);
	echo "</pre>";