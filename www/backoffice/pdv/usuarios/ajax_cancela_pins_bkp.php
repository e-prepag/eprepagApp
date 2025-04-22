<?php

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

	if (isset($_GET["id_pedido"]) && $_GET["id_pedido"] != "") {

		$data = ["data" => []];
		$sql = "select p.pin_codinterno, p.pin_valor, p.pin_codigo , o.opr_nome , vg.vg_data_inclusao , ps.stat_descricao, ug.ug_login, p.pin_status from tb_dist_venda_games vg
				join tb_dist_venda_games_modelo vm on vm.vgm_vg_id = vg.vg_id 
				join tb_dist_venda_games_modelo_pins vp on vp.vgmp_vgm_id = vm.vgm_id
				join pins p on p.pin_codinterno = vp.vgmp_pin_codinterno
				join pins_status ps on p.pin_status = ps.stat_codigo
				join operadoras o on p.opr_codigo = o.opr_codigo 
				join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id 
				where vg.vg_id = :VG_ID 
				order by vg.vg_data_inclusao desc;";
		$selectRows = $conexao->prepare($sql);
		$selectRows->bindValue(":VG_ID", $_GET["id_pedido"]);
		$selectRows->execute();
		$resultRows = $selectRows->fetchAll(PDO::FETCH_ASSOC);

	} else {
		$dt_inicial = isset($_GET["dt_inicial"]) ? str_replace('T', ' ', $_GET["dt_inicial"]) . ":00" : null;
		$dt_final = isset($_GET["dt_final"]) ? str_replace('T', ' ', $_GET["dt_final"]) . ":59" : null;
		$data = ["data" => []];

		$sql = "SELECT p.pin_codinterno, p.pin_valor, p.pin_codigo, o.opr_nome, vg.vg_data_inclusao, ps.stat_descricao, ug.ug_login, p.pin_status
        FROM tb_dist_venda_games vg
        JOIN tb_dist_venda_games_modelo vm ON vm.vgm_vg_id = vg.vg_id 
        JOIN tb_dist_venda_games_modelo_pins vp ON vp.vgmp_vgm_id = vm.vgm_id
        JOIN pins p ON p.pin_codinterno = vp.vgmp_pin_codinterno
        JOIN pins_status ps ON p.pin_status = ps.stat_codigo
        JOIN operadoras o ON p.opr_codigo = o.opr_codigo 
        JOIN dist_usuarios_games ug ON ug.ug_id = vg.vg_ug_id 
        WHERE vg.vg_ug_id = :UG_ID";

		// Adiciona as condições de data apenas se as variáveis não estiverem vazias
		if ($dt_inicial) {
			$sql .= " AND vg.vg_data_inclusao >= :DT_INICIAL";
		}
		if ($dt_final) {
			$sql .= " AND vg.vg_data_inclusao <= :DT_FINAL";
		}

		$sql .= " ORDER BY vg.vg_data_inclusao DESC;";

		$selectRows = $conexao->prepare($sql);

		if ($dt_inicial) {
			$selectRows->bindValue(":DT_INICIAL", $dt_inicial);
		}
		if ($dt_final) {
			$selectRows->bindValue(":DT_FINAL", $dt_final);
		}

		$selectRows->bindValue(":UG_ID", $_GET["id_pdv"]);
		$selectRows->execute();
		$resultRows = $selectRows->fetchAll(PDO::FETCH_ASSOC);

	}
	if (count($resultRows) > 0) {
		foreach ($resultRows as $key => $value) {
			$dataKeys = array_keys($value);
			$acao = '';
			if ($value["pin_status"] != 8 && $value["pin_status"] != 9) {
				$acao = '<button class="btn btn-danger btn-negar right10 bottom10" data-codigo="' . $value["pin_codinterno"] . '" data-idpdv="' . $_GET["id_pdv"] . '" data-atual="' . $_GET["reload"] . '">Cancelar</button>';
			}

			$dataLine = [
				$dataKeys[0] => $value["pin_codinterno"],
				$dataKeys[1] => number_format($value["pin_valor"], 2, ',', ''),
				$dataKeys[2] => $value["pin_codigo"],
				$dataKeys[3] => $value["opr_nome"],
				$dataKeys[4] => DateTime::createFromFormat('Y-m-d H:i:s.u', $value["vg_data_inclusao"])->format('Y-m-d H:i'),
				$dataKeys[5] => utf8_encode($value["stat_descricao"]),
				$dataKeys[6] => $value["ug_login"],
				"acoes" => $acao
			];
			array_push($data["data"], $dataLine);
		}

		echo json_encode($data);
		die;
	}

} else if (isset($_POST["acao"]) && $_POST["acao"] == "todos" && $_POST["dt_inicial"] && $_POST["dt_final"] && $_POST["id_pdv"]) {
	$dt_final = $_POST["dt_final"];
	$queryRow = "UPDATE pins p
				SET pin_status = '9'
				FROM tb_dist_venda_games_modelo_pins vp
				JOIN tb_dist_venda_games_modelo vm ON vp.vgmp_vgm_id = vm.vgm_id
				JOIN tb_dist_venda_games vg ON vm.vgm_vg_id = vg.vg_id
				WHERE p.pin_codinterno = vp.vgmp_pin_codinterno
				  AND p.pin_status <> '8'
				  AND vg.vg_ug_id = :UG_ID
				  AND vg.vg_data_inclusao > :DT_INICIAL
				  AND vg.vg_data_inclusao < :DT_FINAL;";
	$selectRow = $conexao->prepare($queryRow);
	$selectRow->bindValue(":DT_INICIAL", $_POST["dt_inicial"]);
	$selectRow->bindValue(":DT_FINAL", date('Y-m-d 23:59:59', strtotime($dt_final)));
	$selectRow->bindValue(":UG_ID", $_POST["id_pdv"]);
	$selectRow->execute();
	if ($selectRow->rowCount() > 0) {
		$queryRow = "select shn_mail from usuarios where shn_login = :LOGIN;";
		$selectRow = $conexao->prepare($queryRow);
		$selectRow->bindValue(":LOGIN", $_POST["login"]);
		$selectRow->execute();
		$resultRow = $selectRow->fetch(PDO::FETCH_ASSOC);

		$tipoAcao = "Cancelar todos os pins do pdv";

		$queryRow = "select ug_login from dist_usuarios_games where ug_id = :UG_ID;";
		$selectRow = $conexao->prepare($queryRow);
		$selectRow->bindValue(":UG_ID", $_POST["id_pdv"]);
		$selectRow->execute();

		$queryRow = "update dist_usuarios_games set ug_ativo = 0 where ug_id = :UG_ID;";
		$selectRow2 = $conexao->prepare($queryRow);
		$selectRow2->bindValue(":UG_ID", $_POST["id_pdv"]);
		$selectRow2->execute();

		$data = $selectRow->fetch(PDO::FETCH_ASSOC);
		if (!$resultRow["shn_mail"]) {
			echo "Ação realizada com sucesso";
			exit;
		}
		$to = strtolower($resultRow["shn_mail"]);
		$cc = "";
		$bcc = "";
		$subject = utf8_decode("E-prepag - Solicitação de cancelamento de pins");
		$legendaAcao = "Aprovada";
		$html = file_get_contents("./template.html");
		$html = str_replace(["{data-atual}", "{tipo}", "{nome}", "{data}", "{operador}", "{resposta}"], [date("d-m-Y H:i:s"), $tipoAcao, $data["ug_login"], date("d-m-Y H:i:s"), $_POST["nome"], $legendaAcao], $html);
		$msg = $html;
		enviaEmail3($to, $cc, $bcc, $subject, $msg, "");

		echo "Ação realizada com sucesso";
		exit;

	} else {
		echo "Erro ao realizar a ação";
	}
} else if (isset($_POST["acao"]) && $_POST["acao"] == "unico" && $_POST["pin"]) {

	$queryRow = "UPDATE pins p
				SET pin_status = '9'
				WHERE pin_codinterno = :CODIGO;";
	$selectRow = $conexao->prepare($queryRow);
	$selectRow->bindValue(":CODIGO", $_POST["pin"]);
	$selectRow->execute();
	if ($selectRow->rowCount() > 0) {
		$queryRow = "select shn_mail from usuarios where shn_login = :LOGIN;";
		$selectRow = $conexao->prepare($queryRow);
		$selectRow->bindValue(":LOGIN", $_POST["login"]);
		$selectRow->execute();
		$resultRow = $selectRow->fetch(PDO::FETCH_ASSOC);

		$tipoAcao = "Cancelar pin id: " . $_POST["pin"];

		$queryRow = "select ug_login from dist_usuarios_games where ug_id = :UG_ID;";
		$selectRow2 = $conexao->prepare($queryRow);
		$selectRow2->bindValue(":UG_ID", $_POST["idpdv"]);
		$selectRow2->execute();
		$data = $selectRow2->fetch(PDO::FETCH_ASSOC);
		if (!$resultRow["shn_mail"]) {
			echo "Ação realizada com sucesso";
			exit;
		}
		$to = strtolower($resultRow["shn_mail"]);
		$cc = "";
		$bcc = "";
		$subject = utf8_decode("E-prepag - Solicitação de cancelamento de pin");
		$legendaAcao = "Aprovada";
		$html = file_get_contents("./template.html");
		$html = str_replace(["{data-atual}", "{tipo}", "{nome}", "{data}", "{operador}", "{resposta}"], [date("d-m-Y H:i:s"), $tipoAcao, $data["ug_login"], date("d-m-Y H:i:s"), $_POST["nome"], $legendaAcao], $html);
		$msg = $html;
		enviaEmail3($to, $cc, $bcc, $subject, $msg, "");

		echo "Ação realizada com sucesso";
		exit;

	} else {
		echo "Erro ao realizar a ação";
	}
} else {
	echo "Não foi possivel efetuar sua escolha";
}
die;
?>