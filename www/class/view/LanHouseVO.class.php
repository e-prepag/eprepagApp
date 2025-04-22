<?php

/**
 * Classe para os atributos de lan house
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 04-08-2015
 */

class LanHouseVO{
    
    private $_id;
    private $_login;
    private $_senha;
    private $_ativo;
    private $_data_inclusao;
    private $_data_ultimo_acesso;
    private $_qtde_acessos;
    private $_nome_fantasia;
    private $_razao_social;
    private $_cnpj;
    private $_responsavel;
    private $_email;
    private $_endereco;
    private $_numero;
    private $_complemento;
    private $_bairro;
    private $_cidade;
    private $_estado;
    private $_cep;
    private $_tel_ddi;
    private $_tel_ddd;
    private $_tel;
    private $_cel_ddi;
    private $_cel_ddd;
    private $_cel;
    private $_fax_ddi;
    private $_fax_ddd;
    private $_fax;
    private $_ra_codigo;
    private $_ra_outros;
    private $_contato01_nome;
    private $_contato01_cargo;
    private $_contato01_tel_ddi;
    private $_contato01_tel_ddd;
    private $_contato01_tel;
    private $_observacoes;
    private $_tipo_cadastro;
    private $_nome;
    private $_rg;
    private $_cpf;
    private $_data_nascimento;
    private $_sexo;
    private $_perfil_senha_reimpressao;
    private $_perfil_forma_pagto;
    private $_perfil_limite;
    private $_inscr_estadual;
    private $_site;
    private $_abertura_ano;
    private $_abertura_mes;
    private $_cartoes;
    private $_fatura_media_mensal;
    private $_repr_legal_nome;
    private $_repr_legal_rg;
    private $_repr_legal_cpf;
    private $_repr_legal_tel_ddi;
    private $_repr_legal_tel_ddd;
    private $_repr_legal_tel;
    private $_repr_legal_cel_ddi;
    private $_repr_legal_cel_ddd;
    private $_repr_legal_cel;
    private $_repr_legal_email;
    private $_repr_legal_msn;
    private $_repr_venda_nome;
    private $_repr_venda_rg;
    private $_repr_venda_cpf;
    private $_repr_venda_tel_ddi;
    private $_repr_venda_tel_ddd;
    private $_repr_venda_tel;
    private $_repr_venda_cel_ddi;
    private $_repr_venda_cel_ddd;
    private $_repr_venda_cel;
    private $_repr_venda_email;
    private $_repr_venda_msn;
    private $_dados_bancarios_01_banco;
    private $_dados_bancarios_01_agencia;
    private $_dados_bancarios_01_conta;
    private $_dados_bancarios_01_abertura;
    private $_dados_bancarios_02_banco;
    private $_dados_bancarios_02_agencia;
    private $_dados_bancarios_02_conta;
    private $_dados_bancarios_02_abertura;
    private $_computadores_qtde;
    private $_comunicacao_visual;
    private $_perfil_saldo;
    private $_repr_venda_igual_repr_legal;
    private $_perfil_corte_dia_semana;
    private $_perfil_corte_ultimo_corte;
    private $_perfil_limite_sugerido;
    private $_credito_pendente;
    private $_news;
    private $_risco_classif;
    private $_perfil_limite_ref;
    private $_usuario_cartao;
    private $_busca;
    private $_usuario_novo;
    private $_data_email_saldo;
    private $_faixa_email_saldo;
    private $_ficou_sabendo;
    private $_status;
    private $_tipo_end;
    private $_substatus;
    private $_contatada_ultimo_mes;
    private $_ordem;
    private $_compet_participa;
    private $_compet_promoveu;
    private $_compet_participantes_fifa;
    private $_compet_participantes_wc3;
    private $_substatus_pag_online;
    private $_coord_lat;
    private $_coord_lng;
    private $_google_maps_string;
    private $_google_maps_status;
    private $_compet_aceito_data_aceito;
    private $_ongame;
    private $_te_id;
    private $_id_nexcafe;
    private $_login_nexcafe_auto;
    private $_data_inclusao_nexcafe;
    private $_alterar_senha;
    private $_exibir_contrato;
    private $_data_aceite_adesao;
    private $_recarga_celular;
    private $_vip;
    private $_data_envio_saldo_minimo;
    private $_tipo_venda;
    private $_possui_restricao_produtos;
    
