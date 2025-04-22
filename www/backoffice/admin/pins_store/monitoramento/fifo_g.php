<?php
if(!function_exists("convert_secs_to_string_global"))
{
        function convert_secs_to_string_global($n) {
            $sout = "";
            $ndays = 0;
            $nhours = 0;
            $nmins = 0;
            $nsecs = 0;

            $ndays = intval($n/(60*60*24));
            $nhours = intval(($n-$ndays*60*60*24)/(60*60));
            $nmins = intval(($n-$ndays*60*60*24-$nhours*60*60)/(60));
            $nsecs = intval(($n-$ndays*60*60*24-$nhours*60*60-$nmins*60));


            $sout .= "<font size='1'>";
            $sout .= (($ndays>0)?$ndays."<font color='#FF0000'>d</font>":"");
            $sout .= (($ndays>0 || $nhours>0)?$nhours."<font color='#FF0000'>h</font>":"");
            $sout .= (($ndays>0 || $nhours>0 || $nmins>0)?$nmins."<font color='#FF0000'>m</font>":"");
            $sout .= (($ndays>0 || $nhours>0 || $nmins>0 || $nsecs>0)?$nsecs."<font color='#FF0000'>s</font>":"");
            $sout .= "</font>";

            return $sout;
    }
}

//Recupera totais de Saldo pendente e Saldo utilizado
$qtde_saldo_pendente_fifo = 0;
$qtde_saldo_total_movimentado_fifo = 0;
$sql  = "select sum(scf_valor_disponivel) as total_pendente, sum(scf_valor) as total_movimentado from saldo_composicao_fifo ";

$rs_saldo_pendente_fifo = SQLexecuteQuery($sql);
$rs_saldo_pendente_fifo_row = pg_fetch_array($rs_saldo_pendente_fifo);
if($rs_saldo_pendente_fifo_row) {
        $qtde_saldo_pendente_fifo			= $rs_saldo_pendente_fifo_row['total_pendente']; 
        $qtde_saldo_total_movimentado_fifo	= $rs_saldo_pendente_fifo_row['total_movimentado']; 
}

//Recupera Depósito em Saldo mais antigo
$data_deposito_mais_antigo = "";
$sql_deposito_mais_antigo  = "select min(scf_data_deposito) as data_min from saldo_composicao_fifo where scf_valor_disponivel>0 ";

$rs_deposito_mais_antigo = SQLexecuteQuery($sql_deposito_mais_antigo );
$rs_deposito_mais_antigo_row = pg_fetch_array($rs_deposito_mais_antigo);
if($rs_deposito_mais_antigo_row) {
        $data_deposito_mais_antigo			= $rs_deposito_mais_antigo_row['data_min']; 
}

//Recupera totais de Saldo pendente em cadastro de usuarios
$qtde_saldo_users = 0;
$sql  = "select  sum(ug_perfil_saldo) as saldo_total from usuarios_games ";

$rs_saldo_users = SQLexecuteQuery($sql);
$rs_saldo_users_row = pg_fetch_array($rs_saldo_users);
if($rs_saldo_users_row) {
        $qtde_saldo_users	= $rs_saldo_users_row['saldo_total']; 
}
?>
<table class="table  table-striped top10">
    <tr>
      <td width="50%" align="left">Saldo pendente (FIFO)</td>

      <td width="50%" align="right"><?php echo number_format($qtde_saldo_pendente_fifo, 2, '.', '.'); ?></td>
    </tr>
    <tr<?php echo ((($qtde_saldo_pendente_fifo-$qtde_saldo_users)!=0)?" class='bg-amarelo txt-vermelho'":""); ?>>
      <td width="50%" align="left">Saldo pendente (usuários)</td>

      <td width="50%" align="right"><?php echo number_format($qtde_saldo_users, 2, '.', '.'); ?></td>
    </tr>
    <tr>
      <td width="50%" align="left">Saldo total movimentado (FIFO)&nbsp;</td>

      <td width="50%" align="right"><?php echo number_format($qtde_saldo_total_movimentado_fifo, 2, '.', '.'); ?></td>
    </tr>
    <?php
            $n_delay = strtotime(date("Y-m-d H:i:s"))-strtotime($data_deposito_mais_antigo);
    ?>
    <tr valign="top">
      <td width="50%" align="left">Depósito em Saldo mais antigo&nbsp;</td>

      <td width="50%" align="right"><?php echo substr($data_deposito_mais_antigo, 0, 19) ?><br><?php echo convert_secs_to_string_global($n_delay) ?></td>
    </tr>
</table>