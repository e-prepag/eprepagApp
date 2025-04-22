<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
   .title_gamers{
	   font-size: 20px;
	   font-weight: bold;
	   text-align: center;
   }
</style>
<h1 class="title_gamers">Gamers Bloqueados</h1>
<button class="btn btn-success btn-reload" style="margin-bottom: 10px;">Atualizar dados</button>
<table id="table" class="display hover stripe cell-border" style="width:100%;text-align: center;">
	<thead>
		<tr>
			<th>Código do registro</th>
			<th>Login</th>
			<th>Ip</th>
			<th>Data da tentativa</th>
			<th>Quantidade de tentativa</th>
			<th>Ação</th>
		</tr>
	</thead>
</table>
 
<script src="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.js"></script>
<script>
	$(document).ready(function () {
		let table = $('#table').DataTable({
			ajax: '../ajax/gamer/ajaxGamersBloqueados.php?acao=listar',
			columns: [
				{ data: 'codigo' },
				{ data: 'login' },
				{ data: 'ip' },
				{ data: 'data_requisicao' },
				{ data: 'qtde' },
				{ data: 'acao' }
			],
			language: {
                "zeroRecords": "Não foram encontrados registros",
				"lengthMenu":  "Mostrar _MENU_ linhas",
                "info": "Mostrando a página _PAGE_ de _PAGES_",
                "infoEmpty": "Dados inexistentes",
                "infoFiltered": "(filtro aplicado em _MAX_ registros)",
                "sSearch": "Pesquisar",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Próximo",
                }
            }
		});
		
		$(".btn-reload").on("click", function(){
			table.ajax.reload();
			Swal.fire({
			  position: 'top-end',
			  icon: 'success',
			  title: "Dados da tabela atualizados com sucesso",
			  showConfirmButton: false,
			  timer: 1500
			});
		});
		
		$(document).on("click", ".btn-liberar", function(){
			
			let button = $(this);
			$.ajax({
				url:"../ajax/gamer/ajaxGamersBloqueados.php?acao=apagar",
				method: "POST",
				data: {codigo: button.data().codigo}
				
			}).done(function(message){
				if(message == "Bloqueio liberado com sucesso"){
					table.ajax.reload();
					Swal.fire({
					  position: 'top-end',
					  icon: 'success',
					  title: message,
					  showConfirmButton: false,
					  timer: 1500
					});
				}else{
					Swal.fire({
					  position: 'top-end',
					  icon: 'error',
					  title: message,
					  showConfirmButton: false,
					  timer: 1500
					});
				}
			});
			
		});
	});
</script>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>