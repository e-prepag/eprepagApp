<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php

/* 
 * arquivo para ser replicado em HeaderController posteriormente
 */
require_once "../../../includes/constantes.php";
require_once DIR_INCS."inc_register_globals.php";

$server_url = "" . EPREPAG_URL . "";
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}

//exibindo o banner somente para usuários de NÃO Integração
if (!b_isIntegracao()) {
    $sProtocol = (($_SERVER['HTTPS']=="on")?"HTTPS":"HTTP");
    $spref = "";
    if(($GLOBALS['_SERVER']['HTTPS']=="on") && ($GLOBALS['_SERVER']['SERVER_PORT']==443)) {
            $spref = "s";
    }
    
    //  Começa bloco para novo banner 
    // ### Define banner superior
    $varRoot = "/eprepag";
    $varRoot_url = "http".$spref."://" . $server_url . "/eprepag/";

    $sTiposup = " AND ((tiposup=0) OR (tiposup=1)) ";    // Banner dos tipos "Home" ou "Todos";
    $sPath = "/eprepag/";    

    $strRequestURI_Jogos = strstr($_SERVER["REQUEST_URI"], '/prepag2/commerce/jogos/');
    $strRequestURI_Ofertas = strstr($_SERVER["REQUEST_URI"], '/prepag2/commerce/ofertas/');
}

require_once DIR_INCS."configuracao.inc";
    
if (!(isset($_SESSION['epp_origem']) && strlen($_SESSION['epp_origem'])>0)) {
    if(function_exists('getLinkURL_By_ID')) {
        $aux_link_URL = getLinkURL_By_ID($GLOBALS['_SESSION']['integracao_origem_id']);
    }//end if(function_exists('getLinkURL_By_ID')) 
}//end else do if (isset($_SESSION['epp_origem']) && strlen($_SESSION['epp_origem'])>0)


// É integração?
$b_is_integracao = b_isIntegracao();

// Tem questionario pendente? sprint 2
//$usuarioId = $usuarioGames->getId();
//$questionario = new Questionarios($usuarioId,'G');
//$aux_vetor = $questionario->CapturarProximoQuestionario();
//$b_is_questionario = $questionario->getBloqueiaMenu();
