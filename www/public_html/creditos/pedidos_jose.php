<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../includes/constantes.php";

if(isset($GLOBALS['_SESSION']['vendaB2C'])) {
    echo "<script language='javascript'>alert('Aguarde por favor, seu pedido já está sendo processado!');</script>";
}

require_once DIR_CLASS . "pdv/controller/PedidosController.class.php";
require_once DIR_INCS . "pdv/b2c/config.inc.b2c.php";

$_PaginaOperador2Permitido = 54;
$_PaginaOperador1Permitido = 53; // o número magico

$boolNaoImpresso = false;
$limit = $qtde_reg_tela;
$parametroErro = false;
$controller = new PedidosController;

/*
	// ID E-prepag Testes
	if ($controller->usuarios->getId() == 17371) {
		$limit = 1000;
	}
	
*/

	if ($controller->usuarios->getId() == 14549) {
		$limit = 3000;
	}

$dataInclusao = explode(" ",$controller->usuarios->getDataInclusao());
$intervaloMaximoMeses = 8;

if(isset($_POST["hidden_p"])) //pegando variavel de paginacao antes de sobrescrever
    $_POST['p'] = $_POST["hidden_p"];

if(isset($_POST)){ //limpando post
    $temp = Util::getIptHidden($_POST);
    unset($_POST);
    $_POST = $temp;
}

if((isset($_POST['tf_v_data_inclusao_ini']) && $_POST['tf_v_data_inclusao_ini'] != "") && 
   (isset($_POST['tf_v_data_inclusao_fim']) && $_POST['tf_v_data_inclusao_fim'] != "") && 
   (!isset($_POST['tf_v_codigo']) || $_POST['tf_v_codigo'] == ""))
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
    if(!isset($_POST['tf_v_codigo']) || $_POST['tf_v_codigo'] == "")
    {
        $parametroErro = true;
    }else{
        $_POST['tf_v_data_inclusao_fim'] = "";
        $_POST['tf_v_data_inclusao_ini'] = "";
    }
    
}

if(!isset($_POST['tf_v_data_inclusao_ini']) || $parametroErro){
    $_POST['tf_v_data_inclusao_ini'] = date("d/m/Y", mktime(0,0,0,date("m"),date("d")-7,date("Y")));
}
    

if(!isset($_POST['tf_v_data_inclusao_fim']) || $parametroErro)
    $_POST['tf_v_data_inclusao_fim'] = date("d/m/Y", mktime(0,0,0,date("m"),date("d"),date("Y")));


if(isset($_GET['nao_emitidos']) && $_GET['nao_emitidos'] == 1){
    $boolNaoImpresso = true;
}

$banner = $controller->getBanner();

$dataInclusao = explode(" ",$controller->usuarios->getDataInclusao());

$p = (isset($_POST['p']) && $_POST['p'] > 1) ? $_POST['p'] : 1;
$iptHidden = $_POST;

$pedidos = $controller->getPedidosLanHouse($limit, $p, $boolNaoImpresso);

if($controller->usuarios->getId() == 17371){
	//var_dump($pedidos["vendas"][0]->getIdVenda());
}

