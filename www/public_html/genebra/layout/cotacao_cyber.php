
<form style="padding: 20px;">
	<div id="container_apolice" class="container_apolice">
        <div class="container_title_apolice">
		    <h1 class="title_apolice">Suas informações</h1>
		</div>
		<div class="col_apolice_anterior">
			<div class="container-inputs">
				<label class="label_apolice">CPF</label>
			    <input type="text" class="inputs_apolice" name="cpf"> 
			</div>
			<div class="container-inputs">
				<label class="label_anterior">Data de nascimento</label>
			    <input class="inputs_anterior" type="data" name="data_nascimento"><!-- colocar hora -->
			</div>
			<div class="container-inputs">
				<label class="label_anterior">Renda estimada</label>
			    <input class="inputs_anterior" type="text" name="renda_estimada">
			</div>
		</div>
		<div class="col_apolice_anterior">
		    <div class="container-inputs">
				<label class="label_anterior">Valor da cobertura</label>
			    <input class="inputs_anterior" type="text" name="valor_cobertura">
			</div>
			<div class="container-inputs">
				<label class="label_apolice">Sexo</label>
				<div>
				    <label class="label_apolice_radio">Masculino</label>
					<input type="radio" name="genero_cliente" value="true">
					<label class="label_apolice_radio">Feminino</label>
					<input type="radio" name="genero_cliente" value="false">
				</div>
			</div>
		</div>
	</div>
</form>