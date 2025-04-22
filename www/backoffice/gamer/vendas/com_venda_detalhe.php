<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."includes/gamer/functions_vendaGames.php"; 
require_once $raiz_do_projeto."includes/inc_Pagamentos.php"; 
require_once "/www/includes/bourls.php";

	set_time_limit ( 300 ) ;

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

	$bDebug = false;
if($bDebug) {
	echo "Elapsed time (A0): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";
	$time_start_stats = getmicrotime();
}

	$varsel = "&venda_id=$venda_id";

	$msg = "";

	if(!$venda_id) $msg = "Código da venda não fornecido.\n";
	elseif(!is_numeric($venda_id)) $msg = "Código da venda inválido.\n";

	if($msg == ""){
		$vg_integracao_parceiro_origem_id = "";
		if($venda_id) {
			$b_integracao = @isVendaIntegracao($venda_id, $vg_integracao_parceiro_origem_id);
		}
	}
	if($msg == ""){
		$isVendaDeposito = isVendaDeposito($venda_id);
	}
	//Processa acoes
	//----------------------------------------------------------------------------------------------------------
	if($msg == ""){
	
		//Alterar Dados do pedido
		if(isset($acao) && $acao == "a"){
		
			if(!$v_campo || trim($v_campo) == ''){ 
				$msgAcao = "Item a ser alterado não especificado.\n";
			}
			
			if($msgAcao == ""){
				if($v_campo == 'email') {
					if(($v_valor_new) && ($venda_id)) {
						$sqlemail = "update tb_venda_games vg set vg_ex_email='".$v_valor_new."' where vg_id=".$venda_id;
						$rs_email = SQLexecuteQuery($sqlemail);
					}
				}
			}
		}

		if(isset($BtnCancelar) && $BtnCancelar){
			//atualiza status
			if($msg == ""){
				$parametros['usuario_obs'] = $usuario_obs;
				$parametros['ultimo_status_obs'] = $ultimo_status_obs;
				$msgConciliaUsuario = cancelaVendaGames($venda_id, $parametros);
			}
		}

		if(isset($BtnDescancelar) && $BtnDescancelar){
			//atualiza status
			if($msg == ""){
				$parametros['usuario_obs'] = $usuario_obs;
				$parametros['ultimo_status_obs'] = $ultimo_status_obs;
				$msgConcilia = descancelaVendaGames($venda_id, $parametros);
				if($msgConcilia == "") $msgConciliaUsuario .= "Descancelado com sucesso.\n";
				else  $msgConciliaUsuario .= "Descancelamento: " . $msgConcilia;
			}
		}

		if(isset($BtnConcilia) && $BtnConcilia){

			//Valida tipo de pagamento
			if(!$concilia_pagto_tipo) $msgConcilia = "Código de forma de pagamento não fornecido.\n";
			elseif(!is_numeric($concilia_pagto_tipo)) $msgConcilia = "Código de forma de pagamento inválido.\n";

			if( ($concilia_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']) || 
				($concilia_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'])) {
				//Valida codigo de deposito/boleto
				if(!$concilia_cod_sel) $msgConcilia = "Código selecionado não fornecido.\n";
				elseif(!is_numeric($concilia_cod_sel)) $msgConcilia = "Código selecionado inválido.\n";
			}

			//Testa vgm_pin_codinterno = null, caso contrário quer dizer que já houve uma venda (mesmo que o status mostre outra coisa)
			$vgm_pin_codinterno_tmp = get_pins_vendidos($venda_id);
			if(trim($vgm_pin_codinterno_tmp)!="") $msgConcilia = "Esta venda já tem PINs vendidos (PINs ID: '$vgm_pin_codinterno_tmp').\n";

			//Concilia
			if($msgConcilia == ""){
				$parametros['ultimo_status_obs'] = $ultimo_status_obs;
				if($concilia_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']){
					$msgConcilia = conciliaVendaGames_deposito($venda_id, $concilia_cod_sel, 1, $parametros);
				} elseif($concilia_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){
					$msgConcilia = conciliaVendaGames_boleto($venda_id, $concilia_cod_sel, 1, $parametros);
				} elseif($concilia_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD'] || $concilia_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS']){
					$msgConcilia = conciliaVendaGames_redecard($venda_id, $concilia_cod_sel, 1, $parametros);
				} elseif (  ($concilia_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || 
							($concilia_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || 
							($concilia_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) || 
							($concilia_pagto_tipo == $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']) ||
							($concilia_pagto_tipo == $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']) ||
							($concilia_pagto_tipo == $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']) ||
							($concilia_pagto_tipo == $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']) ||
							($concilia_pagto_tipo == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']) 
					) {


					$sql_pag = "select numcompra from tb_pag_compras pag where tipo_cliente = 'M' and idvenda = " . $venda_id. " order by datainicio desc limit 1;";
					$rs_pag = SQLexecuteQuery($sql_pag);
					if(!$rs_pag) {
						$msg_1 = date("Y-m-d H:i:s")." - Erro ao recuperar numcompra (1), venda_id: $venda_id).<br>\n";
						echo $msg_1;
					} else {
						$rs_pag_row = pg_fetch_array($rs_pag);

						$concilia_cod_sel = $rs_pag_row["numcompra"];
						$msgConcilia = conciliaVendaGames_PagamentoOnline($venda_id, $concilia_cod_sel, 1, $parametros);						
					}
				}
				if($msgConcilia == "") $msgConciliaUsuario .= "Conciliação: Conciliado com sucesso.\n";
				else  $msgConciliaUsuario .= "Conciliação: " . $msgConcilia;

				//Ativa o processamento da venda
				if($msgConcilia == "") $BtnProcessa = 1;
			}
		}
		if(isset($msgConcilia) && !($msgConcilia == "")){
			echo "<p class='texto' style='color:red'>msgConcilia: $msgConcilia</p>";
		}
		
		if(isset($BtnProcessa) && $BtnProcessa){
			//Associa pins, gera venda e credita saldo
			if($msgConcilia == ""){
				if($isVendaDeposito) {
					// processa Venda Depósito em Saldo por Boleto -> apenas muda status para 5

						$sql = "update tb_venda_games set 
vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "',
vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'], "") . "
where vg_id = " . $venda_id;
			//			if (  ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || 
			//						($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO'])) {
			//				gravaLog_TMP_conciliacao("VENDA GAMES (55).\n".$sql."\n");
			//			}
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao atualizar venda (Manual 1).\n";

				} elseif(!$b_integracao) {

					$parametros['ultimo_status_obs'] = $ultimo_status_obs;
					$msgConcilia = processaVendaGames($venda_id, 1, $parametros);
					if($msgConcilia == "") $msgConciliaUsuario .= "Processamento: Processado com sucesso.\n";
					else  $msgConciliaUsuario .= "Processamento: " . $msgConcilia;
				} else {

					// Ver functions_vendaGames.php:3677
//					echo "Processamento de venda Integração (Em construção - 2011-07-19)<br>";
					$parametros['ultimo_status_obs'] = $ultimo_status_obs;
					$msgConcilia = processaVendaGamesIntegracao($venda_id, 1, $parametros);
					if($msgConcilia == "") $msgConciliaUsuario .= "Processamento: Processado com sucesso (Manual - Integração).\n";
					else  $msgConciliaUsuario .= "Processamento (Manual - Integração): " . $msgConcilia;

					if($msgConcilia == "" && $venda_id ) {
						$iduser_bko = $_SESSION['iduser_bko'];

						$sql = "update tb_venda_games set 
vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "',
vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'], "") . "
where vg_id = " . $venda_id;
			//			if (  ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || 
			//						($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO'])) {
			//				gravaLog_TMP_conciliacao("VENDA GAMES (55).\n".$sql."\n");
			//			}
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao atualizar venda (Manual).\n";
					}

					if($msgConcilia == "" && $venda_id && $vg_integracao_parceiro_origem_id) {
						$url_notify_url = getPartner_param_By_ID('notify_url', $vg_integracao_parceiro_origem_id);
						$partner_do_notify = getPartner_param_By_ID('partner_do_notify', $vg_integracao_parceiro_origem_id);
						$s_msg = str_repeat("*", 80)."\n".(($partner_do_notify==1)?"VAI FAZER NOTIFY":"Sem notify")."\n";
						$s_msg .= "    vg_integracao_parceiro_origem_id: $vg_integracao_parceiro_origem_id\n    partner_do_notify: $partner_do_notify\n    url_notify_url: '$url_notify_url'\n";
grava_log_integracao_tmp(str_repeat("*", 80)."\nVai processar integração (manual):\n".$s_msg);
						if($partner_do_notify==1 && ($url_notify_url!="")) {

							// Monta o passo 4 da Integração - Notify partner
							$sql = "SELECT * FROM tb_integracao_pedido ip 
WHERE 1=1
and ip_store_id = '".$vg_integracao_parceiro_origem_id."'
and ip_vg_id = '".$venda_id."'";
grava_log_integracao_tmp(str_repeat("-", 80)."\nSelect  registro de integração para o notify (A2 manual)\n".$sql."\n");
//echo "$sql<br>";
							$rs = SQLexecuteQuery($sql);
							if(!$rs) {
								$msg_1 = date("Y-m-d H:i:s")." - Erro ao recuperar transação de integração (manual) (store_id: '".$vg_integracao_parceiro_origem_id."', vg_id: $vg_id).\n";
								echo $msg_1;
grava_log_integracao_tmp(str_repeat("-", 80)."\n".$msg_1);
							} else {
								$rs_row = pg_fetch_array($rs);

								$post_parameters = "store_id=".$rs_row["ip_store_id"]."&";

								$post_parameters .= "transaction_id=".$rs_row["ip_transaction_id"]."&";
								$post_parameters .= "order_id=".$rs_row["ip_order_id"]."&";
								$post_parameters .= "amount=".$rs_row["ip_amount"]."&";
								if(strlen($rs_row["ip_product_id"])>0) {
									$post_parameters .= "product_id=".$rs_row["ip_product_id"]."&";
								}
								$post_parameters .= "client_email=".$rs_row["ip_client_email"]."&";
								$post_parameters .= "client_id=".$rs_row["ip_client_id"]."&";

								$post_parameters .= "currency_code=".$rs_row["ip_currency_code"]."";

								$sret1 = getIntegracaoCURL($url_notify_url, $post_parameters);
//										$sret = substr($sret1,strpos($sret1,"Content-type: text/html")+strlen("Content-type: text/html"));
								$sret = $sret1;

								$s_msg = "AFTER Partner Notify - Conciliacao Automatica de Pagamento Online (m) (".date("Y-m-d H:i:s").")\n - result: \n" . str_repeat("_", 80) . "\n" . $sret . "\n".str_repeat("-", 80)."\n";
grava_log_integracao_tmp(str_repeat("*", 80)."\n"."Retorno de getIntegracaoCURL (2 manual): \n".print_r($post_parameters,true)."\n".$s_msg."\n");
//echo "$s_msg <br>";
							}
						}
					}

				}				
				//Ativa o processamento de envio de email da venda
				if($msgConcilia == "" && (!$b_integracao)) $BtnProcessaEmail = 1;
				
			}
		}

		if((isset($BtnProcessaEmail) && $BtnProcessaEmail) && (!$b_integracao)){
			//envia email para o cliente
			if($msgConcilia == ""){
				$parametros['ultimo_status_obs'] = $ultimo_status_obs;
				$parametros['PROCESS_TEST'] = 1;
				$msgConcilia = processaEmailVendaGames($venda_id, $parametros);
				if($msgConcilia == "") {
					$msgConciliaUsuario .= "Envio de email: Enviado com sucesso.\n";	
				} else { 
					$msgConciliaUsuario .= "Envio de email: " . $msgConcilia;
				}
			}
		}
	}

if($bDebug) echo "Elapsed time (A0): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

	//Mostra a pagina
	//----------------------------------------------------------------------------------------------------------
	//Recupera a venda
	if($msg == ""){
		$sql  = "select vg.*, pag.* \n";
		$sql .= ", case when (not pag_tipo='') then pag_valor else (bol_valor-".((($GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL']-$GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2'])>0)?($GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL']-$GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2']):$GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2']).") end as valor \n";
//		$sql .= "coalesce(idvenda_origem, 0) as idvenda_origem\n";
		$sql .= "from tb_venda_games vg \n";
		$sql .= "inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id 
					left outer join ( 
							select idvenda as pag_vg_id, (total/100-taxas) as pag_valor, iforma as pag_tipo, 
								substr(cesta,6) as pag_cesta, tipo_deposito as pag_tipo_deposito, idpagto
							from tb_pag_compras pg 
							where (pg.tipo_cliente = 'M') 
							) pag on pag.pag_vg_id = vg.vg_id

						left outer join ( 
							select bol_codigo, bol_venda_games_id as bol_vg_id, bol_valor as bol_valor, 'B' || substr(bol_documento, 1, 1) as bol_tipo, 'Boleto Depósito em Saldo Gamer' as bol_cesta 
							from boletos_pendentes bol 
							where ( (substr(bol_documento, 1, 1)='2') or (substr(bol_documento, 1, 1)='3') or (substr(bol_documento, 1, 1)='6'))	
							) bol on bol.bol_vg_id = vg.vg_id 	

				\n";
		$sql .= "where vg.vg_id = " . $venda_id;
		$rs_venda = SQLexecuteQuery($sql);
		if(!$rs_venda || pg_num_rows($rs_venda) == 0) $msg = "Nenhuma venda encontrada.\n";
			$rs_venda_row = pg_fetch_array($rs_venda);
			$vg_ug_id 				= $rs_venda_row['vg_ug_id'];
			$vg_ultimo_status 		= $rs_venda_row['vg_ultimo_status'];
			$vg_ultimo_status_obs 	= $rs_venda_row['vg_ultimo_status_obs'];
			$vg_usuario_obs 		= $rs_venda_row['vg_usuario_obs'];
			$vg_pagto_tipo 			= $rs_venda_row['vg_pagto_tipo'];
			$vg_data_inclusao 		= $rs_venda_row['vg_data_inclusao'];
			$vg_pagto_data_inclusao = $rs_venda_row['vg_pagto_data_inclusao'];
			$vg_pagto_data 			= $rs_venda_row['vg_pagto_data'];
			$vg_pagto_banco 		= $rs_venda_row['vg_pagto_banco'];
			$vg_pagto_local 		= $rs_venda_row['vg_pagto_local'];
			$vg_pagto_valor_pago 	= $rs_venda_row['vg_pagto_valor_pago'];
			$vg_pagto_num_docto 	= $rs_venda_row['vg_pagto_num_docto'];
			$vg_concilia 			= $rs_venda_row['vg_concilia'];
			$vg_data_concilia 		= $rs_venda_row['vg_data_concilia'];
			$vg_user_id_concilia 	= trim($rs_venda_row['vg_user_id_concilia']);
			$vg_dep_codigo 			= $rs_venda_row['vg_dep_codigo'];
			$vg_bol_codigo 			= $rs_venda_row['vg_bol_codigo'];
			$vg_ex_email 			= $rs_venda_row['vg_ex_email'];
			$valor		 			= $rs_venda_row['valor'];
			$vg_deposito_em_saldo 	= $rs_venda_row['vg_deposito_em_saldo'];
			$vg_deposito_em_saldo_valor 	= $rs_venda_row['vg_deposito_em_saldo_valor'];
			$vg_valor_eppcash		= $rs_venda_row['vg_valor_eppcash']; 

			$pag_tipo_deposito		= $rs_venda_row['pag_tipo_deposito']; 
			$idpagto				= $rs_venda_row['idpagto']; 

 			$pagto_num_docto 	 = preg_split("/\|/", $vg_pagto_num_docto);

	}
	
if($bDebug) echo "Elapsed time (A1): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

	//Recupera modelos
	if($msg == "" && $vg_deposito_em_saldo!="1"){
		$sql  = "select * from tb_venda_games vg " .
				"inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
				"where vg.vg_id = " . $venda_id;
		$rs_venda_modelos = SQLexecuteQuery($sql);
		if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) $msg = "Nenhum produto encontrado (ND43).\n";
		else {
			$total_geral = 0; $qtde_itens = 0; $qtde_produtos = 0; $vgm_pin_codinterno = "";
			while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)){
				$qtde = $rs_venda_modelos_row['vgm_qtde'];
				$valor = $rs_venda_modelos_row['vgm_valor'];
				$vgm_pin_codinterno .= $rs_venda_modelos_row['vgm_pin_codinterno'];
				$total_geral += $valor*$qtde;
				$qtde_itens += $qtde;
				$qtde_produtos += 1;
			}
			$vgm_pin_codinterno = str_replace(",", ", ", $vgm_pin_codinterno);

			pg_result_seek($rs_venda_modelos, 0); 
		}
	} else {
		$total_geral = $valor;
		$qtde_itens = 1;
		$qtde_produtos = 1;
	}

if($bDebug) echo "Elapsed time (A2): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";
	if(isset($BtnListaHistorico) && $BtnListaHistorico) {
		//Recupera historico da venda
		if($msg == ""){
			// foi criado um indice para vgh_id => ordenar por vgh_id e não por vgh_data_inclusao
			// mais rápido por ~350 vezes 
			$sql  = "select * from tb_venda_games_historico vgh 
					 where vgh.vgh_vg_id = " . $venda_id . "
					 order by vgh.vgh_id desc";  
					 // "vgh_data_inclusao desc";
			$rs_venda_hist = SQLexecuteQuery($sql);

		}
	}

//echo "$sql<br>";
if($bDebug) echo "Elapsed time (A2a): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

	//Recupera dados do usuario
	if($msg == ""){
		$sql  = "select * from usuarios_games ug " .
				"where ug.ug_id = " . $vg_ug_id;
		$rs_usuario = SQLexecuteQuery($sql);
		if(!$rs_usuario || pg_num_rows($rs_usuario) == 0) $msg = "Nenhum cliente encontrado.\n";
		else {
			$rs_usuario_row = pg_fetch_array($rs_usuario);
			$ug_email = $rs_usuario_row['ug_email'];
			$ug_nome = $rs_usuario_row['ug_nome'];
			$ug_cpf = $rs_usuario_row['ug_cpf'];
			$ug_rg = $rs_usuario_row['ug_rg'];
			$ug_cidade = $rs_usuario_row['ug_cidade'];
			$ug_estado = $rs_usuario_row['ug_estado'];
			$ug_tel_ddi = $rs_usuario_row['ug_tel_ddi'];
			$ug_tel_ddd = $rs_usuario_row['ug_tel_ddd'];
			$ug_tel = $rs_usuario_row['ug_tel'];
			$ug_cel_ddi = $rs_usuario_row['ug_cel_ddi'];
			$ug_cel_ddd = $rs_usuario_row['ug_cel_ddd'];
			$ug_cel = $rs_usuario_row['ug_cel'];
		}
	}

if($bDebug) echo "Elapsed time (A3): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

	//Recupera dados da forma de pagamento
	if($msg == ""){

		if($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){
			$sql  = "select * from boleto_bancario_games bbg " .
					"where bbg.bbg_vg_id = " . $venda_id;
			$rs_boleto = SQLexecuteQuery($sql);
			if(!$rs_boleto || pg_num_rows($rs_boleto) == 0) $msg = "Nenhum boleto encontrado.\n";
			else {
				$rs_boleto_row = pg_fetch_array($rs_boleto);
				$bbg_boleto_codigo = $rs_boleto_row['bbg_boleto_codigo'];
				$bbg_data_inclusao = $rs_boleto_row['bbg_data_inclusao'];
				$bbg_bco_codigo = $rs_boleto_row['bbg_bco_codigo'];
				$bbg_documento = $rs_boleto_row['bbg_documento'];
				$bbg_valor = $rs_boleto_row['bbg_valor'];
				$bbg_valor_taxa = $rs_boleto_row['bbg_valor_taxa'];
				$bbg_data_venc = $rs_boleto_row['bbg_data_venc'];
				$bbg_data_pago = $rs_boleto_row['bbg_data_pago'];
				$bbg_pago = $rs_boleto_row['bbg_pago'];
			}

		} elseif((isset($GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD']) && $vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD']) || (isset($GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS']) && $vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS'])){
			$sql  = "select * from tb_venda_games_redecard vgrc " .
					"where vgrc.vgrc_vg_id = " . $venda_id;
			$rs_redecard = SQLexecuteQuery($sql);
			if(!$rs_redecard || pg_num_rows($rs_redecard) == 0) $msg = "Nenhum redecard encontrado.\n";
			else {
				$rs_redecard_row = pg_fetch_array($rs_redecard);
				$vgrc_id = $rs_redecard_row['vgrc_id'];
				$vgrc_vg_id = $rs_redecard_row['vgrc_vg_id'];
				$vgrc_ug_id = $rs_redecard_row['vgrc_ug_id'];
				$vgrc_parcelas = $rs_redecard_row['vgrc_parcelas'];
				$vgrc_data_inclusao = $rs_redecard_row['vgrc_data_inclusao'];
				$vgrc_total = $rs_redecard_row['vgrc_total'];
				$vgrc_transacao = $rs_redecard_row['vgrc_transacao'];
				$vgrc_bandeira = $rs_redecard_row['vgrc_bandeira'];
				$vgrc_codver = $rs_redecard_row['vgrc_codver'];
				$vgrc_data_envio1 = $rs_redecard_row['vgrc_data_envio1'];
				$vgrc_ret2_data = $rs_redecard_row['vgrc_ret2_data'];
				$vgrc_ret2_nr_cartao = $rs_redecard_row['vgrc_ret2_nr_cartao'];
				$vgrc_ret2_origem_bin = $rs_redecard_row['vgrc_ret2_origem_bin'];
				$vgrc_ret2_numautor = $rs_redecard_row['vgrc_ret2_numautor'];
				$vgrc_ret2_numcv = $rs_redecard_row['vgrc_ret2_numcv'];
				$vgrc_ret2_numautent = $rs_redecard_row['vgrc_ret2_numautent'];
				$vgrc_ret2_numsqn = $rs_redecard_row['vgrc_ret2_numsqn'];
				$vgrc_ret2_codret = $rs_redecard_row['vgrc_ret2_codret'];
				$vgrc_ret2_msgret = $rs_redecard_row['vgrc_ret2_msgret'];
				$vgrc_ret4_ret = $rs_redecard_row['vgrc_ret4_ret'];
				$vgrc_ret4_codret = $rs_redecard_row['vgrc_ret4_codret'];
				$vgrc_ret4_msgret = $rs_redecard_row['vgrc_ret4_msgret'];
				$vgrc_usuario_ip = $rs_redecard_row['vgrc_usuario_ip'];
				$vgrc_ret2_endereco = $rs_redecard_row['vgrc_ret2_endereco'];
				$vgrc_ret2_numero = $rs_redecard_row['vgrc_ret2_numero'];
				$vgrc_ret2_complemento = $rs_redecard_row['vgrc_ret2_complemento'];
				$vgrc_ret2_cep = $rs_redecard_row['vgrc_ret2_cep'];
				$vgrc_ret2_respavs = $rs_redecard_row['vgrc_ret2_respavs'];
				$vgrc_ret2_msgavs = $rs_redecard_row['vgrc_ret2_msgavs'];
				
				$vgrc_ret2_numprg = $rs_redecard_row['vgrc_ret2_numprg'];
				$vgrc_ret2_nr_hash_cartao = $rs_redecard_row['vgrc_ret2_nr_hash_cartao'];
				$vgrc_ret2_cod_banco = $rs_redecard_row['vgrc_ret2_cod_banco'];
			}
		
		}
	}

if($bDebug) echo "Elapsed time (A4): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

	//Se conciliado, Recupera dados do usuario que conciliou
	if($msg == ""){

		if($vg_concilia == 1){
		
			if($vg_user_id_concilia == ""){
				$shn_nome = "Anonymous";
			} else {
				$sql  = "select * from usuarios urpp " .
						"where urpp.id = '" . $vg_user_id_concilia . "'";
				$rs_urpp = SQLexecuteQuery($sql);
				if(!$rs_urpp || pg_num_rows($rs_urpp) == 0){
					$shn_nome = "Anonymous";
				} else {
					$rs_urpp_row = pg_fetch_array($rs_urpp);
					$shn_nome = $rs_urpp_row['shn_nome'];
				}
			}
		}
	}

if($bDebug) echo "Elapsed time (A5): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";
    $msgConciliaUsuario = (isset($msgConciliaUsuario)) ? $msgConciliaUsuario : "";
	$msg = $msgConciliaUsuario . $msg;

	$cor1 = "#F5F5FB";
	$cor2 = "#F5F5FB";
	$cor3 = "#FFFFFF"; 	


ob_end_flush();
?>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
            <li class="active">Venda</li>
        </ol>
    </div>		
	<?php if($msg != ""){?>
        <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr><td align="center"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"><?php echo str_replace("\n", "<br>", $msg) ?></font></td></tr>
		</table>
	<?php }?>
        <table class="txt-preto fontsize-pp table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Venda</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>C&oacute;digo</b></td>
            <td><?php echo $venda_id ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Data</b></td>
            <td><?php echo (($vg_data_inclusao)?formata_data_ts($vg_data_inclusao, 0, true, true):"<font color='red'>Sem data de cadastro</font>") ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Tipo venda</b></td>
            <td title="<?php echo "vg_deposito_em_saldo: ".$vg_deposito_em_saldo. ", pag_tipo_deposito: ". $pag_tipo_deposito . " (".$GLOBALS['TIPO_DEPOSITO_LEGENDA'][$pag_tipo_deposito].")"?>"><?php 	if($vg_deposito_em_saldo=="1"){  ?><font color="darkgreen">Depósito em Saldo</font><?php } else { ?> <font color="blue">Venda de PINs</font><?php } ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Valor</b></td>
            <td><?php 	if($vg_deposito_em_saldo=="1") { echo number_format($vg_deposito_em_saldo_valor, 2, ',', '.');  } else { echo number_format($total_geral, 2, ',', '.'); } ?></td>
          </tr>
		  <?php if($vg_deposito_em_saldo=="1") {	?>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Valor EPP Cash</b></td>
            <td><?php 	if($vg_deposito_em_saldo=="1") { echo number_format($vg_valor_eppcash, 0, ',', '.');  }  ?></td>
          </tr>
		  <?php }  ?>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Qtde Produtos</b></td>
            <td><?php echo number_format($qtde_produtos, 0, '', '.') ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Qtde Itens</b></td>
            <td><?php echo number_format($qtde_itens, 0, '', '.') ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Status</b></td>
			<?php if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA']){?>
			<td title="<?php echo "vg_ultimo_status: '".$vg_ultimo_status."'"; ?>"><font color="FF0000"><?php echo $GLOBALS['STATUS_VENDA_DESCRICAO'][$vg_ultimo_status] ?></font></td>
			<?php } else {?>
			<td title="<?php echo  "vg_ultimo_status: '".$vg_ultimo_status."'"; ?>"><?php echo $GLOBALS['STATUS_VENDA_DESCRICAO'][$vg_ultimo_status] ?></td>
			<?php } ?>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>PINs vendidos</b></td>
            <td><?php 
					if(!$b_integracao) {
						echo $vgm_pin_codinterno." (".((trim($vgm_pin_codinterno)=="")?"<font color='#0000FF'>Sem PINs vendidos</font>":"<font color='#FF0000'>ATENÇÃO: Já tem PINs vendidos</font>").")";
					} else {
						echo "<font color='#0000FF'>Venda Integração store_id: '$vg_integracao_parceiro_origem_id'</font> (não usa PINs)";
					}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Observações</b></td>
			<td><?php echo str_replace("\n", "<br>", $vg_ultimo_status_obs) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Observações ao usuário</b></td>
			<td><?php echo str_replace("\n", "<br>", $vg_usuario_obs) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Conciliação</b></td>
			<td><?php echo ($vg_concilia==1?"Conciliado":"<font color='red'>Não conciliado</font>").(($vg_ultimo_status==5 && $vg_concilia!=1)?" (<font color='red'>ERROR: vg_ultimo_status: '$vg_ultimo_status' mas vg_concilia: '$vg_concilia'</font>)":"") ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Data Conciliação</b></td>
			<td><?php echo ($vg_data_concilia!=""?$vg_data_concilia:"<font color='red'>Sem data de conciliação</font>").(($vg_ultimo_status==5 && $vg_data_concilia=="")?" (<font color='red'>ERROR: vg_ultimo_status: '$vg_ultimo_status' mas não tem vg_data_concilia</font>)":"") ?></td>
          </tr>
		</table>

		<?php 	
			if($msg == "" && $vg_deposito_em_saldo!="1") {  
		?>
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Produtos</font></td>
          </tr>
          <tr>
		  	<td>
                <table class="table txt-preto fontsize-pp">
					<tr bgcolor="F0F0F0" class="texto">
					  <td align="center"><b>Produto</b></td>
					  <td align="center"><b>Quantidade</b></td>
					  <td align="right"><b>Preço Unitário</b></td>
					  <td align="right"><b>Preço Total</b></td>
					</tr>
		<?php
				$total_geral = 0;
				while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)){
					$qtde = $rs_venda_modelos_row['vgm_qtde'];
					$valor = $rs_venda_modelos_row['vgm_valor'];
					$total_geral += $valor*$qtde;

					// Alawar 
					//	$prod_Alawar = 106;
					//	$prod_mod_Alawar = 459;
					$codeProd = 0;
					if(	$rs_venda_modelos_row['vgm_ogp_id'] == $GLOBALS['prod_Alawar'] && $rs_venda_modelos_row['vgm_ogpm_id'] == $GLOBALS['prod_mod_Alawar'] ) {
						$codeProd = $rs_venda_modelos_row['vgm_game_id_alawar'];
					}
		?>
					<tr class="texto" bgcolor="#F5F5FB">
					  <td width="200">
						&nbsp;&nbsp;<nobr><?php echo $rs_venda_modelos_row['vgm_nome_produto']?><?php if($rs_venda_modelos_row['vgm_nome_modelo']!=""){?> - <?php echo $rs_venda_modelos_row['vgm_nome_modelo']?><?php } ?><?php 
							if($codeProd!="") echo " (ID_ALAWAR: ".$codeProd.")";  
						?></nobr>
					  </td>
					  <td align="center"><?php echo $qtde?></td>
					  <td align="right"><?php echo number_format($valor, 2, ',', '.')?></td>
					  <td align="right"><?php echo number_format($valor*$qtde, 2, ',', '.')?></td>
					</tr>
			<?php	} ?>
					<tr bgcolor="F0F0F0" class="texto">
					  <td colspan="2">&nbsp;</td>
					  <td align="right"><b>Total</b></td>
					  <td align="right"><b><?php echo number_format($total_geral, 2, ',', '.')?></b></td>
					</tr>
				</table>
			</td>
		  </tr>
		</table>
		<?php 	
		} 
		?>

<?php 
	if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
?>
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Pins</font></td>
          </tr>
          <tr>
		  	<td>
                <table class="table txt-preto fontsize-pp">
		<?php
//echo $opr_codigo_Alawar."<br>";
            if(isset($rs_venda_modelos) && $rs_venda_modelos){
                pg_result_seek($rs_venda_modelos, 0);
				while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)){
					$vgm_pin_codinterno = $rs_venda_modelos_row['vgm_pin_codinterno'];
					$vgm_opr_codigo = $rs_venda_modelos_row['vgm_opr_codigo'];
					$vgm_valor = $rs_venda_modelos_row['vgm_valor'];

					//Formatação					
					$labSenha = "Senha";
					if($vgm_opr_codigo == 16) $labSenha = "Habbo Crédito";
					if($vgm_opr_codigo == $GLOBALS['opr_codigo_Alawar']) $labSenha = "Certificate";
		?>
					<tr bgcolor="F0F0F0" class="texto">
					  <td width="200">
						&nbsp;&nbsp;
						<?php echo $rs_venda_modelos_row['vgm_nome_produto']?> 
						<?php if($rs_venda_modelos_row['vgm_nome_modelo']!=""){?> - <?php echo $rs_venda_modelos_row['vgm_nome_modelo']?><?php } ?>
					  </td>
					  <td align="center"><?php echo number_format($vgm_valor, 2, ',', '.') ?></td>
					  <td align="center"><b>PIN cod_interno</b></td>
					  <td align="center"><b>No Série</b></td>
					  <td align="center"><b><?php echo $labSenha ?></b></td>
					  <?php if($vgm_opr_codigo==$OPR_CODIGO_EPP) { ?>
						  <td align="center">EPP</b></td>
					  <?php } ?>
					</tr>
		<?php
					//elimina ultima virgula
					if(substr($vgm_pin_codinterno, -1) == ",") $vgm_pin_codinterno = substr($vgm_pin_codinterno, 0, strlen($vgm_pin_codinterno) - 1);

					//separa os ids dos pins
					$vgm_pin_codinternoAr = preg_split("/,/", $vgm_pin_codinterno);
				
					//verifica se o(s) pin(s) foram associados ao modelo
					if(count($vgm_pin_codinternoAr) > 0){

						//Realiza n qtde de venda de pins
						for($i=0; $i < count($vgm_pin_codinternoAr); $i++){
						 
							// Executa uma verificação se o a senha do pin é zerada, se for exibe o campo pin_caracter	
							$sql = "select *, 
										CASE WHEN pin_codigo = '0000000000000000' THEN pin_caracter
										ELSE pin_codigo
										END as case_serial
								from pins
								where pin_codinterno = '" . ((strlen($vgm_pin_codinternoAr[$i])>0)?$vgm_pin_codinternoAr[$i]:0) . "'";
							 
							$rs_pin = SQLexecuteQuery($sql);
							if(!$rs_pin || pg_num_rows($rs_pin) == 0) $msg = "PIN não encontrado.\n";
							else {
								$pgpin = pg_fetch_array($rs_pin);
								$pin_codinterno = $pgpin['pin_codinterno'];
								$pin_serial = $pgpin['pin_serial'];
								$case_serial = $pgpin['case_serial'];
								$opr_codigo = $pgpin['opr_codigo'];

								$epp_status = (($opr_codigo==$OPR_CODIGO_EPP)?retorna_status($case_serial):"-");

								$sAlawar_Activation_key = "";
								if($opr_codigo==$GLOBALS['opr_codigo_Alawar']) {
									//	select pa_data_transacao, pa_activation_key, pa_pag_id, * from pins_alawar where pa_certificate_id = '1256704180550'
									$sql2 = "select * from pins_alawar where pa_certificate_id = '$case_serial';";
									$rs_pin_alawar = SQLexecuteQuery($sql2);
									if(!$rs_pin_alawar || pg_num_rows($rs_pin_alawar) == 0) $msg = "Activation key Alawar não encontrado.\n";
									else {
										$pgpin_alawar = pg_fetch_array($rs_pin_alawar);
										$pa_data_transacao = $pgpin_alawar['pa_data_transacao'];
										$pa_activation_key = $pgpin_alawar['pa_activation_key'];
										$pa_pag_id = $pgpin_alawar['pa_pag_id'];
										$sAlawar_Activation_key = "<table border='0' width='100%' cellpadding='2' cellspacing='0'>";
										$sAlawar_Activation_key .= "<tr class='texto' bgcolor='#CCCCCC'>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td align='center'>data</td>
<td align='center'>activation_key</td>
<td align='center'>id jogo</td>
</tr>
";
										$sAlawar_Activation_key .= "<tr class='texto' bgcolor='#CCCCCC'>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td align='center'>". $pa_data_transacao ."</td>
<td align='center'>". $pa_activation_key ."</td>
<td align='center'>". $pa_pag_id ."</td>
</tr>
";
										$sAlawar_Activation_key .= "</table>";

									}
								}
		
								//Formatacao
								if($vgm_opr_codigo == 13) $case_serial = wordwrap($case_serial, 4, " ", true);
							}
		?>
					<tr class="texto" bgcolor="#F5F5FB">
					  <td width="200">&nbsp;</td>
					  <td>&nbsp;</td>
					  <td align="center"><?php echo $pin_codinterno ?></td>
					  <td align="center"><?php echo $pin_serial ?></td>
					  <td align="center"><?php echo $case_serial ?></td>
					  <?php if($opr_codigo==$OPR_CODIGO_EPP) { ?>
						  <td align="center"><?php echo $PINS_STORE_STATUS[$epp_status]." (".$epp_status.")" ?></td>
					  <?php } ?>

					</tr>
		<?php	
							if($sAlawar_Activation_key) {
								echo "<tr class='texto' bgcolor='#F5F5FB'>
<td>&nbsp;</td>
<td>&nbsp;Activation key Alawar</td>
<td align='center' colspan='3'>$sAlawar_Activation_key</td>
</tr>";	
							}
						}						
					}
				} 
		
            }
        ?>
				</table>
			</td>
		  </tr>
		</table>
