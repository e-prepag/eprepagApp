<?php
libxml_use_internal_errors(true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros

require "../class/classGerarEFinanceira.php";

$efinanceira = new GerarEFinanceira();

$xmlAbertura = $efinanceira->gerarAbertura('2025-09-01', '2025-09-30');
$xmlFechamento = $efinanceira->gerarFechamento('2025-09-01', '2025-09-30');
$xmlMov = $efinanceira->gerarMovimentacaoFinanceira(1, '12345678901', 'Joao Mota', '2000-02-10', 'Rua teste da silve', '2025', '09', 1111, 999.99, 200.11);
$xmlCad = $efinanceira->gerarCadastroDeclarante();

$xmlAberturaCert = $efinanceira->assinarXML($xmlAbertura['xml']);
$xmlFechamentoCert = $efinanceira->assinarXML($xmlFechamento['xml']);
$xmlMovCert = $efinanceira->assinarXML($xmlMov['xml']);
$xmlCadCert = $efinanceira->assinarXML($xmlCad['xml']);

$xmlLote = $efinanceira->gerarLoteAssincrono([
    ['xml' => $xmlAberturaCert, 'id' => $xmlAbertura['id']],
    ['xml' => $xmlFechamentoCert, 'id' => $xmlFechamento['id']],
    ['xml' => $xmlMovCert, 'id' => $xmlMov['id']],
    ['xml' => $xmlCadCert, 'id' => $xmlCad['id']],
]);

header('Content-Type: application/xml; charset=utf-8');
echo $xmlLote->saveXML();
