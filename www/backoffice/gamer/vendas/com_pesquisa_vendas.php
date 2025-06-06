<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php 

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
if(isset($_GET["downloadCsv"]) && $_GET["downloadCsv"] == 1)
    ob_start();

$raiz_do_projeto = '/www/';
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."includes/inc_Pagamentos.php"; 
require_once $raiz_do_projeto."includes/gamer/inc_table_saldo.php";
require_once "/www/includes/bourls.php";

set_time_limit ( 60000 ) ;
ini_set("memory_limit","200M");
$time_start = getmicrotime();

//if(!empty($ex_tf_opr_codigo)) echo "EXCLUI [".$ex_tf_opr_codigo."]<br>";

	if(!isset($ncamp) || !$ncamp)    $ncamp       = 'vg_data_inclusao';
	if(!isset($inicial) || !$inicial)  $inicial     = 0;
	if(!isset($range) || !$range)    $range       = 1;
	//echo "range: $range<br>";
	if(!isset($ordem) || !$ordem)    $ordem       = 1;
	//	if($BtnSearch) $inicial     = 0;
	//	if($BtnSearch) $range       = 1;
	if(isset($BtnSearch) && $BtnSearch) $total_table = 0;

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 50; //$qtde_reg_tela;
	$range_qtde   = 50; //$qtde_range_tela;
//echo "qtde_reg_tela: ".$qtde_reg_tela.", qtde_range_tela: ".$qtde_range_tela."<br>";
//echo "tf_v_origem: ".$tf_v_origem."<br>";


	// Preeche 

	if (!empty($tf_v_data_concilia_ini) && $tf_v_data_concilia_ini != '0') {
		
		if (!preg_match('/\d{2}:\d{2}$/', $tf_v_data_concilia_ini)) {
			$tf_v_data_concilia_ini .= " " . date("H:i");
		}
		
		if (!isset($tf_v_data_concilia_fim) || $tf_v_data_concilia_fim == '0') {
			
			$tf_v_data_concilia_fim = $tf_v_data_inclusao_fim . " " . date("H:i");
			
		}
	}
	

	$varsel = "&BtnSearch=1&tf_v_codigo=".str_replace(" ", "", $tf_v_codigo)."&tf_v_status=$tf_v_status";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	$varsel .= "&tf_v_data_concilia_ini=$tf_v_data_concilia_ini&tf_v_data_concilia_fim=$tf_v_data_concilia_fim";
	$varsel .= "&tf_v_data_cancelamento_ini=$tf_v_data_cancelamento_ini&tf_v_data_cancelamento_fim=$tf_v_data_cancelamento_fim";
	$varsel .= "&tf_v_concilia=$tf_v_concilia&tf_d_forma_pagto=$tf_d_forma_pagto&tf_d_banco=$tf_d_banco&tf_d_local=$tf_d_local";
	$varsel .= "&tf_d_data_ini=$tf_d_data_ini&tf_d_data_fim=$tf_d_data_fim";
	$varsel .= "&tf_d_data_inclusao_ini=$tf_d_data_inclusao_ini&tf_d_data_inclusao_fim=$tf_d_data_inclusao_fim";
	$varsel .= "&tf_d_valor_pago=$tf_d_valor_pago&tf_d_num_docto=$tf_d_num_docto";
	$varsel .= "&tf_u_codigo=".str_replace(" ", "", $tf_u_codigo)."&tf_u_nome=$tf_u_nome&tf_u_email=$tf_u_email&tf_u_cpf=$tf_u_cpf";
	$varsel .= "&tf_v_valor=$tf_v_valor&tf_v_qtde_produtos=$tf_v_qtde_produtos&tf_v_qtde_itens=$tf_v_qtde_itens";
	$varsel .= "&tf_v_dep_codigo=$tf_v_dep_codigo&tf_v_bol_codigo=$tf_v_bol_codigo&tf_v_origem=$tf_v_origem";
	$varsel .= "&tf_v_integracao=$tf_v_integracao&tf_v_drupal=$tf_v_drupal";
	$varsel .= "&tf_v_codigo_include=$tf_v_codigo_include";
	$varsel .= "&tf_v_so_depositos=$tf_v_so_depositos&ex_tf_opr_codigo=$ex_tf_opr_codigo";


	//Operadoras		
	$varsel .= "&tf_opr_codigo=$tf_opr_codigo";

	//Canais
	if ($tf_d_forma_pagto == 'E') {
		$varsel .= "&tf_d_canal=$tf_d_canal&com_saldo=$com_saldo&somente_gocash=$somente_gocash";
	}
	
	//Produtos
	if ($tf_produto && is_array($tf_produto))
		if (count($tf_produto) == 1)
			$tf_produto = $tf_produto[0];
		else
			$tf_produto = implode("|",$tf_produto);
	$varsel .= "&tf_produto=$tf_produto";
	if ($tf_produto && $tf_produto != "")
		$tf_produto = explode("|",$tf_produto);
	
	//Valores
	if ($tf_pins && is_array($tf_pins))
		if (count($tf_pins) == 1)
			$tf_pins = $tf_pins[0];
		else
			$tf_pins = implode("|",$tf_pins);
	$varsel .= "&tf_pins=$tf_pins";
	if ($tf_pins && $tf_pins != "")
		$tf_pins = explode("|",$tf_pins);	

	if(!isset($tf_v_codigo_include)) $tf_v_codigo_include = "1";
