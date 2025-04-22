<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
// Checagem de Pedidos de PDVs em relação a Quantidade de PINs disponibilizados e Quantidade de PINs solicitados
// checagemPedidosPDVs.php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(1200);
ini_set('max_execution_time', 1200); 

//Data considerada
$data_inicio = mktime(0, 0, 0, date('n'),  date('d')-1, date('Y'));
$data_fim = mktime(0, 0, 0, date('n'),  date('d'), date('Y'));
$dataClickIni = date('Y-m-d',$data_inicio);
$dataClickFim = date('Y-m-d',$data_fim);

// Dados do Email
$email  = "suporte@e-prepag.com.br";
$cc     = "glaucia@e-prepag.com.br";
$bcc    = "wagner@e-prepag.com.br";
$subject= "Pedidos PDVs com Diferenças de PINs";
$msg    = "";

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 

$time_start_stats = getmicrotime();

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Checagem de Pedidos de PDVs em relação a Quantidade de PINs disponibilizados e Quantidade de PINs solicitados (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

//Buscando Pedidos com Problemas
$sql = "
SELECT COUNT(DISTINCT(pin_codinterno)) AS total_pins, 
	(SELECT SUM(vgm_qtde) FROM tb_dist_venda_games_modelo WHERE vgm_vg_id = vg.vg_id) AS total_pedido, 
	vg_id,
	vg_data_inclusao
FROM tb_dist_venda_games vg 
        INNER JOIN tb_dist_venda_games_modelo vgm ON  vg.vg_id = vgm.vgm_vg_id
        LEFT OUTER JOIN tb_dist_venda_games_modelo_pins vgmp ON vgmp.vgmp_vgm_id = vgm.vgm_id    
        LEFT OUTER JOIN pins_dist p ON vgmp.vgmp_pin_codinterno = p.pin_codinterno
WHERE vg.vg_data_inclusao >= '".$dataClickIni." 00:00:00'
	AND vg.vg_data_inclusao <= '".$dataClickFim." 23:59:59'
	AND vg.vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."
GROUP BY vg_id,vg_data_inclusao   
HAVING COUNT(DISTINCT(pin_codinterno)) <> (SELECT sum(vgm_qtde) FROM tb_dist_venda_games_modelo WHERE vgm_vg_id = vg.vg_id)
ORDER BY vg_data_inclusao DESC;
";

echo "SQL :".$sql.PHP_EOL;

$rs = SQLexecuteQuery($sql);
$exibicaoDadosProblemas = NULL;
if($rs) {
    $total_de_registros = pg_num_rows($rs);
    if($total_de_registros > 0) {
        $cabecalho = "
        <table class='table table-bordered top20' border='1' >
        <thead class=''>
            <tr bgcolor='navy'>
                <th colspan='4'><font color='white'>Total Pedidos com Problemas Levantados: ".$total_de_registros."</font></th>
            </tr>
            <tr bgcolor='navy'>
                <th><font color='white'>Qtde PINs Disponibilizado no Pedido</font></th>
                <th><font color='white'>Qtde PINs Original do Pedido</font></th>
                <th><font color='white'>Número do Pedido</font></th>
                <th><font color='white'>Data do Pedido</font></th>
            </tr>
        </thead>
         ";
        while ($rsRow = pg_fetch_array($rs)) {

            $exibicaoDadosProblemas .= " 
                <tr class='trListagem'>
                    <td align='right'>".$rsRow['total_pins']."</td>
                    <td align='right'>".$rsRow['total_pedido']."</td>
                    <td align='right'><a href='https://www.e-prepag.com.br:8080/pdv/vendas/com_venda_detalhe.php?venda_id=".$rsRow['vg_id']."'>".$rsRow['vg_id']."</a></td>
                    <td align='center'>".substr($rsRow['vg_data_inclusao'],0,19)."</td>
                </tr>
             ";
        }//end while 
        $exibicaoDadosProblemas .= "</table>";
        $msg .= "Período Considerado $dataClickIni até $dataClickFim.<br>".PHP_EOL;
        $msg .= $cabecalho.$exibicaoDadosProblemas."</table>";
        echo strip_tags($msg).PHP_EOL;

        if(enviaEmail($email, $cc, $bcc, $subject, $msg)) {
            echo "Email enviado com sucesso".PHP_EOL;
        }
        else {
            echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
        }
    }//end if(pg_num_rows($rs) > 0)
    else {
            echo "Nenhum Pedido com Problemas Encontrado.".PHP_EOL;
    }//end else do if(pg_num_rows($rs) > 0) 
}//end if($rs) 
else echo "ERRO na query acima.".PHP_EOL;

echo str_repeat("_", 80) .PHP_EOL."Elapsed time (total: ".count($vetor_ug_id)."): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80).PHP_EOL;

//Fechando Conexão
pg_close($connid);

?>