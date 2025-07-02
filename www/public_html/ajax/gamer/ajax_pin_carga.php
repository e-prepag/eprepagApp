<?php
set_time_limit(180);

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "gamer/inc_ajax.php"; 

block_direct_calling();

//	if ($_SERVER['HTTPS']=="on") { //descomentar para implementar https

//Include com conexão não persistente
require_once RAIZ_DO_PROJETO . "db/connect.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php"; 

require_once DIR_INCS . "gamer/AES.class.php"; 
require_once DIR_INCS . "gamer/chave.php"; 

require_once RAIZ_DO_PROJETO . "banco/gocash/config.inc.php"; 

require_once DIR_INCS . "gamer/inc_ajaxPinPagamento.php";

	
// Wagner
$trava	= unserialize($_SESSION['usuarioGames_ser']);
//if ($trava->b_IsLogin_Wagner() || $trava->b_IsLogin_reinaldopshotmail()) {

$numpin 	= isset($_POST["pin_number"])		? $_POST["pin_number"]		: null;
$op			= isset($_POST["op"])				? $_POST["op"]				: null;
$vercod		= isset($_POST["verificationCode"])	? strtolower($_POST["verificationCode"]): null;
$pagto		= isset($_POST["pagto"])			? $_POST["pagto"]			: null;

validaSessao(1);

// Atualiza valor saldo do BD
$saldo		= getSaldoUsuarioFunc();

$params		= array('numpin'	=> array ('0' => $numpin,
										  '1' => 'S',
										  '2' => '1'
									),
					'op'		=> array ('0' => $op,
										  '1' => 'S',
										  '2' => '1'
									),
					'vercod'	=> array ('0' => $vercod,
										  '1' => 'S',
										  '2' => '0'
									)
					);
$params		= sanitize_input_data_array($params,$err_cod);
extract($params, EXTR_OVERWRITE);
?>
<link href="/css/styles.css" rel="stylesheet" type="text/css" />
<center class="texto">
<?php

//Limpa os PINs da SESSION
limpa_session_pin($pagto);

//variavel contendo as mensagens que serão exibidas 
$msg_ajax = "";

//variavel de teste se deve ser contruido a interface
$verifica_interface = false;

//Comentado a linha abaixo por conta do GoCASH
//$numpin = substr($numpin,0,$PIN_STORE_TAMANHO);
if (strtolower($op) != 'uti') {

	//habilitando a interface
	$verifica_interface = true;

	if (strtolower($_SESSION['palavraCodigo']) == $vercod && $_SESSION['palavraCodigo']!="") {
		//verificando o numero maximo de tentativas sem sucesso em um determinado intervalo de tempo
		if(permite_tentativas($PIN_STORE_TENTATIVAS,$PIN_STORE_PERIODO,$msg_ajax)) {

			//Testando se é PIN CASH
			if(RetonaTamanhoPINEPPCASH($numpin)) {
				//Confirmando existencia e valor do PIN EPP CASH
				$aux_valida_pin = valida_pin($numpin);
				if ($aux_valida_pin >= 0 || ($numpin == "")) {
					if (($numpin != "")&&(count($_SESSION['PINEPP']) < $PAGTO_RESTR_NUM_MAX_PINS_DEFAULT_DEP)) {
						if(valida_vencimento_pin($numpin)){
							session_start();
							addContadorVezCarrinho($numpin);
							$_SESSION['PINEPP'][$numpin]=$aux_valida_pin;
						}else{
							$msg_ajax .= "Este PIN est&aacute; fora da validade (Erro: 4).<br>Por favor, verifique se a validade n&atilde;o passou de 6 meses ou entre em contato com o <a href=\'mailto:suporte@e-prepag.com.br\'>suporte@e-prepag.com.br</a>";
						}
					}
					elseif ($numpin != "") {
						$msg_ajax .=  "Quantidade m&aacute;xima de PINs a ser utilizados s&atilde;o ".$PAGTO_RESTR_NUM_MAX_PINS_DEFAULT_DEP.".<br>";
					}
				}
				else $msg_ajax .= 'Este PIN n&atilde;o foi identificado (Erro: 1).<br>Por favor, verifique se o c&oacute;digo digitado est&aacute; correto ou entre em contato com o <a href="mailto:suporte@e-prepag.com.br">suporte@e-prepag.com.br</a>\n';
			}//end if(RetonaTamanhoPINEPPCASH)

			//Testando se é PIN GoCASH
			elseif(RetonaTamanhoPINGoCASH($numpin)) {
				//Confirmando existencia e valor do PIN GoCASH
				$aux_pin_valor = StatusInquiryPIN_Value($numpin); // Wagner
//echo "A: '$numpin' -> $aux_pin_valor<br>";
				if ($aux_pin_valor > 0 || ($numpin == "")) {
					if (($numpin != "")&&(count($_SESSION['PINEPP']) < $PAGTO_RESTR_NUM_MAX_PINS_DEFAULT_DEP)) {
						session_start(); 

						$aux_pin_valor_converted = ConversionPINs::get_ValorReal('G', $aux_pin_valor);
						if($aux_pin_valor_converted>0) {
							$_SESSION['PINEPP'][$numpin]=$aux_pin_valor_converted;//Classe de conversão de valor de PINs (Real/Nominal)
							$_SESSION['PIN_NOMINAL'][$numpin]=$aux_pin_valor; 
						}
					}
					elseif ($numpin != "") {
						$msg_ajax .= "Quantidade m&aacute;xima de PINs a ser utilizados s&atilde;o ".$PAGTO_RESTR_NUM_MAX_PINS_DEFAULT_DEP.".<br>";
					}
				}
				else $msg_ajax .= 'Este PIN n&atilde;o foi identificado (Erro: 2).<br>Por favor, verifique se o c&oacute;digo digitado est&aacute; correto ou entre em contato com o <a href="mailto:suporte@e-prepag.com.br">suporte@e-prepag.com.br</a>\n';
			}//end elseif(RetonaTamanhoPINGoCASH)

			//Caso não esteja no formato EPP CASH e nem GoCASH
			elseif($numpin != "") $msg_ajax .= 'Este PIN n&atilde;o foi identificado (Erro: 3).<br>Por favor, verifique se o c&oacute;digo digitado est&aacute; correto ou entre em contato com o <a href="mailto:suporte@e-prepag.com.br">suporte@e-prepag.com.br</a>\n';
		}//end if(permite_tentativas($quantidade,$tempo))
	}//end if (strtolower($_SESSION['palavraCodigo']) == $vercod && $_SESSION['palavraCodigo']!="")
	else {
		$msg_ajax .= "O código da imagem (captcha) está incorreto.<br>Por favor, tente novamente.";
		sleep(1);
	}//end else do if (strtolower($_SESSION['palavraCodigo']) == $vercod && $_SESSION['palavraCodigo']!="")
}
else {
	//Utilizar o PIN
	if (utilizar_pin_carga()) {
?>
<script language="JavaScript" type="text/JavaScript">
	$("#link_bank").hide("slow");
	$("#pagamento_ok").show("slow");
</script>
<?php
	}
	else echo "Problemas ao utilizar o PIN.<br>Tente valida-lo novamente.<br>";
}

