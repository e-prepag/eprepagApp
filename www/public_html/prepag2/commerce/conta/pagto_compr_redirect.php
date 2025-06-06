<?php require_once __DIR__ . '/../../../../includes/constantes_url.php'; ?>
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<link href="/css/creditos.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/modalwaitingfor.js"></script>
<script>
    $(function(){
        waitingDialog.show('Por favor aguarde, estamos validando seus dados...',{dialogSize: 'md'});
    });
</script>
<?php 
require_once "../../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
validaSessao(); 

require_once DIR_INCS . "gamer/venda_e_modelos_logica_epp.php";

$rs_venda_row = pg_fetch_array($rs_venda);
$pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
$venda_status = $rs_venda_row['vg_ultimo_status'];

//Verifica se venda cancelada
if($msg == ""){
        if(	$venda_status == $STATUS_VENDA['VENDA_CANCELADA']){
                $msg = "Esta venda se encontra cancelada no momento.";		
                $strRedirect = "/prepag2/commerce/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Informa Pagamento") . "&link=" . urlencode("/prepag2/commerce/conta/lista_vendas.php");
        }
}

//Comprovantes
if($pagto_tipo == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']){
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_dep_doc_transf.php";

} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']){
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_boleto.php";

} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['REDECARD_MASTERCARD'] || $pagto_tipo == $FORMAS_PAGAMENTO['REDECARD_DINERS']){
        $strRedirect = "/prepag2/commerce/redecard/rc_comprovante.php";

} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']){
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";

} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']){
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";

} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO']){
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";

} elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']){
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";

} elseif ($pagto_tipo == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC){ // $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	

} elseif ($pagto_tipo == $PAGAMENTO_PIN_EPREPAG_NUMERIC){ // $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";		// EPREPAG_URL_HTTPS

} elseif ($pagto_tipo == $PAGAMENTO_HIPAY_ONLINE_NUMERIC){ 
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	

} elseif ($pagto_tipo == $PAGAMENTO_PAYPAL_ONLINE_NUMERIC){ 
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	

} elseif ($pagto_tipo == $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC){ 
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	
        
} elseif ($pagto_tipo == $PAGAMENTO_PIX_NUMERIC){ 
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	

//----Wagner 
} elseif (b_IsPagtoCielo($pagto_tipo)){ 
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	

//----Wagner ATÉ
} else {
        $strRedirect = "/prepag2/commerce/conta/lista_vendas.php";
}

//Fechando Conexão
pg_close($connid);

//Redireciona
redirect($strRedirect);
?>