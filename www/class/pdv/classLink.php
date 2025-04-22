<?php

if(!class_exists('Provedor')){
	require_once '/www/class/pdv/classProvedor.php';
    require_once '/www/class/pdv/classSMS.php';
}

class LinkAcesso {
	
	private $token;
	private $login;
	private $provedor;
	private $usuario;
	public $serviceSMS;
	
	public function __construct($login){
		$this->provedor = new Provedor();
		$this->serviceSMS = new SMS();
		$this->login = $login;
	}
	
	private function verificaLogin(){
		$conexao = ConnectionPDO::getConnection()->getLink();		
		$query = $conexao->prepare("select ug_id from dist_usuarios_games where ug_login = :LOGIN;");
		$login = strtoupper(strtolower($this->login));
	    $query->bindValue(":LOGIN", $login);
        $query->execute();
		$retorno = $query->fetch(PDO::FETCH_ASSOC);
		if($retorno != false){
			$this->usuario = $retorno["ug_id"];
		}
	}
	
	public function registra($token){		
		$this->verificaLogin();
		if(empty($this->usuario)){
			return false;
		}
		$id = $this->provedor->coletaProvedor($this->usuario, 'token', 'principal');
		if($id != false){
			$verificaToken = $this->geraToken($id, $token); 
			return $verificaToken ? $this->token: false;
		}
		return false;
	}
	
	private function registraToken($id, $codeLink){
		$conexao = ConnectionPDO::getConnection()->getLink();		
		$query = $conexao->prepare("update inform_pdv set token = :TOKEN, code_link = :LINK where usuario = :USU and cd_inform = :COD;");
	    $query->bindValue(":TOKEN", $this->token);
	    $query->bindValue(":USU", $this->usuario);
		$query->bindValue(":LINK", $codeLink);
	    $query->bindValue(":COD", $id);
        $query->execute();
		
		if($query->rowCount() > 0){
			return true;
		}
        return false;
	}
	
	private function geraToken($id, $codeLink){
		$this->token = $this->serviceSMS->code();
		return $this->registraToken($id, $codeLink);
	}
	
	public function registraLink($url, $token){
		$date = new DateTime('now');
		$date->add(new DateInterval('PT1H')); 
		$conexao = ConnectionPDO::getConnection()->getLink();		
		$query = $conexao->prepare("insert into link_login(url,expira,code)values(:URL,:EXP,:CODE);");
	    $query->bindValue(":URL", $url);
	    $query->bindValue(":EXP", $date->format("Y-m-d H:i:s"));
	    $query->bindValue(":CODE", $token);
        $query->execute();
	}
	
	public function recuparaLink($token){
		$conexao = ConnectionPDO::getConnection()->getLink();		
		$query = $conexao->prepare("select expira from link_login where code = :CODE;");
	    $query->bindValue(":CODE", $token);
        $query->execute();
		$result = $query->fetch(PDO::FETCH_ASSOC);
		
		if($result != false){
			return strtotime($result['expira']);
		}
		
		return false;
	}
	
	public static function geraLink(){
		$link = new LinkAcesso(null);
		$token = $link->serviceSMS->code();
		$url = "https://www.e-prepag.com.br/link/auth.php?code=".$token;
		$link->registraLink($url, $token);
		return $url;
	}
	
	public static function formulaMensagem($tel){
		$link = new LinkAcesso(null);
		$url = self::geraLink();
		$msg = "Olá, aqui está o seu link de verificação E-Prepag: ".$url." para continuar o seu acesso.";
		return $link->serviceSMS->sendSMS($tel, $msg);
	}
	
	public function verificaAcesso($token){
		$erros = [];
		$conexao = ConnectionPDO::getConnection()->getLink();		
		$retorno = $this->provedor->selectInform('token', $token);
		if($retorno != false){
			if(strtotime(date("Y-m-d H:i:s")) > $this->recuparaLink($retorno["code_link"])){
				$erros["code"][] = '(00) O token está expirado.';
				return $erros;
			}
			$selectUsuario = $conexao->prepare("select ug_cidade, ug_estado from dist_usuarios_games where ug_id = :USUARIO;");
			$selectUsuario->bindValue(':USUARIO', $retorno["usuario"]);
			$selectUsuario->execute();
			$usuario = $selectUsuario->fetch(PDO::FETCH_ASSOC);		
				if($_SERVER["REMOTE_ADDR"] != $retorno["ip"]){
					$infoUf = $this->provedor->coletaProvedor($retorno["usuario"], 'token', 'funcionario');
					$estado = empty($infoUf["state"])? $infoUf["state_en"]: $infoUf["state"];
					$cidade = isset($infoUf["city"])? $infoUf["city"]: $infoUf["uf"];
					$pais = $infoUf["country"];
				}else{
					$infoUf = explode('/', $retorno["uf_cidade"]);
					$estado = $infoUf[0];
					$cidade = isset($infoUf[1])? utf8_encode($infoUf[1]): utf8_encode($retorno["estado"]);
					$pais = $retorno["pais"];
				}
				if($pais != "BR"){
					$erros["code"][] = '(02) O pais de onde está acessando é invalido.';
				}
				if($estado == $usuario["ug_estado"]){
					if($cidade != utf8_encode($usuario["ug_cidade"])){
						$erros["code"][] = '(04) A cidade de onde está acessando é invalido.';
					}
				}else{
					$erros["code"][] = '(03) O estado de onde está acessando é invalido.';
				}	
		}else{
			$erros["code"][] = '(01) Não foi encontrado os dados do TOKEN.';
		}
		return $erros;
	}
	
