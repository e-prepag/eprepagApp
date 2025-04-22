<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";

if(!b_IsBKOUsuarioAdminBKO())
{
    Util::redirect("/");
}

$sql = "SELECT * FROM bo_aba order by aba_descricao asc";

$rs = SQLexecuteQuery($sql);

if($rs) {
    $totalRegistros = pg_num_rows($rs);
}
else $totalRegistros = 0;

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
    <div class="alert alert-info" role="alert">Para alterar alguma aba, clique sobre ela.</div>
</div>
<div class="col-md-12">
    <table class="text-center table table-bordered table-hover" >
        <thead class="">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Link</th>
                <th>Sistema</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
<?php 
            if ($totalRegistros == 0) {
?>
            <tr>
                <td colspan="2">Nenhum resultado foi encontrado.</td>
            </tr>
<?php
            }else{
                while($rs_row = pg_fetch_array($rs)) {
?>
            <tr class="opt trListagem" id="<?php echo $rs_row['aba_id']; ?>">
                <td><?php echo $rs_row['aba_id']; ?></td>
                <td><?php echo $rs_row['aba_descricao']; ?></td>
                <td><?php echo $rs_row['aba_link']; ?></td>
                <td><?php echo $rs_row['aba_sistema']; ?></td>
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