<?php

function cancelarSessao(){

        // Cancela esta Sessao (LH) 
        $_SESSION['dist_usuarioGames_ser'] = null;
        unset($_SESSION['dist_usuarioGames_ser']);

        $_SESSION['dist_usuarioGamesOperador_ser'] = null;
        unset($_SESSION['dist_usuarioGamesOperador_ser']);

        $_SESSION['dist_usuarioGamesOperadorTipo_ser'] = null;
        unset($_SESSION['dist_usuarioGamesOperadorTipo_ser']);

        // Cancela Sessao de Gamer (por via das duvidas)
        $_SESSION['usuarioGames_ser'] = null;
        unset($_SESSION['usuarioGames_ser']);

        // Cancela Sessão (Integracao)
        $GLOBALS['_SESSION']['integracao_is_parceiro'] = null;
        unset($GLOBALS['_SESSION']['integracao_is_parceiro']);
        $GLOBALS['_SESSION']['integracao_origem_id'] = null;
        unset($GLOBALS['_SESSION']['integracao_origem_id']);
        $GLOBALS['_SESSION']['integracao_order_id'] = null;
        unset($GLOBALS['_SESSION']['integracao_order_id']);


        $GLOBALS['_SESSION']['nx_idsessaonex'] = null;
        unset($_SESSION['nx_idsessaonex']);

        @session_destroy();
}

function isValidaSessao(){
        global $_PaginaOperador1Permitido;
        global $_PaginaOperador2Permitido;
        $ret = true;
        $ret_op = isSessionOperador();
        // Página não é de operadores
        if(!$ret_op){
                $ret = false;
        }
        // Página de operadores
        if($ret_op){
                $ret_pag = (isset($_PaginaOperador1Permitido) && !is_null($_PaginaOperador1Permitido) && $_PaginaOperador1Permitido==53) ||
                                        (isset($_PaginaOperador2Permitido) && !is_null($_PaginaOperador2Permitido) && $_PaginaOperador2Permitido==54) ;
                if(!$ret_pag) $ret = false;
        }
        //Tempo maximo de sessao
        if($ret_op){
                if(isset($_SESSION['dist_usuarioGamesOperador.horarioLogin'])){
                        if((date("U") - $_SESSION['dist_usuarioGamesOperador.horarioLogin']) > 30*60) $ret = false; 
                } else {
                        $ret = false;
                }
        }
        //Tempo maximo de inatividade
        if($ret_op){
                if(isset($_SESSION['dist_usuarioGamesOperador.horarioInatividade'])){
                        if((date("U") - $_SESSION['dist_usuarioGamesOperador.horarioInatividade']) > 30*60) $ret = false; 
                } else {
                        $ret = false;
                }
        }
        // Se não é usuário operador valida usuário lanhouse
        if(!$ret_op){
                $ret = isSessionLanHouse();
                if($ret){
                        //Tempo maximo de sessao
                        if($ret){
                                if(isset($_SESSION['dist_usuarioGames.horarioLogin'])){
                                        if((date("U") - $_SESSION['dist_usuarioGames.horarioLogin']) > 30*60) $ret = false; 
                                } else {
                                        $ret = false;
                                }
                        }

                        //Tempo maximo de inatividade
                        if($ret){
                                if(isset($_SESSION['dist_usuarioGames.horarioInatividade'])){
                                        if((date("U") - $_SESSION['dist_usuarioGames.horarioInatividade']) > 30*60) $ret = false; 
                                } else {
                                        $ret = false;
                                }
                        }
                } 
        }
        return $ret;		
}