    public function getId() {
        return $this->_id;
    }

    public function getLogin() {
        return $this->_login;
    }

    public function getSenha() {
        return $this->_senha;
    }

    public function getAtivo() {
        return $this->_ativo;
    }

    public function getData_inclusao() {
        return $this->_data_inclusao;
    }

    public function getData_ultimo_acesso() {
        return $this->_data_ultimo_acesso;
    }

    public function getQtde_acessos() {
        return $this->_qtde_acessos;
    }

    public function getNome_fantasia() {
        return $this->_nome_fantasia;
    }

    public function getRazao_social() {
        return $this->_razao_social;
    }

    public function getCnpj() {
        return $this->_cnpj;
    }

    public function getResponsavel() {
        return $this->_responsavel;
    }

    public function getEmail() {
        return $this->_email;
    }

    public function getEndereco() {
        return $this->_endereco;
    }

    public function getNumero() {
        return $this->_numero;
    }

    public function getComplemento() {
        return $this->_complemento;
    }

    public function getBairro() {
        return $this->_bairro;
    }

    public function getCidade() {
        return $this->_cidade;
    }

    public function getEstado() {
        return $this->_estado;
    }

    public function getCep() {
        return $this->_cep;
    }

    public function getTel_ddi() {
        return $this->_tel_ddi;
    }

    public function getTel_ddd() {
        return $this->_tel_ddd;
    }

    public function getTel() {
        return $this->_tel;
    }

    public function getCel_ddi() {
        return $this->_cel_ddi;
    }

    public function getCel_ddd() {
        return $this->_cel_ddd;
    }

    public function getCel() {
        return $this->_cel;
    }

    public function getFax_ddi() {
        return $this->_fax_ddi;
    }

    public function getFax_ddd() {
        return $this->_fax_ddd;
    }

    public function getFax() {
        return $this->_fax;
    }

    public function getRa_codigo() {
        return $this->_ra_codigo;
    }

    public function getRa_outros() {
        return $this->_ra_outros;
    }

    public function getContato01_nome() {
        return $this->_contato01_nome;
    }

    public function getContato01_cargo() {
        return $this->_contato01_cargo;
    }

    public function getContato01_tel_ddi() {
        return $this->_contato01_tel_ddi;
    }

    public function getContato01_tel_ddd() {
        return $this->_contato01_tel_ddd;
    }

    public function getContato01_tel() {
        return $this->_contato01_tel;
    }

    public function getObservacoes() {
        return $this->_observacoes;
    }

    public function getTipo_cadastro() {
        return $this->_tipo_cadastro;
    }

    public function getNome() {
        return $this->_nome;
    }

    public function getRg() {
        return $this->_rg;
    }

    public function getCpf() {
        return $this->_cpf;
    }

    public function getData_nascimento() {
        return $this->_data_nascimento;
    }

    public function getSexo() {
        return $this->_sexo;
    }

    public function getPerfil_senha_reimpressao() {
        return $this->_perfil_senha_reimpressao;
    }

    public function getPerfil_forma_pagto() {
        return $this->_perfil_forma_pagto;
    }

    public function getPerfil_limite() {
        return $this->_perfil_limite;
    }

    public function getInscr_estadual() {
        return $this->_inscr_estadual;
    }

    public function getSite() {
        return $this->_site;
    }

    public function getAbertura_ano() {
        return $this->_abertura_ano;
    }

    public function getAbertura_mes() {
        return $this->_abertura_mes;
    }

    public function getCartoes() {
        return $this->_cartoes;
    }

    public function getFatura_media_mensal() {
        return $this->_fatura_media_mensal;
    }

