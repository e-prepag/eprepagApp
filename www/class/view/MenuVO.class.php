<?php
class MenuVO {
    
    protected $id;
    
    protected $descricao;
    
    protected $idAba;
    
    protected $ordem;
    
    protected $itens;
        
    function __construct($id = null, $descricao = "", $idAba = null, $ordem = null, $itens = array()) {
        $this->id = $id;
        $this->descricao = $descricao;
        $this->idAba = $idAba;
        $this->ordem = $ordem;
        $this->itens = $itens;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function getIdAba() {
        return $this->idAba;
    }

    public function getOrdem() {
        return $this->ordem;
    }

    public function getItens() {
        return $this->itens;
    }

    public function setItens($itens) {
        $this->itens = $itens;
    }
    
    public function setId($id) {
        $this->id = $id;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setIdAba($idAba) {
        $this->idAba = $idAba;
    }

    public function setOrdem($ordem) {
        $this->ordem = $ordem;
    }
    
}

?>