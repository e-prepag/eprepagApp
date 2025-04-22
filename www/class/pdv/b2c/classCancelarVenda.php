<?php
/**
 * cancelarVenda
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "cancelarVenda" da Integracao com B2C 
 * 
 * @author Wagner de Miranda
 *
*/
class cancelarVenda {
		
	public $cnpj;				// string
	public $chave;				// string
	public $pin;				// string
	public $dataCancelamento;	// dateTime
	public $valorEstornado;		// double
	public $motivo;				// string
	
	public function getRequestData($requestData) {		
		$this->cnpj				= $requestData["cnpj"];
		$this->chave			= $requestData["chave"];
		$this->pin				= $requestData["pin"];
		$this->dataCancelamento	= $requestData["dataCancelamento"];
		$this->valorEstornado	= new SoapVar(number_format($requestData["valorEstornado"], 2, '.', ''), XSD_DECIMAL);
		$this->motivo			= $requestData["motivo"];
		return $this;		 
	} 
	
	public function getResponseData($soapResponseData) {				 					

		$serialcancelSale = array();
		$serialcancelSale['resultado'] = $soapResponseData->resultado;
		
		return $serialcancelSale;
	}	

}


?>