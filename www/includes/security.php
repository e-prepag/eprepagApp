<?php
if($pgrow['bko_autoriza'] != 'S') {
        session_destroy();
        echo "<script>";
        echo "setTimeout('top.location = \'".(isset($url_user_blocked)?$url_user_blocked:'https://'.$_SERVER['HTTP_HOST'].'/login.php?UserBlocked=1')."\'', 0);";
        echo "// 5678";
        echo "</script>";
        exit;
}
else {
        @$num = substr($pgrow['bko_local_acesso'], $pos_pagina, 1);
        if($num != 1) {
                header("Location: ".(isset($url_user_denied)?$url_user_denied:'https://'.$_SERVER['HTTP_HOST'].'/mensagens/negado.php')."");
                exit;
        }
}

function gravaLog_LoginBKO2($mensagem){
        //Arquivo
        $file = $GLOBALS['raiz_do_projeto'] . "log/log_LoginBKO.txt";
        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] .PHP_EOL . $mensagem . PHP_EOL;
        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 
}

?>	