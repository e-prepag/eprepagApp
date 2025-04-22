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

?>