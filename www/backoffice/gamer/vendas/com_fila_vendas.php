<?php

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

?>

<?php
	$varsel  = "&tf_v_codigo=$tf_v_codigo&tf_v_status=$tf_v_status";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	$varsel .= "&tf_v_data_concilia_ini=$tf_v_data_concilia_ini&tf_v_data_concilia_fim=$tf_v_data_concilia_fim";
	$varsel .= "&tf_v_concilia=$tf_v_concilia&tf_d_forma_pagto=$tf_d_forma_pagto&tf_d_banco=$tf_d_banco&tf_d_local=$tf_d_local";
	$varsel .= "&tf_d_data_ini=$tf_d_data_ini&tf_d_data_fim=$tf_d_data_fim";
	$varsel .= "&tf_d_data_inclusao_ini=$tf_d_data_inclusao_ini&tf_d_data_inclusao_fim=$tf_d_data_inclusao_fim";
	$varsel .= "&tf_d_valor_pago=$tf_d_valor_pago&tf_d_num_docto=$tf_d_num_docto";
	$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_nome=$tf_u_nome&tf_u_email=$tf_u_email&tf_u_cpf=$tf_u_cpf";
	$varsel .= "&tf_v_valor=$tf_v_valor&tf_v_qtde_produtos=$tf_v_qtde_produtos&tf_v_qtde_itens=$tf_v_qtde_itens";
?>
<?php
	if(!$fila_ordem)    $fila_ordem       = 0;
	if(!$fila_ncamp){
		$fila_ncamp       = 'vg_id';
	} else {
		//Verifica se coluna na variavel $fila_ncamp existe na tabela tb_venda_games
		$rs_tb_venda_games = pg_exec($connid, "select column_name from information_schema.columns where table_name = 'tb_venda_games'");
		if($rs_tb_venda_games)
			while($rs_tb_venda_games_row = pg_fetch_array($rs_tb_venda_games))
				if($fila_ncamp == $rs_tb_venda_games_row['column_name']) $blColumn = true;
		if($blColumn != true) $fila_ncamp = 'vg_id';	
	}
	
	if($fila_ordem == 1){
		$ordemOp = "<";
		$ordemDir = "desc";
	} else {
		$ordemOp = ">";
		$ordemDir = "asc";
	}
/*
	//Recupera pedido pendente de conciliacao
	$sql  = "select vg.vg_id from tb_venda_games vg 
			 where (vg.vg_concilia is null or vg.vg_concilia = 0)
				and vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'];
	if($venda_id) $sql .= " and vg.vg_id " . $ordemOp . " " . $venda_id;
	$sql .= " order by " . $fila_ncamp . " " . $ordemDir;
	$sql .= " limit 1 ";
*/

	//Recupera pedido pendente de conciliacao
	$sql  = "select vg.vg_id, 
					sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, vg.vg_data_inclusao as data
			 from tb_venda_games vg 
			 inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
			 inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id 
			 where 1=1 ";
	$sql .= " and (vg.vg_concilia is null or vg.vg_concilia = 0) ";
	$sql .= " and vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . " ";

	if($venda_id) 				$sql .= " and vg.vg_id " . $ordemOp . " " . $venda_id;

	if($tf_v_codigo) 			$sql .= " and vg.vg_id = '".$tf_v_codigo."' ";
	if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) $sql .= " and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'";
	if($tf_d_forma_pagto) 		$sql .= " and vg.vg_pagto_tipo = '".$tf_d_forma_pagto."' ";
	if($tf_d_banco) 			$sql .= " and vg.vg_pagto_banco = '".$tf_d_banco."' ";
	if($tf_d_local) 			$sql .= " and vg.vg_pagto_local = '".$tf_d_local."' ";
	if($tf_d_data_ini && $tf_d_data_fim) $sql .= " and vg.vg_pagto_data between '".formata_data($tf_d_data_ini,1)." 00:00:00' and '".formata_data($tf_d_data_fim,1)." 23:59:59'";
	if($tf_d_data_inclusao_ini && $tf_d_data_inclusao_fim) $sql .= " and vg.vg_pagto_data_inclusao between '".formata_data($tf_d_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_d_data_inclusao_fim,1)." 23:59:59'";
	if($tf_d_valor_pago) 		$sql .= " and vg.vg_pagto_valor_pago = ".moeda2numeric($tf_d_valor_pago)." ";
	if($tf_d_num_docto) 		$sql .= " and upper(vg.vg_pagto_num_docto) like '%". strtoupper($tf_d_num_docto)."%' ";
	if($tf_u_codigo) 			$sql .= " and ug.ug_id = '".$tf_u_codigo."' ";
	if($tf_u_nome) 				$sql .= " and upper(ug.ug_nome) like '%".strtoupper($tf_u_nome)."%' ";
	if($tf_u_email) 			$sql .= " and upper(ug.ug_email) like '%".strtoupper($tf_u_email)."%' ";
	if($tf_u_cpf) 				$sql .= " and ug.ug_cpf like '%".$tf_u_cpf."%' ";
	if($tf_vgm_nome_produto) 	$sql .= " and upper(vgm.vgm_nome_produto) like '%".str_replace("'", "''",strtoupper($tf_vgm_nome_produto))."%' ";
	if($tf_vgm_nome_modelo) 	$sql .= " and upper(vgm.vgm_nome_modelo) like '%".str_replace("'", "''",strtoupper($tf_vgm_nome_modelo))."%' ";

	$sql .= "group by 	vg.vg_id, vg.vg_data_inclusao
			 having 1=1 ";
	if($tf_v_valor) $sql .= " and sum(vgm.vgm_valor * vgm.vgm_qtde) = ".moeda2numeric($tf_v_valor)." ";
	if($tf_v_qtde_produtos) $sql .= " and count(*) = ".$tf_v_qtde_produtos." ";
	if($tf_v_qtde_itens) $sql .= " and sum(vgm.vgm_qtde) = ".$tf_v_qtde_itens." ";

	$sql .= " order by " . $fila_ncamp . " " . $ordemDir;
	$sql .= " limit 1 ";

