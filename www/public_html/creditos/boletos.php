<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
$_PaginaOperador2Permitido = 54;

require_once "../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/BoletosController.class.php";

$controller = new BoletosController;

$banner = $controller->getBanner();

require_once "includes/header.php";

if(!$ncamp)            $ncamp           = 'trn_data';
if(!$inicial)          $inicial         = 0;
if(!$range)            $range           = 1;
if(!$ordem)            $ordem           = 0;
//	if($BtnSearch)         $inicial         = 0;
//	if($BtnSearch)         $range           = 1;
//	if($BtnSearch)         $total_table     = 0;
if($BtnSearch=="Buscar") {
    $inicial     = 0;
    $range       = 1;
    $total_table = 0;
}

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "/imagens/proxima.gif";
$img_anterior = "/imagens/anterior.gif";
$max          = $qtde_reg_tela;
$range_qtde   = $qtde_range_tela;
$usuario_id = $controller->usuarios->getId();

$varsel .= "";

//Validacao
//------------------------------------------------------------------------------------------------------------------
$msg = "";
$msgFatal = "";

//Busca cortes
if($msg == "" && $msgFatal == ""){
    
    $dataInclusao = explode(" ",$controller->usuarios->getDataInclusao());
    $intervaloMaximoMeses = 6;
    
    if(isset($_POST))
    { //limpando post
        $temp = Util::getIptHidden($_POST);
        unset($_POST);
        $_POST = $temp;
    }
    
    $sql = "select 
                * 
            from 
                cortes c ";
     if(isset($_GET['nao-emitido']) && $_GET['nao-emitido'] == 1) {
        $sql .= "
                inner join boleto_bancario_cortes bbc ON bbc.bbc_boleto_codigo = c.cor_bbc_boleto_codigo ";
     }
     $sql .= " 
            where 
                c.cor_ug_id = ".$usuario_id;
    
    if(isset($_POST['tf_v_data_inclusao_ini']) && isset($_POST['tf_v_data_inclusao_fim']))
    {
        if( verifica_data($_POST['tf_v_data_inclusao_ini']) == 0 && 
            verifica_data($_POST['tf_v_data_inclusao_fim']) == 0 &&
            !Util::dateDiff($_POST['tf_v_data_inclusao_ini'], $_POST['tf_v_data_inclusao_fim'], $intervaloMaximoMeses)
        )
        {
            print "<script>"
                . "alert('Por favor, selecione um período entre as datas de até {$intervaloMaximoMeses} mêses.');"
                . "</script>";
            $geraData = true;
        }
        
    }else{
        $geraData = true;
    }
    
    if(isset($geraData))
    {
        $_POST['tf_v_data_inclusao_fim'] = date("d/m/Y", mktime(0,0,0,date("m"),date("d"),date("Y")));
        $_POST['tf_v_data_inclusao_ini'] = date("d/m/Y", mktime(0,0,0,date("m")-$intervaloMaximoMeses,date("d"),date("Y")));
    }
    
    
    if(isset($_GET['nao-emitido']) && $_GET['nao-emitido'] == 1) {
        $sql .= " and (bbc_status = ".$GLOBALS['CORTE_BOLETO_STATUS']['ABERTO']." OR bbc_status = ".$GLOBALS['CORTE_BOLETO_STATUS']['ENVIADO'].") ";
    }
    else {
        $sql .= " and cor_periodo_ini >= '".formata_data($_POST['tf_v_data_inclusao_ini'],1)." 00:00:00' and cor_periodo_fim <= '".formata_data($_POST['tf_v_data_inclusao_fim'],1)." 23:59:59'";
    }
        
    
    $sql .=" order by 
                cor_periodo_fim desc, cor_periodo_ini desc";

    $res_count = SQLexecuteQuery($sql);
    $total_table = pg_num_rows($res_count);

    $sql .= " limit ".$max; 
    $sql .= " offset ".$inicial;
    $rs_cortes = SQLexecuteQuery($sql);
    $iptHidden['tf_v_data_inclusao_ini'] = $_POST['tf_v_data_inclusao_ini'];
    $iptHidden['tf_v_data_inclusao_fim'] = $_POST['tf_v_data_inclusao_fim'];
}	
if($msgFatal != "") $msg = $msgFatal;

if($max + $inicial > $total_table) $reg_ate = $total_table;
else $reg_ate = $max + $inicial;

$arr_rs_cortes = null;

