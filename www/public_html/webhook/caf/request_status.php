<?php

require_once "/www/class/classCAF.php";

$dados = file_get_contents('php://input');
$infomacoesRecebidas = json_decode($dados);



$caf = new ClassCAF();
$verificacao = $caf->updateOnboarding($infomacoesRecebidas->onboardingId, json_encode($infomacoesRecebidas));

?>