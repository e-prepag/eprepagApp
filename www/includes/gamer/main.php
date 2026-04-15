<?php
// error_reporting(E_ALL & ~E_NOTICE);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
ob_start();
@session_start();

require_once $raiz_do_projeto . "includes/gamer/chave.php";
require_once $raiz_do_projeto . "includes/gamer/AES.class.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "includes/gamer/constantesPinEpp.php";
require_once $raiz_do_projeto . "includes/gamer/functions.php";
require_once $raiz_do_projeto . "includes/gamer/functions_vendaGames.php";
require_once $raiz_do_projeto . "includes/gamer/inc_instrucoes.php";
require_once $raiz_do_projeto . "includes/gamer/inc_sanitize.php";
require_once $raiz_do_projeto . "includes/gamer/inc_functions_epp.php";
require_once $raiz_do_projeto . "class/gamer/classPromocoes.php";
require_once $raiz_do_projeto . "class/gamer/classIntegracao.php";
require_once $raiz_do_projeto . "class/gamer/classProduto.php";
require_once $raiz_do_projeto . "class/gamer/classProdutoModelo.php";
require_once $raiz_do_projeto . "class/gamer/classGamesUsuario.php";
require_once $raiz_do_projeto . "class/gamer/classConversionPINsEPP.php";
require_once $raiz_do_projeto . "includes/gamer/controleSessao.php";


?>