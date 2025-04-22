
<form style="padding: 20px;">
	<div id="container_apolice" class="container_apolice">
        <div class="container_title_apolice">
		    <h1 class="title_apolice">Suas informações</h1>
		</div>
		<div class="col_apolice_anterior">
            <div class="container-inputs">
				<label class="label_anterior">Data de inicio de vigência</label>
			    <input class="inputs_anterior" type="date" name="inicio_vigencia"><!-- colocar hora -->
			</div>
			<div class="container-inputs">
				<label class="label_apolice">CPNJ tomador</label>
			    <input type="text" class="inputs_anterior" name="cnpj_tomador"> 
			</div>
            <div class="container-inputs">
				<label class="label_anterior">CPNJ cliente</label>
			    <input class="inputs_anterior" type="text" name="cnpj_cliente">
			</div>
			<div class="container-inputs">
				<label class="label_anterior">Modalidade</label>
			    <input class="inputs_anterior" type="text" name="id_modalidade">
			</div>
            <div class="container-inputs">
				<label class="label_anterior">Importância segurada</label>
			    <input class="inputs_anterior" type="text" name="importancia_segurada">
			</div>
		</div>
		<div class="col_apolice_anterior">
            <div class="container-inputs">
				<label class="label_anterior">Data de final de vigência</label>
			    <input class="inputs_anterior" type="date" name="final_vigencia"><!-- colocar hora -->
			</div>
            <div class="container-inputs">
				<label class="label_anterior">Valor do contrato</label>
			    <input class="inputs_anterior" type="text" name="valor_contrato">
			</div>
            <div class="container-inputs">
				<label class="label_anterior">Setor privado?</label>
                <div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="privado" value="true">
					<label class="label_apolice_radio">Não</label>
					<input type="radio" name="privado" value="false">
				</div>
			</div>
		    <div class="container-inputs">
				<label class="label_anterior">Cobertura Trabalhista?</label>
                <div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="cobertura_trabalhista" value="true">
					<label class="label_apolice_radio">Não</label>
					<input type="radio" name="cobertura_trabalhista" value="false">
				</div>
			</div>
            <div class="container-inputs">
				<label class="label_anterior">Dedicação exclusiva?</label>
                <div>
				    <label class="label_apolice_radio">Sim</label>
					<input type="radio" name="dedicacao_exclusiva" value="true">
					<label class="label_apolice_radio">Não</label>
					<input type="radio" name="dedicacao_exclusiva" value="false">
				</div>
			</div>
		</div>
	</div>
</form>