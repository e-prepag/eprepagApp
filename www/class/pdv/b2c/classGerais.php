<?php
/**
 * Class Gerais
 * 
 * Classe que vai tratar as Classes de Uso Geral da Integracao com B2C 
 * 
 * @author Wagner de Miranda
 *
*/

//Classe contendo os dados detalhados da Venda
class vendaDetalhesReq {

	public $dataCadastroVenda;			// dateTime
	public $dataCadastroCancelamento;	// dateTime
	public $cnpjVarejo;					// string
	public $cnpjLoja;					// string
	public $codigoLoja;					// string
	public $codigoGerente;				// string
	public $nomeGerente;				// string
	public $codigoVendedor;				// string
	public $nomeVendedor;				// string
	public $numeroNotaFiscal;			// string
	public $siglaMoedaVenda;			// moeda
	public $dataVenda;					// dateTime
	public $tipoPagamento;				// tipoPagamento
	public $valorParcelaFinanciamento;	// double
	public $numeroParcelasFinanciamento;// int
	public $observacoes;				// string
	public $servicoVenda;				// servicoVenda
	public $produto;					// Produto
	public $cliente;					// Cliente

	function __construct($params) {
		if(!empty($params['dataCadastroVenda']))			$this->dataCadastroVenda			= $params['dataCadastroVenda'];			
		if(!empty($params['dataCadastroCancelamento']))		$this->dataCadastroCancelamento		= $params['dataCadastroCancelamento'];
		if(!empty($params['cnpjVarejo']))					$this->cnpjVarejo					= $params['cnpjVarejo'];	
		if(!empty($params['cnpjLoja']))						$this->cnpjLoja						= $params['cnpjLoja'];	
		if(!empty($params['codigoLoja']))					$this->codigoLoja					= $params['codigoLoja'];	
		if(!empty($params['codigoGerente']))				$this->codigoGerente				= $params['codigoGerente'];	
		if(!empty($params['nomeGerente']))					$this->nomeGerente					= $params['nomeGerente'];	
		if(!empty($params['codigoVendedor']))				$this->codigoVendedor				= $params['codigoVendedor'];	
		if(!empty($params['nomeVendedor']))					$this->nomeVendedor					= $params['nomeVendedor'];		
		if(!empty($params['numeroNotaFiscal']))				$this->numeroNotaFiscal				= $params['numeroNotaFiscal'];	
		if(!empty($params['siglaMoedaVenda']))				$this->siglaMoedaVenda				= $params['siglaMoedaVenda'];		
		if(!empty($params['dataVenda']))					$this->dataVenda					= $params['dataVenda'];				
		if(!empty($params['iforma'])) {
			$tipoPagamento						= new tipoPagamentoReq($params['iforma']);
			$this->tipoPagamento				= $tipoPagamento->tipoPagamento;
		}
		if(!empty($params['valorParcelaFinanciamento']))	$this->valorParcelaFinanciamento	= new SoapVar(number_format($params['valorParcelaFinanciamento'], 2, '.', ''), XSD_DECIMAL);
		if(!empty($params['numeroParcelasFinanciamento']))	$this->numeroParcelasFinanciamento	= $params['numeroParcelasFinanciamento'];
		if(!empty($params['observacoes']))					$this->observacoes					= $params['observacoes'];
		if(!empty($params['servicoVenda']))					$this->servicoVenda					= new servicoVendaDetalhesReq($params['servicoVenda']);
		if(!empty($params['produto']))						$this->produto						= new produtoDetalhesReq($params['produto']);
		if(!empty($params['cliente']))						$this->cliente						= new clienteDetalhesReq($params['cliente']);
	}
} //end class vendaDetalhesReq 

//Classe contendo os dados detalhados do tipoPagamento
class tipoPagamentoReq {

	public $tipoPagamento;	// string
	//Conteudo dos Nomes
	private $cartaoCredito	= B2C_TIPO_PAGAMENTO_CARTAO_CREDITO;
	private $boleto			= B2C_TIPO_PAGAMENTO_BOLETO;
	private $dinheiro		= B2C_TIPO_PAGAMENTO_DINHEIRO;
	private $cheque			= B2C_TIPO_PAGAMENTO_CHEQUE;
	private $cartaoDebito	= B2C_TIPO_PAGAMENTO_CARTAO_DEBITO;
	private $cdc			= B2C_TIPO_PAGAMENTO_CDC;
	private $carne			= B2C_TIPO_PAGAMENTO_CARNE;

