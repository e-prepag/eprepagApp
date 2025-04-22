<?php

$originalValue = $webhookData['payment']['originalValue'] ? $webhookData['payment']['originalValue'] : null;
$value = $webhookData['payment']['value'] ? $webhookData['payment']['value'] : null;
$interestValue = $webhookData['payment']['interestValue'] ? $webhookData['payment']['interestValue'] : 0;

// Se originalValue n„o for nulo, faz a verificaÁ„o
if (!is_null($originalValue)) {
    // Soma interestValue ao originalValue caso interestValue n„o seja nulo
    $calculatedValue = $originalValue + $interestValue;

    // ComparaÁ„o
    if (bccomp($calculatedValue, $value, 2) !== 0) { 
        echo "Valores n„o batem";
        exit;
    }
}
if(!$value){
	echo "N„o foi possivel obter o valor do boleto";
    exit;
}

if($paymentReference != "" && $paymentReference != null ){

	// if(!isset($infomacoesRecebidas->response->message->password) || !password_verify("9X!d7#AqB4z&K1wF", $infomacoesRecebidas->response->message->password)){
	// 	exit;
	// }

	if(substr($paymentReference, 0, 2) == "PD"){
		require_once "/www/includes/pdv/functions_vendaGames.php";
		require_once "/www/includes/pdv/functions.php";
		$tipoUsuario = "PDV";
	}else{
		require_once "/www/includes/gamer/functions_vendaGames.php";
		require_once "/www/includes/gamer/functions.php";
		require_once "/www/class/gamer/classIntegracao.php";
		require_once "/www/class/gamer/classPromocoes.php";
		require_once "/www/includes/gamer/inc_instrucoes.php";
        require_once "/www/includes/gamer/functions_pagto.php";
		$tipoUsuario = "USUARIO";
	}
}

// error_reporting(E_ALL); // Exibe todos os tipos de erros
// ini_set('display_errors', 1); // Exibe os erros diretamente na tela
// ini_set('log_errors', 1); // Habilita o registro de erros no log do PHP

class RecebeBoleto {
	
	private $conexao;
	private $idVendaConcilia;
	private $status;
	private $ambiente;
	
	public function __construct($ambiente){
		$this->conexao = ConnectionPDO::getConnection()->getLink();
	    $this->ambiente = $ambiente;
	}
	
	private function verificaPagamento($idVenda){
		
		$tableName = ($this->ambiente == "PDV")? "tb_dist_venda_games": "tb_venda_games";
		$tableNameBol = ($this->ambiente == "PDV")? "dist_boleto_bancario_games": "boleto_bancario_games";
		$selecaoVenda = "select bbg_pago, vg_data_inclusao,vg_ultimo_status,vg_pagto_num_docto from $tableNameBol inner join ".$tableName." on bbg_vg_id = vg_id where vg_id = ".$idVenda.";";
		$informacaoVenda = SQLexecuteQuery($selecaoVenda);
		$venda = pg_fetch_array($informacaoVenda);
		
		if(pg_num_rows($informacaoVenda) > 0){
			return $venda;
		}
		return false;
	}
	
