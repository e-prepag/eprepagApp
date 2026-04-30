<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** @var array<string, mixed>|null $pgrow */
$pgrow = $_SESSION['pgrow'] ?? null;

if ($pgrow === null || ($pgrow['bko_autoriza'] ?? '') !== 'S') {
        session_destroy();
        echo "<script>";
        echo "setTimeout('top.location = \'" . (isset($url_user_blocked) ? $url_user_blocked : 'https://' . $_SERVER['HTTP_HOST'] . '/login.php?UserBlocked=1') . "\'', 0);";
        echo "// 5678";
        echo "</script>";
        exit;
} else {
        if (isset($pos_pagina, $pgrow['bko_local_acesso'])) {
                $num = substr((string)$pgrow['bko_local_acesso'], $pos_pagina, 1);
                if ($num != 1) {
                        header("Location: " . (isset($url_user_denied) ? $url_user_denied : 'https://' . $_SERVER['HTTP_HOST'] . '/mensagens/negado.php'));
                        exit;
                }
        }
}

/**
 * @param string $mensagem
 * @return void
 */
function gravaLog_LoginBKO2(string $mensagem): void
{
        //Arquivo
        $file = ($GLOBALS['raiz_do_projeto'] ?? '') . "arquivos_gerados/logs/log_LoginBKO.txt";
        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . PHP_EOL . $mensagem . PHP_EOL;
        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        }
}
