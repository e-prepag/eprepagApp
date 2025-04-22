<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";


	set_time_limit ( 3000 ) ;

	$time_start_stats = getmicrotime();

	$sql  = "select * from lanhouses_indicadas order by li_data_inclusao desc";
	$rs_logins = SQLexecuteQuery($sql);
	$n_lans = pg_num_rows($rs_logins);
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table bg-branco fontsize-pp">
  <tr> 
  <td width="100%" valign="top"> 
	<form method=post action="lista_LH_indicadas.php">
        <table width="100%" border="0">
            <tr> 
              <td width="50%" class="texto" valign="top" align="right"> 
                  <input type="submit" class="btn btn-info" value="Atualiza">
              </td>
            </tr>
        </table>
	 </form>
  </td>
  </tr>
  <tr> 
      <td width="100%" valign="top"> <div align="left">
	<?php
	
	if($rs_logins && pg_num_rows($rs_logins) > 0){
		echo "<p>Lista de PDVs indicados pelos jogadores para participar no Campeonato (este cadastro é independente do cadastro de Lanhouses da E-Prepag)</p>";
		echo "<p>Total de PDVs encontrados: $n_lans</p>\n";
		echo "<table class='table'>\n";
		$scol1 = "#CCFFCC";
		$scol2 = "#FFFFFF";
		$scol = $scol1;
		$i_row = 1;

		echo "<tr bgcolor='#99CCFF' align='center'>
				<td><font color='#000000'>&nbsp;<b>i</b>&nbsp;</font></td>
				<td align='center'><font color='#00000'>&nbsp;<b>Nome&nbsp;PDV</b>&nbsp;</font></td>
				<td align='center'><font color='#00000'>&nbsp;<b>Data&nbsp;inclusão</b>&nbsp;</font></td>
				<td align='center'><font color='#00000'>&nbsp;<b>Email</b>&nbsp;</font></td>
				<td align='center'><font color='#00000'>&nbsp;<b>Tel</b>&nbsp;</font></td>
				<td align='center'><font color='#00000'>&nbsp;<b>Responsável</b>&nbsp;</font></td>
				<td align='center'><font color='#00000'>&nbsp;<b>Cidade</b>&nbsp;</font></td>
				<td align='center'><font color='#00000'>&nbsp;<b>UF</b>&nbsp;</font></td>
				<td align='center'><font color='#00000'>&nbsp;<b>Email&nbsp;jogador</b>&nbsp;</font></td>
				<td align='center'><font color='#00000'>&nbsp;<b>IP</b>&nbsp;</font></td>
				</tr>\n";

		while($rs_logins_row = pg_fetch_array($rs_logins)) {

		// li_nome, li_data_inclusao , li_email, li_tel, li_resp, li_cidade, li_estado, li_seu_email, li_ip
		echo "<tr bgcolor='".$scol."'  class='fontsize-pp' align='center'>
				<td><font color='#000000'>&nbsp;<b>".($i_row++)."</b>&nbsp;</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['li_nome']."</font></td>
				<td align='center'><font color='#00000'>".substr($rs_logins_row['li_data_inclusao'],0,19)."</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['li_email']."</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['li_tel']."</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['li_resp']."</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['li_cidade']."</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['li_estado']."</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['li_seu_email']."</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['li_ip']."</font></td>
				</tr>\n";

			$scol = ($scol==$scol1)?$scol2:$scol1;

		}

		echo "</table>\n";

	} else {
		echo "<p><font color='#FF0000'><b>Erro ao procurar PDVs cadastradas</b></font></p>\n";
	}

	?>
		</div></td>
      </tr>
    </table>
</center>
<?php 
	echo "<p><font color='#000000'>Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, ',', '.')."s</font></p>";
?>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</body></html>
