<?php

$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');

if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
	$teste = substr($_SERVER['HTTP_USER_AGENT'], strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') + 4, 4) * 1;
	echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=" . htmlspecialchars($teste, ENT_QUOTES, 'UTF-8') . "\" />";
}
//flag para controle de include do jquery
$GLOBALS["jquery"] = true;

?>
<script src="/prepag2/js/jquery-1.11.3.min.js"></script>
<?php
header("Content-Type: text/html; charset=ISO-8859-1; P3P: CP='CAO PSA OUR'", true);
require_once "../../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_CLASS . "gamer/classIntegracao.php";
require_once DIR_INCS . "inc_register_globals.php";
require_once DIR_INCS . "constantes.php";
validaSessao();

if ($_SERVER['REMOTE_ADDR'] == '187.18.199.57') {
	$logFilePath = 'session_log.txt';

	// Formatando os dados da sessão para salvar no log
	$logData = "Sessão em " . date('Y-m-d H:i:s') . ":\n";
	$logData .= print_r($_SESSION, true) . "\n"; // Converte $_SESSION em string legível

	// Escrevendo no arquivo de log
	$result = file_put_contents($logFilePath, $logData, FILE_APPEND);
}

//Recupera usuario
if (isset($_SESSION['usuarioGames_ser']) && !is_null($_SESSION['usuarioGames_ser'])) {
	$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
	$usuarioId = $usuarioGames->getId();
}

require_once DIR_INCS . "gamer/venda_e_modelos_logica_epp.php";

$rs_venda_row = pg_fetch_array($rs_venda);
$pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
$iforma = $pagto_tipo; //$_SESSION['pagamento.pagto'];
$ultimo_status = $rs_venda_row['vg_ultimo_status'];
$vg_integracao_parceiro_origem_id = $rs_venda_row['vg_integracao_parceiro_origem_id'];

if (!isset($total_carrinho))
	$total_carrinho = 0;
if ($ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
	$sql = "select * from tb_pag_compras " .
		"where idvenda = " . $venda_id . " and idcliente=" . $usuarioId;
	$rs_pagto = SQLexecuteQuery($sql);
	if (!$rs_pagto || pg_num_rows($rs_pagto) == 0) {
		$msg = "Não foi encontrado o pagamento para a venda " . $venda_id . ".\n";
	} else {
		$rs_pagto_row = pg_fetch_array($rs_pagto);
		$banco = $rs_pagto_row['banco'];
		$assinatura = $rs_pagto_row['assinatura'];
		if ($total_carrinho == 0) {
			$total_carrinho = $rs_pagto_row['total'] / 100;
		}
	}
}

if ($total_carrinho == 0) {
	$sql = "select * from tb_pag_compras " .
		"where idvenda = " . $venda_id . " and idcliente=" . $usuarioId;
	$rs_pagto = SQLexecuteQuery($sql);
	if (!$rs_pagto || pg_num_rows($rs_pagto) == 0) {
		$msg = "Não foi encontrado o pagamento para a venda " . $venda_id . ".\n";
	} else {
		$rs_pagto_row = pg_fetch_array($rs_pagto);
		if ($total_carrinho == 0) {
			$total_carrinho = $rs_pagto_row['total'] / 100;
		}
	}
}

if (!(b_IsPagtoBoletoDeposito($pagto_tipo) || b_IsPagtoOnline($pagto_tipo))) {
	//		echo "pagto_tipo: '$pagto_tipo'<br>";
//		echo "b_IsPagtoBoletoDeposito($pagto_tipo): ".b_IsPagtoBoletoDeposito($pagto_tipo)."<br>";
//		echo "b_IsPagtoOnline($pagto_tipo): ".b_IsPagtoOnline($pagto_tipo)."<br>";
//		die("Stop3223");
	$strRedirect = "/prepag2/commerce/conta/lista_vendas.php";

	//Fechando Conexão
	pg_close($connid);

	redirect($strRedirect);
}

// recupera numorder
$OrderId = $_SESSION['pagamento.numorder'];
$orderId = $OrderId;
$numOrder = $OrderId;

// Insere os arquivos de URL de cada banco
if (($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO'])) {
	include RAIZ_DO_PROJETO . "banco/bradesco/inc_functions.php";
	include RAIZ_DO_PROJETO . "banco/bradesco/inc_urls_bradesco.php";
	include RAIZ_DO_PROJETO . "banco/bradesco/config.inc.bradesco_transf.php";
} else if ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
	include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
	include RAIZ_DO_PROJETO . "banco/bancodobrasil/inc_urls_bancodobrasil.php";
} else if ($pagto_tipo == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC) {

	// Recupera Itau ID
	$sql = "select * from tb_pag_compras where idvenda = " . $venda_id . " and idcliente=" . $usuarioId;
	$rs_pagto_id = SQLexecuteQuery($sql);
	if (!$rs_pagto_id || pg_num_rows($rs_pagto_id) == 0) {
		$msg = "Não foi encontrado o pagamento para a venda " . $venda_id . ".\n";
	} else {
		$rs_pagto_id_row = pg_fetch_array($rs_pagto_id);
		$id_transacao_itau = $rs_pagto_id_row['id_transacao_itau'];
	}

	include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
	require_once RAIZ_DO_PROJETO . "banco/itau/inc_config.php";
	require_once RAIZ_DO_PROJETO . "banco/itau/inc_urls_bancoitau.php";

} else if ($pagto_tipo == $PAGAMENTO_HIPAY_ONLINE_NUMERIC) {
	include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
	//		include "../../pag/bep/inc_urls_bancoeprepag.php";
} else if ($pagto_tipo == $PAGAMENTO_PAYPAL_ONLINE_NUMERIC) {
	include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
	//		include "../../pag/bep/inc_urls_bancoeprepag.php";
} else if ($pagto_tipo == $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC) {
	include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
	include DIR_WEB . "prepag2/pag/bep/inc_urls_bancoeprepag.php";
} else if ($pagto_tipo == $PAGAMENTO_PIN_EPREPAG_NUMERIC) {
	$taxa = $PAGAMENTO_PIN_EPP_TAXA;
} else if ($pagto_tipo == $PAGAMENTO_PIX_NUMERIC) {
	include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
	$taxa = $PAGAMENTO_PIX_TAXA;

}

$numOrder = $OrderId;

