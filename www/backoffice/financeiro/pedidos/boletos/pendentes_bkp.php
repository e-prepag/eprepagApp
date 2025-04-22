<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";

	set_time_limit ( 18000 ) ; //	500mins
	$time_start = getmicrotime();

	if(!isset($dd_situacao) || !$dd_situacao) $dd_situacao = "";
	if(!isset($tf_data_inic) || !$tf_data_inic) $tf_data_inic = date('d/m/Y');
	if(!isset($tf_data_final) || !$tf_data_final) $tf_data_final = date('d/m/Y');
	if(!isset($ncamp) || !$ncamp) $ncamp = 'bol_codigo';
	if(!isset($inicial) || !$inicial)  $inicial     = 0;
	if(!isset($range) || !$range)    $range       = 1;
	if(!isset($ordem) || !$ordem)    $ordem       = 0;
	if(isset($BtnSearch) && $BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}
	$tf_data_inic = trim($tf_data_inic);
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$sql = "select * from bancos_financeiros where bco_rpp = 1 ";
	$resbco = pg_exec($connid, $sql);
		
	$sql_usuario  = "select ug_id, ug.ug_tipo_cadastro, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)||' ('||ug.ug_tipo_cadastro||')' END) as ug_nome_fantasia, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_razao_social) WHEN (ug.ug_tipo_cadastro='PF') THEN '' END) as ug_razao_social, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_cnpj) WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_cpf) END) as ug_cpf_cnpj from dist_usuarios_games ug where ug_ativo=1 order by ug_nome_fantasia "; // "and ug_usuario_cartao=1 "
	$rs_usuario = SQLexecuteQuery($sql_usuario);
//echo "$sql_usuario<br>";

	$sql = "select bol_codigo, bol_valor, bol_data, bol_importacao, bol_banco, bol_documento, bol_cod_documento, bol_banco, bco_nome, bol_aprovado ";
	$sql .= "from bancos_financeiros, boletos_pendentes bp ";
	if(isset($dd_usuario) && $dd_usuario) {
		$sql .= "inner join tb_dist_venda_games vg on bp.bol_venda_games_id = vg.vg_id ";
		$sql .= "inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id ";
	}
	$sql .= "where (bol_banco = bco_codigo) and (bco_rpp = 1) and (bol_data >= '".formata_data($tf_data_inic, 1)."' and bol_data <= '".formata_data($tf_data_final, 1)."') ";

	if(isset($dd_situacao) && $dd_situacao != '') $sql .= "and bol_aprovado = ".($dd_situacao - 1)." ";
//	if($tf_valor) $sql .= "and bol_valor = '".number_format(str_replace(".","",$tf_valor), 2, '.', '')."' ";
//	if($tf_valor) $sql .= "and bol_valor = '" . str_replace(",",".",str_replace(".","",$tf_valor)) ."' ";
	if(isset($dd_banco) && $dd_banco) $sql .= "and bol_banco = '".$dd_banco."' ";		
	if(isset($dd_usuario) && $dd_usuario) $sql .= "and ug.ug_id = ".$dd_usuario." ";		
	if(isset($tf_cod_documento) && trim($tf_cod_documento) != "") $sql .= "and bol_cod_documento LIKE '%".strtoupper($tf_cod_documento)."%' ";
	if(isset($tf_documento) && trim($tf_documento) != "") $sql .= "and bol_documento LIKE '%".strtoupper($tf_documento)."%' ";
	if(isset($tf_valor) && trim($tf_valor) != "")     $sql .= " and bol_valor " . $tf_valor_oper . " " . str_replace(',', '.', str_replace('.', '', trim($tf_valor))) . " ";
	if(isset($dd_tipodoc) && trim($dd_tipodoc) != "") $sql .= "and bol_documento LIKE '".strtoupper($dd_tipodoc)."%' ";

