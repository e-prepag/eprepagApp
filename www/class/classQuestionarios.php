<?php require_once __DIR__ . '/../includes/constantes_url.php'; ?>
<?php
// include do arquivo contendo IPs DEV
require_once DIR_INCS . 'configIP.php';

//Constante contendo a opção adicional para inserir opção diferente das disponibilizadas
$OUTRA_OPCAO = "Outros";

class Questionarios {

/*
ql_id_questionario serial NOT NULL, -- Id de identificação do questionário nesta tabela
ql_data_inicio timestamp with time zone NOT NULL, -- Campo contendo a data de inicio da vigência do questionário.
ql_data_fim timestamp with time zone NOT NULL, -- Campo contendo a data final da vigência do questionário.
ql_tipo smallint NOT NULL, -- Campo contendo o tipo de aviso quando o usuário logar no site....
ql_lista_ids_inclusao text, -- Campo contendo os ug_id dos usuários que devem ser considerados para este questionário.
ql_lista_ids_exclusao text, -- Campo contendo os ug_id dos usuários que NÃO devem ser considerados para este questionário.
ql_ativo smallint NOT NULL, -- Campo contendo a ativação do questionário. Onde 0 = Desativado e 1 = Ativado.
ql_usuario_bko_responsavel character varying(15) NOT NULL, -- Campo contendo o usuário responsável pelo questionário, equivalente ao campo shn_login da tabela shn_login.
ql_imagem_banner character varying(256), -- Campo contendo o banner utilizado no questionário como indicador deste.
ql_texto character varying(256) NOT NULL, -- Campo contendo uma descrição para o questionário que será usado como título deste.
ql_tipo_usuario character varying(1) NOT NULL DEFAULT 'L'::character varying, -- Campo contendo o tipo de usuário que responderá o questionário. Legenda: G = Usuários Gamers; L = Usuários Lan House.
*/  	
	private $ql_tipo_usuario;
    private $ug_id;
	private $resposta;
	private $url;

	//variaveis  de seleção de questionario
	private $id_quest;		//contem o ID do questionario
	private $tipo_quest;	//contem o tipo de questionario. Bloqueio,etc.
	private $banner_quest;	//contem o banner do questionario quando este houver.
	private $texto_quest;	//contem o texto que será utilizado como titulo do questionario.

	function setTipoUsuario($ql_tipo_usuario) {
 		$this->ql_tipo_usuario = $ql_tipo_usuario;
	}
	function getTipoUsuario(){
    	return $this->ql_tipo_usuario;
    }
    
    function setUgId($ug_id) {
 		$this->ug_id = $ug_id;
	}
	function getUgId(){
		return $this->ug_id;
	}
    
	function set_URL($url) {
 		$this->url = $url;
	}
	function get_URL(){
		return $this->url;
	}
    
	function setResposta($resposta) {
 		$this->resposta = $resposta;
	}
	function getResposta(){
    	return $this->resposta;
    }

	function setIdQuest($id_quest) {
 		$this->id_quest = $id_quest;
	}
	function getIdQuest(){
		return $this->id_quest;
	}
    	
	function setTipoQuest($tipo_quest) {
 		$this->tipo_quest = $tipo_quest;
	}
	function getTipoQuest(){
		return $this->tipo_quest;
	}
	function getBloqueiaMenu(){
		return (($this->tipo_quest==1)?true:false);
	}
	function getRedireciona(){
		return (($this->tipo_quest==1 || $this->tipo_quest==2 || $this->tipo_quest==3)?true:false);
	}
	function getBanner(){
		return (($this->tipo_quest==4)?true:false);
	}
    	
	function setBannerQuest($banner_quest) {
 		$this->banner_quest = $banner_quest;
	}
	function getBannerQuest(){
		return $this->banner_quest;
	}
    	
	function setTextoQuest($texto_quest) {
 		$this->texto_quest = $texto_quest;
	}
	function getTextoQuest(){
		return $this->texto_quest;
	}
    	
	function __construct($ug_id = null,$ql_tipo_usuario = null) {
		/*
		Legenda:

		$ql_tipo_usuario: 
		G = Usuários Gamers
		L = Usuários Lan House
		*/
		$this	->	setUgId			($ug_id);
		$this	->	setTipoUsuario	($ql_tipo_usuario);
		$prefixo	=	"" . EPREPAG_URL . "";
                if(checkIP()) {
                    $prefixo = $_SERVER['SERVER_NAME'];
                    }
		//echo "[".$this->getTipoUsuario()."]<br>";
		if($this->getTipoUsuario() == 'G') {
			$this	->	set_URL			("https://".$prefixo."/imagens/gamer/");
		}//end if($this->getTipoUsuario() == 'G')
		else {
			$this	->	set_URL			("https://".$prefixo."/imagens/pdv/");
		}//end else do if($this->getTipoUsuario() == 'G')
	}//end function __construct

