<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";

$posicao = "Inferior Internas";
$controller = new HeaderController;
$banners = $controller->getBanner($posicao);

$controller->setHeader();

//paginacao
$p = $_POST['p'];
if (!$p) $p = 1;
$registros = 20;
$registros_total = 0;

//Recupera usuario
if (isset($_SESSION['usuarioGames_ser']) && !is_null($_SESSION['usuarioGames_ser'])) {
    $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
    $usuarioId = $usuarioGames->getId();
}else{
    header("Location: /game/conta/login.php");
    die();
}

//Validacoes
$msg = "";
//Recupera as vendas
if ($msg == "") {

    if (array_key_exists('tf_v_codigo', $_POST)) {
        if (!empty($_POST['tf_v_codigo'])) {
            $tf_v_codigo = filter_input(INPUT_POST, 'tf_v_codigo');
        }
    }

    if (array_key_exists('tf_v_data_inclusao_ini', $_POST) && !empty($_POST['tf_v_data_inclusao_ini'])) {
        $tf_v_data_inclusao_ini = $_POST['tf_v_data_inclusao_ini'];
    }else{
        $tf_v_data_inclusao_ini = date("d/m/Y");
    }
    
    if (array_key_exists('tf_v_data_inclusao_fim', $_POST) && !empty($_POST['tf_v_data_inclusao_fim'])) {
        $tf_v_data_inclusao_fim = $_POST['tf_v_data_inclusao_fim'];
    }else{
        $tf_v_data_inclusao_fim = date("d/m/Y");
    }
    
//    $varsel = "&tf_v_codigo=$tf_v_codigo";
//    $varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";

    // $sql = "select 
    //             vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_usuario_obs, 
    //             sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, vg.vg_pagto_banco, vg.vg_integracao_parceiro_origem_id 
    //         from 
    //             tb_venda_games vg 
    //         inner join 
    //             tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
    //         where 
    //             vg.vg_ug_id=" . $usuarioId . " ";
    // if ($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and vg.vg_id=" . $tf_v_codigo;
    // if ($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim)
    //     if (verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
    //         $sql .= " and vg.vg_data_inclusao between '" . formata_data($tf_v_data_inclusao_ini, 1) . " 00:00:00' and '" . formata_data($tf_v_data_inclusao_fim, 1) . " 23:59:59'";

    // $sql .= "   group by 
    //                 vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_usuario_obs, vg.vg_pagto_banco, vg.vg_integracao_parceiro_origem_id 
    //             order by 
    //                 vg.vg_data_inclusao desc ";
      
    // //Inicializando conexao PDO
    // $con = ConnectionPDO::getConnection();
    // $pdo = $con->getLink();
    
    // //Tentando executar a Query de Insert
    // $rs_vendas = $pdo->prepare($sql);
// Montando a query com placeholders
        $sql = "SELECT 
                    vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_usuario_obs, 
                    SUM(vgm.vgm_valor * vgm.vgm_qtde) AS valor, SUM(vgm.vgm_qtde) AS qtde_itens, COUNT(*) AS qtde_produtos, vg.vg_pagto_banco, vg.vg_integracao_parceiro_origem_id 
                FROM 
                    tb_venda_games vg 
                INNER JOIN 
                    tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
                WHERE 
                    vg.vg_ug_id = :usuarioId ";

        // Verificando condições adicionais
        if ($tf_v_codigo && is_numeric($tf_v_codigo)) {
            $sql .= " AND vg.vg_id = :tf_v_codigo";
        }
        if ($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) {
            if (verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0) {
                $sql .= " AND vg.vg_data_inclusao BETWEEN :data_inclusao_ini AND :data_inclusao_fim";
            }
        }

        $sql .= " GROUP BY 
                        vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_usuario_obs, vg.vg_pagto_banco, vg.vg_integracao_parceiro_origem_id 
                    ORDER BY 
                        vg.vg_data_inclusao DESC";

        // Inicializando conexão PDO
        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();

        // Preparando a query de forma segura
        $rs_vendas = $pdo->prepare($sql);

        // Bind dos parâmetros para evitar SQL Injection
        $rs_vendas->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        if ($tf_v_codigo && is_numeric($tf_v_codigo)) {
            $rs_vendas->bindValue(':tf_v_codigo', $tf_v_codigo, PDO::PARAM_INT);
        }
        if ($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) {
            if (verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0) {
                $rs_vendas->bindValue(':data_inclusao_ini', formata_data($tf_v_data_inclusao_ini, 1) . " 00:00:00", PDO::PARAM_STR);
                $rs_vendas->bindValue(':data_inclusao_fim', formata_data($tf_v_data_inclusao_fim, 1) . " 23:59:59", PDO::PARAM_STR);
            }
        }
    if($rs_vendas->execute()){
        $num_rows = $rs_vendas->rowCount();
    }
          
    if (!$rs_vendas ||  $num_rows == 0) $msg = "Nenhuma venda encontrada.\n";

}

