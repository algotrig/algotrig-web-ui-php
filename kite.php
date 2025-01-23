<?php	

	require_once __DIR__ . '/vendor/autoload.php';

    use KiteConnect\KiteConnect;
	
	function get_kite($wat = true){
		$kite = new KiteConnect("004twwh7tdmvkwgk");
	
		if($wat) {
			define("ACCESS_TOKEN","hGIAYF53Tvm056CQ3P08IBrGfyeaEU5F");
			$kite->setAccessToken(ACCESS_TOKEN);
		}
	
		return $kite;
	}
	
?>