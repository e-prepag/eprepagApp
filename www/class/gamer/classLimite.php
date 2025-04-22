<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//comentar os require abaixo
//require_once("classPrincipal.php");
//require_once("functions_pagto.php");

//session_start();
//$teste = unserialize($_SESSION['usuarioGames_ser']);
//echo "<pre>".print_r($teste,true)."</pre>";

@define('CONST_PAGAMENTOS_CIELO_HABILITADOS', 1);

class Limite {
    
    private $CieloHabilitado;
    private $iforma;
	private $idusuario;
	private $valor;
	private $valor_min;
	private $valor_max;
	private $taxa;
	private $condicao;
	private $riscototaldiario;
	private $riscopagamentodiario;
	private $periodo_data_anterior;
	private $periodo_considerado;
	private $opr_codigo= array();
	private $aIsLogin_pagamento= array();
	private $ip_cliente;

	// Limites pagamentos com cartão através da Cielo
	private $risco_cielo_total_day = 700;
	private $risco_cielo_npags_day = 20;

	private $risco_cielo_total_week = 100;
	private $risco_cielo_npags_week = 2;

	private $risco_cielo_total_month = 400;
	private $risco_cielo_npags_month = 20;

	private $risco_cielo_total_quarter = 1000;
	private $risco_cielo_npags_quarter = 20;


	private $risco_cielo_valor_min_para_taxa = 0;
	private $risco_cielo_valor_min = 3;
	private $risco_cielo_valor_max = 90;
	private $risco_cielo_valor_max_assumem_chargeback = 700;

	private $intervalo_para_vendas_em_aberto = 120;		// em minutos
	private $n_vendas_em_aberto = 6;					// n vendas em aberto 

	private	$usuarioGames;


	// Fim Limites pagamentos com cartão através da Cielo

	// Black List de IPs
	private $ips_black_list = array(
									"189.38.238.2", 
									// "189.38.238.205",		//EPP 
									);
	
	// White List de Publishers que utilizam pagamento
	// Este vetor recebe carga no metodo $this->cargaPublishersWhiteList_primeiraversao
	private $publishers_white_list = array();
	
	function __construct($iforma = null,$idusuario = null,$valor = null, $carrinho = null, $periodo_considerado = null) {
		/*
		================ Instruções dos parâmetros do construtor:
		$iforma :...............Indica a forma de pagamento escolhida indicado por caracter
		$idusuario:.............Trata-se do id do usuário que esta executando a compra
		$valor:.................É o valor total da compra
		$carrinho:..............É um vetor contendo os opr_codigo dos produtos presentes na venda. 
		$periodo_considerado:...É o periodo utilizado na totalização para se chegar no volume a ser comprado com constantes de risco cadastrado no início da classe.
				valores:	'day', 'week', 'month', 'quarter', 'year'
		============================================================================================== 
		*/

		$this->usuarioGames = unserialize($_SESSION['usuarioGames_ser']);

		$carrinho_operadoras = converte_carrinho_em_operadoras($carrinho);
//echo "carrinho: <pre>".print_r($carrinho,true)."</pre>";
//echo "carrinho_operadoras: <pre>".print_r($carrinho_operadoras,true)."</pre>";

		$this->CieloHabilitado = CONST_PAGAMENTOS_CIELO_HABILITADOS;
		$this->cargaPublishersWhiteList_primeiraversao();
		$this->setIForma($iforma);
		$this->setIdUsuario($idusuario);
		$this->setValor($valor);
		$this->setCondicoes($this->getIForma());
		$this->setRiscoTotalDiario($this->getIForma());
		$this->setRiscoPagamentoDiario($this->getIForma());
		$this->setArrayIsLoginPagamento($this->getIForma());
		$this->setOprCodigo($carrinho_operadoras);
		$this->setValorMinMax($this->getIForma());
		$this->setTaxa($this->getIForma());
		$this->setPeriodoConsiderado($periodo_considerado);
		$this->setIPCliente($periodo_considerado);

	} 
    
