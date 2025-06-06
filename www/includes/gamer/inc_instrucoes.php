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
 * 		"Vostu_CafeMania" => array("opr_codigo" => 35, "vgm_id" => 0, "vgm_nome" => "Caf�Mania"),
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
	$blEmailVostu_CafeMania = (($blEmailVostu && ($vgm_nome == "Caf�Mania"))?true:false);
	$blEmailVostu_Rede_do_Crime = (($blEmailVostu && ($vgm_nome == "Rede do Crime"))?true:false);

	//Instrucoes Habbo - Parte Descr - Resto
	if($blEmailHabbo || $blEmailHabbo2){
	$msgEmail = "	<br>
					<table border='0' cellspacing='0' width='90%'>
					<tr>
						<td class='texto'> 
							Para ativar seu c�digo de moedas no jogo siga os passos abaixo:<br>
							<b>1</b>. Acesse o site do <a href='http://www.habbo.com.br' target='_blank'>Habbo Hotel</a> e clique em Habboshop.<br>
							<b>2</b>. Ao lado direito da tela voc� encontrar� um campo em branco para inserir seu c�digo pr�-pago.<br>
							<b>3</b>. Insira seu c�digo e clique em 'Ativar'.<br> 
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
								Para inserir seu c�digo no jogo siga os passos abaixo:<br>
								- Acesse o Joga Craque e clique em Empres�rio.<br>
								- Clique em 'Clique aqui para inserir seu c�digo de Boleto Banc�rio ou Eprepag'.<br>
								- Digite a Senha azul que recebeu por e-mail e clique em enviar.<br>
							</td>
							</tr>
							</table>";
		} elseif($blEmailVostu_Rede_do_Crime){
			$msgEmail = "<br>
							<table border='0' cellspacing='0' width='90%'>
							<tr>
								<td class='texto'> 
									Para inserir seu c�digo no jogo siga os passos abaixo:<br>
									<br>
									<b>1</b>) Acesse sua conta no jogo e clique em 'Mercado'. <br>
									<b>2</b>) No canto inferior esquerdo da tela selecione 'Compre com E-Prepag' e clique em 'Inserir c�digo'. <br>
									<b>3</b>) Selecione a quantidade desejada e digite a senha (enviada para seu e-mail com o t�tulo \"Compra Processada\") no campo em branco.<br>
									<b>4</b>) Ap�s digitar sua senha aparecer� uma janela confirmando sua compra e voc� dever� clicar em 'Comprar'.<br>
								</td>
							</tr>
							</table>";
		} else {
			$msgEmail = "<br>
							<table border='0' cellspacing='0' width='90%'>
							<tr>
								<td class='texto'> 
									Para inserir seu c�digo no jogo siga os passos abaixo:<br>
									<b>1</b>) Acesse sua conta no jogo e clique em 'PINS'. <br>
									<b>2</b>) Selecione Grana ou Ouro e digite a senha no campo em branco.<br>
									<b>3</b>) Ap�s digitar sua senha aparecer� uma janela confirmando sua compra e voc� dever� clicar em 'Comprar'.<br>
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
							Para inserir seus cr�ditos na Stardoll siga os passos abaixo: <br><br>
							<b>1.</b> Fa�a Login no jogo;<br>
							<b>2.</b> Clique em 'Avan�ar' do quadro superior direito;<br>
							<b>3.</b> Agora des�a a p�gina e clique no link 'Clique aqui para mais op��es de pagamento';<br>
							<b>4.</b> Ao final da p�gina do lado direito em 'Resgatar c�digo de presente' digite o c�digo comprado;<br>
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
							Para inserir seus cr�ditos na Softnyx siga os passos abaixo: <br><br>
							<b>1.</b> Fa�a Login no jogo;<br>
							<b>2.</b> Clique no item 'Cash' da barra superior e depois em 'Recarregar Cash';<br>
							<b>3.</b> Clique em 'Outros' e depois escolha 'E-Prepag'<br>
							<b>4.</b> Digite o 'C�digo de Seguran�a' (xxxx-xxxx-xxxx)<br>
							<b>5.</b> Digite o 'N�mero do cart�o/cupom' - serial (000000000014) e 'Aceitar'<br>
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
							<b>2-</b> Depois, basta digitar seu usu�rio, senha e o PIN que acaba de receber. <br>
							<br>
							- Ao entrar no ambiente, voc� poder� acompanhar a quantidade de cr�ditos (dias) dispon�veis (no alto do site, do lado esquerdo, voc� encontrar� um �cone de ampulheta e o n�mero de dias). <br>
							- Voc� s� precisar� digitar o seu PIN uma �nica vez. Ele n�o pode ser reutilizado. <br>
							- Durante o per�odo de validade do seu PIN (30 dias + 7 gr�tis), voc� poder� aproveitar todas as vantagens que s� os assinantes do Migux t�m. <br>
							- Caso voc� adquira e inclua na sua conta um outro PIN, os novos cr�ditos (dias) ser�o acumulados<br>
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
							Falta muito pouco para voc� ativar o seu game Alawar <br><br>
							<b>1.</b> Compra do certificado de libera��o<br>
							<b>2.</b> Efetue o download e fa�a a instala��o do game escolhido<br>
							<b>3.</b> Ap�s abrir o jogo, clique em 'Remover Limite de Tempo'<br>
							<b>4.</b> Clique em �Digite a chave do seu jogo� e insira o c�digo que recebeu acima.<br>
							<br>
							Qualquer d�vida, acesse <a href='" . EPREPAG_URL_HTTP . "/prepag2/commerce/jogos/instrucoes_alawar.php' target='_blank'>" . EPREPAG_URL_HTTP . "/prepag2/commerce/jogos/instrucoes_alawar.php</a><br>
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
							<b>4.</b> Adicione o PIN adquirido e clique e em pagar. (Se houver diferen�a entre o valor do PIN e o valor do pedido, voc� ficar� com um saldo em conta para outras compras)<br>
							<br>
							Obs.: Voc� tamb�m pode utilizar este PIN para adicionar saldo em conta clicando em 'Adicionar Saldo'.<br>
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
                                                Instru��es de Resgate:
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
							D�vidas? Acesse http://blog.e-prepag.com/resgate-garena/<br>
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
							<b>7.</b> Clique no bot�o 'Prosseguir para Pagamento'<br>

*/
?>