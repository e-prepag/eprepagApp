<?php

// This class was made to generate variable pins for Eprepag Cash
// Livrodjx did it right


class GeraPinVariavel {
	private $valor_pin;
	private $opr_codigo;
	private $distributor;
	private $quantidade_pins;
	private $nchars;
	private $separador;
	private $connection;
	
	
	public function __construct(
		$valor_pin,
		$opr_codigo,
		$distributor,
		$qtde 
	) {
		$this->nchars = 16;
		$this->separador = 4;
		$this->connection = ConnectionPDO::getConnection()->getLink();
		$this->valor_pin = $valor_pin;
		$this->opr_codigo = $opr_codigo;
		$this->distributor = $distributor;
		$this->quantidade_pins = $qtde;
	}
	
	
	function saveLog($pins, $lote, $operadora, $valor) {
		try {
			$file = fopen("/www/log/classPinEppCash.txt", "a+");
			fwrite($file, str_repeat("*", 50)."\n");
			fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
			fwrite($file, "OPERADORA: ".$operadora. "\n");
			fwrite($file, "VALOR DO PIN: ".$valor."\n");
			fwrite($file, "PIN(S): ".$pins."\n");
			fwrite($file, "LOTE: ".$lote."\n");
			fwrite($file, str_repeat("*", 50)."\n");
			fclose($file);
		}catch (Exception $e) {
			echo "Error(6) writing monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage().PHP_EOL;
		}
	}

	function gera_pin($sformato, $pin_valor) {
		$return				= "";
		$sformato			= intval($sformato);
		$pin_valor			= floatval($pin_valor);
		// set up a counter
		$i = 0;
		// add random characters to $return until $nchars is reached
		while ($i < $this->nchars) {
			// pick a random character from the possible ones
			$char = substr('0123456789', mt_rand(0, strlen('0123456789') - 1), 1);
			// we don't want this character if it's already in the password
			// but repeat character  if length > range 
			if (!strstr($return, $char)||strlen('0123456789') < $this->nchars) {
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
	
	function gerar() {
		$sql = "select max(pin_lote_codigo) as max_pin_lote_codigo from pins where opr_codigo = ".$this->opr_codigo.";";
		$query = $this->connection->prepare($sql);
		$query->execute();
		
		$rs_lote = $query->fetch(PDO::FETCH_ASSOC);
		
		if(!$rs_lote || count($rs_lote) == 0) {
			$rs_lote = 1;
		}
		
		
		$sql_serial = "select CAST(pin_serial AS BIGINT) as max_serial from pins where opr_codigo = ".$this->opr_codigo." order by CAST(pin_serial AS BIGINT) desc limit 1;";
		
		$query = $this->connection->prepare($sql_serial);
		$query->execute();
		
		$rs_serial = $query->fetch(PDO::FETCH_ASSOC);
		
		if(!$rs_serial || count($rs_serial) == 0) {
			$rs_serial = 1;
		}
		
		//instanciando a classe de cryptografia
		$chave256bits = new Chave();
		//chave Publisher
		$aesPub = new AES($chave256bits->retornaChavePub());
		//chave Cash
		$aes = new AES($chave256bits->retornaChave());
		
		
		$i = 1;
		$iaux = 1;
		$pins_final = "";
		
		$spin_codigo = $this->gera_pin(4, $this->valor_pin);
		$spin_codigo = str_replace("-","",$spin_codigo);
		$msg = "";
		$aux_spin_codigo = $spin_codigo;
		$params		= array('spin_codigo' => array ('0' => $spin_codigo,
			'1' => 'S',
			'2' => '1'
		));
		
		$params		= sanitize_input_data_array($params,$err_cod);
		extract($params, EXTR_OVERWRITE);
		
		if($aux_spin_codigo == $spin_codigo) {
			
			$rs_serial ++;
			$spin_serial = str_pad(number_format($rs_serial["max_serial"], 0, '', ''), 10, "0", STR_PAD_LEFT);
			
			$sql = "SELECT * FROM pins where pin_codigo = '". $spin_codigo ."' and opr_codigo = ".$this->opr_codigo.";";
			$query = $this->connection->prepare($sql);
			$query->execute();
			$rs_pins = $query->fetchAll(PDO::FETCH_ASSOC);
			
			if(count($rs_pins) == 0){  
				
				$sql = "select * from pins_store where pin_codigo = '".base64_encode($aes->encrypt($spin_codigo))."'";
				$query = $this->connection->prepare($sql);
				$rs_pins_store = $query->fetchAll(PDO::FETCH_ASSOC);
				
				$sql = "select * from pins where pin_codigo = '".base64_encode($aes->encrypt($spin_codigo))."'";
				$query = $this->connection->prepare($sql);
				$rs_pin = $query->fetchAll(PDO::FETCH_ASSOC);
				
				if(count($rs_pins_store) == 0 && count($rs_pin) == 0) {
					
					$sql = "insert into pins (pin_serial, pin_codigo, opr_codigo, pin_valor, pin_lote_codigo, pin_dataentrada, pin_canal, pin_horaentrada,pin_status,pin_validade) values ('".$spin_serial."', '".$spin_codigo."', ".$this->opr_codigo.", ".$this->valor_pin.", ".$rs_lote["max_pin_lote_codigo"].", CURRENT_TIMESTAMP, 's', NOW(),'1',(NOW() + interval '6 month'));";
					$query = $this->connection->prepare($sql);
					$query->execute();
					$last_pin_inserted = $this->connection->lastInsertId();
					
					$chaveaes = base64_encode($aes->encrypt($spin_codigo));
					
					$sql = "insert into pins_store (pin_serial, pin_codigo, pin_caracter, distributor_codigo, pin_valor, pin_lote_codigo, pin_dataentrada,pin_status, pin_canal, pin_formato) values ('".$spin_serial."', '".$chaveaes."', '', ".$this->distributor.", ".$this->valor_pin.", ".$rs_lote["max_pin_lote_codigo"].", CURRENT_TIMESTAMP, 3, 'w', '4');";
					$query = $this->connection->prepare($sql);
					$query->execute();
					$last_pin_inserted_store = $this->connection->lastInsertId();
					
					$rowCount = $query->rowCount();
					
					$sql = "INSERT INTO tb_pins_store_pins (pins_pin_codinterno, pins_store_pin_codinterno) values ($last_pin_inserted, $last_pin_inserted_store)";
					$query = $this->connection->prepare($sql);
					$query->execute();
					
					$this->saveLog($spin_codigo . " (".$chaveaes.")", $rs_lote["max_pin_lote_codigo"], $this->opr_codigo, $this->valor_pin);
					return $last_pin_inserted;
					
					if($rowCount == 0) {
						die();
					}
					
					$pins_final .= $spin_codigo . ", ";
					
				}
			}
		}
	}
}
?>