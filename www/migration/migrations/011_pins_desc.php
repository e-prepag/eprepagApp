<?php

return function (PDO $pdo) {
    $pdo->exec("ALTER TABLE pins ADD pin_desc TEXT NOT NULL DEFAULT '';");
    $pdo->exec("ALTER TABLE pins_dist ADD pin_desc TEXT NOT NULL DEFAULT '';");
};
