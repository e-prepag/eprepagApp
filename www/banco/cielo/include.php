<?php
define("ENDERECO_BASE_CIELO_PAG_INCLUDES", $raiz_do_projeto . "banco/cielo/");
$logFile = $raiz_do_projeto."log/log_cielo.log";

require ENDERECO_BASE_CIELO_PAG_INCLUDES.'errorHandling.php';
require_once ENDERECO_BASE_CIELO_PAG_INCLUDES.'classPedido.php';

define('VERSAO', "1.1.1");

if(!isset($_SESSION["pedidos"]))
{
	$_SESSION["pedidos"] = new ArrayObject();
}

/**
 * Use test mode if on DEV/Test Server
 * Defined constants for Cielo Environments
 * 
 * Test Cards Numbers:
 *  (visa) 4012001037141112 (authenticates successfully when in debit mode)
 *  (visa) 4551870000000183
 *  (mastercard) 5453010000066167
 *  (elo) 6362970000457013
 *  Security Code: Any value (3 digits number)
 *  Expiration Date: Any date greater than the current date
 * 
 * AT E N Ç Ã O: As constantes  Loja são utilizadas quando os dados do cartão são informados no nosso ambiente da loja
 * As constantes CIELO são utilizadas quando os dados do cartão são informados no ambiente cielo
 */

if(checkIP()) {
    define("ENDERECO_BASE", "https://qasecommerce.cielo.com.br"); //Endereço DEV
    define("LOJA", "1034087115"); //Código da Loja em Produção
    define("LOJA_CHAVE", "559dddb0485f71a4bed766259eb2645ed6abbeb0d1626e78790eb08f7d0ed0d6"); //Chave produção
    define("CIELO", "1001734898");
    define("CIELO_CHAVE", "e84827130b9837473681c2787007da5914d6359947015a5cdb2b8843db0fa832");
    }
else {
    define("ENDERECO_BASE", "https://ecommerce.cbmp.com.br"); //Endereço produção
    define("LOJA", "1006993069");
    define("LOJA_CHAVE", "25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3");
    define("CIELO", "1034087115"); //Código da Cielo em Produção
    define("CIELO_CHAVE", "559dddb0485f71a4bed766259eb2645ed6abbeb0d1626e78790eb08f7d0ed0d6"); //Chave produção   
}
    
define("ENDERECO", ENDERECO_BASE."/servicos/ecommwsec.do");
define("ENDERECO_BASE_CIELO_EPP", ENDERECO_BASE_CIELO_PAG_INCLUDES."pages");

$cielo_codigo_LR = array(
						'00' => 'Transação autorizada.',
						'01' => 'Transação negada. Aguardar contato do emissor.',
						'02' => 'Transação negada. Contatar emissor',
						'03' => 'Transação negada. Estabelecimento inválido.',
						'04' => 'Transação negada - Contatar emissor (Problemas com cartão)',
						'05' => 'Não Autorizada pelo emissor',
						'06' => 'Problemas ocorridos na transação eletrônica.',
						'07' => 'Transação negada - Contatar emissor (Problemas com cartão)',
						'08' => 'Cód de Seg Invalido',
						'11' => 'Transação autorizada.',
						'12' => 'Transação inválida.',
						'13' => 'Valor inválido / Parcelado Loja não atingiu valor minimo por parcela - 5,00R$',
						'14' => 'Cartão inválido',
						'15' => 'Emissor sem comunicação.',
						'19' => 'Refaça a transação',
						'21' => 'Transação não localizada.',
						'22' => 'Parcelamento inválido',
						'25' => 'Número do cartão não foi enviado.',
						'28' => 'Arquivo indisponível.',
						'30' => 'Autorização negada',
						'41' => 'Transação negada - Contatar emissor (Problemas com cartão)',
						'43' => 'Transação negada - Contatar emissor (Problemas com cartão)',
						'51' => 'Não Autorizada pelo Emissor',
						'52' => 'Cartão com dígito de controle inválido.',
						'53' => 'Cartão inválido para essa operação.',
						'54' => 'Cartão Vencido',
						'55' => 'Senha Inválida',
						'57' => 'Transação não permitida para o cartão.',
						'61' => 'Transação negada - Possivel problema com sistema do banco.',
						'62' => 'Transação negada - Cartão não permitido para transação online.',
						'63' => 'Transação negada - Possivel erro de segurança ao tentar processar.',
						'65' => 'Transação negada.',
						'75' => 'Senha Bloqueada',
						'76' => 'Problemas com número de referência da transação.',
						'77' => 'Dados não conferem com mensagem original.',
						'78' => 'Cartão Bloqueado 1º USO',
						'80' => 'Data inválida.',
						'81' => 'Erro de criptografia.',
						'82' => 'Código de Segurança Incorreto ou Inválido',
						'83' => 'Erro no sistema de senhas.',
						'85' => 'Erro métodos de criptografia.',
						'86' => 'Refaça a transação.',
						'91' => 'Emissor sem comunicação..',
						'93' => 'Transação negada - Violação de regra bancária',
						'94' => 'Transação negada - Violação de regra bancária',
						'96' => 'Venda abaixo de  R$ 1,00',
						'98' => 'Emissor sem comunicação.',
						'99' => 'Possivel erro de sistema - Contatar suporte.',
					);

