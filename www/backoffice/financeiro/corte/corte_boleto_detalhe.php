<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/pdv/corte_classPrincipal.php"; 

	$msg = "";

	if(!$bbc_id) $msg = "Código do boleto não fornecido.\n";
	elseif(!is_numeric($bbc_id)) $msg = "Código do boleto inválido.\n";

	//Recupera boleto
	if($msg == ""){
		$sql  = "select * from boleto_bancario_cortes bbc 
				 inner join cortes c on c.cor_codigo = bbc.bbc_cor_codigo
				 where bbc.bbc_boleto_codigo = " . $bbc_id;
		$rs_boleto = SQLexecuteQuery($sql);
		if(!$rs_boleto || pg_num_rows($rs_boleto) == 0) $msg = "Nenhum boleto encontrado.\n";
		else {
			$rs_boleto_row = pg_fetch_array($rs_boleto);
			$bbc_boleto_codigo 	= $rs_boleto_row['bbc_boleto_codigo'];
			$bbc_data_inclusao 	= $rs_boleto_row['bbc_data_inclusao'];
			$bbc_bco_codigo 	= $rs_boleto_row['bbc_bco_codigo'];
			$bbc_documento 		= $rs_boleto_row['bbc_documento'];
			$bbc_valor 			= $rs_boleto_row['bbc_valor'];
			$bbc_valor_taxa 	= $rs_boleto_row['bbc_valor_taxa'];
			$bbc_data_venc 		= $rs_boleto_row['bbc_data_venc'];
			$bbc_status 		= $rs_boleto_row['bbc_status'];
			$bbc_ug_id 			= $rs_boleto_row['bbc_ug_id'];
			$bbc_cor_codigo 	= $rs_boleto_row['bbc_cor_codigo'];
			$bbc_linha_digitavel= $rs_boleto_row['bbc_linha_digitavel'];
			$cor_periodo_ini	= $rs_boleto_row['cor_periodo_ini'];
			$cor_periodo_fim	= $rs_boleto_row['cor_periodo_fim'];
			$bbc_arq_remessa	= $rs_boleto_row['bbc_arq_remessa'];
			$bbc_data_concilia 	= $rs_boleto_row['bbc_data_concilia'];
			$bbc_status_banco 	= $rs_boleto_row['bbc_status_banco'];
			$bbc_bpb_codigo 	= $rs_boleto_row['bbc_bpb_codigo'];
			$bbc_data_cancelado	= $rs_boleto_row['bbc_data_cancelado'];
		}
	}

	//acessos
	$sql = "select * from boleto_bancario_cortes_acessos
			where bbca_bbc_boleto_codigo = $bbc_boleto_codigo
			order by bbca_data_inclusao desc";
	$rs_acessos = SQLexecuteQuery($sql);
	
	$cor1 = "#F5F5FB";
	$cor2 = "#F5F5FB";
	$cor3 = "#FFFFFF"; 	

ob_end_flush();
?>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
<table class="txt-preto fontsize-pp">
  <tr> 
    <td width="891" valign="top"> 
        <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
            <li><a href="index.php">Corte Semanal</a></li>
            <li class="active">Detalhe de corte</li>
        </ol> 
    </div>
	<?php if($msg != ""){?>
        <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr><td align="center"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"><?php echo str_replace("\n", "<br>", $msg) ?></font></td></tr>
		</table>
	<?php }?>

        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Boleto</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>C&oacute;digo</b></td>
            <td><?php echo $bbc_boleto_codigo ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Valor</b></td>
            <td>
			<?php echo number_format($bbc_valor, 2, ',', '.') ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Taxa</b></td>
            <td>
			<?php echo number_format($bbc_valor_taxa, 2, ',', '.') ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Data Inclusão</b></td>
            <td><?php echo formata_data_ts($bbc_data_inclusao, 0, true, true) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Data Vencimento</b></td>
            <td><?php echo formata_data($bbc_data_venc, 0) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Status</b></td>
			<?php if($bbc_status == $GLOBALS['CORTE_BOLETO_STATUS']['CANCELADO']){?>
			<td><font color="FF0000"><?php echo $GLOBALS['CORTE_BOLETO_STATUS_DESCRICAO'][$bbc_status] ?></font></td>
			<?php } else {?>
			<td><?php echo $GLOBALS['CORTE_BOLETO_STATUS_DESCRICAO'][$bbc_status] ?></td>
			<?php } ?>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Banco</b></td>
            <td><?php echo $bbc_bco_codigo ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Documento</b></td>
            <td><?php echo $bbc_documento ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Linha digitável</b></td>
            <td><a style="text-decoration:none" href="corte_boleto.php?bbc_boleto_codigo=<?php echo $bbc_boleto_codigo ?>" target="_blank" title="Ver boleto"><?php echo $bbc_linha_digitavel ?></a></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Usuário</b></td>
            <td><a style="text-decoration:none" href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $bbc_ug_id ?>" title="Ir para o usuário"><?php echo $bbc_ug_id ?></a></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Corte</b></td>
            <td>
            	<a style="text-decoration:none" href="corte_consulta.php?usuario_id=<?php echo $bbc_ug_id ?>&cor_codigo=<?php echo $bbc_cor_codigo ?>" title="Ir para o corte">
            	<?php echo formata_data($cor_periodo_ini, 0) ?> a <?php echo formata_data($cor_periodo_fim, 0) ?> #<?php echo $bbc_cor_codigo ?>
            	</a>
            </td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Remessa</b></td>
            <td>
            	<a style="text-decoration:none" target="_blank" href="corte_boleto_remessas_down.php?tipo=1&arquivo=<?php echo $bbc_arq_remessa ?>" title="Baixar arquivo">
            	<?php echo $bbc_arq_remessa ?>
            	</a>
            </td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Status Banco</b></td>
			<?php if($bbc_status_banco == $GLOBALS['BOLETO_BANCO_STATUS']['REJEITADO']){?>
			<td><font color="FF0000"><?php echo $GLOBALS['BOLETO_BANCO_STATUS_DESCRICAO'][$bbc_status_banco] ?></font></td>
			<?php } else {?>
			<td><?php echo $GLOBALS['BOLETO_BANCO_STATUS_DESCRICAO'][$bbc_status_banco] ?></td>
			<?php } ?>
          </tr>
		<?php if($bbc_status == $GLOBALS['CORTE_BOLETO_STATUS']['CONCILIADO']){?>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Data Conciliação<br>com arquivo Retorno</b></td>
            <td><?php echo formata_data_ts($bbc_data_concilia, 0, true, true) ?></td>
          </tr>
		<?php } ?>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Data Cancelamento</b></td>
            <td><?php if($bbc_data_cancelado) echo formata_data_ts($bbc_data_cancelado, 0, true, true) ?></td>
          </tr>
		</table>

