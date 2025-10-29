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

//$xmlLoteAssinado = file_get_contents('/www/arquivos_gerados/xml_lote_efinanceira-ASSINADO2025-10-29_04_35.xml');

$xmlCriptgrafado = $efinanceira->criptografarLoteEF($xmlLoteAssinado, 'LOTE_20251028_52');

try {
    header('Content-Type: application/xml; charset=utf-8');
    $resultado = $efinanceira->enviarLoteEFinanceira(
        $xmlCriptgrafado,
        false,
        false // true para produção
    );
    
    echo $resultado;
    
} catch (Exception $e) {
    echo $e->getMessage();
}