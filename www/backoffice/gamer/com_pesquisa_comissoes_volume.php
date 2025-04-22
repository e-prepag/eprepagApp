<?php 
set_time_limit ( 6000 ) ;
ob_start();
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

$time_start = getmicrotime();

// ==== Monta $sql 
//$sql = "select *, (select opr_nome from operadoras o where o.opr_codigo=c.co_opr_codigo) as opr_nome from tb_comissoes c where 1=1 ";
$sql = "select * from operadoras o left outer join tb_operadora_vendas_total_mes c on o.opr_codigo=c.ovtm_opr_codigo where 1=1 \n";
if($dd_opr_codigo) {
	$sql .= " and ovtm_opr_codigo=".$dd_opr_codigo." \n";
}
if($dd_canal) {
	$sql .= " and ovtm_canal='".$dd_canal."' \n";
}

$sql .= " order by ovtm_opr_codigo, ovtm_data desc, ovtm_canal, ovtm_data_inclusao desc \n";
		// --where co_canal='P' and co_opr_codigo=13 

// ==== Recupera registros do BD
$rs = SQLexecuteQuery($sql);
if(!$rs) {
	echo "Erro ao listar Comissõess por Volume.\n";
	echo "sql: ".$sql."<br>\n<hr>\n";
//	die("Stop");
}

$total_table = pg_num_rows($rs);
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<div class="col-md-12">
    <table class="table txt-preto fontsize-pp">
	<tr valign="top" align="center">
		  <td>
		    <form name="form1" method="POST" action="com_pesquisa_comissoes.php">
			<table class="table txt-preto fontsize-pp">
					<tr bgcolor="F0F0F0">
					  <td class="texto" align="center" colspan="6"><b>Pesquisa <b><?php echo " (".$total_table." registro"?></b><?php if($total_table>1) echo "s"; ?><?php echo ")"?></b></td>
					</tr>
					<tr bgcolor="F0F0F0">
					  <td width="16%" align="center" class="texto"><?php
							// Select operadoras
							$sql_opr = "select * from operadoras order by opr_nome";
							$rs_opr = SQLexecuteQuery($sql_opr);
							if(!$rs_opr) {
								echo "Erro ao listar Operadoras.\n";
								die("Stop");
							} else {
								echo "<select name='dd_opr_codigo'>\n";
									echo "<option value=''";
									if($dd_opr_codigo=='') {
										echo " selected";
									}
									echo ">Todas as operadoras</option>\n";
								while($rs_opr_row = pg_fetch_array($rs_opr)){ 	
									echo "<option value='".$rs_opr_row['opr_codigo']."'";
									if($rs_opr_row['opr_codigo']==$dd_opr_codigo) {
										echo " selected";
									}
									echo ">".$rs_opr_row['opr_nome']." (".$rs_opr_row['opr_codigo'].")</option>\n";
								}
								echo "</select>\n";
							}

					  ?></td>
					  <td width="17%" align="center" class="texto">&nbsp;</td>
					  <td colspan="3" align="center" class="texto">
						  <select name='dd_volume_tipo'>
							<option value=''<?php echo (($dd_volume_tipo!='D' && $dd_volume_tipo!='I')?" selected":"") ?>>Todos os tipos de Comissão por Volume</option>
							<option value='D'<?php echo (($dd_volume_tipo=='D')?" selected":"") ?>>Comissão por Volume - Direta</option>
							<option value='I'<?php echo (($dd_volume_tipo=='I')?" selected":"") ?>>Comissão por Volume - Indireta</option>
						</select>
						</td>
					  <td width="14%">&nbsp;</td>
					</tr>
					<tr bgcolor="F5F5FB">
					  <td class="texto" align="center"><div align="right">&nbsp;</div></td>
					  <td class="texto" align="center"><div align="left">&nbsp;</div></td>
					  <td colspan="3" align="center" class="texto">&nbsp;</td>
					  <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-info btn-sm"></td>
					</tr>
				</table>
			  </form>
         </td>
     </tr>
     <tr>
        <td>
		<?php

		// ==== Lista tabela com registros

		$j = 1;
		if($rs && pg_num_rows($rs) != 0){
			$lista_logins = "";

// ovtm_opr_codigo, ovtm_data, ovtm_canal, ovtm_data_inclusao, ovtm_total, ovtm_comissao
			echo "<table class=\"table txt-preto fontsize-pp\">\n";
			echo "<tr>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>n</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>opr_codigo</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>opr_nome</font></td>";

			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>ovtm_id</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>ovtm_canal</font></td>";

			echo "<td width='120'><font size='1' face='Arial, Helvetica, sans-serif'>data_inclusao</font></td>";
			echo "<td width='120'><font size='1' face='Arial, Helvetica, sans-serif'>data_atualizacao</font></td>";

			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>comissao</font></td>";
			echo "<td width='80'><font size='1' face='Arial, Helvetica, sans-serif'>data</font></td>";

			echo "<td width='80'><font size='1' face='Arial, Helvetica, sans-serif'>total</font></td>";
			echo "<td width='80'><font size='1' face='Arial, Helvetica, sans-serif'>definitivo</font></td>";
			echo "</tr>\n";

			while($rs_row = pg_fetch_array($rs)){ 	
				echo "<tr align='center'".(($rs_row['ovtm_comissao']==0)?" class=\"txt-vermelho bg-amarelo trListagem\"":" class=\"trListagem\"").">";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".($j++)."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['ovtm_opr_codigo']."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['opr_nome']."</font></td>";
						
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['ovtm_id']."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['ovtm_canal']."</font></td>";

				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".substr($rs_row['ovtm_data_inclusao'], 0, 19)."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".substr($rs_row['ovtm_data_atualizacao'], 0, 19)."</font></td>";

				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['ovtm_comissao']."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['ovtm_data']."</font></td>";

				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".number_format($rs_row['ovtm_total'], 2, ',', '.')."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".(($rs_row['ovtm_tipo_definitivo']==1)?"SIM":(($rs_row['ovtm_tipo_definitivo']==-1)?"não":"-"))."</font></td>";

				echo "</tr>\n";
			}
			echo "</table><hr>\n";
		} else {
			echo "Sem registros<br>";
		}

		?>

         </td>
     </tr>
	<?php 
		
	$varse1 .= "";
		
//	paginacao_query($inicial, $total_table, $max, 50, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varse1);

	?>
	</table>
<?php
echo "<font size='1' face='Arial, Helvetica, sans-serif'>".$search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit."</font>";
?>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
