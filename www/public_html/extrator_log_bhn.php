<?php  
die("Acesso Negado!");
require_once '/www/includes/constantes.php';
require_once '/www/includes/configIP.php';
require_once '/www/includes/main.php';
require_once '/www/includes/pdv/main.php';
require_once '/www/includes/pdv/functions_vendaGames.php';
require_once '/www/includes/inc_Pagamentos.php';
require_once '/www/includes/gamer/functions_pagto.php';
require_once '/www/includes/pdv/functions_vendaGames_pag_online.php';
require_once '/www/banco/pix/blupay/config.inc.pix.php'; 

conciliacaoAutomaticaPagtoPIXemPDV();
die("<br>AQUI 7897898");

session_start();
$teste = new classPIX();

//Bloco que vai para p·gina de checkout
$params = array (
    'metodo'    => PIX_SONDA,
    'idpedido'  => $ARRAY_CONCATENA_ID_VENDA['pdv'].'0000000'."20210322100624671"
);
echo $teste->callSonda($params,$resposta);
echo "<pre>".print_r($resposta,true)."</pre>";
die("Acesso Negado!");
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
//ini_set('memory_limit', '-1');

set_time_limit(18000);

define('WINDOWS', 1);
define('LINUX', 0);

// Colocar aqui o nome do arquivo a ser processado
$arquivo_a_ser_processado = "../arquivos_gerados/csv/log_BHN_WS-Errors_2018_09_06.LOG";

// Ambiente onde o arquivo foi Gerado
$ambiente = WINDOWS;

//ID do produto a ser verificado
$product_id = "505164402135";

//Arquivo que receber· o resultado do processamento
$arquivo_destino = "../arquivos_gerados/csv/arquivo_PINs.csv";

echo "<html>Tamanho do arquivo a ser processado: ".number_format(filesize($arquivo_a_ser_processado),0,".",".")." bytes<br>";

$string = file_get_contents($arquivo_a_ser_processado);

$vetor_nivel1 = explode("SQL Busca Pedido para TRANSACTION:",$string);

echo "Qtde posicoes vetor Nivel 1: ". count($vetor_nivel1)."<br>";//.$vetor_nivel1[0]; die();

if($ambiente) 
    $search = "INFO da CONSULTA: Array".PHP_EOL."(".PHP_EOL."    [url] => https://bhnfake2.xxx:442/transactionManagement/v2/transaction".PHP_EOL."  ";
else
    $search = "INFO da CONSULTA: Array".PHP_EOL."(".PHP_EOL."    [url] => https://webpos.blackhawk-net.com:8443/transactionManagement/v2/transaction".PHP_EOL."  ";

$i = 1;
$conteudo_arquivo = "PRODUCT_ID;retrievalReferenceNumber;systemTraceAuditNumber;PIN".PHP_EOL;
foreach ($vetor_nivel1 as $key => $value) {
    
    $vetor_nivel2 = explode("FUN«√O sendXML no curl_exec:https://bhnfake2.xxx:442/transactionManagement/v2/transaction  is reachable".PHP_EOL."Resultado: ",$value);

    //echo "vetor Nivel 2 :<pre>".print_r($vetor_nivel2,true)."</pre><br>";
    foreach ($vetor_nivel2 as $key2 => $value2) {
        
        $XML = NULL;
        
        $isXML = strpos($value2, $search);
        //var_dump($isXML); 
        if($isXML) {
            //if($key == 453) { echo "-- ".$value2; };            
            $XML = substr($value2,0,$isXML);
            //echo "Entontrou XML: ".$XML;

            //Removendo acentos
            $XML = strtr($XML, "¡Õ”⁄…ƒœ÷‹À¿Ã“Ÿ»√’¬Œ‘€ ·ÌÛ˙È‰Ôˆ¸Î‡ÏÚ˘Ë„ı‚ÓÙ˚Í«Á", 
                                "AIOUEAIOUEAIOUEAOAIOUEaioueaioueaioueaoaioueCc");
            //Removendo &(e comercial)
            $XML = str_replace("&", "&amp;", $XML);

            //Removendo ©(copyright)
            $XML = str_replace(chr(169), "copyright", $XML);

            //Removendo Æ(registrado)
            $XML = str_replace(chr(174), "Registered trademark", $XML);

            //Removendo \n
            $XML = str_replace("\\n", "", $XML);

            //Removendo \f0
            $XML = str_replace("\\f0", "", $XML);

            //Removendo \a2
            $XML = str_replace("\\a2", "", $XML);

            //Removendo \ (contrabarra)
            $XML = str_replace("\\", "", $XML);

            //if($key == 453) { echo "** ".$XML; die("AKII4"); }
            
            libxml_use_internal_errors (true);
            if(simplexml_load_string($XML)) {
                $aux = new SimpleXMLElement($XML);

                //echo "XML Carregado: <pre>".print_r($aux,true)."</pre>";
                if($aux->transaction->additionalTxnFields->productId == $product_id) {
                    echo "<br>Contador [$i] ID [$key] PRODUCT ID: [".$aux->transaction->additionalTxnFields->productId."] - PIN: [Somente No arquivo]";
                    $conteudo_arquivo .= "'".$aux->transaction->additionalTxnFields->productId."';'".$aux->transaction->retrievalReferenceNumber."';'".$aux->transaction->systemTraceAuditNumber."';".$aux->transaction->additionalTxnFields->redemptionAccountNumber.PHP_EOL;
                    $i++;
                }
            }//end if(simplexml_load_string($XML))

        }//end if($isXML)
    }//end foreach nivel 2
}//and foreach nivel 1

//Grava mensagem no arquivo
if ($handle = fopen($arquivo_destino, 'w+')) {
    fwrite($handle, $conteudo_arquivo);
    fclose($handle);
}
echo "</html>";
?>