<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
require "../../../includes/gamer/chave.php";
require "../../../includes/gamer/AES.class.php";

$pdo = ConnectionPDO::getConnection()->getLink();

$chave256bits = new Chave();
$aes = new AES($chave256bits->retornaChave());

// Defina o batch (intervalo de PINs a processar)
$inicio = 1701;
$fim = 2000;

$arquivo = 'pins_rei_dos_coins.csv';
$pinsCsv = [];

if (($handle = fopen($arquivo, "r")) !== false) { 
    $cabecalho = fgetcsv($handle, 1000, ",");
    
    while (($linha = fgetcsv($handle, 1000, ",")) !== false) { 
        $dados = array_combine($cabecalho, $linha);
        if (!empty($dados['pin'])) {
            $pinsCsv[] = [
                'produto' => $dados['produto'], 
                'pin'     => $dados['pin']
            ];
        }
    }
    fclose($handle);
}

$totalPins = count($pinsCsv);
if ($inicio >= $totalPins) {
    die("Erro: O valor de início ($inicio) é maior ou igual ao total de PINs ($totalPins).");
}

if ($fim > $totalPins) {
    $fim = $totalPins; // Ajusta automaticamente para o último PIN disponível
}

$pinsBatch = array_slice($pinsCsv, $inicio, $fim - $inicio);

$pinsEpp = [];

foreach ($pinsBatch as $item) {
    if (stripos($item['produto'], 'E-Prepag') !== false) {
        $pinsEpp[] = base64_encode($aes->encrypt($item['pin']));
    }
}

$pdo->beginTransaction();

try {

    $pinsEppParaAtualizar = [];
    if (!empty($pinsEpp)) {
        $placeholders = implode(',', array_fill(0, count($pinsEpp), '?'));
        $sql = "UPDATE pins_store
                SET pin_status = -1 
                WHERE pin_codigo IN ($placeholders) 
                AND pin_status = 3 
                RETURNING pin_codinterno";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($pinsEpp);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pinsEppParaAtualizar[] = $row['pin_codinterno'];
        }
    }

    $pinsEppAtualizados = [];
    if (!empty($pinsEppParaAtualizar)) {
        $placeholders = implode(',', array_fill(0, count($pinsEppParaAtualizar), '?'));
        $sql = "UPDATE pins 
                SET pin_status = '9' 
                WHERE pin_codinterno IN (
                    SELECT pins_pin_codinterno FROM tb_pins_store_pins WHERE pins_store_pin_codinterno IN ($placeholders)
                ) AND pin_status = '6'
                RETURNING pin_codigo, pin_valor, pin_codinterno";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($pinsEppParaAtualizar);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pinsEppAtualizados[] = $row;
        }
    }

    $pinsParaVenda = $pinsEppAtualizados;
    $valorTotal = 0;
    $valorComissaoTotal = 0;
    $valorDescontoTotal = 0;
    $qtdEpp = 0;

    foreach ($pinsParaVenda as &$pin) {
        $sql = "SELECT vgm.vgm_id, vgm.vgm_pin_valor, vgm.vgm_perc_desconto 
                FROM tb_dist_venda_games_modelo vgm
                JOIN tb_dist_venda_games_modelo_pins vgmp ON vgm.vgm_id = vgmp.vgmp_vgm_id
                WHERE vgmp.vgmp_pin_codinterno = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pin['pin_codinterno']]);
        $venda = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($venda) {
            $pin['vgm_id'] = $venda['vgm_id'];
            $pin['valor_venda'] = $venda['vgm_pin_valor'];
            $pin['valor_desconto'] = $venda['vgm_pin_valor']*$venda['vgm_perc_desconto']/100;
            $pin['comissao'] = $venda['vgm_pin_valor'] - $venda['vgm_pin_valor']*$venda['vgm_perc_desconto']/100;

            // Acumula valores totais
            $valorTotal += $venda['vgm_pin_valor'];
            $valorDescontoTotal += $pin['valor_desconto'];
            $valorComissaoTotal += $pin['comissao'];

            // Conta os E-Prepag
            $qtdEpp++;
        }
    }

    // ?? Criar o resumo em TXT
    $resumo = "Resumo da Operação:\n";
    $resumo .= "?? Total de PINs 'E-Prepag' atualizados: " . $qtdEpp . "\n";
    $resumo .= "?? Valor Total das Vendas: R$ " . number_format($valorTotal, 2, ',', '.') . "\n";
    $resumo .= "?? Valor de Desconto Total: R$ " . number_format($valorDescontoTotal, 2, ',', '.') . "\n";
    $resumo .= "?? Valor de Comissão Total: R$ " . number_format($valorComissaoTotal, 2, ',', '.') . "\n";

    file_put_contents('resumo_operacao_epp' . $inicio . '_' . $fim . '.txt', $resumo);

    // ?? Criar o CSV de comissões
    $fp = fopen('pins_afetados_epp' . $inicio . '_' . $fim . '.csv', 'w');
    fputcsv($fp, ['PIN', 'Valor Venda', 'Valor Desconto', 'Comissão']);
    foreach ($pinsParaVenda as $linha) {
        fputcsv($fp, [
            $linha['pin_codigo'],
            $linha['valor_venda'] ? $linha['valor_venda'] : '',
            $linha['valor_desconto'] ? $linha['valor_desconto'] : '',
            $linha['comissao'] ? $linha['comissao'] : '',
        ]);
    }
    fclose($fp);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Erro: " . $e->getMessage());
}

echo "Processo concluído! " . count($pinsEppAtualizados) . " PINs de E-Prepag foram alterados e salvos no CSV.";
?>
