<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
if(!strpos($_SERVER['HTTP_REFERER'],"prepag2/commerce/pagamento_int.php")) {
    @session_start();
    session_destroy();    
}

// Correcao bug sessao Internet Explorer 6,7,8
//header('P3P: CP="CAO PSA OUR"');
$nonce = base64_encode(openssl_random_pseudo_bytes(16));
header("Content-Type: text/html; charset=ISO-8859-1; P3P: CP='CAO PSA OUR'", true);
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; style-src 'self'");

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_CLASS . "gamer/classIntegracao.php";
require_once DIR_CLASS . "gamer/classLimite.php";
require_once DIR_INCS . "gamer/functions_endereco.php";
$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');

require_once DIR_INCS . "inc_register_globals.php";    	
require_once DIR_INCS . "config.MeiosPagamentos.php";	

ob_end_flush();

//Definindo valor Default no caso do include estar conrrompido
if(!defined('PAGAMENTO_BRADESCO')) {
    //Definindo como ativado
    define('PAGAMENTO_BRADESCO',1);
}// end if
if(!defined('PAGAMENTO_BANCO_BRASIL')) {
    //Definindo como ativado
    define('PAGAMENTO_BANCO_BRASIL',1);
}// end if
if(!defined('PAGAMENTO_ITAU')) {
    //Definindo como ativado
    define('PAGAMENTO_ITAU',1);
}// end if
if(!defined('PAGAMENTO_BOLETO')) {
    //Definindo como ativado
    define('PAGAMENTO_BOLETO',1);
}// end if
if(!defined('PAGAMENTO_EPREPAG_CASH')) {
    //Definindo como ativado
    define('PAGAMENTO_EPREPAG_CASH',1);
}// end if
if(!defined('PAGAMENTO_CIELO')) {
    //Definindo como ativado
    define('PAGAMENTO_CIELO',1);
}// end if
if(!defined('PAGAMENTO_PIX')) {
    //Definindo como ativado
    define('PAGAMENTO_PIX',1);
}// end if

$btSubmit_EPP_8593 = $_REQUEST['btSubmit_EPP_8593'];

$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
$partner_produto_id = 0;

$integracao_logo_img = "";

$b_debug_trace = false;		// true; //
$b_BancoEPP = false;


if(! ($btSubmit_EPP_8593 || $iforma) ){

        if(isset($_SESSION['carrinho_val'])) {
                $_SESSION['carrinho_val'] = null;
                unset($_SESSION['carrinho_val']);
        }

        if($b_debug_trace) echo "_SERVER['HTTP_REFERER']: ".$_SERVER['HTTP_REFERER']."<br>";

        grava_log_integracao(str_repeat("=", 80)."\nIntegração INICIAR: ".date("Y-m-d H:i:s")." \n");

        set_Integracao_marca_sessao_logout();
        $parceiro_pedido_valido = false;

        // Obtem valores após fazer login do usuário
        $integracao_is_parceiro = "";	//	"OK";
        $integracao_origem_id = "";		//	$integracao_store_id;

        // Identifica os casos onde o pedido não vem da loja, mas de um parceiro
        $bret = is_Integracao();

        // Não testamos origem de integração no gateway de pagamento
        if(true) {	//		if(is_Integracao()) {
                if($b_debug_trace) echo "Pedido do Parceiro<br>";
                grava_log_integracao("Integração +++ Passou is_Integracao(): ".date("Y-m-d H:i:s")."\n");

                // Obtem a origem a partir do IP, do HTTP_Referer e outra informação que seja necessária
                if(is_Integracao_valida()) {

                        if($b_debug_trace) echo "Pedido do Parceiro válido<br>";
                        grava_log_integracao("Integração +++ Passou is_Integracao_valida(): ".date("Y-m-d H:i:s")."\n");

                        // Está vindo de Parceiro para pagamento -> Cancela sessão, caso exista de visita anterior
                        cancelarSessao();

                        // Obter parceiro_params a partir de $_POST
                        $parceiro_params = get_Integracao_params_from_POST();

                        // Valida dados de POST
                        $mensagem = "";
                        if(is_Integracao_params_valida($parceiro_params,$mensagem)) {

                                if($b_debug_trace) echo "Pedido do Parceiro válido com POST válido<br><pre>".print_r($parceiro_params, true)."</pre><br>";
                                grava_log_integracao("Integração +++ Passou is_Integracao_params_valida(): ".date("Y-m-d H:i:s")."\n".print_r($parceiro_params, true)."\n");

                                // Transfere dados de parceiro para nosso ambiente
                                $integracao_store_id			= trim($parceiro_params["store_id"]);

                                $integracao_currency_code		= trim($parceiro_params["currency_code"]);
                                $integracao_order_id			= trim($parceiro_params["order_id"]);
                                $integracao_order_description	= trim($parceiro_params["order_description"]);
                                $integracao_amount				= trim($parceiro_params["amount"]);
                                $integracao_product_id			= trim($parceiro_params["product_id"]);
                                $integracao_client_email		= trim($parceiro_params["client_email"]);
                                $integracao_cliente_id			= trim($parceiro_params["client_id"]);
                                $integracao_parceiro_params		= serialize($parceiro_params);

                                // Sanitize email
                                $integracao_client_email = sanitize_general($integracao_client_email);

                                // Testa se order_id já existe para este parceiro
                                $b_bloqueia_por_order_existe = b_order_exists($integracao_store_id, $integracao_order_id);
//					echo (($b_bloqueia_por_order_existe) ?"S":"N");

                                // Por agora trabalhamos apenas com um item em cada pedido
                                $integracao_qtde				= 1;

                                // Modelo de produto
                                $valor							= $integracao_amount/100;
                                $valor_tmp						= $valor;

                                // Se publisher usa valores livres (ouy seja, não aqueles dos PINs cadastrados) então grava em carrinho_val
                                $b_amount_free = getPartner_amount_free_By_ID($integracao_store_id);

                                // Para evitar usar o valor na consulta de modelos
                                if($b_amount_free=="1") {
                                        $valor_tmp	= null;
                                }

//				Dummy 				$valor = 23;
                                $integracao_iativo				= 1;
                                $integracao_mod					= get_Integracao_modelo($integracao_store_id, $valor_tmp, $integracao_iativo, $s_prod_nome, $integracao_product_id);
                                if($b_debug_trace) echo "integracao_mod: $integracao_mod<br>";

                                // Modelo foi encontrado?
                                if($integracao_mod>0) {
                                        grava_log_integracao("Integração +++ Passou integracao_mod>0 (integracao_mod: '$integracao_mod'): ".date("Y-m-d H:i:s")."\n");

                                        // Fazer login para o usuário ou cadastrar um novo usuário
                                        $idcliente = (new UsuarioGames)->existeEmail_get_ID($integracao_client_email);

                                        //echo "integracao_client_email: '$integracao_client_email'<br>";
                                        grava_log_integracao("IN pagamento_int.php: integracao_client_email: '$integracao_client_email'\n");
                                        if($b_debug_trace) echo "idcliente: $idcliente<br>";
                                                                //die("Stop");

                                        if($idcliente>0) {
                                                // Faz login de usuário
                                //		$idcliente = 9093;	=> reinaldops@hotmail.com
                                                grava_log_integracao("Integração +++ Passou idcliente>0 (idcliente: '$idcliente'): ".date("Y-m-d H:i:s")."\n");

                                                // Simulate an error when login in an user
                                                if(strtoupper($integracao_client_email)=="WALTER@MAIL.COM") {
                                                        $bret = false;
                                                } else {
                                                        $bret = (new UsuarioGames)->adicionarLoginSession_ByID($idcliente);
                                                }

                                                if($bret) {
                                                        grava_log_integracao("Integração +++ Passou UsuarioGames::adicionarLoginSession_ByID($idcliente): ".date("Y-m-d H:i:s")."\n");
                                                        // Atualiza último acesso
                                                        (new UsuarioGames)->atualiza_ultimo_acesso($integracao_client_email);

                                                         // Marca esta transação como vinda do parceiro
                                                        set_Integracao_marca_sessao_login($integracao_store_id, $integracao_order_id);

                                                        $integracao_is_parceiro = "OK";
                                                        $integracao_origem_id = $integracao_store_id;

                                                        $integracao_logo_img = getPartner_partner_img_logo_By_ID($integracao_store_id, $integracao_product_id);
                                                        $partner_name = get_Integracao_nome_parceiro($integracao_store_id);

                                                        $msg_success = "Usuário '$integracao_client_email' logado.";

                                                        grava_log_integracao("Integração LOGIN ACEITO: ".date("Y-m-d H:i:s")."\n  idcliente: $idcliente\n  integracao_client_email: $integracao_client_email\n");

                                                } else {
                                                        set_Integracao_error_msg("Integration request failed: client not allowed to login (1)", true);
                                                        set_Integracao_marca_sessao_logout();
                                                        $msg_success = "Usuário '$integracao_client_email' não fez login.";

                                                        grava_log_integracao("Integração LOGIN NEGADO (NOVO USUÁRIO1): ".date("Y-m-d H:i:s")."\n  UsuarioGames::adicionarLoginSession_ByID($idcliente) -> $msg_success)\n");

                                                        // Debug
                                                        send_debug_info_by_email("E-Prepag - Testing integration - Error 12", "Error: Integration request failed: client not allowed to login", 0);

                                                }
                                        } else {
                                                grava_log_integracao("Integração Debug 1_pag_int: ".date("Y-m-d H:i:s")."\n  cadastra novo usuário vindo de Integração, apenas email \n  integracao_store_id: $integracao_store_id\n  integracao_client_email: $integracao_client_email \n");

                                                // cadastra novo usuário vindo de Integração, apenas email
                                                $ug_id_novo = (new UsuarioGames)->inserir_simple($integracao_store_id, $integracao_client_email);

                                                grava_log_integracao("Integração Debug 2: ".date("Y-m-d H:i:s")."\n  msg: $msg \n");
                                                if($ug_id_novo>0) {
                                                        //"faz login"
                                                        (new UsuarioGames)->adicionarLoginSession($integracao_client_email);

                                                        grava_log_integracao("Integração +++ Passou UsuarioGames::adicionarLoginSession($integracao_client_email): ".date("Y-m-d H:i:s")."\n");

                                                        // Envia email de novo uisuário 
                                                        $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']); 
                                                        if($usuarioGames) {
                                                                $ug_id = $usuarioGames->getId();

                                                                /* ---Wagner */
                                                                $promo_msg = "";
                                                                $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER,'SenhaIntegracao');			
                                                                $objEnvioEmailAutomatico->setPromocoes($promo_msg);

                                                                $objEnvioEmailAutomatico->setUgID($ug_id);

                                                                echo $objEnvioEmailAutomatico->MontaEmailEspecifico();
                                                                /* -- Fim Wagner */

                                                        }

                                                         // Marca esta transação como vinda do parceiro
                                                        set_Integracao_marca_sessao_login($integracao_store_id, $integracao_order_id);
                                                        $integracao_is_parceiro = "OK";
                                                        $integracao_origem_id = $integracao_store_id;

                                                        $msg_success = "Usuário '$integracao_client_email' cadastrado.";

                                                        grava_log_integracao("Integração LOGIN ACEITO (NOVO USUÁRIO2): ".date("Y-m-d H:i:s")."\n  integracao_client_email: $integracao_client_email ($msg_success)\n");
                                                } else {
                                                        set_Integracao_error_msg("Integration request failed: new client not allowed to login (2)", true);
                                                        grava_log_integracao("Integração LOGIN NEGADO (NOVO USUÁRIO): ".date("Y-m-d H:i:s")."\n  integracao_client_email: $integracao_client_email (inserir_simple() -> $msg)\n");

                                                        send_debug_info_by_email("E-Prepag - Testing integration - Error 22", "Integration request failed: new client not allowed to login", 0);

                                                }
                                        }


                                        if($integracao_is_parceiro=="OK" && $integracao_origem_id != "") {
                                                // Monta carrinho para Integração
                                                // $carrinho = get_Integracao_carrinho($parceiro_params);
                                                // Inicia carrinho do session
                                                $carrinho = array();
                                                $carrinho_val = array();

                                                //verifica se o modelo existe e esta ativo	
                                                $rs = null;
                                                // Está usando o PIN de treinamento que está desativado (ver abaixo $total_carrinho = mostraCarrinho_pag(false, 0);)
                                                $filtro['ogpm_ativo'] = $integracao_iativo;		
                                                $filtro['ogpm_id'] = $integracao_mod;
                                                $filtro['com_produto'] = true;
                                                $ret = (new ProdutoModelo)->obter($filtro, null, $rs);

                                                //Adiciona modelo no carrinho
                                                if($rs && pg_num_rows($rs) == 1){
                                                        // acrescenta 1 item ao produto (ogpm.ogpm_id = $integracao_mod)
                                                        $carrinho[$integracao_mod] = 1;

                                                        // Se publisher usa valores livres (ou seja, não aqueles dos PINs cadastrados) então grava em carrinho_val
                                                        if($b_amount_free=="1") {
                                                                // acrescenta 1 item ao produto (ogpm.ogpm_id = $integracao_mod)
                                                                $carrinho_val[$integracao_mod] = $parceiro_params['amount'];
                                                                // Salva novo carrinho
                                                                $_SESSION['carrinho_val'] = $carrinho_val;
                                                        }

                                                        // Salva novo carrinho
                                                        $_SESSION['carrinho'] = $carrinho;

                                                        // unico caso onde o pedido do parceiro foi aceito
                                                        $parceiro_pedido_valido = true;

                                                        // Obtem o produto cadastrado para o parceiro
                                                        $partner_produto_id = getPartner_produto_id_By_ID($integracao_store_id);
                                                        // Se estamos levantando produtos inativos (=> Treinamento) então mostra o "Banco EPP de Teste"
                                                        // $b_BancoEPP = ($partner_produto_id==$INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID);
                                                        if (($_SESSION['integracao_is_parceiro']=="OK" && $_SESSION['integracao_origem_id'] && $_SESSION['integracao_order_id'] && ($partner_produto_id==$INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID)) || $iforma == 'Z') {
                                                                $b_BancoEPP = true;
                                                        }

                                                        // Chegou aqui -> pedido aprovado -> salva dados do pedido
                                                        set_Integracao_registro(); //aqui nessa função que gera o pedido de integração no bd
                                                } else {
                                                        if($b_debug_trace) echo "Modelo não encontrado 2<br>";
                                                        set_Integracao_error_msg("Integration request failed: model not found (2)", true);
                                                        // Modelo não encontrado -> não va emfrente 
                                                        grava_log_integracao("Integração Modelo não encontrado (A): ".date("Y-m-d H:i:s")."\n  integracao_mod: $integracao_mod\n  integracao_amount: $integracao_amount\n  valor: $valor\n");

                                                        send_debug_info_by_email("E-Prepag - Testing integration - Error 23", "Integration request failed: model not found (B)", 0);

                                                }
                                        } else {
                                                set_Integracao_error_msg("Integration request failed: user didn't login successfully ($integracao_client_email)", true);
                                                // Modelo não encontrado -> não va emfrente 
                                                grava_log_integracao("Integração falhou - Usuário não fez login: ".date("Y-m-d H:i:s")."\n  integracao_mod: $integracao_mod\n  integracao_client_email: $integracao_client_email\n");

                                                send_debug_info_by_email("E-Prepag - Testing integration - Error 48", "Integration request failed: user didn't login successfully ($integracao_client_email)", 0);
                                        }

                                } else {
                                        if($b_debug_trace) echo "Modelo não encontrado 1<br>";
                                        set_Integracao_error_msg("Integration request failed: model not found (1)", true);
                                        // Modelo não encontrado -> não va emfrente 
                                        grava_log_integracao("Integração Modelo não encontrado (B): ".date("Y-m-d H:i:s")."\n  integracao_amount: $integracao_amount\n  valor: $valor\n  integracao_mod: $integracao_mod\n");

                                        // Debug
                                        send_debug_info_by_email("E-Prepag - Testing integration - Error 32", "Error: Integration request failed - model not found (A)", 0);
                                }

                        } else {
                                if(empty($mensagem)) {
                                set_Integracao_error_msg("Integration request failed: invalid parameters (1)", true);
                                }
                                else {
                                    set_Integracao_error_msg($mensagem, true);
                                }
                                grava_log_integracao("Integração recusada - Parametros inválidos: ".date("Y-m-d H:i:s")."\n  store_id: ".$parceiro_params["store_id"]."\n");

                                // Debug
                                send_debug_info_by_email("E-Prepag - Testing integration - Error 43", "Error: invalid parameters", 0);

                        }
                } else {
                        set_Integracao_error_msg("Integration request failed: invalid source (1)", true);
                        grava_log_integracao("Origem de integração inválida -> Sem login - ".date("Y-m-d H:i:s")."\n  store_id: ".$parceiro_params["store_id"]."\n");
                        if($b_debug_trace) echo "Pedido do Parceiro Inválido<br>";

                        // Debug
                        send_debug_info_by_email("E-Prepag - Testing integration - Error 32", "Error: invalid source", 0);
                }

                if(!$parceiro_pedido_valido) {
                        $msg = "Desculpe, o seu pedido não foi aceito, contate o administrador <a href='mailto:atendimento@e-prepag.com.br'>atendimento@e-prepag.com.br</a> (".date("Y-m-d H:i:s").")";
                }
        } else {
                grava_log_integracao("Integração is_Integracao() falhou: ".date("Y-m-d H:i:s")." \n");
                set_Integracao_error_msg("Integration request failed: unknown source (1)", true);
                // O pedido vem de dentro da Loja ou direto do navegador -> situação normal
                if($b_debug_trace) echo "Pedido da Loja<br>";

                // Debug
                send_debug_info_by_email("E-Prepag - Testing integration - Error 45", "Error: Integration request failed - unknown source information", 0);
        }
        grava_log_integracao("\nIntegração CONCLUIR COM SUCESSO: ".date("Y-m-d H:i:s")." \n".str_repeat("-", 80)."\n\n");

} else {

        // Após escolher o banco procura dados de integração para liberar 'Banco EPP'
        if (($_SESSION['integracao_is_parceiro']=="OK" && $_SESSION['integracao_origem_id'] && $_SESSION['integracao_order_id'] && ($partner_produto_id==$INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID)) || $iforma == 'Z') {
                $b_BancoEPP = true;
        }
}

