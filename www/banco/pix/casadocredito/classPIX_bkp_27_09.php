<?php
//Alterando o limeout do PHP para (PIX_TIMEOUT/1000) segundos
ini_set('default_socket_timeout', ((PIX_TIMEOUT/1000)+5));

class classPIX {
		
        private $location;
        private $access_token;
        
	public function __construct() {
            
            $this->setLocation(PIX_SERVICE_URL);
            $retorno = $this->sendJSONAuthentication();
            $retorno = json_decode($retorno);
            if($retorno->access_token == PIX_ERRO) {
                die("<br><br>ERRO na Comunicação com o Banco!<br>Por favor, entre em contado com o suporte da E-Prepag e informe o erro de código PIX790954.<br>Obrigado.");
            }
            else {
                $this->setAccessToken($retorno->access_token);
            }
            
            //Bloco que vai para página de checkout
            $params = array (
                'valor'     => '0.50',
                'id_venda'  => 'G8978978798798798'
            );
            echo $this->callService($params);
            
        }//end function __construct()

	private function setLocation($location) {
		$this->location = $location;
	}//end function setLocation

	public function getLocation() {
		return $this->location;
	}//end function getLocation

	private function setAccessToken($access_token) {
		$this->access_token = $access_token;
	}//end function setLocation

	public function getAccessToken() {
		return $this->access_token;
	}//end function getLocation

	public function callService($params) {
						
            $resposta = $this->sendJSON($params);
            $resposta = json_decode($resposta);
            if($resposta->codigo == PIX_ERRO) {
                die("<br><br>ERRO na Comunicação com o Banco!<br>Por favor, entre em contado com o suporte da E-Prepag e informe o erro de código PIX985235.<br>Obrigado.");
            }
            else {
                //echo "<pre>".print_r($resposta, true)."</pre>";
                //echo $resposta->imagemQRCodeInBase64;
                return '<img src="data:image/png;base64, '.$resposta->imagemQRCodeInBase64.'" alt="Red dot" width="200px" height="200px"/>';
            }
    
	} //end function callService
		
	// General methods request
	private function getRequestObject($typeOfService = '', $requestParams = array()) {	
		
		if ($typeOfService == PIX_JSON_REQUISICAO) {
                        $serialCheck = new JSONEstruturaPIX();
                        $serialCheckResponseObj = $serialCheck->getRequestData($requestParams);
                        return $serialCheckResponseObj;
		}//end if ($typeOfService == PIX_JSON_REQUISICAO) 
		
	}//end 	function getRequestObject

	// General method Response
	private function getResponseObject($typeOfService = '', $soapResponseData) {			

                if ($typeOfService == PIX_JSON_REQUISICAO) {
                        $serialCheck = new JSONEstruturaPIX();
                        $serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
                        return $serialCheckResponseObj;
                } //end if ($typeOfService == PIX_JSON_REQUISICAO)
		
	}//end function getResponseObject

	private function logEvents($msg) {
			
		$fileLog = PIX_ERROR_LOG_FILE;
		
		$log  = "=================================================================================================".PHP_EOL;
		$log .= "DATA -> ".date("d/m/Y - H:i:s").PHP_EOL;
		$log .= "---------------------------------".PHP_EOL;
		$log .= htmlspecialchars_decode($msg);			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);		
	}//end function logEvents

        private function sendJSONAuthentication() {
            
            $resultado = NULL;
            $autenticacao = new Authentication();
            $autenticacao = urldecode(http_build_query($autenticacao));

            $curl = curl_init(); 

            curl_setopt_array($curl, array(
              CURLOPT_URL => $this->getLocation().PIX_RESQUEST_TOKEN,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => ((PIX_TIMEOUT/1000)+10),
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $autenticacao, 
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
              ),
            ));

            $resultado = curl_exec($curl);
            
            // Em caso de erro libera aqui
            $info = curl_getinfo($sessao_curl);

            //Setando Resposta
            if(in_array($info['http_code'], $GLOBALS['PIX_CODE_ERROR']) || in_array($teste_resultado->codigo, $GLOBALS['PIX_CODE_ERROR'])) {
                $this->logEvents("Metodo sendJSONAuthentication no curl_exec:".$this->getLocation()."  is reachable".PHP_EOL."Resultado: ".$resultado.PHP_EOL."INFO da CONSULTA: ".print_r($info,true).PHP_EOL);
                $resultado = '{"access_token":"'.PIX_ERRO.'"}';
            }

            curl_close($curl);

            return $resultado;
            
    }//end function sendjsonAuthentication

    private function sendJSON($params) {
        
            $resultado = NULL;
            $dados = new JSONEstruturaPIX($params);
            /* Trecho responsavel para enviar acentos por JSON
            array_walk_recursive(
                    $dados,
                    function (&$entry) {
                        $entry = urlencode(
                            $entry
                        );
                    }
            );
            */
            $dados = json_encode($dados);

            //echo ($dados).PHP_EOL."<br>";
            $curl = curl_init(); 

            curl_setopt_array($curl, array(
              CURLOPT_URL => $this->getLocation().PIX_QRCODE,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => ((PIX_TIMEOUT/1000)+10),
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $dados, 
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer ".$this->getAccessToken()
              ),
            ));

            $resultado = curl_exec($curl);
            
            // Em caso de erro libera aqui
            $info = curl_getinfo($sessao_curl);

            $teste_resultado = json_decode($resultado);

            //Setando Resposta
            if(in_array($info['http_code'], $GLOBALS['PIX_CODE_ERROR']) || in_array($teste_resultado->codigo, $GLOBALS['PIX_CODE_ERROR'])) {
                $this->logEvents("Metodo sendJSONAuthentication no curl_exec:".$this->getLocation()."  is reachable".PHP_EOL."Resultado: ".$resultado.PHP_EOL."INFO da CONSULTA: ".print_r($info,true).PHP_EOL);
                $resultado = '{"codigo":"'.PIX_ERRO.'"}';
            }

            curl_close($curl);

            return $resultado;           
            
    }//end function sendjson

} //end class classPIX
?>