<?php
	
    require "/www/class/classVerificacaoIP.php";	
	if(!class_exists("ConnectionPDO")){
		require "/www/db/connect.php";
		require "/www/db/ConnectionPDO.php"; 
	}

class Provedor {
	
     private $name;
	 private $code;
     private $agent;
	 private $state;
	 private $service;
	 private $uf;
	 private $country;
	
	 public function __construct() {
		 $this->service = new VerificacaoIP("findIP");
	 }
	
	 public function coletaProvedor($usuario, $origem, $user){
		 $data = $this->service->verifica(); 
		 $this->name = $data["org"];
		 $this->code = $data["code"];
		 $this->state = empty($data["state"])? $data["state_en"]: $data["state"];
		 $this->uf = isset($data["city"])? $data["uf"]."/".$data["city"]: $data["uf"];
		 $this->country = isset($data["country"])? $data["country"]: '';
		 if($user == 'principal'){
			 return $this->inserirProvedor($usuario, $origem); 
		 }else{
			 return $data;
		 }
	 }
	 
	 public function selectInform($tp, $token){
		 $conexao = ConnectionPDO::getConnection()->getLink();
		 $select = $conexao->prepare("select * from inform_pdv where origem_data = :TP and token = :TOKEN;");
		 $select->bindValue(':TP', $tp);
		 $select->bindValue(':TOKEN', $token);
		 $select->execute();
		 $resultado = $select->fetch(PDO::FETCH_ASSOC);
		 if($resultado != false){
			 return $resultado;
		 }
	     return false;
	 }
	
	 private function inserirProvedor($usuario, $origem){
		 $conexao = ConnectionPDO::getConnection()->getLink();
		 if($origem == 'chave_mestra') {
			 $select = $conexao->prepare("select * from inform_pdv where usuario = :USUARIO;");
			 $select->bindValue(":USUARIO", $usuario);
			 $select->execute();
			 $resultado = $select->fetch(PDO::FETCH_ASSOC);
			 $origemLegenda = 'chave_mestra';
		 }else {
			 $resultado = false;
			 $origemLegenda = 'token';
		 }
		 if($resultado == false){
			 $query = $conexao->prepare("insert into inform_pdv(provedora,agent,usuario,code_provedora,estado,ip,uf_cidade,origem_data,pais)values(:PROVEDORA,:AGENT,:USUARIO,:CODE,:ESTADO,:IP,:UF,:ORIGEM,:PAIS);");
			 $query->bindValue(":PROVEDORA", utf8_decode($this->name));
			 $query->bindValue(":AGENT", $_SERVER["HTTP_USER_AGENT"]);
			 $query->bindValue(":USUARIO", $usuario);
			 $query->bindValue(":CODE", $this->code);
			 $query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
			 $query->bindValue(":ESTADO", utf8_decode($this->state));
			 $query->bindValue(":UF", utf8_decode($this->uf));
			 $query->bindValue(":ORIGEM", $origemLegenda); 
			 $query->bindValue(":PAIS", $this->country); 
			 $query->execute();
			 if($query->rowCount() > 0){
				 return $conexao->lastInsertId(); 
			 }
		 }
		 return  false; 
	 }
}

?>