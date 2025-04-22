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
$Estado = filter_var($_POST['estado'],FILTER_SANITIZE_STRING);

//set client_encoding to utf8;
$SQLBairro = "SELECT 
					ug_bairro
				FROM (

					(SELECT ug_bairro
					FROM dist_usuarios_games
					WHERE replace(ug_cidade, '\'', '') = :ug_cidade
						AND ug_estado = :ug_estado
						AND ug_ativo = 1
						AND ug_status = 1
						AND ug_coord_lat != 0
						AND ug_coord_lng != 0
					)
				) as locais
				GROUP BY ug_bairro 
				ORDER BY ug_bairro
";
//echo "\n<!-- cidade: '$Cidade' \n".$SQLBairro."-->\n";
$Estado = trim($Estado);
$stmt = $pdo->prepare($SQLBairro);
$stmt->bindParam(':ug_cidade', $Cidade, PDO::PARAM_STR);
$stmt->bindParam(':ug_estado', $Estado, PDO::PARAM_STR);
$stmt->execute();
$ResultadoCidade = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($ResultadoCidade) > 0) {
    $render = '&nbsp;&nbsp;&nbsp;Bairro: ';
    $render .= '<select name="bairro" id="bairro">';
    $render .= '<option value="">Selecione o Bairro</option>';
    foreach ($ResultadoCidade as $RowCidade){
            $render .= '<option value="'.$RowCidade['ug_bairro'].'">'.$RowCidade['ug_bairro'].'</option>';
    }
    $render .= '</select>';
}
else {
    $render = '&nbsp;&nbsp;&nbsp;Bairro: Nenhum bairro para esta cidade';
}
echo $render;
?>
