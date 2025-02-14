<?php

	session_start();
	
	$access_token = $_GET['access_token'];
	$_SESSION['access_token'] = $access_token;
	
	echo $access_token;