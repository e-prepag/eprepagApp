<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class requestVendor{
    public $vendorID;
    public $vendorPW;
    public $vendorSiteID;
    
    public function __construct(){
        $this->vendorID = NORTON_VENDOR_ID;
        $this->vendorPW = NORTON_VENDOR_PW;
        $this->vendorSiteID = NORTON_VENDOR_SITE_ID;
    }
}

class requestInfo{
    public $requestID;
    public $resend;
    
    public function __construct($params) {
        $this->requestID = $params["requestID"];
        $this->resend = $params["resend"];
    }
}

class orderDetails{
    public $ecomLang;
    public $orderId;
    public $orderPriceRecord;
    
    public function __construct($params) {
        $this->ecomLang = NORTON_LANG;
        $this->orderId = $params["orderId"];
        $this->orderPriceRecord = new orderPriceRecord($params);
    }
}

class orderPriceRecord{
    public $currency;
    public $taxAmt;
    public $taxExemptID;
    public $total;
    
    public function __construct($params) {
        $this->currency = NORTON_CURRENCY;
        $this->taxAmt = $params["taxAmt"];
        $this->total = $params["total"];
    }
}

class trackingInfo{
    public $siteUser;
    public $storeId;
    
    public function __construct($params){
        $this->siteUser = $params["siteUser"];
        $this->storeId = $params["storeId"];
    }
}

class baseProductPurchaseOrder{
    public $baseProductPurchaseItem;
    
    public function __construct($params){
        $this->baseProductPurchaseItem = new baseProductPurchaseItem($params);
    }
}

class baseProductPurchaseItem{
    public $price;
    public $orderItemNo;
    public $ar;
    public $absContext;
    public $skup;
    
    public function __construct($params){
        
        $this->price = $params["price"];
        $this->orderItemNo = $params["orderItemNo"];
        $this->ar = NORTON_AUTO_RENEWALL;
        $this->absContext = NORTON_RENEWALL_CONTEXT;
        $this->skup = NORTON_PRODUCT_SKU;
    }
}

class refundAmount{
    public $amount;
    public $currency;
    
    public function __construct($params){
        $this->amount = $params["amount"];
        $this->currency = $params["currency"];
    }
}

class refundItem{
    public $orderId;
    public $cancelTxn;
    public $cancelAR;
    public $cancelCode;
    public $refundAmount;
    
    public function __construct($params) {
        $this->orderId = $params["orderId"];
        $this->cancelTxn = $params["cancelTxn"];
        $this->cancelAR = $params["cancelAR"];
        $this->cancelCode = $params["cancelCode"];
        $this->refundAmount = new refundAmount($params);
    }
    
}

class electronicPurchaseRequest{
    
    public $requestVendor;
    public $version;
    public $requestInfo;
    public $orderDetails;
    public $trackingInfo;
    public $baseProductPurchaseOrder;
    
    public function __construct($params){
        $this->requestVendor = new requestVendor();
        $this->version = NORTON_WSDL_VERSION;
        $this->requestInfo = new requestInfo($params);
        $this->orderDetails = new orderDetails($params);
        $this->trackingInfo = new trackingInfo($params);
        $this->baseProductPurchaseOrder = new baseProductPurchaseOrder($params);
    }
    
}


class refundTransactionsRequest{
    public $requestVendor;
    public $version;
    public $requestInfo;
    public $refundItem;
    
    public function __construct($params){
        $this->requestVendor = new requestVendor();
        $this->version = NORTON_WSDL_VERSION;
        $this->requestInfo = new requestInfo($params);
        $this->refundItem = new refundItem($params);
    }
    
}
