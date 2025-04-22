<?php 
echo PHP_EOL.date("Y-m-d H:i:s").PHP_EOL.str_repeat("_", 80).PHP_EOL;

set_time_limit ( 18000 ) ;
require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";

$time_start_stats = getmicrotime();

/* ===========================================================================================================
Lista lans que devem mudar de status de busca.
Regras 2017-02-08: 
 
Regra ===> Para aparecer no mapa o Ponto de Venda deve ter :
    * Ativar a busca para todas as lans que se CADASTRARAM nos ъltimos 2 meses;
    * Ativar a busca para todas as lans que efetuaram LOGIN nos ъltimos 3 meses (Condiзгo OU para regra acima);
    * Status: Ativo;
    * Substatus: Lan House Aprovada;
    * Lan com email diferente(BILHETERIA.COM,E-PREPAG.COM.BR,E-PREPAG);
    * Lan nгo constar na BlackList;
    * "Possui Restriзгo de Vendas de Produtos" no status "Nгo".
 =========================================================================================================== */

/* =========================================================================================================== 
 *
 *  Zerando todas os PDVs em relaзгo a busca antes de aplicar a Regra
 * 
=========================================================================================================== */

$sql = "update dist_usuarios_games set ug_status = 2; ";
$rs_execute = SQLexecuteQuery($sql);
if($rs_execute) echo "PDVs zerados para busca com sucesso!".PHP_EOL;
else echo "Problema na Query ao tentar zerar PDVs para busca com sucesso!".PHP_EOL.$sql.PHP_EOL;

/* =========================================================================================================== 
 *
 *  Regra => Inнcio
 * 
=========================================================================================================== */

$sql = "
SELECT ug_id 
FROM dist_usuarios_games
WHERE ( ug_data_inclusao >= (NOW() - interval '".$CONST_ATIVA_BUSCA_LANS_MESES_APOS_CADASTRO_PARA_ATIVAR." month')
    OR ug_data_ultimo_acesso >= (NOW() - interval '".$CONST_ATIVA_BUSCA_LANS_MESES_APOS_ULTIMO_LOGIN." month') )
    AND ug_ativo = 1
    AND (ug_substatus = 11)
    AND (strpos(upper(ug_email), 'E-PREPAG') = 0)
    AND (strpos(upper(ug_email), 'BILHETERIA.COM') = 0)
    AND ug_id NOT IN  (".$CONST_ATIVA_BUSCA_LANS_BLACK_LIST.")
    AND ug_tipo_venda != '1'
    -- AND ug_possui_restricao_produtos = 0; ";	

echo PHP_EOL.$sql.PHP_EOL;
$ids = "";
$rs_lans = SQLexecuteQuery($sql);
while($rs_lans_row = pg_fetch_array($rs_lans)){ 
        $ids .= (($ids)?", ":"")." ".$rs_lans_row["ug_id"];
        $sql = "update dist_usuarios_games set ug_status = 1 where ug_id = ".$rs_lans_row["ug_id"]."; ";
        $rs_execute = SQLexecuteQuery($sql);
}//end while
echo str_repeat("=",80).PHP_EOL."REGRA: Lista de LANs ativadas para busca [".$ids."]".PHP_EOL.str_repeat("=", 80).PHP_EOL.PHP_EOL;


echo PHP_EOL.str_repeat("_", 80).PHP_EOL."Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80).PHP_EOL;

//Fechando Conexгo
pg_close($connid);
?>