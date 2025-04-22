<?php
// Programa de totalizaчуo de Saldo Diсrio 
// totalizador_saldo_diario.php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";

$time_start_stats = getmicrotime();

$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');
    
    echo PHP_EOL.str_repeat("=", 80).PHP_EOL."    Programa de totalizaчуo de Saldo Diсrio  (".date("Y-m-d H:i:s").").".PHP_EOL.PHP_EOL;

    $sql = "select sum(scf_valor_disponivel) as total_pendente from saldo_composicao_fifo;";
    echo "SQL Gamer:".PHP_EOL.$sql.PHP_EOL.PHP_EOL;
    $rs = SQLexecuteQuery($sql);

    if(!$rs || pg_num_rows($rs) == 0) {
            echo "Erro ao selecionar saldo de Gamer: ".$sql.PHP_EOL;
    } else {
            //Recupera totais de Saldo pendente de Gamers
            $listaPedidos = "";   
            $qtde_saldo_users_gamer = 0;
            $rs_gamer_row = pg_fetch_array($rs);
            if($rs_gamer_row) {
                   $qtde_saldo_users_gamer	= $rs_gamer_row['total_pendente']; 
            }

            //Recupera totais de Saldo pendente de LANs Prщ
            $qtde_saldo_users_lan = 0;
            $sql  = "select  sum(ug_perfil_saldo) as saldo_total from dist_usuarios_games where ug_risco_classif = 2 and ug_perfil_saldo>0;";
            echo "SQL LAN:".PHP_EOL.$sql.PHP_EOL.PHP_EOL;
            $rs_lan = SQLexecuteQuery($sql);
            if(!$rs_lan || pg_num_rows($rs_lan) == 0) {
                    echo "Erro ao selecionar saldo de LAN: ".$sql.PHP_EOL;
            } else {
                    $rs_lan_row = pg_fetch_array($rs_lan);
                    if($rs_lan_row) {
                            $qtde_saldo_users_lan	= $rs_lan_row['saldo_total']; 
                    }
                    echo "Data e Totais considerados neste Processamento:".PHP_EOL."Total de Saldo Gamer R$ ".number_format($qtde_saldo_users_gamer, 2, ',', '.').PHP_EOL."Total de Saldo LAN R$ ".number_format($qtde_saldo_users_lan, 2, ',', '.').PHP_EOL.PHP_EOL;
                    $sql = "INSERT INTO COMPLIANCE_TOTAL_SALDO_DIARIO VALUES ('".date('Y-m-d 00:00:00')."',$qtde_saldo_users_gamer,$qtde_saldo_users_lan);";
                    echo "SQL do INSERT: ".$sql.PHP_EOL.PHP_EOL;
                    $ret = SQLexecuteQuery($sql);
                    if(!$ret) echo "Erro ao Inserir o Registro [".$sql."]".PHP_EOL;
                    else {
                       echo "Sucesso ao Inserir o Registro ".PHP_EOL; 
                    }//end else do if(!$ret) 
            } //end else do if(!$rs_lan || pg_num_rows($rs_lan) == 0)

    }//end else do if(!$rs || pg_num_rows($rs) == 0)
    echo str_repeat("_", 80) .PHP_EOL."Elapsed time (total: ".count($vetor_ug_id)."): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80).PHP_EOL;
    
    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

//Fechando Conexуo
pg_close($connid);
?>