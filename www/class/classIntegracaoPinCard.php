<?php
//Include comumm de integração de PINs
require_once $raiz_do_projeto . "includes/inc_functions_card.php";
require_once $raiz_do_projeto . "consulta_cpf/config.inc.cpf.php";


function retorna_id_pin_card($pin,$id) {
    //instanciando a classe de cryptografia
    $chave256bits = new Chave();
    $aes = new AES($chave256bits->retornaChave());
    
    // Refatorado: O valor criptografado agora é um parâmetro
    $pin_criptografado = base64_encode($aes->encrypt($pin));
    $sql = "SELECT pin_codinterno FROM pins_card WHERE pin_codigo = $1 AND opr_codigo = $2 LIMIT 1";
    
    $rs_log = SQLexecuteQueryParams($sql, [$pin_criptografado, $id]);
    
    if ($rs_log && pg_num_rows($rs_log) > 0) {
        $rs_log_row = pg_fetch_array($rs_log);
        return $rs_log_row['pin_codinterno'];
    } else { 
        // informa zero (0) quando nao foi encontrado -- ATENCAUN
        return '0';
    }
}

function retorna_id_pin_card_para_adm_bo($pin) {
    //instanciando a classe de cryptografia
    $chave256bits = new Chave();
    $aes = new AES($chave256bits->retornaChave());
    
    // Refatorado: O valor criptografado agora é um parâmetro
    $pin_criptografado = base64_encode($aes->encrypt($pin));
    $sql = "SELECT pin_codinterno FROM pins_card WHERE pin_codigo = $1 LIMIT 1";
    
    $rs_log = SQLexecuteQueryParams($sql, [$pin_criptografado]);
    
    if ($rs_log && pg_num_rows($rs_log) > 0) {
        $rs_log_row = pg_fetch_array($rs_log);
        return $rs_log_row['pin_codinterno'];
    } else { 
        // informa zero (0) quando nao foi encontrado -- ATENCAUN
        return '0';
    }
}

function retorna_status_card($pin,$id) {
    //instanciando a classe de cryptografia
    $chave256bits = new Chave();
    $aes = new AES($chave256bits->retornaChave());
    
    // Refatorado: Valores agora são parâmetros
    $pin_criptografado = base64_encode($aes->encrypt($pin));
    $sql = "SELECT pin_status FROM pins_card WHERE pin_codigo = $1 AND opr_codigo = $2 LIMIT 1";
    
    $rs_log = SQLexecuteQueryParams($sql, [$pin_criptografado, $id]);
    
    if ($rs_log && pg_num_rows($rs_log) > 0) {
        $rs_log_row = pg_fetch_array($rs_log);
        return $rs_log_row['pin_status'];
    } else { 
        // informa zero (0) quando nao foi encontrado -- ATENCAUN
        return '0';
    }
}

function retorna_pin_valor_card($pin,$id) {
    //instanciando a classe de cryptografia
    $chave256bits = new Chave();
    $aes = new AES($chave256bits->retornaChave());
    
    // Refatorado: Valores agora são parâmetros
    $pin_criptografado = base64_encode($aes->encrypt($pin));
    $sql = "SELECT pin_valor FROM pins_card WHERE pin_codigo = $1 AND opr_codigo = $2 LIMIT 1";
    
    $rs_log = SQLexecuteQueryParams($sql, [$pin_criptografado, $id]);
    
    if ($rs_log && pg_num_rows($rs_log) > 0) {
        $rs_log_row = pg_fetch_array($rs_log);
        if ($rs_log_row['pin_valor'] != '')
            return $rs_log_row['pin_valor'];
        // informa zero (0) quando nao foi encontrado -- ATENCAUN
        else return '0';
    } else {
        return '0';
    }
}