	function BuscarQuestionario() {

		$sql = "SELECT * 
				FROM tb_questionarios 
				WHERE ql_ativo = '1'
					AND ql_data_inicio <= NOW() 
					AND (ql_data_fim + interval '1 day')   >= NOW()
					AND ql_tipo_usuario = '".$this	->	getTipoUsuario()."'
				ORDER BY ql_tipo";

//gravaLog_DebugQuestionario("==== QUESTIONARIO SQL: $sql\n");

		//echo $sql.":sql<br>";
			
		$rs_questionarios = SQLexecuteQuery($sql);
		if(!$rs_questionarios) {
			return null;
		} else {
			$i = 0;
			$retorno = array();
			while ($rs_questionarios_row = pg_fetch_array($rs_questionarios)) {
				/*
				if($this->getUgId()==4404 || $this->getUgId()==4907){
					echo "IDS EXCLUSAO: ".strlen($rs_questionarios_row['ql_lista_ids_exclusao'])."<br>";
					echo "IDS INCLUSAO: ".strlen($rs_questionarios_row['ql_lista_ids_inclusao'])."<br>";
				}
				*/
				//Verificando erro no cadastro
				if ((strlen($rs_questionarios_row['ql_lista_ids_exclusao'])>0)&&(strlen($rs_questionarios_row['ql_lista_ids_inclusao'])>0)) {
					echo "Erro no cadastro do questionário de ID [".$rs_questionarios_row['ql_id_questionario']."] existem IDs de usuarios a serem excluido e incluidos no mesmo questionário.<br>";
					die("Coredump!<br>");
				}

				//Verificando se percente a lista de exclusão
				if (strlen($rs_questionarios_row['ql_lista_ids_exclusao'])>0) {
					$vetor_ids_exclusao = explode(",", $rs_questionarios_row['ql_lista_ids_exclusao']);
					if (!in_array($this->getUgId(),$vetor_ids_exclusao)) {
						$retorno[$i]['id']		= $rs_questionarios_row['ql_id_questionario'];							
						$retorno[$i]['tipo']	= $rs_questionarios_row['ql_tipo'];							
						$retorno[$i]['banner']	= $rs_questionarios_row['ql_imagem_banner'];
						$retorno[$i]['texto']	= $rs_questionarios_row['ql_texto'];
						$i++;
					}
				}

				//Verificando se percente a lista de inclusão
				if (strlen($rs_questionarios_row['ql_lista_ids_inclusao'])>0) {
					$vetor_ids_inclusao = explode(",", $rs_questionarios_row['ql_lista_ids_inclusao']);
					if (in_array($this->getUgId(),$vetor_ids_inclusao)) {
						$retorno[$i]['id']		= $rs_questionarios_row['ql_id_questionario'];							
						$retorno[$i]['tipo']	= $rs_questionarios_row['ql_tipo'];							
						$retorno[$i]['banner']	= $rs_questionarios_row['ql_imagem_banner'];
						$retorno[$i]['texto']	= $rs_questionarios_row['ql_texto'];
						$i++;
					}
				}
			}//end while
			return $retorno;
		}//end else if(!$rs_questionarios || pg_num_rows($rs_questionarios) == 0) 
	}//end function BuscarQuestionario()

	function CapturarProximoQuestionario() {
            //print_r($_SERVER);
            //die();
		$retorno['id']		= 0;
		$aux_matriz = $this	->	BuscarQuestionario();
	    //echo "<pre>".print_r($aux_matriz,true)."</pre>";
		for($i=0;$i<count($aux_matriz);$i++) {
			//echo $aux_matriz[$i]['id']."<br>";
			$sql = "
					select count(qru.*) as total 
					from tb_questionarios_respostas_usuarios qru 
						inner join tb_questionarios_perguntas_respostas qpr ON (qpr.qlpr_id = qru.qlpr_id)
						inner join tb_questionarios_perguntas qp ON (qp.qlp_id = qpr.qlp_id)
					where qru.ug_id=".$this->getUgId()." 
						AND qp.ql_id_questionario=".$aux_matriz[$i]['id'];
			$rs_resposta = SQLexecuteQuery($sql);
			//echo $sql.":sql<br>";
			$rs_resposta_row = pg_fetch_array($rs_resposta);
			if ($rs_resposta_row['total']==0) {
				$retorno['id']		= $aux_matriz[$i]['id'];
				$retorno['tipo']	= $aux_matriz[$i]['tipo'];							
				$retorno['banner']	= $aux_matriz[$i]['banner'];
				$retorno['texto']	= $aux_matriz[$i]['texto'];

				$this	->	setIdQuest		($retorno['id']);
				$this	->	setTipoQuest	($retorno['tipo']);
				$this	->	setBannerQuest	($retorno['banner']);
				$this	->	setTextoQuest	($retorno['texto']);

				$i = count($aux_matriz);
			}
		}//end foreach
		return $retorno;
	}//end function CapturarProximoQuestionario()

	function MontarQuestionario() {
		$aux_vetor = $this -> CapturarProximoQuestionario();
		
		if($this->getTipoQuest() <> 0) {
			switch ($this -> getTipoQuest()) {
				case 1:
					$retorno = $this	->	MontarQuestionarioBloqueio();
					break;
				case 2:
					$retorno = $this	->	MontarQuestionarioTodasVezes();
					break;
				case 3:
					$retorno = $this	->	MontarQuestionarioUmVezDia();
					break;
				case 4:
					//echo "[".substr($GLOBALS['_SERVER']['PHP_SELF'],(strrpos($GLOBALS['_SERVER']['PHP_SELF'],'/')+1),16)."]<br>";
					if(substr($GLOBALS['_SERVER']['PHP_SELF'],(strrpos($GLOBALS['_SERVER']['PHP_SELF'],'/')+1),16)=="questionario.php") {
						$retorno = $this	->	MontarQuestionarioTodasVezes();
						//$retorno = $this	->	MontarQuestionarioBloqueio();
					}
					else {
						$retorno = $this	->	MontarQuestionarioBanner();
					}
					break;
				default:
				   $retorno = "Tipo de Questionário Ainda não Implementado<br>";
			}//end switch
		}//end if(!empty($this->getTipoQuest()))
		else {
			$retorno = "";
		}
		return $retorno;
	}//end function MontarQuestionario()

