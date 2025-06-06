<?php require_once __DIR__ . '/../constantes_url.php'; ?>
<?php
/**
 * Acrescentar a linha correspondente em gamers_instructions.php para manter a listagem funcionando, depois passar para banco de dados
 * $aoperadoras = array(
 * 		"Habbo" => array("opr_codigo" => 16, "vgm_id" => 0, "vgm_nome" => ""),
 * 		"Stardoll" => array("opr_codigo" => 38, "vgm_id" => 0, "vgm_nome" => ""),
 * 		"Softnyx" => array("opr_codigo" => 37, "vgm_id" => 0, "vgm_nome" => ""),
 * 		"Vostu_MiniFazenda" => array("opr_codigo" => 35, "vgm_id" => 0, "vgm_nome" => "MiniFazenda"),
 * 		"Vostu_Joga_Craque" => array("opr_codigo" => 35, "vgm_id" => 0, "vgm_nome" => "Joga Craque"),
 * 		"Vostu_CafeMania" => array("opr_codigo" => 35, "vgm_id" => 0, "vgm_nome" => "CaféMania"),
 * 	);
 * 
 * 
 */


function get_Instructions_for_Gamer_PIN($opr_codigo, $vgm_id, $vgm_nome) {

	$msgEmail = "";
	$blEmailHabbo = (($opr_codigo == 16)?true:false);
	$blEmailVostu = (($opr_codigo == 35)?true:false);

	$blEmailStardoll = (($opr_codigo == 38)?true:false);
	$blEmailSoftnyx = (($opr_codigo == 37)?true:false);

	$blEmailBrancaleone = (($opr_codigo == 26)?true:false);

	$blEmailAlawar = (($opr_codigo == 55)?true:false);

	$blEmailEPPCASH = (($opr_codigo == 49)?true:false);

	$blEmailHabbo2 = (($opr_codigo == 125)?true:false);

	$blEmailGarena = (($opr_codigo == 124)?true:false);

	$blEmailVostu_Joga_Craque = (($blEmailVostu && ($vgm_nome == "Joga Craque"))?true:false);
	$blEmailVostu_MiniFazenda = (($blEmailVostu && ($vgm_nome == "MiniFazenda"))?true:false);
	$blEmailVostu_CafeMania = (($blEmailVostu && ($vgm_nome == "CaféMania"))?true:false);
	$blEmailVostu_Rede_do_Crime = (($blEmailVostu && ($vgm_nome == "Rede do Crime"))?true:false);

	//Instrucoes Habbo - Parte Descr - Resto
	if($blEmailHabbo || $blEmailHabbo2){
	$msgEmail = "	<br>
					<table border='0' cellspacing='0' width='90%'>
					<tr>
						<td class='texto'> 
							Para ativar seu código de moedas no jogo siga os passos abaixo:<br>
							<b>1</b>. Acesse o site do <a href='http://www.habbo.com.br' target='_blank'>Habbo Hotel</a> e clique em Habboshop.<br>
							<b>2</b>. Ao lado direito da tela você encontrará um campo em branco para inserir seu código pré-pago.<br>
							<b>3</b>. Insira seu código e clique em 'Ativar'.<br> 
						</td>
					</tr>
					</table>";
	}

	//Instrucoes Vostu - Parte Descr - Resto
	//Instrucoes Vostu - Parte Descr - Resto
	if($blEmailVostu){
		if($blEmailVostu_Joga_Craque){
			$msgEmail = "<br>
							<table border='0' cellspacing='0' width='90%'>
							<tr>
								<td class='texto'> 
								Para inserir seu código no jogo siga os passos abaixo:<br>
								- Acesse o Joga Craque e clique em Empresário.<br>
								- Clique em 'Clique aqui para inserir seu código de Boleto Bancário ou Eprepag'.<br>
								- Digite a Senha azul que recebeu por e-mail e clique em enviar.<br>
							</td>
							</tr>
							</table>";
		} elseif($blEmailVostu_Rede_do_Crime){
			$msgEmail = "<br>
							<table border='0' cellspacing='0' width='90%'>
							<tr>
								<td class='texto'> 
									Para inserir seu código no jogo siga os passos abaixo:<br>
									<br>
									<b>1</b>) Acesse sua conta no jogo e clique em 'Mercado'. <br>
									<b>2</b>) No canto inferior esquerdo da tela selecione 'Compre com E-Prepag' e clique em 'Inserir código'. <br>
									<b>3</b>) Selecione a quantidade desejada e digite a senha (enviada para seu e-mail com o título \"Compra Processada\") no campo em branco.<br>
									<b>4</b>) Após digitar sua senha aparecerá uma janela confirmando sua compra e você deverá clicar em 'Comprar'.<br>
								</td>
							</tr>
							</table>";
		} else {
			$msgEmail = "<br>
							<table border='0' cellspacing='0' width='90%'>
							<tr>
								<td class='texto'> 
									Para inserir seu código no jogo siga os passos abaixo:<br>
									<b>1</b>) Acesse sua conta no jogo e clique em 'PINS'. <br>
									<b>2</b>) Selecione Grana ou Ouro e digite a senha no campo em branco.<br>
									<b>3</b>) Após digitar sua senha aparecerá uma janela confirmando sua compra e você deverá clicar em 'Comprar'.<br>
								</td>
							</tr>
							</table>";
		}
	}

	//Instrucoes Stardoll - Parte Descr - Resto
	if($blEmailStardoll){
	$msgEmail = "<br>
					<table border='0' cellspacing='0' width='90%'>
					<tr>
						<td class='texto'> 
							Para inserir seus créditos na Stardoll siga os passos abaixo: <br><br>
							<b>1.</b> Faça Login no jogo;<br>
							<b>2.</b> Clique em 'Avançar' do quadro superior direito;<br>
							<b>3.</b> Agora desça a página e clique no link 'Clique aqui para mais opções de pagamento';<br>
							<b>4.</b> Ao final da página do lado direito em 'Resgatar código de presente' digite o código comprado;<br>
							<b>5.</b> Clique em 'Resgatar' e pronto.<br>
						</td>
					</tr>
					</table>";
	}

	//Instrucoes Softnyx - Parte Descr - Resto
	if($blEmailSoftnyx){
	$msgEmail = "<br>
					<table border='0' cellspacing='0' width='90%'>
					<tr>
						<td class='texto'> 
							Para inserir seus créditos na Softnyx siga os passos abaixo: <br><br>
							<b>1.</b> Faça Login no jogo;<br>
							<b>2.</b> Clique no item 'Cash' da barra superior e depois em 'Recarregar Cash';<br>
							<b>3.</b> Clique em 'Outros' e depois escolha 'E-Prepag'<br>
							<b>4.</b> Digite o 'Código de Segurança' (xxxx-xxxx-xxxx)<br>
							<b>5.</b> Digite o 'Número do cartão/cupom' - serial (000000000014) e 'Aceitar'<br>
						</td>
					</tr>
					</table>";
	}

	//Instrucoes Brancaleone
	if($blEmailBrancaleone){
	$msgEmail = "<br>
				<table border='0' cellspacing='0' width='90%'>
				<tr>
					<td class='texto'> 
							Para utilizar seu PIN:<br><br>
							<b>1-</b> Entre no site do Migux (www.migux.com) e clique em \"Tenho um PIN!\". <br>
							<b>2-</b> Depois, basta digitar seu usuário, senha e o PIN que acaba de receber. <br>
							<br>
							- Ao entrar no ambiente, você poderá acompanhar a quantidade de créditos (dias) disponíveis (no alto do site, do lado esquerdo, você encontrará um ícone de ampulheta e o número de dias). <br>
							- Você só precisará digitar o seu PIN uma única vez. Ele não pode ser reutilizado. <br>
							- Durante o período de validade do seu PIN (30 dias + 7 grátis), você poderá aproveitar todas as vantagens que só os assinantes do Migux têm. <br>
							- Caso você adquira e inclua na sua conta um outro PIN, os novos créditos (dias) serão acumulados<br>
					</td>
				</tr>
				</table>";
	}

	//Instrucoes Alawar
	if($blEmailAlawar){
	$msgEmail = "<br>
					<table border='0' cellspacing='0' width='90%'>
					<tr>
						<td class='texto'> 
							Falta muito pouco para você ativar o seu game Alawar <br><br>
							<b>1.</b> Compra do certificado de liberação<br>
							<b>2.</b> Efetue o download e faça a instalação do game escolhido<br>
							<b>3.</b> Após abrir o jogo, clique em 'Remover Limite de Tempo'<br>
							<b>4.</b> Clique em “Digite a chave do seu jogo” e insira o código que recebeu acima.<br>
							<br>
							Qualquer dúvida, acesse <a href='" . EPREPAG_URL_HTTP . "/prepag2/commerce/jogos/instrucoes_alawar.php' target='_blank'>" . EPREPAG_URL_HTTP . "/prepag2/commerce/jogos/instrucoes_alawar.php</a><br>
						</td>
					</tr>
					</table>";
	}

	//Instrucoes EPP CASH
	if($blEmailEPPCASH){
	$msgEmail = "<br>
					<table border='0' cellspacing='0' width='90%'>
					<tr>
						<td class='texto'> 
							<b>1.</b> Acesse sua conta no site da E-Prepag<br>
							<b>2.</b> Escolha o produto que deseja comprar<br>
							<b>3.</b> Selecione a forma de pagamento<br>
							<b>4.</b> Adicione o PIN adquirido e clique e em pagar. (Se houver diferença entre o valor do PIN e o valor do pedido, você ficará com um saldo em conta para outras compras)<br>
							<br>
							Obs.: Você também pode utilizar este PIN para adicionar saldo em conta clicando em 'Adicionar Saldo'.<br>
						</td>
					</tr>
					</table>";
	}

	if($blEmailGarena){
	$msgEmail = "<br>
					<table border='0' cellspacing='0' width='90%'>
					<tr>
						<td>
                                                    <font face='arial' color='#515151' size='2'>
                                                Instruções de Resgate:
                                                    </font>
						</td>
					</tr>
					<tr>
						<td> 
                                                    <font face='arial' color='#515151' size='2'>
							<b>1.</b> Acesse " . EPREPAG_URL_HTTPS . "/resgate/garena/creditos.php<br>
							<b>2.</b> Digite ou cole o PIN<br>
							<b>3.</b> Informe o ID da conta, localizado dentro do seu personagem do jogo.<br>
							<b>4.</b> Clique em RESGATAR, confirme o Nickname da conta clicando em CONFIRMAR e prontinho!<br>
							<br>
							Dúvidas? Acesse http://blog.e-prepag.com/resgate-garena/<br>
                                                    </font>
						</td>
					</tr>
					</table>";
	}

	return $msgEmail;
}
/*
<b>5.</b> Digite o pin recebido neste e-mail e sua data de nascimento<br>
							<b>6.</b> Informe seu CPF neste formato 000.000.000-00<br>
							<b>7.</b> Clique no botão 'Prosseguir para Pagamento'<br>

*/
?>