// Redireciona para a página final no site do Banco
if ($iforma == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
	$img_bank_logo = $GLOBALS['_SESSION']['is_integration'] == true ? "bradesco_logo_dr.gif" : "bradesco_horiz_peq.jpg";
	$simg_bank = "<img src='/imagens/pag/$img_bank_logo' border='0' title='Bradesco'>";
} else if ($iforma == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
	$location = $link_PagtoFacil;
	$img_bank_logo = $GLOBALS['_SESSION']['is_integration'] == true ? "bradesco_logo_dr.gif" : "bradesco_horiz_peq.jpg";
	$simg_bank = "<img src='/imagens/pag/$img_bank_logo' border='0' title='Bradesco'>";
} else if ($iforma == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
	$location = $link_BBDebito;
	$img_bank_logo = $GLOBALS['_SESSION']['is_integration'] == true ? "bb_logo_dr.gif" : "BB_logo_peq.png";
	$simg_bank = "<img src='/imagens/pag/$img_bank_logo' border='0' title='Banco do Brasil'>";
} else if ($iforma == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC) { //$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']
	$location = $link_BItauShopline;
	//		$simg_bank = "<img src='/imagens/pag/botao_conta_itau_.jpg' width='136' height='48' border='0' title='Banco Itaú Shopline'>";

	$img_bank_logo = $GLOBALS['_SESSION']['is_integration'] == true ? "itau_logo_dr.gif" : "Itau_logo_loja.jpg";
	$img_bank_logo_size = $GLOBALS['_SESSION']['is_integration'] == true ? array('84', '68') : array('116', '43');
	$simg_bank = "<img src='/imagens/pag/$img_bank_logo' width='$img_bank_logo_size[0]' height='$img_bank_logo_size[1]' border='0' title='Banco Itaú Shopline'>";
	//echo "location: ".$location."<br>";
} else if ($iforma == $PAGAMENTO_HIPAY_ONLINE_NUMERIC) {
	//			// width='284' height='98'
	$simg_bank = "<img src='/imagens/pag/Logo-hipay.png' width='142' height='49' border='0' title='Banco Hipay'>";
	//echo "location: ".$location."<br>";
} else if ($iforma == $PAGAMENTO_PAYPAL_ONLINE_NUMERIC) {
	// width="476" height="106"
	$simg_bank = "<img src='/imagens/pag/Logo-paypal.jpg' width='159' height='35' border='0' title='PayPal - Pagamento Online'>";
	//echo "location: ".$location."<br>";
} else if ($iforma == $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC) {
	$location = $link_EPP;

	$simg_bank = "<img src='/imagens/pag/epp_logo_loja.gif' width='116' height='43' border='0' title='Banco E-Prepag'>";
	//echo "location: ".$location."<br>";
} else if ($iforma == $PAGAMENTO_PIX_NUMERIC) {
	$simg_bank = "<img src='/imagens/pag/iconePIX.png' width='116' border='0' title='Pagamento PIX' class='top40'>";
} else {
	$location = $link_error;
	$simg_bank = "";
}

$pagina_titulo = "Comprovante";
$path_imgs = "/imagens/";
$cabecalho_file = isset($GLOBALS['_SESSION']['is_integration']) && $GLOBALS['_SESSION']['is_integration'] == true ? RAIZ_DO_PROJETO . "public_html/prepag2/commerce/includes/cabecalho_int.php" : RAIZ_DO_PROJETO . "public_html/game/includes/cabecalho.php";
include $cabecalho_file;

if ($ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO']) { ?>

	<script language="JavaScript" type="text/JavaScript">
	<!--

		function abrePaginaBanco() {
			$("#vaipagamento").click();
		}

		function GP_popupConfirmMsg(msg) { //v1.0
		  document.MM_returnValue = confirm(msg);
		}

		function IrAoBanco() {
			$("#btnIrAoBanco").click();
		}
		function ShowPopupWindowXY(fileName) {
			myFloater = window.open(fileName,'myWindow','scrollbars=yes,status=no,width=' +(0.8*screen.width) +',height='+(0.8*screen.height) +',top='+(0.1*screen.height) +',left='+(0.1*screen.width)+'');
		}

		refresh_snipet = 1;
		<?php
		if ($pagto_tipo != $PAGAMENTO_PIN_EPREPAG_NUMERIC) {
			?>

			// Mostra status da compra
			function refresh_status(){
				$(document).ready(function(){
					$.ajax({
						type: "POST",
						url: "/ajax/gamer/ajax_info_pagamento.php",
						data: "numcompra=<?php echo $numOrder; ?>",
						beforeSend: function(){
							<?php
							//	$("#info_pagamento").html("<table><tr><td valign='middle'><img src='../../dist_commerce/images/loading1.gif' width='42' height='42' border='0' title='Aguardando pagamento...'></td><td>&nbsp;</td><td valign='middle'><font size='1'><b> </b></font></td></tr></table>");
							// "Aguarde... Procurando Pagamento."
							?>
						},
						success: function(txt){
							if (txt != "ERRO") {
								if(txt.indexOf("Pagamento completo em ")>0) {
									$(".hide-pix-success").hide();
																if($("#info_pagamento").attr("pix") == "1"){
																	$("#pagamento_ok").show("slow");
																	$("#info_pagamento").hide();                 
																}else{
																	$("#info_pagamento").html(txt);
																}
								} else {

		//							var stmp = $("#info_pagamento").html();
		//							stmp = stmp.replace(".....", "");
		//							stmp += ".";
		//							$("#info_pagamento").html(stmp);

									if(refresh_snipet==0) {
										clearInterval(refreshIntervalId);
									}
								}
							} else {
							}
						},
						error: function(){
							$("#info_pagamento").html("");
						}
					});
				});
			}

			var refreshIntervalId = setInterval(refresh_status, 2000);

			<?php
		}
		?>

	//--></SCRIPT>

<?php } ?>

<table class="wrapper" border="0" cellspacing="0" bgcolor="#FFFFFF" <?php if ($GLOBALS['_SESSION']['is_integration'] == true)
	echo ''; ?>>
