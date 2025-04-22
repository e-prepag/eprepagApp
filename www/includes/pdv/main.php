<?php
//error_reporting(E_ALL & ~E_NOTICE);

ob_start();
@session_start();

require_once $raiz_do_projeto.'includes/pdv/constantes.php';
require_once $raiz_do_projeto.'includes/pdv/functions.php';
require_once $raiz_do_projeto.'class/pdv/classGamesUsuario.php';
require_once $raiz_do_projeto.'class/pdv/classOperadorGamesUsuario.php';
require_once $raiz_do_projeto.'class/pdv/classProduto.php';
require_once $raiz_do_projeto.'class/pdv/classProdutoModelo.php';
require_once $raiz_do_projeto.'class/pdv/classBanner.php';
require_once $raiz_do_projeto.'class/pdv/classBannerRelatorio.php';
require_once $raiz_do_projeto.'includes/pdv/sanitize.php';
require_once $raiz_do_projeto.'includes/pdv/functions_vendaGames.php';
require_once $raiz_do_projeto.'includes/pdv/controleSessao.php';
require_once $raiz_do_projeto.'class/classQuestionarios.php';

?>
