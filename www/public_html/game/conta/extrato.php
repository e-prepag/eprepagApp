<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";

$posicao = "Inferior Internas";
$controller = new HeaderController;
$banners = $controller->getBanner($posicao);

$controller->setHeader();

require_once DIR_INCS ."inc_register_globals.php";

if(!is_object($controller->usuario) || !$controller->usuario || empty($controller->usuario->getId())){
    header("Location: /game/conta/login.php");
    die();
}

if(empty($data_inicio) || !Util::checkValidDate($data_inicio)) 
    $data_inicio = date('d/m/Y');
if(empty($data_fim) || !Util::checkValidDate($data_fim)) 
    $data_fim = date('d/m/Y');

$label_deposito = "DEPÓSITO";
$label_compra = "PEDIDO";

$sql = "
SELECT
	data,
	transacao,
	numero,
	credito,
	debito,
	saldo
FROM (
	-- Taxas Anuais
	(
	SELECT 
		pta_data as data,
		'TAXA ANUAL' as transacao,
		pta_id as numero,
		0 as credito,
		pta_valor as debito,
		(pta_valor_total-pta_valor) as saldo
	FROM tb_pag_taxa_anual
	WHERE ug_id = ".$controller->usuario->getId()." AND
		pta_data >= '".Util::getData($data_inicio, true)." 00:00:00' AND
		pta_data <= '".Util::getData($data_fim, true)." 23:59:59'
	)
	
	UNION ALL

	-- Estornos
	(
	SELECT
		tpe_data as data,
		'ESTORNO' as transacao,
		tpe_id as numero,
		0 as credito,
		tpe_valor as debito,
		0 as saldo
	FROM tb_pag_estorno 
	WHERE ug_id = ".$controller->usuario->getId()." AND
		tpe_data >= '".Util::getData($data_inicio, true)." 00:00:00' AND
		tpe_data <= '".Util::getData($data_fim, true)." 23:59:59'
	)
	
	UNION ALL

	-- Depósitos
	(
	SELECT
		scf_data_deposito as data,
		'".$label_deposito."' as transacao,
		vg_id as numero,
		scf_valor as credito,
		0 as debito,
		0 as saldo
	FROM saldo_composicao_fifo
	WHERE ug_id = ".$controller->usuario->getId()." AND
		scf_data_deposito >= '".Util::getData($data_inicio, true)." 00:00:00' AND
		scf_data_deposito <= '".Util::getData($data_fim, true)." 23:59:59'
	)
	
	UNION ALL

	-- Compras / Pedidos
	(
	SELECT
		vg_pagto_data as data,
		'".$label_compra."' as transacao,
		scfu.vg_id as numero,
		0 as credito, 
		SUM(scfu_valor) as debito, 
		0 as saldo
	FROM saldo_composicao_fifo_utilizado scfu
		INNER JOIN tb_venda_games vg ON vg.vg_id = scfu.vg_id
	WHERE vg_ug_id = ".$controller->usuario->getId()." AND
		vg_pagto_data >= '".Util::getData($data_inicio, true)." 00:00:00' AND
		vg_pagto_data <= '".Util::getData($data_fim, true)." 23:59:59'
	GROUP BY scfu.vg_id,vg_pagto_data
	)

     ) AS extrato
ORDER BY data;  
";

//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();
$stmt = $pdo->prepare($sql);
$stmt->execute();
$fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Verificando total de registros
$qtde_registros = count($fetch);

?>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
    optDate.interval = 1;
    setDateInterval('data_inicio','data_fim',optDate);

    $(".detalhePedido").click(function(){
        $("#venda_id").val($(this).attr("pedido"));
        $('#listaPedido').attr('action', $(this).attr("programa"));
        $("#listaPedido").submit();
    });

});
</script>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top20">
        <div class="row">
            <div class="col-md-3 txt-azul-claro">
                <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">Cartão E-Prepag</h4></strong>
            </div>
        </div>
        <?php //include "../includes/menu-carteira.php"?>
        <div class="col-md-12 col-lg-12 col-xs-12 col-sm-12 espacamento">
            <?php require_once RAIZ_DO_PROJETO . "public_html/game/includes/menu-extrato.php"?>
            <form id="form1" name="form1" method="post" style="min-width: 680px !important;">
                <div class="row txt-cinza">
                    <div class="col-md-12 top10">
                        <h4 class="margin004"><strong>Extrato</strong></h4>
                        <p class="margin004"><strong>Selecione o Período</strong></p>
                        <p class="margin004"><span class="fontsize-p">(Intervalo máximo de 30 dias)</span></p>
                    </div>
                </div>
                <div class="row txt-cinza">
                     <div class="col-md-2 top10 col-xs-3 col-sm-3 col-lg-2 left10">
                        Data de início
                        <input type="text" class="form-control w100p data" readonly="readonly" value="<?php echo $data_inicio; ?>" name="data_inicio" id="data_inicio">
                    </div>
                    <div class="col-md-2 col-xs-3 col-sm-3 top10 col-lg-2 left10">
                        Data final
                        <input type="text" class="form-control w100p data" readonly="readonly" value="<?php echo $data_fim; ?>" name="data_fim" id="data_fim">
                    </div>
                    <div class="col-md-2 col-xs-12 col-sm-12 top10 col-lg-2 left10">
                        <input type="submit" name="processar" id="processar" class="top20 btn btn-info" value="Buscar">
                    </div>
                </div>
            </form>
