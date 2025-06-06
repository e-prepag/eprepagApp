<?php
require_once "../../../includes/inc_register_globals.php";	

$render = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
$render .= '<html xmlns="http://www.w3.org/1999/xhtml">';
$render .= '<head>';
$render .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
$render .= '</head>';
$render .= '<body>';

if (strlen($_REQUEST['opcao']) > 0){
	//Imagens com o titulo
	$img1Cadastro = '<td width="27%" height="34"><IMG height=21 src="/imagens/passo1.gif" width=107 vspace=5 ></td>';
	$img2Pedido = '<td width="27%" height="34"><IMG height=21 src="/imagens/passo2.gif" width=107 vspace=5 ></td>';
	$img3Pagamento = '<td width="27%" height="34"><IMG height=21 src="/imagens/passo3.gif" width=107 vspace=5 ></td>';
	$img4Informa = '<td width="27%" height="34"><IMG height=21 src="/imagens/passo4.gif" width=107 vspace=5 ></td>';
	$img4CreditoOnline = '<td width="27%" height="34"><IMG height=21 src="/imagens/passo4b.gif" width=107 vspace=5 ></td>';
	$img5CreditoOnline = '<td width="27%" height="34"><IMG height=21 src="/imagens/passo5.gif" width=107 vspace=5 ></td>';
	/// upgrade 2010-04-16 ///// Configura prazos de entrega ///
	//$prazoTag1 = '<td width="27%" height="34"><b>Prazo de Entrega:</b></td>';
	////////////////////////////////////////////////////////////////////////
	//Textos
	$txtCadastro = utf8_encode('<td width="73%" height="34">Faça seu login, caso não seja cadastrado, cadastre-se agora!</td>');
	$txtPedido = utf8_encode('<td width="73%" height="34">Escolha o crédito online do game desejado. </td>');
	$txtInforma = utf8_encode('<td width="73%" height="34">Informe os dados do comprovante de pagamento em "Informar dados de pagamento" acessando seu pedido novamente.</td>');
	$txtPagamento1 = utf8_encode('<td width="73%" height="34">Escolha "Transferência Online Bradesco" você será direcionado para a página do Banco e realize o pagamento.</td>');
	$txtPagamento2 = utf8_encode('<td width="73%" height="34">Escolha "Cartão de Débito (Visa Electron) Bradesco" você será direcionado para a página do Banco e realize o pagamento.</td>');
	$txtPagamento3 = utf8_encode('<td width="73%" height="34">Escolha o crédito online do game desejado. </td>');
	$txtPagamento4 = utf8_encode('<td width="73%" height="34">Escolha "Depósito em conta/Transferência (offline)" efetue o pagamento no banco e guarde o comprovante.</td>');
	$txtPagamento5 = utf8_encode('<td width="73%" height="34">Escolha "Débito em Conta - Transf. Online" você será direcionado para a página do Banco e realize o pagamento.</td>');
	$txtPagamento6 = utf8_encode('<td width="73%" height="34">Escolha "Boleto Bancário", imprima e pague em qualquer Banco até a data de vencimento.</td>');
	$txtPagamento7 = utf8_encode('<td width="73%" height="34">Escolha "Transferência Online (Shopline)" você será direcionado para a página do Banco e realize o pagamento.</td>');
	$txtCreditoOnline = utf8_encode('<td width="73%" height="34">Após a confirmação de seu pagamento, seu pin code (senha), será enviado ao seu Email Cadastrado.</td>');
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////// upgrade 2010-04-16 /////////// configura os 3 tipos de prazos ///////////////////////
	$txtPrazo1 = utf8_encode('<td width="73%" height="34" colspan="2"><b>Prazo de Entrega:</b> Imediata, podendo eventualmente atrasar devido à aprovação do Banco.</td>');
	$txtPrazo2 = utf8_encode('<td width="73%" height="34" colspan="2"><b>Prazo de Entrega:</b> Até 2 dias úteis, desde que seja informado corretamente os dados do comprovante.</td>');
	$txtPrazo3 = utf8_encode('<td width="73%" height="34" colspan="2"><b>Prazo de Entrega:</b> Até 2 dias úteis, dependendo de feriados e finais de semana.</td>');
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////


	
	$opcao = $_REQUEST['opcao'];
	$render .= '<table width="430px" border="0" cellspacing="0" cellpadding="0">';
	switch($opcao) {
		//Bradesco Transferencia Online
		case 'bscoTranOnline':
			$render .= '<tr valign="center">';
			$render .= $img1Cadastro;
			$render .= $txtCadastro;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img2Pedido;
			$render .= $txtPedido;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img3Pagamento;
			$render .= $txtPagamento1;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img4CreditoOnline;
			$render .= $txtCreditoOnline;
			$render .= '</tr>';
			///////////////////////////////upgrade 2010-04-16 ///// adiciona a tr que mostra e explica o prazo
			$render .= '<tr valing="center" >';
			//$render .= $prazoTag1;
			$render .= $txtPrazo1;
			$render .= '</tr>';
			///////////////////////////////////////////////////
			break;
		//Bradesco Visa Electron
		case 'bscoVisaElectron':
			$render .= '<tr valign="center">';
			$render .= $img1Cadastro;
			$render .= $txtCadastro;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img2Pedido;
			$render .= $txtPedido;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img3Pagamento;
			$render .= $txtPagamento2;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img4CreditoOnline;
			$render .= $txtCreditoOnline;
			$render .= '</tr>';
			$render .= '<tr valing="center">';
			//$render .= $prazoTag1;
			$render .= $txtPrazo1;
			$render .= '</tr>';
			break;
		//Bradesco Transferencia OffLine
		case 'bscoTranOffline':
			$render .= '<tr valign="center">';
			$render .= $img1Cadastro;
			$render .= $txtCadastro;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img2Pedido;
			$render .= $txtPedido;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img3Pagamento;
			$render .= $txtPagamento4;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img4Informa;
			$render .= $txtInforma;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img5CreditoOnline;
			$render .= $txtCreditoOnline;
			$render .= '</tr>';
			$render .= '<tr valing="center">';
			//$render .= $prazoTag1;
			$render .= $txtPrazo2;
			$render .= '</tr>';
			break;
		//Bradesco Boleto
		case 'bscoBoleto':
			$render .= '<tr valign="center">';
			$render .= $img1Cadastro;
			$render .= $txtCadastro;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img2Pedido;
			$render .= $txtPedido;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img3Pagamento;
			$render .= $txtPagamento6;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img4CreditoOnline;
			$render .= $txtCreditoOnline;
			$render .= '</tr>';
			$render .= '<tr valing="center">';
			//$render .= $prazoTag1;
			$render .= $txtPrazo3;
			$render .= '</tr>';
			break;
		//Banco do Brasil Transferencia Online
		case 'bbTranOnline':
			$render .= '<tr valign="center">';
			$render .= $img1Cadastro;
			$render .= $txtCadastro;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img2Pedido;
			$render .= $txtPedido;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img3Pagamento;
			$render .= $txtPagamento5;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img4CreditoOnline;
			$render .= $txtCreditoOnline;
			$render .= '</tr>';
			$render .= '<tr valing="center">';
			//$render .= $prazoTag1;
			$render .= $txtPrazo1;
			$render .= '</tr>';
			break;
		//Banco do Brasil Transferencia Offline
		case 'bbTranOffline':
			$render .= '<tr valign="center">';
			$render .= $img1Cadastro;
			$render .= $txtCadastro;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img2Pedido;
			$render .= $txtPedido;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img3Pagamento;
			$render .= $txtPagamento4;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img4Informa;
			$render .= $txtInforma;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img5CreditoOnline;
			$render .= $txtCreditoOnline;
			$render .= '</tr>';
			$render .= '<tr valing="center">';
			//$render .= $prazoTag1;
			$render .= $txtPrazo2;
			$render .= '</tr>';
			break;
		//Banco Itaú Transferencia Online
		case 'itauTranOnline':
			$render .= '<tr valign="center">';
			$render .= $img1Cadastro;
			$render .= $txtCadastro;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img2Pedido;
			$render .= $txtPedido;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img3Pagamento;
			$render .= $txtPagamento7;
			$render .= '</tr>';
			$render .= '<tr valign="center">';
			$render .= $img4CreditoOnline;
			$render .= $txtCreditoOnline;
			$render .= '</tr>';
			$render .= '<tr valing="center">';
			//$render .= $prazoTag1;
			$render .= $txtPrazo1;
			$render .= '</tr>';
			break;
	}
	$render .= '</table>';
}
$render .= '</body>';
$render .= '</html>';

echo $render;
?>