<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";

$msg = "";

if ($msg == "") {

	$sql = "select * from operadoras order by opr_codigo desc";
	$rs_pins_opr = SQLexecuteQuery($sql);
}

ob_end_flush();
?>

<body>
	<div class="col-md-12">
		<ol class="breadcrumb top10">
			<li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice -
					<?php echo $currentAba->getDescricao(); ?></a></li>
			<li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
			<li class="active"><a
					href="<?php echo $sistema->item->getLink(); ?>"><?php echo $sistema->item->getDescricao(); ?></a>
			</li>
		</ol>
	</div>
	<table class="table txt-preto fontsize-pp">
		<tr>
			<td>
				<form name="form1" method="post" action="#">
					<input type="hidden" name="modelo_id" value="<?php echo $modelo_id ?>">
					<input type="hidden" name="ogpm_ogp_id" value="<?php echo $ogpm_ogp_id ?>">

					<table class="table txt-preto fontsize-pp">
						<tr bgcolor="#FFFFFF">
							<td colspan="2" bgcolor="#ECE9D8" class="texto">Tipo de listagem</font>
							</td>
						</tr>
						<tr bgcolor="#F5F5FB">
							<td width="150"><b>Tipo de listagem</b></td>
							<td>
								<select name="tipo_listagem" class="">
									<option value="O" <?php if ($tipo_listagem == "O" || !$tipo_listagem)
										echo "selected"; ?>>Valores na tabela 'operadoras'</option>
									<option value="P" <?php if ($tipo_listagem == "P")
										echo "selected"; ?>>Valores na
										tabela 'pins'</option>
								</select>
							</td>
						</tr>
					</table>
					<table class="table txt-preto fontsize-pp">
						<tr bgcolor="#F5F5FB">
							<td colspan="2" align="right"><input type="submit" name="BtnAtualizar" value="Atualizar"
									class="btn btn-info btn-sm"></td>
						</tr>
					</table>

				</form>
			</td>
		</tr>
	</table>
	<style>
		.scroll-wrapper {
			overflow-x: auto;
			width: 100%;
		}
		.ghost{
			height: 1px;
		}
	</style>
	<div class="scroll-wrapper" style="height: 25px;" id="scroll-top">
		<div class="ghost" id="ghost"></div>
	</div>
	<div class="scroll-wrapper" id="scroll-bottom">
		<?php
		if ($rs_pins_opr) {
			if ($tipo_listagem == "P") {
				// Lista por PINs (Dinâmica, Novo Modelo)
				?>
				<table class="table txt-preto fontsize-pp table-bordered" id="main-table">
					<thead>
						<tr align="center">
							<th><b>Operadora</b></th>
							<?php
							// Cabeçalho Dinâmico: Calcula o maior número de valores entre as operadoras
							$max_valores = 0;
							$temp_data = [];

							pg_result_seek($rs_pins_opr, 0); // Volta ao início dos resultados
							while ($rs_pins_opr_row = pg_fetch_array($rs_pins_opr)) {
								$sql = "SELECT DISTINCT pin_valor FROM pins 
                            					WHERE opr_codigo = " . intval($rs_pins_opr_row["opr_codigo"]) . " 
                            						AND pin_canal = 's' 
                            					ORDER BY pin_valor";
								$rs_pins = SQLexecuteQuery($sql);

								$valores = [];
								if ($rs_pins) {
									while ($rs_pins_row = pg_fetch_array($rs_pins)) {
										$valores[] = $rs_pins_row['pin_valor'];
									}
								}

								$temp_data[] = [
									'operadora' => $rs_pins_opr_row,
									'valores' => $valores
								];

								if (count($valores) > $max_valores) {
									$max_valores = count($valores);
								}
							}

							// Monta cabeçalho com base no máximo de valores encontrados
							for ($i = 1; $i <= $max_valores; $i++) {
								echo "<th>valor$i</th>\n";
							}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
						// Imprime os dados armazenados
						foreach ($temp_data as $data) {
							$opr = $data['operadora'];
							$valores = $data['valores'];

							echo "<tr>\n";
							echo "<td align='right'><nobr><b>" . $opr["opr_nome"] . "&nbsp;(" . $opr["opr_codigo"] . ")</b></nobr></td>\n";

							foreach ($valores as $valor) {
								echo "<td align='right'>" . number_format($valor, 2, ',', '.') . "</td>\n";
							}

							// Preenche colunas vazias para alinhar a tabela
							$faltam = $max_valores - count($valores);
							for ($i = 0; $i < $faltam; $i++) {
								echo "<td align='right'>-</td>\n";
							}

							echo "</tr>\n";
						}
						?>
					</tbody>
				</table>
				<?php
			} else {
				// Lista por operadoras
				?>
				<table class="table txt-preto fontsize-pp table-bordered" id="main-table">
					<thead>
						<tr align="center">
							<th>Operadora</th>
							<?php
							// Cabeçalho dinâmico: encontra o maior número de valores entre as operadoras
							$max_valores = 0;
							$temp_data = [];

							// Primeiro, varremos as operadoras para descobrir o máximo de valores e guardar os dados
							pg_result_seek($rs_pins_opr, 0); // Volta ao início do resultado
							while ($rs_pins_opr_row = pg_fetch_array($rs_pins_opr)) {
								$sql_valores = "SELECT valor FROM operadoras_valores 
                                    WHERE opr_codigo = " . intval($rs_pins_opr_row["opr_codigo"]) . " 
                                    ORDER BY valor";
								$rs_valores = SQLexecuteQuery($sql_valores);

								$valores = [];
								if ($rs_valores) {
									while ($row = pg_fetch_array($rs_valores)) {
										$valores[] = $row['valor'];
									}
								}

								$temp_data[] = [
									'operadora' => $rs_pins_opr_row,
									'valores' => $valores
								];

								if (count($valores) > $max_valores) {
									$max_valores = count($valores);
								}
							}

							// Monta o cabeçalho com base no maior número de valores encontrados
							for ($i = 1; $i <= $max_valores; $i++) {
								echo "<th>valor$i</th>\n";
							}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
						// Agora imprime os dados armazenados
						foreach ($temp_data as $data) {
							$opr = $data['operadora'];
							$valores = $data['valores'];

							echo "<tr>\n";
							echo "<td align='right'><nobr><b>" . $opr["opr_nome"] . "&nbsp;(" . $opr["opr_codigo"] . ")</b></nobr></td>\n";

							foreach ($valores as $valor) {
								$style = (((int) $valor != $valor) ? " style='background-color:#FFFF99;color:red'" : "");
								echo "<td align='right'$style>";
								if ($valor > 0) {
									echo number_format($valor, 2, ',', '.');
								} else {
									echo "-";
								}
								echo "</td>\n";
							}

							// Preenche células vazias para alinhar corretamente
							$faltam = $max_valores - count($valores);
							for ($i = 0; $i < $faltam; $i++) {
								echo "<td align='right'>-</td>\n";
							}

							echo "</tr>\n";
						}
						?>
					</tbody>
				</table>
				<?php
			}

		}
		?>
	</div>
	<?php
	require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
	?>

	<script type="text/javascript">
		const table = document.getElementById('main-table');
		const ghost = document.getElementById('ghost');
		const scrollTop = document.getElementById('scroll-top');
		const scrollBottom = document.getElementById('scroll-bottom');

		// Ajusta a largura da ghost div para igualar a da tabela
		ghost.style.width = table.scrollWidth + 'px';

		// Sincroniza as rolagens
		scrollTop.addEventListener('scroll', () => {
			scrollBottom.scrollLeft = scrollTop.scrollLeft;
		});
		scrollBottom.addEventListener('scroll', () => {
			scrollTop.scrollLeft = scrollBottom.scrollLeft;
		});
	</script>

	</html>