if (($_SESSION['integracao_is_parceiro']=="OK" && $_SESSION['integracao_origem_id'] && $_SESSION['integracao_order_id'] && ($partner_produto_id==$INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID)) || $iforma == 'Z') {
        $b_BancoEPP = true;
}

// Após processar login de integração -> valida sessão como sempre
validaSessao(); 

$GLOBALS['_SESSION']['is_integration'] = true;

$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);


//Recupera dados do session
$pagto = $_SESSION['pagamento.pagto'];
$pagto_ja_fiz = $_SESSION['pagamento.pagto_ja_fiz'];
$parcelas_REDECARD_MASTERCARD = $_SESSION['pagamento.parcelas.REDECARD_MASTERCARD'];
$parcelas_REDECARD_DINERS = $_SESSION['pagamento.parcelas.REDECARD_DINERS'];
$integracao_is_parceiro = $_SESSION['integracao_is_parceiro'];
$integracao_origem_id = $_SESSION['integracao_origem_id'];
$integracao_order_id = $_SESSION['integracao_order_id'];

if($usuarioGames->b_IsLogin_pagamento_Elex_nova_pagina()) {
        $s_bloqueia_Elex = "";
        $b_bloqueia_Elex = false;
        if($integracao_origem_id=="10411") {
                $b_bloqueia_Elex = true;

                $s_bloqueia_Elex = "";
                $s_bloqueia_Elex .= "<center>\n";
                $s_bloqueia_Elex .= "<table  border='0' cellspacing='0' bgcolor='#FFFFFF' width='100%'>\n";
                $s_bloqueia_Elex .= "<tr>\n";
                $s_bloqueia_Elex .= "  <td align='center' class='texto' width='10%'>&nbsp;\n";
                $s_bloqueia_Elex .= "  </td>\n";
                $s_bloqueia_Elex .= "  <td align='left' valign='top' class='texto'>\n";
                $s_bloqueia_Elex .= get_msg_bloqueio_elex();
                $s_bloqueia_Elex .= "  </td>\n";
                $s_bloqueia_Elex .= "  <td align='center' class='texto' width='10%'>&nbsp;\n";
                $s_bloqueia_Elex .= "  </td>\n";
                $s_bloqueia_Elex .= "</tr>\n";
                $s_bloqueia_Elex .= "</table>\n";
                $s_bloqueia_Elex .= "</center>\n";
        }
}


if($btSubmit_EPP_8593 || $iforma){

        //Variaveis do formulario
        $pagto = (empty($_SESSION['pagamento.pagto'])?$_POST['pagto']:$_SESSION['pagamento.pagto']);
        $pagto_ja_fiz = (empty($_SESSION['pagamento.pagto_ja_fiz'])?$_POST['pagto_ja_fiz']:$_SESSION['pagamento.pagto_ja_fiz']);
        $parcelas_REDECARD_MASTERCARD = (empty($_SESSION['pagamento.parcelas.REDECARD_MASTERCARD'])?$_POST['parcelas_REDECARD_MASTERCARD']:$_SESSION['pagamento.parcelas.REDECARD_MASTERCARD']);
        $parcelas_REDECARD_DINERS = (empty($_SESSION['pagamento.parcelas.REDECARD_DINERS'])?$_POST['parcelas_REDECARD_DINERS']:$_SESSION['pagamento.parcelas.REDECARD_DINERS']);
        $integracao_is_parceiro = (empty($_SESSION['integracao_is_parceiro'])?$_POST['integracao_is_parceiro']:$_SESSION['integracao_is_parceiro']);
        $integracao_origem_id = (empty($_SESSION['integracao_origem_id'])?$_POST['integracao_origem_id']:$_SESSION['integracao_origem_id']);
        $integracao_order_id =  (empty($_SESSION['integracao_order_id'])?$_POST['integracao_order_id']:$_SESSION['integracao_order_id']);

        if(!$pagto && $iforma) $pagto = $iforma;

        //Validacao
        $msg = "";

        //Valida opcao de pagamento
        if($msg == ""){
                if(!$pagto || $pagto == "" || (strlen($pagto)!=1)) $msg = "Selecione a forma de pagamento.";
        }

        //Validacao formas de pagamento		
        if($msg == ""){
                if($b_BancoEPP) {
                        if(!in_array($pagto, $FORMAS_PAGAMENTO) && ($pagto!=$PAGAMENTO_BANCO_EPP_ONLINE) ) $msg = "Forma de pagamento inválida (#'$pagto').";
                } else {
                        if(!in_array($pagto, $FORMAS_PAGAMENTO)) $msg = "Forma de pagamento inválida (*'$pagto').";
                }
        }

        //Adiciona dados no session
        if($msg == ""){

                $_SESSION['pagamento.pagto'] = $pagto;
                $_SESSION['pagamento.pagto_ja_fiz'] = $pagto_ja_fiz;
                $_SESSION['pagamento.parcelas.REDECARD_MASTERCARD'] = $parcelas_REDECARD_MASTERCARD;
                $_SESSION['pagamento.parcelas.REDECARD_DINERS'] = $parcelas_REDECARD_DINERS;
                $_SESSION['integracao_is_parceiro'] = $integracao_is_parceiro;
                $_SESSION['integracao_origem_id'] = $integracao_origem_id;
                $_SESSION['integracao_order_id'] = $integracao_order_id;
                $_SESSION['integracao_client_email'] = $integracao_client_email;
                
                if(($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) && 
                   (trim($usuarioGames->getCEP()) == "" || 
                    trim($usuarioGames->getEndereco()) == "" ||  
                    trim($usuarioGames->getNumero()) == "" || 
                    trim($usuarioGames->getBairro()) == "" || 
                    trim($usuarioGames->getCidade()) == "" || 
                    trim($usuarioGames->getEstado()) == "") )
                {
                    $completar_endereco = true;
                }
                
                if($completar_endereco){
?>
                    <div class="container txt-azul-claro bg-branco">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12 txt-azul-claro top10">
                                        <span class="glyphicon glyphicon-triangle-right graphycon-big" aria-hidden="true"></span><strong>Preencher Dados de Endereço</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 espacamento">
<?php
                            endereco_page_transf($completar_endereco, TRUE);
                }
                else{
                    // Esta página pode ser vista em HTTPS quando atende um pedido de parceiros, a próxima deve estar em HTTP
                    $strRedirect = $https."://".$_SERVER['SERVER_NAME']."/prepag2/commerce/finaliza_venda_int.php";
                    redirect($strRedirect);
                }
        } 

}

