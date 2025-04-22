<?php
/**
 * Classe para os atributos dos Dados Bancários para Estornos
 *
 * @author Wagner de Miranda
 * @email wagner.mbis@gmail.com
 * @date 21-10-2015
 */

class EstornoDadosBancariosVO {
    
    /*
    edb_id bigserial NOT NULL, -- Campo contendo o ID do registro desta tabela.
    edb_titular character varying(512) NOT NULL, -- Campo contendo o Titular que receberá o Estorno
    edb_cpf_cnpj character varying(18), -- Campo contendo o CPF ou CNPJ do Titular.
    edb_banco character varying(256) NOT NULL, -- Campo contendo o Banco do Titular.
    edb_agencia character varying(15) NOT NULL, -- Campo contendo a agência do Titular.
    edb_conta character varying(15) NOT NULL, -- Campo contendo a conta do Titular.
    edb_tipo_conta smallint NOT NULL, -- Campo contendo o tipo da conta do Titular. Onde:  1 => Conta Corrente e 2 => Conta Poupança.
    ec_id bigint NOT NULL, -- Campo contendo o ID do Estorno da tabelaestorno_chargeback.
    */
        
    private $edb_id;
    private $edb_titular;
    private $edb_cpf_cnpj;
    private $edb_banco;
    private $edb_agencia;
    private $edb_conta;
    private $edb_tipo_conta;
    private $ec_id;
    public $dados = array(
		'edb_id'        => null,
		'edb_titular'   => null,
		'edb_cpf_cnpj'       => null,
                'edb_banco'     => null,
		'edb_agencia'   => null,
		'edb_conta'     => null,
		'edb_tipo_conta'=> null,
		'ec_id'         => null
		);
        
    public function __construct($dados = null) {
        
            if(is_array($dados)){
                foreach ($dados as $key => $value) {
                        if($this->isCampoTabela($key)) {
                                $this->dados[$key] = $value;
                        }//end if($this->isCampoTabela($key))
                }//end foreach
            }//end if(is_array($dados))
            $this->setId($this->dados['edb_id']);
            $this->setTitular($this->dados['edb_titular']);
            $this->setCPF($this->dados['edb_cpf_cnpj']);
            $this->setBanco($this->dados['edb_banco']);
            $this->setAgencia($this->dados['edb_agencia']);
            $this->setConta($this->dados['edb_conta']);
            $this->setTipoConta($this->dados['edb_tipo_conta']);
            $this->setIdEstorno($this->dados['ec_id']);
            
    } //end function __construct
    
    public function getId() {
        return $this->edb_id;
    }

    public function setId($edb_id) {
        $this->edb_id = $edb_id;
        return $this;
    }

    public function getTitular() {
        return $this->edb_titular;
    }

    public function setTitular($edb_titular) {
        $this->edb_titular = $edb_titular;
        return $this;
    }

    public function getCPF() {
        return $this->edb_cpf_cnpj;
    }

    public function setCPF($edb_cpf_cnpj) {
        $this->edb_cpf_cnpj = $edb_cpf_cnpj;
        return $this;
    }

    public function getBanco() {
        return $this->edb_banco;
    }

    public function setBanco($edb_banco) {
        $this->edb_banco = $edb_banco;
        return $this;
    }

    public function getAgencia() {
        return $this->edb_agencia;
    }

    public function setAgencia($edb_agencia) {
        $this->edb_agencia = $edb_agencia;
        return $this;
    }

    public function getConta() {
        return $this->edb_conta;
    }

    public function setConta($edb_conta) {
        $this->edb_conta = $edb_conta;
        return $this;
    }

    public function getTipoConta() {
        return $this->edb_tipo_conta;
    }

    public function setTipoConta($edb_tipo_conta) {
        $this->edb_tipo_conta = $edb_tipo_conta;
        return $this;
    }

    public function getIdEstorno() {
        return $this->ec_id;
    }

    public function setIdEstorno($ec_id) {
        $this->ec_id = $ec_id;
        return $this;
    }

    public function isCampoTabela($campo) {
        $retorno = false;
        foreach ($this->dados as $key => $value) {
                if($key == $campo && $campo != 'ec_id') {
                    $retorno = true;
                }//end if($key == $campo)
        }//end foreach
        return $retorno;
    }//end function isCampoTabela

} //end Class