<?php	
	}  
?>

		<form name="form1" method="post" action="com_venda_detalhe.php">
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td bgcolor="#ECE9D8">
                <table class="table txt-preto fontsize-pp">
					<tr bgcolor="#FFFFFF" class="texto"> 
						<td bgcolor="#ECE9D8" align="left">Histórico</td>
						<td bgcolor="#ECE9D8" align="right"><input type="submit" name="BtnListaHistorico" value="Mostrar Histórico" class="btn btn-info btn-sm"></td>
					</tr>
				</table>
			</td>
          </tr>
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td>
                <table class="table txt-preto fontsize-pp">
						<tr bgcolor="#ECE9D8"> 
						  <td align="center" width="150">Data</td>
						  <td align="center" width="250">Status</td>
						  <td align="center" width="494">Observações</td>
						</tr>
				<?php if(isset($BtnListaHistorico) && $BtnListaHistorico) {
				      if($rs_venda_hist && pg_num_rows($rs_venda_hist) > 0){?>
					<?php	while ($rs_venda_hist_row = pg_fetch_array($rs_venda_hist)) {
							if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} ?>
							<tr bgcolor="<?php echo $cor1 ?>"> 
							  <td align="center"><?php echo formata_data_ts($rs_venda_hist_row['vgh_data_inclusao'], 0, true, true) ?></td>
							  <?php $vgh_status = $rs_venda_hist_row['vgh_status'];?>
							  <?php $statusNome = $GLOBALS['STATUS_VENDA_DESCRICAO'][$vgh_status]; ?>
							  <td><?php echo substr($statusNome, 0, strpos($statusNome, '.')) ?></td>
							  <td><?php echo str_replace("\n", "<br>", $rs_venda_hist_row['vgh_status_obs']) ?></td>
							</tr>
					<?php	} ?>
				<?php    }
				   }

				?>
					  </table>

			</td>
          </tr>
		</table>


        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8" class="texto">Usuário</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>C&oacute;digo</b></td>
            <td><a style="text-decoration:none" href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $vg_ug_id ?>"><?php echo $vg_ug_id ?></a></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Nome</b></td>
            <td><a style="text-decoration:none" href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $vg_ug_id ?>"><?php echo $ug_nome ?></a></td>
		  </tr>
	<?php if($vg_ug_id == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']) { ?>
          <tr bgcolor="#F5F5FB"> 
           <td><a class="link_azul" href="#" Onclick="if(confirm('Deseja alterar o Email deste pedido ?')) window.open('com_venda_detalhe_selecao.php?v_campo=email&email=<?php echo $vg_ex_email ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Email</b></a></td>  
            <td><?php echo $vg_ex_email ?></td>
		  </tr>
	
	<?php } else {?>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Email</b></td>
            <td><?php echo $ug_email ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>CPF</b></td>
            <td><?php echo $ug_cpf ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>RG</b></td>
            <td><?php echo $ug_rg ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Telefone</b></td>
            <td>(<?php echo $ug_tel_ddi ?>) (<?php echo $ug_tel_ddd ?>) <?php echo $ug_tel ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Celular</b></td>
            <td>(<?php echo $ug_cel_ddi ?>) (<?php echo $ug_cel_ddd ?>) <?php echo $ug_cel ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Cidade</b></td>
            <td><?php echo $ug_cidade ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Estado</b></td>
            <td><?php echo $ug_estado ?></td>
		  </tr>
	<?php } ?>
		</table>
			
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Dados do Pagamento</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Forma de Pagamento</b></td>
	<?php 
		$spagtos = "";
		$b_has_gocash = false;
		$b_has_eppcash = false;
		$b_has_saldo = false;
		$b_is_eppcash = b_Is_vg_pagto_tipo_EPP_Cash($vg_pagto_tipo);	// Apenas testa a constante de tipo
		if($b_is_eppcash) {
			/*
				get_pagto_epp_part_gocash($venda_id, &$apagtos)
				$apagtos[]
					'valorpagtopin'
					'valorpagtosaldo'
					'valorpagtogocash'
					'valortotal'
					'taxas'
				retorna true se ('valorpagtogocash'>0)

			*/
			$b_is_gocash = get_pagto_epp_part_gocash($venda_id, $apagtos);
			$b_has_eppcash = ($apagtos['valorpagtopin']>0);
			$b_has_saldo = ($apagtos['valorpagtosaldo']>0);

			$spagtos  = "<table class=\"table txt-preto fontsize-pp\">\n";
			$spagtos .= "<tr align='center' class='texto'><td>EPPCash</td><td>Saldo</td><td>GoCash</td><td>&nbsp;</td><td>Total</td></tr>\n";
			$spagtos .= "<tr align='center' class='texto'>";
			$spagtos .= "<td".(($apagtos['valorpagtopin']>0)?" style='color:blue'":"").">".number_format($apagtos['valorpagtopin'], 2, ',', '.')."</td>";
			$spagtos .= "<td".(($apagtos['valorpagtosaldo']>0)?" style='color:blue'":"").">".number_format($apagtos['valorpagtosaldo'], 2, ',', '.')."</td>";
			$spagtos .= "<td".(($apagtos['valorpagtogocash']>0)?" style='color:blue'":"").">".number_format($apagtos['valorpagtogocash'], 2, ',', '.')."</td>";
			$spagtos .= "<td>&nbsp;</td>";
	//		$spagtos .= "<td>".number_format($apagtos['taxas']."</td>";
			$spagtos .= "<td".(($apagtos['valortotal']>0)?" style='color:blue'":"").">".number_format($apagtos['valortotal'], 2, ',', '.')."</td>";
			$spagtos .= "</tr>\n";
			$spagtos .= "</table>\n";

			if($vg_deposito_em_saldo=="1"){
				// encontra a venda que deu origem a este depósito
				$msg_deposito = "";
				$sql_d  = "select * from tb_pag_compras where tipo_cliente = 'M' and idvenda = " . $venda_id;
				$rs_dep = SQLexecuteQuery($sql_d);
				if(!$rs_dep || pg_num_rows($rs_dep) == 0) {
					$msg = "Pagamento depósito não encontrado.\n";
				} else {
					$rs_dep_row = pg_fetch_array($rs_dep);
					$idvenda_origem = $rs_dep_row['idvenda_origem'];
					if($idvenda_origem>0) {
						$msg_origem  = "<table  class=\"table txt-preto fontsize-pp\">\n";
						$msg_origem .= "<tr align='center' class='texto'>";
						$msg_origem .= "<td style='color:darkgreen'>"."Esta venda de depósito de resto no Saldo do usuário foi gerada pela venda <a href='com_venda_detalhe.php?venda_id=$idvenda_origem' target='_blank'>$idvenda_origem</a><br>"."</td>";
						$msg_origem .= "</tr>\n";
						$msg_origem .= "</table>\n";
					} else {
					}
				}

			} else {
				// tem algum pagamento de depósito do resto em saldo onde esta venda é a origem?
				$msg_deposito = "";
				$sql_p  = "select * from tb_pag_compras where tipo_cliente = 'M' and idvenda_origem = " . $venda_id;
				$rs_pag = SQLexecuteQuery($sql_p);
				if(!$rs_pag || pg_num_rows($rs_pag) == 0) {
					$msg = "Pagamento não encontrado.\n";
				} else {
					$rs_pag_row = pg_fetch_array($rs_pag);
					$idvenda_deposito = $rs_pag_row['idvenda'];
					if($idvenda_deposito>0) {
						$msg_deposito  = "<table class=\"table txt-preto fontsize-pp\">\n";
						$msg_deposito .= "<tr align='center' class='texto'>\n";
						$msg_deposito .= "<td style='color:blue'>"."O pagamento desta venda gerou resto que foi depositado no Saldo do usuário com a venda <a href='com_venda_detalhe.php?venda_id=$idvenda_deposito' target='_blank'>$idvenda_deposito</a><br>"."</td>";
						$msg_deposito .= "</tr>\n";
						$msg_deposito .= "</table>\n";
					}
				}
			} 
		}
	?>
            <td><?php echo $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][getCodigoCaracterParaPagto($vg_pagto_tipo)]." (".$vg_pagto_tipo." -&gt; '".getCodigoCaracterParaPagto($vg_pagto_tipo)."')".(($b_is_eppcash)?"&nbsp;$spagtos":"") ?><?php
				if(isset($msg_deposito) && $msg_deposito) {
					echo "$msg_deposito";
				}
				if(isset($msg_origem) && $msg_origem) {
					echo "$msg_origem";
				}
			?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Banco</b></td>
            <td><?php if(trim($vg_pagto_banco)) echo $vg_pagto_banco; 
			//echo getBancoCodigo($vg_pagto_banco)." ('$vg_pagto_banco')" 
			?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>ID_pagto</b></td>
            <td><?php echo $idpagto; ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Data Inclusão</b></td>
            <td><?php if($vg_pagto_data_inclusao) echo formata_data_ts($vg_pagto_data_inclusao, 0, true, true) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Local</b></td>
            <td><?php if(trim($vg_pagto_banco) && trim($vg_pagto_local)) echo $GLOBALS['PAGTO_LOCAIS'][$vg_pagto_banco][$vg_pagto_local] ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Data Informada</b></td>
            <td><?php if($vg_pagto_data) echo formata_data_ts($vg_pagto_data, 0, false, false) ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB">
            <td valign="top"><b>Valor Pago</b></td>
            <td>
				<?php if($vg_pagto_valor_pago) echo number_format($vg_pagto_valor_pago, 2, ',', '.') ?><br>
				<?php if($vg_pagto_valor_pago != $total_geral){?><font color="FF0000">Atenção!!! Valor informado pelo usuário é diferente do valor da compra.<br>Certifique-se de que o pedido está correto. (<?php echo "vg_pagto_valor_pago: ".number_format($vg_pagto_valor_pago, 2, ',', '.').", vg_pagto_valor_pago: ".number_format($total_geral, 2, ',', '.').""?>)</font><?php 
					$vg_pagto_valor_pago = $total_geral;
				} ?>
			</td>
		  </tr>
		  <?php
          if(trim($vg_pagto_banco) && trim($vg_pagto_local)){
            $pagto_nome_docto_Ar = explode(";", $PAGTO_NOME_DOCTO[$vg_pagto_banco][$vg_pagto_local]);
            for($i=0; $i<count($pagto_nome_docto_Ar); $i++){
		  ?>
		  <tr bgcolor="#F5F5FB">
            <td><b><?php echo (trim($pagto_nome_docto_Ar[$i])==""?"Nro Documento":$pagto_nome_docto_Ar[$i]); ?></b></td>
            <td><?php echo $pagto_num_docto[$i]?></td>
          </tr>
          <?php 
            } 
            
          }
          ?>
		  
		<?php	$arquivos = buscaArquivosIniciaCom($FOLDER_COMMERCE_UPLOAD, 'nome', 'asc', "money_comprovante_" . $venda_id . "_");
			if(count($arquivos) > 0){ ?>
		<tr bgcolor="#F5F5FB">
			<td><b>Comprovante</b></td>
			<td><?php for($j = 0; $j < count($arquivos); $j++){ ?><a style="text-decoration:none" target="_blank" href="/gamer/pagamentos/com_pagto_compr_down.php?venda=<?php echo $venda_id?>&arquivo=<?php echo $arquivos[$j]?>">Comprovante <?php echo ($j+1)?></a><br><?php } ?></td>
		</tr>
		<?php 	} ?>

		<?php 
			if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
				if($vg_pagto_tipo == $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC'] ) {
					?>
					<table class="table txt-preto fontsize-pp">
						  <tr bgcolor="#FFFFFF"> 
							<td colspan="2" bgcolor="#ECE9D8">Pagamento EPP CASH</font></td>
						  </tr>
					<?php
					$sql  = "select * from pins_store inner join pins_store_pag_epp_pin ON (ps_pin_codinterno=pin_codinterno) where pin_codinterno in (select ps_pin_codinterno from pins_store_pag_epp_pin where tpc_idpagto = ( select idpagto from tb_pag_compras where tipo_cliente = 'M' and idvenda=".((isset($idvenda_origem) && $idvenda_origem>0)?$idvenda_origem:$venda_id) .") )";
					$rs_pins_eppcash = SQLexecuteQuery($sql);
					if(pg_numrows($rs_pins_eppcash) > 0) {
		
						?>
						  <tr>
							<td>
								<table class="table txt-preto fontsize-pp">
								<tr bgcolor="F0F0F0" class="texto">
								  <td width="200"><b>Pins EPP Cash Utilizados</b>
								  </td>
								  <td align="center"><b>Canal Venda</b></td>
								  <td align="center"><b>PIN cod interno</b></td>
								  <td align="center"><b>Distribuidor</b></td>
								  <td align="center"><b>PIN EPP Cash valor</b></td>
								</tr>
						<?php
								while ($rs_pins_eppcash_row = pg_fetch_array($rs_pins_eppcash)){
									$pin_codinterno = $rs_pins_eppcash_row['pin_codinterno'];
									$distributor_codigo = $rs_pins_eppcash_row['distributor_codigo'];
									$pin_valor = $rs_pins_eppcash_row['pin_valor'];
									$canal_dist = $rs_pins_eppcash_row['pspep_canal'];

							?>
										<tr class="texto" bgcolor="#F5F5FB">
										  <td width="200">&nbsp;</td>
										  <td align="center"><?php echo $DISTRIBUIDORAS_CANAIS[$canal_dist] ?></td>
										  <td align="center"><?php echo $pin_codinterno ?></td>
										  <td align="center"><?php echo get_nome_distribuidora_by_codigo($distributor_codigo)." (Cód_b: $distributor_codigo)" ?></td>
										  <td align="center"><?php echo number_format($pin_valor, 2, ',', '.') ?></td>
										</tr>
							<?php	
								}
						?>
								</table>
							</td>
						  </tr>
						<?php 
					} 

				if($b_is_gocash) {
					?>
					<table class="table txt-preto fontsize-pp">
						  <tr bgcolor="#FFFFFF"> 
							<td colspan="2" bgcolor="#ECE9D8">Pagamento GoCash utilizado</font></td>
						  </tr>
					<?php
					$sql  = "select * from pins_gocash where pgc_vg_id = $venda_id order by pgc_pin_response_date desc";
					$rs_pins_gocash = SQLexecuteQuery($sql);
					if(pg_numrows($rs_pins_gocash) > 0) {
		
						?>
						  <tr>
							<td>
								<table class="table txt-preto fontsize-pp">
								<tr bgcolor="F0F0F0" class="texto">
								  <td width="200"><b>Pins GoCash Utilizados</b>
								  </td>
								  <td align="center"><b>PIN Number</b></td>
								  <td align="center"><b>Valor nominal</b></td>
								  <td align="center"><b>Valor real</b></td>
								  <td align="center"><b>Currency</b></td>
								  <td align="center"><b>Order no</b></td>
								  <td align="center"><b>Data</b></td>
								  <td align="center"><b>Usuário</b></td>
								</tr>
						<?php
									while ($rs_pins_gocash_row = pg_fetch_array($rs_pins_gocash)){
										$pgc_pin_number = $rs_pins_gocash_row['pgc_pin_number'];
										$pgc_face_amount = $rs_pins_gocash_row['pgc_face_amount'];
										$pgc_real_amount = $rs_pins_gocash_row['pgc_real_amount'];
										$pgc_currency = $rs_pins_gocash_row['pgc_currency'];
										$pgc_order_no = $rs_pins_gocash_row['pgc_order_no'];
										$pgc_pin_response_date = $rs_pins_gocash_row['pgc_pin_response_date'];
										$pgc_ug_id = $rs_pins_gocash_row['pgc_ug_id'];
							?>
										<tr class="texto" bgcolor="#F5F5FB">
										  <td width="200">&nbsp;</td>
										  <td align="center"><?php echo $pgc_pin_number ?></td>
										  <td align="center"><?php echo $pgc_face_amount ?></td>
										  <td align="center"><?php echo $pgc_real_amount ?></td>
										  <td align="center"><?php echo $pgc_currency ?></td>
										  <td align="center"><?php echo $pgc_order_no ?></td>
										  <td align="center"><?php echo $pgc_pin_response_date ?></td>
										  <td align="center"><?php echo $pgc_ug_id ?></td>

										</tr>
							<?php	
								}
						?>
								</table>
							</td>
						  </tr>
						<?php 
					}
				}

				// pagamento com saldo
				$sql  = "select * from tb_pag_compras where tipo_cliente = 'M' and idvenda=$venda_id";
					$rs_pins_eppcash = SQLexecuteQuery($sql);
					$rs_pins_eppcash_row = pg_fetch_array($rs_pins_eppcash);
					if($rs_pins_eppcash_row['valorpagtosaldo'] > 0) {
		
						?>
							<td>
								<table class="table txt-preto fontsize-pp">
										<tr class="texto"  bgcolor="F0F0F0">
										  <td width="200"><b>Valor de Saldo Utilizado</b></td>
										  <td align="center" width="44">&nbsp;</td>
										  <td align="center" width="250"></td>
										  <td align="center" width="200"></td>
										  <td align="center" width="200"><b><?php echo number_format($rs_pins_eppcash_row['valorpagtosaldo'], 2, ',', '.') ?></b></td>
										</tr>
								<?php
								$sql = "select scf.vg_id as vg_id_deposito, 
										(select distributor_codigo 
										from pins_store ps
											inner join pins_store_pag_epp_pin pspep ON (ps_pin_codinterno=pin_codinterno) 
										where pin_codinterno in 
											(
												select ps_pin_codinterno 
												from pins_store_pag_epp_pin pspep1
												where tpc_idpagto = ( 
														select idpagto from tb_pag_compras tpc where tpc.tipo_cliente = 'M' and tpc.idvenda=scfu.vg_id
													) 
											)  limit 1
										) as distributor_codigo,  
										* 
										from saldo_composicao_fifo_utilizado scfu
											INNER JOIN saldo_composicao_fifo scf ON (scfu.scf_id=scf.scf_id)
										where scfu.vg_id=".$venda_id;
								$rs_saldo_utilizado = SQLexecuteQuery($sql);
								$contador_exibicao = 1;
								while($rs_saldo_utilizado_row = pg_fetch_array($rs_saldo_utilizado)) {

									// procura a origem do depósito desta parte do saldo
									if($rs_saldo_utilizado_row['vg_id_deposito']>0) {

										$sql_origem = "select idvenda_origem from tb_pag_compras where tipo_cliente = 'M' and idvenda = ".$rs_saldo_utilizado_row['vg_id_deposito']."";
										$idvenda_origem = get_db_single_value($sql_origem);

										$idvenda_origem_efetivo = (($idvenda_origem>0)?$idvenda_origem:$rs_saldo_utilizado_row['vg_id_deposito']);
										$distributor_codigo = 0;
										//$sql_pin_deposito = "select distributor_codigo from pins_store inner join pins_store_pag_epp_pin ON (ps_pin_codinterno=pin_codinterno) where pin_codinterno in (select ps_pin_codinterno from pins_store_pag_epp_pin where tpc_idpagto = ( select idpagto from tb_pag_compras where tipo_cliente = 'M' and idvenda=".$idvenda_origem_efetivo." ) )";
//										$distributor_codigo = get_db_single_value($sql_pin_deposito);
										$distributor_codigo = $rs_saldo_utilizado_row['distributor_codigo'];
										if(!$distributor_codigo) {
												if($rs_saldo_utilizado_row['scf_canal']=="C") {
													$distributor_codigo = "C";
												} else {
													//$distributor_codigo = "?";
													$distributor_codigo = $rs_saldo_utilizado_row['scf_id_pagamento'];
												}
										}

									}
								?>
										<tr class="texto"  bgcolor="F0F0F0">
										  <td width="200"><?php if($contador_exibicao == 1) echo "<b>Composi&ccedil;&atilde;o do Saldo Utilizado</b>";?></td>
										  <td align="center" width="44">&nbsp;Canal:<?php echo $rs_saldo_utilizado_row['scf_id_pagamento'] ?></td>
										  <td align="center" width="250">Utilizado o dep&oacute;sito <?php if($rs_saldo_utilizado_row['scf_valor']>$rs_saldo_utilizado_row['scfu_valor']) echo "Parcialmente"; else echo "Totalmente";?></td>
										  <td align="center" width="200"><nobr><?php if(!empty($rs_saldo_utilizado_row['vg_id_deposito'])) {
												?>
													<a style="text-decoration:none" href="com_venda_detalhe.php?venda_id=<?php echo $rs_saldo_utilizado_row['vg_id_deposito']; ?>"><?php echo $rs_saldo_utilizado_row['vg_id_deposito']; ?></a> 
													<span style='color:darkgreen; background-color:#ffffcc'><?php 
//													if($idvenda_origem>0) {
//														echo " (Cód_c: ".$rs_saldo_utilizado_row['scf_canal'].")";
//													} else {
//													}
													echo get_nome_distribuidora_by_codigo($distributor_codigo)." (Cód_a: $distributor_codigo)" ;
													?></span>
												<?php 
												}
												?></nobr></td>
										  <td align="center" width="200"><b><?php echo number_format($rs_saldo_utilizado_row['scfu_valor'], 2, ',', '.') ?></b></td>
										</tr>
								<?php
									$contador_exibicao++;
								}//end while
								?>
								</table>

							</td>
						  </tr>
						</table>
						<?php 
					} 
				}
			}
		?>
		  
		</table>

