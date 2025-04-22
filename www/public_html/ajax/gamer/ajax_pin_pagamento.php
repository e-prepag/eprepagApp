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
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php"; 

require_once RAIZ_DO_PROJETO . "banco/gocash/config.inc.php"; 

require_once DIR_INCS . "gamer/inc_ajaxPinPagamento.php";

// Wagner
$trava	= unserialize($_SESSION['usuarioGames_ser']);

$numpin 	= isset($_POST["pin_number"])		? $_POST["pin_number"]		: null;
$op			= isset($_POST["op"])				? $_POST["op"]				: null;
$vercod		= isset($_POST["verificationCode"])	? strtolower($_POST["verificationCode"]): null;
$pagto		= isset($_POST["pagto"])			? $_POST["pagto"]			: null;
if(!isset($venda_id)) $venda_id = $_SESSION['venda'];

validaSessao(1);

if($_SERVER['HTTPS']=="on") {
	if(isset($_SESSION['usuarioGames_ser'])) {
		gravaLog_EPPCASH("HTTPS : Existe usuarioGames_ser na SESSION\n");
	}
	else {
		gravaLog_EPPCASH("HTTPS : NAUN existe usuarioGames_ser na SESSION\n");
	}
}//if($_SERVER['HTTPS']=="on") 

// Atualiza valor saldo do BD
$saldo		= getSaldoUsuarioFunc();

//Capturando o valor da compra
$var_origem_ajax_pin_pagamento = true;
include DIR_INCS . "gamer/venda_e_modelos_logica_epp.php"; 
if(empty($venda_id)) {
    if (b_isIntegracao()) {
        echo "Esta venda se encontra cancelada no momento";
    } //end if (b_isIntegracao())
    else {
?>
        <form name="pagamento" id="pagamento" method="POST" action="/game/mensagem.php">
            <input type='hidden' name='msg' id='msg' value='Esta venda se encontra cancelada no momento'>
            <input type='hidden' name='titulo' id='titulo' value='Informa Pagamento'>
            <input type='hidden' name='link' id='link' value='/game/produto/'>
        </form> 
        <script language='javascript'>
            document.getElementById("pagamento").submit();
        </script>        
<?php
    }//end else
    die();            
}
$rs_venda_row = pg_fetch_array($rs_venda);
$ultimo_status	= $rs_venda_row['vg_ultimo_status'];
if ($ultimo_status == 1) {
	include DIR_INCS . "gamer/venda_e_modelos_calculate.php"; 
	//Atribuindo o valor total da compra em REAIS
	//$valor = $total_geral;
	//Atribuindo o valor total da compra em EPP CASH
	$compra = ($total_geral_epp_cash/100+$taxas);
}
else {
	$compra = null;
}

