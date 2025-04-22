<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php"
?>
<div class="col-md-6 top10">
    <div class="top10 lista bg-azul-claro txt-branco">
        <strong>Relatório de Vendas (Detalhado)</strong>
    </div>
    <ul class="nav nav-pills nav-stacked nav-pills-stacked-example"> 
        <li role="presentation"><a href="detalhado/vendas_estab_detalhado.php" class="menu">Por Estabelecimento</a></li> 
    </ul>
</div>
<div class="col-md-6 top10">
    <div class="top10 lista bg-azul-claro txt-branco">
        <strong>Relatório de Vendas (Agrupado)</strong>
    </div>
    <ul class="nav nav-pills nav-stacked nav-pills-stacked-example"> 
        <li role="presentation"><a href="agrupado/vendas_estab/pquery.php" class="menu">Por Estabelecimento</a></li> 
        <li role="presentation"><a href="agrupado/vendas_cid/pquery.php" class="menu">Por 
            Cidade</a></li> 
        <li role="presentation"><a href="agrupado/vendas_estab/vendas_estab_agrupado.php"class="menu">Por Estado</a></li> 
        <li role="presentation"><a href="agrupado/vendas_hora/vendas_hora.php" class="menu">Por Hora</a></li> 
        <li role="presentation"><a href="agrupado/vendas_dia/vendas_dia.php" class="menu">Por Dia</a></li> 
        <li role="presentation"><a href="agrupado/vendas_mes/vendas_mes.php" class="menu">Por M&ecirc;s</a></li> 
        <li role="presentation"><a href="agrupado/vendas_ano/vendas_ano.php" class="menu">Por Ano</a></li> 
    </ul>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";