$pagina_titulo = "Escolher forma de pagamento";

//Recupra carrinho do session
if (count($carrinho)==0) { $carrinho = $_SESSION['carrinho']; }

foreach ($carrinho as $modeloId => $qtde){

        $qtde = intval($qtde);
        $rs = null;
        $filtro['ogpm_ativo'] = 1;
        $filtro['ogpm_id'] = $modeloId;
        $filtro['com_produto'] = true;
        $ret = (new ProdutoModelo)->obter($filtro, null, $rs);
        if($rs && pg_num_rows($rs) != 0){
                $rs_row = pg_fetch_array($rs);
                $ogpm_valor = (($b_amount_free=="1")?($carrinho_val[$modeloId]/100.):$rs_row['ogpm_valor']); 
                $cart = array();
                $product = $rs_row['ogpm_nome'];
                $iof = (new Produto)->buscaIOF($modeloId) ? "incluso" : "";
                $price = number_format($ogpm_valor*$qtde, 2, ',', '.');
                $cart[] = array( "product"=>$product, "iof"=>$iof, "price"=>$price );

        }
}

// Define variavel de sessao com dados do carrinho
$GLOBALS['_SESSION']['int_cart'] = $cart;

require_once RAIZ_DO_PROJETO . "public_html/prepag2/commerce/includes/cabecalho_int.php"; 
?>
<style type="text/css" nonce="<?php echo $nonce; ?>">
<!--
.style5 {font-size: 9px; font-family: Arial, Helvetica, sans-serif; font-weight: bold; }
.style8 {color: #0000FF}
.style10 {font-size: 10px; font-family: Arial, Helvetica, sans-serif; font-weight: bold; }
.style11 {font-family: Arial, Helvetica, sans-serif; font-size: 10px; }

.style20 {font-size: 11px; font-family: Arial, Helvetica, sans-serif; font-weight: bold; }
.style21 {font-size: 10px; font-family: Arial, Helvetica, sans-serif; }
.style21entrega {color: darkgreen; font-size: 10px; font-family: Arial, Helvetica, sans-serif; }
.style22 {font-size: 11px; font-family: Arial, Helvetica, sans-serif; color: #0000CC; }
.style23 {font-size: 11px; font-family: Arial, Helvetica, sans-serif; color: #FF0000; }
.style24 {font-size: 11px; font-family: Arial, Helvetica, sans-serif; color: #000000; }

.linkout {background-color: #fff;} 
.linkover {background-color:#CCFFCC;}
.linkout2 {background-color: #FFCC66;} 

.dnone {display: none;}

#boxPopUpSaibaMais {
			z-index: 2;
			height: 520px;
			width: 680px;
			color: #000000;
			font-size: 14px;
			background-color: #FFFFFF;
			border: 1px solid #444;
			padding: 5px;
			position: fixed;
			top: 5%;
			left: 26%;
			text-align: left;
			display: none;
			overflow: auto;
			}

#boxPopUpSaibaMaisCielo {
			z-index: 2;
			height: 280px;
			width: 320px;
			color: #000000;
			font-size: 14px;
			background-color: #FFFFFF;
			border: 1px solid #444;
			padding: 5px;
			position: fixed;
			top: 280px;
			left: 60%;
			text-align: left;
			display: none;
			overflow: auto;
			}
.img-responsive {
    margin: 0 auto;
}
-->
</style>
<?php
	if(!$b_bloqueia_por_order_existe) {
?>
<script language="Javascript" nonce="<?php echo $nonce; ?>">
        $('#mostraLista').hide();

	function load_saibamais() {
		$('#boxPopUpSaibaMais').load("/game/instr_pcerto.php").show();
	}
	function fecha() {
		$('#boxPopUpSaibaMais').hide();
	}

	function load_saibamais_cielo() {
                var valor = $('#valorCielo').val();
		$('#boxPopUpSaibaMaisCielo').load("/game/saiba_mais_cielo.php?valor="+valor).show();
	}
	function fecha_cielo() {
		$('#boxPopUpSaibaMaisCielo').hide();
	}

<?php
	if(is_object($usuarioGames)) {
		if($usuarioGames->b_IsLogin_pagamento())  {

?>
	function save_shipping(iforma, id, sno) {

		document.form1.iforma.value = iforma;
		document.form1.idu.value = id;
		document.form1.sno.value = sno;
		document.form1.btSubmit_EPP_8593.value = "Continuar";

		for(var i=0; i < document.form1.pagto.length; i++){
			if(document.form1.pagto[i].value==iforma) {
				document.form1.pagto[i].checked = true;
			} else {
				document.form1.pagto[i].checked = false;
			}
		}
		document.form1.submit();
	}
        function check_deposito(checkbox){
                if(checkbox.checked == true){
                    save_shipping(<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'] ?>, <?php echo (($usuarioGames->getId()>0)?$usuarioGames->getId():"0") ?>, '<?php echo $usuarioGames->getNome() ?>');
                }
        }
<?php
		} 
	} 

?>

</script>
<div class="row">
    <div class="col-xs-12 col-sm-12 txt-azul-claro top10">
        <strong>Escolha a forma de pagamento</strong>
    </div>
</div>
<div class="row top20">
    <div class="col-xs-12 txt-vermelho">
        * Clique na imagem referente ao método desejado
    </div>
</div>
<!--Div geral para ocultar dados da pagina no caso de redirecionamento automatico -->
<div id="mostraLista" class="">
    <form name="form1" action="" method="post">
	<!--Div Box que exibe Saiba Mais PINs EPP -->
	<div id="boxPopUpSaibaMais"></div>
	<!--Div Box que exibe Saiba Mais CIELO -->
	<div id="boxPopUpSaibaMaisCielo"></div>

	<?php // Tabela com carrinho - Start ?>
	<table  border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%" style="display: none;">
    <tr><td><span id="img_logo_inategracao" name="img_logo_inategracao"></span>&nbsp;</td></tr>
    <tr valign="top" align="center">
      <td>
  		<?php if($msg != ""){?>
		<table border="0" cellspacing="0" align="center">
        <tr valign="middle" bgcolor="#FFFFFF">
          	<td align="left" class="texto_vermelho"><?php echo str_replace("\n", "\n<br>", $msg)?></td>
        </tr>
		</table>
		<?php  } 
        
        if($msg_success != "" || true){ ?>

		<table border="0" cellspacing="0" align="center">
        <tr valign="middle" bgcolor="#FFFFFF">
          	<td align="left" width="100"><?php echo (($integracao_logo_img)?"<img src='" . $integracao_logo_img ."' border='0' title='".$partner_name."'>":"$partner_name") ?></td>
          	<td align="left" width="20">&nbsp;</td>
          	<td align="left" style="color:#0000CC; font-size:12px"><?php //echo str_replace("\n", "\n<br>", $msg_success)?>&nbsp;</td>
          	<td align="left" width="20">&nbsp;</td>
          	<td align="right" class="texto"><a href="#" onClick="window.open('forma_pagto_prz_entrega.php','prazo','height=500,width=550,top=0,left=0,scrollbars=1');return false;" class="link_azul">Prazo de Entrega</a>&nbsp;&nbsp;&nbsp;</td>
        </tr>
		</table>
		<?php  } ?>
	<br>
<?php
		
$b_nova_forma_pagamento = false;
if(is_object($usuarioGames)) {

    //Recupera carrinho do session
    if($usuarioGames->b_IsLogin_pagamento())  {
        $b_nova_forma_pagamento = true;

        //verifica se o publisher possui restrição ao meio de pagamento 
        $libera_pagamento = array(
                        'BancodoBrasil'         => true,
                        'BancoItau'             => true,
                        'Bradesco'              => true,
                        'Hipay'                 => true,
                        'Paypal'                => true,
                        'Boleto'                => true,
                        'Deposito'              => true,
                        'EppCash'               => true,
                        'Cielo'                 => true,
                        'Cielo_Visa_DEB'        => true,
                        'Cielo_Visa_CRED'       => true,
                        'Cielo_Master_DEB'      => true,
                        'Cielo_Master_CRED'     => true,
                        'Cielo_Elo_DEB'         => true,
                        'Cielo_Elo_CRED'        => true,
                        'Cielo_Diners_CRED'     => true,
                        'Cielo_Discover_CRED'   => true,
                        'Pix'                   => true,
						'Pagamento_personalizado' => true
                        );
                                                
        // Obtem o valor total deste pedido
        // Está usando o PIN de treinamento que está desativado (ver acima ProdutoModelo::obter($filtro, null, $rs))
        $total_carrinho = mostraCarrinho_pag(false, $integracao_iativo, $libera_pagamento);	
        // ==========================================================================================
        // Faz validação de vendas totais, repetir em finaliza_vendas.php, antes de aceitar o pedido

        // Testa que só tem produtos Habbo e GPotato no carrinho
        //$b_IsProdutoOK = bCarrinho_ApenasProdutosOK(1);	// não usa mais

        // Testa que usuário comprou no máximo 10 vezes nas últimas 24 horas
        $qtde_last_dayOK = getNVendasMoney($usuarioGames->getId());

        // Calcula o total nas últimas 24 horas para pagamentos Online 
        $total_diario = getVendasMoneyTotalDiarioOnline($usuarioGames->getId());

//	$RISCO_GAMERS_VIP_TOTAL_DIARIO = 1000;
//	$RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO = 20;
        if($usuarioGames->b_IsLogin_pagamento_free()) {
            $total_diario_const = $RISCO_GAMERS_FREE_TOTAL_DIARIO;
            $pagamentos_diario_const = $RISCO_GAMERS_FREE_PAGAMENTOS_DIARIO;
        } elseif($usuarioGames->b_IsLogin_pagamento_vip()) {
            $total_diario_const = $RISCO_GAMERS_VIP_TOTAL_DIARIO;
            $pagamentos_diario_const = $RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO;
        } else {
            $total_diario_const = $RISCO_GAMERS_TOTAL_DIARIO;
            $pagamentos_diario_const = $RISCO_GAMERS_PAGAMENTOS_DIARIO;
        }

        $b_TentativasDiariasOK = ($qtde_last_dayOK<=$pagamentos_diario_const);
        $b_LimiteDiarioOK = (($total_carrinho+$total_diario)<=$total_diario_const);
        $b_ValorBoletoOK = ($total_carrinho<=$RISCO_GAMERS_BOLETOS_TOTAL_DIARIO);
        $b_ValorDepositoOK = ($total_carrinho<=$RISCO_GAMERS_DEPOSITOS_TOTAL_DIARIO);

        // Libera pagamento Online Banco do Brasil
        $b_bloqueado_BBR9 = b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']);
        $b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $usuarioGames->b_IsLogin_pagamento_bancodobrasil() && (!$b_bloqueado_BBR9) && $libera_pagamento['BancodoBrasil'];

        // Libera pagamento Online Banco Itaú
        $b_bloqueado_BITA = b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']);
        $b_libera_BancoItau = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $usuarioGames->b_IsLogin_pagamento_bancoitau() && (!$b_bloqueado_BITA) && $libera_pagamento['BancoItau'];

        // Libera Bradesco apenas se limite diario não ultrapassado //produtos (Habbo e GPotato) e tem até 5 compras nas últimas 24 horas
        $b_bloqueado_BRD5 = b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']);
        $b_bloqueado_BRD6 = b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']);
        $b_libera_Bradesco5 = $b_LimiteDiarioOK && $b_TentativasDiariasOK && (!$b_bloqueado_BRD5) && $libera_pagamento['Bradesco'];	//$b_IsProdutoOK && 
        $b_libera_Bradesco6 = $b_LimiteDiarioOK && $b_TentativasDiariasOK && (!$b_bloqueado_BRD6) && $libera_pagamento['Bradesco'];	//$b_IsProdutoOK && 
        $b_libera_Bradesco = $b_bloqueado_BRD6 && $b_bloqueado_BRD5;

        // Libera Banco EPP apenas para integração
        $b_libera_BancoEPP = $b_BancoEPP;

        // Libera pagamento Online Hipay
        $b_libera_Hipay = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $usuarioGames->b_IsLogin_pagamento_hipay() && $libera_pagamento['Hipay'];

        // Libera pagamento Online Paypal
        $b_libera_Paypal = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $usuarioGames->b_IsLogin_pagamento_paypal() && $libera_pagamento['Paypal'];

        // Libera Boleto apenas se o valor da venda não ultrapassa o limite por venda
        $b_bloqueado_BOL2 = b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['BOLETO_BANCARIO']);
        $b_libera_Boleto = $b_ValorBoletoOK && (!$b_bloqueado_BOL2) && $libera_pagamento['Boleto'];	

        // Libera Depósito apenas se o valor da venda não ultrapassa o limite por venda
        $b_bloqueado_DEP1 = b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']);
        $b_libera_Deposito = $b_ValorDepositoOK && (!$b_bloqueado_DEP1) && $libera_pagamento['Deposito'];

        // Libera Epp CASH
        $b_libera_EppCash = $libera_pagamento['EppCash'];
		
		$b_libera_Pagamento_personalizado = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $usuarioGames->b_IsLogin_pagamento_pin_Personalizado() && $libera_pagamento['Pagamento_personalizado'];
        
        // Libera PIX
        $b_libera_Pix = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $libera_pagamento["Pix"] && !b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_PIX']);;

        $msg_indisponivel = "<span class='style23'>Meio de pagamento indisponível.</span>";

        $msg_bloqueia_Bradesco = (!$b_libera_Bradesco)?$msg_indisponivel:NULL;

        $msg_bloqueia_BancodoBrasil = (!$b_libera_BancodoBrasil)?$msg_indisponivel:NULL;

        $msg_bloqueia_BancoItau = (!$b_libera_BancoItau)?$msg_indisponivel:NULL;

        $msg_bloqueia_Hipay = (!$b_libera_Hipay)?$msg_indisponivel:NULL;

        $msg_bloqueia_Paypal = (!$b_libera_Paypal)?$msg_indisponivel:NULL;

        $msg_bloqueia_Boleto = (!$b_libera_Boleto)?$msg_indisponivel:NULL;

        $msg_bloqueia_Deposito = (!$b_libera_Deposito)?$msg_indisponivel:NULL;

        $msg_bloqueia_Deposito = (!$b_libera_Deposito)?$msg_indisponivel:NULL;

        $msg_bloqueia_EppCash = (!$b_libera_EppCash)?$msg_indisponivel:NULL;
        
        $msg_bloqueia_Pix = (!$b_libera_Pix)?$msg_indisponivel:NULL;
		
		$msg_bloqueia_Personalizado = (!$b_libera_Pagamento_personalizado)?$msg_indisponivel:NULL;
		
		$lispags = explode(",", VerifyPaymentsMethods($integracao_origem_id)["opr_tipo_pagto_bloqueados"]);

        // Começa Gestão de Risco CIELO


        $carrinho_tmp = $GLOBALS['_SESSION']['carrinho'];

        $params = array();
    // $pagto = 'G' (Visa Crédito), quando passa aqi aonda não foi escolhido o $pagto -> uma forma para todos 
        $limite = new Limite('G', $usuarioGames->getId(), $total_carrinho, $carrinho_tmp, "week"); 
        $limite->getConfigurationCielo($vetorDados);
            
        echo '<input type="hidden" name="valorCielo" id="valorCielo" value="'.$vetorDados['ValorCompraMax'].'">';

//$limite->get_debug_table();

//$limite->getConfigurationCielo($params_cielo);

//echo "carrinho_tmp_operadoras: <pre>".print_r(get_object_vars($limite), true)."</pre>";
        $mensagem = "";

        $ret_regras_cielo = $limite->aplicaRegrasCieloNovas($mensagem, $params);

        if($ret_regras_cielo && $libera_pagamento['Cielo']) {
            $b_libera_Cielo = true;	
        } else {
            $b_libera_Cielo = false;	
            gravaLog_BloqueioPagtoOnline("Pagamento Cielo Bloqueado\n    pagto: $pagto, usuarioGames->getId(): ".$usuarioGames->getId().", total_carrinho: $total_carrinho, qtde_last_dayOK: ".$qtde_last_dayOK. ", total_diario: ".$total_diario."\n    ".$mensagem);
        }

        $msg_bloqueia_Cielo = (!$b_libera_Cielo)? "&nbsp;<br>&nbsp;<br><span class='style24'>Não disponível.<br><nobr><a onClick='load_saibamais_cielo();' class='link_azul' style='cursor:pointer;cursor:help;'>Saiba Mais</a>.</nobr></span>":"";

        // Termina Gestão de Risco CIELO
        if(!$b_TentativasDiariasOK || !$b_LimiteDiarioOK ) {	//|| !$b_libera_Cielo
            $msg_block = "Pagamento Online BLOQUEADO ******  ";

            $smsg_bloqueio = 
                "	Usuário (INT, '$integracao_store_id', '$partner_name'): ID: ".$usuarioGames->getId().", Nome: ".$usuarioGames->getNome().", Email: ".$usuarioGames->getEmail().",\n".
                "	Regras bloqueio: b_TentativasDiariasOK: ".(($b_TentativasDiariasOK)?"SIM":"não")." (n: $qtde_last_dayOK), ".
                    "b_LimiteDiarioOK: ".(($b_LimiteDiarioOK)?"SIM":"não").", \n".
                    "b_libera_Cielo: ".(($b_libera_Cielo)?"SIM":"não")." \n".
                "	total_carrinho: ".number_format($total_carrinho, 2, ',', '.').", total_diario: ".number_format($total_diario, 2, ',', '.')."\n".
            "	solicitado: ".number_format(($total_carrinho+$total_diario), 2, ',', '.')." de ".number_format($total_diario_const, 2, ',', '.')."\n".
            "";
            if(($total_carrinho+$total_diario)<=(2*$total_diario_const)) {
                $smsg_bloqueio .= "	Safe (<=2*LIMITE_MAX)\n";
            } else {
                $smsg_bloqueio .= "	NotSafe (>2*LIMITE_MAX)\n";
            }
            gravaLog_PagtoOnlineUsuariosBloqueadosParaVIP($smsg_bloqueio);

            PagtoOnlineUsuariosBloqueadosParaVIP($pagto, $usuarioGames->getId(), $total_carrinho, $total_diario, $total_diario_const, $qtde_last_dayOK, $pagamentos_diario_const);

        } else {
            $msg_block = "Pagamento Online PERMITIDO ++++++  ";
        }
        
        $mensagem = "=====================================================================================\n".
        "$msg_block (".date("Y-m-d H:i:s").")\n".
        "  Usuário: ID: ".$usuarioGames->getId().", Nome: ".$usuarioGames->getNome().", Email: ".$usuarioGames->getEmail().",\n".
        "  qtde_last_dayOK: ".$qtde_last_dayOK."\n".
        "  total_diario: ".number_format($total_diario, 2, ',', '.')."\n".
        "  total_diario_const: ".number_format($total_diario_const, 2, ',', '.')."\n".
        "  total_carrinho+total_diario: ".number_format(($total_carrinho+$total_diario), 2, ',', '.')."\n".
        "  b_TentativasDiariasOK: ".($b_TentativasDiariasOK?"OK":"nope")."\n".
        "  b_LimiteDiarioOK: ".($b_LimiteDiarioOK?"OK":"nope")."\n".
        "  \n".
        "  b_libera_BancodoBrasil: ".($b_libera_BancodoBrasil?"OK":"nope")."\n".
        "  b_libera_Bradesco: ".($b_libera_Bradesco?"OK":"nope")."\n".
        "  b_libera_Cielo: ".($b_libera_cielo?"OK":"nope")."\n".
        "  b_libera_PayPal: ".($b_libera_Paypal?"OK":"nope")."\n".
        "  b_libera_Hipay: ".($b_libera_Hipay?"OK":"nope")."\n".
//						"  RISCO_GAMERS_PAGAMENTOS_DIARIO: ".$RISCO_GAMERS_PAGAMENTOS_DIARIO."\n".
//						"  RISCO_GAMERS_TOTAL_DIARIO: ".number_format($RISCO_GAMERS_TOTAL_DIARIO, 2, ',', '.')."\n".
            "\n";
        gravaLog_BloqueioPagtoOnline($mensagem);
        
        //Verifica se o pagamento com EPP CASH está habilitado
        $have_eppcash = false;
		
		$have_personalizado = false;

        //Verifica se apenas o pagamento EPP CASH está habilitado
        $only_eppcash = false;
		
		$only_personalizado = false;

        //Conta a quantidade de pagamentos habilitados
        $cont_pagamentos = 0;
        
        if($b_libera_Bradesco5 && PAGAMENTO_BRADESCO){
            $cont_pagamentos++;
            $div_bradesco = true;

        }

        if($b_libera_Deposito){
            $cont_pagamentos++;
            $div_deposito = true;
        }

        if(!$b_bloqueado_BBR9 && $usuarioGames->b_IsLogin_pagamento_bancodobrasil() && PAGAMENTO_BANCO_BRASIL && $b_libera_BancodoBrasil) {
            $cont_pagamentos++;
            $div_brasil = true;
        }

        if(PAGAMENTO_ITAU && $usuarioGames->b_IsLogin_pagamento_bancoitau() && $b_libera_BancoItau) {
            $cont_pagamentos++;
            $div_itau = true;
        }

        if(PAGAMENTO_BOLETO && $b_libera_Boleto){
            $cont_pagamentos++;
            $div_boleto = true;
        }

        if($usuarioGames->b_IsLogin_pagamento_pin_EPP_Cash() && b_pin_forma_pagamento() && PAGAMENTO_EPREPAG_CASH && !b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']) && $b_libera_EppCash) {
            $cont_pagamentos++;
            $have_eppcash = true;
            $div_eppcash = true;
        }
		
		
		
		if($usuarioGames->b_IsLogin_pagamento_pin_Personalizado() && b_pin_forma_pagamento() && !b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_PERSONALIZADO']) && $b_libera_Pagamento_personalizado) {
            $cont_pagamentos++;	
            $have_personalizado = true;
            $div_personalizado = true;
			$only_personalizado = true;
        }

        if(($usuarioGames->b_IsLogin_pagamento_Cielo() && b_cielo_forma_pagamento() && PAGAMENTO_CIELO) && ($libera_pagamento['Cielo_Visa_DEB'] || $libera_pagamento['Cielo_Visa_CRED'] || $libera_pagamento['Cielo_Master_CRED'] || $libera_pagamento['Cielo_Elo_CRED'] || $libera_pagamento['Cielo_Diners_CRED'] || $libera_pagamento['Cielo_Discover_CRED']) && $b_libera_Cielo ) {
            $cont_pagamentos++;
            $div_cielo = true;
        }
        
        if($b_libera_Pix && PAGAMENTO_PIX){
            $cont_pagamentos++;
            $div_pix = true;
        }

        if($cont_pagamentos == 1 && $have_eppcash){
            $only_eppcash = true;
        }


					// Termina validações
                    // ==========================================================================================

        if(get_Integracao_is_sessao_logged()) {
            mostraCarrinho_tmp($integracao_iativo); 
		    // Bloqueado, não precisa por agora
            if(false) {
					?>
						<table border="0" cellspacing="0" align="center">
						<tr valign="middle" bgcolor="#FFFFFF"><td><hr></td></tr>
						<tr valign="middle" bgcolor="#FFFFFF">
							<td align="left" class="texto">
								<table border="0" cellspacing="1">
								<tr bgcolor="#F0F0F0" height="25">
									<td class="texto" align="right" width="40%">&nbsp;&nbsp;<b>Parceiro:</b></td>
									<td class="texto" width="60%">&nbsp;<?php echo get_Integracao_nome_parceiro($integracao_store_id)." (".$integracao_store_id.")" ?></td>
								</tr>
								<tr bgcolor="#F0F0F0" height="25">
									<td class="texto" align="right" width="40%">&nbsp;&nbsp;<b>E-mail:</b></td>
									<td class="texto" width="60%">&nbsp;<?php echo $integracao_client_email ?></td>
								</tr>
								<tr bgcolor="#F0F0F0" height="25">
									<td class="texto" align="right" width="40%">&nbsp;&nbsp;<b>Cliente_ID:</b></td>
									<td class="texto" width="60%">&nbsp;<?php echo $integracao_cliente_id ?></td>
								</tr>
								</table>
							</td>
						</tr>
						<tr valign="middle" bgcolor="#FFFFFF"><td><hr></td></tr>
						</table>

						<?php
            }
        }
    } // End of if($usuarioGames->b_IsLogin_pagamento())

?>
    </table>
<?php // Tabela com carrinho - End 

if(!$msg) {

    if($b_nova_forma_pagamento) {

?>
    <input type="hidden" name="iforma" value="0">
    <input type="hidden" name="idu" value="0">
    <input type="hidden" name="sno" value="0">
    <input type="hidden" name="tipo" value="integracao_gamer">
    <input type="hidden" name="integracao_is_parceiro" value="<?php echo $integracao_is_parceiro ?>">
    <input type="hidden" name="integracao_origem_id" value="<?php echo $integracao_origem_id ?>">
    <input type="hidden" name="integracao_order_id" value="<?php echo $integracao_order_id ?>">
    <input type="hidden" name="integracao_client_email" value="<?php echo $integracao_client_email ?>">
    <div class="row espacamento top20">
<?php
        // Linha Bradesco - inicio
        if($b_bloqueia_Elex) {
?>
            <p>
                <?php echo $s_bloqueia_Elex; ?>
            </p>
                <?php
        } else {	// // Elex - Começa Bloqueio aqui 

            //Testando se a variavel de valor possui carrinho ou se trata de uma integração de valor livre
            if(empty($valor_tmp)) 
                $valor_tmp = $price*1;

?>
            <div class="<?php if(isset($div_pix)) echo 'col-xs-12 col-md-4 mt-sm-15'; else echo "dnone"; ?>">
                <div class="row">
<?php                        
                    // Bloqueio Pagamento Ongame - inicio
                    if(!$b_bloqueia_Ongame && (isset($div_pix))) {
                        $cont_colunas++;

                        if($b_libera_Pix && PAGAMENTO_PIX){
                            $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_PIX'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                        }else{
                            $onclick = '';
                        }
?>           
                        <div class="col-xs-4">
                            <img 
                                src="/imagens/pag/iconePIX.png" 
                                class="c-pointer btnPgto" 
                                style="width:100%; max-width: 100px;"
                                name="btn_5"
                                title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_PIX']] ?>"
                                onClick="<?php echo $onclick; ?>"
                            >
                        </div>
                        <div class="col-xs-8">
<?php
                            if($b_libera_Pix && PAGAMENTO_PIX){
?>
                                <p class="fontsize-pp bottom0 top20">PIX</p>
<?php
                                if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_PIX_TAXA != 0) {
                                        echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_PIX_TAXA, 2, ',', '.')."</p>";
                                }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                else {
                                    echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PIX']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX']) echo " checked"; ?>></span>
<?php
                            }else{
                                echo $msg_bloqueia_Pix;
                            }
?>
                        </div>
<?php
                    }
?>
                </div>
            </div>
<?php
            if($cont_colunas == 3){
                echo "</div><div class='row espacamento'>";
                $cont_colunas = 0;
            }
?>
            <div class="<?php if(isset($div_bradesco)) echo 'col-xs-12 col-md-4 mt-sm-15'; else echo 'dnone'; ?>">
<?php 
                if((!$b_bloqueado_BRD6 || !$b_bloqueado_BRD5) && (isset($div_bradesco))) { 
                    $cont_colunas++;
                } 

                if($b_libera_Bradesco6 && PAGAMENTO_BRADESCO && false) {
?>
                    <p>
                        <img class="img-responsive c-pointer btnPgto" src="/imagens/pag/pagto_forma_debito_visa1.gif" name="btn_6" onMouseOver="document.btn_6.src='/imagens/pag/pagto_forma_debito_visa2.gif'" onMouseOut="document.btn_6.src='/imagens/pag/pagto_forma_debito_visa1.gif'" title="Bradesco pagamento (Débito em conta)" onClick="save_shipping(<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'] ?>, <?php echo (($usuarioGames->getId()>0)?$usuarioGames->getId():"0") ?>, '<?php echo $usuarioGames->getNome() ?>')">
                    </p>
                    <p class="txt-vermelho fontsize-pp bottom0">Sem taxa de serviço</p>
                    <p class="txt-verde fontsize-pp">Entrega em até 90 minutos</p>
                    <span style="display:none;"><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) echo " checked"; ?>></span>
<?php 
                } else { 
                    //echo $msg_bloqueia_Bradesco; //copiado do passo-2
                }  

                if($b_libera_Bradesco5 && PAGAMENTO_BRADESCO && !$only_eppcash) {
                    $onclick = 'save_shipping('. $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'] . ',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                } else { 
                    $onclick = '';
                }
?>
                <div class="row">    
                    <div class="col-xs-4">
                        <img 
                            src="/imagens/pag/iconeBradesco.png" 
                            class="c-pointer btnPgto" 
                            style="width:100%; max-width: 100px;"
                            name="btn_5"
                            title="Bradesco pagamento (Transferência entre contas)"
                            onClick="<?php echo $onclick; ?>"
                        >
                    </div>
                    <div class="col-xs-8">
<?php
                        if($b_libera_Bradesco5 && PAGAMENTO_BRADESCO){
?>
                            <p class="fontsize-pp bottom0 top10">TRANSFERÊNCIA ENTRE CONTAS</p>
<?php
                            if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL != 0) {
                                    echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL, 2, ',', '.')."</p>";
                            }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                            else {
                                echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                            }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                            <p class="txt-verde fontsize-pp">Entrega em até 90 minutos</p>
                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) echo " checked"; ?>></span>
<?php
                        }else{
                            echo $msg_bloqueia_Bradesco;
                        }
?>
                    </div>
                </div>
            </div>
<?php
            if($cont_colunas == 3){
                echo "</div><div class='row espacamento'>";
                $cont_colunas = 0;
            }
?>
            <div class="<?php if(isset($div_brasil)) echo 'col-xs-12 col-md-4 mt-sm-15'; else echo 'dnone'; ?>">
<?php
                // Linha Bradesco - fim

                // Linha BB - inicio
                /*if($usuarioGames->b_IsLogin_pagamento_bancodobrasil()) */ 
                if(!$b_bloqueado_BBR9 && isset($div_brasil)) { 
                    $cont_colunas++;
                } 

                if($b_libera_BancodoBrasil && $usuarioGames->b_IsLogin_pagamento_bancodobrasil() && PAGAMENTO_BANCO_BRASIL && isset($div_brasil)) {
                    $onclick = 'save_shipping('. $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'] . ',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                }else {
                    $onclick = '';
                } 
?>
                <div class="row">
                    <div class="col-xs-4">
                        <img 
                            src="/imagens/pag/iconeBrancodoBrasil.png" 
                            class="c-pointer btnPgto" 
                            style="width:100%; max-width: 100px;"
                            name="btn_9"
                            title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']] ?>"
                            onClick="<?php echo $onclick; ?>"
                        >
                    </div>     
                    <div class="col-xs-8">
<?php
                        if($b_libera_BancodoBrasil && $usuarioGames->b_IsLogin_pagamento_bancodobrasil() && PAGAMENTO_BANCO_BRASIL && isset($div_brasil)){
?>
                            <p class="fontsize-pp bottom0 top20">DÉBITO EM CONTA</p>

<?php
                            if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA) {
                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BANCO_DO_BRASIL_TAXA_DE_SERVICO, 2, ',', '.')."</p>";
                            }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                            else {
                                echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                            }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                            <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) echo " checked"; ?>></span>

<?php
                        }else{
                            echo $msg_bloqueia_Bradesco;
                        }
