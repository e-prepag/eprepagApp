<?php
require_once "../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/DepositosController.class.php";

$controller = new DepositosController;

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



$iptHidden = $_POST;

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "/imagens/proxima.gif";
$img_anterior = "/imagens/anterior.gif";
$max          = $qtde_reg_tela;
$range_qtde   = $qtde_range_tela;

$intervaloMaximoMeses = 6;
$dataInclusao = explode(" ",$controller->usuarios->getDataInclusao());

if(isset($_POST["hidden_p"])) //pegando variavel de paginacao antes de sobrescrever
{
    $_POST['p'] = $_POST["hidden_p"];
    $inicial = ($_POST['p']*$max)-$max;
}

if(isset($_POST)){ //limpando post
    $temp = Util::getIptHidden($_POST);
    unset($_POST);
    $_POST = $temp;
}

if(isset($_POST['tf_v_data_inclusao_ini']) && isset($_POST['tf_v_data_inclusao_fim']) && (!isset($_POST['tf_v_codigo']) || $_POST['tf_v_codigo'] == ""))
{
    if(!Util::dateDiff($_POST['tf_v_data_inclusao_ini'], $_POST['tf_v_data_inclusao_fim'], $intervaloMaximoMeses))
    {
        print "<script>"
        . "alert('Por favor, selecione um período entre as datas de até {$intervaloMaximoMeses} mêses.');"
        . "</script>";
        $parametroErro = true;
    }
    else if(!Util::dateDiff($_POST['tf_v_data_inclusao_ini'], $dataInclusao[0], 0))
    {
        print "<script>"
        . "alert('Seu cadastro foi efetuado no dia ".$dataInclusao[0].", por favor, selecione um período após esse.');"
        . "</script>";
        $parametroErro = true;
    }
}else{
    $parametroErro = true;
}

if(!isset($_POST['tf_v_data_inclusao_ini']) || $parametroErro)
    $_POST['tf_v_data_inclusao_ini'] = date("d/m/Y", mktime(0,0,0,date("m"),date("d")-7,date("Y")));

if(!isset($_POST['tf_v_data_inclusao_fim']) || $parametroErro)
    $_POST['tf_v_data_inclusao_fim'] = date("d/m/Y", mktime(0,0,0,date("m"),date("d"),date("Y")));

$varsel .= "";

$sql  = "";
$sql  = "select * from  ( \n";
$sql .= "	select  'B' as tipo, bol_codigo,(bbg_valor - bbg_valor_taxa) as bol_valor, vg_pagto_data_inclusao, bol_importacao, bol_documento, vg_ultimo_status \n ";
$sql .= "	from boletos_pendentes, bancos_financeiros, tb_dist_venda_games, dist_boleto_bancario_games \n ";
$sql .= "	where (bol_banco = bco_codigo)  \n and (bbg_vg_id = vg_id) \n";
$sql .= "		and (bol_venda_games_id=vg_id) and (bco_rpp = 1) \n ";
if(isset($_POST['tf_v_data_inclusao_ini']) && isset($_POST['tf_v_data_inclusao_fim']))
    if(verifica_data($_POST['tf_v_data_inclusao_ini']) != 0 && verifica_data($_POST['tf_v_data_inclusao_fim']) != 0)
        $sql .= " and vg_pagto_data_inclusao >= '".formata_data($_POST['tf_v_data_inclusao_ini'],1)." 00:00:00' and vg_pagto_data_inclusao <= '".formata_data($_POST['tf_v_data_inclusao_fim'],1)." 23:59:59'";
//		$sql .= "		and cast(substr(bol_documento,2,5) as int)= ".$controller->usuarios->getId()." \n ";
$sql .= "		and vg_ug_id= ".$controller->usuarios->getId()." \n ";
$sql .= "		and bol_documento LIKE '4%'  \n";
//		$sql .= "	-- order by bol_importacao desc  \n"; //"bol_data desc";

$sql .= "	union all  \n";

$sql .= "	select 'D' as tipo,  idvenda as bol_codigo, (total/100 - taxas) as bol_valor, datainicio as vg_pagto_data_inclusao, datacompra as bol_importacao, numcompra as bol_documento, status as vg_ultimo_status \n";
$sql .= "	from tb_pag_compras 
        	where idcliente= ".$controller->usuarios->getId()."  
                        and tipo_cliente = 'LR'
        		and status = 3  \n";
