<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros

require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
require "../../../includes/gamer/chave.php";
require "../../../includes/gamer/AES.class.php";
require "../../../includes/gamer/inc_sanitize.php";
require "../../../class/classGeraPin.php";

// Conectando ao banco de dados

$inicio = microtime(true);

try {
    $pdo = ConnectionPDO::getConnection()->getLink();
    // Lista de vg_id recebida
    $sql = "
    SELECT DISTINCT vg.vg_id
    FROM tb_dist_venda_games vg
    JOIN tb_dist_venda_games_modelo vm ON vg.vg_id = vm.vgm_vg_id
    LEFT JOIN tb_dist_venda_games_modelo_pins vp ON vp.vgmp_vgm_id = vm.vgm_id
    WHERE vp.vgmp_vgm_id IS NULL
      AND vg_ultimo_status = '5'
      AND vm.vgm_opr_codigo <> 78
      AND DATE(vg.vg_data_inclusao) >= CURRENT_DATE - INTERVAL '1 day'
    ";

    $stmt = $pdo->query($sql);
    $vg_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Passo 1: Buscar os registros na tabela tb_dist_venda_games_modelo
    $placeholders = implode(',', array_fill(0, count($vg_ids), '?'));
    $query1 = "SELECT vgm_id, vgm_vg_id, vgm_pin_valor, vgm_opr_codigo, vgm_ogp_id, vgm_qtde FROM tb_dist_venda_games_modelo WHERE vgm_vg_id IN ($placeholders)";
    $stmt1 = $pdo->prepare($query1);
    $stmt1->execute($vg_ids);
    $vendas = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    echo "?? Encontradas " . count($vendas) . " vendas.\n";

    foreach ($vendas as $venda) {
        $vgm_id = $venda['vgm_id'];
        $vgm_pin_valor = $venda['vgm_pin_valor'];
        $vgm_opr_codigo = $venda['vgm_opr_codigo'];
        $vgm_ogp_id = $venda['vgm_ogp_id'];
        $vgm_qtde = $venda['vgm_qtde'];

        echo "operadora: $vgm_opr_codigo, produto: $vgm_ogp_id";

        // Verifica se a quantidade de registros é igual ou maior que o necessário
        $queryCheck = "SELECT COUNT(*) FROM tb_dist_venda_games_modelo_pins WHERE vgmp_vgm_id = ?";
        $stmtCheck = $pdo->prepare($queryCheck);
        $stmtCheck->execute([$vgm_id]);
        $qtdeRegistrada = $stmtCheck->fetchColumn();

        if ($qtdeRegistrada >= $vgm_qtde) {
            echo " Venda ID $vgm_id já possui $qtdeRegistrada PIN(s) vinculados (necessário: $vgm_qtde). Pulando...\n";
            continue;
        }

        for ($i = 0; $i < ($vgm_qtde - $qtdeRegistrada); $i++) {
            // Passo 2: Buscar o pin dispon?vel na tabela pins
            $pin_codinterno = null;

            if ($vgm_ogp_id == 488 && $vgm_opr_codigo == 53) {
                $geraPinEpp = new GeraPinVariavel($vgm_pin_valor, 53, 3, 1);

                $pin_codinterno = $geraPinEpp->gerar();
            } else {
                $query2 = "
                    SELECT p.pin_codinterno 
                    FROM pins p 
                    LEFT JOIN tb_dist_venda_games_modelo_pins vgp ON p.pin_codinterno = vgp.vgmp_pin_codinterno 
                    WHERE p.opr_codigo = ? AND p.pin_valor = ? 
                    AND vgp.vgmp_pin_codinterno IS NULL 
                    AND p.pin_status = '1'
                    LIMIT 1";
                $stmt2 = $pdo->prepare($query2);
                $stmt2->execute([$vgm_opr_codigo, $vgm_pin_valor]);
                $pin = $stmt2->fetch(PDO::FETCH_ASSOC);
                if ($pin) {
                    $pin_codinterno = $pin['pin_codinterno'];
                }
            }

            if ($pin_codinterno) {

                // Iniciar a transaction
                $pdo->beginTransaction();

                try {
                    // Passo 3: Inserir na tabela tb_dist_venda_games_modelo_pins
                    $query3 = "INSERT INTO tb_dist_venda_games_modelo_pins (vgmp_vgm_id, vgmp_pin_codinterno) VALUES (?, ?)";
                    $stmt3 = $pdo->prepare($query3);
                    $stmt3->execute([$vgm_id, $pin_codinterno]);

                    // Passo 4: Inserir na tabela pins_dist
                    $query4 = "INSERT INTO pins_dist SELECT * FROM pins WHERE pin_codinterno = ?";
                    $stmt4 = $pdo->prepare($query4);
                    $stmt4->execute([$pin_codinterno]);

                    // Passo 5: Atualizar o status do PIN para 6
                    $query5 = "UPDATE pins SET pin_status = 6 WHERE pin_codinterno = ?";
                    $stmt5 = $pdo->prepare($query5);
                    $stmt5->execute([$pin_codinterno]);

                    // Commit da transaction
                    $pdo->commit();

                    echo "? Processado vg_id: {$venda['vgm_vg_id']}, PIN: $pin_codinterno atualizado.\n";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo "? Erro ao processar vg_id: {$venda['vgm_vg_id']}. Erro: " . $e->getMessage() . "\n";
                }
            } else {
                echo "?? Nenhum PIN dispon?vel para vg_id: {$venda['vgm_vg_id']}.\n";
            }
        }
    }
} catch (PDOException $e) {
    echo "?? Erro de conex?o: " . $e->getMessage();
}

$fim = microtime(true);
$tempoExecucao = $fim - $inicio;

// Echo de finaliza??o
echo "O script levou " . number_format($tempoExecucao, 5) . " segundos para ser executado.";

?>