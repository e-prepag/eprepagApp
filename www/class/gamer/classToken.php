<?php
class Token {

	private $email;
	private $ug_id;
	private $vg_id;
	private $promo_id;

	// variavel que recebera o objeto de cryptografia
	private $aes;
		
	function setEmail($email) {
 		$this->email = $email;
	}
	function getEmail(){
    	return $this->email;
    }
    
    function setUgId($ug_id) {
 		$this->ug_id = $ug_id;
	}
	function getUgId(){
    	return $this->ug_id;
    }
    
    function setVgId($vg_id) {
 		$this->vg_id = $vg_id;
	}
	function getVgId(){
    	return $this->vg_id;
    }
    
	function setPromoId($promo_id) {
 		$this->promo_id = $promo_id;
	}
	function getPromoId(){
    	return $this->promo_id;
    }

	function __construct() {
		//instanciando a classe de cryptografia
		$chave256bits	=	new Chave();
		//$this	->	aes		=	new AES($chave256bits->retornaChave());
		//substituir pela linha abaixo
		$this	->	aes		=	new AES($chave256bits->retornaChavePromo());
	}

	function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	function base64url_decode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	} 

	function GerarToken($email, $ug_id, $promo_id, $vg_id) {
		$this	->	setEmail	($email);
	    $this	->	setUgId		($ug_id);
	    $this	->	setVgId		($vg_id);
	    $this	->	setPromoId	($promo_id);
		$aux_token = $this->getUniqueCode().";".$this->getEmail().";".$this->getUgId().";".$this->getPromoId().";".$this->getVgId().";".$this->getUniqueCode();
		//$aux_token = $this->getEmail().";".$this->getUgId().";".$this->getPromoId();
		$aux_token = $this -> base64url_encode($this->aes->encrypt($aux_token));
		return $aux_token;
	}

	function RecuperarToken($token) {
		/*
		Sera retornado um vetor com valores na ordem de sua formacao, ou seja:
		[0] = E-Mail,
		[1] = Ug_id,
		[2] = Id_Promocao,
		[3] = Vg_id,
		*/
		$aux_token = $this->aes->decrypt($this -> base64url_decode($token));
		$aux_token = explode(';',$aux_token);
		for($i=1;$i<count($aux_token);$i++) {
			if($i!=(count($aux_token)-1))
				$return[] = $aux_token[$i];
		}
		return $return;
	}

	function getUniqueCode() {	
		$code = md5(uniqid(rand(), true));
		return substr($code, 0, 5);
	}
}
?>