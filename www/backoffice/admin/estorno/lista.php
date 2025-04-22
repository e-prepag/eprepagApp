<?php

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

$ug_id	= isset($_REQUEST['ug_id'])	? htmlentities($_REQUEST['ug_id'])	: '';
$shn_login = isset($_REQUEST['shn_login'])? strtoupper(htmlentities($_REQUEST['shn_login'])) : '';
$est_tipo = isset($_REQUEST['est_tipo'])? htmlentities($_REQUEST['est_tipo']) : -1;

//paginacao
if(isset($_POST['p'])){
    $p = $_POST['p'];
}else{
    $p = 1;
}
$registros = 50;
$registros_total = 0;

$where = " WHERE 1=1";

if(!empty($ug_id)){
    $where .= " AND ug_id = " . $ug_id;
}

if(!empty($shn_login)){
    $where .= " AND shn_login = '" . $shn_login . "'";
}

if($est_tipo != "" && ($est_tipo >= 0)){
    $where .= " AND est_tipo = " . $est_tipo;
}

$sql = "SELECT count(*) as total FROM estorno_pdv" . $where;
$rs_total = SQLexecuteQuery($sql);
$row = pg_fetch_assoc($rs_total);
if($row) $registros_total = $row["total"];


$sql = "SELECT * FROM estorno_pdv" . $where;
$sql .= " ORDER BY data_operacao DESC";	
$sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
$rs_estornos = SQLexecuteQuery($sql);
?>

<!--Cabeçalho-->
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>

<div class='row'>
    <div id="msg" name="msg" class="lstDado">
        <?php

            if(!empty($msg)){
                echo "<div class='alert alert-danger'>";
                foreach($msg as $m){
                    echo $m . "<br>";
                }
                echo "</div>";
            }

        ?>
    </div>
</div>
<div class="row top10">
    <div class="col-md-12">
        <?php
            if(!empty($erro_lst)){
                echo "<div class='alert alert-danger'>";
                echo $erro_lst;
                echo "</div>";
            }
        ?>
    </div>
</div>
<div class="row" style="padding: 10px;">
    <div class="col-md-12 p-left25">
        <h4 class="txt-azul-claro">Filtro de pesquisa</h4>
    </div>
</div>
<div class="row">
    <div class="col-md-12 top10 txt-preto">
        <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="row">
                <div class="col-md-3 text-left">
                    <div class="form-group">
                        <label for="">ID do PDV: </label>
                        <input class="form-control" name="ug_id" type="text" id="ug_id" value="<?php echo ($ug_id) ? $ug_id : "";?>"/>
                    </div>
                </div>
                <div class="col-md-4 text-left">
                    <div class="form-group">
                        <label for="">Login Usuário Backoffice: </label>
                        <input class="form-control" name="shn_login" type="text" id="shn_login" value="<?php echo ($shn_login) ? $shn_login : "";?>"/>
                    </div>
                </div>
                <div class="col-md-3 text-left">
                    <div class="form-group">
                        <label for="">Tipo: </label>
                        <select class="form-control" name="est_tipo">
                            <option value="-1" <?php if($est_tipo == -1) echo "selected"; ?>>Todos</option>
                            <option value="0" <?php if($est_tipo == 0) echo "selected"; ?>>Estorno</option>
                            <option value="1" <?php if($est_tipo == 1) echo "selected"; ?>>Zeramento</option>
                            <option value="2" <?php if($est_tipo == 2) echo "selected"; ?>>Subtração</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <div class="form-group">
                        <input name="btn_pesquisar" class="btn btn-sm btn-info" style="margin-top: 25px;" type="submit" id="btn_pesquisar" value="Pesquisar" />
                    </div>
                </div>
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
    <div class="panel-heading" style="height: 40px;">
        <div class="col-md-6">
            <h3  class="panel-title"></h3>
        </div>
        <div class="col-md-6 text-right">
            <h3  class="panel-title">Página <?php echo $p ?> de <?php echo ceil($registros_total/$registros) ?></h3>
        </div>
    </div>
    <div class="panel-body">
<?php
        if(pg_num_rows($rs_estornos) > 0){
?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>ID PDV</th>
                        <th>Login PDV</th>
                        <th>Saldo Anterior</th>
                        <th>Novo Saldo</th>
                        <th>Valor</th>
                        <th>Data</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
<?php
                    while($estorno = pg_fetch_assoc($rs_estornos)){
                        if(($estorno["est_valor"] == 0) || (number_format(($estorno["ug_saldo_atual"] - $estorno["ug_saldo_anterior"]),2,',','.') == number_format($estorno["est_valor"],2,',','.'))){
                            $style = "";
                        }else{
                            $style = "style='background-color: red; color: white;'";
                        }
?>
        
                        <tr <?php echo $style; ?>>
                            <td><?php echo $estorno["shn_login"] ?></td>
                            <td><?php echo $estorno["ug_id"]; ?></td>
                            <td><?php echo $estorno["ug_login"]; ?></td>
                            <td>R$<?php echo number_format($estorno["ug_saldo_anterior"], 2, ",", "."); ?></td>
                            <td>R$<?php echo number_format($estorno["ug_saldo_atual"], 2, ",", "."); ?></td>
                            <td>
                                <?php
                                    if($estorno["est_tipo"] != -1){
                                        echo "R$" . number_format($estorno["est_valor"], 2, ",", ".");
                                    }
                                ?>
                            </td>
                            <td><?php echo date("d/m/Y H:i:s", strtotime($estorno["data_operacao"])); ?></td>
                            <td>
                                <?php
                                    if($estorno["est_tipo"] == 0){
                                        echo "Estorno";
                                    }if($estorno["est_tipo"] == 2){
                                        echo "Subtração";
                                    }else{
                                        echo "Zeramento";
                                    }
                                ?>
                            </td>
                        </tr>
        
<?php
                    }
?>
                </tbody>
            </table>
            <div class="row">
                <div class="col-md-6 text-right">
                    
                    <?php if($p > 1){ ?>
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                <input type="hidden" name="p" value="<?php echo $p - 1; ?>"/>
                                <input name="ug_id" type="hidden" value="<?php echo $ug_id;?>"/>
                                <input name="shn_login" type="hidden" value="<?php echo $shn_login;?>"/>
                                <input name="est_tipo" type="hidden" value="<?php echo $est_tipo;?>"/>
                                <input type="submit" name="btAnterior" value=" < " class="btn btn-sm btn-info">
                            </form>
                    <?php } ?>
                </div>
                <div class="col-md-6 text-left">
                    <?php if($p < ($registros_total/$registros)){ ?>
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                <input type="hidden" name="p" value="<?php echo $p + 1; ?>"/>
                                <input name="ug_id" type="hidden" value="<?php echo $ug_id;?>"/>
                                <input name="shn_login" type="hidden" value="<?php echo $shn_login;?>"/>
                                <input name="est_tipo" type="hidden" value="<?php echo $est_tipo;?>"/>
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
<script>
$("#ug_id").focusout(function(){
    if($("#ug_id").val() != ""){
        $.ajax({
            type: "POST",
            url: "ajax_carrega_pdv.php",
            data: { ug_id : $("#ug_id").val()},
            beforeSend: function(){
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
</script>

</body>
</html>