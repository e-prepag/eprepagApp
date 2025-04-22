<?php
/**
 * verificaServicoOnline
 * 
 * Classe que vai tratar os eventos de Servico com o Distribuidor
 * 
 * @author Wagner de Miranda
 *
*/

class verificaServicoOnline {
	public $TransferredValueTxn;		// string
        
	public function getRequestData() {		
		$this->TransferredValueTxn	= new TransferredValue();
                return array($this);
	}
	
	public function getRequestDataRequisicao($params) {		
		$this->TransferredValueTxn	= new TransferredValueRequisicao($params);
                return array($this);
	}
	
}//end class verificaServicoOnline

?>