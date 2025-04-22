<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";

$pos_pagina = false; //apenas para nao exibir erro/ resolver depois

if(!b_IsBKOUsuarioAdminBKO())
{
    Util::redirect("/");
}

$sql = "SELECT * FROM grupos_usuarios order by grupos_descricao asc";

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
    <a href="edita.php" class="btn btn-success">Novo</a>
</div>
<div class="col-md-12 top10">
    <div class="alert alert-info" role="alert">Para alterar algum grupo de usuário, clique sobre ele.</div>
</div>
<div class="col-md-12">
    <table class="text-center table table-bordered table-hover" >
        <thead class="">
            <tr>
                <th>ID</th>
                <th>Nome</th>
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
            <tr class="opt trListagem" id="<?php echo $rs_row['grupos_id']; ?>">
                <td><?php echo $rs_row['grupos_id']; ?></td>
                <td><?php echo $rs_row['grupos_descricao']; ?></td>
            </tr>
<?php
                }//end while
            }
?>
        </tbody>
    </table>
</div>
<form method="post" action="edita.php" id="frmGrupos">
    <input type="hidden" id="id" name="id" value="">
</form>
<script>
    $(function(){
        $(".opt").click(function(){
            $("#id").val($(this).attr("id"));
            $("#frmGrupos").submit();
        });
    });
</script>  
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>