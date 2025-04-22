<?php
//Alterando o limeout do PHP para (PIX_TIMEOUT/1000) segundos
ini_set('default_socket_timeout', ((PIX_TIMEOUT/1000)+5));

class classPIX {
		
        private $access_token;
        
	public function __construct() {
            
            $retorno = $this->sendJSONAuthentication();
            $retorno = json_decode($retorno);
            if($retorno->access_token == PIX_ERRO) {
                echo ("<br><br>ERRO na Comunicação com o Banco!<br>Por favor, entre em contado com o suporte da E-Prepag e informe o erro de código PIX790954.<br>Obrigado.");
            }
            else {
                $this->setAccessToken($retorno->access_token);
            }
            
        }//end function __construct()

	private function setAccessToken($access_token) {
		$this->access_token = $access_token;
	}//end function setAccessToken

	public function getAccessToken() {
		return $this->access_token;
	}//end function getAccessToken

	public function callService($params) {
						
            $resposta = $this->sendJSON($params);
            $resposta = json_decode($resposta);
            if($resposta->codigo == PIX_ERRO) {
                echo ("<br><br>ERRO na Comunicação com o Banco!<br>Por favor, entre em contado com o suporte da E-Prepag e informe o erro de código PIX985235.<br>Obrigado.");
            }
            else {
                $GLOBALS["_SESSION"]["QRCODE"] = $resposta->textoImagemQRcode;
                $html ="
                        <div class='col-md-7 text-center d-min-md-none hide-pix-success' style='color: black;'>
                            <button id='btn-copy' title='Copiar código' data-clipboard-text='".$GLOBALS["_SESSION"]["QRCODE"]."' class='top20 btn btn-success'>Copiar código</button>
                        </div>
                        <div class='col-md-7 text-left d-max-sm-none hide-pix-success'>
                            <img src='/includes/qrcode/php/qrcode.php'/>
                        </div>
                        <script src='/js/clipboard.min.js'></script>
                        <script>
                            $(document).ready(function(){
                                var clipboard = new ClipboardJS('#btn-copy');
                                clipboard.on('success', function(e){
                                    $('#btn-copy').attr('title', 'Código copiado');
                                    $('#btn-copy').tooltip('show');
                                });
                            });
                        </script>";
                return $html;
            }
    
	} //end function callService
		
