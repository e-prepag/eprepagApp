<?php include "includes/classPrincipal.php"; ?>
<?php validaSessao(); ?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>E-Prepag - Cr�ditos para games online</title>
	<style type="text/css">
		<!--
		body {
			margin-left: 0px;
			margin-top: 0px;
			margin-right: 0px;
			margin-bottom: 0px;
		}

		.texto_vermelho {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 11px;
			color: #FF0000;
		}
		-->
	</style>
	
	<link href="/incs/css.css" rel="stylesheet" type="text/css">
	<script language="JavaScript" type="text/JavaScript">
	<!--
	function MM_preloadImages() { //v3.0
	  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
	    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
	    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
	}
	
	function MM_swapImgRestore() { //v3.0
	  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
	}
	
	function MM_findObj(n, d) { //v4.01
	  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
	    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
	  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	  if(!x && d.getElementById) x=d.getElementById(n); return x;
	}
	
	function MM_swapImage() { //v3.0
	  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
	   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
	}
	//-->
	</script>

</head>

<body bgcolor="FFFFFF">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="FFFFFF">
<tr>
	<td>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
   		<tr>
			<td height="42" width="60" align="center">
				<img src="/eprepag/imgs/logo_eprepag.gif" title="E-PREPAG" border="0" />
			</td>
			<td align="center">
				<font face="Arial, Helvetia" color="000066" size="5"><b>E-Prepag <font color="FFCC00"><i>Money</i></font></b></font>
			</td>
			<td align="center">
				<a href="javascript: self.close();"><font face="Arial, Helvetia" color="000066" size="1">fechar</font></a>
			</td>
		</tr>
		</table>
		
	</td>
</tr>
<tr><td height="15"></td></tr>
<tr>
	<td height="100%" width="100%" valign="top">
		<table border="0" cellspacing="0" align="center" width="100%" bgcolor="0505FF">
    	<tr valign="middle">
      		<td width="50">&nbsp;</td><td align="left" class="texto"><font color="FFFFFF"><b><?=$pagina_titulo ?></b></font></td>
    	</tr>
		</table>


	<table  border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr><td>&nbsp;</td></tr>
    <tr valign="top" align="center">
      <td>
		<table border="0" cellspacing="0" width="90%" align="center">
	        <tr bgcolor="F0F0F0">
	          <td class="texto" align="center" height="25"><b>Formas de Pagamento e Prazo de Entrega</b></td>
	        </tr>
			<?php 
			$b_nova_forma_pagamento = false;
			$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);

