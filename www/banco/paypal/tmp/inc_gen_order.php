<?php
function order($formAction, $business, $amount, $item_name, $item_number, $retornosucesso, $retornocancela, $botao) {
    $executa  = '';
    $executa .= '<form action="'.$formAction.'" target="_self">';
    $executa .= '<input type="hidden" name="cmd" value="_xclick">';
    $executa .= '<input type="hidden" name="business" value="'.$business.'">';
    $executa .= '<input type="hidden" name="lc" value="US">';
    $executa .= '<input type="hidden" name="item_name" value="'.$item_name.'">';
    $executa .= '<input type="hidden" name="item_number" value="'.$item_number.'">';
    $executa .= '<input type="hidden" name="amount" value="'.$amount.'">';
    $executa .= '<input type="hidden" name="currency_code" value="BRL">';
    $executa .= '<input type="hidden" name="button_subtype" value="services">';
    $executa .= '<input type="hidden" name="no_note" value="1">';
    $executa .= '<input type="hidden" name="no_shipping" value="1">';
	$executa .= '<input type="hidden" name="image_url" value="http://dev.e-prepag.com.br/eprepag/imgs/logo_eprepag.gif">';
	$executa .= '<input type="hidden" name="notify_url" value="http://www.renebmjr_2.pessoal_2.ws/paypal/ipn.php">';
    $executa .= '<input type="hidden" name="rm" value="1">';
    $executa .= '<input type="hidden" name="return" value="'.$retornosucesso.'">';
    $executa .= '<input type="hidden" name="cancel_return" value="'.$retornocancela.'">';
    $executa .= '<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest">';
	$executa .= '<input type="hidden" name="cbt" value="Continue">';
    $executa .= '<input type="image" src="'.$botao.'" border="0" name="submit" alt="PayPal!">';
    $executa .= '<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">';
    $executa .= '</form>';
    
    return $executa;
}
?>