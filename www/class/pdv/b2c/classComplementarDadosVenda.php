<?php
/**
 * complementarDadosVenda
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "complementarDadosVenda" da Integracao com B2C 
 * 
 * @author Wagner de Miranda
 *
*/
class complementarDadosVenda {
		
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

		$serialcompleteDataSale = array();
		$serialcompleteDataSale['critica']['codigo']	= $soapResponseData->critica->codigo;
		$serialcompleteDataSale['critica']['mensagem']	= $soapResponseData->critica->mensagem;
		if(!empty($soapResponseData->critica->erros))	$serialcompleteDataSale['critica']['erros']		= $soapResponseData->critica->erros;
		
		return $serialcompleteDataSale;
	}	

}


?>