	function __construct($iforma) {
		$this->tipoPagamento = $this->getRequestLabel($iforma);
	}

	public function getRequestLabel($iforma) {

		switch($iforma) 
		{
			case $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']:
				return $this->dinheiro; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']:
				return $this->boleto; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD']:
				return $this->cartaoCredito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS']:
				return $this->cartaoCredito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
				return $this->dinheiro; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
				return $this->cartaoDebito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_CREDITO']:
				return $this->cartaoCredito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_EMPRESA']:
				return $this->cartaoDebito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
				return $this->cartaoDebito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:
				return $this->cartaoDebito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']:
				return $this->cartaoDebito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']:
				return $this->cartaoDebito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:
				return $this->cartaoCredito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']:
				return $this->cartaoDebito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']:
				return $this->cartaoCredito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']:
				return $this->cartaoDebito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']:
				return $this->cartaoCredito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']:
				return $this->cartaoDebito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']:
				return $this->cartaoCredito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']:
				return $this->cartaoCredito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']:
				return $this->cartaoCredito; 
				break;
			case $GLOBALS['FORMAS_PAGAMENTO']['OFERTAS']:
				return $this->cartaoDebito; 
				break;
		}//end switch

	}//end function getRequestLabel

}//end class tipoPagamentoReq

//Classe contendo os dados detalhados do Servico Venda
class servicoVendaDetalhesReq {

	public $codigo;				// string
	public $pin;				// string
	public $statusPin;			// status
	public $precoCusto;			// double
	public $precoServico;		// double
	public $garantiaSeguro;		// int
	public $dataVigencia;		// dateTime
	public $prazoVigencia;		// int
	public $pis;				// double
	public $iss;				// double
	public $dataCancelamento;	// dateTime
	public $motivoCancelamento;	// string
	public $valorEstornado;		// double
	
	function __construct($params) {
		if(!empty($params['codigo']))				$this->codigo				= $params['codigo'];			
		if(!empty($params['pin']))					$this->pin					= $params['pin'];		
		if(!empty($params['statusPin']))			$this->statusPin			= $params['statusPin'];			
		if(!empty($params['precoCusto']))			$this->precoCusto			= new SoapVar(number_format($params['precoCusto'], 2, '.', ''), XSD_DECIMAL);
		if(!empty($params['precoServico']))			$this->precoServico			= new SoapVar(number_format($params['precoServico'], 2, '.', ''), XSD_DECIMAL);		
		if(!empty($params['garantiaSeguro']))		$this->garantiaSeguro		= $params['garantiaSeguro'];		
		if(!empty($params['dataVigencia']))			$this->dataVigencia			= $params['dataVigencia'];	
		if(!empty($params['prazoVigencia']))		$this->prazoVigencia		= $params['prazoVigencia'];			
		if(!empty($params['pis']))					$this->pis					= new SoapVar(number_format($params['pis'], 2, '.', ''), XSD_DECIMAL);
		if(!empty($params['iss']))					$this->iss					= new SoapVar(number_format($params['iss'], 2, '.', ''), XSD_DECIMAL);	
		if(!empty($params['dataCancelamento']))		$this->dataCancelamento		= $params['dataCancelamento'];	
		if(!empty($params['motivoCancelamento']))	$this->motivoCancelamento	= $params['motivoCancelamento'];	
		if(!empty($params['valorEstornado']))		$this->valorEstornado		= new SoapVar(number_format($params['valorEstornado'], 2, '.', ''), XSD_DECIMAL);
	}
}//end class servicoVendaDetalhesReq

//Classe contendo os dados detalhados do Produto
class produtoDetalhesReq {
	public $codigo;			// string
	public $descricao;		// string
	public $preco;			// double
	public $garantiaFabrica;// int
	public $dataVenda;		// dateTime
	public $fabricante;		// string
	public $tipoVenda;		// tipoVenda
	public $tipoProduto;	// string
	public $modelo;			// string
	public $numeroSerie;	// string
	public $precoTabela;	// double -- var (decimal)
	
