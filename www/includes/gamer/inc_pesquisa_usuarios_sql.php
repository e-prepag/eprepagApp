<?php
$sql  = "select * 
	";
if($somenteContar == 1) {
    $sql  = "select count(*) as total
        ";
    
}//end if($somenteContar)
$sql .= "		 from usuarios_games ug \n";
if($tf_u_com_totais_vendas) {	//  && $dd_opr_codigo
	$sql  .= "inner join (
				select vg_ug_id, sum(vgm.vgm_valor * vgm.vgm_qtde) as vg_valor, sum(vgm.vgm_qtde) as vg_qtde_itens, min(vg_data_inclusao) as vg_data_primeira_venda, max(vg_data_inclusao) as vg_data_ultima_venda 
				from tb_venda_games vg 
					inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id 		
				where vg_ultimo_status=5 $produtos_query
				group by vg_ug_id
			) v	on v.vg_ug_id = ug.ug_id \n";
}

$sql  .= "where 1=1 ";
if($tf_u_codigo)	{
	$sql .= " and ug.ug_id ";
	if($tf_u_codigo_include==-1) {
		$sql .= " not ";
	}
	$sql .= " in (" . $tf_u_codigo . ") ";
}
if($tf_u_status)	$sql .= " and ug.ug_ativo = " . $tf_u_status . " ";
if($tf_u_qtde_acessos_ini && $tf_u_qtde_acessos_fim) 			$sql .= " and ug.ug_qtde_acessos between " . ($tf_u_qtde_acessos_ini==-1?0:$tf_u_qtde_acessos_ini) ." and " . ($tf_u_qtde_acessos_fim==-1?0:$tf_u_qtde_acessos_fim);
if($tf_u_data_ultimo_acesso_ini && $tf_u_data_ultimo_acesso_fim)$sql .= " and ug.ug_data_ultimo_acesso between '".formata_data($tf_u_data_ultimo_acesso_ini,1)."' and '".formata_data($tf_u_data_ultimo_acesso_fim,1)."'";
if($tf_u_data_inclusao_ini && $tf_u_data_inclusao_fim) 			$sql .= " and ug.ug_data_inclusao between '".formata_data($tf_u_data_inclusao_ini,1)."' and '".formata_data($tf_u_data_inclusao_fim,1)."'";
if($tf_u_nome) 		$sql .= " and upper(ug.ug_nome) like '%" . strtoupper($tf_u_nome) . "%' ";
if($tf_u_email)		$sql .= " and upper(ug.ug_email) like '%" . strtoupper($tf_u_email) . "%' ";
if($tf_u_cpf) 		$sql .= " and ug.ug_cpf like '%" . $tf_u_cpf . "%' ";
if($ug_login) 		$sql .= " and upper(ug.ug_login) like '%" . strtoupper($ug_login) . "%' ";
if($tf_u_sexo) 		$sql .= " and upper(ug.ug_sexo) = '" . strtoupper($tf_u_sexo) . "' ";
if($tf_u_data_nascimento_ini && $tf_u_data_nascimento_fim) 			$sql .= " and ug.ug_data_nascimento between '".formata_data($tf_u_data_nascimento_ini,1)."' and '".formata_data($tf_u_data_nascimento_fim,1)."'";
if($tf_u_tel_ddi) 	$sql .= " and ug.ug_tel_ddi = '" . $tf_u_tel_ddi . "' ";
if($tf_u_tel_ddd) 	$sql .= " and ug.ug_tel_ddd = '" . $tf_u_tel_ddd . "' ";
if($tf_u_tel) 		$sql .= " and ug.ug_tel like '%" . $tf_u_tel . "%' ";
if($tf_u_cel_ddi) 	$sql .= " and ug.ug_cel_ddi = '" . $tf_u_cel_ddi . "' ";
if($tf_u_cel_ddd) 	$sql .= " and ug.ug_cel_ddd = '" . $tf_u_cel_ddd . "' ";
if($tf_u_cel) 		$sql .= " and ug.ug_cel like '%" . $tf_u_cel . "%' ";
if($tf_u_endereco) 	$sql .= " and upper(ug.ug_endereco) like '%" . strtoupper($tf_u_endereco) . "%' ";
if($tf_u_bairro)	$sql .= " and lower(ug.ug_bairro) like '%" . strtolower($tf_u_bairro) . "%' ";
if($tf_u_cidade)	$sql .= " and lower(ug.ug_cidade) like '%" . strtolower($tf_u_cidade) . "%' ";
if($tf_u_cep)		$sql .= " and ug.ug_cep like '%" . $tf_u_cep . "%' ";
if($tf_u_estado)	$sql .= " and upper(ug.ug_estado) = '" . strtoupper($tf_u_estado) . "' ";
if($ug_flag_usando_saldo) $sql .= " and ug.ug_flag_usando_saldo = " . $ug_flag_usando_saldo . " ";
if($ug_cadastro_completo == "1") $sql .= "and (ug.ug_cpf IS NOT NULL AND trim(ug.ug_cpf) <> '')" 
                                . "and (upper(ug.ug_nome) IS NOT NULL AND trim(ug.ug_nome) <> '') "
                                . "and (ug.ug_data_nascimento IS NOT NULL) "
                                . "and (ug.ug_nome_da_mae IS NOT NULL AND trim(ug.ug_nome_da_mae) <> '') "
                                . "and (ug.ug_cel IS NOT NULL AND trim(ug.ug_cel) <> '') "
                                . "and (ug.ug_cel_ddd IS NOT NULL AND trim(ug.ug_cel_ddd) <> '') "
                                . "and (ug.ug_nome_cpf IS NOT NULL AND trim(ug.ug_nome_cpf) <> '') "
                                . "and (upper(ug.ug_endereco) IS NOT NULL AND trim(ug.ug_endereco) <> '') "
                                . "and (ug.ug_numero IS NOT NULL AND trim(ug.ug_numero) <> '') "
                                . "and (lower(ug.ug_bairro) IS NOT NULL AND trim(ug.ug_bairro) <> '') "
                                . "and (ug.ug_cep IS NOT NULL AND trim(ug.ug_cep) <> '') "
                                . "and (lower(ug.ug_cidade) IS NOT NULL AND trim(ug.ug_cidade) <> '') "
                                . "and (upper(ug.ug_estado) IS NOT NULL AND trim(ug.ug_estado) <> '')";
