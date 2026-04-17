<?php
ob_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


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

function admin_ajax_pix_to_utf8($value) {
    if (!is_string($value) || $value === '') {
        return $value;
    }
    if (preg_match('//u', $value)) {
        return $value;
    }
    $converted = function_exists('iconv') ? @iconv('ISO-8859-1', 'UTF-8//IGNORE', $value) : false;
    return ($converted !== false) ? $converted : $value;
}




try {
    // Conex�o com o banco de dados
    $pdo = ConnectionPDO::getConnection()->getLink();
    // Filtros do formul�rio
    $dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] . ' 00:00:00' : date('Y-m-01 00:00:00');
$dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] . ' 23:59:59' : date('Y-m-d 23:59:59');

    $tipo = isset($_GET['tipo']) ? $_GET['tipo']: 'todos';
 // Força UTF-8 na conexão com o banco

    // Consulta SQL
    $sql = "
       SELECT 
    p.data_inclusao AS data, 
    c.idcliente, 
    c.total,
    CASE 
        WHEN c.tipo_cliente = 'M' THEN 'GAMER'
        WHEN c.tipo_cliente = 'LR' THEN 'PDV'
        ELSE 'OUTRO' 
    END AS tipo,
    c.numcompra, 
    COALESCE(g.ug_cpf, d.ug_cnpj) AS cpf_cnpj_cadastro,
    COALESCE(g.ug_nome, d.ug_nome_fantasia) AS nome_cadastro,
    p.cpf_cnpj_pagador, 
    p.nome_pagador,
    c.cliente_nome,
    c.idpagto,
    c.idvenda
   
FROM tb_pag_pix p
INNER JOIN tb_pag_compras c ON p.numcompra = c.numcompra
LEFT JOIN usuarios_games g ON c.idcliente = g.ug_id
LEFT JOIN dist_usuarios_games d ON c.idcliente = d.ug_id
WHERE p.data_inclusao BETWEEN :data_inicio AND :data_fim
";

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
    $item = admin_ajax_pix_to_utf8($item);
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
