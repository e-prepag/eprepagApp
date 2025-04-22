<?php
require_once '../../../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
	#session_start();
	
if(!isset($_SESSION['iduser']))
    $_SESSION['iduser'] = null;

if(!isset($local_acesso))
    $local_acesso = null;

if(!isset($dtaF))
    $dtaF = null;

if(!isset($dtaI))
    $dtaI = null;

if(!isset($ncamp))
    $ncamp = null;

if(!isset($fuf))
    $fuf = null;

if(!isset($trn_teste))
    $trn_teste = null;

if(!isset($valorfcid))
    $fcid = null;

if(!isset($_SESSION['local_acesso']))
    $_SESSION['local_acesso'] = null;

if(!isset($data_inic_invalida))
    $data_inic_invalida = null;

if(!isset($data_fim_invalida))
    $data_fim_invalida = null;

if(!isset($valor))
    $valor = null;

if(!isset($pin_total_valor))
    $pin_total_valor = null;

if(!isset($pin_total_qtde))
    $pin_total_qtde = null;

if(!isset($total_reg))
    $total_reg = null;


	$qtde_nivel = 3;
	$pagina_pos = 0;

	$nivel = '';
	for($i = 1 ; $i <= $qtde_nivel ; $i++)
	{ $nivel .= '../'; }

	$nivel_inc = substr($nivel, 0, strlen($nivel) - 3);
	
	//include $nivel_inc."../incs/functions.php";
	//$connid = pg_connect("host=$host port=$port dbname=$banco user=$usuario password=$senha");

	if(empty($_SESSION["iduser_bko"]) && empty($_SESSION["tipo_acesso"]) && empty($_SESSION["local_acesso"]))
	{ echo "<META HTTP-EQUIV='Refresh' Content=0;URL='http://".$_SERVER['HTTP_HOST'].":$server_port/'>"; }
	else
	{
		$resusr = pg_exec($connid, "select bko_logado from usuarios where id='".$_SESSION['iduser']."'");
		$pgusr = pg_fetch_array($resusr);
		
		if($pgusr['bko_logado'] == 'N')
		{ echo "<META HTTP-EQUIV='Refresh' Content=0;URL='http://".$_SERVER['HTTP_HOST'].":$server_port/'>"; }
		else
		{ 
			$local = $_SESSION["local_acesso"];
			$pagina = substr($local, $pagina_pos, 1);
			
	//		if($pagina == '0')
		//	{ echo "<META HTTP-EQUIV='Refresh' Content=0;URL='".$nivel."bko_prepag/mensagens_internas/negado.php'>"; }
		}
	}
    
	if (! $dtaF) $dtaF = date('d/m/Y');
	if (! $dtaI) $dtaI = date('d/m/Y');
	if (! $ncamp) $ncamp = 'trn_data';
	$enviar = 1;
	
	$rescid = pg_exec($connid, "select cid_codigo, municipio from cidades where uf='$fuf' order by municipio");
	
	if($trn_teste)
		{ $resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where opr_status='1' order by opr_nome"); }
	else
		{ $resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status='1') and (opr_codigo <> 78) order by opr_nome"); }
	
	if(!verifica_data($dtaI))
	{ $data_inic_invalida = TRUE; $enviar = 0; }
	else
	{			
		if(!verifica_data($dtaF))
		{ $data_fim_invalida = TRUE; $enviar = 0; }
	}				
	
		if($enviar == 1)
		{						
			$estat = "select t0.trn_data, t1.uf, t1.municipio, t2.opr_nome, t0.pin_valor, sum(t0.pin_qtde) as quantidade, sum(t0.pin_valor) as total_face from estat_venda t0, cidades t1, operadoras t2 ";
			$estat .= "where (t0.opr_codigo=t2.opr_codigo) ";
			
			if($fcid)
				{ $estat .= "and (t0.cid_codigo='".$fcid."') and (t0.cid_codigo=t1.cid_codigo) "; }
			else
				{ $estat .= "and (t0.cid_codigo=t1.cid_codigo) "; }
				
			if($fuf) { $estat .= " and (t1.uf='".$fuf."') "; }
		
			if(!$trn_teste) { $estat .= "and (t0.opr_codigo <> 78) "; }
				
			if ($dtaI && $dtaF) 
			{
				$data_inic = formata_data(trim($dtaI), 1);
				$data_fim = formata_data(trim($dtaF), 1); 
				$estat .= " and ((trn_data >= '".trim($data_inic)."') and (trn_data <= '".trim($data_fim)."')) "; 
			}
	
			$estat .= "group by trn_data, t1.uf, t1.municipio, t2.opr_nome, t0.pin_valor ";
			$estat .= " order by ".$ncamp;					 
		}
		else
		{ $estat = "select est_codigo from estat_venda where est_codigo=5";}
	
		$resestat = pg_exec($estat);
	
		$varsel = "&dtaF=$dtaF&dtaI=$dtaI&fuf=$fuf&fcid=$fcid&trn_teste=$trn_teste";			
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('dtaI','dtaF',optDate);
        
    });
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="../../pquery.php">Relatórios de Venda</a></li>
        <li class="active">Relatório de Vendas por Cidade</li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td align="center" valign="top" bgcolor="#FFFFFF"> <table width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top"> 
          <td height="100%"> <form name="form1" method="post" action="<?php echo $PHP_SELF ?>">
              <table width="100%" border="0" cellpadding="2" cellspacing="2">
                <tr bgcolor="#ECE9D8"> 
                  <td colspan="8"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>Pesquisa</strong></font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="115"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Intervalo 
                    das Datas:</font></td>
                  <td width="156"> <input name="dtaI" type="text" class="form" id="dtaI" value="<?php echo $dtaI ?>" size="9" maxlength="10"> 
                    <input name="dtaF" type="text" class="form" id="dtaF" value="<?php echo $dtaF ?>" size="9" maxlength="10"> 
                  </td>
                  <td width="21"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">UF:</font></td>
                  <td width="74"><select name="fuf" id="select3" class="combo_normal" onChange="document.form1.submit()">
                      <option value="">Todas</option>
                      <option value="AC" <?php if ($fuf == "AC") echo "selected" ?>>AC</option>
                      <option value="AL" <?php if ($fuf == "AL") echo "selected" ?>>AL</option>
                      <option value="AM" <?php if ($fuf == "AM") echo "selected" ?>>AM</option>
                      <option value="AP" <?php if ($fuf == "AP") echo "selected" ?>>AP</option>
                      <option value="BA" <?php if ($fuf == "BA") echo "selected" ?>>BA</option>
                      <option value="CE" <?php if ($fuf == "CE") echo "selected" ?>>CE</option>
                      <option value="DF" <?php if ($fuf == "DF") echo "selected" ?>>DF</option>
                      <option value="ES" <?php if ($fuf == "ES") echo "selected" ?>>ES</option>
                      <option value="GO" <?php if ($fuf == "GO") echo "selected" ?>>GO</option>
                      <option value="MA" <?php if ($fuf == "MA") echo "selected" ?>>MA</option>
                      <option value="MG" <?php if ($fuf == "MG") echo "selected" ?>>MG</option>
                      <option value="MS" <?php if ($fuf == "MS") echo "selected" ?>>MS</option>
                      <option value="MT" <?php if ($fuf == "MT") echo "selected" ?>>MT</option>
                      <option value="PA" <?php if ($fuf == "PA") echo "selected" ?>>PA</option>
                      <option value="PB" <?php if ($fuf == "PB") echo "selected" ?>>PB</option>
                      <option value="PE" <?php if ($fuf == "PE") echo "selected" ?>>PE</option>
                      <option value="PI" <?php if ($fuf == "PI") echo "selected" ?>>PI</option>
                      <option value="PR" <?php if ($fuf == "PR") echo "selected" ?>>PR</option>
                      <option value="RJ" <?php if ($fuf == "RJ") echo "selected" ?>>RJ</option>
                      <option value="RN" <?php if ($fuf == "RN") echo "selected" ?>>RN</option>
                      <option value="RO" <?php if ($fuf == "RO") echo "selected" ?>>RO</option>
                      <option value="RR" <?php if ($fuf == "RR") echo "selected" ?>>RR</option>
                      <option value="RS" <?php if ($fuf == "RS") echo "selected" ?>>RS</option>
                      <option value="SC" <?php if ($fuf == "SC") echo "selected" ?>>SC</option>
                      <option value="SE" <?php if ($fuf == "SE") echo "selected" ?>>SE</option>
                      <option value="SP" <?php if ($fuf == "SP") echo "selected" ?>>SP</option>
                      <option value="TO" <?php if ($fuf == "TO") echo "selected" ?>>TO</option>
                    </select></td>
                  <td width="44"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Cidade:</font></td>
                  <td width="254" bgcolor="#F5F5FB"><select name="fcid" id="select" class="combo_normal">
                      <option value="">Todos as Cidades</option>
                      <?php while ($pgcid = pg_fetch_array ($rescid)) { ?>
                      <option value="<?php echo $pgcid['cid_codigo'] ?>" <?php if($pgcid['cid_codigo'] == $fcid) echo "selected" ?>><?php echo $pgcid['municipio'] ?></option>
                      <?php } ?>
                    </select></td>
                  <td width="57"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                          <input type="submit" class="btn btn-sm btn-info" name="Submit" value="Buscar">
                      </font></div></td>
                </tr>
              </table>
            </form>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr> 
                <td> 
                  <?php if($data_inic_invalida == TRUE) { echo "<font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>Data inicial Inválida</b></font>"; } ?>
                  <?php if($data_fim_invalida == TRUE)  { echo "<font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>Data final Inválida</b></font>"; } ?>
                </td>
                              <td bgcolor=""></td>
			  </tr>
            </table>
              <table class="table top20">
              <tr class="bg-azul-claro txt-branco"> 
                <td width="76"><strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo "?ncamp=trn_data" ?><?php echo $varsel ?>" class="txt-branco">Data</a></font></strong></td>
                <td width="26"><strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo "?ncamp=uf" ?><?php echo $varsel ?>" class="txt-branco">UF</a></font></strong></td>
                <td width="110"><strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo "?ncamp=municipio" ?><?php echo $varsel ?>" class="txt-branco">Cidade</a></font></strong></td>
                <td width="149" ><strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo "?ncamp=opr_nome" ?><?php echo $varsel ?>" class="txt-branco">Operadora</a></font></strong></td>
                <td width="43" ><div align="right"><strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo "?ncamp=quantidade" ?><?php echo $varsel ?>" class="txt-branco">Qtde</a></font></strong></div></td>
                <td ><div align="right"><strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo "?ncamp=pin_valor" ?><?php echo $varsel ?>" class="txt-branco">Valor 
                    da face</a></font></strong></div></td>
                <td><div align="right"><strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo "?ncamp=total_face" ?><?php echo $varsel ?>" class="txt-branco">Valor 
                    Total</a></font></strong></div></td>
                <?php
					$cor1 = "#F5F5FB";
					$cor2 = "#F5F5FB";
					$cor3 = "#FFFFFF";				
					while ($pgestat = pg_fetch_array($resestat))
					{
						$pin_total_valor += $pgestat['total_face'];
						$pin_total_qtde += $pgestat['quantidade'];
						$total_reg ++;
						$valor = 1;
				?>
              <tr bgcolor="#f5f5fb"> 
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo formata_data($pgestat['trn_data'], 0) ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgestat['uf'] ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgestat['municipio'] ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgestat['opr_nome'] ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgestat['quantidade'], 0, ',', '.') ?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgestat['pin_valor'], 2, ',', '.') ?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgestat['total_face'], 2, ',', '.') ?></font></div></td>
              </tr>
              <?php
				 		if ($cor1==$cor2) {$cor1=$cor3;} else {$cor1=$cor2;} 			  
					}
			 		if (!$valor) { ?>
              <tr bgcolor="#f5f5fb"> 
                <td colspan="11" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                    N&atilde;o h&aacute; registros.<br>
                    <br>
                    </strong></font></div></td>
              </tr>
              <?php } else { ?>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="4"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAIS</strong></font> 
                  <div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></font></div></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="2"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_valor, 2, ',', '.') ?></strong></font></div></td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="7" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><strong>Total 
                  de registros na tela: <?php echo $total_reg ?><br>
                  OBS: Valores expressos em R$.</strong></font></td>
              </tr>
              <?php } ?>
            </table></td>
        </tr>
      </table></td>
  </tr>
</table>
<br><br><br><br><br><br><br><br>
<?php  
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>

</body>
</html>
