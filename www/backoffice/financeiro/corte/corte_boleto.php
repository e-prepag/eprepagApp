<?php ob_start(); 
    require_once '../../../includes/constantes.php';
    require_once $raiz_do_projeto . "backoffice/includes/topo.php";
    require_once $raiz_do_projeto . "includes/pdv/corte_classPrincipal.php"; 
?>
<?php
	$msg = "";
	$str_redirect = "";

	//Valida codigo do boleto
	if($msg == ""){
		if(!$bbc_boleto_codigo || trim($bbc_boleto_codigo) == "" || !is_numeric($bbc_boleto_codigo)) $msg = "Código do boleto inválido.\n";
	}

	//Busca dados do boleto
	if($msg == ""){
		$sql = "select * from boleto_bancario_cortes bbc
				where bbc.bbc_boleto_codigo = $bbc_boleto_codigo";
		$rs_boleto = SQLexecuteQuery($sql);
		if(!$rs_boleto) $msg = "Erro ao buscar boleto.\n";
		elseif(pg_num_rows($rs_boleto) == 0) $msg = "Nenhum boleto encontrado.\n";
		else {
			$rs_boleto_row = pg_fetch_array($rs_boleto);
  			$bbc_bco_codigo = $rs_boleto_row['bbc_bco_codigo'];

			//Validacoes
			//-----------------------------------------------------------------------------------------------------
			//Banco
			if(!$bbc_bco_codigo || trim($bbc_bco_codigo) == "" || !is_numeric($bbc_bco_codigo)) $msg = "Código do banco inválido.\n";
		}
	}

	//define boleto
	if($msg == ""){
		if($bbc_bco_codigo == $GLOBALS['BOLETO_COD_BANCO_BRADESCO']) $str_redirect = "corte_boleto_bradesco.php?bbc_boleto_codigo=$bbc_boleto_codigo";
		else $msg = "Boleto para o banco $bbc_bco_codigo não implementado.\n";
	}

	//redirect
	if($msg == ""){
		ob_clean();
		header("Location: $str_redirect");
		exit;
	}
	
	echo str_replace("\n", "<br>", $msg);
?>
