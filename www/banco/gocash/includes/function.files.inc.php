<?php
$a_fields_monitor_gocash = array(
						'Titulo', 
						'DateCreated', 
						'LastStatus', 
						'DateLastStatus', 
);

/*
   [Titulo] => GoCash monitor File
    [DateCreated] => 2012-08-16 16:36:35
    [LastStatus] => Stopped
    [DateLastStatus] => 2012-08-16 16:36:00
    [monitor_age] => 140
    [status_age] => 175
*/
function get_gocash_monitor_info($params) {
//echo "<pre>".print_r($params, true)."</pre>";
	$sret  = "";
	$sret .= "<div style='background-color:#ccffcc'>\n";
	$sret .= "<table border='0' cellpadding='0' cellspacing='1' width='80%' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
	$sret .= "<tr><td align='center' valign='top'>\n";
	$sret .= "<font color='#666666' size='2' face='Arial, Helvetica, sans-serif'>\n";
	$sret .= "<nobr>A conexão com GoCash está ".(($params['LastStatus']=="Running")?"<span style='color:blue'>CONECTADA</span>":"<span style='color:red'>desconectada</span>")."</nobr><br>\n";
	$sret .= "<nobr>desde ".$params['DateLastStatus']." (faz <span title='status_age: " . $params['status_age'] . "'>" . convert_secs_to_string_global($params['status_age']) . "</span>)</nobr><br>\n";
	$sret .= "<nobr>Última consulta ".$params['DateCreated'] . " (faz <span title='monitor_age: " . $params['monitor_age'] . "'>" . convert_secs_to_string_global($params['monitor_age']) . "</span>)</nobr><br>\n";
	$sret .= "</font>\n";
	$sret .= "<nobr><a href='http://service.gocashgamecard.com:8081/GoCashCardService.asmx' target='_blank'><font color='#666666' size='2' face='Arial, Helvetica, sans-serif'>Consulte o site do webservice</font></a></nobr>\n";
	$sret .= "</td></tr></table>\n";

	return $sret;
}

// ========================================
function gocash_monitor_load(&$params) {
	$bDebug = false;
	if($bDebug) echo "  SALVA FILE MONITOR GOCASH (" . date('d/m/Y - H:i:s') . ")\n";
//echo "GC_MONITOR_FILE: ".GC_MONITOR_FILE."<br>";

	// Salva o file monitor para mostrar no Backoffice
	try {
		if ($handle = fopen(GC_MONITOR_FILE, 'r')) {
//			$contents = fread($handle);
			$contents = stream_get_contents($handle);
			
			fclose($handle);
//echo "<pre>".print_r($contents, true)."</pre>";

				$a_content = explode("\n",$contents);
//echo "<pre>".print_r($a_content, true)."</pre>";
			
				$params = array();
				foreach($a_content as $key => $val) {
					if(strlen(trim($val))>0) {
						$ipos = strpos($val, ":");
						if($ipos!==false) {
							$name = trim(substr($val, 0, $ipos));
							$value = trim(substr($val, $ipos+1));
//							echo "'$name' -> '$value'<br>";
							$params[$name] = $value;
						}
					}
				}
				$params['monitor_age'] = strtotime(date("Y-m-d H:i:s"))-strtotime($params['DateCreated']); 
				$params['status_age'] = strtotime(date("Y-m-d H:i:s"))-strtotime($params['DateLastStatus']); 

/*
//echo "<pre>".print_r($params, true)."</pre>";
echo "data_last_status: '".$params['DateLastStatus']."'<br>";
echo "last_status: '".$params['LastStatus']."'<br>";
echo "data_created: '".$params['DateCreated']."'<br>";
echo "monitor age: ".number_format($params['monitor_age'], 2, '.', '.')."s<br>";
echo "status age: ".number_format($params['status_age'], 2, '.', '.')."s<br>";
*/
		} else {
			echo "\nError: Couldn't open GoCash Monitor File for reading\n";
		}

		// Monta processa
		

	} catch (Exception $e) {
		echo "Error(6A) reading GoCash monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage()."\n";
	}

}

function gocash_monitor_save($params_out) {

	$bDebug = false;
	if($bDebug) echo "  SALVA FILE MONITOR GOCASH (" . date('d/m/Y - H:i:s') . ")\n";

	// Monta mensagem 
	$content  = "";
	foreach($GLOBALS['a_fields_monitor_gocash'] as $key => $val) {
		$content .= $val.":".$params_out[$val]."\n";
	}

	// Salva o file monitor para mostrar no Backoffice
	try {
		if ($handle = fopen(GC_MONITOR_FILE, 'w')) { 
			fwrite($handle, $content);
			
			fclose($handle);
		} else {
			echo "\nError: Couldn't open GoCash Monitor File for writing\n";
		}
	} catch (Exception $e) {
		echo "Error(6B) writing GoCash monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage()."\n";
	}


}

function gravaLog_GoCash($mensagem){
	
		//Arquivo
		$file = $raiz_do_projeto . "log/log_GoCash.txt";
	
		//Mensagem
		$mensagem =  str_repeat("-", 80)."\n".date('Y-m-d H:i:s'). " " .$GLOBALS['_SERVER']['SCRIPT_FILENAME'] . "\n" . $mensagem . "\n";
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
	
}


?>