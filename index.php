<?php
session_start();

	if (isset($_SESSION['access_token'])) {
		// proceed
	} else {
		header('Location: /logout.php');
	}

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

	//print_r($_SESSION);
	//exit(0);


$n=300;
if(isset($_GET['r'])){
	$n=$_GET['r'];
}
// Set the refresh header to refresh the page every $n seconds
header("Refresh: $n");
?>
<!DOCTYPE html>
<?php 
	require_once __DIR__ . '/functions.php';
	
	$target_value = 0.0;
	$execute_orders = false;
	
	if(isset($_GET["target_value"])) {
		$target_value = floatval($_GET["target_value"]);
	}
	
	if(isset($_GET["execute_orders"])) {
		$execute_orders = intval($_GET["execute_orders"]);
	}
	
?>
<html>
<head>
	<title>ALGO TRIG</title>
    <!-- <meta http-equiv="refresh" content="10"> Refresh every 300 seconds (5 minutes) -->
	<style type="text/css">
		pre {
			font-size: 17px;
		}
		button {
			font-size: 18px;
		}
	</style>
</head>
<body>
<p>Current time: <?php echo date('d-m-Y H:i:s A'); ?> <button><a href="/logout.php">Logout</a></button></p>
<p>Refresh: <?php echo $n; ?> seconds</p>
<button><a href="/?execute_orders=0&r=<?php echo $n ?>">Refresh</a></button>
<button><a href="/?execute_orders=0&target_value=<?php echo $target_value ?>&r=<?php echo $n ?>">Refresh [TV]</a></button>
<button><a href="/?execute_orders=1&target_value=<?php echo $target_value ?>&r=<?php echo $n ?>">Execute</a></button>
<?php
	
	$kite->setAccessToken($_SESSION['access_token']);

	// Get the list of positions.
	$positions = $kite->getPositions();
	$positions_day = $positions->day;
	$day_positions = [];
	$day_positions_keys = [];
	for($i=0; $i< count($positions_day); $i++) {
		$pos = $positions_day[$i];
		$ts = $pos->tradingsymbol;
		$qty = $pos->quantity;
		$pos_day = new stdClass();
		$pos_day->trading_symbol = $ts;
		$pos_day->quantity = $qty;
		//print_r($pos_day);
		array_push($day_positions,$pos_day);
		$day_positions_keys[$ts]=$i;
	}
    	
	// Get the list of holdings.
	$holdings = $kite->getHoldings();
		
	$gts = get_trading_symbols($holdings);
	echo "<pre>";
	//print_r($gts);
	
	$trading_symbols = $gts["trading_symbols"];
	$quote_symbols = $gts["quote_symbols"];
	$holding_keys = $gts["holding_keys"];
	
	$nifty50qs = "NSE:NIFTY 50";
	$quote_symbols[] = $nifty50qs;
	$ltps = $kite->getLTP($quote_symbols);
	//print_r($ltps);
	
	$nifty50ltp = $ltps->$nifty50qs->last_price;
	echo "Nifty 50: <a href=\"/?execute_orders=0&target_value=$nifty50ltp&r=$n\">$nifty50ltp</a>";
	
	$result = [];
	$max_curr_val = 0.0;
	
	foreach($trading_symbols as $ts) {
		$holding_qty = $holdings[$holding_keys[$ts]]->opening_quantity;
		if(isset($day_positions_keys[$ts]))
		{
			$key = $day_positions_keys[$ts];
			$dhq = $day_positions[$key]->quantity;
			$holding_qty += intval($dhq);
		}
		$holdings[$holding_keys[$ts]]->holding_quantity = $holding_qty;
	}
		
	//print_r($quote_symbols);
	
	//$quotes = $kite->getQuote($quote_symbols);
	
	//print_r($quotes);
	
	if($target_value == 0.0) {
		foreach($trading_symbols as $ts) {
			if($ts == "SETFNIF50" || $ts == "NIFTYBEES" ){ //|| $ts == "MAFANG" || $ts == "MONQ50" || $ts == "HNGSNGBEES" ||  $ts == "MON100"
				continue;
			}
			
			$qs = "NSE:".$ts;
			$ltp = floatval($ltps->$qs->last_price);
			$holding_qty = $holdings[$holding_keys[$ts]]->holding_quantity;
			$curr_val = floatval(intval($holding_qty) * $ltp);
			if($curr_val > $max_curr_val){
				$max_curr_val = $curr_val;
			}
		}
		$target_value = $max_curr_val;
	} else {
		$max_curr_val = $target_value;
	}
		
	$total_buy_amt = 0.00;
	foreach($trading_symbols as $ts) {
		if($ts == "SETFNIF50" || $ts == "NIFTYBEES" ){ // || $ts == "MAFANG" || $ts == "MONQ50" || $ts == "HNGSNGBEES" ||  $ts == "MON100"
			continue;
		}
		//print_r($ltps->$ts);
		$qs = "NSE:".$ts;
		$ltp_obj = $ltps->$qs;
		$obj = new stdClass();
		$obj->trading_symbol = $ts;
		$obj->quote_symbol = $qs;
		$obj->instrument_token = $ltp_obj->instrument_token;
		$obj->ltp = $ltp_obj->last_price;
		$opening_qty = $holdings[$holding_keys[$ts]]->opening_quantity;
		$holding_qty = $holdings[$holding_keys[$ts]]->holding_quantity;
		$obj->opening_quantity = $opening_qty;
		$obj->holding_quantity = $holding_qty;
		$ltp = floatval($obj->ltp);
		$curr_val = intval($holding_qty) * $ltp;
		$obj->current_value = $curr_val;
		$diff = floatval($target_value) - floatval($obj->current_value);
		$obj->difference = number_format($diff,2);
		$buy_qty = 0; 
		if($diff > 0.0){
			$buy_qty = floor($diff / $ltp);
		}
		$obj->buy_qty = $buy_qty;
		$buy_amt = floatval($buy_qty * $ltp);
		$obj->buy_amt = $buy_amt;
		$total_buy_amt += $buy_amt;
		$obj->proposed_value = $curr_val + $buy_amt;
		$result[$ts] = $obj; 
		if($curr_val == $max_curr_val){
			$obj->trading_symbol = "*".$ts;
		}
	}
	
	//print_r($result);

	echo "<pre/>";
	
	echo "Max Current Value = $max_curr_val <br/>";
	echo "Total Buy Amount = $total_buy_amt <br/>";
   
	echo "<table border = 1 cellspacing = 0>";
	$print_header_row = true;
	$orders = [];
	foreach ($result as $sym =>$r)
	{
		//print_r($r);
		if($print_header_row){
			echo objectToTableRow($r,true);
			$print_header_row = false;
		}
		
		if(intval($r->buy_qty) > 0 || $r->current_value == $max_curr_val){
			echo objectToTableRow($r);
			if(intval($r->buy_qty) > 0){
				array_push($orders, get_order($r,$kite));
			}
		}
	}
	echo "</table>";
	
	echo "<pre>";
	//print_r($orders);
	echo "</pre>";
	//exit(0);
	
	if($execute_orders > 0){
		echo "Executed";
		// Place multiple orders
		foreach ($orders as $order_data) {
			try{
				$order = $kite->placeOrder("regular",$order_data);
				print_r($order);
				echo "Inside try";
			}catch (Exception $e){
				echo "<br/>Caught exception: " . print_r($e) ;
			}
			
		}
		echo "<br/>";
	}
?>
</body>
</html>