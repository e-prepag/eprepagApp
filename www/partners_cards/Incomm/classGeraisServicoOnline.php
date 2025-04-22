<?php
/**
 * Class Gerais
 * 
 * Classe que vai tratar as Classes de Uso Geral para Verificaчуo de Serviчo Online da Integracao com Distribuidor 
 * 
 * @author Wagner de Miranda
 *
*/

class TransferredValue {

	public $TransferredValueTxnReq;		// string

	function __construct() {
		$this->TransferredValueTxnReq	= new dadosConsultaServicoOnline();			
	} //end Construct
        
} //end class TransferredValue 

//Classe contendo os dados da Consulta
class dadosConsultaServicoOnline {

	public $ReqCat;		// string
        public $ReqAction;	// string
        public $Date;		// string
        public $Time;		// string
        public $PartnerName;	// string
        public $EchoData;	// string

	function __construct() {
		$this->ReqCat		= REQ_CAT;			
		$this->ReqAction	= 'Echo';			
		$this->Date		= date('Ymd');			
		$this->Time		= date('His');			
		$this->PartnerName	= VENDOR_NAME;			
		$this->EchoData		= 'Testando';			
	}

}//end class dadosConsultaServicoOnline

?>