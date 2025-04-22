<?php

function verificaProdutoGenebra($codigo, $ambiente, $conexao){
	$operadora = "codigo";
	if($ambiente == "PDV"){
		$sql = "select ogp_opr_codigo from tb_dist_operadora_games_produto where ogp_id = :CODIGO;";
	}else{
		$sql = "select ogp_opr_codigo from tb_operadora_games_produto where ogp_id = :CODIGO;";
	}
	$query = $conexao->getLink()->prepare($sql);
	$query->bindValue(":CODIGO", $codigo);
	$query->execute();
	$resultado = $query->fetch(PDO::FETCH_ASSOC);
	if($resultado != false){
		if($resultado["ogp_opr_codigo"] == $codigo){
			return escolheLayout($codigo);
		}
		return false;
	}
	return false;
}

function escolheLayout($codigoProduto){
	$caminhoLayout = "../../genebra/layout";
	switch($codigoProduto){
		case 1:
		    return $caminhoLayout."/cotacao_auto.php";
		break;
		case 2:
		    return $caminhoLayout."/cotacao_bike.php";
		break;
		case 3:
		    return $caminhoLayout."/cotacao_bolsa.php";
		break;
		case 4:
		    return $caminhoLayout."/cotacao_cyber.php";
		break;
		case 5:
		    return $caminhoLayout."/cotacao_portateis.php";
		break;
		case 6:
		    return $caminhoLayout."/cotacao_prestamista.php";
		break;
		case 7:
		    return $caminhoLayout."/cotacao_vida.php";
		break;
        case 8:
		    return $caminhoLayout."/cotacao_garantia.php";
		break;
		default:
		    return false;
		break;
	}
}

//verificaProdutoGenebra(433, "USUARIO", $conexao);
require_once escolheLayout(5);
?>