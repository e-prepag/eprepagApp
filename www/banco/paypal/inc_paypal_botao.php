<?php

$currency = $PAGAMENTO_PAYPAL_ONLINE_CURRENCY;
if($currency == 'Brazil' || empty($currency)) {
	$currencyValue  = 'BRL';
} else {
	$currencyValue = 'USD';
}
//codigo gerado pelo gerador de botoes do paypal em ref. ao estabelecimento. Lembrando que esse valor e para sandbox atualmente
//$business   = "KT9SDXBS6QQ4G";
$business   = "renebm_1291837586_biz@gmail.com";

// valor do pedido, que deve ter duas casas decimais no formato xx.xx.
$amount = $total_carrinho;
$amount = number_format($amount,2); 

// texto para definir o item vendido
$item_name = "Venda de Creditos E-Prepag"; 

// N�mero da venda no site E-prepag
$item_number = $orderId; 

// P�gina para retorno quando sucesso da transa��o
$retornosucesso = "http://www.e-prepag.com.br/prepag2/pag/pay/sucesso.php"; 

// P�gina para retorno quando a transa��o for cancelada
$retornocancela = "http://www.e-prepag.com.br/prepag2/pag/pay/cancel.html"; 

// Bot�o para submit para PayPal
//$botao = "https://www.sandbox.paypal.com/pt_BR/i/btn/btn_buynowCC_LG.gif"; 

$iforma = ((isset($_SESSION['pagamento.pagto']))?$_SESSION['pagamento.pagto']:0);	
$sbanco = (($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']) ? 
			strval($FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']):"???"); 
			
$taxas = 0;
if ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE'])  {
	$valor_0 = $total_carrinho;
	$taxas = $PAGAMENTO_PAYPAL_ONLINE_TAXA;

/*
	if(false) {
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
		$formulario_paypal .= '<input type="image" src="'.$botao_paypal.'" border="0" name="submit" title="Pague com PayPal">';
		$formulario_paypal .= '<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">';
		$formulario_paypal .= '</form>'; 
	}
*/
}
?>