<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
require_once $raiz_do_projeto . "class/gamer/classToken.php";

class Promocoes {
	
	private $promo_id;
    private $email;
	private $ug_id;
	private $vg_id;
	private $opr_codigo;
	private $resposta;
	private $token;
	private $url;
    
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
		if(empty($this->ug_id)) {
			return 'NULL';
		}
    	else {
			return $this->ug_id;
		}
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

	function setOprCodigo($opr_codigo) {
 		$this->opr_codigo = $opr_codigo;
	}
	function getOprCodigo(){
    	return $this->opr_codigo;
    }

	function setResposta($resposta) {
 		$this->resposta = $resposta;
	}
	function getResposta(){
    	return $this->resposta;
    }

	function __construct() {
		$this->url = "" . EPREPAG_URL . "";
	}

	function BuscarPromocao($email, $ug_id, $opr_codigo, $vg_id=null) {

		$this	->	setEmail	($email);
	    $this	->	setUgId		($ug_id);
	    $this	->	setOprCodigo($opr_codigo);
		$this	->	setVgId		($vg_id);
	    $caminho = "http://".$this->url."/prepag2/commerce/images/promocoes/";
		$sql = "SELECT * 
				FROM promocoes 
				WHERE opr_codigo in (".$this->getOprCodigo().")
					AND promo_ativo = '1'
					AND promo_data_inicio <= NOW() 
					AND (promo_data_fim + interval '1 day')   >= NOW()";
//gravaLog_DebugPromocao("==== PROMOCAO SQL: $sql\n");


		$rs_promocoes = SQLexecuteQuery($sql);
		if(!$rs_promocoes || pg_num_rows($rs_promocoes) == 0) {
			return null;
		} else {
			while ($rs_promocoes_row = pg_fetch_array($rs_promocoes)) {
				$this -> setPromoId($rs_promocoes_row['promo_id']);
				//instanciando o Token
				$this -> token = new Token();
				$cod_token = $this -> token -> GerarToken($this->getEmail(),$this->getUgId(),$this->getPromoId(),$this->getVgId());
				
				$retorno[] = "<a href='http://".$this->url."/prepag2/commerce/promocao.php?token=".$cod_token."' target='_blank' title = '".strip_tags($rs_promocoes_row['promo_label_banner'])."' >".$rs_promocoes_row['promo_label_banner'].".<br><img src='".$caminho.$rs_promocoes_row['promo_banner']."' border='0' alt='".strip_tags($rs_promocoes_row['promo_label_banner'])."' title='".strip_tags($rs_promocoes_row['promo_label_banner'])."'></a><br>";
			}
			return $retorno;
		}
	}

