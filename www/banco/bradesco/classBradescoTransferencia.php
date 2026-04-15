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

class classBradescoTransferencia{
    
    private $location;

	public function __construct() {
            
		$this->setLocation(BRADESCO_URL_TRASFERENCIAS);
        
    }//end function __construct()
    
    private function setLocation($location) {
		$this->location = $location;
	}//end function setLocation

	public function getLocation() {
		return $this->location;
	}//end function getLocation
    
    
    private function callService($typeOfService = '', $requestParams = array()) {
						
        // Armazena na classe os dados do serviço informado 
        $braRequestRecord = $this->getRequestObject($typeOfService, $requestParams);
        if($braRequestRecord) {
            //Preparando o vetor em XML
            $braRequestRecord = $braRequestRecord[0];
            $braRequestRecord = $this->object_to_array($braRequestRecord);
            $braRequestRecord = $this->array_to_xml($braRequestRecord, new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><'.BRADESCO_XML_REQUISICAO.'/>'));
            
            //Salvando no LOG variável antes de enviada 
            $this->logEvents("ANTES do metodo sendXML (".$this->getLocation()."):".PHP_EOL.str_replace("><", ">".PHP_EOL."<", $braRequestRecord->asXML()), BRADESCO_MSG_ERROR_LOG, 0);

            //Chamando o serviço
            $resultWS = strtr($this->sendXML($braRequestRecord->asXML()), "ÁÍÓÚÉÄÏÖÜËÀÌÒÙÈÃÕÂÎÔÛÊáíóúéäïöüëàìòùèãõâîôûêÇç", "AIOUEAIOUEAIOUEAOAIOUEaioueaioueaioueaoaioueCc");
   
            //Capturando a resposta da consulta em vetor
            $braResponseRecord = $this->getResponseObject($typeOfService, $resultWS);
                
            return $braResponseRecord;
                    
        }
        else {
            return false;
        }

    }
    
    public function montaVetorInformacoes($objUser, $valor_total, $venda_id, $descricao, $is_PDV = FALSE){
        
        //Recupera objeto usuario
        if(isset($objUser) && !is_null($objUser)){
            $usuarioId = $objUser->getId();
            
            if($is_PDV){
                $nome = $objUser->getNome();
                $razao = $objUser->getRazaoSocial();
                $fantasia = $objUser->getNomeFantasia();
                $responsavel = $objUser->getResponsavel();
                $usuarioNome = (!empty($razao)?$razao:(!empty($fantasia)?$fantasia:(!empty($nome)?$nome:$responsavel)));
                $cnpj = $objUser->getCNPJ();
                $cpf_cnpj = (empty($cnpj)) ? $objUser->getCPF() : $cnpj;
                
                $tipoEnd = $objUser->getTipoEnd();
                $logradouro = (ucfirst(strtolower($tipoEnd))." ".$objUser->getEndereco());
                $tipo_usuario = 'PDV';
            } else{
                $usuarioNome = ($objUser->getNomeCPF()) ? $objUser->getNomeCPF() : $objUser->getNome();
                $cpf_cnpj = $objUser->getCPF();
                $logradouro = $objUser->getEndereco();
                $tipo_usuario = 'GAMER';
            }
            
            $cpf_cnpj = str_replace(".", "", str_replace("-", "", str_replace("/", "", $cpf_cnpj)));
            $usuarioNome = substr($usuarioNome, 0, 40);
            $descricao = substr(trim($descricao), 0, 255);

            $requestParams = array(
                                    'pedido' => array( 
                                                        'numero'    => $venda_id,
                                                        'valor'     => number_format($valor_total, 2, "", ""),
                                                        'descricao' => $descricao
                                                      ),
                                    'comprador' => array(
                                                        'id'        => $usuarioId,
                                                        'tipo_user' => $tipo_usuario,
                                                        'nome'      => $usuarioNome, 
                                                        'documento' => $cpf_cnpj, 
                                                        'endereco'  => array(
                                                                            'cep'           => str_replace("-", "", $objUser->getCEP()), 
                                                                            'logradouro'    => substr($logradouro, 0, 70), 
                                                                            'numero'        => substr($objUser->getNumero(), 0, 10), 
                                                                            'complemento'   => substr($objUser->getComplemento(), 0 , 20) ,
                                                                            'bairro'        => substr($objUser->getBairro(), 0, 50) , 
                                                                            'cidade'        => substr($objUser->getCidade(), 0, 50) , 
                                                                            'uf'            => substr($objUser->getEstado(), 0, 2)
                                                                            )
                                                        ),
            //'token_request_confirmacao_pagamento' => '21323dsd23434ad12178DDasY'
            );
            return $requestParams;
                
        } else{
            return NULL;
        }
    }
    
    public function Req_EfetuaConsultaURL($requestParams, &$lista_resposta) {
            
		$lista_resposta = null;

        //Consulta no Bradesco
        $responseBradesco = $this->callService(BRADESCO_XML_REQUISICAO, $requestParams);
        if($responseBradesco){
            $this->logEvents("Resposta Pagamento Transferência do Bradesco [".$requestParams['pedido']['numero']."]:".PHP_EOL.print_r($responseBradesco,true).PHP_EOL, BRADESCO_MSG_ERROR_LOG, 0);

            //Salvando informações para variável por referência
            $lista_resposta = $responseBradesco;
            
            if($responseBradesco['status']['codigo'] == '0'){
                return $responseBradesco['transferencia']['url_acesso'];
            } else{
                return $responseBradesco['status'];
            }
            
        } 
        else { 
            return NULL;
        }
	}
    
    private function logEvents($msg, $tipoLog = 'ERROR_LOG') {
			
		if($tipoLog == BRADESCO_MSG_ERROR_LOG) 
			$fileLog = LOG_FILE_BRADESCO_WS_ERRORS;		
		else if($tipoLog == BRADESCO_MSG_TRANSACTION_LOG) 
			$fileLog = LOG_FILE_BRADESCO_WS_TRANSACTIONS;
		
		$log  = PHP_EOL."=================================================================================================".PHP_EOL;
		$log .= "DATA -> ".date("d/m/Y - H:i:s").PHP_EOL;
		$log .= "---------------------------------".PHP_EOL;
		$log .= htmlspecialchars_decode($msg);			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);		
	}
    
