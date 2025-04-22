<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classEmailAutomatico.php";
require_once $raiz_do_projeto . "includes/functions.php";

$EnvioEmailAutomatico = new EnvioEmailAutomatico();

$vetor_identificador = $EnvioEmailAutomatico->getVetorIdentificacao();
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
<table class="table txt-preto fontsize-pp">
	<tr>
	  <td class="texto" align="center" colspan="5">
		(templates em <?php echo $raiz_do_projeto; ?>includes/templates/<br>
		classe <?php echo $raiz_do_projeto; ?>class/classEmailAutomatico.php)
	  </td>
	</tr>
	<tr>
	  <td class="texto" align="center" colspan="5"><nobr>
			&nbsp;Identificador de E-mail: &nbsp;
			<select name="ee_identificador" id="ee_identificador" class="form">
				<option value="" <?php  if($ee_identificador == "") echo "selected" ?>>Selecione</option>
				<?php foreach ($vetor_identificador as $key => $value) { ?>
				<option value="<?php echo $value ?>" <?php if($value == $ee_identificador) echo "selected" ?>><?php echo $value ?></option>
				<?php } ?>
			</select>
		  </nobr>
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="5"><input type="submit" name="btPesquisar" id="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
	  </td>
	</tr>
</table>
</form>
<?php
if(empty($ee_identificador)) {
	$tipo = null;
}
else {
	$tipo = $ee_identificador;
}
echo $EnvioEmailAutomatico->getCorpoEmailTodos($tipo);
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>
