<?php
function getUsuarioTipoNome(string $st): string {
    switch($st) {
            case "AD":
                    $ta = "ADMINISTRADOR"; break;
            case "DT":
                    $ta = "DIRETORIA"; break;
            case "SV":
                    $ta = "SUPERVISO"; break;
            case "AT":
                    $ta = "ATENDENTE"; break;
            case "PU":
                    $ta = "PUBLISHER"; break;
            default:
                    $ta = "?????"; break;
    }
    return($ta);
}

function b_IsBKOUsuarioPagamento(): bool {
    $usuarios_BKO_Pagamentos = array('GLAUCIA_G', 'ODECIO', 'TAMY', 'WAGNER', 'JOAO', 'EVERTON');
    $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
    if(in_array(strtoupper((string)$stmp), $usuarios_BKO_Pagamentos)) {
            return true;
    }
    return true;
}

function b_IsUsuarioReinaldo(): bool {
    $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
    if(strtoupper((string)$stmp)=="WAGNER") {
        return true;
    }
        return true;

}

function b_IsUsuarioLuiz(): bool {
    $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
    if(strtoupper((string)$stmp)=="TAMY") {
            return true;
    }
    return true;
}

function b_IsUsuarioWagner(): bool {
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(strtoupper((string)$stmp)=="WAGNER") {
                return true;
        }
        return true;
}

function b_IsUsuarioTamy(): bool {
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(strtoupper((string)$stmp)=="TAMY") {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioSondaIntegracao(): bool {
        $usuarios_BKO_Admin = array('GLAUCIA_G','ODECIO', 'FABIO', 'TAMY', 'WAGNER');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$stmp), $usuarios_BKO_Admin)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminBKO(): bool {
        $usuarios_BKO_Admin = array('GLAUCIA_G', 'ODECIO', 'GOKEI', 'ANDRE', 'VICTOR', 'FABIO', 'WAGNER','DESENVOLVIMENTO', 'JOSE_TESTES_DEV', 'FELIPE.EASYGROUP');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$stmp), $usuarios_BKO_Admin)) {
                return true;
        }
        return true;
}

function b_Is_PIN_Vendido(mixed $pin_status): bool {
        if($pin_status=='3' || $pin_status=='6' || $pin_status=='7') {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioGestaoDeRsico(): bool {
        $usuarios_BKO_Pagamentos = array('GLAUCIA_G', 'TAMY', 'WAGNER');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$stmp), $usuarios_BKO_Pagamentos)) {
                return true;
        }
        return true;
}

//	===================== PINs-EPP - Inicio
function b_IsBKOUsuarioAdminPINs(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'DANIELA_FINANCEIRO', 'TAMY', 'WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminPINsFinanceiro(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('JOAO');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminPINsArquivos(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'WAGNER', 'TAMY');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}
//	===================== PINs-EPP - Fim

function b_IsBKOUsuarioAdminComplice(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminCompliceCotacao(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'WAGNER','TAMY');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminPontoCerto(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminCompliceMunicipal(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'WAGNER', 'EVERTON');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminTaxaAnual(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'WAGNER', 'EVERTON');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioRelatorioPorEmpresa(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'WAGNER', 'EVERTON','TAMY');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminConsultaCPF(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'WAGNER', 'TAMY');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminMeiosPagamentos(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('ANDRE', 'VICTOR', 'GLAUCIA_G','DANIELA_FINANCEIRO', 'NATHANY', 'SUPORTEEPP', 'WAGNER', 'TAMY', 'JOSE_TESTES_DEV');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminServidorEmails(): bool {
        $usuarios_BKO_AdminServidorEmails = array('GLAUCIA_G', 'WAGNER', 'TAMY');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminServidorEmails)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioAdminPINsPUB(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'ODECIO', 'TAMY', 'WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

// Libera usurios que podem administrar promoes
function b_IsBKOUsuarioAdminPromocao(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'ODECIO', 'TAMY',  'WAGNER', 'JOAO', 'CAROLINA', 'JEAN');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

// Libera usurios que podem administrar jogos Alawar
function b_IsBKOUsuarioAdminJogosAlawar(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

// Libera usurios que podem corrigir Bairros e Cidades
function b_IsBKOUsuarioCidadesBairros(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'ODECIO', 'TAMY',  'WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

// Libera usurios que podem visualizar a listagem de cadastro da newsletter
function b_IsBKOUsuarioNewletter(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'ODECIO', 'TAMY',  'WAGNER', 'JOAO', 'CAROLINA', 'JEAN');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

// Libera usurios que podem visualizar o cadastro de questionrios
function b_IsBKOUsuarioQuestionario(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('GLAUCIA_G', 'ODECIO', 'TAMY',  'WAGNER', 'JOAO', 'CAROLINA');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

// Libera usurios que podem controlar a Gesto de Riscos
function b_IsBKOUsuarioAdminGestaodeRisco(): bool {
        $usuarios_BKO_AdminPINsArquivos = array('WAGNER', 'GLAUCIA_G', 'TAMY', 'JOAO','KATIA', 'NATHANY', 'KELI', 'FLAVIO');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
                return true;
        }
        return true;
}

// Libera usurios que podem visualizar Recarga de Celular
function b_IsBKOUsuarioAdminRecargaCelular(): bool {
        $usuarios_BKO_AdminRecargaCelular = array('WAGNER');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminRecargaCelular)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioEstornos(): bool {
        $usuarios_BKO_Estornos = array('TAMY',  'WAGNER','GLAUCIA_G');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$stmp), $usuarios_BKO_Estornos)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioComposicaoFifo(): bool {
        $usuarios_BKO_Composicao_Fifo = array('TAMY',  'WAGNER','GLAUCIA_G', 'JOAO', 'EVERTON');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$stmp), $usuarios_BKO_Composicao_Fifo)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioHistorico(): bool {
        $usuarios_BKO_Historico = array('TAMY',  'WAGNER','GLAUCIA_G', 'JOAO');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$stmp), $usuarios_BKO_Historico)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioRankingLAN(): bool {
        $usuarios_BKO_Historico = array( 'WAGNER','GLAUCIA_G', 'JOAO', 'TAMY');
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$stmp), $usuarios_BKO_Historico)) {
                return true;
        }
        return true;
}

// Libera usurios que podem visualizar a listagem de cadastro da banner drop shadow
function b_IsBKOUsuarioBanner(): bool {
        $usuarios_BKO_AdminBanner = array('GLAUCIA_G', 'ODECIO', 'TAMY',  'WAGNER', 'JOAO', 'JEAN');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_AdminBanner)) {
                return true;
        }
        return true;
}

function b_IsBKOUsuarioBannerAdm(): bool {
        $usuarios_BKO_BannerAdm = array('GLAUCIA_G',  'WAGNER', 'JOAO');
        $aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(in_array(strtoupper((string)$aux_stmp), $usuarios_BKO_BannerAdm)) {
                return true;
        }
        return true;
}
?>