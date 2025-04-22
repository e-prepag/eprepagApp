<?php
if(!function_exists('checkIP')) {
	function checkIP() {

        $aux_return = false;

        $sComputerName = 'SERVER_WITHOUT_COMPUTERNAME';

        $pComputerName = 'ENV_WITHOUT_COMPUTERNAME';

        foreach ($GLOBALS['ENV_LIST'] as $IP => $parametros) {

            if ( array_key_exists('COMPUTERNAME', $GLOBALS['_SERVER']) ) {

                $sComputerName = $GLOBALS['_SERVER']['COMPUTERNAME'];

            }

            else $sComputerName = php_uname('n');

            if ( array_key_exists('COMPUTERNAME', $parametros) ) {

                $pComputerName = $parametros['COMPUTERNAME'];

            }

            if(@$GLOBALS['_SERVER']['SERVER_NAME'] == $IP or $sComputerName == $pComputerName) {

                $aux_return = $parametros;

            }//end if($_SERVER['SERVER_NAME'] == $IP or $_SERVER['COMPUTERNAME'] == $parametros['COMPUTERNAME'])

        }//end foreach

        return $aux_return;

    }
}
function obtemDesconto($opr_codigo, $vg_pagto_tipo, $ug_id, $total_pedido){

        $msg = "";

        $des_perc_desconto = -1;

        //Ajuste
        if(!is_numeric($opr_codigo)) $opr_codigo = 0;
        if(!is_numeric($vg_pagto_tipo)) $vg_pagto_tipo = 0;
        if(!is_numeric($ug_id)) $ug_id = 0;
        if(!is_numeric($total_pedido)) $total_pedido = 0;

        //Procura desconto
        for($i = 0; $i < 4 && $des_perc_desconto == -1; $i++){
                $sql  = "select * from tb_dist_descontos des 
                                 where des.des_opr_codigo = $opr_codigo and des.des_vg_pagto_tipo = $vg_pagto_tipo and des.des_ug_id = $ug_id";
                $rs_desc = SQLexecuteQuery($sql);
                if($rs_desc && pg_num_rows($rs_desc) == 1){
                        $rs_desc_row = pg_fetch_array($rs_desc);
                        $des_perc_desconto = $rs_desc_row['des_perc_desconto'];
                }

                if($i == 0) $ug_id = 0;
                elseif($i == 1) $vg_pagto_tipo = 0;
                elseif($i == 2) $opr_codigo = 0;
        }

        if($des_perc_desconto == -1) $des_perc_desconto = 0;

        return $des_perc_desconto;
}


function valida_formatacao($tipo, $tamanho, $valor){

        if(is_null($tipo) || is_null($tamanho) || is_null($valor)) return false;

        $valor = trim($valor);

        if($tipo == "N"){
                if(eregi("^[0-9]{" . $tamanho . "}$", $valor)) return true;

        } else if($tipo == "Nle"){
                if(eregi("^[0-9]{1," . $tamanho . "}$", $valor)) return true;

        }else if($tipo == "NleX"){
                if(eregi("^[0-9]{1," . $tamanho . "}$", $valor) || eregi("^[0-9]{1," . ($tamanho - 1) . "}X$", $valor)) return true;	

        }

        return false;
}

if(!function_exists('verificaCPFEx')) {
function verificaCPFEx($CPF){

        if(
                $CPF == '00000000000'
                || $CPF == '11111111111'
                || $CPF == '22222222222'
                || $CPF == '33333333333'
                || $CPF == '44444444444'
                || $CPF == '55555555555'
    		|| $CPF == '66666666666'
    		|| $CPF == '77777777777'
    		|| $CPF == '88888888888'
    		|| $CPF == '99999999999'
        ) return 0;

        return verificaCPF2a($CPF);
}

}

if(!function_exists('DVCampo_Modulo10')) {
function DVCampo_Modulo10 ($campo) {
        $DOIS_UM = array(2,1);
    $soma = 0;
    $tam = strlen($campo);
    $campoTmp = strrev($campo);

    for($i=0;$i<$tam;$i++) {
                $aux = $DOIS_UM[$i % 2] * substr($campoTmp,$i,1);
                if ($aux >= 10) $soma = $soma + (floor($aux/10) + $aux % 10);
                else $soma = $soma + $aux;
        }

        $soma = $soma % 10;
        if ($soma > 0) $dVCampo=10-$soma;
        else $dVCampo=0;

        return $dVCampo;
}
}

if(!function_exists('DVCampo_Modulo10')) {
	function checkIP() {

        $aux_return = false;

        $sComputerName = 'SERVER_WITHOUT_COMPUTERNAME';

        $pComputerName = 'ENV_WITHOUT_COMPUTERNAME';

        foreach ($GLOBALS['ENV_LIST'] as $IP => $parametros) {

            if ( array_key_exists('COMPUTERNAME', $GLOBALS['_SERVER']) ) {

                $sComputerName = $GLOBALS['_SERVER']['COMPUTERNAME'];

            }

            else $sComputerName = php_uname('n');

            if ( array_key_exists('COMPUTERNAME', $parametros) ) {

                $pComputerName = $parametros['COMPUTERNAME'];

            }

            if(@$GLOBALS['_SERVER']['SERVER_NAME'] == $IP or $sComputerName == $pComputerName) {

                $aux_return = $parametros;

            }//end if($_SERVER['SERVER_NAME'] == $IP or $_SERVER['COMPUTERNAME'] == $parametros['COMPUTERNAME'])

        }//end foreach

        return $aux_return;

    }
}

function obterIdVendaValido(){

        $maxID = 100000000-1;
        $nmax = 100;
        $n = 0;

        $venda_id_rand = mt_rand(1, $maxID);
//		$venda_id_rand .= DVCampo_Modulo10($venda_id_rand);
        while(existeIdVenda($venda_id_rand)){
                $venda_id_rand = mt_rand(1, $maxID);
//			$venda_id_rand .= DVCampo_Modulo10($venda_id_rand);
        }
        if($n>=$nmax) {
                $venda_id_rand = "????";
                echo "<p><font color='#FF0000'>Não foi encontrado IDVenda disponível. Favor contatar o Administrador.</font></p>";
                exit();
        }

        return $venda_id_rand;
}


function existeIdVenda($venda_id_rand){

            $ret = true;

            //SQL
            $sql = "select count(*) as qtde from tb_dist_venda_games ";
            $sql .= " where vg_id = " . SQLaddFields($venda_id_rand, "");

            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) > 0){
                    $rs_row = pg_fetch_array($rs);
                    if($rs_row['qtde'] == 0) $ret = false;
            }			

            return $ret;   	
}

