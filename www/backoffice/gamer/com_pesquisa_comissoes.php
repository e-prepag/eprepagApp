<?php 
set_time_limit ( 6000 ) ;
ob_start();
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";

$time_start = getmicrotime();

// ==== Monta $sql 
//$sql = "select *, (select opr_nome from operadoras o where o.opr_codigo=c.co_opr_codigo) as opr_nome from tb_comissoes c where 1=1 ";
$sql = "select * from operadoras o left outer join tb_comissoes c on o.opr_codigo=c.co_opr_codigo where 1=1 \n";
if($dd_opr_codigo) {
	$sql .= " and co_opr_codigo=".$dd_opr_codigo." \n";
}
if($dd_canal) {
	$sql .= " and co_canal='".$dd_canal."' \n";
}
if($dd_tipo) {
	if($dd_tipo!="F" && $dd_tipo!="V") {
		$dd_tipo = "";
		echo "<font color='red'>Tipo de comissão deve ser 'F' ou 'V'</font><br>\n";
	}
}
if($dd_tipo) {
	$sql .= " and co_tipo='".$dd_tipo."' \n";
}
if($dd_volume_tipo) {
	if($dd_volume_tipo!="D" && $dd_volume_tipo!="I") {
		$dd_volume_tipo = "";
		echo "<font color='red'>Tipo de comissão por volume deve ser 'D' ou 'I'</font><br>\n";
	}
}
if($dd_volume_tipo) {
	$sql .= " and (co_volume_tipo='".$dd_volume_tipo."' or co_volume_tipo='' )\n";
}

$sql .= " order by co_opr_codigo, co_canal, co_data_inclusao desc, co_volume_tipo, co_volume_min \n";
		// --where co_canal='P' and co_opr_codigo=13 

//echo "sql: ".$sql."<br>\n<hr>\n";
//die("Stop");

// ==== Recupera registros do BD
$rs = SQLexecuteQuery($sql);
if(!$rs) {
	echo "Erro ao listar Comissõess.\n";
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
    <table class="txt-preto fontsize-pp table">
	<tr valign="top" align="center">
		  <td>
		    <form name="form1" method="POST" action="com_pesquisa_comissoes.php">
			<table class="txt-preto fontsize-pp table">
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
					  <td colspan="3" align="center" class="texto"><?php
						// Select canal
							$acanais= array('C' => 'Cartões', 'E' => 'Express Money', 'L' => 'Lanhouse', 'M' => 'Money', 'P' => 'POS' );
							echo "<select name='dd_canal'>\n";
								echo "<option value=''";
								if($dd_canal=='') {
									echo " selected";
								}
								echo ">Todos os canais</option>\n";
							foreach ($acanais as $key => $val) { 	
								echo "<option value='".$key."'";
								if($key==$dd_canal) {
									echo " selected";
								}
								echo ">".$val." (".$key.")</option>\n";
							}
							echo "</select>\n";
					  ?>
						</td>
					  <td width="14%">&nbsp;</td>
					</tr>
					<tr bgcolor="F0F0F0">
					  <td width="16%" align="center" class="texto">
						  <select name='dd_tipo'>
							<option value=''<?php echo (($dd_tipo!='F' && $dd_tipo!='V')?" selected":"") ?>>Todos os tipos de Comissão</option>
							<option value='F'<?php echo (($dd_tipo=='F')?" selected":"") ?>>Comissão Fixa</option>
							<option value='V'<?php echo (($dd_tipo=='V')?" selected":"") ?>>Comissão por Volume</option>
						</select>
					  </td>
					  <td width="17%" align="center" class="texto">&nbsp;</td>
					  <td colspan="3" align="center" class="texto">
						  <select name='dd_volume_tipo'>
							<option value=''<?php echo (($dd_volume_tipo!='D' && $dd_volume_tipo!='I')?" selected":"") ?>>Todos os tipos de Comissão por Volume</option>
							<option value='D'<?php echo (($dd_volume_tipo=='D')?" selected":"") ?>>Comissão por Volume - Direta</option>
							<option value='I'<?php echo (($dd_volume_tipo=='I')?" selected":"") ?>>Comissão por Volume - Indireta</option>
						</select>
						</td>
					  <td width="14%">&nbsp;</td>
					</tr>					<tr bgcolor="F5F5FB">
					  <td class="texto" align="center"><div align="right">&nbsp;</div></td>
					  <td class="texto" align="center"><div align="left">&nbsp;</div></td>
					  <td colspan="3" align="center" class="texto">&nbsp;</td>
					  <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info"></td>
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

			echo "<table class=\"txt-preto fontsize-pp table\">\n";
			echo "<tr>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>n</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>co_id</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>co_tipo</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>co_canal</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>co_volume_tipo</font></td>";

			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>co_opr_codigo</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>opr_nome</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>co_produto_codigo</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>co_data_inclusao</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>co_comissao</font></td>";

			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>co_volume_min</font></td>";
			echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>co_volume_max</font></td>";
			echo "</tr>\n";

			while($rs_row = pg_fetch_array($rs)){ 	
				echo "<tr align='center'".(($rs_row['co_comissao']==0)?" class='bg-amarelo txt-vermelho'":"").">";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".($j++)."</font></td>";
						
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['co_id']."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['co_tipo']."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['co_canal']."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['co_volume_tipo']."</font></td>";

				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['co_opr_codigo']."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['opr_nome']."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['co_produto_codigo']."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['co_data_inclusao']."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".$rs_row['co_comissao']."</font></td>";

				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".(($rs_row['co_tipo']=="V")?number_format($rs_row['co_volume_min'], 2, ',', '.'):"-")."</font></td>";
				echo "<td><font size='1' face='Arial, Helvetica, sans-serif'>".(($rs_row['co_tipo']=="V")?number_format($rs_row['co_volume_max'], 2, ',', '.'):"-")."</font></td>";

				echo "</tr>\n";
			}
			echo "</table><hr>\n";
		} else {
			echo "Sem registros de comissão por volume<br>";
		}
		?>

         </td>
     </tr>
	<?php 
		
	$varse1 .= "";
		
//	paginacao_query($inicial, $total_table, $max, 50, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varse1);

	?>
	</table>
</center>

<?php
echo "<font size='1' face='Arial, Helvetica, sans-serif'>".$search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit."</font>";
?>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
