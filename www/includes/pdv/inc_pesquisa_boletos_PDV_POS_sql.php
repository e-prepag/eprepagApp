<?php
$sql = "select ug.ug_id, ug.ug_email, ug.ug_responsavel, ug.ug_cnpj, ug.ug_nome_fantasia,ug.ug_cpf, bbc.bbc_boleto_codigo, c.cor_venda_bruta, c.cor_venda_liquida, c.cor_periodo_ini, c.cor_periodo_fim, bbc.bbc_data_inclusao, bbc.bbc_cor_codigo from boleto_bancario_cortes  bbc\n";
$sql .= "INNER JOIN cortes c ON c.cor_codigo = bbc.bbc_cor_codigo\n";
$sql .= "INNER JOIN dist_usuarios_games ug ON ug.ug_id = c.cor_ug_id\n";
$sql  .= "where ug.ug_risco_classif = 1 ";

//id usuário
if($tf_u_codigo)		$sql .= " and ug.ug_id in (" . $tf_u_codigo . ") ";
//inicio corte/fim corte
if($tf_cor_periodo_ini && $tf_cor_periodo_fim) 			$sql .= " and c.cor_periodo_ini >= '".formata_data($tf_cor_periodo_ini,1)." 00:00:00' and c.cor_periodo_fim <='".formata_data($tf_cor_periodo_fim,1)." 23:59:59'";
//cnpj
if($tf_u_cnpj) 			$sql .= " and (ug.ug_cnpj like '%" . $tf_u_cnpj . "%' or ug.ug_cpf like '%" . $tf_u_cnpj . "%') ";
//email
if($tf_u_email)         $sql .= " and ug.ug_email ilike '%" . $tf_u_email . "%'";
//responsavel
if($tf_u_responsavel) 	$sql .= " and upper(ug.ug_responsavel) ilike '%" . strtoupper($tf_u_responsavel) . "%' ";
//valor bruto
if($tf_c_valor){
    $sql .= " and c.cor_venda_bruta = " . str_replace (',', '.', $tf_c_valor). " ";
    $sql .= "::REAL";               //necessario para comparações de dados tipo REAL
}
//valor liquido
if($tf_c_repasse){
    $sql .= " and c.cor_venda_liquida = " . str_replace (',', '.', $tf_c_repasse). "  ";
    $sql .= "::REAL";               //necessario para comparações de dados tipo REAL
}
//data inclusao
if($tf_data_inclusao_ini && $tf_data_inclusao_ini)      $sql .= " and bbc.bbc_data_inclusao >= '".formata_data($tf_data_inclusao_ini,1)." 00:00:00' and bbc.bbc_data_inclusao <='".formata_data($tf_data_inclusao_fim,1)." 23:59:59'";

if(!empty($isAjax)) {
	$rs_boleto_pos = SQLexecuteQuery(utf8_decode($sql));
}
else {
	$rs_boleto_pos = SQLexecuteQuery($sql);
}
?>