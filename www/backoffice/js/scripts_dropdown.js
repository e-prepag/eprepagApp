//JavaScript para o dropdown com o passo a passo de acordo banco
$(document).ready(function () {
	
	// Oculta os passos
	$('#passos').hide();

	//Oculta a DIV do DropDown
	$('#dropDown').hide();
	//Atribui o id da div a uma variavel
	var dropDown = $('#dropDown'); 
	var passos = $('#passos');
	
	//No primeiro click mostra a div no segundo oculta
	$('#btnDropDown').click(function(){
		dropDown.toggle();
	}); 
	
	//Muda a cor do background quando o mouse esta em cima da opção.
	$('#dropDown li').hover(
		function (){
			$(this).css('background-color', '#BEBEBE');
		},
		function (){
			$(this).css('background-color', '#FFFFFF');
		}
	)

	// Quando a opção é escolhida. 
	//	Mostra o logo correspondente, altera a opção selecionada do dropdown e depois executa o ajax que retorna os passos.
	$('#dropDown li').click(function(){
		dropDown.hide();
		passos.show();

		id = $(this).attr('id');

		if (id == 'bscoTranOnline'){
			$('#logoBanco img').not('#logoBradesco').hide();
			$('#logoBradesco').show();
			$('#btnDropDown').attr({src: '../imagens/opcoesdebancobscoTranOnline.jpg'});
		}
		
		if (id == 'bscoVisaElectron'){
			$('#logoBanco img').not('#logoBradesco, #logoVisaElectron').hide();
			$('#logoBradesco, #logoVisaElectron').show();
			$('#btnDropDown').attr({src: '../imagens/opcoesdebancobscoVisaElectron.jpg'});
		}
		
		if (id == 'bscoTranOffline'){
			$('#logoBanco img').not('#logoBradesco, #logoBB').hide();
			$('#logoBradesco, #logoBB').show();
			$('#btnDropDown').attr({src: '../imagens/opcoesdebancobscoTranOffline.jpg'});
		}
		
		if (id == 'bscoBoleto'){
			$('#logoBanco img').not('#logoBradesco, #logoBoleto').hide();
			$('#logoBradesco, #logoBoleto').show();
			$('#btnDropDown').attr({src: '../imagens/opcoesdebancobscoBoletoComCadastro.jpg'});
		}
		
		if (id == 'bbTranOnline'){
			$('#logoBanco img').not('#logoBB').hide();
			$('#logoBB').show();
			$('#btnDropDown').attr({src: '../imagens/opcoesdebancobbTranOnline.jpg'});
		}
		
		if (id == 'bbTranOffline'){
			$('#logoBanco img').not('#logoBradesco, #logoBB').hide();
			$('#logoBradesco, #logoBB').show();
			$('#btnDropDown').attr({src: '../imagens/opcoesdebancobbTranOffline.jpg'});
		}
		
		if (id == 'itauTranOnline'){
			$('#logoBanco img').not('#logoItau').hide();
			$('#logoItau').show();
			$('#btnDropDown').attr({src: '../imagens/opcoesdebancoItauTranOnline.jpg'});
		}
		
		// Processa as opções e retorna os passos.
		$.ajax({
			type: "POST",
			url: "../creditos/ajax/ajaxProcessaPassos.php",
			data: "opcao="+$(this).attr('id'),
			success: function(html){
				$('#passos').html(html);
			},
			error: function(){
				alert('Erro no ajax.')
			}
		});
	});
	
});
