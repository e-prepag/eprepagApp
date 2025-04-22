
<?php
        require_once "../../../../includes/constantes.php";
	// Configura dirs
//	$sDirPOS = "C:/Sites/E-Prepag/www/web/POS/";	//	"../../../POS/";
	$sDirPOS = $raiz_do_projeto."arquivos_gerados/FTPUser/POS/";
	$sDirAVBFiles =	$raiz_do_projeto."public_html/sys/admin/stats/AVBFiles/";		// "AVBFiles/";

	if($print_output) {
		echo '<div class="row"><h4>Copiando arquivos novos</h4></div>';
	}
//echo "sDirPOS: $sDirPOS<br>\n";
//echo "sDirAVBFiles: $sDirAVBFiles<br>\n";
	// Copia arquivos novos
	if ($handle_pos = opendir($sDirPOS)) {
            if(is_dir($sDirPOS)) {
		while (false !== ($file_pos = readdir($handle_pos))) { 
//echo "file_pos: $file_pos   ";
			if ($file_pos != "." && $file_pos != "..") {
				if (substr($file_pos, strlen($file_pos)-3, 3)=="AVB" ) {
					// Procura arquivo 	$sDirAVBFiles
					if(!file_exists($sDirAVBFiles.$file_pos)) {
						if($print_output) {
							echo "Copiando ".$file_pos."<br>\n";
						}
						if (!copy($sDirPOS.$file_pos, $sDirAVBFiles.$file_pos)) {
							if($print_output) {
								echo "<font color='#FF0000'>Falha ao copiar ".$sDirPOS.$file_pos." => ".$sDirAVBFiles.$file_pos."...</font><br>\n";
							}
						}
					}
				}
			}
//echo "<br>\n";
		}
            }
	}
	closedir($handle_pos);


	if($print_output) {
		echo '<div class="row"><h4>Carregando registros de arquivos novos</h4></div>';
	}
	// Le a lista de nomes de arquivos disponíveis para carregar
	$NBFile=0;
	if ($handle = opendir($sDirAVBFiles)) {
            if(is_dir($sDirAVBFiles)) {
		while (false !== ($file = readdir($handle))) { 
			if ($file != "." && $file != "..") {
				if (substr($file, strlen($file)-3, 3)=="AVB" ) {
					$FileArray[$NBFile] = $file;
					$NBFile++;
				}
			}
		}
            }
	}
	closedir($handle);

	// 
	$nLinesTotal=0;
	$lVendasTotal = 0;
	for ($i=0; $i<$NBFile; $i++) {
		// Vendas
		$lVendas = 0;
		// Obtem nome do arquivos
		$FName=$FileArray[$i];

//echo "Filename: '".$sDirAVBFiles."/".$FName."'<br>\n";

		// Le conteúdo do arquivo
		$fh = fopen($sDirAVBFiles."/".$FName, 'r');
		$theData = fread($fh, filesize($sDirAVBFiles."/".$FName));
		fclose($fh);

//echo "theData: '$theData'<br>\n";

		// Extrai info do lote
		// 012345678901234567890123
		// EPREPAG_MU20032008052717.AVB
		$lote_game = substr($FName, 8, 2);
		$lote_opr_codigo = 0;
			if($lote_game=="MU") {
				$lote_game_full = "Mu Online";
				$lote_opr_codigo = 17;
			} else if($lote_game=="OG") {
				$lote_game_full = "OnGame";
				$lote_opr_codigo = 13;
			} else if($lote_game=="HB") {
				$lote_game_full = "Habbo Hotel";
				$lote_opr_codigo = 16;
			}
		$sLoteDay = substr($FName, 10, 2);
		$sLoteMonth = substr($FName, 12, 2);
		$sLoteYear = substr($FName, 14, 4);
		$sLoteHour = substr($FName, 18, 2);
		$sLoteMinute = substr($FName, 20, 2);
		$sLoteSecond = substr($FName, 22, 2);
//		$lote_date = $sLoteMonth."/".$sLoteDay."/".$sLoteYear." ".$sLoteHour.":".$sLoteMinute.":".$sLoteSecond; 

		// Pega a data do lote como o dia anterior ao dia do arquivo
		$dateb0 = $sLoteYear."-".$sLoteMonth."-".$sLoteDay." ".$sLoteHour.":".$sLoteMinute.":".$sLoteSecond;
		$dateb0 = strtotime($dateb0);
		$lote_date = mktime(date("H",$dateb0), date("i",$dateb0), date("s",$dateb0), date("m",$dateb0), date("d",$dateb0)-1, date("Y",$dateb0));

//echo "strtotime(dateb0): ".date("Y-m-d H:i:s",$dateb0)."<br>\n";
//echo "lote_date: ".date("Y-m-d H:i:s",$lote_date)."<br>\n";

		// Converte para Array de linhas
		$aLines = explode("\n", $theData);
		
		// Lista todas as linhas
		if($print_output) {
			echo "Titulo: <b><span style=\"background-color:#33CCFF\">".$FName."</span></b> ".$lote_game_full." (<b>".$lote_game."</b>) (".date("Y-m-d H:i:s",$lote_date).") -> <b>".$aLines[0]."</b><br>\n";
		}
		$n = 0;
		for ($j=1; $j<count($aLines); $j++) {
//echo "$j: ".$aLines[$j]."<br>";
			if(strlen($aLines[$j])==0) {
				continue;
			}

			// Antigo
			//		2584987|50|AUTO POSTO ROMANO|POSTOS DE COMBUSTIVEL|SANTOS|SP|
            // Novo
			//			0	1	2					3						4	  5  6		7		8		9
			//		2558062|10|CARNES MUNICIPAL|CASA DE CARNES, AVICOLA|SAO PAULO|SP|11|66744123|21/08/2008|10:39|
			//echo "".$aLines[$j]."<br>\n";
			$aFields = explode("|", $aLines[$j]); 

			if(count($aFields)>0) {
				add_item_in_DB($lote_game, $lote_opr_codigo, $lote_date, $aFields, $print_output); 
			} else {
				echo "Já foi salvo: ".$lote_game." - ".$lote_opr_codigo." - ".$lote_date." - <pre>".print_r($aFields)."</pre> - ".$print_output."<br>";
			}
		}

		if($print_output) {
			echo "<hr width=\"50%\">\n";
		}
	}
	
	// 0		1	2					3						4		5	6		7			8		9
	// 1721158, 10, BIG POSTO TUBARAO, POSTOS DE COMBUSTIVEL, LONDRINA, PR, 11,	66744123,	21/08/2008,	10:39
	function add_item_in_DB($lote_game, $lote_opr_codigo, $lote_date, $afields, $print_output) {

		$sql = "select ve_id from dist_vendas_pos where ve_id = ".$afields[0].";";
//echo "sql: $sql<br>\n";
		$ret = SQLexecuteQuery($sql);

		if(!$ret || pg_num_rows($ret) == 0) {
//			$sql = "insert into dist_vendas_pos (ve_id, ve_valor, ve_data, ve_jogo, ve_estado, ve_cidade, ve_estabtipo, ve_estabelecimento) values (".$afields[0].", ".$afields[1].", '".date("Y-m-d H:i:s", $lote_date)."', '".$lote_game."', '".$afields[5]."', '".$afields[4]."', '".$afields[3]."', '".$afields[2]."')";
			if(count($afields)>7) {
				// '29/08/2008'
				$sRegDay = substr($afields[8], 0, 2);
				$sRegMonth = substr($afields[8], 3, 2);
				$sRegYear = substr($afields[8], 6, 4);

				$sql = "insert into dist_vendas_pos (ve_id, ve_valor, ve_data_inclusao, ve_jogo, ve_estado, ve_cidade, ve_estabtipo, ve_estabelecimento, ve_ddd, ve_tel, ve_opr_codigo, ve_cod_rede) values (".$afields[0].", ".$afields[1].", '".($sRegYear."-".$sRegMonth."-".$sRegDay )." ".$afields[9]."', '".$lote_game."', '".$afields[5]."', '".str_replace("'", "''", $afields[4])."', '".str_replace("'", "''", $afields[3])."', '".str_replace("'", "''", $afields[2])."', '".$afields[6]."', '".$afields[7]."', ".$lote_opr_codigo.", '9999')";
			} else {
				$sql = "insert into dist_vendas_pos (ve_id, ve_valor, ve_data_inclusao, ve_jogo, ve_estado, ve_cidade, ve_estabtipo, ve_estabelecimento, ve_opr_codigo, ve_cod_rede) values (".$afields[0].", ".$afields[1].", '".date("Y-m-d H:i:s", $lote_date)."', '".$lote_game."', '".$afields[5]."', '".$afields[4]."', '".str_replace("'", "''", $afields[3])."', '".str_replace("'", "''", $afields[2])."', ".$lote_opr_codigo.", '9999')";
			}

//echo "sql: $sql<br>\n";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) {
				if($print_output) {
					echo "<font color='#FF0000' style='background-color:#FFFF66'>ERRO ao cadastrar registro ".$afields[0]."</font><br>\n";
				}
			} else {
				if($print_output) {
					echo "<font color='#66CC66'>Registro ".$afields[0]." cadastrado com sucesso</font>(".date("Y-m-d H:i:s").")<br>\n";
				}
			}
		} else {
//			if($print_output) {
				echo "<font color='#FF0000'>Registro ".$afields[0]." já existe</font> (".date("Y-m-d H:i:s").")<br>\n";
//			}
		}

	}

?>