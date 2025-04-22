<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 
$time_start_stats = getmicrotime();

require_once $raiz_do_projeto."class/business/VendasLanHouseBO.class.php";

$totime = strtotime("-7 days");
$ontem = date("Y-m-d",$totime);
//$ontem = "2015-08-03";
//die($ontem);


$vendasBO = new VendasLanHouseBO;
$vendasExcedentes = $vendasBO->getPrimeiraVenda($ontem, $ontem);


echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Data da Execução do Script: ".date("Y-m-d").PHP_EOL."Verificacao de Lan Houses que excederam o valor diario de compras, em sua primeira compra, referente ao dia {$ontem}.".PHP_EOL.PHP_EOL;

if(!empty($vendasExcedentes)){
    // Dados do Email
    $email  = "rc@e-prepag.com.br";//"tamy@e-prepag.com.br,atendimento1@e-prepag.com.br,everton.almeida@e-prepag.com.br";
    $cc     = "";//"glaucia@e-prepag.com.br,joao.trevisan@e-prepag.com.br";
    $bcc    = "wagner@e-prepag.com.br, felipe.freire@e-prepag.com.br";
    $subject= "Relatório de Lan Houses que excederam o valor diário de compras na primeira compra";
    $msg    = "";

    
    ############################################################################
    

    $msg = '
    <table width="894" border="0" cellpadding="0" cellspacing="2">
        <tr bgcolor="#00008C"> 
            <td height="11" colspan="4" bgcolor="#FFFFFF" class="texto"> 
                    <strong>EXCEDERAM O LIMITE DIÁRIO!!<span id="txt_totais"></span></strong>
            </td>
        </tr>
        <tr bgcolor="#ECE9D8"> 
            <td align="center">
                    <strong><font class="texto">ID</strong>
            </td>
            <td align="center">
                    <strong><font class="texto">Nome</strong>
            </td>
            <td align="center">
                    <strong><font class="texto">Valor Total</strong>
            </td>
            <td align="center">
                <strong><font class="texto">Classificação</strong>
            </td>
            <td align="center">
                    <strong><font class="texto">Data</strong>
            </td>
        </tr>';
            
    $cont = 0;    
    foreach($vendasExcedentes as $ind => $venda){

        $tipouser = $venda['lan_house']->getVip();
        $valor = $venda['venda']->getValor();
        
        if(isset($tipouser) && $tipouser == 1)
            $status = "VIP"; //$limiteDiario = $GLOBALS['RISCO_LANS_PRE_VIP_TOTAL_DIARIO'];
        else if(isset($tipouser) && $tipouser == 2)
            $status = "MASTER"; //$limiteDiario = $GLOBALS['RISCO_LANS_PRE_MASTER_TOTAL_DIARIO'];
        else
            $status = "NORMAL";//$limiteDiario = $GLOBALS['RISCO_LANS_PRE_TOTAL_DIARIO'];
          
        if($valor <= $GLOBALS['RISCO_LANS_PRE_TOTAL_DIARIO'])
            continue;
        
        $cont++;
        
        echo "ID: ".$venda['venda']->getCodUsuario()." - Nome: ".$venda['lan_house']->getNome()." - Valor: ".number_format($valor, 2, ',', '.')." - Data Considerada: ".$venda['venda']->getDataInclusao().PHP_EOL;
        
        $msg .= 
        '<tr bgcolor="#CCFFFF"> 
            <td nowrap valign="top" class="texto" align="center">
                '.$venda['venda']->getCodUsuario().'
            </td>
            <td nowrap valign="top" class="texto" align="center">
                '.$venda['lan_house']->getNome().'
            </td>
            <td nowrap valign="top" class="texto" align="right">
                '.number_format($valor, 2, ',', '.').'
            </td>
            <td nowrap valign="top" class="texto" align="right">
                '.$status.'
            </td>
            <td nowrap valign="top" class="texto" align="right">
                '.$venda['venda']->getDataInclusao().'
            </td>
        <tr>';
    
    }//end foreach
    $msg .= "</table>";
    
    
    
    if($cont>0) {
        if(enviaEmail($email, $cc, $bcc, $subject, $msg)) {
            echo "Email enviado com sucesso".PHP_EOL;
        }
        else {
            echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
        }
    }//end if(!empty($msg))
    
    
    echo str_repeat("_", 80) .PHP_EOL."Elapsed time (total: {$cont}): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80).PHP_EOL;

    //Fechando Conexão
    pg_close($connid);
}else{
    echo "NENHUM REGISTRO ENCONTRADO.";
}