<?php if($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){ ?>
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Dados do Boleto</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Código</b></td>
            <td><?php echo $bbg_boleto_codigo ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Data de Inclusão</b></td>
            <td><?php if($bbg_data_inclusao) echo formata_data_ts($bbg_data_inclusao, 0, true, true) ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Banco</b></td>
            <td><?php echo $bbg_bco_codigo ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Data de Vencimento</b></td>
            <td><?php if($bbg_data_venc) echo formata_data_ts($bbg_data_venc, 0, false, false) ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Valor</b></td>
            <td><?php if($bbg_valor) echo number_format($bbg_valor, 2, ',', '.') ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Taxa de Serviço Bancário</b></td>
            <td><?php if($bbg_valor_taxa) echo number_format($bbg_valor_taxa, 2, ',', '.') ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>N. Docto</b></td>
            <td><?php echo $bbg_documento ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Quitação</b></td>
            <td><?php echo ((is_null($bbg_pago) || $bbg_pago == 0)?("Não quitado"):("Quitado em " . formata_data_ts($bbg_data_pago, 0, false, false))) ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Ver boleto</b></td>
			<?php
			$token = date('YmdHis') . "," . $venda_id . "," . $vg_ug_id;
			$objEncryption = new Encryption();
			$token_crypt = $objEncryption->encrypt($token);
//echo "bbg_bco_codigo: '$bbg_bco_codigo'<br>";
                        
			switch($bbg_bco_codigo) {
				case $BOLETO_MONEY_BANCO_ITAU_COD_BANCO:
					$sboletoURL = "/SICOB/BoletoWebItauCommerce.php";
					break;
				case $BOLETO_MONEY_CAIXA_COD_BANCO:
					$sboletoURL = "/SICOB/BoletoWebCaixaDistCommerce.php";
					break;
				case $BOLETO_MONEY_BRADESCO_COD_BANCO:
					$sboletoURL = "/boletos/gamer/boleto_bradesco.php";
					break;
				case ($BOLETO_MONEY_BANCO_BANESPA_COD_BANCO*1):
					$sboletoURL = "/SICOB/BoletoWebBanespaCommerce.php";
					break;
				default:
					$sboletoURL = "";
					break;
			}

			?>
            <td>
<?php 
/*
				<a style="text-decoration:none" href="<_?_php echo $GLOBALS['PREPAG_DOMINIO']?_>/SICOB/BoletoWebCaixaCommerce.php?token=<_?_php echo $token_crypt?_>" target="_blank">Boleto</a>*
				&nbsp;&nbsp;&nbsp;*link válido por 5 min, após este período recarregar a página para pode acessá-lo.
*/
?>
				<?php if($sboletoURL) { ?>
				<a style="text-decoration:none" href="https://<?php echo $_SERVER["SERVER_NAME"] . $sboletoURL; ?>?token=<?php echo $token_crypt?>" target="_blank">Boleto</a>*
				&nbsp;&nbsp;&nbsp;*link válido por 5 min, após este período recarregar a página para pode acessá-lo.
				<?php } else { ?>
				Sem boleto
				<?php } ?>

			</td>
          </tr>
		</table>

<?php } elseif((isset($GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD']) && $vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD'])  || (isset($GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS'])  && $vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS'])){ ?>
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Dados Redecard</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Código</b></td>
            <td><?php echo $vgrc_id ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Data de Inclusão</b></td>
            <td><?php if($vgrc_data_inclusao) echo formata_data_ts($vgrc_data_inclusao, 0, true, true) ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Parcelas</b></td>
            <td><?php echo $vgrc_parcelas ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Valor</b></td>
            <td><?php if($vgrc_total) echo number_format($vgrc_total, 2, ',', '.') ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Data de Envio</b></td>
            <td><?php if($vgrc_data_envio1) echo formata_data_ts($vgrc_data_envio1, 0, true, true) ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>IP do usuário</b></td>
            <td><?php echo $vgrc_usuario_ip ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>TRANSACAO</b></td>
            <td><?php echo $vgrc_transacao ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>BANDEIRA</b></td>
            <td><?php echo $vgrc_bandeira ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>CODVER</b></td>
            <td><?php echo $vgrc_codver ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>DATA</b></td>
            <td><?php echo $vgrc_ret2_data ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NR_CARTAO</b></td>
            <td><?php echo $vgrc_ret2_nr_cartao ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>ORIGEM_BIN</b></td>
            <td><?php echo $vgrc_ret2_origem_bin ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMAUTOR</b></td>
            <td><?php echo $vgrc_ret2_numautor ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMCV</b></td>
            <td><?php echo $vgrc_ret2_numcv ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMAUTENT</b></td>
            <td><?php echo $vgrc_ret2_numautent ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMSQN</b></td>
            <td><?php echo $vgrc_ret2_numsqn ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>CODRET</b></td>
            <td><?php echo $vgrc_ret2_codret ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>MSGRET</b></td>
            <td><?php echo urldecode($vgrc_ret2_msgret) ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>ENDERECO</b></td>
            <td><?php echo $vgrc_ret2_endereco ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMERO</b></td>
            <td><?php echo $vgrc_ret2_numero ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>COMPLEMENTO</b></td>
            <td><?php echo $vgrc_ret2_complemento ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>CEP</b></td>
            <td><?php echo $vgrc_ret2_cep ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>RESPAVS</b></td>
            <td><?php echo $vgrc_ret2_respavs ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>MSGAVS</b></td>
            <td><?php echo urldecode($vgrc_ret2_msgavs)  ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMPRG</b></td>
            <td><?php echo $vgrc_ret2_numprg  ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NR_HASH_CARTAO</b></td>
            <td><?php echo $vgrc_ret2_nr_hash_cartao ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>COD_BANCO</b></td>
            <td><?php echo $vgrc_ret2_cod_banco ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Retorno Etapa 4</b></td>
            <td><?php echo $vgrc_ret4_ret ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Retorno Etapa 4 - CODRET</b></td>
            <td><?php echo $vgrc_ret4_codret ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Retorno Etapa 4 - MSGRET</b></td>
            <td><?php echo urldecode($vgrc_ret4_msgret) ?></td>
          </tr>
		</table>

<?php }
if($vg_concilia == 1){ ?>

        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8" class="texto">Conciliação</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Data</b></td>
            <td width="744"><?php echo formata_data_ts($vg_data_concilia, 0, true, true) ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Conciliado por</b></td>
            <td><?php echo $shn_nome ?></td>
		  </tr>
		  
		  <?php
			   if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){	
		  ?>

	      <?php						
			   }
		  ?>
		  
		  <?php if(!is_null($vg_dep_codigo) && $vg_dep_codigo != null){ ?>
		  <tr bgcolor="#F5F5FB">
            <td><b>Código do depósito</b></td>
            <td><a style="text-decoration:none" href="/financeiro/pedidos/depositos/altera.php?DepCod=<?php echo $vg_dep_codigo?>" target="_blank"><?php echo $vg_dep_codigo ?></a></td>
		  </tr>
		  	<?php	$sql  = "select * from depositos_pendentes dep where dep_codigo = " . $vg_dep_codigo;
				$rs_dep = SQLexecuteQuery($sql);
				if(!$rs_dep || pg_num_rows($rs_dep) == 0){ ?>
		  			<tr bgcolor="#F5F5FB"><td><b>Dados do depósito</b></td><td>Depósito não encontrado</td></tr>
			<?php 	} else { $rs_dep_row = pg_fetch_array($rs_dep);?>
		  			<tr bgcolor="#F5F5FB"><td><b>Doc Equivalente</b></td><td><?php echo $rs_dep_row['dep_documento'] ?></td></tr>
		  			<tr bgcolor="#F5F5FB"><td><b>Data Depósito</b></td><td><?php echo formata_data($rs_dep_row['dep_data'],0) ?></td></tr>
		  			<tr bgcolor="#F5F5FB"><td><b>Banco</b></td><td><?php echo $rs_dep_row['dep_banco'] ?></td></tr>
		  	<?php 	} ?>
		  <?php } ?>
		  <?php if(!is_null($vg_bol_codigo) && $vg_bol_codigo != null){ ?>
		  <tr bgcolor="#F5F5FB">
            <td><b>Código do boleto</b></td>
            <td><a style="text-decoration:none" href="/financeiro/pedidos/boletos/altera.php?BolCod=<?php echo $vg_bol_codigo?>" target="_blank"><?php echo $vg_bol_codigo ?></a></td>
		  </tr>
		  	<?php	$sql  = "select * from boletos_pendentes bol where bol_codigo = " . $vg_bol_codigo;
				$rs_bol = SQLexecuteQuery($sql);
				if(!$rs_bol || pg_num_rows($rs_bol) == 0){ ?>
		  			<tr bgcolor="#F5F5FB"><td><b>Dados do boleto</b></td><td>Boleto não encontrado</td></tr>
			<?php 	} else { $rs_bol_row = pg_fetch_array($rs_bol);?>
		  			<tr bgcolor="#F5F5FB"><td><b>Doc Equivalente</b></td><td><?php echo $rs_bol_row['bol_documento'] ?></td></tr>
		  			<tr bgcolor="#F5F5FB"><td><b>Data Depósito</b></td><td><?php echo formata_data($rs_bol_row['bol_data'],0) ?></td></tr>
		  			<tr bgcolor="#F5F5FB"><td><b>Banco</b></td><td><?php echo $rs_bol_row['bol_banco'] ?></td></tr>
		  	<?php 	} ?>
		  <?php } ?>
		</table>

