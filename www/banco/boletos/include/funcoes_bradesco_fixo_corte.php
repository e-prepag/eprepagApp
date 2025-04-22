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
// $periodo_ini, $periodo_fim
//
// $num_doc
// $data_venc
// $valor_boleto = valor + taxa
// $num_doc, $data_venc, $valor_boleto sao obrigatorios para geracao da linha digitavel
//
//##########################################################################################################

$usuario_id1 = 0;
if(isset($_SESSION['usuarioGames_ser'])){
    $usuarioGames1 = unserialize($_SESSION['usuarioGames_ser']);
    if($usuarioGames1){
            //Codigo do usuario
            $usuario_id1 = $usuarioGames1->getId();
    //echo "usuario_id1: $usuario_id1<br>";
    } else {
    //echo "Sem usuario cadastrado<br>";
    }
}

$usuario_id_dist = 0;
if(isset($_SESSION['dist_usuarioGames_ser'])){
    $usuarioGames_dist = unserialize($_SESSION['dist_usuarioGames_ser']);
    if($usuarioGames_dist){
            //Codigo do usuario
            $usuario_id_dist = $usuarioGames_dist->getId();
    //echo "usuario_id_dist: $usuario_id_dist<br>";
    } else {
    //echo "Sem usuario cadastrado<br>";
    }
}

// DADOS DO SEU CLIENTE
//----------------------------------------------------------------------------------------------------------
$dadosboleto["sacado"] 				= $sacado;
$dadosboleto["endereco1"] 			= (isset($ug_endereco)) ? $ug_endereco : null;
$dadosboleto["endereco2"] 			= "$municipio - $uf - CEP: $cep";

// INFORMACOES PARA O CLIENTE
//----------------------------------------------------------------------------------------------------------
$dadosboleto["demonstrativo1"] 		= "PERÍODO DE VENDAS " . formata_data($cor_periodo_ini, 0) . " a " . formata_data($cor_periodo_fim, 0);
$dadosboleto["demonstrativo2"] 		= "";
$dadosboleto["demonstrativo3"] 		= "";
$dadosboleto["instrucoes1"] 		= "*** VALORES EXPRESSOS EM REAIS ***";
$dadosboleto["instrucoes2"] 		= "MORA DIA/COM. PERMANÊNCIA................................." . number_format($GLOBALS['BOLETO_JUROS_AO_MES_PRCT'], 2, ",", ".") . "% ao dia";
$dadosboleto["instrucoes3"] 		= "APÓS VENCIMENTO MULTA.........................................5,00%";
//$dadosboleto["instrucoes4"] 		= "SR. CAIXA NAO RECEBER 3 DIAS APOS O VENCIMENTO.<br>";
$dadosboleto["instrucoes4"] 		= "PERÍODO DE VENDAS " . formata_data($cor_periodo_ini, 0) . " a " . formata_data($cor_periodo_fim, 0) . "<br>";
$dadosboleto["instrucoes4"] 		.= "NÃO CONCEDER DESCONTO. COBRAR ENCARGOS APOS O VENCIMENTO.<br>";
$dadosboleto["instrucoes4"] 		.= "A NAO QUITACAO NO PRAZO ACARRETARA NA INTERRUPCAO DOS SERVICOS,<br>BEM COMO EM PROCEDIMENTOS DE COBRANCA E PROTESTO.";

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
$dadosboleto["agencia"] 			= $GLOBALS['BOLETO_CEDENTE_AGENCIA']; 		// Num da agencia, sem digito
$dadosboleto["agencia_dv"] 			= $GLOBALS['BOLETO_CEDENTE_AGENCIA_DV']; 	// Digito do Num da agencia
$dadosboleto["conta"] 				= $GLOBALS['BOLETO_CEDENTE_CONTA'];  		// Num da conta, sem digito
$dadosboleto["conta_dv"] 			= $GLOBALS['BOLETO_CEDENTE_CONTA_DV'];  	// Digito do Num da conta

// DADOS PERSONALIZADOS - Bradesco
//----------------------------------------------------------------------------------------------------------
$dadosboleto["conta_cedente"] 		= (isset($GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_NOVA'])) ? $GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_NOVA'] : null; 		// ContaCedente do Cliente, sem digito (Somente Números)
$dadosboleto["conta_cedente_dv"] 	= (isset($GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_NOVA_DV'])) ? $GLOBALS['BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_NOVA_DV'] : null;		// Digito da ContaCedente do Cliente
$dadosboleto["carteira"] 			= $GLOBALS['BOLETO_CARTEIRA'];  			// Código da Carteira

// Testes nova conta
//if(($usuario_id==16) || ($usuario_id_dist==3)) {
//	$dadosboleto["conta_cedente"] 			= "20459";	//$GLOBALS['BOLETO_CEDENTE_CONTA'];  		// Num da conta, sem digito
//	$dadosboleto["conta_cedente_dv"] 		= "5";	//$GLOBALS['BOLETO_CEDENTE_CONTA_DV'];  	// Digito do Num da conta
//}

// SEUS DADOS
//----------------------------------------------------------------------------------------------------------
$dadosboleto["identificacao"] 		= "E-PREPAG ADMINISTRADORA DE CARTOES LTDA";
$dadosboleto["cpf_cnpj"] 			= "19.037.276/0001-72";
$dadosboleto["endereco"] 			= "";
$dadosboleto["cidade_uf"] 			= "";
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