	// Metodo que carrega as operadoras para os meios de pagamentos - Primeira versão
	//	 [Bilagames, Stardoll, Webzen, Softnyx, Axeso5]	38, 37, 34, 42, 44
	function cargaPublishersWhiteList_primeiraversao() {

		// Aplica esta lista de operadoras/limites (Habbo entra para usar uma operadora de PIN nos testes)
		$this->publishers_white_list = array(
				'23' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							),
				'34' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max,
							),
				'38' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max,
							),
				'42' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max,
							),
				'45' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							),
				'61' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							),
				'83' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							),
				'90' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max,
							),
				'92' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							),
				'96' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							),
				'103' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max_assumem_chargeback,
							),
				'113' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max,
							),
                                '120' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max,
							),
                                '122' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max,
							),
				'124' => array (
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']		=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']	=> $this->risco_cielo_valor_max,
							$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']	=> $this->risco_cielo_valor_max,
							)
				);
	}
	
	function setCondicoes($iforma){
//    	if(b_IsPagtoCielo($iforma)) 
		{
			// seleciona vg_pagto_tipo apenas para pagamentos Cielo
			$b_apenas_cielo = true;
			$this->condicao = "and (" . getSQLWhereParaVendaPagtoOnline($b_apenas_cielo) . ") ";
//			echo "condicao: '".$this->condicao."'<br>";
		}
//		else {
//			$this->condicao = " and false ";
//		}
    }
	function getCondicoes(){
    	return $this->condicao;
    }
    
	
	function setRiscoTotalDiario($iforma){
    	//if(b_IsPagtoCielo($iforma)) 
		{
			$this->riscototaldiario = $this->risco_cielo_total_day;
		}
//		else {
//			$this->riscototaldiario = 0;
//		}
    }

	function setRiscoPagamentoDiario($iforma){
//    	if(b_IsPagtoCielo($iforma)) 
		{
			$this->riscopagamentodiario = $this->risco_cielo_npags_day;
		}
//		else {
//			$this->riscopagamentodiario = 0;
//		}
    }

	function setArrayIsLoginPagamento($iforma){
//    	if(b_IsPagtoCielo($iforma)) 
		{
			$this->aIsLogin_pagamento = $GLOBALS['aIsLogin_pagamento_Cielo'];
		}
    }
	
    function setValorMinMax($iforma){
            foreach ($this->getOprCodigo() as $opr_codigo => $indice){
                    if (array_key_exists($opr_codigo, $this->publishers_white_list)){
                            $aux = $this->publishers_white_list[$opr_codigo];
                            if (array_key_exists($this->getIForma(), $aux)) {
                                    $this->valor_min = $this->risco_cielo_valor_min;
                                    $this->valor_max = $aux[$this->getIForma()];
                            } else {
                                    $this->valor_min = 0;
                                    $this->valor_max = 0;
                                    break;
                            }
                    }//end if (array_key_exists($opr_codigo, $this->publishers_white_list))
                    else {
                            $this->valor_min = 0;
                            $this->valor_max = 0;
                            break;
                    }//end else
            }//end foreach
    }//end function setValorMinMax
			
	function setTaxa($iforma){
		$this->taxa = 0;
//    	if(b_IsPagtoCielo($iforma)) 
		{
			switch($iforma) {
				case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']:
				case $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']:
					$this->taxa = $GLOBALS['PAGAMENTO_VISA_DEBITO_TAXA'];
					break;
				case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']:
				case $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']:
					$this->taxa = $GLOBALS['PAGAMENTO_VISA_CREDITO_TAXA'];
					break;
				case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']:
				case $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']:
					$this->taxa = $GLOBALS['PAGAMENTO_MASTER_DEBITO_TAXA'];
					break;
				case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']:
				case $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']:
					$this->taxa = $GLOBALS['PAGAMENTO_MASTER_CREDITO_TAXA'];
					break;
				case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']:
				case $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']:
					$this->taxa = $GLOBALS['PAGAMENTO_ELO_DEBITO_TAXA'];
					break;
				case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']:
				case $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']:
					$this->taxa = $GLOBALS['PAGAMENTO_ELO_CREDITO_TAXA'];
					break;
				case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']:
				case $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']:
					$this->taxa = $GLOBALS['PAGAMENTO_DINERS_CREDITO_TAXA'];
					break;
				case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']:
				case $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']:
					$this->taxa = $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_TAXA'];
					break;
			}
			if ($this->taxa <  $this->risco_cielo_valor_min_para_taxa) {
				$this->taxa = $this->risco_cielo_valor_min_para_taxa;
			}
			
		}
//		else {
//			$this->taxa = 0;
//		}
    }
	function getTaxa(){
    	return $this->taxa;
    }
    
	function getIdUsuario(){
    	return $this->idusuario;
    }
    function setIdUsuario($idusuario){
    	$this->idusuario = $idusuario;
    }

	function getOprCodigo(){
    	return $this->opr_codigo;
    }
    function setOprCodigo($opr_codigo){
    	$this->opr_codigo = $opr_codigo;
    }
