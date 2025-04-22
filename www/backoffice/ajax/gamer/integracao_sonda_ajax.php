<?php 
	header("Content-Type: text/html; charset=ISO-8859-1",true);
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
//die("Stop - em construção");
        require_once "../../../includes/constantes.php";
        require_once RAIZ_DO_PROJETO . "backoffice/includes/topo_bko_inc.php";
        require_once RAIZ_DO_PROJETO . "includes/main.php";
        require_once RAIZ_DO_PROJETO . "includes/gamer/main.php";
	
	$time_start = getmicrotime();

	$date_now = date("Y-m-d H:i:s");
//	$date_now = date("H:i:s");
//	echo "date_now: ".$date_now."<br>\n";
//	echo "iforma = '$iforma', numcompra = '$numcompra', status = '$status', id_transacao_itau= '$id_transacao_itau'<br>";


//die("Stop");

//	transaction_id;
?><nobr><?php 

//	$sonda_url = getPartner_sonda_url_By_ID($rs_historico_notify_row['ipnh_store_id']);


//	Payment Received:  - $store_id=10402, $order_id=103041	-> store_id=10402&order_id=103041
//	Incomplete payment:  - $store_id=10402, $order_id=70669		-> store_id=10402&order_id=70669
//echo "store_id: $store_id, order_id: $order_id<br>";
//echo "Is_R: ".((b_IsUsuarioReinaldo_2_repeat())?"Y":"N")."<br>";

	if($store_id=="10406" && (!b_IsBKOUsuarioSondaIntegracao_repeat())) {
		echo "Bloqueado<br>Contate o administrador<br>(3223)";
	} else {
		// send data back to caller
		echo "<nobr><span style='font-size:10px; font-family: tahoma,arial,sans serif'>".$date_now."</nobr><br>\n";
		$status_transaction = getIntegracaoStatus($store_id, $order_id, $a_retorno);
//echo "<pre>".print_r($a_retorno, true)."</pre>";
		$sonda = getIntegracaoSonda($store_id, $order_id, $aline);
//echo "<hr>".$sonda."<hr>";
//echo "<pre>".print_r($aline, true)."</pre>";
//echo "<hr>".htmlentities($aline)."<hr>";
//echo "{".$a_retorno['status']." - ".$aline['retcod']."}<br>";
		echo "[".
				(($sonda=='1') ? "<span style='color:#009900' title='Resposta do parceiro:\nstore_id: ".$store_id."\norder_id: ".$order_id."\namount: ".$aline['amount']."\nretcod: ".$sonda."\ndata crédito: ".$aline['credit_date']."\n". 
				"client_email: ".(($aline['client_email']!="") ? $aline['client_email']."\n" : "(vazio)" )
				."'>OK</span>": 
					(($sonda=='2')?"<font color='#0000ff'>Processando</font>":"<font color='#FF0000'>none</font>")
				)
			."]";	// "".print_r($aline, true).""
//echo "store_id: ".$store_id."\norder_id: ".$order_id."\namount: ".$aline['amount']."\nretcod: ".$sonda."\ndata crédito: ".$aline['credit_date']."\n". "client_email: ".(($aline['client_email']!="") ? $aline['client_email']."\n" : "(vazio)" ) ."";
		echo ( (($a_retorno['status']=='1' && ($aline['retcod']=='1')) || ($a_retorno['status']=='3' && ($aline['retcod']!='1'))) ?" <font color='red'>NO SYNC<font>":"");
		if(b_IsUsuarioReinaldo_2_repeat() && ($aline['retcod']!='1')) {
				echo "<pre>".print_r($aline, true)."</pre>";
		}

		$msg = "userlogin_bko: '".$GLOBALS['_SESSION']['userlogin_bko']."'\nstore_id: '".$store_id."'\norder_id: ".$order_id."\naline: ".print_r($aline, true)."\n".str_repeat("-", 80)."\n";
		gravaLog_SondaIntegracao("Sonda Integração: ".$msg."\n");
	}


function gravaLog_SondaIntegracao($mensagem){
	//Arquivo
	$file = RAIZ_DO_PROJETO . "log/log_integracao_Sonda.txt";

	//Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} else {
		echo "erro ao gravar em 'log_integracao_Sonda.txt'<br>";
	}
//	echo "gravou '$mensagem'<br>";
}


// Estes métodos estão definidos em topo_bko.php, repetidos aqui com outro nome para não ter que inserir topo_bko.php
	function b_IsUsuarioReinaldo_2_repeat(){
//echo "<pre>".print_r($GLOBALS['_SESSION'], true)."</pre>";

		$stmp = $GLOBALS['_SESSION']['userlogin_bko'];
//echo "$stmp<br>";
		if(strtoupper($stmp)=="WAGNER") {
			return true;
		}
		return false;

	}

	function b_IsBKOUsuarioSondaIntegracao_repeat(){
		$usuarios_BKO_Admin = array('FABNASCI', 'GLAUCIA', 'WAGNER', 'ODECIO', 'FABIO', 'TAMY');
		$stmp = $GLOBALS['_SESSION']['userlogin_bko'];

		if(in_array(strtoupper($stmp), $usuarios_BKO_Admin)) {
			return true;
		}
		return false;

	}

?></nobr>
