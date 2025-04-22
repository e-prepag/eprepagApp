<?php
//Ambiente
	$ambiente = 'producao';

//Versуo
	$major_version = 3;
	$minor_version = 0;
	$release = 0;
	$build = 0;
        
        date_default_timezone_set('America/Fortaleza');
        
        $strVersao = $major_version;

        $strVersao .= ".".$minor_version;

        $strVersao .= ".".$release;

        $strVersao .= ".".$build;        


        $https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
// URL's para redirecionamento
	$url_session_expires = $https.'://'.$_SERVER['HTTP_HOST'].'/sys/admin/index.php?SessionExpires=1';
	$url_user_blocked = $https.'://'.$_SERVER['HTTP_HOST'].'/sys/admin/index.php?UserBlocked=1';
	$url_user_denied = $https.'://'.$_SERVER['HTTP_HOST'].'/sys/admin/mensagens/negado.php';
	
//Inicio das Operaчѕes 
	$inic_oper_data = '01/01/2008';
	$inic_oper_ano = '2008';
	$inic_oper_msg = 'Data do inэcio das Operaчѕes: ';

// Valores de Query's
	$qtde_reg_tela = 20;
	$qtde_range_tela = 20;
	$search_msg = "Pesquisa gerada em ";
	$search_unit = " segundos";
	$refresh_time = 600;
	$query_cor1 = "#F5F5FB";
	$query_cor2 = "#FFFFFF";

/* Valores de Teste */
	$opr_teste = 78;
	$estab_teste_all = 1;

/* Status */
	$operadora_ativada = 1;

// Pagamentos
	$data_emissao_aberto = '14/10/2004';

/* Valores de Tela */
	$main_table_width = 1050;

/* Bancos */
	$banco_codigo_bradesco = "237";
	$banco_codigo_caixa_economica = "104";
	$bradesco_codigo_bdn = "00351";
	$banco_codigo_banco_do_brasil = "001";

/* Lista de Bancos */
	$LISTA_BANCOS[0] = array("001", "Banco do Brasil");
	$LISTA_BANCOS[1] = array("237", "Bradesco");
	$LISTA_BANCOS[2] = array("104", "Caixa Econєmica");


/* Pedidos */
	$pedido_valor_minimo = 10;
	$pedido_valor_maximo_porcent = 2;
	$pedido_valor_maximo_base = 200;

/* Posiчуo das сreas na string */
	$seg_admin            = 0;
	$seg_auxiliar         = 1;
?>