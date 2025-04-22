<?php
/* 
 * ajax para armazenar e-mails de newsletter
 * author: Diego Andrade
 * e-mail: diego@e-prepag.com
 */

require_once "../../includes/constantes.php";
require_once DIR_CLASS."util/Util.class.php";

if(Util::isAjaxRequest())
{
    
    include DIR_INCS . "main.php";
    include DIR_INCS . "pdv/main.php";
    //Conectando com PDO para execução da QUERY
    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();
    
    $retorno = new stdClass();
    
    if(!empty($_POST['email'])){
        
        $_POST['email'] = filter_var(trim($_POST['email']),FILTER_SANITIZE_EMAIL);
        $filter= "/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i";

        if(!preg_match($filter,$_POST['email'])){
            $retorno->msg = "E-mail inválido. Por favor verifique e tente novamente.";
            $retorno->tipo = 1;
        }
        else{
                
            $sql = "select * from usuarios_newsletter where un_email = :email";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
            $stmt->execute();
            $verificaEmail = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(empty($verificaEmail)) {
                $sql = "INSERT INTO usuarios_newsletter (
                                        un_email,
                                        un_data_cadastro
                                        ) 
                                VALUES (
                                        :email, 
                                        NOW()
                                        );";

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':email', $_POST['email'], PDO::PARAM_STR);

                if($stmt->execute()){
                    $retorno->msg = "E-mail inscrito! Obrigado.";
                    $retorno->tipo = 2;
                }else{
                    $retorno->msg = "Erro ao salvar e-mail, por favor tente novamente.";
                    $retorno->tipo = 1;
                }

            }
            else {

                $retorno->msg = "Obrigado, seu e-mail foi cadastrado.";
                $retorno->tipo = 2;
            }
        }
    }else{
        
        $retorno->msg = "O campo de \"e-mail\" precisa ser preenchido.";
        $retorno->tipo = 1;
    }
    
    $retorno->msg = htmlentities($retorno->msg);
    
    $obj = json_encode($retorno);
    
    print $obj;
}else{
    Util::redirect("/");
}