if(b_IsUsuarioWagner()) { 
//echo "<br><br>".str_replace("\n", "<br>\n", $sql)."<br><br>";
}       
	$res_count = pg_exec($sql);
	$total_table = pg_num_rows($res_count);
	$bol_valor_total_i=0;

	while($u=pg_fetch_array($res_count))
		$bol_valor_total_i+=$u['bol_valor'];
	
	$sql .= "order by ".$ncamp." ";

	if($ordem == 1) {
		$sql .= " asc ";
		$img_seta = "/images/seta_up.gif";
	} else {
		$sql .= " desc ";
		$img_seta = "/images/seta_down.gif";
	}

	$sql .= " limit ".$max." ";
	$sql .= " offset ".$inicial;


//echo $sql."<br>";
//echo "A: ".date("Y-m-d H:i:s")."<br>";
//die("Stop");

//	trace_sql($sql, "Arial", 2, "#666666", 'b');			
	$resest = pg_exec($connid,$sql);
	
	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
    
    if(!isset($tf_data_inic))
        $tf_data_inic = null;
    
    if(!isset($tf_data_final))
        $tf_data_final = null;
    
    if(!isset($dd_banco))
        $dd_banco = null;
    
    if(!isset($tf_cod_documento))
        $tf_cod_documento = null;
    
    if(!isset($tf_documento))
        $tf_documento = null;
    
    if(!isset($dd_situacao))
        $dd_situacao = null;
    
    if(!isset($tf_valor))
        $tf_valor = null;
    
    if(!isset($dd_tipodoc))
        $dd_tipodoc = null;
    
    if(!isset($tf_valor_oper))
        $tf_valor_oper = null;
    
	$varsel =  "&tf_data_inic=$tf_data_inic&tf_data_final=$tf_data_final&dd_banco=$dd_banco&tf_cod_documento=$tf_cod_documento&tf_documento=$tf_documento&dd_situacao=$dd_situacao&tf_valor=$tf_valor&tf_valor_oper=$tf_valor_oper&dd_tipodoc=$dd_tipodoc";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="JavaScript">
    