//if(b_IsUsuarioReinaldo()) { 
//echo "tf_v_status=$tf_v_status<br>";
//	if($tf_v_so_depositos!="1") $tf_v_so_depositos = "1";
//echo "(R) tf_v_so_depositos (forced): ".$tf_v_so_depositos."<br>";
//echo "(R) tf_d_forma_pagto: ".$tf_d_forma_pagto."<br>";
//echo "(R) tf_v_codigo: ".$tf_v_codigo."<br>";
//echo "tf_v_data_inclusao: '$tf_v_data_inclusao_ini' - '$tf_v_data_inclusao_fim'<br>";
//die("Stop");
//echo "<pre>".print_r($_REQUEST, true)."</pre>";
//}
	if(isset($BtnSearch))
	{	

		//Validacao
		$msg = "";
                
                $temData = false;
		//Venda
		//------------------------------------------------------------------
		//codigo
		if($msg == "")
			if($tf_v_codigo){
				$tf_v_codigo = str_replace(" ", "", $tf_v_codigo);
				$tf_v_codigo = str_replace("\t", "", $tf_v_codigo);
				if(!is_csv_numeric($tf_v_codigo)) {
					$msg = "Código da venda deve ser numérico ou lista de números separada por vírgulas.\n";
				}
			}
		//Data
		if($msg == "")
			if($tf_v_data_inclusao_ini || $tf_v_data_inclusao_fim)
			{
				if(verifica_data($tf_v_data_inclusao_ini) == 0)	$msg = "A data inicial da venda é inválida.\n";
				if(verifica_data($tf_v_data_inclusao_fim) == 0)	$msg = "A data final da venda é inválida.\n";
                                
                                if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim)
                                    $temData = true;
			}
		//Data Conciliacao
		
		if($msg == "")
			if($tf_v_data_concilia_ini || $tf_v_data_concilia_fim)
			{
				if(!is_DateTime($tf_v_data_concilia_ini))	$msg = "A data inicial da conciliação da venda é inválida.\n";
				if(!is_DateTime($tf_v_data_concilia_fim))	$msg = "A data final da conciliação da venda é inválida.\n";
                                
                                if($tf_v_data_concilia_ini && $tf_v_data_concilia_fim)
                                    $temData = true;
			}
		//Data Cancelamento
		if($msg == "")
			if($tf_v_data_cancelamento_ini || $tf_v_data_cancelamento_fim)
			{
				if(verifica_data($tf_v_data_cancelamento_ini) == 0)	$msg = "A data inicial do cancelamento é inválida.\n";
				if(verifica_data($tf_v_data_cancelamento_fim) == 0)	$msg = "A data final do cancelamento é inválida.\n";
                                
                                if($tf_v_data_cancelamento_ini && $tf_v_data_cancelamento_fim)
                                    $temData = true;
			}
		//valor
		if($msg == "")
			if($tf_v_valor)
			{
				if(!is_moeda($tf_v_valor))
					$msg = "Valor da venda é inválido.\n";
			}
		//qtde produtos
		if($msg == "")
			if($tf_v_qtde_produtos)
			{
				if(!is_numeric($tf_v_qtde_produtos))
					$msg = "Qtde Produtos da venda deve ser numérico.\n";
			}
		//qtde itens
		if($msg == "")
			if($tf_v_qtde_itens)
			{
				if(!is_numeric($tf_v_qtde_itens))
					$msg = "Qtde Itens da venda deve ser numérico.\n";
			}

		//tf_v_dep_codigo
		if($msg == "")
			if($tf_v_dep_codigo)
			{
				if(!is_numeric($tf_v_dep_codigo))
					$msg = "Código do depósito deve ser numérico.\n";
			}

		//tf_v_bol_codigo
		if($msg == "")
			if($tf_v_bol_codigo)
			{
				if(!is_numeric($tf_v_bol_codigo))
					$msg = "Código do boleto deve ser numérico.\n";
			}

		//tf_v_so_depositos - para busca de deposito em saldo - evita busca express money
		if($msg == "") {
			if($tf_v_so_depositos=="1") {
				$tf_v_origem = "";
			}
		}

		//Dados do Pagamento
		//------------------------------------------------------------------
		//Data
		if($msg == "")
			if($tf_d_data_ini || $tf_d_data_fim)
			{
				if(verifica_data($tf_d_data_ini) == 0)	$msg = "A data inicial dos dados do pagamento é inválida.\n";
				if(verifica_data($tf_d_data_fim) == 0)	$msg = "A data final dos dados do pagamento é inválida.\n";
                                
                                if($tf_d_data_ini && $tf_d_data_fim)
                                    $temData = true;
			}
		
		//Data inclusao
		if($msg == "")
			if($tf_d_data_inclusao_ini || $tf_d_data_inclusao_fim)
			{
				if(verifica_data($tf_d_data_inclusao_ini) == 0)	$msg = "A data de inclusão inicial dos dados do pagamento é inválida.\n";
				if(verifica_data($tf_d_data_inclusao_fim) == 0)	$msg = "A data de inclusão final dos dados do pagamento é inválida.\n";
                                
                                if($tf_d_data_inclusao_ini && $tf_d_data_inclusao_fim)
                                    $temData = true;
			}
                        
                if($msg == "" && $temData === false){
                    $msg = "Por favor preencha pelo menos um dos períodos de data.";
                }
                
		//valor pago
		if($msg == "")
			if($tf_d_valor_pago)
			{
				if(!is_moeda($tf_d_valor_pago))
					$msg = "Valor Pago dos dados do pagamento é inválido.\n";
			}

		//Usuario
		//------------------------------------------------------------------
		//tf_u_codigo
		if($msg == "")
			if($tf_u_codigo)
			{
//				if(!is_numeric($tf_u_codigo))
				$tf_u_codigo = str_replace(" ", "", $tf_u_codigo);
				$tf_u_codigo = str_replace("\t", "", $tf_u_codigo);
				if(!is_csv_numeric($tf_u_codigo)) {
//					$msg = "Código do usuário deve ser numérico.\n";
					$msg = "Código da venda deve ser numérico ou lista de números separada por vírgulas.\n";
				}
			}
	
		//Busca vendas
		if($msg == "")
		{
			$sql  = "select ug.ug_id, ug.ug_email, ug.ug_nome, 
							vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia, vg.vg_data_concilia, vg_usuario_obs, vg_pagto_num_docto, vg_pagto_banco, vg_pagto_local,
							vg.vg_dep_codigo, ".(($tf_v_so_depositos=="1")?"":"vg_")."bol_codigo, \n";
			if ($tf_d_forma_pagto == 'E') {
                                if($tf_v_so_depositos=="1") {
                                    $sql .= "tvgpo.tvgpo_canal, \n";
                                    $sql .= "(case when tvgpo_canal='G' then (case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end) when tvgpo_canal='L' then 'L' when SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' then 'P' else '' end) as comissao_canal, \n";
                                    $sql .= "tvgpo_perc_desconto, tvgpo_canal, \n";
                                }
                                else {
                                    $sql .= "tvgpo.tvgpo_canal, vgm_opr_codigo, \n";
                                    $sql .= "obtem_comissao(vgm.vgm_opr_codigo, (case when tvgpo_canal='G' then (case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end) when tvgpo_canal='L' then 'L' when SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' then 'P' else '' end), vg.vg_data_inclusao, tvgpo_perc_desconto::int) as comissao_epp, \n";
                                    $sql .= "(case when tvgpo_canal='G' then (case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end) when tvgpo_canal='L' then 'L' when SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' then 'P' else '' end) as comissao_canal, \n";
                                    $sql .= "obtem_comissao_publisher(vgm.vgm_opr_codigo, (case when tvgpo_canal='G' then (case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end) when tvgpo_canal='L' then 'L' when SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' then 'P' else '' end), vg.vg_data_inclusao) as comissao_publisher,\n";
                                    $sql .= "opr_nome, tvgpo_perc_desconto, tvgpo_canal, \n";
                                }
			}
			if($tf_v_so_depositos=="1") {
				$sql  .= "		bol_tipo, pag_tipo, \n";
				$sql  .= "		case when (not (bol_tipo is null)) then bol_cesta::varchar when (not (pag_tipo is null) ) then pag_cesta::varchar else 'cesta????'::varchar end as cesta, \n";
				$sql .= "		case when (not pag_tipo='') then pag_valor else (bol_valor-".((($GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL']-$GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2']) < 0)?$GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL']:($GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL']-$GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2'])).") end as valor, 1 as qtde_itens, 1 as qtde_produtos, '' as vg_ex_email \n";
			} else {
				$sql .= "		sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, vg_ex_email \n";
			}
			$sql  .= "	, vg_drupal_order_id \n";

			if ((($tf_d_forma_pagto == 'E')&&(!empty($com_saldo))) || (($tf_d_forma_pagto == 'E')&&(!empty($somente_gocash)))) {
				$sql .= ", scfu_valor, scfu_qtde, scf_canal \n";
			}

			$sql .= "from tb_venda_games vg \n";
			if($tf_v_so_depositos=="1") {
				$sql .= "inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id 
							left outer join ( 
									select idvenda as pag_vg_id, (total/100-taxas) as pag_valor, iforma as pag_tipo, cesta as pag_cesta 
									from tb_pag_compras pg 
									where (pg.tipo_cliente = 'M') 
									) pag on pag.pag_vg_id = vg.vg_id

								left outer join ( 
									select bol_codigo, bol_venda_games_id as bol_vg_id, bol_valor as bol_valor, 'B' || substr(bol_documento, 1, 1) as bol_tipo, 'Boleto Depósito em Saldo Gamer' as bol_cesta 
									from boletos_pendentes bol 
									where substr(bol_documento, 1, 1)='6'	
									) bol on bol.bol_vg_id = vg.vg_id 	
						";
								// (substr(bol_documento, 1, 1)='2') or (substr(bol_documento, 1, 1)='3') or (
								//	"pg.status=3 and "
								//	"bol_aprovado = 1 and "
			} else {
				$sql .= "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
						 inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id  \n";
			}
			if ($tf_d_forma_pagto == 'E') {
                            if($tf_v_so_depositos=="1") {
				$sql .= "inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id=vg.vg_id \n";
                            }
                            else {
				$sql .= "inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id=vg.vg_id \n";
				//$sql .= "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id=vg.vg_id \n";
				$sql .= "inner join operadoras o on o.opr_codigo = vgm.vgm_opr_codigo \n";
                            }
			}
			if ((($tf_d_forma_pagto == 'E')&&(!empty($com_saldo))) || (($tf_d_forma_pagto == 'E')&&(!empty($somente_gocash)))) {
				$sql .= "inner join tb_pag_compras pag2 on pag2.idvenda=vg.vg_id \n";
				$sql .= "	left outer join (
								(select sum(scfu_valor) as scfu_valor, count(*) as scfu_qtde, vg_id, scf_canal
								from (
									select scfu_valor, scfu.vg_id, scf.scf_canal
									from saldo_composicao_fifo_utilizado scfu 
										INNER JOIN saldo_composicao_fifo scf ON (scfu.scf_id=scf.scf_id) 
									) scfu_int 		
								group by vg_id, scf_canal
								)  
							) scfu on scfu.vg_id = pag2.idvenda
						";
			}

 			$sql .= "where 1=1 \n";
			if (($tf_d_forma_pagto == 'E')&&(!empty($tf_d_canal))) {
				$sql .= " and tvgpo.tvgpo_canal = '".$tf_d_canal."' \n";
			}
			if (($tf_d_forma_pagto == 'E')&&(!empty($com_saldo))) {
				$sql .= " and pag2.valorpagtosaldo > 0 \n";
			}
			if (($tf_d_forma_pagto == 'E')&&(!empty($somente_gocash))) {
				$sql .= " and ((pag2.valorpagtogocash > 0) or (valorpagtosaldo>0 and scf_canal = 'C')) \n";
			}
			if($tf_v_so_depositos=="1") {
				$sql .= " and vg.vg_deposito_em_saldo = 1 \n";
			}
			if($tf_v_codigo && ($tf_v_codigo_include=="1" || $tf_v_codigo_include=="-1")) {
				if($tf_v_codigo_include=="1") {
					if($tf_v_codigo) 			$sql .= " and vg.vg_id in (".$tf_v_codigo.") \n";
				} elseif($tf_v_codigo_include=="-1") {
					if($tf_v_codigo) 			$sql .= " and vg.vg_id not in (".$tf_v_codigo.") \n";
				}
			}
			if($tf_v_status) 			$sql .= " and vg.vg_ultimo_status = ".$tf_v_status." \n";
			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) $sql .= " and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' \n";
			if($tf_v_data_concilia_ini && $tf_v_data_concilia_fim) $sql .= " and vg.vg_data_concilia between '".formata_data_ts($tf_v_data_concilia_ini, 2, true, false)."' and '".formata_data_ts($tf_v_data_concilia_fim, 2, true, false)."' \n";
			if($tf_v_data_cancelamento_ini && $tf_v_data_cancelamento_fim){
				$sql .= " and vg.vg_id in (select vgh_vg_id from tb_venda_games_historico where vgh_status = " . $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'] . " and vgh_data_inclusao between '".formata_data($tf_v_data_cancelamento_ini,1)." 00:00:00' and '".formata_data($tf_v_data_cancelamento_fim,1)." 23:59:59')";
			} 
			if(!is_null($tf_v_concilia) && $tf_v_concilia != "") $sql .= " and vg.vg_concilia = '".$tf_v_concilia."' \n";
			if($conciliado_manualmente == "1") {
				$sql .= " and vg_usuario_obs like '%Conciliado manualmente%' \n";
			}
			if($tf_d_forma_pagto) {

				if($tf_d_forma_pagto=="OL") {
					// Todas as formas online
//echo "getSQLWhereParaVendaPagtoOnline(): '".getSQLWhereParaVendaPagtoOnline()."'<br>";
//					$sql .= " and ( vg.vg_pagto_tipo = " . $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']. " or vg.vg_pagto_tipo = " . $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']. " or vg.vg_pagto_tipo = " . $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']. " or vg.vg_pagto_tipo = ".$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC ." or vg.vg_pagto_tipo = " . $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC. " )";
					$sql .= " and ".getSQLWhereParaVendaPagtoOnline()." ";
				} else {
					// Substitui pelo código numerico se for necessário
					$sql .= " and vg.vg_pagto_tipo = ".getCodigoNumericoParaPagto($tf_d_forma_pagto)."";
				}
			}
			if($tf_v_dep_codigo) 		$sql .= " and vg.vg_dep_codigo = '".$tf_v_dep_codigo."' ";
			if($tf_v_bol_codigo) 		$sql .= " and ".(($tf_v_so_depositos=="1")?"":"vg_")."bol_codigo = '".$tf_v_bol_codigo."' ";
			if($tf_d_banco) 			$sql .= " and vg.vg_pagto_banco = '".$tf_d_banco."' ";
			if($tf_d_local) 			$sql .= " and vg.vg_pagto_local = '".$tf_d_local."' ";
			if($tf_d_data_ini && $tf_d_data_fim) $sql .= " and vg.vg_pagto_data between '".formata_data($tf_d_data_ini,1)." 00:00:00' and '".formata_data($tf_d_data_fim,1)." 23:59:59'";
			if($tf_d_data_inclusao_ini && $tf_d_data_inclusao_fim) $sql .= " and vg.vg_pagto_data_inclusao between '".formata_data($tf_d_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_d_data_inclusao_fim,1)." 23:59:59'";
			if($tf_d_valor_pago) 		$sql .= " and vg.vg_pagto_valor_pago = ".str_replace(",", ".", moeda2numeric($tf_d_valor_pago))." ";
			if($tf_d_num_docto) 		$sql .= " and upper(vg.vg_pagto_num_docto) like '%". strtoupper($tf_d_num_docto)."%' ";
		
			// Para permitir buscar E-money por email cadastrado (todos tem o ID de Patrick)
			if($tf_v_origem == "exmo"){	
				$sql .= " and ug.ug_id = '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' ";
				if($tf_u_email) 		$sql .= " and upper(vg.vg_ex_email) like '%".strtoupper($tf_u_email)."%' ";
			} elseif($tf_v_origem == "mo") {
				$sql .= " and (not ug.ug_id = '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."') ";
//				if($tf_u_codigo)		$sql .= " and ug.ug_id = '".$tf_u_codigo."' ";
				if($tf_u_codigo)		$sql .= " and ug.ug_id in (".$tf_u_codigo.") ";
				if($tf_u_nome) 			$sql .= " and upper(ug.ug_nome) like '%".strtoupper($tf_u_nome)."%' ";
				if($tf_u_email) 		$sql .= " and upper(ug.ug_email) like '%".strtoupper($tf_u_email)."%' ";
				if($tf_u_cpf) 			$sql .= " and ug.ug_cpf like '%".$tf_u_cpf."%' ";
			} else {
//				if($tf_u_codigo)		$sql .= " and ug.ug_id = '".$tf_u_codigo."' ";
				if($tf_u_codigo)		$sql .= " and ug.ug_id in (".$tf_u_codigo.") ";
				if($tf_u_nome) 			$sql .= " and upper(ug.ug_nome) like '%".strtoupper($tf_u_nome)."%' ";
				if($tf_u_email) 		$sql .= " and (upper(ug.ug_email) like '%".strtoupper($tf_u_email)."%' or upper(vg.vg_ex_email) like '%".strtoupper($tf_u_email)."%' )";
				if($tf_u_cpf) 			$sql .= " and ug.ug_cpf like '%".$tf_u_cpf."%' ";
			}
			if($tf_v_integracao) {	
				if($tf_v_integracao==1) {	
					$sql .= " and (not (vg_integracao_parceiro_origem_id is null or vg_integracao_parceiro_origem_id='')) ";
				} elseif($tf_v_integracao==-1) {	
					$sql .= " and (vg_integracao_parceiro_origem_id is null or vg_integracao_parceiro_origem_id='') ";
				}
			}
			if($tf_v_drupal) {	
				if($tf_v_drupal==1) {	
					$sql .= " and (vg_drupal = 1) ";
				} elseif($tf_v_drupal==-1) {	
					$sql .= " and (vg_drupal = 0) ";
				}
			}

			//Produtos
			if ($tf_produto && is_array($tf_produto))
				if (count($tf_produto) == 1)
						$sql .= " and upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto[0])) . "%' ";	
				else
				{
					$sql .= " and (";
					foreach($tf_produto as $tf_produto_id => $tf_produto_row)	
						if ($tf_produto_id == count($tf_produto) - 1)
							$sql .= "upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto_row)) . "%')";
						else
							$sql .= "upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto_row)) . "%' or ";
				}

			//Valores
			if ($tf_pins && is_array($tf_pins))
				if (count($tf_pins) == 1)
						$sql .= " and vgm.vgm_valor = " . moeda2numeric($tf_pins[0]) . " ";	
				else
				{
					$sql .= " and (";
					foreach($tf_pins as $tf_pins_id => $tf_pins_row)	
						if ($tf_pins_id == count($tf_pins) - 1)
							$sql .= "vgm.vgm_valor = " . moeda2numeric($tf_pins_row) . ")";
						else
							$sql .= "vgm.vgm_valor = " . moeda2numeric($tf_pins_row) . " or ";
				}
			
			//Operadoras
			if($tf_opr_codigo) 	{
				if(empty($ex_tf_opr_codigo)) {
					$sql .= " and vgm.vgm_opr_codigo = ".$tf_opr_codigo." ";
				}
				else {
					$sql .= " and vgm.vgm_opr_codigo != ".$tf_opr_codigo." ";
				}
			}//end if($tf_opr_codigo)

			if($tf_v_so_depositos=="1") {
//				$sql .= "	and ((vg_pagto_tipo = 2 and bol.bol_vg_id = vg.vg_id) or (vg_pagto_tipo > 4 and pag.pag_vg_id = vg.vg_id)) \n";
				$sql .= "	and ((vg_pagto_tipo = 2 ) or (vg_pagto_tipo > 4 and pag.pag_vg_id = vg.vg_id)) \n";
			} 

			$sql .= "\n";
			$sql .= " group by ug.ug_id, ug.ug_email, ug.ug_nome, 
        						vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia, vg.vg_data_concilia, vg_usuario_obs, vg_pagto_num_docto, vg_pagto_banco, vg_pagto_local,
        						vg.vg_dep_codigo, ".(($tf_v_so_depositos=="1")?"":"vg_")."bol_codigo, vg_ex_email, vg_drupal_order_id ";
			if ($tf_d_forma_pagto == 'E') {
                            if($tf_v_so_depositos=="1") {
				$sql .= ", tvgpo.tvgpo_canal, vg.vg_ug_id, tvgpo_perc_desconto::int, tvgpo_perc_desconto, tvgpo_canal";
                            }
                            else {
				$sql .= ", tvgpo.tvgpo_canal, vgm.vgm_opr_codigo, vg.vg_ug_id, tvgpo_perc_desconto::int, opr_nome, vgm_opr_codigo, tvgpo_perc_desconto, tvgpo_canal";
                            }
			}
			if($tf_v_so_depositos=="1") {
				$sql .= ", pag_tipo, pag_valor, bol_tipo, bol_valor, cesta ";
			} 
			if ((($tf_d_forma_pagto == 'E')&&(!empty($com_saldo))) || (($tf_d_forma_pagto == 'E')&&(!empty($somente_gocash)))) {
				$sql .= ", scfu_valor, scfu_qtde, scf_canal \n";
			}

			$sql .= "\n";
			$sql .= " having 1=1 ";
			if($tf_v_so_depositos=="1") {
				// Do nothing
			} else {
				if($tf_v_valor) $sql .= " and sum(vgm.vgm_valor * vgm.vgm_qtde) = ".moeda2numeric($tf_v_valor)." ";
				if($tf_v_qtde_produtos) $sql .= " and count(*) = ".$tf_v_qtde_produtos." ";
				if($tf_v_qtde_itens) $sql .= " and sum(vgm.vgm_qtde) = ".$tf_v_qtde_itens." ";
			}
