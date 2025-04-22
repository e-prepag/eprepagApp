<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";

$menus = array();
$abas = array();
$totalRegistros = 0;

$dataI = (isset($_POST['dataInicial'])) ? $_POST['dataInicial'] : date("d/m/Y");
$dataF = (isset($_POST['dataFinal'])) ? $_POST['dataFinal'] : date("d/m/Y");
$banco = (isset($_POST['banco'])) ? $_POST['banco'] : "";
$forma = (isset($_POST['formaPagamento'])) ? $_POST['formaPagamento'] : "";
$where = "";
$con = ConnectionPDO::getConnection();
if ($con->isConnected()){
    $pdo = $con->getLink();
    
    $sql = "select max(data) as data_max , id_forma, LPAD(banco::text, 3, '0') as banco
                from 
                    taxas_transacoes_cobradas_da_epp                
                group by id_forma, banco 
                order by banco,id_forma ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $taxas = $stmt->fetchAll(PDO::FETCH_OBJ);
    $totalRegistros = count($taxas);
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
<div class="col-md-12 top10">
    <div class="alert alert-info" role="alert">Lista de taxas vigentes para cada banco e forma de pagamento.</div>
</div>
<div class="col-md-12">
    <table class="txt-preto table table-bordered table-hover" >
        <thead class="">
            <tr>
                <th>Formas de Pagamento</th>
<?php 
            foreach($PAGTO_BANCOS as $idBanco => $banco){
                $arrBanco = explode(" / ",$banco);
?>              
                <th class="nowrap"><?=$arrBanco[0];?></th>
<?php 
            } 
?>
            </tr>
        </thead>
        
            
<?php 
        foreach($FORMAS_PAGAMENTO_DESCRICAO as $idForma => $forma){
?>
        <tr>
            <td><?=$forma?></td>
<?php 
            foreach($PAGTO_BANCOS as $idBanco => $banco){
                $td = false;
                foreach($taxas as $taxa){
                    if($taxa->id_forma == $idForma && $taxa->banco == $idBanco){
                        $sql_aux = "select taxa from taxas_transacoes_cobradas_da_epp where banco = '".$idBanco."' 
                        and id_forma = '".$idForma."' and data = '".$taxa->data_max."'";
                        
                        $stmt_aux = $pdo->prepare($sql_aux);
                        $stmt_aux->execute();
                        $tx = $stmt_aux->fetch(PDO::FETCH_ASSOC);
                        echo "<td class='nowrap'>R$ ".Util::getNumero($tx['taxa'])."</td>";
                        $td = true;
                    }
                }
                
                if(!$td)
                    echo "<td class=\"bg-cinza\">&nbsp;</td>";
            }
?>
        </tr>
<?php
        }
?>
            
    </table>
</div>
<form method="post" action="edita.php" id="frmItensAba">
    <input type="hidden" id="id" name="id" value="">
</form>
<script src="/js/jquery.mask.min.js"></script>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $server_url_ep; ?>/js/global.js"></script>
<script>
    $(function(){
        
        $("#dataInicial").datepicker();
        $("#dataFinal").datepicker();
        
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