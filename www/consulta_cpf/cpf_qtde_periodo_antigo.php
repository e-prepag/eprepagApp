<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
set_time_limit(28800);
// Levantamento de Quantidade de utilização de cada CPFs no período determinado e atualizado na tabela CPF_CACHE
// backoffice\offweb\tarefas\cpf_qtde_periodo.php
require_once "../includes/main.php";

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//Período considerado no Levantamento
define('PERIODO_CONSIDERADO', 30);

//Valor limite para ser considerado no E-mail
define('VALOR_LIMITE', 3000);

//=========  Dia Final considerado no processamento
$currentmonth = mktime(0, 0, 0, date('n'), date('j')-1, date('Y'));

//=========  Dia Inicial considerado no processamento
$initialmonth = mktime(0, 0, 0, date('n'), date('j')-(PERIODO_CONSIDERADO+1), date('Y'));

// Dados do Email
$email  = "rc@e-prepag.com.br; rc1@e-prepag.com.br";
$cc     = "gglaucia@e-prepag.com.br; ti@e-prepag.com.br; flancofi55@gmail.com";
$bcc    = "";
$subject= (checkIP()?"[DEV - HOMOLOGAÇÃO]":"[PROD]") . " Levantamento de Quantidade de cada CPFs no período ".PERIODO_CONSIDERADO." dias";
$msg    = "";

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 

