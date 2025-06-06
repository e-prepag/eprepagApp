<?php
class InibeAtaque {
	
	private $numeroTentativas = 5;
	private $email;
	private $ip;
	
	function __construct($email = null, $ip = null) {
		$this->setEmail($email);
		$this->setIp($ip);
	}
		
	private function setEmail($email) {
		$this->email = $email;
	}
	
	public function getEmail() {
		return $this->email;
	}	

	private function setIp($ip) {
		$this->ip = $ip;
	}
	
	public function getIp() {
		return $this->ip;
	}
	
	private function getNumeroTentativas() {
		return $this->numeroTentativas;
	}
	
	public function verificaAtaque() {
				 
		$sql  = "select 
					count(*) as n
				from tb_venda_games
				where 
					vg_data_inclusao between (NOW()-'1 minutes'::interval) and NOW() 
					and vg_ex_email = '".$this->getEmail()."'
					and vg_http_referer_ip = '".$this->getIp()."';
				";
		$rs   = SQLexecuteQuery($sql);
		
		//colocando o retorno como Não Ataque
		$ret = false;
		
		if($rs) 
			
			while($rs_row = pg_fetch_assoc($rs)) {
				$this->logErrors("Dados da Consulta -> ( email = [".$this->getEmail()."] - ip = [".$this->getIp()."])\nQtde. Tentativas = [".$rs_row['n'] ."]\n");
				if($rs_row['n'] > $this->getNumeroTentativas()) {
					$ret = true;
					$this->logErrors("BLOQUEADO\n");
				}//end if($rs_row['n'] > $this->getNumeroTentativas())
			}//end while($rs_row = pg_fetch_assoc($rs))

		else {			 
			$this->logErrors("ERRORS -> (".pg_errormessage().")");
		}
				
		return $ret;
	}//end function verificaAtaque()
	
	
	private function logErrors($msg) {	
		$fileLog = RAIZ_DO_PROJETO . "log/log_InibeAtaque.log";
		
		$log  = "=================================================================================================\n";
		$log .= "DATA -> ".date("d/m/Y - H:i:s")."\n";
		$log .= "---------------------------------\n";
		$log .= htmlspecialchars_decode($msg);			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);	

	}//end function logErrors($url, $errorMessage)

}//end class
?>