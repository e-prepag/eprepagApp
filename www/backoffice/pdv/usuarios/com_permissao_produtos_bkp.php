<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
require_once "/www/includes/bourls.php";
$connection = ConnectionPDO::getConnection()->getLink(); 
$sql = "select * from tb_dist_operadora_games_produto where ogp_ativo = 1 order by ogp_nome;";
$query = $connection->prepare($sql);
$query->execute();
$produtos = $query->fetchAll(PDO::FETCH_ASSOC);

// 10.204.134.60

 $conexao_new_epp = function(){
	//Conectando ao Banco de dados
	try{
		$username = 'eprepaga_pagorama';
		$password = 'U3yARhv6HcJN';
		$pdo = new PDO('mysql:host=177.11.54.107;port=3306;dbname=eprepaga_pag', $username, $password);
	}catch(PDOEXCEPTION $e){ //5433 
		http_response_code(500);
		return false;
	}
	return $pdo;
 };
  
$verificacao = $conexao_new_epp()->prepare("select id_eprepag, preferredName from user order by preferredName;");
$verificacao->execute();
$vendedores = $verificacao->fetchAll(PDO::FETCH_ASSOC);
//echo '<script>console.log('.json_encode($vendedores).')</script>';
?>

<div class="permissao-page">
	<h1>Permissão de PDVs</h1>
	
	<form method="post">
	
		<label for="vendedores" style="margin-top: 10px">PDVs</label>
		<select id="vendedores" name="vendedores" class="js-example-basic-single">
			<option value="">Selecione um PDV</option>
			<?php foreach($vendedores as $id => $vendedor) {
				echo '<option value="'.$vendedor["id_eprepag"].'">'.$vendedor["preferredName"].'</option>';
			}?>
		</select>
		
		<label for="states[]">Produtos a serem bloqueados</label>
		<select id="produtos" name="states[]" multiple="multiple" class="js-example-basic-single">
			<?php foreach($produtos as $id => $produto) {
				echo '<option value="'.$produto["ogp_id"].'">'.$produto["ogp_nome"].'</option>';
			}; ?>
		</select>
		
		<button type="button" id="save" class="btn btn-success button-send">Associar</button>
	</form>
</div>

<script>
$(document).ready(function () {
	
	var comparativo;
	
	function showLoadingSwal() {
	  Swal.fire({
		title: 'Associado com Sucesso',
		html: 'Agora esses produtos estão sem permissão para o vendedor',
		allowOutsideClick: true,
		allowEscapeKey: false,
		showConfirmButton: true
	  });
	}
	
	let produtos = $("#produtos");
	let vendedores = $("#vendedores");
	produtos.select2();
	vendedores.select2();
	
	vendedores.on('change', function() { 
		produtos.val([]).trigger('change');
	
		$.ajax({
			method: "POST",
			url: "https://<?php echo $server_url_complete ;?>/pdv/usuarios/ajaxPermissaoProdutos.php",
			data: {type: 2, id_eprepag: this.value}
		}).done(function(dataValues) {
			//console.log(dataValues);
			
			const idsEncontrados = {};

			// Filtra o array de produtos para que apenas os objetos com id_produto único permaneçam
			const produtosUnicos = dataValues.filter(function (produto) {
			  if (idsEncontrados[produto.id_produto]) {
				return false; // já foi encontrado, então não inclui no novo array
			  } else {
				idsEncontrados[produto.id_produto] = true; // adiciona no objeto auxiliar
				return true; // não foi encontrado, então inclui no novo array
			  }
			});
			
			let idsProdutos = produtosUnicos.map(x => x.id_produto);
						
			if(idsProdutos.length) {
				comparativo = idsProdutos.map(x => parseInt(x));
				$('#produtos').val(idsProdutos).trigger('change');
			}else{
				comparativo = [];
			}
			
		});
	});
	
	$("#save").click(function () {
		let produtosRemovidos = produtos.val();
		let diferenca;
		
		//console.log("Log comparativo");
		//console.log(comparativo);
		
		//console.log("Produtos removidos: ");
		//console.log(produtosRemovidos);
		if(produtosRemovidos == null) {
			$.ajax({
			  method: "POST",
			  url: "https://<?php echo $server_url_complete ;?>/pdv/usuarios/ajaxPermissaoProdutos.php",
			  data: {type: 4, id_eprepag: vendedores.val()},
			  
				error: function(xhr, textStatus, errorThrown) {
				  // Código a ser executado em caso de erro na requisição
				  Swal.close(); // Fecha o Swal de loading
				  Swal.fire({
					icon: 'error',
					title: 'Oops...',
					text: 'Ocorreu um erro na requisição'
				  });
				}  
			}).done(function(dataValues){
			})
		}
		diferenca = produtosRemovidos ? comparativo.filter(a => !produtosRemovidos.includes(a.toString())) : comparativo
		
		//console.log("Log da diferença");
		//console.log(diferenca);
		
		for(produtoToExclude of diferenca) {
			showLoadingSwal();
			console.log("Dentro do for");
			console.log(produtoToExclude);
			console.log(vendedores.val());
			$.ajax({
			  method: "POST",
			  url: "https://<?php echo $server_url_complete ;?>/pdv/usuarios/ajaxPermissaoProdutos.php",
			  data: {type: 3, id_eprepag: vendedores.val(), id_produto: produtoToExclude},
			  
				error: function(xhr, textStatus, errorThrown) {
				  // Código a ser executado em caso de erro na requisição
				  Swal.close(); // Fecha o Swal de loading
				  Swal.fire({
					icon: 'error',
					title: 'Oops...',
					text: 'Ocorreu um erro na requisição'
				  });
				}  
			}).done(function(dataValues){
			})
		}
		
		if(produtosRemovidos) {
			for(produto of produtosRemovidos) {
				showLoadingSwal();
				
				$.ajax({
				  method: "POST",
				  url: "https://<?php echo $server_url_complete ;?>/pdv/usuarios/ajaxPermissaoProdutos.php",
				  data: {id_eprepag: vendedores.val(), id_produto: produto},
				  
					error: function(xhr, textStatus, errorThrown) {
					  // Código a ser executado em caso de erro na requisição
					  Swal.close(); // Fecha o Swal de loading
					  Swal.fire({
						icon: 'error',
						title: 'Oops...',
						text: 'Ocorreu um erro na requisição'
					  });
					}  
				}).done(function(dataValues){
					$('#produtos').trigger('change');
				})
			}
		}
	});
});
</script>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
<style>
body {
	color: #222;
}
.permissao-page {
	background-color: white;
	width: 100%;
	height: auto;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-direction: column;
}

.select2-container {
	width: 400px !important;
	margin: 5px 10px;
    border-radius: 0;
    border: 1px solid #dddddd !important;
    outline: 0;
}

.select2-container--default .select2-selection--single {
	padding: 20px 0px !important;
	border: none !important;

}	

.select2-container--default .select2-selection--single .select2-selection__rendered {
	margin-top: -15px;
}

form {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	justify-content: space-evenly;
}

form #save {
	margin: 10px auto 0px auto;
}
</style>