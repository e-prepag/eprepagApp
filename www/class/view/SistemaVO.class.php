<?php
require_once "AbaVO.class.php";
require_once "MenuVO.class.php";
require_once "ItemVO.class.php";

class SistemaVO {
    
    protected $tipo;
    
    protected $abas;
        
    public function __construct($tipo = "", $abas = array()) {
        $this->tipo = $tipo;
        $this->abas = $abas;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getAbas() {
        return $this->abas;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setAbas(array $abas) {
        $this->abas = $abas;
    }

}
?>