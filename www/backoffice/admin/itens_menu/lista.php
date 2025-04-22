<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."public_html/sys/includes/language/eprepag_lang_pt.inc.php";

if(!b_IsBKOUsuarioAdminBKO())
{
    Util::redirect("/");
}

$menus = array();
$abas = array();
$totalRegistros = 0;

$con = ConnectionPDO::getConnection();
if ($con->isConnected()){
    $pdo = $con->getLink();
    
    $sql = "select * from bo_aba order by aba_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $abas = $stmt->fetchAll(PDO::FETCH_OBJ);

    if(count($abas) > 0 && isset($_POST['aba']) && $_POST['aba']){
    
        $where = " where aba_id = ".$_POST['aba'];
        
        $sql = "SELECT * FROM bo_menu $where order by menu_order";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $menus = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $on = "";
        if(isset($_POST['menu']) && $_POST['menu'] != ""){
            
            $on = " AND menu.menu_id = ".$_POST['menu'];
            $sql = "SELECT * FROM bo_item item inner join bo_menu menu on item.menu_id = menu.menu_id $on order by item.item_order asc";

            $rs = SQLexecuteQuery($sql);

            if($rs) {
                $totalRegistros = pg_num_rows($rs);
            }
        }else{
            
            $sql = "SELECT 
                        * 
                    FROM 
                        bo_item item 
                    inner join 
                        bo_menu menu 
                    on 
                        item.menu_id = menu.menu_id  
                    and
                        menu.aba_id = {$_POST['aba']}
                    order by 
                        menu.menu_id asc";

            $rs = SQLexecuteQuery($sql);

            if($rs) {
                $totalRegistros = pg_num_rows($rs);
            }
        }
        
    }
   
}
?>
<style>
    .opt{cursor:pointer;}
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<div class="col-md-12">
    <a href="edita.php" class="btn btn-success">Nova</a>
    <a href="reordena.php" class="btn btn-success">Reordenar</a>
</div>
<div class="col-md-12 top20">
    <div class="col-md-12 bottom10 txt-preto">
        <h3>Filtrar:</h3>
    </div>
    <form method="post" id="filtro">
        <div class="form-group has-feedback">
            <label class="control-label col-md-1" for="menu">
                Aba:
            </label>
            <div class="col-md-3">
    <?php
            if(!empty($abas)){
    ?>
                <select name="aba" id="aba" class="form-control">
                    <option value="">Selecione a aba</option>
    <?php 
                foreach($abas as $aba){ 
    ?>
                    <option value="<?php echo $aba->aba_id; ?>" <?php if(isset($_POST['aba']) != "" && $aba->aba_id == $_POST['aba']) echo "selected"; ?>><?php echo $aba->aba_descricao; ?></option>
    <?php 
                } 
    ?>
                </select>
<?php
            }else{
?>
                Nenhuma aba foi encontrada
<?php

            }
?>
            </div>
        </div>
<?php
            if(!empty($menus)){
?>
        <div class="form-group has-feedback">
            <label class="control-label col-md-1" for="menu">
                Menu:
            </label>
            <div class="col-md-3">

                <select name="menu" id="menu" class="form-control" onchange="this.form.submit();">
                    <option value="">Selecione o menu</option>
<?php 
                foreach($menus as $menu){ 
?>
                    <option value="<?php echo $menu->menu_id; ?>" <?php if(isset($_POST['menu']) != "" && $menu->menu_id == $_POST['menu']) echo "selected"; ?>><?php echo (@constant(trim($menu->menu_descricao)) === null) ? $menu->menu_descricao : constant(trim($menu->menu_descricao)); ?></option>
<?php 
                } 
?>
                </select>
                </div>
        </div>
        
<?php
            }
?>
    </form>
</div>
<div class="col-md-12 top10">
    <div class="alert alert-info" role="alert">Para alterar algum item, clique sobre ele.</div>
</div>
<div class="col-md-12">
    <table class="text-center table table-bordered table-hover" >
        <thead class="">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Link</th>
                <th>Monitor</th>
                <th>Menu</th>
                <th>Listagem Principal</th>
                <th>Ordem</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
<?php 
            if ($totalRegistros == 0) {
?>
            <tr>
                <td colspan="7">Nenhum resultado foi encontrado.</td>
            </tr>
<?php
            }else{
                while($rs_row = pg_fetch_array($rs)) {
?>
            <tr class="opt trListagem" id="<?php echo $rs_row['item_id']; ?>">
                <td><?php echo $rs_row['item_id']; ?></td>
                <td><?php echo (@constant(trim($rs_row['item_descricao'])) === null) ? $rs_row['item_descricao'] : constant(trim($rs_row['item_descricao'])); ?></td>
                <td><?php echo (strlen($rs_row['item_link']) > 60) ? substr($rs_row['item_link'],0,60)."..." : $rs_row['item_link']; ?></td>
                <td><?php echo $rs_row['item_monitor']; ?></td>
                <td><?php echo (@constant(trim($rs_row['menu_descricao'])) === null) ? $rs_row['menu_descricao'] : constant(trim($rs_row['menu_descricao'])); ?></td>
                <td><?php echo ($rs_row['item_aparece_menu'] == "1") ? "Sim" : "Não"; ?></td>
                <td><?php echo $rs_row['item_order']; ?></td>
            </tr>
<?php
                }//end while
?>
            <tr>
                <td colspan="7">Total de registros encontrados: <?php echo $totalRegistros; ?></td>
            </tr>
<?php
            }
?>
        </tbody>
    </table>
</div>
<form method="post" action="edita.php" id="frmItensAba">
    <input type="hidden" id="id" name="id" value="">
    <input type="hidden" id="menu_item" name="menu_item" value="<?php echo (isset($_POST['menu'])) ? $_POST['menu'] : "" ;?>">
</form>
<script>
    $(function(){
        $(".opt").click(function(){
            $("#id").val($(this).attr("id"));
            $("#frmItensAba").submit();
        });
        
        $("#aba").change(function(){
            $("#menu").val(false);
            $("#filtro").submit();
        });
        
    });
</script>  
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>