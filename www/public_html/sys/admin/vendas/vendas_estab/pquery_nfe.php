<?php 
error_reporting(E_ALL); 
ini_set("display_errors", 1);
session_start();
if (isset($_POST['exportar_excel'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=exportacao_vendas.csv');

    $output = fopen('php://output', 'w');

   fputcsv($output, [
    'Data da Operação',
    'Nome do Consumidor',
    'Canal Usuário - Nome do Comprador',
    'Canal PDV - Nome do Comprador Informado pelo PDV',
    'Valor de Venda ao Consumidor',
    'Endereço',
    'Bairro',
    'Cidade',
    'CEP'
]);

    if (!isset($_SESSION['sqldata'])) {
        die("Nenhuma consulta disponível para exportação.");
    }

    $sqlExport = $_SESSION['sqldata'];
    $resExport = SQLexecuteQuery($sqlExport);

    while ($row = pg_fetch_assoc($resExport)) {
        $valorVenda = $row['trn_valor'];
        if ($dd_operadora == 13) {
            $valorVenda *= (1 - $row['trn_comissao']);
        }

        fputcsv($output, [
            substr($row['trn_data'], 0, 19),
            $row['trn_nome'],
            $row['canal'],
            $row['opr_nome'],
            number_format($valorVenda, 2, ',', '.')
        ]);
    }

    fclose($output);
    exit; // <--- ESSENCIAL para parar o restante da pÃ¡gina
}
require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

set_time_limit ( 30000 ) ;
$seg_auxilar = 0;
$pos_pagina = $seg_auxilar;
$qtde_reg_tela = 100;

require_once "nfesp_lote.php";
require_once $raiz_do_projeto . "public_html/sys/admin/stats/inc_Comissoes.php";

$time_start = getmicrotime();
$bDebug = false;

if($bDebug) {
echo "dd_operadora: ".$dd_operadora."<br>";
echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempostart: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
}

if($_SESSION["tipo_acesso_pub"]=='PU') {
        $dd_operadora = $_SESSION["opr_codigo_pub"];
        $dd_mode = "S";
        $Submit = "Buscar";
}

// Para todos
$dd_mode='S';
if(!$dd_mode || ($dd_mode!='V')) {
        $dd_mode = "S";
}

$where_mode_data = "vg.vg_data_concilia";

if(!$ncamp)            $ncamp           = 'trn_data';
if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');
if(!$inicial)          $inicial         = 0;
if(!$range)            $range           = 1;
if(!$ordem)            $ordem           = 1;
if($BtnSearch && $BtnSearch!=1 ) {
        $inicial     = 0;
        $range       = 1;
        $total_table = 0;
}

$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
$data_inicial_limite = '01/08/2004';
$FrmEnviar = 1;
	
if($bDebug) {
echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo0: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
//die("Stop");
}
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "/sys/imagens/proxima.gif";
	$img_anterior = "/sys/imagens/anterior.gif";
	$max          = $qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

if($bDebug) {
echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo0: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
//die("Stop");
}

if($cb_opr_teste)
        $resopr = SQLexecuteQuery("select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') order by opr_ordem");
else
        $resopr = SQLexecuteQuery("select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') and (opr_codigo <> ".$opr_teste.") order by opr_ordem");

if(!$dd_operadora) {
        $dd_operadora = 16;
}

if($bDebug) {
    echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo0: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
}

if($dd_operadora) {			
        $sqltmp = "select opr_codigo, opr_nome, opr_pin_online from operadoras where opr_codigo=".$dd_operadora."";
        $res_opr_info = SQLexecuteQuery($sqltmp);
        $pg_opr_info = pg_fetch_array($res_opr_info);

        if($bDebug) {
        echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo01: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
        }

        $dd_operadora_nome = $pg_opr_info['opr_nome'];

        if($pg_opr_info['opr_pin_online'] == 0) {
                $sqltmp = "select pin_valor as valor from pins where opr_codigo='".$pg_opr_info['opr_codigo']."' group by pin_valor order by pin_valor"; 
                $resval = SQLexecuteQuery($sqltmp);
        } else {
                $sqltmp = "select valor_fixo as valor from pin_valor_lista t0, pin_valor_fixo t1 where t0.valor_lista_cod = t1.valor_lista_cod and opr_codigo = ".$pg_opr_info['opr_codigo']." group by valor_fixo order by valor_fixo";
                $resval = SQLexecuteQuery($sqltmp);
        }
}

if($bDebug) {
    echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo02: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
}

if(!verifica_data($tf_data_inicial)) {
        $data_inic_invalida = true;
        $FrmEnviar = 0;
}

if(!verifica_data($tf_data_final)) {
        $data_fim_invalida = true;
        $FrmEnviar = 0;
}
if($bDebug) {
    echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo0: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
}
	
if(qtde_dias($data_inicial_limite, $tf_data_inicial) < 0) {
        $data_inicial_menor = true;
        $FrmEnviar = 0;
}

if($bDebug) {
    echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo0a: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
}

// Preenche os RPS_ID que estiverem faltando, apenas a partir de dez/2009
if($FrmPreencher && $_SESSION["tipo_acesso_pub"]=='AT') {

        $where_data_1a = "";
        $where_data_1b = "";
        $where_data_2 = "";
        $where_data_3 = "";
        $where_valor_1 = "";
        $where_valor_2 = "";
        $where_valor_3 = "";
        $where_opr_1 = "";
        $where_opr_2 = "";
        $where_opr_3 = " and false ";	// Cartões - Só funciona para Ongame
        $where_canal_lh = "";
        $where_canal_s = "";
        $where_canal_p = "";
        $where_canal_c = "";

        $where_data_1a .= " and (vg_data_inclusao >= '2010-04-01 00:00:00') "; 
        $where_data_1b .= " and ($where_mode_data >= '2010-04-01 00:00:00') "; 
        $where_data_2 .= " and (ve_data_inclusao >= '2010-04-01 00:00:00') "; 
        $where_data_3 .= " and (vc_data >= '2010-04-01 00:00:00') "; 

        if($dd_operadora) {
                $where_opr_1 = " and (vgm.vgm_opr_codigo = ".$dd_operadora.") ";
                if($dd_operadora_nome=='ONGAME') {
                        $where_opr_2 = " and (ve_jogo = 'OG') ";
                        $where_opr_3 = " and true ";	// Cartões - Só funciona para Ongame
                }
                elseif  ($dd_operadora_nome=='MU ONLINE') 
                        $where_opr_2 = " and (ve_jogo = 'MU') ";
                elseif  ($dd_operadora_nome=='HABBO HOTEL') 
                        $where_opr_2 = " and (ve_jogo = 'HB') ";
                else
                        $where_opr_2 = " and (ve_jogo = 'XX') ";
        }
        if($dd_operadora=="") $dd_valor = "";

        // Numeração de Nfe's começa em 01/dez/09
        $data_inicio_numeracao_nfe = "2009-12-01 00:00:00";
        if($dd_operadora==13)	// Ongame começa em 01/fev/10
                $data_inicio_numeracao_nfe = "2010-02-01 00:00:00";		
        if($dd_operadora==31)	// GPotato começa em 01/nov/10
                $data_inicio_numeracao_nfe = "2010-11-01 00:00:00";		

        // para todos
        $dd_valor = "";

        $where_canal_lh = " ";
        $where_canal_s = " ";
        $where_canal_p = " ";
        $where_canal_c = " ";

        // SQL para preencher
        $estat  = "select 
        trn_code, trn_data, opr_nome, valor, qtde_itens, qtde_produtos, canal, trn_nome, trn_cpf, trn_cpf_indicador, trn_vgm_id, trn_vgm_nfe_rps_id, 
        endereco,
    bairro, cidade, cep
                from (

                        select '1' as trn_code, vg.vg_data_inclusao as trn_data, t2.opr_nome, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, 
                        'Site LH' as canal, vgm.vgm_nome_cpf AS trn_nome, vgm.vgm_cpf AS trn_cpf, '3' as trn_cpf_indicador, vgm_id as trn_vgm_id, vgm_nfe_rps_id as trn_vgm_nfe_rps_id 
                       ,  ug.ug_endereco as endereco, ug.ug_numero as numero, ug.ug_bairro as bairro, ug.ug_cidade as cidade, ug.ug_cep as CEP
                        from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id , operadoras t2 
                        where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_opr_1." and vg.vg_data_inclusao>='".$data_inicio_numeracao_nfe."' and vg.vg_ultimo_status='5' ".$where_data_1a." ".$where_canal_lh." and vgm.vgm_nfe_rps_id<=0 
                        group by ug.ug_id, vgm.vgm_id, 
                                vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia, 
                                t2.opr_nome, vgm_nfe_rps_id 

                        union all

                        select '2' as trn_code, $where_mode_data as trn_data, t2.opr_nome, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, 
                        'Site '||(case when (vg_ug_id=7909) then 'ExpressMoney' else 'Money' end) as canal,  ug.ug_nome AS trn_nome,
    ug.ug_cpf AS trn_cpf, '3' as trn_cpf_indicador, vgm_id as trn_vgm_id, vgm_nfe_rps_id as trn_vgm_nfe_rps_id ,
      ug.ug_endereco as endereco, ug.ug_numero as numero, ug.ug_bairro as bairro, ug.ug_cidade as cidade, ug.ug_cep as CEP                
    from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                         INNER JOIN usuarios_games ug ON ug.ug_id = vg.vg_ug_id , operadoras t2 
                        where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." and $where_mode_data>='".$data_inicio_numeracao_nfe."' and vg.vg_ultimo_status='5' ".$where_data_1b." ".$where_canal_s." and vgm.vgm_nfe_rps_id<=0 
                        group by vgm.vgm_id, vg.vg_data_concilia, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia, 
                                t2.opr_nome, 
                                vg.vg_ug_id, vgm_nfe_rps_id 

                       

                         
                       

                ) v ";


        $estat .= " order by trn_data asc "; 

        $time_start_0 = getmicrotime();
        $n_trans = 0;
       
        $res_fill = pg_query($estat);

        //Gambiarra para resolver problema de sequencia de RPS para os dois publishers do Habboo Hotel
        $dd_operadora_anterior = $dd_operadora;
        if($dd_operadora == 125) $dd_operadora = 16;
        
        while($pg_fill = pg_fetch_array($res_fill)) {

                $n_trans++;

                $rps_id_max = 1;
                $s_table = (($pg_fill['trn_code']=='1') ? 'tb_dist_venda_games_modelo' : (($pg_fill['trn_code']=='2') ? 'tb_venda_games_modelo' : (($pg_fill['trn_code']=='3') ? 'dist_vendas_pos' : (($pg_fill['trn_code']=='4') ? 'dist_vendas_cartoes_tmp' : '????')) ));
                $s_v_id = (($pg_fill['trn_code']=='1') ? 'vgm_id' : (($pg_fill['trn_code']=='2') ? 'vgm_id' : (($pg_fill['trn_code']=='3') ? 've_id' : (($pg_fill['trn_code']=='4') ? 'vc_id' : '????')) ));


                $sql_update = "update ".$s_table." set vgm_nfe_rps_id = obtem_nfe_seq(".$dd_operadora.") where ".$s_v_id."=".$pg_fill['trn_vgm_id'].";";
                echo "<font face='Arial, Helvetica, sans-serif' size='1' color='#669933'>".$n_trans." - Venda de ".$pg_fill['canal']." (ID: ".$pg_fill['trn_vgm_id'].", data: ".$pg_fill['trn_data'].") RPS_ID: ".($rps_id_max)." <br>(".$sql_update.")<br></font>";

                $ret = SQLexecuteQuery($sql_update);
                if(!$ret) {
                        $msg_error = "Erro ao atualizar registro ($sql_update).\n";
                        echo $msg_error."<br>";
                }

                // Obtem o nfe_rps_id retornado por obtem_nfe_seq(opr_codigo)
                $sql_tmp = "select vgm_nfe_rps_id from ".$s_table." where ".$s_v_id."=".$pg_fill['trn_vgm_id'].";";
                $trn_vgm_nfe_rps_id = getValue($sql_tmp); 
                echo "<font face='Arial, Helvetica, sans-serif' size='1' color='#000099'> [nfe_rps_id: ".$trn_vgm_nfe_rps_id."] (".$pg_fill['trn_data'].", ".$pg_fill['canal'].") ".$sql_tmp."</font><br>";

                $time_end = getmicrotime();
                $time_each = $time_end - $time_start;
                if($_SESSION["tipo_acesso_pub"]=='AT') {
                        echo  " <font face='Arial, Helvetica, sans-serif' size='1' color='#FF6600'>Delay: ".number_format($time_each, 2, '.', '.')."s (total: ".number_format(($time_end - $time_start_0), 2, '.', '.')."s) (média de cada: ".number_format(($time_end - $time_start_0)/(($n_trans==0)?1:$n_trans), 2, '.', '.')."s)</font><br>";
                }
        }
       
        //Gambiarra para resolver problema de sequencia de RPS para os dois publishers do Habboo Hotel
        if($dd_operadora_anterior == 125) $dd_operadora = $dd_operadora_anterior;
        
        if($_SESSION["tipo_acesso_pub"]=='AT') {
                $time_end = getmicrotime();
                echo "<br>&nbsp;<font face='Arial, Helvetica, sans-serif' size='1' color='#0000CC'>Tempo total: ".number_format(($time_end - $time_start_0), 2, '.', '.')."s (média de cada: ".number_format(($time_end - $time_start_0)/(($n_trans==0)?1:$n_trans), 2, '.', '.')."s)</font><br>&nbsp;";
        }
}


if($FrmEnviar == 1) {

        $where_data_1a = "";
        $where_data_1b = "";
        $where_data_2 = "";
        $where_data_3 = "";
        $where_valor_1 = "";
        $where_valor_2 = "";
        $where_valor_3 = "";
        $where_opr_1 = "";
        $where_opr_2 = "";
        $where_opr_3 = " and false ";
        $where_canal_lh = "";
        $where_canal_s = "";
        $where_canal_p = "";
        $where_canal_c = "";

        if($tf_data_inicial && $tf_data_final) {
                $data_inic = formata_data(trim($tf_data_inicial), 1);
                $data_fim = formata_data(trim($tf_data_final), 1); 
                $where_data_1a = " and (vg_data_inclusao between '".trim($data_inic)." 00:00:00' and '".trim($data_fim)." 23:59:59') "; 
                $where_data_1b = " and (($where_mode_data >= '".trim($data_inic)." 00:00:00') and ($where_mode_data <= '".trim($data_fim)." 23:59:59')) "; 
                $where_data_2 = " and (ve_data_inclusao between '".trim($data_inic)." 00:00:00' and '".trim($data_fim)." 23:59:59') "; 
                $where_data_3 = " and (vc_data between '".trim($data_inic)." 00:00:00' and '".trim($data_fim)." 23:59:59') "; 
        }

        if($dd_operadora) {
                $where_opr_1 = " and (vgm.vgm_opr_codigo = ".$dd_operadora.") ";
                if($dd_operadora_nome=='ONGAME') {
                        $where_opr_2 = " and (ve_jogo = 'OG') ";
                        $where_opr_3 = " and true ";
                }
                elseif  ($dd_operadora_nome=='MU ONLINE') 
                        $where_opr_2 = " and (ve_jogo = 'MU') ";
                elseif  ($dd_operadora_nome=='HABBO HOTEL') 
                        $where_opr_2 = " and (ve_jogo = 'HB') ";
                else
                        $where_opr_2 = " and (ve_jogo = 'xx') ";
        }
        if($dd_operadora=="") $dd_valor = "";

        // para todos
        $dd_valor = "";

        if($dd_valor) {
                $where_valor_1 = " and (vgm.vgm_valor = ".$dd_valor.") ";
                $where_valor_2 = " and (ve_valor = ".$dd_valor.")";
                $where_valor_3 = " and ((vc_total_5k*13 + vc_total_10k*25 + vc_total_15k*37 + vc_total_20k*49) = ".$dd_valor.")";
        }
        
        if($dd_canal || $dd_canal == "") {
				
                switch($dd_canal) {
                        case "LH":
                                $where_canal_lh = " and true ";
                                $where_canal_s = " and false ";
                                $where_canal_p = " and false ";
                                $where_canal_c = " and false ";
								$where_canal_a = " and false ";
                                break;
                        case "Money":
                                $where_canal_lh = " and false ";
                                $where_canal_s = " and (not vg_ug_id=7909) ";
                                $where_canal_p = " and false ";
                                $where_canal_c = " and false ";
								$where_canal_a = " and vg_ultimo_status_obs not like '%AtimoPay%' and vg_http_referer_origem <> 'ATIMO' ";
                                break;
                        case "ExpressMoney":
                                $where_canal_lh = " and false ";
                                $where_canal_s = " and (vg_ug_id=7909) ";
                                $where_canal_p = " and false ";
                                $where_canal_c = " and false ";
								$where_canal_a = " and vg_ultimo_status_obs not like '%AtimoPay%' and vg_http_referer_origem <> 'ATIMO' ";
                                break;
                        case "SiteME":
                                $where_canal_lh = " and false ";
                                $where_canal_s = " and true ";
                                $where_canal_p = " and false ";
                                $where_canal_c = " and false ";
								$where_canal_a = " and vg_ultimo_status_obs not like '%AtimoPay%' and vg_http_referer_origem <> 'ATIMO' ";
                                break;
                        case "SiteMEL":
                                $where_canal_lh = " and true ";
                                $where_canal_s = " and true ";
                                $where_canal_p = " and false ";
                                $where_canal_c = " and false ";
								$where_canal_a = " and vg_ultimo_status_obs not like '%AtimoPay%' and vg_http_referer_origem <> 'ATIMO' ";
                                break;
                        case "POS":
                                $where_canal_lh = " and false ";
                                $where_canal_s = " and false ";
                                $where_canal_p = " and true ";
                                $where_canal_c = " and false ";
								$where_canal_a = " and false ";
                                break;
                        case "Cartões":
                                $where_canal_lh = " and false ";
                                $where_canal_s = " and false ";
                                $where_canal_p = " and false ";
                                $where_canal_c = " and true ";
								$where_canal_a = " and false ";
                                break;
						 case "ATIMO":
						        $ativa_atimo = true;
                                $where_canal_lh = " and false ";
                                $where_canal_s = " ";
                                $where_canal_p = " and false ";
                                $where_canal_c = " and false ";
								$where_canal_a = " and true and vg_ultimo_status_obs like '%AtimoPay%' and vg_http_referer_origem = 'ATIMO' ";
                                break;
                        default: 
						        //$ativa_atimo = true;
                                $where_canal_lh = " ";
                                $where_canal_s = " ";
                                $where_canal_p = " ";
                                $where_canal_c = " ";
								$where_canal_a = " ";
                                break;
                }
        }

        // SQL para listar
        $estat  = "select trn_code, trn_data, opr_nome, trn_valor, qtde_itens, qtde_produtos, canal, trn_nome, trn_cpf, trn_cpf_indicador, trn_vgm_id, trn_vgm_nfe_rps_id, trn_comissao 
      ,endereco, bairro, cidade, cep 
        from (

                        select '1' as trn_code, vg.vg_data_inclusao as trn_data, t2.opr_nome, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde) as trn_valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, 
                        'Site LH' as canal, vgm.vgm_nome_cpf as trn_nome,  vgm.vgm_cpf  as trn_cpf, '3' as trn_cpf_indicador, vgm_id as trn_vgm_id, vgm_nfe_rps_id as trn_vgm_nfe_rps_id, vgm_perc_desconto/100 as trn_comissao   
                         ,  
                         ug.ug_endereco as endereco, ug.ug_numero as numero, ug.ug_bairro as bairro, ug.ug_cidade as cidade, ug.ug_cep as CEP
                        from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id , operadoras t2 
                        where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." and vg.vg_data_inclusao>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' ".$where_data_1a." ".$where_canal_lh." 
                        group by ug.ug_id, vgm.vgm_nome_cpf, vgm.vgm_cpf, vgm.vgm_id, 
                                vg.vg_data_inclusao, vg.vg_ultimo_status, vg.vg_concilia, 
                                t2.opr_nome, vgm_nfe_rps_id, vgm.vgm_opr_codigo, vgm.vgm_perc_desconto ,
  ug.ug_endereco, ug.ug_numero, ug.ug_bairro, ug.ug_cidade, ug.ug_cep

                        union all

                        select '2' as trn_code, $where_mode_data as trn_data, t2.opr_nome, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde) as trn_valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, 
                       ";
					   if(isset($ativa_atimo)){
					      $estat .= "'ATIMO' as canal,";
					   }else{
					      $estat .= "(case when (vg_ug_id=7909) then 'Site ExpressMoney' else case when vg_ultimo_status_obs like '%AtimoPay%' then 'ATIMO' else 'Site Money' end end) as canal,";
					   }
					   $estat .= "ug.ug_nome as trn_nome,  ug.ug_cpf as trn_cpf, '3' as trn_cpf_indicador, vgm_id as trn_vgm_id, vgm_nfe_rps_id as trn_vgm_nfe_rps_id, 0 as trn_comissao 
                        ,  ug.ug_endereco as endereco, ug.ug_numero as numero, ug.ug_bairro as bairro, ug.ug_cidade as cidade, ug.ug_cep as CEP 
                       from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id, operadoras t2 
                        where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." and $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' ".$where_data_1b." ".$where_canal_s." ".$where_canal_a."
                        group by vgm.vgm_id, vg.vg_data_concilia,vg.vg_ultimo_status_obs, vg.vg_pagto_tipo, vg.vg_ultimo_status,
                        vg.vg_concilia, 
                                t2.opr_nome,  ug.ug_nome,   ug.ug_cpf, 
                                vg.vg_ug_id, ug.ug_nome, vgm_nfe_rps_id, vgm.vgm_opr_codigo,ug.ug_endereco, ug.ug_numero, ug.ug_bairro, ug.ug_cidade, ug.ug_cep

                       

                      

                ) v ";

        $estat .= " order by trn_data "; 

		//echo $estat;
        if($bDebug) {
            echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo0b: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
            echo str_replace("\n","<br>\n",$estat)."<br>";
        }

        $res_count = pg_query($estat);
        if (!$res_count) {
            die("Erro ao executar query: " . pg_last_error());
        }
        $total_table = pg_num_rows($res_count);
       

        if($bDebug) {
            echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo1aaa: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
        }

        // Calcula os totais
        $qtde_geral = 0;
        $valor_geral = 0;
        $res_geral = SQLexecuteQuery($estat);
        while($pg_geral = pg_fetch_array($res_geral)) {
                // Apenas para Ongame: desconta comissão das lans do valor da venda
                if($dd_operadora==13) {
                        $valor_geral += $pg_geral['trn_valor']*(1-$pg_geral['trn_comissao']);
                } else {
                        $valor_geral += $pg_geral['trn_valor'];
                }
                $qtde_geral += $pg_geral['qtde_itens'];
        }

        if($bDebug) {
            echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo2: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
        }

        if($ordem == 0) {
                $estat .= " desc ";
                $img_seta = "/sys/imagens/seta_down.gif";	
        } else {
                $estat .= " asc ";
                $img_seta = "/sys/imagens/seta_up.gif";
        }

        // Gera e gava NFe para os registros listados (ou seja, para o query com os filtros escolhidos)
        $sNFe = "";
        if($BtnGerarNFe) {
                $sNFe = ""; 

                $n_linhas = 0;
                $val_total = 0;
                $deducoes = 0;

                $data_inicial = substr($tf_data_inicial,6,4).substr($tf_data_inicial,3,2).substr($tf_data_inicial,0,2);
                $data_final = substr($tf_data_final,6,4).substr($tf_data_final,3,2).substr($tf_data_final,0,2);

                // date("t") - Number of days in the given month
                // echo "<hr>Calculate the date of the last day of the previous month: ".date("t/m/Y", strtotime("last month"))."<br>";
                $data_lote = date("Ymt", strtotime("last month"));

                // Ongame "40013952"
                // Sulake "35061375" 
                // EPP "39324311"
                $sNFe .= gera_cabecalho($dd_operadora, $data_inicial, $data_final);

                $res_nfe = SQLexecuteQuery($estat);
                while($pg_nfe = pg_fetch_array($res_nfe)) {
                        $n_linhas++;

                        if($pg_nfe['trn_vgm_nfe_rps_id']*1==0) {
                                echo "<font face='Arial, Helvetica, sans-serif' size='1' color='#FF0000'>Venda de '<b><font color='black'>".$pg_nfe['canal']."</font></b>' (ID: <b><font color='black'>".$pg_nfe['trn_vgm_id']."</font></b>, data: '<b><font color='black'>".substr($pg_nfe['trn_data'],0,19)."</font></b>') com RPS_ID nulo, favor, contatar o administrador para atualizar.</font><br>";
                        }

                        $varTipoRPS = "RPS";
                        $varSerieRPS = "EPP";
                        $varNumeroRPS = str_pad($pg_nfe['trn_vgm_nfe_rps_id'], 12, "0", STR_PAD_LEFT);	//$pg_nfe['trn_code'].str_pad($pg_nfe['trn_vgm_id'], 11, "0", STR_PAD_LEFT);
                        $varNumLote = ""; //str_pad($loteid, 4, "0", STR_PAD_LEFT);
                        $varDataEmissaoRPS = $data_lote;
                        $varSituacaoRPS = "T";
                        $valor_reg_nfe = $pg_nfe['trn_valor'];
                                if($dd_operadora==13) {
                                        $valor_reg_nfe *= (1-$pg_nfe['trn_comissao']);
                                }
                        $varValorRPS = str_pad(number_format(($valor_reg_nfe), 2, '', ''), 15, "0", STR_PAD_LEFT);
                                $val_total += $valor_reg_nfe;
                        $varDeducaoRPS = str_pad($deducoes, 15, "0", STR_PAD_LEFT);
                        $varCodigoServicoRPS = "02800";//"02798";
                        $varAliquotaRPS = "0290";//"0200";	// 2%
                        $varISSRetido = "2";
                        $varIndicadorCPF = "3"; //$pg_nfe['trn_cpf_indicador'];
                        $varCPF = "";	//$pg_nfe['trn_cpf'];	// CPF válido "27329574880"
                        $varIM = str_pad("", 8, "0", STR_PAD_LEFT);
                        $varIE = str_pad("", 12, "0", STR_PAD_LEFT);

                        $varNome = str_pad($pg_nfe['trn_nome'], 75, " ", STR_PAD_RIGHT);	//str_pad($pg_nfe['trn_nome'], 75, " ", STR_PAD_RIGHT);

                        $varTipoEndereco = str_pad("", 3, " ", STR_PAD_RIGHT);
                        $varEndereco = str_pad("", 50, " ", STR_PAD_RIGHT);
                        $varNumero = str_pad("", 10, " ", STR_PAD_RIGHT);
                        $varComplemento = str_pad("", 30, " ", STR_PAD_RIGHT);
                        $varBairro = str_pad("", 30, " ", STR_PAD_RIGHT);

                        $varCidade = str_pad("", 50, " ", STR_PAD_RIGHT);
                        $varUF = str_pad("", 2, " ", STR_PAD_RIGHT);
                        $varCEP = str_pad("", 8, " ", STR_PAD_RIGHT);

                        $varEmail = str_pad("", 75, " ", STR_PAD_RIGHT);

                        $varDiscriminacao = "Cessão de Direito de uso de Programas de Computação";

                        if(true || $n_linhas==1) {
                                // formatar_string_break(, 80)
                                $sNFe .= gera_lote($varTipoRPS, $varSerieRPS, $varNumeroRPS, $varNumLote, $varDataEmissaoRPS, $varSituacaoRPS, $varValorRPS, $varDeducaoRPS, $varCodigoServicoRPS, $varAliquotaRPS, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao).
                                        ""; 
                        }

                }
                $sNFe .= gera_rodape($n_linhas, $val_total);	

                if(false) {
                        $varArquivo = "lotes/"."nfesp_lote_".date("Ymd")."_".str_pad($loteid, 4, "0", STR_PAD_LEFT).".txt";

                        $handle = fopen($varArquivo, "w+");
                        if (fwrite($handle, $sNFe) === FALSE) {
                                $msg = "Não foi possível gravar em '$varArquivo' (2).";
                                echo $msg;
                                die("Stop");
                        } else {
                                echo "<font color='#0000CC'>Arquivo de lote N".str_pad($loteid, 4, "0", STR_PAD_LEFT)." gravado com sucesso em ".$varArquivo."</font>";
                        }
                        fclose($handle);

                }

        }

        $estat .= " limit ".$max; 
        $estat .= " offset ".$inicial;
}
else {
        $estat = "select est_codigo from estabelecimentos where est_codigo = 0";
}
		
        $sql_transform=$estat;

		//echo $estat; //- atimo
		
	$resestat = SQLexecuteQuery($estat);