    public function getRepr_legal_nome() {
        return $this->_repr_legal_nome;
    }

    public function getRepr_legal_rg() {
        return $this->_repr_legal_rg;
    }

    public function getRepr_legal_cpf() {
        return $this->_repr_legal_cpf;
    }

    public function getRepr_legal_tel_ddi() {
        return $this->_repr_legal_tel_ddi;
    }

    public function getRepr_legal_tel_ddd() {
        return $this->_repr_legal_tel_ddd;
    }

    public function getRepr_legal_tel() {
        return $this->_repr_legal_tel;
    }

    public function getRepr_legal_cel_ddi() {
        return $this->_repr_legal_cel_ddi;
    }

    public function getRepr_legal_cel_ddd() {
        return $this->_repr_legal_cel_ddd;
    }

    public function getRepr_legal_cel() {
        return $this->_repr_legal_cel;
    }

    public function getRepr_legal_email() {
        return $this->_repr_legal_email;
    }

    public function getRepr_legal_msn() {
        return $this->_repr_legal_msn;
    }

    public function getRepr_venda_nome() {
        return $this->_repr_venda_nome;
    }

    public function getRepr_venda_rg() {
        return $this->_repr_venda_rg;
    }

    public function getRepr_venda_cpf() {
        return $this->_repr_venda_cpf;
    }

    public function getRepr_venda_tel_ddi() {
        return $this->_repr_venda_tel_ddi;
    }

    public function getRepr_venda_tel_ddd() {
        return $this->_repr_venda_tel_ddd;
    }

    public function getRepr_venda_tel() {
        return $this->_repr_venda_tel;
    }

    public function getRepr_venda_cel_ddi() {
        return $this->_repr_venda_cel_ddi;
    }

    public function getRepr_venda_cel_ddd() {
        return $this->_repr_venda_cel_ddd;
    }

    public function getRepr_venda_cel() {
        return $this->_repr_venda_cel;
    }

    public function getRepr_venda_email() {
        return $this->_repr_venda_email;
    }

    public function getRepr_venda_msn() {
        return $this->_repr_venda_msn;
    }

    public function getDados_bancarios_01_banco() {
        return $this->_dados_bancarios_01_banco;
    }

    public function getDados_bancarios_01_agencia() {
        return $this->_dados_bancarios_01_agencia;
    }

    public function getDados_bancarios_01_conta() {
        return $this->_dados_bancarios_01_conta;
    }

    public function getDados_bancarios_01_abertura() {
        return $this->_dados_bancarios_01_abertura;
    }

    public function getDados_bancarios_02_banco() {
        return $this->_dados_bancarios_02_banco;
    }

    public function getDados_bancarios_02_agencia() {
        return $this->_dados_bancarios_02_agencia;
    }

    public function getDados_bancarios_02_conta() {
        return $this->_dados_bancarios_02_conta;
    }

    public function getDados_bancarios_02_abertura() {
        return $this->_dados_bancarios_02_abertura;
    }

    public function getComputadores_qtde() {
        return $this->_computadores_qtde;
    }

    public function getComunicacao_visual() {
        return $this->_comunicacao_visual;
    }

    public function getPerfil_saldo() {
        return $this->_perfil_saldo;
    }

    public function getRepr_venda_igual_repr_legal() {
        return $this->_repr_venda_igual_repr_legal;
    }

    public function getPerfil_corte_dia_semana() {
        return $this->_perfil_corte_dia_semana;
    }

    public function getPerfil_corte_ultimo_corte() {
        return $this->_perfil_corte_ultimo_corte;
    }

    public function getPerfil_limite_sugerido() {
        return $this->_perfil_limite_sugerido;
    }

    public function getCredito_pendente() {
        return $this->_credito_pendente;
    }

    public function getNews() {
        return $this->_news;
    }

    public function getRisco_classif() {
        return $this->_risco_classif;
    }

    public function getPerfil_limite_ref() {
        return $this->_perfil_limite_ref;
    }

    public function getUsuario_cartao() {
        return $this->_usuario_cartao;
    }

