<?php
require_once $raiz_do_projeto . "includes/main.php";
require_once "../../../includes/load_dotenv.php";

set_time_limit(6000);

if(!isset($_SERVER['COMPUTERNAME']) ) $_SERVER['COMPUTERNAME'] = null;

/**
 ** Implementa o bloqueio por flags para carga no estoque
*/

function flag_publisher_distribuidor_block($opr_codigo, $pcd_id_distribuidor) {
	$sql = "UPDATE pins_card_distribuidoras SET pcd_flag_arquivo = 1 WHERE opr_codigo = ".$opr_codigo." AND pcd_id_distribuidor = ".$pcd_id_distribuidor." and pcd_flag_arquivo = 0;";
	//echo $sql."<br>";
        $ret2 = SQLexecuteQuery($sql);
	$cmdtuples = pg_affected_rows($ret2);
	if($cmdtuples===1) {
		return 0;
	} else {
		return 1;
	}
}//end function flag_publisher_distribuidor_block

function flag_publisher_distribuidor_unblock($opr_codigo, $pcd_id_distribuidor) {
	$sql = "UPDATE pins_card_distribuidoras SET pcd_flag_arquivo = 0 WHERE opr_codigo = ".$opr_codigo." AND pcd_id_distribuidor = ".$pcd_id_distribuidor.";";
	//echo $sql."<br>";
	$ret2 = SQLexecuteQuery($sql);
}//end function flag_publisher_distribuidor_unblock


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

function gravaLogDepuradorCard($mensagem){
	
		//Arquivo
		$file = $GLOBALS['raiz_do_projeto'] . "log/log_Depurado_Card.txt";
	
		//Mensagem
		$mensagem =  str_repeat("-", 80).PHP_EOL.date('Y-m-d H:i:s'). " " .$GLOBALS['_SERVER']['SCRIPT_FILENAME'] . PHP_EOL . $mensagem . PHP_EOL;
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
	
}//end function gravaLogDepuradorCard

