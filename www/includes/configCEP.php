<?php
// Constante que define o site de consulta de CEP. Onde (https://viacep.com.br/ws/ = 1) ou (http://cep.republicavirtual.com.br/web_cep.php = 2)
$vetorCEP = array('VIACEP' => 1, 'REPUBLICA_VIRTUAL' => 2);

$vetorCEP_Legenda = array(1 => 'VIACEP', 2 => 'REPUBLICA_VIRTUAL');

define("CONSULTA_CEP", $vetorCEP ['VIACEP']);