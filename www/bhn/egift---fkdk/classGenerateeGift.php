<?php
class classGenerateeGift extends classAllCatalogs {
    
    public $giftFrom;
    public $giftTo;
    public $giftMessage;
    public $giftAmount;
    public $purchaserId;
    public $recipientId;
    public $retrievalReferenceNumber;
    public $productConfigurationId;
    public $notes;
    private $service;
    
    
    public function __construct($params) {
		$this->setLocation(BHN_EGIFT_URL_GENERATE_EGIFT);

                if($this->validation($params, $errors, isset($params['registro'])?$params['registro']:FALSE)){
                    $this->setGiftFrom(isset($params['giftFrom'])?$params['giftFrom']:"");
                    $this->setGiftTo(isset($params['giftTo'])?$params['giftTo']:"");
                    $this->setGiftMessage(isset($params['giftMessage'])?$params['giftMessage']:"");
                    $this->setGiftAmount($params['giftAmount']);
                    $this->setPurchaserId(isset($params['purchaserId'])?$params['purchaserId']:"");
                    $this->setRecipientId(isset($params['recipientId'])?$params['recipientId']:"");
                    $this->setRetrievalReferenceNumber((isset($params['registro'])&&$params['registro']===TRUE)?"":$params['retrievalReferenceNumber']);
                    $this->setProductConfigurationId($params['productConfigurationId']);
                    $this->setNotes(isset($params['notes'])?$params['notes']:"");
                    $this->setService(TRUE);
                }
                else {
                    $msgEmail = $this->getErrors($errors);
                    enviaEmail("estagiario1@e-prepag.com, wagner@e-prepag.com.br, suporte@e-prepag.com.br", null, null, "E-Prepag - Problema ao Validar Dados de Geração eGift BHN", $msgEmail);
                    $this->setService(FALSE);
                }
                
    }//end function __construct()

    private function setGiftFrom($giftFrom) {
            $this->giftFrom = $giftFrom;
    }//end function setGiftFrom

    private function getGiftFrom() {
            return $this->giftFrom;
    }//end function getGiftFrom
    
    private function setGiftTo($giftTo) {
            $this->giftTo = $giftTo;
    }//end function setGiftTo

    private function getGiftTo() {
            return $this->giftTo;
    }//end function getGiftTo
    
    private function setGiftMessage($giftMessage) {
            $this->giftMessage = $giftMessage;
    }//end function setGiftMessage

    private function getGiftMessage() {
            return $this->giftMessage;
    }//end function getGiftMessage
    
    private function setGiftAmount($giftAmount) {
            $this->giftAmount = $giftAmount;
    }//end function setGiftAmount

    private function getGiftAmount() {
            return $this->giftAmount;
    }//end function getGiftAmount
    
    private function setPurchaserId($purchaserId) {
            $this->purchaserId = $purchaserId;
    }//end function setGiftFrom

    private function getPurchaserId() {
            return $this->purchaserId;
    }//end function getGiftFrom
    
    private function setRecipientId($recipientId) {
            $this->recipientId = $recipientId;
    }//end function setGiftFrom

    private function getRecipientId() {
            return $this->recipientId;
    }//end function getGiftFrom
    
    private function setRetrievalReferenceNumber($retrievalReferenceNumber) {
            $this->retrievalReferenceNumber = $retrievalReferenceNumber;
    }//end function setGiftFrom

    private function getRetrievalReferenceNumber() {
            return $this->retrievalReferenceNumber;
    }//end function getGiftFrom
    
    private function setProductConfigurationId($productConfigurationId) {
            $this->productConfigurationId = $productConfigurationId;
    }//end function setGiftFrom

    private function getProductConfigurationId() {
            return $this->productConfigurationId;
    }//end function getGiftFrom
    
    private function setNotes($notes) {
            $this->notes = $notes;
    }//end function setGiftFrom

