<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";

$time_start_stats = getmicrotime();

// para evitar conflitos no processamento
$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');
    
    // Dados do Email
    $email  = "felipe.farias@easygroupit.com";
    $cc     = "glaucia@e-prepag.com.br";
    $bcc    = "";
    $subject= "Ajuste de IP_ID referente a Integração";
    $msg    = "";

    //Período de tempo entre datas de integração e pedido considerado no Levantamento
    define('PERIODO_CONSIDERADO', '1 minute');
    
    //Período de tempo deconsiderado no Levantamento para não contabilizar vendas ainda em Processamento
    define('PERIODO_DESCONSIDERADO', '2 hours');
    
    //Data considerada na verificação (à partir de)
    define('DATA_INICIAL', '2017-08-01 00:00:00');
    
    //Geração de LOG
    echo PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.$subject.PHP_EOL.str_repeat("_", 80).PHP_EOL.PHP_EOL;
    
    $sql = " 
        select ip_vg_id,
                ip_transaction_id,
                ip_id,
                vg_id,
                vg_ultimo_status_obs,
                vg_ultimo_status,
                vg_data_inclusao,
                vg_pagto_valor_pago,
                vg_pagto_tipo
        from tb_integracao_pedido
                inner join usuarios_games ON UPPER(ug_email)=UPPER(ip_client_email)
                inner join tb_venda_games ON vg_ug_id = ug_id
                inner join tb_pag_compras ON idvenda = vg_id
        where
                ip_vg_id = 0
                and vg_ultimo_status = 5
                and vg_deposito_em_saldo = 0
                and vg_integracao_parceiro_origem_id IS NOT NULL
                and vg_integracao_parceiro_origem_id != ''
                and ip_data_inclusao >= '".DATA_INICIAL."'
                and ip_data_inclusao > (vg_data_inclusao - '".PERIODO_CONSIDERADO."'::interval)
                and ip_data_inclusao < (vg_data_inclusao + '".PERIODO_CONSIDERADO."'::interval)
                and ip_data_inclusao < (NOW() - '".PERIODO_DESCONSIDERADO."':: interval)
                and vg_ultimo_status_obs like '%ip_id: 0,%'
                and vg_pagto_tipo != 2
                and tipo_cliente = 'M'
                and (total-(taxas*100))::int = ip_amount::int
        group by ip_vg_id,ip_transaction_id,ip_id,vg_id,vg_ultimo_status_obs,vg_ultimo_status,vg_data_inclusao,vg_pagto_valor_pago,vg_pagto_tipo	
        order by vg_data_inclusao desc	
             ";
    
    echo $sql.PHP_EOL.PHP_EOL;
    $rs_dados_levantamento = SQLexecuteQuery($sql);
    
    $total_geral_taxa = 0;
    while($rs_dados_levantamento_row = pg_fetch_array($rs_dados_levantamento)) {

            $msg .= " VG_ID [".$rs_dados_levantamento_row['vg_id']."] - IP_ID [".$rs_dados_levantamento_row['ip_id']."] -  Valor Pago  R$ [".number_format($rs_dados_levantamento_row['vg_pagto_valor_pago'],2,",",".")."] => Tipo de Pagamento [".$rs_dados_levantamento_row['vg_pagto_tipo']."] ".PHP_EOL;
            $total_geral_taxa += $rs_dados_levantamento_row['vg_pagto_valor_pago'];


            //inicio do bloco que utiliza o VG_ID/IP_ID na integração
            $msg_controle_transaction = "";
            $sql_begin = "BEGIN TRANSACTION ";
            $ret_begin = SQLexecuteQuery($sql_begin);
            if(!$ret_begin) $msg_controle_transaction .= "Erro ao iniciar transação.";
            
            $sql_update_integracao = "UPDATE tb_integracao_pedido SET ip_vg_id = ".$rs_dados_levantamento_row['vg_id'].", ip_transaction_id = '".$rs_dados_levantamento_row['vg_id']."' WHERE ip_id=".$rs_dados_levantamento_row['ip_id'].";";
            echo "SQL de UPDATE na tabela de integração: ".PHP_EOL.$sql_update_integracao.PHP_EOL;
            $rs_update_integracao = SQLexecuteQuery($sql_update_integracao);
            if($rs_update_integracao) {

                    $sql_update_pedido = "UPDATE tb_venda_games SET vg_ultimo_status_obs=replace( vg_ultimo_status_obs, 'ip_id: 0,', 'ip_id: ".$rs_dados_levantamento_row['ip_id'].",') WHERE vg_id=".$rs_dados_levantamento_row['vg_id'].";";
                    echo "SQL que Atualiza o Pedido: ".PHP_EOL.$sql_update_pedido.PHP_EOL;
                    $rs_update_pedido = SQLexecuteQuery($sql_update_pedido);
                    if(!$rs_update_pedido) {
                            $msg .=  "<br><br><br><b>Problema ao tentar Atualizar o Pedido[".$rs_dados_levantamento_row['vg_id']."].</b><br><br><br><br>";
                            $msg_controle_transaction = "Problema ao tentar Atualizar o Pedido[".$rs_dados_levantamento_row['vg_id']."]";
                    } //end if($rs_update_pedido) 
                    
            }//end if($rs_update_integracao)
            else {
                    $msg .=  "<br><br><br><b>Problema ao tentar atualizar o registro da Integração.</b><br><br><br><br>";
                    $msg_controle_transaction = "Problema ao atualizar o registro da Integração";
                    echo "Problema ao tentar atualizar o registro da Integração.".PHP_EOL.PHP_EOL.PHP_EOL;
            } //end else
            
            if($msg_controle_transaction == ""){
                    $sql_commit = "COMMIT TRANSACTION ";
                    $ret_commit = SQLexecuteQuery($sql_commit);
                    if(!$ret_commit) $msg .= "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.\n<br></b></font><br>";
            }//end commit 
            else {
                    $sql_commit = "ROLLBACK TRANSACTION ";
                    echo PHP_EOL.str_repeat("=",10)."> ".$sql_commit.PHP_EOL.PHP_EOL;
                    $msg .= str_repeat("=",10)."> ".$sql_commit.PHP_EOL;
                    $ret_commit = SQLexecuteQuery($sql_commit);
                    if(!$ret_commit) $msg .= "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
            }//end rollback
            //final do bloco que utiliza o VG_ID/IP_ID na integração

            echo PHP_EOL.str_repeat("-",40).PHP_EOL.PHP_EOL;

    } //end while
    $msg .=  PHP_EOL.PHP_EOL."<b>RESUMO GERAL</b>".PHP_EOL.PHP_EOL."Total do Volume Envolvido: <b>R$ ".number_format($total_geral_taxa,2,",",".")." </b><br><br>".PHP_EOL.PHP_EOL;

    echo strip_tags(str_replace('<br>', PHP_EOL, $msg));

    if(!empty($msg) && $total_geral_taxa > 0) {
        if(enviaEmail($email, $cc, $bcc, $subject, str_replace(PHP_EOL,'<br>'.PHP_EOL, $msg))) {
            echo "Email enviado com sucesso".PHP_EOL;
        }
        else {
            echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
        }
    }//end if(!empty($msg))

    
    //Geração de LOG
    echo PHP_EOL.str_repeat("_", 80).PHP_EOL."Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

?>
