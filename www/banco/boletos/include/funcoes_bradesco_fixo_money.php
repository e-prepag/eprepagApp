<?php
// +----------------------------------------------------------------------+
// | BoletoPhp - Versão Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo está disponível sob a Licença GPL disponível pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Você deve ter recebido uma cópia da GNU Public License junto com     |
// | esse pacote; se não, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colaborações de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de João Prado Maia e Pablo Martins F. Costa			       	  |
// | 																	                                    |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto Bradesco: Ramon Soares						            |
// +----------------------------------------------------------------------+


// ------------------------- DADOS DINÂMICOS DO SEU CLIENTE PARA A GERAÇÃO DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formulário c/ POST, GET ou de BD (MySql,Postgre,etc)	//

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
$dadosboleto["endereco2"] = NULL;
if($ug_id==4707 || $ug_id==468) {
	$dadosboleto["sacado"] 				= $sacado;
	$dadosboleto["endereco1"] 			= $linha2;
	$dadosboleto["endereco2"] 			= $endereco;
	if(trim($uf) != "")					$dadosboleto["endereco2"] .= $municipio." - $uf";
	if(trim($cep) != "")				$dadosboleto["endereco2"] .= " - CEP: $cep";
} else {
	$dadosboleto["sacado"] 				= $sacado;
	$dadosboleto["endereco1"] 			= $endereco;
	if(trim($uf) != "")					$dadosboleto["endereco2"] .= $municipio." - $uf";
	if(trim($cep) != "")				$dadosboleto["endereco2"] .= " - CEP: $cep";
}

// INFORMACOES PARA O CLIENTE
//----------------------------------------------------------------------------------------------------------
$dadosboleto["demonstrativo1"] 		= "PEDIDO: " . formata_codigo_venda($venda_id);
$dadosboleto["demonstrativo2"] 		= "";
$dadosboleto["demonstrativo3"] 		= "";
$dadosboleto["instrucoes1"] 		= "";	//$usuario_id . " " . $venda_id;
$dadosboleto["instrucoes2"] 		= "Sr Caixa, não receber pagamento em cheque.";
$dadosboleto["instrucoes3"] 		= "Sr Caixa, não receber após vencimento.";
$dadosboleto["instrucoes4"] 		= "Não havendo a quitação até o vencimento o pedido será cancelado.<br>";
if($taxa_boleto > 0) $dadosboleto["instrucoes4"] 		.= "Taxa de serviço bancário de R$ " . number_format($taxa_boleto, 2, ',', '.') . " já acrescentada ao valor do documento.<br>";
$dadosboleto["instrucoes4"] 		.= "O produto será liberado somente após a compensação deste boleto bancário.<br>";

// DADOS DO BOLETO PARA O SEU CLIENTE - Parte 2
//----------------------------------------------------------------------------------------------------------
$dadosboleto["nosso_numero"] 		= $num_doc;
$dadosboleto["numero_documento"] 	= $dadosboleto["nosso_numero"];	// Num do pedido ou do documento = Nosso numero
$dadosboleto["data_vencimento"] 	= $data_venc; 					// Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] 		= date("d/m/Y"); 				// Data de emissão do Boleto
$dadosboleto["data_processamento"] 	= date("d/m/Y"); 				// Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] 		= $valor_boleto; 				// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

//------------------------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------------------------- //

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
//----------------------------------------------------------------------------------------------------------
$dadosboleto["quantidade"] 			= "";
$dadosboleto["valor_unitario"] 		= $valor_boleto;
$dadosboleto["aceite"] 				= "Sem";		
$dadosboleto["uso_banco"] 			= ""; 	
$dadosboleto["especie"] 			= "R$";
$dadosboleto["especie_doc"] 		= "DM";

