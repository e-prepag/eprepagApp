<?php
/**
 * Req_Recarga
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "Req_Recarga" da Integracao com RedeSim
 * 
*/
class Req_Recarga {
		
	public $PArRecarga; // Req_RecargaReq
	
	public function getRequestData($requestData) {		
//echo "IN getRequestData: <pre>".print_r($requestData, true)."</pre>\n";

		$this->PArRecarga = new Req_RecargaReq();
		$this->PArRecarga->Usuario = $requestData["Usuario"];
		$this->PArRecarga->Senha = $requestData["Senha"];
		$this->PArRecarga->DV = ((strlen($requestData["DV"])>0)?$requestData["DV"]:"0");

		if(isset($requestData["Telefone"]) && $requestData["Telefone"]) {
			$this->PArRecarga->Telefone = $requestData["Telefone"];
		} else {
			unset($this->PArRecarga->Telefone);
		}
		if(isset($requestData["Operadora"]) && $requestData["Operadora"]) {
			$this->PArRecarga->Operadora = $requestData["Operadora"];
		} else {
			unset($this->PArRecarga->Operadora);
		}
		if(isset($requestData["Valor"]) && $requestData["Valor"]) {
			$this->PArRecarga->Valor = $requestData["Valor"];
		} else {
			unset($this->PArRecarga->Valor);
		}
		
		if(isset($requestData["PontodeVenda"]) && $requestData["PontodeVenda"]) {
			$this->PArRecarga->PontodeVenda = $requestData["PontodeVenda"];
		} else {
			unset($this->PArRecarga->PontodeVenda);
		}

		if(isset($requestData["NIR"]) && $requestData["NIR"]) {
			$this->PArRecarga->NIR = $requestData["NIR"];
		} else {
			unset($this->PArRecarga->NIR);
		}
//echo "IN getRequestData END: <pre>".print_r($this->PArRecarga, true)."</pre>\n";
		return $this;		 
	}
	
	public function getResponseData($soapResponseData) {				 					
/*
echo "soapResponseData->Req_RecargaResult->ReturnValue: ".print_r($soapResponseData->Req_RecargaResult->ReturnValue, true)." (".((isset($soapResponseData->Req_RecargaResult->ReturnValue))?"YES":"Nope").")<br>";
echo "soapResponseData->Req_RecargaResult->Currency: ".$soapResponseData->Req_RecargaResult->Currency." (".((isset($soapResponseData->Req_RecargaResult->Currency))?"YES":"Nope").")<br>";
*/
		$Req_RecargaRecord = array();
		$Req_RecargaRecord['Retorno'] = $soapResponseData->Req_RecargaResult->Retorno;
		if(isset($soapResponseData->Req_RecargaResult->Comprovante)) {
			$Req_RecargaRecord['Comprovante'] = $soapResponseData->Req_RecargaResult->Comprovante;
		}
		if(isset($soapResponseData->Req_RecargaResult->Menu)) {
			$Req_RecargaRecord['Menu'] = $soapResponseData->Req_RecargaResult->Menu;
		}
		if(isset($soapResponseData->Req_RecargaResult->NSU)) {
			$Req_RecargaRecord['NSU'] = $soapResponseData->Req_RecargaResult->NSU;
		}
		if(isset($soapResponseData->Req_RecargaResult->Cupom)) {
			$Req_RecargaRecord['Cupom'] = $soapResponseData->Req_RecargaResult->Cupom;
		}
		if(isset($soapResponseData->Req_RecargaResult->PontodeVenda)) {
			$Req_RecargaRecord["PontodeVenda"] = $soapResponseData->Req_RecargaResult->PontodeVenda;
		} 
		if(isset($soapResponseData->Req_RecargaResult->Operadora)) {
			$Req_RecargaRecord['Operadora'] = $soapResponseData->Req_RecargaResult->Operadora;
		}
		if(isset($soapResponseData->Req_RecargaResult->Telefone)) {
			$Req_RecargaRecord['Telefone'] = $soapResponseData->Req_RecargaResult->Telefone;
		}
		if(isset($soapResponseData->Req_RecargaResult->Valor)) {
			$Req_RecargaRecord['Valor'] = $soapResponseData->Req_RecargaResult->Valor;
		}
                if(isset($soapResponseData->Req_RecargaResult->NIR)) {
			$Req_RecargaRecord['NIR'] = $soapResponseData->Req_RecargaResult->NIR;
                        $GLOBALS['_SESSION']['RS_NIR'] = $Req_RecargaRecord['NIR'];
		}

//echo "IN getResponseData: <pre>".print_r($soapResponseData->Req_RecargaResult, true)."</pre>\n";

		return $Req_RecargaRecord;
	}	

}

class Req_RecargaReq {
	public $Usuario; // string
	public $Senha; // string
	public $DV; // Integer	NNNNNNNN	8
	public $Telefone; // string DDNNNNNNNNN
	public $Operadora; // string - campo menu(N)
	public $Valor; // string - campo menu(N)
	public $PontodeVenda; // string
	public $NIR; // String	XXXXXXXXXX
}


?>