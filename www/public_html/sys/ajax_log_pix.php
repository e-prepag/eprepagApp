<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/includes/configIP.php";
require_once "/www/class/phpmailer/class.smtp.php";
require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");




try {
    // Conex�o com o banco de dados
    $pdo = ConnectionPDO::getConnection()->getLink();
    // Filtros do formul�rio
    $dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
    $dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
    $tipo = $_GET['tipo'] ? $_GET['tipo']: 'todos';
 // Força UTF-8 na conexão com o banco

    // Consulta SQL
    $sql = "
        SELECT p.data_inclusao AS data, 
               c.idcliente, 
               c.numcompra, 
               COALESCE(g.ug_cpf, d.ug_cnpj) AS cpf_cnpj_cadastro,
               COALESCE(g.ug_nome, d.ug_nome_fantasia) AS nome_cadastro,
               p.cpf_cnpj_pagador, 
               p.nome_pagador
        FROM tb_pag_pix p
        INNER JOIN tb_pag_compras c ON p.numcompra = c.numcompra
        LEFT JOIN usuarios_games g ON c.idcliente = g.ug_id
        LEFT JOIN dist_usuarios_games d ON c.idcliente = d.ug_id
        WHERE p.data_inclusao BETWEEN :data_inicio AND :data_fim";

    if ($tipo == 'pf') {
        $sql .= " AND g.ug_cpf IS NOT NULL";
    } else if ($tipo == 'pdv') {
        $sql .= " AND d.ug_cnpj IS NOT NULL";
    }
    // Preparar e executar a query
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'data_inicio' => $dataInicio,
        'data_fim' => $dataFim
    ]);

    $pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
  // Corrige caracteres UTF-8
  array_walk_recursive($pagamentos, function (&$item) {
    if (is_string($item) && !mb_detect_encoding($item, 'UTF-8', true)) {
        $item = utf8_encode($item);
    }
});

// Retorna JSON corretamente formatado
echo json_encode(["data" => $pagamentos], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
   
} catch (\Throwable $th) {
  //  http_response_code(500);
    echo json_encode(["error" => $th->getMessage()]);
    exit;
}
ob_end_flush();