// DADOS DA SUA CONTA - Bradesco
//----------------------------------------------------------------------------------------------------------
$dadosboleto["agencia"] 			= $GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_AGENCIA']; 		// Num da agencia, sem digito
$dadosboleto["agencia_dv"] 			= $GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_AGENCIA_DV']; 	// Digito do Num da agencia
$dadosboleto["conta"] 				= $GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_CONTA'];  		// Num da conta, sem digito
$dadosboleto["conta_dv"] 			= $GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_DV'];  	// Digito do Num da conta

// DADOS PERSONALIZADOS - Bradesco
//----------------------------------------------------------------------------------------------------------
$dadosboleto["conta_cedente"] 		= $GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_NOVA']; 		// ContaCedente do Cliente, sem digito (Somente Números)
$dadosboleto["conta_cedente_dv"] 	= $GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_NOVA_DV']; 		// Digito da ContaCedente do Cliente
$dadosboleto["carteira"] 			= $GLOBALS['BOLETO_MONEY_BRADESCO_CARTEIRA'];  			// Código da Carteira

// Testes nova conta
//if(($usuario_id==16) || ($usuario_id_dist==3)) {
//	$dadosboleto["conta_cedente"] 			= "20459";	//$GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_CONTA'];  		// Num da conta, sem digito
//	$dadosboleto["conta_cedente_dv"] 		= "5";	//$GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_DV'];  	// Digito do Num da conta
//}

// SEUS DADOS
//----------------------------------------------------------------------------------------------------------
$dadosboleto["identificacao"] 		= "E-PREPAG ADMINISTRADORA DE CARTOES LTDA";
$dadosboleto["cpf_cnpj"] 			= "19.037.276/0001-72";
$dadosboleto["tipo_documento_sacador"] = "2";
$dadosboleto["endereco"] 			= "Rua Deputado Lacerda Franco";
$dadosboleto["numero"]              = "300";
$dadosboleto["complemento"]         = "Conjuntos 26,27 e 28";
$dadosboleto["cidade_uf"] 			= "SP";
$dadosboleto["cidade"]              = "São Paulo";
$dadosboleto["bairro"]              = "Pinheiros";
$dadosboleto["cep"]                 = "05418000";
$dadosboleto["cedente"] 			= "E-PREPAG ADMINISTRADORA DE CARTOES LTDA";


//--------------------------------------------------- FUNCOES BRADESCO -------------------------------------------- //
$codigobanco = "237";
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"],10,0,"valor");

//agencia é 4 digitos
$agencia = formata_numero($dadosboleto["agencia"],4,0);
//conta é 6 digitos
$conta = formata_numero($dadosboleto["conta"],6,0);
//dv da conta
$conta_dv = formata_numero($dadosboleto["conta_dv"],1,0);
//carteira é 2 caracteres
$carteira = $dadosboleto["carteira"];

//nosso número (sem dv) é 11 digitos
$nnum = formata_numero($dadosboleto["carteira"],2,0).formata_numero($dadosboleto["nosso_numero"],11,0);
//dv do nosso número
$dv_nosso_numero = digitoVerificador_nossonumero($nnum);

//conta cedente (sem dv) é 7 digitos
$conta_cedente = formata_numero($dadosboleto["conta_cedente"],7,0);
//dv da conta cedente
$conta_cedente_dv = formata_numero($dadosboleto["conta_cedente_dv"],1,0);

// 43 numeros para o calculo do digito verificador do codigo de barras
$dv = digitoVerificador_barra("$codigobanco$nummoeda$fator_vencimento$valor$agencia$nnum$conta_cedente".'0', 9, 0);
// Numero para o codigo de barras com 44 digitos
$linha = "$codigobanco$nummoeda$dv$fator_vencimento$valor$agencia$nnum$conta_cedente"."0";

$nossonumero = substr($nnum,0,2).'/'.substr($nnum,2).'-'.$dv_nosso_numero;
$agencia_codigo = $agencia."-".$dadosboleto["agencia_dv"]." / ". $conta_cedente ."-". $conta_cedente_dv;

$linha_digitavel = monta_linha_digitavel($linha);
$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel($linha);
$dadosboleto["agencia_codigo"] = $agencia_codigo;
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;

?>