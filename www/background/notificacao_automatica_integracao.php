<?php
header("Content-Type: text/html; charset=ISO-8859-1", true);
// Notificaï¿½ï¿½o Automaticamente de Pedidos de Integraï¿½ï¿½o
// \backoffice\offweb\tarefas\notificacao_automatica_integracao.php 
// 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";

$arquivoControle = '/www/arquivos_gerados/logs/ultimo_email_int_pedidos.txt';
$intervaloEmSegundos = 2 * 60 * 60;

$arquivoLog = new ManipulacaoArquivosLog($argv);

if (!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');


    $time_start_stats = getmicrotime();

    // Parametros
    $qtde_minutos_considerados = 3; //jï¿½ conciliado
    $qtde_meses_considerados_apartirde = 1; //qtde de meses considerado na pesquisa
    $subject = "Notificação Automatica de Pedidos de Integração";
    $email = getenv("email_suporte");
    $cc = "glaucia@e-prepag.com.br,jose.carlos@easygroupit.com"; //"glaucia@e-prepag.com.br";
    $bcc = "";
    $msg = "";

    //echo PHP_EOL.str_repeat("=", 80).PHP_EOL."NotificaÃ§Ã£o Automaticamente de Pedidos de IntegraÃ§Ã£o com mais de ".$qtde_minutos_considerados." Minutos jÃ¡ conciliados (".date("Y-m-d H:i:s").")".PHP_EOL.str_repeat("=", 80).PHP_EOL;

    $sql = "
    select  ip.* , 
            vg.vg_ultimo_status, 
            vg.vg_pagto_tipo,
            vg.vg_data_concilia,
            (NOW() - vg_data_concilia) > INTERVAL '30 minutes' AS passou_30_min,
            coalesce(vg_id, 0) as vg_id
    from tb_integracao_pedido ip 
        left outer join tb_venda_games vg on ip.ip_vg_id = vg.vg_id 
    where (ip.ip_data_inclusao > NOW() - '" . $qtde_meses_considerados_apartirde . " month'::interval) 
        and (vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . ") 
        and (vg.vg_data_concilia < NOW() - '" . $qtde_minutos_considerados . " minutes'::interval) 
        and coalesce(vg_id, 0)>0 
        and not (exists( 
                        select iph_ip_status_confirmed 
                        from tb_integracao_pedido_historico iph 
                        where iph.iph_ip_id = ip.ip_id 
                        and iph.iph_ip_store_id = ip.ip_store_id 
                        and iph.iph_ip_order_id = ip.ip_order_id 
                        and iph.iph_ip_vg_id = ip.ip_vg_id 
                        and iph.iph_ip_status_confirmed = 1 
                        )) 
    order by ip_data_inclusao desc 

    ";
    // echo "SQL para levantamento de Pedidos a serem notificados automï¿½ticamente:".PHP_EOL.$sql.PHP_EOL;
    //die();
    $rs = SQLexecuteQuery($sql);
    $n_updates = pg_num_rows($rs);
    // echo "Encontrado".(($n_updates>1)?"s":"")." : ".$n_updates." Registro".(($n_updates>1)?"s":"")." para serem notificados manualmente".PHP_EOL;

    if (!$rs || pg_num_rows($rs) == 0) {
        // echo  "Nenhum pedido selecionado".PHP_EOL;
    } else {
        // echo "Pedidos que serï¿½o considerados nestas notificaï¿½ï¿½es:".PHP_EOL;

        while ($rs_row = pg_fetch_array($rs)) {

            // Get parameters
            $post_parameters = "store_id=" . $rs_row["ip_store_id"] . "&";

            $post_parameters .= "transaction_id=" . $rs_row["ip_transaction_id"] . "&";
            $post_parameters .= "order_id=" . $rs_row["ip_order_id"] . "&";
            $post_parameters .= "amount=" . $rs_row["ip_amount"] . "&";
            if (strlen($rs_row["ip_product_id"]) > 0) {
                $post_parameters .= "product_id=" . $rs_row["ip_product_id"] . "&";
            }
            $post_parameters .= "client_email=" . $rs_row["ip_client_email"] . "&";
            $post_parameters .= "client_id=" . $rs_row["ip_client_id"] . "&";

            $post_parameters .= "currency_code=" . $rs_row["ip_currency_code"] . "";

            // Do notify
            $notify_url = getPartner_notify_url_By_ID($rs_row["ip_store_id"]);
            // echo "URL:".$notify_url.PHP_EOL;
            // echo "Parameters: ".$post_parameters.PHP_EOL;

            $http_code = 0;
            $sret = getIntegracaoCURL($notify_url, $post_parameters, $http_code);

            echo "$sret $post_parameters<br>";

            if ($buffer == false || $http_code != 200 || $rs_row['passou_30_min']) {
                $msg .= " Pedido " . $rs_row['vg_id'] . " => Conciliado em [" . $rs_row['vg_data_concilia'] . "] => Tipo de pagamento [" . $rs_row['vg_pagto_tipo'] . "]; <br>" . PHP_EOL;
            }
        } //end while

        //Enviando email
        if (!empty($msg)) {
            $envia_email = true;
            if (file_exists($arquivoControle)) {
                $ultimoEnvio = (int) file_get_contents($arquivoControle);
                $agora = time();

                if (($agora - $ultimoEnvio) < $intervaloEmSegundos) {
                    echo "Ainda não passaram 2 horas. Email não enviado.";
                    $envia_email = false;
                }
            }
            if ($envia_email) {
                $msg_head = "<html>Pedidos que serão considerados nestas notificações: Total [" . $n_updates . "]<br><br>";
                $msg .= "<br><br></html>";
                $msg_final = $msg_head . $msg;
                if (enviaEmail4($email, $cc, $bcc, $subject, $msg_final, $msg_final, null, false, '', $cc)) {
                    file_put_contents($arquivoControle, time());
                    //  echo "Email enviado com sucesso".PHP_EOL;
                } else {
                    echo "Problemas no envio do Email\n TO: " . $email . PHP_EOL . " CC: " . $cc . PHP_EOL . " BCC: " . $bcc . PHP_EOL . " SUBJECT: " . $subject . PHP_EOL;
                }
            }
        } //end if(!empty($msg))

    } //end else do if(!$rs || pg_num_rows($rs) == 0)
    // echo PHP_EOL.str_repeat("_", 80) .PHP_EOL."Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) .PHP_EOL;

    $arquivoLog->deleteLockedFile();
} else {
    $arquivoLog->showBusy();
}

//Fechando Conexï¿½o
pg_close($connid);
