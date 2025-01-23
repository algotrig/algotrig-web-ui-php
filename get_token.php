<?php	

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