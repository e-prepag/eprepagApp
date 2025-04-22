<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
include_once $raiz_do_projeto."class/util/Util.class.php";
require_once "/www/includes/bourls.php";
set_time_limit(3600);
/* 
    CONTROLLER
 */

//Levantamento de Publishers
$sql = "SELECT opr_codigo, opr_nome FROM operadoras ORDER BY opr_nome;";
$rs = SQLexecuteQuery($sql);
while ($rsRow = pg_fetch_array($rs)) {
    $arrayPublihers[$rsRow['opr_codigo']] = $rsRow['opr_nome'];
}//end While

if(isset($BtnSearch) && $BtnSearch) {
        //Variavel de OUTPUT
        $msg = "";
        
        //Montando SQL
        $sql = "
                SELECT fp_publisher, 
                        CASE WHEN fp_company = 0 THEN 'EPP Pagamentos' 
                             WHEN fp_company = 1 THEN 'EPP Administradora' 
                             ELSE '' END AS empresa, 
                        CASE WHEN fp_nationality = 0 THEN 'NACIONAL' 
                             WHEN fp_nationality = 1 THEN 'INTERNACIONAL' 
                             ELSE '' END AS nacionalidade, 
                        sum(fp_number) as quantidade,
                        sum(fp_total) as total,
                        sum(fp_comission) as comissao,
                        date_trunc('day', fp_date) as data
                FROM financial_processing
                WHERE fp_date >= '".formata_data($_POST["data_inicial"],1)." 00:00:00'
                        AND fp_date <= '".formata_data($_POST["data_final"],1)." 23:59:59' 
                        ";
        if($_POST["opr_codigo"] != "ALL") {
                $sql .= "AND fp_publisher = ".$_POST["opr_codigo"];
        } // end if($_POST["opr_codigo"] != "ALL")
        if($_POST["empresa"] != "ALL") {
                $sql .= "AND fp_company = ".$_POST["empresa"];
        } // end if($_POST["empresa"] != "ALL")
        if($_POST["nacionalidade"] != "ALL") {
                $sql .= "AND fp_nationality = ".$_POST["nacionalidade"];
        } // end if($_POST["nacionalidade"] != "ALL")
        $sql .= " 
                GROUP BY fp_publisher, fp_company, fp_nationality, data
                ORDER BY data, fp_company, fp_nationality;
                ";
        
        //echo $sql."<br>";
        $rs = SQLexecuteQuery($sql);
        if($rs) {
                if(pg_num_rows($rs)>0) {
                        $total_qtde = 0;
                        $total_geral = 0;
                        $total_comissao = 0;
                        $total_liquido = 0;
                        $msg .= "<table class='table table-bordered txt-preto fontsize-pp'>
                                    <tr class='text-center negrito'>
                                        <td>Data</td>
                                        <td>Empresa</td>
                                        <td>Nacionalidade</td>
                                        <td>Publisher</td>
                                        <td>Qtde Transações</td>
                                        <td>Total</td>
                                        <td>Comissão</td>
                                        <td>Total - Comissão</td>
                                    </tr>";
                        while ($rsRow = pg_fetch_array($rs)) {

                                $total_qtde += $rsRow['quantidade'];
                                $total_geral += $rsRow['total'];
                                $total_comissao += $rsRow['comissao'];
                                $total_liquido += ($rsRow['total']-$rsRow['comissao']);

                                $msg .= " 
                                    <tr class='trListagem bg-branco font12'>
                                        <td class='text-center'>".Util::getData($rsRow['data'], true)."</td>
                                        <td class='text-center'>".$rsRow['empresa']."</td>
                                        <td class='text-center'>".$rsRow['nacionalidade']."</td>
                                        <td class='text-center'>".$arrayPublihers[$rsRow['fp_publisher']]."</td>
                                        <td class='text-right'>".number_format($rsRow['quantidade'], 0, ",", ".")."</td>
                                        <td class='text-right'>".number_format($rsRow['total'], 2, ",", ".")."</td>
                                        <td class='text-right'>".number_format($rsRow['comissao'], 2, ",", ".")."</td>
                                        <td class='text-right'>".number_format(($rsRow['total']-$rsRow['comissao']), 2, ",", ".")."</td>
                                    </tr>";

                        }//end while
                        $msg .= "
                                    <tr class='negrito'>
                                        <td class='text-right' colspan='4'>Valor Total R$:</td>
                                        <td class='text-right'>".number_format($total_qtde, 0, ",", ".")."</td>
                                        <td class='text-right'>".number_format($total_geral, 2, ",", ".")."</td>
                                        <td class='text-right'>".number_format($total_comissao, 2, ",", ".")."</td>
                                        <td class='text-right'>".number_format($total_liquido, 2, ",", ".")."</td>
                                    </tr>
                                </table>";
                }//end if(pg_num_rows($rs)>0)
                else {
                        $msg .= "Nenhum registro selecionado no período.";
                }//end else do if(pg_num_rows($rs)>0)
        }//end if($rs) 
        else {
                $msg .= "ERRO: Problema na seleção dos Totais por Empresa.<br>";
        }//end else do if($rs) 
} // end if($BtnSearch)
/*
    FIM CONTROLLER
 */
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-4 txt-preto">
    <form id="buscaBanner" name="buscaBanner" method="post">
        <div class="col-md-5 top10">
            Data inicial:
        </div>
        <div class="col-md-7 top10">
            <input type="text" value="<?php if(isset($_POST["data_inicial"]))  echo $_POST["data_inicial"]; else echo date('d/m/Y'); ?>" id="data_inicial" name="data_inicial" char="10" class="form-control data w150">
        </div>
        <div class="col-md-5 top10">
            Data final: 
        </div>
        <div class="col-md-7 top10">
            <input type="text" value="<?php if(isset($_POST["data_final"])) echo $_POST["data_final"]; else echo date('d/m/Y'); ?>" id="data_final" name="data_final" char="10" class="form-control data w150">
        </div>
        <div class="col-md-5 top10">
            Publisher: 
        </div>
        <div class="col-md-7 top10">
            <select class="form-control data w150" name="opr_codigo" char="1" id="opr_codigo" label="Publisher">
                <option value="ALL">Todos</option>
                <?php
                 foreach ($arrayPublihers as $key => $value) {
                ?>
                <option value="<?php echo $key;?>" <?php if(isset($_POST["opr_codigo"]) && $_POST["opr_codigo"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                <?php     
                 }
                ?>
            </select>
        </div>
        <div class="col-md-5 top10">
            Empresa: 
        </div>
        <div class="col-md-7 top10">
            <select class="form-control data w150" name="empresa" char="1" id="empresa" label="Empresa">
                <option value="ALL">Todas</option>
                <option value="1" <?php if(isset($_POST["empresa"]) && $_POST["empresa"] == "1") echo "selected"; ?>>EPP Administradora</option>
                <option value="0" <?php if(isset($_POST["empresa"]) && $_POST["empresa"] == "0") echo "selected"; ?>>EPP Pagamentos</option>
            </select>
        </div>
        <div class="col-md-5 top10">
            Nacionalidade: 
        </div>
        <div class="col-md-7 top10">
            <select class="form-control data w150" name="nacionalidade" char="1" id="nacionalidade" label="Empresa">
                <option value="ALL">Todas</option>
                <option value="1" <?php if(isset($_POST["nacionalidade"]) && $_POST["nacionalidade"] == "1") echo "selected"; ?>>INTERNACIONAL</option>
                <option value="0" <?php if(isset($_POST["nacionalidade"]) && $_POST["nacionalidade"] == "0") echo "selected"; ?>>NACIONAL</option>
            </select>
        </div>
        <div class="col-md-7 col-md-offset-5 top10">
            <button type="submit" name="BtnSearch" value="Consultar" class="btn btn-success">Consultar</button>
        </div>
    </form>
</div>
<?php
if(isset($msg)) echo '<div class="col-md-8 txt-preto">'.$msg.'</div>';
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script>
    jQuery(function(e){

        var optDate = new Object();
            optDate.interval = 12;
            optDate.minDate = "01/01/2010";

        setDateInterval('data_inicial','data_final',optDate);
        
        $("#buscar").click(function(){
            var erro = [];
            
            $(".form-control").each(function(){
                 if($(this).val().length < $(this).attr("char"))
                     erro.push($(this).attr("label"));
            });
            
            if(erro.length > 4)
            {
                var msgErro = "Nenhum campo foi preenchido";
                alert(msgErro);
            }
            else
               $("#"+$(this).get(0).form.id).submit();

       });
   });
</script>

<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>