if(!empty($BtnGerarArq) && $tf_v_tipo==3) {
	$sEmailTo	= "tamy@e-prepag.com.br";
	$sEmailToNome	= "Tamlyn Keiko Souza Takahata";
	$sEmailCc	= "";//tamy@e-prepag.com.br";
	$sEmailCcNome	= "";//Tamlyn Keiko Souza Takahata";

	//verificando se está habilitado a carga no estoque
	if(flag_publisher_distribuidor_block($opr_codigo, $distributor_codigo)) {
		echo "<font color='red'>Distribuidor já executando carga no Estoque ou em processo de geração de arquivo.</font><br>";
		gravaLogDepuradorCard("Estava BLOQUEADO!".PHP_EOL);
	} else {
		gravaLogDepuradorCard("Estava desbloqueado!".PHP_EOL);

		// Deleta arquivos >5horas
		$now = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
		foreach (glob("arquivos/*.rar") as $filename) {
			if(($now-filemtime($filename))>5*3600) {
				unlink($filename);
			}
		}
                
		// Armazena o range de lote
		$sCodLote = "";
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
		if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao iniciar transação.".PHP_EOL."</b></font><br>";
		
		$sql  = "SELECT pin_codinterno,distributor_codigo, pin_lote_codigo, to_char(pin_dataentrada,'DD/MM/YYYY') AS data, pin_formato, pin_valor, pin_codigo, pin_serial 
                         FROM pins_card pc 
                         where pin_status='".intval($PINS_STORE_STATUS_VALUES['A'])."' 
                                AND opr_codigo = ".$opr_codigo." 
                                AND distributor_codigo=".intval($distributor_codigo)." 
                                AND pin_arq_gerado IS NULL 
                                AND pin_lote_codigo IN (".$sCodLote.") 
                         ORDER BY pin_valor";
		//echo $sql."<br>"; 
                
		$rs_pins_email = SQLexecuteQuery($sql);
		if(!$rs_pins_email|| pg_num_rows($rs_pins_email) == 0) {
			$msg_pin .= "Todos os PINs Ativos da seleção já foram utilizados em arquivos anteriores.<br>";
			$msg .= "<font color='#FF0000'><b>Todos os PINs Ativos da seleção já foram utilizados em arquivos anteriores.".PHP_EOL."</b></font><br>";
		}
		else {
			//Instanciando Objetos para Descriptografia
			$chave256bits = new Chave();
			$pc = new AES($chave256bits->retornaChave());
			
			// Arquivo
                        $path = $GLOBALS['raiz_do_projeto'];
                        $url = "arquivos_gerados/pins_card/";
                        $file = str_replace("(","-",str_replace(")","-",str_replace(" ","",$operacao_array[$distributor_codigo])))."_".str_replace("(","-",str_replace(")","-",str_replace(" ","",$publisher_array[$opr_codigo]))).date("YmdHis").str_pad(rand(0,999), 3, "0", STR_PAD_LEFT).".txt";
                        $varArquivo = $path.$url.$file;
                        $sArq = "";
                        if($DISTRIBUIDORA_EPAY != intval($distributor_codigo)) {
                            while($rs_pins_email_row = pg_fetch_array($rs_pins_email)){ 
                                    $sArq .= $pc->decrypt(base64_decode($rs_pins_email_row['pin_codigo']))."\t".$rs_pins_email_row['pin_valor']."00".PHP_EOL;
                            }
                        }//end if($DISTRIBUIDORA_EPAY != intval($distributor_codigo))
                        else {
                            while($rs_pins_email_row = pg_fetch_array($rs_pins_email)){ 
                                    $sArq .= str_pad($rs_pins_email_row['pin_lote_codigo'],8,"0", STR_PAD_LEFT)." ".str_pad($rs_pins_email_row['pin_serial'],16,"0", STR_PAD_LEFT)." ".$pc->decrypt(base64_decode($rs_pins_email_row['pin_codigo']))." 090 ".str_pad($rs_pins_email_row['pin_valor'],9,"0", STR_PAD_LEFT).".00".PHP_EOL;
                            }
                        }//end else
                        $handle = fopen($varArquivo, "w+");
                        if (fwrite($handle, $sArq) === FALSE) {
                                $msg_pin .= "<font color='#0000CC'>N&atilde;o foi poss&iacute;vel gravar o Arquivo. </font><br>";
                                $msg .= "<font color='#0000CC'>N&atilde;o foi poss&iacute;vel gravar o Arquivo. </font><br>";
                        } else {
                                $msg_pin .= "<font color='#0000CC'>Arquivo gravado com sucesso.</font><br>";
                        }
                        fclose($handle);

                        if (file_exists($varArquivo)) {
                                gravaLogDepuradorCard("Arquivo txt gerado com sucesso!".PHP_EOL."$varArquivo".PHP_EOL);
                        }
                        else  {
                                gravaLogDepuradorCard("Arquivo txt NAUN gerado com sucesso!".PHP_EOL."$varArquivo".PHP_EOL);
                        }
                        //  RAR 
                        $varArquivoRAR = substr($varArquivo,0,strpos($varArquivo,'.')).".rar";

                        $senha = geraSenha();
                        $scmd = "rar a -p".$senha." -ep ".$varArquivoRAR." ".$varArquivo;
                        gravaLogDepuradorCard("Comando de compactação com senha!".PHP_EOL."$scmd".PHP_EOL);

                        //**********************************************  Parou de funcionar o comand exec em 29/1/2013
                        exec($scmd);

                        // Make a new instance of the COM object
                        //$WshShell = new COM("WScript.Shell");

                        // Make the command window but dont show it.
                        //$oExec = $WshShell->Exec("$scmd");

                        //Testando se executou o 
                        //if($oExec->Status == 0) gravaLogDepuradorCard(" Executou o Windows Script com sucesso!!".PHP_EOL);
                        //else gravaLogDepuradorCard(" Não executou o Windows Script com sucesso!!".PHP_EOL);

                        sleep(2);

                        if (file_exists($varArquivoRAR)) {
                                gravaLogDepuradorCard("Arquivo RAR gerado com sucesso!".PHP_EOL."$varArquivoRAR".PHP_EOL);
                        }
                        else {
                                gravaLogDepuradorCard("Arquivo RAR NAUN gerado com sucesso!".PHP_EOL."$varArquivoRAR".PHP_EOL);
                        }
                        
                        //excluir o arquivo que foi compacatado
                        unlink($varArquivo);
                        if (file_exists($varArquivo)) {
                                gravaLogDepuradorCard("Arquivo txt NAUN excluido com sucesso!".PHP_EOL."$varArquivo".PHP_EOL);
                        }
                        else  {
                                gravaLogDepuradorCard("Arquivo txt excluido com sucesso!".PHP_EOL."$varArquivo".PHP_EOL);
                        }
                        
                        $email = new PHPMailer;

//			$email->Host     = "smtp.e-prepag.com.br";	//"localhost";
            //-----Alteração exigida pela BaseNet(11/2017)-------------//
            $email->Host     = getenv("smtp_host");
            //---------------------------------------------------------//
			$email->Mailer   = "smtp";
			$email->From     = getenv("email_suporte");
			$email->SMTPAuth = true;     // turn on SMTP authentication
			$email->Username = getenv("smtp_username");  // a valid email here
			$email->Password = getenv("smtp_password"); //'985856';	//'850637'; 
			$email->FromName = "E-Prepag";	// " (EPP)"
            
            //-----Alteração exigida pela BaseNet(11/2017)-------------//
            $email->IsSMTP();
            //$email->SMTPSecure = "ssl";
            $email->Port     = getenv("smtp_port");
            //---------------------------------------------------------//
                        
                        // Overwrite smt details for dev version cause e-prepag.com.br server reject it
                        // You can just add your IP or use elseif with your details
                        // When run bat files there is not ip address so we need use COMPUTERNAME to check
 
                        if(checkIP() || (class_exists('EmailEnvironment')  && EmailEnvironment::serverId() == 1)) {
                            //  $email->SMTPDebug  = 1; descomentar para debugar 
                            $email->Port     = getenv("smtp_port");
                            $email->Host     = "e-prepag.com";
                            $email->Username = 'send@e-prepag.com';
                            $email->Password = 'sendeprepag2013';
                        }

			// Reply-to
			$email->AddReplyTo(getenv("email_suporte"));


			$email -> AddAddress($sEmailTo,$sEmailToNome);
			$email -> AddBCC("wagner@e-prepag.com.br");
                        
                        $email -> AddAttachment($varArquivoRAR);
                        $email -> Body    = $sEmailToNome.",
                        Segue anexo o arquivo com a relação de PINs Ativos.
                        O anexo contém PINs Card de numeros de lotes: ".$sCodLote."
                        Atenciosamente,
                        Nós";
                        $email -> Subject = "Arquivo de PINs Cards Ativos";
			
                        if ($email -> Send()) {
				gravaLogDepuradorCard("Email enviado com sucesso!".PHP_EOL);
				
				$nomeArquivoProBD = substr($varArquivoRAR,(strrpos($varArquivoRAR,'/')+1),(strlen($varArquivoRAR)-strrpos($varArquivoRAR,'/')));
				
				//teste se não houve problemas na inserção no estoque
				if($msg == "") {

					$sqlArquivo = "insert into pins_card_rel_arquivos (opr_codigo, pcra_dataentrada, pcra_senha, pcra_nome, pcra_distributor_codigo) 
                                                       values (".$opr_codigo.", CURRENT_TIMESTAMP, '".$senha."', '".$nomeArquivoProBD."',".$distributor_codigo.");";
					//echo $sqlArquivo."<br>";
                                        $rs_arquivo = SQLexecuteQuery($sqlArquivo);
					if(!$rs_arquivo) {
						$msg_pin .= "Erro ao salvar informa&ccedil;&otilde;es do arquivo no banco de dados. ($sqlArquivo)<br>";
						$msg .= "<font color='#FF0000'><b>Erro ao salvar informa&ccedil;&otilde;es do arquivo no banco de dados. ($sqlArquivo).".PHP_EOL."<br></b></font><br>";
					}
					else {
						$sqlArquivo = "select pcra_codinterno from pins_card_rel_arquivos where pcra_nome='".$nomeArquivoProBD."' and pcra_senha='".$senha."';";
						//echo $sqlArquivo."<br>";
                                                $rs_arquivoRet = SQLexecuteQuery($sqlArquivo);
						if(!$rs_arquivoRet|| pg_num_rows($rs_arquivoRet) == 0) {
							$msg_pin .= "Erro localizar informações do arquivo no banco de dados. ($sqlArquivo)<br>";
							$msg .= "<font color='#FF0000'><b>Erro localizar informações do arquivo no banco de dados. ($sqlArquivo).".PHP_EOL."<br></b></font><br>";
						}
						else {
							$rs_arquivoRet_row = pg_fetch_array($rs_arquivoRet);
							$sql_update = "UPDATE pins_card 
                                                                        SET pin_arq_gerado=".$rs_arquivoRet_row['pcra_codinterno']." 
                                                                        WHERE pin_status='".intval($PINS_STORE_STATUS_VALUES['A'])."' 
                                                                            AND opr_codigo = ".$opr_codigo." 
                                                                            AND distributor_codigo=".intval($distributor_codigo)." 
                                                                            AND pin_arq_gerado IS NULL 
                                                                            AND pin_lote_codigo IN (".$sCodLote.")";
							//echo $sql_update."<br>";
                                                        $rs_pc_arquivo = SQLexecuteQuery($sql_update);
							if(!$rs_pc_arquivo) {
								$msg_pin .= "Erro ao salvar informações do arquivo no banco de dados. ($sql_update)<br>";
								$msg .= "<font color='#FF0000'><b>Erro ao salvar informações do arquivo no banco de dados. ($sql_update).".PHP_EOL."<br></b></font><br>";
							}
							else {
								unlink($varArquivoRAR);
							}
						}
					}
				}//end if($msg == "")
				$msg_pin .="<font color='#0000CC'>E-mail enviado com sucesso!</font>";
			}
			else {
				gravaLogDepuradorCard("Email NAUN enviado com sucesso!".PHP_EOL);
				$msg_pin .="<font color='#0000CC'>Erro ao enviar o E-mail!</font>";
				$msg .= "<font color='#FF0000'><b>Erro ao enviar o E-mail!".PHP_EOL."<br></b></font><br>";
			}
		}
                
		//Linha abaixo força o ROLLBACK
		//$msg .= "<font color='#FF0000'><b>Teste de ROLLBACK!!!".PHP_EOL."<br></b></font><br>";
		
		//Finaliza transacao
		if($msg == ""){
			gravaLogDepuradorCard("Comitando SQLs!".PHP_EOL);
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.".PHP_EOL."<br></b></font><br>";
		} else {
			gravaLogDepuradorCard("ROLLBACK SQLs!".PHP_EOL);
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.".PHP_EOL."<br></b></font><br>";
			echo $msg;
		}

		// Unblock da carga no estoque 
		flag_publisher_distribuidor_unblock($opr_codigo, $distributor_codigo);

	}//end else do if(flag_publisher_distribuidor_block($distributor_codigo)) 


}//end if(!empty($BtnGerarArq) && $tf_v_tipo==3) 

?>
