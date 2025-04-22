<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
?>
<link href="/css/styles.css" rel="stylesheet" type="text/css" />
<link href="/css/css.css" rel="stylesheet" type="text/css"/>
<body>
<div align="right" onClick="fecha_cielo();" class="link_azul" style="cursor:pointer;cursor:hand;"><font size="1">Fechar [X]</font></div>
<p style="font-family: Arial, Helvetica, sans-serif; color:gray; font-size:11px; text-align:justify;">
<br>
A E-Prepag está aumentando os clientes atendidos por Cartão de Crédito e você, cliente especial E-Prepag, agora poderá fazer suas compras desta forma. Você pode efetuar 2 compras no valor de até R$ <?php echo htmlspecialchars($_GET['valor']); ?>,00 cada, por semana (em um intervalo de 7 dias).<br><br>
Obs.: este meio de pagamento poderá não estar disponível para alguns jogos.<br><br>
Qualquer dúvida, é só entrar em contato conosco no <nobr><a href="mailto:suporte@e-prepag.com.br">suporte@e-prepag.com.br</a></nobr><br><br>
</p>
<center>
<img src="/imagens/gamer/voltar.gif" width="88" height="31" border="0" alt="Voltar" OnClick="fecha_cielo();" style="cursor:pointer;cursor:hand;"/>
</center>
</body>
</html>
