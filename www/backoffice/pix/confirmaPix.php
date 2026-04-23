<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once "/www/includes/constantes.php";
require_once "/www/includes/constantesPagamento.php";
require_once "/www/includes/gamer/constantes.php";
require_once "/www/includes/main.php";
require_once "/www/includes/inc_Pagamentos.php";
require_once "/www/includes/functions.php";
require_once __DIR__ . "/../includes/encoding.php";

$teste = '{
		"http_status_code": 200,
		"http_status_message": "OK",
		"date": "2022-11-28 09:11:40",
		"response": {
			"message": {
				"status": "TRANSACAO_RECEBIDA",
				"id": "20000000020221128090857435"
			}
		}
	}';

# FUNÇÃO DE CONCILIAÇÃO PARA PDV ( conciliacaoAutomaticaPagtoOnlineExpressMoneyLH / conciliacaoAutomaticaPagtoPIXemPDV )
# FUNÇÃO DE CONCILIAÇÃO PARA USUARIO FINAL ( conciliacaoAutomaticaPagtoPIXemGAMER )
$dados = file_get_contents('php://input');
$infomacoesRecebidas = json_decode($dados); //$teste

//$file = fopen("/www/arquivos_gerados/logs/log_webhook.txt", "a+");
//fwrite($file, str_repeat("*", 50)."\n");
//fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
//fwrite($file, "ID VENDA: ".$dados."\n");
//fwrite($file, str_repeat("*", 50)."\n");
//fclose($file);

