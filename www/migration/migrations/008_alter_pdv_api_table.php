<?php

return function (PDO $pdo) {
    $pdo->exec("ALTER table pdv_api_ip 
                    add column created_date date not null default now(),
                    add column active bool not null default true,
                    add column bko_user varchar(20);");
};