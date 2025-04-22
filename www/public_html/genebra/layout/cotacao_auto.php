<?php
# 60 campos json
?>

<label class="label-title">Preencha as informações abaixo</label>
<hr>
<form data-id="<?php echo $modeloGenebra;?>" style="padding: 20px;" id="seleciona" method="post" action="/game/pedido/passo-1.php">
	<label for="renovacao" id="label_renovacao" class="label_anterior">Cotação de renovação?</label>
    <input type="checkbox" id="renovacao" name="renovacao">
	
	<div id="container_apolice_anterior" class="container_apolice_anterior">
	    <div class="container_title_apolice">
		    <h1 class="title_apolice">Informações da renovação</h1>
		</div>
		<div class="col_apolice_anterior">
			<div class="container-inputs">
				<label class="label_apolice">Data inicial de vigência anterior</label>
			    <input type="date" class="inputs_apolice" id="data_inicio_vigencia_anterior" name="data_inicio_vigencia_anterior"> <!-- colocar hora -->
			</div>
			<div class="container-inputs">
				<label class="label_anterior">Quantidade de sinistros</label>
			    <input class="inputs_anterior" min="0" max="1000" type="number" id="sinistros_anterior" name="sinistros_anterior">
			</div>
			<div class="container-inputs">
				<label class="label_anterior">Bônus anterior</label>
				<input class="inputs_anterior" min="0" max="10" type="number" id="bonus_anterior" name="bonus_anterior">
			</div>
		</div>
		<div class="col_apolice_anterior">
		    <div class="container-inputs">
				<label class="label_anterior">Data final de vigência anterior</label>
			    <input class="inputs_anterior" type="date" id="data_final_vigencia_anterior" name="data_final_vigencia_anterior"> <!-- colocar hora -->
			</div>
			<div class="container-inputs">
				<label class="label_anterior">Código de apólice anterior <span class="required-input">*</span></label>
			    <input class="inputs_anterior" type="text" id="ci" name="ci">
				<div></div>
			</div>
		</div>
	</div>
	<div class="container_apolice">
	    <div class="container_title_apolice">
		    <h1 class="title_apolice">Suas informações</h1>
		</div>
	    <div class="col_apolice">
			<div class="container-inputs">
				<label class="label_apolice">Nome <span class="required-input">*</span></label>
			    <input class="inputs_apolice" type="text" id="nome_cliente" name="nome_cliente">
				<div></div>
			</div>
			<div class="container-inputs">
			    <label class="label_apolice">Pessoal jurídica? <span class="required-input">*</span></label>
				<div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" class="tipo_pessoa_cliente" name="tipo_pessoa_cliente" value="1">
					<label class="label_apolice_radio">Não</label>
				    <input type="radio" class="tipo_pessoa_cliente" name="tipo_pessoa_cliente" value="0">
				</div>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">CPF/CNPJ <span class="required-input">*</span></label>
			    <input class="inputs_apolice" id="cpf_cnpj_cliente" data-tamanho="0" type="text" name="cpf_cnpj_cliente">
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">CEP <span class="required-input">*</span></label>
				<input class="inputs_apolice" id="cep" type="text" name="cep">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Cidade <span class="required-input">*</span></label>
				<input class="inputs_apolice" id="cidade" type="text" name="cidade_residencia">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">UF <span class="required-input">*</span></label>
				<input class="inputs_apolice" id="uf" type="text" name="uf_residencia">
				<div></div>
			</div>
			
		</div>
		<div class="col_apolice">
		    <div class="container-inputs">
				<label class="label_apolice">Estado civil <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="estado_civil_cliente" name="estado_civil_cliente">
                    <option value="">Selecione</option>
					<option value="1">Solteiro</option>
					<option value="2">União estável</option>
					<option value="3">Casado</option>
					<option value="4">Divorciado</option>
					<option value="5">Viúvo</option>
				</select>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Data de nascimento <span class="required-input">*</span></label>
				<input type="date" class="inputs_apolice" id="data_nascimento_cliente" name="data_nascimento_cliente"> <!-- colocar hora -->
				<div></div>
			</div>
            <div class="container-inputs">		
				<label class="label_apolice">Data da primeira habilitação</label>
				<input type="date" class="inputs_apolice" name="data_prim_habilitacao_cliente"> <!-- colocar hora -->
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Sexo <span class="required-input">*</span></label>
				<div>
				    <label class="label_apolice_radio">Masculino</label>
					<input type="radio" class="genero_cliente" name="genero_cliente" value="1">
					<label class="label_apolice_radio">Feminino</label>
					<input type="radio" class="genero_cliente" name="genero_cliente" value="0">
				</div>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Telefone <span class="required-input">*</span></label>
				<input class="inputs_apolice" id="fone" type="text" name="fone">
				<div></div>
			</div>
		</div>
	</div>
	<div class="container_apolice">
	    <div class="container_title_apolice">
		    <h1 class="title_apolice">Informações do veículo</h1>
		</div>
	    <div class="col_apolice">
			<div class="container-inputs">
				<label class="label_apolice">Data inicial de vigência <span class="required-input">*</span></label>
			    <input type="date" class="inputs_apolice" id="data_inicio_vigencia" name="data_inicio_vigencia"> <!-- colocar hora -->
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Tipo de cobertura <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="tipo_cobertura" name="tipo_cobertura">
                    <option value="">Selecione</option>
					<option value="1">Completa</option>
					<option value="2">Roubo/furto</option>
					<option value="3">RCF</option>
					<option value="4">roubo/furto/RCF</option>
				</select>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Ano de modelo do veículo <span class="required-input">*</span></label>
				<input class="inputs_apolice" min="1900" max="2100" type="number" id="ano_modelo" name="ano_modelo">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Ano de fabricação do veículo <span class="required-input">*</span></label>
				<input class="inputs_apolice" min="1900" max="2100" type="number" id="ano_fabricacao" name="ano_fabricacao">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Veículo financiado?</label>
				<div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="financiado" value="1">
					<label class="label_apolice_radio">Não</label>
				    <input type="radio" name="financiado" value="0">
				</div>
				<div></div>
			</div>
	    </div>
		<div class="col_apolice">
		    <div class="container-inputs">
				<label class="label_apolice">COD FABRICATE !!</label>
				<input class="inputs_apolice" type="number" name="cod_fabricante">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">COD FIPE !!</label>
				<input class="inputs_apolice" type="number" name="cod_fipe">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Número do chassi <span class="required-input">*</span></label>
				<input class="inputs_apolice" type="text" id="chassi" name="chassi">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Placa do veículo <span class="required-input">*</span></label>
				<input class="inputs_apolice" type="text" id="placa" name="placa">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Número de passageiros</label>
				<input class="inputs_apolice" type="number" name="passageiros">
				<div></div>
			</div>
		</div>
	</div>
	
	<div class="container_apolice">
	    <div class="container_title_apolice">
		    <h1 class="title_apolice">Informações do veículo</h1>
		</div>
	    <div class="col_apolice">
		    <div class="container-inputs">
				<label class="label_apolice">O veículo possui kit gás?</label>
				<div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="kit_gas" value="1">
					<label class="label_apolice_radio">Não</label>
				    <input type="radio" name="kit_gas" value="0">
				</div>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Valor do kit gás</label>
				<input class="inputs_apolice" type="number" name="valor_gas">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">O veículo possui blindagem?</label>
				<div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="blindagem" value="1">
					<label class="label_apolice_radio">Não</label>
				    <input type="radio" name="blindagem" value="0">
				</div>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Valor da blindagem</label>
				<input class="inputs_apolice" type="number" name="valor_blindagem">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Número de portas do veículo</label>
				<input class="inputs_apolice" type="number" name="num_portas">
				<div></div>
			</div>
		</div>
		<div class="col_apolice">
			<div class="container-inputs">
				<label class="label_apolice">Tipo de dispositivo anti-furto <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="codigo_antifurto" name="codigo_antifurto">
                    <option value="">Selecione</option>
					<option value="0">Nenhum</option>
					<option value="1">Alarme</option>
					<option value="2">Bloqueador</option>
					<option value="3">Rastreador</option>
				</select>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Tipo de isenção <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="tipo_isencao" name="tipo_isencao">
                    <option value="">Selecione</option>
					<option value="0">Nenhuma</option>
					<option value="1">PCD</option>
					<option value="2">Táxi</option>
				</select>
				<div></div>
		    </div>
			<div class="container-inputs">
				<label class="label_apolice">O condutor do veículo é menor que 26 anos?</label>
				<div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="jovem_condutor" value="1">
					<label class="label_apolice_radio">Não</label>
				    <input type="radio" name="jovem_condutor" value="0">
				</div>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Sexo do condutor</label>
				<div>
				    <label class="label_apolice_radio">Masculino</label>
					<input type="radio" name="jovem_genero" value="1">
					<label class="label_apolice_radio">Feminino</label>
				    <input type="radio" name="jovem_genero" value="0">
				</div>
				<div></div>
			</div>
		</div>
	</div>
	
	<div class="container_apolice">
	    <div class="container_title_apolice">
		    <h1 class="title_apolice">Informações de utilização</h1>
		</div>
	    <div class="col_apolice">
		    <div class="container-inputs">
				<label class="label_apolice">Usa o veículo para estudar?</label>
				<div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="usa_estudar" value="1">
					<label class="label_apolice_radio">Não</label>
				    <input type="radio" name="usa_estudar" value="0">
				</div>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Possui garagem no local do estudo?</label>
				<div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="garagem_estudo" value="1">
					<label class="label_apolice_radio">Não</label>
				    <input type="radio" name="garagem_estudo" value="0">
				</div>
				<div></div>
			</div>	
			<div class="container-inputs">
				<label class="label_apolice">CEP de pernoite <span class="required-input">*</span></label>
				<input class="inputs_apolice" type="text" id="cep_pernoite" name="cep_pernoite">
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Tipo local pernoite <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="tipo_local_pernoite" name="tipo_local_pernoite">
                    <option value="">Selecione</option>
					<option value="1">Garagem com portão eletrônico</option>
					<option value="2">Garagem sem portão eletrônico</option>
					<option value="3">Na rua</option>
				</select>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Quilometragem média anual</label>
				<input class="inputs_apolice" type="number" name="km_anual">
				<div></div>
			</div>
		</div>
		<div class="col_apolice">
		    <div class="container-inputs">
				<label class="label_apolice">Usa o veículo para trabalho?</label>
				<div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="usa_trabalhar" value="1">
					<label class="label_apolice_radio">Não</label>
				    <input type="radio" name="usa_trabalhar" value="0">
				</div>
				<div></div>
		    </div>
			<div class="container-inputs">
				<label class="label_apolice">Possui garagem no local de trabalho?</label>
				<div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="garagem_trabalho" value="1">
					<label class="label_apolice_radio">Não</label>
				    <input type="radio" name="garagem_trabalho" value="0">
				</div>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Tipo de utilização <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="tipo_utilizacao" name="tipo_utilizacao">
                    <option value="">Selecione</option>
					<option value="1">Lazer</option>
					<option value="2">Comercial</option>
					<option value="3">App/uber</option>
				</select>
				<div></div>
			</div>
			<div class="container-inputs">
				<label class="label_apolice">CEP de circulação <span class="required-input">*</span></label>
				<input class="inputs_apolice" type="text" id="cep_circulacao" name="cep_circulacao">
				<div></div>
			</div>
		</div>
	</div>
	
	<div class="container_apolice">
	    <div class="container_title_apolice">
		    <h1 class="title_apolice">Informações adicionais</h1>
		</div>
	    <div class="col_apolice">
			<div class="container-inputs">
				<label class="label_apolice">Nome do proprietário do veículo <span class="required-input">*</span></label>
				<input class="inputs_apolice" type="text" id="nome_proprietario" name="nome_proprietario">
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">O proprietário é pessoal jurídica? <span class="required-input">*</span></label>
				<div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" class="tipo_pessoa_proprietario" name="tipo_pessoa_proprietario" value="1">
					<label class="label_apolice_radio">Não</label>
				    <input type="radio" class="tipo_pessoa_proprietario" name="tipo_pessoa_proprietario" value="0">
				</div>
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">CPF/CNPJ do proprietário <span class="required-input">*</span></label>
				<input class="inputs_apolice" type="text" id="cpf_cnpj_proprietario" data-tamanho="0" name="cpf_cnpj_proprietario">
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">Estado civil do proprietário <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="estado_civil_proprietario" name="estado_civil_proprietario">
                    <option value="">Selecione</option>
					<option value="1">Solteiro</option>
					<option value="2">União estável</option>
					<option value="3">Casado</option>
					<option value="4">Divorciado</option>
					<option value="5">Viúvo</option>
				</select>
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">Data de nascimento do proprietário <span class="required-input">*</span></label>
				<input class="inputs_apolice" type="date" id="data_nascimento_proprietario" name="data_nascimento_proprietario"> <!-- colocar hora -->
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">Importância segurada para danos materiais</label>
				<input class="inputs_apolice" type="number" name="is_danos_materiais">
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">Importância segurada para danos corporais</label>
				<input class="inputs_apolice" type="number" name="is_danos_corporais">
				<div></div>
			</div>
		</div>
		<div class="col_apolice">
		    <div class="container-inputs">
				<label class="label_apolice">Importância segurada para morte de passageiros</label>
				<input class="inputs_apolice" type="number" name="is_app_morte">
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">Importância segurada para invalidez de passageiros</label>
				<input class="inputs_apolice" type="number" name="is_app_invalidez">
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">Tipo de cobertura para vidros <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="cob_vidros" name="cob_vidros">
                    <option value="">Selecione</option>
					<option value="1">Vidros básicos</option>
					<option value="2">Vidros completos</option>
					<option value="3">Vidros completos com logomarca</option>
				</select>
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">Tipo de cobertura para faróis <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="cob_farol" name="cob_farol">
                    <option value="">Selecione</option>
					<option value="1">Faróis básicos</option>
					<option value="2">Faróis completos</option>
				</select>
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">Valor da cobertura para depesas extraordinárias</label>
				<input class="inputs_apolice" type="number" name="cob_despesas_extra">
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">Tipo de assistência 24 horas <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="assist24hrs" name="assist24hrs">
                    <option value="">Selecione</option>
					<option value="1">Sem guincho</option>
					<option value="2">Com guincho/100km</option>
					<option value="3">Com guincho/300km</option>
					<option value="4">Com guincho/ilimitado</option>
				</select>
				<div></div>
			</div>
			
			<div class="container-inputs">
				<label class="label_apolice">Tipo de carro reserva <span class="required-input">*</span></label>
				<select class="inputs_apolice" id="carro_reserva" name="carro_reserva">
                    <option value="">Selecione</option>
					<option value="1">Sem ar/7 dias</option>
					<option value="2">Com ar/7 dias</option>
					<option value="3">Luxo/7 dias</option>
					<option value="4">Sem ar/14 dias</option>
					<option value="5">Com ar/14 dias</option>
					<option value="6">Luxo/14 dias</option>
					<option value="8">Sem ar/28 dias</option>
					<option value="9">Com ar/28 dias</option>
					<option value="10">luxo/28 dias</option>
				</select>
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
                 
			    $("#renovacao").on("click", function(){
					if($(this).prop("checked")){
						if(window.innerWidth < 767){
							$("#container_apolice_anterior").css("display","block");
						}else{
							$("#container_apolice_anterior").css("display","flex");
						}
					}else{
						$("#container_apolice_anterior").css("display","none");
					}
				});

                $("#cpf_cnpj_cliente").add("#cpf_cnpj_proprietario").on("focus", function(){               
					$(this).unmask();
				});

				$("#cpf_cnpj_cliente").add("#cpf_cnpj_proprietario").on("blur", function(){
                    $(this).val($(this).val().replaceAll(".", ""));
                    $(this).val($(this).val().replaceAll("/", ""));
                    $(this).val($(this).val().replaceAll("-", ""));
					let alerta = $(this).parent().children().last();
					if($(this).val().length == 11){
						$(this).carregaMascara("000.000.000-00");
                        $(this).data("tamanho", 14);
					}else if($(this).val().length == 14){
						$(this).carregaMascara("00.000.000/0000-00");
                        $(this).data("tamanho", 18);
					}else{
				   
					}
				});

				$("#cep").on("blur", function(){
					var cep = $(this).val();
					if(cep != ""){
						$.ajax({
							url: "https://viacep.com.br/ws/"+cep+"/json/"
						}).done(function(data){
							let dadosCep = data;
							if(!dadosCep.hasOwnProperty("erro")){
								$("#cidade").val(dadosCep.localidade);
								$("#uf").val(dadosCep.uf);
							}else{
								$("#cidade").val("");
								$("#uf").val("");
							}
						}).fail(function(data){
							$("#cidade").val("");
							$("#uf").val("");
						});
					}
				});
		
		        $(".container-inputs > input").add(".container-inputs > select").add(".container-inputs > div > input").on("change input", function(event){
					let alerta = "";
					if($(this).attr("type") == "radio"){
						alerta = $("div."+$(this).attr("class"));
					}else{
						alerta = $("div."+$(this).attr("id"));
						$(this).css("border-color", "#ccc");
					}
					alerta.html("");
				});
		
				$("#seleciona").on("submit", function(event){
					let retorno = $("#seleciona").validaCampos();
                    $("#mod").val($(this).data("id"));
					if(retorno.length > 0){
						$.each(retorno, function(index, value){
							let alerta = $(value.elementId).parent().children().last();
							if(alerta.attr("type") == "radio"){
								alerta = $(value.elementId).parent().parent().children().last();
								alerta.text(value.mensagem);
								alerta.addClass((value.elementId).replace(".", ""));
							}else{
								alerta.text(value.mensagem);
							    $(value.elementId).css("border-color","red");	
								alerta.addClass((value.elementId).replace("#", ""));
							}	
							alerta.css({color:"red", padding: "5px 0"});
						});
						event.preventDefault();
					}
				});

                $("#cep").carregaMascara("00000-000");
				$("#cep_pernoite").carregaMascara("00000-000");
				$("#cep_circulacao").carregaMascara("00000-000");

            },
            carregaMascara: function(modelo){
                $(this).mask(modelo);
				var SPMaskBehavior = function (val) {
				  return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
				},
				spOptions = {
				  onKeyPress: function(val, e, field, options) {
					  field.mask(SPMaskBehavior.apply({}, arguments), options);
					}
				};
				$("#fone").mask(SPMaskBehavior, spOptions);
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
				
				function valida_cnpj(cnpj) {
				 
					cnpj = cnpj.replace(/[^\d]+/g,'');
				 
					if(cnpj == '') return false;
					 
					if (cnpj.length != 14)
						return false;
			
					if (cnpj == "00000000000000" || 
						cnpj == "11111111111111" || 
						cnpj == "22222222222222" || 
						cnpj == "33333333333333" || 
						cnpj == "44444444444444" || 
						cnpj == "55555555555555" || 
						cnpj == "66666666666666" || 
						cnpj == "77777777777777" || 
						cnpj == "88888888888888" || 
						cnpj == "99999999999999")
						return false;
						 
					// Valida DVs
					tamanho = cnpj.length - 2
					numeros = cnpj.substring(0,tamanho);
					digitos = cnpj.substring(tamanho);
					soma = 0;
					pos = tamanho - 7;
					for (i = tamanho; i >= 1; i--) {
					  soma += numeros.charAt(tamanho - i) * pos--;
					  if (pos < 2)
							pos = 9;
					}
					resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
					if (resultado != digitos.charAt(0))
						return false;
						 
					tamanho = tamanho + 1;
					numeros = cnpj.substring(0,tamanho);
					soma = 0;
					pos = tamanho - 7;
					for (i = tamanho; i >= 1; i--) {
					  soma += numeros.charAt(tamanho - i) * pos--;
					  if (pos < 2)
							pos = 9;
					}
					resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
					if (resultado != digitos.charAt(1))
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
             
				if($("#renovacao").prop("checked")){
                    if($("#ci").val() == ""){
						let erro = { mensagem: "Código de apólice anterior está vazio", elementId: "#ci"};
					    errors.push(erro);
                    }
				}

                if($("#nome_cliente").val() == ""){
					let erro = { mensagem: "Nome está vazio", elementId: "#nome_cliente"};
					errors.push(erro);
                }

                if($("#estado_civil_cliente").val() == ""){
					let erro = { mensagem: "Estado civil está vazio", elementId: "#estado_civil_cliente"};
					errors.push(erro);
                }

                if($("#data_nascimento_cliente").val() == ""){
					let erro = { mensagem: "Data de nascimento está vazia", elementId: "#data_nascimento_cliente"};
					errors.push(erro);
                }
				
				if(getAge($("#data_nascimento_cliente").val()) < 18){
					let erro = { mensagem: "Necessário ter mais de 18 anos ou mais", elementId: "#data_nascimento_cliente"};
					errors.push(erro);
                }

                let genero = 0;
                $(".genero_cliente").each(function(index, element){
                    if($(element).prop("checked")){
                        genero = 1;
                    }
                }); 
				
				if(genero == 0){
					let erro = { mensagem: "Sexo está vazio", elementId: ".genero_cliente"};
					errors.push(erro);
				}

                if($("#fone").val() == ""){
					let erro = { mensagem: "Telefone está vazio", elementId: "#fone"};
					errors.push(erro);
                }

                let tipo_pessoa = 0;
                $(".tipo_pessoa_cliente").each(function(index, element){
                    if($(element).prop("checked")){
                        tipo_pessoa = 1;
                    }
                });
				
				if(tipo_pessoa == 0){
					let erro = { mensagem: "Tipo pessoa jurídica está vazia", elementId: ".tipo_pessoa_cliente"};
					errors.push(erro);
				}

                if($("#cpf_cnpj_cliente").val() == ""){
					let erro = { mensagem: "CPF/CNPJ está vazio", elementId: "#cpf_cnpj_cliente"};
					errors.push(erro);
                }
				
                if($("#cpf_cnpj_cliente").val().length < $("#cpf_cnpj_cliente").data("tamanho") || $("#cpf_cnpj_cliente").val().length > $("#cpf_cnpj_cliente").data("tamanho")){
					let erro = { mensagem: "CPF/CNPJ não está com tamanho incorreto", elementId: "#cpf_cnpj_cliente"};
					errors.push(erro);
                }

                if($("#cpf_cnpj_cliente").val().length == "14"){
					if(!valida_cpf($("#cpf_cnpj_cliente").val())){
						let erro = { mensagem: "CPF invalido", elementId: "#cpf_cnpj_cliente"};
						errors.push(erro);
					}
                }else if($("#cpf_cnpj_cliente").val().length == "18"){
					if(!valida_cnpj($("#cpf_cnpj_cliente").val())){
						let erro = { mensagem: "CNPJ invalido", elementId: "#cpf_cnpj_cliente"};
						errors.push(erro);
					}
                }
            
                if($("#cep").val() == ""){
					let erro = { mensagem: "CEP está vazio", elementId: "#cep"};
					errors.push(erro);
                }

                if($("#cep").val().length < 9){
					let erro = { mensagem: "CEP está com tamanho incorreto", elementId: "#cep"};
					errors.push(erro);
                }

                if($("#cidade").val() == ""){
					let erro = { mensagem: "Cidade está vazia", elementId: "#cidade"};
					errors.push(erro);
                }

                if($("#uf").val() == ""){
					let erro = { mensagem: "UF está vazia", elementId: "#uf"};
					errors.push(erro);
                }

                if($("#data_inicio_vigencia").val() == ""){
					let erro = { mensagem: "Data de vigência está vazia", elementId: "#data_inicio_vigencia"};
					errors.push(erro);
                }

                if($("#tipo_cobertura").val() == ""){
					let erro = { mensagem: "Tipo de cobertura está vazia", elementId: "#tipo_cobertura"};
					errors.push(erro);
                }

                if($("#ano_modelo").val() == ""){
					let erro = { mensagem: "Ano de modelo está vazio", elementId: "#ano_modelo"};
					errors.push(erro);
                }

                if($("#ano_fabricacao").val() == ""){
					let erro = { mensagem: "Ano de fabricação está vazio", elementId: "#ano_fabricacao"};
					errors.push(erro);
                }

                if($("#chassi").val() == ""){
					let erro = { mensagem: "Chassi está vazio", elementId: "#chassi"};
					errors.push(erro);
                }

                if($("#placa").val() == ""){
					let erro = { mensagem: "Placa está vazio", elementId: "#placa"};
					errors.push(erro);
                }

                if($("#codigo_antifurto").val() == ""){
					let erro = { mensagem: "Tipo de dispositivo anti-furto está vazio", elementId: "#codigo_antifurto"};
					errors.push(erro);
                }

                if($("#tipo_isencao").val() == ""){
					let erro = { mensagem: "Tipo de isenção está vazio", elementId: "#tipo_isencao"};
					errors.push(erro);
                }

                if($("#cep_pernoite").val() == ""){
					let erro = { mensagem: "CEP pernoite está vazio", elementId: "#cep_pernoite"};
					errors.push(erro);
                }
				
				if($("#cep_pernoite").val().length < 9){
					let erro = { mensagem: "CEP pernoite está com tamanho incorreto", elementId: "#cep_pernoite"};
					errors.push(erro);
                }

                if($("#tipo_local_pernoite").val() == ""){
					let erro = { mensagem: "Local pernoite está vazio", elementId: "#tipo_local_pernoite"};
					errors.push(erro);
                }

                if($("#tipo_utilizacao").val() == ""){
					let erro = { mensagem: "Tipo de utilização está vazio", elementId: "#tipo_utilizacao"};
					errors.push(erro);
                }

                if($("#cep_circulacao").val() == ""){
					let erro = { mensagem: "CEP de circulação está vazio", elementId: "#cep_circulacao"};
					errors.push(erro);
                }
				
				if($("#cep_circulacao").val().length < 9){
					let erro = { mensagem: "CEP circulação está com tamanho incorreto", elementId: "#cep_circulacao"};
					errors.push(erro);
                }

                if($("#nome_proprietario").val() == ""){
					let erro = { mensagem: "Nome do proprietário está vazio", elementId: "#nome_proprietario"};
					errors.push(erro);
                }

                let tipo_pessoa_proprietario = 0;
                $(".tipo_pessoa_proprietario").each(function(index, element){
                    if($(element).prop("checked")){
                        tipo_pessoa_proprietario = 1;
                    }
                }); 
				
				if(tipo_pessoa_proprietario == 0){
					let erro = { mensagem: "Pessoa jurídica proprietário está vazio", elementId: ".tipo_pessoa_proprietario"};
					errors.push(erro);
				}
				
				if($("#cpf_cnpj_proprietario").val() == ""){
					let erro = { mensagem: "CPF/CNPJ do proprietário está vazio", elementId: "#cpf_cnpj_proprietario"};
					errors.push(erro);
				}
				
				if($("#cpf_cnpj_proprietario").val().length < $("#cpf_cnpj_proprietario").data("tamanho") || $("#cpf_cnpj_proprietario").val().length > $("#cpf_cnpj_proprietario").data("tamanho")){
					let erro = { mensagem: "CPF/CNPJ do proprietário não está com tamanho incorreto", elementId: "#cpf_cnpj_proprietario"};
					errors.push(erro);
                }

                if($("#cpf_cnpj_proprietario").val().length == "14"){
					if(!valida_cpf($("#cpf_cnpj_proprietario").val())){
						let erro = { mensagem: "CPF invalido", elementId: "#cpf_cnpj_proprietario"};
						errors.push(erro);
					}
                }else if($("#cpf_cnpj_proprietario").val().length == "18"){
					if(!valida_cnpj($("#cpf_cnpj_proprietario").val())){
						let erro = { mensagem: "CNPJ invalido", elementId: "#cpf_cnpj_proprietario"};
						errors.push(erro);
					}
                } 

                if($("#estado_civil_proprietario").val() == ""){
					let erro = { mensagem: "Estado civil do proprietário está vazio", elementId: "#estado_civil_proprietario"};
					errors.push(erro);
				}
				
				if($("#data_nascimento_proprietario").val() == ""){
					let erro = { mensagem: "Data de nascimento do proprietário está vazia", elementId: "#data_nascimento_proprietario"};
					errors.push(erro);
				}
				
				if(getAge($("#data_nascimento_proprietario").val()) < 18){
					let erro = { mensagem: "Necessário ter mais de 18 anos ou mais", elementId: "#data_nascimento_proprietario"};
					errors.push(erro);
                }
				
				if($("#cob_vidros").val() == ""){
					let erro = { mensagem: "Cobertura do vidro está vazia", elementId: "#cob_vidros"};
					errors.push(erro);
				}
				
				if($("#cob_farol").val() == ""){
					let erro = { mensagem: "Cobertura do farol está vazia", elementId: "#cob_farol"};
					errors.push(erro);
				}
				
				if($("#assist24hrs").val() == ""){
					let erro = { mensagem: "Tipo de assistência está vazia", elementId: "#assist24hrs"};
					errors.push(erro);
				}
				
				if($("#carro_reserva").val() == ""){
					let erro = { mensagem: "Carro reserva está vazio", elementId: "#carro_reserva"};
					errors.push(erro);
				}
				
				return errors;
            }
        });
		
        $(document).carregaConfiguracoes();	
	});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
