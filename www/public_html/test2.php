<?php
libxml_use_internal_errors(true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros

require "../class/classGerarEFinanceira.php";

$efinanceira = new GerarEFinanceira();

//$xmlAbertura = $efinanceira->gerarAbertura('2025-09-01', '2025-09-30');
//$xmlAbertura = $efinanceira->gerarFechamento('2025-09-01', '2025-09-30');
//$xmlAbertura = $efinanceira->gerarMovimentacaoFinanceira(1, '12345678901', 'Joao Mota', '2000-02-10', 'Rua teste da silve', '2025', '09', 1111, 999.99, 200.11);
 $xmlAbertura = $efinanceira->gerarCadastroDeclarante();

header('Content-Type: application/xml; charset=utf-8');
echo $xmlAbertura->saveXML();