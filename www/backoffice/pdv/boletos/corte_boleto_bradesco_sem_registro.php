<?php
ob_start();
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/pdv/corte_constantes.php";
require_once $raiz_do_projeto."banco/boletos/include/funcoes_bradesco.php";

$tf_u_codigo = $_GET['ug_id'];
$bbc_boleto_cod = $_GET['bbc_boleto_codigo'];

//Validacao
//------------------------------------------------------------------------------------------------------------------
$msg = "";
$msgFatal = "";
$str_redirect = "";

//Valida estabelecimento
if($msg == "" && $msgFatal == "")
    if(!$tf_u_codigo || !is_numeric($tf_u_codigo) || trim($tf_u_codigo) == "") $msgFatal = "Código do usuário inválido.\n";

//Valida codigo do boleto
if($msg == ""){
    if(!$bbc_boleto_cod || trim($bbc_boleto_cod) == "" || !is_numeric($bbc_boleto_cod)) $msg = "Código do boleto inválido.\n";
}

//Busca dados do boleto
if($msg == ""){
    $sql = "select * from boleto_bancario_cortes bbc
                inner join cortes c on c.cor_codigo = bbc.bbc_cor_codigo
                where bbc.bbc_boleto_codigo = $bbc_boleto_cod
                and bbc.bbc_ug_id = $tf_u_codigo";
    $rs_boleto = SQLexecuteQuery($sql);
    if(!$rs_boleto || pg_num_rows($rs_boleto) == 0) $msg = "Erro ao buscar boleto.\n";
    else {
        $rs_boleto_row = pg_fetch_array($rs_boleto);
        $bbc_bco_codigo = $rs_boleto_row['bbc_bco_codigo'];
              
        //Gambiarra para aparecer o logo do banco no boleto imagem
        $bbg_bco_codigo = $bbc_bco_codigo;
                
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
                $ug_cpf 		= $rs_estab_row['ug_cpf'];
                $ug_cnpj		= $rs_estab_row['ug_cnpj'];
                $ug_nome 		= $rs_estab_row['ug_nome'];
                $ug_tipo_end = $rs_estab_row['ug_tipo_end'];
                $ug_endereco = $ug_tipo_end.": ".$rs_estab_row['ug_endereco'];
                $ug_endereco_logradouro = $ug_endereco;
                $numero 		= $rs_estab_row['ug_numero'];
                $ug_numero 		= $numero;
                if(trim($numero) != "") $ug_endereco .= ", " . trim($numero);
                $complemento	= $rs_estab_row['ug_complemento'];
                $ug_complemento		= $complemento;
                if(trim($complemento) != "") $ug_endereco .= " - " . trim($complemento);
                $bairro 		= $rs_estab_row['ug_bairro'];
                $ug_bairro 		= $bairro;
                $municipio 		= $rs_estab_row['ug_cidade'];
                $ug_cidade 		= $municipio;
                if(trim($bairro) != "") $bairro .= " - " . trim($municipio);
                $uf 			= $rs_estab_row['ug_estado'];
                $ug_estado		= $uf;
                $cep 			= str_replace("-","",$rs_estab_row['ug_cep']);
                $ug_cep = $cep;
                $mask = $cep;
                $var1 = substr("$mask", 0,5);
                $var2 = substr("$mask", 5,8);  
                $cep = $var1."-".$var2;
                
//                 o bloco abaixo serve para se adequar a nova norma da febraban de boleto registrado
                if($rs_estab_row['ug_tipo_cadastro'] == "PF")
                {
                    $rs_estab_row['ug_cpf'] = str_replace(array(".", ","), "",$rs_estab_row['ug_cpf']);
                    $sacado = $rs_estab_row['ug_nome']." - CPF: ".mascara_cnpj_cpf($rs_estab_row['ug_cpf'],"cpf");
                    $dadosboleto["tipo_documento"] = "1";
                    $dadosboleto["nome_pagador"] = $ug_nome;
                    $dadosboleto["sacado"] = $ug_nome;
                }
                else
                {
                    $rs_estab_row['ug_cnpj'] = str_replace(array(".", ","), "",$rs_estab_row['ug_cnpj']);
                    if(!empty($ug_razao_social)){
                        $sacado = $rs_estab_row['ug_razao_social']." - CNPJ: ".mascara_cnpj_cpf($rs_estab_row['ug_cnpj'],"cnpj");
                    } else{
                        if(!empty($ug_nome_fantasia)){
                            $sacado = $rs_estab_row['ug_nome_fantasia']." - CNPJ: ".mascara_cnpj_cpf($rs_estab_row['ug_cnpj'],"cnpj");
                        } else {
                            $sacado = $rs_estab_row['ug_nome']." - CNPJ: ".mascara_cnpj_cpf($rs_estab_row['ug_cnpj'],"cnpj");
                        }

                    }

                }
        }
}
//loga acesso
if($msg == ""){
    $sql = "insert into boleto_bancario_cortes_acessos(bbca_data_inclusao, bbca_ip,	bbca_ug_id, bbca_bbc_boleto_codigo) values (";
    $sql .= "CURRENT_TIMESTAMP,'" . $_SERVER["REMOTE_ADDR"] . "', $tf_u_codigo, $bbc_boleto_codigo)";
    $ret = SQLexecuteQuery($sql);
    if(!$ret) $msg = "";
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
    require_once $raiz_do_projeto . "banco/boletos/include/funcoes_bradesco_fixo_corte.php";

    ob_clean();
    require_once $raiz_do_projeto . "banco/boletos/include/boleto_to_image/boleto_imagem.php";
}
	
?>