if(isset($_POST['tf_v_data_inclusao_ini']) && isset($_POST['tf_v_data_inclusao_fim']))
    if(verifica_data($_POST['tf_v_data_inclusao_ini']) != 0 && verifica_data($_POST['tf_v_data_inclusao_fim']) != 0)
        $sql .= " and datacompra >= '".formata_data($_POST['tf_v_data_inclusao_ini'],1)." 00:00:00' and datacompra <= '".formata_data($_POST['tf_v_data_inclusao_fim'],1)." 23:59:59'";
//		$sql .= "		--and vg_data_inclusao > '2013-01-01 00:00:00' \n";

//$sql .= "		and vg_pagto_num_docto LIKE '5%' \n";
//		$sql .= "	--order by vg_data_inclusao desc  \n";
$sql .= "	) b \n";
$sql .= "	order by vg_pagto_data_inclusao desc \n";


$res_count = SQLexecuteQuery($sql);
$total_table = pg_num_rows($res_count);

$sql .= " limit ".$max; 
$sql .= " offset ".$inicial;
$rs_boletos = SQLexecuteQuery($sql);

if($max + $inicial > $total_table) $reg_ate = $total_table;
else $reg_ate = $max + $inicial;



?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>MEUS DEPÓSITOS</strong>
                </div>
            </div>
            <div class="row txt-cinza">
                <div class="col-md-12 espacamento">
                    <p class="margin004"><strong>Selecione o período do depósito</strong></p>
                    <p class="margin004"><span class="fontsize-p">(Intervalo máximo de 6 meses)</span></p>
                </div>
            </div>
            <div class="row txt-cinza">
                <form method="post">
                <div class="col-md-2">
                    <p>Data de início</p>
                    <p><input type="text" class="form-control data" name="tf_v_data_inclusao_ini" id="tf_v_data_inclusao_ini" value="<?php if(isset($_POST['tf_v_data_inclusao_ini'])) echo $_POST['tf_v_data_inclusao_ini']; ?>"></p>
                </div>
                <div class="col-md-2">
                    <p>Data final</p>
                    <p><input type="text" class="form-control data" name="tf_v_data_inclusao_fim" id="tf_v_data_inclusao_fim" value="<?php if(isset($_POST['tf_v_data_inclusao_fim'])) echo $_POST['tf_v_data_inclusao_fim']; ?>"></p>
                </div>
                <div class="col-md-2">
                    <p>&nbsp;</p>
                    <p><button type="submit" class="btn btn-success">Pesquisar</button></p>
                </div>
                </form>
            </div>
            <div class="row txt-cinza espacamento">
                <div class="col-md-12 bg-cinza-claro">
                    <table class="table bg-branco txt-preto">
                    <thead>
                      <tr class="bg-cinza-claro">
                        <th>Pedido</th>
                        <th>Data do pedido</th>
                        <th>Data do depósito</th>
                        <th>Valor</th>
                      </tr>
                    </thead>
                    <tbody class="trPaginacao">
<!-- -->                        
                        
<?php                        
                        $Tot_Valor = 0.0;
                        $nrecords = 0;
//echo $rs_boletos;
            if($rs_boletos) 
            {
		while($rs_boletos_row = pg_fetch_array($rs_boletos))
                {

                    $Tot_Valor += $rs_boletos_row['bol_valor'];
                    $nrecords += 1;				
?>
                    <tr class="texto trListagem"> 
			<td><?php echo $rs_boletos_row['bol_codigo'] ?> </font></td>
			<td><?php echo substr(formata_data_ts($rs_boletos_row['vg_pagto_data_inclusao'],0, true, true),0,16); ?> </font></td>
			<td><?php echo substr(formata_data_ts($rs_boletos_row['bol_importacao'],0, true, true),0,16); ?> </font></td>
                        <td><?php echo number_format ($rs_boletos_row['bol_valor'], 2, ',', '.') ?>&nbsp;</td>
                    </tr>
<?php
                }
                if($nrecords>0) 
                {
?>
                    <tr class="bg-cinza-claro"> 
			<td>Total:&nbsp;</td>
			<td><?php echo number_format ($Tot_Valor, 2, ',', '.') ?>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
                    </tr>
<?php
                } 
                else 
                {
?>
                    <tr class="texto" bgcolor="#D5D5DB"> 
                        <td align="center" colspan="4">Sem depósitos no período informado.&nbsp;</td>
                    </tr>
<?php
                }
        }
		// Apresenta Total desta LH nesta página
?>
<!-- -->                     
                    </tbody>
                  </table>
<?php
                $paginaAtual = ceil($reg_ate/$max);
                $paginacao = Util::pagination($paginaAtual, $max, $total_table, $iptHidden);
                require_once "includes/paginacao.php";
?>
                </div>
            </div>
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
        <div class="row top20 facebook"></div>
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
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>