<?php if($bbc_bco_codigo == 237){ ?>
<?php
	//retornos
	$sql = "select * from boletos_pendentes_bradesco
			where bpb_identtitulo like '$bbc_documento%'
			order by bpb_importacao desc, bpb_codigo desc";
	$rs_retornos = SQLexecuteQuery($sql);
?>	

		<br>
        <table width="894" border="0" cellpadding="0" cellspacing="2">
		<tr bgcolor="#DCD9C8"> 
			<td colspan="16" align="center" height="20">
				<strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Retornos</font></strong>
			</td>
		</tr>
		<tr bgcolor="#ECE9D8"> 
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Cod&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Data importacao&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Carteira&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Agência&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Conta&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Controle&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Nosso Número&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Ident Ocorrência&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Data Ocorrência&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Número do Documento&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Ident Titulo&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Data Vencimento&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Valor&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Valor Pago&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Data Crédito&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Motivos Rejeições&nbsp;</font></strong></td>
		</tr>
	<?php if($rs_retornos && pg_num_rows($rs_retornos) > 0){
			$cor1 = $cor3;
			while($rs_retornos_row = pg_fetch_array($rs_retornos)){
				if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;}
	?>
		<tr bgcolor="<?php echo $cor1 ?>"> 
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_retornos_row['bpb_codigo'] ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo formata_data_ts($rs_retornos_row['bpb_importacao'], 0, true, true) ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_retornos_row['bpb_identcedcart'] ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_retornos_row['bpb_identcedag'] ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_retornos_row['bpb_identcedcc'] ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_retornos_row['bpb_contrpart'] ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_retornos_row['bpb_identtitulo'] ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_retornos_row['bpb_identocorr'] ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php if($rs_retornos_row['bpb_dataocorr']) echo formata_data($rs_retornos_row['bpb_dataocorr'], 0) ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_retornos_row['bpb_nrodocumento'] ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_retornos_row['bpb_identtitulobanco'] ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php if($rs_retornos_row['bpb_datavenctitulo']) echo formata_data($rs_retornos_row['bpb_datavenctitulo'], 0) ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo number_format($rs_retornos_row['bpb_valortitulo'], 2, ',', '.') ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo number_format($rs_retornos_row['bpb_valorpago'], 2, ',', '.') ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php if($rs_retornos_row['bpb_datacredito']) echo formata_data($rs_retornos_row['bpb_datacredito'], 0) ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_retornos_row['bpb_motivosrejeicoes'] ?>&nbsp;</font></strong></td>
		</tr>
		<?php }?>
	<?php }?>
		</table>
<?php }?>

		<br>
        <table class="table">
		<tr bgcolor="#DCD9C8"> 
			<td colspan="4" align="center" height="20">
				<strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Registro das Emissões do Boleto realizadas pelo usuário</font></strong>
			</td>
		</tr>
		<tr bgcolor="#ECE9D8"> 
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Data acesso&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;IP&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;Usuário&nbsp;</font></strong></td>
		</tr>
<?php if($rs_acessos && pg_num_rows($rs_acessos) > 0){
		$cor1 = $cor3;
		while($rs_acessos_row = pg_fetch_array($rs_acessos)){
			if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;}
		?>
		<tr bgcolor="<?php echo $cor1 ?>"> 
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo formata_data_ts($rs_acessos_row['bbca_data_inclusao'], 0, true, true) ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $rs_acessos_row['bbca_ip'] ?>&nbsp;</font></strong></td>
			<td align="center" nowrap><strong><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;
				<a style="text-decoration:none" href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_acessos_row['bbca_ug_id'] ?>" title="Ir para o usuário"><?php echo $rs_acessos_row['bbca_ug_id'] ?></a>
			</font></strong></td>
		</tr>
	<?php }?>
<?php }?>
		</table>

    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
