<?php
require_once $raiz_do_projeto . 'includes/inc_functions.php';
require_once $raiz_do_projeto . 'includes/gamer/chave.php';
require_once $raiz_do_projeto . 'includes/gamer/AES.class.php';

class Pins_Store {

	var $nchars;
    var $separador;
	var $serial_length = 10;
	var $sformato;
	// variavel que recebera o objeto de cryptografia
	var $aes;
		
	
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
		);

	/************* Status dos PINs E-PREPAG -- LEGENDA ************************
					'1'	=> 'Disponiveis' => (DEFAULT)
					'2'	=> 'Publicado'
					'3'	=> 'Ativado'
					'4'	=> 'Utilizado'
					'5'	=> 'Bloqueado'
					'-1'=> 'Cancelado'
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

 
	function gera_lote($distributor_codigo, $pin_valor, $qtde, $tf_v_formato=null) {
		global $DISTRIBUIDORAS;
		$distributor_codigo = intval($distributor_codigo);
		$pin_valor			= intval($pin_valor);
		$qtde				= intval($qtde);

		//Inicia transacao
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";
		
		// Busca o formato do PIN na constante
		$sformato = $DISTRIBUIDORAS[$distributor_codigo]['distributor_format'];
		$this->set_config($sformato);

		// Adquire um advisory lock para garantir exclusividade
		$lock_key = hash('sha256', "pin_store_generation_{$distributor_codigo}");
		$sql_lock = "SELECT pg_advisory_lock(hashtext('$lock_key'))";
		SQLexecuteQuery($sql_lock);

        // Cria LoteID
		$sql = "select max(pin_lote_codigo) as max_pin_lote_codigo from pins_store where distributor_codigo = ".$distributor_codigo;
		$rs_lote = SQLexecuteQuery($sql);
		if(!$rs_lote || pg_num_rows($rs_lote) == 0) {
			$ilote = 1;
		} else {
			$rs_lote_row = pg_fetch_array($rs_lote);
			$ilote = $rs_lote_row['max_pin_lote_codigo'] + 1;
		}
		// Obtem o ultimo serial
		$sql_serial = "select CAST(pin_serial AS BIGINT) as max_serial from pins_store where distributor_codigo = ".$distributor_codigo." order by CAST(pin_serial AS BIGINT) desc limit 1;";
		$rs_serial = SQLexecuteQuery($sql_serial);
		if($rs_serial && pg_num_rows($rs_serial) > 0) {
			$rs_serial_row = pg_fetch_array($rs_serial);
			$pin_serial = $rs_serial_row['max_serial']+1;
		} else {
			$pin_serial = 1;
		}
		//instanciando a classe de cryptografia
		$chave256bits = new Chave();
		$this->aes = new AES($chave256bits->retornaChave());
		// gera $qtde pins
		$i=1;
		$iaux = 1;
		while ($i <= $qtde) {
			$spin_codigo = $this->gera_pin($sformato, $pin_valor);
			$spin_codigo = str_replace("-","",$spin_codigo);
			$msg = "";
			//if ($iaux==1) {
			//	$spin_codigo="1000101090042569";
			//	$iaux++;
			//}
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
				$spin_serial = str_pad($pin_serial, $this->serial_length, "0", STR_PAD_LEFT);
        		// Testa existencia no banco de dados
				$sql = "select * from pins_store where pin_codigo = '".base64_encode($this->aes->encrypt($spin_codigo))."'";
				$rs_pins = SQLexecuteQuery($sql);
				if(!$rs_pins || pg_num_rows($rs_pins) == 0) {
					//Teste existencia no estoque do publisher
					$sql = "select * from pins where pin_codigo = '".$spin_codigo."'";
					$rs_pins_pub = SQLexecuteQuery($sql);
					if(!$rs_pins_pub || pg_num_rows($rs_pins_pub) == 0) {
						//Teste existencia na tabela de exceção de gocash com tamanho de 16
						$sql = "select * from pins_gocash_lote16 where pgcl_pin_number_encrypt = '".base64_encode($this->aes->encrypt($spin_codigo))."'";
						$rs_pins_gocash = SQLexecuteQuery($sql);
						if(!$rs_pins_gocash || pg_num_rows($rs_pins_gocash) == 0) {
							//transacao
							if($msg == ""){
								$sql = "
								    INSERT INTO pins_store 
								    (
								        pin_serial, 
								        pin_codigo, 
								        pin_caracter, 
								        distributor_codigo, 
								        pin_valor, 
								        pin_lote_codigo, 
								        pin_dataentrada, 
								        pin_canal, 
								        pin_formato
								    ) 
								    VALUES 
								    (
								        '".$spin_serial."', 
								        '".base64_encode($this->aes->encrypt($spin_codigo))."', 
								        '', 
								        ".$distributor_codigo.", 
								        ".$pin_valor.", 
								        ".$ilote.", 
								        CURRENT_TIMESTAMP, 
								        'w', 
								        '".$sformato."'
								    );
								";
								$rs_pins_save = SQLexecuteQuery($sql);
								if(!$rs_pins_save ) {
									$msg = "Erro ao salvar o novo PIN ($sql)<br>";
								}
								else {
									$i++;
								}
							}//end if($msg == "")
						}else {
							echo "PIN ja&acute; existe no Tabela de GoCASH (contendo exceções) com tamanho 16 ('".$spin_codigo."')\n<br>";
						}
					}else {
						echo "PIN ja&acute; existe no Estoque do Publisher ('".$spin_codigo."')\n<br>";
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
		$pin_valor			= intval($pin_valor);
		// set up a counter
		$i = 0;
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