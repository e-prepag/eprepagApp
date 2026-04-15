<?php
require_once $raiz_do_projeto . "includes/inc_functions_card.php";

class Pins_Card {

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
			'4' => array( '0' => '0123456789',
						  '1' => '18',
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


	function gera_lote($opr_codigo, $distributor_codigo, $pin_valor, $qtde) {
		
                $opr_codigo 			= intval($opr_codigo);
		$distributor_codigo 		= intval($distributor_codigo);
		$pin_valor			= intval($pin_valor);
		$qtde				= intval($qtde);

		//Inicia transacao
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";

		// Adquire um advisory lock para garantir exclusividade
		$lock_key = hash('sha256', "pin_card_generation_{$opr_codigo}_{$distributor_codigo}");
		$sql_lock = "SELECT pg_advisory_lock(hashtext('$lock_key'))";
		SQLexecuteQuery($sql_lock);
		
		// Busca o formato do PIN na constante
                $sql = "select * from pins_card_distribuidoras where opr_codigo = ".$opr_codigo." and pcd_id_distribuidor = ".$distributor_codigo.";";
                $rs_distribuidoras = SQLexecuteQuery($sql);
                $rs_distribuidoras_row = pg_fetch_array($rs_distribuidoras);
                $sformato = $rs_distribuidoras_row['pcd_formato'];
		$this->set_config($sformato);
		
                // Cria LoteID
		$sql = "select max(pin_lote_codigo) as max_pin_lote_codigo from pins_card where opr_codigo = ".$opr_codigo." and distributor_codigo = ".$distributor_codigo;
		$rs_lote = SQLexecuteQuery($sql);
		if(!$rs_lote || pg_num_rows($rs_lote) == 0) {
			$ilote = 1;
		} else {
			$rs_lote_row = pg_fetch_array($rs_lote);
			$ilote = $rs_lote_row['max_pin_lote_codigo'] + 1;
		}
		// Obtem o ultimo serial
		$sql_serial = "select MAX(CAST(pin_serial AS BIGINT)) as max_serial from pins_card where opr_codigo = ".$opr_codigo." and distributor_codigo = ".$distributor_codigo.";";
                //echo $sql_serial."<br>";die();
		$rs_serial = SQLexecuteQuery($sql_serial);
		if($rs_serial) {
			$rs_serial_row = pg_fetch_array($rs_serial);
			$pin_serial = $rs_serial_row['max_serial']+1;
		} else {
			$pin_serial = 1;
		}
                //echo "[".$pin_serial."]<br>"; die();
		//instanciando a classe de cryptografia
		$chave256bits = new Chave();
		$this->aes = new AES($chave256bits->retornaChave());
		// gera $qtde pins
		$i=1;
		$iaux = 1;
		while ($i <= $qtde) {
			$spin_codigo = $this->gera_pin($distributor_codigo, $sformato);
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
			//echo "[".$spin_codigo."] Posições [".strlen($spin_codigo)."]<br>"; //die();
			if ($aux_spin_codigo == $spin_codigo) {
				$spin_serial = str_pad($pin_serial, $this->serial_length, "0", STR_PAD_LEFT);
                                // Testa existencia no banco de dados
				$sql = "select * from pins_card where pin_codigo = '".base64_encode($this->aes->encrypt($spin_codigo))."'";
                                //echo $sql."<br>";die();
				$rs_pins = SQLexecuteQuery($sql);
				if(pg_num_rows($rs_pins) == 0) {
                                        //transacao
                                        if($msg == ""){
                                                $sql = "insert into pins_card (pin_serial, pin_codigo, opr_codigo, distributor_codigo, pin_valor, pin_lote_codigo, pin_dataentrada, pin_formato) values (
												(
								            		SELECT LPAD(
                    									(COALESCE(MAX(CAST(pin_serial AS BIGINT)), 0) + 1)::text, 
                    									10, '0') 
								            		FROM pins_card 
								            		WHERE opr_codigo = ".$opr_codigo." and distributor_codigo = ".$distributor_codigo."
								        		)
												, '".base64_encode($this->aes->encrypt($spin_codigo))."', ".$opr_codigo.", ".$distributor_codigo.", ".$pin_valor.", ".$ilote.", CURRENT_TIMESTAMP, '".$sformato."');";
                                                //echo $sql."<br>"."Tamanho do PIN encriptado [".strlen(base64_encode($this->aes->encrypt($spin_codigo)))."]<br>";
                                                $rs_pins_save = SQLexecuteQuery($sql);
                                                if(!$rs_pins_save ) {
                                                        $msg = "Erro ao salvar o novo PIN ($sql)<br>";
                                                }
                                                else {
                                                        $i++;
                                                        $pin_serial ++;
				                }
                                        }//end if($msg == "")
                                	
				} else {
					echo "PIN já existe ('".$spin_codigo."')\n<br>";
				}
			} //end if ($aux_spin_codigo == $spin_codigo)
			else $msg2 = "<font color='#FF0000'><b>PIN gerado contém palavras restritas contidas no sanatize: [".$spin_codigo."]\n<br></b></font><br>";
			echo $msg2;
		}

		// Libera o advisory lock
		$sql_unlock = "SELECT pg_advisory_unlock(hashtext('$lock_key'))";
		SQLexecuteQuery($sql_unlock);

		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao comitar transação.\n<br></b></font><br>";
			echo " ===  Gerado o Lote ".$ilote."  com (".$qtde.") PINs =======<br>";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao dar rollback na transação.\n<br></b></font><br>";
		}
		echo $msg;
	}

	function gera_pin($distributor_codigo, $sformato) {
		$return				= $distributor_codigo;
		$sformato			= intval($sformato);
		// set up a counter
		$i = 2;
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


} //end class Pins_Card

?>