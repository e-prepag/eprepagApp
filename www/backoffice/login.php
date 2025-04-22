<?php
if(!isset($Empty)) $Empty = "";
if(!isset($Invalido)) $Invalido = "";
if(!isset($UserBlocked)) $UserBlocked = "";
if(!isset($SessionExpires)) $SessionExpires = "";

require_once '../includes/constantes.php';
require_once $raiz_do_projeto.'includes/configIP.php';
require_once $raiz_do_projeto.'includes/configuracaoBO.php';
require_once "/www/includes/bourls.php";

?>
<html>
<head>
<title>E-Prepag - BackOffice</title>
<link rel="stylesheet" href="/css/css_frame.css" type="text/css">
<link href="/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<link rel="icon" href="https://www.e-prepag.com.br/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="https://www.e-prepag.com.br/favicon.ico" type="image/x-icon">
<link href="https://<?php echo $server_url; ?>/css/creditos.css" rel="stylesheet" type="text/css" />
</head>
<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onload="document.formLog.user.focus()">
<table width="779" border="0" cellspacing="0" cellpadding="0" height="100%" align="center">
  <tr> 
      <td height="69" colspan="2" align="center" valign="middle"><img src="/images/backoffice.png" width="777" height="72" class="top74"><br> 
    </td>
  </tr>
  <tr> 
    <td colspan="2" align="center" valign="top">
        <table width="78%" border="0" cellspacing="0" cellpadding="0" style="margin-top:56px">
        <tr> 
          <td height="156"> <div align="center"> 
              <form action="https://<?php echo $server_url_bo; ?>:<?php echo $server_port; ?>/index2.php" method="post" name="formLog" id="formLog">
                <div class="text-info top20">Acesso permitido somente para usu&aacute;rios expressamente autorizados pela E-Prepag. <?php echo $server_url_bo; ?>:<?php echo $server_port; ?></div>
                <table width="40%" border="0" style="margin-top:120px">
                  <tr> 
                    <td width="29%" class="text-right text-info">Usu&aacute;rio:</td>
                    <td width="50%">
                      <input name="user" type="text" class="form-control text-info" value="" size="15" maxlength="25">
                    </td>
                    <td width="20%">
                        &nbsp;
                    </td>
                  </tr>
                  <tr> 
                    <td width="29%" height="60" class="text-right text-info">Senha:</td>
                    <td width="50%">
                      <input name="passw" type="password" class="form-control text-info" value="" size="15" maxlength="15">
                    </td>
                    <td width="20%" class="text-right">
                      <input name="Enviar" type="submit" id="Enviar" class="btn btn-info" value="OK ">
                    </td>
                  </tr>
                </table>
              </form>
              <?php  if($Invalido == TRUE) { ?>
              <table width="78%" border="0" cellspacing="0" cellpadding="0">
                <tr> 
                  <td align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000"> 
                    <b>Usu&aacute;rio ou Senha Inv&aacute;lidos</b></font></td>
                </tr>
              </table>
              <?php  } ?>
              <?php  if($UserBlocked == TRUE) { ?>
              <table width="78%" border="0" cellspacing="0" cellpadding="0">
                <tr> 
                  <td align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000"> 
                    <b>Usu&aacute;rio n&atilde;o tem autoriza&ccedil;&atilde;o 
                    para acessar o Backoffice</b></font> </td>
                </tr>
              </table>
              <?php  } ?>
              <?php  if($SessionExpires == TRUE) { ?>
              <table width="78%" border="0" cellspacing="0" cellpadding="0">
                <tr> 
                  <td align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000"> 
                    <b>A sessão expirou</b></font> </td>
                </tr>
              </table>
              <?php  } ?>
              <?php  if($Empty == TRUE) { ?>
              <table width="78%" border="0" cellspacing="0" cellpadding="0">
                <tr> 
                  <td align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000"> 
                    <b>Preencha os campos</b></font></td>
                </tr>
              </table>
              <?php  } ?>
            </div></td>
        </tr>
      </table></td>
  </tr>
  <tr> 
    <td width="450"><div align="left"><font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000"><b><?php  if($ambiente == 'desenvolvimento') echo "DESENVOLVIMENTO" ?></b></font></div></td>
    <td width="300"><div align="right" class="text-info"><b>Versão <?php  echo $major_version .".". $minor_version .".". $release .".". $build ?></b></div></td>
  </tr>
</table>
</body>
</html>
