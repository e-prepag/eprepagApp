<?php 
// Ver tarefas/cadastrabalancosLH.bat que executa tarefas/balanco_executa.php
die("Stop");

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 


	$bHTML = (php_sapi_name()=="isapi");

	if(!$bHTML) { 
		$cReturn = "\n";
		$cBr = "\n";
		$cHr = str_repeat("-",80)."\n";
		$cHr2 = str_repeat("=",80)."\n";

		$cBold = "";
		$cBoldEnd = "";
		$cH3 = "\n";
		$cH3End = "\n";
		$cFontAlert1 = "";
		$cFontAlert2 = "";
		$cFontAlertRed = "";
		$cFontAlertBlue = "";
		$cFontAlertGreen = "";
		$cFontAlertEnd = "";

		echo "=================================================================\n";
	} else {
		$cReturn = "<br>\n";
		$cBr = "<br>";
		$cHr = "<hr>";
		$cHr2 = "<hr color='#6666FF'>";
		
		$cBold = "<b>";
		$cBoldEnd = "</b>";
		$cH3 = "<h3>";
		$cH3End = "</h3>";
		$cFontAlert1 = "<font color='#FF0000'>";
		$cFontAlert2 = "<font color='#009900'>";
		$cFontAlertRed = "<font color='red'>";
		$cFontAlertBlue = "<font color='blue'>";
		$cFontAlertGreen = "<font color='darkgreen'>";
		$cFontAlertEnd = "</font>";

?>

	<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title> Processa transações </title>
	</head>

	<body>
<?php } 

	set_time_limit(30000) ;

	$b_db_execute = true;	//false;

include "../includes/classPrincipal.php";

$raiz_do_projeto = "C:\\Sites\\E-Prepag";
	
//desenvolvimento	
if(false) $raiz_do_projeto = "D:\\Projetos\\Outros\\E-Prepag\\Sites\\Producao";
$_SERVER['DOCUMENT_ROOT'] = $raiz_do_projeto . "\\backoffice\\web";

$webstring = "http://".$_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'];
require $_SERVER['DOCUMENT_ROOT']."/incs/configuracao.inc";
require $_SERVER['DOCUMENT_ROOT']."/connections/connect.php";
include $_SERVER['DOCUMENT_ROOT']."/incs/header.php";
include $_SERVER['DOCUMENT_ROOT']."/incs/security.php";
require $_SERVER['DOCUMENT_ROOT']."/incs/functions.php";

include "../includes/function_time.php"; ?>

<?php

	ob_end_flush();

$time_start = getmicrotime();


// quando publicar lembre-se de comentar essa linha pois aqui no dev os saldos e limits estão encriptados
$objEncryption = new Encryption();

// Seleciona cada lanhouse :
//real ---------> $query = "select ug_id,ug_perfil_saldo,ug_perfil_limite,ug_risco_classif from dist_usuarios_games where ug_ativo = '1' ";
 $query = "select * from dist_usuarios_games ug order by ug.ug_id, ug.ug_login ";	//"where ug_id in (43)";	//", 2596, 522, 3733, 2725, 4000, 3547, 3236, 2644, 3268, 468, 3, 389, 2743, 3971, 17, 6)";	// "limit 10"//" where ug_id = 406 ";

echo "query: ".$query.$cReturn;

//// configura o intervalo de verificação de venda no caso 5 , signica que vai verificar se houve venda dentro dos ultimos 5 dias 
//$n_dias = 7;

//////////////////////////////////////////////////////////////////////

$rs_query = SQLexecuteQUERY($query);

// Obter apenas a lista de ug_id e fazer um loop por essa lista -> assim o transaction fica limitado a cada registro de lan e não para todas as lans
$a_ug_id = array();
while ($movimento_info = pg_fetch_array($rs_query)) {
	$a_ug_id[] = $movimento_info['ug_id'];
//	echo $movimento_info['ug_id'].", ";
}

echo "count(a_ug_id): ".count($a_ug_id).$cReturn;

