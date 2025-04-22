<?php
// Inicializa valores
// Define se vai ser utilizado o sandbox ou a producao do paypal
$formAction = "https://www.sandbox.paypal.com/cgi-bin/webscr"; 

//codigo gerado pelo gerador de botoes do paypal em ref. ao estabelecimento. Lembrando que esse valor e para sandbox atualmente
$business   = "KT9SDXBS6QQ4G"; 

//Gera um número aleatório para ser usado como OrderID ou "numorder".
//$orderId = 	date("YmdHis").str_pad(rand(0,99999999), 8, "0", STR_PAD_LEFT);
$orderId = get_newOrderID();
$_SESSION['pagamento.numorder'] = $orderId;
echo "orderId: <font color='#FF0000'>".$orderId."</font><br>";

// valor do pedido, que deve ter duas casas decimais no formato xx.xx.
$amount = number_format($amount,2); 

// texto para definir o item vendido
$item_name = "Venda de Créditos E-Prepag"; 

// Número da venda no site E-prepag
$item_number = $venda_id; 

// Página para retorno quando sucesso da transação
$retornosucesso = "http://www.renebmjr_3.pessoal_2.ws/paypal/sucesso.php"; 

// Página para retorno quando a transação for cancelada
$retornocancela = "http://www.renebmjr_2.pessoal_3.ws/paypal/cancel.html"; 

// Botão para submit para PayPal
$botao = "https://www.sandbox.paypal.com/pt_BR/i/btn/btn_buynowCC_LG.gif"; 


$iforma = ((isset($_SESSION['pagamento.pagto']))?$_SESSION['pagamento.pagto']:0);	
$sbanco = (($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']) ? 
			strval($FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']):"???"); 
echo "iforma: <font color='#FF0000'>".$iforma."</font><br>";

if ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE'])  {
	$valor_0 = $total_carrinho;
	$taxas = (($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE'])? $PAGAMENTO_PAYPAL_ONLINE : 0 );

	if(empty($taxas)) {
		$taxas = 0;
	}
	
	echo "valor_0: <font color='#FF0000'>".$valor_0."</font><br>";
	echo "taxas: <font color='#FF0000'>".$taxas."</font><br>";
	
	

	$sql = "INSERT INTO tb_pag_compras (numcompra, cliente_nome, idcliente, tipo_cliente, frete, manuseio, taxas, subtotal, cesta, tipoPagto, prazo, numParcelas, valorParcela, total, dataInicio, status, iforma, banco) values ('".$orderId."', '-', 0, 'M', 0, 0, ". $taxas .", 0, '', 0, 0, 0, 0, ".number_format((($valor_0+$taxas)*100), 0, ',', '').", '". date("Y-m-d H:i:s") ."', 0, '".$iforma."', '".$sbanco."')";
	echo "sql: <font color='#FF0000'>".$sql."</font><br>";
	$ret = SQLexecuteQuery($sql);

 	if(!$ret) {
		echo "Erro ao inserir transação de pagamento (32a).\n";
		die("Stop");
	} 	

    $formulario  = '';
    $formulario .= '<form action="'.$formAction.'" target="_self">';
    $formulario .= '<input type="hidden" name="cmd" value="_xclick">';
    $formulario .= '<input type="hidden" name="business" value="'.$business.'">';
    $formulario .= '<input type="hidden" name="lc" value="US">';
    $formulario .= '<input type="hidden" name="item_name" value="'.$item_name.'">';
    $formulario .= '<input type="hidden" name="item_number" value="'.$item_number.'">';
    $formulario .= '<input type="hidden" name="amount" value="'.$amount.'">';
    $formulario .= '<input type="hidden" name="currency_code" value="BRL">';
    $formulario .= '<input type="hidden" name="button_subtype" value="services">';
    $formulario .= '<input type="hidden" name="no_note" value="1">';
    $formulario .= '<input type="hidden" name="no_shipping" value="1">';
	$formulario .= '<input type="hidden" name="image_url" value="http://dev.e-prepag.com.br/eprepag/imgs/logo_eprepag.gif">';
	$formulario .= '<input type="hidden" name="notify_url" value="http://www.renebmjr_2.pessoal_1.ws/paypal/ipn.php">';
    $formulario .= '<input type="hidden" name="rm" value="1">';
    $formulario .= '<input type="hidden" name="return" value="'.$retornosucesso.'">';
    $formulario .= '<input type="hidden" name="cancel_return" value="'.$retornocancela.'">';
    $formulario .= '<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest">';
	$formulario .= '<input type="hidden" name="cbt" value="Continue">';
    $formulario .= '<input type="image" src="'.$botao.'" border="0" name="submit" alt="PayPal!">';
    $formulario .= '<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">';
    $formulario .= '</form>';
    echo $formulario;
}

?>