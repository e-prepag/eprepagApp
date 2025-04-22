<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
$sql  = "select *, (SELECT vg_data_inclusao
     FROM tb_dist_venda_games 
     WHERE vg_ug_id = ug.ug_id and vg_ultimo_status = 5
     ORDER BY ug_data_inclusao DESC 
     LIMIT 1) AS ultima_data_compra
		 from dist_usuarios_games ug\n";
if($tf_u_com_totais_vendas ) {	//&& $dd_opr_codigo
	$sql  .= "left outer join (
				select vg_ug_id, sum(vgm.vgm_valor * vgm.vgm_qtde) as vg_valor, sum(vgm.vgm_qtde) as vg_qtde_itens, min(vg_data_inclusao) as vg_data_primeira_venda, max(vg_data_inclusao) as vg_data_ultima_venda 
				from tb_dist_venda_games vg 
					inner join tb_dist_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id 		
				where vg_ultimo_status=5 $produtos_query
				group by vg_ug_id
			) v	on v.vg_ug_id = ug.ug_id \n";
}
//if($tf_ug_te_id_ativo === '0'||$tf_ug_te_id_ativo === '1')	
$sql .= " left outer join tb_tipo_estabelecimento te ON (te.te_id = ug.ug_te_id) \n";

$sql  .= "where 1=1 ";
if($tf_u_codigo)		$sql .= " and ug.ug_id in (" . $tf_u_codigo . ") ";
if($tf_u_status)		$sql .= " and ug.ug_ativo = " . $tf_u_status . " ";
// LIVRODJX if($tf_u_telefone)      $sql .= " and ug.ug_telefone = " . $tf_u_telefone . "" ;
if($tf_u_status_busca){
	if($tf_u_status_busca==0 || $tf_u_status_busca==1 || $tf_u_status_busca==2) {
		$sql .= " and ug.ug_status = " . $tf_u_status_busca . " ";
	}
}
if($tf_u_substatus ) {	//|| $tf_u_substatus==0 
	if($tf_u_substatus=="v") 
		$sql .= " and (ug.ug_substatus is null orug.ug_substatus=0)  ";
	else
		$sql .= " and ug.ug_substatus = ".$tf_u_substatus." ";
}
// Se tem operadora selecionada emtão quer dizer que tem valor e por isso vamos escolher só os que estão com valor >0
if($produtos_query) {
	$sql .= " and coalesce(vg_valor, 0)>0 ";
}

if($tf_u_qtde_acessos_ini && $tf_u_qtde_acessos_fim) 			$sql .= " and ug.ug_qtde_acessos between " . ($tf_u_qtde_acessos_ini==-1?0:$tf_u_qtde_acessos_ini) ." and " . ($tf_u_qtde_acessos_fim==-1?0:$tf_u_qtde_acessos_fim);
if($tf_u_data_ultimo_acesso_ini && $tf_u_data_ultimo_acesso_fim)$sql .= " and ug.ug_data_ultimo_acesso >= '".formata_data($tf_u_data_ultimo_acesso_ini,1)." 00:00:00' and ug.ug_data_ultimo_acesso <= '".formata_data($tf_u_data_ultimo_acesso_fim,1)." 23:59:59'";
if($tf_u_data_inclusao_ini && $tf_u_data_inclusao_fim) 			$sql .= " and ug.ug_data_inclusao >= '".formata_data($tf_u_data_inclusao_ini,1)." 00:00:00' and ug.ug_data_inclusao <='".formata_data($tf_u_data_inclusao_fim,1)." 23:59:59'";
if($ug_data_expiracao_senha_ini && $ug_data_expiracao_senha_fim) 			$sql .= " and ug.ug_data_expiracao_senha >= '".formata_data($ug_data_expiracao_senha_ini,1)." 00:00:00' and ug.ug_data_expiracao_senha <='".formata_data($ug_data_expiracao_senha_fim,1)." 23:59:59'";
if($tf_u_login) 		$sql .= " and upper(ug.ug_login) like '%" . str_replace("'", "''", trim(strtoupper($tf_u_login))) . "%' ";