function validaSessao(){

        $ret = isValidaSessao();

        //Sessao valida
        if($ret){
                $_SESSION['dist_usuarioGames.horarioInatividade'] = date("U");
                $_SESSION['dist_usuarioGamesOperador.horarioInatividade'] = date("U");

        //Sessao invalida
        } else {

                //Invalida a sessao
                cancelarSessao();

                //redireciona para o login
                if($_SERVER['SCRIPT_NAME'] != '/prepag2/dist_commerce/conta/index.php') {
                    $strRedirect = "/creditos/login.php?pag=" . urlencode($_SERVER['SCRIPT_NAME'] . "?" . $_SERVER['QUERY_STRING']);
                }
                else {
                    $strRedirect = "/creditos/login.php?pag=" . urlencode("/creditos/");
                }

                //redireciona
                if($strRedirect && $strRedirect != ""){

                        //verifica se a pagina atual nao eh a pagina do redirect, senao entra em loop
                        //if instr(1, Request.ServerVariables("URL"), mid(strRedirect, 1, instr(1, strRedirect, "?", 1)-1), 1) = 0 then
                        if(!strpos($_SERVER['SCRIPT_NAME'], substr($strRedirect, 0, strpos($strRedirect, "?")))){

                                //Se eh popup, redireciona a janela principal e fecha o popup
                                if($_REQUEST['pop']){
                                        echo "<html><body OnLoad=\"opener.location.href='$strRedirect';window.close();\"></body></html>";
                                } else {
                                        ob_end_clean();
                                        $location = (strtoupper($_SERVER['HTTPS'])=="ON"?"https":"http") . "://" . $_SERVER['HTTP_HOST'] . $strRedirect;
                                        echo "<script>
                                                top.location.href = '".$location."';
                                             </script>";
                                        //header("Location: " . $location);
                                }
                                exit;
                        }			
                }
        }

        $usuarioGames = ((isset($_SESSION['dist_usuarioGames_ser']))?unserialize($_SESSION['dist_usuarioGames_ser']):null);
        if($usuarioGames) {
                //inicio do bloco de redirecionamento para alteração de senha
                $ug_alterar_senha = $usuarioGames->getAlteraSenha();
                //testando se é a própria página do redirecionamento
                if($GLOBALS['_SERVER']['PHP_SELF']!="/prepag2/dist_commerce/conta/alterar_senha_novo.php") {
                        //teste se é para alterar a senha
                        if($ug_alterar_senha==1) {
                                ob_end_clean();
                                $strRedirect = "/prepag2/dist_commerce/conta/alterar_senha_novo.php";
                                header("Location: " . $strRedirect);
                                die("Stop ALTERA SENHA");
                        }
                }
                //fim do bloco de redirecionamento para alteração de senha

                if($ug_alterar_senha!=1) {
                        // Redireciona para Questionario
                        $usuarioId = $usuarioGames->getId();

                        $questionario = new Questionarios($usuarioId,'L');

                        $aux_vetor = $questionario->CapturarProximoQuestionario();

                        if($questionario->getBloqueiaMenu()) {
                                ob_end_clean();
                                $strRedirect = "/creditos/questionario.php?ug_id=".$usuarioId."&ql_tipo_usuario=L";
                                header("Location: " . $strRedirect);
                                die("Stop ABC");
                        }
                        //Fim Redireciona para Questionario
                }			
        }

        return $ret;

}

function isSessionOperador() {
        $ret = isset($_SESSION['dist_usuarioGamesOperador_ser']) && !is_null($_SESSION['dist_usuarioGamesOperador_ser']);
        return $ret;
}

function isSessionOperadorTipo1() {
        $ret = false;
        if(isSessionOperador()) {
                if(isset($_SESSION['dist_usuarioGamesOperadorTipo_ser']) && !is_null($_SESSION['dist_usuarioGamesOperadorTipo_ser'])) {
                        if($_SESSION['dist_usuarioGamesOperadorTipo_ser']==$GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_1]) {
                                $ret = true;
                        }
                }
        }
        return $ret;
}
function isSessionOperadorTipo2() {
        $ret = false;
        if(isSessionOperador()) {
                if(isset($_SESSION['dist_usuarioGamesOperadorTipo_ser']) && !is_null($_SESSION['dist_usuarioGamesOperadorTipo_ser'])) {
                        if($_SESSION['dist_usuarioGamesOperadorTipo_ser']==$GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]) {
                                $ret = true;
                        }
                }
        }
        return $ret;
}

function isSessionLanHouse() {
        $ret = isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser']) && !isSessionOperador();
        return $ret;
}
	
?>
