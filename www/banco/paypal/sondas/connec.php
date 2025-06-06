<?php
require_once __DIR__ . "/../../../includes/load_dotenv.php";

$fd_conn = mysql_connect(getenv('MYSQL_HOST'),getenv('DB_NAME5'),getenv('DB_PASS_STRING')) or die ("Erro na conexão");
mysql_select_db(getenv('DB_USER_CON'), $fd_conn);
?>