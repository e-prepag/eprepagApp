<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
// Desbloqueio Automático de Gamers e PINs EPP CASH
// destravamentoAutomaticoGamersPINs.php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
$time_start_stats = getmicrotime();

$arquivoLog = new ManipulacaoArquivosLog($argv);
if(!$arquivoLog->haveFile()) {
    
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();
    ob_start('callbackLog');

    // Dados do Email
    $email  = "suporte@e-prepag.com.br";
    $cc     = "wagner@e-prepag.com.br,glaucia@e-prepag.com.br";
    $bcc    = "";
    $subject= "Desbloqueio Automático de Gamers e PINs EPP CASH";
    $msg    = "";


    //Capturando o argumento minutos
    $shellcomm = implode(' ', $argv);
    $minutes = 30;
    $debug = false;
    $matches = array();
    if( preg_match('/--minutes=([0-9]{1,})/', $shellcomm, $matches) )
            $minutes = $matches[1];

  //  echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Desbloqueio Automático de Gamers e PINs EPP CASH (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

    // Variavel controladora de envio de Email
    $enviarEmails = false;

    if (verificaBloqueiosGamers($minutes, $rs_dados_bloqueios_gamers)) {
        $enviarEmails = true;
        $msg .= PHP_EOL." Quantidade de usuários Bloqueados: (TOTAL [".pg_num_rows($rs_dados_bloqueios_gamers)."] usuários)<br><br> ".PHP_EOL;
        $listaIDSDesbloqueio = null;
        while($rs_dados_bloqueios_gamers_row = pg_fetch_array($rs_dados_bloqueios_gamers)) {
                $msg .=  " ID GAMER: [".$rs_dados_bloqueios_gamers_row['ug_id']."] => Hora Bloqueio: [".substr($rs_dados_bloqueios_gamers_row['maior'],0,19)."] => Limite para desbloqueio < [".substr($rs_dados_bloqueios_gamers_row['limite'],0,19)."] <br> ".PHP_EOL;
                if(empty($listaIDSDesbloqueio))
                    $listaIDSDesbloqueio = $rs_dados_bloqueios_gamers_row['ug_id'];
                else $listaIDSDesbloqueio .= ", ".$rs_dados_bloqueios_gamers_row['ug_id'];
        } //end while
        $sql = "update usuarios_games set ug_flag_usando_saldo = 0 where ug_id IN (".$listaIDSDesbloqueio.");";
        //echo $sql.PHP_EOL;
        $rs_dados_desbloqueios = SQLexecuteQuery($sql);
        if($rs_dados_desbloqueios)
            $msg .=  "Sucesso ao desbloquear os usuários [".$listaIDSDesbloqueio."] <br> ".PHP_EOL.PHP_EOL;
        else $msg .=  "Problemas ao tentar desbloquear os usuários [".$listaIDSDesbloqueio."] <br> ".PHP_EOL.PHP_EOL;
    } //end if (verificaBloqueiosGamers($minutes, $rs_dados_bloqueios_gamers))

    if (verificaBloqueiosPINs($minutes, $rs_dados_bloqueios_gamers)) {
        $enviarEmails = true;
        $msg .= PHP_EOL."<br> Quantidade de PINs Bloqueados: (TOTAL [".pg_num_rows($rs_dados_bloqueios_gamers)."] PINs)<br><br> ".PHP_EOL;
        $listaIDSDesbloqueio = null;
        while($rs_dados_bloqueios_gamers_row = pg_fetch_array($rs_dados_bloqueios_gamers)) {
                $msg .=  " ID PIN: [".$rs_dados_bloqueios_gamers_row['pin_codinterno']."] => Hora Bloqueio: [".substr($rs_dados_bloqueios_gamers_row['maior'],0,19)."] => Limite para desbloqueio < [".substr($rs_dados_bloqueios_gamers_row['limite'],0,19)."] <br> ".PHP_EOL;
                if(empty($listaIDSDesbloqueio))
                    $listaIDSDesbloqueio = $rs_dados_bloqueios_gamers_row['pin_codinterno'];
                else $listaIDSDesbloqueio .= ", ".$rs_dados_bloqueios_gamers_row['pin_codinterno'];
        } //end while
        $sql = "update pins_store set pin_bloqueio = 0 where pin_codinterno IN (".$listaIDSDesbloqueio.");";
        //echo $sql.PHP_EOL;
        $rs_dados_desbloqueios = SQLexecuteQuery($sql);
        if($rs_dados_desbloqueios)
            $msg .=  "Sucesso ao desbloquear os PINs [".$listaIDSDesbloqueio."] <br> ".PHP_EOL.PHP_EOL;
        else $msg .=  "Problemas ao tentar desbloquear os PINs [".$listaIDSDesbloqueio."] <br> ".PHP_EOL.PHP_EOL;
    } //end if (verificaBloqueiosPINs($minutes, $rs_dados_bloqueios_gamers))

    if (verificaBloqueiosPINsCards($minutes, $rs_dados_bloqueios_gamers)) {
        $enviarEmails = true;
        $msg .= PHP_EOL."<br> Quantidade de PINs CARDs Bloqueados: (TOTAL [".pg_num_rows($rs_dados_bloqueios_gamers)."] PINs)<br><br> ".PHP_EOL;
        $listaIDSDesbloqueio = null;
        while($rs_dados_bloqueios_gamers_row = pg_fetch_array($rs_dados_bloqueios_gamers)) {
                $msg .=  " ID PIN CARD: [".$rs_dados_bloqueios_gamers_row['pin_codinterno']."] => Hora Bloqueio: [".substr($rs_dados_bloqueios_gamers_row['maior'],0,19)."] => Limite para desbloqueio < [".substr($rs_dados_bloqueios_gamers_row['limite'],0,19)."] <br> ".PHP_EOL;
                if(empty($listaIDSDesbloqueio))
                    $listaIDSDesbloqueio = $rs_dados_bloqueios_gamers_row['pin_codinterno'];
                else $listaIDSDesbloqueio .= ", ".$rs_dados_bloqueios_gamers_row['pin_codinterno'];
        } //end while
        $sql = "update pins_card set pin_bloqueio = 0 where pin_codinterno IN (".$listaIDSDesbloqueio.");";
        //echo $sql.PHP_EOL;
        $rs_dados_desbloqueios = SQLexecuteQuery($sql);
        if($rs_dados_desbloqueios)
            $msg .=  "Sucesso ao desbloquear os PINs CARDs [".$listaIDSDesbloqueio."] <br> ".PHP_EOL.PHP_EOL;
        else $msg .=  "Problemas ao tentar desbloquear os PINs CARDs [".$listaIDSDesbloqueio."] <br> ".PHP_EOL.PHP_EOL;
    } //end if (verificaBloqueiosPINsCards($minutes, $rs_dados_bloqueios_gamers))

   // echo str_replace("<br>", PHP_EOL, $msg);

    if($enviarEmails) {
        if(enviaEmail($email, $cc, $bcc, $subject, $msg)) {
            //echo "Email enviado com sucesso".PHP_EOL;
        }
        else {
          //  echo "Problemas no envio do Email".PHP_EOL." TO: ".$email.PHP_EOL." CC: ".$cc.PHP_EOL." BCC: ".$bcc.PHP_EOL." SUBJECT: ".$subject.PHP_EOL;
        }
    }//end if($enviarEmails)

   // echo str_repeat("_", 80) . PHP_EOL."Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) .PHP_EOL;
    
    /**
     * Fim do Procedimento
     */
    
    $arquivoLog->deleteLockedFile();
}
else {
        $arquivoLog->showBusy();
}

