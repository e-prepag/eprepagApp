<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 			

// Lê dados do $_POST
$data_cielo = array(
                    'codigoBandeira'            => $GLOBALS['_POST']['codigoBandeira'],
                    'tipoParcelamento'          => (isset($GLOBALS['_POST']['tipoParcelamento'])?$GLOBALS['_POST']['tipoParcelamento']:''),
                    'formaPagamento'            => $GLOBALS['_POST']['formaPagamento'],
                    'capturarAutomaticamente'   => $GLOBALS['_POST']['capturarAutomaticamente'],
                    'indicadorAutorizacao'      => $GLOBALS['_POST']['indicadorAutorizacao'],
                    'campolivre'                => $GLOBALS['_POST']['campolivre'],
                    'numcompra'                 => $GLOBALS['_POST']['numcompra'],
                    'produto'                   => $GLOBALS['_POST']['produto'],
                    'dadosPedidoDescricao'      => $GLOBALS['_POST']['dadosPedidoDescricao'],
                    'softDescriptor'            => $GLOBALS['_POST']['softDescriptor'],
                    );

//produção
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
include_once DIR_INCS . "gamer/encryption.inc.php"; 
//include_once $raiz_do_projeto."/www/web/prepag2/commerce/drupal_inc.php"; 

require_once RAIZ_DO_PROJETO . "banco/cielo/include.php";
gravaLog_CIELO_TMP("POST: \n".print_r($GLOBALS['_POST'], true)."\n");

//gravaLog_DrupalOrdersRequestGamers("Cielo payment request from Drupal\n".print_r($_REQUEST, true)."\n");

$trx_data_cielo = $GLOBALS['_POST']['trx_data_cielo'];
if(strlen($trx_data_cielo)==0) {
    $trx_data_cielo = $GLOBALS['_GET']['trx_data_cielo'];
}
//echo "<!-- $trx_data_cielo -->\n";
//echo "<!-- ".strlen($trx_data_cielo)." -->\n";

// Is it a Drupal Request?
$b_is_drupal_request = false;

if(strlen($trx_data_cielo)>0) {
        $msg = "";
	// request from Drupal
	gravaLog_DrupalOrdersRequestGamers("Cielo payment request from Drupal");

	set_IntegracaoDrupal_marca_sessao_logout();

	// Prevents calling finaliza_venda_dr.php directly
	$_SESSION['allow_calling'] = 1;

	// Validate REFERRER (URL & IP)
	if(!$msg) {

	}

	// From $a_callback_data get carrinho, ug_email and iforma
	if(!$msg) {

		$encryption = new BO_Encryption();
//die("trx_data_cielo=".$trx_data_cielo);
		$callback = str_replace('SslashS', '/', $trx_data_cielo);
//die("callback_data=".$callback_data);
		$callback = str_replace('SplusS', '+', $callback);
//die("callback_data=".$callback_data);
	
		$callback_data = $encryption->decrypt($callback);
//echo "<!-- trx_data_cielo: $trx_data_cielo -->\n";
//echo "<!-- callback_data: $callback_data -->\n";
//die("Em manutenção tente novamente em pouco minutos. Obrigado.");
		$a_callback_data = unserialize($callback_data);
	//echo "<pre>".print_r($a_callback_data, true)."</pre><br>";
		$order_user_mail = $a_callback_data['order_user_mail'];
		$order_user_id = $a_callback_data['order_user_id'];

//echo "<!-- order_user_mail:$order_user_mail, order_user_id:$order_user_id -->\n";
//die("Em manutenção tente novamente em pouco minutos. Obrigado.");

		$slog_msg  = str_repeat("*", 80)."\n";
		$slog_msg .= "Drupal Request Cielo - receiving data\n";
	//	$slog_msg .= "  trx_data: $trx_data\n";
	//	$slog_msg .= "  a_callback_data: ".print_r($a_callback_data, true)."\n";
		gravaLog_DrupalOrdersRequestGamers($slog_msg);

		// Fazer login para o usuário ou cadastrar um novo usuário
		$idcliente = UsuarioGames::existeEmail_get_ID($order_user_mail);

//echo "<!-- $idcliente, $order_user_id -->\n";
                if($idcliente>0 && $idcliente==$order_user_id) {
                        // Faz login de usuário
			$bret = UsuarioGames::adicionarLoginSession_ByID($idcliente);
//echo "<!-- $bret -->\n";
			
			if($bret) {
				$b_is_drupal_request = true;

				$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
//echo "<!-- <pre>".print_r($usuarioGames,true)."</pre>  -->\n"; 
//die("Em manutenção tente novamente em pouco minutos. Obrigado.");
//sleep(2);
				// Atualiza último acesso
				//UsuarioGames::atualiza_ultimo_acesso($integracao_client_email);
			}

			// test first here
			//validaSessao();
	
			$slog_msg = "User logged in (order_user_mail: '$order_user_mail'; idcliente: $idcliente)\n";		//".print_r($usuarioGames, true)."\n";
			gravaLog_DrupalOrdersRequestGamers($slog_msg);
		}
	}
}

