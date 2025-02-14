<?php

	function get_trading_symbols(array $holdings) : array {
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
			if(($key == "current_value" || $key == "proposed_value") && !$header){
				$html .= "<td><a href=\"?execute_orders=0&target_value=$value\">$value</a></td>";
			} else {
				$key_br = $header ? str_replace('_', '<br/>', $key) : $key;
				$html .= !$header ? "<td class=\"{$key}\">{$value}</td>" : "<td>{$key_br}</td>";
			}
		}

		// End table row
		$html .= "</tr>";
		
		return $html;
	}
	
	function get_order($obj,$kite) {
		if($obj->trading_symbol == "FMCGIETF" || $obj->trading_symbol == "HDFCSENSEX") {
			$quote_symbols = [$obj->quote_symbol];
			$quotes = $kite->getQuote($quote_symbols);
			$price = $quotes[$obj->quote_symbol]->depth->sell[4]->price;
			
			return [
				"tradingsymbol" => $obj->trading_symbol,
				"exchange" => "NSE",
				"quantity" => $obj->buy_qty,
				"transaction_type" => "BUY",
				"order_type" => "LIMIT",
				"price" => $price,
				"product" => "CNC"
			];
		} else {
			return [
				"tradingsymbol" => $obj->trading_symbol,
				"exchange" => "NSE",
				"quantity" => $obj->buy_qty,
				"transaction_type" => "BUY",
				"order_type" => "MARKET",
				"product" => "CNC"
			];
		}
	}