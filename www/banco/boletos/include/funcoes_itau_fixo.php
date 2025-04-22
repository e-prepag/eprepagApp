<?php

//  ===============================================================================
//##########################################################################################################
//       Dados de entrada
//##########################################################################################################
// $sacado
// $endereco
// $municipio, $uf, $cep
// $taxa_boleto
// $venda_id
//
// $num_doc
// $data_venc
// $valor_boleto = valor + taxa
// $num_doc, $data_venc, $valor_boleto sao obrigatorios para geracao da linha digitavel
//
//##########################################################################################################

$usuario_id1 = 0;
$usuarioGames1 = unserialize($_SESSION['usuarioGames_ser']);
if($usuarioGames1){
	//Codigo do usuario
	$usuario_id1 = $usuarioGames1->getId();
//echo "usuario_id1: $usuario_id1<br>";
//echo "$usuario_id1<br>";
} else {
//echo "Sem usuario cadastrado<br>";
}

$usuario_id_dist = 0;
$usuarioGames_dist = unserialize($_SESSION['dist_usuarioGames_ser']);
if($usuarioGames_dist){
	//Codigo do usuario
	$usuario_id_dist = $usuarioGames_dist->getId();
//echo "usuario_id_dist: $usuario_id_dist<br>";
} else {
//echo "Sem usuario cadastrado<br>";
}


// DADOS DO SEU CLIENTE
//----------------------------------------------------------------------------------------------------------
$dadosboleto["sacado"] 				= $sacado;
$dadosboleto["endereco1"] 			= $endereco;
if(trim($uf) != "")					$dadosboleto["endereco2"] .= " - $uf";
if(trim($cep) != "")				$dadosboleto["endereco2"] .= " - CEP: $cep";


// INFORMACOES PARA O CLIENTE
//----------------------------------------------------------------------------------------------------------
$dadosboleto["demonstrativo1"] 		= "PEDIDO: " . formata_codigo_venda($venda_id);
$dadosboleto["demonstrativo2"] 		= "";
$dadosboleto["demonstrativo3"] 		= "";
$dadosboleto["instrucoes1"] 		= "(Todas as informações deste boleto são de exclusiva responsabilidade do cedente)";	//$usuario_id . " " . $venda_id;
$dadosboleto["instrucoes2"] 		= "Boleto válido para pagamento em qualquer banco até a data do vencimento.";
$dadosboleto["instrucoes3"] 		= "Sr Caixa, não receber após vencimento.";
$dadosboleto["instrucoes4"] 		= "Não havendo a quitação até o vencimento o pedido será cancelado.<br>";
//$dadosboleto["instrucoes4"] 		.= "Taxa de serviço bancário de R$ " . number_format($taxa_boleto, 2, ',', '.') . " já acrescentada ao valor do documento.<br>";
$dadosboleto["instrucoes4"] 		.= "O produto será liberado somente após a compensação deste boleto bancário.<br>";

// DADOS DO BOLETO PARA O SEU CLIENTE - Parte 2
//----------------------------------------------------------------------------------------------------------
$nosso_num_itau = substr($num_doc,3,8) . "-" . modulo_10_itau(substr($num_doc,3,8));
//echo "nosso_num_itau: ".$nosso_num_itau."<br>";
//echo "num_doc: ".$num_doc."<br>";
$dadosboleto["nosso_numero"] 		= substr($num_doc,0,1) . substr($num_doc,3,8);//$num_doc;
$dadosboleto["numero_documento"] 	= $nosso_num_itau;	//$dadosboleto["nosso_numero"];	// Num do pedido ou do documento = Nosso numero
$dadosboleto["data_vencimento"] 	= $data_venc; 					// Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] 		= date("d/m/Y"); 				// Data de emissão do Boleto
$dadosboleto["data_processamento"] 	= date("d/m/Y"); 				// Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] 		= $valor_boleto; 				// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

//------------------------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------------------------- //

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
//----------------------------------------------------------------------------------------------------------
$dadosboleto["quantidade"] 			= "";
$dadosboleto["valor_unitario"] 		= $valor_boleto;
$dadosboleto["aceite"] 				= "N";		
$dadosboleto["uso_banco"] 			= ""; 	
$dadosboleto["especie"] 			= "R$";
$dadosboleto["especie_doc"] 		= "DP";

// DADOS DA SUA CONTA - Itau
//----------------------------------------------------------------------------------------------------------
$dadosboleto["agencia"] 			= $GLOBALS['BOLETO_MONEY_ITAU_CEDENTE_AGENCIA']; 		// Num da agencia, sem digito
$dadosboleto["agencia_dv"] 			= $GLOBALS['BOLETO_MONEY_ITAU_CEDENTE_AGENCIA_DV']; 	// Digito do Num da agencia

