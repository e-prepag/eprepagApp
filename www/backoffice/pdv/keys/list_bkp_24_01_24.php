<?php

 session_start();
 require_once '../../../includes/constantes.php';
 require_once "/www/includes/main.php";
 require_once $raiz_do_projeto."backoffice/includes/topo.php";
 
  $conexao_new_epp = function(){
	//Conectando ao Banco de dados
	try{
		$username = 'eprepaga_pagorama';
		$password = 'waxMTZ0QGSRNlVVBlawNkY';
		$pdo = new PDO('mysql:host=177.11.54.107;port=3306;dbname=eprepaga_pag', $username, $password);
	}catch(PDOEXCEPTION $e){ //5433 
		echo "Error: ".$e->getMessage();
		return false;
	}
	return $pdo;
 };
 
 $query = $conexao_new_epp()->prepare("select preferredName,cod_situacao,id_eprepag,access_token,api.datahora from user u 
	 inner join oauth_clients c on c.user_id = u.id_new 
	 inner join situacao_chave_api ch on ch.cod_usuario = u.id_new 
	 inner join oauth_access_tokens t on t.user_id = u.id_new 
	 inner join AT_ApisUso api on api.token = t.access_token group by preferredName order by api.datahora;");
 $query->execute();
 $resultadoSelecao = $query->fetchAll(PDO::FETCH_ASSOC);
 
 //var_dump($resultadoSelecao);
 
?>

<link href="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-html5-2.4.2/datatables.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-html5-2.4.2/datatables.min.js"></script>

<style>
 
.title {
	text-align: center;
	font-size: 24px;
	margin: 30px 0;
}

label {
	color: black;
	font-weight: normal;
}

.linha {
	width: 80%;
}

table.dataTable tbody td {
	padding: 12px 0;
}

#table, table.dataTable>thead>tr>th, table.dataTable>thead>tr>td {
	color: black;
	padding: 10px 0;
	text-align: center;
}

.dataTables_wrapper .dataTables_info  {
	color: black;
}

.active {
	color: green;
	font-weight: bold;
}

.inactive {
	color: red;
	font-weight: bold;
}

.icone{
	margin-right: 5px;
}

</style>

<div>
    <h1 class="title">Listagem de chaves API</h1>
	<hr class="linha">
	<table class="stripe hover row-border order-column" id="table">
	    <thead>
		    <tr>
			    <th>ID</th>
				<th>PDV</th>
				<th>Primeira Utilização</th>
				<th>Situação chave</th>
			</tr>
		</thead>
		<tbody>
		<?php
		    if(count($resultadoSelecao) > 0){
				foreach($resultadoSelecao as $key => $value){
					$date = new DateTimeImmutable($value["datahora"]);
		?>
		     <tr>
				<td><?php echo $value["id_eprepag"];?></td>
				<td><?php echo $value["preferredName"];?></td>
				<td><?php echo $date->format("d-m-Y H:i:s");?></td>
				<td class="<?php echo ($value["cod_situacao"] == 1)?'active':'inactive';?>"><?php echo ($value["cod_situacao"] == 1)?'Ativo':'Inativo';?></td>
			 </tr>
		<?php
				}
			}
		?>
		</tbody>
	</table>
</div>

<script>
    $(document).ready(function(){
		let table = new DataTable('#table', {
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
			}
		});
	});
</script>