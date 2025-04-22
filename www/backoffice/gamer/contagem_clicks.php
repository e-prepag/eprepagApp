<?php
$pos_pagina = false; //apenas para nao exibir erro/ resolver depois

require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
include_once $raiz_do_projeto.'class/util/Validate.class.php';
include_once $raiz_do_projeto.'class/util/Util.class.php';
require_once "/www/includes/bourls.php";

$sqlProdutosGamer = "select ogp_nome as nome, ogp_id as id from tb_operadora_games_produto order by ogp_nome";

if($rsProdutosGamer = SQLexecuteQuery($sqlProdutosGamer)){
    if(pg_num_rows($rsProdutosGamer)){
        while($rs_row = pg_fetch_array($rsProdutosGamer)) {
            $produtosGamer[$rs_row['id']] = $rs_row['nome'];
        }
    }
}

$sqlProdutosPdv = "select ogp_nome as nome, ogp_id as id from tb_dist_operadora_games_produto order by ogp_nome";

if($rsProdutosPdv = SQLexecuteQuery($sqlProdutosPdv)){
    if(pg_num_rows($rsProdutosPdv)){
        while($rs_row = pg_fetch_array($rsProdutosPdv)) {
            $produtosPdv[$rs_row['id']] = $rs_row['nome'];
        }
    }
}

$dataIni = (!empty($_POST['dataIni'])) ? Util::getData($_POST['dataIni'], true): date("Y-m-d");
$dataFim = (!empty($_POST['dataFim'])) ? Util::getData($_POST['dataFim'], true): date("Y-m-d");

$where = "where data >= '{$dataIni} 00:00:00' and data <= '{$dataFim} 23:59:59'";

if(!empty($_POST['sistema'])){
    $where .= " and upper(sistema) = upper('".$_POST['sistema']."')";

    if(!empty($_POST['produtosGamers'])){
        $where .= " and ogp_id = {$_POST['produtosGamers']}";
    }else if(!empty($_POST['produtosPdvs'])){
        $where .= " and ogp_id = {$_POST['produtosPdvs']}";
    }
    
    $sql = "select count(*) as total, ogp_id, sistema from clicks $where group by ogp_id, sistema  order by total desc";
    $rs = SQLexecuteQuery($sql);

    if($rs) {
        $totalRegistros = pg_num_rows($rs);
    }
    else 
        $totalRegistros = 0;
}else{
    $totalRegistros = 0;
}