	function __construct($params) {
		if(!empty($params['codigo']))			$this->codigo			= $params['codigo'];			
		if(!empty($params['descricao']))		$this->descricao		= $params['descricao'];		
		if(!empty($params['preco']))			$this->preco			= new SoapVar(number_format($params['preco'], 2, '.', ''), XSD_DECIMAL);		
		if(!empty($params['garantiaFabrica']))	$this->garantiaFabrica	= $params['garantiaFabrica'];
		if(!empty($params['dataVenda']))		$this->dataVenda		= $params['dataVenda'];		
		if(!empty($params['fabricante']))		$this->fabricante		= $params['fabricante'];		
		if(!empty($params['tipoVenda'])) {
			$tipoVenda				= new tipoVendaReq($params['tipoVenda']); // $tipoVenda 1 = NOVO; 2 = AVULSO; default = 1
			$this->tipoVenda		= $tipoVenda->tipoVenda;
		}
		if(!empty($params['tipoProduto']))		$this->tipoProduto		= $params['tipoProduto'];	
		if(!empty($params['modelo']))			$this->modelo			= $params['modelo'];			
		if(!empty($params['numeroSerie']))		$this->numeroSerie		= $params['numeroSerie'];	
		if(!empty($params['precoTabela']))		$this->precoTabela		= new SoapVar(number_format($params['precoTabela'], 2, '.', ''), XSD_DECIMAL); //XSD_DOUBLE); 
	
	}
}

//Classe contendo os dados detalhados do tipoVenda
class tipoVendaReq {
	public $tipoVenda;	// string
	//Conteudo dos Nomes
	private $novo	= B2C_TIPO_VENDA_NOVO;
	private $avulso	= B2C_TIPO_VENDA_AVULSO;

	function __construct($tipoVenda = 1) {
		/****************************************
		Valores do parametro $tipoVenda, onde:
			1 = NOVO
			2 = AVULSO
		*****************************************/
		$this->tipoVenda = $this->getRequestLabel($tipoVenda);
	}

	public function getRequestLabel($tipoVenda) {

		switch($tipoVenda) 
		{
			case 1:
				return $this->novo; 
				break;
			case 2:
				return $this->avulso; 
				break;
		}//end switch

	}//end function getRequestLabel

}//end class tipoVendaReq

//Classe contendo os dados detalhados do Cliente
class clienteDetalhesReq {
	
	public $tipoPessoa;			// tipoPessoa
	public $cpfCnpj;			// string
	public $nome;				// string
	public $dataNascimento;		// dateTime
	public $sexo;				// sexo
	public $estadoCivil;		// estadoCivil
	public $telefoneCelular;	// string
	public $telefoneResidencial;// string
	public $rg;					// string
	public $localExpedicaoRg;	// string
	public $dataExpedicaoRg;	// dateTime
	public $email;				// string
	public $endereco;			// endereco
	
	function __construct($params) {
		if(!empty($params['tipoPessoa']))	{
			$tipoPessoa				= new tipoPessoaReq($params['tipoPessoa']);
			$this->tipoPessoa		= $tipoPessoa->tipoPessoa;
		}
		if(!empty($params['cpfCnpj']))				$this->cpfCnpj			= $params['cpfCnpj'];			
		if(!empty($params['nome']))					$this->nome				= $params['nome'];				
		if(!empty($params['dataNascimento']))		$this->dataNascimento	= $params['dataNascimento'];		
		if(!empty($params['sexo']))	{
			$sexo					= new sexoReq($params['sexo']);
			$this->sexo				= $sexo->sexo;
		}
		if(!empty($params['estadoCivil']))	{
			$estadoCivil			= new estadoCivilReq($params['estadoCivil']);
			$this->estadoCivil		= $estadoCivil->estadoCivil;
		}
		if(!empty($params['telefoneCelular']))		$this->telefoneCelular	= $params['telefoneCelular'];	
		if(!empty($params['telefoneResidencial']))	$this->telefoneResidencial= $params['telefoneResidencial'];
		if(!empty($params['rg']))					$this->rg				= $params['rg'];					
		if(!empty($params['localExpedicaoRg']))		$this->localExpedicaoRg	= $params['localExpedicaoRg'];	
		if(!empty($params['dataExpedicaoRg']))		$this->dataExpedicaoRg	= $params['dataExpedicaoRg'];	
		if(!empty($params['email']))				$this->email			= $params['email'];				
		if(!empty($params['endereco']))				$this->endereco			= new enderecoDetalhesReq($params['endereco']);
	}
}

