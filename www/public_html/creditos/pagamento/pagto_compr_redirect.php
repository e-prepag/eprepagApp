<?php 
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "configIP.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";

$_PaginaOperador2Permitido = 54; 
validaSessao(); 
require_once DIR_INCS . "pdv/venda_e_modelos_logica.php";

	$rs_venda_row = pg_fetch_array($rs_venda);
	$pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
	$venda_status = $rs_venda_row['vg_ultimo_status'];

	//Verifica se venda cancelada
	if($msg == ""){
		if(	$venda_status == $STATUS_VENDA['VENDA_CANCELADA']){
			$msg = "Este pedido se encontra cancelado no momento.";		
			$strRedirect = "/creditos/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Pedido") . "&link=" . urlencode("/creditos/conta/lista_vendas.php");
		}
	}

//Recupera o usuario do session
//$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
//if($usuarioGames->b_IsLogin_reinaldolh()) {
//	echo "pagto_tipo: '$pagto_tipo'<br>";
//	die("Stop em pagto_compr_redirect.php<br>");
//}
	//Comprovantes
	if($pagto_tipo == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']){
		$strRedirect = "/creditos/conta/pagto_compr_dep_doc_transf.php";
				
	} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']){
		//$strRedirect = "/prepag2/dist_commerce/conta/pagto_compr_boleto.php";
            $strRedirect = "/creditos/carrinho/final.php";
	
	} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['REDECARD_MASTERCARD'] || $pagto_tipo == $FORMAS_PAGAMENTO['REDECARD_DINERS']){
		$strRedirect = "/creditos/redecard/rc_comprovante.php";
	
	} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['REPASSE']){
		$strRedirect = "/creditos/conta/pagto_compr_repasse.php";
	
	} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']){
		$strRedirect = "/creditos/conta/pagto_compr_online.php";
	
	} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']){
            die("OK");
		$strRedirect = "/cerditos/conta/pagto_compr_online.php";
	
	} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO']){
		$strRedirect = "/creditos/conta/pagto_compr_online.php";
	
	} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']){
		$strRedirect = "/creditos/conta/pagto_compr_online.php";
	
	} elseif ($pagto_tipo == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC){ // $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']
		$strRedirect = "/creditos/conta/pagto_compr_online.php";	

	} else {
		$strRedirect = "/creditos/conta/lista_vendas.php";
	}
                                    
        //Fechando Conexão
        pg_close($connid);
//die($strRedirect);
	//Redireciona
	redirect($strRedirect);

?>

