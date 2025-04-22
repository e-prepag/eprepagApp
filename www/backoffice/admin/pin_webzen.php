<?php 
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<table class="table txt-preto">
	<tr> 
		<td valign="top">
            <table width="894" border="0" cellpadding="0" cellspacing="2">
                <tr>
                    <td>
				    - <a href="pin_retirados.php?mailing_number=3">Teste com exclusividade o novo game Webzen: R2 Online (3)</a><br>
				    - <a href="pin_retirados.php?mailing_number=4">Teste com exclusividade o novo game Webzen: R2 Online (4) - 2011-07-20</a><br>
					</td>
                </tr>
            </table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>