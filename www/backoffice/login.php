<?php 
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/../includes/constantes_url.php'; ?>
<?php
$Empty = isset($_GET['Empty']) ? $_GET['Empty'] : "";
$Invalido = isset($_GET['Invalido']) ? $_GET['Invalido'] : "";
$UserBlocked = isset($_GET['UserBlocked']) ? $_GET['UserBlocked'] : "";
$SessionExpires = isset($_GET['SessionExpires']) ? $_GET['SessionExpires'] : "";

require_once '../includes/constantes.php';
require_once $raiz_do_projeto . 'includes/configIP.php';
require_once $raiz_do_projeto . 'includes/configuracaoBO.php';
require_once "/www/includes/bourls.php";
?>
<html>

<head>
  <title>E-Prepag - BackOffice</title>
  <link rel="stylesheet" href="/css/css_frame.css" type="text/css">
  <link href="/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
  <link rel="icon" href="<?= $server_url_ep ?>/favicon.ico" type="image/x-icon">
  <link rel="shortcut icon" href="<?= $server_url_ep ?>/favicon.ico" type="image/x-icon">
  <link href="<?= $server_url_ep ?>/css/creditos.css" rel="stylesheet" type="text/css" />
  <script type="text/javascript" src="<?= $server_url_ep ?>/js/jquery.js"></script>
  <link href="<?= $server_url_ep ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
  <script src="/js/jquery-ui.min.js"></script>
</head>

<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onload="document.formLog.user.focus()">
  <div id="recebe-modal"></div>
  <table width="779" border="0" cellspacing="0" cellpadding="0" height="100%" align="center">
    <tr>
      <td height="69" colspan="2" align="center" valign="middle"><img src="/images/backoffice.png" width="777" height="72" class="top74"><br>
      </td>
    </tr>
    <tr>
      <td colspan="2" align="center" valign="top">
        <table width="78%" border="0" cellspacing="0" cellpadding="0" style="margin-top:56px">
          <tr>
            <td height="156">
              <div align="center">
                <form name="formLog" id="formLog" method="post">
                  <div class="text-info top20">Acesso permitido somente para usu&aacute;rios expressamente autorizados pela E-Prepag.</div>
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
                <?php if ($Invalido == TRUE) { ?>
                  <table width="78%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="center">
                        <font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000">
                          <b>Usu&aacute;rio ou Senha Inv&aacute;lidos</b>
                        </font>
                      </td>
                    </tr>
                  </table>
                <?php  } ?>
                <?php if ($UserBlocked == TRUE) { ?>
                  <table width="78%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="center">
                        <font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000">
                          <b>Usu&aacute;rio n&atilde;o tem autoriza&ccedil;&atilde;o
                            para acessar o Backoffice</b>
                        </font>
                      </td>
                    </tr>
                  </table>
                <?php  } ?>
                <?php if ($SessionExpires == TRUE) { ?>
                  <table width="78%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="center">
                        <font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000">
                          <b>A sessão expirou</b>
                        </font>
                      </td>
                    </tr>
                  </table>
                <?php  } ?>
                <?php if ($Empty == TRUE) { ?>
                  <table width="78%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="center">
                        <font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000">
                          <b>Preencha os campos</b>
                        </font>
                      </td>
                    </tr>
                  </table>
                <?php  } ?>
                <?php if ($_GET['erro']) { ?>
                  <table width="78%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="center">
                        <font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000">
                          <?php
                          switch ($_GET['erro']) {
                            case 1:
                              echo "<b>Cadastro de autenticador necessário</b>";
                              break;
                            case 2:
                              echo "<b>Usuário inválido</b>";
                              break;
                            case 3:
                              echo "<b>Impossível</b>";
                              break;
                            case 4:
                              echo "<b>Token inválido</b>";
                              break;
                          }
                          ?>
                        </font>
                      </td>
                    </tr>
                  </table>
                <?php  } ?>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td width="450">
        <div align="left">
          <font size="2" face="Arial, Helvetica, sans-serif" color="#FF0000"><b><?php if ($ambiente == 'desenvolvimento') echo "DESENVOLVIMENTO" ?></b></font>
        </div>
      </td>
      <td width="300">
        <div align="right" class="text-info"><b>Versão <?php echo $major_version . "." . $minor_version . "." . $release . "." . $build ?></b></div>
      </td>
    </tr>
  </table>
  <script type="text/javascript" src="/js/bootstrap.min.js"></script>
  <script type="text/javascript">
    $(function() {
      $("#formLog").submit((e) => {
        e.preventDefault();

        // Envia os dados do formulário via AJAX para ajax_login_aut.php, exibe o resultado no body e chama o modal
        $.ajax({
          url: 'ajax_login_aut.php',
          type: 'POST',
          data: $("#formLog").serialize(),
          success: function(response) {
            $("#recebe-modal").html(response);
            // Supondo que o modal tenha id #modalLoginResult
            if ($("#modal-token").length) {
              $("#modal-token").modal('show');
            }
          },
          error: function(xhr, status, error) {
            alert('Ocorreu um erro ao processar o login. Tente novamente.');
          }
        });
      })
    });
  </script>
</body>

</html>