//9093 -> "REINALDOPS@HOTMAIL.COM"
//8972 -> "GLAUCIA@GREGIO.COM.BR"
//if(($usuario_id1==9093) || ($usuario_id1==8972)) {
	$dadosboleto["conta"] 				= $GLOBALS['BOLETO_MONEY_ITAU_CEDENTE_CONTA_NOVA'];  		// Num da conta, sem digito
	$dadosboleto["conta_dv"]			= $GLOBALS['BOLETO_MONEY_ITAU_CEDENTE_CONTA_DV_NOVA'];  	// Digito do Num da conta
//} else {
//	$dadosboleto["conta"] 				= $GLOBALS['BOLETO_MONEY_ITAU_CEDENTE_CONTA'];  			// Num da conta, sem digito
//	$dadosboleto["conta_dv"]			= $GLOBALS['BOLETO_MONEY_ITAU_CEDENTE_CONTA_DV'];  			// Digito do Num da conta
//}

// DADOS PERSONALIZADOS - Itau
//----------------------------------------------------------------------------------------------------------
$dadosboleto["conta_cedente"] 		= $GLOBALS['BOLETO_MONEY_ITAU_CEDENTE_CONTA']; 		// ContaCedente do Cliente, sem digito (Somente Números)
$dadosboleto["conta_cedente_dv"] 	= $GLOBALS['BOLETO_MONEY_ITAU_CEDENTE_CONTA_DV']; 		// Digito da ContaCedente do Cliente
$dadosboleto["carteira"] 			= $GLOBALS['BOLETO_MONEY_ITAU_CARTEIRA'];  			// Código da Carteira

// Testes nova conta
//if(($usuario_id==16) || ($usuario_id_dist==3)) {
//	$dadosboleto["conta_cedente"] 			= "20459";	//$GLOBALS['BOLETO_MONEY_ITAU_CEDENTE_CONTA'];  		// Num da conta, sem digito
//	$dadosboleto["conta_cedente_dv"] 		= "5";	//$GLOBALS['BOLETO_MONEY_ITAU_CEDENTE_CONTA_DV'];  	// Digito do Num da conta
//}

// SEUS DADOS
//----------------------------------------------------------------------------------------------------------
$dadosboleto["identificacao"] 		= "E-Prepag Pagtos Eletrônicos SS";
$dadosboleto["cpf_cnpj"] 			= "08.221.305/0001-35";
$dadosboleto["endereco"] 			= "";
$dadosboleto["cidade_uf"] 			= "";
$dadosboleto["cedente"] 			= "E-Prepag Pagtos Eletrônicos SS";


//--------------------------------------------------- FUNCOES ITAU -------------------------------------------- //
$codigobanco = "341";
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

//valor tem 10 digitos, sem virgula
$valor = formata_numero_itau($dadosboleto["valor_boleto"],10,0,"valor");
//agencia é 4 digitos
$agencia = formata_numero_itau($dadosboleto["agencia"],4,0);
//conta é 5 digitos + 1 do dv
$conta = formata_numero_itau($dadosboleto["conta"],5,0);
$conta_dv = formata_numero_itau($dadosboleto["conta_dv"],1,0);
//carteira 175
$carteira = $dadosboleto["carteira"];
//nosso_numero no maximo 8 digitos
$nnum = formata_numero_itau($dadosboleto["nosso_numero"],8,0);

$codigo_barras = $codigobanco.$nummoeda.$fator_vencimento.$valor.$carteira.$nnum.modulo_10_itau($agencia.$conta.$carteira.$nnum).$agencia.$conta.modulo_10_itau($agencia.$conta).'000';
// 43 numeros para o calculo do digito verificador
$dv = digitoVerificador_barra_itau($codigo_barras);
// Numero para o codigo de barras com 44 digitos
$linha = substr($codigo_barras,0,4).$dv.substr($codigo_barras,4,43);

$nossonumero = $carteira.'/'.$nnum.'-'.modulo_10_itau($agencia.$conta.$carteira.$nnum);
$agencia_codigo = $agencia." / ". $conta."-".modulo_10_itau($agencia.$conta);

$linha_digitavel = monta_linha_digitavel($linha);
$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel($linha);
$dadosboleto["agencia_codigo"] = $agencia_codigo;
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;
//  ===============================================================================


$codigobanco = "341";
$codigo_banco_com_dv = geraCodigoBanco_itau($codigobanco);
$nummoeda = "9";
$fator_vencimento = fator_vencimento_itau($dadosboleto["data_vencimento"]);


$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel_itau($linha); // verificar
$dadosboleto["agencia_codigo"] = $agencia_codigo ;
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;



?>