?>
                    </div>
                </div>
            </div>
            
<?php
            if($cont_colunas == 3){
                echo "</div><div class='row espacamento'>";
                $cont_colunas = 0;
            }
?>
            <div class="<?php if(isset($div_itau)) echo 'col-xs-12 col-md-4 mt-sm-15'; else echo 'dnone';?>">
<?php
                // Linha Banco itau - inicio

                //Constante do configurador de meios de pagamentos
                if(!$b_bloqueado_BITA && PAGAMENTO_ITAU && $usuarioGames->b_IsLogin_pagamento_bancoitau() && isset($div_itau)) {
                    $cont_colunas++;
                } 

                if($b_libera_BancoItau && $usuarioGames->b_IsLogin_pagamento_bancoitau()) {
                    $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                }else{
                    $onclick = '';
                }
?>
                <div class="row">
                    <div class="col-xs-4">
                        <img 
                            src="/imagens/pag/iconeShoplineItau.png" 
                            class="c-pointer btnPgto" 
                            style="width:100%; max-width: 100px;"
                            name="btn_10"
                            title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']] ?>"
                            onClick="<?php echo $onclick; ?>"
                        >
                    </div>            
                    <div class="col-xs-8">
<?php
                        if($b_libera_BancoItau && $usuarioGames->b_IsLogin_pagamento_bancoitau()){
?>
                            <p class="fontsize-pp bottom0 top10">TRANSFERÊNCIA ENTRE CONTAS</p>
<?php
                            if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA) {
                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BANCO_ITAU_TAXA_DE_SERVICO, 2, ',', '.')."</p>";
                            }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                            else {
                                echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                            }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                            <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) echo " checked"; ?>></span>
