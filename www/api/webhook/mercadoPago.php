<?php
require_once "/www/includes/constantes.php";
require_once "/www/includes/constantesPagamento.php";
require_once "/www/includes/gamer/constantes.php";
require_once "/www/includes/main.php";
require_once "/www/includes/inc_Pagamentos.php";
require_once "/www/includes/functions.php";
require_once "/www/includes/load_dotenv.php";
require_once "/www/banco/pix/mercadopago/config.inc.pix.php";

$input = file_get_contents("php://input");
$evento = json_decode($input, true);

if (isset($evento['type']) && $evento['type'] == 'payment') {

	if ($evento['action'] == "payment.updated") {
		$paymentId = $evento['data']['id'];

		$retorno = getSondaPIXbyId($paymentId);
		// 3. Lógica do PIX Confirmado
		if ($retorno != false) {
			$paymentReference = $retorno;
		} else {
			http_response_code(403);

			exit("Pedido não encontrado");
		}
	}else{
		echo "pedido recebido";
		http_response_code(200);
		exit;
	}
}else{
	echo "Tipo inválido";
	http_response_code(403);
	exit;
}

$paymentStatus = "Ok";

# VERIFICAÇÃO DE QUAL AMBIENTE VAI SER TRABALHADO
# 20 = PDV
# 10 = USUARIO FINAL
if ($paymentReference != "" && $paymentReference != null) {

	// if(!isset($infomacoesRecebidas->response->message->password) || !password_verify("9X!d7#AqB4z&K1wF", $infomacoesRecebidas->response->message->password)){
	// 	exit;
	// }
	if (substr($paymentReference, 0, 2) == "20") {
		require_once "/www/includes/pdv/functions_vendaGames.php";
		require_once "/www/includes/pdv/functions.php";
		$tipoUsuario = "PDV";
	} else {
		require_once "/www/includes/gamer/functions_vendaGames.php";
		require_once "/www/includes/gamer/functions.php";
		require_once "/www/class/gamer/classIntegracao.php";
		require_once "/www/class/gamer/classPromocoes.php";
		require_once "/www/includes/gamer/inc_instrucoes.php";
		require_once "/www/includes/gamer/functions_pagto.php";
		$tipoUsuario = "USUARIO";
	}
}

class RecebePix
{

	private $conexao;
	private $idConciliador;
	private $status;
	private $ambiente;

	public function __construct($ambiente)
	{
		$this->conexao = ConnectionPDO::getConnection()->getLink();
		$this->ambiente = $ambiente;
	}

