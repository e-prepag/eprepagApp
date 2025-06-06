<?php require_once __DIR__ . '/../includes/constantes_url.php'; ?>
<?php

// include do arquivo contendo IPs DEV
require_once RAIZ_DO_PROJETO . 'includes/configIP.php';

class BannerDrawShadow {

/*
tb_banner_drop_shadow

bds_id_banner serial NOT NULL, -- Id de identificação do banner nesta tabela
bds_data_inicio timestamp with time zone NOT NULL, -- Campo contendo a data de inicio da vigência do banner.
bds_data_fim timestamp with time zone NOT NULL, -- Campo contendo a data final da vigência do banner.
bds_tipo smallint NOT NULL, -- Campo contendo o tipo de banner quando o usuário logar no site....
bds_lista_ids_inclusao text, -- Campo contendo os ug_id dos usuários que devem ser considerados para este banner.
bds_lista_ids_exclusao text, -- Campo contendo os ug_id dos usuários que NÃO devem ser considerados para este banner.
bds_ativo smallint NOT NULL, -- Campo contendo a ativação do banner. Onde 0 = Desativado e 1 = Ativado.
bds_usuario_bko_responsavel character varying(15) NOT NULL, -- Campo contendo o usuário responsável pelo banner, equivalente ao campo shn_login da tabela shn_login.
bds_imagem_banner character varying(256) NOT NULL, -- Campo contendo o banner utilizado no drop shadow.
bds_link character varying(256), -- Campo contendo o link do banner quando clicado.
bds_tipo_usuario character varying(1) NOT NULL DEFAULT 'L'::character varying, -- Campo contendo o tipo de usuário que visualizará o banner. Legenda: G = Usuários Gamers; L = Usuários Lan House.
bds_texto character varying(256) NOT NULL, -- Campo contendo uma descrição para o banner que será usado como título deste.




*/  	
	private $bds_tipo_usuario;
    private $ug_id;
	private $resposta;
	private $url;
	
	//variaveis  de seleção de Banner Drop Shadow
	private $id_bds;		//contem o ID do Banner Drop Shadow
	private $tipo_bds;		//contem o tipo de Banner Drop Shadow. Bloqueio,etc.
	private $banner_bds;	//contem o banner do Banner Drop Shadow quando este houver.
	private $texto_bds;		//contem o texto que será utilizado como titulo do banner.
	private $bds_link;		//contem o link do banner

