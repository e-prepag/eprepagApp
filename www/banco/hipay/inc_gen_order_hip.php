<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
	require_once( "C:/Sites/E-Prepag/www/web/incs/inc_register_globals.php");	

//Gera um número aleatório para ser usado como OrderID ou "numorder".
$orderId = get_newOrderID();
$_SESSION['pagamento.numorder'] = $orderId;

// valor do pedido, que deve ter duas casas decimais no formato xx.xx.
$amount = $total_carrinho;
$amount = number_format($amount,2); 

// texto para definir o item vendido
$item_name = "Venda de Creditos E-Prepag"; 

// Número da venda no site E-prepag
$item_number = $orderId; 

// Página para retorno quando sucesso da transação
$retornosucesso = ""; 

// Página para retorno quando a transação for cancelada
$retornocancela = "";

// Botão para submit para Hipay
$botao_hipay = "" . EPREPAG_URL_HTTP . "/prepag2/commerce/images/botao_hipay.gif"; 

$iforma = ((isset($_SESSION['pagamento.pagto']))?$_SESSION['pagamento.pagto']:0);	
$sbanco = (($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE']) ? 
			strval($FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE']):"???"); 
			
$taxas = 0;
if ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE'])  {
	$valor_0 = $total_carrinho;
        if($total_carrinho  < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA) {
        	$taxas = $PAGAMENTO_HIPAY_ONLINE_TAXA;
         } //end if($total_carrinho  < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
        else {
                $taxas = 0;
        }//end else do if($total_carrinho  < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)

	$sql = "INSERT INTO tb_pag_compras (numcompra, cliente_nome, idcliente, tipo_cliente, frete, manuseio, taxas, subtotal, cesta, tipoPagto, prazo, numParcelas, valorParcela, total, dataInicio, status, iforma, banco) values ('".$orderId."', '".$cliente_nome_prev."', ".$id_usuario_prev.", '".$tipo_cliente."', 0, 0, ". $taxas .", 0, '', 0, 0, 0, 0, ".number_format((($valor_0+$taxas)*100), 0, ',', '').", '". date("Y-m-d H:i:s") ."', 0, '".$iforma."', '".$sbanco."')";
	//echo "sql: <font color='#FF0000'>".$sql."</font><br>";
	$ret = SQLexecuteQuery($sql);

 	if(!$ret) {
		echo "Erro ao inserir transação de pagamento (32a).\n";
		die("Stop");
	} 	

     $form_hipay  = '';
     $form_hipay .= '<form action="../../pag/hpy/hipay_single_payment.php" target="_blank">';
	 $form_hipay .= '<input type="hidden" name="numcompra" id="numcompra" value="'.$_SESSION['pagamento.numorder'].'">';
	 $form_hipay .= '<input type="hidden" name="amount" id="amount" value="'.$amount.'">';
     $form_hipay .= '<input type="image" src="'.$botao_hipay.'" border="0" name="submit" title="PayPal">';
     $form_hipay .= '</form>';  
}			
?>