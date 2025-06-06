<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
include_once $raiz_do_projeto.'/class/util/Validate.class.php';
include_once $raiz_do_projeto.'/class/util/Util.class.php';

$sql = "SELECT * FROM pesquisa_ernst_young";

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
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<div class="col-md-12" style="overflow-x: scroll;">
    <table id="table" class="text-center fontsize-pp table table-bordered table-hover" 
        <thead class="">
            <tr>
                <th>pin</th>
                <th>email</th>
                <th>ano_nascimento</th>
                <th>sexo</th>
                <th>compra_mes</th>
                <th>comprou_internet</th>
                <th>conta_banco</th>
                <th>cartao_credito</th>
                <th>smart_phone</th>
                <th>ug_id_pdv</th>
                <th>opr_codigo</th>
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
                <td><?php echo $rs_row['pin']; ?></td>
                <td><?php echo $rs_row['email']; ?></td>
                <td><?php echo $rs_row['ano_nascimento']; ?></td>
                <td><?php echo $rs_row['sexo']; ?></td>
                <td><?php echo $rs_row['compra_mes']; ?></td>
                <td><?php echo $rs_row['comprou_internet'] == "t" ? "Sim" : "Não"; ?></td>
                <td><?php echo $rs_row['conta_banco'] == "t" ? "Sim" : "Não"; ?></td>
                <td><?php echo $rs_row['cartao_credito'] == "t" ? "Sim" : "Não"; ?></td>
                <td><?php echo $rs_row['smart_phone'] == "t" ? "Sim" : "Não"; ?></td>
                <td><?php echo $rs_row['ug_id_pdv']; ?></td>
                <td><?php echo $rs_row['opr_codigo']; ?></td>
            </tr>
<?php
                }//end while
            }
?>
        </tbody>
    </table>
    <div class="col-md-6">Total de registros: <?php echo $totalRegistros;?></div>
    <div class="col-md-6"><a href="#" class="btn downloadCsv btn-info ">Download CSV</a></div>
</div>
<script src="<?= EPREPAG_URL_HTTPS ?>/js/table2CSV.js"></script>
<script>
$(function(){
    $('#table').table2CSV({header:['pin','email','ano_nascimento','comprou_internet','conta_banco','cartao_credito','smart_phone','ug_id_pdv','opr_codigo'],toStr:""});
});
</script>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>