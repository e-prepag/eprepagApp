<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>E-Prepag - Créditos para games online</title>
        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            .estiloLink { color:#515151; text-decoration: underline;}
            .font-10px { font-size:10px; }
        </style>
    </head>
    <body>
        <!-- TOPO -->
        <table align="center" border="0" width="640" cellspacing="0" cellpadding="0">
            <tr>
                <td width="100%" height="79" bgcolor="#xxxxxx" background="<?= EPREPAG_URL_HTTP ?>/prepag2/images/topo_email.jpg">
                  
                    <font face="arial" color="#FFFFFF" size="5" style="margin-left:30px;"><b>Aviso de desativação de conta</b></font>
     
                </td>
            </tr>
        </table>
        <!-- /TOPO -->

        <!-- CONTEUDO -->
        <table align="center" border="0" width="640" bgcolor="EEEEEE" cellspacing="0" cellpadding="0"  style="border-bottom: 1px solid #cccccc;">
            <tr>
                <td width="25">&nbsp;</td>
                <td width="280">
                    <br>
                    <font face="arial" color="#515151" size="2">
                    Olá NOME,<br>
                    Verificamos que possui uma conta E-Prepag, que não está sendo movimentada a algum tempo.<br><br>
					E por esse motivo sua conta está sendo selecionada para desativação, caso queira manter a conta ativa entre em contato com o suporte E-Prepag<br>
					passando as informações da sua conta.<br><br>
					
					Caso haja <b>saldo disponível</b> em sua conta, entre em contato para o resgate do valor.<br><br>
					
					As contas sem movimentação serão desativadas até dia 28/11/2022.
					
                    </font>
                    <br><br>
                </td>
                <td width="170">&nbsp;</td>
            </tr>
        </table>
        <table align="center" border="0" width="640" bgcolor="FFFFFF" cellspacing="0" cellpadding="0" style="border-bottom: 1px solid #cccccc;">
            <tr>
                <td width="15">&nbsp;</td>
                <td>
                    <font face="arial" color="#515151" size="2">
                    <br>
                    Qualquer dúvida entre em contato conosco no e-mail <a class="estiloLink" href="mailto:suporte@e-prepag.com.br">suporte@e-prepag.com.br</a><br><br>
                        <a href="<?= EPREPAG_URL_HTTPS_COM ?>/game/suporte.php">Atendimento online</a> de segunda a sexta-feira, das 9h às 17h. <br /><br />
                        Atenciosamente<br><br>
                    </font>
                    <table border="0" width="100%" cellspacing="0">
                        <tr>
                            <td>
                                <a target="_blank" href="<?= EPREPAG_URL_HTTP_COM ?>"><img src="<?= EPREPAG_URL_HTTP ?>/prepag2/images/logo_epp_email.png" align="left" border="0" /></a><br><br>
                                <font face="arial" color="#515151" size="2"><a class="estiloLink" target="_blank" href="<?= EPREPAG_URL_HTTP ?>"><?= EPREPAG_URL ?></a></font>
                            </td>
                            <td>
                                <a target="_blank" href="http://www.youtube.com/user/EPrepagVideos"><img src="<?= EPREPAG_URL_HTTP ?>/prepag2/images/youtube.gif" align="right" style="margin-left:15px;" border="0" /></a>
                                <a target="_blank" href="https://www.facebook.com/eprepagcash"><img src="<?= EPREPAG_URL_HTTP ?>/prepag2/images/facebook.gif" align="right" style="margin-left:15px;" border="0" /></a>
                                <a target="_blank" href="http://www.twitter.com/eprepag"><img src="<?= EPREPAG_URL_HTTP ?>/prepag2/images/twitter.gif" align="right" border="0" style="*margin-right:25px;" /></a>
                            </td>
                        </tr>
                    </table>
                    <br>
                </td>
            </tr>
        </table>
        <!-- /CONTEUDO -->
        <!-- RODAPE -->
        <table align="center" border="0" width="640" bgcolor="FFFFFF" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center" style="font: normal 12px arial, sans-serif; color: #515151;"><br>E-Prepag Copyright 2021 - Todos os direitos reservados</td>
            </tr>
        </table>
        <!-- /RODAPE -->
    </body>
</html>