<?php
	//Recupera usuario
	if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser'])){
		$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
		$usuarioId = $usuarioGames->getId();
	}
/*
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
*/
	//Recupera Id da venda
	if(!$venda_id_request_nome) $venda_id_request_nome = 'venda';
	$venda_id = $_REQUEST[$venda_id_request_nome];
	if(!$venda_id) $venda_id = $_SESSION['venda'];

	//Guarda id da venda no session
	$_SESSION['venda'] = $venda_id;
/*
echo "usuarioId: $usuarioId<br>";
echo "venda_id: $venda_id<br>";
die("Stop ascde");
*/
	//Validacoes
	$msg = "";	

	//Valida id da venda
	if($msg == ""){
		if(!$venda_id || !is_numeric($venda_id)){		
			$msg = "Id da venda inválido ou não fornecido ($venda_id).\n";
		}
	}
		
	//Recupera a venda
	if($msg == ""){
		$sql  = "select * from tb_dist_venda_games vg " .
				"where vg.vg_id = " . $venda_id . " and vg.vg_ug_id = " . $usuarioId;
		$rs_venda = SQLexecuteQuery($sql);
		if(!$rs_venda || pg_num_rows($rs_venda) == 0) $msg = "Nenhuma venda encontrada (A1).\n";

		$vg_pagto_tipo = -1;
		if($msg == ""){
			$rs_venda_row = pg_fetch_array($rs_venda);
			$vg_pagto_tipo		= $rs_venda_row['vg_pagto_tipo'];
			$pagto_tipo			= $rs_venda_row['vg_pagto_tipo'];
			$vg_ultimo_status	= $rs_venda_row['vg_ultimo_status'];
			// Deixa resetado para uso em pagto_compr_redirect.php
			pg_result_seek($rs_venda, 0);
		}
	}

//if(($usuarioGames->getNome()=="REINALDO PEREZ SANCHEZ") || ($usuarioGames->getNome()=="FABIO")){
//echo "vg_pagto_tipo: ".$vg_pagto_tipo."<br>";
//echo "b_IsPagtoOnline: ".(b_IsPagtoOnline($vg_pagto_tipo)?"YES":"NOPE")."<br>";
//}

	
	if(b_IsPagtoOnline($vg_pagto_tipo)) {
		//Recupera modelos
		if($msg == ""){
			$sql  = "select vg.*, 1 as vgm_qtde, bbg.bbg_valor as vgm_valor, 0 as vgm_perc_desconto, 'Crédito online LH Pré (R$' || to_char(bbg_valor-bbg_valor_taxa,'FM9999.00') || ')' as vgm_nome_produto, '' as vgm_nome_modelo from tb_dist_venda_games vg  ";
			$sql .= "inner join dist_boleto_bancario_games bbg on bbg.bbg_vg_id = vg.vg_id ";
			$sql .= "where vg.vg_id = " . $venda_id . " and vg.vg_ug_id=" . $usuarioId;
//echo "sql: ".$sql."<br>";

			$rs_venda_modelos = SQLexecuteQuery($sql);
			if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) $msg = "Nenhum produto encontrado. (4335A)\n";
		}
	} else {

		//Recupera modelos
		if($msg == ""){
			$sql  = "select * from tb_dist_venda_games vg " .
					"inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
					"where vg.vg_id = " . $venda_id . " and vg.vg_ug_id=" . $usuarioId;

			$rs_venda_modelos = SQLexecuteQuery($sql);
			if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) $msg = "Nenhum produto encontrado. (4335B)\n";
		}
	}
	if($msg != ""){
//		echo "_SESSION['dist_pagamento.pagto']: ".$_SESSION['dist_pagamento.pagto']."<br>";

//		echo "$sql<br>";
		die("Stop 544554 ($msg)");
	}

//die("Stop  ('".$msg."')???");
	
	//Redireciona se ha algum dado invalido
	//----------------------------------------------------
	if($msg != ""){
                if($GLOBALS['_SESSION']['drupal_order_id'] > 0) {
                    //Invalida a sessao
                    cancelarSessao();
                    die($GLOBALS['_SESSION']['drupal_render_css'].$msg);
                }//end if($GLOBALS['_SESSION']['drupal_order_id'] > 0)
                else {
                    $strRedirect = "/creditos/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Comprovante") . "&link=" . urlencode("/creditos/conta/lista_vendas.php");
                    redirect($strRedirect);
                }//end else do if($GLOBALS['_SESSION']['drupal_order_id'] > 0)
	}
	
?>