<?php	
                        } else {
                            echo $msg_bloqueia_BancoItau;
                        } 
?>                  
                    </div>
                </div>
            </div>
<?php
            // Linha Banco Itau - fim
            // Linha HIPAY - inicio
            if($usuarioGames->b_IsLogin_pagamento_hipay() && false) {		// DUMMY
?>
            <div class="col-md-2 col-lg-2 col-sm-2 col-xs-6 top20 borda-colunas-formas-pagamento text-center">
                <p>HIPAY</p>
<?php 
                if($usuarioGames->b_IsLogin_pagamento_hipay()) {
                    if($b_libera_Hipay) { 
?>
                <p>
                    <img class="img-responsive c-pointer" src="images/botao_hipay.gif" name="btn_11" onMouseOver="document.btn_11.src='/imagens/pag/pagto_forma_debito_visa2.gif'" onMouseOut="document.btn_11.src='/imagens/pag/pagto_forma_debito_visa1.gif'" border="0" title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE']] ?>" onClick="save_shipping('<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE'] ?>', <?php echo (($usuarioGames->getId()>0)?$usuarioGames->getId():"0") ?>, '<?php echo $usuarioGames->getNome() ?>')">

                    <p>Pagamento Online - HIPAY</p>
                    <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE']) echo " checked"; ?>></span>
<?php	
                    } else {
                        echo $msg_bloqueia_Hipay;
                    } 
                }
