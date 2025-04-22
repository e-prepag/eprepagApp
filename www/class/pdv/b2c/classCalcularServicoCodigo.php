<?php
/**
 * calcularServicoCodigo
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "calcularServicoCodigo" da Integracao com B2C 
 * 
 * @author Wagner de Miranda
 *
*/
class calcularServicoCodigo {
		
	public $calculateServiceCode;
	
	public function getRequestData($requestData) {		
		$this->calculateServiceCode							= new calcularServicoCodigoReq();
		$this->calculateServiceCode->cnpj					= $requestData["cnpj"];
		$this->calculateServiceCode->chave					= $requestData["chave"];
		$this->calculateServiceCode->codigo					= $requestData["codigo"];
		$this->calculateServiceCode->codigoProdutoVarejo	= $requestData["codigoProdutoVarejo"];
		$this->calculateServiceCode->tipoPessoa				= $requestData["tipoPessoa"];
		return $this;		 
	}
	
	public function getResponseData($soapResponseData) {				 					

		$serialcalculateServiceCode = array();
		$serialcalculateServiceCode['CalculoServico'] = $soapResponseData->calcularServicoCodigoResponse->CalculoServico;
		
		return $serialcalculateServiceCode;
	}	

}

class calcularServicoCodigoReq {
	public $cnpj;					// string
	public $chave;					// string
	public $codigo;					// string
	public $codigoProdutoVarejo;	// string
	public $tipoPessoa;				// enum
}


?>