function log_pin_card($codretepp,$pin,$id,$parametros,$valor) {
    // Refatorado: Chamando funções sem addslashes
    $tmpTeste = retorna_id_pin_card($pin, $id);
    if ($tmpTeste == '') 
        $aux_id_pin = '0';
    else $aux_id_pin = $tmpTeste;
    
    // Refatorado: Coletando valores para parametrizar
    $ip_acesso = retorna_ip_acesso();
    $status_card = retorna_status_card($pin, $id);
    
    // Refatorado: SQL com placeholders ($1, $2, ...)
    $sql = "INSERT INTO pins_integracao_card_historico 
            VALUES (NOW(), $1, $2, $3, $4, $5, $6, $7)";
    
    // Refatorado: Array de parâmetros na ordem correta
    $params = [
        $ip_acesso,
        $aux_id_pin,
        $id,
        $codretepp,
        $status_card,
        $parametros,
        $valor
    ];

    $rs_log = SQLexecuteQueryParams($sql, $params);
    
    if(!$rs_log) {
         echo "<font color='#FF0000'><b>Erro na gera&ccedil;&atilde;o de LOG.</b></font><br>";
    }
}

function verifica_valor_pin_card($cod_pin,$valor,$id) {
    global $PINS_STORE_STATUS_VALUES,$PINS_STORE_MSG_LOG_STATUS;
    //instanciando a classe de cryptografia
    $chave256bits = new Chave();
    $aes = new AES($chave256bits->retornaChave());
    
    // Refatorado: Valores agora são parâmetros
    $pin_criptografado = base64_encode($aes->encrypt($cod_pin));
    $status_ativo = intval($PINS_STORE_STATUS_VALUES['A']);
    
    $sql = "SELECT pin_valor FROM pins_card WHERE pin_codigo = $1 AND pin_status = $2 AND opr_codigo = $3 LIMIT 1";
    
    $params = [$pin_criptografado, $status_ativo, $id];

    $rs_oper = SQLexecuteQueryParams($sql, $params);
    //sleep(1); // Mantenho o sleep se ele for intencional, mas ele não estava na função original
    
    if(!$rs_oper || pg_num_rows($rs_oper) == 0) {
        return false;
    } else {
        $rs_oper_row = pg_fetch_array($rs_oper);
        return $rs_oper_row['pin_valor'] == $valor;
    }
}

// --- Funções Auxiliares (Não alteradas) ---

function maskCard($val, $mask)
{
    $maskared = '';
    $k = 0;
    for($i = 0; $i<=strlen($mask)-1; $i++)
    {
        if($mask[$i] == '#')
        {
            if(isset($val[$k]))
                $maskared .= $val[$k++];
        }
        else
        {
            if(isset($mask[$i]))
                $maskared .= $mask[$i];
        }
    }
    return $maskared;
}

