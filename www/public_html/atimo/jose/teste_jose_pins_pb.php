<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

$pdo = ConnectionPDO::getConnection()->getLink();

// ?? Configuração do batch (intervalo de PINs a processar)
$inicio = 0;
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

// ?? Ajusta o batch automaticamente
$totalPins = count($pinsCsv);
if ($inicio >= $totalPins) {
    die("Erro: O valor de início ($inicio) é maior ou igual ao total de PINs ($totalPins).");
}
if ($fim > $totalPins) {
    $fim = $totalPins;
}

$pinsBatch = array_slice($pinsCsv, $inicio, $fim - $inicio);

$pinsPB = [];
foreach ($pinsBatch as $item) {
    if (stripos($item['produto'], 'Point Blank') !== false) {
        $pinsPB[] = $item['pin'];
    }
}

$pdo->beginTransaction();

try {
    $pinsPBAtualizados = [];
    $comissoes = [];
    $valorTotal = 0;
    $valorComissaoTotal = 0;
    $valorDescontoTotal = 0;
    $qtdPointBlank = 0;

    if (!empty($pinsPB)) {
        // ?? Atualiza todos os Point Blank de uma vez e captura os que foram alterados
        $placeholders = implode(',', array_fill(0, count($pinsPB), '?'));
        $sql = "UPDATE pins 
                SET pin_status = '9' 
                WHERE pin_codigo IN ($placeholders) 
                AND pin_status = '6' 
                RETURNING pin_codigo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($pinsPB);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pinsPBAtualizados[] = $row['pin_codigo'];
            $qtdPointBlank++;
            
            // ?? Buscar comissão na tb_dist_venda_games_modelo_pins
            $sqlComissao = "SELECT vgm.vgm_pin_valor, vgm.vgm_perc_desconto 
                            FROM tb_dist_venda_games_modelo vgm
                            JOIN tb_dist_venda_games_modelo_pins vgmp ON vgm.vgm_id = vgmp.vgmp_vgm_id
                            WHERE vgmp.vgmp_pin_codinterno = (SELECT pin_codinterno FROM pins WHERE pin_codigo = ?)";
            $stmtComissao = $pdo->prepare($sqlComissao);
            $stmtComissao->execute([$row['pin_codigo']]);
            
            if ($comissao = $stmtComissao->fetch(PDO::FETCH_ASSOC)) {
                $valorTotal += $comissao['vgm_pin_valor'];
                $valorDescontoTotal += $comissao['vgm_pin_valor']*$comissao['vgm_perc_desconto']/100;
                $valorComissao = $comissao['vgm_pin_valor'] - $comissao['vgm_pin_valor']*$comissao['vgm_perc_desconto']/100;
                $valorComissaoTotal += $valorComissao;

                $comissoes[] = [
                    'pin_codigo' => $row['pin_codigo'],
                    'valor_total' => $comissao['vgm_pin_valor'],
                    'valor_desconto' => $comissao['vgm_pin_valor']*$comissao['vgm_perc_desconto']/100,
                    'valor_comissao' => $valorComissao
                ];
            }
        }
    }

    // ?? Atualiza os Point Blank na tabela pins_dist
    // if (!empty($pinsPBAtualizados)) {
    //     $placeholders = implode(',', array_fill(0, count($pinsPBAtualizados), '?'));
    //     $sql = "UPDATE pins_dist 
    //             SET pin_status = '9' 
    //             WHERE pin_codigo IN ($placeholders)";
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->execute($pinsPBAtualizados);
    // }

    // ?? Salva os PINs modificados no CSV
    if (!empty($pinsPBAtualizados)) {
        $fp = fopen('pins_afetados_pb.csv', 'w');
        fputcsv($fp, ['PIN', 'Valor Total', 'Valor Desconto', 'Comissão']); // Cabeçalho

        foreach ($comissoes as $linha) {
            fputcsv($fp, [
                $linha['pin_codigo'], 
                $linha['valor_total'], 
                $linha['valor_desconto'], 
                $linha['valor_comissao']
            ]);
        }

        fclose($fp);
    }

    // ?? Criar o resumo em um arquivo TXT
    $resumo = "Resumo da Operação:\n";
    $resumo .= "?? Total de PINs 'Point Blank' atualizados: " . $qtdPointBlank . "\n";
    $resumo .= "?? Valor Total das Vendas: R$ " . number_format($valorTotal, 2, ',', '.') . "\n";
    $resumo .= "?? Valor de Desconto Total: R$ " . number_format($valorDescontoTotal, 2, ',', '.') . "\n";
    $resumo .= "?? Valor de Comissão Total: R$ " . number_format($valorComissaoTotal, 2, ',', '.') . "\n";

    file_put_contents('resumo_operacao_pb.txt', $resumo);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Erro: " . $e->getMessage());
}

echo "Processo concluído! " . count($pinsPBAtualizados) . " PINs de Point Blank foram alterados e salvos no CSV.";
?>