if($bDebug) {
    $time_end_ = getmicrotime();
    echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo3: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
}
	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
	$varsel  = "&cb_opr_teste=$cb_opr_teste&cb_estab_teste=$cb_estab_teste";
	$varsel .= "&tf_data_final=$tf_data_final&tf_data_inicial=$tf_data_inicial";
	$varsel .= "&tf_codigo_estab=$tf_codigo_estab&tf_nome_estab=$tf_nome_estab&dd_uf=$dd_uf&dd_uf_except=$dd_uf_except";
	$varsel .= "&dd_operadora=$dd_operadora&dd_valor=$dd_valor&dd_opr_area=$dd_opr_area";
    
    
?>
<html>
<head>

<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<title>E-Prepag</title>
<script language='javascript' src='/js/<?php echo LANG_NAME_CALENDAR_FILE; ?>'></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<script language=javascript>
// http://bytes.com/forum/thread598274.html
function selectNode (node)
{
	var selection, range, doc, win;

	if ((doc = node.ownerDocument) && (win = doc.defaultView) && typeof win.getSelection != 'undefined' && typeof doc.createRange != 'undefined' && (selection = window.getSelection()) && typeof selection.removeAllRanges != 'undefined')	{
		range = doc.createRange();
		range.selectNode(node);
		selection.removeAllRanges();
		selection.addRange(range);
	}
	else if (document.body && typeof document.body.createTextRange != 'undefined' && (range = document.body.createTextRange())) {
		range.moveToElementText(node);
		range.select();
	}
}