    public function getBusca() {
        return $this->_busca;
    }

    public function getUsuario_novo() {
        return $this->_usuario_novo;
    }

    public function getData_email_saldo() {
        return $this->_data_email_saldo;
    }

    public function getFaixa_email_saldo() {
        return $this->_faixa_email_saldo;
    }

    public function getFicou_sabendo() {
        return $this->_ficou_sabendo;
    }

    public function getStatus() {
        return $this->_status;
    }

    public function getTipo_end() {
        return $this->_tipo_end;
    }

    public function getSubstatus() {
        return $this->_substatus;
    }

    public function getContatada_ultimo_mes() {
        return $this->_contatada_ultimo_mes;
    }

    public function getOrdem() {
        return $this->_ordem;
    }

    public function getCompet_participa() {
        return $this->_compet_participa;
    }

    public function getCompet_promoveu() {
        return $this->_compet_promoveu;
    }

    public function getCompet_participantes_fifa() {
        return $this->_compet_participantes_fifa;
    }

    public function getCompet_participantes_wc3() {
        return $this->_compet_participantes_wc3;
    }

    public function getSubstatus_pag_online() {
        return $this->_substatus_pag_online;
    }

    public function getCoord_lat() {
        return $this->_coord_lat;
    }

    public function getCoord_lng() {
        return $this->_coord_lng;
    }

    public function getGoogle_maps_string() {
        return $this->_google_maps_string;
    }

    public function getGoogle_maps_status() {
        return $this->_google_maps_status;
    }

    public function getCompet_aceito_data_aceito() {
        return $this->_compet_aceito_data_aceito;
    }

    public function getOngame() {
        return $this->_ongame;
    }

    public function getTe_id() {
        return $this->_te_id;
    }

    public function getId_nexcafe() {
        return $this->_id_nexcafe;
    }

    public function getLogin_nexcafe_auto() {
        return $this->_login_nexcafe_auto;
    }

    public function getData_inclusao_nexcafe() {
        return $this->_data_inclusao_nexcafe;
    }

    public function getAlterar_senha() {
        return $this->_alterar_senha;
    }

    public function getExibir_contrato() {
        return $this->_exibir_contrato;
    }

    public function getData_aceite_adesao() {
        return $this->_data_aceite_adesao;
    }

    public function getRecarga_celular() {
        return $this->_recarga_celular;
    }

    public function getVip() {
        return $this->_vip;
    }

    public function getData_envio_saldo_minimo() {
        return $this->_data_envio_saldo_minimo;
    }

    public function getTipo_venda() {
        return $this->_tipo_venda;
    }

    public function getPossui_restricao_produtos() {
        return $this->_possui_restricao_produtos;
    }

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

    public function setLogin($login) {
        $this->_login = $login;
        return $this;
    }

    public function setSenha($senha) {
        $this->_senha = $senha;
        return $this;
    }

    public function setAtivo($ativo) {
        $this->_ativo = $ativo;
        return $this;
    }

    public function setData_inclusao($data_inclusao) {
        $this->_data_inclusao = $data_inclusao;
        return $this;
    }

    public function setData_ultimo_acesso($data_ultimo_acesso) {
        $this->_data_ultimo_acesso = $data_ultimo_acesso;
        return $this;
    }

    public function setQtde_acessos($qtde_acessos) {
        $this->_qtde_acessos = $qtde_acessos;
        return $this;
    }

    public function setNome_fantasia($nome_fantasia) {
        $this->_nome_fantasia = $nome_fantasia;
        return $this;
    }

    public function setRazao_social($razao_social) {
        $this->_razao_social = $razao_social;
        return $this;
    }

    public function setCnpj($cnpj) {
        $this->_cnpj = $cnpj;
        return $this;
    }

    public function setResponsavel($responsavel) {
        $this->_responsavel = $responsavel;
        return $this;
    }

    public function setEmail($email) {
        $this->_email = $email;
        return $this;
    }

