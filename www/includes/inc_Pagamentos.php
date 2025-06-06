<?php require_once __DIR__ . '/constantes_url.php'; ?>
<?php
$a_formas_pagamento = array(
	'VISA' => 'Cartão de Crédito VISA', 
	'MasterCard' => 'Cartão de Crédito MasterCard', 
	'Débito em conta Bradesco' => 'Débito em conta Bradesco', 
	'Débito em conta Banco de Brasil' => 'Débito em conta Banco de Brasil', 
	'Débito em conta Banco Real' => 'Débito em conta Banco Real', 
	'Débito em conta Itaú' => 'Débito em conta Itaú', 
	'Débito em conta Unibanco' => 'Débito em conta Unibanco', 
	'Boleto bancário' => 'Boleto bancário' 
); 

// Para manter os arquivos de retorno do bradesco e ler, no máximo, uma vez a cada minuto 
$RETORNO_BRADESCO = array(
		'buffer_facil' => '',
		'data_facil' => '',
		'data_transf' => '',
		'data_transf' => ''
);

// Usar esta função
function getArquivoRetorno($stipo) {
	global $link_ArquivoRetornoPagtoFacil, $link_ArquivoRetornoPagtoFacil_POST, $link_ArquivoRetornoTransf, $link_ArquivoRetornoTransf_POST;
	global $RETORNO_BRADESCO;
	global $link_BBDebito_Sonda, $link_BBDebito_SondaPOST;

	$buffer = "";

	if($stipo=="Transf") {
		$link_ArquivoRetorno = $link_ArquivoRetornoTransf;
		$link_ArquivoRetorno_POST = $link_ArquivoRetornoTransf_POST;	
	} else if($stipo=="PagtoFacil") {
		$link_ArquivoRetorno = $link_ArquivoRetornoPagtoFacil;
		$link_ArquivoRetorno_POST = $link_ArquivoRetornoPagtoFacil_POST;	
	} else if($stipo=="BancodoBrasil") {
		if(!$link_BBDebito_Sonda) {
                        $link_BBDebito_Sonda = "https://mpag.bb.com.br/site/mpag/REC3.jsp";
		}
		$link_ArquivoRetorno = $link_BBDebito_Sonda;
		$link_ArquivoRetorno_POST = $link_BBDebito_SondaPOST;
	}

/*
HTTP/1.1 200 OK
Date: Tue, 01 Sep 2009 20:29:10 GMT
Server: Microsoft-IIS/6.0
X-Powered-By: ASP.NET
Content-Length: 365
Content-Type: text/plain
Expires: Tue, 01 Sep 2009 20:29:10 GMT
Set-Cookie: ASPSESSIONIDAQBRQDCT=HDHHEEABDDGIEBMJEOHCFCPO; path=/
Cache-control: private

#     2009090215334077398681#0000001000#02/09/2009#15:33:40#003#000000#
#     2009090213532675625610#0000001000#02/09/2009#13:53:26#003#000000#
#     2009090210195561242675#0000001000#02/09/2009#10:19:55#003#000000#
#     2009090210032050717163#0000001000#02/09/2009#10:03:20#003#000000#
#     2009090200282898184204#0000001000#02/09/2009#00:28:28#003#000000#
#     2009082521374170816040#0000001000#25/08/2009#21:38:30#003#000000#
#     2009082522143847460937#0000010000#25/08/2009#22:16:02#003#000000#
#     2009082707441064788818#0000005000#27/08/2009#07:44:24#003#000000#
#     2009090112231370266723#0000001300#01/09/2009#12:23:23#003#000000#
#     2009090117044715557861#0000010000#01/09/2009#17:05:04#003#000000#
*/

	// http://blog.unitedheroes.net/curl/
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL,$link_ArquivoRetorno);

	// Some sites may protect themselves from remote logins by checking which site you came from.
	// http://php.net/manual/en/function.curl-setopt.php
	$ref_url = "" . EPREPAG_URL_HTTP . "";
	curl_setopt($curl_handle, CURLOPT_REFERER, $ref_url);

	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);	// não verifica certificado
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);	// então, também não verifica nome no certificado

	curl_setopt($curl_handle, CURLOPT_HEADER, 1); 
	curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"); 

	curl_setopt($curl_handle, CURLOPT_POST, 1);
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $link_ArquivoRetorno_POST);

	// The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 5);		// default: 20s, quando os bancos estão lentos ou fora do ar a conciliação fica muito lenta
	// The maximum number of seconds to allow cURL functions to execute.
	curl_setopt($curl_handle, CURLOPT_TIMEOUT, 10);		// default: 20s, quando os bancos estão lentos ou fora do ar a conciliação fica muito lenta
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);

	$buffer = curl_exec($curl_handle);

	curl_close($curl_handle);


        if($stipo=="BancodoBrasil") {
            gravaLog_TMP_conciliacao_pag("Em getArquivoRetorno - BBR9 :    link_ArquivoRetorno: '".$link_ArquivoRetorno."'".PHP_EOL."   link_ArquivoRetorno_POST: '".$link_ArquivoRetorno_POST."'".PHP_EOL."  Buffer: [".print_r($buffer, true)."]".PHP_EOL);
        }

	return $buffer;
}

