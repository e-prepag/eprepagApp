<?php
if(!function_exists('b_IsPagtoOnline')){
    function b_IsPagtoOnline($iforma) {
            if( 
                            ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || 
                            ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || 
                            ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) || 
                            ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']) || 
                            ($iforma==$GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']) || 
                            ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']) || 
                            ($iforma==$GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']) || 
                            ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']) || 
                            ($iforma==$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']) || 
                            ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']) || 
                            ($iforma==$GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']) || 
                            ($iforma==$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']) || 
                            ($iforma==$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'])|| 
                            ($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']) || 
                            ($iforma==$GLOBALS['PAGAMENTO_PIX_NUMERIC']) || 
                            b_IsPagtoCielo($iforma) 
                    ) {
                    return true;
            } 
            return false;
    }
}
$aIsLogin_pagamento_Cielo = Array(
	53916,
);

	
function b_IsPagtoCielo($iforma)  {
	return(b_IsPagtoCieloNumeric($iforma) || b_IsPagtoCieloAlpha($iforma));
}

function b_IsPagtoCieloNumeric($iforma)  {
	if (
		($iforma==$GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']) || 
		($iforma==$GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']) || 
		($iforma==$GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']) || 
		($iforma==$GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']) || 
		($iforma==$GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']) || 
		($iforma==$GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']) || 
		($iforma==$GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']) || 
		($iforma==$GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC'])
		)
	{
		return true;
	}
	else {
		return false;
	}
}

function b_IsPagtoCieloAlpha($iforma)  {
	if (
		($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']) || 
		($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']) || 
		($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']) || 
		($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']) || 
		($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']) || 
		($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']) || 
		($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']) || 
		($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO'])
		)
	{
		return true;
	}
	else {
		return false;
	}
}


function b_IsPagtoBoletoDeposito($iforma) {
	if( 
		($iforma==$GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']) || 
		($iforma==$GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'])
		) {
		return true;
	} 
	return false;
}

if(!function_exists('getTaxaPagtoOnline')){
    function getTaxaPagtoOnline($iforma) {

            $taxa = 0;
            switch($iforma) {
                    case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
                            $taxa = $GLOBALS['BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
                            $taxa = $GLOBALS['BRADESCO_DEBITO_EM_CONTA_TAXA_ADICIONAL'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
                            $taxa = $GLOBALS['BANCO_DO_BRASIL_TAXA_DE_SERVICO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:
                    case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:
                            $taxa = $GLOBALS['BANCO_ITAU_TAXA_DE_SERVICO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']:
                    case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_HIPAY_ONLINE_TAXA'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:
                    case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_PIN_EPP_TAXA'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']:
                    case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_TAXA'];
                            break;
                    case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:
                    case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:
                            $taxa = $GLOBALS['BANCO_EPP_TAXA_DE_SERVICO'];
                            break;

                    // Pagamentos CIELO 
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']:
                    case $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_VISA_DEBITO_TAXA'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']:
                    case $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_VISA_CREDITO_TAXA'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']:
                    case $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_MASTER_DEBITO_TAXA'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']:
                    case $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_MASTER_CREDITO_TAXA'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']:
                    case $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_ELO_DEBITO_TAXA'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']:
                    case $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_ELO_CREDITO_TAXA'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']:
                    case $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_DINERS_CREDITO_TAXA'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']:
                    case $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_TAXA'];
                            break;
                        
                    // PIX
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']:
                    case $GLOBALS['PAGAMENTO_PIX_NUMERIC']:
                            $taxa = $GLOBALS['PAGAMENTO_PIX_TAXA'];
                            break;
                        
            }
            return $taxa;
    }
    
}
function getDescricaoPagtoOnline($iforma0) {

	$iforma = getCodigoCaracterParaPagto($iforma0);

	$descricao = (
			(array_key_exists($iforma, $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO']))
			?$GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$iforma]
			:
				(($iforma == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE'])
						? $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO_EPP']
						:"DEFAULT - iforma: '$iforma'\n"
				)		
	);

	return $descricao;
}

if(!function_exists('getBcoCodigo')){
    function getBcoCodigo($iforma) {

            $bco_codigo = "000";
            switch($iforma) {
                    case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
                            $bco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
                            $bco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
                            $bco_codigo = $GLOBALS['BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:
                    case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:
                            $bco_codigo = $GLOBALS['BOLETO_MONEY_BANCO_ITAU_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:
                    case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:
                            $bco_codigo = $GLOBALS['PAGAMENTO_PIN_EPP_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']:
                    case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:
                            $bco_codigo = $GLOBALS['BOLETO_MONEY_HIPAY_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']:
                    case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:
                            $bco_codigo = $GLOBALS['BOLETO_MONEY_PAYPAL_COD_BANCO'];
                            break;
                    case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:
                    case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:
                            $bco_codigo = $GLOBALS['BOLETO_MONEY_BANCO_EPP_COD_BANCO'];
                            break;

                    // Pagamentos CIELO
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']:
                    case $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']:
                            $bco_codigo = $GLOBALS['PAGAMENTO_VISA_DEBITO_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']:
                    case $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']:
                            $bco_codigo = $GLOBALS['PAGAMENTO_VISA_CREDITO_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']:
                    case $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']:
                            $bco_codigo = $GLOBALS['PAGAMENTO_MASTER_DEBITO_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']:
                    case $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']:
                            $bco_codigo = $GLOBALS['PAGAMENTO_MASTER_CREDITO_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']:
                    case $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']:
                            $bco_codigo = $GLOBALS['PAGAMENTO_ELO_DEBITO_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']:
                    case $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']:
                            $bco_codigo = $GLOBALS['PAGAMENTO_ELO_CREDITO_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']:
                    case $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']:
                            $bco_codigo = $GLOBALS['PAGAMENTO_DINERS_CREDITO_COD_BANCO'];
                            break;
                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']:
                    case $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']:
                            $bco_codigo = $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_COD_BANCO'];
                            break;

                    case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']:
                    case $GLOBALS['PAGAMENTO_PIX_NUMERIC']:
                            $bco_codigo = $GLOBALS['PAGAMENTO_PIX_COD_BANCO'];
                            break;
            }
            return $bco_codigo;
    }
}

// tb_venda_games usa vg_pagto_tipo::smallint
// tb_pag_compras usa iforma::char(1)
function getCodigoNumericoParaPagto($iforma) {

	$numeric_code = "0";
	switch($iforma) {
		case $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']:
		case $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']:
		case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_CREDITO']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
			$numeric_code = $iforma;
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:
		case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:
		case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']:
		case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']:
		case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC'];
			break;
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'];
			break;

		// Pagamentos CIELO
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']:
		case $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']:
		case $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']:
		case $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']:
		case $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']:
		case $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']:
		case $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']:
		case $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']:
		case $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC'];
			break;

		//Ofertas
		case $GLOBALS['FORMAS_PAGAMENTO']['OFERTAS']:
		case $GLOBALS['PAGAMENTO_OFERTAS_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_OFERTAS_NUMERIC'];
			break;

		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MCOIN']:
		case $GLOBALS['PAGAMENTO_MCOIN_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_MCOIN_NUMERIC'];
			break;

		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']:
		case $GLOBALS['PAGAMENTO_PIX_NUMERIC']:
			$numeric_code = $GLOBALS['PAGAMENTO_PIX_NUMERIC'];
			break;

                    
	}
	return $numeric_code;
}

// tb_venda_games usa vg_pagto_tipo::smallint
// tb_pag_compras usa iforma::char(1)
function getCodigoCaracterParaPagto($iforma) {

	$character_code = "";
	switch($iforma) {
		case $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']:
		case $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']:
		case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_CREDITO']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
			$character_code = $iforma;
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:
		case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:
		case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']:
		case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']:
		case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE'];
			break;
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:
			$character_code = $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE'];
			break;

		// Pagamentos CIELO
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']:
		case $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']:
		case $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']:
		case $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']:
		case $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']:
		case $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']:
		case $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']:
		case $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']:
		case $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO'];
			break;

		//Ofertas
		case $GLOBALS['FORMAS_PAGAMENTO']['OFERTAS']:
		case $GLOBALS['PAGAMENTO_OFERTAS_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['OFERTAS'];
			break;

		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MCOIN']:
		case $GLOBALS['PAGAMENTO_MCOIN_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MCOIN'];
			break;

                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']:
		case $GLOBALS['PAGAMENTO_PIX_NUMERIC']:
			$character_code = $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX'];
			break;
	}
	return $character_code;
}

function getBancoCodigo($iforma) {

	$banco_codigo = "";
	switch($iforma) {
		case $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']:
		case $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']:
			$banco_codigo = "?*?";
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_CREDITO']:
			$banco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
			$banco_codigo = $GLOBALS['BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:
		case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:
			$banco_codigo = $GLOBALS['BOLETO_MONEY_BANCO_ITAU_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:
		case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_PIN_EPP_NOME_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']:
		case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:
			$banco_codigo = $GLOBALS['BOLETO_MONEY_HIPAY_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']:
		case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:
			$banco_codigo = $GLOBALS['BOLETO_MONEY_PAYPAL_COD_BANCO'];
			break;
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:
		case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'];
			break;

		// Pagamentos CIELO
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']:
		case $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_VISA_DEBITO_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']:
		case $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_VISA_CREDITO_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']:
		case $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_MASTER_DEBITO_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']:
		case $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_MASTER_CREDITO_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']:
		case $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_ELO_DEBITO_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']:
		case $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_ELO_CREDITO_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']:
		case $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_DINERS_CREDITO_COD_BANCO'];
			break;
		case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']:
		case $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_COD_BANCO'];
			break;

                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']:
		case $GLOBALS['PAGAMENTO_PIX_NUMERIC']:
			$banco_codigo = $GLOBALS['PAGAMENTO_PIX_COD_BANCO'];
			break;

	}
	return $banco_codigo;
}

function getIconeParaPagtoGamer($iforma0) {

	$iforma = getCodigoCaracterParaPagto($iforma0);
    
    if(array_key_exists($iforma, $GLOBALS['FORMAS_PAGAMENTO_ICONES_GAMER'])){
        $icone = $GLOBALS['FORMAS_PAGAMENTO_ICONES_GAMER'][$iforma];
    }else{
        if($iforma == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']){
            $icone = $GLOBALS['FORMAS_PAGAMENTO_ICONES_EPP'];
        }else{
            $icone = "DEFAULT icone - iforma: '$iforma'\n";
        }
    }
    
	return $icone;
}

function getIconeParaPagto($iforma0) {

	$iforma = getCodigoCaracterParaPagto($iforma0);
	$icone = (
				( array_key_exists($iforma, $GLOBALS['FORMAS_PAGAMENTO_ICONES']) )
				?$GLOBALS['FORMAS_PAGAMENTO_ICONES'][$iforma]
				:	(($iforma == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE'])
						? $GLOBALS['FORMAS_PAGAMENTO_ICONES_EPP']
						:"DEFAULT icone - iforma: '$iforma'\n"
					)
			);
	return $icone;

}

// tb_venda_games usa vg_pagto_tipo::smallint
// tb_pag_compras usa iforma::char(1)
function getListaCodigoNumericoParaPagtoOnline() {

	$numeric_code = "";
	$numeric_code .= $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'].",";
	$numeric_code .= $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO'].",";
	$numeric_code .= $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_CREDITO'].",";
	$numeric_code .= $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA'].",";

	$numeric_code .= $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC'].",";

	$numeric_code .= $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC'].",";

	$numeric_code .= $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC'].",";

	$numeric_code .= $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC'].",";

	// Pagamentos CIELO
	$numeric_code .= $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC'].",";
	$numeric_code .= $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC'].",";
	$numeric_code .= $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC'].",";
	$numeric_code .= $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC'].",";
	$numeric_code .= $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC'].",";
	$numeric_code .= $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC'].",";
	$numeric_code .= $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC'].",";
	$numeric_code .= $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC'].",";

	$numeric_code .= $GLOBALS['PAGAMENTO_PIX_NUMERIC'].",";

	$numeric_code .= $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']."";

	return $numeric_code;
}

// tb_venda_games usa vg_pagto_tipo::smallint
// tb_pag_compras usa iforma::char(1)
function getListaCharacterParaPagtoOnline() {

	$character_code = "";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']."',";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']."',";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_CREDITO']."',";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']."',";

	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']."',";

	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']."',";

	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']."',";

	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']."',";

	// Pagamentos CIELO
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']."',";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']."',";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']."',";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']."',";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']."',";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']."',";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']."',";
	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']."',";

	$character_code .= "'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']."',";

	$character_code .= "'".$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']."'";

	return $character_code;
}

function getSQLCodigoNumericoParaPagtoOnline($b_apenas_cielo = null) {

	$sql_numeric_code = "CASE \n";

	// Pagamentos CIELO
	$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']."' THEN ".$GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']." \n";
	$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']."' THEN ".$GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']." \n";
	$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']."' THEN ".$GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']." \n";
	$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']."' THEN ".$GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']." \n";
	$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']."' THEN ".$GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']." \n";
	$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']."' THEN ".$GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']." \n";
	$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']."' THEN ".$GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']." \n";
	$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']."' THEN ".$GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']." \n";
	$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']."' THEN ".$GLOBALS['PAGAMENTO_PIX_NUMERIC']." \n";

	if(!$b_apenas_cielo) {
		$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']."' THEN ".$GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']." \n";
		$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']."' THEN ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." \n";
		$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']."' THEN ".$GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']." \n";
		$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']."' THEN ".$GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']." \n";
		$sql_numeric_code .= "WHEN iforma='".$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']."' THEN ".$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']." \n";
		//Colocado por conta dos depositos de ofertas
		$sql_numeric_code .= "WHEN iforma='".$GLOBALS['FORMAS_PAGAMENTO']['OFERTAS']."' THEN ".$GLOBALS['PAGAMENTO_OFERTAS_NUMERIC']." \n";
		$sql_numeric_code .= "ELSE iforma::int \n";
	}
	$sql_numeric_code .= "END \n";
	return $sql_numeric_code;
}

function getSQLWhereParaPagtoOnline($b_apenas_cielo = null) {

	$sql_numeric_code = " (\n";

	// Pagamentos CIELO
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']."' \n";

	if(!$b_apenas_cielo) {
		$sql_numeric_code .= " OR \n";
		$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']."' OR \n";
		$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']."' OR \n";
		$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']."' OR \n";
		$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']."' OR \n";
		$sql_numeric_code .= "iforma='".$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']."' OR \n";
		$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']."' \n";
	}
	$sql_numeric_code .= ") \n";
	return $sql_numeric_code;
}

function getSQLWhereParaPagtoOnlineConciliacao($b_apenas_cielo = null) {

	$sql_numeric_code = " (\n";

	// Pagamentos CIELO
	$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']."' OR \n";
	$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']."' OR \n";
	$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']."' OR \n";
	$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']."' OR \n";
	$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']."' OR \n";
	$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']."' OR \n";
	$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']."' OR \n";
	$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']."' \n";

	if(!$b_apenas_cielo) {
		$sql_numeric_code .= " OR \n";
		$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']."' OR \n";
		$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']."' OR \n";
		$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']."' OR \n";
		$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']."' OR \n";
		$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']."' OR \n";
		$sql_numeric_code .= "rfcb_tipo_pagamento='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']."' \n";
	}
	$sql_numeric_code .= ") \n";
	return $sql_numeric_code;
}

function getSQLWhereParaVendaPagtoOnline($b_apenas_cielo = null) {

	$sql_numeric_code = " (\n";

	// Pagamentos CIELO
	$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']." OR \n";
	$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']." OR \n";
	$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']." OR \n";
	$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']." OR \n";
	$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']." OR \n";
	$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']." OR \n";
	$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']." OR \n";
	$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']." OR \n";
	$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PIX_NUMERIC']." \n";

	if(!$b_apenas_cielo) {
		$sql_numeric_code .= " OR \n";
		$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']." OR \n";
		$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." OR \n";
		$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']." OR \n";
		$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']." OR \n";
		$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']." OR \n";
		$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']." OR \n";
		$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']." OR \n";
		$sql_numeric_code .= "vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']." \n";
	}
        $sql_numeric_code .= ") \n";
	return $sql_numeric_code;
}

/*
	$b_integracao = true se estamos em integraчуo (vindo de pagamento_int.php serс sempre de integraчуo)
	$lst_opr = lista de operadoras no carrinho
	$iforma1 = forma de pagamento segundo $FORMAS_PAGAMENTO[]

	// Estс incompleto: passou para classIntegracao.php
*/
function b_Bloqueia_FormaPagto_para_Operadora($b_integracao, $lst_opr, $iforma1) {
	$FORMAS_PAGAMENTO_BLOQUEIO_OPERADORA = array(
							'1' => '',
							'2' => '',
							//'3' => '',
							//'4' => '', 
							'5' => '',
							'6' => '', 
							'7' => '', 
							//'8' => '', 
							'9' => '', 
							'A' => '',
							'B' => '',
							'P' => '',
							'E' => '',

							'F' => '',
							'G' => '',
							'H' => '',
							'I' => '',
							'J' => '',
							'K' => '',
							'L' => '',
							'M' => '',

							'R' => '',
							);
	return false;
}

function get_oprs_em_carrinho() {
	$s_oprs = "";
	foreach($GLOBALS['carrinho'] as $key => $val) {
		$opr_codigo = get_oprs_by_produto($key);
		if($s_oprs) $s_oprs .= ", ";
		$s_oprs .= $opr_codigo;
	}
	return $s_oprs;
}

function get_oprs_by_produto($ogpm_id) {


	// Se nуo tem modelos cadastrado -> retorna vazio (caso contrсrio o SQL pode retornar uma lista de produtos)
	if(!$ogpm_id) {
		return 0;
	}

	$sql = "select ogp_opr_codigo from tb_operadora_games_produto ogp ";
	$sql .= "	inner join tb_operadora_games_produto_modelo ogpm on ogp.ogp_id = ogpm.ogpm_ogp_id ";
	$sql .= "where 1=1 ";
	$sql .= "	and ogpm.ogpm_id in ($ogpm_id) ";
	$rs = SQLexecuteQuery($sql);

	$ogp_opr_codigo = "0";
	if($rs && pg_num_rows($rs) != 0){
		$rs_row = pg_fetch_array($rs);
		$ogp_opr_codigo = $rs_row['ogp_opr_codigo'];
	}

	return $ogp_opr_codigo;
}


function getSQLWhereParaPagtoCieloDebito() {
	$sql_numeric_code = " (\n";

	// Pagamentos CIELO
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']."' \n";

	$sql_numeric_code .= ") \n";
	return $sql_numeric_code;
} //end function getSQLWhereParaPagtoCieloDebito()


function getSQLWhereParaPagtoCieloCredito() {
	$sql_numeric_code = " (\n";

	// Pagamentos CIELO
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']."' OR \n";
	$sql_numeric_code .= "iforma='".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']."' \n";

	$sql_numeric_code .= ") \n";
	return $sql_numeric_code;
} //end function getSQLWhereParaPagtoCieloCredito()

?>