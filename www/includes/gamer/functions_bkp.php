<?php
require_once RAIZ_DO_PROJETO . "includes/gamer/functions_pagto.php";
require_once RAIZ_DO_PROJETO . "includes/gamer/functions_economy.php";

if (!function_exists('checkIP')) {

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

    }//end function checkIP()

}
function enviaEmail4($to, $cc, $bcc, $subject, $body_html, $body_plain, $attach = null, $stringAttach = false, $name = '') {

        $mail = new PHPMailer();
	
        //-----Alteraï¿½ï¿½o exigida pela BaseNet(11/2017)-------------//
        $mail->Host     = "email-smtp.sa-east-1.amazonaws.com";
        //---------------------------------------------------------//
        $mail->Mailer   = "smtp";
        $mail->From     = "suporte@e-prepag.com.br";
        $mail->SMTPAuth = true;     // turn on SMTP authentication
        $mail->Username = 'AKIAUYOIQI7LSCTC6LUP';  // a valid email here
        $mail->Password = 'BIFYsYF5+PhgFer64wPmfalJyRQXhukM3HVDoNO17giB'; //'985856';		//'850637';  985856
        $mail->FromName = "E-Prepag";	// " (EPP)"
        $mail->isHTML(true);

        //-----Alteraï¿½ï¿½o exigida pela BaseNet(11/2017)-------------//
        $mail->IsSMTP();
        //$mail->SMTPSecure = "ssl";
        $mail->Port     = 587;
        //---------------------------------------------------------//  

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

        if (!empty($attach)) {
            if ( $stringAttach ) {
			
                $mail->AddStringAttachment($attach, $name);
            } else {
			
                $mail->addAttachment($attach);
            }
        }
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = $body_plain;
			
       return $mail->Send();	
	   
}//end function enviaEmail4

function declare_valida_formatacao() {

        function valida_formatacao($tipo, $tamanho, $valor){

                if(is_null($tipo) || is_null($tamanho) || is_null($valor)) return false;

                $valor = trim($valor);

                if($tipo == "N"){
                        if(preg_match("/^[0-9]{" . $tamanho . "}$/i", $valor)) return true;

                } else if($tipo == "Nle"){
                        if(preg_match("/^[0-9]{1," . $tamanho . "}$/i", $valor)) return true;

                }else if($tipo == "NleX"){
                        if(preg_match("/^[0-9]{1," . $tamanho . "}$/i", $valor) || preg_match("/^[0-9]{1," . ($tamanho - 1) . "}X$/i", $valor)) return true;	

                }

                return false;
        }
}
 
function verificaCPFEx($CPF){

        if(strpos($CPF, '.') === false) return 0;

        $CPF = str_replace('.','',$CPF);
        $CPF = str_replace('-','',$CPF);

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

        return verificaCPF($CPF);
}
 
 
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
	
function obterIdVendaValido(){

        $maxID = 100000000-1;
        $nmax = 100;
        $n = 1;
        $s_ids = "";
        $time_start_stats = getmicrotime();
        $venda_id_rand = mt_rand(1, $maxID);
        $s_ids .= $venda_id_rand.", ";
        while(existeIdVenda($venda_id_rand)){
                $venda_id_rand = mt_rand(1, $maxID);
                $s_ids .= $venda_id_rand.", ";
                $n++;
                if($n>=2*$nmax) {
                        $venda_id_rand = null;
                        break;
                }
        }

        $msg = (($n==1)?"Just one shot!!! ":"ntentativas: $n ")." ($s_ids)";
        gravaLog_obterIdVendaValido($msg);

        if($n>1) {
                $msg = "\tElapsed time ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."s";
                gravaLog_obterIdVendaValido($msg);
        }
        if($n>=$nmax) {
                $msg = "\t\t!!!\tDemorou muito para encontrar um id_venda ($n>=$nmax).";
                gravaLog_obterIdVendaValido($msg);
        }
        return $venda_id_rand;
}

function existeIdVenda($venda_id_rand){

            $ret = true;

            //SQL
            $sql = "select count(*) as qtde from tb_venda_games ";
            $sql .= " where vg_id = " . SQLaddFields($venda_id_rand, "");
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) > 0){
                    $rs_row = pg_fetch_array($rs);
                    if($rs_row['qtde'] == 0) $ret = false;
            }			
            return $ret;   	
}

if(!function_exists('gravaLog_TMP')) {
    function gravaLog_TMP($mensagem){

                //Arquivo
                $file = $GLOBALS['raiz_do_projeto']."log/log_pagamento_TMP.txt";

                //Mensagem
                $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

                //Grava mensagem no arquivo
                if ($handle = fopen($file, 'a+')) {
                        fwrite($handle, $mensagem);
                        fclose($handle);
                } 
        }
}//end if(!function_exists('gravaLog_TMP'))