    private function getNotes() {
            return $this->notes;
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
    
    private function validation($params, &$errors = array(), $registro = FALSE) {
        
        //obrigatorio
        if(isset($params['giftAmount'])){
            if (!$this->checkTypeSize($params['giftAmount'], "NUMERO", 1, 11)) {
                $errors[] = "Problema no giftAmount (Valor do PIN)<br>Valor Inserido: [".$params['giftAmount'] . "]<br>Campo: tipo BigDecimal";
                return false;
            }
        }else{
            $errors[] = "Campo 'giftAmount' é OBRIGATÓRIO!";
            return false;
        }
        
        //obrigatorio
        if(isset($params['retrievalReferenceNumber'])){
            if (!$this->checkTypeSize($params['retrievalReferenceNumber'], "TEXTO", 1, 12)) {
                $errors[] = "Problema no retrievalReferenceNumber (Identificador unico da transação na BHN)<br>Valor Inserido: [".$params['retrievalReferenceNumber'] . "]<br>Tamanho do campo: 1 a 12 caracteres";
                
                return false;
            }
        } else{
            if($registro) {
                return true;
            }
            else {
                $errors[] = "Campo 'retrievalReferenceNumber' é OBRIGATÓRIO!";
                return false;
            }
        }
        
        //obrigatorio
        if(isset($params['productConfigurationId'])){
            if (!$this->checkTypeSize($params['productConfigurationId'], "TEXTO", 5, 255)) {
                $errors[] = "Problema na Data de Emissao <br>Valor Inserido: [".$params['productConfigurationId']."]<br>Tamanho do campo: 5 a 255 caracteres";
                return false;
            }
        } else{
            $errors[] = "Campo 'productConfigurationId' é OBRIGATÓRIO";
            return false;
        }
        
        if(isset($params['giftFrom'])){
            if(!$this->checkTypeSize($params['giftFrom'], "TEXTO", 0, 255) && !is_null($params['giftFrom'])){
                $errors[] = "Problema no giftFrom<br>Valor Inserido: [".$params['giftFrom'] . "]<br>Tamanho do campo: 0 a 255 caracteres";
                return false;
            }
        }
        
        if(isset($params['giftTo'])){
            if(!$this->checkTypeSize($params['giftTo'], "TEXTO", 0, 255) && !is_null($params['giftTo'])){
                $errors[] = "Problema no giftTo<br>Valor Inserido: [".$params['giftTo'] . "]<br>Tamanho do campo: 0 a 255 caracteres";
                return false;
            }
        }
        
        if(isset($params['giftMessage'])){
            if(!$this->checkTypeSize($params['giftMessage'], "TEXTO", 0, 255) && !is_null($params['giftMessage'])){
                $errors[] = "Problema no giftMessage<br>Valor Inserido: [".$params['giftMessage'] . "]<br>Tamanho do campo: 0 a 255 caracteres";
                return false;
            }
        }
        
        if(isset($params['purchaserId'])){
            if(!$this->checkTypeSize($params['purchaserId'], "TEXTO", 0, 255) && !is_null($params['purchaserId'])){
                $errors[] = "Problema no purchaserId<br>Valor Inserido: [".$params['purchaserId'] . "]<br>Tamanho do campo: 0 a 255 caracteres";
                return false;
            }
        }
        
        if(isset($params['recipientId'])){
            if(!$this->checkTypeSize($params['recipientId'], "TEXTO", 0, 255) && !is_null($params['recipientId'])){
                $errors[] = "Problema no recipientId<br>Valor Inserido: [".$params['recipientId'] . "]<br>Tamanho do campo: 0 a 255 caracteres";
                return false;
            }
        }
        
        if(isset($params['notes'])){
            if(!$this->checkTypeSize($params['notes'], "TEXTO", 0, 255) && !is_null($params['notes'])){
                $errors[] = "Problema no notes<br>Valor Inserido: [".$params['notes'] . "]<br>Tamanho do campo: 0 a 255 caracteres";
                return false;
            }
        }
        
        return true;
        
    }//end function validation
    
    public function saveReturn($return_eGift, $vars, $vg_id = NULL) {
        global $BHN_EGIFT_CODE_STATUS;
        if(isset($return_eGift->total) && $return_eGift->total == "TIMEOUT"){
            $sql = "UPDATE pedidos_egift_bhn SET bhn_json_retorno_generate='".json_encode($return_eGift)."', bhn_status_generate='98' WHERE bhn_id=".($vars['recipientId']*1).";";
            $this->logEvents("SQL UPDATE BHN Generate eGift (Retorno COM ERRO!!!!!!) :".PHP_EOL.$sql.PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);
            $rs = SQLexecuteQuery($sql);
            $auxTentativas = $this->getTentativas($vars);
            if ($auxTentativas < BHN_ATTEMPTS_NUMBER) {
                $sql = "INSERT INTO pedidos_egift_bhn ( bhn_valor, bhn_product_id, vg_id, vgm_id, opr_codigo,bhn_tentativas )
                            SELECT bhn_valor, bhn_product_id, vg_id, vgm_id, opr_codigo,(bhn_tentativas+1) FROM pedidos_egift_bhn WHERE bhn_id=".($vars['recipientId']*1).";";
                $this->logEvents("Recriação do regitro para nova tentativa de geração do eGift Card".PHP_EOL."SQL  :".PHP_EOL.$sql.PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);
                $rs = SQLexecuteQuery($sql);
                return false;
            }
            else {
                //enviar email com alerta que atingiu o numero maximo de tentivas
                if(checkIP()) {
                    if($_SERVER['USERDOMAIN'] == 'VM-DEV'){
                        $assunto = "[DEV] ";
                    } else{
                        $assunto = "[HOMOLOGACAO] ";
                    }
                }
                else {
                    $assunto = "[PROD] ";
                }
                enviaEmail4(BHN_EMAIL_TO, BHN_EMAIL_CC, BHN_EMAIL_BCC, $assunto."BHN EGIFT ERRO: Alcançou o número maximo de recriações automáticas", "Um Produto EGIFT BHN [".$vars['productConfigurationId']."] do Pedido de GAMER número [".$vg_id."] alcançou o número máximo de recriações automáticas(".BHN_ATTEMPTS_NUMBER.")", NULL);
                return true;
            }
        }//end if(isset($return_eGift->total) && $return_eGift->total == "TIMEOUT")
        elseif(isset($return_eGift->retrievalReferenceNumber)) {
            $sql = "UPDATE pedidos_egift_bhn SET bhn_json_retorno_generate='".json_encode($return_eGift)."', bhn_status_generate='".$BHN_EGIFT_CODE_STATUS[trim($return_eGift->status)]."' WHERE bhn_id=".($return_eGift->retrievalReferenceNumber*1).";";
            $this->logEvents("SQL UPDATE BHN Generate eGift (Retorno) :".PHP_EOL.$sql.PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);
            $rs = SQLexecuteQuery($sql);
            if($rs) return true;
            else return false;
        }//end elseif(isset($return_eGift->retrievalReferenceNumber)) 
        else return false;
    } //end function saveReturn
    
    private function getTentativas($requestParams) {
            $sql = "SELECT bhn_tentativas FROM pedidos_egift_bhn WHERE bhn_id=".($requestParams['recipientId']*1).";";
            $this->logEvents("SQL Captura Número de Tentativas:".PHP_EOL.$sql.PHP_EOL, BHN_MSG_ERROR_LOG, 0);
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) != 0){
                $rs_row = pg_fetch_array($rs);
                return $rs_row['bhn_tentativas'];
            }//end if($rs && pg_num_rows($rs) != 0)
            else return 1;
            
    } //end function getTentativas
    
    private function insertPedido($dadosPedido) {
            $sql = "INSERT INTO pedidos_egift_bhn(
                                bhn_id,
                                bhn_valor, 
                                bhn_product_id, 
                                vg_id,
                                vgm_id,
                                opr_codigo)
                    VALUES ((select max(bhn_id) + 1 as contagem from pedidos_egift_bhn),
                            ".$this->getGiftAmount().", 
                            '".$this->getProductConfigurationId()."', 
                            ".$dadosPedido['vg_id'].", 
                            ".$dadosPedido['vgm_id'].", 
                            ".$dadosPedido['opr_codigo'].");";
            
            
            $this->logEvents("SQL Insert:".PHP_EOL.$sql.PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);
            $rs = SQLexecuteQuery($sql);
            if($rs) return true;
            else return false;
            
    } //end function insertPedido    

    public function registroPedido($dadosPedido) {
        
        //Inserindo pedido no Banco de dados
        if($this->insertPedido($dadosPedido)) {
            $this->logEvents("Pedido inserido no Banco de Dados com Sucesso!".PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);
            return true;
        }
        else {
            $this->logEvents("ERRO ao inserir o Pedido no Banco de Dados!".PHP_EOL, BHN_EGIFT_MSG_ERROR_LOG);  
            return false;
        }

    } //end function registroPedido

    
}

?>
