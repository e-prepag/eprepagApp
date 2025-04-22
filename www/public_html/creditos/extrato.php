<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
set_time_limit(300);

require_once "../../includes/constantes.php";
require_once DIR_CLASS . 'pdv/controller/ExtratoController.class.php';
require_once RAIZ_DO_PROJETO.'includes/gamer/functions_pagto.php';

$parametroErro = false;

$controller = new ExtratoController;
$dataInclusao = explode(" ",$controller->usuarios->getDataInclusao());
$intervaloMaximoMeses = 6;
$limit = $qtde_reg_tela; //limite atual de 20 registros por pagina

if(isset($_POST["hidden_p"])) //pegando variavel de paginacao antes de sobrescrever
    $_POST['p'] = $_POST["hidden_p"];

if(isset($_POST)){ //limpando post
    $temp = Util::getIptHidden($_POST);
    unset($_POST);
    $_POST = $temp;
}

if(isset($_POST['tf_v_data_inclusao_ini']) && isset($_POST['tf_v_data_inclusao_fim']))
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
}

if(!isset($_POST['tf_v_data_inclusao_ini']) || $parametroErro)
    $_POST['tf_v_data_inclusao_ini'] = date("d/m/Y", mktime(0,0,0,date("m"),date("d")-7,date("Y")));

if(!isset($_POST['tf_v_data_inclusao_fim']) || $parametroErro)
    $_POST['tf_v_data_inclusao_fim'] = date("d/m/Y", mktime(0,0,0,date("m"),date("d"),date("Y")));

$banner = $controller->getBanner();

if(isset($_POST['p']))
{
    $total['total_final_entrada'] = $_POST['total_final_entrada'];
    $total['total_final_saida'] = $_POST['total_final_saida'];
    $total['total_final_comissao'] = $_POST['total_final_comissao'];
    $total['saldo_conta'] = $_POST['saldo_conta'];
    $total['qtd_total_registros'] = $_POST['qtd_total_registros'];
    $p = $_POST['p'];
}else{
    
    $total = $controller->getTotalEntradaSaidaComissao();
    $p = 1;
}

$iptHidden = array_merge($_POST, $total);
$extratos = $controller->init($limit, $p);

// Monta o arquivo CSV com os pedidos para download

if (!empty($extratos)) {

	function substituirValoresVazios($valor) {
		return ($valor === '' || $valor === null) ? 'Vazio' : $valor;
	}
	
	$todosPedidos = [];
	
	foreach ($extratos as $pedido => $dado) {
		
		for ($i = 0; $i < count($dado); $i++) {
			array_push($todosPedidos, $dado[$i]);
		}
		
	}
	
	header('Content-Type: text/csv');
	
	$nomeArquivoCSV = 'extrato.csv';
	
	if (($handle = fopen($nomeArquivoCSV, 'w')) !== false) {
		// Escreve o cabeçalho do CSV
		fputcsv($handle, array_keys($todosPedidos[0])); // Assume que todos os subarrays têm as mesmas chaves

		// Escreve os dados no CSV
		foreach ($todosPedidos as $pedido) {
			
			$pedido = array_map('substituirValoresVazios', $pedido);
			
			fputcsv($handle, $pedido);
		}

		// Fecha o arquivo CSV
		fclose($handle);

		echo "<script>console.log('Os dados foram escritos com sucesso no arquivo CSV: {$nomeArquivoCSV}')</script>";
		
	} else {
		
		echo "<script>console.log('Não foi possível abrir o arquivo CSV para escrita.')</script>";
		
	}
	
}

