<?php
/**
 * JSONEstruturaPIX
 * 
 * Classe com estrutura PIX_JSON_REQUISICAO da Integracao com Casa do Crédito 
 * 
 * @author Wagner de Miranda
 *
*/

class JSONEstruturaPIX {
    
	public $chave;         // Chave que receberá o pagamento String Máximo de 77 caracteres
        public $valor;         // Valor de pagamento do QRCode Number Máximo de 12 dígitos numéricos, sendo 2 dígitos para casas decimais separados por ponto
        public $nomeRecebedor; // Nome de quem receberá o pagamento String Máximo de 25 caracteres 
        public $cidade;        // Nome da cidade onde é efetuada a transacao String Máximo de 15 caracteres 
        public $identificacaoPedido; //Identificador do QRCode armazenado no sistema que o está gerando String Máximo de 64 caracteres
        public $numeroConta;         // Numero da conta responsável por gerar o QRCode String Máximo de 15 caracteres, apenas números

        function __construct($params) {
		$this->chave            = PIX_CHAVE;			
		$this->valor            = $params['valor'];
		$this->nomeRecebedor	= substr(RAZAO_EMPRESA,0,25);			
		$this->cidade		= substr(CIDADE_EMPRESA,0,15);
		$this->identificacaoPedido        = $params['id_venda'];
                $this->numeroConta      = PIX_CONTA;			
                return array($this);
	} //end Construct
	
}//end class JSONEstruturaPIX

?>