<?php 
	header("Content-Type: text/html; charset=ISO-8859-1",true);
include "../includes/classPrincipal.php";
include "../includes/function_time.php";
$run_silently = 1;
include "../../../incs/topo_bko.php";
include "../../financeiro/corte/corte_constantes.php";
include "inc_balanco.php";


die("Stop");


	set_time_limit(30000) ;

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//$t_ini = microtime(true);
	$time_start_stats = getmicrotime();


echo "Now: ".date("Y-m-d H:i:s")."<br>";
// Calcula total de lans ativas/inativas
$total_query = "select ug_id from dist_usuarios_games order by ug_id";
$total_rs_query = SQLexecuteQuery($total_query);
$total_row = pg_num_rows($total_rs_query);

		$i_balanco = 1;
//		$n_balanco = 164;
//		echo "<hr>Max de $n_balanco registros<br>\n";
		echo "<br>Lidos do BD $total_row registros de LHs<br><hr>\n";


// Fabio### - 17
// Mandrake - 2644
// where ug_id = 17 
$query = "select ug_id from dist_usuarios_games order by ug_id;";	// where ug_ativo = 1 // 

print "<hr>".$query."<hr>";

$rs_query = SQLexecuteQuery($query);

$lh_atual = 0;
while ($balanco_info = pg_fetch_array($rs_query)) {

	$msg = "";

	echo "<hr color='red'>".(++$lh_atual) ." de ".$total_row."<br>\n";

	$usuarioId = $balanco_info['ug_id'];
echo "usuarioId: $usuarioId<br>";
echo "MSG: $msg<br>";

	$usuario_id = $usuarioId;

	$objUsuarioGames = UsuarioGames::getUsuarioGamesById($usuario_id);

	if($msg == ""){
				//Recupera as vendas//////////////////////


				////// data do inicio do balanco 
		$data = $BALANCO_DATA_ABERTURA;
				
				////// 
		$fim_data = strtotime($BALANCO_DATA_ABERTURA);
				//// setei o intervalo para 10
		// $n_dias = 10;/////

				/////////////////// nada aqui //////////
		$data_balanco = data_mais_n($data, $n_dias);


		/// peguei a data de hoje
		$hoje = date('Y-m-d');
		
		//  0123456789
		// '2008-01-01' -> '01-01-2008'
		$data_br = substr($data,8,2)."-".substr($data,5,2)."-".substr($data,0,4);
		$hoje_br = date('d-m-Y');
		
		// medi a quantidade de dias entre a abertura de balanco até hoje
		$qtddias = qtde_dias($data_br,$hoje_br);
echo "qtde_dias('$data_br','$hoje_br') = $qtddias<br>";


				// removi a diferença das datas (quantos dias estão acima do balanco mais recente)
		$dif_dias = $qtddias % $n_dias;

				////////////////////////////////////////////
				////////////////////////////////////////////
				
				// puxo a quantidade de dias necessarios para achar ultimo balanco mais recente
				// $data_ajustada = $qtddias - $dif_dias;
				
				//// atribui a data de hoje para achar a data do ultimo balanco

		$hoje = formata_data_ts($hoje,0,true,false);

		$hoje = data_menos_n($hoje,$dif_dias);
		$data_bal = data_menos_n($hoje,$dif_dias);
		$hoje = formata_data($hoje,1);

		$data_balanco = strtotime($hoje);
		$data_limit = $objUsuarioGames->getDataInclusao();
			
		$data_limit = formata_data($data_limit,1);
		
		$data_limit = strtotime($data_limit);

		$t_lan = $risco = $objUsuarioGames->getRiscoClassif();
			
		$saldo = $objUsuarioGames->getPerfilSaldo();
		$limite = $objUsuarioGames->getPerfilLimite();
			
		$limite_ref =  $objUsuarioGames->getPerfilLimiteRef();


		// limite original só para ter como base.
		$limite_original = $limite_ref;

		$saldo_balanco = $saldo;

echo "&nbsp;&nbsp; = LH vi processar: ID: $usuario_id, Nome: '".(($t_lan==1)?$objUsuarioGames->getNome():$objUsuarioGames->getNomeFantasia())."', Saldo: $saldo, limite: $limite, ativa: ".(($objUsuarioGames->getAtivo()==1)?"<font color='blue'>ATIVA</font>":"<font color='red'>Inativa</font>")."<br>";


		if ( $t_lan == 1 ) {

			if ( $limite > 0 ) {

				$limite += $saldo; 
				
			} 

			if ($limite == 0 && $limite_ref > 0 ) {

				$limite += $limite_ref ;

			}
		}

		// Vamos contar quantos Venda+Cortes+Pagamentos
		$registros_total = 0;
	
		if($msg == ""){

			// Levantar vendas de PINs
			$sql  = "select vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_usuario_obs, 
						sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, 
						sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse
					from tb_dist_venda_games vg 
					inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					where vg.vg_ug_id=" . $usuario_id;

		
			if($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and vg.vg_id=" . $tf_v_codigo;

			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 

			if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)

				$sql .= " and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'";
			$sql .=	" group by vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_usuario_obs ";
		
			$rs_total = SQLexecuteQuery($sql);
		
			// Acrescenta Vendas a $registros_total
			if($rs_total) $registros_total += pg_num_rows($rs_total);
echo "  ========= (registros_total Vendas: (mais ".pg_num_rows($rs_total).") - total: $registros_total)<br>";

			$sql .= " order by vg.vg_data_inclusao desc " ;
			
	
			/////////////////////////////////////////////////////////////////////
			//echo $sql; ////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////////
					
			$rs_vendas = SQLexecuteQuery($sql);
		
	
			if(!$rs_vendas || pg_num_rows($rs_vendas) == 0) $msg = "Nenhuma venda encontrada.\n";
			$rs_vendas_row = pg_fetch_array($rs_vendas);
			
			$data_tratada = substr($rs_vendas_row['vg_data_inclusao'],0,10);
			$data_cred = strtotime($data_tratada);	

			$comissao = $rs_vendas_row['valor'] - $rs_vendas_row['repasse'] ;
			$total_saida += $rs_vendas_row['repasse'];
			$total_comissao += $comissao;

echo "LH Vendas: total_saida: $total_saida<br>";

	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///// RECUPERA VALOR DE CREDITOS COMPRADOS - PRE PAGO ////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$sql = "select * ";
	$sql .= "from boletos_pendentes, bancos_financeiros, tb_dist_venda_games, dist_boleto_bancario_games  ";
	$sql .= "where (bol_banco = bco_codigo)  and (bol_venda_games_id=vg_id) and (bco_rpp = 1) ";
	$sql .= "and vg_ug_id= ".$usuarioId." ";
	$sql .= "and bol_documento LIKE '4%' ";
	$sql .= "and bbg_ug_id=".$usuarioId." ";
	$sql .= "and bbg_vg_id = vg_id ";
	$sql .= "order by bol_importacao desc "; //"bol_data desc";

	$res_count = SQLexecuteQuery($sql);

	// Acrescenta Boleto Pre a $registros_total
	if($res_count) $registros_total += pg_num_rows($res_count);
echo "  ========= (registros_total Boleto Pre (mais ".pg_num_rows($res_count).") - total: $registros_total)<br>";

		
	/////////////////////////////////////////////////////////////////////////////////////////
	/// codigo para pegar a data do boleto mais recente para desenhar corretamente o extrato
	/////////////////////////////////////////////////////////////////////////////////////////
	
	$info_bol = pg_fetch_array($res_count);

	$data_tratada = substr($info_bol['bol_aprovado_data'],0,10);

	$data_bol = strtotime($data_tratada);


	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///// RECUPERA VALOR DE PAGAMENTOS ONLINE - PRE PAGO ////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$sql = "select datainicio, 
		(case when iforma='A' then 10 else iforma::int end ) as tipo_pagto, 
		sum (total/100 - taxas) as valor, 
		(case when tipo_cliente='LR' then '2' when tipo_cliente='LO' then '1' else '???' end) as status 
		from tb_pag_compras 
		where substr(tipo_cliente,1,1)='L' and idcliente=".$usuarioId." and status=3 
		group by datainicio, tipo_pagto, tipo_cliente
		order by datainicio desc;"; 
echo "$sql<br>";



	$res_pag_count = SQLexecuteQuery($sql);

	// Acrescenta Boleto Pre a $registros_total
	if($res_pag_count) $registros_total += pg_num_rows($res_pag_count);
echo "  ========= (registros_total Pagamentos Online (mais ".pg_num_rows($res_pag_count).") - total: $registros_total)<br>";

		
	/////////////////////////////////////////////////////////////////////////////////////////
	/// codigo para pegar a data do boleto mais recente para desenhar corretamente o extrato
	/////////////////////////////////////////////////////////////////////////////////////////
	
	$info_pag_online = pg_fetch_array($res_pag_count);

	$data_pag_online_tratada = substr($info_pag_online['datainicio'],0,10);

	$data_pag_online = strtotime($data_pag_online_tratada);


	//////////////////////////////////////////////////////////////////////////////////////////////
	//////// Codigo para carregar os cortes POS PAGO /////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	$sql = "select * from cortes c
				where c.cor_ug_id = $usuarioId
				order by cor_periodo_fim desc, cor_periodo_ini desc";
	$res_corte = SQLexecuteQuery($sql);

echo "$sql<br>";

	// Acrescenta Boleto Corte a $registros_total
	if($res_corte) $registros_total += pg_num_rows($res_corte);
echo "  ========= (registros_total Corte (mais ".pg_num_rows($res_corte).") - total: $registros_total)<br>";

	$info_corte = pg_fetch_array($res_corte);
	$data_tratada = $info_corte['cor_periodo_fim'];
	$data_cor = strtotime($data_tratada);
echo " = = data_tratada de CORTE mais recente: '$data_tratada'  === <br>\n";

	$i = 0;
	// variavel rows controla o while para varrer até a quantidade de registros setada
	$rows = 0;
echo "  ========= (registros_total final : $registros_total)<br>";
	// 
	while( $rows < $registros_total ){

		//////////////////////////// LOAD DIVIDA BOLETO //////////////////////////////////////////////////
		//// só desenha a linha se a data do boleto for maior que a data dos pedidos 
		// se boleto tiver a data maior que da data da compra de pins

		if ($data_bol >= $data_cred	&& 
			$data_bol >= $data_balanco && 
			$data_bol >= $data_pag_online) {

		// se tiver registro para carregar e desenhar a tabela 
			$total_entrada += $info_bol['bol_valor'] - $info_bol['bbg_valor_taxa'];
			$boleto_valor = $info_bol['bol_valor'] - $info_bol['bbg_valor_taxa'];

			$saldo_balanco -= $boleto_valor ;

			// Força o tipo de lan para Pré
			$t_lan = 2;

			if ( $boleto_valor > 0 ) {  
				//////// VIEW DATAS E PREÇOS
				$numero_view = $info_bol['bol_documento'];
														
				$data_view  = formata_data_ts($info_bol['vg_data_inclusao'], 0, true, false);
				$valor_view = number_format($boleto_valor,2, ',','.');
													
												
			 }
			 ///recebe data e trata para comparar com a de compra de pins da proxima rodada 
			$info_bol = pg_fetch_array($res_count);
														
			$data_tratada = substr($info_bol['vg_data_inclusao'],0,10);
			$data_bol = strtotime($data_tratada);
			$b++;
//echo "<font color='red'> NOVO DATA_BOL: '$data_bol'</font><br>";
		}// fim do if data do boleto

		
							
		//////////////////////////// LOAD CORTE SEMANAL //////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////

		//// só desenha a linha se a data do corte for maior que a data dos pedidos 
		//		if ($risco == 1) {	
		// se boleto tiver a data maior que da data da compra de pins
		 if ($data_cor >= $data_cred && 
			 $data_cor >= $data_balanco && 
			 $data_cor >= $data_pag_online) {
											
			// se tiver registro para carregar e desenhar a tabela 
			if ($data_cor != '') {
																
				if (  $info_corte['cor_venda_liquida'] > 0 ) { 
						
					// Força o tipo de Lan Pós
					$t_lan = 1; 
															
					$limite -= $info_corte['cor_venda_liquida'];
					$saldo -= 	$info_corte['cor_venda_liquida'];																				
				 } // fim if valor > 0
				///recebe data e trata para comparar com a de compra de pins da proxima rodada 
																	
				/// $saldo_balanco -= $info_corte['cor_venda_liquida'];

			$total_entrada += $info_corte['cor_venda_liquida'];
			$info_corte = pg_fetch_array($res_corte);
			$data_tratadac = $info_corte['cor_periodo_fim'];
			$data_cor = strtotime($data_tratadac);
			$c++;
//echo "<font color='red'> NOVO DATA_COR: '$data_cor'</font><br>";

			} // fim if fetch_array

		}// fim do if data do corte 
														 
		//////////////// Pedido	desenha linha /////////////////////////	
		/// compara se a data do corte é menor que a do pedido então desenha o pedido
				
		if ( $data_cred > $data_cor && 
			 $data_cred > $data_bol && 
			 $data_cred > $data_balanco && 
			 $data_cred > $data_pag_online) {
												
			if ( $data_cred != '') { 
			
				if ( $t_lan == 1) {
					$limite += $rs_vendas_row['repasse'];
					$saldo += $rs_vendas_row['repasse'];
				} else {
					$saldo_balanco += $rs_vendas_row['repasse'];
				}
										

				$rs_vendas_row = pg_fetch_array($rs_vendas);

				$comissao = $rs_vendas_row['valor'] - $rs_vendas_row['repasse'] ;
				$total_saida += $rs_vendas_row['repasse'];
				$total_comissao += $comissao;

				$data_tratada = substr($rs_vendas_row['vg_data_inclusao'],0,10);
				$data_cred = strtotime($data_tratada);	
				$s++;

													
				 ///// Tirando o valor do repasse para comissão ///// 
													
//echo "<font color='red'> NOVO DATA_CRED: '$data_cred'</font><br>";
			}
		} // fim if que compara se o corte é maior que o pedido
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////
		////////////////////////////// CALCULO PAGAMENTO ONLINE /////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////////////////////////////////

		if (	$data_pag_online >= $data_bol && 
				$data_pag_online >= $data_balanco && 
				$data_pag_online >= $data_cor &&
				$data_pag_online >= $data_cred ) {
		// se tiver registro para carregar e desenhar a tabela 

		$total_entrada += $info_pag_online['valor'];
		$boleto_valor = $info_pag_online['valor'];
		
		//	$total_entrada += $info_bol['bol_valor'] - $info_bol['bbg_valor_taxa'];
		//	$boleto_valor = $info_bol['bol_valor'] - $info_bol['bbg_valor_taxa'];

			$saldo_balanco -= $boleto_valor ;

			// Força o tipo de lan para Pré
			$t_lan = $info_pag_online['status'];

			if ( $boleto_valor > 0 ) {  
				//////// VIEW DATAS E PREÇOS
			//	$numero_view = $info_bol['bol_documento'];
														
			//	$data_view  = formata_data_ts($info_pag_online['data'], 0, true, false);
			//	$valor_view = number_format($boleto_valor,2, ',','.');
													
			 }
			 ///recebe data e trata para comparar com a de compra de pins da proxima rodada 

			$info_pag_online = pg_fetch_array($res_pag_count);
														
			$data_tratada = substr($info_pag_online['data'],0,10);
			$data_pag_online = strtotime($data_tratada);
			$pag_online++;
//echo "<font color='red'> NOVO DATA_PAG_ONLINE: '$data_pag_online'</font><br>";

		}// fim do if data de pagamento online

		//////////////////////////// LOAD DESENHA BALANCO //////////////////////////////////////////////////
		// só desenha a linha do balanco se a data do balanco for maior que as datas do corte, do pedido e do boleto
		while ( $data_balanco >= $data_cred && 
				$data_balanco >= $data_bol && 
				$data_balanco >= $data_cor && 
				$data_balanco >= $data_pag_online && 
				$data_balanco >= $data_limit) {
		// se tiver registro para carregar e desenhar a tabela 
				
		// ********************* GRAVA REGISTROS ***********************************************************//

			$data_bal_grava = formata_data($data_bal,1);
			$data_bal_grava .= " 23:59:59";

			$query_insert = "insert into dist_balancos (db_ug_id,db_valor_balanco,db_tipo_lan,db_data_balanco,db_limite,db_saldo, db_balanco_historico) 	values (";
	
			$query_insert .= SQLaddFields($usuarioId,"").",";
	
			if ( $t_lan == '1' ) {

				$query_insert .= SQLaddFields($limite,"").",";

			} else {

				$query_insert .= SQLaddFields($saldo_balanco,"").",";

			}

			$query_insert .= SQLaddFields($t_lan,"").",";
	
			$query_insert .= "'".SQLaddFields($data_bal_grava,"")."',";

			$query_insert .= SQLaddFields($limite_original,"").",";
	
			$query_insert .= SQLaddFields($saldo_balanco,"").", ";
	
			$query_insert .= " 1) ";	// aqui todos são registros historicos
// print $t_lan;

			if ( $t_lan == 2) {
				if ( $saldo_velho != $saldo_balanco ) {
						
					echo "<br>$i_balanco (Pre)<b>".$query_insert."</b><br>";
					
					SQLexecuteQuery($query_insert);
					
					$i_balanco++;
					$saldo_velho = $saldo_balanco; 
				}
				//	echo "<br>".$limite_velho = $limite;

			} else {
						
				if ( $limite_velho != $limite ) {

					echo "<br>$i_balanco (Pos)<b>".$query_insert."</b><br>";
					
					SQLexecuteQuery($query_insert);
					
					$i_balanco++;

					$limite_velho = $limite;
				}

			}
				
			//	print $query_insert;

// ***************************************************************************************************//

							 ///recebe data e trata para comparar com a de compra de pins da proxima rodada 
				$data_bal =	data_menos_n($data_bal,$n_dias);
							
							// converter para formato americano 
				$data_balanco = formata_data($data_bal,1);
				$data_balanco .= " 23:59:59"; 

				$data_balanco = strtotime($data_balanco);

							
//echo "<font color='green'> NOVO DATA_BALANCO: '$data_balanco'</font><br>";
							
			}// fim do while
			
			$rows++;	

/*
echo " = = ROW [$rows] : data_balanco: '$data_balanco', data_cred: '$data_cred', data_bol: '$data_bol', data_cor: '$data_cor', data_pag_online: '$data_pag_online', data_limit: '$data_limit' [".(($data_balanco >= $data_cred && 
				$data_balanco >= $data_bol && 
				$data_balanco >= $data_cor && 
				$data_balanco >= $data_pag_online && 
				$data_balanco >= $data_limit)?"TRUE":"FALSE")."]<br>\n";
*/
		} // fim do row registros de pedido

echo " = = MSG $row<br>\n";
	flush() ;
	}// fim do msg ação

/*
	if($lh_atual>=20) {
		echo "Processamento primeira parte em ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ." s.<br>Média de ".number_format((getmicrotime() - $time_start_stats)/$lh_atual, 2, '.', '.') ." s/lh.\n";

		die("lh_atual: $lh_atual, Stop");
	}
*/
	flush() ;

} // fim do while que varre todas as lans 

/*
$t_fim = microtime(true);

$final_time = $t_fim - $t_ini;


$num =  $d - $n;

$final_time = $final_time  * $num;

// pegar segundos
$pos = strpos($final_time,'.');

$final_time2 = substr($final_time,0,$pos);

echo "Tempo restante  ".timePassed($final_time2)."<br>";
*/
echo "Processamento primeira parte em ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ." s.<br>Média de ".number_format((getmicrotime() - $time_start_stats)/$lh_atual, 2, '.', '.') ." s/lh.";


//die();
?>

