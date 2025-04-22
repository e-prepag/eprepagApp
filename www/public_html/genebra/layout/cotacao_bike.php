
<form data-id="<?php echo $modeloGenebra;?>" id="seleciona" style="padding: 20px;" method="post" action="/game/pedido/passo-1.php">
	<div id="container_apolice" class="container_apolice">
        <div class="container_title_apolice">
		    <h1 class="title_apolice">Suas informações</h1>
		</div>
		<div class="col_apolice_anterior">
			<div class="container-inputs">
				<label class="label_apolice">CPF <span class="required-input">*</span></label>
			    <input type="text" class="inputs_apolice" data-tamanho="0" id="cpf" name="cpf">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_anterior">Data de nascimento <span class="required-input">*</span></label>
			    <input class="inputs_anterior" type="date" id="data_nascimento" name="data_nascimento"><!-- colocar hora -->
				<div></div>
			</div>
		</div>
		<div class="col_apolice_anterior">
		    <div class="container-inputs">
				<label class="label_anterior">Valor da bike</label>
			    <input class="inputs_anterior" type="text" id="valor_bike" name="valor_bike">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_anterior">CEP <span class="required-input">*</span></label>
			    <input class="inputs_anterior" type="text" id="cep" name="cep">
				<div></div>
			</div>
		</div>
	</div>
    <input type="hidden" name="acao" id="acao" value="a">
    <input type="hidden" name="mod" id="mod" value="">
	<button id="btn-send" class="">Enviar</button>
</form>
<script>
    $(document).ready(function(){
		function con(msg){
            console.log(msg);
        }
		
		 $.fn.extend({
            carregaConfiguracoes: function(){
               
                $("#cpf").on("focus", function(){               
					$(this).unmask();
				});

				$("#cpf").on("blur", function(){
                    $(this).val($(this).val().replaceAll(".", ""));
                    $(this).val($(this).val().replaceAll("-", ""));
					if($(this).val().length == 11){
						$(this).carregaMascara("000.000.000-00");
                        $(this).data("tamanho", 14);
					}else{
				   
					}
				});

		        $(".container-inputs > input").add(".container-inputs > select").add(".container-inputs > div > input").on("change input", function(event){
					let alerta = $("div."+$(this).attr("id"));
					$(this).css("border-color", "#ccc");
					alerta.html("");
				});
		
				$("#seleciona").on("submit", function(event){
					let retorno = $("#seleciona").validaCampos();
                    $("#mod").val($(this).data("id"));
					if(retorno.length > 0){
						$.each(retorno, function(index, value){
							let alerta = $(value.elementId).parent().children().last();
						    alerta.text(value.mensagem);
							$(value.elementId).css("border-color","red");	
							alerta.addClass((value.elementId).replace("#", ""));
							alerta.css({color:"red", padding: "5px 0"});
						});
						event.preventDefault();
					}
				});

                $("#cep").carregaMascara("00000-000");
			
            },
            carregaMascara: function(modelo){
                $(this).mask(modelo);
            },
            validaCampos: function(){
				
				let errors = new Array();

                function valida_cpf(cpf) {
					cpf = cpf.replace(/[^\d]+/g, '');
					if (cpf == '') return false;
					if (cpf.length != 11 ||
						cpf == "00000000000" ||
						cpf == "11111111111" ||
						cpf == "22222222222" ||
						cpf == "33333333333" ||
						cpf == "44444444444" ||
						cpf == "55555555555" ||
						cpf == "66666666666" ||
						cpf == "77777777777" ||
						cpf == "88888888888" ||
						cpf == "99999999999")
						return false;
					add = 0;
					for (i = 0; i < 9; i++)
						add += parseInt(cpf.charAt(i)) * (10 - i);
					rev = 11 - (add % 11);
					if (rev == 10 || rev == 11)
						rev = 0;
					if (rev != parseInt(cpf.charAt(9)))
						return false;
					add = 0;
					for (i = 0; i < 10; i++)
						add += parseInt(cpf.charAt(i)) * (11 - i);
					rev = 11 - (add % 11);
					if (rev == 10 || rev == 11)
						rev = 0;
					if (rev != parseInt(cpf.charAt(10)))
						return false;
					return true;
				}
				
				function getAge(dateString) {
					const today = new Date();
					const birthDate = new Date(dateString);
					let age = today.getFullYear() - birthDate.getFullYear();
					const m = today.getMonth() - birthDate.getMonth();
					
					if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
						age--;
					}
					
					return age;
				}
             
                if($("#cpf").val() == ""){
					let erro = { mensagem: "CPF está vazio", elementId: "#cpf"};
					errors.push(erro);
                }

                if($("#cpf").val().length < $("#cpf").data("tamanho") || $("#cpf").val().length > $("#cpf").data("tamanho")){
					let erro = { mensagem: "CPF não está com tamanho incorreto", elementId: "#cpf"};
					errors.push(erro);
                }

                if($("#cpf").val() != ""){
					if(!valida_cpf($("#cpf").val())){
						let erro = { mensagem: "CPF invalido", elementId: "#cpf"};
						errors.push(erro);
					}
                }
                /*if($("#valor_bike").val() == ""){
					let erro = { mensagem: "Valor da bike está vazio", elementId: "#valor_bike"};
					errors.push(erro);
                }*/

                if($("#cep").val() == ""){
					let erro = { mensagem: "CEP está vazio", elementId: "#cep"};
					errors.push(erro);
                }
				
				if($("#cep").val().length < 9){
					let erro = { mensagem: "CEP está com tamanho incorreto", elementId: "#cep"};
					errors.push(erro);
                }

				if($("#data_nascimento").val() == ""){
					let erro = { mensagem: "Data de nascimento está vazia", elementId: "#data_nascimento"};
					errors.push(erro);
				}
				
				if(getAge($("#data_nascimento").val()) < 18){
					let erro = { mensagem: "Necessário ter mais de 18 anos ou mais", elementId: "#data_nascimento"};
					errors.push(erro);
                }
				
				return errors;
            }
        });
		
        $(document).carregaConfiguracoes();
		
	});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