$data_de_hoje = date("Y-m-d H:i:s");	// "2010-12-01 00:00:00";	
$msg = "";
$nrows = 0;
$str_lista_lans = "";
$n_lista_lans = 0;

//echo "BALANCO_ZERO_FLOAT: ".$BALANCO_ZERO_FLOAT.$cReturn;

//while ($movimento_info = pg_fetch_array($rs_query)) {
for($i=0;$i<count($a_ug_id);$i++) {

	$time_start_lh = getmicrotime();
	$query = "select * from dist_usuarios_games ug where ug_id = ".$a_ug_id[$i]."";	

	echo "query: ".$query.$cReturn;

	$rs_query = SQLexecuteQUERY($query);
	if($rs_query) {
		$movimento_info = pg_fetch_array($rs_query);


		// Init geral values
		$saldo = 0;
		$total = 0;

		$saldo_ultimo_balanco = 0;
		$b_cadastra_novo_balanco = false;
		$data_ultimo_balanco = "";
		$s_resumo = "";
		$b_insert = false;

		// Init lan values
		$id_lan = $movimento_info['ug_id'];
		$lan_saldo = (is_null($movimento_info['ug_perfil_saldo'])?0:$movimento_info['ug_perfil_saldo']);
		$lan_limite = (is_null($movimento_info['ug_perfil_limite'])?0:$movimento_info['ug_perfil_limite']);
		$tipo_lan = $movimento_info['ug_risco_classif'];
		$data_leitura = data_menos_n($data_de_hoje,0); // Exibição da data para leitura humana


	$s_resumo .= $cHr2."Entering n: $i (".number_format( $i*100/count($a_ug_id), 2, '.', '.')."%) '$data_de_hoje'  ID: $id_lan (tipo_lan: ".(($tipo_lan==1)?"PÓS":(($tipo_lan==2)?"PRÉ":"???")).", Ativa: ".(($movimento_info['ug_ativo']==1)?"SIM":"não").", limite: $lan_limite, saldo: $lan_saldo)".$cReturn;	
		///////////////////////////////////////////////////////////////////////////////////////////////

		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			if($b_db_execute) {
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao iniciar transação.".$cReturn;
			}
		}

		if($msg == ""){

//			$ultimo_balanco_query = "select max(coalesce (db_data_balanco,'2001-01-01'::timestamp without time zone) )as data_primeiro_balanco, db_saldo, db_limite, db_valor_balanco from dist_balancos where db_ug_id = $id_lan group by db_saldo, db_limite, db_valor_balanco, db_data_balanco order by db_data_balanco desc ";
			$ultimo_balanco_query = "select coalesce (db_data_balanco,'2001-01-01'::timestamp without time zone) as data_ultimo_balanco, db_saldo, db_limite, db_valor_balanco 
			from dist_balancos 
			where db_ug_id = $id_lan 
			order by db_data_balanco desc limit 1";


echo "ultimo_balanco_query: ".$ultimo_balanco_query.$cReturn;

			$rs_ultimo_balanco_query = SQLexecuteQuery($ultimo_balanco_query);
			
			if(!$rs_ultimo_balanco_query || pg_num_rows($rs_ultimo_balanco_query) == 0){
				//$msg = "Erro ao selecionar MAX() de balanço".$cReturn;
				$stmp = "******* Não encontrou ponto inicial (ug_id:$id_lan '$ultimo_balanco_query')".$cReturn;
				$s_resumo .= $stmp;
				echo $stmp."".$cReturn;

			} else {
				$dados_ultimo_balanco_query = pg_fetch_array($rs_ultimo_balanco_query);
				$data_ultimo_balanco = $dados_ultimo_balanco_query['data_ultimo_balanco'];
				$limite_ultimo_balanco =  $dados_ultimo_balanco_query['db_limite'];
				$saldo_ultimo_balanco =  $dados_ultimo_balanco_query['db_saldo'];
				$pos_valor_ultimo_balanco = $dados_ultimo_balanco_query['db_valor_balanco'];
				$db_resultado = 0;//// <------- significa que não é um ponto inicial

				$stmp = "******* Encontrado ponto inical (ug_id:$id_lan -> '$data_ultimo_balanco')".$cReturn;
				$s_resumo .= $stmp;
				echo $stmp."".$cReturn;
			}
		}

		if ($data_ultimo_balanco == "") {
			// => Criar ponto inicial de balanço (ou seja, dados da lan neste instante)

			$limite_ultimo_balanco = 0;
			$saldo_ultimo_balanco = 0;
			$pos_valor_ultimo_balanco = 0;
			$db_resultado = 5;//// <------- significa que é um ponto inicial


			$sql_lh = "select * from dist_usuarios_games where ug_id=$id_lan";
			$rs_lh = SQLexecuteQuery($sql_lh);
			
			if(!$rs_lh || pg_num_rows($rs_lh) == 0){
				$msg = "ERRO ao levantar dados do PDV $id_lan ($sql)".$cReturn;
			} else {
				$b_cadastra_novo_balanco = true;
				$data_ultimo_balanco = date("Y-m-d H:i:s");
				$dados_lh = pg_fetch_array($rs_lh);
				$ug_risco_classif = $dados_lh['ug_risco_classif'];

				$limite_ultimo_balanco = $dados_lh['ug_perfil_limite'];
				$saldo_ultimo_balanco = $dados_lh['ug_perfil_saldo'];
				if($ug_risco_classif==1) {
					$saldo = $dados_lh['ug_perfil_limite'];
				} elseif($ug_risco_classif==2) {
					$saldo = $dados_lh['ug_perfil_saldo'];
				} else {
					$saldo = 0;
				}
			
				$stmp = "-------- ============ Cadastra novo balanço $id_lan".$cReturn;
				$s_resumo .= $stmp;
				echo $stmp."".$cReturn;
	/*	  
	O CAMPO db_resultados APRESENTA NUMEROS QUE SIGNIFICA ISSO:

	1 - O SALDO PRÉ ESTA OK
	2 - FALHA NA CONTAGEM DO SALDO DE UMA LAN PRÉ
	3 - O SALDO DA LAN PÓS ESTA CORRETO
	4 - FALHA NO SALDO DE UMA LAN PÓS
	5 - É O PONTO INICIAL **
	*/

			}
		} else {
			echo $cFontAlertBlue."Existe data_ultimo_balanco: '$data_ultimo_balanco'".$cFontAlertEnd.$cReturn; 
		}

		$s_tipo_balanco = $cFontAlertGreen."=== data_ultimo_balanco: '$data_ultimo_balanco' ".(($b_cadastra_novo_balanco) ? "Cadastra Balanço <b>Ponto inicial</b>)" : "Cadastra Balanço comun")."".$cFontAlertEnd.$cReturn;
		echo $s_tipo_balanco."".$cReturn;

	//if($msg == ""){
		// pos
		if ($tipo_lan == 1) {

			// Corrigir os decrypts tambem 
			//<---- AQUI BUG DE ENCRIPTAÇÃO ---->///
	//		$total = $objEncryption->decrypt($movimento_info['ug_perfil_limite']);
			$total = (is_null($movimento_info['ug_perfil_limite'])?0:$movimento_info['ug_perfil_limite']);
				
		} else {// pre
			// Aqui tambem
			//<---- AQUI BUG DE ENCRIPTAÇÃO ---->///
	//		$total = $objEncryption->decrypt($movimento_info['ug_perfil_saldo']);
			$total = (is_null($movimento_info['ug_perfil_saldo'])?0:$movimento_info['ug_perfil_saldo']);
		}

		/////Aqui
		//<---- AQUI BUG DE ENCRIPTAÇÃO ---->///
	//	$saldo  = $objEncryption->decrypt($movimento_info['ug_perfil_saldo']);
		$ug_perfil_saldo  = (is_null($movimento_info['ug_perfil_saldo'])?0:$movimento_info['ug_perfil_saldo']); // usar aqui para o produção

		/////Aqui
		//<---- AQUI BUG DE ENCRIPTAÇÃO ---->///
	//	$limite = $objEncryption->decrypt($movimento_info['ug_perfil_limite']);
		$limite = (is_null($movimento_info['ug_perfil_limite'])?0:$movimento_info['ug_perfil_limite']); // usar aqui para o produção

		$stmp = "Resumo - total: ".number_format($total, 2, ',', '.').", ug_perfil_saldo: ".number_format($ug_perfil_saldo, 2, ',', '.').", limite: ".number_format($limite, 2, ',', '.')."".$cReturn;
		$s_resumo .= $stmp;
		echo $stmp."".$cReturn;

			
		if ($data_ultimo_balanco != '' ) {

//echo "b_cadastra_novo_balanco: '".(($b_cadastra_novo_balanco)?"TRUE":"false")."'".$cReturn;
			$qtde_vendas = 0;
			$qtde_boletos = 0;
			$qtde_cortes = 0;
			$qtde_pag_online = 0;
			$val_vendas = 0;
			$val_boletos = 0;
			$val_cortes = 0;
			$val_pag_online = 0;

			// Se não é ponto inicial => tenta cadastrar balanço caso tenha vendas/boletos/pagamentos
			if(!$b_cadastra_novo_balanco) {

				////// <--------- CONFERINDO VALORES ----------------> ////////////////////////////////////
				//// 1 -- RESGASTAR OS VALORES -----------------------  ///////////////////////////////////
				
				$data_final_intervalo = $data_de_hoje;	// date("Y-m-d H:i:s");

				/// A QUERY ABAIXO IRÁ PUXAR UMA LISTA COM VENDAS , BOLETOS , PAGAMENTOS , CORTES PARA PODER FAZER A CONTAGEM  
				$sql = "select num_doc, tipo_pagto, data_transacao, valor, sum(valor - repasse) as comissao, repasse, tipo, status from ( 
				(select (vg.vg_id::text) as num_doc,
				vg.vg_data_inclusao as data_transacao,
				vg.vg_pagto_tipo as tipo_pagto,
				(sum(vgm.vgm_valor * vgm.vgm_qtde)::real) as valor,
				sum(vgm.vgm_qtde) as qtde_itens,
				count(*) as qtde_produtos, 
				sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse ,
				'Venda'::text as tipo,
				 NULL::text as status
				from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join dist_usuarios_games ug on vg.vg_ug_id = ug.ug_id where 1=1 and ug_id = $id_lan and vg.vg_data_inclusao between '$data_ultimo_balanco' and '$data_final_intervalo' group by num_doc, data_transacao, tipo_pagto, vg.vg_ultimo_status,vg.vg_ug_id,ug.ug_nome_fantasia,ug.ug_nome,ug.ug_cpf,ug.ug_cnpj ) 
					
				union all 

				(select (bol_documento::text) as num_doc,
				bol_importacao as data_transacao,
				vg_pagto_tipo as tipo_pagto ,
				(sum (bol_valor - bbg_valor_taxa)::real) as valor ,
				NULL::int as qtde_itens,
				NULL::int as qtde_produtos ,
				NULL::real as repasse ,
				'Boleto'::text as tipo,
				'Pre'::text as status

				from boletos_pendentes, bancos_financeiros, tb_dist_venda_games, dist_boleto_bancario_games, dist_usuarios_games ug where 1=1 and ug_id =  $id_lan and (bol_banco = bco_codigo) and (bol_venda_games_id=vg_id) and (bco_rpp = 1) and (vg_ug_id = ug_id)  and bbg_vg_id = vg_id and bol_importacao between '$data_ultimo_balanco' and '$data_final_intervalo' and substr(bol_documento,1,1) = '4' group by bol_documento,vg_data_inclusao,vg_pagto_tipo,bol_importacao,ug_id,ug_nome_fantasia,ug_nome,ug_cpf,ug_cnpj ) 

				union all

				(select numcompra::text as num_doc,
				datainicio as data_transacao,
				(case when iforma='A' then 10 else iforma::int end ) as tipo_pagto, 
				(sum (total/100 - taxas)::real) as valor, NULL as qtde_itens, NULL as qtde_produtos , NULL as repasse , 'BoletoPagtoOnline' as tipo, (case when tipo_cliente='LR' then 'Pre' when tipo_cliente='LO' then 'Pos' else '???' end) as status from tb_pag_compras where substr(tipo_cliente,1,1)='L' and idcliente=$id_lan and status=3 and datacompra between '$data_ultimo_balanco' and '$data_final_intervalo' group by numcompra::text, datainicio, tipo_pagto, tipo_cliente order by data_transacao ) 
					
				union all

				(select (bbc_documento::text) as num_doc,
				bbc_data_inclusao as data_transacao,
				cor_tipo_pagto as tipo_pagto,
				(cor_venda_liquida::real) as valor,
				NULL::int as qtde_itens,
				NULL::int as qtde_produtos,
				NULL::real as repasse,
				'Corte'::text as tipo,
				'Pos'::text as status

				from cortes c inner join boleto_bancario_cortes as bbc on cor_bbc_boleto_codigo = bbc_boleto_codigo inner join dist_usuarios_games ug on c.cor_ug_id = ug.ug_id where 1=1 and ug.ug_id = $id_lan and c.cor_venda_liquida > 0 and bbc_data_inclusao between '$data_ultimo_balanco' and '$data_final_intervalo')
						
				) as venda 
				group by venda.num_doc,venda.tipo_pagto,venda.data_transacao,venda.valor,tipo,repasse,status 
				order by data_transacao desc  ";
				
	//echo $cReturn.str_replace("\n", "<br>\n", $sql).$cReturn;
	//$s_resumo .= $cReturn.str_replace("\n", "<br>\n", $sql).$cReturn;


	///////// TRATEI VALORES PARA EVITAR DIVERGENCIAS ABAIXO DE 0,001/////////////////////////////
	//////////<------------ ATENÇÃO AQUI DECIDIR A RESPEITO DE TODAS AS VARIAVEIS ---->///////////
				if ($saldo_ultimo_balanco < $BALANCO_ZERO_FLOAT) {
					$saldo_ultimo_balanco = 0;
				}
	///////////<---------- SE TODAS AS OUTRAS TAMBEM SERÃO TRATADAS ------------------>///////////
	//////////////////////////////////////////////////////////////////////////////////////////////

				$res_conta_lista = SQLexecuteQuery($sql);

				while ($info_conta_lista = pg_fetch_array($res_conta_lista)) {
				
					switch ($info_conta_lista['tipo']) {

						case 'Venda':
							$val_vendas += $info_conta_lista['valor'];
							$qtde_vendas++;
							break;

						case 'Boleto':
							$val_boletos += $info_conta_lista['valor'];
							$qtde_boletos++;
							break;

						case 'Corte':
							$val_cortes += $info_conta_lista['valor'];
							$qtde_cortes++;
							break;

						case 'BoletoPagtoOnline':
							$val_pag_online += $info_conta_lista['valor'];
							$qtde_pag_online++;
							break;

					}
				}// fim while info_conta_lista pg_fetch_array

				$valor_final = 0; /// setei para 0 para não haver poluição 
			//// 2 -  PARTE DE CONFERENCIAS  : AQUI IREMOS FAZER AS CONTAS DE SUBTRAçÃO E SOMA, " TIPO_LAN = 1 - POS , 2 - PRE "

				$s_resumo .= "   saldo ultimo balanco: ".number_format($saldo_ultimo_balanco,2).$cReturn;
				$s_resumo .= "   saldo ultimo pos balanco :". number_format($pos_valor_ultimo_balanco,2).$cReturn;
				$s_resumo .= "   val vendas :".number_format($val_vendas,2).$cReturn;
				$s_resumo .= "   val pag online :".number_format($val_pag_online,2).$cReturn;
				$s_resumo .= "   val cortes :".number_format($val_cortes,2).$cReturn;
				$s_resumo .= "   val boletos :".number_format($val_boletos,2).$cReturn;
				
				$s_resumo .= "  <span style='color:blue; background-color:#FFFFCC'>($nrows) nVendas: $qtde_vendas venda(s), nBoletos: $qtde_boletos boleto(s), $qtde_cortes corte(s), PagOnline: $qtde_pag_online pagamento(s) online</span>  Total: <b>$total</b>".$cReturn;

	//echo $s_resumo."".$cReturn;

				if ( $tipo_lan == 1 ) {	///// SE A LANHOUSE FOR POS

					$saldo_pos = ($limite - $val_vendas);
					$saldo = $saldo_pos;				
					
					//$limite_ultimo_balanco
					/// O SALDO POS É O VALOR QUE RESULTA DA SOBRA ENTRE O QUE JA FOI GASTO DO LIMITE
					$valor_final =  ($pos_valor_ultimo_balanco - $val_vendas) + ($val_pag_online + $val_cortes + $val_boletos); 
					
					//// APARTIR da 13ª casa depois da virgula esta havendo diferenças entre os numeros DECIDIR MAIS TARDE UMA SOLUÇÃO 
					$s_resumo .= "VALOR FINAL CALCULADO DE LIMITE: ".number_format($valor_final,2). " -----> "." LIMITE ATUAL DO PERFIL : ".number_format($saldo_pos,2).$cReturn.$cReturn;	
					if ($valor_final != $saldo_pos) {

	$s_resumo .= $cFontAlertRed."HOUVE FALHA NO LIMITE !!!!!! ".$cFontAlertEnd.$cReturn;
	$s_resumo .= "Cortes : $val_cortes".$cReturn."vendas: $val_vendas".$cReturn."--------------".$cReturn."$valor_final";

						$db_resultado = 4; // 4 resultado falha na contagem do limite
					} else {
	$s_resumo .= $cFontAlertBlue." O LIMITE ESTA CORRETO !!!!!!! ".$cFontAlertEnd.$cReturn;
						$db_resultado = 3; // 3 resultado ok o limite esta correto
					}			
				} else {	///// SE A LANHOUSE FOR PRE

					$saldo = $ug_perfil_saldo;

					$valor_final = ($saldo_ultimo_balanco - $val_vendas) + ($val_pag_online + $val_cortes + $val_boletos); 

					if ($valor_final != $ug_perfil_saldo) {
	$s_resumo .= $cFontAlertGreen."VALOR FINAL CALCULADO DE SALDO: ".$valor_final. " -----> "." SALDO ATUAL DO PERFIL : ".$ug_perfil_saldo."".$cFontAlertEnd.$cReturn;
	$s_resumo .= "Boletos : $val_boletos".$cReturn."vendas: $val_vendas".$cReturn." -------------- ".$cReturn."$valor_final";
	$s_resumo .= $cFontAlertRed."HOUVE FALHA NO SALDO !!!!!! ".$cFontAlertEnd.$cReturn;
						$db_resultado = 2; // 2 resultado falha na contagem do saldo
					} else {

	$s_resumo .= $cFontAlertBlue." O SALDO ESTA CORRETO !!!!!!! ".$cFontAlertEnd.$cReturn;
						$db_resultado = 1; // 1 saldo correto
					}
				} /// fim if tipo lan

			} // fim 	if(!$b_cadastra_novo_balanco) 
	//echo $s_resumo."".$cReturn;

			if(($qtde_vendas+$qtde_boletos+$qtde_cortes+$qtde_pag_online)>0) {
				echo "  +++++  será inserido o balanco (qtde_total ".($qtde_vendas+$qtde_boletos+$qtde_cortes+$qtde_pag_online).").".$cReturn;

				$saldo = (!$saldo)?0:$saldo;
				$total = (!$total)?0:$total;

				$query_insert = "insert into dist_balancos (
				db_ug_id,
				db_saldo,
				db_limite,
				db_tipo_lan,
				db_data_balanco,
				db_valor_balanco,
				db_qtde_cortes,
				db_qtde_boletos,
				db_qtde_vendas,
				db_qtde_pagonline,
				db_resultado,
				db_val_boletos,
				db_val_vendas,
				db_val_cortes,
				db_val_pag_online

				) values ( ";
				$query_insert .= "'".$id_lan."',";
				$query_insert .= "'".$saldo."',";
				$query_insert .= "'".$limite."',";
				$query_insert .= "'".$tipo_lan."',";
				$query_insert .= "'".$data_de_hoje."',";
				$query_insert .= "'".$total."',";
				$query_insert .= " ".$qtde_cortes.",";
				$query_insert .= " ".$qtde_boletos.",";
				$query_insert .= " ".$qtde_vendas.",";
				$query_insert .= " ".$qtde_pag_online.",";
				$query_insert .= " ".$db_resultado.",";
				$query_insert .= " ".$val_boletos.",";
				$query_insert .= " ".$val_vendas.",";
				$query_insert .= " ".$val_cortes.",";
				$query_insert .= " ".$val_pag_online.")";
				
$s_resumo .= "SQL Insert: ".$cFontAlertGreen.$query_insert."".$cFontAlertEnd.$cReturn;

				if($b_db_execute && false) {
					SQLexecuteQuery($query_insert);
				}
			
				$b_insert = true;

				$s_resumo .= "<span style='color:#009933; background-color:#FFFFCC'>ID PDV: <b>".$id_lan. "</b>, Saldo: <b>".$saldo. "</b>,  Limite: <b>".$limite. "</b>Data: <b>".$data_de_hoje. "</b> - Saldo Total: <b>".$total."</b></span>".$cReturn;
				$str_lista_lans .= $movimento_info['ug_id']." - ".$movimento_info['ug_login']."".$cReturn;
				$n_lista_lans++;

				$nrows++;
			} else {
				$s_resumo .= "  ---------  NADA será inserido no balanco.</span>".$cReturn;
			}
	//echo $s_resumo."".$cReturn;

		} else {
			$s_resumo .= $cHr2."Nenhum balanço encontrado ou criado para o PDV: ".$id_lan.$cFontAlertRed." Não será cadastrado Balanco para ela.".$cFontAlertEnd.$cReturn;
		}

	//echo "$msg".$cReturn;
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			if($b_db_execute) {
	//echo "  === COMMIT".$cReturn;
				$ret = SQLexecuteQuery($sql);
			}
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			if($b_db_execute) {
	//echo "  === ROLLBACK".$cReturn;
				$ret = SQLexecuteQuery($sql);
			}
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		if($b_insert) {
			echo $s_resumo."".$cReturn;
		}

		// Clear errors
		$msg = "";
	//	if($nrows>=10) {
	//		break;
	//	}

	} else {
		echo $cFontAlertRed."LH ug_id = ".$a_ug_id[$i]." não foi encontrada".$cFontAlertEnd.$cReturn;
	}// fim da consulta para lan por ug_id
//if($nrows>5) die("Stop");
	echo "<p>Elapsed time: ".number_format(getmicrotime() - $time_start_lh, 2, ',', '.')."s</p>";
if($b_output) {
	echo $cHr2;
}
//if($i>100) break;

}// fim do ciclo for por Lans
//}// fim do while por Lans

echo str_repeat("-",80)."\n";
echo "n_lista_lans: ".$n_lista_lans." lans com novos balanços".$cReturn;
echo "str_lista_lans: ".$cReturn.$str_lista_lans."".$cReturn;

echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit.", nrows=$nrows, i=$i (".number_format( $nrows/(getmicrotime() - $time_start), 2, '.', '.')."cadastro/s, (".number_format( $i/(getmicrotime() - $time_start), 2, '.', '.')." processadas/s)".$cReturn;
echo "Projeção para ".count($a_ug_id)." registros: ".number_format( count($a_ug_id)*(getmicrotime() - $time_start)/$i, 2, '.', '.')."s".$cReturn;

?>