function gravaLog_BoletoExpressLH($mensagem){
        global $raiz_do_projeto;
        //Arquivo
//		$file = $GLOBALS['ARQUIVO_LOG_SQL_EXECUTE_QUERY'];
        $file = $raiz_do_projeto . 'log/log_dist_commerce_BoletoExpressLH.txt';	

        //Mensagem
	$mensagem = date('Y-m-d H:i:s')." - ".$mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_BloqueioPagtoOnline($mensagem){
        global  $raiz_do_projeto;

        //Arquivo
        $file = $raiz_do_projeto . 'log/log_BloqueioPagtoOnline_LH.txt';

        //Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . " (Lanhouse)".PHP_EOL . $mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_PagtoOnlineUsuariosBloqueadosParaVIP($mensagem){
        global  $raiz_do_projeto;

        //Arquivo
        $file = $raiz_do_projeto . 'log/log_Money_PagtoOnlineUsuariosBloqueadosParaVIP_LH.txt';

        //Mensagem
	$mensagem = str_repeat("=", 80).PHP_EOL.date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . PHP_EOL . $mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_EnviaEmail($canal, $to, $subject) {
        global  $raiz_do_projeto;

        //Arquivo
        $file = $raiz_do_projeto . 'log/log_EnviEmail.txt';

        //Mensagem
	$mensagem = date('Y-m-d H:i:s') . "|$canal|$to|$subject ".PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function redirect($strRedirect){

        ob_end_clean();

        if(substr($strRedirect, 0, 4) != "http")
                $strRedirect = (strtoupper($_SERVER['HTTPS']) == "ON"?"https":"http") . "://" . $_SERVER['HTTP_HOST'] . $strRedirect;

        //Reidrect interno
        //header("Location: " . $strRedirect);

        //redirect externo
        ?><html><body onload="window.location='<?=$strRedirect?>'"><?php
        exit;
}

function Dia_Semana($posicao){
        //'posicao = número relacionado a string de dados
        $dias = array("Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado");
        return $dias[$posicao];
}

function Mes_Do_Ano($posicao){
        //'posicao = número relacionado a string de dados
        $meses = array("", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");
        return $meses[$posicao];
}

function Data_Atual_Por_Extenso(){
        $diaDaSemana = Dia_Semana(date("w"));
        $mesDoAno = Mes_Do_Ano(date("n"));
        return $diaDaSemana . ", " . date("j") . " de " . $mesDoAno . " de " . date("Y");
}

function BomDia(){
        $hora = date("H");
        if($hora >= 0 && $hora < 12)
                return "Bom dia";
        elseif($hora >= 12 && $hora < 18)
                return "Boa tarde";
        else
                return "Boa noite";
}

function session_kill(){
        // Initialize the session.
        // If you are using session_name("something"), don't forget it now!
        session_start();

        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }

        // Finally, destroy the session.
        session_destroy();

}

function moeda2numeric($val){

        if(	(strlen($val) >= 4)
                && (strrpos($val, ",") == strlen($val) - 3)
                && is_numeric(substr($val, 0, 1))
                //&& (substr($val, 0, 1) != "0")
                ){

                $val = str_replace('.','',$val);
                $val = str_replace(',','.',$val);
        }

        return $val;
}

function formata_codigo_venda($codigo){

        return str_pad($codigo, 8, "0", STR_PAD_LEFT);

}

function formata_data_ts($data, $gravar, $blComHora, $blComSegundos){

        $mask = $data;

        //Entra: yyyy-mm-dd hh:mm:ss
        //Sai: dd/mm/yyyy hh:mm:ss
        if($gravar == 0){
                $dia = substr($mask, 8, 2);
                $mes = substr($mask, 5, 2);
                $ano = substr($mask, 0, 4);
                $doc = $dia."/".$mes."/".$ano;

                if($blComHora){
                        $hora = substr($mask, 11, 2);
                        $minuto = substr($mask, 14, 2);
                        $segundo = substr($mask, 17, 2);
                        $doc = $doc . " " . $hora . ":" . $minuto;
                        if($blComSegundos) $doc = $doc . ":" . $segundo;
                }
        }

        //Entra: dd/mm/yyyy hh:mm:ss
        //Sai: yyyymmddhhmmss
        if($gravar == 1){
                $dia = substr($mask, 0, 2);
                $mes = substr($mask, 3, 2);
                $ano = substr($mask, 6, 4);
                $doc = $ano . $mes . $dia;
                if($blComHora){
                        $hora = substr($mask, 11, 2);
                        $minuto = substr($mask, 14, 2);
                        $segundo = substr($mask, 17, 2);
                        $doc .= $hora . $minuto;
                        if($blComSegundos) $doc .= $segundo;
                        else $doc .= "00";

                } else {
                        $doc .= "000000";
                }
        }

        //Entra: dd/mm/yyyy hh:mm:ss
        //Sai: yyyy-mm-dd hh:mm:ss
        if($gravar == 2){
                $dia = substr($mask, 0, 2);
                $mes = substr($mask, 3, 2);
                $ano = substr($mask, 6, 4);
                $doc = $ano . "-" . $mes . "-" . $dia;
                if($blComHora){
                        $hora = substr($mask, 11, 2);
                        $minuto = substr($mask, 14, 2);
                        $segundo = substr($mask, 17, 2);
                        $doc = $doc . " " . $hora . ":" . $minuto;
                        if($blComSegundos) $doc = $doc . ":" . $segundo;

                } else {
                        $doc .= "00:00:00";
                }
        }

        return $doc;
}

function retorna_ip_acesso_pdv() {
        $realip = "";
        if (isset($_SERVER)) {
                if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                } else {
                        $ip = $_SERVER['REMOTE_ADDR'];
                }
   } else {
                if (getenv('HTTP_X_FORWARDED_FOR')) {
                        $ip = getenv('HTTP_X_FORWARDED_FOR');
                } elseif (getenv('HTTP_CLIENT_IP')) {
                        $ip = getenv('HTTP_CLIENT_IP');
                } else {
                        $ip = getenv('REMOTE_ADDR');
                }
   }
   return $ip;
}

function usuarios_games_log($tipo, $usuario_games_id, $venda_id, $observacao = null){

        // Log primeiro de Operador
        if(isSessionOperador()) {
                $usuarioGamesOperador = unserialize($_SESSION['dist_usuarioGamesOperador_ser']);
                if($usuarioGamesOperador) $usuario_games_operador_id = $usuarioGamesOperador->getId();
                if($usuario_games_operador_id) {
                        usuarios_games_operador_log($tipo, $usuario_games_operador_id, $venda_id);	
                }
        }

        if(!$usuario_games_id){
                $usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
                if($usuarioGames) $usuario_games_id = $usuarioGames->getId();
        }
        if(!$usuario_games_id) return;

        $sql = "insert into dist_usuarios_games_log (" .
                        "	ugl_data_inclusao, ugl_ip, ugl_uglt_id, ugl_ug_id, ugl_vg_id" . ($observacao == null ? "" : ", ugl_obs") .
                        ") values (";
        $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
        $sql .= SQLaddFields(retorna_ip_acesso_pdv(), "s") . ",";
        $sql .= SQLaddFields($tipo, "") . ",";
        $sql .= SQLaddFields($usuario_games_id, "") . ",";
        $sql .= SQLaddFields($venda_id, "");
        if($observacao != null) {
                $sql.= ", ". SQLaddFields($observacao, "s");
        }
        $sql .= ")";
        $ret = SQLexecuteQuery($sql);

}

function usuarios_games_operador_log($tipo, $usuario_games_operador_id, $venda_id){

        if(!$usuario_games_operador_id){
                $usuarioGamesOperador = unserialize($_SESSION['dist_usuarioGamesOperador_ser']);
                if($usuarioGamesOperador) $usuario_games_operador_id = $usuarioGamesOperador->getId();
        }
        if(!$usuario_games_operador_id) return;

        $sql = "insert into dist_usuarios_games_operador_log (" .
                        "	ugol_data_inclusao, ugol_ip, ugol_uglt_id, ugol_ugo_id, ugol_vg_id" .
                        ") values (";
        $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
        $sql .= SQLaddFields($_SERVER['REMOTE_ADDR'], "s") . ",";
        $sql .= SQLaddFields($tipo, "") . ",";
        $sql .= SQLaddFields($usuario_games_operador_id, "") . ",";
        $sql .= SQLaddFields($venda_id, "") . ")";

        $ret = SQLexecuteQuery($sql);

}

function theRealStripTags2($string){

   $tam=strlen($string);
   // tam have number of cars the string

   $newstring="";
   // newstring will be returned

   $tag=0;
   /* if tag = 0 => copy car from string to newstring
           if tag > 0 => don't copy. Found one or more  '<' and need
           to search '>'. If we found 3 '<' need to find all the 3 '>'
   */

   /* I am C programmer. walk in a string is natural for me and more efficient
   */
   for ($i=0; $i < $tam; $i++){
           // If I found one '<', $tag++ and continue whithout copy
           if ($string{$i} == '<'){
                   $tag++;
                   continue;
           }

           // if I found '>', decrease $tag and continue 
           if ($string{$i} == '>'){
                   if ($tag){
                           $tag--;
                   }
           /* $tag never be negative. If string is "<b>test</b>>"
                   (error, of course) $tag will stop in 0
           */
                   continue;
           }

           // if $tag is 0, can copy 
           if ($tag == 0){
                   $newstring .= $string{$i}; // simple copy, only one car
           }
   }
   return $newstring;
}


function enviaEmail($to, $cc, $bcc, $subject, $msgEmail) {
        $body_plain = str_replace("\r\n", "", $msgEmail);
        $body_plain = str_replace("<br>", "\r\n", $msgEmail);
        $body_plain = str_replace("\t", "", theRealStripTags2($body_plain));
        $body_plain = html_entity_decode($body_plain, ENT_QUOTES, 'ISO8859-1');
        $body_plain = str_replace("    ", "", $body_plain);
        $body_plain = str_replace("\r\n\r\n\r\n\r\n", "\r\n", $body_plain);
        $body_plain = str_replace(", \r\n", ", ", $body_plain);

        return enviaEmail3($to, $cc, $bcc, $subject, $msgEmail, $body_plain);	
}

	// Está usando esta enviaEmail3() no envio de emails de Lanhouses
function enviaEmail3($to, $cc, $bcc, $subject, $body_html, $body_plain) {

        $mail = new PHPMailer();
        //		$mail->Host     = "smtp.e-prepag.com.br";	//"localhost";
        //-----Alteração exigida pela BaseNet(11/2017)-------------//
        $mail->Host     = "smtp.basenet.com.br";
        //---------------------------------------------------------//
        $mail->Mailer   = "smtp";
        $mail->From     = "suporte@e-prepag.com.br";
        $mail->SMTPAuth = true;     // turn on SMTP authentication
        $mail->Username = 'suporte@e-prepag.com.br';  // a valid email here
        $mail->Password = '@AnQ1V7hP#E7pQ31'; //'985856';		//'850637'; 
        $mail->FromName = "E-Prepag";	  // "(EPP LH)"

        //-----Alteração exigida pela BaseNet(11/2017)-------------//
        $mail->IsSMTP();
        $mail->SMTPSecure = "ssl";
        $mail->Port     = 465;
        //---------------------------------------------------------//

        // Overwrite smt details for dev version cause e-prepag.com.br server reject it
        // You can just add your IP or use elseif with your details
        //Comentar aki quando problema no envio de email
        if(checkIP() || (class_exists('EmailEnvironment')  && EmailEnvironment::serverId() == 1)) {
        //                    $mail->SMTPDebug  = 2; //descomentar para debugar 
            $mail->IsSMTP();
            $mail->SMTPSecure = "ssl";
            $mail->Host     = "email-ssl.com.br";
            $mail->Port     = 465;
            $mail->From     = "send@e-prepag.com";
            $mail->Username = 'send@e-prepag.com';
            $mail->Password = 'sendeprepag2013';
        }

        // Reply-to
        $mail->AddReplyTo('suporte@e-prepag.com.br');

        //To
        if($to && trim($to) != ""){
                $toAr = explode(",", $to);
                for($i = 0; $i < count($toAr); $i++) $mail->AddAddress($toAr[$i]);
        }

        //Cc
        if($cc && trim($cc) != ""){
                $ccAr = explode(",", $cc);
                for($i = 0; $i < count($ccAr); $i++) $mail->AddCC($ccAr[$i]);
        }

        //Bcc
        if($bcc && trim($bcc) != ""){
                $bccAr = explode(",", $bcc);
                for($i = 0; $i < count($bccAr); $i++) $mail->AddBCC($bccAr[$i]);
        }


        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = $body_plain;

        $sret = $mail->Send();	

        gravaLog_EnviaEmail("L", $to, $subject);

        return $sret;	

}

function enviaEmail4($to, $cc, $bcc, $subject, $body_html, $body_plain, $attach = null, $stringAttach = false, $nome = '') {

    $mail = new PHPMailer();
//                $mail->Host     = "smtp.e-prepag.com.br";	//"localhost";
    //-----Alteração exigida pela BaseNet(11/2017)-------------//
    $mail->Host     = "smtp.basenet.com.br";
    //---------------------------------------------------------//
    $mail->Mailer   = "smtp";
    $mail->From     = "suporte@e-prepag.com.br";
    $mail->SMTPAuth = true;     // turn on SMTP authentication
    $mail->Username = 'suporte@e-prepag.com.br';  // a valid email here
    $mail->Password = '@AnQ1V7hP#E7pQ31'; //'985856';		//'850637'; 
    $mail->FromName = "E-Prepag";	// " (EPP)"
    $mail->isHTML(true);
    //-----Alteração exigida pela BaseNet(11/2017)-------------//
    $mail->IsSMTP();
    $mail->SMTPSecure = "ssl";
    $mail->Port     = 465;
    //---------------------------------------------------------//  

    // Overwrite smt details for dev version cause e-prepag.com.br server reject it
    // When run bat files there is not ip address so we need use COMPUTERNAME to check
    //Comentar aki quando problema no envio de email
    if(checkIP() || (class_exists('EmailEnvironment')  && EmailEnvironment::serverId() == 1)) {
    //                    $mail->SMTPDebug  = 2; //descomentar para debugar 
        $mail->IsSMTP();
        $mail->SMTPSecure = "ssl";
        $mail->Host     = "email-ssl.com.br";
        $mail->Port     = 465;
        $mail->From     = "send@e-prepag.com";
        $mail->Username = 'send@e-prepag.com';
        $mail->Password = 'sendeprepag2013';
    }

    // Reply-to
    $mail->AddReplyTo('suporte@e-prepag.com.br');

    //To
    if ($to && trim($to) != "") {
        $toAr = explode(",", $to);
        for ($i = 0; $i < count($toAr); $i++)
            $mail->AddAddress($toAr[$i]);
    }

    //Cc
    if ($cc && trim($cc) != "") {
        $ccAr = explode(",", $cc);
        for ($i = 0; $i < count($ccAr); $i++)
            $mail->AddCC($ccAr[$i]);
    }

    //Bcc
    if ($bcc && trim($bcc) != "") {
        $bccAr = explode(",", $bcc);
        for ($i = 0; $i < count($bccAr); $i++)
            $mail->AddBCC($bccAr[$i]);
    }

    $mail->Subject = $subject;
    $mail->Body = $body_html;
    $mail->AltBody = $body_plain;

    if ( !is_null($attach) ) {
        if ( $stringAttach ) {
            $mail->addStringAttachment($attach, $nome);
        } else {
            $mail->addAttachment($attach);
        }
    }

    $sret = $mail->Send();

    gravaLog_EnviaEmail("L", $to, $subject);

    return $sret;
}//end function enviaEmail4

function enviaEmail2($to, $cc, $bcc, $subject, $body_html, $body_plain) {

		$s_eol = "\r\n";

		$body_simple = $body_html;
		
		$boundary = md5(uniqid(time())); 

		$headers  = 'From: E-Prepag <suporte@e-prepag.com.br>' . $s_eol;
		$headers .= 'Reply-To: E-Prepag <suporte@e-repag.com.br>' . $s_eol;
		if($cc)  $headers .= 'Cc: '  . $cc  . $s_eol;
		if($bcc) $headers .= 'Bcc: ' . $bcc . $s_eol;
		$headers .= 'MIME-Version: 1.0' .$s_eol; 
		$headers .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '"' . $s_eol . $s_eol;
		$headers .= $body_simple . $s_eol; 
		$headers .= '--' . $boundary . $s_eol. $s_eol; 
		$headers .= 'Content-Type: text/plain; charset=ISO-8859-1' .$s_eol; 
		$headers .= 'Content-Transfer-Encoding: 8bit'. $s_eol . $s_eol;
		$headers .= $body_plain . $s_eol;
		$headers .= '--' . $boundary . $s_eol. $s_eol;
		$headers .= 'Content-Type: text/HTML; charset=ISO-8859-1' .$s_eol;
		$headers .= 'Content-Transfer-Encoding: 8bit'. $s_eol . $s_eol;
		$headers .= $body_html . $s_eol; 
		$headers .= '--' . $boundary . "--" . $s_eol; 

		return mail($to, $subject,'', $headers);

}


function email_cabecalho($parametros){ 

        if(isValidaSessao()){

                $usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
                if($usuarioGames->getTipoCadastro() == 'PF'){

                        //Prezado(a)				
                        if($usuarioGames->getSexo() == "M") $prezado = "Prezado ";
                        else if($usuarioGames->getSexo() == "F") $prezado = "Prezada ";
                        else  $prezado = "Prezado(a) ";

                        //Nome
                        $nome = $usuarioGames->getNome();
                }

                if($usuarioGames->getTipoCadastro() == 'PJ'){

                        //Prezado(a)				
                        $prezado = "";

                        //Nome
                        $nome = $usuarioGames->getNomeFantasia();
                }

        } else {

                if($parametros['tipo_cadastro'] == 'PF'){

                        //prezado
                        if($parametros['sexo'] == "M") $prezado = "Prezado ";
                        else if($parametros['sexo'] == "F") $prezado = "Prezada ";
                        else $prezado = "Prezado(a) ";

                        //Nome
                        if($parametros['nome']) $nome = $parametros['nome'];
                        else $nome = " Usuário(a) E-PREPAG";

                }

                if($parametros['tipo_cadastro'] == 'PJ'){

                        //Prezado(a)				
                        $prezado = "";

                        //Nome
                        if($parametros['nome_fantasia']) $nome = $parametros['nome_fantasia'];
                        else $nome = " Usuário(a) E-PREPAG";
                }

        }

        $email_cab = "
                                        <html>
                                        <head>
                                                <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
                                                <title>E-Prepag - Créditos para games online</title>
                                                <style type='text/css'>
                                                        <!--
                                                        body {
                                                                margin-left: 0px;
                                                                margin-top: 0px;
                                                                margin-right: 0px;
                                                                margin-bottom: 0px;
                                                        }
                                                        .texto {
                                                                font-family: Arial, Helvetica, sans-serif;
                                                                font-size: 11px;
                                                                color: #333333;
                                                                text-decoration: none;
                                                        }
                                                        .texto_vermelho {
                                                                font-family: Arial, Helvetica, sans-serif;
                                                                font-size: 11px;
                                                                color: #FF0000;
                                                        }
                                                        .link_azul {  text-decoration: none; color: #00008C}
                                                        .link_azul:hover { color: #00008C; text-decoration: underline}
                                                        .field_dados {	
                                                                color: #666666;
                                                                text-decoration: none;
                                                                border: 1px solid #336666;
                                                        }
                                                        .botao_simples
                                                        { 
                                                                font-family: Arial, Helvetica, sans-serif; 
                                                                font-size: 13px; 
                                                                color: #FFFFFF; 
                                                                background-color: #A6A6A6; 
                                                                border: none; 
                                                                text-transform: none; 
                                                                font-weight: bold; 
                                                                height: 20;
                                                        }
                                                        .rodape {
                                                                font-family: Arial, Helvetica, sans-serif;
                                                                font-size: 10px;
                                                                color: #787878;
                                                        }
                                                        -->
                                                </style>
                                        </head>
                                        <body bgcolor='FFFFFF'>
                                        <table width='570' height='100%' border='0' cellspacing='0' cellpadding='0' align='center' bgcolor='FFFFFF'>
                                        <tr>
                                                <td>
                                                        <table border='0' cellspacing='0' width='100%'>
                                                <tr valign='middle' bgcolor='#FFFFFF'>
                                                        <td align='left' class='texto'>
                                                                E-PREPAG, SÃO PAULO, SP, " . Data_Atual_Por_Extenso(). ", " . date("H:i") . "<br><br><br>
                                                        </td>
                                                </tr>
                                                <tr valign='middle' bgcolor='#FFFFFF'>
                                                        <td align='left' class='texto'>" . 
                                                                $prezado . $nome . "." .
                                                                        "<br><br><br>
                                                        </td>
                                                </tr>
                                                        </table>
                                ";

        return $email_cab;
}
	
function email_rodape($parametros){ 

        if(!isset($parametros['email_campeonato'])) {
                $email_rod  = "
                                                                <br>
                                                                <table border='0' cellspacing='0' width='100%'>
                                                                <tr valign='middle' bgcolor='#FFFFFF'>
                                                                        <td align='left' class='texto'>
                                                                                <br><br>
                                                                                A E-Prepag, através de seus consultores de negócios, 
                                                                                está disponível para esclarecer quaisquer dúvidas através do e-mail <a href='mailto:suporte@e-prepag.com.br'>suporte@e-prepag.com.br</a>.<br><br>
                                                                                Agradecemos por sua preferência em nossos produtos e serviços.<br><br>
                                                                                Atenciosamente, <br><br>
                                                                                E-Prepag<br>
                                                                                * One Stop Total Billing Service!<br>
                                                                                www.e-prepag.com.br<br>
                                                                        </td>
                                                                </tr>
                                                                </table>

                                                        </td>
                                                </tr>";
        }
        $email_rod  .= "	<tr><td>&nbsp;</td></tr>
                                        <tr align='center'><td><hr></td></tr>
                                        <tr>
                                                <td height='10'>
                                                        <br>
                                                        <table width='100%'  border='0' cellpadding='0' cellspacing='0' bgcolor='#F1F1F1'>
                                                        <tr height='23'>
                                                        <td width='1%'></td>
                                                        <td width='98%' align='center' class='rodape'>E-Prepag Copyright ".date('Y').". Todos os direitos reservados.</td>
                                                        <td width='1%' align='right'></td>
                                                        </tr>
                                                        </table>
                                                <br>
                                                </td>
                                        </tr>
                                        </table>
                                        </body>
                                        </html>
                                ";

        return $email_rod;
}
	
function buscaArquivosIniciaCom($folder, $ordem = 'nome', $direcao = 'asc', $iniciaCom) {

        $arquivoAr = array();

        if(is_dir($folder)){
                if ($handle = opendir($folder)) {
                        //Carrega e Filtra os arquivos
                        while(false !== ($file = readdir($handle))) {
                           if ($file != '.' && $file != '..') {
                                        if($iniciaCom != ''){
                                                if(strpos(strtolower($file), strtolower($iniciaCom)) !== false){
                                                        if($ordem == 'nome') $arquivoAr[strtolower($file)] = $file;
                                                        if($ordem == 'data') $arquivoAr[date("YmdHis", filemtime($folder.$file))] = $file;
                                                }
                                        } else {
                                                if($ordem == 'nome') $arquivoAr[strtolower($file)] = $file;
                                                if($ordem == 'data') $arquivoAr[date("YmdHis", filemtime($folder.$file))] = $file;
                                        }					
                                }
                        }
                        closedir($handle);

                        //Ordena os arquivos
                        if (count($arquivoAr) != 0) {
                                if($direcao == 'asc') ksort($arquivoAr);
                                if($direcao == 'desc') krsort($arquivoAr);
                        }

                        return array_values($arquivoAr);
                }
        }

        return $arquivoAr;
}
	
function obtemContentType($strFileType){

        switch (strtolower($strFileType)) {
                 case 'asf':
                          $ContentType = 'video/x-ms-asf'; break;
                 case 'avi':
                          $ContentType = 'video/avi'; break;
                 case 'doc':
                          $ContentType = 'application/msword'; break;
                 case 'zip':
                          $ContentType = 'application/zip'; break;
                 case 'xls':
                          $ContentType = 'application/vndms-excel'; break;
                 case 'gif':
                          $ContentType = 'image/gif'; break;
                 case 'jpg':
                 case 'jpeg':
                          $ContentType = 'image/jpeg'; break;
                 case 'wav':
                          $ContentType = 'audio/wav'; break;
                 case 'mp3':
                          $ContentType = 'audio/mpeg3'; break;
                 case 'mpg':
                 case 'mpeg':
                          $ContentType = 'video/mpeg'; break;
                 case 'rtf':
                          $ContentType = 'application/rtf'; break;
                 case 'htm':
                 case 'html':
                          $ContentType = 'text/html'; break;
                 case 'asp':
                          $ContentType = 'text/asp'; break;
                 case 'mov':
                          $ContentType = 'video/quicktime'; break;
                 case 'txt':
                          $ContentType = 'text/plain'; break;
                 default:
                          //Handle All Other Files
                          $ContentType = 'application/octet-stream';
        }

        return $ContentType;
}

function formatar($dados) {
  $arrayNome = strtolower($dados);
  $arrayNome = explode(" ",$arrayNome);

  if (sizeof($arrayNome) > 0)
  {
   $dados = "";
   $restri = array("em","e","&","da","das","de","des","do","dos");

   foreach($arrayNome as $ind=>$dado)
    if (in_array($dado,$restri))
     if ($ind == count($arrayNome) - 1)
      $dados .= $dado;
     else
      $dados .= $dado . " ";
    else
     if ($ind == count($arrayNome) - 1)
      $dados .= ucfirst($dado);
     else
      $dados .= ucfirst($dado) . " ";
  }
  else
   $dados = ucfirst($dados);

  return $dados;
}



// http://www.andreavb.com/tip000013.html

function Encrypt($icText) {
	$icChar = "";
    $icLen = strlen($icText);

    for($i=0;$i<$icLen;$i++) {
        $icChar = substr($icText, $i, 1);
		if((ord($icChar)>=65) && (ord($icChar)<=90)) {
			$icChar = chr(ord($icChar) + 127);
		} elseif ((ord($icChar)>=97) && (ord($icChar)<=122)) {
			$icChar = chr(ord($icChar) + 121);
		} elseif ((ord($icChar)>=48) && (ord($icChar)<=57)) {
			$icChar = chr(ord($icChar) + 196);
		} elseif (ord($icChar)==32) {
			$icChar = chr(32);
		}
        $icNewText = $icNewText.$icChar;
    }
    return $icNewText;

}


function Decrypt($icText) {
	$icChar = "";
    $icLen = strlen($icText);
    for($i=0;$i<$icLen;$i++) {

        $icChar = substr($icText, $i, 1);
		If((ord($icChar)>=192) && (ord($icChar)<=217)) {
			$icChar = chr(ord($icChar) - 127);
		} elseif ((ord($icChar)>=218) && (ord($icChar)<=243)) {
			$icChar = chr(ord($icChar) - 121);
		} elseif ((ord($icChar)>=244) && (ord($icChar)<=253)) {
			$icChar = chr(ord($icChar) - 196);
		} elseif (ord($icChar)==32) {
			$icChar = chr(32);
		}
        $icNewText = $icNewText.$icChar;
    }
    return $icNewText;
}

// Monitor
function getLastOrders() {

        $msg = "??";	
        //Recupera usuario
        if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser'])){
                $usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
                $usuarioId = $usuarioGames->getId();

                // Obtem últimos pedidos
                $sql  = "select vg.vg_id, vg.vg_data_inclusao, 
                                        sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos
                                from tb_dist_venda_games vg 
                                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                where vg.vg_ug_id=" . $usuarioId;
                $sql .=	" group by vg.vg_id, vg.vg_data_inclusao ";
                $sql .= " order by vg.vg_data_inclusao desc limit 1;";
                $rs_vendas = SQLexecuteQuery($sql);
		if(!$rs_vendas || pg_num_rows($rs_vendas) == 0) $msg = "Nenhuma venda encontrada.".PHP_EOL;
                else {
                        $rs_vendas_row = pg_fetch_array($rs_vendas);

                        $t_diff = (strtotime(date("Y-m-d H:i:s"))-strtotime($rs_vendas_row['vg_data_inclusao']));
                        $t_diff_mark1 = 60*60;
                                $s_mark1 = "Compras na última hora";
                        $t_diff_mark2 = 24*60*60;
                                $s_mark2 = "Compras no últimos dia";
                                $s_mark3 = "Sem compras no último dia";

                        $msg = "<span ".(($t_diff<$t_diff_mark1)?"style='background-color:#FF3300' title='".$s_mark1."'":(($t_diff<$t_diff_mark2)?"style='background-color:#FF9900' title='".$s_mark2."'":" title='".$s_mark3."'"))."><a href='/prepag2/dist_commerce/conta/lista_vendas.php'>Última compra</a>: <a href='/prepag2/dist_commerce/conta/pagto_compr_redirect.php?venda=".$rs_vendas_row['vg_id']." class='link_azul'>ID: ".formata_codigo_venda($rs_vendas_row['vg_id'])."</a> (".formata_data_ts($rs_vendas_row['vg_data_inclusao'], 0, true, false).") R$".number_format($rs_vendas_row['valor'], 2, ',','.')." Qtd: ".$rs_vendas_row['qtde_itens']." (Faz ".get_time_splitted_1($t_diff).")"."</span>";
                }
                $sql = "select ug_qtde_acessos from dist_usuarios_games where ug_id=$usuarioId";
                $qtde_acessos = getSingleValue($sql);
                $msg .= " (".$qtde_acessos." acesso".(($qtde_acessos>0)?"s":"").")";
        }
        return $msg;
}

// n seconds -> (days, hours, minutes, seconds)
function get_time_splitted_1($nsecs) {
        $days = floor($nsecs / 86400);
        $hours = floor(($nsecs / 3600) - ($days * 24));
        $minutes = floor(($nsecs / 60) - ($days * 1440) - ($hours * 60));
        $seconds = floor(($nsecs % 60));

        $sout = "";
        if($days>30) {
                $sout .= "mais de um mês";
        } else if($days>7) {
                $sout .= "mais de uma semana";
        } else {
                if($days>0)	$sout .= $days."d";
                if($hours>0)	$sout .= $hours."h";	// str_pad($hours, 2, "0", STR_PAD_LEFT)
                if($minutes>0)	$sout .= $minutes."m";	// str_pad($minutes, 2, "0", STR_PAD_LEFT)
                if($seconds>0)	$sout .= $seconds."s";	// str_pad($seconds, 2, "0", STR_PAD_LEFT)
        }

        return 	$sout;
}

function mostraCarrinho_pag($bprint){

        //Recupera carrinho do session
        $carrinho = $_SESSION['dist_carrinho'];
        $total_geral = 0;

        if(!$carrinho || count($carrinho) == 0){		
                if($bprint) {
?>			
                <table border="0" cellspacing="0" width="90%" height="200">
    <tr align="center" bgcolor="#FFFFFF">
      <td align="center" class="texto">Carrinho vázio no momento</td>
    </tr>
                </table>
<?php
                }
        } else {

                if($bprint) {
                ?>
                <table border="0" cellspacing="0" width="95%" align="center">
        <tr bgcolor="F0F0F0">
          <td class="texto" align="center" height="25"><b>Descrição</b>&nbsp;</td>
          <td class="texto" align="center" colspan="1"><b>Quantidade</b>&nbsp;</td>
          <td class="texto" align="center">&nbsp;</td>
          <td class="texto" align="center"><b>Unitário</b>&nbsp;</td>
          <td class="texto" align="center"><b>Total</b>&nbsp;</td>
          <td class="texto" align="center">&nbsp;</td>
        </tr>
                <?php
                }			
                foreach ($carrinho as $modeloId => $qtde){

                        $qtde = intval($qtde);
                        $rs = null;
                        $filtro['ogpm_ativo'] = 1;
                        $filtro['ogpm_id'] = $modeloId;
                        $filtro['com_produto'] = true;
                        $ret = ProdutoModelo::obter($filtro, null, $rs);
                        if($rs && pg_num_rows($rs) != 0){
                                $rs_row = pg_fetch_array($rs);
                                $total_geral += $rs_row['ogpm_valor'] * $qtde;
                                if($bprint) {
?>
        <tr>
          <td class="texto" height="25" width="150">
                &nbsp;&nbsp;<nobr>
                <?=$rs_row['ogp_nome']?>
                <?if($rs_row['ogpm_nome']!="") { echo " - ".$rs_row['ogpm_nome']; }?></nobr>
          </td>
          <td class="texto" align="center"><?=$qtde?></td>
          <td class="texto">&nbsp;</td>
          <td class="texto" align="center"><?=number_format($rs_row['ogpm_valor'], 2, ',', '.')?></td>
          <td class="texto" align="right"><?=number_format($rs_row['ogpm_valor']*$qtde, 2, ',', '.')?></td>
          <td class="texto" align="right"></td>
        </tr>
<?php
                                }
                        }
                }
                if($bprint) {
                ?>
        <tr bgcolor="F0F0F0">
          <td colspan="3">&nbsp;</td>
          <td class="texto" align="right" height="25"><b>Total</b>&nbsp;</td>
          <td class="texto" align="right"><b><?=number_format($total_geral, 2, ',', '.')?></b></td>
          <td>&nbsp;</td>
        </tr>
                </table>
                <?php
                }
        }

        return $total_geral;
}


	// Retorna true se o carrinho contem apenas produtos escolhidos para usar novas formas de pagamento: Habbo (16) e GPotato (31)
function bCarrinho_ApenasProdutosOK(){

        //Recupera carrinho do session
        $carrinho = $_SESSION['dist_carrinho'];

        if(!$carrinho || count($carrinho) == 0){
                $breturn = false;
        } else {
                foreach ($carrinho as $modeloId => $qtde) {
                        $rs = null;
                        $filtro['ogpm_ativo'] = 1;
                        $filtro['ogpm_id'] = $modeloId;
                        $filtro['com_produto'] = true;

                        $ret = ProdutoModelo::obter($filtro, null, $rs);
                        if($rs && pg_num_rows($rs) != 0){
                                $rs_row = pg_fetch_array($rs);
                                if($rs_row['ogp_opr_codigo']!=16 && $rs_row['ogp_opr_codigo']!=31 ) {
                                        $breturn = false;
                                        break;
                                }
                        }
                }
        }

        return $breturn;
}

function montaCesta_pag(){
        //teste de depósito from Drupal para não gerar erro de SQL
        if($_SESSION['drupal_deposit']=="1") {
		$sout = "item:Depósito DRUPAL: ".PHP_EOL;
		$sout .= "1".PHP_EOL;
		$sout .= "deposit".PHP_EOL;
		$sout .= "".(($_SESSION['drupal_deposit_amount']>0)?$_SESSION['drupal_deposit_amount']:"0").PHP_EOL;
                return $sout;
        }//end if($_SESSION['drupal_deposit']=="1")
        //Recupera carrinho do session
        $carrinho = $_SESSION['dist_carrinho'];

        if(!$carrinho || count($carrinho) == 0){		
		$sout = "Vazio".PHP_EOL;
        } else {
                $sout = "";
                foreach ($carrinho as $modeloId => $qtde){
                        if($modeloId != $GLOBALS["NO_HAVE"]){
                            $qtde = intval($qtde);
                            $rs = null;
                            $filtro['ogpm_ativo'] = 1;
                            $filtro['ogpm_id'] = $modeloId;
                            $filtro['com_produto'] = true;
                            $instProdutoModelo = new ProdutoModelo;
                            $ret = $instProdutoModelo->obter($filtro, null, $rs);
                            if($rs && pg_num_rows($rs) != 0){
                                    $rs_row = pg_fetch_array($rs);
                                    $sout .= "item:".$rs_row['ogp_nome'].(($rs_row['ogpm_nome']!="")?(" - ".$rs_row['ogpm_nome']):"").PHP_EOL; 
                                    $sout .= $qtde.PHP_EOL;
                                    $sout .= "pin".(($qtde>1)?"s":"").PHP_EOL;
                                    $sout .= (100*$rs_row['ogpm_valor']*$qtde).PHP_EOL;
                            }
                        }else{
                            foreach ($qtde as $codeProd => $vetor_valor) {
                                foreach ($vetor_valor as $valor => $quantidade) {
                                    $filtro['ogp_ativo'] = 1;
                                    $filtro['ogp_id'] = $codeProd;
                                    $filtro['opr'] = 1;
                                    $ret = (new Produto)->obtermelhorado($filtro, null, $rs);

                                    if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
                                    else{
                                        $rs_row = pg_fetch_array($rs);
                                        $sout .= "item:".$rs_row['ogp_nome']. " - " . $valor . PHP_EOL; 
                                        $sout .= $quantidade.PHP_EOL;
                                        $sout .= "pin".(($quantidade>1)?"s":"").PHP_EOL;
                                        $sout .= (100*$valor*$quantidade).PHP_EOL;
                                    }
                                    
                                }
                            }
                        }
                }
        }
        return $sout;
}

//  ================================================
function get_time_difference_formatted( $start, $end ) {
	$aret = get_time_difference( $start, $end );
	$s = "";
	if(is_array($aret)) {
		if($aret['days']>0)
			$s.= $aret['days']."d ";
		if($aret['hours']>0)
			$s.= $aret['hours']."h ";
		if($aret['minutes']>0)
			$s.= $aret['minutes']."m ";
//		if($aret['seconds']>0)
			$s.= $aret['seconds']."s ";
	} else {
		$s = "??? s";
	}
	return $s;
}

// -- 'M' - Money, 'E' - Money Express, 'LR' - Lanhouse Pré, 'LO' - Lanhouse Pós
function get_tipo_cliente_descricao($stipo) {
        switch ($stipo) {
                case "M":
                        $sout = "Money";
                        break;
                case "E":
                        $sout = "Money_Express";
                        break;
                case "LR":
                        $sout = "LH_Pré";
                        break;
                case "LO":
                        $sout = "LH_Pós";
                        break;
                default:
                        $sout = "???";
                        break;
        }
        return $sout;
}


// http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html
// Não está funcionando bem

//	ug_id	ug_login
//	468		"REINALDOLH2"
//	6		"GLAUCIAPJ"
//	3		"ODECIO"
//	17		"FABIO###"
$aIsLogin_pagamento_LH_testes = Array(
        468, 6, 3, 17
);

function getNVendasLH($idusuario){
		
        global $aIsLogin_pagamento_LH_testes;
        $qtde = 0;

        // Se for usuário de testes -> sem restrições
        if(in_array($idusuario, $aIsLogin_pagamento_LH_testes)) {
                return $qtde;   	
        }

        //SQL
        $sql = "select count(*) as qtde from tb_dist_venda_games ";
        $sql .= " where vg_ug_id = " . SQLaddFields($idusuario, "");
        $sql .= " and vg_data_inclusao>='".date('Y-m-d H:i:s', strtotime("-1 days"))."' and vg_ultimo_status=5 ";
        $sql .= " and (vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PIX_NUMERIC']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']."); ";

//echo "<!-- sql: $sql\n -->";
        $rs = SQLexecuteQuery($sql);
        if($rs && pg_num_rows($rs) > 0){
                $rs_row = pg_fetch_array($rs);
                $qtde = $rs_row['qtde'];
        }			
//echo "<!-- qtde: ".$rs_row['qtde']."\n-->";
//echo $idusuario."-".$rs_row['qtde']."";

        // for Debug
		$mensagem = "In getNVendasLH(): ".PHP_EOL.
				"qtde: ".$rs_row['qtde'].PHP_EOL.
                                "idusuario: ".$idusuario."";
//					$sql.PHP_EOL.

        gravaLog_BloqueioPagtoOnline($mensagem);

        return $qtde;   	

}

function getNVendasSemanaisLH($idusuario){
        global $aIsLogin_pagamento_LH_testes;
        $qtde = 0;

        // Se for usuário de testes -> sem restrições
        if(in_array($idusuario, $aIsLogin_pagamento_LH_testes)) {
                return $qtde;   	
        }

        //SQL
        $sql = "select count(*) as qtde from tb_dist_venda_games ";
        $sql .= " where vg_ug_id = " . SQLaddFields($idusuario, "");
        $sql .= " and vg_data_inclusao>='".date('Y-m-d H:i:s', strtotime("-7 days"))."' and vg_ultimo_status=5 ";
        $sql .= " and (vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PIX_NUMERIC']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']."); ";

//echo "<!-- sql: $sql\n -->";
        $rs = SQLexecuteQuery($sql);
        if($rs && pg_num_rows($rs) > 0){
                $rs_row = pg_fetch_array($rs);
                $qtde = $rs_row['qtde'];
        }			
//echo "<!-- qtde: ".$rs_row['qtde']."\n-->";
//echo $idusuario."-".$rs_row['qtde']."";

        // for Debug
		$mensagem = "In getNVendasSemanaisLH(): ".PHP_EOL.
				"qtde: ".$rs_row['qtde'].PHP_EOL.
                                "idusuario: ".$idusuario."";
//					$sql.PHP_EOL.

        gravaLog_BloqueioPagtoOnline($mensagem);

        return $qtde;   	

}

function getVendasLHTotalDiarioOnline($idusuario){
	
		if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
			//return 10000;
		}
	
        global $aIsLogin_pagamento_LH_testes;
        $total = 0;

        // Se for usuário de testes -> sem restrições
        if(in_array($idusuario, $aIsLogin_pagamento_LH_testes)) {
                return $total;   	
        }

        //SQL
        $sql = "select sum(vg_pagto_valor_pago) as total from tb_dist_venda_games ";
        $sql .= " where vg_ug_id = " . SQLaddFields($idusuario, "");
        $sql .= " and vg_data_inclusao>='".date('Y-m-d H:i:s', strtotime("-1 days"))."' ";	
        $sql .= " and (vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PIX_NUMERIC']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC'].") and vg_ultimo_status=5 ";
        $rs = SQLexecuteQuery($sql);
        if($rs && pg_num_rows($rs) > 0){
                $rs_row = pg_fetch_array($rs);
                $total = ($rs_row['total'])?$rs_row['total']:0;
        }			

        // for Debug
		$mensagem = "In getVendasLHTotalDiarioOnline(): ".PHP_EOL.
				"total: ".$total.PHP_EOL.
                                "idusuario: ".$idusuario."";
        gravaLog_BloqueioPagtoOnline($mensagem);

        return $total;   	

}