    private function array_to_xml(array $arr, SimpleXMLElement $xml){
        foreach ($arr as $k => $v) {
            if(is_array($v))
                $this->array_to_xml($v, $xml->addChild(htmlentities(utf8_decode($k))));
            else $xml->addChild($k, htmlentities(utf8_decode($v)));
            }
        return $xml;
    }//end function array_to_xml

    private function object_to_array($obj) {
        if(is_object($obj)) $obj = (array) $obj;
        if(is_array($obj)) {
            $new = array();
            foreach($obj as $key => $val) {
                $new[$key] = $this->object_to_array($val);
            }
        }
        else $new = $obj;
        return $new;       
    } //end function object_to_array

    public function xml2array ( $xmlObject, $out = array () ) {
        foreach ( (array)$xmlObject as $index => $node ) {
            $out[$index] = (is_object($node)) ? self::xml2array($node) : $node;
        }

        return $out;
    } //end function xml2array
    
    // General method request
	private function getRequestObject($typeOfService = '', $requestParams = array()) {
		
		if ($typeOfService == BRADESCO_XML_REQUISICAO) {
            $serialCheck = new XMLEstruturaBradescoTransf();
            if($serialCheck->getRequestData($requestParams)){
                $serialCheckResponseObj = $serialCheck->getRequestData($requestParams);
                return $serialCheckResponseObj;
                
            } else{
                return false;
            }
		}		
	}//end 	function getRequestObject
    
