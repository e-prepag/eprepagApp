<?php

class VerificacaoIP {
	
	private $chave;
	private $url;
	private $caminho;
	private $data;
	private $keys;
	
	public function __construct($servico){	
		switch($servico){
			case "iplocation":
			    $this->chave = "5511EF36034BBF328613F089B68D6507";
				$this->url = "https://api.ip2location.io/?";
				$this->caminho = $this->url . http_build_query([
					'ip'      => $_SERVER["REMOTE_ADDR"],
					'key'     => $this->chave,
					'format'  => 'json'
				]);
				$this->keys = [
                    "data" => [
					   "code" => "asn",
					   "org" => "as",
					   "state" => "region_name",
					   "uf" => "city_name"
					]					
				];
			break;
			case "findIP":
				$this->chave = "e8407752646b459ab50daee5786a35da";
				$this->url = "https://api.findip.net/".$_SERVER["REMOTE_ADDR"]."/?";
				$this->caminho = $this->url . http_build_query([
					'token'     => $this->chave
				]);
				$this->keys = [
                    "nivel" => 1,
                    "data" => [
					   "code" => "autonomous_system_number",
					   "org" => "autonomous_system_organization",
					   "state" => "pt-BR",
					   "state_en" => "en",
					   "uf" => "iso_code",
					   "city" => "",
					   "country" => ""
					]					
				];
			break;
		}
	}
	
	public function verifica(){
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $this->caminho);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		$retorno = curl_exec($ch);
		$this->data = json_decode($retorno, true);
		//print_r(json_encode($this->data));
		return $this->montaRetorno();
	}
	
	
	public function montaRetorno(){
			
		$retorno = [];
		if(isset($this->keys["nivel"])){
							
			foreach($this->data["traits"] as $key => $value){
				$key_array = array_search($key, $this->keys["data"]);
				if($key_array != false){
					$retorno[$key_array] = $value;
				}
			}
			
			foreach($this->data["subdivisions"][0]["names"] as $key => $value){
				$key_array = array_search($key, $this->keys["data"]);
				if($key_array != false){
					$retorno[$key_array] = $value;
				}
			}
			
			foreach($this->data["subdivisions"][0] as $key => $value){
				$key_array = array_search($key, $this->keys["data"]);
				if($key_array != false){
					$retorno[$key_array] = $value;
				}
			}
			
			foreach($this->data["subdivisions"][1]["names"] as $key => $value){
				if($key == "en"){
					$retorno["city"] = $value;
				}
			}
			
			foreach($this->data["country"] as $key => $value){
				if($key == "iso_code"){
					$retorno['country'] = $value;
				}
			}
			
		}else{
			
			foreach($this->data as $key => $value){
				$key_array = array_search($key, $this->keys["data"]);
				if($key_array != false){
					$retorno[$key_array] = $value;
				}
			}
			
		}
		
	    return $retorno;
		
	}
}

?>


