<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
set_time_limit(3600);
/* 
    CONTROLLER
 */
if(isset($BtnSearch) && $BtnSearch) {
        //Variavel de OUTPUT
        $msg = "";
        
        //Montando SQL para a Busca das Taxas Envolvidas na Geração do RPS
        $sql = "
                SELECT to_char(pta_data,'DD/MM/YYYY') as data,* 
                FROM tb_pag_taxa_anual pta
                    INNER JOIN usuarios_games ug ON ug.ug_id = pta.ug_id
                WHERE 
                    pta_data >= '".formata_data($_POST["data_inicial"],1)." 00:00:00' 
                    AND  pta_data <= '".formata_data($_POST["data_final"],1)." 23:59:59'
                ORDER BY data,pta_valor DESC, pta_quantidade_depositos DESC, pta_valor_total DESC; 
                ";
        //echo $sql."<br>";
        $rs = SQLexecuteQuery($sql);
        if($rs) {
                if(pg_num_rows($rs)>0) {
                        $total_geral = 0;
                        $total_registros = 0;
                        $msg .= "<table class='table txt-preto fontsize-pp'>
                                    <tr class='text-center negrito'>
                                        <td>Count</td>
                                        <td>ID</td>
                                        <td>E-MAIL</td>
                                        <td>Data</td>
                                        <td>Saldo Momento<br>Cobrança</td>
                                        <td>Qtde Dep</td>
                                        <td>Valor da Taxa</td>
                                    </tr>";
                        while ($rsRow = pg_fetch_array($rs)) {

                                $total_geral+= $rsRow['pta_valor'];
                                $total_registros++;

                                $msg .= " 
                                    <tr class='trListagem fontsize-pp'>
                                        <td>".$total_registros."</td>
                                        <td class='text-center'><a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=".$rsRow['ug_id']."' target='_blank'>".$rsRow['ug_id']."</a></td> 
                                        <td class='text-left'>".$rsRow['ug_email']."</td>
                                        <td class='text-center'>".$rsRow['data']."</td>
                                        <td class='text-right'>".number_format($rsRow['pta_valor_total'], 2, ",", ".")."</td>
                                        <td class='text-right'>".number_format($rsRow['pta_quantidade_depositos'], 0, ",", ".")."</td>
                                        <td class='text-right'>".number_format($rsRow['pta_valor'], 2, ",", ".")."</td>
                                    </tr>";

                        }//end while
                        $msg .= "
                                    <tr class='negrito'>
                                        <td class='text-center' colspan='2'>Total Registros:</td> 
                                        <td class='text-left'>".number_format($total_registros, 0, ",", ".")."</td>
                                        <td class='text-right' colspan='3'>Valor Total R$:</td>
                                        <td class='text-right'>".number_format($total_geral, 2, ",", ".")."</td>
                                    </tr>
                                </table>";
                }//end if(pg_num_rows($rs)>0)
                else {
                        $msg .= "Nenhum registro selecionado no período.";
                }//end else do if(pg_num_rows($rs)>0)
        }//end if($rs) 
        else {
                $msg .= "ERRO: Problema na seleção das Taxas Anuais.<br>";
        }//end else do if($rs) 
} // end if($BtnSearch)
/*
    FIM CONTROLLER
 */
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <form id="buscaBanner" name="buscaBanner" method="post">
        <div class="col-md-2 text-right">Data inicial:</div>
        <div class="col-md-2">
            <input type="text" value="<?php if(isset($_POST["data_inicial"]))  echo $_POST["data_inicial"]; else echo date('d/m/Y'); ?>" id="data_inicial" name="data_inicial" char="10" class="form-control data w150">
        </div>
        <div class="col-md-2 text-right">Data final:</div>
        <div class="col-md-2">
            <input type="text" value="<?php if(isset($_POST["data_final"])) echo $_POST["data_final"]; else echo date('d/m/Y'); ?>" id="data_final" name="data_final" char="10" class="form-control data w150">
        </div>
        <div class="col-md-2 pull-right">
            <button type="submit" name="BtnSearch" value="Consultar" class="btn pull-right btn-success">Consultar</button>
        </div>
    </form>
</div>
<div class="col-md-12 borda bloco bg-cinza-claro top20">
        <?php
        if(isset($msg)) echo "<p>$msg</p>";
        ?>
</div>
<script>
    jQuery(function(e){

        var optDate = new Object();
            optDate.interval = 1000;
            optDate.minDate = "19/01/2016";

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