<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto . '/class/util/ControleIP.php';
//Classe de Verificação de IPs das LANs
class DistIntegracaoIP {

	//Variavel que coloca o sistema em OFF LINE qdo FALSE
	private $auxOnLine = true;
	
	//variavel contendo o IP da E-Prepag
	//private $auxIP = "189.38.238.205";
	
	//Dados Intergrador de Verificação de IP
	/*****************************************************
	LEGENDA:
	USE_CHECK	=> 1 = Utiliza confirmação de solicitação através do PARTNETR_CHECK
				=> 2 = NÃO Utiliza confirmação de solicitação
	*******************************************************/

	// Cadastro dos Publishers
	private $publishers = array(
							'78'	=> array(
											'CamposObrigatorio'	=> array(
																			'Email',
																			'Jogo',
																			'Promocao',
																		),
										),
	);

	
	// CODRETEPP VALORES INTERNOS
	private $notify_list_values = array(
				'SV'	=> '1',
				'FIP'	=> '2',
				'FID'	=> '3',
				'F2'	=> '4',
				'ID'	=> '5',
				'ND'	=> '6',
				'EG'	=> '7',
				'OL'	=> '8',
				'FO'	=> '9',
	);

	// CODRETEPP VALORES PUBLISHER
	private $notify_list_values_pub = array(
				'SV'	=> '1',
				'FP'	=> '2',
				'ND'	=> '3',
				'EG'	=> '4',
				'OL'	=> '5',
				'FO'	=> '6',
	);

	private $ID;
    private $IP;
	private $Email;
	private $Jogo;
	private $Promocao;
	
	function setIP($IP) {
 		$this->IP = $IP;
	}
	function getIP(){
    	return $this->IP;
    }
    
	function setID($ID) {
 		$this->ID = $ID;
	}
	function getID(){
    	return $this->ID;
    }
    
    function setEmail($Email) {
 		$this->Email = $Email;
	}
	function getEmail(){
    	return $this->Email;
    }
    
	function setJogo($Jogo) {
 		$this->Jogo = $Jogo;
	}
	function getJogo(){
    	return $this->Jogo;
    }
        
    function setPromocao($Promocao) {
 		$this->Promocao = $Promocao;
	}
	function getPromocao(){
    	return $this->Promocao;
    }
    
    function __construct($IP,$ID,$Email,$Jogo,$Promocao) {
		$this	->	setIP		($IP);
		$this	->	setID		($ID);
		$this	->	setEmail	($Email);
		$this	->	setJogo		($Jogo);
		$this	->	setPromocao	($Promocao);
	}//end function __construct($IP,$ID,$Email,$Jogo,$Promocao)


	function converte_detalhe_codretepp($valor){
		switch ($valor) {
			case $this->notify_list_values['SV'] : 
					return $this->notify_list_values_pub['SV'];
					break;
			case $this->notify_list_values['FIP']:
					return $this->notify_list_values_pub['FP'];
					break;
			case $this->notify_list_values['FID']:
					return $this->notify_list_values_pub['FP'];
					break;
			case $this->notify_list_values['F2']:
					return $this->notify_list_values_pub['FP'];
					break;
			case $this->notify_list_values['ID']:
					return $this->notify_list_values_pub['ND'];
					break;
			case $this->notify_list_values['ND']:
					return $this->notify_list_values_pub['ND'];
					break;
			case $this->notify_list_values['EG']:
					return $this->notify_list_values_pub['EG'];
					break;
			case $this->notify_list_values['OL']:
					return $this->notify_list_values_pub['OL'];
					break;
			case $this->notify_list_values['FO']:
					return $this->notify_list_values_pub['FO'];
					break;
		}//end switch
	}//end function converte_detalhe_codretepp($valor)

