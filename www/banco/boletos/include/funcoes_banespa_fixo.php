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


// ------------------------- DADOS DINÂMICOS DO SEU CLIENTE PARA A GERAÇÃO DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formulário c/ POST, GET ou de BD (MySql,Postgre,etc)	//

// DADOS DO BOLETO PARA O SEU CLIENTE

// INicio do bloco de variaveis para teste
/*
$num_doc = "200003008030"; //ATENÇAUN: preparar numero com 12 posições do Itau tem 11 (depois necessario o digito modulo 11
$data_venc = "13/11/2013";
$valor_boleto = "10,50";
$sacado= "Wagner de Miranda";
$endereco ="Rua: Gonçalves Figueira, 177 Casa 2 Bairro: Casa Verde";
$venda_id = "2018570";
 */
// FIM do bloco de variaveis para teste

$nosso_num_banespa = $num_doc.modulo_11_autoconferencia($num_doc);
$dadosboleto["nosso_numero"] 		= substr($num_doc,0,1) . substr($num_doc,3,8);//$num_doc;
$dadosboleto["numero_documento"] 	= $nosso_num_banespa;	//$dadosboleto["nosso_numero"];	// Num do pedido ou do documento = Nosso numero
$dadosboleto["data_vencimento"] 	= $data_venc; 					// Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] 		= date("d/m/Y"); 				// Data de emissão do Boleto
$dadosboleto["data_processamento"] 	= date("d/m/Y"); 				// Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] 		= $valor_boleto; 				// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] 			= $sacado;
$dadosboleto["endereco1"] 		= $endereco;
if(trim($uf) != "")			$dadosboleto["endereco2"] .= $municipio." - $uf";
if(trim($cep) != "")			$dadosboleto["endereco2"] .= " - CEP: $cep";

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] 		= "PEDIDO: " . formata_codigo_venda($venda_id);
$dadosboleto["demonstrativo2"] 		= "";
$dadosboleto["demonstrativo3"] 		= "";
$dadosboleto["instrucoes1"] 		= "(Todas as informações deste boleto são de exclusiva responsabilidade do cedente)";	//$usuario_id . " " . $venda_id;
$dadosboleto["instrucoes2"] 		= "Boleto válido para pagamento em qualquer banco até a data do vencimento.";
$dadosboleto["instrucoes3"] 		= "Sr Caixa, não receber após vencimento.";
$dadosboleto["instrucoes4"] 		= "Não havendo a quitação até o vencimento o pedido será cancelado.<br>";
$dadosboleto["instrucoes4"] 		.= "O produto será liberado somente após a compensação deste boleto bancário.<br>";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] 		= "";
$dadosboleto["valor_unitario"] 		= $valor_boleto;
$dadosboleto["aceite"] 			= "N";		
$dadosboleto["uso_banco"] 		= ""; 	
$dadosboleto["especie"] 		= "R$";
$dadosboleto["especie_doc"] 		= "DP";


// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //

// DADOS PERSONALIZADOS - Banespa 
/*
$BOLETO_MONEY_BANCO_BANESPA_COD_BANCO = "033";
$BOLETO_MONEY_BANESPA_CODIGO_CEDENTE = "6377980";
$BOLETO_MONEY_BANESPA_CARTEIRA  = "102";
$BOLETO_MONEY_BANESPA_CEDENTE_AGENCIA = "3793";
$BOLETO_MONEY_BANESPA_CEDENTE_AGENCIA_DV = "1";
$BOLETO_MONEY_BANESPA_CEDENTE_CONTA = "130062938";
$BOLETO_MONEY_BANESPA_CEDENTE_CONTA_DV = "";
*/