//Fechando Conexï¿½o
pg_close($connid);


// Funï¿½ï¿½o que verifica bloqueio de Gamers
function verificaBloqueiosGamers($minutos,&$rs_dados_bloqueios) {

        // Buscando informaï¿½ï¿½es 
        $sql = "select ug_id, max(ughb_data) as maior,(NOW()- '".$minutos." minutes'::interval) as limite
                from usuarios_games 
                        inner join usuarios_games_historico_bloqueio ON (ug_id = ughb_ug_id)
                where ughb_ug_flag_usando_saldo=1 and
                        ug_flag_usando_saldo=1 
                group by ug_id
                having max(ughb_data) < (NOW()- '".$minutos." minutes'::interval)
                order by maior desc,ug_id
                ";

        //echo $sql.PHP_EOL; 
        $rs_dados_bloqueios = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_dados_bloqueios)."<br>";
        if(!$rs_dados_bloqueios) {
           // echo "Erro na Query de Levantamento de Desbloqueio Automático de Gamers.<br>".PHP_EOL;
            return false;
        }
        if(pg_num_rows($rs_dados_bloqueios) == 0) {
            //echo "Vai retorna Falso. Ou seja, Nï¿½O possui Gamers Bloqueados.<br>";
            return false;
        }//end if(!$rs_dados_bloqueios || pg_num_rows($rs_dados_bloqueios) == 0)
        else {
            //echo "Vai retorna verdadeiro. Ou seja, possui Gamers Bloqueados.<br>";
            return true;
        }//end else
    
}//end function verificaBloqueiosGamers


