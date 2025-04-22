<?php
//ini_set("display_errors", 1);
//ini_set("display_startup_errors", 1);
//error_reporting(E_ALL);
ini_set('memory_limit', '4038M');
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 

$term = $_GET['q'];
$page = $_GET['page'];
$offset = ($page - 1) * 100;

$stmt = $connection->prepare("select ug_id, ug_nome from usuarios_games where ug_ativo = 1 and ug_nome LIKE :term LIMIT 100 OFFSET :offset");
$stmt->bindValue(':term', "%$term%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$nomes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$more = count($nomes) == 100;
echo json_encode(array('nomes' => $nomes, 'more' => $more));
?>