if($verifica_interface) {

	$aux_saldo = unserialize($_SESSION['usuarioGames_ser']);

	require_once DIR_CLASS . "classAtivacaoPinTemplate.class.php";
	
	unset($lista_pins);
	$aux_total_pins = 0;
	if (isset($_SESSION['PINEPP']) && is_array($_SESSION['PINEPP'])) {
			foreach($_SESSION['PINEPP'] as $key => $value) {
				$lista_pins[$key]['VALOR'] = $value;
				$aux_total_pins += $lista_pins[$key]['VALOR'];
				$lista_pins[$key]['BONUS'] = 0;
				$aux_total_pins += $lista_pins[$key]['BONUS'];
			}//end foreach
	}//end if (isset($_SESSION['PINEPP']) && is_array($_SESSION['PINEPP']))

	if (b_isIntegracao() && b_isIntegracao_with_nonvalidated_email() && (!b_isIntegracao_logged_in())) {
		$user_logado_aux	= false;
		$saldo_aux			= 0;
		$saldo_final_aux	= number_format((0+$aux_total_pins),2,'.','');
	}
	else {
		$user_logado_aux	= true;
		$saldo_aux			= $aux_saldo->ug_fPerfilSaldo;
		$saldo_final_aux	= number_format(($aux_saldo->ug_fPerfilSaldo+$aux_total_pins),2,'.','');
	}

	$paramList	= array(
						'jquery_core_include'	=>	false,
						'url_resources'			=>	'/ativacao_pin/',
						'usuarioLogado'			=>	$user_logado_aux,
						'saldo'					=>	$saldo_aux,
						'valor_pedido'			=>	null,
						'saldo_final'			=>	$saldo_final_aux,
						'captcha_valor'			=>	$vercod,
						'email'					=>	$_SESSION['integracao_client_email'],
						'box_carga_saldo'		=>	true,
						);
	//echo "paramList:<pre>".print_r($paramList,true)."</pre>";
	//echo "lista_pins:<pre>".print_r($lista_pins,true)."</pre>";
	
	$ativacaoPinTemplate2 = new AtivacaoPinTemplate($paramList,$lista_pins);
	echo $ativacaoPinTemplate2->boxAtivacaoPin();

}//end if($verifica_interface)

?>
</center>
<script language="JavaScript" type="text/JavaScript">
		$('#box-msg-utilizacao').html('<?php echo $msg_ajax;?>'); 
</script>
<?php
//}	//	end do teste HTTPS //descomentar para implementar https

//Wagner fim trava
//}//end if b_IsLogin_Wagner()
//else echo "Acesso não permitido!!";

//Fechando Conexão
pg_close($connid);

?>
