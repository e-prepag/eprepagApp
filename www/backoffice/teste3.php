<?php
// Configura��es iniciais para depura��o
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// In�cio do script
echo "In�cio do script longo.<br>";
flush();

// **FASE 1: Atrasos Sequenciais**
$delay_steps = [30, 60, 90]; // Atrasos em segundos
foreach ($delay_steps as $delay) {
    echo "Aguardando $delay segundos...<br>";
    flush();
    sleep($delay); // Atraso individual
}

// **FASE 2: Processamento Intensivo Simulado**
$total_iterations = 5000000; // N�mero de itera��es
echo "Iniciando processamento intensivo com $total_iterations itera��es...<br>";
flush();
for ($i = 0; $i < $total_iterations; $i++) {
    if ($i % 500000 == 0) { // Atualiza a cada 500.000 itera��es
        echo "Processando: $i de $total_iterations<br>";
        flush();
    }
}

// **FASE 3: Atraso Adicional**
$final_delay = 120; // Atraso final de 120 segundos
echo "Atraso final de $final_delay segundos...<br>";
flush();
sleep($final_delay);

// Conclus�o
echo "Fim do script ap�s longos atrasos e processamento intensivo.<br>";
flush();
?>
