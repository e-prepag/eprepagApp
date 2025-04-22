<?php

/**
 * Req_Seguros
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "Req_Seguros" da Integracao com RedeSim
 * 
*/
class Req_Seguros {
		
	public $Parametros; // Req_SegurosReq
	
	public function getRequestData($requestData) {		
		$this->Parametros = new Req_SegurosReq();
		$this->Parametros->Usuario = $requestData["Usuario"];
		$this->Parametros->Senha = $requestData["Senha"];
		if(isset($requestData["Produto"]) && $requestData["Produto"]) {
			$this->Parametros->Produto = $requestData["Produto"];
		}
		if(isset($requestData["CPF"]) && $requestData["CPF"]) {
			$this->Parametros->CPF = $requestData["CPF"];
		}
		if(isset($requestData["LocaldeVenda"]) && $requestData["LocaldeVenda"]) {
			$this->Parametros->LocaldeVenda = $requestData["LocaldeVenda"];
		}

		return $this;		 
	}
	
	public function getResponseData($soapResponseData) {				 					
/*
echo "soapResponseData->Req_SegurosResult->ReturnValue: ".print_r($soapResponseData->Req_SegurosResult->ReturnValue, true)." (".((isset($soapResponseData->Req_SegurosResult->ReturnValue))?"YES":"Nope").")<br>";
echo "soapResponseData->Req_SegurosResult->Currency: ".$soapResponseData->Req_SegurosResult->Currency." (".((isset($soapResponseData->Req_SegurosResult->Currency))?"YES":"Nope").")<br>";
*/
		$Req_SegurosRecord = array();
		$Req_SegurosRecord['Retorno'] = $soapResponseData->Req_SegurosResult->Retorno;
		if(isset($soapResponseData->Req_SegurosResult->Apolice)) {
			$Req_SegurosRecord['Apolice'] = $soapResponseData->Req_SegurosResult->Apolice;
		}
		if(isset($soapResponseData->Req_SegurosResult->Item)) {
			$Req_SegurosRecord['Item'] = $soapResponseData->Req_SegurosResult->Item;
		}
		if(isset($soapResponseData->Req_SegurosResult->ItemNome)) {
			$Req_SegurosRecord['ItemNome'] = $soapResponseData->Req_SegurosResult->ItemNome;
		}
		if(isset($soapResponseData->Req_SegurosResult->ItemDescricao)) {
			$Req_SegurosRecord['ItemDescricao'] = $soapResponseData->Req_SegurosResult->ItemDescricao;
		}
		if(isset($soapResponseData->Req_SegurosResult->ItemContrato)) {
			$Req_SegurosRecord['ItemContrato'] = $soapResponseData->Req_SegurosResult->ItemContrato;
		}
		if(isset($soapResponseData->Req_SegurosResult->ItemValor)) {
			$Req_SegurosRecord['ItemValor'] = $soapResponseData->Req_SegurosResult->ItemValor;
		}

//echo "IN getResponseData: <pre>".print_r($soapResponseData->Req_SegurosResult, true)."</pre>\n";

		return $Req_SegurosRecord;
	}	

}

class Req_SegurosReq {
	public $Usuario; // string
	public $Senha; // string
	public $Produto; // string
	public $CPF; // string
	public $LocaldeVenda; // string
}


?>