$operadores = $controller->getOperadores();
require_once "includes/header.php";
?>
<div class="container txt-azul-claro bg-branco" style="min-width: 680px !important;">
    <div class="row">
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>EXTRATO</strong>
                </div>
            </div>
            <form id="form1" name="form1" method="post">
                <div class="row txt-cinza">
                    <div class="col-md-12 top10">
                         <p class="margin004"><strong>Selecione o Período</strong></p>
                        <p class="margin004"><span class="fontsize-p">(Intervalo máximo de 6 meses)</span></p>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-2 col-sm-12 col-xs-12 ">
                        <p>Data de início</p>
                        <p><input type="text" class="form-control data w100p" readonly="readonly" value="<?php echo isset($_POST['tf_v_data_inclusao_ini']) ? $_POST['tf_v_data_inclusao_ini'] : ""; ?>" name="tf_v_data_inclusao_ini" id="tf_v_data_inclusao_ini"></p>
                    </div>
                    <div class="col-md-2  col-sm-12 col-xs-12">
                        <p>Data final</p>
                        <p><input type="text" class="form-control data w100p" readonly="readonly" value="<?php echo isset($_POST['tf_v_data_inclusao_fim']) ? $_POST['tf_v_data_inclusao_fim'] : ""; ?>" name="tf_v_data_inclusao_fim" id="tf_v_data_inclusao_fim"></p>
                    </div>
                    <div class="col-md-3 col-sm-12  col-xs-12">
                        <p>Vendas de Funcionários</p>
                        <p><select class="form-control w-auto" name="ugo_login" id="ugo_login">
                            <option value="">Todos</option>
<?php 
                        foreach($operadores as $operador)
                        {
?>
                            <option value="<?php echo $operador;?>" <?php if($_POST['ugo_login'] == $operador) echo "selected"; ?>><?php echo $operador;?></option>
<?php
                        }
?>
                        </select></p>
                    </div>
                    <div class="col-md-3 col-sm-12  col-xs-12">
                        <p><input type="submit" class="btn top20 btn-info" value="Buscar"></p>
                    </div>
                </div>
            </form>
            <hr>
            <form id="changePage" name="changePage" method="post" action="/creditos/extrato.php">
<?php
            if($controller->usuarios->getRiscoClassif() == 2)
            {
?>
                <div class="row txt-azul top10">
                    <div class="col-md-4 col-sm-5  col-xs-5 p-left15">
                        <strong>Total de depósitos: </strong>
                        <span class="txt-verde pull-right"><strong>R$ <?php echo number_format($total['total_final_entrada'],2,",",".");?></strong></span>
                    </div>
                </div>
<?php
            }
?>
                <div class="row txt-azul <?php if($controller->usuarios->getRiscoClassif() == 1) echo "top10";?>">
                    <div class="col-md-4 col-sm-5  col-xs-5 p-left15">
                        <strong>Total de vendas: </strong>
                        <span class="txt-vermelho pull-right"><strong>R$ <?php echo number_format($total['total_final_saida'],2,",",".");?></strong></span>
                    </div>
                </div>
                <div class="row txt-azul ">
                    <div class="col-md-4 col-sm-5  col-xs-5 p-left15">
                        <strong>Total de comissão: </strong>
                        <span class="txt-cinza pull-right"><strong>R$ <?php echo number_format($total['total_final_comissao'],2,",",".");?></strong></span>
                    </div>
                </div>
				
<?php
			// Exibe o btn para exportação dos pedidos em CSV
			if (!empty($extratos)) {
?>
				<div class="row txt-azul ">
                    <div class="col-md-4 col-sm-5  col-xs-5 p-left15">
                        <a href='extrato.csv' class='btn top20 btn-info' download>Download CSV</a>
                    </div>
                </div>
<?php
			}

?>
				
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table class="table bg-branco txt-preto">
                            <thead>
                                <tr class="bg-cinza-claro">
                                    <th>Pedido E-Prepag</th>
									<th>Pedido API</th>
                                    <th class="text-center">Data</th>
                                    <th>Transação</th>
                                    <th class="txt-verde text-right">Crédito</th>
                                    <th class="txt-vermelho text-right">Débito</th>
                                    <th class="text-right">Comissão</th>
                                    <th>Funcionário</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
