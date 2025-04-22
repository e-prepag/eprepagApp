<?php
class AbaVO {
    
    protected $id;
    
    protected $descricao;
    
    protected $sistema ;
    
    protected $ordem;
    
    protected $menus;
    
    protected $link;
    
    function __construct($id = null, $descricao = "", $sistema = "", $ordem = null, $menus = array(), $link = "") {
        $this->id = $id;
        $this->descricao = $descricao;
        $this->sistema = $sistema;
        $this->ordem = $ordem;
        $this->menus = $menus;
        $this->link = $link;
    }

    public function getId() {
        return $this->id;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function getSistema() {
        return $this->sistema;
    }

    public function getOrdem() {
        return $this->ordem;
    }
    
    public function getMenus() {
        return $this->menus;
    }

    public function getLink() {
        return $this->link;
    }

    public function setLink($link) {
        $this->link = $link;
    }
    
    public function setMenus($menus) {
        $this->menus = $menus;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setSistema($sistema) {
        $this->sistema = $sistema;
    }

    public function setOrdem($ordem) {
        $this->ordem = $ordem;
    }


    
}
?>