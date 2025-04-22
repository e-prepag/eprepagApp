<?php
class ItemVO {
    
    protected $id;
    
    protected $descricao;
    
    protected $link;
    
    protected $chaveMonitor;
    
    protected $idMenu;
    
    protected $ordem;
    
    protected $apareceNaListagem;
    
    function __construct($id = null, $descricao = "", $link = "", $chaveMonitor = "", $idMenu = null, $ordem = null) {
        $this->id = $id;
        $this->descricao = $descricao;
        $this->link = $link;
        $this->chaveMonitor = $chaveMonitor;
        $this->idMenu = $idMenu;
        $this->ordem = $ordem;
    }

    public function getId() {
        return $this->id;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function getLink() {
        return $this->link;
    }

    public function getChaveMonitor() {
        return $this->chaveMonitor;
    }

    public function getIdMenu() {
        return $this->idMenu;
    }

    public function getOrdem() {
        return $this->ordem;
    }

    public function getApareceNaListagem() {
        return $this->apareceNaListagem;
    }

    public function setApareceNaListagem($apareceNaListagem) {
        $this->apareceNaListagem = $apareceNaListagem;
    }
    
    public function setId($id) {
        $this->id = $id;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setLink($link) {
        $this->link = $link;
    }

    public function setChaveMonitor($chaveMonitor = "") {
        $this->chaveMonitor = $chaveMonitor;
    }

    public function setIdMenu($idMenu) {
        $this->idMenu = $idMenu;
    }

    public function setOrdem($ordem) {
        $this->ordem = $ordem;
    }

}
?>