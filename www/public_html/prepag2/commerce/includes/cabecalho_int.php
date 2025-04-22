<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once DIR_INCS . "inc_register_globals.php";    	
// include do arquivo contendo IPs DEV
require_once DIR_INCS . "configIP.php";

require_once DIR_CLASS . "pdv/classHelper.php";

$server_url = "www.e-prepag.com.br";
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}
?>
<html>
    <head>
    <title>E-Prepag - Créditos para games online<?php echo ((isset($pagina_titulo))?" - ".$pagina_titulo:""); ?></title> 
<?php
require_once DIR_INCS . "meta.php";
//exibindo o banner somente para usuários de NÃO Integração
if (!b_isIntegracao()) {

	 //  Começa bloco para novo banner 
	// ### Define banner superior
	$varRoot = "/eprepag";
	$varRoot_url = "http://" . $server_url . "/eprepag/";

	$sTiposup = " AND ((tiposup=0) OR (tiposup=1)) ";	// Banner dos tipos "Home" ou "Todos";
	$sPath = "/eprepag/";	

	require_once DIR_INCS . "inc_bannersuperior.php"; 
?>
<script language="javascript">
addLoadEvent(carregaBanner);
</script>
<?php
}//end if (!b_isIntegracao())

        $strRequestURI_Jogos = strstr($_SERVER["REQUEST_URI"], '/prepag2/commerce/jogos/');
        $strRequestURI_Ofertas = strstr($_SERVER["REQUEST_URI"], '/prepag2/commerce/ofertas/');

        // Para modificar alinhamento em p?gina de jogos Alawar
        if(trim($strRequestURI_Jogos) || trim($strRequestURI_Ofertas)) {
                $styleMain = 'style="width: 930px;"';	// 02-04-2012 -> Esta <div> define o tamanho da area de conteudo para todas as paginas
                $tableNavWidth = '100%';	// 02-04-2012 -> Barra de navegacao das paginas, antes estava com width="779" 
                $styleConteudo= 'style="padding:0;"';	// 02-04-2012 -> Esta <div> posiciona o conteudo principal na pagina. Precisei remover o Padding do elemento para 0px
        }
        else {
                $tableNavWidth = '779';
        }

require_once DIR_INCS . "configuracao.inc";
if(isValidaSessao()){
        // identifica se mostra a navegação com Saldo, para visitantes de integração fica bloqueado
        $b_show_saldo_and_navegation = (!b_isIntegracao() || ( (b_isIntegracao() && b_isIntegracao_logged_in() ) ) );

        $usuarioGames = unserialize($_SESSION['usuarioGames_ser']); 
        if (isset($_SESSION['epp_origem']) && strlen($_SESSION['epp_origem'])>0) {
        }//end if (isset($_SESSION['epp_origem']) && strlen($_SESSION['epp_origem'])>0)
        else {
            if(function_exists('getLinkURL_By_ID')) {
                $aux_link_URL = getLinkURL_By_ID($GLOBALS['_SESSION']['integracao_origem_id']);
            }//end if(function_exists('getLinkURL_By_ID')) 
        }//end else do if (isset($_SESSION['epp_origem']) && strlen($_SESSION['epp_origem'])>0)

        // Para mostrar o saldo -> mais um table cell
        if($usuarioGames->b_IsLogin_pagamento_pin_EPP_Cash() && b_pin_forma_pagamento()) {
                if(!$path_imgs && (
                        (strpos($_SERVER['PHP_SELF'], "conta")!==false) || (strpos($_SERVER['PHP_SELF'], "jogos")!==false)  || (strpos($_SERVER['PHP_SELF'], "ofertas")!==false) 
                        )) {
                        $path_imgs = "../";
                }
        }
        // É integração?
        $b_is_integracao = b_isIntegracao();

        // Tem questionario pendente?
        $usuarioId = $usuarioGames->getId();
        $questionario = new Questionarios($usuarioId,'G');
        $aux_vetor = $questionario->CapturarProximoQuestionario();
        $b_is_questionario = $questionario->getBloqueiaMenu();

        ob_clean();
        
        
        ?>
        <!DOCTYPE html heys>
        <html>
        <head>
        <script type="text/javascript" src="/js/scripts.js"></script>
        <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
        <link href="/css/creditos.css" rel="stylesheet" type="text/css" />
        <link href="/css/game.css" rel="stylesheet" type="text/css" />
        <!-- includes js -->
        <script type="text/javascript" src="/js/jquery.js"></script>
        <script type="text/javascript" src="/includes//bootstrap/js/bootstrap.min.js"></script>
        <link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
        <script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
        <script type="text/javascript" src="/js/modalwaitingfor.js"></script>
        <script src="/js/valida.js"></script>
        <!--<link href="/incs/css.css" rel="stylesheet" type="text/css"/>-->
        </head>
        <body class="bg-cinza-claro txt-preto">
        <div id="modal-load" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4 class="modal-title txt-vermelho" id="modal-title">Erro de preenchimento</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" id="tipo-modal" role="alert"> 
                          <h5><span id="error-text">PINs E-Prepag: São milhares de Lan Houses, lojas de games, de informáticas e vários outros tipos de comércio em todo o Brasil.</span></h5>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php 
        $GLOBALS["jquery"] = true;
        echo integracao_layout('css');
        modal_includes();
        echo integracao_layout('header');
        echo integracao_layout('order'); 
}
else {
    redirect("../index.php");
}
?>