<?php
//Gera um número aleatário para ser usado como OrderID ou "numorder".
//$orderId = 	date("YmdHis").str_pad(rand(0,99999999), 8, "0", STR_PAD_LEFT);

$orderId = get_newOrderID();
$GLOBALS['_SESSION']['pagamento.numorder'] = $orderId;
$iforma = ((isset($GLOBALS['_SESSION']['pagamento.pagto']))?$GLOBALS['_SESSION']['pagamento.pagto']:0);
$sbanco = getBcoCodigo($iforma);	

	
if ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']) {
	$valor_0 = $total_carrinho;
gravaLog_EPPCASH_PINs("Vai para mostraCarrinho_pag(false, 0)\n  valor_0 = '$valor_0'; total_carrinho: '$total_carrinho'\n  integracao_is_parceiro: ".$GLOBALS['_SESSION']['integracao_is_parceiro']."\n  integracao_origem_id: ".$GLOBALS['_SESSION']['integracao_origem_id']."\n  carrinho: ".print_r($GLOBALS['_SESSION']['carrinho'], true)."\n".str_repeat("-", 80)."\n");

	$taxas = 0;
	$amount = number_format($amount,2); // valor do pedido, que deve ter duas casas decimais no formato xx.xx.
	$item_name = "Venda de Créditos E-Prepag"; // texto para definir o item vendido
	$item_number = 0;	//$venda_id; // Número da venda no site E-prepag - Neste ponto ainda não está definido o venda_id
	
	$sql = "INSERT INTO tb_pag_compras (numcompra, cliente_nome, idcliente, tipo_cliente, frete, manuseio, taxas, subtotal, cesta, tipoPagto, prazo, numParcelas, valorParcela, total, dataInicio, status, iforma, banco) values ('".$orderId."', '".$cliente_nome_prev."', ".$id_usuario_prev.", '".$tipo_cliente."', 0, 0, ". $taxas .", 0, '', 0, 0, 0, 0, ".number_format((($valor_0+$taxas)*100), 0, ',', '').", '". date("Y-m-d H:i:s") ."', 0, '".$iforma."', '".$sbanco."')";
	//echo $sql;
	//die("STOP");
	gravaLog_EPPCASH_PINs("Inserindo o registro na tabela tb_pag_compras através do programa pag\\epp\\inc_config.php\n total_carrinho: ".(($total_carrinho==0) ? "É ZERO === " : "NÃO É ZERO +++ ")."\n$sql");
	$ret = SQLexecuteQuery($sql);
	if(!$ret) {
		echo "Erro ao inserir transação de pagamento.\n";
		die("Stop");
	}
}	

function gravaLog_EPPCASH($mensagem){
	
		//Arquivo
		$file =  RAIZ_DO_PROJETO . "log/log_EPP_CASH.txt";
	
		//Mensagem
		$mensagem =  str_repeat("-", 80)."\n".date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
	
}

?>