<?php
                    $total_final_entrada = 0;
                    $total_final_saida = 0;
                    $total_comissao = 0;
    
                    if(is_array($extratos['pedidos']) && !empty($extratos['pedidos']))
                    {
                        foreach($extratos['pedidos'] as $ind => $extrato)
                        {
                            $total_final_entrada += $extrato['valor_view'];
                            $total_final_saida += $extrato['valor_venda'];
                            $total_comissao += $extrato['comissao'];
    ?>                        
                                <tr class="trListagem">
                                    <td>
<?php 
                                    if($extrato['transacao'] == "Boleto" || $extrato['transacao'] == "Depósito" || $extrato['tipo'] == 'Recarga Celular' || $extrato['tipo'] == 'B2C' || $extrato['tipo'] == 'Seguro')
                                    {
                                        echo $extrato['num_doc'];  
                                        $iforma_numerico = getCodigoCaracterParaPagto($extrato['tipo_pagto']);
                                        if($extrato['transacao'] == "Depósito"){
                                            echo '<img class="margin9n p-3 pull-right" src="/imagens/'.str_replace(array(".gif",".jpg"),".png",$FORMAS_PAGAMENTO_ICONES[$iforma_numerico]).'" border="0" title="'.$FORMAS_PAGAMENTO_DESCRICAO[$iforma_numerico].'">';
                                        }elseif($extrato['transacao'] == "Boleto"){
                                            echo '<img class="margin9n p-3 center-block" src="/imagens/'.str_replace(array(".gif",".jpg"),".png",$FORMAS_PAGAMENTO_ICONES[$iforma_numerico]).'" border="0" title="'.$FORMAS_PAGAMENTO_DESCRICAO[$iforma_numerico].'">';
                                        }
                                        
                                    }else
                                    {  
?>
                                        <a class="decoration-none detalhePedido" href="#" title="Ver detalhes" alt="Ver detalhes" pedido="<?php echo $extrato['num_doc']?>"><?php echo $extrato['num_doc']?></a> 
<?php 
                                        
                                    } 
?>
                                    </td>
									<td class="txt-cinza"><?php echo $extrato['pedido_parceiro'];?></td>
                                    <td align="center"><?php echo $extrato['data_view'];?></td>
                                    <td><?php echo $extrato['transacao'];?></td>
                                    <td class="txt-verde text-right"><?php echo $extrato['valor_view'] != "" ? number_format($extrato['valor_view'],2,",",".") : "";?></td>
                                    <td class="txt-vermelho text-right"><?php echo $extrato['valor_venda'] != "" ? number_format($extrato['valor_venda'],2,",",".") : "";?></td>
                                    <td class="text-right"><?php echo $extrato['comissao'] != "" ? number_format($extrato['comissao'],2,",",".") : ""; ?></td>
                                    <td><?php echo $extrato['operador'] != "" ? $extrato['operador'] : "";?></td>
                                    <td><?php if($extrato['transacao'] == "Boleto" || $extrato['transacao'] == "Depósito" || $extrato['tipo'] == 'Recarga Celular' || $extrato['tipo'] == 'B2C' || $extrato['tipo'] == 'Seguro') echo "";  else{  ?><a class="decoration-none detalhePedido" href="#" title="Ver detalhes" alt="Ver detalhes" pedido="<?php echo $extrato['num_doc']?>"><span class="glyphicon glyphicon-zoom-in"></span></a><?php } ?></td>
                                </tr>
<?php
                        }
                    }        
?>
                                <tr class="bg-cinza-claro">
                                    <td colspan="3">&nbsp;</td>
                                    <td class="txt-verde text-right">R$ <?php echo number_format($total_final_entrada,2,",",".");?></td>
                                    <th class="txt-vermelho text-right">R$ <?php echo number_format($total_final_saida,2,",",".");?></th>
                                    <td class="txt-cinza text-right">R$ <?php echo number_format($total_comissao,2,",",".");?></td>
                                    <td colspan="3">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
<?php         
        if($total['qtd_total_registros'] > $limit)
        {
            $paginacao = Util::pagination($p, $limit, $total['qtd_total_registros'], $iptHidden);
            require_once "includes/paginacao.php";
        }

?>            
            <form method="POST" name="detalhe_extrato" id="detalhe_extrato" action="/creditos/pedido/detalhe.php">
                <input type="hidden" id="tf_v_codigo_detalhe" name="tf_v_codigo_detalhe" value="">
            </form>
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
$(function () {
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
    
    $(".detalhePedido").click(function(){
       $("#tf_v_codigo_detalhe").val($(this).attr("pedido"));
       $("#detalhe_extrato").submit();
       
    });
});
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>