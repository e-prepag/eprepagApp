<?php
/**
 * Classe View Object das categorias de Estorno e Chargeback
 *
 * @author Wagner de Miranda
 * @email wagner.mbis@gmail.com
 * @date 06-10-2015
 */

class CategoriaEstornoChargebackVO {
    public $id;
    public $descricao;
    public $status;
    public $dataCadastro;
    
    public function __construct($id = null, $descricao = "", $status = false, $dataCadastro = "") {
        $this->id = $id;
        $this->descricao = $descricao;
        $this->dataCadastro = $dataCadastro;
        $this->status = $status;
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
}//end Class CategoriaEstornoChargebackVO