/*
    function addOprCodigo($opr_codigo){
		if(!is_array($this->opr_codigo)) {
			$this->opr_codigo= array();
		}
		if(isset($this->opr_codigo[$opr_codigo]) && $this->opr_codigo[$opr_codigo] ) {
			$this->opr_codigo[$opr_codigo]++;
		} else {
			$this->opr_codigo[$opr_codigo] = 1;
		}
		ksort($this->opr_codigo);
    }
*/
	function getIForma(){
    	return $this->iforma;
    }
    function setIForma($iforma){
    	$this->iforma = $iforma;
    }

	function getValor(){
    	return $this->valor;
    }
    function setValor($valor){
    	$this->valor = $valor;
    }

	function getPeriodoConsiderado(){
    	return $this->periodo_considerado;
    }
    function setPeriodoConsiderado($periodo_considerado){

		if(($periodo_considerado!="day") && ($periodo_considerado!="week") && ($periodo_considerado!="month") && ($periodo_considerado!="quarter")) {
			$periodo_considerado = "day";
		}
    	$this->periodo_considerado = $periodo_considerado;
		
		// Dados de hoje	
		$id = date("d");
		$im = date("m");
		$iy = date("Y");
		$ih = date("H");
		$ii = date("i");
		$is = date("s");
//		echo "Ontem: ".date("Y-m-d H:i:s", mktime($ih, $ii, $is, $im, $id-1, $iy))."<br>";
//		echo "Semana passada: ".date("Y-m-d H:i:s", mktime($ih, $ii, $is, $im, $id-7, $iy))."<br>";
//		echo "Mês passado: ".date("Y-m-d H:i:s", mktime($ih, $ii, $is, $im-1, $id, $iy))."<br>";
//		echo "trimestre passado: ".date("Y-m-d H:i:s", mktime($ih, $ii, $is, $im-3, $id, $iy))."<br>";

		$d_yesterday = date("Y-m-d H:i:s", mktime($ih, $ii, $is, $im, $id-1, $iy));
		$d_last_week = date("Y-m-d H:i:s", mktime($ih, $ii, $is, $im, $id-7, $iy));
		$d_last_month = date("Y-m-d H:i:s", mktime($ih, $ii, $is, $im-1, $id, $iy));
		$d_last_quarter = date("Y-m-d H:i:s", mktime($ih, $ii, $is, $im-3, $id, $iy));

		switch ($periodo_considerado) {
			case "day":
				$this->periodo_data_anterior = $d_yesterday;
				break;
			case "week":
				$this->periodo_data_anterior = $d_last_week;
				break;
			case "month":
				$this->periodo_data_anterior = $d_last_month;
				break;
			case "quarter":
				$this->periodo_data_anterior = $d_last_quarter;
				break;
			default: // "day"
				$this->periodo_data_anterior = $d_yesterday;
				break;
		}
    }
	
	function getCieloHabilitado() {
		return $this->CieloHabilitado;
	}

	function getIPCliente() {
		return $this->ip_cliente;
	}
	function setIPCliente() {
		$this->ip_cliente = retorna_ip_acesso_new();
	}

	//Conforme esta habilitado abaixo se um unico item do carrinho for de uma operadora que não esta habilitada para a forma de pagamento a compra inteira será abortada. 
	function verificaPublisherHabilitado(&$msg) {
		$resposta = false;
//echo "publishers_white_list: <pre>".print_r($this->publishers_white_list, true)."</pre>";
		foreach ($this->getOprCodigo() as $opr_codigo => $indice){
//			echo "ABCDE - [$opr_codigo => $indice]<br>";
			//Teste se o Publisher Suporta este Pagamento
			if (array_key_exists($opr_codigo, $this->publishers_white_list)){
				$msg .= "Operadora [".$opr_codigo."] Permitida\n";
				$aux = $this->publishers_white_list[$opr_codigo];
//echo "aux: <pre>".print_r($aux, true)."</pre>";

				if (array_key_exists($this->getIForma(), $aux)) {
					$msg .= "Tipo de Pagamento [".$this->getIForma()."] Permitido\n";
					if($aux[$this->getIForma()] >= $this->getValor()) {
						$msg .= "Valor [".$this->getValor()."] Permitido para este Publisher nesta Forma de Pagamento\n";
						$resposta = true;
					} else {
						$msg .= "Valor [".$this->getValor()."] Não Permitido para este Publisher nesta Forma de Pagamento\n";
						$resposta = false;
						break;
					}
				} else {
					$msg .= "Tipo de Pagamento [".$this->getIForma()."] Não Permitido\n";
					$resposta = false;
					break;
				}
			} else {
				$msg .= "Operadora [".$opr_codigo."] Não Permitida\n";
				$resposta = false;
				break;
			}
		}//end foreach
		return $resposta;
	}

	function aplicaRegrasCielo(&$msg, $params){
		$resposta = true;
		$msg = "";

		if($resposta && !$msg) {
			// 0 - Testa se Cielo está habilitado
			if(!($this->getCieloHabilitado()==1)) {
				$msg = "Pagamentos Cielo não estão habilitados\n";
				$resposta = false;
			}
		}

		if($resposta && !$msg) {
			// 1 - Teste se o IP esta na Black List
			if (in_array($this->ip_cliente, $this->ips_black_list)){
				$msg = "IP pertence a Black List";
				$resposta = false;
			}
		}		
		if($resposta && !$msg) {
			// 2 - Testa se usuário está habilitado para Cielo
			$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
//echo "<pre>".print_r($usuarioGames, true)."</pre>";
			if(is_object($usuarioGames)) {
//echo "usuarioGames->getUseCielo(): ".$usuarioGames->getUseCielo()."<br>";
//gravaLog_DebugTMP("==== ug_use_cielo: \n    usuarioGames->getUseCielo(): ".$usuarioGames->getUseCielo()."\n   ".print_r($usuarioGames, true)." \n");

				if($usuarioGames->getUseCielo()!=1) {
					$msg = "Usuário não está habilitado para usar pagamentos Cielo (1) \n	Usuário: ID: ".$usuarioGames->getId().", Nome: ".$usuarioGames->getNome().", Email: ".$usuarioGames->getEmail()."\n";
					$resposta = false;
				}
			} else {
				$msg = "Usuário não está logado\n	Usuário: ID: ".$usuarioGames->getId().", Nome: ".$usuarioGames->getNome().", Email: ".$usuarioGames->getEmail()."\n";
				$resposta = false;
			}
		}
		
		if($resposta && !$msg) {
			// 3 - Testa limites de vendas com se Cielo
			$resposta = $this->getVendasTotalDiario($msg, $params);
		}

		$msg_debug = "Em classLimite.php - aplicaRegrasCielo()\n	resposta: ".(($resposta)?"YES":"nope")."\n	$msg";
//		gravaLog_LimitePagtoOnline($msg_debug);
                
/*
 * ************************* TEMPORÁRIO PARA NOSSOS USUARIOS
 */
if(isset($_SESSION['usuarioGames_ser'])) {
    $users_para_excecao = array(53916,2745,46198);    
    $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
    if(in_array($usuarioGames->getId(),$users_para_excecao)) {
        $resposta = true;
    }
}//end if(isset($_SESSION['usuarioGames_ser'])) 
//FIM do TEMPORÁRIO PARA NOSSOS USUARIOS

		return $resposta;
	}

	function aplicaRegrasCieloNovas(&$msg, $params){
		$resposta = true;
		$msg = "";
		$usuarioGames = null;

		if($resposta && !$msg) {
			// 0 - Testa se Cielo está habilitado
			if(!($this->getCieloHabilitado()==1)) {
				$msg = "Pagamentos Cielo não estão habilitados\n";
				$resposta = false;
			}
		}

		if($resposta && !$msg) {
			// 1 - Teste se o IP esta na Black List
			if (in_array($this->ip_cliente, $this->ips_black_list)){
				$msg = "IP pertence a Black List\n";
				$resposta = false;
			}
		}		
		if($resposta && !$msg) {
			// 2 - Testa se usuário está habilitado para Cielo
			if(isset($_SESSION['usuarioGames_ser'])) {
				$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
			} elseif($this->getIdUsuario()>0) {
				$usuarioGames = UsuarioGames::getUsuarioGamesById($this->getIdUsuario());
			}
//echo "<pre>".print_r($usuarioGames, true)."</pre>";
			if(is_object($usuarioGames)) {
//echo "usuarioGames->getUseCielo(): ".$usuarioGames->getUseCielo()."<br>";
//gravaLog_DebugTMP("==== ug_use_cielo: \n    usuarioGames->getUseCielo(): ".$usuarioGames->getUseCielo()."\n   ".print_r($usuarioGames, true)." \n");

				if($usuarioGames->getUseCielo()!=1) {
					$msg = "Usuário não está habilitado para usar pagamentos Cielo (2)\n	Usuário: ID: ".$usuarioGames->getId().", Nome: ".$usuarioGames->getNome().", Email: ".$usuarioGames->getEmail()."\n";
					$resposta = false;
				}
			} else {
				$msg = "Usuário não está logado\n";
				$resposta = false;
			}
		}
		
		if($resposta && !$msg) {
			// 3 - Testa limites de vendas completas com Cielo
			$resposta = $this->getVendasTotalDiario($msg, $params);
		}			
//gravaLog_LimitePagtoOnline("Teste 3: ".(($resposta)?"YES":"nope")."\n");

		if($resposta && !$msg) {
			// 4 - Teste de disponibilidade de Pagamento para Operadora e Meio de Pagamento
			if (!$this->verificaPublisherHabilitado($msg)) {
				$resposta = false;
			}
		}
//gravaLog_LimitePagtoOnline("Teste 4: ".(($resposta)?"YES":"nope")."\n");

		if($resposta) {
			// 5 - Testa limites de vendas em aberto com Cielo
			$resposta = $this->getVendasTentativas90minutos($msg, $params);
		}			
//gravaLog_LimitePagtoOnline("Teste 5: ".(($resposta)?"YES":"nope")."\n");


		$msg_debug  = str_repeat("T", 80)."\n";
		$msg_debug .= "Em classLimite.php - aplicaRegrasCieloNovas()\n	resposta: ".(($resposta)?"YES":"nope")."\n	$msg\n";
		$msg_debug .= $this->get_debug_table_str();
		$msg_debug .= str_repeat("_", 80)."\n";
		gravaLog_LimitePagtoOnline($msg_debug);
                /*
                 * ************************* Liberando PARA USUARIOS ESPECIFICOS
                 * 555219   =>  CARLOS.CORREA@SMILEGATEWEST.COM = G4BOX - Teste
                 * 46198    =>  JOAO.TREVISAN+066@E-PREPAG.COM
                 * 53916    =>  WAGNER.PLAYER@GMAIL.COM
                 */
                if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                    $users_para_excecao = array(53916,46198,555219);    
                    $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                    if(in_array($usuarioGames->getId(),$users_para_excecao)) {
                        $resposta = true;
                    }
                }//end if(isset($_SESSION['usuarioGames_ser'])) 
                //FIM do Liberando PARA USUARIOS ESPECIFICOS

		return $resposta;

	}

	function getVendasTotalDiario(&$msg,$params){
		$total = 0;
		$resposta = false;

/*
		//Vetor contendo valores
		$params['IP']				= retorna_ip_acesso_new();
		$params['ValorMax']			= $this->riscototaldiario;
		$params['QtdeMax']			= $this->riscopagamentodiario;
		$params['ValorTaxa']		= $this->risco_cielo_valor_min_para_taxa;
		$params['ValorCompraMin']	= $this->risco_cielo_valor_min;
		$params['ValorCompraMax']	= $this->risco_cielo_valor_max;
*/

		/*
		// Se for usuário de testes -> sem restrições
		if(in_array($this->getIdUsuario(), $this->aIsLogin_pagamento)) {
	 		$msg .= "Super Usuario";
			return true;   	
		}
		*/

		// Debug
//		$this->setValor(300);

		if (!($this->getValor() >= $this->valor_min && $this->getValor() <= $this->valor_max)) {
			$msg .= "Valor da compra fora do valor Mínimo e Máximo permitido";
			return false;
		}

		//SQL
		$sql = "select sum(vg_pagto_valor_pago) as total from tb_venda_games ";
		$sql .= " where vg_ug_id = " . SQLaddFields($this->getIdUsuario(), "");
		$sql .= " and vg_data_inclusao>='".$this->periodo_data_anterior."' ";	
		$sql .= $this->getCondicoes()." and vg_ultimo_status=5 ";

		$rs = SQLexecuteQuery($sql);
		if($rs && pg_num_rows($rs) > 0){
			$rs_row = pg_fetch_array($rs);
			$total = ($rs_row['total'])?$rs_row['total']:0;
		}
		
		// Debug
//		$total = 300;

//		if($this->usuarioGames) 
		{
//			if($this->usuarioGames->b_IsLogin_Reinaldo()) 
			{
				// for Debug
				$msg_debug = "In getVendasTotalDiario(): \n".
							"total não incluido valor da compra abaixo: ".$total." (periodo: ".$this->periodo_considerado.")\n".
							"idusuario: ".$this->getIdUsuario()."\n".	// , Nome: ".$usuarioGames->getNome().", Email: ".$usuarioGames->getEmail()."
							"valor da compra: ".$this->getValor()."\n".
							"valor taxa: ".$this->getTaxa()."\n".
							"riscototaldiario: ".$this->riscototaldiario."\n".
							"sql: ".$sql."\n";

				gravaLog_LimitePagtoOnline($msg_debug);
			}
		}
		
		if(($total+$this->getValor())<= $this->riscototaldiario) {
			if ($this->getNVendas($msg)) {
				$resposta = true;
			}
			else {
				$msg .= "Ultrapassou a quantidade diária de compras.\n";
			}
		}
		else {
			$msg .= "Ultrapassou o limite diário de compras.\n";
		}

		if($resposta){
			return true;
		}
		else {
			return false;
		}
	}

	function getVendasTentativas90minutos(&$msg, $params){
		$qtde = 0;
		$resposta = false;
		
		/*
		// Se for usuário de testes -> sem restrições
		if(in_array($this->getIdUsuario(), $this->aIsLogin_pagamento)) {
	 		return true;   ??   	
		}
		*/

		//SQL
		$sql = "select count(*) as qtde from tb_venda_games ";
		$sql .= " where vg_ug_id = " . SQLaddFields($this->idusuario, "");
		$sql .= " and vg_data_inclusao>=(CURRENT_TIMESTAMP - interval '".$this->intervalo_para_vendas_em_aberto." minute')";
		$sql .= " and not (vg_ultimo_status = 6) ";	
		$sql .= $this->getCondicoes()." ; ";	// escolhe pagamentos Cielo

		$rs = SQLexecuteQuery($sql);
		if($rs && pg_num_rows($rs) > 0){
			$rs_row = pg_fetch_array($rs);
			$qtde = $rs_row['qtde'];
		}

//echo "qtde: $qtde<br>";

		$resposta = false;
		$risco_cielo_90mins_npags = $this->n_vendas_em_aberto;
//gravaLog_LimitePagtoOnline("Control\n\tqtde: $qtde\trisco_cielo_90mins_npags: $risco_cielo_90mins_npags\n");

//		if($this->usuarioGames) 
		{
//			if($this->usuarioGames->b_IsLogin_Reinaldo()) 
			{
				// for Debug
				$msg_debug = "In getNVendas() 3: \n".
					"	qtde: $qtde; \n	risco_cielo_90mins_npags: $risco_cielo_90mins_npags; \n	periodo_considerado: '90mins'\n".
					"	qtde não incluindo esta compra: ".$rs_row['qtde']."\n".
					"	idusuario: ".$this->getIdUsuario()."\n".		// , Nome: ".$usuarioGames->getNome().", Email: ".$usuarioGames->getEmail()."
					"	sql: ".$sql."\n";
				gravaLog_LimitePagtoOnline($msg_debug);
			}
		}

		if($qtde<$risco_cielo_90mins_npags) {
			$msg .= "N Tentativas nos últimos ".$this->intervalo_para_vendas_em_aberto." minutos [$qtde <= $risco_cielo_90mins_npags] => Permitido\n";
			$resposta = true;
		} else {
			$msg .= "N Tentativas nos últimos ".$this->intervalo_para_vendas_em_aberto." minutos [$qtde > $risco_cielo_90mins_npags] => não Permitido\n";
			$resposta = false;
		}
		
//gravaLog_LimitePagtoOnline("Control final\n\tresposta :".(($resposta)?"YES":"nope")."\n");
		return $resposta;
	}

	function getNVendas($msg){
		
		$qtde = 0;
		$resposta = false;
		
		/*
		// Se for usuário de testes -> sem restrições
		if(in_array($this->getIdUsuario(), $this->aIsLogin_pagamento)) {
	 		return true;   ??   	
		}
		*/

		//SQL
		$sql = "select count(*) as qtde from tb_venda_games ";
		$sql .= " where vg_ug_id = " . SQLaddFields($this->idusuario, "");
		$sql .= " and vg_data_inclusao>='".$this->periodo_data_anterior."' ";
		$sql .= " and vg_ultimo_status=5 ";
		$sql .= $this->getCondicoes()." ; ";	// escolhe pagamentos Cielo

		$rs = SQLexecuteQuery($sql);
		if($rs && pg_num_rows($rs) > 0){
			$rs_row = pg_fetch_array($rs);
			$qtde = $rs_row['qtde'];
		}

		// Dummy
//		$qtde = 30;


//echo "qtde: $qtde<br>";
//echo "this->periodo_considerado: ".$this->periodo_considerado."<br>";


		$resposta = false;
		$risco_cielo_npags = 0;
		switch($this->periodo_considerado) {
			case "day":
				$risco_cielo_npags = $this->risco_cielo_npags_day;
				break;
			case "week":
				$risco_cielo_npags = $this->risco_cielo_npags_week;
				break;
			case "month":
				$risco_cielo_npags = $this->risco_cielo_npags_month;
				break;
			case "quarter":
				$risco_cielo_npags = $this->risco_cielo_npags_quarter;
				break;
			default:
				$risco_cielo_npags = 0;
				break;
		}

//		if($this->usuarioGames) 
		{
//			if($this->usuarioGames->b_IsLogin_Reinaldo()) 
			{
				// for Debug
				$msg_debug = "In getNVendas() 2: \n".
					"	qtde: $qtde; \n	risco_cielo_npags: $risco_cielo_npags; \n	periodo_considerado: '".$this->periodo_considerado."'\n".
					"	qtde não incluindo esta compra: ".$rs_row['qtde']."\n".
					"	idusuario: ".$this->getIdUsuario()."\n".		// , Nome: ".$usuarioGames->getNome().", Email: ".$usuarioGames->getEmail()."
					"	riscopagamentodiario: ".$this->riscopagamentodiario."\n".
					"	sql: ".$sql."\n";
				gravaLog_LimitePagtoOnline($msg_debug);
			}
		}

		if($qtde<$risco_cielo_npags) {
			$resposta = true;
		}
		
		return $resposta;
	}

	function getConfigurationCielo(&$params){

		//Vetor contendo valores
		$params['CIELO_Habilitado']		= CONST_PAGAMENTOS_CIELO_HABILITADOS;
		$params['IP']					= $this->getIPCliente();
		$params['ValorTaxa']			= $this->risco_cielo_valor_min_para_taxa;

		$params['ValorMaxDiario']		= $this->riscototaldiario;
		$params['QtdeMaxDiario']		= $this->riscopagamentodiario;
		$params['ValorMaxSemanal']		= $this->risco_cielo_total_week;
		$params['QtdeMaxSemanal']		= $this->risco_cielo_npags_week;
		
		$params['ValorCompraMin']		= $this->risco_cielo_valor_min;
		$params['ValorCompraMax']		= $this->risco_cielo_valor_max;

		$params['Periodo']				= $this->periodo_considerado;
		$params['PeriodoDataAnterior']	= $this->periodo_data_anterior;
		$params['IPBlackList']			= $this->ips_black_list;
	}

	function get_debug_table() {
		echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
		echo "<tr><td align='left'>CieloHabilitado</td> <td align='left'>".(($this->getCieloHabilitado()==1)?"<font color='blue'>SIM</font>":"<font color='red'>não</font>")."</td></tr>\n";
		echo "<tr><td align='left'>iforma</td> <td align='left'>".$this->iforma."</td></tr>\n";
		echo "<tr><td align='left'>idusuario</td> <td align='left'>".$this->idusuario."</td></tr>\n";
		echo "<tr><td align='left'>valor</td> <td align='left'>".$this->valor."</td></tr>\n";
		echo "<tr><td align='left'>valor_min</td> <td align='left'>".$this->valor_min."</td></tr>\n";
		echo "<tr><td align='left'>valor_max</td> <td align='left'>".$this->valor_max."</td></tr>\n";
		echo "<tr><td align='left'>taxa</td> <td align='left'>".$this->taxa."</td></tr>\n";
		echo "<tr><td align='left'>condicao</td> <td align='left'>".$this->condicao."</td></tr>\n";
		echo "<tr><td align='left'>opr_codigo</td> <td align='left'><pre>".print_r($this->opr_codigo, true)."</pre></td></tr>\n";
		echo "<tr><td align='left'>getIForma()</td> <td align='left'>".$this->getIForma()."</td></tr>\n";
		echo "<tr><td align='left'>periodo_considerado</td> <td align='left'>".$this->periodo_considerado."</td></tr>\n";
		echo "<tr><td align='left'>periodo_data_anterior</td> <td align='left'>".$this->periodo_data_anterior."</td></tr>\n";
		echo "<tr><td align='left'>IP cliente</td> <td align='left'>".$this->getIPCliente()."</td></tr>\n";
		echo "<tr><td align='left'>ips_black_list</td> <td align='left'><pre>".print_r($this->ips_black_list, true)."</pre></td></tr>\n";
		echo "<tr><td align='left'></td> <td align='left'>$</td></tr>\n";
		echo "</table>\n";
	}

	function get_debug_table_str() {
		$str  = "";
		$str .= "	CieloHabilitado :".(($this->getCieloHabilitado()==1)?"SIM":"não")."\n";
		$str .= "	iforma:	".(($this->iforma)?$this->iforma:"  --- EMPTY ---  ")."\n";
		$str .= "	idusuario:	".$this->idusuario."\n";
		$str .= "	valor:	".$this->valor."\n";
		$str .= "	valor_min:	".$this->valor_min."\n";
		$str .= "	valor_max:	".$this->valor_max."\n";
		$str .= "	taxa:	".$this->taxa."\n";
//		$str .= "	condicao:	".$this->condicao."\n";
		$str .= "	opr_codigo:	".print_r($this->opr_codigo, true)."\n";
		$str .= "	getIForma():	".$this->getIForma()."\n";
		$str .= "	periodo_considerado:	".$this->periodo_considerado."\n";
		$str .= "	periodo_data_anterior:	".$this->periodo_data_anterior."\n";
		$str .= "	IP cliente:	".$this->getIPCliente()."\n";
		$str .= "	ips_black_list:	".print_r($this->ips_black_list, true)."\n";
		$str .= "	publishers_white_list:	".print_r($this->publishers_white_list, true)."\n";
		$str .= "\n";
		return $str;
	}


