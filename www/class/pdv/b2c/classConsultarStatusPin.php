<?php
/**
 * consultarStatusPin
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "consultarStatusPin" da Integracao com B2C 
 * 
 * @author Wagner de Miranda
 *
*/
class consultarStatusPin {
		
	public $cnpj;		// string
	public $chave;		// string
	public $pin;		// string
	
	public function getRequestData($requestData) {		
		$this->cnpj	= $requestData["cnpj"];
		$this->chave= $requestData["chave"];
		$this->pin	= $requestData["pin"];
		return $this;		 
	}
	
	public function getResponseData($soapResponseData) {				 					

		$serialconsultStatusPin = array();
		$serialconsultStatusPin['vendaStatus']['status']	= $soapResponseData->vendaStatus->status;
		$serialconsultStatusPin['vendaStatus']['pin']		= $soapResponseData->vendaStatus->pin;
		
		return $serialconsultStatusPin;
	}	

}


?>