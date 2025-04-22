<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."backoffice/includes/constantes.php";
require_once "/www/includes/bourls.php";
	$time_start = getmicrotime();

	if(!isset($dd_situacao) || !$dd_situacao) $dd_situacao = "";
	
    if(!isset($tf_data_inic) || !$tf_data_inic){
        $tf_data_inic = (!isset($dep_codigo)) ? date('d/m/Y') : null;
    }
	if(!isset($tf_data_final) || !$tf_data_final){
        $tf_data_final = (!isset($dep_codigo)) ? date('d/m/Y') : null;
    }
    
	if(!isset($ncamp) || !$ncamp) $ncamp = 'dep_codigo';
	if(!isset($inicial) || !$inicial)  $inicial     = 0;
	if(!isset($range) || !$range)    $range       = 1;
	if(!isset($ordem) || !$ordem)    $ordem       = 0;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	if(isset($BtnSearch) && $BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100;	$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$sql = "select * from bancos_financeiros where bco_rpp = 1 ";
	$resbco = pg_exec($connid, $sql);
		
	$sql = "select dep_codigo, dep_valor, dep_data, dep_importacao, dep_banco, dep_agencia, dep_conta, dep_documento, dep_cod_documento, dep_cod_documento2, dep_banco,bco_nome, dep_aprovado ";
	$sql .= ",case when length(replace(replace(dep_documento, ' ', ''), 'T', '')) = 0 then -100 ";
	$sql .= " else cast(replace(replace(dep_documento, ' ', ''), 'T', '') as bigint) end as dep_documento2 ";
	$sql .= "from depositos_pendentes, bancos_financeiros ";
	//$sql .= "where (dep_cod_documento2 <> '".MOV."' AND dep_cod_documento2 <> '".TAR."' OR dep_cod_documento2 IS NULL) and (dep_banco = bco_codigo) and (bco_rpp = 1) and (dep_data >= '".formata_data($tf_data_inic, 1)."' and dep_data <= '".formata_data($tf_data_final, 1)."') ";
        $sql .= "where (dep_banco = bco_codigo) and (bco_rpp = 1)";
        if($tf_data_inic && $tf_data_final)
            $sql .= " and (dep_data >= '".formata_data($tf_data_inic, 1)."' and dep_data <= '".formata_data($tf_data_final, 1)."') ";

	if(isset($dd_situacao) && $dd_situacao != '') $sql .= "and dep_aprovado = ".($dd_situacao - 1)." ";
	if(isset($dd_banco) && $dd_banco) $sql .= "and dep_banco = '".$dd_banco."' ";		
	if(isset($dd_agencia) && $dd_agencia) $sql .= "and dep_agencia LIKE '%".$dd_agencia."%' ";
	if(isset($dd_conta) && $dd_conta) $sql .= "and dep_conta LIKE '%". $dd_conta."%' ";
	if(isset($tf_cod_documento) && $tf_cod_documento) $sql .= "and dep_cod_documento LIKE '%".strtoupper($tf_cod_documento)."%' ";
	if(isset($tf_documento) && $tf_documento) $sql .= "and dep_documento LIKE '%".strtoupper($tf_documento)."%' ";
	if(isset($dep_codigo) && $dep_codigo) $sql .= "and dep_codigo = '".$dep_codigo."' ";		

    if(!isset($tf_valor_oper) || $tf_valor_oper == ""){
        $tf_valor_oper = '=';
    }
	if (isset($tf_valor_oper) && $tf_valor_oper == 'between')
	{
	if(isset($tf_valor) && isset($tf_valor2) && $tf_valor && $tf_valor2)     $sql .= " and dep_valor " . $tf_valor_oper . " " . str_replace(',', '.', str_replace('.', '', trim($tf_valor))) . " and  " . str_replace(',', '.', str_replace('.', '', trim($tf_valor2))) . " ";
	}
	else
	{
	if(isset($tf_valor) && $tf_valor)     $sql .= " and dep_valor " . $tf_valor_oper . " " . str_replace(',', '.', str_replace('.', '', trim($tf_valor))) . " ";
	}
/*        
if(b_IsUsuarioWagner()) { 
	echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";
}
*/
$res_count = pg_exec($sql);
	$total_table = pg_num_rows($res_count);
	$dep_valor_total_i=0;

	while($u=pg_fetch_array($res_count))
		$dep_valor_total_i+=$u['dep_valor'];
	
	$sql .= "order by ".$ncamp." ";

	if($ordem == 1)
	{
		$sql .= " asc ";
		$img_seta = "/images/seta_up.gif";
	}
	else
	{
		$sql .= " desc ";
		$img_seta = "/images/seta_down.gif";
	}

	$sql .= " limit ".$max." ";
	$sql .= " offset ".$inicial;

//	trace_sql($sql, "Arial", 2, "#666666", 'b');
	$resest = pg_exec($connid,$sql);
	
	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
		
	@$varsel = "&dd_codigo=$dep_codigo&tf_data_inic=$tf_data_inic&tf_data_final=$tf_data_final&dd_banco=$dd_banco&tf_cod_documento=$tf_cod_documento";
	@$varsel .= "&tf_documento=$tf_documento&dd_situacao=$dd_situacao&tf_valor_oper=$tf_valor_oper&tf_valor=$tf_valor&tf_valor2=$tf_valor2";
	@$varsel .= "&dd_agencia=$dd_agencia&dd_conta=$dd_conta";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
        optDate.interval = 1000;

    setDateInterval('tf_data_inic','tf_data_final',optDate);
});

