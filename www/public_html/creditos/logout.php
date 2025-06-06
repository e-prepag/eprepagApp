<?php 
require_once "../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";

//Log na base
usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['LOGOUT'], null, null);

//Logout
cancelarSessao();

//Redireciona
$msg = "Deslogado com sucesso!";
$strRedirect = "/creditos/login.php?msg=" . urlencode($msg);
redirect($strRedirect);

?>