<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(1200);
ini_set('max_execution_time', 1200); 

require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once $raiz_do_projeto."includes/complice/functions.php";

if(empty($dataClickIni) || !Util::checkValidDate($dataClickIni)) 
    $dataClickIni = date('d/m/Y');
if(empty($dataClickFim) || !Util::checkValidDate($dataClickFim)) 
    $dataClickFim = date('d/m/Y');

//Publishers Exigem CPFs como Obrigatórios
$vetorPublisherAux = levantamentoPublisherObrigatorioCPF($vetorPublisherLegenda);

//Dados para caso seja marcado o checkbox dos últimos 12 meses
$dia_aux = date("d");
$ano_anterior_aux = date("Y-m-d",strtotime(date("Y-m-d")."-12 month"));
$ano_anterior = date("d/m/Y",strtotime($ano_anterior_aux."-".($dia_aux-1)."day"));

$data_mes_anterior = date("Y-m-d",strtotime(date("Y-m-d")."-1 month"));
$mes_anterior_aux = date("m",strtotime($data_mes_anterior));
$mes_anterior = date("d/m/Y",strtotime( date("Y",strtotime(date($data_mes_anterior)))."-".$mes_anterior_aux."-".date("t", strtotime(date($data_mes_anterior)))));

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
    if(is_array($publishers)) {
        if(in_array("ALL", $publishers)) {
            $vetorPublisher = $vetorPublisherAux;
        }
        else {
            $vetorPublisher = $publishers;
        }
    }
    else {
        $publishers = array();
        $vetorPublisher = $vetorPublisherAux;
    }
    
    //Removendo espaço em branco e formatação
    $cpf = trim($cpf);
	$cpfComFormatacao = $cpf;
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
//    $cpf = "";
    if(!empty($cpf)) {
        //Buscando CPF e nomes envolvido em transações de vendas
        $sql = "select cod, ug_id, vg_id, ug_nome,tipo,ug_cpf, sum(valor_total) as valor_total, vg_pagto_tipo, vg_data_inclusao from ( 
                    (select 
                            'Gamer' as tipo,
                            vgm_opr_codigo as cod,
                            ug_id,
                            vg.vg_id as vg_id,
                            ug_cpf, 
                            UPPER(ug_nome_cpf) as ug_nome,
                            sum(vgm.vgm_valor * vgm.vgm_qtde) as valor_total,
                            vg.vg_pagto_tipo,
                            vg.vg_data_inclusao
                    from tb_venda_games vg 
                            inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                            inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                    where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                            and vg.vg_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                            and vg.vg_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
                            and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                            ".(!empty($no_cpf)?"-- ":"")."and vgm_opr_codigo IN (".implode(",", $vetorPublisher).")
                            and replace(replace(ug_cpf, '.', ''), '-', '') = '".$cpf."' 
                    group by ug_id, ug_cpf, vg_id, tipo, ug_nome_cpf, vgm_opr_codigo, vg.vg_pagto_tipo, vg.vg_data_inclusao)

                union all

                    (select 
                            'CPF na Venda do PDV' as tipo,
                            vgm_opr_codigo as cod,
                            vg.vg_ug_id as ug_id,
                            vg.vg_id as vg_id,
                            vgm_cpf as ug_cpf, 
                            UPPER(vgm_nome_cpf) as ug_nome,
                            sum(vgm.vgm_valor * vgm.vgm_qtde) as valor_total,
                            vg_pagto_tipo,
                            vg_data_inclusao
                    from tb_dist_venda_games vg 
                            inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                            and vg.vg_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                            and vg.vg_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
                            ".(!empty($no_cpf)?"-- ":"")."and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                            and vgm_cpf in('".$cpf."', '".$cpfComFormatacao."') 
                    group by ug_id, vg_id, tipo, vgm_nome_cpf, vgm_cpf, vgm_opr_codigo, vg_pagto_tipo, vg_data_inclusao) 

                union all

                    (select 
                            'CPF no Gift Card' as tipo,
                            pih_id as cod,
                            NULL::bigint as ug_id,
                            NULL::bigint as vg_id,
                            picc_cpf as ug_cpf, 
                            UPPER(picc_nome) as ug_nome,
                            sum(pih_pin_valor/100) as valor_total,
                            0 as vg_pagto_tipo,
                            pih_data as vg_data_inclusao
                    from pins_integracao_card_historico
                        left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                    where pin_status = '4' 
                            and pih_codretepp = '2'
                            and pih_data >= '".Util::getData($dataClickIni, true)." 00:00:00'
                            and pih_data <= '".Util::getData($dataClickFim, true)." 23:59:59'
                            ".(!empty($no_cpf)?"-- ":"")."and pih_id IN (".implode(",", $vetorPublisher).") 
                            and replace(replace(picc_cpf, '.', ''), '-', '') = '".$cpf."' 
                    group by ug_id, vg_id, tipo, picc_nome, picc_cpf, pih_id, vg_pagto_tipo, vg_data_inclusao)

                union all

                    (select 
                            'CPF no Boleto Express' as tipo,
                            vgm_opr_codigo as cod,
                            NULL::bigint as ug_id,
                            vg.vg_id as vg_id,
                            vgcbe_cpf as ug_cpf, 
                            UPPER(vgcbe_nome_cpf) as ug_nome,
                            sum(vgm_valor * vgm_qtde) as valor_total,
                            vg_pagto_tipo,
                            vg_data_inclusao
                    from tb_venda_games_cpf_boleto_express
                        inner join tb_venda_games vg ON (vg_id = vgcbe_vg_id)
                        inner join tb_venda_games_modelo vgm ON (vgm_vg_id = vg_id)
                    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                            and vgcbe_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                            and vgcbe_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
                            ".(!empty($no_cpf)?"-- ":"")."and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                            and replace(replace(vgcbe_cpf, '.', ''), '-', '') = '".$cpf."' 
                    group by ug_id, vg_id, tipo, vgcbe_nome_cpf, vgcbe_cpf,vgm_opr_codigo, vg_pagto_tipo, vg_data_inclusao)
        ) tabelaUnion 
                    group by ug_id, vg_id, ug_nome, tipo, ug_cpf, cod , vg_pagto_tipo, vg_data_inclusao 
                    order by vg_data_inclusao, vg_id;
            ";

        //echo "SQL :<pre>".$sql."</pre><br>";
        $rs = SQLexecuteQuery($sql);
    }//end if(!empty($cpf)) 
    else {
        echo "<div>CPF não informado na pesquisa!</div>";
    }
    
} //end if(isset($_POST["busca"]))

