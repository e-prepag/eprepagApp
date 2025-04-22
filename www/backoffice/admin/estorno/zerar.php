<?php

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
if(isset($_POST["ug_id"])){
    $ug_id = $_POST["ug_id"];
    $sql = "SELECT ug_perfil_saldo, ug_login, ug_risco_classif FROM dist_usuarios_games WHERE ug_id = " . $ug_id;
    $ret = SQLexecuteQuery($sql);
    if(pg_num_rows($ret) == 0){
        $msg = "O ID não corresponde a um pdv";
    }else{
        $row = pg_fetch_assoc($ret);
        if($row["ug_risco_classif"] != 2){
            $msg = "O zeramento de saldo é permitido apenas para PDVs pré-pagos.";
        }else{
            $ug_login = $row["ug_login"]; 
            $saldo_anterior = $row["ug_perfil_saldo"];
            $valor_atual = 0;
			$sql = "INSERT INTO estorno_pdv(shn_id, shn_login, ug_id, ug_login, ug_saldo_anterior, ug_saldo_atual, est_tipo, est_valor, ug_descricao) "
					. "VALUES "
					. "("
					. "'" . $_SESSION['iduser_bko'] . "', "
					. "'" . $_SESSION['userlogin_bko'] . "', "
					. $ug_id . ", "
					. "'" . $ug_login . "', "
					. $saldo_anterior . ", "
					. $valor_atual . ", "
					. "1, "
					. "0 ,'" .
					$_POST["descricao"] . "')";
			$ret = SQLexecuteQuery($sql);
			if(!$ret){
				$msg = "Erro ao salvar as informações do estorno";
			}
            
        }
    }
}

//paginacao
if(isset($_POST['p'])){
    $p = $_POST['p'];
}else{
    $p = 1;
}
$registros = 50;
$registros_total = 0;


$sql = "SELECT count(*) as total FROM estorno_pdv WHERE est_tipo = 1";
$rs_total = SQLexecuteQuery($sql);
$row = pg_fetch_assoc($rs_total);
if($row) $registros_total = $row["total"];


$sql = "SELECT * FROM estorno_pdv WHERE est_tipo = 1";
$sql .= " ORDER BY data_operacao DESC";	
$sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
$rs_estornos = SQLexecuteQuery($sql);

?>



<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/jquery.mask.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<style>
.modal {
  text-align: center;
}

.container {
	width: 100%;
}

@media screen and (min-width: 768px) { 
  .modal:before {
    display: inline-block;
    vertical-align: middle;
    content: " ";
    height: 100%;
  }
}

.modal-dialog {
  display: inline-block;
  text-align: left;
  vertical-align: middle;
}
</style>
<script>
    $(document).ready(function(){

        
    });
    
    var searching = false;
    var content = "";
        
    
</script>

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
    
    <div class="col-md-12">
        
        <form id="form-estorno" href="#" class="form-inline" method="POST">
            <div class="form-group">
                <label for="exampleInputName2">ID do PDV</label>
                <input type="number" class="form-control" id="ug_id" name="ug_id" step="1" min="1" required>
                <small id="validacao_ug_id" style="color: red;"></small>
            </div>
            <button id="confirma-estorno" class="btn btn-default left20">Zerar</button>
			<div class="form-group top10" style="display: block;">
                <label for="exampleInputEmail2">Justificativa</label>
				<textarea id="descricao" name="descricao" class="form-control" placeholder="Informe o motivo do estorno" required></textarea>
                <small id="validacao_valor_descricao" style="color: red;"></small>            
            </div>
        </form>
        
    </div>
    
</div>

<div class="panel panel-default top20" id="div-dados-pdv" style="display: none;">
    <div class="panel-heading">
        <h3 accesskey=""class="panel-title">Dados do PDV</h3>
    </div>
    <div class="panel-body" id="dados-pdv">
            
    </div>
</div>


<div class="panel panel-default top20">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6">
                <h3  class="panel-title">Últimos estornos</h3>
            </div>
            <div class="col-md-6 text-right">
                <h3  class="panel-title">Página <?php echo $p ?> de <?php echo ceil($registros_total/$registros) ?></h3>
            </div>
        </div>
    </div>
    <div class="panel-body">
