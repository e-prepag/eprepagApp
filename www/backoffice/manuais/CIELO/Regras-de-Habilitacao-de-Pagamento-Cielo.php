<?php 
require_once "../../../includes/constantes.php";
require_once RAIZ_DO_PROJETO . "backoffice/includes/topo.php";
require_once RAIZ_DO_PROJETO . "backoffice/manuais/includes/navegacao.php";
?>
<a href='/manuais/CIELO/Regras-de-Habilitacao-de-Pagamento-Cielo.pdf' download='Regras-de-Habilitacao-de-Pagamento-Cielo' class='btn btn-info'>Download</a><div class='row top10'>
<div class='col-md-12'>
<iframe style='border:1px solid #666CCC' title='Regras de Habilitação de Pagamento Cielo' src='<?php echo basename(__FILE__, ".php") ?>.pdf' frameborder='1' scrolling='auto' height='1100' width='850' ></iframe>
</div>
</div>
<?php
require_once RAIZ_DO_PROJETO . "backoffice/includes/rodape_bko.php";
?>
