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

$on = "";
if(isset($_POST['aba']) && $_POST['aba'] != ""){
    $on = " AND bo_aba.aba_id = ".$_POST['aba'];
}

$sql = "SELECT * FROM bo_menu inner join bo_aba on bo_menu.aba_id = bo_aba.aba_id $on order by menu_order asc";

$rs = SQLexecuteQuery($sql);

if($rs) {
    $totalRegistros = pg_num_rows($rs);
}
else $totalRegistros = 0;

$abas = array();
$con = ConnectionPDO::getConnection();
if ($con->isConnected()){
    $pdo = $con->getLink();
    $sql = "SELECT * FROM bo_aba order by aba_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $abas = $stmt->fetchAll(PDO::FETCH_OBJ);
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
<div class="col-md-12 top10">
    <div class="alert alert-info" role="alert">Para alterar algum menu, clique sobre ele.</div>
</div>
<div class="col-md-12 top20">
    <div class="form-group has-feedback">
        <label class="control-label col-md-2" for="menu">
            Filtrar por aba:
        </label>
        <div class="col-md-3">
<?php
        if(!empty($abas)){
?>
        <form method="post">
            <select name="aba" id="aba" class="form-control" onchange="this.form.submit();">
                <option value="">Selecione a aba</option>
<?php 
            foreach($abas as $aba){ 
?>
                <option value="<?php echo $aba->aba_id; ?>" <?php if(isset($_POST['aba']) != "" && $aba->aba_id == $_POST['aba']) echo "selected"; ?>><?php echo $aba->aba_descricao; ?></option>
<?php 
            } 
?>
            </select>
        </form>
<?php
        }else{
?>
            Nenhum menu cadastrado
<?php

        }
?>
        </div>
    </div>
</div>
<div class="col-md-12 top20">
    <table class="text-center table table-bordered table-hover" >
        <thead class="">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Aba</th>
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
            <tr class="opt trListagem" id="<?php echo $rs_row['menu_id']; ?>">
                <td><?php echo $rs_row['menu_id']; ?></td>
                <td><?php echo (@constant(trim($rs_row['menu_descricao'])) === null) ? $rs_row['menu_descricao'] : constant(trim($rs_row['menu_descricao'])); ?></td>
                <td><?php echo $rs_row['aba_descricao']; ?></td>
            </tr>
<?php
                }//end while
            }
?>
        </tbody>
    </table>
</div>
<form method="post" action="edita.php" id="frmAbas">
    <input type="hidden" id="id" name="id" value="">
    <input type="hidden" id="aba" name="aba" value="<?php echo (isset($_POST['aba'])) ? $_POST['aba'] : "" ; ?>">
</form>
<script>
    $(function(){
        $(".opt").click(function(){
            $("#id").val($(this).attr("id"));
            $("#frmAbas").submit();
        });
    });
</script>  
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>