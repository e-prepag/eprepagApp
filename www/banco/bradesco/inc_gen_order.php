<?php 

$b_homologacao = true;

$descr = array();
$descr[0] = "item:diskette 3 1/4 Sony".chr(10)."1".chr(10)."cx".chr(10)."500".chr(10);
$descr[1] = "item:lapiseira Pentel 0.5 preta".chr(10)."1".chr(10)."pc".chr(10)."1300".chr(10);
$descr[2] = "item:borracha Tk-Plast".chr(10)."1".chr(10)."pc".chr(10)."200".chr(10);
$descr[3] = "item:Regua 30 cm Trident".chr(10)."1".chr(10)."pc".chr(10)."100".chr(10);
$descr[4] = "item:pilha 1.5 V AA raiovac".chr(10)."4".chr(10)."pc".chr(10)."500".chr(10);
$descr[5] = "item:Produto de teste".chr(10)."1".chr(10)."pc".chr(10)."100".chr(10);

$descr_mark = array(6);
$descr_mark[0] = 0;
$descr_mark[1] = 0;
$descr_mark[2] = 0;
$descr_mark[3] = 0;
$descr_mark[4] = 0;
$descr_mark[5] = 0;

//  Gera um número aleatório para ser usado como OrderID ou "numorder".
//$OrderId = 	date("YmdHis").str_pad(rand(0,99999999), 8, "0", STR_PAD_LEFT);
$OrderId = get_newOrderID();
//echo "orderId: <font color='#FF0000'>".$OrderId."</font><br>";
$GLOBALS['_SESSION']['pagamento.numorder'] = $OrderId;
$orderId = $OrderId;

// Criando cesta
$cesta = "";
$cesta_ind = "";
$nmax = rand(3,5);
$n = 0;
while($n<$nmax) {
	$i = rand(0,4);
	if($descr_mark[$i]==0) {
		$cesta .= $descr[$i];
		$cesta_ind .= "[".$i."]";
		$descr_mark[$i] = 1;
		$n++;
	}
}

//	if($iforma==0) {
		$taxas = 0;
		if($tipo_cliente=="M") {
			$iforma = ((isset($GLOBALS['_SESSION']['pagamento.pagto']))?$GLOBALS['_SESSION']['pagamento.pagto']:0);
                        if($total_carrinho  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) {
                            $taxas = (($iforma==$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'])? $GLOBALS['BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL']: 0);
                        } //end if($total_carrinho  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                        else {
                            $taxas = 0;
                        }//end else do if($total_carrinho  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
		} elseif($tipo_cliente=="LR") {
			$iforma = ((isset($GLOBALS['_SESSION']['dist_pagamento.pagto']))?$GLOBALS['_SESSION']['dist_pagamento.pagto']:0);
			// $taxas já foi calculado em finaliza_vendaExLH_pgtoonline.php
			$taxas = ((isset($GLOBALS['_SESSION']['dist_pagamento.taxa']))?$GLOBALS['_SESSION']['dist_pagamento.taxa']:0);
		}
//	}
	$sbanco = ((($iforma==$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO'])) ? 
				strval($GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO']):"???"); 

	if (($iforma==$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO'])) {


		$cesta_brd = montaCesta_pag();

//Dummy
$smsg = "LOG Integração pagamentos BRD - ".date("Y-m-d H:i:s")."\n  total_carrinho: $total_carrinho\n  cesta_brd: '$cesta_brd'\n  OrderId: '$OrderId'\n";
gravaLog_TMP($smsg);

		//		  cliente_nome	 character varying(100),  --    LH - ug_nome_fantasia/ug_razao_social, M - ug_nome, E - tb_venda_games.vg_ex_email
		//		  tipo_cliente   character varying(2),	-- 'M' - Money, 'E' - Money Express, 'LR' - Lanhouse Pré, 'LO' - Lanhouse Pos, 
		$sql = "INSERT INTO tb_pag_compras (numcompra, cliente_nome, idcliente, tipo_cliente, frete, manuseio, taxas, subtotal, cesta, tipoPagto, prazo, numParcelas, valorParcela, total, dataInicio, status, iforma, banco) values ('".$OrderId."', '".$cliente_nome_prev."', ".$id_usuario_prev.", '".$tipo_cliente."', 0, 0, ".$taxas.", 0, '".$cesta_brd."', 0, 0, 0, 0, ".(100*($total_carrinho+$taxas)).", '". date("Y-m-d H:i:s") ."', 0, '".$iforma."', ".$sbanco.")";

//		gravaLog_Pagto_Insert($sql."\n");

	//	echo "<br>".$sql."<br>";
//		$GLOBALS['_SESSION']['sql_pagto_online_insert'] = $sql;

		//$rsCompra = $conn->Execute($sql) or die("Erro 22");
		$ret = SQLexecuteQuery($sql);
		if(!$ret) {
			echo "Erro ao inserir transação de pagamento.\n";
			die("Stop");
		}
//Dummy
//$smsg = "LOG Integração pagamentos BRD2 - ".date("Y-m-d H:i:s")."\n  $sql\n";
//gravaLog_TMP($smsg);

		//echo "OK1<br>";
	} else {
		$GLOBALS['_SESSION']['sql_pagto_online_insert'] = "Nope (iforma: ".$iforma.", tipo_cliente: '".$tipo_cliente."')";
	}

?>