	function setTipoUsuario($bds_tipo_usuario) {
 		$this->bds_tipo_usuario = $bds_tipo_usuario;
	}
	function getTipoUsuario(){
    	return $this->bds_tipo_usuario;
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

	function setIdBanner($id_bds) {
 		$this->id_bds = $id_bds;
	}
	function getIdBanner(){
		return $this->id_bds;
	}
    	
	function setTipoBanner($tipo_bds) {
 		$this->tipo_bds = $tipo_bds;
	}
	function getTipoBanner(){
		return $this->tipo_bds;
	}

	function setBannerBanner($banner_bds) {
 		$this->banner_bds = $banner_bds;
	}
	function getBannerBanner(){
		return $this->banner_bds;
	}
    	
	function setTextoBanner($texto_bds) {
 		$this->texto_bds = $texto_bds;
	}
	function getTextoBanner(){
		return $this->texto_bds;
	}
    	
	function setLinkBanner($bds_link) {
 		$this->bds_link = $bds_link;
	}
	function getLinkBanner(){
		return $this->bds_link;
	}
    	
	function __construct($ug_id = null,$bds_tipo_usuario = null) {
            $server_url = "" . EPREPAG_URL . "";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
		/* Legenda:
		$bds_tipo_usuario: 
		G = Usuários Gamers
		L = Usuários Lan House
		*/
		$this->setUgId			($ug_id);
		$this->setTipoUsuario	($bds_tipo_usuario);
		$prefixo=$server_url;
                
                $http = ($_SERVER['HTTPS']=="on") ? "https://" : "http://" ;
                
		$this->set_URL			($http.$prefixo."/imagens/banners/");
		
	}//end function __construct

	function BuscarBanner() {

		$sql = "SELECT * 
				FROM tb_banner_drop_shadow 
				WHERE bds_ativo = '1'
					AND bds_data_inicio <= NOW() 
					AND (bds_data_fim + interval '1 day')   >= NOW()
					AND bds_tipo_usuario = '".$this	->	getTipoUsuario()."'
					AND bds_imagem_banner IS NOT NULL
				ORDER BY bds_id_banner";

//gravaLog_DebugBanner("==== Banner SQL: $sql\n");

		//echo $sql.":sql<br>";
			
		$rs_banners = SQLexecuteQuery($sql);
		if(!$rs_banners) {
			return null;
		} else {
			$i = 0;
			$retorno = array();
			while ($rs_banners_row = pg_fetch_array($rs_banners)) {
				/*
				if($this->getUgId()==4404 || $this->getUgId()==4907){
					echo "IDS EXCLUSAO: ".strlen($rs_banners_row['bds_lista_ids_exclusao'])."<br>";
					echo "IDS INCLUSAO: ".strlen($rs_banners_row['bds_lista_ids_inclusao'])."<br>";
				}
				*/
				//Verificando erro no cadastro
				if ((strlen($rs_banners_row['bds_lista_ids_exclusao'])>0)&&(strlen($rs_banners_row['bds_lista_ids_inclusao'])>0)) {
					echo "Erro no cadastro do Banner Drop Shadow de ID [".$rs_banners_row['bds_id_banner']."] existem IDs de usuarios a serem excluido e incluidos para o mesmo Banner.<br>";
					die("Coredump!<br>");
				}

				//Verificando se percente a lista de exclusão
				if (strlen($rs_banners_row['bds_lista_ids_exclusao'])>0) {
					$vetor_ids_exclusao = explode(",", $rs_banners_row['bds_lista_ids_exclusao']);
					if (!in_array($this->getUgId(),$vetor_ids_exclusao)) {
						$retorno[$i]['id']		= $rs_banners_row['bds_id_banner'];							
						$retorno[$i]['tipo']	= $rs_banners_row['bds_tipo'];							
						$retorno[$i]['banner']	= $rs_banners_row['bds_imagem_banner'];
						$retorno[$i]['texto']	= $rs_banners_row['bds_texto'];
						$retorno[$i]['link']	= $rs_banners_row['bds_link'];
						$i++;
					}
				}

				//Verificando se percente a lista de inclusão
				if (strlen($rs_banners_row['bds_lista_ids_inclusao'])>0) {
					$vetor_ids_inclusao = explode(",", $rs_banners_row['bds_lista_ids_inclusao']);
					if (in_array($this->getUgId(),$vetor_ids_inclusao)) {
						$retorno[$i]['id']		= $rs_banners_row['bds_id_banner'];							
						$retorno[$i]['tipo']	= $rs_banners_row['bds_tipo'];							
						$retorno[$i]['banner']	= $rs_banners_row['bds_imagem_banner'];
						$retorno[$i]['texto']	= $rs_banners_row['bds_texto'];
						$retorno[$i]['link']	= $rs_banners_row['bds_link'];
						$i++;
					}
				}
			}//end while
			return $retorno;
		}//end else if(!$rs_banners || pg_num_rows($rs_banners) == 0) 
	}//end function BuscarBanner()

	function CapturarProximoBanner() {
		$retorno['id']		= 0;
		$aux_matriz = $this->BuscarBanner();
	    //echo "<pre>".print_r($aux_matriz,true)."</pre>";
		for($i=0;$i<count($aux_matriz);$i++) {
			//echo $aux_matriz[$i]['id']."<br>";
			$sql = "select count(bdsc.*) as total 
					from tb_banner_drop_shadow_clicks bdsc 
					where bdsc.ug_id=".$this->getUgId()." 
						AND bdsc.bds_id_banner=".$aux_matriz[$i]['id'];
			$rs_resposta = SQLexecuteQuery($sql);
			//echo $sql.":sql<br>";
			$rs_resposta_row = pg_fetch_array($rs_resposta);
			if ($rs_resposta_row['total']==0) {
				$retorno['id']		= $aux_matriz[$i]['id'];
				$retorno['tipo']	= $aux_matriz[$i]['tipo'];							
				$retorno['banner']	= $aux_matriz[$i]['banner'];
				$retorno['texto']	= $aux_matriz[$i]['texto'];
				$retorno['link']	= $aux_matriz[$i]['link'];

				$this->setIdBanner		($retorno['id']);
				$this->setTipoBanner	($retorno['tipo']);
				$this->setBannerBanner	($retorno['banner']);
				$this->setTextoBanner	($retorno['texto']);
				$this->setLinkBanner	($retorno['link']);

				$i = count($aux_matriz);
			}
		}//end foreach
		return $retorno;
	}//end function CapturarProximoBanner()

	function MontarBanner() {
		$aux_vetor = $this -> CapturarProximoBanner();
		
		if($this->getTipoBanner() <> 0) {
			switch ($this->getTipoBanner()) {
				case 1:
					$retorno = $this->MontarBannerBloqueio();
					break;
				case 2:
					$retorno = $this->MontarBannerTodasVezes();
					break;
				default:
				   $retorno = "Tipo de Banner Drop Shadow Ainda não Implementado<br>";
			}//end switch
			if($this->getTipoBanner()<>1) {
				$this->InsereVisualizacao($this->getIdBanner());
			}//end if($this->getTipoBanner()<>1) 
		}//end if($this->getTipoBanner() <> 0)
		else {
			$retorno = "";
		}
		return $retorno;
	}//end function MontarBanner()

	function MontarBannerBloqueio() {
            $server_url = "" . EPREPAG_URL . "";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
                
                $http = ($_SERVER['HTTPS']=="on") ? "https://" : "http://" ;
                $server_url = $http.$server_url;
                
		$retorno = "<div id='popup_banner' title='". $this->getTextoBanner()."' align='center'>\n
						<img id='imagem_banner' src='".$this->get_URL().$this->getBannerBanner()."' style='cursor:pointer;cursor:hand;' alt='Clique para mais informações' title='Clique para mais informações' onClick='javascript: ClickBanner();'>\n
					</div>\n
					<script type='text/javascript' src='" .  $server_url . "/js/jqueryui/js/jquery-1.7.1.js'></script>\n
					<script type='text/javascript' src='" .  $server_url . "/js/jqueryui/js/jquery-ui-1.8.16.custom.min.js'></script>\n
					<style type='text/css'><!-- @import '" .  $server_url . "/js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css';--></style>\n
					<script type='text/javascript'>
					heavyImage = new Image(); 
					heavyImage.src = '".$this->get_URL().$this->getBannerBanner()."';
					function ClickBanner(){
							window.open('".$this->getLinkBanner()."','_blank');
							$(document).ready(function(){
								$.ajax({
									type: 'POST',
									url: '" .  $server_url . "/ajax/ajaxClickBanner.php',
									data: 'bds_tipo_usuario=".$this->getTipoUsuario()."&ug_id=".$this->getUgId()."&bds_id_banner=".$this->getIdBanner()."',
									success: function(html){
										$('#popup_banner').dialog('close');
									},
									error: function() {
										alert('ERRO');
									}
								});
							});
					}
					</script>
					<script type='text/javascript' src='" .  $server_url . "/js/jqueryui/popup_banner_bloqueio.js'></script>";
		return $retorno;
		
		
	}//end MontarBannerBloqueio()

	function MontarBannerTodasVezes(){
            $server_url = "" . EPREPAG_URL . "";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
                
                $http = ($_SERVER['HTTPS']=="on") ? "https://" : "http://" ;
                $server_url = $http.$server_url;

		$retorno = "<div id='popup_banner' title='". $this->getTextoBanner()."' align='center'>\n
						<img id='imagem_banner' src='".$this->get_URL().$this->getBannerBanner()."' style='cursor:pointer;cursor:hand;' alt='Clique para mais informações' title='Clique para mais informações' onClick='javascript: ClickBanner();'>\n
					</div>\n
					<script type='text/javascript' src='" .  $server_url . "/js/jqueryui/js/jquery-1.7.1.js'></script>\n
					<script type='text/javascript' src='" .  $server_url . "/js/jqueryui/js/jquery-ui-1.8.16.custom.min.js'></script>\n
					<style type='text/css'><!-- @import '" .  $server_url . "/js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css';--></style>\n
					<script type='text/javascript'>
					heavyImage = new Image(); 
					heavyImage.src = '".$this->get_URL().$this->getBannerBanner()."';
					function ClickBanner(){
							window.open('".$this->getLinkBanner()."','_blank');
							$(document).ready(function(){
								$.ajax({
									type: 'POST',
									url: '" .  $server_url . "/ajax/ajaxClickBanner.php',
									data: 'bds_tipo_usuario=".$this->getTipoUsuario()."&ug_id=".$this->getUgId()."&bds_id_banner=".$this->getIdBanner()."',
									success: function(html){
										$('#popup_banner').dialog('close');
									},
									error: function() {
										alert('ERRO');
									}
								});
							});
					}
					</script>
					<script type='text/javascript' src='" .  $server_url . "/js/jqueryui/popup_banner.js'></script>";
		return $retorno;
		
	}//end MontarBannerTodasVezes()

	function VerificarRespondeu() {
		$sql = "SELECT count(bdsc.*) as total 
				FROM tb_banner_drop_shadow_clicks bdsc 
				WHERE bdsc.ug_id			=".$this->getUgId()." 
					AND bdsc.bds_id_banner	=".$this->getIdBanner();
		$rs_resposta = SQLexecuteQuery($sql);
		//echo $sql.":sql<br>";
		$rs_resposta_row = pg_fetch_array($rs_resposta);
		$retorno = $rs_resposta_row['total'];
		return $retorno;
	}//end function VerificarRespondeu()

	function InsereVisualizacao(){
		$retorno = false;
		$sql ="INSERT INTO tb_banner_drop_shadow_clicks (bds_id_banner, ug_id, bdsc_data_inclusao, bdsc_tipo_usuario) VALUES (".$this->getIdBanner().",".$this->getUgId().",NOW(),'".$this -> getTipoUsuario()."'); ";
		$rs_banner_repostas = SQLexecuteQuery($sql);
		if(!$rs_banner_repostas) {
			$retorno = "Erro ao salvar informa&ccedil;&otilde;es da pergunta. ($sql)<br>";
		}
		else $retorno = true;
		return $retorno;
	}//end InsereVisualizacao()

	function InsereClick(){
		$retorno = false;
		if($this->getTipoBanner()==1) {
			$sql ="INSERT INTO tb_banner_drop_shadow_clicks (bds_id_banner, ug_id, bdsc_data_inclusao, bdsc_tipo_usuario, bdsc_click) VALUES (".$this->getIdBanner().",".$this->getUgId().",NOW(),'".$this -> getTipoUsuario()."', 1);";
		}//end if($this->getTipoBanner()==1)
		else {
			$sql ="UPDATE tb_banner_drop_shadow_clicks SET bdsc_click=1 WHERE bds_id_banner=".$this->getIdBanner()." AND ug_id=".$this->getUgId()." AND bdsc_tipo_usuario='".$this -> getTipoUsuario()."';";
		}//end else if($this->getTipoBanner()==1)
		$rs_banner_repostas = SQLexecuteQuery($sql);
		if(!$rs_banner_repostas) {
			$retorno = "Erro ao salvar informa&ccedil;&otilde;es da pergunta. ($sql)<br>";
		}
		else $retorno = true;
		return $retorno;
	}//end InsereClick()

	function CapturaBannerEspecifico($bds_id_banner){
		$sql = "SELECT * 
				FROM tb_banner_drop_shadow 
				WHERE bds_id_banner=$bds_id_banner;";
		$rs_banners = SQLexecuteQuery($sql);
		if(!$rs_banners) {
			return null;
		} else {
			if($rs_banners_row = pg_fetch_array($rs_banners)) {
						$this->setIdBanner		($rs_banners_row['bds_id_banner']);
						$this->setTipoBanner	($rs_banners_row['bds_tipo']);
						$this->setBannerBanner	($rs_banners_row['bds_imagem_banner']);
						$this->setTextoBanner	($rs_banners_row['bds_texto']);
						$this->setLinkBanner	($rs_banners_row['bds_link']);
			}//end if($rs_banners_row = pg_fetch_array($rs_banners))
			return $retorno;
		}//end else if(!$rs_banners)
	}//end function CapturaBannerEspecifico($bds_id_banner)

}//end class

function gravaLog_DebugBanner($mensagem){
	//Arquivo
	$file = RAIZ_DO_PROJETO . "log/log_Debug_Banner.txt";

	//Mensagem
	$mensagem = "\n".date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 
}

?>