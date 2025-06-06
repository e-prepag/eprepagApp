<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1);
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/functions_pagto_reduced.php"; 
	set_time_limit ( 6000 ) ;
//echo "<pre>".print_r($SIGLA_REGIOES,true)."</pre>";
	$time_start = getmicrotime();
	
	if(!$ncamp)    $ncamp       = 'vg_data_inclusao';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 1;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$tf_u_cidade = str_replace("_", " ", $tf_u_cidade);
	$tf_u_cidade_mod = str_replace(" ", "_", $tf_u_cidade);

	if( ! (($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) || ($tf_v_data_concilia_ini && $tf_v_data_concilia_fim) || ($tf_v_data_cancelamento_ini && $tf_v_data_cancelamento_fim) ) ) {
		$tf_v_data_inclusao_ini = date("d/m/Y");
		$tf_v_data_inclusao_fim = date("d/m/Y");
	}
	$varsel = "&BtnSearch=1&&tf_v_codigo=".str_replace(" ", "", $tf_v_codigo)."&tf_v_status=$tf_v_status";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	$varsel .= "&tf_v_data_concilia_ini=$tf_v_data_concilia_ini&tf_v_data_concilia_fim=$tf_v_data_concilia_fim";
	$varsel .= "&tf_v_data_cancelamento_ini=$tf_v_data_cancelamento_ini&tf_v_data_cancelamento_fim=$tf_v_data_cancelamento_fim";
	$varsel .= "&tf_v_concilia=$tf_v_concilia&tf_d_forma_pagto=$tf_d_forma_pagto&tf_d_banco=$tf_d_banco&tf_d_local=$tf_d_local";
	$varsel .= "&tf_d_data_ini=$tf_d_data_ini&tf_d_data_fim=$tf_d_data_fim";
	$varsel .= "&tf_d_data_inclusao_ini=$tf_d_data_inclusao_ini&tf_d_data_inclusao_fim=$tf_d_data_inclusao_fim";
	$varsel .= "&tf_d_valor_pago=$tf_d_valor_pago&tf_d_num_docto=$tf_d_num_docto";
	$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_nome_fantasia=$tf_u_nome_fantasia&tf_u_email=$tf_u_email&tf_u_responsavel=$tf_u_responsavel";
	$varsel .= "&tf_u_cnpj=$tf_u_cnpj&tf_v_repasse=$tf_v_repasse";
	$varsel .= "&tf_u_nome=$tf_u_nome&tf_u_rg=$tf_u_rg&tf_u_cpf=$tf_u_cpf";
	$varsel .= "&tf_v_valor=$tf_v_valor&tf_v_qtde_produtos=$tf_v_qtde_produtos&tf_v_qtde_itens=$tf_v_qtde_itens";
	$varsel .= "&tf_vgm_nome_produto=$tf_vgm_nome_produto&tf_vgm_nome_modelo=$tf_vgm_nome_modelo";
	$varsel .= "&tf_o_valor_face=$tf_o_valor_face";
	$varsel .= "&tf_u_risco_classif=$tf_u_risco_classif";
	$varsel .= "&tf_v_codigo_include=$tf_v_codigo_include";