// Pega o string de retorno do Bradesco (para todos os registros) e retorna o status para uma transação em específico, caso exista neste arquivo
/* Exemplos 

	HTTP/1.1 200 OK
	Date: Tue, 01 Sep 2009 20:29:10 GMT
	Server: Microsoft-IIS/6.0
	X-Powered-By: ASP.NET
	Content-Length: 365
	Content-Type: text/plain
	Expires: Tue, 01 Sep 2009 20:29:10 GMT
	Set-Cookie: ASPSESSIONIDAQBRQDCT=HDHHEEABDDGIEBMJEOHCFCPO; path=/
	Cache-control: private

	#     2009082521374170816040#0000001000#25/08/2009#21:38:30#003#000000#
	#     2009082522143847460937#0000010000#25/08/2009#22:16:02#003#000000#
	#     2009082707441064788818#0000005000#27/08/2009#07:44:24#003#000000#
	#     2009090112231370266723#0000001300#01/09/2009#12:23:23#003#000000#
	#     2009090117044715557861#0000010000#01/09/2009#17:05:04#003#000000#


	HTTP/1.1 200 OK
	Date: Tue, 01 Sep 2009 20:29:10 GMT
	Server: Microsoft-IIS/6.0
	X-Powered-By: ASP.NET
	Content-Length: 71
	Content-Type: text/plain
	Expires: Tue, 01 Sep 2009 20:29:10 GMT
	Set-Cookie: ASPSESSIONIDAQBRQDCT=EDHHEEABFHGEEHKBCHJJEHLK; path=/
	Cache-control: private

	#     2009082722045703332519#0000001120#27/08/09#22:05:21#081#000000#

*/
function processReturnFromBradesco($stipo, $sid, $buffer, &$aline) {
	$aretorno = explode(PHP_EOL, $buffer);
	for($i=10;$i<count($aretorno);$i++) {

		// processa cada linha de retorno e poe em $aline
		$aline = explode("#", $aretorno[$i]);

		$sid_this = ((isset($aline[1]))?$aline[1]:"");
		if(strlen($sid)>0) {
			if($sid==$sid_this) {
				return $aline[5];
			}
		}
	}
	return "";
}

function processReturnFromBradescoNovaIntegracao($sid, $buffer) {
    
    if (is_array($buffer) && $buffer['status']['codigo'] == 0) {
        if($buffer['pedidos']['pedido']['@attributes']['numero'] == $sid) {
            return '0'.$buffer['pedidos']['pedido']['status'];
        }
        else return '';
    }
    else return '';
} //end function processReturnFromBradescoNovaIntegracao()

function getArquivoRetornoBradescoNovaIntegracao($orderID) {
        global $raiz_do_projeto;

        $file_token = $raiz_do_projeto.'banco/bradesco/token/token_bradesco.php';
        ini_set('display_errors', 0);
        if(! file_exists($file_token)){

            geraArquivo($file_token);

        }//end if(! file_exists($file_token))
        else {
            require_once ($file_token);
        }

            $url_acesso = URL_ACESSO_BRADESCO. "SPSConsulta/GetOrderById/".BRADESCO_MERCHANTID."?token=".$GLOBALS['token_brd']."&orderId=".$orderID;
            $consulta = consulta_pedidos($url_acesso);
            $consulta = xml_to_array_ignore_header($consulta);
        
        if(in_array($consulta['status']['codigo'], $GLOBALS['ARRAY_ERROR_TO_NEW_TOKEN'])){
            geraArquivo($file_token);
        }
        return $consulta;
}

function geraArquivo($file_name){
    ini_set('display_errors', 0);
    $url_acesso = URL_ACESSO_BRADESCO."SPSConsulta/Authentication/".BRADESCO_MERCHANTID;
    $autentica = consulta_pedidos($url_acesso);
    $array_autentica = xml_to_array_ignore_header($autentica);
    
    if($array_autentica['status']['codigo'] == '0'){
        $token = $array_autentica['token']['token'];
        $data_criacao_token = $array_autentica['token']['dataCriacao'];
        $conteudoArquivo =
'<?php
//'.$data_criacao_token.'
$GLOBALS["token_brd"] = "'.$token.'";
?>';
        $GLOBALS["token_brd"] = $token;
        
        $newfile = fopen($file_name, 'w');
        if($newfile !== false){
            if(fwrite($newfile, $conteudoArquivo)){
                fclose($newfile);
            }
        } 
    }   
}

function xml2array ( $xmlObject, $out = array () ) {
    foreach ( (array)$xmlObject as $index => $node ) {
        $out[$index] = (is_object($node)) ? xml2array($node) : $node;
    }

    return $out;
} //end function xml2array

function xml_to_array_ignore_header($resultado){
    try{
            $retorno = explode(PHP_EOL, $resultado);
            
            foreach ($retorno as $i => $content){
                if(trim($content) == ""){
                    $indice_inicio_xml = $i+1;
                }
            }
        
            $xml_element = new SimpleXMLElement($retorno[$indice_inicio_xml]);
            $array = xml2array($xml_element);
            
            return $array;
            
    } catch(Exception $e) {
        return NULL;
    }
}

function consulta_pedidos($url_acesso){
    
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL,$url_acesso);

    //string base para envio no Authorization do HEADER
    $stringBase = EMAIL_AUTENTICACAO_BRADESCO.":".BRADESCO_CHAVE_SEGURANCA;

    // http://www.php.net/manual/en/function.curl-setopt.php
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);	// não verifica certificado
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);	// então, também não verifica nome no certificado

    curl_setopt($curl_handle, CURLOPT_HEADER, 1); 
    curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"); 
    
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
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);

    // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 5);		// default: 20s, quando os bancos estão lentos ou fora do ar a conciliação fica muito lenta
    // The maximum number of seconds to allow cURL functions to execute.
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, 10);		// default: 20s, quando os bancos estão lentos ou fora do ar a conciliação fica muito lenta
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);

    $buffer = curl_exec($curl_handle);

    $info = curl_getinfo($curl_handle);

    curl_close($curl_handle);

    return $buffer;
}//end function consulta_pedidos($url_acesso)


// processa linha e poe em $aline
// exemplo: 	'#     2009082722045703332519#0000001120#27/08/09#22:05:21#081#000000#'
function processStringRetornoBradesco($stipo, $sstring, &$aline) {
	$sret = "";
	$aline = explode("#", $sstring);
	for($j=0;$j<count($aline);$j++) {
		if(strlen($aline[$j])) {
			$sret .= "&nbsp;&nbsp;".$j.": <span style='background-color:#FFFF99'>".$aline[$j]."</span>";
			if($j==5) {
				$sret .= " - ".processReturnCodeBradesco($stipo, $aline[$j]);
			}
			$sret .= "<br>";
		}
	}
	return $sret;
}