function getVendasLHTotalSemanalOnline($idusuario){
        global $aIsLogin_pagamento_LH_testes;
        $total = 0;

        // Se for usuário de testes -> sem restrições
        if(in_array($idusuario, $aIsLogin_pagamento_LH_testes)) {
                return $total;   	
        }

        //SQL
        $sql = "select sum(vg_pagto_valor_pago) as total from tb_dist_venda_games ";
        $sql .= " where vg_ug_id = " . SQLaddFields($idusuario, "");
        $sql .= " and vg_data_inclusao>='".date('Y-m-d H:i:s', strtotime("-7 days"))."' ";	
        $sql .= " and (vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PIX_NUMERIC']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC'].") and vg_ultimo_status=5 ";

        $rs = SQLexecuteQuery($sql);
        if($rs && pg_num_rows($rs) > 0){
                $rs_row = pg_fetch_array($rs);
                $total = ($rs_row['total'])?$rs_row['total']:0;
        }			

        // for Debug
		$mensagem = "In getVendasLHTotalSemanalOnline(): ".PHP_EOL.
				"total: ".$total.PHP_EOL.
                                "idusuario: ".$idusuario."";
        gravaLog_BloqueioPagtoOnline($mensagem);

        return $total;   	

}


function getVendasLHTotalDiarioBoletos($idusuario){
        global $aIsLogin_pagamento_LH_testes;
        $total = 0;

        // Se for usuário de testes -> sem restrições
        if(in_array($idusuario, $aIsLogin_pagamento_LH_testes)) {
                return $total;   	
        }

        //SQL
	$sql = "select sum(bbg_valor) as total from tb_dist_venda_games INNER JOIN dist_boleto_bancario_games on (bbg_vg_id = vg_id) ";
        $sql .= " where vg_ug_id = " . SQLaddFields($idusuario, "");
	$sql .= " and bbg_ug_id = vg_ug_id ";	
        $sql .= " and vg_data_inclusao>='".date('Y-m-d H:i:s', strtotime("-1 days"))."' ";	
        $sql .= " and vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'];
        $rs = SQLexecuteQuery($sql);
        if($rs && pg_num_rows($rs) > 0){
                $rs_row = pg_fetch_array($rs);
                $total = ($rs_row['total'])?$rs_row['total']:0;
        }			

        // for Debug
	$mensagem = "In getVendasLHTotalDiarioOnline(): ".PHP_EOL.
				"total: ".$total.PHP_EOL.
				"idusuario: ".$idusuario.PHP_EOL;

        gravaLog_BloqueioPagtoOnline($mensagem);

        return $total;

}