?>
                </div>
<?php
            }
            // Linha HIPAY - fim
            // Linha PAYPAL - inicio
            if($usuarioGames->b_IsLogin_pagamento_paypal() && false) {		// DUMMY
?>
            <div class="col-md-2 col-lg-2 col-sm-2 col-xs-6 top20 borda-colunas-formas-pagamento text-center">
                <p>PAYPAL</p>
<?php
                if($usuarioGames->b_IsLogin_pagamento_paypal()) {
                    if($b_libera_Paypal) {
?>
                    <p>
                        <img src="images/botao_paypal.gif" name="btn_12" onMouseOver="document.btn_12.src='/imagens/pag/pagto_forma_debito_visa2.gif'" onMouseOut="document.btn_12.src='/imagens/pag/pagto_forma_debito_visa1.gif'" border="0" title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']] ?>" onClick="save_shipping('<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE'] ?>', <?php echo (($usuarioGames->getId()>0)?$usuarioGames->getId():"0") ?>, '<?php echo $usuarioGames->getNome() ?>')">
                    </p>
                    <p>Pagamento Online - PAYPAL</p>
                    <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']) echo " checked"; ?>></span>
<?php
                    } else {
                        echo $msg_bloqueia_Paypal;
                    } 
                }
?>
            </div>
<?php
            }
            // Linha PAYPAL - fim
            

            if($cont_colunas == 3){
                echo "</div><div class='row espacamento'>";
                $cont_colunas = 0;
            }
    
            // Linha Boleto - inicio

            //Constante do configurador de meios de pagamentos
            //if(PAGAMENTO_BOLETO && isset($div_boleto)) {
?>
            
<?php
                if($b_libera_Deposito && !in_array("1", $lispags)) {
                    $cont_colunas++; 
?>
                    <div class="col-xs-12 col-md-4 mt-sm-15">
                        <div class="row">
                            <div class="col-xs-4">
                                <img 
                                    src="/imagens/pag/iconeDeposito.png" 
                                    class="c-pointer btnPgto" 
                                    style="width:100%; max-width: 100px;"
                                    name="btn_5"
                                    title="Pagamento por Depósito"
                                    onClick="save_shipping(<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'] ?>, <?php echo (($usuarioGames->getId()>0)?$usuarioGames->getId():"0") ?>, '<?php echo $usuarioGames->getNome() ?>');"
                                >
                            </div>
                            <div class="col-xs-8">
                                <p class="fontsize-pp bottom0">Depósito / DOC / TED</p>
                                <p class="txt-vermelho fontsize-pp bottom0">Sem taxa de serviço</p>
                                <p class="txt-verde fontsize-pp">Entrega em até 1 dia útil</p>
<!--                                <p class="fontsize-pp txt-preto"><strong>Depósito, DOC
                                    Transferência offline
                                    Ag.2062-1<br>
                                    Cc.0030265-1</strong>
                                </p>-->
                                <p class="txt-preto fontsize-pp"><input type="checkbox" name="pagto_ja_fiz" id="pagto_ja_fiz" value="1" onchange='check_deposito(this);' class="" <?php if($pagto_ja_fiz == "1") echo "checked"; ?>>  Após efetuar o pagamento, clique aqui para informar os dados.</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']?>" <?php if($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']) echo " checked"; ?>></span>
                            </div>
                        </div>
                    </div>
                
<?php
                }
?>
<?php
                if($cont_colunas == 3){
                    echo "</div><div class='row espacamento'>";
                    $cont_colunas = 0;
                }
?>                

            <div class="<?php if(isset($div_boleto)) echo 'col-xs-12 col-md-4 mt-sm-15'; else echo 'dnone';?>">
<?php     
                if(!$b_bloqueado_BOL2 && $b_libera_Boleto && PAGAMENTO_BOLETO && isset($div_boleto)) {
                    $cont_colunas++;
                    $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['BOLETO_BANCARIO'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                }else{
                    $onclick = '';
                }
?>
                <div class="row">
                    <div class="col-xs-4">
                        <img 
                            src="/imagens/pag/iconeBoleto.png" 
                            class="c-pointer btnPgto" 
                            style="width:100%; max-width: 100px;"
                            name="btn_2"
                            title="Boleto Bancário"
                            onClick="<?php echo $onclick; ?>"
                        >
                    </div>
                    <div class="col-xs-8">
<?php
                        if(!$b_bloqueado_BOL2 && $b_libera_Boleto && PAGAMENTO_BOLETO && isset($div_boleto)) {
?>
                            <p class="fontsize-pp bottom0 top20">BOLETO BANCÁRIO</p>
<?php
                            if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA) {
                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BOLETO_TAXA_ADICIONAL, 2, ',', '.')."</p>";
                            }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                            else {
                                echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                            }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                            <p class="txt-verde fontsize-pp">Entrega até 2 dias úteis</p>
                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['BOLETO_BANCARIO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) echo " checked"; ?>></span>
    <?php 
                        } else { 
                            echo $msg_bloqueia_Boleto;
                        }
?>
                    </div>
                </div>
            </div>
<?php
            //}//end if(PAGAMENTO_BOLETO)
        // Linha Boleto - fim
        }  // Elex - Bloqueia até aqui 
        
        if($cont_colunas == 3){
            echo "</div><div class='row espacamento'>";
            $cont_colunas = 0;
        }
?>
        <div class="<?php if($only_eppcash) echo "col-xs-offset-1" ?> <?php if(isset($div_eppcash)) echo "col-xs-12 col-md-4 mt-sm-15"; else echo "dnone"; ?>">
<?php
        // Linha PIN E-PREPAG - inicio
        if($usuarioGames->b_IsLogin_pagamento_pin_EPP_Cash() && 
            b_pin_forma_pagamento() && 
            PAGAMENTO_EPREPAG_CASH && 
            !b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']) && isset($div_eppcash)) {
            
            $cont_colunas++;
            
            if($b_libera_EppCash) {
                $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
            }else{
                $onclick = '';
            }
            
?>
            <div class="row">
                <div class="col-xs-4">
                    <img 
                        src="/imagens/pag/iconeeppcash.png" 
                        class="c-pointer btnPgto" 
                        style="width:100%; max-width: 100px;"
                        name="btn_13"
                        title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']] ?>"
                        onClick="<?php echo $onclick; ?>"
                    >
                </div>
                <div class="col-xs-8">
<?php 
                    if($b_libera_EppCash) {
?>
                        <p class="fontsize-pp bottom0">E-PREPAG CASH</p>
                        <p class="txt-vermelho fontsize-pp bottom0">Sem taxa de serviço</p>
                        <p class="txt-verde fontsize-pp">Entrega Imediata</p>
<?php
                        if(!$only_eppcash){
?>
                            <p class="txt-azul fontsize-pp bottom0"><a href="<?= EPREPAG_URL_HTTPS ?>/game/produto/detalhe.php?token=IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNFXtVWl4P">Não tem um PIN?</a></p>
<?php
                        }
?>
                        <span style='display:none'><input type="radio" name="pagto" value="<?=$FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']) echo " checked"; ?>></span>
<?php 
                    } else {
                        echo $msg_bloqueia_EppCash . " AAAAAHHH";
                    } 
?>
                </div>
            </div>
	 </div>
<?php
        }
					// Linha PIN E-PREPAG - fim
					
		// Personalizado
		
		//if ($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
			
			$partner_info = get_img($integracao_origem_id);
						
	?>	
		<div class="<?php if(isset($only_personalizado)) echo "col-xs-12 col-md-4 mt-sm-15"; ?>">
	<?php	
			
		if($usuarioGames->b_IsLogin_pagamento_pin_Personalizado() && 
            b_pin_forma_pagamento() &&  
            !in_array("S", $lispags)
			)  {
            
            $cont_colunas++;
            
            if($b_libera_Pagamento_personalizado) {
                $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
            }else{
                $onclick = '';
            }	
	?>
            <div class="row">
                <div class="col-xs-4">
					<div id="water-mark">
						<img 
							src="<?php echo $partner_info["logo"]; ?>" 
							class="c-pointer btnPgto"
							id="logo-inside"
							style="width:100%; max-width: 100px;"
							name="btn_13"
							title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']] ?>"
							onClick="<?php echo $onclick; ?>"
						>
					</div>
					
                </div>
				
				
                <div class="col-xs-8">
						<!-- volteaqui -->
                        <p class="fontsize-pp bottom0"><?php echo strtoupper(verifica_nome_operadora($integracao_origem_id)["opr_nome_loja"]); ?> </p>
                        <p class="txt-vermelho fontsize-pp bottom0">Sem taxa de serviço</p>
                        <p class="txt-verde fontsize-pp">Entrega Imediata</p>
				</div>
            </div>
		</div>
    <?php	
			 
			}
		//}
?>  
       
