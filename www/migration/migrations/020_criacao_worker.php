<?php

return function (PDO $pdo) {
    $pdo->exec("CREATE TABLE fila_tarefas_background (
    id SERIAL PRIMARY KEY,
    tipo_tarefa VARCHAR(50) NOT NULL,
    parametros TEXT, 
    status VARCHAR(20) DEFAULT 'PENDENTE', 
    caminho_arquivo VARCHAR(255),
    mensagem_erro TEXT,
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_inicio_processamento TIMESTAMP,
    data_conclusao TIMESTAMP
);");
};
