<?php
/**
 * XMLEstruturaBHN
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName BHN_XML_REQUISICAO da Integracao com Black Hawk Network 
 * 
 * @author Wagner de Miranda
 *
*/

class XMLEstruturaBHN {
    
        /*
	public $request;		// string
	
	public function getRequestData($params) {		
		$this->request		= new XMLBHN($params);
		return array($this);		 
	}
         * 
         */
    
	public $header;		// string
	public $transaction;	// string
	
	public function getRequestData($params) {		
		$this->header		= new header();
		$this->transaction	= new transaction($params);
		return array($this);		 
	}
        
	public function getResponseData($soapResponseData) {
                //echo "----[<pre>".print_r($soapResponseData,true)."</pre>]----";
                try{
                        $aux = new SimpleXMLElement($soapResponseData);
                        $serialsaleRecord = classBHN::xml2array($aux);
                        return $serialsaleRecord;
                } catch(Exception $e) {
                    return NULL;
                }
	}
	
}//end class XMLEstruturaBHN

?>