echo $sql;

	$rs_concilia = SQLexecuteQuery($sql);
	if($rs_concilia && pg_num_rows($rs_concilia) > 0){
		$rs_concilia_row = pg_fetch_array($rs_concilia);
		$vg_id_prox = $rs_concilia_row['vg_id'];
	}
		
	if(!$vg_id_prox) $msg = "Nenhuma venda encontrada";
	else{
		//redireciona
		$strRedirect = "com_venda_detalhe.php?venda_id=" . $vg_id_prox . "&fila_ncamp=" . $fila_ncamp . "&fila_ordem=" . ($fila_ordem == 1?2:1) . $varsel;
		ob_end_clean();
		header("Location: " . $strRedirect);
		exit;
		?><html><body onload="window.location='<?php echo $strRedirect?>'"><?php
		exit;
	}
		
ob_end_flush();
?>
<html>
<head>
<title>E-Prepag - &Aacute;rea do Parceiro</title>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<META HTTP-EQUIV="EXPIRES" CONTENT="0">
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<link rel="stylesheet" href="/css/css.css" type="text/css">

<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="894" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="891" valign="top"> 
        <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr> 
            <td colspan="5">
				<table width="894" border="0" cellpadding="0" cellspacing="0" dwcopytype="CopyTableCell">
					<tr> 
					    <td width="894" height="21" bgcolor="00008C">
							<font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><b>Money - Venda</b></font></td>
					  </tr>
				</table>
		      <table border='0' width="100%" cellpadding="2" cellspacing="0">
        			<tr bgcolor=""> 
			          <td>&nbsp;</td>
			          <td><div align="right">
						<a href="com_pesquisa_vendas.php"><img src="/images/voltar.gif" border="0"></a>&nbsp;&nbsp;
					  	<a href="index.php"><img src="/images/voltar_menu.gif" width="107" height="15" border="0"></a>
						</div></td>
			        </tr>
		      </table>
			</td>
          </tr>
		</table>
		
        <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr><td align="center"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"><?php echo str_replace("\n", "<br>", $msg) ?></font></td></tr>
		</table>
	
		<form name="form1" method="post" action="com_fila_vendas.php">
		<input type="hidden" name="venda_id" value="<?php echo $vg_id_prox ?>">
		<input type="hidden" name="fila_ordem" value="<?php echo $fila_ordem ?>">
		<input type="hidden" name="fila_ncamp" value="<?php echo $fila_ncamp ?>">
		<table width="894" border="0" cellpadding="0" cellspacing="1" class="texto">
		  <tr><td colspan="2">&nbsp;</td></tr>
		  <?php if($fila_ordem == 1){ ?>
		  <tr><td align="center"><input type="submit" name="BtnProximo" value="Próximo" class="botao_search" onClick="form1.fila_ordem.value='2';"></td></tr>
		  <?php } else {?>
		  <tr><td align="center"><input type="submit" name="BtnAnterior" value="Anterior" class="botao_search" onClick="form1.fila_ordem.value='1';"></td></tr>
		  <?php } ?>
		</table>
		</form>
	
    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
