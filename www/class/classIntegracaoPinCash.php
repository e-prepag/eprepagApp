<?php

function retorna_id_pin_cash($pin)
{
    //instanciando a classe de cryptografia
    $chave256bits = new Chave();
    $aes = new AES($chave256bits->retornaChave());

    // Refatorado: O valor criptografado agora é um parâmetro
    $pin_criptografado = base64_encode($aes->encrypt($pin));
    $sql = "SELECT pin_codinterno FROM pins_store WHERE pin_codigo = $1 LIMIT 1";
    
    // Refatorado: Usando SQLexecuteQueryParams
    $rs_log = SQLexecuteQueryParams($sql, [$pin_criptografado]);

    if ($rs_log && pg_num_rows($rs_log) > 0) {
        $rs_log_row = pg_fetch_array($rs_log);
        return $rs_log_row['pin_codinterno'];
    } else {
        // informa zero (0) quando nao foi encontrado -- ATENCAUN
        return '0';
    }
}

function retorna_id_pin_GoCASH($pin)
{
    // Refatorado: O valor tratado (trim) agora é um parâmetro
    $pin_tratado = trim($pin);
    $sql = "SELECT pgc_id FROM pins_gocash WHERE pgc_pin_number = $1 LIMIT 1";
    
    // Refatorado: Usando SQLexecuteQueryParams
    $rs_log = SQLexecuteQueryParams($sql, [$pin_tratado]);

    if ($rs_log && pg_num_rows($rs_log) > 0) {
        $rs_log_row = pg_fetch_array($rs_log);
        return $rs_log_row['pgc_id'];
    } else {
        // informa zero (0) quando nao foi encontrado -- ATENCAUN
        return '0';
    }
}

function retorna_status_cash($pin)
{
    //instanciando a classe de cryptografia
    $chave256bits = new Chave();
    $aes = new AES($chave256bits->retornaChave());

    // Refatorado: O valor criptografado agora é um parâmetro
    $pin_criptografado = base64_encode($aes->encrypt($pin));
    $sql = "SELECT pin_status FROM pins_store WHERE pin_codigo = $1 LIMIT 1";

    // Refatorado: Usando SQLexecuteQueryParams
    $rs_log = SQLexecuteQueryParams($sql, [$pin_criptografado]);

    if ($rs_log && pg_num_rows($rs_log) > 0) {
        $rs_log_row = pg_fetch_array($rs_log);
        return $rs_log_row['pin_status'];
    } else {
        // informa zero (0) quando nao foi encontrado -- ATENCAUN
        return '0';
    }
}

function retorna_pin_valor_cash($pin)
{
    //instanciando a classe de cryptografia
    $chave256bits = new Chave();
    $aes = new AES($chave256bits->retornaChave());

    // Refatorado: O valor criptografado agora é um parâmetro
    $pin_criptografado = base64_encode($aes->encrypt($pin));
    $sql = "SELECT pin_valor FROM pins_store WHERE pin_codigo = $1 LIMIT 1";
    
    // Refatorado: Usando SQLexecuteQueryParams
    $rs_log = SQLexecuteQueryParams($sql, [$pin_criptografado]);

    if ($rs_log && pg_num_rows($rs_log) > 0) {
        $rs_log_row = pg_fetch_array($rs_log);
        if ($rs_log_row['pin_valor'] != '') {
            return $rs_log_row['pin_valor'];
        } else {
            return '0'; // Mantendo lógica original
        }
    } else {
        // informa zero (0) quando nao foi encontrado -- ATENCAUN
        return '0';
    }
}

function log_pin_cash($codretepp, $pin, $id, $parametros, $valor, $gocash)
{
    // Refatorado: Chamando funções sem addslashes
    if (empty($gocash)) {
        $aux_id_pin = retorna_id_pin_cash($pin);
    } else {
        $aux_id_pin = retorna_id_pin_GoCASH($pin);
    }
    
    // Refatorado: Coletando todos os valores para parametrizar
    $ip_acesso = retorna_ip_acesso();
    $status_cash = retorna_status_cash($pin); // Lógica original chamava esta função

    // Refatorado: SQL com placeholders ($1, $2, ...)
    $sql = "INSERT INTO pins_integracao_cash_historico 
            VALUES (NOW(), $1, $2, $3, $4, $5, $6, $7, $8)";

    // Refatorado: Array de parâmetros na ordem correta
    $params = [
        $ip_acesso,
        $aux_id_pin,
        $id,
        $codretepp,
        $status_cash,
        $parametros,
        $valor,
        $gocash
    ];

    // Refatorado: Usando SQLexecuteQueryParams
    $rs_log = SQLexecuteQueryParams($sql, $params);
    
    if (!$rs_log) {
        echo "<font color='#FF0000'><b>Erro na gera&ccedil;&atilde;o de LOG.</b></font><br>";
    }
}

function verifica_valor_pin_cash($cod_pin, $valor)
{
    global $PINS_STORE_STATUS_VALUES, $PINS_STORE_MSG_LOG_STATUS;
    
    //instanciando a classe de cryptografia
    $chave256bits = new Chave();
    $aes = new AES($chave256bits->retornaChave());

    // Refatorado: Valores agora são parâmetros
    $pin_criptografado = base64_encode($aes->encrypt($cod_pin));
    $status_ativo = intval($PINS_STORE_STATUS_VALUES['A']);

    $sql = "SELECT pin_valor FROM pins_store WHERE pin_codigo = $1 AND pin_status = $2 LIMIT 1";
    
    // Refatorado: Usando SQLexecuteQueryParams
    $rs_oper = SQLexecuteQueryParams($sql, [$pin_criptografado, $status_ativo]);
    
    sleep(1); // Mantendo lógica original

    if (!$rs_oper || pg_num_rows($rs_oper) == 0) {
        return false;
    } else {
        $rs_oper_row = pg_fetch_array($rs_oper);
        // Lógica simplificada
        return $rs_oper_row['pin_valor'] == $valor;
    }
}

function VetorIntegrator()
{
    $sql_opr = "SELECT opr_nome, opr_codigo FROM operadoras WHERE opr_product_type != 0 ORDER BY opr_nome";
    
    // Refatorado: Usando SQLexecuteQueryParams com array vazio, pois não há parâmetros
    $rs_oper = SQLexecuteQueryParams($sql_opr, []);
    
    $operacao_array = []; // Inicializa o array para evitar warnings
    if ($rs_oper) {
        while ($rs_oper_row = pg_fetch_array($rs_oper)) {
            $operacao_array[$rs_oper_row['opr_codigo']] = $rs_oper_row['opr_nome'];
        }
    }
    return $operacao_array;
}

?>