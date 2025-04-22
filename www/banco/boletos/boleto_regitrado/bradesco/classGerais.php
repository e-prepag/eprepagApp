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

class pagador{
    public $nome;
    public $documento;
    public $tipo_documento;
    public $endereco;
    
    
    public function __construct($params) {
        $this->nome = $params['nome'];
        $this->documento = $params['documento'];
        $this->tipo_documento = $params['tipo_documento'];
        $this->endereco = new endereco($params['endereco']);
    }
    
}

class sacadoravalista{
    public $nome;
    public $documento;
    public $tipo_documento;
    public $endereco;
    
    
    public function __construct($params) {
        $this->nome = $params['nome'];
        $this->documento = $params['documento'];
        $this->tipo_documento = $params['tipo_documento'];
        $this->endereco = new endereco($params['endereco']);
        
    }
}

class informacoesopcionais{
    public $agencia_pagador;
    public $razao_conta_pagador;
    public $conta_pagador;
    public $controle_participante;
    public $especie;
    public $aceite;
    public $tipo_protesto_negociacao;
    public $qtde_dias_protesto;
    public $tipo_decurso_prazo;
    public $qtde_dias_decurso;
    public $tipo_emissao_papeleta;
    public $qtde_parcelas;
    public $perc_juros;
    public $valor_juros;
    public $qtde_dias_juros;
    public $perc_multa_atraso;
    public $valor_multa_atraso;
    public $qtde_dias_multa_atraso;
    public $perc_desconto_1;
    public $valor_desconto_1;
    public $data_limite_desconto_1;
    public $perc_desconto_2;
    public $valor_desconto_2;
    public $data_limite_desconto_2;
    public $perc_desconto_3;
    public $valor_desconto_3;
    public $data_limite_desconto_3;
    public $tipo_bonificacao;
    public $perc_desc_bonificacao;
    public $valor_desc_bonificacao;
    public $data_limite_desc_bonificacao;
    public $valor_abatimento;
    public $valor_iof;
    public $sequencia_registro;
    public $sacador_avalista;
    
    
    public function __construct($params) {
        if(isset($params['agencia_pagador'])){  $this->agencia_pagador = $params['agencia_pagador'];} else{    unset($this->agencia_pagador);}
        if(isset($params['razao_conta_pagador'])){  $this->razao_conta_pagador = $params['razao_conta_pagador'];} else{    unset($this->razao_conta_pagador);}
        if(isset($params['conta_pagador'])){  $this->conta_pagador = $params ['conta_pagador'];} else{    unset($this->conta_pagador);}
        if(isset($params['controle_participante'])){  $this->controle_participante = $params['controle_participante'];} else{    unset($this->controle_participante);}
        if(isset($params['especie'])){  $this->especie = $params['especie']; } else{unset($this->especie);}
        
        if(isset($params['aceite'])){  $this->aceite = $params['aceite'];} else{    unset($this->aceite);}
        if(isset($params['tipo_protesto_negociacao'])){ $this->tipo_protesto_negociacao = $params['tipo_protesto_negociacao'];} else{    unset($this->tipo_protesto_negociacao);}
        if(isset($params['qtde_dias_protesto'])){   $this->qtde_dias_protesto = $params['qtde_dias_protesto'];} else{    unset($this->qtde_dias_protesto);}
        if(isset($params['tipo_decurso_prazo'])){ $this->tipo_decurso_prazo = $params['tipo_decurso_prazo'];} else{    unset($this->tipo_decurso_prazo);}
        if(isset($params['qtde_dias_decurso'])){    $this->qtde_dias_decurso = $params['qtde_dias_decurso'];} else{    unset($this->qtde_dias_decurso);}
        
        if(isset($params['tipo_protesto_negociacao'])){ $this->tipo_emissao_papeleta = $params['tipo_emissao_papeleta'];} else{    unset($this->tipo_emissao_papeleta);}
        if(isset($params['qtde_parcelas'])){    $this->qtde_parcelas = $params['qtde_parcelas'];} else{    unset($this->qtde_parcelas);}
        if(isset($params['perc_juros'])){ $this->perc_juros = $params['perc_juros'];} else{    unset($this->perc_juros);}
        if(isset($params['valor_juros'])){ $this->valor_juros = $params['valor_juros'];} else{    unset($this->valor_juros);}
        if(isset($params['qtde_dias_juros'])){ $this->qtde_dias_juros = $params['qtde_dias_juros'];} else{    unset($this->qtde_dias_juros);}
        if(isset($params['perc_multa_atraso'])){ $this->perc_multa_atraso = $params['perc_multa_atraso'];} else{    unset($this->perc_multa_atraso);}
        if(isset($params['valor_multa_atraso'])){   $this->valor_multa_atraso = $params['valor_multa_atraso'];} else{    unset($this->valor_multa_atraso);}
        if(isset($params['qtde_dias_multa_atraso'])){ $this->qtde_dias_multa_atraso = $params['qtde_dias_multa_atraso'];} else{     unset($this->qtde_dias_multa_atraso);}
        
        if(isset($params['perc_desconto_1'])){ $this->perc_desconto_1 = $params['perc_desconto_1'];} else{     unset($this->perc_desconto_1);}
        if(isset($params['valor_desconto_1'])){ $this->valor_desconto_1 = $params['valor_desconto_1'];} else{     unset($this->valor_desconto_1);}
        if(isset($params['data_limite_desconto_1'])){ $this->data_limite_desconto_1 = $params['data_limite_desconto_1'];} else{     unset($this->data_limite_desconto_1);}
        if(isset($params['perc_desconto_2'])){ $this->perc_desconto_2 = $params['perc_desconto_2'];} else{     unset($this->perc_desconto_2);}
        if(isset($params['valor_desconto_2'])){ $this->valor_desconto_2 = $params['valor_desconto_2'];} else{     unset($this->valor_desconto_2);}
        if(isset($params['data_limite_desconto_2'])){ $this->data_limite_desconto_2 = $params['data_limite_desconto_2'];} else{     unset($this->data_limite_desconto_2);}
        if(isset($params['perc_desconto_3'])){  $this->perc_desconto_3 = $params['perc_desconto_3'];} else{     unset($this->perc_desconto_3);}
        if(isset($params['valor_desconto_3'])){ $this->valor_desconto_3 = $params['valor_desconto_3'];} else{     unset($this->valor_desconto_3);}
        if(isset($params['data_limite_desconto_3'])){ $this->data_limite_desconto_3 = $params['data_limite_desconto_3'];} else{     unset($this->data_limite_desconto_3);}
        
        if(isset($params['tipo_bonificacao'])){ $this->tipo_bonificacao = $params['tipo_bonificacao'];} else{     unset($this->tipo_bonificacao);}
        if(isset($params['perc_desconto_bonificacao'])){ $this->perc_desc_bonificacao = $params['perc_desc_bonificacao'];} else{     unset($this->perc_desc_bonificacao);}
        if(isset($params['valor_desc_bonificacao'])){ $this->valor_desc_bonificacao = $params['valor_desc_bonificacao'];} else{     unset($this->valor_desc_bonificacao);}
        if(isset($params['data_limite_desc_bonificacao'])){ $this->data_limite_desc_bonificacao = $params['data_limite_desc_bonificacao'];} else{     unset($this->data_limite_desc_bonificacao);}
        if(isset($params['valor_abatimento'])){ $this->valor_abatimento = $params['valor_abatimento'];} else{     unset($this->valor_abatimento);}
        if(isset($params['valor_iof'])){ $this->valor_iof = $params['valor_iof'];} else{     unset($this->valor_iof);}
        if(isset($params['sequencia_registro'])){ $this->sequencia_registro = $params['sequencia_registro'];} else{     unset($this->sequencia_registro);}
        $this->sacador_avalista= new sacadoravalista($params['sacador_avalista']);
    }
}

class boleto{
    public $carteira;
    public $nosso_numero;
    public $numero_documento;
    public $data_emissao;
    public $data_vencimento;
    public $valor_titulo;
    public $pagador;
    public $informacoes_opcionais;


    public function __construct($params) {
        $this->carteira = BRADESCO_CARTEIRA;
        $this->nosso_numero = $params['nosso_numero'];
        $this->numero_documento = $params['numero_documento'];
        $this->data_emissao = $params['data_emissao'];
        $this->data_vencimento = $params['data_vencimento'];
        $this->valor_titulo = $params['valor_titulo'];
        
        $this->pagador = new pagador($params['pagador']);
        if(isset($params['informacoes_opcionais'])) {
            $this->informacoes_opcionais = new informacoesopcionais($params['informacoes_opcionais']);
        }
    }
   
}
?>