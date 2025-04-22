<?php

	//Status da venda
	//------------------------------------------------------------------------------------------------
	//	1: Pedido efetuado, aguardando dados do pagamento
	//	2: Dados do pagamento recebido, aguardando confirmacao de pagamento
	//	3: Pagamento confirmado e liberado para venda
	//	4: Processamento realizado. Crщdito serс encaminhado para o usuсrio
	//	5: Venda realizada. Crщdito encaminhado para o usuсrio
	//	6: Venda cancelada.
	$STATUS_VENDA_PAG = array(	'PEDIDO_EFETUADO' 			=> '1',
								'DADOS_PAGTO_RECEBIDO' 		=> '2',
								'PAGTO_CONFIRMADO' 			=> '3',
								'PROCESSAMENTO_REALIZADO'	=> '4',
								'VENDA_REALIZADA' 			=> '5',
								'VENDA_CANCELADA'			=> '6');
	//Ate o primeiro ponto final eh o chamado Descricao Curta, usado em alguns lugares
	//Para novos status, evitar passar de 25 caracteres ateh o primeiro ponto final.
	$STATUS_VENDA_PAG_DESCRICAO = array('1' => 'Pedido efetuado. Aguardando dados do pagamento.',
										'2' => 'Dados do pagamento jс informados. Aguardando confirmaчуo bancсria.',
										'3' => 'Pagamento confirmado. Liberado para venda.',
										'4' => 'Processamento realizado. Crщdito serс encaminhado para o usuсrio.',
										'5' => 'Venda realizada. Crщdito encaminhado para o usuсrio.',
										'6'	=> 'Venda cancelada.');
	$STATUS_VENDA_PAG_ICONES    = array('1' => 'Blue-5-1.gif',
										'2' => 'Blue-5-2.gif',
										'3' => 'Blue-5-3.gif',
										'4' => 'Blue-5-4.gif',
										'5' => 'Blue-5-5.gif',
										'6'	=> 'cancel.gif');
		
	
	// Delay para solicitar a sonda novamente ao banco 
	// (isso garante que os pedidos serуo, no mсximo, uma vez cada 60 segundos)
	$SONDA_BRADESCO_5_DELAY = 60; 
	$SONDA_BRADESCO_6_DELAY = 60; 
	$SONDA_BANCODOBRASIL_9_DELAY = 60; 

?>