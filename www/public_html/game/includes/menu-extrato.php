<?php
    $controller->atualizaSessaoUsuario();
    if(isset($controller->logado) && $controller->logado === true){
?>
<div class="row">
    <div class="col-md-8 col-lg-8 col-xs-12 col-sm-12">
        <a href="<?php echo ($_SERVER['SCRIPT_NAME'] == "/game/conta/extrato.php") ? '#' : '/game/conta/extrato.php';?>" class="btn btn-info top10"><strong>Extrato</strong></a>
        <a href="<?php echo ($_SERVER['SCRIPT_NAME'] == "/game/conta/depositos-processamento.php") ? '#' : '/game/conta/depositos-processamento.php';?>" class="btn btn-info top10"><strong>Depósito em Processamento</strong></a>
        <a href="<?php echo ($_SERVER['SCRIPT_NAME'] == "/game/conta/add-saldo.php") ? '#' : '/game/conta/add-saldo.php';?>" class="btn btn-success top10"><strong>Adicionar Saldo</strong></a>
    </div>
    <div class="col-md-4 col-lg-4 col-xs-12 col-sm-12">
        <div class="hidden-md hidden-lg top20"></div>
        <div class="col-md-9 col-lg-9 col-xs-5 col-sm-5 p-right0 text-right-lg text-right-md text-left-sm text-left-xs">
            <img src="/imagens/icone_eppcash.png">
        </div>
        <div class="col-md-3 col-lg-3 col-xs-7 col-sm-7 p-left0 p-right0">
            <h5 class="txt-azul-claro p-left-3 bottom0 m-top0"><strong>Saldo</strong></h5>
            <h4 class="txt-verde p-left-3 m-top0"><strong><?php echo number_format(getEPPCash_from_Currency($controller->usuario->getPerfilSaldo()),0,',','.'); ?></strong></h4>
        </div>
    </div>
</div>
<?php
    }