<?php

	require_once __DIR__ . '/kite.php';
	
	$kite = get_kite();
	
	function get_order($obj, $type = "MARKET") {
		return [
			"tradingsymbol" => $obj->trading_symbol,
			"exchange" => "NSE",
			"quantity" => $obj->buy_qty,
			"transaction_type" => "BUY",
			"order_type" => $type,
			"price" => $obj->limit_price,
			"product" => "CNC"
		];
	}
	
	$fmcg_obj = (object) [];
	$fmcg_obj->trading_symbol = "FMCGIETF";
	$fmcg_obj->buy_qty = 16;
	$fmcg_obj->limit_price = 58.65;
	
	$fmcg_order = get_order($fmcg_obj, "LIMIT");
	
	$orders = [$fmcg_order];
	echo "<pre>";
	print_r($orders);
	
	
	/*foreach($orders as $ord) {
		try{
				$order = $kite->placeOrder("regular",$ord);
				print_r($order);
				echo "Inside try";
			}catch (Exception $e){
				echo "<br/>Caught exception: " . print_r($e) ;
			}
	}*/
	
	//print_r($kite->cancelOrder("regular","1882345314761859072"));
	print_r($kite->cancelOrder("regular","1882344523116339200"));
	
	print_r($kite->getOrders());
	
	//print_r($kite->cancelOrder("regular","1882345314761859072"));
	
	echo "</pre>";
?>