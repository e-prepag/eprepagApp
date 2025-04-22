<?php
//Status dos PINs E-PREPAG
$PINS_PUBLISHERS_STATUS_VALUES = array(
										'D'	=> '1',
										'V'	=> '3',
										'L'	=> '6',
										'P'	=> '7',
										'U'	=> '8',
										);

function retorna_id_pin($pin,$id) {
	$sql = "select pin_codinterno from pins where pin_codigo = '".addslashes($pin)."' and opr_codigo =".addslashes($id);
	//echo $sql;
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pin_codinterno'] != '')
			return $rs_log_row['pin_codinterno'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function retorna_status($pin,$id) {
	$sql = "select pin_status from pins where pin_codigo = '".addslashes($pin)."' and opr_codigo =".addslashes($id);
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pin_status'] != '')
			return $rs_log_row['pin_status'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function log_pin($codretepp,$pin,$id) {
	if (retorna_id_pin(addslashes($pin),addslashes($id))=='')
		$aux_id_pin = '0';
	else $aux_id_pin = retorna_id_pin(addslashes($pin),addslashes($id));
	$sql = "INSERT INTO pins_integracao_historico VALUES (NOW(),'".retorna_ip_acesso()."',".$aux_id_pin.",'".addslashes($id)."','".addslashes($codretepp)."',".retorna_status(addslashes($pin),addslashes($id)).")";
	//echo $sql."<br>";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
		 echo "<font color='#FF0000'><b>Erro na gera&ccedil;&atilde;o de LOG.\n</b></font><br>";
	}
}

function verifica_valor_pin($cod_pin,$valor,$id) {
	global $PINS_PUBLISHERS_STATUS_VALUES;
	$sql = "select pin_codigo,pin_valor from pins where pin_codigo='".addslashes($cod_pin)."' and (pin_status='".intval($PINS_PUBLISHERS_STATUS_VALUES['V'])."' OR pin_status='".intval($PINS_PUBLISHERS_STATUS_VALUES['L'])."' OR pin_status='".intval($PINS_PUBLISHERS_STATUS_VALUES['P'])."') and opr_codigo =".addslashes($id);
	$rs_oper = SQLexecuteQuery($sql);
	sleep(1);
	if(!$rs_oper || pg_num_rows($rs_oper) == 0) {
		return false;
	} else {
		$rs_oper_row = pg_fetch_array($rs_oper);
		if ($rs_oper_row['pin_valor']==$valor) {
			return true;
		}
		else {
			return false;
		}
	}
}

function retorna_pin_valor($pin,$id) {
	$sql = "select pin_valor from pins where pin_codigo = '".addslashes($pin)."' and opr_codigo =".addslashes($id);
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pin_valor'] != '')
			return $rs_log_row['pin_valor'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function verifica_validade($pin, $id) {
    // Consulta para buscar os dados da venda
    $sql = "SELECT pin_datavenda, pin_horavenda, pin_validade 
            FROM pins 
            WHERE pin_codigo = '" . addslashes($pin) . "' 
              AND opr_codigo = " . addslashes($id);

    $rs_log = SQLexecuteQuery($sql);

    if ($rs_log) {
        $rs_log_row = pg_fetch_array($rs_log);

        if ($rs_log_row['pin_datavenda'] != '' && $rs_log_row['pin_horavenda'] != '') {
            // Combinar a data e a hora da venda em um único objeto DateTime
            $data_venda = DateTime::createFromFormat(
                'Y-m-d H:i:s', 
                $rs_log_row['pin_datavenda'] . ' ' . $rs_log_row['pin_horavenda']
            );

            if ($data_venda) {
                // Define o limite de dias baseado no ID
                $dias_limite = ($id == 166) ? 60 : 180;

                // Adicionar os dias limite à data da venda
                $data_limite = clone $data_venda;
                $data_limite->add(new DateInterval("P" . $dias_limite . "D"));

                // Verificar se a data atual é menor ou igual ao limite
                return new DateTime() <= $data_limite;
            }
        } else if ($rs_log_row['pin_validade'] != '') {
            // Transformar pin_validade em um objeto DateTime
            $data_validade = DateTime::createFromFormat('Y-m-d', $rs_log_row['pin_validade']);

            if ($data_validade) {
                // Verificar se a validade ainda não expirou
                return $data_validade >= new DateTime();
            }
        }
    }

    // Retorna false se nenhuma condição for atendida
    return $sql;
}



?>