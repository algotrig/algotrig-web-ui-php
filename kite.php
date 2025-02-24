<?php	

	require_once __DIR__ . '/vendor/autoload.php';

    use KiteConnect\KiteConnect;
	
	$ini_data = parse_ini_file('algotrig.ini', true);
	define("API_KEY",$ini_data['zerodha']['API_KEY']);
	//define("SECRET",$ini_data['zerodha']['SECRET']);
	
	function get_kite($wat = true){
		$kite = new KiteConnect(API_KEY);
	
		if($wat) {
			define("ACCESS_TOKEN","Uf7cvLC7YXMGfIKZEE6FmGtlnRR7rZJu");
			$kite->setAccessToken(ACCESS_TOKEN);
		}
	
		return $kite;
	}
	
?>