<?php
class PosicaoBannerVO {
    public $id;
    public $descricao;
    public $tamanho;
    public $status;
    public $dataCadastro;
    
    public function __construct($id = null, $descricao = "", $tamanho = null, $status = false, $dataCadastro =  "") {
        $this->id = $id;
        $this->descricao = $descricao;
        $this->tamanho = $tamanho;
        $this->status = $status;
        $this->dataCadastro = $dataCadastro;
    }

    public function getId() {
        return $this->id;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function getTamanho() {
        return $this->tamanho;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getDataCadastro() {
        return Util::getData($this->dataCadastro);
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
        return $this;
    }

    public function setTamanho($tamanho) {
        $this->tamanho = $tamanho;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function setDataCadastro($dataCadastro) {
        $this->dataCadastro = $dataCadastro;
        return $this;
    }


    
}
?>