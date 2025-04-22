<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";

if(!b_IsBKOUsuarioAdminBKO())
{
    Util::redirect("/");
}

if(!empty($_POST['reordenar'])){
    
    $erro = array();
    
    foreach($_POST['reordenar'] as $id => $ordem){
        
        $sql = "select * from bo_aba where aba_id = ".$id;
        $rs_reordena = SQLexecuteQuery($sql);
        if(pg_num_rows($rs_reordena) > 0) {
            $sqlAtualizaAba = "update bo_aba set aba_order = $ordem where aba_id = $id";
            
            if(!$rsAtualiza = SQLexecuteQuery($sqlAtualizaAba)){
                
                $aba = pg_fetch_all($rs_reordena);
                
                $erro[] = "Erro ao atualizar a aba ".$aba[0]['aba_descricao'];
            }
        }
    }
    
}

$totalRegistros = 0;

if(isset($_POST['sistema'])){
    $sql = "SELECT * FROM bo_aba where aba_sistema = '".$_POST['sistema']."' order by aba_order asc";    
    
    $rs = SQLexecuteQuery($sql);

    if($rs) {
        $totalRegistros = pg_num_rows($rs);
    }
}
?>
<style>
    .opt{cursor:pointer;}
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="0">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li><a href="lista.php">Abas</a></li>
        <li class="active">Reordenação de abas</li>
    </ol>
</div>
<div class="col-md-12">
    <a href="edita.php" class="btn btn-success">Nova</a>
</div>
<div class="col-md-12 top10">
    <div class="alert alert-info" role="alert">Para reordenar, selecione o sistema, clique na aba e arraste para a posição desejada e clique em salvar.</div>
<?php
    if(isset($erro)){
        if(empty($erro)){
            echo '<div class="alert alert-success" role="alert">Abas reordenadas com sucesso.</div>';
        }else{
            echo '<div class="alert alert-danger" role="alert">'.implode("<br>",$erro).'.</div>';
        }
    }
?>
</div>

<div class="col-md-3">
    <form method="post">
        <select name="sistema" onchange="this.form.submit();" class="form-control">
            <option value="">Selecione o Sistema</option>
            <option value="backoffice" <?php if(isset($_POST['sistema']) && $_POST['sistema'] == "backoffice") echo "selected"; ?>>backoffice</option>
            <option value="sysadmin" <?php if(isset($_POST['sistema']) && $_POST['sistema'] == "sysadmin") echo "selected"; ?>>sysadmin</option>
        </select>
    </form>
</div>
<div class="col-md-6">
    <form method="POST" id="editaBanner">
        <?php
            if(isset($_POST['sistema']))
                echo "<input type='hidden' name='sistema' value='".$_POST['sistema']."'>";
        ?>
        <table class="text-center table table-bordered table-hover" >
            <thead>
                <tr class="text-center">
                    <th>Nome / Sistema</th>
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
                <tr class="opt trListagem c-move"  id="<?php echo $rs_row['aba_id']; ?>">
                    <td>
                        <?php echo $rs_row['aba_descricao']; ?> / <?php echo $rs_row['aba_sistema']; ?>
                        <input type="hidden" name="reordenar[<?php echo $rs_row['aba_id']; ?>]" value="">
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