if($ug_cadastro_completo == "2") $sql .= "and ((ug.ug_cpf IS NULL OR trim(ug.ug_cpf) = '')" 
                                . "OR (upper(ug.ug_nome) IS NULL OR trim(ug.ug_nome) = '') "
                                . "OR (ug.ug_data_nascimento IS NULL) "
                                . "OR (ug.ug_nome_da_mae IS NULL OR trim(ug.ug_nome_da_mae) = '') "
                                . "OR (ug.ug_cel IS NULL OR trim(ug.ug_cel) = '') "
                                . "OR (ug.ug_cel_ddd IS NULL OR trim(ug.ug_cel_ddd) = '') "
                                . "OR (ug.ug_nome_cpf IS NULL OR trim(ug.ug_nome_cpf) = '') "
                                . "OR (upper(ug.ug_endereco) IS NULL OR trim(ug.ug_endereco) = '') "
                                . "OR (ug.ug_numero IS NULL OR trim(ug.ug_numero) = '') "
                                . "OR (lower(ug.ug_bairro) IS NULL OR trim(ug.ug_bairro) = '') "
                                . "OR (ug.ug_cep IS NULL OR trim(ug.ug_cep) = '') "
                                . "OR (lower(ug.ug_cidade) IS NULL OR trim(ug.ug_cidade) = '') "
                                . "OR (upper(ug.ug_estado) IS NULL OR trim(ug.ug_estado) = ''))";
if($tf_u_news)	{
	if($tf_u_news=="n")	{
		$sql .= " and (upper(ug.ug_news) = '" . strtoupper($tf_u_news) . "' or ug.ug_news = ' ' or ug.ug_news = '') ";
	} else	{
		$sql .= " and upper(ug.ug_news) = '" . strtoupper($tf_u_news) . "' ";
	}
}

if($tf_u_endereco_ip) {
	$sql .= " and EXISTS (
        SELECT 1 
        FROM usuarios_games_log 
        WHERE ugl_ug_id = ug.ug_id 
          AND ugl_ip = '$tf_u_endereco_ip'
    ) ";
}

if($tf_u_observacoes) {
	$sql .= " and EXISTS (
        SELECT 1 
        FROM usuarios_games_obs obs
        WHERE obs.ug_id = ug.ug_id 
          AND obs.ug_obs ilike '%".trim($tf_u_observacoes)."%' 
    ) ";
}


if($tf_u_compet_aceito_regulamento) { 
	if(strtolower($tf_u_compet_aceito_regulamento)=="s") {
		$sql .= " and lower(ug.ug_compet_aceito_regulamento) = 's' ";
		if($tf_u_compet_jogo==1) { 
			$sql .= " and ug.ug_compet_jogo = 1 ";
		} elseif($tf_u_compet_jogo==2) { 
			$sql .= " and ug.ug_compet_jogo = 2 ";
		}

	} if(strtolower($tf_u_compet_aceito_regulamento)!="s") {
		$sql .= " and not (lower(ug.ug_compet_aceito_regulamento) = 's') ";
	}
}
if($tf_u_integracao_origem) { 
	if($tf_u_integracao_origem=="-1") { 
		$sql .= " and not (ug.ug_integracao_origem = '') ";
	} elseif($tf_u_integracao_origem=="-2") { 
		$sql .= " and (ug.ug_integracao_origem = '') ";
	} else {
		$sql .= " and (ug.ug_integracao_origem = '$tf_u_integracao_origem') ";
	}
}
if($tf_u_habilitado_cielo) {
	if($tf_u_habilitado_cielo==1) {
		$sql .= " and ug.ug_use_cielo = 1 ";
	} elseif($tf_u_habilitado_cielo==-1) {
		$sql .= " and ug.ug_use_cielo = 0 ";
	}
}


if($tf_u_usuario_vip) {
	if(is_array($a_lista_usuarios_VIP) && (count($a_lista_usuarios_VIP)>0)) {
		$s_list_vip = implode(",", $a_lista_usuarios_VIP);
		if($tf_u_usuario_vip==1) {
			$sql .= " and (ug_id in ($s_list_vip)) ";
		} elseif($tf_u_usuario_vip==-1) {
			$sql .= " and (not(ug_id in ($s_list_vip))) ";
		}
	}
}

if(b_IsUsuarioWagner()) {
//echo str_replace("\n", "<br>\n", $sql)."<br>";
}

if(empty($somenteContar)) {
    $rs_usuario = SQLexecuteQuery($sql);
} // end if(empty($somenteContar))
?>