$(document).ready(function(){

    var optDate = new Object();
    optDate.interval = 10000;

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
<div class="col-md-12">
    <a href="boletos_pendentes_carga.php" class="btn btn-sm btn-info bottom20">Novo Registro</a>
</div>
<table class="fontsize-pp txt-preto">
  <tr> 
    <td valign="top"> 
	<form name="form1" method="post" action="">
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Intervalo 
              de Datas</font></td>
            <td height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
              <input name="tf_data_inic" type="text" class="form" id="tf_data_inic" value="<?php  echo $tf_data_inic ?>" size="9" maxlength="10">
              - 
              <input name="tf_data_final" type="text" class="form" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
            </td>
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Tipo de doc.</font></td>
            <td height="0" colspan="4"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
              <select name="dd_tipodoc" id="dd_tipodoc" class="combo_normal">
                <option value="" <?php  if($dd_tipodoc == "") echo "selected" ?>>Todos</option>
<?php  //                <option value="1" <_? if($dd_tipodoc == 1) echo "selected" ?_>>Corte (1)</option> ?>
                <option value="2" <?php  if($dd_tipodoc == 2) echo "selected" ?>>Money (2)</option>
                <option value="3" <?php  if($dd_tipodoc == 3) echo "selected" ?>>ExpressMoney(3)</option>
                <option value="4" <?php  if($dd_tipodoc == 4) echo "selected" ?>>ExpressMoney LH (4)</option>
                <option value="6" <?php  if($dd_tipodoc == 6) echo "selected" ?>>Depósito Saldo Gamer (6)</option>
              </select>
              </font></td>
            <td width="0" height="0"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                </font></div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Banco</font></td>
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
              <select name="dd_banco" id="dd_banco" class="combo_normal">
                <option value="">Todos</option>
                <?php  while($pgbco = pg_fetch_array($resbco)) { ?>
                <option value="<?php  echo $pgbco['bco_codigo'] ?>" <?php  if($pgbco['bco_codigo'] == $dd_banco) echo "selected" ?>><?php  echo $pgbco['bco_nome'] ?></option>
                <?php  } ?>
              </select>
              </font><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
              </font></td>
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Situa&ccedil;&atilde;o</font></td>
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
              <select name="dd_situacao" id="dd_situacao" class="combo_normal">
                <option value="" <?php  if($dd_situacao == "") echo "selected" ?>>Todos</option>
                <option value="1" <?php  if($dd_situacao == 1) echo "selected" ?>>Disponível</option>
                <option value="2" <?php  if($dd_situacao == 2) echo "selected" ?>>Conciliado</option>
              </select>
              </font></td>
            <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Valor 
              </font> </td>
            <td><select name="tf_valor_oper">
                <option value=">" <?php  if(isset($tf_valor_oper) && $tf_valor_oper == ">") echo "selected" ?>></option>
                <option value="=" <?php  if(!isset($tf_valor_oper) || !$tf_valor_oper || isset($tf_valor_oper) && $tf_valor_oper == "=") echo "selected" ?>>=</option>
                <option value="<" <?php  if(isset($tf_valor_oper) && $tf_valor_oper == "<") echo "selected" ?>><</option>
              </select></td>
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
              <input name="tf_valor" type="text" class="form" id="tf_valor" value="<?php if(isset($tf_valor)) echo $tf_valor ?>" size="9" maxlength="10">
              </font></td>
            <td width="0" height="0"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                </font></div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Cod. 
              Documento</font></td>
            <td width="0" height="0"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
              <input name="tf_cod_documento" type="text" class="form" id="tf_cod_documento" value="<?php  echo $tf_cod_documento ?>" size="10" maxlength="10">
              </font></td>
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Documento</font></td>

            <td height="0" colspan="4"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
              <input name="tf_documento" type="text" class="form" id="tf_documento" value="<?php  echo $tf_documento ?>" size="30" maxlength="30">
              </font></td>
            <td width="0" height="0"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> &nbsp;</font></div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Usuário:</font></td>
            <td width="0" height="0"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
				<select name="dd_usuario" id="dd_usuario" class="combo_normal">
					<option value="0">Escolha o usuário</option>
                      <?php  
					  
					  while ($pgusuario = pg_fetch_array ($rs_usuario)) { ?>
                      <option value="<?php  echo $pgusuario['ug_id'] ?>" <?php  if(isset($dd_usuario) && $pgusuario['ug_id'] == $dd_usuario) echo "selected" ?>><?php  echo substr($pgusuario['ug_nome_fantasia'],0,25) ?> (ID: <?php  echo $pgusuario['ug_id'] ?>)</option>
                      <?php  
					}
					
						?>
				</select>
              
              </font></td>
            <td width="0" height="0"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>

            <td height="0" colspan="4"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
            <td width="0" height="0"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info">
                </font></div></td>
          </tr>
        </table>
      </form>
        <table class="table">
			<tr> 
				<td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
					<?php  if($total_table > 0) { ?>
						Exibindo resultados <strong><?php  echo $inicial + 1 ?></strong> a <strong><?php  echo $reg_ate ?></strong> de <strong><?php  echo $total_table ?></strong> <span id="txt_totais" style="color:blue"></span></font>
					<?php  } else { ?>
						&nbsp;
					<?php  } ?>
				</td>
				<td></td>
			</tr>
		</table>   
        <table class="table">
        <tr bgcolor="#0086c5"> 
          <td><strong><a href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=bol_codigo" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF" class="link_br">C&oacute;digo</font></a></strong> 
            <?php  if($ncamp == 'bol_codigo') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
          </td>
          <td><strong><a href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=bol_data" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF" class="link_br">Data</font></a></strong> 
            <?php  if($ncamp == 'bol_data') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
          </td>
          <td><strong><a href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=bol_importacao" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF" class="link_br">Importa&ccedil;&atilde;o</font></a></strong> 
            <?php  if($ncamp == 'bol_importacao') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
          </td>
          <td><strong><a href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=bco_nome" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Banco</span></font></a></strong> 
            <?php  if($ncamp == 'bco_nome') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
          </td>
          <td><strong><a href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=bol_cod_documento" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Cod 
            Documento</span></font></a></strong> 
            <?php  if($ncamp == 'bol_cod_documento') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
          </td>
          <td><strong><a href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=bol_documento" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Documento</span></font></a></strong> 
            <?php  if($ncamp == 'bol_documento') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
          </td>
          <td> <div align="right"> 
              <?php  if($ncamp == 'bol_valor') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
              <strong><a href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=bol_valor" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Valor</span></font></a></strong></div></td>
          <td><div align="center"><strong><a href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=bol_aprovado" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Situa&ccedil;&atilde;o</span></font></a> 
              </strong></div></td>
          <td><font face="Arial, Helvetica, sans-serif" size="2" color="#0086c5">x</font></td>
        </tr>
        <?php 
			$cor1 = "#F5F5FB";
			$cor2 = "#F5F5FB";
			$cor3 = "#FFFFFF"; 	
//echo "rows: ".pg_num_rows($resest)."<br>";
//echo "B: ".date("Y-m-d H:i:s")."<br>";
//die("Stop");
            if(!isset($bol_valor_total))
                $bol_valor_total = null;
            
			while ($pgest = pg_fetch_array($resest))
			{
				$valor = 1;
				$bol_valor_total += $pgest['bol_valor'];
				
				if($pgest['bol_aprovado'] == 1)
					$bol_aprovado = "Conciliado";
				else
					$bol_aprovado = "Disponível";
		 ?>
        <tr> 
          <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
            <a href="altera.php?BolCod=<?php  echo $pgest['bol_codigo']; ?>" class="link_azul"> 
            <?php  echo $pgest['bol_codigo'] ?></a></font></td>
          <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo formata_data($pgest['bol_data'], 0) ?></font></td>
          <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo formata_timestamp ($pgest['bol_importacao'],2) ?></font></td>
          <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgest['bco_nome'] ?></font></td>
          <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgest['bol_cod_documento'] ?></font></td>
          <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgest['bol_documento'] ?></font></td>
          <td bgcolor="<?php  echo $cor1 ?>" class="text-right <?php echo ($bol_aprovado == "Disponível") ? "txt-azul" : "txt-preto";?>">
                <?php  echo number_format($pgest['bol_valor'], 2, ',', '.') ?>
          </td>
          <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $bol_aprovado ?></font></div></td>
          <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><a href="deleta.php?BolCod=<?php  echo $pgest['bol_codigo'] . $varsel ?>" onClick="GP_popupConfirmMsg('Deseja excluir esta informação de boleto?');return document.MM_returnValue"><img src="/images/deletar.gif" alt="Excluir Registro" width="12" height="14" border="0"></a></div></td>
        </tr>
        <?php 

       if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} }
	   if (!isset($valor) || !$valor)
	   {  ?>
        <tr bgcolor="#f5f5fb"> 
          <td colspan="9" bgcolor="<?php  echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
              N&atilde;o h&aacute; registros<br>
              <br>
              </strong></font></div></td>
        </tr>
        <?php  } else { ?>
        <tr bgcolor="#E4E4E4"> 
          <td colspan="6"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>SUBTOTAL</strong></font></td>
          <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($bol_valor_total, 2, ',', '.') ?></strong></font></div></td>
          <td>&nbsp;</td>
          <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></font></div></td>
        </tr>
        <tr bgcolor="#E4E4E4"> 
          <td colspan="6"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></td>
          <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($bol_valor_total_i, 2, ',', '.') ?></strong></font></div></td>
          <td>&nbsp;</td>
          <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></font></div></td>
        </tr>
        <tr bgcolor="#E4E4E4"> 
          <td colspan="9" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><strong> 
            OBS: Valores expressos em R$.</strong></font></td>
        </tr>
<script language="JavaScript">
  document.getElementById('txt_totais').innerHTML = '( <?php echo number_format($bol_valor_total, 2, ',', '.') ?> / <?php echo number_format($bol_valor_total_i, 2, ',', '.') ?>)';
</script>

        <?php 
			  $time_end = getmicrotime();
			  $time = $time_end - $time_start;
	  ?>
        <tr> 
          <td colspan="9" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php  echo $search_msg . number_format($time, 2, '.', '.') . $search_unit?> 
            </font></td>
        </tr>
        <?php 
			paginacao_query($inicial, $total_table, $max, '7', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
		?>
        <?php   }  ?>
      </table>
      <?php  pg_close ($connid); ?>
    </td>
  </tr>
</table>
</html>