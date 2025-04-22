<?php

  //require "/www/db/connect.php";
  //require "/www/db/ConnectionPDO.php";

class Onminidata {
	
	/* configuração dodos cliente */
    private $cpf;
    private $data_nascimento;
   	private $result;
	
	/* configuração global */
	private $http_status_code;
	private $query;
	private $token;
	private $id_search;
	private $key = "19037276000172";
	private $password = "BHcpQN8MP4PatFg";
	
	public function __construct(){
		$this->query = ConnectionPDO::getConnection()->getLink();
	}
	
	private function token(){
	     $data = [
		      "AuthFlow" => "USER_PASSWORD_AUTH",
			  "ClientId" => "v3dmm8ddo8130chj0ofa3cdif",
			  "AuthParameters" => [ "USERNAME" => "19037276000172", "PASSWORD" => "BHcpQN8MP4PatFg" ]
		 ];
	      
         $requestToken = curl_init();
		 curl_setopt_array($requestToken, [
			  CURLOPT_URL => 'https://cognito-idp.us-east-1.amazonaws.com/',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_HTTPHEADER => [
			     'Content-Type: application/x-amz-json-1.1',
				 'Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3',
				 'Referer: https://de-cargo.omninetworking.com.br/login',
				 'X-Amz-Target: AWSCognitoIdentityProviderService.InitiateAuth',
				 'X-Amz-User-Agent: aws-amplify/0.1.x js',
				 'Origin: https://de-cargo.omninetworking.com.br' ,
				 'Connection: keep-alive' ,
				 'TE: Trailers'
			  ],
			  CURLOPT_POSTFIELDS => json_encode($data)
		 ]);
		 
		 $this->result = curl_exec($requestToken);
		 $infoRequest = curl_getinfo($requestToken);
		 
		 $result = json_decode($this->result);
		 $this->token = $result->AuthenticationResult->IdToken;
	}
	
	public function query($cpf, $data_nascimento){	
		$this->token();
		$dataRequest = [
		    [
				"service" => "RECEITA_PF",
				"args" => [
					"cpf" => $cpf,
					"data_nascimento" => $data_nascimento //format = "21/01/1960"
				]
			]
		];
		
		 $requestToken = curl_init();
		 curl_setopt_array($requestToken, [
			  CURLOPT_URL => 'https://doce.api.omnidata.com.br/v1/informations/asynchronous', //desenv
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_HTTPHEADER => [
			     'cliente: 62',
				 'Content-Type: application/json',
				 'Authorization: Bearer '. $this->token 
			  ],
			  CURLOPT_POSTFIELDS => json_encode($dataRequest)
		 ]);
		 
		 $this->result = curl_exec($requestToken);
		 $infoRequest = curl_getinfo($requestToken);
	 
		 $result = json_decode($this->result);
		 
		 /*if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){

            var_dump($result);
			exit;
			
		}*/
		 
         $this->id_search = $result[0]->pesquisa_id;		 
	}
	
	  public function result_status_search($id)
	  {
			$url = "https://doce.api.omnidata.com.br/v1/informations/asynchronous?ids=". $id; //desenv

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
			  $ch,
			  CURLOPT_HTTPHEADER,
			  array(
				'Authorization: Bearer ' . $this->token
			  )
			);

			$response = curl_exec($ch);

			if ($response === false) {
				$error = 'Erro ao fazer a requisição cURL: ' . curl_error($ch);
				
				$arquivo = '/www/log/logONMINIDATAerror.txt';

				$abre_arquivo = fopen($error, 'a+');

				fwrite($abre_arquivo, $response . "\n");

				fclose($abre_arquivo);
				
				return $error;
			  
			} else {
			  $data = json_decode($response, true);
			  
				$arquivo = '/www/log/logONMINIDATA.txt';

				$abre_arquivo = fopen($arquivo, 'a+');

				fwrite($abre_arquivo, $response . "\n");

				fclose($abre_arquivo);
				
			  if ($data !== null) {
				// Verifique se a resposta é uma matriz e se contém pelo menos um elemento
				if (is_array($data) && count($data) > 0) {
				    // Acesse o campo 'status' do primeiro elemento (índice 0) da matriz
				    //$status = $data[0]['status'];
					$dataNascimento = $data[0]['body']["data_nascimento"];

					if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataNascimento)) {
					    // Converte para DD/MM/YYYY
					    $partes = explode('-', $dataNascimento);
					    $dataNascimento = $partes[2] . '/' . $partes[1] . '/' . $partes[0];
					}
				    $info = ["pesquisas" => [ "camposResposta" => [ "status" => $data[0]['status'], "situacao" => $data[0]['body']["situacao"], "nome" => $data[0]['body']["nome"], "data_nascimento" => $dataNascimento ] ] ];
				  
				  curl_close($ch);
				  return $info;
				  //return 'Status: ' . $status;
				} else { 
				  curl_close($ch);
				  return 'A resposta JSON não contém nenhum elemento ou não é uma matriz.';
				}
			  } else {
				curl_close($ch);
				return 'Erro ao analisar a resposta JSON.';
			  }
			}
	  }
	
	public function take_property($object, $name){
		return $object->{$name};
	}
	
	public function collects_data(){
		return $this;
	}
	
}

?>