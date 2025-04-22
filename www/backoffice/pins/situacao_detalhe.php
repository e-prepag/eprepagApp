<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

	$s_dist = (($PinStatus == '6')?"_dist":"");

	$sql  = "select t0.pin_codinterno,
		CASE WHEN pin_codigo = '0000000000000000' THEN pin_caracter
		ELSE pin_codigo
    	END as case_codigo,
		t1.opr_nome, t0.pin_serial, t0.pin_valor, t2.stat_descricao as pin_stat, t0.pin_dataentrada, t0.pin_horaentrada ";
	if(($PinStatus == '3') || ($PinStatus == '6')) $sql .= ", t0.pin_datavenda, t0.pin_horavenda \n";
									//	", t0.pin_ddd, t0.pin_celular \n";
									// ", t3.est_codigo, t3.nome_fantasia, t4.stat_descricao as estab_stat \n";

	$sql .= ", (select vg_id 
				from tb".$s_dist."_venda_games vg 
					inner join tb".$s_dist."_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					inner join tb".$s_dist."_venda_games_modelo_pins vgmp on vgm.vgm_id = vgmp.vgmp_vgm_id
			where vgmp.vgmp_pin_codinterno = t0.pin_codinterno
			) as vg_id,
				(select vgm_id 
						from tb".$s_dist."_venda_games_modelo vgm 
							inner join tb".$s_dist."_venda_games_modelo_pins vgmp on vgm.vgm_id = vgmp.vgmp_vgm_id
					where vgmp.vgmp_pin_codinterno = t0.pin_codinterno
					) as vgm_id,
			(select vg_ug_id 
						from tb".$s_dist."_venda_games vg 
							inner join tb".$s_dist."_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
							inner join tb".$s_dist."_venda_games_modelo_pins vgmp on vgm.vgm_id = vgmp.vgmp_vgm_id
					where vgmp.vgmp_pin_codinterno = t0.pin_codinterno
					) as vg_ug_id, 
			(select vgm_pin_codinterno
				from tb".$s_dist."_venda_games_modelo vgm
					inner join tb".$s_dist."_venda_games_modelo_pins vgmp on vgm.vgm_id = vgmp.vgmp_vgm_id
			where vgmp.vgmp_pin_codinterno = t0.pin_codinterno
			) as vgm_pin_codinterno\n";

	$sql .= "from pins t0, operadoras t1, pins_status t2 ";
//	if($PinStatus == '3') $sql .= ", estabelecimentos t3, estab_status t4 \n";
//	else $sql .= "\n";

	$sql .= "where (t0.opr_codigo=t1.opr_codigo) and (t0.pin_status=t2.stat_codigo) and (pin_codinterno=".$PinCod.") ";
//	if($PinStatus == '3') $sql .= "and (t0.pin_est_codigo=t3.est_codigo) and (t3.status=t4.stat_codigo) ";

	$resest = pg_exec($connid,$sql);
	$pgest = pg_fetch_array($resest);
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="situacao_query.php">Voltar</a></li>
        <li class="active">Consulta Situação do PIN</li>
    </ol>
</div>
<link rel="stylesheet" href="/css/css.css" type="text/css">
<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
<table class="table txt-preto fonsize-pp">
  <tr> 
    <td> 
        <table class="table table-bordered">
        <tr> 
          <td colspan="2" bgcolor="#FFFFFF"><strong>Dados 
            do PIN</strong> </td>
        </tr>
        <tr> 
          <td>C&oacute;digo</td>
          <td><?php echo $pgest['pin_codinterno'] ?></td>
        </tr>
        <tr> 
          <td width="121">Operadora</td>
          <td width="769"><?php echo $pgest['opr_nome'] ?></td>
        </tr>
        <tr> 
          <td>N&uacute;mero 
            do PIN</td>
          <td><?php echo @formata_string($pgest['case_codigo'], ' ', 4) ?></td>
        </tr>
        <tr> 
          <td>N&uacute;mero 
            de S&eacute;rie</td>
          <td><?php echo $pgest['pin_serial'] ?></td>
        </tr>
        <tr> 
          <td width="121">Valor</td>
          <td><?php echo "R$ ".number_format($pgest['pin_valor'], 2, ',', '.') ?></td>
        </tr>
        <tr> 
          <td>Status</td>
          <td><?php echo $pgest['pin_stat']." (".$PinStatus.")" ?></td>
        </tr>
        <tr bgcolor="#FFFFFF"> 
          <td height="22" colspan="2"><strong><br>
            Dados de Entrada do PIN</strong></td>
        </tr>
        <tr> 
          <td height="22">Data</td>
          <td><?php echo formata_data($pgest['pin_dataentrada'], 0) ?></td>
        </tr>
        <tr> 
          <td height="22">Hora</td>
          <td><?php echo $pgest['pin_horaentrada'] ?></td>
        </tr>
        <?php if(($PinStatus == '3') || ($PinStatus == '6')) { ?>
        <tr bgcolor="#FFFFFF"> 
          <td height="22" colspan="2"><strong><br>
            Dados de Venda do PIN</strong></td>
        </tr>
        <tr> 
          <td height="22">Data venda PIN</td>
          <td><?php echo formata_data($pgest['pin_datavenda'], 0) ?></td>
        </tr>
        <tr> 
          <td height="22">Hora venda PIN</td>
          <td><?php echo $pgest['pin_horavenda'] ?></td>
        </tr>
        <tr> 
          <td height="22">Código venda</td>
          <td><a href="/<?php echo (($PinStatus == "6")?"pdv":"gamer") ?>/vendas/com_venda_detalhe.php?venda_id=<?php echo $pgest['vg_id'] ?>" target="_blank"><?php echo $pgest['vg_id'] ?></a></td>
        </tr>
        <tr> 
          <td height="22">Código modelo</td>
          <td><?php echo $pgest['vgm_id'] ?></td>
        </tr>
        <tr> 
          <td height="22">PIN codinterno</td>
          <td><?php echo $pgest['vgm_pin_codinterno'] ?></td>
        </tr>
		<?php

			
			if($PinStatus == '3' && isset($pgest['vg_ug_id'])) {
				echo $sql = "select * from usuarios_games where ug_id = ".$pgest['vg_ug_id'];
			} elseif($PinStatus == '6' && isset($pgest['vg_ug_id'])) {
				$sql = "select * from dist_usuarios_games where ug_id = ".$pgest['vg_ug_id'];
			} else {
				$sql = "";
			}
			if($sql) {
				$rsusuario = pg_exec($connid,$sql);
				$pgusu = pg_fetch_array($rsusuario);

		?>
        <tr bgcolor="#FFFFFF"> 
          <td height="22" colspan="2"><strong><br>
            Dados do usuário (<?php echo (($PinStatus == '3')?"Gamer":"Lanhouse") ?>)</strong></td>
        </tr>
        <tr> 
          <td height="22">C&oacute;digo usuário</td>
          <td><a href="<?php echo "/".(($PinStatus == '6')?"pdv":"gamer")."/usuarios/com_usuario_detalhe.php?usuario_id=".$pgusu['ug_id'] ?>" target="_blank"><?php echo $pgusu['ug_id'] ?></a></td>
        </tr>
        <tr> 
          <td height="22">Nome/Login</td>
          <td><?php echo (($PinStatus == '3')?$pgusu['ug_email']:$pgusu['ug_login']) ?></td>
        </tr>
	        <?php } ?>
        <?php } ?>
      </table>
    </td>
  </tr>
</table>
</html>
<?php  require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>