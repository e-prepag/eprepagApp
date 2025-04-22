<?php
set_time_limit(1200);
ini_set('max_execution_time', 1200); 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "class/util/Validate.class.php";
require_once $raiz_do_projeto . "class/util/Util.class.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "includes/complice/functions.php";

if(empty($dataClickIni) || !Util::checkValidDate($dataClickIni)) 
    $dataClickIni = date('d/m/Y');
if(empty($dataClickFim) || !Util::checkValidDate($dataClickFim)) 
    $dataClickFim = date('d/m/Y');

//Publishers Exigem CPFs como Obrigatórios
$vetorPublisherAux = levantamentoPublisherObrigatorioCPF($vetorPublisherLegenda);

/* 
    CONTROLLER
 */
if(isset($_POST["busca"])){
    //echo Util::getData($dataClickIni, true)."<br>".Util::getData($dataClickFim, true)."<br>";
    
    //Publishers Já em Operação constantes em arquivos BACEN anteriores INTERNacionais
    $ano = substr($dataClickIni,6,4);
    $mes = substr($dataClickIni,3,2);
    //echo "ANO: ".$ano." Mes: ".$mes."<br>";
    
    //Publishers Exigem CPFs como Obrigatórios
    if(isset($publishers) && is_array($publishers)) {
        if(in_array("ALL", $publishers)) {
            $vetorPublisher = $vetorPublisherAux;
        }
        else {
            $vetorPublisher = $publishers;
        }
    }
    else {
        $vetorPublisher = $vetorPublisherAux;
    }

    //Buscando Publisher que possuem totalização por utilização
//    require_once $raiz_do_projeto . "/incs/functions.php";
//    $vetorPublisherPorUtilizacao = levantamentoPublisherComFechamentoUtilizacaoInternacional();
    
    
    //Buscando nomes envolvido em transações de vendas
    // Buscando informações 
    $sql = "select ug_nome,tipo from ( 
                (select 
                        'Gamer' as tipo,
                        UPPER(ug_nome_cpf) as ug_nome
                from tb_venda_games vg 
                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_concilia >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vg.vg_data_concilia <= '".Util::getData($dataClickFim, true)." 23:59:59'
                        and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                        and vgm_opr_codigo IN (".implode(",", $vetorPublisher).")
                group by tipo, ug_nome_cpf)

            union all

                (select 
                        'CPF na Venda do PDV' as tipo,
                        UPPER(vgm_nome_cpf) as ug_nome
                from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vg.vg_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
                        and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                group by tipo, vgm_nome_cpf) 

            union all

                (select 
                        'PDV - Razão Social' as tipo,
                        UPPER(ug_razao_social) as ug_nome
                from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vg.vg_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
                        and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                group by tipo, ug_razao_social) 

            union all

                (select 
                        'PDV - Nome Fantasia' as tipo,
                        UPPER(ug_nome_fantasia) as ug_nome
                from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vg.vg_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
                        and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                group by tipo, ug_nome_fantasia) 

            union all

                (select 
                        'PDV - Representante Legal' as tipo,
                        UPPER(ug_repr_legal_nome) as ug_nome
                from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vg.vg_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
                        and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                group by tipo, ug_repr_legal_nome) 

            union all

                (select 
                        'PDV - Sócio' as tipo,
                        UPPER(ugs_nome) as ug_nome
                from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        inner join dist_usuarios_games_socios ugc on ugc.ug_id = vg.vg_ug_id
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vg.vg_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
                        and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                group by tipo, ugs_nome) 

            union all

                (select 
                        'CPF no Gift Card' as tipo,
                        UPPER(picc_nome) as ug_nome
                from pins_integracao_card_historico
                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                where pin_status = '4' 
                        and pih_codretepp = '2'
                        and pih_data >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and pih_data <= '".Util::getData($dataClickFim, true)." 23:59:59'
                        and pih_id IN (".implode(",", $vetorPublisher).") 
                group by tipo, picc_nome)

            union all

                (select 
                        'CPF no Boleto Express' as tipo,
                        UPPER(vgcbe_nome_cpf) as ug_nome
                from tb_venda_games_cpf_boleto_express
                    inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                    inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vgcbe_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vgcbe_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
                        and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                group by tipo, vgcbe_nome_cpf)
    ) tabelaUnion 
                group by ug_nome, tipo 
                order by ug_nome;
        ";

    //echo "SQL :<pre>".$sql."</pre><br>";
    $rs = SQLexecuteQuery($sql);
    
} //end if(isset($_POST["busca"]))
/*
    FIM CONTROLLER
 */