?>
<style>
    .opt{cursor:pointer;}
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<form method="post" class="txt-preto" id="formBusca">
    <div class="col-md-2">
        <div class="form-group">
            <label for="dataIni" class="w100">*Data Inicial</label>
            <input type="text" name="dataIni" id="dataIni" value="<?php echo (!empty($_POST['dataIni'])) ? $_POST['dataIni'] : date("d/m/Y"); ?>" class="form-control input-sm">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="dataFim" class="w100">*Data Final</label>
            <input type="text" name="dataFim" id="dataFim" value="<?php echo (!empty($_POST['dataFim'])) ? $_POST['dataFim'] : date("d/m/Y"); ?>" class="form-control input-sm">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="sistema" class="w100">*Loja</label>
            <select class="input-sm form-control w-auto" name="sistema" id="sistema" label="Tipo de Usuário">
                <option value="">--</option>
                <option value="pdv" <?php if(isset($_POST['sistema']) && $_POST['sistema'] == "pdv") echo "selected"; ?>>Pdv</option>
                <option value="gamer" <?php if(isset($_POST['sistema']) && $_POST['sistema'] == "gamer") echo "selected"; ?>>Gamer</option>
            </select>
        </div>
    </div>
    <div class="col-md-3 <?php if((isset($_POST['sistema']) && $_POST['sistema'] !== "pdv") || empty($_POST['sistema'])) echo "hidden"?>" id="divProdutosPdvs">
        <div class="form-group">
            <label for="produtosPdvs" class="w100">Produtos de PDV</label>
            <select class="input-sm form-control w-auto" name="produtosPdvs" id="produtosPdvs" label="Lista de Produtos de PDV">
                <option value="">Todas</option>
                <?php
                    foreach($produtosPdv as $id => $produto){
                        
                        if(!empty($_POST['produtosPdvs']) && $_POST['produtosPdvs'] == $id)
                            $selected = "selected";
                        else
                            $selected = "";
                        
                        echo "<option value=\"{$id}\" {$selected}>{$produto} ({$id})</option>";
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-3 <?php if((!empty($_POST['sistema']) && $_POST['sistema'] !== "gamer") || empty($_POST['sistema'])) echo "hidden"?>" id="divProdutosGamers">
        <div class="form-group">
            <label for="produtosGamers" class="w100">Produtos de Gamers</label>
            <select class="input-sm form-control w-auto" name="produtosGamers" id="produtosGamers" label="Lista de Produtos de PDV">
                <option value="">Todas</option>
                <?php
                    foreach($produtosGamer as $id => $produto){
                        
                        if(!empty($_POST['produtosGamers']) && $_POST['produtosGamers'] == $id)
                            $selected = "selected";
                        else
                            $selected = "";
                        
                        echo "<option value=\"{$id}\" {$selected}>{$produto} ({$id})</option>";
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-2 pull-right">
        <input type="submit" class="btn pull-right top20 btn-sm btn-info" value="Filtrar">
    </div>
</form>
<div class="col-md-12">
    <table class="text-center table txt-preto table-bordered table-hover" >
        <thead>
            <tr class=" text-center">
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Sistema</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
<?php 
            if ($totalRegistros == 0) {
?>
            <tr>
                <td colspan="3">Nenhum resultado foi encontrado.</td>
            </tr>
<?php
            }else{
                while($rs_row = pg_fetch_array($rs)) {
?>
            <tr class="opt trListagem">
                <td><?php echo ($rs_row['sistema'] == "pdv") ? $produtosPdv[$rs_row['ogp_id']]: $produtosGamer[$rs_row['ogp_id']]; ?> (<?php echo $rs_row['ogp_id']; ?>)</td>
                <td><?php echo $rs_row['total']; ?></td>
                <td><?php echo $rs_row['sistema']; ?></td>
            </tr>
<?php
                }//end while
            }
?>
        </tbody>
    </table>
</div>
<div id="modal-erro" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Erro</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    <span class="glyphicon glyphicon-exclamation-sign dislineblock t0" aria-hidden="true"></span>
                    <span class="sr-only">Error:</span>
                    <span id="erroBuscaClicks" class="dislineblock"></span>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
         optDate.interval = 10000;

     setDateInterval('dataIni','dataFim',optDate);

      $("#sistema").change(function(){
          if($(this).val() == "pdv"){
              $("#divProdutosGamers").addClass("hidden");
              $("#produtosGamers").val("");
              $("#divProdutosPdvs").removeClass("hidden");

          }else if($(this).val() == "gamer"){
              $("#divProdutosPdvs").addClass("hidden");
              $("#produtosPdvs").val("");
              $("#divProdutosGamers").removeClass("hidden");

          }else{
              $("#divProdutosPdvs").addClass("hidden");
              $("#divProdutosGamers").addClass("hidden").val("");
              $("#produtosGamers").val("");
              $("#produtosPdvs").val("");
          }
    });
    
    $("#formBusca").submit(function(){
        var erro = 0;
        $("#erroBuscaClicks").html("");
        if($("#dataIni").val().length < 10 || $("#dataFim").val().length < 10){
            $("label[for='dataIni']").addClass("txt-vermelho");
            $("label[for='dataFim']").addClass("txt-vermelho");
            $("#erroBuscaClicks").append("<p>É necessário preencher um intervalo válido de datas.</p>");
            erro++;
        }else{
            $("label[for='dataIni']").removeClass("txt-vermelho");
            $("label[for='dataFim']").removeClass("txt-vermelho");
        }
        
        if($("#sistema").val().length < 1){
            $("label[for='sistema']").addClass("txt-vermelho");
            $("#erroBuscaClicks").append("<p>O campo \"Loja\" deve ser preenchido.</p>");
            erro++;
        }else{
            $("label[for='sistema']").removeClass("txt-vermelho");
        }
        
        if(erro > 0){
            $("#modal-erro").modal("show");
            return false;
        }else{
            return true;
        }
    });
});        
</script>
    