if(b_IsUsuarioReinaldo()||b_IsUsuarioWagner()) { 

// tb_pag_compras usa iforma::char(1)
//echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>";
//die("Stop");
}		
//echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>";

			$rs_venda = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_venda);

$b_lista = true;
			//Total Geral
			$totalGeral_valor = 0;
			$totalGeral_qtde_itens = 0;
			if($total_table > 0)
			{
				if($b_lista) $lista_vg_id = "";
				$lista_ug_id = "";
				$lista_ug_email = "";
				$n_lista_ug_id = 0;
				$n_lista_ug_email = 0;
				$n_lista_id_ped = 0;
				while($rs_venda_row = pg_fetch_array($rs_venda))
				{
					$totalGeral_valor += $rs_venda_row['valor'];
					$totalGeral_qtde_itens += $rs_venda_row['qtde_itens'];

					// lista de vg_id
					if($b_lista) $lista_vg_id .= $rs_venda_row['vg_id'].", ";
					if(strpos($lista_ug_id, $rs_venda_row['ug_id'])===false) {
						if($lista_ug_id!="") $lista_ug_id .= ", ";
						$lista_ug_id .= $rs_venda_row['ug_id'];
						$n_lista_ug_id ++;
					}
					if(strpos($lista_ug_email, $rs_venda_row['ug_email'])===false) {
						if($lista_ug_email!="") $lista_ug_email .= ", ";
						$lista_ug_email .= $rs_venda_row['ug_email'];
						$n_lista_ug_email ++;
					}
				}
				$lista_id_ped = $lista_vg_id;
				$n_lista_id_ped = $total_table;
			}
if($b_lista) {
if(b_IsUsuarioReinaldo()||b_IsUsuarioWagner()) { 
//echo "(R) $lista_vg_id<br>";
//echo "(R) $lista_ug_id<br>";
//echo "(R) $lista_ug_email<br>";
}
}
/*
if(function_exists('getCodigoNumericoParaPagto')) {
echo "***";
}
*/
			//Ordem
			$sql .= " order by ".$ncamp;
			if($ordem == 1)
			{
				$sql .= " desc ";
				$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
			} else {
				$sql .= " asc ";
				$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
			}
		
                        if(!isset($_GET["downloadCsv"])){
                            $sql .= " limit ".$max; 
                            $sql .= " offset ".$inicial;
                        }
                        
			

//if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//die("Stop");
//}
			if($total_table == 0) 
			{
				$msg = "Nenhuma venda encontrada.\n";
			} else {		
				$rs_venda = SQLexecuteQuery($sql);
				
				if($max + $inicial > $total_table)
					$reg_ate = $total_table;
				else
					$reg_ate = $max + $inicial;
			}		
		}
	}
	
	//Operadoras / Produtos / Valores
	$sql = "select * from operadoras ope where opr_status = '1' order by opr_nome";
	$rs_operadoras = SQLexecuteQuery($sql);
	if($tf_opr_codigo) {
		$sql = "select ogp_id,ogp_nome from tb_operadora_games_produto where ogp_opr_codigo = " . $tf_opr_codigo . "";
		$rs_oprProdutos = SQLexecuteQuery($sql);
		$sql = "select pin_valor from pins where opr_codigo = " . $tf_opr_codigo . " group by pin_valor order by pin_valor;";
		$rs_oprPins = SQLexecuteQuery($sql);
	}
	
	ob_end_flush();