//			echo ((is_object($usuarioGames))?"OK":"nope")."<br>";
			if(is_object($usuarioGames)) {
				if($usuarioGames->b_IsLogin_pagamento())  {
					$b_nova_forma_pagamento = true;
			?>
	        <tr>
	          <td class="texto">

					<h2>Prazos e Condi��es</h2>

					<p><b>Bradesco</b>:</p>
					<ul>
					<li>Visa Electron (D�bito em conta) - Entrega imediata em seu email, podendo haver algum atraso em fun��o do Banco. Sem acr�scimo de taxa adicional - Op��o mais r�pida!</li>
					<li>Dep�sito em conta/Transfer�ncia (offline) - de 1hora at� 1dia �til - se informar corretamente os dados de pagamento. Sem acr�scimo de taxa adicional.</li>
					<li>Transfer�ncia Online - Entrega imediata em seu email, podendo haver algum atraso em fun��o da compensa��o banc�ria; Acr�scimo de R$1,20 referente � taxa de servi�o. Op��o mais r�pida!</li>
					</ul>
					 

					<p><b>Banco do Brasil</b>:</p>
					<ul>
					<li>Transfer�ncia Online - Entrega imediata em seu email, podendo haver algum atraso em fun��o do Banco. Acr�scimo de R$1,00 referente � taxa de servi�o - Op��o mais r�pida!</li>
					<li>Dep�sito em conta/Transfer�ncia (offline) - de 1hora at� 1dia �til se informar corretamente os dados de pagamento. Sem acr�scimo de taxa.</li>
					</ul>


					<p><b>Boleto</b>:</p>
					<ul>
					<li>Entrega em seu email no dia �til seguinte ao pagamento, se for pago em qualquer banco at� a data de vencimento. Se for pago em lot�rica at� a data do vencimento pode demorar at� 2 dias �teis. Acr�scimo R$ <?php echo number_format($BOLETO_TAXA_ADICIONAL, 2, ',', '.')?> de taxa de servi�o.</li>
					</ul>

					 

					<h2>Sobre Seguran�a</h2>

					 

					<p>Comprar cr�ditos de games on-line na E-Prepag � seguro.</p>

					 

					<p><b>Sabe por que?</b></p>

					 
					<ul>
					<li>A E-Prepag n�o armazena seus dados financeiros.</li>
					<li>Todas as opera��es financeiras s�o realizadas no pr�prio site do Banco.</li>
					<li>Dados finaceiros pessoais como n� do cart�o e senha s�o digitados na pr�pria p�gina do Banco em um ambiente de segua�a pr�prio deles.</li>
					<li>A E-Prepag tamb�m disponibiliza op��es de pagamento em que o cliente pode pagar direto em uma ag�ncia banc�ria sem precisar informar nenhum dado financeiro pessoal pela internet.</li>
					</ul>

					 

					<h2>Meios de Pagamento que disponibilizamos - Tipos</h2>

					 

					<p><b>Sem informar dados banc�rios pessoais</b>:</p>
					<ul>
					<li>Boleto - nesta op��o voc� gera um boleto e paga diretamente em uma ag�ncia banc�ria at� o vencimento;</li>
					<li>Dep�sito em conta/Transfer�ncia (offline) - ap�s cadastro conosco, v� at� uma ag�ncia, fa�a dep�sito em nossa conta e acesse este pedido no site, informe o Banco e Local de pagamento, data/hora, n� de documento e valor de compra.</li>
					</ul>

					 

					<p><b>Informando dados banc�rios pessoais no site do Banco</b>:</p>
					<ul>
					<li>D�bito em conta - escolhendo esta op��o, voc� ser� direcionado para a p�gina do Banco, informando os dados necess�rios para debitar em sua conta. Informando esses dados sempre no ambiente do pr�prio Banco;</li>
					<li>Transfer�ncia Online - escolhendo esta op��o, voc� ser� direcionado para a p�gina do Banco, informando os dados necess�rios para debitar em sua conta. Informando esses dados sempre no ambiente do pr�prio Banco;</li>
					<li>Cart�o de Cr�dito - escolhendo esta op��o, voc� ser� direcionado para a p�gina do Banco, informando os dados necess�rios para debitar em sua conta. Informando esses dados sempre no ambiente do pr�prio Banco;</li>
					</ul>

					 

					<p><b>Sistema Anti-Fraude</b></p>

					 

					<ul>
					<li>Possu�mos mecanismos autom�ticos de evitar fraudes em nosso site;</li>
					<li>Controlamos valores, tipos de jogo e meios de pagamento;</li>
					<li>Certificados digitais e criptografia;</li>
					</ul>

					 

					<p><b>Conselhos ao cliente:</b></p>

					 
					<ul>
					<li>Nunca forne�a sua conta e senha de usu�rio a ningu�m;</li>
					<li>Nunca forne�a seus dados de conta e senha do Banco a ningu�m. Utilize somente em um site seguro como o do pr�prio Banco;</li>
					<li>Troque sua senha periodicamente e n�o compartilhe com ningu�m;</li>
					<li>Cuidado com sites que n�o tem refer�ncia de outros sites conhecidos;</li>
					<li>Sempre encerre a sess�o clicando no bot�o "Sair" ou fechando o seu Browser.</li>
					<li>N�o escolha senhas que sejam muito f�ceis de serem deduzidas.</li>
					<li>Tenha sempre um Anti-v�rus atualizado em seu computador. Instale tamb�m um Firewall e Antispyware.</li>
					</ul>

					 

			  </td>
	        </tr>

			<?php		
				} else {
//					echo "Not blogin<br>";
				}
			} else {
//				echo "Not object<br>";
			}

			if(!$b_nova_forma_pagamento) {

			?>

	        <tr>
	          <td class="texto" height="25">
	          	<b>Em at� 48 horas, ap�s a confirma��o dos dados de pagamento:</b><br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Transfer�ncia Banc�ria<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Dep�sito na Ag�ncia<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>DOC Eletr�nico<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Dep�sito OUTROS<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Boleto Banc�rio<br>
	          </td>
	        </tr>
	        <tr><td colspan="3">&nbsp;</td></tr>
	        <tr>
	          <td class="texto" height="25">
	          	<b>Remessa do exterior:</b><br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>O processamento do pedido ser� concluido somente ap�s a liquida��o do c�mbio.<br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tempo aproximado de 5 dias �teis.<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Ser� cobrada uma taxa de USD 20,00 referente a taxa de servi�o banc�rio.<br>
	          </td>
	        </tr>
	        <tr><td colspan="3">&nbsp;</td></tr>
	        <tr>
	          <td class="texto" height="25">
	          	<b>Bancos dispon�veis:</b><br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Bradesco<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Banco do Brasil<br>
	          </td>
	        </tr>
<?php
			}
?>

	        <tr><td colspan="3">&nbsp;</td></tr>
		</table>
		
      </td>
    </tr>
	</table>

	</td>
</tr>
<tr><td>&nbsp;</td></tr>
</table>
</body>
</html>
