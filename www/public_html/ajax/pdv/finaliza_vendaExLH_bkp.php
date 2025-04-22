<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros

require_once "../../../includes/constantes.php";
// include do arquivo contendo IPs DEV
require_once DIR_INCS . "configIP.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";
require_once DIR_INCS . "inc_register_globals.php";
$_PaginaOperador1Permitido = 53;
require_once DIR_INCS . "pdv/corte_constantes.php";
require_once DIR_INCS . "config.MeiosPagamentos.php";	
//validacao
$msg = "";

//echo "<pre>";
//print_r($GLOBALS['QUERY_STRING']);
//print_r($GLOBALS['FORM']);
//echo "</pre>";
//die();

//echo "<pre>";
//print_r($GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']);
//echo "</pre>";

//Produtos
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

	$sql = "insert into tb_dist_venda_games (" .
		"vg_id, vg_ug_id, vg_data_inclusao, vg_pagto_tipo, " .
		"vg_ultimo_status, vg_ultimo_status_obs, vg_deposito_em_saldo) values (";
	$sql .= SQLaddFields($venda_id, "") . ",";
	$sql .= SQLaddFields($usuarioId, "") . ",";
	$sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
	$sql .= SQLaddFields($GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'], "") . ",";
	$sql .= SQLaddFields($GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'], "") . ",";
	$sql .= SQLaddFields("", "s") . ", ";
	$sql .= SQLaddFields("1", "") . ")";
	//		$sql .= SQLaddFields($email, "s") . ")";
//echo "sql: $sql<br>";

	$ret = SQLexecuteQuery($sql);
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
	}
	elseif(BANCO_BOLETO == "asaas" || $usuarioGames->getId() == 17371) {
		// INICIO BLOCO BRADESCO
		if ($total_geral >= $BOLETO_LIMITE_PARA_TAXA_ADICIONAL_BRADESCO)
			$taxa_adicional = 0;
		else
			$taxa_adicional = $GLOBALS['BOLETO_TAXA_ADICIONAL_BRADESCO'];

		$qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];
		$bco_codigo = $GLOBALS['BOLETO_MONEY_ASAAS_COD_BANCO'];
		// FIM BLOCO BRADESCO
	}
	 elseif(BANCO_BOLETO == "bradesco") {
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
	$sql = "insert into dist_boleto_bancario_games (" .
		"bbg_ug_id, bbg_vg_id, bbg_data_inclusao, bbg_valor, bbg_valor_taxa, " .
		"bbg_bco_codigo, bbg_documento, bbg_data_venc" .
		") values (";
	$sql .= SQLaddFields($usuarioId, "") . ",";
	$sql .= SQLaddFields($venda_id, "") . ",";
	$sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
	$sql .= SQLaddFields($total_geral + $taxa_adicional, "") . ",";
	$sql .= SQLaddFields($taxa_adicional, "") . ",";
	$sql .= SQLaddFields($bco_codigo, "s") . ",";
	$sql .= SQLaddFields($num_doc, "s") . ","; //documento
	$sql .= SQLaddFields("CURRENT_DATE + interval '$qtde_dias_venc day'", "") . ")"; //vencimento
	$ret = SQLexecuteQuery($sql);

	//echo "sql: $sql<br>";
	//atualiza dados do pagamento e status da venda
	if ($ret) {
		$sql = "update tb_dist_venda_games set 
						vg_cor_codigo = 0,  
						vg_pagto_data_inclusao = " . SQLaddFields("CURRENT_TIMESTAMP", "") . ",
						vg_pagto_banco = '" . $bco_codigo . "',
						vg_pagto_num_docto = '" . $num_doc . "',
						vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['AGUARDANDO_PROCESSAMENTO'], "") . "
					where vg_id = " . $venda_id;
		//echo "sql: $sql<br>";

		$ret = SQLexecuteQuery($sql);
		if (!$ret)
			$msg = "Erro ao atualizar status da venda.\n";
	} else {
		//echo "ret: $ret<br>";
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
	if(BANCO_BOLETO == "asaas" || $usuarioGames->getId() == 17371){
		require_once "../../../banco/asaas/classBoletoAsaas.php";
		$classBoleto = new classBoleto();
		$params = array (
			'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCNPJ)),
			'nome'      => $usuarioGames->ug_sRazaoSocial,
			'valor'     => number_format(($total_geral+$taxa_adicional),2,'.',''),
			'idpedido'  => "PD" . $venda_id,
			'email'    => $usuarioGames->ug_sEmail
		); 
		$token = $classBoleto->callService($params);
		if(!$token) {
			$msg = "Erro ao gerar boleto.";
		}
	}elseif(BANCO_BOLETO == "bradesco"){
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
	$server_url = "www.e-prepag.com.br";
	if (checkIP()) {
		$server_url = $_SERVER['SERVER_NAME'];
	}
	// Envio de boleto
	$GLOBALS['_SESSION']['saldoAdicionado'] = number_format($total_geral, 2, ',', '.');
	;
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