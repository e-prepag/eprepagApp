<?php include "includes/classPrincipal.php"; ?>
<?php validaSessao(); ?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>E-Prepag - Créditos para games online</title>
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

					<h2>Prazos e Condições</h2>

					<p><b>Bradesco</b>:</p>
					<ul>
					<li>Visa Electron (Débito em conta) - Entrega imediata em seu email, podendo haver algum atraso em função do Banco. Sem acréscimo de taxa adicional - Opção mais rápida!</li>
					<li>Depósito em conta/Transferência (offline) - de 1hora até 1dia útil - se informar corretamente os dados de pagamento. Sem acréscimo de taxa adicional.</li>
					<li>Transferência Online - Entrega imediata em seu email, podendo haver algum atraso em função da compensação bancária; Acréscimo de R$1,20 referente à taxa de serviço. Opção mais rápida!</li>
					</ul>
					 

					<p><b>Banco do Brasil</b>:</p>
					<ul>
					<li>Transferência Online - Entrega imediata em seu email, podendo haver algum atraso em função do Banco. Acréscimo de R$1,00 referente à taxa de serviço - Opção mais rápida!</li>
					<li>Depósito em conta/Transferência (offline) - de 1hora até 1dia útil se informar corretamente os dados de pagamento. Sem acréscimo de taxa.</li>
					</ul>


					<p><b>Boleto</b>:</p>
					<ul>
					<li>Entrega em seu email no dia útil seguinte ao pagamento, se for pago em qualquer banco até a data de vencimento. Se for pago em lotérica até a data do vencimento pode demorar até 2 dias úteis. Acréscimo R$ <?php echo number_format($BOLETO_TAXA_ADICIONAL, 2, ',', '.')?> de taxa de serviço.</li>
					</ul>

					 

					<h2>Sobre Segurança</h2>

					 

					<p>Comprar créditos de games on-line na E-Prepag é seguro.</p>

					 

					<p><b>Sabe por que?</b></p>

					 
					<ul>
					<li>A E-Prepag não armazena seus dados financeiros.</li>
					<li>Todas as operações financeiras são realizadas no próprio site do Banco.</li>
					<li>Dados finaceiros pessoais como nº do cartão e senha são digitados na própria página do Banco em um ambiente de seguaça próprio deles.</li>
					<li>A E-Prepag também disponibiliza opções de pagamento em que o cliente pode pagar direto em uma agência bancária sem precisar informar nenhum dado financeiro pessoal pela internet.</li>
					</ul>

					 

					<h2>Meios de Pagamento que disponibilizamos - Tipos</h2>

					 

					<p><b>Sem informar dados bancários pessoais</b>:</p>
					<ul>
					<li>Boleto - nesta opção você gera um boleto e paga diretamente em uma agência bancária até o vencimento;</li>
					<li>Depósito em conta/Transferência (offline) - após cadastro conosco, vá até uma agência, faça depósito em nossa conta e acesse este pedido no site, informe o Banco e Local de pagamento, data/hora, nº de documento e valor de compra.</li>
					</ul>

					 

					<p><b>Informando dados bancários pessoais no site do Banco</b>:</p>
					<ul>
					<li>Débito em conta - escolhendo esta opção, você será direcionado para a página do Banco, informando os dados necessários para debitar em sua conta. Informando esses dados sempre no ambiente do próprio Banco;</li>
					<li>Transferência Online - escolhendo esta opção, você será direcionado para a página do Banco, informando os dados necessários para debitar em sua conta. Informando esses dados sempre no ambiente do próprio Banco;</li>
					<li>Cartão de Crédito - escolhendo esta opção, você será direcionado para a página do Banco, informando os dados necessários para debitar em sua conta. Informando esses dados sempre no ambiente do próprio Banco;</li>
					</ul>

					 

					<p><b>Sistema Anti-Fraude</b></p>

					 

					<ul>
					<li>Possuímos mecanismos automáticos de evitar fraudes em nosso site;</li>
					<li>Controlamos valores, tipos de jogo e meios de pagamento;</li>
					<li>Certificados digitais e criptografia;</li>
					</ul>

					 

					<p><b>Conselhos ao cliente:</b></p>

					 
					<ul>
					<li>Nunca forneça sua conta e senha de usuário a ninguém;</li>
					<li>Nunca forneça seus dados de conta e senha do Banco a ninguém. Utilize somente em um site seguro como o do próprio Banco;</li>
					<li>Troque sua senha periodicamente e não compartilhe com ninguém;</li>
					<li>Cuidado com sites que não tem referência de outros sites conhecidos;</li>
					<li>Sempre encerre a sessão clicando no botão "Sair" ou fechando o seu Browser.</li>
					<li>Não escolha senhas que sejam muito fáceis de serem deduzidas.</li>
					<li>Tenha sempre um Anti-vírus atualizado em seu computador. Instale também um Firewall e Antispyware.</li>
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
	          	<b>Em até 48 horas, após a confirmação dos dados de pagamento:</b><br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Transferência Bancária<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Depósito na Agência<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>DOC Eletrônico<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Depósito OUTROS<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Boleto Bancário<br>
	          </td>
	        </tr>
	        <tr><td colspan="3">&nbsp;</td></tr>
	        <tr>
	          <td class="texto" height="25">
	          	<b>Remessa do exterior:</b><br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>O processamento do pedido será concluido somente após a liquidação do câmbio.<br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tempo aproximado de 5 dias úteis.<br>
	          	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<li>Será cobrada uma taxa de USD 20,00 referente a taxa de serviço bancário.<br>
	          </td>
	        </tr>
	        <tr><td colspan="3">&nbsp;</td></tr>
	        <tr>
	          <td class="texto" height="25">
	          	<b>Bancos disponíveis:</b><br>
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