<?php
if($qtde_registros > 0) {
?>            
            <form method="POST" id="listaPedido" name="listaPedido" action="">
                <input type="hidden" id="venda_id" name="venda_id" value="">
            </form>
            <div class="row txt-cinza espacamento" style="min-width: 680px !important;">
                <div class="col-md-12 col-lg-12 col-xs-12 col-sm-12 bg-cinza-claro">
                    <table class="table bg-branco txt-preto">
                        <thead>
                            <tr class="bg-cinza-claro">
                                <th class="text-center">Data</th>
                                <th class="text-center">Transação</th>
                                <th class="text-right">Número</th>
                                <th class="txt-verde text-right">Crédito</th>
                                <th class="txt-vermelho text-right">Débito</th>
                                <th class="text-right">Saldo</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
                        foreach ($fetch as $key => $value) {
?>
                            <tr class="trListagem">
                                <td align="center"><?php echo Util::getData($value['data']).substr($value['data'],10,9); ?></td>
                                <td class="text-center"><?php echo $value['transacao']; ?></td>
                                <td class="text-right">
<?php
                                if($value['transacao'] == $label_deposito || $value['transacao'] == $label_compra) { 
?>                                    
                                    <a href="javascript:void(0);" class="decoration-none detalhePedido" title="Ver detalhes" alt="Ver detalhes" programa="/game/<?php echo ($value['transacao'] == $label_deposito)?"conta/detalhe-deposito.php":"carteira/detalhe-pedido.php"; ?>" pedido="<?php echo $value['numero']; ?>">
<?php 
                                }//end if($value['transacao'] == $label_deposito || $value['transacao'] == $label_compra)
                                echo $value['numero']; 
                                if($value['transacao'] == $label_deposito || $value['transacao'] == $label_compra) {
?> 
                                    </a> 
<?php 
                                }//end if($value['transacao'] == $label_deposito || $value['transacao'] == $label_compra)
?>                                    
                                </td>
                                <td class="txt-verde text-right"><?php echo ($value['credito'] > 0)?number_format(getEPPCash_from_Currency($value['credito']),0,',','.'):""; ?></td>
                                <td class="txt-vermelho text-right"><?php echo ($value['debito'] > 0)?number_format(getEPPCash_from_Currency($value['debito']),0,',','.'):""; ?></td>
                                <td class="text-right">
<?php 
                                if($value['saldo'] > 0) {
                                    echo number_format(getEPPCash_from_Currency($value['saldo']),0,',','.');
                                }//end if($value['debito'] > 0) 
                                else {
                                    $sql = " 
                                        SELECT ugsl_ug_perfil_saldo 
                                        FROM usuarios_games_saldo_log 
                                        WHERE ugsl_data_inclusao >= '".$value['data']."'::timestamp - '1 second'::interval 
                                          AND ugsl_data_inclusao <= '".$value['data']."'::timestamp + '1 second'::interval 
                                          AND ugsl_ug_id = ".$controller->usuario->getId().";";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();
                                    $fetchSaldo = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    if(is_array($fetchSaldo)) echo number_format(getEPPCash_from_Currency($fetchSaldo[0]['ugsl_ug_perfil_saldo']),0,',','.');
                                } //end else do if($value['debito'] > 0) 
?>                                    
                                </td>
                                <td>
<?php 
                                if($value['transacao'] == $label_deposito || $value['transacao'] == $label_compra) {
?>                                    
                                    <a href="javascript:void(0);" class="decoration-none detalhePedido" href="detalhe-deposito.php" title="Ver detalhes" alt="Ver detalhes" programa="/game/<?php echo ($value['transacao'] == $label_deposito)?"conta/detalhe-deposito.php":"carteira/detalhe-pedido.php"; ?>" pedido="<?php echo $value['numero']; ?>"><span class="glyphicon glyphicon-zoom-in t0"></span></a>
<?php 
                                }//end if($value['transacao'] == $label_deposito || $value['transacao'] == $label_compra)
?>                                    
                                </td>
                            </tr>
<?php        
                        }//end foreach
?>                        
                        </tbody>
                    </table>
                </div>
            </div>
<?php
} //end if($qtde_registros > 0) 
else {
?>
            <div class="text-center txt-vermelho top50">
                Nenhuma transação no período informado.
            </div>
<?php                    
}//end else do if($qtde_registros > 0) 
?>
        </div>
    </div>
<?php
    if(!empty($banners)){
?>
    <div class="col-md-12 top10">
        <a href='<?php echo $banners[0]->link; ?>' target="_blank">
            <img title="<?php echo $banners[0]->titulo; ?>" alt="<?php echo $banners[0]->titulo; ?>" class="img-responsive" src="<?php echo $controller->objBanners->urlLink.$banners[0]->imagem; ?>">
        </a>
    </div>
<?php 
    } 
?>
</div>
</div>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";