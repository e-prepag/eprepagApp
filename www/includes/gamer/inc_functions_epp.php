<?php
function gravaLog_EPPCASH_Sonda($mensagem){
	
		//Arquivo
		$file =  $GLOBALS['raiz_do_projeto']."log/log_EPP_CASH_Sonda.txt";
	
		//Mensagem
		$mensagem =  str_repeat("-", 80)."\n".date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
}

function getSondaPINsEPP($numcompra, &$dtPag) {
        if(!$numcompra) {
		$dtPag = "";
		return "0"; 
	} else {
		$sql = "select status, dataconfirma from tb_pag_compras where iforma='E' and numcompra = '$numcompra'";
		$rs_transacoes = SQLexecuteQuery($sql);
		if($rs_transacoes && pg_num_rows($rs_transacoes)) {
			$rs_transacoes_row = pg_fetch_array($rs_transacoes);
			$iforma_pin = $rs_transacoes_row['iforma_pin'];
			if($iforma_pin=='G') {
				// PINs Gocash
				$status_gocash = getSondaGoCash($numcompra, $dtPag);
				return $status_gocash; 
			} else {
				// PINs EPP 
				$status = $rs_transacoes_row['status'];
				$dtPag  = $rs_transacoes_row['dataconfirma'];
				return $status; 
			}
		}
	}
}

function getSondaGoCash($numcompra, &$dtPag) {
	// Cria instтncia de classe de integraчуo GoCash
	// Consulta status
	// Processa retorno para indicar "status=3" se completo ou "status=0" se incompleto (depois podemos indicar "status=-1" para cancelados, mas hoje nуo usamos)
        return "0";
}
?>