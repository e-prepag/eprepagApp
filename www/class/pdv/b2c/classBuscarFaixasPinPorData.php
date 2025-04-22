<?php
/**
 * buscarFaixasPinPorData
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "buscarFaixasPinPorData" da Integracao com B2C 
 * 
 * @author Wagner de Miranda
 *
*/
class buscarFaixasPinPorData {
		
	public $cnpj;			// string
	public $chave;			// string
	public $dataGeracao;	// dateTime
	
	public function getRequestData($requestData) {		
		$this->cnpj			= $requestData["cnpj"];
		$this->chave		= $requestData["chave"];
		$this->dataGeracao	= $requestData["dataGeracao"];
		return $this;		 
	}
	
	public function getResponseData($soapResponseData) {				 					

//echo "<pre>".print_r($soapResponseData,true)."</pre>";
		$serialsearchRangePintoDate = array();
		$serialsearchRangePintoDate['faixas'] = $soapResponseData->buscarFaixasPinPorDataResponse->faixas;
		
		return $serialsearchRangePintoDate;
	}	

}


?>