<?php } elseif($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] ) { //|| $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO']
?>
		<script>
		function load_me(cod_sel, pagto_tipo){
			document.form1.concilia_cod_sel.value = cod_sel; 
			document.form1.concilia_pagto_tipo.value = pagto_tipo; 
			return false;
		}
		</script>
	
			<input type="hidden" name="venda_id" value="<?php echo $venda_id ?>">
			<input type="hidden" name="concilia_pagto_tipo" value="<?php echo $vg_pagto_tipo ?>">
			<table class="table txt-preto fontsize-pp">
			  <tr bgcolor="#FFFFFF"> 
				<td colspan="2" bgcolor="#ECE9D8" class="texto">Conciliação</font></td>
			  </tr>
			  <tr bgcolor="#F5F5FB"> 
				<td align="center" width="50%"><b>Observações</b></td><td align="center" width="50%"><b>Código selecionado</b> 
				<?php 
				if( ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']) || 
					($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'])) {
				?>
				<input type="text" name="concilia_cod_sel" value="" class="form">
				<?php 

				} else {
					$sql_pag = "select numcompra from tb_pag_compras pag where tipo_cliente = 'M' and idvenda = " . $venda_id. " order by datainicio desc limit 1;";
					$rs_pag = SQLexecuteQuery($sql_pag);
					if(!$rs_pag) {
						$msg_1 = date("Y-m-d H:i:s")." - Erro ao recuperar numcompra, venda_id: $venda_id).<br>\n";
						echo $msg_1;
					} else {
						$rs_pag_row = pg_fetch_array($rs_pag);

						$concilia_cod_sel = $rs_pag_row["numcompra"];
						echo "$concilia_cod_sel";
					}
				}

				?>
				</td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td align="center">
					<textarea cols="40" rows="8" name="ultimo_status_obs"><?php echo $vg_ultimo_status_obs ?></textarea>
				</td>
				<td valign="top" align="center">
	
	<?php
		if(isset($inicial) == false) 	$inicial = 0;
		$max          	= 200; //	$qtde_reg_tela;
		$default_add  	= nome_arquivo($PHP_SELF);
		$varsel 		= "&venda_id=" . $venda_id;
		$img_anterior 	= "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
		$img_proxima  	= "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
		if(!isset($range) || !$range)    	$range       = 1;
		$range_qtde   	= $qtde_range_tela;
	?>
	
	<?php 
		if($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']){ ?>
		<?php
			if(isset($ncamp) == false) 	$ncamp = " dep_data ";
			$sql = "select dep_codigo, dep_valor, dep_data, dep_banco, dep_documento, dep_cod_documento, bco_nome, dep_aprovado ";
			$sql .= "from depositos_pendentes, bancos_financeiros ";
			$sql .= "where (dep_banco = bco_codigo) and (bco_rpp = 1) and dep_aprovado = 0 and dep_banco = '".$vg_pagto_banco."' ";
			$sql .= " and dep_valor = " . $vg_pagto_valor_pago. " and dep_conta != '20.459-5' and dep_conta != '1689-6'";
			$rs_dep = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_dep);
			$sql .= " order by " . $ncamp . " " . ((isset($ordem) &&$ordem == 1)?"asc":"desc");
			$sql .= " limit " . $max . " offset " . $inicial;
			$rs_dep = SQLexecuteQuery($sql);

		?>
				<div id="Layer1" class="" style="position:static; width:350px; height:130px; z-index:1; overflow: auto;"> 
				<table class="table txt-preto fontsize-pp">
				  <tr> 
					<td valign="top">
					  <table class="table txt-preto fontsize-pp">
						<tr bgcolor="#ECE9D8"> 
						  <td colspan="5" align="center"><b>Depósitos Disponíveis </b></a></td>
						</tr>
						  <?php $ordem = (isset($ordem) && $ordem == 1)?2:1; ?>
						<tr bgcolor="#ECE9D8"> 
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=dep_codigo&inicial=".$inicial.$varsel ?>" class="link_branco" >Codigo</a></td>
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=dep_data&inicial=".$inicial.$varsel ?>" class="link_branco" >Data</a></td>
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=dep_banco&inicial=".$inicial.$varsel ?>" class="link_branco" >Banco</a></td>
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=dep_documento&inicial=".$inicial.$varsel ?>" class="link_branco" >Documento</a></td>
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=dep_valor&inicial=".$inicial.$varsel ?>" class="link_branco" >Valor</a></td>
						</tr>
				<?php if($total_table == 0){?>
						<tr> 
						  <td align="center" colspan="5">Nenhum depósito encontrado</td>
						</tr>
				<?php } else {?>
					<?php	while ($pgest = pg_fetch_array($rs_dep)) {
							if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} ?>
							<tr bgcolor="<?php echo $cor1 ?>"> 
							  <td align="center"><a style="text-decoration:none" href="#" onClick="load_me('<?php echo $pgest['dep_codigo'] ?>','<?php echo $vg_pagto_tipo?>');return false;"><?php echo $pgest['dep_codigo'] ?></a></td>
							  <td align="center"><?php echo formata_data($pgest['dep_data'], 0) ?></td>
							  <td align="center"><?php echo $pgest['dep_banco'] ?></td>
							  <td align="center"><?php echo $pgest['dep_documento'] ?></td>
							  <td align="center"><?php echo number_format($pgest['dep_valor'], 2, ',','.') ?></td>
							</tr>
					<?php	} ?>
					<?php	paginacao_query($inicial, $total_table, $max, '5', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>		
				<?php } ?>
					  </table>
					</td>
				  </tr>
				</table>
				</div>
	
	<?php } elseif($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){ ?>
		<?php
			if(isset($ncamp) == false) 	$ncamp = " bol_data ";
			$sql = "select bol_codigo, bol_valor, bol_data, bol_banco, bol_documento, bol_cod_documento, bco_nome, bol_aprovado ";
			$sql .= "from boletos_pendentes, bancos_financeiros ";
			$sql .= "where (bol_banco = bco_codigo) and (bco_rpp = 1) and bol_aprovado = 0 and bol_banco = '".$vg_pagto_banco."' ";
			//$sql .= " and bol_documento like '8___" . substr($vg_pagto_num_docto, 4) . "%'";
			$rs_bol = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_bol);
			$sql .= " order by " . $ncamp . " " . ((isset($ordem) &&$ordem == 1)?"asc":"desc");
			$sql .= " limit " . $max ;
            if(isset($inicial)){
                $sql .= " offset " . $inicial;
            }
			$rs_bol = SQLexecuteQuery($sql);
		?>
				<div id="Layer1" class="" style="position:static; width:300px; height:130px; z-index:1; overflow: auto;"> 
				<table class="table txt-preto fontsize-pp">
				  <tr> 
					<td valign="top">
					  <table class="table txt-preto fontsize-pp">
						<tr bgcolor="#ECE9D8"> 
						  <td colspan="4" align="center"><b>Boletos Disponíveis </b></a></td>
						</tr>
						  <?php $ordem = ($ordem == 1)?2:1; ?>
						<tr bgcolor="#ECE9D8"> 
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bol_codigo&inicial=".$inicial.$varsel ?>" class="link_branco" >Codigo</a></td>
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bol_data&inicial=".$inicial.$varsel ?>" class="link_branco" >Data</a></td>
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bol_documento&inicial=".$inicial.$varsel ?>" class="link_branco" >Documento</a></td>
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bol_valor&inicial=".$inicial.$varsel ?>" class="link_branco" >Valor</a></td>
						</tr>
				<?php if($total_table == 0){?>
						<tr> 
						  <td align="center" colspan="4">Nenhum boleto encontrado</td>
						</tr>
				<?php } else {?>
					<?php	while ($pgest = pg_fetch_array($rs_bol)) {
							if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} ?>
							<tr bgcolor="<?php echo $cor1 ?>"> 
							  <td align="center"><a style="text-decoration:none" href="#" onClick="load_me('<?php echo $pgest['bol_codigo'] ?>','<?php echo $vg_pagto_tipo?>');return false;"><?php echo $pgest['bol_codigo'] ?></a></td>
							  <td align="center"><?php echo formata_data($pgest['bol_data'], 0) ?></td>
							  <td align="center"><?php echo $pgest['bol_documento'] ?></td>
							  <td align="center"><?php echo number_format($pgest['bol_valor'], 2, ',','.') ?></td>
							</tr>
					<?php	} ?>
					<?php	if(isset($inicial) && isset($range))
                                paginacao_query($inicial, $total_table, $max, '4', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>		
				<?php	} ?>
					  </table>
					</td>
				  </tr>
				</table>
				</div>
	
	<?php } elseif($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD'] || $vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS']){ ?>
		<?php
			if(!$ncamp) 	$ncamp = " vgrc_ret2_data ";
			$sql = "select vgrc_id, vgrc_total, vgrc_ret2_data, vgrc_ret2_numautent ";
			$sql .= "from tb_venda_games_redecard vgrc ";
			$sql .= "where vgrc.vgrc_aprovado = 0 and vgrc.vgrc_ret4_codret = '0' and vgrc.vgrc_vg_id = ".$venda_id;
			$rs_redecard = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_redecard);
			$sql .= " order by " . $ncamp . " " . ($ordem == 1?"asc":"desc");
			$sql .= " limit " . $max . " offset " . $inicial;
			$rs_redecard = SQLexecuteQuery($sql);
		?>
				<div id="Layer1" class="" style="position:static; width:300px; height:130px; z-index:1; overflow: auto;"> 
				<table class="table txt-preto fontsize-pp">
				  <tr> 
					<td valign="top">
					  <table class="table txt-preto fontsize-pp">
						<tr bgcolor="#ECE9D8"> 
						  <td colspan="4" align="center"><b>Redecard Disponíveis </b></a></td>
						</tr>
						  <?php $ordem = ($ordem == 1)?2:1; ?>
						<tr bgcolor="#ECE9D8"> 
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vgrc_id&inicial=".$inicial.$varsel ?>" class="link_branco" >Codigo</a></td>
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vgrc_ret2_data&inicial=".$inicial.$varsel ?>" class="link_branco" >Data</a></td>
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vgrc_ret2_numautent&inicial=".$inicial.$varsel ?>" class="link_branco" >NUMAUTENT</a></td>
						  <td align="center"><a style="text-decoration:none" href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vgrc_total&inicial=".$inicial.$varsel ?>" class="link_branco" >Valor</a></td>
						</tr>
				<?php if($total_table == 0){?>
						<tr> 
						  <td align="center" colspan="4">Nenhum redecard encontrado</td>
						</tr>
				<?php } else {?>
					<?php	while ($pgest = pg_fetch_array($rs_redecard)) {
							if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} ?>
							<tr bgcolor="<?php echo $cor1 ?>"> 
							  <td align="center"><a style="text-decoration:none" href="#" onClick="load_me('<?php echo $pgest['vgrc_id'] ?>','<?php echo $vg_pagto_tipo?>');return false;"><?php echo $pgest['vgrc_id'] ?></a></td>
							  <td align="center"><?php echo substr($pgest['vgrc_ret2_data'], 6, 2) . '/' . substr($pgest['vgrc_ret2_data'], 4, 2) . '/' . substr($pgest['vgrc_ret2_data'], 0, 4) ?></td>
							  <td align="center"><?php echo $pgest['vgrc_ret2_numautent'] ?></td>
							  <td align="center"><?php echo number_format($pgest['vgrc_total'], 2, ',','.') ?></td>
							</tr>
					<?php	} ?>
					<?php	paginacao_query($inicial, $total_table, $max, '4', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>		
				<?php	} ?>
					  </table>
					</td>
				  </tr>
				</table>
				</div>
	
	<?php }?>

				</td>
			  </tr>