<tr valign="top" align="center">
	<td>

		<?php include DIR_INCS . "gamer/venda_e_modelos_view_epp.php"; ?>

		<?php
		if ($vg_integracao_parceiro_origem_id) {

			//Novo modelo de captura de CPF
			cpf_page($partner_list);

		} else {

			//Testando a necessidade de solicitação de CPF para Gamer
			if ($test_opr_need_cpf || $pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {

				cpf_page_gamer();
			}//end if($test_opr_need_cpf)
		
			require_once DIR_INCS . "gamer/pagto_compr_usuario_dados.php";
		}
		?>

	</td>
</tr>
</table>

<?php if (($ultimo_status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO']) || ($ultimo_status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']) || ($ultimo_status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'])) { ?>
	<table border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
		<tr>
			<td class="texto" align="center" height="25">
				<br>
				Obrigado por comprar conosco!<br><br>
				<?php if ($vg_integracao_parceiro_origem_id) { ?>
					Após o pagamento o crédito será automaticamente ativado na conta do seu jogo.
				<?php } else { ?>
					Após o pagamento a senha será automaticamente enviada para o seu email.
				<?php } ?>
			</td>
		</tr>
	</table>

	<?php if ($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) { ?>
		<table border="0" cellspacing="0" bgcolor="#FFFFFF" class="wrapper">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center" class="texto">
					<input type="button" name="btOK" value="Clique aqui para emitir o Boleto Bancário"
						OnClick="fcnJanelaBoleto();" class="botao_simples">
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center" class="texto">
					&nbsp;
				</td>
			</tr>
		</table>
	<?php } ?>

<?php } else if ($ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) { ?>
		<table border="0" cellspacing="0" bgcolor="#FFFFFF" class="wrapper">
			<tr>
				<td class="texto" align="center" height="25">
					<br>
					Obrigado por comprar conosco!<br><br>
				<?php if ($vg_integracao_parceiro_origem_id) { ?>
						Sua compra foi processada e o crédito será ativado diretamente na sua conta no jogo.
				<?php } else { ?>
						Sua compra foi processada e os pins enviados para seu email cadastrado conosco.
				<?php } ?>
				</td>
			</tr>
		</table>
	<?php if (($banco == "237") && ($assinatura)) { ?>
			<center>
				<table border="0" cellspacing="0" align="center">
					<tr bgcolor="F0F0F0">
						<td class="texto" align="center" height="25"><b>Autenticação</b></td>
					</tr>
					<tr bgcolor="F0F0F0">
						<td class="texto" align="center"><?php formataAssinatura($assinatura) ?></td>
					</tr>
				</table>
			</center>

	<?php } ?>



<?php } else if ($ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA']) { ?>
			<table border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
				<tr>
					<td class="texto" align="center" height="25">
						<br>
						Obrigado por comprar conosco!<br><br>
						Sua compra foi cancelada pelo sistema. Tente novamente.
					</td>
				</tr>
			</table>


<?php } else { //	apenas $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'] ?>
			<center>
				<TABLE cellspacing="0" cellpadding="0" class="wrapper int-box" style="border-top: 0px;">
					<tr>
						<td align="center">
							<div id="link_bank"> <?php // bloco pagamento inicio ?>
								<table border="0" cellspacing="0" width="75%" class="int-pagamento-compr-online">
								<?php
								if ((isset($simg_bank) && $simg_bank) || $pagto_tipo != $PAGAMENTO_PIN_EPREPAG_NUMERIC) {
									?>
										<tr>
											<td class="texto" height="25" align="center">
											<?php
											if (isset($simg_bank) && $simg_bank) {
												?>
													<table border="0" cellspacing="0" width="90%"
														class="int-pagamento-compr-online-logo hide-pix-success">
														<tr>
															<td class="texto" align="left" width="0%"><?php echo $simg_bank; ?></td>
															<td class="texto" align="center" width="100%" style="color: #1681b7;">
																&nbsp;<?php
																if ($pagto_tipo == $PAGAMENTO_PIX_NUMERIC) {
																	$sql = "select nome from provedor_pix where ativo = 'A';";
																	$ativo = SQLexecuteQuery($sql);
																	$ativoNome = pg_fetch_assoc($ativo);

																	if (!defined("PAGAMENTO_PIX_CHAVEAMENTO")) {
																		include "/www/includes/config.MeiosPagamentos.php";
																	}

																	if (PAGAMENTO_PIX_CHAVEAMENTO == "a") {
																		if (number_format(($total_carrinho + $taxa), 2, '.', '') > VALOR_TROCA) {
																			$ativoNome["nome"] = PAGAMENTO_PIX_PROVEDOR2;
																		} else {
																			$ativoNome["nome"] = PAGAMENTO_PIX_PROVEDOR;
																		}
																		require_once RAIZ_DO_PROJETO . 'banco/pix/' . $ativoNome["nome"] . '/config.inc.pix.php';
																		//classe para pagamento em pix usada atualmente
																		if ($ativoNome["nome"] == "asaas") {
																			$pix = new classPIX();
																			$params = array(
																				'metodo' => PIX_REGISTER,
																				'cpf_cnpj' => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
																				'nome' => $usuarioGames->ug_nome_cpf,
																				'valor' => number_format(($total_carrinho + $taxa), 2, '.', ''),
																				'descricao' => "E-PREPAG",
																				'idpedido' => $ARRAY_CONCATENA_ID_VENDA['gamer'] . $_SESSION['pagamento.numorder'],
																				'venda_id' => $venda_id,
																				'email' => $usuarioGames->ug_sEmail
																			);
																			echo $pix->callService($params);
																		} else if ($ativoNome["nome"] == "mercadopago") {
																			$pix = new classPIX();
																			$itens = [];
																			pg_result_seek($rs_venda_modelos, 0);
																			while ($venda_modelo = pg_fetch_assoc($rs_venda_modelos)) {
																				$itens[] = [
																					"title" => $venda_modelo['vgm_nome_produto'] ? $venda_modelo['vgm_nome_produto'] : 'Produto sem nome',
																					"description" => $venda_modelo['vgm_nome_modelo'] ? $venda_modelo['vgm_nome_modelo'] : 'Sem descrição',
																					"category_id" => "integracao_gamer",
																					"unit_price" => isset($venda_modelo['vgm_valor']) ? $venda_modelo['vgm_valor'] : 0.0,
																					"quantity" => isset($venda_modelo['vgm_qtde']) ? $venda_modelo['vgm_qtde'] : 1,
																				];
																			}
																			// Garante pelo menos 1 item, mesmo se $arr_venda_modelos estiver vazio ou inválido
																			if (empty($itens)) {
																				$itens[] = [
																					"title" => "Produto sem nome",
																					"description" => "Não foi possível recuperar o produto",
																					"category_id" => "pins_gamer",
																					"unit_price" => 0.0,
																					"quantity" => 1,
																				];
																			}
																			$params = array(
																				'metodo' => PIX_REGISTER,
																				'cpf_cnpj' => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
																				'nome' => $usuarioGames->ug_nome_cpf,
																				'valor' => number_format(($total_carrinho + $taxa), 2, '.', ''),
																				'descricao' => "E-PREPAG",
																				'idpedido' => $ARRAY_CONCATENA_ID_VENDA['gamer'] . $_SESSION['pagamento.numorder'],
																				'email' => $usuarioGames->ug_sEmail,
																				'venda_id' => $venda_id,
																				'itens' => $itens
																			);
																			echo $pix->callService($params);
																		} else {
																			echo "Pix não disponível no momento.";
																		}
																	} else {
																		require_once RAIZ_DO_PROJETO . 'banco/pix/' . $ativoNome["nome"] . '/config.inc.pix.php';

																		if ($ativoNome["nome"] == "asaas") {
																			$pix = new classPIX();
																			$params = array(
																				'metodo' => PIX_REGISTER,
																				'cpf_cnpj' => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
																				'nome' => $usuarioGames->ug_nome_cpf,
																				'valor' => number_format(($total_carrinho + $taxa), 2, '.', ''),
																				'descricao' => "E-PREPAG",
																				'idpedido' => $ARRAY_CONCATENA_ID_VENDA['gamer'] . $_SESSION['pagamento.numorder'],
																				'venda_id' => $venda_id,
																				'email' => $usuarioGames->ug_sEmail
																			);
																			echo $pix->callService($params);
																		} else if ($ativoNome["nome"] == "mercadopago") {
																			$pix = new classPIX();
																			$itens = [];
																			pg_result_seek($rs_venda_modelos, 0);
																			while ($venda_modelo = pg_fetch_assoc($rs_venda_modelos)) {
																				$itens[] = [
																					"title" => $venda_modelo['vgm_nome_produto'] ? $venda_modelo['vgm_nome_produto'] : 'Produto sem nome',
																					"description" => $venda_modelo['vgm_nome_modelo'] ? $venda_modelo['vgm_nome_modelo'] : 'Sem descrição',
																					"category_id" => "integracao_gamer",
																					"unit_price" => isset($venda_modelo['vgm_valor']) ? $venda_modelo['vgm_valor'] : 0.0,
																					"quantity" => isset($venda_modelo['vgm_qtde']) ? $venda_modelo['vgm_qtde'] : 1,
																				];
																			}
																			// Garante pelo menos 1 item, mesmo se $arr_venda_modelos estiver vazio ou inválido
																			if (empty($itens)) {
																				$itens[] = [
																					"title" => "Produto sem nome",
																					"description" => "Não foi possível recuperar o produto",
																					"category_id" => "pins_gamer",
																					"unit_price" => 0.0,
																					"quantity" => 1,
																				];
																			}
																			$params = array(
																				'metodo' => PIX_REGISTER,
																				'cpf_cnpj' => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
																				'nome' => $usuarioGames->ug_nome_cpf,
																				'valor' => number_format(($total_carrinho + $taxa), 2, '.', ''),
																				'descricao' => "E-PREPAG",
																				'idpedido' => $ARRAY_CONCATENA_ID_VENDA['gamer'] . $_SESSION['pagamento.numorder'],
																				'email' => $usuarioGames->ug_sEmail,
																				'venda_id' => $venda_id,
																				'itens' => $itens
																			);
																			echo $pix->callService($params);
																		} else {
																			$pix = new Pix(
																				"CPF",
																				str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
																				!empty($usuarioGames->ug_nome_cpf) ? $usuarioGames->ug_nome_cpf : "Nao possui nome",
																				$ARRAY_CONCATENA_ID_VENDA['gamer'] . $_SESSION['pagamento.numorder'],
																				number_format(($total_carrinho + $taxa), 2, '', '')
																			);
																			echo $pix->callService();
																		}
																	}

																	//var_dump($params); die();
													
																} //end else if($pagto_tipo == $PAGAMENTO_PIX_NUMERIC)
													
																?></td>
														</tr>
													</table>
											<?php
											}

											if ($pagto_tipo != $PAGAMENTO_PIN_EPREPAG_NUMERIC && $pagto_tipo != $PAGAMENTO_PIX_NUMERIC) {
												?>

													<table border="0" cellspacing="0" bgcolor="#FFFFFF" width="300" height="50">
														<tr>
															<td align="center" class="texto" align="center" height="100">
																<div id="info_pagamento">
																	<table>
																		<tr>
																			<td valign='middle'><img src='/imagens/loading1.gif' width='42'
																					height='42' border='0' title='Aguardando pagamento...'
																					class="int-pagamento-compr-online-loading"></td>
																			<td>&nbsp;</td>
																			<td valign='middle'>
																				<font color='#FF0000' size='1'
																					class="int-pagamento-compr-online-message1">Clique
																					abaixo para efetuar o pagamento.<br>Após o pagamento o
																					processamento leva alguns instantes.</font>
																			</td>
																		</tr>
																	</table>
																</div>
															</td>
														</tr>
													</table>
											<?php
											}
											if ($pagto_tipo == $PAGAMENTO_PIX_NUMERIC) {
												?>
													<div id="info_pagamento" pix="1">
														<b>ATENÇÃO: Não efetue o Pix fora do nosso site. Para cada pagamento será necessário
															gerar um novo pedido.</b>
													</div>
											<?php
											}
											?>
											</td>
								<?php } ?>
									</tr>
									<tr bgcolor="ffffff">
									<?php
									//inicio do bloco de pagamento com PINS EPREPAG
									if ($pagto_tipo != $PAGAMENTO_PIN_EPREPAG_NUMERIC) {
										?>

									<?php
										//ELSE para exibição do pagamento PINs E-PREPG
									} else {
										unset($_SESSION['PINEPP']);
										unset($_SESSION['PIN_NOMINAL']);
										echo "</tr><tr><td>
						<div id='box-principal' name='box-principal'>
						";
										// Deserializa os dados da sessão
										$aux_saldo = unserialize($_SESSION['usuarioGames_ser']);

										// Converte os dados para JSON
										$aux_saldo_json = json_encode($aux_saldo);
										// Inclui o script JavaScript para exibir os dados no console
										echo "
						<script>
							// Passe os dados do PHP para o JavaScript
							var auxSaldo = $aux_saldo_json;

							// Exiba os dados no console
							console.log(auxSaldo);
							
						</script>
						";

										if (!isset($total_geral_epp_cash)) {
											$total_geral_epp_cash = 0; // Ou algum valor apropriado
											// Executa a consulta SQL para calcular o valor total
											$sql = "SELECT * FROM tb_venda_games vg 
							INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
							WHERE vg.vg_id = " . $venda_id;

											$rs_venda_modelos = SQLexecuteQuery($sql);

											// Calcula o total_geral_epp_cash baseado nos resultados da consulta
											while ($row = pg_fetch_assoc($rs_venda_modelos)) {
												$qtde = $row['vgm_qtde'];
												//$valor = $row['vgm_valor'];
												//$total_geral += $valor*$qtde;
												$total_geral_epp_cash += $row['vgm_valor_eppcash'] * $qtde;

											}
										}
										if (($total_geral_epp_cash / 100 + $taxa) > 0) {

											$aux_saldo = unserialize($_SESSION['usuarioGames_ser']);

											include_once DIR_CLASS . "classAtivacaoPinTemplate.class.php";
											/*
											Na confecção do vetor abaixo onde está sendo mencionado:
											[ $total_geral_epp_cash/100 ]
											não se trata de conversão e sim uma divisão simples para que o ajax receba o valor dividido por 100 com a finalidade
											de facilitar os calculos
											*/
											if (b_isIntegracao() && b_isIntegracao_with_nonvalidated_email() && (!b_isIntegracao_logged_in())) {
												$user_logado_aux = false;
												$saldo_aux = 0;
												$saldo_final_aux = number_format((0 - ($total_geral_epp_cash / 100 + $taxa)), 2, '.', '');
											} else {
												$user_logado_aux = true;
												$saldo_aux = $aux_saldo->ug_fPerfilSaldo;
												$saldo_final_aux = number_format(($aux_saldo->ug_fPerfilSaldo - ($total_geral_epp_cash / 100 + $taxa)), 2, '.', '');
											}


											//echo "[".$aux_saldo->ug_fPerfilSaldo."]";
											$paramList = array(
												'jquery_core_include' => false,
												'url_resources' => '/ativacao_pin/',
												'usuarioLogado' => $user_logado_aux,
												'saldo' => $saldo_aux,
												'valor_pedido' => ($total_geral_epp_cash / 100 + $taxa),
												'saldo_final' => $saldo_final_aux,
												'email' => $_SESSION['integracao_client_email'],
											);
											$ativacaoPinTemplate2 = new AtivacaoPinTemplate($paramList);
											echo $ativacaoPinTemplate2->boxAtivacaoPin();

										}//end if(($total_geral_epp_cash/100+$taxa) > 0)
										else
											echo "Dados da compra recebido sem valores.";
										echo "
						</td></tr>
						</div>";
										?>
									<?php
									}  //fim do bloco de pagamento com PINS EPREPAG
								

									//inicio do bloco de pagamentos CIELO
									if (b_IsPagtoCielo($pagto_tipo)) {

										//Aplicando nova regra
										include_once DIR_CLASS . "gamer/classLimite.php";
										$limite = new Limite(getCodigoCaracterParaPagto($pagto_tipo), intval($usuarioId));

										//Verificando Token digitado
										if (!empty($token) && !empty($cielo_pan)) {
											$limite->setStatusTokenUtilizado($cielo_pan, $token);
										}

										if ($limite->getPrimeiraVendaGamers($cielo_pan, $data_exibicao)) {
											echo "
							<style>
								.divToken { font: 11px bolder arial, sans-serif; color: #000000; margin: 20px 20px 0px 20px; text-align: left; }
								.titulo {font: 13px bolder arial, sans-serif; font-weight: bold;}
							</style>
							<div class='divToken'><span class='titulo'>Validação do cartão</span><br><br>
								Para a segurança desta transação, pedimos que digite abaixo o código de 6 dígitos que aparece na fatura do seu cartão. ( Este código aparece após o *, na descrição de sua Última compra pela E-prepag realizada em <nobr>" . htmlspecialchars($data_exibicao, ENT_QUOTES, 'UTF-8') . "</nobr><br><br>
								Esta operação será solicitada somente uma vez para este cartão.<br><br>
								<form action='" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "' method='POST'>
									<input type='hidden' name='cielo_pan' id='cielo_pan' value='" . htmlspecialchars($cielo_pan, ENT_QUOTES, 'UTF-8') . "'/>
									<input type='text' name='token' id='token' maxlength='6' size='5'/>
									<input type='submit' name='btnToken' id='btnToken' value='Enviar'>
								</form>
							</div>";
											//$cielo_pan contem o hash do cartão
										}//end if($limite->getPrimeiraVendaGamers())
										else {

											// controle primeiro pedido
											$dadosPedidoDescricao = "EPPPEDIDODESCRICAO";
											//$softDescriptor = "EPP".date("His")."";
								
											//Nova linha abaixo colocada por Wagner de Miranda
											$softDescriptor = $_SESSION['pagamento.token'];
											//echo "[$softDescriptor]<BR>";
								
											switch ($pagto_tipo) {
												case $PAGAMENTO_VISA_DEBITO_NUMERIC:
													$codigoBandeira = "visa";
													$formaPagamento = "A";
													$indicadorAutorizacao = "2";
													break;
												case $PAGAMENTO_VISA_CREDITO_NUMERIC:
													$codigoBandeira = "visa";
													$formaPagamento = "1";
													$indicadorAutorizacao = "2";
													break;
												case $PAGAMENTO_MASTER_DEBITO_NUMERIC:
													$codigoBandeira = "mastercard";
													$formaPagamento = "A";
													$indicadorAutorizacao = "2";
													break;
												case $PAGAMENTO_MASTER_CREDITO_NUMERIC:
													$codigoBandeira = "mastercard";
													$formaPagamento = "1";
													$indicadorAutorizacao = "2";
													break;
												case $PAGAMENTO_ELO_DEBITO_NUMERIC:
													$codigoBandeira = "elo";
													$formaPagamento = "A";
													$indicadorAutorizacao = "2";
													break;
												case $PAGAMENTO_ELO_CREDITO_NUMERIC:
													$codigoBandeira = "elo";
													$formaPagamento = "1";
													$indicadorAutorizacao = "3";
													break;
												case $PAGAMENTO_DINERS_CREDITO_NUMERIC:
													$codigoBandeira = "diners";
													$formaPagamento = "1";
													$indicadorAutorizacao = "3";
													break;
												case $PAGAMENTO_DISCOVER_CREDITO_NUMERIC:
													$codigoBandeira = "discover";
													$formaPagamento = "1";
													$indicadorAutorizacao = "3";
													break;
											}
											?>
												<td align='right'>
													<form action="/cielo/pages/novoPedidoAguarde.php" method="POST" target="_blank">
														<input type="hidden" name="produto" id="produto"
															value="<?php echo ($total_geral + $taxa) * 100; ?>" />
														<input type="hidden" name="codigoBandeira" id="codigoBandeira"
															value="<?php echo $codigoBandeira; ?>" />
														<input type="hidden" name="formaPagamento" id="formaPagamento"
															value="<?php echo $formaPagamento; ?>" />
														<input type="hidden" name="capturarAutomaticamente" id="capturarAutomaticamente"
															value="true" />
														<input type="hidden" name="indicadorAutorizacao" id="indicadorAutorizacao"
															value="<?php echo $indicadorAutorizacao; ?>" />

														<input type="hidden" name="dadosPedidoDescricao" id="dadosPedidoDescricao"
															value="<?php echo $dadosPedidoDescricao; ?>" />
														<input type="hidden" name="softDescriptor" id="softDescriptor"
															value="<?php echo $softDescriptor; ?>" />

														<input type="hidden" name="campolivre" id="campolivre"
															value="<?php echo md5(uniqid(rand(), true)); ?>" />
														<!--font color='blue'> R$ <?php //echo number_format(($total_geral+$taxa),2,',','.'); ?></font-->
													<?php
													$sql = "select * from tb_pag_compras " .
														"where idvenda = " . $venda_id . " and idcliente=" . $usuarioId;
													$rs_pagto = SQLexecuteQuery($sql);
													if (!$rs_pagto || pg_num_rows($rs_pagto) == 0) {
														$msg = "Não foi encontrado o pagamento para a venda " . $venda_id . ".\n";
													} else {
														$rs_pagto_row = pg_fetch_array($rs_pagto);
														$numcompra = $rs_pagto_row['numcompra'];
														?>
															<!--br>pagto_tipo : <?php //echo $pagto_tipo; ?><br>vg_id: <?php //echo $venda_id; ?><br>usuarioId: <?php //echo $usuarioId; ?><br>numcompra: <?php //echo $numcompra; ?><br-->
															<input type="hidden" name="numcompra" id="numcompra"
																value="<?php echo $numcompra; ?>" />
															<input type="submit" name="btnIrCielo" class="btn btn-success"
																value="Clique aqui para pagar">
													<?php
													}
													?>
													</form>
												</td>
										<?php
										}//end else do if($limite->getPrimeiraVendaGamers())
									}//fim do bloco de pagamentos CIELO                                        
									?>

									</tr>
									<tr bgcolor="ffffff">
										<td align="center">&nbsp;</td>
									</tr>
									<tr bgcolor="ffffff">
										<td align="center" class="texto">
											<table border="0" cellspacing="0" width="90%">
												<tr>
													<td class="texto" align="center" width="33%">
													<?php
													if (!b_isIntegracao()) {
														?>
															<input type="button" name="btVoltar" value="Voltar"
																OnClick="window.location='/prepag2/commerce/conta/lista_vendas.php';"
																class="botao_simples">
													<?php
													}
													?>&nbsp;
													</td>
													<td class="texto" align="center" width="33%">&nbsp;</td>

													<td class="texto" align="center" width="33%">
													<?php
													if (($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO'])) {
														$cesta = $rs_pagto_row['cesta'];
														$numcompra = $rs_pagto_row['numcompra'];

														if (strpos($cesta, "\n")) {
															$cesta_desc = explode("\n", $cesta);

															$cesta_descricao = "";
															$pattern = "/item:/";
															foreach ($cesta_desc as $i => $prod) {
																if (preg_match($pattern, $prod)) {
																	$cesta_descricao .= $prod;
																}
															}
														} else {
															$cesta_descricao = $cesta;
														}

														$obj_pagamento = new classBradescoTransferencia();

														$array_infos_ws = $obj_pagamento->montaVetorInformacoes($usuarioGames, $total_carrinho, $numcompra, trim($cesta_descricao));
														//echo "<pre>".print_r($array_infos_ws,true);die();
												
														if (is_null($array_infos_ws)) {
															$msg_problem = "Sua sessão expirou!";
															$titulo = "Sessão expirada";
														} else {
															$comunica = $obj_pagamento->Req_EfetuaConsultaURL($array_infos_ws, $lista_resposta);

															if (is_null($comunica)) {
																$titulo = "ERRO - Problema na validação de seus dados";
																$msg_problem = "Problema ao validar seus dados cadastrados! Por favor, relate o problema ao Suporte";
															} else {
																if (is_array($comunica)) {
																	$titulo = "ERRO - Problema de comunicação com o Bradesco";
																	$msg_problem = "Houve um problema de comunicação com o Bradesco! Tente novamente mais tarde. Obrigado!";
																} else {
																	$location = $comunica;
																}
															}
														}

														if (isset($msg_problem)) {
															?>
															</td>
															<div class="col-md-12 top10 col-sm-12 col-xs-12">
																<p class="txt-vermelho"><?php echo $msg_problem; ?></p>
															</div>

															<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet"
																type="text/css" />
															<link href="/css/creditos.css" rel="stylesheet" type="text/css" />
															<script type="text/javascript" src="/js/jquery.js"></script>
															<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
															<script type="text/javascript"
																src="/includes/bootstrap/js/bootstrap.min.js"></script>
															<!-- Modal -->
															<div id="modal-problema-comunicacao" class="modal fade text-left"
																data-backdrop="static" role="dialog">
																<div class="modal-dialog">
																	<!-- Modal content-->
																	<div class="modal-content">
																		<div class="modal-header">
																			<h4 class="modal-title txt-vermelho"><?php echo $titulo; ?></h4>
																		</div>
																		<div class="modal-body alert alert-danger">
																			<div class="form-group top10">
																				<p><?php echo $msg_problem; ?></p>
																			</div>
																		</div>
																		<div class="modal-footer">
																			<button type="button" class="btn btn-default"
																				data-dismiss="modal">Fechar</button>
																		</div>
																	</div>
																</div>
															</div>
															<script>
																$("#info_pagamento").hide();
																$("#modal-problema-comunicacao").modal();
															</script>
													<?php
													die();
														} else {
															?>
															<form action="" method="post" target="_blank">
																<input class="btn btn-success top50" type="button" name="btnIrAoBanco"
																	value="Clique aqui para pagar"
																	onclick="window.open('<?php echo $location; ?>')"
																	class="int-btn1 grad1 int-pagamento-compr-online-btn1 btn btn-success btn btn-sm btn-success">
															</form>
													<?php
														}
														//Dummy
//$smsg = "LOG Integração pagamentos - ".date("Y-m-d H:i:s")."\n orderId1: $orderId, OrderId1: $OrderId, numOrder1: $numOrder\n  taxa: $taxa\n location: $location\n";
//gravaLog_TMP($smsg);
												
														?>
												<?php
													} else if ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
														?>
															<form action="<?php echo $location; // "../debug.php" ?>" method="post"
																target="_blank"><input type="submit" name="btnIrAoBanco"
																	value="Clique aqui para pagar"
																	class="int-btn1 grad1 int-pagamento-compr-online-btn1 btn btn-success btn btn-sm btn-success">

																<input type="hidden" name="idConv" value="<?php echo $bbr_idConv; ?>">
																<input type="hidden" name="refTran" value="<?php echo $bbr_refTran; ?>">
																<input type="hidden" name="valor" value="<?php echo $bbr_valor; ?>">
																<input type="hidden" name="qtdPontos"
																	value="<?php echo $bbr_qtdPontos; ?>">
																<input type="hidden" name="dtVenc" value="<?php echo $bbr_dtVenc; ?>">
																<input type="hidden" name="tpPagamento"
																	value="<?php echo $bbr_tpPagamento; ?>">
																<input type="hidden" name="urlRetorno"
																	value="<?php echo $bbr_urlRetorno; ?>">
																<input type="hidden" name="urlInforma"
																	value="<?php echo $bbr_urlInforma; ?>">
																<input type="hidden" name="nome" value="<?php echo $bbr_nome; ?>">
																<input type="hidden" name="endereco" value="<?php echo $bbr_endereco; ?>">
																<input type="hidden" name="cidade" value="<?php echo $bbr_cidade; ?>">
																<input type="hidden" name="uf" value="<?php echo $bbr_uf; ?>">
																<input type="hidden" name="cep" value="<?php echo $bbr_cep; ?>">
																<input type="hidden" name="msgLoja" value="<?php echo $bbr_msgLoja; ?>">
															</form>
												<?php
													} else if ($pagto_tipo == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC) { // $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']
												
														$cripto = new Itaucripto();

														$dados_cripto = $cripto->geraDados($codEmp, $pedido, $valorAux, $observacao, $chave, $nomeSacado, $codigoInscricao, $numeroInscricao, $enderecoSacado, $bairroSacado, $cepSacado, $cidadeSacado, $estadoSacado, $dataVencimento, $urlRetorna, $ObsAdicional1, $ObsAdicional2, $ObsAdicional3);
														//-----------------------------------------------------------------------------------------------------------------------------------------------------
//MODO UTILIZANDO ASP - DESCOMENTAR CASO UTILIZAR ASP
//							  $dados = getItauCrypto($form_fields, "pagto");
//
//							  $aretorno = explode("\n", $dados);
//
//                            $dados_cripto = $aretorno[9];
//-----------------------------------------------------------------------------------------------------------------------------------------------------
														?>
																<form action="<?php echo $location; ?>" method="post" target="_blank">
																	<INPUT type="hidden" name="DC" value="<?php echo $dados_cripto ?>">
																	<input type="submit" name="btnIrAoBanco" value="Clique aqui para pagar"
																		class="int-btn1 grad1 int-pagamento-compr-online-btn1 btn btn-success">
																</form>
											<?php } else if ($pagto_tipo == $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC) { ?>
													<?php
													//echo "(usuarioId: ".$usuarioId." , venda_id: ". $venda_id.")"
											
													$_SESSION['Banco_EPP_usuarioId'] = $usuarioId;
													$_SESSION['Banco_EPP_venda_id'] = $venda_id;
													?>
																	<form action="" method="post" target="_blank" name="formEPP"><input
																			type="button" name="btnIrAoBanco" value="Clique aqui para pagar"
																			onclick="window.open('<?php echo $location; ?>')">
																	</form>
												<?php
													} elseif ($pagto_tipo == $PAGAMENTO_PAYPAL_ONLINE_NUMERIC) { // $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE'] (
												
														// aqui exibimos o botao paypal que e gerado no inc_gen_order_pay.php
														include "../../pag/pay/inc_paypal_botao.php";
														//echo "total_geral: ".$total_geral."<br>";
														// Para uso em testes PayPal/Hipay
														$total_carrinho_nominal = $GLOBALS['_SESSION']['carrinho_total_geral_treinamento'];

														$amount = $total_carrinho_nominal;		//$total_geral;
//echo "amount: ".$amount."<br>";
//echo "number_format(amount,2): ".number_format($amount,2)."<br>";
//echo "<pre>".print_r($GLOBALS,true)."</pre>";
//echo "_SESSION['venda']: ".$_SESSION['venda']."<br>";
														$item_number = $OrderId;
														$item_name = montaCesta_pag_paypal($_SESSION['venda']);	//"Pagamento Online EPP";
//echo "item_name: <br>".str_replace("\n", "<br>\n", $item_name)."<br>";
												
														?>
																	<form action="../../pag/pay/paypal_process.php" target="_blank">
																		<input type="hidden" name="cmd" value="_xclick">
																		<input type="hidden" name="business" value="<?php echo $business ?>">
																		<input type="hidden" name="item_name" value="<?php echo $item_name ?>">
																		<input type="hidden" name="item_number" value="<?php echo $item_number ?>">
																		<input type="hidden" name="INVNUM" value="<?php echo $item_number ?>">
																		<input type="hidden" name="invoice" value="<?php echo $item_number ?>">
																		<input type="hidden" name="amount"
																			value="<?php echo number_format($amount, 2) ?>">
																		<input type="hidden" name="mc_gross"
																			value="<?php echo number_format($amount, 2) ?>">
																		<input type="hidden" name="tax" value="<?php echo $taxas ?>">
																		<input type="hidden" name="quantity" value="1">
																		<input type="hidden" name="currency_code"
																			value="<?php echo $currencyValue ?>">
																		<input type="hidden" name="button_subtype" value="services">
																		<input type="hidden" name="no_note" value="1">
																		<input type="hidden" name="no_shipping" value="1">
																		<input type="hidden" name="rm" value="1">
																		<input type="hidden" name="return" value="<?php echo $retornosucesso ?>">
																		<input type="hidden" name="cancel_return"
																			value="<?php echo $retornocancela ?>">
																		<input type="hidden" name="bn"
																			value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest">
																		<input type="hidden" name="cbt" value="Continue">
																		<input type="image"
																			src="<?php echo $https; ?>://www.sandbox.paypal.com/pt_BR/i/btn/btn_buynowCC_LG.gif"
																			border="0" name="submit" title="Pague com PayPal!">
																		<img alt="" border="0"
																			src="<?php echo $https; ?>://www.sandbox.paypal.com/en_US/i/scr/pixel.gif"
																			width="1" height="1">
																	</form>

												<?php
													} elseif ($pagto_tipo == $PAGAMENTO_HIPAY_ONLINE_NUMERIC) { // $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE'] (
												
														// Para uso em testes PayPal/Hipay
														$total_carrinho_nominal = $GLOBALS['_SESSION']['carrinho_total_geral_treinamento'];
														$amount = $total_carrinho_nominal;		//$total_geral;
														?>
																	<form action="/prepag2/pag/hpy/hipay_single_payment.php" target="_blank">
																		<input type="hidden" name="numcompra" id="numcompra"
																			value="<?php echo $_SESSION['pagamento.numorder'] ?>">
																		<input type="hidden" name="amount" id="amount"
																			value="<?php echo number_format($amount, 2) ?>">
																		<input type="image"
																			src="<?php echo $https; ?>://www.e-prepag.com.br/prepag2/commerce/images/botao_hipay.gif"
																			border="0" name="submit" title="Hipay">
																	</form>
												<?php
													}
													?>
										</td>

									</tr>
								</table>
						</td>
					</tr>
				<?php
				//inicio do bloco de pagamento com PINS EPREPAG
				if ($pagto_tipo != $PAGAMENTO_PIN_EPREPAG_NUMERIC && $pagto_tipo != $PAGAMENTO_PIX_NUMERIC) {
					?>

						<tr>
							<td align="center" class="texto" colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td align="center" class="texto" colspan="2">Os seus dados serão fornecidos com toda segurança apenas ao seu
								banco.</td>
						</tr>
				<?php
				}
				?>

				</table>
				<br>
				</div> <?php // bloco pagamento fim  ?>

				<div id="pagamento_ok"> <?php // bloco pagamento efetuado inicio ?>
					<script language="JavaScript" type="text/javascript">

						function send_to_trans() {
							document.forms['last_trans'].submit();
						}

					</script>
					<form method=post name="last_trans" <?php if (!getAutoClose_By_ID($vg_integracao_parceiro_origem_id)) {
						echo "target=\"_blank\" ";
					} ?>action="<?php echo getPartner_return_url_By_ID($vg_integracao_parceiro_origem_id) ?>">
						<input type="hidden" name="store_id" value="<?php echo $vg_integracao_parceiro_origem_id ?>">
						<input type="hidden" name="cliente_email" value="<?php echo $_SESSION['integracao_client_email'] ?>">
						<input type="hidden" name="order_id" value="<?php echo $_SESSION['integracao_order_id'] ?>">
						<p><?php //echo getPartner_return_url_By_ID($vg_integracao_parceiro_origem_id) ?>
						<p>

					</form>
					<center>
						Pagamento realizado com sucesso.<br>
				<?php if ($vg_integracao_parceiro_origem_id) {
					?>
							Em alguns minutos o crédito será ativado no jogo.<br>
					<?php //" e uma mensagem será enviada para seu email cadastrado" ?>
							Para retornar ao site do jogo <a href="javascript:send_to_trans()">clique aqui</a><br>
				<?php } else { ?>
							O crédito foi enviado para seu Email cadastrado.<br>
				<?php } ?>
						<br>

				<?php if ($vg_integracao_parceiro_origem_id) {
				/*
			?>
			<input type="button" name="btVoltar" value="Voltar ao site do Parceiro" OnClick="window.location='<?php getPartner_return_url_By_ID($vg_integracao_parceiro_origem_id) ?>';" class="botao_simples">
			<?php
				*/
			} else { ?>
							<input type="button" name="btVoltar" value="Voltar" OnClick="window.location='/prepag2/commerce/index.php';"
								class="botao_simples">
				<?php } ?>

					</center>
				</div> <?php // bloco pagamento efetuado fim  ?>
				<div id="pagamento_cancela"> <?php // bloco pagamento cancelado inicio ?>
					<center>
						Compra <font color="#FF0000">cancelada</font> por falta de pagamento.<br>
						Se ainda quiser realizar a compra, tente novamente e complete o pagamento sem demorar muito.<br>
						Obrigado.<br>
						<br>

						<input type="button" name="btVoltar" value="Voltar" OnClick="window.location='/prepag2/commerce/index.php';"
							class="botao_simples">

					</center>
				</div> <?php // bloco pagamento cancelado fim  ?>
				<script language="JavaScript" type="text/JavaScript">
				$("#pagamento_ok").hide();
				$("#pagamento_cancela").hide();
			</script>

				</td>
				</tr>
				</table>

	<?php } ?>

	<?php
	require_once RAIZ_DO_PROJETO . "public_html/prepag2/commerce/includes/rodape.php";
	?>
</center>

<script language="JavaScript" type="text/JavaScript">
<!--
<?php
//	// Abre janela para pagamento no site do banco
//	ShowPopupWindowXY('<_?php echo $location; ?_>');
?>
//-->
</SCRIPT>

<!-- Google Code for Analytics Page -->
<script src="<?php echo $https; ?>://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
	_uacct = "UA-1903237-3";
	urchinTracker();
</script>
<!-- Google Code for P&aacute;gina de pagamento Conversion Page -->
<script type="text/javascript">
	/* <![CDATA[ */
	var google_conversion_id = 1052651518;
	var google_conversion_language = "pt";
	var google_conversion_format = "1";
	var google_conversion_color = "ffffff";
	var google_conversion_label = "WS5VCIKYswIQ_t_49QM";
	var google_conversion_value = 0;
	/* ]]> */
</script>
<script type="text/javascript" src="<?php echo $https; ?>://www.googleadservices.com/pagead/conversion.js">
</script>
<!-- Facebook Pixel Code -->
<script>
	!function (f, b, e, v, n, t, s) {
		if (f.fbq) return; n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments) }; if (!f._fbq) f._fbq = n;
		n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0;
		t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s)
	}(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '228069144336893'); // Insert your pixel ID here.
	fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
		src="https://www.facebook.com/tr?id=228069144336893&ev=PageView&noscript=1" /></noscript>
<!-- End Facebook Pixel Code -->
<noscript>
	<div style="display:inline;">
		<img height="1" width="1" style="border-style:none;" alt=""
			src="<?php echo $https; ?>://www.googleadservices.com/pagead/conversion/1052651518/?label=WS5VCIKYswIQ_t_49QM&guid=ON&script=0" />
	</div>
</noscript>
<?php

//Fechando Conexão
//pg_close($connid);

?>