function processReturnCodeBradesco($stipo, $scode) {
	$sret = "???";
	switch($stipo) {
		case "PagtoFacil":
			switch($scode) {
				case "000":
					$sret = "Não Autorizado";
					break;
				case "001":
					$sret = "Autorizado - não Capturar";
					break;
				case "002":
					$sret = "Autorizado - à Capturar";
					break;
				case "003":
					$sret = "Autorizado - Capturado";
					break;
				default:
					$sret = "??????T";
					break;
			}
			break;
		case "Transf":
			switch($scode) {
				case "000":
					$sret = "Não Autorizado";
					break;
				case "080":
					$sret = "Transferência Pendente";
					break;
				case "081":
					$sret = "Transferência Sucesso";
					break;
				default:
					$sret = "??????T";
					break;
			}
			break;
		default:
			$sret = "??????";
			break;
	}
	return $sret;
}

/*
	Exemplo
HTTP/1.1 200 OK
Server: Sun-Java-System-Application-Server/7 2004Q2UR5
Date: Tue, 08 Sep 2009 21:25:02 GMT
Content-length: 88
Content-type: text/html;charset=ISO-8859-1
Set-cookie: JSESSIONID=9b8b971177cbd92b4f057ab3ff131;Path=/site/mpag;Secure







000000000011111111112222222222333333333344444444445555555555666666
012345678901234567890123456789012345678901234567890123456789012345
200909081733448840000000000000003062330010000000000000000000000000

000000000011111111112222222222333333333344444444445555555555666666
012345678901234567890123456789012345678901234567890123456789012345
aaaaaaaaaaaaaaaaa000000000000000bbbbbbcddeeeeeeeeqqqqqqqqqqqqqqq

Onde é a Variável
aaaaaaaaaaaaaaaaa refTran
000000000000000 Valor
bbbbbb idConv
c tpPagamento
dd Situação
eeeeeeee dataPagamento
qqqqqqqqqqqqqqq qtdPontos (exclusivo para o Programa de Relacionamento de Pontos)

*/
function processReturnFromBancodoBrasil($stipo, $sid, $buffer, &$aresult) {
	$aretorno = explode(PHP_EOL, $buffer);
	for($i=0;$i<count($aretorno);$i++) {
		if(strlen(trim($aretorno[$i]))>0 && substr($aretorno[$i],0,17) == $sid) {
			$aresult = "";
			// processa cada linha de retorno e poe em $aline
			$aresult['refTran']			= substr($aretorno[$i],0,17);
			$aresult['Valor']			= substr($aretorno[$i],17,15);
			$aresult['idConv']			= substr($aretorno[$i],32,6);
			$aresult['tpPagamento']		= substr($aretorno[$i],38,1);
			$aresult['Situação']		= substr($aretorno[$i],39,2);
			$aresult['dataPagamento']	= substr($aretorno[$i],41,8);
			$aresult['qtdPontos']		= substr($aretorno[$i],49,15);
			$sid_this = $aresult['refTran'];
			if(strlen($sid)>0) {
				if($sid==$sid_this) {
					return $aresult['Situação'];	//$aretorno[$i];
				}
			}
		}
	}
	return "";
}

function processReturnCodeBancodoBrasil($scode) {
	$a_situacao = array(
						'00' => 'pagamento efetuado', 
						'01' => 'pagamento não autorizado', 
						'02' => 'erro no processamento da consulta', 
						'03' => 'pagamento não localizado', 
						'10' => 'campo "idConv" inválido ou nulo', 
						'11' => 'valor informado é inválido, nulo ou não confere com o valor registrado', 
						'99' => 'Operação cancelada pelo cliente'
						);
	if(array_key_exists($scode, $a_situacao)) {
		$sret = $a_situacao[$scode];
	} else {
		$sret = "??";
	}
	return $sret;
}