$time_start_stats = getmicrotime();

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Levantamento de Quantidade de utilização de cada CPFs no período de ".PERIODO_CONSIDERADO." dias e atualizado na tabela CPF_CACHE (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

//Query de captura dos dados do dia considerado
$sql = "
select 
	replace(replace(ug_cpf, '.', ''), '-', '')::bigint * 1 as teste,
	ug_cpf, 
	ug_nome, 
	sum(total_em_reais) as total_em_reais, 
	sum(total_cpf) as total_cpf
from ( 
            (select 
                    ug_cpf,
                    count(distinct(vg_id)) as total_cpf, 
                    UPPER(trim(ug_nome_cpf)) as ug_nome, 
                    sum(vgm.vgm_valor * vgm.vgm_qtde) as total_em_reais 
            from tb_venda_games vg 
                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vg.vg_data_concilia >= '".date("Y-m-d",$initialmonth)." 00:00:00'
                    and vg.vg_data_concilia <= '".date("Y-m-d",$currentmonth)." 23:59:59'
                    and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                    and vgm.vgm_opr_codigo NOT IN (49,53)
            group by ug_cpf, ug_nome_cpf, vgm_opr_codigo 
            )
            
        union all
            
            (select 
                    vgm_cpf as ug_cpf, 
                    count(distinct(vg_id)) as total_cpf, 
                    UPPER(vgm_nome_cpf) as ug_nome, 
                    sum(vgm.vgm_valor * vgm.vgm_qtde) as total_em_reais 
            from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."'
                    and vg.vg_data_inclusao >= '".date("Y-m-d",$initialmonth)." 00:00:00'
                    and vg.vg_data_inclusao <= '".date("Y-m-d",$currentmonth)." 23:59:59'
            group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo
            )

	union all
            
            (select 
                    picc_cpf as ug_cpf, 
                    count(distinct(pin_codinterno)) as total_cpf, 
                    UPPER(picc_nome) as ug_nome, 
                   sum(pih_pin_valor/100) as total_em_reais
            from pins_integracao_card_historico
                left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
	    where pin_status = '4' 
		    and pih_codretepp = '2'
                    and pih_data >= '".date("Y-m-d",$initialmonth)." 00:00:00'
                    and pih_data <= '".date("Y-m-d",$currentmonth)." 23:59:59'
            group by picc_cpf, picc_nome, pih_id)
           
        union all
            
            (select 
                    vgcbe_cpf as ug_cpf, 
                    count(distinct(vg_id)) as total_cpf, 
                    UPPER(vgcbe_nome_cpf) as ug_nome, 
                    sum(vgm_valor * vgm_qtde) as total_em_reais
            from tb_venda_games_cpf_boleto_express
                inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
	    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vgcbe_data_inclusao >= '".date("Y-m-d",$initialmonth)." 00:00:00'
                    and vgcbe_data_inclusao <= '".date("Y-m-d",$currentmonth)." 23:59:59'
           group by vgcbe_cpf, vgcbe_nome_cpf)

) tabelaUnion 
where 
	ug_nome is NOT null
	and
	ug_nome is NOT null
	and
	length(ug_cpf) = 14 
	AND
	ug_nome != ''
group by ug_cpf, ug_nome 
order by total_cpf desc;    
";
echo ($sql);
$rs_dados_cpf = SQLexecuteQuery($sql);
        
//Verificando Dados
$msg .= PHP_EOL."Total de CPF considerados entre ".date("Y-m-d",$initialmonth)." à ".date("Y-m-d",$currentmonth)." (YYYY-MM-DD):  [<b>".pg_num_rows($rs_dados_cpf)."</b>] CPFs<br>".PHP_EOL.PHP_EOL;
$total_cpfs_valor_superior = 0;
$total_cpfs_na_tabela = 0;
$total_cpfs_ainda_nao_na_tabela = 0;
$msg_cpf_nao_em_cache = "";
$cpf_com_utilizacao = [];
while($rs_dados_cpf_row = pg_fetch_array($rs_dados_cpf)) {
    
    if($rs_dados_cpf_row['total_cpf'] > 0){
        $cpf_com_utilizacao[] = $rs_dados_cpf_row['teste'];
    }
    //Verificando se dado já existe na tabela de CACHE
    $sql = "UPDATE cpf_cache SET qtde_utilizado = ".$rs_dados_cpf_row['total_cpf']." where cpf = ".$rs_dados_cpf_row['teste'].";";
    echo($sql);
    $rs_existe = SQLexecuteQuery($sql);
    $cmdtuples = pg_affected_rows($rs_existe);
    if($cmdtuples===1) {

        echo " CPF [".$rs_dados_cpf_row['ug_cpf']."] atualizado com SUCESSO".PHP_EOL;
        $total_cpfs_na_tabela++;
        
    } //end if($cmdtuples===1)
    else {
        $aux_msg = " CPF [".$rs_dados_cpf_row['ug_cpf']."] ainda NÃO existe na tabela";
        $msg_cpf_nao_em_cache .= $aux_msg."<br>";
        echo $aux_msg.PHP_EOL;
        $total_cpfs_ainda_nao_na_tabela++;
        
    } //end else do if($cmdtuples===1)
    
    //Verificando se atingiu o VALOR_LIMITE
    if($rs_dados_cpf_row['total_em_reais'] >= VALOR_LIMITE) {
        
        $msg .=  " CPF ".$rs_dados_cpf_row['ug_cpf']." possui volume de <b>R$ ".  number_format($rs_dados_cpf_row['total_em_reais'], 2, ",", "."). "</b> com quantidade de utilização de <b>".$rs_dados_cpf_row['total_cpf']."</b> vezes".PHP_EOL;
        $total_cpfs_valor_superior++;
        
    }//end if($rs_dados_cpf_row['ug_cpf'] <= VALOR_LIMITE) 
    
} //end while

$msg .=  PHP_EOL.PHP_EOL."<b>RESUMO GERAL</b>".PHP_EOL.PHP_EOL."Total de CPFs já existentes na tabela de CACHE: <b>".$total_cpfs_na_tabela." Atualizados</b>".PHP_EOL.PHP_EOL."Total de CPFs NÃO localizados na tabela de CACHE: <b>".$total_cpfs_ainda_nao_na_tabela."</b>".PHP_EOL.PHP_EOL."Total de CPFs excederam o valor limite(".VALOR_LIMITE."): <b>".$total_cpfs_valor_superior." </b>".PHP_EOL.PHP_EOL; //.($total_cpfs_ainda_nao_na_tabela > 0?"Lista de CPFs não encontrado na tabela de cache:".PHP_EOL.$msg_cpf_nao_em_cache.PHP_EOL.PHP_EOL:"");

echo str_replace('<br>', PHP_EOL, $msg);

if(!empty($msg)) {
    if(enviaEmail($email, $cc, $bcc, $subject, str_replace(PHP_EOL,'<br>', $msg))) {
        echo "Email enviado com sucesso".PHP_EOL;
    }
    else {
        echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
    }
}//end if(!empty($msg))


//Limpando registro que sairam do período de abrangencia e ficaram com dados de quantidade diferente de zero.
if(count($cpf_com_utilizacao) > 0 && date('d') == "01"){
    echo "Atualizando cpfs sem compra";
    $sql = "UPDATE cpf_cache SET qtde_utilizado = 0 where cpf NOT IN (".implode(",", $cpf_com_utilizacao).");";
    // Query para fins de testes mais rápidos
    // $sql = "UPDATE cpf_cache SET qtde_utilizado = 0 where cpf IN (SELECT cpf FROM cpf_cache WHERE cpf NOT IN (".implode(",", $cpf_com_utilizacao).") LIMIT 10);";
    echo($sql . PHP_EOL);
    $rs_zera = SQLexecuteQuery($sql);
    echo pg_affected_rows($rs_zera) . " registros foram atualizados" . PHP_EOL;
}

$sql = "
SELECT 
    COUNT(ug_id) AS total,
    ug_cpf
FROM usuarios_games 
WHERE ug_cpf is not null AND ug_cpf != '' AND ug_cpf != '..-'
    AND ug_ativo = 1
    AND ug_email NOT LIKE '%_BLOQUEADO_PERMANENTEMENTE'
GROUP BY ug_cpf
ORDER BY total desc;
";


echo str_repeat("_", 80) .PHP_EOL."Calculando os totais de contas com o mesmo CPF".PHP_EOL.PHP_EOL.$sql.PHP_EOL;
$rs_dados_cpf = SQLexecuteQuery($sql);
        
//Verificando Dados
$msg = PHP_EOL."Total de CPF considerados com contas ativas de GAMERS:  [".pg_num_rows($rs_dados_cpf)."] CPFs<br>";
echo $msg.PHP_EOL.PHP_EOL;
$total_cpfs_na_tabela = 0;
$total_cpfs_ainda_nao_na_tabela = 0;
$msg_cpf_nao_em_cache = "";
while($rs_dados_cpf_row = pg_fetch_array($rs_dados_cpf)) {
    
    //Verificando se dado já existe na tabela de CACHE
    $cpf_sem_mascara = preg_replace('/[^0-9]/', '', $rs_dados_cpf_row['ug_cpf']);
    if(!empty($cpf_sem_mascara)) {
            $sql = "UPDATE cpf_cache SET qtde_contas = ".$rs_dados_cpf_row['total']." where cpf = ".$cpf_sem_mascara.";";
            echo $sql.PHP_EOL;
            $rs_existe = SQLexecuteQuery($sql);
            $cmdtuples = pg_affected_rows($rs_existe);
            if($cmdtuples===1) {

                echo " CPF [".$rs_dados_cpf_row['ug_cpf']."] atualizado com SUCESSO".PHP_EOL;
                $total_cpfs_na_tabela++;

            } //end if($cmdtuples===1)
            else {
                $aux_msg = " CPF [".$rs_dados_cpf_row['ug_cpf']."] ainda NÃO existe na tabela";
                //$msg_cpf_nao_em_cache .= $aux_msg."<br>";
                echo $aux_msg.PHP_EOL;
                $total_cpfs_ainda_nao_na_tabela++;

            } //end else do if($cmdtuples===1)
    }//end if(!empty($cpf_sem_mascara))
    else echo "====> Problema no CPF [".$rs_dados_cpf_row['ug_cpf']."]".PHP_EOL;
    
} //end while

$msg =  PHP_EOL.PHP_EOL."<b>RESUMO GERAL</b>".PHP_EOL.PHP_EOL.$msg.PHP_EOL.PHP_EOL."Total de CPFs NÃO localizados na tabela de CACHE: <b>".$total_cpfs_ainda_nao_na_tabela."</b>".PHP_EOL.PHP_EOL; //."Lista de CPFs não encontrado na tabela de cache:".PHP_EOL.$msg_cpf_nao_em_cache.PHP_EOL.PHP_EOL;
$subject= (checkIP()?"[DEV - HOMOLOGAÇÃO]":"[PROD]") . " Levantamento de Quantidade de Contas com o mesmo CPF";

echo str_replace('<br>', PHP_EOL, $msg);
$cc     = "glaucia@e-prepag.com.br";

if(!empty($msg)) {
    if(enviaEmail($email, $cc, $bcc, $subject, str_replace(PHP_EOL,'<br>', $msg))) {
        echo "Email enviado com sucesso".PHP_EOL;
    }
    else {
        echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
    }
}//end if(!empty($msg))

echo str_repeat("_", 80) .PHP_EOL."Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80).PHP_EOL;

//Fechando Conexão
pg_close($connid);

?>