<?php 
	require_once '/www/includes/constantes.php';
	require_once $raiz_do_projeto."backoffice/includes/topo.php";
	
	$nome_operador = $_SESSION["userlogin_bko"];
	$dir = scandir("/www/includes/templates");

	$bloqueados = [
		 "SenhaIntegracao.html", "SenhaExMoney.html", "RecargaRedeSim.html", "PedidoRegistradoInt.html", "PedidoRegistradoEx.html", 
		 "PedidoRegistrado.html", "PedidoCancelado.html", "FechamentoFinanceiro.html", "DepositoOfertas.html", "ComprasNaoConcluidas.html", 
		 "CompraProcessadaInt.html", "CompraProcessadaEx.html", "CompraProcessada.html", "CieloLiberado.html", "AlteracaoSenha.html",
		 "AlteracaoEmail.html", "AlteracaoCadastro.html", "2FA.html", "CompraB2C.html", "DadosInsuficientes.html"
	];
				 
	$contexto = [ 
	     "AdicaoSaldoLan.html" => "Confirmação de pedido de adição de saldo em conta E-Prepag.",
	     "AlteracaoCadastroLH.html" => "Alerta de alteração de informações do cadastro (E-mail).",
	     "AlteracaoLoginLH.html" => "Alerta de alteração de informações do cadastro (Login).",
		 "AlteracaoSenhaLH.html" => "Alerta de alteração de informações do cadastro (Senha).",
		 "BoletoParaPagamentoLanPos.html" => "Alerta de boleto disponível para pagamento das vendas feitas pelo PDV pós-pago.",
		 "CadastroLAN.html" => "Alerta de cadastro concluido, em periodo de análise pelo time E-Prepag.",
		 "ChaveMestra.html" => "Envio de chave mestra, para que o PDV possa utilizar na hora da venda em caso de dispositivo diferente para se autenticar.",
		 "CompraPontoVenda.html" => "Alerta de compra quando um PDV efetua uma venda e encaminha o pin para o cliente por e-mail.",
		 "DadosInsuficientes.html" => "Alerta para informar alguns dados que ainda estão pendentes e precisam ser encaminhados para que o cadastro seja concluído com sucesso (desativado).",
		 "EsqueciSenhaLan.html" => "Alerta disparado quando o PDV solicita uma troca de senha da conta E-Prepag.",
		 "LanAprovada.html" => "Alerta disparado quando o PDV finaliza o cadastro na E-Prepag e passa por todas as validações.",
		 "Onboarding-de-boas-vindas.html" => "Link para validação de documentos e informações ligadas ao PDV nos serviços CAF.",
		 "PedidoNegadoLan.html" => "Alerta de cadastro negado na E-Prepag.",
		 "RecuperaSenhaLH.html" => "Alerta disparado quando o PDV solicita uma troca de senha da conta E-Prepag.",
		 "RetornarContato.html" => "Alerta para informar alguns dados que ainda estão pendentes e precisam ser encaminhados para que o cadastro seja concluído com sucesso.",
		 "SaldoMinimoLH.html" => "Alerta para informar que saldo está baixo.",
		 "VendaProcessadaLH.html" => "Alerta para informar que a venda foi concluida e informa o produto adquirido.",
		 "Operador.html" => "Alerta de Cadastro de Operador de PDV.<br><span style='font-size: 13px;'>Para - daniela.oliveira@e-prepag.com.br ;glaucia@e-prepag.com.br<br></span>"
	];			 
				 
				 
	echo "<h1 class='titulo'>E-mails disparados para PDVs</h1>"; 
	 
    foreach($dir as $key => $html){
		if($html != "." && $html != ".." && (strpos($html, "Gamer") === false && strpos($html, "money") === false) && !in_array($html, $bloqueados)){
			echo "<div class='lay'><div class='lay-container'>". file_get_contents("/www/includes/templates/".$html)."</div>
			<div class='legends-container'><span class='legends'><b>Nome Layout:</b> ".$html."</span><span class='legends'><b>Descrição:</b> ".$contexto[$html]."</span></div></div>";
		}
	}
?>
<style>
    .titulo{
		text-align: center;
		color: black;
	    font-size: 23px;
	}
    .lay{
		margin: 18px 10px;
		border: 1px solid #ccc;
        padding: 10px;
	}
	.lay-container{
		margin-bottom: 18px;
	}
	.legends-container{
		text-align: center;
	}
	.legends{
		padding: 8px;
		background-color: #eee;
		font-size: 15px;
		margin: 10px;
		display: block;
	}
	
</style>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>