    public function setEndereco($endereco) {
        $this->_endereco = $endereco;
        return $this;
    }

    public function setNumero($numero) {
        $this->_numero = $numero;
        return $this;
    }

    public function setComplemento($complemento) {
        $this->_complemento = $complemento;
        return $this;
    }

    public function setBairro($bairro) {
        $this->_bairro = $bairro;
        return $this;
    }

    public function setCidade($cidade) {
        $this->_cidade = $cidade;
        return $this;
    }

    public function setEstado($estado) {
        $this->_estado = $estado;
        return $this;
    }

    public function setCep($cep) {
        $this->_cep = $cep;
        return $this;
    }

    public function setTel_ddi($tel_ddi) {
        $this->_tel_ddi = $tel_ddi;
        return $this;
    }

    public function setTel_ddd($tel_ddd) {
        $this->_tel_ddd = $tel_ddd;
        return $this;
    }

    public function setTel($tel) {
        $this->_tel = $tel;
        return $this;
    }

    public function setCel_ddi($cel_ddi) {
        $this->_cel_ddi = $cel_ddi;
        return $this;
    }

    public function setCel_ddd($cel_ddd) {
        $this->_cel_ddd = $cel_ddd;
        return $this;
    }

    public function setCel($cel) {
        $this->_cel = $cel;
        return $this;
    }

    public function setFax_ddi($fax_ddi) {
        $this->_fax_ddi = $fax_ddi;
        return $this;
    }

    public function setFax_ddd($fax_ddd) {
        $this->_fax_ddd = $fax_ddd;
        return $this;
    }

    public function setFax($fax) {
        $this->_fax = $fax;
        return $this;
    }

    public function setRa_codigo($ra_codigo) {
        $this->_ra_codigo = $ra_codigo;
        return $this;
    }

    public function setRa_outros($ra_outros) {
        $this->_ra_outros = $ra_outros;
        return $this;
    }

    public function setContato01_nome($contato01_nome) {
        $this->_contato01_nome = $contato01_nome;
        return $this;
    }

    public function setContato01_cargo($contato01_cargo) {
        $this->_contato01_cargo = $contato01_cargo;
        return $this;
    }

    public function setContato01_tel_ddi($contato01_tel_ddi) {
        $this->_contato01_tel_ddi = $contato01_tel_ddi;
        return $this;
    }

    public function setContato01_tel_ddd($contato01_tel_ddd) {
        $this->_contato01_tel_ddd = $contato01_tel_ddd;
        return $this;
    }

    public function setContato01_tel($contato01_tel) {
        $this->_contato01_tel = $contato01_tel;
        return $this;
    }

    public function setObservacoes($observacoes) {
        $this->_observacoes = $observacoes;
        return $this;
    }

    public function setTipo_cadastro($tipo_cadastro) {
        $this->_tipo_cadastro = $tipo_cadastro;
        return $this;
    }

    public function setNome($nome) {
        $this->_nome = $nome;
        return $this;
    }

    public function setRg($rg) {
        $this->_rg = $rg;
        return $this;
    }

    public function setCpf($cpf) {
        $this->_cpf = $cpf;
        return $this;
    }

    public function setData_nascimento($data_nascimento) {
        $this->_data_nascimento = $data_nascimento;
        return $this;
    }

    public function setSexo($sexo) {
        $this->_sexo = $sexo;
        return $this;
    }

    public function setPerfil_senha_reimpressao($perfil_senha_reimpressao) {
        $this->_perfil_senha_reimpressao = $perfil_senha_reimpressao;
        return $this;
    }

    public function setPerfil_forma_pagto($perfil_forma_pagto) {
        $this->_perfil_forma_pagto = $perfil_forma_pagto;
        return $this;
    }

    public function setPerfil_limite($perfil_limite) {
        $this->_perfil_limite = $perfil_limite;
        return $this;
    }

    public function setInscr_estadual($inscr_estadual) {
        $this->_inscr_estadual = $inscr_estadual;
        return $this;
    }

    public function setSite($site) {
        $this->_site = $site;
        return $this;
    }

