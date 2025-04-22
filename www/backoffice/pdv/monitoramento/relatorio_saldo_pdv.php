<?php
//Recupera totais de Saldo pendente em cadastro de usuarios
$qtde_saldo_users = 0;
$sql  = "select  sum(ug_perfil_saldo) as saldo_total from dist_usuarios_games where ug_risco_classif = 2 and ug_perfil_saldo>0;";
$rs_saldo_users = SQLexecuteQuery($sql);
$rs_saldo_users_row = pg_fetch_array($rs_saldo_users);
if($rs_saldo_users_row) {
        $qtde_saldo_users	= $rs_saldo_users_row['saldo_total']; 
}

//Recupera totais de Limite pendente em cadastro de usuarios
$qtde_saldo_users_pos = 0;
$sql  = "select  sum(ug_perfil_limite) as saldo_total from dist_usuarios_games where ug_risco_classif = 1;";
$rs_saldo_users = SQLexecuteQuery($sql);
$rs_saldo_users_row = pg_fetch_array($rs_saldo_users);
if($rs_saldo_users_row) {
        $qtde_saldo_users_pos	= $rs_saldo_users_row['saldo_total']; 
}

//Recupera totais de Saldo negativo para LANs Pré
$saldo_users_pre	= 0; 
$qtde_users_pre		= 0; 
$sql  = "select count(*) as n,sum(ug_perfil_saldo) as saldo_total from dist_usuarios_games where ug_risco_classif = 2 and ug_perfil_saldo<0;";
$rs_saldo_users = SQLexecuteQuery($sql);
$rs_saldo_users_row = pg_fetch_array($rs_saldo_users);
if($rs_saldo_users_row) {
        $saldo_users_pre	= $rs_saldo_users_row['saldo_total']; 
        $qtde_users_pre		= $rs_saldo_users_row['n']; 
}

//Recupera totais de Saldo negativo para LANs Pós
$saldo_users_pos	= 0; 
$qtde_users_pos		= 0; 
$sql  = "select count(*) as n,sum(ug_perfil_saldo) as saldo_total from dist_usuarios_games where ug_risco_classif = 1 and ug_perfil_saldo<0;";
$rs_saldo_users = SQLexecuteQuery($sql);
$rs_saldo_users_row = pg_fetch_array($rs_saldo_users);
if($rs_saldo_users_row) {
        $saldo_users_pos	= $rs_saldo_users_row['saldo_total']; 
        $qtde_users_pos		= $rs_saldo_users_row['n']; 
}
?>
<table class="table table-bordered top10">
    <tr>
      <td align="left">Saldo (LANs Pré)</td>

      <td align="right"><?php echo number_format($qtde_saldo_users, 2, '.', '.'); ?></td>
    </tr>
    <tr>
      <td align="left">Limite (LANs Pós)&nbsp;</td>

      <td align="right"><?php echo number_format($qtde_saldo_users_pos, 2, '.', '.'); ?></td>
    </tr>
    <tr>
      <td align="left">Qtde (LANs Pré) Saldo Negativo&nbsp;</td>

      <td align="right"><?php echo number_format($qtde_users_pre, 0, '.', '.'); ?></td>
    </tr>
    <tr>
      <td align="left">Saldo Negativo (LANs Pré)</td>

      <td align="right"><font color="#B22E1C" size="1" face="Arial, Helvetica, sans-serif"><?php echo number_format($saldo_users_pre, 2, '.', '.'); ?></td>
    </tr>
    <tr>
      <td align="left">Qtde (LANs Pós) Saldo Negativo&nbsp;</td>

      <td align="right"><?php echo number_format($qtde_users_pos, 0, '.', '.'); ?></td>
    </tr>
    <tr>
      <td align="left">Saldo Negativo (LANs Pós)</td>

      <td align="right"><?php echo number_format($saldo_users_pos, 2, '.', '.'); ?></td>
    </tr>
</table>