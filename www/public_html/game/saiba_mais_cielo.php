<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
?>
<link href="/css/styles.css" rel="stylesheet" type="text/css" />
<link href="/css/css.css" rel="stylesheet" type="text/css"/>
<body>
<div align="right" onClick="fecha_cielo();" class="link_azul" style="cursor:pointer;cursor:hand;"><font size="1">Fechar [X]</font></div>
<p style="font-family: Arial, Helvetica, sans-serif; color:gray; font-size:11px; text-align:justify;">
<br>
A E-Prepag est� aumentando os clientes atendidos por Cart�o de Cr�dito e voc�, cliente especial E-Prepag, agora poder� fazer suas compras desta forma. Voc� pode efetuar 2 compras no valor de at� R$ <?php echo htmlspecialchars($_GET['valor']); ?>,00 cada, por semana (em um intervalo de 7 dias).<br><br>
Obs.: este meio de pagamento poder� n�o estar dispon�vel para alguns jogos.<br><br>
Qualquer d�vida, � s� entrar em contato conosco no <nobr><a href="mailto:suporte@e-prepag.com.br">suporte@e-prepag.com.br</a></nobr><br><br>
</p>
<center>
<img src="/imagens/gamer/voltar.gif" width="88" height="31" border="0" alt="Voltar" OnClick="fecha_cielo();" style="cursor:pointer;cursor:hand;"/>
</center>
</body>
</html>
