<?php
require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto."public_html/sys/includes/topo_sys_inc.php";

if ($_REQUEST['id'] > 0){
    if(strpos($_SERVER['HTTP_REFERER'],'dist_commerce') > 0)
        $tb = "tb_dist_operadora_games_produto";
    else
        $tb = "tb_operadora_games_produto";
    
	$sql = "SELECT ogp_id,ogp_nome FROM $tb WHERE ogp_opr_codigo = " . $_REQUEST['id'] . "";
//echo $sql."<br>";
	$rs_oprProdutos = SQLexecuteQuery($sql);
}

$id = $_REQUEST['id'];

if(isset($rs_oprProdutos) && $rs_oprProdutos){

	$v = 0;
	while($rs_oprProdutos_row = pg_fetch_array($rs_oprProdutos)){ 
?>
		<nobr><input type="checkbox" id="tf_produto" name="tf_produto[]" value="<?php echo $rs_oprProdutos_row['ogp_nome']; ?>"<?php
		if (isset($tf_produto) && is_array($tf_produto)){
			if (in_array($rs_oprProdutos_row['ogp_nome'], $tf_produto)){ 
				echo " checked";
			}else{
				if ($rs_oprProdutos_row['ogp_nome'] == $tf_produto){
					echo " checked";
				}
			}
		}								
		?>><?php 
		echo str_replace(" ", "&nbsp;", utf8_encode($rs_oprProdutos_row['ogp_nome'])); 
		?></nobr> 
<?php 
	}
}	
?><script>
		
/*


		function reload_precos() {
		
		'NOOOOOO';

		var selectedItems = new Array();
	
		$("input[@name='tf_produto[]']:checked").each( function () { 
		selectedItems.push($(this).val());
		

		$.ajax({
				
			type: "POST",
			url: "../commerce/includes/ajaxTipoComPesquisaVendas.php",
		    data: 
				
				{id:<?=$id?>}
			,
beforeSend: function(){
					$('#mostraValores2').html("Aguarde...");
				},
				success: function(html){
					
					$('#mostraValores2').html(html);
				},
				error: function(){
					alert('erro ao carregar valores');
				}

				}); //fim ajax

					
		});

	
		}// fim function reload precos

		
		$("input[@name='tf_produto[]']:unchecked").change(function () { 
			
		reload_precos();

	//	alert('eittaa');
		
		}); 
		//	"input[@name='chkBox']"
		
		//alert(this.value);
		
		*/</script>