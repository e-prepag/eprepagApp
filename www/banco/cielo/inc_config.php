<?php
$orderId = get_newOrderID();
$_SESSION['pagamento.numorder'] = $orderId;
$iforma		= ((isset($_SESSION['pagamento.pagto']))	?$_SESSION['pagamento.pagto']	:0);

// Naum deveria ser colocado objeto da classe limite neste programa pra verificar os limites, ou й melhor deichar dentro do finaliza_venda.php como estб comentado?

if (b_IsPagtoCieloAlpha($iforma)) {
	/*
	switch ($iforma) {
		case $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO']:
			$item_name = "VISA Dйbito"; // texto para definir o item vendido
			break;
		case $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO']:
			$item_name = "VISA Crйdito"; // texto para definir o item vendido
			break;
		case $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_DEBITO']:
			$item_name = "MASTER Dйbito"; // texto para definir o item vendido
			break;
		case $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO']:
			$item_name = "MASTER Crйdito"; // texto para definir o item vendido
			break;
		case $FORMAS_PAGAMENTO['PAGAMENTO_ELO_DEBITO']:
			$item_name = "ELO Dйbito"; // texto para definir o item vendido
			break;
		case $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO']:
			$item_name = "ELO Crйdito"; // texto para definir o item vendido
			break;
		case $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO']:
			$item_name = "DINERS Crйdito"; // texto para definir o item vendido
			break;
		case $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO']:
			$item_name = "DISCOVER Crйdito"; // texto para definir o item vendido
			break;
	}
	*/
	if(!isset($amount) || (!$amount)) {
		$amount = $total_carrinho+$taxas;
	}
	if(!isset($venda_id)) {
		$venda_id = 0;
	}

	//$valor_0 = $total_carrinho;
	$sbanco		= getBcoCodigo($iforma);
	$pagto_venda	= getCodigoNumericoParaPagto($iforma);
        if($total_carrinho  < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA) {
            $taxas	= getTaxaPagtoOnline($iforma); 
        }
        else {
           $taxas = 0; 
        }
	$amount			= number_format($amount,2); // valor do pedido, que deve ter duas casas decimais no formato xx.xx.
	$item_number	= $venda_id; // Nъmero da venda no site E-prepag
	$sql = "INSERT INTO tb_pag_compras (numcompra, cliente_nome, idcliente, tipo_cliente, frete, manuseio, taxas, subtotal, cesta, tipoPagto, prazo, numParcelas, valorParcela, total, dataInicio, status, iforma, banco) values ('".$orderId."', '".$cliente_nome_prev."', ".$id_usuario_prev.", '".$tipo_cliente."', 0, 0, ". $taxas .", 0, '', 0, 0, 0, 0, ".number_format((($total_carrinho+$taxas)*100), 0, ',', '').", '". date("Y-m-d H:i:s") ."', 0, '".$iforma."', '".$sbanco."')";
//	echo $sql;
//	die("<br>STOP");
	$ret = SQLexecuteQuery($sql);
	if(!$ret) {
		echo "Erro ao inserir transaзгo de pagamento.\n";
		die("Stop");
	}

	//Gerando Token
	$softDescriptor = str_pad(rand(1,999999), 6, "0", STR_PAD_LEFT);
	$_SESSION['pagamento.token'] = $softDescriptor;

}

?>