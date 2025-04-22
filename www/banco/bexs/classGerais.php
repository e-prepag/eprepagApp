<?php

class cliente{
    public $natureza;
    public $documento;
    public $datanasc;
    public $nome;
    public $logradouro;
    public $bairro;
    public $cidade;
    public $estado;
    public $cep;
    public $email;
    public $telefone;
    public $verificacao;

    public function __construct($params) {
        $this->natureza = $params['natureza'];
        $this->documento = $params['documento'];
        $this->datanasc = $params['datanasc'];
        $this->nome = $params['nome'];
        $this->logradouro = $params['logradouro'];
        $this->cep = $params['cep'];  
        $this->bairro = $params['bairro'];
        $this->cidade = $params['cidade'];
        $this->estado = $params['estado']; 
        $this->email = $params['email']; 
        $this->telefone = $params['telefone'];
        $this->verificacao = $params['verificacao'];
    }
}

class merchant{
    public $id;
    
    public function __construct($params) {
        $this->id = $params['id'];
    }
}

class operacao{
    public $id_op;
    public $datacp;
    public $valorme;
    public $valormn;
    public $taxaop;
    public $payment_method;
    public $cliente;
    public $merchant;
    public $pdv;

    public function __construct($params) {
        $this->id_op = $params['id_op'];
        $this->datacp = $params['datacp'];
        $this->valorme = $params['valorme'];
        $this->valormn = $params['valormn'];
        $this->taxaop = $params['taxaop'];
        $this->payment_method = $params['payment_method'];
        
        $this->cliente = new cliente($params['cliente']);
        $this->merchant = new merchant($params['merchant']);
        $this->pdv = new pdv($params['pdv']);
    }
    
}

class remessa{
    public $id_arquivo;
    public $perfil_op;
    public $tipoop;
    public $moeda;
    public $dataop;
    public $formame;
    public $datame;
    public $formamn;
    public $datamn;
    public $datalq;
    public $valorme;
    public $valormn;
    
    public function __construct($params) {
        $this->id_arquivo = $params['id_arquivo'];
        $this->perfil_op = $params['perfil_op'];
        $this->tipoop = $params['tipoop'];
        $this->moeda = $params['moeda'];
        $this->dataop = $params['dataop'];
        $this->formame = $params['formame'];
        $this->datame = $params['datame'];
        $this->formamn = $params['formamn'];
        $this->datamn = $params['datamn'];
        $this->datalq = $params['datalq'];
        $this->valorme = $params['valorme'];
        $this->valormn = $params['valormn'];
    }
}

class pdv{
    public $id;
    
    public function __construct($params) {
        $this->id = $params['id'];
    }
}