?>
		<!--trecho necessário para o calendario com data hora-->
		<link rel="stylesheet" type="text/css" href="/css/anytime512.css" />
		<!--link rel="stylesheet" type="text/css" href="<?= EPREPAG_URL_HTTP ?>/prepag2/js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css" /-->
		<script language="JavaScript" src="/js/anytime512.js"></script>
		<script language="JavaScript" src="/js/anytimetz.js"></script>
		<script language="JavaScript" src="/js/anytimeBR.js"></script>
		<!--fim do trecho necessário para o calendario com data hora-->
        <link href="https://<?php echo $server_url_complete ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
        <script src="https://<?php echo $server_url_complete ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
        <script src="https://<?php echo $server_url_complete ;?>/js/global.js"></script>
		
		<script language="javascript">
			function GP_popupAlertMsg(msg) 
			{ //v1.0
  				document.MM_returnValue = alert(msg);
			}

			function GP_popupConfirmMsg(msg) 
			{ //v1.0
  				document.MM_returnValue = confirm(msg);
			}

			$(document).ready(function () {
                
                var optDate = new Object();
                    optDate.interval = 10000;

                setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
                setDateInterval('tf_v_data_cancelamento_ini','tf_v_data_cancelamento_fim',optDate);
                setDateInterval('tf_d_data_ini','tf_d_data_fim',optDate);
                setDateInterval('tf_d_data_inclusao_ini','tf_d_data_inclusao_fim',optDate);
                
			    const d = new Date();
	            let year = d.getFullYear();
				
                $("#tf_v_data_concilia_ini").AnyTime_noPicker().AnyTime_picker({baseYear: 2008,
                    earliest: rangeDemoConv.format(new Date(2008,1,1,0,0,0)),
                    format: rangeDemoFormat,
                    latest: rangeDemoConv.format(new Date(year,11,31,23,59,59)),
                    dayAbbreviations: ['DOM','SEG','TER','QUA','QUI','SEX','SAB'],
                    labelDayOfMonth: 'Dia do Mês',
                    labelHour: 'Hora',
                    labelMinute: 'Minuto',
                    labelMonth: 'Mês',
                    labelTitle: 'Selecione a Data e Hora',
                    labelYear: 'Ano',
                    monthAbbreviations: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
                });
                
                $("#tf_v_data_concilia_fim").AnyTime_noPicker().AnyTime_picker({baseYear: 2008,
                    earliest: rangeDemoConv.format(new Date(2008,1,1,0,0,0)),
                    format: rangeDemoFormat,
                    latest: rangeDemoConv.format(new Date(year,11,31,23,59,59)),
                    dayAbbreviations: ['DOM','SEG','TER','QUA','QUI','SEX','SAB'],
                    labelDayOfMonth: 'Dia do Mês',
                    labelHour: 'Hora',
                    labelMinute: 'Minuto',
                    labelMonth: 'Mês',
                    labelTitle: 'Selecione a Data e Hora',
                    labelYear: 'Ano',
                    monthAbbreviations: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
                });
                
				$('#tf_v_origem').change(function(){
					if ($('#tf_v_origem').val() == "exmo") {
						$('#DivCodigoNome').hide();
						$('#DivCpf').hide();
						$('#DivCpfInput').hide();
					}else{
						$('#DivCodigoNome').show();
						$('#DivCpf').show();
						$('#DivCpfInput').show();
					}
				});
                
			<?php
			if ($tf_v_origem == "exmo"){
			?>
			$('#DivCodigoNome').hide();
			$('#DivCpf').hide();
			$('#DivCpfInput').hide();
			<?php
			}
			?>
				
				//Testando se a froma de pagamento é EPP CASH e carregando o combo se sim
				<?php if ($tf_d_forma_pagto == 'E') {?>
				$('#mostraCanais').html("<tr><td class='texto'>Canal de Distribuição do PIN CASH Utilizado: </td><td><select name='tf_d_canal' id='tf_d_canal' class='form2'><option value=''<?php if($tf_d_canal == "") echo " selected" ?>>Todos</option><?php foreach ($DISTRIBUIDORAS_CANAIS as $key => $value){ ?><option value='<?php echo $key; ?>' <?php if ($tf_d_canal == $key) echo "selected";?>><?php echo $value; ?></option><?php } ?></select></td><td class='texto'>&nbsp;&nbsp;&nbsp;Exibir Vendas com Saldo(Parcialmente/Totalmente): </td><td class='texto'>&nbsp;<input name='com_saldo' type='checkbox' id='com_saldo' value='1' <?php if(!empty($com_saldo)) echo "checked";?>/> Sim &nbsp; | Somente GoCASH <input name='somente_gocash' type='checkbox' id='somente_gocash' value='1' <?php if(!empty($somente_gocash)) echo "checked";?>/> <a href='#' onclick='javascript:$(\"#ajudagocash\").show();'>?</a></td></tr><tr><td class='texto' colspan='4' align='right' onclick='javascript:$(\"#ajudagocash\").hide();'><div id='ajudagocash'>Este filtro captura todas as vendas que foram pagas através de GoCASH <br>incluindo as vendas pagas com saldo composto por depósito de GoCASH.</div></td></tr>");
				<?php
					// Este filtro captura somente as vendas que foram pagas através de GoCASH.<br>Isto NÃO inclui as vendas pagas com saldo composto por depósito de GoCASH.
					?>
				$('#ajudagocash').hide();
				<?php }?>

				//Ao selecionar a forma de pagamento
				$('#tf_d_forma_pagto').change(function(){
					var id = $(this).val();
					//alert(id);
					if (id == 'E') {
						$('#mostraCanais').html("<tr><td class='texto'>Canal de Distribuição do PIN CASH Utilizado: </td><td><select name='tf_d_canal' id='tf_d_canal' class='form2'><option value=''<?php if($tf_d_canal == "") echo " selected" ?>>Todos</option><?php foreach ($DISTRIBUIDORAS_CANAIS as $key => $value){ ?><option value='<?php echo $key; ?>' <?php if ($tf_d_canal == $key) echo "selected";?>><?php echo $value; ?></option><?php } ?></select></td><td class='texto'>&nbsp;&nbsp;&nbsp;Exibir Vendas com Saldo(Parcialmente/Totalmente): </td><td class='texto'>&nbsp;<input name='com_saldo' type='checkbox' id='com_saldo' value='1'/> Sim &nbsp; | Somente GoCASH <input name='somente_gocash' type='checkbox' id='somente_gocash' value='1' <?php if(!empty($somente_gocash)) echo "checked";?>/>  <a href='#' onclick='javascript:$(\"#ajudagocash\").show();'>?</a></td></tr><tr><td class='texto' colspan='4' align='right' onclick='javascript:$(\"#ajudagocash\").hide();'><div id='ajudagocash'>Este filtro captura todas as vendas que foram pagas através de GoCASH <br>incluindo as vendas pagas com saldo composto por depósito de GoCASH.</div></td></tr>");
						$('#ajudagocash').hide();
					}
					else {
						$('#mostraCanais').html("");
					}
				});
				<?php
				// Este filtro captura somente as vendas que foram pagas através de GoCASH.<br>Isto NÃO inclui as vendas pagas com saldo composto por depósito de GoCASH.	
				?>
				//Ao selecionar a operadora
				$('#tf_opr_codigo').change(function(){
					var id = $(this).val();
					//alert(id);
					
					$.ajax({
						type: "POST",
						url: "/ajax/gamer/ajaxProdutoComPesquisaVendas.php",
						data: "id="+id,
						beforeSend: function(){
							$('#mostraProdutos').html("Aguarde...");
						},
						success: function(html){
							//alert('produto');
							$('#mostraProdutos').html(html);
						},
						error: function(){
							alert('erro produto');
						}
					});

					$.ajax({
						type: "POST",
						url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
						data: "id="+id,
						beforeSend: function(){
							$('#mostraValores').html("Aguarde...");
						},
						success: function(html){
							//alert('valor');
							$('#mostraValores').html(html);
						},
						error: function(){
							alert('erro valor');
						}
					});
				});
			});
						 
		</script>
        <div class="col-md-12">
            <ol class="breadcrumb top10">
                <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
                <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
                <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
            </ol>
        </div>
		
        <table class="table txt-preto fontsize-pp">
  			<tr> 
    			<td >
                <form name="form1" id="form1" method="post" action="com_pesquisa_vendas.php<?php
				if($tf_v_origem == "exmo")
				{	
					echo "?tf_v_origem=exmo";
				}
				?>">
                <table class="table">
          			<tr bgcolor="#F5F5FB"> 
            			<td align="right"><input type="button" value="Buscar" class="BtnSearch btn btn-info btn-sm"></td>
          			</tr>
				</table>
                <table class="table">
          			<tr bgcolor="#FFFFFF"> 
            			<td colspan="6" bgcolor="#ECE9D8" class="texto">Venda</font></td>
          			</tr>
				<?php 
//					if(b_IsUsuarioReinaldo()) { 
				 ?>
				  <tr bgcolor="#F5F5FB"> 
					<td class="texto">Tipo de pedido</font></td>
					<td>
						<select name="tf_v_so_depositos" class="form2">
							<option value="" <?php if($tf_v_so_depositos != "1") echo "selected" ?>>Venda de PINs</option>
							<option value="1" <?php if($tf_v_so_depositos == "1") echo "selected" ?>>Depósito em Saldo (Gamer)</option>
						</select>
					</td>
					<td width="100" class="texto">&nbsp;</font></td>
					<td>&nbsp;</td>
				  </tr>
				<?php 