// Retorna status da transação obtido do Banco (Bradesco)
//		a consulta ao banco é feita buffered (no máximo cada $SONDA_BRADESCO_5_DELAY/$SONDA_BRADESCO_6_DELAY segundos)
//									chamando getArquivoRetorno($link, $post) (apenas aqui)
//		getStatusTransacao() é chamada apenas em getTransacaoPagamentoOK($stipo, $sid, &$aline) 
//									que é chamada em lista_pagamentos.php e em function_vendaGamers.php
// Testar em bkov2_prepag/pagamento/lista_pagamentos.php
// Também utilizado em conciliacaoAutomaticaPagamentoOnline() (bkov2_prepag/pagamento/functions_vendaGames.php)
function getStatusTransacao($stipo, $sid, &$aline) {
	global $link_ArquivoRetornoPagtoFacil, $link_ArquivoRetornoPagtoFacil_POST, $link_ArquivoRetornoTransf, $link_ArquivoRetornoTransf_POST;
	global $link_BBDebito_SondaPOST;
	global $SONDA_BRADESCO_5_DELAY, $SONDA_BRADESCO_6_DELAY, $SONDA_BANCODOBRASIL_9_DELAY;
	global $connid;
        global $raiz_do_projeto;

        gravaLog_TMP_conciliacao_pag("Em getStatusTransacao - sid: '".$sid."'".PHP_EOL);

        $bUpdate = false;
	$buffer = "";
	$msg = "";

	$pc_data_sonda_brd_5 = $pc_data_sonda_brd_6 = $pc_data_sonda_bbr_9 = $pc_data_sonda_bbr_Z = $pc_data_sonda_bbr_E = date("Y-m-d H:i:s");

	// Obtem retorno armazenado no BD
	$sql_0 = "select * from pag_config where pc_id=1"; 
	$rs_config = pg_exec($connid,$sql_0); 

	if(!$rs_config || pg_num_rows($rs_config) == 0) {
		$msg = "Nenhuma configuração encontrada.".PHP_EOL;
	} else {
		$rs_config_row = pg_fetch_array($rs_config);
		if($stipo=="Transf") {
			$pc_data_sonda_brd_5 = $rs_config_row['pc_data_sonda_brd_5'];
			$pc_sonda_brd_5 = $rs_config_row['pc_sonda_brd_5'];
		} else if($stipo=="PagtoFacil") {
			$pc_data_sonda_brd_6 = $rs_config_row['pc_data_sonda_brd_6'];
			$pc_sonda_brd_6 = $rs_config_row['pc_sonda_brd_6'];
		} else if($stipo=="BancodoBrasil") {
			$pc_data_sonda_bbr_9 = $rs_config_row['pc_data_sonda_bbr_9'];
			$pc_sonda_bbr_9 = $rs_config_row['pc_sonda_bbr_9'];
		} else if($stipo=="PINsEPP") {
			$pc_data_sonda_bbr_E = "";
			$pc_sonda_bbr_E = "";
			$pc_sonda_bbr_E = getSondaPINsEPP($sid, $pc_data_sonda_bbr_E);
		} else if($stipo=="BancoEPP") {
			$pc_data_sonda_bbr_Z = date("Y-m-d H:i:s");
			$pc_sonda_bbr_Z = "";
		}

		$time1 = date("Y-m-d H:i:s");
		$time_diff_5 = strtotime($time1) - strtotime($pc_data_sonda_brd_5);
		$time_diff_6 = strtotime($time1) - strtotime($pc_data_sonda_brd_6);
		$time_diff_9 = strtotime($time1) - strtotime($pc_data_sonda_bbr_9);
		$time_diff_Z = strtotime($time1) - strtotime($pc_data_sonda_bbr_Z);
		$time_diff_E = strtotime($time1) - strtotime($pc_data_sonda_bbr_E);

		$buffer = "";
		$s_sonda_brd = "";																									// Debug (para permitir ACIONA)
		// Atualiza Sonda 
		if($stipo=="Transf") {
                        $buffer = getArquivoRetornoBradescoNovaIntegracao($sid);
                        $bUpdate = true;
		}
		if($stipo=="PagtoFacil") {
			if($time_diff_6>$SONDA_BRADESCO_6_DELAY) {
				$buffer = getArquivoRetorno($stipo);
				$bUpdate = true;
			} else {
				$buffer = $pc_sonda_brd_6;
			}
		}
		if($stipo=="BancodoBrasil") {
			// No Banco do Brasil consulta sempre que precissar
			if (true) {	// $time_diff_9>$SONDA_BANCODOBRASIL_9_DELAY) {
				// Insere $sid no string de POST
				require_once $raiz_do_projeto.'banco/bancodobrasil/inc_urls_bancodobrasil.php';
				$link_BBDebito_SondaPOST = adjust_BBDebito_SondaPOST($sid, 0);

				if(!$link_BBDebito_Sonda) {
					$link_BBDebito_Sonda = "https://mpag.bb.com.br/site/mpag/REC3.jsp";
				}

				$buffer = getArquivoRetorno($stipo);

				$bUpdate = false;
			} else {
				$buffer = $pc_sonda_bbr_9;
			}
		}
		if($stipo=="BancoItau") {
			// No Banco Itaú consulta sempre que precissar
			if (true) {	
				$buffer = getSondaItau($sid, $a_retornoitau, $sitPagitau, $dtPagitau);	//getArquivoRetorno($stipo);
				$bUpdate = false;
			} else {
				$buffer = "??";
			}
		}
		if($stipo=="PINsEPP") {
			if (true) {	
				$buffer = $pc_sonda_bbr_E;
				$bUpdate = false;
			} else {
				$buffer = "??";
			}
		}
		if($stipo=="BancoEPP") {
			// No BancoEPP a Sonda sempre está disponível
			if (true) {	
				// Obtem status do pagamento no BD
				$sql_p = "select status from tb_pag_compras where tipo_cliente='M' and iforma='Z' and numcompra='".$sid."'"; 
				$rs_sonda = pg_exec($connid,$sql_p); 

				if(!$rs_sonda || pg_num_rows($rs_sonda) == 0) {
					$msg = "Nenhum pagamento encontrado para Banco E-Prepag (numorder = '$sid')".PHP_EOL;
				} else {
					$rs_sonda_row = pg_fetch_array($rs_sonda);
					$buffer = $rs_sonda_row['status'];
				}

				$bUpdate = false;
			}
		}
		if($stipo=="Cielo") {

			// Rotina chamada em /tarefas/pagamento_online.php
			$filename = $GLOBALS['raiz_do_projeto']."banco/cielo/include.php";
			include_once($filename);

			// No Banco Cielo consulta sempre que precissar
			$buffer = getSondaCielo($sid, $aline);
			$bUpdate = false;
		}

		if($stipo==$GLOBALS['PAGAMENTO_PIX_NOME_BANCO']) {
                        require_once RAIZ_DO_PROJETO.'banco/pix/mercadopago/config.inc.pix.php'; 
                        $buffer = getSondaPIX($sid, $aline);
                        $bUpdate = false;
		}
	}

	// Obteve arquivo de retorno? -> salva no BD
	if($bUpdate) {
		$buffer_safe = str_replace("'", "''", $buffer);
		if($stipo=="Transf") {
			$pc_sonda_brd_5 = $buffer;
                        $buffer_safe = json_encode($buffer);
			$sql  = "update pag_config set pc_data_sonda_brd_5='".$time1."', pc_sonda_brd_5='".$buffer_safe."' where pc_id=1;"; 
		} else if($stipo=="PagtoFacil") {
			$pc_sonda_brd_6 = $buffer;
			$sql  = "update pag_config set pc_data_sonda_brd_6='".$time1."', pc_sonda_brd_6='".$buffer_safe."' where pc_id=1;"; 
		} else if($stipo=="BancodoBrasil") {
			$pc_sonda_bbr_9 = $buffer;
			$sql  = "update pag_config set pc_data_sonda_bbr_9='".$time1."', pc_sonda_bbr_9='".$buffer_safe."' where pc_id=1;"; 
		} else if($stipo=="BancoItau") {
			// Não deve chegar aqui, Banco Itau não usa buffer de sonda
		} else if($stipo=="BancoEPP") {
			$pc_sonda_bbr_Z = $buffer;
			$sql  = "";	//"update pag_config set pc_data_sonda_bbr_9='".$time1."', pc_sonda_bbr_9='".$buffer_safe."' where pc_id=1;"; 
		}

		if($sql) {
			$rs_config = SQLexecuteQuery($sql);
		}

	}

	$sret = "";
	// Leu alguma coisa? -> processa
	if($buffer) {
		$alinetmp = "";	
		if(($stipo=="Transf") || ($stipo=="PagtoFacil")) {
			$sret = processReturnFromBradescoNovaIntegracao($sid, $buffer);
                        $aline = $buffer;
		} else if($stipo=="BancodoBrasil") {
			$sret = processReturnFromBancodoBrasil($stipo, $sid, $buffer, $aline); 
		} else if($stipo=="BancoItau") {
			$sret = $buffer;
		} else if($stipo=="PINsEPP") {
			$sret = $buffer;
		} else if($stipo=="BancoEPP") {
			$sret = $buffer;
		} else if($stipo=="Cielo") {
			$sret = $buffer;
		} else if($stipo==$GLOBALS['PAGAMENTO_PIX_NOME_BANCO']) {
			$sret = $buffer;
		}
	}
	return $sret;
}

