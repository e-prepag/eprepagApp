<?php
// Classe para exibição da descrição do relatório
class DescriptionReport {

	private $ID;
	private $Altura;
	private $Mensagens = array(
								"COMISSAO"			=> 
												"Os totais disponibilizados neste relatório são as comissões já descontado o custo do canal.\nOs publisher EPP CASH LAN House e E-Prepag CASH estão excluídos destes totais.\nA exclusão é somente em relação a venda do EPP CASH e não da utilização deste, que está redirecionada para o canal de venda correspondente.",
								"VOLUME"			=> 
												"Os totais disponibilizados neste relatório são por volumes de vendas.\nOs publisher EPP CASH LAN House e E-Prepag CASH serão excluídos destes totais quando selecionado o checkbox correspondente.\nA exclusão é somente em relação a venda do EPP CASH e não da utilização deste, que está redirecionada para o canal de venda correspondente.",
								"FECHAMENTO"		=> 
												"Os totais disponibilizados neste relatório quando nenhum Publisher é selecionado não estão considerando os Publishers EPP CASH LAN House e E-Prepag CASH.\nA exclusão é somente em relação a venda do EPP CASH e não da utilização deste, que está redirecionada para o canal de venda correspondente.",
								"NFSE"				=> 
												"Este relatório contem os mesmos totais do relatório de fechamento, com a diferença de poder listas mais de um Publisher simultaneamente.\n Além de possibilitar a geração do arquivo RPS para importação no site da prefeitura.",
								"ESTORNO"			=> 
												"A resposta da pesquisa contém somente depósitos passíveis de estornos.\nOu seja, somente depósitos que não foram utilizados (totalmente ou parcialmente).",
								"COMPOSICAO_FIFO"	=> 
												"Relatório de pesquisa da composição do Saldo de Gamers.\nÉ utilizado fila FIFO para sua utilização/depósito.",
								"HISTORICO_USUARIO"	=> 
												"Relatório de pesquisa do histórico do Gamers.\nEsta pesquisa retorna toda ação executada pelo Gamer.",
								"TOTAIS_JOGOS"	=> 
												"Os totais disponibilizados neste relatório são de volumes de vendas por jogos.\nPara exibir as colunas de canais deve ser clicado na coluna do jogo que deseja expandir.\nPara ocultar as colunas de canais, basta clicar sobre elas próprias.",
								"CONCILIACAO_BANCARIA"	=> 
												"O botão SALVAR no final da página, salva todas as informações alteradas na grade.\nPara o botão '+' Juntar linhas e 'x' Separar linhas, somente é salvo os dados contidos nas duas linhas que serão unidas/separadas.\n",
								"HISTORICO_SALDO"	=> 
												"Relatório de pesquisa do histórico de Saldo de Gamers e/ou LANs.\nEsta pesquisa retorna o total de movimentação no período, saldo no inicio e final do período.",
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
					<!--Div Box que exibe Regra do Relatório -->
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