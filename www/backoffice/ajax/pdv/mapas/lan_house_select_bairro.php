<?php
require_once "/www/backoffice/includes/encoding.php";
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();

require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

//if (function_exists('SQLexecuteQuery'))
// echo "Existe!!!";
//else echo "Naun existe!!!";
//die();
$Cidade = $_POST['cidade'] ?? "";
$Estado = $_POST['estado'] ?? "";

SQLexecuteQuery("set client_encoding to utf8;");
$SQLBairro = "SELECT distinct(ug_bairro) as ug_bairro
					FROM dist_usuarios_games
					WHERE ug_cidade = $1 
						AND ug_estado = $2 
					ORDER BY ug_bairro";
$params = array(backoffice_utf8_to_iso($Cidade), backoffice_utf8_to_iso($Estado));
//echo $SQLBairro."<br>";

$ResultadoCidade = SQLexecuteQueryParams($SQLBairro, $params);

$render = '<select class="form-control" name="bairro" id="bairro">';
$render .= '<option value="">Todos os Bairros</option>';
while ($RowCidade = pg_fetch_array($ResultadoCidade)){
	$render .= '<option value="'.$RowCidade['ug_bairro'].'">'.$RowCidade['ug_bairro'].'</option>';
}
$render .= '</select>';
echo $render;
?>