// Está usando esta
function getTransacaoPagamentoOK($stipo, $sid, &$aline) {
	$bPagamentoOK = false; 

	switch($stipo) {
		case "PagtoFacil":
			if(getStatusTransacao($stipo, $sid, $aline)=='003') {
				$bPagamentoOK = true;
				gravaLog_TMP_conciliacao_pag("Em getTransacaoPagamentoOK - Sonda de Pagto BRD6 (".$sid.").".PHP_EOL.print_r($aline, true).PHP_EOL);

			}
			break;
		case "Transf":
			if(getStatusTransacao($stipo, $sid, $aline)=='081') {
				$bPagamentoOK = true;
				gravaLog_TMP_conciliacao_pag("Em getTransacaoPagamentoOK - Sonda de Pagto BRD5 (".$sid.").".PHP_EOL.print_r($aline, true).PHP_EOL);
			}
			break;
		case "BancodoBrasil":
			$aline = "";
			$status = getStatusTransacao($stipo, $sid, $aline);
			if($status=='00') {
				$bPagamentoOK = true;
			}
			gravaLog_TMP_conciliacao_pag("Em getTransacaoPagamentoOK - Sonda de Pagto BBR9 ('".$sid."', OK?: $bPagamentoOK).".PHP_EOL.print_r($aline, true).PHP_EOL);
			break;
		case "BancoItau":
			$aline = "";
			$status = getStatusTransacao($stipo, $sid, $aline);
			if($status=='00') {
				$bPagamentoOK = true;
			}
			break;
		case "PINsEPP":
			$aline = "";
			$status = getStatusTransacao($stipo, $sid, $aline);
			if($status=='3') {
				$bPagamentoOK = true;
			}
			break;
		case "BancoEPP":
			$aline = "";
			$status = getStatusTransacao($stipo, $sid, $aline);
			if($status=='3') {
				$bPagamentoOK = true;
			}
			break;
		case "Cielo":
			$aline = "";
			$status = getStatusTransacao($stipo, $sid, $aline);
			$bPagamentoOK = $status;
			break;
                case $GLOBALS['PAGAMENTO_PIX_NOME_BANCO']:
        		$aline = "";
			$status = getStatusTransacao($stipo, $sid, $aline);
			$bPagamentoOK = $status;
			break;
	}
	return $bPagamentoOK;
}

function gravaLog_TMP_conciliacao_pag($mensagem){

	//Arquivo
	$file = $GLOBALS['raiz_do_projeto']."log/log_pagamento_TMP_conciliacao_pag.txt";

	//Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . PHP_EOL . $mensagem . PHP_EOL;

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 

}

// Substitui o refTran e o valor no POST string
function adjust_BBDebito_SondaPOST($sid, $svalor) {
	global $link_BBDebito_SondaPOST;
	global $connid;
	
	if($svalor==0) {
		$sql  = "select total from tb_pag_compras where numcompra='".$sid."'";
//		$rs_pagto = SQLexecuteQuery($sql);
		$rs_pagto = pg_exec($connid,$sql); 
		if(!$rs_pagto || pg_num_rows($rs_pagto) == 0) {
			$msg = "Não foi encontrado o pagamento '".$sid."'.".PHP_EOL;
		} else {
			$rs_pagto_row = pg_fetch_array($rs_pagto);
			$svalor	= str_pad($rs_pagto_row['total'], 15, "0", STR_PAD_LEFT);
		}
	}

	$irefTran = strpos($link_BBDebito_SondaPOST,"refTran=")+strlen("refTran=");
	$ivalorSonda = strpos($link_BBDebito_SondaPOST,"valorSonda=")+strlen("valorSonda=");

	$sret = substr($link_BBDebito_SondaPOST,0,$irefTran).$sid."&valorSonda=".$svalor.substr($link_BBDebito_SondaPOST,$ivalorSonda+15);

	return $sret;
}

function getDocPrefix($iforma){

	switch ($iforma) {
		case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
			$prefix = "BRD";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
			$prefix = "BRD";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
			$prefix = "BBR";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:		// 'A'
		case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:					// 10
			$prefix = "BIT";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:		// 'E'
		case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:					// 13
			$prefix = "EPP";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']:		// 'B'
		case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:					// 11
			$prefix = "HIP";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']:		// 'P'
		case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:					// 12
			$prefix = "PYP";
			break;
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:
			$prefix = "BEP";
			break;

		// Cielo
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']:
		case $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']:
			$prefix = "CVD";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']:
		case $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']:
			$prefix = "CVC";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']:
		case $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']:
			$prefix = "CMD";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']:
		case $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']:
			$prefix = "CMC";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']:
		case $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']:
			$prefix = "CED";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']:
		case $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']:
			$prefix = "CEC";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']:
		case $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']:
			$prefix = "CDC";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']:
		case $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']:
			$prefix = "CDC";
			break;

		case $GLOBALS['FORMAS_PAGAMENTO']['OFERTAS']:
		case $GLOBALS['PAGAMENTO_OFERTAS_NUMERIC']:
			$prefix = "OFE";
			break;

		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MCOIN']:
		case $GLOBALS['PAGAMENTO_MCOIN_NUMERIC']:
			$prefix = "MCO";
			break;

		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']:
		case $GLOBALS['PAGAMENTO_PIX_NUMERIC']:
			$prefix = $GLOBALS['PAGAMENTO_PIX_NOME_BANCO'];
			break;

		default:
			$prefix = "???";
	}
	return $prefix;
}

