<?php
//Fun��o que retorna o tamanho do GiftCard
function RetonaTamanhoPINEPPCARD_SINGLEPAGE(string $pin): bool
{
	$tamanho = strlen(trim($pin));
	if ($tamanho == $GLOBALS['PIN_CARD_TAMANHO']) {
		return true;
	} else {
		return false;
	}
} //end function RetonaTamanhoPINEPPCARD_SINGLEPAGE($pin)

//Funo que Grava LOG de Integrao de PIN
function gravaLog_IntegracaoPIN(mixed $mensagem): void
{

	//Arquivo
	$file = $GLOBALS['raiz_do_projeto'] . "arquivos_gerados/logs/logCheckRedeem.txt";

	//Mensagem
	$mensagem =  str_repeat("-", 80) . PHP_EOL . date('Y-m-d H:i:s') . " " . $GLOBALS['_SERVER']['SCRIPT_FILENAME'] . PHP_EOL . (string)$mensagem . PHP_EOL;
	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	}
} //end function gravaLog_EPPCARD

//Funo desenvolvida exclusivamente para a RIOT
function publisherOrderId(mixed $pin_codinterno, mixed $riot_order_id, mixed $pin_channel): void
{
	$sql = "INSERT INTO pins_riot_id (pin_codinterno, riot_order_id, pin_channel)
        VALUES ($1, $2, $3)";
	$params = [$pin_codinterno, $riot_order_id, $pin_channel];

	$rs_log = SQLexecuteQueryParams($sql, $params);
	if (!$rs_log) {
		echo "Erro ao Salvar o ID da Transao do Publisher (RIOT)." . PHP_EOL;
	}
} //end function publisherOrderId

function logEventsONGAME(mixed $msg): void
{

	global $raiz_do_projeto;

	$log  = PHP_EOL . "=================================================================================================" . PHP_EOL;
	$log .= "DATA -> " . date("d/m/Y - H:i:s") . PHP_EOL;
	$log .= "---------------------------------" . PHP_EOL;
	$log .= htmlspecialchars_decode((string)$msg);

	$fp = fopen($raiz_do_projeto . "arquivos_gerados/logs/logONGAME_DEBUG.log", 'a+');
	if ($fp) {
		fwrite($fp, $log);
		fclose($fp);
	}
} //end function logEventsONGAME($msg)
