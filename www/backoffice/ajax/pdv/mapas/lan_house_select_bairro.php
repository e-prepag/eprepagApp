<?php
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
$Cidade = $_POST['cidade'];
$Estado = $_POST['estado'];

$SQLBairro = "set client_encoding to utf8;SELECT distinct(ug_bairro) as ug_bairro
					FROM dist_usuarios_games
					WHERE ug_cidade = '".utf8_decode($Cidade)."' 
						AND ug_estado = '".utf8_decode($Estado)."' 
					ORDER BY ug_bairro";
//echo $SQLBairro."<br>";

$ResultadoCidade = SQLexecuteQuery($SQLBairro);

$render = '<select class="form-control" name="bairro" id="bairro">';
$render .= '<option value="">Todos os Bairros</option>';
while ($RowCidade = pg_fetch_array($ResultadoCidade)){
	$render .= '<option value="'.$RowCidade['ug_bairro'].'">'.$RowCidade['ug_bairro'].'</option>';
}
$render .= '</select>';
echo $render;
?>
