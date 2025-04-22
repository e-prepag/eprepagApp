<?php
/** 
 * Classe para os atributos de pedidos de lan house
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 08-06-2015
 */

class VendasLanHouseVO {
    
    private $idVenda = null;
	
	private $idVendaAPI = null;
    
    private $dataInclusao = "";
	
	private $categoria = "";
    
    private $tipoPagamento =  "";
    
    private $valor =  "";
    
    private $repasse =  "";
    
    private $qtdItens =  null;
    
    private $qtdProdutos =  null;
    
    private $codUsuario =  null;
    
    private $tipoUsuario =  "";
    
    private $nome =  "";
    
    private $conciliacao =  "";
    
    private $status =  "";
    
    private $cpf =  "";
    
    private $cesta = array();
    
    public function __construct(
                                    $idVenda = null, 
                                    $dataInclusao = "", 
                                    $tipoPagamento = "", 
                                    $valor = "", 
                                    $repasse = "", 
                                    $qtdItens = null, 
                                    $qtdProdutos = null, 
                                    $codUsuario = null, 
                                    $tipoUsuario = "", 
                                    $nome = "",
                                    $conciliacao = "", 
                                    $status = "", 
                                    $cesta = array()) 
        {
        $this->idVenda = $idVenda;
		$this->idVendaAPI = "";
        $this->dataInclusao = $dataInclusao;
        $this->tipoPagamento = $tipoPagamento;
        $this->valor = $valor;
        $this->repasse = $repasse;
        $this->qtdItens = $qtdItens;
        $this->qtdProdutos = $qtdProdutos;
        $this->codUsuario = $codUsuario;
        $this->tipoUsuario = $tipoUsuario;
        $this->nome = $nome;
        $this->conciliacao = $conciliacao;
        $this->status = $status;
        $this->cesta = $cesta;
    }

    
    /*
     * GETTERS
     */
    
    public function getIdVenda() {
        return $this->idVenda;
    }
	
	public function getIdVendaAPI() {
        return $this->idVendaAPI;
    }

    public function getDataInclusao() {
        return $this->dataInclusao;
    }

    public function getTipoPagamento() {
        return $this->tipoPagamento;
    }

    public function getValor() {
        return $this->valor;
    }

    public function getRepasse() {
        return $this->repasse;
    }

    public function getQtdItens() {
        return $this->qtdItens;
    }

    public function getQtdProdutos() {
        return $this->qtdProdutos;
    }

    public function getCodUsuario() {
        return $this->codUsuario;
    }

    public function getTipoUsuario() {
        return $this->tipoUsuario;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getCPF() {
        return $this->cpf;
    }

    public function getConciliacao() {
        return $this->conciliacao;
    }

    public function getStatus() {
        return $this->status;
    }
    
    public function getCesta() {
        return $this->cesta;
    }
	
	public function getCategoria() {
        return $this->categoria;
    }

    /*
     *  SETTERS
     */
    
    public function setIdVenda($idVenda) {
        $this->idVenda = $idVenda;
        return $this;
    }
	
	public function setIdVendaAPI($idVenda) {
        $this->idVendaAPI = $idVenda;
        return $this;
    }

    public function setDataInclusao($dataInclusao) {
        $this->dataInclusao = $dataInclusao;
        return $this;
    }

    public function setTipoPagamento($tipoPagamento) {
        $this->tipoPagamento = $tipoPagamento;
        return $this;
    }

    public function setValor($valor) {
        $this->valor = $valor;
        return $this;
    }

    public function setRepasse($repasse) {
        $this->repasse = $repasse;
        return $this;
    }

    public function setQtdItens($qtdItens) {
        $this->qtdItens = $qtdItens;
        return $this;
    }

    public function setQtdProdutos($qtdProdutos) {
        $this->qtdProdutos = $qtdProdutos;
        return $this;
    }

    public function setCodUsuario($codUsuario) {
        $this->codUsuario = $codUsuario;
        return $this;
    }

    public function setTipoUsuario($tipoUsuario) {
        $this->tipoUsuario = $tipoUsuario;
        return $this;
    }

    public function setNome($nome) {
        $this->nome = preg_replace('/[^A-Za-z0-9\-]/', '', $nome);
        return $this;
    }

    public function setCPF($cpf) {
        $this->cpf = $cpf;
        return $this;
    }

    public function setConciliacao($conciliacao) {
        $this->conciliacao = $conciliacao;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }
    
    public function setCesta($cesta) {
        $this->cesta = $cesta;
        return $this;
    }
	
	public function setCategoria($tipo) {
		
		switch($tipo){
			case 0:
				$this->categoria = "Normal";
			break;			
			case 1:
				$this->categoria = "Vip";
			break;	
			case 2:
				$this->categoria = "Master";
			break;	
			case 3:
				$this->categoria = "Black";
			break;	
			case 4:
				$this->categoria = "Gold";
			break;			
			default:
				$this->categoria = "Não encontrada";
			break;				
		}
        return $this;
    }
	
 }