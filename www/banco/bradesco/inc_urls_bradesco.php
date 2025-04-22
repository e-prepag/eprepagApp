<?php

//echo "\n<!-- carrinho: <pre>";
//print_r($_SESSION);
//echo "</pre>\n";
//echo " -->\n";	 

// https://mup.comercioeletronico.com.br/sepsManager/merchant.asp?merchantid=004552539
//$query = str_replace(Request.QueryString,"numOrder="&request("numorder"),"");
$Merchantid = "004552539";
$Manager = "adm_epr2539";
$Senha = "@DM_859674";	//	(antigo: 12345678)

//Dummy
//$smsg = "LOG Integração pagamentos Entering inc_urls_bradesco.php - ".date("Y-m-d H:i:s")."\n orderId1: $orderId, OrderId1: $OrderId, numOrder1: $numOrder\n";
//gravaLog_TMP($smsg);

	$nome_servidor = "mup.comercioeletronico.com.br";

    $link_PagtoFacil = "https://". $nome_servidor . "/sepsapplet/". $Merchantid . "/prepara_pagto.asp?merchantid=". $Merchantid . "&orderid=". $OrderId."&transId=getBoleto&";

/*	
	http://mup.comercioeletronico.com.br/paymethods/applet/model1/payment.asp?forma_pag=13418&merchantid=004552539&orderid=2009081316130744500732&transId=getBoleto&intCard=12&strTpPagamento=101&strPrazo=1&strQtdeParcelas=1&strVlrParcela=1300&strPurcheAmount=1300&strBuyFreq=0&strTpCartao=12&strPrazoExpire=.


*/

//        $link_Boleto = "http://". $nome_servidor . "/sepsBoleto/". $Merchantid . "/prepara_pagto.asp?merchantid=". $Merchantid . "&orderid=". $OrderId."&";
//     $link_BoletoRet = "http://". $nome_servidor . "/sepsBoletoRet/". $Merchantid . "/prepara_pagto.asp?merchantid=". $Merchantid . "&orderid=". $OrderId."&";
 $link_Transferencia = "https://". $nome_servidor . "/sepsTransfer/". $Merchantid . "/prepara_pagto.asp?merchantid=". $Merchantid . "&orderid=". $OrderId."&transId=getTransfer&";
// $link_Financiamento = "http://". $nome_servidor . "/sepsFinanciamento/". $Merchantid . "/prepara_pagto.asp?merchantid=". $Merchantid . "&orderid=". $OrderId."&";
//$link_NE_SPSEmpresas = "http://". $nome_servidor . "/sepsSPSEmpresas/". $Merchantid . "/prepara_pagto.asp?merchantid=". $Merchantid . "&orderid=". $OrderId."&";

        $link_joker = "http://www.e-prepag.com.br/prepag2/pag/brd/joker.php?merchantid=". $Merchantid . "&orderid=". $OrderId."&";

        $link_error = "http://www.e-prepag.com.br/prepag2/pag/brd/joker.php?merchantid=". $Merchantid . "&orderid=". $OrderId."&";

        $link_debug = "https://mup.comercioeletronico.com.br/paymethods/boleto/model5dbg/prepara_pagto.asp?merchantid=". $Merchantid . "&orderid=". $OrderId."&";

        $link_confirma = "http://www.e-prepag.com.br/prepag2/pag/brd/confirmaBradesco.php?numOrder=". $OrderId."&";
//echo "<!-- link_Transferencia: ".$link_Transferencia." -->";

//        $link_ajax_status = "http://www.e-prepag.com.br/prepag2/commerce/ajax_info_pagamento.php";

	// Links para arquivos de retorno Bradesco
	$data_retorno = date('Y/m/d', strtotime("-5 days"));	
	$data_retorno2 = date('Y/m/d', strtotime("-1 days"));
	//echo "data_retorno: ".$data_retorno."<br>";
	//echo "data_retorno2: ".$data_retorno2."<br>";
	//		Transferência grupo
    $link_ArquivoRetornoTransf = "https://". $nome_servidor . "/sepsmanager/ArqRetBradescoTransfer_TXT.asp";
	$link_ArquivoRetornoTransf_POST = "merchantid=".$Merchantid."&data=".$data_retorno."&Manager=". $Manager."&passwd=".$Senha."&";
	//		Transferência transação
    $link_ArquivoRetornoTransf2 = "https://". $nome_servidor . "/sepsmanager/ArqRetBradescoTransfer_TXT2.asp";
	$link_ArquivoRetornoTransf2_POST = "merchantid=".$Merchantid."&data=".$data_retorno."&Manager=". $Manager."&passwd=".$Senha."&NumOrder=";

	//		PagtoFacil grupo
    $link_ArquivoRetornoPagtoFacil = "https://". $nome_servidor . "/sepsmanager/ArqRetBradescoTXT.asp";
	$link_ArquivoRetornoPagtoFacil_POST = "merchantid=".$Merchantid."&data=".$data_retorno."&Manager=". $Manager."&passwd=".$Senha."&";
	//		PagtoFacil transação
    $link_ArquivoRetornoPagtoFacil2 = "https://". $nome_servidor . "/sepsmanager/ArqRetBradescoTXT2.asp";
	$link_ArquivoRetornoPagtoFacil2_POST = "merchantid=".$Merchantid."&data=".$data_retorno."&Manager=". $Manager."&passwd=".$Senha."&NumOrder=";
//echo $link_ArquivoRetornoPagtoFacil2."<br>";
//echo $link_ArquivoRetornoPagtoFacil2_POST."<br>";



?> 
