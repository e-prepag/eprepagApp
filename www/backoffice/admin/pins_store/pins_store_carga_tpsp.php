<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

// Carga para PINS Cash disponibilizados para compra de Gamers 
echo "Gamers<br>";
	$sql  = "select * from pins where opr_codigo= 49 and pin_codinterno <= 1712356"; // lan =53 e distributor_codigo=3
	//echo $sql;
	//die();
	$rs_pins_email = SQLexecuteQuery($sql);
	if(!$rs_pins_email|| pg_num_rows($rs_pins_email) == 0) {
		$msg_pin .= "Não existe nenhum PIN no estoque para a operadora 49.<br>";
	}
	else {
		//Instanciando Objetos para Descriptografia
		$chave256bits = new Chave();
		$ps = new AES($chave256bits->retornaChave());
		//Variavel contendo menssagem de erro
		$msg = "";
			
		//Inicia transacao
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";
		
		$iaux = 1;
		while($rs_pins_email_row = pg_fetch_array($rs_pins_email)){ 
			//transacao
			if($msg == ""){																// $ps->encrypt(base64_decode($rs_pins_email_row['pin_codigo']))
					$sql = "select pin_codinterno from pins_store where pin_codigo = '".base64_encode($ps->encrypt(addslashes($rs_pins_email_row['pin_codigo'])))."' and distributor_codigo = 2;";
					$rs_pins_estoque = SQLexecuteQuery($sql);
					if(!$rs_pins_estoque) {
						$msg .= "Erro ao selecionar o novo PIN no estoque ($sql)<br>";
					}
					else{
						$rs_pins_estoque_row = pg_fetch_array($rs_pins_estoque);
						$sql = "insert into tb_pins_store_pins (pins_pin_codinterno, pins_store_pin_codinterno) values (".$rs_pins_email_row['pin_codinterno'].",".$rs_pins_estoque_row['pin_codinterno'].");";
						echo $iaux." - INSERT SQL : ".$sql."<br>";
						$iaux++;
						/*
						$rs_pins_save_tracer = SQLexecuteQuery($sql);
						if(!$rs_pins_save_tracer) {
							$msg .= "Erro ao salvar a rastreabilidade PIN no estoque e na pins_store($sql)<br>";
						}//end if(!$rs_pins_save)
						*/
					}//end else if(!$rs_pins_estoque)
			}//end if($msg == "")
		}//end while($rs_pins_email_row = pg_fetch_array($rs_pins_email))
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.\n<br></b></font><br>";
			else $importacaoOk = true;
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
		}
		echo $msg;
	}

// Carga para PINS Cash disponibilizados para compra de LAN House 
echo "<br>==========================================================================================<br>LANs<br>";

	$sql  = "select * from pins where opr_codigo= 53 and pin_codinterno <= 1712356"; // gamer=49 e distributor_codigo=2
	//echo $sql;
	//die();
	$rs_pins_email = SQLexecuteQuery($sql);
	if(!$rs_pins_email|| pg_num_rows($rs_pins_email) == 0) {
		$msg_pin .= "Não existe nenhum PIN no estoque para a operadora 49.<br>";
	}
	else {
		//Instanciando Objetos para Descriptografia
		$chave256bits = new Chave();
		$ps = new AES($chave256bits->retornaChave());
		//Variavel contendo menssagem de erro
		$msg = "";
			
		//Inicia transacao
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";
		
		$iaux = 1;
		while($rs_pins_email_row = pg_fetch_array($rs_pins_email)){ 
			//transacao
			if($msg == ""){																// $ps->encrypt(base64_decode($rs_pins_email_row['pin_codigo']))
					$sql = "select pin_codinterno from pins_store where pin_codigo = '".base64_encode($ps->encrypt(addslashes($rs_pins_email_row['pin_codigo'])))."' and distributor_codigo = 3;";
					$rs_pins_estoque = SQLexecuteQuery($sql);
					if(!$rs_pins_estoque) {
						$msg .= "Erro ao selecionar o novo PIN no estoque ($sql)<br>";
					}
					else{
						$rs_pins_estoque_row = pg_fetch_array($rs_pins_estoque);
						$sql = "insert into tb_pins_store_pins (pins_pin_codinterno, pins_store_pin_codinterno) values (".$rs_pins_email_row['pin_codinterno'].",".$rs_pins_estoque_row['pin_codinterno'].");";
						echo $iaux." - INSERT SQL : ".$sql."<br>";
						$iaux++;
						/*
						$rs_pins_save_tracer = SQLexecuteQuery($sql);
						if(!$rs_pins_save_tracer) {
							$msg .= "Erro ao salvar a rastreabilidade PIN no estoque e na pins_store($sql)<br>";
						}//end if(!$rs_pins_save)
						*/
					}//end else if(!$rs_pins_estoque)
			}//end if($msg == "")
		}//end while($rs_pins_email_row = pg_fetch_array($rs_pins_email))
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.\n<br></b></font><br>";
			else $importacaoOk = true;
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
		}
		echo $msg;
	}

?>
