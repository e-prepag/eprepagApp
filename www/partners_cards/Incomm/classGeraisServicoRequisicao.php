<?php
/**
 * Class Gerais
 * 
 * Classe que vai tratar as Classes de Uso Geral para Requisicao da Integracao com Distribuidor 
 * 
 * @author Wagner de Miranda
 *
*/

class TransferredValueRequisicao {

	public $TransferredValueTxnReq;		// string

	function __construct($params) {
		$this->TransferredValueTxnReq	= new dadosConsultaServicoRequisicao($params);			
	} //end Construct
        
} //end class TransferredValueRequisicao 

//Classe contendo os dados da Consulta
class dadosConsultaServicoRequisicao {

	public $ReqCat;		// string
        public $ReqAction;	// string
        public $Date;		// string
        public $Time;		// string
        public $PartnerName;	// string
        public $CardActionInfo;	// string

	function __construct($params) {
            	$this->ReqCat		= REQ_CAT;			
		$this->ReqAction	= $params['servico'];			
		$this->Date		= date('Ymd');			
		$this->Time		= date('His');			
		$this->PartnerName	= VENDOR_NAME;			
		$this->CardActionInfo	= new CardActionInfoRequisicao($params);			
	}
}//end class dadosConsultaServicoRequisicao

class CardActionInfoRequisicao {

	public $PIN;	// string
        public $SrcRefNum;	// string
        
	function __construct($params) {
		$this->PIN		= $params['pin'];			
		$this->SrcRefNum	= date("YmdHis").str_pad(rand(0,999), 3, "0", STR_PAD_LEFT);			
	}
}//end class CardActionInfoRequisicao


?>