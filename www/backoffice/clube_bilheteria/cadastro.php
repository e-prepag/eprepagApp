
<?php 

require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

	$pos_pagina = $seg_auxilar;
?>

<?php 
	$time_start = getmicrotime();

	if(!$ncamp)    $ncamp       = 'ug_id';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 1;
	if($BtnSearch) $inicial     = 0;
	if($BtnSearch) $range       = 1;
	if($BtnSearch) $total_table = 0;
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100;	//$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;


	$sql = "select ug_id, ug_login, ug_ativo, ug_data_ultimo_acesso, ug_qtde_acessos, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia||' ('||ug.ug_tipo_cadastro||')'  WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome||' ('||ug.ug_tipo_cadastro||')'  END) as ug_lan_nome , ug.ug_tipo_cadastro, ug_cidade, ug_estado, ug_cep, ug_perfil_saldo, ug_risco_classif from dist_usuarios_games ug where ug_ativo='1' ";	

//echo $sql;

	$res_count = pg_query($sql);
	$total_table = pg_num_rows($res_count);

	$sql .= " order by ".$ncamp;

//echo $sql;

	if($ordem == 0)
	{
		$sql .= " desc ";
		$img_seta = "/images/seta_down.gif";	
	}
	else
	{
		$sql .= " asc ";
		$img_seta = "/images/seta_up.gif";
	}

//	$sql .= " limit ".$max; 
//	$sql .= " offset ".$inicial;

	$resid = pg_exec($connid, $sql);
	
	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;

//echo $sql."<br>";
?>

<link rel="stylesheet" href="/css/css.css" type="text/css">
<script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
<script language='javascript' src='/js/popcalendar.js'></script>

<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<table width="100%" border="0" cellspacing="6" cellpadding="0">
  <tr> 
    <td height="22,5" bgcolor="#00008C"><p><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><b><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><b><font color="#FF1931" size="3"> 
        </font><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><b><font color="#FFFFFF" size="2">PDV's cadastrados para vendas em  Bilheteria.com<?php if($lh_nome) echo " - para o PDV <font color='#FF0000'>'$lh_nome'</font>"; ?><br>
        </font></b></font></b></font></b></font></p></td>
  </tr>
  <tr> 
    <td>&nbsp;
    </td>
  </tr>

  <tr> 
    <td height="232">
	<table border='0' width="100%" cellpadding="" cellspacing="1">
        <tr> 
          <td height="1" colspan="2"><div align="left"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"> 
              </font></div></td>
        </tr>
        <tr> 
          <td height="10" bgcolor=""><div align="left"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"> 
              <?php if($total_table > 0 and false) { ?>
              Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> 
              de <strong><?php echo $total_table ?></strong></font> 
              <?php } else { ?>
              <?php } ?>
              <font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"></font></div></td>
          <td bgcolor=""><div align="right"><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><a href="/index.php" class="menu"><img src="/images/voltar.gif" width="50" height="15" border="0"></a></font></div></td>
        </tr>
      </table>
	  <table border='0' width="100%" cellpadding="1" cellspacing="1">
        <tr> 
          <?
			if($ordem == 1)
				$ordem = 0;
			else
				$ordem = 1;
		?>

          <td width="" bgcolor="#00008C" align="center"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">ID</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">Login</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">Ativo</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">Último acesso</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">Qtde<br>acessos</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C" align="center"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">Nome</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">Tipo</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C" align="center"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">Cidade</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">UF</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C" align="center"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">CEP</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C" align="center"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">Saldo</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">Classif.</b></font>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">Habilitada</b></font>
            </strong></nobr></td>
		</tr>
          <?php
				$nregs=0;
				$cor1 = $query_cor1; 
				$cor2 = $query_cor1;
				$cor3 = $query_cor2;
				while ($pgrow = pg_fetch_array($resid)) {
					$valor = 1;
					$txtcolor = ($pgrow['ug_ativo']==1)?"#666666":"#FF0000";

					$bBilheteria = (bUsaBilheteria(strtoupper($pgrow['ug_login'])) || bUsaBilheteria_2(strtoupper($pgrow['ug_login'])));

					if($bBilheteria) {

			?>

		<tr> 
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['ug_id'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['ug_login'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo (($pgrow['ug_ativo']==1)?"SIM":"não") ?></font></td>

		  <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><nobr><?php echo formata_data_ts($pgrow['ug_data_ultimo_acesso'], 0, true, false) ?></nobr></font></td>
          
		  <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['ug_qtde_acessos'] ?></font></td>
		  <td bgcolor="<?php echo $cor1 ?>"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['ug_lan_nome'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['ug_tipo_cadastro'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['ug_cidade'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['ug_estado'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['ug_cep'] ?></font></td>

		  <td bgcolor="<?php echo $cor1 ?>" align="right" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgrow['ug_perfil_saldo'], 2, ',','.') ?></font></td>
          
		  <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo (($pgrow['ug_risco_classif']==1)?"PÓS":"<font color='#FF0000'>pré</font>") ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$txtcolor?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo ( ($bBilheteria) ?"Sim":"-") ?></font></td>
        </tr>
        <?php
						$nregs ++;
					}
					if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} 
				}
				if (!$valor) {
			?>
        <tr bgcolor="#f5f5fb"> 
          <td colspan="4" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
              N&atilde;o h&aacute; registros.<br>
              <br>
              </strong></font></div></td>
        </tr>
        <?php  } else {  ?>
        <?php
			$time_end = getmicrotime();
			$time = $time_end - $time_start;
		?>
        <tr> 
          <td colspan="7" bgcolor="#FFFFFF"><br>&nbsp;<font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><b><?php echo $nregs; ?> lans cadastradas. </b>
            </font></td>
        </tr>
        <tr> 
          <td colspan="7" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?> 
            </font></td>
        </tr>
        <?php

			//paginacao_query($inicial, $total_table, $max, '4', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, '');
		?>
        <?php } ?>
      </table>
      <p>
      <p>&nbsp; </p>
      <p></p>
      &nbsp; 
      <?php
      	require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
      ?>
    </td>
  </tr>
</table>
</body>
</html>