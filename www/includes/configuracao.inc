<?php
	$ambiente = 'producao';
	
	$ip_server = "www.prepag.com.br";
	$ip_boleto = "www.prepag.com.br";
	$domain_boleto = "www.prepag.com.br/";
	$domain_server = "www.prepag.com.br";
	$diretorio_atual = "prepag2";

	$url_session_expires   = $ip_server."/".$diretorio_atual."/extranet/login.php?SessionExpires=1";
	$url_blocked           = $ip_server."/".$diretorio_atual."/extranet/login.php?Blocked=1";

	$url_permission_denied = $ip_server."/mensagens/permission_denied.php";
	$url_access_denied     = $ip_server."/mensagens/access_denied.php";	
	
	$url_negado          = $ip_server."/prepag/amex/mensagens/negado.php";
	$url_negado          = $ip_server."/prepag/aut.htm";

// Valores genщricos
	$qtde_reg_tela = 20;
	$qtde_range_tela = 20;
	$qtde_reg_tela_estatistica = 20;
	$search_msg = "Pesquisa gerada em ";
	$search_unit = " segundos";
	$data_venda_alagoas = 01/12/2004;
	$query_cor1 = "#F5F5FB";
	$query_cor2 = "#FFFFFF";
		
// Boleto Bancсrio
	$boleto_valor_default = 50;
	$boleto_prazo_default = 30;
	$tf_qtde_dias_default = 5;
	$tf_qtde_dias_estab_default = 7;
			
// Valores Amex
	$amex_codigo = 81;

// Inclusуo de terminal
	$ter_codigo_inicial = '00000000';
	$ter_dv_inicial = 0;

// Inclusуo de estabelecimento
	$uf_insere = 'RJ';
	$municipio_insere = '3761';

// Tipo de Acesso
	$tipo_acesso_aux = 1;
	$tipo_acesso_amex = "PC";
	$tipo_acesso_esa = "ESA";
	
// Posiчуo das сreas na string
	$seg_admin            = 0;
	$seg_auxiliar         = 1;
	
	$seg_cadastramento    = 2;
	$seg_nao_afiliados    = 3;
	$seg_afiliados        = 4;
	$seg_qtde_estabs      = 5;
	$seg_forma_pagto      = 6;	
	$seg_emissao_boleto   = 7;	
	$seg_form_adesao      = 8;
	$seg_contrato_adesao  = 9;
	$seg_remuneracoes     = 10;
	$seg_estatistica      = 11;
	$seg_tabela_status    = 12;
	$seg_insere_term      = 13;
	$seg_compra_credito   = 14;
	$seg_consulta_saldo   = 15;
	
/* Estabelecimentos Status */
	$estab_pagto_pendente = '9';
	$estab_ativado = '6';
	$estab_habilitado = '10';
	$estab_cancelamento_solictado = '12';
	$estab_cancelado = '8';
	$estab_desistencia = '11';

// Estabelecimentos Etapas
	$estab_etapa_cadastro = 1;
	$estab_etapa_pagto_pendente = 2;
	$estab_etapa_ativado = 3;
	$estab_etapa_habilita = 4;
	$estab_etapa_cancelamento_solictado	= 7;
	$estab_etapa_cancelado	= 8;
	$estab_etapa_desistencia = 6;
	
// Operadora
	$operadora_ativada = 1;
	$opr_teste = 78;
	
?>