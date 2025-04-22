<?php

//Recuperando o backtrace para definir qual include da 'classPrincipal' deve ser colocado nessa página - GAMER OU PDV
$backtrace = debug_backtrace();
$cont_pdv = 0;
$pattern = "/creditos/";
foreach ($backtrace as $i => $param){
    foreach ($param as $indice => $content){
        //O indice 'file' mostra os arquivos que incluíram a 'classBradescoTransferencia'
        if($indice == 'file'){
            if(preg_match($pattern, $content)){
                $cont_pdv++;
            }
        }
    }
}

require_once DIR_INCS . "main.php";
if($cont_pdv > 0){
    require_once DIR_INCS . "pdv/main.php";
} else{
    require_once DIR_INCS . "gamer/main.php";
}

class XMLEstruturaBradescoTransf{
    
    public $merchant_id;
    public $meio_pagamento;
    public $pedido;
    public $comprador;
    //public $token_request_confirmacao_pagamento;
    
    //retorna um array com todas informações a serem transformadas em XML e enviadas ao Web Service Bradesco
    public function getRequestData($params) {
        $errors = array();
        if($this->validation($params, $errors)){
            $this->merchant_id = BRADESCO_MERCHANTID;
            $this->meio_pagamento = BRADESCO_MEIO_PAGAMENTO_TRANSFERENCIA;
            $this->pedido = new pedido($params['pedido']);
            $this->comprador = new comprador($params['comprador']);
            //$this->token_request_confirmacao_pagamento = $params['token_request_confirmacao_pagamento'];
            
            return array($this);
        }
        else {
            $msgEmail = $this->getErrors($errors);
            
            if(checkIP()){
                $assunto = "[DEV] ";
                $destino = "luis.gustavo@e-prepag.com.br";
            } else{
                $assunto = "[PROD] ";
                $destino = "luis.gustavo@e-prepag.com.br, wagner@e-prepag.com.br, suporte@e-prepag.com.br";
            }
            $assunto .= "E-Prepag - Problema no Meio Pagamento Transferência entre contas Bradesco";
            
            $id_tipo_usuario = (isset($params['comprador']['id'])?"<strong>ID do Usuário [".$params['comprador']['tipo_user']."]</strong>: ".$params['comprador']['id']."<br><br>":"Problema ao recuperar o ID e tipo do usuário<br><br>");
            
            enviaEmail($destino, null, null, $assunto, $id_tipo_usuario . $msgEmail);
            
            return false;
        }
			 
	}
    
    public function getErrors($errors){
        $msg = "";
        foreach($errors as $er){
            $msg .=  "ERRO: " .$er . "<br><br>"; 
        }
        return $msg;
    }
    
    public function getResponseData($soapResponseData) {
        try{
            $aux = new SimpleXMLElement($soapResponseData);
            $instClassBradescoTransferencia = new classBradescoTransferencia;
            $serialsaleRecord = $instClassBradescoTransferencia->xml2array($aux);
            return $serialsaleRecord;
        } catch(Exception $e) {
            return NULL;
        }
	}
    