function getVendasLHTotalSemanalBoletos($idusuario){
        global $aIsLogin_pagamento_LH_testes;
        $total = 0;

        // Se for usuário de testes -> sem restrições
        if(in_array($idusuario, $aIsLogin_pagamento_LH_testes)) {
                return $total;   	
        }

        //SQL
	$sql = "select sum(bbg_valor) as total from tb_dist_venda_games INNER JOIN dist_boleto_bancario_games on (bbg_vg_id = vg_id) ";
        $sql .= " where vg_ug_id = " . SQLaddFields($idusuario, "");
	$sql .= " and bbg_ug_id = vg_ug_id ";	
        $sql .= " and vg_data_inclusao>='".date('Y-m-d H:i:s', strtotime("-7 days"))."' ";	
        $sql .= " and vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'];
        $rs = SQLexecuteQuery($sql);
        if($rs && pg_num_rows($rs) > 0){
                $rs_row = pg_fetch_array($rs);
                $total = ($rs_row['total'])?$rs_row['total']:0;
        }			

        // for Debug
	$mensagem = "In getVendasLHTotalSemanalOnline(): ".PHP_EOL.
				"total: ".$total.PHP_EOL.
				"idusuario: ".$idusuario.PHP_EOL;

        gravaLog_BloqueioPagtoOnline($mensagem);

        return $total;

}