require_once "includes/header.php";
?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10">
            <div class="col-md-12 espacamento">
                <strong>MEUS PEDIDOS</strong>
            </div>
            <div class="row espacamento txt-cinza">
                <p class="margin004"><strong>Selecione o período ou número do pedido</strong></p>
                <p class="margin004"><span class="fontsize-p">(Intervalo máximo de 8 meses)</span></p>
            </div>
            <div class="row">
                <form method="post">
                    <div class="col-md-2 col-sm-12 col-xs-12 col-lh-2 txt-cinza">
                        <p>Data de início</p>
                        <p><input type="text" placeholder="Data de início" class="form-control data" name="tf_v_data_inclusao_ini" id="tf_v_data_inclusao_ini" value="<?php if(isset($_POST['tf_v_data_inclusao_ini'])) echo $_POST['tf_v_data_inclusao_ini']; ?>"></p>
                    </div>
                    <div class="col-md-2 col-sm-12 col-xs-12 col-lh-2 txt-cinza">
                        <p>Data final</p>
                        <p><input type="text" placeholder="Data final" class="form-control data" name="tf_v_data_inclusao_fim" id="tf_v_data_inclusao_fim" value="<?php if(isset($_POST['tf_v_data_inclusao_fim'])) echo $_POST['tf_v_data_inclusao_fim']; ?>"></p>
                    </div>
                    <div class="col-md-3 col-sm-12 col-xs-12 col-lh-2 txt-cinza">
                        <p>Número do pedido</p>
                        <p><input type="text" placeholder="Número do pedido" class="form-control" name="tf_v_codigo" id="tf_v_codigo" value="<?php if(isset($_POST['tf_v_codigo'])) echo $_POST['tf_v_codigo']; ?>"></p>
                    </div>
                    <div class="col-md-2 col-sm-12 col-xs-12 col-lh-2">
                        <p>&nbsp;</p>
                        <p><button type="submit" class="btn btn-success">Pesquisar</button></p>
                    </div>
                </form>
            </div>
            <div class="row txt-cinza espacamento">
                <form method="POST" id="listaPedido" action="/creditos/pedido/detalhe_jose.php">
<?php 
                    if(is_array($pedidos['vendas']))
                    {
                        foreach($pedidos['vendas'] as $ind => $pedido)
                        {

                            if(isset($_GET['nao_emitidos']) && $_GET['nao_emitidos'] == 1)
                            {
                                if(!$controller->getBoolCuponsImpressao($pedido->getIdVenda()))
                                    continue;
                                else
                                    $temRegistro = true;
                            }
                            
                            if($pedido->getTipoPagamento() != "B2C" && $pedido->getTipoPagamento() != "Recarga") 
                            { 
                            
                                $status = $pedido->getStatus();
                                
                                if($status == 5 || $status == 1){
                                    $corStatus = "txt-verde";
                                    
                                }else if($status == 6){
                                    $corStatus = "txt-vermelho";
                                    
                                }else {
                                    $corStatus = "txt-laranja";
                                    
                                }
                                
                                $statusIcone        = $STATUS_VENDA_ICONES[$status];
                                $statusDescricao    = $STATUS_VENDA_DESCRICAO[$status];
                                
                            }
                            else 
                            { 
                                if($pedido->getStatus() == 'N') 
                                {
                                    $statusIcone        = $STATUS_VENDA_ICONES[$STATUS_VENDA['VENDA_CANCELADA']];
                                    $statusDescricao    = $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['VENDA_CANCELADA']];
                                    $corStatus = "txt-vermelho";
                                }
                                elseif($pedido->getStatus() == '0')
                                {
                                    $statusIcone = $STATUS_VENDA_ICONES[$STATUS_VENDA['PEDIDO_EFETUADO']];
                                    $statusDescricao = $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['PEDIDO_EFETUADO']];
                                    $corStatus = "txt-laranja";
                                }
                                elseif($pedido->getStatus() == '1') 
                                {
                                    $statusIcone = $STATUS_VENDA_ICONES[$STATUS_VENDA['VENDA_REALIZADA']];
                                    $statusDescricao = $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['VENDA_REALIZADA']];
                                    $corStatus = "txt-verde";

                                }
                            }
?>                    
                            <div class="hidden-lg hidden-md txt-preto espacamento">
                                <div class="row p-3 borda-fina">
                                    <div class="col-sm-6 col-xs-6 borda-colunas-formas-pagamento">
                                        <p>
