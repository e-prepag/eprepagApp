<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);  // Exibe todos os tipos de erros

require_once "../../../includes/constantes.php";
// include do arquivo contendo IPs DEV
require_once DIR_INCS . "configIP.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";
$_PaginaOperador1Permitido = 53;
require_once DIR_INCS . "pdv/corte_constantes.php";
require_once DIR_INCS . "config.MeiosPagamentos.php";

function finalizaVendaExLHBoletoLog($message, array $context = array()) {
	$context["script"] = "finaliza_vendaExLH.php";
	error_log("[finaliza_vendaExLH_boleto] " . $message . " " . json_encode($context));
}
//validacao

$token_csrf      = $_REQUEST['token_csrf'] ?? null;
$iforma          = $_REQUEST['iforma'] ?? null;
$idu             = $_REQUEST['idu'] ?? null;
$sno             = $_REQUEST['sno'] ?? null;
$btSubmit        = $_REQUEST['btSubmit'] ?? null;
$produtos_valor  = $_REQUEST['produtos_valor'] ?? null;
$email           = $_REQUEST['email'] ?? null;
$produtos        = $_REQUEST['produtos'] ?? null;
$msg        	 = htmlspecialchars($_REQUEST['msg']) ?? "";

if ($msg == "") {
	if (!$produtos)
		$msg = "Nenhum produto selecionado.\n";
}

////Recupera o usuario do session

if (isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser'])) {
	$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
	$usuarioId = $usuarioGames->getId();
} else {
	echo "<script>window.top.location.href = '/creditos/login.php';</script>";
	die();
}
//echo "produtos: $produtos<br>";

if ($msg != "") {
	$msg = "<script>alert('" . str_replace("\n", "\\n", $msg) . "');disableElementId('btnSubmit', false);disableElementId('btnPagamento', false);</script>";
	echo $msg;
	exit;
}

//Usuario
//	$usuarioId = $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'];
//	$usuarioId = $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY_LH'];
//echo "usuarioId: $usuarioId<br>";

//echo "Step: 'Inicia transacao'<br>";

//Inicia transacao
if ($msg == "") {
	$sql = "BEGIN TRANSACTION ";
	$ret = SQLexecuteQuery($sql);
	if (!$ret)
		$msg = "Erro ao iniciar transação.\n";
}

//echo "Step: 'Gera a venda'<br>";
//Gera a venda
if ($msg == "") {
	$venda_id = obterIdVendaValido();
	// Tentar mais 3 vezes
	$iloop = 0;
	while (existeIdVenda($venda_id) && ($iloop < 3)) {
		gravaLog_BoletoExpressLH("venda_id repetido($iloop): " . $venda_id);
		$venda_id = obterIdVendaValido();
		$iloop++;
	}
	// Se ainda não foi encontrado um $venda_id livre vai aparecer um erro e terá que tentar novamente atualizando a página

	//echo "venda_id: $venda_id<br>";

	$sql = "
		    INSERT INTO tb_dist_venda_games (
		        vg_id, 
		        vg_ug_id, 
		        vg_data_inclusao, 
		        vg_pagto_tipo, 
		        vg_ultimo_status, 
		        vg_ultimo_status_obs, 
		        vg_deposito_em_saldo
		    ) VALUES (
		        $1, $2, CURRENT_TIMESTAMP, $3, $4, $5, $6
		    )
		";

	$params = array(
		$venda_id,                                // $1
		$usuarioId,                               // $2
		$GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'], // $3
		$GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'],     // $4
		"",                                       // $5 (vg_ultimo_status_obs)
		"1"                                       // $6 (vg_deposito_em_saldo)
	);

	$ret = SQLexecuteQueryParams($sql, $params);

	if (!$ret) {
		$msg = "Erro ao inserir venda. Por favor, tente novamente atualizando a página. Obrigado.\n";
		gravaLog_BoletoExpressLH($msg . "\n" . $sql);
	}
}

//echo "Step: 'Log na base'<br>";
//Log na base
if ($msg == "") {
	usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['VENDA'], $usuarioId, $venda_id);
}

