<?php
//Essa é a sintaxe de uma migration simples
//Ela deve retornar uma função que recebe o PDO como parâmetro
//Dentro dessa função, você pode executar comandos SQL para criar ou modificar tabelas
//Lembre-se de que as migrations devem ser idempotentes, ou seja, podem ser executadas várias vezes sem, porém apenas a primeira execução terá efeito
//Abaixo está um exemplo de criação de uma tabela "teste" com um campo "id" e "nome"
return function(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE teste (
            id SERIAL PRIMARY KEY,
            nome TEXT NOT NULL
        );
    ");
};
