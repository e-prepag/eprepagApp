<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
set_time_limit(3600);
// Levantamento de CPFs consultados no dia anterior e atualizado na tabela CPF_CACHE
// backoffice\offweb\tarefas\cpf_cache.php

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//Perùodo considerado no Levantamento
define('PERIODO_CONSIDERADO', 1);

//=========  Dia considerado no processamento
$currentmonth = mktime(0, 0, 0, date('n'), date('j')-1, date('Y'));

//=========  Dia Inicial considerado no processamento
$initialmonth = mktime(0, 0, 0, date('n'), date('j')-(PERIODO_CONSIDERADO), date('Y'));

// Dados do Email
$email  = "wagner@e-prepag.com.br";
$cc     = "glaucia@e-prepag.com.br";
$bcc    = "";
$subject= "AtualizaÁ„o Di·ria do Cache de Consultas de CPF considerados nos ˙ltimos ".PERIODO_CONSIDERADO." dias";
$msg    = "";

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 

$time_start_stats = getmicrotime();

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Atualizaùùo Diùria do Cache de Consultas de CPF (".date("Y-m-d H:i:s").") considerados nos ùltimos ".PERIODO_CONSIDERADO." dias".PHP_EOL.PHP_EOL;

//Query de captura dos dados do dia considerado
$sql = "
    select 
  			replace(replace(ug_cpf, '.', ''), '-', '')::bigint * 1 as teste,
                        ug_cpf, 
                        ug_nome,
                        data_nascimento
                from ( 
                
                    (select 
                            ug_cpf, 
                            ug_nome_cpf as ug_nome,
                            ug_data_nascimento as data_nascimento
                    from tb_venda_games vg 
                            inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                            inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                    where ug.ug_data_cpf_informado >= '".date("Y-m-d",$initialmonth)." 00:00:00'
                          and ug.ug_data_cpf_informado <= '".date("Y-m-d",$currentmonth)." 23:59:59'
                          and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' 
                          and ug_cpf is not null
			  AND ug_nome_cpf is not null
			  AND length(ug_cpf) = 14 
			  AND ug_nome_cpf != ''
                            and ug_data_cpf_informado is not NULL
                            and ug_data_cpf_informado != '2014-07-11 00:00:00'
                            and ug_data_nascimento is not NULL
                    group by ug_cpf, ug_nome_cpf,ug_data_nascimento)

                    union all
                    
                    (select 
                            vgm_cpf as ug_cpf, 
                            vgm_nome_cpf as ug_nome,
                            vgm_cpf_data_nascimento as data_nascimento
                    from tb_dist_venda_games vg 
                            inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                            inner join dist_usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                    where vg.vg_data_inclusao >= '".date("Y-m-d",$initialmonth)." 00:00:00'
                           and vg.vg_data_inclusao <= '".date("Y-m-d",$currentmonth)." 23:59:59'
                           and vgm_cpf is not null
                           AND vgm_nome_cpf is not null
                           AND length(vgm_cpf) = 14 
                           AND vgm_nome_cpf != ''
                          and vgm_cpf_data_nascimento is not null
                    group by vgm_cpf, vgm_nome_cpf,vgm_cpf_data_nascimento)
                    
                    union all

                    (select 
                        picc_cpf as ug_cpf, 
                        picc_nome as ug_nome,
                        picc_data_nascimento as data_nascimento
                    from pins_integracao_card_historico
                            left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                    where pin_status = '4' 
                        and pih_codretepp = '2'
                        and pih_data >= '".date("Y-m-d",$initialmonth)." 00:00:00'
                        and pih_data <= '".date("Y-m-d",$currentmonth)." 23:59:59'
                        and picc_cpf is not null
                        AND picc_nome is not null
                        AND length(picc_cpf) = 14 
                        AND picc_nome != ''
                        and picc_data_nascimento is not null
                    group by picc_cpf, picc_nome, picc_data_nascimento)
                    
                    union all

                    (select 
                        vgcbe_cpf as ug_cpf, 
                        vgcbe_nome_cpf as ug_nome,
                        vgcbe_data_nascimento as data_nascimento
                    from tb_venda_games_cpf_boleto_express
			inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
			inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                    where vgcbe_data_inclusao >= '".date("Y-m-d",$initialmonth)." 00:00:00'
                        and vgcbe_data_inclusao <= '".date("Y-m-d",$currentmonth)." 23:59:59'
                        and vgcbe_cpf is not null
                        AND vgcbe_nome_cpf is not null
                        AND length(vgcbe_cpf) = 14 
                        AND vgcbe_nome_cpf != ''
                       and vgcbe_data_nascimento is not null
                    group by vgcbe_cpf, vgcbe_nome_cpf,vgcbe_data_nascimento)

 ) tabelaUnion 
                    where data_nascimento != '0001-01-01 00:00:00 BC'
                    group by ug_cpf, ug_nome, data_nascimento
                    order by ug_cpf; 

        ";
