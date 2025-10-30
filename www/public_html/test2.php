<?php
//libxml_use_internal_errors(true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros

require "../class/classGerarEFinanceira.php";
require '../class/classMostraXML.php';

function eUmaStringXmlValida($stringConteudo) {
    // Se a string estiver vazia, não é um XML válido.
    if (trim($stringConteudo) == '') {
        return false;
    }

    // Ponto-chave: Suprime os warnings e erros do PHP.
    // Sem isso, o loadXML() vai "poluir" seu log.
    libxml_use_internal_errors(true);

    $dom = new DOMDocument('1.0', 'UTF-8');
    
    // Tenta carregar a string
    $sucesso = $dom->loadXML($stringConteudo);

    // Opcional: Limpa os erros da memória do libxml
    libxml_clear_errors();

    // Restaura o comportamento padrão de erros (importante)
    libxml_use_internal_errors(false);

    return $sucesso;
}

$efinanceira = new GerarEFinanceira();
//exit();
//$xmlAbertura = $efinanceira->gerarAbertura('2025-09-01', '2025-09-30');
//$xmlFechamento = $efinanceira->gerarFechamento('2025-09-01', '2025-09-30');
$xmlMov = $efinanceira->gerarMovimentacaoFinanceira(1, '12345678901', 'Joao Mota', '2000-02-10', 'Rua teste da silve', '2025', '09', 1111, 999.99, 200.11);

$xmlMov['xml'] = $xmlMov['xml']->saveXML($xmlMov['xml']->documentElement);

//$xmlCad['xml'] = $xmlCad['xml']->saveXML($xmlCad['xml']->documentElement);

$xmlLote = $efinanceira->gerarLoteAssincrono([
    //['xml' => $xmlAberturaCert, 'id' => $xmlAbertura['id']],
    //['xml' => $xmlFechamentoCert, 'id' => $xmlFechamento['id']],
    $xmlMov
    //$xmlCad
]);

//$arquivo = '/www/arquivos_gerados/xml_lote_efinanceira.xml';
//file_put_contents($arquivo, $xmlLote);

//$xmlLoteAssinado = $efinanceira->assinarLoteEventos($xmlLote);

//file_put_contents('/www/arquivos_gerados/xml_lote_efinanceira-ASSINADO'.date('Y-m-d_h_i').'.xml', $xmlLoteAssinado);

//$xmlCriptgrafado = $efinanceira->criptografarLoteEF($xmlLoteAssinado, 'LOTE_20251027_001');

// if(eUmaStringXmlValida($xmlLote)) {
     //header('Content-Type: application/xml; charset=utf-8');
// }
//echo $efinanceira->validarLoteAssinado($xmlLoteAssinado);
echo $xmlLote;
//echo ($xmlLoteAssinado);
//echo $xmlCriptgrafado;
