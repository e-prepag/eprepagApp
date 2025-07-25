<?php
require __DIR__ . "/../db/connect.php";
require __DIR__ . "/../db/ConnectionPDO.php";
require __DIR__ . "/lib/MigrationRunner.php";

$pdo = ConnectionPDO::getConnection()->getLink();
//Rodar todas as migrations da pasta migrations
$runner = new MigrationRunner($pdo);
$runner->run(__DIR__ . '/migrations');
