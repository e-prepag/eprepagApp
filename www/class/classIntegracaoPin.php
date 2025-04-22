<?php

//Dados Intergrador de PINs EPP
/*****************************************************
LEGENDA:
USE_CHECK	=> 1 = Utiliza confirmação de solicitação através do PARTNETR_CHECK
			=> 2 = NÃO Utiliza confirmação de solicitação

PRODUCT_TYPE=> 1 = Utiliza somente PINs Produto
			=> 2 = Utiliza somente PINs Moeda
			=> 3 = Utiliza PINs Produto e PINs Moeda simultaneamente 
*******************************************************/

//Dados de depuração
$partner_dep = array(
	'13' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'47' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'48' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'51' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'73' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'81' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'86' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'88' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'89' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'90' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'96' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'102' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'103' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'124' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'137' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'142' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'143' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'144' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'147' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
	'148' => array(
				'email'		=> 'wagner@e-prepag.com.br',
				'depurar'	=> false,
				),
);

// CODRETEPP VALORES INTERNOS
$notify_list_values = array(
	'SV' => '1',
	'SU' => '2',
	'FC' => '3',
	'FV' => '4',
	'FA' => '5',
	'FI' => '6',
	'F4' => '7',
	'VD' => '8',
	'ID' => '9',
	'PU' => 'A',
	'SD' => 'B',
	'PO' => 'C',
	'ND' => 'D',
	'EU' => 'E',
	'EG' => 'F',
	'OL' => 'G',
	'PF' => 'H',
	'OF' => 'I',
	'CF' => 'J',
	'IN' => 'K',
	'FD' => 'L',
	'DE' => 'M',
	'TD' => 'N',
	'IM' => 'O',
);

// CODRETEPP VALORES PUBLISHER
$notify_list_values_pub = array(
	'SV' => '1',
	'SU' => '2',
	'FP' => '3',
	'VD' => '4',
	'PU' => '5',
	'SD' => '6',
	'PO' => '7',
	'ND' => '8',
	'EU' => '9',
	'EG' => 'A',
	'OL' => 'B',
	'PF' => 'C',
	'OF' => 'D',
	'CF' => 'E',
	'BK' => 'F',
	'IN' => 'G',
	'FD' => 'H',
	'DE' => 'I',
	'TD' => 'J',
	'IM' => 'K',
);

// CODRETEPP LEGENDA INTERNA
$notify_list = array(
	$notify_list_values['SV'] => 'Sucesso Validacao',
	$notify_list_values['SU'] => 'Sucesso Utilizacao',
	$notify_list_values['FC'] => 'Falta parametro: Codigo do PIN',
	$notify_list_values['FV'] => 'Falta parametro: Valor do PIN',
	$notify_list_values['FA'] => 'Falta parametro: ACTION',
	$notify_list_values['FI'] => 'Falta parametro: ID',
	$notify_list_values['F4'] => 'Faltam os quatros parametros: PIN, Valor, Acao e ID',
	$notify_list_values['VD'] => 'O PIN possui valor diferente ao informado',
	$notify_list_values['ID'] => 'O IP do requisitante nao confere com o cadastrado',
	$notify_list_values['PU'] => 'PIN ja utilizado',
	$notify_list_values['SD'] => 'PIN com Status diferente de Disponivel e Vendido',
	$notify_list_values['PO'] => 'Operadora nao cadastrada, PIN de outra operadora, Id diferente, ou opr_codigo e NULL',
	$notify_list_values['ND'] => 'PIN nao existe no DB',
	$notify_list_values['EU'] => 'ERRO na Utilizacao',
	$notify_list_values['EG'] => 'ERRO GERAL',
	$notify_list_values['OL'] => 'SISTEMA OFF LINE',
	$notify_list_values['PF'] => 'CPF Invalido',
	$notify_list_values['OF'] => 'Sistema de consulta de CPF Inativo',
	$notify_list_values['CF'] => 'Falta parametro: CPF',
	$notify_list_values['BK'] => 'PIN Bloqueado momentaneamente por questão de Segurança',
	$notify_list_values['IN'] => 'PIN Cartão ainda não Ativado no Caixa',
	$notify_list_values['FD'] => 'Falta parametro: Data de Nascimento',
	$notify_list_values['DE'] => 'Data de Nascimento não confere com registrado junto a Receita Federal',
	$notify_list_values['TD'] => 'Este CPF está temporariamente desabilitado para compras',
	$notify_list_values['IM'] => 'Idade informada menor que a mínima permitida',
);