?>
<script>
    function fcnOnSubmit(){
        return confirm('A pesquisa demorará a ser processada.\nPor favor, NÃO FECHE a tela acreditando que o programa travou!');
    }
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <form id="buscaCruzamento" name="buscaCruzamento" class="form-inline" method="post" onsubmit="return fcnOnSubmit();">
        <h4>Filtrar Data da Operação:</h4>
        <div class="text-left col-md-12 top20">
            <div class="form-group col-md-6">
                <label for="dataClickIni">Data inicial:</label>
                <input type="text" id="dataClickIni" label="data inicial " char="10" name="dataClickIni" <?php if(isset($dataClickIni)) echo "value='".$dataClickIni."'"; ?> class="form-control w150">
            </div>
            <div class="form-group col-md-6">
                <label for="dataClickFim">Data final:</label>
                <input type="text" id="dataClickFim"  label="data final " char="10" name="dataClickFim" <?php if(isset($dataClickFim)) echo "value='".$dataClickFim."'"; ?> class="form-control w150">
            </div>
        </div>
        <div class="text-left col-md-12 top20">
            <div class="form-group col-md-6">
                <label for="dataClickFim">Publishers que Exigem CPF como Obrigatório:</label>
                <select style="width: 320px;" class="form-control" multiple size="5" name="publishers[]" id="publishers[]">
                    <option value="ALL"<?php if(isset($publishers) && in_array("ALL", $publishers) || isset($publishers) && count($publishers) == 0) echo " selected"; ?>>TODOS</option>
                    <?php
                    foreach ($vetorPublisherLegenda as $key => $value) {
                    ?>
                    <option value="<?php echo $key; ?>" <?php if(isset($publishers) && in_array($key, $publishers)) echo " selected"; ?>><?php echo $value;?></option>
                    <?php
                    }
                    ?>                    
                </select>
            </div>
            <div class="form-group col-md-6 top74">
                <span class="p5">
                    <input type="hidden" name="busca" value="1">
                    <input type="submit" value="BUSCAR" id="buscar" name="buscar" class="btn btn-md btn-info pull-right">
                </span>
            </div>
        </div>
    </form>
</div>
<div class="col-md-12 txt-preto ">
    <table class="table table-bordered top20" >
<?php 
            if(isset($rs) && $rs) {
                $total_de_registros = pg_num_rows($rs);
                if($total_de_registros > 0) {
?>
        <thead class="">
            <tr>
                <th colspan="4">Total Nomes Levantados: <?php echo $total_de_registros; ?></th>
            </tr>
            <tr>
                <th>Tipo EPP</th>
                <th>Nome - Nosso Banco de Dados</th>
                <th>Nome Encontrado - Lista OFAC</th>
                <th>Tipo OFAC</th>
            </tr>
        </thead>
<?php        
                    while ($rsRow = pg_fetch_array($rs)) {
                        
                       // Eliminando espaços em branco inicio e fim da String
                       $rsRow['ug_nome'] = trim($rsRow['ug_nome']);
                       // Verificando conteudo válido oara pesquisa
                       if(!empty($rsRow['ug_nome'])) {
                        
                            //Buscando insidencia na lista da OFAC
                            $sql = "SELECT * FROM ofac WHERE nome LIKE '%".  str_replace("'", "\'",strtoupper($rsRow['ug_nome']))."%';";
                            //echo $sql."<br>";
                            $rsEncontrado = SQLexecuteQuery($sql);
                            // Verificando se foi encontrado algum nome
                            if(isset($rsEncontrado) && pg_num_rows($rsEncontrado) > 0) {
                                // Exibindo todas incidencias de nomes
                                while ($rsEncontradoRow = pg_fetch_array($rsEncontrado)) {
?>
        <tbody title="Nome Encontrado">
            <tr class="trListagem">
                <td><?php echo $rsRow['tipo']; ?></td>
                <td><?php echo $rsRow['ug_nome']; ?></td>
                <td><?php echo $rsEncontradoRow['nome']; ?></td>
                <td><?php echo $rsEncontradoRow['tipo_dado']; ?></td>
            </tr>
<?php
                                }//end while ($rsEncontradoRow = pg_fetch_array($rsEncontrado))
                            }//end if(pg_num_rows($rsEncontrado) > 0) 
                        }//end if(!empty(trim($rsRow['ug_nome']))) 
                    }//end while 
                }//end if(pg_num_rows($rs) > 0)
                else {
?>
            <tr>
                <td colspan="3">Nenhum registro encontrado.</td>
            </tr>
<?php
                }//end else do if(pg_num_rows($rs) > 0) 
            }elseif(isset($rs)){
?>
            <tr>
                <td colspan="3">Erro Ao buscar informações no banco de dados.</td>
            </tr>
<?php
            }
?>
        </tbody>
    </table>
</div>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
    $(function(){
        var optDate = new Object();
        optDate.interval = 3;
        optDate.minDate = "01/01/2016";
        setDateInterval('dataClickIni','dataClickFim',optDate);
    });
</script>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>