// Funï¿½ï¿½o que verifica bloqueio de PINs
function verificaBloqueiosPINs($minutos,&$rs_dados_bloqueios) {

        // Buscando informaï¿½ï¿½es 
        $sql = "select pin_codinterno, max(psdhb_data) as maior,(NOW()- '".$minutos." minutes'::interval) as limite
                from pins_store 
                        inner join pins_store_db_historico_bloqueio ON (pin_codinterno = psdhb_pin_codinterno)
                where psdhb_pin_bloqueio=1 and
                        pin_bloqueio=1 
                group by pin_codinterno
                having max(psdhb_data) < (NOW()- '".$minutos." minutes'::interval)
                order by pin_codinterno
                ";

        //echo $sql.PHP_EOL; 
        $rs_dados_bloqueios = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_dados_bloqueios)."<br>";
        if(!$rs_dados_bloqueios) {
           // echo "Erro na Query de Levantamento de Desbloqueio Automático de PINs.<br>".PHP_EOL;
            return false;
        }
        if(pg_num_rows($rs_dados_bloqueios) == 0) {
            //echo "Vai retorna Falso. Ou seja, Nï¿½O possui PINs Bloqueados.<br>";
            return false;
        }//end if(!$rs_dados_bloqueios || pg_num_rows($rs_dados_bloqueios) == 0)
        else {
            //echo "Vai retorna verdadeiro. Ou seja, possui PINs Bloqueados.<br>";
            return true;
        }//end else
    
}//end function verificaBloqueiosPINs

// Funï¿½ï¿½o que verifica bloqueio de PINs Cards
function verificaBloqueiosPINsCards($minutos,&$rs_dados_bloqueios) {

        // Buscando informaï¿½ï¿½es 
        $sql = "
                select pin_codinterno, max(pcdhb_data) as maior,(NOW()- '".$minutos." minutes'::interval) as limite
                from pins_card
                        inner join pins_card_db_historico_bloqueio ON (pin_codinterno = pcdhb_pin_codinterno)
                where pcdhb_pin_bloqueio=1 and
                        pin_bloqueio=1
                group by pin_codinterno
                having max(pcdhb_data) < (NOW()- '".$minutos." minutes'::interval);
                ";
        //echo $sql.PHP_EOL; 
        $rs_dados_bloqueios = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_dados_bloqueios)."<br>";
        if(!$rs_dados_bloqueios) {
           // echo "Erro na Query de Levantamento de Desbloqueio Automático de PINs.<br>".PHP_EOL;
            return false;
        }
        if(pg_num_rows($rs_dados_bloqueios) == 0) {
            //echo "Vai retorna Falso. Ou seja, Nï¿½O possui PINs Bloqueados.<br>";
            return false;
        }//end if(!$rs_dados_bloqueios || pg_num_rows($rs_dados_bloqueios) == 0)
        else {
            //echo "Vai retorna verdadeiro. Ou seja, possui PINs Bloqueados.<br>";
            return true;
        }//end else
    
}//end function verificaBloqueiosPINsCards


/*

  */
?>