// CODRETEPP LEGENDA PUBLISHER
$notify_list_pub = array(
	$notify_list_values_pub['SV'] => 'Sucesso Validacao',
	$notify_list_values_pub['SU'] => 'Sucesso Utilizacao',
	$notify_list_values_pub['FP'] => 'Falta parametro',
	$notify_list_values_pub['VD'] => 'O PIN possui valor diferente ao informado',
	$notify_list_values_pub['PU'] => 'PIN ja utilizado',
	$notify_list_values_pub['SD'] => 'PIN com Status diferente de Disponivel e Vendido',
	$notify_list_values_pub['PO'] => 'Dado do Publisher incorreto',
	$notify_list_values_pub['ND'] => 'PIN nao existe no DB',
	$notify_list_values_pub['EU'] => 'ERRO na Utilizacao',
	$notify_list_values_pub['EG'] => 'ERRO GERAL',
	$notify_list_values_pub['OL'] => 'SISTEMA OFF LINE',
	$notify_list_values_pub['PF'] => 'CPF Invalido',
	$notify_list_values_pub['OF'] => 'Sistema de consulta de CPF Inativo',
	$notify_list_values_pub['CF'] => 'Falta parametro: CPF',
	$notify_list_values_pub['BK'] => 'PIN Bloqueado momentaneamente por questão de Segurança',
	$notify_list_values_pub['IN'] => 'PIN Cartão ainda não Ativado no Caixa',
	$notify_list_values_pub['FD'] => 'Falta parametro: Data de Nascimento',
	$notify_list_values_pub['DE'] => 'Data de Nascimento não confere com registrado junto a Receita Federal',
	$notify_list_values_pub['TD'] => 'Este CPF está temporariamente desabilitado para compras',
	$notify_list_values_pub['IM'] => 'Idade informada menor que a mínima permitida',
);

function converte_detalhe_codretepp($valor){
	global $notify_list_values,$notify_list_values_pub;
	switch ($valor) {
		case $notify_list_values['SV'] : 
				return $notify_list_values_pub['SV'];
				break;
		case $notify_list_values['SU'] : 
				return $notify_list_values_pub['SU'];
				break;
		case $notify_list_values['FC']:
				return $notify_list_values_pub['FP'];
				break;
		case $notify_list_values['FV']:
				return $notify_list_values_pub['FP'];
				break;
		case $notify_list_values['FA']:
				return $notify_list_values_pub['FP'];
				break;
		case $notify_list_values['FI']:
				return $notify_list_values_pub['FP'];
				break;
		case $notify_list_values['F4']:
				return $notify_list_values_pub['FP'];
				break;
		case $notify_list_values['VD']:
				return $notify_list_values_pub['VD'];
				break;
		case $notify_list_values['ID']:
				return $notify_list_values_pub['PO'];
				break;
		case $notify_list_values['PU']:
				return $notify_list_values_pub['PU'];
				break;
		case $notify_list_values['SD']:
				return $notify_list_values_pub['SD'];
				break;
		case $notify_list_values['PO']:
				return $notify_list_values_pub['PO'];
				break;
		case $notify_list_values['ND']:
				return $notify_list_values_pub['ND'];
				break;
		case $notify_list_values['EU']:
				return $notify_list_values_pub['EU'];
				break;
		case $notify_list_values['EG']:
				return $notify_list_values_pub['EG'];
				break;
		case $notify_list_values['OL']:
				return $notify_list_values_pub['OL'];
				break;
		case $notify_list_values['PF']:
				return $notify_list_values_pub['PF'];
				break;
		case $notify_list_values['OF']:
				return $notify_list_values_pub['OF'];
				break;
		case $notify_list_values['CF']:
				return $notify_list_values_pub['CF'];
				break;
		case $notify_list_values['BK']:
				return $notify_list_values_pub['BK'];
				break;
		case $notify_list_values['IN']:
				return $notify_list_values_pub['IN'];
				break;
		case $notify_list_values['FD']:
				return $notify_list_values_pub['FD'];
				break;
		case $notify_list_values['DE']:
				return $notify_list_values_pub['DE'];
				break;
		case $notify_list_values['TD']:
				return $notify_list_values_pub['TD'];
				break;
		case $notify_list_values['IM']:
				return $notify_list_values_pub['IM'];
				break;
	}
}

