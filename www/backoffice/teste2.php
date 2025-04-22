<?php

require_once "../db/connect.php";
require_once "../db/ConnectionPDO_teste.php";

$con = ConnectionPDO::getConnection();
    if ($con->isConnected()) {

        $pdo = $con->getLink();

        $sql = "Select * from tb_vendas_games;";

        $stmt = $pdo->prepare($sql);

        $stmt->execute();

    }

?>
