<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function disparo(){

    $dadosHash = "10006710000335";
	$hash256 = bin2hex(hash_hmac("sha256", $dadosHash, "YSXGf8y9L4L7W2tF2dbhgTkB3Apxs5V2fLQc", true));
	
    $curl = curl_init();
	curl_setopt_array($curl, [
	    CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => [
		    "Authorization: Signature ".$hash256
		],
		CURLOPT_URL => "https://testpay.recargajogo.com.br/api/partner/get_role_list?app_id=100067&player_id=10000335&channel_name=eprepag_br"
	]);
	$resultado = json_decode(curl_exec($curl), true);
	
	$role_id = $resultado["roles"][0]["packed_role_id"];
	//$info = curl_getinfo($curl);
	curl_close($curl);
	
	var_dump($resultado);
	echo "<hr>";
	
	
	$currecy = "BRL";
	$ip = $_SERVER["REMOTE_ADDR"]; // "127.0.0.1"
	$guid = "TXN".date("YmdHis");
	$amount = 1000;
	$test = 1;
	
	$dadosHash2 = "10006710000335".$role_id.$guid.$amount.$currecy.$ip;
	$hash2562 = bin2hex(hash_hmac("sha256", $dadosHash2, "YSXGf8y9L4L7W2tF2dbhgTkB3Apxs5V2fLQc", true));
	
	$curl1 = curl_init();
	curl_setopt_array($curl1, [
	    CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => json_encode(["test_mode"=> $test, "app_id" => 100067, "player_id"=> 10000335,
		"packed_role_id" => $role_id, "txn_id" => $guid, "amount" => $amount, "currency_code" => $currecy, "ip_address" => $ip]),
		CURLOPT_HTTPHEADER => [
		    "Authorization: Signature ".$hash2562
		],
		CURLOPT_URL => "https://testpay.recargajogo.com.br/api/partner/eprepag_br/notify"
	]);
	$resultado1 = json_decode(curl_exec($curl1), true);
	//$info = curl_getinfo($curl1);
	curl_close($curl1);

	var_dump($resultado1);
	echo "<hr>";
		
}
disparo();
?>