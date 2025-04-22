<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/pdv/b2c/config.inc.b2c.php";

if(empty($tf_store_id))	$tf_store_id	= null;
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<form name="form1" method="post" action="com_lista_b2c.php">
    <table class="table txt-preto fontsize-pp">
      <tr bgcolor="#FFFFFF"> 
        <td colspan="4" bgcolor="#ECE9D8" class="texto">Produtos</font></td>
      </tr>
      <tr bgcolor="#F5F5FB"> 
        <td width="100" class="texto">Produtos B2C</td>
        <td>
            <select name="tf_store_id" class="form2">
                <option value="" <?php if($tf_store_id=="") echo "selected" ?>>Todos os produtos</option>
            <?php
                foreach($GLOBALS['B2C_PRODUCT'] as $key => $val) {
            ?>
                <option value="<?php echo $key ?>" <?php if ($tf_store_id == $key) echo "selected";?>><?php echo $val['name']; ?></option>
            <?php
                }	
            ?>
            </select>
        </td>
        <td width="100" class="texto">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr bgcolor="#F5F5FB"> 
        <td align="right" colspan="4"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td> 
      </tr>
    </table>
</form>
<br>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td> 
		<table class="table txt-preto fontsize-pp">
			<tr class="texto" style="font-weight:bold" align="center"> 
				<td colspan="7" align="left">&nbsp;Total de cadastros: <?php echo count($GLOBALS['B2C_PRODUCT'])?><br>
				<div id="div_link_monitor">&nbsp;<a href="#monitor_atividade">Monitor de atividade da B2C</a></div>
				</td>
			</tr>
			<tr class="texto" style="font-weight:bold" align="center"> 
				<td>Nome</td><td>ID</td><td>Fornecedor</td><td>Validade</td><td>Preço</td><td><nobr>Comissão Total</nobr></td><td><nobr>Comissão da LAN</nobr></td>
			</tr>
			<?php
				foreach($GLOBALS['B2C_PRODUCT'] as $key => $val) {
					if(($tf_store_id!="") && ($tf_store_id!=$key)) {
						continue;
					}
					echo "<tr class='texto' bgcolor='#EEEEEE'>";
					echo "<td><nobr>&nbsp;".$val['name']."&nbsp;</nobr></td>\n";
					echo "<td align='center'>&nbsp;".$key."&nbsp;</td>\n";
					echo "<td><nobr>&nbsp;".$val['provider']."&nbsp;<nobr></td>\n";
					echo "<td>&nbsp;".$val['validity']."&nbsp;</td>\n";
					echo "<td align='right'>&nbsp;".$val['price']."&nbsp;</td>\n";
					echo "<td align='right'>&nbsp;".$val['comiss']."%&nbsp;</td>\n";
					echo "<td align='right'>&nbsp;".$val['comiss_lan']."%&nbsp;</td>\n";
					echo "</tr>";
				}//end foreach
			?>
		</table>
<?php
//if(b_IsUsuarioReinaldo()) 
{ 

	$sql_monitor = "select \"vb2c_coServico\", max(\"vb2c_dataCadastroVenda\") as last_data, 
	EXTRACT ('epoch' FROM (CURRENT_TIMESTAMP - max(\"vb2c_dataCadastroVenda\"))) as delay_request,
	count(*) as n_pedidos,
	(max(\"vb2c_dataCadastroVenda\")) as last_data_notify, 
	EXTRACT ('epoch' FROM (CURRENT_TIMESTAMP - ( max(\"vb2c_dataCadastroVenda\")))) as delay_notify, 
	sum(case when vb2c_status = '1' then 1 else 0 end) as n_notify
from tb_vendas_b2c ip
group by \"vb2c_coServico\"
order by \"vb2c_coServico\"";

//echo "(R) ".str_replace("\n", "<br>\n", $sql_monitor)."<br>\n"; 

	echo "<a name='monitor_atividade'></a>\n";
	$rs = SQLexecuteQuery($sql_monitor);

	$n_rows = pg_num_rows($rs);
	if($n_rows == 0) {
		echo "<font color='red'>Nenhum registro de integração encontrado para monitor.</font>\n";
	} else {
		echo "<p class='texto'>Encontrados $n_rows registros no monitor</p>";
		echo "<table cellpadding='2' cellspacing='0' border='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
		echo "<tr class='texto'>";
			echo "<td>código</td>";
			echo "<td>nome</td>";
			echo "<td>delay_pedidos</td>";
			echo "<td>n_pedidos</td>";
			echo "<td>delay_notify</td>";
			echo "<td>n_notify</td>";
			echo "<td>% notify</td>";
		echo "</tr>\n";
		
		while($rs_row = pg_fetch_array($rs)){
            $b2cProdNome = (isset($GLOBALS['B2C_PRODUCT'][$rs_row['vb2c_coServico']]['name'])) ? $GLOBALS['B2C_PRODUCT'][$rs_row['vb2c_coServico']]['name'] : "";
			echo "<tr class='texto' style='background-color:#ccff99'>";
				echo "<td>".$rs_row['vb2c_coServico']."</td>";
				echo "<td align='right'>" . $b2cProdNome . "</td>";
				echo "<td align='right' title='".substr($rs_row['last_data'], 0, 19)."'>".convert_secs_to_string($rs_row['delay_request'])."</td>";
				echo "<td align='right'>".$rs_row['n_pedidos']."</td>";
				echo "<td align='right' title='".substr($rs_row['last_data_notify'], 0, 19)."'>".convert_secs_to_string($rs_row['delay_notify'])."</td>";
				echo "<td align='right'>".$rs_row['n_notify']."</td>";
				echo "<td align='right'>".number_format((100*$rs_row['n_notify']/$rs_row['n_pedidos']), 2, '.', '.')."% </td>";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
}
?>
	</td>
  </tr>
</table>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</body></html>
<?php 
	function convert_secs_to_string($n) {
		$sout = "";
		$ndays = 0;
		$nhours = 0;
		$nmins = 0;
		$nsecs = 0;

		$ndays = intval($n/(60*60*24));
		$nhours = str_pad(intval(($n-$ndays*60*60*24)/(60*60)), 2, "0", STR_PAD_LEFT);
		$nmins = str_pad(intval(($n-$ndays*60*60*24-$nhours*60*60)/(60)), 2, "0", STR_PAD_LEFT);
		$nsecs = str_pad(intval(($n-$ndays*60*60*24-$nhours*60*60-$nmins*60)), 2, "0", STR_PAD_LEFT);
		
		
		$sout .= "<font size='1'>";
		$sout .= (($ndays>0)?$ndays."<font color='#FF0000'>d</font>":"");
		$sout .= (($ndays>0 || $nhours>0)?$nhours."<font color='#FF0000'>h</font>":"");
		$sout .= (($ndays>0 || $nhours>0 || $nmins>0)?$nmins."<font color='#FF0000'>m</font>":"");
		$sout .= (($ndays>0 || $nhours>0 || $nmins>0 || $nsecs>0)?$nsecs."<font color='#FF0000'>s</font>":"");
		$sout .= "</font>";

		return $sout;
	}


?>
