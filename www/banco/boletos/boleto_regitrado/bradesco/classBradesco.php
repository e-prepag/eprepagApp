<?php

class classBradesco{
    
    private $location;

    public function __construct() {

        $this->setLocation(BRADESCO_URL_HOMOLOGACAO);

    }//end function __construct()
    
    private function setLocation($location) {
		$this->location = $location;
	}//end function setLocation

	public function getLocation() {
		return $this->location;
	}//end function getLocation
    
    
    public function callService($typeOfService = '', $requestParams = array()) {
						
        // Armazena na classe os dados do servi?o informado 
        $braRequestRecord = $this->getRequestObject($typeOfService, $requestParams);
        if($braRequestRecord) {
                //Preparando o vetor em XML
            $braRequestRecord = $braRequestRecord[0];
            $braRequestRecord = $this->object_to_array($braRequestRecord);
            $braRequestRecord = $this->array_to_xml($braRequestRecord, new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><'.BRADESCO_XML_REQUISICAO.'/>'));                 
                //Salvando no LOG vari?vel antes de enviada 
            $this->logEvents("ANTES do metodo sendXML (".$this->getLocation()."):".PHP_EOL.str_replace("><", ">".PHP_EOL."<", $braRequestRecord->asXML()), BRADESCO_MSG_ERROR_LOG, 0);

                //Chamando o serviço
            $resultWS = strtr($this->sendXML($braRequestRecord->asXML()), "ÁÍÓÚÉÄÏÖÜËÀÌÒÙÈÃÕÂÎÔÛÊáíóúéäïöüëàìòùèãõâîôûêÇç", "AIOUEAIOUEAIOUEAOAIOUEaioueaioueaioueaoaioueCc");
                
                //Capturando a resposta da consulta em vetor
            $braResponseRecord = $this->getResponseObject($typeOfService, $resultWS);
                
            return $braResponseRecord;
                    
        }
        else {
            return false;
        }

    }
    
    
    public function Req_EfetuaConsultaRegistro($requestParams,&$lista_resposta) {
            
		$lista_resposta = null;

                //Consulta na BHN
        $responseBradesco = $this->callService(BRADESCO_XML_REQUISICAO, $requestParams);
        if($responseBradesco){
            $this->logEvents("Resposta do registro do Bradesco [".$requestParams['nosso_numero']."]:".PHP_EOL.print_r($responseBradesco,true).PHP_EOL, BRADESCO_MSG_ERROR_LOG, 0);

              //Salvando informações para variável por referência
            $lista_resposta = $responseBradesco;

            return isset($responseBradesco['status']['codigo'])?$responseBradesco['status']['codigo']:NULL;
        } 
        else { 
            return NULL;
        }
        

	}
    
    public function logEvents($msg, $tipoLog = 'ERROR_LOG') {
			
		if($tipoLog == BRADESCO_MSG_ERROR_LOG) 
			$fileLog = LOG_FILE_BRADESCO_WS_ERRORS;		
		else if($tipoLog == BRADESCO_MSG_TRANSACTION_LOG) 
			$fileLog = LOG_FILE_BRADESCO_WS_TRANSACTIONS;
		
		$log  = PHP_EOL."=================================================================================================".PHP_EOL;
		$log .= "DATA -> ".date("d/m/Y - H:i:s").PHP_EOL;
		$log .= "---------------------------------".PHP_EOL;
		$log .= htmlspecialchars_decode($msg);			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);		
	}
    
    public function array_to_xml(array $arr, SimpleXMLElement $xml)
        {
            foreach ($arr as $k => $v) {
                if(is_array($v))
                    $this->array_to_xml($v, $xml->addChild(htmlspecialchars($k)));
                else $xml->addChild($k, htmlspecialchars ($v));
            }
            return $xml;
        }//end function array_to_xml

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
        
        public function xml2array ( $xmlObject, $out = array () ) {
            foreach ( (array)$xmlObject as $index => $node ) {
                $out[$index] = (is_object($node)) ? self::xml2array($node) : $node;
            }

            return $out;
        } //end function xml2array
    
    
    
    
    // General methods request
	public function getRequestObject($typeOfService = '', $requestParams = array()) {	
		
		if ($typeOfService == BRADESCO_XML_REQUISICAO) {
            $serialCheck = new XMLEstruturaBradesco();
            if($serialCheck->getRequestData($requestParams)){
                $serialCheckResponseObj = $serialCheck->getRequestData($requestParams);
                return $serialCheckResponseObj;
                
            } else{
                return false;
            }
		}		
	}//end 	function getRequestObject
    
