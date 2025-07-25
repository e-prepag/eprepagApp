<?php
@session_start();
date_default_timezone_set('America/Fortaleza');
require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto . "includes/inc_register_globals.php";	
require_once $raiz_do_projeto . "public_html/sys/includes/functions.php";
require_once $raiz_do_projeto . "class/util/Log.class.php";


$varBlDebug = true;

$user = strtoupper(filter_input(INPUT_POST, 'user'));
$passw = filter_input(INPUT_POST, 'passw');

if(!$user || !$passw) header("Location: index.php?Empty=1");

require_once $raiz_do_projeto."includes/gamer/chave.php";
require_once $raiz_do_projeto."includes/gamer/AES.class.php";
//Instanciando Objetos para Descriptografia
$chave256bits = new Chave();
$aes = new AES($chave256bits->retornaChavePub());
$passw = base64_encode($aes->encrypt(addslashes($passw)));

if($Enviar) {

        gravaLog_LoginSys("Login: '".$user."', '".$passw."'", true);

        $_SESSION["iduser_bko_pub"] = "";
        $_SESSION["tipo_acesso_pub"] = "";	
        $_SESSION["opr_codigo_pub"] = "";	
        $_SESSION["nome_bko"] = "";
        $_SESSION["userlogin_bko"] = "";
        $_SESSION["opr_nome"] = "";	
        $_SESSION["datalog_bko"] = "";
        $_SESSION["horalog_bko"] = "";

        require_once $raiz_do_projeto . "db/connect.php";
        require_once $raiz_do_projeto . "db/ConnectionPDO.php";

        $con = ConnectionPDO::getConnection();

        if ( !$con->isConnected() ) {
            // retornar os erros: $con->getErrors();
            die('Erro#2');
        }
		
        $pdo = $con->getLink();
        $sql = "SELECT * FROM usuarios WHERE shn_login = ? AND shn_password = ? AND ((tipo_acesso='AD') OR (tipo_acesso='DT') OR (tipo_acesso='SV') OR (tipo_acesso='AT') OR (tipo_acesso='PU') OR (tipo_acesso='US'))";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($user, $passw));

        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($fetch) == 1) {
                $pgrow = $fetch[0];
                if($pgrow['bko_autoriza'] == 'S') {
                             $iduser_var	 = $pgrow['id'];
                             $nome_var	 = $pgrow['shn_nome'];
                             $login_var	 = $pgrow['shn_login'];
                             $opr_codigo_var	 = $pgrow['opr_codigo'];
                             $opr_nome_var = '';
//                             if($opr_codigo_var>0) {
//                                     $sql_opr = "select opr_nome from operadoras where opr_codigo=".$opr_codigo_var;	
//                                     $result_opr = pg_exec($connid, $sql_opr);
//                                     if($pgrow_opr = pg_fetch_array($result_opr)) {
//                                             $opr_nome_var = $pgrow_opr['opr_nome'];
//                                }
                             if($opr_codigo_var>0) {
                                $sql = "select opr_nome from operadoras where opr_codigo= ?";
                                
                                try{
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute(array($opr_codigo_var));
                                    $operadoras = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    $opr_nome_var = $operadoras[0]["opr_nome"];
                                }catch(PDOException $e){     
                                    $geraLog = new Log("LOGINSYSADMIN",array("ERROR: ".$ex->getMessage(),
                                                                             "FILE: ".$ex->getFile(),
                                                                             "LINE ".$ex->getLine()));
                                }
                                
                             }

                             $tipo_acesso_var = $pgrow['tipo_acesso'];
                             $datalog_var = $pgrow['bko_datalogin'];
                             $horalog_var = $pgrow['bko_horalogin'];
							 $opr_banco = $pgrow['opr_codigo'];

                             if (!empty($iduser_var)) {
                                 
                                if(isset($_SESSION['iduser_bko']) && $_SESSION['iduser_bko'] != $iduser_var)
                                {
                                    $_SESSION = array();
                                    session_destroy();
                                    session_start();
                                }

                                     $_SESSION["iduser_bko_pub"] = $iduser_var;
                                     $_SESSION["tipo_acesso_pub"] = $tipo_acesso_var;	
                                     $_SESSION["opr_codigo_pub"] = $opr_codigo_var;	
                                     $_SESSION["nome_bko"] = $nome_var;
                                     $_SESSION["userlogin_bko"] = $login_var;
                                     $_SESSION["opr_nome"] = $opr_nome_var;	
                                     $_SESSION["datalog_bko"] = $datalog_var;
                                     $_SESSION["horalog_bko"] = $horalog_var;
									 $_SESSION["opr_vinculo"] = $opr_banco;

                                     gravaLog_LoginSys("Login Success: $login_var", true);

                             } else {
                                     gravaLog_LoginSys("Login Error (1): $login_var", true);

                                     header("Location: index.php");
                                     exit;
                             }
                             
                            $acesso_atual = $pgrow['shn_qtde_acesso'] + 1;
                             
                            /*
                             $sql = "update usuarios set bko_datalogin='".date('Y-m-d')."', bko_horalogin='".date('H:i:s')."', shn_qtde_acesso=".$acesso_atual." where id='".$pgrow['id']."'";
                             pg_exec($connid,$sql);

                             $sql = "insert into bko_access_log (log_data, log_hora, log_ip, log_user_id) values ('".date('Y-m-d')."', '".date('H:i:s')."', '".retorna_ip_acesso_sys_admin()."', '".$pgrow['id']."') ";
                             
                             echo $sql;
                             pg_exec($connid,$sql);
                             */
                            
                            try {
                                $sql = "update usuarios set bko_datalogin= :bko_datalogin, bko_horalogin= :bko_horalogin, shn_qtde_acesso= :shn_qtde_acesso where id= :id";
                                $stmt2 = $pdo->prepare($sql);
                                $stmt2->execute(array(':bko_datalogin' => date('Y-m-d'), 
                                                      ':bko_horalogin'=> date('H:i:s'), 
                                                      ':shn_qtde_acesso' => $acesso_atual, 
                                                      ':id' => $pgrow['id']));
                            } catch(PDOException $e) {  
                                $geraLog = new Log("LOGINSYSADMIN",array("ERROR: ".$e->getMessage(),
                                                                          "FILE: ".$e->getFile(),
                                                                          "LINE ".$e->getLine()));
                            }

                            try{
                                $sql = "insert into bko_access_log (log_data, log_hora, log_ip, log_user_id) values ('".date('Y-m-d')."', '".date('H:i:s')."', '".retorna_ip_acesso_sys_admin()."', '".$pgrow['id']."') ";
                                $stmt22 = $pdo->prepare($sql);
                                $stmt22->execute();
                            }catch(PDOException $e){   
                                $geraLog = new Log("LOGINSYSADMIN",array("ERROR: ".$e->getMessage(),
                                                                          "FILE: ".$e->getFile(),
                                                                          "LINE ".$e->getLine()));
                            }

                             header("Location: frameset.php");
                             exit;
                } else { 
                        gravaLog_LoginSys("Login Error (2 Blocked): $login_var", true);
                        header("Location: index.php?UserBlocked=1");
                        exit;
                     }
        } else { 
                gravaLog_LoginSys("Login Error (2 Invalido): $login_var", true);
                header("Location: index.php?Invalido=$passw");
                exit;
        }
        pg_close($connid); 
} else {
        header("Location: index.php");
        exit;
}
?>
<html>
<head>
<title>E-Prepag - BackOffice</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
</head>
<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">


	  
<table width="779" border="0" cellspacing="0" cellpadding="0" height="100%" align="center">
  <tr> 
    <td valign="middle" align="center" height="69"> <br>
      <img src="/sys/imagens/backoffice.jpg" width="777" height="72"><br> </td>
  </tr>
  <tr> 
    <td valign="top" align="center"> <table width="80%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td background="/sys/imagens/interna_r4_c3.gif" height="24">&nbsp; </td>
        </tr>
      </table>
      <br> <table width="78%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td height="156"> <div align="center"><font size="2" face="Arial, Helvetica, sans-serif"><strong><font color="#0000FF">checando 
              Login...</font><font color="#990000"><br>
              <br>
              acessando &aacute;rea restrita...<br>
              </font></strong></font></div></td>
        </tr>
      </table>
	  </td>
  </tr>
  <tr> 
    <td valign="middle" align="center" height="100">&nbsp; </td>
  </tr>
</table>
</body>
</html>

<?php
function gravaLog_LoginSys($mensagem, $forced_save = false){

        // Desativa o registro de Sucesso/Erro de logins
        global $raiz_do_projeto;
        if(!$forced_save) return;

        //Arquivo
        $file = $raiz_do_projeto . "log/log_login_sys.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . " (".$_SERVER['REMOTE_ADDR'].")\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}
?>
