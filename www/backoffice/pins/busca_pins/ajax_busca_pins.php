<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
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

function converterParaUtf8($data) {
    if (is_array($data)) {
        return array_map('converterParaUtf8', $data);
    }
    
    if (is_string($data)) {
        // Detecta e converte automaticamente
        $encoding = mb_detect_encoding($data, ['ISO-8859-1', 'UTF-8', 'ASCII'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            return mb_convert_encoding($data, 'UTF-8', $encoding);
        }
    }
    
    return $data;
}
function processarListaTextarea($texto)
{
	if (empty($texto)) {
		return [];
	}

	// Remove espacos em branco extras no incio e fim
	$texto = trim($texto);

	// Substitui quebras de linha por vrgulas
	$texto = preg_replace('/\r\n|\r|\n/', ',', $texto);

	// Explode por vrgula
	$itens = explode(',', $texto);

	// Remove espaos em branco de cada item e filtra vazios
	$lista = array_filter(array_map('trim', $itens), function ($item) {
		return $item !== '';
	});

	// Reindexar array (opcional, para remover ndices vazios)
	return array_values($lista);
}

if (isset($_POST["acao"]) && $_POST["acao"] == "listar") {

	$data = ["data" => []];


	if (isset($_POST["pin_cod"]) && $_POST["pin_cod"] != "") {

		$valores = processarListaTextarea($_POST["pin_cod"]);

		if (empty($valores)) {
			echo "Nenhum valor fornecido";
			exit;
		}
		$placeholders = implode(',', array_fill(0, count($valores), '?'));

		$sql = "WITH pins_filtrados AS (
					    SELECT 
					        p.pin_codigo,
					        p.pin_valor,
					        p.pin_codinterno,
					        p.opr_codigo,
					        p.pin_status,
					        p.pin_dataentrada,
							p.pin_validade
					    FROM pins p
					    WHERE pin_codigo IN ($placeholders)
					),
					pins_epp_status AS (
					    SELECT 
					        psp.pins_pin_codinterno,
					        pst.pin_status
					    FROM pins_store pst
					    JOIN tb_pins_store_pins psp ON psp.pins_store_pin_codinterno = pst.pin_codinterno
					    WHERE psp.pins_pin_codinterno IN (
					        SELECT pin_codinterno 
					        FROM pins_filtrados 
					        WHERE opr_codigo IN (53, 49)
					    )
					),
					pins_int_status AS (
					    SELECT 
					    	pih.pih_pin_id,
					        max(pih.pih_data) as pih_data
					    FROM pins_integracao_historico pih
					    WHERE pih.pih_pin_id  IN (
					        SELECT pin_codinterno 
					        FROM pins_filtrados 
					        WHERE pin_status = '8'
					    ) and pih.pin_status = 8 and pih.pih_codretepp = '2'
					    group by pih.pih_pin_id 
					)
					SELECT 
					    distinct p.pin_codigo,
						p.pin_codinterno,
					    p.pin_valor,
					    CASE WHEN pih.pih_data IS NULL AND p.pin_status = '8' THEN 'Como utiliz., mas sem dados' ELSE ps.stat_descricao END AS stat_descricao,
						p.pin_validade,
					    CASE
					        WHEN pes.pin_status = -1 then 'Cancelado'
					        WHEN pes.pin_status = 1 then 'Disponivel'
					        WHEN pes.pin_status = 2 then 'Publicado'
					        WHEN pes.pin_status = 3 then 'Ativado'
					        WHEN pes.pin_status = 4 then 'Utilizado'
					        else 'Nao Epp'
					    	END AS pin_epp_status,
					    COALESCE(pih.pih_data::text, 'Sem data utiliz.') AS pin_data_uti,
					    o.opr_nome,
					    CASE
					        WHEN dug.ug_id IS NOT NULL THEN 'PDV'
					        WHEN ug.ug_id IS NOT NULL THEN 'GAMER'
					        ELSE 'Nao vendido'
					    END AS tipo_usuario,
					    COALESCE(dug.ug_nome_fantasia, ug.ug_nome, 'Sem usuario') AS nome_usuario,
					    COALESCE(dv.vg_id, v.vg_id, 0) AS venda_id,
					    coalesce(dv.vg_data_inclusao::text, v.vg_data_inclusao::text, '') as data_venda,
					    coalesce(dvm.vgm_nome_produto, vm.vgm_nome_produto, 'Nao vendido') as nome_produto
					FROM pins_filtrados p
					LEFT JOIN pins_epp_status pes ON pes.pins_pin_codinterno = p.pin_codinterno
					LEFT JOIN pins_int_status pih ON pih.pih_pin_id = p.pin_codinterno
					JOIN pins_status ps ON p.pin_status = ps.stat_codigo
					JOIN operadoras o ON p.opr_codigo = o.opr_codigo
					LEFT JOIN tb_dist_venda_games_modelo_pins dvp ON dvp.vgmp_pin_codinterno = p.pin_codinterno
					LEFT JOIN tb_dist_venda_games_modelo dvm ON dvm.vgm_id = dvp.vgmp_vgm_id
					LEFT JOIN tb_dist_venda_games dv ON dv.vg_id = dvm.vgm_vg_id
					LEFT JOIN dist_usuarios_games dug ON dug.ug_id = dv.vg_ug_id
					LEFT JOIN tb_venda_games_modelo_pins vp ON vp.vgmp_pin_codinterno = p.pin_codinterno
					LEFT JOIN tb_venda_games_modelo vm ON vm.vgm_id = vp.vgmp_vgm_id
					LEFT JOIN tb_venda_games v ON v.vg_id = vm.vgm_vg_id
					LEFT JOIN usuarios_games ug ON ug.ug_id = v.vg_ug_id;";

		$selectRows = $conexao->prepare($sql);
		$selectRows->execute($valores);
		$resultRows = $selectRows->fetchAll(PDO::FETCH_ASSOC);
	}
	if (count($resultRows) > 0) {
		$data["data"] = converterParaUtf8($resultRows);
	}
	echo json_encode($data);
	die;
} else {
	echo "Não foi possivel efetuar sua escolha";
}
die;