function GP_popupConfirmMsg(msg) { 
  document.MM_returnValue = confirm(msg);
}

function GP_popupAlertMsg(msg) { 
  document.MM_returnValue = alert(msg);
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
    <a href="insere.php" class="btn btn-sm btn-info">
        Inserir Novo Registro
    </a>
</div>
<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
<table class="txt-preto fontsize-pp top20">
  <tr> 
    <td valign="top"> 
	<form name="form1" method="post" action="">
        <table class="table">
            <tr bgcolor="#F5F5FB"> 
              <td width="18%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Cod. 
                Deposito</font></td>
              <td width="13%" height="0"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
                <input name="dd_codigo" type="text" class="form" id="dd_codigo" value="<?php if(isset($dep_codigo)) echo $dep_codigo ?>" size="10" maxlength="10">
                </font></td>

              <td width="18%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Intervalo 
                de Datas</font></td>
              <td height="0" colspan="6"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                <input name="tf_data_inic" type="text" class="form" id="tf_data_inic" value="<?php echo $tf_data_inic ?>" size="9" maxlength="10">
                - 
                <input name="tf_data_final" type="text" class="form" id="tf_data_final" value="<?php echo $tf_data_final ?>" size="9" maxlength="10">
              <td width="9%" height="0">&nbsp;</td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
              <td width="18%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Banco</font></td>
              <td width="13%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                <select name="dd_banco" id="dd_banco" class="combo_normal">
                  <option value="">Todos</option>
                  <?php while($pgbco = pg_fetch_array($resbco)) { ?>
                  <option value="<?php echo $pgbco['bco_codigo'] ?>" <?php if(isset($dd_banco) && $pgbco['bco_codigo'] == $dd_banco) echo "selected" ?>><?php echo $pgbco['bco_nome'] ?></option>
                  <?php } ?>
                </select>
                </td>
              <td width="11%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Situa&ccedil;&atilde;o</font></td>
              <td width="15%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                <select name="dd_situacao" id="dd_situacao" class="combo_normal">
                  <option value="" <?php if(isset($dd_situacao) && $dd_situacao == "") echo "selected" ?>>Todos</option>
                  <option value="1" <?php if(isset($dd_situacao) && $dd_situacao == 1) echo "selected" ?>>Disponível</option>
                  <option value="2" <?php if(isset($dd_situacao) && $dd_situacao == 2) echo "selected" ?>>Conciliado</option>
                </select>
                </font></td>
              <td width="6%"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Valor 
                </font> </td>
              <td width="7%">
                  <select name="tf_valor_oper">
                      <option value="=" <?php if((!isset($tf_valor_oper) || !$tf_valor_oper)  || $tf_valor_oper == "=") echo "selected" ?>>=</option>
                      <option value=">" <?php if(isset($tf_valor_oper) && $tf_valor_oper == ">") echo "selected" ?>>&gt;</option>
                      <option value="<" <?php if(isset($tf_valor_oper) && $tf_valor_oper == "<") echo "selected" ?>>&lt;</option>
                      <option value="between" <?php if(isset($tf_valor_oper) && $tf_valor_oper == "between") echo "selected" ?>>&gt;&lt;</option>
                </select></td>
              <td width="8%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                <input name="tf_valor" type="text" class="form" id="tf_valor" value="<?php if(isset($tf_valor)) echo $tf_valor ?>" size="9" maxlength="10">
                </font></td>
              <td width="6%"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Entre</font> 
              </td>
              <td width="7%"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
                <input name="tf_valor2" type="text" class="form" id="tf_valor2" value="<?php if(isset($tf_valor2)) echo $tf_valor2 ?>" size="9" maxlength="10">
                </font></td>
              <td width="9%" height="0"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                  </font></div></td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
              <td width="18%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Agência</font></td>
              <td width="13%" height="0"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
                <input name="dd_agencia" type="text" class="form" id="dd_agencia" value="<?php if(isset($dd_agencia)) echo $dd_agencia ?>" size="10" maxlength="10">
                </font></td>
              <td width="11%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Conta</font></td>
              <td height="0" colspan="6"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
                <input name="dd_conta" type="text" class="form" id="dd_conta" value="<?php  if(isset($dd_conta)) echo $dd_conta ?>" size="10" maxlength="15">
                </font></td>
              <td width="9%" height="0"></td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
              <td width="18%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Cod. 
                Documento</font></td>
              <td width="13%" height="0"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
                <input name="tf_cod_documento" type="text" class="form" id="tf_cod_documento" value="<?php if(isset($tf_cod_documento))  echo $tf_cod_documento ?>" size="10" maxlength="10">
                </font></td>
              <td width="11%" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Documento</font></td>
              <td height="0" colspan="6"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
                <input name="tf_documento" type="text" class="form" id="tf_documento" value="<?php if(isset($tf_documento))  echo $tf_documento ?>" size="30" maxlength="30">
                </font></td>
              <td width="9%" height="0"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                  <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info">
                  </font></div></td>
            </tr>
        </table>
    </form>
    </td>
  </tr>
</table>
<table class="txt-preto fontsize-pp">
  <tr>
        <td>
            <table class="table">
                <tr> 
                    <td width="40%"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      <?php if($total_table > 0) { ?>
                      Exibindo resultados <strong> 
                      <?php echo $inicial + 1 ?>
                      </strong> a <strong> 
                      <?php echo $reg_ate ?>
                      </strong> de <strong> 
                      <?php echo $total_table ?>
                      </strong></font> 
                      <?php } else { ?>
                      &nbsp; 
                      <?php } ?>
                      <font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                      </font>
                    </td>
                </tr>
            </table>   
            <table class="table">
                <tr bgcolor="#268fbd"> 
                    <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_codigo" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF" class="link_br">C&oacute;digo</font></a></strong> 
                      <?php if($ncamp == 'dep_codigo') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                    </td>
                    <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_data" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF" class="link_br">Data</font></a></strong> 
                      <?php if($ncamp == 'dep_data') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                    </td>
                    <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_importacao" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF" class="link_br">Importa&ccedil;&atilde;o</font></a></strong> 
                      <?php if($ncamp == 'dep_importacao') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                    </td>
                    <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=bco_nome" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Banco</span></font></a></strong> 
                      <?php if($ncamp == 'bco_nome') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                    </td>
                    <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_agencia" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Agência</span></font></a></strong> 
                      <?php if($ncamp == 'dep_agencia') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                    </td>
                    <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_conta" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Conta</span></font></a></strong> 
                      <?php if($ncamp == 'dep_conta') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                    </td>
                    <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_cod_documento" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Cod 
                      Documento</span></font></a></strong> 
                      <?php if($ncamp == 'dep_cod_documento') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                    </td>
                    <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_cod_documento2" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Cod 
                      Documento2</span></font></a></strong> 
                      <?php if($ncamp == 'dep_cod_documento2') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                    </td>
                    <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_documento2" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Documento</span></font></a></strong> 
                      <?php if($ncamp == 'dep_documento2') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                    </td>
                    <td> <div align="right"> 
                        <?php if($ncamp == 'dep_valor') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                        <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_valor" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Valor</span></font></a></strong></div></td>
                    <td><div align="center"><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_aprovado" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Situa&ccedil;&atilde;o</span></font></a> 
                        </strong></div></td>
                    <td><font face="Arial, Helvetica, sans-serif" size="2" color="#268fbd">x</font></td>
                </tr>
<?php
                  $cor1 = "#F5F5FB";
                  $cor2 = "#F5F5FB";
                  $cor3 = "#FFFFFF"; 	
                              if(!isset($dep_valor_total)) $dep_valor_total = 0;
                  while ($pgest = pg_fetch_array($resest))
                  {
                      $valor = 1;
                      $dep_valor_total += $pgest['dep_valor'];

                      if($pgest['dep_aprovado'] == 1)
                          $dep_aprovado = "Conciliado";
                      else
                          $dep_aprovado = "Disponível";
               ?>
              <tr> 
                <td bgcolor="<?php echo $cor1 ?>">
                  <a href="altera.php?DepCod=<?php echo $pgest['dep_codigo'] ?>" class="link_azul"> 
                  <?php echo $pgest['dep_codigo'] ?></a></td>
                <td bgcolor="<?php echo $cor1 ?>" nowrap><?php echo formata_data($pgest['dep_data'], 0) ?></td>
                <td bgcolor="<?php echo $cor1 ?>" nowrap><?php echo formata_timestamp ($pgest['dep_importacao'],2) ?></td>
                <td bgcolor="<?php echo $cor1 ?>" nowrap><?php echo $pgest['bco_nome'] ?></td>
                <td bgcolor="<?php echo $cor1 ?>" nowrap><?php echo $pgest['dep_agencia'] ?></td>
                <td bgcolor="<?php echo $cor1 ?>" nowrap><?php echo $pgest['dep_conta'] ?></td>
                <td bgcolor="<?php echo $cor1 ?>" nowrap><div align="left"><?php echo $pgest['dep_cod_documento'] ?></div></td>
                <td bgcolor="<?php echo $cor1 ?>" nowrap><div align="left"><?php echo $pgest['dep_cod_documento2'] ?></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><?php echo $pgest['dep_documento'] ?></div></td>
                <td bgcolor="<?php echo $cor1 ?>" class="<?php echo ($dep_aprovado == "Disponível") ? "txt-verde" : "txt-preto";?>">
                    <?php echo number_format($pgest['dep_valor'], 2, ',', '.') ?>
                </td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="center"><?php echo $dep_aprovado ?></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="center"><a href="deleta.php?DepCod=<?php echo $pgest['dep_codigo']?><?=$varsel?>" onClick="GP_popupConfirmMsg('Deseja exculir esta informação de depósito?');return document.MM_returnValue"><img src="../../../../images/deletar.gif" alt="Excluir Registro" width="12" height="14" border="0"></a></div></td>
              </tr>
              <?php

             if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} }
             if (!isset($valor) || !$valor)
             {  ?>
              <tr bgcolor="#f5f5fb"> 
                <td colspan="12" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                    N&atilde;o h&aacute; registros<br>
                    <br>
                    </strong></font></div></td>
              </tr>
              <?php } else { ?>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="9"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($dep_valor_total, 2, ',', '.') ?></strong></font></div></td>
                <td>&nbsp;</td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></font></div></td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="9"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAL 
                  GERAL</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($dep_valor_total_i, 2, ',', '.') ?></strong></font></div></td>
                <td>&nbsp;</td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></font></div></td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="12" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><strong> 
                  OBS: Valores expressos em R$.</strong></font></td>
              </tr>
              <?php
                    $time_end = getmicrotime();
                    $time = $time_end - $time_start;
            ?>
              <tr> 
                <td colspan="12" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit?> 
                  </font></td>
              </tr>
              <?php	
                  paginacao_query($inicial, $total_table, $max, '7', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
              ?>
              <?php  }  ?>
            </table>
        </td>
    </tr>
</table>
<?php pg_close ($connid); ?>
</div>
</body>
</html>
