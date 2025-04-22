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

if(!empty($_POST['reordenar'])){
    
    $erro = array();
    
    foreach($_POST['reordenar'] as $id => $ordem){
        
        $sql = "select * from bo_menu where menu_id = ".$id;
        $rs_reordena = SQLexecuteQuery($sql);
        if(pg_num_rows($rs_reordena) > 0) {
            $sqlAtualizaAba = "update bo_menu set menu_order = $ordem where menu_id = $id";
            
            if(!$rsAtualiza = SQLexecuteQuery($sqlAtualizaAba)){
                
                $aba = pg_fetch_all($rs_reordena);
                
                $erro[] = "Erro ao atualizar menu ".$aba[0]['menu_descricao'];
            }
        }
    }
    
}

$totalRegistros = 0;

if(isset($_POST['aba'])){
    $sql = "SELECT * FROM bo_menu inner join bo_aba on bo_menu.aba_id = ".$_POST['aba']." and bo_menu.aba_id = bo_aba.aba_id order by menu_order asc";

    $rs = SQLexecuteQuery($sql);

    if($rs) {
        $totalRegistros = pg_num_rows($rs);
    }
}


$totalAbas = 0;
$sql = "select * from bo_aba";
if($rs_abas = SQLexecuteQuery($sql)){
    $totalAbas = pg_num_rows($rs_abas);
}

?>
<style>
    .opt{cursor:pointer;}
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="0">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li><a href="lista.php">Menus</a></li>
        <li class="active">Gerenciamento de Abas</li>
    </ol>
</div>
<div class="col-md-12">
    <a href="edita.php" class="btn btn-success">Nova</a>
</div>
<div class="col-md-12 top10">
    <div class="alert alert-info" role="alert">Para reordenar, selecione a aba, clique no menu desejado e arraste-o para a posição desejada, depois clique em salvar.
    </div>
<?php
    if(isset($erro)){
        if(empty($erro)){
            echo '<div class="alert alert-success" role="alert">Menus reordenadas com sucesso.</div>';
        }else{
            echo '<div class="alert alert-danger" role="alert">'.implode("<br>",$erro).'.</div>';
        }
    }
?>
</div>
<div class="col-md-3">
    <form method="post">
        <select name="aba" onchange="this.form.submit();" class="form-control">
            <option>Selecione a Aba</option>
<?php
        if($totalAbas > 0){
            while($rs_abas_row = pg_fetch_array($rs_abas)) {
                
                $selected = (isset($_POST['aba']) && $rs_abas_row['aba_id'] == $_POST['aba']) ? "selected" : "";
                echo '<option value="'.$rs_abas_row['aba_id'].'" '.$selected.'>'.$rs_abas_row['aba_descricao'].'</option>';
            }//end while
        }
?>
        </select>
    </form>
</div>
<div class="col-md-6">
    <form method="post" id="editaBanner">
        <?php
            if(isset($_POST['aba']))
                echo "<input type='hidden' name='aba' value='".$_POST['aba']."'>";
        ?>
        <table class="text-center table table-bordered table-hover" >
            <thead class="">
                <tr class="text-center">
                    <th>Menu</th>
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
                <tr class="opt trListagem c-move" id="<?php echo $rs_row['menu_id']; ?>">
                    <td>
                        <?php echo (@constant(trim($rs_row['menu_descricao'])) === null) ? $rs_row['menu_descricao'] : constant(trim($rs_row['menu_descricao'])); ?>
                        <input type="hidden" name="reordenar[<?php echo $rs_row['menu_id']; ?>]" value="">
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