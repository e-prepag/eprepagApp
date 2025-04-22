<?php
//Alterando o limeout do PHP para (BHN_TIMEOUT/1000) segundos
//ini_set('max_execution_time', ((BHN_TIMEOUT/1000)+50));
ini_set('default_socket_timeout', ((BHN_TIMEOUT/1000)+5));

class classBHN {
		
        private $location;
        
	public function __construct($REQUEST = "TRANSACTION") {
            
		if($REQUEST == "TRANSACTION") {
                        $this->setLocation(BHN_SERVICE_URL_TRANSACTION);
                }
                else {
                        $this->setLocation(BHN_SERVICE_URL_REVERSE);
                }

        }//end function __construct()

	private function setLocation($location) {
		$this->location = $location;
	}//end function setLocation

	public function getLocation() {
		return $this->location;
	}//end function getLocation

	public function callService($typeOfService = '', $requestParams = array()) {
						
		// Armazena na classe os dados do serviço informado 
		$bhnRequestRecord = $this->getRequestObject($typeOfService, $requestParams);
		
                //Preparando o vetor em XML
                $bhnRequestRecord = $bhnRequestRecord[0];
                $bhnRequestRecord = $this->object_to_array($bhnRequestRecord);
                $bhnRequestRecord = $this->array_to_xml($bhnRequestRecord,new SimpleXMLElement('<'.BHN_XML_REQUISICAO.'/>'));                 

                //Salvando no LOG variável antes de enviada 
                $this->logEvents("ANTES do metodo sendXML (".$this->getLocation()."):".PHP_EOL.str_replace("><", ">".PHP_EOL."<", $bhnRequestRecord->asXML()), BHN_MSG_ERROR_LOG, 0);

                //Chamando o serviço
                $resultWS = strtr($this->sendXML($bhnRequestRecord->asXML()), "ÁÍÓÚÉÄÏÖÜËÀÌÒÙÈÃÕÂÎÔÛÊáíóúéäïöüëàìòùèãõâîôûêÇç", 
                                                                              "AIOUEAIOUEAIOUEAOAIOUEaioueaioueaioueaoaioueCc");
                //Removendo &(e comercial)
                $resultWS = str_replace("&", "&amp;", $resultWS);

                //Removendo ©(copyright)
                $resultWS = str_replace(chr(169), "copyright", $resultWS);

                //Removendo ®(registrado)
                $resultWS = str_replace(chr(174), "Registered trademark", $resultWS);

                //Removendo \n
                $resultWS = str_replace("\\n", "", $resultWS);

                //Removendo \f0
                $resultWS = str_replace("\\f0", "", $resultWS);

                //Removendo \a2
                $resultWS = str_replace("\\a2", "", $resultWS);

                //Removendo \ (contrabarra)
                $resultWS = str_replace("\\", "", $resultWS);

                //Removendo " (aspas duplas)
                $resultWS = str_replace('"', "", $resultWS);

                //Removendo ' (aspas simples)
                $resultWS = str_replace("'", "", $resultWS);

                //Salvando no LOG após STR_REPLACE
                $this->logEvents(PHP_EOL.$typeOfService.PHP_EOL."LOG após STR_REPLACE (".$resultWS.")", BHN_MSG_ERROR_LOG, 0);

                //Capturando a resposta da consulta em vetor
                $bhnResponseRecord = $this->getResponseObject($typeOfService, $resultWS);
                
                return $bhnResponseRecord;

	} //end function callService
		
	// General methods request
	private function getRequestObject($typeOfService = '', $requestParams = array()) {	
		
		if ($typeOfService == BHN_XML_REQUISICAO) {
                        $serialCheck = new XMLEstruturaBHN();
                        $serialCheckResponseObj = $serialCheck->getRequestData($requestParams);
                        return $serialCheckResponseObj;
		}//end if ($typeOfService == BHN_XML_REQUISICAO) 
		
	}//end 	function getRequestObject

