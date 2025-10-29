<?php

require_once '../class/classGerarEFinanceira.php';
//header("Content-Type: text/plain; charset=utf-8");

$gerador = new GerarEFinanceira();

$_POST = json_decode(file_get_contents('php://input'), true);

$protocolo = "{$_POST['protocolo']}";

sleep($_POST['tempo_espera']);

try {
    header('Content-Type: application/xml; charset=utf-8');

    $resultado = $gerador->consultarLoteEFinanceira($protocolo, false);
    
    echo $resultado;
    // echo "\n✓ Situação: " . $resultado['situacao'] . "\n";
    // echo "Mensagem: " . $resultado['mensagem'] . "\n";
    // echo "cdResposta: " . $resultado['cdResposta'] . "\n";
    
    // if (isset($resultado['recibos'])) {
    //     echo "\nRecibos:\n";
    //     print_r($resultado['recibos']);
    // }
    
    // if (isset($resultado['ocorrencias'])) {
    //     echo "\nOcorrências:\n";
    //     print_r($resultado['ocorrencias']);
    // }
    
} catch (Exception $e) {
    echo "\n✗ ERRO: " . $e->getMessage() . "\n";
}