	public function callSonda($params, &$reposta_consulta) {
						
            $resposta = $this->sendJSON($params);
// TESTE EXEMPLO PARA IMPLEMENTAR CONCILIAÇÂO
/*            $resposta = '{
    "calendario": {
        "criacao": "2021-03-18T09:37:42.75-03:00",
        "expiracao": 200000
    },
    "status": "CONCLUIDA",
    "txid": "e6FkvgijjRxvhWZs7mWMxeW0AGwi3yAW61e",
    "revisao": 0,
    "location": "qrcodepix.bb.com.br/pix/v2/28851a0c-c571-4449-bdf5-ce57e28654b6",
    "valor": {
        "original": "1.0"
    },
    "chave": "33873062000167",
    "solicitacaoPagador": "Solicitacao Pix",
    "infoAdicionais": [
        {
            "nome": "Daniel",
            "valor": "1"
        }
    ],
    "pix": [
        {
            "endToEndId": "E60701190202103181239DY515T7OH77",
            "txid": "e6FkvgijjRxvhWZs7mWMxeW0AGwi3yAW61e",
            "valor": "1.0",
            "horario": "2021-03-18T09:40:34.00-03:00",
            "pagador": {
                "cpf": "41250888883",
                "nome": "PAULO HENRIQUE FERREIRA SOARES"
            },
            "infoPagador": ""
        }
    ]
}
';*/
            $resposta = json_decode($resposta);
            if($resposta->codigo == PIX_ERRO) {
                echo ("<br><br>ERRO na Comunicação com o Banco!<br>Por favor, entre em contado com o suporte da E-Prepag e informe o erro de código PIX985235.<br>Obrigado.");
            }
            else {
                $reposta_consulta = $resposta;
                if($resposta->status == PIX_SONDA_PAGO_OK) {
                    $sql = "SELECT * FROM tb_pag_pix WHERE numcompra = '".substr($params['idpedido'],9,17)."' AND cpf_cnpj_pagador = '".(isset($resposta->pix[0]->pagador->cpf)?$resposta->pix[0]->pagador->cpf:$resposta->pix[0]->pagador->cnpj)."'; ";
                    $rs_teste_existencia = SQLexecuteQuery($sql);
                    if(pg_num_rows($rs_teste_existencia) == 0) {
                        $sql = "INSERT INTO tb_pag_pix( 
                                                numcompra, 
                                                cpf_cnpj_pagador, 
                                                nome_pagador, 
                                                json_resposta)
                                    VALUES (
                                            '".substr($params['idpedido'],9,17)."', 
                                            '".(isset($resposta->pix[0]->pagador->cpf)?$resposta->pix[0]->pagador->cpf:$resposta->pix[0]->pagador->cnpj)."',
                                            '".$resposta->pix[0]->pagador->nome."',
                                            '". json_encode($resposta)."');";
                        $rs = SQLexecuteQuery($sql);
                        if($rs) $this->logEvents("Sucesso no INSERT: ".PHP_EOL.$sql.PHP_EOL);
                        else $this->logEvents("ERRO no INSERT: ".PHP_EOL.$sql.PHP_EOL);
                    }//end if(pg_num_rows($rs_teste_existencia) == 0)
                    else $this->logEvents("Já existe registro de dados do pagador para o pagamento ".substr($params['idpedido'],9,17).PHP_EOL);
                }//end if($resposta->status == PIX_SONDA_PAGO_OK)
                return $resposta->status;
            } //end else if($resposta->codigo == PIX_ERRO)
    
	} //end function callSonda
		
	// General methods request
	private function getRequestObject($typeOfService = '', $requestParams = array()) {	
		
		if ($typeOfService == PIX_JSON_REQUISICAO) {
                        $serialCheck = new JSONEstruturaPIX();
                        $serialCheckResponseObj = $serialCheck->getRequestData($requestParams);
                        return $serialCheckResponseObj;
		}//end if ($typeOfService == PIX_JSON_REQUISICAO) 
		
	}//end 	function getRequestObject

	// General method Response
	private function getResponseObject($typeOfService = '', $soapResponseData) {			

                if ($typeOfService == PIX_JSON_REQUISICAO) {
                        $serialCheck = new JSONEstruturaPIX();
                        $serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
                        return $serialCheckResponseObj;
                } //end if ($typeOfService == PIX_JSON_REQUISICAO)
		
	}//end function getResponseObject

	private function logEvents($msg) {
			
		$fileLog = PIX_ERROR_LOG_FILE;
		
		$log  = "=================================================================================================".PHP_EOL;
		$log .= "DATA -> ".date("d/m/Y - H:i:s").PHP_EOL;
		$log .= "---------------------------------".PHP_EOL;
		$log .= htmlspecialchars_decode($msg);			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);		
	}//end function logEvents

        private function sendJSONAuthentication() {
            
            $resultado = NULL;

            $errorFileLog = fopen(LOG_FILE_PIX_WS_ERRORS, "a+");
            $log  = "=================================================================================================".PHP_EOL;
            $log .= "DATA -> ".date("d/m/Y - H:i:s")." -> Request Token".PHP_EOL;
            $log .= "---------------------------------------------------".PHP_EOL;
            fwrite($errorFileLog, $log);
            
            $curl = curl_init(); 
            curl_setopt_array($curl, array(
              CURLOPT_URL => PIX_SERVICE_URL_AUTH,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => ((PIX_TIMEOUT/1000)+10),
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_VERBOSE => true,
              CURLOPT_STDERR => $errorFileLog,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              //CURLOPT_USERPWD => $username . ":" . $password,
              CURLOPT_POSTFIELDS => urldecode("grant_type=".GRANT_TYPE), 
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization: Basic ".base64_encode(CLIENT_ID.":".CLIENT_SECRET)
              ),
            ));

            $resultado = curl_exec($curl);
            
            // Em caso de erro libera aqui
            $info = curl_getinfo($curl);

            //Setando Resposta
            if($info['http_code'] != 200 && $info['http_code'] != 201) {
                $this->logEvents("Metodo sendJSONAuthentication no curl_exec:".PIX_SERVICE_URL_AUTH."  is reachable".PHP_EOL."Resultado: ".$resultado.PHP_EOL."INFO da CONSULTA: ".print_r($info,true).PHP_EOL);
                $resultado = '{"access_token":"'.PIX_ERRO.'"}';
            }

            curl_close($curl);

            return $resultado;
            
    }//end function sendjsonAuthentication

    private function sendJSON($params) {
        
            $resultado = NULL;

            $errorFileLog = fopen(LOG_FILE_PIX_WS_ERRORS, "a+");
            $log  = "=================================================================================================".PHP_EOL;
            $log .= "DATA -> ".date("d/m/Y - H:i:s")." -> Send JSON to Get QRCode".PHP_EOL;
            $log .= "---------------------------------------------------".PHP_EOL;
            fwrite($errorFileLog, $log);
            
            $curl = curl_init(); 

            curl_setopt_array($curl, array(
              CURLOPT_URL => PIX_SERVICE_URL_SERVICE.$params['idpedido'],
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => ((PIX_TIMEOUT/1000)+10),
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_VERBOSE => true,
              CURLOPT_STDERR => $errorFileLog,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => $params['metodo'],
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer ".$this->getAccessToken()
              ),
            ));
            if($params['metodo']==PIX_REGISTER) {
                $dados = new JSONEstruturaPIX($params);
                $dados = json_encode($dados);
                curl_setopt($curl, CURLOPT_POSTFIELDS,$dados);
            }

            $resultado = curl_exec($curl);
            
            // Em caso de erro libera aqui
            $info = curl_getinfo($curl);

            //Gravando LOG
            $this->logEvents("Metodo sendJSONAuthentication no curl_exec:".PIX_SERVICE_URL_SERVICE.$params['idpedido']."  is reachable".PHP_EOL."Dados da Consulta: ".print_r($dados,true).PHP_EOL."Resultado: ".$resultado.PHP_EOL."INFO da CONSULTA: ".print_r($info,true).PHP_EOL);

            //Setando Resposta
            if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                $resultado = '{"codigo":"'.PIX_ERRO.'"}';
            }

            curl_close($curl);

            return $resultado;           
            
    }//end function sendjson

} //end class classPIX