/*
	Primeira versão alterada em função da reunião com o Reynaldo

	function getPrimeiraVendaGamers(&$cielo_pan) {
		$sql = "select count(*) as total,cielo_pan from tb_venda_games vg ";
		$sql .= " inner join tb_pag_compras pc on (vg.vg_id = pc.idvenda) ";
		$sql .= " inner join codigo_confirmacao cc on (vg.vg_id = cc.cc_vg_id) ";
		$sql .= " where vg_ug_id = " . SQLaddFields($this->getIdUsuario(), "");
		$sql .= " and vg_pagto_tipo=".getCodigoNumericoParaPagto($this->getIForma())." and vg_ultimo_status=5 and cc_status='0' ";
		$sql .= " group by cielo_pan ";
		$sql .= " having count(*) =1 ";
		//echo $sql."<br>";
		$rs = SQLexecuteQuery($sql);
		if($rs && pg_num_rows($rs)== 1){
			//Teste se já foi digitado com sucesso o Token para a primeira venda
			//$sql = "select * from codigo_confirmacao where cc_tipo_usuario='M' and cc_ug_id=".SQLaddFields($this->getIdUsuario(), "")." and cc_tipo_pagamento='".$this->getIForma()."' and cc_status='0';";
			//$rs_token_verify = SQLexecuteQuery($sql);
			//echo $sql;
			//if(pg_num_rows($rs_token_verify) == 1) {
				$rs_row = pg_fetch_array($rs);
				$cielo_pan = $rs_row['cielo_pan'];
				return true;
			//}
			//else {
			//	return false;
			//}
		}
		else {
			return false;
		}
	}//end function getPrimeiraVendaGamers()
*/
	function getPrimeiraVendaGamers(&$cielo_pan,&$data_exibicao) {
		$sql = "select cielo_pan from tb_venda_games vg ";
		$sql .= " inner join tb_pag_compras pc on (vg.vg_id = pc.idvenda) ";
		$sql .= " where vg_ug_id = " . SQLaddFields($this->getIdUsuario(), "");
		$sql .= " and vg_pagto_tipo=".getCodigoNumericoParaPagto($this->getIForma())." and vg_ultimo_status=5 ";
		$sql .= " and cielo_pan IS NOT NULL ";
		$sql .= " group by cielo_pan ";
		//echo $sql."<br>";
		$rs = SQLexecuteQuery($sql);
		if($rs && pg_num_rows($rs) > 0){
			while ($rs_row = pg_fetch_array($rs)) {
				$cielo_pan = $rs_row['cielo_pan'];
				$sql = "select to_char(cc_aux.cc_data_inclusao,'DD/MM/YYYY HH24:MI') as data_exibicao,* from codigo_confirmacao cc_aux where cc_aux.cc_data_inclusao = ( ";
				$sql .= "select min(cc_data_inclusao) from tb_venda_games vg ";
				$sql .= " inner join tb_pag_compras pc on (vg.vg_id = pc.idvenda) ";
				$sql .= " inner join codigo_confirmacao cc on (vg.vg_id = cc.cc_vg_id) ";
				$sql .= " where vg_ug_id = " . SQLaddFields($this->getIdUsuario(), "");
				$sql .= " and vg_pagto_tipo=".getCodigoNumericoParaPagto($this->getIForma())." and vg_ultimo_status=5 ";
				$sql .= " and cielo_pan = '".$cielo_pan."') ";
				$rs_token_verify = SQLexecuteQuery($sql);
				//echo $sql;
				if($rs_token_verify) {
					while ($rs_token_verify_row = pg_fetch_array($rs_token_verify)) {
						if ($rs_token_verify_row['cc_status'] == '0') {
							$data_exibicao = $rs_token_verify_row['data_exibicao'];
							return true;
						}//end if ($rs_token_verify_row['cc_status'] == '0')
					}//end while
				}//end 	if($rs_token_verify)
				else {  
					return false;
				}
			}//end while
			return false;
		}//end if($rs && pg_num_rows($rs) > 0)
		else {
			return false;
		}
	}//end function getPrimeiraVendaGamers()

	function setStatusTokenUtilizado($cielo_pan,$token) {
		$sql = "select * from tb_venda_games vg ";
		$sql .= " inner join tb_pag_compras pc on (vg.vg_id = pc.idvenda) ";
		$sql .= " inner join codigo_confirmacao cc on (vg.vg_id = cc.cc_vg_id) ";
		$sql .= " where vg_ug_id = " . SQLaddFields($this->getIdUsuario(), "");
		$sql .= " and vg_pagto_tipo =".getCodigoNumericoParaPagto($this->getIForma())." and vg_ultimo_status = 5 and cc_status = '0' ";
		$sql .= " and cielo_pan = '".$cielo_pan."'";
		sleep(1);
		//echo $sql."<br>";
		$rs = SQLexecuteQuery($sql);
		if($rs && pg_num_rows($rs)== 1){
			$rs_row = pg_fetch_array($rs);
			$cc_id_aux = $rs_row['cc_id'];
			$cc_codigo_aux = $rs_row['cc_codigo'];
			//echo $cc_id_aux."<br>".$cc_codigo_aux."<br>";
			if($cc_codigo_aux == $token) {
				$sql = "update codigo_confirmacao ";
				$sql .= " set cc_status = '1', cc_data_confirmado = NOW() ";
				$sql .= " where cc_ug_id = " . SQLaddFields($this->getIdUsuario(), "");
				$sql .= " and cc_tipo_pagamento = '".$this->getIForma()."' and cc_id = ".$cc_id_aux." and cc_tipo_usuario = 'M' ";
				//echo $sql."<br>";
				$rs_token_verify = SQLexecuteQuery($sql);
				if($rs_token_verify) {
					return true;
				}
				else {
					return false;
				}
			}//end if($cc_codigo_aux == $token)
			else {
				return false;
			}
		}//end 	if($rs && pg_num_rows($rs)== 1)
		else {
			return false;
		}
	}//end function setStatusTokenUtilizado()

}//end class Limite