	function MontarPromocao($token) {

		$caminho = "http://".$this->url."/prepag2/commerce/images/promocoes/";
		//instanciando o Token
		$this -> token = new Token();
		$aux_promocao = $this -> token -> RecuperarToken($token);
		
		$this	->	setEmail	($aux_promocao[0]);
	    $this	->	setUgId		($aux_promocao[1]);
	    $this	->	setPromoId	($aux_promocao[2]);
	    $this	->	setVgId		($aux_promocao[3]);
		//echo "<pre>".print_r($aux_promocao)."</pre>";

gravaLog_DebugPromocao("Confere token em MontarPromocao()\n    Email: ".$this->getEmail().", ID: ".$this->getUgId().", PromoID: ".$this->getPromoId().", VG_ID: ".$this->getVgId()."\n");
		
		
		$sql = "SELECT * 
				FROM promocoes 
				WHERE promo_id = ".$this	->	getPromoId()."
					AND promo_ativo = '1'
					AND promo_data_inicio <= NOW() 
					AND (promo_data_fim + interval '1 day')   >= NOW()";
		$rs_promocao = SQLexecuteQuery($sql);
		//echo $sql.":sql<br>";
		
		if(!$rs_promocao || pg_num_rows($rs_promocao) == 0) {
			return "Promo&ccedil;&atilde;o expirou!<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
		} else {
			$rs_promocao_row = pg_fetch_array($rs_promocao);
			//tirando do form para naun fazer validação
			//onsubmit='return validaResposta();'
			if (empty($rs_promocao_row['promo_link_redir'])) {
				//die("AKI!!!");
				if ($this	->	 VerificarRespondeu()) {
					$retorno = "<script type='text/javascript'>\nfunction Trim(str){return str.replace(/^\\s+|\\s+$/g,'');}\nfunction validaResposta()\n{\n if(Trim(document.frmPreCadastro.promo_resposta.value.toUpperCase()) != '".strtoupper(utf8_decode($rs_promocao_row['promo_resposta']))."')\n{\n alert('Resposta Incorreta!\\n Tente Novamente!');\n return false;\n}\nelse return true;\n}\n</script>\n<form method='post' action='".$GLOBALS['_SERVER']['PHP_SELF']."' name='frmPreCadastro' id='frmPreCadastro' >\n<br><img src='".$caminho.$rs_promocao_row['promo_banner_resposta']."' border='0' alt='Promo&ccedil;&atilde;o E-PREPAG'>\n<br><br>".$rs_promocao_row['promo_pergunta']."<br> <br>\nResposta: <input name='promo_r_resposta' type='text' id='promo_r_resposta' size='40' maxlength='40' value='' /><br><br>\n<input type='submit' name='Submit' value='PARTICIPAR'/><br><br>\n<input type='hidden' name='promo_r_email' id='promo_r_email' value='".$this	->	getEmail()."' />\n<input type='hidden' name='ug_id' id='ug_id' value='".$this	->	getUgId()."' />\n<input type='hidden' name='vg_id' id='vg_id' value='".$this	->	getVgId()."' />\n<input type='hidden' name='promo_id' id='promo_id' value='".$this	->	getPromoId	()."' />\n<br><b>REGULAMENTO:</b><br><br>\n</form>\n".$rs_promocao_row['promo_descricao']."<br><br>\n<br>\n";
				}
				else return "Voc&ecirc; j&aacute; est&aacute; participando deste concurso.<br>\nPor favor, aguarde os resultados e a pr&oacute;xima promo&ccedil;&atilde;o.<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
			}
			else {
				$this	->	setResposta	('Redirecionado');
				$sql = "INSERT INTO promocoes_resposta (
											promo_r_email, 
											ug_id,
											promo_id,
											promo_r_data, 
											promo_r_resposta
										) 
								VALUES (
										'".$this	->	getEmail()."', 
										".$this		->	getUgId().", 
										".$this		->	getPromoId().", 
										NOW(), 
										'".$this	->	getResposta	()."');";
				$rs_promocoes = SQLexecuteQuery($sql);
				$retorno = "<script type='text/javascript'>\nwindow.location='".$rs_promocao_row['promo_link_redir']."'\n</script>\n";
			}
			return $retorno;
		}
		
	}
	
	function ResponderPromocao($promo_r_email, $ug_id, $promo_id, $promo_r_resposta, $vg_id) {
		$this	->	setEmail	($promo_r_email);
	    $this	->	setUgId		($ug_id);
	    $this	->	setPromoId	($promo_id);
		$this	->	setResposta	($promo_r_resposta);
		$this	->	setVgId		($vg_id);
	    if ($this	->	 VerificarRespondeu()) {
			$sql = "INSERT INTO promocoes_resposta (
										promo_r_email, 
										ug_id,
										promo_id,
										promo_r_data, 
										promo_r_resposta,
										vg_id
									) 
							VALUES (
									'".$this	->	getEmail()."', 
									".$this		->	getUgId().", 
									".$this		->	getPromoId().", 
									NOW(), 
									'".$this	->	getResposta	()."', 
									".$this		->	getVgId().");";
			/*
			if ($this	->	getEmail() == "WAGNER@E-PREPAG.COM.BR") {
				echo $sql."<br>";
			}
			*/
			$rs_promocoes = SQLexecuteQuery($sql);
			if(!$rs_promocoes) {
				return "Erro ao salvar informa&ccedil;&otilde;es da Promo&ccedil;&atilde;o.<br>Tente mais tarde<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
			}
			else {
				return "<br>Parab&eacute;ns!<br>\n Voc&ecirc; est&aacute; participando da promo&ccedil;&atilde;o. Aguarde o resultado, no site da E-Prepag ou nas <br>\nRedes Sociais: <a href='http://www.twitter.com/eprepag'>Twitter</a>, <a href='http://www.facebook.com/eprepag'>Facebook</a> e <a href='http://www.orkut.com.br/Main#Profile?uid=12230165291514931131'>Orkut</a> (links).<br>\n<br>\n<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
			}
		}
		else return "<br>Voc&ecirc; j&aacute; est&aacute; participando deste concurso.<br>\nPor favor, aguarde os resultados e a pr&oacute;xima promo&ccedil;&atilde;o.<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
	}

	function VerificarRespondeu() {
		$sql = "SELECT * 
				FROM promocoes_resposta 
				WHERE promo_r_email	= '".$this	->	getEmail()."'
					AND promo_id	= ".$this	->	getPromoId()."
					AND vg_id = ".$this	->	getVgId();
		if ($this	->	getUgId() == 'NULL') {
			$sql .= "	AND ug_id		IS ".$this	->	getUgId();
		}
		else {
			$sql .= "	AND ug_id		= ".$this	->	getUgId();
		}
		/*
		if ($this	->	getEmail() == "WAGNER@E-PREPAG.COM.BR") {
			echo $sql."<br>";
		}
		*/
		$rs_promocoes = SQLexecuteQuery($sql);
		if(!$rs_promocoes) {
			return false;
		} else if(pg_num_rows($rs_promocoes) == 0) {
			return true;
		}
		else {
			return false;
		}
	}

}

