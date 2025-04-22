<?php
/**
 * Class Gerais
 * 
 * Classe que vai tratar as Classes de Uso Geral da Integracao com BHN 
 * 
 * @author Wagner de Miranda
 *
*/

//Classe contendo os details do header
class details {

	public $productCategoryCode;	// string
	public $specVersion;		// string

	function __construct() {
		$this->productCategoryCode	= PRODUCTCATEGORYCODE;			
		$this->specVersion		= SPECVERSION;
	} //end Construct
        
} //end class details 

//Classe contendo os dados do header
class header {

	public $signature;	// string
        public $details;	// string

	function __construct() {
		$this->details		= new details();			
		$this->signature	= BHN_SIGNATURE;			
	}

}//end class header

//Classe contendo os dados de additionalTxnFields
class additionalTxnFields {

	public $productId;	// string

	function __construct($productId) {
		$this->productId	= $productId;			
	}

}//end class additionalTxnFields

//Classe contendo os dados de transaction
class transaction {

	public $acquiringInstitutionIdentifier;	// string
	public $additionalTxnFields;		// string
        public $localTransactionDate;		// string
        public $localTransactionTime;		// string
        public $merchantCategoryCode;		// string
        public $merchantIdentifier;		// string
        public $merchantLocation;		// string
        public $merchantTerminalId;		// string
        public $pointOfServiceEntryMode;	// string
        public $primaryAccountNumber;		// string
        public $processingCode;			// string
        public $retrievalReferenceNumber;	// string
        public $systemTraceAuditNumber;		// string
        public $transactionAmount;		// string
        public $transactionCurrencyCode;	// string
        public $transmissionDateTime;		// string

	function __construct($params) {
		$this->acquiringInstitutionIdentifier	= ACQUIRINGINSTITUTIONIDENTIFIER;
                $this->additionalTxnFields		= new additionalTxnFields($params['productId']);
                $this->localTransactionDate		= $params['localTransactionDate'];
                $this->localTransactionTime		= $params['localTransactionTime'];
                $this->merchantCategoryCode		= MERCHANTCATEGORYCODE;
                $this->merchantIdentifier		= MERCHANTIDENTIFIER;
                $this->merchantLocation			= MERCHANTLOCATION;
                $this->merchantTerminalId		= $params['merchantTerminalId'].MERCHANTTERMINALID;
                $this->pointOfServiceEntryMode		= POINTOFSERVICEENTRYMODE;
                $this->primaryAccountNumber		= ($params['productId'] == "505164402135" 
                                                        || $params['productId'] == "505164402406" 
                                                        || $params['productId'] == "505164402407" 
                                                        || $params['productId'] == "505164405223" 
                                                        || $params['productId'] == "505164405222" 
                                                        || $params['productId'] == "505164401997" 
                                                        || $params['productId'] == "505164405225" 
                                                        || $params['productId'] == "505164405224"
                                                        || $params['productId'] == "505164403097"
                                                        || $params['productId'] == "505164403793"
                                                        || $params['productId'] == "505164403794"
                                                        || $params['productId'] == "505164408017"
                                                        || $params['productId'] == "505164408018"
                                                        || $params['productId'] == "505164408021"
                                                        || $params['productId'] == "505164408022"
                                                        || $params['productId'] == "505164408023"
                                                        || $params['productId'] == "505164408024"
                                                        || $params['productId'] == "505164401978"
                                                        )?'6039534201000000030':(
                                                        ($params['productId'] == "505164402039" 
                                                        || $params['productId'] == "505164402040"
                                                        || $params['productId'] == "505164401697"
                                                        || $params['productId'] == "505164404333"
                                                        || $params['productId'] == "505164407478"
                                                        )?'6039534201000000050':(
                                                        ($params['productId'] == "505164401622"
                                                        )?'6039534201000000066':PRIMARYACCOUNTNUMBER));
                $this->processingCode			= PROCESSINGCODE;
                $this->retrievalReferenceNumber		= $params['retrievalReferenceNumber'];
                $this->systemTraceAuditNumber		= $params['systemTraceAuditNumber'];
                $this->transactionAmount		= $params['transactionAmount'];
                $this->transactionCurrencyCode		= TRANSACTIONCURRENCYCODE;
                date_default_timezone_set('UTC');
                $this->transmissionDateTime		= date('ymdHis'); //$params['transmissionDateTime'];
                date_default_timezone_set('America/Fortaleza');
	}

}//end class transaction

/*
class XMLBHN {
	public $header;		// string
	public $transaction;	// string
	
	public function __construct($params) {		
		$this->header		= new header();
		$this->transaction	= new transaction($params);
		return array($this);		 
	}
	
	
}//end class XMLBHN
*/
?>