//echo "Step: 'Boleto'<br>";
//Boleto
if ($msg == "") {

	//obtem o valor total da venda
	//----------------------------------------------------
	// $produtos
	$total_geral = $produtos;

	//Boleto Bradesco
	//Formato do Nosso Numero e Numero do documento
	//----------------------------------------------------
	//4EEEEECCCCC Onde: 
	//4 – identifica MONEY EXPRESS LH
	//CCCCC – código do cliente MONEY (composto com zeros a esquerda)
	//VVVVV – codigo da venda (composto com zeros a esquerda)
	//		$num_doc = "4" . substr("00000" . $usuarioId, -5) . substr("00000" . $venda_id, -5);
	$num_doc = "4" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);

	if ($usuarioGames->b_Is_Boleto_Itau()) {
		//INICIO BLOCO ITAU
		if ($total_geral >= $BOLETO_ITAU_LIMITE_PARA_TAXA_ADICIONAL)
			$taxa_adicional = 0;
		else
			$taxa_adicional = $GLOBALS['BOLETO_ITAU_TAXA_ADICIONAL'];

		$qtde_dias_venc = $GLOBALS['BOLETO_MONEY_ITAU_QTDE_DIAS_VENCIMENTO'];
		$bco_codigo = $GLOBALS['BOLETO_MONEY_ITAU_COD_BANCO'];
		//FIM BLOCO ITAU
	} elseif ($usuarioGames->b_Is_Boleto_Banespa()) {
		//PARA BOLETO SANTANDER
		$qtde_dias_venc = $GLOBALS['BOLETO_BANESPA_QTDE_DIAS_VENCIMENTO'];
		$bco_codigo = $GLOBALS['BOLETO_BANCO_BANESPA_COD_BANCO'];
		if ($total_geral >= $BOLETO_BANESPA_LIMITE_PARA_TAXA_ADICIONAL)
			$taxa_adicional = 0;
		else
			$taxa_adicional = $GLOBALS['BOLETO_BANESPA_TAXA_ADICIONAL'];
		$num_doc = "4" . "000" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
	} elseif (BANCO_BOLETO == "asaas" || $usuarioGames->getId() == 17371) {
		// INICIO BLOCO BRADESCO
		if ($total_geral >= $BOLETO_LIMITE_PARA_TAXA_ADICIONAL_BRADESCO)
			$taxa_adicional = 0;
		else
			$taxa_adicional = $GLOBALS['BOLETO_TAXA_ADICIONAL_BRADESCO'];

		$qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];
		$bco_codigo = $GLOBALS['BOLETO_MONEY_ASAAS_COD_BANCO'];
		// FIM BLOCO BRADESCO
	} elseif (BANCO_BOLETO == "bradesco") {
		// INICIO BLOCO BRADESCO
		if ($total_geral >= $BOLETO_LIMITE_PARA_TAXA_ADICIONAL_BRADESCO)
			$taxa_adicional = 0;
		else
			$taxa_adicional = $GLOBALS['BOLETO_TAXA_ADICIONAL_BRADESCO'];

		$qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];
		$bco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
		// FIM BLOCO BRADESCO
	}
	//Insere boleto na base
	//----------------------------------------------------
	$sql = "
			    INSERT INTO dist_boleto_bancario_games (
			        bbg_ug_id,
			        bbg_vg_id,
			        bbg_data_inclusao,
			        bbg_valor,
			        bbg_valor_taxa,
			        bbg_bco_codigo,
			        bbg_documento,
			        bbg_data_venc
			    ) VALUES (
			        $1, $2, CURRENT_TIMESTAMP, $3, $4, $5, $6, CURRENT_DATE + $7::integer
			    )
			";

	$params = array(
		$usuarioId,             // $1
		$venda_id,              // $2
		$total_geral + $taxa_adicional, // $3
		$taxa_adicional,        // $4
		$bco_codigo,            // $5
		$num_doc,               // $6
		$qtde_dias_venc         // $7
	);

	$ret = SQLexecuteQueryParams($sql, $params);


	//echo "sql: $sql<br>";
	//atualiza dados do pagamento e status da venda
	if ($ret) {
		$sql = "
			    UPDATE tb_dist_venda_games SET
			        vg_cor_codigo = 0,
			        vg_pagto_data_inclusao = CURRENT_TIMESTAMP,
			        vg_pagto_banco = $1,
			        vg_pagto_num_docto = $2,
			        vg_ultimo_status = $3
			    WHERE vg_id = $4
			";

		$params = array(
			$bco_codigo,                                   // $1
			$num_doc,                                      // $2
			$GLOBALS['STATUS_VENDA']['AGUARDANDO_PROCESSAMENTO'], // $3
			$venda_id                                      // $4
		);

		$ret = SQLexecuteQueryParams($sql, $params);

		if (!$ret) {
			$msg = "Erro ao atualizar status da venda.\n";
			finalizaVendaExLHBoletoLog("erro_update_tb_dist_venda_games", array("usuario_id" => $usuarioId, "venda_id" => $venda_id, "banco" => $bco_codigo, "documento" => $num_doc, "erro" => function_exists("pg_last_error") ? pg_last_error() : ""));
		}
	} else {
		$msg = "Erro ao inserir boleto.\n";
		finalizaVendaExLHBoletoLog("erro_insert_dist_boleto_bancario_games", array("usuario_id" => $usuarioId, "venda_id" => $venda_id, "valor" => $total_geral + $taxa_adicional, "taxa" => $taxa_adicional, "banco" => $bco_codigo, "documento" => $num_doc, "dias_vencimento" => $qtde_dias_venc, "erro" => function_exists("pg_last_error") ? pg_last_error() : ""));
	}
} else {
	//echo "msg: $msg<br>";
}

