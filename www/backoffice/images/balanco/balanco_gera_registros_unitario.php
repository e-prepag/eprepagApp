<?php 
	header("Content-Type: text/html; charset=ISO-8859-1",true);

include "../includes/classPrincipal.php"; 
include "../includes/function_time.php"; 
$run_silently = 1;
include "../../../incs/topo_bko.php"; 

include "../../financeiro/corte/corte_constantes.php"; 
include "inc_balanco.php"; 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//echo "GLOBALS['connid']: ".print_r($GLOBALS['connid'],true)."<br>";


$saldo_balancos = 0;
//$t_ini = microtime(true);
	$time_start_stats = getmicrotime();



$usuario_id = $_REQUEST['id'];

//$GLOBALS['_SESSION']['sql'] = "";
//$GLOBALS['_SESSION']['ug_fPerfilSaldo'] = "";

//die("n_dias: $n_dias, usuario_id: ".$usuario_id);
//echo "n_dias: $n_dias, usuario_id: ".$usuario_id."<br>";

//$cad_usuarioGames = new UsuarioGames($usuario_id);
//$objUsuarioGames = $cad_usuarioGames;
//echo $GLOBALS['_SESSION']['sql']."<br>";
/*
if(is_object($cad_usuarioGames)) {
	echo "ID: ".$cad_usuarioGames->getID()."<br>";
	echo "Nome: ".$cad_usuarioGames->getPerfilSaldo()."<br>";
	echo "<pre>".print_r($cad_usuarioGames,true)."</pre>";
} else {
	echo "cad_usuarioGames is not an object<br>";
}
*/
//die("Stop");

	if (!$objUsuarioGames = UsuarioGames::getUsuarioGamesById($usuario_id)) {
		$msg = "<br><b><font color='red'>N&atilde;o foi poss&iacute;vel encontrar PDV com ID: ".$usuario_id."</font></b><br>";
	} else {
//		echo "ID: ".$objUsuarioGames->getID()."<br>";
//		echo "Nome: ".$objUsuarioGames->getPerfilSaldo()."<br>";
//		echo "<pre>".print_r($objUsuarioGames,true)."</pre>";
	}
//echo "SQL (SESSION): ".$GLOBALS['_SESSION']['sql']."<br>";
//echo "usuarioGames (SESSION): ".$GLOBALS['_SESSION']['usuarioGames']."<br>";
//echo "pg_num_rows (SESSION): ".$GLOBALS['_SESSION']['pg_num_rows']."<br>";