<?php	if(trim($vgm_pin_codinterno)=="" && $vg_deposito_em_saldo!="1") {?>
			  <tr bgcolor="#F5F5FB"><td colspan="2">&nbsp;</td></tr>
			  <tr bgcolor="#F5F5FB">
				<td colspan="2" align="center"><input type="submit" name="BtnConcilia" value="Conciliar Venda" class="btn btn-info btn-sm"></td>
			  </tr>
<?php	} else { 
			echo "<p style='color:red' class='texto'>Venda está no status '".$vg_ultimo_status."' mas já tem PINs vendidos ($vgm_pin_codinterno) -> sem botão '<b>Conciliar Venda</b>' (1)</p>\n";
		}?>
			</table>
			</form>
	
<?php }?>

<?php 
	if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']) {
			if(trim($vgm_pin_codinterno)=="") {
?>
			<form name="form2" method="post" action="com_venda_detalhe.php">
			<input type="hidden" name="venda_id" value="<?php echo $venda_id ?>">
			<table class="table txt-preto fontsize-pp">
			  <tr bgcolor="#FFFFFF"> 
				<td colspan="2" bgcolor="#ECE9D8" class="texto">Processa venda</font></td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td colspan="2"><b>Observações</b></td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td valign="top" colspan="2">
					<textarea cols="40" rows="8" name="ultimo_status_obs"><?php echo $vg_ultimo_status_obs ?></textarea>
				</td>
			  </tr>
			  <tr bgcolor="#F5F5FB"> 
				<td colspan="2" align="center"><input type="submit" name="BtnProcessa" value="Processar" class="btn btn-info btn-sm"></td>
			  </tr>
			</table>
			</form>
<?php		} else {
			echo "<p style='color:red' class='texto'>Venda está no status '".$GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']."' mas já tem PINs vendidos ($vgm_pin_codinterno)  -> sem botão '<b>Processa Venda</b>' (2)</p>\n";
			}
	}
