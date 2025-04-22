<?php

ini_set('memory_limit', '200M');
ini_set('max_execution_time', 0);

$timeIni = microtime(true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$html = '
        <table align="center" border="0" width="640" cellspacing="0" cellpadding="0">
            <tr>
                <td width="100%" height="79" bgcolor="#xxxxxx" background="http://www.e-prepag.com.br/prepag2/images/topo_email.jpg">
                  
                    <font face="arial" color="#FFFFFF" size="5" style="margin-left:30px;"><b>Aviso de desativação de conta</b></font>
     
                </td>
            </tr>
        </table>
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
					
					As contas sem movimentação serão desativadas até dia 15/02/2023.
					
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
                        <a href="https://www.e-prepag.com/game/suporte.php">Atendimento online</a> de segunda a sexta-feira, das 9h às 17h. <br /><br />
                        Atenciosamente<br><br>
                    </font>
                    <table border="0" width="100%" cellspacing="0">
                        <tr>
                            <td>
                                <a target="_blank" href="http://www.e-prepag.com"><img src="http://www.e-prepag.com.br/prepag2/images/logo_epp_email.png" align="left" border="0" /></a><br><br>
                                <font face="arial" color="#515151" size="2"><a class="estiloLink" target="_blank" href="http://www.e-prepag.com.br">www.e-prepag.com.br</a></font>
                            </td>
                            <td>
                                <a target="_blank" href="http://www.youtube.com/user/EPrepagVideos"><img src="http://www.e-prepag.com.br/prepag2/images/youtube.gif" align="right" style="margin-left:15px;" border="0" /></a>
                                <a target="_blank" href="https://www.facebook.com/eprepagcash"><img src="http://www.e-prepag.com.br/prepag2/images/facebook.gif" align="right" style="margin-left:15px;" border="0" /></a>
                                <a target="_blank" href="http://www.twitter.com/eprepag"><img src="http://www.e-prepag.com.br/prepag2/images/twitter.gif" align="right" border="0" style="*margin-right:25px;" /></a>
                            </td>
                        </tr>
                    </table>
                    <br>
                </td>
            </tr>
        </table>
        <table align="center" border="0" width="640" bgcolor="FFFFFF" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center" style="font: normal 12px arial, sans-serif; color: #515151;"><br>E-Prepag Copyright 2021 - Todos os direitos reservados</td>
            </tr>
        </table>';

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/constantes.php";
require_once "/www/includes/main.php";
require_once "/www/includes/gamer/main.php";
require_once "/www/includes/gamer/functions.php";

$sql = "select * from (
		select ug_id, (select count(*) from tb_venda_games where EXTRACT(YEAR FROM vg_data_inclusao) BETWEEN '2012' and '2022' and vg_ug_id = ug_id) as vendas, 'USUARIO' as ambiente,ug_nome,ug_email
		from usuarios_games where ug_ativo = 1 and EXTRACT(YEAR FROM ug_data_inclusao) BETWEEN '2000' and '2012'
	) as dados where vendas = 0 offset 114800 limit 3000;";  

/*$sql = "select * from (
	select ug_id, (select count(*) from tb_dist_venda_games where EXTRACT(YEAR FROM vg_data_inclusao) BETWEEN '2012' and '2022' and vg_ug_id = ug_id) as vendas, 'PDV' as ambiente,ug_nome_fantasia,ug_email
	from dist_usuarios_games where ug_ativo = 1 and EXTRACT(YEAR FROM ug_data_inclusao) BETWEEN '2000' and '2012'
) as dados where vendas = 0;";*/

$conexao = ConnectionPDO::getConnection()->getLink();
$query = $conexao->prepare($sql);
$query->execute();
$dados = $query->fetchAll(PDO::FETCH_ASSOC);

$enviaEmail = function($email, $titulo, $msg){
	
	$mail = new PHPMailer();
	//-----Altera��o exigida pela BaseNet(11/2017)-------------//
	$mail->Host     = "smtp.basenet.com.br";
	//---------------------------------------------------------//
	$mail->Mailer   = "smtp";
	$mail->From     = "suporte@e-prepag.com.br";
	$mail->SMTPAuth = true;     // turn on SMTP authentication
	$mail->Username = 'suporte@e-prepag.com.br';  // a valid email here
	$mail->Password = '@AnQ1V7hP#E7pQ31'; //'985856';		//'850637'; 
	$mail->FromName = "E-Prepag";	// " (EPP)"

	//-----Altera��o exigida pela BaseNet(11/2017)-------------//
	$mail->IsSMTP();
	$mail->SMTPSecure = "ssl";
	$mail->Port     = 465;
	//---------------------------------------------------------//        
	// Overwrite smt details for dev version cause e-prepag.com.br server reject it
	// When run bat files there is not ip address so we need use COMPUTERNAME to check
	//Comentar aki quando problema no email
	if(checkIP() || (class_exists('EmailEnvironment')  && EmailEnvironment::serverId() == 1)) {
		//  $mail->SMTPDebug  = 1; descomentar para debugar 
		$mail->IsSMTP();
		$mail->SMTPSecure = "ssl";
		$mail->Host     = "email-ssl.com.br";
		$mail->Port     = 465;
		$mail->From     = "send@e-prepag.com";
		$mail->Username = 'send@e-prepag.com';
		$mail->Password = 'sendeprepag2013';
		}

	$mail->AddReplyTo('suporte@e-prepag.com.br');
	$mail->AddAddress($email);

	$mail->Subject = $titulo;
	$mail->isHTML();
	$mail->Body    = $msg;
	$mail->AltBody = $body_plain;

	$sret = $mail->Send();	
	return $sret;

};

//$html = file_get_contents("http://e-prepag.com.br/atimo/email.php");
$file = fopen("/www/public_html/atimo/email-new.txt", "a+"); 
fwrite($file, "Data: ".date("d-m-Y H:i:s")." \n");
//$count = 1;

foreach($dados as $index => $value){
	
	$htmlAux = $html;
	$nome = ($value["ambiente"] == "PDV")? $value["ug_nome_fantasia"]: $value["ug_nome"];
	$htmlAux = str_replace("NOME", $nome, $htmlAux);
	//echo $count." - ".$value["ug_email"]."<br>";
	//$count++;
	if($enviaEmail($value["ug_email"], utf8_decode("Aviso de desativação"), utf8_decode($htmlAux))){ //   "andresilva@gokeitecnologia.com.br"  $value["ug_email"]
		fwrite($file, "Status: OK \n");
		fwrite($file, "E-mail: ".$value["ug_email"]." \n");
		fwrite($file, "Ambiente: ".$value["ambiente"]." \n");
		fwrite($file, str_repeat("*", 50)." \n");
	}else{
		fwrite($file, "Status: NAO \n"); 
		fwrite($file, "E-mail: ".$value["ug_email"]." \n");
		fwrite($file, "Ambiente: ".$value["ambiente"]." \n");
		fwrite($file, str_repeat("*", 50)." \n");
	}
	
}
fclose($file);

$time = microtime(true) - $timeIni;
echo "Tempo de execução: ". $time."<br>";

?>