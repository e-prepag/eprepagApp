<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
session_start();
require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/configuracao.php";
require_once $raiz_do_projeto . "public_html/sys/includes/languages.php";
$username = "Gestor";
$password = "games2007";

if (!($_SERVER['SERVER_NAME'] == "192.168.200.91" || $_SERVER['SERVER_NAME'] == "192.168.200.65" || $_SERVER['SERVER_NAME'] == "192.168.200.55" || $_SERVER['SERVER_NAME'] == "192.168.200.61" || $_SERVER['SERVER_NAME'] == "192.168.200.51" || $_SERVER['SERVER_NAME'] == "192.168.200.75" || $_SERVER['SERVER_NAME'] == "200.201.132.134" || $_SERVER['SERVER_NAME'] == "189.126.102.34" || $_SERVER['SERVER_NAME'] == "" . EPREPAG_URL . "" || $_SERVER['SERVER_NAME'] == "www.eprepag.com.br" || $_SERVER['SERVER_NAME'] == "www2.e-prepag.com.br" || $_SERVER['SERVER_NAME'] == "sandbox.e-prepag.com.br" || $_SERVER['SERVER_NAME'] == "www2.eprepag.com.br" || $_SERVER['SERVER_NAME'] == "xxxdnn1081.locaweb.com.br" || $_SERVER['SERVER_NAME'] == "e-prepag.com.br" || $_SERVER['SERVER_NAME'] == "eprepag.com.br" || $_SERVER['SERVER_NAME'] == "eprepag.ddns.net") )
{
        if ($PHP_AUTH_USER != $username || $PHP_AUTH_PW != $password)
        { 
                header("WWW-Authenticate: basic realm=Backoffice");
                header("HTTP/1.0 401 Unauthorized");
                echo "<META HTTP-EQUIV='Refresh' Content=0;URL='/mensagens/access_denied.php'>";
                exit;
        }
}

require_once $raiz_do_projeto . "public_html/sys/includes/functions.php";

if($_SERVER['HTTPS']!="on" && !checkIP()) {
    Header("Location: " . EPREPAG_URL_HTTPS . "".$_SERVER['REQUEST_URI']);
    die();
} //end if($_SERVER['HTTPS']!="on") 

if(strpos(strtolower($GLOBALS['_SERVER']['SERVER_NAME']), "www.") === false && !checkIP()){
    header("Location: " . EPREPAG_URL_HTTPS . "".$_SERVER['REQUEST_URI']);
    die();
}
elseif(strpos(strtolower($GLOBALS['_SERVER']['SERVER_NAME']), ".br") === false && !checkIP()){
    header("Location: " . EPREPAG_URL_HTTPS . "".$_SERVER['REQUEST_URI']);
    die();
}

$erro = "";
if(isset($_GET["Invalido"]) && $_GET["Invalido"] == TRUE) {
    $erro .= "<br><strong>".LANG_USER_PASS_INVALID."</strong> (A1b)";
}
if(isset($_GET["UserBlocked"]) && $_GET["UserBlocked"] == TRUE) {
    $erro .= "<br><strong>".LANG_ACCESS_DENIED_BACKOFFICE."</strong>";
}
if(isset($_GET["SessionExpires"]) && $_GET["SessionExpires"] == TRUE) {
    $erro .= "<br><strong>".LANG_SESSION_EXPIRED."</strong>";
}
if(isset($_GET["Empty"]) && $_GET["Empty"] == TRUE) {
    $erro .= "<br><strong>".LANG_WRITE_FIELDS."</strong>";
} 
?>

