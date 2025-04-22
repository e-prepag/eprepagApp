<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros

require "../includes/constantes.php";
require_once '/www/includes/pdv/functions.php';
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "../includes/funcoes_cpf.php";
require_once $raiz_do_projeto . "consulta_cpf/config.inc.cpf.php";


require_once $raiz_do_projeto . 'includes/functions.php';
require_once $raiz_do_projeto . "consulta_cpf/trocaAutomatica.php";
require_once '/www/includes/pdv/functions.php';

require "/www/consulta_cpf/Onminidata.php";

$inicio = microtime(true);

echo "01336259310 " . "28/12/1950";

$onminidata = new Onminidata();
$onminidata->query("01336259310", "28/12/1950");
$result = $onminidata->collects_data();
$id_search = $onminidata->take_property($result, "id_search");
///sleep(5);
$tempoRetorno = 0;
$lista_resposta = $onminidata->result_status_search($id_search);
$file = fopen("/www/log/logONMINIDATA.txt", "a+");
fwrite($file, "DATA " . date("d-m-Y H:i:s") . "\n");
fwrite($file, "resposta id_search: " . json_encode($id_search) . "\n");
fwrite($file, "tentativa numero: " . $tempoRetorno . "\n");
fwrite($file, "Duraчуo da requisiчуo: " . number_format((microtime(true) - $inicio), 4) . "\n");
fwrite($file, str_repeat("*", 50) . "\n");
fclose($file);

while ($lista_resposta["pesquisas"]["camposResposta"]["status"] != "DadoDisponivel" &&
$lista_resposta["pesquisas"]["camposResposta"]["status"] != "ArgumentosInvalidos") {

    if ($tempoRetorno >= 9) {
        break;
    }

    $lista_resposta = $onminidata->result_status_search($id_search);
    echo print_r($lista_resposta);
    $tempoRetorno++;
    sleep(10);
    $file = fopen("/www/log/logONMINIDATA.txt", "a+");
    fwrite($file, "DATA " . date("d-m-Y H:i:s") . "\n");
    fwrite($file, "tentativa numero: " . $tempoRetorno . "\n");
    fwrite($file, "resposta id_search: " . json_encode($id_search) . "\n");
    fwrite($file, "Duraчуo da requisiчуo: " . number_format((microtime(true) - $inicio), 4) . "\n");
    fwrite($file, str_repeat("*", 50) . "\n");
    fclose($file);
}

echo print_r("SFIM");

?>