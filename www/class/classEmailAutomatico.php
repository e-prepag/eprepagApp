<?php require_once __DIR__ . '/../includes/constantes_url.php'; ?>
<?php
require_once $raiz_do_projeto . "includes/constantesGerais.php";

class EnvioEmailAutomatico {
    private $ug_id;
    private $tipo_usuario;
    private $ug_nome;
    private $ug_nome2;
    private $ug_login;
    private $ug_email;
    private $ug_email_sup;
    private $ug_email_antigo;
    private $ug_email_novo;
    private $ug_login_antigo;
    private $ug_perfil_saldo;
    private $periodo_sem_usar_saldo;
    private $saldo_maior_que;
    private $identificador_email;
    private $periodo_novo_envio;
    private $aux_file;
    private $lista_credito_oferta;
    private $pedido;
    private $lista_produto;
    private $token;
    private $instrucoes_uso;
    private $promocoes;
    private $ug_senha;
    private $product;
    private $ug_atendimento;
    private $ug_cor;
    private $ug_logo;
    private $subject = null;
    private $bcc = "";
    private $partner_email;
    private $partner_details;
    private $partner_graph;
    private $saldoAdicionado;
    private $formaPagamento;
    private $info1;
    private $info2;
    private $info3;
	private $onboardingNome;
	private $onboardingCodigo;
	private $twofaNome;
	private $twofaToken;
	private $twofaUrl;
	private $chaveMestra;
    private $responsavel = '';
    private $subject_mail = array(
        // Saldo Gamer 
        "SaldoGamer" => "Saldo E-Prepag CASH",
        // Quando cielo é liberado
        "CieloLiberado" => "Agora você pode utilizar seu cartão de crédito",
        // Depósito em Saldo de Ofertas 
        "DepositoOfertas" => "Depósito em Saldo de Ofertas",
        // Alteração de Cadastro 
        "AlteracaoCadastro" => "Alteração de Cadastro",
        // Alteração de Senha 
        "AlteracaoSenha" => "Alteração de Senha",
        // Alteração de e-mail
        "AlteracaoEmail" => "Alteração de e-mail",
        // Cadastro de Novo Usuário Gamer 
        "CadastroGamer" => "Cadastro na E-Prepag",
        // Pedido Registrado 
        "PedidoRegistrado" => "Pedido Registrado (M)",
        // Pedido Registrado Express Money
        "PedidoRegistradoEx" => "Pedido Registrado (E)",
        // Compra Processada Express Money
        "CompraProcessadaEx" => "Compra Processada",
        // Compra Processada Money
        "CompraProcessada" => "Compra Processada",
        // Envio de Senha para Cadastramento Automático Através da Integração
        "SenhaIntegracao" => "Seu Acesso ao Site E-Prepag",
        // Alteração de Cadastro Usuário LAN House 
        "AlteracaoCadastroLH" => "Alteração de Cadastro",
        // Alteração de Cadastro Usuário LAN House 
        "AlteracaoLoginLH" => "Alteração de Cadastro",
        // Alteração de Senha de LAN House
        "AlteracaoSenhaLH" => "Alteração de Senha",
        // Recuperação de Senha de LAN House
        "RecuperaSenhaLH" => "Recuperação de Senha",
        // Venda Processada de LAN House
        "VendaProcessadaLH" => "Venda Realizada",
        // Pedido Registrado Integração
        "PedidoRegistradoInt" => "Pedido Registrado (I)",
        // Compra Processada Integração
        "CompraProcessadaInt" => "Compra Processada",
        // Compras Não Concluídas
        "ComprasNaoConcluidas" => "E-Prepag - Podemos Ajudar?",
        // Envio de Senha para Cadastramento Automático Através do Express Money
        "SenhaExMoney" => "Seu Acesso ao Site E-Prepag",
        // Envio de Email com PINs através da LAN House para seus usuários
        "CompraPontoVenda" => "Sua compra no ponto de venda",
        // Envio de Email com Compra de Serviços B2C
        "CompraB2C" => "Sua compra no ponto de venda",
        // Envio de Email com Recarga de Celular
        "RecargaRedeSim" => "Comprovante de Recarga de Celular",
        // Envio de Email com Fechamento Financeiro para o Publisher
        "FechamentoFinanceiro" => "E-Prepag Report",
        // Envio de Email para informar o pedido
        "AdicaoSaldoLan" => "E-Prepag - Adição de Saldo",
        // Adicao de Saldo Gamer
        "AdicaoSaldoGamer" => "E-Prepag - Adição de Saldo",
        // Pedido negado lan
        "PedidoNegadoLan" => "E-Prepag - Seu cadastro E-Prepag não foi aprovado",
        // Boleto para pagamento
        "BoletoParaPagamentoLanPos" => "E-Prepag - Boleto para pagamento",
        // Pedido Cancelado
        "PedidoCancelado" => "E-Prepag - Pedido Cancelado",
        // Pedido Cancelado
        "LanAprovada" => "E-Prepag - Seu Cadastro Foi Aprovado",
        // Lan com saldo abaixo de R$ 60
        "SaldoMinimoLH" => "E-Prepag - Saldo minimo para vendas",
        // Cadastro de Lan
        "CadastroLAN" => "E-Prepag - Cadastro Completo",
        // Retornar Contato de LAN
        "RetornarContato" => "E-Prepag - Não conseguimos localizar sua loja",
        // Dados Insuficientes de LAN
        "DadosInsuficientes" => "E-Prepag - Seu cadastro E-Prepag está incompleto",
		
		"Onboarding" => "E-prepag - Cadastre seu PDV no Onboarding",
		
		"TWOFA" => "E-prepag - Duplo fator de autenticação",
		
		"ChaveMestra" => "E-prepag - Chave Mestra"
    );

    
    private $tags_corpo_mail = array(
        // Saldo Gamer 
        "SaldoGamer" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<UG_PERFIL_SALDO>>>" => "number_format((\$this->getUgPerfilSaldo()*100),0,',','.')",
        ),
        // Libera cielo
        "CieloLiberado" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()"
        ),
        // Depósito em Saldo de Ofertas 
        "DepositoOfertas" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<LISTA_OFERTAS>>>" => "\$this->getListaCreditoOferta()",
        ),
        // Alteração de Cadastro 
        "AlteracaoCadastro" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<UG_EMAIL>>>" => "\$this->getUgEmail()",
        ),
        // Alteração de Senha 
        "AlteracaoSenha" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<UG_EMAIL>>>" => "\$this->getUgEmail()",
        ),
        // Alteração de e-mail 
        "AlteracaoEmail" => array(
            "<<<INSTRUCOES_ALTERACAO_EMAIL>>>" => "\$this->getInstrucoesUso()",
            "<<<UG_EMAIL>>>" => "\$this->getUgEmail()",
            "<<<UG_NOME>>>" => "\$this->getUgNome()"
        ),
        // Cadastro de Novo Usuário Gamer 
        "CadastroGamer" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<UG_EMAIL>>>" => "\$this->getUgEmail()",
        ),
        // Pedido Registrado 
        "PedidoRegistrado" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<UG_EMAIL>>>" => "\$this->getUgEmail()",
            "<<<PEDIDO>>>" => "\$this->getPedido()",
            "<<<LISTA_OFERTAS>>>" => "\$this->getListaCreditoOferta()",
        ),
        // Pedido Registrado Express Money
        "PedidoRegistradoEx" => array(
            "<<<UG_EMAIL>>>" => "\$this->getUgEmail()",
            "<<<PEDIDO>>>" => "\$this->getPedido()",
            "<<<LISTA_OFERTAS>>>" => "\$this->getListaCreditoOferta()",
            "<<<UG_TOKEN>>>" => "\$this->getToken()",
        ),
        // Compra Processada Express Money
        "CompraProcessadaEx" => array(
            "<<<PEDIDO>>>" => "\$this->getPedido()",
            "<<<LISTA_OFERTAS>>>" => "\$this->getListaCreditoOferta()",
            "<<<LISTA_PRODUTO>>>" => "\$this->getListaProduto()",
            "<<<PROMOCOES>>>" => "\$this->getPromocoes()",
        ),
        // Compra Processada Money
        "CompraProcessada" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<PEDIDO>>>" => "\$this->getPedido()",
            "<<<LISTA_OFERTAS>>>" => "\$this->getListaCreditoOferta()",
            "<<<LISTA_PRODUTO>>>" => "\$this->getListaProduto()",
            "<<<INSTRUCOES_USO>>>" => "\$this->getInstrucoesUso()",
            "<<<PROMOCOES>>>" => "\$this->getPromocoes()",
        ),
        // Envio de Senha para Cadastramento Automático Através da Integração
        "SenhaIntegracao" => array(
            "<<<UG_EMAIL>>>" => "\$this->getUgEmail()",
            "<<<UG_SENHA>>>" => "\$this->getUgSenha()",
            "<<<PROMOCOES>>>" => "\$this->getPromocoes()",
        ),
        // Alteração de Cadastro Usuário LAN House 
        "AlteracaoCadastroLH" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<UG_EMAIL>>>" => "\$this->getUgEmail()",
            "<<<UG_EMAIL_NOVO>>>" => "\$this->getUgEmailNovo()",
        ),
        // Alteração de Cadastro Usuário LAN House 
        "AlteracaoLoginLH" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<UG_LOGIN>>>" => "\$this->getUgLogin()",
            "<<<UG_LOGIN_ANTIGO>>>" => "\$this->getUgLoginAntigo()",
        ),
        // Alteração de Senha de LAN House
        "AlteracaoSenhaLH" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
        ),
        // Recuperação de Senha de LAN House
        "RecuperaSenhaLH" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<UG_SENHA>>>" => "\$this->getUgSenha()",
        ),
        // Venda Processada de LAN House
        "VendaProcessadaLH" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<PEDIDO>>>" => "\$this->getPedido()",
            "<<<LISTA_OFERTAS>>>" => "\$this->getListaCreditoOferta()",
        ),
        // Pedido Registrado Integração
        "PedidoRegistradoInt" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<PEDIDO>>>" => "\$this->getPedido()",
            "<<<LISTA_OFERTAS>>>" => "\$this->getListaCreditoOferta()",
        ),
        // Compra Processada Integração
        "CompraProcessadaInt" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<PEDIDO>>>" => "\$this->getPedido()",
            "<<<LISTA_OFERTAS>>>" => "\$this->getListaCreditoOferta()",
        ),
        // Compras Não Concluídas
        "ComprasNaoConcluidas" => array(
            "<<<UG_EMAIL>>>" => "\$this->getUgEmail()",
        ),
        // Envio de Senha para Cadastramento Automático Através do Express Money
        "SenhaExMoney" => array(
            "<<<UG_EMAIL>>>" => "\$this->getUgEmail()",
            "<<<UG_SENHA>>>" => "\$this->getUgSenha()",
            "<<<PROMOCOES>>>" => "\$this->getPromocoes()",
        ),
        // Envio de Email com PINs através da LAN House para seus usuários
        "CompraPontoVenda" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<UG_NOME2>>>" => "\$this->getUgNome2()",
            "<<<LISTA_PRODUTO>>>" => "\$this->getListaProduto()",
            "<<<INSTRUCOES_USO>>>" => "\$this->getInstrucoesUso()",
            "<<<PROMOCOES>>>" => "\$this->getPromocoes()",
            "<<<UG_EMAIL_SUPORTE>>>" => "\$this->getUgEmailSup()",
            "<<<UG_ATENDIMENTO>>>" => "\$this->getUgAtendimento()",
            "<<<UG_COR>>>" => "\$this->getUgCor()",
            "<<<UG_LOGO>>>" => "\$this->getUgLogo()",
        ),
        // Envio de Email com Compra de Serviços B2C
        "CompraB2C" => array(
            "<<<UG_NOME>>>" => "\$this->getUgNome()",
            "<<<PRODUCT>>>" => "\$this->getProduct()",
            "<<<LISTA_PRODUTO>>>" => "\$this->getListaProduto()",
            "<<<INSTRUCOES_USO>>>" => "\$this->getInstrucoesUso()",
            "<<<PROMOCOES>>>" => "\$this->getPromocoes()",
        ),
        // Envio de Email com Recarga de Celular
        "RecargaRedeSim" => array(
            "<<<LISTA_PRODUTO>>>" => "\$this->getListaProduto()",
            "<<<PROMOCOES>>>" => "\$this->getPromocoes()",
        ),
        // Envio de Email com Fechamento Financeiro para o Publisher
        "FechamentoFinanceiro" => array(
            "<<<PARTNER_EMAIL>>>" => "\$this->getPartnerEmail()",
            "<<<PARTNER_DETAILS>>>" => "\$this->getPartnerDetails()",
            "<<<PARTNER_GRAPH>>>" => "\$this->getPartnerGraph()",
        ),
        // Envio de Email com Fechamento Financeiro para o Publisher
        "AdicaoSaldoLan" => array(
            "<<<UG_NOME>>>" => '$this->getUgNome()',
            "<<<TOTAL_SALDO_ADD>>>" => '$this->getSaldoAdicionado()',
            "<<<NUMERO_PEDIDO>>>" => '$this->getPedido()',
            "<<<FORMA_PAGTO>>>" => '$this->getFormaPagamento()',
            "<<<UG_EMAIL>>>" => '$this->getUgEmail()',
        ),
        // Envio de Email com Fechamento Financeiro para o Publisher
        "AdicaoSaldoGamer" => array(
            "<<<UG_NOME>>>" => '$this->getUgNome()',
            "<<<TOTAL_SALDO_ADD>>>" => '$this->getSaldoAdicionado()',
            "<<<NUMERO_PEDIDO>>>" => '$this->getPedido()',
            "<<<FORMA_PAGTO>>>" => '$this->getFormaPagamento()',
            "<<<UG_EMAIL>>>" => '$this->getUgEmail()',
            "<<<DADOS_BOLETO>>>" => '$this->getToken()',
        ),
        // Envio de Email com Senha da lan
        "PedidoNegadoLan" => array(
            "<<<UG_NOME>>>" => '$this->getUgNome()',
        ),
        // Boleto para pagamento
        "BoletoParaPagamentoLanPos" => array(
            "<<<UG_NOME>>>" => '$this->getResponsavel()',
            "<<<PERIODO_INICIO>>>" => '$this->getInfo1()',
            "<<<PERIODO_FIM>>>" => '$this->getInfo2()',
        ),
        // Pedido Cancelado
        "PedidoCancelado" => array(
            "<<<PEDIDO>>>" => '$this->getPedido()',
            "<<<LISTA_OFERTAS>>>" => '$this->getInfo1()',
            "<<<MOTIVO>>>" => '$this->getInfo2()',
        ),
        // Pedido Cancelado
        "LanAprovada" => array(
            "<<<LOGIN>>>" => '$this->getUgLogin()',
        ),
        // Pedido Cancelado
        "SaldoMinimoLH" => array(),
        // Cadastro de LAN
        "CadastroLAN" => array(
            "<<<UG_NOME>>>" => '$this->getUgNome()',
        ),
        // Retornar Contato de LAN
        "RetornarContato" => array(
            "<<<UG_NOME>>>" => '$this->getUgNome()',
        ),
        // Dados Insuficientes de LAN
        "DadosInsuficientes" =>   array(
            "<<<UG_NOME>>>" => '$this->getUgNome()',
        ),
		
		"Onboarding" => array(
			"<<<NOME>>>" => '$this->getOnboardingNome()',
			"<<<CODIGO>>>" => '$this->getOnboardingCodigo()'
		),
		
		"TWOFA" => array(
			"<<<NOME>>>" => '$this->get2FaNome()',
			"<<<TOKEN>>>" => '$this->get2FaToken()',
			"<<<URL>>>" =>  '$this->getUrl2FaToken()'
		),
		
		"ChaveMestra" => array(
			"<<<NOME>>>" => '$this->getUgNome()',
			"<<<CHAVE>>>" => '$this->getChaveMestra()'
		)
    );

    private $corpo_mail = array(
        // Saldo Gamer 
        "SaldoGamer" => "SaldoGamer.html",
    
        // Saldo Gamer 
        "CieloLiberado" => "CieloLiberado.html",

        // Depósito em Saldo de Ofertas 
        "DepositoOfertas" => "DepositoOfertas.html",

        // Alteração de Cadastro 
        "AlteracaoCadastro" => "AlteracaoCadastro.html",

        // Alteração de Senha 
        "AlteracaoSenha" => "AlteracaoSenha.html",
    
        // Alteração de e-mail
        "AlteracaoEmail" => "AlteracaoEmail.html",

        // Cadastro de Novo Usuário Gamer 
        "CadastroGamer" => "CadastroGamer.html",

        // Pedido Registrado 
        "PedidoRegistrado" => "PedidoRegistrado.html",

        // Pedido Registrado Express Money
        "PedidoRegistradoEx" => "PedidoRegistradoEx.html",

        // Compra Processada Express Money
        "CompraProcessadaEx" => "CompraProcessadaEx.html",

        // Compra Processada Money
        "CompraProcessada" => "CompraProcessada.html",

        // Envio de Senha para Cadastramento Automático Através da Integração
        "SenhaIntegracao" => "SenhaIntegracao.html",

        // Alteração de Cadastro Usuário LAN House 
        "AlteracaoCadastroLH" => "AlteracaoCadastroLH.html",
    
        // Alteração de Cadastro Usuário LAN House 
        "AlteracaoLoginLH" => "AlteracaoLoginLH.html",
    
        // Alteração de Senha de LAN House
        "AlteracaoSenhaLH" => "AlteracaoSenhaLH.html",

        // Recuperação de Senha de LAN House
        "RecuperaSenhaLH" => "RecuperaSenhaLH.html",

        // Venda Processada de LAN House
        "VendaProcessadaLH" => "VendaProcessadaLH.html",
                                
        // Pedido Registrado Integração
        "PedidoRegistradoInt" => "PedidoRegistradoInt.html",

        // Compra Processada Integração
        "CompraProcessadaInt" => "CompraProcessadaInt.html",

        // Compras Não Concluídas
        "ComprasNaoConcluidas" => "ComprasNaoConcluidas.html",
    
        // Envio de Senha para Cadastramento Automático Através do Express Money
        "SenhaExMoney" => "SenhaExMoney.html",

        // Envio de Email com PINs através da LAN House para seus usuários
        "CompraPontoVenda" => "CompraPontoVenda_jose.html",

        // Envio de Email com Compra de Serviços B2C
        "CompraB2C" => "CompraB2C.html",
            
        // Envio de Email com Recarga de Celular
        "RecargaRedeSim" => "RecargaRedeSim.html",

        // Envio de Email com Fechamento Financeiro para o Publisher
        "FechamentoFinanceiro" => "FechamentoFinanceiro.html",

        // Lanhouses pre-pagas ativas com saldo entre 0 e 20
        "SaldoLAN0_20" => "",

        // Lanhouses pre-pagas ativas com saldo entre 20-100
        "SaldoLAN20_100" => "",

        // Lanhouses pre-pagas ativas com saldo maior que 100
        "SaldoLANMais100" => "",
        
        // Informação da adição de saldo da lan
        "AdicaoSaldoLan" => "AdicaoSaldoLan.html",
        
        // Informação da adição de saldo da lan
        "AdicaoSaldoGamer" => "AdicaoSaldoGamer.html",
        
        // Pedido negado lan
        "PedidoNegadoLan" => "PedidoNegadoLan.html",
        
        // Boleto para pagamento
        "BoletoParaPagamentoLanPos" => "BoletoParaPagamentoLanPos.html",
        
        // Pedido Cancelado
        "PedidoCancelado" => "PedidoCancelado.html",
        
        // Lan House aprovada
        "LanAprovada" => "LanAprovada.html",
        
        // Lan com saldo abaixo de R$ 60
        "SaldoMinimoLH" => "SaldoMinimoLH.html",
        
        // Cadastro de Lan
        "CadastroLAN" => "CadastroLAN.html",
        
        // Retornar Contato de LAN
        "RetornarContato" => "RetornarContato.html",
        
        // Dados Insuficientes de LAN
        "DadosInsuficientes" =>  "DadosInsuficientes.html",
		
		"Onboarding" => "Onboarding-de-boas-vindas.html",
		
		"TWOFA" => "2FA.html",
		
		"ChaveMestra" => "ChaveMestra.html"
    );

    
    public function __construct($tipo_usuario = null, $identificador_email = null, $periodo_sem_usar_saldo = null, $saldo_maior_que = null, $periodo_novo_envio = null) {
        $this->setTipoUsuario($tipo_usuario);
        $this->setIdentificadorEmail($identificador_email);
        $this->setPeriodoSemUsarSaldo($periodo_sem_usar_saldo);
        $this->setSaldoMaiorQ($saldo_maior_que);
        $this->setPeriodoNovoEnvio($periodo_novo_envio);
    }//end function __construct
	
	public function getOnboardingNome() {
		return $this->onboardingNome;
	}
	public function getOnboardingCodigo() {
		return $this->onboardingCodigo;
	}
	public function setOnboardingNome($nome) {
		$this->onboardingNome = $nome;
	}
	public function setOnboardingCodigo($codigo) {
		$this->onboardingCodigo = $codigo;
	}
	
	
	public function get2FaNome() {
		return $this->twofaNome;
	}
	public function get2FaToken() {
		return $this->twofaToken;
	}
	public function set2FaNome($nome) {
		$this->twofaNome = $nome;
	}
	public function set2FaToken($token) {
		$this->twofaToken = $token;
	}
	
	public function setUrl2FaToken($type) {
		
		switch($type) {
			
			case "P":
				$this->twofaUrl = "" . EPREPAG_URL_HTTPS . "/creditos/confirmacao.php";
				break;
				
			case "U":
				$this->twofaUrl = "" . EPREPAG_URL_HTTPS . "/game/conta/confirmacao.php";
				break;
		}
	}
	
	public function getUrl2FaToken() {
		
		return $this->twofaUrl;
	}
	
	
    public function getUgID() {
        return $this->ug_id;
    }
    public function setUgID($ug_id) {
        $this->ug_id = $ug_id;
    }

    public function getTipoUsuario() {
        return $this->tipo_usuario;
    }
    public function setTipoUsuario($tipo_usuario) {
        $this->tipo_usuario = strtoupper($tipo_usuario);
    }
    
    public function getUgLogin(){
        return $this->ug_login;
    }
    public function setUgLogin($login){
        $this->ug_login = $login;
    }

    public function getUgNome() {
        return $this->ug_nome;
    }
    public function setUgNome($ug_nome) {
        $this->ug_nome = $ug_nome;
    }

    public function getUgNome2() {
        return $this->ug_nome2;
    }
    public function setUgNome2($ug_nome2) {
        $this->ug_nome2 = $ug_nome2;
    }

    public function getUgEmail() {
        return $this->ug_email;
    }
    public function setUgEmail($ug_email) {
        $this->ug_email = $ug_email;
    }

    public function getUgEmailAntigo() {
        return $this->ug_email_antigo;
    }
    public function setUgEmailAntigo($ug_email_antigo) {
        $this->ug_email_antigo = $ug_email_antigo;
    }
    
    public function setUgEmailNovo($ug_email_novo) {
        $this->ug_email_novo = $ug_email_novo;
    }
    
    public function getUgEmailNovo() {
        return $this->ug_email_novo;
    }
    
    public function setUgLoginAntigo($ug_login_antigo) {
    $this->ug_login_antigo = $ug_login_antigo;
    }
    
    public function getUgLoginAntigo() {
        return $this->ug_login_antigo;
    }
    
    public function getUgPerfilSaldo() {
        return $this->ug_perfil_saldo;
    }
    public function setUgPerfilSaldo($ug_perfil_saldo) {
        $this->ug_perfil_saldo = $ug_perfil_saldo;
    }

    public function getPeriodoSemUsarSaldo() {
        return $this->periodo_sem_usar_saldo;
    }
    public function setPeriodoSemUsarSaldo($periodo_sem_usar_saldo) {
        $this->periodo_sem_usar_saldo = $periodo_sem_usar_saldo;
    }

    public function getSaldoMaiorQ() {
        return $this->saldo_maior_que;
    }
    public function setSaldoMaiorQ($saldo_maior_que) {
        $this->saldo_maior_que = $saldo_maior_que;
    }

    public function getIdentificadorEmail() {
        return $this->identificador_email;
    }
    public function setIdentificadorEmail($identificador_email) {
        $this->identificador_email = $identificador_email;
    }

    public function getPeriodoNovoEnvio() {
        return $this->periodo_novo_envio;
    }
    public function setPeriodoNovoEnvio($periodo_novo_envio) {
        $this->periodo_novo_envio = $periodo_novo_envio;
    }

    public function getCorpoEmailTemplate($identificador) {
        $this->aux_file = file_get_contents($GLOBALS['raiz_do_projeto']."includes/templates/" . $this->corpo_mail[$identificador]);
        return $this->aux_file;
    }

    public function getCorpoEmailFull() {
        return $this->corpo_mail;
    }

    public function getTagsCorpoEmail($identificador) {
        return $this->tags_corpo_mail[$identificador];
    }

    public function getSubjectEmail($identificador) {
        return $this->subject_mail[$identificador];
    }

    public function getListaCreditoOferta() {
        return $this->lista_credito_oferta;
    }
    public function setListaCreditoOferta($lista_credito_oferta) {
        $this->lista_credito_oferta = $lista_credito_oferta;
    }

    public function getPedido() {
        return $this->pedido;
    }
    public function setPedido($pedido) {
        $this->pedido = $pedido;
    }

    public function getListaProduto() {
        return $this->lista_produto;
    }
    public function setListaProduto($lista_produto) {
        $this->lista_produto = $lista_produto;
    }

    public function getToken() {
        return $this->token;
    }
    public function setToken($token) {
        $this->token = $token;
    }

    public function getInstrucoesUso() {
        return $this->instrucoes_uso;
    }
    public function setInstrucoesUso($instrucoes_uso) {
        $this->instrucoes_uso = $instrucoes_uso;
    }

    public function getPromocoes() {
        return $this->promocoes;
    }
    public function setPromocoes($promocoes) {
        $this->promocoes = $promocoes;
    }

    public function getUgSenha() {
        return $this->ug_senha;
    }
    public function setUgSenha($ug_senha) {
        $this->ug_senha = $ug_senha;
    }

    public function getProduct() {
        return $this->product;
    }
    public function setProduct($product) {
        $this->product = $product;
    }

    public function getSubjectAdicional() {
        return $this->subject;
    }
    public function setSubjectAdicional($subject) {
        $this->subject = $subject;
    }

    public function getPartnerEmail() {
        return $this->partner_email;
    }
    public function setPartnerEmail($partner_email) {
        $this->partner_email = $partner_email;
    }

    public function getPartnerDetails() {
        return $this->partner_details;
    }
    public function setPartnerDetails($partner_details) {
        $this->partner_details = $partner_details;
    }

    public function getPartnerGraph() {
        return $this->partner_graph;
    }
    public function setPartnerGraph($partner_graph) {
        $this->partner_graph = $partner_graph;
    }

    public function getBccAdicional() {
        return $this->bcc;
    }
    public function setBccAdicional($bcc) {
        $this->bcc .= (($this->bcc) ? "," : "") . $bcc;
    }
    
    public function setSaldoAdicionado($saldo) {
        $this->saldoAdicionado = $saldo;
    }
    public function getSaldoAdicionado() {
        return $this->saldoAdicionado;
    }
    
    public function setFormaPagamento($forma) {
        $this->formaPagamento = $forma;
    }
    public function getFormaPagamento() {
        return $this->formaPagamento;
    }
    
    public function setInfo1($info1) {
        $this->info1 = $info1;
    }
    
    public function getInfo1() {
        return $this->info1;
    }
    
    
    public function setInfo2($info2) {
        $this->info2 = $info2;
    }
    
    public function getInfo2() {
        return $this->info2;
    }
    
    
    public function setInfo3($info3) {
        $this->info3 = $info3;
    }
    
    public function getInfo3() {
        return $this->info3;
    }
    
    public function getResponsavel() {
        return $this->responsavel;
    }

    public function setResponsavel($responsavel) {
        $this->responsavel = $responsavel;
    }

    public function getChaveMestra() {
		return $this->chaveMestra;
	}    
	
	public function setChaveMestra($chave_mestra) {
		$this->chaveMestra = $chave_mestra;
	}

    public function getUgAtendimento() {
        return $this->ug_atendimento;
    }

    public function setUgAtendimento($ug_atendimento) {
        $this->ug_atendimento = $ug_atendimento;
    }

    public function getUgCor() {
        return $this->ug_cor;
    }

    public function setUgCor($ug_cor) {
        $this->ug_cor = $ug_cor;
    }

    public function getUgLogo() {
        return $this->ug_logo;
    }

    public function setUgLogo($ug_logo) {
        $this->ug_logo = $ug_logo;
    }

    public function getUgEmailSup() {
        return $this->ug_email_sup;
    }

    public function setUgEmailSup($ug_email_sup) {
        $this->ug_email_sup = $ug_email_sup;
    }

    public function getUserDados() {
        $sql = "select ";
        if ($this->getTipoUsuario() == 'G') {
            $sql .= "ug_nome as ug_nome_full,* from usuarios_games ";
        }
        else {
            $sql .= "(CASE WHEN (ug_tipo_cadastro='PJ') THEN ug_nome_fantasia WHEN (ug_tipo_cadastro='PF') THEN ug_nome END) as ug_nome_full,* from dist_usuarios_games ";
        }
        $sql .= "where ug_id = " . $this->getUgID();
        $result = SQLexecuteQuery($sql);
        if ($result_row = pg_fetch_array($result)) {
            if (array_key_exists('ug_responsavel', $result_row) ) {
                $this->setResponsavel($result_row['ug_responsavel']);
            }
            $this->setUgEmail($result_row['ug_email']);
            $this->setUgLogin($result_row['ug_login']);
            $this->setUgNome($result_row['ug_nome_full']);
            $this->setUgPerfilSaldo($result_row['ug_perfil_saldo']);

            $objEncryption = new Encryption();
            $senha = $objEncryption->decrypt($result_row['ug_senha']);

            $this->setUgSenha($senha);
            return true;
    }
        else return false;
    }//end function getUserDados


    public function setLogEnvioEmail() {
        // Em alguns casos ug_id pode não estar definido => usa 0 para evitar erro no SQL query
        $ug_id = (($this->getUgID()) ? $this->getUgID() : 0);
        $sql = "insert into envio_email (ee_data_inclusao, ee_tipo_usuario, ug_id, ug_email, ee_identificador) values (NOW(), '" . strtoupper($this->getTipoUsuario()) . "', " . $ug_id . ", '" . $this->getUgEmail() . "', '" . $this->getIdentificadorEmail() . "');";
        //$result = 0;
        $result = SQLexecuteQuery($sql);
        if ($result) return true;
        else return false;
    }//end function setLogEnvioEmail


    public function getUsersIDs() {
        $sql = "select ug_id from ";
        if ($this->getTipoUsuario() == 'G') {
            $sql .= " usuarios_games ug ";
        }
        else {
            $sql .= " dist_usuarios_games ug ";
        }
        $sql .= " 
            where ug_perfil_saldo > " . $this->getSaldoMaiorQ();
        if ($this->getTipoUsuario() == 'G') {
            $sql .= " 
                and (
                    select count(*) as n 
                    from tb_venda_games vg 
                    where vg.vg_ug_id = ug.ug_id 
                        and vg.vg_ultimo_status = 5 
                        and vg.vg_data_inclusao >= (NOW()-'" . $this->getPeriodoSemUsarSaldo() . " days'::interval)) = 0";
        }
        else {
            $sql .= " 
                and (
                    select count(*) as n 
                    from tb_dist_venda_games vg 
                    where vg.vg_ug_id = ug.ug_id 
                        and vg.vg_ultimo_status = 5 
                        and vg.vg_data_inclusao >= (NOW()-'" . $this->getPeriodoSemUsarSaldo() . " days'::interval)) = 0";
        }
        $sql .= " 
                and coalesce(
                                (select extract(DAY from date_trunc('day', (NOW()-MAX(ee_data_inclusao)))) as delay 
                                from envio_email ee
                                       where ee.ug_id = ug.ug_id 
                                        AND ee.ee_tipo_usuario = '" . strtoupper($this->getTipoUsuario()) . "')
                                , (" . $this->getPeriodoNovoEnvio() . "+1)
                        ) > " . $this->getPeriodoNovoEnvio();
        echo $sql . PHP_EOL;
        $result = SQLexecuteQuery($sql);
        $lista_ids = "";
        if(pg_num_rows($result) > 0) {
            while ($result_row = pg_fetch_array($result)) {
                if (strlen($lista_ids) > 1) {
                    $lista_ids .="," . $result_row['ug_id'];
                }
                else {
                    $lista_ids = $result_row['ug_id'];
                }
            }
        }//end if(pg_num_rows($result) > 0)
        return $lista_ids;
    }//end function getUsersIDs


    public function MontaEmail() {
        $subjectEmail = $this->getSubjectEmail($this->getIdentificadorEmail());
        $lista_ids = $this->getUsersIDs();
        if(!empty($lista_ids)) {
                $aux_ids = explode(",", $lista_ids);
                $lista_emails = "<html>";
                foreach ($aux_ids as $key => $value) {
                    $this->setUgID($value);
                    $this->getUserDados();

                    echo "UG_ID: " . $this->getUgID() . PHP_EOL;
                    echo "UG_EMAIL: " . $this->getUgEmail() . PHP_EOL;
                    echo "UG_NOME: " . $this->getUgNome() . PHP_EOL;
                    echo "UG_PERFIL_SALDO: " . $this->getUgPerfilSaldo() . PHP_EOL;

                    $this->setLogEnvioEmail();
                    $msgEmail = $this->getCorpoEmail();
                    $ug_email = $this->getUgEmail();
                    $lista_emails .= ", " . $this->getUgEmail();
                    $aux_ids_aux[] = $this->getUgID();
                    $bcc = ""; 
                    enviaEmail($ug_email, null, $bcc, $subjectEmail, $msgEmail);
                    echo "-----------------------------------------------------".PHP_EOL;
                }//end foreach
        }//end if(!empty($lista_ids))
        echo "=====================================================".PHP_EOL;
        echo "IDENTIFICADOR DO EMAIL: " . $this->getIdentificadorEmail() . PHP_EOL;
        echo "=====================================================".PHP_EOL;
        echo "Quantidade total de Emails:: " . count($aux_ids_aux) . PHP_EOL;
        //$lista_emails .= "<br>Quantidade total de Emails: [".count($aux_ids_aux)."]</html>";
        //enviaEmail("wagner@e-prepag.com.br,joao.trevisan@e-prepag.com.br", null, null, "Resumo de Envio de Email ".$this->getIdentificadorEmail(), $lista_emails);

    }//end function MontaEmail


    public function MontaEmailEspecifico($attachment = null, $stringAttach = false, $name = '', &$sendStatus = false, $nome = '') {
	 
        $subjectEmail = $this->getSubjectEmail($this->getIdentificadorEmail());

        //Verificando se existe informação adicional para o subject do e-mail
        if ($this->getSubjectAdicional()) {
            $subjectEmail .= " " . $this->getSubjectAdicional();
        }//end if($this->getSubjectAdicional())

        //Verificando se já foi setado o email para não buscar informações pelo UG_ID
        if ($this->getUgEmail() == "") {
            $this->getUserDados();
        }//end if(empty($this->getUgEmail()))

        $this->setLogEnvioEmail();

        $msgEmail = $this->getCorpoEmail();

        $ug_email = $this->getUgEmail();

        $bcc = "";
        //envio de email para monitoramento
        if ($this->getBccAdicional()) {
            $bcc .= (($bcc) ? "," : "") . $this->getBccAdicional();
        }

        //envio do email
        if (is_null($attachment)) {
            if($nome == ''){
                enviaEmail($ug_email, null, $bcc, $subjectEmail, $msgEmail);
            }else{
                enviaEmail($ug_email, null, $bcc, $subjectEmail, $msgEmail, $nome);
            }
            $sendStatus = true;
        }//end if(is_null($attachment))
        else {
		
		            $envio = enviaEmail4($ug_email, null, $bcc, $subjectEmail, $msgEmail, null, $attachment, $stringAttach, $name);
					
                    if ($envio) {
					
                        if ( !$stringAttach ) {
                            $sendStatus = true;
                            echo "Email enviado com sucesso para $ug_email.<br>";
                        }
                    } else {
				    
                        if ( !$stringAttach ) {
						
                            $sendStatus = false;
                            echo "<font color='#E80000'>Problema no envio do EMAIL!</font><br>";
                        }
                    }
        }//end else do if(is_null($attachment))

    }//end function MontaEmailEspecifico()


    public function getCorpoEmail() {

        $tmp_vetor = $this->getTagsCorpoEmail($this->getIdentificadorEmail());

        $tmp_corpo_email = $this->getCorpoEmailTemplate($this->getIdentificadorEmail()); //UserProperty::
        
        foreach ($tmp_vetor as $key => $value) {
            eval("\$aux_value = " . $value . ";");
            $tmp_corpo_email = str_replace($key, $aux_value, $tmp_corpo_email);
        }//end foreach
        
        return $tmp_corpo_email;
    }//end function getCorpoEmail


    public function getCorpoEmailTodos($tipo = null) {
        global $raiz_do_projeto;
        $return = "<style type='text/css'>
                    <!--
                    .vetor {
                                font-family: Arial, Helvetica, sans-serif;
                                font-size: 12px;
                                text-align:justify;
                                color: #272A74;
                            }
                    -->
                    </style><div class='vetor'><br>";
        if (is_null($tipo)) {
            foreach ($this->getCorpoEmailFull() as $key => $val) {
                if(!empty($val)){
                    $return .= '<h4>'.$key.'</h4><div style="width:100%; height: 550px;"><iframe style="width:100%; height: 100%;" src="/includes/imprimeTemplate.php?var='.$val.'"></iframe></div>';
                }
            }//end foreach
        }//end if(is_null($tipo)) 
        else {
            $return .= "" . $tipo . "<br><br>" . $this->getCorpoEmailTemplate($tipo) . "<br><br><br>";
        }
        $return .= "<div><br>";
        return $return;
    }//end function getCorpoEmailTodos()

    public function getVetorIdentificacao() {
        unset($vetor);
        foreach ($this->getCorpoEmailFull() as $key => $val) {
            $vetor[] = $key;
        }
        asort($vetor);
        return $vetor;
    }//end function getVetorIdentificacao() 

}//end class EnvioEmailAutomatico

?>
