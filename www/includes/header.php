<?php
$url_session_expires = $url_session_expires ?? '/login.php';
if (empty($_SESSION["iduser_bko"])) {
    echo "<script>";
    echo "setTimeout('top.location = \'" . addslashes((string)$url_session_expires) . "\'', 0);";
    echo "</script>";
    exit;
}

$connid = $connid ?? null;
if ($connid) {
    $sql = "select bko_autoriza, bko_local_acesso from usuarios where id=$1";
    $result = pg_query_params($connid, $sql, array($_SESSION['iduser_bko']));
    if ($result) {
        $pgrow = pg_fetch_array($result);
        if (is_array($pgrow)) {
            $_SESSION['pgrow'] = $pgrow;
        }
    }
}
