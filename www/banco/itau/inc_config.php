<?php
require_once $raiz_do_projeto.'includes/inc_register_globals.php';
require_once $raiz_do_projeto.'banco/itau/Itaucripto.php';

//  Inicializa valores
//  EPP Pagamentos
//	$codEmp = "J0082213050001350000011193"; //Coloque aqui seu código de empresa
//	$chave = "05418EPREPAG2627"; // Coloque aqui sua chave de criptografia em maiúscula
    
//EPP Administradora
$codEmp = "J0190372760001720000023415";  //Coloque aqui seu c digo de empresa 
$chave = "7PR7P2G2DMBR2S1L"; // Coloque aqui sua chave de criptografia em mai scula
$formato_itau = "1";		//	Coloque aqui o tipo de retorno desejado: 0 para HTML ou 1 para XML

if(!function_exists("formata_string_itau")){
    function formata_string_itau($s, $c) {
            $stmp = "";

            for($i=0;$i<strlen($s);$i++) { 
                    $stmp .= substr($s,$i,1);
                    if(($i % $c)==($c-1)) {
                            $stmp .= "<br>".PHP_EOL;
                    }
            }
            return $stmp;
    }
}

// ddmmaaaa
function getDataItau() {
        return date("dmY");
}

//-----------------------------------------------------------------------------------------------------------------------------------------------------
function getSondaItau_InShopline($dados) {

        $curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL,"https://shopline.itau.com.br/shopline/Consulta.aspx");

        // http://www.weberdev.com/get_example-4136.html
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($curl_handle, CURLOPT_HEADER, 1); 
        curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"); // "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)");	//

        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $dados);

        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,5);	// valor normal = 10s, mas quando o shopline.itau.com.br sai do ar => 5s
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,1);

        $buffer = curl_exec($curl_handle);

        curl_close($curl_handle);

        return $buffer;
}

// No Itaú o ID (8 lugares - 99999999) é único por 60 dias (+10 de margem de segurança), nós fazemos único em cada ano, <10^8-1
function get_newTransacaoID_Itau(){
        $maxID = 100000000-1;
        $nmax = 100;
        $n = 1;
        $s_ids = "";
        $time_start_stats = getmicrotime();
        $itau_id_rand = mt_rand(1, $maxID);
        $s_ids .= $itau_id_rand.", ";
        while(existeIdItau($itau_id_rand)){
                $itau_id_rand = mt_rand(1, $maxID);
                $s_ids .= $itau_id_rand.", ";
                $n++;
                if($n>=2*$nmax) {
                        $itau_id_rand = null;
                        break;
                }
        }
        $msg = (($n==1)?"Just one shot!!! ":"ntentativas: $n ")." ($s_ids)";
        gravaLog_obterIdItauValido($msg);
        if($n>1) {
                $msg = "\tElapsed time ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."s";
                gravaLog_obterIdItauValido($msg);
        }
        if($n>=$nmax) {
                $msg = "\t\t!!!\tDemorou muito para encontrar um id_venda ($n>=$nmax).";
                gravaLog_obterIdItauValido($msg);
        }
        // se for maior do que 10^8-1 => pode resetar, já devem ter passado mais de 60 dias desde o último reset
        // (ver documentação do Itaú Shopline para o formato do "Pedido")
        if($itau_id_rand>1E8-1) {
                $itau_id_rand = 1;
        }
        return $itau_id_rand;
} //end function get_newTransacaoID_Itau()
//-----------------------------------------------------------------------------------------------------------------------------------------------------

function existeIdItau($itau_id_rand){

            $ret = true;
            //SQL
            $sql = "select count(*) as qtde from tb_pag_compras ";
            $sql .= " where iforma='A' ";
            $sql .= " and id_transacao_itau = " . SQLaddFields($itau_id_rand, "");
            $rs = SQLexecuteQuery($sql);
            if($rs && pg_num_rows($rs) > 0){
                    $rs_row = pg_fetch_array($rs);
                    if($rs_row['qtde'] == 0) $ret = false;
            }			
            return $ret;   	
} //end function existeIdItau($itau_id_rand)

function gravaLog_obterIdItauValido($mensagem){

    //Arquivo
    $file = $GLOBALS['raiz_do_projeto']."log/log_obterIdItauValido.txt";

    //Mensagem
    $mensagem = date('Y-m-d H:i:s') . " " . $mensagem . PHP_EOL;

    //Grava mensagem no arquivo
    if ($handle = fopen($file, 'a+')) {
            fwrite($handle, $mensagem);
            fclose($handle);
    } 

} //end function gravaLog_obterIdItauValido($mensagem)

