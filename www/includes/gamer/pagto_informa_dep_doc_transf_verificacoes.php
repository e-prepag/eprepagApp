<?php

	//Verificacoes
	//----------------------------------------------------------------------------------------
	$msg = "";

	$rs_venda_row = pg_fetch_array($rs_venda);
	$venda_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
	$venda_status = $rs_venda_row['vg_ultimo_status'];
	
	//Verifica se eh dep_doc_transf
	if($msg == ""){
		if(	$venda_pagto_tipo != $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']){
			$msg = "Forma de Pagamento inválida.";		
		}
	}
	
	//Verifica se venda cancelada
	if($msg == ""){
		if(	$venda_status == $STATUS_VENDA['VENDA_CANCELADA']){
			$msg = "Esta venda se encontra cancelada no momento.";		
		}
	}

	//Verifica se status permite
	if($msg == ""){
		if(	$venda_status != $STATUS_VENDA['PEDIDO_EFETUADO']){
			$msg = "Informe do Pagamento desta venda já efetuado anteriormente.";		
		}
	}

	//Redireciona
	if($msg != ""){
		$strRedirect = "/game/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Informa Pagamento") . "&link=" . urlencode("/prepag2/commerce/conta/lista_vendas.php");
		redirect($strRedirect);
	}
	
?>
