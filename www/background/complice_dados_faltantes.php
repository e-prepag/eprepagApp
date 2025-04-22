<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
// Levantamento de CPF e Nome não cadastrados
// complice_dados_faltantes.php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//=========  Mês/Ano
$diaLimite = 10;
if((int)date('j') <= (int)$diaLimite) {
    $currentmonth = mktime(0, 0, 0, date('n')-1, 1, date('Y'));
} //end if(date('j') <= 10)
else {
    $currentmonth = mktime(0, 0, 0, date('n'), 1, date('Y'));
} //end else do if(date('j') <= 10)
$mesAno = date('m/Y',$currentmonth);

// Split ano/mes
list($mes, $ano) = explode("/", $mesAno);

// Dados do Email
$email  = "tamy@e-prepag.com.br,atendimento1@e-prepag.com.br,rc@e-prepag.com.br";
$cc     = "glaucia@e-prepag.com.br,joao.trevisan@e-prepag.com.br";
$bcc    = "wagner@e-prepag.com.br";
$subject= "Dados Faltantes CPF/Nome para Compliance";
$msg    = "";

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 
include_once $raiz_do_projeto . "includes/complice/functions.php";

$time_start_stats = getmicrotime();

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Verificação de Dados Faltantes para Compliance (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

//Publishers Já em Operação constantes em arquivos BACEN anteriores
$vetorPublisher = levantamentoPublisherOperantes($ano,$mes);

//Publishers novos nunca antes contou nos arquivos BACEN
$vetorPublisherNovos = levantamentoPublisherNovosOperantes($ano,$mes);

//Verificando se faltam Dados
if (verificaFaltaCPFNome($vetorPublisher, $diaLimite, $rs_dados_incompletos, $vetorPublisherNovos)) {
    $msg .= "Faltam Dados de CPF e Nome: (TOTAL [".pg_num_rows($rs_dados_incompletos)."] Usuários)<br>".PHP_EOL;
    while($rs_dados_incompletos_row = pg_fetch_array($rs_dados_incompletos)) {
            $msg .=  " ".$rs_dados_incompletos_row['tipo']." => ID: ".$rs_dados_incompletos_row['ug_id']." Email: ".$rs_dados_incompletos_row['ug_email']." Data da Transação: ".substr($rs_dados_incompletos_row['data_transacao'],0,19)."<br>".PHP_EOL;
    } //end while
} //end if (verificaFaltaCPFNome($rs_dados_incompletos))

//Verificando se o CPF Informado possui uma estrutura correta
$exibicaoDadosProblemas = "";
if (verificaCPFValido($vetorPublisher, $diaLimite, $rs_dados, $vetorPublisherNovos)) {
    $i = 0;
    while($rs_dados_row = pg_fetch_array($rs_dados)) {
        if(!verificaCPF_BACEN($rs_dados_row['ug_cpf'])) {
            $exibicaoDadosProblemas .= " ".$rs_dados_row['tipo']." => ID: ".$rs_dados_row['ug_id']." CPF Inválido: ".$rs_dados_row['ug_cpf']."<br>".PHP_EOL;
            $i++;
        }// end if(!verificaCPF_BACEN($rs_dados_row['ug_cpf']))
    } //end while
    if($i > 0) {
        $msg .=  "Dados de CPF Incorretos: (TOTAL [".$i."] CPFs)<br>";
    }
} //end if (verificaCPFValido())


$msg .= $exibicaoDadosProblemas;
echo str_replace('<br>', PHP_EOL, $msg);

if(!empty($msg)) {
    if(enviaEmail($email, $cc, $bcc, $subject, $msg)) {
        echo "Email enviado com sucesso".PHP_EOL;
    }
    else {
        echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
    }
}//end if(!empty($msg))

echo str_repeat("_", 80) .PHP_EOL."Elapsed time (total: ".count($vetor_ug_id)."): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80).PHP_EOL;

//Fechando Conexão
pg_close($connid);

?>