/*
As combinações de consulta possíveis são:
TIPPAG SITPAG
- 00 para pagamento ainda não escolhido
    ONLINE E REAL TIME
    - 01 para situação de pagamento não finalizada (tente novamente)
    - 02 para erro no processamento da consulta (tente novamente)
    - 03 para pagamento não localizado (consulta fora de prazo ou pedido não registrado no banco)

- 01 para Pagamento à Vista (TEF e CDC)
    ONLINE E REAL TIME
    - 00 para pagamento efetuado
    - 01 para situação de pagamento não finalizada (tente novamente)
    - 02 para erro no processamento da consulta (tente novamente)
    - 03 para pagamento não localizado (consulta fora de prazo ou pedido não registrado no banco)

- 02 para Boleto Bancário
    ONLINE E REAL TIME
    - 01 para situação de pagamento não finalizada (tente novamente)
    - 02 para erro no processamento da consulta (tente novamente)
    - 03 para pagamento não localizado (consulta fora de prazo ou pedido não registrado no banco)
    - 04 para Boleto emitido com sucesso
    A PARTIR DE UM DIA ÚTIL DA EMISSÃO DO BOLETO
    - 00 para pagamento efetuado
    - 05 para pagamento efetuado, aguardando compensação
    - 06 para pagamento não compensado

- 03 para Cartão Itaucard
    ONLINE E REAL TIME
    - 00 para pagamento efetuado
    - 01 para situação de pagamento não finalizada (tente novamente)
    - 02 para erro no processamento da consulta (tente novamente)
    - 03 para pagamento não localizado (consulta fora de prazo ou pedido não registrado no banco)

De acordo com a tabela acima, as opções de pagamento 01 e 03 poderão ser confirmadas em tempo real, mas a opção

*/

$a_itau_erros = array(
         '00' => array('descr' => 'pagamento ainda não escolhido', 'sitpag' => array(
            '01' => 'situação de pagamento não finalizada (tente novamente)', 
            '02' => 'erro no processamento da consulta (tente novamente)', 
            '03' => 'pagamento não localizado (consulta fora de prazo ou pedido não registrado no banco)' 
            )), 

        '01' => array('descr' => 'Pagamento à Vista (TEF e CDC)', 'sitpag' => array(
            '00' => 'pagamento efetuado', 
            '01' => 'situação de pagamento não finalizada (tente novamente)', 
            '02' => 'erro no processamento da consulta (tente novamente)', 
            '03' => 'pagamento não localizado (consulta fora de prazo ou pedido não registrado no banco)' 
            )), 

        '02' => array('descr' => 'Boleto Bancário', 'sitpag' => array(
            '01' => 'situação de pagamento não finalizada (tente novamente)', 
            '02' => 'erro no processamento da consulta (tente novamente)', 
            '03' => 'pagamento não localizado (consulta fora de prazo ou pedido não registrado no banco)', 
            '04' => 'Boleto emitido com sucesso', 
            '00' => 'pagamento efetuado', 
            '05' => 'pagamento efetuado, aguardando compensação', 
            '06' => 'pagamento não compensado' 
            )), 
        '03' => array('descr' => 'Cartão Itaucard', 'sitpag' => array(
            '00' => 'pagamento efetuado', 
            '01' => 'situação de pagamento não finalizada (tente novamente)', 
            '02' => 'erro no processamento da consulta (tente novamente)', 
            '03' => 'pagamento não localizado (consulta fora de prazo ou pedido não registrado no banco)' 
            ))
);

function getItauSondaErro($tipPag, $sitPag) {
    global $a_itau_erros;

    if(!array_key_exists("".$tipPag."", $a_itau_erros)) {
        return "Tipo Pagamento '$tipPag' desconhecido.";
    }
    if(!array_key_exists("".$sitPag."", $a_itau_erros["".$tipPag.""]['sitpag'])) {
        return "Situação Pagamento '$sitPag' desconhecida.";
    }

    return $a_itau_erros["".$tipPag.""]['sitpag']["".$sitPag.""];
}

function getSondaItau($pedido, &$a_retorno, &$sitPag, &$dtPag) {
    global $codEmp, $formato_itau, $chave;
    $cripto = new Itaucripto();
    $dados_aux = $cripto->geraConsulta($codEmp, $pedido, $formato_itau, $chave);
    $dados = "DC=".$dados_aux;
    $dados_sonda = getSondaItau_InShopline($dados);
    $aretorno = explode(PHP_EOL, $dados_sonda);
    $xml_file = "";
    $verificadorXML = false;
    for($i=0;$i<count($aretorno);$i++) {
        if(strpos(strtoupper($aretorno[$i]), strtoupper('path=/'))) {
            $verificadorXML = true;
        }//end if(strpos($aretorno[$i], 'path=/'))
        elseif($verificadorXML) {
            $xml_file .= $aretorno[$i].PHP_EOL; 
        }//end elseif($verificadorXML)
    }//end for
    //var_dump($xml_file);

    $a_xml = simplexml_load_string(trim($xml_file));

    // ======================================================================================
    // Expand directly
    // Está usando esta forma de obter o retorno
    // retorna 
    //	key2: PARAM => [1] CodEmp = J0082213050001350000011193
    //	key2: PARAM => [2] Pedido = 00000020
    //	key2: PARAM => [3] Valor = 10,00
    //	key2: PARAM => [4] tipPag = 01
    //	key2: PARAM => [5] sitPag = 00
    //	key2: PARAM => [6] dtPag = 16102009
    //	key2: PARAM => [7] codAut = 
    //	key2: PARAM => [8] numId = 
    //	key2: PARAM => [9] compVend = 
    //	key2: PARAM => [10] tipCart = 

    $a_retorno = array();
    $sitPag = "99";	
    if($a_xml) {
        foreach($a_xml->PARAMETER->PARAM as $key2 => $val2) {
            //Coloca os valores retornados em 'VALUE' em variáveis com o nome em 'ID'
            $$val2['ID'] = $val2['VALUE'];
            $a_retorno["".$val2['ID'].""] = $val2['VALUE'];
        }

        $sitPag = $a_retorno['sitPag'];
        $dtPag = $a_retorno['dtPag'];
    }
    return $sitPag;
}
?>