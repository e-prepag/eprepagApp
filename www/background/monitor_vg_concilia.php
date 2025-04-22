<?php 
ob_start(); 
set_time_limit(1200);
ini_set('max_execution_time', 1200); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
$time_start_stats = getmicrotime();

$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');
    echo PHP_EOL."Data execuчуo : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

    //INICIO Bloco Monitor VG Concilia
    // Lista vendas completas com vg_concilia = 0 ou vg_data_concilia = null
    $sql = "
        select vg_concilia, vg_data_concilia, vg_id, vg_ug_id, vg_data_inclusao   
        from tb_venda_games vg
        where vg_ultimo_status = 5
                and ((not vg_concilia = 1) or vg_data_concilia is null)
        order by vg_data_inclusao desc;";	

    $smonitor = "Monitor vg_concilia: ".date("Y-m-d H:i:s").PHP_EOL;
    $rs_vendas = SQLexecuteQuery($sql);
    if(!$rs_vendas || pg_num_rows($rs_vendas) == 0) {
            $smonitor .= "Nenhum registro encontrado com problemas.".PHP_EOL;
    } else {
            $smonitor .= "Encontrados ".pg_num_rows($rs_vendas)." registros com problemas".PHP_EOL;
            while($rs_vendas_row = pg_fetch_array($rs_vendas)){ 
                if($smonitor) $smonitor .= ", ";
                $smonitor .= $rs_vendas_row["vg_id"];
            }
            echo PHP_EOL;
    }

    gravaLog_MonitorConcilia($smonitor);

    echo "Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL;
    //FIM Bloco Monitor VG Concilia

    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL. "Final de execuчуo em: ". date('Y-m-d H:i:s'). PHP_EOL. "Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

//Fechando Conexуo
pg_close($connid);


function gravaLog_MonitorConcilia($mensagem){
    global $raiz_do_projeto;

    // Salva o file monitor para mostrar no Backoffice
    try {
            if ($handle = fopen($raiz_do_projeto.'log/monitor_concilia.txt', 'w')) { 
                    fwrite($handle, $mensagem.PHP_EOL);

                    fclose($handle);
            } else {
                    echo PHP_EOL."Error: Couldn't open Monitor File for writing".PHP_EOL;
            }
    } catch (Exception $e) {
            echo "Error(6) writing monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage().PHP_EOL;
    }

} //end function gravaLog_MonitorConcilia
?>