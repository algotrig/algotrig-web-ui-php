<html><head></head><body>
<?php
	
	require_once __DIR__ . '/vendor/autoload.php';

    use KiteConnect\KiteConnect;
	
	$kite = new KiteConnect("004twwh7tdmvkwgk");
	
	if(isset($_GET['request_token']))
	{
		$req_token = $_GET['request_token'];
		echo "Token Set: $req_token <br/>";
		try {
			$user = $kite->generateSession($req_token, "89aivmhz2z9q9eqo0fy0dy1yy3e8xuw3");
			echo "Authentication successful. <br /><pre>";
			print_r($user);
			echo "</pre>";
			$kite->setAccessToken($user->access_token);
		} catch(Exception $e) {
			echo "Authentication failed: ".$e->getMessage();
			throw $e;
		}
		exit(0);
	}
	
	define("ACCESS_TOKEN","TKVkCGrwtGsuyB77PwgEYnAb786o2dNB");
	echo ACCESS_TOKEN;
	$kite->setAccessToken(ACCESS_TOKEN);

	function get_trading_symbols(array $holdings) : array
	{
		$holding_keys = [];
		$trading_symbols = [];
		for ($i=0; $i< count($holdings); $i++){
				$holding = $holdings[$i];
				$ts = "NSE:".$holding->tradingsymbol;
				array_push($trading_symbols, $ts);
				$holding_keys[$ts] = $i;
		}
		//print_r($trading_symbols);
		return ["trading_symbols" => $trading_symbols, "holding_keys" => $holding_keys];
	}

	function objectToTableRow($object, bool $header = false) {
		// Start table row
		$html = "<tr>";
		
		// Loop through the object properties
		foreach ($object as $key => $value) {
			$html .= !$header ? "<td>{$value}</td>" : "<td>{$key}</td>";
		}

		// End table row
		$html .= "</tr>";
		
		return $html;
	}

	function get_order($obj)
	{
		return [
			"tradingsymbol" => substr($obj->trading_symbol, 4),
			"exchange" => "NSE",
			"quantity" => $obj->buy_qty,
			"transaction_type" => "BUY",
			"order_type" => "MARKET",
			"product" => "CNC"
		];
	}

	//print_r($_GET);
	
	//$target_value = false;
	$execute_orders = false;
	
	if(isset($_GET["target_value"]))
	{
		$target_value = $_GET["target_value"];
		echo "Target Value : " . $target_value;
	}
	echo "<br/>";
	
	if(isset($_GET["execute_orders"]))
	{
		$execute_orders = intval($_GET["execute_orders"]);
	}
	
	echo "Execute : $execute_orders ;";
	
	echo "<br/>";
	
    // Initialise.
    // $kite = new KiteConnect("004twwh7tdmvkwgk");
    //$kite = new KiteConnect("004twwh7tdmvkwgk","KdAOdP7LtCrjYZ3PtRLmVOjfnsQxqw8R");

	
	// Get the list of holdings.
	//echo "Holdings: ";
	$holdings = $kite->getHoldings();
	//$holdings_json = json_encode($holdings);
	//print_r($holdings);
	
	$gts = get_trading_symbols($holdings);
	$trading_symbols = $gts["trading_symbols"];
	$holding_keys = $gts["holding_keys"];
	$ltps = $kite->getLTP($trading_symbols);
	//print_r($ltps);
	
	$result = [];
	$max_curr_val = 0.0;
	
	if($execute_orders > 0){
		foreach($trading_symbols as $ts){
			if($ts == "NSE:SETFNIF50" || $ts == "NSE:NIFTYBEES"){
				continue;
			}
			$opening_quantity = $holdings[$holding_keys[$ts]]->opening_quantity;
			$ltp = floatval($ltps->$ts->last_price);
			$curr_val = floatval(intval($opening_quantity) * $ltp);
			if($curr_val > $max_curr_val){
				$max_curr_val = $curr_val;
			}
		}
	}
	echo "Max Current Value = $max_curr_val <br/>";
	$target_value = $max_curr_val;
		
	foreach($trading_symbols as $ts){
		if($ts == "NSE:SETFNIF50" || $ts == "NSE:NIFTYBEES"){
			continue;
		}
		//print_r($ltps->$ts);
		$obj = new stdClass();
		$obj->trading_symbol = $ts;
		$obj->instrument_token = $ltps->$ts->instrument_token;
		$obj->ltp = $ltps->$ts->last_price;
		$opening_quantity = $holdings[$holding_keys[$ts]]->opening_quantity;
		$obj->opening_quantity = $opening_quantity;
		$ltp = floatval($obj->ltp);
		$curr_val = intval($opening_quantity) * $ltp;
		$obj->current_value = $curr_val;
		$diff = floatval($target_value) - floatval($obj->current_value);
		$obj->difference = number_format($diff,2);
		$buy_qty = 0; 
		if($diff > 0.0){
			$buy_qty = floor($diff / $ltp);
		}
		$obj->buy_qty = $buy_qty;
		$result[$ts] = $obj; 
		if($curr_val == $max_curr_val){
			$obj->trading_symbol = "*".$ts;
		}
	}
	
	//print_r($result);

	echo "<pre/>";
	
	echo "Max Current Value = $max_curr_val <br/>";
   
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
			array_push($orders, get_order($r));
		}
	}
	echo "</table>";
	
	echo "<pre>";
	print_r($orders);
	echo "</pre>";
	exit(0);
	
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