//	$varsel .= "&tf_opr_codigo=$tf_opr_codigo&tf_o_valor_face=$tf_o_valor_face&tf_v_origem=$tf_v_origem";
	$varsel .= "&tf_u_so_depositos=$tf_u_so_depositos&tf_u_cidade=$tf_u_cidade_mod&tf_u_estado=$tf_u_estado&tf_regiao=$tf_regiao&tf_v_drupal=$tf_v_drupal";

	//Operadoras  
	 $varsel .= "&tf_opr_codigo=$tf_opr_codigo&tf_v_origem=$tf_v_origem&ex_tf_opr_codigo=$ex_tf_opr_codigo";

	 //Produtos
	 if ($tf_produto && is_array($tf_produto)) {
		 if (count($tf_produto) == 1) {
			 $tf_produto = $tf_produto[0];
		 } else {
			 $tf_produto = implode("|",$tf_produto);
		 }
	 }
	 $varsel .= "&tf_produto=$tf_produto";
	 if ($tf_produto && $tf_produto != "") {
		$tf_produto = explode("|",$tf_produto);
	 }
	 
	 //Valores
	 if ($tf_pins && is_array($tf_pins)){
		 if (count($tf_pins) == 1) {
			 $tf_pins = $tf_pins[0];
		 } else {
			 $tf_pins = implode("|",$tf_pins);
		 }
	 }
	 $varsel .= "&tf_pins=$tf_pins";
	 if ($tf_pins && $tf_pins != "") {
		$tf_pins = explode("|",$tf_pins);
	 }

	if(!isset($tf_v_codigo_include)) $tf_v_codigo_include = "1";

	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//Venda
		//------------------------------------------------------------------
		//codigo
		if($msg == "")
			if($tf_v_codigo){
				if(!is_csv_numeric($tf_v_codigo)) {
					$msg = "Código da venda deve ser numérico ou lista de números separada por vírgulas.\n";
				}
			}

		//Data
		if($msg == "")
			if($tf_v_data_inclusao_ini || $tf_v_data_inclusao_fim){
				if(verifica_data($tf_v_data_inclusao_ini) == 0)	$msg = "A data inicial da venda é inválida.\n";
				if(verifica_data($tf_v_data_inclusao_fim) == 0)	$msg = "A data final da venda é inválida.\n";
			}
		//Data Conciliacao
		if($msg == "")
			if($tf_v_data_concilia_ini || $tf_v_data_concilia_fim){
				if(!is_DateTime($tf_v_data_concilia_ini))	$msg = "A data inicial da conciliação da venda é inválida.\n";
				if(!is_DateTime($tf_v_data_concilia_fim))	$msg = "A data final da conciliação da venda é inválida.\n";
			}
		//Data Cancelamento
		if($msg == "")
			if($tf_v_data_cancelamento_ini || $tf_v_data_cancelamento_fim){
				if(verifica_data($tf_v_data_cancelamento_ini) == 0)	$msg = "A data inicial do cancelamento é inválida.\n";
				if(verifica_data($tf_v_data_cancelamento_fim) == 0)	$msg = "A data final do cancelamento é inválida.\n";
			}
		//valor
		if($msg == "")
			if($tf_v_valor){

				if(!is_moeda($tf_v_valor))
					$msg = "Valor da venda é inválido.\n";
			}
		//repasse
		if($msg == "")
			if($tf_v_repasse){

				if(!is_moeda($tf_v_repasse))
					$msg = "Valor do repasse da venda é inválido.\n";
			}
		//qtde produtos
		if($msg == "")
			if($tf_v_qtde_produtos){
			
				if(!is_numeric($tf_v_qtde_produtos))
					$msg = "Qtde Produtos da venda deve ser numérico.\n";
			}
		//qtde itens
		if($msg == "")
			if($tf_v_qtde_itens){
			
				if(!is_numeric($tf_v_qtde_itens))
					$msg = "Qtde Itens da venda deve ser numérico.\n";
			}


		//Dados do Pagamento
		//------------------------------------------------------------------
		//Data
		if($msg == "")
			if($tf_d_data_ini || $tf_d_data_fim){
				if(verifica_data($tf_d_data_ini) == 0)	$msg = "A data inicial dos dados do pagamento é inválida.\n";
				if(verifica_data($tf_d_data_fim) == 0)	$msg = "A data final dos dados do pagamento é inválida.\n";
			}
		//Data inclusao
		if($msg == "")
			if($tf_d_data_inclusao_ini || $tf_d_data_inclusao_fim){
				if(verifica_data($tf_d_data_inclusao_ini) == 0)	$msg = "A data de inclusão inicial dos dados do pagamento é inválida.\n";
				if(verifica_data($tf_d_data_inclusao_fim) == 0)	$msg = "A data de inclusão final dos dados do pagamento é inválida.\n";
			}
		//valor pago
		if($msg == "")
			if($tf_d_valor_pago){

				if(!is_moeda($tf_d_valor_pago))
					$msg = "Valor Pago dos dados do pagamento é inválido.\n";
			}

                //Busca vendas
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){
			$sql  = "select ug.ug_id, ug.ug_email, ug.ug_responsavel, ug.ug_nome_fantasia, ug.ug_nome, ug.ug_vip, ug.ug_tipo_cadastro, ug.ug_risco_classif, 
							vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia, vg.vg_usuario_obs, vg_pagto_num_docto, vg_pagto_banco, vg_pagto_local, ";

			if($tf_u_so_depositos=="1") {
				$sql .= "		case when (substr(vg_pagto_num_docto, 1, 1)='4') then bol_valor else pag_valor end as valor, 1 as qtde_itens, count(*) as qtde_produtos, 
								0 as repasse, bol_tipo,  ";

			} else {
				$sql .= "		sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, 
								sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse, vgm.vgm_cpf, ";
			}

			$sql  .= "	vg_drupal_order_id, \n";
			$sql .= "		ug_google_maps_status, ug_coord_lat, ug_coord_lng
					 from tb_dist_venda_games vg \n";

			if($tf_u_so_depositos=="1") {
				if($tf_v_status) {
					$tf_v_status_pag = "";
					if($tf_v_status=="5") {
						$tf_v_status_pag = "3";
					} else {
//						$tf_v_status_pag = "1";
					}
				}

				$sql .= "inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id 
							left outer join ( 
									select idvenda as pag_vg_id, (total/100-taxas) as pag_valor, iforma as pag_tipo from tb_pag_compras pg 
									where 1=1 ";
				if($tf_v_status_pag) {
					$sql .= " and pg.status=$tf_v_status_pag ";
				}
				$sql .= "and (pg.tipo_cliente = 'LR') 
									) pag on pag.pag_vg_id = vg.vg_id

								left outer join ( 
									select bol_venda_games_id as bol_vg_id, bol_valor as bol_valor, 'B' || substr(bol_documento, 1, 1) as bol_tipo from boletos_pendentes bol 
									where bol_aprovado = 1 and (substr(bol_documento, 1, 1)='4') 	
									) bol on bol.bol_vg_id = vg.vg_id 	
						";
							// --and pg.datacompra between '2011-11-24 00:00:00' and '2011-11-24 23:59:59' 
							// --and bol_importacao between '2011-10-24 00:00:00' and '2011-11-24 23:59:59' 
			} else {
				$sql .= "inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
						 inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id \n";
			}
 			$sql .= "where 1=1 \n";

//			if($tf_v_codigo) 			$sql .= " and vg.vg_id in (".$tf_v_codigo.") \n";
			if($tf_v_codigo && ($tf_v_codigo_include=="1" || $tf_v_codigo_include=="-1")) {
				if($tf_v_codigo_include=="1") {
					if($tf_v_codigo) 			$sql .= " and vg.vg_id in (".$tf_v_codigo.") \n";
				} elseif($tf_v_codigo_include=="-1") {
					if($tf_v_codigo) 			$sql .= " and vg.vg_id not in (".$tf_v_codigo.") \n";
				}
			}
			if($tf_v_status) 			$sql .= " and vg.vg_ultimo_status = '".$tf_v_status."' \n";
			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) $sql .= " and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' \n";
			if($tf_v_data_concilia_ini && $tf_v_data_concilia_fim) $sql .= " and vg.vg_data_concilia between '".formata_data_ts($tf_v_data_concilia_ini, 2, true, false)."' and '".formata_data_ts($tf_v_data_concilia_fim, 2, true, false)."' \n";
			if($tf_v_data_cancelamento_ini && $tf_v_data_cancelamento_fim){
				$sql .= " and vg.vg_id in (select vgh_vg_id from tb_dist_venda_games_historico where vgh_status = " . $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'] . " and vgh_data_inclusao between '".formata_data($tf_v_data_cancelamento_ini,1)." 00:00:00' and '".formata_data($tf_v_data_cancelamento_fim,1)." 23:59:59') \n";
			} 
			if(!is_null($tf_v_concilia) && $tf_v_concilia != "")$sql .= " and vg.vg_concilia = '".$tf_v_concilia."' \n";
			if($conciliado_manualmente == "1") {
				$sql .= " and vg_usuario_obs like '%manualmente%' \n";
			}
			if($tf_d_forma_pagto) 		$sql .= " and vg.vg_pagto_tipo = ".getCodigoNumericoParaPagto($tf_d_forma_pagto)." \n";
			if($tf_d_banco) 			$sql .= " and vg.vg_pagto_banco = '".$tf_d_banco."' \n";
			if($tf_d_local) 			$sql .= " and vg.vg_pagto_local = '".$tf_d_local."' \n";
			if($tf_d_data_ini && $tf_d_data_fim) $sql .= " and vg.vg_pagto_data between '".formata_data($tf_d_data_ini,1)." 00:00:00' and '".formata_data($tf_d_data_fim,1)." 23:59:59' \n";
			if($tf_d_data_inclusao_ini && $tf_d_data_inclusao_fim) $sql .= " and vg.vg_pagto_data_inclusao between '".formata_data($tf_d_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_d_data_inclusao_fim,1)." 23:59:59' \n";
			if($tf_d_valor_pago) 		$sql .= " and vg.vg_pagto_valor_pago = ".moeda2numeric($tf_d_valor_pago)." \n";
			if($tf_d_num_docto) 		$sql .= " and upper(vg.vg_pagto_num_docto) like '%". strtoupper($tf_d_num_docto)."%' \n";
			if($tf_u_codigo) 			$sql .= " and ug.ug_id IN (".$tf_u_codigo.") \n";
			if($tf_u_nome_fantasia) 	$sql .= " and upper(ug.ug_nome_fantasia) like '%".strtoupper($tf_u_nome_fantasia)."%' \n";
			if($tf_u_email) 			$sql .= " and upper(ug.ug_email) like '%".strtoupper($tf_u_email)."%' \n";
			if($tf_u_cnpj) 				$sql .= " and ug.ug_cnpj like '%".$tf_u_cnpj."%' \n";
			if($tf_u_vip != null) 				$sql .= " and ug.ug_vip in(".$tf_u_vip.") \n";
			if($tf_u_nome) 				$sql .= " and upper(ug.ug_nome) like '%" . strtoupper($tf_u_nome) . "%' \n";
			if($tf_u_cpf) 				$sql .= " and ug.ug_cpf like '%" . $tf_u_cpf . "%' \n";
			if($tf_u_rg) 				$sql .= " and ug.ug_rg like '%" . $tf_u_rg . "%' \n";
			if($tf_u_risco_classif) 	$sql .= " and ug.ug_risco_classif =" . $RISCO_CLASSIFICACAO[$tf_u_risco_classif] . " \n";
			if($tf_u_cidade) 			$sql .= " and upper(ug.ug_cidade) like '%".strtoupper($tf_u_cidade_mod)."%' \n";
			if($tf_u_estado) 			$sql .= " and ug.ug_estado = '".strtoupper($tf_u_estado)."' \n";
			if($tf_regiao) 				$sql .= " and ug.ug_estado IN ('".implode("','", $SIGLA_REGIOES[$tf_regiao])."') \n";
			if($tf_v_drupal) {	
				if($tf_v_drupal==1) {	
					$sql .= " and (vg_drupal = 1) ";
				} elseif($tf_v_drupal==-1) {	
					$sql .= " and (vg_drupal = 0) ";
				}
			}
			
			if($tf_u_so_depositos=="1") {
				// Do nothing
			} else {

//				if($tf_vgm_nome_produto) 	$sql .= " and upper(vgm.vgm_nome_produto) like '%".strtoupper($tf_vgm_nome_produto)."%' \n";
//				if($tf_vgm_nome_modelo) 	$sql .= " and upper(vgm.vgm_nome_modelo) like '%".strtoupper($tf_vgm_nome_modelo)."%' \n";
//				if($tf_o_valor_face) 		$sql .= " and vgm.vgm_pin_valor = ".moeda2numeric($tf_o_valor_face)." \n";
//				if($tf_opr_codigo) 			$sql .= " and vgm.vgm_opr_codigo = ".$tf_opr_codigo." \n";

				//Produtos
				if ($tf_produto && is_array($tf_produto)) {
					if (count($tf_produto) == 1) {
					  $sql .= " and upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto[0])) . "%' "; 
					} else {
						$sql .= " and (";
						foreach($tf_produto as $tf_produto_id => $tf_produto_row) {
							if ($tf_produto_id == count($tf_produto) - 1) {
								$sql .= "vgm.vgm_nome_produto like '%" . str_replace("'", "''",$tf_produto_row) . "%')";
							} else {
								$sql .= "vgm.vgm_nome_produto like '%" . str_replace("'", "''",$tf_produto_row) . "%' or ";
							}
						}
					}
				}
				
				//Valores
				if ($tf_pins && is_array($tf_pins)) {
					if (count($tf_pins) == 1) {
						$sql .= " and vgm.vgm_valor = " . moeda2numeric($tf_pins[0]) . " "; 
					} else {
						$sql .= " and (";
						foreach($tf_pins as $tf_pins_id => $tf_pins_row) {
							if ($tf_pins_id == count($tf_pins) - 1) {
								$sql .= "vgm.vgm_valor = " . moeda2numeric($tf_pins_row) . ")";
							} else {
								$sql .= "vgm.vgm_valor = " . moeda2numeric($tf_pins_row) . " or ";
							}
						}
					}
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

			}
			if($tf_u_so_depositos=="1") {
//				$sql .= "	and vg_ultimo_status = 5 \n";
				$sql .= "	and ( (not (bol_tipo is null)) or (not (pag_tipo is null)  ) ) \n";
			} 

			$sql .= " group by ug.ug_id, ug.ug_email, ug.ug_responsavel, ug.ug_nome_fantasia, ug.ug_nome, ug.ug_tipo_cadastro, ug.ug_risco_classif, 
        				vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia,vg.vg_usuario_obs, vg_pagto_num_docto, vg_pagto_banco, 
						vg_pagto_local, ug_google_maps_status, ug_coord_lat, ug_coord_lng, vg_drupal_order_id, ug.ug_vip ";
			if($tf_u_so_depositos=="1") {
				$sql .= ", pag_valor, bol_tipo, bol_valor ";
			} 
                        else {
                            $sql .= ", vgm.vgm_cpf ";
                        }
			$sql .= "\n";

			$sql .= " having 1=1 \n";
			if($tf_u_so_depositos=="1") {
				// Do nothing
			} else {

				if($tf_v_valor) 		$sql .= " and sum(vgm.vgm_valor * vgm.vgm_qtde) = ".moeda2numeric($tf_v_valor)." \n";
				if($tf_v_repasse) 		$sql .= " and sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) = ".moeda2numeric($tf_v_repasse)." \n";
				if($tf_v_qtde_produtos) $sql .= " and count(*) = ".$tf_v_qtde_produtos." \n";
				if($tf_v_qtde_itens) 	$sql .= " and sum(vgm.vgm_qtde) = ".$tf_v_qtde_itens." \n";
			}
		
			$rs_venda = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_venda);
                        
			//Total Geral
			$totalGeral_valor = 0;
			$totalGeral_repasse = 0;
			$totalGeral_qtde_itens = 0;
			$totalGeral_qtde_produtos = 0;
			$users = array();
			if($total_table > 0){
				while($rs_venda_row = pg_fetch_array($rs_venda)){
					$totalGeral_valor += $rs_venda_row['valor'];
					$totalGeral_repasse += $rs_venda_row['repasse'];
					$totalGeral_qtde_itens += $rs_venda_row['qtde_itens'];
					$totalGeral_qtde_produtos += $rs_venda_row['qtde_produtos'];
					$users[$rs_venda_row['ug_id']] = null;
				}
			}

			//Ordem
			$sql .= " order by ".$ncamp;
			if($ordem == 1){
				$sql .= " desc \n";
				$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
			} else {
				$sql .= " asc \n";
				$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
			}
                        
                        /*
                         * Inicio geracao csv
                         */
                        require_once $raiz_do_projeto.'class/business/VendasLanHouseBO.class.php';
                        $vendasBO = new VendasLanHouseBO;
                        $csv = $vendasBO->geraCsv($sql);
                        /*
                         * Fim geracao CSV
                        */
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;
//if(b_IsUsuarioWagner()) { 
//echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>";
//die("Stop");
//}

			if($total_table == 0) {
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
        
//echo "tf_opr_codigo: $tf_opr_codigo<br>";	
	//Operadoras / Produtos / Valores
	 $sql = "select * from operadoras ope where opr_status = '1' order by opr_nome";
	 $rs_operadoras = SQLexecuteQuery($sql);
	 if($tf_opr_codigo) {
		 $sql = "select ogp_id,ogp_nome from tb_dist_operadora_games_produto where ogp_opr_codigo = " . $tf_opr_codigo . "";
		 $rs_oprProdutos = SQLexecuteQuery($sql);
		
		 $sql = "select pin_valor from pins_dist where opr_codigo = " . $tf_opr_codigo . " group by pin_valor order by pin_valor;";
		 $rs_oprPins = SQLexecuteQuery($sql);
	 }
        
ob_end_flush();
require_once "/www/includes/bourls.php";
?>
<!--trecho necessário para o calendario com data hora-->
<link rel="stylesheet" type="text/css" href="/css/anytime512.css" />
<!--link rel="stylesheet" type="text/css" href="<?= EPREPAG_URL_HTTP ?>/prepag2/js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css" /-->
<script language="JavaScript" src="/js/anytime512.js"></script>
<script language="JavaScript" src="/js/anytimetz.js"></script>
<script language="JavaScript" src="/js/anytimeBR.js"></script>
<!--fim do trecho necessário para o calendario com data hora-->
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
<script language="javascript">
	function GP_popupAlertMsg(msg) { //v1.0
	  document.MM_returnValue = alert(msg);
	}
	function GP_popupConfirmMsg(msg) { //v1.0
	  document.MM_returnValue = confirm(msg);
	}
	function atuaOper(id){
		if (document.getElementById('tf_opr_codigo').value != '') {
			document.form1.submit();
			//window.location = 'com_pesquisa_vendas.php?tf_opr_codigo=' + id;
		}
	}

    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        setDateInterval('tf_v_data_cancelamento_ini','tf_v_data_cancelamento_fim',optDate);
		
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
    <td>    
        <form name="form1" method="post" action="com_pesquisa_vendas.php">
        <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm pull-right bottom10">
        <table class="table top20" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8" class="texto">Venda</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Tipo de pedido</font></td>
			<td>
				<select name="tf_u_so_depositos" class="form2">
					<option value="" <?php if($tf_u_so_depositos != "1") echo "selected" ?>>Venda de PINs</option>
					<option value="1" <?php if($tf_u_so_depositos == "1") echo "selected" ?>>Depósito em Saldo (PDV Pré)</option>
				</select>
			</td>
            <td width="100" class="texto">&nbsp;</font></td>
            <td>&nbsp;</td>
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
            <td class="texto">Qtde Itens</font></td>
            <td>
              	<input name="tf_v_qtde_itens" type="text" class="form2" value="<?php echo $tf_v_qtde_itens ?>" size="7" maxlength="7">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Valor</font></td>
            <td>
              	<input name="tf_v_valor" type="text" class="form2" value="<?php echo $tf_v_valor ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Repasse</font></td>
            <td>
              	<input name="tf_v_repasse" type="text" class="form2" value="<?php echo $tf_v_repasse ?>" size="7" maxlength="7">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data Inclusão</font></td>
            <td class="texto">
              <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
			</td>
			<td colspan="2"></td>
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
              <input name="tf_v_data_concilia_ini" type="text" class="form" id="tf_v_data_concilia_ini" value="<?php echo $tf_v_data_concilia_ini ?>" size="15" maxlength="16">
              a 
              <input name="tf_v_data_concilia_fim" type="text" class="form" id="tf_v_data_concilia_fim" value="<?php echo $tf_v_data_concilia_fim ?>" size="15" maxlength="16">
			  <a href="#" onclick="document.form1.tf_v_data_concilia_ini.value='';document.form1.tf_v_data_concilia_fim.value='';">Limpar</a>
            </td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Conciliação Manual</font></td>
            <td>
				<select name="conciliado_manualmente" class="form2">
					<option value="" <?php if ($conciliado_manualmente == '') echo "selected";?>>Selecione</option>
					<option value="0" <?php if ($conciliado_manualmente == '0') echo "selected";?>>Não</option>
					<option value="1" <?php if ($conciliado_manualmente == '1') echo "selected";?>>Sim</option>
				</select>
			</td>
            <td class="texto">Data Cancelamento</font></td>
            <td class="texto">
              <input name="tf_v_data_cancelamento_ini" type="text" class="form" id="tf_v_data_cancelamento_ini" value="<?php echo $tf_v_data_cancelamento_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_v_data_cancelamento_fim" type="text" class="form" id="tf_v_data_cancelamento_fim" value="<?php echo $tf_v_data_cancelamento_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Dados do Pagamento</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Forma de Pagamento</td>
            <td colspan="3">
				<select name="tf_d_forma_pagto" class="form2">
					<option value="" <?php if($tf_d_forma_pagto == "") echo "selected" ?>>Selecione</option>
					<?php foreach ($FORMAS_PAGAMENTO_DESCRICAO as $formaId => $formaNome){ ?>
					<option value="<?php echo $formaId; ?>" <?php if ($tf_d_forma_pagto == $formaId) echo "selected";?>><?php echo $formaId . " - " . $formaNome; ?></option>
					<?php } ?>
				</select>
			</td>
		  </tr>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Usuário</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">C&oacute;digo</font></td>
            <td>
              	<input name="tf_u_codigo" type="text" class="form2" value="<?php echo $tf_u_codigo ?>" size="25">
			</td>
            <td class="texto">Nome Fantasia</font></td>
            <td>
              	<input name="tf_u_nome_fantasia" type="text" class="form2" value="<?php echo $tf_u_nome_fantasia ?>" size="25" maxlength="100">
			</td>
		  </tr>
		  <tr bgcolor="#F5F5FB"> 
            <td class="texto">Categoria</font></td>
            <td>
			   <select name="tf_u_vip" class="form2" style="width: 180px;">
					<option value="" <?php if($tf_u_vip == null || $tf_u_vip == "") echo "selected" ?>>Selecione uma categoria</option>
					<option value="0,1,2,3,4" <?php if ($tf_u_vip == "0,1,2,3,4") echo "selected";?>>Todas categorias</option>
					<?php 
					     if($tf_u_vip != null){
					          foreach ($CATEGORIA as $idCat => $catNome){ ?>
					                <option value="<?php echo $idCat; ?>" <?php if ((int)$tf_u_vip === $idCat) echo "selected";?>><?php echo $idCat . " - " . $catNome; ?></option>
					<?php 
					          }
						 }else{
							 foreach ($CATEGORIA as $idCat => $catNome){
					?>
					                <option value="<?php echo $idCat; ?>"><?php echo $idCat . " - " . $catNome; ?></option>
					<?php
					         }
						 }
					?>
				</select>
			</td>
			<td class="texto">RG</font></td>
            <td>
              	<input name="tf_u_rg" type="text" class="form2" value="<?php echo $tf_u_rg ?>" size="25" maxlength="14">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">CNPJ</font></td>
            <td>
              	<input name="tf_u_cnpj" type="text" class="form2" value="<?php echo $tf_u_cnpj ?>" size="25" maxlength="14">
			</td>
			<td class="texto">RG</font></td>
            <td>
              	<input name="tf_u_rg" type="text" class="form2" value="<?php echo $tf_u_rg ?>" size="25" maxlength="14">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Responsável</font></td>
            <td>
              	<input name="tf_u_responsavel" type="text" class="form2" value="<?php echo $tf_u_responsavel ?>" size="25" maxlength="14">
			</td>
            <td class="texto">Email</font></td>
            <td>
              	<input name="tf_u_email" type="text" class="form2" value="<?php echo $tf_u_email ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Nome</font></td>
            <td>
              	<input name="tf_u_nome" type="text" class="form2" value="<?php echo $tf_u_nome ?>" size="25" maxlength="100">
			</td>
            <td>Cidade</font></td>
            <td>
              	<input name="tf_u_cidade" id="tf_u_cidade" type="text" class="form2" value="<?php echo $tf_u_cidade ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>CPF</font></td>
            <td>
              	<input name="tf_u_cpf" type="text" class="form2" value="<?php echo $tf_u_cpf ?>" size="25" maxlength="14">
			</td>
            <td>Estado</font></td>
            <td>
				<select name="tf_u_estado" id="tf_u_estado" class="form2" class="field_dados">
					<option value="" <?php if($tf_u_estado == "") echo "selected" ?>>Selecione</option>
				<?php for($i=0; $i < count($SIGLA_ESTADOS); $i++){ ?>
					<option value="<?php echo $SIGLA_ESTADOS[$i] ?>" <?php if($tf_u_estado == $SIGLA_ESTADOS[$i]) echo "selected"; ?>><?php echo $SIGLA_ESTADOS[$i] ?></option>
				<?php } ?>
				</select>
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td colspan="2"></td>
            <td>Regi&atilde;o</font></td>
            <td>
				<select name="tf_regiao" id="tf_regiao" class="form2" class="field_dados">
					<option value="" <?php if($tf_regiao == "") echo "selected" ?>>Selecione</option>
				<?php foreach($SIGLA_REGIOES as $key => $val){ ?>
					<option value="<?php echo $key ?>" <?php if($tf_regiao == $key) echo "selected"; ?>><?php echo $SIGLA_REGIOES_LEG[$key]; ?></option>
				<?php } ?>
				</select>
			</td>
          </tr>
        <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Drupal</font></td>
        </tr>
	<tr bgcolor="#F5F5FB" id="divUsuario1"> 
            <td class="texto" width='98px'>Drupal</font></td>
            <td>
                <select name="tf_v_drupal" id="tf_v_drupal" class="form2">
                        <option value=""<?php if($tf_v_drupal!="1" && $tf_v_drupal!="-1") echo " selected"?>>Todos os registros</option>
                        <option value="1"<?php if($tf_v_drupal=="1") echo " selected"?>>Apenas pedidos registrados no site Drupal</option>
                        <option value="-1"<?php if($tf_v_drupal=="-1") echo " selected"?>>Apenas pedidos registrados no site antigo</option>
                </select>	
            </td>
        </tr>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Produto</font></td>
          </tr>
	 <!--     -------------------      -->

		<tr bgcolor="#F5F5FB"> 
		       <td width="100" class="texto">Operadora</font></td>
		       <td>
	       <select name="tf_opr_codigo" id="tf_opr_codigo" class="form2" onChange="javascript:atuaOper(this.value);">
		<option value="" <?php if($tf_opr_codigo == "") echo "selected" ?>>Selecione</option>
		<?php 
		 if($rs_operadoras) 
		  while($rs_operadoras_row = pg_fetch_array($rs_operadoras))
		  {
		?>
		   <option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>"
		   <?php 
		    if ($tf_opr_codigo == $rs_operadoras_row['opr_codigo'] || $rs_operadoras_row['opr_codigo'] == $buscaOper) 
		     echo "selected";
		   ?>><?php echo $rs_operadoras_row['opr_nome']; ?></option>
		  <?php } ?>
	       </select>
	      </td>
		       <td colspan="2" class="texto"><input name='ex_tf_opr_codigo' type='checkbox' id='ex_tf_opr_codigo' value='1' <?php if(!empty($ex_tf_opr_codigo)) echo "checked";?>/> Excluir da Pesquisa a Operadora Selecionada</td>
		     </tr>
	       <tr bgcolor="#F5F5FB"> 
		       <td class="texto">Produtos</font></td>
		       <td colspan="3" class="texto">
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
					    <?php } ?>
	      </td>
		     </tr>
		     <tr bgcolor="#F5F5FB"> 
		       <td class="texto">Valores</font></td>
		       <td colspan="3" class="texto">
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
	      </td>
		     </tr>
	 <!--     -------------------      -->
		  <tr bgcolor="#F5F5FB" class="texto"> 
			<td class="texto">Classificação</td>
			<td colspan="3" class="texto">
				<select name="tf_u_risco_classif" class="field_dados" class="form2">
					<option value="" <?php  if($tf_u_risco_classif == "") echo "selected" ?>>Selecione</option>
				<?php  for($i=1; $i < count($RISCO_CLASSIFICACAO_NOMES)+1; $i++){ ?>
					<option value="<?php  echo $RISCO_CLASSIFICACAO_NOMES[$i] ?>" <?php  if($tf_u_risco_classif == $RISCO_CLASSIFICACAO_NOMES[$i]) echo "selected"; ?>><?php  echo $RISCO_CLASSIFICACAO_NOMES[$i] ?></option>
				<?php  } ?>
				</select>
			</td>
		  </tr>

		  
		</table>

        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm pull-right"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>
    </td></tr></table>
		<?php if($total_table > 0) { ?>
</div></div>
    <div style="overflow: auto;">
    <table class="table txt-preto bg-branco fontsize-pp table-bordered" >
        <tr> 
            <td colspan="20" class="texto"> 
              Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
              a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong> <span id="txt_totais"></span></font> 
            </td>
        </tr>
        <?php $ordem = ($ordem == 1)?2:1; ?>
        <tr bgcolor="#ECE9D8">
            <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.</a> 
              <?php if($ncamp == 'vg_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              </strong></td>
            <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data Inclusão</font></a>
              <?php if($ncamp == 'vg_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              </strong></td>
<!--            <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_pagto_tipo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Forma de<br>Pagamento</font></a>
              <?php if($ncamp == 'vg_pagto_tipo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              </strong></td>-->
            <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=valor&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Valor</font></a>
              <?php if($ncamp == 'valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              </strong></td>
            <?php 
                if($tf_u_so_depositos!="1") {
            ?>
            <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=valor&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Repasse</font></a>
              <?php if($ncamp == 'repasse') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              </strong></td>
            <?php 
                }
            ?>
            <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=qtde_itens&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Qtde<br>Itens</font></a>
              <?php if($ncamp == 'qtde_itens') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              </strong></td>
            <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=qtde_produtos&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Qtde<br>Produtos</font></a>
              <?php if($ncamp == 'qtde_produtos') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              </strong></td>
            <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">C&oacute;d.<br>Usuário</font></a>
              <?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              </strong></td>
            <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_risco_classif&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Tipo<br>Usuário</font></a>
              <?php if($ncamp == 'ug_risco_classif') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              </strong></td>
            <td align="center"><strong><font class="texto">Nome Fantasia</font></strong></td>
			<td align="center"><strong><font class="texto">Categoria</font></strong></td>
             <?php 
                if($tf_u_so_depositos!="1") {
            ?>
            <td align="center"><strong><font class="texto">CPF</font></strong></td>
            <?php 
                }
            ?>
           <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_concilia&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Conciliação</font></a>
              <?php if($ncamp == 'vg_concilia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              </strong></td>
            <td align="center"><strong><font class="texto">Status</font></strong></td>
            <td align="center"><strong><font class="texto">Produtos</font></strong></td>
        </tr>
<?php
        $cor1 = $query_cor1;
        $cor2 = $query_cor1;
        $cor3 = $query_cor2;

        $listaIDs = "";

        while($rs_venda_row = pg_fetch_array($rs_venda)){

            if(!empty($listaIDs))
                $listaIDs .= ", ";
			
			switch($rs_venda_row["ug_vip"]){
				case 0:
                    $categoria = "Normal";
                break;			
				case 1:
                    $categoria = "Vip";
                break;	
				case 2:
                    $categoria = "Master";
                break;	
				case 3:
                    $categoria = "Black";
                break;	
				case 4:
                    $categoria = "Gold";
                break;
				case 5:
                    $categoria = "Platinum";
                break;					
                default:
                    $categoria = "Não encontrada";
                break;				
			}

            $listaIDs .= $rs_venda_row['vg_id'];

            $cor1 = ($cor1 == $cor2)?$cor3:$cor2;
            $status = $rs_venda_row['vg_ultimo_status'];
            $pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
            $pagto_tipo_descricao = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][getCodigoCaracterParaPagto($pagto_tipo)];

            //total
            $total_valor += $rs_venda_row['valor'];
            $total_repasse += $rs_venda_row['repasse'];
            $total_qtde_itens += $rs_venda_row['qtde_itens'];
            $total_qtde_produtos += $rs_venda_row['qtde_produtos'];

            $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
            $vg_pagto_local = $rs_venda_row['vg_pagto_local'];
            $pagto_num_docto = preg_split("/\|/", $rs_venda_row['vg_pagto_num_docto']);
            $pagto_nome_docto_Ar = preg_split("/;/", $PAGTO_NOME_DOCTO[$vg_pagto_banco][$vg_pagto_local]);

            $sql = "select opr_codigo, opr_nome, vgm.vgm_nome_produto, vgm.vgm_nome_modelo, vgm_pin_valor, vgm_qtde 
                    from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    inner join operadoras opr on opr.opr_codigo = vgm.vgm_opr_codigo
                    where vgm_vg_id = " . $rs_venda_row['vg_id'];
			
            $rs_produtos = SQLexecuteQuery($sql);

            $statusMaps = $rs_venda_row['ug_google_maps_status'];

            $statusMaps_descr = "";

            switch($statusMaps) {
                case 1: 
                    $statusMaps_descr = "Não Localizada";
                    break;
                case 2: 
                    $statusMaps_descr = "Fora do Brasil";
                    break;
                default: 
                    $statusMaps_descr = "Tipo Desconhecido";
                    if(strlen(trim($statusMaps))==0) $statusMaps_descr .= " (Empty)";
                    else $statusMaps_descr .= " ('$statusMaps')";
                    break;
            }

            if($rs_venda_row['ug_coord_lat']==0 && $rs_venda_row['ug_coord_lng']==0) {
                if($statusMaps_descr!="") $statusMaps_descr.= "\n";
                $statusMaps_descr .= "Sem Geolocalização";
            } else {
                $statusMaps_descr .= "\n[".number_format($rs_venda_row['ug_coord_lat'], 2, '.', '.').", ".number_format($rs_venda_row['ug_coord_lng'], 2, '.', '.')."]";
            }

            if(trim($statusMaps)=="") {
                if($rs_venda_row['ug_coord_lat']==0 && $rs_venda_row['ug_coord_lng']==0) {
                    $statusMaps = "<font color='red'>Coords=0</font>";
                    $statusMaps_title = "Coords=0";
                    $statusMaps_small = "<font color='red'>*</font>";
                } else {
                    $statusMaps = "<font color='blue'>Com_Coords</font>";
                    $statusMaps_title = "Com_Coords";
                    $statusMaps_small = "";
                }
            }
?>
        <tr bgcolor="<?php echo $cor1 ?>">
            <td nowrap valign="top" class="texto" align="center">
                <a style="text-decoration:none" href="com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['vg_id'] ?>&fila_ncamp=<?php echo $ncamp?>&fila_ordem=<?php echo ($ordem == 1?2:1) ?><?php echo $varsel?>"><?php echo $rs_venda_row['vg_id'] ?></a>
                <?php
                if($rs_venda_row['vg_drupal_order_id']>0) {
                    echo "<br><nobr>(dr_id: ".$rs_venda_row['vg_drupal_order_id'].")</nobr>";
                }
                ?>
            </td>
            <td nowrap valign="top" class="texto" align="center">
                <?php echo formata_data_ts($rs_venda_row['vg_data_inclusao'],0, true,true) ?>
            </td>
<!--            <td nowrap valign="top" class="texto">
                <span style='color:#006600'><?php echo $pagto_tipo_descricao ?></span>
            </td>-->
            <td nowrap valign="top" class="texto" align="right"><?php echo number_format($rs_venda_row['valor'], 2, ',','.') ?></td>
            <?php 
            if($tf_u_so_depositos!="1") {
            ?>
            <td nowrap valign="top" class="texto" align="right"><?php echo number_format($rs_venda_row['repasse'], 2, ',','.') ?></td>
            <?php 
            }
            ?>
            <td nowrap valign="top" class="texto" align="right"><?php echo $rs_venda_row['qtde_itens'] ?></td>
            <td nowrap valign="top" class="texto" align="right"><?php echo $rs_venda_row['qtde_produtos'] ?></td>
            <td nowrap valign="top" class="texto" align="center">
                <a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_venda_row['ug_id'] ?>"><?php echo $rs_venda_row['ug_id'] ?></a>
            </td>
            <td nowrap valign="top" class="texto" align="center">
                <?php echo (($rs_venda_row['ug_risco_classif']==1)?"<font color='darkgreen'>Pós</font>":(($rs_venda_row['ug_risco_classif']==2)?"<font color='blue'>Pré</font>":"???"))."(".$rs_venda_row['ug_tipo_cadastro'].")" ?> <?php echo "<span title='".$statusMaps_title."'><nobr> ".$statusMaps_small." </nobr></span>" ?></td>
            <td nowrap valign="top" class="texto">
                <a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_venda_row['ug_id'] ?>">
                <?php echo (strtoupper($rs_venda_row['ug_tipo_cadastro']) == 'PF' ) ? $rs_venda_row['ug_nome'] : substr($rs_venda_row['ug_nome_fantasia'],0,30); ?>
                </a>
            </td>
			<td nowrap valign="top" class="texto">
			    <?php echo $categoria; ?>
            </td>
            <?php 
            if($tf_u_so_depositos!="1") {
            ?>
            <td nowrap valign="top" class="texto" align="right"><?php echo $rs_venda_row['vgm_cpf']; ?></td>
            <?php 
            }
            if(	$status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] ||
                $status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] ||
                $status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] ||
                $status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'])
            {
?>
                <td nowrap valign="top" class="texto" align="center">
                    <a style="text-decoration:none" href="com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['vg_id'] ?>&fila_ncamp=<?php echo $ncamp?>&fila_ordem=<?php echo ($ordem == 1?2:1) ?><?php echo $varsel?>">
                    <?php
						
						$vg_usuario_obs = $rs_venda_row['vg_usuario_obs'];
						$verifica_conciliacao_manual = stripos($vg_usuario_obs, "conciliado manualmente");

						if ($rs_venda_row['vg_concilia'] != 0 && $verifica_conciliacao_manual == false) {
							echo "<font color='#009933'>Conciliado</font>";
						} else if ($rs_venda_row['vg_concilia'] != 0 && $verifica_conciliacao_manual !== false) {
							echo "<font color='#009933'>Conciliado Manualmente</font>";
						} else {
							echo "<font color='#ff0000'>Não conciliado</font>";
						}
					?>
					
                    </a>
                </td>
            <?php } else { ?>
                <td nowrap valign="top" class="texto" align="center">
					<font color='#ff0000'>Não conciliado</font>
				</td>
            <?php } 

            if($status == $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA']){?>
            <td nowrap valign="top" class="texto"><font color="FF0000"><?php echo substr($GLOBALS['STATUS_VENDA_DESCRICAO'][$status], 0, strpos($GLOBALS['STATUS_VENDA_DESCRICAO'][$status], '.')) ?></font></td>
            <?php } else {?>
            <td nowrap valign="top" class="texto"><?php echo substr($GLOBALS['STATUS_VENDA_DESCRICAO'][$status], 0, strpos($GLOBALS['STATUS_VENDA_DESCRICAO'][$status], '.')) ?></td>
			
            <?php } ?>
            <td nowrap valign="top" class="texto" nowrap valign="top">
                <?php if($rs_produtos && pg_num_rows($rs_produtos) > 0){ ?>
                        <table class="texto table" border="0" width="400" cellpadding="0" cellspacing="0">
                            <tr bgcolor="#ECE9D8">
                                <td nowrap width="30%">Operadora</td>
                                <td nowrap width="50%">Produto</td>
                                <td nowrap width="20%">Valor de face</td>
                                <td nowrap width="50%">Qtde</td>
                            </tr>
                        <?php while($rs_produtos_row = pg_fetch_array($rs_produtos)){ ?>
                                <tr>
                                <td nowrap><?php echo $rs_produtos_row['opr_nome']?></td>
                                <td nowrap><?php echo $rs_produtos_row['vgm_nome_produto']?> 
                                <?php if($rs_produtos_row['vgm_nome_modelo']!=""){?> - <?php echo $rs_produtos_row['vgm_nome_modelo']?><?php }?></td>
                                <td nowrap align="right"><?php echo number_format($rs_produtos_row['vgm_pin_valor'], 2, ',','.')?></td>
                                <td nowrap align="right"><?php echo $rs_produtos_row['vgm_qtde']?></td>
                                </tr>
                        <?php } ?>
                        </table>
                <?php } ?>
            </td>
        </tr>
<?php 	
        }	
?>
        <tr bgcolor="E5E5EB"> 
            <td class="texto" align="right" colspan="3"><b>Total:</b></td>
            <td class="texto" align="right"><?php echo number_format($total_valor, 2, ',','.') ?></td>
            <?php 
                if($tf_u_so_depositos!="1") {
            ?>
            <td class="texto" align="right"><?php echo number_format($total_repasse, 2, ',','.') ?></td>
            <?php 
                }
            ?>
            <td class="texto" align="right"><?php echo number_format($total_qtde_itens, 0, '','.') ?></td>
            <td class="texto" align="right"><?php echo number_format($total_qtde_produtos, 0, '','.') ?></td>
            <td class="texto" align="right" colspan="7"></td>
        </tr>
        <tr bgcolor="D5D5DB"> 
            <td class="texto" align="right" colspan="3"><b>Total Geral:</b></td>
            <td class="texto" align="right"><?php echo number_format($totalGeral_valor, 2, ',','.') ?></td>
            <?php 
                if($tf_u_so_depositos!="1") {
            ?>
            <td class="texto" align="right"><?php echo number_format($totalGeral_repasse, 2, ',','.') ?></td>
            <?php 
                }
            ?>
            <td class="texto" align="right"><?php echo number_format($totalGeral_qtde_itens, 0, '','.') ?></td>
            <td class="texto" align="right"><?php echo number_format($totalGeral_qtde_produtos, 0, '','.') ?></td>
            <td class="texto" colspan="7"><?php echo " Número de LANs: ".count($users); ?></td>
        </tr>
        <tr> 
            <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
        </tr>
        <?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
    </table>
	</div>
    <div class="col-md-12 bottom10">
        <a style="float: right; text-decoration: none;" target="_blank" href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/download/downloadCsv.php?dir=bkov&csv=<?php echo $csv;?>">
            <input type="button" value="Exportar" class="btn btn-sm btn-success">
        </a>
    </div>
    <div class="txt-preto">
        <div>
        <script language="JavaScript">
          document.getElementById('txt_totais').innerHTML = '( <?php echo number_format($total_valor, 2, ',', '.') ?> / <?php echo number_format($totalGeral_valor, 2, ',', '.') ?>)';
        </script>
    
<?php  
}

echo "<br><span class='texto'>Lista de IDs de Vendas da Pesquisa:</span><hr><span class='texto'>".$listaIDs."</span><hr>";
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
<?php 

function is_csv_numeric($list) {
//echo "list: '$list'<br>";
	$list1 = str_replace(" ", "", $list);
//echo "list1: '$list1'<br>";
	$alist = explode(",", $list1);
//echo "alist: <pre>".print_r($alist, true)."</pre><br>";
	$bret = true;
	foreach($alist as $key => $val) {
//echo $val." - ".((is_numeric($val))?"NUMERIC":"ALPHA")."<br>";
		$bret = is_numeric($val);
		if(!$bret) {
			break;
		}
	}
	return $bret;
	
}							
?>
