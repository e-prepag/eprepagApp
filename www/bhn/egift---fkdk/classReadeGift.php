<?php

class classReadeGift extends classAllCatalogs {
    
    private $retrievalReferenceNumber;
    
    public function __construct($retrievalReferenceNumber) {
		$this->setLocation(BHN_EGIFT_URL_READ_EGIFT);
                $this->setRetrievalReferenceNumber($retrievalReferenceNumber*1);
    }//end function __construct()

    private function setRetrievalReferenceNumber($retrievalReferenceNumber) {
            $this->retrievalReferenceNumber = $retrievalReferenceNumber;
    }//end function setGiftFrom

    private function getRetrievalReferenceNumber() {
            return $this->retrievalReferenceNumber;
    }//end function getGiftFrom
     
    public function saveAccountId($accountId) {
        $sql = "UPDATE pedidos_egift_bhn SET bhn_account_id='".$accountId."' WHERE bhn_id=".$this->getRetrievalReferenceNumber().";";
        $this->logEvents("SQL UPDATE BHN Account ID :".PHP_EOL.$sql.PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);
        $rs = SQLexecuteQuery($sql);
        if($rs) return true;
        else return false;
    } //end function saveAccountId
    
    public function saveReturn($return_eGift) {
        global $BHN_EGIFT_CODE_STATUS, $BHN_EGIFT_CODE_SUCESS;
        $sql = "UPDATE pedidos_egift_bhn SET bhn_json_retorno_read_account='".json_encode($return_eGift)."', bhn_status_read_account='".$BHN_EGIFT_CODE_STATUS[trim($return_eGift->status)]."' ";
        if(in_array($BHN_EGIFT_CODE_STATUS[trim($return_eGift->status)],$BHN_EGIFT_CODE_SUCESS)){
            $sql .= ", bhn_pin =  '".(empty($return_eGift->securityCode)?$return_eGift->accountNumber:$return_eGift->securityCode)."'";
        }
        $sql .= " WHERE bhn_id=".$this->getRetrievalReferenceNumber().";";
        $this->logEvents("SQL UPDATE BHN Read Account (Retorno) :".PHP_EOL.$sql.PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);
        $rs = SQLexecuteQuery($sql);
        if($rs) return true;
        else return false;
    } //end function saveReturn
    
    static function buscaReLoad() {
            global $BHN_EGIFT_CODE_SUCESS;
            $sql = "SELECT bhn_id, bhn_valor, opr_codigo, bhn_account_id, vg_id, vgm_id FROM pedidos_egift_bhn WHERE bhn_status_read_account NOT IN ('".implode("','", $BHN_EGIFT_CODE_SUCESS)."') AND bhn_status_read_account IS NOT NULL ORDER BY vg_id;";
            echo "SQL Busca Pedido de eGIft Card para RERELOAD do PIN:".PHP_EOL.$sql.PHP_EOL;
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) > 0 ) return $rs;
            else return false;
            
    } //end function buscaReversal
    
}

?>
