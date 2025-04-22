<?php ob_start(); 
    require_once '../../../includes/constantes.php';
    require_once $raiz_do_projeto . "backoffice/includes/topo_bko_inc.php";
    require_once $raiz_do_projeto . "includes/pdv/corte_classPrincipal.php"; 
?>
<?php
	$msg = "";

	//Valida codigo do boleto
	if($msg == ""){
		if(!$bbc_boleto_codigo || trim($bbc_boleto_codigo) == "" || !is_numeric($bbc_boleto_codigo)) $msg = "Código do boleto inválido.\n";
	}

	//Busca dados do boleto
	if($msg == ""){
		$sql = "select * from boleto_bancario_cortes bbc
				inner join cortes c on c.cor_codigo = bbc.bbc_cor_codigo
				where bbc.bbc_boleto_codigo = $bbc_boleto_codigo";
		$rs_boleto = SQLexecuteQuery($sql);
		if(!$rs_boleto) $msg = "Erro ao buscar boleto.\n";
		elseif(pg_num_rows($rs_boleto) == 0) $msg = "Nenhum boleto encontrado.\n";
		else {
			$rs_boleto_row = pg_fetch_array($rs_boleto);
  			$bbc_bco_codigo = $rs_boleto_row['bbc_bco_codigo'];
			$bbc_documento 	= $rs_boleto_row['bbc_documento'];
			$bbc_valor 		= $rs_boleto_row['bbc_valor'];
			$bbc_valor_taxa = $rs_boleto_row['bbc_valor_taxa'];
			$bbc_data_venc 	= $rs_boleto_row['bbc_data_venc'];
			$bbc_ug_id 		= $rs_boleto_row['bbc_ug_id'];
			$cor_periodo_ini = $rs_boleto_row['cor_periodo_ini'];
			$cor_periodo_fim = $rs_boleto_row['cor_periodo_fim'];

			//Validacoes
			//-----------------------------------------------------------------------------------------------------
			//Banco
			if($bbc_bco_codigo != $GLOBALS['BOLETO_COD_BANCO_BRADESCO']) $msg = "Boleto não é do Bradesco.\n";
			
			//usuario
			if(!$bbc_ug_id || trim($bbc_ug_id) == "" || !is_numeric($bbc_ug_id)) $msg = "Código do usuário inválido.\n";
		}
	}

	//Obtem estabelecimento
	if($msg == ""){
		$sql  = "select * from dist_usuarios_games ug where ug.ug_id = " . $bbc_ug_id;
		$rs_estab = SQLexecuteQuery($sql);
		if(!$rs_estab || pg_num_rows($rs_estab) == 0) $msg = "Nenhum usuário encontrado.\n";
		else {
			$rs_estab_row = pg_fetch_array($rs_estab);

			$ug_tipo_cadastro 	= $rs_estab_row['ug_tipo_cadastro'];
			$ug_razao_social 	= $rs_estab_row['ug_razao_social'];
			$ug_nome 			= $rs_estab_row['ug_nome'];
			if($ug_tipo_cadastro == "PF")$sacado = $ug_nome;
			else $sacado = $ug_razao_social;
			$endereco 		= $rs_estab_row['ug_endereco'];
			$numero 		= $rs_estab_row['ug_numero'];
			if(trim($numero) != "") $endereco .= ", " . trim($numero);
			$complemento	= $rs_estab_row['ug_complemento'];
			if(trim($complemento) != "") $endereco .= " - " . trim($complemento);
			$bairro 		= $rs_estab_row['ug_bairro'];
			$municipio 		= $rs_estab_row['ug_cidade'];
			if(trim($bairro) != "") $bairro .= " - " . trim($municipio);
			$uf 			= $rs_estab_row['ug_estado'];
			$cep 			= $rs_estab_row['ug_cep'];
		}
	}

	//gera boleto
	if($msg == ""){
		// DADOS DO BOLETO PARA O SEU CLIENTE
		$data_venc 		= formata_data($bbc_data_venc, 0); 
		$taxa_boleto 	= $bbc_valor_taxa;
		$valor_boleto 	= number_format($bbc_valor, 2, ',', '');
		$num_doc 		= $bbc_documento;
		//$sacado 		= $razao_social;
		$periodo_ini	= $cor_periodo_ini;
		$periodo_fim	= $cor_periodo_fim;
		
		
		// NÃO ALTERAR!
		ob_clean();
		include $raiz_do_projeto . "banco/boletos/include/funcoes_bradesco_fixo_corte.php";
		include $raiz_do_projeto . "banco/boletos/include/layout_bradesco.php";
		
	}
	
	echo str_replace("\n", "<br>", $msg);
?>
