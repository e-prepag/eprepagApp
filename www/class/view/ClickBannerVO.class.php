<?php
class ClickBannerVO {
    
    public $id;
    
    public $dataClick;
    
    public $idBanner;
    
    public $clicks;
    
    public $tituloBanner;
    
    public function __construct($id = null, $dataClick = "", $idBanner = null, $clicks = null, $tituloBanner = "") {
        $this->id = $id;
        $this->dataClick = $dataClick;
        $this->idBanner = $idBanner;
        $this->clicks = $clicks;
        $this->tituloBanner = $tituloBanner;
    }

    public function getTitulo() {
        return $this->tituloBanner;
    }

    public function setTitulo($tituloBanner) {
        $this->tituloBanner = $tituloBanner;
        return $this;
    }

        public function getId() {
        return $this->id;
    }

    public function getDataClick() {
        return Util::getData($this->dataClick);
    }

    public function getIdBanner() {
        return $this->idBanner;
    }

    public function getClicks() {
        return $this->clicks;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setDataClick($dataClick) {
        $this->dataClick = $dataClick;
        return $this;
    }

    public function setIdBanner($idBanner) {
        $this->idBanner = $idBanner;
        return $this;
    }

    public function setClicks($clicks) {
        $this->clicks = $clicks;
        return $this;
    }





}
?>