<?php
// require_once "/www/includes/bourls.php";
// https://www.e-prepag.com.br:$server_port/admin/pins_store/pins_store_lista.php

// https://www.e-prepag.com.br:$server_port/admin/pins_store/pins_store_lista_pin.php

require_once $raiz_do_projeto . "includes/main.php";

set_time_limit(6000);

if(!isset($_SERVER['COMPUTERNAME']) ) $_SERVER['COMPUTERNAME'] = null;

/**
 ** Implementa o bloqueio por flags para carga no estoque
*/

function flag_publisher_test($dec_id_epp_cash) {
	$sql = "update distribuidoras_epp_cash set dec_flag_carga_estoque_arquivo  = 1 where dec_id_epp_cash = $dec_id_epp_cash and dec_flag_carga_estoque_arquivo  = 0;";
	$ret2 = SQLexecuteQuery($sql);

	$cmdtuples = pg_affected_rows($ret2);
//	echo $cmdtuples . " tuples are affected.<br>".PHP_EOL;

	if($cmdtuples===1) {
		return 0;
	} else {
		return 1;
	}
}

function flag_publisher_unblock($dec_id_epp_cash) {
	$sql = "update distribuidoras_epp_cash set dec_flag_carga_estoque_arquivo  = 0 where dec_id_epp_cash = $dec_id_epp_cash;";
	$ret2 = SQLexecuteQuery($sql);
//	if(!$ret2) echo "<font color='#FF0000'><b>Erro ao setar flag</b></font>".PHP_EOL."<br><br>";
}

/**
 ** FIM de Implementa o bloqueio por flags para carga no estoque
*/

//funcao que gera senha do arquivo RAR
function geraSenha() {
	$return = "";
	// Senha de 256 bit / 32 Bytes
	$tamanhoSenha = 32;
	// Variável contendo caracteres possíveis
	$sPossib = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789';
	// add random characters to $return until $tamanhoSenha is reached
	for ($i=0; $i < $tamanhoSenha; $i++) {
		// pick a random character from the possible ones
		$char = substr($sPossib, mt_rand(0, strlen($sPossib) - 1), 1);
		// we don't want this character if it's already in the password
		// but repeat character  if length > range 
		$return .= $char;
	}
	return $return;
}


