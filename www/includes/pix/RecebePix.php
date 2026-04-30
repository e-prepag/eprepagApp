<?php

declare(strict_types=1);

class RecebePix
{
    private PDO $conexao;
    private string $idConciliador;
    private string $status;
    private string $ambiente;

    public function __construct(string $ambiente)
    {
        /** @var PDO $conn */
        // @phpstan-ignore-next-line
        $conn = ConnectionPDO::getConnection()->getLink();
        $this->conexao = $conn;
        $this->ambiente = $ambiente;
    }

    /**
     * @return array<string, mixed>|false
     */
    private function verificaPagamento(string $idVenda): array|false
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

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : false;
        } catch (PDOException $e) {
            error_log("Erro ao verificar pagamento: " . $e->getMessage());
            return false;
        }
    }

    private function atulizaPagamento(string $idVenda, string $data_venda, string $tipo = "PDV"): bool
    {
        try {
            if ($tipo === "PDV") {
                $data_venda = substr($data_venda, 0, 19);

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

                $dataInicio = null;
                if ($stmtVerifica->rowCount() > 0) {
                    $dadosPagamento = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
                    if (is_array($dadosPagamento)) {
                        $dataInicio = $dadosPagamento['datainicio'];
                    }
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


    private function atualizaVenda(int $idVenda): bool
    {
        try {
            $tableName = ($this->ambiente == "PDV") ? "tb_dist_venda_games" : "tb_venda_games";
            $tableNameJoin = ($this->ambiente == "PDV") ? "tb_dist_venda_games_modelo" : "tb_venda_games_modelo";

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
                if (is_array($venda)) {
                    $data_venda = substr((string)$venda["vg_data_inclusao"], 0, 19);
                    $valor = substr((string)$venda["total"], 0, -2) . "." . substr((string)$venda["total"], -2);

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
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar venda: " . $e->getMessage());
            return false;
        }
    }


    private function gravaLog(string $idVenda, string $novoStatus, string $antigoStatus, string $statusFinalVenda): void
    {
        $confirmaConciliacao = ($novoStatus != $antigoStatus) ? "CONCILIADO COM SUCESSO" : "PEDIDO JÁ CONCILIADO";
        $file = @fopen("/www/arquivos_gerados/logs/log_webhook.txt", "a+");
        if (is_resource($file)) {
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

    /**
     * @param mixed $informacoes
     */
    public function conciliaPix($informacoes): void
    {
        $this->status = (string)$informacoes->response->message->status;
        $this->idConciliador = (string)$informacoes->response->message->id;

        $novoRetorno = ["status" => "", "vg_ultimo_status" => ""];
        $conciliado = false;
        $venda = false;

        if ($this->ambiente == "PDV") {
            $venda = $this->verificaPagamento(substr($this->idConciliador, 2));
            if ($venda !== false && ($venda["status"] == "1" || $venda["status"] == "-1")) {
                $this->atulizaPagamento(substr($this->idConciliador, 2), (string)$venda["vg_data_inclusao"]);
                $this->atualizaVenda((int)$venda["idvenda"]);
                $novoRetornoArr = $this->verificaPagamento(substr($this->idConciliador, 2));
                if ($novoRetornoArr !== false) {
                    $novoRetorno = $novoRetornoArr;
                    $conciliado = ($novoRetorno["status"] == '3');
                }
            } else if ($venda !== false) {
                $novoRetorno["status"] = (string)$venda["status"];
                $novoRetorno["vg_ultimo_status"] = (string)$venda["vg_ultimo_status"];
                $conciliado = false;
            }
            if ($venda !== false) {
                $this->gravaLog((string)$venda["idvenda"], (string)$novoRetorno["status"], (string)$venda["status"], (string)$novoRetorno["vg_ultimo_status"]);
            }
        } else {
            $venda = $this->verificaPagamento(substr($this->idConciliador, 2));
            if ($venda !== false && ($venda["status"] == "1" || $venda["status"] == "-1")) {
                $this->atulizaPagamento(substr($this->idConciliador, 2), (string)$venda["vg_data_inclusao"], "USUARIO");
                if ($venda["tipo_deposito"] == 0) {
                    $idvenda = htmlspecialchars((string)$venda["idvenda"], ENT_QUOTES, 'UTF-8');
                    conciliacaoAutomaticaPagtoPIXemGAMER(true, $idvenda);
                } else if ($venda["tipo_deposito"] == 2) {
                    conciliaAutomaticaMoneyDepositoSaldocomPIX(true, (string)$venda["idvenda"]);
                }
                $novoRetornoArr = $this->verificaPagamento(substr($this->idConciliador, 2));
                if ($novoRetornoArr !== false) {
                    $novoRetorno = $novoRetornoArr;
                    $conciliado = ($novoRetorno["status"] == '3');
                }
            } else if ($venda !== false) {
                $novoRetorno["status"] = (string)$venda["status"];
                $novoRetorno["vg_ultimo_status"] = (string)$venda["vg_ultimo_status"];
                $conciliado = false;
            }
            if ($venda !== false) {
                $this->gravaLog((string)$venda["idvenda"], (string)$novoRetorno["status"], (string)$venda["status"], (string)$novoRetorno["vg_ultimo_status"]);
            }
        }

        if ($conciliado && $venda !== false) {
            $mensagem = utf8_decode('<b>O pagamento foi conciliado com sucesso!</b><br> 
			    Data da conciliação: ' . date("d-m-Y H:i:s") . '<br>
				ID pagamento E-Prepag: ' . substr($this->idConciliador, 2) . '<br>
				Status final venda: ' . (string)$novoRetorno["vg_ultimo_status"] . '<br>
				Ambiente Venda: ' . $this->ambiente . '<br>
				ID de venda E-Prepag: ' . (string)$venda["idvenda"]);
            $retornoEmail = enviaEmail("monitoramento@e-prepag.com.br", "", "", "WEB HOOK(" . (string)$venda["idvenda"] . ")", $mensagem);

            $statusStr = ($retornoEmail === true) ? "OK" : "NOK";
            $file = @fopen("/www/arquivos_gerados/logs/emailwebhook.txt", "a+");
            if (is_resource($file)) {
                fwrite($file, str_repeat("*", 50) . "\n");
                fwrite($file, "DATA: " . date("d-m-Y H:i:s") . "\n");
                fwrite($file, "RETORNO DISPARO: " . $statusStr . "\n");
                fwrite($file, "VENDA: " . (string)$venda["idvenda"] . "\n");
                fwrite($file, str_repeat("*", 50) . "\n");
                fclose($file);
            }
            echo ($retornoEmail === true) ? "e-mail enviado com sucesso" : "erro e-mail";
        } else {
            echo "Pagamento já conciliado";
        }
    }
}