//die("Em manutenção tente novamente em pouco minutos. Obrigado.");
// If it was a Drupal request then it was already validated
validaSessao();

//$slog_msg = "SUCCESS - After validaSessao()";
//gravaLog_DrupalOrdersRequestGamers($slog_msg);

$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
$pagina_titulo = "Redirecionando para Cielo"; 

if($usuarioGames->b_IsLogin_pagamento_Cielo() && b_cielo_forma_pagamento()) {	
?>
<html>
	<head>
		<title>Pagamento <?php echo strtoupper($data_cielo['codigoBandeira']); ?></title>		
	</head>
	<body>
		Redirecionando...		
<?php

        gravaLog_CIELO_TMP("DATA CIELO DO POST: \n".print_r($data_cielo, true)."\n");

	$Pedido = new Pedido();
	
	$Pedido->formaPagamentoBandeira = $data_cielo['codigoBandeira']; 
	if($data_cielo['formaPagamento'] != "A" && $data_cielo['formaPagamento'] != "1")
	{
		$Pedido->formaPagamentoProduto = $data_cielo['tipoParcelamento'];
		$Pedido->formaPagamentoParcelas = $data_cielo['formaPagamento'];
	} 
	else 
	{
		$Pedido->formaPagamentoProduto = $data_cielo['formaPagamento'];
		$Pedido->formaPagamentoParcelas = 1;
	}
	
	$Pedido->dadosEcNumero = CIELO;
	$Pedido->dadosEcChave = CIELO_CHAVE;
	
	$Pedido->capturar = $data_cielo['capturarAutomaticamente'];	
	$Pedido->autorizar = $data_cielo['indicadorAutorizacao'];
	$Pedido->campolivre = $data_cielo['campolivre'];
	
	$Pedido->dadosPedidoNumero = $data_cielo['numcompra']; //rand(1000000, 9999999); 
	$Pedido->dadosPedidoValor = $data_cielo['produto'];

	$Pedido->dadosPedidoDescricao = $data_cielo['dadosPedidoDescricao'];	
	$Pedido->softDescriptor = $data_cielo['softDescriptor'];	

        if(strlen($trx_data_cielo)>0) {
            $Pedido->urlRetorno = ReturnURL()."?trx_data_cielo=".$trx_data_cielo;
        }
        else {
            $Pedido->urlRetorno = ReturnURL();
        }
        gravaLog_CIELO_TMP("Object: ".print_r($Pedido,true)."\n");

	// ENVIA REQUISIÇÃO SITE CIELO
	$objResposta = $Pedido->RequisicaoTransacao(false);

	$Pedido->tid = $objResposta->tid;
	$Pedido->pan = $objResposta->pan;
	$Pedido->status = $objResposta->status;
        $numero_ret = $objResposta->{'dados-pedido'}->numero;
        
        if(!empty($objResposta->tid) && !empty($numero_ret)) {
                $sql = "UPDATE tb_pag_compras SET cielo_tid='".$objResposta->tid."' where numcompra = '".$numero_ret."' and idcliente=".intval($usuarioGames->ug_id).";";
                $rs_compra = SQLexecuteQuery($sql);
                if(!$rs_compra) {
                        gravaLog_CIELO_TMP("Atualizando TID : Erro ao atualizar SQL => ".$sql.PHP_EOL);
                } //end if(!$rs_compra)
        }//end if(!empty($objResposta->tid) && !empty($numero_ret))
	
	$urlAutenticacao = "url-autenticacao";
	$Pedido->urlAutenticacao = $objResposta->$urlAutenticacao;
        gravaLog_CIELO_TMP("urlAutenticacao: ".$Pedido->urlAutenticacao."\n");
gravaLog_DrupalOrdersRequestGamers("Cielo payment request from Drupal - exiting \n\tPedido->urlAutenticacao: '".$Pedido->urlAutenticacao."'\n");

//echo "<br>OBJ_RESPOSTA<pre>".print_r($objResposta,true)."</pre>";
//echo "<br>SESSION<pre>".print_r($_SESSION,true)."</pre>";
//echo "PEDIDO<pre>".print_r($Pedido,true)."</pre>";
//die();

	// Serializa Pedido e guarda na SESSION
	$StrPedido = $Pedido->ToString();
        gravaLog_CIELO_TMP("StrPedido: ".$StrPedido."\n");
	$_SESSION["pedidos"]->append($StrPedido);

	if($b_is_drupal_request) {
		// Logout Drupal session that was loggedin at the beginning
		set_IntegracaoDrupal_marca_sessao_logout();
	}

        if(is_null($objResposta) || is_object($objResposta) == false || trim($objResposta->$urlAutenticacao == "")){
            echo '<p>Problema de comunicação com o sistema Cielo! Por favor, tente novamente mais tarde.</p>';
        } else{
            echo '<script type="text/javascript">
                window.location.href = "' . $Pedido->urlAutenticacao . '"
             </script>';
        }

?>
	</body>
</html>
<?php
}
?>