if(!empty($BtnGerarArq) && $tf_v_tipo==3) {
	$sEmailTo		= "daniela.oliveira@e-prepag.com.br";//"tamy@e-prepag.com.br,daniela.oliveira@e-prepag.com.br,financeiro@e-prepag.com.br";
	$sEmailToNome	= "Daniela Oliveira";
	//$sEmailTo		= "wagner.mbis@gmail.com";
	//$sEmailToNome	= "Wagner de Miranda";
	//$sEmailCc		= "daniela.oliveira@e-prepag.com.br";//tamy@e-prepag.com.br";
	$sEmailCcNome	= "Daniela Oliveira";//Tamlyn Keiko Souza Takahata";

	if($DISTRIBUIDORA_EPP == intval($distributor_codigo)) {
		 $opr_codigo_aux = $OPR_CODIGO_EPP;
	}
	else if($DISTRIBUIDORA_EPP_LH == intval($distributor_codigo)) {
		 $opr_codigo_aux = $OPR_CODIGO_EPP_LH;
	}
	else $opr_codigo_aux = "";

	//verificando se está habilitado a carga no estoque
	if(flag_publisher_test($distributor_codigo)) {
		echo "<font color='red'>Distribuidor já executando carga no Estoque ou em processo de geração de arquivo.</font><br>";
		gravaLog_Depurador("Estava BLOQUEADO!".PHP_EOL);
	} else {
//sleep(20);	
		gravaLog_Depurador("Estava desbloqueado!".PHP_EOL);

		// Deleta arquivos >5horas
		$now = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
		foreach (glob("arquivos/*.rar") as $filename) {
			if(($now-filemtime($filename))>5*3600) {
				unlink($filename);
			}
		}
		$sCodLote = "";
		// Armazena o range de lote
		for ($i=0; $i<count($ids_temp);$i++) {
			list($codlote,$codopr) = explode("|",$ids_temp[$i]);
			if (strlen($sCodLote)==0) {
				$sCodLote .= $codlote;
			}
			else $sCodLote .= ",".$codlote;
		}

		//Variavel contendo menssagem de erro
		$msg = "";

		//Inicia transacao
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.".PHP_EOL."</b></font><br>";
		
		$sql  = "select pin_codinterno,distributor_codigo, pin_lote_codigo, to_char(pin_dataentrada,'DD/MM/YYYY') as data, pin_formato, pin_valor, pin_codigo, pin_serial from pins_store ps where pin_status='".intval($PINS_STORE_STATUS_VALUES['A'])."' and distributor_codigo=".intval($distributor_codigo)." and pin_arq_gerado IS NULL and pin_lote_codigo IN (".$sCodLote.") order by pin_valor";
		//echo $sql;
		$rs_pins_email = SQLexecuteQuery($sql);
		if(!$rs_pins_email|| pg_num_rows($rs_pins_email) == 0) {
			$msg_pin .= "Todos os PINs Ativos da seleção j&aacute; foram utilizados em arquivos anteriores.<br>";
			$msg .= "<font color='#FF0000'><b>Todos os PINs Ativos da seleção j&aacute; foram utilizados em arquivos anteriores.".PHP_EOL."</b></font><br>";
		}
		else {
			//Instanciando Objetos para Descriptografia
			$chave256bits = new Chave();
			$ps = new AES($chave256bits->retornaChave());
			
			//Variavel contraladora de sucesso na importacao de PINs CASH para o Estoque
			$importacaoOk = false;
			
			//Verificando se o distribuidor é EPP
			if (($DISTRIBUIDORA_EPP != intval($distributor_codigo))&&($DISTRIBUIDORA_EPP_LH != intval($distributor_codigo))) {
				// Arquivo
				$path = $GLOBALS['raiz_do_projeto'];
				$url = "arquivos_gerados/pins_store/";
				$file = str_replace("(","-",str_replace(")","-",str_replace(" ","",$operacao_array[$distributor_codigo]))).date("YmdHis").str_pad(rand(0,999), 3, "0", STR_PAD_LEFT).".txt";
				$varArquivo = $path.$url.$file;
				$sArq = "";
                                if($DISTRIBUIDORA_EPAY == intval($distributor_codigo)) {
                                    while($rs_pins_email_row = pg_fetch_array($rs_pins_email)){ 
                                            $sArq .= str_pad($rs_pins_email_row['pin_lote_codigo'],8,"0", STR_PAD_LEFT)." ".str_pad($rs_pins_email_row['pin_serial'],16,"0", STR_PAD_LEFT)." ".$ps->decrypt(base64_decode($rs_pins_email_row['pin_codigo']))." 090 ".str_pad($rs_pins_email_row['pin_valor'],9,"0", STR_PAD_LEFT).".00".PHP_EOL;
                                    }
                                }//end if($DISTRIBUIDORA_EPAY != intval($distributor_codigo))
                                else if($DISTRIBUIDORA_INCOMM_REDETREL == intval($distributor_codigo)) {
                                    while($rs_pins_email_row = pg_fetch_array($rs_pins_email)){ 
                                            $sArq .= $ps->decrypt(base64_decode($rs_pins_email_row['pin_codigo']))."; ".$rs_pins_email_row['pin_valor'].".00".PHP_EOL;
                                    }
                                }//end else if($DISTRIBUIDORA_INCOMM_REDETREL == intval($distributor_codigo)) 
                                else {
                                    while($rs_pins_email_row = pg_fetch_array($rs_pins_email)){ 
                                            $sArq .= "0;0;0;".$ps->decrypt(base64_decode($rs_pins_email_row['pin_codigo'])).";".$rs_pins_email_row['pin_valor']."00;".$rs_pins_email_row['pin_serial'].";".$rs_pins_email_row['pin_lote_codigo'].PHP_EOL;
                                    }
                                }//end else
				$handle = fopen($varArquivo, "a+");
				if (fwrite($handle, $sArq) === FALSE) {
					$msg_pin .= "<font color='#0000CC'>N&atilde;o foi poss&iacute;vel gravar o Arquivo. </font><br>";
					$msg .= "<font color='#0000CC'>N&atilde;o foi poss&iacute;vel gravar o Arquivo. </font><br>";
				} else {
					$msg_pin .= "<font color='#0000CC'>Arquivo gravado com sucesso.</font><br>";
				}
				fclose($handle);

				if (file_exists($varArquivo)) {
					gravaLog_Depurador("Arquivo txt gerado com sucesso!".PHP_EOL."$varArquivo".PHP_EOL);
				}
				else  {
					gravaLog_Depurador("Arquivo txt NAUN gerado com sucesso!".PHP_EOL."$varArquivo".PHP_EOL);
				}
				//  RAR 
				$varArquivoRAR = substr($varArquivo,0,strpos($varArquivo,'.')).".rar";
									
				$senha = geraSenha();
				$scmd = "rar a -p".$senha." -ep ".$varArquivoRAR." ".$varArquivo;
				gravaLog_Depurador("Comando de compactação com senha!".PHP_EOL."$scmd".PHP_EOL);
				
				//**********************************************  Parou de funcionar o comand exec em 29/1/2013
				exec($scmd);

				// Make a new instance of the COM object
				//$WshShell = new COM("WScript.Shell");

				// Make the command window but dont show it.
				//$oExec = $WshShell->Exec("$scmd");

				//Testando se executou o 
				//if($oExec->Status == 0) gravaLog_Depurador(" Executou o Windows Script com sucesso!!".PHP_EOL);
				//else gravaLog_Depurador(" Não executou o Windows Script com sucesso!!".PHP_EOL);
				
				sleep(2);
				/*
				$handle = fopen($varArquivoRAR, "w+");
				if (fwrite($handle, "Teste") === FALSE) {
					$msg_pin .= "<font color='#0000CC'>N&atilde;o foi poss&iacute;vel gravar o Arquivo RAR. </font><br>";
					$msg .= "<font color='#0000CC'>N&atilde;o foi poss&iacute;vel gravar o Arquivo RAR. </font><br>";
				} else {
					$msg_pin .= "<font color='#0000CC'>Arquivo RAR gravado com sucesso.</font><br>";
				}
				fclose($handle);
				*/

				if (file_exists($varArquivoRAR)) {
					gravaLog_Depurador("Arquivo RAR gerado com sucesso!".PHP_EOL."$varArquivoRAR".PHP_EOL);
				}
				else {
					gravaLog_Depurador("Arquivo RAR NAUN gerado com sucesso!".PHP_EOL."$varArquivoRAR".PHP_EOL);
				}
				/*********************************************** comentado para naun esxcçluir o arquivo que será compacatado manualmente
				unlink($varArquivo);
				if (file_exists($varArquivo)) {
					gravaLog_Depurador("Arquivo txt NAUN excluido com sucesso!".PHP_EOL."$varArquivo".PHP_EOL);
				}
				else  {
					gravaLog_Depurador("Arquivo txt excluido com sucesso!".PHP_EOL."$varArquivo".PHP_EOL);
				}
				*/
			} //end if (($DISTRIBUIDORA_EPP != intval($distributor_codigo))&&($DISTRIBUIDORA_EPP_LH != intval($distributor_codigo)))
			else {
				//tamanho do serial na tabela estoque
				$serial_length = 10;

				$vetorLotesUtilizados = array();
				

				if(!empty($opr_codigo_aux)) {
							
					// Cria LoteID
					$sql = "select max(pin_lote_codigo) as max_pin_lote_codigo from pins where opr_codigo = ".$opr_codigo_aux;
					$rs_lote = SQLexecuteQuery($sql);
					if(!$rs_lote || pg_num_rows($rs_lote) == 0) {
						$ilote = 1;
					} else {
						$rs_lote_row = pg_fetch_array($rs_lote);
						$ilote = $rs_lote_row['max_pin_lote_codigo'] + 1;
					}
					
					// Obtem o ultimo serial
					$sql_serial = "select CAST(pin_serial AS BIGINT) as max_serial from pins where opr_codigo = ".$opr_codigo_aux." order by CAST(pin_serial AS BIGINT) desc limit 1;";
					$rs_serial = SQLexecuteQuery($sql_serial);
					if($rs_serial) {
						if (pg_num_rows($rs_serial) > 0) {
							$rs_serial_row = pg_fetch_array($rs_serial);
							$pin_serial = $rs_serial_row['max_serial'];
						}
						else {
							$pin_serial = 1;
						}
					} else {
						$msg .= "No Estoque para esta Operadora possui PIN_SERIAL ALPHA.";
					}

					// Importa os PINs CASH para a Tabela estoque
					$iaux = 1;
					//echo "[$msg]<br>";
					$msg_aux = $msg;
					while($rs_pins_email_row = pg_fetch_array($rs_pins_email)){ 
						$pin_serial ++;
						$spin_serial = str_pad(number_format($pin_serial, 0, '', ''), $serial_length, "0", STR_PAD_LEFT);
						if (!in_array($rs_pins_email_row['pin_lote_codigo'], $vetorLotesUtilizados, true)) {
							$vetorLotesUtilizados[]=$rs_pins_email_row['pin_lote_codigo'];
						}
						//transacao
						$sql = "insert into pins (pin_serial, pin_codigo, opr_codigo, pin_valor, pin_lote_codigo, pin_dataentrada, pin_canal, pin_horaentrada,pin_status,pin_validade) values ('".$spin_serial."', '".$ps->decrypt(base64_decode($rs_pins_email_row['pin_codigo']))."', ".$opr_codigo_aux.", ".$rs_pins_email_row['pin_valor'].", ".$ilote.", CURRENT_TIMESTAMP, 's', NOW(),'1',(NOW() + interval '6 month'));";
						$rs_pins_save = SQLexecuteQuery($sql);
						if(!$rs_pins_save) {
							$msg .= "Erro ao salvar o novo PIN ($sql)<br>";
						}
						else {
							$sql = "select pin_codinterno from pins where pin_codigo = '".$ps->decrypt(base64_decode($rs_pins_email_row['pin_codigo']))."' and opr_codigo = ".$opr_codigo_aux." and pin_serial = '".$spin_serial."' and pin_lote_codigo = ".$ilote.";";
							$rs_pins_estoque = SQLexecuteQuery($sql);
							if(!$rs_pins_estoque) {
								$msg .= "Erro ao selecionar o novo PIN no estoque ($sql)<br>";
							}
							else{
								$rs_pins_estoque_row = pg_fetch_array($rs_pins_estoque);
								$sql = "insert into tb_pins_store_pins (pins_pin_codinterno, pins_store_pin_codinterno) values (".$rs_pins_estoque_row['pin_codinterno'].", ".$rs_pins_email_row['pin_codinterno'].");";
								$rs_pins_save_tracer = SQLexecuteQuery($sql);
								if(!$rs_pins_save_tracer) {
									$msg .= "Erro ao salvar a rastreabilidade PIN no estoque e na pins_store($sql)<br>";
								}//end if(!$rs_pins_save)
							}//end else if(!$rs_pins_estoque)
						}//end else if(!$rs_pins_save)
					
					}//end while($rs_pins_email_row = pg_fetch_array($rs_pins_email))
					if($msg_aux == $msg) {
						$importacaoOk = true;
					}
				}//end if(!empty($opr_codigo_aux))
				else {
					$msg .= "<font color='#FF0000'><b>C&oacute;digo de Distribuidor n&atilde;o localizado.".PHP_EOL."<br></b></font><br>";
				}
			}//end else if (($DISTRIBUIDORA_EPP != intval($distributor_codigo))&&($DISTRIBUIDORA_EPP_LH != intval($distributor_codigo)))
			
			try {
			
				$email = new PHPMailer;


				//$email->Host     = "smtp.e-prepag.com.br";	//"localhost";
				//-----Alteração exigida pela BaseNet(11/2017)-------------//
				$email->Host     = "smtp.basenet.com.br";
				//---------------------------------------------------------//
				$email->Mailer   = "smtp";
				$email->From     = "suporte@e-prepag.com.br";
				$email->SMTPAuth = true;     // turn on SMTP authentication
				$email->Username = 'suporte@e-prepag.com.br';  // a valid email here
				$email->Password = '@AnQ1V7hP#E7pQ31'; //'985856';	//'850637'; 
				$email->FromName = "E-Prepag";	// " (EPP)"
				
				//-----Alteração exigida pela BaseNet(11/2017)-------------//
				$email->IsSMTP();
				$email->SMTPSecure = "ssl";
				$email->Port     = 465;
				//---------------------------------------------------------//
						/*	
							// Overwrite smt details for dev version cause e-prepag.com.br server reject it
							// You can just add your IP or use elseif with your details
							// When run bat files there is not ip address so we need use COMPUTERNAME to check
					//Comentar aki para envio de email através do e-prepag.com
							if(checkIP() || (class_exists('EmailEnvironment')  && EmailEnvironment::serverId() == 1)) {
								//  $email->SMTPDebug  = 1; descomentar para debugar 
								$email->Port     = 587;
								$email->Host     = "e-prepag.com";
								$email->Username = 'send@e-prepag.com';
								$email->Password = 'sendeprepag2013';
								}
						*/
				// Reply-to
				$email->AddReplyTo('suporte@e-prepag.com.br');

				$msg_temp = [];
				if($sEmailTo && trim($sEmailTo) != ""){
					$toAr = explode(",", $sEmailTo);
					for($i = 0; $i < sizeof($toAr); $i++){
		
						$email->AddAddress($toAr[$i]);
						$msg_temp[] = $toAr[$i];
					} 
				}
				
				//$email -> AddAddress($sEmailTo,$sEmailToNome);
				//$email -> AddBCC("daniela.oliveira@e-prepag.com.br",$sEmailCcNome);
				//$email -> AddCC($sEmailCc,$sEmailCcNome);
				//$email -> AddBCC("wagner@e-prepag.com.br");
				if (($DISTRIBUIDORA_EPP != intval($distributor_codigo))&&($DISTRIBUIDORA_EPP_LH != intval($distributor_codigo))) {
					$email -> AddAttachment($varArquivoRAR);
					$email -> Body    = $msg_temp[0]." e ".$msg_temp[1].",
					Segue anexo o arquivo com a relação de PINs Ativos.";
					$email -> Subject = "Arquivo de PINs Ativos";
				}
				else {
					$aux_lotes = implode(",", $vetorLotesUtilizados);
					if ($importacaoOk) {
						$email -> Body    = $sEmailToNome.",
					Foi carregado automaticamente com SUCESSO na tabela de estoque os PINs CASH de numeros de lotes na tabela moeda de: ".$aux_lotes.".";
					}
					else {
						$email -> Body    = $sEmailToNome.",
					A T E N C A O:
					NAO Foi carregado automaticamente com SUCESSO na tabela de estoque os PINs CASH de numeros de lotes na tabela moeda de: ".$aux_lotes.".";
					}
					$email -> Subject = "PINs CASH disponibilizados no Estoque";
				}
			//	echo "[".$email -> Body."]";
			//	$email -> AltBody = $body_plain;
				if ($email->Send()) {
					gravaLog_Depurador("Email enviado com sucesso!".PHP_EOL);
					
					if (($DISTRIBUIDORA_EPP != intval($distributor_codigo))&&($DISTRIBUIDORA_EPP_LH != intval($distributor_codigo))){
						$nomeArquivoProBD = substr($varArquivoRAR,(strrpos($varArquivoRAR,'/')+1),(strlen($varArquivoRAR)-strrpos($varArquivoRAR,'/')));
					}
					else {
						$nomeArquivoProBD = "Carga no Estoque ".date('Y-m-d H:i:s');
						$senha = "";
					}
					
					//teste se não houve problemas na inserção no estoque
					if($msg == "") {

						$sqlArquivo = "insert into pins_store_rel_arquivos (psra_dataentrada, psra_senha, psra_nome, psra_distributor_codigo) values ( CURRENT_TIMESTAMP, '".$senha."', '".$nomeArquivoProBD."',".$distributor_codigo.");";
						$rs_arquivo = SQLexecuteQuery($sqlArquivo);
						if(!$rs_arquivo) {
							$msg_pin .= "Erro ao salvar informa&ccedil;&otilde;es do arquivo no banco de dados. ($sqlArquivo)<br>";
							$msg .= "<font color='#FF0000'><b>Erro ao salvar informa&ccedil;&otilde;es do arquivo no banco de dados. ($sqlArquivo).".PHP_EOL."<br></b></font><br>";
						}
						else {
							$sqlArquivo = "select psra_codinterno from pins_store_rel_arquivos where psra_nome='".$nomeArquivoProBD."' and psra_senha='".$senha."';";
							$rs_arquivoRet = SQLexecuteQuery($sqlArquivo);
							if(!$rs_arquivoRet|| pg_num_rows($rs_arquivoRet) == 0) {
								$msg_pin .= "Erro localizar informa&ccedil;&otilde;es do arquivo no banco de dados. ($sqlArquivo)<br>";
								$msg .= "<font color='#FF0000'><b>Erro localizar informa&ccedil;&otilde;es do arquivo no banco de dados. ($sqlArquivo).".PHP_EOL."<br></b></font><br>";
							}
							else {
								$rs_arquivoRet_row = pg_fetch_array($rs_arquivoRet);
								$sql_update = "update pins_store set pin_arq_gerado=".$rs_arquivoRet_row['psra_codinterno']." where pin_status='".intval($PINS_STORE_STATUS_VALUES['A'])."' and distributor_codigo=".intval($distributor_codigo)." and pin_arq_gerado IS NULL and pin_lote_codigo IN (".$sCodLote.")";
								$rs_ps_arquivo = SQLexecuteQuery($sql_update);
								if(!$rs_ps_arquivo) {
									$msg_pin .= "Erro ao salvar informa&ccedil;&otilde;es do arquivo no banco de dados. ($sql_update)<br>";
									$msg .= "<font color='#FF0000'><b>Erro ao salvar informa&ccedil;&otilde;es do arquivo no banco de dados. ($sql_update).".PHP_EOL."<br></b></font><br>";
								}
								elseif (($DISTRIBUIDORA_EPP != intval($distributor_codigo))&&($DISTRIBUIDORA_EPP_LH != intval($distributor_codigo))){
									unlink($varArquivoRAR);
								}
							}
						}
					}//end if($msg == "")
					$msg_pin .="<font color='#0000CC'>E-mail enviado com sucesso!</font>";
				}
				else {
					gravaLog_Depurador("Email NAUN enviado com sucesso!".PHP_EOL);
					$msg_pin .="<font color='#0000CC'>Erro ao enviar o E-mail!</font>";
					$msg .= "<font color='#FF0000'><b>Erro ao enviar o E-mail!".PHP_EOL."<br></b></font><br>";
				}			
				
			} catch (Exception $e) {
				$msgErro = $mail->ErrorInfo;
				$arquivo = '/www/log/testePINstore.txt';
				$abre_arquivo = fopen($arquivo, 'w+');
				fwrite($abre_arquivo, $msgErro . "\n");
				fclose($abre_arquivo);
			}
		
		
			
		}

		//Linha abaixo força o ROLLBACK
		//$msg .= "<font color='#FF0000'><b>Teste de ROLLBACK!!!".PHP_EOL."<br></b></font><br>";
		
		//Finaliza transacao
		if($msg == ""){
			gravaLog_Depurador("Comitando SQLs!".PHP_EOL);
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.".PHP_EOL."<br></b></font><br>";
		} else {
			gravaLog_Depurador("ROLLBACK SQLs!".PHP_EOL);
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.".PHP_EOL."<br></b></font><br>";
			echo $msg;
		}

		// Unblock da carga no estoque 
		flag_publisher_unblock($distributor_codigo);

	}//end else do if(flag_publisher_test($distributor_codigo)) 


}//end if(!empty($BtnGerarArq) && $tf_v_tipo==3) 


function gravaLog_Depurador($mensagem){
	
		//Arquivo
		$file = $GLOBALS['raiz_do_projeto'] . "log/log_Depurador.txt";
	
		//Mensagem
		$mensagem =  str_repeat("-", 80).PHP_EOL.date('Y-m-d H:i:s'). " " .$GLOBALS['_SERVER']['SCRIPT_FILENAME'] . PHP_EOL . $mensagem . PHP_EOL;
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
	
}//end function gravaLog_Depurador
?>