//					 } 
				 ?>
          			<tr bgcolor="#F5F5FB"> 
            			<td width="100" class="texto">Origem</font></td>
            			<td>
							<select name="tf_v_origem" id='tf_v_origem' class="form2">
								<option value="">Selecione</option>
								<option value="mo" <?php if($tf_v_origem == "mo") echo "selected" ?>>Money</option>
								<option value="exmo" <?php if($tf_v_origem == "exmo") echo "selected" ?>>Express Money</option>
							</select>
						</td>
						<td colspan="2">&nbsp;</td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td width="100" class="texto">Número do Pedido</font></td>
            			<td>
              				<nobr><input name="tf_v_codigo" type="text" class="form2" value="<?php echo $tf_v_codigo ?>" size="30">
							<select name="tf_v_codigo_include">
								<option value="1"<?php if ($tf_v_codigo_include=="1") echo " selected"?>>Incluir lista</option>
								<option value="-1"<?php if ($tf_v_codigo_include=="-1") echo " selected"?>>EXCLUIR lista</option>
							</select></nobr>
						</td>
            			<td class="texto">Status</font></td>
						<td>
							<select name="tf_v_status" class="form2">
								<option value="" <?php if($tf_v_status == "") echo "selected" ?>>Selecione</option>
								<?php foreach ($STATUS_VENDA_DESCRICAO as $statusId => $statusNome){ ?>
									<option value="<?php echo $statusId; ?>" <?php if ($tf_v_status == $statusId) echo "selected";?>><?php echo $statusId . " - " . substr($statusNome, 0, strpos($statusNome, '.')); ?></option>
								<?php } ?>
							</select>
						</td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td class="texto">Qtde Produtos</font></td>
            			<td>
              				<input name="tf_v_qtde_produtos" type="text" class="form2" value="<?php echo $tf_v_qtde_produtos ?>" size="7" maxlength="7">
						</td>
            			<td class="texto">Qtde Itens Total</font></td>
            			<td>
              				<input name="tf_v_qtde_itens" type="text" class="form2" value="<?php echo $tf_v_qtde_itens ?>" size="7" maxlength="7">
						</td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td class="texto">Valor</font></td>
            			<td>
              				<input name="tf_v_valor" type="text" class="form2" value="<?php echo $tf_v_valor ?>" size="10" maxlength="10">
						</td>
            			<td class="texto">Data Inclusão</font></td>
            			<td class="texto">
              				<input name="tf_v_data_inclusao_ini" type="text" class="form datePopUpCalendar" id="tf_v_data_inclusao_ini" value="<?php echo ($tf_v_data_inclusao_ini) ? $tf_v_data_inclusao_ini : date("d/m/Y"); ?>" size="9" maxlength="10">
              				a 
              				<input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo ($tf_v_data_inclusao_fim) ? $tf_v_data_inclusao_fim : date("d/m/Y"); ?>" size="9" maxlength="10">
						</td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td class="texto">Conciliação</font></td>
						<td>
							<select name="tf_v_concilia" class="form2">
								<option value="" <?php if($tf_v_concilia == "") echo "selected" ?>>Selecione</option>
								<option value="1" <?php if ($tf_v_concilia == "1") echo "selected";?>>Conciliado</option>
								<option value="0" <?php if ($tf_v_concilia == "0") echo "selected";?>>Não conciliado</option>
							</select>
						</td>
            			<td class="texto">Data Conciliação <nobr>DD/MM/AAAA hh:mm &nbsp;</nobr></font></td>
						<td class="texto">
						  <input name="tf_v_data_concilia_ini" type="text" class="form datePopUpCalendar" id="tf_v_data_concilia_ini" value="<?php echo $tf_v_data_concilia_ini ?>" size="15" maxlength="16" onclick="javascript:teste(tf_v_data_concilia_ini);">
						  a 
						  <input name="tf_v_data_concilia_fim" type="text" class="form datePopUpCalendar" id="tf_v_data_concilia_fim" value="<?php echo $tf_v_data_concilia_fim ?>" size="15" maxlength="16" onclick="javascript:teste(tf_v_data_concilia_fim);">
						  <a href="#" onclick="document.form1.tf_v_data_concilia_ini.value='';document.form1.tf_v_data_concilia_fim.value='';">Limpar</a>
						</td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td class="texto">Conciliação Manual</font></td>
           	 			<td>
							<select name="conciliacao_manual" class="form2">
								<option value="" <?php if($conciliacao_manual == "") echo "selected"; ?>>Selecione</option>
								<option value="1" <?php if ($conciliacao_manual == "1") echo "selected"; ?>>Sim</option>
								<option value="0" <?php if ($conciliacao_manual == "0") echo "selected"; ?>>Não</option>
							</select>
						</td>
            			<td class="texto">Data Cancelamento</font></td>
            			<td class="texto">
              				<input name="tf_v_data_cancelamento_ini" type="text" class="form datePopUpCalendar" id="tf_v_data_cancelamento_ini" value="<?php echo $tf_v_data_cancelamento_ini ?>" size="9" maxlength="10">
              				a 
              				<input name="tf_v_data_cancelamento_fim" type="text" class="form" id="tf_v_data_cancelamento_fim" value="<?php echo $tf_v_data_cancelamento_fim ?>" size="9" maxlength="10">
						</td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td class="texto">Cód Depósito</font></td>
            			<td>
              				<input name="tf_v_dep_codigo" type="text" class="form2" value="<?php echo $tf_v_dep_codigo ?>" size="7" maxlength="7">
						</td>
            			<td class="texto">Cód Boleto</font></td>
            			<td>
              				<input name="tf_v_bol_codigo" type="text" class="form2" value="<?php echo $tf_v_bol_codigo ?>" size="7" maxlength="7">
						</td>
          			</tr>
          			<tr bgcolor="#FFFFFF"> 
            			<td colspan="4" bgcolor="#ECE9D8" class="texto">Dados do Pagamento</font></td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td width="100" class="texto">Forma de Pagamento</td>
            			<td colspan="3">
							<select name="tf_d_forma_pagto" id="tf_d_forma_pagto" class="form2">
								<option value="" <?php if($tf_d_forma_pagto == "") echo "selected" ?>>Selecione</option>
								<option value="OL" <?php if($tf_d_forma_pagto == "OL") echo "selected" ?>>OL - Apenas formas de pagamento online</option>
								<?php foreach ($FORMAS_PAGAMENTO_DESCRICAO as $formaId => $formaNome){ ?>
									<option value="<?php echo $formaId; ?>" <?php if ($tf_d_forma_pagto == $formaId) echo "selected";?>><?php echo $formaId . " - " . $formaNome; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr bgcolor="#F5F5FB"> 
						<td colspan="4">
							<div id='mostraCanais'>
							</div>
						</td>
		  			<tr>
					<tr bgcolor="#F5F5FB">
            			<td width="100" class="texto">Banco</td>
            			<td colspan="3">
							<select name="tf_d_banco" class="form2" onChange="populate_local(document.form1.tf_d_banco, document.form1.tf_d_local, '');">
								<option value="" <?php if($tf_d_banco == "") echo "selected" ?>>Selecione</option>
								<?php foreach ($PAGTO_BANCOS as $bancoId => $bancoNome){ ?>
									<option value="<?php echo $bancoId; ?>" <?php if ($tf_d_banco == $bancoId) echo "selected";?>><?php echo $bancoNome; ?></option>
								<?php } ?>
							</select>
						</td>
		  			</tr>
		  			<tr bgcolor="#F5F5FB">
            			<td width="100" class="texto">Local</td>
            			<td colspan="3">
							<select name="tf_d_local" class="form2"></select>
						</td>
		  			</tr>
					<script>
						function populate_local(combo1, combo2, combo2_default)
						{
							opcoesAr = new Array(new Array("","Selecione"));

							<?php foreach ($PAGTO_BANCOS as $bancoId => $bancoNome){ ?>
								if(combo1[combo1.selectedIndex].value == "<?php echo $bancoId?>")
								{ 
									<?php if(!empty($PAGTO_LOCAIS[$bancoId])){
                                        foreach ($PAGTO_LOCAIS[$bancoId] as $localId => $localNome){ ?>
										opcoesAr[opcoesAr.length] = new Array("<?php echo $localId?>","<?php echo $localNome?>");
                                    <?php }} ?>
								}
							<?php } ?>
		
							//limpa combo
							for(var i=combo2.length-1; i>=0; i--) combo2.options[i] = null;
							//popula combo
							for(var i=0; i<opcoesAr.length; i++) combo2.options[i] = new Option(opcoesAr[i][1], opcoesAr[i][0]);
							//seleciona opcao
							for(var i=combo2.length-1; i>=0; i--) if(combo2[i].value == combo2_default) combo2.selectedIndex = i;
						} 
					</script>
					<script>populate_local(document.form1.tf_d_banco, document.form1.tf_d_local, "<?php echo $tf_d_local?>");</script>

					<script language="JavaScript">
					<!--
					function get_vg_id() {
						var numdoc = document.getElementById('tf_numdoc').value;
					//	alert("numdoc: "+numdoc);
						// 000000000011111111112222222222333333333344444444445555
						// 012345678901234567890123456789012345678901234567890123
						//          6  75484 0
						// 34191.75264 75484.010444 48975.650002 1 48220000001550
						var vg_id = numdoc.substr(9,1)+numdoc.substr(12,5)+numdoc.substr(18,2);
					//	alert("vg_id: "+vg_id);
						document.getElementById('tf_d_num_docto').value = vg_id;

					}
					//-->
					</script>
		  			
					<tr bgcolor="#F5F5FB">
            			<td class="texto" colspan="2">&nbsp;<input type="button" value="Obtem 'N. Docto' de código de barras Itaú" onClick="get_vg_id()">&nbsp;</font></td>
            			<td class="texto" colspan="2">cod.barras.Itaú&nbsp;<input type="text" name="tf_numdoc" id="tf_numdoc" value="" size="54" maxlength="54"></font></td>
          			</tr>

					<tr bgcolor="#F5F5FB">
            			<td class="texto">N. Docto</font></td>
            			<td>
              				<input name="tf_d_num_docto" id="tf_d_num_docto" type="text" class="form2" value="<?php echo $tf_d_num_docto ?>" size="25" maxlength="15">
						</td>
            			<td class="texto">Data Informada</font></td>
            			<td class="texto">
              				<input name="tf_d_data_ini" type="text" class="form datePopUpCalendar" id="tf_d_data_ini" value="<?php echo $tf_d_data_ini ?>" size="9" maxlength="10">
              				a 
              				<input name="tf_d_data_fim" type="text" class="form" id="tf_d_data_fim" value="<?php echo $tf_d_data_fim ?>" size="9" maxlength="10">
						</td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td class="texto">Valor Pago</font></td>
            			<td>
              				<input name="tf_d_valor_pago" type="text" class="form2" value="<?php echo $tf_d_valor_pago ?>" size="7" maxlength="7">
						</td>
            			<td class="texto">Data Inclusão</font></td>
            			<td class="texto">
              				<input name="tf_d_data_inclusao_ini" type="text" class="form datePopUpCalendar" id="tf_d_data_inclusao_ini" value="<?php echo $tf_d_data_inclusao_ini ?>" size="9" maxlength="10">
              				a 
              				<input name="tf_d_data_inclusao_fim" type="text" class="form" id="tf_d_data_inclusao_fim" value="<?php echo $tf_d_data_inclusao_fim ?>" size="9" maxlength="10">
						</td>
          			<tr bgcolor="#FFFFFF" id="divUsuario"> 
            			<td colspan="4" bgcolor="#ECE9D8" class="texto">
								
								<table width="100%" border="0" cellpadding="0" bgcolor="#FFFFFF">
		          				<tr bgcolor="#FFFFFF"> 
		            				<td colspan="4" bgcolor="#ECE9D8" class="texto">Usuário</font></td>
		          				</tr>
		          			</table>
								
								<div id='DivCodigoNome'>
								<table width="100%" border="0" cellpadding="0" bgcolor="#FFFFFF">
									<tr bgcolor="#F5F5FB" id="divUsuario1"> 
		            				<td class="texto" width='98px'>C&oacute;digo</font></td>
		            				<td width='208px'>
		              					<input name="tf_u_codigo" type="text" class="form2" value="<?php echo $tf_u_codigo ?>" size="30">
										</td>
		            				<td class="texto" width='110px'>Nome</font></td>
		            				<td>
		              					<input name="tf_u_nome" type="text" class="form2" value="<?php echo $tf_u_nome ?>" size="25" maxlength="100">
										</td>
									</tr>
		          					</table>
								</div>
								
								<table width="100%" border="0" cellpadding="0" bgcolor="#FFFFFF">
									<tr bgcolor="#F5F5FB"> 
		            				<td class="texto" width='98px'>Email</font></td>
		            				<td width='208px'>
		              					<input name="tf_u_email" type="text" class="form2" value="<?php echo $tf_u_email ?>" size="25" maxlength="100">
										</td>
		            				<td class="texto" id="divUsuario2" width='110px'>
											<div id='divCpf'>
											CPF
											</div>
										</td>
		            				<td id="divUsuario3">
											<div id='divCpfInput'>
		              					<input name="tf_u_cpf" type="text" class="form2" value="<?php echo $tf_u_cpf ?>" size="25" maxlength="14">
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
          			<tr bgcolor="#FFFFFF" id="divUsuario"> 
            			<td colspan="4" bgcolor="#ECE9D8" class="texto">
								
								<table width="100%" border="0" cellpadding="0" bgcolor="#FFFFFF">
		          				<tr bgcolor="#FFFFFF"> 
		            				<td colspan="4" bgcolor="#ECE9D8" class="texto">Integração</font></td>
		          				</tr>
		          			</table>
								
								<div id='DivCodigoNome'>
								<table width="100%" border="0" cellpadding="0" bgcolor="#FFFFFF">
									<tr bgcolor="#F5F5FB" id="divUsuario1"> 
		            				<td class="texto" width='98px'>Integração</font></td>
		            				<td width='208px'>
										<select name="tf_v_integracao" id="tf_v_integracao" class="form2">
											<option value=""<?php if($tf_v_integracao!="1" && $tf_v_integracao!="-1") echo " selected"?>>Todos os registros</option>
											<option value="1"<?php if($tf_v_integracao=="1") echo " selected"?>>Apenas de Integração</option>
											<option value="-1"<?php if($tf_v_integracao=="-1") echo " selected"?>>Apenas SEM Integração</option>
										</select>	
										</td>
		            				<td class="texto" width='110px'>&nbsp;</font></td>
		            				<td>&nbsp;</td>
									</tr>
		          					</table>
								</div>
							</td>
						</tr>

          			<tr bgcolor="#FFFFFF" id="divUsuario"> 
            			<td colspan="4" bgcolor="#ECE9D8" class="texto">
								
								<table width="100%" border="0" cellpadding="0" bgcolor="#FFFFFF">
		          				<tr bgcolor="#FFFFFF"> 
		            				<td colspan="4" bgcolor="#ECE9D8" class="texto">Drupal</font></td>
		          				</tr>
		          			</table>
								
								<div id='DivCodigoNome'>
								<table width="100%" border="0" cellpadding="0" bgcolor="#FFFFFF">
									<tr bgcolor="#F5F5FB" id="divUsuario1"> 
		            				<td class="texto" width='98px'>Drupal</font></td>
		            				<td width='208px'>
										<select name="tf_v_drupal" id="tf_v_drupal" class="form2">
											<option value=""<?php if($tf_v_drupal!="1" && $tf_v_drupal!="-1") echo " selected"?>>Todos os registros</option>
											<option value="1"<?php if($tf_v_drupal=="1") echo " selected"?>>Apenas pedidos registrados no site Drupal</option>
											<option value="-1"<?php if($tf_v_drupal=="-1") echo " selected"?>>Apenas pedidos registrados no site antigo</option>
										</select>	
										</td>
		            				<td class="texto" width='110px'>&nbsp;</font></td>
		            				<td>&nbsp;</td>
									</tr>
		          					</table>
								</div>
							</td>
						</tr>
					<tr bgcolor="#FFFFFF"> 
            			<td colspan="4" bgcolor="#ECE9D8" class="texto">Produto</font></td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td width="100" class="texto">Operadora</font></td>
            			<td>
							<select name="tf_opr_codigo" id="tf_opr_codigo" class="form2">
								<option value="" <?php if($tf_opr_codigo == "") echo "selected" ?>>Selecione</option>
								<?php 
									if($rs_operadoras) 
										while($rs_operadoras_row = pg_fetch_array($rs_operadoras))
										{
								?>
										<option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>"
										<?php 
											if ($tf_opr_codigo == $rs_operadoras_row['opr_codigo'] || $rs_operadoras_row['opr_codigo'] == $buscaOper) 
												echo " selected";
										?>><?php echo $rs_operadoras_row['opr_nome']; ?></option>
										<?php } ?>
							</select>
						</td>
            			<td colspan="2" class="texto"><input name='ex_tf_opr_codigo' type='checkbox' id='ex_tf_opr_codigo' value='1' <?php if(!empty($ex_tf_opr_codigo)) echo "checked";?>/> Excluir da Pesquisa a Operadora Selecionada</td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td class="texto">Produtos</font></td>
            			<td colspan="3" class="texto">
								<div id='mostraProdutos'>
								<?php 
                        if($rs_oprProdutos)
                           while($rs_oprProdutos_row = pg_fetch_array($rs_oprProdutos))
                           { 
                        ?>
										<input type="checkbox" id="tf_produto[]" name="tf_produto[]" value="<?php echo $rs_oprProdutos_row['ogp_nome']; ?>" 
										<?php
											if ($tf_produto && is_array($tf_produto))
												if (in_array($rs_oprProdutos_row['ogp_nome'], $tf_produto)) 
													echo " checked";
											else
												if ($rs_oprProdutos_row['ogp_nome'] == $tf_produto)
													echo " checked";
										?>><?php echo $rs_oprProdutos_row['ogp_nome']; ?>
                        <?php 
									} 
								?>
								</div>
							</td>
          			</tr>
                  <tr bgcolor="#F5F5FB"> 
            			<td class="texto">Valores</font></td>
            			<td colspan="3" class="texto">
								<div id='mostraValores'>
								<?php 
                                if($rs_oprPins)
                                    while($rs_oprPins_row = pg_fetch_array($rs_oprPins))
                                    { 
                            ?>
                                        <input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="<?php echo $rs_oprPins_row['pin_valor']; ?>" 
										<?php
											if ($tf_pins && is_array($tf_pins))
												if (in_array($rs_oprPins_row['pin_valor'], $tf_pins)) 
													echo " checked";
											else
												if ($rs_oprPins_row['pin_valor'] == $tf_pins)
													echo " checked";
										?>><?php echo $rs_oprPins_row['pin_valor'] . ",00"; ?>
                                    <?php } ?>
								</div>
							</td>
          			</tr>  
				</table>
                    <table class="table">
          			<tr bgcolor="#F5F5FB"> 
            			<td align="right">
                                    <input type="button" value="Buscar" class="btn btn-info btn-sm BtnSearch">
                                    <input type="hidden" name="BtnSearch" value="1">
                                </td>
          			</tr>
          			<?php if($msg != ""){?>
                    	<tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr>
					<?php }?>
				</table>
				</form>
                </td>
            </tr>
        </table>
        </div></div>
<?php 
        if($total_table > 0) { 
        /*
        * csv
        */
        require_once $raiz_do_projeto."class/util/CSV.class.php";

        $cabecalho = "Cód;Data Inclusão; Forma de pagamento;Dados de pagamento;Valor;Depósito/Boleto;Produtos;Qtde Total;Cód Usuário;Nome Usuário;Venda Completa;Conciliação;Produtos;";
        if ($tf_d_forma_pagto == 'E')
            $cabecalho .= "Canal Venda CASH;Comissão EPP";

        $espacamento = ";;;;;;;;;;;;";

       $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto."arquivos_gerados/csv/");
       $objCsv->setCabecalho();
       /*
        * CSV
       */

?>
                        <table class="table txt-preto fontsize-pp bg-branco">
                            <tr> 
                                <td colspan="20" class="texto"> 
                                    Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong> <span id="txt_totais"></span></font> 
                                </td>
                            </tr>
                            <?php $ordem = ($ordem == 1)?2:1; ?>
                            <tr  bgcolor="#ECE9D8"> 
                                <td align="left">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.</a><?php if($ncamp == 'vg_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="left">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data Inclusão</font></a><?php if($ncamp == 'vg_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="left">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_pagto_tipo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Forma de<br>Pagamento</font></a><?php if($ncamp == 'vg_pagto_tipo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="left">
                                    <strong><font class="texto">Dados de pagamento</font></strong>
                                </td>
                                <td align="left">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=valor&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Valor</font></a><?php if($ncamp == 'valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="left">
                                    <strong><font class="texto">Depósito/<br>Boleto</font></strong>
                                </td>
                                <td align="left">
                                    <strong><font class="texto">Produtos</font></strong>
                                </td>
                                <td align="left">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=qtde_itens&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Qtde<br>Total</font></a><?php if($ncamp == 'qtde_itens') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="left">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">C&oacute;d.<br>Usuário</font></a><?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="left">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_nome&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Nome Usuário</font></a><?php if($ncamp == 'ug_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="left">
                                    <strong><font class="texto">Venda&nbsp;completa</font></strong>
                                </td>
                                <td align="left">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_concilia&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Conciliação</font></a><?php if($ncamp == 'vg_concilia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="left">
                                    <strong><font class="texto">Produtos</font></strong>
                                </td>
                                <?php
                                if ($tf_d_forma_pagto == 'E') {
                                ?>
                                <td align="left">
                                    <strong><font class="texto"><nobr>Canal Venda CASH</nobr></font></strong>
                                </td>
                                <td align="left">
                                    <strong><font class="texto"><nobr>Comissão&nbsp;EPP</nobr></font></strong>
                                </td>
                                <?php
                                }
                                ?>

                            </tr>
<?php
                        $cor1 = $query_cor1;
                        $cor2 = $query_cor1;
                        $cor3 = $query_cor2;
                        //total
                        $total_valor = 0;
                        $total_qtde_itens = 0;

                        while($rs_venda_row = pg_fetch_array($rs_venda))
                        {
                            $lineCsv = array();
                            $cor1 = ($cor1 == $cor2)?$cor3:$cor2;
                            $status = $rs_venda_row['vg_ultimo_status'];
                            $pagto_tipo = $rs_venda_row['vg_pagto_tipo'];

                            if($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']) {
                                    $pagto_tipo_aux = "Transf, DOC, Dep";
                            } elseif($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) {
                                    $pagto_tipo_aux = "Boleto";
                            } else {
                                    $descricao = getDescricaoPagtoOnline($pagto_tipo);
                                    $pagto_tipo_aux = "$descricao ('$pagto_tipo')";
                            }

                            //total
                            $total_valor += $rs_venda_row['valor'];
                            $total_qtde_itens += $rs_venda_row['qtde_itens'];

                            $strId = $rs_venda_row['vg_id'];
                            if($rs_venda_row['vg_drupal_order_id']>0) 
                            {
                                $strId .= "<br><nobr>(dr_id: ".$rs_venda_row['vg_drupal_order_id'].")</nobr>";
                            }

                            $lineCsv[] = $strId;
                            $lineCsv[] = formata_data_ts($rs_venda_row['vg_data_inclusao'],0, true,true);
                            $lineCsv[] = utf8_decode(strip_tags(html_entity_decode($pagto_tipo_aux)));
?>
                            <tr bgcolor="<?php echo $cor1 ?>"  onmouseover="bgColor='#CFDAD7'" onmouseout="bgColor='<?php echo $cor1 ?>'" valign="top"> 
                                <td class="texto" align="left">
                                    <a style="text-decoration:none" href="com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['vg_id'] ?>&fila_ncamp=<?php echo $ncamp?>&fila_ordem=<?php echo ($ordem == 1?2:1) ?><?php echo $varsel?>">
<?php 
                                    echo $strId;
?>
                                    </a>
                                </td>
                                <td class="texto" align="center">
                                    <?php echo formata_data_ts($rs_venda_row['vg_data_inclusao'],0, true,true) ?>
                                </td>
                                <td class="texto">
                                    <?php echo $pagto_tipo_aux ?>
                                </td>
                                <td class="texto" align="left">
<?php
                                    $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
                                    $vg_pagto_local = $rs_venda_row['vg_pagto_local'];
                                    $pagto_num_docto = explode("|", $rs_venda_row['vg_pagto_num_docto']);
                                    $pagto_nome_docto_Ar = explode(";", $PAGTO_NOME_DOCTO[$vg_pagto_banco][$vg_pagto_local]);

                                    $dado = "Banco: ".$PAGTO_BANCOS[$vg_pagto_banco];
                                    $dado .= " Local ".$PAGTO_LOCAIS[$vg_pagto_banco][$vg_pagto_local];
?>
                                    <b>Banco:</b> <?php echo $PAGTO_BANCOS[$vg_pagto_banco] ?><br>
                                    <b>Local:</b> <?php echo $PAGTO_LOCAIS[$vg_pagto_banco][$vg_pagto_local] ?><br>
<?php 
                                    for($i=0; $i<count($pagto_nome_docto_Ar); $i++)
                                    {
                                        $dado .= (trim($pagto_nome_docto_Ar[$i])==""?" Nro Documento":$pagto_nome_docto_Ar[$i]);
                                        $dado .= ": ".$pagto_num_docto[$i];
?>
                                        <b><?php echo (trim($pagto_nome_docto_Ar[$i])==""?" Nro Documento":$pagto_nome_docto_Ar[$i]); ?></b>: <?php echo $pagto_num_docto[$i]?><br>
<?php
                                    } 

                                    $lineCsv[] = strip_tags(html_entity_decode($dado));
                                    $lineCsv[] = number_format($rs_venda_row['valor'], 2, ',','.');
?>
                                </td>
                                <td class="texto" align="right">
                                    <?php echo number_format($rs_venda_row['valor'], 2, ',','.'); ?>
                                </td>
<?php	
                            if($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF'])
                            {
                                $lineCsv[] = $rs_venda_row['vg_dep_codigo'];
?>
                                <td class="texto" align="center">
                                    <a style="text-decoration:none" target="_blank" href="/financeiro/pedidos/depositos/altera.php?DepCod=<?php echo $rs_venda_row['vg_dep_codigo'] ?>"><?php echo $rs_venda_row['vg_dep_codigo'] ?></a>
                                </td>
<?php	
                            } else if($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'])
                            {
                                $lineCsv[] = $rs_venda_row['bol_codigo'];
?>
                                <td class="texto" align="center">
                                    <a style="text-decoration:none" target="_blank" href="/financeiro/pedidos/boletos/altera.php?BolCod=<?php echo $rs_venda_row['bol_codigo'] ?>"><?php echo $rs_venda_row['bol_codigo'] ?></a>
                                </td>
<?php	
                            } else { 
                                $lineCsv[] = "";
?>
                                <td class="texto" align="center">&nbsp;</td>
<?php	
                            }

                            $venda_id = $rs_venda_row['vg_id'];
                            if($rs_venda_row['cesta']=="") 
                            {
                                //Recupera modelos
                                if($msg == "")
                                {
                                    $sql  = "select * from tb_venda_games vg "  . "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " . "where vg.vg_id = " . $venda_id;

                                    $rs_venda_modelos = SQLexecuteQuery($sql);
                                    if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) 
                                        $produtos = "Sem produto.\n";
                                    else 
                                    {
                                        $produtos = "";
                                        while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos))
                                        {
                                            $produtos .= "<b>" . $rs_venda_modelos_row['vgm_nome_produto'] . "</b>"; 
                                            if($rs_venda_modelos_row['vgm_nome_modelo']!="") 
                                                $produtos .= " - " . $rs_venda_modelos_row['vgm_nome_modelo'];
                                                $produtos .= "<br>"; 
                                        }
                                    }
                                }
                            } else 
                            {
                                $produtos = $rs_venda_row['cesta'];
                            }

                            $lineCsv[] = strip_tags(html_entity_decode(str_replace(array("\n", "\r")," ",$produtos)));
                            $lineCsv[] = $rs_venda_row['qtde_itens'];
                            $lineCsv[] = $rs_venda_row['ug_id'];
?>                        
                                <td class="texto" align="left"><?php echo $produtos ?></td>
                                <td class="texto" align="right"><?php echo $rs_venda_row['qtde_itens'] ?></td>
                                <td class="texto" align="center"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_venda_row['ug_id'] ?>"target="_blank" ><?php echo $rs_venda_row['ug_id'] ?></a></td>
                                <td class="texto">
<?php 
                            $nome = $rs_venda_row['ug_nome']; 
                            // Recupera email para Money Express
//												if($tf_v_origem == "exmo")
                            if($rs_venda_row['ug_id'] == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']) {	
                                $nome.= "<br>(".strtoupper($rs_venda_row['vg_ex_email']).")";
                            } else {
                                $nome .= "<br>(".strtoupper($rs_venda_row['ug_email']).")";
                            }

                            echo $nome;
                            $lineCsv[] = strip_tags($nome);
?>
                                </td>
<?php 
                            if(	$status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] ||
                                    $status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] ||
                                    $status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] ||
                                    $status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'])
                            {
?>
                                <td class="texto" align="center" title="<?php  echo "status: '$status'" ?>">
<?php 
                                if($rs_venda_row['vg_ultimo_status'] != 5)
                                {
                                    $lineCsv[] = "Venda incompleta";
?>
                                    <nobr><font color="#ff0000">Venda incompleta</font></nobr>
<?php 
                                } else 
                                { 
                                    $lineCsv[] = "Venda realizada";
?>
                                    <nobr><font color="#009933">Venda realizada</font></nobr>
<?php 
                                } 
?>
                                </td>
                                <td class="texto" align="center" <?php echo ($rs_venda_row['vg_concilia']==1)?" title='data_concilia: ".$rs_venda_row['vg_data_concilia']."'":"" ?>>
                                    <a style="text-decoration:none" href="com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['vg_id'] ?>&fila_ncamp=<?php echo $ncamp?>&fila_ordem=<?php echo ($ordem == 1?2:1) ?><?php echo $varsel?>">
<?php 

									$vg_usuario_obs = $rs_venda_row['vg_usuario_obs'];
									$verifica_conciliacao_manual = stripos($vg_usuario_obs, "conciliado manualmente");
									
									if($rs_venda_row['vg_concilia'] == 0)
                                    {
                                        $lineCsv[] = "Conciliar";

                                        echo "<font color='#ff0000'>Conciliar</font>";
 
                                    } else if ($rs_venda_row['vg_concilia'] == 1 && $verifica_conciliacao_manual !== false) {
										
										$lineCsv[] = "Conciliado Manualmente";

                                        echo "<font color='#009933'>Conciliado Manualmente</font>";
										
									} else if ($rs_venda_row['vg_concilia'] == 1 && $verifica_conciliacao_manual == false) {
										
                                        $lineCsv[] = "Conciliado";

                                        echo "<font color='#009933'>Conciliado</font>";
										
                                    } else {
										echo "<font color='#ff0000'>Não conciliado</font>";
									}
?>
                                    </a>
                                </td>
<?php
                            } else { 
                                $lineCsv[] = "Venda incompleta";
                                $lineCsv[] = "-";
?>
                                <td class="texto" align="center" title="<?php  echo "status: '$status'" ?>">
                                    <nobr><font color="#ff0000">Venda incompleta</font></nobr>
                                </td>
                                <td>&nbsp;-&nbsp;</td>
<?php 
                            } 
?>
                                <td valign="top" align="left">
<?php
                                $sql = "select opr_codigo, opr_nome, vgm.vgm_nome_produto, vgm.vgm_nome_modelo, vgm_pin_valor, vgm_qtde, vgm_valor 
                                        from tb_venda_games vg 
                                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                        inner join operadoras opr on opr.opr_codigo = vgm.vgm_opr_codigo
                                        where vgm_vg_id = " . $rs_venda_row['vg_id'];

                                $rs_produtos = SQLexecuteQuery($sql);

                                if($rs_produtos && pg_num_rows($rs_produtos) > 0)
                                {
                                    $lineCsv[] = "Operadora";
                                    $lineCsv[] = "Produto";
                                    $lineCsv[] = "Valor de face";
                                    $lineCsv[] = "Qtde";

                                    $objCsv->setLine(implode(";",$lineCsv));
?>
                                    <table class="texto" border="0" width="400" cellpadding="0" cellspacing="0">
                                        <tr bgcolor="#ECE9D8">
                                            <td nowrap width="30%">Operadora</td>
                                            <td nowrap width="50%">Produto</td>
                                            <td nowrap width="20%">Valor de face</td>
                                            <td nowrap width="50%">Qtde</td>
                                        </tr>
<?php 
                                    while($rs_produtos_row = pg_fetch_array($rs_produtos))
                                    {
                                        $lineCsv = array();
                                        $lineCsv[] = $rs_produtos_row['opr_nome'];
                                        $lineCsv[] = $rs_produtos_row['vgm_nome_produto'].($rs_produtos_row['vgm_nome_modelo']!="") ? $rs_produtos_row['vgm_nome_modelo'] : "";
                                        $lineCsv[] = number_format($rs_produtos_row['vgm_valor'], 2, ',','.');
                                        $lineCsv[] = $rs_produtos_row['vgm_qtde'];
?>
                                        <tr>
                                            <td nowrap><?php echo $rs_produtos_row['opr_nome']?></td>
                                            <td nowrap>
                                                <?php echo $rs_produtos_row['vgm_nome_produto']?> 
                                                <?php if($rs_produtos_row['vgm_nome_modelo']!=""){?> - <?php echo $rs_produtos_row['vgm_nome_modelo']?><?php }?>
                                            </td>
                                            <td nowrap align="right" title="<?php echo "PIN valor: ".number_format($rs_produtos_row['vgm_pin_valor'], 2, ',','.')?>"><?php echo number_format($rs_produtos_row['vgm_valor'], 2, ',','.')?></td>
                                            <td nowrap align="right"><?php echo $rs_produtos_row['vgm_qtde']?></td>
                                            </tr>
<?php	
                                        $objCsv->setLine($espacamento.implode(";",$lineCsv));
                                    } 
?>
                                    </table>
<?php			
                                }else{
                                    $objCsv->setLine(implode(";",$lineCsv));
                                }
?>
                                </td>
<?php
                                if ($tf_d_forma_pagto == 'E') 
                                {
                                    // Composição do pagamento com Saldo
                                    if(b_IsUsuarioReinaldo()||b_IsUsuarioWagner()) 
                                    {
                                        $s_composicao = "";
                                        $s_canal_gocash = "";

                                        //if($somente_gocash) 
                                        {
                                            // valorpagtopin, valorpagtosaldo, valorpagtogocash
                                            $b_has_gocash = get_pagto_epp_part_gocash($rs_venda_row['vg_id'], $apagtos);
                                            $b_has_eppcash	= ($apagtos['valorpagtopin']>0);
                                            $b_has_saldo	= ($apagtos['valorpagtosaldo']>0);
                                            $s_canal_gocash .= "[".
                                                    (($b_has_eppcash)?"P":"-").
                                                    (($b_has_saldo)?"S":"-").
                                                    (($b_has_gocash)?"G":"-")."] "
                                                    ;
                                            if($b_has_eppcash)	{ $s_canal_gocash .= "PIN_EPP"; }
                                            if($b_has_saldo)	{ $s_canal_gocash .= "Saldo"; }
                                            if($b_has_gocash)	{ $s_canal_gocash .= "GoCash"; }

                                            // Abre tabela
                                            if($rs_venda_row['scfu_valor'] || $b_has_gocash) {

                                                    $lineCsv = array();
                                                    $lineCsv[] = "valor";
                                                    $lineCsv[] = "qtde";
                                                    $lineCsv[] = "canal";
                                                    $objCsv->setLine(implode(";",$lineCsv));

                                                    $s_composicao  = "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
                                                    $s_composicao .=    "<tr class='texto' align='center'>
                                                                            <td><b>valor</b></td>
                                                                            <td><b>qtde</b></td>
                                                                            <td><b>canal</b></td>
                                                                        </tr>\n";
                                            }
                                            // Valor utilizado de Saldo depositado por GoCash
                                            if($rs_venda_row['scfu_valor']) {

                                                $lineCsv = array();
                                                $lineCsv[] = $rs_venda_row['scfu_valor'];
                                                $lineCsv[] = $rs_venda_row['scfu_qtde'];
                                                $lineCsv[] = $rs_venda_row['scf_canal'];

                                                $objCsv->setLine($espacamento.implode(";",$lineCsv));

                                                    $s_composicao .=    "<tr class='texto' align='center'>
                                                                            <td>".$rs_venda_row['scfu_valor']." </td> 
                                                                            <td>".$rs_venda_row['scfu_qtde']." </td> 
                                                                            <td>".$rs_venda_row['scf_canal']." </td>
                                                                        </tr>\n";
                                            }
                                            // Valor de PINs GoCash utilizados diretamente
                                            if($b_has_gocash) {
                                                $lineCsv = array();
                                                $lineCsv[] = $apagtos['valorpagtogocash'];

                                                $objCsv->setLine($espacamento.implode(";",$lineCsv));

                                                    $s_composicao .=    "<tr class='texto' align='center'>
                                                                            <td>".$apagtos['valorpagtogocash']." </td> 
                                                                            <td>-</td> 
                                                                            <td>C</td>
                                                                        </tr>\n";
                                            }
                                            // Fecha tabela
                                            if($rs_venda_row['scfu_valor'] || $b_has_gocash) {
                                                $s_composicao .= "</table>\n";
                                            }

                                            $venda_id = $rs_venda_row['vg_id'];

                                            if($b_has_saldo) 
                                            {
                                                //if(b_IsUsuarioReinaldo()||b_IsUsuarioWagner())
                                                { 
                                                        $s_composicao = get_venda_pagto_com_saldo_composicao($venda_id);
                                                }
                                            }

                                            if($b_has_eppcash) 
                                            {
                                                //if(b_IsUsuarioReinaldo()||b_IsUsuarioWagner())
                                                { 
                                                        $s_composicao = get_venda_pagto_com_pinsepp_composicao($venda_id);
                                                }
                                            }

                                        }
                                    }
?>
                                    <td align="center" valign="top">
                                        <strong><font class="texto">$$$$$<?php echo $s_canal_gocash;		//$DISTRIBUIDORAS_CANAIS[$rs_venda_row['tvgpo_canal']]." <br>(".$rs_venda_row['comissao_canal'].")";?></font></strong><br>
<?php
                                        echo $s_composicao;
?>
                                    </td>
                                    <td align="center" title="<?php if(b_IsUsuarioReinaldo()||b_IsUsuarioWagner()) {echo "Publisher: ".$rs_venda_row['opr_nome']." (ID: ".$rs_venda_row['vgm_opr_codigo'].")\nComissão Publisher: ".$rs_venda_row['comissao_publisher']."%\nCanal venda PIN EPP Cash: ".$rs_venda_row['tvgpo_canal']."\nDesconto canal: ".number_format($rs_venda_row['tvgpo_perc_desconto'], 0, '.', '.')."%\n"; }?>">
                                            @@@@@<strong><font class="texto"><?php if(b_IsUsuarioReinaldo()||b_IsUsuarioWagner()) {echo number_format((100*$rs_venda_row['comissao_epp']), 0, '.', '.');} ?>%</font></strong>
                                    </td>
<?php
                                }
?>
                                    </tr>
<?php 
                        }

                        if($reg_ate >= $total_table && !isset($_REQUEST["inicial"]))
                            $csv = $objCsv->export();

                        if(isset($csv))
                        {
                            $csv = "https://".$_SERVER['SERVER_NAME']."/includes/downloadCsv.php?csv=$csv&dir=bkov";
                        }elseif(isset($_GET["downloadCsv"]))
                        {
                            require_once $raiz_do_projeto."public_html/includes/downloadCsv.php";
                        }elseif($total_table > 0)
                        {
                            $csv = "/gamer/vendas/com_pesquisa_vendas.php?downloadCsv=1".$varsel;
                        }
						
?>
                        <tr bgcolor="E5E5EB"> 
                            <td class="texto" align="right" colspan="4"><b>Total:</b></td>
                            <td class="texto" align="right"><?php echo number_format($total_valor, 2, ',','.') ?></td>
                            <td class="texto" align="right" colspan="2">&nbsp;</td>
                            <td class="texto" align="right"><?php echo number_format($total_qtde_itens, 0, '','.') ?></td>
                            <td class="texto" align="right" colspan="7"></td>
                        </tr>
                        <tr bgcolor="D5D5DB"> 
                            <td class="texto" align="right" colspan="4"><b>Total Geral:</b></td>
                            <td class="texto" align="right"><?php echo number_format($totalGeral_valor, 2, ',','.') ?></td>
                            <td class="texto" align="right" colspan="2">&nbsp;</td>
                            <td class="texto" align="right"><?php echo number_format($totalGeral_qtde_itens, 0, '','.') ?></td>
                            <td class="texto" align="right" colspan="7"></td>
                        </tr>
<script language="JavaScript">
document.getElementById('txt_totais').innerHTML = '( <?php echo number_format($total_valor, 2, ',', '.') ?> / <?php echo number_format($totalGeral_valor, 2, ',', '.') ?>)';
</script>
                        <tr bgcolor="D5D5DB"> 
                            <td class="texto" align="right" colspan="4"><b>Lista de usuários que realizaram as compras:</b></td>
                            <td class="texto" align="center" colspan="4">
                                <input type="button" id="but_ids_show" class="btn btn-sm btn-info" value="Mostra Lista de IDs" onclick="$('#div_ids').show();$('#but_ids_show').hide();">
                            </td>
                            <td class="texto" align="center" colspan="3">
                                <input type="button" id="but_emails_show" class="btn btn-sm btn-info" value="Mostra Lista de Emails" onclick="$('#div_emails').show();$('#but_emails_show').hide();">
                            </td>
                            <td class="texto" align="right" colspan="2"></td>
                        </tr>
                        <tr bgcolor="D5D5DB"> 
                            <td class="texto" align="right" colspan="4"><b>Lista de Códs. de pedidos:</b></td>
                            <td class="texto" align="center" colspan="4">
                                                            <input type="button" class="btn btn-sm btn-info" id="but_ids_ped_show" value="Mostra Lista de Códs de Pedidos" onclick="$('#div_ids_ped').show();$('#but_ids_ped_show').hide();">
                                                    </td>
                            <td class="texto" align="center" colspan="3">&nbsp;
                                                    </td>
                            <td class="texto" align="right" colspan="2"></td>
                        </tr>
                        <tr bgcolor="D5D5DB"> 
                            <td class="texto" align="left" colspan="13">
                                <div id="div_ids" style="display:none;">
                                        Encontrados <?php echo $n_lista_ug_id ?> usuários. - <input type="button" class="btn btn-sm btn-info" id="but_ids_hide" value="Oculta Lista de IDs" onclick="$('#div_ids').hide(); $('#but_ids_show').show();"><br>
                                        <?php echo $lista_ug_id ?>
                                </div>
                                <div id="div_emails" style="display:none;">
                                        Encontrados <?php echo $n_lista_ug_email ?> usuários. - <input type="button" class="btn btn-sm btn-info" id="but_emails_hide" value="Oculta Lista de Emails" onclick="$('#div_emails').hide(); $('#but_emails_show').show();"><br>
                                        <?php echo $lista_ug_email ?>
                                </div>
                            </td>
                            </tr>
                            <tr bgcolor="D5D5DB"> 
                                <td class="texto" align="left" colspan="13">
                                    <div id="div_ids_ped" style="display:none;">
                                            Encontrados <?php echo $n_lista_id_ped ?> pedidos. - <input type="button" class="btn btn-sm btn-info" id="but_ids_ped_hide" value="Oculta Lista de Códs de Pedidos" onclick="$('#div_ids_ped').hide(); $('#but_ids_ped_show').show();"><br>
                                            <?php echo $lista_id_ped ?>
                                    </div>
                                    <div id="div_emails" style="display:none;">&nbsp;</div>
                                </td>
                            </tr>
                            <tr> 
                                <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
                            </tr>
                                <?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
                    </table>
<?php  
        }  

        if(isset($csv))
        { 
?>
            <a style="float:right; text-decoration: none;" target="_blank" href="<?php print $csv;?>"><span style="padding:5px;" class="btn botao_search downloadCsv">Download CSV</span></a>
<?php 
        } 
?>
            <div><div>
<script>
$(function(e){
    
    $("#form1").keyup(function(e){
        var x = e.which || e.keyCode
        if(x == 13){
            $(".BtnSearch").trigger("click");
        }
    });
    
    
   $(".BtnSearch").click(function(){
        var erro = true;
        $(".datePopUpCalendar").each(function(){
           var ipt = $(this).attr("id").replace("_ini","");
           var ini = $("#"+ipt+"_ini");
           var fim = $("#"+ipt+"_fim");
           
            if(ini.val() != "" && fim.val() != "")
               erro = false;
        });
       
        if(erro == true)
        {
            alert("Por favor preencha pelo menos um dos períodos de data.");
        }else{
            $("#form1").submit();
        }
   });
   
});
</script>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</html>
<?php 

//echo "<hr>".$lista_vg_id."<hr>";

function is_csv_numeric($list) {
	$bDebug = false;
//if(b_IsUsuarioReinaldo()) { 
//	$bDebug = true;
//}
if($bDebug) echo "list: '$list'<br>";
	$list1 = str_replace(" ", "", $list);
if($bDebug) echo "list1: '$list1'<br>";
	$alist = explode(",", $list1);
if($bDebug) echo "alist: <pre>".print_r($alist, true)."</pre><br>";
	$bret = true;
	foreach($alist as $key => $val) {
		$val1 = str_replace(" ", "", $val);
if($bDebug) echo "'".$val."' - ".((is_numeric($val1))?"NUMERIC":"ALPHA")."<br>\n";
		$bret = is_numeric($val1);
		if(!$bret) {
			break;
		}
	}
	return $bret;
}
													
?>
