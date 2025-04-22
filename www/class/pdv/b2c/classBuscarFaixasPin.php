<?php
/**
 * buscarFaixasPin
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "buscarFaixasPin" da Integracao com B2C 
 * 
 * @author Wagner de Miranda
 *
*/
class buscarFaixasPin {
		
	public $cnpj;	// string
	public $chave;	// string
	
	public function getRequestData($requestData) {		
		$this->cnpj	= $requestData["cnpj"];
		$this->chave= $requestData["chave"];
		return $this;		 
	}
	
	public function getResponseData($soapResponseData) {				 					
		$serialsearchRangePin = array();
		$serialsearchRangePin['codigoServico']	= $soapResponseData->buscarFaixasPinResponse->codigoServico;
		$serialsearchRangePin['tipo']			= $soapResponseData->buscarFaixasPinResponse->tipo;
		$serialsearchRangePin['parteFixa']		= $soapResponseData->buscarFaixasPinResponse->parteFixa;
		$serialsearchRangePin['inicio']			= $soapResponseData->buscarFaixasPinResponse->inicio;
		$serialsearchRangePin['fim']			= $soapResponseData->buscarFaixasPinResponse->fim;
		return $serialsearchRangePin;
	}	

}

?>