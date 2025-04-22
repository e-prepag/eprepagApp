<?php
/**
 * JSONEstruturaPIX
 * 
 * Classe com estrutura PIX_JSON_REQUISICAO da Integracao com Casa do Cr�dito 
 * 
 * @author Wagner de Miranda
 *
*/

class JSONEstruturaPIX {
    
	public $chave;         // Chave que receber� o pagamento String M�ximo de 77 caracteres
        public $valor;         // Valor de pagamento do QRCode Number M�ximo de 12 d�gitos num�ricos, sendo 2 d�gitos para casas decimais separados por ponto
        public $nomeRecebedor; // Nome de quem receber� o pagamento String M�ximo de 25 caracteres 
        public $cidade;        // Nome da cidade onde � efetuada a transacao String M�ximo de 15 caracteres 
        public $identificacaoPedido; //Identificador do QRCode armazenado no sistema que o est� gerando String M�ximo de 64 caracteres
        public $numeroConta;         // Numero da conta respons�vel por gerar o QRCode String M�ximo de 15 caracteres, apenas n�meros

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