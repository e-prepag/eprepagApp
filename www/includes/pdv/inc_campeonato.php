<?php require_once __DIR__ . '/../constantes_url.php'; ?>
<?php

$DIAS_VALIDADE_TOKEN_AVISO_LH = 20;

function manda_email_campeonato($ug_id, $ug_email, $ug_email_bcc){
	$venda_id = 0;
//echo "ug_email: $ug_email<br>";
//echo "ug_id: $ug_id<br>";

	if(strlen($ug_email)==0) {
		echo "<font color='red'>Erro: email vazio</font><br>";
		return;
	}
	//token
	if($msg == ""){
		//$token = date('YmdHis') . "," . $ug_email . "," . $usuarioId;
		$token = date('YmdHis', strtotime("+".$GLOBALS['DIAS_VALIDADE_TOKEN_AVISO_LH']." day")) . "," . $ug_email . "," . $ug_id;
echo "token: '$token'<br>";
		$objEncryption = new Encryption();
		$token = $objEncryption->encrypt($token);
	}

	//Envia email
	//--------------------------------------------------------------------------------
	if($msg == ""){
	
		$parametros['prepag_dominio'] = "" . EPREPAG_URL_HTTP . "";
		$msgEmail = email_cabecalho($parametros);
	    $msgEmail .= "
<html xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:m='http://schemas.microsoft.com/office/2004/12/omml' xmlns='http://www.w3.org/TR/REC-html40'>

<head>
<meta http-equiv=Content-Type content='text/html; charset=iso-8859-1'>
<meta name=Generator content='Microsoft Word 12 (filtered medium)'>
<!--[if !mso]>
<style>
v\:* {behavior:url(#default#VML);}
o\:* {behavior:url(#default#VML);}
w\:* {behavior:url(#default#VML);}
.shape {behavior:url(#default#VML);}
</style>
<![endif]-->
<style>
<!--
 /* Font Definitions */
 @font-face
	{font-family:Calibri;
	panose-1:2 15 5 2 2 2 4 3 2 4;}
@font-face
	{font-family:Tahoma;
	panose-1:2 11 6 4 3 5 4 4 2 4;}
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{margin:0cm;
	margin-bottom:.0001pt;
	font-size:11.0pt;
	font-family:'Calibri','sans-serif';}
a:link, span.MsoHyperlink
	{mso-style-priority:99;
	color:blue;
	text-decoration:underline;}
a:visited, span.MsoHyperlinkFollowed
	{mso-style-priority:99;
	color:purple;
	text-decoration:underline;}
p.MsoAcetate, li.MsoAcetate, div.MsoAcetate
	{mso-style-priority:99;
	mso-style-link:'Texto de balão Char';
	margin:0cm;
	margin-bottom:.0001pt;
	font-size:8.0pt;
	font-family:'Tahoma','sans-serif';}
span.EstiloDeEmail17
	{mso-style-type:personal-compose;
	font-family:'Calibri','sans-serif';
	color:windowtext;}
span.TextodebaloChar
	{mso-style-name:'Texto de balão Char';
	mso-style-priority:99;
	mso-style-link:'Texto de balão';
	font-family:'Tahoma','sans-serif';}
.MsoChpDefault
	{mso-style-type:export-only;}
@page Section1
	{size:612.0pt 792.0pt;
	margin:70.85pt 3.0cm 70.85pt 3.0cm;}
div.Section1
	{page:Section1;}
-->
</style>
<!--[if gte mso 9]><xml>
 <o:shapedefaults v:ext='edit' spidmax='2050' />
</xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext='edit'>
  <o:idmap v:ext='edit' data='1' />
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=PT-BR link=blue vlink=purple>

<div class=Section1>

<p class=MsoNormal>Bom dia,<o:p></o:p></p>

<p class=MsoNormal><b><o:p>&nbsp;</o:p></b></p>

<p class=MsoNormal>Em razão de alterações na estrutura do Campeonato Mundial
enviadas pela International e-Sports Federation (IeSF) - Coréia do Sul, para
este ano o campeonato de Warcraft 3 foi suspenso. &nbsp;A Organização da seletiva
brasileira do Campeonato Mundial 2011 Fifa Online 2 e Warcraft 3 pede desculpas
a todos os jogadores e interessados no jogo Warcraft 3 pela mudança. Lamentamos
o ocorrido e infelizmente esta decisão foge do controle da Organização
Brasileira.<o:p></o:p></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b>De qualquer forma o campeonato Fifa Online 2 (Fifa 2011)
prosseguirá normalmente. <o:p></o:p></b></p>

<p class=MsoNormal><b>Precisamos que você confirme a presença de sua Lan House
como arena para a Seletiva Brasileira clicando no link abaixo para aceitar os
termos e condições de participação.<o:p></o:p></b></p>

<p class=MsoNormal>
	    				<table border='0' cellspacing='0' width='90%'>
			            <tr>
			            	<td class='texto' colspan='2'>&nbsp;</td>
			            </tr>
			            <tr>
			            	<td class='texto'> 
			            		Para aceitar o Termo de Adesão ao Campeonato <a href='" . $parametros['prepag_dominio']. "/eprepag/moedavirtual/campeonato_adesao.php?token=" . $token . "'>clique aqui</a> ou acesse esta página: <br>
								" . $parametros['prepag_dominio']. "/eprepag/moedavirtual/campeonato_adesao.php?token=" . $token . "
			                </td>
			            </tr>
						</table>

</p>

<p class=MsoNormal><b><o:p>&nbsp;</o:p></b></p>

<p class=MsoNormal><b>Todos os comunicados para as próximas etapas serão feitos
através deste e-mail. Fique atento e acompanhe as novidades.<o:p></o:p></b></p>

<p class=MsoNormal><b><o:p>&nbsp;</o:p></b></p>

<p class=MsoNormal>Conheça também o novo site do campeonato <a
href='http://www.campeonatocbec2011.com.br'>www.campeonatocbec2011.com.br</a><o:p></o:p></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal>Caso tenha alguma dúvida envie um e-mail para <a
href='mailto:contato@fifawarcraft2011.com.br'>contato@fifawarcraft2011.com.br</a><o:p></o:p></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><i>Atenciosamente,<o:p></o:p></i></p>

<p class=MsoNormal><i><o:p>&nbsp;</o:p></i></p>

<p class=MsoNormal><i>Organização<o:p></o:p></i></p>

<p class=MsoNormal><i>Campeonato Mundial 2011 Fifa Online 2 &#8211; Coréia do Sul<o:p></o:p></i></p>

<p class=MsoNormal><i>Seletiva Brasileira</i><o:p></o:p></p>

</div>

</body>

</html>
";
			
		$parametros['email_campeonato'] = 1;
		$msgEmail .= email_rodape($parametros);

// Dummy
//if(!($ug_email=="reinaldo@e-prepag.com.br" || $ug_email == "joao.trevisan@e-prepag.com.br")) {
//$ug_email = "reinaldo@e-prepag.com.br";
//$ug_email_bcc = "joao.trevisan@e-prepag.com.br";
//} else {
//$ug_email = "reinaldo@e-prepag.com.br";
//$ug_email_bcc = "reinaldo@e-prepag.com.br, joao.trevisan@e-prepag.com.br";
//}

//echo "Enviado para ug_email: '$ug_email'<br>";	//", ug_email_bcc: '$ug_email_bcc'<br>";
		enviaEmail($ug_email, null, null, "AVISO IMPORTANTE - Mundial 2011 Fifa e Warcraft3", $msgEmail);
//echo "<hr>".$msgEmail."<hr>";
	}

}
?>