<?php 
                                            if(!($pedido->getTipoPagamento() == "Boleto" || $pedido->getTipoPagamento() == "Depósito" || $pedido->getTipoPagamento() == 'Recarga Celular' || $pedido->getTipoPagamento() == 'B2C' || $pedido->getTipoPagamento() == 'Seguro'))
                                            {
?>
                                                <a class="decoration-none detalhePedido" href="#" title="Clique para emitir" alt="Clique para emitir" pedido="<?php echo $pedido->getIdVenda(); ?>"><span class="glyphicon glyphicon-search"></span></a>
<?php
                                            }else{
                                                echo "&nbsp;";
                                            }
?>                                
                                            </p>
                                        <p class="bottom0">Pedido:</p>
                                        <p class="txt-azul-claro text18"><strong>

<?php 
                                            if($pedido->getTipoPagamento() == "Boleto" || $pedido->getTipoPagamento() == "Depósito" || $pedido->getTipoPagamento() == 'Recarga Celular' || $pedido->getTipoPagamento() == 'B2C' || $pedido->getTipoPagamento() == 'Seguro')
                                            {
                                                echo $pedido->getIdVenda();
                                            }
                                            else
                                            {
?>
                                                <a class="decoration-none detalhePedido" href="#" title="Clique para emitir" alt="Clique para emitir" pedido="<?php echo $pedido->getIdVenda(); ?>"><?php echo $pedido->getIdVenda(); ?></a>
<?php
                                            }
?>
                                        </strong></p>
                                        <p>
<?php
                                            if(is_array($pedido->getCesta()))
                                            {
?>
                                            <div class="dropdown top10">
                                                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu<?php echo $ind;?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Itens <span class="caret"></span></button> 
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu<?php echo $ind;?>">
<?php
                                                    foreach($pedido->getCesta() as $produto)
                                                    {
                                                        if($pedido->getTipoPagamento() == 'B2C')
                                                            $produto = $GLOBALS['B2C_PRODUCT'][(string) $produto]['name'];
?>
                                                        <li class="dropdown-header"><?php echo $produto; ?></li>
<?php
                                                    }
?>
                                                </ul>
                                            </div>
<?php
                                            }
?>                            
                                        </p>
                                    </div>
                                    <div class="col-sm-6 col-xs-6">
                                        <p>&nbsp;</p>
                                        <p class="bottom0">Data:</p>
                                        <p class="text18"><strong><?php echo substr(formata_data_ts($pedido->getDataInclusao(),0, true,true),0,16); ?></strong></p>
                                        <p class="bottom0">Valor:</p>
                                        <p class="text18"><strong>R$ <?php echo number_format($pedido->getValor(), 2, ',','.'); ?></strong></p>
                                    </div>
                                </div>
                                <div class="p-3 row">
                                    <p class="<?php echo $corStatus;?>">Status: <?php echo $statusDescricao;?></p>
                                </div>
                            </div>
<?php
                        }
                    
                    }
?>
                <div class="col-md-12 bg-cinza-claro">
                    <table class="table bg-branco hidden-sm hidden-xs txt-preto text-center">
                    <thead>
                      <tr class="bg-cinza-claro text-center">
                        <th>Pedido E-prepag</th>
						<th>Pedido API</th>
                        <th>&nbsp;</th>
                        <th>Data do pedido</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Operador</th>
                        <th>Não emitido</th>
						<th>Garena não emitidos</th>	
                        <th>&nbsp;</th>
                      </tr>
                    </thead>
                    <tbody>
