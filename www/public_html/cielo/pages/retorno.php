<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 			

require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
include_once DIR_INCS . "gamer/encryption.inc.php"; 

$trx_data_cielo = $GLOBALS['_GET']['trx_data_cielo'];

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
		$callback = str_replace('SslashS', '/', $trx_data_cielo);
		$callback = str_replace('SplusS', '+', $callback);
		$callback_data = $encryption->decrypt($callback);
		$a_callback_data = unserialize($callback_data);
		$order_user_mail = $a_callback_data['order_user_mail'];
		$order_user_id = $a_callback_data['order_user_id'];

		$slog_msg  = str_repeat("*", 80)."\n";
		$slog_msg .= "Drupal Request Cielo - receiving data\n";
		gravaLog_DrupalOrdersRequestGamers($slog_msg);

		// Fazer login para o usuário ou cadastrar um novo usuário
		$idcliente = UsuarioGames::existeEmail_get_ID($order_user_mail);

                if($idcliente>0 && $idcliente==$order_user_id) {
                        // Faz login de usuário
			$bret = UsuarioGames::adicionarLoginSession_ByID($idcliente);
			if($bret) {
				$b_is_drupal_request = true;

				$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
			}

			$slog_msg = "User logged in (order_user_mail: '$order_user_mail'; idcliente: $idcliente)\n";		//".print_r($usuarioGames, true)."\n";
			gravaLog_DrupalOrdersRequestGamers($slog_msg);
		}
	}
}

validaSessao();
header("Content-Type: text/html; charset=ISO-8859-1",true);
$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
$pagina_titulo = "Redirecionando para Cielo"; 
//include ($raiz_do_projeto."/www/web/prepag2/commerce/includes/cabecalho.php"); 
	