//	converte o carrinho de produtos para um carrinho de operadoras
//		estrutura: Array ( [opr_codigo1] => n1; [opr_codigo] => n2; ... ) onde os n1, n2... são o total de pins de cada operadora presente na venda
function converte_carrinho_em_operadoras($carrinho) {
	
	$s_carrinho = "";
	$s_carrinho_ogp_id = "";
        $carrinho_operadoras = array();
        if(count($carrinho) > 0) {
            foreach($carrinho as  $key => $val) {
                if($key !== $GLOBALS['NO_HAVE']) {
                    $s_carrinho .= (($s_carrinho)?",":"")."$key";
                }
                else {
                    $s_carrinho_ogp_id .= (($s_carrinho_ogp_id)?",":"").key($val);
                }
            }
        }//end if(count($carrinho) > 0)
	if($s_carrinho != "" || $s_carrinho_ogp_id != "") {
		$sql = "select ogp_opr_codigo, ogpm_id
		from tb_operadora_games_produto ogp
			inner join tb_operadora_games_produto_modelo ogpm on ogp.ogp_id = ogpm.ogpm_ogp_id 
		where ".(!empty($s_carrinho)?" ogpm_id IN (".$s_carrinho.")":"").(!empty($s_carrinho_ogp_id)?(!empty($s_carrinho)?" OR":"")." ogp.ogp_id IN (".$s_carrinho_ogp_id.")":"")."
		order by ogp_opr_codigo";
	//echo "$sql<br>";

		$rs = SQLexecuteQuery($sql);
		if($rs){
			while ($rs_row = pg_fetch_array($rs)){
				$opr_codigo = $rs_row['ogp_opr_codigo'];
				$ogpm_id	= $rs_row['ogpm_id'];
				$n			= $carrinho[$ogpm_id];

				// O carrinho pode ter mais de um produto da mesma operadora
				if(isset($carrinho_operadoras[$opr_codigo])) {
					$carrinho_operadoras[$opr_codigo] += $n;
				} else {
					$carrinho_operadoras[$opr_codigo] = $n;
				}
			}
		} else {
	//		echo "Nada encontrado<br>";
		}
		ksort($carrinho_operadoras);
	}//end if($s_carrinho != "")

	return $carrinho_operadoras;
}