	// General method Response
	private function getResponseObject($typeOfService = '', $soapResponseData) {			

                if ($typeOfService == BHN_XML_REQUISICAO) {
                        $serialCheck = new XMLEstruturaBHN();
                        $serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
                        return $serialCheckResponseObj;
                } //end if ($typeOfService == BHN_XML_REQUISICAO)
		
	}//end function getResponseObject

	public function Req_EfetuaConsulta($requestParams,&$lista_resposta) {
            
		$lista_resposta = null;
                
                //Incrementar o proximo systemTraceAuditNumber
                $requestParams['systemTraceAuditNumber'] = str_pad($this->getAuditBHN($requestParams['retrievalReferenceNumber']), 6, "0", STR_PAD_LEFT);
 
                //Consulta na BHN
                $responseBHN = $this->callService(BHN_XML_REQUISICAO, $requestParams);

                $this->logEvents("Resposta da consulta do BHN [".$requestParams['productId']."]:".PHP_EOL.print_r($responseBHN,true).PHP_EOL, BHN_MSG_ERROR_LOG, 0);

                //Salvando informações para variável por referência
                $lista_resposta = $responseBHN;

                //Atualizando registro do pedido
                $this->updatePedido($requestParams,$lista_resposta);
                
                //retornando o código da consulta
                //return $responseBHN['header']['details']['statusCode'];
                return isset($responseBHN['transaction']['responseCode'])?$responseBHN['transaction']['responseCode']:NULL;

	}//end function Req_EfetuaConsulta($requestParams,&$lista_resposta)

