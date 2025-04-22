<?php
/**
bsc_id serial NOT NULL,
bsc_descricao character varying(256) NOT NULL,
bsc_data_cadastro timestamp with time zone NOT NULL, -- Campo contendo a data de cadastro da categoria
bsc_status
 */
class CategoriaBannerVO {
    public $id;
    public $descricao;
    public $status;
    public $dataCadastro;
    
    public function __construct($id = null, $descricao = "", $status = false, $dataCadastro = "") {
        $this->id = $id;
        $this->descricao = $descricao;
        $this->status = $status;
        $this->dataCadastro = $dataCadastro;
    }

    public function getId() {
        return $this->id;
    }

    public function getDescricao() {
        return $this->descricao;
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