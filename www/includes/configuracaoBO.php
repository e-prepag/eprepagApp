<?php
//Charset
header("Content-type: text/html; charset=ISO-8859-1 ");

//Ambiente
$ambiente = 'producao';

$server_url = 'www.e-prepag.com.br';
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}

//Versão
$major_version = 3;
$minor_version = 0;
$release = 0;
$build = 0;

// URL's para redirecionamento
$url_session_expires = 'https://'.$_SERVER['HTTP_HOST'].'/login.php?SessionExpires=1';
$url_user_blocked = 'https://'.$_SERVER['HTTP_HOST'].'/login.php?UserBlocked=1';
$url_user_denied = 'https://'.$_SERVER['HTTP_HOST'].'/mensagens/negado.php';

//Inicio das Operações
$inic_oper_data = '29/08/2003';
$inic_oper_ano = '2003';
$inic_oper_msg = 'Data do início das Operações: ';

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
$LISTA_BANCOS[2] = array("104", "Caixa Econômica");


/* Pedidos */
$pedido_valor_minimo = 10;
$pedido_valor_maximo_porcent = 2;
$pedido_valor_maximo_base = 200;

/* Posição das áreas na string */
$seg_admin            = 0;
$seg_auxiliar         = 1;
?>
