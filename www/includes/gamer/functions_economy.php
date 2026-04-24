<?php
// EPP Cash for R$1
function getEPPCash_Rate(): int {
	$eppcash_rate_for_one_real = 100;
	return $eppcash_rate_for_one_real;
}

// Transforma R$ -> EPP Cash
function getEPPCash_from_Currency(float $valor_currency): float {
	$valor_eppcash = $valor_currency*getEPPCash_Rate();
	return $valor_eppcash;
}

// Transforma EPP Cash -> R$
function getCurrency_from_EPPCash(float $valor_eppcash): float {
	$valor_currency = $valor_eppcash/getEPPCash_Rate();
	return $valor_currency;
}
?>