<?php
/**
 * Class Gerais
 * 
 * Classe que vai tratar as Classes de Uso Geral da Integracao com CPF 
 * 
 * @author Wagner de Miranda
 *
*/

//Classe contendo os dados de Acesso
class dadosAcesso {

	public $logon;		// string
	public $senha;		// string

	function __construct() {
		$this->logon	= CPF_CLIENT_ID;			
		$this->senha	= CPF_CLIENT_PASSWORD;
	} //end Construct
        
} //end class dadosAcesso 

//Classe contendo os dados da Consulta
class dadosConsulta {

	public $idconsulta;	// int
        public $cpfcnpj; 	// string
        public $tipopessoa;	// string

	function __construct($params) {
		$this->idconsulta	= CPF_ID_CONSULT;			
		$this->cpfcnpj		= $params['cpfcnpj'];			
		$this->tipopessoa	= CPF_TIPO_PESSOA_FISICA;			
	}

}//end class dadosConsulta

//Classe contendo os dados da Parametros
class dadosParametros {

	public $nomeCampo;	// string
        public $valorCampo; 	// string

	function __construct($nomeCampo,$param) {
		$this->nomeCampo	= $nomeCampo;			
		$this->valorCampo	= $param;			
	}

}//end class dadosParametros


?>