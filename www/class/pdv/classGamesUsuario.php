<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
class UsuarioGames {

    var $ug_id;
    var $ug_sLogin;
    var $ug_sSenha;
    var $ug_blAtivo;
    var $ug_dDataInclusao;
    var $ug_dDataUltimoAcesso;
    var $ug_iQtdeAcessos;

    var $ug_sNomeFantasia;
    var $ug_sRazaoSocial;
    var $ug_sCNPJ;
    var $ug_sResponsavel;
    var $ug_sEmail;

    var $ug_sNews;

    var $ug_sEndereco;
    var $ug_sTipoEnd;
    var $ug_sNumero;
    var $ug_sComplemento;
    var $ug_sBairro;
    var $ug_sCidade;
    var $ug_sEstado;
    var $ug_sCEP;

    var $ug_sTelDDI;
    var $ug_sTelDDD;
    var $ug_sTel;
    var $ug_sCelDDI;
    var $ug_sCelDDD;
    var $ug_sCel;
    var $ug_sFaxDDI;
    var $ug_sFaxDDD;
    var $ug_sFax;
    
    var $ug_sRACodigo;
    var $ug_sRAOutros;
    
    var $ug_sContato01TelDDI;
    var $ug_sContato01TelDDD;
    var $ug_sContato01Tel;
    var $ug_sContato01Nome;
    var $ug_sContato01Cargo;
    
    var $ug_sObservacoes;
    var $ug_iRiscoClassif;
    
    var $ug_cTipoCadastro;
    var $ug_sNome;
    var $ug_sCPF;
    var $ug_sRG;
    var $ug_dDataNascimento;
    var $ug_cSexo;
    
    var $ug_sPerfilSenhaReimpressao;
    var $ug_iPerfilFormaPagto;
    var $ug_fPerfilLimite;
    var $ug_fPerfilSaldo;

    var $ug_s_inscr_estadual;
    var $ug_s_site;
    var $ug_i_abertura_ano;
    var $ug_i_abertura_mes;
    var $ug_s_cartoes;
    var $ug_i_fatura_media_mensal;

    var $ug_s_repr_legal_nome;
    var $ug_s_repr_legal_rg;
    var $ug_s_repr_legal_cpf;
    var $ug_repr_legal_data_nascimento;
    var $ug_s_repr_legal_tel_ddi;
    var $ug_s_repr_legal_tel_ddd;
    var $ug_s_repr_legal_tel;
    var $ug_s_repr_legal_cel_ddi;
    var $ug_s_repr_legal_cel_ddd;
    var $ug_s_repr_legal_cel;
    var $ug_s_repr_legal_email;
    var $ug_s_repr_legal_msn;

    var $ug_bl_repr_venda_igual_repr_legal;
    var $ug_s_repr_venda_nome;
    var $ug_s_repr_venda_rg;
    var $ug_s_repr_venda_cpf;
    var $ug_s_repr_venda_tel_ddi;
    var $ug_s_repr_venda_tel_ddd;
    var $ug_s_repr_venda_tel;
    var $ug_s_repr_venda_cel_ddi;
    var $ug_s_repr_venda_cel_ddd;
    var $ug_s_repr_venda_cel;
    var $ug_s_repr_venda_email;
    var $ug_s_repr_venda_msn;

    var $ug_s_dados_bancarios_01_banco;
    var $ug_s_dados_bancarios_01_agencia;
    var $ug_s_dados_bancarios_01_conta;
    var $ug_s_dados_bancarios_01_abertura;

    var $ug_s_dados_bancarios_02_banco;
    var $ug_s_dados_bancarios_02_agencia;
    var $ug_s_dados_bancarios_02_conta;
    var $ug_s_dados_bancarios_02_abertura;

    var $ug_i_computadores_qtde;
    var $ug_s_comunicacao_visual;
    
    var $ug_perfil_corte_dia_semana;
    var $ug_perfil_corte_ultimo_corte;
    var $ug_perfil_limite_sugerido;
    var $ug_perfil_limite_ref;
    var $ug_credito_pendente;

    var $ug_ficou_sabendo;

    // Tipo de venda (online / offline)
    public $ug_tipo_venda;
    
    // Dados dos sócios do PDV
    public $ug_nome_socios;
    public $ug_cpf_socios;
    public $ug_data_nascimento_socios;
    public $ug_porcentagem_socios;

    var $ug_Substatus;

    // Competicao
    var $ug_compet_participa;
    var $ug_compet_promoveu;
    var $ug_compet_participantes_fifa;
    var $ug_compet_participantes_wc3;

    //ONGAME
    var $ug_ongame;
    
    // Tipo de Estabelecimento
    var $ug_te_id;
    
    // Nexcafe
    var $ug_id_nexcafe;
    var $ug_login_nexcafe_auto;
    var $ug_data_inclusao_nexcafe;
    
    // Altera senha no próximo login
    var $ug_alterar_senha;
    
    // Exibi contrato de adesão no próximo login
    var $ug_exibir_contrato;
    
    // Data do aceite do contrato de adesão
    var $ug_data_aceite_adesao;
    
    // Recarga de Celular
    var $ug_recarga_celular;
    
    // Usuário VIP
    var $ug_vip;

    // Possui Restrição de Vendas de Produtos
    var $ug_possui_restricao_produtos;
    
    // Data de aprovação do PDV
    var $ug_data_aprovacao;
    
    // Data de expiracao da senha do usuario
    var $ug_data_expiracao_senha;
    
	var $ug_canais_venda;
    /*
      function UsuarioGames() {
      }
     */
    function UsuarioGames(    $ug_id                 = null,        
                            $ug_sLogin             = null,        
                            $ug_sSenha             = null,        
                            $ug_blAtivo         = null,        
                            $ug_blStatusBusca     = null,        
                            $ug_dDataInclusao     = null,        
                            $ug_dDataUltimoAcesso= null,    
                            $ug_iQtdeAcessos    = null,        

                            $ug_sNomeFantasia    = null,        
                            $ug_sRazaoSocial    = null,        
                            $ug_sCNPJ            = null,        
                            $ug_sResponsavel    = null,        
                            $ug_sEmail             = null,        
                            
                            $ug_sEndereco         = null,        
                            $ug_sTipoEnd         = null,        
                            $ug_sNumero         = null,        
                            $ug_sComplemento     = null,        
                            $ug_sBairro         = null,        
                            $ug_sCidade         = null,        
                            $ug_sEstado         = null,        
                            $ug_sCEP             = null,        

                            $ug_sTelDDI         = null,        
                            $ug_sTelDDD         = null,        
                            $ug_sTel             = null,        
                            $ug_sCelDDI         = null,        
                            $ug_sCelDDD         = null,        
                            $ug_sCel             = null,        
                            $ug_sFaxDDI         = null,        
                            $ug_sFaxDDD         = null,        
                            $ug_sFax             = null,        
    
                            $ug_sRACodigo        = null,        
                            $ug_sRAOutros        = null,        
                                
                            $ug_sContato01TelDDI= null,        
                            $ug_sContato01TelDDD= null,        
                            $ug_sContato01Tel    = null,        
                            $ug_sContato01Nome    = null,        
                            $ug_sContato01Cargo    = null,        
                                        
                            $ug_sObservacoes    = null,        
                            
                            $ug_cTipoCadastro    = null,        
                            $ug_sNome             = null,        
                            $ug_sCPF             = null,        
                            $ug_sRG             = null,        
                            $ug_dDataNascimento = null,        
                            $ug_cSexo             = null,        
                            
                            $ug_sPerfilSenhaReimpressao     = null,        
                            $ug_iPerfilFormaPagto            = null,        
                            $ug_fPerfilLimite                = null,        
                            $ug_fPerfilSaldo                = null,        

                            $ug_s_inscr_estadual             = null,        
                            $ug_s_site                         = null,
                            $ug_i_abertura_ano                 = null,
                            $ug_i_abertura_mes                 = null,
                            $ug_s_cartoes                     = null,
                            $ug_i_fatura_media_mensal         = null,

                            $ug_s_repr_legal_nome             = null,
                            $ug_s_repr_legal_rg             = null,
                            $ug_s_repr_legal_cpf             = null,
                            $ug_s_repr_legal_tel_ddi         = null,
                            $ug_s_repr_legal_tel_ddd         = null,
                            $ug_s_repr_legal_tel             = null,
                            $ug_s_repr_legal_cel_ddi         = null,
                            $ug_s_repr_legal_cel_ddd         = null,
                            $ug_s_repr_legal_cel             = null,
                            $ug_s_repr_legal_email             = null,
                            $ug_s_repr_legal_msn             = null,

                            $ug_bl_repr_venda_igual_repr_legal = null,
                            $ug_s_repr_venda_nome             = null,
                            $ug_s_repr_venda_rg             = null,
                            $ug_s_repr_venda_cpf             = null,
                            $ug_s_repr_venda_tel_ddi         = null,
                            $ug_s_repr_venda_tel_ddd         = null,
                            $ug_s_repr_venda_tel             = null,
                            $ug_s_repr_venda_cel_ddi         = null,
                            $ug_s_repr_venda_cel_ddd         = null,
                            $ug_s_repr_venda_cel             = null,
                            $ug_s_repr_venda_email             = null,
                            $ug_s_repr_venda_msn             = null,

                            $ug_s_dados_bancarios_01_banco         = null,
                            $ug_s_dados_bancarios_01_agencia     = null,
                            $ug_s_dados_bancarios_01_conta         = null,
                            $ug_s_dados_bancarios_01_abertura     = null,

                            $ug_s_dados_bancarios_02_banco         = null,
                            $ug_s_dados_bancarios_02_agencia     = null,
                            $ug_s_dados_bancarios_02_conta         = null,
                            $ug_s_dados_bancarios_02_abertura     = null,

                            $ug_i_computadores_qtde             = null,
                            $ug_s_comunicacao_visual             = null, 

                            $ug_sNews             = null,        
                            $ug_iRiscoClassif    = null, 
                            $ug_perfil_limite_ref = null,
                            $ug_ficou_sabendo = null,
                            $ug_Substatus        = null, 

                            $ug_compet_participa                    = null, 
                            $ug_compet_promoveu                        = null, 
                            $ug_compet_participantes_fifa            = null, 
                            $ug_compet_participantes_wc3            = null, 
                        
                            $ug_ongame            = null,

                            $ug_te_id            = null,
                            
                            $ug_id_nexcafe = null, 
                            $ug_login_nexcafe_auto = 0, 
                            $ug_data_inclusao_nexcafe = null,
                                
                            $ug_alterar_senha = null,

                            $ug_exibir_contrato = null,

                            $ug_data_aceite_adesao = null,

                            $ug_recarga_celular = null,
                            $ug_tipo_venda = null,
    
                            $ug_nome_socios = null,
                            $ug_cpf_socios = null,
                            $ug_data_nascimento_socios = null,
                            $ug_porcentagem_socios = null,
							$ug_canais_venda = null
    ) {

        $this->setId($ug_id);
        $this->setLogin($ug_sLogin);
        $this->setSenha($ug_sSenha);
        $this->setAtivo($ug_blAtivo);
        $this->setStatusBusca($ug_blStatusBusca);
        $this->setDataInclusao($ug_dDataInclusao);
        $this->setDataUltimoAcesso($ug_dDataUltimoAcesso);
        $this->setQtdeAcessos($ug_iQtdeAcessos);

        $this->setNomeFantasia($ug_sNomeFantasia);
        $this->setRazaoSocial($ug_sRazaoSocial);
        $this->setCNPJ($ug_sCNPJ);
        $this->setResponsavel($ug_sResponsavel);
        $this->setEmail($ug_sEmail);

        $this->setNews($ug_sNews);

        $this->setEndereco($ug_sEndereco);
        $this->setTipoEnd($ug_sTipoEnd);
        $this->setNumero($ug_sNumero);
        $this->setComplemento($ug_sComplemento);
        $this->setBairro($ug_sBairro);
        $this->setCidade($ug_sCidade);
        $this->setEstado($ug_sEstado);
        $this->setCEP($ug_sCEP);

        $this->setTelDDI($ug_sTelDDI);
        $this->setTelDDD($ug_sTelDDD);
        $this->setTel($ug_sTel);
        $this->setCelDDI($ug_sCelDDI);
        $this->setCelDDD($ug_sCelDDD);
        $this->setCel($ug_sCel);

        $this->setFaxDDI($ug_sFaxDDI);
        $this->setFaxDDD($ug_sFaxDDD);
        $this->setFax($ug_sFax);

        $this->setRACodigo($ug_sRACodigo);
        $this->setRAOutros($ug_sRAOutros);

        $this->setContato01TelDDI($ug_sContato01TelDDI);
        $this->setContato01TelDDD($ug_sContato01TelDDD);
        $this->setContato01Tel($ug_sContato01Tel);
        $this->setContato01Nome($ug_sContato01Nome);
        $this->setContato01Cargo($ug_sContato01Cargo);

        $this->setObservacoes($ug_sObservacoes);
        $this->setRiscoClassif($ug_iRiscoClassif);

        $this->setTipoCadastro($ug_cTipoCadastro);
        $this->setNome($ug_sNome);
        $this->setCPF($ug_sCPF);
        $this->setRG($ug_sRG);
        $this->setDataNascimento($ug_dDataNascimento);
        $this->setSexo($ug_cSexo);

        $this->setPerfilSenhaReimpressao($ug_sPerfilSenhaReimpressao);
        $this->setPerfilFormaPagto($ug_iPerfilFormaPagto);
        $this->setPerfilLimite($ug_fPerfilLimite);
        $this->setPerfilSaldo($ug_fPerfilSaldo);


        $this->setInscrEstadual($ug_s_inscr_estadual);
        $this->setSite($ug_s_site);
        $this->setAberturaAno($ug_i_abertura_ano);
        $this->setAberturaMes($ug_i_abertura_mes);
        $this->setCartoes($ug_s_cartoes);
        $this->setFaturaMediaMensal($ug_i_fatura_media_mensal);

        $this->setReprLegalNome($ug_s_repr_legal_nome);
        $this->setReprLegalRG($ug_s_repr_legal_rg);
        $this->setReprLegalCPF($ug_s_repr_legal_cpf);
        $this->setReprLegalTelDDI($ug_s_repr_legal_tel_ddi);
        $this->setReprLegalTelDDD($ug_s_repr_legal_tel_ddd);
        $this->setReprLegalTel($ug_s_repr_legal_tel);
        $this->setReprLegalCelDDI($ug_s_repr_legal_cel_ddi);
        $this->setReprLegalCelDDD($ug_s_repr_legal_cel_ddd);
        $this->setReprLegalCel($ug_s_repr_legal_cel);
        $this->setReprLegalEmail($ug_s_repr_legal_email);
        $this->setReprLegalMSN($ug_s_repr_legal_msn);

        $this->setReprVendaIgualReprLegal($ug_bl_repr_venda_igual_repr_legal);
        $this->setReprVendaNome($ug_s_repr_venda_nome);
        $this->setReprVendaRG($ug_s_repr_venda_rg);
        $this->setReprVendaCPF($ug_s_repr_venda_cpf);
        $this->setReprVendaTelDDI($ug_s_repr_venda_tel_ddi);
        $this->setReprVendaTelDDD($ug_s_repr_venda_tel_ddd);
        $this->setReprVendaTel($ug_s_repr_venda_tel);
        $this->setReprVendaCelDDI($ug_s_repr_venda_cel_ddi);
        $this->setReprVendaCelDDD($ug_s_repr_venda_cel_ddd);
        $this->setReprVendaCel($ug_s_repr_venda_cel);
        $this->setReprVendaEmail($ug_s_repr_venda_email);
        $this->setReprVendaMSN($ug_s_repr_venda_msn);

        $this->setDadosBancarios01Banco($ug_s_dados_bancarios_01_banco);
        $this->setDadosBancarios01Agencia($ug_s_dados_bancarios_01_agencia);
        $this->setDadosBancarios01Conta($ug_s_dados_bancarios_01_conta);
        $this->setDadosBancarios01Abertura($ug_s_dados_bancarios_01_abertura);

        $this->setDadosBancarios02Banco($ug_s_dados_bancarios_02_banco);
        $this->setDadosBancarios02Agencia($ug_s_dados_bancarios_02_agencia);
        $this->setDadosBancarios02Conta($ug_s_dados_bancarios_02_conta);
        $this->setDadosBancarios02Abertura($ug_s_dados_bancarios_02_abertura);

        $this->setComputadoresQtde($ug_i_computadores_qtde);
        $this->setComunicacaoVisual($ug_s_comunicacao_visual);

        $this->setPerfilLimiteRef($ug_perfil_limite_ref);

        $this->setFicouSabendo($ug_ficou_sabendo);

        $this->setSubstatus($ug_Substatus);

        $this->setCompet_participa($ug_compet_participa);
        $this->setCompet_promoveu($ug_compet_promoveu);
        $this->setCompet_participantes_fifa($ug_compet_participantes_fifa);
        $this->setCompet_participantes_wc3($ug_compet_participantes_wc3);

        $this->setUgOngame($ug_ongame);

        $this->setTipoEstabelecimento($ug_te_id);

        // NexCafe
        $this->setUgIdNexCafe($ug_id_nexcafe);
        $this->setUgLoginNexCafeAuto($ug_login_nexcafe_auto);
        $this->setUgDataInclusaoNexCafe($ug_data_inclusao_nexcafe);

        $this->setAlteraSenha($ug_alterar_senha);

        $this->setExibirContrato($ug_exibir_contrato);

        $this->setDataAceite($ug_data_aceite_adesao);

        $this->setRecargaCelular($ug_recarga_celular);

        $this->setTipoVenda($ug_tipo_venda);
        
        $this->setNomeSocios($ug_nome_socios);
        $this->setCPFSocios($ug_cpf_socios);
        $this->setDataNascimentoSocios($ug_data_nascimento_socios);
        $this->setPorcentagemSocios($ug_porcentagem_socios);
		
		$this->setCanaisVenda($ug_canais_venda);
    }

    public function getDataExpiraSenha() {
        return $this->ug_data_expiracao_senha;
    }

    public function setDataExpiraSenha($ug_data_expiracao_senha) {
        $this->ug_data_expiracao_senha = $ug_data_expiracao_senha;
    }

        
    function getId() {
        return $this->ug_id;
    }
    function setId($ug_id) {
        $this->ug_id = $ug_id;
    }

    function getLogin() {
        return $this->ug_sLogin;
    }
    function setLogin($ug_sLogin) {
        $this->ug_sLogin = $ug_sLogin;
    }

    function getSenha() {
        return $this->ug_sSenha;
    }
    function setSenha($ug_sSenha) {
        $this->ug_sSenha = $ug_sSenha;
    }

    function getAtivo() {
        return $this->ug_blAtivo;
    }
    function setAtivo($ug_blAtivo) {
        if (!is_null($ug_blAtivo))
            if($ug_blAtivo == 1 || $ug_blAtivo == "1" || $ug_blAtivo === "true") $ug_blAtivo = 1;
            else $ug_blAtivo = 2;
        $this->ug_blAtivo = $ug_blAtivo;
    }

    function getStatusBusca() {
        return $this->ug_blStatusBusca;
    }
    function setStatusBusca($ug_blStatusBusca) {
        if (!is_null($ug_blStatusBusca)) {
            if($ug_blStatusBusca == 1 || $ug_blStatusBusca == "1" || $ug_blStatusBusca === "true") $ug_blStatusBusca = 1;
            else $ug_blStatusBusca = 2;
        }
        $this->ug_blStatusBusca = $ug_blStatusBusca;
    }

    function getDataInclusao() {
        return $this->ug_dDataInclusao;
    }
    function setDataInclusao($ug_dDataInclusao) {
        $this->ug_dDataInclusao = $ug_dDataInclusao;
    }

    function getDataUltimoAcesso() {
        return $this->ug_dDataUltimoAcesso;
    }
    function setDataUltimoAcesso($ug_dDataUltimoAcesso) {
        $this->ug_dDataUltimoAcesso = $ug_dDataUltimoAcesso;
    }

    function getQtdeAcessos() {
        return $this->ug_iQtdeAcessos;
    }
    function setQtdeAcessos($ug_iQtdeAcessos) {
        $this->ug_iQtdeAcessos = $ug_iQtdeAcessos;
    }

    function getNomeFantasia() {
        return $this->ug_sNomeFantasia;
    }
    function setNomeFantasia($ug_sNomeFantasia) {
        $this->ug_sNomeFantasia = $ug_sNomeFantasia;
    }

    function getRazaoSocial() {
        return $this->ug_sRazaoSocial;
    }
    function setRazaoSocial($ug_sRazaoSocial) {
        $this->ug_sRazaoSocial = $ug_sRazaoSocial;
    }

    function getCNPJ() {
        return $this->ug_sCNPJ;
    }
    function setCNPJ($ug_sCNPJ) {
        $this->ug_sCNPJ = UsuarioGames::cleanField($ug_sCNPJ);
    }

    function getResponsavel() {
        return $this->ug_sResponsavel;
    }
    function setResponsavel($ug_sResponsavel) {
        $this->ug_sResponsavel = $ug_sResponsavel;
    }

    function getEmail() {
        return $this->ug_sEmail;
    }
    function setEmail($ug_sEmail) {
        $this->ug_sEmail = $ug_sEmail;
    }

    function getNews() {
        return $this->ug_sNews;
    }
    function setNews($ug_sNews) {
        $this->ug_sNews = $ug_sNews;
    }

    function getEndereco() {
        return $this->ug_sEndereco;
    }
    function setEndereco($ug_sEndereco) {
        $this->ug_sEndereco = $ug_sEndereco;
    }

    function getTipoEnd() {
        return $this->ug_sTipoEnd;
    }
    function setTipoEnd($ug_sTipoEnd) {
        $this->ug_sTipoEnd = $ug_sTipoEnd;
    }

    function getNumero() {
        return $this->ug_sNumero;
    }
    function setNumero($ug_sNumero) {
        $this->ug_sNumero = $ug_sNumero;
    }

    function getComplemento() {
        return $this->ug_sComplemento;
    }
   function setComplemento($ug_sComplemento) {
        $this->ug_sComplemento = $ug_sComplemento;
    }

    function getBairro() {
        return $this->ug_sBairro;
    }
    function setBairro($ug_sBairro) {
        $this->ug_sBairro = $ug_sBairro;
    }

    function getCidade() {
        return $this->ug_sCidade;
    }
    function setCidade($ug_sCidade) {
        $this->ug_sCidade = $ug_sCidade;
    }

    function getEstado() {
        return $this->ug_sEstado;
    }
    function setEstado($ug_sEstado) {
        $this->ug_sEstado = $ug_sEstado;
    }

    function getCEP() {
        return $this->ug_sCEP;
    }
    function setCEP($ug_sCEP) {
        $this->ug_sCEP = UsuarioGames::cleanField($ug_sCEP);
    }

    function getTelDDI() {
        return $this->ug_sTelDDI;
    }
    function setTelDDI($ug_sTelDDI) {
        $this->ug_sTelDDI = $ug_sTelDDI;
    }

    function getTelDDD() {
        return $this->ug_sTelDDD;
    }
    function setTelDDD($ug_sTelDDD) {
        $this->ug_sTelDDD = $ug_sTelDDD;
    }

    function getTel() {
        return $this->ug_sTel;
    }
    function setTel($ug_sTel) {
        $this->ug_sTel = $ug_sTel;
    }

    function getCelDDI() {
        return $this->ug_sCelDDI;
    }
    function setCelDDI($ug_sCelDDI) {
        $this->ug_sCelDDI = $ug_sCelDDI;
    }

    function getCelDDD() {
        return $this->ug_sCelDDD;
    }
    function setCelDDD($ug_sCelDDD) {
        $this->ug_sCelDDD = $ug_sCelDDD;
    }

    function getCel() {
        return $this->ug_sCel;
    }
    function setCel($ug_sCel) {
        $this->ug_sCel = $ug_sCel;
    }

    function getFaxDDI() {
        return $this->ug_sFaxDDI;
    }
    function setFaxDDI($ug_sFaxDDI) {
        $this->ug_sFaxDDI = $ug_sFaxDDI;
    }

    function getFaxDDD() {
        return $this->ug_sFaxDDD;
    }
    function setFaxDDD($ug_sFaxDDD) {
        $this->ug_sFaxDDD = $ug_sFaxDDD;
    }

    function getFax() {
        return $this->ug_sFax;
    }
    function setFax($ug_sFax) {
        $this->ug_sFax = $ug_sFax;
    }

    
    function getRACodigo() {
        return $this->ug_sRACodigo;
    }
    function setRACodigo($ug_sRACodigo) {
        $this->ug_sRACodigo = $ug_sRACodigo;
    }

    function getRAOutros() {
        return $this->ug_sRAOutros;
    }
    function setRAOutros($ug_sRAOutros) {
        $this->ug_sRAOutros = $ug_sRAOutros;
    }

    function getContato01TelDDI() {
        return $this->ug_sContato01TelDDI;
    }
    function setContato01TelDDI($ug_sContato01TelDDI) {
        $this->ug_sContato01TelDDI = $ug_sContato01TelDDI;
    }

    function getContato01TelDDD() {
        return $this->ug_sContato01TelDDD;
    }
    function setContato01TelDDD($ug_sContato01TelDDD) {
        $this->ug_sContato01TelDDD = $ug_sContato01TelDDD;
    }

    function getContato01Tel() {
        return $this->ug_sContato01Tel;
    }
    function setContato01Tel($ug_sContato01Tel) {
        $this->ug_sContato01Tel = $ug_sContato01Tel;
    }

    function getContato01Nome() {
        return $this->ug_sContato01Nome;
    }
    function setContato01Nome($ug_sContato01Nome) {
        $this->ug_sContato01Nome = $ug_sContato01Nome;
    }

    function getContato01Cargo() {
        return $this->ug_sContato01Cargo;
    }
    function setContato01Cargo($ug_sContato01Cargo) {
        $this->ug_sContato01Cargo = $ug_sContato01Cargo;
    }

    
    function getObservacoes() {
        return $this->ug_sObservacoes;
    }
    function setObservacoes($ug_sObservacoes) {
        $this->ug_sObservacoes = $ug_sObservacoes;
    }

    function getRiscoClassif() {
        return $this->ug_iRiscoClassif;
    }
    function setRiscoClassif($ug_iRiscoClassif) {
        $this->ug_iRiscoClassif = $ug_iRiscoClassif;
    }

    function getTipoCadastro() {
        return $this->ug_cTipoCadastro;
    }
    function setTipoCadastro($ug_cTipoCadastro) {
        $this->ug_cTipoCadastro = $ug_cTipoCadastro;
    }

    function getNome() {
        return $this->ug_sNome;
    }
    function setNome($ug_sNome) {
        $this->ug_sNome = $ug_sNome;
    }

    function getCPF() {
        return $this->ug_sCPF;
    }
    function setCPF($ug_sCPF) {
        $this->ug_sCPF = UsuarioGames::cleanField($ug_sCPF);
    }

    function getRG() {
        return $this->ug_sRG;
    }
    function setRG($ug_sRG) {
        $this->ug_sRG = $ug_sRG;
    }

    function getDataNascimento() {
        return $this->ug_dDataNascimento;
    }
    function setDataNascimento($ug_dDataNascimento) {
        $this->ug_dDataNascimento = $ug_dDataNascimento;
    }

    function getSexo() {
        return $this->ug_cSexo;
    }
    function setSexo($ug_cSexo) {
        $this->ug_cSexo = $ug_cSexo;
    }

    
    function getPerfilSenhaReimpressao() {
        return $this->ug_sPerfilSenhaReimpressao;
    }
    function setPerfilSenhaReimpressao($ug_sPerfilSenhaReimpressao) {
        $this->ug_sPerfilSenhaReimpressao = $ug_sPerfilSenhaReimpressao;
    }

    function getPerfilFormaPagto() {
        return $this->ug_iPerfilFormaPagto;
    }
    function setPerfilFormaPagto($ug_iPerfilFormaPagto) {
        $this->ug_iPerfilFormaPagto = $ug_iPerfilFormaPagto;
    }

    function getPerfilLimite() {
        return $this->ug_fPerfilLimite;
    }
    function setPerfilLimite($ug_fPerfilLimite) {
        $this->ug_fPerfilLimite = $ug_fPerfilLimite;
    }

    function getPerfilSaldo() {
        return $this->ug_fPerfilSaldo;
    }
    function setPerfilSaldo($ug_fPerfilSaldo) {
        $this->ug_fPerfilSaldo = $ug_fPerfilSaldo;
    }

    function getPerfilCorteDiaSemana() {
        return $this->ug_perfil_corte_dia_semana;
    }
    function setPerfilCorteDiaSemana($ug_perfil_corte_dia_semana) {
        $this->ug_perfil_corte_dia_semana = $ug_perfil_corte_dia_semana;
    }

    function getPerfilCorteUltimoCorte() {
        return $this->ug_perfil_corte_ultimo_corte;
    }
    function setPerfilCorteUltimoCorte($ug_perfil_corte_ultimo_corte) {
        $this->ug_perfil_corte_ultimo_corte = $ug_perfil_corte_ultimo_corte;
    }

    function getPerfilLimiteSugerido() {
        return $this->ug_perfil_limite_sugerido;
    }
    function setPerfilLimiteSugerido($ug_fPerfilLimite_sugerido) {
        $this->ug_perfil_limite_sugerido = $ug_fPerfilLimite_sugerido;
    }

    function getPerfilLimiteRef() {
        return $this->ug_perfil_limite_ref;
    }
    function setPerfilLimiteRef($ug_fPerfilLimite_ref) {
        $this->ug_perfil_limite_ref = $ug_fPerfilLimite_ref;
    }

    function getFicouSabendo() {
        return $this->ug_ficou_sabendo;
    }
    function setFicouSabendo($ug_ficou_sabendo) {
        $this->ug_ficou_sabendo = $ug_ficou_sabendo;
    }

    function getCreditoPendente() {
        return $this->ug_credito_pendente;
    }
    function setCreditoPendente($ug_credito_pendente) {
        $this->ug_credito_pendente = $ug_credito_pendente;
    }


    function getInscrEstadual(){    return $this->ug_s_inscr_estadual; }        function setInscrEstadual($ug_s_inscr_estadual){    $this->ug_s_inscr_estadual = $ug_s_inscr_estadual; }
    function getSite(){                return $this->ug_s_site; }                    function setSite($ug_s_site){    $this->ug_s_site = $ug_s_site; }

    function getAberturaAno(){        return $this->ug_i_abertura_ano; }            
    function setAberturaAno($ug_i_abertura_ano){    $this->ug_i_abertura_ano = ($ug_i_abertura_ano)?$ug_i_abertura_ano:0; }
    function getAberturaMes(){        return $this->ug_i_abertura_mes; }            
    function setAberturaMes($ug_i_abertura_mes){    $this->ug_i_abertura_mes = ($ug_i_abertura_mes)?$ug_i_abertura_mes:0; }

    function getCartoes(){            return $this->ug_s_cartoes; }                function setCartoes($ug_s_cartoes){    $this->ug_s_cartoes = $ug_s_cartoes; }
    function getFaturaMediaMensal(){return $this->ug_i_fatura_media_mensal; }    function setFaturaMediaMensal($ug_i_fatura_media_mensal){    $this->ug_i_fatura_media_mensal = $ug_i_fatura_media_mensal; }

    function getReprLegalNome(){    return $this->ug_s_repr_legal_nome; }        function setReprLegalNome($ug_s_repr_legal_nome){        $this->ug_s_repr_legal_nome = $ug_s_repr_legal_nome; }
    function getReprLegalRG(){        return $this->ug_s_repr_legal_rg; }            function setReprLegalRG($ug_s_repr_legal_rg){            /*if(!is_null($ug_s_repr_legal_rg)) $ug_s_repr_legal_rg = ereg_replace("[^0-9]", "", $ug_s_repr_legal_rg);*/ $this->ug_s_repr_legal_rg = $ug_s_repr_legal_rg; }
    function getReprLegalCPF(){        return $this->ug_s_repr_legal_cpf; }        function setReprLegalCPF($ug_s_repr_legal_cpf){            /*if(!is_null($ug_s_repr_legal_cpf)) $ug_s_repr_legal_cpf = ereg_replace("[^0-9]", "", $ug_s_repr_legal_cpf);*/ $this->ug_s_repr_legal_cpf = UsuarioGames::cleanField($ug_s_repr_legal_cpf); }
    function getReprLegalDataNascimento(){  return $this->ug_repr_legal_data_nascimento; }        
    function setReprLegalDataNascimento($ug_repr_legal_data_nascimento){
        if($ug_repr_legal_data_nascimento != "" && !is_null($ug_repr_legal_data_nascimento)){
            $this->ug_repr_legal_data_nascimento = formata_data($ug_repr_legal_data_nascimento,1);
        } else{
            $this->ug_repr_legal_data_nascimento = $ug_repr_legal_data_nascimento;
        }
    }
    function getReprLegalTelDDI(){    return $this->ug_s_repr_legal_tel_ddi; }    function setReprLegalTelDDI($ug_s_repr_legal_tel_ddi){    $this->ug_s_repr_legal_tel_ddi = $ug_s_repr_legal_tel_ddi; }
    function getReprLegalTelDDD(){    return $this->ug_s_repr_legal_tel_ddd; }    function setReprLegalTelDDD($ug_s_repr_legal_tel_ddd){    $this->ug_s_repr_legal_tel_ddd = $ug_s_repr_legal_tel_ddd; }
    function getReprLegalTel(){        return $this->ug_s_repr_legal_tel; }        function setReprLegalTel($ug_s_repr_legal_tel){            $this->ug_s_repr_legal_tel = $ug_s_repr_legal_tel; }
    function getReprLegalCelDDI(){    return $this->ug_s_repr_legal_cel_ddi; }    function setReprLegalCelDDI($ug_s_repr_legal_cel_ddi){    $this->ug_s_repr_legal_cel_ddi = $ug_s_repr_legal_cel_ddi; }
    function getReprLegalCelDDD(){    return $this->ug_s_repr_legal_cel_ddd; }    function setReprLegalCelDDD($ug_s_repr_legal_cel_ddd){    $this->ug_s_repr_legal_cel_ddd = $ug_s_repr_legal_cel_ddd; }
    function getReprLegalCel(){        return $this->ug_s_repr_legal_cel; }        function setReprLegalCel($ug_s_repr_legal_cel){            $this->ug_s_repr_legal_cel = $ug_s_repr_legal_cel; }
    function getReprLegalEmail(){    return $this->ug_s_repr_legal_email; }        function setReprLegalEmail($ug_s_repr_legal_email){        $this->ug_s_repr_legal_email = $ug_s_repr_legal_email; }
    function getReprLegalMSN(){        return $this->ug_s_repr_legal_msn; }        function setReprLegalMSN($ug_s_repr_legal_msn){            $this->ug_s_repr_legal_msn = $ug_s_repr_legal_msn; }

    function getReprVendaIgualReprLegal(){    return $this->ug_bl_repr_venda_igual_repr_legal; }
    function setReprVendaIgualReprLegal($ug_bl_repr_venda_igual_repr_legal) {
        if (!is_null($ug_bl_repr_venda_igual_repr_legal))
            if($ug_bl_repr_venda_igual_repr_legal == 1 || $ug_bl_repr_venda_igual_repr_legal == "1" || $ug_bl_repr_venda_igual_repr_legal === "true") $ug_bl_repr_venda_igual_repr_legal = 1;
            else $ug_bl_repr_venda_igual_repr_legal = 2;
        $this->ug_bl_repr_venda_igual_repr_legal = $ug_bl_repr_venda_igual_repr_legal;
    }
    function getReprVendaNome(){    return $this->ug_s_repr_venda_nome; }        function setReprVendaNome($ug_s_repr_venda_nome){        $this->ug_s_repr_venda_nome = $ug_s_repr_venda_nome; }
    function getReprVendaRG(){        return $this->ug_s_repr_venda_rg; }            function setReprVendaRG($ug_s_repr_venda_rg){            /*if(!is_null($ug_s_repr_venda_rg)) $ug_s_repr_venda_rg = ereg_replace("[^0-9]", "", $ug_s_repr_venda_rg);*/ $this->ug_s_repr_venda_rg = $ug_s_repr_venda_rg; }
    function getReprVendaCPF(){        return $this->ug_s_repr_venda_cpf; }        function setReprVendaCPF($ug_s_repr_venda_cpf){            /*if(!is_null($ug_s_repr_venda_cpf)) $ug_s_repr_venda_cpf = ereg_replace("[^0-9]", "", $ug_s_repr_venda_cpf);*/ $this->ug_s_repr_venda_cpf = $ug_s_repr_venda_cpf; }
    function getReprVendaTelDDI(){    return $this->ug_s_repr_venda_tel_ddi; }    function setReprVendaTelDDI($ug_s_repr_venda_tel_ddi){    $this->ug_s_repr_venda_tel_ddi = $ug_s_repr_venda_tel_ddi; }
    function getReprVendaTelDDD(){    return $this->ug_s_repr_venda_tel_ddd; }    function setReprVendaTelDDD($ug_s_repr_venda_tel_ddd){    $this->ug_s_repr_venda_tel_ddd = $ug_s_repr_venda_tel_ddd; }
    function getReprVendaTel(){        return $this->ug_s_repr_venda_tel; }        function setReprVendaTel($ug_s_repr_venda_tel){            $this->ug_s_repr_venda_tel = $ug_s_repr_venda_tel; }
    function getReprVendaCelDDI(){    return $this->ug_s_repr_venda_cel_ddi; }    function setReprVendaCelDDI($ug_s_repr_venda_cel_ddi){    $this->ug_s_repr_venda_cel_ddi = $ug_s_repr_venda_cel_ddi; }
    function getReprVendaCelDDD(){    return $this->ug_s_repr_venda_cel_ddd; }    function setReprVendaCelDDD($ug_s_repr_venda_cel_ddd){    $this->ug_s_repr_venda_cel_ddd = $ug_s_repr_venda_cel_ddd; }
    function getReprVendaCel(){        return $this->ug_s_repr_venda_cel; }        function setReprVendaCel($ug_s_repr_venda_cel){            $this->ug_s_repr_venda_cel = $ug_s_repr_venda_cel; }
    function getReprVendaEmail(){    return $this->ug_s_repr_venda_email; }        function setReprVendaEmail($ug_s_repr_venda_email){        $this->ug_s_repr_venda_email = $ug_s_repr_venda_email; }
    function getReprVendaMSN(){        return $this->ug_s_repr_venda_msn; }        function setReprVendaMSN($ug_s_repr_venda_msn){            $this->ug_s_repr_venda_msn = $ug_s_repr_venda_msn; }

    function getDadosBancarios01Banco(){    return $this->ug_s_dados_bancarios_01_banco; }        function setDadosBancarios01Banco($ug_s_dados_bancarios_01_banco){        $this->ug_s_dados_bancarios_01_banco = $ug_s_dados_bancarios_01_banco; }
    function getDadosBancarios01Agencia(){    return $this->ug_s_dados_bancarios_01_agencia; }    function setDadosBancarios01Agencia($ug_s_dados_bancarios_01_agencia){    $this->ug_s_dados_bancarios_01_agencia = $ug_s_dados_bancarios_01_agencia; }
    function getDadosBancarios01Conta(){    return $this->ug_s_dados_bancarios_01_conta; }        function setDadosBancarios01Conta($ug_s_dados_bancarios_01_conta){        $this->ug_s_dados_bancarios_01_conta = $ug_s_dados_bancarios_01_conta; }
    function getDadosBancarios01Abertura(){    return $this->ug_s_dados_bancarios_01_abertura; }    function setDadosBancarios01Abertura($ug_s_dados_bancarios_01_abertura){$this->ug_s_dados_bancarios_01_abertura = $ug_s_dados_bancarios_01_abertura; }

    function getDadosBancarios02Banco(){    return $this->ug_s_dados_bancarios_02_banco; }        function setDadosBancarios02Banco($ug_s_dados_bancarios_02_banco){        $this->ug_s_dados_bancarios_02_banco = $ug_s_dados_bancarios_02_banco; }
    function getDadosBancarios02Agencia(){    return $this->ug_s_dados_bancarios_02_agencia; }    function setDadosBancarios02Agencia($ug_s_dados_bancarios_02_agencia){    $this->ug_s_dados_bancarios_02_agencia = $ug_s_dados_bancarios_02_agencia; }
    function getDadosBancarios02Conta(){    return $this->ug_s_dados_bancarios_02_conta; }        function setDadosBancarios02Conta($ug_s_dados_bancarios_02_conta){        $this->ug_s_dados_bancarios_02_conta = $ug_s_dados_bancarios_02_conta; }
    function getDadosBancarios02Abertura(){    return $this->ug_s_dados_bancarios_02_abertura; }    function setDadosBancarios02Abertura($ug_s_dados_bancarios_02_abertura){$this->ug_s_dados_bancarios_02_abertura = $ug_s_dados_bancarios_02_abertura; }

    function getComputadoresQtde(){            return $this->ug_i_computadores_qtde; }              function setComputadoresQtde($ug_i_computadores_qtde){                    $this->ug_i_computadores_qtde = $ug_i_computadores_qtde; }
    function getComunicacaoVisual(){        return $this->ug_s_comunicacao_visual; }              function setComunicacaoVisual($ug_s_comunicacao_visual){                $this->ug_s_comunicacao_visual = $ug_s_comunicacao_visual; }

    function getSubstatus() {
        return $this->ug_Substatus;
    }
    function setSubstatus($ug_Substatus) {
        $this->ug_Substatus = $ug_Substatus;
    }

    function getCompet_participa(){ return $this->ug_compet_participa; }
    function setCompet_participa($ug_compet_participa){ $this->ug_compet_participa = $ug_compet_participa; }

    function getCompet_promoveu(){ return $this->ug_compet_promoveu; }
    function setCompet_promoveu($ug_compet_promoveu){ $this->ug_compet_promoveu = $ug_compet_promoveu; }

    function getCompet_participantes_fifa(){ return $this->ug_compet_participantes_fifa; }
    function setCompet_participantes_fifa($ug_compet_participantes_fifa){ $this->ug_compet_participantes_fifa = $ug_compet_participantes_fifa; }

    function getCompet_participantes_wc3(){ return $this->ug_compet_participantes_wc3; }
    function setCompet_participantes_wc3($ug_compet_participantes_wc3){ $this->ug_compet_participantes_wc3 = $ug_compet_participantes_wc3; }

    //Metodos de SET e GET para o Campo ug_ongame
    function getUgOngame(){ return $this->ug_ongame; }
    function setUgOngame($ug_ongame){ $this->ug_ongame = $ug_ongame; }

    //Metodos de SET e GET para o Campo ug_te_id - Tipo de Estabelecimento
    function getTipoEstabelecimentoParaBanco() {
        if ($this->ug_te_id == "") {
            return 'NULL';
        }
        else {
        return $this->ug_te_id;
    }
    }
    function getTipoEstabelecimento(){ return $this->ug_te_id; }
    function setTipoEstabelecimento($ug_te_id){    $this->ug_te_id = $ug_te_id; }

    // Metodos de SET e GET para o Campo ug_id_nexcafe - Integração com o NexCafe
    function getUgIdNexCafe(){ return $this->ug_id_nexcafe;  }
    function setUgIdNexCafe($ug_id_nexcafe) { $this->ug_id_nexcafe = $ug_id_nexcafe;  }
    function getUgLoginNexCafeAuto(){ return (($this->ug_login_nexcafe_auto==1)?1:0); }    
    function setUgLoginNexCafeAuto($ug_login_nexcafe_auto) { $this->ug_login_nexcafe_auto = $ug_login_nexcafe_auto; }
    function getUgDataInclusaoNexCafe(){ return $this->ug_data_inclusao_nexcafe; }
    function setUgDataInclusaoNexCafe($ug_data_inclusao_nexcafe) { $this->ug_data_inclusao_nexcafe = $ug_data_inclusao_nexcafe;}

    //Metodos de SET e GET para o Campo ug_alterar_senha - Altera senha no próximo login
    function getAlteraSenha(){ return $this->ug_alterar_senha; }
    function setAlteraSenha($ug_alterar_senha){    $this->ug_alterar_senha = $ug_alterar_senha; }

    //Metodos de SET e GET para o Campo ug_exibir_contrato - Exibi contrato de adesão no próximo login
    function getExibirContrato(){ return $this->ug_exibir_contrato; }
    function setExibirContrato($ug_exibir_contrato){    $this->ug_exibir_contrato = $ug_exibir_contrato; }

    //Metodos de SET e GET para o Campo ug_data_aceite_adesao - Data do aceite do contrato de adesão
    function getDataAceite(){ return $this->ug_data_aceite_adesao; }
    function setDataAceite($ug_data_aceite_adesao){    $this->ug_data_aceite_adesao = $ug_data_aceite_adesao; }

    //Metodos de SET e GET para o Campo ug_recarga_celular - Habilita LAN na revenda de Recarga de Celular
    function getRecargaCelular(){ return $this->ug_recarga_celular; }
    function setRecargaCelular($ug_recarga_celular){    $this->ug_recarga_celular = $ug_recarga_celular; }

    //Metodos de SET e GET para o Campo ug_tipo_venda
    function getTipoVenda(){ return $this->ug_tipo_venda; }
    function setTipoVenda($ug_tipo_venda){ $this->ug_tipo_venda = $ug_tipo_venda; }
    
    //Metodos GET e SET para os dados dos sócios de PDVs
    function getNomeSocios(){ return $this->ug_nome_socios; }
    function setNomeSocios($ug_nome_socios){ $this->ug_nome_socios = $ug_nome_socios; }
    
    function getCPFSocios(){ return $this->ug_cpf_socios; }
    function setCPFSocios($ug_cpf_socios){ $this->ug_cpf_socios = $ug_cpf_socios; }
    
    function getDataNascimentoSocios(){ return $this->ug_data_nascimento_socios; }
    function setDataNascimentoSocios($ug_data_nascimento_socios){ $this->ug_data_nascimento_socios = $ug_data_nascimento_socios; }
    
    function getPorcentagemSocios(){ return $this->ug_porcentagem_socios; }
    function setPorcentagemSocios($ug_porcentagem_socios){ $this->ug_porcentagem_socios = $ug_porcentagem_socios; }

    //Metodos de SET e GET para o Campo ug_vip - Habilita LAN como revenda VIP alterando o limite de compras
    function getVIP(){ return $this->ug_vip; }
    function setVIP($ug_vip){    $this->ug_vip = $ug_vip; }

    //Metodos de SET e GET para o Campo ug_possui_restricao_produtos - Possui Restrição de Vendas de Produtos
    function getPossuiRestricaoProdutos(){ return $this->ug_possui_restricao_produtos; }
    function setPossuiRestricaoProdutos($ug_possui_restricao_produtos){    $this->ug_possui_restricao_produtos = $ug_possui_restricao_produtos; }

    //Metodos de SET e GET para o Campo ug_data_aprovacao - Data de aprovação do Cadastro do PDV
    function getDataAprovacao(){ return $this->ug_data_aprovacao; }
    function setDataAprovacao($ug_data_aprovacao){    $this->ug_data_aprovacao = $ug_data_aprovacao; }

	function getCanaisVenda() {
		return $this->ug_canais_venda;
	}
	function setCanaisVenda($ug_canais_venda) {
		$this->ug_canais_venda = $ug_canais_venda;
	} 

    function inserir(&$objGamesUsuario) {
        
        $objGamesUsuario->setPerfilSenhaReimpressao("E!!!Prepag");
        $objGamesUsuario->setPerfilLimite("0,00");

        $objGamesUsuario->setPerfilCorteDiaSemana("1");
        $objGamesUsuario->setPerfilLimiteSugerido("0,00");
        $objGamesUsuario->setCreditoPendente("0,00");

        $s_compet_participa = $objGamesUsuario->getCompet_participa();
        if (strtoupper($s_compet_participa) == "S") {
            $ret = UsuarioGames::validarCampos2($objGamesUsuario, true);
        } else {
            $ret = UsuarioGames::validarCampos($objGamesUsuario, true);
        }

        if ($ret == "") {
            if (UsuarioGames::existeLogin($objGamesUsuario->getLogin(), null)) {
                $ret = "Login já cadastrado.";
            }
        }

        if ($ret == "") {
            if (UsuarioGames::existeCNPJ($objGamesUsuario->getCNPJ(), null)) {
                $ret = "CNPJ já cadastrado.";
            }
        }

        if ($ret == "") {
            if (UsuarioGames::existeEmail($objGamesUsuario->getEmail())) {
                $ret .= "Email já cadastrado.".PHP_EOL;
            }
        }

        if ($ret == "") {
            if ($this->existeCPF($objGamesUsuario->getCPF(), null)) {
                $ret .= "CPF já cadastrado.".PHP_EOL;
            }
        }
        
        if ($ret == "") {

            //Formata
            $objEncryption = new Encryption();
            $senha = $objEncryption->encrypt(trim($objGamesUsuario->getSenha()));
            $dataInclusao = "CURRENT_TIMESTAMP";
            $dataUltimoAcesso = "CURRENT_TIMESTAMP";
            $qtdeAcessos = 0;
             if(!is_null($objGamesUsuario->getDataNascimento())) $dataNascimento = formata_data($objGamesUsuario->getDataNascimento(), 1);
             if(is_null($objGamesUsuario->getUgOngame())) $objGamesUsuario->setUgOngame("n");


            //SQL
            $sql = "insert into dist_usuarios_games(ug_login, ug_senha, ug_ativo, ug_status, ug_substatus, ug_data_inclusao, 
                         ug_data_ultimo_acesso, ug_qtde_acessos, ug_nome_fantasia, ug_razao_social, ug_cnpj, 
                         ug_responsavel, ug_email, ug_endereco, ug_tipo_end, ug_numero, ug_complemento, 
                         ug_bairro, ug_cidade, ug_estado, ug_cep, ug_tel_ddi, ug_tel_ddd, ug_tel, 
                         ug_cel_ddi, ug_cel_ddd, ug_cel, ug_fax_ddi, ug_fax_ddd, ug_fax, 
                         ug_ra_codigo, ug_ra_outros, ug_contato01_nome, ug_contato01_cargo, 
                         ug_contato01_tel_ddi, ug_contato01_tel_ddd, ug_contato01_tel, ug_risco_classif,  
                         ug_tipo_cadastro, ug_nome, ug_cpf, ug_rg, ug_data_nascimento, ug_sexo, 
                         ug_perfil_senha_reimpressao, ug_perfil_forma_pagto, ug_perfil_limite, ug_perfil_saldo,
                         ug_perfil_corte_dia_semana, ug_perfil_corte_ultimo_corte, ug_perfil_limite_sugerido, ug_credito_pendente,
                         ug_inscr_estadual, ug_site, ug_abertura_ano, ug_abertura_mes,ug_cartoes, ug_fatura_media_mensal,
                        ug_repr_legal_nome, ug_repr_legal_rg, ug_repr_legal_cpf,
                        ug_repr_legal_tel_ddi, ug_repr_legal_tel_ddd, ug_repr_legal_tel,
                        ug_repr_legal_cel_ddi, ug_repr_legal_cel_ddd, ug_repr_legal_cel,
                        ug_repr_legal_email, ug_repr_legal_msn,
                        ug_repr_venda_igual_repr_legal,
                        ug_repr_venda_nome, ug_repr_venda_rg, ug_repr_venda_cpf,
                        ug_repr_venda_tel_ddi, ug_repr_venda_tel_ddd, ug_repr_venda_tel,
                        ug_repr_venda_cel_ddi, ug_repr_venda_cel_ddd, ug_repr_venda_cel,
                        ug_repr_venda_email, ug_repr_venda_msn,
                        ug_dados_bancarios_01_banco, ug_dados_bancarios_01_agencia, ug_dados_bancarios_01_conta, ug_dados_bancarios_01_abertura, 
                        ug_dados_bancarios_02_banco, ug_dados_bancarios_02_agencia, ug_dados_bancarios_02_conta, ug_dados_bancarios_02_abertura, 
                        ug_computadores_qtde, ug_comunicacao_visual, ug_perfil_limite_ref, ug_ficou_sabendo,
                        ug_compet_participa, ug_compet_promoveu, ug_compet_participantes_fifa, ug_compet_participantes_wc3, ug_ongame, 
                        ug_id_nexcafe, ug_login_nexcafe_auto, ug_data_inclusao_nexcafe, 
                        ug_te_id, ug_alterar_senha, ug_exibir_contrato, ug_tipo_venda, ug_data_aceite_adesao

                    ) values (";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getLogin())), "s") . ",";
            $sql .= SQLaddFields($senha, "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getAtivo()), "") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getStatusBusca()), "") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getSubstatus()), "") . ",";
            $sql .= SQLaddFields($dataInclusao, "") . ",";
            $sql .= SQLaddFields($dataUltimoAcesso, "") . ",";
            $sql .= SQLaddFields($qtdeAcessos, "") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getNomeFantasia())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getRazaoSocial())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getCNPJ())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getResponsavel())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getEmail())), "s") . ",";

            $sql .= SQLaddFields(trim($objGamesUsuario->getEndereco()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getTipoEnd()), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getNumero())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getComplemento())), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getBairro()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCidade()), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getEstado())), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCEP()), "s") . ",";

            $sql .= SQLaddFields(trim($objGamesUsuario->getTelDDI()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getTelDDD()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getTel()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCelDDI()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCelDDD()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCel()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getFaxDDI()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getFaxDDD()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getFax()), "s") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getRACodigo())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getRAOutros())), "s") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getContato01Nome())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getContato01Cargo())), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getContato01TelDDI()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getContato01TelDDD()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getContato01Tel()), "s") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getRiscoClassif())), "") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getTipoCadastro())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getNome())), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCPF()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getRG()), "s") . ",";
            $sql .= SQLaddFields($dataNascimento, "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getSexo())), "s") . ",";

            $sql .= SQLaddFields(trim($objGamesUsuario->getPerfilSenhaReimpressao()), "s") . ",";
            $sql .= SQLaddFields($objGamesUsuario->getPerfilFormaPagto(), "") . ",";
            $sql .= SQLaddFields((int) moeda2numeric($objGamesUsuario->getPerfilLimite()), "") . ",";
            $sql .= SQLaddFields((int) moeda2numeric($objGamesUsuario->getPerfilSaldo()), "") . ",";

            $sql .= SQLaddFields($objGamesUsuario->getPerfilCorteDiaSemana(), "") . ",";
            $sql .= SQLaddFields($objGamesUsuario->getPerfilCorteUltimoCorte(), "s") . ",";
            $sql .= SQLaddFields(moeda2numeric($objGamesUsuario->getPerfilLimiteSugerido()), "") . ",";
            $sql .= SQLaddFields(moeda2numeric($objGamesUsuario->getCreditoPendente()), "") . ",";


            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getInscrEstadual())), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getSite()), "s") . ",";
            $sql .= SQLaddFields($objGamesUsuario->getAberturaAno(), "") . ",";
            $sql .= SQLaddFields($objGamesUsuario->getAberturaMes(), "") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getCartoes())), "s") . ",";
            $sql .= SQLaddFields($objGamesUsuario->getFaturaMediaMensal(), "") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalNome())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalRG())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCPF())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalTelDDI())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalTelDDD())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalTel())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCelDDI())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCelDDD())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCel())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalEmail())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalMSN())), "s") . ",";

            $sql .= SQLaddFields($objGamesUsuario->getReprVendaIgualReprLegal(), "") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaNome())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaRG())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCPF())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaTelDDI())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaTelDDD())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaTel())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCelDDI())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCelDDD())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCel())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaEmail())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaMSN())), "s") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Banco())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Agencia())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Conta())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Abertura())), "s") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Banco())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Agencia())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Conta())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Abertura())), "s") . ",";

            $sql .= SQLaddFields($objGamesUsuario->getComputadoresQtde(), "") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getComunicacaoVisual())), "s") . ",";

            $sql .= SQLaddFields(moeda2numeric($objGamesUsuario->getPerfilLimiteRef()), "") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getFicouSabendo())), "s") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getCompet_participa())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getCompet_promoveu())), "s") . ",";
            $sql .= SQLaddFields($objGamesUsuario->getCompet_participantes_fifa(), "") . ",";
            $sql .= SQLaddFields($objGamesUsuario->getCompet_participantes_wc3(), "") . ",";

            $sql .= SQLaddFields($objGamesUsuario->getUgOngame(), "s") . ",";

            $sql .= SQLaddFields($objGamesUsuario->getUgIdNexCafe(), "s") . ", ";
            $sql .= SQLaddFields($objGamesUsuario->getUgLoginNexCafeAuto(), "") . ", ";
            $sql .= SQLaddFields((($objGamesUsuario->getUgIdNexCafe()) ? "CURRENT_TIMESTAMP" : "NULL"), "") . ", ";  /* Se o Cadastro ou Edição foi acionado pelo NexCafé */


            $sql .= SQLaddFields($objGamesUsuario->getTipoEstabelecimentoParaBanco(), "") . ",";

            $sql .= intval(SQLaddFields($objGamesUsuario->getAlteraSenha(), "") * 1) . ",";
            $sql .= intval(SQLaddFields($objGamesUsuario->getExibirContrato(), "") * 1) . ",";
            $sql .= SQLaddFields($objGamesUsuario->getTipoVenda(), "s") . ", ";
            $sql .= SQLaddFields((($objGamesUsuario->getDataAceite()) ? "CURRENT_TIMESTAMP" : "NULL"), "") . ")";

            $ret = SQLexecuteQuery($sql);

            if(!$ret) $ret = "Erro ao inserir usuário.\n<!--".PHP_EOL.$sql."\n-->";
            else {
                $ret = "";
                $rs_id = SQLexecuteQuery("select currval('dist_usuarios_games_id_seq') as last_id");
                if ($rs_id && pg_num_rows($rs_id) > 0) {
                    $rs_id_row = pg_fetch_array($rs_id);
                    $objGamesUsuario->setId($rs_id_row['last_id']);

                    //Log na base
                    usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CRIACAO_DO_CADASTRO'], $objGamesUsuario->getId(), null);

                    /* Se o Cadastro ou Edição foi acionado pelo NexCafé, Grava o Log de Cadastro CADASTRO_LANHOUSE_VIA_NEXCAFE */
                    if ($objGamesUsuario->getUgIdNexCafe()) {
                        usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CADASTRO_LANHOUSE_VIA_NEXCAFE'], $objGamesUsuario->getId(), null);
                    }


                    //Envia email
                    //--------------------------------------------------------------------------------
                    $parametros['prepag_dominio'] = "" . EPREPAG_URL_HTTP . "";
                    $parametros['nome_fantasia'] = $objGamesUsuario->getNomefantasia();
                    $parametros['tipo_cadastro'] = $objGamesUsuario->getTipoCadastro();
                    $parametros['nome'] = $objGamesUsuario->getNome();
                    $parametros['sexo'] = $objGamesUsuario->getSexo();

                    $msgEmail = email_cabecalho($parametros);
                    $msgEmail .= "  <br><br>
                                    <table border='0' cellspacing='0'>
                                    <tr><td>&nbsp;</td></tr>
                                    <tr valign='middle' bgcolor='#FFFFFF'>
                                        <td align='left' class='texto'>
                                            Confirmamos a recepção do seu pedido de cadastro junto ao E-Prepag LanHouses. 
                                            O seu pedido será submetido a análise e você será informado por e-mail quanto ao aceite, ou não, do seu pedido.<br>
                                        </td>
                                    </tr>
                                    <tr><td>&nbsp;</td></tr>
                                    </table>
                                ";
                    $msgEmail .= email_rodape($parametros);
                    enviaEmail($objGamesUsuario->getEmail(), null, null, "E-Prepag - Pedido de Cadastro", $msgEmail);
                }
            }
        }

        return $ret;
    }
	
	function verificaPOST($referer,$POST){
            
		//if (strpos($referer,"bronzato.com.br")===false && strpos($referer,"contause.digital")===false){return false;}
		$flag=true;
		foreach($_POST as $xa=>$xb){
			$xb = serialize($xb);
			if (strpos($xb,"dbms_pipe.receive_message")!==false || strpos($xb,"DBMS_PIPE.RECEIVE_MESSAGE")!==false || strpos($xb,"delete")!==false || strpos($xb,"delete")!==false || strpos($xb,"update")!==false || strpos($xb,"select")!==false ){
					return false;
			}
			
			if (strpos($xb,"dbms_pipe.receive_message")!==false || strpos($xb,"DBMS_PIPE.RECEIVE_MESSAGE")!==false ||strpos($this->hexToStr($xb),"delete")!==false || strpos($this->hexToStr($xb),"update")!==false || strpos($this->hexToStr($xb),"select")!==false ){
					return false;
			}
		}
		
		if ($flag){return true;}else{return false;}
	}
	
	function strToHex($string){
		$hex = '';
		for ($i=0; $i<strlen($string); $i++){
			$ord = ord($string[$i]);
			$hexCode = dechex($ord);
			$hex .= substr('0'.$hexCode, -2);
		}
		return strToUpper($hex);
	}
	
	function hexToStr($hex){
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2){
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}

    function inserirPDO(&$objGamesUsuario, $dados = "") {
        $objGamesUsuario->setPerfilSenhaReimpressao("E!!!Prepag");
        $objGamesUsuario->setPerfilLimite("0,00");

        $objGamesUsuario->setPerfilCorteDiaSemana("1");
        $objGamesUsuario->setPerfilLimiteSugerido("0,00");
        $objGamesUsuario->setCreditoPendente("0,00");
		
		if(!$this->verificaPOST("", $dados)){
			$ret = "Não foi possivel continuar o processo.";
		}
        
		if ($ret == "") {
			$s_compet_participa = $objGamesUsuario->getCompet_participa();
			if (strtoupper($s_compet_participa) == "S") {
				$ret = $this->validarCampos2($objGamesUsuario, true);
			} else {
				$ret = $this->validarCampos($objGamesUsuario, true);
			}
        }
		
        if ($ret == "") {
            if ($this->existeLogin($objGamesUsuario->getLogin(), null)) {
                $ret = "Login já cadastrado.";
            }
        }

        if ($ret == "") {
            if ($this->existeCNPJ($objGamesUsuario->getCNPJ(), null)) {
                $ret = "CNPJ já cadastrado.";
            }
        }

        if ($ret == "") {
            if ($this->existeEmail($objGamesUsuario->getEmail())) {
                $ret .= "Email já cadastrado.".PHP_EOL;
            }
        }
        
        if ($ret == "") {
            if ($this->existeCPF($objGamesUsuario->getCPF(), null)) {
                $ret .= "CPF já cadastrado.".PHP_EOL;
            }
        }

        if ($ret == "") {

            //Formata
            $objEncryption = new Encryption();
            $senha = $objEncryption->encrypt(trim($objGamesUsuario->getSenha()));
            $dataInclusao = "CURRENT_TIMESTAMP";
            $dataUltimoAcesso = "CURRENT_TIMESTAMP";
            $qtdeAcessos = 0;
            if(!is_null($objGamesUsuario->getDataNascimento())) $dataNascimento = formata_data($objGamesUsuario->getDataNascimento(), 1);
            if(is_null($objGamesUsuario->getUgOngame())) $objGamesUsuario->setUgOngame("n");


            //Inicializando conexao PDO
            $con = ConnectionPDO::getConnection();
            $pdo = $con->getLink();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //Array para a Query de Insert
            $tmpArray = array(
                                ':ug_login'                 => (string) trim(strtoupper($objGamesUsuario->getLogin())),
                                ':ug_senha'                 => (string) $senha,
                                ':ug_ativo'                 => trim($objGamesUsuario->getAtivo()),
                                ':ug_status'                => trim($objGamesUsuario->getStatusBusca()),
                                ':ug_substatus'             => trim($objGamesUsuario->getSubstatus()),
                                ':ug_data_inclusao'         => date("Y-m-d H:i:s"),
                                ':ug_data_ultimo_acesso'    => date("Y-m-d H:i:s"),
                                ':ug_qtde_acessos'          => $qtdeAcessos,
                                ':ug_nome_fantasia'         => (string) trim(strtoupper($objGamesUsuario->getNomeFantasia())),
                                ':ug_razao_social'          => (string) trim(strtoupper($objGamesUsuario->getRazaoSocial())),
                                ':ug_cnpj'                  => (string) trim(strtoupper($objGamesUsuario->getCNPJ())),
                                ':ug_responsavel'           => (string) trim(strtoupper($objGamesUsuario->getResponsavel())),
                                ':ug_email'                 => (string) trim(strtoupper($objGamesUsuario->getEmail())),
                                ':ug_endereco'              => (string) trim($objGamesUsuario->getEndereco()),
                                ':ug_tipo_end'              => (string) trim($objGamesUsuario->getTipoEnd()),
                                ':ug_numero'                => (string) trim(strtoupper($objGamesUsuario->getNumero())),
                                ':ug_complemento'           => (string) trim(strtoupper($objGamesUsuario->getComplemento())),
                                ':ug_bairro'                => (string) trim($objGamesUsuario->getBairro()),
                                ':ug_cidade'                => (string) trim($objGamesUsuario->getCidade()),
                                ':ug_estado'                => (string) trim(strtoupper($objGamesUsuario->getEstado())),
                                ':ug_cep'                   => (string) trim($objGamesUsuario->getCEP()),
                                ':ug_tel_ddi'               => (string) trim($objGamesUsuario->getTelDDI()),
                                ':ug_tel_ddd'               => (string) trim($objGamesUsuario->getTelDDD()),
                                ':ug_tel'                   => (string) trim($objGamesUsuario->getTel()),
                                ':ug_cel_ddi'               => (string) trim($objGamesUsuario->getCelDDI()),
                                ':ug_cel_ddd'               => (string) trim($objGamesUsuario->getCelDDD()),
                                ':ug_cel'                   => (string) trim($objGamesUsuario->getCel()),
                                ':ug_fax_ddi'               => (string) trim($objGamesUsuario->getFaxDDI()),
                                ':ug_fax_ddd'               => (string) trim($objGamesUsuario->getFaxDDD()),
                                ':ug_fax'                   => (string) trim($objGamesUsuario->getFax()),
                                ':ug_ra_codigo'             => (string) trim(strtoupper($objGamesUsuario->getRACodigo())),
                                ':ug_ra_outros'             => (string) trim(strtoupper($objGamesUsuario->getRAOutros())),
                                ':ug_contato01_nome'        => (string) trim(strtoupper($objGamesUsuario->getContato01Nome())),
                                ':ug_contato01_cargo'       => (string) trim(strtoupper($objGamesUsuario->getContato01Cargo())),
                                ':ug_contato01_tel_ddi'     => (string) trim($objGamesUsuario->getContato01TelDDI()),
                                ':ug_contato01_tel_ddd'     => (string) trim($objGamesUsuario->getContato01TelDDD()),
                                ':ug_contato01_tel'         => (string) trim($objGamesUsuario->getContato01Tel()),
                                ':ug_risco_classif'         => trim(strtoupper($objGamesUsuario->getRiscoClassif())),
                                ':ug_tipo_cadastro'         => (string) trim(strtoupper($objGamesUsuario->getTipoCadastro())),
                                ':ug_nome'                  => (string) trim(strtoupper($objGamesUsuario->getNome())),
                                ':ug_cpf'                   => (string) trim($objGamesUsuario->getCPF()),
                                ':ug_rg'                    => (string) trim($objGamesUsuario->getRG()),
                                ':ug_data_nascimento'       => $dataNascimento,
                                ':ug_sexo'                  => (string) trim(strtoupper($objGamesUsuario->getSexo())),
                                ':ug_perfil_senha_reimpressao'=> (string) trim($objGamesUsuario->getPerfilSenhaReimpressao()),
                                ':ug_perfil_forma_pagto'    => $objGamesUsuario->getPerfilFormaPagto(),
                                ':ug_perfil_limite'         => (int) moeda2numeric($objGamesUsuario->getPerfilLimite()),
                                ':ug_perfil_saldo'          => (int) moeda2numeric($objGamesUsuario->getPerfilSaldo()),
                                ':ug_perfil_corte_dia_semana'=> $objGamesUsuario->getPerfilCorteDiaSemana(),
                                ':ug_perfil_corte_ultimo_corte'=> $objGamesUsuario->getPerfilCorteUltimoCorte(),
                                ':ug_perfil_limite_sugerido'=> moeda2numeric($objGamesUsuario->getPerfilLimiteSugerido()),
                                ':ug_credito_pendente'      => moeda2numeric($objGamesUsuario->getCreditoPendente()),
                                ':ug_inscr_estadual'        => (string) trim(strtoupper($objGamesUsuario->getInscrEstadual())),
                                ':ug_site'                  => (string) trim($objGamesUsuario->getSite()),
                                ':ug_abertura_ano'          => $objGamesUsuario->getAberturaAno(),
                                ':ug_abertura_mes'          => $objGamesUsuario->getAberturaMes(),
                                ':ug_cartoes'               => (string) trim(strtoupper($objGamesUsuario->getCartoes())),
                                ':ug_fatura_media_mensal'   => $objGamesUsuario->getFaturaMediaMensal(),
                                ':ug_repr_legal_nome'       => (string) trim(strtoupper($objGamesUsuario->getReprLegalNome())),
                                ':ug_repr_legal_rg'         => (string) trim(strtoupper($objGamesUsuario->getReprLegalRG())),
                                ':ug_repr_legal_cpf'        => (string) trim(strtoupper($objGamesUsuario->getReprLegalCPF())),
                                ':ug_repr_legal_tel_ddi'    => (string) trim(strtoupper($objGamesUsuario->getReprLegalTelDDI())),
                                ':ug_repr_legal_tel_ddd'    => (string) trim(strtoupper($objGamesUsuario->getReprLegalTelDDD())),
                                ':ug_repr_legal_tel'        => (string) trim(strtoupper($objGamesUsuario->getReprLegalTel())),
                                ':ug_repr_legal_cel_ddi'    => (string) trim(strtoupper($objGamesUsuario->getReprLegalCelDDI())),
                                ':ug_repr_legal_cel_ddd'    => (string) trim(strtoupper($objGamesUsuario->getReprLegalCelDDD())),
                                ':ug_repr_legal_cel'        => (string) trim(strtoupper($objGamesUsuario->getReprLegalCel())),
                                ':ug_repr_legal_email'      => (string) trim(strtoupper($objGamesUsuario->getReprLegalEmail())),
                                ':ug_repr_legal_msn'        => (string) trim(strtoupper($objGamesUsuario->getReprLegalMSN())),
                                ':ug_repr_venda_igual_repr_legal'=> $objGamesUsuario->getReprVendaIgualReprLegal(),
                                ':ug_repr_venda_nome'       => (string) trim(strtoupper($objGamesUsuario->getReprVendaNome())),
                                ':ug_repr_venda_rg'         => (string) trim(strtoupper($objGamesUsuario->getReprVendaRG())),
                                ':ug_repr_venda_cpf'        => (string) trim(strtoupper($objGamesUsuario->getReprVendaCPF())),
                                ':ug_repr_venda_tel_ddi'    => (string) trim(strtoupper($objGamesUsuario->getReprVendaTelDDI())),
                                ':ug_repr_venda_tel_ddd'    => (string) trim(strtoupper($objGamesUsuario->getReprVendaTelDDD())),
                                ':ug_repr_venda_tel'        => (string) trim(strtoupper($objGamesUsuario->getReprVendaTel())),
                                ':ug_repr_venda_cel_ddi'    => (string) trim(strtoupper($objGamesUsuario->getReprVendaCelDDI())),
                                ':ug_repr_venda_cel_ddd'    => (string) trim(strtoupper($objGamesUsuario->getReprVendaCelDDD())),
                                ':ug_repr_venda_cel'        => (string) trim(strtoupper($objGamesUsuario->getReprVendaCel())),
                                ':ug_repr_venda_email'      => (string) trim(strtoupper($objGamesUsuario->getReprVendaEmail())),
                                ':ug_repr_venda_msn'        => (string) trim(strtoupper($objGamesUsuario->getReprVendaMSN())),
                                ':ug_dados_bancarios_01_banco'=> (string) trim(strtoupper($objGamesUsuario->getDadosBancarios01Banco())),
                                ':ug_dados_bancarios_01_agencia'=> (string) trim(strtoupper($objGamesUsuario->getDadosBancarios01Agencia())),
                                ':ug_dados_bancarios_01_conta'=> (string) trim(strtoupper($objGamesUsuario->getDadosBancarios01Conta())),
                                ':ug_dados_bancarios_01_abertura'=> (string) trim(strtoupper($objGamesUsuario->getDadosBancarios01Abertura())),
                                ':ug_dados_bancarios_02_banco'=> (string) trim(strtoupper($objGamesUsuario->getDadosBancarios02Banco())),
                                ':ug_dados_bancarios_02_agencia'=> (string) trim(strtoupper($objGamesUsuario->getDadosBancarios02Agencia())),
                                ':ug_dados_bancarios_02_conta'=> (string) trim(strtoupper($objGamesUsuario->getDadosBancarios02Conta())),
                                ':ug_dados_bancarios_02_abertura'=> (string) trim(strtoupper($objGamesUsuario->getDadosBancarios02Abertura())),
                                ':ug_computadores_qtde'     => $objGamesUsuario->getComputadoresQtde(),
                                ':ug_comunicacao_visual'    => (string) trim(strtoupper($objGamesUsuario->getComunicacaoVisual())),
                                ':ug_perfil_limite_ref'     => moeda2numeric($objGamesUsuario->getPerfilLimiteRef()),
                                ':ug_ficou_sabendo'         => (string) trim(strtoupper($objGamesUsuario->getFicouSabendo())),
                                ':ug_compet_participa'      => (string) trim(strtoupper($objGamesUsuario->getCompet_participa())),
                                ':ug_compet_promoveu'       => (string) trim(strtoupper($objGamesUsuario->getCompet_promoveu())),
                                ':ug_compet_participantes_fifa'=> $objGamesUsuario->getCompet_participantes_fifa(),
                                ':ug_compet_participantes_wc3'=> $objGamesUsuario->getCompet_participantes_wc3(),
                                ':ug_ongame'                => (string) $objGamesUsuario->getUgOngame(),
                                ':ug_id_nexcafe'            => (string) $objGamesUsuario->getUgIdNexCafe(),
                                ':ug_login_nexcafe_auto'    => $objGamesUsuario->getUgLoginNexCafeAuto(),
                                ':ug_data_inclusao_nexcafe' => (($objGamesUsuario->getUgIdNexCafe()) ? date("Y-m-d H:i:s") : NULL), /* Se o Cadastro ou Edição foi acionado pelo NexCafé */
                                ':ug_te_id'                 => $objGamesUsuario->getTipoEstabelecimentoParaBanco(),
                                ':ug_alterar_senha'         => intval($objGamesUsuario->getAlteraSenha() * 1),
                                ':ug_exibir_contrato'       => intval($objGamesUsuario->getExibirContrato() * 1) ,
                                ':ug_tipo_venda'            => (string) $objGamesUsuario->getTipoVenda(),
                                ':ug_data_aceite_adesao'    => (($objGamesUsuario->getDataAceite()) ? date("Y-m-d H:i:s") : NULL),
                                ':ug_repr_legal_data_nascimento' => (($objGamesUsuario->getReprLegalDataNascimento()) ? $objGamesUsuario->getReprLegalDataNascimento() : NULL),
								':ug_canais_venda' => (string) trim($objGamesUsuario->getCanaisVenda())
                              );
            //SQL
            $sql = "insert into dist_usuarios_games";

            foreach($tmpArray as $ind => $val){
                if(isset($columns)){
                    $columns .= ", ".str_replace(":","",$ind);
                    $values  .= ", ".$ind;
                }else{
                    $columns    = str_replace(":","",$ind);
                    $values     = $ind;
                }
                
            } //end foreach
            
            $sql .= "(".$columns.") values (".$values.");";
            
            try {
                //Tentando executar a Query de Insert
                $rs = $pdo->prepare($sql);
                $rs->execute($tmpArray);
                
                $ret = "";
                
                $sql = "select currval('dist_usuarios_games_id_seq') as last_id;";
                $rs_id = $pdo->prepare($sql);
                $rs_id->execute();
                $rs_id_row = $rs_id->fetch(PDO::FETCH_ASSOC);
                $objGamesUsuario->setId($rs_id_row["last_id"]);
                
                $array_nomes_s = $objGamesUsuario->getNomeSocios();
                $array_cpf_s = $objGamesUsuario->getCPFSocios();
                $array_data_nascimento_s = $objGamesUsuario->getDataNascimentoSocios();
                $array_porcentagem_s = $objGamesUsuario->getPorcentagemSocios();

                if(is_array($array_nomes_s) && is_array($array_cpf_s) && is_array($array_data_nascimento_s) && is_array($array_porcentagem_s)){
                    if((count($array_nomes_s) === count($array_cpf_s)) && (count($array_cpf_s) === count($array_data_nascimento_s)) && (count($array_data_nascimento_s) === count($array_porcentagem_s)))
                    {
                        $count = count($array_nomes_s);
                    }
                }
                if(!is_null($objGamesUsuario->getId())) {
                    for($i=0; $i < $count; $i++){

                        $array_socios = array(  ':ug_id' => $objGamesUsuario->getId(),
                                                ':ugs_nome' => fix_name_cpf($array_nomes_s[$i]),
                                                ':ugs_cpf' => str_replace('.', '', str_replace('-','',$array_cpf_s[$i])),
                                                ':ugs_data_nascimento' => formata_data($array_data_nascimento_s[$i], 1),
                                                ':ugs_porcentagem' => str_replace(',','.',str_replace('%', '', $array_porcentagem_s[$i]))
                                            );

                        $sql_socios = "INSERT INTO dist_usuarios_games_socios values(:ug_id, :ugs_nome, :ugs_cpf, :ugs_data_nascimento, :ugs_porcentagem);";

                        $rs_socios = $pdo->prepare($sql_socios);
                        $rs_socios->execute($array_socios);
                        
                        unset($array_socios);
                    }
                }
                $sql = "SELECT to_char(ugo_data,'DD/MM/YYYY HH24:MI:SS') as data,* FROM dist_usuarios_games_obs WHERE ug_id = ".$rs_row['ug_id']." order by ugo_data ASC;";
                $rs_usuario_obs = SQLexecuteQuery($sql);
                $ug_obs= "" ;
                if(pg_num_rows($rs_usuario_obs) > 0) { 
                        while($rs_usuario_obs_row = pg_fetch_array($rs_usuario_obs)) {
                            $ug_obs .= "Em ".$rs_usuario_obs_row['data'].PHP_EOL."Autor: ".$rs_usuario_obs_row['ugo_user_insert'].PHP_EOL."Observao:".PHP_EOL.$rs_usuario_obs_row['ug_obs'].PHP_EOL.str_repeat("-",40).PHP_EOL;
                        }//end while
                } //end if(pg_num_rows($rs_usuario) > 0)

                if(!is_null($ug_obs)) {
                    if(trim($ug_obs) != "") {  
                        $sql_insert_obs = "INSERT INTO dist_usuarios_games_obs VALUES (".$objGamesUsuario->getId().",". SQLaddFields(trim($ug_obs), "s") . ",'".$GLOBALS['_SESSION']['userlogin_bko']."');";
                        $ret_insert_obs = SQLexecuteQuery($sql_insert_obs);
                        if(!$ret_insert_obs) echo "Erro ao atualizar Observação do Usuário.".PHP_EOL;
                    }//end if(trim($objGamesUsuario->getObservacoes()) != "")
                } //end if(!is_null($objGamesUsuario->getObservacoes()))  

                //Log na base
                usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CRIACAO_DO_CADASTRO'], $objGamesUsuario->getId(), null);

                /* Se o Cadastro ou Edição foi acionado pelo NexCafé, Grava o Log de Cadastro CADASTRO_LANHOUSE_VIA_NEXCAFE */
                if ($objGamesUsuario->getUgIdNexCafe()) {
                    usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CADASTRO_LANHOUSE_VIA_NEXCAFE'], $objGamesUsuario->getId(), null);
                }


                //Envia email
                //--------------------------------------------------------------------------------
                /*
                $parametros['prepag_dominio'] = "EPREPAG_URL_HTTP";
                $parametros['nome_fantasia'] = $objGamesUsuario->getNomefantasia();
                $parametros['tipo_cadastro'] = $objGamesUsuario->getTipoCadastro();
                $parametros['nome'] = $objGamesUsuario->getNome();
                $parametros['sexo'] = $objGamesUsuario->getSexo();

                $msgEmail = email_cabecalho($parametros);
                $msgEmail .= "  <br><br>
                                <table border='0' cellspacing='0'>
                                <tr><td>&nbsp;</td></tr>
                                <tr valign='middle' bgcolor='#FFFFFF'>
                                    <td align='left' class='texto'>
                                        Confirmamos a recepção do seu pedido de cadastro junto ao E-Prepag LanHouses. 
                                        O seu pedido será submetido a análise e você será informado por e-mail quanto ao aceite, ou não, do seu pedido.<br>
                                    </td>
                                </tr>
                                <tr><td>&nbsp;</td></tr>
                                </table>
                            ";
                $msgEmail .= email_rodape($parametros);
                enviaEmail($objGamesUsuario->getEmail(), null, null, "E-Prepag - Pedido de Cadastro", $msgEmail);
                UsuarioGames::logEvents("Teste deu tudo Certo!!!".PHP_EOL);
                */
                
                $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'CadastroLAN');
                $envioEmail->setUgID($objGamesUsuario->getId());
                $envioEmail->MontaEmailEspecifico();
                
            } catch(PDOException $e) {
                
                UsuarioGames::logEvents($e->getMessage());
                UsuarioGames::logEvents(print_r($pdo->errorInfo()));
            }
            
        } //end if ($ret == "")

        return $ret;
    } //end function inserirPDO

    
    function logEvents($msg) {
            global $raiz_do_projeto;

            $fileLog = $raiz_do_projeto.'log/log_class_users_LANs_PDO-Errors.log';

            $log  = "=================================================================================================".PHP_EOL;
            $log .= "DATA -> ".date("d/m/Y - H:i:s")."".PHP_EOL;
            $log .= "---------------------------------".PHP_EOL;
            $log .= htmlspecialchars_decode($msg);			

            $fp = fopen($fileLog, 'a+');
            fwrite($fp, $log);
            fclose($fp);		
    }//end function logEvents

    function atualizar($objGamesUsuario) {

        $ret = UsuarioGames::validarCampos($objGamesUsuario, false);

        if ($ret == "") {
            if (UsuarioGames::existeLogin($objGamesUsuario->getLogin(), $objGamesUsuario->getId())) {
                $ret = "Login já cadastrado.";
            }
        }

        if ($ret == "") {
            if (UsuarioGames::existeCNPJ($objGamesUsuario->getCNPJ(), $objGamesUsuario->getId())) {
                $ret = "CNPJ já cadastrado.";
            }
        }
        
        if ($ret == "") {
            if (UsuarioGames::existeEmail($objGamesUsuario->getEmail(), $objGamesUsuario->getId())) {
                $ret .= "Email já cadastrado.".PHP_EOL;
            }
        }
        
        
        if ($ret == "") {
            if (UsuarioGames::existeCPF($objGamesUsuario->getCPF(), $objGamesUsuario->getId())) {
                $ret = "CPF já cadastrado.";
            }
        }
/*
        if ($ret == "") {
            if (UsuarioGames::existeRG($objGamesUsuario->getRG(), $objGamesUsuario->getId())) {
                $ret = "RG já cadastrado.";
            }
        }
        */

        if ($ret == "") {

            //Formata
             if(!is_null($objGamesUsuario->getDataNascimento())) $dataNascimento = formata_data($objGamesUsuario->getDataNascimento(), 1);

            //SQL
            $sql = "update dist_usuarios_games set ";
             if(!is_null($objGamesUsuario->getAtivo()))             $sql .= " ug_ativo = "                 . SQLaddFields(trim($objGamesUsuario->getAtivo()), "") . ",";
            if(!is_null($objGamesUsuario->getStatusBusca()))     $sql .= " ug_status = "             . SQLaddFields(trim($objGamesUsuario->getStatusBusca()), "") . ",";
            if(!is_null($objGamesUsuario->getSubstatus()))             $sql .= " ug_substatus = "                 . SQLaddFields(trim($objGamesUsuario->getSubstatus()), "") . ",";
            if(!is_null($objGamesUsuario->getLogin()))             $sql .= " ug_login = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getLogin())), "s") . ",";

             if(!is_null($objGamesUsuario->getNomeFantasia()))     $sql .= " ug_nome_fantasia = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getNomeFantasia())), "s") . ",";
             if(!is_null($objGamesUsuario->getRazaoSocial()))     $sql .= " ug_razao_social = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getRazaoSocial())), "s") . ",";
             if(!is_null($objGamesUsuario->getCNPJ()))             $sql .= " ug_cnpj = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getCNPJ())), "s") . ",";
             if(!is_null($objGamesUsuario->getResponsavel()))     $sql .= " ug_responsavel = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getResponsavel())), "s") . ",";
             if(!is_null($objGamesUsuario->getEmail()))             $sql .= " ug_email = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getEmail())), "s") . ",";

             if(!is_null($objGamesUsuario->getEndereco()))         $sql .= " ug_endereco = "             . SQLaddFields(trim($objGamesUsuario->getEndereco()), "s") . ",";
            if(!is_null($objGamesUsuario->getTipoEnd()))         $sql .= " ug_tipo_end = "             . SQLaddFields(trim($objGamesUsuario->getTipoEnd()), "s") . ",";
             if(!is_null($objGamesUsuario->getNumero()))         $sql .= " ug_numero = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getNumero())), "s") . ",";
             if(!is_null($objGamesUsuario->getComplemento()))     $sql .= " ug_complemento = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getComplemento())), "s") . ",";
             if(!is_null($objGamesUsuario->getBairro()))         $sql .= " ug_bairro = "             . SQLaddFields(trim($objGamesUsuario->getBairro()), "s") . ",";
             if(!is_null($objGamesUsuario->getCidade()))         $sql .= " ug_cidade = "             . SQLaddFields(trim($objGamesUsuario->getCidade()), "s") . ",";
             if(!is_null($objGamesUsuario->getEstado()))         $sql .= " ug_estado = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getEstado())), "s") . ",";
             if(!is_null($objGamesUsuario->getCEP()))             $sql .= " ug_cep = "                 . SQLaddFields(trim($objGamesUsuario->getCEP()), "s") . ",";

             if(!is_null($objGamesUsuario->getTelDDI()))         $sql .= " ug_tel_ddi = "             . SQLaddFields(trim($objGamesUsuario->getTelDDI()), "s") . ",";
             if(!is_null($objGamesUsuario->getTelDDD()))         $sql .= " ug_tel_ddd = "             . SQLaddFields(trim($objGamesUsuario->getTelDDD()), "s") . ",";
             if(!is_null($objGamesUsuario->getTel()))             $sql .= " ug_tel = "                 . SQLaddFields(trim($objGamesUsuario->getTel()), "s") . ",";
             if(!is_null($objGamesUsuario->getCelDDI()))         $sql .= " ug_cel_ddi = "             . SQLaddFields(trim($objGamesUsuario->getCelDDI()), "s") . ",";
             if(!is_null($objGamesUsuario->getCelDDD()))         $sql .= " ug_cel_ddd = "             . SQLaddFields(trim($objGamesUsuario->getCelDDD()), "s") . ",";
             if(!is_null($objGamesUsuario->getCelDDD()))         $sql .= " ug_cel = "                 . SQLaddFields(trim($objGamesUsuario->getCel()), "s") . " ,";
             if(!is_null($objGamesUsuario->getFaxDDI()))         $sql .= " ug_fax_ddi = "             . SQLaddFields(trim($objGamesUsuario->getFaxDDI()), "s") . ",";
             if(!is_null($objGamesUsuario->getFaxDDD()))         $sql .= " ug_fax_ddd = "             . SQLaddFields(trim($objGamesUsuario->getFaxDDD()), "s") . ",";
             if(!is_null($objGamesUsuario->getFax()))             $sql .= " ug_fax = "                 . SQLaddFields(trim($objGamesUsuario->getFax()), "s") . ",";

             if(!is_null($objGamesUsuario->getRACodigo()))         $sql .= " ug_ra_codigo = "             . SQLaddFields(trim($objGamesUsuario->getRACodigo()), "s") . ",";
             if(!is_null($objGamesUsuario->getRAOutros()))         $sql .= " ug_ra_outros = "             . SQLaddFields(trim($objGamesUsuario->getRAOutros()), "s") . ",";

             if(!is_null($objGamesUsuario->getContato01TelDDI()))$sql .= " ug_contato01_tel_ddi = "     . SQLaddFields(trim($objGamesUsuario->getContato01TelDDI()), "s") . ",";
             if(!is_null($objGamesUsuario->getContato01TelDDD()))$sql .= " ug_contato01_tel_ddd = "     . SQLaddFields(trim($objGamesUsuario->getContato01TelDDD()), "s") . ",";
             if(!is_null($objGamesUsuario->getContato01Tel()))     $sql .= " ug_contato01_tel = "         . SQLaddFields(trim($objGamesUsuario->getContato01Tel()), "s") . ",";
             if(!is_null($objGamesUsuario->getContato01Nome()))     $sql .= " ug_contato01_nome = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getContato01Nome())), "s") . ",";
             if(!is_null($objGamesUsuario->getContato01Cargo())) $sql .= " ug_contato01_cargo = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getContato01Cargo())), "s") . ",";
             $sql = "SELECT to_char(ugo_data,'DD/MM/YYYY HH24:MI:SS') as data,* FROM dist_usuarios_games_obs WHERE ug_id = ".$rs_row['ug_id']." order by ugo_data ASC;";
             $rs_usuario_obs = SQLexecuteQuery($sql);
             $ug_obs= "" ;
             if(pg_num_rows($rs_usuario_obs) > 0) { 
                     while($rs_usuario_obs_row = pg_fetch_array($rs_usuario_obs)) {
                         $ug_obs .= "Em ".$rs_usuario_obs_row['data'].PHP_EOL."Autor: ".$rs_usuario_obs_row['ugo_user_insert'].PHP_EOL."Observao:".PHP_EOL.$rs_usuario_obs_row['ug_obs'].PHP_EOL.str_repeat("-",40).PHP_EOL;
                     }//end while
             } //end if(pg_num_rows($rs_usuario) > 0)

             if(!is_null($ug_obs)) {
                 if(trim($ug_obs) != "") {  
                     $sql_insert_obs = "INSERT INTO dist_usuarios_games_obs VALUES (".$objGamesUsuario->getId().",". SQLaddFields(trim($ug_obs), "s") . ",'".$GLOBALS['_SESSION']['userlogin_bko']."');";
                    
            // if(!is_null($objGamesUsuario->getObservacoes())) {
            //     if(trim($objGamesUsuario->getObservacoes()) != "") {  
            //         $sql_insert_obs = "INSERT INTO dist_usuarios_games_obs VALUES (".$objGamesUsuario->getId().",". SQLaddFields(trim($objGamesUsuario->getObservacoes()), "s") . ",'".$GLOBALS['_SESSION']['userlogin_bko']."');";
                    //echo $sql_insert_obs;
                    $ret_insert_obs = SQLexecuteQuery($sql_insert_obs);
                    if(!$ret_insert_obs) echo "Erro ao atualizar Observação do Usuário.".PHP_EOL;
                }//end if(trim($objGamesUsuario->getObservacoes()) != "")
            } //end if(!is_null($objGamesUsuario->getObservacoes())) 
            
            if (!is_null($objGamesUsuario->getRiscoClassif())) {
                if (array_key_exists($objGamesUsuario->getRiscoClassif(), $GLOBALS['RISCO_CLASSIFICACAO_NOMES'])) {
                    $sql .= " ug_risco_classif = " . SQLaddFields(trim($objGamesUsuario->getRiscoClassif()), "") . ",";
                }
            }

             if(!is_null($objGamesUsuario->getTipoCadastro()))     $sql .= " ug_tipo_cadastro = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getTipoCadastro())), "s") . ",";
             if(!is_null($objGamesUsuario->getNome()))             $sql .= " ug_nome = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getNome())), "s") . ",";
             if(!is_null($objGamesUsuario->getCPF()))             $sql .= " ug_cpf = "                 . SQLaddFields(trim($objGamesUsuario->getCPF()), "s") . ",";
             if(!is_null($objGamesUsuario->getRG()))             $sql .= " ug_rg = "                 . SQLaddFields(trim($objGamesUsuario->getRG()), "s") . ",";
             if(!is_null($objGamesUsuario->getDataNascimento())) $sql .= " ug_data_nascimento = "    . SQLaddFields(trim($dataNascimento), "s") . ",";
             if(!is_null($objGamesUsuario->getSexo()))             $sql .= " ug_sexo = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getSexo())), "s") . ",";

             if(!is_null($objGamesUsuario->getPerfilSenhaReimpressao()))     $sql .= " ug_perfil_senha_reimpressao = "         . SQLaddFields(trim($objGamesUsuario->getPerfilSenhaReimpressao()), "s") . ",";
            if(!is_null($objGamesUsuario->getPerfilFormaPagto()))             $sql .= " ug_perfil_forma_pagto = "             . SQLaddFields( (($objGamesUsuario->getPerfilFormaPagto())?$objGamesUsuario->getPerfilFormaPagto():0), "") . ",";
            if(!is_null($objGamesUsuario->getPerfilLimite()))                 $sql .= " ug_perfil_limite = "                     . SQLaddFields(moeda2numeric($objGamesUsuario->getPerfilLimite()), "") . ",";
             if(!is_null($objGamesUsuario->getPerfilSaldo()))                 $sql .= " ug_perfil_saldo = "                     . SQLaddFields(moeda2numeric($objGamesUsuario->getPerfilSaldo()), "") . ",";

             if(!is_null($objGamesUsuario->getPerfilCorteDiaSemana()))         $sql .= " ug_perfil_corte_dia_semana = "         . SQLaddFields($objGamesUsuario->getPerfilCorteDiaSemana(), "") . ",";
             if(!is_null($objGamesUsuario->getPerfilCorteUltimoCorte()))     $sql .= " ug_perfil_corte_ultimo_corte = "         . SQLaddFields($objGamesUsuario->getPerfilCorteUltimoCorte(), "s") . ",";
             if(!is_null($objGamesUsuario->getPerfilLimiteSugerido()))         $sql .= " ug_perfil_limite_sugerido = "         . SQLaddFields(moeda2numeric($objGamesUsuario->getPerfilLimiteSugerido()), "") . ",";
             if(!is_null($objGamesUsuario->getCreditoPendente()))             $sql .= " ug_credito_pendente = "                 . SQLaddFields(moeda2numeric($objGamesUsuario->getCreditoPendente()), "") . ",";

             if(!is_null($objGamesUsuario->getInscrEstadual()))                 $sql .= " ug_inscr_estadual = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getInscrEstadual())), "s") . ",";    
             if(!is_null($objGamesUsuario->getSite()))                         $sql .= " ug_site = "                             . SQLaddFields(strtoupper($objGamesUsuario->getSite()), "s") . ",";            
             if(!is_null($objGamesUsuario->getAberturaAno()))                 $sql .= " ug_abertura_ano = "                     . SQLaddFields($objGamesUsuario->getAberturaAno(), "") . ",";        
             if(!is_null($objGamesUsuario->getAberturaMes()))                 $sql .= " ug_abertura_mes = "                     . SQLaddFields($objGamesUsuario->getAberturaMes(), "") . ",";        
             if(!is_null($objGamesUsuario->getCartoes()))                     $sql .= " ug_cartoes = "                         . SQLaddFields(trim(strtoupper($objGamesUsuario->getCartoes())), "s") . ",";        
             if(!is_null($objGamesUsuario->getFaturaMediaMensal()))             $sql .= " ug_fatura_media_mensal = "             . SQLaddFields($objGamesUsuario->getFaturaMediaMensal(), "") . ",";

             if(!is_null($objGamesUsuario->getReprLegalNome()))                 $sql .= " ug_repr_legal_nome = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalNome())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalRG()))                 $sql .= " ug_repr_legal_rg = "                     . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalRG())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprLegalCPF()))                 $sql .= " ug_repr_legal_cpf = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCPF())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprLegalTelDDI()))             $sql .= " ug_repr_legal_tel_ddi = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalTelDDI())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalTelDDD()))             $sql .= " ug_repr_legal_tel_ddd = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalTelDDD())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalTel()))                 $sql .= " ug_repr_legal_tel = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalTel())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprLegalCelDDI()))             $sql .= " ug_repr_legal_cel_ddi = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCelDDI())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalCelDDD()))             $sql .= " ug_repr_legal_cel_ddd = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCelDDD())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalCel()))                 $sql .= " ug_repr_legal_cel = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCel())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprLegalEmail()))             $sql .= " ug_repr_legal_email = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalEmail())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalMSN()))                 $sql .= " ug_repr_legal_msn = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalMSN())), "s") . ",";        

             if(!is_null($objGamesUsuario->getReprVendaIgualReprLegal()))    $sql .= " ug_repr_venda_igual_repr_legal = "     . SQLaddFields($objGamesUsuario->getReprVendaIgualReprLegal(), "") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaNome()))                 $sql .= " ug_repr_venda_nome = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaNome())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaRG()))                 $sql .= " ug_repr_venda_rg = "                     . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaRG())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprVendaCPF()))                 $sql .= " ug_repr_venda_cpf = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCPF())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprVendaTelDDI()))             $sql .= " ug_repr_venda_tel_ddi = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaTelDDI())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaTelDDD()))             $sql .= " ug_repr_venda_tel_ddd = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaTelDDD())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaTel()))                 $sql .= " ug_repr_venda_tel = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaTel())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprVendaCelDDI()))             $sql .= " ug_repr_venda_cel_ddi = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCelDDI())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaCelDDD()))             $sql .= " ug_repr_venda_cel_ddd = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCelDDD())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaCel()))                 $sql .= " ug_repr_venda_cel = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCel())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprVendaEmail()))             $sql .= " ug_repr_venda_email = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaEmail())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaMSN()))                 $sql .= " ug_repr_venda_msn = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaMSN())), "s") . ",";        

             if(!is_null($objGamesUsuario->getDadosBancarios01Banco()))         $sql .= " ug_dados_bancarios_01_banco = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Banco())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios01Agencia()))     $sql .= " ug_dados_bancarios_01_agencia = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Agencia())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios01Conta()))         $sql .= " ug_dados_bancarios_01_conta = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Conta())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios01Abertura()))     $sql .= " ug_dados_bancarios_01_abertura = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Abertura())), "s") . ",";    

             if(!is_null($objGamesUsuario->getDadosBancarios02Banco()))         $sql .= " ug_dados_bancarios_02_banco = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Banco())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios02Agencia()))     $sql .= " ug_dados_bancarios_02_agencia = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Agencia())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios02Conta()))         $sql .= " ug_dados_bancarios_02_conta = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Conta())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios02Abertura()))     $sql .= " ug_dados_bancarios_02_abertura = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Abertura())), "s") . ",";    

             if(!is_null($objGamesUsuario->getComputadoresQtde()))             $sql .= " ug_computadores_qtde = "                 . SQLaddFields($objGamesUsuario->getComputadoresQtde(), "") . ",";        
             if(!is_null($objGamesUsuario->getComunicacaoVisual()))             $sql .= " ug_comunicacao_visual = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getComunicacaoVisual())), "s") . ",";        

             if(!is_null($objGamesUsuario->getPerfilLimiteRef()))                 $sql .= " ug_perfil_limite_ref = "                     . SQLaddFields(moeda2numeric($objGamesUsuario->getPerfilLimiteRef()), "") . ",";

             if(!is_null($objGamesUsuario->getFicouSabendo()))                 $sql .= " ug_ficou_sabendo = "                     . SQLaddFields(trim(strtoupper($objGamesUsuario->getFicouSabendo())), "s") . ",";    

             if(!is_null($objGamesUsuario->getCompet_participa()))                 $sql .= " ug_compet_participa = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getCompet_participa())), "s") . ",";    
             if(!is_null($objGamesUsuario->getCompet_promoveu()))                 $sql .= " ug_compet_promoveu = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getCompet_promoveu())), "s") . ",";    
             if(!is_null($objGamesUsuario->getCompet_participantes_fifa()))         $sql .= " ug_compet_participantes_fifa = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getCompet_participantes_fifa())), "") . ",";    
             if(!is_null($objGamesUsuario->getCompet_participantes_wc3()))         $sql .= " ug_compet_participantes_wc3 = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getCompet_participantes_wc3())), "") . ",";    

            if(!is_null($objGamesUsuario->getUgOngame()))     $sql .= " ug_ongame = ". SQLaddFields(trim(strtoupper($objGamesUsuario->getUgOngame())), "s") . ",";

            if(!is_null($objGamesUsuario->getTipoEstabelecimento()))    $sql .= " ug_te_id = ". SQLaddFields(trim(strtoupper($objGamesUsuario->getTipoEstabelecimentoParaBanco())), "") . ",";    

            if(!is_null($objGamesUsuario->getUgIdNexCafe())) $sql .= " ug_id_nexcafe = ". SQLaddFields(trim(strtoupper($objGamesUsuario->getUgIdNexCafe())), "s") . ",";
            if(!is_null($objGamesUsuario->getUgLoginNexCafeAuto())) $sql .= " ug_login_nexcafe_auto = ". SQLaddFields(trim(strtoupper($objGamesUsuario->getUgLoginNexCafeAuto())), "") . ",";

            if(!is_null($objGamesUsuario->getAlteraSenha())) $sql .= " ug_alterar_senha = ". intval(SQLaddFields($objGamesUsuario->getAlteraSenha(), "")*1) . ",";
            if(!is_null($objGamesUsuario->getExibirContrato())) $sql .= " ug_exibir_contrato = ". intval(SQLaddFields($objGamesUsuario->getExibirContrato(), "")*1) . ",";
            if(!is_null($objGamesUsuario->getDataAceite())) $sql .= " ug_data_aceite_adesao = ". SQLaddFields((($objGamesUsuario->getDataAceite())?"CURRENT_TIMESTAMP":"NULL"), "") . ",";

            if(!is_null($objGamesUsuario->getRecargaCelular())) $sql .= " ug_recarga_celular = ". intval(SQLaddFields($objGamesUsuario->getRecargaCelular(), "")*1) . ",";

            if(!is_null($objGamesUsuario->getTipoVenda())) $sql .= " ug_tipo_venda = ". SQLaddFields($objGamesUsuario->getTipoVenda(), "s") . ",";
			
			if(!is_null($objGamesUsuario->getCanaisVenda())) $sql .= " ug_canais_venda = ". SQLaddFields($objGamesUsuario->getCanaisVenda());
			
            if($objGamesUsuario->getDataAprovacao() == "" && $objGamesUsuario->getSubstatus() == 11) $sql .= " ug_data_aprovacao = NOW(),";
            
            if(substr($sql, -1) == ",") $sql = substr($sql, 0, strlen($sql) - 1);

            $sql .= " where ug_id = " . SQLaddFields($objGamesUsuario->getId(), "");

            switch ($objGamesUsuario->getSubstatus()) {
                    case 2:
                        // Caso a lan que deve retornar contato 
                        //$envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'RetornarContato');
                        //$envioEmail->setUgID($objGamesUsuario->getId());
                        //$envioEmail->MontaEmailEspecifico();
                        break;
                    case 3:
                        // Caso a lan dados insuficientes para a aprovação do cadastro
                        //$envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'DadosInsuficientes');
                       // $envioEmail->setUgID($objGamesUsuario->getId());
                       // $envioEmail->MontaEmailEspecifico();
                        break;
                    case 4:
                        // Caso a lan não seja aprovada, enviar e-mail.
                        $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'PedidoNegadoLan');
                        $envioEmail->setUgID($objGamesUsuario->getId());
                        $envioEmail->MontaEmailEspecifico();
                        break;
                    case 9:
                        // Lan house aprovada, mas ainda não fez a primeira compra
                        if($objGamesUsuario->getDataAprovacao() == "") {
                            $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'LanAprovada');
                            $envioEmail->setUgID($objGamesUsuario->getId());
                            $envioEmail->MontaEmailEspecifico();
                        }//end if($objGamesUsuario->getDataAprovacao() == "")
                        break;
                    case 11:
                        // Lan house aprovada
                        if($objGamesUsuario->getDataAprovacao() == "") {
                            $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'LanAprovada');
                            $envioEmail->setUgID($objGamesUsuario->getId());
                            $envioEmail->MontaEmailEspecifico();
                        }//end if($objGamesUsuario->getDataAprovacao() == "")
                        break;
            }
            $ret = SQLexecuteQuery($sql);
            if(!$ret) $ret = "Erro ao atualizar usuário.".PHP_EOL;
            else {
                $ret = "";
            }

        }

        return $ret;
    }

    function apenas_validar($objGamesUsuario) {
        $ret_all = "";
        $ret = "";

        $ret_all = UsuarioGames::validarCampos($objGamesUsuario, true);

        if ($ret == "") {
            if (UsuarioGames::existeLogin($objGamesUsuario->getLogin(), $objGamesUsuario->getId())) {
                $ret_all .= "Login já cadastrado.".PHP_EOL;
            }
        }

        if ($ret == "") {
            if (UsuarioGames::existeCNPJ($objGamesUsuario->getCNPJ(), $objGamesUsuario->getId())) {
                $ret_all .= "CNPJ já cadastrado.".PHP_EOL;
            }
        }
        
        if ($ret == "") {
            if (UsuarioGames::existeEmail($objGamesUsuario->getEmail(), $objGamesUsuario->getId())) {
                $ret_all .= "Email já cadastrado.".PHP_EOL;
            }
        }
        
        
        if ($ret == "") {
            if (UsuarioGames::existeCPF($objGamesUsuario->getCPF(), $objGamesUsuario->getId())) {
                $ret_all .= "CPF já cadastrado.".PHP_EOL;
            }
        }
/*
        if ($ret == "") {
            if (UsuarioGames::existeRG($objGamesUsuario->getRG(), $objGamesUsuario->getId())) {
                $ret_all .= "RG já cadastrado.".PHP_EOL;
            }
        }
        */
        return $ret_all . "<br>" . $ret;
    }

    function atualizar_sem_validar($objGamesUsuario, $ateracao_usuario = true) {
        $ret_all = "";
        $ret = "";
        
        $ret_all = UsuarioGames::validarCampos($objGamesUsuario, false, $ateracao_usuario);
        
        if ($ret == "") {
            if (UsuarioGames::existeLogin($objGamesUsuario->getLogin(), $objGamesUsuario->getId())) {
                $ret_all .= "Login já cadastrado.".PHP_EOL;
            }
        }

        if ($ret == "") {
            if (UsuarioGames::existeCNPJ($objGamesUsuario->getCNPJ(), $objGamesUsuario->getId())) {
                $ret_all .= "CNPJ já cadastrado.".PHP_EOL;
            }
        }
        
        if ($ret == "") {
            if (UsuarioGames::existeEmail($objGamesUsuario->getEmail(), $objGamesUsuario->getId())) {
                $ret_all .= "Email já cadastrado.".PHP_EOL;
            }
        }
		
		$sqlUltimoEmail = "select ug_email from dist_usuarios_games where ug_id = ".$objGamesUsuario->getId().";";
        $retEmail = SQLexecuteQuery($sqlUltimoEmail);
		$rsUltimoEmail = pg_fetch_array($retEmail);
		
        
        if ($ret == "") {
            if (UsuarioGames::existeCPF($objGamesUsuario->getCPF(), $objGamesUsuario->getId())) {
                $ret_all .= "CPF já cadastrado.".PHP_EOL;
            }
        }
/*
        if ($ret == "") {
            if (UsuarioGames::existeRG($objGamesUsuario->getRG(), $objGamesUsuario->getId())) {
                $ret_all .= "RG já cadastrado.".PHP_EOL;
            }
        }
        */
        if ($ret == "") {

            //Formata
             if(!is_null($objGamesUsuario->getDataNascimento()) && $objGamesUsuario->getDataNascimento() != "") $dataNascimento = formata_data($objGamesUsuario->getDataNascimento(), 1);
             if($objGamesUsuario->getDataExpiraSenha()) $dataExpiraSenha = formata_data($objGamesUsuario->getDataExpiraSenha(), 1);
             if(!is_null($objGamesUsuario->getReprLegalDataNascimento())) $dataNascimentoRepr = $objGamesUsuario->getReprLegalDataNascimento();

            //SQL
            $sql = "update dist_usuarios_games set ";
             if(!is_null($objGamesUsuario->getAtivo()))             $sql .= " ug_ativo = "                 . SQLaddFields(trim($objGamesUsuario->getAtivo()), "") . ",";
            if(!is_null($objGamesUsuario->getStatusBusca()))     $sql .= " ug_status = "             . SQLaddFields(trim($objGamesUsuario->getStatusBusca()), "") . ",";
            if(!is_null($objGamesUsuario->getSubstatus()))             $sql .= " ug_substatus = "                 . SQLaddFields(trim($objGamesUsuario->getSubstatus()), "") . ",";
             if(!is_null($objGamesUsuario->getLogin()))             $sql .= " ug_login = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getLogin())), "s") . ",";

             if(!is_null($objGamesUsuario->getNomeFantasia()))     $sql .= " ug_nome_fantasia = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getNomeFantasia())), "s") . ",";
             if(!is_null($objGamesUsuario->getRazaoSocial()))     $sql .= " ug_razao_social = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getRazaoSocial())), "s") . ",";
             if(!is_null($objGamesUsuario->getCNPJ()))             $sql .= " ug_cnpj = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getCNPJ())), "s") . ",";
             if(!is_null($objGamesUsuario->getResponsavel()))     $sql .= " ug_responsavel = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getResponsavel())), "s") . ",";
             if(!is_null($objGamesUsuario->getEmail()))             $sql .= " ug_email = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getEmail())), "s") . ",";

             if(!is_null($objGamesUsuario->getEndereco()))         $sql .= " ug_endereco = "             . SQLaddFields(trim($objGamesUsuario->getEndereco()), "s") . ",";
            if(!is_null($objGamesUsuario->getTipoEnd()))         $sql .= " ug_tipo_end = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getTipoEnd())), "s") . ",";
             if(!is_null($objGamesUsuario->getNumero()))         $sql .= " ug_numero = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getNumero())), "s") . ",";
             if(!is_null($objGamesUsuario->getComplemento()))     $sql .= " ug_complemento = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getComplemento())), "s") . ",";
             if(!is_null($objGamesUsuario->getBairro()))         $sql .= " ug_bairro = "             . SQLaddFields(trim($objGamesUsuario->getBairro()), "s") . ",";
             if(!is_null($objGamesUsuario->getCidade()))         $sql .= " ug_cidade = "             . SQLaddFields(trim($objGamesUsuario->getCidade()), "s") . ",";
             if(!is_null($objGamesUsuario->getEstado()))         $sql .= " ug_estado = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getEstado())), "s") . ",";
             if(!is_null($objGamesUsuario->getCEP()))             $sql .= " ug_cep = "                 . SQLaddFields(trim($objGamesUsuario->getCEP()), "s") . ",";

             if(!is_null($objGamesUsuario->getTelDDI()))         $sql .= " ug_tel_ddi = "             . SQLaddFields(trim($objGamesUsuario->getTelDDI()), "s") . ",";
             if(!is_null($objGamesUsuario->getTelDDD()))         $sql .= " ug_tel_ddd = "             . SQLaddFields(trim($objGamesUsuario->getTelDDD()), "s") . ",";
             if(!is_null($objGamesUsuario->getTel()))             $sql .= " ug_tel = "                 . SQLaddFields(trim($objGamesUsuario->getTel()), "s") . ",";
             if(!is_null($objGamesUsuario->getCelDDI()))         $sql .= " ug_cel_ddi = "             . SQLaddFields(trim($objGamesUsuario->getCelDDI()), "s") . ",";
             if(!is_null($objGamesUsuario->getCelDDD()))         $sql .= " ug_cel_ddd = "             . SQLaddFields(trim($objGamesUsuario->getCelDDD()), "s") . ",";
             if(!is_null($objGamesUsuario->getCelDDD()))         $sql .= " ug_cel = "                 . SQLaddFields(trim($objGamesUsuario->getCel()), "s") . " ,";
             if(!is_null($objGamesUsuario->getFaxDDI()))         $sql .= " ug_fax_ddi = "             . SQLaddFields(trim($objGamesUsuario->getFaxDDI()), "s") . ",";
             if(!is_null($objGamesUsuario->getFaxDDD()))         $sql .= " ug_fax_ddd = "             . SQLaddFields(trim($objGamesUsuario->getFaxDDD()), "s") . ",";
             if(!is_null($objGamesUsuario->getFax()))             $sql .= " ug_fax = "                 . SQLaddFields(trim($objGamesUsuario->getFax()), "s") . ",";

             if(!is_null($objGamesUsuario->getRACodigo()))         $sql .= " ug_ra_codigo = "             . SQLaddFields(trim($objGamesUsuario->getRACodigo()), "s") . ",";
             if(!is_null($objGamesUsuario->getRAOutros()))         $sql .= " ug_ra_outros = "             . SQLaddFields(trim($objGamesUsuario->getRAOutros()), "s") . ",";

             if(!is_null($objGamesUsuario->getContato01TelDDI()))$sql .= " ug_contato01_tel_ddi = "     . SQLaddFields(trim($objGamesUsuario->getContato01TelDDI()), "s") . ",";
             if(!is_null($objGamesUsuario->getContato01TelDDD()))$sql .= " ug_contato01_tel_ddd = "     . SQLaddFields(trim($objGamesUsuario->getContato01TelDDD()), "s") . ",";
             if(!is_null($objGamesUsuario->getContato01Tel()))     $sql .= " ug_contato01_tel = "         . SQLaddFields(trim($objGamesUsuario->getContato01Tel()), "s") . ",";
             if(!is_null($objGamesUsuario->getContato01Nome()))     $sql .= " ug_contato01_nome = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getContato01Nome())), "s") . ",";
             if(!is_null($objGamesUsuario->getContato01Cargo())) $sql .= " ug_contato01_cargo = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getContato01Cargo())), "s") . ",";

            if(!is_null($objGamesUsuario->getObservacoes())) {
                if(trim($objGamesUsuario->getObservacoes()) != "") {  
                    $sql_insert_obs = "INSERT INTO dist_usuarios_games_obs VALUES (".$objGamesUsuario->getId().",". SQLaddFields(trim($objGamesUsuario->getObservacoes()), "s") . ",'".$GLOBALS['_SESSION']['userlogin_bko']."');";
                    //echo $sql_insert_obs;
                    $ret_insert_obs = SQLexecuteQuery($sql_insert_obs);
                    if(!$ret_insert_obs) echo "Erro ao atualizar Observação do Usuário.".PHP_EOL;
                }//end if(trim($objGamesUsuario->getObservacoes()) != "")
            } //end if(!is_null($objGamesUsuario->getObservacoes())) 
            if (!is_null($objGamesUsuario->getRiscoClassif())) {
                if (array_key_exists($objGamesUsuario->getRiscoClassif(), $GLOBALS['RISCO_CLASSIFICACAO_NOMES'])) {
                    $sql .= " ug_risco_classif = " . SQLaddFields(trim($objGamesUsuario->getRiscoClassif()), "") . ",";
                }
            }

             if(!is_null($objGamesUsuario->getTipoCadastro()))     $sql .= " ug_tipo_cadastro = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getTipoCadastro())), "s") . ",";
             if(!is_null($objGamesUsuario->getNome()))             $sql .= " ug_nome = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getNome())), "s") . ",";
             if(!is_null($objGamesUsuario->getCPF()))             $sql .= " ug_cpf = "                 . SQLaddFields(trim($objGamesUsuario->getCPF()), "s") . ",";

             if(!is_null($objGamesUsuario->getRG()))             $sql .= " ug_rg = "                 . SQLaddFields(trim($objGamesUsuario->getRG()), "s") . ",";
             if(!is_null($objGamesUsuario->getDataNascimento()) && $objGamesUsuario->getDataNascimento() != "") $sql .= " ug_data_nascimento = "    . SQLaddFields(trim($dataNascimento), "s") . ",";
             if(!is_null($objGamesUsuario->getSexo()))             $sql .= " ug_sexo = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getSexo())), "s") . ",";

             if(!is_null($objGamesUsuario->getPerfilSenhaReimpressao()))     $sql .= " ug_perfil_senha_reimpressao = "         . SQLaddFields(trim($objGamesUsuario->getPerfilSenhaReimpressao()), "s") . ",";
             if(!is_null($objGamesUsuario->getPerfilFormaPagto()))             $sql .= " ug_perfil_forma_pagto = "             . SQLaddFields($objGamesUsuario->getPerfilFormaPagto(), "") . ",";
             if(!is_null($objGamesUsuario->getPerfilLimite()))                 $sql .= " ug_perfil_limite = "                     . SQLaddFields(moeda2numeric($objGamesUsuario->getPerfilLimite()), "") . ",";
             if(!is_null($objGamesUsuario->getPerfilSaldo()))                 $sql .= " ug_perfil_saldo = "                     . SQLaddFields(moeda2numeric($objGamesUsuario->getPerfilSaldo()), "") . ",";

             if(!is_null($objGamesUsuario->getPerfilCorteDiaSemana()))         $sql .= " ug_perfil_corte_dia_semana = "         . SQLaddFields($objGamesUsuario->getPerfilCorteDiaSemana(), "") . ",";
             if(!is_null($objGamesUsuario->getPerfilCorteUltimoCorte()))     $sql .= " ug_perfil_corte_ultimo_corte = "         . SQLaddFields($objGamesUsuario->getPerfilCorteUltimoCorte(), "s") . ",";
             if(!is_null($objGamesUsuario->getPerfilLimiteSugerido()))         $sql .= " ug_perfil_limite_sugerido = "         . SQLaddFields(moeda2numeric($objGamesUsuario->getPerfilLimiteSugerido()), "") . ",";
             if(!is_null($objGamesUsuario->getCreditoPendente()))             $sql .= " ug_credito_pendente = "                 . SQLaddFields(moeda2numeric($objGamesUsuario->getCreditoPendente()), "") . ",";

             if(!is_null($objGamesUsuario->getInscrEstadual()))                 $sql .= " ug_inscr_estadual = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getInscrEstadual())), "s") . ",";    
             if(!is_null($objGamesUsuario->getSite()))                         $sql .= " ug_site = "                             . SQLaddFields(strtoupper($objGamesUsuario->getSite()), "s") . ",";            
             if(!is_null($objGamesUsuario->getAberturaAno()))                 $sql .= " ug_abertura_ano = "                     . SQLaddFields($objGamesUsuario->getAberturaAno(), "") . ",";        
             if(!is_null($objGamesUsuario->getAberturaMes()))                 $sql .= " ug_abertura_mes = "                     . SQLaddFields($objGamesUsuario->getAberturaMes(), "") . ",";        
             if(!is_null($objGamesUsuario->getCartoes()))                     $sql .= " ug_cartoes = "                         . SQLaddFields(trim(strtoupper($objGamesUsuario->getCartoes())), "s") . ",";        
             if(!is_null($objGamesUsuario->getFaturaMediaMensal()))             $sql .= " ug_fatura_media_mensal = "             . SQLaddFields($objGamesUsuario->getFaturaMediaMensal(), "") . ",";

             if(!is_null($objGamesUsuario->getReprLegalNome()))                 $sql .= " ug_repr_legal_nome = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalNome())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalRG()))                 $sql .= " ug_repr_legal_rg = "                     . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalRG())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprLegalCPF()))                 $sql .= " ug_repr_legal_cpf = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCPF())), "s") . ",";
             if(!is_null($objGamesUsuario->getReprLegalDataNascimento()) && $objGamesUsuario->getReprLegalDataNascimento() != "")      $sql .= " ug_repr_legal_data_nascimento = "     . SQLaddFields(trim(strtoupper($dataNascimentoRepr)), "s") . ",";
             if(!is_null($objGamesUsuario->getReprLegalTelDDI()))             $sql .= " ug_repr_legal_tel_ddi = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalTelDDI())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalTelDDD()))             $sql .= " ug_repr_legal_tel_ddd = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalTelDDD())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalTel()))                 $sql .= " ug_repr_legal_tel = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalTel())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprLegalCelDDI()))             $sql .= " ug_repr_legal_cel_ddi = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCelDDI())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalCelDDD()))             $sql .= " ug_repr_legal_cel_ddd = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCelDDD())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalCel()))                 $sql .= " ug_repr_legal_cel = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalCel())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprLegalEmail()))             $sql .= " ug_repr_legal_email = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalEmail())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprLegalMSN()))                 $sql .= " ug_repr_legal_msn = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprLegalMSN())), "s") . ",";        

             if(!is_null($objGamesUsuario->getReprVendaIgualReprLegal()))    $sql .= " ug_repr_venda_igual_repr_legal = "     . SQLaddFields($objGamesUsuario->getReprVendaIgualReprLegal(), "") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaNome()))                 $sql .= " ug_repr_venda_nome = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaNome())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaRG()))                 $sql .= " ug_repr_venda_rg = "                     . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaRG())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprVendaCPF()))                 $sql .= " ug_repr_venda_cpf = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCPF())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprVendaTelDDI()))             $sql .= " ug_repr_venda_tel_ddi = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaTelDDI())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaTelDDD()))             $sql .= " ug_repr_venda_tel_ddd = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaTelDDD())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaTel()))                 $sql .= " ug_repr_venda_tel = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaTel())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprVendaCelDDI()))             $sql .= " ug_repr_venda_cel_ddi = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCelDDI())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaCelDDD()))             $sql .= " ug_repr_venda_cel_ddd = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCelDDD())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaCel()))                 $sql .= " ug_repr_venda_cel = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaCel())), "s") . ",";        
             if(!is_null($objGamesUsuario->getReprVendaEmail()))             $sql .= " ug_repr_venda_email = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaEmail())), "s") . ",";    
             if(!is_null($objGamesUsuario->getReprVendaMSN()))                 $sql .= " ug_repr_venda_msn = "                 . SQLaddFields(trim(strtoupper($objGamesUsuario->getReprVendaMSN())), "s") . ",";        

             if(!is_null($objGamesUsuario->getDadosBancarios01Banco()))         $sql .= " ug_dados_bancarios_01_banco = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Banco())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios01Agencia()))     $sql .= " ug_dados_bancarios_01_agencia = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Agencia())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios01Conta()))         $sql .= " ug_dados_bancarios_01_conta = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Conta())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios01Abertura()))     $sql .= " ug_dados_bancarios_01_abertura = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios01Abertura())), "s") . ",";    

             if(!is_null($objGamesUsuario->getDadosBancarios02Banco()))         $sql .= " ug_dados_bancarios_02_banco = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Banco())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios02Agencia()))     $sql .= " ug_dados_bancarios_02_agencia = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Agencia())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios02Conta()))         $sql .= " ug_dados_bancarios_02_conta = "         . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Conta())), "s") . ",";    
             if(!is_null($objGamesUsuario->getDadosBancarios02Abertura()))     $sql .= " ug_dados_bancarios_02_abertura = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getDadosBancarios02Abertura())), "s") . ",";    

             if(!is_null($objGamesUsuario->getComputadoresQtde()))             $sql .= " ug_computadores_qtde = "                 . SQLaddFields($objGamesUsuario->getComputadoresQtde(), "") . ",";        
             if(!is_null($objGamesUsuario->getComunicacaoVisual()))             $sql .= " ug_comunicacao_visual = "             . SQLaddFields(trim(strtoupper($objGamesUsuario->getComunicacaoVisual())), "s") . ",";        

             if(!is_null($objGamesUsuario->getPerfilLimiteRef()))                 $sql .= " ug_perfil_limite_ref = "                     . SQLaddFields(moeda2numeric($objGamesUsuario->getPerfilLimiteRef()), "") . ",";

             if(!is_null($objGamesUsuario->getFicouSabendo()))     $sql .= " ug_ficou_sabendo = "     . SQLaddFields(trim(strtoupper($objGamesUsuario->getFicouSabendo())), "s") . ",";    

            if(!is_null($objGamesUsuario->getUgOngame()))    $sql .= " ug_ongame = ". SQLaddFields(trim(strtoupper($objGamesUsuario->getUgOngame())), "s") . ",";    

            if(!is_null($objGamesUsuario->getTipoEstabelecimento()))    $sql .= " ug_te_id = ". SQLaddFields(trim(strtoupper($objGamesUsuario->getTipoEstabelecimentoParaBanco())), "") . ",";    

            if(!is_null($objGamesUsuario->getUgIdNexCafe())) $sql .= " ug_id_nexcafe = ". SQLaddFields(trim(strtoupper($objGamesUsuario->getUgIdNexCafe())), "s") . ",";
            if(!is_null($objGamesUsuario->getUgLoginNexCafeAuto())) $sql .= " ug_login_nexcafe_auto = ". SQLaddFields(trim(strtoupper($objGamesUsuario->getUgLoginNexCafeAuto())), "") . ",";

            if(!is_null($objGamesUsuario->getAlteraSenha())) $sql .= " ug_alterar_senha = ". intval(SQLaddFields($objGamesUsuario->getAlteraSenha(), "")*1) . ",";
            if(!is_null($objGamesUsuario->getExibirContrato())) $sql .= " ug_exibir_contrato = ". intval(SQLaddFields($objGamesUsuario->getExibirContrato(), "")*1) . ",";
            if(!is_null($objGamesUsuario->getDataAceite())) $sql .= " ug_data_aceite_adesao = ". SQLaddFields((($objGamesUsuario->getDataAceite())?"CURRENT_TIMESTAMP":"NULL"), "") . ",";

            if(!is_null($objGamesUsuario->getRecargaCelular())) $sql .= " ug_recarga_celular = ". intval(SQLaddFields($objGamesUsuario->getRecargaCelular(), "")*1) . ",";

            if(!is_null($objGamesUsuario->getVIP())) $sql .= " ug_vip = ". intval(SQLaddFields($objGamesUsuario->getVIP(), "")*1) . ",";

            if(!is_null($objGamesUsuario->getPossuiRestricaoProdutos())) $sql .= " ug_possui_restricao_produtos = ". intval(SQLaddFields($objGamesUsuario->getPossuiRestricaoProdutos(), "")*1) . ",";

            if(!is_null($objGamesUsuario->getTipoVenda())) $sql .= " ug_tipo_venda = ". SQLaddFields($objGamesUsuario->getTipoVenda(), "s") . ",";

			if(!is_null($objGamesUsuario->getCanaisVenda())) $sql .= " ug_canais_venda = ". SQLaddFields(trim($objGamesUsuario->getCanaisVenda()), "s") . ",";

            if(!is_null($objGamesUsuario->getDataExpiraSenha())) $sql .= " ug_data_expiracao_senha = ". SQLaddFields($dataExpiraSenha, "s") . ",";
            if($objGamesUsuario->getDataAprovacao() == "" && $objGamesUsuario->getSubstatus() == 11) $sql .= " ug_data_aprovacao = NOW(),";
            
            if(substr($sql, -1) == ",") $sql = substr($sql, 0, strlen($sql) - 1);

            $sql .= " where ug_id = " . SQLaddFields($objGamesUsuario->getId(), "");

            switch ($objGamesUsuario->getSubstatus()) {
                    case 2:
                        // Caso a lan que deve retornar contato 
                        //$envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'RetornarContato');
                       // $envioEmail->setUgID($objGamesUsuario->getId());
                        //$envioEmail->MontaEmailEspecifico();
                        break;
                    case 3:
                        // Caso a lan dados insuficientes para a aprovação do cadastro
                        //$envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'DadosInsuficientes');
                       // $envioEmail->setUgID($objGamesUsuario->getId());
                       // $envioEmail->MontaEmailEspecifico();
                        break;
                    case 4:
                        // Caso a lan não seja aprovada, enviar e-mail.
                        $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'PedidoNegadoLan');
                        $envioEmail->setUgID($objGamesUsuario->getId());
                        $envioEmail->MontaEmailEspecifico();
                        break;
                    case 9:
                        // Lan house aprovada, mas ainda não fez a primeira compra
                        if($objGamesUsuario->getDataAprovacao() == "") {
                            $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'LanAprovada');
                            $envioEmail->setUgID($objGamesUsuario->getId());
                            $envioEmail->MontaEmailEspecifico();
                        }//end if($objGamesUsuario->getDataAprovacao() == "")
                        break;
                    case 11:
                        // Lan house aprovada
                        if($objGamesUsuario->getDataAprovacao() == "") {
                            $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'LanAprovada');
                            $envioEmail->setUgID($objGamesUsuario->getId());
                            $envioEmail->MontaEmailEspecifico();
                        }//end if($objGamesUsuario->getDataAprovacao() == "")
                        break;
            } 
            $ret = SQLexecuteQuery($sql);

            if(!$ret) $ret_all .= "Erro ao atualizar usuário.".PHP_EOL;
            else {
                $ret = "";
                if(($objGamesUsuario->getEmail() != $rsUltimoEmail["ug_email"]) && !empty($objGamesUsuario->getEmail())){
					
					$sqlEmail = "insert into log_modificacao_email(email_anterior,email_novo,data_inclusao,usuario_bo,pdv)values('".$rsUltimoEmail["ug_email"]."','".$objGamesUsuario->getEmail()."',CURRENT_TIMESTAMP,'".$_SESSION["userlogin_bko"]."',".$objGamesUsuario->getId().");";
					$ret = SQLexecuteQuery($sqlEmail);
						
				}
            }

        }

        return $ret_all;
    }
    function validarCamposLogin($senha, $senhaConf, $login) {

        $ret = "";

        $senha = trim($senha);
        $senhaConf = trim($senhaConf);
        $login = trim($login);

        //Senha
        if(is_null($senha) || $senha == "")                 $ret .= "A Senha deve ser preenchida.".PHP_EOL;
        elseif(strlen($senha) < 6 || strlen($senha) > 15)     $ret .= "A Senha deve ter entre 6 e 15 caracteres.".PHP_EOL;

        //SenhaConf         
        if($senha != $senhaConf)                             $ret .= "A confirmação da senha deve ser igual a senha.";

        //login
         if(is_null($login) || $login == "")                 $ret .= "O Login deve ser preenchido.".PHP_EOL;
        elseif(strlen($login) < 6 || strlen($login) > 100)    $ret .= "O Login deve ter entre 6 e 100 caracteres.".PHP_EOL;


        return $ret;
    }

    function validarCampos($objGamesUsuario, $blCompleto, $blEditaCadastro = true) {

        $ret = "";

        //Dados do login
        if ($blCompleto) {
            $ret .= UsuarioGames::validarCamposLogin($objGamesUsuario->getSenha(), $objGamesUsuario->getSenha(), $objGamesUsuario->getLogin());
        }

        //Email
        $email = $objGamesUsuario->getEmail();
        //echo $email;
        if (!is_null($email) || $blCompleto) {
            $email = trim($objGamesUsuario->getEmail());
            if(is_null($email) || $email == "") $ret .= "O Email deve ser preenchido.".PHP_EOL;
            elseif(strlen($email) > 100)         $ret .= "O Email deve ter até 100 caracteres.".PHP_EOL;
            elseif(!verifica_email2($email))     $ret .= "O Email é inválido.".PHP_EOL;
        }
        
        //Tipo Cadastro
        $tipoCadastro = $objGamesUsuario->getTipoCadastro();
        if (!is_null($tipoCadastro) || $blCompleto) {
            $tipoCadastro = trim($objGamesUsuario->getTipoCadastro());
            if(is_null($tipoCadastro) || $tipoCadastro == "")     $ret .= "O Tipo de Cadastro deve ser selecionado.".PHP_EOL;
            elseif($tipoCadastro != 'PJ' && $tipoCadastro != 'PF')     $ret .= "Tipo de Cadastro inválido.".PHP_EOL;
        }

        //NomeFantasia
        $nomeFantasia = $objGamesUsuario->getNomeFantasia();
        if (!is_null($nomeFantasia) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $nome = trim($objGamesUsuario->getNomeFantasia());
            //if($tipoCadastro == 'PJ' && (is_null($nomeFantasia) || $nomeFantasia == ""))     $ret .= "O Nome Fantasia deve ser preenchido.".PHP_EOL;
			//elseif
            if(strlen($nomeFantasia) > 100)                 $ret .= "O Nome Fantasia deve ter até 100 caracteres.".PHP_EOL;
        }

        //Responsavel
        $responsavel = $objGamesUsuario->getResponsavel();
        if (!is_null($responsavel) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $nome = trim($objGamesUsuario->getResponsavel());
            //if($tipoCadastro == 'PJ' && (is_null($responsavel) || $responsavel == ""))     $ret .= "O Responsável deve ser preenchido.".PHP_EOL;
			//elseif
            if(strlen($responsavel) > 100)                     $ret .= "O Responsável deve ter até 100 caracteres.".PHP_EOL;
        }

        //Tipo de Estabelecimento
        $tipo_estabelecimento = $objGamesUsuario->getTipoEstabelecimento();
        if ($blCompleto && $tipoCadastro == 'PJ') {
            if ($blCompleto && (is_null($tipo_estabelecimento) || ($tipo_estabelecimento == ''))) {
                $ret .= "O Tipo de Estabelecimento não foi selecionado.".PHP_EOL;
            }
            elseif ($tipo_estabelecimento=="Outros"){
                // colocar uma validação do campo outros que esta na SESSION
                $aux_cad_tipo_estabelecimento = $GLOBALS['_SESSION']['dist_cadin_outrosUgTEID'];
                if (!isset($aux_cad_tipo_estabelecimento) || ($aux_cad_tipo_estabelecimento == "")) {
                    $ret .= "O Tipo de Estabelecimento 'Outros' não foi fornecido.".PHP_EOL;
                }
            }
        }

        //FaturaMediaMensal
        $FaturaMediaMensal = $objGamesUsuario->getFaturaMediaMensal();
        if (!is_null($FaturaMediaMensal) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $FaturaMediaMensal = trim($objGamesUsuario->getFaturaMediaMensal());
            if($tipoCadastro == 'PJ' && (is_null($FaturaMediaMensal) || $FaturaMediaMensal == ""))     $ret .= "Pelo menos um Faturamento Médio Mensal deve ser selecionado.".PHP_EOL;
            else if(!is_numeric($FaturaMediaMensal))                     $ret .= "O Faturamento Médio Mensal deve ser númerico.".PHP_EOL;
        }

        //getComputadoresQtde
        $ComputadoresQtde = $objGamesUsuario->getComputadoresQtde();
        if (!is_null($ComputadoresQtde) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ComputadoresQtde = trim($objGamesUsuario->getComputadoresQtde());
            if($tipoCadastro == 'PJ' && (is_null($ComputadoresQtde) || $ComputadoresQtde == ""))     $ret .= "Pelo menos um item de Quantos Computadores deve ser selecionado.".PHP_EOL;
            else if(!is_numeric($ComputadoresQtde)) $ret .= "A Quantidade de Computadores deve ser númerico.".PHP_EOL;
        }

        //ComunicacaoVisual
//        $ComunicacaoVisual = $objGamesUsuario->getComunicacaoVisual();
//        if (!is_null($ComunicacaoVisual) || ($blCompleto && $tipoCadastro == 'PJ')) {
//            $ComunicacaoVisual = trim($objGamesUsuario->getComunicacaoVisual());
//            if($tipoCadastro == 'PJ' && (is_null($ComunicacaoVisual) || $ComunicacaoVisual == ""))     $ret .= "Pelo menos uma Comunicação Visual deve ser selecionado.".PHP_EOL;
//            elseif(strlen($ComunicacaoVisual) > 100)     $ret .= "A Comunicação Visual não pode passar de 100 caracteres.".PHP_EOL;
//        }

//        //ReprLegalMSN
//        $ReprLegalMSN = $objGamesUsuario->getReprLegalMSN();
//        if (!(is_null($ReprLegalMSN) || ($ReprLegalMSN == '')) || ($blCompleto && $tipoCadastro == 'PJ')) {
//            $ReprLegalMSN = trim($objGamesUsuario->getReprLegalMSN());
//            if($tipoCadastro == 'PJ' && (is_null($ReprLegalMSN) || $ReprLegalMSN == "")) $ret .= "";
//            elseif(strlen($ReprLegalMSN) > 100)                 $ret .= "O MSN do Representante Legal da Empresa deve ter até 100 caracteres.".PHP_EOL;
//            elseif(!verifica_email2($ReprLegalMSN))                 $ret .= "O MSN do Representante Legal da Empresa é inválido.".PHP_EOL;
//        }

        //CEP
        $CEP = $objGamesUsuario->getCEP();
        if (!is_null($CEP) || $blCompleto) {
            $CEP = trim($objGamesUsuario->getCEP());

            if(is_null($CEP) || $CEP == "")    $ret .= "O CEP deve ser preenchido.".PHP_EOL;
            elseif(strlen($CEP) <> 8)         $ret .= "O CEP deve ser no formato 00000000. Sem traço.".PHP_EOL;
            elseif(!verifica_cepEx2($CEP, false))         $ret .= "O CEP é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
        }

        //Tipo
        $tipoEnd = $objGamesUsuario->getTipoEnd();
        if (!is_null($tipoEnd) || $blCompleto) {
            $tipoEnd = trim($objGamesUsuario->getTipoEnd());
        }

        //Endereco
        $endereco = $objGamesUsuario->getEndereco();
        if (!is_null($endereco) || $blCompleto) {
            $endereco = trim($objGamesUsuario->getEndereco());
            //if(is_null($endereco) || $endereco == "")     $ret .= "O Endereço deve ser preenchido.".PHP_EOL;
			//elseif
            if(strlen($endereco) > 100)             $ret .= "O Endereço deve ter até 100 caracteres.".PHP_EOL;
        }

        //Numero
        $numero = $objGamesUsuario->getNumero();
        if (!is_null($numero) || $blCompleto) {
            $numero = trim($objGamesUsuario->getNumero());
            //if(is_null($numero) || $numero == "")     $ret .= "O Número deve ser preenchido.".PHP_EOL;
			//elseif
            if(strlen($numero) > 10)             $ret .= "O Número deve ter até 10 caracteres.".PHP_EOL;
        }

        //Complemento
        $complemento = $objGamesUsuario->getComplemento();
        if (!is_null($complemento) || $blCompleto) {
            $complemento = trim($objGamesUsuario->getComplemento());
            if(strlen($complemento) > 100)                 $ret .= "O Complemento deve ter até 100 caracteres.".PHP_EOL;
        }

        //Bairro
        $bairro = $objGamesUsuario->getBairro();
        if (!is_null($bairro) || $blCompleto) {
            $bairro = trim($objGamesUsuario->getBairro());
            //if(is_null($bairro) || $bairro == "")     $ret .= "O Bairro deve ser preenchido.".PHP_EOL;
			//elseif
            if(strlen($bairro) > 100)             $ret .= "O Bairro deve ter até 100 caracteres.".PHP_EOL;
        }

        //Cidade
        $cidade = $objGamesUsuario->getCidade();
        if (!is_null($cidade) || $blCompleto) {
            $cidade = trim($objGamesUsuario->getCidade());
            //if(is_null($cidade) || $cidade == "")     $ret .= "O Cidade deve ser preenchido.".PHP_EOL;
			//elseif
            if(strlen($cidade) > 100)             $ret .= "O Cidade deve ter até 100 caracteres.".PHP_EOL;
        }

        //Estado
        $estado = $objGamesUsuario->getEstado();
        if (!is_null($estado) || $blCompleto) {
            $estado = trim($objGamesUsuario->getEstado());
            if(is_null($estado) || $estado == "")     $ret .= "O Estado deve ser preenchido.".PHP_EOL;
            elseif(strlen($estado) <> 2)             $ret .= "O Estado deve ter 2 caracteres.".PHP_EOL;
        }

        //Tel DDI
        $TelDDI = $objGamesUsuario->getTelDDI();
        if (!is_null($TelDDI) || $blCompleto) {
            $TelDDI = trim($objGamesUsuario->getTelDDI());
            if(is_null($TelDDI) || $TelDDI == "")    $ret .= "O Código do País do Telefone deve ser preenchido.".PHP_EOL;
            elseif(strlen($TelDDI) <> 2)             $ret .= "O Código do País do Telefone deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($TelDDI))             $ret .= "O Código do País do Telefone deve ser númerico.".PHP_EOL;
        }

        //Tel DDD
        $TelDDD = $objGamesUsuario->getTelDDD();
        if (!is_null($TelDDD) || $blCompleto) {
            $TelDDD = trim($objGamesUsuario->getTelDDD());
            //if(is_null($TelDDD) || $TelDDD == "")    $ret .= "O DDD do Telefone deve ser preenchido.".PHP_EOL;
            //elseif(strlen($TelDDD) <> 2)             $ret .= "O DDD do Telefone deve ter 2 dígitos.".PHP_EOL;
            //elseif(!is_numeric($TelDDD))             $ret .= "O DDD do Telefone deve ser númerico.".PHP_EOL;
            //elseif($TelDDD <= 10 || ($TelDDD % 10 == 0)) $ret .= "O DDD do Telefone é inválido.".PHP_EOL;
        }

        //Tel 
        $Tel = $objGamesUsuario->getTel();
        if (!is_null($Tel) || $blCompleto) {
            $Tel = trim($objGamesUsuario->getTel());
            //if(is_null($Tel) || $Tel == "")            $ret .= "O Telefone deve ser preenchido.".PHP_EOL;
			//elseif(verifica_telEx2($Tel, false) == 0)$ret .= "O Telefone é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
        }

        //Cel DDI
        $CelDDI = $objGamesUsuario->getCelDDI();
        if (!is_null($CelDDI) || $blCompleto) {
            $CelDDI = trim($objGamesUsuario->getCelDDI());
            if(is_null($CelDDI) || $CelDDI == "")    $ret .= "";
            elseif(strlen($CelDDI) <> 2)             $ret .= "O Código do País do Celular deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($CelDDI))             $ret .= "O Código do País do Celular deve ser númerico.".PHP_EOL;
        }

        //Cel DDD
        $CelDDD = $objGamesUsuario->getCelDDD();
        if (!is_null($CelDDD) || $blCompleto) {
            $CelDDD = trim($objGamesUsuario->getCelDDD());
            if(is_null($CelDDD) || $CelDDD == "")    $ret .= "";
            elseif(strlen($CelDDD) <> 2)             $ret .= "O DDD do Celular deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($CelDDD))             $ret .= "O DDD do Celular deve ser númerico.".PHP_EOL;
            elseif($CelDDD <= 10 || ($CelDDD % 10 == 0)) $ret .= "O DDD do Celular é inválido.".PHP_EOL;
        }

        //Cel 
        $Cel = $objGamesUsuario->getCel();
        if (!is_null($Cel) || $blCompleto) {
            $Cel = trim($objGamesUsuario->getCel());
            if(is_null($Cel) || $Cel == "")            $ret .= "";
            elseif(verifica_telEx2($Cel, false) == 0)$ret .= "O Celular é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
        }

        //Fax DDI
        $FaxDDI = $objGamesUsuario->getFaxDDI();
        if (!is_null($FaxDDI) || $blCompleto) {
            $FaxDDI = trim($objGamesUsuario->getFaxDDI());
            if(is_null($FaxDDI) || $FaxDDI == "")    $ret .= "";
            elseif(strlen($FaxDDI) <> 2)             $ret .= "O Código do País do Fax deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($FaxDDI))             $ret .= "O Código do País do Fax deve ser númerico.".PHP_EOL;
        }

        //Fax DDD
        $FaxDDD = $objGamesUsuario->getFaxDDD();
        if (!is_null($FaxDDD) || $blCompleto) {
            $FaxDDD = trim($objGamesUsuario->getFaxDDD());
            if(is_null($FaxDDD) || $FaxDDD == "")    $ret .= "";
            elseif(strlen($FaxDDD) <> 2)             $ret .= "O DDD do Fax deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($FaxDDD))             $ret .= "O DDD do Fax deve ser númerico.".PHP_EOL;
            elseif($FaxDDD <= 10 || ($FaxDDD % 10 == 0)) $ret .= "O DDD do Fax é inválido.".PHP_EOL;
        }

        //Fax 
        $Fax = $objGamesUsuario->getFax();
        if (!is_null($Fax) || $blCompleto) {
            $Fax = trim($objGamesUsuario->getFax());
            if(is_null($Fax) || $Fax == "")            $ret .= "";
            elseif(verifica_telEx2($Fax, false) == 0)$ret .= "O Fax é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
        }

        //Observacoes
        $Observacoes = $objGamesUsuario->getObservacoes();
        if (!is_null($Observacoes) || $blCompleto) {
            $Observacoes = trim($objGamesUsuario->getObservacoes());
            if(is_null($Observacoes) || $Observacoes == "")     $ret .= "";
            elseif(strlen($Observacoes) > 2048)                     $ret .= "Observações deve ter até 2000 caracteres.".PHP_EOL;
        }

        //hablitando a validação dos demais campos
        if ($blEditaCadastro) {

            //login
            $login = $objGamesUsuario->getLogin();
            if (!is_null($login) || $blCompleto) {
                $login = trim($objGamesUsuario->getLogin());
                if(is_null($login) || $login == "")                 $ret .= "O Login deve ser preenchido.".PHP_EOL;
                elseif(strlen($login) < 6 || strlen($login) > 100)     $ret .= "O Login deve ter entre 6 e 100 caracteres.".PHP_EOL;
                elseif(strpos($login, "@")!==false)                    $ret .= "O login é inválido (não pode conter '@').".PHP_EOL;
            }
            //echo "<!-- L Na classe [".$login."] -->".PHP_EOL;
            
            //RiscoClassif
            $iRiscoClassif = $objGamesUsuario->getRiscoClassif();
            if (!is_null($iRiscoClassif) || $blCompleto) {
                $iRiscoClassif = trim($objGamesUsuario->getRiscoClassif());
                if(is_null($iRiscoClassif) || $iRiscoClassif == "")     $ret .= "";
            }

            //RazaoSocial
            $razaoSocial = $objGamesUsuario->getRazaoSocial();
            if (!is_null($razaoSocial) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $razaoSocial = trim($objGamesUsuario->getRazaoSocial());
                if($tipoCadastro == 'PJ' && (is_null($razaoSocial) || $razaoSocial == ""))     $ret .= "A Razão Social deve ser preenchida.".PHP_EOL;
                elseif(strlen($razaoSocial) > 100)                     $ret .= "A Razão Social deve ter até 100 caracteres.".PHP_EOL;
            }

            //CNPJ
            $CNPJ = $objGamesUsuario->getCNPJ();
            if (!is_null($CNPJ) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $CNPJ = trim($objGamesUsuario->getCNPJ());
                
                if ($tipoCadastro == 'PJ') {
                    if(is_null($CNPJ) || $CNPJ == "")     $ret .= "O CNPJ deve ser preenchido.".PHP_EOL;
                    elseif(verificaCNPJ($CNPJ) == 0)     $ret .= "O CNPJ inválido. Utilize somente números sem pontos, barra e traço (1)".PHP_EOL; //[".$GLOBALS['_SESSION']['bdebug']."]
                } else {
                    if($CNPJ != "" && verificaCNPJ($CNPJ) == 0)     $ret .= "O CNPJ inválido. Utilize somente números sem pontos, barra e traço (2)".PHP_EOL;
                }
            }

            //RACodigo e RAOutros
            $RACodigo = $objGamesUsuario->getRACodigo();
            $RAOutros = $objGamesUsuario->getRAOutros();
            if (!is_null($RACodigo) || !is_null($RAOutros) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $RACodigo = trim($objGamesUsuario->getRACodigo());
                $RAOutros = trim($objGamesUsuario->getRAOutros());
                if($tipoCadastro == 'PJ' && ((is_null($RACodigo) || $RACodigo == "") && (is_null($RAOutros) || $RAOutros == ""))) $ret .= "";
                elseif((!is_null($RACodigo) && $RACodigo != "") && (!is_null($RAOutros) && $RAOutros != ""))$ret .= "No Ramo de Atividade, preencher \"Outros\" somente se nenhum ramo for selecionado.".PHP_EOL;
                else {
                    if (!is_null($RACodigo) && $RACodigo != "") {
                        if(strlen($RACodigo) > 8) $ret .= "O Ramo de Atividade é inválido.".PHP_EOL;
                    }
                    if (!is_null($RAOutros) && $RAOutros != "") {
                        if(strlen($RAOutros) > 60) $ret .= "O Ramo de Atividade (Outros) deve ter até 60 caracteres.".PHP_EOL;
                    }
                }
            }


            //Contato01 Tel DDI
            $Contato01TelDDI = $objGamesUsuario->getContato01TelDDI();
            if (!is_null($Contato01TelDDI) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $Contato01TelDDI = trim($objGamesUsuario->getContato01TelDDI());
                if ($tipoCadastro == 'PJ') {
                    if(is_null($Contato01TelDDI) || $Contato01TelDDI == "")    $ret .= "";
                    elseif(strlen($Contato01TelDDI) <> 2)                     $ret .= "O Código do País do Telefone do Contato Técnico deve ter 2 dígitos.".PHP_EOL;
                    elseif(!is_numeric($Contato01TelDDI))                     $ret .= "O Código do País do Telefone do Contato Técnico deve ser númerico.".PHP_EOL;
                } else {
                    if(strlen($Contato01TelDDI) > 2)                                 $ret .= "O Código do País do Telefone do Contato Técnico deve ter até 2 dígitos.".PHP_EOL;
                    elseif($Contato01TelDDI != "" && !is_numeric($Contato01TelDDI)) $ret .= "O Código do País do Telefone do Contato Técnico deve ser númerico.".PHP_EOL;
                }
            }

            //Contato01 Tel DDD
            $Contato01TelDDD = $objGamesUsuario->getContato01TelDDD();
            if (!is_null($Contato01TelDDD) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $Contato01TelDDD = trim($objGamesUsuario->getContato01TelDDD());

                if ($tipoCadastro == 'PJ') {
                    if(is_null($Contato01TelDDD) || $Contato01TelDDD == "")    $ret .= "";
                    elseif(strlen($Contato01TelDDD) <> 2)                     $ret .= "O DDD do Telefone do Contato Técnico deve ter 2 dígitos.".PHP_EOL;
                    elseif(!is_numeric($Contato01TelDDD))                     $ret .= "O DDD do Telefone do Contato Técnico deve ser númerico.".PHP_EOL;
                } else {
                    if(strlen($Contato01TelDDD) > 2)                                 $ret .= "O DDD do Telefone do Contato Técnico deve ter até 2 dígitos.".PHP_EOL;
                    elseif($Contato01TelDDD != "" && !is_numeric($Contato01TelDDD)) $ret .= "O DDD do Telefone do Contato Técnico deve ser númerico.".PHP_EOL;
                    elseif($Contato01TelDDD != "" && ($Contato01TelDDD <= 10 || ($Contato01TelDDD % 10 == 0))) $ret .= "O DDD do Telefone do Contato Técnico é inválido.".PHP_EOL;
                }
            }

            //Contato01 Tel 
            $Contato01Tel = $objGamesUsuario->getContato01Tel();
            if (!is_null($Contato01Tel) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $Contato01Tel = trim($objGamesUsuario->getContato01Tel());

                if ($tipoCadastro == 'PJ') {
                    if(is_null($Contato01Tel) || $Contato01Tel == "")    $ret .= "";
                    elseif(verifica_telEx2($Contato01Tel, false) == 0)    $ret .= "O Telefone do Contato Técnico é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
                } else {

                    if($Contato01Tel != "" && verifica_telEx2($Contato01Tel, false) == 0) $ret .= "O Telefone do Contato Técnico é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
                    
                }
            }

            //Contato01Nome
            $Contato01Nome = $objGamesUsuario->getContato01Nome();
            if (!is_null($Contato01Nome) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $Contato01Nome = trim($objGamesUsuario->getContato01Nome());
                if($tipoCadastro == 'PJ' && (is_null($Contato01Nome) || $Contato01Nome == "")) $ret .= "O Nome deve ser preenchido.".PHP_EOL;
            }


            //Contato01Cargo
            $Contato01Cargo = $objGamesUsuario->getContato01Cargo();
            if (!is_null($Contato01Cargo) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $Contato01Cargo = trim($objGamesUsuario->getContato01Cargo());
                if($tipoCadastro == 'PJ' && (is_null($Contato01Cargo) || $Contato01Cargo == ""))     $ret .= "";
                elseif(strlen($Contato01Cargo) > 20)                     $ret .= "O Cargo do Contato Técnico deve ter até 20 caracteres.".PHP_EOL;
            }


            //Nome
            $nome = $objGamesUsuario->getNome();
            if (!is_null($nome) || ($blCompleto && $tipoCadastro == 'PF')) {
                $nome = trim($objGamesUsuario->getNome());
                if($tipoCadastro == 'PF' && (is_null($nome) || $nome == ""))     $ret .= "O Nome deve ser preenchido.".PHP_EOL;
                elseif(strlen($nome) > 100)         $ret .= "O Nome deve ter até 100 caracteres.".PHP_EOL;
            }

            //CPF e RG
            $CPF = $objGamesUsuario->getCPF();
            $RG = $objGamesUsuario->getRG();
            if (!is_null($CPF) || !is_null($RG) || ($blCompleto && $tipoCadastro == 'PF')) {
                if($tipoCadastro == 'PF' && ((is_null($CPF) || $CPF == "") && (is_null($RG) || $RG == ""))) $ret .= "O CPF ou RG deve ser preenchido.".PHP_EOL;
                else {
                    if (!is_null($CPF) && $CPF != "") {
                        //if(verificaCPFEx($CPF) == 0) $ret .= "O CPF é inválido. Utilize somente números sem pontos, barra e traço".PHP_EOL;
                    }
                    if (!is_null($RG) && $RG != "") {
                        //                    if(!eregi("^[0-9,A-Z]{7,13}$", $RG)) $ret .= "O RG é inválido. Utilize somente números e letras, sem pontos, barra e traço".PHP_EOL;
                    }
                }
            }

            //Data Nascimento
            $dataNascimento = $objGamesUsuario->getDataNascimento();
            if (!is_null($dataNascimento) || ($blCompleto && $tipoCadastro == 'PF')) {
                $dataNascimento = trim($objGamesUsuario->getDataNascimento());
                if($tipoCadastro == 'PF' && (is_null($dataNascimento) || $dataNascimento == ""))     $ret .= "A Data de Nascimento deve ser preenchida.".PHP_EOL;
                elseif(verifica_data2($dataNascimento) == 0)                $ret .= "A Data de Nascimento é inválida.".PHP_EOL;
            }

            //Sexo
            $sexo = $objGamesUsuario->getSexo();
            if (!is_null($sexo) || ($blCompleto && $tipoCadastro == 'PF')) {
                $sexo = trim($objGamesUsuario->getSexo());
                if ($tipoCadastro == 'PF') {
                    if(is_null($sexo) || $sexo == "") $ret .= "O Sexo deve ser preenchida.".PHP_EOL;
                    elseif(strtoupper($sexo) != "M" && strtoupper($sexo) != "F")$ret .= "O Sexo é inválido.".PHP_EOL;
                }
            }


            //PerfilSenhaReimpressao
            $perfilSenhaReimpressao = $objGamesUsuario->getPerfilSenhaReimpressao();
            if (!is_null($perfilSenhaReimpressao)) {
                $perfilSenhaReimpressao = trim($objGamesUsuario->getPerfilSenhaReimpressao());
                if(strlen($perfilSenhaReimpressao) > 50)                     $ret .= "A Senha de Reimpressão deve ter até 50 caracteres.".PHP_EOL;
            }

            //PerfilFormaPagto
            $perfilFormaPagto = $objGamesUsuario->getPerfilFormaPagto();
            if (!is_null($perfilFormaPagto)) {
                $perfilFormaPagto = trim($objGamesUsuario->getPerfilFormaPagto());
                if(is_null($perfilFormaPagto) || $perfilFormaPagto == "")     $ret .= "A Forma de Pagamento deve ser selecionada.".PHP_EOL;
                else if(!is_numeric($perfilFormaPagto))                     $ret .= "A Forma de Pagamento deve ser númerico.".PHP_EOL;
            }

            //PerfilLimite
            $perfilLimite = $objGamesUsuario->getPerfilLimite();
            if (!is_null($perfilLimite)) {
                if(is_null($perfilLimite))             $ret .= "O Limite deve ser preenchido.".PHP_EOL;
                elseif(!is_moeda($perfilLimite))     $ret .= "Limite inválido.".PHP_EOL;
            }

            //PerfilSaldo
            $perfilSaldo = $objGamesUsuario->getPerfilSaldo();
            if (!is_null($perfilSaldo)) {
                if(is_null($perfilSaldo))             $ret .= "O Saldo deve ser preenchido.".PHP_EOL;
                elseif(!is_moeda($perfilSaldo))     $ret .= "Saldo inválido.".PHP_EOL;
            }


            //PerfilCorteDiaSemana
            $perfilCorteDiaSemana = $objGamesUsuario->getPerfilCorteDiaSemana();
            if (!is_null($perfilCorteDiaSemana)) {
                $perfilCorteDiaSemana = trim($objGamesUsuario->getPerfilCorteDiaSemana());
                if(is_null($perfilCorteDiaSemana) || $perfilCorteDiaSemana == "")     $ret .= "";
                else if(!is_numeric($perfilCorteDiaSemana))                         $ret .= "O Dia do Corte deve ser númerico.".PHP_EOL;
            }

            //PerfilCorteUltimoCorte
            $perfilCorteUltimoCorte = $objGamesUsuario->getPerfilCorteUltimoCorte();
            if (!is_null($perfilCorteUltimoCorte)) {
                $perfilCorteUltimoCorte = trim($objGamesUsuario->getPerfilCorteUltimoCorte());
                if(is_null($perfilCorteUltimoCorte) || $perfilCorteUltimoCorte == "")     $ret .= "";
                elseif(verifica_data2($perfilCorteUltimoCorte) == 0)                        $ret .= "A Data do Último Corte é inválida.".PHP_EOL;
            }

            //PerfilLimiteSugerido
            $perfilLimiteSugerido = $objGamesUsuario->getPerfilLimiteSugerido();
            if (!is_null($perfilLimiteSugerido)) {
                if(is_null($perfilLimiteSugerido))             $ret .= "";
                elseif(!is_moeda($perfilLimiteSugerido))     $ret .= "Limite inválido.".PHP_EOL;
            }

            //CreditoPendente
            $creditoPendente = $objGamesUsuario->getCreditoPendente();
            if (!is_null($creditoPendente)) {
                if(is_null($creditoPendente))             $ret .= "";
                elseif(!is_moeda($creditoPendente))     $ret .= "Crédito Pendente inválido.".PHP_EOL;
            }


            //InscrEstadual
            $InscrEstadual = $objGamesUsuario->getInscrEstadual();
            if (!is_null($InscrEstadual) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $InscrEstadual = trim($objGamesUsuario->getInscrEstadual());
                if($tipoCadastro == 'PJ' && (is_null($InscrEstadual) || $InscrEstadual == ""))     $ret .= "";
                elseif(strlen($InscrEstadual) > 20)     $ret .= "A Inscrição Estadual deve ter até 20 caracteres.".PHP_EOL;
            }

            //Site
            $Site = $objGamesUsuario->getSite();
            if ((!is_null($Site) && $tipoCadastro == 'PJ') || ($blCompleto && $tipoCadastro == 'PJ')) {
                $Site = trim($objGamesUsuario->getSite());
                if($tipoCadastro == 'PJ' && (is_null($Site) || $Site == ""))     $ret .= "";
                elseif(strlen($Site) > 250)     $ret .= "A URL do Site deve ter até 250 caracteres.".PHP_EOL;
            }

            /*
            //AberturaAno;
            $AberturaAno = $objGamesUsuario->getAberturaAno();
            if (!is_null($AberturaAno) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $AberturaAno = trim($objGamesUsuario->getAberturaAno());
                if($tipoCadastro == 'PJ' && (is_null($AberturaAno) || $AberturaAno == ""))     $ret .= "O Ano de Abertura da empresa deve ser preenchido.".PHP_EOL;
                else if(!is_numeric($AberturaAno))     $ret .= "O Ano de Abertura da empresa deve ser númerico.".PHP_EOL;
                else if(intval($AberturaAno) > date('Y'))    $ret .= "O Ano de Abertura da empresa é inválido.".PHP_EOL;
            }

            //AberturaMes
            $AberturaMes = $objGamesUsuario->getAberturaMes();

            if ((!is_null($AberturaMes) && $tipoCadastro == 'PJ') || ($blCompleto && $tipoCadastro == 'PJ')) {
                $AberturaMes = trim($objGamesUsuario->getAberturaMes());
                if($tipoCadastro == 'PJ' && (is_null($AberturaMes) || $AberturaMes == ""))     $ret .= "O Mês de Abertura da empresa deve ser preenchido.".PHP_EOL;
                else if(!is_numeric($AberturaMes)) $ret .= "O Mês de Abertura da empresa deve ser númerico.".PHP_EOL;
                else if(intval($AberturaMes) < 1 || intval($AberturaMes) > 12)    $ret .= "O Mês de Abertura da empresa é inválido.".PHP_EOL;
            }
            */

            //ReprLegalNome
            $ReprLegalNome = $objGamesUsuario->getReprLegalNome();
            if (!is_null($ReprLegalNome) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $ReprLegalNome = trim($objGamesUsuario->getReprLegalNome());
                if($tipoCadastro == 'PJ' && (is_null($ReprLegalNome) || $ReprLegalNome == ""))     $ret .= "O Nome do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
                elseif(strlen($ReprLegalNome) > 50)         $ret .= "O Nome do Representante Legal da Empresa deve ter até 50 caracteres.".PHP_EOL;
            }

            //ReprLegalRG
            $ReprLegalRG = $objGamesUsuario->getReprLegalRG();
            if (!is_null($ReprLegalRG) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $ReprLegalRG = trim($objGamesUsuario->getReprLegalRG());
                if($tipoCadastro == 'PJ' && (is_null($ReprLegalRG) || $ReprLegalRG == ""))     $ret .= "O RG do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
                //             elseif(!eregi("^[0-9]{7,13}$", $ReprLegalRG))     $ret .= "O RG do Representante Legal da Empresa é inválido. Utilize somente números sem letras, pontos, barra e traço".PHP_EOL;
            }

            //ReprLegalCPF
            $ReprLegalCPF = $objGamesUsuario->getReprLegalCPF();

            if (!is_null($ReprLegalCPF) || ($blCompleto && $tipoCadastro == 'PJ')) {
                if($tipoCadastro == 'PJ' && (is_null($ReprLegalCPF) || $ReprLegalCPF == ""))     $ret .= "O CPF do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
               // elseif(verificaCPFEx($ReprLegalCPF) == 0)         $ret .= "O CPF do Representante Legal da Empresa é inválido. Utilize somente números sem pontos, barra e traço".PHP_EOL;
            }
            
            $ReprLegalDataNascimento = $objGamesUsuario->getReprLegalDataNascimento();
            if (!is_null($ReprLegalDataNascimento) || ($blCompleto && $tipoCadastro == 'PJ')) {
                //if($tipoCadastro == 'PJ' && (is_null($ReprLegalDataNascimento) || $ReprLegalDataNascimento == ""))     $ret .= "A Data de Nascimento do Representante Legal deve ser preenchida.".PHP_EOL;
            }

            //ReprLegalTel DDI
            $ReprLegalTelDDI = $objGamesUsuario->getReprLegalTelDDI();
            if (!is_null($ReprLegalTelDDI) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $ReprLegalTelDDI = trim($objGamesUsuario->getReprLegalTelDDI());
                if($tipoCadastro == 'PJ' && (is_null($ReprLegalTelDDI) || $ReprLegalTelDDI == ""))    $ret .= "O Código do País do Telefone do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
                elseif(strlen($ReprLegalTelDDI) <> 2)                     $ret .= "O Código do País do Telefone do Representante Legal da Empresa deve ter 2 dígitos.".PHP_EOL;
                elseif(!is_numeric($ReprLegalTelDDI))                     $ret .= "O Código do País do Telefone do Representante Legal da Empresa deve ser númerico.".PHP_EOL;
            }

            //ReprLegalTel DDD
            $ReprLegalTelDDD = $objGamesUsuario->getReprLegalTelDDD();
            if (!is_null($ReprLegalTelDDD) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $ReprLegalTelDDD = trim($objGamesUsuario->getReprLegalTelDDD());
                //if($tipoCadastro == 'PJ' && (is_null($ReprLegalTelDDD) || $ReprLegalTelDDD == ""))    $ret .= "O DDD do Telefone do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
                //elseif(strlen($ReprLegalTelDDD) <> 2)                             $ret .= "O DDD do Telefone do Representante Legal da Empresa deve ter 2 dígitos.".PHP_EOL;
                //elseif(!is_numeric($ReprLegalTelDDD))                             $ret .= "O DDD do Telefone do Representante Legal da Empresa deve ser númerico.".PHP_EOL;
                //elseif($ReprLegalTelDDD <= 10 || ($ReprLegalTelDDD % 10 == 0))     $ret .= "O DDD do Telefone do Representante Legal da Empresa é inválido.".PHP_EOL;
            }

            //ReprLegalTel 
            $ReprLegalTel = $objGamesUsuario->getReprLegalTel();
            if (!is_null($ReprLegalTel) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $ReprLegalTel = trim($objGamesUsuario->getReprLegalTel());
                //if($tipoCadastro == 'PJ' && (is_null($ReprLegalTel) || $ReprLegalTel == ""))    $ret .= "O Telefone do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
                //elseif(verifica_telEx2($ReprLegalTel, false) == 0)    $ret .= "O Telefone do Representante Legal da Empresa é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
            }

            //ReprLegalCel DDI
            $ReprLegalCelDDI = $objGamesUsuario->getReprLegalCelDDI();
            if (!is_null($ReprLegalCelDDI) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $ReprLegalCelDDI = trim($objGamesUsuario->getReprLegalCelDDI());
                if($tipoCadastro == 'PJ' && (is_null($ReprLegalCelDDI) || $ReprLegalCelDDI == ""))    $ret .= "O Código do País do Celular do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
                elseif(strlen($ReprLegalCelDDI) <> 2)                     $ret .= "O Código do País do Celular do Representante Legal da Empresa deve ter 2 dígitos.".PHP_EOL;
                elseif(!is_numeric($ReprLegalCelDDI))                     $ret .= "O Código do País do Celular do Representante Legal da Empresa deve ser númerico.".PHP_EOL;
            }

            //ReprLegalCel DDD
            $ReprLegalCelDDD = $objGamesUsuario->getReprLegalCelDDD();
            if (!is_null($ReprLegalCelDDD) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $ReprLegalCelDDD = trim($objGamesUsuario->getReprLegalCelDDD());
                //if($tipoCadastro == 'PJ' && (is_null($ReprLegalCelDDD) || $ReprLegalCelDDD == ""))    $ret .= "O DDD do Celular do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
                //elseif(strlen($ReprLegalCelDDD) <> 2)                             $ret .= "O DDD do Celular do Representante Legal da Empresa deve ter 2 dígitos.".PHP_EOL;
                //elseif(!is_numeric($ReprLegalCelDDD))                             $ret .= "O DDD do Celular do Representante Legal da Empresa deve ser númerico.".PHP_EOL;
                //elseif($ReprLegalCelDDD <= 10 || ($ReprLegalCelDDD % 10 == 0))     $ret .= "O DDD do Celular do Representante Legal da Empresa é inválido.".PHP_EOL;
            }

            //ReprLegalCel 
            $ReprLegalCel = $objGamesUsuario->getReprLegalCel();
            if (!is_null($ReprLegalCel) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $ReprLegalCel = trim($objGamesUsuario->getReprLegalCel());
                //if($tipoCadastro == 'PJ' && (is_null($ReprLegalCel) || $ReprLegalCel == "")) $ret .= "O Celular do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
                //elseif(verifica_telEx2($ReprLegalCel, false) == 0)    $ret .= "O Celular do Representante Legal da Empresa é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
            }

            //ReprLegalEmail
            $ReprLegalemail = $objGamesUsuario->getReprLegalEmail();
            if (!is_null($ReprLegalemail) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $ReprLegalemail = trim($objGamesUsuario->getReprLegalEmail());
                if($tipoCadastro == 'PJ' && (is_null($ReprLegalemail) || $ReprLegalemail == "")) $ret .= "O Email do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
                elseif(strlen($ReprLegalemail) > 100)                     $ret .= "O Email do Representante Legal da Empresa deve ter até 100 caracteres.".PHP_EOL;
                elseif(!verifica_email2($ReprLegalemail))                 $ret .= "O Email do Representante Legal da Empresa é inválido.".PHP_EOL;
            }

            //ReprVendaIgualReprLegal
            $ReprVendaIgualReprLegal = $objGamesUsuario->getReprVendaIgualReprLegal();

            //ReprVendaNome
            $ReprVendaNome = $objGamesUsuario->getReprVendaNome();
            if ((!is_null($ReprVendaNome) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaNome = trim($objGamesUsuario->getReprVendaNome());
                if($tipoCadastro == 'PJ' && (is_null($ReprVendaNome) || $ReprVendaNome == ""))     $ret .= "O Nome do Representante Relacionado à Vendas deve ser preenchido.".PHP_EOL;
                elseif(strlen($ReprVendaNome) > 50)         $ret .= "O Nome do Representante Relacionado à Vendas deve ter até 50 caracteres.".PHP_EOL;
            }

            //ReprVendaRG
            $ReprVendaRG = $objGamesUsuario->getReprVendaRG();
            if ((!is_null($ReprVendaRG) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaRG = trim($objGamesUsuario->getReprVendaRG());
                if($tipoCadastro == 'PJ' && (is_null($ReprVendaRG) || $ReprVendaRG == ""))     $ret .= "O RG do Representante Relacionado à Vendas deve ser preenchido.".PHP_EOL;
                //             elseif(!eregi("^[0-9]{7,13}$", $ReprVendaRG))     $ret .= "O RG do Representante Relacionado à Vendas é inválido. Utilize somente números sem letras, pontos, barra e traço".PHP_EOL;
            }

            //ReprVendaCPF
            $ReprVendaCPF = $objGamesUsuario->getReprVendaCPF();
            if ((!is_null($ReprVendaCPF) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaCPF = trim($objGamesUsuario->getReprVendaCPF());
                if($tipoCadastro == 'PJ' && (is_null($ReprVendaCPF) || $ReprVendaCPF == ""))     $ret .= "O CPF do Representante Relacionado à Vendas deve ser preenchido.".PHP_EOL;
                elseif(verificaCPFEx($ReprVendaCPF) == 0)         $ret .= "O CPF do Representante Relacionado à Vendas é inválido. Utilize somente números sem pontos, barra e traço".PHP_EOL;
            }

            //ReprVendaTel DDI
            $ReprVendaTelDDI = $objGamesUsuario->getReprVendaTelDDI();
            if ((!(is_null($ReprVendaTelDDI) || ($ReprVendaTelDDI == '')) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaTelDDI = trim($objGamesUsuario->getReprVendaTelDDI());
                if($tipoCadastro == 'PJ' && (is_null($ReprVendaTelDDI) || $ReprVendaTelDDI == ""))    $ret .= "O Código do País do Telefone do Representante Relacionado à Vendas deve ser preenchido.".PHP_EOL;
                elseif(strlen($ReprVendaTelDDI) <> 2)                     $ret .= "O Código do País do Telefone do Representante Relacionado à Vendas deve ter 2 dígitos.".PHP_EOL;
                elseif(!is_numeric($ReprVendaTelDDI))                     $ret .= "O Código do País do Telefone do Representante Relacionado à Vendas deve ser númerico.".PHP_EOL;
            }

            //ReprVendaTel DDD
            $ReprVendaTelDDD = $objGamesUsuario->getReprVendaTelDDD();
            if ((!(is_null($ReprVendaTelDDD) || ($ReprVendaTelDDD == '')) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaTelDDD = trim($objGamesUsuario->getReprVendaTelDDD());
                if($tipoCadastro == 'PJ' && (is_null($ReprVendaTelDDD) || $ReprVendaTelDDD == ""))    $ret .= "O DDD do Telefone do Representante Relacionado à Vendas deve ser preenchido.".PHP_EOL;
                elseif(strlen($ReprVendaTelDDD) <> 2)                             $ret .= "O DDD do Telefone do Representante Relacionado à Vendas deve ter 2 dígitos.".PHP_EOL;
                elseif(!is_numeric($ReprVendaTelDDD))                             $ret .= "O DDD do Telefone do Representante Relacionado à Vendas deve ser númerico.".PHP_EOL;
                elseif($ReprVendaTelDDD <= 10 || ($ReprVendaTelDDD % 10 == 0))     $ret .= "O DDD do Telefone do Representante Relacionado à Vendas é inválido.".PHP_EOL;
            }

            //ReprVendaTel 
            $ReprVendaTel = $objGamesUsuario->getReprVendaTel();
            if ((!(is_null($ReprVendaTel) || ($ReprVendaTel == '')) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaTel = trim($objGamesUsuario->getReprVendaTel());
                if($tipoCadastro == 'PJ' && (is_null($ReprVendaTel) || $ReprVendaTel == ""))    $ret .= "O Telefone do Representante Relacionado à Vendas deve ser preenchido.".PHP_EOL;
                elseif(verifica_telEx2($ReprVendaTel, false) == 0) $ret .= "O Telefone do Representante Relacionado à Vendas é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
            }

            //ReprVendaCel DDI
            $ReprVendaCelDDI = $objGamesUsuario->getReprVendaCelDDI();
            if ((!(is_null($ReprVendaCelDDI) || ($ReprVendaCelDDI == '')) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaCelDDI = trim($objGamesUsuario->getReprVendaCelDDI());
                if($tipoCadastro == 'PJ' && (is_null($ReprVendaCelDDI) || $ReprVendaCelDDI == ""))    $ret .= "O Código do País do Celular do Representante Relacionado à Vendas deve ser preenchido.".PHP_EOL;
                elseif(strlen($ReprVendaCelDDI) <> 2)                     $ret .= "O Código do País do Celular do Representante Relacionado à Vendas deve ter 2 dígitos.".PHP_EOL;
                elseif(!is_numeric($ReprVendaCelDDI))                     $ret .= "O Código do País do Celular do Representante Relacionado à Vendas deve ser númerico.".PHP_EOL;
            }

            //ReprVendaCel DDD
            $ReprVendaCelDDD = $objGamesUsuario->getReprVendaCelDDD();
            if ((!(is_null($ReprVendaCelDDD) || ($ReprVendaCelDDD == '')) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaCelDDD = trim($objGamesUsuario->getReprVendaCelDDD());
                if($tipoCadastro == 'PJ' && (is_null($ReprVendaCelDDD) || $ReprVendaCelDDD == ""))    $ret .= "O DDD do Celular do Representante Relacionado à Vendas deve ser preenchido.".PHP_EOL;
                elseif(strlen($ReprVendaCelDDD) <> 2)                             $ret .= "O DDD do Celular do Representante Relacionado à Vendas deve ter 2 dígitos.".PHP_EOL;
                elseif(!is_numeric($ReprVendaCelDDD))                             $ret .= "O DDD do Celular do Representante Relacionado à Vendas deve ser númerico.".PHP_EOL;
                elseif($ReprVendaCelDDD <= 10 || ($ReprVendaCelDDD % 10 == 0))     $ret .= "O DDD do Celular do Representante Relacionado à Vendas é inválido.".PHP_EOL;
            }

            //ReprVendaCel 
            $ReprVendaCel = $objGamesUsuario->getReprVendaCel();
            if ((!(is_null($ReprVendaCel) || ($ReprVendaCel == '')) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaCel = trim($objGamesUsuario->getReprVendaCel());
                if($tipoCadastro == 'PJ' && (is_null($ReprVendaCel) || $ReprVendaCel == ""))    $ret .= "O Celular do Representante Relacionado à Vendas deve ser preenchido.".PHP_EOL;
                elseif(verifica_telEx2($ReprVendaCel, false) == 0)    $ret .= "O Celular do Representante Relacionado à Vendas é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
            }

            //ReprVendaEmail
            $ReprVendaemail = $objGamesUsuario->getReprVendaEmail();
            if ((!is_null($ReprVendaemail) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaemail = trim($objGamesUsuario->getReprVendaEmail());
                if($tipoCadastro == 'PJ' && (is_null($ReprVendaemail) || $ReprVendaemail == ""))     $ret .= "O Email do Representante Relacionado à Vendas deve ser preenchido.".PHP_EOL;
                elseif(strlen($ReprVendaemail) > 100)                     $ret .= "O Email do Representante Relacionado à Vendas deve ter até 100 caracteres.".PHP_EOL;
                elseif(!verifica_email2($ReprVendaemail))                 $ret .= "O Email do Representante Relacionado à Vendas é inválido.".PHP_EOL;
            }

            //ReprVendaMSN
            $ReprVendaMSN = $objGamesUsuario->getReprVendaMSN();
            if ((!is_null($ReprVendaMSN) || ($blCompleto && $tipoCadastro == 'PJ')) && $ReprVendaIgualReprLegal != 1) {
                $ReprVendaMSN = trim($objGamesUsuario->getReprVendaMSN());
                if(($tipoCadastro == 'PJ' && is_null($ReprVendaMSN) || $ReprVendaMSN == ""))     $ret .= "";
                elseif(strlen($ReprVendaMSN) > 100)                 $ret .= "O MSN do Representante Relacionado à Vendas deve ter até 100 caracteres.".PHP_EOL;
            }

            //getDadosBancarios01Banco
            $DadosBancarios01Banco = $objGamesUsuario->getDadosBancarios01Banco();
            if (!is_null($DadosBancarios01Banco) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $DadosBancarios01Banco = trim($objGamesUsuario->getDadosBancarios01Banco());
                if($tipoCadastro == 'PJ' && (is_null($DadosBancarios01Banco) || $DadosBancarios01Banco == ""))     $ret .= "";
                elseif(strlen($DadosBancarios01Banco) > 3)         $ret .= "O Banco da linha 1 de Dados Bancários deve ter até 4 caracteres.".PHP_EOL;
            }

            //getDadosBancarios01Agencia
            $DadosBancarios01Agencia = $objGamesUsuario->getDadosBancarios01Agencia();
            if (!(is_null($DadosBancarios01Agencia) || ($DadosBancarios01Agencia == '')) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $DadosBancarios01Agencia = trim($objGamesUsuario->getDadosBancarios01Agencia());
                if($tipoCadastro == 'PJ' && (is_null($DadosBancarios01Agencia) || $DadosBancarios01Agencia == ""))     $ret .= "";
                elseif(strlen($DadosBancarios01Agencia) < 2 || strlen($DadosBancarios01Agencia) > 5) $ret .= "A Agência da linha 1 de Dados Bancários deve ter até 5 caracteres. Formato: 4 dígitos + Digito de Conferência.".PHP_EOL;
            }

            //getDadosBancarios01Conta
            $DadosBancarios01Conta = $objGamesUsuario->getDadosBancarios01Conta();
            if (!(is_null($DadosBancarios01Conta) || ($DadosBancarios01Conta == '')) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $DadosBancarios01Conta = trim($objGamesUsuario->getDadosBancarios01Conta());
                if($tipoCadastro == 'PJ' && (is_null($DadosBancarios01Conta) || $DadosBancarios01Conta == ""))     $ret .= "";
                elseif(strlen($DadosBancarios01Conta) < 2 || strlen($DadosBancarios01Conta) > 11)     $ret .= "A Conta da linha 1 de Dados Bancários deve ter até 11 caracteres. Formato: 10 dígitos + Digito de Conferência.".PHP_EOL;
            }

            //getDadosBancarios01Abertura
            $DadosBancarios01Abertura = $objGamesUsuario->getDadosBancarios01Abertura();
            if (!(is_null($DadosBancarios01Abertura) || ($DadosBancarios01Abertura == '')) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $DadosBancarios01Abertura = trim($objGamesUsuario->getDadosBancarios01Abertura());
                if($tipoCadastro == 'PJ' && (is_null($DadosBancarios01Abertura) || $DadosBancarios01Abertura == ""))     $ret .= "";
                elseif(strlen($DadosBancarios01Abertura) != 7)                 $ret .= "A Data de Abertura da linha 1 de Dados Bancários deve ter o formato MM/AAAA.".PHP_EOL;
                elseif(verifica_data2("01/".$DadosBancarios01Abertura) == 0)    $ret .= "A Data de Abertura da linha 1 de Dados Bancários é inválida.".PHP_EOL;
            }

            //Se um item do Dados Bancarios for preenchido, todos os campos devem ser preenchidos         
            if (($blCompleto && $tipoCadastro == 'PJ') &&
                    ($DadosBancarios01Banco != "" || $DadosBancarios01Agencia != "" || $DadosBancarios01Conta != "" || $DadosBancarios01Abertura != "")) {

                if($DadosBancarios01Banco == "")     $ret .= "O Banco da linha 1 de Dados Bancários deve ser preenchido.".PHP_EOL;
                if($DadosBancarios01Agencia == "")     $ret .= "A Agência da linha 1 de Dados Bancários deve ser preenchida.".PHP_EOL;
                if($DadosBancarios01Conta == "")     $ret .= "A Conta da linha 1 de Dados Bancários deve ser preenchida.".PHP_EOL;
                if($DadosBancarios01Abertura == "") $ret .= "A Data de Abertura da linha 1 de Dados Bancários deve ser preenchida.".PHP_EOL;
            }

            //getDadosBancarios02Banco
            $DadosBancarios02Banco = $objGamesUsuario->getDadosBancarios02Banco();
            if (!is_null($DadosBancarios02Banco) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $DadosBancarios02Banco = trim($objGamesUsuario->getDadosBancarios02Banco());
                if($tipoCadastro == 'PJ' && (is_null($DadosBancarios02Banco) || $DadosBancarios02Banco == ""))     $ret .= "";
                elseif(strlen($DadosBancarios02Banco) > 3)    $ret .= "O Banco da linha 2 de Dados Bancários deve ter até 4 caracteres.".PHP_EOL;
            }

            //getDadosBancarios02Agencia
            $DadosBancarios02Agencia = $objGamesUsuario->getDadosBancarios02Agencia();
            if (!(is_null($DadosBancarios02Agencia) || ($DadosBancarios02Agencia == '')) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $DadosBancarios02Agencia = trim($objGamesUsuario->getDadosBancarios02Agencia());
                if($tipoCadastro == 'PJ' && (is_null($DadosBancarios02Agencia) || $DadosBancarios02Agencia == ""))     $ret .= "";
                elseif(strlen($DadosBancarios02Agencia) < 2 || strlen($DadosBancarios02Agencia) > 5)         $ret .= "A Agência da linha 2 de Dados Bancários deve ter até 5 caracteres. Formato: 4 dígitos + Digito de Conferência.".PHP_EOL;
            }

            //getDadosBancarios02Conta
            $DadosBancarios02Conta = $objGamesUsuario->getDadosBancarios02Conta();
            if (!(is_null($DadosBancarios02Conta) || ($DadosBancarios02Conta == '')) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $DadosBancarios02Conta = trim($objGamesUsuario->getDadosBancarios02Conta());
                if($tipoCadastro == 'PJ' && (is_null($DadosBancarios02Conta) || $DadosBancarios02Conta == ""))     $ret .= "";
                elseif(strlen($DadosBancarios02Conta) < 2 || strlen($DadosBancarios02Conta) > 11)         $ret .= "A Conta da linha 2 de Dados Bancários deve ter até 11 caracteres. Formato: 10 dígitos + Digito de Conferência.".PHP_EOL;
            }

            //getDadosBancarios02Abertura
            $DadosBancarios02Abertura = $objGamesUsuario->getDadosBancarios02Abertura();
            if (!(is_null($DadosBancarios02Abertura) || ($DadosBancarios02Abertura == '')) || ($blCompleto && $tipoCadastro == 'PJ')) {
                $DadosBancarios02Abertura = trim($objGamesUsuario->getDadosBancarios02Abertura());
                if($tipoCadastro == 'PJ' && (is_null($DadosBancarios02Abertura) || $DadosBancarios02Abertura == ""))     $ret .= "";
                elseif(strlen($DadosBancarios02Abertura) != 7)                 $ret .= "A Data de Abertura da linha 2 de Dados Bancários deve ter o formato MM/AAAA.".PHP_EOL;
                elseif(verifica_data2("01/".$DadosBancarios02Abertura) == 0)    $ret .= "A Data de Abertura da linha 2 de Dados Bancários é inválida.".PHP_EOL;
            }

            //Se um item do Dados Bancarios for preenchido, todos os campos devem ser preenchidos         
            if (($blCompleto && $tipoCadastro == 'PJ') &&
                    ($DadosBancarios02Banco != "" || $DadosBancarios02Agencia != "" || $DadosBancarios02Conta != "" || $DadosBancarios02Abertura != "")) {

                if($DadosBancarios02Banco == "")     $ret .= "O Banco da linha 2 de Dados Bancários deve ser preenchido.".PHP_EOL;
                if($DadosBancarios02Agencia == "")     $ret .= "A Agência da linha 2 de Dados Bancários deve ser preenchida.".PHP_EOL;
                if($DadosBancarios02Conta == "")     $ret .= "A Conta da linha 2 de Dados Bancários deve ser preenchida.".PHP_EOL;
                if($DadosBancarios02Abertura == "") $ret .= "A Data de Abertura da linha 2 de Dados Bancários deve ser preenchida.".PHP_EOL;
            }


            //Compet_participantes_fifa
            $compet_participantes_fifa = $objGamesUsuario->getCompet_participantes_fifa();
            if (!is_null($compet_participantes_fifa)) {
                if(!is_numeric($compet_participantes_fifa))                 $ret .= "O Número previsto de participantes FIFA deve ser numérico.".PHP_EOL;
            }

            //Compet_participantes_wc3
            $compet_participantes_wc3 = $objGamesUsuario->getCompet_participantes_wc3();
            if (!is_null($compet_participantes_wc3)) {
                if(!is_numeric($compet_participantes_wc3))                 $ret .= "O Número previsto de participantes WC3 deve ser numérico.".PHP_EOL;
            }

        }//end if ($blEditaCadastro)

        return $ret;
    }
    
    function cleanField($str){
        $restrict = array("-"," ","/",".");
        $auxReturn = str_replace($restrict,"",$str);
        return ($auxReturn==""?NULL:$auxReturn);
    }
 
    // validarCampos() reduzida para cadastro de Campeonato
    function validarCampos2($objGamesUsuario, $blCompleto) {

        $ret = "";

        //Dados do login
        if ($blCompleto)
            $ret .= UsuarioGames::validarCamposLogin($objGamesUsuario->getSenha(), $objGamesUsuario->getSenha(), $objGamesUsuario->getLogin());

        //login
        $login = $objGamesUsuario->getLogin();
        if (!is_null($login) || $blCompleto) {
            $login = trim($objGamesUsuario->getLogin());
             if(is_null($login) || $login == "")                 $ret .= "O Login deve ser preenchido.".PHP_EOL;
            elseif(strlen($login) < 6 || strlen($login) > 100)     $ret .= "O Login deve ter entre 6 e 100 caracteres.".PHP_EOL;
        }

        //Email
        $email = $objGamesUsuario->getEmail();
        if (!is_null($email) || $blCompleto) {
            $email = trim($objGamesUsuario->getEmail());
             if(is_null($email) || $email == "") $ret .= "O Email deve ser preenchido.".PHP_EOL;
            elseif(strlen($email) > 100)         $ret .= "O Email deve ter até 100 caracteres.".PHP_EOL;
            elseif(!verifica_email2($email))     $ret .= "O Email é inválido.".PHP_EOL;
        }

        //Endereco
        $endereco = $objGamesUsuario->getEndereco();
        if (!is_null($endereco) || $blCompleto) {
            $endereco = trim($objGamesUsuario->getEndereco());
             if(is_null($endereco) || $endereco == "")     $ret .= "O Endereço deve ser preenchido.".PHP_EOL;
             elseif(strlen($endereco) > 100)             $ret .= "O Endereço deve ter até 100 caracteres.".PHP_EOL;
        }

        //Tipo
        $tipoEnd = $objGamesUsuario->getTipoEnd();
        if (!is_null($tipoEnd) || $blCompleto) {
            $tipoEnd = trim($objGamesUsuario->getTipoEnd());
        }

        //Numero
        $numero = $objGamesUsuario->getNumero();
        if (!is_null($numero) || $blCompleto) {
            $numero = trim($objGamesUsuario->getNumero());
             if(is_null($numero) || $numero == "")     $ret .= "O Número deve ser preenchido.".PHP_EOL;
             elseif(strlen($numero) > 10)             $ret .= "O Número deve ter até 10 caracteres.".PHP_EOL;
        }

        //Complemento
        $complemento = $objGamesUsuario->getComplemento();
        if (!is_null($complemento) || $blCompleto) {
            $complemento = trim($objGamesUsuario->getComplemento());
             if(strlen($complemento) > 100)                 $ret .= "O Complemento deve ter até 100 caracteres.".PHP_EOL;
        }

        //Bairro
        $bairro = $objGamesUsuario->getBairro();
        if (!is_null($bairro) || $blCompleto) {
            $bairro = trim($objGamesUsuario->getBairro());
             if(is_null($bairro) || $bairro == "")     $ret .= "O Bairro deve ser preenchido.".PHP_EOL;
             elseif(strlen($bairro) > 100)             $ret .= "O Bairro deve ter até 100 caracteres.".PHP_EOL;
        }

        //Cidade
        $cidade = $objGamesUsuario->getCidade();
        if (!is_null($cidade) || $blCompleto) {
            $cidade = trim($objGamesUsuario->getCidade());
             if(is_null($cidade) || $cidade == "")     $ret .= "O Cidade deve ser preenchido.".PHP_EOL;
             elseif(strlen($cidade) > 100)             $ret .= "O Cidade deve ter até 100 caracteres.".PHP_EOL;
        }

        //Estado
        $estado = $objGamesUsuario->getEstado();
        if (!is_null($estado) || $blCompleto) {
            $estado = trim($objGamesUsuario->getEstado());
             if(is_null($estado) || $estado == "")     $ret .= "O Estado deve ser preenchido.".PHP_EOL;
             elseif(strlen($estado) <> 2)             $ret .= "O Estado deve ter 2 caracteres.".PHP_EOL;
        }

        //CEP
        $CEP = $objGamesUsuario->getCEP();
        if (!is_null($CEP) || $blCompleto) {
            $CEP = trim($objGamesUsuario->getCEP());
             if(is_null($CEP) || $CEP == "")    $ret .= "O CEP deve ser preenchido.".PHP_EOL;
             elseif(strlen($CEP) <> 8)         $ret .= "O CEP deve ser no formato 00000000. Sem traço.".PHP_EOL;
             elseif(!verifica_cepEx2($CEP, false))         $ret .= "O CEP é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
        }

        //Tel DDI
        $TelDDI = $objGamesUsuario->getTelDDI();
        if (!is_null($TelDDI) || $blCompleto) {
            $TelDDI = trim($objGamesUsuario->getTelDDI());
             if(is_null($TelDDI) || $TelDDI == "")    $ret .= "O Código do País do Telefone deve ser preenchido.".PHP_EOL;
             elseif(strlen($TelDDI) <> 2)             $ret .= "O Código do País do Telefone deve ter 2 dígitos.".PHP_EOL;
             elseif(!is_numeric($TelDDI))             $ret .= "O Código do País do Telefone deve ser númerico.".PHP_EOL;
        }

        //Tel DDD
        $TelDDD = $objGamesUsuario->getTelDDD();
        if (!is_null($TelDDD) || $blCompleto) {
            $TelDDD = trim($objGamesUsuario->getTelDDD());
             if(is_null($TelDDD) || $TelDDD == "")    $ret .= "O DDD do Telefone deve ser preenchido.".PHP_EOL;
             elseif(strlen($TelDDD) <> 2)             $ret .= "O DDD do Telefone deve ter 2 dígitos.".PHP_EOL;
             elseif(!is_numeric($TelDDD))             $ret .= "O DDD do Telefone deve ser númerico.".PHP_EOL;
             elseif($TelDDD <= 10 || ($TelDDD % 10 == 0)) $ret .= "O DDD do Telefone é inválido.".PHP_EOL;
        }

        //Tel 
        $Tel = $objGamesUsuario->getTel();
        if (!is_null($Tel) || $blCompleto) {
            $Tel = trim($objGamesUsuario->getTel());
             if(is_null($Tel) || $Tel == "")            $ret .= "O Telefone deve ser preenchido.".PHP_EOL;
             elseif(verifica_telEx2($Tel, false) == 0)$ret .= "O Telefone é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
        }

        //Cel DDI
        $CelDDI = $objGamesUsuario->getCelDDI();
        if (!is_null($CelDDI) || $blCompleto) {
            $CelDDI = trim($objGamesUsuario->getCelDDI());
             if(is_null($CelDDI) || $CelDDI == "")    $ret .= "";
            elseif(strlen($CelDDI) <> 2)             $ret .= "O Código do País do Celular deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($CelDDI))             $ret .= "O Código do País do Celular deve ser númerico.".PHP_EOL;
        }

        //Cel DDD
        $CelDDD = $objGamesUsuario->getCelDDD();
        if (!is_null($CelDDD) || $blCompleto) {
            $CelDDD = trim($objGamesUsuario->getCelDDD());
             if(is_null($CelDDD) || $CelDDD == "")    $ret .= "";
             elseif(strlen($CelDDD) <> 2)             $ret .= "O DDD do Celular deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($CelDDD))             $ret .= "O DDD do Celular deve ser númerico.".PHP_EOL;
             elseif($CelDDD <= 10 || ($CelDDD % 10 == 0)) $ret .= "O DDD do Celular é inválido.".PHP_EOL;
        }

        //Cel 
        $Cel = $objGamesUsuario->getCel();
        if (!is_null($Cel) || $blCompleto) {
            $Cel = trim($objGamesUsuario->getCel());
             if(is_null($Cel) || $Cel == "")            $ret .= "";
             elseif(verifica_telEx2($Cel, false) == 0)$ret .= "O Celular é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
        }

        //Fax DDI
        $FaxDDI = $objGamesUsuario->getFaxDDI();
        if (!is_null($FaxDDI) || $blCompleto) {
            $FaxDDI = trim($objGamesUsuario->getFaxDDI());
             if(is_null($FaxDDI) || $FaxDDI == "")    $ret .= "";
             elseif(strlen($FaxDDI) <> 2)             $ret .= "O Código do País do Fax deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($FaxDDI))             $ret .= "O Código do País do Fax deve ser númerico.".PHP_EOL;
        }

        //Fax DDD
        $FaxDDD = $objGamesUsuario->getFaxDDD();
        if (!is_null($FaxDDD) || $blCompleto) {
            $FaxDDD = trim($objGamesUsuario->getFaxDDD());
             if(is_null($FaxDDD) || $FaxDDD == "")    $ret .= "";
             elseif(strlen($FaxDDD) <> 2)             $ret .= "O DDD do Fax deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($FaxDDD))             $ret .= "O DDD do Fax deve ser númerico.".PHP_EOL;
             elseif($FaxDDD <= 10 || ($FaxDDD % 10 == 0)) $ret .= "O DDD do Fax é inválido.".PHP_EOL;
        }

        //Fax 
        $Fax = $objGamesUsuario->getFax();
        if (!is_null($Fax) || $blCompleto) {
            $Fax = trim($objGamesUsuario->getFax());
             if(is_null($Fax) || $Fax == "")            $ret .= "";
             elseif(verifica_telEx2($Fax, false) == 0)$ret .= "O Fax é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
        }


        //Observacoes
        $Observacoes = $objGamesUsuario->getObservacoes();
        if (!is_null($Observacoes) || $blCompleto) {
            $Observacoes = trim($objGamesUsuario->getObservacoes());
             if(is_null($Observacoes) || $Observacoes == "")     $ret .= "";
             elseif(strlen($Observacoes) > 2048)                     $ret .= "Observações deve ter até 2000 caracteres.".PHP_EOL;
        }

        //RiscoClassif
        $iRiscoClassif = $objGamesUsuario->getRiscoClassif();
        if (!is_null($iRiscoClassif) || $blCompleto) {
            $iRiscoClassif = trim($objGamesUsuario->getRiscoClassif());
             if(is_null($iRiscoClassif) || $iRiscoClassif == "")     $ret .= "";
        }

        //Tipo Cadastro
        $tipoCadastro = $objGamesUsuario->getTipoCadastro();
        if (!is_null($tipoCadastro) || $blCompleto) {
            $tipoCadastro = trim($objGamesUsuario->getTipoCadastro());
             if(is_null($tipoCadastro) || $tipoCadastro == "")     $ret .= "O Tipo de Cadastro deve ser selecionado.".PHP_EOL;
             elseif($tipoCadastro != 'PJ' && $tipoCadastro != 'PF')     $ret .= "Tipo de Cadastro inválido.".PHP_EOL;
        }


        //NomeFantasia
        $nomeFantasia = $objGamesUsuario->getNomeFantasia();
        if (!is_null($nomeFantasia) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $nome = trim($objGamesUsuario->getNomeFantasia());
             //if($tipoCadastro == 'PJ' && (is_null($nomeFantasia) || $nomeFantasia == ""))     $ret .= "O Nome Fantasia deve ser preenchido.".PHP_EOL;
			 //elseif
             if(strlen($nomeFantasia) > 100)                 $ret .= "O Nome Fantasia deve ter até 100 caracteres.".PHP_EOL;
        }


        //RazaoSocial
        $razaoSocial = $objGamesUsuario->getRazaoSocial();
        if (!is_null($razaoSocial) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $razaoSocial = trim($objGamesUsuario->getRazaoSocial());
             if($tipoCadastro == 'PJ' && (is_null($razaoSocial) || $razaoSocial == ""))     $ret .= "A Razão Social deve ser preenchida.".PHP_EOL;
             elseif(strlen($razaoSocial) > 100)                     $ret .= "A Razão Social deve ter até 100 caracteres.".PHP_EOL;
        }

        //CNPJ
        $CNPJ = $objGamesUsuario->getCNPJ();
        if (!is_null($CNPJ) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $CNPJ = trim($objGamesUsuario->getCNPJ());
            if ($tipoCadastro == 'PJ') {
                  if(is_null($CNPJ) || $CNPJ == "")     $ret .= "O CNPJ deve ser preenchido.".PHP_EOL;
                 elseif(verificaCNPJ($CNPJ) == 0)     $ret .= "O CNPJ inválido. Utilize somente números sem pontos, barra e traço (3)".PHP_EOL;
            } else {
                 if($CNPJ != "" && verificaCNPJ($CNPJ) == 0)     $ret .= "O CNPJ inválido. Utilize somente números sem pontos, barra e traço (4)".PHP_EOL;
            }
        }

        //Responsavel
        $responsavel = $objGamesUsuario->getResponsavel();
        if (!is_null($responsavel) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $nome = trim($objGamesUsuario->getResponsavel());
             if($tipoCadastro == 'PJ' && (is_null($responsavel) || $responsavel == ""))     $ret .= "O Responsável deve ser preenchido.".PHP_EOL;
             elseif(strlen($responsavel) > 100)                     $ret .= "O Responsável deve ter até 100 caracteres.".PHP_EOL;
        }

        //RACodigo e RAOutros
        $RACodigo = $objGamesUsuario->getRACodigo();
        $RAOutros = $objGamesUsuario->getRAOutros();
        if (!is_null($RACodigo) || !is_null($RAOutros) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $RACodigo = trim($objGamesUsuario->getRACodigo());
            $RAOutros = trim($objGamesUsuario->getRAOutros());
            if($tipoCadastro == 'PJ' && ((is_null($RACodigo) || $RACodigo == "") && (is_null($RAOutros) || $RAOutros == ""))) $ret .= "";
            elseif((!is_null($RACodigo) && $RACodigo != "") && (!is_null($RAOutros) && $RAOutros != ""))$ret .= "No Ramo de Atividade, preencher \"Outros\" somente se nenhum ramo for selecionado.".PHP_EOL;
            else {
                if (!is_null($RACodigo) && $RACodigo != "") {
                    if(strlen($RACodigo) > 8) $ret .= "O Ramo de Atividade é inválido.".PHP_EOL;
                }
                if (!is_null($RAOutros) && $RAOutros != "") {
                    if(strlen($RAOutros) > 60) $ret .= "O Ramo de Atividade (Outros) deve ter até 60 caracteres.".PHP_EOL;
                }
            }
        }


        //Contato01 Tel DDI
        $Contato01TelDDI = $objGamesUsuario->getContato01TelDDI();
        if (!is_null($Contato01TelDDI) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $Contato01TelDDI = trim($objGamesUsuario->getContato01TelDDI());
            if ($tipoCadastro == 'PJ') {
                 if(is_null($Contato01TelDDI) || $Contato01TelDDI == "")    $ret .= "";
                 elseif(strlen($Contato01TelDDI) <> 2)                     $ret .= "O Código do País do Telefone do Contato Técnico deve ter 2 dígitos.".PHP_EOL;
                 elseif(!is_numeric($Contato01TelDDI))                     $ret .= "O Código do País do Telefone do Contato Técnico deve ser númerico.".PHP_EOL;
            } else {
                 if(strlen($Contato01TelDDI) > 2)                                 $ret .= "O Código do País do Telefone do Contato Técnico deve ter até 2 dígitos.".PHP_EOL;
                 elseif($Contato01TelDDI != "" && !is_numeric($Contato01TelDDI)) $ret .= "O Código do País do Telefone do Contato Técnico deve ser númerico.".PHP_EOL;
            }
        }

        //Contato01 Tel DDD
        $Contato01TelDDD = $objGamesUsuario->getContato01TelDDD();
        if (!is_null($Contato01TelDDD) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $Contato01TelDDD = trim($objGamesUsuario->getContato01TelDDD());

            if ($tipoCadastro == 'PJ') {
                 if(is_null($Contato01TelDDD) || $Contato01TelDDD == "")    $ret .= "";
                 elseif(strlen($Contato01TelDDD) <> 2)                     $ret .= "O DDD do Telefone do Contato Técnico deve ter 2 dígitos.".PHP_EOL;
                 elseif(!is_numeric($Contato01TelDDD))                     $ret .= "O DDD do Telefone do Contato Técnico deve ser númerico.".PHP_EOL;
            } else {
                 if(strlen($Contato01TelDDD) > 2)                                 $ret .= "O DDD do Telefone do Contato Técnico deve ter até 2 dígitos.".PHP_EOL;
                 elseif($Contato01TelDDD != "" && !is_numeric($Contato01TelDDD)) $ret .= "O DDD do Telefone do Contato Técnico deve ser númerico.".PHP_EOL;
                 elseif($Contato01TelDDD != "" && ($Contato01TelDDD <= 10 || ($Contato01TelDDD % 10 == 0))) $ret .= "O DDD do Telefone do Contato Técnico é inválido.".PHP_EOL;
            }
        }

        //Contato01 Tel 
        $Contato01Tel = $objGamesUsuario->getContato01Tel();
        if (!is_null($Contato01Tel) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $Contato01Tel = trim($objGamesUsuario->getContato01Tel());

            if ($tipoCadastro == 'PJ') {
                 if(is_null($Contato01Tel) || $Contato01Tel == "")    $ret .= "";
                 elseif(verifica_telEx2($Contato01Tel, false) == 0)    $ret .= "O Telefone do Contato Técnico é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
            } else {

                 if($Contato01Tel != "" && verifica_telEx2($Contato01Tel, false) == 0) $ret .= "O Telefone do Contato Técnico é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
                 
            }
        }

        //Contato01Nome
        $Contato01Nome = $objGamesUsuario->getContato01Nome();
        if (!is_null($Contato01Nome) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $Contato01Nome = trim($objGamesUsuario->getContato01Nome());
             if($tipoCadastro == 'PJ' && (is_null($Contato01Nome) || $Contato01Nome == "")) $ret .= "";
             elseif(strlen($Contato01Nome) > 20)                 $ret .= "O Nome do Contato Técnico deve ter até 20 caracteres.".PHP_EOL;
        }


        //Contato01Cargo
        $Contato01Cargo = $objGamesUsuario->getContato01Cargo();
        if (!is_null($Contato01Cargo) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $Contato01Cargo = trim($objGamesUsuario->getContato01Cargo());
             if($tipoCadastro == 'PJ' && (is_null($Contato01Cargo) || $Contato01Cargo == ""))     $ret .= "";
             elseif(strlen($Contato01Cargo) > 20)                     $ret .= "O Cargo do Contato Técnico deve ter até 20 caracteres.".PHP_EOL;
        }


        //Nome
        $nome = $objGamesUsuario->getNome();
        if (!is_null($nome) || ($blCompleto && $tipoCadastro == 'PF')) {
            $nome = trim($objGamesUsuario->getNome());
             if($tipoCadastro == 'PF' && (is_null($nome) || $nome == ""))     $ret .= "O Nome deve ser preenchido.".PHP_EOL;
             elseif(strlen($nome) > 100)         $ret .= "O Nome deve ter até 100 caracteres.".PHP_EOL;
        }

        //CPF e RG
        $CPF = $objGamesUsuario->getCPF();
        $RG = $objGamesUsuario->getRG();
        if (!is_null($CPF) || !is_null($RG) || ($blCompleto && $tipoCadastro == 'PF')) {
            $CPF = trim($objGamesUsuario->getCPF());
            $RG = trim($objGamesUsuario->getRG());
            if($tipoCadastro == 'PF' && ((is_null($CPF) || $CPF == "") && (is_null($RG) || $RG == ""))) $ret .= "O CPF ou RG deve ser preenchido.".PHP_EOL;
            else {
                if (!is_null($CPF) && $CPF != "") {
                    if(verificaCPFEx($CPF) == 0) $ret .= "O CPF é inválido. Utilize somente números sem pontos, barra e traço".PHP_EOL;
                }
                if (!is_null($RG) && $RG != "") {
//                    if(!eregi("^[0-9,A-Z]{7,13}$", $RG)) $ret .= "O RG é inválido. Utilize somente números e letras, sem pontos, barra e traço".PHP_EOL;
                }
            }
        }

        //Data Nascimento
        $dataNascimento = $objGamesUsuario->getDataNascimento();
        if (!is_null($dataNascimento) || ($blCompleto && $tipoCadastro == 'PF')) {
            $dataNascimento = trim($objGamesUsuario->getDataNascimento());
             if($tipoCadastro == 'PF' && (is_null($dataNascimento) || $dataNascimento == ""))     $ret .= "A Data de Nascimento deve ser preenchida.".PHP_EOL;
             elseif(verifica_data2($dataNascimento) == 0)                $ret .= "A Data de Nascimento é inválida.".PHP_EOL;
        }

        //Sexo
        $sexo = $objGamesUsuario->getSexo();
        if (!is_null($sexo) || ($blCompleto && $tipoCadastro == 'PF')) {
            $sexo = trim($objGamesUsuario->getSexo());
            if ($tipoCadastro == 'PF') {
                 if(is_null($sexo) || $sexo == "") $ret .= "O Sexo deve ser preenchida.".PHP_EOL;
                 elseif(strtoupper($sexo) != "M" && strtoupper($sexo) != "F")$ret .= "O Sexo é inválido.".PHP_EOL;
            }
        }


        //PerfilSenhaReimpressao
        $perfilSenhaReimpressao = $objGamesUsuario->getPerfilSenhaReimpressao();
        if (!is_null($perfilSenhaReimpressao)) {
            $perfilSenhaReimpressao = trim($objGamesUsuario->getPerfilSenhaReimpressao());
             if(strlen($perfilSenhaReimpressao) > 50)                     $ret .= "A Senha de Reimpressão deve ter até 50 caracteres.".PHP_EOL;
        }

        //PerfilFormaPagto
        $perfilFormaPagto = $objGamesUsuario->getPerfilFormaPagto();
        if (!is_null($perfilFormaPagto)) {
            $perfilFormaPagto = trim($objGamesUsuario->getPerfilFormaPagto());
             if(is_null($perfilFormaPagto) || $perfilFormaPagto == "")     $ret .= "A Forma de Pagamento deve ser selecionada.".PHP_EOL;
            else if(!is_numeric($perfilFormaPagto))                     $ret .= "A Forma de Pagamento deve ser númerico.".PHP_EOL;
        }

        //PerfilLimite
        $perfilLimite = $objGamesUsuario->getPerfilLimite();
        if (!is_null($perfilLimite)) {
             if(is_null($perfilLimite))             $ret .= "O Limite deve ser preenchido.".PHP_EOL;
             elseif(!is_moeda($perfilLimite))     $ret .= "Limite inválido.".PHP_EOL;
        }

        //PerfilSaldo
        $perfilSaldo = $objGamesUsuario->getPerfilSaldo();
        if (!is_null($perfilSaldo)) {
             if(is_null($perfilSaldo))             $ret .= "O Saldo deve ser preenchido.".PHP_EOL;
             elseif(!is_moeda($perfilSaldo))     $ret .= "Saldo inválido.".PHP_EOL;
        }


        //PerfilCorteDiaSemana
        $perfilCorteDiaSemana = $objGamesUsuario->getPerfilCorteDiaSemana();
        if (!is_null($perfilCorteDiaSemana)) {
            $perfilCorteDiaSemana = trim($objGamesUsuario->getPerfilCorteDiaSemana());
             if(is_null($perfilCorteDiaSemana) || $perfilCorteDiaSemana == "")     $ret .= "";
            else if(!is_numeric($perfilCorteDiaSemana))                         $ret .= "O Dia do Corte deve ser númerico.".PHP_EOL;
        }

        //PerfilCorteUltimoCorte
        $perfilCorteUltimoCorte = $objGamesUsuario->getPerfilCorteUltimoCorte();
        if (!is_null($perfilCorteUltimoCorte)) {
            $perfilCorteUltimoCorte = trim($objGamesUsuario->getPerfilCorteUltimoCorte());
             if(is_null($perfilCorteUltimoCorte) || $perfilCorteUltimoCorte == "")     $ret .= "";
             elseif(verifica_data2($perfilCorteUltimoCorte) == 0)                        $ret .= "A Data do Último Corte é inválida.".PHP_EOL;
        }

        //PerfilLimiteSugerido
        $perfilLimiteSugerido = $objGamesUsuario->getPerfilLimiteSugerido();
        if (!is_null($perfilLimiteSugerido)) {
             if(is_null($perfilLimiteSugerido))             $ret .= "";
             elseif(!is_moeda($perfilLimiteSugerido))     $ret .= "Limite inválido.".PHP_EOL;
        }

        //CreditoPendente
        $creditoPendente = $objGamesUsuario->getCreditoPendente();
        if (!is_null($creditoPendente)) {
             if(is_null($creditoPendente))             $ret .= "";
             elseif(!is_moeda($creditoPendente))     $ret .= "Crédito Pendente inválido.".PHP_EOL;
        }


        //InscrEstadual
        $InscrEstadual = $objGamesUsuario->getInscrEstadual();
        if (!is_null($InscrEstadual) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $InscrEstadual = trim($objGamesUsuario->getInscrEstadual());
             if($tipoCadastro == 'PJ' && (is_null($InscrEstadual) || $InscrEstadual == ""))     $ret .= "";
             elseif(strlen($InscrEstadual) > 20)     $ret .= "A Inscrição Estadual deve ter até 20 caracteres.".PHP_EOL;
        }

        //Site
        $Site = $objGamesUsuario->getSite();
        if ((!is_null($Site) && $tipoCadastro == 'PJ') || ($blCompleto && $tipoCadastro == 'PJ')) {
            $Site = trim($objGamesUsuario->getSite());
             if($tipoCadastro == 'PJ' && (is_null($Site) || $Site == ""))     $ret .= "";
             elseif(strlen($Site) > 250)     $ret .= "A URL do Site deve ter até 250 caracteres.".PHP_EOL;
        }

        //AberturaAno
        $AberturaAno = $objGamesUsuario->getAberturaAno();
        if (!is_null($AberturaAno) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $AberturaAno = trim($objGamesUsuario->getAberturaAno());
             if($tipoCadastro == 'PJ' && (is_null($AberturaAno) || $AberturaAno == ""))     $ret .= "O Ano de Abertura da empresa deve ser preenchido.".PHP_EOL;
            else if(!is_numeric($AberturaAno))     $ret .= "O Ano de Abertura da empresa deve ser númerico.".PHP_EOL;
            else if(intval($AberturaAno) > date('Y'))    $ret .= "O Ano de Abertura da empresa é inválido.".PHP_EOL;
        }

        //AberturaMes
        $AberturaMes = $objGamesUsuario->getAberturaMes();

        if ((!is_null($AberturaMes) && $tipoCadastro == 'PJ') || ($blCompleto && $tipoCadastro == 'PJ')) {
            $AberturaMes = trim($objGamesUsuario->getAberturaMes());
             if($tipoCadastro == 'PJ' && (is_null($AberturaMes) || $AberturaMes == ""))     $ret .= "O Mês de Abertura da empresa deve ser preenchido.".PHP_EOL;
            else if(!is_numeric($AberturaMes)) $ret .= "O Mês de Abertura da empresa deve ser númerico.".PHP_EOL;
            else if(intval($AberturaMes) < 1 || intval($AberturaMes) > 12)    $ret .= "O Mês de Abertura da empresa é inválido.".PHP_EOL;
        }

        //FaturaMediaMensal
        $FaturaMediaMensal = $objGamesUsuario->getFaturaMediaMensal();
        if (!is_null($FaturaMediaMensal) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $FaturaMediaMensal = trim($objGamesUsuario->getFaturaMediaMensal());
             if($tipoCadastro == 'PJ' && (is_null($FaturaMediaMensal) || $FaturaMediaMensal == ""))     $ret .= "Pelo menos um Faturamento Médio Mensal deve ser selecionado.".PHP_EOL;
            else if(!is_numeric($FaturaMediaMensal))                     $ret .= "O Faturamento Médio Mensal deve ser númerico.".PHP_EOL;
        }

        //ReprLegalNome
        $ReprLegalNome = $objGamesUsuario->getReprLegalNome();
        if (!is_null($ReprLegalNome) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ReprLegalNome = trim($objGamesUsuario->getReprLegalNome());
             if($tipoCadastro == 'PJ' && (is_null($ReprLegalNome) || $ReprLegalNome == ""))     $ret .= "O Nome do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
             elseif(strlen($ReprLegalNome) > 50)         $ret .= "O Nome do Representante Legal da Empresa deve ter até 50 caracteres.".PHP_EOL;
        }

        //ReprLegalRG
        $ReprLegalRG = $objGamesUsuario->getReprLegalRG();
        if (!is_null($ReprLegalRG) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ReprLegalRG = trim($objGamesUsuario->getReprLegalRG());
             if($tipoCadastro == 'PJ' && (is_null($ReprLegalRG) || $ReprLegalRG == ""))     $ret .= "O RG do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
//             elseif(!eregi("^[0-9]{7,13}$", $ReprLegalRG))     $ret .= "O RG do Representante Legal da Empresa é inválido. Utilize somente números sem letras, pontos, barra e traço".PHP_EOL;
        }

        //ReprLegalCPF
        $ReprLegalCPF = $objGamesUsuario->getReprLegalCPF();
        if (!is_null($ReprLegalCPF) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ReprLegalCPF = trim($objGamesUsuario->getReprLegalCPF());
             if($tipoCadastro == 'PJ' && (is_null($ReprLegalCPF) || $ReprLegalCPF == ""))     $ret .= "O CPF do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
             elseif(verificaCPFEx($ReprLegalCPF) == 0)         $ret .= "O CPF do Representante Legal da Empresa é inválido. Utilize somente números sem pontos, barra e traço".PHP_EOL;
        }

        //ReprLegalTel DDI
        $ReprLegalTelDDI = $objGamesUsuario->getReprLegalTelDDI();
        if (!is_null($ReprLegalTelDDI) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ReprLegalTelDDI = trim($objGamesUsuario->getReprLegalTelDDI());
             if($tipoCadastro == 'PJ' && (is_null($ReprLegalTelDDI) || $ReprLegalTelDDI == ""))    $ret .= "O Código do País do Telefone do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
             elseif(strlen($ReprLegalTelDDI) <> 2)                     $ret .= "O Código do País do Telefone do Representante Legal da Empresa deve ter 2 dígitos.".PHP_EOL;
             elseif(!is_numeric($ReprLegalTelDDI))                     $ret .= "O Código do País do Telefone do Representante Legal da Empresa deve ser númerico.".PHP_EOL;
        }

        //ReprLegalTel DDD
        $ReprLegalTelDDD = $objGamesUsuario->getReprLegalTelDDD();
        if (!is_null($ReprLegalTelDDD) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ReprLegalTelDDD = trim($objGamesUsuario->getReprLegalTelDDD());
             if($tipoCadastro == 'PJ' && (is_null($ReprLegalTelDDD) || $ReprLegalTelDDD == ""))    $ret .= "O DDD do Telefone do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
             elseif(strlen($ReprLegalTelDDD) <> 2)                             $ret .= "O DDD do Telefone do Representante Legal da Empresa deve ter 2 dígitos.".PHP_EOL;
             elseif(!is_numeric($ReprLegalTelDDD))                             $ret .= "O DDD do Telefone do Representante Legal da Empresa deve ser númerico.".PHP_EOL;
             elseif($ReprLegalTelDDD <= 10 || ($ReprLegalTelDDD % 10 == 0))     $ret .= "O DDD do Telefone do Representante Legal da Empresa é inválido.".PHP_EOL;
        }

        //ReprLegalTel 
        $ReprLegalTel = $objGamesUsuario->getReprLegalTel();
        if (!is_null($ReprLegalTel) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ReprLegalTel = trim($objGamesUsuario->getReprLegalTel());
             if($tipoCadastro == 'PJ' && (is_null($ReprLegalTel) || $ReprLegalTel == ""))    $ret .= "O Telefone do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
             elseif(verifica_telEx2($ReprLegalTel, false) == 0)    $ret .= "O Telefone do Representante Legal da Empresa é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
        }

        //ReprLegalCel DDI
        $ReprLegalCelDDI = $objGamesUsuario->getReprLegalCelDDI();
        if (!is_null($ReprLegalCelDDI) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ReprLegalCelDDI = trim($objGamesUsuario->getReprLegalCelDDI());
             if($tipoCadastro == 'PJ' && (is_null($ReprLegalCelDDI) || $ReprLegalCelDDI == ""))    $ret .= "O Código do País do Celular do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
            elseif(strlen($ReprLegalCelDDI) <> 2)                     $ret .= "O Código do País do Celular do Representante Legal da Empresa deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($ReprLegalCelDDI))                     $ret .= "O Código do País do Celular do Representante Legal da Empresa deve ser númerico.".PHP_EOL;
        }

        //ReprLegalCel DDD
        $ReprLegalCelDDD = $objGamesUsuario->getReprLegalCelDDD();
        if (!is_null($ReprLegalCelDDD) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ReprLegalCelDDD = trim($objGamesUsuario->getReprLegalCelDDD());
             if($tipoCadastro == 'PJ' && (is_null($ReprLegalCelDDD) || $ReprLegalCelDDD == ""))    $ret .= "O DDD do Celular do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
             elseif(strlen($ReprLegalCelDDD) <> 2)                             $ret .= "O DDD do Celular do Representante Legal da Empresa deve ter 2 dígitos.".PHP_EOL;
            elseif(!is_numeric($ReprLegalCelDDD))                             $ret .= "O DDD do Celular do Representante Legal da Empresa deve ser númerico.".PHP_EOL;
             elseif($ReprLegalCelDDD <= 10 || ($ReprLegalCelDDD % 10 == 0))     $ret .= "O DDD do Celular do Representante Legal da Empresa é inválido.".PHP_EOL;
        }

        //ReprLegalCel 
        $ReprLegalCel = $objGamesUsuario->getReprLegalCel();
        if (!is_null($ReprLegalCel) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ReprLegalCel = trim($objGamesUsuario->getReprLegalCel());
             if($tipoCadastro == 'PJ' && (is_null($ReprLegalCel) || $ReprLegalCel == "")) $ret .= "O Celular do Representante Legal da Empresa deve ser preenchido.".PHP_EOL;
             elseif(verifica_telEx2($ReprLegalCel, false) == 0)    $ret .= "O Celular do Representante Legal da Empresa é inválido. Utilize o formato 00000000. Sem traço.".PHP_EOL;
        }

        //ReprLegalMSN
        $ReprLegalMSN = $objGamesUsuario->getReprLegalMSN();

//        if (!(is_null($ReprLegalMSN) || ($ReprLegalMSN == '')) || ($blCompleto && $tipoCadastro == 'PJ')) {
//            $ReprLegalMSN = trim($objGamesUsuario->getReprLegalMSN());
//             if($tipoCadastro == 'PJ' && (is_null($ReprLegalMSN) || $ReprLegalMSN == "")) $ret .= "";
//            elseif(strlen($ReprLegalMSN) > 100)                 $ret .= "O MSN do Representante Legal da Empresa deve ter até 100 caracteres.".PHP_EOL;
//            elseif(!verifica_email2($ReprLegalMSN))                 $ret .= "O MSN do Representante Legal da Empresa é inválido.".PHP_EOL;
//        }

        //ReprVendaIgualReprLegal
        $ReprVendaIgualReprLegal = $objGamesUsuario->getReprVendaIgualReprLegal();

        //getComputadoresQtde
        $ComputadoresQtde = $objGamesUsuario->getComputadoresQtde();
        if (!is_null($ComputadoresQtde) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ComputadoresQtde = trim($objGamesUsuario->getComputadoresQtde());
             if($tipoCadastro == 'PJ' && (is_null($ComputadoresQtde) || $ComputadoresQtde == ""))     $ret .= "Pelo menos um item de Quantos Computadores deve ser selecionado.".PHP_EOL;
            else if(!is_numeric($ComputadoresQtde)) $ret .= "A Quantidade de Computadores deve ser númerico.".PHP_EOL;
        }

        //ComunicacaoVisual
        $ComunicacaoVisual = $objGamesUsuario->getComunicacaoVisual();
        if (!is_null($ComunicacaoVisual) || ($blCompleto && $tipoCadastro == 'PJ')) {
            $ComunicacaoVisual = trim($objGamesUsuario->getComunicacaoVisual());
             if($tipoCadastro == 'PJ' && (is_null($ComunicacaoVisual) || $ComunicacaoVisual == ""))     $ret .= "Pelo menos uma Comunicação Visual deve ser selecionado.".PHP_EOL;
             elseif(strlen($ComunicacaoVisual) > 100)     $ret .= "A Comunicação Visual não pode passar de 100 caracteres.".PHP_EOL;
        }

        //Compet_participantes_fifa
        $compet_participantes_fifa = $objGamesUsuario->getCompet_participantes_fifa();
        if (!is_null($compet_participantes_fifa)) {
             if(!is_numeric($compet_participantes_fifa))                 $ret .= "O Número previsto de participantes FIFA deve ser numérico.".PHP_EOL;
        }

        //Compet_participantes_wc3
        $compet_participantes_wc3 = $objGamesUsuario->getCompet_participantes_wc3();
        if (!is_null($compet_participantes_wc3)) {
             if(!is_numeric($compet_participantes_wc3))                 $ret .= "O Número previsto de participantes WC3 deve ser numérico.".PHP_EOL;
        }

        return $ret;
    }

    function obterUltimoCorteStatus() {
        $cor_status = "";
        $sql = "select c.cor_status from cortes c where c.cor_ug_id = " . $this->ug_id . "";
        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) != 0) {
            $rs_row = pg_fetch_array($rs);
            $cor_status = $rs_row['cor_status'];
        }
        return $cor_status;
    }

    function obter($filtro, $orderBy, &$rs) {

        $ret = "";
        $filtro = array_map("strtoupper", $filtro);

        $sql = "select * from dist_usuarios_games ";

        if (!is_null($filtro) && $filtro != "") {

            if (isset($filtro['ug_data_inclusaoMin']) && !is_null($filtro['ug_data_inclusaoMin']) && isset($filtro['ug_data_inclusaoMax']) && !is_null($filtro['ug_data_inclusaoMax'])) {
                $filtro['ug_data_inclusaoMin'] = formata_data_ts($filtro['ug_data_inclusaoMin'] . " 00:00:00", 1, true, true);
                $filtro['ug_data_inclusaoMax'] = formata_data_ts($filtro['ug_data_inclusaoMax'] . " 23:59:59", 1, true, true);
            }

            if (isset($filtro['ug_data_ultimo_acessoMin']) && !is_null($filtro['ug_data_ultimo_acessoMin']) && isset($filtro['ug_data_ultimo_acessoMax']) && !is_null($filtro['ug_data_ultimo_acessoMax'])) {
                $filtro['ug_data_ultimo_acessoMin'] = formata_data_ts($filtro['ug_data_ultimo_acessoMin'] . " 00:00:00", 1, true, true);
                $filtro['ug_data_ultimo_acessoMax'] = formata_data_ts($filtro['ug_data_ultimo_acessoMax'] . " 23:59:59", 1, true, true);
            }

            if (isset($filtro['ug_data_nascimentoMin']) && !is_null($filtro['ug_data_nascimentoMin']) && isset($filtro['ug_data_nascimentoMax']) && !is_null($filtro['ug_data_nascimentoMax'])) {
                $filtro['ug_data_nascimentoMin'] = formata_data_ts($filtro['ug_data_nascimentoMin'] . " 00:00:00", 1, true, true);
                $filtro['ug_data_nascimentoMax'] = formata_data_ts($filtro['ug_data_nascimentoMax'] . " 23:59:59", 1, true, true);
            }

            if (isset($filtro['ug_iRiscoClassif']) && is_null($filtro['ug_iRiscoClassif'])) {
                $filtro['ug_iRiscoClassif'] = 0;
            }

            $sql .= " where 1=1";

            $sql .= " and (" . (is_null($filtro['ug_id']) ? 1 : 0);
            $sql .= "=1 or ug_id = " . SQLaddFields($filtro['ug_id'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_ativo']) ? 1 : 0);
            $sql .= "=1 or ug_ativo = " . SQLaddFields($filtro['ug_ativo'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_substatus']) ? 1 : 0);
            $sql .= "=1 " . (!is_null($filtro['ug_substatus']) ? " or ug_substatus IN " . SQLaddFields($filtro['ug_substatus'], "") : "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_data_inclusaoMin']) || is_null($filtro['ug_data_inclusaoMax']) ? 1 : 0);
            $sql .= "=1 or ug_data_inclusao between " . SQLaddFields($filtro['ug_data_inclusaoMin'], "") . " and " . SQLaddFields($filtro['ug_data_inclusaoMax'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_data_ultimo_acessoMin']) || is_null($filtro['ug_data_ultimo_acessoMax']) ? 1 : 0);
            $sql .= "=1 or ug_data_ultimo_acesso between " . SQLaddFields($filtro['ug_data_ultimo_acessoMin'], "") . " and " . SQLaddFields($filtro['ug_data_ultimo_acessoMax'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_qtde_acessosMin']) || is_null($filtro['ug_qtde_acessosMax']) ? 1 : 0);
            $sql .= "=1 or ug_qtde_acessos between " . SQLaddFields($filtro['ug_qtde_acessosMin'], "") . " and " . SQLaddFields($filtro['ug_qtde_acessosMax'], "") . ")";



            $sql .= " and (" . (is_null($filtro['ug_login']) ? 1 : 0);
            $sql .= "=1 or ug_login = '" . SQLaddFields($filtro['ug_login'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_loginLike']) ? 1 : 0);
            $sql .= "=1 or ug_login like '%" . SQLaddFields($filtro['ug_loginLike'], "r") . "%')";


            $sql .= " and (" . (is_null($filtro['ug_nome_fantasia']) ? 1 : 0);
            $sql .= "=1 or ug_nome_fantasia = '" . SQLaddFields($filtro['ug_nome_fantasia'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_nome_fantasiaLike']) ? 1 : 0);
            $sql .= "=1 or ug_nome_fantasia like '%" . SQLaddFields($filtro['ug_nome_fantasiaLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_razao_social']) ? 1 : 0);
            $sql .= "=1 or ug_razao_social = '" . SQLaddFields($filtro['ug_razao_social'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_razao_socialLike']) ? 1 : 0);
            $sql .= "=1 or ug_razao_social like '%" . SQLaddFields($filtro['ug_razao_socialLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_cnpj']) ? 1 : 0);
            $sql .= "=1 or ug_cnpj = '" . SQLaddFields($filtro['ug_cnpj'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_responsavel']) ? 1 : 0);
            $sql .= "=1 or ug_responsavel = '" . SQLaddFields($filtro['ug_responsavel'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_responsavelLike']) ? 1 : 0);
            $sql .= "=1 or ug_responsavel like '%" . SQLaddFields($filtro['ug_responsavelLike'], "r") . "%')";



            $sql .= " and (" . (is_null($filtro['ug_endereco']) ? 1 : 0);
            $sql .= "=1 or ug_endereco = '" . SQLaddFields($filtro['ug_endereco'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_enderecoLike']) ? 1 : 0);
            $sql .= "=1 or ug_endereco like '%" . SQLaddFields($filtro['ug_enderecoLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_numero']) ? 1 : 0);
            $sql .= "=1 or ug_numero = '" . SQLaddFields($filtro['ug_numero'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_complemento']) ? 1 : 0);
            $sql .= "=1 or ug_complemento = '" . SQLaddFields($filtro['ug_complemento'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_bairro']) ? 1 : 0);
            $sql .= "=1 or ug_bairro = '" . SQLaddFields($filtro['ug_bairro'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_bairroLike']) ? 1 : 0);
            $sql .= "=1 or ug_bairro like '%" . SQLaddFields($filtro['ug_bairroLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_cidade']) ? 1 : 0);
            $sql .= "=1 or ug_cidade = '" . SQLaddFields($filtro['ug_cidade'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_cidadeLike']) ? 1 : 0);
            $sql .= "=1 or ug_cidade like '%" . SQLaddFields($filtro['ug_cidadeLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_estado']) ? 1 : 0);
            $sql .= "=1 or ug_estado = '" . SQLaddFields($filtro['ug_estado'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_cep']) ? 1 : 0);
            $sql .= "=1 or ug_cep = '" . SQLaddFields($filtro['ug_cep'], "r") . "')";

            //Tel
            $sql .= " and (" . (is_null($filtro['ug_tel_ddi']) ? 1 : 0);
            $sql .= "=1 or ug_tel_ddi = '" . SQLaddFields($filtro['ug_tel_ddi'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_tel_ddd']) ? 1 : 0);
            $sql .= "=1 or ug_tel_ddd = '" . SQLaddFields($filtro['ug_tel_ddd'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_tel']) ? 1 : 0);
            $sql .= "=1 or ug_tel = '" . SQLaddFields($filtro['ug_tel'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_telLike']) ? 1 : 0);
            $sql .= "=1 or ug_tel like '%" . SQLaddFields($filtro['ug_telLike'], "r") . "%')";

            //Cel
            $sql .= " and (" . (is_null($filtro['ug_cel_ddi']) ? 1 : 0);
            $sql .= "=1 or ug_cel_ddi = '" . SQLaddFields($filtro['ug_cel_ddi'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_cel_ddd']) ? 1 : 0);
            $sql .= "=1 or ug_cel_ddd = '" . SQLaddFields($filtro['ug_cel_ddd'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_cel']) ? 1 : 0);
            $sql .= "=1 or ug_cel = '" . SQLaddFields($filtro['ug_cel'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_celLike']) ? 1 : 0);
            $sql .= "=1 or ug_cel like '%" . SQLaddFields($filtro['ug_celLike'], "r") . "%')";

            //Fax
            $sql .= " and (" . (is_null($filtro['ug_fax_ddi']) ? 1 : 0);
            $sql .= "=1 or ug_fax_ddi = '" . SQLaddFields($filtro['ug_fax_ddi'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_fax_ddd']) ? 1 : 0);
            $sql .= "=1 or ug_fax_ddd = '" . SQLaddFields($filtro['ug_fax_ddd'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_fax']) ? 1 : 0);
            $sql .= "=1 or ug_fax = '" . SQLaddFields($filtro['ug_fax'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_faxLike']) ? 1 : 0);
            $sql .= "=1 or ug_fax like '%" . SQLaddFields($filtro['ug_faxLike'], "r") . "%')";


            //RA
            $sql .= " and (" . (is_null($filtro['ug_ra_codigo']) ? 1 : 0);
            $sql .= "=1 or ug_ra_codigo = '" . SQLaddFields($filtro['ug_ra_codigo'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_ra_outros']) ? 1 : 0);
            $sql .= "=1 or ug_ra_outros = '" . SQLaddFields($filtro['ug_ra_outros'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_ra_outrosLike']) ? 1 : 0);
            $sql .= "=1 or ug_ra_outros like '%" . SQLaddFields($filtro['ug_ra_outrosLike'], "r") . "%')";

            //Contato 01
            $sql .= " and (" . (is_null($filtro['ug_contato01_tel_ddi']) ? 1 : 0);
            $sql .= "=1 or ug_contato01_tel_ddi = '" . SQLaddFields($filtro['ug_contato01_tel_ddi'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_contato01_tel_ddd']) ? 1 : 0);
            $sql .= "=1 or ug_contato01_tel_ddd = '" . SQLaddFields($filtro['ug_contato01_tel_ddd'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_contato01_tel']) ? 1 : 0);
            $sql .= "=1 or ug_contato01_tel = '" . SQLaddFields($filtro['ug_contato01_tel'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_contato01_telLike']) ? 1 : 0);
            $sql .= "=1 or ug_contato01_tel like '%" . SQLaddFields($filtro['ug_contato01_telLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_contato01_nome']) ? 1 : 0);
            $sql .= "=1 or ug_contato01_nome = '" . SQLaddFields($filtro['ug_contato01_nome'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_contato01_nomeLike']) ? 1 : 0);
            $sql .= "=1 or ug_contato01_nome like '%" . SQLaddFields($filtro['ug_contato01_nomeLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_contato01_cargo']) ? 1 : 0);
            $sql .= "=1 or ug_contato01_cargo = '" . SQLaddFields($filtro['ug_contato01_cargo'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_contato01_cargoLike']) ? 1 : 0);
            $sql .= "=1 or ug_contato01_cargo like '%" . SQLaddFields($filtro['ug_contato01_cargoLike'], "r") . "%')";

            //RiscoClassif
			$risco = (is_null($filtro['ug_iRiscoClassif']))? "0":$filtro['ug_iRiscoClassif'];
            $sql .= " and (" . ((is_null($filtro['ug_iRiscoClassif']) || ($filtro['ug_iRiscoClassif'] == 0)) ? 1 : 0);
            $sql .= "=1 or ug_risco_classif = " . $risco . ")"; //SQLaddFields($filtro['ug_iRiscoClassif'], "") . "')";

            $sql .= " and (" . (is_null($filtro['ug_tipo_cadastro']) ? 1 : 0);
            $sql .= "=1 or ug_tipo_cadastro = '" . SQLaddFields($filtro['ug_tipo_cadastro'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_nome']) ? 1 : 0);
            $sql .= "=1 or ug_nome = '" . SQLaddFields($filtro['ug_nome'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_nomeLike']) ? 1 : 0);
            $sql .= "=1 or ug_nome like '%" . SQLaddFields($filtro['ug_nomeLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_cpf']) ? 1 : 0);
            $sql .= "=1 or ug_cpf = '" . SQLaddFields($filtro['ug_cpf'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_rg']) ? 1 : 0);
            $sql .= "=1 or ug_rg = '" . SQLaddFields($filtro['ug_rg'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_data_nascimentoMin']) || is_null($filtro['ug_data_nascimentoMax']) ? 1 : 0);
            $sql .= "=1 or ug_data_nascimento between " . SQLaddFields($filtro['ug_data)nascimentoMin'], "") . " and " . SQLaddFields($filtro['ug_data_nascimentoMax'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_sexo']) ? 1 : 0);
            $sql .= "=1 or ug_sexo = '" . SQLaddFields($filtro['ug_sexo'], "r") . "')";


            $sql .= " and (" . (is_null($filtro['ug_perfil_senha_reimpressao']) ? 1 : 0);
            $sql .= "=1 or ug_perfil_senha_reimpressao = '" . SQLaddFields($filtro['ug_perfil_senha_reimpressao'], "r") . "')";
            $sql .= " and (" . (is_null($filtro['ug_perfil_senha_reimpressaoLike']) ? 1 : 0);
            $sql .= "=1 or ug_perfil_senha_reimpressao like '%" . SQLaddFields($filtro['ug_perfil_senha_reimpressaoLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_perfil_forma_pagto']) ? 1 : 0);
            $sql .= "=1 or ug_perfil_forma_pagto = " . SQLaddFields($filtro['ug_perfil_forma_pagto'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_perfil_limiteMin']) || is_null($filtro['ug_perfil_limiteMax']) ? 1 : 0);
            $sql .= "=1 or ug_perfil_limite between " . SQLaddFields($filtro['ug_perfil_limiteMin'], "") . " and " . SQLaddFields($filtro['ug_perfil_limiteMax'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_perfil_saldoMin']) || is_null($filtro['ug_perfil_saldoMax']) ? 1 : 0);
            $sql .= "=1 or ug_perfil_saldo between " . SQLaddFields($filtro['ug_perfil_saldoMin'], "") . " and " . SQLaddFields($filtro['ug_perfil_saldoMax'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_compet_participa']) ? 1 : 0);
            $sql .= "=1 or ug_compet_participa = '" . SQLaddFields($filtro['ug_compet_participa'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_compet_promoveu']) ? 1 : 0);
            $sql .= "=1 or ug_compet_promoveu = '" . SQLaddFields($filtro['ug_compet_promoveu'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_ongame']) ? 1 : 0);
            $sql .= "=1 or ug_ongame = '" . SQLaddFields($filtro['ug_ongame'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_te_id']) ? 1 : 0);
            $sql .= "=1 or ug_te_id = " . SQLaddFields($filtro['ug_te_id'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_id_nexcafe']) ? 1 : 0);
            $sql .= "=1 or ug_id_nexcafe = '" . SQLaddFields($filtro['ug_id_nexcafe'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_login_nexcafe_auto']) ? 1 : 0);
            $sql .= "=1 or ug_login_nexcafe_auto = " . SQLaddFields($filtro['ug_login_nexcafe_auto'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_data_inclusao_nexcafe']) || is_null($filtro['ug_data_inclusao_nexcafe']) ? 1 : 0);
            $sql .= "=1 or ug_data_inclusao_nexcafe between " . SQLaddFields($filtro['ug_data_inclusao_nexcafe'], "") . " and " . SQLaddFields($filtro['ug_data_inclusao_nexcafe'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_alterar_senha']) ? 1 : 0);
            $sql .= "=1 or ug_alterar_senha = " . SQLaddFields($filtro['ug_alterar_senha'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_exibir_contrato']) ? 1 : 0);
            $sql .= "=1 or ug_exibir_contrato = " . SQLaddFields($filtro['ug_exibir_contrato'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_data_aceite_adesao']) || is_null($filtro['ug_data_aceite_adesao']) ? 1 : 0);
            $sql .= "=1 or ug_data_aceite_adesao between " . SQLaddFields($filtro['ug_data_aceite_adesao'], "") . " and " . SQLaddFields($filtro['ug_data_aceite_adesao'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_tipo_venda']) || is_null($filtro['ug_tipo_venda']) ? 1 : 0);
            $sql .= "=1 or ug_tipo_venda = '" . SQLaddFields($filtro['ug_tipo_venda'], "s") . "')";

            $sql .= " and (" . (is_null($filtro['ug_recarga_celular']) ? 1 : 0);
            $sql .= "=1 or ug_recarga_celular = " . SQLaddFields($filtro['ug_recarga_celular'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_data_aprovacao']) ? 1 : 0);
            $sql .= "=1 or ug_data_aprovacao = " . SQLaddFields($filtro['ug_data_aprovacao'], "") . ")";

        }

        if(!is_null($orderBy)) $sql .= " order by " . $orderBy;

        $rs = SQLexecuteQuery($sql);
        if(!$rs) $ret = "Erro ao obter usuário(s).".PHP_EOL;

        return $ret;

    }

    function existeLogin($login, $usuario_id_excessao) {

        $ret = true;
        $err_cod = "";

        $params = array('login' => array('0' => $login,
                '1' => 'S',
                '2' => '1'
            )
        );
        $params = sanitize_input_data_array($params, $err_cod);
        extract($params, EXTR_OVERWRITE);

        $login = strtoupper(trim($login));

        //SQL
        $sql = "select count(*) as qtde from dist_usuarios_games ";
        $sql .= " where ug_login = " . SQLaddFields($login, "s");
        if ($usuario_id_excessao && !is_null($usuario_id_excessao) && is_numeric($usuario_id_excessao))
            $sql .= " and ug_id <> " . SQLaddFields(trim($usuario_id_excessao), "");


        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            if($rs_row['qtde'] == 0) $ret = false;
        }

        return $ret;
    }

    function operadorCadastrado($usuario_id, $operador_id) {

        $ret = true;
        if (strlen($operador_id) == 0) {
            return false;
        }
        //SQL
        $sql = "select count(*) as qtde from dist_usuarios_games_operador ";
        $sql .= " where ugo_id = " . $operador_id;
        $sql .= " and ugo_ug_id = " . $usuario_id;

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            if($rs_row['qtde'] == 0) $ret = false;
        }

        return $ret;
    }

    function existeEmail($email, $usuario_id_excessao = NULL) {

        if (!defined('RAIZ_DO_PROJETO')) {
            require_once "../../../includes/constantes.php";
        }
        if (!defined('DB_HOST')) {
            require_once RAIZ_DO_PROJETO . 'db/connect.php';
        }
        
        if (!class_exists('ConnectionPDO')) {
            require_once RAIZ_DO_PROJETO . 'db/ConnectionPDO.php';
        }
        //inicializando retorno como verdadeiro
        $ret = true;
        
        //Inicializando conexao PDO
        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
        
        //Inicializando variavel com email tratado
        $tmpEmail = strtoupper(trim($email));
        
        //Array para as demais Queries
        $tmpArray = array(':email' => $tmpEmail);
        
        //Inicializando variavel com excessao de ID
        $tmpExcessao = false;
        if ($usuario_id_excessao && !is_null($usuario_id_excessao) && is_numeric($usuario_id_excessao)) {
            $tmpExcessao = true;
            
            //Array para o a Query de LAN
            $tmpArrayLAN = array(
                                ':email'    => $tmpEmail,
                                ':excessao' => $usuario_id_excessao
                                );
            
        } //end if 
        else {
            //Array para o a Query de LAN
            $tmpArrayLAN = $tmpArray;
        }//end else do if para excessao
        
        // buscando em dist_usuarios_games
        $sql = "select count(*) as qtde from dist_usuarios_games  
                where ug_email IS NOT NULL 
                        and ug_email <> '' 
                        and ug_email = :email ";
        if($tmpExcessao) $sql .= " and ug_id <> :excessao ";
        $sql .= " ;";
        $rs = $pdo->prepare($sql);
        $rs->execute($tmpArrayLAN);
        $rs_row = $rs->fetch(PDO::FETCH_ASSOC);
        if($rs_row['qtde'] == 0) {
            
                    
                // buscando em dist_usuarios_games_operador 
                $sql = "select count(*) as qtde from dist_usuarios_games_operador  
                        where ugo_email IS NOT NULL 
                                and ugo_email <> '' 
                                and ugo_email = :email ;";
                $rs_operador = $pdo->prepare($sql);
                $rs_operador->execute($tmpArray);
                $rs_operador_row = $rs_operador->fetch(PDO::FETCH_ASSOC);
                if($rs_operador_row['qtde'] == 0) {

                        // buscando em usuarios_games 
                        $sql = "SELECT ug_ativo, count(*) as qtde 
                                    FROM usuarios_games  
                                        WHERE ug_email IS NOT NULL 
                                        AND ug_email <> '' 
                                        AND ug_email = :email 
                                        GROUP BY ug_ativo;";
                        $rs_gamer = $pdo->prepare($sql);
                        $rs_gamer->execute($tmpArray);
                      
                        $found = false;
                        $STATUS_PERMITIDOS = array(1, 2, 6);
                        while ($row = $rs_gamer->fetch(PDO::FETCH_ASSOC)) {
                            $found = true;
                            $status = $row['ug_ativo'];
                            $qtde = $row['qtde'];
                        
                            if (in_array($status, $STATUS_PERMITIDOS) && $qtde <= 1) {
                                $ret = false; //RETORNA FALSE POIS AINDA VAI DEIXAR CADASTRAR UM USUARIO
                                break;
                            } elseif (!in_array($status, $STATUS_PERMITIDOS)) {
                                $ret = true;
                                break;
                            }
                            
                        }//end if($rs_gamer_row['qtde'] == 0)  => usuarios_games
                        if (!$found) {
                            $ret = false;
                        }

                }//end if($rs_operador_row['qtde'] == 0)  => dist_usuarios_games_operador
                    
        } //end if($rs_row['qtde'] == 0)  => dist_usuarios_games
        
        //Retornando resposta
        return $ret;
    }//end function existeEmail

    function existeCNPJ($cnpj, $usuario_id_excessao) {

        $ret = true;

        //SQL
        $sql = "select count(*) as qtde from dist_usuarios_games ";
        $sql .= " where ug_cnpj IS NOT NULL and ug_cnpj <> '' and ug_cnpj = " . SQLaddFields(trim($cnpj), "s");
        if ($usuario_id_excessao && !is_null($usuario_id_excessao) && is_numeric($usuario_id_excessao))
            $sql .= " and ug_id <> " . SQLaddFields(trim($usuario_id_excessao), "");

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            if($rs_row['qtde'] == 0) $ret = false;
        }

        return $ret;
    }

    function existeCPF($cpf, $usuario_id_excessao) {

        $ret = true;

        //SQL
        $sql = "select count(*) as qtde from dist_usuarios_games ";
        $sql .= " where ug_cpf IS NOT NULL and ug_cpf <> '' and ug_cpf <> '00000000000' and ug_cpf <> '..-' and ug_cpf = " . SQLaddFields(trim($cpf), "s");
        if ($usuario_id_excessao && !is_null($usuario_id_excessao) && is_numeric($usuario_id_excessao))
            $sql .= " and ug_id <> " . SQLaddFields(trim($usuario_id_excessao), "");

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            if($rs_row['qtde'] == 0) $ret = false;
        }

        return $ret;
    }

    function existeRG($rg, $usuario_id_excessao) {

        $ret = true;

        //SQL
        $sql = "select count(*) as qtde from dist_usuarios_games ";
        $sql .= " where ug_rg IS NOT NULL and ug_rg <> '' and ug_rg = " . SQLaddFields(trim($rg), "s");
        if ($usuario_id_excessao && !is_null($usuario_id_excessao) && is_numeric($usuario_id_excessao))
            $sql .= " and ug_id <> " . SQLaddFields(trim($usuario_id_excessao), "");

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            if($rs_row['qtde'] == 0) $ret = false;
        }

        return $ret;
    }

    
    function autenticarLogin($login, $senha, $aut = false) {
        $senha0 = $senha;

        $ret = false;
        $senha0 = $senha;

        $err_cod = "";

        /*
        $params = array('login' => array('0' => $login,
                '1' => 'S',
                '2' => '1'
            )
        );
        $params = sanitize_input_data_array($params, $err_cod);
        extract($params, EXTR_OVERWRITE);
        */

        //Autentica usuario
        //------------------------------------------------------------------
        $objEncryption = new Encryption();
		$original = trim($senha);
        $senha = $objEncryption->encrypt(trim($senha));
        $login = strtoupper(trim($login));

		/*
			//SQL
			$sql = "select count(*) as qtde from dist_usuarios_games ";
			$sql .= " where ug_ativo = 1 ";
			$sql .= " and (ug_substatus = '11' or ug_substatus = '9') ";
			$sql .= " and ug_login = " . SQLaddFields($login, "s");
			$sql .= " and ug_senha = " . SQLaddFields($senha, "s");
		*/
		
		$sql = "select count(*) as qtde from dist_usuarios_games where ug_ativo = 1 and ug_substatus in ('11', '9') and ug_login = ? and ug_senha = ?"; //"select count(*) as qtde from dist_usuarios_games where ug_ativo = 1 and (ug_substatus = 11 or ug_substatus = 9) and ? in(ug_login,ug_cnpj) and ug_senha = ?";
		// $sql = "select count(*) as qtde from dist_usuarios_games where ug_ativo = 1 and (ug_substatus = 11 or ug_substatus = 9) and ug_login = ? and ug_senha = ?"; //"select count(*) as qtde from dist_usuarios_games where ug_ativo = 1 and (ug_substatus = 11 or ug_substatus = 9) and ? in(ug_login,ug_cnpj) and ug_senha = ?";

        //$file = fopen("/www/log/a.txt", "a+");
		//fwrite($file, "Login: ".$login."\n");
		//fwrite($file, "senha: ".$senha."\n");
		//fwrite($file, "senha: ".$original."\n");
		//fclose($file);

        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($login, $senha));
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
		    		
        if ( $fetch[0]['qtde'] > 0 ) {
            $ret = true;
        }
        /*
        $rs = SQLexecuteQuery($sql);

        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            if($rs_row['qtde'] > 0) $ret = true;
        }
        */

        //Adiciona objeto usuario no session
        if ($ret) {
            
            $ret = $this->adicionarLoginSession($login);
				
        } else {
            gravaLog_Login("Login de lanhouse falhou ($senha0): '$sql'.".PHP_EOL, true);
        }

        //Atualiza ultimo acesso
        //------------------------------------------------------------------
        if ($ret) {
            $sql = "UPDATE dist_usuarios_games 
                    SET ug_data_ultimo_acesso = CURRENT_TIMESTAMP, 
                        ug_qtde_acessos = ug_qtde_acessos + 1 
                        WHERE ug_login = :ug_login";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':ug_login', $login, PDO::PARAM_STR);
            $stmt->execute();

            //Log na base
            $obs = "";
            if($aut == true){
                $obs = "Login com autenticador";
            } else{
                $obs = "Login sem autenticador";
            }

            usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['LOGIN'], null, null,$obs);
            
        }

        return $ret;
    }

    function getUsuarioGamesById($usuario_id) {

        if(!$usuario_id || $usuario_id == "" || !is_numeric($usuario_id)) return null;

        $rs = null;
        $filtro['ug_id'] = $usuario_id;
        //$filtro['ug_ativo'] = 1;
        $ret = $this->obter($filtro, null, $rs);
        return $this->create($rs);
        
    }

    function getUsuarioGamesByLogin($login) {

        if(!$login || $login == "") return null;

        $rs = null;
        $filtro['ug_login'] = $login;
        //$filtro['ug_ativo'] = 1;
        $ret = UsuarioGames::obter($filtro, null, $rs);

        return UsuarioGames::create($rs);
        
    }
	
	function adicionarLoginSessionByIdDjx($ug_id) {
		if(!$ug_id || $ug_id == "") return false;
		
		$con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
		$sql = "SELECT * FROM dist_usuarios_games where ug_id = :UG_ID";
		$query = $pdo->prepare($sql);
		$query->bindValue(':UG_ID', $ug_id);
		$query->execute();
		
		if($query->rowCount() > 0) {
			
			$ret = $query->fetch(PDO::FETCH_ASSOC);
			
			return $ret;
		}
	}

    function adicionarLoginSession($login) {

        if(!$login || $login == "") return false;

        $rs = null;
		/*
		if(is_numeric($login)){
			$filtro['ug_cnpj'] = $login;
		}else{
			$filtro['ug_login'] = $login;
		}
		*/
		$filtro['ug_login'] = $login;
        $filtro['ug_ativo'] = 1;
        $filtro['ug_substatus'] = "(11,9)";
        $ret = UsuarioGames::obter($filtro, null, $rs);
	
        $usuarioGames = UsuarioGames::create($rs);

        if ($usuarioGames != null) {
            $ret = true;

            //Poe no session                
            $GLOBALS['_SESSION']['dist_usuarioGames_ser'] = serialize($usuarioGames);
            $GLOBALS['_SESSION']['dist_usuarioGames.horarioLogin'] = date("U");
            $GLOBALS['_SESSION']['dist_usuarioGames.horarioInatividade'] = date("U");
        } else {
            $ret = false;
        }
        return $ret;
    }

    function create($rs) {

        $usuarioGames = null;

        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            $usuarioGames = new UsuarioGames();
            $usuarioGames->setId($rs_row['ug_id']);
            $usuarioGames->setLogin($rs_row['ug_login']);
            // $usuarioGames->setSenha($rs_row['ug_senha']);
            $usuarioGames->setAtivo($rs_row['ug_ativo']);
            $usuarioGames->setStatusBusca($rs_row['ug_status']);
            $usuarioGames->setSubstatus($rs_row['ug_substatus']);
            $usuarioGames->setDataInclusao(formata_data_ts($rs_row['ug_data_inclusao'], 0, true, false));
            $usuarioGames->setDataUltimoAcesso(formata_data_ts($rs_row['ug_data_ultimo_acesso'], 0, true, false));
            $usuarioGames->setQtdeAcessos($rs_row['ug_qtde_acessos']);

            $usuarioGames->setNomeFantasia($rs_row['ug_nome_fantasia']);
            $usuarioGames->setRazaoSocial($rs_row['ug_razao_social']);
            $usuarioGames->setCNPJ($rs_row['ug_cnpj']);
            $usuarioGames->setResponsavel($rs_row['ug_responsavel']);
            $usuarioGames->setEmail($rs_row['ug_email']);

            $usuarioGames->setEndereco($rs_row['ug_endereco']);
            $usuarioGames->setTipoEnd($rs_row['ug_tipo_end']);
            $usuarioGames->setNumero($rs_row['ug_numero']);
            $usuarioGames->setComplemento($rs_row['ug_complemento']);
            $usuarioGames->setBairro($rs_row['ug_bairro']);
            $usuarioGames->setCidade($rs_row['ug_cidade']);
            $usuarioGames->setEstado($rs_row['ug_estado']);
            $usuarioGames->setCEP($rs_row['ug_cep']);

            $usuarioGames->setTelDDI($rs_row['ug_tel_ddi']);
            $usuarioGames->setTelDDD($rs_row['ug_tel_ddd']);
            $usuarioGames->setTel($rs_row['ug_tel']);
            $usuarioGames->setCelDDI($rs_row['ug_cel_ddi']);
            $usuarioGames->setCelDDD($rs_row['ug_cel_ddd']);
            $usuarioGames->setCel($rs_row['ug_cel']);
            $usuarioGames->setFaxDDI($rs_row['ug_fax_ddi']);
            $usuarioGames->setFaxDDD($rs_row['ug_fax_ddd']);
            $usuarioGames->setFax($rs_row['ug_fax']);

            $usuarioGames->setRACodigo($rs_row['ug_ra_codigo']);
            $usuarioGames->setRAOutros($rs_row['ug_ra_outros']);

            $usuarioGames->setContato01TelDDI($rs_row['ug_contato01_tel_ddi']);
            $usuarioGames->setContato01TelDDD($rs_row['ug_contato01_tel_ddd']);
            $usuarioGames->setContato01Tel($rs_row['ug_contato01_tel']);
            $usuarioGames->setContato01Nome($rs_row['ug_contato01_nome']);
            $usuarioGames->setContato01Cargo($rs_row['ug_contato01_cargo']);

            $sql = "SELECT to_char(ugo_data,'DD/MM/YYYY HH24:MI:SS') as data,* FROM dist_usuarios_games_obs WHERE ug_id = ".$rs_row['ug_id']." order by ugo_data ASC;";
            $rs_usuario_obs = SQLexecuteQuery($sql);
            $ug_obs = NULL;
            if(pg_num_rows($rs_usuario_obs) > 0) { 
                    while($rs_usuario_obs_row = pg_fetch_array($rs_usuario_obs)) {
                        $ug_obs .= "Em ".$rs_usuario_obs_row['data'].PHP_EOL."Autor: ".$rs_usuario_obs_row['ugo_user_insert'].PHP_EOL."Observação:".PHP_EOL.$rs_usuario_obs_row['ug_obs'].PHP_EOL.str_repeat("-",40).PHP_EOL;
                    }//end while
            } //end if(pg_num_rows($rs_usuario) > 0)
            $usuarioGames->setObservacoes($ug_obs);

			$usuarioGames->setCanaisVenda($rs_row['ug_canais_venda']);

            $usuarioGames->setRiscoClassif($rs_row['ug_risco_classif']);

            $usuarioGames->setTipoCadastro($rs_row['ug_tipo_cadastro']);
            $usuarioGames->setNome($rs_row['ug_nome']);
            $usuarioGames->setCPF($rs_row['ug_cpf']);
            $usuarioGames->setRG($rs_row['ug_rg']);
            if ($rs_row['ug_data_nascimento'] != "") {
                $usuarioGames->setDataNascimento(formata_data_ts($rs_row['ug_data_nascimento'], 0, true, true));
            }
            else {
                $usuarioGames->setDataNascimento($rs_row['ug_data_nascimento']);
            }
            $usuarioGames->setSexo($rs_row['ug_sexo']);

            $usuarioGames->setPerfilSenhaReimpressao($rs_row['ug_perfil_senha_reimpressao']);
            $usuarioGames->setPerfilFormaPagto($rs_row['ug_perfil_forma_pagto']);
            $usuarioGames->setPerfilLimite($rs_row['ug_perfil_limite']);
            $usuarioGames->setPerfilLimiteRef($rs_row['ug_perfil_limite_ref']);
            $usuarioGames->setPerfilSaldo($rs_row['ug_perfil_saldo']);

            $usuarioGames->setPerfilCorteDiaSemana($rs_row['ug_perfil_corte_dia_semana']);
            $usuarioGames->setPerfilCorteUltimoCorte($rs_row['ug_perfil_corte_ultimo_corte']);
            $usuarioGames->setPerfilLimiteSugerido($rs_row['ug_perfil_limite_sugerido']);
            $usuarioGames->setCreditoPendente($rs_row['ug_credito_pendente']);

            $usuarioGames->setInscrEstadual($rs_row['ug_inscr_estadual']);
            $usuarioGames->setSite($rs_row['ug_site']);
            $usuarioGames->setAberturaAno($rs_row['ug_abertura_ano']);
            $usuarioGames->setAberturaMes($rs_row['ug_abertura_mes']);
            $usuarioGames->setCartoes($rs_row['ug_cartoes']);
            $usuarioGames->setFaturaMediaMensal($rs_row['ug_fatura_media_mensal']);

            $usuarioGames->setReprLegalNome($rs_row['ug_repr_legal_nome']);
            $usuarioGames->setReprLegalRG($rs_row['ug_repr_legal_rg']);
            $usuarioGames->setReprLegalCPF($rs_row['ug_repr_legal_cpf']);
            $usuarioGames->setReprLegalTelDDI($rs_row['ug_repr_legal_tel_ddi']);
            $usuarioGames->setReprLegalTelDDD($rs_row['ug_repr_legal_tel_ddd']);
            $usuarioGames->setReprLegalTel($rs_row['ug_repr_legal_tel']);
            $usuarioGames->setReprLegalCelDDI($rs_row['ug_repr_legal_cel_ddi']);
            $usuarioGames->setReprLegalCelDDD($rs_row['ug_repr_legal_cel_ddd']);
            $usuarioGames->setReprLegalCel($rs_row['ug_repr_legal_cel']);
            $usuarioGames->setReprLegalEmail($rs_row['ug_repr_legal_email']);
            $usuarioGames->setReprLegalMSN($rs_row['ug_repr_legal_msn']);
            
            if($rs_row['ug_repr_legal_data_nascimento'] != ""){
                $usuarioGames->setReprLegalDataNascimento(formata_data_ts($rs_row['ug_repr_legal_data_nascimento'], 0, true, false));
            } else{
                $usuarioGames->setReprLegalDataNascimento($rs_row['ug_repr_legal_data_nascimento']);
            }
            
            $usuarioGames->setReprVendaIgualReprLegal($rs_row['ug_repr_venda_igual_repr_legal']);
            $usuarioGames->setReprVendaNome($rs_row['ug_repr_venda_nome']);
            $usuarioGames->setReprVendaRG($rs_row['ug_repr_venda_rg']);
            $usuarioGames->setReprVendaCPF($rs_row['ug_repr_venda_cpf']);
            $usuarioGames->setReprVendaTelDDI($rs_row['ug_repr_venda_tel_ddi']);
            $usuarioGames->setReprVendaTelDDD($rs_row['ug_repr_venda_tel_ddd']);
            $usuarioGames->setReprVendaTel($rs_row['ug_repr_venda_tel']);
            $usuarioGames->setReprVendaCelDDI($rs_row['ug_repr_venda_cel_ddi']);
            $usuarioGames->setReprVendaCelDDD($rs_row['ug_repr_venda_cel_ddd']);
            $usuarioGames->setReprVendaCel($rs_row['ug_repr_venda_cel']);
            $usuarioGames->setReprVendaEmail($rs_row['ug_repr_venda_email']);
            $usuarioGames->setReprVendaMSN($rs_row['ug_repr_venda_msn']);

            $usuarioGames->setDadosBancarios01Banco($rs_row['ug_dados_bancarios_01_banco']);
            $usuarioGames->setDadosBancarios01Agencia($rs_row['ug_dados_bancarios_01_agencia']);
            $usuarioGames->setDadosBancarios01Conta($rs_row['ug_dados_bancarios_01_conta']);
            $usuarioGames->setDadosBancarios01Abertura($rs_row['ug_dados_bancarios_01_abertura']);

            $usuarioGames->setDadosBancarios02Banco($rs_row['ug_dados_bancarios_02_banco']);
            $usuarioGames->setDadosBancarios02Agencia($rs_row['ug_dados_bancarios_02_agencia']);
            $usuarioGames->setDadosBancarios02Conta($rs_row['ug_dados_bancarios_02_conta']);
            $usuarioGames->setDadosBancarios02Abertura($rs_row['ug_dados_bancarios_02_abertura']);

            $usuarioGames->setComputadoresQtde($rs_row['ug_computadores_qtde']);
            $usuarioGames->setComunicacaoVisual($rs_row['ug_comunicacao_visual']);

            $usuarioGames->setFicouSabendo($rs_row['ug_ficou_sabendo']);

            $usuarioGames->setCompet_participa($rs_row['ug_compet_participa']);
            $usuarioGames->setCompet_promoveu($rs_row['ug_compet_promoveu']);
            $usuarioGames->setCompet_participantes_fifa($rs_row['ug_compet_participantes_fifa']);
            $usuarioGames->setCompet_participantes_wc3($rs_row['ug_compet_participantes_wc3']);

            $usuarioGames->setUgOngame($rs_row['ug_ongame']);

            $usuarioGames->setTipoEstabelecimento($rs_row['ug_te_id']);

            $usuarioGames->setUgIdNexCafe($rs_row['ug_id_nexcafe']);
            $usuarioGames->setUgLoginNexCafeAuto($rs_row['ug_login_nexcafe_auto']);
            $usuarioGames->setUgDataInclusaoNexCafe($rs_row['ug_data_inclusao_nexcafe']);

            $usuarioGames->setAlteraSenha($rs_row['ug_alterar_senha']);

            $usuarioGames->setExibirContrato($rs_row['ug_exibir_contrato']);

            $usuarioGames->setDataAceite($rs_row['ug_data_aceite_adesao']);

            $usuarioGames->setRecargaCelular($rs_row['ug_recarga_celular']);

            $usuarioGames->setVIP($rs_row['ug_vip']);

            $usuarioGames->setPossuiRestricaoProdutos($rs_row['ug_possui_restricao_produtos']);

            $usuarioGames->setTipoVenda($rs_row['ug_tipo_venda']);

            $usuarioGames->setDataAprovacao(($rs_row['ug_data_aprovacao']!=""?formata_data_ts($rs_row['ug_data_aprovacao'], 0, true, true):""));
            
            $usuarioGames->setDataExpiraSenha(($rs_row['ug_data_expiracao_senha']!=""?formata_data_ts($rs_row['ug_data_expiracao_senha'], 0, true, true):""));

        }

        return $usuarioGames;
    }

    function adicionarLoginSession_ByID($ug_id) {

        if(!$ug_id || $ug_id == "") return false;

        $rs = null;
        $filtro['ug_id'] = $ug_id;
        $filtro['ug_ativo'] = 1;
        $filtro['ug_substatus'] = 11;
        $ret = UsuarioGames::obter($filtro, null, $rs);
        if ($rs && pg_num_rows($rs) > 0) {
            $ret = true;
            $rs_row = pg_fetch_array($rs);
            $usuarioGames = new UsuarioGames();

            $usuarioGames->setId($rs_row['ug_id']);
            $usuarioGames->setLogin($rs_row['ug_login']);
            // $usuarioGames->setSenha($rs_row['ug_senha']);
            $usuarioGames->setAtivo($rs_row['ug_ativo']);
            $usuarioGames->setStatusBusca($rs_row['ug_status']);
            $usuarioGames->setSubstatus($rs_row['ug_substatus']);
            $usuarioGames->setDataInclusao(formata_data_ts($rs_row['ug_data_inclusao'], 0, true, false));
            $usuarioGames->setDataUltimoAcesso(formata_data_ts($rs_row['ug_data_ultimo_acesso'], 0, true, false));
            $usuarioGames->setQtdeAcessos($rs_row['ug_qtde_acessos']);

            $usuarioGames->setNomeFantasia($rs_row['ug_nome_fantasia']);
            $usuarioGames->setRazaoSocial($rs_row['ug_razao_social']);
            $usuarioGames->setCNPJ($rs_row['ug_cnpj']);
            $usuarioGames->setResponsavel($rs_row['ug_responsavel']);
            $usuarioGames->setEmail($rs_row['ug_email']);

            $usuarioGames->setEndereco($rs_row['ug_endereco']);
            $usuarioGames->setTipoEnd($rs_row['ug_tipo_end']);
            $usuarioGames->setNumero($rs_row['ug_numero']);
            $usuarioGames->setComplemento($rs_row['ug_complemento']);
            $usuarioGames->setBairro($rs_row['ug_bairro']);
            $usuarioGames->setCidade($rs_row['ug_cidade']);
            $usuarioGames->setEstado($rs_row['ug_estado']);
            $usuarioGames->setCEP($rs_row['ug_cep']);

            $usuarioGames->setTelDDI($rs_row['ug_tel_ddi']);
            $usuarioGames->setTelDDD($rs_row['ug_tel_ddd']);
            $usuarioGames->setTel($rs_row['ug_tel']);
            $usuarioGames->setCelDDI($rs_row['ug_cel_ddi']);
            $usuarioGames->setCelDDD($rs_row['ug_cel_ddd']);
            $usuarioGames->setCel($rs_row['ug_cel']);
            $usuarioGames->setFaxDDI($rs_row['ug_fax_ddi']);
            $usuarioGames->setFaxDDD($rs_row['ug_fax_ddd']);
            $usuarioGames->setFax($rs_row['ug_fax']);

            $usuarioGames->setRACodigo($rs_row['ug_ra_codigo']);
            $usuarioGames->setRAOutros($rs_row['ug_ra_outros']);

            $usuarioGames->setContato01TelDDI($rs_row['ug_contato01_tel_ddi']);
            $usuarioGames->setContato01TelDDD($rs_row['ug_contato01_tel_ddd']);
            $usuarioGames->setContato01Tel($rs_row['ug_contato01_tel']);
            $usuarioGames->setContato01Nome($rs_row['ug_contato01_nome']);
            $usuarioGames->setContato01Cargo($rs_row['ug_contato01_cargo']);

            //Buscando Informações de observações
            // $sql = "SELECT to_char(ugo_data,'DD/MM/YYYY HH24:MI:SS') as data,* FROM dist_usuarios_games_obs WHERE ug_id = ".$rs_row['ug_id']." order by ugo_data ASC;";
            // $rs_usuario_obs = SQLexecuteQuery($sql);
            // $ug_obs = NULL;
            // if(pg_num_rows($rs_usuario_obs) > 0) { 
            //         while($rs_usuario_obs_row = pg_fetch_array($rs_usuario_obs)) {
            //             $ug_obs .= "Em ".$rs_usuario_obs_row['data'].PHP_EOL."Autor: ".$rs_usuario_obs_row['ugo_user_insert'].PHP_EOL."Observação:".PHP_EOL.$rs_usuario_obs_row['ug_obs'].PHP_EOL.str_repeat("-",40).PHP_EOL;
            //         }//end while
            // } //end if(pg_num_rows($rs_usuario) > 0)
				
            // $usuarioGames->setObservacoes($ug_obs);

			$usuarioGames->setCanaisVenda($rs_row['ug_canais_venda']);

            $usuarioGames->setRiscoClassif($rs_row['ug_risco_classif']);

            $usuarioGames->setTipoCadastro($rs_row['ug_tipo_cadastro']);
            $usuarioGames->setNome($rs_row['ug_nome']);
            $usuarioGames->setCPF($rs_row['ug_cpf']);
            $usuarioGames->setRG($rs_row['ug_rg']);
            $usuarioGames->setDataNascimento(formata_data_ts($rs_row['ug_data_nascimento'], 0, true, true));
            $usuarioGames->setSexo($rs_row['ug_sexo']);

            $usuarioGames->setPerfilSenhaReimpressao($rs_row['ug_perfil_senha_reimpressao']);
            $usuarioGames->setPerfilFormaPagto($rs_row['ug_perfil_forma_pagto']);
            $usuarioGames->setPerfilLimite($rs_row['ug_perfil_limite']);
            $usuarioGames->setPerfilLimiteRef($rs_row['ug_perfil_limite_ref']);
            $usuarioGames->setPerfilSaldo($rs_row['ug_perfil_saldo']);

            $usuarioGames->setPerfilCorteDiaSemana($rs_row['ug_perfil_corte_dia_semana']);
            $usuarioGames->setPerfilCorteUltimoCorte($rs_row['ug_perfil_corte_ultimo_corte']);
            $usuarioGames->setPerfilLimiteSugerido($rs_row['ug_perfil_limite_sugerido']);
            $usuarioGames->setCreditoPendente($rs_row['ug_credito_pendente']);

            $usuarioGames->setInscrEstadual($rs_row['ug_inscr_estadual']);
            $usuarioGames->setSite($rs_row['ug_site']);
            $usuarioGames->setAberturaAno($rs_row['ug_abertura_ano']);
            $usuarioGames->setAberturaMes($rs_row['ug_abertura_mes']);
            $usuarioGames->setCartoes($rs_row['ug_cartoes']);
            $usuarioGames->setFaturaMediaMensal($rs_row['ug_fatura_media_mensal']);

            $usuarioGames->setReprLegalNome($rs_row['ug_repr_legal_nome']);
            $usuarioGames->setReprLegalRG($rs_row['ug_repr_legal_rg']);
            $usuarioGames->setReprLegalCPF($rs_row['ug_repr_legal_cpf']);
            $usuarioGames->setReprLegalTelDDI($rs_row['ug_repr_legal_tel_ddi']);
            $usuarioGames->setReprLegalTelDDD($rs_row['ug_repr_legal_tel_ddd']);
            $usuarioGames->setReprLegalTel($rs_row['ug_repr_legal_tel']);
            $usuarioGames->setReprLegalCelDDI($rs_row['ug_repr_legal_cel_ddi']);
            $usuarioGames->setReprLegalCelDDD($rs_row['ug_repr_legal_cel_ddd']);
            $usuarioGames->setReprLegalCel($rs_row['ug_repr_legal_cel']);
            $usuarioGames->setReprLegalEmail($rs_row['ug_repr_legal_email']);
            $usuarioGames->setReprLegalMSN($rs_row['ug_repr_legal_msn']);

            $usuarioGames->setReprVendaIgualReprLegal($rs_row['ug_repr_venda_igual_repr_legal']);
            $usuarioGames->setReprVendaNome($rs_row['ug_repr_venda_nome']);
            $usuarioGames->setReprVendaRG($rs_row['ug_repr_venda_rg']);
            $usuarioGames->setReprVendaCPF($rs_row['ug_repr_venda_cpf']);
            $usuarioGames->setReprVendaTelDDI($rs_row['ug_repr_venda_tel_ddi']);
            $usuarioGames->setReprVendaTelDDD($rs_row['ug_repr_venda_tel_ddd']);
            $usuarioGames->setReprVendaTel($rs_row['ug_repr_venda_tel']);
            $usuarioGames->setReprVendaCelDDI($rs_row['ug_repr_venda_cel_ddi']);
            $usuarioGames->setReprVendaCelDDD($rs_row['ug_repr_venda_cel_ddd']);
            $usuarioGames->setReprVendaCel($rs_row['ug_repr_venda_cel']);
            $usuarioGames->setReprVendaEmail($rs_row['ug_repr_venda_email']);
            $usuarioGames->setReprVendaMSN($rs_row['ug_repr_venda_msn']);

            $usuarioGames->setDadosBancarios01Banco($rs_row['ug_dados_bancarios_01_banco']);
            $usuarioGames->setDadosBancarios01Agencia($rs_row['ug_dados_bancarios_01_agencia']);
            $usuarioGames->setDadosBancarios01Conta($rs_row['ug_dados_bancarios_01_conta']);
            $usuarioGames->setDadosBancarios01Abertura($rs_row['ug_dados_bancarios_01_abertura']);

            $usuarioGames->setDadosBancarios02Banco($rs_row['ug_dados_bancarios_02_banco']);
            $usuarioGames->setDadosBancarios02Agencia($rs_row['ug_dados_bancarios_02_agencia']);
            $usuarioGames->setDadosBancarios02Conta($rs_row['ug_dados_bancarios_02_conta']);
            $usuarioGames->setDadosBancarios02Abertura($rs_row['ug_dados_bancarios_02_abertura']);

            $usuarioGames->setComputadoresQtde($rs_row['ug_computadores_qtde']);
            $usuarioGames->setComunicacaoVisual($rs_row['ug_comunicacao_visual']);

            $usuarioGames->setFicouSabendo($rs_row['ug_ficou_sabendo']);

            $usuarioGames->setCompet_participa($rs_row['ug_compet_participa']);
            $usuarioGames->setCompet_promoveu($rs_row['ug_compet_promoveu']);
            $usuarioGames->setCompet_participantes_fifa($rs_row['ug_compet_participantes_fifa']);
            $usuarioGames->setCompet_participantes_wc3($rs_row['ug_compet_participantes_wc3']);

            $usuarioGames->setUgOngame($rs_row['ug_ongame']);

            $usuarioGames->setTipoEstabelecimento($rs_row['ug_te_id']);

            // NexCafe
            $usuarioGames->setUgIdNexCafe($rs_row['ug_id_nexcafe']);
            $usuarioGames->setUgLoginNexCafeAuto($rs_row['ug_login_nexcafe_auto']);
            $usuarioGames->setUgDataInclusaoNexCafe($rs_row['ug_data_inclusao_nexcafe']);

            $usuarioGames->setAlteraSenha($rs_row['ug_alterar_senha']);

            $usuarioGames->setExibirContrato($rs_row['ug_exibir_contrato']);

            $usuarioGames->setDataAceite($rs_row['ug_data_aceite_adesao']);

            $usuarioGames->setRecargaCelular($rs_row['ug_recarga_celular']);

            $usuarioGames->setVIP($rs_row['ug_vip']);
            
            $usuarioGames->setPossuiRestricaoProdutos($rs_row['ug_possui_restricao_produtos']);
            
            $usuarioGames->setTipoVenda($rs_row['ug_tipo_venda']);
            $usuarioGames->setCanaisVenda($rs_row['ug_canais_venda']);
			
            //Poe no session
            $_SESSION['dist_usuarioGames_ser'] = serialize($usuarioGames);
            $_SESSION['dist_usuarioGames.horarioLogin'] = date("U");
            $_SESSION['dist_usuarioGames.horarioInatividade'] = date("U");
        } else {
            $ret = false;
        }

        return $ret;
    }
    
    function alterarAcesso($attr, $senha, $id){
        
        $con = ConnectionPDO::getConnection();
        if ( !$con->isConnected() ) {
            // retornar os erros: $con->getErrors();
            die('Erro#2');
        }
        $pdo = $con->getLink();
        
        //encripta senha
        $objEncryption = new Encryption();
        $senha = $objEncryption->encrypt(trim($senha));
        //autentica o usuário
        $sql = "select count(*) from  dist_usuarios_games where ug_id = ? and ug_senha = ?";
        
        $paransValida = array(
            filter_var($id,FILTER_SANITIZE_NUMBER_INT),
            filter_var($senha,FILTER_SANITIZE_STRING)
        );
        
        $stmtAutentica = $pdo->prepare($sql);
        $stmtAutentica->execute($paransValida);
        $autenticado = $stmtAutentica->fetchAll(PDO::FETCH_OBJ);
        
        if($autenticado[0]->count > 0){
            //usuário autenticado
            if($attr['campo'] == "login"){
                $param = filter_var(strtoupper($attr['value']),FILTER_SANITIZE_STRING);
                $selectCond = "upper(ug_login) = upper(:param)";
                $updateCont = "ug_login = upper(:param)";
                $tpl = "AlteracaoLoginLH";
                $strEval  = '$objEnvioEmailAutomatico->setUgLogin("'.strtoupper($attr['value']).'"); ';
                $strEval .= '$objEnvioEmailAutomatico->setUgLoginAntigo("'.$attr['loginAntigo'].'"); ';
                $strEval .= '$objEnvioEmailAutomatico->setUgNome("'.$attr['nome'].'");';
                $strEval .= '$objEnvioEmailAutomatico->setUgEmail("'.$attr['email'].'"); ';
                
            }else if($attr['campo'] == "email"){
                $param = filter_var($attr['value'],FILTER_SANITIZE_EMAIL);
                $selectCond = "upper(ug_email) = upper(:param)";
                $updateCont = "ug_email = upper(:param)";
                $tpl = "AlteracaoCadastroLH";
                $strEval  = '$objEnvioEmailAutomatico->setUgEmailNovo("'.$attr['value'].'"); ';
                $strEval .= '$objEnvioEmailAutomatico->setUgEmail("'.$attr['emailAntigo'].'"); ';
                $strEval .= '$objEnvioEmailAutomatico->setUgNome("'.$attr['nome'].'");';
            }

            $params = array();
            $params[':param'] = $param;
            $params[':id'] = $id;
            
            $sql = "select count(*) from dist_usuarios_games where $selectCond and ug_id != :id";
            //valida se o login está disponível
            $verificaParamExistente = $pdo->prepare($sql);
            $verificaParamExistente->execute($params);

            $validaLoginDisponivel = $verificaParamExistente->fetchAll(PDO::FETCH_OBJ);

            if($validaLoginDisponivel[0]->count > 0){
                //login já existe
                $retorno['msg'] = "'{$attr['value']}' já está cadastrado em nosso sistema.";
                $retorno['sucesso'] = 0;
            }else{
                
                $params[':pass'] = $senha;
                //insere
                $update = "update dist_usuarios_games set $updateCont where ug_id = :id and ug_senha = :pass and (select count(*) from dist_usuarios_games where $selectCond) < 1";
                $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,$tpl);			
                eval($strEval);
                echo $objEnvioEmailAutomatico->MontaEmailEspecifico();
                $stmtAtualiza = $pdo->prepare($update);
                $stmtAtualiza->execute($params);

                if($stmtAtualiza->rowCount() == 1){
                    $retorno['msg'] = "Dado alterado com sucesso, favor efetuar o login novamente.";
                    $retorno['sucesso'] = 1;
                                        
                }else{
                    $retorno['msg'] = "Erro. Entre em contato com o Administrador do Sistema.";
                    $retorno['sucesso'] = 0;
                }
            }
            
        }else{
            //usuario errou a senha
            $retorno['msg'] = "Senha incorreta.";
            $retorno['sucesso'] = 0;
        }
        
        return $retorno;
        
    }
    
    function alterarSenha($senha, $senhaAtual, $login) {
        
        global $raiz_do_projeto;
        
        $ret = false;

        //Autentica usuario
        //------------------------------------------------------------------
        $objEncryption = new Encryption();
        $senha = $objEncryption->encrypt(trim($senha));
        $senhaAtual = $objEncryption->encrypt(trim($senhaAtual));
        $login = strtoupper(trim($login));

        //SQL
        $sql = "select count(*) as qtde from dist_usuarios_games ";
        $sql .= " where ug_login = " . SQLaddFields($login, "s");
        $sql .= " and ug_senha = " . SQLaddFields($senhaAtual, "s");

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            if($rs_row['qtde'] > 0) $ret = true;
        }

        //Atualiza senha
        //------------------------------------------------------------------
        if ($ret) {
            //SQL
            $sql = "update dist_usuarios_games set ";
            $sql .= " ug_senha = " . SQLaddFields($senha, "s");
            $sql .= ", ug_alterar_senha = 0";
            $sql .= ", ug_data_expiracao_senha = null";
            $sql .= " where ug_login = " . SQLaddFields($login, "s");
            $sql .= " and ug_senha = " . SQLaddFields($senhaAtual, "s");
            //update da data de expiracao aqui
            $ret = SQLexecuteQuery($sql);

            if ($ret) {
                //Log na base de dados
                usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['TROCA_DE_SENHA'], null, null, "Troca de senha pelo pdv");

                // Nova forma de enviar e-mail
                $objGamesUsuario = unserialize($_SESSION['dist_usuarioGames_ser']);
                $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN, 'AlteracaoSenhaLH');
                $envioEmail->setUgID($objGamesUsuario->ug_id);


                $envioEmail->MontaEmailEspecifico();

                //Grava no arquivo o ID do PDV para Exclusão de todas as Sessões abertas
                $nome_tmp = $raiz_do_projeto.'log/idsPDVs.txt';
                if ($handle = fopen($nome_tmp, 'a+')) {
                        fwrite($handle, $objGamesUsuario->ug_id.PHP_EOL);
                        fclose($handle);
                }//end if ($handle = fopen($nome_tmp, 'a+'))
                
            }
        }

        return $ret;
    }

    function alterarCadastro($senha0, $login0, $a_campos) {

        $ret = false;

        //Autentica usuario
        //------------------------------------------------------------------
        $objEncryption = new Encryption();
        $senha = $senha0; //$objEncryption->encrypt(trim($senha0));
        $login = strtoupper(trim($login0));

        //SQL
        $sql = "select count(*) as qtde, ug_email from dist_usuarios_games ";
        $sql .= " where ug_login = " . SQLaddFields($login0, "s");
        $sql .= " and ug_senha = " . SQLaddFields($senha, "s");
        $sql .= " group by ug_email";

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            if ($rs_row['qtde'] > 0) {
                $ret = true;
                // Salva o email atual
                $email_prev = $rs_row['ug_email'];
            }
        }

        //------------------------------------------------------------------
        if ($ret) {

            // Init
            $ret = "";
            $sql_update = "";
            $msg_modificacoes = "";

            // Valida email
            $email = trim($a_campos['email']);
            if (!is_null($email)) {
                if(is_null($email) || $email == "") $ret .= "O Email deve ser preenchido.".PHP_EOL;
                elseif(strlen($email) > 100)         $ret .= "O Email deve ter até 100 caracteres.".PHP_EOL;
                elseif(!verifica_email2($email))     $ret .= "O Email é inválido.".PHP_EOL;
            }
            if (!$ret) {
                $sql_update .= " ug_email = " . SQLaddFields($email, "s");
                $msg_modificacoes = "Email atualizado de '" . strtoupper($email_prev) . "' para '" . strtoupper($email) . "'";
            }

            //Atualiza cadastro
            if (!$ret) {
                $sql = "update dist_usuarios_games set ";
                $sql .= $sql_update;
                $sql .= " where ug_login = " . SQLaddFields($login0, "s");
                $sql .= " and ug_senha = " . SQLaddFields($senha, "s");
                $ret = SQLexecuteQuery($sql);

                // Atualiza o SESSION
                if ($ret) {
                    $rs = null;
                    $filtro['ug_login'] = $login;
                    $filtro['ug_ativo'] = 1;
                    $filtro['ug_substatus'] = 11;
                    $ret = UsuarioGames::obter($filtro, null, $rs);

                    $usuarioGames1 = UsuarioGames::create($rs);

                    if ($usuarioGames1 != null) {
                        //Poe no session                
                        $GLOBALS['_SESSION']['dist_usuarioGames_ser'] = serialize($usuarioGames1);
                    } else {
                        $msg = false;
                    }
                }

                if (!$ret) {

                    //Log na base
                    usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['ALTERACAO_DO_CADASTRO'], null, null);

                    //Envia email
                    //--------------------------------------------------------------------------------
                    $objGamesUsuario = unserialize($_SESSION['dist_usuarioGames_ser']);
                    $parametros['prepag_dominio'] = "" . EPREPAG_URL_HTTP . "";
                    $parametros['nome_fantasia'] = $objGamesUsuario->getNomefantasia();
                    $parametros['tipo_cadastro'] = $objGamesUsuario->getTipoCadastro();
                    $parametros['nome'] = $objGamesUsuario->getNome();
                    $parametros['sexo'] = $objGamesUsuario->getSexo();

                    $msgEmail = email_cabecalho($parametros);
                    $msgEmail .= "  <br><br>
                                    <table border='0' cellspacing='0'>
                                    <tr><td>&nbsp;</td></tr>
                                    <tr valign='middle' bgcolor='#FFFFFF'>
                                        <td align='left' class='texto'>
                                            Você acessou nosso site e alterou seu cadastro (" . $msg_modificacoes . ").<br><br>
                                            Utilize seu login " . $objGamesUsuario->getLogin() . " para acessar sua conta e realizar compras em nosso site.<br><br>
                                        </td>
                                    </tr>
                                    <tr><td>&nbsp;</td></tr>
                                    </table>
                                ";
                    $msgEmail .= email_rodape($parametros);
                    $cc = null;
                    $bcc = $email_prev;
                    enviaEmail($objGamesUsuario->getEmail(), $cc, $bcc, "E-Prepag - Alteração de Cadastro", $msgEmail);
                    
                }//end if (!$ret)
            }//end if (!$ret)
        } //end if ($ret)
        return $ret;
    }

    function b_IsLogin_pagamento_normal($op = null, &$a_logins = null) {

        if ($op == 1) {
            $a_logins = array();
            $sql = "select ug_id from dist_usuarios_games where ug_vip = 0 order by ug_id;";
            $rs_usuarios = SQLexecuteQuery($sql);
            while ($rs_usuarios_row = pg_fetch_array($rs_usuarios)) {
                $a_logins[] = $rs_usuarios_row['ug_id'];
            }//ens while
            $aret = $a_logins;
            return false;
        }//end if($op == 1)
        else {
            if ($this->getVIP() == 0)
                return true;
                        else return false;
        }//end else do if($op == 1)
        
    }//end function b_IsLogin_pagamento_normal

    function b_IsLogin_pagamento_vip($op = null, &$a_logins = null) {

        if ($op == 1) {
            $a_logins = array();
            $sql = "select ug_id from dist_usuarios_games where ug_vip = 1 order by ug_id;";
            $rs_usuarios = SQLexecuteQuery($sql);
            while ($rs_usuarios_row = pg_fetch_array($rs_usuarios)) {
                $a_logins[] = $rs_usuarios_row['ug_id'];
            }//ens while
            $aret = $a_logins;
            return false;
        }//end if($op == 1)
        else {
            if ($this->getVIP() == 1)
                return true;
                        else return false;
        }//end else do if($op == 1)
        
    }//end function b_IsLogin_pagamento_vip

    function b_IsLogin_pagamento_master($op = null, &$a_logins = null) {

        if ($op == 1) {
            $a_logins = array();
            $sql = "select ug_id from dist_usuarios_games where ug_vip = 2 order by ug_id;";
            $rs_usuarios = SQLexecuteQuery($sql);
            while ($rs_usuarios_row = pg_fetch_array($rs_usuarios)) {
                $a_logins[] = $rs_usuarios_row['ug_id'];
            }//ens while
            $aret = $a_logins;
            return false;
        }//end if($op == 1)
        else {
            if ($this->getVIP() == 2) {
                return true;
            }
                        else return false;
        }//end else do if($op == 1)
        
    }//end function b_IsLogin_pagamento_master

    function b_IsLogin_pagamento_black($op = null, &$a_logins = null) {

        if ($op == 1) {
            $a_logins = array();
            $sql = "select ug_id from dist_usuarios_games where ug_vip = 3 order by ug_id;";
            $rs_usuarios = SQLexecuteQuery($sql);
            while ($rs_usuarios_row = pg_fetch_array($rs_usuarios)) {
                $a_logins[] = $rs_usuarios_row['ug_id'];
            }//ens while
            $aret = $a_logins;
            return false;
        }//end if($op == 1)
        else {
            if ($this->getVIP() == 3) {
                return true;
            }
                        else return false;
        }//end else do if($op == 1)
        
    }//end function b_IsLogin_pagamento_black

    function b_IsLogin_pagamento_gold($op = null, &$a_logins = null) {

        if ($op == 1) {
            $a_logins = array();
            $sql = "select ug_id from dist_usuarios_games where ug_vip = 4 order by ug_id;";
            $rs_usuarios = SQLexecuteQuery($sql);
            while ($rs_usuarios_row = pg_fetch_array($rs_usuarios)) {
                $a_logins[] = $rs_usuarios_row['ug_id'];
            }//ens while
            $aret = $a_logins;
            return false;
        }//end if($op == 1)
        else {
            if ($this->getVIP() == 4) {
                return true;
            }
                        else return false;
        }//end else do if($op == 1)
        
    }//end function b_IsLogin_pagamento_gold
	
	function b_IsLogin_pagamento_platinum($op = null, &$a_logins = null) {

        if ($op == 1) {
            $a_logins = array();
            $sql = "select ug_id from dist_usuarios_games where ug_vip = 5 order by ug_id;";
            $rs_usuarios = SQLexecuteQuery($sql);
            while ($rs_usuarios_row = pg_fetch_array($rs_usuarios)) {
                $a_logins[] = $rs_usuarios_row['ug_id'];
            }//ens while
            $aret = $a_logins;
            return false;
        }//end if($op == 1)
        else {
            if ($this->getVIP() == 5) {
                return true;
            }
                        else return false;
        }//end else do if($op == 1)
        
    }//end function b_IsLogin_pagamento_platinum

    function enviaEmailAtivacao($usuario_id) {

        $ret = "";

        $objEncryption = new Encryption();
        $objGamesUsuario = UsuarioGames::getUsuarioGamesById($usuario_id);

        if ($objGamesUsuario == null) {
            $ret = "Não foi possível enviar email de ativação de cadastro. Usuário não encontrado.".PHP_EOL;
            return $ret;
        }


        //Envia email
        //--------------------------------------------------------------------------------
        $parametros['prepag_dominio'] = "" . EPREPAG_URL_HTTP . "";
        $parametros['nome_fantasia'] = $objGamesUsuario->getNomefantasia();
        $parametros['tipo_cadastro'] = $objGamesUsuario->getTipoCadastro();
        $parametros['nome'] = $objGamesUsuario->getNome();
        $parametros['sexo'] = $objGamesUsuario->getSexo();

        $msgEmail = email_cabecalho($parametros);
        $msgEmail .= "  <br><br>
                        <table border='0' cellspacing='0'>
                        <tr><td>&nbsp;</td></tr>
                        <tr valign='middle' bgcolor='#FFFFFF'>
                            <td align='left' class='texto'>
                                Informamos que o seu cadastro junto ao E-PREPAG LanHouses foi aprovado.<br><br>
                                Para acessar a sua área de trabalho, utilize o login e senha que você criou no momento do cadastro.<br><br>
                                Na sua área de trabalho, dentre outras coisas, é possível fazer compras, acompanhar a situação os seus pedidos e fazer a impressão dos cupons.<br><br>
                                Acesse agora mesmo e já faça a sua primeira compra, é fácil e rápido!<br><br>
                                <b>Login:</b> " . $objGamesUsuario->getLogin() . "<br>
                                Assista os vídeos de instrução. Clique nos links abaixo:<br><br>
                                <a href=\"" . EPREPAG_URL_HTTP . "/prepag2/dist_commerce/conta/v/comocolocarcreditos.wmv\" target=\"_blank\" style=\"text-decoration:none;\"><u>Vídeo 1: Como colocar créditos. (duração: 1:44)</u></a><br>
                                <a href=\"" . EPREPAG_URL_HTTP . "/prepag2/dist_commerce/conta/v/comoganhocomissao.wmv\" target=\"_blank\" style=\"text-decoration:none;\"><u>Vídeo 2: Como ganho comissão. (duração: 1:14)</u></a><br>
                                <a href=\"" . EPREPAG_URL_HTTP . "/prepag2/dist_commerce/conta/v/comorealizarvendas.wmv\" target=\"_blank\" style=\"text-decoration:none;\"><u>Vídeo 2: Como realizar vendas. (duração: 3:13)</u></a><br>
                            </td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                        </table>
                    ";
        $msgEmail .= email_rodape($parametros);
        enviaEmail($objGamesUsuario->getEmail(), null, null, "E-Prepag - Cadastro Aprovado", $msgEmail);

        return $ret;
    }

    function usuarioAdministrador() {

        $usuarios_administradores = array("TAMY123@E-PREPAG.COM.BR", "TAMY@E-PREPAG.COM.BR", "GLAUCIA-E-PREPAG@HOTMAIL.COM", "COMERCIAL@E-PREPAG.COM.BR", "WAGNER@E-PREPAG.COM.BR"); 

        if (in_array(strtoupper($this->getEmail()), $usuarios_administradores)) {
            return true;
        }
        return false;
    }

    // ================================================================
    function getSubstatusDescription() {
        global $SUBSTATUS_LH;

        $sret = "Substatus ???";
        if (array_key_exists($this->getSubstatus(), $SUBSTATUS_LH)) {
            $sret = $SUBSTATUS_LH[$this->getSubstatus()];
        } else {
            $sret = "Substatus não definido";
        }
        return $sret;
    }

    function b_IsLogin_reinaldolh() {
        if (strtoupper($this->getLogin()) == "REINALDOLH") {
            return true;
        }
        return false;
    }

    function b_IsLogin_teste_captura_ip() {
        if (strtoupper($this->getLogin()) == "WAGNER") {
            return true;
        }
        return false;
    }


    function b_IsLogin_email_ponto_venda($op = null, &$aret = null) {

        if ($op == 1) {
            $msg = "";
            $a_logins = array();
            $sql = "select ug_id from dist_usuarios_games where ug_ativo = 1 and ug_substatus = 11 order by ug_id;";
            $rs_usuarios = SQLexecuteQuery($sql);
            if(!$rs_usuarios || pg_num_rows($rs_usuarios) == 0) $msg = "Nenhum usuário encontrado (1ag).".PHP_EOL;

            if ($msg == "") {
                while ($rs_usuarios_row = pg_fetch_array($rs_usuarios)) {
                    $a_logins[] = $rs_usuarios_row['ug_id'];
                }
            }
            $aret = $a_logins;

        }

        //solicitado para ser liberado para todos em 15/3/2013
        return true;
    }

    function b_IsLogin_reinaldolh2() {
        if (strtoupper($this->getLogin()) == "WAGNER") {
            return true;
        }
        return false;
    }

    function b_IsLogin_tamlyn() {
        if (strtoupper($this->getLogin()) == "TAMLYN") {
            return true;
        }
        return false;
    }

    // Podem ver a seção "Competição" em "Minha LH"
    function b_IsLH_Campeonato_Permitidos() {
        return true;
        /*
          $a_IsLH_Competicao_Permitidos = array(
          "WAGNER", "ODECIO", "FABIO###", "PATROCLO1234", "PATROCLO123457",
          );
          if(in_array(strtoupper($this->getLogin()), $a_IsLH_Competicao_Permitidos)) {
          return true;
          }
          //        echo "".$this->getLogin()."<br>";
          return false;
         */
    }

    // Aceitaram paraticipar do campeonato
    function b_IsLH_Campeonato() {
        if (strtoupper($this->getCompet_participa()) == "S") {
            return true;
        }
        return false;
    }

    function atualizarCompet_participa($ug_id, $ug_compet_participa) {
        $sql = "update dist_usuarios_games set ";
        $sql .= " ug_compet_participa = " . SQLaddFields($ug_compet_participa, "s") . "";
        $sql .= " where ug_id = " . SQLaddFields($ug_id, "") . "";
        $ret = SQLexecuteQuery($sql);

        if ($ret) {
            return true;
        }
        return false;
    }

    function atualizarUgOngame($ug_id, $ug_ongame) {
        $sql = "update dist_usuarios_games set ";
        $sql .= " ug_ongame = " . SQLaddFields($ug_ongame, "s") . "";
        $sql .= " where ug_id = " . SQLaddFields($ug_id, "") . "";
        $ret = SQLexecuteQuery($sql);
        if ($ret) {
            return true;
        }
        return false;
    }

    function atualizarTipoEstabelecimento($ug_id, $ug_te_id) {
        $sql = "update dist_usuarios_games set ";
        $sql .= " ug_te_id = " . SQLaddFields($ug_te_id, "s") . "";
        $sql .= " where ug_id = " . SQLaddFields($ug_id, "") . "";
        $ret = SQLexecuteQuery($sql);
        if ($ret) {
            return true;
        }
        return false;
    }

    function existeLoginNexCafe($login_nxc) {
        $ret = true;
        $sql = "select count(*) as qtde from dist_usuarios_games ";
        $sql .= " where ug_id_nexcafe IS NOT NULL and ug_id_nexcafe <> '' and ug_id_nexcafe = " . SQLaddFields(strtoupper(trim($login_nxc)), "s");

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            if($rs_row['qtde'] == 0) $ret = false;
        }

        return $ret;
    }

    function iniciaSessaoNexCafe($loginNXC) {

        $usuarioGames = UsuarioGames::getUsuarioGamesByIdNexCafe(strtoupper(trim($loginNXC)));

        // Atualiza LOG de Operações pelo NexCafe
        //------------------------------------------------------------------
        if ($usuarioGames) {
            $sql = "update dist_usuarios_games set ";
            $sql .= " ug_data_ultimo_acesso = CURRENT_TIMESTAMP,";
            $sql .= " ug_qtde_acessos = ug_qtde_acessos + 1 ";
            $sql .= " where ug_login = " . SQLaddFields($login, "s");
            $rs = SQLexecuteQuery($sql);

            usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CARREGA_SESSAO_NEXCAFE'], $usuarioGames->getId(), null);
        }

        return $usuarioGames;
    }


    function registraDadosNexCafe($ug_id, $login_nexcafe, $loginAutomatico = 0) {

        $ret = false;
        $dataRegistro = "CURRENT_TIMESTAMP";
        $loginAutomatico = $loginAutomatico ? 1 : 'DEFAULT';

        $sql = "update dist_usuarios_games set ";
        $sql .= " ug_data_inclusao_nexcafe = " . SQLaddFields($dataRegistro, "") . ",";
        $sql .= " ug_id_nexcafe = " . SQLaddFields(strtoupper($login_nexcafe), "s") . ",";
        $sql .= " ug_login_nexcafe_auto = " . SQLaddFields($loginAutomatico, "");
        $sql .= " where ug_id = " . SQLaddFields($ug_id, "");
        $rs = SQLexecuteQuery($sql);

        usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['UPDATE_REGISTRO_NEXCAFE'], $ug_id, null);

        if ($rs != null) {
            $ret = true;
        }

        return $ret;
    }

    function getUsuarioGamesByIdNexCafe($ug_id_nexcafe) {

        $rs = null;
        $filtro['ug_id_nexcafe'] = strtoupper($ug_id_nexcafe);
        $filtro['ug_ativo'] = 1;
        $filtro['ug_substatus'] = 11;

        $ret = UsuarioGames::obter($filtro, null, $rs);

        return UsuarioGames::create($rs);
        
    }


    function b_IsLogin_pagamento() {
        // Libera para todos os usuários
        return true;
    }

    function b_IsLogin_pagamento_minimo_1_real() {

        $aIsLogin_pagamento_mini_1_real = array(
            "WAGNER", "GLAUCIAPJ", "CAMPEONATOS", "TAMYOP", "TAMLYN",
        );
        if (in_array(strtoupper($this->getLogin()), $aIsLogin_pagamento_mini_1_real)) {
            return true;
        }
        return false;
    }

    function b_IsLogin_lista_extrato($op = null, &$aret = null) {
        
        if ($op == 1) {
            $msg = "";
            $a_logins = array();
            $sql = "select ug_login from dist_usuarios_games where ug_ativo = 1 and ug_substatus = 11 order by ug_login;";
            $rs_usuarios = SQLexecuteQuery($sql);
            if(!$rs_usuarios || pg_num_rows($rs_usuarios) == 0) $msg = "Nenhum usuário encontrado (1ag).".PHP_EOL;

            if ($msg == "") {
                while ($rs_usuarios_row = pg_fetch_array($rs_usuarios)) {
                    $a_logins[] = $rs_usuarios_row['ug_login'];
                }
            }
            $aret = $a_logins;

        }
        // Liberado em 2012-11-29
        return true;

    }
    
    function b_IsLogin_pagamento_bancodobradesco() {
        // Libera para todos os usuários
        return true;

    }
    
    function b_IsLogin_pagamento_boleto() {
        // Libera para todos os usuários
        return true;

    }
    
    function b_IsLogin_pagamento_bancodobrasil() {
        // Libera para todos os usuários
        return true;

    }

    function b_IsLogin_pagamento_bancoitau() {

        // Libera PAGAMENTO OnLine para todos os usuários LAN House
        return true;

    }

    function b_ValidacaoNexCafe($sLogin) {
        $aIsLogin_lista_usuarios_nexcafe = array(
            "WAGNER", "GLAUCIAPJ", "ODECIO", "FABIO###", "TAMLYN", "GLOBALLH", "FABIOSS13"
        );

        if (in_array(strtoupper($this->getLogin()), $aIsLogin_lista_usuarios_nexcafe)) {
            return true;
        }
        return false;
    }

    function b_VendasB2C() {
        // 6161 - PORTALBRASILGAMES;
        // 7323 - CARDSBR ELETRONICS
        $aIsId_lista_usuarios_Nao_B2C = array(6161, 7323);

        if (!in_array($this->getId(), $aIsId_lista_usuarios_Nao_B2C)) {  // && $this->bIsLanPre()) {
            return true;
        }
        return false;
    }

    function b_MateriaisPromocionais() {
        return true;
    }

    function bIsLanPre() {
        return (($this->getRiscoClassif() == 2) ? true : false);
    }
    function bIsLanPos() {
        return (($this->getRiscoClassif() == 1) ? true : false);
    }

    function b_exibeNexCafeInfo($emailUserLan = "") {
        $usuarios_liberados_nexcafe = array("GLAUCIA@E-PREPAG.COM.BR", "WAGNER@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR", "WAGNER.MBIS@GMAIL.COM");

        if (in_array(strtoupper($emailUserLan), $usuarios_liberados_nexcafe)) {
            return true;
        }
        return false;
    }

    function existeLogin_get_ID($login) {

        $ret_id = 0;
        $login = strtoupper(trim($login));

        //SQL
        $sql = "select ug_id from dist_usuarios_games ";
        $sql .= " where ug_login = " . SQLaddFields($login, "s");

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            $ret_id = $rs_row['ug_id'];
        }

        return $ret_id;
    }//end function existeLogin_get_ID

    function atualiza_ultimo_acesso($ug_id) {

        //Atualiza ultimo acesso
        //------------------------------------------------------------------
        if ($ug_id) {
            //SQL
            $sql = "update dist_usuarios_games set ";
            $sql .= " ug_data_ultimo_acesso = CURRENT_TIMESTAMP, ";
            $sql .= " ug_qtde_acessos = ug_qtde_acessos + 1 ";
            $sql .= " where ug_id = " . SQLaddFields($ug_id, "");
            $rs = SQLexecuteQuery($sql);
        }
    }//end function atualiza_ultimo_acesso

    function b_Is_Boleto_Itau() {
        // Libera BOLETO Itau para todos os usuários LAN House
        return false; //true;
    }
    
    function b_Is_Boleto_Banespa() {
        // Libera BOLETO Santander para todos os usuários LAN House
        return false;
    }
    
    function b_IsLogin_Wagner() {
        if (strtoupper($this->getLogin()) == "WAGNER") {
            return true;
        }
        return false;
    } //end function b_IsLogin_Wagner()

}  // End Class definition

// ================================================================
function testaBloqueoPorNaoPagamento() {
    $bBloqueado = false;
    $bDebug = false; //true; //

    //Recupera o usuario do session
    $usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
    $usuarioId = $usuarioGames->getId();
    $pagto = $_SESSION['dist_pagamento.pagto'];

    //recupera saldo atual
    $usuarioGames = UsuarioGames::getUsuarioGamesById($usuarioId);

    $today1 = strtotime('now');
    $today = date("d.m.Y", $today1);
    $sdate1 = $usuarioGames->getPerfilCorteUltimoCorte(); // "2007-09-17"
    $f_date1 = mktime(0, 0, 0, substr($sdate1, 5, 2), substr($sdate1, 8, 2), substr($sdate1, 0, 4));
    $f_date = date("d.m.Y", $f_date1);
    $difference = intval(($today1 - $f_date1) / 86400 + 1);

//echo "<!-- difference: $difference<br> -->";

    $iaberto = checaBoletoEmAberto();
    $BoletosEmAberto = MesagemBoletoEmAberto($iaberto);
    $BoletosEmAberto = str_replace("<p>", "", $BoletosEmAberto);
    $BoletosEmAberto = str_replace("</p>", "", $BoletosEmAberto);
    $BoletosEmAberto = str_replace("<br>", "", $BoletosEmAberto);

    if ($bDebug) {
        echo "getPerfilFormaPagto(): " . $usuarioGames->getPerfilFormaPagto() . ",<br>getPerfilLimite(): " . $usuarioGames->getPerfilLimite() . ",<br>getPerfilSaldo(): " . $usuarioGames->getPerfilSaldo() . ",<br>getPerfilCorteDiaSemana(): " . $usuarioGames->getPerfilCorteDiaSemana() . ",<br>getPerfilCorteUltimoCorte(): " . $usuarioGames->getPerfilCorteUltimoCorte() . ",<br>getPerfilLimiteSugerido(): " . $usuarioGames->getPerfilLimiteSugerido() . ",<br>getCreditoPendente(): " . $usuarioGames->getCreditoPendente() . "<br>";

        echo "iaberto: $iaberto<br>";
        echo "BoletosEmAberto: $BoletosEmAberto<br>";
    }
    // Se "Saldo negativo", "último corte em aberto" & "último corte faz mais de 3 dias" & "existe último corte" & "sem boletos pendentes"
    if ((($usuarioGames->getPerfilSaldo() < 0) &&
            ( ($usuarioGames->obterUltimoCorteStatus() == "1") && ($difference > $GLOBALS['CORTE_BOLETO_PRACO_BLOQUEIO']) && (strlen($sdate1) != 0) ) ) ||
            ($iaberto < 0)
    ) {
        $bBloqueado = true;
    }

    if ($bDebug) {
        echo "<div class=\"texto\" align=\"left\">"; //"Em classGamesUsuario.php<br>";
        echo "Saldo: <b>" . $usuarioGames->getPerfilSaldo() . "</b><br>";
        echo "Boletos em aberto: <b>" . $BoletosEmAberto . "</b> (<b>" . $iaberto . "</b>)<br>";
        echo "usuarioGames->obterUltimoCorteStatus(): " . ($usuarioGames->obterUltimoCorteStatus()) . "<br>";
        echo "difference: " . ($difference) . "<br>";
        echo "GLOBALS['CORTE_BOLETO_PRACO_BLOQUEIO']: " . ($GLOBALS['CORTE_BOLETO_PRACO_BLOQUEIO']) . "<br>";
        echo "difference>GLOBALS['CORTE_BOLETO_PRACO_BLOQUEIO']: " . ($difference > $GLOBALS['CORTE_BOLETO_PRACO_BLOQUEIO']) . "<br>";
        echo "sdate1: " . $sdate1 . "<br>";

        if (strlen($sdate1) != 0) {
            echo "Diferença de datas: (<b>" . $f_date . "</b>)-(<b>" . $today . "</b>) -> <b>" . $difference . "</b> dias<br>";
            echo "Dias para corte se estiver em aberto: (<b>" . $GLOBALS['CORTE_BOLETO_PRACO_BLOQUEIO'] . "</b>) <br>";
            echo "Último Corte: <b>" . $usuarioGames->getPerfilCorteUltimoCorte() . "</b><br>";
            echo "Último Corte Status: <b>" . $GLOBALS['CORTE_STATUS_DESCRICAO'][$usuarioGames->obterUltimoCorteStatus()] . " (" . $usuarioGames->obterUltimoCorteStatus() . ")</b><br>";
        } else {
            echo "Não foi encontrado Último Corte para este Usuário<br>";
        }
        echo "<br>";
        if ($bBloqueado) {
            echo "<font color=\"#FF0000\">Bloqueado por não pagamento</font><br>";
        } else {
            echo "Sem problemas por não pagamento<br>";
        }
        echo "</div>";
        echo "<br>";
    }

    return $bBloqueado;

}

function checaBoletoEmAberto() {
    $bDebug = false; //true; //

    $iaberto = 0;
    //login
    $usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
    $usuario_id = $usuarioGames->getId();
    if ($bDebug) {
        echo "GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO']: " . $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO'] . "<br>";
        echo "GLOBALS['CORTE_STATUS']['ABERTO']: " . $GLOBALS['CORTE_STATUS']['ABERTO'] . "<br>";
    }
    $sql = "select * from cortes c where c.cor_ug_id = " . $usuario_id . " and cor_status=1 order by cor_periodo_fim desc, cor_periodo_ini desc";
    if ($bDebug) {
        echo "sql1: $sql<br>";
    }
    $res_count = SQLexecuteQuery($sql);
    $total_table = pg_num_rows($res_count);
    if ($total_table > 0) {
        $rs_cortes_row = pg_fetch_array($res_count);

        if ($rs_cortes_row['cor_status'] == $GLOBALS['CORTE_STATUS']['ABERTO']) {
            if ($rs_cortes_row['cor_bbc_boleto_codigo'] && $rs_cortes_row['cor_tipo_pagto'] == $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) {
                $sql = "select * from boleto_bancario_cortes bbc where bbc.bbc_boleto_codigo = " . $rs_cortes_row['cor_bbc_boleto_codigo'];
                if ($bDebug) {
                    echo "sql2: $sql<br>";
                }
                $rs_boleto = SQLexecuteQuery($sql);
                if ($rs_boleto && pg_num_rows($rs_boleto) > 0) {
                    $rs_boleto_row = pg_fetch_array($rs_boleto);
                    $bbc_status = $rs_boleto_row['bbc_status'];
                    $bbc_data_venc = $rs_boleto_row['bbc_data_venc'];
                    if ($bbc_status == $GLOBALS['CORTE_BOLETO_STATUS']['ABERTO'] || $bbc_status == $GLOBALS['CORTE_BOLETO_STATUS']['ENVIADO']) {
                        if (((date("Y-m-d", time()) - strtotime($bbc_data_venc)) / (60 * 60 * 24)) > $GLOBALS['CORTE_BOLETO_PRACO_BLOQUEIO']) {
                            $iaberto = -1; //"<p><br><font color=\"#FF0000\">Existem Boletos em aberto vencidos. Favor, entrar em contato com nosso atendimento. </font></p>";
                        } else {
                            $iaberto = 1; //"<p><br><font color=\"#FF9966\">Existem Boletos em aberto. Favor, realizar o pagamento do Boleto em aberto. </font></p>";
                        }
                    }
                }
            }

        }
    } else {
        $iaberto = 0; //"Não existem Boletos em Aberto";
    }

    if ($bDebug) {
        echo "iaberto: $iaberto<br>";
    }
    return $iaberto;
}

function MesagemBoletoEmAberto($iaberto) {
    $sout = "";
    if ($iaberto == -1) {
        $sout = "<p><br><b><font color=\"#FF0000\">Existem Boletos em aberto e vencidos. Clique <a href='../corte/corte_consulta.php'>aqui</a> para visualizá-los. </font></b></p>";
    } else if ($iaberto == 1) {
        $sout = "<p><br><b><font color=\"#FF0000\">Existem Boletos em aberto. Favor, realizar o pagamento do Boleto em aberto. </font></b></p>";
    } else if ($iaberto == 0) {
        $sout = ""; //"<p><br>Não existem Boletos em Aberto</p>";
    } else {
        $sout = "<p><br><b><font color=\"#FF0000\">Situação com boletos desconhecida. Status: " . $iaberto . "</font></b></p>";
    }
    return $sout;
}

// Retorna true se o usuário estiver na lista dos que utilizam Bilheteria
function bUsaBilheteria($sLogin) {

    //             "YOSHIDACOMPUTERS", "REDWING", "MOCOCA", "UPLOAD", "BLANCO", "NEBHUMA", 
    $aUsaBilheteria = Array(
        /* Permanentes */
        "FABIO###", "WAGNER", "ODECIO",
        /* 2009-Out-01 */
        "MUNDODIGITAL", "FOURPLAY", "RIDEACASE", "PENTAGONO", "YAG4MI3K", "SMAGNUN", "140000411", "IMMERSION", "SAKURALANHOUSE", "MINHOKAS", "LANDCOM", "GAMEMANIA", "VIRUSSP", "ANEXUS_INTERNET", "BLACKDOG", "NETPLANET", "DARKRETOS", "VIRTUAL.BEACH", "FAVICO", "BATTLESTAR I", "MONSTERGAMES", "OXILAN", "PHOENIXNET_OSTREZE", "REVOLUTION", "PSYKOLANHOUSE", "TAMLYN", "VIRTUALGAMES", "STAR GAMES", "XZNOVATO37ZX", "LANTNT", "AREARESTRITA", "CRONUS", "GUERREIROS", "CANALGAMES", "ALLANALVES", "XPLANET", "PSYCOHOUSE", "UNILAN", "MISTER", "CAVERA23", "SKYNET", "PLAYNETLAN", "VALMARCEL", "CYBERX1", "ULTRACYBER", "COLISEULAN", "PLUGADO", "REDWING", "SR.BRITO", "NEWSTATION", "MESTREMAU", "COLISEOLANHOUSE", "HELTONGUNB", "EVOLUTIONLANGAMES", "SOSLANHOUSE", "RGFSOLUTIONS", "NETSTATION", "GAMESFORUS", "CYBERGENERATION", "NEBHUMA", "YEAHBABY", "TSACOM", "BLUEWAY", "CYBERCAFEAVENIDA", "PEDROFLORIDO", "GAME MANIA CLUBE", "LANMANIA", "MIFELINA", "WSPLANHOUSE", "ROBSONGAMES", "JOAOFLINS", "JOSE MILANO", "ZANZAR", "LEONARDO_POMPOLO", "DONIZETEQ", "SOFTPRINT", "UPLOAD", "GOTTECK", "DRAGON", "GRGAMESNET", "CYBERPARK", "PORTALHPG", "TORPEDO", "CARDSCOMBR", "AMAZINGNET", "ARENACLH", "DISCOVERY", "FENIXLH", "GLOBALLH", "SPYLAN", "WORKPLAY", "CENTRALPC", "MICROABC", "ALEXANDRE1470", "FRANKA", "DANIELGAMES", "GMTLANHOUSE", "WILBELISON", "TB WEB", "BREAKPOINT", "IMPACTLANHOUSE", "MIKESLAN", "XNEURON", "ULTRASNIPER", "SPACEGAMERS", "CONNECTINGLAN", "ADRIANODDD", "LEGENDSDOBRASIL", "MOCOCA", "IBI.COM", "YOSHIDACOMPUTERS", "CRIATIVA", "KAVERNA.NET", "CAMPER", "MORPHEUS.INFO", "MAYLONFP", "VO NA NET", "VANDERSONCF", "ZEMARCOS", "ALEFCOM", "TAVERNADRACON", "PRONTNET", "LANHOUSE", "MR.POPOV", "MEGALOAD", "MANDRAKEJO", "MEGAPLACE", "MARIOBROWN", "A.BARAO", "BRUNIVALDO", "DUDALANHOUSE", "ADE_RESMINI", "FIRELAN", "GAMEBIT", "CYBERNEW", "QUESTSAN", "HORIZON", "AP_SONIC", "BONNER", "METEORA", "NETTIME", "CLEBINHO", "MNONLINE", "ACAOVIRTUAL", "BATTLESTAR2", "BATTLESTAR3", "XGAMES", "YEAHBABY1", "NICOLAPINHATAR", "EASYNET", "RENATAGODE", "FIXMACONECT", "ACCESSPOINT", "CONNET.COM", "ALIENINTERNET", "METROPOLIS", "EXTREMESANTOS", "GENESISLAN", "MCATHARINO", "KEIDISAN", "HIGHET", "MRCAIOBOY", "GR.INFORMATICA", "BARIBARIBARI", "LOKOGAMES", "NET SOURCE", "MOUSEMEDIA", "PARAFINAFR", "3DM3DM3DM", "BARTKANINDE", "PLANET-LAN-HOUSE", "CASTLEVANIA", "BELIZARIO", "SKROTHU", "AGUIAF16", "COYOTE GAMES", "MATRIXNEOV8", "CMSLAN", "CONEXAO LAN HOUSE", "SEICOMP", "BLANCO", "STORMLAN", "KALANGOSLAN", "GIGANET", "YELLOWHOUSE", "PONTOCOM LAN HOUSE", "MMLANHOUSE", "ANDCARD", "JUNIOR", "PLANNERFUTURELANHOUSE", "PRISMALH", "NEW EVOLUTION", "AZAZEL", "RIPWAY", "EMANUELDT", "OMEGA.NET", "TISTEC", "SPIDERS_1", "WOODYINF", "MDALANHOUSE", "REGISRABBIT", "CLAUDINEI BIZERRA DA COSTA", "ARENNAHOUSE", "OYAMANET", "WALCOW.NET", "BARUTTI", "NETOWILSON", "LANSECRET", "LEMAX152", "FIRMAGAMES", "GAMEOVERLAN", "BUSSOLAESOUSA", "BILHETERIA", "VIGNADO", "PLAYCENTER", "GIGABYTE", "ANNAMARIA", "ANGELALAN", "GARAGYNHA", "NEITIVI", "MARCELOGTB2009", "DANIELREDDRAGONS", "REDFOXLAN", "ETIELCOMPUTER", "GEORGIA", "ANDRECONRADO", "JAPONESMDG", "JWILLIANS", "RMMAURI", "ARTCOPY", "DANIELMILICIA", "KAZZAN", "AGEPCHELP", "CYBERBROOK", "AREAXLANHOUSEMOGI", "RENATOCRD", "WOLFMASTERLANHOUSE", "ELIEZIO007", "JOTABIG", "JUNIOR_PAC", "JPCLANHOUSE", "LEOHOUSE", "ACERVUS", "GIGASHOT", "THAIS.CVL", "JKLANHOUSE01", "KISHIMA2301", "FABREGAS01", "BOOMLAN", "IZZACK", "METEORO", "UP GAMES", "SLAMPGAMES", "ZECANBS", "FERNANDOFONSECA", "XPLAYNET", "MASTERSOLUCOES", "CENTRALINFO", "NETGAME1", "SHOCKLAN", "VINICIUSJMP", "MARCEL.PORTELLA", "ZAPPAEU", "PIECALEG", "ABASLAN", "LGUIMARAES", "AMC SISTEMAS", "MARCIOARDENGHE", "GAMEHOUSE", "ESTRELASPEDD", "MUVESNET", "SEAGAMES", "FLAVIO.AKIN4THON", "JOSEFPRADO", "GIGABYTEINFORMATICA", "DAVYPRADO", "TURBNET", "MEGACHIP", "TULLIO", "ALTERNATIVE", "ACESSOWEBLH", "ARAGORNPI", "JAYCRIS", "PAPAGAIO", "ROMACYBER",
        // Novos de Bilheteria_2
        "BLESSADM", "CAPSWAT", "LANHOUSEEDNET",

        /* 2010-Fev-06 */
        "TWISTERLH",
        
        "SOLLUCIO", "BRUYURI"
    );

    $bret = false;
    if (in_array($sLogin, $aUsaBilheteria)) {
        $bret = true;
    }
//        $bret = true;
    $bret = false;
    return $bret;
}

// Retorna true se o usuário estiver na lista dos que utilizam Bilheteria 2a versão (a versão com senha gerada na bilheteria)
function bUsaBilheteria_2($sLogin) {

    $aUsaBilheteria_2 = Array(
        /* Permanentes */
        "WAGNER", "ODECIO",
        /* Lan houses */
        "BLESSADM", "TOMNIC", "CAPSWAT", "DAYANE", "DUDALANHOUSE", "PEDROFLORIDO", "FOURPLAY", "BRUNIVALDO", "GAMESFORUS", "GEORGIA", "HORIZON", "KILANHOUSE", "LANCOMBAT", "LANHOUSEEDNET", "LANSECRET", "JOAOGOMES1968", "MOTENAIS", "MR.POPOV", "LKFINFO", "PHOENIXNET_OSTREZE", "PLANNET HOUSE LTDA ME", "QUESTSAN", "QUESTVG", "ROXLANCURITIBA", "SEICOMP", "ULTRASNIPER", "WORKPLAY", "YBCYBERCAFE", "YOSHIDACOMPUTERS",
        "ANNAMARIA", "TREINAMENTO"
        , "SOLLUCIO", "BRUYURI"
    );


    $bret = false;
    if (bUsaBilheteria($sLogin) || (in_array($sLogin, $aUsaBilheteria_2))) {
        $bret = true;
    }
    return $bret;
}


function bRelatorioVendasComOperadores($sLogin) {
    return true;
}

function b_IsLogin_Tipo_Estabelecimento($sLogin) {
    $usuarios_tipo_estabelecimento = array("WAGNER", "FABIO###");

    if (in_array($sLogin, $usuarios_tipo_estabelecimento)) {
        return true;
    }
    return false;
}


function b_IsLogin_Recarga_Celular($sLogin) {
    $usuarios_recarga_celular = array("WAGNER", "FABIO###", "JPTREVISAN");

    if (in_array($sLogin, $usuarios_recarga_celular)) {
        return true;
    }
    return false;
}

function b_IsLogin_Servicos($sLogin) {
    $usuarios_recarga_celular = array("WAGNER", "FABIO###", "JPTREVISAN");

    if (in_array($sLogin, $usuarios_recarga_celular)) {
        return true;
    }
    return false;
}

function b_IsNew_template($vg_ug_id) {

    // Liberado em 2013-02-18
    return true;
}

?>
