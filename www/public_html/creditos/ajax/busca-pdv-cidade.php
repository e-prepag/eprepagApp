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
require_once DIR_INCS."main.php";
require_once DIR_INCS."pdv/main.php";

//Id do GoCASH
$id_gocash = 1;

$Estado = filter_var ( utf8_encode($_POST['estado']), FILTER_SANITIZE_STRING);

//set client_encoding to utf8;
$SQLCidade = "SELECT 
		ug_cidade
	FROM (

		(SELECT 
			ug_cidade
		FROM dist_usuarios_games
		WHERE ug_ativo = 1
			AND ug_status = 1
                    AND trim(both ' ' from ug_estado) = :ug_estado
			AND ug_coord_lat != 0
			AND ug_coord_lng != 0
		)
	UNION ALL
		(SELECT 
			us_cidade AS ug_cidade
		FROM dist_usuarios_stores_cartoes
		WHERE us_coord_lat != 0
			AND us_coord_lng != 0
					AND trim(both ' ' from us_estado) = :us_estado
                        AND us_id IN (
                            select us_id from classificacao_mapas cm
                                    INNER JOIN classificacao_mapas_pdv cmp ON cm.cm_id = cmp.cm_id
                            WHERE cm.cm_id = :idGoCash
                                    AND cm_status = 1
                        )
		)
    UNION ALL
		(SELECT 
            trim(both ' ' from us_cidade) AS ug_cidade
            FROM dist_usuarios_stores_qiwi
            WHERE us_coord_lat != 0
                AND us_coord_lng != 0
					AND trim(both ' ' from us_estado) = :us_estado2
		)
	) as locais
	GROUP BY ug_cidade
	ORDER BY ug_cidade
	";

//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();
$stmt = $pdo->prepare($SQLCidade);
$auxEstado=trim($Estado);
$stmt->bindParam(':ug_estado', $auxEstado, PDO::PARAM_STR);
$stmt->bindParam(':us_estado', $auxEstado, PDO::PARAM_STR);
$stmt->bindParam(':us_estado2',$auxEstado, PDO::PARAM_STR);
$stmt->bindParam(':idGoCash', $id_gocash, PDO::PARAM_INT);
$stmt->execute();
$ResultadoCidade = $stmt->fetchAll(PDO::FETCH_ASSOC);

$render = '<select name="cidade" id="cidade" class="form-control input-sm" onChange="MostraBairro();">';
$render .= '<option value="">Selecione a Cidade</option>';
foreach ($ResultadoCidade as $RowCidade){
	$render .= '<option value="'.$RowCidade['ug_cidade'].'">'.$RowCidade['ug_cidade'].'</option>';
}
$render .= '</select>';
echo $render;
?>