	function legenda_resposta($valor,$tipo = 'INTERNO'){
		// CODRETEPP LEGENDA INTERNA
		$notify_list = array(
					$this->notify_list_values['SV']		=> 'Sucesso Validacao',
					$this->notify_list_values['FIP']	=> 'Falta parametro: IP',
					$this->notify_list_values['FID']	=> 'Falta parametro: ID',
					$this->notify_list_values['F2']		=> 'Faltam os dois parametros: IP e ID',
					$this->notify_list_values['ID']		=> 'O IP do requisitante nao confere com o cadastrado',
					$this->notify_list_values['ND']		=> 'IP nao cadastrado',
					$this->notify_list_values['EG']		=> 'ERRO GERAL',
					$this->notify_list_values['OL']		=> 'SISTEMA OFF LINE',
					$this->notify_list_values['FO']		=> 'Faltam parametros adicionais obrigatorios',
		);

		// CODRETEPP LEGENDA PUBLISHER
		$notify_list_pub = array(
					$this->notify_list_values_pub['SV']	 => 'Sucesso Validacao',
					$this->notify_list_values_pub['FP']	 => 'Falta parametro',
					$this->notify_list_values_pub['ND']	 => 'IP nao cadastrado',
					$this->notify_list_values_pub['EG']	 => 'ERRO GERAL',
					$this->notify_list_values_pub['OL']	 => 'SISTEMA OFF LINE',
					$this->notify_list_values_pub['FO']	 => 'Faltam parametros adicionais obrigatorios',
		);
		if(strtoupper($tipo)=='INTERNO') {
			return $notify_list[$valor];
		}
		else {
			return $notify_list_pub[$valor];
		}
	}//end function legenda_resposta($valor,$tipo = 'INTERNO')

	function verifica_campos_obrigatorios(){
		$aux_vetor = $this->publishers[$this->getID()]['CamposObrigatorio'];
		$resposta = "";
		foreach($aux_vetor as $value ){
			$nameMethod = "get".$value;
			if(!$this->$nameMethod()){
				$resposta = $this->notify_list_values['FO'];
				break;
			}
		}//end foreach
		return $resposta;
	}//end function verifica_campos_obrigatorios()

	function retorna_ip_integracao($opr_codigo) {
		$sql = "select opr_ip from operadoras where opr_codigo=".$opr_codigo." and opr_ip!='';";
		$rs_log = SQLexecuteQuery($sql);
		if($rs_log) { 
			$rs_log_row = pg_fetch_array($rs_log);
			if ($rs_log_row['opr_ip'] != '')
				return $rs_log_row['opr_ip'];
			// informa zero (0) quando nao foi encontrado -- ATENCAUN
			else return '0';
		}//end if($rs_log)
	}//end function retorna_ip_integracao($opr_codigo)

	function retorna_ug_id() {
		//verificar o que fazer se acontecer de LANs diferente possuirem o mesmo IP cadastrado +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$sql = "select ug_id from dist_ip where di_ip = '".addslashes($this->getIP())."' and di_ativo = 1";
		$rs_log = SQLexecuteQuery($sql);
		if($rs_log) { 
			$rs_log_row = pg_fetch_array($rs_log);
			if ($rs_log_row['ug_id'] != '')
				return $rs_log_row['ug_id'];
			// informa zero (0) quando nao foi encontrado -- ATENCAUN
			else return '0';
		}
	}//end function retorna_ug_id()

	function ip_log_publisher($codret) { 
		$sql = "INSERT INTO dist_ip_log_publisher (opr_codigo, dilp_ip_publisher, dilp_data, dilp_ip_verificado, ug_id, dilp_codretepp_interno, dilp_codretepp,dilp_email,dilp_jogo,dilp_promocao) 
				VALUES (".intval($this->getID()).",'".retorna_ip_acesso()."',NOW(),'".$this->getIP()."',".intval($this->retorna_ug_id()).",".intval($codret).",".intval($this->converte_detalhe_codretepp($codret)).",'".$this->getEmail()."','".$this->getJogo()."','".$this->getPromocao()."')";
		$rs_log = SQLexecuteQuery($sql);
		if(!$rs_log) {
			 echo "<font color='#FF0000'><b>Erro na gera&ccedil;&atilde;o de LOG.\n</b></font><br>";
		}
	}//end function ip_log_publisher()

