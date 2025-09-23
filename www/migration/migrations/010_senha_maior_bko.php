<?php

return function (PDO $pdo) {
    $pdo->exec("ALTER TABLE public.usuarios ALTER COLUMN shn_password TYPE varchar(100) USING shn_password::varchar(100);");
};
