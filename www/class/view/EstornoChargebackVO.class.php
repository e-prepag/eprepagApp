<?php
/**
 * Classe para os atributos dos Estornos e ChargeBack
 *
 * @author Wagner de Miranda
 * @email wagner.mbis@gmail.com
 * @date 22-10-2015
 */

class EstornoChargeBackVO {
    
    /*
    ec_id bigserial NOT NULL, -- Campo contendo o ID desta tabela.
    ec_data_devolucao timestamp with time zone NOT NULL, -- Campo contendo a data do Estorno ou ChargeBack.
    ec_pin_bloqueado smallint NOT NULL, -- Campo contendo informação se o PIN relacionado ao pedido foi Bloqueado ou não. Onde: 0 => NÃO foi Bloqueado e 1 => Foi Bloqueado.
    cec_id integer NOT NULL, -- Campo contendo o ID do motivo do Estorno ou ChargeBack.
    ec_tipo_usuario character varying(1) NOT NULL, -- Campo contendo o tipo de usuário do estorno. Onde: G => Gamer e L => Lan House.
    ec_valor numeric(15,2) NOT NULL DEFAULT 0, -- Campo contendo o valor do ChargeBack ou Estorno.
    ug_id bigint NOT NULL, -- Campo contendo o ID do usuário (LAN ou GAMER).
    ec_tipo smallint NOT NULL, -- Campo contendo o tipo. Onde: 1 => ChargeBack e 2 => Estorno.
    ec_nome character varying(256) NOT NULL, -- Campo contendo o nome do solicitante.
    vg_id bigint NOT NULL, -- Campo contendo o ID do pedido (LAN ou GAMER).
    opr_codigo integer NOT NULL, -- Campo contendo o ID do Publisher.
    ec_data_nascimento timestamp with time zone, -- Campo contendo a data de nascimento do solicitante
    ec_cpf character varying(14), -- Campo contendo o CPF do solicitante
    ec_telefone character varying(15), -- Campo contendo o telefone do solicitante
    ec_email character varying(256), -- Campo contendo o e-mail do solicitante
    ec_data_pedido timestamp with time zone, -- Campo contendo a data do pedido
    ec_pin character varying(60), -- Campo contendo o PIN
    ec_ip_pedido character varying(15), -- Campo contendo o IP do pedido
    ec_cod_autorizacao character varying(60), -- Campo contendo o código de autorização
    ec_tid character varying(60), -- Campo contendo o TID
    ec_cod_boleto character varying(20), -- Campo contendo o código do boleto
    ec_cod_deposito character varying(20), -- Campo contendo o código do depósito
    ec_forma_devolucao smallint, -- Campo contendo a forma de devolução no caso de Estorno e usuário ser LAN. Onde: 1 => Devolução em Saldo e 2 => Devolução através de Depósito.
    */
        
    private $ec_id;
    private $ec_data_devolucao;
    private $ec_pin_bloqueado;
    private $cec_id;
    private $ec_tipo_usuario;
    private $ec_valor;
    private $ug_id;
    private $ec_tipo;
    private $ec_nome;
    private $vg_id;
    private $opr_codigo;
    private $ec_data_nascimento;
    private $ec_cpf;
    private $ec_telefone;
    private $ec_email;
    private $ec_data_pedido;
    private $ec_pin;
    private $ec_ip_pedido;
    private $ec_cod_autorizacao;
    private $ec_tid;
    private $ec_cod_boleto;
    private $ec_cod_deposito;
    private $ec_forma_devolucao;
    public $dados = array(
		'ec_id'             => null,
		'ec_data_devolucao' => null,
		'ec_pin_bloqueado'  => null,
                'cec_id'            => null,
		'ec_tipo_usuario'   => null,
		'ec_valor'          => null,
		'ug_id'             => null,
		'ec_tipo'           => null,
		'ec_nome'           => null,
		'vg_id'             => null,
		'opr_codigo'        => null,
		'ec_data_nascimento'=> null,
		'ec_cpf'            => null,
		'ec_telefone'       => null,
		'ec_email'          => null,
		'ec_data_pedido'    => null,
		'ec_pin'            => null,
		'ec_ip_pedido'      => null,
		'ec_cod_autorizacao'=> null,
		'ec_tid'            => null,
		'ec_cod_boleto'     => null,
		'ec_cod_deposito'   => null,
		'ec_forma_devolucao'=> null,
        	);
        
    public function __construct($dados = null) {
        
            if(is_array($dados)){
                foreach ($dados as $key => $value) {
                        if($this->isCampoTabela($key)) {
                                $this->dados[$key] = $value;
                        }//end if($this->isCampoTabela($key))
                }//end foreach
            }//end if(is_array($dados))
            $this->setId($this->dados['ec_id']);
            $this->setDataDevolucao($this->dados['ec_data_devolucao']);
            $this->setPinBloqueado($this->dados['ec_pin_bloqueado']);
            $this->setMotivo($this->dados['cec_id']);
            $this->setTipoUsuario($this->dados['ec_tipo_usuario']);
            $this->setValor($this->dados['ec_valor']);
            $this->setUgId($this->dados['ug_id']);
            $this->setTipo($this->dados['ec_tipo']);
            $this->setNome($this->dados['ec_nome']);
            $this->setIdVenda($this->dados['vg_id']);
            $this->setIdPublisher($this->dados['opr_codigo']);
            $this->setDataNascimento($this->dados['ec_data_nascimento']);
            $this->setCPF($this->dados['ec_cpf']);
            $this->setTelefone($this->dados['ec_telefone']);
            $this->setEmail($this->dados['ec_email']);
            $this->setDataPedido($this->dados['ec_data_pedido']);
            $this->setPin($this->dados['ec_pin']);
            $this->setIpPedido($this->dados['ec_ip_pedido']);
            $this->setCodAutorizacao($this->dados['ec_cod_autorizacao']);
            $this->setTID($this->dados['ec_tid']);
            $this->setCodBoleto($this->dados['ec_cod_boleto']);
            $this->setCodDeposito($this->dados['ec_cod_deposito']);
            $this->setFormaDevolucao($this->dados['ec_forma_devolucao']);
            
    } //end function __construct
    
