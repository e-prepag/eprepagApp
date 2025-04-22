<?php
// 2025-03-31 10:32:40
//Vetor que possui o status ativado e desativado
$vetorHabilita = array(
                        "0" => "Desativado",
                        "1" => "Ativado"
                        );
						
$vetoropcao = array(
						"blupay" => "Casa do Crédito",
						"cielo" => "Cielo",
                        "mercadopago" => "Mercado Pago",
                        "asaas"=> "Asaas",
			 );

$vetortroca = array(
						"a" => "Ativa",
						"i" => "Inativa"
			 );

$vetoropcao_boleto = array(
				"bradesco" => "Bradesco",
				"asaas" => "Asaas",
	 );

// Constantes que definem se o Pagamento se está 1 => Ativado ou 0 => Desativado
define("PAGAMENTO_BRADESCO",0);

define("PAGAMENTO_BANCO_BRASIL",1);

define("PAGAMENTO_ITAU",0);

define("PAGAMENTO_BOLETO",1);

define("BANCO_BOLETO", "asaas");

define("PAGAMENTO_EPREPAG_CASH",1);

define("PAGAMENTO_CIELO",0);
    
define("PAGAMENTO_PIX",1);

define("PAGAMENTO_PIX_PROVEDOR", "asaas");

define("PAGAMENTO_PIX_PROVEDOR2", "mercadopago");

define("PAGAMENTO_PIX_CHAVEAMENTO", "a");

define("VALOR_TROCA", 2000);

?>