    public function setAbertura_ano($abertura_ano) {
        $this->_abertura_ano = $abertura_ano;
        return $this;
    }

    public function setAbertura_mes($abertura_mes) {
        $this->_abertura_mes = $abertura_mes;
        return $this;
    }

    public function setCartoes($cartoes) {
        $this->_cartoes = $cartoes;
        return $this;
    }

    public function setFatura_media_mensal($fatura_media_mensal) {
        $this->_fatura_media_mensal = $fatura_media_mensal;
        return $this;
    }

    public function setRepr_legal_nome($repr_legal_nome) {
        $this->_repr_legal_nome = $repr_legal_nome;
        return $this;
    }

    public function setRepr_legal_rg($repr_legal_rg) {
        $this->_repr_legal_rg = $repr_legal_rg;
        return $this;
    }

    public function setRepr_legal_cpf($repr_legal_cpf) {
        $this->_repr_legal_cpf = $repr_legal_cpf;
        return $this;
    }

    public function setRepr_legal_tel_ddi($repr_legal_tel_ddi) {
        $this->_repr_legal_tel_ddi = $repr_legal_tel_ddi;
        return $this;
    }

    public function setRepr_legal_tel_ddd($repr_legal_tel_ddd) {
        $this->_repr_legal_tel_ddd = $repr_legal_tel_ddd;
        return $this;
    }

    public function setRepr_legal_tel($repr_legal_tel) {
        $this->_repr_legal_tel = $repr_legal_tel;
        return $this;
    }

    public function setRepr_legal_cel_ddi($repr_legal_cel_ddi) {
        $this->_repr_legal_cel_ddi = $repr_legal_cel_ddi;
        return $this;
    }

    public function setRepr_legal_cel_ddd($repr_legal_cel_ddd) {
        $this->_repr_legal_cel_ddd = $repr_legal_cel_ddd;
        return $this;
    }

    public function setRepr_legal_cel($repr_legal_cel) {
        $this->_repr_legal_cel = $repr_legal_cel;
        return $this;
    }

    public function setRepr_legal_email($repr_legal_email) {
        $this->_repr_legal_email = $repr_legal_email;
        return $this;
    }

    public function setRepr_legal_msn($repr_legal_msn) {
        $this->_repr_legal_msn = $repr_legal_msn;
        return $this;
    }

    public function setRepr_venda_nome($repr_venda_nome) {
        $this->_repr_venda_nome = $repr_venda_nome;
        return $this;
    }

    public function setRepr_venda_rg($repr_venda_rg) {
        $this->_repr_venda_rg = $repr_venda_rg;
        return $this;
    }

    public function setRepr_venda_cpf($repr_venda_cpf) {
        $this->_repr_venda_cpf = $repr_venda_cpf;
        return $this;
    }

    public function setRepr_venda_tel_ddi($repr_venda_tel_ddi) {
        $this->_repr_venda_tel_ddi = $repr_venda_tel_ddi;
        return $this;
    }

    public function setRepr_venda_tel_ddd($repr_venda_tel_ddd) {
        $this->_repr_venda_tel_ddd = $repr_venda_tel_ddd;
        return $this;
    }

    public function setRepr_venda_tel($repr_venda_tel) {
        $this->_repr_venda_tel = $repr_venda_tel;
        return $this;
    }

    public function setRepr_venda_cel_ddi($repr_venda_cel_ddi) {
        $this->_repr_venda_cel_ddi = $repr_venda_cel_ddi;
        return $this;
    }

    public function setRepr_venda_cel_ddd($repr_venda_cel_ddd) {
        $this->_repr_venda_cel_ddd = $repr_venda_cel_ddd;
        return $this;
    }

    public function setRepr_venda_cel($repr_venda_cel) {
        $this->_repr_venda_cel = $repr_venda_cel;
        return $this;
    }

    public function setRepr_venda_email($repr_venda_email) {
        $this->_repr_venda_email = $repr_venda_email;
        return $this;
    }

