
function exibeLogin() {		
	$(document).ready(function() {
		$(".box-sessao-usuario-msg").hide();
		$(".box-sessao-usuario-login").show();	
	});	
}

function jaLogado() {		
	$(document).ready(function() {
		$("div.box-ativacao-pin").css('width', '190px');
		$("div.box-ativacao-pin").css('margin-top', '25px');
		$("div.box-ativacao-pin").css('margin-right', '0px');
		$("div.box-ativacao-pin").css('padding-left', '5px');
		$("div.box-ativacao-pin").css('border-right', '1px solid #f7f4f4');
		$("div.box-ativacao-pin").css('float', 'right');
		
		$(".box-resumo-pedido-seu-saldo").css('display', 'block');
		$(".box-resumo-pedido-seu-saldo-valor").css('display', 'block');
		
		$(".box-sessao-usuario-login-sucesso").show();				
	});	
}

function naoLogado() {		
	$(document).ready(function() {
		$(".box-resumo-pedido-seu-saldo").css('display', 'block');
		$(".box-resumo-pedido-seu-saldo-valor").css('display', 'block');
	});	
}

function executaPagamento() {		
	$(document).ready(function() {
		//if ($(".box-sessao-usuario").css('display') == 'none') {
			$("div.box-adicionar-pin").hide();
			$("table.box-resumo-datagrid-pin").hide();	

			$("div.box-resumo-pedido-pagar").hide();
			$("div.box-resumo-pedido-msg-confirma-pagamento").show();	
			$("div.box-resumo-pins-utilizado").show();	

		//}		
	});	
}

function executaPagamentoNaoLogado() {		
	$(document).ready(function() {
		//if ($(".box-sessao-usuario").css('display') == 'none') {
			$("div.box-adicionar-pin").hide();
			$("table.box-resumo-datagrid-pin").hide();	

			$("div.box-resumo-pedido-pagar").hide();
			$("div.box-resumo-pedido-msg-confirma-pagamento").show();	
			$("div.box-resumo-pins-utilizado").show();	

			$(".box-sessao-usuario").hide();									
			$(".box-sessao-usuario-msg").hide();
			$(".box-sessao-usuario-login").hide();													
			
			$(".box-sessao-usuario-login-sucesso").hide();
			
		//}		
	});	
}

function executaPagamentoRollBack() {		
	$(document).ready(function() {
	//	if ($(".box-sessao-usuario").css('display') == 'none') {
			$("div.box-adicionar-pin").show();
			$("table.box-resumo-datagrid-pin").show();	

			$("div.box-resumo-pedido-pagar").show();
			$("div.box-resumo-pedido-msg-confirma-pagamento").hide();							
			$("div.box-resumo-pins-utilizado").hide();	
	//	}		
	});	
}

function executaPagamentoRollBackNaoLogado() {		
	$(document).ready(function() {
	//	if ($(".box-sessao-usuario").css('display') == 'none') {
			$("div.box-adicionar-pin").show();
			$("table.box-resumo-datagrid-pin").show();	

			$("div.box-resumo-pedido-pagar").show();
			$("div.box-resumo-pedido-msg-confirma-pagamento").hide();							
			$("div.box-resumo-pins-utilizado").hide();	

			$(".box-sessao-usuario").show();									
			$(".box-sessao-usuario-msg").show();
			$(".box-sessao-usuario-login").hide();													
	
			$(".box-sessao-usuario-login-sucesso").hide();
	//	}		
	});	
}

function clickAdicionar(valor) {		
	if(document.formAddPIN.verificationCode.value != "" && document.formAddPIN.verificationCode.value.length == 3) { 
		document.formListaPIN.pagto.value=valor;
		verificar_pin();
	} else { 
		document.formAddPIN.verificationCode.value=""; 
		document.formAddPIN.verificationCode.focus(); 
		alert('Informe o código da imagem.');
	}
}

function clickPagar() {		
	if(document.formAddPIN.verificationCode.value != "" && document.formAddPIN.verificationCode.value.length == 3) { 
		document.formListaPIN.pagto.value="1";
		return true;
	} else { 
		document.formAddPIN.verificationCode.value=""; 
		document.formAddPIN.verificationCode.focus(); 
		alert('Informe o código da imagem.'); 
		return false;
	}
}

function load_regradeuso() {
	$('#boxPopUpRegradeUso').load("/prepag2/commerce/conta/regrasdeuso.php").show();
}

function fecha() {
	$('#boxPopUpRegradeUso').hide();
}

$(function(){
    var leftBoxMapa = $(".box-ativacao-page").offset().left;
    $( ".mapa-epp" ).offset({ left: leftBoxMapa });
});