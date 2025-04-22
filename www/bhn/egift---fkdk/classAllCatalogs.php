<?php

class classAllCatalogs{
    
    private $location;

    public function __construct() {
		$this->setLocation(BHN_EGIFT_URL_QUERY_PRODUCT_CATALOGS);
    }//end function __construct()
    
    public function setLocation($location) {
            $this->location = $location;
    }//end function setLocation

    private function getLocation() {
            return $this->location;
    }//end function getLocation
    
    private function callService($catalog = NULL, $json = NULL) {
						
        //Salvando no LOG variável antes de enviada 
        $this->logEvents("ANTES do metodo sendRequest (".$this->getLocation().$catalog.")".PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);

        //Chamando o serviço
        $resultWS = $this->sendRequest($catalog, $json);

        return $resultWS;
                    
    }
    
    public function Req_EfetuaConsultaRegistro(&$lista_resposta, $catalog = NULL, $json = NULL) {
            
        $lista_resposta = null;

        //Consulta na BHN
        $responseBHN_EGIFT = $this->callService($catalog, $json);
        if(isset($responseBHN_EGIFT->total) && $responseBHN_EGIFT->total == "TIMEOUT"){
            $this->logEvents("Ocorreu TIMEOUT na consulta [".$this->getLocation().$catalog."]".PHP_EOL.str_repeat("=",80).PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);
            $lista_resposta = $responseBHN_EGIFT;
            return false;
        }
        else {
            $this->logEvents("Resposta do registro do BHN_EGIFT [".$this->getLocation().$catalog."]:".PHP_EOL.print_r($responseBHN_EGIFT,true).PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);

              //Salvando informações para variável por referência
            $lista_resposta = $responseBHN_EGIFT;

            return isset($responseBHN_EGIFT->results->total)?$responseBHN_EGIFT->results->total:NULL;
        }

    }//end function Req_EfetuaConsultaRegistro
    
    public function logEvents($msg, $tipoLog = 'ERROR_LOG') {
			
            if($tipoLog == BHN_EGIFT_MSG_ERROR_LOG) 
                    $fileLog = LOG_FILE_BHN_EGIFT_WS_ERRORS;		
            else if($tipoLog == BHN_EGIFT_MSG_TRANSACTION_LOG) 
                    $fileLog = LOG_FILE_BHN_EGIFT_WS_TRANSACTIONS;

            $log  = PHP_EOL.str_repeat("=",80).PHP_EOL;
            $log .= "DATA -> ".date("d/m/Y - H:i:s").PHP_EOL;
            $log .= str_repeat("-",40).PHP_EOL;
            $log .= htmlspecialchars_decode($msg);			

            $fp = fopen($fileLog, 'a+');
            fwrite($fp, $log);
            fclose($fp);		
    }
    
    private function sendRequest($catalog = NULL, $json = NULL) {
        
            $resultado = NULL;

            $sessao_curl = curl_init();
            curl_setopt($sessao_curl, CURLOPT_URL, $this->getLocation().$catalog);
            curl_setopt($sessao_curl, CURLOPT_FAILONERROR, false);

            //Setando o Agente do CURL
            curl_setopt($sessao_curl, CURLOPT_USERAGENT, 'curl 7.47.0 (x86_64-pc-linux-gnu) libcurl/7.47.0 OpenSSL/1.0.2g zlib/1.2.8');

            $headers = array();
            $headers[] = "Host: ".  str_replace("https://", "", BHN_EGIFT_URL_PREFIX);
            $headers[] = "Accept: application/json; charset=UTF-8";
            $headers[] = "Content-Type: application/json; charset=UTF-8";
            $headers[] = "requestorId: ".BHN_EGIFT_REQUESTORID;
            if(!is_null($json)) {
                $auxJson = json_decode($json);
                if(isset($auxJson->recipientId) && $auxJson->recipientId != "") {
                   $headers[] = "requestId: ".$auxJson->recipientId;
                   unset($auxJson->recipientId);
                   $json = json_encode($auxJson);
                }//end if(isset($auxJson->recipientId) && $auxJson->recipientId != "")
            }//end if(!is_null($json) && isset($json->recipientId) && $json->recipientId != "")
            $this->logEvents("Headers CURL: ".print_r($headers,true));
            //Setando que a requisição se trata de um XML, cuja Authorization é do tipo Basic
            curl_setopt($sessao_curl, CURLOPT_HTTPHEADER, $headers); //Authorization: OAuth 

            //  CURLOPT_SSL_VERIFYPEER
            //  verifica a validade do certificado
            curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, 0);

