<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php

session_start();

require_once "../../../includes/constantes.php";
require_once DIR_CLASS."util/Util.class.php";

/*
 * Programa em AJAX validar os dados de seguran�a
 * 
 * @return RETURN_SUCCESS = sucesso
 * @return RETURN_EMPTY = usu�rio ou senha em branco
 * @return RETURN_WRONG = usu�rio ou senha inv�lidos
 * 
 */

if(Util::isAjaxRequest()){

    require_once DIR_CLASS."util/Log.class.php";
    
    $retorno = new stdClass();
    $retorno->erro = '';
    $retorno->sucesso = false;
    
    if(isset($_POST['type']) && isset($_POST['senha'])){
        
        require_once DIR_CLASS."util/Login.class.php";
        require_once DIR_CLASS."util/Validate.class.php";
        require_once DIR_INCS."main.php";
        require_once DIR_INCS."gamer/main.php";
        
        $usuario = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
		
        $validate = new Validate;
        
        if($_POST['type'] == "pass"){

            if(isset($_POST['novaSenha']) && isset($_POST['confirmaSenha']) ){

                $erros = array();
                $clsLogin = new Login($_POST['novaSenha']);

                if($clsLogin->valida() > 0 || $_POST['novaSenha'] != $_POST['confirmaSenha']){
                    $retorno->erro = utf8_encode("Senha n�o atinge os n�veis de seguran�a desejados.");
                }
                
                $campo = "ug_senha";
                $valor = $_POST['novaSenha'];
                
            }else{
                $retorno->erro = utf8_encode("Par�metros inv�lidos.");
            }
        }else if($_POST['type'] == "novoEmail"){
            
            $objEncryption = new Encryption();
            $strId = $objEncryption->encrypt("id");
            $strEmail = $objEncryption->encrypt("email");
            $id = $objEncryption->decrypt($_SESSION[$strId]);
            $email = $objEncryption->decrypt($_SESSION[$strEmail]);
                
            if($validate->numeros($id) > 0 || $validate->email($email) > 0){
                $retorno->erro = utf8_encode("Usu�rio ou e-mail inv�lidos.");
            }
            
            if(UsuarioGames::existeEmail($email, $id)){
                $retorno->erro = utf8_encode("E-mail j� cadastrado em nosso banco de dados.");
                
            }else{
            
                $usuario = UsuarioGames::getUsuarioGamesById($id);    
            }
            
            
            $campo = "ug_email";
            $valor = trim(strtoupper($email));
            
        }else if($_POST['type'] == "solicitaNovoEmail"){
            
            require_once DIR_INCS."configIP.php";
            
            if($validate->email($_POST['email']) > 0 || $_POST['email'] != $_POST['confirmaEmail'])
            {
                $retorno->erro = "E-mail inv�lido";
                
            }else if((new UsuarioGames)->existeEmail($_POST['email'], $usuario->getId())){
                $retorno->erro = utf8_encode("E-mail j� cadastrado em nosso banco de dados.");
                
            }else{
                
                $strEncrypt = "email=".$_POST['email']."&id=".$usuario->getId();
                $objEncryption = new Encryption();
                $strEncrypt = urlencode($objEncryption->encrypt($strEncrypt));
                $server_url = 'https://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');

                $strMail = "Para efetivar a altera��o, por favor, fa�a o login de sua conta em nosso site e acesse este link a seguir: <a href='$server_url/game/conta/altera-email.php?c=".$strEncrypt."'>$server_url/game/conta/altera-email.php?c=".$strEncrypt."</a>";

                //enviar str por e-mail
                $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'AlteracaoEmail');
                $objEnvioEmailAutomatico->setInstrucoesUso($strMail);
                $objEnvioEmailAutomatico->setUgNome($usuario->getNome());
                $objEnvioEmailAutomatico->setUgID($usuario->getId());
                $objEnvioEmailAutomatico->setUgEmail(htmlspecialchars($_POST['email'], ENT_NOQUOTES));
                $objEnvioEmailAutomatico->MontaEmailEspecifico();

                $retorno->sucesso = true;
            }
            
            
            
        }else if($_POST['type'] == "novoLogin"){
            
            if($_POST['login'] != $_POST['confirmaLogin']) {
                $retorno->erro = utf8_encode("A confirma��o de login est� incorreta.(32C)");
            }
            if($validate->qtdCaracteres($_POST['login'],5,100)){
                $retorno->erro = utf8_encode("O Login deve ter mais de 5 caracteres.");
            }
            if(!$validate->caracteresEspeciais($_POST['login'])){
                $retorno->erro = utf8_encode("O login n�o deve ter caracteres especiais (|,!,?,*,$,%, etc).");
            }            
            $campo = "ug_login";
            $valor = utf8_decode($_POST['login']);
            
        }else if($_POST['type'] == "esqueciMinhaSenha"){
            
            if($validate->email($_POST['email']) > 0)
            {
                $retorno->erro = utf8_encode("E-mail inv�lido");
                
            }else{
                
                if(!$usuario){
                    $idUsuario = (new UsuarioGames)->getIdUsuarioGamerByEmail($_POST['email']);
                }else{
                    $idUsuario = $usuario->getId();
                }
                
                if($idUsuario){
                    $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER,'EsqueciSenhaGamer');
                    // file deepcode ignore XSS: <N�o tem XSS pois pega o id direto no banco>
                    $envioEmail->setUgID($idUsuario);

                    $envioEmail->MontaEmailEspecifico();
                    $retorno->sucesso = true;
                }else{
                    $retorno->erro = utf8_encode("Usu�rio n�o cadastrado.");
                }

            }
        }else{
            $retorno->erro = utf8_encode("Par�metros inv�lidos.");
        }
        
        if(empty($retorno->erro)){
            if(isset($campo) && isset($valor)){                
                $alteraDado = $usuario->alteraDadoAcesso($campo, $valor, $_POST['senha']);
                if($alteraDado === true){
                    $retorno->sucesso = true;                    
                }else{
                    $retorno->erro = $alteraDado;
                }
            }
        }
        
    }else{
        $retorno->erro = utf8_encode("Par�metros inv�lidos.");
        
    }
    
    if($retorno->erro != ''){
        $retorno->erro = htmlentities($retorno->erro);
    }
    
    print json_encode($retorno);
}else{
    print "Acesso n�o permitido.";
}