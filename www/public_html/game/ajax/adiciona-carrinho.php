<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
header("Content-Type: text/html; charset=ISO-8859-1", true);
function isAjax()
{
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}
function block_direct_calling()
{
	if (!isAjax()) {
		echo "Chamada não permitida<br>";
		die("Stop");
	}
}
block_direct_calling();
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";

function getModelo($modelo)
{
	try {
		$pdo = ConnectionPDO::getConnection()->getLink();

		$sql = "select 1 from tb_operadora_games_produto_modelo where ogpm_ativo = 1 and ogpm_id = :modelo";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":modelo", $modelo, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetch();
	} catch (Exception $ex) {
		return false;
		echo $ex;
	}
}

function getProduto($produto)
{
	try {
		$pdo = ConnectionPDO::getConnection()->getLink();

		$sql = "select 1 from tb_operadora_games_produto where ogp_ativo = 1 and ogp_id = :produto";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":produto", $produto, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetch();
	} catch (Exception $ex) {
		return false;
		echo $ex;
	}
}

function actions($get)
{
	$NO_HAVE = 'NO HAVE';

	$carrinho = (isset($_SESSION['carrinho'])) ? $_SESSION['carrinho'] : null;
	//Acao
	$acao = $get['acao'];
	//Modelo
	$mod = $get['mod'];
	//Valor para p´rodutos de valor variável
	$valor = $get['valor'] > 0 ? $get['valor'] : 0;
	//Idjogo
	$codeProd = $get['codeProd'];
	//Adiciona modelo no carrinho
	//---------------------------------------------------------------
	if ($mod && $mod != "" && is_numeric($mod)) {
		if ($acao == "u") {

			//Qtde
			if (isset($get['qtde'])) $qtde = $get['qtde'];

			//Atualiza se for qtde valida
			if ($qtde && is_numeric($qtde) && $qtde > 0) {

				//somente para evitar fraude
				if ($qtde > 999)
					$qtde = 999;

				//verifica se o modelo esta no carrinho
				if ($carrinho[$mod]) {

					//atualiza modelo no carrinho
					$carrinho[$mod] += $qtde;

					//Se o modelo nao esta no carrinho, adiciona
				} else {
					//verifica se o modelo existe e esta ativo	
					//Adiciona modelo no carrinho
					if (getModelo($mod)) {
						$carrinho[$mod] = $qtde;
					} else {
						return false;
					}
				}
			}
		}
	} elseif ($mod == $NO_HAVE) {

		if (($mod == $NO_HAVE) && !$valor) {
			return false;
		}

		if ($acao == "u") {
			//Qtde
			if (isset($get['qtde'])) $qtde = $get['qtde'];

			//Atualiza se for qtde valida
			if ($qtde && is_numeric($qtde) && $qtde > 0) {

				//somente para evitar fraude
				if ($qtde > 999)
					$qtde = 999;
				//verifica se o modelo esta no carrinho
				if ($carrinho[$mod][$codeProd][$valor]) {
					//atualiza modelo no carrinho
					$carrinho[$mod][$codeProd][$valor] += $qtde;
				} else {
					//Se o modelo nao esta no carrinho, adiciona
					if (getProduto($codeProd)) {
						$carrinho[$mod][$codeProd][$valor] = $qtde;
					} else {
						return false;
					}
				}
			}
		}
	}else{
		return false;
	}
	//Devolve carrinho no session
	$_SESSION['carrinho'] = $carrinho;
	return true;
}

$inseriu = actions($_POST);

if($inseriu){
	echo "sucesso";
}else{
	echo "falhou";
}