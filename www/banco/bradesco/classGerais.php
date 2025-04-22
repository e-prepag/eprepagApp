<?php

class endereco{
    public $cep;
    public $logradouro;
    public $numero;
    public $complemento;
    public $bairro;
    public $cidade;
    public $uf;


    public function __construct($params) {
        $this->cep = $params['cep'];  //cep comprador
        $this->logradouro = $params['logradouro']; //logradouro comprador
        $this->numero = $params['numero']; //numero casa comprador
        if(isset($params['complemento'])){
            if(empty($params['complemento'])){
                unset($this->complemento);
            } else{
                $this->complemento = $params['complemento'];
            }
        } else{
            unset($this->complemento);
        }
        $this->bairro = $params['bairro'];
        $this->cidade = $params['cidade'];
        $this->uf = $params['uf'];
    }
}

class comprador{
    public $nome;
    public $documento;
    public $endereco;
    public $ip;
    public $user_agent;
    
    
    public function __construct($params) {
        $this->nome = $params['nome'];
        $this->documento = $params['documento'];
        $this->endereco = new endereco($params['endereco']);
        $this->ip = $params['ip'];
        $this->user_agent = $params['user_agent'];
    }
    
}

class pedido{
    public $numero;
    public $valor;
    public $descricao;


    public function __construct($params) {
        $this->numero = $params['numero'];
        $this->valor = $params['valor'];
        $this->descricao = $params['descricao'];
    }
   
}
?>