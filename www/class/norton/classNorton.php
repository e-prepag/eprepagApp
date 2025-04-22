<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Norton{
    
    public $soapClient;
    
    public function __construct(){
        
    }
    
    public function callService($typeOfService = '', $requestParams = array()){
        $nortonRequestRecord = $this->getRequestObject($typeOfService, $requestParams);
        
        if($nortonRequestRecord){
            try{
                $this->soapClient = @new SoapClient(NORTON_WSDL_URL, array(  'location'      => NORTON_URL,
                                                                        'uri'           => NORTON_URL,
                                                                        'cache_wsdl'    => WSDL_CACHE_NONE,
                                                                        //'soap_version'  => SOAP_1_1,
                                                                        'encoding'      => 'ISO-8859-1',
                                                                        'trace'         => 1,
                                                                        'exceptions'    => 1,
                                                                        'connection_timeout' => (60000/1000)
                                                                    )
                                            );
            } catch (SoapFault $e) {
                $this->logEvents( "Caught exception 2A (".utf8_decode($e->faultcode)."): ". utf8_decode($e->getMessage()).PHP_EOL, NORTON_ERROR_LOG, 0);
            }
            
            if($this->soapClient){
                
                //Salvando no LOG variável antes de enviada
                $this->logEvents("Antes do metodo __soapCall (Envio informacoes da remessa):".PHP_EOL.str_replace("><", ">".PHP_EOL."<", print_r($nortonRequestRecord,true)), NORTON_ERROR_LOG, 0);                   

            
                if($typeOfService == NORTON_ELETRONIC_PURCHASE){

                        try{
                            
                            //Chamando o serviço      
                            $resultWS = $this->soapClient->__call($typeOfService, (array)$nortonRequestRecord);

                            $this->logEvents("<hr>SUCESSO".PHP_EOL."<pre>".htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages()))."</pre>".PHP_EOL."<hr>", NORTON_ERROR_LOG, 0);

                            if ($resultWS instanceof SoapFault) {
                                $this->logEvents($this->getErrorMessages($resultWS), NORTON_ERROR_LOG, 0);	
                            } else {
                                //Capturando a resposta da consulta em vetor
                                $nortonRequestRecord = $this->getResponseObject($typeOfService, $resultWS);
                                return $nortonRequestRecord;
                            }

                        } catch (SoapFault $ex) {
                            $this->logEvents("<hr>SUCESSO".PHP_EOL."<pre>".htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages()))."</pre>".PHP_EOL."<hr>", NORTON_ERROR_LOG, 0);
                        }

                }elseif($typeOfService == NORTON_REFUND_TRANSACTION){

                    try{

                        //Chamando o serviço     
                        $resultWS = $this->soapClient->__call($typeOfService, (array)$nortonRequestRecord);

                        $this->logEvents("<hr>SUCESSO".PHP_EOL."<pre>".htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages()))."</pre>".PHP_EOL."<hr>", NORTON_ERROR_LOG, 0);

                        if ($resultWS instanceof SoapFault) {
                            $this->logEvents($this->getErrorMessages($resultWS), NORTON_ERROR_LOG, 0);	
                        } else {
                            //Capturando a resposta da consulta em vetor
                            $nortonRequestRecord = $this->getResponseObject($typeOfService, $resultWS);
                            return $nortonRequestRecord;
                        }

                    } catch (SoapFault $ex) {
                        $this->logEvents("<hr>SUCESSO".PHP_EOL."<pre>".htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages()))."</pre>".PHP_EOL."<hr>", NORTON_ERROR_LOG, 0);
                    }
                }
            }
        }
    }
    
    private function getRequestObject($typeOfService = '', $requestParams = array()) {	
		
        if($typeOfService == NORTON_ELETRONIC_PURCHASE) {
            $serialCheck = new eletronicPurchase();
            $serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
            return $serialCheckRequestObj;
        }elseif($typeOfService == NORTON_REFUND_TRANSACTION){
            $serialCheck = new refundTransactions();
            $serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
            return $serialCheckRequestObj;
        }

    }
    
    private function getResponseObject($typeOfService = '', $soapResponseData) {			

        if ($typeOfService == NORTON_ELETRONIC_PURCHASE) {
            $serialCheck = new eletronicPurchase();
            $serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
            return $serialCheckResponseObj;
                        
        } //end if ($typeOfService == BEXS_XML_REQUISICAO_INFORMACOES_REMESSA)
        
    }//end function getResponseObject($typeOfService = '', $soapResponseData)
    
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
    } //end function object_to_array($obj)

    public function logEvents($msg, $tipoLog = 'ERROR_LOG') {
			
        if($tipoLog == NORTON_ERROR_LOG) 
                $fileLog = NORTON_ERROR_LOG_FILE;		
        else if($tipoLog == NORTON_ELETRONIC_PURCHASE_LOG) 
                $fileLog = NORTON_ELETRONIC_PURCHASE_LOG_FILE;
        else $fileLog = NORTON_ERROR_LOG_FILE;	

        $log  = "=================================================================================================".PHP_EOL;
        $log .= "DATA -> ".date("d/m/Y - H:i:s").PHP_EOL;
        $log .= "---------------------------------".PHP_EOL;
        $log .= htmlspecialchars_decode($msg);			

        $fp = fopen($fileLog, 'a+');
        fwrite($fp, $log);
        fclose($fp);		
    }//end function logEvents($msg, $tipoLog = 'ERROR_LOG')
    
    public function getTransactionMessages() {

        if($this->soapClient) {
            $requestMsg        = htmlspecialchars_decode($this->soapClient->__getLastRequest());
            $requestHeaderMsg  = htmlspecialchars_decode($this->soapClient->__getLastRequestHeaders());
            $responseMsg       = htmlspecialchars_decode($this->soapClient->__getLastResponse());
            $responseHeaderMsg = htmlspecialchars_decode($this->soapClient->__getLastResponseHeaders());

            $msg  = "";
            $msg .= "--------------------------".PHP_EOL;
            $msg .= "Request :".PHP_EOL.PHP_EOL.$requestMsg.PHP_EOL;
            $msg .= "--------------------------".PHP_EOL;
            $msg .= "RequestHeaders:".PHP_EOL.PHP_EOL.$requestHeaderMsg;
            $msg .= "--------------------------".PHP_EOL;
            $msg .= "Response:".PHP_EOL.PHP_EOL.$responseMsg.PHP_EOL.PHP_EOL;
            $msg .= "--------------------------".PHP_EOL;
            $msg .= "ResponseHeaders:".PHP_EOL.PHP_EOL.$responseHeaderMsg.PHP_EOL.PHP_EOL;
        } else {
            $msg = "Erro Interno A: soapClient não definido";
        }
        return $msg;		
    }//end function getTransactionMessages()
}

