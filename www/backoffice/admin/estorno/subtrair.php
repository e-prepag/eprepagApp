<?php

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

define("MAX_VALOR", 50000);

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";

if(isset($_POST["ug_id"]) && !empty($_POST["ug_id"]) && isset($_POST["valor"]) && !empty($_POST["valor"])){
    $ug_id = $_POST["ug_id"];
    $valor = doubleval(str_replace(",",".",str_replace(".","",$_POST["valor"])));
    if($valor > MAX_VALOR){
        $msg = "O valor máximo permitido para estorno é de R$" . number_format(MAX_VALOR, 2, ",", ".") . ".";
    }else{
        $sql = "SELECT ug_login, ug_perfil_saldo, ug_risco_classif FROM dist_usuarios_games WHERE ug_id = " . $ug_id;
        $ret = SQLexecuteQuery($sql);
        if(pg_num_rows($ret) == 0){
            $msg = "O ID não corresponde a um pdv";
        }else{
            $row = pg_fetch_assoc($ret);
            if($row["ug_perfil_saldo"] < $valor){
                $msg = "O saldo do PDV é menor que o valor a ser removido.";
            }else{
                $ug_login = $row["ug_login"];
                $saldo_anterior = $row["ug_perfil_saldo"];
				$valor_atual = $saldo_anterior - $valor; 
				$sql = "INSERT INTO estorno_pdv(shn_id, shn_login, ug_id, ug_login, ug_saldo_anterior, ug_saldo_atual, est_tipo, est_valor, ug_descricao) "
						. "VALUES "
						. "("
						. "'" . $_SESSION['iduser_bko'] . "', "
						. "'" . $_SESSION['userlogin_bko'] . "', "
						. $ug_id . ", "
						. "'" . $ug_login . "', "
						. $saldo_anterior . ", "
						. $valor_atual . ", "
						. "2, "
						. $valor * (-1) . ",'"
						. $_POST["descricao"]."')";
				$ret = SQLexecuteQuery($sql);
				if(!$ret){
					$msg = "Erro ao salvar as informações do estorno";
				}
                
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



$sql = "SELECT count(*) as total FROM estorno_pdv WHERE est_tipo = 2";
$rs_total = SQLexecuteQuery($sql);
$row = pg_fetch_assoc($rs_total);
if($row) $registros_total = $row["total"];


$sql = "SELECT * FROM estorno_pdv WHERE est_tipo = 2";
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
            <div class="form-group left20">
                <label for="exampleInputEmail2">Valor a ser subtraído</label>
                <input type="text" class="form-control" id="valor" name="valor" placeholder="Informe o ID do PDV" maxlength="9" readonly required>
                <small id="validacao_valor" style="color: red;"></small>            
            </div>
            <button id="confirma-estorno" class="btn btn-default left20">Subtrair</button>
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
                        <th>Valor</th>
                        <th>Data</th>
						<th>Justificativa</th>
                    </tr>
                </thead>
                <tbody>
<?php
                    while($estorno = pg_fetch_assoc($rs_estornos)){
                        //if(number_format(($estorno["ug_saldo_atual"] - $estorno["ug_saldo_anterior"]),2,',','.') == number_format($estorno["est_valor"],2,',','.')){
                            $style = "";
                       // }else{
                         //   $style = "style='background-color: red; color: white;'";
                       // }
						
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
        
                        <tr <?php echo $style; ?>>
						    <td><?php echo $status; ?></td>
							<td><?php echo $operador; ?></td>
                            <td><?php echo $estorno["shn_login"] ?></td>
                            <td><?php echo $estorno["ug_id"]; ?></td>
                            <td><?php echo $estorno["ug_login"]; ?></td>
                            <td>R$<?php echo number_format($estorno["ug_saldo_anterior"], 2, ",", "."); ?></td>
                            <td>R$<?php echo number_format($estorno["ug_saldo_atual"], 2, ",", "."); ?></td>
                            <td>R$<?php echo number_format($estorno["est_valor"], 2, ",", "."); ?></td>
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
                        Você está prestes a subtrair o valor de R$<span id="valor-modal"></span> ao PDV <span id="id-modal"></span><br>
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
<script src="/js/jquery.mask.min.js"></script>
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
    $(document).ready(function(){
        
        $('#valor').mask('000.000.000.000.000,00', {reverse: true});
        
        $("#confirma-estorno").click(function(e){
            e.preventDefault();
            if($("#ug_id").val() == ""){
                $("#validacao_valor").html("");
                $("#validacao_ug_id").html("Por favor, preencha este campo");
                return false;
            }else if($("#valor").val() == ""){
                $("#validacao_ug_id").html("");
                $("#validacao_valor").html("Por favor, preencha este campo");
                return false;
            }else if($("#descricao").val() == ""){
                $("#validacao_ug_id").html("");
                $("#validacao_valor_descricao").html("Por favor, preencha este campo");
                return false;
            }
			$("#validacao_valor_descricao").html("");
            $("#validacao_valor").html("");
            $("#validacao_ug_id").html("");
            var valor = $("#valor").val();
            var pdv = $("#ug_id").val();

            
            $("#valor-modal").html(valor);
            $("#id-modal").html(pdv);
            
            $("#modalConfirma").modal();
            
        });
        
        $("#btn-confirmar").click(function(){
           $("#form-estorno").submit(); 
        });
        
        $("#ug_id").focusout(function(){
            $("#validacao_valor").html("");
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
                            $("#valor").attr("placeholder", "Informe o ID do PDV");
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
    });
</script>
</body>
</html>