    private function validation($params, &$errors = array()) {
        
        $validou = TRUE;
     
        //obrigatorio
        if(!$this->checkTypeSize(BRADESCO_MERCHANTID, "TEXTO", 9, 36)){
            $errors[] = "Problema no Merchantid<br>";
            $validou = FALSE;
        }
        
        //obrigatorio
        if(!$this->checkTypeSize(BRADESCO_MEIO_PAGAMENTO_TRANSFERENCIA, "TEXTO", 3, 3)){
            $errors[] = "Problema no Meio Pagamento<br>";
            $validou = FALSE;
        }
        
        //obrigatorio
        if(isset($params['pedido']['numero'])){
            if (!$this->checkTypeSize($params['pedido']['numero'], "TEXTO", 1, 23)) {
                $errors[] = "Problema no Numero Documento<br>Valor Inserido: [".$params['pedido']['numero'] . "]<br>Tamanho do campo: 1 a 23 caracteres";
                
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'numero' de Pedido é OBRIGATÓRIO!";
            $validou = FALSE;
        }
        
        //obrigatorio
        if(isset($params['pedido']['valor'])){
            if (!$this->checkTypeSize($params['pedido']['valor'], "NUMERO", 1, 13)) {
                $errors[] = "Problema no Valor Pedido <br>Valor Inserido: [".$params['pedido']['valor']."]<br>Tamanho do campo: 1 a 13 caracteres";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'valor' de Pedido é OBRIGATÓRIO";
            $validou = FALSE;
        }
        
        //obrigatorio
        if(isset($params['pedido']['descricao'])){
            if (!$this->checkTypeSize($params['pedido']['descricao'], "TEXTO", 1, 255)) {
            $errors[] = "Problema na Descricao Pedido <br>Valor Inserido: [".$params['pedido']['descricao']."]<br>Tamanho do campo: 1 a 255 caracteres";
            $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'descricao' de Pedido é OBRIGATÓRIO";
            $validou = FALSE;
        }
        
        //obrigatorio
        if(isset($params['comprador']['nome'])){
            if(!$this->checkTypeSize($params['comprador']['nome'], "TEXTO", 1, 40)){
                $errors[] = "Problema no Nome Comprador<br>Valor Inserido: [".$params['comprador']['nome'] . "]<br>Tamanho do campo: 1 a 40 caracteres";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'nome' do Comprador é OBRIGATÓRIO";
            $validou = FALSE;
        }
        
        //obrigatorio
        if(isset($params['comprador']['documento'])){
            if(!$this->checkTypeSize($params['comprador']['documento'], "TEXTO", 11, 14)){
                $errors[] = "Problema no Documento do Comprador<br>Valor Inserido: [".$params['comprador']['documento'] . "]<br>Tamanho do campo: 11 a 14 caracteres";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'documento' do Comprador é OBRIGATÓRIO";
            $validou = FALSE;
        }
        
        if(isset($params['comprador']['ip'])){
            if(!$this->checkTypeSize($params['comprador']['ip'], "TEXTO", 0, 50) && !is_null($params['comprador']['ip'])){
                $errors[] = "Problema no IP do Comprador<br>Valor Inserido: [".$params['comprador']['ip'] . "]<br>Tamanho do campo: 0 a 50 caracteres";
                $validou = FALSE;
            }
        }
        
        if(isset($params['comprador']['user_agent'])){
            if(!$this->checkTypeSize($params['comprador']['user_agent'], "TEXTO", 0, 255) && !is_null($params['comprador']['user_agent'])){
                $errors[] = "Problema no User Agent do Comprador<br>Valor Inserido: [".$params['comprador']['user_agent'] . "]<br>Tamanho do campo: 0 a 255 caracteres";
                $validou = FALSE;
            }
        }
        
        //obrigatorio
        if(isset($params['comprador']['endereco']['cep'])){
            if(!$this->checkTypeSize($params['comprador']['endereco']['cep'], "TEXTO", 8, 8)){
                $errors[] = "Problema no CEP do Endereco do Comprador<br>Valor Inserido: [".$params['comprador']['endereco']['cep'] . "]<br>Tamanho do campo: apenas 8 caracteres";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'cep' do Endereco do Comprador é OBRIGATÓRIO";
            $validou = FALSE;
        }
        
        //obrigatorio
        if(isset($params['comprador']['endereco']['logradouro'])){
            if(!$this->checkTypeSize($params['comprador']['endereco']['logradouro'], "TEXTO", 1, 70)){
                $errors[] = "Problema no Logradouro do Endereco do Comprador<br>Valor Inserido: [".$params['comprador']['endereco']['logradouro'] ."]<br>Tamanho do campo: 1 a 70 caracteres";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'logradouro' do Endereco do Comprador é OBRIGATÓRIO";
            $validou = FALSE;
        }
        
        //obrigatorio
        if(isset($params['comprador']['endereco']['numero'])){
            if(!$this->checkTypeSize($params['comprador']['endereco']['numero'], "TEXTO", 1, 10)){
                $errors[] = "Problema no Numero do Endereco do Comprador<br>Valor Inserido: [".$params['comprador']['endereco'] . "]<br>Tamanho do campo: 1 a 10 caracteres";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'numero' do Endereco do Comprador é OBRIGATÓRIO";
            $validou = FALSE;
        }
        
        if(isset($params['comprador']['endereco']['complemento']) && !empty($params['comprador']['endereco']['complemento'])){
            if(!$this->checkTypeSize($params['comprador']['endereco']['complemento'], "TEXTO", 0, 20) && !is_null($params['comprador']['endereco']['complemento'])){
                $errors[] = "Problema no Complemento do Endereco do Comprador<br>Valor Inserido: [".$params['comprador']['endereco']['complemento'] . "]<br>Tamanho do campo: 1 a 20 caracteres";
                $validou = FALSE;
            }
        }
        
        //obrigatorio
        if(isset($params['comprador']['endereco']['bairro'])){
            if(!$this->checkTypeSize($params['comprador']['endereco']['bairro'], "TEXTO", 1, 50)){
                $errors[] = "Problema no Bairro do Endereco do Comprador<br>Valor Inserido: [".$params['comprador']['endereco']['bairro'] . "]<br>Tamanho do campo: 1 a 50 caracteres";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'bairro' do Endereco do Comprador é OBRIGATÓRIO";
            $validou = FALSE;
        }
        
        //obrigatorio
        if(isset($params['comprador']['endereco']['cidade'])){
            if(!$this->checkTypeSize($params['comprador']['endereco']['cidade'], "TEXTO", 1, 50)){
                $errors[] = "Problema na cidade do Endereco do Comprador<br>Valor Inserido: [".$params['comprador']['endereco']['cidade'] . "]<br>Tamanho do campo: 1 a 100 caracteres";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'cidade' do Endereco do Comprador é OBRIGATÓRIO";
            $validou = FALSE;
        }
        
        //obrigatorio
        if(isset($params['comprador']['endereco']['uf'])){
            if(!$this->checkTypeSize($params['comprador']['endereco']['uf'], "TEXTO", 2, 2)){
                $errors[] = "Problema no UF do Endereco do Comprador<br>Valor Inserido: [".$params['comprador']['endereco']['uf'] . "]<br>Tamanho do campo: apenas 2 caracteres";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'uf' do Endereco do Comprador é OBRIGATÓRIO";
            $validou = FALSE;
        }
        
        if($validou){
            return TRUE;
        } else{
            return FALSE;
        }   
    }
    
    private function checkTypeSize($var, $type, $min, $max) {
        
        switch (strtoupper($type)) {
            case "TEXTO":
                if(preg_match('/^[A-Za-zÀ-ú0-9\x21-\xBAü\s]+$/', $var)) {
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
            case "NUMERO":
                if(preg_match('/^[0-9]+$/u', $var)){
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
            case "DATA":
                if(preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $var)){
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
            case "TEXTO_ESP":
                if(preg_match('/^[A-Za-z0-9]+$/', $var)) {
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
        }    
    } 
}
?>

