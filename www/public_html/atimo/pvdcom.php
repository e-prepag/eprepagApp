<?php
// Destravamento de Pedidos de LANs Bloqueados com Status 4
// unlock_orders_PDV.php 

error_reporting(E_ALL); 
ini_set("display_errors", 1); 

// include do arquivo contendo IPs DEV
$raiz_do_projeto = "/www/";
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
$time_start_stats = getmicrotime();

//$arquivoLog = new ManipulacaoArquivosLog($argv);

//if(!$arquivoLog->haveFile()) {
 //   $arquivoLog->createLockedFile();
 //   $nome_arquivo = $arquivoLog->getNomeArquivo();

   // ob_start('callbackLog');
    echo PHP_EOL.str_repeat("=", 80).PHP_EOL."    Destravamento de Pedidos de LANs Bloqueados com Status 4 (".date("Y-m-d H:i:s").").".PHP_EOL.PHP_EOL;

    $time_start_stats = getmicrotime();

    // Dados do Email
    $email  = "tamy@e-prepag.com.br";
    $cc     = "glaucia@e-prepag.com.br";
    $bcc    = "wagner@e-prepag.com.br";
    $subject= "Destravamento de Pedidos de LANs Bloqueados com Status 4";
    $msg    = "";

    $sql = "select vg_id
    from tb_dist_venda_games 
    where vg_ultimo_status = '".$GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO']."' 
    and vg_data_inclusao >= NOW()- '6 months'::interval 
    and vg_data_inclusao <= NOW()- '15 minutes'::interval ;";
    echo $sql.PHP_EOL.PHP_EOL;
    $rs = SQLexecuteQuery($sql);
    $n_updates = pg_num_rows($rs);
    echo "Encontrado".(($n_updates>1)?"s":"")." : ".$n_updates." Pedidos".(($n_updates>1)?"s":"")." para serem atualizados".PHP_EOL;

    if(!$rs || pg_num_rows($rs) == 0) {
            echo "Nenhum Pedido Selecionado".PHP_EOL;
    } else {
            // Gerando a lista de Pedidos
            $listaPedidos = "";   
            while($rs_row = pg_fetch_array($rs)) {
                if(strlen($listaPedidos) == 0) {
                    $listaPedidos = $rs_row['vg_id'];
                }
                else {
                    $listaPedidos .= ",".$rs_row['vg_id'];
                }
            }//end while

            if(strlen($listaPedidos) == 0) {
                echo "Ocorreu algum erro na query de seleção de pedidos.".PHP_EOL;
            } //end if(strlen($listaPedidos) == 0)
            else {
                $msg = "Pedidos de LANs considerados neste Desbloqueio [".$listaPedidos."]".PHP_EOL.PHP_EOL;
                echo $msg;
                $sql = "
                    update tb_dist_venda_games 
                    set vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'].",
                            vg_ultimo_status_obs = 'Ajuste Automático para Venda Realizada (".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'].") em ".date('d/m/Y - H:i:s')."', 
                            vg_pagto_num_docto = 'Automático'
                    where vg_id in (".$listaPedidos.");";
                    echo "SQL do Update: ".$sql.PHP_EOL.PHP_EOL;
                    $ret = SQLexecuteQuery($sql);
                    if(!$ret) echo "Erro ao Atualizar os Pedidos [".$listaPedidos."]".PHP_EOL;
                    else {
                        if(enviaEmail($email, $cc, $bcc, $subject, $msg)) {
                            echo "Email enviado com sucesso".PHP_EOL;
                        }
                        else {
                            echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
                        }
                    }//end else do if(!$ret) 
            } //end else do if(strlen($listaPedidos) == 0)

    }//end else do if(!$rs || pg_num_rows($rs) == 0)
    echo str_repeat("_", 80) .PHP_EOL."Elapsed time (total: ".count($vetor_ug_id)."): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80).PHP_EOL;

  //  $arquivoLog->deleteLockedFile();
//}
//else {
  //  $arquivoLog->showBusy();
//}

//Fechando Conexão
pg_close($connid);    
?>