// Envia requisição
function cielo_httprequest($paEndereco, $paPost){

	$sessao_curl = curl_init();
	curl_setopt($sessao_curl, CURLOPT_URL, $paEndereco);
	gravaLog_CIELO("Endereço Cielo: ".$paEndereco.PHP_EOL);
	curl_setopt($sessao_curl, CURLOPT_FAILONERROR, true);

	//  CURLOPT_SSL_VERIFYPEER
	//  verifica a validade do certificado
	curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, 0);
	//  CURLOPPT_SSL_VERIFYHOST
	//  verifica se a identidade do servidor bate com aquela informada no certificado
	curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 0);

	//  CURLOPT_SSL_CAINFO
	//  informa a localização do certificado para verificação com o peer

	//  CURLOPT_CONNECTTIMEOUT
	//  o tempo em segundos de espera para obter uma conexão
	curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, 10);

	//  CURLOPT_TIMEOUT
	//  o tempo máximo em segundos de espera para a execução da requisição (curl_exec)
	curl_setopt($sessao_curl, CURLOPT_TIMEOUT, 40);

        //  CURLOPT_RETURNTRANSFER
	//  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
	//  invés de imprimir o resultado na tela. Retorna FALSE se há problemas na requisição
	curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);

    	curl_setopt($sessao_curl, CURLOPT_HEADER, 0);
    	curl_setopt($sessao_curl, CURLOPT_POST, true);
	curl_setopt($sessao_curl, CURLOPT_POSTFIELDS, $paPost );
        
        $resultado = curl_exec($sessao_curl);
        
	if ($resultado)
	{
		curl_close($sessao_curl);
		return $resultado;
	}
	else
	{

		$curl_error_ret = curl_error($sessao_curl);
		curl_close($sessao_curl);
		return $curl_error_ret;
	}
}

// Monta URL de retorno
function ReturnURL()
{
	$pageURL = 'http';

	if ($GLOBALS['_SERVER']["SERVER_PORT"] == 443) // protocolo https
	{
		$pageURL .= 's';
	}
	$pageURL .= "://";
        $pageURL .= $GLOBALS['_SERVER']["SERVER_NAME"]. substr($GLOBALS['_SERVER']["REQUEST_URI"], 0);

	$file = substr($GLOBALS['_SERVER']["SCRIPT_NAME"],strrpos($GLOBALS['_SERVER']["SCRIPT_NAME"],"/")+1);

	$ReturnURL = str_replace($file, "retorno.php", $pageURL);
	gravaLog_CIELO("Retorno: $ReturnURL".PHP_EOL);  
	return $ReturnURL;
}

