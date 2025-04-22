<?php
$banners = array();
$banners = $objBanner->pegaBanner();
$where_c = "bsc_status = 1";
$where_p = "bsp_status = 1";
$categorias = $objCategoria->pegaCategoria($where_c);
$posicoes = $objPosicao->pegaPosicao($where_p);
$icon = "glyphicon-menu-down";

if(isset($_POST["busca"])){
    $filtro = "";
    $where = array();
    $order = "";
    
    if(!empty($_POST["bs_titulo"]))         $where[] = "UPPER(bs_titulo) like '%".strtoupper ($_POST["bs_titulo"])."%'";
    if(!empty($_POST["bs_data_cadastro"]))  $where[] = "bs_data_cadastro  = '".Util::getData($_POST["bs_data_cadastro"], true)."'";
    if(!empty($_POST["bsp_id"]))            $where[] = "bsp_id = ".$_POST["bsp_id"];
    if(!empty($_POST["bsc_id"]))            $where[] = "bsc_id = ".$_POST["bsc_id"];
    if($_POST["bs_status"] != "")           $where[] = "bs_status = ".$_POST["bs_status"];
    if(!empty($_POST["vigente"])){
            $where[] =  $_POST["vigente"] == "sim" ? "(bs_data_inicio  <= '".date("Y-m-d")." 00:00:00' and 
                                                    bs_data_fim >= '".date("Y-m-d")." 00:00:00' and
                                                    bs_status = 1)"
                                                 :
                                                   "(bs_data_inicio  > '".date("Y-m-d")." 00:00:00' or 
                                                    bs_data_fim < '".date("Y-m-d")." 00:00:00' or
                                                    bs_status = 0)";
    }
            
    
    if(!empty($_POST['order']) && !empty($_POST['order_type'])){
        $order = $_POST['order']. " " . $_POST['order_type'];
        
        if($_POST['order_type'] == "DESC")
            $icon = "glyphicon-menu-up";
    }
    
    $bannersBusca = $objBanner->pegaBanner($where, null, $order);
}else{
        
    $bannersBusca = $banners;
}
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><a href="banners.php"><?php echo $sistema->menu[0]->getDescricao(); ?></a></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12">
    <div style="borda">
        <a href="banners.php?acao=novo" class="btn btn-info btn-sm">Novo Banner</a>
    </div>
</div>
<?php
if(isset($msg))
{
?>
    <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-vermelho top20">
        <strong><h4>Erro: <?php echo $msg?></h4></strong>
    </div>
<?php
}
?>

<div class=" txt-preto col-md-12 col-xs-12 col-sm-12 col-lg-12">
    <form id="buscaBanner" name="buscaBanner" method="post">
        <h3>Filtrar:</h3>
        <div class="col-md-1 col-sm-12 col-lg-1 col-xs-12">
            Titulo: 
        </div>
        <div class="col-md-3 col-sm-12 col-lg-3 col-xs-12">
            <input type="text" <?php if(isset($_POST["bs_titulo"]))  echo "value='".$_POST["bs_titulo"]."'"; ?> name="bs_titulo" id="bs_titulo" char="4" class="form-control w100">
        </div>
        <div class="col-md-1 col-sm-12 col-lg-1 col-xs-12">
            Categoria:
        </div>
        <div class="col-md-3 col-sm-12 col-lg-3 col-xs-12">
<?php
if(!empty($categorias)){
?>
            <select class="form-control w100" name="bsc_id" char="1" id="bsc_id" label="Categoria ">
                <option value="">--</option>
<?php
                        foreach ($categorias as $categoria){
?>
                        <option value="<?php echo $categoria->getId(); ?>" <?php if(isset($_POST["bsc_id"]) && $_POST["bsc_id"] == $categoria->getId()) echo "selected"; ?>><?php echo $categoria->getDescricao();?></option>
<?php
                        }
?>                    
            </select>
<?php
}else{
?>
            <span>Não temos categorias cadastradas</span>
<?php
}
?>
        </div>
        <div class="col-md-1 col-sm-12 col-lg-1 col-xs-12">
            Posicao:
        </div>
        <div class="col-md-3 col-sm-12 col-lg-3 col-xs-12">
