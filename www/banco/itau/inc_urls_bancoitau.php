<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php

	require_once DIR_INCS . "inc_register_globals.php";

	$pedido				= str_pad($id_transacao_itau, 8, "0", STR_PAD_LEFT);						// 8 posições (99999999)
	$observacao			= "Observacao: Pagamento Online - Itau Shopline";
	$nomeSacado			= "E-Prepag";
	$codigoInscricao	= "02";								// (01 CPF, 02 CNPJ)
//	$numeroInscricao	= "08221305000135";					// 14 posições (99999999999999)	(CPF, CNPJ, E-Prepag 08.221.305/0001-35) - EPP Pagamentos
    $numeroInscricao	= "19037276000172";					// 14 posições (99999999999999)	(CPF, CNPJ, E-Prepag 19.037.276/0001-72) - EPP Administradora
	$enderecoSacado		= "R. Dep. Lacerda Franco, 300, 2o andar";
	$bairroSacado		= "Pinheiros";
	$cepSacado			= "05418000";						// CEP (99999999):
	$cidadeSacado		= "Sao Paulo";
	$estadoSacado		= "SP";
	$dataVencimento		= getDataItau();					// 8 posições no formato "DDMMAAAA".
	$urlRetorna			= "/pag/ita/ita_retorno.php";	// "https://" //Anterior =>  "EPREPAG_URL/prepag2/pag/ita/ita_retorno.php"
	$ObsAdicional1		= "Pagamentos Online";
	$ObsAdicional2		= "Banco Itau";
	$ObsAdicional3		= "Empresa E-Prepag";

	$data_vencimento = date("dmY");
	// $total_geral é calculado em venda_e_modelos_calculate.php

	$valor_0 = $total_geral;		
	$valor = str_pad(number_format($valor_0, 2, ',', '.'), 10, "0", STR_PAD_LEFT);	// 10 posições (7+1+2 com virgula: 9999999,99) 

    //Removendo o ponto milhar que o Itau não aceito pelo Banco
    $valorAux = str_replace(".", "", $valor);
	$form_fields = "pedido=" . $pedido . "&valor=" . $valorAux . "&observacao=" . $observacao . "&nomeSacado=" . $nomeSacado . "&codigoInscricao=".$codigoInscricao."&numeroInscricao=" . $numeroInscricao . "&enderecoSacado=" . $enderecoSacado . "&bairroSacado=" . $bairroSacado . "&cepSacado=" . $cepSacado ."&cidadeSacado=" . $cidadeSacado . "&estadoSacado=" . $estadoSacado . "&dataVencimento=" . $dataVencimento . "&urlRetorna=" . $urlRetorna . "&ObsAdicional1=" . $ObsAdicional1 . "&ObsAdicional2=" . $ObsAdicional2 . "&ObsAdicional3=" . $ObsAdicional3 . "";

	// dados para o banco
	$dados = "";

	$link_BItauShopline = "https://shopline.itau.com.br/shopline/shopline.aspx";

	$link_BItauShopline_Sonda = "https://shopline.itau.com.br/shopline/consulta.aspx";
?> 
