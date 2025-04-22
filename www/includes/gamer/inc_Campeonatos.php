<?php

// ================================================
function get_Campeonato_modelo($prod_id, &$params) {

	$opr_id = $GLOBALS['CAMPEONATO_OPR_ID'];
	$prod_id = $GLOBALS['CAMPEONATO_PROD_ID'];
	$prod_mod_id = $GLOBALS['CAMPEONATO_PROD_MOD_ID'];
//echo "prod_id: ".$prod_id."<br>";

	$sql = "select * from tb_operadora_games_produto ogp 
				inner join tb_operadora_games_produto_modelo ogpm on ogp.ogp_id = ogpm.ogpm_ogp_id
			where 1=1
				and ogp.ogp_id = $prod_id 
				and ogpm.ogpm_id = $prod_mod_id 
				and ogp.ogp_opr_codigo = $opr_id ";
	if(!is_null($valor)) {
		$sql .= "	and ogpm.ogpm_pin_valor = $valor ";
	}
	$sql .= "order by ogp_id desc";
//		"	--and (0=1 or ogp.ogp_ativo = 1) "
echo "<!-- ".str_replace("\n","<br>\n",$sql)."<br> -->";
//echo "".$sql."<br>";

	$rs = SQLexecuteQuery($sql);

	$params['ogpm_id'] = 0;
	if($rs && pg_num_rows($rs) != 0){
		$rs_row = pg_fetch_array($rs);
		$params['ogpm_id']			= $rs_row['ogpm_id'];
		$params['ogpm_ativo']		= $rs_row['ogpm_ativo'];
		$params['ogp_nome']			= $rs_row['ogp_nome'];
		$params['ogp_nome_imagem']	= $rs_row['ogp_nome_imagem'];
		$params['ogpm_valor']		= $rs_row['ogpm_valor'];
		$params['ogp_descricao']	= $rs_row['ogp_descricao'];
	}
	return $params['ogpm_id'];
}


// ================================================
function get_Campeonato_Pagto_Completo($ug_id, $prod_id, &$pagtos_valor) {

	$prod_id	= $GLOBALS['CAMPEONATO_PROD_ID'];
	$opr_codigo = $GLOBALS['CAMPEONATO_OPR_ID'];
	
	$pagtos_valor = 0;
//echo "prod_id: ".$prod_id."<br>";

	$sql = "select sum(vgm_qtde) as n_vendas, sum(vgm_valor) as valor_total
			from tb_venda_games vg 
				inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id 
			where 1=1
				and vgm_opr_codigo = $opr_codigo
				and vgm_ogp_id = $prod_id 
				and vg_ug_id = $ug_id
				and vg_ultimo_status = 5
			group by vgm_opr_codigo, vg_ug_id, vgm_ogp_id, vgm_valor";
				//		and vgm_valor = $ogp_valor
				// -- apenas para operadora de Campeonatos
//		"	--and (0=1 or ogp.ogp_ativo = 1) "
//echo "".str_replace("\n","<br>\n",$sql)."<br>";
//echo "".$sql."<br>";
//gravaLog_Temporario("   SQL em get_Campeonato_Pagto_Completo($ug_id, $prod_id, $pagtos_valor): \n   ".$sql);

	$rs = SQLexecuteQuery($sql);

	$pagtos_n = 0;
	$pagtos_valor = 0;
	if($rs && pg_num_rows($rs) != 0){
		$rs_row = pg_fetch_array($rs);
		$pagtos_n		= $rs_row['n_vendas'];
		$pagtos_valor	= $rs_row['valor_total'];
	}
//gravaLog_Temporario("   SQL em get_Campeonato_Pagto_Completo($ug_id, $prod_id, $pagtos_valor)");
	return $pagtos_n;
}

// ================================================
function get_Campeonato_Dados_LH($ug_id) {

	$sql = "select * from dist_usuarios_games where ug_id = $ug_id ";
//echo "".$sql."<br>";

	$rs = SQLexecuteQuery($sql);

	if($rs && pg_num_rows($rs) != 0){
		$rs_row = pg_fetch_array($rs);
		?>
		<table border="0" cellspacing="0" width="100%" class="texto">	
			<tr><td>Nome</td><td>&nbsp;</td><td>&nbsp;<?php echo $rs_row['ug_nome_fantasia'] ?></td></tr>
			<tr><td>Responsável</td><td>&nbsp;</td><td>&nbsp;<?php echo $rs_row['ug_responsavel'] ?></td></tr>
			<tr><td>Endereço</td><td>&nbsp;</td><td>&nbsp;<?php 
								echo "".$rs_row['ug_endereco']."";
									if($rs_row['ug_numero']) echo ", ";
								echo "".$rs_row['ug_numero']."";
									if($rs_row['ug_complemento']) echo ", ";
								echo "".$rs_row['ug_complemento']."";
									if($rs_row['ug_bairro']) echo ", ";
								echo "".$rs_row['ug_bairro']." (".$rs_row['ug_cep'].") ";

			?></td></tr>
			<tr><td>Telefone</td><td>&nbsp;</td><td>&nbsp;<?php echo "(".$rs_row['ug_tel_ddd'].") ".$rs_row['ug_tel'] ?></td></tr>
			<tr><td>Cidade</td><td>&nbsp;</td><td>&nbsp;<?php echo $rs_row['ug_cidade'] ?></td></tr>
			<tr><td>Estado</td><td>&nbsp;</td><td>&nbsp;<?php echo $rs_row['ug_estado'] ?></td></tr>			
		</table>
		<br>&nbsp;
		<?php
	} else {
		echo "Lanhouse não foi encontrada (ID LH: ".$ug_id.")<br>";
	}
	return 1;
}



?>