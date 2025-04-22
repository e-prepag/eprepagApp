<?php
require_once $raiz_do_projeto . 'includes/inc_functions.php';
require_once $raiz_do_projeto . 'includes/gamer/chave.php';
require_once $raiz_do_projeto . 'includes/gamer/AES.class.php';

class Pins_Publishers {

	var $nchars;
    var $separador;
	var $serial_length = 10;
	var $sformato;
	// variavel que recebera o objeto de cryptografia Cash
	var $aes;
	// variavel que recebera o objeto de cryptografia Publisher
	var $aesPub;
		
	
    //O subvetor para o formato tem a seguinte caracteristicas para as posições
	// 0 = Range de Caracteres
	// 1 = Tamanho do PIN
	// 2 = Espamento do separador - Apenas LayOut de saída
	var $banks = array(
			'0' => array( '0' => '23456789',
						  '1' => '8',
						  '2' => '4'),
			'1' => array( '0' => '23456789abcdefghjkmnpqrstvwxyz',
						  '1' => '16',
						  '2' => '4'),
			'2' => array( '0' => '23456789ABCDEFGHIJKLMNPQRSTUVWXYZ',
						  '1' => '16',
						  '2' => '4'),
			'3' => array( '0' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789',
						  '1' => '16',
						  '2' => '4'),
			'4' => array( '0' => '0123456789',
						  '1' => '16',
						  '2' => '4'),
			'5' => array( '0' => '0123456789',
						  '1' => '14',
						  '2' => '4'),
			'6' => array( '0' => '0123456789',
						  '1' => '20',
						  '2' => '4'),
		);

	/************* Status dos PINs E-PREPAG -- LEGENDA ************************
	  tabela "pins_status", ver exemplo
		   select pin_status, stat_descricao, count(*) as n 
		   from pins inner join pins_status on pin_status=stat_codigo 
		   group by pin_status, stat_descricao 
		   order by pin_status, stat_descricao

- posição hoje:                                 
pin_status          stat_descricao                                 n
"0"                "Aguardando Liberação"                          2
"1"                "Disponivel"                                81541
"3"                "Vendido"                                  499597
"6"                "Vendido – Lan House"                      301405
"9"                "Desativado"                                 4630

	OBS: Estas informações estão cadastradas no arquivo:
	"/www/web/prepag2/commerce/includes/constantes.php"
	OBS2: A chave de cryptografia esta no arquivo:
	"/www/web/prepag2/commerce/includes/chave.php"
	*****************************************************************************/
	
	function set_Tamanho($nchars) {
 		$this->nchars = $nchars;
	}

    function set_Separador($separador) {
 		$this->separador = $separador;
	}


