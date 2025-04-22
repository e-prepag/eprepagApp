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
require_once "../../../includes/constantes.php";
require_once  DIR_INCS . "main.php";
require_once  DIR_INCS . "pdv/main.php";
//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$Cidade = filter_var(utf8_decode(trim(str_replace("'", "",$_POST['cidade']))),FILTER_SANITIZE_STRING);

$SQLBairro = "SELECT distinct(ug_bairro) as ug_bairro
					FROM dist_usuarios_games
					WHERE replace(ug_cidade, '\'', '') = :ug_cidade
						AND ug_ativo = 1 
						AND ug_status = 1
					ORDER BY ug_bairro";
//echo $SQLBairro."<br>";

$stmt = $pdo->prepare($SQLBairro);
$stmt->bindParam(':ug_cidade', $Cidade, PDO::PARAM_STR);
$stmt->execute();
$ResultadoCidade = $stmt->fetchAll(PDO::FETCH_ASSOC);

$render = '&nbsp;&nbsp;&nbsp;Bairro: ';
$render .= '<select name="bairro" id="bairro">';
$render .= '<option value="">Selecione o Bairro</option>';
foreach ($ResultadoCidade as $RowCidade){
	$render .= '<option value="'.$RowCidade['ug_bairro'].'">'.$RowCidade['ug_bairro'].'</option>';
}
$render .= '</select>';
echo $render;
?>
