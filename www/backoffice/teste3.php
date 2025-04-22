<?php
// Configurações iniciais para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Início do script
echo "Início do script longo.<br>";
flush();

// **FASE 1: Atrasos Sequenciais**
$delay_steps = [30, 60, 90]; // Atrasos em segundos
foreach ($delay_steps as $delay) {
    echo "Aguardando $delay segundos...<br>";
    flush();
    sleep($delay); // Atraso individual
}

// **FASE 2: Processamento Intensivo Simulado**
$total_iterations = 5000000; // Número de iterações
echo "Iniciando processamento intensivo com $total_iterations iterações...<br>";
flush();
for ($i = 0; $i < $total_iterations; $i++) {
    if ($i % 500000 == 0) { // Atualiza a cada 500.000 iterações
        echo "Processando: $i de $total_iterations<br>";
        flush();
    }
}

// **FASE 3: Atraso Adicional**
$final_delay = 120; // Atraso final de 120 segundos
echo "Atraso final de $final_delay segundos...<br>";
flush();
sleep($final_delay);

// Conclusão
echo "Fim do script após longos atrasos e processamento intensivo.<br>";
flush();
?>
