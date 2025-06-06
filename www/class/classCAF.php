<?php

    require_once "/www/db/connect.php";
	require_once "/www/db/ConnectionPDO.php";
	require_once "/www/includes/load_dotenv.php";

class  ClassCAF {
	
	private $baseurl;
	private $auth_token;
	private $environment = "homol";
	private $connect;
	
	public function __construct() {
		
		$this->baseurl = ($this->environment == "homol")? getenv('CAF_URL_HOMOLOG'): getenv('CAF_URL_PROD');
		$this->auth_token = getenv('CAF_TOKEN_HOMOLOG');
		$this->connect = ConnectionPDO::getConnection()->getLink();
		
	}
	
	public function getAll() {
		
		$url = $this->baseurl . "/transactions";
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json', 
			'Authorization: '. $this->auth_token
		));
		$response = curl_exec($curl);
		
		if($response == false) {
			$error = curl_error($curl);
			curl_close($curl);
			throw new Exception("Error making GET request: " . $error);
		}
		
		curl_close($curl);
		
		return $response;
		
	}
	
	public function getOne($code){
		
		$url = $this->baseurl . "/transactions/". $code;
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json', 
			'Authorization: '. $this->auth_token
		));
		$response = curl_exec($curl);
		
		if($response == false) {
			$error = curl_error($curl);
			curl_close($curl);
			throw new Exception("Error making GET request: " . $error);
		}
		
		curl_close($curl);
		
		return $response;
		
	}
	
	public function generateTransaction($data) {
		
		$url = $this->baseurl . "/transactions?origin=TRUST";
		
		$json = json_encode($data);
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json', 
			'Authorization: '. $this->auth_token
		));
		
		$response = curl_exec($curl);
		
		if($response == false) {
			$error = curl_error($curl);
			curl_close($curl);
			throw new Exception("Error making POST request: " . $error);
		}
		
		curl_close($curl);
		
		return $response;
		
	}
	
	public function generateOnboarding($type, $email, $data){

		$url = $this->baseurl . "/onboardings?origin=TRUST";
		$templateID = ["PJ" => getenv('CAF_PJ_TEMPLATE'), "PF" => getenv('CAF_PF_TEMPLATE')];
		$keyData = ["PJ" => "cnpj", "PF" => "cpf"];
		
		$information = [
		     "type" => $type,
			 "transactionTemplateId" => $templateID[$type],
			 "templateId" => "",
			 "transactionPFTemplateId" => "",
			 "transactionQsaTemplateId" => "",
			 "email" => $email,
			 "smsPhoneNumber" => "",
			 "noExpire" => true,
			 "variables" => [],
			 "attributes" => [
			      $keyData[$type] => $data
			 ]
		];
			
		$curl = curl_init($url);
		curl_setopt_array($curl, [
		     CURLOPT_RETURNTRANSFER => true,
			 CURLOPT_CUSTOMREQUEST => "POST",
			 CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json', 
				'Authorization: '. $this->auth_token
			 ),
			 CURLOPT_POSTFIELDS => json_encode($information)
		]);
		$response = curl_exec($curl);
		$responseHeaders = curl_getinfo($curl);
		curl_close($curl);
		
		if($responseHeaders["http_code"] == 200){
			
			$infoResponse = json_decode($response, true);
			$insertQuery = "INSERT INTO verificacao_caf(tipo,templateid,dados_enviados,dados_recebidos,link_onboarding)VALUES(:TP, :TEMP, :DD_ENV, :DD_REC,:LINK);";
			$insertRow = $this->connect->prepare($insertQuery);
			$insertRow->bindValue(":TP", $type);
			$insertRow->bindValue(":TEMP", $templateID[$type]);
			$insertRow->bindValue(":DD_ENV", json_encode($information));
			$insertRow->bindValue(":DD_REC", $response);
			$insertRow->bindValue(":LINK", $infoResponse["url"]);
			$insertRow->execute();
			
			if($insertRow->rowCount() > 0){
				return true;
			}
			
			return false;
			
		}
				
		//return $response;
	}
	
	public function updateOnboarding($link, $data){
		
		$updateQuery = "update verificacao_caf set resposta_webhook = :DATA where link_onboarding = :LINK;";
		$updateRow = $this->connect->prepare($updateQuery);
		$updateRow->bindValue(":DATA", $data);
		$updateRow->bindValue(":LINK", $link);
		$updateRow->execute();
		
		if($updateRow->rowCount() > 0){
			return true;
		}
		
		return false;
		
	}
}
?>