	function MontarQuestionarioBloqueio() {
            $server_url = "" . EPREPAG_URL . "";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
		global $OUTRA_OPCAO;
		$sql = "SELECT * 
				FROM tb_questionarios_perguntas 
				WHERE qlp_ativo = '1'
					AND ql_id_questionario = ".$this -> getIdQuest()."
				ORDER BY qlp_ordem ";
		//echo $sql.":sql<br>";
		$rs_questionario = SQLexecuteQuery($sql);
		
		if(!$rs_questionario || pg_num_rows($rs_questionario) == 0) {
			return "Question&aacute;rio sem perguntas!<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
		} else {
			$retorno = "<div id='popup_questionario' align='left' title='Pesquisa ". $this -> getTextoQuest()."'><script type='text/javascript'>\nfunction Trim(str){return str.replace(/^\\s+|\\s+$/g,'');}\n</script>\n<form method='post' action='".$GLOBALS['_SERVER']['PHP_SELF']."' name='frmPreCadastro' id='frmPreCadastro' onsubmit='return validaform();'>\n<input type='hidden' name='ql_tipo_usuario' id='ql_tipo_usuario' value='".$this -> getTipoUsuario()."' />\n<input type='hidden' name='ug_id' id='ug_id' value='".$this -> getUgId()."' />\n<input type='hidden' name='ql_id_questionario' id='ql_id_questionario' value='".$this -> getIdQuest()."' />\n<img src='".$this -> get_URL()."../../commerce/images/logo_eprepag.jpg' width='130' height='30' border='0' alt='E-Prepag'>\n<br>\n<div style='color:#1f5b89;font-size:15px;font-weight: bold;'>\n";
			if($this -> getTipoUsuario()=='L'){
				$retorno .= "Buscando oferecer uma melhor qualidade no suporte a vendas, por favor, responda o question&aacute;rio abaixo antes de acessar a loja da E-Prepag:\n";
			} else { 
				$retorno .= "Buscando o melhor atendimento, gostar&iacute;amos de ouvir sua opini&atilde;o. Por favor, responda a pesquisa abaixo:";
			}
			$retorno .="<br>\n<br>\n</div>\n<div style='background-color:#e7eef8;font-size:15px;font-weight: bold;'>\n<br>\n</div>\n"; 
			$aux_count = 1;
			while ($rs_questionario_row = pg_fetch_array($rs_questionario)) {
				$retorno .= "<div style='background-color:#e7eef8;font-size:12px;'>".$aux_count.") ".$rs_questionario_row['qlp_texto']."<br> <br>\n</div><div style='background-color:#e7eef8;font-size:12px;'>";
				$sql = "SELECT * 
						FROM tb_questionarios_perguntas_respostas 
						WHERE qlpr_ativo = '1'
							AND qlp_id = ".$rs_questionario_row['qlp_id']."
						ORDER BY qlpr_ordem ";
				//echo $sql.":sql<br>";
				if($rs_questionario_row['qlp_tipo']=='U') {
					$aux_combos[$rs_questionario_row['qlp_texto']] = $rs_questionario_row['qlp_id'];
				}elseif($rs_questionario_row['qlp_tipo']=='M') {
					$aux_multiplos[$rs_questionario_row['qlp_texto']] = $rs_questionario_row['qlp_id'];
				}
				$aux_show_others = $rs_questionario_row['qlp_outros'];
				$rs_perguntas = SQLexecuteQuery($sql);
				if(!$rs_perguntas || pg_num_rows($rs_perguntas) == 0) {
					return "Pergunta ['".$rs_questionario_row['qlp_texto']."'] sem respostas!<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
				} else {
					if($rs_questionario_row['qlp_tipo']=='U') { 	
						$retorno .= "<script type='text/javascript'>\nfunction validaResposta".$rs_questionario_row['qlp_id']."()\n{\n var myForm = document.forms['frmPreCadastro'];\n//var myControls = myForm.elements['qlpr_id[".$rs_questionario_row['qlp_id']."]'];\n var myControls = document.all(\"qlpr_id[".$rs_questionario_row['qlp_id']."]\");\n var teste = false;\n for (var i = 0; i < myControls.length; i++) {\n if(myControls[i].checked) {\n teste = true;\n }\n }\n return teste;\n}\n</script>\n";
						while ($rs_perguntas_row = pg_fetch_array($rs_perguntas)) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."]' type='radio' id='qlpr_id[".$rs_questionario_row['qlp_id']."]' value='".$rs_perguntas_row["qlpr_id"]."'/> ".$rs_perguntas_row['qlpr_descricao']."<br>"; 
						}//end while
						if($aux_show_others==1) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."]' type='radio' id='qlpr_id[".$rs_questionario_row['qlp_id']."]' value='".$OUTRA_OPCAO."'/> ".$OUTRA_OPCAO." <input type='text' name='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' id='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' onFocus='validaResposta".$rs_questionario_row['qlp_id']."();'/>";
						}//end if($aux_show_others==1)
						$retorno .= "<br><br></div>";
					} elseif($rs_questionario_row['qlp_tipo']=='M') {
						$retorno .= "<script type='text/javascript'>\nfunction validaResposta".$rs_questionario_row['qlp_id']."()\n{\n //var myForm = document.frmPreCadastro;\n //var myControls = myForm.elements['qlpr_id[".$rs_questionario_row['qlp_id']."][]'];\n var myControls = document.all(\"qlpr_id[".$rs_questionario_row['qlp_id']."][]\");\n var teste = false;\n for (var i = 0; i < myControls.length; i++) {\n if(myControls[i].checked) {\n teste = true;\n }\n }\n return teste;\n}\n</script>\n";
						while ($rs_perguntas_row = pg_fetch_array($rs_perguntas)) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."][]' type='checkbox' id='qlpr_id[".$rs_questionario_row['qlp_id']."][]' value='".$rs_perguntas_row["qlpr_id"]."'/> ".$rs_perguntas_row['qlpr_descricao']."<br>"; 
						}//end while
						if($aux_show_others==1) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."][]' type='checkbox' id='qlpr_id[".$rs_questionario_row['qlp_id']."][]' value='".$OUTRA_OPCAO."'/> ".$OUTRA_OPCAO." <input type='text' name='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' id='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' onFocus='validaResposta".$rs_questionario_row['qlp_id']."();'/>";
						}//end if($aux_show_others==1)
						$retorno .= "<br><br></div>";
					}
					else {
						$retorno .= "Tipo de pergunta n&atilde;o identificado.<br><br></div>";
					}
				}//end else if(!$rs_questionario || pg_num_rows($rs_questionario) == 0)
				$aux_count++;
			}//end while 
			$retorno .= "<br><center><input type='hidden' name='Submit' id='Submit' value='RESPONDER'><input type='submit' name='resp' id='resp' value='' style='background:url(\"http://" . $server_url . "/prepag2/images/responder.gif\");background-repeat:no-repeat;width:79px;height=24px;'/></center>\n\n";
			$retorno .= "<script type='text/javascript'>\n function validaform() {\n";
			foreach($aux_combos as $key => $value) {
				$retorno .= "if (!validaResposta".$value."())\n {\n alert('Por favor, responda a pergunta\\n[".$key."].');\n return false;\n }\n else {\n //var aux = document.frmPreCadastro.elements['qlpr_id[".$value."]'].length - 1;\n var aux = document.all(\"qlpr_id[".$value."]\").length - 1;\n //if (document.frmPreCadastro.elements['qlpr_id[".$value."]'][aux].checked)\n if (document.all(\"qlpr_id[".$value."]\")[aux].checked)\n {\n //if (document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".value == '')\n if (document.all(\"".$OUTRA_OPCAO."".$value."\").value == '')\n {\n alert('Você selecionou a opção ".strtoupper($OUTRA_OPCAO).".\\nPor favor, para validar sua resposta preencha o campo em branco.\\n[".$key."]');\n //document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".focus();\n document.all(\"".$OUTRA_OPCAO."".$value."\").focus();\n return false;\n}\n }\n } "  ;
			}
			foreach($aux_multiplos as $key => $value) {
				$retorno .= "if (!validaResposta".$value."())\n {\n alert('Por favor, responda a pergunta\\n[".$key."].');\n return false;\n }\n else {\n //var aux = document.frmPreCadastro.elements['qlpr_id[".$value."][]'].length - 1;\n var aux = document.all(\"qlpr_id[".$value."][]\").length - 1;\n //if (document.frmPreCadastro.elements['qlpr_id[".$value."][]'][aux].checked)\n  if (document.all(\"qlpr_id[".$value."][]\")[aux].checked)\n  {\n  "  ;
				if($aux_show_others==1) {
					$retorno .="//if (document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".value == '')\n if (document.all(\"".$OUTRA_OPCAO."".$value."\").value == '')\n {\n alert('Você selecionou a opção ".strtoupper($OUTRA_OPCAO).".\\nPor favor, para validar sua resposta preencha o campo em branco.\\n[".$key."]');\n //document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".focus();\n document.all(\"".$OUTRA_OPCAO."".$value."\").focus();\n return false;\n}\n ";
				}//end if($aux_show_others==1) 
				$retorno .=" }\n }\n";
			}
			// linha javascript qwue mostra os valores da posição do vetor alert(aux+' '+document.frmPreCadastro.elements['qlpr_id[".$value."][]'][aux].value+ ' '+document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".value); \n
			$retorno .= "}\n</script>\n</div><script type='text/javascript' src='https://" . $server_url . "/js/jqueryui/js/jquery-1.7.1.js'></script><script type='text/javascript' src='https://" . $server_url . "/js/jqueryui/js/jquery-ui-1.8.16.custom.min.js'></script><style type='text/css'><!-- @import '../js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css';--></style><script type='text/javascript' src='http://" . $server_url . "/prepag2/js/jqueryui/popup_questionario_bloqueio.js'></script>";
			return $retorno;
			
		}// end else if(!$rs_questionario || pg_num_rows($rs_questionario) == 0)
		
	}//end MontarQuestionarioBloqueio()

	function MontarQuestionarioUmVezDia(){
            $server_url = "" . EPREPAG_URL . "";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
		global $OUTRA_OPCAO;
		$sql = "SELECT * 
				FROM tb_questionarios_perguntas 
				WHERE qlp_ativo = '1'
					AND ql_id_questionario = ".$this -> getIdQuest()."
				ORDER BY qlp_ordem ";
		//echo $sql.":sql<br>";
		$rs_questionario = SQLexecuteQuery($sql);
		
		if(!$rs_questionario || pg_num_rows($rs_questionario) == 0) {
			return "Question&aacute;rio sem perguntas!<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
		} else {
			$retorno = "<div id='popup_questionario' align='left' title='Pesquisa ". $this -> getTextoQuest()."'><script type='text/javascript'>\nfunction Trim(str){return str.replace(/^\\s+|\\s+$/g,'');}\n</script>\n<form method='post' action='".$GLOBALS['_SERVER']['PHP_SELF']."' name='frmPreCadastro' id='frmPreCadastro' onsubmit='return validaform();'>\n<input type='hidden' name='ql_tipo_usuario' id='ql_tipo_usuario' value='".$this -> getTipoUsuario()."' />\n<input type='hidden' name='ug_id' id='ug_id' value='".$this -> getUgId()."' />\n<input type='hidden' name='ql_id_questionario' id='ql_id_questionario' value='".$this -> getIdQuest()."' />\n<br>\n<div style='color:#1f5b89;font-size:15px;font-weight: bold;'>\n";
			if($this -> getTipoUsuario()=='L'){
				$retorno .= "Buscando oferecer uma melhor qualidade no suporte a vendas, por favor, responda o question&aacute;rio abaixo antes de acessar a loja da E-Prepag:\n";
			} else { 
				$retorno .= "Buscando o melhor atendimento, gostar&iacute;amos de ouvir sua opini&atilde;o. Por favor, responda a pesquisa abaixo:";
			}
			$retorno .="<br>\n<br>\n</div>\n<div style='background-color:#e7eef8;font-size:15px;font-weight: bold;'>\n<br>\n</div>\n"; 
			$aux_count = 1;
			while ($rs_questionario_row = pg_fetch_array($rs_questionario)) {
				$retorno .= "<div style='background-color:#e7eef8;font-size:12px;'>".$aux_count.") ".$rs_questionario_row['qlp_texto']."<br> <br>\n</div><div style='background-color:#e7eef8;font-size:10px;'>";
				$sql = "SELECT * 
						FROM tb_questionarios_perguntas_respostas 
						WHERE qlpr_ativo = '1'
							AND qlp_id = ".$rs_questionario_row['qlp_id']."
						ORDER BY qlpr_ordem ";
				//echo $sql.":sql<br>";
				if($rs_questionario_row['qlp_tipo']=='U') {
					$aux_combos[$rs_questionario_row['qlp_texto']] = $rs_questionario_row['qlp_id'];
				}elseif($rs_questionario_row['qlp_tipo']=='M') {
					$aux_multiplos[$rs_questionario_row['qlp_texto']] = $rs_questionario_row['qlp_id'];
				}
				$aux_show_others = $rs_questionario_row['qlp_outros'];
				$rs_perguntas = SQLexecuteQuery($sql);
				if(!$rs_perguntas || pg_num_rows($rs_perguntas) == 0) {
					return "Pergunta ['".$rs_questionario_row['qlp_texto']."'] sem respostas!<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
				} else {
					if($rs_questionario_row['qlp_tipo']=='U') { 	
						$retorno .= "<script type='text/javascript'>\nfunction validaResposta".$rs_questionario_row['qlp_id']."()\n{\n var myForm = document.forms['frmPreCadastro'];\n//var myControls = myForm.elements['qlpr_id[".$rs_questionario_row['qlp_id']."]'];\n var myControls = document.all(\"qlpr_id[".$rs_questionario_row['qlp_id']."]\");\n var teste = false;\n for (var i = 0; i < myControls.length; i++) {\n if(myControls[i].checked) {\n teste = true;\n }\n }\n return teste;\n}\n</script>\n";
						while ($rs_perguntas_row = pg_fetch_array($rs_perguntas)) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."]' type='radio' id='qlpr_id[".$rs_questionario_row['qlp_id']."]' value='".$rs_perguntas_row["qlpr_id"]."'/> ".$rs_perguntas_row['qlpr_descricao']."<br>"; 
						}//end while
						if($aux_show_others==1) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."]' type='radio' id='qlpr_id[".$rs_questionario_row['qlp_id']."]' value='".$OUTRA_OPCAO."'/> ".$OUTRA_OPCAO." <input type='text' name='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' id='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' onFocus='validaResposta".$rs_questionario_row['qlp_id']."();'/>";
						}//end if($aux_show_others==1)
						$retorno .= "<br><br></div>";
					} elseif($rs_questionario_row['qlp_tipo']=='M') {
						$retorno .= "<script type='text/javascript'>\nfunction validaResposta".$rs_questionario_row['qlp_id']."()\n{\n //var myForm = document.frmPreCadastro;\n //var myControls = myForm.elements['qlpr_id[".$rs_questionario_row['qlp_id']."][]'];\n var myControls = document.all(\"qlpr_id[".$rs_questionario_row['qlp_id']."][]\");\n var teste = false;\n for (var i = 0; i < myControls.length; i++) {\n if(myControls[i].checked) {\n teste = true;\n }\n }\n return teste;\n}\n</script>\n";
						while ($rs_perguntas_row = pg_fetch_array($rs_perguntas)) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."][]' type='checkbox' id='qlpr_id[".$rs_questionario_row['qlp_id']."][]' value='".$rs_perguntas_row["qlpr_id"]."'/> ".$rs_perguntas_row['qlpr_descricao']."<br>"; 
						}//end while
						if($aux_show_others==1) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."][]' type='checkbox' id='qlpr_id[".$rs_questionario_row['qlp_id']."][]' value='".$OUTRA_OPCAO."'/> ".$OUTRA_OPCAO." <input type='text' name='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' id='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' onFocus='validaResposta".$rs_questionario_row['qlp_id']."();'/>";
						}//end if($aux_show_others==1)
						$retorno .= "<br><br></div>";
					}
					else {
						$retorno .= "Tipo de pergunta n&atilde;o identificado.<br><br></div>";
					}
				}//end else if(!$rs_questionario || pg_num_rows($rs_questionario) == 0)
				$aux_count++;
			}//end while 
			$retorno .= "<br><center><input type='hidden' name='Submit' id='Submit' value='RESPONDER'><input type='submit' name='resp' id='resp' value='' style='background:url(\"http://" . $server_url . "/prepag2/images/responder.gif\");background-repeat:no-repeat;width:79px;height=24px;'/></center>\n\n";
			$retorno .= "<script type='text/javascript'>\n function validaform() {\n";
			foreach($aux_combos as $key => $value) {
				$retorno .= "if (!validaResposta".$value."())\n {\n alert('Por favor, responda a pergunta\\n[".$key."].');\n return false;\n }\n else {\n //var aux = document.frmPreCadastro.elements['qlpr_id[".$value."]'].length - 1;\n var aux = document.all(\"qlpr_id[".$value."]\").length - 1;\n //if (document.frmPreCadastro.elements['qlpr_id[".$value."]'][aux].checked)\n if (document.all(\"qlpr_id[".$value."]\")[aux].checked)\n {\n //if (document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".value == '')\n if (document.all(\"".$OUTRA_OPCAO."".$value."\").value == '')\n {\n alert('Você selecionou a opção ".strtoupper($OUTRA_OPCAO).".\\nPor favor, para validar sua resposta preencha o campo em branco.\\n[".$key."]');\n //document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".focus();\n document.all(\"".$OUTRA_OPCAO."".$value."\").focus();\n return false;\n}\n }\n } "  ;
			}
			foreach($aux_multiplos as $key => $value) {
				$retorno .= "if (!validaResposta".$value."())\n {\n alert('Por favor, responda a pergunta\\n[".$key."].');\n return false;\n }\n else {\n //var aux = document.frmPreCadastro.elements['qlpr_id[".$value."][]'].length - 1;\n var aux = document.all(\"qlpr_id[".$value."][]\").length - 1;\n //if (document.frmPreCadastro.elements['qlpr_id[".$value."][]'][aux].checked)\n  if (document.all(\"qlpr_id[".$value."][]\")[aux].checked)\n  {\n  "  ;
				if($aux_show_others==1) {
					$retorno .="//if (document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".value == '')\n if (document.all(\"".$OUTRA_OPCAO."".$value."\").value == '')\n {\n alert('Você selecionou a opção ".strtoupper($OUTRA_OPCAO).".\\nPor favor, para validar sua resposta preencha o campo em branco.\\n[".$key."]');\n //document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".focus();\n document.all(\"".$OUTRA_OPCAO."".$value."\").focus();\n return false;\n}\n ";
				}//end if($aux_show_others==1) 
				$retorno .=" }\n }\n";
			}
			// linha javascript qwue mostra os valores da posição do vetor alert(aux+' '+document.frmPreCadastro.elements['qlpr_id[".$value."][]'][aux].value+ ' '+document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".value); \n
			$retorno .= "}\n</script>\n</div><script type='text/javascript' src='http://" . $server_url . "/prepag2/js/jqueryui/js/jquery-1.7.1.js'></script><script type='text/javascript' src='http://" . $server_url . "/prepag2/js/jqueryui/js/jquery-ui-1.8.16.custom.min.js'></script><style type='text/css'><!-- @import '../js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css';--></style><script type='text/javascript' src='http://" . $server_url . "/prepag2/js/jqueryui/popup_questionario.js'></script>";
			return $retorno;
			
		}// end else if(!$rs_questionario || pg_num_rows($rs_questionario) == 0)
		
	}//end MontarQuestionarioUmVezDia()

	function MontarQuestionarioTodasVezes(){
            $server_url = "" . EPREPAG_URL . "";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
		global $OUTRA_OPCAO;
		$sql = "SELECT * 
				FROM tb_questionarios_perguntas 
				WHERE qlp_ativo = '1'
					AND ql_id_questionario = ".$this -> getIdQuest()."
				ORDER BY qlp_ordem ";
		//echo $sql.":sql<br>";
		$rs_questionario = SQLexecuteQuery($sql);
		
		if(!$rs_questionario || pg_num_rows($rs_questionario) == 0) {
			return "Question&aacute;rio sem perguntas!<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
		} else {
			$retorno = "<div id='popup_questionario' align='left' title='Pesquisa ". $this -> getTextoQuest()."'><script type='text/javascript'>\nfunction Trim(str){return str.replace(/^\\s+|\\s+$/g,'');}\n</script>\n<form method='post' action='".$GLOBALS['_SERVER']['PHP_SELF']."' name='frmPreCadastro' id='frmPreCadastro' onsubmit='return validaform();'>\n<input type='hidden' name='ql_tipo_usuario' id='ql_tipo_usuario' value='".$this -> getTipoUsuario()."' />\n<input type='hidden' name='ug_id' id='ug_id' value='".$this -> getUgId()."' />\n<input type='hidden' name='ql_id_questionario' id='ql_id_questionario' value='".$this -> getIdQuest()."' />\n<br>\n<div style='color:#1f5b89;font-size:15px;font-weight: bold;'>\n";
			if($this -> getTipoUsuario()=='L'){
				$retorno .= "Buscando oferecer uma melhor qualidade no suporte a vendas, por favor, responda o question&aacute;rio abaixo antes de acessar a loja da E-Prepag:\n";
			} else { 
				$retorno .= "Buscando o melhor atendimento, gostar&iacute;amos de ouvir sua opini&atilde;o. Por favor, responda a pesquisa abaixo:";
			}
			$retorno .="<br>\n<br>\n</div>\n<div style='background-color:#e7eef8;font-size:15px;font-weight: bold;'>\n<br>\n</div>\n"; 
			$aux_count = 1;
			while ($rs_questionario_row = pg_fetch_array($rs_questionario)) {
				$retorno .= "<div style='background-color:#e7eef8;font-size:12px;'>".$aux_count.") ".$rs_questionario_row['qlp_texto']."<br> <br>\n</div><div style='background-color:#e7eef8;font-size:10px;'>";
				$sql = "SELECT * 
						FROM tb_questionarios_perguntas_respostas 
						WHERE qlpr_ativo = '1'
							AND qlp_id = ".$rs_questionario_row['qlp_id']."
						ORDER BY qlpr_ordem ";
				//echo $sql.":sql<br>";
				if($rs_questionario_row['qlp_tipo']=='U') {
					$aux_combos[$rs_questionario_row['qlp_texto']] = $rs_questionario_row['qlp_id'];
				}elseif($rs_questionario_row['qlp_tipo']=='M') {
					$aux_multiplos[$rs_questionario_row['qlp_texto']] = $rs_questionario_row['qlp_id'];
				}
				$aux_show_others = $rs_questionario_row['qlp_outros'];
				$rs_perguntas = SQLexecuteQuery($sql);
				if(!$rs_perguntas || pg_num_rows($rs_perguntas) == 0) {
					return "Pergunta ['".$rs_questionario_row['qlp_texto']."'] sem respostas!<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
				} else {
					if($rs_questionario_row['qlp_tipo']=='U') { 	
						$retorno .= "<script type='text/javascript'>\nfunction validaResposta".$rs_questionario_row['qlp_id']."()\n{\n var myForm = document.forms['frmPreCadastro'];\n//var myControls = myForm.elements['qlpr_id[".$rs_questionario_row['qlp_id']."]'];\n var myControls = document.all(\"qlpr_id[".$rs_questionario_row['qlp_id']."]\");\n var teste = false;\n for (var i = 0; i < myControls.length; i++) {\n if(myControls[i].checked) {\n teste = true;\n }\n }\n return teste;\n}\n</script>\n";
						while ($rs_perguntas_row = pg_fetch_array($rs_perguntas)) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."]' type='radio' id='qlpr_id[".$rs_questionario_row['qlp_id']."]' value='".$rs_perguntas_row["qlpr_id"]."'/> ".$rs_perguntas_row['qlpr_descricao']."<br>"; 
						}//end while
						if($aux_show_others==1) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."]' type='radio' id='qlpr_id[".$rs_questionario_row['qlp_id']."]' value='".$OUTRA_OPCAO."'/> ".$OUTRA_OPCAO." <input type='text' name='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' id='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' onFocus='validaResposta".$rs_questionario_row['qlp_id']."();'/>";
						}//end if($aux_show_others==1)
						$retorno .= "<br><br></div>";
					} elseif($rs_questionario_row['qlp_tipo']=='M') {
						$retorno .= "<script type='text/javascript'>\nfunction validaResposta".$rs_questionario_row['qlp_id']."()\n{\n //var myForm = document.frmPreCadastro;\n //var myControls = myForm.elements['qlpr_id[".$rs_questionario_row['qlp_id']."][]'];\n var myControls = document.all(\"qlpr_id[".$rs_questionario_row['qlp_id']."][]\");\n var teste = false;\n for (var i = 0; i < myControls.length; i++) {\n if(myControls[i].checked) {\n teste = true;\n }\n }\n return teste;\n}\n</script>\n";
						while ($rs_perguntas_row = pg_fetch_array($rs_perguntas)) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."][]' type='checkbox' id='qlpr_id[".$rs_questionario_row['qlp_id']."][]' value='".$rs_perguntas_row["qlpr_id"]."'/> ".$rs_perguntas_row['qlpr_descricao']."<br>"; 
						}//end while
						if($aux_show_others==1) {
							$retorno .= "<input name='qlpr_id[".$rs_questionario_row['qlp_id']."][]' type='checkbox' id='qlpr_id[".$rs_questionario_row['qlp_id']."][]' value='".$OUTRA_OPCAO."'/> ".$OUTRA_OPCAO." <input type='text' name='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' id='".$OUTRA_OPCAO."".$rs_questionario_row['qlp_id']."' onFocus='validaResposta".$rs_questionario_row['qlp_id']."();'/>";
						}//end if($aux_show_others==1)
						$retorno .= "<br><br></div>";
					}
					else {
						$retorno .= "Tipo de pergunta n&atilde;o identificado.<br><br></div>";
					}
				}//end else if(!$rs_questionario || pg_num_rows($rs_questionario) == 0)
				$aux_count++;
			}//end while 
			$retorno .= "<br><center><input type='hidden' name='Submit' id='Submit' value='RESPONDER'><input type='submit' name='resp' id='resp' value='' style='background:url(\"http://" . $server_url . "/prepag2/images/responder.gif\");background-repeat:no-repeat;width:79px;height=24px;'/></center>\n\n";
			$retorno .= "<script type='text/javascript'>\n function validaform() {\n";
			foreach($aux_combos as $key => $value) {
				$retorno .= "if (!validaResposta".$value."())\n {\n alert('Por favor, responda a pergunta\\n[".$key."].');\n return false;\n }\n else {\n //var aux = document.frmPreCadastro.elements['qlpr_id[".$value."]'].length - 1;\n var aux = document.all(\"qlpr_id[".$value."]\").length - 1;\n //if (document.frmPreCadastro.elements['qlpr_id[".$value."]'][aux].checked)\n if (document.all(\"qlpr_id[".$value."]\")[aux].checked)\n {\n //if (document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".value == '')\n if (document.all(\"".$OUTRA_OPCAO."".$value."\").value == '')\n {\n alert('Você selecionou a opção ".strtoupper($OUTRA_OPCAO).".\\nPor favor, para validar sua resposta preencha o campo em branco.\\n[".$key."]');\n //document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".focus();\n document.all(\"".$OUTRA_OPCAO."".$value."\").focus();\n return false;\n}\n }\n } "  ;
			}
			foreach($aux_multiplos as $key => $value) {
				$retorno .= "if (!validaResposta".$value."())\n {\n alert('Por favor, responda a pergunta\\n[".$key."].');\n return false;\n }\n else {\n //var aux = document.frmPreCadastro.elements['qlpr_id[".$value."][]'].length - 1;\n var aux = document.all(\"qlpr_id[".$value."][]\").length - 1;\n //if (document.frmPreCadastro.elements['qlpr_id[".$value."][]'][aux].checked)\n  if (document.all(\"qlpr_id[".$value."][]\")[aux].checked)\n  {\n "  ;
				if($aux_show_others==1) {
					$retorno .="//if (document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".value == '')\n if (document.all(\"".$OUTRA_OPCAO."".$value."\").value == '')\n {\n alert('Você selecionou a opção ".strtoupper($OUTRA_OPCAO).".\\nPor favor, para validar sua resposta preencha o campo em branco.\\n[".$key."]');\n //document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".focus();\n document.all(\"".$OUTRA_OPCAO."".$value."\").focus();\n return false;\n}\n ";
				}//end if($aux_show_others==1) 
				$retorno .=" }\n }\n";
			}
			// linha javascript qwue mostra os valores da posição do vetor alert(aux+' '+document.frmPreCadastro.elements['qlpr_id[".$value."][]'][aux].value+ ' '+document.frmPreCadastro.".$OUTRA_OPCAO."".$value.".value); \n
			$retorno .= "}\n</script>\n</div><script type='text/javascript' src='http://" . $server_url . "/prepag2/js/jqueryui/js/jquery-1.7.1.js'></script><script type='text/javascript' src='http://" . $server_url . "/prepag2/js/jqueryui/js/jquery-ui-1.8.16.custom.min.js'></script><style type='text/css'><!-- @import '../js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css';--></style><script type='text/javascript' src='http://" . $server_url . "/prepag2/js/jqueryui/popup_questionario.js'></script>";
			return $retorno;
			
		}// end else if(!$rs_questionario || pg_num_rows($rs_questionario) == 0)
		
	}//end MontarQuestionarioTodasVezes()

	function MontarQuestionarioBanner(){
		$retorno = "<a href='".$this -> get_URL()."../questionario.php?ug_id=".$this -> getUgId()."&ql_tipo_usuario=".$this -> getTipoUsuario()."'><img src='".$this -> get_URL()."../../commerce/images/questionario/".$this -> getBannerQuest()."' border='0' alt='Question&aacute;rio E-Prepag' title='Question&aacute;rio E-Prepag'></a>\n"; 
		return $retorno;
	}//end MontarQuestionarioBanner()

	function VerificarRespondeu() {
		$sql = "
				select count(qru.*) as total 
				from tb_questionarios_respostas_usuarios qru 
					inner join tb_questionarios_perguntas_respostas qpr ON (qpr.qlpr_id = qru.qlpr_id)
					inner join tb_questionarios_perguntas qp ON (qp.qlp_id = qpr.qlp_id)
				where qru.ug_id=".$this->getUgId()." 
					AND qp.ql_id_questionario=".$this	->	getIdQuest	();
		$rs_resposta = SQLexecuteQuery($sql);
		//echo $sql.":sql<br>";
		$rs_resposta_row = pg_fetch_array($rs_resposta);
		$retorno = $rs_resposta_row['total'];
		return $retorno;
	}//end function VerificarRespondeu()

	function RetornaIDseTiposPerguntas($ql_id_questionario){
		$this	->	setIdQuest	($ql_id_questionario);
		$retorno[0]['id']	= ""; 
		$retorno[0]['tipo']	= ""; 
		if ($this -> VerificarRespondeu() == 0){
			$sql = "SELECT * 
					FROM tb_questionarios_perguntas 
					WHERE qlp_ativo = '1'
						AND ql_id_questionario = ".$this -> getIdQuest()."
					ORDER BY qlp_ordem ";
			//echo $sql.":sql<br>";
			$rs_questionario = SQLexecuteQuery($sql);
			if(!$rs_questionario || pg_num_rows($rs_questionario) == 0) {
				return "Question&aacute;rio sem perguntas!<br><br>\n<input type='button' name='Submit' value='FECHAR' onClick='javascript:window.close();'/>\n";
			} else {
				$i = 0;
				while ($rs_questionario_row = pg_fetch_array($rs_questionario)) {
					$retorno[$i]['id']		= $rs_questionario_row['qlp_id'];
					$retorno[$i]['tipo']	= $rs_questionario_row['qlp_tipo'];
					$i++;
				}//end while 
			}// end else if(!$rs_questionario || pg_num_rows($rs_questionario) == 0)
		}//end 
		return $retorno;
	}//end RetornaIDsPerguntas

	function InsereNovaResposta($qlp_id,$resposta){
		$retorno = 0;
		$sql ="insert into tb_questionarios_perguntas_respostas (qlp_id,qlpr_descricao,qlpr_ativo) values ($qlp_id,'$resposta',0) ";//".utf8_decode($resposta)."
		$rs_questionario_repostas = SQLexecuteQuery($sql);
		if(!$rs_questionario_repostas) {
			$retorno = "Erro ao salvar informa&ccedil;&otilde;es da pergunta. ($sql)<br>";
		}
		else {
			$sql ="select qlpr_id from tb_questionarios_perguntas_respostas where qlpr_descricao='$resposta' and qlp_id=$qlp_id";
			$rs_questionario_select_repostas = SQLexecuteQuery($sql);
			$rs_questionario_select_repostas_row = pg_fetch_array($rs_questionario_select_repostas);
			$retorno = $rs_questionario_select_repostas_row['qlpr_id'];
		}
		return $retorno;
	}//end InsereNovaResposta

	function InsereRespostaUsuario($qlpr_id){
		$retorno = false;
		$sql ="insert into tb_questionarios_respostas_usuarios (qlpr_id,ug_id,qlpru_data_inclusao,qlpru_tipo_usuario) values ($qlpr_id,".$this->getUgId().",NOW(),'".$this -> getTipoUsuario()."') ";
		$rs_questionario_repostas = SQLexecuteQuery($sql);
		if(!$rs_questionario_repostas) {
			$retorno = "Erro ao salvar informa&ccedil;&otilde;es da pergunta. ($sql)<br>";
		}
		else $retorno = true;
		return $retorno;
	}//end InsereRespostaUsuario($qlpr_id)

}//end class

// é o único método chamado para mostrar as promoções disponíveis
//	$ug_email		- email do usuário, fará parte do token para poder identificar o usuário quando voltar ao site
//	$ug_id			- id do usuário (pode ser null), fará parte do token para poder identificar o usuário quando voltar ao site
//	$s_opr_codigo	- lista CSV com os opr_codigo para os quais devem ser procuradas as operadoras cadastradas nas promoções
//	$vg_id			- id do venda, fará parte do token para poder identificar a venda quando voltar ao site
/*
function getQuestionarioCorrente($ug_email, $ug_id, $s_opr_codigo, $vg_id = null) {
		$promo_msg = "";
$msg_debug = "========= PROMOÇÃO (ug_email: '$ug_email', ug_id: $ug_id, s_opr_codigo: '$s_opr_codigo')[".date("Y-m-d H:i:s")."]\n";
	try {

		$token = new questionarios();
		$teste_promo = $token->BuscarQuestionario($ug_email, $ug_id, $s_opr_codigo, $vg_id);
		if(count($teste_promo)>0) {
$msg_debug .= print_r($teste_promo, true)."\n";
			foreach($teste_promo as $key => $val) {
				$promo_msg .= $val."\n";
			}
		}
	} catch (Exception $e) {
		echo "Error(7) enviando email de promoção [".date("Y-m-d H:i:s")."]: ".$e->getMessage()."\n";
$msg_debug .= "  Error(7) enviando email de promoção [".date("Y-m-d H:i:s")."]: ".$e->getMessage()."\n";
	}

$msg_debug .= "$promo_msg\n";
$msg_debug .= "  Fecha Promoção\n";
gravaLog_DebugQuestionario($msg_debug);

	return $promo_msg;

}
*/

function gravaLog_DebugQuestionario($mensagem){
	//Arquivo
	$file = "C:/Sites/E-Prepag/backoffice/offweb/tarefas/log/log_Debug_Questionario.txt";

	//Mensagem
	$mensagem = "\n".date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 
}

?>