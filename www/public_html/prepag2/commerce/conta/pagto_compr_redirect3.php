<?php require_once __DIR__ . '/../../../../includes/constantes_url.php'; ?>

<?php 
//session_start();
// function salvarErroEmArquivo($mensagem) {
    // // Caminho completo para o arquivo de log
    // $arquivoLog = __DIR__ . "/log.txt";
	
	
	
	

    // // Monta a mensagem formatada com a data/hora do erro
    // $mensagemFormatada = date('Y-m-d H:i:s') . " - " . $mensagem . "\n";

    // // Escreve a mensagem no arquivo de log (modo append)
    // file_put_contents($arquivoLog, $mensagemFormatada, FILE_APPEND);
// }

require_once "../../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
validaSessao(); 

require_once DIR_INCS . "gamer/venda_e_modelos_logica_epp.php";
 $mensagemLog = "Dados ANTES de pg_fetch_array(\$rs_venda): " . print_r($rs_venda, true);
    //salvarErroEmArquivo($mensagemLog);
$rs_venda_row = pg_fetch_array($rs_venda);

$mensagemLog = "Dados DEPOIS de pg_fetch_array(\$rs_venda): " . print_r($rs_venda_row, true);
   // salvarErroEmArquivo($mensagemLog);

$pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
$venda_status = $rs_venda_row['vg_ultimo_status'];
echo "e-prepag: ".$venda_status ;
// //Verifica se venda cancelada
// if($msg == ""){
        // if(	$venda_status == $STATUS_VENDA['VENDA_CANCELADA']){
                // $msg = "Esta venda se encontra cancelada no momento.";		
                // $strRedirect = "/prepag2/commerce/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Informa Pagamento") . "&link=" . urlencode("/prepag2/commerce/conta/lista_vendas.php");
        // }
// }

// //Comprovantes
// if($pagto_tipo == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']){
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_dep_doc_transf.php";

// } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']){
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_boleto.php";

// } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['REDECARD_MASTERCARD'] || $pagto_tipo == $FORMAS_PAGAMENTO['REDECARD_DINERS']){
        // $strRedirect = "/prepag2/commerce/redecard/rc_comprovante.php";

// } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']){
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";

// } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']){
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";

// } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO']){
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";

// } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']){
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";

// } elseif ($pagto_tipo == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC){ // $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	

// } elseif ($pagto_tipo == $PAGAMENTO_PIN_EPREPAG_NUMERIC){ // $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";		// EPREPAG_URL_HTTPS

// } elseif ($pagto_tipo == $PAGAMENTO_HIPAY_ONLINE_NUMERIC){ 
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	

// } elseif ($pagto_tipo == $PAGAMENTO_PAYPAL_ONLINE_NUMERIC){ 
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	

// } elseif ($pagto_tipo == $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC){ 
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	
        
// } elseif ($pagto_tipo == $PAGAMENTO_PIX_NUMERIC){ 
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	

// //----Wagner 
// } elseif (b_IsPagtoCielo($pagto_tipo)){ 
        // $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";	

// //----Wagner ATÉ
// } else {
        // $strRedirect = "/prepag2/commerce/conta/lista_vendas.php";
// }

// //Fechando Conexão
// pg_close($connid);

// //Redireciona
// redirect($strRedirect);
// Verifica se a venda está cancelada
if ($venda_status == $STATUS_VENDA['VENDA_CANCELADA']) {
    $msg = "Esta venda se encontra cancelada no momento.";		
    $strRedirect = "/prepag2/commerce/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Informa Pagamento") . "&link=" . urlencode("/prepag2/commerce/conta/lista_vendas.php");
} else {
    // Comprovantes de pagamento
	echo "e-prepag: ".$pagto_tipo;
    switch ($pagto_tipo) {
		
        case $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']:
            $strRedirect = "/prepag2/commerce/conta/pagto_compr_dep_doc_transf.php";
            break;
        case $FORMAS_PAGAMENTO['BOLETO_BANCARIO']:
            $strRedirect = "/prepag2/commerce/conta/pagto_compr_boleto.php";
            break;
        case $FORMAS_PAGAMENTO['REDECARD_MASTERCARD']:
        case $FORMAS_PAGAMENTO['REDECARD_DINERS']:
            $strRedirect = "/prepag2/commerce/redecard/rc_comprovante.php";
            break;
        case $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
        case $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
        case $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO']:
        case $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
        case $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC:
        case $PAGAMENTO_PIN_EPREPAG_NUMERIC:
        case $PAGAMENTO_HIPAY_ONLINE_NUMERIC:
        case $PAGAMENTO_PAYPAL_ONLINE_NUMERIC:
        case $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC:
        case $PAGAMENTO_PIX_NUMERIC:
            $strRedirect = "/prepag2/commerce/conta/pagto_compr_online.php";
            break;
        default:
            $strRedirect = "/prepag2/commerce/conta/lista_vendas.php";
            break;
    }
}

// Fechando conexão
pg_close($connid);

// Redirecionamento
header("Location: " . $strRedirect);
// Certifique-se de sair após o redirecionamento



?>