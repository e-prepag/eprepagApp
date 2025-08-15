<?php

return function (PDO $pdo) {
    $pdo->exec("CREATE TABLE pdv_api_ip (
    id SERIAL PRIMARY KEY,
    ug_id INTEGER NOT NULL REFERENCES dist_usuarios_games (ug_id) ON DELETE CASCADE,
    ip_address VARCHAR(15),
    ip_range_ini VARCHAR(15),
    ip_range_end VARCHAR(15),
    ip_range BOOLEAN
);");
};