function clearSelection (){
	if (document.selection)
		document.selection.empty();
	else if (window.getSelection)
		window.getSelection().removeAllRanges();
}
</script>
<SCRIPT LANGUAGE=JAVASCRIPT><!--
    function ShowPopupWindowXY(fileName,x,y) {
    myFloater = window.open('','myWindow','scrollbars=1,status=0,resizable=1,width=' + x + ',height=' + y)
    myFloater.location.href = fileName;
}
//--></SCRIPT>
<!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_PINS_PAGE_TITLE_1; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="../../commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
  <?php if($sNFe) { 

	// Deleta arquivos >2horas
	$now = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
	foreach (glob("lotes/*.txt") as $filename) {
		if(($now-filemtime($filename))>2*3600) {
			unlink($filename);
		}
	}


	// Arquivo
	$path = $raiz_do_projeto . "public_html";
	$url = "/sys/admin/vendas/vendas_estab/lotes/";
	$file = date("YmdHis").str_pad(rand(0,999), 3, "0", STR_PAD_LEFT).".txt";

	//Grava mensagem no arquivo
	if ($handle = fopen($path.$url.$file, 'a+')) {
		fwrite($handle, $sNFe);
		fclose($handle);
	} 

	?>
  <tr> 
    <td valign="center" bgcolor="#FFFFFF" width="903"><p><font face="Arial, Helvetica, sans-serif" size="2" color="#000000">
	<h3>NFe para registros listados.</h3>
	<p>Salvar arquivo <a href="<?php echo $url.$file; ?>">aqui</a>. (Tamanho do arquivo: <?php 
		$f_size = strlen($sNFe);
		echo ( ($f_size>=1024*1024)?number_format($f_size/1024/1024, 2, ',', '.')."Mb":number_format($f_size/1024, 2, ',', '.')."kB"); 
		?>)</p>
	
	<SCRIPT LANGUAGE=JAVASCRIPT><!--
	// Abre janela para pagamento no site do banco
		ShowPopupWindowXY('<?php echo $url.$file; ?>', 800, 600);

	//--></SCRIPT>

	<?php
	// Se tamanho do arquivo > 10Mb => avisa
	if(strlen($sNFe)>10*1024*1024) {
	?>
	<p><font color="#FF0000">(Avido: O tamanho do arquivo gerado é maior do que 10Mb, tente diminuir o intervalo das datas)</font></p>
	<?php
	}
	?>
	</td>
  </tr>
  <?php } ?>
  
    <form name="form1" method="post" action="">
        <div class="row txt-cinza top20">
            <div class="col-md-2">
                <span class="pull-right"><?php echo LANG_PINS_START_DATE; ?></span>
            </div>
            <div class="col-md-3">
                 <input name="tf_data_inicial" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
            </div>
            <div class="col-md-2">
                <span class="pull-right"><?php echo LANG_PINS_END_DATE; ?></span>
            </div>
            <div class="col-md-3">
                <input name="tf_data_final" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
            </div>
            <?php
                if($_SESSION["tipo_acesso_pub"]=='AT') {
            ?>
            <div class="col-md-2">
                <input type="submit" name="FrmPreencher" value="Preencher Seq." class="btn btn-sm btn-info botao_gerarnfe" style="color:blue">
            </div>
            <?php
                }
            ?>
        </div>
        <div class="row txt-cinza top10">
            <div class="col-md-2 text-right">
                <span class="pull-right"><?php echo LANG_PINS_CHANNEL; ?></span>
            </div>
            <div class="col-md-3">
                <select name="dd_canal" id="dd_canal" class="form-control">
                    <option value=""><?php echo LANG_PINS_ALL_CHANNELS; ?></option>
                    <option value="LH" <?php  if("LH" == $dd_canal) echo "selected" ?>>LanHouse</option>
                    <option value="Money" <?php  if("Money" == $dd_canal) echo "selected" ?>>Money</option>
                    <option value="ExpressMoney" <?php  if("ExpressMoney" == $dd_canal) echo "selected" ?>>Express Money</option>
					<option value="ATIMO" <?php  if("ATIMO" == $dd_canal) echo "selected" ?>>ATIMO</option>
                    <option value="SiteME" <?php  if("SiteME" == $dd_canal) echo "selected" ?>>Site (M+E)</option>
                    <option value="SiteMEL" <?php  if("SiteMEL" == $dd_canal) echo "selected" ?>>Site (M+E+L)</option>
                    <option value="POS" <?php  if("POS" == $dd_canal) echo "selected" ?>>POS</option>
                    <option value="Cartões" <?php  if("Cartões" == $dd_canal) echo "selected" ?>>Cartões</option>
                </select>
            </div>
            <div class="col-md-2 text-right">
                <?php echo LANG_PINS_REPORT_TYPE; ?>
            </div>
            <div class="col-md-3">
                <?php  if($_SESSION["tipo_acesso_pub"]=='PU') { ?>
                    <span style="font-weight: bold"><?php echo LANG_PINS_OUT; ?></span>
                    <input type="hidden" name="dd_mode" id="dd_mode" value="<?php echo $dd_mode?>">
                <?php  } else { ?>	
                <select name="dd_mode" id="dd_mode" class="form-control" disabled>
                  <option value="S" <?php  if($dd_mode=='S') echo "selected" ?>><?php echo LANG_PINS_OUT; ?></option>
                  <option value="V" <?php  if($dd_mode=='V') echo "selected" ?>><?php echo LANG_PINS_SALES; ?></option>
                </select>
                <?php 
                  } 
                ?>
            </div>
            <div class="col-md-2">
                <input type="submit" name="BtnGerarNFe" value="Gerar NFe" class="btn btn-sm btn-danger botao_gerarnfe">
            </div>
        </div>
        <div class="row txt-cinza top10">
            <div class="col-md-2 text-right">
                <?php echo LANG_PINS_OPERATOR; ?>
            </div>
            <div class="col-md-3">
                <?php 
                    if($_SESSION["tipo_acesso_pub"]=='PU') {
                ?>
                    <?php echo $_SESSION["opr_nome"]?>
                    <input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
                <?php 
                  } else {
                ?>

                <select name="dd_operadora" id="dd_operadora" class="form-control">
                  <option value=""><?php echo LANG_PINS_ALL_OPERATORS; ?></option>
                  <?php  while ($pgopr = pg_fetch_array ($resopr)) { ?>
                  <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?> (<?php echo $pgopr['opr_codigo']?>)</option>
                  <?php  } ?>
                </select>
                <?php 
                  } 
                ?>
            </div>
            <div class="col-md-2 text-right">
                <?php echo LANG_PINS_VALUE; ?>
            </div>
            <div class="col-md-3">
                <select name="dd_valor" id="dd_valor" class="form-control" disabled>
                    <option value=""><?php echo LANG_PINS_ALL_VALUES; ?></option>
                    <?php  while ($pgval = pg_fetch_array ($resval)) { ?>
                    <option value="<?php  echo $pgval['valor'] ?>" <?php  if($pgval['valor'] == $dd_valor) echo "selected" ?>><?php  echo number_format($pgval['valor'], 2, ',', '.'); ?></option>
                    <?php  } ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn btn-sm btn-default botao_search">
            </div>
        </div>
    </form>
    <button onclick="exportarTabelaParaExcel()" class="btn btn-success">Exportar Excel</button>

    <table border="0" cellpadding="0" cellspacing="0" width="897">
      <tr> 
        <td> 
          <?php 
            if($data_inic_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_PINS_START_DATE."</b></font>";
            if($data_fim_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_PINS_END_DATE."</b></font>";
            if($data_inicial_menor == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_PINS_COMP_DATE_START_WITH_END."</b></font>";
        ?>
        </td>
      </tr>
    </table>
    <div class="row txt-cinza espacamento">
        <div class="col-md-12 bg-cinza-claro">
            <table id="table" class="table bg-branco txt-preto fontsize-p">
                <tr> 
                    <td>
                      <?php $_SESSION['sqldata']=$sql_transform;  if($total_table > 0) { ?>
                      <?php echo LANG_SHOW_DATA; ?> <strong><?php  echo $inicial + 1 ?></strong> 
                      <?php echo LANG_TO; ?> <strong><?php  echo $reg_ate ?></strong> <?php echo LANG_FROM; ?> <strong><?php  echo $total_table ?></strong>
                      <?php  } ?>
                    </td> 
                </tr>
            </table>
            <table id="tableNfe" class="table bg-branco txt-preto fontsize-p">
                
              <tr> 
                <?php 
				if($ordem == 1)
					$ordem = 0;
				else
					$ordem = 1;
				?>
                <td><strong><?php echo LANG_PINS_DATE; ?></strong> 
                  <?php  if($ncamp == 'trn_data') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><?php echo "VG_ID"; ?></strong> 
                  <?php  if($ncamp == 'trn_vgm_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>

				<!--td><strong><a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=est_codigo&inicial=".$inicial.$varsel ?>" class="link_br">Codigo</a></strong> 
                  <?php  if($ncamp == 'est_codigo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=nome_fantasia&inicial=".$inicial.$varsel ?>" class="link_br">Canal 
                  de Venda</a></strong> 
                  <?php  if($ncamp == 'nome_fantasia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=nome_fantasia&inicial=".$inicial.$varsel ?>" class="link_br">Estabelecimento</a></strong> 
                  <?php  if($ncamp == 'nome_fantasia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=municipio&inicial=".$inicial.$varsel ?>" class="link_br">Municipio</a></strong> 
                  <?php  if($ncamp == 'municipio') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td-->
                <td><strong><?php echo "RPS_ID"; ?></strong> 
                  <?php  if($ncamp == 'trn_vgm_nfe_rps_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><div align="center"> 
                    <?php  if($ncamp == 'canal') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><?php echo LANG_PINS_CHANNEL; ?>
                    </strong></div></td>
                <td><div align="center"><strong><?php echo LANG_PINS_OPERATOR; ?></strong> 
                  <?php  if($ncamp == 'opr_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></div>
                </td>
                <td><div align="center"><strong><?php echo LANG_PINS_USER; ?></strong> 
                  <?php  if($ncamp == 'trn_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></div>
                </td>
                <td><div align="center"><strong><?php echo "CPF"; ?></strong> 
                  <?php  if($ncamp == 'trn_cpf') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></div>
                </td>
                <td><div align="right"> 
                    <?php  if($ncamp == 'quantidade') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><?php echo LANG_PINS_QUANTITY_1; ?></strong></div></td>
                <td><div align="right"> 
                    <?php  if($ncamp == 'trn_valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><?php echo LANG_PINS_TOTAL_VALUE; ?></strong></div></td>
                    <td><div align="right"> 
                       <strong>Endereço</strong></div></td>
                       <td><div align="right"> 
                       <strong>Bairro</strong></div></td>
                       <td><div align="right"> 
                       <strong>Cidade</strong></div></td>
                       <td><div align="right"> 
                       <strong>CEP</strong></div></td>
              </tr>
              <?php 
if($bDebug) {
echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo4: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
//die("Stop");
}
					$cor1 = $query_cor1;
					$cor2 = $query_cor1;
					$cor3 = $query_cor2;
					while ($pgrow = pg_fetch_array($resestat)) {
						$valor = true;

						$valor_da_venda = $pgrow['trn_valor'];
						$valor_comissao = $pgrow['trn_comissao'];
						$valor_da_venda_sem_comissao = $valor_da_venda*(1-$valor_comissao);
						if($dd_operadora==13) {
							$valor_da_venda *= (1-$valor_comissao);
						}
						$qtde_total_tela += $pgrow['qtde_itens'];
						$valor_total_tela += $valor_da_venda;

				?>
              <tr bgcolor="#f5f5fb"> 
                <td bgcolor="<?php  echo $cor1 ?>"><?php  echo substr($pgrow['trn_data'], 0, 19) ?></td>
                <td bgcolor="<?php  echo $cor1 ?>"><?php  echo $pgrow['trn_code'].str_pad($pgrow['trn_vgm_id'], 11, "0", STR_PAD_LEFT) ?></td>
                <!--td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['est_codigo'] ?></td>
                <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['nome'] ?></td>
                <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['nome_fantasia'] ?></td>
                <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['municipio'] ?></td-->
                <td bgcolor="<?php  echo $cor1 ?>" align="right"><?php  echo $pgrow['trn_vgm_nfe_rps_id'] ?></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><?php  echo $pgrow['canal'] ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><?php  echo $pgrow['opr_nome'] ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><?php  echo ($pgrow['trn_nome']); ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><?php  echo ($pgrow['trn_cpf']); ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><?php  echo $pgrow['qtde_itens'] ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>" title="<?php  echo number_format($pgrow['trn_valor'], 2, ',', '.') ?>"><div align="right"><?php  echo number_format($valor_da_venda, 2, ',', '.') ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><?php  echo $pgrow['endereco'] ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><?php  echo $pgrow['bairro'] ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><?php  echo $pgrow['cidade'] ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><?php  echo $pgrow['CEP'] ?></div></td>
                </tr>
              <?php 
				 		if($cor1 == $cor2)
							$cor1 = $cor3;
						else
							$cor1 = $cor2;
					}
if($bDebug) {
echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(tempo5: ".number_format((getmicrotime() - $time_start), 2, '.', '.')."s)</font><br>"; 
}
			 		if (!$valor) { ?>
              <tr bgcolor="#f5f5fb"> 
                <td colspan="14" bgcolor="<?php  echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                    <?php echo LANG_NO_DATA; ?>.<br>
                    <br>
                    </strong></font></div></td>
              </tr>
              <?php  } else { ?>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="8" align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo LANG_PINS_SUBTOTAL; ?>&nbsp;</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($qtde_total_tela, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="2"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($valor_total_tela, 2, ',', '.') ?></strong></font></div></td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="8" align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo LANG_PINS_TOTAL; ?>&nbsp;</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($qtde_geral, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="2"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($valor_geral, 2, ',', '.') ?></strong></font></div></td>
              </tr>
              <?php 
					$time_end = getmicrotime();
					$time = $time_end - $time_start;
				?>
              <?php 
					paginacao_query($inicial, $total_table, $max, '9', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
				?>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="10" bgcolor="#FFFFFF"><strong> 
                  <?php echo LANG_PINS_LAST_MSG; ?>. </strong></font></td>
              </tr>
              <tr> 
                <td height="52" colspan="10" bgcolor="#FFFFFF"><p><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php  echo LANG_POS_SEARCH_MSG." ".number_format($time, 2, '.', '.')." ".LANG_POS_SEARCH_MSG_UNIT ?> 
                 <br>
				 <a href="#header">Top</a>
				 </font></p>
                  </td>
              </tr>
              <?php  } ?>
            </table>
        </div>
    </div>
            
      
            </div>
        </div>
    </div>
</div>
<script>
function exportarTabelaParaExcel() {
    const tabela = document.getElementById("tableNfe");
    if (!tabela) {
        alert("Tabela não encontrada!");
        return;
    }

    let csv = '';

    // Cabeçalho
    csv += [
        "Data da Operação",
        "RPS_ID",
        "Canal",
        "Operadora",
        "Usuário",
        "CPF",
        "Qtde",
        "Valor Total",
        "Endereço",
        "Bairro",
        "Cidade",
        "CEP"
    ].map(col => `"${col}"`).join(",") + '\n';

    const linhas = tabela.querySelectorAll('tr');

    linhas.forEach((row) => {
        const textoLinha = row.innerText?.trim() || row.textContent?.trim() || "";

        // Ignorar linhas irrelevantes
        if (
            textoLinha.toLowerCase().includes("subtotal") ||
            textoLinha.toLowerCase().includes("total") ||
            textoLinha.toLowerCase().includes("valores expressos") ||
            textoLinha.toLowerCase().includes("pesquisa gerada em") ||
            textoLinha.toLowerCase().includes("obs") ||
            row.querySelectorAll('td, th').length <= 1
        ) {
            return;
        }

        const cols = row.querySelectorAll('td, th');
        let linhaCsv = [];

        // Índices: 0 = Data, 1 = VG_ID (ignorar), 2 = RPS_ID, 3 = Canal, 4 = Operadora, 5 = Usuário,
        // 6 = CPF, 7 = Qtde, 8 = Valor Total, 9 = Endereço, 10 = Bairro, 11 = Cidade, 12 = CEP
        // Vamos montar na ordem desejada: 0,2,3,4,5,6,7,9,10,11,12,13
        const mapaIndices = [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        mapaIndices.forEach((index) => {
            let col = cols[index];
            let valor = col ? (col.innerText || col.textContent || '').trim() : '';

            // Substitui valores vazios por 'não informado' para campos finais
            if ((index >= 9 && index <= 12) && (!valor || valor === '-' || valor === '""')) {
                valor = 'não informado';
            }

            valor = valor.replace(/"/g, '""'); // escapa aspas duplas
            linhaCsv.push(`"${valor}"`);
        });

        if (linhaCsv.length > 0) {
            csv += linhaCsv.join(',') + '\n';
        }
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", "exportacao_nfe.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}



function exportarTabelaParaExcel2() {
    const tabela = document.getElementById("tableNfe");
    if (!tabela) {
        alert("Tabela não encontrada!");
        return;
    }

    let csv = '';

    // Cabeçalho SEM a coluna VG_ID
    csv += [
        "Data da Operação",
        "RPS_ID",
        "Operadora",
        "Usuário",
        //"CPF",
        "Qtde",
        "Valor Total"
    ].map(col => `"${col}"`).join(",") + '\n';

    const linhas = tabela.querySelectorAll('tr');

    linhas.forEach((row) => {
        const textoLinha = row.innerText?.trim() || row.textContent?.trim() || "";

        // Ignorar linhas que nÃ£o devem ir para o Excel
        if (
            textoLinha.toLowerCase().includes("subtotal") ||
            textoLinha.toLowerCase().includes("total") ||
            textoLinha.toLowerCase().includes("valores expressos") ||
            textoLinha.toLowerCase().includes("pesquisa gerada em") ||
            textoLinha.toLowerCase().includes("obs") ||
            row.querySelectorAll('td, th').length <= 1
        ) {
            return;
        }

        const cols = row.querySelectorAll('td, th');
        let linhaCsv = [];

        // Ãndices das colunas desejadas: 0 = Data, 2 = RPS_ID, 4 = Operadora, 5 = UsuÃ¡rio, 6 = CPF, 7 = Qtde, 9 = Valor Total
        const colunasDesejadas = [0, 2, 4, 5, 6, 7, 9];

        cols.forEach((col, index) => {
            if (colunasDesejadas.includes(index)) {
                let valor = col.innerText || col.textContent;
                valor = valor.replace(/"/g, '""'); // escapa aspas duplas
                linhaCsv.push(`"${valor}"`);
            }
        });

        if (linhaCsv.length > 0) {
            csv += linhaCsv.join(',') + '\n';
        }
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", "exportacao_nfe.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>




<?php  require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
</body>
</html>