?>

<?php if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {?>
			<form name="form3" method="post" action="com_venda_detalhe.php">
			<input type="hidden" name="venda_id" value="<?php echo $venda_id ?>">
			<table class="table txt-preto fontsize-pp">
			  <tr bgcolor="#FFFFFF"> 
				<td colspan="2" bgcolor="#ECE9D8" class="texto">Processa envio de email</font></td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td colspan="2"><b>Observações</b></td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td valign="top" colspan="2">
					<textarea cols="40" rows="8" name="ultimo_status_obs"><?php echo $vg_ultimo_status_obs ?></textarea>
				</td>
			  </tr>
			  <tr bgcolor="#F5F5FB"> 
				<td colspan="2" align="center">
				<?php if(!$b_integracao) { ?>
					<input type="submit" name="BtnProcessaEmail" value="Processar Email" class="btn btn-info btn-sm">
				<?php } else { ?><br>
					<p style='color:red'>Vendas de integração não enviam email</p>
					<br>
				<?php } ?>
				</td>
			  </tr>
			</table>
			</form>
<?php }?>
<?php if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO']) {?>

			<form name="form4" method="post" action="com_venda_detalhe.php">
			<input type="hidden" name="venda_id" value="<?php echo $venda_id ?>">
			<table class="table txt-preto fontsize-pp">
			  <tr bgcolor="#FFFFFF"> 
				<td bgcolor="#ECE9D8" colspan="2">Cancelamento</font></td>
			  </tr>
			  <tr bgcolor="#F5F5FB"> 
				<td align="center"><b>Observações</b></td>
				<td align="center"><b>Observações ao usuário</b></td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td align="center"><textarea cols="40" rows="8" name="ultimo_status_obs"><?php echo $vg_ultimo_status_obs ?></textarea></td>
				<td align="center"><textarea cols="40" rows="8" name="usuario_obs"><?php echo $vg_usuario_obs ?></textarea></td>
			</tr>
			<tr bgcolor="#F5F5FB">
					<td colspan="2" align="center">
						<input style="padding: 10px;" id="code_payment" type="number" oninput="mascara_numeros(this)" placeholder="Informe o ID aqui" required maxlength="19"/>
						<br>
						<button type="button" id="btn_concilia" class="btn btn-success">Conciliar Manualmente</button>
					</td>
				<div class="modal fade" id="modalResultado" tabindex="-1" role="dialog">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title"></h4>
								
								<div class="modal-body">
								</div>
								<div class="modal-footer">
								</div>
							</div>
						</div>
					</div>
				</div>
			</tr>
			  <tr bgcolor="#F5F5FB">
				<td align="center" colspan="2"><input type="submit" name="BtnCancelar" value="Cancelar Venda" class="btn btn-info btn-sm" onClick="return confirm('Deseja realmente cancelar esta venda ?');"></td>
			  </tr>
			</table>
			</form>
			<input type="hidden" id="venda_id" name="venda_id" value="<?php echo $venda_id; ?>">
			<input type="hidden" id="dados_operador" name="dados_operador" value="<?php  echo $_SESSION["userlogin_bko"]; ?>">
			