# VERIFICAÇÃO DE QUAL AMBIENTE VAI SER TRABALHADO
# 20 = PDV
# 10 = USUARIO FINAL
if ($infomacoesRecebidas != "" && $infomacoesRecebidas != null && isset($infomacoesRecebidas->response->message->id)) {

	if (!isset($infomacoesRecebidas->response->message->password) || !password_verify("9X!d7#AqB4z&K1wF", $infomacoesRecebidas->response->message->password)) {
		exit;
	}

	if (substr($infomacoesRecebidas->response->message->id, 0, 2) == "20") {
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
			$tableName = ($this->ambiente == "PDV") ? "tb_dist_venda_games" : "tb_venda_games";

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
				$venda = $stmt->fetch(PDO::FETCH_ASSOC);
				return $venda;
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
				$data_venda = substr((string)($data_venda ?? ""), 0, 19);

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
				} else {
					$dataInicio = null;
				}

				if ($dataInicio !== null) {
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

			// Seleção da venda
			$sqlSelecao = "SELECT * 
                       FROM {$tableName} 
                       INNER JOIN tb_pag_compras ON idvenda = vg_id 
                       LEFT JOIN {$tableNameJoin} ON vgm_vg_id = vg_id 
                       WHERE vg_id = :idVenda";

			$stmtSelecao = $this->conexao->prepare($sqlSelecao);
			$stmtSelecao->bindParam(':idVenda', $idVenda, PDO::PARAM_INT);
			$stmtSelecao->execute();

			if ($stmtSelecao->rowCount() > 0) {
				$venda = $stmtSelecao->fetch(PDO::FETCH_ASSOC);
				$data_venda = substr((string)($venda["vg_data_inclusao"] ?? ""), 0, 19);
				$valor = substr((string)($venda["total"] ?? ""), 0, -2) . "." . substr((string)($venda["total"] ?? ""), -2);

				// Atualiza a venda
				$sqlAtualiza = "UPDATE {$tableName} 
                            SET vg_ultimo_status_obs = '',
                                vg_usuario_obs = '',
                                vg_pagto_data_inclusao = :dataVenda,
                                vg_ultimo_status = 5,
                                vg_pagto_data = :dataVendaData,
                                vg_pagto_banco = '400',
                                vg_pagto_num_docto = :numDocto,
                                vg_concilia = 1,
                                vg_data_concilia = :dataConcilia,
                                vg_user_id_concilia = '0401121156014',
                                vg_pagto_valor_pago = :valor
                            WHERE vg_id = :idVenda";

				$stmtAtualiza = $this->conexao->prepare($sqlAtualiza);
				$stmtAtualiza->bindParam(':dataVenda', $data_venda, PDO::PARAM_STR);
				$stmtAtualiza->bindValue(':dataVendaData', $data_venda . ".097958", PDO::PARAM_STR);
				$stmtAtualiza->bindValue(':numDocto', "PIXR_" . date("YmdHis") . "591", PDO::PARAM_STR);
				$stmtAtualiza->bindValue(':dataConcilia', $data_venda . ".431227", PDO::PARAM_STR);
				$stmtAtualiza->bindParam(':valor', $valor, PDO::PARAM_STR);
				$stmtAtualiza->bindParam(':idVenda', $idVenda, PDO::PARAM_INT);
				$stmtAtualiza->execute();

				if ($stmtAtualiza->rowCount() > 0 && $this->ambiente === "PDV") {
					// Atualiza saldo do usuário
					$sqlSaldo = "UPDATE dist_usuarios_games 
                             SET ug_perfil_saldo = ug_perfil_saldo + :valor 
                             WHERE ug_id = :idUsuario";

					$stmtSaldo = $this->conexao->prepare($sqlSaldo);
					$stmtSaldo->bindParam(':valor', $valor, PDO::PARAM_STR);
					$stmtSaldo->bindParam(':idUsuario', $venda["vg_ug_id"], PDO::PARAM_INT);
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
		if ($file) {
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
	}

	public function conciliaPix($informacoes)
	{

		$this->status = $informacoes->response->message->status;
		$this->idConciliador = $informacoes->response->message->id;
		$idVendaConciliador = substr((string)($this->idConciliador ?? ""), 2);

		if ($this->ambiente == "PDV") {
			$venda = $this->verificaPagamento($idVendaConciliador);
			if (!is_array($venda)) {
				$this->gravaLog($idVendaConciliador, "", "", "");
				return false;
			}
			if (($venda["status"] == "1" || $venda["status"] == "-1")) {
				$this->atulizaPagamento($idVendaConciliador, $venda["vg_data_inclusao"]);
				$this->atualizaVenda($venda["idvenda"]);
				///conciliacaoAutomaticaPagtoOnlineExpressMoneyLH($venda["idvenda"]);
				$novoRetorno = $this->verificaPagamento($idVendaConciliador);
				if (!is_array($novoRetorno)) {
					$novoRetorno = array("status" => $venda["status"], "vg_ultimo_status" => $venda["vg_ultimo_status"]);
				}
				$conciliado = ($novoRetorno["status"] == '3') ? true : false;
			} else {
				$novoRetorno["status"] = $venda["status"];
				$novoRetorno["vg_ultimo_status"] = $venda["vg_ultimo_status"];
				$conciliado = false;
			}
			$this->gravaLog($venda["idvenda"], $novoRetorno["status"], $venda["status"], $novoRetorno["vg_ultimo_status"]);
		} else {
			$venda = $this->verificaPagamento($idVendaConciliador);
			if (!is_array($venda)) {
				$this->gravaLog($idVendaConciliador, "", "", "");
				return false;
			}
			if (($venda["status"] == "1" || $venda["status"] == "-1")) {
				$this->atulizaPagamento($idVendaConciliador, $venda["vg_data_inclusao"], "USUARIO");
				//$this->atualizaVenda($venda["idvenda"]);
				if ($venda["tipo_deposito"] == 0) {
					$idvenda = htmlspecialchars((string)($venda["idvenda"] ?? ""), ENT_QUOTES, 'UTF-8');
					conciliacaoAutomaticaPagtoPIXemGAMER(true, $idvenda);
				} else if ($venda["tipo_deposito"] == 2) {
					conciliaAutomaticaMoneyDepositoSaldocomPIX(true, $venda["idvenda"]);
				}
				$novoRetorno = $this->verificaPagamento($idVendaConciliador);
				if (!is_array($novoRetorno)) {
					$novoRetorno = array("status" => $venda["status"], "vg_ultimo_status" => $venda["vg_ultimo_status"]);
				}
				$conciliado = ($novoRetorno["status"] == '3') ? true : false;
			} else {
				$novoRetorno["status"] = $venda["status"];
				$novoRetorno["vg_ultimo_status"] = $venda["vg_ultimo_status"];
				$conciliado = false;
			}
			$this->gravaLog($venda["idvenda"], $novoRetorno["status"], $venda["status"], $novoRetorno["vg_ultimo_status"]);
		}

		if ($conciliado) {
			$mensagem = backoffice_utf8_to_iso('<b>O pagamento foi conciliado com sucesso!</b><br> 
			    Data da conciliação: ' . date("d-m-Y H:i:s") . '<br>
				ID pagamento E-Prepag: ' . $idVendaConciliador . '<br>
				Status final venda: ' . $novoRetorno["vg_ultimo_status"] . '<br>
				Ambiente Venda: ' . $this->ambiente . '<br>
				ID de venda E-Prepag: ' . $venda["idvenda"]);
			$retornoEmail = enviaEmail("monitoramento@e-prepag.com.br", "", "", "WEB HOOK(" . $venda["idvenda"] . ")", $mensagem);
			if ($retornoEmail) { //

				$status = ($retornoEmail == true) ? "OK" : "NOK";
				$file = fopen("/www/arquivos_gerados/logs/emailwebhook.txt", "a+");
				if ($file) {
				fwrite($file, str_repeat("*", 50) . "\n");
				fwrite($file, "DATA: " . date("d-m-Y H:i:s") . "\n");
				fwrite($file, "RETORNO DISPARO: " . $status . "\n");
				fwrite($file, "VENDA: " . $venda["idvenda"] . "\n");
				fwrite($file, str_repeat("*", 50) . "\n");
				fclose($file);
				}
				echo "e-mail enviado com sucesso";
			} else {
				$status = ($retornoEmail == true) ? "OK" : "NOK";
				$file = fopen("/www/arquivos_gerados/logs/emailwebhook.txt", "a+");
				if ($file) {
				fwrite($file, str_repeat("*", 50) . "\n");
				fwrite($file, "DATA: " . date("d-m-Y H:i:s") . "\n");
				fwrite($file, "RETORNO DISPARO: " . $status . "\n");
				fwrite($file, "VENDA: " . $venda["idvenda"] . "\n");
				fwrite($file, str_repeat("*", 50) . "\n");
				fclose($file);
				}
				echo "erro e-mail";
			}
		} else {
			echo "Pagamento já conciliado";
		}
	}
}

# 20000000020221109121112256 | 10000000020221109120309450
$pix = new RecebePix($tipoUsuario);
$pix->conciliaPix($infomacoesRecebidas);
http_response_code(200);