function get_newOrderID() {
	$bfound = true;
	$ntries = 0;
	$orderId = "";

//	$orderId = 	date("YmdHis").str_pad(rand(0,99999999), 8, "0", STR_PAD_LEFT);
	$orderId = 	date("YmdHis").str_pad(rand(0,999), 3, "0", STR_PAD_LEFT);
//	return $orderId;

	do {

//		$orderId = 	"2003120408301545872781";
//		$orderId = 	date("YmdHis").str_pad(rand(0,99999999), 8, "0", STR_PAD_LEFT);

		$sql = "SELECT count(*) as n from tb_pag_compras where numcompra='".$orderId."'";

//echo "<br>".$sql."<br>";
//		$rsCompra = $conn->Execute($sql) or die("Erro 22");
		$ret = SQLexecuteQuery($sql);
		if(!$ret) {
			echo "Erro ao recuperar transação de pagamento.".PHP_EOL;
			die("Stop");
		} else {
			$pgresult = pg_fetch_array($ret);
			$bfound = (($pgresult['n']==0)?true:false);
		}

//		$orderId = 	date("YmdHis").str_pad(rand(0,99999999), 8, "0", STR_PAD_LEFT);
		$orderId = 	date("YmdHis").str_pad(rand(0,999), 3, "0", STR_PAD_LEFT);
		$ntries++;
	} while(!$bfound && $ntries<10);

	// tried too much, something is wrong
	if($ntries>=10) {
		$orderId = "????";
		$bfound = false;
	}

	return $orderId;
}

