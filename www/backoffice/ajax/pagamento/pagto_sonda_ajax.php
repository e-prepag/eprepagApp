<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
require_once "/www/includes/bourls.php";
header("Content-Type: text/html; charset=ISO-8859-1", true);
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
set_time_limit(600);

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "includes/constantesPagamento.php";
require_once $raiz_do_projeto . "includes/inc_Pagamentos.php";
require_once $raiz_do_projeto . "includes/functionsPagamento.php";
require_once $raiz_do_projeto . "includes/gamer/inc_functions_epp.php";
require_once $raiz_do_projeto . "banco/bradesco/inc_urls_bradesco.php";
require_once $raiz_do_projeto . "includes/gamer/functions_pagto.php";
require_once $raiz_do_projeto . "includes/pix/RecebePix.php";

$time_start = getmicrotime();

$iforma            = $_REQUEST['iforma'] ?? null;
$numcompra         = $_REQUEST['numcompra'] ?? null;
$status            = $_REQUEST['status'] ?? null;
$id_transacao_itau = $_REQUEST['id_transacao_itau'] ?? null;

$date_now = date("H:i:s");

// send data back to caller
echo "<span style='font-size:10px; font-family: tahoma,arial,sans serif'>" . $date_now . "<br>\n";

?>
<nobr>
	<?php
	if ($iforma == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
		$sonda = getTransacaoPagamentoOK("Transf", $numcompra, $aline);
		echo "[" . (($sonda) ? "<font color='#009900'>OK</font>" : "<font color='#FF0000'>none</font>") . "]";
		echo ((($status == '1' && $sonda) || ($status == '3' && !$sonda)) ? " <font color='#FF0000'>NO SYNC<font>" : "");
	} else if ($iforma == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
		$sonda = getTransacaoPagamentoOK("PagtoFacil", $numcompra, $aline);
		echo "[" . (($sonda) ? "<font color='#009900'>OK</font>" : "<font color='#FF0000'>none</font>") . "]";
		echo (((($status == '1' && $sonda) || ($status == '3' && !$sonda))) ? " <font color='#FF0000'>NO SYNC<font>" : "");
	} else if ($iforma == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
		$sonda = getTransacaoPagamentoOK("BancodoBrasil", $numcompra, $aline);
		$dataconfirma = "'" . substr($aline[3], 6, 4) . "-" . substr($aline[3], 3, 2) . "-" . substr($aline[3], 0, 2) . "'";

		echo "[" . (($sonda) ? "<font color='#009900'>OK</font>" : "<font color='#FF0000'>none</font>") . "]";
		echo (((($status == '1' && $sonda) || ($status == '3' && !$sonda))) ? " <font color='#FF0000'>NO SYNC<font>" : "");	//." <nobr>[".$dataconfirma."]</nobr>";
	} else if ($iforma == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'] || $iforma == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC) {
		$pedido = str_pad($id_transacao_itau, 8, "0", STR_PAD_LEFT);
		$pag_status = getSondaItau($pedido, $a_retorno_itau, $sitPag, $dtPag);
		$sonda = (($pag_status == "00") ? true : false);
		if ($sonda) {
			$sonda = (($sonda) ? "OK" : "none");
			$dataconfirma = "'" . substr($dtPag, 4, 4) . "-" . substr($dtPag, 2, 2) . "-" . substr($dtPag, 0, 2) . "'";
		} else
			$dataconfirma = null;
		//var_dump($sonda);
		//echo $_SERVER['URL']."[$dtPag]".$id_transacao_itau."<pre>".print_r($aline,true)."</pre>";
		echo "[" . (($sonda) ? "<font color='#009900'>OK</font>" : "<font color='#FF0000'>none</font>") . "]";
		echo (((($status == '1' && $sonda) || ($status == '3' && !$sonda))) ? " <font color='#FF0000'>NO SYNC<font>" : "");	//." <nobr>[".$dataconfirma."]</nobr>";
	} else if ($iforma == $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG'] || $iforma == $PAGAMENTO_PIN_EPREPAG_NUMERIC) {
		$sonda = getTransacaoPagamentoOK("PINsEPP", $numcompra, $aline);
		$dataconfirma = "'" . substr($aline[3], 6, 4) . "-" . substr($aline[3], 3, 2) . "-" . substr($aline[3], 0, 2) . "'";

		echo "[" . (($sonda) ? "<font color='#009900'>OK</font>" : "<font color='#FF0000'>none</font>") . "]";
		echo (((($status == '1' && $sonda) || ($status == '3' && !$sonda))) ? " <font color='#FF0000'>NO SYNC<font>" : "");	//." <nobr>[".$dataconfirma."]</nobr>";
	} else if ($iforma == $PAGAMENTO_BANCO_EPP_ONLINE || $iforma == $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC) {
		$sonda = getTransacaoPagamentoOK("BancoEPP", $numcompra, $aline);
		$dataconfirma = "'" . substr($aline[3], 6, 4) . "-" . substr($aline[3], 3, 2) . "-" . substr($aline[3], 0, 2) . "'";

		echo "[" . (($sonda) ? "<font color='#009900'>OK</font>" : "<font color='#FF0000'>none</font>") . "]";
		echo (((($status == '1' && $sonda) || ($status == '3' && !$sonda))) ? " <font color='#FF0000'>NO SYNC<font>" : "");	//." <nobr>[".$dataconfirma."]</nobr>";
	} else if ($iforma == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']) {

		$ARRAY_CONCATENA_ID_VENDA = array(
			'gamer' => '10',
			'pdv' => '20',
		);
	
		$sql = "SELECT * from tb_pag_compras where numcompra = '" . $numcompra . "'";
	
		$rs_sonda = SQLexecuteQuery($sql);
		if (!$rs_sonda) {
			echo "<font color='#FF0000'><b>Erro na Sonda da Compra (" . $numcompra . ").</b></font><br>";
			exit;
		} 
		else {
			$rs_sonda_row = pg_fetch_array($rs_sonda);
	
			$tipo = $rs_sonda_row['tipo_cliente'];
	
			if ($tipo == "LR") {
				$tipoUsuario = $ARRAY_CONCATENA_ID_VENDA['pdv'];
			} elseif ($tipo == "M") {
				$tipoUsuario = $ARRAY_CONCATENA_ID_VENDA['gamer'];
			} else {
				echo "<font color='#FF0000'><b>Não consta Tipo de Pedido na Tabela de Pagamento (" . $numcompra . ").</b></font><br>";
				exit;
			}
		}

		// Define ambiente consumido pela classe RecebePix
		$ambientePix = ($tipo == "LR") ? 'PDV' : 'USUARIO';

		// Monta payload equivalente ao JSON enviado para confirmaPix.php
		$payloadPix = (object)[
			'response' => (object)[
				'message' => (object)[
					'status'   => 'TRANSACAO_RECEBIDA',
					'id'       => $tipoUsuario . $numcompra,
					'password' => '$2y$10$9psPj6jdadcZQRH48VhXpuRaqCAnvmOWs2fMRcwHXAuCvv/o7fhZS',
				],
			],
		];

		$pix = new RecebePix($ambientePix);

		ob_start();
		$pix->conciliaPix($payloadPix);
		$response = ob_get_clean();

		if (strpos($response, "e-mail enviado com sucesso") !== false) {
			$dataconfirma = date('d/m/Y H:i:s');
			echo "[<font color='#009900'>Confirmado</font>]";
			echo "<font color='#FF0000'>$dataconfirma<font>";
		} else {
			echo "[<font color='#FF0000'>Erro ao confirmar</font>]";
		}
	} else if (b_IsPagtoCielo($iforma)) {
		/*
				 $numcompra = '20120418121429796';
				 $status = -1;
				 echo "iforma = '$iforma', numcompra = '$numcompra', status = '$status', id_transacao_itau= '$id_transacao_itau'<br>";
				 */
		$sonda = getTransacaoPagamentoOK("Cielo", $numcompra, $aline);
		$dataconfirma = substr($aline['data'], 0, 19);

		echo "[" . (($sonda) ? "<span title='dataconfirma: $dataconfirma'><font color='#009900'>OK</font></span>" : "<font color='#FF0000'>none</font>") . "]";
	}
	echo "</font>";
	?>
</nobr>