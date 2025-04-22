<?php
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

	$pos_pagina = $seg_auxilar;
        
	$time_start = getmicrotime();

//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "<pre>";
//print_r ($GLOBALS);
//print_r ($_POST);
//print_r ($_GET);
//echo "</pre>";
//echo "dd_estabelecimento1: ".$dd_estabelecimento1."<br>";
//echo "Submit: $Submit<br>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		$Submit = "Buscar";
	}

	if(!$ncamp1) $ncamp1 = 've_data_inclusao';


	if(!$tf_data_inicial)  {
		$resdatainicio = pg_exec($connid, "select ve_data_inclusao from dist_vendas_pos where ve_estabelecimento='$dd_estabelecimento1' order by ve_data_inclusao limit 1");
		if($pgdatainicio = pg_fetch_array ($resdatainicio)) {
			$tf_data_inicial = substr($pgdatainicio['ve_data_inclusao'],8,2)."/".substr($pgdatainicio['ve_data_inclusao'],5,2)."/".substr($pgdatainicio['ve_data_inclusao'],0,4);
		} else {
			$tf_data_inicial = date('d/m/Y');
		}
		$today_data = date('d/m/Y');
		$iday = intval(substr($today_data,0,2));
		$imonth = intval(substr($today_data,3,2));
		$iyear = intval(substr($today_data,6,4));

		$tf_data_inicial = date('d/m/Y', mktime(0,0,0,$imonth,$iday-7,$iyear));
	}
	if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');

	if(!$inicial)          $inicial         = 0;
	if(!$range)            $range           = 1;
	if(!$ordem)            $ordem           = 0;
	if($BtnSearch)         $inicial         = 0;
	if($BtnSearch)         $range           = 1;
	if($BtnSearch)         $total_table     = 0;

	$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
	$data_inicial_limite = '01/08/2004';
	$FrmEnviar = 1;
	
//echo "tf_data_inicial: $tf_data_inicial<br>";	
//echo "tf_data_final: $tf_data_final<br>";	
//echo "data_inicial_limite: $data_inicial_limite<br>";	

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "/sys/imagens/proxima.gif";
	$img_anterior = "/sys/imagens/anterior.gif";
	$max          = 50; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

//	$resuf = pg_exec($connid, "select uf from uf order by uf");
//	$resuf_except = pg_exec($connid, "select uf from uf order by uf");

	$resusuario = pg_exec($connid, "select ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado from dist_vendas_pos where ve_estabelecimento='$dd_estabelecimento1' order by ve_data_inclusao desc limit 1");  

	$resvalor = pg_exec($connid, "select ve_valor, count(*) as n from dist_vendas_pos where ve_estabelecimento='$dd_estabelecimento1' group by ve_valor order by ve_valor");  

	if(!verifica_data($tf_data_inicial))
	{
		$data_inic_invalida = true;
		$FrmEnviar = 0;
	}

	
	if(!verifica_data($tf_data_final))
	{
		$data_fim_invalida = true;
		$FrmEnviar = 0;
	}
	
	if(qtde_dias($data_inicial_limite, $tf_data_inicial) < 0)
	{
		$data_inicial_menor = true;
		$FrmEnviar = 0;
	}

	if($FrmEnviar == 1)
	{
		$where_data = "";
		$where_valor = "";
		$where_opr = "";
		$where_estabelecimento = "";

		if($tf_data_inicial && $tf_data_final) {
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 
			$where_data = " and ((ve_data_inclusao >= '".trim($data_inic)." 00:00:00') and (ve_data_inclusao <= '".trim($data_fim)." 23:59:59')) "; 
		}

		if($dd_valor) {
			$where_valor= " and (ve_valor=$dd_valor) ";
		}

		if($dd_operadora) {
			if(($dd_operadora=="OG") || ($dd_operadora=="HB") || ($dd_operadora=="MU"))
				$where_opr = " and (ve_jogo='$dd_operadora') ";
		}
		if($dd_operadora=="") $dd_valor = "";
//echo "dd_valor: ".$dd_valor."<br>";


		$estat  = "select ve_id, ve_data_inclusao, ve_valor, ve_jogo from dist_vendas_pos ";
		$estat  .= " where 1=1 ".$where_data." ".$where_valor." ".$where_opr." and ve_estabelecimento='$dd_estabelecimento1' ";		
	

		$res_count = pg_query($estat);
		$total_table = pg_num_rows($res_count);

		$estat .= " order by ".$ncamp1; 

		if($ordem == 0) {
			$estat .= " desc ";
			$img_seta = "/sys/imagens/seta_down.gif";	
		}
		else {
			$estat .= " asc ";
			$img_seta = "/sys/imagens/seta_up.gif";
		}

		$estat .= " limit ".$max; 
		$estat .= " offset ".$inicial;

	}
		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