	private function logEvents($msg, $tipoLog = 'ERROR_LOG') {
			
		if($tipoLog == BHN_MSG_ERROR_LOG) 
			$fileLog = LOG_FILE_BHN_WS_ERRORS;		
		else if($tipoLog == BHN_MSG_TRANSACTION_LOG) 
			$fileLog = LOG_FILE_BHN_WS_TRANSACTIONS;
		
		$log  = "=================================================================================================\n";
		$log .= "DATA -> ".date("d/m/Y - H:i:s")."\n";
		$log .= "---------------------------------\n";
		$log .= htmlspecialchars_decode($msg);			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);		
	}//end function logEvents

        public function array_to_xml(array $arr, SimpleXMLElement $xml)
        {
            foreach ($arr as $k => $v) {
                if(is_array($v))
                    $this->array_to_xml($v, $xml->addChild($k));
                else $xml->addChild($k, $v);
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
        
        public static function xml2array ( $xmlObject, $out = array () ) {
            foreach ( (array)$xmlObject as $index => $node ) {
                $out[$index] = (is_object($node)) ? self::xml2array($node) : $node;
            }

            return $out;
        } //end function xml2array
        
        private function sendXML($xml) {
            
            $resultado = NULL;

            $sessao_curl = curl_init();
            curl_setopt($sessao_curl, CURLOPT_URL, $this->getLocation());
            curl_setopt($sessao_curl, CURLOPT_FAILONERROR, true);

            //Setando o Agente do CURL
            curl_setopt($sessao_curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0');
            
            //Setando que a requisição se trata de um XML
            curl_setopt($sessao_curl, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            
            //  CURLOPT_SSL_VERIFYPEER
            //  verifica a validade do certificado
            curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, 0);
            //  CURLOPPT_SSL_VERIFYHOST
            //  verifica se a identidade do servidor bate com aquela informada no certificado
            curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 0);

            //  CURLOPT_SSL_CAINFO
            //  informa a localização do certificado para verificação com o peer
            curl_setopt($sessao_curl, CURLOPT_CAINFO, ENDERECO_BASE_CERTIFICADO_BHN ."/blast.preprod.blackhawk-net.com.cer"); 
            curl_setopt($sessao_curl, CURLOPT_SSLVERSION, 6);

            //  CURLOPT_CONNECTTIMEOUT
            //  o tempo em segundos de espera para obter uma conexão
            curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, (BHN_TIMEOUT/1000));

            //  CURLOPT_TIMEOUT
            //  o tempo máximo em segundos de espera para a execução da requisição (curl_exec)
            curl_setopt($sessao_curl, CURLOPT_TIMEOUT, ((BHN_TIMEOUT/1000)+10));

            //  CURLOPT_RETURNTRANSFER
            //  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
            //  invés de imprimir o resultado na tela. Retorna FALSE se há problemas na requisição
            curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($sessao_curl, CURLOPT_POST, true);
            curl_setopt($sessao_curl, CURLOPT_POSTFIELDS, $xml );

            $errorFileLog = fopen(LOG_FILE_BHN_WS_ERRORS, "a+");
            curl_setopt($sessao_curl, CURLOPT_VERBOSE, true);
            curl_setopt($sessao_curl, CURLOPT_STDERR, $errorFileLog);
            curl_setopt($sessao_curl, CURLOPT_HEADER, 0);

            $resultado = curl_exec($sessao_curl);

            // Em caso de erro libera aqui
            $info = curl_getinfo($sessao_curl);
        
            //Gerando LOG em arquivo para Debug
            $this->logEvents("FUNÇÃO sendXML no curl_exec:".$this->getLocation()."  is reachable".PHP_EOL."Resultado: ".str_replace("><", ">".PHP_EOL."<",$resultado).PHP_EOL."INFO da CONSULTA: ".print_r($info,true).PHP_EOL, BHN_MSG_ERROR_LOG, 0);
        
            curl_close($sessao_curl);
            
            //Setando Resposta por timeout ['transaction']['responseCode'] //['header']['details']['statusCode']
            if($info['http_code'] == 408) $resultado = "<response><transaction><responseCode>99</responseCode></transaction></response>"; //"<response><header><details><statusCode>99</statusCode></details></header></response>";

            return $resultado;
            
    }//end function sendXML
    
    public function getIdBHN () {
            $sql = "SELECT MAX(bhn_id) as proximo_id FROM pedidos_bhn";
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) != 0){
                $rs_row = pg_fetch_array($rs);
                return ($rs_row['proximo_id'] + 1);
            }//end if($rs && pg_num_rows($rs) != 0)
            else return 1;
    } //end function getIdBHN

    private function getAuditBHN ($retrievalReferenceNumber) {
            $sql = "SELECT MAX(bhn_audit) as proximo_audit FROM pedidos_bhn;";
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) != 0){
                $rs_row = pg_fetch_array($rs);
                $retorno = $rs_row['proximo_audit'] + 1;
                $this->updateBhnAudit($retorno, $retrievalReferenceNumber);
                return $retorno;
            }//end if($rs && pg_num_rows($rs) != 0)
            else {
                $retorno = 1;
                $this->updateBhnAudit($retorno, $retrievalReferenceNumber);
                return $retorno;
            }//end else do if($rs && pg_num_rows($rs) != 0)
    } //end function getAuditBHN

    private function updateBhnAudit($systemTraceAuditNumber, $retrievalReferenceNumber) {
            $sql = "UPDATE pedidos_bhn SET bhn_audit=".($systemTraceAuditNumber*1)." WHERE bhn_id=".($retrievalReferenceNumber*1).";";
            $this->logEvents("SQL UPDATE BHN Audit (systemTraceAuditNumber):".PHP_EOL.$sql.PHP_EOL, BHN_MSG_ERROR_LOG, 0);
            $rs = SQLexecuteQuery($sql);
            if($rs) return true;
            else return false;
            
    } //end function updateBhnAudit

    private function insertPedido($requestParams) {
            if(isset($requestParams['retrievalReferenceNumberOld']) && ($requestParams['retrievalReferenceNumberOld']*1) <>0 ) {
                $auxTentativas = $this->getTentativas($requestParams);
                if ($auxTentativas < BHN_ATTEMPTS_NUMBER) {
                        $sql = "INSERT INTO pedidos_bhn(
                                            bhn_id, 
                                            bhn_audit, 
                                            bhn_vetor, 
                                            bhn_valor, 
                                            bhn_product_id, 
                                            vg_id,
                                            bhn_tentativas)
                                VALUES (
                                        ".$requestParams['retrievalReferenceNumber'].", 
                                        ".$requestParams['systemTraceAuditNumber'].", 
                                        '". json_encode($requestParams)."', 
                                        ".($requestParams['transactionAmount']/100).", 
                                        '".$requestParams['productId']."', 
                                        ".$requestParams['vg_id'].",
                                        ".($auxTentativas+1).");";
                        $this->logEvents("SQL Insert no Processo de RECRIAÇÃO:".PHP_EOL.$sql.PHP_EOL, BHN_MSG_ERROR_LOG, 0);
                        $rs = SQLexecuteQuery($sql);
                        if($rs) return true;
                        else return false;
                }
                else {
                    //enviar email com alerta que atingiu o numero maximo de tentivas
                    if(checkIP()) {
                        if($_SERVER['USERDOMAIN'] == 'VM-DEV'){
                            $assunto = "[DEV] ";
                        } else{
                            $assunto = "[HOMOLOGACAO] ";
                        }
                    }
                    else {
                        $assunto = "[PROD] ";
                    }
                    enviaEmail4(BHN_EMAIL_TO, BHN_EMAIL_CC, BHN_EMAIL_BCC, $assunto."BHN PDV ERRO: Alcançou o número maximo de recriações automáticas", "Um Produto BHN [".$requestParams['productId']."] do Pedido de PDV número [".$requestParams['vg_id']."] alcançou o número máximo de recriações automáticas(".BHN_ATTEMPTS_NUMBER.")", NULL);
                    return true;
                }
            }//end if(isset($requestParams['retrievalReferenceNumberOld']) && ($requestParams['retrievalReferenceNumberOld']*1) <>0 )
            else {
                $sql = "INSERT INTO pedidos_bhn(
                                    bhn_id, 
                                    bhn_audit, 
                                    bhn_vetor, 
                                    bhn_valor, 
                                    bhn_product_id, 
                                    vg_id)
                        VALUES (
                                ".$requestParams['retrievalReferenceNumber'].", 
                                ".$requestParams['systemTraceAuditNumber'].", 
                                '". json_encode($requestParams)."', 
                                ".($requestParams['transactionAmount']/100).", 
                                '".$requestParams['productId']."', 
                                ".$requestParams['vg_id'].");";
                $this->logEvents("SQL Insert:".PHP_EOL.$sql.PHP_EOL, BHN_MSG_ERROR_LOG, 0);
                $rs = SQLexecuteQuery($sql);
                if($rs) return true;
                else return false;
            }//end else do if(isset($requestParams['retrievalReferenceNumberOld']) && ($requestParams['retrievalReferenceNumberOld']*1) <>0 )
            
    } //end function insertPedido

    private function testExiste($requestParams) {
            $sql = "SELECT bhn_id FROM pedidos_bhn WHERE bhn_id=".($requestParams['retrievalReferenceNumber']*1).";";
            $this->logEvents("SQL Test Existe:".PHP_EOL.$sql.PHP_EOL, BHN_MSG_ERROR_LOG, 0);
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) > 0 ) return true;
            else return false;
            
    } //end function testExiste

    private function getTentativas($requestParams) {
            $sql = "SELECT bhn_tentativas FROM pedidos_bhn WHERE bhn_id=".($requestParams['retrievalReferenceNumberOld']*1).";";
            $this->logEvents("SQL Captura Número de Tentativas:".PHP_EOL.$sql.PHP_EOL, BHN_MSG_ERROR_LOG, 0);
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) != 0){
                $rs_row = pg_fetch_array($rs);
                return $rs_row['bhn_tentativas'];
            }//end if($rs && pg_num_rows($rs) != 0)
            else return 1;
            
    } //end function getTentativas

    private function updatePedido($requestParams, $resposta) {
            global $BHN_CODE_SUCESS, $BHN_CODE_REVERSAL, $BHN_CODE_NO_CREATE;
            
            if(!isset($resposta['transaction']['responseCode']) || trim($resposta['transaction']['responseCode']) == "" || is_null($resposta['transaction']['responseCode'])) {
               $resposta['transaction']['responseCode'] = "98";
               $resposta['transaction']['message'] = 'ERRO: XML corrompido, estrutura invalida ou erro inesperado';
            }
            
			$pin_bhn = empty($resposta['transaction']['additionalTxnFields']['redemptionPin'])?trim($resposta['transaction']['additionalTxnFields']['redemptionAccountNumber']):trim($resposta['transaction']['additionalTxnFields']['redemptionPin']);
			
			
            $sql = "UPDATE pedidos_bhn SET bhn_status='".trim($resposta['transaction']['responseCode'])."' "; //['header']['details']['statusCode']
            if($this->getLocation() == BHN_SERVICE_URL_TRANSACTION && in_array(trim($resposta['transaction']['responseCode']),$BHN_CODE_SUCESS)) { //['header']['details']['statusCode']
			 
                $sql .= ", bhn_pin = '".(empty($resposta['transaction']['additionalTxnFields']['redemptionPin'])?trim($resposta['transaction']['additionalTxnFields']['redemptionAccountNumber']):trim($resposta['transaction']['additionalTxnFields']['redemptionPin']))."'
                         , bhn_xml_retorno = '". json_encode($resposta)."' ";
            }
            elseif($this->getLocation() == BHN_SERVICE_URL_TRANSACTION && !in_array(trim($resposta['transaction']['responseCode']), $BHN_CODE_SUCESS)) {
				               
				$parametros = $requestParams;
                $parametros['retrievalReferenceNumberOld']	= $parametros['retrievalReferenceNumber'];
                $parametros['retrievalReferenceNumber']	= str_pad($this->getIdBHN(), 12, "0", STR_PAD_LEFT);
                $parametros['systemTraceAuditNumber']	= str_pad(0, 6, "0", STR_PAD_LEFT);
                //Para gerar novo pedido automático para cada  NÃO sucesso descomentar a linha abaixo
				
				if($pin_bhn == null || $pin_bhn == ""){
					if(!in_array(trim($resposta['transaction']['responseCode']), $BHN_CODE_NO_CREATE)) {
						$this->registroPedido($parametros);
					}	
				}
                $sql .= ", bhn_xml_retorno = '". json_encode($resposta)."' ";
            }
            elseif($this->getLocation() == BHN_SERVICE_URL_REVERSE) {
                if(!in_array(trim($resposta['transaction']['responseCode']), $BHN_CODE_REVERSAL)) {
                        $sql .= ", bhn_pin = '".BHN_MSG_REVERSAL."' ";
                }
                $sql .= ", bhn_xml_retorno = '". json_encode($resposta)."' ";
            }
            $sql .=" WHERE bhn_id=".($requestParams['retrievalReferenceNumber']*1).";";
            $this->logEvents("SQL UPDATE PEDIDO:".PHP_EOL.$sql.PHP_EOL, BHN_MSG_ERROR_LOG, 0);
            $rs = SQLexecuteQuery($sql);
            if($rs) return true;
            else return false;
            
    } //end function updatePedido

    public function buscaRversal() {
            global $BHN_CODE_REVERSAL;
            $sql = "SELECT bhn_vetor FROM pedidos_bhn WHERE bhn_status IN ('".implode("','", $BHN_CODE_REVERSAL)."') AND bhn_pin IS NULL AND bhn_data > NOW() - '1 day' :: interval ORDER BY bhn_id;";
            $this->logEvents("SQL Busca Pedido para REVERSAL:".PHP_EOL.$sql.PHP_EOL, BHN_MSG_ERROR_LOG, 0);
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) > 0 ) return $rs;
            else return false;
            
    } //end function buscaRversal

    public function registroPedido($requestParams) {
        
            //Testando se o pedido já existe
            if(!$this->testExiste($requestParams)) {

                //Inserindo pedido no Banco de dados
                if($this->insertPedido($requestParams)) {
                    $this->logEvents("Pedido inserido no Banco de Dados com Sucesso!".PHP_EOL, BHN_MSG_ERROR_LOG, 0);
                    return true;
                }
                else {
                    $this->logEvents("ERRO ao inserir o Pedido no Banco de Dados!".PHP_EOL, BHN_MSG_ERROR_LOG, 0);  
                    return false;
                }

            }//end if(!$this->testExiste($requestParams))
            else {
                    $this->logEvents("Pedido já existe! (Retorno do teste de existencia do pedido antes de inserir)".PHP_EOL, BHN_MSG_ERROR_LOG, 0);  
                    return false;
            }//end else do if(!$this->testExiste($requestParams))
            
    } //end function registroPedido

    public function buscaTransaction() {
            $sql = "SELECT bhn_vetor,bhn_id FROM pedidos_bhn WHERE bhn_status IS NULL ORDER BY bhn_id;";
            $this->logEvents("SQL Busca Pedido para TRANSACTION:".PHP_EOL.$sql.PHP_EOL, BHN_MSG_ERROR_LOG, 0);
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) > 0 ) return $rs;
            else return false;
            
    } //end function buscaTransaction
	
	public function updateStatusTemporario($id) {
		
            $sql = "update pedidos_bhn set bhn_status = 'PR' where bhn_id = ".$id;
            $this->logEvents("SQL ATUALIZA STATUS PARA PROCESSANDO :".PHP_EOL.$sql.PHP_EOL, BHN_MSG_ERROR_LOG, 0);
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_affected_rows($rs) > 0 ){
				$this->logEvents("Status do pedido atualizado com sucesso ".date("d-m-Y H:i:s").PHP_EOL, BHN_MSG_ERROR_LOG, 0);
				return true;
			}
            else return false;
            
    } //end function updateStatusTemporario
    
    
    private function nextSerial($requestParams) {
            // Obtem o ultimo serial
            $pin_serial = false;
            $sql_serial = "select CAST(pin_serial AS BIGINT) as max_serial from pins where opr_codigo = ".$requestParams['opr_codigo']." and pin_serial!='' order by CAST(pin_serial AS BIGINT) desc limit 1;";
            $rs_serial = SQLexecuteQuery($sql_serial);
            if($rs_serial) {
                    if (pg_num_rows($rs_serial) > 0) {
                            $rs_serial_row = pg_fetch_array($rs_serial);
                            $pin_serial = $rs_serial_row['max_serial'];
                    }
                    else {
                            $pin_serial = 1;
                    }
            } else {
                    echo "No Estoque para esta Operadora possui PIN_SERIAL ALPHA.".PHP_EOL."ERRO na query de buscar próximo Serial: ".PHP_EOL.$sql_serial.PHP_EOL;
                    return false;
            }
            $pin_serial = $pin_serial+1;
            return str_pad(number_format($pin_serial, 0, '', ''), 10, "0", STR_PAD_LEFT);;
            
    }//end function nextSerial
    
    private function nextLote($requestParams) {
        
            $sql = "select max(pin_lote_codigo) as max_pin_lote_codigo from pins where opr_codigo = ".$requestParams['opr_codigo'].";";
            $rs_lote = SQLexecuteQuery($sql);
            if(!$rs_lote || pg_num_rows($rs_lote) == 0) {
                    $ilote = 1;
            } else {
                    $rs_lote_row = pg_fetch_array($rs_lote);
                    $ilote = $rs_lote_row['max_pin_lote_codigo'] + 1;
            }
            return $ilote;
    }//end function nextLote

    private function insereEstoque($requestParams, $resposta) {
        
            //$serial = $this->nextSerial($requestParams);
            $serial = empty($resposta['transaction']['additionalTxnFields']['activationAccountNumber'])?$this->nextSerial($requestParams):trim($resposta['transaction']['additionalTxnFields']['activationAccountNumber']);
            if($serial) {
                    $sql = "insert into pins ( 
                                               pin_serial, 
                                               pin_codigo, 
                                               opr_codigo, 
                                               pin_valor, 
                                               pin_lote_codigo, 
                                               pin_dataentrada, 
                                               pin_canal, 
                                               pin_horaentrada, 
                                               pin_status,
                                               pin_datavenda,
                                               pin_datapedido,
                                               pin_horavenda,
                                               pin_horapedido,
                                               pin_est_codigo,
                                               pin_validade) 
                            values (
                                            '".$serial."',
                                            '".(empty($resposta['transaction']['additionalTxnFields']['redemptionPin'])?trim($resposta['transaction']['additionalTxnFields']['redemptionAccountNumber']):trim($resposta['transaction']['additionalTxnFields']['redemptionPin']))."', 
                                            ".$requestParams['opr_codigo'].",
                                            ".($requestParams['transactionAmount']/100).", 
                                            ".$this->nextLote($requestParams).", 
                                            CURRENT_TIMESTAMP, 
                                            's', 
                                            NOW(),
                                            '6',
                                            NOW(),
                                            NOW(),
                                            '".date ("H:i:s")."',
                                            '".date ("H:i:s")."',
                                            '1',
                                            (NOW() + interval '6 month')
                                    );";
                    echo $sql.PHP_EOL;
                    $rs_pins_save = SQLexecuteQuery($sql);
                    if(!$rs_pins_save) {
                            echo "Erro ao salvar o novo PIN ($sql)".PHP_EOL;
                            return false;
                    }
                    else return true;
            }//end if($serial)
            else return false;
    }//end function insereEstoque
	
    
    private function getPinCodInterno($requestParams,$resposta) {
            $sql = "select pin_codinterno from pins where pin_codigo = '".(empty($resposta['transaction']['additionalTxnFields']['redemptionPin'])?trim($resposta['transaction']['additionalTxnFields']['redemptionAccountNumber']):trim($resposta['transaction']['additionalTxnFields']['redemptionPin']))."' and opr_codigo = ".$requestParams['opr_codigo']." ORDER BY pin_dataentrada DESC, pin_horaentrada DESC LIMIT 1;";
            $rs_pins_estoque = SQLexecuteQuery($sql);
            if(!$rs_pins_estoque) {
                    echo "Erro ao selecionar o novo PIN no estoque ($sql)".PHP_EOL;
                    return false;
            }
            else{
                    $rs_pins_estoque_row = pg_fetch_array($rs_pins_estoque);
                    return $rs_pins_estoque_row['pin_codinterno'];
            }//end else if(!$rs_pins_estoque)
    }//end function getPinCodInterno
    
    private function relacionaPinVendaModelo($requestParams,$resposta){
            $codigoInterno = $this->getPinCodInterno($requestParams,$resposta);
            if($codigoInterno) {
                $sql = "insert into tb_dist_venda_games_modelo_pins (vgmp_vgm_id, vgmp_pin_codinterno) values (" . $requestParams['vgm_id'] . "," . $codigoInterno . ");";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) {
                    echo "Erro ao associar pin no modelo vendido.".PHP_EOL;
                    return false;
                }
                else return $codigoInterno;
            }//end if($codigoInterno) 
            else return false;
    }//end  function relacionaPinVendaModelo
    
    private function insereEstoquePDV($requestParams,$resposta) {
            $pin_codinterno = $this->relacionaPinVendaModelo($requestParams, $resposta);
            if($pin_codinterno) {
                $sql = "insert into pins_dist 
                                select * from pins where pin_codinterno = ".$pin_codinterno.";";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) {
                    echo "Erro ao inserir pins na tabela auxiliar (S).".PHP_EOL.$sql.PHP_EOL;        
                    return false;
                }
                else return true;
            }//end if($pin_codinterno)
            else return false;
    }//end function insereEstoquePDV
    
    public function disponibilizaPIN($requestParams,$resposta) {
        if($this->insereEstoque($requestParams, $resposta)) {
            $this->insereEstoquePDV($requestParams, $resposta);
        }//end if($this->insereEstoque($requestParams, $resposta))
        else return false;
    }//function disponibilizaPIN

} //end class classBHN
?>