//inicializando variável
 if(!isset($publishers)) $publishers = array();
 
/*
    FIM CONTROLLER
 */
?>
<script>
    function fcnOnSubmit(){
        if($("#cpf").val() =='') {
            alert('É obrigatório informar o CPF!');
            return false;
        }
        else {
            return true;
        }
    }
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <form id="buscaCruzamento" name="buscaCruzamento" class="form-inline" method="post" onsubmit="return fcnOnSubmit();">
        <h4>Filtrar Data da Operação:</h4>
        <div class="text-left col-md-12 top20">
            <div class="form-group col-md-6">
                <label for="periodo_fixo">Considerar últimos 12 meses</label>
                <input type="checkbox" name="periodo_fixo" id="periodo_fixo" value="1" <?php echo (isset($periodo_fixo) && $periodo_fixo)?'checked="checked"':"";?>>
            </div><div class="form-group col-md-6">
                <label for="media">Mostrar Média</label>
                <input type="checkbox" name="media" id="media" value="1" <?php echo (isset($media) && $media)?'checked="checked"':"";?>>
            </div>
        </div>
        <div class="text-left col-md-12 top20">
            <div class="form-group col-md-6">
                <label for="dataClickIni">Data inicial:</label>
                <input type="text" id="dataClickIni" label="data inicial " char="10" onclick="javascript:verifychecked();" name="dataClickIni" <?php if(isset($dataClickIni)) echo "value='".$dataClickIni."'"; ?> class="form-control w150">
            </div>
            <div class="form-group col-md-6">
                <label for="dataClickFim">Data final:</label>
                <input type="text" id="dataClickFim"  label="data final " char="10" onclick="javascript:verifychecked();" name="dataClickFim" <?php if(isset($dataClickFim)) echo "value='".$dataClickFim."'"; ?> class="form-control w150">
            </div>
        </div>
        <div class="text-left col-md-12 top20">
            <div class="form-group col-md-6">
                <label for="dataClickFim">Publishers que Exigem CPF como Obrigatório:</label>
                <select style="width: 320px;" class="form-control" multiple size="5" name="publishers[]" id="publishers[]">
                    <option value="ALL"<?php if(in_array("ALL", $publishers) || count($publishers) == 0) echo " selected"; ?>>TODOS</option>
                    <?php
                    foreach ($vetorPublisherLegenda as $key => $value) {
                    ?>
                    <option value="<?php echo $key; ?>" <?php if(in_array($key, $publishers)) echo " selected"; ?>><?php echo $value;?></option>
                    <?php
                    }
                    ?>                    
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" label="CPF" char="14" maxlength="14" name="cpf" <?php if(isset($cpf)) echo "value='".$cpf."'"; ?> class="form-control w150"><br><br>
                <nobr><b>Desconsiderar Filtro de Publishers</b> 
                <input name="no_cpf" type="checkbox" id="no_cpf" value="1" <?php if(isset($no_cpf) && $no_cpf == "1") echo "checked" ?>/></nobr>
            </div>
            <div class="form-group col-md-2 top74">
                <span class="p5">
                    <input type="hidden" name="busca" value="1">
                    <input type="submit" value="BUSCAR" id="buscar" name="buscar" class="btn btn-md btn-info pull-right">
                </span>
            </div>
        </div>
    </form>
