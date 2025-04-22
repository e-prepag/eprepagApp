<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

	$msg = "";

	//Recupera operdoras
	if($msg == ""){
		$sql = "select opr_nome, opr_codigo from operadoras order by opr_nome";
//echo $sql."<br>".
		$rs_operadoras = SQLexecuteQuery($sql);
	}


	//Recupera valores de pins
	if($msg == ""){
//		if($opr_codigo && is_numeric($opr_codigo)) {
			$sql  = "select distinct pin_valor from pins where opr_codigo = " . $opr_codigo . " and pin_canal='s' order by pin_valor";
//echo $sql."<br>".
//			$rs_pins = SQLexecuteQuery($sql);

			$sql  = "select opr_valor1, opr_valor2, opr_valor3, opr_valor4, opr_valor5, opr_valor6, opr_valor7, opr_valor8, opr_valor9, opr_valor10, opr_valor11, opr_valor12, opr_valor13, opr_valor14, opr_valor15 from operadoras where opr_codigo = " . $opr_codigo . "";
			$sql  = "select * from operadoras order by opr_codigo desc";
//echo $sql."<br>".
			$rs_pins_opr = SQLexecuteQuery($sql);

//		}
	}
	


ob_end_flush();
?>
<body>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td>
	<form name="form1" method="post" action="com_modelo_detalhe_testa_pin_valor.php">
		<input type="hidden" name="modelo_id" value="<?php echo $modelo_id ?>">
		<input type="hidden" name="ogpm_ogp_id" value="<?php echo $ogpm_ogp_id ?>">

        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8" class="texto">Tipo de listagem</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Tipo de listagem</b></td>
            <td>
				<select name="tipo_listagem" class="">
					<option value="O" <?php if ($tipo_listagem == "O" || !$tipo_listagem) echo "selected";?>>Valores na tabela 'operadoras'</option>
					<option value="P" <?php if ($tipo_listagem == "P") echo "selected";?>>Valores na tabela 'pins'</option>
				</select>
			</td>
		  </tr>