    public function getId() {
        return $this->ec_id;
    }

    public function setId($ec_id) {
        $this->ec_id = $ec_id;
        return $this;
    }

    public function getDataDevolucao() {
        return $this->ec_data_devolucao;
    }

    public function setDataDevolucao($ec_data_devolucao) {
        $this->ec_data_devolucao = $ec_data_devolucao;
        return $this;
    }

    public function getPinBloqueado() {
        return $this->ec_pin_bloqueado;
    }

    public function setPinBloqueado($ec_pin_bloqueado) {
        $this->ec_pin_bloqueado = $ec_pin_bloqueado;
        return $this;
    }

    public function getMotivo() {
        return $this->cec_id;
    }

    public function setMotivo($cec_id) {
        $this->cec_id = $cec_id;
        return $this;
    }

    public function getTipoUsuario() {
        return $this->ec_tipo_usuario;
    }

    public function setTipoUsuario($ec_tipo_usuario) {
        $this->ec_tipo_usuario = $ec_tipo_usuario;
        return $this;
    }

    public function getValor() {
        return $this->ec_valor;
    }

    public function setValor($ec_valor) {
        $this->ec_valor = $ec_valor;
        return $this;
    }

    public function getUgId() {
        return $this->ug_id;
    }

    public function setUgId($ug_id) {
        $this->ug_id = $ug_id;
        return $this;
    }

    public function getTipo() {
        return $this->ec_tipo;
    }

    public function setTipo($ec_tipo) {
        $this->ec_tipo = $ec_tipo;
        return $this;
    }

    public function getNome() {
        return $this->ec_nome;
    }

    public function setNome($ec_nome) {
        $this->ec_nome = $ec_nome;
        return $this;
    }

    public function getIdVenda() {
        return $this->vg_id;
    }

    public function setIdVenda($vg_id) {
        $this->vg_id = $vg_id;
        return $this;
    }

    public function getIdPublisher() {
        return $this->opr_codigo;
    }

    public function setIdPublisher($opr_codigo) {
        $this->opr_codigo = $opr_codigo;
        return $this;
    }

    public function getDataNascimento() {
        return $this->ec_data_nascimento;
    }

    public function setDataNascimento($ec_data_nascimento) {
        $this->ec_data_nascimento = $ec_data_nascimento;
        return $this;
    }

    public function getCPF() {
        return $this->ec_cpf;
    }

    public function setCPF($ec_cpf) {
        $this->ec_cpf = $ec_cpf;
        return $this;
    }

    public function getTefefone() {
        return $this->ec_telefone;
    }

    public function setTelefone($ec_telefone) {
        $this->ec_telefone = $ec_telefone;
        return $this;
    }

    public function getEmail() {
        return $this->ec_email;
    }

    public function setEmail($ec_email) {
        $this->ec_email = $ec_email;
        return $this;
    }

    public function getDataPedido() {
        return $this->ec_data_pedido;
    }

    public function setDataPedido($ec_data_pedido) {
        $this->ec_data_pedido = $ec_data_pedido;
        return $this;
    }

    public function getPin() {
        return $this->ec_pin;
    }

    public function setPin($ec_pin) {
        $this->ec_pin = $ec_pin;
        return $this;
    }

    public function getIpPedido() {
        return $this->ec_ip_pedido;
    }

    public function setIpPedido($ec_ip_pedido) {
        $this->ec_ip_pedido = $ec_ip_pedido;
        return $this;
    }

    public function getCodAutorizacao() {
        return $this->ec_cod_autorizacao;
    }

    public function setCodAutorizacao($ec_cod_autorizacao) {
        $this->ec_cod_autorizacao = $ec_cod_autorizacao;
        return $this;
    }

    public function getTID() {
        return $this->ec_tid;
    }

    public function setTID($ec_tid) {
        $this->ec_tid = $ec_tid;
        return $this;
    }

    public function getCodBoleto() {
        return $this->ec_cod_boleto;
    }

    public function setCodBoleto($ec_cod_boleto) {
        $this->ec_cod_boleto = $ec_cod_boleto;
        return $this;
    }

    public function getCodDeposito() {
        return $this->ec_cod_deposito;
    }

    public function setCodDeposito($ec_cod_deposito) {
        $this->ec_cod_deposito = $ec_cod_deposito;
        return $this;
    }

    public function getFormaDevolucao() {
        return $this->ec_forma_devolucao;
    }

    public function setFormaDevolucao($ec_forma_devolucao) {
        $this->ec_forma_devolucao = $ec_forma_devolucao;
        return $this;
    }

     public function isCampoTabela($campo) {
        $retorno = false;
        foreach ($this->dados as $key => $value) {
                if($key == $campo) {
                    $retorno = true;
                }//end if($key == $campo)
        }//end foreach
        return $retorno;
    }//end function isCampoTabela

   
} //end Class