// Ver getBcoCodigo()
function getCodigoBanco($iforma){

	switch ($iforma) {
		case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
			$cod_banco = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
			$cod_banco = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
			$cod_banco = $GLOBALS['BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:		// 'A'
		case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:					// 10
			$cod_banco = $GLOBALS['BOLETO_MONEY_BANCO_ITAU_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:		// 'E'
		case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:					// 13
			$cod_banco = $GLOBALS['PAGAMENTO_PIN_EPP_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']:		// 'B'
		case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:					// 11
			$cod_banco = $GLOBALS['BOLETO_MONEY_HIPAY_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']:		// 'P'
		case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:					// 12
			$cod_banco = $GLOBALS['BOLETO_MONEY_PAYPAL_COD_BANCO'];
			break;
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:							// 'Z'
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:					// 199
			$cod_banco = $GLOBALS['BOLETO_MONEY_BANCO_EPP_COD_BANCO'];
			break;
		default:
			$cod_banco = "???";
	}
	return $cod_banco;
}

function b_Is_vg_pagto_tipo_EPP_Cash($vg_pagto_tipo) {
	$bret = false;
	switch ($vg_pagto_tipo) {
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:		// 'E'
		case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:					// 13
			$bret = true;
			break;
	}
	return $bret;
}
// Usar b_IsPagtoOnline() para levar em conta Cielo
function b_Is_vg_pagto_tipo_online($vg_pagto_tipo) {

	$bret = false;
	switch ($vg_pagto_tipo) {
		case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
			$bret = true;
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
			$bret = true;
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
			$bret = true;
			break;
		case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:
			$bret = true;
			break;
		case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:
			$bret = true;
			break;
		case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:
			$bret = true;
			break;
		case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:
			$bret = true;
			break;
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:
			$bret = true;
			break;
		default:
			$bret = false;
	}
	return $bret;
}

// Usar getDescricaoPagtoOnline() para mostrar Cielo
function get_Pagamento_Descricao($vg_pagto_tipo) {

	$sret = "";
	switch ($vg_pagto_tipo) {
		case $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']:
		case $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']:
//			$sret = "Money";
			$sret = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$vg_pagto_tipo];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
			$sret = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$vg_pagto_tipo];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
			$sret = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$vg_pagto_tipo];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
			$sret = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$vg_pagto_tipo];
			break;
		case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:
			$sret = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO']['A'];
			break;
		case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:
			$sret = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO']['E'];
			break;
		case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:
			$sret = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO']['B'];
			break;
		case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:
			$sret = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO']['P'];
			break;
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:
			$sret = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO_EPP'];
			break;
		default:
			$sret = "???*(".$vg_pagto_tipo.")";
	}
	return $sret;
}

function getLogoBancoSmall($iforma, $isubforma = null){

//echo "In getLogoBancoSmall(): iforma: ".$iforma."<br>";

	switch ($iforma) {
		case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
			$logostring = "<img src='/images/pagamento/logo-bradesco-small.gif' width='15' height='15' border='0' title='Bradesco - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
			$logostring = "<img src='/images/pagamento/logo-bradesco-small.gif' width='15' height='15' border='0' title='Bradesco - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
			$logostring = "<img src='/images/pagamento/B_Brasil-small.gif' width='15' height='15' border='0' title='Banco do Brasil - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:		// 'A'
		case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:					// 10
			$logostring = "<img src='/images/pagamento/itau-small.gif' width='15' height='15' border='0' title='Itaú - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:		// 'E'
		case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:					// 13
			$logostring = "
			<div style='width: 60px; height: 60px'>
				<img src='/images/pagamento/Epp_cash_loja_small.jpg' style='max-width: 100%' title='PINS EPP - ".getDocPrefix($iforma). $iforma .  (($isubforma)?(($isubforma=='G')?"_GoCash":"_??$isubforma??"):"") ."'> 
			</div>
			";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']:		// 'B'
		case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:					// 11
			$logostring = "<img src='/images/pagamento/hipay_small.gif' width='20' height='20' border='0' title='HiPay - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']:		// 'P'
		case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:					// 12
			$logostring = "<img src='/images/pagamento/paypal_small.gif' width='28' height='18' border='0' title='PayPal - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:							// 'Z'
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:					// 999
			$logostring = "<img src='/images/pagamento/logo_eppBanco.gif' width='15' height='15' border='0' title='E-Prepag - ".getDocPrefix($iforma).$iforma."'>";
			break;

		// Cielo
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']:
		case $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']:
		case $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']:
			$logostring = "<img src='/images/pagamento/logo_small_visa.gif' width='32' height='22' border='0' title='E-Prepag - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']:
		case $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']:
		case $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']:
			$logostring = "<img src='/images/pagamento/logo_small_mastercard.gif' width='32' height='22' border='0' title='E-Prepag - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']:
		case $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']:
		case $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']:
			$logostring = "<img src='/images/pagamento/logo_small_elo.gif' width='32' height='22' border='0' title='E-Prepag - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']:
		case $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']:
			$logostring = "<img src='/images/pagamento/logo_small_diners.gif' width='32' height='22' border='0' title='E-Prepag - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']:
		case $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']:
			$logostring = "<img src='/images/pagamento/logo_small_discover.gif' width='32' height='22' border='0' title='E-Prepag - ".getDocPrefix($iforma).$iforma."'>";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['OFERTAS']:
		case $GLOBALS['PAGAMENTO_OFERTAS_NUMERIC']:
			$logostring = "<img src='/images/pagamento/ico_bonus.gif' width='30' height='20' border='0' title='E-Prepag - ".getDocPrefix($iforma).$iforma."'>";
			break;

		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MCOIN']:
		case $GLOBALS['PAGAMENTO_MCOIN_NUMERIC']:
			$logostring = "<img src='/images/pagamento/ico_mcoin.gif' width='30' height='10' border='0' title='MCOIN - ".getDocPrefix($iforma).$iforma."'>";
			break;
                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']:
                case $GLOBALS['PAGAMENTO_PIX_NUMERIC']:
			$logostring = "
			<div style='width: 30px; height: 30px'>
				<img src='/images/pagamento/ico_pix.png' style='max-width: 100%' border='0' title='PIX - ".getDocPrefix($iforma).$iforma."'>
			</div>
			";
			break;
		default:
			$logostring = "??? LogoBanco iforma: ".$iforma."???";
	}
	return $logostring ;
}