if($tf_u_nome_fantasia) $sql .= " and upper(ug.ug_nome_fantasia) like '%" . str_replace("'", "''", trim(strtoupper($tf_u_nome_fantasia))) . "%' ";
if($tf_u_razao_social) 	$sql .= " and upper(ug.ug_razao_social) like '%" . str_replace("'", "''", trim(strtoupper($tf_u_razao_social))) . "%' ";
if($tf_u_cnpj) 			$sql .= " and ug.ug_cnpj like '%" . trim($tf_u_cnpj) . "%' ";
//tipo estabelecimento
if($tf_ug_te_id)		$sql .= " and ug.ug_te_id = " . $tf_ug_te_id . " ";
if($tf_ug_te_id_ativo === '0'||$tf_ug_te_id_ativo === '1')	$sql .= " and te.te_ativo = " . $tf_ug_te_id_ativo. " ";

if($tf_u_responsavel) 	$sql .= " and upper(ug.ug_responsavel) like '%" . str_replace("'", "''", trim(strtoupper($tf_u_responsavel))) . "%' ";
if($tf_u_email)			$sql .= " and upper(ug.ug_email) like '%" . str_replace("'", "''", trim(strtoupper($tf_u_email))) . "%' ";
if($tf_u_site)			$sql .= " and upper(ug.ug_site) like '%" . str_replace("'", "''", trim(strtoupper($tf_u_site))) . "%' ";

if($tf_u_tipo_cadastro) $sql .= " and upper(ug.ug_tipo_cadastro) = '" . str_replace("'", "''", strtoupper($tf_u_tipo_cadastro)) . "' ";
if($tf_u_nome) 		$sql .= " and upper(ug.ug_nome) like '%" . str_replace("'", "''", trim(strtoupper($tf_u_nome))) . "%' ";
if($tf_u_cpf) 		$sql .= " and (ug.ug_cpf like '%" . trim($tf_u_cpf) . "%' or  ug.ug_repr_legal_cpf like '%" . trim($tf_u_cpf) . "%' or  ug.ug_repr_venda_cpf like '%" . trim($tf_u_cpf) . "%')";
if($tf_u_rg) 		$sql .= " and ug.ug_rg like '%" . trim($tf_u_rg) . "%' ";
if($tf_u_sexo) 		$sql .= " and upper(ug.ug_sexo) = '" . strtoupper($tf_u_sexo) . "' ";
if($tf_u_data_nascimento_ini && $tf_u_data_nascimento_fim) 			$sql .= " and ug.ug_data_nascimento between '".formata_data($tf_u_data_nascimento_ini,1)."' and '".formata_data($tf_u_data_nascimento_fim,1)."'";

if($tf_u_endereco) 	$sql .= " and upper(ug.ug_endereco) like '%" . str_replace("'", "''", trim(strtoupper($tf_u_endereco))) . "%' ";
if($tf_u_bairro)	$sql .= " and lower(ug.ug_bairro) like '%" . str_replace("'", "''", trim(strtolower($tf_u_bairro))) . "%' ";
if($tf_u_cidade)	$sql .= " and lower(ug.ug_cidade) like '%" . str_replace("'", "''", trim(strtolower($tf_u_cidade))) . "%' ";
if($tf_u_cep)		$sql .= " and ug.ug_cep like '%" . $tf_u_cep . "%' ";
if($tf_u_estado)	$sql .= " and upper(ug.ug_estado) = '" . str_replace("'", "''", strtoupper($tf_u_estado)) . "' ";

