<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class eletronicPurchase{
    public $electronicPurchaseRequest;
    
    public function getRequestData($request){
        $this->electronicPurchaseRequest = new electronicPurchaseRequest($request);
        return array($this);
    }
    
    public function getResponseData($soapResponseData) {
        $quebra_string = explode(" ", $soapResponseData);
        
        if(count($quebra_string) > 1){
            try{
                $aux = new SimpleXMLElement($soapResponseData);
                $serialsaleRecord = (new Norton)->xml2array($aux);
                return $serialsaleRecord;
            } catch(Exception $e) {
                return NULL;
            }
        } else{
            return $soapResponseData;
        }
    }
}

class refundTransactions{
    public $refundTransactionsRequest;
    public function getRequestData($request){
        $this->refundTransactionsRequest = new refundTransactionsRequest($request);
        return array($this);
    }
}
