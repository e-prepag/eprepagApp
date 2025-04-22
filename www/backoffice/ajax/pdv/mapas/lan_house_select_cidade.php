<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
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
$Estado = $_POST['estado'];

$SQLBairro = "set client_encoding to utf8;SELECT distinct(ug_cidade) as ug_cidade
					FROM dist_usuarios_games
					WHERE ug_estado = '".utf8_decode($Estado)."' 
					ORDER BY ug_cidade";
//echo $SQLBairro."<br>";
//die();
$ResultadoCidade = SQLexecuteQuery($SQLBairro);

$render = '<select class="form-control" name="cidade" id="cidade" onChange="MostraBairro();">';
$render .= '<option value="">Todos as Cidades</option>';
while ($RowCidade = pg_fetch_array($ResultadoCidade)){
	$render .= '<option value="'.$RowCidade['ug_cidade'].'">'.$RowCidade['ug_cidade'].'</option>';
}
$render .= '</select>';
echo $render;
?>