<?php 
        if($only_eppcash) {
?>       
            <div class="col-xs-12 col-md-7 mt-sm-15">
                <span class="txt-cinza">
                    Para finalizar esta compra, você precisa de um <b><a href="<?= EPREPAG_URL_HTTPS ?>/game/produto/detalhe.php?token=IlJQTB5TdFZVAj0XFwEHNwdDTRYafFtNGHlHQxg">Cartão E-Prepag Cash</a></b> ou <b><a href="<?= EPREPAG_URL_HTTPS ?>/game/conta/add-saldo.php">saldo</a></b> em sua Conta E-Prepag.<br>
                    Caso já possua um <b>cartão</b> ou <b>saldo</b>, clique no botão <b>"E-PREPAG Cash"</b> e finalize a compra.<br><br>
                    Caso queira adquirir um Cartão E-Prepag Cash, <b><a href="<?= EPREPAG_URL_HTTPS ?>/game/produto/detalhe.php?token=IlJQTB5TdFZVAj0XFwEHNwdDTRYafFtNGHlHQxg">clique aqui</a></b><br>
                    <i>(Você pode pagar por boleto bancário, transferência, débito em conta, depósito bancário, DOC ou TED)</i>
                </span>
            </div>
<?php 
        }
        if($cont_colunas == 3){
            echo "</div><div class='row espacamento'>";
            $cont_colunas = 0;
        }
?>
        <!--<div class="<?php // if(isset($div_cielo)) echo 'col-md-2'; else echo 'dnone';?> col-lg-2 col-sm-2 col-xs-6 top20 text-center">-->
<?php
        //----Wagner
         // Linha CIELO - inicio
         if(($usuarioGames->b_IsLogin_pagamento_Cielo() && b_cielo_forma_pagamento() && PAGAMENTO_CIELO) && (!$b_bloqueia_Ongame && isset($div_cielo))
        //liberação abaixo somente para nossos usuários
        //&& ($usuarioGames->b_IsLogin_pagamento_Cielo_Integracao())
        ) 
        {
            if($b_libera_Cielo) {
                $liberado = true;
            }else{
                $liberado = false;
            }
            if($libera_pagamento['Cielo_Visa_DEB'] && !b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO'])){
                $cont_colunas++;
                if($liberado){
                    $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                }else{
                    $onclick = '';
                }
?>
                <div class="col-xs-12 col-md-4 mt-sm-15">
                    <div class="row">
                         <div class="col-xs-4">
                            <img 
                                src="/imagens/pag/iconeVisa.png" 
                                class="c-pointer btnPgto" 
                                style="width:100%; max-width: 100px;"
                                name="btn_14"
                                title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO']] ?>"
                                onClick="<?php echo $onclick; ?>"
                            >
                        </div>
                        <div class="col-xs-8 top20">
<?php
                            if($liberado){
?>
                                <p class="fontsize-pp bottom0">VISA - DÉBITO</p>
<?php
                                if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_VISA_DEBITO_TAXA != 0) {
                                    echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_VISA_DEBITO_TAXA, 2, ',', '.')."</p>";
                                }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                else {
                                    echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO']) echo " checked"; ?>></span>

<?php
                            }else{
                                echo $msg_bloqueia_Cielo;
                            }
?>

                        </div>
                    </div>
                </div>
<?php
            }

            if($cont_colunas == 3){
                echo "</div><div class='row espacamento'>";
                $cont_colunas = 0;
            }

            if($libera_pagamento['Cielo_Visa_CRED']  && !b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO'])){
                $cont_colunas++;
                if($liberado){
                    $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                }else{
                    $onclick = '';
                }
?>
                <div class="col-xs-12 col-md-4 mt-sm-15">
                    <div class="row">
                         <div class="col-xs-4">
                            <img 
                                src="/imagens/pag/iconeVisa.png" 
                                class="c-pointer btnPgto" 
                                style="width:100%; max-width: 100px;"
                                name="btn_15"
                                title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO']] ?>"
                                onClick="<?php echo $onclick; ?>"
                            >
                        </div>
                        <div class="col-xs-8 top20">
<?php
                            if($liberado){
?>
                                <p class="fontsize-pp bottom0">VISA - CRÉDITO</p>
<?php
                                if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_VISA_CREDITO_TAXA != 0) {
                                    echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_VISA_CREDITO_TAXA, 2, ',', '.')."</p>";
                                }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                else {
                                    echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO']) echo " checked"; ?>></span>

<?php
                            }else{
                                echo $msg_bloqueia_Cielo;
                            }
?>

                        </div>
                    </div>
                </div>
<?php
            }

            if($cont_colunas == 3){
                echo "</div><div class='row espacamento'>";
                $cont_colunas = 0;
            }

            if($libera_pagamento['Cielo_Master_CRED'] && !b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO'])){
                $cont_colunas++;
                if($liberado){
                    $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                }else{
                    $onclick = '';
                }
?>
                <div class="col-xs-12 col-md-4 mt-sm-15">
                    <div class="row">
                         <div class="col-xs-4">
                            <img 
                                src="/imagens/pag/iconeMasterCard.png" 
                                class="c-pointer btnPgto" 
                                style="width:100%; max-width: 100px;"
                                name="btn_17"
                                title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO']] ?>"
                                onClick="<?php echo $onclick; ?>"
                            >
                        </div>
                        <div class="col-xs-8 top20">
<?php
                            if($liberado){
?>
                                <p class="fontsize-pp bottom0">MASTERCARD - CRÉDITO</p>
<?php
                                if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_MASTER_CREDITO_TAXA != 0) {
                                    echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_MASTER_CREDITO_TAXA, 2, ',', '.')."</p>";
                                }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                else {
                                    echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO']) echo " checked"; ?>></span>

<?php
                            }else{
                                echo $msg_bloqueia_Cielo;
                            }
?>

                        </div>
                    </div>
                </div>
<?php
            }

            if($cont_colunas == 3){
                echo "</div><div class='row espacamento'>";
                $cont_colunas = 0;
            }

            if($libera_pagamento['Cielo_Elo_CRED'] && !b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO'])){
                $cont_colunas++;
                if($liberado){
                    $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                }else{
                    $onclick = '';
                }
?>
                <div class="col-xs-12 col-md-4 mt-sm-15">
                    <div class="row">
                         <div class="col-xs-4">
                            <img 
                                src="/imagens/pag/iconeElo.png" 
                                class="c-pointer btnPgto" 
                                style="width:100%; max-width: 100px;"
                                name="btn_19"
                                title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO']] ?>"
                                onClick="<?php echo $onclick; ?>"
                            >
                        </div>
                        <div class="col-xs-8 top20">
<?php
                            if($liberado){
?>
                                <p class="fontsize-pp bottom0">ELO - CRÉDITO</p>
<?php
                                if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_ELO_CREDITO_TAXA != 0) {
                                    echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_ELO_CREDITO_TAXA, 2, ',', '.')."</p>";
                                }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                else {
                                    echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO']) echo " checked"; ?>></span>

<?php
                            }else{
                                echo $msg_bloqueia_Cielo;
                            }
?>

                        </div>
                    </div>
                </div>
<?php
            }

            if($cont_colunas == 3){
                echo "</div><div class='row espacamento'>";
                $cont_colunas = 0;
            }

            if($libera_pagamento['Cielo_Diners_CRED'] && !b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO'])){
                $cont_colunas++;
                if($liberado){
                    $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                }else{
                    $onclick = '';
                }
?>
                <div class="col-xs-12 col-md-4 mt-sm-15">
                    <div class="row">
                         <div class="col-xs-4">
                            <img 
                                src="/imagens/pag/iconeDiners.png" 
                                class="c-pointer btnPgto" 
                                style="width:100%; max-width: 100px;"
                                name="btn_20"
                                title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO']] ?>"
                                onClick="<?php echo $onclick; ?>"
                            >
                        </div>
                        <div class="col-xs-8 top20">
<?php
                            if($liberado){
?>
                                <p class="fontsize-pp bottom0">DINERS - CRÉDITO</p>
<?php
                                if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_DINERS_CREDITO_TAXA != 0) {
                                    echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_DINERS_CREDITO_TAXA, 2, ',', '.')."</p>";
                                }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                else {
                                    echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO']) echo " checked"; ?>></span>

<?php
                            }else{
                                echo $msg_bloqueia_Cielo;
                            }
?>

                        </div>
                    </div>
                </div>
<?php
            }

            if($cont_colunas == 3){
                echo "</div><div class='row espacamento'>";
                $cont_colunas = 0;
            }

            if($libera_pagamento['Cielo_Discover_CRED'] && !b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO'])){
                $cont_colunas++;
                if($liberado){
                    $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO'] . '\',' . (($usuarioGames->getId()>0)?$usuarioGames->getId():'0') . ',\'' . $usuarioGames->getNome() . '\');';
                }else{
                    $onclick = '';
                }
?>
                <div class="col-xs-12 col-md-4 mt-sm-15">
                    <div class="row">
                         <div class="col-xs-4">
                            <img 
                                src="/imagens/pag/iconeDiscover.png" 
                                class="c-pointer btnPgto" 
                                style="width:100%; max-width: 100px;"
                                name="btn_21"
                                title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO']] ?>"
                                onClick="<?php echo $onclick; ?>"
                            >
                        </div>
                        <div class="col-xs-8 top20">
<?php
                            if($liberado){
?>
                                <p class="fontsize-pp bottom0">DISCOVER - CRÉDITO</p>
<?php
                                if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_DISCOVER_CREDITO_TAXA != 0) {
                                    echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_DISCOVER_CREDITO_TAXA, 2, ',', '.')."</p>";
                                }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                else {
                                    echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO']) echo " checked"; ?>></span>

<?php
                            }else{
                                echo $msg_bloqueia_Cielo;
                            }
?>

                        </div>
                    </div>
                </div>
<?php
            }
                    
            } else {
//                        echo $msg_bloqueia_Cielo;
            }  
        // Linha CIELO - fim
        //----Wagner ATÉ
?>
        </div>
        
