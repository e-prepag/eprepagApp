<?php

/**
 * PayCashCheck
 *
 * Classe que vai tratar os eventos de Request/Response da ActionName "PayCashCheck" da Integracao com GoCash, 
 * que visa fazer a checagem da utilzação do PIN GoCash em nossa loja.
 *
 * @author Fabio S. Santos
 *
*/
class PayCashCheck {

	public $payRequest;

	public function getRequestData($requestData) {

//echo "  == SUCCESS (1): <pre>".print_r($requestData, true)."</pre><br> ";	

		$this->payRequest = new PayCashCheckReq();
		$this->payRequest->ClientID = $requestData["ClientID"];
		$this->payRequest->Currency = $requestData["Currency"];
		$this->payRequest->SerialCodes = $requestData["SerialCodes"];
		$this->payRequest->GameCode = $requestData["GameCode"];
		$this->payRequest->OrderNo = $requestData["OrderNo"];
		$this->payRequest->ChargeAmt = $requestData["ChargeAmt"];
//echo "  == SUCCESS (1a): <pre>".print_r($this->payRequest, true)."</pre><br> ";	
		return $this;
	}

	public function getResponseData($soapResponseData) {		
//echo "  == SUCCESS (resp): <pre>".print_r($soapResponseData, true)."</pre><br> ";	
//die("Stop dssd");
		$payCashCheckRecord = array();
		$payCashCheckRecord['PayType'] = $soapResponseData->PayCashCheckResult->PayType;
		$payCashCheckRecord['ChargeNo'] = $soapResponseData->PayCashCheckResult->ChargeNo;
		$payCashCheckRecord['TransTime'] = $soapResponseData->PayCashCheckResult->TransTime;
		$payCashCheckRecord['ChargedAmt'] = $soapResponseData->PayCashCheckResult->ChargedAmt;
		if(isset($soapResponseData->PayCashCheckResult->Currency)) {
			$payCashCheckRecord['Currency'] = $soapResponseData->PayCashCheckResult->Currency;
		}
		$payCashCheckRecord['ReturnValue']['RetMsg'] = $soapResponseData->PayCashCheckResult->ReturnValue->RetMsg;
		$payCashCheckRecord['ReturnValue']['RetCode'] = $soapResponseData->PayCashCheckResult->ReturnValue->RetCode;
		return $payCashCheckRecord;		
	}

}

class PayCashCheckReq {
	public $ClientID; // string
	public $Currency; // string
	public $SerialCodes; // string
	public $GameCode; // string
	public $OrderNo; // string
	public $ChargeAmt; // double
}

?>