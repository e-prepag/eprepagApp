<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "includes/main.php";

$where = " WHERE 1=1";

if(!empty($data_inclusao_inicio)){
    $where .= " AND ug.ug_data_inclusao >= '" . $data_inclusao_inicio . "'";
}

if(!empty($data_inclusao_fim)){
    $where .= " AND ug.ug_data_inclusao <= '" . $data_inclusao_fim . "'";
}

if(!empty($substatus)){
    $where .= " AND ug.ug_substatus = " . $substatus;
}

if(!empty($status)){
    $where .= " AND ug.ug_status = " . $status;
}

if(!empty($cpf)){
    $where .= " AND (ug.ug_repr_legal_cpf = '" . $cpf . "' OR ugs.ugs_cpf = '" . $cpf . "')";
}

$sql = "SELECT count(*) as total FROM dist_usuarios_games ug LEFT JOIN dist_usuarios_games_socios ugs ON ug.ug_id = ugs.ug_id " . $where;
$rs_total = SQLexecuteQuery($sql);
$row = pg_fetch_assoc($rs_total);
if($row) $registros_total = $row["total"];

$sql = "SELECT ug.ug_id as id_pdv,ug.ug_cnpj, ug.ug_repr_legal_nome as nome_repr, ug_repr_legal_cpf as cpf_repr, ugs.ugs_nome as nome_socio, ugs.ugs_cpf as cpf_socio FROM dist_usuarios_games ug LEFT JOIN dist_usuarios_games_socios ugs ON ug.ug_id = ugs.ug_id " . $where;
$sql .= " ORDER BY ug.ug_id DESC";	

//    echo "<pre>" . var_export($_POST, true) . "</pre>";
//    die();

$pdvs = array();
$rs_pdv_download = SQLexecuteQuery($sql);
while($row = pg_fetch_assoc($rs_pdv_download)){
    $pdvs[] = $row;
}
$fileName = "relatorio_representantes_socios-".date('d-m-Y').".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $fileName);
$output = fopen('php://output', 'w');
fputcsv($output, array('Id', 'CNPJ', 'Representante Legal', 'CPF do Representante Legal', 'Sócio', 'CPF do Sócio'), ";");
if (count($pdvs) > 0) {
    foreach ($pdvs as $row) {
        fputcsv($output, $row, ";");
    }
}