//echo "Step: 'Finaliza transacao'<br>";
//Finaliza transacao
if ($msg == "") {
	$sql = "COMMIT TRANSACTION ";
	$ret = SQLexecuteQuery($sql);
	//if(!$ret) $msg = "Erro ao comitar transação.\n";
} else {
	$sql = "ROLLBACK TRANSACTION ";
	$ret = SQLexecuteQuery($sql);
	//if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
}

//token
if ($msg == "") {
	if (BANCO_BOLETO == "asaas" || $usuarioGames->getId() == 17371) {
		require_once "../../../banco/asaas/classBoletoAsaas.php";
		$classBoleto = new classBoleto();
		$params = array(
			'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCNPJ)),
			'nome'      => $usuarioGames->ug_sRazaoSocial,
			'valor'     => number_format(($total_geral + $taxa_adicional), 2, '.', ''),
			'idpedido'  => "PD" . $venda_id,
			'email'    => $usuarioGames->ug_sEmail
		);
		$token = $classBoleto->callService($params);
		if (!$token) {
			$msg = "Erro ao gerar boleto.";
		}
	} elseif (BANCO_BOLETO == "bradesco") {
		//$token = date('YmdHis') . "," . $venda_id . "," . $usuarioId;
		$token = date('YmdHis', strtotime("+20 day")) . "," . $venda_id . "," . $usuarioId;
		$objEncryption = new Encryption();
		$token = $objEncryption->encrypt($token);
	}
}

//echo "Step: 'Envia email'<br>";
//Envia email
//--------------------------------------------------------------------------------
if ($msg == "") {
	$server_url = "" . EPREPAG_URL . "";
	if (checkIP()) {
		$server_url = $_SERVER['SERVER_NAME'];
	}
	// Envio de boleto
	$GLOBALS['_SESSION']['saldoAdicionado'] = number_format($total_geral, 2, ',', '.');;
	$GLOBALS['_SESSION']['boleto_imagem'] = 'AdicaoSaldoLan';
}

//echo "Retorno<br>";
//Retorno
if ($msg != "") {
	$msg = "<script>alert('" . str_replace("\n", "\\n", $msg) . "');</script>";
	echo $msg;
	exit;
} else {
	$msg = "<font color='red'><strong><span class='style3'>";
	$msg .= "Se a janela do boleto nao abrir automaticamente, ou se tiver algum bloqueador de popup, <br> desabilite-o e ";
	$msg .= "<a href='#' onclick=\"fcnJanelaBoleto('" . $token . "'); return false;\">clique aqui</a> para abrir o boleto novamente!!";
	$msg .= "<script>fcnJanelaBoleto('" . $token . "');</script>";
	$msg .= "</span></strong></font> ";
	echo $msg;
	exit;
}

?>