            //  CURLOPPT_SSL_VERIFYHOST
            //  verifica se a identidade do servidor bate com aquela informada no certificado
            curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 0);

            //  CERTIFICANDO COM AUTORIZAÇÃO POR SENHA
            //  informa a localização do certificado para verificação com o peer
            curl_setopt($sessao_curl, CURLOPT_SSLCERT, CERTIFICADO_BHN_EGIFT_CURLOPT_SSLCERT);
            curl_setopt($sessao_curl, CURLOPT_SSLKEY, CERTIFICADO_BHN_EGIFT_CURLOPT_SSLKEY);
            curl_setopt($sessao_curl, CURLOPT_SSLCERTPASSWD, PASSWORD_CURLOPT_SSLCERTPASSWD);              
            curl_setopt($sessao_curl, CURLOPT_SSLKEYPASSWD, PASSWORD_CURLOPT_SSLKEYPASSWD); 

            curl_setopt($sessao_curl, CURLOPT_SSLVERSION, 6);
            curl_setopt($sessao_curl, CURLOPT_SSL_CIPHER_LIST, 'ECDHE-RSA-AES128-GCM-SHA256,ECDHE-ECDSA-AES128-SHA');

            //  CURLOPT_CONNECTTIMEOUT
            //  o tempo em segundos de espera para obter uma conexão
            curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, 0);

            //  CURLOPT_TIMEOUT
            //  o tempo máximo em segundos de espera para a execução da requisição (curl_exec)
            curl_setopt($sessao_curl, CURLOPT_TIMEOUT, ((BHN_EGIFT_TIMEOUT/1000)+60));

            //  CURLOPT_RETURNTRANSFER
            //  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
            //  invés de imprimir o resultado na tela. Retorna FALSE se há problemas na requisição
            curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);

            if(!is_null($json)) {
               curl_setopt($sessao_curl, CURLOPT_CUSTOMREQUEST, 'POST');
               curl_setopt($sessao_curl, CURLOPT_POST, true);
               curl_setopt($sessao_curl, CURLOPT_POSTFIELDS, $json );                 
            }
            else {
               curl_setopt($sessao_curl, CURLOPT_CUSTOMREQUEST, 'GET');
            }

            $errorFileLog = fopen(LOG_FILE_BHN_EGIFT_WS_ERRORS, "a+");
            curl_setopt($sessao_curl, CURLOPT_VERBOSE, true);
            curl_setopt($sessao_curl, CURLOPT_STDERR, $errorFileLog);
            curl_setopt($sessao_curl, CURLOPT_HEADER, 0);

            $resultado = curl_exec($sessao_curl);
            $auxResultado = json_decode($resultado);

            // Em caso de erro libera aqui
            $info = curl_getinfo($sessao_curl);

            //Gerando LOG em arquivo para Debug
            $this->logEvents("FUNÇÃO sendRequest no curl_exec:".$this->getLocation().$catalog."  is reachable".PHP_EOL."Resultado:".$resultado.PHP_EOL.(isset($GLOBALS['BHN_EGIFT_CODE_STATUS_PROTOCOL_HTTP'][$info['http_code']][isset($auxResultado->errorCode)?$auxResultado->errorCode:""])?$GLOBALS['BHN_EGIFT_CODE_STATUS_PROTOCOL_HTTP'][$info['http_code']][isset($auxResultado->errorCode)?$auxResultado->errorCode:""]:"*****").PHP_EOL."INFO da CONSULTA: ".print_r($info,true).PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);

            curl_close($sessao_curl);

            //Setando Resposta por timeout 
            if($info['http_code'] != 200) $resultado = '{"total":"TIMEOUT"}'; 

            return json_decode($resultado);

     }//end function sendRequest

     public function readVarsRestful($link) {
            if(str_replace(BHN_EGIFT_URL_PREFIX_RESPONSE,"",$link) == $link) {
                $vars_temp = explode("/",str_replace(BHN_EGIFT_URL_PREFIX."/","",$link));
            }
            else {
                $vars_temp = explode("/",str_replace(BHN_EGIFT_URL_PREFIX_RESPONSE,"",$link));
            }
            for($count=0;$count < count($vars_temp); $count+=2) {
                $vars[$vars_temp[$count]] = isset($vars_temp[$count+1])?$vars_temp[$count+1]:"";
            } 
            return $vars;
     }//end function readVarsRestful

     public function object_to_array($obj) {
            if(is_object($obj)) $obj = (array) $obj;
            if(is_array($obj)) {
                $new = array();
                foreach($obj as $key => $val) {
                    $new[$key] = $this->object_to_array($val);
                }
            }
            else $new = $obj;
            return $new;       
    } //end function object_to_array
 
    public function checkTypeSize($var, $type, $min, $max) {
        
        switch (strtoupper($type)) {
            case "TEXTO":
                if(preg_match('/^[A-Za-zÀ-ú0-9\x21-\xBA\s]+$/', $var)) {
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
                
            case "NUMERO":     
                if(preg_match('/^[0-9]+(\\.[0-9]+)?$/', $var)){
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
                
            case "DATA":
                if(preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $var)){
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
            case "TEXTO_ESP":
                if(preg_match('/^[A-Za-z0-9]+$/', $var)) {
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;

        }    
        
    }//end function checkTypeSize
    
}
?>
