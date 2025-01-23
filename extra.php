<?php	
	
	print_r($kite->getInstruments("NSE"));
	print_r($kite->getInstruments("NFO"));
	
	print_r($kite->getQuote(["NFO:TATAMOTORS"]));
    // Assuming you have obtained the `request_token`
    // after the auth flow redirect by redirecting the
    // user to $kite->login_url()
	$req_token = $_GET['request_token'];
    try {
        $user = $kite->generateSession($req_token, "89aivmhz2z9q9eqo0fy0dy1yy3e8xuw3");
        echo "Authentication successful. <br />";
        print_r($user);
        $kite->setAccessToken($user->access_token);
    } catch(Exception $e) {
        echo "Authentication failed: ".$e->getMessage();
        throw $e;
    }

    echo $user->user_id." has logged in";


	Fetch all instruments
    $instruments = $kite->getInstruments("NFO");

	print_r($instruments);

    //Filter instruments for a specific symbol and type (FUT or OPT)
    $symbol = "TATAMOTORS"; // Replace with your desired symbol
    $filtered_instruments = array_filter($instruments, function($instrument) use ($symbol) {
        return $instrument->name === $symbol && ($instrument->segment === "NFO-FUT" || $instrument->segment === "NFO-OPT");
    });
	
	print_r($filtered_instruments);
	
    // Display the filtered instruments
    foreach ($filtered_instruments as $instrument) {
        echo "Trading Symbol: " . $instrument->tradingsymbol . "\n";
        echo "Instrument Token: " . $instrument->instrument_token . "\n";
        echo "Segment: " . $instrument->segment . "\n";
        echo "Expiry: " . $instrument->expiry->format('Y-m-d') . "\n";
        echo "Strike Price: " . $instrument->strike . "\n";
        echo "Option Type: " . $instrument->instrument_type . "\n";
        echo "---------------------------------\n";
    }

	print_r($holdings);
    // Get the list of positions.
    echo "Positions: ";
    print_r($kite->getPositions());
	
	$orders =  [
		[
			"tradingsymbol" => "ITBEES",
			"exchange" => "NSE",
			"quantity" => 10,
			"transaction_type" => "BUY",
			"order_type" => "MARKET",
			"product" => "CNC"
		],
		[
			"tradingsymbol" => "SILVERBEES",
			"exchange" => "NSE",
			"quantity" => 4,
			"transaction_type" => "BUY",
			"order_type" => "MARKET",
			"product" => "CNC"
		],
		[
			"tradingsymbol" => "FMCGIETF",
			"exchange" => "NSE",
			"quantity" => 5,
			"transaction_type" => "BUY",
			"order_type" => "LIMIT",
			"price" => 59,
			"product" => "CNC"
		],
		[
			"tradingsymbol" => "CONSUMIETF",
			"exchange" => "NSE",
			"quantity" => 1,
			"transaction_type" => "BUY",
			"order_type" => "MARKET",
			"product" => "CNC"
		],
		[
			"tradingsymbol" => "GOLDBEES",
			"exchange" => "NSE",
			"quantity" => 2,
			"transaction_type" => "BUY",
			"order_type" => "MARKET",
			"product" => "CNC"
		],
		[
			"tradingsymbol" => "MONIFTY500",
			"exchange" => "NSE",
			"quantity" => 2,
			"transaction_type" => "BUY",
			"order_type" => "MARKET",
			"product" => "CNC"
		],
		[
			"tradingsymbol" => "HDFCSENSEX",
			"exchange" => "NSE",
			"quantity" => 1,
			"transaction_type" => "BUY",
			"order_type" => "LIMIT",
			"price" => 86,
			"product" => "CNC"
		],
		[
			"tradingsymbol" => "NV20IETF",
			"exchange" => "NSE",
			"quantity" => 4,
			"transaction_type" => "BUY",
			"order_type" => "MARKET",
			"product" => "CNC"
		]
	];
	
	
	// Place order.
    // $order = $kite->placeOrder("regular",);
	
	// Place multiple orders
	foreach ($orders as $order_data) {
		$order = $kite->placeOrder("regular",$order_data);
		print_r($order);
	}

    echo "Order id is ".$order->order_id;
?>