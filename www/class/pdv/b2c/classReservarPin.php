<?php
/**
 * reservarPin
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "reservarPin" da Integracao com B2C 
 * 
 * @author Wagner de Miranda
 *
*/
class reservarPin {
		
	public $cnpj;		// string
	public $chave;		// string
	public $coServico;	// string
	
	public function getRequestData($requestData) {		
		$this->cnpj			= $requestData["cnpj"];
		$this->chave		= $requestData["chave"];
		$this->coServico	= $requestData["coServico"];
		return $this;		 
	}
	
	public function getResponseData($soapResponseData) {				 					

		$serialpinRecord = array();
		$serialpinRecord['pin'] = $soapResponseData->pin;
		
		return $serialpinRecord;
	}	

}
// -- PINS CAPTURADO
//035050010000012022
//035050010000012031
?>