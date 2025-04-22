<?php
        require_once '../../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";	
        $pos_pagina = isset($seg_auxilar) ? $seg_auxilar : null;
	$varsel = "&tf_data_inic=$tf_data_inic&tf_data_final=$tf_data_final&dd_banco=$dd_banco&tf_cod_documento=$tf_cod_documento&tf_documento=$tf_documento&dd_situacao=$dd_situacao&tf_valor=$tf_valor";

	$sql = "delete from boletos_pendentes where bol_codigo = " . $BolCod;
	pg_exec($connid, $sql);
	
	header("Location: pendentes.php?a=" . $varsel);
	

?>