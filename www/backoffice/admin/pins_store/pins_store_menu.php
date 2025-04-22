<center class="texto">
<?php
$sNomePagina = substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1,(strlen($_SERVER['PHP_SELF'])-strrpos($_SERVER['PHP_SELF'],'/')));

$MENU = array (
				'Hist&oacute;rico de PINs'		=> 'pins_store_historico.php',
				'Monitoramento'					=> 'pins_store_monitoramento.php',
				'Integra&ccedil;&atilde;o'		=> 'pins_store_integracao_atividade.php',
				);
if(b_IsBKOUsuarioAdminPINs()) {
	$MENU = array (
					'Estoque'						=> 'index.php',
					'Gerador PINs'					=> 'pins_store_gera.php',
					'Publicar PINs'					=> 'pins_store_publicar.php',
					'Listar PINs'					=> 'pins_store_lista.php',
					'Hist&oacute;rico de PINs'		=> 'pins_store_historico.php',
					'Relat&oacute;rio Financeiro'	=> 'pins_store_financeiro.php',
					'Comiss&atilde;o'				=> 'pins_store_comiss.php',
					'Monitoramento'					=> 'pins_store_monitoramento.php',
					'Integra&ccedil;&atilde;o'		=> 'pins_store_integracao_atividade.php',
					'Cancelar Pins'				=> 'altera_status_para_PINs_cancelados.php',
					);
}
if(b_IsBKOUsuarioAdminPINsFinanceiro()) {
	$MENU = array (
					'Estoque'						=> 'index.php',
					'Hist&oacute;rico de PINs'		=> 'pins_store_historico.php',
					'Monitoramento'					=> 'pins_store_monitoramento.php',
					'Integra&ccedil;&atilde;o'		=> 'pins_store_integracao_atividade.php',
					'Relat&oacute;rio Financeiro'	=> 'pins_store_financeiro.php',
					'Comiss&atilde;o'				=> 'pins_store_comiss.php',
					);
}
foreach($MENU as $key => $val) {
	if ($val == $sNomePagina) {
		echo " | ".$key;
	}
	else {
		echo " | <a href='".$val."'>".$key."</a>";
	}
}
echo " |";

?>
</center>
<br>