<?php
class Logger
{
	private $fp = null;
	
	public function logOpen()
	{
		$this->fp = fopen($GLOBALS['raiz_do_projeto'] . "log/log_xml_cielo.log", 'a');
	}
	 
	public function logWrite($strMessage, $transacao)
	{
		if(!$this->fp)
			$this->logOpen();
		
		$ip = "";	//retorna_ip_acesso_new_sonda();
		$path = (isset($GLOBALS['_SERVER']["REQUEST_URI"]))?$GLOBALS['_SERVER']["REQUEST_URI"]:"";
		$data = date("Y-m-d H:i:s:u (T)");
		
		$log = "***********************************************" . PHP_EOL;
		$log .= $data . PHP_EOL;
		if($ip) {
			$log .= "IP de Tentativa: ".$ip . PHP_EOL;
		} else {
			$log .= "IP de Tentativa: CONCILIACAO".PHP_EOL;
		}
		$log .= "DO ARQUIVO: " . (($path)?$path:"REQUEST_URI EMPTY") . PHP_EOL; 
		$log .= "OPERAวรO: " . $transacao . PHP_EOL;
		$log .= $strMessage .PHP_EOL.PHP_EOL; 

		fwrite($this->fp, $log);
	}
}
?>