</div>
<div class="col-md-12 txt-preto ">
    <table class="table table-bordered top20" id="table_dados">
<?php 
            if(isset($rs) && $rs) {
                $total_de_registros = pg_num_rows($rs);
                if($total_de_registros > 0) {
?>
        <thead class="">
            <tr>
                <th colspan="5">Total Nomes Levantados: <?php echo $total_de_registros; ?></th>
            </tr>
            <tr>
                <th>Data</th>
                <th>Tipo de Venda</th>
                <th>Publisher</th>
                <th>ID PDV/Gamer</th>
                <th>ID Venda</th>
                <th>CPF</th>
                <th>Nome</th>
                <th>Cód Forma Pagto</th>
                <th>Valor</th>
            </tr>
        </thead>
<?php        
                    $total = 0;
                    //Dados usados para calcular e exibir a média de gastos por mês
                    $diferenca_dias = strtotime(Util::getData($dataClickFim,true)) - strtotime(Util::getData($dataClickIni,true));
                    $dias = floor(($diferenca_dias)/(60*60*24));
                    $meses = floor($dias / 30);
                    
                    $periodo = (isset($periodo_fixo) && $periodo_fixo)?"últimos ":"considerando ";
                    $num_meses = (isset($periodo_fixo) && $periodo_fixo)?12:$meses;
                    $plural_singular = (($num_meses == "1")?" mês":" meses");
                    
                    while ($rsRow = pg_fetch_array($rs)) {
                        
                       // Eliminando espaços em branco inicio e fim da String
                       $rsRow['ug_nome'] = trim($rsRow['ug_nome']);

?>
        <tbody title="Pedido">
            <tr class="trListagem" <?php echo (!isset($vetorPublisherLegenda[$rsRow['cod']])?"bgcolor='#ff9090'":""); ?>>
                <td><?php echo formata_data(substr($rsRow['vg_data_inclusao'], 0, 10), 0); ?></td>
                <td><?php echo $rsRow['tipo']; ?></td>
                <td><?php echo !isset($vetorPublisherLegenda[$rsRow['cod']])?$rsRow['cod']:$vetorPublisherLegenda[$rsRow['cod']]; ?></td>
                <td><?php if(!is_null($rsRow['ug_id'])) { ?><a href="/<?php echo(strpos($rsRow['tipo'], "PDV")?"pdv":"gamer"); ?>/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rsRow['ug_id']; ?>" target="_blank"><?php echo $rsRow['ug_id']; ?></a><?php } else echo "Não se aplica"; ?></td>
                <td><?php if(!is_null($rsRow['vg_id'])) { ?><a href="/<?php echo(strpos($rsRow['tipo'], "PDV")?"pdv":"gamer"); ?>/vendas/com_venda_detalhe.php?venda_id=<?php echo $rsRow['vg_id']; ?>" target="_blank"><?php echo $rsRow['vg_id']; ?></a><?php } else echo "Não se aplica"; ?></td>
                <td><?php echo $rsRow['ug_cpf']; ?></td>
                <td><?php echo $rsRow['ug_nome']; ?></td>
                <td><?php echo $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO_NUMERICO'][$rsRow['vg_pagto_tipo']]; ?></td>
                <td align="right">R$ <?php echo number_format($rsRow['valor_total'], 2, ',', '.'); ?></td>
            </tr>
<?php
                        $total+=$rsRow['valor_total'];
                    }//end while 
?>
            <tr>
                <td colspan="8" align="right"><b>Total</b></td>
                <td align="right"><nobr><b>R$ <?php echo number_format($total, 2, ',', '.'); ?></b></nobr></td>
            </tr>
<?php
                    if(isset($media) && $media){
?>
            <tr>
                <td colspan="8" align="right"><b>Média de Valor Gasto por Mês (<?php echo $periodo . $num_meses . $plural_singular; ?>)</b></td>
                <td align="right"><nobr><b>R$ <?php echo number_format(($total/$num_meses), 2, ',', '.'); ?></b></nobr></td>
            </tr>
            <tr>
                <td colspan="8" align="right"><b>Média de Quantidade de Compras por Mês</b></td>
                <td align="right"><nobr><b><?php echo number_format(($total_de_registros/$num_meses), 0); ?></b></nobr></td>
            </tr>
<?php          
                    } //end if(isset($media) && $media)
                }//end if(pg_num_rows($rs) > 0)
                else {
?>
            <tr>
                <td colspan="7" class="no-table">Nenhum registro encontrado.</td>
            </tr>
<?php
                }//end else do if(pg_num_rows($rs) > 0) 
            }elseif(isset($rs)){
?>
            <tr>
                <td colspan="7" class="no-table">Erro Ao buscar informações no banco de dados.</td>
            </tr>
<?php
            }