function b_IsPagtoOnline($iforma) {
        global $FORMAS_PAGAMENTO, $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC, $PAGAMENTO_BANCO_EPP_ONLINE, $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC, $PAGAMENTO_PIX_NUMERIC;
        global $b_reynaldo;
        if( 
                ($iforma==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || 
                ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || 
                ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) || 
                ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) || 
                ($iforma==$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC) || 
                ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_PIX']) || 
                ($iforma==$PAGAMENTO_PIX_NUMERIC) || 
//		($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE']) || 
//		($iforma==$PAGAMENTO_HIPAY_ONLINE_NUMERIC) || 
//		($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']) || 
//		($iforma==$PAGAMENTO_PAYPAL_ONLINE_NUMERIC) || 
                ($iforma==$PAGAMENTO_BANCO_EPP_ONLINE) || 
                ($iforma==$PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC) 
                ) {
                return true;
        } 
        return false;
}

function getTaxaPagtoOnline($iforma, $valor) {
        global $FORMAS_PAGAMENTO, $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC;
        global $BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL, $BRADESCO_DEBITO_EM_CONTA_TAXA_ADICIONAL, 
                        $BANCO_DO_BRASIL_TAXA_DE_SERVICO, $BANCO_ITAU_TAXA_DE_SERVICO;
        global $RISCO_LANS_PRE_VALOR_MIN_PARA_TAXA;

//echo "RISCO_LANS_PRE_VALOR_MIN_PARA_TAXA: ".(1.0*$RISCO_LANS_PRE_VALOR_MIN_PARA_TAXA)."<br>";
//echo "valor: ".(1.0*$valor)."<br>";
        $taxa = 0;
        // Por enquanto só aceitamos para pagamentos para LHs Pre acima de R$60,00 => taxa zero
        if((1.0*$valor)>=(1.0*$RISCO_LANS_PRE_VALOR_MIN_PARA_TAXA)) {
                $taxa = 0;
//echo "OK<br>";
        } else {
                switch($iforma) {
                        case $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
                                $taxa = $BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL;
                                break;
                        case $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
                                $taxa = $BRADESCO_DEBITO_EM_CONTA_TAXA_ADICIONAL;
                                break;
                        case $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
                                $taxa = $BANCO_DO_BRASIL_TAXA_DE_SERVICO;
                                break;
                        case $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']:
                        case $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:
                                $taxa = $BANCO_ITAU_TAXA_DE_SERVICO;
                                break;
                        // PIX
                        case $GLOBALS['PAGAMENTO_PIX_NUMERIC']:
                                $taxa = $GLOBALS['PAGAMENTO_PIX_TAXA'];
                                break;
                }
        }
//echo "BANCO_ITAU_TAXA_DE_SERVICO: ".$BANCO_ITAU_TAXA_DE_SERVICO."<br>";
//echo "taxa em getTaxaPagtoOnline('$iforma', $valor): ".$taxa."<br>";
        return $taxa;
}


function getBcoCodigo($iforma) {
        global $FORMAS_PAGAMENTO, $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC;
        global $BOLETO_MONEY_BRADESCO_COD_BANCO, $BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO, $BOLETO_MONEY_BANCO_ITAU_COD_BANCO;

        $bco_codigo = "000";
        switch($iforma) {
                case $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
                        $bco_codigo = $BOLETO_MONEY_BRADESCO_COD_BANCO;
                        break;
                case $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
                        $bco_codigo = $BOLETO_MONEY_BRADESCO_COD_BANCO;
                        break;
                case $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
                        $bco_codigo = $BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO;
                        break;
                case $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']:
                case $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:
                        $bco_codigo = $BOLETO_MONEY_BANCO_ITAU_COD_BANCO;
                        break;

                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']:
                case $GLOBALS['PAGAMENTO_PIX_NUMERIC']:
                        $bco_codigo = $GLOBALS['PAGAMENTO_PIX_COD_BANCO'];
                        break;
        }
        return $bco_codigo;
}

