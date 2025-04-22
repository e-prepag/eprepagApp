<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";

$posicao = "Inferior Internas";
$controller = new HeaderController;
$banners = $controller->getBanner($posicao);

$controller->setHeader();

require_once DIR_INCS ."inc_register_globals.php";

if(empty($data_inicio)) $data_inicio = date('d/m/Y');
if(empty($data_fim)) $data_fim = date('d/m/Y');

$sql = "
SELECT 
    vg_data_inclusao as data,
    vg_id as numero,
    vg_pagto_tipo as pagamento,
    vg_ultimo_status as status,
    vg_deposito_em_saldo_valor as valor, 
    vg_valor_eppcash as eppcash
FROM tb_venda_games
WHERE vg_ultimo_status != ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']." AND
        vg_deposito_em_saldo = 1 AND 
	vg_ug_id = ".$controller->usuario->getId()." AND
  	vg_data_inclusao >= '".Util::getData($data_inicio, true)." 00:00:00' AND
        vg_data_inclusao <= '".Util::getData($data_fim, true)." 23:59:59'      
ORDER BY vg_data_inclusao;
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
        optDate.interval = 6;
        setDateInterval('data_inicio','data_fim',optDate);
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
        <div class="col-md-12 espacamento">
            <?php require_once RAIZ_DO_PROJETO . "public_html/game/includes/menu-extrato.php"?>
            <form id="form1" name="form1" method="post">
                <div class="row txt-cinza">
                    <div class="col-md-12 top10">
                        <h4 class="margin004"><strong>Depósitos em processamento</strong></h4>
                        <p class="margin004"><strong>Selecione o Período</strong></p>
                        <p class="margin004"><span class="fontsize-p">(Intervalo máximo de 6 meses)</span></p>
                    </div>
                </div>
                <div class="row txt-cinza">
                     <div class="col-md-3 left10">
                        Data de início
                        <input type="text" class="form-control" readonly="readonly" value="<?php echo $data_inicio; ?>" name="data_inicio" id="data_inicio">
                    </div>
                    <div class="col-md-3 left10">
                        Data final
                        <input type="text" class="form-control" readonly="readonly" value="<?php echo $data_fim; ?>" name="data_fim" id="data_fim">
                    </div>
                    <div class="col-md-3 left10">
                        <input type="submit" name="processar" id="processar" class="top20 btn btn-info" value="Buscar">
                    </div>
                </div>
            </form>
<?php
if($qtde_registros > 0) {
?>            
            <div class="row txt-cinza espacamento">
<?php
            foreach($fetch as $key => $value){

                $img_icone = "??";
                $img_icone = getIconeParaPagtoGamer($value['pagamento']);
                $msg_icone = getDescricaoPagtoOnline($value['pagamento']);
                
                if($value['status'] == 6){
                    $corStatus = "txt-vermelho";

                }else if($value['status'] == 5){
                    $corStatus = "txt-verde";

                }else{
                    $corStatus = "txt-laranja";

                }
?>
                <div class="hidden-lg hidden-md txt-preto espacamento">
                    <div class="row p-3 borda-fina">
                        <div class="col-sm-6 col-xs-6 borda-colunas-formas-pagamento">
                            <p class="bottom0">Número:</p>
                            <p class="txt-azul-claro text18">
                                <strong><?php echo $value['numero']; ?></strong>
                            </p>
                            <p>Pagamento</p>
                            <p>
<?php 
                            if ($img_icone) { 
?>
                                <img src="<?php echo $img_icone ?>" class="img-responsive center-block" title="<?php echo $msg_icone ?>">
<?php 
                            } else {
                                echo "<span title='".$value['pagamento'].", ".getIconeParaPagtoGamer($value['pagamento'])."'>-</span>";
                            } 
?>
                            </p>
                        </div>
                        <div class="col-sm-6 col-xs-6">
                            <p class="bottom0">Data:</p>
                            <p class="text18"><strong><?php echo Util::getData($value['data']).substr($value['data'],10,9); ?></strong></p>
                            <p class="bottom0">Valor:</p>
                            <p class="text18">
                                <strong>R$ <?php echo number_format($value['valor'], 2, ",", "."); ?><br>
                                    <span class="txt-verde"><img src="/imagens/eppcash_mini.png" width="30" height="17" border="0" alt="EPPCash" title="EPPCash"><?php echo number_format($value['eppcash'], 0, ",", "."); ?></span>
                                </strong>
                            </p>
                        </div>
                    </div>
                    <div class="p-3 row">
                        <p class="<?php echo $corStatus;?>">Status: <?php echo $STATUS_VENDA_DESCRICAO_GAMER[$value['status']] ?></p>
                    </div>
                </div>
<?php
            }
?>
                <div class="col-md-12 bg-cinza-claro">
                    <table class="table bg-branco txt-preto text-center hidden-sm hidden-xs">
                    <thead>
                        <tr class="bg-cinza-claro">
                            <th class="text-center">Data</th>
                            <th class="text-right">Número</th>
                            <th class="text-center">Pagamento</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Valor</th>                            
                            <th class="text-right">EPPCash</th>
                      </tr>
                    </thead>
                    <tbody>
<?php
            foreach ($fetch as $key => $value) {

                        $img_icone = "??";
                        $img_icone = getIconeParaPagtoGamer($value['pagamento']);
                        $msg_icone = getDescricaoPagtoOnline($value['pagamento']);
?>
                        <tr class="trListagem">
                            <td class="nowrap text-center"><?php echo Util::getData($value['data']).substr($value['data'],10,9); ?></td>
                            <td class="text-right">
                                <?php echo $value['numero']; ?>
                            </td>
                            <td class="text-center">
<?php 
                            if ($img_icone) { 
?>
                                <img src="<?php echo $img_icone ?>" class="img-responsive center-block" title="<?php echo $msg_icone ?>">
<?php 
                            } else {
                                echo "<span title='".$value['pagamento'].", ".getIconeParaPagtoGamer($value['pagamento'])."'>-</span>";
                            } 
?>
                            </td>
                            <td class="text-center">
                                <img src="/imagens/gamer/<?php echo $STATUS_VENDA_ICONES_GAMER[$value['status']]; ?>" class="img-responsive h20 center-block" title="<?php echo $STATUS_VENDA_DESCRICAO_GAMER[$value['status']] ?>">    	          	
                            </td>
                            <td class="text-right">R$ <?php echo number_format($value['valor'], 2, ",", "."); ?></td>                            
                            <td class="text-right">
                                <?php echo number_format($value['eppcash'], 0, ",", "."); ?>
                            </td>
                        </tr>
<?php        
            }//end foreach
?>                        
                    </tbody>
                    </table>
                </div>
            </div>
            <div class="row espacamento">
                <div class="row txt-cinza">
                        <div class="col-md-12 espacamento">
                            <strong>Status / legenda</strong>
                        </div>
                </div> 
<?php
//            $contador = count($STATUS_VENDA_ICONES);
            foreach($STATUS_VENDA_GAMER as $ind => $i){
                if($i != $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
?>                
                <div class="row top10 txt-cinza">
                    <div class="col-md-1">
                        <img src="/imagens/gamer/<?php echo $STATUS_VENDA_ICONES_GAMER[$i]; ?>" width="20" height="20" border="0">
                    </div>
                    <div class="col-md-11">
                        <?php echo $STATUS_VENDA_DESCRICAO_GAMER[$i]; ?>
                    </div>
                </div>
<?php
                }//end $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']
                
            }//end for
?>
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
?>