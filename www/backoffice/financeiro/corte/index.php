<?php

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/pdv/corte_classPrincipal.php"; ?>
<div class="col-md-12">
    <div class="top10 lista bg-azul-claro text-center txt-branco">
            <strong>Corte Semanal</strong>
    </div>
</div>
<div class="col-md-6 top10">
    <div class="top10 lista bg-azul-claro txt-branco">
            <strong>Cortes</strong>
    </div>
    <ul class="nav nav-pills nav-stacked nav-pills-stacked-example"> 
        <li role="presentation"><a href="corte_consulta_informa_estab.php" class="menu">Consulta Corte Semanal</a></li>
        <li role="presentation"><a href="corte_corta_semana.php" class="menu">Corte Semanal Manual</a></li>
    </ul>
</div>
<div class="col-md-6 top10">
    <div class="top10 lista bg-azul-claro txt-branco">
        <strong>Boletos</strong>
    </div>
    <ul class="nav nav-pills nav-stacked nav-pills-stacked-example"> 
        <li role="presentation"><a href="corte_pesquisa_boletos.php" class="menu">Pesquisa boletos</a></li>
        <li role="presentation"><a href="corte_pesquisa_boletos_pendentes.php" class="menu">Pesquisa de Boletos Pendentes (Remessas do banco)</a></li>
        <li role="presentation"><a href="corte_boleto_remessas.php" class="menu">Boletos - Remessa e Retorno</a></li>
    </ul>
    <div class="top10 lista bg-azul-claro txt-branco">
        <strong>Relatórios</strong>
    </div>
    <ul class="nav nav-pills nav-stacked nav-pills-stacked-example"> 
        <li role="presentation"><a href="corte_pesquisa.php" class="menu">Pesquisa Cortes</a></li>
    </ul>
</div>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</body></html>