if($tf_u_tel_ddi) 	$sql .= " and ug.ug_tel_ddi = '" . $tf_u_tel_ddi . "' ";
if($tf_u_tel_ddd) 	$sql .= " and ug.ug_tel_ddd = '" . $tf_u_tel_ddd . "' ";
if($tf_u_tel) 		$sql .= " and ug.ug_tel like '%" . $tf_u_tel . "%' ";
if($tf_u_cel_ddi) 	$sql .= " and ug.ug_cel_ddi = '" . $tf_u_cel_ddi . "' ";
if($tf_u_cel_ddd) 	$sql .= " and ug.ug_cel_ddd = '" . $tf_u_cel_ddd . "' ";
if($tf_u_cel) 		$sql .= " and ug.ug_cel like '%" . $tf_u_cel . "%' ";
if($tf_u_fax_ddi) 	$sql .= " and ug.ug_fax_ddi = '" . $tf_u_fax_ddi . "' ";
if($tf_u_fax_ddd) 	$sql .= " and ug.ug_fax_ddd = '" . $tf_u_fax_ddd . "' ";
if($tf_u_fax) 		$sql .= " and ug.ug_fax like '%" . $tf_u_fax . "%' ";

if($tf_u_endereco_ip) {
	$sql .= " and EXISTS (
        SELECT 1 
        FROM dist_usuarios_games_log 
        WHERE ugl_ug_id = ug.ug_id 
          AND ugl_ip = '$tf_u_endereco_ip'
    ) ";
}

if($tf_u_observacoes) {
	$sql .= " and EXISTS (
        SELECT 1 
        FROM dist_usuarios_games_obs obs
        WHERE obs.ug_id = ug.ug_id 
          AND obs.ug_obs ilike '%".trim($tf_u_observacoes)."%' 
    ) ";
}

if($tf_u_ra_codigo)	$sql .= " and upper(ug.ug_ra_codigo) = '" . str_replace("'", "''", strtoupper($tf_u_ra_codigo)) . "' ";
if($tf_u_ra_outros)	$sql .= " and upper(ug.ug_ra_outros) like '%" . str_replace("'", "''", strtoupper($tf_u_ra_outros)) . "%' ";

if($tf_u_contato01_nome) 	$sql .= " and upper(ug.ug_contato01_nome) like '%" . str_replace("'", "''", strtoupper($tf_u_contato01_nome)) . "%' ";
if($tf_u_contato01_cargo) 	$sql .= " and upper(ug.ug_contato01_cargo) like '%" . str_replace("'", "''", strtoupper($tf_u_contato01_cargo)) . "%' ";
if($tf_u_contato01_tel_ddi) $sql .= " and ug.ug_contato01_tel_ddi = '" . $tf_u_contato01_tel_ddi . "' ";
if($tf_u_contato01_tel_ddd) $sql .= " and ug.ug_contato01_tel_ddd = '" . $tf_u_contato01_tel_ddd . "' ";
if($tf_u_contato01_tel) 	$sql .= " and ug.ug_contato01_tel like '%" . $tf_u_contato01_tel . "%' ";

//if($tf_u_observacoes) 	$sql .= " and ug.ug_observacoes like '%" . $tf_u_observacoes . "%' ";


$classif_new = (isset($tf_decode) && $tf_decode == false)?$tf_u_risco_classif:utf8_decode($tf_u_risco_classif);
if($tf_u_risco_classif) 	$sql .= " and ug.ug_risco_classif =" . $RISCO_CLASSIFICACAO[$classif_new] . " ";

//echo $RISCO_CLASSIFICACAO[utf8_decode($tf_u_risco_classif)];

if(strtoupper($tf_u_saldo_positivo)=="P") 	$sql .= " and ug.ug_perfil_saldo>0 ";
elseif(strtoupper($tf_u_saldo_positivo)=="N") 	$sql .= " and ug.ug_perfil_saldo<0 ";

if($tf_u_usuarios_cartao) {
	$sql .= " and ug.ug_usuario_cartao =" . $tf_u_usuarios_cartao . " ";
	if($tf_u_usuarios_novos) {	
		$tf_u_usuarios_novos = NULL;	
	}
}
if($tf_u_usuarios_novos) {	
	if($tf_u_usuarios_novos==1) {	
		$sql .= " and ug.ug_usuario_novo =1 ";
	} elseif($tf_u_usuarios_novos==2){
		$sql .= " and ug.ug_usuario_novo =2 ";
	} elseif($tf_u_usuarios_novos==3){
		$sql .= " and ug.ug_usuario_novo =1 and ug.ug_usuario_cartao=0 ";
	} elseif($tf_u_usuarios_novos==4){
		$sql .= " and ug.ug_usuario_novo =0 ";
	}
}

