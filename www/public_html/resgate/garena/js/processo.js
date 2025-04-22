function token(token) {
   localStorage.setItem('token', token);
}
function getToken(){
   grecaptcha.ready(function(){
       grecaptcha.execute(); 
   });
} 
$( document ).ready(function() {
    getToken();
});

$("#confirma").on("click", function(){
       
		let conta = $("#container-data").find("#conta");
		let pin = $("#container-data").find("#codigo");
		if(pin.val() == ""){
			$(".alert").removeClass("d-none").addClass("alert-danger");
			$(".alert").fadeIn("slow");
			$(".alert").html("Por favor digite um PIN válido");
			$(".alert").delay(5000).fadeOut(1000);
			$(".diamond").css("color","red");
			$(pin).css("border-color","red").focus();
			$(pin).on("input", function(){
				$(".diamond").css("color","#40739e");
				$(this).css("border-color","rgb(206, 212, 218)");
			});
		}else if(conta.val() == ""){
			$(".alert").removeClass("d-none").addClass("alert-danger");
			$(".alert").fadeIn("slow");
			$(".alert").html("Por favor digite uma conta garena válida");
			$(".alert").delay(5000).fadeOut(1000);
			$(".icone-conta").css("color","red");
			$(conta).css("border-color","red").focus();
			$(conta).on("input", function(){
				$(".icone-conta").css("color","#40739e");
				$(this).css("border-color","rgb(206, 212, 218)");
			});
		}else{
			
			getToken(); 
			$.ajax({
				url: "https://www.e-prepag.com.br/ajax/garena/verificaProduto.php",
				method: "POST",
				data: {vde: 111111111, codigo: pin.val(), garena: conta.val(), valid: true, type: "pdv", verifica: true, token: localStorage.getItem('token')},
				beforeSend: function(){
					getToken();
					$("#confirma").html("Processando...");
					$("#confirma").prop("disabled", true);
				}
			}).done(function(res){
				let dados = JSON.parse(res);
				if(dados.hasOwnProperty('Erro')){
					$(".alert").removeClass("d-none").addClass("alert-danger");	
					$(".alert").text(dados.Erro); // Usa .text() para evitar XSS
					if($(".alert").hasClass("alert-success")){
						$(".alert").removeClass("alert-success");
					}
					$(".alert").fadeIn("slow");
					$(".alert").delay(5000).fadeOut(1000);
					$("#confirma").html("Resgatar");
					$("#confirma").prop( "disabled", false );
				}else{
					Swal.fire({
					  title: 'Confirmação de resgate',
					  html: 'Você deseja adicionar <b>'+dados.modelo+'</b> na conta <b>'+dados.usuario.nome+'</b> ?',
					  icon: 'question',
					  showDenyButton: true,
					  allowOutsideClick: false,
					  allowEscapeKey: false,
					  confirmButtonColor: '#28a745',
					  reverseButtons: true,
					  denyButtonColor: '#d33',
					  confirmButtonText: 'Confirmar',
					  denyButtonText: 'Cancelar'
					}).then((result) => {
					 
						if (result.isConfirmed) {
					
						   $.ajax({

								url: "https://www.e-prepag.com.br/ajax/garena/verificaProduto.php",
								method: "POST",
								data: { codigo: pin.val(), garena: conta.val(), type: "pdv", vde: 111111111, verifica: true, token: localStorage.getItem('token') },
								beforeSend: function(){
									
									$("#confirma").html("Processando...");
									$("#confirma").prop( "disabled", true );
									
								}
					
							}).done(function(result){
								let dadosJson = result;
								let retorno = JSON.parse(result);
								if(retorno.hasOwnProperty('Erro')){
									$(".alert").removeClass("d-none").addClass("alert-danger");
									$(".alert").text(retorno.Erro);
									if($(".alert").hasClass("alert-success")){
										$(".alert").removeClass("alert-success");
									}
									$(".alert").fadeIn("slow");
									$(".alert").delay(5000).fadeOut(1000);
									
									$("#confirma").html("Resgatar");
									$("#confirma").prop( "disabled", false );
								}else{
									/*$(".alert").html(retorno.Sucesso);
									if($(".alert").hasClass("alert-danger")){
										$(".alert").removeClass("alert-danger");
									}
									$(".alert").removeClass("d-none").addClass("alert-success");
									$(".alert").fadeIn("slow");
									$(".alert").delay(5000).fadeOut(1000);*/
									$("#confirma").html("Resgatar");
									$("#confirma").prop( "disabled", false );
									pin.val("");
									conta.val("");
									localStorage.setItem('info', dadosJson);
									
									//if($("#part").length && $("#part").html() != '0'){
										//window.location.href = "https://www.e-prepag.com.br/resgate/garena/comprovante.php?partner="+ $("#part").html();
									//}else{
										window.location.href = "https://www.e-prepag.com.br/resgate/garena/comprovante.php";
									//}
					
								}
							});	  
						}else if (result.isDenied) {		
							Swal.fire({
							  title: 'O resgate para o usuário foi cancelado!',
							  allowOutsideClick: false,
							  allowEscapeKey: false,
							  icon: 'info',
							  confirmButtonColor: '#28a745',
							  confirmButtonText: 'Fechar'
							});
							
							$("#confirma").html("Resgatar");
							$("#confirma").prop( "disabled", false );	
					   }
					});
				}
					
			}).fail(function(){
				$(".alert").removeClass("d-none").addClass("alert-danger");
				$(".alert").fadeIn("slow");
				$(".alert").html("Não foi possivel finalizar corretamente o resgate");
				$(".alert").delay(5000).fadeOut(1000);
				$("#confirma").html("Resgatar");
				$("#confirma").prop( "disabled", false );
			});
			
		}
});
	 