?>
        </tbody>
    </table>
<!--    <div class="col-md-12 text-center">
        <button type="button" class="btn btn-info btn-sm hidden" id="download_csv">Download CSV</button>
    </div>-->
</div>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script src="/js/jquery.mask.min.js"></script>
<script src="/js/jquery.base64.min.js"></script>
<script>
    $("#cpf").mask("999.999.999-99");
    $(function(){
        var optDate = new Object();
        optDate.interval = 3;
        optDate.minDate = "01/01/2016";
        setDateInterval('dataClickIni','dataClickFim',optDate);
        
        if($("#periodo_fixo").is(":checked")){
            $("#dataClickIni,#dataClickFim").each(function(){
                $(this).attr('readonly', 'readonly');
            });
        }
        
        $('input[name="periodo_fixo"]').change(function () {
            if($('input[name="periodo_fixo"]:checked').val() === "1") {
                $('#dataClickIni').val('<?php echo $ano_anterior; ?>');
                $('#dataClickFim').val('<?php echo $mes_anterior; ?>');
                $("#dataClickIni,#dataClickFim").each(function(){
                    $(this).attr('readonly', 'readonly');
                });
                
            } else {
                $('input[name="periodo_fixo"]').val("0");
                $('#dataClickIni').removeAttr('readonly');
                $('#dataClickFim').removeAttr('readonly');
            }
        });
        
        var libera = true;
        if($("#cpf").val() != ''){
            $(this).find('tr').each(function(){
                tr = $(this);

                tr.find('td').each(function(){
                    if($(this).hasClass('no-table')){
                        libera = false;
                    }

                });
            });

            if(libera){
                $("#download_csv").removeClass('hidden');
            }
        }
        
        $("#download_csv").click(function(){
            window.location = "/includes/download/pdv/downloadCsv.php?filename=tabela_venda_cpf_"+$("#cpf").val()+".csv&content=" + $.base64.encode($('#table_dados').table2CSV2().replace(/\./g, "").replace(/,/g, "."));
        });
        
    });
    
    function verifychecked(){
        if($("#periodo_fixo").is(":checked")){
            $("#periodo_fixo").prop("checked", false);
            $("#dataClickIni,#dataClickFim").each(function(){
                $(this).removeAttr('readonly');
            });
        }
    }
    
    jQuery.fn.table2CSV2 = function() {
        rows = [];
        data = "";

        $(this).find('tr').each(function(){
            row = [];
            tr = $(this);
            
            tr.find('th').each(function(){
                text = $(this).text().trim();
                
                if(typeof(text)!== "string" || text.indexOf("Nomes Levantados") != -1)
                    text = "";
                
                text = text.replace(/\n/g, "");
                row.push(text);
                
            });

            tr.find('td').each(function(){
                text = $(this).text().trim();

                if(typeof(text)!== "string")
                    text = "";
                
                if(text.indexOf("Total") != -1 || text.indexOf("Média de") != -1){
                    var aux = ";;;;;;;"+text;
                    text = aux;
                }

                text = text.replace(/\n/g, "");
                row.push(text);
            });
            rows.push(row);
        });
        data += "Período Considerado:;"+$("#dataClickIni").val()+" a "+$("#dataClickFim").val();
        for(key in rows) {
            data += rows[key].join(";") + "\n";
        }

        return data;
    };
    
</script>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>