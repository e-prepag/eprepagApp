<?php 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
include $raiz_do_projeto."includes/main.php";

$BtnSearch                          = $_REQUEST['BtnSearch'] ?? null;

$tf_u_codigo                        = $_REQUEST['tf_u_codigo'] ?? null;
$tf_u_codigo_include                = $_REQUEST['tf_u_codigo_include'] ?? null;
$tf_u_status                        = $_REQUEST['tf_u_status'] ?? null;

$tf_u_qtde_acessos_ini               = $_REQUEST['tf_u_qtde_acessos_ini'] ?? null;
$tf_u_qtde_acessos_fim               = $_REQUEST['tf_u_qtde_acessos_fim'] ?? null;

$tf_u_data_ultimo_acesso_ini         = $_REQUEST['tf_u_data_ultimo_acesso_ini'] ?? null;
$tf_u_data_ultimo_acesso_fim         = $_REQUEST['tf_u_data_ultimo_acesso_fim'] ?? null;

$tf_u_data_inclusao_ini              = $_REQUEST['tf_u_data_inclusao_ini'] ?? null;
$tf_u_data_inclusao_fim              = $_REQUEST['tf_u_data_inclusao_fim'] ?? null;

$tf_u_news                          = $_REQUEST['tf_u_news'] ?? null;
$ug_cadastro_completo                = $_REQUEST['ug_cadastro_completo'] ?? null;

$tf_u_endereco_ip                    = $_REQUEST['tf_u_endereco_ip'] ?? null;
$tf_u_observacoes                    = $_REQUEST['tf_u_observacoes'] ?? null;

$tf_u_email                          = $_REQUEST['tf_u_email'] ?? null;
$tf_u_nome                           = $_REQUEST['tf_u_nome'] ?? null;
$tf_u_cpf                            = $_REQUEST['tf_u_cpf'] ?? null;
$ug_login                            = $_REQUEST['ug_login'] ?? null;

$tf_u_data_nascimento_ini            = $_REQUEST['tf_u_data_nascimento_ini'] ?? null;
$tf_u_data_nascimento_fim            = $_REQUEST['tf_u_data_nascimento_fim'] ?? null;

$tf_u_sexo                           = $_REQUEST['tf_u_sexo'] ?? null;

$tf_u_tel_ddi                        = $_REQUEST['tf_u_tel_ddi'] ?? null;
$tf_u_tel_ddd                        = $_REQUEST['tf_u_tel_ddd'] ?? null;
$tf_u_tel                            = $_REQUEST['tf_u_tel'] ?? null;

$tf_u_cel_ddi                        = $_REQUEST['tf_u_cel_ddi'] ?? null;
$tf_u_cel_ddd                        = $_REQUEST['tf_u_cel_ddd'] ?? null;
$tf_u_cel                            = $_REQUEST['tf_u_cel'] ?? null;

$tf_u_endereco                       = $_REQUEST['tf_u_endereco'] ?? null;
$tf_u_bairro                         = $_REQUEST['tf_u_bairro'] ?? null;
$tf_u_cidade                         = $_REQUEST['tf_u_cidade'] ?? null;
$tf_u_cep                            = $_REQUEST['tf_u_cep'] ?? null;
$tf_u_estado                         = $_REQUEST['tf_u_estado'] ?? null;

$tf_u_compet_aceito_regulamento      = $_REQUEST['tf_u_compet_aceito_regulamento'] ?? null;
$tf_u_integracao_origem              = $_REQUEST['tf_u_integracao_origem'] ?? null;

$tf_u_habilitado_cielo               = $_REQUEST['tf_u_habilitado_cielo'] ?? null;
$tf_u_usuario_vip                    = $_REQUEST['tf_u_usuario_vip'] ?? null;
$tf_u_com_totais_vendas            = $_REQUEST['tf_u_com_totais_vendas'] ?? null;

// Decodifica cidade se necessário
if (isset($tf_u_cidade)) {
    $tf_u_cidade = utf8_decode($tf_u_cidade);
}

