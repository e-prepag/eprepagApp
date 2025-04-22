<?php
	ob_start();
	require_once '../../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo.php";
	include_once $raiz_do_projeto."includes/gamer/constantes.php";

	$pos_pagina = isset($seg_auxilar) ? $seg_auxilar : null;
	if(!$BolCod)
        $varsel = "&tf_data_inic=$tf_data_inic&tf_data_final=$tf_data_final&dd_banco=$dd_banco&tf_cod_documento=$tf_cod_documento&tf_documento=$tf_documento&dd_situacao=$dd_situacao&tf_valor=$tf_valor";
    else
        $varsel = "&bol_codigo=$BolCod";

	$sql  = "select bol_codigo, bol_valor, bol_data, bol_banco, bol_cod_documento, bol_documento, bol_aprovado, bco_nome, bol_venda_games_id ";
	$sql .= "from boletos_pendentes, bancos_financeiros ";
	$sql .= "where (bol_banco = bco_codigo) and (bco_rpp = 1) and (bol_codigo = ".$BolCod.") ";
	$resbol = pg_exec($connid, $sql);
	$pgbol = pg_fetch_array($resbol);

	// Obtem o ID Venda a aprtir do numeor do documento
	if($pgbol['bol_banco']==$GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO']) {
		// Formato "3000vvvvvvvP"
		$vg_id_boleto = substr($pgbol['bol_documento'],3,8);
	} elseif($pgbol['bol_banco']==$GLOBALS['BOLETO_MONEY_ITAU_COD_BANCO']) {
		// Formato "3vvvvvvv"
		$vg_id_boleto = substr($pgbol['bol_documento'],1,8);
	}elseif($GLOBALS['BOLETO_MONEY_ASAAS_COD_BANCO'] == $pgbol['bol_banco']) {
    $vg_id_boleto = $pgbol['bol_venda_games_id'];
  } else {
		$vg_id_boleto = $pgbol['bol_venda_games_id'];
	}
	
	// usa o indicador no numero do documento para mostrar a venda
	$pesquisa_venda = "";
	$s_indicador = substr($pgbol['bol_documento'],0,1);
	if($vg_id_boleto>0) {
		if($s_indicador=="1" || $s_indicador=="4") {
			$pesquisa_venda = "/pdv/vendas/com_venda_detalhe.php?venda_id=$vg_id_boleto";
		} elseif($s_indicador=="2" || $s_indicador=="3" || $s_indicador=="6") {
			$pesquisa_venda = "/gamer/vendas/com_venda_detalhe.php?venda_id=$vg_id_boleto";
		}
	}

	$FrmEnviar = 1;
	if(isset($BtnAlterar) && $BtnAlterar)
	{
		$tela = 1;
		if(!verifica_valor_moeda_neg($tf_valor2))
		{
			$valor_invalido = true;
			$FrmEnviar = 0;
		}
		$tf_valor2 = str_replace(',', '.', $tf_valor2);


		if($FrmEnviar == 1)
		{
			$sql  = "update boletos_pendentes set bol_aprovado = ".$dd_situacao2.", bol_valor = ".$tf_valor2." where bol_codigo = ".$pgbol['bol_codigo']."";
			pg_exec($connid,$sql);

			ob_end_clean();
			header("location: pendentes.php?a=" . $varsel);
		}
	}
	
	$mostra = true;
?>
<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="pendentes.php?a=<?php echo $varsel ?>" class="menu">Boletos</a></li>
        <li class="active">Boletos Pendentes</li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
    <td height="215">
        <form name="form1" method="post" action="">
        <table class="table txt-preto">
          <tr> 
              <td width="157" bgcolor="#0086c5" class="txt-branco"><strong>Código</strong></td>
            <td bgcolor="#f5f5fb"><?php echo $pgbol['bol_codigo'] ?></td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#0086c5" class="txt-branco"><strong>Data</strong></td>
            <td bgcolor="#f5f5fb"><?php echo formata_data($pgbol['bol_data'], 0) ?></td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#0086c5" class="txt-branco"><strong>Banco</strong></td>
            <td bgcolor="#f5f5fb"><?php echo $pgbol['bco_nome'] ?></td>
          </tr>
          <tr> 
            <td bgcolor="#0086c5" class="txt-branco"><strong>Cod Documento</strong></td>
            <td bgcolor="#f5f5fb"><?php echo $pgbol['bol_cod_documento'] ?></td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#0086c5" class="txt-branco"><strong>Documento</strong></td>
            <td bgcolor="#f5f5fb"><?php echo $pgbol['bol_documento'] ?> 
              </td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#0086c5" class="txt-branco"><strong>Valor</strong></td>
            <td bgcolor="#f5f5fb"> 
              <input name="tf_valor2" type="text" class="form" id="tf_doc2" value="<?php if(isset($tela) && $tela == 1) echo $tf_valor2; else echo number_format($pgbol['bol_valor'], 2, ',', '.'); ?>" size="10" maxlength="10">
              <?php
			  	if(isset($valor_invalido) && $valor_invalido == true)
					echo "<font color='#FF0000'><b>Valor Inválido</b></font>"
			  ?>
              </td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#0086c5" class="txt-branco"><strong>Situação</strong></td>
            <td bgcolor="#f5f5fb"> 
              <select name="dd_situacao2" id="dd_situacao2" class="combo_normal">
                <option value="1" <?php if($pgbol['bol_aprovado'] == 1) echo "selected" ?>>Conciliado</option>
                <option value="0" <?php if($pgbol['bol_aprovado'] == 0) echo "selected" ?>>Disponivel</option>
              </select>
              </td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#0086c5" class="txt-branco"><strong>Consulta Venda</strong></td>
            <td bgcolor="#f5f5fb"><?php 
				if($vg_id_boleto>0) {
					echo "<a href='$pesquisa_venda'>$vg_id_boleto</a>";	
				} else {
					echo "ID venda nulo ou não foi localizado";
				}

			?> </td>
          </tr>
          <tr> 
            <td width="157">&nbsp;</td>
            <td> <input name="BtnAlterar" type="submit" id="BtnAlterar" value="Alterar" class="btn btn-sm btn-info" onClick="GP_popupConfirmMsg('Deseja alterar esse boleto?');return document.MM_returnValue"> 
              <div align="right"></div></td>
          </tr>
         </table>
      </form></td>
  </tr>
 <tr>
            <td colspan="2"><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><br><br>
              <div align="center"></div>
              <?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?></td>
          </tr>  
</table>
</body>
</html>