    private function getResponseObject($typeOfService = '', $soapResponseData) {			

        if ($typeOfService == BRADESCO_XML_REQUISICAO) {
            $serialCheck = new XMLEstruturaBradescoTransf();
            $serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
            return $serialCheckResponseObj;
        } //end if ($typeOfService == BRADESCO_XML_REQUISICAO)
		
	}//end function getResponseObject($typeOfService = '', $soapResponseData)
    
    private function sendXML($xml) {

        $resultado = NULL;

        $sessao_curl = curl_init();
        curl_setopt($sessao_curl, CURLOPT_URL, $this->getLocation());
        curl_setopt($sessao_curl, CURLOPT_FAILONERROR, true);

        //Setando o Agente do CURL
        curl_setopt($sessao_curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');

        //composição da String base, que é formada pelo MERCHANTID da loja, concatenada a : chave de Segurança
        $stringBase = BRADESCO_MERCHANTID . ":" . BRADESCO_CHAVE_SEGURANCA;
        $headers = array();
        if(checkIP() ) {
           //URL Homologação/testes
           $headers[] = "Host: homolog.meiosdepagamentobradesco.com.br";
        }
        else {
           //URL Producao 
           $headers[] = "Host: meiosdepagamentobradesco.com.br";
        }
        $headers[] = "Accept: application/xml";
        $headers[] = "Content-Type: application/xml; UTF-8";
        $headers[] = "Authorization: Basic ".base64_encode($stringBase);

        //Setando que a requisição se trata de um XML, cuja Authorization é do tipo Basic
       curl_setopt($sessao_curl, CURLOPT_HTTPHEADER, $headers); //Authorization: OAuth 

        //  CURLOPT_SSL_VERIFYPEER
        //  verifica a validade do certificado
        curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, 0);

        //  CURLOPPT_SSL_VERIFYHOST
        //  verifica se a identidade do servidor bate com aquela informada no certificado
        curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 0);

        //  CURLOPT_CONNECTTIMEOUT
        //  o tempo em segundos de espera para obter uma conexão
        curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, 0);

        //  CURLOPT_TIMEOUT
        //  o tempo máximo em segundos de espera para a execução da requisição (curl_exec)
        curl_setopt($sessao_curl, CURLOPT_TIMEOUT, ((BRADESCO_TIMEOUT/1000)+60));

        //  CURLOPT_RETURNTRANSFER
        //  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
        //  invés de imprimir o resultado na tela. Retorna FALSE se há problemas na requisição
        curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($sessao_curl, CURLOPT_POST, true);
        curl_setopt($sessao_curl, CURLOPT_POSTFIELDS,  mb_convert_encoding($xml,'ISO-8859-1','utf-8') );
//             curl_setopt($sessao_curl, CURLOPT_POSTFIELDS, mb_convert_encoding($xml,'utf-8','ISO-8859-1') );

        $errorFileLog = fopen(LOG_FILE_BRADESCO_WS_ERRORS, "a+");
        curl_setopt($sessao_curl, CURLOPT_VERBOSE, true);
        curl_setopt($sessao_curl, CURLOPT_STDERR, $errorFileLog);
        curl_setopt($sessao_curl, CURLOPT_HEADER, 0);

        $resultado = curl_exec($sessao_curl);

        // Em caso de erro libera aqui
        $info = curl_getinfo($sessao_curl);

        //Gerando LOG em arquivo para Debug
        $this->logEvents("FUNÇÃO sendXML no curl_exec:".$this->getLocation()."  is reachable".PHP_EOL."Resultado:".PHP_EOL.str_replace("><", ">".PHP_EOL."<",$resultado).PHP_EOL."INFO da CONSULTA: ".print_r($info,true).PHP_EOL, BRADESCO_MSG_ERROR_LOG, 0);

        curl_close($sessao_curl);

       //Setando Resposta por timeout 
       if($info['http_code'] == 408 || $info['http_code'] == 503){
           $resultado = "<response><status><codigo>99</codigo><mensagem>Request Timeout</mensagem></status></response>";
       }
       return utf8_decode($resultado);

     } //end function sendXML
   
}
?>
