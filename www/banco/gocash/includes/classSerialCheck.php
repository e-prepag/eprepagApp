<?php

/**
 * SerialCheck
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName "SerialCheck" da Integracao com GoCash 
 * 
 * @author Fabio S. Santos
 *
*/
class SerialCheck {
		
	public $checkRequest; // SerialCheckReq
	
	public function getRequestData($requestData) {		
		$this->checkRequest = new SerialCheckReq();
		$this->checkRequest->ClientID = $requestData["ClientID"];
		$this->checkRequest->Currency = $requestData["Currency"];
		$this->checkRequest->SerialCode = $requestData["SerialCode"];
		$this->checkRequest->GameCode = $requestData["GameCode"];		
		return $this;		 
	}
	
	public function getResponseData($soapResponseData) {				 					
/*
echo "soapResponseData->SerialCheckResult->ReturnValue: ".print_r($soapResponseData->SerialCheckResult->ReturnValue, true)." (".((isset($soapResponseData->SerialCheckResult->ReturnValue))?"YES":"Nope").")<br>";
echo "soapResponseData->SerialCheckResult->Currency: ".$soapResponseData->SerialCheckResult->Currency." (".((isset($soapResponseData->SerialCheckResult->Currency))?"YES":"Nope").")<br>";
*/
		$serialCheckRecord = array();
		$serialCheckRecord['IndexNo'] = $soapResponseData->SerialCheckResult->IndexNo;
		$serialCheckRecord['UseState'] = $soapResponseData->SerialCheckResult->UseState;
		$serialCheckRecord['SellType'] = $soapResponseData->SerialCheckResult->SellType;
		$serialCheckRecord['SettleType'] = $soapResponseData->SerialCheckResult->SettleType;
		$serialCheckRecord['FaceAmt'] = $soapResponseData->SerialCheckResult->FaceAmt;
		$serialCheckRecord['RemainAmt'] = $soapResponseData->SerialCheckResult->RemainAmt;
		if(isset($soapResponseData->SerialCheckResult->Currency)) {
			$serialCheckRecord['Currency'] = $soapResponseData->SerialCheckResult->Currency;
		}
		$serialCheckRecord['PubDesc'] = $soapResponseData->SerialCheckResult->PubDesc;
		$serialCheckRecord['SerialType'] = $soapResponseData->SerialCheckResult->SerialType;
		$serialCheckRecord['CardType'] = $soapResponseData->SerialCheckResult->CardType;
		$serialCheckRecord['ExpDate'] = $soapResponseData->SerialCheckResult->ExpDate;
		$serialCheckRecord['RegDate'] = $soapResponseData->SerialCheckResult->RegDate;
		$serialCheckRecord['UpdDate'] = $soapResponseData->SerialCheckResult->UpdDate;
		$serialCheckRecord['ReturnValue']['RetMsg'] = $soapResponseData->SerialCheckResult->ReturnValue->RetMsg;
		$serialCheckRecord['ReturnValue']['RetCode'] = $soapResponseData->SerialCheckResult->ReturnValue->RetCode;

//echo "IN getResponseData: <pre>".print_r($soapResponseData->SerialCheckResult, true)."</pre>\n";

		return $serialCheckRecord;
	}	

}

class SerialCheckReq {
	public $ClientID; // string
	public $Currency; // string
	public $SerialCode; // string
	public $GameCode; // string
}


?>