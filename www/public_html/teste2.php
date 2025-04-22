<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros

require "../consulta_cpf/Caf.php";

$requestParams = [
    'cpfcnpj' => '0837391398',
    'data_nascimento' => '27-01-2000'
];

echo print_r($requestParams) . "<br>";

$caf = new ClassCAF();
$lista_resposta = $caf->consultaCPF($requestParams['cpfcnpj'], $requestParams['data_nascimento']);

echo print_r($lista_resposta) . "<br>";

if (isset($lista_resposta["pesquisas"]["camposResposta"]["status"])) {

    $dataFornecida = $requestParams['data_nascimento'];
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFornecida)) {
        // Converte para DD/MM/YYYY
        $partes = explode('-', $dataFornecida);
        $dataFornecida = $partes[2] . '/' . $partes[1] . '/' . $partes[0];
    }

    if ($lista_resposta["pesquisas"]["camposResposta"]["status"] == "REGULAR" && $lista_resposta["pesquisas"]["camposResposta"]["data_nascimento"] == $dataFornecida) {
        echo 3 . "<br>";
    } else {
        echo 2 . "<br>";
    }
} else {
    if(isset($lista_resposta["msg"]) && 
    ($lista_resposta["msg"] == "Erro ao analisar a resposta JSON." || strpos($lista_resposta["msg"], "Erro ao fazer a") !== false)) {
        echo 1 . "<br>";
        exit;
    }
    echo 2 . "<br>";
}


?>