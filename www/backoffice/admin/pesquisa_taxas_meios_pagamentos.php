<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";

set_time_limit(3600);
/* 
    CONTROLLER
 */
if(isset($BtnSearch) && $BtnSearch) {
        //Variavel de OUTPUT
        $msg = "";
        
        //Montando SQL
        $sql = "
                SELECT sum(total) as total_dia,
                       to_char(dia,'DD/MM/YYYY') as dia_formatado
                FROM (
                ";
        $union_all = 0;

        //Parte para considerar boletos
        if($_POST["meio_pagto"] == "ALL" || $_POST["meio_pagto"] == "2") {
            
            if($_POST["tipo_user"] == "ALL" || $_POST["tipo_user"] == "M") {
                $sql .= "(
                    -- GAMER
                    SELECT sum(bbg_valor_taxa) as total, 
                           date_trunc('day', bol_data ) as dia 
                    FROM boletos_pendentes
                        INNER JOIN boleto_bancario_games ON bol_venda_games_id = bbg_vg_id
                    WHERE bol_data  >= '".formata_data($_POST["data_inicial"],1)." 00:00:00'
                            AND bol_data  <= '".formata_data($_POST["data_final"],1)." 23:59:59'
                            AND substring(bol_documento from 1 for 1) IN ('2','3','6')
                    GROUP BY dia
                    )
                    ";
                $union_all++;
            } //end if($_POST["tipo_user"] == "ALL" || $_POST["tipo_user"] == "M")
            if($_POST["tipo_user"] == "ALL" || $_POST["tipo_user"] == "LR") {
            if($union_all > 0) {
                $sql .= "
                    UNION ALL

                    ";
            }//end if($union_all > 0)
                $sql .= " 
                    (
                    -- PDV
                    SELECT sum(bbg_valor_taxa) as total, 
                        date_trunc('day', bol_data ) as dia 
                    FROM boletos_pendentes
                        INNER JOIN dist_boleto_bancario_games ON bol_venda_games_id = bbg_vg_id
                    WHERE bol_data  >= '".formata_data($_POST["data_inicial"],1)." 00:00:00'
                            AND bol_data  <= '".formata_data($_POST["data_final"],1)." 23:59:59'
                            AND substring(bol_documento from 1 for 1) IN ('1','4')
                    GROUP BY dia
                    )";
                $union_all++;
            } //end if($_POST["tipo_user"] == "ALL" || $_POST["tipo_user"] == "LR") 
        } //end if($_POST["meio_pagto"] == "ALL" || $_POST["meio_pagto"] == "2")

        //Parte para considerar pagamentos online
        if($_POST["meio_pagto"] == "ALL" || $_POST["meio_pagto"] != "2") {
            if($union_all > 0) {
                $sql .= "
                    UNION ALL

                    ";
            }//end if($union_all > 0)
            $sql .= "(
                    SELECT sum(taxas) as total, 
                        date_trunc('day', datacompra) as dia 
                    FROM tb_pag_compras
                    WHERE datacompra >= '".formata_data($_POST["data_inicial"],1)." 00:00:00'
                            AND datacompra <= '".formata_data($_POST["data_final"],1)." 23:59:59'
                            AND status = 3
                            ";
            if($_POST["meio_pagto"] != "ALL") {
                    $sql .= "AND iforma = '".$_POST["meio_pagto"]."'";
            }//end if($_POST["meio_pagto"] != "ALL")
            if($_POST["tipo_user"] != "ALL") {
                    $sql .= "AND tipo_cliente = '".$_POST["tipo_user"]."'";
            }//end if($_POST["meio_pagto"] != "ALL")
            $sql .= " 
                    GROUP BY dia
                    )
                    ";
            $union_all++;
        } //end if($_POST["meio_pagto"] == "ALL" || $_POST["meio_pagto"] == "2")
        $sql .= " 
                ) tabela
                GROUP BY dia
                ORDER BY dia;
                ";
        //echo $sql."<br>";
        $rs = SQLexecuteQuery($sql);
        if($rs) {
                if(pg_num_rows($rs)>0) {
                        $total_geral = 0;
                        $msg .= "<table class='table table-bordered txt-preto'>
                                    <tr class='text-center negrito'>
                                        <td>Data</td>
                                        <td>Total Taxas no Dia</td>
                                    </tr>";
                        while ($rsRow = pg_fetch_array($rs)) {

                                $total_geral+= $rsRow['total_dia'];

                                $msg .= " 
                                    <tr class='trListagem bg-branco font12'>
                                        <td class='text-center'>".$rsRow['dia_formatado']."</td>
                                        <td class='text-right'>".number_format($rsRow['total_dia'], 2, ",", ".")."</td>
                                    </tr>";

                        }//end while
                        $msg .= "
                                    <tr class='negrito'>
                                        <td class='text-right'>Valor Total R$:</td>
                                        <td class='text-right'>".number_format($total_geral, 2, ",", ".")."</td>
                                    </tr>
                                </table>";
                }//end if(pg_num_rows($rs)>0)
                else {
                        $msg .= "Nenhum registro selecionado no período.";
                }//end else do if(pg_num_rows($rs)>0)
        }//end if($rs) 
        else {
                $msg .= "ERRO: Problema na seleção das Taxas do Meios de Pagamentos.<br>";
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
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<div class="col-md-7 txt-preto">
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
            Meio de Pagamento: 
        </div>
        <div class="col-md-7 top10">
            <select class="form-control data w150" name="meio_pagto" char="1" id="meio_pagto" label="Meio de Pagamento">
                <option value="ALL">Todos</option>
                <?php
                 foreach ($FORMAS_PAGAMENTO_DESCRICAO as $key => $value) {
                ?>
                <option value="<?php echo $key;?>" <?php if(isset($_POST["meio_pagto"]) && $_POST["meio_pagto"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                <?php     
                 }
                ?>
            </select>
        </div>
        <div class="col-md-5 top10">
            Tipo de Usuário: 
        </div>
        <div class="col-md-7 top10">
            <select class="form-control data w150" name="tipo_user" char="1" id="tipo_user" label="Meio de Pagamento">
                <option value="ALL">Todos</option>
                <option value="M" <?php if(isset($_POST["tipo_user"]) && $_POST["tipo_user"] == "M") echo "selected"; ?>>GAMER</option>
                <option value="LR" <?php if(isset($_POST["tipo_user"]) && $_POST["tipo_user"] == "LR") echo "selected"; ?>>PDV</option>
            </select>
        </div>
        <div class="col-md-offset-5 top10 col-md-7">
            <button type="submit" name="BtnSearch" value="Consultar" class="btn btn-success">Consultar</button>
        </div>
    </form>
</div>
<?php
if(isset($msg)) echo '<div class="col-md-5 espacamento">'.$msg.'</div>';
require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $server_url_ep; ?>/js/global.js"></script>
<script>
    jQuery(function(e){

        var optDate = new Object();
            optDate.interval = 6;
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