<?php
if(false) {
?>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8" class="texto">Operadora</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Operadora</b></td>
            <td>
				<select name="opr_codigo" class="form2">
					<option value="">Selecione</option>
					<?php if($rs_operadoras) while($rs_operadoras_row = pg_fetch_array($rs_operadoras)){ ?>
					<option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>" <?php if ($opr_codigo == $rs_operadoras_row['opr_codigo']) echo "selected";?>><?php echo $rs_operadoras_row['opr_codigo'] . " - " .$rs_operadoras_row['opr_nome'] ; ?></option>
					<?php } ?>
				</select>
			</td>
		  </tr>
		</table>

        <table width="894" border="0" cellpadding="0" cellspacing="1" class="texto">
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>PIN da Operadora (PINs)</b></td>
            <td>
				<select name="pin_valor" class="form2">
					<option value="">Selecione</option>
					<?php if($rs_pins) while($rs_pins_row = pg_fetch_array($rs_pins)){ ?>
					<option value="<?php echo $rs_pins_row['pin_valor']; ?>" <?php if ($pin_valor == $rs_pins_row['pin_valor']) echo "selected";?>><?php echo number_format($rs_pins_row['pin_valor'],2,',','.'); ?></option>
					<?php } ?>
				</select>
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>PIN da Operadora (Operadoras)</b></td>
            <td>
				<select name="pin_valor_opr" class="form2">
					<option value="">Selecione</option>
					<?php 
						if($rs_pins_opr) {
							$rs_pins_opr_row = pg_fetch_array($rs_pins_opr); 
							for($i=1;$i<=15;$i++) {
								if($rs_pins_opr_row["opr_valor$i"]>0) {
					?>
									<option value="<?php echo $rs_pins_opr_row["opr_valor$i"]; ?>" <?php if ($pin_valor_opr == $rs_pins_opr_row["opr_valor$i"]) echo "selected";?>><?php echo "opr_valor$i - ".number_format($rs_pins_opr_row["opr_valor$i"],2,',','.'); ?></option>
					<?php 
								}
								if($i>15) {
									echo "Ultrapassou limite<br>";
									break;
								}
							}
						} 
					?>
				</select>
			</td>
		  </tr>
<?php
}
?>
		</table>
        <table class="table txt-preto fontsize-pp">
		  <tr bgcolor="#F5F5FB">
			<td colspan="2" align="right"><input type="submit" name="BtnAtualizar" value="Atualizar" class="btn btn-info btn-sm"></td>
		  </tr>
		</table>

	</form>
    </td></tr></table>
    <table class="table txt-preto fontsize-pp"><tr><td>
		<?php 
			if($rs_pins_opr) {
				if($tipo_listagem == "P") {
					// Lista por PINs

		?>
		<table class="table txt-preto fontsize-pp table-bordered">
          <tr align="center"> 
            <td><b>Operadora</b></td>
		<?php 
				for($i=1;$i<=15;$i++) {
					echo "<td>valor$i</td>\n";
				}
				echo "<td>valor11min</td>\n";
				echo "<td>valor11max</td>\n";
		?>
		  </tr>
		<?php 
					while($rs_pins_opr_row = pg_fetch_array($rs_pins_opr)) {

						echo "<tr>\n";
						echo "<td align='right'><nobr><b>".$rs_pins_opr_row["opr_nome"]."&nbsp;(".$rs_pins_opr_row["opr_codigo"].")</b></nobr></td>\n";

						$sql  = "select distinct pin_valor from pins where opr_codigo = " . $rs_pins_opr_row["opr_codigo"] . " and pin_canal='s' order by pin_valor";
//echo $sql."<br>".
						$rs_pins = SQLexecuteQuery($sql);
			
						if($rs_pins) {
							$i_vals = 1;
							while($rs_pins_row = pg_fetch_array($rs_pins)) {
								$i_vals++;
								echo "<td align='right'>".number_format($rs_pins_row["pin_valor"],2,',','.')."</td>\n";
							}

							for($i=$i_vals;$i<=15;$i++) {
								echo "<td align='right'>-</td>\n";
							}
						}
						echo "<td align='right'>-</td>\n";
						echo "<td align='right'>-</td>\n";
						echo "</tr>\n";
					}

				} else {
				// Lista por operadoras
		?>
          <table class="table txt-preto fontsize-pp table-bordered">
          <tr align="center"> 
            <td><b>Operadora</b></td>
		<?php 
					for($i=1;$i<=15;$i++) {
						echo "<td>valor$i</td>\n";
					}
					echo "<td>valor11min</td>\n";
					echo "<td>valor11max</td>\n";
		?>
		  </tr>
		<?php 
					while($rs_pins_opr_row = pg_fetch_array($rs_pins_opr)) {

						echo "<tr>\n";
						echo "<td align='right'><nobr><b>".$rs_pins_opr_row["opr_nome"]."&nbsp;(".$rs_pins_opr_row["opr_codigo"].")</b></nobr></td>\n";

						for($i=1;$i<=15;$i++) {
							$style = ((((int)$rs_pins_opr_row["opr_valor$i"])!=$rs_pins_opr_row["opr_valor$i"])?" style='background-color:#FFFF99;color:red'":"");
							echo "<td align='right'$style>";
							if($rs_pins_opr_row["opr_valor$i"]>0) {
								echo number_format($rs_pins_opr_row["opr_valor$i"],2,',','.');
							} else {
								echo "-";
							}
							echo "</td>\n";
						}
						echo "<td align='right'>";
						if($rs_pins_opr_row["opr_valor11min"]>0) {
							echo number_format($rs_pins_opr_row["opr_valor11min"],2,',','.');
						} else {
							echo "-";
						}
						echo "</td>\n";
						echo "<td align='right'>";
						if($rs_pins_opr_row["opr_valor11max"]>0) {
							echo number_format($rs_pins_opr_row["opr_valor11max"],2,',','.');
						} else {
							echo "-";
						}
						echo "</td>\n";
						echo "</tr>\n";
					}
				}
			}
		?>
		</table>


    </td>
  </tr>
</table>
<?php
    require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
