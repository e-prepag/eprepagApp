<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."public_html/sys/includes/language/eprepag_lang_pt.inc.php";

$con = ConnectionPDO::getConnection();
if (!$con->isConnected()){
    die("Erro de conexão");
}
$pdo = $con->getLink();
    
if(!b_IsBKOUsuarioAdminBKO())
{
    Util::redirect("/");
}

if(!empty($_POST['reordenar'])){
    
    $erro = array();
    
    foreach($_POST['reordenar'] as $id => $ordem){
        
        $sql = "select * from bo_item where item_id = ".$id;
        $rs_reordena = SQLexecuteQuery($sql);
        if(pg_num_rows($rs_reordena) > 0) {
            $sqlAtualizaAba = "update bo_item set item_order = $ordem where item_id = $id";
            
            if(!$rsAtualiza = SQLexecuteQuery($sqlAtualizaAba)){
                
                $aba = pg_fetch_all($rs_reordena);
                
                $erro[] = "Erro ao atualizar item ".$aba[0]['menu_descricao'];
            }
        }
    }
    
}

$totalRegistros = 0;
$totalMenus = 0;
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
    }
}
?>
<style>
    .opt{cursor:pointer;}
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="0">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li><a href="lista.php">Menus</a></li>
        <li class="active">Gerenciamento de Menus</li>
    </ol>
</div>
<div class="col-md-12">
    <a href="edita.php" class="btn btn-success">Nova</a>
</div>
<div class="col-md-12 top10">
    <div class="alert alert-info" role="alert">Para reordenar, selecione o menu, clique no item desejado e arraste-o para a posição desejada, depois clique em salvar.
</div>
<?php
    if(isset($erro)){
        if(empty($erro)){
            echo '<div class="alert alert-success" role="alert">Itens reordenados com sucesso.</div>';
        }else{
            echo '<div class="alert alert-danger" role="alert">'.implode("<br>",$erro).'.</div>';
        }
    }
?>
</div>
<div class="col-md-3">
    <form method="post">
    <?php
            if(!empty($abas)){
    ?>
                <select name="aba" onchange="this.form.submit();" id="aba" class="form-control">
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
            
            if(!empty($menus)){
?>
                <select name="menu" onchange="this.form.submit();" class="form-control top10">
                    <option value="">Selecione o menu</option>
<?php 
                foreach($menus as $menu){ 
?>
                    <option value="<?php echo $menu->menu_id; ?>" <?php if(isset($_POST['menu']) != "" && $menu->menu_id == $_POST['menu']) echo "selected"; ?>><?php echo (@constant(trim($menu->menu_descricao)) === null) ? $menu->menu_descricao : constant(trim($menu->menu_descricao)); ?></option>
<?php 
                } 
?>
                </select>
<?php
            }
?>
    </form>
</div>
<div class="col-md-6">
    <form method="post" id="editaBanner">
        <?php
            if(isset($_POST['menu']))
                echo "<input type='hidden' name='menu' value='".$_POST['menu']."'>";
        ?>
        <table class="text-center table table-bordered table-hover" >
            <thead class="">
                <tr class="text-center">
                    <th>Item</th>
                </tr>
            </thead>
            <tbody title="Clique para editar">
    <?php 
                if ($totalRegistros == 0) {
    ?>
                <tr>
                    <td colspan="1">Nenhum resultado foi encontrado.</td>
                </tr>
    <?php
                }else{
                    while($rs_row = pg_fetch_array($rs)) {
    ?>
                <tr class="opt trListagem c-move" id="<?php echo $rs_row['item_id']; ?>">
                    <td>
                        <?php echo (@constant(trim($rs_row['item_descricao'])) === null) ? $rs_row['item_descricao'] : constant(trim($rs_row['item_descricao'])); ?>
                        <input type="hidden" name="reordenar[<?php echo $rs_row['item_id']; ?>]" value="">
                    </td>
                </tr>
    <?php
                    }//end while
                }
    ?>
            </tbody>
        </table>
        <button type="button" class="btn btn-success" onclick="reordena();">Salvar</button>
    </form>
</div>
<script>
    $(function(){
        $("tbody").sortable({
            appendTo: "parent",
            helper: "clone"
        }).disableSelection();
        
    });
    
    function reordena(){
        $(".table-bordered tr td").each(function(i){
           $(this).children().val(i);
        });

        $("#editaBanner").submit();
    }
</script>   
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>