    private function getResponseObject($typeOfService = '', $soapResponseData) {			

        if ($typeOfService == BRADESCO_XML_REQUISICAO) {
            $serialCheck = new XMLEstruturaBradesco();
            $serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
            return $serialCheckResponseObj;
        } //end if ($typeOfService == BRADESCO_XML_REQUISICAO)
		
	}
    
    private function sendXML($xml) {

             $resultado = NULL;

             $sessao_curl = curl_init();
             curl_setopt($sessao_curl, CURLOPT_URL, $this->getLocation());
             curl_setopt($sessao_curl, CURLOPT_FAILONERROR, true);

             //Setando o Agente do CURL
             curl_setopt($sessao_curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');

             //composi??o da String base, que ? formada pelo MERCHANTID da loja, concatenada a : chave de Seguran?a
             $stringBase = BRADESCO_MERCHANTID . ":" . BRADESCO_CHAVE_SEGURANCA;
             $headers = array();
             if(checkIP() ) {
                // =============> Ambiente DEV / HOMOLOGA??O
                //URL Homologa??o/testes 
                $headers[] = "Host: homolog.meiosdepagamentobradesco.com.br";
             }
             else {
                //URL Producao 
                $headers[] = "Host: meiosdepagamentobradesco.com.br";
             }
             $headers[] = "Accept: application/xml";
             $headers[] = "Content-Type: application/xml;charset=UTF-8";
             $headers[] = "Authorization: Basic ".base64_encode($stringBase);

             //Setando que a requisi??o se trata de um XML, cuja Authorization ? do tipo Basic
            curl_setopt($sessao_curl, CURLOPT_HTTPHEADER, $headers); //Authorization: OAuth 

             //  CURLOPT_SSL_VERIFYPEER
             //  verifica a validade do certificado
             curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, 0);
            
             //  CURLOPPT_SSL_VERIFYHOST
             //  verifica se a identidade do servidor bate com aquela informada no certificado
             curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 0);

             //  CURLOPT_SSL_CAINFO
             //  informa a localiza??o do certificado para verifica??o com o peer
             //  COMENTEI
             //curl_setopt($sessao_curl, CURLOPT_CAINFO, ENDERECO_BASE_CERTIFICADO_BRADESCO ."/www_e-prepag_com_br.cer");
             // COMENTEI
             //curl_setopt($sessao_curl, CURLOPT_SSLVERSION, 4);

             //  CURLOPT_CONNECTTIMEOUT
             //  o tempo em segundos de espera para obter uma conex?o
             curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, 0);

             //  CURLOPT_TIMEOUT
             //  o tempo m?ximo em segundos de espera para a execu??o da requisi??o (curl_exec)
             curl_setopt($sessao_curl, CURLOPT_TIMEOUT, ((BRADESCO_TIMEOUT/1000)+60));

             //  CURLOPT_RETURNTRANSFER
             //  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
             //  inv?s de imprimir o resultado na tela. Retorna FALSE se h? problemas na requisi??o
             curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);

             curl_setopt($sessao_curl, CURLOPT_POST, true);
             curl_setopt($sessao_curl, CURLOPT_POSTFIELDS,  mb_convert_encoding($xml,'ISO-8859-1','utf-8') );

             $errorFileLog = fopen(LOG_FILE_BRADESCO_WS_ERRORS, "a+");
             curl_setopt($sessao_curl, CURLOPT_VERBOSE, true);
             curl_setopt($sessao_curl, CURLOPT_STDERR, $errorFileLog);
             curl_setopt($sessao_curl, CURLOPT_HEADER, 0);

             $resultado = curl_exec($sessao_curl);

             // Em caso de erro libera aqui
             $info = curl_getinfo($sessao_curl);

             //Gerando LOG em arquivo para Debug
             $this->logEvents("FUNÇÃO sendXML no curl_exec:".$this->getLocation()."  is reachable".PHP_EOL."Resultado:".str_replace("><", ">".PHP_EOL."<",$resultado).PHP_EOL."INFO da CONSULTA: ".print_r($info,true).PHP_EOL, BRADESCO_MSG_ERROR_LOG, 0);

             curl_close($sessao_curl);

            //Setando Resposta por timeout 
            if($info['http_code'] == 408) $resultado = "<response><status><codigo>99</codigo></status></response>"; 

             return $resultado;

     }//end function sendXML
   
}

?>