function verificaCPF_Card($cpf)
{
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    $RecebeCPF = $cpf;

    if (strlen($RecebeCPF) != 11) {
        return 0;
    } else if ($RecebeCPF == "00000000000" || $RecebeCPF == "11111111111") {
        return 0;
    } else {
        $Numero[1] = intval(substr($RecebeCPF, 1 - 1, 1));
        $Numero[2] = intval(substr($RecebeCPF, 2 - 1, 1));
        $Numero[3] = intval(substr($RecebeCPF, 3 - 1, 1));
        $Numero[4] = intval(substr($RecebeCPF, 4 - 1, 1));
        $Numero[5] = intval(substr($RecebeCPF, 5 - 1, 1));
        $Numero[6] = intval(substr($RecebeCPF, 6 - 1, 1));
        $Numero[7] = intval(substr($RecebeCPF, 7 - 1, 1));
        $Numero[8] = intval(substr($RecebeCPF, 8 - 1, 1));
        $Numero[9] = intval(substr($RecebeCPF, 9 - 1, 1));
        $Numero[10] = intval(substr($RecebeCPF, 10 - 1, 1));
        $Numero[11] = intval(substr($RecebeCPF, 11 - 1, 1));

        $soma = 10 * $Numero[1] + 9 * $Numero[2] + 8 * $Numero[3] + 7 * $Numero[4] + 6 * $Numero[5] + 5 *
            $Numero[6] + 4 * $Numero[7] + 3 * $Numero[8] + 2 * $Numero[9];
        $soma = $soma - (11 * (intval($soma / 11)));

        if ($soma == 0 || $soma == 1) {
            $resultado1 = 0;
        } else {
            $resultado1 = 11 - $soma;
        }

        if ($resultado1 == $Numero[10]) {
            $soma = $Numero[1] * 11 + $Numero[2] * 10 + $Numero[3] * 9 + $Numero[4] * 8 + $Numero[5] * 7 + $Numero[6] * 6 + $Numero[7] * 5 +
                $Numero[8] * 4 + $Numero[9] * 3 + $Numero[10] * 2;
            $soma = $soma - (11 * (intval($soma / 11)));

            if ($soma == 0 || $soma == 1) {
                $resultado2 = 0;
            } else {
                $resultado2 = 11 - $soma;
            }
            if ($resultado2 == $Numero[11]) {
                return TRUE;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
}

function fix_name_Card($str){
    $name = explode(' ', strtolower($str));
    foreach( $name as $k=>$n ){
        if(strlen($n)<=2)
            continue;
        
       $name[$k] = ucfirst($n);
    }
    return implode(' ', $name);
}

function verificaNomeCard($nome) {

    $reg = '/^\\s*[a-zA-ZÀ-ú\']{1,}(\\s+[a-zA-ZÀ-ú\']{1,}\\s*)+$/';

    if (preg_match($reg, $nome) && strpos($nome, "  ") === false) {
        return TRUE;
    }
    return FALSE;

}


function verificaCPFnaReceitaFederal($cpf, $pin, $id, $data_nascimento = null) {

    /*
     * A T E N Ç Ã O:  Foi implementado RETURN 2 para verificação se o serviço está disponível
    */
    
    $name = null;
    
    if( !verificaCPF_Card($cpf) ) {
        return false;
    }

    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    //Novo modelo de Consulta
    $rs_api = new classCPF();

    //Testando se o serviço está disponível
    if(!$rs_api->get_service_status()) {
        return 2;
    } else {
        
        $resposta = null;
        $parametros = array(
                            'cpfcnpj' => $cpf,
                            'data_nascimento' => $data_nascimento,
                            );
        $testeCPF = $rs_api->Req_EfetuaConsulta($parametros,$resposta);

        //Testando menor de idade minima
        if($testeCPF == 112) {
            
            return $testeCPF;
            
        }
        //Testando se o CPF consta na BlackList
        elseif($testeCPF == 299) {
            
            return 171;
            
        }

        //Testando se ultrapassou o limite de utilização do mesmo CPF
        elseif ($testeCPF != 171) {
        
                if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY) { 

                        if($testeCPF == 0 && $resposta['resposta']['cpf']['situacao'] == CPF_SITUCAO_REGULAR){
                            $name = $resposta['resposta']['cpf']['nome'];
                        }

                        elseif($testeCPF == 1){
                            return 2;
                        }

                        elseif(is_null($testeCPF)){
                            return 2;
                        }

                        else {
                            return false;
                        }

                }
                elseif (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) {

                        if($testeCPF == 2){
                            return 2;
                        }

                        elseif($testeCPF == 1){
                            $name = $resposta['pesquisas']['camposResposta']['nome'];
                            $data_nascimento = $resposta['pesquisas']['camposResposta']['data_nascimento'];
                        }

                        else {
                            return 2;
                        }

                } else {

                    if($testeCPF == 3 && $resposta['pesquisas']['camposResposta']['situacao'] == CPF_SITUCAO_REGULAR){
                        $name = $resposta['pesquisas']['camposResposta']['nome'];
                        $data_nascimento = $resposta['pesquisas']['camposResposta']['data_nascimento'];
                    }

                    elseif($testeCPF == 1){
                        return 2;
                    }

                    elseif($testeCPF == 9){
                        return 2;
                    }

                    elseif($testeCPF == 12){
                        return $testeCPF;
                    }

                    elseif(is_null($testeCPF)){
                        return 2;
                    }

                    else {
                        return false;
                    }


            }

        }

        // Atingiu o limite máximo de utilização do mesmo CPF
        else {

            return 171;

        }

        if(!is_null($name)){

            // Vamos certificar que extraimos apenas os numeros do CPF, para depois aplicarmos a mascara
            $matches = array();
            preg_match_all('!\d+!', $cpf, $matches);

            $cpf = implode('', $matches[0]);

            // REFATORADO: SQL com placeholders
            $sql = "INSERT INTO pins_integracao_card_cpf 
                    VALUES ($1, $2, $3, NOW(), to_date($4, 'DD/MM/YYYY'))";
            
            $pin_codinterno = retorna_id_pin_card($pin, $id);
            $cpf_mascarado = maskCard($cpf, '###.###.###-##');
            $name_fixo = fix_name_Card($name);

            $params = [
                $pin_codinterno,
                $cpf_mascarado,
                $name_fixo,
                $data_nascimento // Espera-se que seja 'DD/MM/YYYY'
            ];

            $res = SQLexecuteQueryParams($sql, $params);

            $cmdtuples = pg_affected_rows($res);

            if($cmdtuples===1) {
                    return true;
            } else {
                    return false;
            }

        }
        else {
            //retorna false se a estrutura do nome não está OK.
            return false;
        }
        
    }

}


function RetonaTamanhoPINEPPCARD($pin) {
    $tamanho = strlen(trim($pin));
    if($tamanho == $GLOBALS['PIN_CARD_TAMANHO']) {
        return true;
    }
    else {
        return false;
    }
}


function flag_pin_card_test($pin,$id) {

    //variavel auxiliar para o retorno da função
    $aux_retorno = 1;

        if(RetonaTamanhoPINEPPCARD($pin)) {
                // Refatorado: Chamando função sem addslashes
                $aux_pin_codinterno = retorna_id_pin_card($pin, $id);
                
                if($aux_pin_codinterno != '0') {

                        //Inicia transacao
                        $sql_begin = "BEGIN TRANSACTION";
                        $ret = SQLexecuteQueryParams($sql_begin, []); // Refatorado
                        
                        if($ret) {
                                // Refatorado: UPDATE com placeholders
                                $sql_update = "UPDATE pins_card SET pin_bloqueio = 1 WHERE pin_codinterno = $1 AND pin_bloqueio = 0 AND opr_codigo = $2";
                                $ret2 = SQLexecuteQueryParams($sql_update, [$aux_pin_codinterno, $id]); // Refatorado
                                
                                $cmdtuples = pg_affected_rows($ret2);
                                gravaLog_EPPCARD("Em flag_pin_test: $cmdtuples registros afetados.$sql_update");

                                if($cmdtuples===1) {
                                        $sql_commit = "COMMIT TRANSACTION";
                                        $ret3 = SQLexecuteQueryParams($sql_commit, []); // Refatorado
                                        if($ret3) $aux_retorno = 0;
                                } else {
                                        $sql_rollback = "ROLLBACK TRANSACTION";
                                        $ret3 = SQLexecuteQueryParams($sql_rollback, []); // Refatorado
                                }
                        }

                }

        } 

    return $aux_retorno;

}


function flag_pin_card_unblock($pin,$id) {
    
        if(RetonaTamanhoPINEPPCARD($pin)) {
                // Refatorado: Chamando função sem addslashes
                $aux_pin_codinterno = retorna_id_pin_card($pin, $id);
                
                if($aux_pin_codinterno != '0') {
                        // Refatorado: UPDATE com placeholders
                        $sql = "UPDATE pins_card SET pin_bloqueio = 0 WHERE pin_codinterno = $1 AND opr_codigo = $2";
                        $ret2 = SQLexecuteQueryParams($sql, [$aux_pin_codinterno, $id]); // Refatorado

                        gravaLog_EPPCARD("DESBLOQUEIO EPP CASH: $sql");
                        
                }

        } 

}


function gravaLog_EPPCARD($mensagem){
    
        //Arquivo
        $file = $GLOBALS['raiz_do_projeto'] . "arquivos_gerados/logs/log_integracao_PIN_Cartao.txt";
    
        //Mensagem
        $mensagem =  str_repeat("-", 80).PHP_EOL.date('Y-m-d H:i:s'). " " .$GLOBALS['_SERVER']['SCRIPT_FILENAME'] . PHP_EOL . $mensagem . PHP_EOL;
        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
            fwrite($handle, $mensagem);
            fclose($handle);
        } 
    
}


function retornaID_Distibuidora($pin){
    
        return substr($pin,0,2);
    
}


function getmicrotimePINCard()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

?>