	public function verificaFamiliaIps() {
		$ips = [
			'200.217.103.187',
			'200.173.208.169',
			'200.173.208.169',
			'200.173.203.121',
			'200.173.202.152',
			'200.173.198.129',
			'200.173.196.150',
			'191.96.5.109',
			'191.96.5.113',
			'191.96.5.156',
			'191.96.5.189',
			'191.96.5.193',
			'191.96.5.62',
			'191.96.5.64',
			'191.96.5.80',
			'191.96.5.94',
			'189.127.60.146',
			'179.102.140.124',
			'179.102.129.22',
			'177.101.92.6',
			'177.51.84.213',
			'177.51.82.153',
			'169.150.198.91',
			'169.150.220.146',
			'169.150.220.152',
			'169.150.220.156',
			'169.150.220.159',
			'169.150.198.77',
			'169.150.198.77',
			'169.150.198.77',
			'169.150.198.90',
			'169.150.198.91',
			'169.150.198.91',
			'149.102.233.198',
			'149.102.233.180',
			'149.102.233.186',
			'149.102.233.196',
			'149.102.233.206',
			'149.102.233.224',
			'149.102.233.230',
			'149.102.233.233',
			'149.78.184.206',
			'149.78.184.206',
			'149.78.184.206',
			'149.78.184.205',
			'149.78.184.205',
			'149.78.184.205',
			'149.78.184.205',
			'143.137.155.249',
			'131.221.99.11',
			'131.221.99.114',
			'85.10.192.143',
			'20.163.125.12',
			'20.163.125.12',
			'45.180.219.19',
			'45.180.219.19',
			'45.180.219.2',
			'45.180.219.25',
			'45.180.219.25',
			'45.180.219.25',
			'149.102.251.98',
			'38.62.214.130',
			'138.199.58.244',
			'38.174.59.103',
			'200.217.103.187',
			'200.173.208.169',
			'200.173.208.169',
			'200.173.203.121',
			'200.173.202.152',
			'200.173.198.129',
			'200.173.196.150',
			'191.96.5.109',
			'191.96.5.113',
			'191.96.5.156',
			'191.96.5.189',
			'191.96.5.193',
			'191.96.5.62',
			'191.96.5.64',
			'191.96.5.80',
			'191.96.5.94',
			'189.127.60.146',
			'179.102.140.124',
			'179.102.129.22',
			'177.101.92.6',
			'177.51.84.213',
			'177.51.82.153',
			'169.150.198.91',
			'169.150.220.146',
			'169.150.220.152',
			'169.150.220.156',
			'169.150.220.159',
			'169.150.198.77',
			'169.150.198.77',
			'169.150.198.77',
			'169.150.198.90',
			'169.150.198.91',
			'169.150.198.91',
			'149.102.233.198',
			'149.102.233.180',
			'149.102.233.186',
			'149.102.233.196',
			'149.102.233.206',
			'149.102.233.224',
			'149.102.233.230',
			'149.102.233.233',
			'149.78.184.206',
			'149.78.184.206',
			'149.78.184.206',
			'149.78.184.205',
			'149.78.184.205',
			'149.78.184.205',
			'149.78.184.205',
			'143.137.155.249',
			'131.221.99.11',
			'131.221.99.114',
			'85.10.192.143',
			'20.163.125.12',
			'20.163.125.12',
			'45.180.219.19',
			'45.180.219.19',
			'45.180.219.2',
			'45.180.219.25',
			'45.180.219.25',
			'45.180.219.25',
			'149.102.251.98',
			'38.62.214.130',
			'138.199.58.244',
			'38.174.59.103'
		];
		
		if(in_array($_SERVER["REMOTE_ADDR"], $ips)){
			return false;
		}
		return true;
	}
	
}






?>