// =================================================================================
function getSondaBanco($iforma, $numcompra, $id_transacao_itau, &$areturn) {
	global $cReturn, $cSpaces, $sFontRedOpen, $sFontRedClose;

	$dataconfirma = date("Y-m-d H:i:s");		// "CURRENT_TIMESTAMP";	// 
	$s_sonda = "????";

	//$valtotal = 0;
	unset($aline5);
	unset($aline6);
	unset($aline9);
	unset($alineP);
	unset($alineC);
	unset($alinePIX);
	if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'])  {
		
		// obtem status, OK se status='081'
		$b_sonda_5 = getTransacaoPagamentoOK("Transf", $numcompra, $aline5);

		// Se existe registro da transação -> salva data
                if(count($aline5) > 0){
            
			$s_sonda = (($b_sonda_5)?"OK":"none");
			$sBanco = "Bradesco";
                        $dataconfirma = "'".date('Y-m-d H:i:s')."'";
		}
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO'])  {

		// obtem status, OK se status='003'
		$b_sonda_6 = getTransacaoPagamentoOK("PagtoFacil", $numcompra, $aline6);

		// Se existe registro da transação -> salva data 	
		if(strlen($aline6[1])>0) {
			$s_sonda = (($b_sonda_6)?"OK":"none");
			$sBanco = "Bradesco";
			$dataconfirma = "'".substr($aline6[3],6,4)."-".substr($aline6[3],3,2)."-".substr($aline6[3],0,2)." ".$aline6[4]."'";
		}
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA'])  {

		// obtem status, OK se status='003'
		$b_sonda_9 = getTransacaoPagamentoOK("BancodoBrasil", $numcompra, $aline9);
		if($b_sonda_9) {
			$s_sonda = (($b_sonda_9)?"OK":"none");
			$sBanco = "Banco do Brasil";
                        echo " =====> Trecho 3 ".$aline9['dataPagamento'].PHP_EOL;
                        if(strpos($aline9['dataPagamento'], date('Y')) == 4) {
                            $dataconfirma = "'".substr($aline9['dataPagamento'], 4, 4)."-".substr($aline9['dataPagamento'], 2, 2)."-".substr($aline9['dataPagamento'], 0, 2)."'";
                        } //end if(strpos($aline9['dataPagamento'], date('Y')) == 4)
                        else {
                            $dataconfirma = "'".substr($aline9['dataPagamento'], 0, 4)."-".substr($aline9['dataPagamento'], 4, 2)."-".substr($aline9['dataPagamento'], 6, 2)."'";
                        }
                        echo " =====> DEPOIS Trecho 3 ".$dataconfirma.PHP_EOL;
		}

	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE'])  {

		$pedido =  str_pad($id_transacao_itau, 8, "0", STR_PAD_LEFT);
		$pag_status = getSondaItau($pedido, $a_retorno_itau, $sitPag, $dtPag);
		$b_sonda_A = (($pag_status=="00")?true:false);
		if($b_sonda_A) {
			$s_sonda = (($b_sonda_A)?"OK":"none");
			$sBanco = "Banco Itaú";
			$dataconfirma = "'".substr($dtPag,4,4) . "-" . substr($dtPag,2,2) . "-" . substr($dtPag,0,2) . "'";
		}

	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG'])  {

		$b_sonda_E = true;	//getTransacaoPagamentoOK("PINsEPP", $numcompra, $alineZ);
		if($b_sonda_E) {
			$s_sonda = (($b_sonda_E)?"OK":"none");
			$sBanco = "PINs E-Prepag";
			$dataconfirma = date("Y-m-D H:i:s");
		}
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE'])  {

		// Não temos Sonda para Hipay
		$b_sonda_B = false;
		if($b_sonda_B) {
			$s_sonda = (($b_sonda_B)?"OK":"none");
			$sBanco = "Banco Hipay";
			$dataconfirma = date("Y-m-D H:i:s");
		}
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE'])  {

		$b_sonda_P = getTransacaoPagamentoOK("Paypal", $numcompra, $alineP);
		if($b_sonda_P) {
			$s_sonda = (($b_sonda_P)?"OK":"none");
			$sBanco = "PayPal";
			$dataconfirma = date("Y-m-D H:i:s");
		}

	} else if($iforma==$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE'])  {

		$b_sonda_Z = getTransacaoPagamentoOK("BancodoEPP", $numcompra, $alineZ);
		if($b_sonda_Z) {
			$s_sonda = (($b_sonda_Z)?"OK":"none");
			$sBanco = "Banco E-Prepag";
			$dataconfirma = date("Y-m-D H:i:s");
		}
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX'])  {
                $b_sonda_PIX = getTransacaoPagamentoOK($GLOBALS['PAGAMENTO_PIX_NOME_BANCO'], $numcompra, $alinePIX);
                $s_sonda = (($b_sonda_PIX)?"OK":"none");
		if($b_sonda_PIX) {
			$sBanco = $GLOBALS['PAGAMENTO_PIX_NOME_BANCO'];
                        //pegar a data do JSON
			$dataconfirma = "'".substr(str_replace('T', ' ', $alinePIX),0,19)."'";
		}
	} elseif(b_IsPagtoCielo($iforma)) {
		$b_sonda_C = getTransacaoPagamentoOK("Cielo", $numcompra, $alineC);
		if($b_sonda_C) {
			$s_sonda = (($b_sonda_C)?"OK":"none");
			$sBanco = "Banco Cielo";
			$dataconfirma = date("Y-m-D H:i:s");
		}
	}


	$dataconfirma = str_replace("/","-",$dataconfirma);

	// Procura pagamentos em aberto no site do banco (Sonda), se (status=1 & sonda) => "NO SYNC"
	$s_sync = "";
	$prefix_1 = getDocPrefix($iforma);
	$vg_pagto_tipo = $iforma;

	if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
		$s_sync = (($b_sonda_5)?"NO SYNC":"");
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
		$s_sync = (($b_sonda_6)?"NO SYNC":"");
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
		$s_sync = (($b_sonda_9)?"NO SYNC":"");
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']) {
		$s_sync = (($b_sonda_A)?"NO SYNC":"");
		// No Itau ajusta 'A' -> 10 (usa nuemrico em tb_venda_games)
		$vg_pagto_tipo = $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC'];	 
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']) {
		$s_sync = (($b_sonda_B)?"NO SYNC":"");
		// No Hipay ajusta 'B' -> 11 (usa nuemrico em tb_venda_games)
		$vg_pagto_tipo = $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC'];	 
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']) {
		$s_sync = (($b_sonda_P)?"NO SYNC":"");
		// No Itau ajusta 'P' -> 12 (usa nuemrico em tb_venda_games)
		$vg_pagto_tipo = $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC'];	 
	} else if($iforma==$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']) {
		// No Banco EPP ajusta 'Z' -> 199 (usa nuemrico em tb_venda_games)
		$s_sync = (($b_sonda_Z)?"NO SYNC":"");
		$vg_pagto_tipo = $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'];	 
	} else if($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']) {
		$s_sync = (($b_sonda_PIX)?"NO SYNC":"");
		$vg_pagto_tipo = $GLOBALS['PAGAMENTO_PIX_NUMERIC'];	 
	} else if(b_IsPagtoCielo($iforma)) {
		$s_sync = (($b_sonda_C)?"NO SYNC":"");
		$vg_pagto_tipo = getCodigoNumericoParaPagto($iforma);
	} 

	$areturn['s_sonda']		= $s_sonda;
	$areturn['sBanco']		= $sBanco;
	$areturn['dataconfirma']	= $dataconfirma;
	$areturn['prefix_1']		= $prefix_1;
	$areturn['s_sync']		= $s_sync;
	$areturn['vg_pagto_tipo']	= $vg_pagto_tipo;
	

	return 0;
}