	private function buscarBoletoPorVenda($conexao, $bbg_vg_id) {
		// Query SQL para buscar os dados do boleto com base no ID da venda
		$tableNameBol = ($this->ambiente == "PDV")? "dist_boleto_bancario_games": "boleto_bancario_games";
		$sql = "SELECT * FROM $tableNameBol WHERE bbg_vg_id = :bbg_vg_id";
	
		// Preparar a query
		$stmt = $conexao->prepare($sql);
	
		// Executar a query com o par‚metro seguro
		$stmt->execute(["bbg_vg_id" => $bbg_vg_id]);
	
		// Retornar os dados encontrados (fetch para um ˙nico resultado)
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	private function inserirBoleto($conexao, $dados) {
		// Definir as colunas da tabela
		$colunas = [
			"bol_valor",
			"bol_data",
			"bol_banco",
			"bol_cod_documento",
			"bol_aprovado",
			"bol_documento",
			"bol_aprovado_data",
			"bol_pedido",
			"bol_importacao",
			"bol_venda_games_id"
		];
	
		// Criar placeholders para a query (:bol_codigo, :bol_valor, ...)
		$placeholders = ":" . implode(", :", $colunas);
	
		// Montar a query de inserÁ„o
		$sql = "INSERT INTO boletos_pendentes (" . implode(", ", $colunas) . ") 
				VALUES ($placeholders)";
	
		// Preparar a query
		$stmt = $conexao->prepare($sql);
	
		// Executar a query vinculando os valores do array
		return $stmt->execute($dados);
	}
	
	private function gravaLog($idVenda, $novoStatus, $antigoStatus, $statusFinalVenda){
		
		$confirmaConciliacao = ($novoStatus != $antigoStatus)? "CONCILIADO COM SUCESSO": "PEDIDO J√ù CONCILIADO";
		$file = fopen("/www/log/log_webhook.txt", "a+");
		fwrite($file, str_repeat("*", 50)."\n");
		fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
		fwrite($file, "ID VENDA: ". $idVenda."\n");
		fwrite($file, "NOVO STATUS PAGAMENTO: ". $novoStatus."\n");
		fwrite($file, "ANTIGO STATUS PAGAMENTO: ". $antigoStatus."\n");
		fwrite($file, "CODIGO PEDIDO RECEBIDO CDC: ". $this->idVendaConcilia."\n");
		fwrite($file, "STATUS RECEBIDO CDC: ". $this->status."\n");
		fwrite($file, "SITUA√á√ÉO DA CONCILIA√á√ÉO: ". $confirmaConciliacao."\n");
        fwrite($file, "STATUS FINAL VENDA: ". $statusFinalVenda."\n");
        fwrite($file, "AMBIENTE VENDA: ". $this->ambiente."\n");
		fwrite($file, str_repeat("*", 50)."\n");
		fclose($file);
		
	}
	
	public function conciliaBoleto($id, $status){
		
		$this->status = $status;
		$this->idVendaConcilia = substr($id, 2);
		
		if($this->ambiente == "PDV"){
			$venda = $this->verificaPagamento($this->idVendaConcilia);
			//echo $venda["vg_ultimo_status"];
			if(($venda["vg_ultimo_status"] == "1" || $venda["vg_ultimo_status"] == "3")){ 
				$boleto = $this->buscarBoletoPorVenda($this->conexao, $this->idVendaConcilia);

				$dadosBoleto = [
					"bol_valor" => $boleto["bbg_valor"],
					"bol_data" => $boleto["bbg_data_inclusao"],
					"bol_banco" => "461",
					"bol_cod_documento" => null,
					"bol_aprovado" => 0,
					"bol_documento" => $venda["vg_pagto_num_docto"] . 'A',
					"bol_aprovado_data" => null,
					"bol_pedido" => null,
					"bol_importacao" => date('Y-m-d H:i:s'),
					"bol_venda_games_id" => $this->idVendaConcilia
				];

				$this->inserirBoleto($this->conexao, $dadosBoleto);
			
				conciliacaoAutomaticaBoletoExpressMoneyLH($this->idVendaConcilia);
				$novoRetorno = $this->verificaPagamento($this->idVendaConcilia);
				$conciliado = ($novoRetorno["vg_ultimo_status"] == '5')? true: false;
			}else{
				$novoRetorno["vg_ultimo_status"] = $venda["vg_ultimo_status"];
                $novoRetorno["vg_ultimo_status"] = $venda["vg_ultimo_status"];
				$conciliado = false;
			}
			$this->gravaLog($venda["idvenda"], $novoRetorno["status"], $venda["status"], $novoRetorno["vg_ultimo_status"]);
		}else{
			$venda = $this->verificaPagamento($this->idVendaConcilia);
			if(($venda["vg_ultimo_status"] == "1" || $venda["vg_ultimo_status"] == "2")){ 
			    $boleto = $this->buscarBoletoPorVenda($this->conexao, $this->idVendaConcilia);

				$dadosBoleto = [
					"bol_valor" => $boleto["bbg_valor"],
					"bol_data" => $boleto["bbg_data_inclusao"],
					"bol_banco" => "461",
					"bol_cod_documento" => null,
					"bol_aprovado" => 0,
					"bol_documento" => $venda["vg_pagto_num_docto"] . 'A',
					"bol_aprovado_data" => null,
					"bol_pedido" => null,
					"bol_importacao" => null,
					"bol_venda_games_id" => $this->idVendaConcilia
				];

				$this->inserirBoleto($this->conexao, $dadosBoleto);

				echo conciliacaoAutomaticaBoleto();

				$novoRetorno = $this->verificaPagamento($this->idVendaConcilia);
				$conciliado = ($novoRetorno["vg_ultimo_status"] == '5')? true: false;
			}else{
				$novoRetorno["vg_ultimo_status"] = $venda["vg_ultimo_status"];
                $novoRetorno["vg_ultimo_status"] = $venda["vg_ultimo_status"];
				$conciliado = false;
			}	
			$this->gravaLog($venda["idvenda"], $novoRetorno["vg_ultimo_status"], $venda["vg_ultimo_status"], $novoRetorno["vg_ultimo_status"]);
		}
		
		if($conciliado){
			$mensagem = utf8_decode('<b>O pagamento foi conciliado com sucesso!</b><br> 
			    Data da concilia√ß√£o: '.date("d-m-Y H:i:s").'<br>
				ID pagamento E-Prepag: '.$this->idVendaConcilia.'<br>
				Status final venda: '.$novoRetorno["vg_ultimo_status"].'<br>
				Ambiente Venda: '.$this->ambiente.'<br>
				ID de venda E-Prepag: '.$venda["idvenda"]);
				$retornoEmail = enviaEmail("monitoramento@e-prepag.com.br", "", "", "WEB HOOK(".$venda["idvenda"].")", $mensagem);
			if($retornoEmail){ //
			
			    $status = ($retornoEmail == true)? "OK":"NOK";
			    $file = fopen("/www/log/emailwebhook.txt", "a+");
				fwrite($file, str_repeat("*", 50)."\n");
				fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
				fwrite($file, "RETORNO DISPARO: ".$status."\n");
				fwrite($file, "VENDA: ".$venda["idvenda"]."\n");
				fwrite($file, str_repeat("*", 50)."\n");
				fclose($file);
				echo "e-mail enviado com sucesso";
			}else{
				$status = ($retornoEmail == true)? "OK":"NOK";
			    $file = fopen("/www/log/emailwebhook.txt", "a+");
				fwrite($file, str_repeat("*", 50)."\n");
				fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
				fwrite($file, "RETORNO DISPARO: ".$status."\n");
				fwrite($file, "VENDA: ".$venda["idvenda"]."\n");
				fwrite($file, str_repeat("*", 50)."\n");
				fclose($file);
				echo "erro e-mail";
			}
		}else{
			echo "Pagamento j√° conciliado";
		}
	}
	
}

# 20000000020221109121112256 | 10000000020221109120309450
$Boleto = new RecebeBoleto($tipoUsuario);
$Boleto->conciliaBoleto($paymentReference, $paymentStatus);
http_response_code(200);

?>