<?php
        // Linha Banco E-Prepag - inicio
        // && FALSE em 17/09/2014 por Wagner para inibir o banco teste
        if($b_BancoEPP && FALSE) {
?>
            <div class="col-md-2 col-lg-2 col-sm-2 col-xs-6 top20 text-center">
            <p class=" borda-linhas-formas-pagamento">
                <img src="/imagens/pag/pagto_epp_teste.gif" border="0"><br><img src="images/spacer.gif" width="1" height="5" border="0">
            </p>
<?php 
            if($usuarioGames->b_IsLogin_pagamento_bancoepp()) {
                if($b_libera_BancoEPP) { ?>
                <p>
                    <img src="/imagens/pag/botao_banco_epp_1.gif" name="btn_999" onMouseOver="document.btn_999.src='/imagens/pag/botao_banco_epp_2.gif'" onMouseOut="document.btn_999.src='/imagens/pag/botao_banco_epp_1.gif'" width="110" height="35" border="0" title="Banco E-Prepag - TESTES" onClick="save_shipping('<?php echo $PAGAMENTO_BANCO_EPP_ONLINE ?>', <?php echo (($usuarioGames->getId()>0)?$usuarioGames->getId():"0") ?>, '<?php echo $usuarioGames->getNome() ?>')">
                </p>
                <p>Banco E-Prepag (TESTE)</p>
                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $PAGAMENTO_BANCO_EPP_ONLINE?>" <?php if($pagto == $PAGAMENTO_BANCO_EPP_ONLINE) echo " checked"; ?>></span>
<?php	
                } else {
                    echo $msg_bloqueia_BancoEPrepag;
                } 
            }
?>
            </div>
<?php
        }
                            // Linha Banco E-Prepag - fim					
    }	// End of if($b_nova_forma_pagamento)
}	// End of if(!$msg)
			} else {
				echo "OUT<br>";
			}	// End of if(is_object($usuarioGames))
        
			if(!$b_nova_forma_pagamento) {
?>
                <div clas="col-md-12 text-left">
	          	<input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']?>" <?php if($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']) echo "checked"; ?>> 
                <b><?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['DEP_DOC_TRANSF']]?></b></div>
		        <input type="checkbox" name="pagto_ja_fiz" value="1" <?php if($pagto_ja_fiz == "1") echo "checked"; ?>> Já fiz meu pagamento e quero informar os dados<br>
		        <table width="53%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="9%">&nbsp;</td>
                    <td width="12%"><img src="/imagens/pag/p_bb.jpg" border="0" alt="Banco do Brasil" /></td>
                    <td width="35%"><span class="style10"><span class="style8">Banco do Brasil</span><br />
  </span><span class="style11">Ag&ecirc;ncia: 4328-1<br />
                    Conta: 2978-5</span></td>
                    <td width="12%"><img src="/imagens/pag/p_bradesco.jpg" border="0" alt="Bradesco" /></td>
                    <td width="32%"><span class="style10"><span class="style8">Bradesco</span><br />
  </span><span class="style11">Ag&ecirc;ncia: 2062-1<br />
                    Conta: 0030265-1</span></td>
                  </tr>
                  <tr>
                    <td colspan="5" height="5"></td>
                  </tr>
                  <tr>
                    <td class="style5">&nbsp;</td>
                    <td colspan="4" class="style5"><strong>Raz&atilde;o Social:</strong> <span class="style8">E-PREPAG PAGAMENTOS ELETR&Ocirc;NICOS S/S   LTDA</span><br />
                      <strong>CNPJ/CPF:</strong><span class="style8"> 08.221.305/0001-35 </span></td>
                  </tr>
                </table>				
		         
				  <!--img src="/imagens/pag/p_caixa.jpg" border="0" alt="Caixa Econômica Federal"--><br>
	          </td>
	        </tr>
	        <tr>
	          <td class="texto" height="25" colspan="3">
	          	<input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['BOLETO_BANCARIO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) echo "checked"; ?>> 
				<b><?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['BOLETO_BANCARIO']]?></b>
                                <?php
                                if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA) {
                                ?>
				- Acréscimo de R$ <?php echo number_format($BOLETO_TAXA_ADICIONAL, 2, ',', '.')?> referente a taxa de serviço bancário<br>
		        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <?php
                                }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                ?>
				<img src="/imagens/pag/p_boleto.jpg" border="0" alt="Boleto Bancário"><br>
				<br>
	          </td>
	        </tr>
<?php
			}
?>

			<?php  
				if(false) {
			?>
	        <tr>
	          <td class="texto" height="25" colspan="3" valign="middle">
	          	<input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['REDECARD_MASTERCARD']?>" <?php if($pagto == $FORMAS_PAGAMENTO['REDECARD_MASTERCARD']) echo "checked"; ?>>
				<img src="/imagens/pag/p_mastercard.jpg" border="0" alt="Mastercard"> <b><?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['REDECARD_MASTERCARD']]?></b><br>
				<br>
	          </td>
	        </tr>
	        <tr>
	          <td class="texto" height="25" colspan="3" valign="middle">
	          	<input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['REDECARD_DINERS']?>" <?php if($pagto == $FORMAS_PAGAMENTO['REDECARD_DINERS']) echo "checked"; ?>>
				<img src="/imagens/pag/p_diners.jpg" border="0" alt="Diners"> <b><?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['REDECARD_DINERS']]?></b><br>
				<br>
	          </td>
	        </tr>
			<?php  
				}
			?>
		</table>
	<?php
	// Aqui terminam as formas de pagamento
	} else {

		$partner_name = getPartner_param_By_ID('partner_name', $integracao_store_id);
		$partner_url = getPartner_param_By_ID('partner_url', $integracao_store_id);
	?>
        <div class="alert alert-danger" role="alert"> 
            <p>
                Desculpe, o pedido <b><?php echo $integracao_order_id;?></b> do parceiro <span title="<?php echo $integracao_store_id; ?>"><b><?php echo $partner_name; ?></b></span> já existe no nosso sistema. <br><br>

				Retorne ao site do parceiro e faça outro pedido (<a href="<?php echo $partner_url; ?>" target="_blank"><?php echo $partner_name; ?></a>). <br><br>
				Lembre que seu crédito será efetivado no site do Parceiro apenas após completar o pagamento do pedido no banco da sua escolha. 
            </p>
        </div>
	<?php

		// O pedido foi recusado -> Cancela sessão, caso exista de visita anterior
		cancelarSessao();
	}
	
    if($b_nova_forma_pagamento) {
?>
		<input type="hidden" name="btSubmit_EPP_8593" value="Continuar">
<?php
    }

?>
    </form>
</div>
<?php
	// Completa o bloqueio por repetição de pedido para order_id
	
?>
<script src="<?php echo $https.$spref; ?>://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript" nonce="<?php echo $nonce; ?>">
_uacct = "UA-1903237-3";
urchinTracker();
</script>
<!-- Facebook Pixel Code -->
<script nonce="<?php echo $nonce; ?>">
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '228069144336893'); // Insert your pixel ID here.
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=228069144336893&ev=PageView&noscript=1"/></noscript>
<!-- End Facebook Pixel Code -->
<?php  
include "includes/rodape.php"; 

function mostraCarrinho_tmp($iativo){

        $iativo = ($iativo)?1:0;

        // recupera dados integração
        $b_amount_free = "0";
        $carrinho_val = "";
        if(isset($GLOBALS['_SESSION']['integracao_origem_id'])) {
                if (function_exists('getPartner_amount_free_By_ID')) {
                        $b_amount_free = getPartner_amount_free_By_ID($GLOBALS['_SESSION']['integracao_origem_id']);
                        if(isset($GLOBALS['_SESSION']['carrinho_val'])) {
                                $carrinho_val = $GLOBALS['_SESSION']['carrinho_val'];
                        }
                }
        }

        //Recupra carrinho do session
        $carrinho = $_SESSION['carrinho'];

        if(!$carrinho || count($carrinho) == 0){		
?>			
                <table border="0" cellspacing="0" width="90%" height="200">
    <tr align="center" bgcolor="#FFFFFF">
      <td align="center" class="texto">Carrinho vázio no momento</td>
    </tr>
                </table>
<?php
        } else {
                ?>

    <table border="0" cellspacing="0" width="95%" align="center">
        <tr bgcolor="F0F0F0">
          <td class="texto" align="left" height="25"><b>Descrição</b></td>
          <td class="texto" align="center"><b>I.O.F.</b></td>
          <td class="texto" align="right"><b>Total</b></td>
        </tr>
        <tr bgcolor="F0F0F0">
          <td>&nbsp;</td>
          <td class="texto" align="right" height="25">&nbsp;</td>
          <td class="texto" align="right"><b><?php echo number_format($total_geral, 2, ',', '.') ?></b></td>
        </tr>
    </table>
<?php
        }

}
// Redireciona automatico para Pagto Online quando possui somente EPP CASH como tipo de pagamento habilitado 
if (!$b_libera_Bradesco5 && !$b_libera_Bradesco6 && !$b_libera_Deposito && !$b_libera_BancodoBrasil && !$b_libera_BancoItau && !$b_libera_Boleto && !$b_libera_Cielo) {
    if($usuarioGames->b_IsLogin_pagamento_pin_EPP_Cash() && b_pin_forma_pagamento()) {
         ob_clean();
?>
<form name="form1" action="" method="post">
            <input type="hidden" name="iforma" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG'] ?>">
            <input type="hidden" name="idu" value="<?php echo (($usuarioGames->getId()>0)?$usuarioGames->getId():"0") ?>">
            <input type="hidden" name="sno" value="<?php echo $usuarioGames->getNome() ?>">
            <input type="hidden" name="integracao_is_parceiro" value="<?php echo $integracao_is_parceiro ?>">
            <input type="hidden" name="integracao_origem_id" value="<?php echo $integracao_origem_id ?>">
            <input type="hidden" name="integracao_order_id" value="<?php echo $integracao_order_id ?>">
            <input type="hidden" name="integracao_client_email" value="<?php echo $integracao_client_email ?>">
            <input type="hidden" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']?>">
            <input type="hidden" name="btSubmit_EPP_8593" value="Continuar">
    </form>
    <script type="text/javascript" nonce="<?php echo $nonce; ?>">
        document.form1.submit();
    </script>
<?php
    }//end if($usuarioGames->b_IsLogin_pagamento_pin_EPP_Cash() && b_pin_forma_pagamento())
    else {
?>
    <script type="text/javascript" nonce="<?php echo $nonce; ?>">
     $('#mostraLista').show();
    </script>
<?php
    }//end else do  if($usuarioGames->b_IsLogin_pagamento_pin_EPP_Cash() && b_pin_forma_pagamento())
} //end if (!$b_bloqueado_BRD6 && !$b_bloqueado_BRD5 && !$b_libera_Deposito && !$b_libera_BancodoBrasil && !$b_libera_BancoItau && !$b_libera_Boleto && !$b_libera_Cielo)

// Redireciona automatico para Pagto Online quando possui somente Boleto como tipo de pagamento habilitado 
if (!$b_libera_Bradesco5 && !$b_libera_Bradesco6 && !$b_libera_Deposito && !$b_libera_BancodoBrasil && !$b_libera_BancoItau && b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']) && !$b_libera_Cielo) {
    if($b_libera_Boleto) {
        ob_clean();
?>
    <form name="form1" action="" method="post">
            <input type="hidden" name="iforma" value="<?php echo $FORMAS_PAGAMENTO['BOLETO_BANCARIO'] ?>">
            <input type="hidden" name="idu" value="<?php echo (($usuarioGames->getId()>0)?$usuarioGames->getId():"0") ?>">
            <input type="hidden" name="sno" value="<?php echo $usuarioGames->getNome() ?>">
            <input type="hidden" name="integracao_is_parceiro" value="<?php echo $integracao_is_parceiro ?>">
            <input type="hidden" name="integracao_origem_id" value="<?php echo $integracao_origem_id ?>">
            <input type="hidden" name="integracao_order_id" value="<?php echo $integracao_order_id ?>">
            <input type="hidden" name="integracao_client_email" value="<?php echo $integracao_client_email ?>">
            <input type="hidden" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['BOLETO_BANCARIO']?>">
            <input type="hidden" name="btSubmit_EPP_8593" value="Continuar">
    </form>
    <script type="text/javascript" nonce="<?php echo $nonce; ?>">
        document.form1.submit();
    </script>
<?php
    }//end if($b_libera_Boleto) 
    else {
?>
    <script type="text/javascript" nonce="<?php echo $nonce; ?>">
     $('#mostraLista').show();
    </script>
<?php
    }//end else do if($b_libera_Boleto) 
} //end if (!$b_libera_Bradesco5 && !$b_libera_Bradesco6 && !$b_libera_Deposito && !$b_libera_BancodoBrasil && !$b_libera_BancoItau && b_is_forma_pagto_bloqueada($integracao_origem_id, $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']) && !$b_libera_Cielo)
ob_end_flush();
?>
<style nonce="<?php echo $nonce; ?>">
	#water-mark {
		background: url("/sys/imagens/teste2.png");
		width: 100px;
		height: 100px;
		background-size: 80px;
		background-position: center;
	}
	
	#logo-inside {
		width: 50%;
		position: absolute;
		top: 30%;
	}
</style>