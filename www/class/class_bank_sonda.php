<?php
class bank_sonda {
    
    // private vars
    private $_bank_sonda;
    private $_date_last_db_restore;
    // how long to wait for the Sonda to complete (in seconds)
    private $CONST_delay_max;

    function __construct() {

		$this->CONST_delay_max = 9;
		
		$this->_bank_sonda = array(
			$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']	=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']		=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']		=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']		=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_HIPAY_ONLINE']			=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PAYPAL_ONLINE']			=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']			=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),

			// Todos os pagamentos Cielo usam a mesma Sonda
			'C'									=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			/*
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']				=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']				=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']				=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']			=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']				=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']				=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']			=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
			$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']			=> array('time_waiting_for_sonda' => 0, 'pagto_blocked' => false, 'last_numcompra' => 0, ),
				*/
		);
	}


	public function key_is_Cielo($iforma) {
		$ret = false;
		if(
			($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']) || 
			($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']) || 
			($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']) || 
			($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']) || 
			($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']) || 
			($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']) || 
			($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']) || 
			($iforma==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO'])
			) {
			$ret = true;
		}
		return $ret;
	}
	public function adjust_key_as_Cielo($iforma) {
		if($this->key_is_Cielo($iforma)) {
			return 'C';
		}
		return $iforma;
	}

	public function get_date_last_db_restore() {
		return $this->_date_last_db_restore;
	}

	public function get_pagto_blocked($iforma) {
		$ret = false;
		foreach($this->_bank_sonda as $key => $val) {
			$iforma_mod = $this->adjust_key_as_Cielo($iforma);
			if($key == $iforma_mod) {
				$ret = $val['pagto_blocked'];
				break;
			}
		}
		return $ret;
	}
	public function set_pagto_blocked($iforma, $pagto_blocked) {
		foreach($this->_bank_sonda as $key => $val) {
			$iforma_mod = $this->adjust_key_as_Cielo($iforma);
			if($key == $iforma_mod) {
				$this->_bank_sonda[$iforma_mod]['pagto_blocked'] = $pagto_blocked;
				break;
			}
		}
	}

	public function get_last_numcompra($iforma) {
		$ret = 0;
		foreach($this->_bank_sonda as $key => $val) {
			$iforma_mod = $this->adjust_key_as_Cielo($iforma);
			if($key == $iforma_mod) {
				$ret = $val['last_numcompra'];
				break;
			}
		}
		return $ret;
	}
	public function set_last_numcompra($iforma, $last_numcompra) {
		foreach($this->_bank_sonda as $key => $val) {
			$iforma_mod = $this->adjust_key_as_Cielo($iforma);
			if($key == $iforma_mod) {
				$this->_bank_sonda[$iforma_mod]['last_numcompra'] = $last_numcompra;
				break;
			}
		}
	}

	public function get_time_waiting_for_sonda($iforma) {
		$ret = 0;
		foreach($this->_bank_sonda as $key => $val) {
			$iforma_mod = $this->adjust_key_as_Cielo($iforma);
			if($key == $iforma_mod) {
				$ret = $val['time_waiting_for_sonda'];
				break;
			}
		}
		return $ret;
	}
	public function set_time_waiting_for_sonda($iforma, $time_waiting_for_sonda) {
		foreach($this->_bank_sonda as $key => $val) {
			$iforma_mod = $this->adjust_key_as_Cielo($iforma);
			if($key == $iforma_mod) {
				$this->_bank_sonda[$iforma_mod]['time_waiting_for_sonda'] = $time_waiting_for_sonda;
				break;
			}
		}
	}

	public function is_bank_blocked($iforma) {
		$ret = $this->get_pagto_blocked($iforma);
		return $ret;
	}

	public function start_time_waiting_for_sonda($iforma) {
		$iforma_mod = $this->adjust_key_as_Cielo($iforma);
		$this->time_stats = getmicrotime();
	}
	public function stop_time_waiting_for_sonda($iforma) {
		$time_waiting_for_sonda = getmicrotime() - $this->time_stats;
		foreach($this->_bank_sonda as $key => $val) {
			$iforma_mod = $this->adjust_key_as_Cielo($iforma);
			if($key == $iforma_mod) {
				$this->_bank_sonda[$iforma_mod]['time_waiting_for_sonda'] = $time_waiting_for_sonda;
				break;
			}
		}
	}
	public function block_bank_if_slow($iforma) {
		$iforma_mod = $this->adjust_key_as_Cielo($iforma);
		if($this->_bank_sonda[$iforma_mod]['time_waiting_for_sonda']==0) {
			return false;
		}
		if($this->_bank_sonda[$iforma_mod]['time_waiting_for_sonda']>=$this->CONST_delay_max) {
			$this->_bank_sonda[$iforma_mod]['pagto_blocked'] = true;
			return $this->_bank_sonda[$iforma_mod]['pagto_blocked'];
		}
		return false;
	}
	public function unblock_bank_if_normal($iforma) {
		$bret = false;
		$iforma_mod = $this->adjust_key_as_Cielo($iforma);
		echo "Desbloqueando Sonda de banco ('$iforma_mod', time_waiting_for_sonda: '".$this->_bank_sonda[$iforma_mod]['time_waiting_for_sonda']."') - ";
		if($this->_bank_sonda[$iforma_mod]['time_waiting_for_sonda']==0) {
			$bret = true;
		} elseif($this->_bank_sonda[$iforma_mod]['time_waiting_for_sonda'] < $this->CONST_delay_max) {
			$this->_bank_sonda[$iforma_mod]['pagto_blocked'] = false;
			$bret = true;
		}
		echo "Resultado de unblock_bank_if_normal('$iforma_mod') [".$this->_bank_sonda[$iforma_mod]['time_waiting_for_sonda']."?".$this->CONST_delay_max."] - ".(($bret)?"[Desbloqueia]":"[deixa como está]").PHP_EOL;
		return $bret;
	}

	public function list_registers($bHTML) {
		
		$sret = "";
		if($bHTML) {
			$sret .= "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>".PHP_EOL;
			$sret .= "<tr align='center'><td><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'><b>key</b></font></td> <td><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'><b>pagto_blocked</b></font></td> <td><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'><b>last_numcompra</b></font></td> <td><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'><b>time_waiting_for_sonda</b></font></td> </tr>".PHP_EOL;
		} else {
			$sret .= str_repeat("-", 80).PHP_EOL."key	pagto_blocked	last_numcompra	time_waiting_for_sonda".PHP_EOL;
		}
		foreach($this->_bank_sonda as $key => $val) {
			if($bHTML) {
				$sret .= "<tr align='center'><td><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>$key</font></td> <td><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>".(($val['pagto_blocked'])?"YES":"nope")."</font></td> <td><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>".$val['last_numcompra']."</font></td> <td><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>".number_format($val['time_waiting_for_sonda'], 2, '.', '.')."</font></td> </tr>".PHP_EOL;
			} else {
				$sret .= "$key	".(($val['pagto_blocked'])?"YES":"nope")."	".$val['last_numcompra']."	".number_format($val['time_waiting_for_sonda'], 2, '.', '.').PHP_EOL;
			}
		}
		if($bHTML) {
			$sret .= "</table>";
		}
		$sret .= PHP_EOL;
		return $sret;
	}

	public function get_list_blocked_banks() {
		$aret = array();
		foreach($this->_bank_sonda as $key => $val) {
			if($val['pagto_blocked']) {
				$aret[$key] = $val;
			}
		}
		return $aret;
	}

	public function load_banks_sonda_array() {
		$aret = array();
		$sql  = "select pc_banks_sonda_array, pc_data_banks_sonda_array from pag_config where pc_id = 1;";
		$rs = SQLexecuteQuery($sql);
		if($rs && pg_num_rows($rs) > 0){
			$rs_row = pg_fetch_array($rs);
			$aret = unserialize($rs_row['pc_banks_sonda_array']);
			$this->_bank_sonda = $aret;
			$this->_date_last_db_restore = $rs_row['pc_data_banks_sonda_array'];
//			echo "<pre>".print_r($rs_row, true)."</pre>";
		}
		return $aret;
	}
	public function save_banks_sonda_array() {
		$ret = serialize($this->_bank_sonda);
		$sql  = "update pag_config set pc_banks_sonda_array = '".$ret."', pc_data_banks_sonda_array = CURRENT_TIMESTAMP where pc_id = 1;";
		$rs = SQLexecuteQuery($sql);
	}

}

?>