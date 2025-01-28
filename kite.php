<?php	

	require_once __DIR__ . '/vendor/autoload.php';

    use KiteConnect\KiteConnect;
	
	function get_kite($wat = true){
		$kite = new KiteConnect("004twwh7tdmvkwgk");
	
		if($wat) {
			define("ACCESS_TOKEN","hWDUtiE82rrgAYo0th4I7YNnxlzq4zrZ");
			$kite->setAccessToken(ACCESS_TOKEN);
		}
	
		return $kite;
	}
	
?>