/*
if(is_object($objUsuarioGames)) {
	echo "Nome: ".$objUsuarioGames->getID()."<br>";
} else {
	echo "objUsuarioGames is not an object<br>";
}
*/
//echo $GLOBALS['_SESSION']['sql']."<br>";
//echo "BALANCO_DATA_ABERTURA: '".$BALANCO_DATA_ABERTURA."'<br>";
				if($msg == ""){
				//Recupera as vendas//////////////////////

				////// data do inicio do balanco 

				$data = $BALANCO_DATA_ABERTURA;

				$fim_data = strtotime($BALANCO_DATA_ABERTURA);
				//// setei o intervalo para 10
				// $n_dias = 10;/////

				/////////////////// nada aqui //////////
				$data_balanco = data_mais_n($data, $n_dias);


				/// peguei a data de hoje
				$hoje = date('Y-m-d');

				// medi a quantidade de dias entre a abertura de balanco até hoje
				$qtddias = qtde_dias($data,$hoje);


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

				
				echo $data_bal;

				$hoje = formata_data($hoje,1);

			

				$data_balanco = strtotime($hoje);
				$data_limit = $objUsuarioGames->getDataInclusao();
			
				$data_limit = formata_data($data_limit,1);
		
				$data_limit = strtotime($data_limit);

		
				$t_lan = $objUsuarioGames->getRiscoClassif();
				$risco = $objUsuarioGames->getRiscoClassif();
			
				$saldo = $objUsuarioGames->getPerfilSaldo();
				$limite = $objUsuarioGames->getPerfilLimite();
			
				$limite_ref =  $objUsuarioGames->getPerfilLimiteRef();


	// limite original só para ter como base.
	$limite_original = $limite_ref;

	$saldo_balancos = $saldo;
//echo "ug_fPerfilSaldo: ".$GLOBALS['_SESSION']['ug_fPerfilSaldo']."<br>";
//print "entrei na venda:".$saldo; die("Stop sdsd");

	if ( $t_lan == 1 ) {

		if ( $limite > 0 ) {

			$limite += $saldo; 
			
		} 

		if ($limite == 0 && $limite_ref > 0 ) {

			$limite += $limite_ref ;

		}
	}

	
	if($msg == ""){

		$sql  = "select vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_usuario_obs, 
					sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, 
					sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse
				from tb_dist_venda_games vg 
				inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
				where vg.vg_ug_id=" . $usuario_id;
		$sql .=	" group by vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_usuario_obs ";

		$rs_total = SQLexecuteQuery($sql);
		
		if($rs_total) $registros_total += pg_num_rows($rs_total);

		
		$sql .= " order by vg.vg_data_inclusao desc " ;
		
//echo "<hr>$sql<br>";
	
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

	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///// RECUPERA VALOR DE CREDITOS COMPRADO - PRE PAGO ////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//  if ($risco == 2) {
		$sql = "select * ";
		$sql .= "from boletos_pendentes, bancos_financeiros, tb_dist_venda_games, dist_boleto_bancario_games  ";
		$sql .= "where (bol_banco = bco_codigo)  and (bol_venda_games_id=vg_id) and (bco_rpp = 1) ";
		$sql .= "and vg_ug_id= ".$usuario_id." ";
		$sql .= "and substr(bol_documento,1,1) = '4' ";
		$sql .= "and bbg_ug_id=".$usuario_id." ";
		$sql .= "and bbg_vg_id = vg_id ";
		$sql .= "order by bol_importacao desc "; //"bol_data desc";

		
//echo "<hr>$sql<br>";

		$res_count = SQLexecuteQuery($sql);

		if($res_count) $registros_total += pg_num_rows($res_count);

			
	/////////////////////////////////////////////////////////////////////////////////////////
	/// codigo para pegar a data do boleto mais recente para desenhar corretamente o extrato
	/////////////////////////////////////////////////////////////////////////////////////////
	
		$info_bol = pg_fetch_array($res_count);

		$data_tratada = substr($info_bol['bol_aprovado_data'],0,10);

		$data_bol = strtotime($data_tratada);


//  }

	//////////////////////////////////////////////////////////////////////////////////////////////
	//////// Codigo para carregar os cortes POS PAGO /////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////

//	if ($risco == 1 ) {

	$sql = "select * from cortes c
				where c.cor_ug_id = $usuario_id
				order by cor_periodo_fim desc, cor_periodo_ini desc";
				$res_corte = SQLexecuteQuery($sql);
	if($res_corte) $registros_total += pg_num_rows($res_corte);
		
	$info_corte = pg_fetch_array($res_corte);
	$data_tratada = $info_corte['cor_periodo_fim'];
	$data_cor = strtotime($data_tratada);

//	}
	


		$i = 0;
		$i_balanco = 1;
		$n_balanco = 164;
		echo "<br>Max de $n_balanco registros<br>\n";
		echo "<br>Lidos do BD $registros_total registros<br>\n";

		// variavel rows controla o while para varrer até a quantidade de registros setada
		$rows = 0;
		
			while( $rows < $registros_total ){ 
				$rodada++;
					
					 //////////////////////////// LOAD DIVIDA BOLETO //////////////////////////////////////////////////
					 //// só desenha a linha se a data do boleto for maior que a data dos pedidos 
					 // se boleto tiver a data maior que da data da compra de pins

															// if ( $risco == 2) {
															 if ($data_bol >= $data_cred	&& $data_bol >= $data_balanco) {
															// se tiver registro para carregar e desenhar a tabela 
															
																														
															$total_entrada += $info_bol['bol_valor'] - $info_bol['bbg_valor_taxa'];
															$boleto_valor = $info_bol['bol_valor'] - $info_bol['bbg_valor_taxa'];

															$saldo_balancos -= $boleto_valor ;

														
															$t_lan = 2;

														

															 if (  $boleto_valor > 0 ) {  
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
																	
																
												 
															 }// fim do if data do boleto

														

														//} // if boleto == risco ?>
								 <?php
													 //////////////////////////// LOAD CORTE SEMANAL //////////////////////////////////////////////////
													 //////////////////////////////////////////////////////////////////////////////////////////////////

													 //// só desenha a linha se a data do corte for maior que a data dos pedidos 
										//		if ($risco == 1) {	
													// se boleto tiver a data maior que da data da compra de pins

													
														 if ($data_cor >= $data_cred && $data_cor >= $data_balanco ) {
											
											print "entrei no corte:".$saldo_balancos."<br>"; //die("Stop 231");
													// se tiver registro para carregar e desenhar a tabela 
																if ($data_cor != '') {
																	
																	 if (  $info_corte['cor_venda_liquida'] > 0 ) { 
																		
																		 $t_lan = 1; 
																	
																		 $limite -= $info_corte['cor_venda_liquida'];
																		 $saldo -= 	$info_corte['cor_venda_liquida'];																				
																	 } // fim if valor > 0
																	 ///recebe data e trata para comparar com a de compra de pins da proxima rodada 
																	
																	// $saldo_balancos -= $info_corte['cor_venda_liquida'];

																	 $total_entrada += $info_corte['cor_venda_liquida'];
																	 $info_corte = pg_fetch_array($res_corte);
																	  $data_tratadac = $info_corte['cor_periodo_fim'];
																	  $data_cor = strtotime($data_tratadac);
																	  
																	  $c++;
																	  
																} // fim if fetch_array

														 }// fim do if data do corte 
														 
											//	}// fim do if risco 1

													
					 					
					 //////////////// Pedido	desenha linha /////////////////////////	
						 /// compara se a data do corte é menor que a do pedido então desenha o pedido
				
											if ( $data_cor < $data_cred && $data_bol < $data_cred && $data_cred > $data_balanco) {		
												
											print "entrei na venda:".$saldo_balancos; //die("Stop 32");

												if ( $data_cred != '') { 
														//pos
														//	 if ($risco == 1 ) {
														//	 }
														//pre
														//	if ($risco == 2) {
														//$saldo_balancos += $rs_vendas_row['repasse'];
														//	}

														if ( $t_lan == 1) {
														$limite += $rs_vendas_row['repasse'];
														$saldo += $rs_vendas_row['repasse'];
														} else {
														$saldo_balancos += $rs_vendas_row['repasse'];
														}

														 
														
														$rs_vendas_row = pg_fetch_array($rs_vendas);

														$comissao = $rs_vendas_row['valor'] - $rs_vendas_row['repasse'] ;
														$total_saida += $rs_vendas_row['repasse'];
														$total_comissao += $comissao;


														$data_tratada = substr($rs_vendas_row['vg_data_inclusao'],0,10);
														$data_cred = strtotime($data_tratada);	
														$s++;


														
														 ///// Tirando o valor do repasse para comissão ///// 
												}
										   } // fim if que compara se o corte é maior que o pedido

				
			
					//////////////////////////// LOAD DESENHA BALANCO //////////////////////////////////////////////////
					// só desenha a linha do balanco se a data do balanco for maior que as datas do corte, do pedido e do boleto
				while ($data_balanco >= $data_cred && $data_balanco >= $data_bol && $data_balanco >= $data_cor && $data_balanco >= $data_limit) {
								// se tiver registro para carregar e desenhar a tabela 
									
						
 // ********************* GRAVA REGISTROS ***********************************************************//
			


						$data_bal_grava = formata_data($data_bal,1);
						$data_bal_grava .= " 23:59:59";
						$query_insert = "insert into dist_balancos (ug_id_lan,ug_valor_balanco,ug_tipo_lan,ug_data_balanco,ug_limite,ug_saldo, ug_balanco_historico) 	values (";
	
						$query_insert .= SQLaddFields($usuario_id,"").",";
	
						if ( $t_lan == '1' ) {

							$query_insert .= SQLaddFields($limite,"").",";

						} else {

							$query_insert .= SQLaddFields($saldo_balancos,"").",";

						}

						$query_insert .= SQLaddFields($t_lan,"").",";

						$query_insert .= "'".SQLaddFields($data_bal_grava,"")."',";

						$query_insert .= SQLaddFields($limite_original,"").",";
	
						$query_insert .= SQLaddFields($saldo_balancos,"").", ";

						$query_insert .= " 1) ";	// aqui todos são registros historicos

					if ( $t_lan == 2) {
						if ( $saldo_velho != $saldo_balancos ) {
						
							echo "<br>$i_balanco: <b>".$query_insert."</b><br>\n";
		//<--- Destravar					SQLexecuteQUERY($query_insert);
							$i_balanco++;
//							if($i_balanco>=$n_balanco) die("StopA");

							$saldo_velho = $saldo_balancos; 
						} else {
							echo "Não houve vendas/pagamentos<br>";
						}
					//	echo "<br>".$limite_velho = $limite;

					} else {
						
						if ( $limite_velho != $limite ) {

							echo "<br>$i_balanco: <b>".$query_insert."</b><br>\n";
		//<--- Destravar					SQLexecuteQUERY($query_insert);
							$i_balanco++;
//							if($i_balanco>=$n_balanco) die("StopB");

							$limite_velho = $limite;
						} else {
							echo "Não houve vendas/pagamentos<br>";
						}

					}				


// ***************************************************************************************************//

							 ///recebe data e trata para comparar com a de compra de pins da proxima rodada 
				$data_bal =	data_menos_n($data_bal,$n_dias);
							
							// converter para formato americano 
				$data_balanco = formata_data($data_bal,1);

				$data_balanco .= " 23:59:59"; 

				$data_balanco = strtotime($data_balanco);

							
							
			}// fim do while
								
			$rows++;	
								// passa
		} // fim do row registros de pedido

	// fim do msg ação

//die();

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

				}
echo "Processamento primeira parte em ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ." s.";
//print "BUG : <br><br>".$saldo_balancos;
//echo $msg;
//die();
?>