if($usuarioGames_dist){
    //CONSTANTES DE PDV
    
    $dadosboleto["codigo_cedente"]	= $GLOBALS['BOLETO_BANESPA_CODIGO_CEDENTE'];  // Código do cedente 
    $dadosboleto["carteira"]		= $GLOBALS['BOLETO_BANESPA_CARTEIRA'];        // Código da Carteira
    // SEUS DADOS
    //----------------------------------------------------------------------------------------------------------
    $dadosboleto["agencia"] 		= $GLOBALS['BOLETO_BANESPA_CEDENTE_AGENCIA']; 		// Num da agencia, sem digito
    $dadosboleto["agencia_dv"] 		= $GLOBALS['BOLETO_BANESPA_CEDENTE_AGENCIA_DV']; 	// Digito do Num da agencia

    $dadosboleto["conta"] 		= $GLOBALS['BOLETO_BANESPA_CEDENTE_CONTA'];  	// Num da conta, sem digito
    $dadosboleto["conta_dv"]		= $GLOBALS['BOLETO_BANESPA_CEDENTE_CONTA_DV'];  	// Digito do Num da conta
    //----------------------------------------------------------------------------------------------------------
    
    $codigobanco = $GLOBALS['BOLETO_BANCO_BANESPA_COD_BANCO'];

}
else {
    //CONSTANTES DE GAMERS
    
    $dadosboleto["codigo_cedente"]	= $GLOBALS['BOLETO_MONEY_BANESPA_CODIGO_CEDENTE'];  // Código do cedente 
    $dadosboleto["carteira"]		= $GLOBALS['BOLETO_MONEY_BANESPA_CARTEIRA'];        // Código da Carteira
    // SEUS DADOS
    //----------------------------------------------------------------------------------------------------------
    $dadosboleto["agencia"] 		= $GLOBALS['BOLETO_MONEY_BANESPA_CEDENTE_AGENCIA']; 		// Num da agencia, sem digito
    $dadosboleto["agencia_dv"] 		= $GLOBALS['BOLETO_MONEY_BANESPA_CEDENTE_AGENCIA_DV']; 	// Digito do Num da agencia

    $dadosboleto["conta"] 		= $GLOBALS['BOLETO_MONEY_BANESPA_CEDENTE_CONTA'];  	// Num da conta, sem digito
    $dadosboleto["conta_dv"]		= $GLOBALS['BOLETO_MONEY_BANESPA_CEDENTE_CONTA_DV'];  	// Digito do Num da conta
    //----------------------------------------------------------------------------------------------------------
    
    $codigobanco = $GLOBALS['BOLETO_MONEY_BANCO_BANESPA_COD_BANCO'];

}

$dadosboleto["ponto_venda"]     = "400";                                            // Ponto de Venda = Agencia
$dadosboleto["nome_da_agencia"] = "";  // Nome da agencia (Opcional)


// SEUS DADOS
$dadosboleto["identificacao"] 	= "E-Prepag Pagamentos Eletrônicos Ltda";
$dadosboleto["cpf_cnpj"] 	= "08.221.305/0001-35";
$dadosboleto["endereco"] 	= "";
$dadosboleto["cidade_uf"] 	= "";
$dadosboleto["cedente"] 	= "E-Prepag Pagamentos Eletrônicos Ltda";


//--------------------------------------------------- FUNCOES BANESPA -------------------------------------------- //
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
$valor_fixo_cod_barras = "9";
$iof = "0";
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"],10,0,"valor");
//Modalidade Carteira
$carteira = formata_numero($dadosboleto["carteira"],3,0);
//codigocedente deve possuir 7 caracteres
$codigocliente = formata_numero($dadosboleto["codigo_cedente"],7,"valor");

//agencia é 4 digitos
$agencia = formata_numero($dadosboleto["agencia"],4,0);
//conta é 5 digitos + 1 do dv
$conta = formata_numero($dadosboleto["conta"],5,0);
$conta_dv = formata_numero($dadosboleto["conta_dv"],1,0);
//nosso_numero no maximo 13 digitos
$nossonumero = formata_numero($dadosboleto["numero_documento"],13,0);
//carteira

// Calcula vencimento juliano
$vencjuliano = dataJuliano($vencimento);

// Calcula Campo Livre --- Não foi identificado utilização // Bloqueado por conta do loop infinito na função abaixo
$campoLivre = calculaCampoLivre((int)($codigocliente.$nossonumero."00".$codigobanco));

// Nova montagem elaborada por Wagner
//Posição   Tam.    Conteúdo                                    Exemplo
//01-03     3       Identificação do Banco = 033                033
//04-04     1       Código da moeda 9 = real                    9
//05-05     1       DV do código de barras
//06-09     4       Fator de vencimento                         2046
//10-19     10      Valor nominal                               273,71
//20-20     1       Fixo ?9?                                     9
//21-27     7       número do PSK(Código do Cliente)            0282033
//28-40     13      Nosso Número                                5666124578002
//41-41     1       IOF ?Seguradoras - Demais clientes= zero    0
//42-44     3       102- Cobrança simples ? SEM Registro        102
$linha = monta_codigo_de_barras_wagner($codigobanco.$nummoeda.$fator_vencimento.$valor.$valor_fixo_cod_barras.$codigocliente.$nossonumero.$iof.$carteira);

$agencia_codigo = $agencia." / ".$dadosboleto["codigo_cedente"]; // $conta."-".modulo_10($agencia.$conta);

$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel_wagner($linha);
$dadosboleto["nosso_numero"] = $nossonumero; //calcula_verificador_nosso_numero($dadosboleto["ponto_venda"], $nossonumero);
$dadosboleto["agencia_codigo"]  = $agencia_codigo;
$dadosboleto["agencia_conta"] = $agencia." / ".substr($dadosboleto["codigo_cedente"],0,3)." ".substr($dadosboleto["codigo_cedente"],3,2)." ".substr($dadosboleto["codigo_cedente"],5,5)." ".substr($dadosboleto["codigo_cedente"],10);
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;

?>