// $vg_pagto_tipo	- is numeric (para Itau = PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC)
// $iforma			- is char(1) (para itau = 'A')
function get_iforma($vg_pagto_tipo) {
	global $FORMAS_PAGAMENTO, $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC,$PAGAMENTO_PIX_NUMERIC;

	$iforma = (($vg_pagto_tipo==$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC)?$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']:(($vg_pagto_tipo==$PAGAMENTO_PIX_NUMERIC)?$FORMAS_PAGAMENTO['PAGAMENTO_PIX']:((string)$vg_pagto_tipo))); 

	return $iforma;
}

function gravaLog_TMP($mensagem){
        global  $raiz_do_projeto;
        //Arquivo
        $file =  $raiz_do_projeto . 'log/log_pagamento_TMP_dist.txt';

        //Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . PHP_EOL . $mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}
        
function gravaLog_Pagto_Insert($mensagem){
        global  $raiz_do_projeto;

        //Arquivo
        $file =  $raiz_do_projeto . 'log/log_Pagto_Insert.txt';

        //Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . PHP_EOL . $mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_Login($mensagem, $forced_save = false){
    
        global  $raiz_do_projeto;

        // Desativa o registro de Sucesso/Erro de logins
        if(!$forced_save) return;

        //Arquivo
        $file =  $raiz_do_projeto . 'log/log_dist_login.txt';

        //Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . " (".$_SERVER['REMOTE_ADDR'].")".PHP_EOL . $mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_Captura($mensagem){
    
        global  $raiz_do_projeto;

        //Arquivo
        $file = $raiz_do_projeto . 'log/log_captura.txt';

        //Mensagem
 	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . PHP_EOL . $mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

// Passar para /backoffice/web/bkov2_prepag/pagamento/includes/inc_Pagamentos.php
function isFormaPagtoOnline($iforma) {
	global $FORMAS_PAGAMENTO, $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC;

        if(($iforma==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) || ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) || ($iforma==$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC)) {
		return true;
	} else {
		return false;
	}

	return false;	// just to be sure...
}

// $iNumericType 
//	0 - any char type
//	1 - only numbers
//	2 - only chars
//	3 - alphanumeric
function is_csv_numeric_global($list, $iNumericType = 1) {
        $list1 = str_replace(" ", "", $list);
        $list1 = str_replace("\t", "", $list);
        $alist = explode(",", $list1);
        $bret = true;
        foreach($alist as $key => $val) {
                switch($iNumericType) {
                        case 1: 
                                $bret = is_numeric($val);
                                break;
                        case 2: 
                                $bret = ctype_alpha($val);
                                break;
                        case 3: 
                                $bret = ctype_alnum($val);
                                break;
                        default: 
                                $bret = true;
                                break;
                }
                if(!$bret) {
                        break;
                }
        }
        return $bret;
}
	
//função que retorna o vídeo vigente
function BuscarVideo() {
        $sql = "SELECT * 
                        FROM dist_videos 
                        WHERE dv_ativo = '1'
                                AND dv_data_inicio <= NOW() 
                                AND (dv_data_fim + interval '1 day')   >= NOW()
                        ORDER BY dv_data_cadastro";
        $rs_videos = SQLexecuteQuery($sql);
        if(!$rs_videos) {
                return null;
        } else {
                $i = 0;
                while ($rs_videos_row = pg_fetch_array($rs_videos)) {
                        $retorno[$i]['url']			= $rs_videos_row['dv_url'];							
                        $retorno[$i]['descricao']	= $rs_videos_row['dv_descricao'];
                        $i++;
                }//end while
                $i = rand(0, (count($retorno)-1));
                $aux_retorno = "<div style='font-size:12px;font-weight:bold;width:260px;text-align:left;'>".$retorno[$i]['descricao']."</div><iframe width='260' height='162' src='".$retorno[$i]['url']."' frameborder='0' allowfullscreen></iframe>";
                return $aux_retorno;
        }//end else if(!$rs_videos || pg_num_rows($rs_videos) == 0) 
}//end function BuscarVideo()

//função que retorna GLOBALS['_SERVER']['REMOTE_ADDR']
function retorna_remote_addr() {
        if (isset($GLOBALS['_SERVER'])) {
                $ip = $GLOBALS['_SERVER']['REMOTE_ADDR'];
   } else {
                $ip = getenv('REMOTE_ADDR');
   }
   return $ip;
}

//função que retorna GLOBALS['_SERVER']['HTTP_CLIENT_IP']
function retorna_http_client_ip() {
        if (isset($GLOBALS['_SERVER'])) {
                $ip = $GLOBALS['_SERVER']['HTTP_CLIENT_IP'];
   } else {
                $ip = getenv('HTTP_CLIENT_IP');
   }
   return $ip;
}

//função que retorna GLOBALS['_SERVER']['HTTP_X_FORWARDED_FOR']
function retorna_http_x_forwarded_for() {
        if (isset($GLOBALS['_SERVER'])) {
                $ip = $GLOBALS['_SERVER']['HTTP_X_FORWARDED_FOR'];
   } else {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
   }
   return $ip;
}

//função que retrono o IP de acesso
function retorna_ip_acesso() {
        if (isset($GLOBALS['_SERVER'])) {
                if (isset($GLOBALS['_SERVER']['REMOTE_ADDR'])) {
                        $ip = $GLOBALS['_SERVER']['REMOTE_ADDR'];
                } elseif (isset($GLOBALS['_SERVER']['HTTP_X_FORWARDED_FOR'])) {
                        $ip = $GLOBALS['_SERVER']['HTTP_X_FORWARDED_FOR'];
                } else {
                        $ip = $GLOBALS['_SERVER']['HTTP_CLIENT_IP'];
                }
   }//end if (isset($GLOBALS['_SERVER'])) 
   else {
                if (getenv('REMOTE_ADDR')) {
                        $ip = getenv('REMOTE_ADDR');
                } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
                        $ip = getenv('HTTP_X_FORWARDED_FOR');
                } else {
                        $ip = getenv('HTTP_CLIENT_IP');
                }
   }//end else if (isset($GLOBALS['_SERVER']))
   return $ip;
}//end function retorna_ip_acesso()

	
//função que retorna o IP ativo
function BuscarIPAtivo($ug_id) {
        $sql = "SELECT * 
                        FROM dist_ip 
                        WHERE di_ativo = '1'
                                AND ug_id = $ug_id
                                AND di_ip != ''
                        ORDER BY di_data_ativacao";
        $rs_busca_ip = SQLexecuteQuery($sql);
        if(!$rs_busca_ip) {
                return null;
        } else {
                $rs_busca_ip_row = pg_fetch_array($rs_busca_ip);
                $retorno = $rs_busca_ip_row['di_ip'];							
                return $retorno;
        }//end else if(!$rs_busca_ip || pg_num_rows($rs_busca_ip) == 0) 
}//end function BuscarIPAtivo()

	//função que desativa IP
function DesativaIP($ug_id) {
        $sql = "UPDATE dist_ip 
                        SET di_ativo = '0' , di_data_desativacao=NOW()
                        WHERE di_ativo = '1'
                                AND ug_id = $ug_id";
        $rs_desativa_ip = SQLexecuteQuery($sql);
        if(!$rs_desativa_ip) {
                return false;
        } else {
                return true;
        }//end else if(!$rs_desativa_ip) 
}//end function DesativaIP($ug_id)

//função que Ativa IP
function AtivaIP($ug_id) {
        if(BuscarIPAtivo($ug_id)!=retorna_ip_acesso()) {
                DesativaIP($ug_id);
                $sql = "INSERT 
                                INTO dist_ip (
                                        ug_id,
                                        di_ativo,
                                        di_ip,
                                        di_data_ativacao,
                                        di_remote_addr,
                                        di_http_client_ip,
                                        di_http_x_forwarded_for
                                )
                                VALUES (
                                        $ug_id,
                                        1,
                                        '".retorna_ip_acesso()."',
                                        NOW(),
                                        '".retorna_remote_addr()."',
                                        '".retorna_http_client_ip()."',
                                        '".retorna_http_x_forwarded_for()."'
                                )";
                $rs_ativa_ip = SQLexecuteQuery($sql);
                if(!$rs_ativa_ip) {
                        return false;
                } else {
                        return true;
                }//end else if(!$rs_ativa_ip) 
        }//end if(DesativaIP($ug_id))
        else {
                return false;
        }//end else if(DesativaIP($ug_id))
}//end function AtivaIP($ug_id)


// função que monta o ranking
function exibe_ranking() {
	$cReturn = "";
	$aux_session = unserialize($_SESSION['dist_usuarioGames_ser']);
	//echo "<pre>".print_r($aux_session,true)."</pre>";

	// busca as promoções vigentes
	$query = "select promolh_id,
					promolh_titulo_tabela,
					promolh_regulamento,
					promolh_banner,
					promolh_link_download
				from promocoes_lanhouses 
				where promolh_data_inicio <= NOW() 
					  and (promolh_data_fim + interval '1 day') >= NOW()
				order by promolh_id";
//echo "<!-- query: ".$query. "--><br>".PHP_EOL;

	$rs_query = SQLexecuteQUERY($query);

	// Obter apenas a lista de promoções vigentes
	$a_promolh_id = array();
	$i = 0;
	while ($promocoes_info = pg_fetch_array($rs_query)) {
		$a_promolh_id[$i]['ID']						= $promocoes_info['promolh_id'];
		$a_promolh_id[$i]['titulo_tabela']			= $promocoes_info['promolh_titulo_tabela'];
		$a_promolh_id[$i]['promolh_regulamento']	= $promocoes_info['promolh_regulamento'];
		$a_promolh_id[$i]['promolh_banner']			= $promocoes_info['promolh_banner'];
		$a_promolh_id[$i]['promolh_link_download']	= $promocoes_info['promolh_link_download'];
		$i++;
	}//end while

	//echo "count(a_promolh_id): ".count($a_promolh_id)." Promoções cadastradas vigentes".PHP_EOL;
	//echo "<pre>".print_r($a_promolh_id,true)."</pre>";

	for($i=0;$i<count($a_promolh_id);$i++) {

		$query = "SELECT 
						promolh_id,
						promolh_r_rank, 
						plr.ug_id,
						(CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome END) as ug_nome,
						ug_estado,
						promolh_r_valor
				FROM promocoes_lanhouses_rank plr
					INNER JOIN dist_usuarios_games ug ON (plr.ug_id = ug.ug_id)
				WHERE promolh_id = ".$a_promolh_id[$i]['ID']."
					and to_char(promolh_r_data_processamento,'YYYYMMDDHH24') = (
													select max(to_char(promolh_r_data_processamento,'YYYYMMDDHH24'))
													from promocoes_lanhouses_rank
													)
				order by promolh_r_rank";
		//echo "query: ".$query.PHP_EOL;

		$rs_query = SQLexecuteQuery($query);
		$cReturn .="<br><table>";
		$cReturn .="<tr style='color:blue;font-size:15px;font-weight: bold;'><td colspan='2' align='left'>".$a_promolh_id[$i]['titulo_tabela']."</td></tr>";
		$cReturn .="<tr><td valign='top'><table style='font-family:arial;'>";
		$cReturn .="<tr style='font-size:11px;font-weight: bold;'><td colspan='4'>Acompanhe os primeiros colocados</td></tr>";
		$cReturn .="<tr style='font-size:11px;font-weight: bold;'><td></td><td></td><td></td><td><nobr>Voc&ecirc; est&aacute; a:</nobr></td></tr>";
		while ($promocoes_info = pg_fetch_array($rs_query)) {
			//echo " dentro while".PHP_EOL;
			if(($promocoes_info['promolh_r_rank'] % 2) == 1) {
				$aux_bgcolor='#E3F0FF';
			}
			else {
				$aux_bgcolor='#FFFFFF';
			}
			$cReturn .="<tr style='font-size:10px;background-color:".$aux_bgcolor.";'><td align='center'><nobr>".$promocoes_info['promolh_r_rank'].chr(170)."&nbsp;&nbsp;</nobr>";
			$cReturn .="</td><td align='center'><nobr>".$promocoes_info['ug_estado']."&nbsp;&nbsp;</nobr>";
			$cReturn .="</td><td><nobr>".substr($promocoes_info['ug_nome'],0,30)."</nobr>";
			//echo "</td><td>".($promocoes_info['promolh_r_rank'] % 2);
			if ($aux_session->ug_id == $promocoes_info['ug_id']) {
				if(!empty($vlr_anterior)) {
					if (($vlr_anterior-$promocoes_info['promolh_r_valor'])==0){
						$cReturn .="</td><td align='center'><nobr>Empatada com ".($promocoes_info['promolh_r_rank']-1).chr(170)."&nbsp;</nobr>";
					}
					else {
						$cReturn .="</td><td align='center'><nobr>R$ ".number_format(($vlr_anterior-$promocoes_info['promolh_r_valor']), 2, ',', '.')." da ".($promocoes_info['promolh_r_rank']-1).chr(170)."&nbsp;</nobr>";
					}
				}
				else {
					$cReturn .="</td><td align='center'><nobr>Voc&ecirc; &eacute; o 1".chr(170)."&nbsp;</nobr>";
				}
			}//end if $aux_session
			else {
				$cReturn .="</td><td>";
			}
			$vlr_anterior = $promocoes_info['promolh_r_valor'];
			$cReturn .="</td></tr>";
			//echo "<pre>".print_r($promocoes_info,true)."</pre>";
		}
		$cReturn .="<tr style='font-size:11px;color:red;font-family:verdana;'><td colspan='4' align='left'>Se voc&ecirc; n&atilde;o est&aacute; na lista acima &eacute; porque sua coloca&ccedil;&atilde;o est&aacute; abaixo da 20".chr(170).".</td></tr>";
		$cReturn .="</table></td>";
		$pasta = "http://".$_SERVER['SERVER_NAME']."/prepag2/dist_commerce/images/promocoes/";
		$pastadwl = "http://".$_SERVER['SERVER_NAME']."/prepag2/dist_commerce/images/";
		$cReturn .="<td style='font-size:10px;font-family:arial;'><center><img src='".$pasta.$a_promolh_id[$i]['promolh_banner']."' alt='Banner desta Promo&ccedil;&atilde;o' border='0' align='absmiddle' /><br>";
		$cReturn .="<div style='width: 92%;' align='left'>".$a_promolh_id[$i]['promolh_regulamento']."<hr></div><br>";
		$cReturn .="<a href='".$a_promolh_id[$i]['promolh_link_download']."' target='_blank'><img src='".$pastadwl."bt_download.jpg' alt='Banner desta Promo&ccedil;&atilde;o' border='0' align='absmiddle' /></a></center></td>";
		$cReturn .="</tr></table><br><hr>";
		$vlr_anterior = 0;
	}//end for
	return $cReturn;
}// end function

function getSingleValue($sql) {

	$ret = null;
	 
//echo "<!-- sql: $sql\n -->";
	$rs = SQLexecuteQuery($sql);
	if($rs && pg_num_rows($rs) > 0){
		$rs_row = pg_fetch_array($rs);
		 $ret = $rs_row[0];
	}			
//echo "<!-- resultado (getValue): " & $ret & "\n-->";
		
	return $ret;   	
}

function verificaValorVazioArray($array){
    foreach ($array as $value){
        if(empty($value)){
            return false;
        }
    }
    return true;
}
function verifica_cepEx2($cep, $blComTraco = true){

	if($blComTraco){
		return preg_match("/^[0-9]{5}-[0-9]{3}$/", $cep);
	}else{
		return preg_match("/^[0-9]{8}$/", $cep);
	}
	
}

function verifica_telEx2($tel, $blComTraco = true){

	if($blComTraco){
		return preg_match("/^[0-9]{4,5}-[0-9]{4}$/", $tel);
	}else{
		return preg_match("/^[0-9]{8,9}$/", $tel);
	}
	
}

function verifica_data2($data) {
	$aux = $data;
	$tam = strlen($aux);
		if($tam < 10)
		{ return 0; }
		else
		{
				$bar1 = substr($aux,2,1);
				$bar2 = substr($aux,5,1);
					if(ord($bar1) != 47 || ord($bar2) != 47)
					{ return 0; }
					else
					{
						$dia = substr($aux,0,2); 
						for ($x = 1 ; $x <= strlen($dia) ; $x++)
						{
							$pos = substr($dia,$x-1,1);
							if(ord($pos) >= 48 && ord($pos) <= 57)
							{ $alerta = 0; }
							else
							{ $alerta = 1; break;}
						}							
								if($alerta == 1) 
								{ return 0; }
								else
								{
									$mes = substr($aux,3,2); 
									for ($x = 1 ; $x <= strlen($mes) ; $x++)
									{
										$pos = substr($mes,$x-1,1);
										if(ord($pos) >= 48 && ord($pos) <= 57)
										{ $alerta = 0; }
										else
										{ $alerta = 1; break;}							
									}
										
										if($alerta == 1) 
										{ return  0; }
										else
										{
											$ano = substr($aux,6,4); 
											for ($x = 1 ; $x <= strlen($ano) ; $x++)
											{
												$pos = substr($ano,$x-1,1);
												if(ord($pos) >= 48 && ord($pos) <= 57)
												{ $alerta = 0; }
												else
												{ $alerta = 1; break;}							
											}
											
											if($alerta == 1) 
											{ return  0; }
											else	
											{ 
												if($mes > 12 || $dia > 31)
												{ return 0; }
												else
												{									
													if ((($ano % 4) == 0 and ($ano % 100) != 0) or ($ano % 400) == 0)
														{ $bissexto = 1; }
													else 
														{ $bissexto = 0; }
													
													if($bissexto == 0)
													{
														if(($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && ($dia > 30))
														{ return 0; }
														else
														{
															if($mes == 2 && $dia > 28) 
															{ return 0; }
															else
															{ return 1; }														
														}
													}
													if($bissexto == 1)
													{
														if(($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && ($dia > 30))
														{ return 0; }
														else
														{
															if($mes == 2 && $dia > 29) 
															{ return 0; }
															else
															{ return 1; }
														}
													}													
												}
											}											
										}															
								}																				
					}																		
		}			
}

function verifica_email2($email) {
	$aux = $email;
	$first = substr($aux,0,1);
	$last = substr($aux,strlen($aux)-1,1);
		if(ord($first) == 64 || ord($last) == 64)
		{ return 0; }
		else
		{
			$contador = 0;	
			for ($x = 1 ; $x <= strlen($aux) ; $x++)
			{
				$pos = substr($aux,$x-1,1);
				if(ord($pos) == 64)
				{ $alerta = 1; break; }
				else
				{ $alerta = 0; }
				$contador++;
			}
																							
				if($alerta == 0)
				{ return 0; }
				else
				{
					$ponto = substr($aux,$contador,1); 
					if(ord($ponto) == 46)
					{ return 0; }
					else
					{ return 1; }
				}
		}
}

function verificaCNPJ2($string) {
	$RecebeCNPJ = $string;
	
	if(strlen($RecebeCNPJ) != 14 || $RecebeCNPJ == "00000000000000")
		return 0;
	else
	{
		for($i = 1 ; $i <= 14 ; $i++)
			$Numero[$i] = intval(substr($RecebeCNPJ, $i - 1, 1));		
		
		$soma = 0;
		for($i = 1 ; $i <= 12 ; $i++)
		{
			if($i == 1) $j = 5;

			$soma += $Numero[$i] * $j;
			$j--;

			if($j == 1) $j = 9;
		}
		
//		$soma = $Numero[1] * 5 + $Numero[2] * 4 + $Numero[3] * 3 + $Numero[4] * 2 + $Numero[5] * 9 + $Numero[6] * 8 + $Numero[7] * 7 + $Numero[8] * 6 + $Numero[9] * 5 + $Numero[10] * 4 + $Numero[11] * 3 + $Numero[12] * 2; 
		
		$soma = $soma - (11 * (intval($soma / 11)));
		
		if($soma == 0 || $soma == 1)
			$resultado1 = 0;
		else
			$resultado1 = 11 - $soma;
	
		if($resultado1 == $Numero[13])
		{

			$soma = 0;
			for($i = 1 ; $i <= 13 ; $i++)
			{
				if($i == 1) $j = 6;
	
				$soma += $Numero[$i] * $j;
				$j--;
	
				if($j == 1) $j = 9;
			}

//			$soma = $Numero[1] * 6 + $Numero[2] * 5 + $Numero[3] * 4 + $Numero[4] * 3 + $Numero[5] * 2 + $Numero[6] * 9 + $Numero[7] * 8 + $Numero[8] * 7 + $Numero[9] * 6 + $Numero[10] * 5 + $Numero[11] * 4 + $Numero[12] * 3 + $Numero[13] * 2;
		
			$soma = $soma - (11 * (intval($soma / 11)));

			if($soma == 0 || $soma==1)
				$resultado2 = 0;
			else
				$resultado2 = 11 - $soma;

			if ($resultado2 == $Numero[14])
				return 1;
			else
				return 0;
		}
		else
			return 0;

	}
}

function verificaCPF_2($cpf) {

	$RecebeCPF=$cpf;
	
	$RecebeCPF = str_replace(".", "", $RecebeCPF);
	$RecebeCPF = str_replace("-", "", $RecebeCPF);

	return verificaCPF2($RecebeCPF);	
}

function verificaCPF2a($cpf) {

	$RecebeCPF=$cpf;

		if (strlen($RecebeCPF)!=11)
		{ return 0; }
		else
		if ($RecebeCPF=="00000000000" || $RecebeCPF=="11111111111")
		{ return 0; }
		else
		{
			$Numero[1]=intval(substr($RecebeCPF,1-1,1));
			$Numero[2]=intval(substr($RecebeCPF,2-1,1));
			$Numero[3]=intval(substr($RecebeCPF,3-1,1));
			$Numero[4]=intval(substr($RecebeCPF,4-1,1));
			$Numero[5]=intval(substr($RecebeCPF,5-1,1));
			$Numero[6]=intval(substr($RecebeCPF,6-1,1));
			$Numero[7]=intval(substr($RecebeCPF,7-1,1));
			$Numero[8]=intval(substr($RecebeCPF,8-1,1));
			$Numero[9]=intval(substr($RecebeCPF,9-1,1));
			$Numero[10]=intval(substr($RecebeCPF,10-1,1));
			$Numero[11]=intval(substr($RecebeCPF,11-1,1));
			
			$soma=10*$Numero[1]+9*$Numero[2]+8*$Numero[3]+7*$Numero[4]+6*$Numero[5]+5*
			$Numero[6]+4*$Numero[7]+3*$Numero[8]+2*$Numero[9];
			$soma=$soma-(11*(intval($soma/11)));
			
			if ($soma==0 || $soma==1)
			{ $resultado1=0; }
			else
			{ $resultado1=11-$soma; }
		
			if ($resultado1==$Numero[10])
			{
				$soma=$Numero[1]*11+$Numero[2]*10+$Numero[3]*9+$Numero[4]*8+$Numero[5]*7+$Numero[6]*6+$Numero[7]*5+
				$Numero[8]*4+$Numero[9]*3+$Numero[10]*2;
				$soma=$soma-(11*(intval($soma/11)));
			
				if ($soma==0 || $soma==1)
				{ $resultado2=0; }
				else
				{ $resultado2=11-$soma; }
				if ($resultado2==$Numero[11])
				{ return TRUE;}
				else
				{ return 0; }
			}
			else
			{ return 0; }
	 }
}

function fix_name_cpf($str){
    $name = explode(' ', strtolower($str));
    foreach( $name as $k=>$n ){
        if(strlen($n)<=2)
            continue;
        
       $name[$k] = ucfirst($n);
    }
    return implode(' ', $name);
}
?>