<?php
        if(pg_num_rows($rs_estornos) > 0){
?>
            <table class="table">
                <thead>
                    <tr>
					    <th>Status</th>
						<th>Autorização</th>
                        <th>Usuário</th>
                        <th>ID PDV</th>
                        <th>Login PDV</th>
                        <th>Saldo Anterior</th>
                        <th>Novo Saldo</th>
                        <th>Data</th>
						<th>Justificativa</th>
                    </tr>
                </thead>
                <tbody>
<?php
                    while($estorno = pg_fetch_assoc($rs_estornos)){
						
						if($estorno["ug_aprovacao"] == "S" || strtotime((new DateTime($estorno["data_operacao"]))->format("Y-m-d")) < strtotime((new DateTime("2023-07-07"))->format("Y-m-d"))){
							$status = "Aprovado";
						}else if($estorno["ug_aprovacao"] == "N"){
							$status = "Não aprovado";
						}else if($estorno["ug_aprovacao"] == null){
							$status = "Esperando aprovação";
						}
						
						if($estorno["ug_user_aprova"] != ""){
							$operador = $estorno["ug_user_aprova"];
						}else if(strtotime((new DateTime($estorno["data_operacao"]))->format("Y-m-d")) <= strtotime((new DateTime("2023-07-07"))->format("Y-m-d"))){
							$operador = "WAGNER";
						}else{
							$operador = "Não possui";
						}
						
?>
        
                        <tr>
						    <td><?php echo $status; ?></td>
							<td><?php echo $operador; ?></td>
                            <td><?php echo $estorno["shn_login"] ?></td>
                            <td><?php echo $estorno["ug_id"]; ?></td>
                            <td><?php echo $estorno["ug_login"]; ?></td>
                            <td>R$<?php echo number_format($estorno["ug_saldo_anterior"], 2, ",", "."); ?></td>
                            <td>R$<?php echo number_format($estorno["ug_saldo_atual"], 2, ",", "."); ?></td>
                            <td><?php echo date("d/m/Y H:i:s", strtotime($estorno["data_operacao"])); ?></td>
							<td><?php echo (($estorno["ug_descricao"] != null)? $estorno["ug_descricao"]: "Não possui justificativa"); ?></td>
                        </tr>
        
<?php
                    }
?>
                </tbody>
            </table>
            <div class="row">
                <div class="col-md-6 text-right">
                    
                    <?php if($p > 1){ ?>
                            <form action="#" method="POST">
                                <input type="hidden" name="p" value="<?php echo $p - 1; ?>"/>
                                <input type="submit" name="btAnterior" value=" < " class="btn btn-sm btn-info">
                            </form>
                    <?php } ?>
                </div>
                <div class="col-md-6 text-left">
                    <?php if($p < ($registros_total/$registros)){ ?>
                            <form action="#" method="POST">
                                <input type="hidden" name="p" value="<?php echo $p + 1; ?>"/>
                                <input type="submit" name="btAnterior" value=" > " class="btn btn-sm btn-info">
                            </form>
                    <?php } ?></nobr>
                </div>
            </div>
<?php
        }else{
?>
            Nenhum estorno realizado
<?php
        }
?>
    </div>
</div>


<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</div>
<div class="modal fade" id="modalConfirma" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-center" style="color: black;">
                        Você está prestes a zerar o saldo do PDV <span id="id-modal"></span><br>
                        Deseja continuar ?
                    </div>
                </div>
                <div class="row top20">
                    <div class="col-md-12 text-center">
                        <button class="btn btn-success" id="btn-confirmar">Confirmar</button>
                        <button class="btn btn-danger" style="margin-left: 10px;" type="button" class="close" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
    $(document).ready(function(){
        $("#confirma-estorno").click(function(e){
            e.preventDefault();
            if($("#ug_id").val() == ""){
                $("#validacao_ug_id").html("Por favor, preencha este campo");
                return false;
            }else if($("#descricao").val() == ""){
                $("#validacao_ug_id").html("");
                $("#validacao_valor_descricao").html("Por favor, preencha este campo");
                return false;
            }
			$("#validacao_valor_descricao").html("");
			
            var pdv = $("#ug_id").val();
            
            $("#id-modal").html(pdv);
            
            $("#modalConfirma").modal();
            
        });
        
        $("#btn-confirmar").click(function(){
           $("#form-estorno").submit(); 
        });
        
        $("#ug_id").focusout(function(){
            $("#validacao_ug_id").html("");
            if($("#ug_id").val() != ""){
                $.ajax({
                    type: "POST",
                    url: "ajax_carrega_pdv.php",
                    data: { ug_id : $("#ug_id").val()},
                    beforeSend: function(){
                        searching = true;
                        $("#valor").attr("placeholder", "Carregando...");
                    },
                    success: function(txt){
                        searching = false;
                        if(txt.trim() == "erro"){
                            $("#dados-pdv").html("<span style='color: red;'>O id informado não corresponde a um pdv</span>");
                        }else{
                            $("#dados-pdv").html(txt);
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
    });
</script>
</body>
</html>