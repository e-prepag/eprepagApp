<?php
// 2025-01-09 13:18:36
//Vetor que possui o status ativado e desativado
$vetorHabilita = array(
                        "0" => "Desativado",
                        "1" => "Ativado"
                        );
						
$vetoropcao = array(
						"blupay" => "Casa do Crédito",
						"cielo" => "Cielo"
			 );

$vetortroca = array(
						"a" => "Ativa",
						"i" => "Inativa"
			 );

// Constantes que definem se o Pagamento se está 1 => Ativado ou 0 => Desativado
define("PAGAMENTO_BRADESCO",0);

define("PAGAMENTO_BANCO_BRASIL",1);

define("PAGAMENTO_ITAU",0);

define("PAGAMENTO_BOLETO",1);

define("PAGAMENTO_EPREPAG_CASH",1);

define("PAGAMENTO_CIELO",0);
    
define("PAGAMENTO_PIX",0);

define("PAGAMENTO_PIX_PROVEDOR", "blupay");

define("PAGAMENTO_PIX_CHAVEAMENTO", "i");

?>