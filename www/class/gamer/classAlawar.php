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
		 * URL VÃ¡lida para trocar o CertificateID pela ActivationKey
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
				 
		$usuarioGames = new UsuarioGames();
		$usuarioGamer = $usuarioGames->getIdUsuarioGamerByEmail($this->getEmail());
		
		$sql  = "INSERT INTO pins_alawar (pa_id, pa_certificate_id, pa_data_transacao, pa_activation_key, pa_ug_id, pa_pag_id) VALUES (DEFAULT, $1, CURRENT_TIMESTAMP, $2, $3, $4)";
		$rs   = SQLexecuteQueryParams($sql, array($this->getCertificateID(), $this->getGameActivationKey(), $usuarioGamer, $this->getGameID()));


		$ret = false;
		
		if($rs) 
			$ret = true;
		else {			 
			$this->logErrors("localhost","ERRORS -> (".pg_last_error().")");
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

		$usuarioGames = new UsuarioGames();
		$usuarioGamer = $usuarioGames->getIdUsuarioGamerByEmail($this->getEmail());
		
		$sql  = "INSERT INTO pins_alawar_log (pal_id, pal_pa_certificate_id, pal_ug_id, pal_data_log, pal_mensagem_log, pal_pag_id) VALUES (DEFAULT, $1, $2, CURRENT_TIMESTAMP, $3, $4)";
		$rs   = SQLexecuteQueryParams($sql, array($this->getCertificateID(), $usuarioGamer, $errorMessage, $this->getGameID()));


		$log   = "URL -> ".$url."\n";
		$log  .= "SCRIPT -> ".$_SERVER["SCRIPT_FILENAME"]."\n";
		$log  .= "CERTIFICADO -> ".$this->getCertificateID()."\n";
		$log  .= "E-MAIL USER -> ".$this->getEmail()."\n";
		$log  .= "MENSAGEM -> ".$errorMessage."\n";
				
		Utils::logEvent(LOG_FILE_ALAWAR, $log);				
	}	

	private static function addSqlParam(&$params, $value) {
		$params[] = $value;
		return '$' . count($params);
	}

	private static function filtroValue($filtro, $key) {
		return is_array($filtro) && array_key_exists($key, $filtro) ? $filtro[$key] : null;
	}

	private static function orderSql($orderBy, $allow) {
		$parts = array();
		foreach (explode(',', (string)$orderBy) as $part) {
			$bits = preg_split('/\s+/', trim($part));
			$field = $bits[0];
			$dir = isset($bits[1]) && strtolower($bits[1]) == 'desc' ? 'DESC' : 'ASC';
			if (isset($allow[$field])) $parts[] = $allow[$field] . ' ' . $dir;
		}
		return implode(', ', $parts);
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
		$params = array();

		if(self::filtroValue($filtro, 'pa_id') !== null && self::filtroValue($filtro, 'pa_id') !== '') {
			$sql .= " AND pa_id = " . self::addSqlParam($params, self::filtroValue($filtro, 'pa_id'));
		}
		
		if(self::filtroValue($filtro, 'ug_email') !== null && self::filtroValue($filtro, 'ug_email') !== '') {		
			$sql .= " AND UPPER(usuarios_games.ug_email) LIKE " . self::addSqlParam($params, '%' . strtoupper(self::filtroValue($filtro, 'ug_email')) . '%');
		}
		
		if(self::filtroValue($filtro, 'pag_name') !== null && self::filtroValue($filtro, 'pag_name') !== '') {		
			$sql .= " AND UPPER(pins_alawar_games.pag_name) LIKE " . self::addSqlParam($params, '%' . strtoupper(self::filtroValue($filtro, 'pag_name')) . '%');
		}

		if(self::filtroValue($filtro, 'pa_certificate_id') !== null && self::filtroValue($filtro, 'pa_certificate_id') !== '') {		
			$sql .= " AND pa_certificate_id LIKE " . self::addSqlParam($params, '%' . self::filtroValue($filtro, 'pa_certificate_id') . '%');			
		}

		if(self::filtroValue($filtro, 'pa_activation_key') !== null && self::filtroValue($filtro, 'pa_activation_key') !== '') {		
			$sql .= " AND pa_activation_key LIKE " . self::addSqlParam($params, '%' . self::filtroValue($filtro, 'pa_activation_key') . '%');
		}
		
		/* Data Transacao */
		if(self::filtroValue($filtro, 'pa_data_transacao_ini') !== null && self::filtroValue($filtro, 'pa_data_transacao_fim') !== null) {
			$filtro['pa_data_transacao_ini'] = formata_data_ts($filtro['pa_data_transacao_ini'] . " 00:00:00", 2, true, true);
			$filtro['pa_data_transacao_fim'] = formata_data_ts($filtro['pa_data_transacao_fim'] . " 23:59:59", 2, true, true);
			$sql .= " AND (pa_data_transacao between " . self::addSqlParam($params, $filtro['pa_data_transacao_ini']) . " and " . self::addSqlParam($params, $filtro['pa_data_transacao_fim']) . ")";
		}
		else if (self::filtroValue($filtro, 'pa_data_transacao_ini') !== null && self::filtroValue($filtro, 'pa_data_transacao_fim') === null) {
			$filtro['pa_data_transacao_ini'] = formata_data_ts($filtro['pa_data_transacao_ini'] . " 00:00:00", 2, true, true);
			$sql .= " AND (pa_data_transacao >= " . self::addSqlParam($params, $filtro['pa_data_transacao_ini']) . ")";
		}
		
		$order = self::orderSql($orderBy, array(
			'pa_id' => 'pa_id',
			'ug_email' => 'usuarios_games.ug_email',
			'pag_name' => 'pins_alawar_games.pag_name',
			'pa_certificate_id' => 'pa_certificate_id',
			'pa_activation_key' => 'pa_activation_key',
			'pa_data_transacao' => 'pa_data_transacao'
		));
		if($order != "") $sql .= " ORDER BY " . $order;
		if(!is_null($limitQuery) && (int)$limitQuery != 0) $sql .= " LIMIT " . (int)$limitQuery;
		if(!is_null($offSetQuery) && (int)$offSetQuery != 0) $sql .= " OFFSET " . (int)$offSetQuery;
		
		$rs = $params ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
	
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
		$params = array();
		
		if(self::filtroValue($filtro, 'pal_id') !== null && self::filtroValue($filtro, 'pal_id') !== '') {
			$sql .= " AND pal_id = " . self::addSqlParam($params, self::filtroValue($filtro, 'pal_id'));
		}
				
		if(self::filtroValue($filtro, 'pal_pag_id') !== null && self::filtroValue($filtro, 'pal_pag_id') !== '') {
			$sql .= " AND pal_pag_id = " . self::addSqlParam($params, self::filtroValue($filtro, 'pal_pag_id'));
		}
		
		if(self::filtroValue($filtro, 'ug_email') !== null && self::filtroValue($filtro, 'ug_email') !== '') {
			$sql .= " AND UPPER(usuarios_games.ug_email) LIKE " . self::addSqlParam($params, '%' . strtoupper(self::filtroValue($filtro, 'ug_email')) . '%');
		}
		
		if(self::filtroValue($filtro, 'pag_name') !== null && self::filtroValue($filtro, 'pag_name') !== '') {
			$sql .= " AND UPPER(pins_alawar_games.pag_name) LIKE " . self::addSqlParam($params, '%' . strtoupper(self::filtroValue($filtro, 'pag_name')) . '%');
		}
		
		if(self::filtroValue($filtro, 'pal_pa_certificate_id') !== null && self::filtroValue($filtro, 'pal_pa_certificate_id') !== '') {
			$sql .= " AND pal_pa_certificate_id LIKE " . self::addSqlParam($params, '%' . self::filtroValue($filtro, 'pal_pa_certificate_id') . '%');
		}
		
		if(self::filtroValue($filtro, 'pal_mensagem_log') !== null && self::filtroValue($filtro, 'pal_mensagem_log') !== '') {
			$sql .= " AND pal_mensagem_log LIKE " . self::addSqlParam($params, '%' . self::filtroValue($filtro, 'pal_mensagem_log') . '%');
		}
		
		/* Data Log */
		if(self::filtroValue($filtro, 'pal_data_log_ini') !== null && self::filtroValue($filtro, 'pal_data_log_fim') !== null) {
			$filtro['pal_data_log_ini'] = formata_data_ts($filtro['pal_data_log_ini'] . " 00:00:00", 2, true, true);
			$filtro['pal_data_log_fim'] = formata_data_ts($filtro['pal_data_log_fim'] . " 23:59:59", 2, true, true);
			$sql .= " AND (pal_data_log between " . self::addSqlParam($params, $filtro['pal_data_log_ini']) . " and " . self::addSqlParam($params, $filtro['pal_data_log_fim']) . ")";
		}
		else if (self::filtroValue($filtro, 'pal_data_log_ini') !== null && self::filtroValue($filtro, 'pal_data_log_fim') === null) {
			$filtro['pal_data_log_ini'] = formata_data_ts($filtro['pal_data_log_ini'] . " 00:00:00", 2, true, true);
			$sql .= " AND (pal_data_log >= " . self::addSqlParam($params, $filtro['pal_data_log_ini']) . ")";
		}
		
		$order = self::orderSql($orderBy, array(
			'pal_id' => 'pal_id',
			'pal_pag_id' => 'pal_pag_id',
			'ug_email' => 'usuarios_games.ug_email',
			'pal_pa_certificate_id' => 'pal_pa_certificate_id',
			'pal_mensagem_log' => 'pal_mensagem_log',
			'pal_data_log' => 'pal_data_log'
		));
		if($order != "") $sql .= " ORDER BY " . $order;
		if(!is_null($limitQuery) && (int)$limitQuery != 0) $sql .= " LIMIT " . (int)$limitQuery;
		if(!is_null($offSetQuery) && (int)$offSetQuery != 0) $sql .= " OFFSET " . (int)$offSetQuery;		
				
		$rs = $params ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
	
		$listOfGameLog = array();
	
		while ($result = pg_fetch_assoc($rs)) {			
			$sqlGame = "SELECT pag_name,pag_online_game FROM pins_alawar_games WHERE pag_id=$1";
			$rsGame = pg_fetch_assoc(SQLexecuteQueryParams($sqlGame, array($result['pal_pag_id'])));

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

