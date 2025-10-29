<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);  // Exibe todos os tipos de erros

require "../class/classGerarEFinanceira.php";

$efinanceira = new GerarEFinanceira();

$xmlCad = $efinanceira->gerarCadastroDeclarante();


$xmlLote = $efinanceira->gerarLoteAssincrono([
    ['xml' => $xmlCad['xml']->saveXML($xmlCad['xml']->documentElement), 'id' => $xmlCad['id']]
]);

$xmlLoteAssinado = $efinanceira->assinarLoteEventos($xmlLote);

$xmlCriptgrafado = $efinanceira->criptografarLoteEF($xmlLoteAssinado->saveXML(), 'LOTE_20251028_012');

try {
    header('Content-Type: application/xml; charset=utf-8');
    $resultado = $efinanceira->enviarLoteEFinanceira(
        $xmlCriptgrafado,
        $usarGzip = false,
        $producao = false // true para produção
    );
    
    echo $resultado;
    
} catch (Exception $e) {
    echo $e->getMessage();
}