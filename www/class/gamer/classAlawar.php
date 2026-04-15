<?php

class AlawarAPI {
	
	private $certificateID;
	private $gameID;
	private $email;
	private $affiliateID;
	private $locale;
	private $secretKey;	
	private $errorsFound = null;
	private $errorList = array();
	private $gameActivationKey = null;		
	
	function __construct($certificateID = null, $affiliateID = null, $email = null, $secretKey = null, $locale = null, $gameID = null) {
		$this->certificateID = $certificateID;
		$this->gameID = $gameID;
		$this->affiliateID = $affiliateID;
		$this->email = $email;
		$this->secretKey = $secretKey;
		$this->locale = $locale;
	}
		
	public function setCertificateID($certificateID) {
		$this->certificateID = $certificateID;
	}
	
	public function getCertificateID() {
		return $this->certificateID;
	}
		
	public function setGameID($gameID) {
		$this->gameID = $gameID;
	}
	
	public function getGameID() {
		return $this->gameID;
	}

	public function setAffiliateID($affiliateID) {
		$this->affiliateID = $affiliateID;
	}
	
	public function getAffiliateID() {
		return $this->affiliateID;
	}
	
	public function setEmail($email) {
		$this->email = $email;
	}
	
	public function getEmail() {
		return $this->email;
	}	

	public function setLocale($locale) {
		$this->locale = $locale;
	}
	
	public function getLocale() {
		return $this->locale;
	}
	
	public function setSecretKey($secretKey) {
		$this->secretKey = $secretKey;
	}
	
	public function getSecretKey() {
		return $this->secretKey;
	}
	
	private function setError($errorsFound) {
		$this->errorsFound = $errorsFound;
	}
	
	private function setGameActivationKey($gameActivationKey) {
		$this->gameActivationKey = $gameActivationKey;
	}
	
	public function getGameActivationKey() {
		return $this->gameActivationKey;
	}
	
