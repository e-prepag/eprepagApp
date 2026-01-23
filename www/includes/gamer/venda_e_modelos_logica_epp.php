<?php
//Recupera usuario
if (isset($GLOBALS['_SESSION']['usuarioGames_ser']) && !is_null($GLOBALS['_SESSION']['usuarioGames_ser'])) {

	$usuarioGames = unserialize(
		$GLOBALS['_SESSION']['usuarioGames_ser']
	);

	if (!$usuarioGames) {
		die("Erro crítico ao carregar a sessão do usuário.");
	}
	$usuarioId = $usuarioGames->getId();
}

//Recupera Id da venda
$venda_id_request_nome = $_REQUEST['venda_id_request_nome'] ?? null;

if (!$venda_id_request_nome) $venda_id_request_nome = 'venda';
$venda_id = $GLOBALS['_REQUEST'][$venda_id_request_nome];
if (!$venda_id) $venda_id = $GLOBALS['_SESSION']['venda'];

//Guarda id da venda no session
$GLOBALS['_SESSION']['venda'] = $venda_id;

//Validacoes
$msg = "";

//Valida id da venda
if ($msg == "") {
	if (!$venda_id || !is_numeric($venda_id)) {
		$msg = "Id da venda inválido ou não fornecido.\n";
	}
}

//Recupera a venda
if ($msg == "") {
	$sql  = "SELECT * FROM tb_venda_games vg WHERE vg.vg_id = $1 AND vg.vg_ug_id = $2";
	$params = [$venda_id, $usuarioId];

	$rs_venda = SQLexecuteQueryParams($sql, $params);
	if (!$rs_venda || pg_num_rows($rs_venda) == 0) $msg = "Nenhuma venda encontrada.\n";
}

if (((strlen($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']) > 0) && (strlen($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']) > 0))) {

	//Recupera modelos para deposito em saldo
	if ($msg == "") {

		$params = [$venda_id, $usuarioId];

		if ($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo'] == '2') {
			$sql  = "SELECT vg.*, 1 as vgm_qtde, bbg.bbg_valor as vgm_valor, 0 as vgm_perc_desconto, 'Crédito online EPP Cash (R\$' || to_char(bbg_valor-bbg_valor_taxa,'FM9999.00') || ')' as vgm_nome_produto, '' as vgm_nome_modelo ";
			$sql .= "FROM tb_venda_games vg  ";
			$sql .= "INNER JOIN boleto_bancario_games bbg ON bbg.bbg_vg_id = vg.vg_id ";
			$sql .= "WHERE vg.vg_id = $1 AND vg.vg_ug_id = $2"; // Placeholders
		} else {
			$sql  = "SELECT vg.*, 1 as vgm_qtde, (total/100-taxas) as vgm_valor, 0 as vgm_perc_desconto, 'Crédito online EPP Cash (R\$' || to_char((total/100-taxas),'FM9999.00') || ')' as vgm_nome_produto, '' as vgm_nome_modelo ";
			$sql .= "FROM tb_venda_games vg  ";
			$sql .= "INNER JOIN tb_pag_compras pg ON pg.idvenda = vg.vg_id ";
			$sql .= "WHERE vg.vg_id = $1 AND vg.vg_ug_id = $2"; // Placeholders
		}

		echo "venda modelos: " . $sql; // Mantido, pois parecia ser um debug intencional

		$rs_venda_modelos = SQLexecuteQueryParams($sql, $params);

		if (!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) {
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
	if ($msg == "") {
		$sql  = "SELECT * FROM tb_venda_games vg ";
		$sql .= "INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id ";
		$sql .= "WHERE vg.vg_id = $1 AND vg.vg_ug_id = $2"; // Placeholders
		$params = [$venda_id, $usuarioId];

		$rs_venda_modelos = SQLexecuteQueryParams($sql, $params);
		if (!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) $msg = "Nenhum produto encontrado (1rew).\n";
	}
}

//Redireciona se ha algum dado invalido
//----------------------------------------------------
if ($msg != "") {
	$strRedirect = "/game/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Comprovante") . "&link=" . urlencode("/game/conta/lista_vendas.php");
	if (!$var_origem_ajax_pin_pagamento) {
		redirect($strRedirect);
	} //end if(!$var_origem_ajax_pin_pagamento)
}
