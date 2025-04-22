<?php

class SMS {
	
     private $token;
	 private $login;
	 
	 public function __construct(){
		 
		 $this->token = '1b30d7b706a1104c4ac2ec0e354955a4';
		 $this->login = 'luisricardojr';
		 
	 }
	 
	 public function sendSMS($tel, $sms = ''){
		
		$code = $this->code();
		if($sms == ''){
			$sms = "Seu código E-Prepag é: ". $code;
		}
		//$_SESSION["SMS_CODE"] = $code;
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'http://painel.kingsms.com.br/kingsms/api.php?acao=sendsms&login='.$this->login.'&token='.$this->token.'&numero='.$tel.'&msg='.urlencode($sms),
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));

		$response = curl_exec($curl);
		
		$file = fopen("/www/log/sms.txt", "a+");
		fwrite($file, "data: ".date("d-m-Y H:i:s")."\n");
		fwrite($file, "conteudo: ".$response."\n");
		fclose($file);
		
		curl_close($curl);		
		$json = json_decode($response, true);
		
		if($json["status"] == "success"){
			return true;
		}
		
		return false;
					 
	 }
	 
	 public function code(){
		 
		 $car = "abcdefghijklmnopqrstuvwxyz1234567890";
		 $limit = 24;
		 $code = "";
		 
		 for($num = 0; $num < $limit; $num++){
			 
			 $code .= $car[rand(0, strlen($car) -1)];
			 
		 }
		 
		 return $code;
	 }
	 
}

?>