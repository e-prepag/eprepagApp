<?php
	ob_start();
	require_once '../../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo.php";
	$pos_pagina = isset($seg_auxilar) ? $seg_auxilar : null;

	@$varsel = "&dep_codigo=$dep_codigo&tf_data_inic=$tf_data_inic&tf_data_final=$tf_data_final&dd_banco=$dd_banco&tf_cod_documento=$tf_cod_documento";
	@$varsel .= "&tf_documento=$tf_documento&dd_situacao=$dd_situacao&tf_valor_oper=$tf_valor_oper&tf_valor=$tf_valor&tf_valor2=$tf_valor2";
	@$varsel .= "&dd_agencia=$dd_agencia&dd_conta=$dd_conta&BtnSearch=Buscar";
	$sql  = "select dep_codigo, dep_valor, dep_data, dep_banco, dep_agencia, dep_conta, dep_cod_documento, dep_documento, dep_aprovado, bco_nome ";
	$sql .= "from depositos_pendentes, bancos_financeiros ";
	$sql .= "where (dep_banco = bco_codigo) and (bco_rpp = 1) and (dep_codigo = ".$DepCod.") ";
	$resdep = pg_exec($connid, $sql);
	$pgdep = pg_fetch_array($resdep);

	$FrmEnviar = 1;
	if(isset($BtnAlterar) && $BtnAlterar)
	{
		$tela = 1;
		if(!verifica_valor_moeda_neg($tf_valor))
		{
			$valor_invalido = true;
			$FrmEnviar = 0;
		}
		$tf_valor = str_replace(',', '.', $tf_valor);


		if($FrmEnviar == 1)
		{
			$sql  = "update depositos_pendentes set dep_aprovado = ".$dd_situacao.", dep_valor = ".$tf_valor." where dep_codigo = ".$pgdep['dep_codigo']."";
//            echo $sql;
			pg_exec($connid,$sql);

//			ob_end_clean();
			header("location: pendentes.php"); //header("location: pendentes.php?" . $varsel);
		}
	}
	
	$mostra = true;
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="pendentes.php">Voltar</a></li>
        <li class="active">Depósitos Pendentes</li>
    </ol>
</div>
<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
<div class="col-md-12">
<table class="table txt-preto fontsize-p top20">
  <tr> 
    <td height="215">
        <form name="form1" method="post">
            <table class="table">
          <tr> 
            <td width="157" bgcolor="#268fbd"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">C&oacute;digo</font></td>
            <td bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgdep['dep_codigo'] ?></font>
                <input type="hidden" name="dep_codigo" value="<?php echo $pgdep['dep_codigo'] ?>"</td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#268fbd"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Data</font></td>
            <td bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo formata_data($pgdep['dep_data'], 0) ?></font></td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#268fbd"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Banco</font></td>
            <td bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgdep['bco_nome'] ?></font></td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#268fbd"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Agência</font></td>
            <td bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgdep['dep_agencia'] ?></font></td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#268fbd"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Conta</font></td>
            <td bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgdep['dep_conta'] ?></font></td>
          </tr>
          <tr> 
            <td bgcolor="#268fbd"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Cod 
              Documento </font></td>
            <td bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgdep['dep_cod_documento'] ?></font></td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#268fbd"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Documento</font></td>
            <td bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgdep['dep_documento'] ?> 
              </font></td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#268fbd"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Valor</font></td>
            <td bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
              <input name="tf_valor" type="text" class="form" id="tf_doc2" value="<?php if(isset($tela) && $tela == 1) echo $tf_valor; else echo number_format($pgdep['dep_valor'], 2, ',', '.'); ?>" size="10" maxlength="10">
              <?php
			  	if(isset($valor_invalido) && $valor_invalido == true)
					echo "<font color='#FF0000'><b>Valor Inválido</b></font>"
			  ?>
              </font></td>
          </tr>
          <tr> 
            <td width="157" bgcolor="#268fbd"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Situa&ccedil;&atilde;o</font></td>
            <td bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
              <select name="dd_situacao" id="dd_situacao" class="combo_normal">
                <option value="1" <?php if($pgdep['dep_aprovado'] == 1) echo "selected" ?>>Conciliado</option>
                <option value="0" <?php if($pgdep['dep_aprovado'] == 0) echo "selected" ?>>Disponivel</option>
              </select>
              </font></td>
          </tr>
          <tr> 
            <td width="157"><font size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
            <td> <input name="BtnAlterar" type="submit" id="BtnAlterar" value="Alterar" class="btn btn-sm btn-info" onClick="GP_popupConfirmMsg('Deseja alterar esse depósito?');return document.MM_returnValue"> 
              <div align="right"><font face="Arial, Helvetica, sans-serif" size="2" color="#268fbd"></font></div></td>
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
</div>
</body>
</html>
