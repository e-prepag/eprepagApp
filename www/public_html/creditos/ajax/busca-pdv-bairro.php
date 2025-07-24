<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada n�o permitida<br>";
           die("Stop");
    }
}
block_direct_calling();
require_once "../../../includes/constantes.php";
require_once DIR_INCS."main.php";
require_once DIR_INCS."pdv/main.php";

//Id do GoCASH
$id_gocash = 1;

$Cidade =  isset($_POST['cidade']) ? filter_var(utf8_decode(trim(str_replace("'", "",$_POST['cidade']))),FILTER_SANITIZE_STRING) : '';
$Estado = filter_var ( utf8_decode($_POST['estado']), FILTER_SANITIZE_STRING);

//Conectando com PDO para execu��o da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$SQLBairro = "SELECT 
					ug_bairro
				FROM (

					(SELECT ug_bairro
					FROM dist_usuarios_games
					WHERE trim(both ' ' from replace(ug_cidade, '''', '')) = :ug_cidade
						AND trim(both ' ' from ug_estado) = :ug_estado
						AND ug_ativo = 1
						AND ug_status = 1
						AND ug_coord_lat != 0
						AND ug_coord_lng != 0
					)
				UNION ALL
					(SELECT us_bairro AS ug_bairro
					FROM dist_usuarios_stores_cartoes
					WHERE 
                        trim(both ' ' from replace(us_cidade, '''', '')) = :us_cidade
						AND trim(both ' ' from us_estado) = :us_estado 
						AND us_coord_lat != 0
						AND us_coord_lng != 0
                                                AND us_id IN (
                                                    select us_id from classificacao_mapas cm
                                                            INNER JOIN classificacao_mapas_pdv cmp ON cm.cm_id = cmp.cm_id
                                                    WHERE cm.cm_id = :idGoCash	
                                                            AND cm_status = 1
                                                )
					)
                UNION ALL
					(SELECT trim(both ' ' from us_bairro) AS ug_bairro
					FROM dist_usuarios_stores_qiwi
					WHERE trim(both ' ' from replace(us_cidade, '''', '')) = :us_cidade2
						AND trim(both ' ' from us_estado) = :us_estado2
						AND us_coord_lat != 0
						AND us_coord_lng != 0
					)
				) as locais
				GROUP BY ug_bairro 
				ORDER BY ug_bairro
				";
//echo "\n<!-- cidade: '$Cidade' \n".$SQLBairro."-->\n";
$stmt = $pdo->prepare($SQLBairro);
$auxEstado=trim($Estado);
$auxCidade=trim($Cidade);
$stmt->bindParam(':ug_cidade', $auxCidade, PDO::PARAM_STR);
$stmt->bindParam(':ug_estado', $auxEstado, PDO::PARAM_STR);
$stmt->bindParam(':us_cidade', $auxCidade, PDO::PARAM_STR);
$stmt->bindParam(':us_estado', $auxEstado, PDO::PARAM_STR);
$stmt->bindParam(':idGoCash', $id_gocash, PDO::PARAM_INT);
$stmt->bindParam(':us_cidade2', $auxCidade, PDO::PARAM_STR);
$stmt->bindParam(':us_estado2', $auxEstado, PDO::PARAM_STR);
$stmt->execute();
$ResultadoCidade = $stmt->fetchAll(PDO::FETCH_ASSOC);


$render = '<select name="bairro" class="form-control  input-sm" id="bairro">';
$render .= '<option value="">Selecione o Bairro</option>';
foreach($ResultadoCidade as $RowCidade){
	$render .= '<option value="'.$RowCidade['ug_bairro'].'">'.$RowCidade['ug_bairro'].'</option>';
}
$render .= '</select>';
echo $render;
?>
