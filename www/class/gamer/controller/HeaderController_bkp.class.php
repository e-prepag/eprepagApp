<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
set_time_limit(180);
ini_set('max_execution_time', 180); 
session_start();

require_once RAIZ_DO_PROJETO.'includes/configIP.php';

$server_url = $GLOBALS['_SERVER']['SERVER_NAME'];
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}

if($_SERVER['HTTPS']!="on") {
    Header("Location: https://".$server_url.$_SERVER['REQUEST_URI']);
    die();
} //end if($_SERVER['HTTPS']!="on") 

if(!checkIP()){
    if(strpos(strtolower($GLOBALS['_SERVER']['SERVER_NAME']), "www.") === false){
        header("Location: " . EPREPAG_URL_HTTPS . "" . $_SERVER['REQUEST_URI']);
        die();
    }
    elseif(strpos(strtolower($GLOBALS['_SERVER']['SERVER_NAME']), ".br") === false){
        header("Location: " . EPREPAG_URL_HTTPS . "" . $_SERVER['REQUEST_URI']);
        die();
    }
} 
header('Content-Type: text/html; charset=ISO-8859-1');
require_once RAIZ_DO_PROJETO."class/util/Busca.class.php";
require_once RAIZ_DO_PROJETO."class/business/BannerBO.class.php";
require_once RAIZ_DO_PROJETO."db/ConnectionPDO.php";
require_once DIR_INCS."main.php";
require_once DIR_INCS."gamer/main.php";

$sessId = session_id();

if(!empty($sessId))
{
    if(!empty($_SESSION['integracao_is_parceiro']) || !empty($_SESSION['integracao_origem_id']) || !empty($_SESSION['integracao_order_id'])) {
        unset($_SESSION);
        session_destroy();
        session_start();
    } 
}

class HeaderController{
    
    public $objBanners;
    public $usuario;
    public $logado = false;
    private $_loginRedirect =  array
                                (
                                    "/game/pedido/deposito.php",
                                    "/game/pedido/deposito-informado.php",
                                    "/game/pedido/finalizado.php",
                                    "/game/carteira/detalhe-pedido.php",
                                    "/game/conta/add-saldo.php",
                                    "/game/conta/dados-acesso.php",
                                    "/game/conta/depositos-processamento.php",
                                    "/game/conta/detalhe-deposito.php",
                                    "/game/conta/detalhe-pedido.php",
                                    "/game/conta/extrato.php",
                                    "/game/conta/meus-dados.php",
                                    "/game/conta/pedidos.php",
                                    "/game/mensagem.php",
                                    "/game/pagamento/finaliza_deposito.php",
                                    "/game/pagamento/finaliza_venda.php",
                                    "/game/pagamento/informa_deposito.php",
                                    "/game/pagamento/pagto_compr_boleto.php",
                                    "/game/pagamento/pagto_compr_offline.php",
                                    "/game/pagamento/pagto_compr_online.php",
                                    "/game/credito/meios-pagamento.php",
                                    "/game/credito/deposito_epp_cash.php"
                                ); // array com paginas que exibem e/ou dao a possibilidade de alterar informacoes sigilosas (senha, pins etc)
    
    private $_loginPaymentRedirect = array(
                                    "/game/pedido/passo-2.php"
            );

    public function __construct(){
        if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
            $this->usuario = unserialize($_SESSION['usuarioGames_ser']);
            $this->logado = true; 
        }
        
        $this->accessVerify();
        
        $this->objBanners = new BannerBO;
    }
    
    public function setHeader(){
        require_once ($this->logado) ? DIR_GAMES."includes/header.php" : DIR_GAMES."includes/header-off.php";
    }


    public function getBanner($posicao, $categoria = "Gamer"){
        return $this->objBanners->getBannersFromJson($posicao,$categoria);
    }
    
    public function atualizaSessaoUsuario(){
        if($this->usuario && !empty($this->usuario->getId())){
            $instUsuarioGames = new UsuarioGames();
            $tmp = $instUsuarioGames->getUsuarioGamesById($this->usuario->getId());
            $_SESSION['usuarioGames_ser'] = serialize($tmp);
            $this->usuario = $tmp;
        }else{
            header("Location: /game/conta/login.php");
            die();
        }
    }
	public function verifica_cpf_usuario($cpf){
		 $usu = new UsuarioGames();
		 $retornousu = $usu->verifica_situacao_cpf($cpf);
		 return $retornousu;
	}
    
    private function accessVerify(){
        
        if(!$this->logado){
            if(in_array($_SERVER['PHP_SELF'], $this->_loginRedirect)){
                Util::redirect("/game/conta/login.php");
            }else if(in_array($_SERVER['PHP_SELF'], $this->_loginPaymentRedirect)){
                Util::redirect("/game/pedido/pagamento-offline.php");
            }
        }else{
            if(in_array($_SERVER['PHP_SELF'], $this->_loginRedirect) || in_array($_SERVER['PHP_SELF'], $this->_loginPaymentRedirect)){
                validaSessao();
            }
        }
        
    }
}