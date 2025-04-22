<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>
<?php

require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
?>

<div class="permission-page">
	<h1 style="margin: 20px; font-size: 25px;">Solicitações</h1>
	
	<div class="searchs" style="width: auto;">
		<input type="text" id="filtro-atividade-cnae" placeholder="Filtrar por Usuário">
	</div>
	
	<table id="myTable" class="display compact hover stripe cell-border" style="width:100%;text-align: center;">
		<thead>
			<th>ID</th>
			<th>SHN ID</th>
			<th>ID Usuário</th>
			<th>Nome do Usuário</th>
			<th>Saldo Anterior</th>
			<th>Saldo Depois</th>
			<th>Data do Pedido</th>
			<th>Login do Usuário</th>
			<th>Ações</th>
		</thead>
		<tbody>
			
		</tbody>
	</table>
</div>
<script>
 
	$(document).ready(function() {
		$.noConflict();
		 
		let table = new DataTable('#myTable', {
			
			language: {
				lengthMenu: "Mostrar _MENU_ resultados por página",
				zeroRecords: "Não foram encontrados usuários",
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
				url: "https://<?php echo $server_url_complete; ?>/ajax/gamer/ajaxPermissaoSaldo.php?acao=listar",
				type: "POST",
				dataSrc: ''
			},
			
			columns: [
				{ 	
					title: 'ID',
					data: 'id' 
				},
				{ 
					title: 'SHN ID',
					data: 'shn_id'
				},
				
				{
					title: 'ID Usuário',
					data: 'ug_id'
				},
				
				{
					title: "Nome do Usuário",
					data: "ug_nome"
				},
				{ 
					title: 'Saldo Anterior',
					data: 'ug_saldo_anterior' 
				},
				
				{
					title: 'Saldo Depois',
					data: 'ug_saldo_atual'
				},
				{
					title: 'Data do Pedido',
					data: 'data_operacao'
				},
				
				{
					title: 'Login do Usuário',
					data: 'ug_login'
				},
				{
					title: 'Ações',
					data: 'foi_aprovado',
					render: function(data, type, row) {
						if (data == 1) {
						  return 'Liberado';
						} else {
						  return '<button type="button" class="btn btn-aprovar" style="background-color: green; color: white">Liberar</button>';
						}
					}
				}
			],
		} );
		
		$("#myTable_filter").hide();
		
		$('#myTable').on('click', '.btn-aprovar', function() {
		  var row = $(this).closest('tr');
		  var rowData = $('#myTable').DataTable().row(row).data();
		  rowData.foi_aprovado = 1;
		  atualizarDadosNoServidor(rowData);
		  $('#myTable').DataTable().row(row).data(rowData).draw();
		});

	
		$('div.searchs #filtro-atividade-cnae').on('keyup', function() {
			console.log(this.value);
			table.column(3).search(this.value).draw();
		});
	
		
		function atualizarDadosNoServidor(rowData) {
		  
		  let {ug_id, ug_saldo_atual} = rowData;
		  
		  $.ajax({
			url: "https://<?php echo $server_url_complete; ?>/ajax/gamer/ajaxPermissaoSaldo.php?acao=alterar",
			method: "POST",
			data: {ug_id, novo_saldo: ug_saldo_atual}
		  }).done(function(dataValues) {
			//console.log(dataValues);
		  });
		}
	});
</script>

<style>
body {
	color: #222;
}

.permission-page {
	width: 100%;
	display: flex;
	flex-direction: column;
	align-items: center;
}

.searchs {
	width: 300px;
	display: flex;
	align-items: center;
	justify-content: space-between;
}

.searchs input {
	margin-right: 10px;
	padding: 10px;
}

.searchs select {
	padding: 12px !important; 
}
</style>