echo $sql.PHP_EOL;

$rs_dados_cpf = SQLexecuteQuery($sql);
        
//Verificando Dados
$msg .= PHP_EOL."Total de CPF ùNICOS consultados em ".date("Y-m-d",$currentmonth)."(YYYY-MM-DD):  [".pg_num_rows($rs_dados_cpf)."] CPFs<br>".PHP_EOL;
$total_cpfs_ja_existentes = 0;
$total_cpfs_ja_existentes_checados = 0;
$total_cpfs_novos = 0;
while($rs_dados_cpf_row = pg_fetch_array($rs_dados_cpf)) {
    
    //Verificando se dado jù existe na tabela de CACHE
    $sql = "select cpf,checado from cpf_cache where cpf=".$rs_dados_cpf_row['teste'].";";
    $rs_existe = SQLexecuteQuery($sql);
    //echo($sql);
    if(pg_num_rows($rs_existe) > 0) {
        
        //Atualizando o registro na tabela cd CPF CACHE
        echo "CPF [".$rs_dados_cpf_row['ug_cpf']."] jù existe na tabela de CACHE".PHP_EOL;
        $sql = "UPDATE cpf_cache SET data_nascimento = '".$rs_dados_cpf_row['data_nascimento']."', nome = '".$rs_dados_cpf_row['ug_nome']."', checado=1 WHERE cpf=".$rs_dados_cpf_row['teste'].";";
        //echo $sql;
        $rs_update = SQLexecuteQuery($sql);
        if(!$rs_update) {
            echo "Erro ao atualizar registro: ".$sql.PHP_EOL;
        }//end if(!$rs_update)
        else {
            $rs_existe_row = pg_fetch_array($rs_existe);
            if($rs_existe_row['checado'] == 1) {
                $total_cpfs_ja_existentes_checados++;
            }//end if($rs_existe_row['checado'] == 1)
            else {
                $total_cpfs_ja_existentes++;
            }//end else do if($rs_existe_row['checado'] == 1)
        }//end else do if(!$rs_update)
        
    } //end if(pg_num_rows($rs_existe) > 0)
    else {
        
        //Inserindo novo registro na tabela
        echo "CPF [".$rs_dados_cpf_row['ug_cpf']."] novo na tabela de CACHE".PHP_EOL;
        $sql = "INSERT INTO cpf_cache (cpf, data_nascimento, nome, checado) VALUES (".$rs_dados_cpf_row['teste'].", '".$rs_dados_cpf_row['data_nascimento']."', '".$rs_dados_cpf_row['ug_nome']."', 1);";
        //echo $sql;
        $rs_update = SQLexecuteQuery($sql);
        if(!$rs_update) {
            echo "Erro ao Inserir registro: ".$sql.PHP_EOL;
        }//end if(!$rs_update)
        else {
            $total_cpfs_novos++;
        }//end else do if(!$rs_update)
        
    } //end else do if(pg_num_rows($rs_existe) > 0)
    
} //end while
$msg .=  "Total de CPFs jù existentes na tabela de CACHE que jù foram validados na Receita: <b>".$total_cpfs_ja_existentes_checados." Atualizados</b><br>".PHP_EOL."Total de CPFs jù existentes na tabela de CACHE SEM validaùùo na Receita: <b>".$total_cpfs_ja_existentes." Atualizados</b><br>".PHP_EOL."Total de CPFs novos na tabela de CACHE: <b>".$total_cpfs_novos." Atualizados</b><br>".PHP_EOL;

echo str_replace('<br>', PHP_EOL, $msg);

if(!empty($msg)) {
    if(enviaEmail($email, $cc, $bcc, $subject, $msg)) {
        echo "Email enviado com sucesso".PHP_EOL;
    }
    else {
        echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
    }
}//end if(!empty($msg))

echo str_repeat("_", 80) .PHP_EOL."Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80).PHP_EOL;
// Fim da execuÁ„o
$fim = microtime(true);
$horaFim = date('Y-m-d H:i:s');
$tempoExecucao = round($fim - $inicio, 2);

// Log
$logMsg = "[INÕCIO] $horaInicio | [FIM] $horaFim | [DURA«√O] {$tempoExecucao}s" . PHP_EOL;
file_put_contents(__DIR__ . '/cron_execucao.log', $logMsg, FILE_APPEND);
//Fechando Conexùo
pg_close($connid);

?>