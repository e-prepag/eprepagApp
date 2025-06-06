<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
die("Stop PP");
	// Inicializa valores
	$formAction = "https://www.sandbox.paypal.com/cgi-bin/webscr"; // Define se vai ser utilizado o sandbox ou a producao do paypal
	$business   = "KT9SDXBS6QQ4G"; //codigo gerado pelo gerador de botoes do paypal em ref. ao estabelecimento. Lembrando que esse valor e para sandbox atualmente
	
	//Gera um número aleatório para ser usado como OrderID ou "numorder".
	//$orderId = 	date("YmdHis").str_pad(rand(0,99999999), 8, "0", STR_PAD_LEFT);
	$orderId = get_newOrderID();
	$_SESSION['pagamento.numorder'] = $orderId;
	echo $orderId;
	echo "<hr>";
	//echo "orderId: <font color='#FF0000'>".$orderId."</font><br>";

	$amount = number_format($amount,2); // valor do pedido, que deve ter duas casas decimais no formato xx.xx.
	$item_name = "Venda de Créditos E-Prepag"; // texto para definir o item vendido
	$item_number = $venda_id; // Número da venda no site E-prepag
	$retornosucesso = "" . EPREPAG_URL_HTTP . "/prepag2/pag/pay/sucesso.php"; // Página para retorno quando sucesso da transação
	$retornocancela = "" . EPREPAG_URL_HTTP . "/prepag2/pag/pay/cancel.html"; // Página para retorno quando a transação for cancelada
	$botao_paypal = "https://www.sandbox.paypal.com/pt_BR/i/btn/btn_buynowCC_LG.gif"; // Botão para submit para PayPal

/* 	echo "<hr>";
	echo "formAction: ".$formAction."<br>";
	echo "business: ".$business."<br>";
	echo "amount: ".$amount."<br>";
	echo "item_name: ".$item_name."<br>";
	echo "item_number: ".$item_number."<br>";
	echo "retornosucesso: ".$retornosucesso."<br>";
	echo "retornocancela: ".$retornocancela."<br>";
	echo "botao: ".$botao."<br>";
	echo "<hr>"; */

// Insert feito apenas em inc_gen_order_pay.php
//	$sql = "INSERT INTO tb_pag_compras (numcompra, cliente_nome, idcliente, tipo_cliente, frete, manuseio, taxas, subtotal, cesta, tipoPagto, prazo, numParcelas, valorParcela, total, dataInicio, status, iforma, banco) values ('".$orderId."', '-', 0, 'M', 0, 0, ". $taxas .", 0, '', 0, 0, 0, 0, ".number_format((($valor_0+$taxas)*100), 0, ',', '').", '". date("Y-m-d H:i:s") ."', 0, '".$iforma."', '".$sbanco."')";

   echo $sql;
   echo "<hr>";
   //die('paro paro ooo');
?>