//Classe contendo os dados detalhados do tipoPessoa
class tipoPessoaReq {
	public $tipoPessoa;	// string
	//Conteudo dos Nomes
	private $pf	= B2C_TIPO_PESSOA_FISICA;
	private $pj	= B2C_TIPO_PESSOA_JURIDICA;

	function __construct($tipoPessoa) {
		/****************************************
		Valores do parametro $tipoPessoa, onde:
			PF = FISICA
			PJ = JURIDICA
		*****************************************/
		$this->tipoPessoa = $this->getRequestLabel($tipoPessoa);
	}

	public function getRequestLabel($tipoPessoa) {

		switch($tipoPessoa) 
		{
			case 'PF':
				return $this->pf; 
				break;
			case 'PJ':
				return $this->pj; 
				break;
		}//end switch

	}//end function getRequestLabel

}//end class tipoPessoaReq

//Classe contendo os dados detalhados do sexo
class sexoReq {
	public $sexo;	// string
	//Conteudo dos Nomes
	private $feminino	= B2C_TIPO_FEMININO;
	private $masculino	= B2C_TIPO_MASCULINO;

	function __construct($sexo) {
		/****************************************
		Valores do parametro $sexo, onde:
			F = FEMININO
			M = MASCULINO
		*****************************************/
		$this->sexo = $this->getRequestLabel($sexo);
	}

	public function getRequestLabel($sexo) {

		switch($sexo) 
		{
			case 'F':
				return $this->feminino; 
				break;
			case 'M':
				return $this->masculino; 
				break;
		}//end switch

	}//end function getRequestLabel

}//end class sexoReq

//Classe contendo os dados detalhados do estadoCivil
class estadoCivilReq {
	public $estadoCivil;	// string 
	//Conteudo dos Nomes
	private $solteiro	= B2C_ESTADO_CIVIL_SOLTEIRO;
	private $casado		= B2C_ESTADO_CIVIL_CASADO;
	private $viuvo		= B2C_ESTADO_CIVIL_VIUVO;
	private $divorciado	= B2C_ESTADO_CIVIL_DIVORCIADO;
	private $desquitado	= B2C_ESTADO_CIVIL_DESQUITADO;
	private $companheiro= B2C_ESTADO_CIVIL_COMPANHEIRO;
	private $outros		= B2C_ESTADO_CIVIL_OUTROS;

	function __construct($estadoCivil) {
		/****************************************
		Valores do parametro $estadoCivil, onde:
			S	= SOLTEIRO
			C	= CASADO
			V	= VIUVO
			D	= DIVORCIADO
			DES = DESQUITADO
			COM = COMPANHEIRO
			O	= OUTROS
		*****************************************/
		$this->estadoCivil = $this->getRequestLabel($estadoCivil);
	}

	public function getRequestLabel($estadoCivil) {

		switch($estadoCivil) 
		{
			case 'S':
				return $this->solteiro; 
				break;
			case 'C':
				return $this->casado; 
				break;
			case 'V':
				return $this->viuvo; 
				break;
			case 'D':
				return $this->divorciado; 
				break;
			case 'DES':
				return $this->desquitado; 
				break;
			case 'COM':
				return $this->companheiro; 
				break;
			case 'O':
				return $this->outros; 
				break;
		}//end switch

	}//end function getRequestLabel

}//end class estadoCivilReq

//Classe contendo os dados detalhados do Endereco
class enderecoDetalhesReq {
	public $logradouro;		// string
	public $numero;			// int
	public $bairro;			// string
	public $cidade;			// string
	public $cep;			// string
	public $complemento;	// string
	public $uf;				// string sigla em maisculo

	function __construct($params) {
		if(!empty($params['logradouro']))	$this->logradouro	= $params['logradouro'];			
		if(!empty($params['numero']))		$this->numero		= $params['numero'];				
		if(!empty($params['bairro']))		$this->bairro		= $params['bairro'];		
		if(!empty($params['cidade']))		$this->cidade		= $params['cidade'];	
		if(!empty($params['cep']))			$this->cep			= $params['cep'];
		if(!empty($params['complemento']))	$this->complemento	= $params['complemento'];	
		if(!empty($params['uf']))			$this->uf			= $params['uf'];	
	}
	
}

?>