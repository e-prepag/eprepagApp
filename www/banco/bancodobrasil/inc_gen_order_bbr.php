<?php 

// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

//  Gera um número aleatório para ser usado como OrderID ou "numorder".
//$orderId = 	date("YmdHis").str_pad(rand(0,99999999), 8, "0", STR_PAD_LEFT);
$orderId = get_newOrderID();
//echo "orderId: <font color='#FF0000'>".$orderId."</font><br>";
$GLOBALS['_SESSION']['pagamento.numorder'] = $orderId;
/*
if($usuarioGames->b_IsLogin_Reinaldo()) {
echo "iforma1: ".$iforma."<br>";
echo "_SESSION['pagamento.pagto']: ".$GLOBALS['_SESSION']['pagamento.pagto']."<br>";
}
*/
if($tipo_cliente=="M") {
	if(!$iforma) {
		$iforma = (($GLOBALS['_SESSION']['pagamento.pagto'])?$GLOBALS['_SESSION']['pagamento.pagto']:0);
	}
        if($total_carrinho  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) {
            $taxas = (($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA'])? $GLOBALS['BANCO_DO_BRASIL_TAXA_DE_SERVICO'] : 0 );
                } //end if($total_carrinho  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
        else {
            $taxas = 0;
        }//end else do if($total_carrinho  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])

} elseif($tipo_cliente=="LR") {
	$iforma = ((isset($GLOBALS['_SESSION']['dist_pagamento.pagto']))?$GLOBALS['_SESSION']['dist_pagamento.pagto']:0);
	// $taxas já foi calculado em finaliza_vendaExLH_pgtoonline.php
	$taxas = ((isset($GLOBALS['_SESSION']['dist_pagamento.taxa']))?$GLOBALS['_SESSION']['dist_pagamento.taxa']:0);
}

$sbanco = (($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) ? 
			strval($GLOBALS['BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO']):"???"); 

//echo "iforma: ".$iforma."<br>";
if ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA'])  {

	$valor_0 = $total_carrinho;

	//		  cliente_nome	 character varying(100),  --    LH - ug_nome_fantasia/ug_razao_social, M - ug_nome, E - tb_venda_games.vg_ex_email
	//		  tipo_cliente   character varying(2),	-- 'M' - Money, 'E' - Money Express, 'LR' - Lanhouse Pré, 'LO' - Lanhouse Pos, 
	$sql = "INSERT INTO tb_pag_compras (numcompra, cliente_nome, idcliente, tipo_cliente, frete, manuseio, taxas, subtotal, cesta, tipoPagto, prazo, numParcelas, valorParcela, total, dataInicio, status, iforma, banco) values ('".$orderId."', '".$cliente_nome_prev."', ".$id_usuario_prev.", '".$tipo_cliente."', 0, 0, ". $taxas .", 0, '', 0, 0, 0, 0, ".number_format((($valor_0+$taxas)*100), 0, ',', '').", '". date("Y-m-d H:i:s") ."', 0, '".$iforma."', '".$sbanco."')";
	
//	gravaLog_Pagto_Insert($sql."\n");

/*
if($usuarioGames->b_IsLogin_Reinaldo()) {
	echo "<br>".$sql."<br>";
	$GLOBALS['_SESSION']['sql_pagto_online_insert'] = $sql;
}
*/

	//$rsCompra = $conn->Execute($sql) or die("Erro 22");
	$ret = SQLexecuteQuery($sql);
	if(!$ret) {
		echo "Erro ao inserir transação de pagamento (32a).\n";
		die("Stop");
	}

//	echo "OK1<br>";
//	die("Stop");
} else {
	$GLOBALS['_SESSION']['sql_pagto_online_insert'] = "ERROR: No INSERT in Banco do Brasil('".$iforma."' <> ".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']."')";
}

?>
