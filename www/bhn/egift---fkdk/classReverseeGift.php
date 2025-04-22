<?php
class classReverseeGift extends classAllCatalogs {
    
    public $reversalEGiftRequestId;
    private $service;
    
    public function __construct($params) {
		$this->setLocation(BHN_EGIFT_URL_REVERSE_EGIFT);

                if($this->validation($params, $errors)){
                    $this->setReversalEGiftRequestId($params['reversalEGiftRequestId']);
                    $this->setService(TRUE);
                }
                else {
                    $msgEmail = $this->getErrors($errors);
                    enviaEmail("estagiario1@e-prepag.com, wagner@e-prepag.com.br, suporte@e-prepag.com.br", null, null, "E-Prepag - Problema ao Validar Dados de Geração eGift BHN", $msgEmail);
                    $this->setService(FALSE);
                }
                
    }//end function __construct()

    private function setReversalEGiftRequestId($reversalEGiftRequestId) {
            $this->reversalEGiftRequestId = $reversalEGiftRequestId;
    }//end function setGiftFrom

    private function getReversalEGiftRequestId() {
            return $this->reversalEGiftRequestId;
    }//end function getGiftFrom
    
    private function setService($service) {
            $this->service = $service;
    }//end function setGiftFrom

    public function getService() {
            return $this->service;
    }//end function getGiftFrom
    
    private function getErrors($errors){
        foreach($errors as $er){
            return "ERRO: " .$er . "<br>"; 
        }
    }//end function getErrors
    
    private function validation($params, &$errors = array()) {
        
        //obrigatorio
        if(isset($params['reversalEGiftRequestId'])){
            if (!$this->checkTypeSize($params['reversalEGiftRequestId'], "NUMERO", 1, 12)) {
                $errors[] = "Problema no reversalEGiftRequestId (Identificador unico da transação na BHN)<br>Valor Inserido: [".$params['reversalEGiftRequestId'] . "]<br>Tamanho do campo: 1 a 12 caracteres";
                
                return false;
            }
        } else{
            if($registro) {
                return true;
            }
            else {
                $errors[] = "Campo 'reversalEGiftRequestId' é OBRIGATÓRIO!";
                return false;
            }
        }
        
        return true;
        
    }//end function validation
    
    public function saveReturn($return_eGift) {
        global $BHN_EGIFT_CODE_STATUS;
        if(isset($return_eGift->total) && $return_eGift->total == "TIMEOUT"){
            $sql = "UPDATE pedidos_egift_bhn SET bhn_json_retorno_generate='".json_encode($return_eGift)."', bhn_status_generate='98' WHERE bhn_id=".$this->getReversalEGiftRequestId().";";
            $this->logEvents("REVERSAL: SQL UPDATE BHN REVERSE eGift (Retorno COM ERRO!!!!!!) :".PHP_EOL.$sql.PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);
            $rs = SQLexecuteQuery($sql);
            return false;
        }//end if(isset($return_eGift->total) && $return_eGift->total == "TIMEOUT")
        else{
            $sql = "UPDATE pedidos_egift_bhn SET bhn_json_retorno_generate='".json_encode($return_eGift)."', bhn_status_generate='".((array_key_exists($return_eGift->transactionStatus,$BHN_EGIFT_CODE_STATUS))?$BHN_EGIFT_CODE_STATUS[trim($return_eGift->transactionStatus)]:"98")."', bhn_pin = '".BHN_EGIFT_MSG_REVERSAL."'  WHERE bhn_id=".$this->getReversalEGiftRequestId().";";
            $this->logEvents("REVERSAL: SQL UPDATE BHN REVERSE eGift (Retorno) :".PHP_EOL.$sql.PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);
            $rs = SQLexecuteQuery($sql);
            if($rs) return true;
            else return false;
        }//end else
    } //end function saveReturn
    
    static function buscaReversal() {
            global $BHN_EGIFT_CODE_REVERSAL;
            $sql = "SELECT bhn_id FROM pedidos_egift_bhn WHERE bhn_status_generate IN ('".implode("','", $BHN_EGIFT_CODE_REVERSAL)."') AND bhn_data > NOW() - '1 day' :: interval ORDER BY bhn_id;";
            echo "SQL Busca Pedido de eGIft Card para REVERSAL:".PHP_EOL.$sql.PHP_EOL;
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) > 0 ) return $rs;
            else return false;
            
    } //end function buscaReversal


    
}

?>
