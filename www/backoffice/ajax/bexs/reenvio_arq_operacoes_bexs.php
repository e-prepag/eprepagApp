<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1);

function isAjax() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}
function block_direct_calling() {
    if(!isAjax()) {
        echo "Chamada não permitida<br>";
        die("Stop");
    }
}
block_direct_calling();
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."banco/bexs/config.inc.bexs.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";


//DATAS NECESSÁRIAS RECUPERADAS NO FORM DO MODAL 
$data_operacao = trim($GLOBALS['_POST']['data_op']);
$data_liquidacao = trim($GLOBALS['_POST']['data_lq']);
$data_moeda_estrangeira = trim($GLOBALS['_POST']['data_me']);
$data_moeda_nacional = trim($GLOBALS['_POST']['data_mn']);

//Informações do publisher
$id_operadora = $GLOBALS['_POST']['dd_operadora'];
$nome_merchant = $GLOBALS['_POST']['nome_merchant'];

//Período considerado na remessa
$data_inic = trim($GLOBALS['_POST']['data_ini']);
$data_fim = trim($GLOBALS['_POST']['data_fim']);

$id_merchant_bexs =trim($GLOBALS['_POST']['merchant_id_bexs']);
$perfil_operacional = trim($GLOBALS['_POST']['perfil_op']);

$valor_total_enviar = $GLOBALS['_POST']['valor_moeda_nacional'];

$array_infos_recuperadas = 
    array
    ( 
        "data_me" => $data_moeda_estrangeira, 
        "data_mn" => $data_moeda_nacional, 
        "data_lq" => $data_liquidacao, 
        "data_ini" => $data_inic,
        "data_fim" => $data_fim,
        "perfil_op" => $perfil_operacional,
        "valor_moeda_nacional" => $valor_total_enviar,
        "data_op" => $data_operacao,
        "dd_operadora" => $id_operadora,
        "tipoop" => BEXS_TIPO_OP,
        "moeda" => BEXS_MOEDA,
        "nome_merchant" => $nome_merchant,
        "merchant_id_bexs" => $id_merchant_bexs
    );

$bexs = new classBexs($id_operadora, $array_infos_recuperadas);

$resultado = "";

if(!$bexs->getStatus()){
    $msg_erro = $bexs->getMsgErro();
    $msg_env_conc = $bexs->getMsgRemessaEnvOuConc();

    $msg = "";
    if($msg_erro != ""){
        if(preg_match("/ERRO:/", $msg_erro)){
            $resultado .= utf8_encode("<strong>ERRO 100</strong>: Problema de campo obrigatório! Um e-mail com o detalhamento do problema está sendo enviado para <i>financeiro@e-prepag.com.br</i>");
            $msg .= "Problema com os dados da remessa referente ao Publisher ". $nome_merchant." de Perfil Operacional ". $perfil_operacional .
            "<br>Período considerado: <strong>". $data_inic. "</strong> até <strong>". $data_fim."</strong><br><br>";

            $assunto = (checkIP()?"[DEV] ":"[PROD] ")."Problema com Dados a serem enviados ao Web Service BEXS";
            $destino = (checkIP()) ? EMAIL_DEV : EMAILS_PROD;
            enviaEmail($destino,null,null, $assunto, $msg.$msg_erro);
        } else{
            $resultado .= utf8_encode($msg_erro);
        }    
    }

    if($msg_env_conc != ""){
        switch ($msg_env_conc){
            case "1":
               $resultado .= utf8_encode("<strong>[INFO]</strong>: As informações da remessa e o arquivo de operações referente a este PUBLISHER neste período já foram enviados com sucesso ao BEXS!<br><br>AGUARDE a resposta do processamento do arquivo de operações que será enviada no e-mail <i>financeiro@e-prepag.com.br</i>"); 
                break;
            case "2":
                $resultado .= utf8_encode("<strong>[INFO]</strong>: As informações da remessa e o arquivo de operações referentes a este PUBLISHER neste período já foram enviados e processados com SUCESSO pelo BEXS!<br><br><strong>REMESSA CONCLUÍDA!</strong>"); 
                break;
            case "3":
                $resultado .=utf8_encode("<strong>ERRO 607</strong>: O processamento da remessa de ID <strong>".str_replace(".zip", "", $bexs->getnomeArquivoZip())."</strong> já deveria ter sido realizado pelo BEXS.<br>Possível problema com a resposta. Por favor, entre em contato com o BEXS informando o ID acima para maiores esclarecimentos.");
                break;
        }
    }   

} //end if(!$bexs->getStatus())

//Enviar arquivo SFTP
if($bexs->getStatus() && $bexs->need_envio_sFTP()) {
    $resultado .= $bexs->envio_sFTP($need_req_Web_Service);
}

echo $resultado;