	private function verificaPagamento($idVenda)
	{
		try {
			$tableName = ($this->ambiente === "PDV") ? "tb_dist_venda_games" : "tb_venda_games";

			$sql = "SELECT idvenda,
                       status,
                       status_processed,
                       vg_data_inclusao,
                       tipo_deposito,
                       vg_ultimo_status
                FROM tb_pag_compras
                INNER JOIN {$tableName} ON idvenda = vg_id
                WHERE numcompra = :idVenda";

			$stmt = $this->conexao->prepare($sql);
			$stmt->bindParam(':idVenda', $idVenda, PDO::PARAM_STR);
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				return $stmt->fetch(PDO::FETCH_ASSOC);
			}

			return false;
		} catch (PDOException $e) {
			error_log("Erro ao verificar pagamento: " . $e->getMessage());
			return false;
		}
	}


	private function atulizaPagamento($idVenda, $data_venda, $tipo = "PDV")
	{
		try {
			if ($tipo === "PDV") {
				$sql = "UPDATE tb_pag_compras
                    SET status_processed = 1,
                        status = 3,
                        datacompra = :data_venda,
                        dataconfirma = :data_venda
                    WHERE numcompra = :idVenda";

				$stmt = $this->conexao->prepare($sql);
				$stmt->bindParam(':data_venda', $data_venda, PDO::PARAM_STR);
				$stmt->bindParam(':idVenda', $idVenda, PDO::PARAM_STR);
				$stmt->execute();

				return $stmt->rowCount() > 0;
			} else {
				$sqlVerificaData = "SELECT datainicio
                                FROM tb_pag_compras
                                WHERE numcompra = :idVenda
                                  AND (datainicio > (NOW() - INTERVAL '1440 minutes'))";

				$stmtVerifica = $this->conexao->prepare($sqlVerificaData);
				$stmtVerifica->bindParam(':idVenda', $idVenda, PDO::PARAM_STR);
				$stmtVerifica->execute();

				if ($stmtVerifica->rowCount() > 0) {
					$dadosPagamento = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
					$dataInicio = $dadosPagamento['datainicio'];

					$sqlAtualiza = "UPDATE tb_pag_compras
                                SET status_processed = 0,
                                    status = 1,
                                    datainicio = :dataInicio
                                WHERE numcompra = :idVenda";

					$stmtAtualiza = $this->conexao->prepare($sqlAtualiza);
					$stmtAtualiza->bindParam(':dataInicio', $dataInicio, PDO::PARAM_STR);
				} else {
					$sqlAtualiza = "UPDATE tb_pag_compras
                                SET status_processed = 0,
                                    status = 1,
                                    datainicio = CURRENT_TIMESTAMP
                                WHERE numcompra = :idVenda";

					$stmtAtualiza = $this->conexao->prepare($sqlAtualiza);
				}

				$stmtAtualiza->bindParam(':idVenda', $idVenda, PDO::PARAM_STR);
				$stmtAtualiza->execute();

				return $stmtAtualiza->rowCount() > 0;
			}
		} catch (PDOException $e) {
			error_log("Erro ao atualizar pagamento: " . $e->getMessage());
			return false;
		}
	}


	private function atualizaVenda($idVenda)
	{
		try {
			$tableName = ($this->ambiente == "PDV") ? "tb_dist_venda_games" : "tb_venda_games";
			$tableNameJoin = ($this->ambiente == "PDV") ? "tb_dist_venda_games_modelo" : "tb_venda_games_modelo";

			// Selecao da venda
			$sqlSelect = "SELECT * 
                      FROM {$tableName} 
                      INNER JOIN tb_pag_compras ON idvenda = vg_id 
                      LEFT JOIN {$tableNameJoin} ON vgm_vg_id = vg_id 
                      WHERE vg_id = :idVenda";

			$stmtSelect = $this->conexao->prepare($sqlSelect);
			$stmtSelect->bindParam(':idVenda', $idVenda, PDO::PARAM_INT);
			$stmtSelect->execute();

			if ($stmtSelect->rowCount() > 0) {
				$venda = $stmtSelect->fetch(PDO::FETCH_ASSOC);
				$data_venda = date('Y-m-d H:i:s') . '.' . sprintf('%05d', round(microtime(true) * 1000) % 1000);
				$valor = substr($venda["total"], 0, -2) . "." . substr($venda["total"], -2);

				// Atualizacao da venda
				$sqlAtualiza = "UPDATE {$tableName} 
                            SET vg_ultimo_status_obs = '',
                                vg_usuario_obs = :usuario_obs,
                                vg_pagto_data_inclusao = :data_venda,
                                vg_ultimo_status = 5,
                                vg_pagto_data = :data_venda,
                                vg_pagto_banco = '400',
                                vg_concilia = 1,
                                vg_data_concilia = :data_venda,
                                vg_pagto_valor_pago = :valor
                            WHERE vg_id = :idVenda";

				$usuario_obs = "Pagamento Online PIX POR WEBHOOK em " . date('Y-m-d H:i:s');

				$stmtAtualiza = $this->conexao->prepare($sqlAtualiza);
				$stmtAtualiza->bindParam(':usuario_obs', $usuario_obs, PDO::PARAM_STR);
				$stmtAtualiza->bindParam(':data_venda', $data_venda, PDO::PARAM_STR);
				$stmtAtualiza->bindParam(':valor', $valor, PDO::PARAM_STR);
				$stmtAtualiza->bindParam(':idVenda', $idVenda, PDO::PARAM_INT);
				$stmtAtualiza->execute();

				if ($stmtAtualiza->rowCount() > 0 && $this->ambiente === "PDV") {
					// Atualiza saldo do usuario
					$sqlSaldo = "UPDATE dist_usuarios_games 
                             SET ug_perfil_saldo = ug_perfil_saldo + :valor 
                             WHERE ug_id = :ugId";
					$stmtSaldo = $this->conexao->prepare($sqlSaldo);
					$stmtSaldo->bindParam(':valor', $valor, PDO::PARAM_STR);
					$stmtSaldo->bindParam(':ugId', $venda['vg_ug_id'], PDO::PARAM_INT);
					$stmtSaldo->execute();

					return $stmtSaldo->rowCount() > 0;
				}

				return true;
			}

			return false;
		} catch (PDOException $e) {
			error_log("Erro ao atualizar venda: " . $e->getMessage());
			return false;
		}
	}


	private function gravaLog($idVenda, $novoStatus, $antigoStatus, $statusFinalVenda)
	{

		$confirmaConciliacao = ($novoStatus != $antigoStatus) ? "CONCILIADO COM SUCESSO" : "PEDIDO JÝ CONCILIADO";
		$file = fopen("/www/arquivos_gerados/logs/log_webhook.txt", "a+");
		fwrite($file, str_repeat("*", 50) . "\n");
		fwrite($file, "DATA: " . date("d-m-Y H:i:s") . "\n");
		fwrite($file, "ID VENDA: " . $idVenda . "\n");
		fwrite($file, "NOVO STATUS PAGAMENTO: " . $novoStatus . "\n");
		fwrite($file, "ANTIGO STATUS PAGAMENTO: " . $antigoStatus . "\n");
		fwrite($file, "CODIGO PEDIDO RECEBIDO CDC: " . $this->idConciliador . "\n");
		fwrite($file, "STATUS RECEBIDO CDC: " . $this->status . "\n");
		fwrite($file, "SITUAÇÃO DA CONCILIAÇÃO: " . $confirmaConciliacao . "\n");
		fwrite($file, "STATUS FINAL VENDA: " . $statusFinalVenda . "\n");
		fwrite($file, "AMBIENTE VENDA: " . $this->ambiente . "\n");
		fwrite($file, str_repeat("*", 50) . "\n");
		fclose($file);
	}

	public function conciliaPix($id, $status)
	{

		$this->status = $status;
		$this->idConciliador = $id;

		$data_atual = date('Y-m-d H:i:s') . '.' . sprintf('%05d', round(microtime(true) * 1000) % 1000);

		if ($this->ambiente == "PDV") {
			$venda = $this->verificaPagamento(substr($this->idConciliador, 2));
			if (($venda["status"] == "1" || $venda["status"] == "-1")) {
				$this->atulizaPagamento(substr($this->idConciliador, 2), $data_atual);
				$this->atualizaVenda($venda["idvenda"]);
				///conciliacaoAutomaticaPagtoOnlineExpressMoneyLH($venda["idvenda"]);
				$novoRetorno = $this->verificaPagamento(substr($this->idConciliador, 2));
				$conciliado = ($novoRetorno["status"] == '3') ? true : false;
			} else {
				$novoRetorno["status"] = $venda["status"];
				$novoRetorno["vg_ultimo_status"] = $venda["vg_ultimo_status"];
				$conciliado = false;
			}
			$this->gravaLog($venda["idvenda"], $novoRetorno["status"], $venda["status"], $novoRetorno["vg_ultimo_status"]);
		} else {
			$venda = $this->verificaPagamento(substr($this->idConciliador, 2));
			if (($venda["status"] == "1" || $venda["status"] == "-1")) {
				$this->atulizaPagamento(substr($this->idConciliador, 2), $data_atual, "USUARIO");
				//$this->atualizaVenda($venda["idvenda"]);
				if ($venda["tipo_deposito"] == 0) {
					$idvenda = htmlspecialchars($venda["idvenda"], ENT_QUOTES, 'UTF-8');
					conciliacaoAutomaticaPagtoPIXemGAMER(true, $idvenda);
				} else if ($venda["tipo_deposito"] == 2) {
					conciliaAutomaticaMoneyDepositoSaldocomPIX(true, $venda["idvenda"]);
				}
				$novoRetorno = $this->verificaPagamento(substr($this->idConciliador, 2));
				$conciliado = ($novoRetorno["status"] == '3') ? true : false;
			} else {
				$novoRetorno["status"] = $venda["status"];
				$novoRetorno["vg_ultimo_status"] = $venda["vg_ultimo_status"];
				$conciliado = false;
			}
			$this->gravaLog($venda["idvenda"], $novoRetorno["status"], $venda["status"], $novoRetorno["vg_ultimo_status"]);
		}

		if ($conciliado) {
			$mensagem = utf8_decode('<b>O pagamento foi conciliado com sucesso!</b><br> 
			    Data da conciliação: ' . date("d-m-Y H:i:s") . '<br>
				ID pagamento E-Prepag: ' . substr($this->idConciliador, 2) . '<br>
				Status final venda: ' . $novoRetorno["vg_ultimo_status"] . '<br>
				Ambiente Venda: ' . $this->ambiente . '<br>
				ID de venda E-Prepag: ' . $venda["idvenda"]);
			$retornoEmail = enviaEmail("monitoramento@e-prepag.com.br", "", "", "WEB HOOK(" . $venda["idvenda"] . ")", $mensagem);
			if ($retornoEmail) { //

				$status = ($retornoEmail == true) ? "OK" : "NOK";
				$file = fopen("/www/arquivos_gerados/logs/emailwebhook.txt", "a+");
				fwrite($file, str_repeat("*", 50) . "\n");
				fwrite($file, "DATA: " . date("d-m-Y H:i:s") . "\n");
				fwrite($file, "RETORNO DISPARO: " . $status . "\n");
				fwrite($file, "VENDA: " . $venda["idvenda"] . "\n");
				fwrite($file, str_repeat("*", 50) . "\n");
				fclose($file);
				echo "e-mail enviado com sucesso";
			} else {
				$status = ($retornoEmail == true) ? "OK" : "NOK";
				$file = fopen("/www/arquivos_gerados/logs/emailwebhook.txt", "a+");
				fwrite($file, str_repeat("*", 50) . "\n");
				fwrite($file, "DATA: " . date("d-m-Y H:i:s") . "\n");
				fwrite($file, "RETORNO DISPARO: " . $status . "\n");
				fwrite($file, "VENDA: " . $venda["idvenda"] . "\n");
				fwrite($file, str_repeat("*", 50) . "\n");
				fclose($file);
				echo "erro e-mail";
			}
		} else {
			echo "Pagamento já conciliado";
		}
	}
}

$pix = new RecebePix($tipoUsuario);
$pix->conciliaPix($paymentReference, $paymentStatus);
http_response_code(200);