//Redireciona se ha algum dado invalido
//----------------------------------------------------
if ($msg != "") {
    $strRedirect = "/game/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Meus Pedidos") . "&link=" . urlencode("/prepag2/commerce/conta/lista_vendas.php");
    //redirect($strRedirect);
}


$arr_vendas = $rs_vendas->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top20">
        
            <div class="col-md-3 txt-azul-claro">
                <div class="row">
                    <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">Minha Conta</h4></strong>
                </div>
                <div class="row">
                    <?php require_once RAIZ_DO_PROJETO . "public_html/game/includes/menu-carteira.php"?>
                </div>
            </div>
            <div class="col-md-9 txt-azul-claro">
                <div class="row">
                    <strong class="pull-left p-left15 top20"><strong>MEUS PEDIDOS</strong></strong>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-12 espacamento">
                        <p class="margin004"><strong>Selecione o período ou número do pedido</strong></p>
                        <p class="margin004"><span class="fontsize-p">(Intervalo máximo de 6 meses)</span></p>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <form method="post">
                        <div class="col-md-2 leftright15">
                            <p>Data de início</p>
                            <p><input type="text" class="form-control" name="tf_v_data_inclusao_ini" id="tf_v_data_inclusao_ini" readonly="readonly" value="<?= htmlspecialchars($tf_v_data_inclusao_ini, ENT_QUOTES, 'UTF-8'); ?>"></p>
                        </div>
                        <div class="col-md-2 leftright15">
                            <p>Data final</p>
                            <p><input type="text" class="form-control" name="tf_v_data_inclusao_fim" id="tf_v_data_inclusao_fim" readonly="readonly" value="<?= htmlspecialchars($tf_v_data_inclusao_fim, ENT_QUOTES, 'UTF-8'); ?>"></p>
                        </div>
                        <div class="col-md-3 leftright15">
                            <p>Número do pedido</p>
                            <p><input name="tf_v_codigo" type="text" class="form-control" value="<?=$tf_v_codigo?>" maxlength="8"></p>
                        </div>
                        <div class="col-md-2 leftright15 ">
                            <button type="submit" class="top20 btn btn-success">Pesquisar</button>
                        </div>
                    </form>
                </div>
                <div class="row txt-cinza espacamento">