//if($usuarioGames->getEmail()=="WAGNER@E-PREPAG.COM.BR") {	
if($usuarioGames->b_IsLogin_pagamento_Cielo() && b_cielo_forma_pagamento()) {

	//session_start();
	
	

	//Não vai funcionar esta captura de IP
	//Pq a aplicação esta redirecionando meu browser para a página de retorno
	//ou seja, sempre aparecerá o meu IP e não o da cielo

	require_once RAIZ_DO_PROJETO . "banco/cielo/include.php";
	
	// Resgata último pedido feito da SESSION
	$ultimoPedido = $_SESSION["pedidos"]->count();
gravaLog_TMP("ultimoPedido: $ultimoPedido\n");
	
	$ultimoPedido -= 1;
	
	$Pedido = new Pedido();
	$Pedido->FromString($_SESSION["pedidos"]->offsetGet($ultimoPedido));
	
	// Consulta situação da transação
	$objResposta = $Pedido->RequisicaoConsulta();
	
	//echo "PEDIDO<pre>".print_r($Pedido,true)."</pre>";
	//echo "Numero: ".$objResposta->{'dados-pedido'}->numero."<br>";
	$numero_ret = $objResposta->{'dados-pedido'}->numero;
	//echo "Valor: ".$objResposta->{'dados-pedido'}->valor."<br>";
	$valor_ret = $objResposta->{'dados-pedido'}->valor;
	//echo "PAN: ".$objResposta->pan."<br>";
	$pan_ret = $objResposta->pan;
	//echo "TID: ".$objResposta->tid."<br>";
	$tid_ret = $objResposta->tid;
	//echo "NSU: ".$objResposta->autorizacao->nsu."<br>";
	$nsu_ret = $objResposta->autorizacao->nsu;
	//echo "STATUS: ".$objResposta->status."<br>";
	$status_ret = $objResposta->status;
	$lr_ret = $objResposta->autorizacao->lr;
	$lr_msg_ret = utf8_decode($objResposta->autorizacao->mensagem);
	//echo "[".$objResposta->autorizacao->mensagem."]<br>";
gravaLog_TMP("ObjResposta: ".print_r($objResposta, true)."\n");

	
	$msg = "";
	if(!empty($numero_ret)) {
		$sql = "SELECT * from tb_pag_compras where numcompra = '".$numero_ret."' and idcliente=".intval($usuarioGames->ug_id);
		//echo $sql."<br>";
gravaLog_TMP("$sql\n");
		
		$rs_compra = SQLexecuteQuery($sql);
		if(!$rs_compra) {
			 $msg .= "<font color='#FF0000'><b>Erro ao selecionar a Compra (".$numero_ret.").\n</b></font><br>";
		}
		else {
			$rs_compra_row = pg_fetch_array($rs_compra);
			$cielo_tid	= $rs_compra_row['cielo_tid'];
			$pagto_tipo	= $rs_compra_row['iforma'];
			$id_venda	= $rs_compra_row['idvenda'];
			//capturando o logo da bandeira
			$iconeBandeira = "";
			switch ($pagto_tipo) {
				case $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO']:
					$iconeBandeira = "visa_logo.gif";
					break;
				case $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO']:
					$iconeBandeira = "visa_logo.gif";
					break;
				case $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_DEBITO']:
					$iconeBandeira = "mastercard_logo.gif";
					break;
				case $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO']:
					$iconeBandeira = "mastercard_logo.gif";
					break;
				case $FORMAS_PAGAMENTO['PAGAMENTO_ELO_DEBITO']:
					$iconeBandeira = "elo_logo.gif";
					break;
				case $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO']:
					$iconeBandeira = "elo_logo.gif";
					break;
				case $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO']:
					$iconeBandeira = "diners_logo.gif";
					break;
				case $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO']:
					$iconeBandeira = "discover_logo.gif";
					break;
			} 

			// Foi feito algum retorno para essa transação?
                        if(($status_ret == "4")||($status_ret == "6")) {
                                $sql = "UPDATE tb_pag_compras SET cielo_tid='".$tid_ret."',cielo_nsu='".$nsu_ret."',cielo_pan='".$pan_ret."', cielo_status=".$status_ret.",cielo_codigo_lr='".$lr_ret."' where numcompra = '".$numero_ret."' and idcliente=".intval($usuarioGames->ug_id);
                                //echo $sql."<br>";
gravaLog_TMP("$sql\n");
                                $rs_compra = SQLexecuteQuery($sql);
                                if(!$rs_compra) {
                                         $msg .= "<h4><font color='#FF0000'><b>Erro ao atualizar a Compra (".$numero_ret.").\n</b></font></h4><br>";
                                }
                                else {
                                        $msg .= "<h5>".$lr_msg_ret."</h5>
                                                        <div id='vbv_passo'>	
                                                        Aguardamos somente a confirma&ccedil;&atilde;o de pagamento.<br>
                                                        Em breve voc&ecirc; deve receber os cr&eacute;ditos adquiridos.<br>
                                                        </div>";
                                }
                        } else {
                                $sql = "UPDATE tb_pag_compras SET status=-1,status_processed=1,cielo_tid='".$tid_ret."',cielo_nsu='".$nsu_ret."',cielo_pan='".$pan_ret."', cielo_status=".$status_ret.",cielo_codigo_lr='".$lr_ret."' where numcompra = '".$numero_ret."' and idcliente=".intval($usuarioGames->ug_id);
                                //echo $sql."<br>";
gravaLog_TMP("$sql\n");
                                $rs_compra = SQLexecuteQuery($sql);
                                if(!$rs_compra) {
                                         $msg .= "<h4><font color='#FF0000'><b>Erro ao atualizar a Compra (".$numero_ret.").\n</b></font></h4><br>";
                                }
                                //definir esquema de mensagem de erro (status diferente de 4 e 6)
                                $msg .= "<h4><font color='#FF0000'><b>Erro no Pagamento.\n</b><br>".$lr_msg_ret."</font></h4><br>";
                        }
		}
	}

	// Atualiza Pedido da SESSION
	$StrPedido = $Pedido->ToString(); 
	$_SESSION["pedidos"]->offsetSet($ultimoPedido, $StrPedido);
//	echo $msg."<br>";
//echo $Pedido->getStatus();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Cielo E-commerce</title>
<link rel="stylesheet" href="../css/verifyed.css" type="text/css">
</head>
<body>
<div id="vbv_base">
	<div id="vbv_topo">
		<div id="vbv_tl"></div>
		<div id="vbv_t"></div>
		<div id="vbv_tr"></div>
	</div>
	<div id="vbv_centro">
		<div id="vbv_logos">
			<div id="vbv_logo_visa">
			
				<img src="../imgs/<?php echo $iconeBandeira;?>" alt="visa" />
			</div>
			<div id="vbv_logo_ec">				
				<img src="../imgs/logo_eprepag.gif" alt="E-Prepag" />
			</div>
		</div>
		<div class="vbv_delimitador"></div>
		<div class="vbv_resumo">
				<?php echo $msg;?>
				<h4>Pedido: <?php echo $id_venda;?></h4>
	            <div class="clear"></div>
				<img id="Retornar" tabindex="10"  src="../imgs/retornar.gif" alt="Retornar" style="cursor: pointer" onclick="javascript:window.close();">
		</div>
<div class="vbv_delimitador"></div>
<div class="vbv_resumo">
<?php 
/* comentario em função da solicitação do João em 6/11/2012
?>
		<br>
		<h4>Veja tamb&eacute;m a lista completa de cr&eacute;ditos para<br>games que a E-Prepag oferece:</h4>
        <div class="clear"></div>
		<a href="<?= EPREPAG_URL_HTTP ?>/eprepag/destaques.asp?id=16" target="_blank" border="0"><img id="VejaTodos" tabindex="10"  src="../imgs/vejatodos.gif" alt="Veja todos os jogos" title="Veja todos os jogos" style="border: none;"></a>
<?php
			<img id="VejaTodos" tabindex="10"  src="../imgs/vejatodos.gif" alt="Veja todos os jogos" title="Veja todos os jogos" style="cursor: pointer" onclick="javascript:window.location.href='" . EPREPAG_URL_HTTP . "/eprepag/destaques.asp?id=16';">	
*/
?>
</div>
<div class="spacer"></div>
<div class="vbv_delimitador"></div>
<div class="vbv_fields">
<br>
	<label for="block1">
	</label>
<br>
<br>
<br>
<br>
<center>
		Cr&eacute;ditos para mais de 1000 jogos.
</center>
</div>
		<div class="vbv_delimitador"></div>
	</div>
	<div id="vbv_fundo">
		<div id="vbv_codigo">
				br.com.cbmp.ecommerce3ds.entities.impl.TidImpl@1c3e66b4
		</div>
	</div>
</div>
</body>
</html>
<?php
}
?>