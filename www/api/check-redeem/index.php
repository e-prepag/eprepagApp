<?php /*
*/ header("Content-Type: text/html; charset=ISO-8859-1", true);/*


*/
define('ACCESS_ALLOWED', true);

$inicio_timer = microtime(true);

require_once "../../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "includes/functionsCheckRedeem.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "class/classControleIP.php";

/**
 * Validates the OAuth Token by calling the Go API
 * This ensures token rotation and blacklist checks are performed.
 */
function validateOAuthToken($token) {
    $apiUrl = 'https://auth-hml.eprepag.com.br/api/secure/profile';
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    
    // In production, ensure SSL verification is appropriate for your environment
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 200 means token is valid, active, and not blacklisted
    return ($httpCode === 200);
}

// START OAUTH SECURE: Token validation check
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    header('HTTP/1.1 401 Unauthorized');
    exit('Unauthorized: Bearer token missing');
}

$token = $matches[1];

// This is where you call your OAuth server or validate the JWT
if (!validateOAuthToken($token)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized: Invalid or expired token');
}
// END OAUTH SECURE

gravaLog_IntegracaoPIN("IP Tentativa: " . retorna_ip_acesso() . PHP_EOL . print_r($_POST, true));

$dataAtual = date('Y-m-d H:i:s');
$informacoesPOST = $_POST;
$ipReq = $_SERVER['HTTP_X_FORWARDED_FOR'] ?: $_SERVER['REMOTE_ADDR'];
$infoAdicional = json_encode($_SERVER, JSON_UNESCAPED_UNICODE);

if (!empty($_POST)) {
	// Se $_POST existe e n�o est� vazio
	$informacoesReq = "Via POST: " . json_encode($_POST, JSON_UNESCAPED_UNICODE);
} else {
	// Se $_POST est� vazio ou n�o est� definido

	if (!empty($_GET)) {
		$informacoesReq = "Via GET: " . json_encode($_GET, JSON_UNESCAPED_UNICODE);
	} else if (!empty($_COOKIE))
		$informacoesReq = "Via COOKIE: " . json_encode($_COOKIE, JSON_UNESCAPED_UNICODE);
	else {
		$informacoesReq = 'Sem informa��es de requisi��o POST, GET ou COOKIE';
	}
}


$mensagemLog = '****#### IN�CIO ####****' . PHP_EOL .
	'Data e Hora: ' . $dataAtual . PHP_EOL .
	'Informa��es de REQUEST: ' . $informacoesReq . PHP_EOL .
	'IP de Acesso: ' . $ipReq . PHP_EOL .
	'Informa��es Adicionais do Servidor: ' . $infoAdicional . PHP_EOL .
	'****#### FIM ####****' . PHP_EOL . PHP_EOL;

$fileLog = "/www/arquivos_gerados/logs/logCheckRedeemALL.txt";
$file = fopen($fileLog, 'a+');
if ($file) {
	fwrite($file, $mensagemLog);
	fclose($file);
} else {
	echo "Erro ao abrir o arquivo de log.";
}


//For�ando todos os parametros em minusculo
$_POST = array_change_key_case($_POST, CASE_LOWER);
$id 		= isset($_POST["id"])			? $_POST["id"]			: null;

//Variavel que coloca o sistema em OFF LINE qdo FALSE
$auxOnLineTop = true;

//comentar e deixar soimente o POST
//	descomentar $id 		= isset($_REQUEST["id"])		? $_REQUEST["id"]			: null;

$params		= array(
	'id'		=> array(
		'0' => $id,
		'1' => 'I',
		'2' => '1'
	),
);
$params		= sanitize_input_data_array($params, $err_cod);
extract($params, EXTR_OVERWRITE);

$aux_codreteppTOP = '0';
$aux_pin_valueTOP = null;

if ($auxOnLineTop) {
	$sql_opr = "SELECT opr_product_type from operadoras where opr_codigo=$1";
	$rs_oper = SQLexecuteQueryParams($sql_opr, [$id]);
	if ($rs_oper) {
		if ($rs_oper_row = pg_fetch_array($rs_oper)) {
			switch ($rs_oper_row['opr_product_type']) {
				case '1':
					include "epp_verify.php";
					break;
				case '2':
					include "epp_cash.php";
					break;
				case '3':
					include "epp_verify.php";
					if ($aux_codreteppTOP <> $notify_list_values['SV'] && $aux_codreteppTOP <> $notify_list_values['SU'] && $aux_codreteppTOP <> $notify_list_values['VD'] && $aux_codreteppTOP <> $notify_list_values['PU'] && $aux_codreteppTOP <> $notify_list_values['SD'] && $aux_codreteppTOP <> $notify_list_values['EU']) {
						include "epp_cash.php";
					}
					break;
				case '4':
					include "epp_go_cash.php";
					break;
				case '5':
					include "epp_go_cash_real_value.php";
					break;
				case '6':
					$pin_code 	= isset($_POST["pin_code"])	? $_POST["pin_code"]	: null;
					$pin_code	= filter_var($pin_code, FILTER_SANITIZE_NUMBER_INT);
					if (RetonaTamanhoPINEPPCARD_SINGLEPAGE($pin_code)) {
						include "epp_card.php";
					} else {
						include "epp_verify.php";
					}
					break;
				case '7':
					require_once $raiz_do_projeto . "banco/gocash/config.inc.php";
					$pin_code 	= isset($_POST["pin_code"])	? $_POST["pin_code"]	: null;
					$pin_code	= filter_var($pin_code, FILTER_SANITIZE_NUMBER_INT);
					if (RetonaTamanhoPINGoCASH($pin_code)) {
						include "epp_go_cash.php";
					} else {
						include "epp_verify.php";
					}
					break;
				case '8':
					require_once $raiz_do_projeto . "banco/gocash/config.inc.php";
					$pin_code 	= isset($_POST["pin_code"])	? $_POST["pin_code"]	: null;
					$pin_code	= filter_var($pin_code, FILTER_SANITIZE_NUMBER_INT);
					if (RetonaTamanhoPINEPPCARD_SINGLEPAGE($pin_code)) {
						include "epp_card.php";
					} elseif (RetonaTamanhoPINGoCASH($pin_code)) {
						include "epp_go_cash.php";
					} else {
						include "epp_verify.php";
					}
					break;
				default:
					$aux_codreteppTOP = $notify_list_values['PO'];
					break;
			}
		} else $aux_codreteppTOP = $notify_list_values['EG'];
	} else $aux_codreteppTOP = $notify_list_values['EG'];
} else $aux_codreteppTOP = $notify_list_values['OL'];

