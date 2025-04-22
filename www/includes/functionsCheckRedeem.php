<?php
//Funчуo que retorna o tamanho do GiftCard
function RetonaTamanhoPINEPPCARD_SINGLEPAGE($pin) {
	$tamanho = strlen(trim($pin));
	if($tamanho == $GLOBALS['PIN_CARD_TAMANHO']) {
		return true;
	}
	else {
		return false;
	}
}//end function RetonaTamanhoPINEPPCARD_SINGLEPAGE($pin)

//Funчуo que Grava LOG de Integraчуo de PIN
function gravaLog_IntegracaoPIN($mensagem){
	
		//Arquivo
		$file = $GLOBALS['raiz_do_projeto'] . "log/logCheckRedeem.txt";
	
		//Mensagem
		$mensagem =  str_repeat("-", 80).PHP_EOL.date('Y-m-d H:i:s'). " " .$GLOBALS['_SERVER']['SCRIPT_FILENAME'] . PHP_EOL . $mensagem . PHP_EOL;
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
	
}//end function gravaLog_EPPCARD

//Funчуo desenvolvida exclusivamente para a RIOT
function publisherOrderId($pin_codinterno, $riot_order_id, $pin_channel) {
	$sql = "INSERT INTO pins_riot_id VALUES (".$pin_codinterno.",'".$riot_order_id."','".$pin_channel."')";
	//echo $sql."<br>";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
		 echo "Erro ao Salvar o ID da Transaчуo do Publisher (RIOT).".PHP_EOL;
	}
}//end function publisherOrderId

function logEventsONGAME($msg) {
    
    global $raiz_do_projeto;

    $log  = PHP_EOL."=================================================================================================".PHP_EOL;
    $log .= "DATA -> ".date("d/m/Y - H:i:s").PHP_EOL;
    $log .= "---------------------------------".PHP_EOL;
    $log .= htmlspecialchars_decode($msg);			

    $fp = fopen($raiz_do_projeto . "log/logONGAME_DEBUG.log", 'a+');
    fwrite($fp, $log);
    fclose($fp);		
    
}//end function logEventsONGAME($msg)
?>