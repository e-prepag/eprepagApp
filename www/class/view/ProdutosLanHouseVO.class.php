<?php

/**
 * Classe para os atributos de pedidos de lan house
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 08-06-2015
 */

class ProdutosLanHouseVO {
    
    private $nomeOperador;
    
    private $nomeProduto;
    
    private $modelo;
    
    private $valor;
    
    private $qtd;
    
    private $desconto;
    
    private $repasse;
    
    private $valorUnitario;
    
    private $qtdImpressoes;
    
    private $pinCodInterno;
    
    private $ogp_iof;
    
    public function getIOF() {
        return $this->ogp_iof;
    }

    public function setIOF($ogp_iof) {
        $this->ogp_iof = $ogp_iof;
        return $this;
    }

        
    public function getPinCodInterno() {
        return $this->pinCodInterno;
    }

    public function setPinCodInterno($pinCodInterno) {
        $this->pinCodInterno = $pinCodInterno;
        return $this;
    }

        
    public function getQtdImpressoes() {
        return $this->qtdImpressoes;
    }

    public function setQtdImpressoes($qtdImpressoes) {
        $this->qtdImpressoes = $qtdImpressoes;
        return $this;
    }
    
    public function getValorUnitario() {
        return $this->valorUnitario;
    }

    public function setValorUnitario($valorUnitario) {
        $this->valorUnitario = $valorUnitario;
        return $this;
    }
    
    public function getDesconto() {
        return $this->desconto;
    }

    public function getRepasse() {
        return $this->repasse;
    }

    public function setDesconto($desconto) {
        $this->desconto = $desconto;
        return $this;
    }

    public function setRepasse($repasse) {
        $this->repasse = $repasse;
        return $this;
    }
    
    public function setNomeOperador($nomeOperador) {
        $this->nomeOperador = preg_replace('/[^A-Za-z0-9\-]/', '', $nomeOperador);
        return $this;
    }
    
    public function getNomeOperador() {
        return $this->nomeOperador;
    }
    
    public function setNomeProduto($nomeProduto) {
        $this->nomeProduto = $nomeProduto;
        return $this;
    }

    public function getNomeProduto() {
        return $this->nomeProduto;
    }
    
    public function setModelo($modelo) {
        $this->modelo = $modelo;
        return $this;
    }
    
    public function getModelo() {
        return $this->modelo;
    }

    public function setValor($valor) {
        $this->valor = $valor;
        return $this;
    }
    
    public function getValor() {
        return $this->valor;
    }

    public function setQtd($qtd) {
        $this->qtd = $qtd;
        return $this;
    }
    public function getQtd(){
        return $this->qtd;
    }
    
}