<script>
	$('.fecha-modal').on('click', function(){
		$('.modal-title').empty();
		$('.modal-body h5').empty();
		$('.modal-body p').empty();
		$('.modal-footer').empty();
	});
	function mascara_numeros(input) {
		console.log(input.value);
		let parsed = Number.parseInt(input.value);
		if(Number.isNaN(parsed)) {
			input.value = input.value.replace(/[^0-9]/g, '');
		}
		
		if(input.value.length === 20) {
			$('.modal-title').append('Ops...');
			$('.modal-body').append('<h5>Você utrapassou o limite de caracteres!</h5><p>Insira um código com <strong>19 caracteres</strong>!</p>');
			$('#modalResultado').modal();
		}
	}
	$('#btn_concilia').on('click', function(){
		let valor_code_payment = $('#code_payment').val();
		
		let parsed = Number.parseInt(valor_code_payment);

		if (Number.isNaN(parsed)) {
 			$('.modal-title').append('Ops...');
			$('.modal-body').append('<h5>Houve um erro. Tente mais tarde...</h5>');
			$('#modalResultado').modal();
		} else {
			$.ajax({
				url: "https://<?php echo $server_url_complete ;?>/gamer/vendas/ajax/request-api-rest.php",
				method: "POST",
				data: {
					id: $("#code_payment").val(),
					dados_operador: $("#dados_operador").val(),
					venda_id: $("#venda_id").val()
				}
			}).done(function(mensagem){
				if (mensagem == 'Conciliação manual não foi realizada!') {
					$('.modal-title').append('Ops!');
					$('.modal-body').append('<h5>'+mensagem+'</h5>');
					$('.modal-footer').append('<button type="button" class="btn btn-default fecha-modal" data-dismiss="modal">Fechar</button>');
					$('#modalResultado').modal();
				} else if (mensagem == 'Concialização realizada com sucesso!') {
					$('.modal-title').append('Sucesso!');
					$('.modal-body').append('<h5>'+mensagem+'</h5>');
					$('.modal-footer').append('<button type="button" class="btn btn-default fecha-modal" onclick="window.location.reload()" data-dismiss="modal">Fechar</button>');
					$('#modalResultado').modal();
				} else {
					$('.modal-title').append('Ops!');
					$('.modal-body').append('<h5>'+mensagem+'</h5>');
					$('.modal-footer').append('<button type="button" class="btn btn-default fecha-modal" data-dismiss="modal">Fechar</button>');
					$('#modalResultado').modal();
				}
				
			});
		}
	});