if ($aux_codreteppTOP == '0') {
	$aux_codreteppTOP = $notify_list_values['EG'];
}



if ($action == '2' && (($id * 1) == 124 || ($id * 1) == 137)) {

	if (converte_detalhe_codretepp($aux_codreteppTOP) == 2 || converte_detalhe_codretepp($aux_codreteppTOP) == 1 || converte_detalhe_codretepp($aux_codreteppTOP) == 5 || converte_detalhe_codretepp($aux_codreteppTOP) == 6) {
		$sql_id = "SELECT pin_codinterno from pins where pin_codigo =$1";
		$rs_id = SQLexecuteQueryParams($sql_id, [$pin_code]);
		$rsiD = pg_fetch_array($rs_id);

		$sql_venda_user = "SELECT * from tb_venda_games inner join tb_venda_games_modelo on vg_id= vgm_vg_id inner join tb_venda_games_modelo_pins on vgm_id = vgmp_vgm_id where vgmp_pin_codinterno =$1";
		$rs_venda_user = SQLexecuteQueryParams($sql_venda_user, [$rsiD["pin_codinterno"]]);
		$rs_user = pg_fetch_array($rs_venda_user);

		if (pg_num_rows($rs_venda_user) == 0 || $rs_venda_user == false) {

			$sql_venda_pdv = "SELECT * from tb_dist_venda_games inner join tb_dist_venda_games_modelo on vg_id= vgm_vg_id inner join tb_dist_venda_games_modelo_pins on vgm_id = vgmp_vgm_id where vgmp_pin_codinterno =$1";
			$rs_venda_pdv = SQLexecuteQueryParams($sql_venda_pdv, [$rsiD["pin_codinterno"]]);
			$rs_pdv = pg_fetch_array($rs_venda_pdv);

			if (pg_num_rows($rs_venda_pdv) > 0) {
				echo "CODRETEPP=" . converte_detalhe_codretepp($aux_codreteppTOP) . ";CODCHANNEL=1";
				if (!is_null($aux_pin_valueTOP)) {
					$pinValueFormatted = number_format($aux_pin_valueTOP * 100, 0, '', '');
					echo ";PIN_VALUE=" . $pinValueFormatted;
				}
			}
		} else {

			if ($rs_user["vg_pagto_tipo"] == 2 || $rs_user["vg_pagto_tipo"] == 24) {
				echo "CODRETEPP=" . converte_detalhe_codretepp($aux_codreteppTOP) . ";CODCHANNEL=0";
				if (!is_null($aux_pin_valueTOP)) {
					$pinValueFormatted = number_format($aux_pin_valueTOP * 100, 0, '', '');
					echo ";PIN_VALUE=" . $pinValueFormatted;
				}
			} elseif ($rs_user["vg_pagto_tipo"] == 13) {

				$sql_venda_pincash = "SELECT * from pins_store_pag_epp_pin where tpc_idvenda =$1";
				$rs_venda_pin_cash = SQLexecuteQueryParams($sql_venda_pincash, [$rs_user["vg_id"]]);
				$pdv = false;
				while ($row_rs_cash = pg_fetch_array($rs_venda_pin_cash)) {
					if ($row_rs_cash["pspep_canal"] == 'L') {
						$pdv = true;
					}
				}

				if ($pdv == true) {
					echo "CODRETEPP=" . converte_detalhe_codretepp($aux_codreteppTOP) . ";CODCHANNEL=1";
				} else {
					echo "CODRETEPP=" . converte_detalhe_codretepp($aux_codreteppTOP) . ";CODCHANNEL=0";
				}
				if (!is_null($aux_pin_valueTOP)) {
					$pinValueFormatted = number_format($aux_pin_valueTOP * 100, 0, '', '');
					echo ";PIN_VALUE=" . $pinValueFormatted;
				}
			}
		}
	} else {

		echo "CODRETEPP=" . converte_detalhe_codretepp($aux_codreteppTOP);
		if (!is_null($aux_pin_valueTOP)) {
			$pinValueFormatted = number_format($aux_pin_valueTOP * 100, 0, '', '');
			echo ";PIN_VALUE=" . $pinValueFormatted;
		}
	}
} else {

	echo "CODRETEPP=" . converte_detalhe_codretepp($aux_codreteppTOP);
	if (!is_null($aux_pin_valueTOP)) {
		$pinValueFormatted = number_format($aux_pin_valueTOP * 100, 0, '', '');
		echo ";PIN_VALUE=" . $pinValueFormatted;
	}
}

/* echo "CODRETEPP=".converte_detalhe_codretepp($aux_codreteppTOP);
	if (!is_null($aux_pin_valueTOP)) {
		echo ";PIN_VALUE=".$aux_pin_valueTOP."00";
	} */

//Fechando Conex�o
pg_close($connid);
