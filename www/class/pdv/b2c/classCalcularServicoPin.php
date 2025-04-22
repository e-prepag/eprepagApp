<?php
/**
 * calcularServicoPin
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "calcularServicoPin" da Integracao com B2C 
 * 
 * @author Wagner de Miranda
 *
*/
class calcularServicoPin {
		
	public $calculateServicePin;
	
	public function getRequestData($requestData) {		
		$this->calculateServicePin						= new calcularServicoPinReq();
		$this->calculateServicePin->cnpj				= $requestData["cnpj"];
		$this->calculateServicePin->chave				= $requestData["chave"];
		$this->calculateServicePin->pin					= $requestData["pin"];
		$this->calculateServicePin->codigoProdutoVarejo	= $requestData["codigoProdutoVarejo"];
		$this->calculateServicePin->tipoPessoa			= $requestData["tipoPessoa"];
		return $this;		 
	}
	
	public function getResponseData($soapResponseData) {				 					

		$serialcalculateServicePin = array();
		$serialcalculateServicePin['CalculoServico'] = $soapResponseData->calcularServicoPinResponse->CalculoServico;
		
		return $serialcalculateServicePin;
	}	

}

class calcularServicoPinReq {
	public $cnpj;					// string
	public $chave;					// string
	public $pin;					// string
	public $codigoProdutoVarejo;	// string
	public $tipoPessoa;				// enum 
}


?>