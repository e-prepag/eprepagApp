<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>

<style>
    .coluna{
		display: flex;
		justify-content: center;
		margin-bottom: 10px;
	}
	.input{
		width: auto;
	}
	.label-text{
		margin-right: 20px;
		width: 140px;
		text-align: right;
	}
	.linha{
		border: 2px solid black;
		width: 90%;
	}
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="row">
     <h1 class="text-center">Cadastro de usuário</h1>
	 <hr class="linha">
	 <form action="ajaxCadastra.php" method="POST" id="form-info">
	      <div class="coluna">
		       <label class="label-text">Login &#128100;</label>
			   <input class="form-control input" type="text" name="login" id="login" placeholder="Digite o login">
		  </div>
		  <div class="coluna">
		       <label class="label-text">E-mail &#128231</label>
			   <input class="form-control input" type="email" name="email" id="email" placeholder="Digite o E-mail">
		  </div>
		   <div class="coluna">
		       <label class="label-text">Confirmar E-mail &#128231</label>
			   <input class="form-control input" type="email" name="confemail" id="confemail" placeholder="Confirme o E-mail">
		  </div>
		  <div class="coluna">
		       <label class="label-text">Senha &#128272;</label>
			   <input class="form-control input" type="text" name="passw" id="passw" placeholder="Digite a senha">
		  </div>
		  <div class="coluna">
		       <label class="label-text">Confirmar senha &#128272;</label>
			   <input class="form-control input" type="text" name="confpassw" id="confpassw" placeholder="Confirme a senha">
		  </div>
		  <div class="coluna">
		       <label class="label-text">Tipo de acesso &#128187;</label>
			   <select class="form-control input" name="tipo" id="tipo">
			           <option value="">Selecione um tipo</option>
			           <option value="AT">Atendente</option>
					   <option value="PU">Publisher</option>
			   </select>
		  </div>
		  <div class="coluna">
		       <label class="right10">Visualiza informações (Login, id, e-mail) na listagem &#128203;</label>
			   <input class="" type="checkbox" name="check" value="S" id="check">
		  </div>
		  <div class="coluna">
		  	   <button class="btn btn-success add-user" type="button">Cadastrar</button>
		  </div>
	 </form>
</div>
<script>
     $(".add-user").click((e) => {
		 let elementsData = $("#form-info").find(".input");
		 let error = false;
		 let num = 0;
		 $.each(elementsData, (index, data) => {
			   let element = $(data);
			   					   			  
			   if(element.val() == ""){
				   element.css("border", "1px solid red");
				   if(num == 0){
					   Swal.fire(
						  'Processo interrompido!',
						  'Preenchar os campos vazios',
						  'error'
					   )
					   num++;
				   }
				   error = true;
			   }else{
				   
				   if(element.attr("id") == "login" || element.attr("id") == "tipo"){
					   element.css("border", "1px solid #ccc");
				   }
				   
				   if(element.attr("id") == "email"){
					   if(element.val().indexOf("@") == -1 || element.val().indexOf(".") == -1){
						   element.css("border", "1px solid red");
						   error = true;
						   Swal.fire(
							  'Processo interrompido!',
							  'E-mail invalido',
							  'error'
						   )
					   }else{
						   element.css("border", "1px solid #ccc");
					   }
				   }
				   
				   	if(element.attr("id") == "confemail"){
					   if(element.val() != $("#email").val()){
						   element.css("border", "1px solid red");
						   error = true;
						   Swal.fire(
							  'Processo interrompido!',
							  'A confirmação está diferente do e-mail digitado',
							  'error'
						   )
					   }else{
						   element.css("border", "1px solid #ccc");
					   }
				   }
				   
				   if(element.attr("id") == "passw"){
					   if(element.val().length < 5){
						   element.css("border", "1px solid red");
						   Swal.fire(
							  'Processo interrompido!',
							  'A senha deve ser maior que 5 caracteres',
							  'error'
						   )
						   error = true;
					   }else{
						   element.css("border", "1px solid #ccc");
					   }
				   }
				   
				   if(element.attr("id") == "confpassw"){
					   if(element.val() != $("#passw").val()){
						   element.css("border", "1px solid red");
						   Swal.fire(
							  'Processo interrompido!',
							  'A confirmação de senha está diferente da digitada',
							  'error'
						   )
						   error = true;
					   }else{
						   element.css("border", "1px solid #ccc");
					   }
				   }
			   }
		 });
		 
		 if(error == false){
			   //let res = $("#form-info").serialize();
			   $.ajax({
				   url: "ajaxCadastra.php",
				   method: "POST",
				   data: $("#form-info").serialize()  
			   }).done(function(data){
				   let dados = JSON.parse(data);
				   if(dados.type == 'error'){
					   Swal.fire(
						  'Processo interrompido!',
						  dados.msg,
						  'error'
						)
				   }else{
					   Swal.fire(
						  'Processo finalizado!',
						  dados.msg,
						  'success'
					   )
				   }
			   });
	     }
		 
		 //console.log(elementCheck);
		 
	 });
</script>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>