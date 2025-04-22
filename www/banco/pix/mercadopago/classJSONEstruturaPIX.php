<?php
/**
 * JSONEstruturaPIX
 * 
 * Classe com estrutura PIX_JSON_REQUISICAO da Integracao com BluPay
 * 
 * @author Wagner de Miranda
 *
*/

class JSONEstruturaPIX {

        public $calendario;
        public $devedor;
        public $valor;
        public $solicitacaoPagador; //Determina um texto a ser apresentado ao pagador para que ele possa digitar uma informação correlata, em formato livre, a ser enviada ao recebedor.
         
        function __construct($params) {
		$this->calendario       =  new Validade();			
		$this->devedor          =  new Devedor($params);			
		$this->valor            =  new Original($params);
                $this->solicitacaoPagador = $params['descricao'];
                return array($this);
	} //end Construct
	
}//end class JSONEstruturaPIX

class Validade {
    
        public $expiracao;     //Data de validade da cobrança, após a data a mesma será cancelada

       function __construct() {
           $this->expiracao = 3600; //date('Y-m-d');
       }//end Contruct
       
}//end class Validade

class Devedor {
    
        public $cpf;           //CPF do devedor da cobrança, será obrigatório caso não seja informado o CNPJ do devedor
        public $cnpj;          //CNPJ do devedor da cobrança, será obrigatório caso não seja informado o CPF do devedor
        public $nome;          //Nome do devedor da cobrança

       function __construct($params) {
           if (strlen($params['cpf_cnpj']) > 11) {
               $this->cnpj = $params['cpf_cnpj'];
               unset($this->cpf);
           }
           else {
               $this->cpf = $params['cpf_cnpj'];
               unset($this->cnpj);
           }
            $this->nome = $params['nome'];
       }//end Contruct
       
}//end class Devedor

class Original {
    
        public $original;      //Valor da cobrança

       function __construct($params) {
           $this->original = $params['valor'];
       }//end Contruct
       
}//end class Original


?>