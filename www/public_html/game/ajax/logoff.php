<?php 

session_start();

require_once "../../../includes/constantes.php";
require_once DIR_CLASS."util/Util.class.php";    

if(Util::isAjaxRequest()){
    
    require_once DIR_INCS."main.php";
    require_once DIR_INCS."gamer/main.php";
    
    //Log na base
    usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['LOGOUT'], null, null);

    //Logout
    cancelarSessao();

    print 1;
    
}