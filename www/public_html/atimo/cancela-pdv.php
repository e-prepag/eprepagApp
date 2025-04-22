<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
// Cancela automáticamente pedidos de LAN House com mais de $qtde_minutos_considerados Minutos e não processados
// \background\cancelamento_pedido_LH.php 
// 

error_reporting(E_ALL); 
ini_set("display_errors", 1); 
$raiz_do_projeto = "/www/";
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";

$time_start_stats = getmicrotime();


    // Parametros
    $qtde_minutos_considerados = 10;
    $msg_sistema = "Pedido Cancelado Automaticamente pelo Sistema";
    $msg_para_usuario = "Pedido cancelado automaticamente: tempo de processamento expirado. Caso tenha necessidade, por gentileza, faça o pedido novamente.";
    $bcc = "wagner@e-prepag.com.br";
    $lista_VG_IDS = "";
    $msg = "";

    echo PHP_EOL.str_repeat("=", 80).PHP_EOL.PHP_EOL."Cancelamento automáticamente pedidos de LAN House com mais de ".$qtde_minutos_considerados." Minutos e não processados (".date("Y-m-d H:i:s").")".PHP_EOL.str_repeat("=", 80).PHP_EOL;

    $sql = "
    select vg_id, vg_data_inclusao
    from tb_dist_venda_games 
    where vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['AGUARDANDO_PROCESSAMENTO']." 
            and vg_deposito_em_saldo = 0
            and vg_data_inclusao < (NOW() -'".$qtde_minutos_considerados." minutes'::interval)
    order by vg_data_inclusao;";
    
    echo "SQL para levantamento de IDS a serem considerados no cancelamento:".PHP_EOL.$sql.PHP_EOL;
    
    $rs = SQLexecuteQuery($sql);
    $n_updates = pg_num_rows($rs);
    echo "Encontrado".(($n_updates>1)?"s":"")." : ".$n_updates." Registro".(($n_updates>1)?"s":"")." para serem verifidos e atualizados".PHP_EOL;

    if(!$rs || pg_num_rows($rs) == 0) {
            $msg = "Nenhum usuários selecionado".PHP_EOL;
    } else {
            echo "Pedidos que serão considerados neste cancelamento:".PHP_EOL;
            while($rs_row = pg_fetch_array($rs)) {
                $lista_VG_IDS .= (($lista_VG_IDS)?", ":"").$rs_row['vg_id'];
                echo " Pedido ".$rs_row['vg_id']."\t\t=> Gerado em [".$rs_row['vg_data_inclusao']."];".PHP_EOL;
            }//end while
            //echo $lista_VG_IDS.PHP_EOL;
            $sql = "
            update tb_dist_venda_games
            set vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'].",
                vg_usuario_obs = '".$msg_para_usuario."',
                vg_ultimo_status_obs = '".$msg_sistema."'
            where vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['AGUARDANDO_PROCESSAMENTO']." 
                    and vg_deposito_em_saldo = 0
                    and vg_id IN (".$lista_VG_IDS.")
                    and vg_data_inclusao < (NOW() -'".$qtde_minutos_considerados." minutes'::interval)
            ";
            echo "SQL de atualização dos pedidos para cancelados: ".PHP_EOL.$sql.PHP_EOL;
            $ret = SQLexecuteQuery($sql);
            if(!$ret) echo "Erro ao Cancelar/Atualizar os Pedidos [".$lista_VG_IDS."]".PHP_EOL;
            else {
                $vetor_VG_IDS = explode(",", $lista_VG_IDS);
                foreach ($vetor_VG_IDS as $key => $value) {
                    echo "Venda cancelada ".$value.".".PHP_EOL;
                    //Mensagem
                    $msgEmail = "<br>
                                <table border='0' cellspacing='0' width='90%'>
                                <tr>
                                        <td class='texto'> 
                                                <b>Pedido cancelado pelo motivo descrito abaixo:</b><br><br>
                                                ".$msg_para_usuario."
                                        </td>
                                </tr>
                                </table>";
                    $retEmail = enviaEmailFormatadoComProdutos($value, null, null, $bcc, "E-Prepag - ".$value." - Cancelado", $msgEmail);
                    if($retEmail == "") echo "Envio de email: Enviado com sucesso.".PHP_EOL;
                    else echo "Envio de email: $retEmail ".PHP_EOL;
                }//end foreach
            }//end else do if(!$ret)
    }//end else do if(!$rs || pg_num_rows($rs) == 0)
    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL."Elapsed time (total: ".count($vetor_VG_IDS)."): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) .PHP_EOL;

//Fechando Conexão
pg_close($connid);

?>