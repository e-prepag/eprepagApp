<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
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
<h1 class="title_gamers">IP�s Seguros</h1>
<button class="btn btn-success btn-reload" style="margin-bottom: 10px;">Atualizar dados</button>
<table id="table" class="display hover stripe cell-border" style="width:100%;text-align: center;">
	<thead>
		<tr>
			<th>C�digo</th>
			<th>Ip</th>
			<th>Liberado</th>
			<th>Usuario</th>
			<th>A��o</th>
		</tr>
	</thead>
</table>
 
<script src="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.js"></script>
<script>
	$(document).ready(function () {
		let table = $('#table').DataTable({
			ajax: {
				url: "https://<?php echo $server_url_complete ;?>/pdv/usuarios/ajaxDesbloquearChaveMestra.php?acao=listar",
				type: "POST",
				dataSrc: ''
			},
			
			columns: [
				{ data: 'codigo'},
				{ data: 'ip' },
				{ data: 'liberado' },
				{ data: 'ug_nome_fantasia' },
				{ data: 'acao' }
			],
			language: {
                "zeroRecords": "N�o foram encontrados registros",
				"lengthMenu":  "Mostrar _MENU_ linhas",
                "info": "Mostrando a p�gina _PAGE_ de _PAGES_",
                "infoEmpty": "Dados inexistentes",
                "infoFiltered": "(filtro aplicado em _MAX_ registros)",
                "sSearch": "Pesquisar",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Pr�ximo",
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
				url:"ajaxDesbloquearChaveMestra.php?acao=apagar",
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