    public function setRepr_venda_msn($repr_venda_msn) {
        $this->_repr_venda_msn = $repr_venda_msn;
        return $this;
    }

    public function setDados_bancarios_01_banco($dados_bancarios_01_banco) {
        $this->_dados_bancarios_01_banco = $dados_bancarios_01_banco;
        return $this;
    }

    public function setDados_bancarios_01_agencia($dados_bancarios_01_agencia) {
        $this->_dados_bancarios_01_agencia = $dados_bancarios_01_agencia;
        return $this;
    }

    public function setDados_bancarios_01_conta($dados_bancarios_01_conta) {
        $this->_dados_bancarios_01_conta = $dados_bancarios_01_conta;
        return $this;
    }

    public function setDados_bancarios_01_abertura($dados_bancarios_01_abertura) {
        $this->_dados_bancarios_01_abertura = $dados_bancarios_01_abertura;
        return $this;
    }

    public function setDados_bancarios_02_banco($dados_bancarios_02_banco) {
        $this->_dados_bancarios_02_banco = $dados_bancarios_02_banco;
        return $this;
    }

    public function setDados_bancarios_02_agencia($dados_bancarios_02_agencia) {
        $this->_dados_bancarios_02_agencia = $dados_bancarios_02_agencia;
        return $this;
    }

    public function setDados_bancarios_02_conta($dados_bancarios_02_conta) {
        $this->_dados_bancarios_02_conta = $dados_bancarios_02_conta;
        return $this;
    }

    public function setDados_bancarios_02_abertura($dados_bancarios_02_abertura) {
        $this->_dados_bancarios_02_abertura = $dados_bancarios_02_abertura;
        return $this;
    }

    public function setComputadores_qtde($computadores_qtde) {
        $this->_computadores_qtde = $computadores_qtde;
        return $this;
    }

    public function setComunicacao_visual($comunicacao_visual) {
        $this->_comunicacao_visual = $comunicacao_visual;
        return $this;
    }

    public function setPerfil_saldo($perfil_saldo) {
        $this->_perfil_saldo = $perfil_saldo;
        return $this;
    }

    public function setRepr_venda_igual_repr_legal($repr_venda_igual_repr_legal) {
        $this->_repr_venda_igual_repr_legal = $repr_venda_igual_repr_legal;
        return $this;
    }

    public function setPerfil_corte_dia_semana($perfil_corte_dia_semana) {
        $this->_perfil_corte_dia_semana = $perfil_corte_dia_semana;
        return $this;
    }

    public function setPerfil_corte_ultimo_corte($perfil_corte_ultimo_corte) {
        $this->_perfil_corte_ultimo_corte = $perfil_corte_ultimo_corte;
        return $this;
    }

    public function setPerfil_limite_sugerido($perfil_limite_sugerido) {
        $this->_perfil_limite_sugerido = $perfil_limite_sugerido;
        return $this;
    }

    public function setCredito_pendente($credito_pendente) {
        $this->_credito_pendente = $credito_pendente;
        return $this;
    }

    public function setNews($news) {
        $this->_news = $news;
        return $this;
    }

    public function setRisco_classif($risco_classif) {
        $this->_risco_classif = $risco_classif;
        return $this;
    }

    public function setPerfil_limite_ref($perfil_limite_ref) {
        $this->_perfil_limite_ref = $perfil_limite_ref;
        return $this;
    }

    public function setUsuario_cartao($usuario_cartao) {
        $this->_usuario_cartao = $usuario_cartao;
        return $this;
    }

    public function setBusca($busca) {
        $this->_busca = $busca;
        return $this;
    }

    public function setUsuario_novo($usuario_novo) {
        $this->_usuario_novo = $usuario_novo;
        return $this;
    }

    public function setData_email_saldo($data_email_saldo) {
        $this->_data_email_saldo = $data_email_saldo;
        return $this;
    }

    public function setFaixa_email_saldo($faixa_email_saldo) {
        $this->_faixa_email_saldo = $faixa_email_saldo;
        return $this;
    }