/*
//exemplo de implementação
//para funcionar este exemplo deve-se fazer uma compra na loja em outra aba até o momento de seleção do meio de pagamento.
//entaun somente após deverá executar este.

$valor_compra = 0;
$opr_codigo   = array();
$carrinho = $_SESSION['carrinho'];
if(!$carrinho || count($carrinho) == 0){		
	echo "Carrinho Vazio\n";
} else {
	foreach ($carrinho as $modeloId => $qtde){

		$qtde = intval($qtde);
		$rs = null;
		$filtro['ogpm_ativo'] = 1;
		$filtro['ogpm_id'] = $modeloId;
		$filtro['com_produto'] = true;
		$ret = ProdutoModelo::obter($filtro, null, $rs);
		if($rs && pg_num_rows($rs) != 0){
			$rs_row = pg_fetch_array($rs);
			//echo "<pre>".print_r($rs_row,true)."</pre>";
			//echo "<br>opr_codigo:".$rs_row['ogp_opr_codigo']."<br>";
			$opr_codigo[]	= $rs_row['ogp_opr_codigo'];
			$valor_compra	+= $rs_row['ogpm_valor'];
		}
	}
}
*/
//echo "<pre>".print_r($opr_codigo,true)."</pre>";
/*			
$teste = new Limite('G',$teste->ug_id,$valor_compra,$opr_codigo,7);

echo "Valor: ".$teste->getValor();
//echo "<br>ID: ".$teste->getIdUsuario();
//echo "<br>getCondicoes(): ".$teste->getCondicoes();
$mensagem = "";
if ($teste->getVendasTotalDiario($mensagem,$val)) {
	echo "<br>Transação OK\nMensagem: \n".$mensagem;
}
else {
	echo "<br>Transação NAUN OK\nMensagem: \n".$mensagem;
}
echo "<pre>".print_r($val,true)."</pre>";
//echo "<pre>".print_r($GLOBALS['_SESSION'],true)."</pre>";
*/

?>
