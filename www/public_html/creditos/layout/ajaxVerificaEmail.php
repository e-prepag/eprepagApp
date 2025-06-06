<?php

require_once "../../../includes/constantes.php";
require_once "../../../class/pdv/classGamesUsuario.php";
require_once RAIZ_DO_PROJETO . 'db/connect.php';
require_once RAIZ_DO_PROJETO . 'db/ConnectionPDO.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obter o email do POST
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $instUsuarioGames = new UsuarioGames;
    echo json_encode(array('valid' => !$instUsuarioGames->existeEmail($email)));

}