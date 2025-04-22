<?php
class BannerVO {
    
    public $id;
    
    public $titulo;
    
    public $imagem;
    
    public $link;
    
    public $dataInicio;
    
    public $dataFim;
    
    public $dataCadastro;
    
    public $status;
    
    public $categoria;

    public $posicao;
    
    public $clicks;
    
    public $ordenacao;
        
    public function __construct(
                                $id = null, 
                                $titulo = "", 
                                $imagem = "", 
                                $link = "", 
                                $dataInicio = "", 
                                $dataFim = "", 
                                $dataCadastro = "", 
                                $status = 0, 
                                $categoria = 0, 
                                $posicao = 0,
                                $clicks = 0,
                                $ordenacao = 0
                            )
    {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->imagem = $imagem;
        $this->link = $link;
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
        $this->dataCadastro = $dataCadastro;
        $this->status = $status;
        $this->categoria = $categoria;
        $this->posicao = $posicao;
        $this->clicks = $clicks;
        $this->ordenacao = $ordenacao;
    }
    
    public function getOrdenacao() {
        return $this->ordenacao;
    }

    public function setOrdenacao($ordenacao) {
        $this->ordenacao = $ordenacao;
        return $this;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getTitulo() {
        return $this->titulo;
    }

    public function getImagem() {
        return $this->imagem;
    }

    public function getLink() {
        return $this->link;
    }

    public function getDataInicio() {
        return Util::getData($this->dataInicio);
    }

    public function getDataFim() {
        return Util::getData($this->dataFim);
    }

    public function getDataCadastro() {
        return Util::getData($this->dataCadastro);
    }

    public function getStatus() {
        return $this->status;
    }

    public function getCategoria() {
        return $this->categoria;
    }

    public function getPosicao() {
        return $this->posicao;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setTitulo($titulo) {
        $this->titulo = $titulo;
        return $this;
    }

    public function setImagem($imagem) {
        $this->imagem = $imagem;
        return $this;
    }

    public function setLink($link) {
        $this->link = $link;
        return $this;
    }

    public function setDataInicio($dataInicio) {
        $this->dataInicio = $dataInicio;
        return $this;
    }

    public function setDataFim($dataFim) {
        $this->dataFim = $dataFim;
        return $this;
    }

    public function setDataCadastro($dataCadastro) {
        $this->dataCadastro = $dataCadastro;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function setCategoria($categoria) {
        $this->categoria = $categoria;
        return $this;
    }

    public function setPosicao($posicao) {
        $this->posicao = $posicao;
        return $this;
    }
    
    public function getClicks() {
        return $this->clicks;
    }

    public function setClicks($clicks) {
        $this->clicks = $clicks;
        return $this;
    }
}
?>