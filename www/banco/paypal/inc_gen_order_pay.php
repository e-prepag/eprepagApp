<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php

	require_once( "C:/Sites/E-Prepag/www/web/incs/inc_register_globals.php");	

$currency = $PAGAMENTO_PAYPAL_ONLINE_CURRENCY;
if($currency == 'Brazil' || empty($currency)) {
	$currencyValue  = 'BRL';
} else {
	$currencyValue = 'USD';
}
//codigo gerado pelo gerador de botoes do paypal em ref. ao estabelecimento. Lembrando que esse valor e para sandbox atualmente
//$business   = "KT9SDXBS6QQ4G";
$business   = "renebm_1291837586_biz@gmail.com";

//Gera um número aleatório para ser usado como OrderID ou "numorder".
$orderId = get_newOrderID();
$_SESSION['pagamento.numorder'] = $orderId;

// valor do pedido, que deve ter duas casas decimais no formato xx.xx.
//$amount = $total_geral;
//$amount = number_format($amount,2); 

// texto para definir o item vendido
$item_name = "Venda de Creditos E-Prepag"; 

// Número da venda no site E-prepag
$item_number = $orderId; 

// Página para retorno quando sucesso da transação
$retornosucesso = "" . EPREPAG_URL_HTTP . "/prepag2/pag/pay/sucesso.php"; 

// Página para retorno quando a transação for cancelada
$retornocancela = "" . EPREPAG_URL_HTTP . "/prepag2/pag/pay/cancel.html"; 

// Botão para submit para PayPal
$botao = "https://www.sandbox.paypal.com/pt_BR/i/btn/btn_buynowCC_LG.gif"; 

$iforma = ((isset($_SESSION['pagamento.pagto']))?$_SESSION['pagamento.pagto']:0);	
$sbanco = (($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']) ? 
			strval($FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']):"???"); 

$taxas = 0;
if ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE'])  {
	$valor_0 = $total_carrinho;
        if($total_carrinho  < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA) {
            $taxas = $PAGAMENTO_PAYPAL_ONLINE_TAXA;
         } //end if($total_carrinho  < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
        else {
            $taxas = 0;
        }//end else do if($total_carrinho  < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)

	$sql = "INSERT INTO tb_pag_compras (numcompra, cliente_nome, idcliente, tipo_cliente, frete, manuseio, taxas, subtotal, cesta, tipoPagto, prazo, numParcelas, valorParcela, total, dataInicio, status, iforma, banco) values ('".$orderId."', '".$cliente_nome_prev."', ".$id_usuario_prev.", '".$tipo_cliente."', 0, 0, ". $taxas .", 0, '', 0, 0, 0, 0, ".number_format((($valor_0+$taxas)*100), 0, ',', '').", '". date("Y-m-d H:i:s") ."', 0, '".$iforma."', '".$sbanco."')";
	//echo "sql: <font color='#FF0000'>".$sql."</font><br>";
	$ret = SQLexecuteQuery($sql);

 	if(!$ret) {
		echo "Erro ao inserir transação de pagamento (32a).\n";
		die("Stop2132");
	} 	
/*
    $formulario_paypal  = '';
    $formulario_paypal .= '<form action="../../pag/pay/paypal_process.php" target="_blank">';
    $formulario_paypal .= '<input type="hidden" name="cmd" value="_xclick">';
    $formulario_paypal .= '<input type="hidden" name="business" value="'.$business.'">';
    $formulario_paypal .= '<input type="hidden" name="item_name" value="'.$item_name.'">';
    $formulario_paypal .= '<input type="hidden" name="item_number" value="'.$item_number.'">';
	$formulario_paypal .= '<input type="hidden" name="INVNUM" value="'.$item_number.'">';
	$formulario_paypal .= '<input type="hidden" name="invoice" value="'.$item_number.'">';	
    $formulario_paypal .= '<input type="hidden" name="amount" value="'.$amount.'">';
	$formulario_paypal .= '<input type="hidden" name="mc_gross" value="'.$amount.'">';
	$formulario_paypal .= '<input type="hidden" name="tax" value="'.$taxas.'">';
	$formulario_paypal .= '<input type="hidden" name="quantity" value="1">';
    $formulario_paypal .= '<input type="hidden" name="currency_code" value="'.$currencyValue.'">';
    $formulario_paypal .= '<input type="hidden" name="button_subtype" value="services">';
    $formulario_paypal .= '<input type="hidden" name="no_note" value="1">';
    $formulario_paypal .= '<input type="hidden" name="no_shipping" value="1">';
    $formulario_paypal .= '<input type="hidden" name="rm" value="1">';
    $formulario_paypal .= '<input type="hidden" name="return" value="'.$retornosucesso.'">';
    $formulario_paypal .= '<input type="hidden" name="cancel_return" value="'.$retornocancela.'">';
    $formulario_paypal .= '<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest">';
	$formulario_paypal .= '<input type="hidden" name="cbt" value="Continue">';
    $formulario_paypal .= '<input type="image" src="'.$botao.'" border="0" name="submit" alt="PayPal!">';
    $formulario_paypal .= '<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">';
    $formulario_paypal .= '</form>'; 
*/
}			






?>