$sql_transform=$estat;

//echo "Subtotal: $estat<br>";

	$resestat = pg_exec($connid, $estat);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;

	$varsel = "&dd_operadora=$dd_operadora&tf_data_inicial=$tf_data_inicial&tf_data_final=$tf_data_final&dd_valor=$dd_valor";
	$varsel .= "&dd_estabelecimento1=$dd_estabelecimento1&dd_vendas=$dd_vendas&dd_ultima_vendas=$dd_ultima_vendas";
		
?>
<html>
<head>

<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<title>E-Prepag</title>
<script language='javascript' src='/js/popcalendar.js'></script>
<script language="javascript" src="/js/jquery.js"></script>
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function envia_lista(id) { 
  document.formlista.op.value = "lst";
  document.formlista.id.value = id;
  document.formlista.action = "pos_detalhe_insere.php";
//alert("op: "+document.formlista.op.value+", id:"+document.formlista.id.value+", action: "+document.formlista.action+"");
  document.formlista.submit();
}

//-->
</script>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="903" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr> 
    <td height="22,5" valign="center" bgcolor="#00008C" width="903"><p><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><b><b><b>Lista Vendas de Estabelecimento de POS<br>
        </b></b></b></font></p></td>
  </tr>
  <tr> 
    <td align="center" valign="top" bgcolor="#FFFFFF"> <table width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top"> 
          <td height="100%"> 
			<form name="formlista" method="post" action="pos_lista_detalhe.php">
			<input type="hidden" name="op" id="op" value="">
			<input type="hidden" name="id" id="id" value="">
			<input type="hidden" name="ncamp1" id="ncamp1" value="<?php $ncamp1?>">
              <table width="100%" border="0" cellpadding="2" cellspacing="2">
                <tr bgcolor="#00008C"> 
                  <td colspan="6" bgcolor="#ECE9D8"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Pesquisa</font></td>
                  <td bgcolor="#ECE9D8" align="right"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      <input type="button" name="BtnInsert" value="Insere Novo" class="botao_search" onClick="envia_novo();">
                      </font></div></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Data 
                    Inicial da Venda:</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input name="tf_data_inicial" type="text" class="form" id="tf_data_inicial" value="<?php echo $tf_data_inicial ?>" size="9" maxlength="10">
                    <a href="#"><img src="/sys/imagens/cal.gif" width="16" height="16" alt="Calendário" onclick="popUpCalendar(this, formlista.tf_data_inicial, 'dd/mm/yyyy')" border="0" align="absmiddle"></a> 
                    </font></td>
                  <td width="90"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Data 
                    Final da Venda:</font></td>
                  <td colspan="3"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input name="tf_data_final" type="text" class="form" id="tf_data_final" value="<?php echo $tf_data_final ?>" size="9" maxlength="10">
                    <a href="#"><img src="/sys/imagens/cal.gif" width="16" height="16" alt="Calendário" onclick="popUpCalendar(this, formlista.tf_data_final, 'dd/mm/yyyy')" border="0" align="absmiddle"></a> 
                    </font></td>
                  <td width="62">&nbsp;</td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Estabelecimento: </font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <select name="dd_estabelecimento1" id="dd_estabelecimento1" class="combo_normal" onChange="document.formlista.submit()">
                      <?php while ($pgusuario = pg_fetch_array ($resusuario)) { ?>
							<option value="<?php echo $pgusuario['ve_estabelecimento'] ?>" <?php if($pgusuario['ve_estabelecimento'] == $dd_estabelecimento1) echo "selected"; $ve_estabtipo=$pgusuario['ve_estabtipo']; ?>><?php echo $pgusuario['ve_estabelecimento'] ?> </option>
					 <?php		
							$ve_estabelecimento = $pgusuario['ve_estabelecimento'];
							$ve_estabtipo = $pgusuario['ve_estabtipo']; 
							$ve_cidade = $pgusuario['ve_cidade'];
							$ve_estado = $pgusuario['ve_estado'];
					  ?>	
                      <?php } ?>
                    </select>
					</td>
                  <td width="90"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Tipo&nbsp;</font></td>
                  <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php=$ve_estabtipo?>&nbsp;</font></td>
                  <td width="62"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                </tr>
				<?php
					$telefones = "select ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado, ve_ddd, ve_tel from dist_vendas_pos where ve_estabelecimento='".$ve_estabelecimento."' and ve_estabtipo='".$ve_estabtipo."' and ve_cidade='".$ve_cidade."' and ve_estado='".$ve_estado."' and not (ve_tel is null) group by ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado, ve_ddd, ve_tel";
