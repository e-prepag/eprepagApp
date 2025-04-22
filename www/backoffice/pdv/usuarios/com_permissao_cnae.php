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

<div class="cnae-page">
	<h1 style="margin: 20px; font-size: 25px;">CNAE´s permitidos na verificação de cadastro PDV</h1>
	
	<div class="searchs">
		<input type="text" id="filtro-atividade-cnae" placeholder="Filtrar por atividade CNAE">
		<select id="filtro-status">
			<option value="">Filtrar por status</option>
			<option value="1">Ativo</option>
			<option value="0">Inativo</option>
		</select>
	</div>
	
	<table id="myTable" class="display">
		<thead>
			<th>Código CNAE</th>
			<th>Identificação</th>
			<th>Atividade CNAE</th>
			<th>Status</th>
			<th>Ações</th>
		</thead>
		<tbody>
			
		</tbody>
	</table>
</div>
<script>

	$(document).ready(function() {
		$.noConflict();
		
		let cnaes = $("#cnaes");
		
		 
		let table = new DataTable('#myTable', {
			
			language: {
				lengthMenu: "Mostrar _MENU_ resultados por página",
				zeroRecords: "Não foram encontrados CNAES",
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
				url: "https://<?php echo $server_url_complete ;?>/pdv/usuarios/ajaxPermissaoCnaes.php",
				type: "POST",
				dataSrc: ''
			},
			
			columns: [
				{ 	
					title: 'Código CNAE',
					data: 'codigo_cnae' 
				},
				{ 
					title: 'Identificação CNAE',
					data: 'identificacao_cnae'
				},
				{ 
					title: 'Atividade CNAE',
					data: 'atividade_cnae' 
				},
				{ 
					title: 'Status',
					data: 'aprovado_cnae',
					searchable: true,
					render: function(data, type, row, meta) {
					
						if(type == 'display'){
							if (data == 1) {
							  return '<div style="color: white; background-color: green; padding: 10px">Ativo</div>';
							} else {
							  return '<span style="color: white; background-color: red; padding: 10px">Inativo</span>';
							}
						}else{
							return data;
					   }
					}
				},
				{
					title: 'Ações',
					data: 'aprovado_cnae',
					render: function(data, type, row) {
						if (data == 1) {
						  return '<button type="button" class="btn btn-reprovar" style="background-color: red; color: white">Inativar</button>';
						} else {
						  return '<button type="button" class="btn btn-aprovar" style="background-color: green; color: white">Ativar</button>';
						}
					}
				}
			],
		} );
		
		$("#myTable_filter").hide();
		
		$('#myTable').on('click', '.btn-aprovar', function() {
		  var row = $(this).closest('tr');
		  var rowData = $('#myTable').DataTable().row(row).data();
		  rowData.aprovado_cnae = 1;
		  atualizarDadosNoServidor(rowData);
		  $('#myTable').DataTable().row(row).data(rowData).draw();
		});

		$('#myTable').on('click', '.btn-reprovar', function() {
		  var row = $(this).closest('tr');
		  var rowData = $('#myTable').DataTable().row(row).data();
		  rowData.aprovado_cnae = 0;
		  atualizarDadosNoServidor(rowData);
		  $('#myTable').DataTable().row(row).data(rowData).draw();
		});
	
		$('div.searchs #filtro-atividade-cnae').on('keyup', function() {
			console.log(this.value);
			table.column(2).search(this.value).draw();
		});
	 
		$('div.searchs #filtro-status').on('change', function() {
			table.column(3).search(this.value).draw();
		});
		
		function atualizarDadosNoServidor(rowData) {
		  
		  let {codigo_cnae, aprovado_cnae} = rowData;
		  
		  $.ajax({
			url: "https://<?php echo $server_url_complete ;?>/pdv/usuarios/ajaxPermissaoCnaes.php",
			method: "POST",
			data: {type: 2, codigo_cnae, aprovado_cnae}
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

.cnae-page {
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