<?php
	
	define("API_KEY","004twwh7tdmvkwgk");
	define("SECRET","89aivmhz2z9q9eqo0fy0dy1yy3e8xuw3");
	define("ACCESS_TOKEN","J3lXAVuAFF015R99e5bLRMbiv839qDMg");
	
	require_once __DIR__ . '/vendor/autoload.php';

    use KiteConnect\KiteConnect;
	
	$kite = new KiteConnect(API_KEY);
	$kite->setAccessToken(ACCESS_TOKEN);
	
	// Get the list of positions.
	$positions = $kite->getPositions();
	
	// Get the list of holdings.
	$holdings = $kite->getHoldings();
	
	echo "<pre>";
	print_r($positions);
	print_r($holdings);
	echo "</pre>";