if($rs_cortes){
    $arr_rs_cortes = pg_fetch_all($rs_cortes);
}

?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>MEUS BOLETOS</strong>
                </div>
            </div>
            <div class="row txt-cinza">
                <div class="col-md-12 espacamento">
                    <p class="fontsize-p margin004">- Será acrescentado um valor de R$ 1,80 para boletos abaixo de R$ 60,00.</p>
                    <p class="fontsize-p margin004">- Neste caso, para evitar taxa, entre em contato com nosso <a href='http<?php if($_SERVER['HTTPS']=="on") { echo "s"; } ?>://<?php echo $_SERVER["SERVER_NAME"]; ?>/game/suporte.php' class="txt-azul">suporte</a> para efetuar o pagamento via depósito.</p>
                </div>
            </div>
<?php
            if(!isset($_GET['nao-emitido'])) 
            {
?>
            <div class="row txt-cinza">
                <form method="post">
                    <div class="col-md-2 col-xs-12 col-lg-2 col-sm-12">
                        <p>Data de início</p>
                        <p><input type="text" class="form-control data" name="tf_v_data_inclusao_ini" id="tf_v_data_inclusao_ini" value="<?php if(isset($_POST['tf_v_data_inclusao_ini'])) echo $_POST['tf_v_data_inclusao_ini']; ?>"></p>
                    </div>
                    <div class="col-md-2 col-xs-12 col-lg-2 col-sm-12">
                        <p>Data final</p>
                        <p><input type="text" class="form-control data" name="tf_v_data_inclusao_fim" id="tf_v_data_inclusao_fim" value="<?php if(isset($_POST['tf_v_data_inclusao_fim'])) echo $_POST['tf_v_data_inclusao_fim']; ?>"></p>
                    </div>
                    <div class="col-md-2 col-xs-6 col-lg-2 col-sm-6">
                        <p>&nbsp;</p>
                        <p><button type="submit" class="btn btn-success">Pesquisar</button></p>
                    </div>
                </form>
            </div>
<?php
            }

            $Tot_QtdeVendas = 0.0;
            $Tot_VendaBruta = 0.0;
            $Tot_Comissão = 0.0;
            $Tot_VendaLíquida = 0.0;

            if($arr_rs_cortes)
            {
                foreach($arr_rs_cortes as $ind => $rs_cortes_row)
                {
                    
                    $cor_status_descricao = $GLOBALS['CORTE_STATUS_DESCRICAO'][$rs_cortes_row['cor_status']];
                    $cor_tipo_pagto = $rs_cortes_row['cor_tipo_pagto'];

                    $Tot_QtdeVendas += $rs_cortes_row['cor_venda_qtde'];
                    $Tot_VendaBruta += $rs_cortes_row['cor_venda_bruta'];
                    $Tot_Comissão += $rs_cortes_row['cor_venda_comissao'];
                    $Tot_VendaLíquida += $rs_cortes_row['cor_venda_liquida'];
?>
                <div class="hidden-lg hidden-md txt-preto espacamento">
                    <div class="row p-3 borda-fina">
                        <div class="col-sm-7 col-xs-7 borda-colunas-formas-pagamento">
                            <p class="bottom0">Período</p>
                            <p><strong><?php echo  formata_data($rs_cortes_row['cor_periodo_ini'], 0) ?> a <?php echo  formata_data($rs_cortes_row['cor_periodo_fim'], 0); ?></strong></p>
                            <p class="bottom0">Valor total</p>
                            <p><?php echo  number_format ($rs_cortes_row['cor_venda_bruta'], 2, ',', '.') ?></p>
                            <p class="bottom0">Venda líquida</p>
                            <p><?php echo  number_format ($rs_cortes_row['cor_venda_liquida'], 2, ',', '.') ?></p>
                        </div>
                        <div class="col-sm-5 col-xs-5">
                            <p class="bottom0">Qtde vendas</p>
                            <p><?php echo  $rs_cortes_row['cor_venda_qtde'] ?></p>
                            <p class="bottom0">Comissão</p>
                            <p><?php echo  number_format ($rs_cortes_row['cor_venda_comissao'], 2, ',', '.') ?></p>
                            <p class="bottom0">Status</p>
                            <p class="txt-verde"><strong>
<?php
                            if($rs_cortes_row['cor_status'] == $GLOBALS['CORTE_STATUS']['ABERTO'])
                            {
                                if($rs_cortes_row['cor_bbc_boleto_codigo'] && $cor_tipo_pagto == $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO'])
                                {
                                    $sql = "select * from boleto_bancario_cortes bbc where bbc.bbc_boleto_codigo = " . $rs_cortes_row['cor_bbc_boleto_codigo'];
                                    $rs_boleto = SQLexecuteQuery($sql);
                                    if($rs_boleto && pg_num_rows($rs_boleto) > 0)
                                    {
                                        $rs_boleto_row = pg_fetch_array($rs_boleto);
                                        $bbc_status = $rs_boleto_row['bbc_status'];
                                        if($bbc_status == $GLOBALS['CORTE_BOLETO_STATUS']['ABERTO'] || $bbc_status == $GLOBALS['CORTE_BOLETO_STATUS']['ENVIADO'])
                                        {
?>								
                                            <a href="/creditos/corte/corte_boleto.php?bbc_boleto_codigo=<?php echo  $rs_cortes_row['cor_bbc_boleto_codigo'] ?>" target="_blank" class="txt-vermelho">
                                                <?php echo  substr($cor_status_descricao, 0, strpos($cor_status_descricao, ".")); ?>
                                                <span class="txt-cinza glyphicon glyphicon-zoom-in t0"></span>
                                            </a>
<?php
                                        }
                                    }
                                }
                            }else
                            {
                                echo  substr($cor_status_descricao, 0, strpos($cor_status_descricao, "."));                                
                            }

?>
                            </strong></p>
                        </div>
                    </div>
                </div>
<?php
                }
            }
?>            
                <div class="col-md-12 bg-cinza-claro hidden-sm hidden-xs">
                    <table class="table bg-branco txt-preto text-center">
                    <thead>
                      <tr class="bg-cinza-claro text-center">
                        <th>Período de Apuração</th>
                        <th>Qtde de vendas</th>
                        <th>Valor total</th>
                        <th>Comissão</th>
                        <th>Venda líquida</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody class="trPaginacao">
<!-- -->                   
<?php
$Tot_QtdeVendas = 0.0;
$Tot_VendaBruta = 0.0;
$Tot_Comissão = 0.0;
$Tot_VendaLíquida = 0.0;

if($arr_rs_cortes)
{
    foreach($arr_rs_cortes as $ind => $rs_cortes_row)
    {
        $cor1 = ($cor1 == $cor2 ? $cor3 : $cor2);
        $cor_status = $rs_cortes_row['cor_status'];
        $cor_status_descricao = $GLOBALS['CORTE_STATUS_DESCRICAO'][$rs_cortes_row['cor_status']];
        $cor_tipo_pagto = $rs_cortes_row['cor_tipo_pagto'];

        $Tot_QtdeVendas += $rs_cortes_row['cor_venda_qtde'];
        $Tot_VendaBruta += $rs_cortes_row['cor_venda_bruta'];
        $Tot_Comissão += $rs_cortes_row['cor_venda_comissao'];
        $Tot_VendaLíquida += $rs_cortes_row['cor_venda_liquida'];

?>
                    <tr class="trListagem"> 
			<td><?php echo  formata_data($rs_cortes_row['cor_periodo_ini'], 0) ?> a <?php echo  formata_data($rs_cortes_row['cor_periodo_fim'], 0); ?></td>
			<td><?php echo  $rs_cortes_row['cor_venda_qtde'] ?> </font></td>
			<td><?php echo  number_format ($rs_cortes_row['cor_venda_bruta'], 2, ',', '.') ?></td>
			<td><?php echo  number_format ($rs_cortes_row['cor_venda_comissao'], 2, ',', '.') ?></td>
			<td><?php echo  number_format ($rs_cortes_row['cor_venda_liquida'], 2, ',', '.') ?></td>
            <td class="txt-verde">
<?php


                if($rs_cortes_row['cor_status'] == $GLOBALS['CORTE_STATUS']['ABERTO'])
                {
                    if($rs_cortes_row['cor_bbc_boleto_codigo'] && $cor_tipo_pagto == $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO'])
                    {
                        $sql = "select * from boleto_bancario_cortes bbc where bbc.bbc_boleto_codigo = " . $rs_cortes_row['cor_bbc_boleto_codigo'];
                        $rs_boleto = SQLexecuteQuery($sql);
                        if($rs_boleto && pg_num_rows($rs_boleto) > 0)
                        {
                            $rs_boleto_row = pg_fetch_array($rs_boleto);
                            $bbc_status = $rs_boleto_row['bbc_status'];
                            if($bbc_status == $GLOBALS['CORTE_BOLETO_STATUS']['ABERTO'] || $bbc_status == $GLOBALS['CORTE_BOLETO_STATUS']['ENVIADO'])
                            {
?>								
                                <a href="/creditos/corte/corte_boleto.php?bbc_boleto_codigo=<?php echo  $rs_cortes_row['cor_bbc_boleto_codigo'] ?>" target="_blank" class="txt-vermelho">
                                    <?php echo  substr($cor_status_descricao, 0, strpos($cor_status_descricao, ".")); ?>
                                    <span class="txt-cinza glyphicon glyphicon-zoom-in t0"></span>
                                </a>
<?php
                            }
                        }
                    }
                }else
                {
                    echo  substr($cor_status_descricao, 0, strpos($cor_status_descricao, "."));                                
                }
?>
			</td>
                    </tr>
<?php			
    }
}
if($total_table <= 0)
{
?>
                    <tr class="text-center txt-vermelho">
                        <td colspan="6">Nenhum pedido foi encontrado.</th>
                    </tr>
<?php
}
?>
<!-- -->                   
                    </tbody>
                  </table>
                </div>
<?php
                $paginaAtual = ceil($reg_ate/$max);
                $paginacao = Util::pagination($paginaAtual, $max, $total_table, $iptHidden);
                require_once "includes/paginacao.php";
?>
            </div>
        <div class="col-md-2 p-top10 hidden-sm hidden-xs">
<?php 
            if($banner){
                foreach($banner as $b){
?>
                <div class="row pull-right">
                    <a href="<?php echo $b->link; ?>" class="banner" id="<?php echo $b->id; ?>" target="_blank"><img src="<?php echo $controller->objBanner->urlLink.$b->imagem; ?>" width="186" class="p-3" title="<?php echo $b->titulo; ?>"></a>
                </div>
<?php 
                }
            }
?>
            <div class="row pull-right facebook">
            </div>
        </div>
        
    </div>
</div>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script>
$(function(){

    var currentDate = new Date();
    
    jQuery(function(e){
        e.datepicker.regional["pt-BR"]={
            closeText:"Fechar",
            prevText:"&#x3C;Anterior",
            nextText:"Próximo&#x3E;",
            currentText:"Hoje",
            monthNames:["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
            monthNamesShort:["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
            dayNames:["Domingo","Segunda-feira","Terça-feira","Quarta-feira","Quinta-feira","Sexta-feira","Sábado"],
            dayNamesShort:["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],
            dayNamesMin:["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],
            weekHeader:"Sm",
            dateFormat:"dd/mm/yy",
            firstDay:0,
            isRTL:!1,
            showMonthAfterYear:!1,
            yearSuffix:""},e.datepicker.setDefaults(e.datepicker.regional["pt-BR"])});

    $("#tf_v_data_inclusao_ini").datepicker({
        minDate: "<?php echo $dataInclusao[0]; ?>",
        maxDate: "dateToday",
        changeMonth: true,
        dateFormat: 'dd/mm/yy',
        onClose: function (selectedDate, instance) {
            if (selectedDate != '') {
                $("#tf_v_data_inclusao_fim").datepicker("option", "minDate", selectedDate);
                var date = $.datepicker.parseDate(instance.settings.dateFormat, selectedDate, instance.settings);
                date.setMonth(date.getMonth() + 6);
                if(date > currentDate)
                    date = currentDate;
                $("#tf_v_data_inclusao_fim").datepicker("option", "minDate", selectedDate);
                $("#tf_v_data_inclusao_fim").datepicker("option", "maxDate", date);
            }
        }
    });
    
    var data = $("#tf_v_data_inclusao_ini").datepicker("getDate");
    if(data){
        var tmpData = data;
        tmpData.setMonth(tmpData.getMonth() + 6);
        data = (tmpData <= currentDate) ? tmpData : currentDate;
        
    }else{
        data = currentDate;
    }
    
    $("#tf_v_data_inclusao_fim").datepicker({
        maxDate: data,
        changeMonth: true,
        dateFormat: 'dd/mm/yy',
        minDate: $("#tf_v_data_inclusao_ini").datepicker("getDate")
    });
});
</script>
<?php
require_once "includes/footer.php";
?>