    public function setFicou_sabendo($ficou_sabendo) {
        $this->_ficou_sabendo = $ficou_sabendo;
        return $this;
    }

    public function setStatus($status) {
        $this->_status = $status;
        return $this;
    }

    public function setTipo_end($tipo_end) {
        $this->_tipo_end = $tipo_end;
        return $this;
    }

    public function setSubstatus($substatus) {
        $this->_substatus = $substatus;
        return $this;
    }

    public function setContatada_ultimo_mes($contatada_ultimo_mes) {
        $this->_contatada_ultimo_mes = $contatada_ultimo_mes;
        return $this;
    }

    public function setOrdem($ordem) {
        $this->_ordem = $ordem;
        return $this;
    }

    public function setCompet_participa($compet_participa) {
        $this->_compet_participa = $compet_participa;
        return $this;
    }

    public function setCompet_promoveu($compet_promoveu) {
        $this->_compet_promoveu = $compet_promoveu;
        return $this;
    }

    public function setCompet_participantes_fifa($compet_participantes_fifa) {
        $this->_compet_participantes_fifa = $compet_participantes_fifa;
        return $this;
    }

    public function setCompet_participantes_wc3($compet_participantes_wc3) {
        $this->_compet_participantes_wc3 = $compet_participantes_wc3;
        return $this;
    }

    public function setSubstatus_pag_online($substatus_pag_online) {
        $this->_substatus_pag_online = $substatus_pag_online;
        return $this;
    }

    public function setCoord_lat($coord_lat) {
        $this->_coord_lat = $coord_lat;
        return $this;
    }

    public function setCoord_lng($coord_lng) {
        $this->_coord_lng = $coord_lng;
        return $this;
    }

    public function setGoogle_maps_string($google_maps_string) {
        $this->_google_maps_string = $google_maps_string;
        return $this;
    }

    public function setGoogle_maps_status($google_maps_status) {
        $this->_google_maps_status = $google_maps_status;
        return $this;
    }

    public function setCompet_aceito_data_aceito($compet_aceito_data_aceito) {
        $this->_compet_aceito_data_aceito = $compet_aceito_data_aceito;
        return $this;
    }

    public function setOngame($ongame) {
        $this->_ongame = $ongame;
        return $this;
    }

    public function setTe_id($te_id) {
        $this->_te_id = $te_id;
        return $this;
    }

    public function setId_nexcafe($id_nexcafe) {
        $this->_id_nexcafe = $id_nexcafe;
        return $this;
    }

    public function setLogin_nexcafe_auto($login_nexcafe_auto) {
        $this->_login_nexcafe_auto = $login_nexcafe_auto;
        return $this;
    }

    public function setData_inclusao_nexcafe($data_inclusao_nexcafe) {
        $this->_data_inclusao_nexcafe = $data_inclusao_nexcafe;
        return $this;
    }

    public function setAlterar_senha($alterar_senha) {
        $this->_alterar_senha = $alterar_senha;
        return $this;
    }

    public function setExibir_contrato($exibir_contrato) {
        $this->_exibir_contrato = $exibir_contrato;
        return $this;
    }

    public function setData_aceite_adesao($data_aceite_adesao) {
        $this->_data_aceite_adesao = $data_aceite_adesao;
        return $this;
    }

    public function setRecarga_celular($recarga_celular) {
        $this->_recarga_celular = $recarga_celular;
        return $this;
    }

    public function setVip($vip) {
        $this->_vip = $vip;
        return $this;
    }

    public function setData_envio_saldo_minimo($data_envio_saldo_minimo) {
        $this->_data_envio_saldo_minimo = $data_envio_saldo_minimo;
        return $this;
    }

    public function setTipo_venda($tipo_venda) {
        $this->_tipo_venda = $tipo_venda;
        return $this;
    }

    public function setPossui_restricao_produtos($possui_restricao_produtos) {
        $this->_possui_restricao_produtos = $possui_restricao_produtos;
        return $this;
    }

}