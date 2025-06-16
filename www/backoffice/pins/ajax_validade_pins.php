<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/includes/configIP.php";
require_once "/www/class/phpmailer/class.smtp.php";
require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
$conexao = ConnectionPDO::getConnection()->getLink();

if (isset($_GET["acao"]) && $_GET["acao"] == "listar") {
	if (isset($_GET["tipo_pesquisa"])) {
		$tipo = $_GET["tipo_pesquisa"];
		if ($tipo == 0) {
			$sql = "SELECT p.pin_codinterno, p.pin_valor, p.pin_codigo, o.opr_nome, p.pin_validade, ps.stat_descricao, p.pin_vencimento FROM pins p
				JOIN pins_status ps ON p.pin_status = ps.stat_codigo
        		JOIN operadoras o ON p.opr_codigo = o.opr_codigo
				WHERE p.pin_codigo = :pin";
			$selectRows = $conexao->prepare($sql);
			$selectRows->bindValue(":pin", $_GET["campo_pesquisa"]);
		} else if ($tipo == 1) {
			$sql = "SELECT p.pin_codinterno, p.pin_valor, p.pin_codigo, o.opr_nome, p.pin_validade, ps.stat_descricao, p.pin_vencimento FROM pins p
				JOIN pins_status ps ON p.pin_status = ps.stat_codigo
        		JOIN operadoras o ON p.opr_codigo = o.opr_codigo
				WHERE p.pin_codinterno in (SELECT vp.vgmp_pin_codinterno FROM tb_dist_venda_games_modelo_pins vp
					JOIN tb_dist_venda_games_modelo vm ON vm.vgm_id = vp.vgmp_vgm_id
					WHERE vm.vgm_vg_id = :vg_id
				)";
			$selectRows = $conexao->prepare($sql);
			$selectRows->bindValue(":vg_id", $_GET["campo_pesquisa"]);
		} else if ($tipo == 2) {
			$sql = "SELECT p.pin_codinterno, p.pin_valor, p.pin_codigo, o.opr_nome, p.pin_validade, ps.stat_descricao, p.pin_vencimento FROM pins p
				JOIN pins_status ps ON p.pin_status = ps.stat_codigo
        		JOIN operadoras o ON p.opr_codigo = o.opr_codigo
				WHERE p.pin_codinterno in (SELECT vp.vgmp_pin_codinterno FROM tb_venda_games_modelo_pins vp
					JOIN tb_venda_games_modelo vm ON vm.vgm_id = vp.vgmp_vgm_id
					WHERE vm.vgm_vg_id = :vg_id
				)";
			$selectRows = $conexao->prepare($sql);
			$selectRows->bindValue(":vg_id", $_GET["campo_pesquisa"]);
		} else {
			echo "Tipo de pesquisa inválido.";
			die;
		}
		$selectRows->execute();
		$resultRows = $selectRows->fetchAll(PDO::FETCH_ASSOC);

		$data = ["data" => []];
		if (count($resultRows) > 0) {
			foreach ($resultRows as $key => $value) {
				$dataKeys = array_keys($value);
				$acao = "<button class='btn btn-info btn-negar' 
							style='border-width: 0px;border-radius: 1px;box-shadow: 1px 1px 5px rgb(0,0,0,0.5);font-weight: bold;'
							data-codigo='" . $value["pin_codinterno"] . "' data-atual='" . $_GET["reload"] . "'
						>
							Alterar
						</button>";

				$dataLine = [
					"acoes" => $acao,
					$dataKeys[0] => $value["pin_codinterno"],
					$dataKeys[1] => number_format($value["pin_valor"], 2, ',', ''),
					$dataKeys[2] => $value["pin_codigo"],
					$dataKeys[3] => $value["opr_nome"],
					$dataKeys[4] => $value["pin_validade"],
					$dataKeys[5] => utf8_encode($value["stat_descricao"]),
					$dataKeys[6] => ($value["pin_vencimento"] ? utf8_encode($value["pin_vencimento"]) : "Não alterada"),
				];
				array_push($data["data"], $dataLine);
			}
		}
		echo json_encode($data);
		die;
	}
	echo "Tipo de pesquisa inválido.";
	die;
} else if (isset($_POST["acao"]) && $_POST["acao"] == "todos" && $_POST["vg_id"] && $_POST["tipo"] && $_POST["nova_validade"]) {

	$nova_validade = $_POST["nova_validade"];
	if (!$nova_validade || !is_numeric($nova_validade) || $nova_validade <= 0) {
		echo "Nova validade não informada.";
		die;
	}

	$hoje = new DateTime();
	$hoje->modify("+{$nova_validade} days");
	$dataFinal = $hoje->format('Y-m-d');

	$conexao->beginTransaction();

	if ($_POST["tipo"] == "gamer") {
		$queryRow = "UPDATE pins p
			SET pin_validade = :validade, pin_vencimento = 'Alterada', pin_datavenda = NOW()
			WHERE p.pin_codinterno IN (SELECT vp.vgmp_pin_codinterno FROM tb_venda_games_modelo_pins vp
				JOIN tb_venda_games_modelo vm ON vm.vgm_id = vp.vgmp_vgm_id
				WHERE vm.vgm_vg_id = :VG_ID);";
		$selectRow = $conexao->prepare($queryRow);
		$selectRow->bindValue(":VG_ID", $_POST["vg_id"]);
		$selectRow->bindValue(":validade", $dataFinal);
		$selectRow->execute();

		if ($selectRow->rowCount() > 0) {

			$conexao->commit();
			echo "1";
		}
	} else if ($_POST["tipo"] == "pdv") {
		$queryRow = "UPDATE pins p
			SET pin_validade = :validade, pin_vencimento = 'Alterada', pin_datavenda = NOW()
			WHERE p.pin_codinterno IN (SELECT vp.vgmp_pin_codinterno FROM tb_dist_venda_games_modelo_pins vp
				JOIN tb_dist_venda_games_modelo vm ON vm.vgm_id = vp.vgmp_vgm_id
				WHERE vm.vgm_vg_id = :VG_ID);";
		$selectRow = $conexao->prepare($queryRow);
		$selectRow->bindValue(":VG_ID", $_POST["vg_id"]);
		$selectRow->bindValue(":validade", $dataFinal);
		$selectRow->execute();
		if ($selectRow->rowCount() > 0) {

			$conexao->commit();
			echo "1";
		}
	} else {
		echo "Tipo de venda inválido.";
		die;
	}
} else if (isset($_POST["acao"]) && $_POST["acao"] == "unico" && $_POST["pin"] && $_POST["nova_validade"]) {

	$nova_validade = $_POST["nova_validade"];
	if (!$nova_validade || !is_numeric($nova_validade) || $nova_validade <= 0) {
		echo "Nova validade não informada.";
		die;
	}

	$hoje = new DateTime();
	$hoje->modify("+{$nova_validade} days");
	$dataFinal = $hoje->format('Y-m-d');
	$conexao->beginTransaction();

	$queryRow = "UPDATE pins p
					SET pin_validade = :validade, pin_vencimento = 'Alterada', pin_datavenda = NOW()
					WHERE pin_codinterno = :CODIGO;";
	$selectRow = $conexao->prepare($queryRow);
	$selectRow->bindValue(":CODIGO", $_POST["pin"]);
	$selectRow->bindValue(":validade", $dataFinal);
	$selectRow->execute();
	if ($selectRow->rowCount() > 0) {

		$conexao->commit();
		echo "1";
	}
} else {
	echo "Não foi possivel efetuar sua escolha";
}