	function verifica_ip_lan_house() {
		$aux_codretepp = '0';
		$mensagem = '';
		if($this->auxOnLine) {
			if (!($this->getIP()) && !($this->getID())) {
				$aux_codretepp = $this->notify_list_values['F2'];
			}//end if (!($this->getIP()) && !($this->getID()))
			elseif (!($this->getIP())) {
				$aux_codretepp = $this->notify_list_values['FIP'];
			}//end elseif (!($this->getIP()))
			elseif (!($this->getID())) {
				$aux_codretepp = $this->notify_list_values['FID'];
			}//end elseif (!($this->getID()))
			elseif ($this->verifica_campos_obrigatorios()) {
				$aux_codretepp = $this->notify_list_values['FO'];
			}//end elseif (!($this->getID()))
			else {
                $aux_opr_ip = $this->retorna_ip_integracao($this->getID());
                //$aux_opr_ip = $this->retorna_ip_integracao(64);
                //$vetor_IPs = explode(';', $aux_opr_ip);
				$aux_teste_IP = false;
                $controleIP = new ControleIP();
                if ( $controleIP->isInOprRange($aux_opr_ip, retorna_ip_acesso()) ) {
                    $aux_teste_IP = true;
                }
                /*
				for ($i = 0; $i < count($vetor_IPs); $i++) {
					if ($vetor_IPs[$i] == retorna_ip_acesso())
						$aux_teste_IP = true;
				}//end for
                */
				if ($aux_opr_ip <> 0 && $aux_teste_IP) {
					if($this->retorna_ug_id()) {
						//if(retorna_ip_acesso()==$this->auxIP) {
							$sql="select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)||' ('||ug.ug_tipo_cadastro||')' END) as ug_nome2,* from dist_usuarios_games ug where ug_id=".$this->retorna_ug_id();
							$rs_detalhes = SQLexecuteQuery($sql);
							if($rs_detalhes) {
								$rs_detalhes_row = pg_fetch_array($rs_detalhes);
								$mensagem .= "ID da LAN: ".$rs_detalhes_row['ug_id']."\n";
								$mensagem .= "Nome da LAN: ".$rs_detalhes_row['ug_nome2']."\n";
								$mensagem .= "Cidade da LAN: ".$rs_detalhes_row['ug_cidade']."\n";
								$mensagem .= "Estado da LAN: ".$rs_detalhes_row['ug_estado']."\n";
							}//end if($rs_detalhes)
						//}//end if(retorna_ip_acesso()=="189.38.238.205")
						$aux_codretepp = $this->notify_list_values['SV'];
					}//end if($this->retorna_ug_id())
					else $aux_codretepp = $this->notify_list_values['ND'];
				}//end if ($aux_opr_ip <> 0 && $aux_teste_IP)
				else $aux_codretepp = $this->notify_list_values['ID'];
			}//end else do elseif(!($this->getID))
		}//end if($this->auxOnLine)
		else {
			$aux_codretepp = $this->notify_list_values['OL'];
		}
		//testando se não houve nenhuma alteração na variavel auxiliar
		if ($aux_codretepp == '0') {
			$aux_codretepp = $this->notify_list_values['EG'];
		}//end if ($aux_codretepp == '0')
		
		$mensagem .= "Legenda Interna: ".$this->legenda_resposta($aux_codretepp)."\n";
		$mensagem .= "CODRETEPP INTERNO EPP = ".$aux_codretepp."\n";
		//exibe legenda
		$mensagem .= "Legenda Publisher: ".$this->legenda_resposta($this->converte_detalhe_codretepp($aux_codretepp),'Externa')."\n";
		gravaLog_DistIntegracaoIP($mensagem);
		
		//gravando o log da chamada do metodo
		$this->ip_log_publisher($aux_codretepp);
		//retornando o codigo da varificação
		return $aux_codretepp;
	}//end function verifica_ip_lan_house()

}//end class

function gravaLog_DistIntegracaoIP($mensagem){
        
		//Arquivo
		$file = RAIZ_DO_PROJETO . "log/log_DistIntegracaoIP.txt";
	
		//Mensagem
		$mensagem =  str_repeat("-", 80)."\n".date('Y-m-d H:i:s'). " " .$GLOBALS['_SERVER']['SCRIPT_FILENAME'] . "\n" . $mensagem . "\n";
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
	
}//end function gravaLog_DistIntegracaoIP
?>