</script>
<?php } ?>
<?php if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA']) {?>
			
			<form name="form4" method="post" action="com_venda_detalhe.php">
			<input type="hidden" name="venda_id" value="<?php echo $venda_id ?>">
			<table class="table txt-preto fontsize-pp">
			  <tr bgcolor="#FFFFFF"> 
				<td bgcolor="#ECE9D8" colspan="2">Descancelamento</font></td>
			  </tr>
			  <tr bgcolor="#F5F5FB"> 
				<td align="center"><b>Observações</b></td>
				<td align="center"><b>Observações ao usuário</b></td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td align="center"><textarea cols="40" rows="8" name="ultimo_status_obs"><?php echo $vg_ultimo_status_obs ?></textarea></td>
				<td align="center"><textarea cols="40" rows="8" name="usuario_obs"><?php echo $vg_usuario_obs ?></textarea></td>
			</tr>
			  <tr bgcolor="#F5F5FB"><td colspan="2">&nbsp;</td></tr>
			  <tr bgcolor="#F5F5FB">
				<td align="center" colspan="2"><input type="submit" name="BtnDescancelar" value="Descancelar Venda" class="btn btn-info btn-sm" onClick="return confirm('Deseja realmente descancelar esta venda ?');"></td>
			  </tr>
			</table>
			</form>
<?php } ?>
<?php
    $tf_v_codigo = (isset($tf_v_codigo)) ? $tf_v_codigo : "";
    $tf_v_status = (isset($tf_v_status)) ? $tf_v_status : "";
    $tf_v_data_inclusao_ini = (isset($tf_v_data_inclusao_ini)) ? $tf_v_data_inclusao_ini : "";
    $tf_v_data_inclusao_fim = (isset($tf_v_data_inclusao_fim)) ? $tf_v_data_inclusao_fim : "";
    $tf_v_data_concilia_ini = (isset($tf_v_data_concilia_ini)) ? $tf_v_data_concilia_ini : "";
    $tf_v_data_concilia_fim = (isset($tf_v_data_concilia_fim)) ? $tf_v_data_concilia_fim : "";
    $tf_v_concilia = (isset($tf_v_concilia)) ? $tf_v_concilia : "";
    $tf_d_banco = (isset($tf_d_banco)) ? $tf_d_banco : "";
    $tf_d_local = (isset($tf_d_local)) ? $tf_d_local : "";
    $tf_d_data_ini = (isset($tf_d_data_ini)) ? $tf_d_data_ini : "";
    $tf_d_data_fim = (isset($tf_d_data_fim)) ? $tf_d_data_fim : "";
    $tf_d_data_inclusao_ini = (isset($tf_d_data_inclusao_ini)) ? $tf_d_data_inclusao_ini : "";
    $tf_d_data_inclusao_fim = (isset($tf_d_data_inclusao_fim)) ? $tf_d_data_inclusao_fim : "";
    $tf_d_valor_pago = (isset($tf_d_valor_pago)) ? $tf_d_valor_pago : "";
    $tf_d_num_docto = (isset($tf_d_num_docto)) ? $tf_d_num_docto : "";
    $tf_u_codigo = (isset($tf_u_codigo)) ? $tf_u_codigo : "";
    $tf_u_nome = (isset($tf_u_nome)) ? $tf_u_nome : "";
    $tf_u_email = (isset($tf_u_email)) ? $tf_u_email : "";
    $tf_u_cpf = (isset($tf_u_cpf)) ? $tf_u_cpf : "";
    $tf_v_valor = (isset($tf_v_valor)) ? $tf_v_valor : "";
    $tf_v_qtde_produtos = (isset($tf_v_qtde_produtos)) ? $tf_v_qtde_produtos : "";
    $tf_v_qtde_itens = (isset($tf_v_qtde_itens)) ? $tf_v_qtde_itens : "";
    $fila_ncamp = (isset($fila_ncamp)) ? $fila_ncamp : "";
    $tf_d_forma_pagto = (isset($tf_d_forma_pagto)) ? $tf_d_forma_pagto : "";
    
	$varsel  = "&tf_v_codigo=$tf_v_codigo&tf_v_status=$tf_v_status";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	$varsel .= "&tf_v_data_concilia_ini=$tf_v_data_concilia_ini&tf_v_data_concilia_fim=$tf_v_data_concilia_fim";
	$varsel .= "&tf_v_concilia=$tf_v_concilia&tf_d_forma_pagto=$tf_d_forma_pagto&tf_d_banco=$tf_d_banco&tf_d_local=$tf_d_local";
	$varsel .= "&tf_d_data_ini=$tf_d_data_ini&tf_d_data_fim=$tf_d_data_fim";
	$varsel .= "&tf_d_data_inclusao_ini=$tf_d_data_inclusao_ini&tf_d_data_inclusao_fim=$tf_d_data_inclusao_fim";
	$varsel .= "&tf_d_valor_pago=$tf_d_valor_pago&tf_d_num_docto=$tf_d_num_docto";
	$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_nome=$tf_u_nome&tf_u_email=$tf_u_email&tf_u_cpf=$tf_u_cpf";
	$varsel .= "&tf_v_valor=$tf_v_valor&tf_v_qtde_produtos=$tf_v_qtde_produtos&tf_v_qtde_itens=$tf_v_qtde_itens";
?>
			<table class="table txt-preto fontsize-pp">
			  <tr bgcolor="#F5F5FB"> 
				<td colspan="2" align="center">
					<input type="button" name="BtnAnterior" value="Anterior não conciliado" class="btn btn-info btn-sm" onClick="window.location='com_fila_vendas.php?venda_id=<?php echo $venda_id?>&fila_ncamp=<?php echo $fila_ncamp?>&fila_ordem=1<?php echo $varsel?>';">
					&nbsp;&nbsp;&nbsp;
					<input type="button" name="BtnVoltar" value="Voltar" class="btn btn-info btn-sm" onClick="window.location='/index.php'">
					&nbsp;&nbsp;&nbsp;
					<input type="button" name="BtnProximo" value="Próximo não conciliado" class="btn btn-info btn-sm" onClick="window.location='com_fila_vendas.php?venda_id=<?php echo $venda_id?>&fila_ncamp=<?php echo $fila_ncamp?>&fila_ordem=2<?php echo $varsel?>';">
				</td>
			  </tr>
			</table>

    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>

</html>
<?php

//Testa vgm_pin_codinterno = null, caso contrario já houve uam venda
function get_pins_vendidos($vg_id) {
	$vgm_pin_codinterno = "";
	$sql_pin = "select vgm_pin_codinterno from tb_venda_games_modelo where vgm_vg_id = $vg_id;";
	$rs_pin = SQLexecuteQuery($sql_pin);
	if(!$rs_pin || pg_num_rows($rs_pin) == 0) {
		$msg = "Nenhuma venda encontrada.\n";
	} else {
		$rs_pin_row = pg_fetch_array($rs_pin);
		$vgm_pin_codinterno = $rs_pin_row['vgm_pin_codinterno']."";
	}
	return $vgm_pin_codinterno;
}

function retorna_status($pin) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_status from pins_store where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."'";
	
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pin_status'] != '')
			return $rs_log_row['pin_status'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function get_db_single_value($sql) {
	$val = 0;
	if(!$sql) {
		return $val;
	}
	$res = SQLexecuteQuery($sql);
	if($pg = pg_fetch_array ($res)) { 
		$val = $pg[0];
	} 
	return $val;
}
?>