// Esta função formata a assinatura digital da transação, que é um número de 256 caracteres.
// A formatação consiste em colocar todos esses caracteres em uma tabela, separando-os em grupos
// de 4 caracteres cada um
function formataAssinatura($entrada) {
?>
	<table border='1' cellpadding='1' cellspacing='2' bordercolor='#cccccc' style='border-collapse:collapse;'>
	<TR>
	<?php
	$pos=0;
	$tam = strlen($entrada);
	$numColuna=0;
	while ($pos<$tam-1) {
	?>
		<TD align="center"><font size="1"><?php echo substr($entrada, $pos, 4)?></font></TD>
		<?php
		$pos += 4;
		$numColuna++;
		if ($numColuna==16) {
			$numColuna = 0;
		?>
	</TR>
	<TR>
	<?php
		}
	}
	?>
	</TR>
</table>
<?php
}

// É a mesma gravaLog_TMP() de commerce/function.php e dist_commerce/function.php mas para ser usada nos retornos dos bancos
// antes da inclusão da classPrincipal.php
function gravaLog_TMP_Retorno($mensagem){

        //Arquivo
        $file = $GLOBALS['raiz_do_projeto']."log/log_pagamento_TMP.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . PHP_EOL . $mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function get_pagto_epp_part_gocash($idvenda, &$a_parts = null) {

	$bret = false;
	$a_parts = array();

	// Se não tem modelos cadastrado -> retorna vazio (caso contrário o SQL pode retornar uma lista de produtos)
	if(!$idvenda) {
		// Nada
	} else {

		$sql = "select valorpagtopin, valorpagtosaldo, valorpagtogocash, (total/100-taxas) as valortotal, taxas from tb_pag_compras ";
		$sql .= "where 1=1 ";
		$sql .= "	and tipo_cliente = 'M'";
		$sql .= "	and idvenda = $idvenda";
		$rs = SQLexecuteQuery($sql);

		if($rs && pg_num_rows($rs) != 0){
			$rs_row = pg_fetch_array($rs);
			$a_parts['valorpagtopin']		= $rs_row['valorpagtopin'];
			$a_parts['valorpagtosaldo']		= $rs_row['valorpagtosaldo'];
			$a_parts['valorpagtogocash']	= $rs_row['valorpagtogocash'];
			if($a_parts['valorpagtogocash']>0) 	$bret = true;

			$a_parts['valortotal']			= $rs_row['valortotal'];
			$a_parts['taxas']				= $rs_row['taxas'];
		}
	}
	return $bret;
}

// Em constantesPinEpp.php foi definido $DISTRIBUIDORAS[], que contem o nome de cada distrinuidora
function get_nome_distribuidora_by_codigo($codigo) {
	global $DISTRIBUIDORAS;
	$snome_distribuidora = "";
	if($codigo=="?") {
		$snome_distribuidora = "Desconhecido [$codigo]";
	} elseif($codigo=="C") {
		$snome_distribuidora = "Cartão GoCash";
	} elseif(isset($DISTRIBUIDORAS)) {
		if($codigo>0) {
			if(array_key_exists($codigo, $DISTRIBUIDORAS)) {
				$snome_distribuidora = $DISTRIBUIDORAS[$codigo]['distributor_name'];
			}
		}		
	}
	return $snome_distribuidora;
}
?>