<?php

	require_once __DIR__ . '/kite.php';
	
	$kite = get_kite();
	
	echo "<pre>";
	
	$quote_symbols = [];
	$quote_symbols[] = "NSE:NIFTY 50";
	$quote_symbols[] = "NSE:FMCGIETF";
	$quote_symbols[] = "NSE:HDFCSENSEX";
	
	print_r($quote_symbols);
	
	$quotes = $kite->getQuote($quote_symbols);
	
	print_r($quotes);
	//print_r($quotes["NSE:FMCGIETF"]->depth->sell);
	
	echo "</pre>";
	
?>