<!DOCTYPE html>
<html>
        <title><?php echo LANG_EPP_REPORT;?></title>
        <meta charset="ISO-8859-1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- css --> 
       <link href="/includes/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
       <link rel="stylesheet" href="/sys/css/css_frame.css" type="text/css">
        <!-- js -->
        <script type="text/javascript" src="/js/jquery.js"></script>
        <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
        <script>
        $(function(){
           $("#formLog").submit(function(){
               var erro = new Array();
               if($("#user").val() == "")
                   erro.push("<?php echo LANG_WRITE_FIELDS;?>.");
               if($("#passw").val().length < 4)
                   erro.push("<?php echo sprintf(LANG_PASSWORD_MUST_HAVE, 4);?>.");
               
               if(erro.length > 0){
                   alert(erro.join("\n"));
                   return false;
               }else{
                   return true;
               }
               
           }); 
        });
        
	function changeLang(langName) {
            document.form_lang.nome.value = langName;
            document.form_lang.submit();
	}

        </script>
    <body onload="document.formLog.user.focus()">
        <div class="container pt-30 pb-30 borda-container">
            <div class="row">
                <div class="col-md-8 text-center pt-10 pb-10">
                    <a href="/sys/admin/frameset.php" class="">
                        <img src="/sys/imagens/epp_logo.gif" alt="Painel de Administração da E-Prepag" name="LogoRPP" border="0" id="LogoRPP">
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h2><p class="text-primary "> <?php echo LANG_EPP_REPORT;?> </p></h2>
                            <hr>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <p class="text-center text-primary"><?php echo LANG_ACCESS_RESTRICT;?>.</p>
                        </div>
                    </div>
                    <div class="row top20">
                        <form class="form-horizontal" action="index2.php" method="post" name="formLog" id="formLog">
                            <div class="form-group">
                                <label for="user" class="col-sm-2 col-sm-offset-1 text-primary control-label"><?php echo LANG_STATISTICS_USER;?></label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" name="user" maxlength="50" id="user" placeholder="<?php echo LANG_STATISTICS_USER;?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputPassword3" class="col-sm-2 col-md-offset-1 text-primary control-label"><?php echo LANG_PASSW;?></label>
                                <div class="col-sm-7">
                                  <input type="password" name="passw" id="passw" class="form-control" size="10" maxlength="15" placeholder="<?php echo LANG_PASSW;?>">
                                </div>
                            </div>
                            <div class="form-group top30">
                                <div class="col-sm-offset-4 col-sm-4 col-md-offset-4 col-md-4 text-primary">
                                  <button name="Enviar" type="submit" id="Enviar" value="Ok" class="btn btn-block btn-primary"><?php echo LANG_SEND;?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-12 text-center top20">
                            <h2>
                                <img src="/sys/imagens/flg/flag_brasil.gif" width="29" border="0" title="Português - Brasil"  style="cursor:pointer;" onClick="changeLang('pt');">&nbsp;
                                <img src="/sys/imagens/flg/flag_uk.gif" width="29" border="0" title="English"  style="cursor:pointer;" onClick="changeLang('en');">&nbsp;
                                <img src="/sys/imagens/flg/flag_corea.gif" width="29" border="0" title="Korean" style="cursor:pointer;" onClick="changeLang('ko');">
                            </h2>
                            <form method="post" name="form_lang">
                                <input type="hidden" name="nome" value="<?php if(isset($_SESSION['langNome'])) echo $_SESSION['langNome']; ?>" />
                            </form>
                        </div>
                    </div>
                    <div class="row top20">
                        <a href="mailto: suporte@e-prepag.com.br" class="">
                            <div class="col-md-12 text-center top20">
                                <img src="/sys/imagens/interrogation.gif">
                            </div>
                            <div class="col-md-12 text-center text-primary box-duvidas">
                                <?php echo LANG_DOUBTS_CONTACT;?>.
                            </div>
                        </a> 
                    </div>
                    <?php if($ambiente == 'desenvolvimento'){ ?>
                        <div class="row top30 text-center">
                            <div class="text-primary">
                                <b>DESENVOLVIMENTO</b>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="row top30 bottom20 text-center">
                        <div class="text-primary">
                            <b><?= LANG_VERSION." ".$strVersao; ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
