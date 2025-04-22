<?php

/**
 * PayCash
 *
 * Classe que vai tratar os eventos de Request/Response da ActionName "PayCash" da Integracao com GoCash, 
 * responsavel por fazer o resgate do valor (Redeem) do PIN para o usuário.
 *
 * @author Fabio S. Santos
 *
*/
class PayCash {
			
	public $payRequest; 
	
	public function getRequestData($requestData) {				 	
		$this->payRequest = new PayCashReq();
		$this->payRequest->SerialCodes = $requestData["SerialCodes"];
		$this->payRequest->PayerName = $requestData["PayerName"];
		$this->payRequest->ServiceName = $requestData["ServiceName"];
		$this->payRequest->PayerEmail = $requestData["PayerEmail"];
		$this->payRequest->OrderNo = $requestData["OrderNo"];
		$this->payRequest->PayerID = $requestData["PayerID"];
		$this->payRequest->GameCode = $requestData["GameCode"];
		$this->payRequest->ClientID = $requestData["ClientID"];
		$this->payRequest->ProdName = $requestData["ProdName"];
		$this->payRequest->IPAddr = $requestData["IPAddr"];
		$this->payRequest->ChargeAmt =$requestData["ChargeAmt"] ;
		$this->payRequest->Currency = $requestData["Currency"];
		return $this;		 
	}

	public function getResponseData($soapResponseData) { 					

//echo "  == SUCCESS (2): <pre>".print_r($soapResponseData, true)."</pre><br> ";	

		$payCashRecord = array();
		$payCashRecord['PayType'] = $soapResponseData->PayCashResult->PayType;
		$payCashRecord['ChargeNo'] = $soapResponseData->PayCashResult->ChargeNo;
		$payCashRecord['TransTime'] = $soapResponseData->PayCashResult->TransTime;
		$payCashRecord['ChargedAmt'] = $soapResponseData->PayCashResult->ChargedAmt;
		if(isset($soapResponseData->PayCashResult->Currency)) {
			$payCashRecord['Currency'] = $soapResponseData->PayCashResult->Currency;
		}
		$payCashRecord['NotiURL'] = $soapResponseData->PayCashResult->NotiURL;
		$payCashRecord['ReturnValue']['RetMsg'] =  $soapResponseData->PayCashResult->ReturnValue->RetMsg;
		$payCashRecord['ReturnValue']['RetCode'] =  $soapResponseData->PayCashResult->ReturnValue->RetCode;

		return $payCashRecord;
	}	
	
}

class PayCashReq {
	public $SerialCodes; // string
	public $PayerName; // string
	public $ServiceName; // string
	public $PayerEmail; // string
	public $OrderNo; // string
	public $PayerID; // string
	public $GameCode; // string
	public $ClientID; // string
	public $ProdName; // string
	public $IPAddr; // string
	public $ChargeAmt; // double
	public $Currency; // string
}


?>