function getSondaCielo($numero,&$a_resp){ 
	$acao = "consultar";
	//Código do STATUS é igual a CAPTURADA(6)
	$cod_sucesso = '6';
	
	$sql = "SELECT * from tb_pag_compras where numcompra = '".$numero."'";
	$rs_sonda = SQLexecuteQuery($sql);
	if(!$rs_sonda) {
		 echo "<font color='#FF0000'><b>Erro na Sonda da Compra (".$numero.").".PHP_EOL."</b></font><br>";
		 return false;
	}
	else {
		$rs_sonda_row = pg_fetch_array($rs_sonda);
		$tid	= $rs_sonda_row['cielo_tid'];
		$pan	= $rs_sonda_row['cielo_pan'];
		$valor	= $rs_sonda_row['total'];
		$nsu	= $rs_sonda_row['cielo_nsu'];
		$objResposta = null;

                $Pedido = new Pedido();
		$Pedido->tid = $tid;
		$Pedido->dadosEcNumero	= CIELO;
		$Pedido->dadosEcChave	= CIELO_CHAVE;
		switch($acao)
		{
			case "autorizar":  
				$objResposta = $Pedido->RequisicaoAutorizacaoTid();
				break;
			case "capturar": 
				$PercentualCaptura = $Pedido->dadosPedidoValor;
				$objResposta = $Pedido->RequisicaoCaptura($PercentualCaptura, null);
				break;
			case "cancelar":
				$objResposta = $Pedido->RequisicaoCancelamento();
				break;
			case "consultar": 
				$objResposta = $Pedido->RequisicaoConsulta(); 
				break; 
		}
		
		$a_resp = array();

		$a_resp['numero'] = (string)$objResposta->{'dados-pedido'}->numero;
		$a_resp['valor'] = (string)$objResposta->{'dados-pedido'}->valor;
		$a_resp['pan'] = (string)$objResposta->pan;
		$a_resp['tid'] = (string)$objResposta->tid;
		$a_resp['nsu'] = (string)$objResposta->autorizacao->nsu;
		$a_resp['status'] = (string)$objResposta->status;
		$a_resp['codigo_lr'] = (string)$objResposta->autorizacao->lr;
		$a_resp['data'] = str_replace('T',' ',$objResposta->captura->{'data-hora'});
		// Teste se STATUS é igual a CAPTURADA 
		if (($objResposta->status == $cod_sucesso) && ($tid == $a_resp['tid']) && ($valor == $a_resp['valor']) && ($numero == $a_resp['numero'])) {
                        $sql = "UPDATE tb_pag_compras SET cielo_nsu='".$a_resp['nsu']."',cielo_pan='".$a_resp['pan']."', cielo_status=".$a_resp['status'].",cielo_codigo_lr='".$a_resp['codigo_lr']."' where numcompra = '".$numero."';";
                        $rs_compra = SQLexecuteQuery($sql);
                        if(!$rs_compra) {
                            gravaLog_CIELO("ERRO no UPDATE da transação CIELO: ".$sql.PHP_EOL);
                        }
			return true;
		}
		else {
			return false;
		}
	}
}

function gravaLog_CIELO($mensagem){

	//Arquivo
	$file = $GLOBALS['raiz_do_projeto']."log/log_pagamento_CIELO.txt";
	
	//Mensagem
	$mensagem = str_repeat("-", 80).PHP_EOL.date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . PHP_EOL . $mensagem . PHP_EOL;

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 
}

function gravaLog_CIELO_TMP($mensagem){

	//Arquivo
	$file = $GLOBALS['raiz_do_projeto']."log/log_pagamento_CIELO_TMP.txt";
	
	//Mensagem
	$mensagem = str_repeat("-", 80).PHP_EOL.date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . PHP_EOL . $mensagem . PHP_EOL;

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 
}

// função que retorna se o código LR esta catalogado no array $cielo_codigo_LR
function verifica_LR_Catalogado($codigo){
	$teste = array_keys($GLOBALS['cielo_codigo_LR']);
	if (in_array($codigo, $teste)){ 
		return true;
	}else{
		return false;
	}
}

// função que retorna a descrição do código LR no array $cielo_codigo_LR
function exibe_descricao_LR($codigo){
	if(verifica_LR_Catalogado($codigo)){
		return $GLOBALS['cielo_codigo_LR'][$codigo];
	}
	else {
		return "C&oacute;digo N&Atilde;O Catalogado.";
	}
}
?>