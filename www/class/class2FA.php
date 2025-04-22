<?php
require_once "/www/includes/constantes.php";
require_once "/www/includes/functions.php";
require_once "/www/includes/gamer/functions.php";
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php";
require_once "/www/class/classEmailAutomatico.php";
require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/includes/configIP.php";
require_once "/www/class/phpmailer/class.smtp.php";

class TwoFactorAuthenticator {
	
	private $random_code;
	private $development;
	private $connection;
	private $ug_id;
	private $ug_login;
	private $ug_email;
	
	public function __construct(
		$ambient = null,
		$ug_id = null,
		$ug_login = null,
		$ug_email = null
	) {
		$this->development = $ambient;
		$this->connection = ConnectionPDO::getConnection()->getLink();
		$this->ug_id = $ug_id;
		$this->ug_login = $ug_login;
		$this->ug_email = $ug_email;
	}
	
	public function generate_random_code(){
		$code = mt_rand(100000,999999);
		
		$token = hash_hmac('sha256',$code, getEnvVariable('HMAC_SECRET'));
		
		return $token;
	}
	
	public function grava_2fa_log($email) {
		try {
			$file = fopen("/www/log/2fa_tokens.txt", "a+");
			fwrite($file, str_repeat("*", 50)."\n");
			fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
			fwrite($file, "Teste Livrodjx". "\n");
			fwrite($file, "email: ".$email."\n");
			fclose($file);
		}catch (Exception $e) {
			echo "Error(6) writing monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage().PHP_EOL;
		}
	}
	
	
	public function send_email() {
		
		$token_url = $this->generate_random_code();
		
		if($this->development == "PDV") {
			$sql = "SELECT * FROM tokens_2fa WHERE ug_id = :UG_ID AND ambient = 'p'";
			$query = $this->connection->prepare($sql);
			$query->bindValue(":UG_ID", $this->ug_id);
			$query->execute();
			

			if($query->rowCount() == 0) {
				$sql = "INSERT INTO tokens_2fa(id, token, ug_id, ambient) VALUES (default, :TOKEN, :UG_ID, 'p');";
				$query = $this->connection->prepare($sql);
				$query->bindValue(":TOKEN", $token_url);
				$query->bindValue(":UG_ID", $this->ug_id);
				
				$query->execute();
				
				$errorCode = $query->errorCode();
				if ($errorCode === '00000') {
					// O INSERT foi executado com sucesso
					
					$envia_email = new EnvioEmailAutomatico('G', 'TWOFA');
					
					$envia_email->set2FaNome($this->ug_login);
					$envia_email->set2FaToken($token_url);
					$envia_email->setUrl2FaToken('P');
					
					$to = strtolower($this->ug_email);
					$cc = "";
					$bcc = "";
					$subject = "E-prepag - Código de Segundo Fator de Autenticação";
					$msg = $envia_email->getCorpoEmail();
					
					$this->grava_2fa_log($to);
					enviaEmail3($to, $cc, $bcc, $subject, $msg, "");
				} 

			} else {
				$sql = "DELETE FROM tokens_2fa WHERE ug_id = :UG_ID and ambient = 'p';";
				$query = $this->connection->prepare($sql);
				$query->bindValue(":UG_ID", $this->ug_id);
				
				$query->execute();
				
				$this->send_email();
			}
		}
		else {
			
			$sql = "SELECT * FROM tokens_2fa WHERE ug_id = :UG_ID AND ambient = 'u';";
			$query = $this->connection->prepare($sql);
			$query->bindValue(":UG_ID", $this->ug_id);
			$query->execute();
			

			if($query->rowCount() == 0) {
				$sql = "INSERT INTO tokens_2fa(id, token, ug_id, ambient) VALUES (default, :TOKEN, :UG_ID, 'u');";
				$query = $this->connection->prepare($sql);
				$query->bindValue(":TOKEN", $token_url);
				$query->bindValue(":UG_ID", $this->ug_id);
				
				$query->execute();
				
				$errorCode = $query->errorCode();
				if ($errorCode === '00000') {
					// O INSERT foi executado com sucesso
					$envia_email = new EnvioEmailAutomatico('G', 'TWOFA');
					
					$envia_email->set2FaNome($this->ug_login);
					$envia_email->set2FaToken($token_url);
					$envia_email->setUrl2FaToken('U');
					
					$to = strtolower($this->ug_email);
					$cc = "";
					$bcc = "";
					$subject = "E-prepag - Código de Segundo Fator de Autenticação";
					$msg = $envia_email->getCorpoEmail();
					
					$this->grava_2fa_log($to);
					enviaEmail3($to, $cc, $bcc, $subject, $msg, "");
				} 

			} else {
				$sql = "DELETE FROM tokens_2fa WHERE ug_id = :UG_ID and ambient = 'u';";
				$query = $this->connection->prepare($sql);
				$query->bindValue(":UG_ID", $this->ug_id);
				
				$query->execute();
				
				$this->send_email();
			}
		}
	}
	
	public function verify_token($token){
		
		$sql = "SELECT * FROM tokens_2fa WHERE token = :TOKEN";
		$query = $this->connection->prepare($sql);
		$query->bindParam(':TOKEN', $token);
		$query->execute();
		
		if($query->rowCount() > 0) {
			$result = $query->fetch(PDO::FETCH_ASSOC);
			
			$update_sql = "UPDATE tokens_2fa SET foi_verificado = 1 WHERE token = :TOKEN";
			$update_query = $this->connection->prepare($update_sql);
			$update_query->bindValue(':TOKEN', $token);
			$update_query->execute();
			
			return $result;
		}
	
		return false;
		
	}
	
	public function verify_time() {
		
		$sql = "SELECT * FROM tokens_2fa WHERE ug_id = :UG_ID;";
		$query = $this->connection->prepare($sql);
		$query->bindValue(":UG_ID", $this->ug_id);
		$query->execute();
		
		if($query->rowCount() > 0) {
			$result = $query->fetch(PDO::FETCH_ASSOC);
			
			$dataCriacao = new DateTime($result["data_criacao"]);

			// Adicionar 3 horas à data de criação
			$dataExpiracao = clone $dataCriacao;
			$dataExpiracao->add(new DateInterval("PT3H")); // Adiciona 3 horas

			// Obter a data e hora atual
			$dataAtual = new DateTime();

			// Verificar se a data de expiração é anterior à data e hora atual
			if ($dataExpiracao < $dataAtual) {
				return true;
			} else {
				return false;
			}
		}
		
		return true;
	}
	
	public function verify_activate($ug_id) {
		
		$sql = "SELECT * FROM tokens_2fa WHERE ug_id = :UG_ID";
		$query = $this->connection->prepare($sql);
		$query->bindValue(":UG_ID", $ug_id);
		$query->execute();
		
		if($query->rowCount() > 0) {
			$result = $query->fetch(PDO::FETCH_ASSOC);
			
			if($result["foi_verificado"] == 1) {
				return true;
			}
			
			return false;
		}
		
		return false;
	}
}
?>