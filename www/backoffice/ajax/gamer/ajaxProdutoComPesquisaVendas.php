<?php
require_once "/www/backoffice/includes/encoding.php";
require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";

$request_id = $request_id ?? 0;
if ($request_id > 0){
    if(strpos(($_SERVER['HTTP_REFERER'] ?? ""),'pdv') > 0)
        $tb = "tb_dist_operadora_games_produto";
    else
        $tb = "tb_operadora_games_produto";
    
	$sql = "SELECT ogp_id,ogp_nome FROM $tb WHERE ogp_opr_codigo = $1";
//echo $sql."<br>";
	$rs_oprProdutos = SQLexecuteQueryParams($sql, array($request_id));
}

$id = $request_id;

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
		echo str_replace(" ", "&nbsp;", backoffice_iso_to_utf8($rs_oprProdutos_row['ogp_nome'])); 
		?></nobr> 
<?php 
	}
}	
?>