<?php 
                    if(is_array($pedidos['vendas']))
                    {
                        foreach($pedidos['vendas'] as $ind => $pedido)
                        {
                            
                            $sql = "SELECT ugo_nome FROM dist_usuarios_games_operador ugo INNER JOIN tb_dist_venda_games_operador vgo on ugo.ugo_id = vgo.ugo_id WHERE vgo.vg_id = " . $pedido->getIdVenda();
                            $ret_operador = SQLexecuteQuery($sql);
                            $operador = pg_fetch_assoc($ret_operador);
                            
                            if(isset($_GET['nao_emitidos']) && $_GET['nao_emitidos'] == 1)
                            {
                                if(!$controller->getBoolCuponsImpressao($pedido->getIdVenda()))
                                    continue;
                                else
                                    $temRegistro = true;
                            }
                            
                            if($pedido->getTipoPagamento() != "B2C" && $pedido->getTipoPagamento() != "Recarga") 
                            { 
                                $statusIcone        = $STATUS_VENDA_ICONES[$pedido->getStatus()];
                                $statusDescricao    = $STATUS_VENDA_DESCRICAO[$pedido->getStatus()];
                                
                            }
                            else 
                            { 
                                if($pedido->getStatus() == 'N') 
                                {
                                    $statusIcone        = $STATUS_VENDA_ICONES[$STATUS_VENDA['VENDA_CANCELADA']];
                                    $statusDescricao    = $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['VENDA_CANCELADA']];
                                }
                                elseif($pedido->getStatus() == '0')
                                {
                                    $statusIcone = $STATUS_VENDA_ICONES[$STATUS_VENDA['PEDIDO_EFETUADO']];
                                    $statusDescricao = $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['PEDIDO_EFETUADO']];
                                }
                                elseif($pedido->getStatus() == '1') 
                                {
                                    $statusIcone = $STATUS_VENDA_ICONES[$STATUS_VENDA['VENDA_REALIZADA']];
                                    $statusDescricao = $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['VENDA_REALIZADA']];

                                }
                            }
?>
                        <tr class="trListagem">
                            <td>
<?php 
                                if($pedido->getTipoPagamento() == "Boleto" || $pedido->getTipoPagamento() == "Depósito" || $pedido->getTipoPagamento() == 'Recarga Celular' || $pedido->getTipoPagamento() == 'B2C' || $pedido->getTipoPagamento() == 'Seguro')
                                {
                                    echo $pedido->getIdVenda();
                                }
                                else
                                {
?>
                                    <a class="decoration-none detalhePedido" href="#" title="Clique para emitir" alt="Clique para emitir" pedido="<?php echo $pedido->getIdVenda(); ?>"><?php echo $pedido->getIdVenda(); ?></a>
<?php
                                }
								echo "<script>console.log(".json_encode($pedido).");</script>";
?>
                            </td>
							<td>
                                <?php echo ($pedido->getIdVendaAPI() != "")? $pedido->getIdVendaAPI(): "Não possui";?>
                            </td>
                            <td>
<?php
                                if(is_array($pedido->getCesta()))
                                {
?>
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu<?php echo $ind;?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Itens <span class="caret"></span></button> 
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu<?php echo $ind;?>">
<?php
                                        foreach($pedido->getCesta() as $produto)
                                        {
                                            if($pedido->getTipoPagamento() == 'B2C')
                                                $produto = $GLOBALS['B2C_PRODUCT'][(string) $produto]['name'];
?>
                                            <li class="dropdown-header"><?php echo utf8_decode($produto); ?></li>
<?php
                                        }
?>
                                    </ul>
                                </div>
<?php
                                }
?>
                            </td>
                            <td><?php echo formata_data_ts($pedido->getDataInclusao(),0, true,true); ?></td>
                            <td>R$ <?php echo number_format($pedido->getValor(), 2, ',','.'); ?></td>
                            <td><!-- <image src="/images/icone_ag_processamento.png"> -->
                                <img src="/imagens/pdv/<?php echo $statusIcone?>" width="20" height="20" border="0" alt="<?php echo $statusDescricao;?>" title="<?php echo $statusDescricao; ?>">
                            </td>
                            <td>
<?php
                                if(!empty($operador)){
                                    echo $operador["ugo_nome"];
                                }
?>
                            </td>
                            <td>
<?php 
                            if($pedido->getTipoPagamento() != "B2C" && $pedido->getTipoPagamento() != "Recarga" && $controller->getBoolCuponsImpressao($pedido->getIdVenda()))
                            {
?>
                                <a href="#" class="detalhePedido naoEmitidos link_azul" pedido="<?php echo $pedido->getIdVenda(); ?>"><img title="Clique para emitir" alt="Clique para emitir" src="/imagens/icone_pins_naoemitidos.png"></a> 
<?php
                            }else {
?>
                                --
<?php 
                            }
