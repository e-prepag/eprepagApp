<?php
require_once __DIR__ . "/../../class/classGerarEFinanceira.php";

function compararDatas($data_inicial, $data_final) {
	if ($data_inicial == "" || $data_final == "") {
		return 0;
	}
    try {
        $d1 = new DateTime($data_inicial);
        $d2 = new DateTime($data_final);

        if ($d1 < $d2) {
            return 1;   // data inicial menor (normal)
        } else {
            return -1;  // data inicial maior ou igual
        }
    } catch (Exception $e) {
        return 0; // erro na data
    }
}
function gerarXmlMovimentacao($data_inicial, $data_final, $tipo_cliente)
{
	if(compararDatas($data_inicial, $data_final) < 1){
		return [];
	}

	$efinanceira = new GerarEFinanceira();

	$movimentacoes = $efinanceira->gerarMovimentacaoFinanceiraCompleta($data_inicial, $data_final);

	$xmls = $efinanceira->gerarLotesMovsFinanceira($movimentacoes);

	return $xmls;
}

