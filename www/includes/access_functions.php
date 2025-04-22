<?php
function getUsuarioTipoNome($st) {
    switch($st) {
            case "AD":
                    $ta = "ADMINISTRADOR"; break;
            case "DT":
                    $ta = "DIRETORIA"; break;
            case "SV":
                    $ta = "SUPERVIS�O"; break;
            case "AT":
                    $ta = "ATENDENTE"; break;
            case "PU":
                    $ta = "PUBLISHER"; break;
            default:
                    $ta = "?????"; break;
    }
    return($ta);
}

function b_IsBKOUsuarioPagamento(){
    $usuarios_BKO_Pagamentos = array('GLAUCIA', 'ODECIO', 'TAMY', 'WAGNER', 'JOAO', 'EVERTON');
    $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
    if(in_array(strtoupper($stmp), $usuarios_BKO_Pagamentos)) {
            return true;
    }
    return false;
}

function b_IsUsuarioReinaldo(){
    $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
    if(strtoupper($stmp)=="WAGNER") {
        return true;
    }
        return false;

}

function b_IsUsuarioLuiz(){
    $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
    if(strtoupper($stmp)=="TAMY") {
            return true;
    }
    return false;
}

function b_IsUsuarioWagner(){
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(strtoupper($stmp)=="WAGNER") {
                return true;
        }
        return false;
}

function b_IsUsuarioTamy(){
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(strtoupper($stmp)=="TAMY") {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioSondaIntegracao(){
        $usuarios_BKO_Admin = array('GLAUCIA','ODECIO', 'FABIO', 'TAMY', 'WAGNER');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($stmp), $usuarios_BKO_Admin)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminBKO(){
        $usuarios_BKO_Admin = array('GLAUCIA', 'ODECIO', 'GOKEI', 'ANDRE', 'VICTOR', 'FABIO', 'WAGNER','DESENVOLVIMENTO', 'JOSEC.EASYGROUP', 'FELIPE.EASYGROUP');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($stmp), $usuarios_BKO_Admin)) {
                return true;
        }
        return false;
}

function b_Is_PIN_Vendido($pin_status){
        if($pin_status=='3' || $pin_status=='6' || $pin_status=='7') {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioGestaoDeRsico(){
        $usuarios_BKO_Pagamentos = array('GLAUCIA', 'TAMY', 'WAGNER');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($stmp), $usuarios_BKO_Pagamentos)) {
                return true;
        }
        return false;
}

//	===================== PINs-EPP - Inicio
function b_IsBKOUsuarioAdminPINs(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'DANIELA', 'TAMY', 'WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminPINsFinanceiro(){
        $usuarios_BKO_AdminPINsArquivos = array('JOAO');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminPINsArquivos(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'WAGNER', 'TAMY');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}
//	===================== PINs-EPP - Fim

function b_IsBKOUsuarioAdminComplice(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminCompliceCotacao(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'WAGNER','TAMY');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminPontoCerto(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminCompliceMunicipal(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'WAGNER', 'EVERTON');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminTaxaAnual(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'WAGNER', 'EVERTON');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioRelatorioPorEmpresa(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'WAGNER', 'EVERTON','TAMY');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminConsultaCPF(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'WAGNER', 'TAMY');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminMeiosPagamentos(){
        $usuarios_BKO_AdminPINsArquivos = array('ANDRE', 'VICTOR', 'GLAUCIA','DANIELA', 'NATHANY', 'SUPORTEEPP', 'WAGNER', 'TAMY', 'JOSEC.EASYGROUP');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminServidorEmails(){
        $usuarios_BKO_AdminServidorEmails = array('GLAUCIA', 'WAGNER', 'TAMY');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminServidorEmails)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioAdminPINsPUB(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'ODECIO', 'TAMY', 'WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

// Libera usu�rios que podem administrar promo��es
function b_IsBKOUsuarioAdminPromocao(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'ODECIO', 'TAMY',  'WAGNER', 'JOAO', 'CAROLINA', 'JEAN');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

// Libera usu�rios que podem administrar jogos Alawar
function b_IsBKOUsuarioAdminJogosAlawar(){
        $usuarios_BKO_AdminPINsArquivos = array('WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

// Libera usu�rios que podem corrigir Bairros e Cidades
function b_IsBKOUsuarioCidadesBairros(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'ODECIO', 'TAMY',  'WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

// Libera usu�rios que podem visualizar a listagem de cadastro da newsletter
function b_IsBKOUsuarioNewletter(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'ODECIO', 'TAMY',  'WAGNER', 'JOAO', 'CAROLINA', 'JEAN');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

// Libera usu�rios que podem visualizar o cadastro de question�rios
function b_IsBKOUsuarioQuestionario(){
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'ODECIO', 'TAMY',  'WAGNER', 'JOAO', 'CAROLINA');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

// Libera usu�rios que podem controlar a Gest�o de Riscos
function b_IsBKOUsuarioAdminGestaodeRisco(){
        $usuarios_BKO_AdminPINsArquivos = array('WAGNER', 'GLAUCIA', 'TAMY', 'JOAO','KATIA', 'NATHANY', 'KELI', 'FLAVIO');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return false;
}

// Libera usu�rios que podem visualizar Recarga de Celular
function b_IsBKOUsuarioAdminRecargaCelular(){
        $usuarios_BKO_AdminRecargaCelular = array('WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminRecargaCelular)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioEstornos(){
        $usuarios_BKO_Estornos = array('TAMY',  'WAGNER','GLAUCIA');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($stmp), $usuarios_BKO_Estornos)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioComposicaoFifo(){
        $usuarios_BKO_Composicao_Fifo = array('TAMY',  'WAGNER','GLAUCIA', 'JOAO', 'EVERTON');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($stmp), $usuarios_BKO_Composicao_Fifo)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioHistorico(){
        $usuarios_BKO_Historico = array('TAMY',  'WAGNER','GLAUCIA', 'JOAO');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($stmp), $usuarios_BKO_Historico)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioRankingLAN(){
        $usuarios_BKO_Historico = array( 'WAGNER','GLAUCIA', 'JOAO', 'TAMY');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($stmp), $usuarios_BKO_Historico)) {
                return true;
        }
        return false;
}

// Libera usu�rios que podem visualizar a listagem de cadastro da banner drop shadow
function b_IsBKOUsuarioBanner(){
        $usuarios_BKO_AdminBanner = array('GLAUCIA', 'ODECIO', 'TAMY',  'WAGNER', 'JOAO', 'JEAN');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminBanner)) {
                return true;
        }
        return false;
}

function b_IsBKOUsuarioBannerAdm(){
        $usuarios_BKO_BannerAdm = array('GLAUCIA',  'WAGNER', 'JOAO');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper($aux_stmp), $usuarios_BKO_BannerAdm)) {
                return true;
        }
        return false;
}
?>