//echo $telefones."<br>";
					$restelefones = pg_exec($connid, $telefones);
					
				?>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Cidade:&nbsp;</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php=$ve_cidade." (".$ve_estado.")" ?> </td>
                  <td width="90"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Telefone:  &nbsp;</font></td>
                  <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php

					$bvirgula = false;
					while ($pgtel = pg_fetch_array($restelefones)) {
						if($bvirgula) echo ", ";
						if(!$bvirgula) $bvirgula = true;
						echo "<nobr>(".$pgtel['ve_ddd'].") ".$pgtel['ve_tel']."</nobr>";
					}
					
					?>&nbsp;</font></td>
                  <td width="62"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                </tr>

                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Operadora:</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
					<?php
						if($_SESSION["tipo_acesso_pub"]=='PU') {
					?>
						<?php=$_SESSION["opr_nome"]?>
						<input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php=$dd_operadora?>">
					<?php
					  } else {
					?>
                    <select name="dd_operadora" id="dd_operadora" class="combo_normal" onChange="document.formlista.dd_valor.value='';document.formlista.submit()">
							<option value=""<?php if(($dd_operadora!="OG") && ($dd_operadora!="MU") && ($dd_operadora!="HB")) echo "selected" ?>>Todas as Operadoras</option>
                            <option value="OG"<?php if($dd_operadora=="OG") echo "selected" ?>>ONGAME (13)</option>
                            <option value="HB"<?php if($dd_operadora=="HB") echo "selected" ?>>HABBO HOTEL (16)</option>
                            <option value="MU"<?php if($dd_operadora=="MU") echo "selected" ?>>MU ONLINE (17)</option>
					</select>
					<?php
					  } 
					?>
                    </font></td>
                  <td ><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Valor:</font></td>
                  <td colspan="3"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <select name="dd_valor" id="dd_valor" class="combo_normal" onChange="document.formlista.submit()">
					  <?php if($dd_operadora=="MU") { ?>
	                    <option value=""<?php if($dd_valor!=10) echo "selected" ?>>Todos os Valores</option>
						<option value="10"<?php if($dd_valor==10) echo "selected" ?>>R$ 10,00</option>
					  <?php } else if($dd_operadora=="OG") { ?>
	                    <option value=""<?php if(($dd_valor!=13) && ($dd_valor!=25) && ($dd_valor!=37) && ($dd_valor!=49)) echo "selected" ?>>Todos os Valores</option>
						<option value="13"<?php if($dd_valor==13) echo "selected" ?>>R$ 13,00</option>
						<option value="25"<?php if($dd_valor==25) echo "selected" ?>>R$ 25,00</option>
						<option value="37"<?php if($dd_valor==37) echo "selected" ?>>R$ 37,00</option>
						<option value="49"<?php if($dd_valor==49) echo "selected" ?>>R$ 49,00</option>
					  <?php } else if($dd_operadora=="HB") { ?>
	                    <option value=""<?php if(($dd_valor!=10) && ($dd_valor!=25) && ($dd_valor!=50)) echo "selected" ?>>Todos os Valores</option>
						<option value="10"<?php if($dd_valor==10) echo "selected" ?>>R$ 10,00</option>
						<option value="25"<?php if($dd_valor==25) echo "selected" ?>>R$ 25,00</option>
						<option value="50"<?php if($dd_valor==50) echo "selected" ?>>R$ 50,00</option>
					  <?php } ?>
					  <?php if(($dd_operadora!="OG") && ($dd_operadora!="MU") && ($dd_operadora!="HB")) { ?>
	                    <option value="">Todos os Valores</option>
					  <?php } ?>

                    </select>
                    </font></td>
                  <td width="62"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      <input type="submit" name="BtnSearch" value="Buscar" class="botao_search">
                      </font></div></td>
                </tr>
              </table>
            </form>
            <table border='0' width="100%" cellpadding="2" cellspacing="1">
              <tr> 
                <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                  <?php if($total_table > 0) { ?>
                  Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                  a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                  <?php } ?>
                </td>
                <td><div align="right"><a href="http://<?php echo $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] ?>/sys/admin/commerce/index.php"><img src="../../images/voltar_menu.gif" width="107" height="15" border="0" alt="Voltar para menu"></a> 
                    <?php
					$_SESSION['sqldata']=$sql_transform;
					?>&nbsp; &nbsp;<a href="http://<?php echo $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] ?>/sys/admin/stats/pos_lista.php?ordem=<?php=$ordem?>&ncamp=<?php=$ncamp?>&inicial=<?php=$inicial.$varsel?>"><img src="../../images/voltar.gif" width="47" height="15" border="0" alt="Voltar para lista"></a> 
                    </div>
                </td>
              </tr>
            </table>
            <table width="100%" border='0' cellpadding="2" cellspacing="1">
              <tr bgcolor="#00008C"> 
                <?php
				if($ordem == 1)
					$ordem = 0;
				else
					$ordem = 1;
				?>
                <td align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">ID</font></strong></td>
                <td align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp1=ve_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_br">Data</a></font></strong><?php if($ncamp1 == 've_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></td>
                <td align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp1=ve_valor&inicial=".$inicial.$varsel ?>" class="link_br">Valor</a></font></strong><?php if($ncamp1 == 've_valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></td>
                <td align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp1=ve_jogo&inicial=".$inicial.$varsel ?>" class="link_br">Jogo</a></font></strong><?php if($ncamp1 == 've_jogo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></td>

              </tr>
              <?php
					$cor1 = $query_cor1;
					$cor2 = $query_cor1;
					$cor3 = $query_cor2;

					while ($pgrow = pg_fetch_array($resestat)) {
						$valor = true;

				?>
              <tr bgcolor="#f5f5fb"> 
                <td bgcolor="<?php echo $cor1 ?>"><div align="center"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><?php=$pgrow['ve_id']?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="center"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><?php=$pgrow['ve_data_inclusao']?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="center"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><?php=number_format($pgrow['ve_valor'], 2, ',', '.')?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="center"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><?php=nomeOperadora($pgrow['ve_jogo'])?></font></div></td>

              </tr>
              <?php
				 		if($cor1 == $cor2) $cor1 = $cor3;
						else				$cor1 = $cor2;
					}
		 		if (!$valor) { ?>
              <tr bgcolor="#f5f5fb"> 
                <td colspan="10" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                    N&atilde;o h&aacute; registros.<br>
                    <br>
                    </strong></font></div></td>
              </tr>
              <?php } else { ?>
              <?php
					$time_end = getmicrotime();
					$time = $time_end - $time_start;
				?>
              <?php
					paginacao_query($inicial, $total_table, $max, '11', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp1, $varsel);
				?>
              <tr> 
                <td colspan="11">&nbsp;</td>
              </tr>
              <tr align="center"> 
                <td height="52" colspan="10" bgcolor="#FFFFFF"><p><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?> 
                    </font></p>
                  </td>
              </tr>
              <?php } ?>
            </table></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td colspan="3">
      <?php require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";?>
      <div align="center"></div></td></tr>
</table>
</body>
</html>
<?php
	#include "../../incs/footer.php";
?>