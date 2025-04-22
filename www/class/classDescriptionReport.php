<?php
// Classe para exibi��o da descri��o do relat�rio
class DescriptionReport {

	private $ID;
	private $Altura;
	private $Mensagens = array(
								"COMISSAO"			=> 
												"Os totais disponibilizados neste relat�rio s�o as comiss�es j� descontado o custo do canal.\nOs publisher EPP CASH LAN House e E-Prepag CASH est�o exclu�dos destes totais.\nA exclus�o � somente em rela��o a venda do EPP CASH e n�o da utiliza��o deste, que est� redirecionada para o canal de venda correspondente.",
								"VOLUME"			=> 
												"Os totais disponibilizados neste relat�rio s�o por volumes de vendas.\nOs publisher EPP CASH LAN House e E-Prepag CASH ser�o exclu�dos destes totais quando selecionado o checkbox correspondente.\nA exclus�o � somente em rela��o a venda do EPP CASH e n�o da utiliza��o deste, que est� redirecionada para o canal de venda correspondente.",
								"FECHAMENTO"		=> 
												"Os totais disponibilizados neste relat�rio quando nenhum Publisher � selecionado n�o est�o considerando os Publishers EPP CASH LAN House e E-Prepag CASH.\nA exclus�o � somente em rela��o a venda do EPP CASH e n�o da utiliza��o deste, que est� redirecionada para o canal de venda correspondente.",
								"NFSE"				=> 
												"Este relat�rio contem os mesmos totais do relat�rio de fechamento, com a diferen�a de poder listas mais de um Publisher simultaneamente.\n Al�m de possibilitar a gera��o do arquivo RPS para importa��o no site da prefeitura.",
								"ESTORNO"			=> 
												"A resposta da pesquisa cont�m somente dep�sitos pass�veis de estornos.\nOu seja, somente dep�sitos que n�o foram utilizados (totalmente ou parcialmente).",
								"COMPOSICAO_FIFO"	=> 
												"Relat�rio de pesquisa da composi��o do Saldo de Gamers.\n� utilizado fila FIFO para sua utiliza��o/dep�sito.",
								"HISTORICO_USUARIO"	=> 
												"Relat�rio de pesquisa do hist�rico do Gamers.\nEsta pesquisa retorna toda a��o executada pelo Gamer.",
								"TOTAIS_JOGOS"	=> 
												"Os totais disponibilizados neste relat�rio s�o de volumes de vendas por jogos.\nPara exibir as colunas de canais deve ser clicado na coluna do jogo que deseja expandir.\nPara ocultar as colunas de canais, basta clicar sobre elas pr�prias.",
								"CONCILIACAO_BANCARIA"	=> 
												"O bot�o SALVAR no final da p�gina, salva todas as informa��es alteradas na grade.\nPara o bot�o '+' Juntar linhas e 'x' Separar linhas, somente � salvo os dados contidos nas duas linhas que ser�o unidas/separadas.\n",
								"HISTORICO_SALDO"	=> 
												"Relat�rio de pesquisa do hist�rico de Saldo de Gamers e/ou LANs.\nEsta pesquisa retorna o total de movimenta��o no per�odo, saldo no inicio e final do per�odo.",
								);
		
	function setID($ID) {
 		$this->ID = $ID;
	}
	function getID(){
    	return $this->ID;
    }
    
	function setAltura($Altura) {
 		$this->Altura = $Altura;
	}
	function getAltura(){
    	return $this->Altura;
    }
    
	function __construct($ID=NULL) {
		$this->setID(strtoupper($ID));
	}

	function SelecionaDescricao() {
		$aux = "&nbsp;&nbsp;&nbsp;".str_replace("\n","<br>&nbsp;&nbsp;&nbsp;",$this->Mensagens[$this->getID()]);
		$this->setAltura(round(strlen($aux)/2));
		return $aux;
	}

	function MontaAreaDescricao() {
		$return = "<style type='text/css'>
					<!--
					#boxPopUpRegradeUso {
								z-index: 2;
								height: ".$this->getAltura()."px;
								width: 200px;
								*width: 210px;
								font-family: Arial, Helvetica, sans-serif;
								font-weight:bold;
								font-style:italic;
								font-size: 12px;
								text-align:justify;
								color: #272A74;
								background-color: #CCCCCC;
								border: 1px solid #444;
								padding: 5px;
								*padding-bottom: 5px;
								position: fixed;
								*position: absolute;
								top: 5%;
								left: 1%;
								display: none;
								overflow: auto;
								}
					-->
					</style>
					<!--Div Box que exibe Regra do Relat�rio -->
					<div id='boxPopUpRegradeUso'>".$this->SelecionaDescricao()."<span style='width:100%; height: 20px; text-align: right; display: block; cursor:pointer; color:000;' onclick='fechar()'>[ fechar ]</span></div>
					<script language='JavaScript' src='" . DIR_WEB . "/js/jquery.js'></script>
					<script language='JavaScript' type='text/JavaScript'>
                                        function fechar(){ $('#boxPopUpRegradeUso').css('display','none'); }
                                        
					$('#boxPopUpRegradeUso').show();
					</script>";
		return $return;
	}

	function MontaAreaDescricaoTodos() {
		$return	= "<style type='text/css'>
					<!--
					.vetor {
								font-family: Arial, Helvetica, sans-serif;
								font-weight:bold;
								font-style:italic;
								font-size: 12px;
								text-align:justify;
								color: #272A74;
							}
					-->
					</style><div class='vetor'><br>";
		foreach($this->Mensagens as $key => $val) {  
			$return .= "".$key."<br><br>&nbsp;&nbsp;&nbsp;".str_replace("\n","<br>&nbsp;&nbsp;&nbsp;",$val)."<br><br><br>";
		}
		$return .= "<div><br>";
		return $return;
	}


} //end class
?>