<?php
/**
	Armazena as taxas de conversão para PINs de parceiros como pares de valores nominal/real

		get_ValorEPPCash($id, $valor)
		get_Valor($id, $valor_eppcash)
		get_Rate($id, $valor)


*/
class ConversionPINsEPP {
	private static $a_taxas = array(
			// partner ID
			'E' => array(
							'name' => 'EPPCash',
							'taxas' => array(

											array(
												'data_inicio' => '',
												'data_fim' => '',
												'valor' => 1,
												'valor_eppcash' => 100,
											),
							),
			),

	);

	public function __construct() {
	}

	/**
		$id				- ID do parceiro 
		$valor			- valor do PIN

		return			- valor EPPCash para o valor do Parceiro
	*/ 
	public function get_ValorEPPCash($id, $valor) {
//echo "id: '$id', valor: '$valor'<br>\n";
		return ($valor * self::$a_taxas[$id]['taxas'][0]['valor_eppcash']);
	}

	/**
		$id				- ID do parceiro 
		$valor_epp		- valor EPPCash do PIN

		return			- valor para o valor EPPCash do Parceiro
	*/ 
	public function get_Valor($id, $valor_epp) {
//echo "id: '$id', valor: '$valor'<br>\n";
		if(!array_key_exists ($id, self::$a_taxas)) {
			return 0;
		}
		if(!($valor_epp>0)) {
			return 0;
		}
		return ($valor_epp / self::$a_taxas[$id]['taxas'][0]['valor_eppcash']);
	}

	/**
		$id				- ID do parceiro 
		$valor	- valor nominal do PIN

		return			- taxa de desconto no PIN 
	*/ 
	public function get_Rate($id, $valor) {
		return (self::$a_taxas[$id]['taxas'][0]['valor_eppcash']/self::$a_taxas[$id]['taxas'][0]['valor']);
	}


}//end class ConversionPINs
?>