<?php               
                if($num_rows > 0){

                    foreach($arr_vendas as $ind => $rs_vendas_row){

                        if($rs_vendas_row['vg_ultimo_status'] == 6){
                            $corStatus = "txt-vermelho";

                        }else if($rs_vendas_row['vg_ultimo_status'] == 5){
                            $corStatus = "txt-verde";

                        }else{
                            $corStatus = "txt-laranja";

                        }
?>
                    <div class="hidden-lg hidden-md txt-preto espacamento">
                        <div class="row p-3 borda-fina">
                            <div class="col-sm-6 col-xs-6 borda-colunas-formas-pagamento">
                                <p>
                                    <a class="decoration-none detalhePedido" href="javascript:void(0);" title="Clique para emitir" alt="Clique para emitir" pedido="<?php echo $rs_vendas_row['vg_id'] ?>">
                                        <span class="glyphicon glyphicon-search"></span>
                                    </a>
                                </p>
                                <p class="bottom0">Pedido:</p>
                                <p class="txt-azul-claro text18">
                                    <strong><a class="decoration-none detalhePedido" href="#" title="Clique para emitir" alt="Clique para emitir" pedido="<?php echo formata_codigo_venda($rs_vendas_row['vg_id']) ?>"><?php echo formata_codigo_venda($rs_vendas_row['vg_id']) ?></a></strong>
                                </p>
                                <p>Pagamento</p>
                                <p>
<?php
                                $img_icone = "??";
                                $img_icone = getIconeParaPagtoGamer($rs_vendas_row['vg_pagto_tipo']);
                                $msg_icone = getDescricaoPagtoOnline($rs_vendas_row['vg_pagto_tipo']);

                                if ($img_icone) { 
?>
                                    <img src="<?php echo $img_icone ?>" border="0" title="<?php echo $msg_icone ?>">
<?php 
                                } else {
                                    echo "<span title='".$rs_vendas_row['vg_pagto_tipo'].", ".getIconeParaPagto($rs_vendas_row['vg_pagto_tipo'])."'>-</span>";
                                } 
?>
                                </p>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <p>&nbsp;</p>
                                <p class="bottom0">Data:</p>
                                <p class="text18"><strong><?php echo substr(formata_data_ts($rs_vendas_row['vg_data_inclusao'], 0, true, false),0,16); ?></strong></p>
                                <p class="bottom0">Valor:</p>
                                <p class="text18"><strong><?php echo number_format($rs_vendas_row['valor'], 2, ',', '.') ?></strong></p>
                            </div>
                        </div>
                        <div class="p-3 row">
                            <p class="<?php echo $corStatus;?>">Status: <?php echo $STATUS_VENDA_DESCRICAO_GAMER[$rs_vendas_row['vg_ultimo_status']] ?></p>
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
                            <th>Pedido</th>
                            <th>Data do pedido</th>
                            <th>Valor</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                            <th>&nbsp;</th>
                          </tr>
                        </thead>
                        <tbody>
<?php               
                        if($num_rows > 0){

                            foreach($arr_vendas as $ind => $rs_vendas_row){
?>
                            <tr class="trListagem">
                                <td>
                                    <a class="decoration-none detalhePedido" href="javascript:void(0);" title="Clique para emitir" alt="Clique para emitir" pedido="<?php echo $rs_vendas_row['vg_id'] ?>">
                                        <?php echo formata_codigo_venda($rs_vendas_row['vg_id']) ?>
                                    </a>
                                </td>
                                <td><?php echo formata_data_ts($rs_vendas_row['vg_data_inclusao'], 0, true, false) ?></td>
                                <td><?php echo number_format($rs_vendas_row['valor'], 2, ',', '.') ?></td>
<?php
                                $img_icone = "??";
                                $img_icone = getIconeParaPagtoGamer($rs_vendas_row['vg_pagto_tipo']);
                                $msg_icone = getDescricaoPagtoOnline($rs_vendas_row['vg_pagto_tipo']);
?>
                                <td>
<?php 
                                    if ($img_icone) { 
?>
                                        <img src="<?php echo $img_icone ?>" border="0" title="<?php echo $msg_icone ?>">
<?php 
                                    } else {
                                        echo "<span title='".$rs_vendas_row['vg_pagto_tipo'].", ".getIconeParaPagto($rs_vendas_row['vg_pagto_tipo'])."'>-</span>";
                                    } 
?>
                                </td>
                                <td>
                                    <img src="/imagens/gamer/<?php echo $STATUS_VENDA_ICONES_GAMER[$rs_vendas_row['vg_ultimo_status']] ?>" width="20" height="20" border="0" title="<?php echo $STATUS_VENDA_DESCRICAO_GAMER[$rs_vendas_row['vg_ultimo_status']] ?>">    	          	
                                </td>
                                <td>
                                    <a class="detalhePedido" title="Clique para emitir" alt="Clique para emitir" href="javascript:void(0);" pedido="<?php echo $rs_vendas_row['vg_id'] ?>"><span class="glyphicon glyphicon-zoom-in t0"></span></a>
                                </td>
                            </tr>
<?php 
                            } 
                        }else{
                            echo "<tr><td colspan=\"7\">$msg</td></tr>";
                        }
?>
                        </tbody>
                        </table>
                    </div>
                    <form action="/game/conta/detalhe-pedido.php" id="detalheVenda" method="post">
                            <input type="hidden" id="venda_id" name="venda_id" value="">
                    </form>
                </div>
                <div class="row espacamento hidden-xs hidden-sm">
                    <div class="row txt-cinza">
                            <div class="col-md-12 espacamento">
                                <strong>Status / legenda</strong>
                            </div>
                    </div>
<?php
                foreach($STATUS_VENDA_GAMER as $ind => $i){
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

                }//end for
?>
                </div>
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
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    $(".detalhePedido").click(function(){
        $("#venda_id").val($(this).attr("pedido"));
        $("#detalheVenda").submit();
    });
    
    var optDate = new Object();
        optDate.interval = 6;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
});
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";