// é o único método chamado para mostrar as promoções disponíveis
//	$ug_email		- email do usuário, fará parte do token para poder identificar o usuário quando voltar ao site
//	$ug_id			- id do usuário (pode ser null), fará parte do token para poder identificar o usuário quando voltar ao site
//	$s_opr_codigo	- lista CSV com os opr_codigo para os quais devem ser procuradas as operadoras cadastradas nas promoções
//	$vg_id			- id do venda, fará parte do token para poder identificar a venda quando voltar ao site
function getPromocaoCorrente($ug_email, $ug_id, $s_opr_codigo, $vg_id = null) {
	$promo_msg = "";
	$msg_debug = "========= PROMOÇÃO (ug_email: '$ug_email', ug_id: $ug_id, s_opr_codigo: '$s_opr_codigo')[".date("Y-m-d H:i:s")."]\n";
	try {

		$token = new Promocoes();
		$teste_promo = $token->BuscarPromocao($ug_email, $ug_id, $s_opr_codigo, $vg_id);
		if(count($teste_promo)>0) {
			$msg_debug .= print_r($teste_promo, true)."\n";
			foreach($teste_promo as $key => $val) {
				$promo_msg .= $val."\n";
				$msg_debug .= "$promo_msg\n";
			}
		} else {
			$msg_debug .= "   --- Sem promoção\n";
		}
	} catch (Exception $e) {
		echo "Error(7) enviando email de promoção [".date("Y-m-d H:i:s")."]: ".$e->getMessage()."\n";
		$msg_debug .= "  Error(7) enviando email de promoção [".date("Y-m-d H:i:s")."]: ".$e->getMessage()."\n";
	}

	$msg_debug .= "  Fecha Promoção\n";
	gravaLog_DebugPromocao($msg_debug);

	return $promo_msg;

}

function gravaLog_DebugPromocao($mensagem){
    
        global $raiz_do_projeto;
        
	//Arquivo
	$file = $raiz_do_projeto . "log/log_Debug.txt";

	//Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 
}

?>