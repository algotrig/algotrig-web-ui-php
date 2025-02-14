<?php	

	require_once __DIR__ . '/vendor/autoload.php';

    use KiteConnect\KiteConnect;
	
	function get_kite($wat = true){
		$kite = new KiteConnect("004twwh7tdmvkwgk");
	
		if($wat) {
			define("ACCESS_TOKEN","Uf7cvLC7YXMGfIKZEE6FmGtlnRR7rZJu");
			$kite->setAccessToken(ACCESS_TOKEN);
		}
	
		return $kite;
	}
	
?>