	public function Execute() {
			
		global $ERRORS_ALAWAR_ID, $ERRORS_ALAWAR;
		
		/* 
		 * URL Válida para trocar o CertificateID pela ActivationKey
		 * $urlCURL = "http://eu.partners.export.services.alawar.com/activate_certificate.php?code=$code&gid=$gid&email=$email&srpid=$srpid&locale=$locale&sign=".md5($code.'~'.$gid.'~'.$srpid.'~'.$secret);
		*/
		
		$code = $this->getCertificateID();
		$gid = $this->getGameID();
		$email = $this->getEmail();
		$srpid = $this->getAffiliateID();
		$locale = $this->getLocale();	
		$secretKey = $this->getSecretKey();		
		$sign = md5($code.'~'.$gid.'~'.$srpid.'~'.$secretKey);
		
		$urlCURL = "http://eu.partners.export.services.alawar.com/activate_certificate.php?code=$code&gid=$gid&email=$email&srpid=$srpid&locale=$locale&sign=$sign";		
		
		$ch = curl_init($urlCURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		try {

			$content = curl_exec($ch);			
			$xmlResponse = new SimpleXMLElement(trim($content));
			
			if(isset($xmlResponse->Error)) {
				
				$logErrors = '';
				$this->setError(true);
				
				foreach ($xmlResponse->Error as $errorAlawarAPI) {
					$this->addError($ERRORS_ALAWAR_ID[(string)$errorAlawarAPI]);
					$logErrors .= $errorAlawarAPI.", ";
				}
				
				$this->logErrors($urlCURL, "ERRORS -> $logErrors");
			}
			else {				
				
				$gameActivationKey = (string)$xmlResponse->gamekey ? (string)$xmlResponse->gamekey : (string)$xmlResponse->Key;
				$this->setGameActivationKey($gameActivationKey);				
				$this->registerTransaction();
			}				
		} catch (Exception $e) {
			
			$errmsg  = curl_error($ch);
			$this->setError(true);
			$this->addError($ERRORS_ALAWAR_ID["CURL_CONNECTION_ERROR"]);
			$this->logErrors($urlCURL, "ERRORS -> ".$ERRORS_ALAWAR[$ERRORS_ALAWAR_ID["CURL_CONNECTION_ERROR"]]. " (".$errmsg.")");			
		}
		
		curl_close($ch);				
	}			
	
	private function registerTransaction() {
				 
		$usuarioGamer = UsuarioGames::getIdUsuarioGamerByEmail($this->getEmail());
		
		$sql  = "INSERT INTO pins_alawar (pa_id, pa_certificate_id, pa_data_transacao, pa_activation_key, pa_ug_id, pa_pag_id) VALUES (";
		$sql .= SQLaddFields("DEFAULT", ""). ",";
		$sql .= SQLaddFields($this->getCertificateID(), "s"). ",";
		$sql .= SQLaddFields("CURRENT_TIMESTAMP", ""). ",";
		$sql .= SQLaddFields($this->getGameActivationKey(), "s"). ",";
		$sql .= SQLaddFields($usuarioGamer, ""). ",";
		$sql .= SQLaddFields($this->getGameID(), ""). ")";
		$rs   = SQLexecuteQuery($sql);

		$ret = false;
		
		if($rs) 
			$ret = true;
		else {			 
			$this->logErrors("localhost","ERRORS -> (".pg_errormessage().")");
		}
				
		return $ret;
	}
	
	public function foundErrors() {
		return $this->errorsFound;
	}
	
	public function addError($errorItem) {
		array_push($this->errorList, $errorItem);
	}
	
	public function getErrors() {
		 return $this->errorList;
	}
	
	private function logErrors($url, $errorMessage) {

		$usuarioGamer = UsuarioGames::getIdUsuarioGamerByEmail($this->getEmail());
		
		$sql  = "INSERT INTO pins_alawar_log (pal_id, pal_pa_certificate_id, pal_ug_id, pal_data_log, pal_mensagem_log, pal_pag_id) VALUES (";
		$sql .= SQLaddFields("DEFAULT", ""). ",";
		$sql .= SQLaddFields($this->getCertificateID(), "s"). ",";
		$sql .= SQLaddFields($usuarioGamer, ""). ",";		
		$sql .= SQLaddFields("CURRENT_TIMESTAMP", ""). ",";				
		$sql .= SQLaddFields($errorMessage, "s"). ",";
		$sql .= SQLaddFields($this->getGameID(), ""). ")";
		$rs   = SQLexecuteQuery($sql);

		$log   = "URL -> ".$url."\n";
		$log  .= "SCRIPT -> ".$_SERVER["SCRIPT_FILENAME"]."\n";
		$log  .= "CERTIFICADO -> ".$this->getCertificateID()."\n";
		$log  .= "E-MAIL USER -> ".$this->getEmail()."\n";
		$log  .= "MENSAGEM -> ".$errorMessage."\n";
				
		Utils::logEvent(LOG_FILE_ALAWAR, $log);				
	}	

	
	static public function listAllTransactions($filtro = "", $orderBy = "", $limitQuery = 0, $offSetQuery = 0) {

		$sql  = "SELECT 
					pins_alawar.*,
					usuarios_games.ug_email as ug_email,
					pag_name as nome_jogo, 
					to_char(pa_data_transacao, 'dd/mm/yyyy - HH24:MI:SS') as pa_data_transacao_format 				
				 FROM 
					pins_alawar INNER JOIN pins_alawar_games ON (pa_pag_id = pag_id) 
						        INNER JOIN usuarios_games ON (pa_ug_id = ug_id)
				WHERE 1=1 		          
		";	

		if(!is_null($filtro['pa_id']) && $filtro['pa_id'] != "") {
			$sql .= " AND (" . (is_null($filtro['pa_id'])?1:0);
			$sql .= "=1 OR pa_id = " . SQLaddFields($filtro['pa_id'], "") . ")";
		}
		
		if(!is_null($filtro['ug_email']) && $filtro['ug_email'] != "") {		
			$sql .= " AND (" . (is_null($filtro['ug_email'])?1:0);
			$sql .= "=1 OR UPPER(usuarios_games.ug_email) LIKE '%" . SQLaddFields(strtoupper($filtro['ug_email']), "r") . "%')";
		}
		
		if(!is_null($filtro['pag_name']) && $filtro['pag_name'] != "") {		
			$sql .= " AND (" . (is_null($filtro['pag_name'])?1:0);
			$sql .= "=1 OR UPPER(pins_alawar_games.pag_name) LIKE '%" . SQLaddFields(strtoupper($filtro['pag_name']), "r") . "%')";
		}

		if(!is_null($filtro['pa_certificate_id']) && $filtro['pa_certificate_id'] != "") {		
			$sql .= " AND (" . (is_null($filtro['pa_certificate_id'])?1:0);
			$sql .= "=1 OR pa_certificate_id LIKE '%" . SQLaddFields($filtro['pa_certificate_id'], "r") . "%')";			
		}

		if(!is_null($filtro['pa_activation_key']) && $filtro['pa_activation_key'] != "") {		
			$sql .= " AND (" . (is_null($filtro['pa_activation_key'])?1:0);
			$sql .= "=1 OR pa_activation_key LIKE '%" . SQLaddFields($filtro['pa_activation_key'], "r") . "%')";
		}
		
		/* Data Transacao */
		if(!is_null($filtro['pa_data_transacao_ini']) && !is_null($filtro['pa_data_transacao_fim'])) {
			$filtro['pa_data_transacao_ini'] = formata_data_ts($filtro['pa_data_transacao_ini'] . " 00:00:00", 2, true, true);
			$filtro['pa_data_transacao_fim'] = formata_data_ts($filtro['pa_data_transacao_fim'] . " 23:59:59", 2, true, true);
				
			$sql .= " AND (pa_data_transacao between " . SQLaddFields($filtro['pa_data_transacao_ini'], "s") . " and " . SQLaddFields($filtro['pa_data_transacao_fim'], "s") . ")";
		}
		else if (!is_null($filtro['pa_data_transacao_ini']) && is_null($filtro['pa_data_transacao_fim'])) {
			$filtro['pa_data_transacao_ini'] = formata_data_ts($filtro['pa_data_transacao_ini'] . " 00:00:00", 2, true, true);
			$sql .= " AND (pa_data_transacao >= " . SQLaddFields($filtro['pa_data_transacao_ini'], "s"). ")";
		}
		
		if(!is_null($orderBy) && $orderBy != "") $sql .= " ORDER BY " . $orderBy;
		if(!is_null($limitQuery) && $limitQuery != 0) $sql .= " LIMIT " . $limitQuery;
		if(!is_null($offSetQuery) && $offSetQuery != 0) $sql .= " OFFSET " . $offSetQuery;
		
		$rs = SQLexecuteQuery($sql);
	
		$listAllPurchaseOrders = array();
	
		while ($result = pg_fetch_assoc($rs)) {
			array_push($listAllPurchaseOrders, $result);
		}
		
		return $listAllPurchaseOrders;
	}	


	static public function listLogErrors($filtro = "", $orderBy = "", $limitQuery = 0, $offSetQuery = 0) {
	
		 $sql  = "SELECT
					pins_alawar_log.*,
					usuarios_games.ug_email as ug_email, 
					to_char(pal_data_log, 'dd/mm/yyyy - HH24:MI:SS') as pal_data_log_format 	
				FROM 
					pins_alawar_log INNER JOIN usuarios_games ON (pal_ug_id = ug_id)  
				WHERE 1=1 					  
		";
		
		if(!is_null($filtro['pal_id']) && $filtro['pal_id'] != "") {
			$sql .= " AND (" . (is_null($filtro['pal_id'])?1:0);
			$sql .= "=1 OR pal_id = " . SQLaddFields($filtro['pal_id'], "") . ")";
		}
				
		if(!is_null($filtro['pal_pag_id']) && $filtro['pal_pag_id'] != "") {
			$sql .= " AND (" . (is_null($filtro['pal_pag_id'])?1:0);
			$sql .= "=1 OR pal_pag_id = " . SQLaddFields($filtro['pal_pag_id'], "") . ")";
		}
		
		if(!is_null($filtro['ug_email']) && $filtro['ug_email'] != "") {
			$sql .= " AND (" . (is_null($filtro['ug_email'])?1:0);
			$sql .= "=1 OR UPPER(usuarios_games.ug_email) LIKE '%" . SQLaddFields(strtoupper($filtro['ug_email']), "r") . "%')";
		}
		
		if(!is_null($filtro['pag_name']) && $filtro['pag_name'] != "") {
			$sql .= " AND (" . (is_null($filtro['pag_name'])?1:0);
			$sql .= "=1 OR UPPER(pins_alawar_games.pag_name) LIKE '%" . SQLaddFields(strtoupper($filtro['pag_name']), "r") . "%')";
		}
		
		if(!is_null($filtro['pal_pa_certificate_id']) && $filtro['pal_pa_certificate_id'] != "") {
			$sql .= " AND (" . (is_null($filtro['pal_pa_certificate_id'])?1:0);
			$sql .= "=1 OR pal_pa_certificate_id LIKE '%" . SQLaddFields($filtro['pal_pa_certificate_id'], "r") . "%')";
		}
		
		if(!is_null($filtro['pal_mensagem_log']) && $filtro['pal_mensagem_log'] != "") {
			$sql .= " AND (" . (is_null($filtro['pal_mensagem_log'])?1:0);
			$sql .= "=1 OR pal_mensagem_log LIKE '%" . SQLaddFields($filtro['pal_mensagem_log'], "r") . "%')";
		}
		
		/* Data Log */
		if(!is_null($filtro['pal_data_log_ini']) && !is_null($filtro['pal_data_log_fim'])) {
			$filtro['pal_data_log_ini'] = formata_data_ts($filtro['pal_data_log_ini'] . " 00:00:00", 2, true, true);
			$filtro['pal_data_log_fim'] = formata_data_ts($filtro['pal_data_log_fim'] . " 23:59:59", 2, true, true);
		
			$sql .= " AND (pal_data_log between " . SQLaddFields($filtro['pal_data_log_ini'], "s") . " and " . SQLaddFields($filtro['pal_data_log_fim'], "s") . ")";
		}
		else if (!is_null($filtro['pal_data_log_ini']) && is_null($filtro['pal_data_log_fim'])) {
			$filtro['pal_data_log_ini'] = formata_data_ts($filtro['pal_data_log_ini'] . " 00:00:00", 2, true, true);
			$sql .= " AND (pal_data_log >= " . SQLaddFields($filtro['pal_data_log_ini'], "s"). ")";
		}
		
		if(!is_null($orderBy) && $orderBy != "") $sql .= " ORDER BY " . $orderBy;
		if(!is_null($limitQuery) && $limitQuery != 0) $sql .= " LIMIT " . $limitQuery;
		if(!is_null($offSetQuery) && $offSetQuery != 0) $sql .= " OFFSET " . $offSetQuery;		
				
		$rs = SQLexecuteQuery($sql);
	
		$listOfGameLog = array();
	
		while ($result = pg_fetch_assoc($rs)) {			
			$sqlGame = "SELECT pag_name,pag_online_game FROM pins_alawar_games WHERE pag_id=".SQLaddFields($result['pal_pag_id'], "");
			$rsGame = pg_fetch_assoc(SQLexecuteQuery($sqlGame));

			if($rsGame['pag_name'])
				$result['nome_jogo'] = $rsGame['pag_name'].($rsGame['pag_online_game']==1? ' (online) ' : '');
			else
				$result['nome_jogo'] =  iconv("UTF-8", "ISO-8859-1", "ID Inválido -> (".$result['pal_pag_id'].")");
			
			array_push($listOfGameLog, $result);
		}
	
		return $listOfGameLog;
	}
}


?>