//gravaLog_EPPCASH("Venda ID1 - Dummy : '$venda_id'; Compra: '$compra'\n");
$params		= array('numpin'	=> array ('0' => $numpin,
										  '1' => 'S',
										  '2' => '1'
									),
					'compra'	=> array ('0' => $compra,
										  '1' => 'F',
										  '2' => '1'
									),
					'venda_id'	=> array ('0' => $venda_id,
										  '1' => 'I',
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

//Limpa os PINs da SESSION
limpa_session_pin($pagto);


//variavel contendo as mensagens que serão exibidas 
$msg_ajax = "";

//variavel de teste se deve ser contruido a interface
$verifica_interface = false;

if (strtolower($_SESSION['palavraCodigo']) == $vercod && $_SESSION['palavraCodigo']!="") {
		
	//Comentado a linha abaixo por conta do GoCASH
	//$numpin = substr($numpin,0,$PIN_STORE_TAMANHO);
	if (strtolower($op) != 'uti') {

		//habilitando a interface
		$verifica_interface = true;

		//verificando o numero maximo de tentativas sem sucesso em um determinado intervalo de tempo
		if(permite_tentativas($PIN_STORE_TENTATIVAS,$PIN_STORE_PERIODO,$msg_ajax)) {

			//Testando se é PIN CASH

			$tamanho_pin = strlen($numpin);

			//Testando a quantidade de PINs Utilizado
			if (($numpin != "")&&(count($_SESSION['PINEPP'])<5)) {

				if(RetonaTamanhoPINEPPCASH($numpin)) {
					//Confirmando existencia e valor do PIN EPP CASH
					$aux_valida_pin = valida_pin($numpin);
					if ($aux_valida_pin >= 0 || ($numpin == "")) {
						session_start();
						addContadorVezCarrinho($numpin);
						$_SESSION['PINEPP'][$numpin]=$aux_valida_pin;
					}
					else $msg_ajax .= "Este PIN n&atilde;o foi identificado (Erro: 1).<br>Por favor, verifique se o c&oacute;digo digitado est&aacute; correto ou entre em contato com o <a href=\'mailto:suporte@e-prepag.com.br\'>suporte@e-prepag.com.br</a>";
				}//end if(RetonaTamanhoPINEPPCASH)

				//Testando se é PIN GoCASH
				elseif(RetonaTamanhoPINGoCASH($numpin)) {
					//Confirmando existencia e valor do PIN GoCASH
					$aux_pin_valor = StatusInquiryPIN_Value($numpin); // Wagner
//echo "A: '$numpin' -> $aux_pin_valor<br>";
					if ($aux_pin_valor > 0 || ($numpin == "")) {
						session_start(); 

						$aux_pin_valor_converted = ConversionPINs::get_ValorReal('G', $aux_pin_valor);
						if($aux_pin_valor_converted>0) {
							$_SESSION['PINEPP'][$numpin]=$aux_pin_valor_converted;//Classe de conversão de valor de PINs (Real/Nominal)
							$_SESSION['PIN_NOMINAL'][$numpin]=$aux_pin_valor; 
						}
						else {
							$msg_ajax .= "PIN válido. Conversão do valor do PIN não cadastrada";
						}
					}
					else $msg_ajax .= "Este PIN n&atilde;o foi identificado (Erro: 2).<br>Por favor, verifique se o c&oacute;digo digitado est&aacute; correto ou entre em contato com o <a href=\'mailto:suporte@e-prepag.com.br\'>suporte@e-prepag.com.br</a>";
				}//end elseif(RetonaTamanhoPINGoCASH)

				//Caso não esteja no formato EPP CASH e nem GoCASH
				elseif($numpin != "") $msg_ajax .= "Este PIN n&atilde;o foi identificado (Erro: 3).<br>Por favor, verifique se o c&oacute;digo digitado est&aacute; correto ou entre em contato com o <a href=\'mailto:suporte@e-prepag.com.br\'>suporte@e-prepag.com.br</a>";

			}//end if (($numpin != "")&&(count($_SESSION['PINEPP'])<5))
			elseif ($numpin != "") {
				$msg_ajax .= "A quantidade m&aacute;xima de PINs a serem utilizados s&atilde;o 5 (cinco).";
			}

		}//end if(permite_tentativas($quantidade,$tempo))
/*	
if ($trava->b_IsLogin_Wagner() || $trava->b_IsLogin_reinaldopshotmail()) {
	$teste_flag = flag_pin_test();
	echo "[".$teste_flag."]<br>";
	if ($teste_flag) {
		echo "PINs já sendo utilizados<br>";
	}
	else {
		echo "==================================<br>";
		sleep(30);
		flag_pin_unblock();
	}
}
*/
	} //end if (strtolower($op) != 'uti')
	else {
		
		//Utilizar o PIN
		if (utilizar_pin()) {

?>
<script language="JavaScript" type="text/JavaScript">
<?php

			//teste de transporte da variavel
			if(!is_null($_POST['dr_par_general']) && ($trava->b_IsLogin_Wagner() || $trava->b_IsLogin_reinaldopshotmail())) {
				echo "alert('".$_POST['dr_par_general']."');";
			}
			

?>
	$("#link_bank").hide("slow");
	$("#box-principal").hide("slow");
	$("#pagamento_ok").show("slow");
</script>
<?php
		} //end if (utilizar_pin())
		else $msg_ajax .= "Problemas ao utilizar o PIN.<br>Tente valida-lo novamente.<br>";
	}//end else do if (strtolower($op) != 'uti') 

}//end if (strtolower($_SESSION['palavraCodigo']) == $vercod && $_SESSION['palavraCodigo']!="")
else {
	//habilitando a interface
	$verifica_interface = true;

	$msg_ajax .= "O código da imagem (captcha) está incorreto.<br>Por favor, tente novamente.";
	sleep(1);
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
		$saldo_final_aux	= number_format((0-$compra+$aux_total_pins),2,'.','');
	}
	else {
		$user_logado_aux	= true;
		$saldo_aux			= $aux_saldo->ug_fPerfilSaldo;
		$saldo_final_aux	= number_format(($aux_saldo->ug_fPerfilSaldo-$compra+$aux_total_pins),2,'.','');
	}

	$paramList	= array(
						'jquery_core_include'	=>	false,
						'url_resources'			=>	'/ativacao_pin/',
						'usuarioLogado'			=>	$user_logado_aux,
						'saldo'					=>	$saldo_aux,
						'valor_pedido'			=>	$compra,
						'saldo_final'			=>	$saldo_final_aux,
						'captcha_valor'			=>	$vercod,
						'email'					=>	$_SESSION['integracao_client_email'],
						);
	//echo "paramList:<pre>".print_r($paramList,true)."</pre>";
	//echo "lista_pins:<pre>".print_r($lista_pins,true)."</pre>";
	
	$ativacaoPinTemplate2 = new AtivacaoPinTemplate($paramList,$lista_pins);
	echo $ativacaoPinTemplate2->boxAtivacaoPin();

}//end if($verifica_interface)
?>
</center>
<script language="JavaScript" type="text/JavaScript">
    
    <?php
        if(trim($msg_ajax) != ""){
    ?>
    if(typeof $('#box-msg-utilizacao').fancybox === "function"){
        $('#box-msg-utilizacao')
        .html('<?php echo $msg_ajax;?>')
        .fancybox()
        .trigger('click');
    }else{
        $('#box-msg-utilizacao').html('<?php echo $msg_ajax;?>'); 
    }
    
    <?php 
        }
    ?>
    
	
</script>
<?php
//}	//	end do teste HTTPS //descomentar para implementar https

//Wagner fim trava
//}//end if b_IsLogin_Wagner()
//else echo "Acesso não permitido!!";

//Fechando Conexão
pg_close($connid);

?>