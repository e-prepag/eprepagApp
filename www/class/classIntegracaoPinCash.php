<?php
function retorna_id_pin_cash($pin) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_codinterno from pins_store where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."'";
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

function retorna_id_pin_GoCASH($pin) {
	$sql = "select pgc_id from pins_gocash where pgc_pin_number = '".trim(addslashes($pin))."'";
	$rs_log = SQLexecuteQuery($sql);
	if($rs_log) {
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pgc_id'] != '')
			return $rs_log_row['pgc_id'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function retorna_status_cash($pin) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_status from pins_store where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."'";
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

function retorna_pin_valor_cash($pin) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_valor from pins_store where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."'";
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

function log_pin_cash($codretepp,$pin,$id,$parametros,$valor,$gocash) {
	if (empty($gocash)) {
		if (retorna_id_pin_cash(addslashes($pin))=='')
			$aux_id_pin = '0';
		else $aux_id_pin = retorna_id_pin_cash(addslashes($pin));
	}
	else {
		if (retorna_id_pin_GoCASH(addslashes($pin))=='')
			$aux_id_pin = '0';
		else $aux_id_pin = retorna_id_pin_GoCASH(addslashes($pin));
	}
	$sql = "INSERT INTO pins_integracao_cash_historico VALUES (NOW(),'".retorna_ip_acesso()."',".$aux_id_pin.",".addslashes($id).",'".addslashes($codretepp)."',".retorna_status_cash(addslashes($pin)).",'".$parametros."',$valor,$gocash)";
	//die ($sql);
	$rs_log = SQLexecuteQuery($sql);
	//var_dump($rs_log);
	if(!$rs_log) {
		 echo "<font color='#FF0000'><b>Erro na gera&ccedil;&atilde;o de LOG.</b></font><br>";
	}
}

function verifica_valor_pin_cash($cod_pin,$valor) {
	global $PINS_STORE_STATUS_VALUES,$PINS_STORE_MSG_LOG_STATUS;
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_codigo,pin_valor from pins_store where pin_codigo='".base64_encode($aes->encrypt(addslashes($cod_pin)))."' and pin_status='".intval($PINS_STORE_STATUS_VALUES['A'])."'";
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

function VetorIntegrator() {
	$sql_opr = "select opr_nome,opr_codigo from operadoras where opr_product_type!=0 order by opr_nome";
	$rs_oper = SQLexecuteQuery($sql_opr);
	if($rs_oper) {
		while ($rs_oper_row = pg_fetch_array($rs_oper)) {
				$operacao_array[$rs_oper_row['opr_codigo']]=$rs_oper_row['opr_nome'];
			}
	}
	return $operacao_array;
}

?>