	function gera_lote($opr_codigo, $pin_valor, $qtde, $tf_v_formato=null, $is_pdv = null) {
		global $DISTRIBUIDORAS;
		$opr_codigo			= intval($opr_codigo);
		
		if(is_null($is_pdv)) {
			switch($pin_valor){
				case 4:
					$pin_valor = 4.49;
					break;
				case 14:
					$pin_valor = 13.99;
					break;
				case 21:
					$pin_valor = 20.99;
					break;
				case 45:
					$pin_valor = 44.99;
					break;
				case 88:
					$pin_valor = 87.99;
					break;
				case 210:
					$pin_valor = 209.99;
					break;
			}
		}
		//echo ($pin_valor);
		
		$pin_valor			= floatval($pin_valor);
		$qtde				= intval($qtde);

		//Inicia transacao
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";
		
		// Adquire um advisory lock para garantir exclusividade
		$lock_key = hash('sha256', "pin_publisher_generation_{$opr_codigo}");
		$sql_lock = "SELECT pg_advisory_lock(hashtext('$lock_key'))";
		SQLexecuteQuery($sql_lock);

		// Busca o formato do PIN na constante
		$sql = "select opr_pin_epp_formato from operadoras where opr_codigo=".$opr_codigo;
		//echo $sql."<br>";
		$rs_oper = SQLexecuteQuery($sql);
		$rs_oper_row = pg_fetch_array($rs_oper);
		$sformato = $rs_oper_row['opr_pin_epp_formato'];
		$this->set_config($sformato);
   		// Cria LoteID
		$sql = "select max(pin_lote_codigo) as max_pin_lote_codigo from pins where opr_codigo = ".$opr_codigo;
		//echo $sql."<br>";
		$rs_lote = SQLexecuteQuery($sql);
		if(!$rs_lote || pg_num_rows($rs_lote) == 0) {
			$ilote = 1;
		} else {
			$rs_lote_row = pg_fetch_array($rs_lote);
			$ilote = $rs_lote_row['max_pin_lote_codigo'] + 1;
		}
		// Obtem o ultimo serial
		
		//$sql_serial = "select MAX(lpad(pin_serial,40,'0')) as max_serial from pins where opr_codigo = ".$opr_codigo;
		$sql_serial = "select CAST(pin_serial AS BIGINT) as max_serial from pins where opr_codigo = ".$opr_codigo." order by CAST(pin_serial AS BIGINT) desc limit 1;";
		//echo $sql_serial."<br>";
		$rs_serial = SQLexecuteQuery($sql_serial);
		if($rs_serial) {
			if (pg_num_rows($rs_serial) > 0) {
				$rs_serial_row = pg_fetch_array($rs_serial);
				/*
				echo $rs_serial_row['max_serial']."<br>".$pin_serial.":teste<br>";
				if (ereg("^[[:digit:]]{1,40}$",$rs_serial_row['max_serial'])) {
					$pin_serial = floatval($rs_serial_row['max_serial']);
					echo number_format($pin_serial, 0, '', '').'<br>SOMENTE NUMEROS<br>';
				}
				else {
					$numero_digitos = 0;
					$pin_serial = "";
					$pin_tmp = str_replace('0','',$rs_serial_row['max_serial']);
					while (strlen($pin_tmp) > $numero_digitos) {
						$pin_serial .="9";
						$numero_digitos++;
					}
					$pin_serial =  floatval($pin_serial);
					echo number_format($pin_serial, 0, '', '').'<br>NÃO POSSUI SOMENTE NUMEROS<br>';
				}*/
				$pin_serial = $rs_serial_row['max_serial'];
			}
			else {
				$pin_serial = 1;
			}
		} else {
			die("No Estoque para esta Operadora possui PIN_SERIAL ALPHA.");
			//$pin_serial = 1;
		}

	
		//instanciando a classe de cryptografia
		$chave256bits = new Chave();
		//chave Publisher
		$this->aesPub = new AES($chave256bits->retornaChavePub());
		//chave Cash
		$this->aes	= new AES($chave256bits->retornaChave());
		
		// gera $qtde pins
		$i=1;
		while ($i <= $qtde) {
			$spin_codigo = $this->gera_pin($sformato, $pin_valor);
			$spin_codigo = str_replace("-","",$spin_codigo);
			$msg = "";
			$aux_spin_codigo = $spin_codigo;
			$params		= array('spin_codigo'	=> array ('0' => $spin_codigo,
														  '1' => 'S',
														  '2' => '1'
													)
								);
			$params		= sanitize_input_data_array($params,$err_cod);
			extract($params, EXTR_OVERWRITE);
			if ($aux_spin_codigo == $spin_codigo) {
				$pin_serial ++;
				$spin_serial = str_pad(number_format($pin_serial, 0, '', ''), $this->serial_length, "0", STR_PAD_LEFT);
        		// Testa existencia no banco de dados
				$sql = "select * from pins where pin_codigo = '".$spin_codigo."' and opr_codigo =".$opr_codigo;
				//echo $sql."<br>";
				//$sql = "select * from pins where pin_codigo = '".base64_encode($this->aesPub->encrypt($spin_codigo))."'";
				$rs_pins = SQLexecuteQuery($sql);
				if(!$rs_pins || pg_num_rows($rs_pins) == 0) {
					// Testa existencia no banco de dados
					$sql = "select * from pins_store where pin_codigo = '".base64_encode($this->aes->encrypt($spin_codigo))."'";
					//echo $sql."<br>";
					$rs_pins_store = SQLexecuteQuery($sql);
					if(!$rs_pins_store || pg_num_rows($rs_pins_store) == 0) {
						//transacao
						if($msg == ""){
							
							// $sql = "insert into pins (pin_serial, pin_codigo, opr_codigo, pin_valor, pin_lote_codigo, pin_dataentrada, pin_canal, pin_horaentrada,pin_status,pin_validade) values (
							// 			(
							// 	            SELECT LPAD(
                    		// 					(COALESCE(MAX(CAST(pin_serial AS BIGINT)), 0) + 1)::text, 
                    		// 						10, '0') 
							// 	            FROM pins 
							// 	            WHERE opr_codigo = ".$opr_codigo."
							// 	        ),
							// '".$spin_codigo."', ".$opr_codigo.", ".$pin_valor.", ".$ilote.", CURRENT_TIMESTAMP, 's', NOW(),'1',(NOW() + interval '6 month'));";
							$sql = "insert into pins (pin_serial, pin_codigo, opr_codigo, pin_valor, pin_lote_codigo, pin_dataentrada, pin_canal, pin_horaentrada,pin_status,pin_validade) values ('".$spin_serial."', '".$spin_codigo."', ".$opr_codigo.", ".$pin_valor.", ".$ilote.", CURRENT_TIMESTAMP, 's', NOW(),'1',(NOW() + interval '6 month'));";
							//die("SQL : ".$sql);
							$rs_pins_save = SQLexecuteQuery($sql);
							if(!$rs_pins_save) {
								$msg = "Erro ao salvar o novo PIN ($sql)<br>";
								// Libera o advisory lock
								$sql_unlock = "SELECT pg_advisory_unlock(hashtext('$lock_key'))";
								SQLexecuteQuery($sql_unlock);
								die($msg);
							}
							else {
								$i++;
							}
						}
					} else {
						echo "PIN ja&acute; existe na Tabela de PIN CASH ('".$spin_codigo."')\n<br>";
					}
				} else {
					echo "PIN ja&acute; existe ('".$spin_codigo."')\n<br>";
				}
			} //end if teste sanatize
			else $msg2 = "<font color='#FF0000'><b>PIN gerado cont&eacute;m palavras restritas contidas no sanatize: [".$spin_codigo."]\n<br></b></font><br>";
			echo $msg2;
		}

		// Libera o advisory lock
		$sql_unlock = "SELECT pg_advisory_unlock(hashtext('$lock_key'))";
		SQLexecuteQuery($sql_unlock);

		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.\n<br></b></font><br>";
			echo " ===  Gerado o Lote ".$ilote."  com (".$qtde.") PINs =======<br>";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
		}
		echo $msg; 
	}

	function gera_pin($sformato, $pin_valor) {
		$return				= "";
		$sformato			= intval($sformato);
		$pin_valor			= floatval($pin_valor);
		// set up a counter
		$i = 0;
				
		if($_SERVER["REMOTE_ADDR"] == "201.93.162.169") {
			echo "nchars: ". $this->nchars;
			echo "bank: ". $this->bank;
			echo "separator: ". $this->separador;
			echo "serial" . $this->serial_length;
		}
		// add random characters to $return until $nchars is reached
		while ($i < $this->nchars) {
			// pick a random character from the possible ones
                        $char = substr($this->bank, mt_rand(0, strlen($this->bank) - 1), 1);
                        // we don't want this character if it's already in the password
                        // but repeat character  if length > range 
                        if (!strstr($return, $char)||strlen($this->bank)<$this->nchars) {
							// testa formatos
							if(($i%$this->separador==0) && ($i>0)) {
									$return .= "-";
							}
							$return .= $char;
							$i++;
						}
		}
		return $return;
	}

	function getUniqueCode($length = "") {	
		$code = md5(uniqid(rand(), true));
		if ($length != "") return substr($code, 0, $length);
		else return $code;
	}

	// Return a random value
	function tep_rand($min = null, $max = null) {
		static $seeded;
		if (!$seeded) {
			mt_srand((double)microtime()*1000000);
			$seeded = true;
		}
		if (isset($min) && isset($max)) {
			if ($min >= $max) {
				return $min;
			} else {
				return mt_rand($min, $max);
			}
		} else {
			return mt_rand();
		}
	}

	// Set vars
	function set_config($sformato) {
		$this->sformato = $sformato;
		
		$this->bank = $this->banks[$sformato]['0'];
		if (strlen($this->bank)<1)
			$this->bank = $this->banks['1']['0'];
		
		$this->set_Tamanho($this->banks[$sformato]['1']);
		if (strlen($this->nchars)<1)
			$this->set_Tamanho($this->banks['1']['1']);
		
		$this->set_Separador($this->banks[$sformato]['2']);
		if (strlen($this->separador)<1)
			$this->set_Separador($this->banks['1']['2']);
	}

	// Validate a password
	function tep_validate($sformato, $pin_codigo) {
		if (strlen($_POST['newPass1']) < 4) {
			$page_error = 'New pin is too Short - 4 Character Minimum';
			$error = true;	
		}
		//character check
		if (!isset($page_error) && preg_match("/[[:punct:]]/", $_POST['newPass1'])) {
			$page_error = "pin Contains Invalid Characters - No Punctuation";
			$error = true;	
		}
		//check for equality
		if (!isset($page_error)) {
			if (strcmp($_POST['newPass1'], $_POST['newPass2'])) {
				$page_error = 'New pins Do Not Match';
				$error = true;	
			}	
		}
		if (!$error) {
			$user = new User($_POST['user']);
			$user->passwd($_POST['newPass1']);
		}
	}

}

?>