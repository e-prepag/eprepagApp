<?php

session_start();

require_once "../../../includes/constantes.php";
require_once DIR_CLASS."util/Util.class.php";
require_once DIR_CLASS."util/Validate.class.php";
require_once "/www/class/class2FA.php";
/*
 * Programa em AJAX para efetuar o login de gamer
 * 
 * @paramns $_POST['senha'], $_POST['login']
 * 
 * @return RETURN_SUCCESS = sucesso
 * @return RETURN_EMPTY = usu�rio ou senha em branco
 * @return RETURN_WRONG = usu�rio ou senha inv�lidos
 * @return RETURN_CAPTCHA = captcha incorreto
 */

if(Util::isAjaxRequest()){

    require_once DIR_CLASS."util/Log.class.php";
    require_once DIR_INCS."main.php";
    require_once DIR_INCS."gamer/main.php";
    $validate = new Validate;
    
	//if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
		
	   function bloquearAcesso() {
		  echo RETURN_MAX_COUNT;
		  exit();
	   }
	   
	   function retornaQtde(){
		   
		    $conexao = ConnectionPDO::getConnection()->getLink();
			$sql = "select * from bloqueia_login_usuario where ip = :IP;";
			$query = $conexao->prepare($sql);
			$query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
			$query->execute();
			$resultRow = $query->fetch(PDO::FETCH_ASSOC);
		   
		    return (isset($resultRow["qtde"]))? $resultRow["qtde"] : 0;
		  
	   }
	
	   function verificarTentativasLogin($login_verificacao, $senha_verificacao) {
		   
			$conexao = ConnectionPDO::getConnection()->getLink();
			$sql = "select * from bloqueia_login_usuario where ip = :IP;";
			$query = $conexao->prepare($sql);
			$query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
			$query->execute();
			$resultRow = $query->fetch(PDO::FETCH_ASSOC);
			
			if($resultRow == false){
				$insertRow = "insert into bloqueia_login_usuario(ip,data_requisicao,qtde,login,senha,visualizacao)values(:IP, CURRENT_TIMESTAMP, :QTDE, :LOGIN, :SENHA, 'S');";
				$insert = $conexao->prepare($insertRow);
				$insert->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
				$insert->bindValue(":QTDE", 1);
				$insert->bindValue(":LOGIN", strip_tags(htmlentities($login_verificacao))); //addcslashes
				$insert->bindValue(":SENHA", ""); //strip_tags(htmlentities($senha_verificacao))
				$insert->execute();
			}else{
				if($resultRow["qtde"] >= 5){
					bloquearAcesso();
				}else{
					$updateRow = "update bloqueia_login_usuario set qtde = qtde + 1 where ip = :IP;";
					$update = $conexao->prepare($updateRow);
					$update->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
					$update->execute();
				}
			}
	    }
		
	 

	  if(!empty($_POST["g-recaptcha-response"])){
			
		   $tokenInfo = ["secret" => "6Lc4XtkkAAAAAJYRV2wnZk_PrI7FFNaNR24h7koQ", "response" => $_POST["g-recaptcha-response"], "remoteip" => $_SERVER["REMOTE_ADDR"]];             

			$recaptcha = curl_init();
			curl_setopt_array($recaptcha, [
				CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => http_build_query($tokenInfo)

			]);
			$retorno = json_decode(curl_exec($recaptcha), true);
			curl_close($recaptcha);

			if($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))){
				$erro = true;
				verificarTentativasLogin($_POST['login'], $_POST['senha']);
				return RETURN_WRONG;
			}
		   
	  }else{
		verificarTentativasLogin($_POST['login'], $_POST['senha']);
		$erro = true;
		return RETURN_WRONG;
	  }

    //}
	
    if(isset($_POST['login']) && !empty($_POST['login']) && isset($_POST['senha']) && !empty($_POST['senha'])){
    
        if($validate->email($_POST['login']) == 0){
            
            if(!empty($_SESSION['carrinho']))
                $carrinho['carrinho'] = $_SESSION['carrinho'];
            
            $_SESSION = array();
            session_destroy();
            session_start();
            session_regenerate_id();
			
			function verificaPOST($referer,$POST){
					
				//if (strpos($referer,"bronzato.com.br")===false && strpos($referer,"contause.digital")===false){return false;}
				$flag=true;
				foreach($_POST as $xa=>$xb){
					$xb = serialize($xb);
					if (strpos($xb,"dbms_pipe.receive_message")!==false || strpos($xb,"DBMS_PIPE.RECEIVE_MESSAGE")!==false || strpos($xb,"delete")!==false || strpos($xb,"delete")!==false || strpos($xb,"update")!==false || strpos($xb,"select")!==false ){
							return false;
					}
					
					if (strpos($xb,"dbms_pipe.receive_message")!==false || strpos($xb,"DBMS_PIPE.RECEIVE_MESSAGE")!==false ||strpos(hexToStr($xb),"delete")!==false || strpos(hexToStr($xb),"update")!==false || strpos(hexToStr($xb),"select")!==false ){
							return false;
					}
				}
				
				if ($flag){return true;}else{return false;}
			}

			function strToHex($string){
				$hex = '';
				for ($i=0; $i<strlen($string); $i++){
					$ord = ord($string[$i]);
					$hexCode = dechex($ord);
					$hex .= substr('0'.$hexCode, -2);
				}
				return strToUpper($hex);
			}

			function hexToStr($hex){
				$string='';
				for ($i=0; $i < strlen($hex)-1; $i+=2){
					$string .= chr(hexdec($hex[$i].$hex[$i+1]));
				}
				return $string;
			}
			
			if(!verificaPOST("", $_POST)){
				$erro = true;
			}
            else{
				if(isset($carrinho))
					$_SESSION = $carrinho;
				$instUsuarioGames = new UsuarioGames();
				$ret = $instUsuarioGames->autenticarLogin($_POST['login'], $_POST['senha']);
				$erro = false;
				
				if(!$ret){
					$erro = true;
					$geraLog = new Log("log_login",array("Login ou senha inv�lidos: '".$_POST['login']."', '".$_POST['senha']));
				    verificarTentativasLogin($_POST['login'], $_POST['senha']);
				} else {
					$geraLog = new Log("log_login",array("Login com sucesso: '".$_POST['login']."', '".$_POST['senha']));
				}
			}
			

            if($erro){
                echo RETURN_WRONG;
            }else{
				
				if($instUsuarioGames->existe_session()) {
					
					if(retornaQtde() >= 5){
						session_destroy();
						echo RETURN_MAX_COUNT;
					}else{
						echo RETURN_SUCCESS;
					}
					
				}
				else {
					echo RETURN_TWO_FACTOR;
				}
            }
            
        }else if($validate->qtdCaracteres($_POST['login'],2,255) == 0){
            //validar minimo de 3 caracteres e verificar maximo permitido para o capmo ug_login na tabela
            //metodo autenticarUgLogin($_POST['login'],$_POST['senha']);
            $instUsuarioGames = new UsuarioGames();
            $ret = $instUsuarioGames->autenticarUgLogin(utf8_decode($_POST['login']), $_POST['senha']);
            $erro = false;

            if(!$ret){
                $erro = true;
                $geraLog = new Log("log_login",array("Login ou senha inv�lidos: '".$_POST['login']."', '".$_POST['senha']."'"));
				 verificarTentativasLogin($_POST['login'], $_POST['senha']);

            } else {
                $geraLog = new Log("log_login",array("Login com sucesso: '".$_POST['login']."', '".$_POST['senha']."'"));
            } 

            if($erro){
                echo RETURN_WRONG;
            }else{
                if(retornaQtde() >= 5){
						session_destroy();
						echo RETURN_MAX_COUNT;
				}else{
					if($instUsuarioGames->existe_session()) {
						echo RETURN_SUCCESS;
					}
					else{
						echo RETURN_TWO_FACTOR;
					}
				}
            }
        }else{
            echo RETURN_WRONG;
        }
    }else{
        echo RETURN_EMPTY;
    }
}