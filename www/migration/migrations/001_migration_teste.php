<?php
//Essa � a sintaxe de uma migration simples
//Ela deve retornar uma fun��o que recebe o PDO como par�metro
//Dentro dessa fun��o, voc� pode executar comandos SQL para criar ou modificar tabelas
//Lembre-se de que as migrations devem ser idempotentes, ou seja, podem ser executadas v�rias vezes sem, por�m apenas a primeira execu��o ter� efeito
//Abaixo est� um exemplo de cria��o de uma tabela "teste" com um campo "id" e "nome"
return function(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE teste (
            id SERIAL PRIMARY KEY,
            nome TEXT NOT NULL
        );
    ");
};