//função SONDA para checagem da situação do PIX
function getSondaPIX($numero,&$a_resp){
    
       $ARRAY_CONCATENA_ID_VENDA = array(
                                        'gamer'          => '10',
                                        'pdv'            => '20',
                                        'cards'          => '30',
                                        'boleto_express' => '40'
                                    );

        $sql = "SELECT * from tb_pag_compras where numcompra = '".$numero."'";
	$rs_sonda = SQLexecuteQuery($sql);
	if(!$rs_sonda) {
		 echo "<font color='#FF0000'><b>Erro na Sonda da Compra (".$numero.").".PHP_EOL."</b></font><br>";
		 return false;
	} //end if(!$rs_sonda) 
	else {
		$rs_sonda_row = pg_fetch_array($rs_sonda);
		$tipo	= $rs_sonda_row['tipo_cliente'];
		$valor	= $rs_sonda_row['total'];

                $numeroPedido = null;

                if($tipo == "LR") {
                    $numeroPedido = $ARRAY_CONCATENA_ID_VENDA['pdv'].'0000000'.$numero;
                }
                elseif($tipo == "M") {
                    $numeroPedido = $ARRAY_CONCATENA_ID_VENDA['gamer'].'0000000'.$numero;
                }
                else {
                    echo "<font color='#FF0000'><b>Não consta Tipo de Pedido na Tabela de Pagamento (".$numero.").".PHP_EOL."</b></font><br>";
                    return false;   
                }

                $consulta = new classPIX();
                $params = array (
                    'metodo'    => PIX_SONDA,
                    'idpedido'  => $numeroPedido
                );
                $auxChecagem = $consulta->callSonda($params,$a_resp);
                if($auxChecagem == PIX_SONDA_PAGO_OK) {
                    return true;
                }
                else {
                    return false;
                }
                
	}//end else do if(!$rs_sonda) 
}//end function getSondaPIX

?>