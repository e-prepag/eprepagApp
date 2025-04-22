<?php
/**
 * registrarVenda
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "registrarVenda" da Integracao com B2C 
 * 
 * @author Wagner de Miranda
 *
*/
class registrarVenda {
	public $cnpj;	// string
	public $chave;	// string
	public $venda;	// Venda
	
	public function getRequestData($requestData) {		
		$this->cnpj	= $requestData["cnpj"];
		$this->chave= $requestData["chave"];
		$this->venda= new vendaDetalhesReq($requestData["venda"]);

		return $this;		 
	}
	
	public function getResponseData($soapResponseData) {				 					

		$serialsaleRecord = array();
		$serialsaleRecord['critica']['codigo']		= $soapResponseData->critica->codigo;
		$serialsaleRecord['critica']['mensagem']	= $soapResponseData->critica->mensagem;
		if(!empty($soapResponseData->critica->erros))	$serialsaleRecord['critica']['erros']		= $soapResponseData->critica->erros;

		return $serialsaleRecord;
	}
	
}//end class registrarVenda


?>