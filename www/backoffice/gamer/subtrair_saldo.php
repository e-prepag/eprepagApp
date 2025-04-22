<?php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

define("MAX_VALOR", 50000);

require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";

if(isset($_POST["ug_id"]) && !empty($_POST["ug_id"]) && isset($_POST["valor"]) && !empty($_POST["valor"])) {
	$ug_id = $_POST["ug_id"];
    $valor = doubleval(str_replace(",",".",str_replace(".","",$_POST["valor"])));
	
	$sql = "SELECT ug_perfil_saldo, ug_login FROM usuarios_games WHERE ug_id = " . $ug_id;
	$ret = SQLexecuteQuery($sql);
	
	if(pg_num_rows($ret) == 0) {
		$msg = "Nenhum Usuário Encontrado";
	}
	else {
		$row = pg_fetch_assoc($ret);
		$saldo_anterior = $row['ug_perfil_saldo'];
		$ug_login = $row["ug_login"];
		
		$valor_novo = $saldo_anterior - $valor;
		
		if(($saldo_anterior - $valor) <= 0)  $valor_novo = 0;
		
		$sql = "INSERT INTO estorno_usuario (
			id,
			shn_id,
			ug_id,
			ug_saldo_anterior,
			ug_saldo_atual,
			ug_login
		)
		VALUES (
			default,
			'".$_SESSION['iduser_bko']."',
			".$ug_id . ",
			".$saldo_anterior.",
			".$valor_novo.", 
			'".$ug_login."'
		);
		";
		
		$ret = SQLexecuteQuery($sql);
		
		if(!$ret){
			$msg = "Erro ao salvar as informações do estorno";
		}
	}
	
	
}
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/jquery.mask.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>


<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<?php

if(isset($msg)){

?>
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger">
            <?php echo $msg ?>
        </div>
    </div>
</div>

<?php

}

?>

<div class="row">
    
    <div class="col-md-12 text-center">
        
        <form id="form-estorno" href="#" class="form-inline" method="POST">
            <div class="form-group">
                <label for="exampleInputName2">ID do Usuário</label>
                <input type="number" class="form-control" id="ug_id" name="ug_id" step="1" min="1" required>
                <small id="validacao_ug_id" style="color: red;"></small>
            </div>
            <div class="form-group left20">
                <label for="exampleInputEmail2">Valor a ser subtraído</label>
                <input type="text" class="form-control" id="valor" name="valor" placeholder="Informe o ID do Usuário" maxlength="9" readonly required>
                <small id="validacao_valor" style="color: red;"></small>            
            </div>
            <button id="confirma-estorno" class="btn btn-default left20">Subtrair</button>
        </form>
        
    </div>
    
</div>

<div class="panel panel-default top20" id="div-dados-pdv" style="display: none;">
    <div class="panel-heading">
        <h3 accesskey=""class="panel-title">Dados do Usuário</h3>
    </div>
    <div class="panel-body" id="dados-pdv"></div>
</div>

<script src="/js/jquery.mask.min.js"></script>
<script>

$("#ug_id").focusout(function(){
	$("#validacao_valor").html("");
	$("#validacao_ug_id").html("");
	if($("#ug_id").val() != ""){
		$.ajax({
			type: "POST",
			url: "ajax_carrega_usuario.php",
			data: { ug_id : $("#ug_id").val()},
			beforeSend: function(){
				searching = true;
				$("#valor").attr("placeholder", "Carregando...");
			},
			success: function(txt){
				searching = false;
				if(txt.trim() == "erro"){
					$("#dados-pdv").html("<span style='color: red;'>O id informado não corresponde a um usuário</span>");
					$("#valor").attr("placeholder", "Informe o ID do Usuário");
					$("#valor").attr("readonly", "true")
				}else{
					$("#dados-pdv").html(txt);
					$("#valor").removeAttr("readonly", "false");
					$("#valor").attr("placeholder", "");
				}
				$("#div-dados-pdv").show(500);
			},
			error: function(x,y){
				return false;
			}
		});
	}else{
		$("#dados-pdv").html("");
		$("#div-dados-pdv").hide(500);
	}
});
</script>