<?php
if(!empty($posicoes)){
?>
            <select class="form-control w100" name="bsp_id" char="1" id="bsp_id" label="Posição ">
                <option value="">--</option>
<?php
                        foreach ($posicoes as $posicao){
?>
                        <option value="<?php echo $posicao->getId(); ?>" <?php if(isset($_POST["bsp_id"]) && $_POST["bsp_id"] == $posicao->getId()) echo "selected"; ?>><?php echo $posicao->getDescricao();?></option>
<?php
                        }
?>                    
            </select>
<?php
}else{
?>
            <span>Não temos posições cadastradas</span>
<?php
}
?>
        </div>
        <div class="col-md-1 col-sm-12 col-lg-1 col-xs-12 top10">
            Status: 
        </div>
        <div class="col-md-3 col-sm-12 col-lg-3 col-xs-12 top10">
            <select class="form-control w100" name="bs_status" char="1" id="bs_status">
                <option value="">--</option>
                <option value="1" <?php if(isset($_POST["bs_status"]) && $_POST["bs_status"] == "1") echo "selected"; ?>>Ativo</option>
                <option value="0" <?php if(isset($_POST["bs_status"]) && $_POST["bs_status"] == "0") echo "selected"; ?>>Inativo</option>
            </select>
        </div>
        <div class="col-md-1 col-sm-12 col-lg-1 col-xs-12 top10">
            Vigente: 
        </div>
        <div class="col-md-3 col-sm-12 col-lg-3 col-xs-12 top10">
            <select class="form-control w100" name="vigente" char="1" id="vigente">
                <option value="">--</option>
                <option value="sim" <?php if(isset($_POST["vigente"]) && $_POST["vigente"] == "sim") echo "selected"; ?>>Sim</option>
                <option value="nao" <?php if(isset($_POST["vigente"]) && $_POST["vigente"] == "nao") echo "selected"; ?>>Não</option>
            </select>
        </div>
        <div class="col-md-4 col-sm-12 col-lg-4 col-xs-12 top10">
            <input type="hidden" name="busca" value="1">
            <input type="button" value="Buscar" id="buscar" name="buscar" class="btn btn-sm btn-info pull-right">
        </div>
        <input type='hidden' name="order" id="order">
        <input type='hidden' name="order_type" id="order_type">
    </form>
</div>
<div class="txt-preto col-md-12 col-xs-12 col-sm-12 col-lg-12 top20">
    <table class="text-center table table-bordered" >
        <thead class="">
            <tr>
                <th>ID <span class="glyphicon <?php echo (!empty($_POST['order']) && $_POST['order'] == "bs_id") ? $icon : "glyphicon-menu-down";?> t0 c-pointer" attr='bs_id'></span></th>
                <th>Titulo</th>
                <th>Categoria</th>
                <th>Posição</th>
                <th>Data Inicio  <span class="glyphicon <?php echo (!empty($_POST['order']) && $_POST['order'] == "bs_data_inicio") ? $icon : "glyphicon-menu-down";?> t0 c-pointer" attr='bs_data_inicio'></span></th>
                <th>Data Fim  <span class="glyphicon <?php echo (!empty($_POST['order']) && $_POST['order'] == "bs_data_fim") ? $icon : "glyphicon-menu-down";?> t0 c-pointer" attr='bs_data_fim'></span></th>
                <th>Clicks</th>
                <th>Ordenação  <span class="glyphicon <?php echo (!empty($_POST['order']) && $_POST['order'] == "bs_ordenacao") ? $icon : "glyphicon-menu-down";?> t0 c-pointer" attr='bs_ordenacao'></span></th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
<?php 
            if(is_array($bannersBusca)){
                foreach($bannersBusca as $banner){
                    
                    $dataIni = $banner->getDataInicio();
                    $dataFim = $banner->getDataFim();
                    $status = $banner->getStatus();
//                    var_dump($dataIni);
//                    die;
                    $dataI = mktime(0, 0, 0, substr($dataIni,3,2), substr($dataIni,0,2), substr($dataIni,6,4));
                    $dataF = mktime(0, 0, 0, substr($dataFim,3,2), substr($dataFim,0,2), substr($dataFim,6,4));
                    $bg = ($dataI > time() || $dataF < time() || $status != 1) ? "bg-vermelho" : "bg-verde-claro";
?>
            <tr class="trListagem bannersOpt c-pointer <?php echo $bg;?>" id="<?php echo $banner->getId(); ?>">
                <td><?php echo $banner->getId(); ?></td>
                <td><?php echo $banner->getTitulo(); ?></td>
                <td><?php echo $banner->getCategoria()->getDescricao(); ?></td>
                <td><?php echo $banner->getPosicao()->getDescricao(); ?></td>
                <td><?php echo $banner->getDataInicio(); ?></td>
                <td><?php echo $banner->getDataFim(); ?></td>
                <td><?php echo $banner->getClicks(); ?></td>
                <td><?php echo $banner->getOrdenacao(); ?></td>
                <td><?php echo ($banner->getStatus() == 1) ? "Ativo" : "Inativo"; ?></td>
            </tr>
<?php
                }
            }else{
?>
            <tr>
                <td colspan="9">Nenhum resultado foi encontrado.</td>
            </tr>
<?php
            }
?>
        </tbody>
    </table>
</div>
<script>
    $(function(){
        
        $(".glyphicon-menu-down").click(function(){
           $("#order").val($(this).attr("attr"));
           $("#order_type").val("DESC");
           $("#buscaBanner").submit();
        });
        
        $(".glyphicon-menu-up").click(function(){
           $("#order").val($(this).attr("attr"));
           $("#order_type").val("ASC");
           $("#buscaBanner").submit();
        });
        
        $(".bannersOpt").click(function(){
            window.location = "banners.php?acao=edita&id="+$(this).attr("id");
        });
        
        $("#buscar").click(function(){
            $("#"+$(this).get(0).form.id).submit();
       });
    });
</script>