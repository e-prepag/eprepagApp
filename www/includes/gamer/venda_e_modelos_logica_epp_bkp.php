<?php
// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 
//gravaLog_DebugTMP("Entering venda_e_modelos_logica.php");

	//Recupera usuario
	if(isset($GLOBALS['_SESSION']['usuarioGames_ser']) && !is_null($GLOBALS['_SESSION']['usuarioGames_ser'])){
		$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
		$usuarioId = $usuarioGames->getId();
	}

if($usuarioGames->b_IsLogin_Reinaldo()) {
//gravaLog_MCOIN("Entering venda_e_modelos_logica.php"."\n");

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
//gravaLog_PagtoPINEPP("Em venda_e_modelos_logica.php\n_REQUEST: ".print_r($GLOBALS['_REQUEST'], true)."\n_SESSION: ".print_r($GLOBALS['_SESSION'], true));
}
if($usuarioGames->b_IsLogin_Reinaldo()) {
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
}
	//Recupera Id da venda
	if(!$venda_id_request_nome) $venda_id_request_nome = 'venda';
	$venda_id = $GLOBALS['_REQUEST'][$venda_id_request_nome];
	if(!$venda_id) $venda_id = $GLOBALS['_SESSION']['venda'];

	//Guarda id da venda no session
	$GLOBALS['_SESSION']['venda'] = $venda_id;

//	gravaLog_DRUPAL_TMP("Em venda_e_modelos_logica.php: {venda_id = '$venda_id'} => ".$_SERVER["SCRIPT_FILENAME"]."\n");
	
	//Validacoes
	$msg = "";	

	//Valida id da venda
	if($msg == ""){
		if(!$venda_id || !is_numeric($venda_id)){		
			$msg = "Id da venda inválido ou não fornecido.\n";
//	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

//			gravaLog_DRUPAL_TMP("Em venda_e_modelos_logica.php: '".$msg."' => ".$_SERVER["SCRIPT_FILENAME"]."\n");
		}
	}
		
	//Recupera a venda
	if($msg == ""){
		$sql  = "select * from tb_venda_games vg " .
				"where vg.vg_id = " . $venda_id . " and vg.vg_ug_id=" . $usuarioId;
				echo "venda: ".$sql;
		$rs_venda = SQLexecuteQuery($sql);
		if(!$rs_venda || pg_num_rows($rs_venda) == 0) $msg = "Nenhuma venda encontrada.\n";
	}

//gravaLog_TMP("Em venda_e_modelos_logica.php (".date("Y-m-d H:i:s").")\n\t".$sql."\n");

//echo "<!-- $sql -->\n";
if($usuarioGames->b_IsLogin_Reinaldo()) {
//gravaLog_MCOIN($sql."\n");
//gravaLog_PagtoPINEPP("Em venda_e_modelos_logica.php 2\n".$sql."\nGLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']: ".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']."\nGLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']:".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']."\n");
}
/*	
if($usuarioGames->b_IsLogin_Reinaldo()) {
//echo " sql: ".$sql."<br>";
echo " GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']: ".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']."<br>";
echo " GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']: ".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']."<br>";

//die("Stop 43ss311");
}
*/

