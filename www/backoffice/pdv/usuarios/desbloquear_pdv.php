<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>
<?php
//header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
?>

<div class="blocked-page">
	<h1>Desbloquear PDV´s</h1>
	
	<table id="myTable" class="display hover stripe cell-border" style="width:100%;text-align: center;">
		<thead>
			<th>Código do registro</th>
			<th>PDV</th>
			<th>Bloqueado em</th>
			<th>IP</th>
			<th>Login</th>
			<th>Tentativas</th>
			<th>Ações</th>
		</thead>
		<tbody>
			
		</tbody>
	</table>
</div>

<script>
	$(document).ready(function() {
		$.noConflict();
		
		var table = new DataTable('#myTable', {
			
			language: {
				lengthMenu: "Mostrar _MENU_ resultados por página",
				zeroRecords: "Não foram encontrados PDVs Bloqueados",
                info: "Mostrando a página _PAGE_ de _PAGES_",
                infoEmpty: "Dados inexistentes",
                infoFiltered: "(filtro aplicado em _MAX_ registros)",
                sSearch: "Pesquisar:",
                paginate: {
                    previous: "Anterior",
                    next: "Próximo",
                }
			},
			ajax: {
				url: "https://<?php echo $server_url_complete ;?>/pdv/usuarios/ajaxDesbloquearPDV.php",
				type: "POST",
				dataSrc: ''
			},
			
			columns: [
				{ 	
					title: 'Código do registro',
					data: 'id' 
				},
				{ 
					title: 'PDV',
					data: 'ug_id'
				},
				{ 
					title: 'Criado em',
					data: 'created' 
				},
				{ 
					title: 'IP',
					data: 'ip',
					searchable: true
				},
				{ 
					title: 'Login',
					data: 'login'
				},
				{
					title: 'Tentativas',
					data: 'tentativas'
				},
				{
					title: 'Ações',
					data: 'msg'
					
				}
			],
			
		})
	} );
	
	$('#myTable').on('click', '.btn-aprovar', function() {
	  var row = $(this).closest('tr');
	  var rowData = $('#myTable').DataTable().row(row).data();
	  console.log(rowData);
	  atualizarDadosNoServidor(rowData);
	  $('#myTable').DataTable().ajax.reload();
	  /*var rowToRemove = $('#myTable').DataTable().rows().eq(0).filter(function(index) {
		console.log($('#myTable').DataTable().cell(index, 0).data());
		return $('#myTable').DataTable().cell(index, 0).data() ===  rowData.id;
	  });
	  
	  $('#myTable').DataTable().row(rowToRemove).remove().draw();*/
	});
	/* 
		NOTA:::
		
		Atualiza o banco de dados e a tabela após o clique no botão Desbloquear
	*/

	function atualizarDadosNoServidor(rowData) {
		  
		let {id} = rowData;
		  
		$.ajax({
			url: "https://<?php echo $server_url_complete ;?>/pdv/usuarios/ajaxDesbloquearPDV.php",
			method: "POST",
			data: {type: 2, id}
		}).done(function(dataValues) {
			console.log(dataValues);
			Swal.fire({
				position: 'top-end',
				icon: 'success',
				title: dataValues,
				showConfirmButton: false,
				timer: 1500
			});
		});
	}
	
</script>

<style>
	body {
		color: #222;
	}
	.blocked-page {
		width: 100%;
		height: 100vh;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
</style>