<?php

        session_start();

	require_once '../../../../includes/constantes.php';
        require_once $raiz_do_projeto."includes/inc_register_globals.php";
        require_once $raiz_do_projeto."includes/access_functions.php";
        require_once $raiz_do_projeto.'includes/configIP.php';
        require_once $raiz_do_projeto."includes/configuracaoBO.php";
        
        $pos_pagina = $seg_auxiliar;
        
        require_once $raiz_do_projeto."db/connect.php";
        require_once $raiz_do_projeto."db/ConnectionPDO.php";
        require_once $raiz_do_projeto."includes/header.php";
        require_once $raiz_do_projeto."includes/functions.php";
        
	$varsel = "&dd_codigo=$dd_codigo&tf_data_inic=$tf_data_inic&tf_data_final=$tf_data_final&dd_banco=$dd_banco&tf_cod_documento=$tf_cod_documento";
	$varsel .= "&tf_documento=$tf_documento&dd_situacao=$dd_situacao&tf_valor_oper=$tf_valor_oper&tf_valor=$tf_valor&tf_valor2=$tf_valor2";
	$varsel .= "&dd_agencia=$dd_agencia&dd_conta=$dd_conta";

	$sql = "delete from depositos_pendentes where dep_codigo = ".$DepCod."";
	pg_exec($connid, $sql);
        
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
	
	header("Location: pendentes.php?" . $varsel);
	

?>