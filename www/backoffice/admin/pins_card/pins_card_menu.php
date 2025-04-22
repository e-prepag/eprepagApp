<center class="texto">
<?php
require_once $raiz_do_projeto . "includes/access_functions.php";

$sNomePagina = substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1,(strlen($_SERVER['PHP_SELF'])-strrpos($_SERVER['PHP_SELF'],'/')));

$MENU = array (
				'Hist&oacute;rico de PINs'		=> 'pins_card_historico.php',
				'Monitoramento'				=> 'pins_card_monitoramento.php',
				'Integra&ccedil;&atilde;o'		=> 'pins_card_integracao_atividade.php',
				'Comunicação com Distribuidor'		=> 'pins_card_consulta_distribuidor.php',
				);
if(b_IsBKOUsuarioAdminPINs()) {
	$MENU = array (
					'Estoque'			=> 'index.php',
					'Gerador PINs'			=> 'pins_card_gera.php',
					'Publicar PINs'			=> 'pins_card_publicar.php',
					'Listar PINs'			=> 'pins_card_lista.php',
					'Hist&oacute;rico de PINs'	=> 'pins_card_historico.php',
					'Relat&oacute;rio Financeiro'	=> 'pins_card_financeiro.php',
					'Comiss&atilde;o'		=> 'pins_card_comiss.php',
					'Monitoramento'			=> 'pins_card_monitoramento.php',
					'Integra&ccedil;&atilde;o'	=> 'pins_card_integracao_atividade.php',
					'Comunicação com Distribuidor'	=> 'pins_card_consulta_distribuidor.php',
					);
}
if(b_IsBKOUsuarioAdminPINsFinanceiro()) {
	$MENU = array (
					'Estoque'			=> 'index.php',
					'Hist&oacute;rico de PINs'	=> 'pins_card_historico.php',
					'Monitoramento'			=> 'pins_card_monitoramento.php',
					'Integra&ccedil;&atilde;o'	=> 'pins_card_integracao_atividade.php',
					'Relat&oacute;rio Financeiro'	=> 'pins_card_financeiro.php',
					'Comiss&atilde;o'		=> 'pins_card_comiss.php',
					'Comunicação com Distribuidor'	=> 'pins_card_consulta_distribuidor.php',
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