//if($usuarioGames->b_IsLogin_Reinaldo()) {
//echo "pagamento.pagto.deposito.em.saldo: ".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']."<br>";
//echo "pagamento.pagto.deposito.em.saldo.num.docto: ".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']."<br>";
//}

	if(((strlen($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo'])>0) && (strlen($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto'])>0))) {
		//Recupera modelos para deposito em saldo
		if($msg == ""){

			if($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']=='2') {
				$sql  = "select vg.*, 1 as vgm_qtde, bbg.bbg_valor as vgm_valor, 0 as vgm_perc_desconto, 'Crédito online EPP Cash (R\$' || to_char(bbg_valor-bbg_valor_taxa,'FM9999.00') || ')' as vgm_nome_produto, '' as vgm_nome_modelo ";
				$sql .= "from tb_venda_games vg  ";
				$sql .= "inner join boleto_bancario_games bbg on bbg.bbg_vg_id = vg.vg_id ";
				$sql .= "where vg.vg_id = " . $venda_id . " and vg.vg_ug_id=" . $usuarioId;
			} else {

				$sql  = "select vg.*, 1 as vgm_qtde, (total/100-taxas) as vgm_valor, 0 as vgm_perc_desconto, 'Crédito online EPP Cash (R\$' || to_char((total/100-taxas),'FM9999.00') || ')' as vgm_nome_produto, '' as vgm_nome_modelo ";
				$sql .= "from tb_venda_games vg  ";
				$sql .= "inner join tb_pag_compras pg on pg.idvenda = vg.vg_id ";
				$sql .= "where vg.vg_id = " . $venda_id . " and vg.vg_ug_id=" . $usuarioId;
			}



//if($usuarioGames->b_IsLogin_Reinaldo()) {
//echo " sql: ".$sql."<br>";
//die("Stop");
//$msg_tmp = " TESTE 32234: \n  pagamento.pagto.deposito.em.saldo: ".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']."\n  pagamento.pagto.deposito.em.saldo.num.docto: ".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']."\n  ".$sql."\n";
//gravaLog_DebugTMP($msg_tmp);
//}

if($usuarioGames->b_IsLogin_Reinaldo()) {
//gravaLog_PagtoPINEPP("Em venda_e_modelos_logica.php 3\n".$sql."\nGLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']: ".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']."\nGLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']:".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']."\n");
}
echo "venda modelos: ".$sql;
			$rs_venda_modelos = SQLexecuteQuery($sql);
			if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) {
				$msg = "Nenhum produto encontrado. (4335A)\n";
	gravaLog_DRUPAL_TMP("Em venda_e_modelos_logica.php: {venda_id = '$venda_id', $msg} \n\t$sql\n");
			}
		}

		// Reset pagamento deposito
		$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo'] = "";
		$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto'] = "";
		unset($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']);
		unset($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']);

	} else {

		//Recupera modelos normal
		if($msg == ""){
			$sql  = "select * from tb_venda_games vg ";
			$sql .= "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id ";
			$sql .= "where vg.vg_id = " . $venda_id . " and vg.vg_ug_id=" . $usuarioId;

//echo " <!-- ".$sql." -->\n";

/*
if($usuarioGames->b_IsLogin_Reinaldo()) {
echo " sql: ".$sql."<br>";
echo " _SESSION['pagamento.pagto.deposito.em.saldo']: ".$_SESSION['pagamento.pagto.deposito.em.saldo']."<br>";
echo " _SESSION['pagamento.pagto.deposito.em.saldo.num.docto']: ".$_SESSION['pagamento.pagto.deposito.em.saldo.num.docto']."<br>";

die("Stop 43ss311");
}
*/
//$msg_tmp = " TESTE 32234_a: \n  pagamento.pagto.deposito.em.saldo: ".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']."\n  pagamento.pagto.deposito.em.saldo.num.docto: ".$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']."\n  ".$sql."\n";
//gravaLog_DebugTMP($msg_tmp);
echo "teste: ". $sql;
			$rs_venda_modelos = SQLexecuteQuery($sql);
			if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) $msg = "Nenhum produto encontrado (1rew).\n";
		}

	}

/*
if($usuarioGames->b_IsLogin_Reinaldo()) {
echo " sql: ".$sql."<br>";
echo " _SESSION['pagamento.pagto.deposito.em.saldo']: ".$_SESSION['pagamento.pagto.deposito.em.saldo']."<br>";
echo " _SESSION['pagamento.pagto.deposito.em.saldo.num.docto']: ".$_SESSION['pagamento.pagto.deposito.em.saldo.num.docto']."<br>";

die("Stop 43ss3");
}
*/
//gravaLog_DebugTMP("Quitting venda_e_modelos_logica.php [".(($msg)?"msg: $msg":"msg is empty")."]\n");

	//provocando erro para usuario Wagner
	/*if($usuarioId = 53916) {
		$msg = "****";
	}*/
	//Redireciona se ha algum dado invalido
	//----------------------------------------------------
	if($msg != ""){
		$strRedirect = "/game/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Comprovante") . "&link=" . urlencode("/game/conta/lista_vendas.php");
		if(!$var_origem_ajax_pin_pagamento) {
			redirect($strRedirect);
		}//end if(!$var_origem_ajax_pin_pagamento)
	}
	
?>
