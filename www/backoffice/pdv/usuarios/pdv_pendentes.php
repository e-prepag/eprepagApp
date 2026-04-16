<?php
session_start();
require_once '../../../includes/constantes.php';
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

if (isset($_POST["btn-envia"])) {

	function estruturaExcel($info)
	{
		$html = "<table border='1'>";
		$html .= "<tr><td style='text-align:center;color:#fff;background-color:#268fbd;font-size:24px;' colspan='8'><b>PDVs que n√£o finalizaram o cadastro</b></td></tr>";
		$html .= "<thead>";
		$html .= "<tr>";
		$html .= "<th colspan='4'><b>Username</b></th>";
		$html .= "<th colspan='4'><b>E-mail</b></th>";
		$html .= "</tr>";
		$html .= "</thead>";
		$html .= "<tbody>";
		for ($num = 0; $num < (is_countable($info) ? count($info) : 0); $num++) {
			$html .= "<tr>
					 <td style='text-align:center;' colspan='4'>" . $info[$num][1] . "</td>
					 <td style='text-align:center;' colspan='4'>" . $info[$num][3] . "</td>
				 </tr>";
		}
		$html .= "</tbody>";
		$html .= "</table>";
		$_SESSION["excelPdv"] = $html;
	}
	// Nova vers„o: busca direto do banco de dados ao invÈs de arquivos

	if (isset($_POST["dtMin"]) && isset($_POST["dtMax"])) {

		// As datas vÍm do POST no formato YYYY-MM-DD
		$dtMin = $_POST["dtMin"] ?? ''; // Ex: "2025-01-15"
		$dtMax = $_POST["dtMax"] ?? ''; // Ex: "2025-01-31"

		$sql = "SELECT DISTINCT 
        		    ug_id,
        		    ug_nome,
        		    ug_email,
        		    ug_data_cadastro,
        		    ug_ativo,
        		    ug_qtde_acessos
        		FROM dist_usuarios_games
        		WHERE ug_ativo = 2
        		  AND ug_qtde_acessos = 0
        		  AND ug_data_cadastro >= $1
        		  AND ug_data_cadastro <= $2
        		ORDER BY ug_data_cadastro";

		$resultado = SQLexecuteQueryParams($sql, [$dtMin, $dtMax]);

		$dados = [];

		// Monta o array com os dados retornados
		while ($linha = pg_fetch_assoc($resultado)) {
			$dados[] = [
				$linha['ug_id'],
				$linha['ug_nome'],
				$linha['ug_email'],
				$linha['ug_data_cadastro'],
				$linha['ug_ativo'],
				$linha['ug_qtde_acessos']
			];
		}

		// Se encontrou dados, gera o Excel
		if ((is_countable($dados) ? count($dados) : 0) > 0) {
			$emails = array_column($dados, 2); // Coluna do email (Ìndice 2)
			// echo '<script>console.log('.json_encode($emails).')</script>';
			estruturaExcel($dados);
		} else {
			echo "Nenhum registro encontrado no perÌodo selecionado.";
		}
	} else {
		echo "Erro: Datas n„o informadas.";
	}
}

?>
<style>
	#titulo {
		font-size: 1.6em;
		color: #000;
		margin-left: 8px;
	}

	.btn-enviar {
		padding: 10px 15px;
		color: #fff;
		border: none;
		border-radius: 5px;
		background-color: #198754;
	}

	input[type="date"] {
		width: 200px;
		font-size: 17px;
		margin: 8px;
	}

	#info {
		margin-top: 20px;
		margin-left: 8px;
		clear: both;
		width: calc(100% - 15px);
	}

	#info th {
		padding: 10px;
		text-align: center;
		background-color: #dddddd;
		color: #000;
	}

	#info td {
		color: #000;
		text-align: center;
		padding: 10px;
	}

	.btn-excel {
		margin-right: 7px;
		margin-bottom: 10px;
		display: block;
		float: right;
		width: fit-content;
	}

	.btn-enviar:hover {
		color: #dddddd;
	}
</style>
<form method="POST">
	<h2 id="titulo">Pesquisa de PDVs com cadastro pendente</h2>
	<input value="<?php echo isset($_POST["dtMin"]) ? $_POST["dtMin"] : ""; ?>" max="<?php echo date("Y-m-d"); ?>" name="dtMin" type="date" id="dtMin" required>
	<input value="<?php echo isset($_POST["dtMax"]) ? $_POST["dtMax"] : ""; ?>" max="<?php echo date("Y-m-d"); ?>" name="dtMax" type="date" id="dtMax" required>
	<input type="submit" name="btn-envia" value="Buscar" class="btn-enviar" id="btn-enviar">
</form>
<?php
if (isset($_POST["btn-envia"])) {
	if (isset($dados)) {
?>
		<a href="excelPdv.php" class="btn-enviar btn-excel">Excel</a>
	<?php  }  ?>
	<table id="info" border="1">
		<thead>
			<tr>
				<th>Username</th>
				<th>E-mail</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (isset($dados) && (is_countable($dados) ? count($dados) : 0) > 0) {
				for ($num = 0; $num < (is_countable($dados) ? count($dados) : 0); $num++) {
			?>
					<tr>
						<td><?php echo $dados[$num][1]; ?></td>
						<td><?php echo $dados[$num][3]; ?></td>
					</tr>
				<?php
				}
			} else {
				?>
				<tr>
					<td colspan="2">Nenhum registro encontrado</td>
				</tr>
			<?php
			}
			?>
		</tbody>
	</table>
<?php
}
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>