function gravaLog_Pagto_Insert($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_Pagto_Insert.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_MCOIN($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_pagamento_MCOIN.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 
}

function gravaLog_Login($mensagem, $forced_save = false){

        // Desativa o registro de Sucesso/Erro de logins
        if(!$forced_save) return;

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_login.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . " (".$_SERVER['REMOTE_ADDR'].")\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}


function gravaLog_EPPCASH_PINs($mensagem){

                //Arquivo
                $file = $GLOBALS['raiz_do_projeto'] . "log/log_EPP_CASH_PINs.txt";

                //Mensagem
                $mensagem =  str_repeat("-", 80)."\n".date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";
                //Grava mensagem no arquivo
                if ($handle = fopen($file, 'a+')) {
                        fwrite($handle, $mensagem);
                        fclose($handle);
                } 

}


function gravaLog_obterIdVendaValido($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_obterIdVendaValido.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_Temporario($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_TEMPORARIO.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 
}

function gravaLog_CadastraUsuariosExpressMoney($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_CadastraUsuariosExpressMoney.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 
}

function gravaLog_BloqueioPagtoOnline($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_BloqueioPagtoOnline_Money.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_LimitePagtoOnline($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_LimitePagtoOnline_Money.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_LimitePagtoOnline_Drupal($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_LimitePagtoOnline_Money_Drupal.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_PagtoOnlineUsuariosBloqueadosParaVIP($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_Money_PagtoOnlineUsuariosBloqueadosParaVIP.txt";

        //Mensagem
        $mensagem = str_repeat("=", 80)."\n".date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function PagtoOnlineUsuariosBloqueadosParaVIP($tipo_pagto, $ug_id, $total_carrinho, $total_diario, $total_limite, $n_compras, $n_compras_limite){

        if(!$ug_id) return;

        $tipo_usuario = "G";
        $is_safe = (($total_carrinho+$total_diario)<2*$total_limite)?1:0;

        $sql = "insert into usuarios_games_pagamento_bloqueio_log (" .
                        "ugpbl_tipo_usuario, ugpbl_tipo_pagto, ugpbl_ug_id, ugpbl_valor_carrinho, ".
                        "ugpbl_valor_total, ugpbl_valor_limite, ugpbl_n_compras, ugpbl_n_compras_limite, ugpbl_is_safe " .
                        ") values (";
        $sql .= SQLaddFields($tipo_usuario, "s") . ",";
        $sql .= SQLaddFields($tipo_pagto, "s") . ",";
        $sql .= SQLaddFields($ug_id, "") . ",";
        $sql .= SQLaddFields($total_carrinho, "") . ",";
        $sql .= SQLaddFields($total_diario, "") . ",";
        $sql .= SQLaddFields($total_limite, "") . ",";
        $sql .= SQLaddFields($n_compras, "") . ", ";
        $sql .= SQLaddFields($n_compras_limite, "") . ", ";
        $sql .= SQLaddFields($is_safe, "") . ") ";
        $ret = SQLexecuteQuery($sql);

}



function gravaLog_EnviaEmail($canal, $to, $subject) {

        //Arquivo
        $file = $GLOBALS['raiz_do_projeto'] . "log/log_EnviEmail.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . "|$canal|$to|$subject \n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_Captura($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_captura.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";
        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_BoletoExpressMoney($mensagem){
        global $raiz_do_projeto;
        //Arquivo
//		$file = $GLOBALS['ARQUIVO_LOG_SQL_EXECUTE_QUERY'];
        $file = $raiz_do_projeto . "log/log_commerce_BoletoExpressMoney.txt";	

        //Mensagem
        $mensagem = date('Y-m-d H:i:s')." - ".$mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function gravaLog_DrupalOrdersRequestGamers($mensagem){

        //Arquivo
        $file = RAIZ_DO_PROJETO . "log/log_DrupalOrdersRequestGamers.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

// ================================================
// ver em ajax_info_pagamento.php - usa diretamente este cï¿½digo para nï¿½o ter que incluir este arquivo
function set_IntegracaoDrupal_marca_sessao_logout() {
        //Invalida a sessao, caso exista 
        //	(para evitar o caso onde o usuï¿½rio faz login na pï¿½gina de pagamentos com PINs EPP para usar o saldo e volta a pagamento_int.php, 
        //		que tenta o login automatico de integraï¿½ï¿½o e nï¿½o consegue invalidando a integraï¿½ï¿½o, mas ficaria o login feito anteriormente)
        //		nï¿½o seria uma ameaï¿½a porque o usuï¿½rio teria que fazer login antes, mas queremos que o login de integraï¿½ï¿½o seja usado apenas para integraï¿½ï¿½o
        if(isValidaSessao()) {
                cancelarSessao();
        }

        $GLOBALS['_SESSION']['integracao_is_parceiro'] = "";
        $GLOBALS['_SESSION']['integracao_origem_id'] = "";
        $GLOBALS['_SESSION']['integracao_order_id'] = "";
        $GLOBALS['_SESSION']['integracao_transaction_id'] = "";
        $GLOBALS['_SESSION']['integracao_error_msg'] = "";
        $GLOBALS['_SESSION']['integracao_autenticado'] = "";
        $GLOBALS['_SESSION']['allow_calling'] = "";

        $GLOBALS['_SESSION']['drupal_order_id'] = "";
        $GLOBALS['_SESSION']['drupal_render_css'] = "";
        $GLOBALS['_SESSION']['drupal_render_cart'] = "";


        unset($GLOBALS['_SESSION']['integracao_is_parceiro']);
        unset($GLOBALS['_SESSION']['integracao_origem_id']);
        unset($GLOBALS['_SESSION']['integracao_order_id']);
        unset($GLOBALS['_SESSION']['integracao_transaction_id']);
        unset($GLOBALS['_SESSION']['integracao_autenticado']);
        unset($GLOBALS['_SESSION']['allow_calling']);

        unset($GLOBALS['_SESSION']['drupal_order_id']);
        unset($GLOBALS['_SESSION']['drupal_render_css']);
        unset($GLOBALS['_SESSION']['drupal_render_cart']);

}


function redirect($strRedirect){

        ob_end_clean();

        if(substr($strRedirect, 0, 4) != "http")
                $strRedirect = (strtoupper($_SERVER['HTTPS']) == "ON"?"https":"http") . "://" . $_SERVER['HTTP_HOST'] . $strRedirect;
        //redirect externo
        ?><html><body onload="window.location='<?php echo $strRedirect?>'"><?php
        exit;
}

function redirect_dr($strRedirect, $post_ser_encrypted_encoded){

        ob_end_clean();

        if(substr($strRedirect, 0, 4) != "http")
                $strRedirect = "http" . "://" . $_SERVER['HTTP_HOST'] . $strRedirect;
        //redirect externo
        ?><html><body onload="window.location='<?php echo $strRedirect;		//."".$post_ser_encrypted_encoded ?>'"><?php
        exit;
}

function Dia_Semana($posicao){
        //'posicao = nï¿½mero relacionado a string de dados
        $dias = array("Domingo", "Segunda", "Terï¿½a", "Quarta", "Quinta", "Sexta", "Sï¿½bado");
        return $dias[$posicao];
}

function Mes_Do_Ano($posicao){
        //'posicao = nï¿½mero relacionado a string de dados
        $meses = array("", "Janeiro", "Fevereiro", "Marï¿½o", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");
        return $meses[$posicao];
}

function Mes_Do_Ano_Short($posicao){
        //'posicao = nï¿½mero relacionado a string de dados
        $meses = array("", "Jan", "Fev", "Mar", "Abr", "Ma", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez");
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

        if(	(strlen($val) >= 4) &&
                (strrpos($val, ",") == strlen($val) - 3) &&
                is_numeric(substr($val, 0, 1)) &&
                (substr($val, 0, 1) != "0")){

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

function retorna_ip_acesso_gamer() {
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

        if(!$usuario_games_id){
                $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
                if($usuarioGames) $usuario_games_id = $usuarioGames->getId();
        }
        if(!$usuario_games_id) return;
        $ip_long_unsigned = sprintf("%u\n", ip2long($_SERVER['REMOTE_ADDR']));

        $sql = "insert into usuarios_games_log (" .
                        "	ugl_data_inclusao, ugl_ip, ugl_ip_long, ugl_uglt_id, ugl_ug_id, ugl_vg_id" . ($observacao == null ? "" : ", ugl_obs") .
                        ") values (";
        $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
        $sql .= SQLaddFields(retorna_ip_acesso_gamer(), "s") . ",";
        $sql .= SQLaddFields($ip_long_unsigned, "") . ",";
        $sql .= SQLaddFields($tipo, "") . ",";
        $sql .= SQLaddFields($usuario_games_id, "") . ",";
        $sql .= SQLaddFields($venda_id, "");
        if($observacao != null) {
                $sql.= ", ". SQLaddFields($observacao, "s");
        }
        $sql .= ")";
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

function enviaEmailRelatorio($to, $cc, $bcc, $subject, $msgEmail) {
       
        return enviaEmail3($to, $cc, $bcc, $subject, $msgEmail, $body_plain);	
}

// Estï¿½ usando esta enviaEmail3() em bko\commerce\ -> processaEmailVendaGames()
function enviaEmail3($to, $cc, $bcc, $subject, $body_html, $body_plain) {

        $mail = new PHPMailer();
        //-----Alteraï¿½ï¿½o exigida pela BaseNet(11/2017)-------------//
        $mail->Host     = "email-smtp.sa-east-1.amazonaws.com";
        //---------------------------------------------------------//
        $mail->Mailer   = "smtp";
        $mail->From     = "suporte@e-prepag.com.br";
        $mail->SMTPAuth = true;     // turn on SMTP authentication
        $mail->Username = 'AKIAUYOIQI7LSCTC6LUP';  // a valid email here
        $mail->Password = 'BIFYsYF5+PhgFer64wPmfalJyRQXhukM3HVDoNO17giB'; //'985856';		//'850637'; 
        $mail->FromName = "E-Prepag";	// " (EPP)"

        //-----Alteraï¿½ï¿½o exigida pela BaseNet(11/2017)-------------//
        $mail->IsSMTP();
        ////$mail->SMTPSecure = "ssl";
        $mail->Port     = 587;

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
        $mail->isHTML();
        $mail->Body    = $body_html;
        $mail->AltBody = $body_plain;

        $sret = $mail->Send();	

        gravaLog_EnviaEmail("M", $to, $subject);

        return $sret;	

}

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
                $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);

                //prezado
                if($usuarioGames->getSexo() == "M") $prezado = "Prezado ";
                else if($usuarioGames->getSexo() == "F") $prezado = "Prezada ";
                else  $prezado = "Prezado(a) ";

                //Nome
                $nome = $usuarioGames->getNome();

        } else {

                //prezado
                if($parametros['sexo'] == "M") $prezado = "Prezado ";
                else if($parametros['sexo'] == "F") $prezado = "Prezada ";
                else  $prezado = "Prezado(a) ";

                //Nome
                if($parametros['nome']) $nome = $parametros['nome'];
                else $nome = " Usuï¿½rio(a) E-Prepag";

        }

        $email_cab = "
                                        <html>
                                        <head>
                                                <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
                                                <title>Prepag</title>
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
                                                        .texto_azul {
                                                                font-family: Arial, Helvetica, sans-serif;
                                                                font-size: 11px;
                                                                color: #000080;
                                                                text-decoration: none;
                                                        }
                                                        .texto_destaque {
                                                                font-size: 16px;
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
                                                                " . Data_Atual_Por_Extenso(). ", " . date("H:i") . "<br><br><br>
                                                        </td>";


        if(isset($parametros['logoepp']) && $parametros['logoepp']) { 
                        // width='226' height='54' 
                $email_cab .= "		<td align='right' class='texto'>
                                                                <a href='http://www.e-prepag.com.br/'><img src='http://www.e-prepag.com.br/eprepag/imgs/home/LogoEPP_novo.jpg' title='E-PREPAG(EPP)' width='110' height='28' border='0' /></a>
                                                        </td>";
        }

        $email_cab .= "			</tr>
                                                <tr valign='middle' bgcolor='#FFFFFF'>
                                                        <td align='left' class='texto'>" . 
                                                                $prezado . $nome . "," .
                                                                        " (".((isset($parametros['logoepp']))?$parametros['logoepp']:"").")".
                                                                        "<br><br><br>
                                                        </td>";
        if(isset($parametros['logoepp']) && $parametros['logoepp']) { 
                $email_cab .= "		<td align='left' class='texto'>&nbsp;</td>";
        }

        $email_cab .= "			</tr>
                                                        </table>
                                ";

        return $email_cab;
}

function email_rodape($parametros){ 

        $email_rod  = "
                                                        <br>
                                                        <table border='0' cellspacing='0' width='100%'>
                                                <tr valign='middle' bgcolor='#FFFFFF'>
                                                        <td align='left' class='texto'>
                                                                <br><br>
                                                                        Para resolver qualquer dï¿½vida entre em contato conosco no email <a href='mailto:suporte@e-prepag.com.br'>suporte@e-prepag.com.br</a>.<br>
                                                                        <br>
                                                                        Agradecemos sua preferï¿½ncia por nossos produtos e serviï¿½os.<br>
                                                                         <br>
                                                                        Atenciosamente<br>
                                                                        <br>";
        if(isset($parametros['logoepp']) && ($parametros['logoepp']==1)) { 
                $email_rod .= "<a href='http://www.e-prepag.com.br/'><img src='http://www.e-prepag.com.br/eprepag/imgs/home/LogoEPP_novo.jpg' title='E-PREPAG(EPP)' width='110' height='28' border='0' /></a><br>\n";
        }

        $email_rod  .= "				E-Prepag<br>
                                                                        www.e-prepag.com.br<br>
                                                        </td>
                                                </tr>
                                                        </table>

                                                </td>
                                        </tr>
                                        <tr><td>&nbsp;</td></tr>
                                        <tr align='center'><td><hr></td></tr>
                                        <tr>
                                                <td height='10'>
                                                        <br>
                                                        <table width='100%'  border='0' cellpadding='0' cellspacing='0' bgcolor='#F1F1F1'>
                                                        <tr height='23'>
                                                        <td width='1%'></td>
                                                        <td width='98%' align='center' class='rodape'>E-Prepag Copyright ".date('Y').". Todos os direitos reservados. <a href='http://www.e-prepag.com.br/eprepag/moedavirtual/ajuda_seguranca.asp' class='rodape'>Pol&iacute;tica de Privacidade e Seguran&ccedil;a</a></td>
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

function getMeiosPagamentosBloqueados($tipoId, $libera_pagamento){

        global $raiz_do_projeto;

        if(!defined("ARR_JSON_PRODUTOS_MEIOS_DE_PAGAMENTOS_BLOQUEADOS_GAMER"))
            require_once $raiz_do_projeto.'/includes/constantes.php';

        require_once $raiz_do_projeto."/class/util/Json.class.php";

        $json = new Json;
        $json->setFullPath(DIR_JSON);
        $json->setArrJsonFiles(unserialize(ARR_JSON_PRODUTOS_MEIOS_DE_PAGAMENTOS_BLOQUEADOS_GAMER));
        $obj = $json->getJsonRecursive();

        if(isset($tipoId['produtoModeloId'])){

            $id = $tipoId['produtoModeloId'];

            if(isset($obj->$id)){

                foreach($obj->$id->formasPagamento as $fPagamento => $mode){

                    if($mode === true){
                        $libera_pagamento = bloqueiaPagamento($libera_pagamento,$fPagamento);
                    }
                }
            }
        }else if(isset($tipoId['operadora'])){
            if($obj){
                foreach($obj as $modelo){
                    if($modelo->operadora == $tipoId['operadora']){
                        foreach($modelo->formasPagamento as $fPagamento => $mode){

                            if($mode === true){
                                $libera_pagamento = bloqueiaPagamento($libera_pagamento,$fPagamento);
                            }
                        }

                    }
                }
            }
        }

        return $libera_pagamento;
}

function bloqueiaPagamento($libera_pagamento,$fPagamento){

        switch (trim($fPagamento)) {
            case '1':
                $libera_pagamento['Deposito'] = false;
                break;
            case '2':
                $libera_pagamento['Boleto'] = false;
                break;
            case '5':
                $libera_pagamento['Bradesco'] = false;
                break;
            case '6':
                $libera_pagamento['Bradesco'] = false;
                break;
            case '7':
                $libera_pagamento['Bradesco'] = false;
                break;
            case '9':
                $libera_pagamento['BancodoBrasil'] = false;
                break;
            case 'A':
                $libera_pagamento['BancoItau'] = false;
                break;
            case 'B':
                $libera_pagamento['Hipay'] = false;
                break;
            case 'P':
                $libera_pagamento['Paypal'] = false;
                break;
            case 'E':
                $libera_pagamento['EppCash'] = false;
                break;
            case 'F':
                $libera_pagamento['Cielo_Visa_DEB'] = false;
                break;
            case 'G':
                $libera_pagamento['Cielo_Visa_CRED'] = false;
                break;
            case 'H':
                $libera_pagamento['Cielo_Master_DEB'] = false;
                break;
            case 'I':
                $libera_pagamento['Cielo_Master_CRED'] = false;
                break;
            case 'J':
                $libera_pagamento['Cielo_Elo_DEB'] = false;
                break;
            case 'K':
                $libera_pagamento['Cielo_Elo_CRED'] = false;
                break;
            case 'L':
                $libera_pagamento['Cielo_Diners_CRED'] = false;
                break;
            case 'M':
                $libera_pagamento['Cielo_Discover_CRED'] = false;
                break;
            case 'R':
                $libera_pagamento['Pix'] = false;
                break;
        } //end switch ($cod_pagto)

        return $libera_pagamento;
}

function mostraCarrinho_pag($bprint, $iativo, &$libera_pagamento = array()){

        $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
        //Recupera carrinho do session
        $carrinho = $GLOBALS['_SESSION']['carrinho'];
        if(count($carrinho)==0 && isset($GLOBALS['_SESSION']['campeonato.prod_id'])) {
                $carrinho = array();
                $carrinho[$GLOBALS['_SESSION']['campeonato.prod_id']] = 1;
        }
        $total_geral = 0;
        $GLOBALS['_SESSION']['carrinho_total_geral_treinamento'] = 0;
        $iativo = ($iativo)?1:0;
        // recupera dados integraï¿½ï¿½o
        $b_amount_free = "0";
        $carrinho_val = "";
        if(isset($GLOBALS['_SESSION']['integracao_origem_id'])) {
                if (function_exists('getPartner_amount_free_By_ID')) {
                        $b_amount_free = getPartner_amount_free_By_ID($GLOBALS['_SESSION']['integracao_origem_id']);
                        if(isset($GLOBALS['_SESSION']['carrinho_val'])) {
                                $carrinho_val = $GLOBALS['_SESSION']['carrinho_val'];
                                $iativo = 0;	// Como o product_id vem do parceiro, nï¿½o importa se o produto ï¿½ ativo ou nï¿½o 
                        }
                }
        }
        if(!$carrinho || count($carrinho) == 0){
                if($bprint) {
?>			
                <table border="0" cellspacing="0" width="90%" height="200">
    <tr align="center" bgcolor="#FFFFFF">
      <td align="center" class="texto">Carrinho vï¿½zio no momento (1)</td>
    </tr>
                </table>
<?php
                }
        } else {

                if($bprint) {
                ?>
                <table border="0" cellspacing="0" width="95%" align="center">
        <tr bgcolor="F0F0F0">
          <td class="texto" align="center" height="25"><b>Descriï¿½ï¿½o</b>&nbsp;</td>
          <td class="texto" align="center" colspan="1"><b>Quantidade</b>&nbsp;</td>
          <td class="texto" align="center">&nbsp;</td>
          <td class="texto" align="center"><b>Unitï¿½rio</b>&nbsp;</td>
          <td class="texto" align="center"><b>Total</b>&nbsp;</td>
          <td class="texto" align="center">&nbsp;</td>
        </tr>
                <?php
                }			
        $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);

        foreach ($carrinho as $modeloId => $qtde){
            if($modeloId !== $GLOBALS['NO_HAVE']) {
                $qtde = intval($qtde);
                $rs = null;
                $filtro['ogpm_ativo'] = $iativo;
                $filtro['ogpm_id'] = $modeloId;
                $filtro['com_produto'] = true;

        //inicio bloco que libera pagamento para os publisher
        $tipoId['produtoModeloId'] = $modeloId;
        $libera_pagamento = getMeiosPagamentosBloqueados($tipoId, $libera_pagamento);
        //fim do bloco que libera pagamento para os publisher

        if(isset($usuarioGames)) {
                if($usuarioGames->b_IsLogin_pagamento_usa_produto_treinamento())  {
                        $filtro['show_treinamento'] = 1;
                }
        }
        $instProdutoModelo = new ProdutoModelo;
        $ret = $instProdutoModelo->obter($filtro, null, $rs);
        if($rs && pg_num_rows($rs) != 0){
                $rs_row = pg_fetch_array($rs);
                $ogpm_valor = (($b_amount_free=="1")?($carrinho_val[$modeloId]/100):$rs_row['ogpm_valor']); 
                $ogpm_pin_valor = (($b_amount_free=="1")?($carrinho_val[$modeloId]/100):$rs_row['ogpm_pin_valor']); 
                $total_geral += $ogpm_valor * $qtde;

                // Debug reinaldops
                // Para PINs de Treinamento 
                //		-> salva tambï¿½m o valor nominal para usar nos testes de Pagamento online (envia para o banco um valor !=0 mas a venda ï¿½ =0)
                //	Tem que modificar a conciliaï¿½ï¿½o para aceitar o pagamento !=0 numa venda =0
                if($rs_row['ogpm_ogp_id']==63 && 
                        ($rs_row['ogpm_id']==282 || $rs_row['ogpm_id']==283 || $rs_row['ogpm_id']==284 || $rs_row['ogpm_id']==285 || $rs_row['ogpm_id']==286)) {
                        $GLOBALS['_SESSION']['carrinho_total_geral_treinamento'] += $ogpm_pin_valor * $qtde;

                }
                if($bprint) {
?>
        <tr>
          <td class="texto" height="25" width="150">
                &nbsp;&nbsp;<nobr>
                <?php echo $rs_row['ogp_nome']?>
                <?php if($rs_row['ogpm_nome']!="") { echo " - ".$rs_row['ogpm_nome']; }?></nobr>
          </td>
          <td class="texto" align="center"><?php echo $qtde?></td>
          <td class="texto">&nbsp;</td>		
                          <?php //echo number_format($rs_row['ogpm_valor'], 2, ',', '.')?>
          <td class="texto" align="center"><?php echo number_format($ogpm_valor, 2, ',', '.')?></td>				  
                          <?php //echo number_format($rs_row['ogpm_valor']*$qtde, 2, ',', '.')?>
          <td class="texto" align="right"><?php echo number_format($ogpm_valor*$qtde, 2, ',', '.')?></td>
          <td class="texto" align="right"></td>
        </tr>
<?php
                                }
                        }
            }//end if($modeloId !== $GLOBALS['NO_HAVE'])
            else {
                foreach ($qtde as $codeProd => $vetor_valor) {
                    foreach ($vetor_valor as $valor => $quantidade) {
                            $quantidade = intval($quantidade);
                            $rs = null;
                            $filtro['ogp_ativo'] = 1;
                            $filtro['ogp_id'] = $codeProd;
                            $filtro['ogp_mostra_integracao_com_loja'] = '1';
                            $filtro['opr'] = 1;
                            $ret = (new Produto)->obtermelhorado($filtro, null, $rs);
                            if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponï¿½vel no momento.";
                            else $rs_row = pg_fetch_array($rs);

                            //inicio bloco que libera pagamento para os publisher
                            $tipoId['operadora'] = $rs_row['ogp_opr_codigo'];
                            $libera_pagamento = getMeiosPagamentosBloqueados($tipoId, $libera_pagamento);
                            //fim do bloco que libera pagamento para os publisher

                            $ogpm_valor = $valor; 
                            $ogpm_pin_valor = (new ConversionPINsEPP)->get_ValorEPPCash('E',$valor); 

                            $total_geral += $ogpm_valor * $quantidade;
					
                            if($bprint) {
                ?>
    	        <tr>
    	          <td class="texto" height="25" width="150">
    	          	&nbsp;&nbsp;<nobr>
    	          	<?php echo $rs_row['ogp_nome'];?></nobr>
    	          </td>
    	          <td class="texto" align="center"><?php echo $quantidade?></td>
    	          <td class="texto">&nbsp;</td>		
    	          <td class="texto" align="center"><?php echo number_format($ogpm_valor, 2, ',', '.')?></td>				  
    	          <td class="texto" align="right"><?php echo get_info_EPPCash_NO_Table((new ConversionPINsEPP)->get_ValorEPPCash('E',$valor)*$quantidade);?></td>
    	          <td class="texto" align="right"></td>
    	        </tr>
<?php
                            }//end if($bprint)
                    }//end foreach 
                }//end foreach
            }//end else do if($modeloId !== $GLOBALS['NO_HAVE'])
        }//end foreach
        if($bprint) {
			?>
        <tr bgcolor="F0F0F0">
          <td colspan="3">&nbsp;</td>
          <td class="texto" align="right" height="25"><b>Total</b>&nbsp;</td>
          <td class="texto" align="right"><b><?php echo number_format($total_geral, 2, ',', '.')?></b></td>
          <td>&nbsp;</td>
        </tr>
                </table>
                <?php
                }
        }

        return $total_geral;
}


// Retorna true se o carrinho contem apenas produtos escolhidos para usar novas formas de pagamento: Habbo (16) e GPotato (31)
function bCarrinho_ApenasProdutosOK($iativo){

        $iativo = ($iativo)?1:0;

        //Recupera carrinho do session
        $carrinho = $_SESSION['carrinho'];

        if(!$carrinho || count($carrinho) == 0){
                $breturn = false;
        } else {
                foreach ($carrinho as $modeloId => $qtde) {
                        $rs = null;
                        $filtro['ogpm_ativo'] = $iativo;
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
        if($_SESSION['drupal_deposit']=="1") {
/* seguir este padrï¿½o para funcionar com pagamentos Bradesco (dadosComopraBradesco.php)
"item:Habbo Hotel - Pacote de 30 HABBO MOEDAS
1
pin
1000
"
*/
                $sout = "item:Depï¿½sito DRUPAL: \n";
                $sout .= "1\n";
                $sout .= "deposit\n";
                $sout .= "".(($_SESSION['drupal_deposit_amount']>0)?$_SESSION['drupal_deposit_amount']:"0")."\n";
                $sout .= "";
                return $sout;
        }

        $iativo = 1;

        $b_isintegracao = (get_Integracao_is_sessao_logged()?true:false); 
        if($b_isintegracao) {
                if (function_exists('getPartner_amount_free_By_ID')) {
                        $b_amount_free = getPartner_amount_free_By_ID($GLOBALS['_SESSION']['integracao_origem_id']);
                        if(isset($GLOBALS['_SESSION']['carrinho_val'])) {
                                $carrinho_val = $GLOBALS['_SESSION']['carrinho_val'];
                                $iativo = 0;	// Como o product_id vem do parceiro, nï¿½o importa se o produto ï¿½ ativo ou nï¿½o 
                                if($b_amount_free=="1") {
                                }
                        }
                }
        }

        if($b_isintegracao) {
                if(strlen($carrinho)==0 && strlen($carrinho_val)>0) {
                        $carrinho = $carrinho_val;
                }
                $iativo = 0;
        }
        //Recupera carrinho do session
        $carrinho = $GLOBALS['_SESSION']['carrinho'];
        if(count($carrinho)===0 && isset($GLOBALS['_SESSION']['campeonato.prod_id'])) {
                $carrinho = array();
                $carrinho[$GLOBALS['_SESSION']['campeonato.prod_id']] = 1;
                $iativo = 0;
        }

        if(isset($GLOBALS['_SESSION']['carrinho_mcoin']) && ($GLOBALS['_SESSION']['carrinho_mcoin']==1)) {
                $iativo = 0;
        }
        if(!$carrinho || count($carrinho) == 0){		
                $sout = "Vazio\n";
        } else {
                // Debug reinaldops
                $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);

                $sout = "";
                foreach ($carrinho as $modeloId => $qtde){

                    if($modeloId !== $GLOBALS['NO_HAVE']) {
                        $qtde = intval($qtde);
                        $rs = null;
                        $filtro['ogpm_ativo'] = $iativo;
                        $filtro['ogpm_id'] = $modeloId;
                        $filtro['com_produto'] = true;

                        if(isset($usuarioGames)) {
                                if($usuarioGames->b_IsLogin_pagamento_usa_produto_treinamento())  {
                                        $filtro['show_treinamento'] = 1;
                                }
                        }
                        $instProdutoModelo = new ProdutoModelo;
                        $ret = $instProdutoModelo->obter($filtro, null, $rs);
                        if($rs && pg_num_rows($rs) != 0){
                                $rs_row = pg_fetch_array($rs);
                                $ogpm_valor = (($b_amount_free=="1")?($carrinho_val[$modeloId]/100):$rs_row['ogpm_valor']); 
                                $sout .= "item:".$rs_row['ogp_nome'].(($rs_row['ogpm_nome']!="")?(" - ".$rs_row['ogpm_nome']):"")."\n"; 
                                $sout .= $qtde."\n";
                                $sout .= "pin".(($qtde>1)?"s":"")."\n";
                                $sout .= (100*$ogpm_valor*$qtde)."\n";
                        }
                    }//end if($modeloId !== $GLOBALS['NO_HAVE'])
                    else {
                        foreach ($qtde as $codeProd => $vetor_valor) {
                            foreach ($vetor_valor as $valor => $quantidade) {
                                    $quantidade = intval($quantidade);
                                    $rs = null;
                                    $filtro['ogp_ativo'] = 1;
                                    $filtro['ogp_id'] = $codeProd;
                                    $filtro['ogp_mostra_integracao_com_loja'] = '1';
                                    $filtro['opr'] = 1;
                                    $ret = (new Produto)->obtermelhorado($filtro, null, $rs);
                                    if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponï¿½vel no momento.";
                                    else $rs_row = pg_fetch_array($rs);

                                    $ogpm_valor = $valor; 
                                    $sout .= "item:".$rs_row['ogp_nome'].PHP_EOL; 
                                    $sout .= $quantidade.PHP_EOL;
                                    $sout .= "pin".(($quantidade>1)?"s":"").PHP_EOL;
                                    $sout .= (100*$ogpm_valor*$quantidade).PHP_EOL;
                            }//end foreach 
                        }//end foreach
                    }//end else do if($modeloId !== $GLOBALS['NO_HAVE'])
                }//end foreach
        }
        return $sout;
}

function montaCesta_pag_bep(){
        //Recupera carrinho do session
        $carrinho = $GLOBALS['_SESSION']['carrinho'];
        if(!$carrinho || count($carrinho) == 0){		
                $sout = "Vazio BEP\n";
        } else {
                $sout = "";
                foreach ($carrinho as $modeloId => $qtde){

                        $qtde = intval($qtde);
                        $rs = null;
                        $filtro['ogpm_ativo'] = 0;
                        $filtro['ogpm_id'] = $modeloId;
                        $filtro['com_produto'] = true;
                        $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                        if($usuarioGames->b_IsLogin_pagamento_usa_produto_treinamento())  {
                                $filtro['show_treinamento'] = 1;
                        }
                        if(isset($usuarioGames)) {
                                if($usuarioGames->b_IsLogin_pagamento_pin_eprepag()) {
                                }
                        }

                        $ret = ProdutoModelo::obter($filtro, null, $rs);
                        if($rs && pg_num_rows($rs) != 0){
                                $rs_row = pg_fetch_array($rs);
                                $sout .= "item:".$rs_row['ogp_nome'].(($rs_row['ogpm_nome']!="")?(" - ".$rs_row['ogpm_nome']):"")."\n"; 
                                $sout .= $qtde."\n";
                        $sout .= "pin".(($qtde>1)?"s":"")."\n";
                        $sout .= (100*$rs_row['ogpm_valor']*$qtde)."\n";
                        }
                }
        }
        return $sout;
}

function montaCesta_pag_paypal($vg_id){
        $cesta_nome = "";
        $sql = "select vgm_nome_produto, vgm_qtde, vgm_nome_modelo, vgm_valor, *
                from tb_venda_games vg 
                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                where vg_id = $vg_id
                order by vgm_qtde";
        $rs = SQLexecuteQuery($sql);
        if($rs && pg_num_rows($rs) > 0){
                while($rs_row = pg_fetch_array($rs)) {
                        $cesta_nome .= $rs_row['vgm_qtde']." x ".$rs_row['vgm_nome_modelo']." (R\$".number_format($rs_row['vgm_valor'], 2, ',', '.').")\n";
                }
        }			
        if(!$cesta_nome || count($cesta_nome) == 0){		
                $sout = "Produto EPP padrao\n";
        } else {
                $sout = $cesta_nome;
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

// -- 'M' - Money, 'E' - Money Express, 'LR' - Lanhouse Prï¿½, 'LO' - Lanhouse Pï¿½s
function get_tipo_cliente_descricao($stipo) {
        switch ($stipo) {
                case "M":
                        $sout = "Money";
                        break;
                case "E":
                        $sout = "Money_Express";
                        break;
                case "LR":
                        $sout = "LH_PrÃ©";
                        break;
                case "LO":
                        $sout = "LH_PÃ³s";
                        break;
                default:
                        $sout = "???";
                        break;
        }
        return $sout;
}

function getNVendasMoneySEG($idusuario){ //flavio aqui

        $publishers = [
		      149,97,45,128,62,150,139,142,103,61,63,124,
			  113,16,135,155,148,23,129,156,130,131,146,
			  132,121,13,40,47,60,82,90,37,140,133,157,134,
			  143,34,95,126,127,141,114,115,66
		];
        $qtde = 0;
        //SQL
       // $sql = "select count(*) as qtde from tb_venda_games";
       // $sql .= " where vg_ug_id = " . SQLaddFields($idusuario, "");
       // $sql .= " and vg_data_inclusao>='".date('Y-m-d H:i:s', strtotime("-30 days"))."' and vg_ultimo_status=5 ";
		
		//select distinct vg.vg_id, count(vg.vg_data_inclusao) as qtde from tb_venda_games vg inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id 
        //where vg.vg_ug_id = 1190925 and vg_data_inclusao >= '2020-09-01' and vg_ultimo_status=5 and vgm.vgm_opr_codigo in(149,97,45,128,62,150,139,142,103,61,63,124,113,16,135,155,148,23,129,156,130,131,146,132,121,13,40,47,60,82,90,37,140,133,157,134,143,34,95,126,127,141,114,115,66) group by vg.vg_id;

		$sql = "select distinct vg.vg_id, count(vg.vg_data_inclusao) as qtde from tb_venda_games vg inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id";
        $sql .= " where vg.vg_ug_id = ".SQLaddFields($idusuario, "")." and vg_data_inclusao >= '".date('Y-m-d H:i:s', strtotime("-30 days"))."' and vg_ultimo_status=5 and";
		$sql .= " vgm.vgm_opr_codigo in(".implode(',', $publishers).") group by vg.vg_id;";
		
		//var_dump($sql);
        $rs = SQLexecuteQuery($sql);
        if($rs && pg_num_rows($rs) > 0){
                $rs_row = pg_fetch_array($rs);
                $qtde = pg_num_rows($rs);  //$rs_row['qtde'];
        }			
        // for Debug
        $mensagem = "In getNVendasMoney(): ".
                                "qtde: ".$rs_row['qtde']." - ".
                                "idusuario: ".$idusuario."\n";
        gravaLog_BloqueioPagtoOnline($mensagem);
        return $qtde;   	
}

function getNVendasMoney($idusuario){
        $qtde = 0;
        //SQL
        $sql = "select count(*) as qtde from tb_venda_games ";
        $sql .= " where vg_ug_id = " . SQLaddFields($idusuario, "");
        $sql .= " and vg_data_inclusao>='".date('Y-m-d H:i:s', strtotime("-1 days"))."' and vg_ultimo_status=5 ";
        $sql .= " and (vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PIX_NUMERIC']." ); ";
        $rs = SQLexecuteQuery($sql);
        if($rs && pg_num_rows($rs) > 0){
                $rs_row = pg_fetch_array($rs);
                $qtde = $rs_row['qtde'];
        }			
        // for Debug
        $mensagem = "In getNVendasMoney(): ".
                                "qtde: ".$rs_row['qtde']." - ".
                                "idusuario: ".$idusuario."\n";
        gravaLog_BloqueioPagtoOnline($mensagem);
        return $qtde;   	
}

function getVendasMoneyTotalDiarioOnline($idusuario){
        $total = 0;
        // novo - lista vendas nï¿½o canceladas (completas + em aberto) nas ï¿½ltimas 24h
        $sql = "select sum(vgm_valor*vgm_qtde) as total from tb_venda_games vg inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id";
        $sql .= " where vg_ug_id = " . SQLaddFields($idusuario, "");
        $sql .= " and vg_data_inclusao>='".date('Y-m-d H:i:s', strtotime("-1 days"))."' ";	
        $sql .= " and (vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']." or vg_pagto_tipo=".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']." or vg_pagto_tipo=".$GLOBALS['PAGAMENTO_PIX_NUMERIC'].") ";
        $sql .= " and (not vg_ultimo_status=6) ";
        $sql .= " group by vg_ug_id ";
        $rs = SQLexecuteQuery($sql);
        if($rs && pg_num_rows($rs) > 0){
                $rs_row = pg_fetch_array($rs);
                $total = ($rs_row['total'])?$rs_row['total']:0;
        }			
        // for Debug
        $mensagem = "In getVendasMoneyTotalDiarioOnline(): ".
                                "idusuario: ".$idusuario." - ".
                                "total: ".$total."\n".
                                $sql."\n";
        gravaLog_BloqueioPagtoOnline($mensagem);
        return $total;   	
}


function get_newOrderID() {
	$bfound = true;
	$ntries = 0;
	$orderId = "";

	$orderId = 	date("YmdHis").str_pad(rand(0,999), 3, "0", STR_PAD_LEFT);
	do {

//		$orderId = 	"2003120408301545872781";
//		$orderId = 	date("YmdHis").str_pad(rand(0,99999999), 8, "0", STR_PAD_LEFT);
		$sql = "SELECT count(*) as n from tb_pag_compras where numcompra='".$orderId."'";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) {
			echo "Erro ao recuperar transaï¿½ï¿½o de pagamento.\n";
			die("Stop");
		} else {
			$pgresult = pg_fetch_array($ret);
			$bfound = (($pgresult['n']==0)?true:false);
		}
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


function print_r2($val){
	echo '<pre>';
	print_r($val);
	echo  '</pre>';
}

function b_isIntegracao() {
	// Algumas pï¿½ginas nï¿½o trabalham em integraï¿½ï¿½o, para evitar que ProdutoModelo:obter() retorne modelos inativos
	if(strpos(strtolower($GLOBALS['_SERVER']['SCRIPT_NAME']), "/prepag2/commerce/modelosEx.php")===false) {
		if($GLOBALS['_SESSION']['integracao_is_parceiro']=="OK" && isset($GLOBALS['_SESSION']['integracao_origem_id']) && isset($GLOBALS['_SESSION']['integracao_order_id'])) {
			return true;
		}
	}
	return false;
}

// Algumas operadoras permitem que o usuï¿½rio troque de email antes de enviar o pedido de integraï¿½ï¿½o -> nï¿½o permite ver o Saldo ou pagar com Saldo
function b_isIntegracao_with_nonvalidated_email() {
		return true;
}

// Retorna true para as pï¿½ginas da loja que podem ser utilizadas no login por integraï¿½ï¿½o, todas as outras serï¿½o recusadas por validaSessao()
function b_isIntegracao_allowed_url() {
	$a_urls_allowed_in_integracao = array(
		"/prepag2/commerce/pagamento_int.php", 
//		"/prepag2/commerce/pagamento_int_cielo.php",				// temporario, para testes de pagto Cielo para integraï¿½ï¿½o
		"/prepag2/commerce/finaliza_venda_int.php", 
		"/prepag2/commerce/finaliza_venda_int_cielo.php",			// temporario, para testes de pagto Cielo para integraï¿½ï¿½o
		"/prepag2/commerce/conta/pagto_compr_redirect.php", 
		"/prepag2/commerce/conta/pagto_compr_online.php",
//			"/prepag2/commerce/conta/pagto_compr_online_new.php",	// temporï¿½rio, apenas para os testes desta pï¿½gina
//			"/prepag2/commerce/ajax_pin_pagamento_new.php",			// temporï¿½rio, apenas para os testes desta pï¿½gina
//			"/prepag2/commerce/ajax_pin_pagamento_data_test.php",	// temporï¿½rio, apenas para os testes desta pï¿½gina 
		"/prepag2/commerce/conta/pagto_compr_online_comp.php",
		"/prepag2/commerce/conta/pagto_compr_boleto.php",
		"/prepag2/commerce/conta/pagto_compr_dep_doc_transf.php",
		"/prepag2/commerce/forma_pagto_prz_entrega.php", 
		"/ajax/gamer/ajax_pin_pagamento.php", 
		"/ajax/gamer/ajax_pin_pagamento_data.php", 
		"/ajax/gamer/ajax_login_integracao.php", 
		"/cielo/pages/novoPedidoAguarde.php", 
		"/cielo/pages/retorno.php", 
                "/prepag2/commerce/conta/pagto_informa_dep_doc_transf.php",     // informa  dados do depï¿½sito off line
                "/prepag2/commerce/conta/pagto_informa_dep_doc_transfConf.php", // confirma os dados do depï¿½sito off line
                "/prepag2/commerce/conta/pagto_informa_dep_doc_transfEf.php",   // salva os dados informados depï¿½sito off line
                "/prepag2/commerce/conta/lista_vendas.php",                     // lista pedido depï¿½sito off line
		);
	$b_script_allowed = in_array($GLOBALS['_SERVER']['SCRIPT_NAME'], $a_urls_allowed_in_integracao);

	if($b_script_allowed) {
		return true;
	} else {
		return false;
	}
}

// Retorna true quando o login extra por ajax na pï¿½gina de pagamentio com Saldo foi feito com sucesso
function b_isIntegracao_logged_in() {
	if(isset($GLOBALS['_SESSION']['integracao_autenticado']) && $GLOBALS['_SESSION']['integracao_autenticado']==1) {
		return true;
	} else {
		return false;
	}
}

// Utiliza em finaliza_vendaEx.php para obter a data de vencimento
// Quando houver feriado, modifica aqui a data de vencimento para garantir # de dias para pagar: BOLETO_MONEY_ITAU_QTDE_DIAS_UTEIS_VENCIMENTO
// DOW	3 dias	4 dias	5 dias	6 dias
// 7	0		0		0		+2
// 1	0		0		+2		+2
// 2	0		+2		+2		+2
// 3	+2		+2		+2		+2
// 4	+2		+2		+2		+2
// 5	+2		+2		+2		+4
// 6	+1		+1		+1		+4
// Estï¿½ montado para "5 dias"
function get_dias_uteis_para_vencimento_boleto($cod_banco, $data1) {
	$dow = date("N", $data1);
	// Usa  5 dias para todos os bancos
	$ndias = 5;
	
	if($dow==7) {	// Dom
		$dias = $ndias;
	} elseif($dow==6) {	// Sab
		$dias = $ndias + 1;
	} else {	// 2F/3F/4F/5F/6F
		$dias = $ndias + 2;
	}
	return $dias;
}

	// Retorna o nï¿½mero de pins solicitados na venda (para todos os modelos)
	function get_qtde_pins($venda_id, &$vgm_qtde, &$vgm_pin_codinterno) {

		$msg = "";

		$vgm_qtde = 0;
		$vgm_pin_codinterno = "";
		//Recupera modelos
		$sql  = "select * from tb_venda_games vg 
					inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
				where vg.vg_id = " . $venda_id;
		$rs_venda_modelos = SQLexecuteQuery($sql);
		if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) $msg = "Nenhum produto encontrado (1ag).\n";
		if($msg == ""){
			//Verifica cada item de cada produto
			while($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
				$vgm_qtde += $rs_venda_modelos_row['vgm_qtde'];
				$vgm_pin_codinterno .= $rs_venda_modelos_row['vgm_pin_codinterno'];
			}
		}
		return $vgm_qtde;
	}

// $iNumericType 
//	0 - any char type
//	1 - only numbers
//	2 - only chars
//	3 - alphanumeric
function is_csv_numeric_global($list, $iNumericType = 1) {
        $list1 = str_replace(" ", "", $list);
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

                                // Allows for undescore in alphanumeric
                                $aValid = array('_');	//array('-', '_');
                                if(!$bret) {
                                        $bret = ctype_alnum(str_replace($aValid, '', $val));
                                }  
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

function gravaLog_DebugTMP($mensagem){
	//Arquivo
	$file = RAIZ_DO_PROJETO . "log/log_DebugTMP.txt";

	//Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 
}

function gravaLog_DRUPAL_TMP($mensagem){
	//Arquivo
	$file = RAIZ_DO_PROJETO . "log/log_DRUPAL_TMP.txt";

	//Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 
}

//funï¿½ï¿½o que retrono o IP de acesso
function retorna_ip_acesso_new() {
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
}//end function retorna_ip_acesso_new()

// Livrodjx has been here
if(!function_exists("convert_secs_to_string_global")) {
	function convert_secs_to_string_global($n) {
		$sout = "";
		$ndays = 0;
		$nhours = 0;
		$nmins = 0;
		$nsecs = 0;
		$ndays = intval($n/(60*60*24));
		$nhours = intval(($n-$ndays*60*60*24)/(60*60));
		$nmins = intval(($n-$ndays*60*60*24-$nhours*60*60)/(60));
		$nsecs = intval(($n-$ndays*60*60*24-$nhours*60*60-$nmins*60));
		$sout .= "<font size='1'>";
		$sout .= (($ndays>0)?$ndays."<font color='#FF0000'>d</font>":"");
		$sout .= (($ndays>0 || $nhours>0)?$nhours."<font color='#FF0000'>h</font>":"");
		$sout .= (($ndays>0 || $nhours>0 || $nmins>0)?$nmins."<font color='#FF0000'>m</font>":"");
		$sout .= (($ndays>0 || $nhours>0 || $nmins>0 || $nsecs>0)?$nsecs."<font color='#FF0000'>s</font>":"");
		$sout .= "</font>";

		return $sout;
	}
}
/*
//Funï¿½ï¿½o que retorna a quantidade mï¿½xima de PINs permitidos nos pagamentos
function PagamentoNumeroMaximoPIN() {
	return 5;
}
*/

// $stipo: 
//	'boleto' - texto total
//	'pagto' - texto parcial
function get_msg_bloqueio($stipo) {
	$smsg = "";
	$smsg .= "<style>\n";
	$smsg .= ".notice {font-family:arial, verdana, sans serif;color:#1F3682; font-size:12px}\n";
	$smsg .= "</style>\n";
	$smsg .= "<p class='notice'>O pagamento de produtos Ongame pelo site da E-Prepag ï¿½ feito somente por <a href='http://www.e-prepag.com.br/prepag2/newhome/eppcash.php?secao=onde-comprar-eprepag-cash' target='_blank' class='notice'>E-Prepag Cash</a></p>\n";
	$smsg .= "<p class='notice'>&nbsp;</p>\n";
	$smsg .= "<p class='notice'><b>Vocï¿½ pode adquirir E-Prepag Cash da seguinte forma:</b></p>\n";
	$smsg .= "<p class='notice'>&nbsp;</p>\n";
	$smsg .= "<center>\n";
	$smsg .= "<table border='0'><tr>\n";
	$smsg .= "<td><a href='http://www.e-prepag.com.br/eprepag/moedavirtual/lan_houses_geral.php' target='_blank'><img src='images/botao_lans.gif' width='140' height='40' border='0' alt='E-Prepag Lan Houses' title='E-Prepag Lan Houses'></a></td>\n";
	$smsg .= "<td>&nbsp;&nbsp;&nbsp;</td>";
	$smsg .= "<td><a href='http://www.e-prepag.com.br/prepag2/newhome/eppcash.php?secao=onde-comprar-eprepag-cash' target='_blank'><img src='images/ponto_certo.gif' width='136' height='36' border='0' alt='Rede Ponto Certo' title='Rede Ponto Certo'></a></td>\n";
	$smsg .= "</tr></table>";
	$smsg .= "</center>\n";
	$smsg .= "<p class='notice'>&nbsp;</p>\n";
	if($stipo == "boleto") {
		$smsg .= "<p class='notice'><b>Se vocï¿½ jï¿½ tem um PIN E-Prepag Cash:</b></p>\n";
		$smsg .= "<ol>\n";
		$smsg .= "<li class='notice'>Escolha o valor desejado</li>\n";
		$smsg .= "<li class='notice'>Faï¿½a seu login ou cadastro na E-Prepag</li>\n";
		$smsg .= "<li class='notice'>Conclua a compra escolhendo o E-Prepag Cash para pagar</li>\n";
		$smsg .= "</ol>\n";
	}
	$smsg .= "<p class='notice'>&nbsp;</p>\n";
	$smsg .= "<p class='notice'><b>Saiba mais sobre o E-Prepag Cash</b></p>\n";
	$smsg .= "<p class='notice'><a href='http://www.e-prepag.com.br/prepag2/newhome/eppcash.php?secao=o-que-e-eprepag-cash' target='_blank'><img src='images/epp_cash.gif' width='92' height='35' border='0' alt='E-Prepag' title='E-Prepag'></a></p>\n";
	return $smsg;
}

function get_msg_bloqueio_elex() {
	$smsg = "";
	$smsg .= "<style>\n";
	$smsg .= ".notice {font-family:arial, verdana, sans serif;color:#7e7e7e; font-size:12px}\n";
	$smsg .= "</style>\n";
	$smsg .= "<p class='notice'>&nbsp;</p>\n";
	$smsg .= "<p class='notice'><b>Adquira EPP Cash em um Ponto de Venda abaixo ou, caso jï¿½ possua o PIN, clique no botï¿½o E-Prepag Cash ao lado:</b></p>\n";
	$smsg .= "<p class='notice'>&nbsp;</p>\n";
	$smsg .= "<center>\n";
	$smsg .= "<table border='0'><tr>\n";
	$smsg .= "<td><a href='http://www.e-prepag.com.br/eprepag/moedavirtual/lan_houses_geral.php' target='_blank'><img src='images/botao_lans.gif' width='140' height='40' border='0' alt='E-Prepag Lan Houses' title='E-Prepag Lan Houses'></a></td>\n";
	$smsg .= "<td>&nbsp;&nbsp;&nbsp;</td>";
	$smsg .= "<td><a href='http://www.e-prepag.com.br/prepag2/newhome/eppcash.php?secao=onde-comprar-eprepag-cash' target='_blank'><img src='images/ponto_certo.gif' width='136' height='36' border='0' alt='Rede Ponto Certo' title='Rede Ponto Certo'></a></td>\n";
	$smsg .= "</tr></table>";
	$smsg .= "</center>\n";
	$smsg .= "<p class='notice'>&nbsp;</p>\n";
	$smsg .= "<p class='notice'>&nbsp;</p>\n";
	$smsg .= "<p class='notice'><b>Saiba mais sobre o E-Prepag Cash</b></p>\n";
	$smsg .= "<p class='notice'><a href='http://www.e-prepag.com.br/prepag2/newhome/eppcash.php?secao=o-que-e-eprepag-cash' target='_blank'><img src='images/epp_cash.gif' width='92' height='35' border='0' alt='E-Prepag' title='E-Prepag'></a></p>\n";
	return $smsg;
}

function get_opr_codigo_by_prod_id($prod_id){
	$opr_codigo = 0;
	$prod_id = str_replace(" ", "", $prod_id);
	if(!is_numeric($prod_id)) {
		return $opr_codigo;   	
	}
	if(!($prod_id>0)) {
		return $opr_codigo;   	
	}
	//SQL
	$sql = "select ogp_opr_codigo from tb_operadora_games_produto where ogp_id  = ".$prod_id . "";	//" and ogp_ativo = 1";
	$rs = SQLexecuteQuery($sql);
	if($rs && pg_num_rows($rs) > 0){
		$rs_row = pg_fetch_array($rs);
		if($rs_row['ogp_opr_codigo'] > 0) {
			$opr_codigo = $rs_row['ogp_opr_codigo'];
		}
	}		
	return $opr_codigo;
}

function get_opr_codigo_by_modelo_id($modelo_id){
	$opr_codigo = 0;
	$modelo_id = str_replace(" ", "", $modelo_id);
	if(!is_numeric($modelo_id)) {
		return $opr_codigo;   	
	}
	if(!($modelo_id>0)) {
		return $opr_codigo;   	
	}
	//SQL
	$sql = "select ogp_opr_codigo, * from tb_operadora_games_produto where ogp_id  = (select ogpm_ogp_id from tb_operadora_games_produto_modelo where ogpm_id  = ".$modelo_id." limit 1)";	//" and ogp_ativo = 1";
	$rs = SQLexecuteQuery($sql);
	if($rs && pg_num_rows($rs) > 0){
		$rs_row = pg_fetch_array($rs);
		if($rs_row['ogp_opr_codigo'] > 0) {
			$opr_codigo = $rs_row['ogp_opr_codigo'];
		}
	}		
	return $opr_codigo;
}

function get_carrinho_com_produtos_ongame(){
	$b_carrinho_com_Ongame = false;
	// Se carrinho contem algum produto da Ongame -> bloqueia
	$carrinho = $GLOBALS['_SESSION']['carrinho'];
	foreach($carrinho as $key => $val) {
		$modelo_id = $key;
		$opr_codigo = get_opr_codigo_by_modelo_id($modelo_id);
		if($opr_codigo==13) {
			$b_carrinho_com_Ongame = true;
		}
	}
	return $b_carrinho_com_Ongame;
}

function get_info_EPPCash($valor_eppcash,$id_DIV = false){
	$sret = "";
	$sret .= "<table border='0'>\n";
	$sret .= "<tr>\n";
	$sret .= "<td align='center' valign='middle'><div ";
	if($id_DIV)
		$sret .= "id='divTotalEPP' ";
	$sret .= "style='color:darkgreen;font-weight:bold;font-size:12px'>".number_format($valor_eppcash, 0, ',', '.')."</div></td>\n";
//	$sret .= "<td>&nbsp;</td>\n";
	$sret .= "<td align='center' valign='middle'><img src='http://www.e-prepag.com.br/prepag2/commerce/images/EPPCash_logo.gif' width='38' height='17' border='0' alt='EPPCash' title='EPPCash'></td>\n";
	$sret .= "</tr>\n";
	$sret .= "</table>\n";
	return $sret;
}

function get_info_EPPCash_NO_Table($valor_eppcash,$id_DIV = false){
	$sret = "";
	if($id_DIV)
		$sret .= "<div id='divTotalEPP' style='color:darkgreen;font-weight:bold;font-size:11px'>".number_format($valor_eppcash, 0, ',', '.')."</div>\n";
	else $sret .= "<font style='color:darkgreen;font-weight:bold;font-size:11px'>".number_format($valor_eppcash, 0, ',', '.')."</font>\n";
	$sret .= "<img src='/imagens/eppcash_mini.png' width='30' height='17' border='0' alt='EPPCash' title='EPPCash' style='vertical-align: middle'>\n";
	return $sret;
}

function getSingleValue($sql) {

	$ret = null;
	$rs = SQLexecuteQuery($sql);
	if($rs && pg_num_rows($rs) > 0){
		$rs_row = pg_fetch_array($rs);
		 $ret = $rs_row[0];
	}			
	return $ret;   	
}

//Funï¿½ï¿½o de Conversï¿½o da data
function converteData($data_nasc) {
	if (strstr($data_nasc, "/")) {
		  $data_array = explode ("/", $data_nasc);
		  return $data_array[2] . "-". $data_array[1] . "-" . $data_array[0];
	} else {
		return null;
	}
}//end function converteData

//Funï¿½ï¿½o que verifica se o publisher exige CPF do Gamer
function checkingNeedCPFGamer($opr_codigo) {
    $sql_function ="SELECT opr_need_cpf_lh from operadoras where opr_codigo=".intval($opr_codigo).";";
    $rs_function = SQLexecuteQuery($sql_function);
    if($rs_function_row = pg_fetch_array($rs_function)) {
            $opr_need_cpf_lh = $rs_function_row['opr_need_cpf_lh'];
    }
    return $opr_need_cpf_lh;
}//end function checkingNeedCPFGamer

function verifica_data_rc($data) {
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

function formata_data_rc($data,$gravar) {
	$mask = $data;
	if($gravar == 0)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,0,4);
		$doc = $dia."/".$mes."/".$ano;
	}
	
	if($gravar == 1)
	{
		$dia = substr($mask,0,2);
		$mes = substr($mask,3,2);
		$ano = substr($mask,6,4);
		$doc = $ano."-".$mes."-".$dia;
	}
	return $doc;
}

?>

 