if($tf_u_origem_cadastro) 	$sql .= " and ug.ug_ficou_sabendo='" . $tf_u_origem_cadastro . "' ";

if($tf_u_computadores_qtde && ($tf_u_computadores_qtde!="Todos")) {
	$sql .= " and ug.ug_computadores_qtde =" . $tf_u_computadores_qtde . " ";
}

if($tf_u_fatura_media_mensal && ($tf_u_fatura_media_mensal!="Todos")) {
	$sql .= " and ug.ug_fatura_media_mensal =" . $tf_u_fatura_media_mensal . " ";
}

if($tf_u_compet_participa) { 
	if(strtolower($tf_u_compet_participa)=="s") {
		$sql .= " and lower(ug.ug_compet_participa) = 's' ";
	} if(strtolower($tf_u_compet_participa)!="s") {
		$sql .= " and not (lower(ug.ug_compet_participa) = 's') ";
	}
}

if(!empty($ug_ongame)) { 
	if(strtolower($ug_ongame)=="s") {
		$sql .= " and lower(ug.ug_ongame) = 's' ";
	} else {
		$sql .= " and not (lower(ug.ug_ongame) = 's') ";
	}
}

if(!empty($tf_gmaps)) {
	if($tf_gmaps =="L") {
		$sql .= " and ((not ug.ug_coord_lat = 0) and (not ug.ug_coord_lng = 0))  ";
	} else {
		if($tf_gmaps <0) {
			$sql .= " and ug.ug_coord_lat = 0 and ug.ug_coord_lng = 0 ";
		} elseif($tf_gmaps>0) {
//			$sql .= " and ug.ug_google_maps_status = '" . $tf_gmaps . "' ";
			if($tf_gmaps=="1") {
				$sql .= " and (ug.ug_coord_lat = 0 and ug.ug_coord_lng = 0)  ";
			} elseif($tf_gmaps=="2") { 
				$sql .= " and ug.ug_google_maps_status = '2' ";
			}
		}
	}
}

// Tipo de Venda
if ( $tf_u_tipo_venda ) {
    $sql .= " AND ug.ug_tipo_venda='$tf_u_tipo_venda' ";
}

// NexCafe
if($tf_u_login_nexcafe)	$sql .= " and upper(ug.ug_id_nexcafe) like '%" . str_replace("'", "''", strtoupper($tf_u_login_nexcafe)) . "%' ";				
if($tf_u_login_automatico_nexcafe) $sql .= " and ug.ug_login_nexcafe_auto=".$tf_u_login_automatico_nexcafe." ";			
if($tf_u_data_adesao_nexcafe_ini && $tf_u_data_adesao_nexcafe_fim) 
	$sql .= " and ug.ug_data_inclusao_nexcafe between '".formata_data($tf_u_data_adesao_nexcafe_ini,1)."' and '".formata_data($tf_u_data_adesao_nexcafe_fim,1)."'";
if(is_numeric($tf_u_vip)) $sql .= " and ug.ug_vip=".$tf_u_vip." ";	
if(is_numeric($tf_ug_possui_restricao_produtos)) $sql .= " and ug.ug_possui_restricao_produtos=".$tf_ug_possui_restricao_produtos." ";	


if($tf_u_data_aprovacao_ini && $tf_u_data_aprovacao_fim)	$sql .= " and ug.ug_data_aprovacao >= '".formata_data($tf_u_data_aprovacao_ini,1)." 00:00:00' and ug.ug_data_aprovacao <= '".formata_data($tf_u_data_aprovacao_fim,1)." 23:59:59'";

if(!empty($isAjax)) {
	//echo $sql;
	$rs_usuario = SQLexecuteQuery(utf8_decode($sql));
}
else {
	$rs_usuario = SQLexecuteQuery($sql);
}

?>