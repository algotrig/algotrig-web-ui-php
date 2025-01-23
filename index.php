<html><head></head><body>
<button><a href="http://localhost/kite/?execute_orders=0">Refresh</a></button>
<button><a href="http://localhost/kite/?execute_orders=1">Execute</a></button>
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
	
	define("ACCESS_TOKEN","hGIAYF53Tvm056CQ3P08IBrGfyeaEU5F");
	$kite->setAccessToken(ACCESS_TOKEN);

	function get_trading_symbols(array $holdings) : array
	{
		$holding_keys = [];
		$trading_symbols = [];
		$quote_symbols = [];
		for ($i=0; $i< count($holdings); $i++){
				$holding = $holdings[$i];
				$ts = $holding->tradingsymbol;
				$qs = "NSE:".$ts;
				array_push($trading_symbols, $holding->tradingsymbol);
				array_push($quote_symbols, $qs);
				$holding_keys[$ts] = $i;
		}
		//print_r($trading_symbols);
		return ["trading_symbols" => $trading_symbols, "quote_symbols" => $quote_symbols, "holding_keys" => $holding_keys];
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
			"tradingsymbol" => $obj->trading_symbol,
			"exchange" => "NSE",
			"quantity" => $obj->buy_qty,
			"transaction_type" => "BUY",
			"order_type" => "MARKET",
			"product" => "CNC"
		];
	}

	//print_r($_GET);
	
	$target_value = 0.0;
	$execute_orders = false;
	
	if(isset($_GET["target_value"]))
	{
		$target_value = floatval($_GET["target_value"]);
		echo "Target Value : " . $target_value;
	}
	echo "<br/>";
	
	if(isset($_GET["execute_orders"]))
	{
		$execute_orders = intval($_GET["execute_orders"]);
	}
	
	echo "Execute : $execute_orders ;";
	
	echo "<br/>";
	
	// Get the list of positions.
	$positions = $kite->getPositions();
	$positions_day = $positions->day;
	$day_positions = [];
	$day_positions_keys = [];
	for($i=0; $i< count($positions_day); $i++)
	{
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
    echo "Positions: <br/>";
	echo "<pre>";
    //print_r($positions->day);
	echo "</pre>";
	//exit(0);
	
    // Initialise.
    // $kite = new KiteConnect("004twwh7tdmvkwgk");
    //$kite = new KiteConnect("004twwh7tdmvkwgk","KdAOdP7LtCrjYZ3PtRLmVOjfnsQxqw8R");

	
	// Get the list of holdings.
	//echo "Holdings: ";
	$holdings = $kite->getHoldings();
	//$holdings_json = json_encode($holdings);
	//print_r($holdings);
	
	$gts = get_trading_symbols($holdings);
	echo "<pre>";
	//print_r($gts);
	
	$trading_symbols = $gts["trading_symbols"];
	$quote_symbols = $gts["quote_symbols"];
	$holding_keys = $gts["holding_keys"];
	$ltps = $kite->getLTP($quote_symbols);
	//print_r($ltps);
	echo "</pre>";
	$result = [];
	$max_curr_val = 0.0;
	
	foreach($trading_symbols as $ts){
		$holding_qty = $holdings[$holding_keys[$ts]]->opening_quantity;
		if(isset($day_positions_keys[$ts]))
		{
			$key = $day_positions_keys[$ts];
			$dhq = $day_positions[$key]->quantity;
			$holding_qty += intval($dhq);
		}
		$holdings[$holding_keys[$ts]]->holding_quantity = $holding_qty;
	}
	
	if($target_value == 0.0){
		foreach($trading_symbols as $ts){
			if($ts == "SETFNIF50" || $ts == "NIFTYBEES" || $ts == "MAFANG"){
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
	
	echo "Max Current Value = $max_curr_val <br/>";
		
	$total_buy_amt = 0.00;
	foreach($trading_symbols as $ts){
		if($ts == "SETFNIF50" || $ts == "NIFTYBEES" || $ts == "MAFANG"){
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
		$obj->buy_amt = floatval($buy_qty * $ltp);
		$total_buy_amt += $obj->buy_amt;
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
				array_push($orders, get_order($r));
			}
		}
	}
	echo "</table>";
	
	echo "<pre>";
	print_r($orders);
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