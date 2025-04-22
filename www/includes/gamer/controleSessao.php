<?php
require_once $raiz_do_projeto . "class/classQuestionarios.php";
	function cancelarSessao(){
	
		// Cancela Sessao (LH) (por via das duvidas) 
		$GLOBALS['_SESSION']['dist_usuarioGames_ser'] = null;
		unset($GLOBALS['_SESSION']['dist_usuarioGames_ser']);

		$GLOBALS['_SESSION']['dist_usuarioGamesOperador_ser'] = null;
		unset($GLOBALS['_SESSION']['dist_usuarioGamesOperador_ser']);

		$GLOBALS['_SESSION']['dist_usuarioGamesOperadorTipo_ser'] = null;
		unset($GLOBALS['_SESSION']['dist_usuarioGamesOperadorTipo_ser']);

		
		// Cancela esta Sessao (Gamer)
		$GLOBALS['_SESSION']['usuarioGames_ser'] = null;
		unset($GLOBALS['_SESSION']['usuarioGames_ser']);

		// Cancela Sessão (Integracao)
		$GLOBALS['_SESSION']['integracao_is_parceiro'] = null;
		unset($GLOBALS['_SESSION']['integracao_is_parceiro']);
		$GLOBALS['_SESSION']['integracao_origem_id'] = null;
		unset($GLOBALS['_SESSION']['integracao_origem_id']);
		$GLOBALS['_SESSION']['integracao_order_id'] = null;
		unset($GLOBALS['_SESSION']['integracao_order_id']);
	
	}

	function isValidaSessao(){
		
            $ret = isset($_SESSION['usuarioGames_ser']) && !is_null($_SESSION['usuarioGames_ser']);

            //Tempo maximo de sessao
            if($ret){
                if(isset($_SESSION['usuarioGames.horarioLogin'])){
                    if((date("U") - $_SESSION['usuarioGames.horarioLogin']) > 30*60) 
                        $ret = false; 
                } else {
                    cancelarSessao();
                    $ret = false;
                }
            }
		
            //Tempo maximo de inatividade
            if($ret){
                if(isset($_SESSION['usuarioGames.horarioInatividade'])){
                    if((date("U") - $_SESSION['usuarioGames.horarioInatividade']) > 30*60) 
                        $ret = false; 
                } else {
                    cancelarSessao();
                    $ret = false;
                }
            }

            return $ret;		
	}


	function validaSessao($parent = null){
		
		$ret = isValidaSessao();

		$usuarioName = "";
		$usuarioGames = ((isset($_SESSION['usuarioGames_ser']))?unserialize($_SESSION['usuarioGames_ser']):null);
		
                if($usuarioGames) {
                    $usuarioName = $usuarioGames->getNome();
		}
                
		$b_script_allowed = b_isIntegracao_allowed_url();
		$b_mostra_script = ((!b_isIntegracao()) || (b_isIntegracao() && b_isIntegracao_allowed_url()) );
		
                gravaLog_TMP(" Nome: '$usuarioName'\n Is integracao?: ".((b_isIntegracao())?"YES":"Nope")."\n Is Logged?: ".((isValidaSessao())?"YES":"nope")." \n URL Allowed?: ".(($b_script_allowed)?"YES":"nope")."\n Mostra Script?: ".(($b_mostra_script)?"YES":"nope")."\n");

		if(!$b_mostra_script) {
                    //Invalida a sessao
                    cancelarSessao();
                    $ret = isValidaSessao();
		}

		//Sessao valida
		if($ret){
                    $_SESSION['usuarioGames.horarioInatividade'] = date("U");
		//Sessao invalida
		} else {

                    //Invalida a sessao
                    cancelarSessao();

                    //redireciona para o login
                    $strRedirect = "/game/conta/login.php?" . urlencode($_SERVER['QUERY_STRING']);
                    //$strRedirect = "/prepag2/commerce/login.php?pag=" . urlencode($_SERVER['SCRIPT_NAME'] . "?" . $_SERVER['QUERY_STRING']);

                    //redireciona
                    if($strRedirect && $strRedirect != ""){
                        //verifica se a pagina atual nao eh a pagina do redirect, senao entra em loop
                        //if instr(1, Request.ServerVariables("URL"), mid(strRedirect, 1, instr(1, strRedirect, "?", 1)-1), 1) = 0 then
                        if(!strpos($_SERVER['SCRIPT_NAME'], substr($strRedirect, 0, strpos($strRedirect, "?")))){

                            //Se eh popup, redireciona a janela principal e fecha o popup
                            if($_REQUEST['pop']){
                                    echo "<html><body OnLoad=\"opener.location.href='$strRedirect';window.close();\"></body></html>";
                            } elseif(!empty($parent)){
                                    echo "<SCRIPT language=\"JavaScript\">\n <!-- \n top.location ='$strRedirect';\n //-->\n </SCRIPT>";
                            } else {
                                    ob_end_clean();
                                    $location = (strtoupper($_SERVER['HTTPS'])=="ON"?"https":"http") . "://" . $_SERVER['HTTP_HOST'] . $strRedirect;
                                    header("Location: " . $location);
                            }
                            exit;
                        }			
                    }
		}

		// Redireciona para Questionario
		$usuarioGames = ((isset($_SESSION['usuarioGames_ser']))?unserialize($_SESSION['usuarioGames_ser']):null);
		if($usuarioGames) {
			$usuarioId = $usuarioGames->getId();
			$questionario = new Questionarios($usuarioId,'G');

			$aux_vetor = $questionario->CapturarProximoQuestionario();

			if($questionario->getBloqueiaMenu()) {
				ob_end_clean();
				$strRedirect = "/creditos/questionario.php?ug_id=".$usuarioId."&ql_tipo_usuario=G";
				header("Location: " . $strRedirect);
				die("Stop ABCG");
			}
		}

		
		return $ret;
		
	}
	
?>