// Gera a query SQL com base nos filtros
include $raiz_do_projeto."includes/gamer/inc_pesquisa_usuarios_sql.php";

ob_start();

$tf_tipo = "u";
$filename = $tf_tipo . "_" . date("Ymd_His") . ".txt";
header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . strlen($mensagem));

// Cabeçalho da planilha
if ($tf_u_com_totais_vendas) {
    echo "ID\tNome\tLogin\tEMail\tData de Cadastro\tCPF\tSexo\tEndereco\tBairro\tCidade\tEstado\tCEP\tVendas R$\tn Vendas\tTicket medio\tData ultima venda\tStatus\tData de Nascimento\tSaldo Atual\n\n";
} else {
    echo "ID\tNome\tLogin\tEMail\tData de Cadastro\tCPF\tSexo\tEndereco\tBairro\tCidade\tEstado\tCEP\tData de Nascimento\tSaldo Atual\n\n";
}

// Monta o conteúdo
while ($rs_usuario_row = pg_fetch_array($rs_usuario)) {
    $ug_id      = $rs_usuario_row['ug_id'];
    $ug_nome    = $rs_usuario_row['ug_nome'];
    $ug_email   = $rs_usuario_row['ug_email'];
    $ug_data_inclusao = substr($rs_usuario_row['ug_data_inclusao'], 0, 10);
    $ug_cpf     = $rs_usuario_row['ug_cpf'];
    $ug_login   = $rs_usuario_row['ug_login'];
    $ug_sexo    = $rs_usuario_row['ug_sexo'];
    $ug_endereco= $rs_usuario_row['ug_endereco'] . "," . $rs_usuario_row['ug_numero'] . " " . $rs_usuario_row['ug_complemento'];
    $ug_bairro  = $rs_usuario_row['ug_bairro'];
    $ug_cidade  = $rs_usuario_row['ug_cidade'];
    $ug_estado  = $rs_usuario_row['ug_estado'];
    $ug_cep     = $rs_usuario_row['ug_cep'];
    $ug_data_nascimento = substr($rs_usuario_row['ug_data_nascimento'], 0, 10);
    $ug_perfil_saldo = $rs_usuario_row['ug_perfil_saldo'];

    if ($tf_u_com_totais_vendas) {
        $vg_valor = number_format($rs_usuario_row['vg_valor'], 2, '.', '.');
        $vg_qtde_itens = ($rs_usuario_row['vg_qtde_itens'] > 0) ? $rs_usuario_row['vg_qtde_itens'] : 1;
        $ticket_medio = number_format($rs_usuario_row['vg_valor'] / $vg_qtde_itens, 2, '.', '.');
        $vg_data_ultima_venda = substr($rs_usuario_row['vg_data_ultima_venda'], 0, 19);

        $dias = qtde_dias(substr($rs_usuario_row['vg_data_ultima_venda'], 8, 2) . "-" . substr($rs_usuario_row['vg_data_ultima_venda'], 5, 2) . "-" . substr($rs_usuario_row['vg_data_ultima_venda'], 0, 4), date('d-m-Y'));
        if ($dias <= 15) {
            $status_label = "Frequente";
        } elseif ($dias <= 30) {
            $status_label = "Abandonou";
        } else {
            $status_label = "Atrasado";
        }

        echo "$ug_id\t$ug_nome\t$ug_login\t$ug_email\t$ug_data_inclusao\t$ug_cpf\t$ug_sexo\t$ug_endereco\t$ug_bairro\t$ug_cidade\t$ug_estado\t$ug_cep\t$vg_valor\t$vg_qtde_itens\t$ticket_medio\t$vg_data_ultima_venda\t$status_label\t$ug_data_nascimento\t$ug_perfil_saldo\n";
    } else {
        echo "$ug_id\t$ug_nome\t$ug_login\t$ug_email\t$ug_data_inclusao\t$ug_cpf\t$ug_sexo\t$ug_endereco\t$ug_bairro\t$ug_cidade\t$ug_estado\t$ug_cep\t$ug_data_nascimento\t$ug_perfil_saldo\n";
    }
}

ob_end_flush();
exit;

?>