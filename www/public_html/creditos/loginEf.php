<?php
	require_once "../../includes/constantes.php";
    require_once DIR_INCS . "main.php";
    require_once DIR_INCS . "pdv/main.php";
    require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";

	// include do arquivo contendo IPs DEV
    require_once DIR_INCS . "configIP.php";
    require_once DIR_CLASS . "util/Login.class.php";
	
	require_once "/www/db/connect.php";
    require_once "/www/db/ConnectionPDO.php";
	
	$connection = ConnectionPDO::getConnection()->getLink();
	
	
	$sqlVerificaBloqueio = "select * from bloqueios_login_pdv where login = :LOGIN and tentativas >= 5;" ;
	
	$queryVerificaBloqueio = $connection->prepare($sqlVerificaBloqueio);
	$queryVerificaBloqueio->bindValue(":LOGIN", $_REQUEST["login"]);
	$queryVerificaBloqueio->execute();
	
	$resultadoDaVerificacao = $queryVerificaBloqueio->fetch(PDO::FETCH_ASSOC);
	
	if($resultadoDaVerificacao !== false) {
		header("Location: pagina_bloqueio.php");
		exit();
	}
	/*
		NOTA:::
		
		Se o usuário está na tabela de bloqueio e atingiu o limite de tentativas, ele será direcionado à pagina_bloquieio.php e não conseguirá logar.
		Se não, a sessão seguirá com o restante do fluxo.
		
	*/

    if (checkIP()) {
        $server_url = $_SERVER["SERVER_NAME"];
    }
	
	if ($_SERVER["HTTPS"] != "on") {
        redirect("https://" . $server_url . "/creditos/login.php");
        die();
    } // NOTA::: Faz o redirecionamento adicionando SSL na URL.
	
	session_destroy();
	
	/*
		NOTA:::
		
		Esse session_destroy() está comentado porque limpa os dados da sessão.
		
		É bom descomentar e executá-lo para limpar os dados de testes.
	*/
	
    session_start(); // Inicia a sessão
	
	$msg = ""; // Define a variável mensagem
	
	
	
	
    if (!isset($_SESSION["tentativas_login"])) {
        
		$_SESSION["tentativas_login"] = 1;
		
    } else {
        
		if ($_SESSION["tentativas_login"] >= 5) {
            
			bloquearAcesso();
			
        }
        
		$_SESSION["tentativas_login"]++;
    }
	/*
		NOTA:::
		
		Se "tentativas_login" estiver definido e não for nulo, adiciona o valor 1.
		Caso a quatidade de tentativas for igual ou maior que 5, chama a função bloquearAcesso() .
		Se não, incrementa "tentativas_login" .
	*/
	
	if (isset($_SESSION["bloqueado"]) && $_SESSION["bloqueado"] == true) {
        
		global $connection;
		        
		$sql = "select * from bloqueios_login_pdv where ip = :IP;";
        
		$query = $connection->prepare($sql);
        $query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
        $query->execute();
        
		$result = $query->fetch(PDO::FETCH_ASSOC);
		
		if($result['tentativas'] >= 5) {
			
			header("Location: pagina_bloqueio.php");
			exit();
		}
		else {
			
			unset($_SESSION["bloqueado"]);
			$_SESSION["tentativas_login"] = 1;
			
		}
    }
	/*
		NOTA:::
		
		O código acima verifica se a sessão foi bloqueada e se o IP estão no banco de dados.
	
		Caso esteja bloqueado e no banco de dados, envia para pagina_bloqueio.php .
		
		Se não, limpa a condição "bloqueado" da sessão e adiciona "tentativas_login" igual a 1.
	*/
	
    function bloquearAcesso(){
		
        global $connection;
			
		$_SESSION["bloqueado"] = true;
		
		$sql = "select * from bloqueios_login_pdv where ip = :IP;";
			
		$query = $connection->prepare($sql);
		$query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
		$query->execute();
			
		$result = $query->fetch(PDO::FETCH_ASSOC);
			
		if ($result !== false){
			
		    $sqlUpdate = "update bloqueios_login_pdv set created = :DATE_TIME, tentativas = :TENTATIVAS where ip = :IP;";
				
			$query = $connection->prepare($sqlUpdate);
			
			$query->bindValue(":DATE_TIME", date("m-d-Y H:i:s"));
			$query->bindValue(":TENTATIVAS", $_SESSION["tentativas_login"]);
			$query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
			$query->execute();
			
		} else {
			
			$sqlInsert = "insert into bloqueios_login_pdv(id, ug_id, created, ip, login, tentativas, visualizacao) values (default, NULL, :DATE_TIME, :IP, :LOGIN, :TENTATIVAS, 'S');";
				
			$query = $connection->prepare($sqlInsert);
				
			$query->bindValue(":DATE_TIME", date("m-d-Y H:i:s"));
			$query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
			$query->bindValue(":LOGIN", $_REQUEST["login"]);
			$query->bindValue(":TENTATIVAS", $_SESSION["tentativas_login"]);
			$query->execute();
		
		}
		
		header("Location: pagina_bloqueio.php");
        exit;
			
    } // NOTA::: Atualiza ou insere no banco de dados as informações da sessão / login bloqueado.
	
	$pag = $_REQUEST["pag"];
    $login = $_REQUEST["login"];
    $senha = $_REQUEST["senha"];
    $recaptcha = $_REQUEST["g-recaptcha-response"];
		
    if ($_REQUEST["g-recaptcha-response"] != "") {
        
		$tokenInfo = [
            "secret" => "6Lc4XtkkAAAAAJYRV2wnZk_PrI7FFNaNR24h7koQ",
            "response" => $_REQUEST["g-recaptcha-response"],
            "remoteip" => $_SERVER["REMOTE_ADDR"],
        ];
        
		$recaptcha = curl_init();
        
		curl_setopt_array($recaptcha, [
            CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($tokenInfo),
        ]);
        
		$dadosT = curl_exec($recaptcha);
		
		$inforCurl = curl_getinfo($recaptcha);
		

		
		$retorno = json_decode($dadosT, true);
		

        curl_close($recaptcha);
        
		if ($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))) {
            $msg .= "Recaptcha Errado. \n";
        }
    } else {
        $msg .= "Recaptcha Errado. \n";
    }

	/*
		NOTA:::
		
		Verifica se a resposta do recaptcha está OK.
		
		Se não estiver OK ou se estiver vazia, adiciona a mensagem de erro na variável $msg .
	*/

    if (substr($pag, 0, 23) == "/creditos/") {
        $pag = "http" . ($_SERVER["HTTPS"] == "on" ? "s" : "") . "://" . $server_url . $pag;
        //	echo "new pag: '".$pag."'<br>";
    }
	
	/*
		NOTA:::
		
		Parece que o código acima é uma failsafe que corrige a URL.
	*/
	
	
	/*
		$msg = "Usuario bloqueado. Por favor, tente novamente em %s.";
		$msg = "Usuario bloqueado. Para desbloquear seu acesso, entre em contato <a href='https://www.e-prepag.com.br/game/suporte.php' title='Desbloquear Acesso'>aqui<a>.";
		
		NOTA:::
		
		Inicialmente, a delacaração de $msg acima estava ativa, porém, aparentemente, estava interferindo na contagem de tentativas.
	*/
	
	
    $strRedirect = "https://" . $server_url . "/creditos/login.php?login=" . urlencode($login) . "&msg=";
	
	$clsLogin = new Login();
	
    if (file_exists(DIR_INCS . "attrLogin.php")) {
        
		require_once DIR_INCS . "attrLogin.php";
        $clsLogin->setTempoDesbloqueio($cfgLoginLan->tempoMaxBloqueio);
        $clsLogin->setMaxTentativas($cfgLoginLan->maxTentativas);
		
    }
	
    $clsLogin->setUrlRedirect($strRedirect);
    $clsLogin->setMsgErro($msg);
    $clsLogin->autentica();
	
	/*
		NOTA:::
		
		Faz a instância para validar os dados, usando como referência os limites de tentativas + tempo de bloqueio.
	
	*/
	
	
    if (!$login || $login == "") {
        $msg .= "O login deve ser preenchido.\n";
    }
	
    if (!$senha || $senha == "") {
        $msg .= "A senha deve ser preenchida.\n";
    }
	
    if (!$recaptcha || $recaptcha == "") {
        $msg .= "Recaptcha incorreto. \n";
    }
	
	
	
    if ($msg != "") {
            $strRedirect = "https://" . $server_url . "/creditos/login.php?pag=" . urlencode($pag) . "&msg=" . urlencode($msg) . "&login=" . urlencode($login . "&tentativas=" . urlencode($_SESSION["tentativas_login"]));
    }

    if ($msg == "") {
		if(!isset($_SESSION["tentativas_login"]) || $_SESSION["tentativas_login"] <= 0) {
			$_SESSION = [];
			@session_destroy();
			session_start();
			session_regenerate_id();
		}
		
        function verificaPOST($referer, $POST) {
            //if (strpos($referer,"bronzato.com.br")===false && strpos($referer,"contause.digital")===false){return false;}
            $flag = true;
            foreach ($_POST as $xa => $xb) {
                $xb = serialize($xb);
                if (
                    strpos($xb, "dbms_pipe.receive_message") !== false ||
                    strpos($xb, "DBMS_PIPE.RECEIVE_MESSAGE") !== false ||
                    strpos($xb, "delete") !== false ||
                    strpos($xb, "delete") !== false ||
                    strpos($xb, "update") !== false ||
                    strpos($xb, "select") !== false
                ) {
                    return false;
                }
                            if (
                    strpos($xb, "dbms_pipe.receive_message") !== false ||
                    strpos($xb, "DBMS_PIPE.RECEIVE_MESSAGE") !== false ||
                    strpos(hexToStr($xb), "delete") !== false ||
                    strpos(hexToStr($xb), "update") !== false ||
                    strpos(hexToStr($xb), "select") !== false
                ) {
                    return false;
                }
            }
            if ($flag) {
                return true;
            } else {
                return false;
            }
        }
		
        function strToHex($string) {
            $hex = "";
            for ($i = 0; $i < strlen($string); $i++) {
                $ord = ord($string[$i]);
                $hexCode = dechex($ord);
                $hex .= substr("0" . $hexCode, -2);
            }
            return strToUpper($hex);
        }
		
        function hexToStr($hex) {
            $string = "";
            for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
                $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
            }
            return $string;
        }
            if (!verificaPOST("", $_POST)) {
            $ret = false;
        } else {
            //validaLogin
            $instUsuarioGames = new UsuarioGames();
            $ret = $instUsuarioGames->autenticarLogin($login, $senha);
                    if (!$ret) {
                $instUsuarioGames = new UsuarioGamesOperador();
                $ret = $instUsuarioGames->autenticarLogin($login, $senha);
                            if ($ret) {
                    $op = unserialize($_SESSION["dist_usuarioGamesOperador_ser"]);
                    $ugo_ug_id = $op->getUgId();
                                    if ($ugo_ug_id) {
                                        $sqlDataLoginOp = "UPDATE dist_usuarios_games SET ug_data_ultimo_acesso = NOW() WHERE ug_id = :ug_id";
                                        $stmt = $connection->prepare($sqlDataLoginOp);
                                        $stmt->bindParam(':ug_id', $ugo_ug_id, PDO::PARAM_INT);
                                        $stmt->execute();
                    }
                }
            }
        }
		
        if (!$ret) {
            $clsLogin->falhaAutenticacao();
            
			$ug_login = "SELECT ug_substatus FROM dist_usuarios_games WHERE ug_login = :ug_login";
            $stmt = $connection->prepare($ug_login);
            $loginUpper = strtoupper($login); // Converte o login para maiúsculas
            $stmt->bindParam(':ug_login', $loginUpper, PDO::PARAM_STR);
            $stmt->execute();

            $infoRow = $stmt->fetch(PDO::FETCH_ASSOC);
            
			if (
                isset($infoRow["ug_substatus"]) &&
                $infoRow["ug_substatus"] == "12"
            ) {
                $msg = "Para sua segurança, sua conta está inativa. Para reativá-la, entre em contato com o suporte E-Prepag.\n";
                gravaLog_Login("Para sua segurança, sua conta está inativa. Para reativá-la, entre em contato com o suporte E-Prepag: '$login', '$senha'.\n");
            } else {
                $msg = "Login ou senha inválidos.\n";
                gravaLog_Login("Login ou senha inválidos: '$login', '$senha'.\n");
            }
            $strRedirect =
                "http" .
                ($_SERVER["HTTPS"] == "on" ? "s" : "") .
                "://" .
                $server_url .
                "/creditos/login.php?pag=" .
                urlencode($pag) .
                "&msg=" .
                urlencode($msg) .
                "&login=" .
                urlencode($login);
            // $strRedirect = "https://" . $server_url . "/creditos/login.php?pag=" . urlencode($pag) . "&msg=" . urlencode($msg) . "&login=" . urlencode($login . "&tentativas=" . urlencode($_SESSION["tentativas_login"]));
        } else {
            gravaLog_Login("Login com sucesso: '$login', '$senha'.\n");
                    //'Pagina default de redirecionamento apos login
            $strRedirect = "https://" . $server_url . "/creditos/";
                    //'Se foi passado pagina de redirecionamento
            if ($pag) {
                //verifica se a pagina atual nao eh a pagina do redirect, senao entra em loop
                //if instr(1, Request.ServerVariables("URL"), mid(strRedirect, 1, instr(1, strRedirect, "?", 1)-1), 1) = 0 then
                if (strpos($pag, "/creditos/login.php")) {
                    $pag = $strRedirect;
                }
                             //'Se nao eh popup, redireciona a janela atual
                if (!$pop) {
                    // Se login vem da página de cadastro de campeonatos -> vai para index.php
                    if (strpos($pag, "cadastroIn2.php")) {
                        $pag = $strRedirect;
                    } else {
                        $strRedirect = $pag;
                    }
                } else {
                    //Fechando Conexão
                    pg_close($connid);
                    //'Se eh popup, redireciona a janela atual e abre o popup
                    ?><html><body OnLoad="window.location.href='<?= $strRedirect ?>';window.open('<?= $pag ?>','','scrollbars=yes,width=467,height=500');"><html><?php exit;
                }
            }
                     //inicio do bloco de redirecionamento do questionario
            $ug_id = 0;
            $ug_alterar_senha = 0;
            if (
                isset($_SESSION["dist_usuarioGames_ser"]) &&
                !is_null($_SESSION["dist_usuarioGames_ser"])
            ) {
                $usuarioGames = unserialize($_SESSION["dist_usuarioGames_ser"]);
                $ug_id = $usuarioGames->getId();
                            // variável abaixo necessária para verificação se é obrigatório a alteração de senha no próximo login
                $ug_alterar_senha = $usuarioGames->getAlteraSenha();
            }
                    $questionario = new Questionarios($ug_id, "L");
                    $aux_vetor = $questionario->CapturarProximoQuestionario();
                    if ($questionario->getRedireciona()) {
                //'Pagina questionario de redirecionamento apos login
                $strRedirect =
                    "http" .
                    ($_SERVER["HTTPS"] == "on" ? "s" : "") .
                    "://" .
                    $server_url .
                    "/creditos/questionario.php?ug_id=" .
                    $ug_id .
                    "&ql_tipo_usuario=L";
            }
            //fim do bloco de redirecionamento do questionario
                     //inicio do bloco de redirecionamento para alteração de senha
            if ($ug_alterar_senha == 1) {
                //'Pagina alteração de senha no redirecionamento apos login
                $strRedirect =
                    "http" .
                    ($_SERVER["HTTPS"] == "on" ? "s" : "") .
                    "://" .
                    $server_url .
                    "/creditos/alterar_senha.php";
            }
            //fim do bloco de redirecionamento para alteração de senha
        }
    }

	/*
		NOTA:::
		
		Esse bloco dentro do if($msg == ""){} parece ser uma maneira de validar e evitar vazamentos de dados caso ocorra um acesso de forma
		não convecional.
	*/

	
	//Fecha a Conexão
    pg_close($connid);
	
    //Redirect
    redirect($strRedirect);
?>