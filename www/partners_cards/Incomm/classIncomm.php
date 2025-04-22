<?php
//Alterando o limeout do PHP para 200 segundos
ini_set('max_execution_time', 200);

class Incomm {
		
	private $service_online;
        
	public function __construct() {
            	
                //Executando a verificação de comunicação com o Distribuidor
                //QUANDO INCOMM Descomentar a linha callServiceOnline para o funcionamento correto
                //QUANDO Utilizar o redirecionamento para servidor Windows 2003 comentar a linha callServiceOnline abaixo
                $retorno = $this->callServiceOnline(XML_REQUISICAO);
                //echo "<pre>".print_r($retorno,true)."</pre>";                    
                        
        }//end function __construct()

	private function set_service_status($status) {
		$this->service_online	=	$status;
	}//end function set_service_status

	public function get_service_status() {
		return $this->service_online;
	}//end function get_service_status


	public function callServiceOnline($typeOfService = '', $service = null, $params = array() ) {
		
                switch ($service) {
                        case INQUIRY:
                            //Setando o nome do serviço
                            $params['servico'] = 'StatInq';
                            // Armazena na classe os dados do serviço informado 
                            $RequestRecord = $this->getRequestObjectServicoRequisicao($typeOfService, $params);
                            break;
                        case REDEEM:
                            //Setando o nome do serviço
                            $params['servico'] = 'Redeem';
                            // Armazena na classe os dados do serviço informado 
                            $RequestRecord = $this->getRequestObjectServicoRequisicao($typeOfService, $params);
                            break;
                        case REVERSE:
                            //Setando o nome do serviço
                            $params['servico'] = 'Reverse';
                            // Armazena na classe os dados do serviço informado 
                            $RequestRecord = $this->getRequestObjectServicoRequisicao($typeOfService, $params);
                            break;
                        default:
                            // Armazena na classe os dados do serviço informado 
                            $RequestRecord = $this->getRequestObjectServicoOnline($typeOfService);
                            break;
                } //end switch
            

                //Convertendo Objeto em Array =====> Necessário por conta de execução em CURL
                $RequestRecord = $this->object_to_array($RequestRecord);

                //Convertendo Array em XML e depois XML em String =====> Necessário por conta de execução em CURL
                $RequestRecord = (string) str_replace('</xml>','',str_replace('<xml>','',print_r($this->array_to_xml($RequestRecord[0], new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>'.'<xml/>'))->asXML(),true)));

                //Adicionando quebra de linhas
                $RequestRecord = str_replace("><", ">".PHP_EOL."<", $RequestRecord).PHP_EOL; 

                //Salvando no LOG variável antes de enviada
                $this->logEvents("Antes do metodo callUsingCURL_Online:".PHP_EOL.$RequestRecord.PHP_EOL."URL de acesso: ".SERVICE_URL.PHP_EOL, MSG_ERROR_LOG, 0);

                //Chamando o serviço
                $resultWS = $this->callUsingCURL_Online($RequestRecord); 

                $this->logEvents("SUCESSO:".PHP_EOL.$resultWS.PHP_EOL, MSG_ERROR_LOG, 0);

                //Testando se conseguiu resposta
                if($this->get_service_status()) {
                    //Capturando a resposta da consulta em vetor
                    $cpfResponseRecord = $this->getResponseObjectServicoOnline($typeOfService, $resultWS);
                }//end if($this->get_service_status())

                return $cpfResponseRecord;


	} //end function callServiceOnline

        
	private function getRequestObjectServicoOnline($typeOfService = '') {	
		
		if ($typeOfService == XML_REQUISICAO) {
                        $serialCheck = new verificaServicoOnline();
                        $serialCheckResponseObj = $serialCheck->getRequestData();
                        return $serialCheckResponseObj;
		}//end if ($typeOfService == XML_REQUISICAO) 
		
	}//end 	function getRequestObjectServicoOnline

	private function getResponseObjectServicoOnline($typeOfService = '', $ResponseData) {			

                if ($typeOfService == XML_REQUISICAO) {
                        $serialCheckResponseObj = $this->getResponseDataServicoOline($ResponseData);
                        return $serialCheckResponseObj;
                } //end if ($typeOfService == XML_REQUISICAO)
		
	}//end function getResponseObjectServicoOnline

	private function getRequestObjectServicoRequisicao($typeOfService = '', $params) {	
		
		if ($typeOfService == XML_REQUISICAO) {
                        $serialCheck = new verificaServicoOnline();
                        $serialCheckResponseObj = $serialCheck->getRequestDataRequisicao($params);
                        return $serialCheckResponseObj;
		}//end if ($typeOfService == XML_REQUISICAO) 
		
	}//end 	function getRequestObjectServicoOnline


	function Req_EfetuaConsulta($requestParams,&$lista_resposta, $servico) {

                $lista_resposta = null;
                if($this->get_service_status()) {

                        //inicio do bloco para a consulta de PIN
                       $params = array(
                                        'pin'		=> $requestParams['pin'],
                                        );
                       $response = $this->callServiceOnline(XML_REQUISICAO, $servico, $params);
                       //echo " consultado<pre>".print_r($response,true)."</pre>".PHP_EOL;
                       //final do bloco para a consulta de 
                       
                       $this->logEvents("Resposta da consulta do  [".$requestParams['pin']."]:".print_r($response,true), MSG_ERROR_LOG, 0);
                       
                       //Salvando informações para variável por referência
                       $lista_resposta = $response;

		}//end if($this->get_service_status())
                
	}//end function Req_EfetuaConsulta($requestParams,&$lista_resposta)

	function RetornaStatusConsultaInquiry($lista_resposta) {
            
                $tempResponse = $GLOBALS['notify_list_values']['ND'];
		
                switch ($lista_resposta['TransferredValueTxnResp']['RespCode']) {
                        case INQUIRY_SUCESS:
                            $tempResponse = $GLOBALS['notify_list_values']['SV'];
                            break;
                        case INQUIRY_INACTIVE:
                            $tempResponse = $GLOBALS['notify_list_values']['IN'];
                            break;
                        case INQUIRY_USED:
                            $tempResponse = $GLOBALS['notify_list_values']['PU'];
                            break;
                        case INQUIRY_NOT_FOUND:
                            $tempResponse = $GLOBALS['notify_list_values']['ND'];
                            break;
                        default:
                            $tempResponse = $GLOBALS['notify_list_values']['ND'];
                            break;
                } //end switch
                
                return $tempResponse;

                
	}//end function RetornaStatusConsultaInquiry

	function RetornaStatusConsultaRedeem($lista_resposta) {
 //echo "<pre>".print_r($lista_resposta,true)."</pre>"; die();
                $tempResponse = $GLOBALS['notify_list_values']['ND'];
		
                switch ($lista_resposta['TransferredValueTxnResp']['RespCode']) {
                        case REDEEM_SUCESS:
                            $tempResponse = $GLOBALS['notify_list_values']['SU'];
                            break;
                        default:
                            $tempResponse = $GLOBALS['notify_list_values']['ND'];
                            break;
                } //end switch
                
                return $tempResponse;

                
	}//end function RetornaStatusConsultaInquiry

	function RetornaValorConsulta($lista_resposta) {
        
                return ($lista_resposta['TransferredValueTxnResp']['ProductResp']['Product']['FaceValue']*1);
                
	}//end function RetornaValorConsulta

	function RetornaDataAtivacaoPINnoCaixa($lista_resposta) {
                
                $auxResponse = NULL;
                
                foreach ($lista_resposta['TransferredValueTxnResp']['Extension'] as $key => $value) {
                    if($value['Name'] == "ActivationDateTime") {
                        $auxResponse = $value['Value'];
                    }//end if($value['Name'] == "ActivationDateTime")
                }//end foreach 
        
                return $auxResponse;
                
	}//end function RetornaDataAtivacaoPINnoCaixa

        private function logEvents($msg, $tipoLog = 'ERROR_LOG') {
			
		if($tipoLog == MSG_ERROR_LOG) 
			$fileLog = LOG_FILE_WS_ERRORS;		
		else if($tipoLog == MSG_TRANSACTION_LOG) 
			$fileLog = LOG_FILE_WS_TRANSACTIONS;
		
		$log  = "=================================================================================================".PHP_EOL;
		$log .= "DATA -> ".date("d/m/Y - H:i:s").PHP_EOL;
		$log .= "SERVICE STATUS  -> ".$this->get_service_status().PHP_EOL;
		$log .= "---------------------------------".PHP_EOL;
		$log .= htmlspecialchars_decode($msg);			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);		
	}//end function logEvents


        function array_to_xml(array $arr, SimpleXMLElement $xml)
        {
            foreach ($arr as $k => $v) {
                if(is_array($v))
                    $this->array_to_xml($v, $xml->addChild($k));
                else $xml->addChild($k, $v);
            }
            return $xml;
        }//end function array_to_xml

        function object_to_array($obj) {
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

        public function callUsingCURL_Online($requestBody) {


            $ws_url = SERVICE_URL; 

            $buffer = curl_init();
            curl_setopt($buffer, CURLOPT_URL, $ws_url);
            curl_setopt($buffer, CURLOPT_FAILONERROR, true);
            
            //  CURLOPT_SSL_VERIFYPEER
            //  verifica a validade do certificado
            curl_setopt($buffer, CURLOPT_SSL_VERIFYPEER, false);
            
            //  CURLOPPT_SSL_VERIFYHOST
            //  verifica se a identidade do servidor bate com aquela informada no certificado
            curl_setopt($buffer, CURLOPT_SSL_VERIFYHOST, 0);

            //TLSv1.2
            curl_setopt($buffer, CURLOPT_SSLVERSION, 6);

            //  CURLOPT_CONNECTTIMEOUT
            //  o tempo em segundos de espera para obter uma conexão
            curl_setopt($buffer, CURLOPT_CONNECTTIMEOUT, 30);

            //  CURLOPT_TIMEOUT
            //  o tempo máximo em segundos de espera para a execução da requisição (curl_exec)
            curl_setopt($buffer, CURLOPT_TIMEOUT, 60);

            //  CURLOPT_RETURNTRANSFER
            //  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
            //  invés de imprimir o resultado na tela. Retorna FALSE se há problemas na requisição
            curl_setopt($buffer, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($buffer, CURLOPT_POST, true);
            curl_setopt($buffer, CURLOPT_POSTFIELDS, $requestBody );// "XML=" . $requestBody );

            $resultado = curl_exec($buffer);

            // Em caso de erro libera aqui
            $info = curl_getinfo($buffer);

            if ($resultado === false || $info['http_code'] != 200) {
                $this->logEvents("No cURL data returned for URL (HTTP_CODE) [". $info['http_code']. "]".PHP_EOL, MSG_ERROR_LOG, 0);
                if (curl_error($buffer)) {
                    $this->logEvents("CURL_ERROR: [".curl_error($buffer)."]".PHP_EOL, MSG_ERROR_LOG, 0);
                }
                
                //Setando como Desativado o Serviço
                $this->set_service_status(false);
                
            } //end if ($resultado === false || $info['http_code'] != 200) 
            else {
                
                //Setnado como serviço ATIVO
                $this->set_service_status(true);
                $this->logEvents("Service enable!".PHP_EOL, MSG_ERROR_LOG);

            }//end else

            return str_replace("><", ">".PHP_EOL."<", $resultado).PHP_EOL;
            
        }//end callUsingCURL_Online
        
        public function getResponseDataServicoOline($ResponseData) {
                $ResponseData = simplexml_load_string($ResponseData);
                $serialsaleRecord = $this->object_to_array($ResponseData);
		return $serialsaleRecord;
	}//end getResponseDataServicoOline


} //end class Incomm
?>