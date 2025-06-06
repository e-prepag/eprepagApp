<?php

session_start();
error_reporting(E_ALL); 
ini_set("display_errors", 1); 
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "util/Util.class.php";
require_once "../../libs/PHPGangsta/GoogleAuthenticator.php";

/*
 * Programa em AJAX validar os dados de seguranca
 * 
 * @return RETURN_SUCCESS = sucesso
 * @return RETURN_EMPTY = usuario ou senha em branco
 * @return RETURN_WRONG = usuario ou senha invalidos
 * 
 */

if (Util::isAjaxRequest()) {

    require_once DIR_CLASS . "util/Log.class.php";

    $retorno = new stdClass();
    $retorno->erro = '';
    $retorno->sucesso = false;

    if (isset($_POST['cad_senhaAtual'])) {

        require_once DIR_CLASS . "util/Login.class.php";
        require_once DIR_CLASS . "util/Validate.class.php";
        require_once DIR_INCS . "main.php";
        require_once DIR_INCS . "gamer/main.php";

        $usuario = unserialize($_SESSION['usuarioGames_ser']);
        if(!$usuario){
            echo "Acesso não permitido.";
            exit;
        }

        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();

        $token = $_POST['token'];
        $token_old = $_POST['token_old'];
        $secret = $_SESSION['secret'];

        if ($token && $secret) {

            $cad_id = $usuario->getId();

            if($cad_id == 0 || !$cad_id){
                print "Acesso não permitido.";
                exit;
            }

            $cad_senhaAtual = $_POST['cad_senhaAtual'];

            $objEncryption = new Encryption();
            $senhaAtual = $objEncryption->encrypt(trim($cad_senhaAtual));

            $ga = new PHPGangsta_GoogleAuthenticator();
            $checkResult = $ga->verifyCode($secret, $token, 2);

            if ($checkResult) {

                $sql = "SELECT ug_chave_autenticador FROM usuarios_games WHERE ug_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array($cad_id));
                $authData = $stmt->fetch(PDO::FETCH_ASSOC);

                $checkResult_old = true;
                if (!empty($authData['ug_chave_autenticador'])) {
                    $checkResult_old = $ga->verifyCode($authData['ug_chave_autenticador'], $token_old, 2);
                }

                if ($checkResult_old) {
                    $sql = "UPDATE usuarios_games SET ug_chave_autenticador = ? WHERE ug_id = ? and ug_senha = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$secret, $cad_id, $senhaAtual]);

                    // Verifica se alguma linha foi afetada
                    if ($stmt->rowCount() > 0) {
                        $retorno->sucesso = true;
                    } else {
                        $retorno->erro = "Senha ou Token inválidos! Verifique se o Token atual foi inserido corretamente.";
                    }
                }else{
                    $retorno->erro = "Senha ou Token inválidos! Verifique se o Token atual foi inserido corretamente.";
                }
            } else {
                // Token is invalid
                $retorno->erro = "Token novo inválido! Verifique se o Token foi inserido corretamente." . $secret;
            }
        }

    } else {
        $retorno->erro = "Senha não preenchida";

    }

    if ($retorno->erro != '') {
        $retorno->erro = htmlentities($retorno->erro);
    }

    print json_encode($retorno);
} else {
    print "Acesso não permitido.";
}