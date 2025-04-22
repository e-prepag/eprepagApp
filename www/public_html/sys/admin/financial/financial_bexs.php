<?php

function isAjax() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}
function block_direct_calling() {
    if(!isAjax()) {
        echo "Chamada n�o permitida<br>";
        die("Stop");
    }
}
block_direct_calling();
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . 'banco/bexs/config.inc.bexs.php';
require_once $raiz_do_projeto . 'class/main.php';
require_once $raiz_do_projeto . 'class/gamer/main.php';

/*-----------------------DADOS IMPORTANTES CAPTURADOS DO $_POST------------------------------------------
// $_POST['data_operacao'] = data da opera��o da remessa                                                 |
// $_POST['data_liquidacao'] = data da liquida��o da remessa                                             |
// $_POST['data_me'] = data da moeda estrageira da remessa                                               |
// $_POST['data_mn'] = data da moeda nacional da remessa                                                 |
// $_POST['merchant_id_bexs'] = id do publisher no bexs                                                  |
// $_POST['tf_data_inicial'] = data inicial                                                              |
// $_POST['tf_data_final'] = data final                                                                  |
// $_POST['dd_operadora'] = id operadora                                                                 |
// $_POST['grosswired'] = valor total em vendas                                                          |
// $_POST['netwired'])) = valor total a ser enviado (TOTAL - COMISSAO - IOF)                             |
// $_POST['facilitadora_perfil_op'] = perfil operacional criado pelo BEXS de acordo com o PUBLISHER      |
---------------------------------------------------------------------------------------------------------
*/

$msg_erro_ids = "";

//DATAS NECESS�RIAS RECUPERADAS NO FORM DO MODAL 
$data_operacao = trim($GLOBALS['_POST']['data_operacao']);
$data_liquidacao = trim($GLOBALS['_POST']['data_liquidacao']);
$data_moeda_estrangeira = trim($GLOBALS['_POST']['data_me']);
$data_moeda_nacional = trim($GLOBALS['_POST']['data_mn']);

//Informa��es do publisher
$id_operadora = $GLOBALS['_POST']['dd_operadora'];
$nome_merchant = $GLOBALS['_POST']['nome_merchant'];

//Per�odo considerado na remessa
$data_inic = formata_data(trim($GLOBALS['_POST']['tf_data_inicial']), 1);
$data_fim = formata_data(trim($GLOBALS['_POST']['tf_data_final']), 1);

//PEGA VALOR DE ACORDO COM VALOR GRAVADO NO CAMPO 'merchant_id_bexs' DA TABELA "OPERADORAS" QUE CORRESPONDE A MERCHANT ID (identificador dados do publisher) - numero identificador (CRIADO pelo BEXS)
$id_merchant_bexs =(trim($GLOBALS['_POST']['merchant_id_bexs']) == "" || is_null($GLOBALS['_POST']['merchant_id_bexs']) || trim($GLOBALS['_POST']['merchant_id_bexs']) == "0") ? NULL : $GLOBALS['_POST']['merchant_id_bexs'];

if(is_null($id_merchant_bexs)){
    $msg_erro_ids .= utf8_encode("<strong>ERRO 0001</strong>: Merchant ID n�o identificado!<br>Por favor, entre em contato com o BEXS para a cria��o de um MERCHANT ID para o publisher <u><i>".$nome_merchant. "</i></u>!");
}

//PEGA VALOR DE ACORDO COM VALOR GRAVADO NO CAMPO 'opr_facilitadora' DA TABELA "OPERADORAS" QUE CORRESPONDE A PERFIL OPERACIONAL (identificador dados banc�rios do publisher) - numero identificador (CRIADO pelo BEXS)
$perfil_operacional = (trim($GLOBALS['_POST']['facilitadora_perfil_op']) == "" || is_null($GLOBALS['_POST']['facilitadora_perfil_op']) || trim($GLOBALS['_POST']['facilitadora_perfil_op']) == "0") ? NULL : trim($GLOBALS['_POST']['facilitadora_perfil_op']);

if(is_null($perfil_operacional)){
    $msg_erro_ids .= utf8_encode("<strong>ERRO 0010</strong>: Perfil Operacional do Publisher n�o identificado!<br>Por favor, entre em contato com o BEXS para a cri��o de um perfil operacional para o publisher <u><i>".$nome_merchant. "</i></u>!");
}

if($msg_erro_ids == ""){
    //Valor total em reais recuperado no per�odo selecionado
    $valor_total_enviar = str_replace(',','.',str_replace('.', '', $GLOBALS['_POST']['grosswired']));

    //ARRAY PARA VERIFICAR SE J� EXISTE ARQUIVO CRIADO COM ESSAS INFORMA��ES
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
                $resultado .= utf8_encode("<strong>ERRO 100</strong>: Problema de campo obrigat�rio! Um e-mail com o detalhamento do problema est� sendo enviado para <i>financeiro@e-prepag.com.br</i>");
                $msg .= "Problema com os dados da remessa referente ao Publisher ". $nome_merchant." de Perfil Operacional ". $perfil_operacional .
                "<br>Per�odo considerado: <strong>". $data_inic. "</strong> at� <strong>". $data_fim."</strong><br><br>";

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
                   $resultado .= utf8_encode("<strong>[INFO]</strong>: As informa��es da remessa e o arquivo de opera��es referente a este PUBLISHER neste per�odo j� foram enviados com sucesso ao BEXS!<br><br>AGUARDE a resposta do processamento do arquivo de opera��es que ser� enviada no e-mail <i>financeiro@e-prepag.com.br</i>"); 
                    break;
                case "2":
                    $resultado .= utf8_encode("<strong>[INFO]</strong>: As informa��es da remessa e o arquivo de opera��es referentes a este PUBLISHER neste per�odo j� foram enviados e processados com SUCESSO pelo BEXS!<br><br><strong>REMESSA CONCLU�DA!</strong>"); 
                    break;
                case "3":
                    $resultado .=utf8_encode("<strong>ERRO 607</strong>: O processamento da remessa de ID <strong>".str_replace(".zip", "", $bexs->getnomeArquivoZip())."</strong> j� deveria ter sido realizado pelo BEXS.<br>Poss�vel problema com a resposta. Por favor, entre em contato com o BEXS informando o ID acima para maiores esclarecimentos.");
                    break;
            }
        }   

    } //end if(!$bexs->getStatus())

    //Enviar requisicao WS
    $need_req_Web_Service = $bexs->need_req_Web_Service();
    if($bexs->getStatus() && $need_req_Web_Service) {
        $resultado .= $bexs->req_Web_service();
    }

    //Enviar arquivo SFTP
    if($bexs->getStatus() && $bexs->need_envio_sFTP()) {
        $resultado .= $bexs->envio_sFTP($need_req_Web_Service);
    }

    echo $resultado;
    
} else{
    
    echo $msg_erro_ids;
}
?>