function retorna_ip_acesso() {
	$realip = "";
	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
   } else {
		if (getenv('HTTP_X_FORWARDED_FOR')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} else {
			$ip = getenv('REMOTE_ADDR');
		}
   }
   return $ip;
}

function retorna_dominio($opr_codigo) {
	$sql = "select opr_partner_dominio from operadoras where opr_codigo=".$opr_codigo." and opr_partner_dominio!='';";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['opr_partner_dominio'] != '')
			return $rs_log_row['opr_partner_dominio'];
		else return '';
	}
}

function retorna_ip_integracao($opr_codigo) {
	$sql = "select opr_ip from operadoras where opr_codigo=".$opr_codigo." and opr_ip!='';";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['opr_ip'] != '')
			return $rs_log_row['opr_ip'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function existeIdVendaGoCASH($venda_id_rand){

	$ret = true;
	 
	//SQL
	$sql = "select count(*) as qtde from pins_gocash ";
	$sql .= " where pgc_vg_id = " . SQLaddFields($venda_id_rand, "");
	$rs = SQLexecuteQuery($sql);
	if($rs && pg_num_rows($rs) > 0){
		$rs_row = pg_fetch_array($rs);
		if($rs_row['qtde'] == 0) $ret = false;
	}			
		
	return $ret;   	
}

function obterIdVendaValidoGoCASH(){

	$maxID = 1000-1;
	$nmax = 10;
	$n = 1;
	$s_ids = "";

	$time_start_stats = getmicrotime();

	$venda_id_rand = date('YmdHis').mt_rand(1, $maxID);
	$s_ids .= $venda_id_rand.", ";
	while(existeIdVendaGoCASH($venda_id_rand)){

		$venda_id_rand = date('YmdHis').mt_rand(1, $maxID);

		$s_ids .= $venda_id_rand.", ";
		$n++;
	}
	
	$msg = (($n==1)?"Just one shot!!! ":"ntentativas: $n ")." ($s_ids)";
	gravaLog_obterIdVendaValidoGoCASH($msg);

	if($n>1) {
		$msg = "Elapsed time ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."s";
		gravaLog_obterIdVendaValidoGoCASH($msg);
	}
	if($n>=$nmax) {
		$msg = "Demorou muito para encontrar um id_venda ($n>=$nmax).";
		gravaLog_obterIdVendaValidoGoCASH($msg);
	}
	
	return $venda_id_rand;
}

function gravaLog_obterIdVendaValidoGoCASH($mensagem){

	//Arquivo
	$file = $GLOBALS['raiz_do_projeto'] . "log/logObterIdVendaValidoGoCASH.txt";
	
	//Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $mensagem . PHP_EOL;

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 

}

function send_debug_info_by_email_PINCASH($subject, $body, $vetor_tmp, $id) {
	if($vetor_tmp[$id]['depurar']){
		$s_testing_email = $vetor_tmp[$id]['email'];
		if($s_testing_email != "") {
			enviaEmail(
					$s_testing_email,
					"",
					"",
					$subject,
					$body
					//str_replace(PHP_EOL, "<br>", $body)
			);
		}
	}//end if($vetor_tmp[$id]['depurar'])
}
?>