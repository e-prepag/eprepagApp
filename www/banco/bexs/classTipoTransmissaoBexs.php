<?php

class informacoesRemessa {
	public $remessa;
	
	public function getRequestData($requestData) {
        $this->remessa = new remessa($requestData);

        return array($this);
	}
    
	public function getResponseData($soapResponseData) {
        $quebra_string = explode(" ", $soapResponseData);
        
        if(count($quebra_string) > 1){
            try{
                $aux = new SimpleXMLElement($soapResponseData);
                $serialsaleRecord = classBexs::xml2array($aux);
                return $serialsaleRecord;
            } catch(Exception $e) {
                return NULL;
            }
        } else{
            return $soapResponseData;
        }
	}
    	
}//end class informacoesRemessa

class operacoesRemessa{
    public $operacao;
    
    public function getRequestData($requestData) {
        
        $this->operacao = new operacao($requestData);
        return array($this);
        
	}
    
    public function getResponseData($ftpResponseData) {
        try{
                //$aux = new SimpleXMLElement($ftpResponseData);
                $serialsaleRecord = classBexs::xml2array($ftpResponseData);
                return $serialsaleRecord;
                
        } catch(Exception $e) {
            return NULL;
        }
	}
    
}