?>
                            </td>
                       
                            <td>
							    <?php echo $controller->retornaQtdeGarena($pedido->getIdVenda()); ?>
							</td>
						
                            <td>
<?php 
                                if($pedido->getTipoPagamento() == "Boleto" || $pedido->getTipoPagamento() == "Depósito" || $pedido->getTipoPagamento() == 'Recarga Celular' || $pedido->getTipoPagamento() == 'B2C' || $pedido->getTipoPagamento() == 'Seguro')
                                {
                                    echo "";
                                }
                                else
                                {
?>
                                    <a class="detalhePedido" title="Clique para emitir" alt="Clique para emitir" href="#" pedido="<?php echo $pedido->getIdVenda(); ?>">Detalhes</a>
<?php
                                }
?>
                            </td>
                        </tr>
<?php 
                        }
                        
                    }
?>
                    </tbody>
                    </table>
<?php
                
                    if(!is_array($pedidos['vendas']) || (isset($_GET['nao_emitidos']) && $_GET['nao_emitidos'] == 1 && !isset($temRegistro)))
                    {
?>                  
                    <div class="col-md-12 col-xs-12-col-lg-12 col-sm-12 text-center txt-vermelho">
                            <p>Nenhum pedido foi encontrado.</p>
                    </div>
<?php
                    }
?>
                    <input type="hidden" id="tf_v_codigo_detalhe" name="tf_v_codigo_detalhe" value="">
                    <input type="hidden" id="nao_emitidos" name="nao_emitidos" value="<?php if(isset($_POST['nao_emitidos'])) echo $_POST['nao_emitidos']; ?>">
                </div>
                </form>
            </div>
            
<?php
                if($pedidos['qtd'] > $limit)
                {
                    $paginacao = Util::pagination($p, $limit, $pedidos['qtd'], $iptHidden);
                    require_once "includes/paginacao.php";
                }
?>
<?php 
            if(!isset($_GET['nao_emitidos']) || $_GET['nao_emitidos'] != 1)
            {
?>
            <div class="col-md-10 p-top10 hidden-xs hidden-sm">
                <div class="row txt-cinza">
                        <div class="col-md-12 espacamento">
                            <strong>Status / legenda</strong>
                        </div>
                </div>
                <div class="row top10 txt-cinza">
                    <div class="col-md-1">
                        <img src="/imagens/icone_ag_processamento.png">
                    </div>
                    <div class="col-md-11">
                        Pedido aguardando liberação
                    </div>
                </div>
                <div class="top10 row txt-cinza">
                    <div class="col-md-1">
                        <img src="/imagens/icone_vendacompleta.png">
                    </div>
                    <div class="col-md-11">
                        Venda processada
                    </div>
                </div>
                <div class="top10 bottom10 row txt-cinza">
                    <div class="col-md-1">
                        <img src="/imagens/icone_vendacancelada.png">
                    </div>
                    <div class="col-md-11">
                        Venda cancelada
                    </div>
                </div>
                <div class="top10 bottom10 row txt-cinza">
                    <div class="col-md-1">
                        <img src="/imagens/icone_pins_naoemitidos.png">
                    </div>
                    <div class="col-md-11">
                        Pedido com PINs não emitidos
                    </div>
                </div>
            </div>
<?php
            }
?>
        </div>
        <div class="col-md-2 col-lg-2 hidden-sm hidden-xs p-top10">
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
                date.setMonth(date.getMonth() + 8);
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
        tmpData.setMonth(tmpData.getMonth() + 8);
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
       
       if($(this).hasClass('naoEmitidos'))
           $("#nao_emitidos").val("1");
       
       $("#listaPedido").submit();
       
    });
    
});
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>