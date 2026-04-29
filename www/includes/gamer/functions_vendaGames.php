<?php
$cReturn = PHP_EOL;
$remoteAddr = $_SERVER["REMOTE_ADDR"] ?? "";

// Livrodjx has been here
if ($remoteAddr == "201.93.162.169") {
        /*ini_set('display_errors', 1);
               //ini_set('display_startup_errors', 1);
               //error_reporting(E_ALL);*/
        /*require_once "/www/includes/constantes.php";
               require_once "/www/includes/constantesPagamento.php";
               require_once "/www/includes/gamer/constantes.php";
               require_once "/www/includes/main.php";
               require_once "/www/includes/inc_Pagamentos.php";
               require_once "/www/includes/functions.php";*/
        //require_once "/www/includes/gamer/functions.php";

}
require_once RAIZ_DO_PROJETO . 'db/connect.php';
require_once RAIZ_DO_PROJETO . 'db/ConnectionPDO.php';
require_once RAIZ_DO_PROJETO . 'includes/gamer/inc_sanitize.php';
require_once RAIZ_DO_PROJETO . 'includes/gamer/chave.php';
require_once RAIZ_DO_PROJETO . 'includes/gamer/AES.class.php';
require_once RAIZ_DO_PROJETO . 'class/classGeraPin.php';

if (!function_exists('writeLogVendaPixSafe')) {
        function writeLogVendaPixSafe($fileLog, $mensagem)
        {
                if (is_resource($fileLog)) {
                        fwrite($fileLog, $mensagem);
                }
        }
}

if (!function_exists('closeLogVendaPixSafe')) {
        function closeLogVendaPixSafe($fileLog)
        {
                if (is_resource($fileLog)) {
                        fclose($fileLog);
                }
        }
}

if (!function_exists('isPgResultSafe')) {
        function isPgResultSafe($result)
        {
                return is_resource($result) || (is_object($result) && get_class($result) === 'PgSql\\Result');
        }
}

if (!function_exists('pgNumRowsSafe')) {
        function pgNumRowsSafe($result)
        {
                return isPgResultSafe($result) ? pg_num_rows($result) : 0;
        }
}

if (!function_exists('pgFetchArraySafe')) {
        function pgFetchArraySafe($result)
        {
                return isPgResultSafe($result) ? pg_fetch_array($result) : false;
        }
}

if (!function_exists('pgFetchAssocSafe')) {
        function pgFetchAssocSafe($result)
        {
                return isPgResultSafe($result) ? pg_fetch_assoc($result) : false;
        }
}

if (!function_exists('pgAffectedRowsSafe')) {
        function pgAffectedRowsSafe($result)
        {
                return isPgResultSafe($result) ? pg_affected_rows($result) : 0;
        }
}



function conciliaVendaGames_deposito(int $venda_id, int $dep_id, int $EstabCod, array $parametros): string
{

	//Validacoes
	$msg = "";

	//Valida venda id
        if (!$venda_id)
                $msg = "Cdigo da venda no fornecido." . PHP_EOL;

        elseif (!is_numeric($venda_id))
                $msg = "C�digo da venda inv�lido." . PHP_EOL;

        //Valida dep id
        if (!$dep_id)
                $msg = "C�digo do dep�sito n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($dep_id))
                $msg = "C�digo do dep�sito inv�lido." . PHP_EOL;

        //Valida EstabCod
        if (!$EstabCod)
                $msg = "C�digo do estabelecimento n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($EstabCod))
                $msg = "C�digo do estabelecimento inv�lido." . PHP_EOL;

        //Recupera a venda
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg
                                 where vg.vg_id = " . $venda_id;
                $rs_venda = SQLexecuteQuery($sql);
                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
                else {
                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                        if (is_array($rs_venda_row)) {
                                $vg_ug_id = $rs_venda_row['vg_ug_id'] ?? null;
                                $vg_id = $rs_venda_row['vg_id'] ?? null;
                                $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'] ?? null;
                                $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'] ?? null;
                                $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'] ?? null;
                                $vg_integracao_parceiro_origem_id = $rs_venda_row['vg_integracao_parceiro_origem_id'] ?? null;
                        }

                        //valida status
                        if ($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'])
                                $msg = "Dados do Pagamento ainda n�o recebidos (A1) (vg_ultimo_status: '$vg_ultimo_status')." . PHP_EOL;
                }
        }

        if ($msg == "") {
                if ($vg_integracao_parceiro_origem_id == "") {
                        //Verifica estoque
                        $msg = verificaEstoque($venda_id);
                } else {
                        // Aciona notify_url
                        $url_notify_url = getPartner_param_By_ID('notify_url', $vg_integracao_parceiro_origem_id);
                        $partner_do_notify = getPartner_param_By_ID('partner_do_notify', $vg_integracao_parceiro_origem_id);
                        $s_msg = str_repeat("*", 80) . PHP_EOL . (($partner_do_notify == 1) ? "VAI FAZER NOTIFY" : "Sem notify") . PHP_EOL;
                        $s_msg .= "    vg_integracao_parceiro_origem_id: $vg_integracao_parceiro_origem_id" . PHP_EOL . "    partner_do_notify: $partner_do_notify" . PHP_EOL . "    url_notify_url: '$url_notify_url'" . PHP_EOL;
                        grava_log_integracao_tmp(str_repeat("*", 80) . PHP_EOL . "Vai processar integra��o:" . PHP_EOL . $s_msg);
                        if ($partner_do_notify == 1 && ($url_notify_url != "")) {

                                // Monta o passo 4 da Integra��o - Notify partner
                                $sql = "SELECT * FROM tb_integracao_pedido ip 
                                        WHERE 1=1
                                        and ip_store_id = '" . $vg_integracao_parceiro_origem_id . "'
                                        and ip_vg_id = '" . $vg_id . "'";
                                grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . "Select  registro de integra��o para o notify (A1a)" . PHP_EOL . $sql . PHP_EOL);

                                $rs = SQLexecuteQuery($sql);
                                if (!$rs) {
                                        $msg_1 = date("Y-m-d H:i:s") . " - Erro ao recuperar transa��o de integra��o B2 (store_id: '" . $vg_integracao_parceiro_origem_id . "', vg_id: $vg_id)." . PHP_EOL;
                                        echo $msg_1;
                                        grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . $msg_1);
	                                                        } else {
	                                                                $rs_row = pgFetchArraySafe($rs);

	                                                                if (!$rs_row) {
	                                                                        $msg_1 = date("Y-m-d H:i:s") . " - Transacao de integracao nao encontrada para notify (store_id: '" . $vg_integracao_parceiro_origem_id . "', vg_id: $vg_id)." . PHP_EOL;
	                                                                        echo $msg_1;
	                                                                        grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . $msg_1);
	                                                                } else {
	                                                                $post_parameters = "store_id=" . $rs_row["ip_store_id"] . "&";

	                                                                $post_parameters .= "transaction_id=" . $rs_row["ip_transaction_id"] . "&";
                                        $post_parameters .= "order_id=" . $rs_row["ip_order_id"] . "&";
                                        $post_parameters .= "amount=" . $rs_row["ip_amount"] . "&";
                                        if (strlen($rs_row["ip_product_id"]) > 0) {
                                                $post_parameters .= "product_id=" . $rs_row["ip_product_id"] . "&";
                                        }
                                        $post_parameters .= "client_email=" . $rs_row["ip_client_email"] . "&";
                                        $post_parameters .= "client_id=" . $rs_row["ip_client_id"] . "&";

                                        $post_parameters .= "currency_code=" . $rs_row["ip_currency_code"];

                                        $sret1 = getIntegracaoCURL($url_notify_url, $post_parameters);
                                        $sret = $sret1;

                                        $s_msg = "AFTER Partner Notify - Conciliacao Manual de Dep�sito (Novo esquema) (" . date("Y-m-d H:i:s") . ")" . PHP_EOL . " - result: " . PHP_EOL . str_repeat("_", 80) . PHP_EOL . $sret . PHP_EOL . str_repeat("-", 80) . PHP_EOL;
                                        grava_log_integracao_tmp(str_repeat("*", 80) . PHP_EOL . "Retorno de getIntegracaoCURL (1): " . PHP_EOL . print_r($post_parameters, true) . PHP_EOL . $s_msg . PHP_EOL);
                                }
	                        }
	                }
	        }
	}

	        //Recupera o deposito
        if ($msg == "") {
                $sql = "select * from depositos_pendentes dep
                                 where dep_codigo = " . $dep_id;
                $rs_dep = SQLexecuteQuery($sql);
                if (!$rs_dep || pgNumRowsSafe($rs_dep) == 0)
                        $msg = "Nenhum dep�sito encontrado." . PHP_EOL;
                else {
                        $rs_dep_row = pgFetchArraySafe($rs_dep);
                        $dep_valor = $rs_dep_row['dep_valor'];
                }
        }

        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
        }

        //Concilia deposito
        if ($msg == "") {

                //Concilia deposito
                $sql = "update depositos_pendentes set dep_aprovado = 1, dep_aprovado_data = CURRENT_DATE, dep_venda_games_id = " . $venda_id . "
                                where dep_codigo = " . $dep_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar deposito." . PHP_EOL;
        }

        //Concilia na venda_games e atualiza status (deposito)
        if ($msg == "") {
                $iduser_bko = null;
                $sql = "update tb_venda_games set 
                                        vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "',
                                        vg_dep_codigo = " . SQLaddFields($dep_id, "") . ", 
                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'], "") . "
                                where vg_id = " . $venda_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar venda." . PHP_EOL;
        }

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transa��o." . PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;
        }

        return $msg;
}

function conciliaVendaGames_boleto($venda_id, $boleto_id, $EstabCod, $parametros)
{

        $bHTML = (php_sapi_name() == "isapi");

        $bPrint = (!$bHTML);

        $bDebug = true; //false;

        //Validacoes
        $msg = "";
        if ($bDebug) {
                $time_start_stats = getmicrotime();
        }

        //Valida venda id
        if (!$venda_id)
                $msg = "C�digo da venda n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($venda_id))
                $msg = "C�digo da venda inv�lido." . PHP_EOL;

        //Valida boleto id
        if (!$boleto_id)
                $msg = "C�digo do boleto n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($boleto_id))
                $msg = "C�digo do boleto inv�lido." . PHP_EOL;

        //Valida EstabCod
        if (!$EstabCod)
                $msg = "C�digo do estabelecimento n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($EstabCod))
                $msg = "C�digo do estabelecimento inv�lido." . PHP_EOL;

        // Levanta venda de Campeonato
        $b_isVendaCampeonato = isVendaCampeonato($venda_id);

        $isVendaDeposito = isVendaDeposito($venda_id);

        //Recupera a venda
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg
                                 where vg.vg_id = " . $venda_id;
                $rs_venda = SQLexecuteQuery($sql);
                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
                else {
                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];
                        $vg_integracao_parceiro_origem_id = $rs_venda_row['vg_integracao_parceiro_origem_id'];

                        //valida status
                        if ($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'])
                                $msg = "Dados do Pagamento ainda n�o recebidos (tem '" . $vg_ultimo_status . "' e devia ser '" . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "')(A2)." . PHP_EOL;
                }
        }
        if ($bDebug) {
                echo "Elapsed time (ID: $venda_id, ARG recupera a venda): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . PHP_EOL;
        }

        //Verifica estoque
        if ($msg == "") {
                if ($b_isVendaCampeonato) {
                        gravaLog_TMP("Testing Campeonato Boleto - " . date("Y-m-d H:i:s") . "." . PHP_EOL . "  Venda de Campeonato - SEM  verificaEstoque(), (vg_id = $venda_id)" . PHP_EOL);
                } elseif ($vg_integracao_parceiro_origem_id != "") {
                        gravaLog_TMP("Integra��o Boleto - " . date("Y-m-d H:i:s") . "." . PHP_EOL . "  Venda de Integra��o '$vg_integracao_parceiro_origem_id' - SEM  verificaEstoque(), (vg_id = $venda_id)" . PHP_EOL);
                } elseif ($isVendaDeposito == 1) {
                        echo "Dep�sito em saldo (2132)";

                        $concilia_cod_sel = $GLOBALS['_REQUEST']['concilia_cod_sel'] ?? null;
                        if (!$concilia_cod_sel || !is_numeric($concilia_cod_sel)) {
                                $msg = "C�digo do boleto para concilia��o n�o informado." . PHP_EOL;
                        }
                        if ($msg == "") {
                                $sql = "select * 
                                        from tb_venda_games vg
                                                inner join boleto_bancario_games bbg on bbg.bbg_vg_id = vg.vg_id 
                                                left outer join tb_pag_compras pg on (pg.idvenda = vg.vg_id and tipo_cliente = 'M')
                                                , boletos_pendentes bol 
                                        where (not (vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . " or vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'] . ")) and vg_deposito_em_saldo = 1 
                                                and bol_codigo = $concilia_cod_sel
                                                and vg_id = $venda_id
                                        order by vg_data_inclusao desc";
                                $rs_vendas = SQLexecuteQuery($sql);
                                if (!$rs_vendas || pgNumRowsSafe($rs_vendas) == 0) {
                                        $msg = "Nenhuma venda de dep�sito em Saldo Gamer encontrada para concilia��o." . PHP_EOL;
                                        echo $msg;
                                } else {
                                        echo "Encontradas " . pgNumRowsSafe($rs_vendas) . " vendas de dep�sito pendentes de concilia��o" . PHP_EOL;
                                        while ($rs_vendas_row = pgFetchArraySafe($rs_vendas)) {
                                                $msg = "";
                                                if ($bDebug)
                                                        echo str_repeat("-", 80) . PHP_EOL;

                                        $boleto_id = $concilia_cod_sel;
                                        $usuario_id = $rs_vendas_row['vg_ug_id'];
                                        $bbg_valor = $rs_vendas_row['bbg_valor'];
                                        $bbg_valor_sem_taxa = $rs_vendas_row['bbg_valor'] - $rs_vendas_row['bbg_valor_taxa'];
                                        $vg_pagto_tipo = $rs_vendas_row['vg_pagto_tipo'];
                                        $bol_codigo = $rs_vendas_row['bol_codigo'];

                                        echo "Vai processar venda " . $venda_id . " do usu�rio " . $usuario_id . ", pagto_tipo: '" . $vg_pagto_tipo . "' , bbg_boleto_codigo: '" . $boleto_id . "', bol_codigo: " . $bol_codigo . " (valor: '" . $bbg_valor . "', valor sem taxa: '" . $bbg_valor_sem_taxa . "')<br>" . PHP_EOL;

                                        // Procura boletos
                                        if ($vg_pagto_tipo == 2) {
                                                echo "	vg_pagto_tipo $vg_pagto_tipo==2" . PHP_EOL;
                                                if ($boleto_id > 0) {
                                                        echo "Encontrado boleto " . $bol_codigo . " para venda " . $venda_id . " (usuarioID: $usuario_id)" . PHP_EOL;

                                                        // O retorno do banco com o boleto pode n�o ter sido importado ainda (nesse caso � null)
                                                        $bol_valor = (($rs_vendas_row['bol_valor']) ? $rs_vendas_row['bol_valor'] : 0);
                                                        $bol_banco = $rs_vendas_row['bol_banco'];
                                                        $venda_id = $rs_vendas_row['vg_id'];
                                                        $usuario_id = $rs_vendas_row['vg_ug_id'];
                                                        $parametros = array();

                                                        if ($bDebug)
                                                                echo "Valores Boleto: [(" . $bol_valor . "+" . $GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2'] . ") ? (" . $bbg_valor_sem_taxa . "+" . $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'] . ")]" . PHP_EOL;

                                                        if (($bol_valor + $GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2']) == ($bbg_valor_sem_taxa + $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'])) {
                                                                if ($bDebug)
                                                                        echo "Vai conciliar Boleto (bol: $bol_codigo, vg: $venda_id, ug: $usuario_id)" . PHP_EOL;
                                                                $parametros['valor'] = $bbg_valor_sem_taxa; {        /////////	==================   BLOCKED
                                                                        $ret = conciliaMoneyDepositoSaldo_boleto($bol_codigo, $venda_id, $usuario_id, $parametros);
                                                                        if ($ret != "")
                                                                                echo $ret;
                                                                        else
                                                                                echo "Dep�sito por boleto conciliado com sucesso e saldo depositado" . PHP_EOL;
                                                                }        /////////	==================   BLOCKED
                                                        } else {
                                                                if ($bDebug)
                                                                        echo "N�O concilia Boleto ($boleto_id, $venda_id, $usuario_id)" . PHP_EOL;
                                                        }
                                                } else {
                                                        if ($bDebug)
                                                                echo "Nenhum Boleto encontrado ($venda_id, $usuario_id)" . PHP_EOL;
                                                }
                                        }
                                        }
                                }
                        }
                } else {
                        $msg = verificaEstoque($venda_id);
                }
        }

        if ($bDebug) {
                echo "Elapsed time (ID: $venda_id, ARG verifica estoque): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . PHP_EOL;
        }
        //Recupera o boleto pendente
        if ($msg == "") {
                $sql = "select * from boletos_pendentes bol
                                where bol.bol_codigo = " . $boleto_id;
                $rs_boleto = SQLexecuteQuery($sql);
                if (!$rs_boleto || pgNumRowsSafe($rs_boleto) == 0)
                        $msg = "Nenhum boleto encontrado." . PHP_EOL;
                else {
                        $rs_boleto_row = pgFetchArraySafe($rs_boleto);
                        $bol_data = $rs_boleto_row['bol_data'];
                        $bol_valor = $rs_boleto_row['bol_valor'];
                        $bol_banco = $rs_boleto_row['bol_banco'];
                        $bol_documento = $rs_boleto_row['bol_documento'];
                }
        }

        if ($bDebug) {
                echo "Elapsed time (ID: $venda_id, ARG recupera boleto pendente): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . PHP_EOL;
        }

        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
        }


        //atualiza boleto bancario
        if ($msg == "") {
                $sql = "update boleto_bancario_games set bbg_pago = 1, bbg_data_pago = '" . $bol_data . "'
                                where bbg_vg_id = " . $venda_id;
                if ($vg_integracao_parceiro_origem_id != "") {
                        gravaLog_TMP("Integra��o Boleto - " . date("Y-m-d H:i:s") . "." . PHP_EOL . "  Venda de Integra��o '$vg_integracao_parceiro_origem_id' - Atualiza boleto bancario, (vg_id = $venda_id)" . PHP_EOL . "   $sql" . PHP_EOL);
                }
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao atualizar boleto banc�rio." . PHP_EOL;
        }

        //Concilia boleto
        if ($msg == "") {
                $sql = "update boletos_pendentes set bol_aprovado = 1, bol_aprovado_data = CURRENT_TIMESTAMP, bol_venda_games_id = " . $venda_id . "
                                where bol_codigo = " . $boleto_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar boleto." . PHP_EOL;
        }

        if ($bDebug) {
                echo "Elapsed time (ID: $venda_id, ARG concilia boleto): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . PHP_EOL;
        }

        //Usuario backoffice
        $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
        if ($parametros['PROCESS_AUTOM'] == '1')
                $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

        //Concilia na venda_games e atualiza status (boleto)
        if ($msg == "") {
                $sql = "update tb_venda_games set 
                                        vg_pagto_banco = '" . $bol_banco . "',
                                        vg_pagto_num_docto = '" . $bol_documento . "',
                                        vg_pagto_data = '" . $bol_data . "',
                                        vg_pagto_valor_pago = " . $bol_valor . ",
                                        vg_bol_codigo = " . SQLaddFields($boleto_id, "") . ",
                                        vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "',
                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'], "") . "
                                where vg_id = " . $venda_id;

                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar venda A1 (vg_id=$venda_id)." . PHP_EOL;
        }

        if ($bDebug) {
                echo "Elapsed time (ID: $venda_id, ARG concilia venda): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . PHP_EOL;
        }

        if ($bDebug) {
                echo "Elapsed time (ID: $venda_id, ARG insere estabelecimento): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . PHP_EOL;
        }

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transa��o." . PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;
        }

        if ($bDebug) {
                echo "Elapsed time (ID: $venda_id, ARG end transaction): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . " (MSG: '$msg')" . PHP_EOL;
        }

        return $msg;
}

function conciliaVendaGames_PagamentoOnline($venda_id, $pagamento_id, $EstabCod, $parametros, $webhook = false)
{

	//Validacoes
	$msg = "";
	$pag_banco = "";
	$pag_data = "";
	$pag_documento = "";
	$pag_valor = 0;
	$pag_valor_conc = 0;

	//Valida venda id
        if (!$venda_id)
                $msg = "C�digo da venda n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($venda_id))
                $msg = "C�digo da venda inv�lido." . PHP_EOL;

        //Valida boleto id
        if (!$pagamento_id)
                $msg = "C�digo do pagamento n�o fornecido (1)." . PHP_EOL;
        elseif (!is_numeric($pagamento_id))
                $msg = "C�digo do pagamento inv�lido." . PHP_EOL;

        //Valida EstabCod
        if (!$EstabCod)
                $msg = "C�digo do estabelecimento n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($EstabCod))
                $msg = "C�digo do estabelecimento inv�lido." . PHP_EOL;

        // Levanta venda de Campeonato
        $b_isVendaCampeonato = isVendaCampeonato($venda_id);

        $vgm_qtde = null;
        $vgm_pin_codinterno = null;
        // Levanta pins vendidos
        $npins = get_qtde_pins($venda_id, $vgm_qtde, $vgm_pin_codinterno);
        gravaLog_TMP("Testing Conciliacao nPINs - em conciliacao() - " . date("Y-m-d H:i:s") . "." . PHP_EOL . "  venda_id: " . $venda_id . ", vgm_qtde: " . $vgm_qtde . "'" . PHP_EOL);
        if ($vgm_pin_codinterno) {
                $msg = "Erro na Concilia��o PagOnline - PINs j� foram vendidos - " . date("Y-m-d H:i:s") . PHP_EOL . "   Venda $venda_id ." . PHP_EOL;
                gravaLog_TMP("ERROR - Conciliacao nPINs - em conciliacao() - " . date("Y-m-d H:i:s") . "." . PHP_EOL . "  venda_id: " . $venda_id . ", vgm_qtde: " . $vgm_qtde  . PHP_EOL . "  $msg" . PHP_EOL);
        }

        // Valida valores
        if ($msg == "") {
                $total_geral = 0;
                $sql = "select * from tb_venda_games vg " .
                        "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                        "where vg.vg_id = " . $venda_id;

                $rs_venda_modelos = SQLexecuteQuery($sql);

                if ($rs_venda_modelos && pgNumRowsSafe($rs_venda_modelos) > 0) {
                        while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                                $qtde = $rs_venda_modelos_row['vgm_qtde'];
                                $valor = $rs_venda_modelos_row['vgm_valor'];
                                $total_geral += $valor * $qtde;
                        }
                }
                echo "  - Valor total da venda (venda_id: $venda_id) " . $total_geral . PHP_EOL;
        }

        $fileLog = @fopen("/www/arquivos_gerados/logs/log_vendaPIX.txt", "a+");
        if ($fileLog) {
        writeLogVendaPixSafe($fileLog, "ID VENDA CONCILIA��O ONLINE: " . $venda_id . "\n");
        }

        //Recupera a venda
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg
                                 where vg.vg_id = " . $venda_id;
                $rs_venda = SQLexecuteQuery($sql);
                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
                else {
                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];

                        writeLogVendaPixSafe($fileLog, "ULTIMO STATUS VENDA: " . $vg_ultimo_status . " - PRECISA SER '2' / " . $venda_id . " \n");

                        $sql = "select count(*) as qtde from tb_venda_games_modelo where vgm_vg_id = " . $venda_id . " and vgm_pin_codinterno <> '';";
                        $rs_venda = SQLexecuteQuery($sql);
                        if (pgNumRowsSafe($rs_venda) == 0) {
                                //valida status 
                                if ($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] && $webhook == false)
                                        $msg = "Dados do Pagamento ainda n�o recebidos(A3) (vg_ultimo_status: '$vg_ultimo_status', debia ser " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . ")." . PHP_EOL;
                        }
                }
        }


        //Verifica estoque
        // if ($msg == "") {
        //         if ($b_isVendaCampeonato) {
        //                 gravaLog_TMP("Testing Campeonato PagOnline - " . date("Y-m-d H:i:s") . "." . PHP_EOL . "  Venda de Campeonato - SEM  verificaEstoque(), (vg_id = $venda_id)" . PHP_EOL);
        //         } else {
        //                 $msg = verificaEstoque($venda_id);
        //         }
        // }

        if ($msg !== "") {
                /* ===============================
                * Verifica pedido de integra��o
                * =============================== */
                $sqlIntegracao = "SELECT 1 FROM tb_integracao_pedido WHERE ip_vg_id = " . $venda_id;
                $pedidoIntegracao = SQLexecuteQuery($sqlIntegracao);
                $existeIntegracao = $pedidoIntegracao && pgNumRowsSafe($pedidoIntegracao) > 0;

                if (!$existeIntegracao) {

                        if ($b_isVendaCampeonato) {
                                gravaLog_TMP("Testing Campeonato PagOnline - " . date("Y-m-d H:i:s") . "." . PHP_EOL . "  Venda de Campeonato - SEM  verificaEstoque(), (vg_id = $venda_id)" . PHP_EOL);
                        } else {
                                $msg = verificaEstoque($venda_id);
                        }
                } else {
                        grava_log_integracao("Pedido de integra��o, bypass de estoque aplicado. Pedido: $venda_id");
                }
        }

        //Recupera o pagamento pendente
        if ($msg == "") {
                $sql = "select * from tb_pag_compras pag where pag.numcompra = '" . $pagamento_id . "' ";
                $sql .= " and idcliente = $vg_ug_id ";

                $rs_pagamento = SQLexecuteQuery($sql);
                if (!$rs_pagamento || pgNumRowsSafe($rs_pagamento) == 0)
                        $msg = "Nenhum pagamento encontrado (B)." . PHP_EOL;
                else {
                        $rs_pagamento_row = pgFetchArraySafe($rs_pagamento);
                        $pag_data = $rs_pagamento_row['dataconfirma'];
                        // Testa a existencia de data cadastrada
                        if (!trim($pag_data))
                                $pag_data = date("Y-m-d H:i:s");
                        $pag_valor = $rs_pagamento_row['total'] / 100;
                        $pag_valor_conc = round(($rs_pagamento_row['total'] / 100) - $rs_pagamento_row['taxas'], 2);
                        $pag_banco = $rs_pagamento_row['banco'];

                        $prefix = getDocPrefix($rs_pagamento_row['iforma']);

                        $pag_documento = $prefix . $rs_pagamento_row['iforma'] . "_" . $rs_pagamento_row['numcompra'];
                        echo "  - Valor total do pagamento (venda_id: $venda_id, '$pagamento_id') " . $pag_valor . PHP_EOL;
                }
        }

        //Valida valores
        if ($msg == "") {
                if (round($total_geral, 2) != $pag_valor_conc) {
                        echo "  - Valores N�O conferem ($total_geral)!=($pag_valor_conc))" . PHP_EOL;
                        $msg = " ERRO na CONCILIA��O - Valores N�O conferem [venda_id:$venda_id] ($total_geral)==($pag_valor_conc))" . PHP_EOL;
                } else {
                        echo "  - Valores conferem ($total_geral)==($pag_valor_conc))" . PHP_EOL;
                }
        }

        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
        }

        //Usuario backoffice
        $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
        if ($parametros['PROCESS_AUTOM'] == '1')
                $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

        //Concilia na venda_games e atualiza status (PagamentoOnline)
        if ($msg == "") {
                $sql = "update tb_venda_games set 
                                        vg_pagto_banco = '" . $pag_banco . "',
                                        vg_pagto_num_docto = '" . $pag_documento . "',
                                        vg_pagto_data = '" . $pag_data . "',
                                        vg_pagto_valor_pago = " . $pag_valor . ",
                                        vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "',
                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'], "") . "
                                where vg_id = " . $venda_id;

                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar venda (32)" . PHP_EOL . $sql . PHP_EOL;

                writeLogVendaPixSafe($fileLog, "ATUALIZA��O STATUS VENDA PARA '3' PAGAMENTO ONLINE / " . $venda_id . " \n");
        }

        if ($msg == "") {
                // registro foi processado
                $sql = "update tb_pag_compras set status_processed=1 where numcompra='" . $pagamento_id . "' ";
                $sql .= " and idcliente = $vg_ug_id ";
                echo "DEBUG A (status_processed = 1, idvenda = $venda_id): " . $sql . PHP_EOL;

                $rs_update2 = SQLexecuteQuery($sql);
                if (!$rs_update2) {
                        $msg = "Erro atualizando status de registro (62)." . PHP_EOL;
                        echo $msg;
                }
        } else {
                echo "ERRO antes de atualziar tb_pag_compras REREREW ($msg)" . PHP_EOL;
        }

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transa��o." . PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;
        }

        writeLogVendaPixSafe($fileLog, "MENSAGEM FINAL VENDA ONLINE: " . $msg . " / " . $venda_id . " \n");
        closeLogVendaPixSafe($fileLog);


        return $msg;
}

function conciliaVendaGames_Integracao($venda_id, $pagamento_id, $EstabCod, $parametros)
{

        //Validacoes
        $msg = "";

        //Valida venda id
        if (!$venda_id)
                $msg = "C�digo da venda n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($venda_id))
                $msg = "C�digo da venda inv�lido." . PHP_EOL;

        //Valida boleto id
        if (!$pagamento_id)
                $msg = "C�digo do pagamento n�o fornecido (2)." . PHP_EOL;
        elseif (!is_numeric($pagamento_id))
                $msg = "C�digo do pagamento inv�lido." . PHP_EOL;

        //Valida EstabCod
        if (!$EstabCod)
                $msg = "C�digo do estabelecimento n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($EstabCod))
                $msg = "C�digo do estabelecimento inv�lido." . PHP_EOL;

        //Recupera a venda
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg
                                 where vg.vg_id = " . $venda_id;
                $rs_venda = SQLexecuteQuery($sql);
                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
                else {
                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];

                        //valida status
                        if ($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'])
                                $msg = "Dados do Pagamento ainda n�o recebidos(A3a) (vg_ultimo_status: '$vg_ultimo_status', != '" . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "')." . PHP_EOL;
                }
        }

        //Recupera o pagamento pendente
        if ($msg == "") {
                $sql = "select * from tb_pag_compras pag where pag.numcompra = '" . $pagamento_id . "'";
                $sql .= " and idcliente = $vg_ug_id ";

                $rs_pagamento = SQLexecuteQuery($sql);
                if (!$rs_pagamento || pgNumRowsSafe($rs_pagamento) == 0)
                        $msg = "Nenhum pagamento encontrado (B)." . PHP_EOL;
                else {
                        $rs_pagamento_row = pgFetchArraySafe($rs_pagamento);
                        $pag_data = $rs_pagamento_row['dataconfirma'];
                        // Testa a existencia de data cadastrada
                        if (!trim($pag_data))
                                $pag_data = date("Y-m-d H:i:s");
                        $pag_valor = $rs_pagamento_row['total'] / 100;
                        $pag_banco = $rs_pagamento_row['banco'];

                        $prefix = getDocPrefix($rs_pagamento_row['iforma']);

                        $pag_documento = $prefix . $rs_pagamento_row['iforma'] . "_" . $rs_pagamento_row['numcompra'];
                }
        }
        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
        }

        //Usuario backoffice
        $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
        if ($parametros['PROCESS_AUTOM'] == '1')
                $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

        //Concilia na venda_games e atualiza status (Integracao)
        if ($msg == "") {
                $sql = "update tb_venda_games set 
                                        vg_pagto_banco = '" . $pag_banco . "',
                                        vg_pagto_num_docto = '" . $pag_documento . "',
                                        vg_pagto_data = '" . $pag_data . "',
                                        vg_pagto_valor_pago = " . $pag_valor . ",
                                        vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "',
                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'], "") . "
                                where vg_id = " . $venda_id;

                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar venda (32)" . PHP_EOL . $sql . PHP_EOL;
        }

        if (!$msg) {
                // registro foi processado
                $sql = "update tb_pag_compras set status_processed=1 where numcompra='" . $pagamento_id . "' ";
                $sql .= " and idcliente = $vg_ug_id ";
                echo "DEBUG B (atualiza status_processed=1, vendaid = $venda_id): " . $sql . PHP_EOL;

                $rs_update2 = SQLexecuteQuery($sql);
                if (!$rs_update2) {
                        $msg = "Erro atualizando status de registro (62)." . PHP_EOL;
                        echo $msg;
                }
        }

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transa��o." . PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;
        }

        return $msg;
}

function conciliaVendaGames_redecard($venda_id, $redecard_id, $EstabCod, $parametros)
{

        //Validacoes
        $msg = "";

        //Valida venda id
        if (!$venda_id)
                $msg = "C�digo da venda n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($venda_id))
                $msg = "C�digo da venda inv�lido." . PHP_EOL;

        //Valida redecard id
        if (!$redecard_id)
                $msg = "C�digo do redecard n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($redecard_id))
                $msg = "C�digo do redecard inv�lido." . PHP_EOL;

        //Valida EstabCod
        if (!$EstabCod)
                $msg = "C�digo do estabelecimento n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($EstabCod))
                $msg = "C�digo do estabelecimento inv�lido." . PHP_EOL;

        //Recupera a venda
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg
                                 where vg.vg_id = " . $venda_id;
                $rs_venda = SQLexecuteQuery($sql);
                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
                else {
                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];

                        //valida status
                        if ($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'])
                                $msg = "Dados do Pagamento ainda n�o recebidos(A4)." . PHP_EOL;
                }
        }

        //Verifica estoque
        if ($msg == "") {
                $msg = verificaEstoque($venda_id);
        }

        //Recupera o redecard
        if ($msg == "") {
                $sql = "select * from tb_venda_games_redecard vgrc
                                where vgrc.vgrc_id = " . $redecard_id;
                $rs_redecard = SQLexecuteQuery($sql);
                if (!$rs_redecard || pgNumRowsSafe($rs_redecard) == 0)
                        $msg = "Nenhum redecard encontrado." . PHP_EOL;
                else {
                        $rs_redecard_row = pgFetchArraySafe($rs_redecard);
                        $vgrc_total = $rs_redecard_row['vgrc_total'];
                        $vgrc_ret2_numautent = $rs_redecard_row['vgrc_ret2_numautent'];
                        $vgrc_ret2_data = $rs_redecard_row['vgrc_ret2_data'];
                }
        }

        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
        }

        //Concilia boleto
        if ($msg == "") {
                $sql = "update tb_venda_games_redecard set vgrc_aprovado = 1, vgrc_aprovado_data = CURRENT_TIMESTAMP
                                where vgrc_id = " . $redecard_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao aprovar redecard." . PHP_EOL;
        }

        //Usuario backoffice
        $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
        if ($parametros['PROCESS_AUTOM'] == '1')
                $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

        //Concilia na venda_games e atualiza status (redecard)
        if ($msg == "") {
                $sql = "update tb_venda_games set 
                                        vg_pagto_data = '" . $vgrc_ret2_data . "',
                                        vg_pagto_num_docto = '" . $vgrc_ret2_numautent . "',
                                        vg_pagto_valor_pago = " . $vgrc_total . ",
                                        vg_vgrc_id = " . SQLaddFields($redecard_id, "") . ",
                                        vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "',
                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'], "") . "
                                where vg_id = " . $venda_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar venda." . PHP_EOL;
        }

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transa��o." . PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;
        }

        return $msg;
}


function verificaEstoque($venda_id)
{

        $msg = "";

        //Recupera modelos
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                inner join operadoras ope on ope.opr_codigo = vgm.vgm_opr_codigo
                                where vg.vg_id = " . $venda_id;
                $rs_venda_modelos = SQLexecuteQuery($sql);
                if (!$rs_venda_modelos || pgNumRowsSafe($rs_venda_modelos) == 0)
                        $msg = "Nenhum produto encontrado (VE_1)." . PHP_EOL;
        }

        if ($msg == "") {

                //Verifica cada item de cada produro
                while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                        $vgm_qtde = $rs_venda_modelos_row['vgm_qtde'];
                        $vgm_opr_codigo = $rs_venda_modelos_row['vgm_opr_codigo'];
                        $vgm_pin_valor = $rs_venda_modelos_row['vgm_pin_valor'];
                        $opr_nome = $rs_venda_modelos_row['opr_nome'];
                        $vgm_pin_request = $rs_venda_modelos_row['vgm_pin_request'];
                        $produto_operadora = $rs_venda_modelos_row['vgm_ogp_id'];


                        // Teste se o PIN n�o � de requisi��o
                        if ($vgm_pin_request == 0) {

                                if ($produto_operadora != 560) {

                                        //PINS
                                        //---------------------------------------------------------------------------------------------------
                                        $sql = "select count(*) as pins_qtde from pins
													where opr_codigo = " . $vgm_opr_codigo . "
															and pin_status = '1' and pin_canal='s' 
															and pin_valor = " . $vgm_pin_valor;
                                        $rs_pins = SQLexecuteQuery($sql);
                                        if (!$rs_pins || pgNumRowsSafe($rs_pins) == 0)
                                                $msg .= "N�o h� pin de " . number_format($vgm_pin_valor, 2, ',', '.') . " da operadora " . $opr_nome . " em estoque (A)." . PHP_EOL;
                                        else {
                                                $rs_pins_row = pgFetchArraySafe($rs_pins);
                                                $pins_qtde = $rs_pins_row['pins_qtde'];
                                                if ($pins_qtde < $vgm_qtde)
                                                        $msg .= "N�o h� suficientes pins de " . number_format($vgm_pin_valor, 2, ',', '.') . " da operadora " . $opr_nome . " em estoque (B)." . PHP_EOL;
                                        }
                                }
                        } //end if($vgm_pin_request == 0) {
                }
        }

        return $msg;
}

function processaVendaGamesIntegracao($venda_id, $EstabCod, $parametros)
{
        $msg = "";
        $vg_ug_id = null;
        $vg_pagto_banco = null;
        $vg_pagto_num_docto = "";
        $vg_pagto_tipo = null;
        $vg_dep_codigo = null;
        $vg_bol_codigo = null;

        if (!$venda_id) {
                $msg = "Erro em processaVendaGamesIntegracao() [" . date("Y-m-d H:i:s") . "] - venda_id = 0";
                return $msg;
        }
        $vg_integracao_param = $parametros['vg_integracao_parceiro_origem_id'] ?? '';
        $ip_id = (($vg_integracao_param) ? getIntegracaoPedidoID_By_Venda($vg_integracao_param, $venda_id) : 0);

        grava_log_integracao_tmp("Integra��o get ip_id from vg_id em processaVendaGamesIntegracao(): " . date("Y-m-d H:i:s") . " (vg: $venda_id -> ip_id: '$ip_id', vg_integracao_parceiro_origem_id: '" . $vg_integracao_param . "') " . PHP_EOL);

        //Recupera a venda
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg " .
                        "where vg.vg_id = " . $venda_id;
                $rs_venda = SQLexecuteQuery($sql);
                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
                else {
                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
                        $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_dep_codigo = $rs_venda_row['vg_dep_codigo'];
                        $vg_bol_codigo = $rs_venda_row['vg_bol_codigo'];

                        //valida status
                        if ($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'])
                                $msg = "Pagamento ainda n�o esta confirmado (B, status: '$vg_ultimo_status', deve ser '" . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . "')." . PHP_EOL;
                }
        }

        //Recupera modelos
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg " .
                        "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                        "where vg.vg_id = " . $venda_id;
                $rs_venda_modelos = SQLexecuteQuery($sql);
                if (!$rs_venda_modelos || pgNumRowsSafe($rs_venda_modelos) == 0)
                        $msg = "Nenhuma produto encontrado (I)." . PHP_EOL;
        }

        //Recupera dados do usuario
        if ($msg == "") {
                $sql = "select * from usuarios_games ug " .
                        "where ug.ug_id = " . $vg_ug_id;
                $rs_usuario = SQLexecuteQuery($sql);
                if (!$rs_usuario || pgNumRowsSafe($rs_usuario) == 0)
                        $msg = "Nenhum cliente encontrado." . PHP_EOL;
                else {
                        $rs_usuario_row = pgFetchArraySafe($rs_usuario);
                        $ug_cel_ddd = $rs_usuario_row['ug_cel_ddd'];
                        $ug_cel = $rs_usuario_row['ug_cel'];
                }
        }

        //Recupera dados do credito
        if ($msg == "") {
                //Deposito
                if ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']) {

                        $sql = "select * from depositos_pendentes dep " .
                                "where dep.dep_codigo = " . $vg_dep_codigo;
                        $rs_deposito = SQLexecuteQuery($sql);
                        if (!$rs_deposito || pgNumRowsSafe($rs_deposito) == 0)
                                $msg = "Nenhum deposito encontrado (I)." . PHP_EOL;
                        else {
                                $rs_deposito_row = pgFetchArraySafe($rs_deposito);
                                $dep_documento = $rs_deposito_row['dep_documento'];
                                $ped_cod_doc_equiv = $dep_documento;
                                $ped_dep_codigo = $vg_dep_codigo;
                                $ped_bol_codigo = null;

                                $valor_pago = $rs_deposito_row['dep_valor'];
                                $datainicio = $rs_deposito_row['dep_aprovado_data'];
                        }

                        //Boleto
                } elseif ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) {

                        $sql = "select * from boletos_pendentes bol " .
                                "where bol.bol_codigo = " . $vg_bol_codigo;
                        $rs_boleto = SQLexecuteQuery($sql);
                        if (!$rs_boleto || pgNumRowsSafe($rs_boleto) == 0)
                                $msg = "Nenhum boleto encontrado (I)." . PHP_EOL;
                        else {
                                $rs_boleto_row = pgFetchArraySafe($rs_boleto);
                                $bol_documento = $rs_boleto_row['bol_documento'];
                                $ped_cod_doc_equiv = $bol_documento;
                                $ped_dep_codigo = null;
                                $ped_bol_codigo = $vg_bol_codigo;

                                $valor_pago = $rs_boleto_row['bol_valor'];
                                $datainicio = $rs_boleto_row['bol_aprovado_data'];
                        }
                        // Pagamentos Online Bradesco: D�bito/Transfer�ncia - Banco do Brasil
                } elseif (
                        b_IsPagtoOnline($vg_pagto_tipo)
                        /*
                                        ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || 
                                        ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || 
                                        ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) || 
                                        ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']) ||
                                        ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']) ||
                                        ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']) ||
                                        ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']) ||
                                        ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']) 
                        */
                ) {

                        $sql = "select * from tb_pag_compras pag where pag.idvenda = " . $venda_id;
                        echo "  DEBUG *** EE ($vg_pagto_num_docto, '" . $vg_pagto_tipo . "'): " . $sql . PHP_EOL;
                        $rs_pagamento = SQLexecuteQuery($sql);
                        if (!$rs_pagamento || pgNumRowsSafe($rs_pagamento) == 0)
                                $msg = "Nenhum pagamento encontrado (A2)." . PHP_EOL;
                        else {
                                $rs_pagamento_row = pgFetchArraySafe($rs_pagamento);

                                $prefix = getDocPrefix($rs_pagamento_row['iforma']);

                                $bol_documento = $prefix . $rs_pagamento_row['iforma'] . "_" . $rs_pagamento_row['numcompra'];
                                $ped_cod_doc_equiv = "##&&**";
                                $ped_dep_codigo = null;
                                $ped_bol_codigo = null;

                                $valor_pago = ($rs_pagamento_row['total'] / 100);
                                $datainicio = $rs_pagamento_row['datainicio'];
                        }
                } else {
                        $msg = "Nenhuma forma de pagamento existente." . PHP_EOL;
                        echo "Sem forma pagamento definida '$vg_pagto_tipo'" . PHP_EOL;
                }
        }

        //Usuario backoffice
        $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
        if (($parametros['PROCESS_AUTOM'] ?? '') == '1')
                $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

        $data_corrente = date("Y/m/d");
        $hora_corrente = date("H:i:s");

        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
        }

        if ($msg == "") {

                //Realiza uma venda para cada item de cada produto
                while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                        $vgm_id = $rs_venda_modelos_row['vgm_id'];
                        $vgm_valor = $rs_venda_modelos_row['vgm_valor'];
                        $vgm_qtde = $rs_venda_modelos_row['vgm_qtde'];
                        $vgm_opr_codigo = $rs_venda_modelos_row['vgm_opr_codigo'];
                        $vgm_pin_valor = $rs_venda_modelos_row['vgm_pin_valor'];
                        $vgm_game_id = $rs_venda_modelos_row['vgm_game_id'];

                        //Realiza n qtde de venda de pins
                        for ($i = 0; $i < $vgm_qtde; $i++) {
                                // Aciona notify_url no site do parceiro
                                $s_msg = "      Realizando venda - " . date("Y-m-d H:i:s") . " - vg_integracao_parceiro_origem_id: " . $parametros['vg_integracao_parceiro_origem_id'] . ", vg_id = $venda_id, ip_id = $ip_id, vgm_id = $vgm_id, vgm_valor: $vgm_valor, vgm_qtde = $vgm_qtde, vgm_opr_codigo = $vgm_opr_codigo, vgm_pin_valor = $vgm_pin_valor, vgm_game_id = $vgm_game_id" . PHP_EOL . PHP_EOL;
                        }
                }
        }

        //VENDA GAMES
        //---------------------------------------------------------------------------------------------------
        //atualiza status 'PROCESSAMENTO_REALIZADO' -> '4'
        if ($msg == "") {

                //Usuario backoffice
                $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
                if ($parametros['PROCESS_AUTOM'] == '1')
                        $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

                $sql = "update tb_venda_games set " . PHP_EOL . "
                                        vg_pagto_valor_pago	= " . SQLaddFields($valor_pago, "") . "," . PHP_EOL . "
                                        vg_pagto_data_inclusao	= " . SQLaddFields($datainicio, "s") . "," . PHP_EOL . "
                                        vg_pagto_num_docto = " . SQLaddFields($bol_documento, "s") . "," . PHP_EOL . "
                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . "," . PHP_EOL . "
                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'], "") . " " . PHP_EOL;
                $sql .= "where vg_id = " . $venda_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar venda." . PHP_EOL;
        }

        //atualiza status em tabela pagamentos para pagamentos online
        if ($msg == "") {
                if (
                        ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'])
                ) {

                        //atualiza boleto bancario
                        if ($msg == "") {
                                $sql = "update boleto_bancario_games set bbg_pago = 1 where bbg_vg_id = " . $venda_id;
                                echo "    DUMMY SQL Integracao - " . $sql . PHP_EOL;
                                $ret = SQLexecuteQuery($sql);
                                if (!$ret)
                                        $msg = "Erro ao atualizar boleto banc�rio." . PHP_EOL;
                        }

                        //Concilia boleto
                        if ($msg == "") {
                                $sql = "update boletos_pendentes set bol_aprovado = 1, bol_aprovado_data = CURRENT_TIMESTAMP, bol_venda_games_id = " . $venda_id . "
                                                where bol_codigo = " . $vg_bol_codigo;
                                $ret = SQLexecuteQuery($sql);
                                if (!$ret)
                                        $msg = "Erro ao conciliar boleto." . PHP_EOL;
                        }
                } elseif (
                        b_IsPagtoOnline($vg_pagto_tipo)
                        /*	
                        ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || 
                        ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || 
                        ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) || 
                        ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']) ||
                        ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']) ||
                        ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']) ||
                        ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']) ||
                        ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']) 
                        */
                ) {
                        $sql = "update tb_pag_compras set status_processed=1 where idvenda=" . $venda_id . ";";
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret)
                                $msg_not_relevant = "Erro ao conciliar venda (status_processed not set to '1')." . PHP_EOL;
                }
        }

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transa��o." . PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                echo "    DUMMY Integracao OUT - " . $s_msg . PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;
        }
        echo "    DUMMY Integracao EXITING" . PHP_EOL . PHP_EOL;

        return $msg;
}

function processaVendaGames_pagto_online_banco_epp($venda_id, $EstabCod, $parametros)
{

        $msg = "";

        //Recupera a venda
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg " .
                        "where vg.vg_id = " . $venda_id;
                $rs_venda = SQLexecuteQuery($sql);
                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
                else {
                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
                        $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_dep_codigo = $rs_venda_row['vg_dep_codigo'];
                        $vg_bol_codigo = $rs_venda_row['vg_bol_codigo'];

                        //valida status
                        if ($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'])
                                $msg = "Pagamento ainda n�o esta confirmado (C)." . PHP_EOL;
                }
        }

        //Recupera modelos
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg " .
                        "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                        "where vg.vg_id = " . $venda_id;
                $rs_venda_modelos = SQLexecuteQuery($sql);
                if (!$rs_venda_modelos || pgNumRowsSafe($rs_venda_modelos) == 0)
                        $msg = "Nenhum produto encontrado (PVG_EPP_1)." . PHP_EOL;
        }

        //Recupera dados do usuario
        if ($msg == "") {
                $sql = "select * from usuarios_games ug " .
                        "where ug.ug_id = " . $vg_ug_id;
                $rs_usuario = SQLexecuteQuery($sql);
                if (!$rs_usuario || pgNumRowsSafe($rs_usuario) == 0)
                        $msg = "Nenhum cliente encontrado." . PHP_EOL;
                else {
                        $rs_usuario_row = pgFetchArraySafe($rs_usuario);
                        $ug_cel_ddd = $rs_usuario_row['ug_cel_ddd'];
                        $ug_cel = $rs_usuario_row['ug_cel'];
                }
        }

        //Recupera dados do credito
        if ($msg == "") {
                // Pagamentos Online Banco E-Prepag
                if ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']) {

                        $sql = "select * from tb_pag_compras pag where pag.numcompra = '" . substr($vg_pagto_num_docto, 5) . "'";
                        $rs_pagamento = SQLexecuteQuery($sql);
                        if (!$rs_pagamento || pgNumRowsSafe($rs_pagamento) == 0)
                                $msg = "Nenhum pagamento encontrado (A3)." . PHP_EOL;
                        else {
                                $rs_pagamento_row = pgFetchArraySafe($rs_pagamento);

                                $prefix = getDocPrefix($rs_pagamento_row['iforma']);
                                echo "DEBUG in processaVendaGames($venda_id, EstabCod, parametros)(iforma: " . $rs_pagamento_row['iforma'] . ", prefix: '" . $prefix . "')" . PHP_EOL;

                                $bol_documento = $prefix . $rs_pagamento_row['iforma'] . "_" . $rs_pagamento_row['numcompra'];
                                $ped_cod_doc_equiv = "##&&**";
                                $ped_dep_codigo = null;
                                $ped_bol_codigo = $vg_bol_codigo;
                        }
                } else {
                        $msg = "Nenhuma forma de pagamento existente." . PHP_EOL;
                        echo "Sem forma pagamento definida '$vg_pagto_tipo'" . PHP_EOL;
                }
        }

        //Usuario backoffice
        $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
        if ($parametros['PROCESS_AUTOM'] == '1')
                $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

        $data_corrente = date("Y/m/d");
        $hora_corrente = date("H:i:s");

        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
        }

        if ($msg == "") {

                //Realiza uma venda para cada item de cada produto
                while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                        $vgm_id = $rs_venda_modelos_row['vgm_id'];
                        $vgm_valor = $rs_venda_modelos_row['vgm_valor'];
                        $vgm_qtde = $rs_venda_modelos_row['vgm_qtde'];
                        $vgm_opr_codigo = $rs_venda_modelos_row['vgm_opr_codigo'];
                        $vgm_pin_valor = $rs_venda_modelos_row['vgm_pin_valor'];
                        $vgm_game_id = $rs_venda_modelos_row['vgm_game_id'];

                        //Realiza n qtde de venda de pins
                        for ($i = 0; $i < $vgm_qtde; $i++) {
                                // Processa mais um PIN

                        }
                }
        }

        //VENDA GAMES
        //---------------------------------------------------------------------------------------------------
        //atualiza status
        if ($msg == "") {
                $sql = "update tb_venda_games set 
                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'], "") . "
                                where vg_id = " . $venda_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar venda." . PHP_EOL;
        }

        //atualiza status em tabela pagamentos
        if ($msg == "") {
                if ($vg_pagto_tipo == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']) {
                        $sql = "update tb_pag_compras set status_processed=1 where idvenda=" . $venda_id . ";";
                        echo "DEBUG EPP (atualiza status_processed=1, vendaid = $venda_id): " . $sql . PHP_EOL;
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret)
                                $msg_not_relevant = "Erro ao conciliar venda (status_processed not set to '1')." . PHP_EOL;
                }
        }


        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transa��o." . PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;
        }

        return $msg;
}


function processaVendaGames($venda_id, $EstabCod, $parametros)
{

        $msg = "";
        $vg_pagto_tipo = null;

        $fileLog = @fopen("/www/arquivos_gerados/logs/log_vendaPIX.txt", "a+");
        if ($fileLog) {
        writeLogVendaPixSafe($fileLog, "ID VENDA PROCESSA VENDA: " . $venda_id . "\n");
        }

        // Levanta venda de Campeonato
        $b_isVendaCampeonato = isVendaCampeonato($venda_id);
        gravaLog_TMP("Testing Campeonato processaVendaGames - " . date("Y-m-d H:i:s") . "." . PHP_EOL . "  " . (($b_isVendaCampeonato) ? " - Venda de Campeonato" : "Venda normal") . " (vg_id = $venda_id)" . PHP_EOL);

        //Recupera a venda
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg " .
                        "where vg.vg_id = " . $venda_id;
                $rs_venda = SQLexecuteQuery($sql);
                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
                else {
                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
                        $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_dep_codigo = $rs_venda_row['vg_dep_codigo'];
                        $vg_bol_codigo = $rs_venda_row['vg_bol_codigo'];

                        //valida status
                        if ($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'])
                                $msg = "Pagamento ainda n�o esta confirmado (A)." . PHP_EOL;
                }
        }

        //Recupera modelos
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg " .
                        "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                        "where vg.vg_id = " . $venda_id;
                $rs_venda_modelos = SQLexecuteQuery($sql);
                if (!$rs_venda_modelos || pgNumRowsSafe($rs_venda_modelos) == 0)
                        $msg = "Nenhum produto encontrado (PVG_1)." . PHP_EOL;
        }

        //Recupera dados do usuario
        if ($msg == "") {
                $sql = "select * from usuarios_games ug " .
                        "where ug.ug_id = " . $vg_ug_id;
                $rs_usuario = SQLexecuteQuery($sql);
                if (!$rs_usuario || pgNumRowsSafe($rs_usuario) == 0)
                        $msg = "Nenhum cliente encontrado." . PHP_EOL;
                else {
                        $rs_usuario_row = pgFetchArraySafe($rs_usuario);
                        $ug_cel_ddd = $rs_usuario_row['ug_cel_ddd'];
                        $ug_cel = $rs_usuario_row['ug_cel'];
                        $ug_email = $rs_usuario_row['ug_email'];
                }
        }

        //Recupera dados do credito
        if ($msg == "") {
                //Deposito
                if ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']) {

                        $sql = "select * from depositos_pendentes dep " .
                                "where dep.dep_codigo = " . $vg_dep_codigo;
                        $rs_deposito = SQLexecuteQuery($sql);
                        if (!$rs_deposito || pgNumRowsSafe($rs_deposito) == 0)
                                $msg = "Nenhum deposito encontrado." . PHP_EOL;
                        else {
                                $rs_deposito_row = pgFetchArraySafe($rs_deposito);
                                $dep_documento = $rs_deposito_row['dep_documento'];
                                $ped_cod_doc_equiv = $dep_documento;
                                $ped_dep_codigo = $vg_dep_codigo;
                                $ped_bol_codigo = null;
                        }

                        //Boleto
                } elseif ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) {

                        $sql = "select * from boletos_pendentes bol " .
                                "where bol.bol_codigo = " . $vg_bol_codigo;
                        $rs_boleto = SQLexecuteQuery($sql);
                        if (!$rs_boleto || pgNumRowsSafe($rs_boleto) == 0)
                                $msg = "Nenhum boleto encontrado." . PHP_EOL;
                        else {
                                $rs_boleto_row = pgFetchArraySafe($rs_boleto);
                                $bol_documento = $rs_boleto_row['bol_documento'];
                                $ped_cod_doc_equiv = $bol_documento;
                                $ped_dep_codigo = null;
                                $ped_bol_codigo = $vg_bol_codigo;
                        }
                        // Pagamentos Online Bradesco: D�bito/Transfer�ncia - Banco do Brasil
                } elseif (b_IsPagtoOnline($vg_pagto_tipo)) {

                        $sql = "select * from tb_pag_compras pag where pag.numcompra = '" . substr($vg_pagto_num_docto, 5) . "'";
                        $rs_pagamento = SQLexecuteQuery($sql);
                        if (!$rs_pagamento || pgNumRowsSafe($rs_pagamento) == 0)
                                $msg = "Nenhum pagamento encontrado (A1)." . PHP_EOL;
                        else {
                                $rs_pagamento_row = pgFetchArraySafe($rs_pagamento);

                                $prefix = getDocPrefix($rs_pagamento_row['iforma']);
                                $bol_documento = $prefix . $rs_pagamento_row['iforma'] . "_" . $rs_pagamento_row['numcompra'];
                                $ped_cod_doc_equiv = "##&&**";
                                $ped_dep_codigo = null;
                                $ped_bol_codigo = null;
                        }
                } else {
                        $msg = "Nenhuma forma de pagamento existente." . PHP_EOL;
                }
        }

        //Usuario backoffice
        $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : null);
        if ($parametros['PROCESS_AUTOM'] == '1')
                $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

        $data_corrente = date("Y/m/d");
        $hora_corrente = date("H:i:s");

        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
        }

        if ($b_isVendaCampeonato) {
                gravaLog_TMP("Testing Campeonato Boleto - " . date("Y-m-d H:i:s") . "." . PHP_EOL . "  Venda de Campeonato - Sem Venda de PINs , (vg_id = $venda_id)" . PHP_EOL);
        } else {

                if ($msg == "") {

                        //Realiza uma venda para cada item de cada produto
                        while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                                $vgm_id = $rs_venda_modelos_row['vgm_id'];
                                $vgm_valor = $rs_venda_modelos_row['vgm_valor'];
                                $vgm_qtde = $rs_venda_modelos_row['vgm_qtde'];
                                $vgm_opr_codigo = $rs_venda_modelos_row['vgm_opr_codigo'];
                                $vgm_pin_valor = $rs_venda_modelos_row['vgm_pin_valor'];
                                $vgm_game_id_alawar = $rs_venda_modelos_row['vgm_game_id_alawar'];
                                $vgm_pin_request = $rs_venda_modelos_row['vgm_pin_request'];
                                $produto_operadora = $rs_venda_modelos_row['vgm_ogp_id'];

                                // Teste se o PIN n�o � de requisi��o
                                if ($vgm_pin_request == 0) {

                                        $verificaExist = SQLexecuteQuery("SELECT COUNT(*) AS qtde FROM tb_venda_games_modelo_pins WHERE vgmp_vgm_id = $vgm_id;");

                                        // Verifique se a consulta foi bem-sucedida e obtemos um recurso de resultado
                                        if ($verificaExist) {
                                                // Extrai o valor da contagem usando pg_fetch_assoc
                                                $row = pgFetchAssocSafe($verificaExist);
                                                $qtde = $row['qtde'];
                                                if ($qtde >= $vgm_qtde) {
                                                        continue;
                                                }
                                        }

                                        //Realiza n qtde de venda de pins
                                        for ($i = 0; $i < $vgm_qtde; $i++) {

                                                //PINS
                                                //Verifica se � EPP Cash e Se � valor vari�vel
                                                if ($vgm_opr_codigo == 49 && $produto_operadora == 560) {
                                                        $geraPinEpp = new GeraPinVariavel(
                                                                $vgm_pin_valor,
                                                                49,
                                                                2,
                                                                1
                                                        );
                                                        $pin_gerado = $geraPinEpp->gerar();

                                                        $sql = "
                                                                    UPDATE pins SET
                                                                        pin_desc       = 'Gamer, produto: $produto_operadora',
                                                                        pin_status      = '3',
                                                                        pin_celular     = '" . str_replace('-', '', $ug_cel) . "',
                                                                        pin_ddd         = " . ($ug_cel_ddd ?: 0) . ",
                                                                        pin_datavenda   = '$data_corrente',
                                                                        pin_datapedido  = '$data_corrente',
                                                                        pin_horavenda   = '$hora_corrente',
                                                                        pin_horapedido  = '$hora_corrente',
                                                                        pin_est_codigo  = '$EstabCod',
                                                                        pin_validade    = CURRENT_DATE + INTERVAL '2 months'
                                                                    WHERE pin_codinterno = $pin_gerado
                                                                ";
                                                        $ret = SQLexecuteQuery($sql);
                                                        if (!$ret)
                                                                $msg = "Erro ao atualizar tabela de pins (3212)." . PHP_EOL;

                                                        $sql = "update tb_venda_games_modelo set 
							vgm_pin_codinterno = coalesce(vgm_pin_codinterno,'') || '" . $pin_gerado . ",' 
								where vgm_id = '" . $vgm_id . "'";
                                                        $ret = SQLexecuteQuery($sql);
                                                        if (!$ret)
                                                                $msg = "Erro ao atualizar pin no modelo vendido." . PHP_EOL;


                                                        $sql = "insert into tb_venda_games_modelo_pins (vgmp_vgm_id, vgmp_pin_codinterno) values (" . $vgm_id . "," . $pin_gerado . ")";
                                                        $ret = SQLexecuteQuery($sql);
                                                        if (!$ret)
                                                                $msg = "Erro ao associar pin no modelo vendido." . PHP_EOL;
                                                } else {

                                                        //---------------------------------------------------------------------------------------------------
                                                        // Captura o primeiro pin v�lido
                                                        if ($msg == "") {

                                                                $sqlEstoque = "select count(*) as qtde from pins where opr_codigo = " . $vgm_opr_codigo . " and pin_status = '1' and pin_canal = 's' and pin_valor = " . $vgm_pin_valor . ";";
                                                                $estoqueR = SQLexecuteQuery($sqlEstoque);
                                                                $estoqueNoBanco = pgFetchArraySafe($estoqueR);
                                                                $temEstoque = (isset($estoqueNoBanco["qtde"]) && $estoqueNoBanco["qtde"] > 10) ? true : false;

                                                                // Executa uma verifica��o se o a senha do pin � zerada, se for exibe o campo pin_caracter	
                                                                if ($temEstoque) {
                                                                        $sql = "select * from pins
									        where opr_codigo = " . $vgm_opr_codigo . "
									        and pin_status = '1'
									        and pin_canal = 's'
									        and pin_valor = " . $vgm_pin_valor . "
									        order by pin_codinterno asc, pin_serial asc
									        limit 1 offset 10";
                                                                } else {
                                                                        $sql = "select * from pins
									        where opr_codigo = " . $vgm_opr_codigo . "
										and pin_status = '1' 
                                                                                and pin_canal = 's'
										and pin_valor = " . $vgm_pin_valor . "
										order by pin_codinterno asc, pin_serial asc
										limit 1;";
                                                                }

                                                                $rs_pins = SQLexecuteQuery($sql);
                                                                if (!$rs_pins || pgNumRowsSafe($rs_pins) == 0)
                                                                        $msg = "Nenhum pin encontrado." . PHP_EOL;
                                                                else {
                                                                        $pgpins = pgFetchArraySafe($rs_pins);
                                                                        $pin_codinterno = $pgpins['pin_codinterno'];
                                                                        $pin_valor = $pgpins['pin_valor'];
                                                                        $pin_serial = $pgpins['pin_serial'];
                                                                        $pin_codigo = $pgpins['pin_codigo'];
                                                                }
                                                        } //end if($msg == "")

                                                        // Atualiza a tabela de pins		
                                                        if ($msg == "") {
                                                                $ug_cel_ddd = (trim($ug_cel_ddd) == "") ? 0 : trim($ug_cel_ddd);
                                                                $periodoValidade = ($vgm_opr_codigo == 166) ? "INTERVAL '60 days'" : "INTERVAL '2 months'";

                                                                $sql = "
                                                                    UPDATE pins SET
                                                                        pin_desc       = 'Gamer, produto: $produto_operadora',
                                                                        pin_status      = '3',
                                                                        pin_celular     = '" . str_replace('-', '', $ug_cel) . "',
                                                                        pin_ddd         = $ug_cel_ddd,
                                                                        pin_datavenda   = '$data_corrente',
                                                                        pin_datapedido  = '$data_corrente',
                                                                        pin_horavenda   = '$hora_corrente',
                                                                        pin_horapedido  = '$hora_corrente',
                                                                        pin_est_codigo  = '$EstabCod',
                                                                        pin_validade    = CURRENT_DATE + $periodoValidade
                                                                    WHERE pin_codinterno = '$pin_codinterno'
                                                                ";
                                                                $ret = SQLexecuteQuery($sql);
                                                                if (!$ret)
                                                                        $msg = "Erro ao atualizar tabela de pins (3212)." . PHP_EOL;
                                                        }

                                                        // Atualiza o serial do pin no modelo vendido
                                                        if ($msg == "") {
                                                                $sql = "update tb_venda_games_modelo set 
									vgm_pin_codinterno = coalesce(vgm_pin_codinterno,'') || '" . $pin_codinterno . ",' 
									where vgm_id = '" . $vgm_id . "'";
                                                                $ret = SQLexecuteQuery($sql);
                                                                if (!$ret)
                                                                        $msg = "Erro ao atualizar pin no modelo vendido." . PHP_EOL;
                                                        }

                                                        if ($msg == "") {
                                                                $sql = "insert into tb_venda_games_modelo_pins (vgmp_vgm_id, vgmp_pin_codinterno) values (" . $vgm_id . "," . $pin_codinterno . ")";
                                                                $ret = SQLexecuteQuery($sql);
                                                                if (!$ret)
                                                                        $msg = "Erro ao associar pin no modelo vendido." . PHP_EOL;
                                                        }
                                                }

                                                // Alawar
                                                if ($msg == "") {
                                                        gravaLog_Debug(str_repeat("*", 80) . PHP_EOL . "OPR (opr_codigo = $vgm_opr_codigo) -> opr_codigo_Alawar = " . $GLOBALS['opr_codigo_Alawar'] . "" . PHP_EOL);
                                                        if ($vgm_opr_codigo == $GLOBALS['opr_codigo_Alawar']) {
                                                                gravaLog_Debug("IS ALAWAR " . PHP_EOL);

                                                                $certificateID = $pin_codigo;        //'1919823594123';
                                                                $email = $ug_email;        //'fabioss@e-prepag.com.br';
                                                                $gameID = $vgm_game_id_alawar;        //'3876'; // Farm Frenzy: Ancient Rome 
                                                                $activationKeyAlawar = '';
                                                                $errorsAlawar = '';

                                                                gravaLog_Debug("certificateID: '$certificateID', AFFILIATE_PID_ALAWAR: '" . AFFILIATE_PID_ALAWAR . "', AFFILIATE_SECRET_KEY: '" . AFFILIATE_SECRET_KEY . "', AFFILIATE_LOCALE_ALAWAR: '" . AFFILIATE_LOCALE_ALAWAR . "', gameID: $gameID" . PHP_EOL);
                                                                $objalawar = new AlawarAPI($certificateID, AFFILIATE_PID_ALAWAR, $email, AFFILIATE_SECRET_KEY, AFFILIATE_LOCALE_ALAWAR, $gameID);
                                                                $objalawar->Execute();
                                                                if ($objalawar->foundErrors()) {
                                                                        $errorsAlawarMsg = $objalawar->getErrors();
                                                                        $msg = $errorsAlawarMsg;
                                                                } else {
                                                                        $activationKeyAlawar = $objalawar->getGameActivationKey();
                                                                        if (isset($ERRORS_ALAWAR_ID["NO_ERROR"])) {
                                                                                $errorsAlawarMsg = $ERRORS_ALAWAR_ID["NO_ERROR"];
                                                                        } else {
                                                                                $errorsAlawarMsg = "";
                                                                        }
                                                                }

                                                                gravaLog_Debug("activationKeyAlawar: '$activationKeyAlawar', errorsAlawarMsg: '$errorsAlawarMsg'" . PHP_EOL . PHP_EOL);
                                                                if ($activationKeyAlawar == "") {
                                                                        $msg = "Concilia��o Alawar - Empty Activation key" . PHP_EOL;
                                                                }
                                                        }
                                                }
                                        } //end for
                                } //end if($vgm_pin_request == 0)
                        }
                }
        }

        //VENDA GAMES
        //---------------------------------------------------------------------------------------------------
        //atualiza status
        if ($msg == "") {
                $sql = "update tb_venda_games set 
                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'], "") . "
                                where vg_id = " . $venda_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar venda." . PHP_EOL;
        }

        writeLogVendaPixSafe($fileLog, "ATUALIZA��O STATUS VENDA PARA '4' / " . $venda_id . " \n");

        //atualiza status em tabela pagamentos para pagamentos online
        if ($msg == "") {

                if (b_IsPagtoOnline($vg_pagto_tipo)) {

                        $sql = "update tb_pag_compras set status_processed=1 where idvenda=" . $venda_id . ";";
                        echo "DEBUG C (status_processed = 1, idvenda = $venda_id): " . $sql . PHP_EOL;

                        $ret = SQLexecuteQuery($sql);
                        if (!$ret)
                                $msg_not_relevant = "Erro ao conciliar venda (status_processed not set to '1')." . PHP_EOL;
                }
        }

        closeLogVendaPixSafe($fileLog);

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transa��o." . PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;
        }

        return $msg;
} //end function processaVendaGames

function processaEmailVendaGames($venda_id, $parametros)
{

        global $raiz_do_projeto;
        $msg = "";
        $isExpressMoney = false;

        $fileLog = @fopen("/www/arquivos_gerados/logs/log_vendaPIX.txt", "a+");
        if ($fileLog) {
        writeLogVendaPixSafe($fileLog, "ID VENDA PROCESSA VENDA EMAIL: " . $venda_id . "\n");
        }


        // Variavel teste se N�O tem PIN de Requisi��o
        $testeNaoRequisicao = TRUE;

        // Variavel teste se trata de Reenvio ou PIN por requisi��o
        $testeSubject = TRUE;

        //Recupera a venda
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg " .
                        "where vg.vg_id = " . $venda_id;
                $rs_venda = SQLexecuteQuery($sql);
                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
                else {
                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_ex_email = $rs_venda_row['vg_ex_email'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_integracao_parceiro_origem_id = $rs_venda_row['vg_integracao_parceiro_origem_id'];
                        //valida status
                        if ($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] && $vg_ultimo_status != $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
                                $msg = "Processamento ainda n�o realizado." . PHP_EOL;
                        }

                        // N�o envia emails para vendas de integra��o
                        if (strlen($vg_integracao_parceiro_origem_id) > 0) {
                                if (!isset($vg_id))
                                        $vg_id = "";
                                $msg = "N�o enviar emails para vendas de Integra��o (vg_id: $vg_id, store_id: '$vg_integracao_parceiro_origem_id')." . PHP_EOL;
                        }
                }
        }

        //Recupera modelos
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg " .
                        "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                        "where vg.vg_id = " . $venda_id;

                $rs_venda_modelos = SQLexecuteQuery($sql);
                if (!$rs_venda_modelos || pgNumRowsSafe($rs_venda_modelos) == 0)
                        $msg = "Nenhum produto encontrado (PEVG_1)." . PHP_EOL;
        }

        //Recupera dados do usuario
        if ($msg == "") {

                $sql = "select * from usuarios_games ug " .
                        "where ug.ug_id = " . $vg_ug_id;
                $rs_usuario = SQLexecuteQuery($sql);
                if (!$rs_usuario || pgNumRowsSafe($rs_usuario) == 0)
                        $msg = "Nenhum cliente encontrado." . PHP_EOL;
                else {
                        $rs_usuario_row = pgFetchArraySafe($rs_usuario);
                        $ug_email = $rs_usuario_row['ug_email'];
                        $ug_sexo = $rs_usuario_row['ug_sexo'];
                        $ug_nome = $rs_usuario_row['ug_nome'];
                        $ug_cpf = $rs_usuario_row['ug_cpf'];
                        $ug_endereco = $rs_usuario_row['ug_endereco'];
                        $ug_numero = $rs_usuario_row['ug_numero'];
                        $ug_complemento = $rs_usuario_row['ug_complemento'];
                        $ug_bairro = $rs_usuario_row['ug_bairro'];
                        $ug_cidade = $rs_usuario_row['ug_cidade'];
                        $ug_estado = $rs_usuario_row['ug_estado'];
                        $ug_cep = $rs_usuario_row['ug_cep'];
                }
        }

        if ($vg_ug_id == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']) {
                $isExpressMoney = true;
                $ug_email = $vg_ex_email;
                $ug_sexo = null;
                $ug_nome = null;
        }
        $s_opr_codigo = "";

        // Levanta venda de Campeonato
        $b_isVendaCampeonato = isVendaCampeonato($venda_id);
        if ($b_isVendaCampeonato) {

                $sql = "select * from tb_venda_games vg " .
                        "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                        "where vg.vg_id = " . $venda_id;
                $rs_venda_modelos_campeonato = SQLexecuteQuery($sql);
                $rs_venda_modelos_campeonato_row = pgFetchArraySafe($rs_venda_modelos_campeonato);

                // Define operadora e produtos
                $vgm_opr_codigo = $rs_venda_modelos_campeonato_row['vgm_opr_codigo'];
                $vgm_nome_produto = $rs_venda_modelos_campeonato_row['vgm_nome_produto'];
                $vgm_valor = $rs_venda_modelos_campeonato_row['vgm_valor'];
        } else {

                if ($msg == "") {

                        $msgEmailSenhas = "";
                        $aux_lista_prods = "<table cellspacing='0' cellpadding='0' width='100%' style='font: normal 14px arial, sans-serif;'>" . PHP_EOL;

                        // Obtem o PIN vendido para cada item de cada produto
                        while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                                $vgm_id = $rs_venda_modelos_row['vgm_id'];
                                $vgm_nome_produto = $rs_venda_modelos_row['vgm_nome_produto'];
                                $vgm_nome_modelo = $rs_venda_modelos_row['vgm_nome_modelo'];
                                $vgm_valor = $rs_venda_modelos_row['vgm_valor'];
                                $vgm_qtde = $rs_venda_modelos_row['vgm_qtde'];
                                $vgm_pin_codinterno = trim($rs_venda_modelos_row['vgm_pin_codinterno']);
                                $vgm_opr_codigo = $rs_venda_modelos_row['vgm_opr_codigo'];
                                $vgm_pin_request = $rs_venda_modelos_row['vgm_pin_request'];
                                $vgm_ogp_id = $rs_venda_modelos_row['vgm_ogp_id'];
                                $vgm_valor_pin = $rs_venda_modelos_row['vgm_valor'];
                                $vgm_ogpm_id = $rs_venda_modelos_row['vgm_ogpm_id'];

                                //Gerando o registro de requisi��o de PIN por Webservice
                                if ($vgm_pin_request > 0) {
                                        $testeSubject = FALSE;
                                        //Para produtos BHN
                                        if ($vgm_pin_request == 1) {


                                                if ($vgm_opr_codigo == 159) {

                                                        $sql = "select ogp_valor_minimo,ogp_valor_maximo,ogp_nome from tb_operadora_games_produto where ogp_id=" . $vgm_ogp_id . ";";
                                                        $rs_bhn_variavel = SQLexecuteQuery($sql);
                                                        $rs_bhn_variavel_row = pgFetchArraySafe($rs_bhn_variavel);
                                                        //Bloco para registro do pedido
                                                        $sql = "select ogpm_pin_resquest_id,ogpm_provider_id,ogpm_cod_epay
													from tb_operadora_games_produto_modelo 
													where ogpm_id = $vgm_ogpm_id ";
                                                        if (is_null($rs_bhn_variavel_row['ogp_valor_minimo']) && is_null($rs_bhn_variavel_row['ogp_valor_maximo']))
                                                                $sql .= " and ogpm_valor = " . $vgm_valor_pin . ";";
                                                        $rs_bhn = SQLexecuteQuery($sql);
                                                        $rs_bhn_row = pgFetchArraySafe($rs_bhn);

                                                        include_once "/www/e-pay/Epay.php";
                                                        $epay = new Epay();

                                                        //if(!$epay->verifyRequest($venda_id)){
                                                        //$testeNaoRequisicao = FALSE;
                                                        for ($i = 1; $i <= ($vgm_qtde * 1); $i++) {
                                                                $dados_pedido = ["code" => $rs_bhn_row['ogpm_pin_resquest_id'], "shopid" => $rs_bhn_row['ogpm_provider_id'], "model" => $vgm_id, "operator" => $vgm_opr_codigo, "retailerid" => $rs_bhn_row['ogpm_cod_epay'], "type_sale" => "USUARIO", "value" => $vgm_valor_pin, "sale" => $venda_id, "name_prod" => $rs_bhn_variavel_row['ogp_nome']];
                                                                $msg = $epay->sale("DIRECT", $dados_pedido);

                                                                if ($msg != "") {
                                                                        break;
                                                                }
                                                        }

                                                        //continue;
                                                        $sql = "select vgm_pin_codinterno from tb_venda_games vg " .
                                                                "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                                                                "where vgm.vgm_id = " . $vgm_id;
                                                        $rs_mod = SQLexecuteQuery($sql);
                                                        $pin_mod_epay = pgFetchArraySafe($rs_mod);
                                                        $vgm_pin_codinterno = $pin_mod_epay["vgm_pin_codinterno"];

                                                        //}

                                                } else {

                                                        require_once RAIZ_DO_PROJETO . "bhn/egift/config.inc.bhn.egift.php";
                                                        //Dados Pedido de Verifica��o e Registro do Pedido de requisi��o
                                                        $dadosPedido = array(
                                                                'vg_id' => $venda_id,
                                                                'vgm_id' => $vgm_id,
                                                                'opr_codigo' => $vgm_opr_codigo
                                                        );
                                                        if (!classRegistroPinRequest::verificaExisteRegistroBHN($dadosPedido)) {
                                                                //Alterando variavel de Teste para indicar que teve PIN de Resquissi��o
                                                                $testeNaoRequisicao = FALSE;
                                                                //Bloco de verifica��o de produto de valro vari�vel
                                                                $sql = "select ogp_valor_minimo,ogp_valor_maximo from tb_operadora_games_produto where ogp_id=" . $vgm_ogp_id . ";";
                                                                $rs_bhn_variavel = SQLexecuteQuery($sql);
                                                                $rs_bhn_variavel_row = pgFetchArraySafe($rs_bhn_variavel);
                                                                //Bloco para registro do pedido
                                                                $sql = "select ogpm_pin_resquest_id 
															from tb_operadora_games_produto_modelo 
															where ogpm_id = $vgm_ogpm_id ";
                                                                if (is_null($rs_bhn_variavel_row['ogp_valor_minimo']) && is_null($rs_bhn_variavel_row['ogp_valor_maximo']))
                                                                        $sql .= " and ogpm_valor = " . $vgm_valor_pin . ";";
                                                                $rs_bhn = SQLexecuteQuery($sql);
                                                                $rs_bhn_row = pgFetchArraySafe($rs_bhn);
                                                                for ($i = 1; $i <= ($vgm_qtde * 1); $i++) {
                                                                        $parametrosRequest = array(
                                                                                'productConfigurationId' => $rs_bhn_row['ogpm_pin_resquest_id'],
                                                                                'giftAmount' => $vgm_valor_pin,
                                                                                'registro' => TRUE
                                                                        );
                                                                        $rs_api = new classGenerateeGift($parametrosRequest);
                                                                        $rs_api->registroPedido($dadosPedido);
                                                                } //end for
                                                                continue;
                                                        } //end if(registroBHN::verificaExisteRegistro($dadosPedido))

                                                }
                                        } //end if($vgm_pin_request == 1)
                                        //FIM Para produtos BHN

                                } //end if($vgm_pin_request > 0)

                                //verifica se o(s) pin(s) foram associados ao modelo
                                if ($vgm_pin_codinterno == "") {
                                        $msg = "Codigo de PIN n�o associado." . PHP_EOL;
                                        break;
                                }

                                //elimina ultima virgula
                                if (substr($vgm_pin_codinterno, -1) == ",")
                                        $vgm_pin_codinterno = substr($vgm_pin_codinterno, 0, strlen($vgm_pin_codinterno) - 1);

                                //separa os ids dos pins
                                $vgm_pin_codinternoAr = explode(",", $vgm_pin_codinterno);

                                //verifica se o(s) pin(s) foram associados ao modelo
                                if (count($vgm_pin_codinternoAr) == 0) {
                                        $msg = "Codigo de PIN n�o encontrado." . PHP_EOL;
                                        break;
                                }

                                $b_Have_Alawar = false;
                                $b_Only_Alawar = true;
                                // Envia email para n qtde de venda de pins
                                for ($i = 0; $i < count($vgm_pin_codinternoAr); $i++) {

                                        //PINS
                                        //---------------------------------------------------------------------------------------------------
                                        // Obtem o PIN vendido
                                        if ($msg == "") {
                                                // Executa uma verifica��o se o a senha do pin � zerada, se for exibe o campo pin_caracter	
                                                $sql = "SELECT *, 
                                                               CASE 
                                                                    WHEN (pin_codigo IS NULL OR pin_codigo = '') AND (pin_caracter IS NULL OR pin_caracter = '') THEN pin_serial
                                                                    WHEN pin_codigo = '0000000000000000' THEN pin_caracter
                                                                    ELSE pin_codigo
                                                               END AS case_serial
                                                        FROM pins
                                                        WHERE pin_codinterno = " . $vgm_pin_codinternoAr[$i] . ";";

                                                $rs_pin = SQLexecuteQuery($sql);
                                                if (!$rs_pin || pgNumRowsSafe($rs_pin) == 0) {
                                                        $msgEmailSenhas .= "PIN n�o encontrado. [" . $GLOBALS['opr_codigo_Alawar'] . "]<br>";
                                                        $aux_lista_prods .= "<tr><td><font face='arial' color='#304D77'>PIN n�o encontrado. [" . $GLOBALS['opr_codigo_Alawar'] . "]</font></td></tr>";
                                                        $msg = "PIN n�o encontrado." . PHP_EOL;
                                                } else {
                                                        $pgpin = pgFetchArraySafe($rs_pin);
                                                        $pin_serial = $pgpin['pin_serial'];
                                                        $case_serial = $pgpin['case_serial'];
                                                        $opr_codigo = $pgpin['opr_codigo'];
                                                        $s_opr_codigo .= (($s_opr_codigo) ? "," : "") . $opr_codigo;

                                                        if ($opr_codigo == 44) {        //	opr_codigo = 44 -> 'Axeso5'
                                                                // o carregaemnto no estoque para Axeso5 est� trocado -> ent�o troca de novo aqui
                                                                $pin_serial = $pgpin['pin_serial'];
                                                                $case_serial = $pgpin['case_serial'];
                                                        }

                                                        // Alawar - obtem o activation key ou indica o erro com o certificado
                                                        if ($vgm_opr_codigo == $GLOBALS['opr_codigo_Alawar']) {        // 55  

                                                                $sql_alawar = "select pa_activation_key from pins_alawar where pa_certificate_id = '$case_serial'";

                                                                $rs = SQLexecuteQuery($sql_alawar);
                                                                if ($rs && pgNumRowsSafe($rs) > 0) {
                                                                        $rs_row = pgFetchArraySafe($rs);
                                                                        $case_serial = $rs_row[0];
                                                                } else {
                                                                        $case_serial = "CERT_" . $case_serial;
                                                                }
                                                                $b_Have_Alawar = true;
                                                        } else {
                                                                $b_Only_Alawar = false;
                                                        }


                                                        // Formata��o de t�tulo de senha
                                                        $labSenha = "PIN";
                                                        if ($opr_codigo == 16)
                                                                $labSenha = "Habbo Cr�dito";        // Habbo
                                                        if ($opr_codigo == 38)
                                                                $labSenha = "Senha (C�digo de Presente)";        // Stardoll
                                                        if ($opr_codigo == 37)
                                                                $labSenha = "C�d de seguran�a";        // Softnyx
                                                        if ($opr_codigo == 31)
                                                                $labSenha = "";        // GPotato
                                                        if ($opr_codigo == 31)
                                                                $labSenhaExtra = " (Nro S�rie + Senha)";        // GPotato
                                                        if ($opr_codigo == 33)
                                                                $labSenha = "PIN Password";        // NDoors
                                                        if ($opr_codigo == 44)
                                                                $labSenha = "Serial";        // Axeso5

                                                        // Formata��o de t�tulo do No de Serie
                                                        $labSerie = "No S�rie";
                                                        if ($opr_codigo == 37)
                                                                $labSerie = "No do cart�o";        // Softnyx
                                                        if ($opr_codigo == 44)
                                                                $labSerie = "Pin";        // Axeso5
                                                        if ($opr_codigo == 34)
                                                                $labSerie = "C�digo";        // Webzen
                                                        if ($opr_codigo == 74 || $opr_codigo == 75 || $opr_codigo == 76 || $opr_codigo == 77)
                                                                $labSerie = "C�digo"; //Eletronic Arts

                                                        // Formata��o do Serial
                                                        if ($opr_codigo == 13)
                                                                $case_serial = wordwrap($case_serial, 4, " ", true);        // Ongame
                                                        if ($opr_codigo == 31)
                                                                $labSerie = "Pin Eletr�nico";        // GPotato
                                                        if ($opr_codigo == 33)
                                                                $labSerie = "PIN Code";        // NDoors
                                                        if ($opr_codigo == 125)
                                                                $labSenha = "Habbo Cr�dito";        // Habbo 2
                                                }
                                        }

                                        if ($msg == "") {

                                                $aux_lista_prods .= "<tr>
                                                                                            <td><font face='arial' color='#304D77'>Produto</font></td>
                                                                                            <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                                                                            <td><font face='arial' color='#304D77'>" . $vgm_nome_produto . (trim($vgm_nome_modelo) == "" ? "" : " - " . $vgm_nome_modelo) . "</font></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                            <td><font face='arial' color='#304D77'>Valor</font></td>
                                                                                            <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                                                                            <td><font face='arial' color='#304D77'>" . number_format($vgm_valor, 2, ',', '.') . "<font></td>
                                                                                    </tr>";
                                                if ($opr_codigo == 31) {        // GPotato
                                                        $aux_lista_prods .= "<tr>
                                                                                            <td><font face='arial' color='#304D77' size='4'><b>" . $labSerie . "</b></font></td>
                                                                                            <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                                                                            <td><font face='arial' color='#304D77' size='4'><b>" . $pin_serial . " - " . $case_serial . " " . $labSenhaExtra . "</b></font></td>
                                                                                    </tr>";
                                                } else {
                                                        if (($opr_codigo != 16) && ($opr_codigo != $GLOBALS['opr_codigo_Alawar'])) {
                                                                $aux_lista_prods .= "<tr>
                                                                                            <td><font face='arial' color='#304D77'><b>" . $labSerie . "</b></font></td>
                                                                                            <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                                                                            <td><font face='arial' color='#304D77'><b>" . $pin_serial . "</b></font></td>
                                                                                    </tr>";
                                                        }

                                                        if ($opr_codigo != 34 && $opr_codigo != 74 && $opr_codigo != 75 && $opr_codigo != 76 && $opr_codigo != 77) {
                                                                $aux_lista_prods .= "<tr>
                                                                                            <td><font face='arial' color='#304D77' size='4'><b>" . $labSenha . "</b></font></td>
                                                                                            <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                                                                            <td><font face='arial' color='#304D77' size='4'><b>" . $case_serial . "</b></font></td>
                                                                                    </tr>";
                                                        }
                                                }
                                                $aux_lista_prods .= "<tr>
                                                                                            <td colspan='3'>&nbsp;</td>
                                                                                    </tr>";
                                        }
                                }
                        }
                        $aux_lista_prods .= "</table>";
                }
        }

        //USUARIO
        //---------------------------------------------------------------------------------------------------
        //envia email
        if ($msg == "" && $testeNaoRequisicao) {

                // Promo��es
                $promo_msg = "";


                if ($b_isVendaCampeonato) {
                        // Vendas de tipo Campeonato usam um template espec�fico 

                } else {
                        //Informacoes do pedido
                        $sql = "select * from tb_venda_games vg " .
                                "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                                "where vg.vg_id = " . $venda_id;
                        $rs_venda_modelos = SQLexecuteQuery($sql);
                        $aux_lista = "<table cellspacing='0' cellpadding='5' width='100%' style='font: normal 13px arial, sans-serif;'>
                                                            <tr bgcolor='#CCCCCC'>
                                                                    <td width='3'>&nbsp;</td>
                                                                    <td align='left'><b>Jogo</b></td>
                                                                    <td align='center'><b>Produto</b></td>
                                                                    <td align='center'><b>Unit.(R$)</b></td>
                                                                    <td align='center'><b>Qtde.</b></td>
                                                                    <td align='right'><b>Total(R$)</b></td>
                                                                    <td width='5'>&nbsp;</td>
                                                            </tr>";
                        $vgm_opr_codigo_list = "";
                        $qtde_total = 0;
                        $total_geral = 0;
                        $opr_codigo_contains_EPP_Cash = false;
                        $vetorIDsProdsRequest = array();
                        while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {

                                // Define operadora e produtos
                                $vgm_opr_codigo = $rs_venda_modelos_row['vgm_opr_codigo'];
                                $vg_ug_id = $rs_venda_modelos_row['vg_ug_id'];
                                $vgm_opr_codigo_list .= ((strlen($vgm_opr_codigo_list) > 0) ? "," : "") . $rs_venda_modelos_row['vgm_opr_codigo'];
                                if ($rs_venda_modelos_row['vgm_opr_codigo'] == "49")
                                        $opr_codigo_contains_EPP_Cash = true;        // marca a venda como contendo EPP Cash
                                $vgm_nome_produto = $rs_venda_modelos_row['vgm_nome_produto'];
                                $vgm_ogp_id = $rs_venda_modelos_row['vgm_ogp_id'];
                                $blEmailHabbo = (($rs_venda_modelos_row['vgm_opr_codigo'] == 16) ? true : false);
                                $blEmailVostu = (($rs_venda_modelos_row['vgm_opr_codigo'] == 35) ? true : false);
                                $blEmailVostu_MiniFazenda = (($blEmailVostu && ($rs_venda_modelos_row['vgm_nome_produto'] == "MiniFazenda")) ? true : false);
                                $blEmailVostu_Joga_Craque = (($blEmailVostu && ($rs_venda_modelos_row['vgm_nome_produto'] == "Joga Craque")) ? true : false);
                                $blEmailVostu_CafeMania = (($blEmailVostu && ($rs_venda_modelos_row['vgm_nome_produto'] == "Caf�Mania")) ? true : false);
                                $blEmailStardoll = (($rs_venda_modelos_row['vgm_opr_codigo'] == 38) ? true : false);
                                $blEmailSoftnyx = (($rs_venda_modelos_row['vgm_opr_codigo'] == 37) ? true : false);
                                $blEmailAlawar = (($rs_venda_modelos_row['vgm_opr_codigo'] == $GLOBALS['opr_codigo_Alawar']) ? true : false);
                                //	opr_codigo = 44 -> 'Axeso5'
                                $blEmailAxeso5 = (($rs_venda_modelos_row['vgm_opr_codigo'] == 44) ? true : false);
                                $blEmailEPPCash = (($rs_venda_modelos_row['vgm_opr_codigo'] == 49) ? true : false);
                                $pagto_tipo = $rs_venda_modelos_row['vg_pagto_tipo'];
                                $codigo = $rs_venda_modelos_row['vgm_id'];
                                $qtde = $rs_venda_modelos_row['vgm_qtde'];
                                $valor = $rs_venda_modelos_row['vgm_valor'];
                                $vgm_pin_request = $rs_venda_modelos_row['vgm_pin_request'];
                                $qtde_total += $qtde;
                                $total_geral += $valor * $qtde;
                                $aux_lista .= "<tr bgcolor='#E6E6E6'>
			        		<td width='3'>&nbsp;</td>
			        		<td align='left'>" . $rs_venda_modelos_row['vgm_nome_produto'] . "</td>
			        		<td align='center'>" . $rs_venda_modelos_row['vgm_nome_modelo'] . "</td>
			        		<td align='center'>" . number_format($valor, 2, ',', '.') . "</td>
			        		<td align='center'>" . $qtde . "</td>
			        		<td align='right'><b>" . number_format($valor * $qtde, 2, ',', '.') . "</b></td>
			        		<td width='5'>&nbsp;</td>
			        </tr>";
                                if ($vgm_pin_request > 0) {
                                        //Para produtos BHN
                                        if ($vgm_pin_request == 1) {
                                                $vetorIDsProdsRequest[$vgm_ogp_id] = 1;
                                        } //end if($vgm_pin_request == 1)
                                } //end if($vgm_pin_request > 0) 


                        }
                        $aux_lista .= "<tr bgcolor='#CCCCCC'>
                                                                    <td >&nbsp;</td>
                                                                    <td align='right' colspan='4'><b>Total(R$)</b></td>
                                                                    <td align='right'><b>" . number_format($total_geral, 2, ',', '.') . "</b></td>
                                                                    <td width='5'>&nbsp;</td>
                                                            </tr>
                                                            </table>";
                        if (count($vetorIDsProdsRequest) > 0) {
                                foreach ($vetorIDsProdsRequest as $key => $value) {
                                        $aux_lista_prods .= getInstrucoesPinRequest($key);
                                } //end foreach
                        } //end if(count($vetorIDsProdsRequest) > 0) 

                        // Obtem Promo��es	==============================================
                        $prom_email = $ug_email;
                        $prom_bcc = null;
                        $prom_ug_id = $vg_ug_id;
                        $prom_opr_codigos = $vgm_opr_codigo_list;
                        if (substr($prom_opr_codigos, 0, 1) == ",")
                                $prom_opr_codigos = substr($prom_opr_codigos, 1);
                        $promo_msg = getPromocaoCorrente($prom_email, $prom_ug_id, $prom_opr_codigos, $venda_id);
                        // Fim de Obtem Promo��es	==============================================
                        //Instrucoes PINs - Parte Descr - Resto
                        $msgEmail_instrucoes = get_Instructions_for_Gamer_PIN($vgm_opr_codigo, $vgm_ogp_id, $vgm_nome_produto);
                }
                if ($b_isVendaCampeonato) {
                        $stipoEmail = 'Campeonato';
                } else {
                        $stipoEmail = (($vg_ug_id == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']) ? 'CompraProcessadaEx' : 'CompraProcessada');
                }
                $objEnvioEmailAutomatico = new EnvioEmailAutomatico('G', $stipoEmail);
                $objEnvioEmailAutomatico->setListaCreditoOferta($aux_lista);
                $objEnvioEmailAutomatico->setListaProduto($aux_lista_prods);
                $objEnvioEmailAutomatico->setPedido($venda_id);
                $objEnvioEmailAutomatico->setPromocoes($promo_msg);
                $objEnvioEmailAutomatico->setInstrucoesUso($msgEmail_instrucoes);

                if ($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] && $testeSubject) {
                        $objEnvioEmailAutomatico->setSubjectAdicional("(Reenvio)");
                }

                if ($vg_ug_id != $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']) {
                        $objEnvioEmailAutomatico->setUgID($vg_ug_id);
                } else {
                        $objEnvioEmailAutomatico->setUgEmail($ug_email);
                }
                if ($blEmailAlawar) {
                        $objEnvioEmailAutomatico->setBccAdicional("wagner@e-prepag.com.br");
                }
                if ($blEmailAxeso5) {
                        $objEnvioEmailAutomatico->setBccAdicional("tamy@e-prepag.com.br");
                }

                echo $objEnvioEmailAutomatico->MontaEmailEspecifico();
        }

        //VENDA GAMES
        //---------------------------------------------------------------------------------------------------
        //atualiza status
        if ($msg == "") {
                $sql = "update tb_venda_games set ";
                if (isset($parametros['ultimo_status_obs']) && !empty($parametros['ultimo_status_obs'])) {
                        $sql .= " vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ", ";
                }
                $sql .= " vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'], "") . "
                                where vg_id = " . $venda_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao atualizar venda (ultimo_status)." . PHP_EOL;
                if ($msg == "") {
                        if (isset($parametros['PROCESS_TEST']) && ($parametros['PROCESS_TEST'] != 1)) {
                                $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
                                $sql = "update tb_venda_games set 
                                                        vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "'
                                                where vg_id = " . $venda_id;
                                $ret = SQLexecuteQuery($sql);
                                if (!$ret)
                                        $msg = "Erro ao atualizar venda (data_concilia)." . PHP_EOL;
                        }
                }

                writeLogVendaPixSafe($fileLog, "ATUALIZA��O STATUS VENDA PARA '5' / " . $venda_id . " \n");
                writeLogVendaPixSafe($fileLog, "MENSAGEM DE VENDA PROCESSA EMAIL: " . $msg . " / " . $venda_id . " \n\r");
        }

        closeLogVendaPixSafe($fileLog);

        return $msg;
} //end function processaEmailVendaGames

function enviaEmailFormatadoComProdutos($venda_id, $parametros, $cc, $bcc, $subjectEmail, $mensagem)
{

        $msg = "";
        //Recupera a venda
        $sql = "select * from tb_venda_games vg " .
                "where vg.vg_id = " . $venda_id;
        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                $msg = "Nenhuma venda encontrada." . PHP_EOL;
        else {
                $rs_venda_row = pgFetchArraySafe($rs_venda);
                $vg_ug_id = $rs_venda_row['vg_ug_id'];
        }

        //Recupera dados do usuario
        if ($msg == "") {
                $sql = "select * from usuarios_games ug " .
                        "where ug.ug_id = " . $vg_ug_id;
                $rs_usuario = SQLexecuteQuery($sql);
                if (!$rs_usuario || pgNumRowsSafe($rs_usuario) == 0)
                        $msg = "Nenhum cliente encontrado." . PHP_EOL;
        }

        return $msg;
}

function cancelaVendaGames($venda_id, $parametros)
{

        $msg = "";

        //atualiza status
        if ($msg == "") {
                $sql = "update tb_venda_games set 
                                        vg_usuario_obs = " . SQLaddFields($parametros['usuario_obs'], "s") . ",
                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'], "") . "
                                where vg_id = " . $venda_id . " ";
                echo "DEBUG Cancela Venda (vg_id = $venda_id): " . $sql . PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao cancelar venda." . PHP_EOL;
                else {
                        // Cancela pagamento desta venda, se existir
                        // e se o pagamento n�o tiver sido feito e a venda ainda n�o tiver sido processada
                        $sql = "update tb_pag_compras set 
                                                status_processed = 1,
                                                status = -1
                                        where idvenda = " . $venda_id . " and status_processed = 0 and status = 1 and tipo_cliente='M' ";
                        echo "DEBUG Cancela Pagto (atualiza status_processed=1, vendaid = $venda_id): " . $sql . PHP_EOL;

                        // Ver cancelaVendasEmPedidoEfetuado()
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret)
                                $msg = "Erro ao cancelar pagamentos de venda (venda foi cancelada)." . PHP_EOL;
                }
        }

        return $msg;
}

function descancelaVendaGames($venda_id, $parametros)
{

        $msg = "";

        //Recupera a venda
        if ($msg == "") {
                $sql = "select * from tb_venda_games vg where vg.vg_id = " . $venda_id;
                $rs_venda = SQLexecuteQuery($sql);
                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
                else {
                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];

                        //valida status
                        if ($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'])
                                $msg = "Venda n�o esta cancelada (status: '$vg_ultimo_status')." . PHP_EOL;
                }
        }


        //Recupera historico da venda
        if ($msg == "") {
                // acrescenta ", vgh_status desc" em order para que os registros de status 1 e 2 apare�a~m na ordem certa, est� retornando "1" como o segundo status do hist�rico em ordem inversa de datas, por isso descancela para status 1 e n�o para 2 (as datas de 1 e 2 s�o as mesmas)
                $sql = "select * from tb_venda_games_historico vgh 
                                 where vgh.vgh_vg_id = " . $venda_id . "
                                 order by vgh_data_inclusao desc, vgh_status desc offset 1";
                $rs_venda_historico = SQLexecuteQuery($sql);
                if (!$rs_venda_historico || pgNumRowsSafe($rs_venda_historico) == 0)
                        $msg = "Nenhum hist�rico da venda encontrado." . PHP_EOL;
                else {
                        $rs_venda_historico_row = pgFetchArraySafe($rs_venda_historico);
                        $vgh_status = $rs_venda_historico_row['vgh_status'];
                        $vgh_status = $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO']; // fixo 

                        //valida status
                        if ($vgh_status != $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'] && $vgh_status != $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO']) {
                                $msg = "Venda n�o pode ser descancelada." . PHP_EOL;
                                $msg .= "Somente vendas que estavam nos status  " .
                                        "\"" . $GLOBALS['STATUS_VENDA_DESCRICAO'][$GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO']] . "\" ou " .
                                        "\"" . $GLOBALS['STATUS_VENDA_DESCRICAO'][$GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO']] . "\"
                                                quando do cancelamento podem ser descanceladas." . PHP_EOL;
                        }
                }
        }


        //VENDA GAMES
        //---------------------------------------------------------------------------------------------------
        //atualiza status
        if ($msg == "") {
                $sql = "update tb_venda_games set 
                                        vg_usuario_obs = " . SQLaddFields($parametros['usuario_obs'], "s") . ",
                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
                                        vg_ultimo_status = " . SQLaddFields($vgh_status, "") . "
                                where vg_id = " . $venda_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao descancelar venda." . PHP_EOL;
        }

        return $msg;
}

function cancelaVendasBoletoVencido()
{

        //header
        $header = PHP_EOL . "------------------------------------------------------------------------" . PHP_EOL;
        $header .= "Cancela Vendas Boleto Vencido" . PHP_EOL;
        $header .= date('d/m/Y - H:i:s') . PHP_EOL . PHP_EOL;
        $msg = "";

        //Busca vendas com boletos vencidos
        $sql = "select vg.vg_id, bbg.bbg_data_inclusao, bbg.bbg_data_venc
                        from tb_venda_games vg 
                        inner join boleto_bancario_games bbg on bbg.bbg_vg_id = vg.vg_id
                        where (vg.vg_concilia is null or vg.vg_concilia = 0)
                                and vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "
                                and vg.vg_pagto_tipo = " . $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'] . "
                                and bbg.bbg_data_venc + interval '" . $GLOBALS['PROCESS_AUTOM_BOLETO_CANCELAMENTO_DIAS_VENCIDO'] . " day' < CURRENT_DATE ";
        $msg .= PHP_EOL . PHP_EOL;
        $rs_venda_bol_venc = SQLexecuteQuery($sql);
        if (!$rs_venda_bol_venc || pgNumRowsSafe($rs_venda_bol_venc) == 0)
                $msg = "Nenhuma venda encontrada com boleto vencido ha mais de " . $GLOBALS['PROCESS_AUTOM_BOLETO_CANCELAMENTO_DIAS_VENCIDO'] . " dias." . PHP_EOL;
        else {
                $msg .= "Encontrado(s) " . pgNumRowsSafe($rs_venda_bol_venc) . " venda(s) com boleto vencido:" . PHP_EOL;
                while ($rs_venda_bol_venc_row = pgFetchArraySafe($rs_venda_bol_venc)) {
                        $vg_id = $rs_venda_bol_venc_row['vg_id'];

                        $parametros['usuario_obs'] = "Pedido cancelado. Boleto n�o quitado 'dentro do prazo estabelecido." . PHP_EOL;
                        $parametros['ultimo_status_obs'] = "Pedido cancelado. Boleto n�o quitado dentro do prazo estabelecido." . PHP_EOL;
                        $ret = cancelaVendaGames($vg_id, $parametros);
                        $msg .= "Venda " . $vg_id . ": " . str_replace(PHP_EOL, " ", $ret) . PHP_EOL;
                }
        }
        $msg = $header . $msg . "------------------------------------------------------------------------" . PHP_EOL;

        return $msg;
}


function cancelaVendasEmPedidoEfetuado()
{

        //header
        $header = PHP_EOL . "------------------------------------------------------------------------" . PHP_EOL;
        $header .= "Cancela Vendas Em Status PEDIDO EFETUADO (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutes)" . PHP_EOL;
        $header .= date('d/m/Y - H:i:s') . PHP_EOL . PHP_EOL;
        $msg = "";


        echo $header . PHP_EOL;
        //Busca vendas com boletos vencidos
        $sql = "select vg.vg_id, vg.vg_pagto_tipo 
                from tb_venda_games vg 
                where (vg.vg_concilia is null or vg.vg_concilia = 0)
                        and vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'] . " 
                        and 
                        ( ( (vg_pagto_tipo= " . $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF'] . " or vg_pagto_tipo= " . $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'] . " ) 
                        and vg.vg_data_inclusao + interval '" . $GLOBALS['PROCESS_AUTOM_PEDIDO_EFETUADO_CANCELAMENTO_DIAS_VENCIDO'] . " day' < CURRENT_TIMESTAMP ) 
                        or
                                ( (" . getSQLWhereParaVendaPagtoOnline(false) . ") 
                                        and (
                                                        (vg.vg_data_inclusao + interval '" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutes' < CURRENT_TIMESTAMP)  
                                                ) 
                                ) 
                        ) ";

        $msg .= PHP_EOL . PHP_EOL;
        $rs_venda_venc = SQLexecuteQuery($sql);
        if (!$rs_venda_venc || pgNumRowsSafe($rs_venda_venc) == 0) {
                $msg = "Nenhuma venda encontrada com pedido vencido ha mais de " . $GLOBALS['PROCESS_AUTOM_PEDIDO_EFETUADO_CANCELAMENTO_DIAS_VENCIDO'] . " dias." . PHP_EOL;
        } else {
                $msg .= "Encontrado(s) " . pgNumRowsSafe($rs_venda_venc) . " venda(s) com pedido vencido:" . PHP_EOL;
                while ($rs_venda_venc_row = pgFetchArraySafe($rs_venda_venc)) {
                        $vg_id = $rs_venda_venc_row['vg_id'];
                        $vg_pagto_tipo = $rs_venda_venc_row['vg_pagto_tipo'];

                        switch ($vg_pagto_tipo) {
                                case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:        //   5
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:        //   6
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:        //   9
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']:        //   10
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']:        //   13
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']:        //   11
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']:        //   12
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']:        //   999
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;

                                // Pagamentos CIELO 
                                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_DEBITO']:
                                case $GLOBALS['PAGAMENTO_VISA_DEBITO_NUMERIC']:
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_VISA_CREDITO']:
                                case $GLOBALS['PAGAMENTO_VISA_CREDITO_NUMERIC']:
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_DEBITO']:
                                case $GLOBALS['PAGAMENTO_MASTER_DEBITO_NUMERIC']:
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_MASTER_CREDITO']:
                                case $GLOBALS['PAGAMENTO_MASTER_CREDITO_NUMERIC']:
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_DEBITO']:
                                case $GLOBALS['PAGAMENTO_ELO_DEBITO_NUMERIC']:
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_ELO_CREDITO']:
                                case $GLOBALS['PAGAMENTO_ELO_CREDITO_NUMERIC']:
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DINERS_CREDITO']:
                                case $GLOBALS['PAGAMENTO_DINERS_CREDITO_NUMERIC']:
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_DISCOVER_CREDITO']:
                                case $GLOBALS['PAGAMENTO_DISCOVER_CREDITO_NUMERIC']:
                                        $parametros['usuario_obs'] = "Seu pagamento n�o foi realizado dentro do prazo indicado (" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos)." . PHP_EOL;
                                        break;
                                default:
                                        $parametros['usuario_obs'] = "Seus dados de pagamento n�o foram informados corretamente." . PHP_EOL;
                                        break;
                        }
                        // Obsaerva��es antigas:
                        $parametros['ultimo_status_obs'] = "Pedido cancelado. Pedido n�o quitado dentro do prazo estabelecido." . PHP_EOL;
                        $ret = cancelaVendaGames($vg_id, $parametros);
                        $msg .= "Venda " . $vg_id . ": " . str_replace(PHP_EOL, " ", $ret) . PHP_EOL;
                }
        }

        // Cancela pagamentos sem venda cadastrada e com mais de PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO minutos
        // Este vale para todos os tipos de pagamento, n�o apenas para o Money
        $msg .= PHP_EOL . "Cancela pagamentos em aberto por mais de " . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutos" . PHP_EOL;
        $sql = "update tb_pag_compras set 
                                status_processed = 1,
                                status = -1
                        where 1=1
                                        and status_processed = 0 
                                        and status = 1 
                                        and ((datainicio + interval '" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutes' < CURRENT_TIMESTAMP))";
        echo "DEBUG Cancela pagtos por tempo (atualiza status_processed=1): " . $sql . PHP_EOL;
        $ret = SQLexecuteQuery($sql);
        if (!$ret)
                $msg .= "Erro ao cancelar pagamentos sem venda e atrasados ($sql)." . PHP_EOL;

        // Cancela vendas com pagamentos CIELO onde o pagamento est� com status -1
        $msg .= PHP_EOL . "Cancela vendas com pagamentos CIELO onde o pagamento est� com status -1" . PHP_EOL;
        $sql = "select vg.vg_id ";
        $sql .= "from tb_venda_games vg 
                                inner join tb_pag_compras p on p.idvenda = vg.vg_id 
                        where 1=1
                        and (vg.vg_concilia is null or vg.vg_concilia = 0)
                        and vg.vg_ultimo_status = 1 
                        and p.status = -1
                        and 
                                (
                                " . getSQLWhereParaVendaPagtoOnline(true) . "
                                ) ";
        echo "CANCELA vendas com pagamentos CIELO onde o pagamento est� com status -1" . PHP_EOL;        //"$sql".PHP_EOL;
        $ret_cielo = SQLexecuteQuery($sql);
        if (!$ret_cielo || pgNumRowsSafe($ret_cielo) == 0) {
                $msg .= "Nenhuma venda encontrada com pagamento CIELO cancelado " . PHP_EOL;
        } else {
                $msg .= "Encontrada(s) " . pgNumRowsSafe($ret_cielo) . " venda(s) com pagamento CIELO cancelado" . PHP_EOL;
                $ret = "";
                while ($ret_cielo_row = pgFetchArraySafe($ret_cielo)) {
                        $vg_id = $ret_cielo_row['vg_id'];
                        $parametros['usuario_obs'] = "Seu pagamento CIELO foi cancelado" . PHP_EOL;
                        $parametros['ultimo_status_obs'] = "Pedido cancelado. Pagamento Cielo cancelado." . PHP_EOL;

                        $ret = cancelaVendaGames($vg_id, $parametros);
                        $msg .= " Venda " . $vg_id . ": " . str_replace(PHP_EOL, " ", $ret) . PHP_EOL;
                }
        }
        $msg = $header . $msg . "------------------------------------------------------------------------" . PHP_EOL;

        return $msg;
}

function conciliacaoAutomaticaBoleto()
{
        global $cReturn, $cSpaces, $sFontRedOpen, $sFontRedClose;

        $bDebug = false;        // true;

        if ($bDebug || true) {
                $time_start_stats = getmicrotime();
                $time_start_stats_prev = $time_start_stats;
                echo $cReturn . $cReturn . "Entering  conciliacaoAutomaticaBoleto() [nova] - " . date('Y-m-d - H:i:s') . $cReturn;
                echo "Elapsed time : " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        //header
        $header = $cReturn . "------------------------------------------------------------------------" . $cReturn;
        $header .= "Conciliacao Automatica de Boleto (Novo esquema)" . $cReturn;
        $header .= date('d/m/Y - H:i:s') . $cReturn . $cReturn;
        $msg = "";

        // obtem a data de 5 dias atr�s
        $today_minus_5_days = (new DateTimeImmutable("-5 days"))->setTime(0, 0, 0)->format("Y-m-d H:i:s");

        //Procura boletos em aberto nos �ltimos 5 dias
        $sql = "select b.* ";        // "--, vg.*  "
        $sql .= "from bancos_financeiros bf, boletos_pendentes b ";
        $sql .= "where (bol_banco = bco_codigo) and (bco_rpp = 1) and bol_aprovado = 0 ";
        $sql .= " and bol_data>='" . $today_minus_5_days . "' ";                // ".date("Y-m-d")."
        $sql .= " and (substr(bol_documento,1,1)='2' or substr(bol_documento,1,1)='3') ";
        $sql .= " order by bol_codigo desc ";

        //if($bDebug) 
        echo "SQL A0: " . $sql . $cReturn;
        $msg1 = "";

        if ($bDebug)
                echo $sql . $cReturn;
        if ($bDebug)
                echo "Elapsed time (Procura boleto " . (isset($n_boletos) ? $n_boletos : "") . "): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;

        $rs_bol = SQLexecuteQuery($sql);
        if ($bDebug)
                echo "pgNumRowsSafe(rs_bol): " . pgNumRowsSafe($rs_bol) . $cReturn . $cReturn;

        if ($rs_bol && (pgNumRowsSafe($rs_bol) > 0)) {

                $n_boletos = 0;
                $s_lista_boletos_sem_vendas = "";
                while ($rs_bol_row = pgFetchArraySafe($rs_bol)) {

                        $msg1 .= $msg;
                        $msg = "";
                        $n_boletos++;
                        $bol_codigo = $rs_bol_row['bol_codigo'];
                        $bol_banco = $rs_bol_row['bol_banco'];
                        $bol_valor = $rs_bol_row['bol_valor'];
                        if (($bol_banco == "033") || ($bol_banco == "237") || ($bol_banco == "461")) {
                                $bol_documento = substr($rs_bol_row['bol_documento'], 0, (strlen($rs_bol_row['bol_documento']) - 1));
                        } else {
                                $bol_documento = $rs_bol_row['bol_documento'];
                        }
                        $bol_venda_games_id = $rs_bol_row['bol_venda_games_id'];

                        //Recupera as vendas pendentes de boleto
                        // Para Bradesco:	"3000cccccccc" 
                        // Para Ita�:		"3cccccccc" 
                        //		com vg_id = cccccccc 
                        if ($msg == "") {
                                // Boletos Bradesco t�m n�meros do tipo "3000vvvvvvvP"
                                // j� boletos It�u s�o do tipo "2vvvvvvv"
                                // onde "vvvvvvv" � o vg_id

                                // Em tb_venda_games o campo bol_documento tem os formatos: "3000ccccccc", "3000cccccccP", "2000ccccccc" 
                                // Ap�s concilia��o, os boletos Ita� passam a ter o formato "2ccccccc", esses tamb�m s�o levantados aqui
                                // tentar substr(bol_documento, length(bol_documento)-7, 7) 
                                if (strlen($bol_documento) == 12) {
                                        $istart = 5;
                                } elseif (strlen($bol_documento) == 11) {
                                        $istart = 4;
                                } elseif (strlen($bol_documento) == 8) {
                                        $istart = 1;
                                }
                                $vg_id_documento = substr($bol_documento, $istart, 8);
                                $sql = "select * from tb_venda_games vg 
                                                where vg_pagto_tipo = " . $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'] . " 
                                                        and (vg_pagto_banco = $1)";
                                $params_venda = [$bol_banco];
                                $sql .= "	and ( (vg_pagto_num_docto like $2) or 
                                                                        (vg_pagto_num_docto like $3  
                                                                                and vg_ug_id=" . $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'] . ") )";
                                $params_venda[] = '2%' . $vg_id_documento . '%';
                                $params_venda[] = '3%' . $vg_id_documento . '%';

                                $sql .= "	and (not (vg_ultimo_status='" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "' 
                                                                                or vg_ultimo_status='" . $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'] . "' )) ";

                                //if($bDebug) 
                                echo "SQL A1: " . $sql . $cReturn;

                                $rs_venda = SQLexecuteQueryParams($sql, $params_venda);
                                if ($rs_venda && pgNumRowsSafe($rs_venda) > 0) {
                                        $rs_venda_row = pgFetchArraySafe($rs_venda);
                                } else {
                                        $msg = "Nenhuma venda encontrada." . $cReturn;
                                }
                        }
                        if ($bDebug)
                                echo "  >> pgNumRowsSafe(rs_venda): " . pgNumRowsSafe($rs_venda) . $cReturn . PHP_EOL;

                        if ($msg == "") {
                                $vg_id = $rs_venda_row['vg_id'];
                                $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
                                $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];
                                $vg_ultimo_status_obs = $rs_venda_row['vg_ultimo_status_obs'];
                                $vg_ug_id = $rs_venda_row['vg_ug_id'];
                                $vg_integracao_parceiro_origem_id
                                        = $rs_venda_row['vg_integracao_parceiro_origem_id'];

                                $ip_id = (($vg_integracao_parceiro_origem_id) ? getIntegracaoPedidoID_By_Venda($vg_integracao_parceiro_origem_id, $vg_id) : 0);

                                grava_log_integracao_tmp("Integra��o get ip_id from vg_id em conciliacaoAutomaticaBoleto(): " . date("Y-m-d H:i:s") . " (vg: $vg_id -> ip_id: '$ip_id', vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id') " . PHP_EOL);

                                //if($bDebug) 
                                echo $cReturn . "  LOGB>> vg_id: $vg_id, vg_pagto_banco: '$vg_pagto_banco', bol_banco: '$bol_banco', vg_pagto_num_docto: '$vg_pagto_num_docto', ip_id: $ip_id, vg_ug_id: $vg_ug_id, vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id'" . $cReturn;

                                //obtem o valor total da venda
                                //----------------------------------------------------
                                $total_geral = 0;
                                $sql = "select * from tb_venda_games vg " .
                                        "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                                        "where vg.vg_id = $1";
                                if ($bDebug)
                                        echo "SQL RRR: " . $sql . $cReturn;

                                $rs_venda_modelos = SQLexecuteQueryParams($sql, [$vg_id]);
                                if ($rs_venda_modelos && pgNumRowsSafe($rs_venda_modelos) > 0) {
                                        while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                                                $qtde = $rs_venda_modelos_row['vgm_qtde'];
                                                $valor = $rs_venda_modelos_row['vgm_valor'];
                                                $total_geral += $valor * $qtde;

                                                if ($vg_integracao_parceiro_origem_id) {
                                                        // Para integra��o salva o ID de produto (sempre � um modelo por venda)
                                                        $vgm_ogp_id = $rs_venda_modelos_row['vgm_ogp_id'];
                                                        echo "  TESTA PRODUTO EM INTEGRA��O BOL >> ['" . $rs_venda_modelos_row['vg_integracao_parceiro_origem_id'] . "'] ->  [vg_id: '" . $rs_venda_modelos_row['vg_id'] . "'; vgm_ogp_id: '$vgm_ogp_id']- qtde: '$qtde', valor: '$valor' " . $cReturn;
                                                }
                                                if ($bDebug)
                                                        echo "  >> - qtde: $qtde, valor: $valor" . $cReturn;
                                        }
                                        //echo "    resumo de valores>> vg_id: $vg_id, bol_valor+custo_itau: ".($bol_valor+$GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2'])." (".$bol_valor.", ".$GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2']."), total+taxas: ".($total_geral + $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'])." (".$total_geral.", ".$GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'].")".$cReturn;

                                }
                        } else {
                                echo "N�o foi encontrada a venda para este boleto A (vg_id= " . $bol_venda_games_id . ", bol_codigo='$bol_codigo', bol_valor=" . $bol_valor . ", bol_data: '" . $rs_bol_row['bol_data'] . "')" . PHP_EOL . "  Msg: '$msg'" . $cReturn;
                        }

                        if ($msg == "") {

                                $sql = "select bbg_valor_taxa 
                                        from boleto_bancario_games bbg 
                                        where (bbg_pago = 0 or bbg_pago is null) 
                                            and bbg.bbg_vg_id = $1 ;";
	                                echo "Buscando Taxa no Banco de Dados:" . PHP_EOL . $sql . PHP_EOL;
	                                $rs_taxa = SQLexecuteQueryParams($sql, [$vg_id]);
	                                $rs_taxa_row = pgFetchArraySafe($rs_taxa);
	                                $taxas = $rs_taxa_row ? $rs_taxa_row['bbg_valor_taxa'] : 0;

                                if ($vg_pagto_banco == $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'] || $vg_pagto_banco == $GLOBALS['BOLETO_MONEY_ASAAS_COD_BANCO']) {

                                        if ($bDebug)
                                                echo "   === Boleto Bradesco -> vai processar" . $cReturn;
                                        switch (substr($vg_pagto_num_docto, 0, 1)) {
                                                case '2':
                                                        if (round($bol_valor, 2) == round(($total_geral + $taxas), 2)) {
                                                                // OK
                                                        } else {
                                                                $msg .= "Erro2: valor do boleto inv�lido Money (Bradesco): bol_valor: [" . $bol_valor . "], total+taxas: [" . ($total_geral + $taxas) . $cReturn;
                                                        }
                                                        break;
                                                case '3':
                                                        if ($vg_ug_id == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']) {
                                                                if (round($bol_valor, 2) == round(($total_geral + $taxas), 2)) {
                                                                        // OK
                                                                } else {
                                                                        $msg .= "Erro3: valor do boleto inv�lido Express Money (Bradesco): bol_valor: " . $bol_valor . ", total+taxas: " . ($total_geral + $taxas) . $cReturn;
                                                                }
                                                        } else {
                                                                $msg .= "Erro3: usu�rio inv�lido para Money Express (Bradesco): " . $vg_ug_id . $cReturn;
                                                        }
                                                        break;
                                                default:
                                                        $msg .= "Erro#: c�digo de boleto inv�lido (Bradesco): '" . substr($vg_pagto_num_docto, 0, 1) . "' (vg_pagto_num_docto: " . $vg_pagto_num_docto . ") " . $cReturn;
                                                        break;
                                        }
                                } elseif ($vg_pagto_banco == $GLOBALS['BOLETO_MONEY_ITAU_COD_BANCO']) {

                                        if ($bDebug)
                                                echo "   === Boleto Ita� -> vai processar (vg_id: $vg_id)" . $cReturn;

                                        if ($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) {
                                                $valor_pago_no_boleto = ($bol_valor + $GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2']);
                                                //Excluir ap�s testes => $taxas = $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'];
                                        } //end if($total_geral  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                                        else {
                                                $valor_pago_no_boleto = ($bol_valor - $GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2']);
                                                // Excluir ap�s testes => $taxas = 0;
                                        } //end else do if($total_geral  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])

                                        switch (substr($vg_pagto_num_docto, 0, 1)) {
                                                case '2':
                                                        $valor_venda = ($total_geral + $taxas);
                                                        echo "  DUMMY valor_pago_no_boleto 2 = $valor_pago_no_boleto,  valor_venda = $valor_venda, diff = " . ($valor_pago_no_boleto - $valor_venda) . PHP_EOL;
                                                        // para valores com centavos o valor lido em double precision n�o � arredondado => n�o serve para comparar depois
                                                        //  =>  exemplo, para 28,99 temos
                                                        //		 valor_pago_no_boleto = 30.49,  valor_venda = 30.49, diff = 3.5527136788005E-15

                                                        if (abs($valor_pago_no_boleto - $valor_venda) < 0.000001) {
                                                                // OK
                                                                echo "    Valor CORRETO Boleto 2 - OK1 >> vg_id: $vg_id, bol_valor+custo_itau: " . ($bol_valor + $valor_pago_no_boleto) . " (" . $bol_valor . ", " . $valor_pago_no_boleto . "), total+taxas: " . ($total_geral + $taxas) . " (" . $total_geral . ", " . $taxas . ")" . $cReturn;
                                                        } else {
                                                                $msg .= "Erro_2: valor do boleto inv�lido Money (Ita�)(vg_id: $vg_id): bol_valor+custo_itau: " . ($bol_valor + $valor_pago_no_boleto) . " (" . $bol_valor . ", " . $valor_pago_no_boleto . "), total+taxas: " . ($total_geral + $taxas) . " (" . $total_geral . ", " . $taxas . ")" . $cReturn;
                                                                $msg .= "  [Total boleto: " . $bol_valor . "]" . $cReturn;
                                                                $msg .= "  [Total venda: " . $total_geral . "]" . $cReturn;
                                                                $msg .= "  [Difference: " . (($bol_valor + $valor_pago_no_boleto) - ($total_geral + $taxas)) . "]" . $cReturn;
                                                        }
                                                        break;
                                                case '3':
                                                        if ($vg_ug_id == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']) {
                                                                $valor_venda = ($total_geral + $taxas);
                                                                echo "  DUMMY valor_pago_no_boleto 3 = $valor_pago_no_boleto,  valor_venda = $valor_venda, diff = " . ($valor_pago_no_boleto - $valor_venda) . PHP_EOL;

                                                                if (abs($valor_pago_no_boleto - $valor_venda) < 0.000001) {
                                                                        // OK
                                                                        echo "    Valor CORRETO Boleto 3 - OK1 >> vg_id: $vg_id, bol_valor+custo_itau: " . ($bol_valor + $valor_pago_no_boleto) . " (" . $bol_valor . ", " . $valor_pago_no_boleto . "), total+taxas: " . ($total_geral + $taxas) . " (" . $total_geral . ", " . $taxas . ")" . $cReturn;
                                                                } else {
                                                                        $msg .= "Erro_3: valor do boleto inv�lido Express Money (Ita�)(vg_id: $vg_id): bol_valor+custo_itau: " . ($bol_valor + $valor_pago_no_boleto) . ", total+taxas: " . ($total_geral + $taxas) . $cReturn;
                                                                }
                                                        } else {
                                                                $msg .= "Erro3: usu�rio inv�lido para Money Express (Ita�)(vg_id: $vg_id): " . $vg_ug_id . $cReturn;
                                                        }
                                                        break;
                                                default:
                                                        $msg .= "Erro#: c�digo de boleto inv�lido (Ita�)(vg_id: $vg_id): '" . substr($vg_pagto_num_docto, 0, 1) . "' (vg_pagto_num_docto: " . $vg_pagto_num_docto . ") " . $cReturn;
                                                        break;
                                        }
                                } elseif ($vg_pagto_banco == $GLOBALS['BOLETO_MONEY_BANCO_BANESPA_COD_BANCO']) {

                                        if ($bDebug)
                                                echo "   === Boleto Santander -> vai processar" . $cReturn;
                                        switch (substr($vg_pagto_num_docto, 0, 1)) {
                                                case '2':
                                                        if (round($bol_valor, 2) == round(($total_geral + $taxas), 2)) {
                                                                // OK
                                                        } else {
                                                                $msg .= "Erro2: valor do boleto inv�lido Money (Santander): bol_valor: " . $bol_valor . ", total+taxas: " . ($total_geral + $taxas) . $cReturn;
                                                        }
                                                        break;
                                                case '3':
                                                        if ($vg_ug_id == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']) {
                                                                if (round($bol_valor, 2) == round(($total_geral + $taxas), 2)) {
                                                                        // OK
                                                                } else {
                                                                        $msg .= "Erro3: valor do boleto inv�lido Express Money (Santander): bol_valor: " . $bol_valor . ", total+taxas: " . ($total_geral + $taxas) . $cReturn;
                                                                }
                                                        } else {
                                                                $msg .= "Erro3: usu�rio inv�lido para Money Express (Santander): " . $vg_ug_id . $cReturn;
                                                        }
                                                        break;
                                                default:
                                                        $msg .= "Erro#: c�digo de boleto inv�lido (Santander): '" . substr($vg_pagto_num_docto, 0, 1) . "' (vg_pagto_num_docto: " . $vg_pagto_num_docto . ") " . $cReturn;
                                                        break;
                                        } //end switch

                                } else {
                                        $msg .= "Erro?: Banco n�o suportado: " . $vg_pagto_banco . $cReturn;
                                }
                                echo "Debug - depois de boleto_testa_valores: " . (($msg) ? "Nope" : "OK") . " (vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id')" . PHP_EOL;

                                if (trim($vg_integracao_parceiro_origem_id) == '') {
                                        // Processamento normal
                                        echo " = Em Processamento Normal (B1) - nada a fazer" . $cReturn;
                                } else {
                                        // Processa integra��o
                                        echo " = Em Processamento para integra��o (A1) ($vg_id :'$vg_integracao_parceiro_origem_id') - vai Processar" . $cReturn;
                                        $bDebug = true;
                                        // Se o teste de valores falhou, n�o realiza concilia��o de integra��o, ou seja, n�o faz notifica��o de parceiro
                                        $msgConcilia = "";
                                        if ($msg) {
                                                $msgConcilia = "Erro ao testar valores (msgConcilia: $msg)";
                                                echo "ERRO de valores: $msgConcilia" . $cReturn;
                                                grava_log_integracao_tmp("Integra��o Debug Cont 0 : " . date("Y-m-d H:i:s") . PHP_EOL . "$sql" . PHP_EOL . " (msg: '$msg')" . PHP_EOL);
                                        }
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (C2): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                                echo "get_vg_ultimo_status($vg_id) 222: " . get_vg_ultimo_status($vg_id) . PHP_EOL;
                                        }

                                        if ($msgConcilia == "") {
                                                // Teste de valores passou -> venda est� paga aqui, 
                                                //		na concilia��o de integra��o faz a notifica��o e a venda j� precisa estar completa para responder corretamente
                                                $parametrosUltimoStatus = isset($parametros['ultimo_status_obs']) ? $parametros['ultimo_status_obs'] : "";

                                                $sql = "update tb_venda_games set 
                                                        vg_bol_codigo = $1,
                                                        vg_ultimo_status_obs = $2,
                                                        vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . "
                                                        where vg_id = $3";
                                                echo " DUMMY INT 1 $sql" . PHP_EOL . " ";
                                                $ret = SQLexecuteQueryParams($sql, [$bol_codigo, $parametrosUltimoStatus, $vg_id]);
                                                if (!$ret)
                                                        $msg = "Erro ao atualizar venda." . PHP_EOL;
                                                grava_log_integracao_tmp("Integra��o Debug Cont 1 (ip_id: $ip_id): " . date("Y-m-d H:i:s") . PHP_EOL . "$sql" . PHP_EOL . " (msg: '$msg')" . PHP_EOL);
                                        } else {
                                                echo "ERRO Concilia (A2): $msgConcilia" . $cReturn;
                                        }

                                        // Processa vendas de usu�rios integra��o com boleto
                                        grava_log_integracao_tmp("Integra��o Debug 4_bko Boleto: " . date("Y-m-d H:i:s") . PHP_EOL . "      vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id', vg_id: $vg_id, ip_id: $ip_id" . PHP_EOL);

                                        if ($msgConcilia == "") {
                                                if ($bDebug) {
                                                        //echo "Vai para processaVendaGames($vg_id, 1, parametros): ".print_r($parametros, true).PHP_EOL;
                                                        //echo "get_vg_ultimo_status($vg_id) 111: ".get_vg_ultimo_status($vg_id).PHP_EOL;
                                                }

                                                $parametros['vg_integracao_parceiro_origem_id'] = $vg_integracao_parceiro_origem_id;
                                                $parametros['ultimo_status_obs'] = "Processa integra��o em notify Boleto (" . date("Y-m-d H:i:s") . ") Parceiro: $vg_integracao_parceiro_origem_id, ip_id: $ip_id, vg_id: $vg_id";
                                                $msgConcilia = processaVendaGamesIntegracao($vg_id, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Processamento Integra��o (Boleto): Processado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Processamento (6): " . $msgConcilia;
                                        } else {
                                                echo "ERRO Concilia (A3): $msgConcilia" . $cReturn;
                                        }

                                        if ($bDebug)
                                                echo " = Em Processamento para integra��o - Apenas completa a venda" . $cReturn;

                                        if ($msgConcilia == "") {
                                                //Usuario backoffice
                                                $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
                                                if (($parametros['PROCESS_AUTOM'] ?? '') == '1')
                                                        $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

                                                $sql = "update tb_venda_games set 
                                                        vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = $1," . PHP_EOL . "
                                                        vg_ultimo_status_obs = $2," . PHP_EOL . "
                                                        vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . PHP_EOL . "
                                                        where vg_id = $3";
                                                echo " DUMMY INT 2 $sql" . PHP_EOL . " ";
                                                $ret = SQLexecuteQueryParams($sql, [$iduser_bko, $parametros['ultimo_status_obs'], $vg_id]);
                                                if (!$ret)
                                                        $msg = "Erro ao atualizar venda (RESDEW)." . PHP_EOL;
                                        } else {
                                                echo "ERRO Concilia (A4): $msgConcilia" . $cReturn;
                                        }

                                        if ($msgConcilia == "") {
                                                echo "    Em concilia��o de integra��o, vai procurar notify_url: vgm_ogp_id='$vgm_ogp_id'" . PHP_EOL;
                                                $url_notify_url = getPartner_param_By_ID('notify_url', $vg_integracao_parceiro_origem_id);
                                                $partner_do_notify = getPartner_param_By_ID('partner_do_notify', $vg_integracao_parceiro_origem_id);
                                                $s_msg = str_repeat("*", 80) . PHP_EOL . (($partner_do_notify == 1) ? "VAI FAZER NOTIFY" : "Sem notify") . PHP_EOL;
                                                $s_msg .= "    vg_integracao_parceiro_origem_id: $vg_integracao_parceiro_origem_id" . PHP_EOL . "    partner_do_notify: $partner_do_notify" . PHP_EOL . "    url_notify_url: '$url_notify_url'" . PHP_EOL;
                                                grava_log_integracao_tmp(str_repeat("*", 80) . PHP_EOL . "Vai processar integra��o:" . PHP_EOL . $s_msg);
                                                if ($partner_do_notify == 1 && ($url_notify_url != "")) {

                                                        // Monta o passo 4 da Integra��o - Notify partner
                                                        $sql = "SELECT * FROM tb_integracao_pedido ip 
                                                                WHERE 1=1
                                                                and ip_store_id = $1
                                                                and ip_vg_id = $2";
                                                        grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . "Select  registro de integra��o para o notify (A1)" . PHP_EOL . $sql . PHP_EOL);

                                                        $rs = SQLexecuteQueryParams($sql, [$vg_integracao_parceiro_origem_id, $vg_id]);
                                                        if (!$rs) {
                                                                $msg_1 = date("Y-m-d H:i:s") . " - Erro ao recuperar transa��o de integra��o (store_id: '" . $vg_integracao_parceiro_origem_id . "', vg_id: $vg_id)." . PHP_EOL;
                                                                echo $msg_1;
                                                                grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . $msg_1);
	                                                        } else {
	                                                                $rs_row = pgFetchArraySafe($rs);

	                                                                if (!$rs_row) {
	                                                                        $msg_1 = date("Y-m-d H:i:s") . " - Transacao de integracao nao encontrada para notify (store_id: '" . $vg_integracao_parceiro_origem_id . "', vg_id: $vg_id)." . PHP_EOL;
	                                                                        echo $msg_1;
	                                                                        grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . $msg_1);
	                                                                } else {
	                                                                $post_parameters = "store_id=" . $rs_row["ip_store_id"] . "&";

                                                                $post_parameters .= "transaction_id=" . $rs_row["ip_transaction_id"] . "&";
                                                                $post_parameters .= "order_id=" . $rs_row["ip_order_id"] . "&";
                                                                $post_parameters .= "amount=" . $rs_row["ip_amount"] . "&";
                                                                if (strlen($rs_row["ip_product_id"]) > 0) {
                                                                        $post_parameters .= "product_id=" . $rs_row["ip_product_id"] . "&";
                                                                }
                                                                $post_parameters .= "client_email=" . $rs_row["ip_client_email"] . "&";
                                                                $post_parameters .= "client_id=" . $rs_row["ip_client_id"] . "&";

                                                                $post_parameters .= "currency_code=" . $rs_row["ip_currency_code"];

                                                                $sret1 = getIntegracaoCURL($url_notify_url, $post_parameters);
                                                                //										$sret = substr($sret1,strpos($sret1,"Content-type: text/html")+strlen("Content-type: text/html"));
                                                                $sret = $sret1;

	                                                                $s_msg = "AFTER Partner Notify - Conciliacao Automatica de Boleto (Novo esquema) (" . date("Y-m-d H:i:s") . ")" . PHP_EOL . " - result: " . PHP_EOL . str_repeat("_", 80) . PHP_EOL . $sret . PHP_EOL . str_repeat("-", 80) . PHP_EOL;
	                                                                grava_log_integracao_tmp(str_repeat("*", 80) . PHP_EOL . "Retorno de getIntegracaoCURL (1): " . PHP_EOL . print_r($post_parameters, true) . PHP_EOL . $s_msg . PHP_EOL);
	                                                                echo "  ==  $s_msg" . $cReturn . print_r($post_parameters, true) . $cReturn;
	                                                                }
	                                                        }
                                                }
                                        } else {
                                                echo "ERRO Concilia (A5): $msgConcilia" . $cReturn;
                                        }
                                }
                        } else {
                                echo "Pass 123: Sem venda para o boleto bol_codigo='$bol_codigo'" . $cReturn;
                        }

                        if ($msg == "") {

                                $msg .= $cReturn . "Pass 765 - Venda " . $vg_id . ": Boleto " . $bol_codigo . ":" . $cReturn;
                                $parametros['ultimo_status_obs'] = "Concilia��o autom�tica em " . date('d/m/Y - H:i:s') . $cReturn;
                                if (trim($vg_ultimo_status_obs) != "")
                                        $parametros['ultimo_status_obs'] = $vg_ultimo_status_obs . $cReturn . $parametros['ultimo_status_obs'];
                                $parametros['PROCESS_AUTOM'] = '1';

                                //if($bDebug) 
                                echo "DUMMY - VENDA de boleto conciliada com sucesso (vg_id: $vg_id): bol_valor+custo_itau: " . ($bol_valor + (!empty($valor_pago_no_boleto) ? $valor_pago_no_boleto : 0)) . " (" . $bol_valor . ", " . (!empty($valor_pago_no_boleto) ? $valor_pago_no_boleto : 0) . "), total+taxas: " . ($total_geral + $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL']) . " (" . $total_geral . ", " . $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'] . ")" . $cReturn;

                                echo "Elapsed time (Busca boletos - Venda " . $vg_id . ": Boleto " . $bol_codigo . ", valorBoleto: " . number_format($bol_valor, 2, '.', '.') . ", vendaTotal: " . number_format($total_geral, 2, '.', '.') . "): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;

                                //Concilia
                                $msgConcilia = "";
                                if ($msgConcilia == "") {
                                        //if($bDebug) 
                                        echo "conciliaVendaGames_boleto($vg_id, $bol_codigo, 1, parametros) " . date('Y-m-d H:i:s') . "  ============================= 1 " . $cReturn;

                                        $msgConcilia = conciliaVendaGames_boleto($vg_id, $bol_codigo, 1, $parametros);
                                        if ($msgConcilia == "") {
                                                $msg .= "Conciliacao(A): Conciliado com sucesso." . $cReturn;
                                                echo "msgConcilia OK: Conciliacao(A): Conciliado com sucesso." . $cReturn;
                                        } else {
                                                $msg .= "N�o Conciliado (1): " . $msgConcilia;
                                                //if($bDebug) 
                                                echo "msgConcilia: $msgConcilia" . $cReturn;
                                        }
                                }

                                if ($bDebug)
                                        echo "Elapsed time (conciliaVendaGames_boleto): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;

                                if (trim($vg_integracao_parceiro_origem_id) == '') {
                                        // Processamento normal
                                        echo " = Em Processamento Normal (B2) - vai processar" . $cReturn;

                                        //Associa pins, gera venda e credita saldo
                                        if ($msgConcilia == "") {
                                                if ($bDebug)
                                                        echo "processaVendaGames($vg_id, 1, parametros) " . date('Y-m-d H:i:s') . "  ============================= 2 " . $cReturn;

                                                $msgConcilia = processaVendaGames($vg_id, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Processamento (7): Processado com sucesso." . $cReturn;
                                                else {
                                                        $msg .= "Processamento (1): " . $msgConcilia;
                                                }
                                        }
                                        if ($bDebug)
                                                echo "Elapsed time (processaVendaGames(" . $vg_id . ")) : " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;

                                        //envia email para o cliente
                                        if ($msgConcilia == "") {
                                                if ($bDebug)
                                                        echo "processaEmailVendaGames($vg_id, parametros) " . date('Y-m-d H:i:s') . "  ============================= 3	" . $cReturn;
                                                $msgConcilia = processaEmailVendaGames($vg_id, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Envio de email: Enviado com sucesso." . $cReturn;
                                                else {
                                                        $msg .= "Envio de email: " . $msgConcilia;
                                                }
                                        }
                                } else {
                                        // Processamento para integra��o
                                        // Posteriormente -> envia email para usu�rio

                                }
                                if ($bDebug)
                                        echo "Elapsed time (processaEmailVendaGames(" . $vg_id . ")) : " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        } else {
                                echo $cReturn . "Pass 432 - Venda " . $vg_id . ": Boleto " . $bol_codigo . ":" . $cReturn . "msg: " . $msg . $cReturn . $cReturn;
                                $s_lista_boletos_sem_vendas .= $vg_id . ", ";
                        }

                        if ($bDebug) {
                                $time_start_stat_this = getmicrotime();
                                echo " ==== Elapsed time (Procura boleto 2): " . number_format($time_start_stat_this - $time_start_stats, 2, '.', '.') . " (diff prev: " . number_format($time_start_stat_this - $time_start_stats_prev, 2, '.', '.') . ")" . $cReturn;
                                //" Nboletos2: ".pgNumRowsSafe($rs_bol)." boletos (".$rs_bol.")".$cReturn;
                                $time_start_stats_prev = $time_start_stat_this;
                        }

                        echo "  +++ Termina boleto " . $bol_documento . " " . str_repeat("+", 50) . PHP_EOL . PHP_EOL;
                }        // Para cada boleto

        } else {
                if ($bDebug)
                        echo "Sem boletos " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . " (boleto " . (isset($n_boletos) ? $n_boletos : "") . ")" . $cReturn . "===============================" . $cReturn;
        }


        if ($bDebug || true)
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME: " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn . "ELAPSED TIME PER CYCLE: " . number_format(((getmicrotime() - $time_start_stats) / (($n_boletos > 0) ? $n_boletos : 1)), 2, '.', '.') . $cReturn;

        echo (empty($s_lista_boletos_sem_vendas) ? "" : PHP_EOL . "Lista de boletos sem vendas (de Pass 432) " . PHP_EOL . "vg_id in (" . $s_lista_boletos_sem_vendas . ")" . PHP_EOL);
        $msg1 = $header . $msg1 . "------------------------------------------------------------------------" . $cReturn;

        return $msg1;
}

// N�o usa mais
// Est� usando conciliacaoAutomaticaBoleto_nova()
function conciliacaoAutomaticaBoleto_antiga()
{

        return "conciliacaoAutomaticaBoleto_antiga() - desabilitada " . date('Y-m-d H:i:s') . "";
}


function conciliacaoAutomaticaPagamentoOnline()
{
        global $FORMAS_PAGAMENTO, $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC, $PAGAMENTO_PIN_EPREPAG_NUMERIC, $PAGAMENTO_BANCO_EPP_ONLINE, $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC, $PAGAMENTO_HIPAY_ONLINE_NUMERIC, $PAGAMENTO_PAYPAL_ONLINE_NUMERIC;
        global $cReturn, $cSpaces, $sFontRedOpen, $sFontRedClose;

        $bank_sonda = new bank_sonda();
        if (isset($bank_sonda)) {
                $bank_sonda->load_banks_sonda_array();
        }

        $bDebug = false;
        if ($bDebug) {
                $time_start_stats = getmicrotime();
                $time_start_stats_prev = $time_start_stats;
                echo $cReturn . $cReturn . "Entering  conciliacaoAutomaticaPagamentoOnline() - " . date('Y-m-d - H:i:s') . $cReturn;
                echo "Elapsed time : " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        $nminutes = 1440;        // Por agora 1 dia, depois apenas 90 min
        echo PHP_EOL . "========================================================================" . PHP_EOL;
        echo "Prepara concilia��o de pagamentos online (registros com idvenda>0 e n�o processados nos �ltimos " . $nminutes . " minutos, desde " . date('Y-m-d H:i:s', strtotime("-" . $nminutes . " minutes")) . ")" . PHP_EOL;
        // Prepara concilia��o de pagamentos online
        $date_ini = date('Y-m-d H:i:s', strtotime("-" . $nminutes . " minutes"));
        $date_end = date("Y-m-d H:i:s");

        // Quando o pagamento retorna por sonda (e n�o diretamente do banco) o status_processed=0 mas ainda vg.vg_ultimo_status=3
        // Os dois casos devem ser conciliados

        // O anterior est� demorando muito e n�o � necess�rio consultar tb_venda_games, apenas tb_pag_compras
        $sql = "select * from tb_pag_compras pgt where idvenda>0 and status_processed=0 and tipo_cliente='M' and tipo_deposito = 0 and datainicio > (now() -'2 months'::interval) and iforma!='E' and iforma!='A' and iforma!='9' and iforma!='6' and iforma!='5' and iforma!='" . $FORMAS_PAGAMENTO['PAGAMENTO_PIX'] . "'";

        // status=1 and 
        // Apenas para vendas que n�o s�o integra��o
        //		$sql .= "and vg_integracao_parceiro_origem_id is null ";

        // Op��o 1 - n�o precissa limitar por data - apenas os pagtos com status_processed=0 ser�o retornados, ap�s 90mins eles s�o cancelados.
        //	se houver um descancelamento de venda o pagto correspondente vai aparecer aqui

        // Op��o 1 - Para processar normalmente
        //		$sql .= " and (pgt.datainicio between '".$date_ini."' and '".$date_end."') ";	
        // Op��o 2 - Para incluir algum pagamento antigo descancelado
        //		$sql .= " and ((pgt.datainicio between '".$date_ini."' and '".$date_end."') or (pgt.datainicio between '2010-01-26 00:00:00' and '2010-01-26 23:59:59'))";	

        $rs_total = SQLexecuteQuery($sql);
        if ($bDebug) {
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 0): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        if ($rs_total)
                $registros_total = pgNumRowsSafe($rs_total);
        $sql .= " order by pgt.datainicio desc ";

        if ($bDebug) {
                echo "DEBUG A1: " . $sql . $cReturn;
        }

        $time_start_stats0 = getmicrotime();
        $rs_transacoes = SQLexecuteQuery($sql);
        if ($bDebug) {
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 1): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }
        echo $cReturn . $cReturn . $cReturn . "TIME FOR FIRST QUERY: " . number_format(getmicrotime() - $time_start_stats0, 2, '.', '.') . $cReturn;        //."$sql".$cReturn;

        if (!$rs_transacoes || pgNumRowsSafe($rs_transacoes) == 0)
                $msg = "Nenhuma transa��o encontrada." . $cReturn;

        $time_start_stats0 = getmicrotime();
        $irows = 0;
        if ($rs_transacoes) {
                $npags = pgNumRowsSafe($rs_transacoes);
                $total_pagtos_pendente = 0;
                while ($rs_transacoes_row = pgFetchArraySafe($rs_transacoes)) {
                        $time_start_stats0_in = getmicrotime();
                        $irows++;

                        $msgregister = $rs_transacoes_row['numcompra'] . " - " . $rs_transacoes_row['datainicio'] . " - " . $rs_transacoes_row['datacompra'] . " - " . $rs_transacoes_row['iforma'] . " - " . $rs_transacoes_row['idvenda'] . " - Proc: " . $rs_transacoes_row['status_processed'] . " - " . get_tipo_cliente_descricao($rs_transacoes_row['tipo_cliente']) . " -: R\$" . number_format($rs_transacoes_row['total'] / 100, 2, ',', '.') . " - '" . $rs_transacoes_row['cliente_nome'] . "'" . $cReturn;

                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 2): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }
                        $msg = "";
                        // Venda cadastrada
                        if ($rs_transacoes_row['idvenda'] > 0) {
                                // Pagamento concluido com sucesso -  status=3 em \prepag2\pag\*.php (arquivo de retorno do banco) 
                                if ($rs_transacoes_row['status'] == 3) {
                                        $prefix = getDocPrefix($rs_transacoes_row['iforma']);

                                        $iforma_tmp = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);

                                        // Atualiza dados para tabela vendas
                                        $sql_update = "update tb_venda_games set 
                                                                        vg_pagto_valor_pago		= " . ($rs_transacoes_row['total'] / 100) . ",
                                                                        vg_pagto_tipo			= " . $iforma_tmp . ",
                                                                        vg_pagto_num_docto		= '" . $prefix . $rs_transacoes_row['iforma'] . "_" . $rs_transacoes_row['numcompra'] . "', 
                                                                        vg_pagto_data_inclusao	= '" . $rs_transacoes_row['datainicio'] . "',
                                                                        vg_ultimo_status		= " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "
                                                                where vg_id=" . $rs_transacoes_row['idvenda'] . ";";

                                        $rs_update = SQLexecuteQuery($sql_update);
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 3): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }

                                        $sout = $rs_transacoes_row['datainicio'] . PHP_EOL . "   " . $rs_transacoes_row['numcompra'] . PHP_EOL . "   tipo: (" . $rs_transacoes_row['iforma'] . ") " . $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$rs_transacoes_row['iforma']] . "," . PHP_EOL . "   idvenda: " . $rs_transacoes_row['idvenda'] . "." . PHP_EOL;
                                        if (!$rs_update) {
                                                $msg = "Erro atualizando registro (61)." . $cReturn . "($sql_update)" . $cReturn . $sout . $cReturn;
                                                echo $msg;
                                        } else {
                                                echo "Pagamento atualizado com sucesso (432345)." . $cReturn;
                                        }

                                        // Pagamento ainda n�o foi feito ou n�o tem confirma��o bancaria -  status=1 -> Sonda o banco, se estiver completo atualiza aqui
                                } else if ($rs_transacoes_row['status'] == 1) {

                                        if (isset($bank_sonda)) {
                                                if ($bank_sonda->is_bank_blocked($rs_transacoes_row['iforma'])) {
                                                        echo "Banco '" . $rs_transacoes_row['iforma'] . "' BLOQUEADO para Sonda (" . date("Y-m-d H:i:s") . ")" . PHP_EOL;
                                                        // Loop to the next register
                                                        // continue;
                                                } else {
                                                        echo "Banco '" . $rs_transacoes_row['iforma'] . "' LIBERADO para Sonda (" . date("Y-m-d H:i:s") . ")" . PHP_EOL;
                                                }
                                        }

                                        // bloqueio para evitar consulta ao MUP
                                        //if(false) 
                                        {

                                                if ($bDebug) {
                                                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 4a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                                        $time_start_stats_4a = getmicrotime();
                                                }

                                                // come�a aqui nova fun��o getSondaBanco()

                                                $dataconfirma = date("Y-m-d H:i:s");                // "CURRENT_TIMESTAMP";	// 
                                                $s_sonda = "????";
                                                //$valtotal = 0;
                                                unset($aline5);
                                                unset($aline6);
                                                unset($aline9);
                                                //unset($alineA);
                                                unset($alineC);
                                                $s_update_status_lr = "";

                                                if (isset($bank_sonda)) {
                                                        $bank_sonda->set_last_numcompra($rs_transacoes_row['iforma'], $rs_transacoes_row['numcompra']);
                                                        $bank_sonda->start_time_waiting_for_sonda($rs_transacoes_row['iforma']);
                                                }

                                                if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {

                                                        // obtem status, OK se status='081'
                                                        $b_sonda_5 = getTransacaoPagamentoOK("Transf", $rs_transacoes_row['numcompra'], $aline5);

                                                        // Se existe registro da transa��o -> salva data 	
                                                        if ((isset($aline5[1], $aline5[3], $aline5[4])) && (strlen($aline5[1]) > 0)) {
                                                                $s_sonda = (($b_sonda_5) ? "OK" : "none");
                                                                $sBanco = "Bradesco";

                                                                /*	Retorno da Sonda
                                                                                   01234567
                                                                        [3] => 25/04/12
                                                                        [4] => 14:35:08
                                                                */
                                                                $dataconfirma = "'20" . substr($aline5[3], 6, 2) . "-" . substr($aline5[3], 3, 2) . "-" . substr($aline5[3], 0, 2) . " " . $aline5[4] . "'";

                                                                gravaLog_TMP_conciliacao("Em concilia��o - Sonda de Pagto BRD5 (" . $rs_transacoes_row['numcompra'] . ")." . PHP_EOL . print_r($aline5, true) . PHP_EOL);
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {

                                                        // obtem status, OK se status='003'
                                                        $b_sonda_6 = getTransacaoPagamentoOK("PagtoFacil", $rs_transacoes_row['numcompra'], $aline6);

                                                        // Se existe registro da transa��o -> salva data 	
                                                        if ((isset($aline6[1], $aline6[3], $aline6[4])) && (strlen($aline6[1]) > 0)) {
                                                                $s_sonda = (($b_sonda_6) ? "OK" : "none");
                                                                $sBanco = "Bradesco";
                                                                /*	
                                                                Retorno da Sonda
                                                                                           0123456789
                                                                                [3] => 25/05/2012
                                                                                [4] => 00:16:59
                                                                */
                                                                $dataconfirma = "'" . substr($aline6[3], 6, 4) . "-" . substr($aline6[3], 3, 2) . "-" . substr($aline6[3], 0, 2) . " " . $aline6[4] . "'";
                                                                gravaLog_TMP_conciliacao("Em concilia��o - Sonda de Pagto BRD6 (" . $rs_transacoes_row['numcompra'] . ")." . PHP_EOL . print_r($aline6, true) . PHP_EOL);
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {

                                                        // obtem status, OK se status='003'

                                                        $b_sonda_9 = getTransacaoPagamentoOK("BancodoBrasil", $rs_transacoes_row['numcompra'], $aline9);
                                                        // Se existe registro da transa��o -> salva data 	
                                                        // Para for�ar a concilia��o de uma venda cancelada e sem registro de pagamento (mas com pagamento realizado pelo cliente)

                                                        if ($b_sonda_9) {
                                                                $s_sonda = (($b_sonda_9) ? "OK" : "none");
                                                                $sBanco = "Banco do Brasil";
                                                                //     [dataPagamento] => 16092009
                                                                $dataPagamento = $aline9['dataPagamento'] ?? "";
                                                                echo " =====> Trecho 2 " . $dataPagamento . PHP_EOL;
                                                                if ($dataPagamento !== "" && strpos($dataPagamento, date('Y')) == 4) {
                                                                        $dataconfirma = "'" . substr($dataPagamento, 4, 4) . "-" . substr($dataPagamento, 2, 2) . "-" . substr($dataPagamento, 0, 2) . "'";
                                                                } //end if(strpos($aline9['dataPagamento'], date('Y')) == 4) 
                                                                else if ($dataPagamento !== "") {
                                                                        $dataconfirma = "'" . substr($dataPagamento, 0, 4) . "-" . substr($dataPagamento, 4, 2) . "-" . substr($dataPagamento, 6, 2) . "'";
                                                                }
                                                                echo " =====> DEPOIS Trecho 2 " . $dataconfirma . PHP_EOL;
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']) {
                                                        $pedido = str_pad((string)$rs_transacoes_row['id_transacao_itau'], 8, "0", STR_PAD_LEFT);

                                                        $pag_status = getSondaItau($pedido, $a_retorno_itau, $sitPag, $dtPag);
                                                        $b_sonda_A = false;
                                                        $b_sonda_A = (($pag_status == "00") ? true : false);


                                                        if ($b_sonda_A) {
                                                                $s_sonda = (($b_sonda_A) ? "OK" : "none");
                                                                $sBanco = "Banco Ita�";
                                                                //     [dtPag] => 16092009
                                                                $dataconfirma = "'" . substr($dtPag, 4, 4) . "-" . substr($dtPag, 2, 2) . "-" . substr($dtPag, 0, 2) . "'";
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']) {

                                                        $pag_status = getSondaPINsEPP($rs_transacoes_row['numcompra'], $dtPag);
                                                        $b_sonda_E = ($pag_status == 3) ? true : false;

                                                        if ($b_sonda_E) {
                                                                $s_sonda = (($b_sonda_E) ? "OK" : "none");
                                                                $sBanco = "PINs E-Prepag";
                                                                $dataconfirma = "'" . date("Y-m-d") . "'";
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_HIPAY_ONLINE']) {

                                                        $pag_status = "";        //getSondaHipay($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                        $b_sonda_B = ($rs_transacoes_row['status'] == 3) ? true : false;


                                                        if ($b_sonda_B) {
                                                                $s_sonda = (($b_sonda_B) ? "OK" : "none");
                                                                $sBanco = "Banco HiPay";
                                                                $dataconfirma = "'" . date("Y-m-d") . "'";
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_PAYPAL_ONLINE']) {

                                                        $pag_status = "";        //getSondaPayPal($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                        $b_sonda_P = ($rs_transacoes_row['status'] == 3) ? true : false;


                                                        if ($b_sonda_P) {
                                                                $s_sonda = (($b_sonda_P) ? "OK" : "none");
                                                                $sBanco = "Banco PayPal";
                                                                $dataconfirma = "'" . date("Y-m-d") . "'";
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']) {

                                                        $pag_status = "";        //getSondaItau($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                        $b_sonda_Z = ($rs_transacoes_row['status'] == 3) ? true : false;


                                                        if ($b_sonda_Z) {
                                                                $s_sonda = (($b_sonda_Z) ? "OK" : "none");
                                                                $sBanco = "Banco E-Prepag";
                                                                $dataconfirma = "'" . date("Y-m-d") . "'";
                                                        }
                                                } else if (b_IsPagtoCielo($rs_transacoes_row['iforma'])) {

                                                        // obtem status, OK se status='6'
                                                        $b_sonda_C = getTransacaoPagamentoOK("Cielo", $rs_transacoes_row['numcompra'], $alineC);

                                                        // Se existe registro da transa��o -> salva data 	
                                                        if (($alineC['status'] ?? '') == "6") {
                                                                $s_sonda = (($b_sonda_C) ? "OK" : "none");
                                                                $sBanco = "Banco Cielo";
                                                                $dataconfirma = "'" . substr($alineC['data'] ?? '', 0, 19) . "'";
                                                                $s_update_status_lr = ", cielo_status = '" . $alineC['status'] . "', cielo_codigo_lr = '" . ($alineC['codigo_lr'] ?? '') . "' ";
                                                        }
                                                }


                                                if (isset($bank_sonda)) {
                                                        $bank_sonda->stop_time_waiting_for_sonda($rs_transacoes_row['iforma']);
                                                        $bank_sonda->block_bank_if_slow($rs_transacoes_row['iforma']);
                                                }

                                                $dataconfirma = str_replace("/", "-", $dataconfirma);

                                                if ($bDebug) {
                                                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 4b): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . " (waiting for Sonda: " . number_format(getmicrotime() - $time_start_stats_4a, 2, '.', '.') . ") " . $cReturn;
                                                }

                                                // Procura pagamentos em aberto no site do banco (Sonda), se (status=1 & sonda) => "NO SYNC"
                                                $s_sync = "";
                                                $prefix_1 = getDocPrefix($rs_transacoes_row['iforma']);
                                                $vg_pagto_tipo = $rs_transacoes_row['iforma'];

                                                if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
                                                        $s_sync = (($b_sonda_5) ? "NO SYNC" : "");
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
                                                        $s_sync = (($b_sonda_6) ? "NO SYNC" : "");
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
                                                        $s_sync = (($b_sonda_9) ? "NO SYNC" : "");
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']) {
                                                        $s_sync = (($b_sonda_A) ? "NO SYNC" : "");
                                                        // No Itau ajusta 'A' -> 10 (usa numerico em tb_venda_games)
                                                        //							$vg_pagto_tipo = $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC'];
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']) {
                                                        $s_sync = (($b_sonda_E) ? "NO SYNC" : "");
                                                        // No Banco E-Prepag ajusta 'E' -> 998 (usa numerico em tb_venda_games)
                                                        //							$vg_pagto_tipo = $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'];	 
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']) {
                                                        $s_sync = (($b_sonda_Z) ? "NO SYNC" : "");
                                                        // No Banco E-Prepag ajusta 'Z' -> 999 (usa numerico em tb_venda_games)
                                                        //							$vg_pagto_tipo = $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'];	 
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']) {
                                                        $s_sync = (($b_sonda_B) ? "NO SYNC" : "");
                                                        // No Banco HiPay ajusta 'B' -> 11 (usa numerico em tb_venda_games)
                                                        //							$vg_pagto_tipo = $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC'];	 
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']) {
                                                        $s_sync = (($b_sonda_P) ? "NO SYNC" : "");
                                                        // No Banco Paypal ajusta 'P' -> 12 (usa numerico em tb_venda_games)
                                                        //							$vg_pagto_tipo = $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC'];	 
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                } else if (b_IsPagtoCielo($rs_transacoes_row['iforma'])) {
                                                        $s_sync = (($b_sonda_C) ? "NO SYNC" : "");
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                }
                                                if ($bDebug) {
                                                        echo "MSG_543443 - rs_transacoes_row['iforma']: '" . $rs_transacoes_row['iforma'] . "' -> '" . $vg_pagto_tipo . "', s_sync = '" . $s_sync . "' (8765)" . PHP_EOL;
                                                }
                                                // at� aqui nova fun��o getSondaBanco()

                                                // Se (!$s_sync), ou seja (status=1 & sonda) => completa a venda POR SONDA
                                                if ($s_sync == "NO SYNC") {                        /////   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

                                                        if ($bDebug) {
                                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 5a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                                        }

                                                        //Inicia transacao
                                                        if ($msg == "") {
                                                                $sql = "BEGIN TRANSACTION ";
                                                                $ret = SQLexecuteQuery($sql);
                                                                $ret = true;                                                                                                /////////////////////
                                                                if (!$ret)
                                                                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
                                                        }

                                                        // Marca registro como processado (status_processed=1), e status=3, j� que se chegou aqui quer dizer que n�o passou por confirmaBanco.php
                                                        $sql = "update tb_pag_compras set status_processed=1, datacompra=CURRENT_TIMESTAMP, dataconfirma=" . $dataconfirma . ", status=3 " . $s_update_status_lr . " where numcompra='" . $rs_transacoes_row['numcompra'] . "' ";
                                                        echo PHP_EOL . " NO SYNC => " . $sql . " " . PHP_EOL . "($msg)" . PHP_EOL;
                                                        echo "DEBUG F (atualiza status_processed=1, vendaid = " . $rs_transacoes_row['idvenda'] . "): " . $sql . PHP_EOL;

                                                        $rs_update2 = SQLexecuteQuery($sql);
                                                        if (!$rs_update2) {
                                                                $msg = "Erro atualizando status de registro (62aa)." . $cReturn . "$sql" . $cReturn;
                                                                echo $msg;
                                                        }
                                                        if (!$msg) {

                                                                // Atualiza dados para tabela vendas
                                                                //'DADOS_PAGTO_RECEBIDO' => 2
                                                                $sql_update = "update tb_venda_games set 
                                                                                        vg_pagto_valor_pago		= " . ($rs_transacoes_row['total'] / 100 + $rs_transacoes_row['taxas'] + $rs_transacoes_row['frete'] + $rs_transacoes_row['manuseio']) . ",
                                                                                        vg_pagto_tipo			= " . $vg_pagto_tipo . ",
                                                                                        vg_pagto_num_docto		= '" . $prefix_1 . $rs_transacoes_row['iforma'] . "_" . $rs_transacoes_row['numcompra'] . "', 
                                                                                        vg_pagto_data_inclusao	= '" . $rs_transacoes_row['datainicio'] . "',
                                                                                        vg_usuario_obs			= 'Pagamento Online " . $sBanco . " POR SONDA [" . $rs_transacoes_row['iforma'] . "] em " . date("Y-m-d H:i:s") . "',
                                                                                        vg_ultimo_status		= " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "
                                                                                where vg_id=" . $rs_transacoes_row['idvenda'] . ";";

                                                                $rs_update = SQLexecuteQuery($sql_update);
                                                                $sout = $rs_transacoes_row['datainicio'] . PHP_EOL . "   " . $rs_transacoes_row['numcompra'] . PHP_EOL . "   tipo: (" . $rs_transacoes_row['iforma'] . ") " . getDescricaoPagtoOnline($rs_transacoes_row['iforma']) . "," . PHP_EOL . "   idvenda: " . $rs_transacoes_row['idvenda'] . "." . PHP_EOL;

                                                                if (!$rs_update) {
                                                                        $msg = "Erro atualizando registro POR SONDA (61a)." . $sout . $cReturn;
                                                                        echo $msg;
                                                                } else {
                                                                        echo "Pagamento atualizado POR SONDA com sucesso (4322)." . $sout . $cReturn;
                                                                }
                                                        }

                                                        //Finaliza transacao
                                                        if ($msg == "") {
                                                                $sql = "COMMIT TRANSACTION ";
                                                                $ret = SQLexecuteQuery($sql);
                                                                if (!$ret)
                                                                        $msg = "Erro ao comitar transa��o." . PHP_EOL;

                                                                $msg_sonda = "PROCESSADO POR SONDA";
                                                        } else {
                                                                $sql = "ROLLBACK TRANSACTION ";
                                                                $ret = SQLexecuteQuery($sql);
                                                                if (!$ret)
                                                                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;

                                                                $msg_sonda = "PROCESSAMENTO POR SONDA FALHOU (ROLLBACK TRANSACTION)";
                                                        }

                                                        echo $msg_sonda . ": Sonda='$s_sonda' forma:" . $rs_transacoes_row['iforma'] . ", numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . $rs_transacoes_row['idvenda'] . "- " . $rs_transacoes_row['datainicio'] . " - R\$" . number_format(($rs_transacoes_row['total'] / 100 - $rs_transacoes_row['taxas']), 2, '.', '.') . " (SYNC)" . $cReturn;

                                                        if ($bDebug) {
                                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 5b): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                                        }
                                                } else {
                                                        $total_pagto = $rs_transacoes_row['total'] / 100 - $rs_transacoes_row['taxas'];
                                                        $total_pagtos_pendente += $total_pagto;
                                                        $leading_zeros = (($total_pagto < 1000) ? (($total_pagto < 100) ? "00" : "0") : "");
                                                        echo "N�o Processado por sonda: forma:" . $rs_transacoes_row['iforma'] . ", numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . str_pad($rs_transacoes_row['idvenda'], 8, '0', STR_PAD_LEFT) . " - " . $rs_transacoes_row['datainicio'] . " - R\$" . $leading_zeros . number_format(($total_pagto), 2, '.', '.') . " (NO SYNC) [" . number_format(getmicrotime() - $time_start_stats0_in, 2, '.', '.') . " s] [" . number_format(getmicrotime() - $time_start_stats0, 2, '.', '.') . " s]" . $cReturn;
                                                }
                                        }  // bloqueio para evitar consulta ao MUP

                                } else {
                                        echo "N�o processado: status!=3 e Sonda=false." . $cReturn . "numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . $rs_transacoes_row['idvenda'] . "- " . $rs_transacoes_row['datainicio'] . $cReturn;
                                }
                        } // 
                        else {
                                echo "N�o processado: idvenda=0." . $cReturn . "numcompra: " . $rs_transacoes_row['numcompra'] . "- " . $rs_transacoes_row['datainicio'] . $cReturn;
                        }
                } // End while loop 

                if (isset($bank_sonda)) {
                        echo str_repeat("-", 80) . PHP_EOL . "Lista bank_sonda[]" . PHP_EOL . $bank_sonda->list_registers(false) . PHP_EOL;
                        $aret = $bank_sonda->get_list_blocked_banks();

                        if (count($aret) > 0) {
                                $b_unblock_banks = true;
                                // Do an extra Sonda to monitor if Bank is online again
                                foreach ($aret as $key => $val) {
                                        echo "TEST SONDA in '$key' ('" . $val['time_waiting_for_sonda'] . "', '" . $val['last_numcompra'] . "')" . PHP_EOL;
                                        $bank_sonda->start_time_waiting_for_sonda($key);
                                        if ($b_unblock_banks) {
                                                switch ($key) {
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
                                                                $b_sonda_5s = getTransacaoPagamentoOK("Transf", $val['last_numcompra'], $aline5);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
                                                                $b_sonda_6s = getTransacaoPagamentoOK("PagtoFacil", $val['last_numcompra'], $aline6);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
                                                                $b_sonda_9s = getTransacaoPagamentoOK("BancodoBrasil", $val['last_numcompra'], $aline9);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:
                                                                $sql = "select id_transacao_itau from tb_pag_compras where numcompra='" . $val['last_numcompra'] . "'";
                                                                $pedido = str_pad(getValueSingle($sql), 8, "0", STR_PAD_LEFT);
                                                                $pag_status_s = getSondaItau($pedido, $a_retorno_itau, $sitPag, $dtPag);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:
                                                                $sql = "select status from tb_pag_compras where numcompra='" . $val['last_numcompra'] . "'";
                                                                $b_sonda_E_s = (getValueSingle($sql) == 3) ? true : false;
                                                                break;
                                                        case $GLOBALS['PAGAMENTO_HIPAY_ONLINE']:
                                                                //							$pag_statuss = "";	//getSondaHipay($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                                break;
                                                        case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE']:
                                                                //							$pag_statuss = "";	//getSondaPayPal($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                                break;
                                                        case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:
                                                                $sql = "select status from tb_pag_compras where numcompra='" . $val['last_numcompra'] . "'";
                                                                $b_sonda_Zs = (getValueSingle($sql) == 3) ? true : false;
                                                                break;
                                                        default:
                                                                if (b_IsPagtoCielo($key)) {
                                                                        $b_sonda_Cs = getTransacaoPagamentoOK("Cielo", $val['last_numcompra'], $alineC);
                                                                }
                                                }
                                        } else {
                                                echo "  ==  DUMMY - chamada a Sonda est� bloqueada" . PHP_EOL;
                                        }
                                        $bank_sonda->stop_time_waiting_for_sonda($key);
                                        $bank_sonda->unblock_bank_if_normal($key);
                                }
                        } else {
                                echo "  ==  Sem chamada a Sonda - n�o tem Bancos bloqueados" . PHP_EOL;
                        }

                        // Save bank block configuration
                        $bank_sonda->save_banks_sonda_array();
                }
        } // End if(rs)
        echo "Tempo m�dio de processamento: " . number_format((getmicrotime() - $time_start_stats0) / (($irows > 0) ? $irows : 1), 2, '.', '.') . " s/processamento (WSA)" . $cReturn;
        echo "Total pagamentos pendentes: R\$" . number_format($total_pagtos_pendente, 2, '.', '.') . " em $npags pagamentos (WSATP)" . $cReturn;

        $smonitor = "Tempo m�dio de processamento (" . date("Y-m-d H:i:s") . "): " . number_format((getmicrotime() - $time_start_stats0) / (($irows > 0) ? $irows : 1), 2, '.', '.') . " s/processamento<br>$irows pagamento" . (($irows > 1) ? "s" : "") . " em aberto<br>";

        // ===================================================

        //header
        $header = PHP_EOL . "------------------------------------------------------------------------" . PHP_EOL;
        $header .= "Conciliacao Automatica de Pagamento Online" . PHP_EOL;
        $header .= date('d/m/Y - H:i:s') . PHP_EOL . PHP_EOL;
        $msg = "";
        $bDebug = false;
        if ($bDebug) {
                echo $header . PHP_EOL;
        }
        //	Recupera as vendas pendentes com pagamento online completo 
        if ($msg == "") {
                // 'DADOS_PAGTO_RECEBIDO' 		=> '2',
                // 'PAGTO_CONFIRMADO' 			=> '3',
                $sql = "
                    select * from tb_venda_games vg 
                    where ((vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . " or 
                                    vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . ")
                            and " . getSQLWhereParaVendaPagtoOnline(false) . "
                    )  and vg_data_inclusao > (now() -'2 months'::interval)
                     and vg_pagto_tipo != 13 
                     and vg_pagto_tipo != 10 
                     and vg_pagto_tipo != 9 
                     and vg_pagto_tipo != 6 
                     and vg_pagto_tipo != 5 
                    order by vg_data_inclusao desc
                    ";

                //	Vendas de integra��o s�o levantadas aqui para executar o notify
                //	n�o precisssa de: "and vg_integracao_parceiro_origem_id is null"

                $time_start_stats = getmicrotime();

                if ($bDebug) {
                        echo "DEBUG_ABCD: " . $sql . PHP_EOL;
                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0000): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                }

                $rs_venda = SQLexecuteQuery($sql);

                if ($bDebug) {
                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (00): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                }

                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
        }

        //Busca vendas
        if ($msg == "") {
                while ($rs_venda_row = pgFetchArraySafe($rs_venda)) {
                        $vg_id = $rs_venda_row['vg_id'];
                        $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
                        $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_ultimo_status_obs = $rs_venda_row['vg_ultimo_status_obs'];
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_integracao_parceiro_origem_id = $rs_venda_row['vg_integracao_parceiro_origem_id'];
                        if ($bDebug) {
                                echo "DEBUG - YTRE: vg_pagto_tipo: '$vg_pagto_tipo', vg_ultimo_status: $vg_ultimo_status" . PHP_EOL;
                        }
                        grava_log_integracao_tmp(PHP_EOL . PHP_EOL . str_repeat("*", 80) . PHP_EOL . "Integra��o Debug B_bko: " . date("Y-m-d H:i:s") . PHP_EOL . "  vg_id: $vg_id, vg_pagto_banco: $vg_pagto_banco, vg_ultimo_status: $vg_ultimo_status, vg_pagto_tipo: $vg_pagto_tipo, vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id'" . PHP_EOL . "  " . (($vg_integracao_parceiro_origem_id != "") ? "Integra��o - parceiro " . $vg_integracao_parceiro_origem_id : "N�o � integra��o (1)") . PHP_EOL);

                        //obtem o valor total da venda
                        //----------------------------------------------------
                        $total_geral = 0;
                        $sql = "select * from tb_venda_games vg " .
                                "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                                "where vg.vg_id = " . $vg_id;
                        if ($bDebug) {
                                echo "DEBUG (A1): " . $sql . PHP_EOL;
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0-): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }

                        $rs_venda_modelos = SQLexecuteQuery($sql);
                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0+): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                echo "get_vg_ultimo_status($vg_id) 000: " . get_vg_ultimo_status($vg_id) . PHP_EOL;
                        }
                        if ($rs_venda_modelos && pgNumRowsSafe($rs_venda_modelos) > 0) {
                                while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                                        $qtde = $rs_venda_modelos_row['vgm_qtde'];
                                        $valor = $rs_venda_modelos_row['vgm_valor'];
                                        $total_geral += $valor * $qtde;
                                        if ($vg_integracao_parceiro_origem_id) {
                                                // Para integra��o salva o ID de produto (sempre � um modelo por venda)
                                                $vgm_ogp_id = $rs_venda_modelos_row['vgm_ogp_id'];
                                                echo "  TESTA PRODUTO EM INTEGRA��O PAG >> ['" . $rs_venda_modelos_row['vg_integracao_parceiro_origem_id'] . "'] ->  [vg_id: '" . $rs_venda_modelos_row['vg_id'] . "'; vgm_ogp_id: '$vgm_ogp_id']- qtde: '$qtde', valor: '$valor' " . $cReturn;
                                        }
                                }
                        }
                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }
                        $rs_pag = SQLexecuteQuery($sql);
                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }
                        if ($rs_pag && pgNumRowsSafe($rs_pag) > 0) {
                                $rs_pag_row = pgFetchArraySafe($rs_pag);
                                // Remove o prefixo "BRD6_"/"BRD5_"/"BBR9_"/"BITA_"/"HIPB_"/"PYPP_"/"BEPZ_"
                                $pag_codigo = substr($rs_pag_row['vg_pagto_num_docto'], 5);
                                $vg_integracao_parceiro_origem_id = $rs_pag_row['vg_integracao_parceiro_origem_id'];

                                $ip_id = (($vg_integracao_parceiro_origem_id) ? getIntegracaoPedidoID_By_Venda($vg_integracao_parceiro_origem_id, $vg_id) : 0);

                                $msg .= PHP_EOL . "Venda: " . $vg_id . ", Pagamento: " . $pag_codigo . " (" . $rs_pag_row['vg_pagto_num_docto'] . ")" . PHP_EOL;
                                if ($bDebug) {
                                        echo PHP_EOL . "Venda: " . $vg_id . ", Pagamento: " . $pag_codigo . PHP_EOL;
                                }

                                echo "  LOGP>> vg_id: $vg_id, pag_codigo: '$pag_codigo', ip_id: $ip_id, vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id'" . $cReturn;


                                /*
                                ================ Banco EPP - Start
                                 */

                                // Processa pagamentos online sem integra��o (Usu�rios Money com pagamento online)
                                if (trim($vg_integracao_parceiro_origem_id) == '') {

                                        // Prepara concilia��o
                                        $parametros['ultimo_status_obs'] = "Concilia��o autom�tica pagamento online em " . date('d/m/Y - H:i:s') . PHP_EOL;
                                        if (trim($vg_ultimo_status_obs) != "") {
                                                $vg_ultimo_status_obs = substr($vg_ultimo_status_obs, -1023);
                                                $ultimo_status_obs_sem_trim = $vg_ultimo_status_obs . PHP_EOL . $parametros['ultimo_status_obs'];
                                                $parametros['ultimo_status_obs'] = substr($ultimo_status_obs_sem_trim, -1023);
                                        }
                                        $parametros['PROCESS_AUTOM'] = '1';
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (A): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //Concilia
                                        $msgConcilia = "";
                                        if ($msgConcilia == "") {
                                                $msgConcilia = conciliaVendaGames_PagamentoOnline($vg_id, $pag_codigo, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Conciliacao(C1): Conciliado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Conciliacao: " . $msgConcilia;
                                        }
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (B, vg_pagto_tipo: '$vg_pagto_tipo', PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC: " . $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'] . "): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //Associa pins, gera venda e credita saldo
                                        if ($msgConcilia == "") {
                                                $msgConcilia = processaVendaGames($vg_id, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Processamento (Pagtos Online): Processado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Processamento (4): " . $msgConcilia;
                                        }
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (C1): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //envia email para o cliente de pagamento online
                                        if ($msgConcilia == "") {
                                                $msgConcilia = processaEmailVendaGames($vg_id, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Envio de email: Enviado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Envio de email: " . $msgConcilia;
                                        }

                                        if ($msgConcilia == "") {
                                                $smonitor .= "Venda processada: <a href='/gamer/vendas/com_venda_detalhe.php?venda_id=$vg_id' target='_blank'>$vg_id</a> (R$" . number_format($total_geral, 2, '.', '.') . ")<br>";
                                        }
                                } else {
                                        // ===========================================================	
                                        // Processa vendas de usu�rios integra��o com pagamento online

                                        grava_log_integracao_tmp("Integra��o Debug 4_bko Pagto Online: " . date("Y-m-d H:i:s") . PHP_EOL . "      vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id', vg_id: $vg_id" . PHP_EOL);

                                        // Prepara concilia��o
                                        $parametros['ultimo_status_obs'] = "Concilia��o autom�tica pagamento online em " . date('d/m/Y - H:i:s') . PHP_EOL;
                                        if (trim($vg_ultimo_status_obs) != "") {
                                                $vg_ultimo_status_obs = substr($vg_ultimo_status_obs, -1023);
                                                $ultimo_status_obs_sem_trim = $vg_ultimo_status_obs . PHP_EOL . $parametros['ultimo_status_obs'];
                                                $parametros['ultimo_status_obs'] = substr($ultimo_status_obs_sem_trim, -1023);
                                        }
                                        $parametros['PROCESS_AUTOM'] = '1';
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (A): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //Concilia
                                        $msgConcilia = "";
                                        if ($msgConcilia == "") {

                                                $msgConcilia = conciliaVendaGames_Integracao($vg_id, $pag_codigo, 1, $parametros);

                                                if ($msgConcilia == "")
                                                        $msg .= "Conciliacao(C_I): Conciliado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Conciliacao: " . $msgConcilia;
                                        }

                                        if ($msgConcilia == "") {
                                                $parametros['vg_integracao_parceiro_origem_id'] = $vg_integracao_parceiro_origem_id;
                                                $parametros['ultimo_status_obs'] = "Processa integra��o em notify Pagtos Online (" . date("Y-m-d H:i:s") . ") Parceiro: $vg_integracao_parceiro_origem_id, ip_id: $ip_id, vg_id: $vg_id";
                                                $msgConcilia = processaVendaGamesIntegracao($vg_id, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Processamento Integra��o (Pagtos Online): Processado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Processamento (5): " . $msgConcilia;
                                        }

                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (C2): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        if ($msgConcilia == "") {
                                                //Usuario backoffice
                                                $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
                                                if ($parametros['PROCESS_AUTOM'] == '1')
                                                        $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

                                                $sql = "update tb_venda_games set 
                                            vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "'," . PHP_EOL . "
                                            vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . "," . PHP_EOL . "
                                            vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'], "") . PHP_EOL . "
                                            where vg_id = " . $vg_id;
                                                $ret = SQLexecuteQuery($sql);
                                                if (!$ret)
                                                        $msg = "Erro ao atualizar venda." . PHP_EOL;
                                        }
                                        if ($msgConcilia == "") {
                                                $url_notify_url = getPartner_param_By_ID('notify_url', $vg_integracao_parceiro_origem_id);
                                                $partner_do_notify = getPartner_param_By_ID('partner_do_notify', $vg_integracao_parceiro_origem_id);
                                                $s_msg = str_repeat("*", 80) . PHP_EOL . (($partner_do_notify == 1) ? "VAI FAZER NOTIFY" : "Sem notify") . PHP_EOL;
                                                $s_msg .= "    vg_integracao_parceiro_origem_id: $vg_integracao_parceiro_origem_id" . PHP_EOL . "    partner_do_notify: $partner_do_notify" . PHP_EOL . "    url_notify_url: '$url_notify_url'" . PHP_EOL;
                                                grava_log_integracao_tmp(str_repeat("*", 80) . PHP_EOL . "Vai processar integra��o:" . PHP_EOL . $s_msg);
                                                if ($partner_do_notify == 1 && ($url_notify_url != "")) {

                                                        // Monta o passo 4 da Integra��o - Notify partner
                                                        $sql = "SELECT * FROM tb_integracao_pedido ip 
                                                    WHERE 1=1
                                                    and ip_store_id = '" . $vg_integracao_parceiro_origem_id . "'
                                                    and ip_vg_id = '" . $vg_id . "'";
                                                        grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . "Select  registro de integra��o para o notify (A2)" . PHP_EOL . $sql . PHP_EOL);

                                                        $rs = SQLexecuteQuery($sql);
                                                        if (!$rs) {
                                                                $msg_1 = date("Y-m-d H:i:s") . " - Erro ao recuperar transa��o de integra��o (store_id: '" . $vg_integracao_parceiro_origem_id . "', vg_id: $vg_id)." . PHP_EOL;
                                                                echo $msg_1;
                                                                grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . $msg_1);
	                                                        } else {
	                                                                $rs_row = pgFetchArraySafe($rs);

	                                                                if (!$rs_row) {
	                                                                        $msg_1 = date("Y-m-d H:i:s") . " - Transacao de integracao nao encontrada para notify (store_id: '" . $vg_integracao_parceiro_origem_id . "', vg_id: $vg_id)." . PHP_EOL;
	                                                                        echo $msg_1;
	                                                                        grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . $msg_1);
	                                                                } else {
	                                                                $post_parameters = "store_id=" . $rs_row["ip_store_id"] . "&";
	                                                                $post_parameters .= "transaction_id=" . $rs_row["ip_transaction_id"] . "&";
                                                                $post_parameters .= "order_id=" . $rs_row["ip_order_id"] . "&";
                                                                $post_parameters .= "amount=" . $rs_row["ip_amount"] . "&";
                                                                if (strlen($rs_row["ip_product_id"]) > 0) {
                                                                        $post_parameters .= "product_id=" . $rs_row["ip_product_id"] . "&";
                                                                }
                                                                $post_parameters .= "client_email=" . $rs_row["ip_client_email"] . "&";
                                                                $post_parameters .= "client_id=" . $rs_row["ip_client_id"] . "&";
                                                                $post_parameters .= "currency_code=" . $rs_row["ip_currency_code"];
                                                                $sret1 = getIntegracaoCURL($url_notify_url, $post_parameters);
                                                                $sret = $sret1;

	                                                                $s_msg = "AFTER Partner Notify - Conciliacao Automatica de Pagamento Online (" . date("Y-m-d H:i:s") . ")" . PHP_EOL . " - result: " . PHP_EOL . str_repeat("_", 80) . PHP_EOL . $sret . PHP_EOL . str_repeat("-", 80) . PHP_EOL;
	                                                                grava_log_integracao_tmp(str_repeat("*", 80) . PHP_EOL . "Retorno de getIntegracaoCURL (2): " . PHP_EOL . print_r($post_parameters, true) . PHP_EOL . $s_msg . PHP_EOL);
	                                                                }
	                                                        }
	                                                }
	                                        }
	                                        if ($msgConcilia == "") {
	                                                $smonitor .= "Venda processada: ('$vg_integracao_parceiro_origem_id') <a href='/gamer/vendas/com_venda_detalhe.php?venda_id=$vg_id' target='_blank'>$vg_id</a> (R$" . number_format($total_geral, 2, '.', '.') . ")<br>";
	                                        }
                                }
                                /*
                                ================ Banco EPP - End
                                */

                                if ($bDebug) {
                                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (D): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                }
                        }        // Obtem vg_pagto_num_docto
                } // Para cada venda
        }

        // Em certos casos o status da venda fica em vg_ultimo_status=3 (PAGTO_CONFIRMADO) com o pagamento completo e os PINs entregues
        //	-> tem que passar para vg_ultimo_status=5 (VENDA_REALIZADA)
        $sql = "select vg.vg_id, pag.status, vg.vg_ultimo_status  
                                from tb_venda_games vg 
                                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                        inner join tb_pag_compras pag on pag.idvenda = vg.vg_id 
                                where (vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . "	
                                                and " . getSQLWhereParaVendaPagtoOnline(false) . "
                                                ) 
                                        and vg_pagto_data_inclusao > '2009-01-01' and (not vgm_pin_codinterno='') 
                                        and vg_integracao_parceiro_origem_id is null 
                                order by vg_data_inclusao desc";

        if ($bDebug) {
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (1abc): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        $rs_venda_pendentes = SQLexecuteQuery($sql);
        if ($rs_venda_pendentes && pgNumRowsSafe($rs_venda_pendentes) > 0) {
                while ($rs_venda_pendentes_row = pgFetchArraySafe($rs_venda_pendentes)) {
                        $vg_id_pendente = $rs_venda_pendentes_row['vg_id'];
                        $pag_status = $rs_venda_pendentes_row['status'];
                        $vg_ultimo_status = $rs_venda_pendentes_row['vg_ultimo_status'];

                        if ($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']) {        // 3 and "not vgm_pin_codinterno=''"	(from query) 
                                $sql = "update tb_venda_games
                                                set vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . " 
                                                where vg_id = " . $vg_id_pendente;
                                echo "==>> Atualiza status de venda de '" . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . "' para '" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "' (vg_id pendente = " . $vg_id_pendente . ") " . PHP_EOL;

                                $ret = SQLexecuteQuery($sql);
                                if (!$ret)
                                        $msg = "Erro ao atualizar venda com status pendente (pagamento online)" . PHP_EOL . "$sql." . PHP_EOL;
                                else {
                                        echo "Venda status pendente vg_id:$vg_id_pendente, status ajustado de PAGTO_CONFIRMADO -> VENDA_REALIZADA (" . date('d/m/Y - H:i:s') . ")" . PHP_EOL;
                                }
                        } else {
                                echo "Venda status pendente vg_id:$vg_id_pendente, status do pagamento != " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . ", nada feito (" . date('d/m/Y - H:i:s') . ")" . PHP_EOL;
                        }
                }
        }

        $msg = $header . $msg . "------------------------------------------------------------------------" . PHP_EOL;
        if ($bDebug) {
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME TOTAL: " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        gravaLog_MonitorGamer($smonitor);

        return $msg;
}


function gravaLog_MonitorPedidosDuplicados($mensagem)
{
        $bDebug = false;
        if ($bDebug)
                echo "  SALVA FILE MONITOR (" . date('d/m/Y - H:i:s') . ")" . PHP_EOL;
        // Salva o file monitor para mostrar no Backoffice
        try {
                if ($handle = fopen(RAIZ_DO_PROJETO . 'arquivos_gerados/logs/monitor_integracao_pedidos_duplicados.txt', 'w')) {
                        fwrite($handle, $mensagem . "<br>");

                        fclose($handle);
                } else {
                        echo PHP_EOL . "Error (I): Couldn't open Monitor File for writing" . PHP_EOL;
                }
        } catch (Exception $e) {
                echo "Error(I6) writing monitor file [" . date("Y-m-d H:i:s") . "]: " . $e->getMessage() . PHP_EOL;
        }
}

function gravaLog_TMP_conciliacao($mensagem)
{
        //Arquivo
        $file = RAIZ_DO_PROJETO . "arquivos_gerados/logs/log_pagamento_TMP_conciliacao.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . ($_SERVER["SCRIPT_FILENAME"] ?? "") . PHP_EOL . $mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        }
}

function gravaLog_MonitorGamer($mensagem, $tipopagamento = null)
{
        $bDebug = false;
        if ($bDebug)
                echo "  SALVA FILE MONITOR (" . date('d/m/Y - H:i:s') . ")" . PHP_EOL;
        // Salva o file monitor para mostrar no Backoffice
        try {
                if ($handle = fopen(RAIZ_DO_PROJETO . 'arquivos_gerados/logs/monitorprocessapagtoonline' . $tipopagamento . '.txt', 'w')) {
                        fwrite($handle, $mensagem . "<br>");

                        fclose($handle);
                } else {
                        echo PHP_EOL . "Error: Couldn't open Monitor File for writing" . PHP_EOL;
                }
        } catch (Exception $e) {
                echo "Error(6) writing monitor file [" . date("Y-m-d H:i:s") . "]: " . $e->getMessage() . PHP_EOL;
        }
}


function gravaLog_Debug($mensagem)
{
        //Arquivo
        $file = RAIZ_DO_PROJETO . "arquivos_gerados/logs/log_Debug.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . ($_SERVER["SCRIPT_FILENAME"] ?? "") . PHP_EOL . $mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        }
}

function get_vg_ultimo_status($vg_id)
{
        $vg_ultimo_status = "";
        $sql = "select * from tb_venda_games vg where vg.vg_id = " . $vg_id;
        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                $msg = "Nenhuma venda encontrada." . PHP_EOL;
        else {
                $rs_venda_row = pgFetchArraySafe($rs_venda);
                $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
        }
        return $vg_ultimo_status;
}

// Retorna  true s� se todos os modelos da venda s�o da operadora de Campeonatos
function isVendaCampeonato($venda_id)
{

        $msg = "";
        $b_is_operadora = true;

        //Recupera modelos
        $sql = "select * from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        where vg.vg_id = " . $venda_id;
        $rs_venda_modelos = SQLexecuteQuery($sql);
        if (!$rs_venda_modelos || pgNumRowsSafe($rs_venda_modelos) == 0)
                $msg = "Nenhum produto encontrado." . PHP_EOL;

        if ($msg == "") {
                //Verifica cada item de cada produto
                while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                        $vgm_opr_codigo = $rs_venda_modelos_row['vgm_opr_codigo'];
                        $b_is_operadora = ($b_is_operadora && ($vgm_opr_codigo == $GLOBALS['CAMPEONATO_OPR_ID']));
                }
        } else {
                $b_is_operadora = false;
        }
        return $b_is_operadora;
}


function getInstrucoesPinRequest($ogp_id)
{

	$sql = "select ogp_nome,ogp_descricao,ogp_detalhes_utilizacao,ogp_termos_condicoes from tb_operadora_games_produto where ogp_id = $ogp_id;";
	$rs_produto = SQLexecuteQuery($sql);
	$rs_produto_row = pgFetchArraySafe($rs_produto);
	if (!$rs_produto_row) {
		return "";
	}
	$msg = "<hr>
                <div style='font: normal 10px arial, sans-serif;text-align: justify;font-weight: bold;'>" . $rs_produto_row['ogp_nome'] . "</div><div style='font: normal 10px arial, sans-serif;text-align: justify;'>" . $rs_produto_row['ogp_descricao'] . "</div>
                <div style='font: normal 10px arial, sans-serif;text-align: justify;font-weight: bold;top: 10px;'>Detalhes do Resgate</div><div style='font: normal 10px arial, sans-serif;text-align: justify;'>" . $rs_produto_row['ogp_detalhes_utilizacao'] . "</div>
                <div style='font: normal 10px arial, sans-serif;text-align: justify;font-weight: bold;top: 10px;'>Termos e Condi��es</div><div style='font: normal 10px arial, sans-serif;text-align: justify;'>" . $rs_produto_row['ogp_termos_condicoes'] . "</div>";
        return $msg;
} //end function getInstrucoesPinRequest($ogp_id)

function isVendaIntegracao($venda_id, &$vg_integracao_parceiro_origem_id)
{

        $msg = "";
        $vg_integracao_parceiro_origem_id = "";

        //Recupera modelos
        $sql = "select vg_integracao_parceiro_origem_id from tb_venda_games vg where vg.vg_id = " . $venda_id;
        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                $msg = "Nenhuma venda encontrada." . PHP_EOL;

        if ($msg == "") {
                $rs_venda_row = pgFetchArraySafe($rs_venda);
                $vg_integracao_parceiro_origem_id = $rs_venda_row['vg_integracao_parceiro_origem_id'];
        }
        $b_is_integracao = ($vg_integracao_parceiro_origem_id != "");
        return $b_is_integracao;
}

function isVendaDeposito($venda_id)
{

        $msg = "";

        $sql = "select vg_deposito_em_saldo from tb_venda_games vg where vg.vg_id = " . $venda_id;
        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                $msg = "Nenhuma venda encontrada (em isvendaDeposito($venda_id))." . PHP_EOL;

        if ($msg == "") {
                $rs_venda_row = pgFetchArraySafe($rs_venda);
                $vg_deposito_em_saldo = $rs_venda_row['vg_deposito_em_saldo'];
        }
        $vg_deposito_em_saldo = (($vg_deposito_em_saldo == 1) ? 1 : 0);
        return $vg_deposito_em_saldo;
}

function getValueSingle($sql)
{

        $ret = null;
        $rs = SQLexecuteQuery($sql);
        if ($rs && pgNumRowsSafe($rs) > 0) {
                $rs_row = pgFetchArraySafe($rs);
                $ret = $rs_row[0];
        }
        return $ret;
}

function conciliacaoAutomaticaPagamentoOnlineTipoEspecifico($codigoAlphaNumerico, $codigoNumerico)
{
        global $FORMAS_PAGAMENTO, $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC, $PAGAMENTO_PIN_EPREPAG_NUMERIC, $PAGAMENTO_BANCO_EPP_ONLINE, $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC, $PAGAMENTO_HIPAY_ONLINE_NUMERIC, $PAGAMENTO_PAYPAL_ONLINE_NUMERIC;
        global $cReturn, $cSpaces, $sFontRedOpen, $sFontRedClose;

        $bank_sonda = new bank_sonda();
        if (isset($bank_sonda)) {
                $bank_sonda->load_banks_sonda_array();
        }

        $bDebug = false;
        if ($bDebug) {
                $time_start_stats = getmicrotime();
                $time_start_stats_prev = $time_start_stats;
                echo $cReturn . $cReturn . "Entering  conciliacaoAutomaticaPagamentoOnlineTipoEspecifico(" . $codigoAlphaNumerico . ", " . $codigoNumerico . ") - " . date('Y-m-d - H:i:s') . $cReturn;
                echo "Elapsed time : " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        $nminutes = 1440;        // Por agora 1 dia, depois apenas 90 min
        echo PHP_EOL . "========================================================================" . PHP_EOL;
        echo "Prepara concilia��o de pagamentos online (registros com idvenda>0 e n�o processados nos �ltimos " . $nminutes . " minutos, desde " . date('Y-m-d H:i:s', strtotime("-" . $nminutes . " minutes")) . ")" . PHP_EOL;
        // Prepara concilia��o de pagamentos online
        //		$date_ini = date('Y-m-d', strtotime("-5 days"));	//"2009-01-01"; //date("Y-m-d");
        $date_ini = date('Y-m-d H:i:s', strtotime("-" . $nminutes . " minutes"));
        $date_end = date("Y-m-d H:i:s");

        // Quando o pagamento retorna por sonda (e n�o diretamente do banco) o status_processed=0 mas ainda vg.vg_ultimo_status=3
        // Os dois casos devem ser conciliados
        //		$sql = "select * from tb_pag_compras pgt inner join tb_venda_games vg on vg.vg_id = pgt.idvenda ";
        //		$sql .= "where idvenda>0 and (status_processed=0 or vg.vg_ultimo_status=3) and tipo_cliente='M' ";

        // O anterior est� demorando muito e n�o � necess�rio consultar tb_venda_games, apenas tb_pag_compras
        $sql = "select * from tb_pag_compras pgt where idvenda>0 and status_processed=0 and tipo_cliente='M' and tipo_deposito = 0 and datainicio > (now() -'2 months'::interval) and iforma!='" . $FORMAS_PAGAMENTO['PAGAMENTO_PIX'] . "' and iforma='" . $codigoAlphaNumerico . "' "; // and iforma!='5'and iforma!='6' and iforma!='9' 

        // Op��o 1 - n�o precissa limitar por data - apenas os pagtos com status_processed=0 ser�o retornados, ap�s 90mins eles s�o cancelados.
        //	se houver um descancelamento de venda o pagto correspondente vai aparecer aqui

        // Op��o 1 - Para processar normalmente
        //		$sql .= " and (pgt.datainicio between '".$date_ini."' and '".$date_end."') ";	
        // Op��o 2 - Para incluir algum pagamento antigo descancelado
        //		$sql .= " and ((pgt.datainicio between '".$date_ini."' and '".$date_end."') or (pgt.datainicio between '2010-01-26 00:00:00' and '2010-01-26 23:59:59'))";	

        $rs_total = SQLexecuteQuery($sql);
        if ($bDebug) {
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 0): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        if ($rs_total)
                $registros_total = pgNumRowsSafe($rs_total);
        $sql .= " order by pgt.datainicio desc ";

        if ($bDebug) {
                echo "DEBUG A1: " . $sql . $cReturn;
        }

        $time_start_stats0 = getmicrotime();
        $rs_transacoes = SQLexecuteQuery($sql);
        if ($bDebug) {
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 1): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }
        echo $cReturn . $cReturn . $cReturn . "TIME FOR FIRST QUERY: " . number_format(getmicrotime() - $time_start_stats0, 2, '.', '.') . $cReturn;        //."$sql".$cReturn;

        if (!$rs_transacoes || pgNumRowsSafe($rs_transacoes) == 0)
                $msg = "Nenhuma transa��o encontrada." . $cReturn;

        $time_start_stats0 = getmicrotime();
        $irows = 0;
        if ($rs_transacoes) {
                $npags = pgNumRowsSafe($rs_transacoes);
                $total_pagtos_pendente = 0;
                while ($rs_transacoes_row = pgFetchArraySafe($rs_transacoes)) {
                        $time_start_stats0_in = getmicrotime();
                        $irows++;

                        $msgregister = $rs_transacoes_row['numcompra'] . " - " . $rs_transacoes_row['datainicio'] . " - " . $rs_transacoes_row['datacompra'] . " - " . $rs_transacoes_row['iforma'] . " - " . $rs_transacoes_row['idvenda'] . " - Proc: " . $rs_transacoes_row['status_processed'] . " - " . get_tipo_cliente_descricao($rs_transacoes_row['tipo_cliente']) . " -: R\$" . number_format($rs_transacoes_row['total'] / 100, 2, ',', '.') . " - '" . $rs_transacoes_row['cliente_nome'] . "'" . $cReturn;

                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 2): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }
                        $msg = "";
                        // Venda cadastrada
                        if ($rs_transacoes_row['idvenda'] > 0) {
                                // Pagamento concluido com sucesso -  status=3 em \prepag2\pag\*.php (arquivo de retorno do banco) 
                                if ($rs_transacoes_row['status'] == 3) {
                                        $prefix = getDocPrefix($rs_transacoes_row['iforma']);

                                        $iforma_tmp = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);

                                        // Atualiza dados para tabela vendas
                                        $sql_update = "update tb_venda_games set 
                                                                        vg_pagto_valor_pago		= " . ($rs_transacoes_row['total'] / 100) . ",
                                                                        vg_pagto_tipo			= " . $iforma_tmp . ",
                                                                        vg_pagto_num_docto		= '" . $prefix . $rs_transacoes_row['iforma'] . "_" . $rs_transacoes_row['numcompra'] . "', 
                                                                        vg_pagto_data_inclusao	= '" . $rs_transacoes_row['datainicio'] . "',
                                                                        vg_ultimo_status		= " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "
                                                                where vg_id=" . $rs_transacoes_row['idvenda'] . ";";

                                        $rs_update = SQLexecuteQuery($sql_update);
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 3): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }

                                        $sout = $rs_transacoes_row['datainicio'] . PHP_EOL . "   " . $rs_transacoes_row['numcompra'] . PHP_EOL . "   tipo: (" . $rs_transacoes_row['iforma'] . ") " . $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$rs_transacoes_row['iforma']] . "," . PHP_EOL . "   idvenda: " . $rs_transacoes_row['idvenda'] . "." . PHP_EOL;
                                        if (!$rs_update) {
                                                $msg = "Erro atualizando registro (61)." . $cReturn . "($sql_update)" . $cReturn . $sout . $cReturn;
                                                echo $msg;
                                        } else {
                                                echo "Pagamento atualizado com sucesso (432345)." . $cReturn;
                                        }

                                        // Pagamento ainda n�o foi feito ou n�o tem confirma��o bancaria -  status=1 -> Sonda o banco, se estiver completo atualiza aqui
                                } else if ($rs_transacoes_row['status'] == 1) {
                                        if (isset($bank_sonda)) {
                                                if ($bank_sonda->is_bank_blocked($rs_transacoes_row['iforma'])) {
                                                        echo "Banco '" . $rs_transacoes_row['iforma'] . "' BLOQUEADO para Sonda (" . date("Y-m-d H:i:s") . ")" . PHP_EOL;
                                                        // Loop to the next register
                                                        // continue;
                                                } else {
                                                        echo "Banco '" . $rs_transacoes_row['iforma'] . "' LIBERADO para Sonda (" . date("Y-m-d H:i:s") . ")" . PHP_EOL;
                                                }
                                        }

                                        // bloqueio para evitar consulta ao MUP
                                        //if(false) 
                                        {

                                                if ($bDebug) {
                                                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 4a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                                        $time_start_stats_4a = getmicrotime();
                                                }

                                                // come�a aqui nova fun��o getSondaBanco()

                                                $dataconfirma = date("Y-m-d H:i:s");                // "CURRENT_TIMESTAMP";	// 
                                                $s_sonda = "????";
                                                //$valtotal = 0;
                                                unset($aline5);
                                                unset($aline6);
                                                unset($aline9);
                                                //unset($alineA);
                                                unset($alineC);
                                                $s_update_status_lr = "";

                                                if (isset($bank_sonda)) {
                                                        $bank_sonda->set_last_numcompra($rs_transacoes_row['iforma'], $rs_transacoes_row['numcompra']);
                                                        $bank_sonda->start_time_waiting_for_sonda($rs_transacoes_row['iforma']);
                                                }

                                                if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {

                                                        // obtem status, OK se status='081'
                                                        $b_sonda_5 = getTransacaoPagamentoOK("Transf", $rs_transacoes_row['numcompra'], $aline5);

                                                        // Se existe registro da transa��o -> salva data 
                                                        if ((is_array($aline5)) && (count($aline5) > 0)) {
                                                                $s_sonda = (($b_sonda_5) ? "OK" : "none");
                                                                $sBanco = "Bradesco";

                                                                $dataconfirma = "'" . date('Y-m-d H:i:s') . "'";
                                                                gravaLog_TMP_conciliacao("Em concilia��o - Sonda de Pagto BRD5 (" . $rs_transacoes_row['numcompra'] . ")." . PHP_EOL . print_r($aline5, true) . PHP_EOL);
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {

                                                        // obtem status, OK se status='003'
                                                        $b_sonda_6 = getTransacaoPagamentoOK("PagtoFacil", $rs_transacoes_row['numcompra'], $aline6);

                                                        // Se existe registro da transa��o -> salva data 	
                                                        if ((isset($aline6[1], $aline6[3], $aline6[4])) && (strlen($aline6[1]) > 0)) {
                                                                $s_sonda = (($b_sonda_6) ? "OK" : "none");
                                                                $sBanco = "Bradesco";
                                                                /*	
                                                                Retorno da Sonda
                                                                                           0123456789
                                                                                [3] => 25/05/2012
                                                                                [4] => 00:16:59
                                                                */
                                                                $dataconfirma = "'" . substr($aline6[3], 6, 4) . "-" . substr($aline6[3], 3, 2) . "-" . substr($aline6[3], 0, 2) . " " . $aline6[4] . "'";
                                                                gravaLog_TMP_conciliacao("Em concilia��o - Sonda de Pagto BRD6 (" . $rs_transacoes_row['numcompra'] . ")." . PHP_EOL . print_r($aline6, true) . PHP_EOL);
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {

                                                        // obtem status, OK se status='003'
                                                        $b_sonda_9 = getTransacaoPagamentoOK("BancodoBrasil", $rs_transacoes_row['numcompra'], $aline9);
                                                        if ($b_sonda_9) {
                                                                $s_sonda = (($b_sonda_9) ? "OK" : "none");
                                                                $sBanco = "Banco do Brasil";
                                                                //     [dataPagamento] => 16092009 20161003
                                                                $dataPagamento = $aline9['dataPagamento'] ?? "";
                                                                echo "=========> Trecho 1 " . $dataPagamento . PHP_EOL;
                                                                if ($dataPagamento !== "" && strpos($dataPagamento, date('Y')) == 4) {
                                                                        $dataconfirma = "'" . substr($dataPagamento, 4, 4) . "-" . substr($dataPagamento, 2, 2) . "-" . substr($dataPagamento, 0, 2) . "'";
                                                                } //end if(strpos($aline9['dataPagamento'], date('Y')) == 4)
                                                                else if ($dataPagamento !== "") {
                                                                        $dataconfirma = "'" . substr($dataPagamento, 0, 4) . "-" . substr($dataPagamento, 4, 2) . "-" . substr($dataPagamento, 6, 2) . "'";
                                                                }
                                                                echo "=========> DEPOIS Trecho 1 " . $dataconfirma . PHP_EOL;
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']) {
                                                        $pedido = str_pad((string)$rs_transacoes_row['id_transacao_itau'], 8, "0", STR_PAD_LEFT);

                                                        $pag_status = getSondaItau($pedido, $a_retorno_itau, $sitPag, $dtPag);
                                                        $b_sonda_A = false;
                                                        $b_sonda_A = (($pag_status == "00") ? true : false);
                                                        if ($b_sonda_A) {
                                                                $s_sonda = (($b_sonda_A) ? "OK" : "none");
                                                                $sBanco = "Banco Ita�";
                                                                //     [dtPag] => 16092009
                                                                $dataconfirma = "'" . substr($dtPag, 4, 4) . "-" . substr($dtPag, 2, 2) . "-" . substr($dtPag, 0, 2) . "'";
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']) {
                                                        $pag_status = getSondaPINsEPP($rs_transacoes_row['numcompra'], $dtPag);
                                                        $b_sonda_E = ($pag_status == 3) ? true : false;
                                                        if ($b_sonda_E) {
                                                                $s_sonda = (($b_sonda_E) ? "OK" : "none");
                                                                $sBanco = "PINs E-Prepag";
                                                                $dataconfirma = "'" . date("Y-m-d") . "'";
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_HIPAY_ONLINE']) {
                                                        $pag_status = "";        //getSondaHipay($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                        $b_sonda_B = ($rs_transacoes_row['status'] == 3) ? true : false;
                                                        if ($b_sonda_B) {
                                                                $s_sonda = (($b_sonda_B) ? "OK" : "none");
                                                                $sBanco = "Banco HiPay";
                                                                $dataconfirma = "'" . date("Y-m-d") . "'";
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_PAYPAL_ONLINE']) {
                                                        $pag_status = "";        //getSondaPayPal($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                        $b_sonda_P = ($rs_transacoes_row['status'] == 3) ? true : false;
                                                        if ($b_sonda_P) {
                                                                $s_sonda = (($b_sonda_P) ? "OK" : "none");
                                                                $sBanco = "Banco PayPal";
                                                                $dataconfirma = "'" . date("Y-m-d") . "'";
                                                        }
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']) {
                                                        $pag_status = "";        //getSondaItau($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                        $b_sonda_Z = ($rs_transacoes_row['status'] == 3) ? true : false;
                                                        if ($b_sonda_Z) {
                                                                $s_sonda = (($b_sonda_Z) ? "OK" : "none");
                                                                $sBanco = "Banco E-Prepag";
                                                                $dataconfirma = "'" . date("Y-m-d") . "'";
                                                        }
                                                } else if (b_IsPagtoCielo($rs_transacoes_row['iforma'])) {
                                                        // obtem status, OK se status='6'
                                                        $b_sonda_C = getTransacaoPagamentoOK("Cielo", $rs_transacoes_row['numcompra'], $alineC);

                                                        // Se existe registro da transa��o -> salva data 	
                                                        if (($alineC['status'] ?? '') == "6") {
                                                                $s_sonda = (($b_sonda_C) ? "OK" : "none");
                                                                $sBanco = "Banco Cielo";
                                                                $dataconfirma = "'" . substr($alineC['data'] ?? '', 0, 19) . "'";
                                                                $s_update_status_lr = ", cielo_status = '" . $alineC['status'] . "', cielo_codigo_lr = '" . ($alineC['codigo_lr'] ?? '') . "' ";
                                                        }
                                                }

                                                if (isset($bank_sonda)) {
                                                        $bank_sonda->stop_time_waiting_for_sonda($rs_transacoes_row['iforma']);
                                                        // Dummy - bloqueia consulta a Ita� para testes
                                                        $bank_sonda->block_bank_if_slow($rs_transacoes_row['iforma']);
                                                }

                                                $dataconfirma = str_replace("/", "-", $dataconfirma);

                                                if ($bDebug) {
                                                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 4b): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . " (waiting for Sonda: " . number_format(getmicrotime() - $time_start_stats_4a, 2, '.', '.') . ") " . $cReturn;
                                                }

                                                // Procura pagamentos em aberto no site do banco (Sonda), se (status=1 & sonda) => "NO SYNC"
                                                $s_sync = "";
                                                $prefix_1 = getDocPrefix($rs_transacoes_row['iforma']);
                                                $vg_pagto_tipo = $rs_transacoes_row['iforma'];

                                                if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
                                                        $s_sync = (($b_sonda_5) ? "NO SYNC" : "");
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
                                                        $s_sync = (($b_sonda_6) ? "NO SYNC" : "");
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
                                                        $s_sync = (($b_sonda_9) ? "NO SYNC" : "");
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']) {
                                                        $s_sync = (($b_sonda_A) ? "NO SYNC" : "");
                                                        // No Itau ajusta 'A' -> 10 (usa numerico em tb_venda_games)
                                                        //							$vg_pagto_tipo = $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC'];
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']) {
                                                        $s_sync = (($b_sonda_E) ? "NO SYNC" : "");
                                                        // No Banco E-Prepag ajusta 'E' -> 998 (usa numerico em tb_venda_games)
                                                        //							$vg_pagto_tipo = $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'];	 
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']) {
                                                        $s_sync = (($b_sonda_Z) ? "NO SYNC" : "");
                                                        // No Banco E-Prepag ajusta 'Z' -> 999 (usa numerico em tb_venda_games)
                                                        //							$vg_pagto_tipo = $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'];	 
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']) {
                                                        $s_sync = (($b_sonda_B) ? "NO SYNC" : "");
                                                        // No Banco HiPay ajusta 'B' -> 11 (usa numerico em tb_venda_games)
                                                        //							$vg_pagto_tipo = $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC'];	 
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']) {
                                                        $s_sync = (($b_sonda_P) ? "NO SYNC" : "");
                                                        // No Banco Paypal ajusta 'P' -> 12 (usa numerico em tb_venda_games)
                                                        //							$vg_pagto_tipo = $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC'];	 
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                } else if (b_IsPagtoCielo($rs_transacoes_row['iforma'])) {
                                                        $s_sync = (($b_sonda_C) ? "NO SYNC" : "");
                                                        $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                                }
                                                if ($bDebug) {
                                                        echo "MSG_543443 - rs_transacoes_row['iforma']: '" . $rs_transacoes_row['iforma'] . "' -> '" . $vg_pagto_tipo . "', s_sync = '" . $s_sync . "' (8765)" . PHP_EOL;
                                                }
                                                // at� aqui nova fun��o getSondaBanco()

                                                // Se (!$s_sync), ou seja (status=1 & sonda) => completa a venda POR SONDA
                                                if ($s_sync == "NO SYNC") {                        /////   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

                                                        if ($bDebug) {
                                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 5a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                                        }

                                                        //Inicia transacao
                                                        if ($msg == "") {
                                                                $sql = "BEGIN TRANSACTION ";
                                                                $ret = SQLexecuteQuery($sql);
                                                                $ret = true;                                                                                                /////////////////////
                                                                if (!$ret)
                                                                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
                                                        }

                                                        // Marca registro como processado (status_processed=1), e status=3, j� que se chegou aqui quer dizer que n�o passou por confirmaBanco.php
                                                        $sql = "update tb_pag_compras set status_processed=1, datacompra=CURRENT_TIMESTAMP, dataconfirma=" . $dataconfirma . ", status=3 " . $s_update_status_lr . " where numcompra='" . $rs_transacoes_row['numcompra'] . "' ";
                                                        echo PHP_EOL . " NO SYNC => " . $sql . " " . PHP_EOL . "($msg)" . PHP_EOL;
                                                        echo "DEBUG F (atualiza status_processed=1, vendaid = " . $rs_transacoes_row['idvenda'] . "): " . $sql . PHP_EOL;

                                                        $rs_update2 = SQLexecuteQuery($sql);
                                                        if (!$rs_update2) {
                                                                $msg = "Erro atualizando status de registro (62aa)." . $cReturn . "$sql" . $cReturn;
                                                                echo $msg;
                                                        }
                                                        if (!$msg) {

                                                                // Atualiza dados para tabela vendas
                                                                //'DADOS_PAGTO_RECEBIDO' => 2
                                                                $sql_update = "update tb_venda_games set 
                                                                                        vg_pagto_valor_pago		= " . ($rs_transacoes_row['total'] / 100 + $rs_transacoes_row['taxas'] + $rs_transacoes_row['frete'] + $rs_transacoes_row['manuseio']) . ",
                                                                                        vg_pagto_tipo			= " . $vg_pagto_tipo . ",
                                                                                        vg_pagto_num_docto		= '" . $prefix_1 . $rs_transacoes_row['iforma'] . "_" . $rs_transacoes_row['numcompra'] . "', 
                                                                                        vg_pagto_data_inclusao	= '" . $rs_transacoes_row['datainicio'] . "',
                                                                                        vg_usuario_obs			= 'Pagamento Online " . $sBanco . " POR SONDA [" . $rs_transacoes_row['iforma'] . "] em " . date("Y-m-d H:i:s") . "',
                                                                                        vg_ultimo_status		= " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "
                                                                                where vg_id=" . $rs_transacoes_row['idvenda'] . ";";
                                                                $rs_update = SQLexecuteQuery($sql_update);
                                                                $sout = $rs_transacoes_row['datainicio'] . PHP_EOL . "   " . $rs_transacoes_row['numcompra'] . PHP_EOL . "   tipo: (" . $rs_transacoes_row['iforma'] . ") " . getDescricaoPagtoOnline($rs_transacoes_row['iforma']) . "," . PHP_EOL . "   idvenda: " . $rs_transacoes_row['idvenda'] . "." . PHP_EOL;

                                                                if (!$rs_update) {
                                                                        $msg = "Erro atualizando registro POR SONDA (61a)." . $sout . $cReturn;
                                                                        echo $msg;
                                                                } else {
                                                                        echo "Pagamento atualizado POR SONDA com sucesso (4322)." . $sout . $cReturn;
                                                                }
                                                        }
                                                        //Finaliza transacao
                                                        if ($msg == "") {
                                                                $sql = "COMMIT TRANSACTION ";
                                                                $ret = SQLexecuteQuery($sql);
                                                                if (!$ret)
                                                                        $msg = "Erro ao comitar transa��o." . PHP_EOL;

                                                                $msg_sonda = "PROCESSADO POR SONDA";
                                                        } else {
                                                                $sql = "ROLLBACK TRANSACTION ";
                                                                $ret = SQLexecuteQuery($sql);
                                                                if (!$ret)
                                                                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;

                                                                $msg_sonda = "PROCESSAMENTO POR SONDA FALHOU (ROLLBACK TRANSACTION)";
                                                        }

                                                        echo $msg_sonda . ": Sonda='$s_sonda' forma:" . $rs_transacoes_row['iforma'] . ", numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . $rs_transacoes_row['idvenda'] . "- " . $rs_transacoes_row['datainicio'] . " - R\$" . number_format(($rs_transacoes_row['total'] / 100 - $rs_transacoes_row['taxas']), 2, '.', '.') . " (SYNC)" . $cReturn;

                                                        if ($bDebug) {
                                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 5b): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                                        }
                                                } else {
                                                        $total_pagto = $rs_transacoes_row['total'] / 100 - $rs_transacoes_row['taxas'];
                                                        $total_pagtos_pendente += $total_pagto;
                                                        $leading_zeros = (($total_pagto < 1000) ? (($total_pagto < 100) ? "00" : "0") : "");
                                                        echo "N�o Processado por sonda: forma:" . $rs_transacoes_row['iforma'] . ", numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . str_pad($rs_transacoes_row['idvenda'], 8, '0', STR_PAD_LEFT) . " - " . $rs_transacoes_row['datainicio'] . " - R\$" . $leading_zeros . number_format(($total_pagto), 2, '.', '.') . " (NO SYNC) [" . number_format(getmicrotime() - $time_start_stats0_in, 2, '.', '.') . " s] [" . number_format(getmicrotime() - $time_start_stats0, 2, '.', '.') . " s]" . $cReturn;
                                                }
                                        }  // bloqueio para evitar consulta ao MUP

                                } else {
                                        echo "N�o processado: status!=3 e Sonda=false." . $cReturn . "numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . $rs_transacoes_row['idvenda'] . "- " . $rs_transacoes_row['datainicio'] . $cReturn;
                                }
                        } // 
                        else {
                                echo "N�o processado: idvenda=0." . $cReturn . "numcompra: " . $rs_transacoes_row['numcompra'] . "- " . $rs_transacoes_row['datainicio'] . $cReturn;
                        }
                } // End while loop 

                if (isset($bank_sonda)) {
                        echo str_repeat("-", 80) . PHP_EOL . "Lista bank_sonda[]" . PHP_EOL . $bank_sonda->list_registers(false) . PHP_EOL;
                        $aret = $bank_sonda->get_list_blocked_banks();

                        if (count($aret) > 0) {
                                //					echo "Bancos bloqueados: ".print_r($aret, true).PHP_EOL;
                                //					echo "function_exists('getValueSingle'): ".((function_exists('getValueSingle'))?"YES":"Nope").PHP_EOL;
                                $b_unblock_banks = true;
                                // Do an extra Sonda to monitor if Bank is online again
                                foreach ($aret as $key => $val) {
                                        echo "TEST SONDA in '$key' ('" . $val['time_waiting_for_sonda'] . "', '" . $val['last_numcompra'] . "')" . PHP_EOL;
                                        $bank_sonda->start_time_waiting_for_sonda($key);
                                        if ($b_unblock_banks) {
                                                switch ($key) {
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
                                                                $b_sonda_5s = getTransacaoPagamentoOK("Transf", $val['last_numcompra'], $aline5);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
                                                                $b_sonda_6s = getTransacaoPagamentoOK("PagtoFacil", $val['last_numcompra'], $aline6);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
                                                                $b_sonda_9s = getTransacaoPagamentoOK("BancodoBrasil", $val['last_numcompra'], $aline9);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:
                                                                $sql = "select id_transacao_itau from tb_pag_compras where numcompra='" . $val['last_numcompra'] . "'";
                                                                $pedido = str_pad(getValueSingle($sql), 8, "0", STR_PAD_LEFT);
                                                                $pag_status_s = getSondaItau($pedido, $a_retorno_itau, $sitPag, $dtPag);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:
                                                                $sql = "select status from tb_pag_compras where numcompra='" . $val['last_numcompra'] . "'";
                                                                $b_sonda_E_s = (getValueSingle($sql) == 3) ? true : false;
                                                                break;
                                                        case $GLOBALS['PAGAMENTO_HIPAY_ONLINE']:
                                                                //							$pag_statuss = "";	//getSondaHipay($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                                break;
                                                        case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE']:
                                                                //							$pag_statuss = "";	//getSondaPayPal($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                                break;
                                                        case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:
                                                                $sql = "select status from tb_pag_compras where numcompra='" . $val['last_numcompra'] . "'";
                                                                $b_sonda_Zs = (getValueSingle($sql) == 3) ? true : false;
                                                                break;
                                                        default:
                                                                if (b_IsPagtoCielo($key)) {
                                                                        $b_sonda_Cs = getTransacaoPagamentoOK("Cielo", $val['last_numcompra'], $alineC);
                                                                }
                                                }
                                        } else {
                                                echo "  ==  DUMMY - chamada a Sonda est� bloqueada" . PHP_EOL;
                                        }
                                        $bank_sonda->stop_time_waiting_for_sonda($key);
                                        $bank_sonda->unblock_bank_if_normal($key);
                                }
                        } else {
                                echo "  ==  Sem chamada a Sonda - n�o tem Bancos bloqueados" . PHP_EOL;
                        }

                        // Save bank block configuration
                        //$bank_sonda->save_banks_sonda_array();
                }
        } // End if(rs)
        echo "Tempo m�dio de processamento: " . number_format((getmicrotime() - $time_start_stats0) / (($irows > 0) ? $irows : 1), 2, '.', '.') . " s/processamento (WSA)" . $cReturn;
        echo "Total pagamentos pendentes: R\$" . number_format($total_pagtos_pendente, 2, '.', '.') . " em $npags pagamentos (WSATP)" . $cReturn;

        $smonitor = "Tempo m�dio de processamento (" . date("Y-m-d H:i:s") . "): " . number_format((getmicrotime() - $time_start_stats0) / (($irows > 0) ? $irows : 1), 2, '.', '.') . " s/processamento<br>$irows pagamento" . (($irows > 1) ? "s" : "") . " em aberto<br>";

        // ===================================================

        //header
        $header = PHP_EOL . "------------------------------------------------------------------------" . PHP_EOL;
        $header .= "Conciliacao Automatica de Pagamento Online" . PHP_EOL;
        $header .= date('d/m/Y - H:i:s') . PHP_EOL . PHP_EOL;
        $msg = "";
        $bDebug = false;
        if ($bDebug) {
                echo $header . PHP_EOL;
        }
        //	Recupera as vendas pendentes com pagamento online completo 
        if ($msg == "") {
                // 'DADOS_PAGTO_RECEBIDO' 		=> '2',
                // 'PAGTO_CONFIRMADO' 			=> '3',
                $sql = "
                        select * from tb_venda_games vg 
                        where ((vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . " or 
                                        vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . ")
                                and " . getSQLWhereParaVendaPagtoOnline(false) . "
                        )  and vg_data_inclusao > (now() -'2 months'::interval)
                        and vg_pagto_tipo=" . $codigoNumerico . " 
                        order by vg_data_inclusao desc
                        ";

                //	Vendas de integra��o s�o levantadas aqui para executar o notify
                //	n�o precisssa de: "and vg_integracao_parceiro_origem_id is null"

                $time_start_stats = getmicrotime();

                if ($bDebug) {
                        echo "DEBUG_ABCD: " . $sql . PHP_EOL;
                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0000): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                }
                $rs_venda = SQLexecuteQuery($sql);
                if ($bDebug) {
                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (00): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                }

                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
        }

        //Busca vendas
        if ($msg == "") {
                while ($rs_venda_row = pgFetchArraySafe($rs_venda)) {
                        $vg_id = $rs_venda_row['vg_id'];
                        $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
                        $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_ultimo_status_obs = $rs_venda_row['vg_ultimo_status_obs'];
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_integracao_parceiro_origem_id = $rs_venda_row['vg_integracao_parceiro_origem_id'];
                        if ($bDebug) {
                                echo "DEBUG - YTRE: vg_pagto_tipo: '$vg_pagto_tipo', vg_ultimo_status: $vg_ultimo_status" . PHP_EOL;
                        }
                        grava_log_integracao_tmp(PHP_EOL . PHP_EOL . str_repeat("*", 80) . PHP_EOL . "Integra��o Debug B_bko: " . date("Y-m-d H:i:s") . PHP_EOL . "  vg_id: $vg_id, vg_pagto_banco: $vg_pagto_banco, vg_ultimo_status: $vg_ultimo_status, vg_pagto_tipo: $vg_pagto_tipo, vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id'" . PHP_EOL . "  " . (($vg_integracao_parceiro_origem_id != "") ? "Integra��o - parceiro " . $vg_integracao_parceiro_origem_id : "N�o � integra��o (1)") . PHP_EOL);

                        //obtem o valor total da venda
                        //----------------------------------------------------
                        $total_geral = 0;
                        $sql = "select * from tb_venda_games vg " .
                                "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                                "where vg.vg_id = " . $vg_id;
                        if ($bDebug) {
                                echo "DEBUG (A1): " . $sql . PHP_EOL;
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0-): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }

                        $rs_venda_modelos = SQLexecuteQuery($sql);
                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0+): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                echo "get_vg_ultimo_status($vg_id) 000: " . get_vg_ultimo_status($vg_id) . PHP_EOL;
                        }
                        if ($rs_venda_modelos && pgNumRowsSafe($rs_venda_modelos) > 0) {
                                while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                                        $qtde = $rs_venda_modelos_row['vgm_qtde'];
                                        $valor = $rs_venda_modelos_row['vgm_valor'];
                                        $total_geral += $valor * $qtde;
                                        if ($vg_integracao_parceiro_origem_id) {
                                                // Para integra��o salva o ID de produto (sempre � um modelo por venda)
                                                $vgm_ogp_id = $rs_venda_modelos_row['vgm_ogp_id'];
                                                echo "  TESTA PRODUTO EM INTEGRA��O PAG >> ['" . $rs_venda_modelos_row['vg_integracao_parceiro_origem_id'] . "'] ->  [vg_id: '" . $rs_venda_modelos_row['vg_id'] . "'; vgm_ogp_id: '$vgm_ogp_id']- qtde: '$qtde', valor: '$valor' " . $cReturn;
                                        }
                                }
                        }
                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }
                        $rs_pag = SQLexecuteQuery($sql);
                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }
                        if ($rs_pag && pgNumRowsSafe($rs_pag) > 0) {
                                $rs_pag_row = pgFetchArraySafe($rs_pag);
                                // Remove o prefixo "BRD6_"/"BRD5_"/"BBR9_"/"BITA_"/"HIPB_"/"PYPP_"/"BEPZ_"
                                $pag_codigo = substr($rs_pag_row['vg_pagto_num_docto'], 5);
                                $vg_integracao_parceiro_origem_id = $rs_pag_row['vg_integracao_parceiro_origem_id'];

                                $ip_id = (($vg_integracao_parceiro_origem_id) ? getIntegracaoPedidoID_By_Venda($vg_integracao_parceiro_origem_id, $vg_id) : 0);

                                $msg .= PHP_EOL . "Venda: " . $vg_id . ", Pagamento: " . $pag_codigo . " (" . $rs_pag_row['vg_pagto_num_docto'] . ")" . PHP_EOL;
                                if ($bDebug) {
                                        echo PHP_EOL . "Venda: " . $vg_id . ", Pagamento: " . $pag_codigo . PHP_EOL;
                                }

                                echo "  LOGP>> vg_id: $vg_id, pag_codigo: '$pag_codigo', ip_id: $ip_id, vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id'" . $cReturn;


                                /*
                                ================ Banco EPP - Start
                                 */
                                // Processa pagamentos online sem integra��o (Usu�rios Money com pagamento online)
                                if (trim($vg_integracao_parceiro_origem_id) == '') {

                                        // Prepara concilia��o
                                        $parametros['ultimo_status_obs'] = "Concilia��o autom�tica pagamento online em " . date('d/m/Y - H:i:s') . PHP_EOL;
                                        if (trim($vg_ultimo_status_obs) != "") {
                                                $vg_ultimo_status_obs = substr($vg_ultimo_status_obs, -1023);
                                                $ultimo_status_obs_sem_trim = $vg_ultimo_status_obs . PHP_EOL . $parametros['ultimo_status_obs'];
                                                $parametros['ultimo_status_obs'] = substr($ultimo_status_obs_sem_trim, -1023);
                                        }
                                        $parametros['PROCESS_AUTOM'] = '1';
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (A): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //Concilia
                                        $msgConcilia = "";
                                        if ($msgConcilia == "") {
                                                $msgConcilia = conciliaVendaGames_PagamentoOnline($vg_id, $pag_codigo, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Conciliacao(C1): Conciliado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Conciliacao: " . $msgConcilia;
                                        }
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (B, vg_pagto_tipo: '$vg_pagto_tipo', PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC: " . $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'] . "): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //Associa pins, gera venda e credita saldo
                                        if ($msgConcilia == "") {
                                                $msgConcilia = processaVendaGames($vg_id, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Processamento (Pagtos Online): Processado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Processamento (4): " . $msgConcilia;
                                        }
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (C1): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //envia email para o cliente de pagamento online
                                        if ($msgConcilia == "") {
                                                $msgConcilia = processaEmailVendaGames($vg_id, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Envio de email: Enviado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Envio de email: " . $msgConcilia;
                                        }

                                        if ($msgConcilia == "") {
                                                $smonitor .= "Venda processada: <a href='/gamer/vendas/com_venda_detalhe.php?venda_id=$vg_id' target='_blank'>$vg_id</a> (R$" . number_format($total_geral, 2, '.', '.') . ")<br>";
                                        }
                                } else {
                                        // ===========================================================	
                                        // Processa vendas de usu�rios integra��o com pagamento online

                                        grava_log_integracao_tmp("Integra��o Debug 4_bko Pagto Online: " . date("Y-m-d H:i:s") . PHP_EOL . "      vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id', vg_id: $vg_id" . PHP_EOL);

                                        // Prepara concilia��o
                                        $parametros['ultimo_status_obs'] = "Concilia��o autom�tica pagamento online em " . date('d/m/Y - H:i:s') . PHP_EOL;
                                        if (trim($vg_ultimo_status_obs) != "") {
                                                $vg_ultimo_status_obs = substr($vg_ultimo_status_obs, -1023);
                                                $ultimo_status_obs_sem_trim = $vg_ultimo_status_obs . PHP_EOL . $parametros['ultimo_status_obs'];
                                                $parametros['ultimo_status_obs'] = substr($ultimo_status_obs_sem_trim, -1023);
                                        }
                                        $parametros['PROCESS_AUTOM'] = '1';
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (A): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //Concilia
                                        $msgConcilia = "";
                                        if ($msgConcilia == "") {
                                                $msgConcilia = conciliaVendaGames_Integracao($vg_id, $pag_codigo, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Conciliacao(C_I): Conciliado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Conciliacao: " . $msgConcilia;
                                        }

                                        if ($msgConcilia == "") {
                                                $parametros['vg_integracao_parceiro_origem_id'] = $vg_integracao_parceiro_origem_id;
                                                $parametros['ultimo_status_obs'] = "Processa integra��o em notify Pagtos Online (" . date("Y-m-d H:i:s") . ") Parceiro: $vg_integracao_parceiro_origem_id, ip_id: $ip_id, vg_id: $vg_id";
                                                $msgConcilia = processaVendaGamesIntegracao($vg_id, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Processamento Integra��o (Pagtos Online): Processado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Processamento (5): " . $msgConcilia;
                                        }

                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (C2): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        if ($msgConcilia == "") {
                                                //Usuario backoffice
                                                $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
                                                if ($parametros['PROCESS_AUTOM'] == '1')
                                                        $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

                                                $sql = "update tb_venda_games set 
                                                        vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "'," . PHP_EOL . "
                                                        vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . "," . PHP_EOL . "
                                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'], "") . PHP_EOL . "
                                                        where vg_id = " . $vg_id;
                                                $ret = SQLexecuteQuery($sql);
                                                if (!$ret)
                                                        $msg = "Erro ao atualizar venda." . PHP_EOL;
                                        }
                                        if ($msgConcilia == "") {
                                                $url_notify_url = getPartner_param_By_ID('notify_url', $vg_integracao_parceiro_origem_id);
                                                $partner_do_notify = getPartner_param_By_ID('partner_do_notify', $vg_integracao_parceiro_origem_id);
                                                $s_msg = str_repeat("*", 80) . PHP_EOL . (($partner_do_notify == 1) ? "VAI FAZER NOTIFY" : "Sem notify") . PHP_EOL;
                                                $s_msg .= "    vg_integracao_parceiro_origem_id: $vg_integracao_parceiro_origem_id" . PHP_EOL . "    partner_do_notify: $partner_do_notify" . PHP_EOL . "    url_notify_url: '$url_notify_url'" . PHP_EOL;
                                                grava_log_integracao_tmp(str_repeat("*", 80) . PHP_EOL . "Vai processar integra��o:" . PHP_EOL . $s_msg);
                                                if ($partner_do_notify == 1 && ($url_notify_url != "")) {

                                                        // Monta o passo 4 da Integra��o - Notify partner
                                                        $sql = "SELECT * FROM tb_integracao_pedido ip 
                                                        WHERE 1=1
                                                        and ip_store_id = '" . $vg_integracao_parceiro_origem_id . "'
                                                        and ip_vg_id = '" . $vg_id . "'";
                                                        grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . "Select  registro de integra��o para o notify (A2)" . PHP_EOL . $sql . PHP_EOL);

                                                        $rs = SQLexecuteQuery($sql);
                                                        if (!$rs) {
                                                                $msg_1 = date("Y-m-d H:i:s") . " - Erro ao recuperar transa��o de integra��o (store_id: '" . $vg_integracao_parceiro_origem_id . "', vg_id: $vg_id)." . PHP_EOL;
                                                                echo $msg_1;
                                                                grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . $msg_1);
	                                                        } else {
	                                                                $rs_row = pgFetchArraySafe($rs);

	                                                                if (!$rs_row) {
	                                                                        $msg_1 = date("Y-m-d H:i:s") . " - Transacao de integracao nao encontrada para notify (store_id: '" . $vg_integracao_parceiro_origem_id . "', vg_id: $vg_id)." . PHP_EOL;
	                                                                        echo $msg_1;
	                                                                        grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . $msg_1);
	                                                                } else {
	                                                                $post_parameters = "store_id=" . $rs_row["ip_store_id"] . "&";

	                                                                $post_parameters .= "transaction_id=" . $rs_row["ip_transaction_id"] . "&";
                                                                $post_parameters .= "order_id=" . $rs_row["ip_order_id"] . "&";
                                                                $post_parameters .= "amount=" . $rs_row["ip_amount"] . "&";
                                                                if (strlen($rs_row["ip_product_id"]) > 0) {
                                                                        $post_parameters .= "product_id=" . $rs_row["ip_product_id"] . "&";
                                                                }
                                                                $post_parameters .= "client_email=" . $rs_row["ip_client_email"] . "&";
                                                                $post_parameters .= "client_id=" . $rs_row["ip_client_id"] . "&";

                                                                $post_parameters .= "currency_code=" . $rs_row["ip_currency_code"];

                                                                $sret1 = getIntegracaoCURL($url_notify_url, $post_parameters);
                                                                $sret = $sret1;

                                                                $s_msg = "AFTER Partner Notify - Conciliacao Automatica de Pagamento Online (" . date("Y-m-d H:i:s") . ")" . PHP_EOL . " - result: " . PHP_EOL . str_repeat("_", 80) . PHP_EOL . $sret . PHP_EOL . str_repeat("-", 80) . PHP_EOL;
                                                                grava_log_integracao_tmp(str_repeat("*", 80) . PHP_EOL . "Retorno de getIntegracaoCURL (2): " . PHP_EOL . print_r($post_parameters, true) . PHP_EOL . $s_msg . PHP_EOL);
	                                                        }
	                                                }
	                                        }
	                                        }
	                                        if ($msgConcilia == "") {
	                                                $smonitor .= "Venda processada: ('$vg_integracao_parceiro_origem_id') <a href='/gamer/vendas/com_venda_detalhe.php?venda_id=$vg_id' target='_blank'>$vg_id</a> (R$" . number_format($total_geral, 2, '.', '.') . ")<br>";
	                                        }
                                }
                                /*
                                ================ Banco EPP - End
                                */
                                if ($bDebug) {
                                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (D): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                }
                        }        // Obtem vg_pagto_num_docto
                } // Para cada venda
        }

        // Em certos casos o status da venda fica em vg_ultimo_status=3 (PAGTO_CONFIRMADO) com o pagamento completo e os PINs entregues
        //	-> tem que passar para vg_ultimo_status=5 (VENDA_REALIZADA)
        $sql = "select vg.vg_id, pag.status, vg.vg_ultimo_status  
                                from tb_venda_games vg 
                                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                        inner join tb_pag_compras pag on pag.idvenda = vg.vg_id 
                                where (vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . "	
                                                and " . getSQLWhereParaVendaPagtoOnline(false) . "
                                                ) 
                                        and vg_pagto_data_inclusao > '2009-01-01' and (not vgm_pin_codinterno='') 
                                        and vg_integracao_parceiro_origem_id is null 
                                order by vg_data_inclusao desc";

        if ($bDebug) {
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (1abc): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        $rs_venda_pendentes = SQLexecuteQuery($sql);
        if ($rs_venda_pendentes && pgNumRowsSafe($rs_venda_pendentes) > 0) {
                while ($rs_venda_pendentes_row = pgFetchArraySafe($rs_venda_pendentes)) {
                        $vg_id_pendente = $rs_venda_pendentes_row['vg_id'];
                        $pag_status = $rs_venda_pendentes_row['status'];
                        $vg_ultimo_status = $rs_venda_pendentes_row['vg_ultimo_status'];

                        if ($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']) {        // 3 and "not vgm_pin_codinterno=''"	(from query) 
                                $sql = "update tb_venda_games
                                                set vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . " 
                                                where vg_id = " . $vg_id_pendente;
                                echo "==>> Atualiza status de venda de '" . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . "' para '" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "' (vg_id pendente = " . $vg_id_pendente . ") " . PHP_EOL;

                                $ret = SQLexecuteQuery($sql);
                                if (!$ret)
                                        $msg = "Erro ao atualizar venda com status pendente (pagamento online)" . PHP_EOL . "$sql." . PHP_EOL;
                                else {
                                        echo "Venda status pendente vg_id:$vg_id_pendente, status ajustado de PAGTO_CONFIRMADO -> VENDA_REALIZADA (" . date('d/m/Y - H:i:s') . ")" . PHP_EOL;
                                }
                        } else {
                                echo "Venda status pendente vg_id:$vg_id_pendente, status do pagamento != " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . ", nada feito (" . date('d/m/Y - H:i:s') . ")" . PHP_EOL;
                        }
                }
        }

        $msg = $header . $msg . "------------------------------------------------------------------------" . PHP_EOL;
        if ($bDebug) {
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME TOTAL: " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        gravaLog_MonitorGamer($smonitor, $codigoNumerico);

        return $msg;
} //end function conciliacaoAutomaticaPagamentoOnlineTipoEspecifico


function conciliacaoAutomaticaPagtoPIXemGAMER($webhook = false, $venda = 0)
{
        global $FORMAS_PAGAMENTO, $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC, $PAGAMENTO_PIN_EPREPAG_NUMERIC, $PAGAMENTO_BANCO_EPP_ONLINE, $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC, $PAGAMENTO_HIPAY_ONLINE_NUMERIC, $PAGAMENTO_PAYPAL_ONLINE_NUMERIC, $PAGAMENTO_PIX_NUMERIC;
        global $cReturn, $cSpaces, $sFontRedOpen, $sFontRedClose;

        $bDebug = false;
        $minutes = 1440;

        if ($bDebug) {
                $time_start_stats = getmicrotime();
                $time_start_stats_prev = $time_start_stats;
                echo $cReturn . $cReturn . "Entering  conciliacaoAutomaticaPagtoPIXemGAMER() - " . date('Y-m-d - H:i:s') . $cReturn;
                echo "Elapsed time : " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        echo PHP_EOL . "========================================================================" . PHP_EOL;
        echo "Prepara concilia��o de pagamentos PIX (registros com idvenda>0 e n�o processados nos �ltimos " . $minutes . " minutos, desde " . date('Y-m-d H:i:s', strtotime("-" . $minutes . " minutes")) . ")" . PHP_EOL;

        // Prepara concilia��o de pagamentos online

        if ($webhook === true) {
                $sql = "select * from tb_pag_compras pgt where idvenda>0 and status_processed=0 and tipo_cliente='M' and idvenda = $1 and tipo_deposito = 0 and (pgt.datainicio > (now() -'" . $minutes . " minutes'::interval)) AND status = 1 AND iforma=$2 ";
                $sql .= " order by pgt.datainicio desc ";
                $params = [$venda, $FORMAS_PAGAMENTO['PAGAMENTO_PIX']];
        } else {
                $sql = "select * from tb_pag_compras pgt where idvenda>0 and status_processed=0 and tipo_cliente='M' and tipo_deposito = 0 and (pgt.datainicio > (now() -'" . $minutes . " minutes'::interval)) AND status = 1 AND iforma=$1 ";
                $sql .= " order by pgt.datainicio desc ";
                $params = [$FORMAS_PAGAMENTO['PAGAMENTO_PIX']];
        }


        $rs_transacoes = SQLexecuteQueryParams($sql, $params);
        $time_start_stats0 = getmicrotime();
        if ($bDebug) {
                echo "DEBUG A1: " . $sql . $cReturn;
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 1): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }
        //echo $cReturn.$cReturn.$cReturn."TIME FOR FIRST QUERY: ".number_format(getmicrotime() - $time_start_stats0, 2, '.', '.').$cReturn;	//."$sql".$cReturn;

        if (!$rs_transacoes || pgNumRowsSafe($rs_transacoes) == 0)
                $msg = "Nenhuma transa��o encontrada." . $cReturn;

        $time_start_stats0 = getmicrotime();
        $irows = 0;
        if ($rs_transacoes) {

                $fileLog = @fopen("/www/arquivos_gerados/logs/log_vendaPIX.txt", "a+");
                if ($fileLog) {
                writeLogVendaPixSafe($fileLog, "DATA DE REQUISI��O: " . date("d-m-Y H:i:s") . "\n");
                writeLogVendaPixSafe($fileLog, "MODO DE CONCILIA��O: " . (($webhook === true) ? "WEBHOOK" : "SONDA") . "\n");
                }

                $registros_total = pgNumRowsSafe($rs_transacoes);
                $npags = $registros_total;
                $total_pagtos_pendente = 0;
                while ($rs_transacoes_row = pgFetchArraySafe($rs_transacoes)) {
                        $time_start_stats0_in = getmicrotime();
                        $irows++;

                        $msgregister = $rs_transacoes_row['numcompra'] . " - " . $rs_transacoes_row['datainicio'] . " - " . $rs_transacoes_row['datacompra'] . " - " . $rs_transacoes_row['iforma'] . " - " . $rs_transacoes_row['idvenda'] . " - Proc: " . $rs_transacoes_row['status_processed'] . " - " . get_tipo_cliente_descricao($rs_transacoes_row['tipo_cliente']) . " -: R\$" . number_format($rs_transacoes_row['total'] / 100, 2, ',', '.') . " - '" . $rs_transacoes_row['cliente_nome'] . "'" . $cReturn;

                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 2): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }
                        $msg = "";

                        // Venda cadastrada
                        if ($rs_transacoes_row['idvenda'] > 0) {

                                if ($bDebug) {
                                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 4a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        $time_start_stats_4a = getmicrotime();
                                }

                                // come�a aqui nova fun��o getSondaBanco()

                                $dataconfirma = date("Y-m-d H:i:s");
                                unset($alinePIX);

                                if ($webhook === true) {
                                        $b_sonda_PIX = null;
                                        $s_sonda = "none";
                                } else {
                                        $b_sonda_PIX = getTransacaoPagamentoOK($GLOBALS['PAGAMENTO_PIX_NOME_BANCO'], $rs_transacoes_row['numcompra'], $alinePIX);
                                        $s_sonda = (($b_sonda_PIX) ? "OK" : "none");
                                }

                                if ($webhook === true) {
                                        $dataconfirma = "CURRENT_TIMESTAMP";
                                        $sBanco = $GLOBALS['PAGAMENTO_PIX_NOME_BANCO'];
                                        $legenda = 'Pagamento via WEB HOOK ' . $sBanco . ' [R] em ' . date("Y-m-d H:i:s");
                                } else if ($b_sonda_PIX) {
                                        $sBanco = $GLOBALS['PAGAMENTO_PIX_NOME_BANCO'];
                                        $legenda = 'Pagamento PIX ' . $sBanco . ' POR SONDA [R] em ' . date("Y-m-d H:i:s");
                                        //pegar a data do JSON
                                        $dataconfirma = "'" . substr(str_replace('T', ' ', $alinePIX), 0, 19) . "'";
                                }

                                if ($bDebug) {
                                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 4b): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . " (waiting for Sonda: " . number_format(getmicrotime() - $time_start_stats_4a, 2, '.', '.') . ") " . $cReturn;
                                }

                                // Procura pagamentos em aberto no site do banco (Sonda), se (status=1 & sonda) => "NO SYNC"
                                $s_sync = "";
                                $prefix_1 = getDocPrefix($rs_transacoes_row['iforma']);

                                $s_sync = (($b_sonda_PIX) ? "NO SYNC" : "");
                                $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);

                                if ($bDebug) {
                                        echo "MSG_543443 - rs_transacoes_row['iforma']: '" . $rs_transacoes_row['iforma'] . "' -> '" . $vg_pagto_tipo . "', s_sync = '" . $s_sync . "' (8765)" . PHP_EOL;
                                }
                                // at� aqui nova fun��o getSondaBanco()

                                // Se (!$s_sync), ou seja (status=1 & sonda) => completa a venda POR SONDA
                                if ($s_sync == "NO SYNC" || $webhook == true) {

                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 5a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }

                                        //Inicia transacao
                                        if ($msg == "") {
                                                $sql = "BEGIN TRANSACTION ";
                                                $ret = SQLexecuteQuery($sql);
                                                $ret = true;                                                                                                /////////////////////
                                                if (!$ret)
                                                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
                                        }

                                        // Marca registro como processado (status_processed=1), e status=3, j� que se chegou aqui quer dizer que n�o passou por confirmaBanco.php
                                        $sql = "update tb_pag_compras set status_processed=1, datacompra=CURRENT_TIMESTAMP, dataconfirma=" . $dataconfirma . ", status=3 where numcompra=$1 ";
                                        echo PHP_EOL . " NO SYNC => " . $sql . " " . PHP_EOL . "($msg)" . PHP_EOL;
                                        echo "DEBUG F (atualiza status_processed=1, vendaid = " . $rs_transacoes_row['idvenda'] . "): " . $sql . PHP_EOL;

                                        $rs_update2 = SQLexecuteQueryParams($sql, [$rs_transacoes_row['numcompra']]);
                                        if (!$rs_update2) {
                                                $msg = "Erro atualizando status de registro (62aa)." . $cReturn . "$sql" . $cReturn;
                                                echo $msg;
                                        }
                                        if (!$msg) {

                                                // Atualiza dados para tabela vendas
                                                //'DADOS_PAGTO_RECEBIDO' => 2
                                                $sql_update = "update tb_venda_games set 
                                                                                vg_pagto_valor_pago		= $1,
                                                                                vg_pagto_tipo			= $2,
                                                                                vg_pagto_num_docto		= $3, 
                                                                                vg_pagto_data_inclusao	= $4,
                                                                                vg_usuario_obs			= $5,
                                                                                vg_ultimo_status		= " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "
                                                                        where vg_id=$6;";
                                                $rs_update_params = [
                                                        ($rs_transacoes_row['total'] / 100 + $rs_transacoes_row['taxas'] + $rs_transacoes_row['frete'] + $rs_transacoes_row['manuseio']),
                                                        $vg_pagto_tipo,
                                                        $prefix_1 . $rs_transacoes_row['iforma'] . "_" . $rs_transacoes_row['numcompra'],
                                                        $rs_transacoes_row['datainicio'],
                                                        $legenda,
                                                        $rs_transacoes_row['idvenda']
                                                ];
                                                $rs_update = SQLexecuteQueryParams($sql_update, $rs_update_params);
                                                $sout = $rs_transacoes_row['datainicio'] . PHP_EOL . "   " . $rs_transacoes_row['numcompra'] . PHP_EOL . "   tipo: (" . $rs_transacoes_row['iforma'] . ") " . getDescricaoPagtoOnline($rs_transacoes_row['iforma']) . "," . PHP_EOL . "   idvenda: " . $rs_transacoes_row['idvenda'] . "." . PHP_EOL;

                                                if (!$rs_update) {
                                                        $msg = "Erro atualizando registro POR SONDA (61a)." . $sout . $cReturn;
                                                        echo $msg;
                                                } else {
                                                        echo "Pagamento atualizado POR SONDA com sucesso (4322)." . $sout . $cReturn;
                                                        writeLogVendaPixSafe($fileLog, "PAGAMENTO ATUALIZADO E STATUS VENDA ATUALIZADO PARA '2' / ID " . $rs_transacoes_row['idvenda'] . " \n");

                                                }
                                        }
                                        //Finaliza transacao
                                        if ($msg == "") {
                                                $sql = "COMMIT TRANSACTION ";
                                                $ret = SQLexecuteQuery($sql);
                                                if (!$ret)
                                                        $msg = "Erro ao comitar transa��o." . PHP_EOL;

                                                $msg_sonda = "PROCESSADO POR SONDA";
                                        } else {
                                                $sql = "ROLLBACK TRANSACTION ";
                                                $ret = SQLexecuteQuery($sql);
                                                if (!$ret)
                                                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;

                                                $msg_sonda = "PROCESSAMENTO POR SONDA FALHOU (ROLLBACK TRANSACTION)";
                                        }

                                        echo $msg_sonda . ": Sonda='$s_sonda' forma:" . $rs_transacoes_row['iforma'] . ", numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . $rs_transacoes_row['idvenda'] . "- " . $rs_transacoes_row['datainicio'] . " - R\$" . number_format(($rs_transacoes_row['total'] / 100 - $rs_transacoes_row['taxas']), 2, '.', '.') . " (SYNC)" . $cReturn;

                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (Prev 5b): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                } else {
                                        $total_pagto = $rs_transacoes_row['total'] / 100 - $rs_transacoes_row['taxas'];
                                        $total_pagtos_pendente += $total_pagto;
                                        $leading_zeros = (($total_pagto < 1000) ? (($total_pagto < 100) ? "00" : "0") : "");
                                        echo "N�o Processado por sonda: forma:" . $rs_transacoes_row['iforma'] . ", numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . str_pad($rs_transacoes_row['idvenda'], 8, '0', STR_PAD_LEFT) . " - " . $rs_transacoes_row['datainicio'] . " - R\$" . $leading_zeros . number_format(($total_pagto), 2, '.', '.') . " (NO SYNC) [" . number_format(getmicrotime() - $time_start_stats0_in, 2, '.', '.') . " s] [" . number_format(getmicrotime() - $time_start_stats0, 2, '.', '.') . " s]" . $cReturn;
                                }
                        } // 
                        else {
                                echo "N�o processado: idvenda=0." . $cReturn . "numcompra: " . $rs_transacoes_row['numcompra'] . "- " . $rs_transacoes_row['datainicio'] . $cReturn;
                        }
                } // End while loop 

        } // End if(rs)
        echo "Tempo m�dio de processamento: " . number_format((getmicrotime() - $time_start_stats0) / (($irows > 0) ? $irows : 1), 2, '.', '.') . " s/processamento (WSA)" . $cReturn;
        echo "Total pagamentos pendentes: R\$" . number_format($total_pagtos_pendente, 2, '.', '.') . " em $npags pagamentos (WSATP)" . $cReturn;

        $smonitor = "Tempo m�dio de processamento (" . date("Y-m-d H:i:s") . "): " . number_format((getmicrotime() - $time_start_stats0) / (($irows > 0) ? $irows : 1), 2, '.', '.') . " s/processamento<br>$irows pagamento" . (($irows > 1) ? "s" : "") . " em aberto<br>";

        // ===================================================

        //header
        $header = PHP_EOL . "------------------------------------------------------------------------" . PHP_EOL;
        $header .= "Conciliacao Automatica de Pagamento PIX" . PHP_EOL;
        $header .= date('d/m/Y - H:i:s') . PHP_EOL . PHP_EOL;
        $msg = "";
        $bDebug = false;
        if ($bDebug) {
                echo $header . PHP_EOL;
        }
        //	Recupera as vendas pendentes com pagamento online completo 
        if ($msg == "") {
                // 'DADOS_PAGTO_RECEBIDO' 		=> '2',
                // 'PAGTO_CONFIRMADO' 			=> '3',
                $sql = "
                        select * from tb_venda_games vg 
                        where ((vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . " or 
                                        vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . ")
                        )  and vg_data_inclusao > (now() -'2 months'::interval)
                        and vg_pagto_tipo=" . $PAGAMENTO_PIX_NUMERIC . " 
                        order by vg_data_inclusao desc
                        ";

                //	Vendas de integra��o s�o levantadas aqui para executar o notify
                //	n�o precisssa de: "and vg_integracao_parceiro_origem_id is null"

                $time_start_stats = getmicrotime();

                if ($bDebug) {
                        echo "DEBUG_ABCD: " . $sql . PHP_EOL;
                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0000): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                }
                $rs_venda = SQLexecuteQuery($sql);
                if ($bDebug) {
                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (00): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                }

                if (!$rs_venda || pgNumRowsSafe($rs_venda) == 0)
                        $msg = "Nenhuma venda encontrada." . PHP_EOL;
        }

        //Busca vendas
        if ($msg == "") {
                while ($rs_venda_row = pgFetchArraySafe($rs_venda)) {
                        $vg_id = $rs_venda_row['vg_id'];
                        $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
                        $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];
                        $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
                        $vg_ultimo_status_obs = $rs_venda_row['vg_ultimo_status_obs'];
                        $vg_ug_id = $rs_venda_row['vg_ug_id'];
                        $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                        $vg_integracao_parceiro_origem_id = $rs_venda_row['vg_integracao_parceiro_origem_id'];
                        if ($bDebug) {
                                echo "DEBUG - YTRE: vg_pagto_tipo: '$vg_pagto_tipo', vg_ultimo_status: $vg_ultimo_status" . PHP_EOL;
                        }
                        grava_log_integracao_tmp(PHP_EOL . PHP_EOL . str_repeat("*", 80) . PHP_EOL . "Integra��o Debug B_bko: " . date("Y-m-d H:i:s") . PHP_EOL . "  vg_id: $vg_id, vg_pagto_banco: $vg_pagto_banco, vg_ultimo_status: $vg_ultimo_status, vg_pagto_tipo: $vg_pagto_tipo, vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id'" . PHP_EOL . "  " . (($vg_integracao_parceiro_origem_id != "") ? "Integra��o - parceiro " . $vg_integracao_parceiro_origem_id : "N�o � integra��o (1)") . PHP_EOL);

                        //obtem o valor total da venda
                        //----------------------------------------------------
                        $total_geral = 0;
                        $sql = "select * from tb_venda_games vg " .
                                "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                                "where vg.vg_id = $1";
                        if ($bDebug) {
                                echo "DEBUG (A1): " . $sql . PHP_EOL;
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0-): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }

                        $rs_venda_modelos = SQLexecuteQueryParams($sql, [$vg_id]);
                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0+): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                echo "get_vg_ultimo_status($vg_id) 000: " . get_vg_ultimo_status($vg_id) . PHP_EOL;
                        }
                        if ($rs_venda_modelos && pgNumRowsSafe($rs_venda_modelos) > 0) {
                                while ($rs_venda_modelos_row = pgFetchArraySafe($rs_venda_modelos)) {
                                        $qtde = $rs_venda_modelos_row['vgm_qtde'];
                                        $valor = $rs_venda_modelos_row['vgm_valor'];
                                        $total_geral += $valor * $qtde;
                                        if ($vg_integracao_parceiro_origem_id) {
                                                // Para integra��o salva o ID de produto (sempre � um modelo por venda)
                                                $vgm_ogp_id = $rs_venda_modelos_row['vgm_ogp_id'];
                                                echo "  TESTA PRODUTO EM INTEGRA��O PAG >> ['" . $rs_venda_modelos_row['vg_integracao_parceiro_origem_id'] . "'] ->  [vg_id: '" . $rs_venda_modelos_row['vg_id'] . "'; vgm_ogp_id: '$vgm_ogp_id']- qtde: '$qtde', valor: '$valor' " . $cReturn;
                                        }
                                }
                        }
                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }
                        $rs_pag = SQLexecuteQueryParams($sql, [$vg_id]);
                        if ($bDebug) {
                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (0a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                        }
                        if ($rs_pag && pgNumRowsSafe($rs_pag) > 0) {
                                $rs_pag_row = pgFetchArraySafe($rs_pag);
                                // Remove o prefixo "PIX"
                                $pag_codigo = substr($rs_pag_row['vg_pagto_num_docto'], 5);
                                $vg_integracao_parceiro_origem_id = $rs_pag_row['vg_integracao_parceiro_origem_id'];

                                $ip_id = (($vg_integracao_parceiro_origem_id) ? getIntegracaoPedidoID_By_Venda($vg_integracao_parceiro_origem_id, $vg_id) : 0);

                                $msg .= PHP_EOL . "Venda: " . $vg_id . ", Pagamento: " . $pag_codigo . " (" . $rs_pag_row['vg_pagto_num_docto'] . ")" . PHP_EOL;
                                if ($bDebug) {
                                        echo PHP_EOL . "Venda: " . $vg_id . ", Pagamento: " . $pag_codigo . PHP_EOL;
                                }

                                echo "  LOGP>> vg_id: $vg_id, pag_codigo: '$pag_codigo', ip_id: $ip_id, vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id'" . $cReturn;


                                // Processa pagamentos PIX sem integra��o (Usu�rios Money com pagamento online)
                                if (trim($vg_integracao_parceiro_origem_id) == '') {
                                        // Prepara concilia��o
                                        $parametros['ultimo_status_obs'] = "Concilia��o autom�tica pagamento online em " . date('d/m/Y - H:i:s') . PHP_EOL;
                                        if (trim($vg_ultimo_status_obs) != "") {
                                                $vg_ultimo_status_obs = substr($vg_ultimo_status_obs, -1023);
                                                $ultimo_status_obs_sem_trim = $vg_ultimo_status_obs . PHP_EOL . $parametros['ultimo_status_obs'];
                                                $parametros['ultimo_status_obs'] = substr($ultimo_status_obs_sem_trim, -1023);
                                        }
                                        $parametros['PROCESS_AUTOM'] = '1';
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (A): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //Concilia
                                        $msgConcilia = "";

                                        if ($msgConcilia == "") {
                                                writeLogVendaPixSafe($fileLog, "ENTRANDO NA FUN��O PAGAMENTO ONLINE / ID " . $vg_id . " \n");

                                                $msgConcilia = conciliaVendaGames_PagamentoOnline($vg_id, $pag_codigo, 1, $parametros, $webhook);
                                                if ($msgConcilia == "")
                                                        $msg .= "Conciliacao(C1): Conciliado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Conciliacao: " . $msgConcilia;
                                        }
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (B, vg_pagto_tipo: '$vg_pagto_tipo', PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC: " . $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC'] . "): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //Associa pins, gera venda e credita saldo
                                        if ($msgConcilia == "") {
                                                writeLogVendaPixSafe($fileLog, "ENTRANDO NA FUN��O PROCESSA VENDA / ID " . $vg_id . " \n");

                                                $msgConcilia = processaVendaGames($vg_id, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Processamento (Pagtos PIX): Processado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Processamento (4): " . $msgConcilia;
                                        }
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (C1): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //envia email para o cliente de pagamento online
                                        if ($msgConcilia == "") {
                                                writeLogVendaPixSafe($fileLog, "ENTRANDO NA FUN��O PROCESSA VENDA EMAIL / ID " . $vg_id . " \n");

                                                $msgConcilia = processaEmailVendaGames($vg_id, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Envio de email: Enviado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Envio de email: " . $msgConcilia;
                                        }

                                        if ($msgConcilia == "") {
                                                $smonitor .= "Venda processada: <a href='/gamer/vendas/com_venda_detalhe.php?venda_id=$vg_id' target='_blank'>$vg_id</a> (R$" . number_format($total_geral, 2, '.', '.') . ")<br>";
                                        }

                                        closeLogVendaPixSafe($fileLog);
                                } else {
                                        // ===========================================================	
                                        // Processa vendas de usu�rios integra��o com pagamento PIX

                                        grava_log_integracao_tmp("Integra��o Debug 4_bko Pagto PIX: " . date("Y-m-d H:i:s") . PHP_EOL . "      vg_integracao_parceiro_origem_id: '$vg_integracao_parceiro_origem_id', vg_id: $vg_id" . PHP_EOL);

                                        // Prepara concilia��o
                                        $parametros['ultimo_status_obs'] = "Concilia��o autom�tica pagamento online em " . date('d/m/Y - H:i:s') . PHP_EOL;
                                        if (trim($vg_ultimo_status_obs) != "") {
                                                $vg_ultimo_status_obs = substr($vg_ultimo_status_obs, -1023);
                                                $ultimo_status_obs_sem_trim = $vg_ultimo_status_obs . PHP_EOL . $parametros['ultimo_status_obs'];
                                                $parametros['ultimo_status_obs'] = substr($ultimo_status_obs_sem_trim, -1023);
                                        }
                                        $parametros['PROCESS_AUTOM'] = '1';
                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (A): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        //Concilia
                                        $msgConcilia = "";
                                        if ($msgConcilia == "") {
                                                $msgConcilia = conciliaVendaGames_Integracao($vg_id, $pag_codigo, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Conciliacao(C_I): Conciliado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Conciliacao: " . $msgConcilia;
                                        }

                                        if ($msgConcilia == "") {
                                                $parametros['vg_integracao_parceiro_origem_id'] = $vg_integracao_parceiro_origem_id;
                                                $parametros['ultimo_status_obs'] = "Processa integra��o em notify Pagtos PIX (" . date("Y-m-d H:i:s") . ") Parceiro: $vg_integracao_parceiro_origem_id, ip_id: $ip_id, vg_id: $vg_id";
                                                $msgConcilia = processaVendaGamesIntegracao($vg_id, 1, $parametros);
                                                if ($msgConcilia == "")
                                                        $msg .= "Processamento Integra��o (Pagtos PIX): Processado com sucesso." . PHP_EOL;
                                                else
                                                        $msg .= "Processamento (5): " . $msgConcilia;
                                        }

                                        if ($bDebug) {
                                                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (C2): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                        }
                                        if ($msgConcilia == "") {
                                                //Usuario backoffice
                                                $iduser_bko = ((isset($GLOBALS['_SESSION']['iduser_bko'])) ? $GLOBALS['_SESSION']['iduser_bko'] : 0);
                                                if ($parametros['PROCESS_AUTOM'] == '1')
                                                        $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

                                                $sql = "update tb_venda_games set 
                                                        vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = $1," . PHP_EOL . "
                                                        vg_ultimo_status_obs = $2," . PHP_EOL . "
                                                        vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'], "") . PHP_EOL . "
                                                        where vg_id = $3";
                                                $ret = SQLexecuteQueryParams($sql, [$iduser_bko, $parametros['ultimo_status_obs'], $vg_id]);
                                                if (!$ret)
                                                        $msg = "Erro ao atualizar venda." . PHP_EOL;
                                        }
                                        if ($msgConcilia == "") {
                                                $url_notify_url = getPartner_param_By_ID('notify_url', $vg_integracao_parceiro_origem_id);
                                                $partner_do_notify = getPartner_param_By_ID('partner_do_notify', $vg_integracao_parceiro_origem_id);
                                                $s_msg = str_repeat("*", 80) . PHP_EOL . (($partner_do_notify == 1) ? "VAI FAZER NOTIFY" : "Sem notify") . PHP_EOL;
                                                $s_msg .= "    vg_integracao_parceiro_origem_id: $vg_integracao_parceiro_origem_id" . PHP_EOL . "    partner_do_notify: $partner_do_notify" . PHP_EOL . "    url_notify_url: '$url_notify_url'" . PHP_EOL;
                                                grava_log_integracao_tmp(str_repeat("*", 80) . PHP_EOL . "Vai processar integra��o:" . PHP_EOL . $s_msg);
                                                if ($partner_do_notify == 1 && ($url_notify_url != "")) {

                                                        // Monta o passo 4 da Integra��o - Notify partner
                                                        $sql = "SELECT * FROM tb_integracao_pedido ip 
                                                        WHERE 1=1
                                                        and ip_store_id = $1
                                                        and ip_vg_id = $2";
                                                        grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . "Select  registro de integra��o para o notify (A2)" . PHP_EOL . $sql . PHP_EOL);

                                                        $rs = SQLexecuteQueryParams($sql, [$vg_integracao_parceiro_origem_id, $vg_id]);
                                                        if (!$rs) {
                                                                $msg_1 = date("Y-m-d H:i:s") . " - Erro ao recuperar transa��o de integra��o (store_id: '" . $vg_integracao_parceiro_origem_id . "', vg_id: $vg_id)." . PHP_EOL;
                                                                echo $msg_1;
                                                                grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . $msg_1);
	                                                        } else {
	                                                                $rs_row = pgFetchArraySafe($rs);

	                                                                if (!$rs_row) {
	                                                                        $msg_1 = date("Y-m-d H:i:s") . " - Transacao de integracao nao encontrada para notify (store_id: '" . $vg_integracao_parceiro_origem_id . "', vg_id: $vg_id)." . PHP_EOL;
	                                                                        echo $msg_1;
	                                                                        grava_log_integracao_tmp(str_repeat("-", 80) . PHP_EOL . $msg_1);
	                                                                } else {
	                                                                $post_parameters = "store_id=" . $rs_row["ip_store_id"] . "&";

                                                                $post_parameters .= "transaction_id=" . $rs_row["ip_transaction_id"] . "&";
                                                                $post_parameters .= "order_id=" . $rs_row["ip_order_id"] . "&";
                                                                $post_parameters .= "amount=" . $rs_row["ip_amount"] . "&";
                                                                if (strlen($rs_row["ip_product_id"]) > 0) {
                                                                        $post_parameters .= "product_id=" . $rs_row["ip_product_id"] . "&";
                                                                }
                                                                $post_parameters .= "client_email=" . $rs_row["ip_client_email"] . "&";
                                                                $post_parameters .= "client_id=" . $rs_row["ip_client_id"] . "&";

                                                                $post_parameters .= "currency_code=" . $rs_row["ip_currency_code"];

                                                                $sret1 = getIntegracaoCURL($url_notify_url, $post_parameters);
                                                                $sret = $sret1;

	                                                                $s_msg = "AFTER Partner Notify - Conciliacao Automatica de Pagamento PIX (" . date("Y-m-d H:i:s") . ")" . PHP_EOL . " - result: " . PHP_EOL . str_repeat("_", 80) . PHP_EOL . $sret . PHP_EOL . str_repeat("-", 80) . PHP_EOL;
	                                                                grava_log_integracao_tmp(str_repeat("*", 80) . PHP_EOL . "Retorno de getIntegracaoCURL (2): " . PHP_EOL . print_r($post_parameters, true) . PHP_EOL . $s_msg . PHP_EOL);
	                                                                }
	                                                        }
                                                }
                                        }
                                        if ($msgConcilia == "") {
                                                $smonitor .= "Venda processada: ('$vg_integracao_parceiro_origem_id') <a href='/gamer/vendas/com_venda_detalhe.php?venda_id=$vg_id' target='_blank'>$vg_id</a> (R$" . number_format($total_geral, 2, '.', '.') . ")<br>";
                                        }
                                }
                                if ($bDebug) {
                                        echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (D): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
                                }
                        }        // Obtem vg_pagto_num_docto
                } // Para cada venda
        }

        // Em certos casos o status da venda fica em vg_ultimo_status=3 (PAGTO_CONFIRMADO) com o pagamento completo e os PINs entregues
        //	-> tem que passar para vg_ultimo_status=5 (VENDA_REALIZADA)
        $sql = "select vg.vg_id, pag.status, vg.vg_ultimo_status  
                                from tb_venda_games vg 
                                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                        inner join tb_pag_compras pag on pag.idvenda = vg.vg_id 
                                where (vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . "	
                                                and vg_pagto_tipo=" . $PAGAMENTO_PIX_NUMERIC . "
                                                ) 
                                        and vg_pagto_data_inclusao > '2021-01-01' and (not vgm_pin_codinterno='') 
                                        and vg_integracao_parceiro_origem_id is null 
                                order by vg_data_inclusao desc";

        if ($bDebug) {
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME (1abc): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        $rs_venda_pendentes = SQLexecuteQuery($sql);
        if ($rs_venda_pendentes && pgNumRowsSafe($rs_venda_pendentes) > 0) {
                while ($rs_venda_pendentes_row = pgFetchArraySafe($rs_venda_pendentes)) {
                        $vg_id_pendente = $rs_venda_pendentes_row['vg_id'];
                        $pag_status = $rs_venda_pendentes_row['status'];
                        $vg_ultimo_status = $rs_venda_pendentes_row['vg_ultimo_status'];

                        if ($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']) {        // 3 and "not vgm_pin_codinterno=''"	(from query) 
                                $sql = "update tb_venda_games
                                                set vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . " 
                                                where vg_id = $1";
                                echo "==>> Atualiza status de venda de '" . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . "' para '" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "' (vg_id pendente = " . $vg_id_pendente . ") " . PHP_EOL;

                                $ret = SQLexecuteQueryParams($sql, [$vg_id_pendente]);
                                if (!$ret)
                                        $msg = "Erro ao atualizar venda com status pendente (pagamento PIX)" . PHP_EOL . "$sql." . PHP_EOL;
                                else {
                                        echo "Venda status pendente vg_id:$vg_id_pendente, status ajustado de PAGTO_CONFIRMADO -> VENDA_REALIZADA (" . date('d/m/Y - H:i:s') . ")" . PHP_EOL;
                                }
                        } else {
                                echo "Venda status pendente vg_id:$vg_id_pendente, status do pagamento != " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . ", nada feito (" . date('d/m/Y - H:i:s') . ")" . PHP_EOL;
                        }
                }
        }

        $msg = $header . $msg . "------------------------------------------------------------------------" . PHP_EOL;
        if ($bDebug) {
                echo $cReturn . $cReturn . $cReturn . "ELAPSED TOTAL TIME TOTAL: " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . $cReturn;
        }

        if (!isset($codigoNumerico))
                $codigoNumerico = null;

        gravaLog_MonitorGamer($smonitor, $codigoNumerico);

        return $msg;
} //end function conciliacaoAutomaticaPagtoPIXemGAMER


function conciliaMoneyDepositoSaldo_boleto($bol_codigo, $venda_id, $usuario_id, $parametros)
{
        global $cReturn, $cSpaces, $sFontRedOpen, $sFontRedClose, $bHTML;

        $bDebug = (!$bHTML);

        //Validacoes
        $msg = "";

        //Valida boleto id
        if (!$bol_codigo)
                $msg = "C�digo do boleto n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($bol_codigo))
                $msg = "C�digo do boleto inv�lido." . PHP_EOL;

        //Valida usuario_id
        if (!$usuario_id)
                $msg = "C�digo do usu�rio Money n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($usuario_id))
                $msg = "C�digo do usu�rio Money inv�lido." . PHP_EOL;

        //Recupera o boleto pendente
        if ($msg == "") {
                $sql = "select * from boletos_pendentes bol
                                where bol.bol_codigo = " . $bol_codigo;
                if ($bDebug)
                        echo "sqlA1: $sql" . PHP_EOL;
                $rs_boleto = SQLexecuteQuery($sql);
                if (!$rs_boleto || pgNumRowsSafe($rs_boleto) == 0)
                        $msg = "Nenhum boleto encontrado." . PHP_EOL;
                else {
                        $rs_boleto_row = pgFetchArraySafe($rs_boleto);
                        $bol_data = $rs_boleto_row['bol_data'];
                        $bol_valor = $rs_boleto_row['bol_valor'];
                        $bol_banco = $rs_boleto_row['bol_banco'];
                        $bol_documento = $rs_boleto_row['bol_documento'];
                        if ($bDebug)
                                echo "bol_data: '$bol_data', bol_valor: $bol_valor, bol_banco: $bol_banco, bol_documento: '$bol_documento', " . PHP_EOL;
                }
        }

        //Recupera o saldo do usu�rio
        if ($msg == "") {
                $sql = "select ug_perfil_saldo from usuarios_games where ug_id =" . $usuario_id;
                if ($bDebug)
                        echo "sqlA1a: $sql" . PHP_EOL;
                $rs_saldo = SQLexecuteQuery($sql);
                if (!$rs_saldo || pgNumRowsSafe($rs_saldo) == 0)
                        $msg = "Nenhum usu�rio encontrado." . PHP_EOL;
                else {
                        $rs_saldo_row = pgFetchArraySafe($rs_saldo);
                        $ug_perfil_saldo_prev = $rs_saldo_row['ug_perfil_saldo'];
                        if ($bDebug)
                                echo " ++++B [" . date("Y-m-d H:i:s") . "] para ug_id:$usuario_id ug_perfil_saldo_prev = '$ug_perfil_saldo_prev', valor = " . $parametros['valor'] . PHP_EOL;
                }
        }

        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
        }

        //Concilia boleto
        if ($msg == "") {
                $sql = "update boletos_pendentes set bol_aprovado = 1, bol_aprovado_data = CURRENT_TIMESTAMP, bol_venda_games_id = " . $venda_id . " where bol_codigo = " . $bol_codigo;
                if ($bDebug)
                        echo "sqlA2: $sql" . PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar boleto." . PHP_EOL;
        }

        //Credita valor do boleto no usu�rio Money
        if ($msg == "") {
                $sql = "update usuarios_games set ug_perfil_saldo = coalesce(ug_perfil_saldo,0) + " . $parametros['valor'] . " where ug_id =" . $usuario_id;
                if ($bDebug)
                        echo "sqlA3: $sql" . PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao creditar valor do boleto no usu�rio Money." . PHP_EOL;
        }

        //Completa venda Money
        if ($msg == "") {
                $sql = "update tb_venda_games set vg_ultimo_status =  " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . ", vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP where vg_id =" . $venda_id . " and vg_ug_id =" . $usuario_id . ";";
                if ($bDebug)
                        echo "sqlA4: $sql" . PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao completar venda Money (Boleto)." . PHP_EOL;
        }

        //Insere registro em saldo_fifo
        if ($msg == "") {
                $sql = "insert into saldo_composicao_fifo (ug_id,scf_data_deposito,scf_valor,scf_valor_disponivel,scf_canal,scf_comissao,scf_id_pagamento, vg_id) values (" . $usuario_id . ",CURRENT_TIMESTAMP," . $parametros['valor'] . "," . $parametros['valor'] . ",'G',0,'" . $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'] . "', " . (($venda_id) ? $venda_id : 0) . ")";
                if ($bDebug)
                        echo "sqlA5: $sql" . PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao inserir Saldo FIFO (BOleto)." . PHP_EOL;
        }

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transa��o." . PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;
        }

        //Recupera o saldo do usu�rio no final
        if ($msg == "") {
                $sql = "select ug_perfil_saldo from usuarios_games where ug_id =" . $usuario_id;
                if ($bDebug)
                        echo "sqlA1b: $sql" . PHP_EOL;
                $rs_saldo = SQLexecuteQuery($sql);
                if (!$rs_saldo || pgNumRowsSafe($rs_saldo) == 0)
                        $msg = "Nenhum usu�rio encontrado." . PHP_EOL;
                else {
                        $rs_saldo_row = pgFetchArraySafe($rs_saldo);
                        $ug_perfil_saldo_prev = $rs_saldo_row['ug_perfil_saldo'];
                        if ($bDebug)
                                echo " ++++B2 [" . date("Y-m-d H:i:s") . "] para ug_id:$usuario_id ug_perfil_saldo_prev = '$ug_perfil_saldo_prev', valor = " . $parametros['valor'] . PHP_EOL;
                }
        }

        return $msg;
}


function conciliaAutomaticaMoneyDepositoSaldo()
{
        global $cReturn, $cSpaces, $sFontRedOpen, $sFontRedClose;

        $bDebug = false;
        $time_start_stats = getmicrotime();

        echo str_repeat("=", 80) . PHP_EOL . "Entrando em conciliaAutomaticaMoneyDepositoSaldo() - consulta Sondas " . date("Y-M-d H:i:s") . PHP_EOL;
        $bank_sonda = new bank_sonda();
        if (isset($bank_sonda)) {
                $bank_sonda->load_banks_sonda_array();
        }

        $nminutes = 1440;        // Por agora 1 dia, depois apenas 90 min
        echo PHP_EOL . "========================================================================" . PHP_EOL;
        echo "Prepara concilia��o de pagamentos online para dep�sito em Saldo (registros com idvenda>0 e n�o processados nos �ltimos " . $nminutes . " minutos, desde " . date('Y-m-d H:i:s', strtotime("-" . $nminutes . " minutes")) . ")" . PHP_EOL;
        // Prepara concilia��o de pagamentos online
        //		$date_ini = date('Y-m-d', strtotime("-5 days"));	//"2009-01-01"; //date("Y-m-d");
        // echo "-90 minutes: ".date('Y-m-d H:i:s', strtotime("-90 minutes"))."<br>";
        $date_ini = date('Y-m-d H:i:s', strtotime("-" . $nminutes . " minutes"));
        $date_end = date("Y-m-d H:i:s");

        // Quando o pagamento retorna por sonda (e n�o diretamente do banco) o status_processed=0 mas ainda vg.vg_ultimo_status=3
        // Os dois casos devem ser conciliados
        //		$sql = "select * from tb_pag_compras pgt inner join tb_venda_games vg on vg.vg_id = pgt.idvenda ";
        //		$sql .= "where idvenda>0 and (status_processed=0 or vg.vg_ultimo_status=3) and tipo_cliente='M' ";

        // O anterior est� demorando muito e n�o � necess�rio consultar tb_venda_games, apenas tb_pag_compras
        $sql = "select * from tb_pag_compras pgt where idvenda>0 and status_processed=0 and tipo_cliente='M' and tipo_deposito = 2 and datainicio > (now() -'2 months'::interval) and iforma!='6' and iforma!='" . $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX'] . "' ";

        // status=1 and 
        // Apenas para vendas que n�o s�o integra��o
        //		$sql .= "and vg_integracao_parceiro_origem_id is null ";

        // Op��o 1 - n�o precissa limitar por data - apenas os pagtos com status_processed=0 ser�o retornados, ap�s 90mins eles s�o cancelados.
        //	se houver um descancelamento de venda o pagto correspondente vai aparecer aqui

        // Op��o 1 - Para processar normalmente
        //		$sql .= " and (pgt.datainicio between '".$date_ini."' and '".$date_end."') ";	
        // Op��o 2 - Para incluir algum pagamento antigo descancelado
        //		$sql .= " and ((pgt.datainicio between '".$date_ini."' and '".$date_end."') or (pgt.datainicio between '2010-01-26 00:00:00' and '2010-01-26 23:59:59'))";	

        $rs_total = SQLexecuteQuery($sql);
        if ($bDebug) {
                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 0): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
        }

        if ($rs_total)
                $registros_total = pgNumRowsSafe($rs_total);
        $sql .= " order by pgt.datainicio desc ";

        if ($bDebug) {
                echo "DEBUG A1: " . $sql . $cReturn;
        }

        $time_start_stats0 = getmicrotime();
        $rs_transacoes = SQLexecuteQuery($sql);
        if ($bDebug) {
                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 1): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
        }
        echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "TIME FOR FIRST QUERY: " . number_format(getmicrotime() - $time_start_stats0, 2, '.', '.') . "" . $cReturn;        //."$sql".$cReturn;

        if (!$rs_transacoes || pgNumRowsSafe($rs_transacoes) == 0)
                $msg = "Nenhuma transa��o encontrada." . $cReturn;

        $time_start_stats0 = getmicrotime();
        $irows = 0;
        if ($rs_transacoes) {
                $npags = pgNumRowsSafe($rs_transacoes);
                echo "NPags: " . $npags . $cReturn;
                $total_pagtos_pendente = 0;
                while ($rs_transacoes_row = pgFetchArraySafe($rs_transacoes)) {
                        $time_start_stats0_in = getmicrotime();
                        $irows++;

                        $msgregister = $rs_transacoes_row['numcompra'] . " - " . $rs_transacoes_row['datainicio'] . " - " . $rs_transacoes_row['datacompra'] . " - " . $rs_transacoes_row['iforma'] . " - " . $rs_transacoes_row['idvenda'] . " - Proc: " . $rs_transacoes_row['status_processed'] . " - " . get_tipo_cliente_descricao($rs_transacoes_row['tipo_cliente']) . " -: R\$" . number_format($rs_transacoes_row['total'] / 100, 2, ',', '.') . " - '" . $rs_transacoes_row['cliente_nome'] . "'" . $cReturn;

                        if ($bDebug) {
                                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 2): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
                        }
                        $msg = "";
                        // Venda cadastrada
                        if ($rs_transacoes_row['idvenda'] > 0) {
                                // Pagamento concluido com sucesso -  status=3 em \prepag2\pag\*.php (arquivo de retorno do banco) 
                                if ($rs_transacoes_row['status'] == 3) {

                                        $prefix = getDocPrefix($rs_transacoes_row['iforma']);

                                        $iforma_tmp = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);

                                        // Atualiza dados para tabela vendas
                                        $sql_update = "update tb_venda_games set 
                                                                        vg_pagto_valor_pago		= " . ($rs_transacoes_row['total'] / 100) . ",
                                                                        vg_pagto_tipo			= " . $iforma_tmp . ",
                                                                        vg_pagto_num_docto		= '" . $prefix . $rs_transacoes_row['iforma'] . "_" . $rs_transacoes_row['numcompra'] . "', 
                                                                        vg_pagto_data_inclusao	= '" . $rs_transacoes_row['datainicio'] . "',
                                                                        vg_ultimo_status		= " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "
                                                                where vg_id=" . $rs_transacoes_row['idvenda'] . ";";

                                        $rs_update = SQLexecuteQuery($sql_update);
                                        if ($bDebug) {
                                                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 3): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
                                        }

                                        $sout = $rs_transacoes_row['datainicio'] . PHP_EOL . "   " . $rs_transacoes_row['numcompra'] . PHP_EOL . "   tipo: (" . $rs_transacoes_row['iforma'] . ") " . $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$rs_transacoes_row['iforma']] . "," . PHP_EOL . "   idvenda: " . $rs_transacoes_row['idvenda'] . "." . PHP_EOL;
                                        if (!$rs_update) {
                                                $msg = "Erro atualizando registro (61)." . $cReturn . "($sql_update)" . $cReturn . "" . $sout . $cReturn;
                                                echo $msg;
                                        } else {
                                                echo "Pagamento atualizado com sucesso (432345)." . $cReturn;
                                        }

                                        // Pagamento ainda n�o foi feito ou n�o tem confirma��o bancaria -  status=1 -> Sonda o banco, se estiver completo atualiza aqui
                                } else if ($rs_transacoes_row['status'] == 1) {

                                        if (isset($bank_sonda)) {
                                                if ($bank_sonda->is_bank_blocked($rs_transacoes_row['iforma'])) {
                                                        echo "Banco '" . $rs_transacoes_row['iforma'] . "' BLOQUEADO para Sonda (" . date("Y-m-d H:i:s") . ")" . PHP_EOL;
                                                } else {
                                                        echo "Banco '" . $rs_transacoes_row['iforma'] . "' LIBERADO para Sonda (" . date("Y-m-d H:i:s") . ")" . PHP_EOL;
                                                }
                                        }

                                        if ($bDebug) {
                                                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 4a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
                                                $time_start_stats_4a = getmicrotime();
                                        }


                                        // come�a aqui nova fun��o getSondaBanco()

                                        $dataconfirma = date("Y-m-d H:i:s");                // "CURRENT_TIMESTAMP";	// 
                                        $s_sonda = "????";
                                        //$valtotal = 0;
                                        unset($aline5);
                                        unset($aline6);
                                        unset($aline9);
                                        //unset($alineA);
                                        unset($alineC);
                                        $s_update_status_lr = "";

                                        if (isset($bank_sonda)) {
                                                $bank_sonda->set_last_numcompra($rs_transacoes_row['iforma'], $rs_transacoes_row['numcompra']);
                                                $bank_sonda->start_time_waiting_for_sonda($rs_transacoes_row['iforma']);
                                        }

                                        if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {

                                                // obtem status, OK se status='081'
                                                $b_sonda_5 = getTransacaoPagamentoOK("Transf", $rs_transacoes_row['numcompra'], $aline5);

                                                // Se existe registro da transa��o -> salva data 	
                                                if ((is_array($aline5)) && (count($aline5) > 0)) {
                                                        $s_sonda = (($b_sonda_5) ? "OK" : "none");
                                                        $sBanco = "Bradesco";

                                                        $dataconfirma = "'" . date('Y-m-d H:i:s') . "'";
                                                }
                                                echo ("Em concilia��o TPDeposito - Sonda de Pagto BRD5 (" . $rs_transacoes_row['numcompra'] . ")." . PHP_EOL . print_r($aline5, true) . PHP_EOL);
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {

                                                // obtem status, OK se status='003'
                                                $b_sonda_6 = getTransacaoPagamentoOK("PagtoFacil", $rs_transacoes_row['numcompra'], $aline6);

                                                // Se existe registro da transa��o -> salva data 	
                                                if ((isset($aline6[1], $aline6[3], $aline6[4])) && (strlen($aline6[1]) > 0)) {
                                                        $s_sonda = (($b_sonda_6) ? "OK" : "none");
                                                        $sBanco = "Bradesco";
                                                        /*	
                                                        Retorno da Sonda
                                                                                   0123456789
                                                                        [3] => 25/05/2012
                                                                        [4] => 00:16:59
                                                        */
                                                        $dataconfirma = "'" . substr($aline6[3], 6, 4) . "-" . substr($aline6[3], 3, 2) . "-" . substr($aline6[3], 0, 2) . " " . $aline6[4] . "'";
                                                }
                                                echo ("Em concilia��o TPDeposito - Sonda de Pagto BRD6 (" . $rs_transacoes_row['numcompra'] . ")." . PHP_EOL . print_r($aline6, true) . PHP_EOL);
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {

                                                // obtem status, OK se status='003'
                                                $b_sonda_9 = getTransacaoPagamentoOK("BancodoBrasil", $rs_transacoes_row['numcompra'], $aline9);
                                                if ($b_sonda_9) {
                                                        $s_sonda = (($b_sonda_9) ? "OK" : "none");
                                                        $sBanco = "Banco do Brasil";
                                                        //     [dataPagamento] => 16092009
                                                        $dataPagamento = $aline9['dataPagamento'] ?? "";
                                                        if ($dataPagamento !== "") {
                                                                $dataconfirma = "'" . substr($dataPagamento, 4, 4) . "-" . substr($dataPagamento, 2, 2) . "-" . substr($dataPagamento, 0, 2) . "'";
                                                        }
                                                }
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']) {
                                                $pedido = str_pad((string)$rs_transacoes_row['id_transacao_itau'], 8, "0", STR_PAD_LEFT);

                                                $pag_status = getSondaItau($pedido, $a_retorno_itau, $sitPag, $dtPag);
                                                $b_sonda_A = false;
                                                $b_sonda_A = (($pag_status == "00") ? true : false);


                                                if ($b_sonda_A) {
                                                        $s_sonda = (($b_sonda_A) ? "OK" : "none");
                                                        $sBanco = "Banco Ita�";
                                                        //     [dtPag] => 16092009
                                                        $dataconfirma = "'" . substr($dtPag, 4, 4) . "-" . substr($dtPag, 2, 2) . "-" . substr($dtPag, 0, 2) . "'";
                                                }
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']) {
                                                $pag_status = getSondaPINsEPP($rs_transacoes_row['numcompra'], $dtPag);
                                                $b_sonda_E = ($pag_status == 3) ? true : false;

                                                if ($b_sonda_E) {
                                                        $s_sonda = (($b_sonda_E) ? "OK" : "none");
                                                        $sBanco = "PINs E-Prepag";
                                                        $dataconfirma = "'" . date("Y-m-d") . "'";
                                                }
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_HIPAY_ONLINE']) {
                                                $pag_status = "";        //getSondaHipay($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                $b_sonda_B = ($rs_transacoes_row['status'] == 3) ? true : false;


                                                if ($b_sonda_B) {
                                                        $s_sonda = (($b_sonda_B) ? "OK" : "none");
                                                        $sBanco = "Banco HiPay";
                                                        $dataconfirma = "'" . date("Y-m-d") . "'";
                                                }
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_PAYPAL_ONLINE']) {
                                                $pag_status = "";        //getSondaPayPal($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                $b_sonda_P = ($rs_transacoes_row['status'] == 3) ? true : false;


                                                if ($b_sonda_P) {
                                                        $s_sonda = (($b_sonda_P) ? "OK" : "none");
                                                        $sBanco = "Banco PayPal";
                                                        $dataconfirma = "'" . date("Y-m-d") . "'";
                                                }
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']) {
                                                $pag_status = "";        //getSondaItau($pedido, &$a_retorno_itau, $sitPag, $dtPag);
                                                $b_sonda_Z = ($rs_transacoes_row['status'] == 3) ? true : false;


                                                if ($b_sonda_Z) {
                                                        $s_sonda = (($b_sonda_Z) ? "OK" : "none");
                                                        $sBanco = "Banco E-Prepag";
                                                        $dataconfirma = "'" . date("Y-m-d") . "'";
                                                }
                                        } else if (b_IsPagtoCielo($rs_transacoes_row['iforma'])) {

                                                // obtem status, OK se status='6'
                                                $b_sonda_C = getTransacaoPagamentoOK("Cielo", $rs_transacoes_row['numcompra'], $alineC);

                                                // Se existe registro da transa��o -> salva data 	
                                                if (($alineC['status'] ?? '') == "6") {
                                                        $s_sonda = (($b_sonda_C) ? "OK" : "none");
                                                        $sBanco = "Banco Cielo";
                                                        $dataconfirma = "'" . substr($alineC['data'] ?? '', 0, 19) . "'";
                                                        $s_update_status_lr = ", cielo_status = '" . $alineC['status'] . "', cielo_codigo_lr = '" . ($alineC['codigo_lr'] ?? '') . "' ";
                                                }
                                        }


                                        if (isset($bank_sonda)) {
                                                $bank_sonda->stop_time_waiting_for_sonda($rs_transacoes_row['iforma']);
                                                $bank_sonda->block_bank_if_slow($rs_transacoes_row['iforma']);
                                        }

                                        $dataconfirma = str_replace("/", "-", $dataconfirma);

                                        if ($bDebug) {
                                                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 4b): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . " (waiting for Sonda: " . number_format(getmicrotime() - $time_start_stats_4a, 2, '.', '.') . ") " . $cReturn;
                                        }

                                        // Procura pagamentos em aberto no site do banco (Sonda), se (status=1 & sonda) => "NO SYNC"
                                        $s_sync = "";
                                        $prefix_1 = getDocPrefix($rs_transacoes_row['iforma']);
                                        $vg_pagto_tipo = $rs_transacoes_row['iforma'];

                                        if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
                                                $s_sync = (($b_sonda_5) ? "NO SYNC" : "");
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
                                                $s_sync = (($b_sonda_6) ? "NO SYNC" : "");
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
                                                $s_sync = (($b_sonda_9) ? "NO SYNC" : "");
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']) {
                                                $s_sync = (($b_sonda_A) ? "NO SYNC" : "");
                                                // No Itau ajusta 'A' -> 10 (usa numerico em tb_venda_games)
                                                $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']) {
                                                $s_sync = (($b_sonda_E) ? "NO SYNC" : "");
                                                // No Banco E-Prepag ajusta 'E' -> 998 (usa numerico em tb_venda_games)
                                                $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']) {
                                                $s_sync = (($b_sonda_Z) ? "NO SYNC" : "");
                                                // No Banco E-Prepag ajusta 'Z' -> 999 (usa numerico em tb_venda_games)
                                                $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_HIPAY_ONLINE_NUMERIC']) {
                                                $s_sync = (($b_sonda_B) ? "NO SYNC" : "");
                                                // No Banco HiPay ajusta 'B' -> 11 (usa numerico em tb_venda_games)
                                                $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                        } else if ($rs_transacoes_row['iforma'] == $GLOBALS['PAGAMENTO_PAYPAL_ONLINE_NUMERIC']) {
                                                $s_sync = (($b_sonda_P) ? "NO SYNC" : "");
                                                // No Banco Paypal ajusta 'P' -> 12 (usa numerico em tb_venda_games)
                                                $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                        } else if (b_IsPagtoCielo($rs_transacoes_row['iforma'])) {
                                                $s_sync = (($b_sonda_C) ? "NO SYNC" : "");
                                                $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);
                                        }
                                        if ($bDebug) {
                                                echo "MSG_543443 - rs_transacoes_row['iforma']: '" . $rs_transacoes_row['iforma'] . "' -> '" . $vg_pagto_tipo . "', s_sync = '" . $s_sync . "' (8765)" . PHP_EOL;
                                        }
                                        // at� aqui nova fun��o getSondaBanco()

                                        // Se (!$s_sync), ou seja (status=1 & sonda) => completa a venda POR SONDA
                                        if ($s_sync == "NO SYNC") {                        /////   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

                                                if ($bDebug) {
                                                        echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 5a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
                                                }

                                                //Inicia transacao
                                                if ($msg == "") {
                                                        $sql = "BEGIN TRANSACTION ";
                                                        $ret = SQLexecuteQuery($sql);
                                                        $ret = true;                                                                                                /////////////////////
                                                        if (!$ret)
                                                                $msg = "Erro ao iniciar transa��o." . PHP_EOL;
                                                }

                                                // Marca registro como processado (status_processed=1), e status=3, j� que se chegou aqui quer dizer que n�o passou por confirmaBanco.php
                                                // Retira status_processed=1 para poder encontrar o registro depois em sqlB2: 
                                                $sql = "update tb_pag_compras set datacompra=CURRENT_TIMESTAMP, dataconfirma=" . $dataconfirma . ", status=3 " . $s_update_status_lr . " where numcompra='" . $rs_transacoes_row['numcompra'] . "' ";
                                                echo PHP_EOL . " NO SYNC => " . $sql . " " . PHP_EOL . "($msg)" . PHP_EOL;
                                                echo "DEBUG F (atualiza status_processed=1, vendaid = " . $rs_transacoes_row['idvenda'] . "): " . $sql . PHP_EOL;

                                                $rs_update2 = SQLexecuteQuery($sql);
                                                if (!$rs_update2) {
                                                        $msg = "Erro atualizando status de registro (62aa)." . $cReturn . "$sql" . $cReturn;
                                                        echo $msg;
                                                }
                                                if (!$msg) {

                                                        // Atualiza dados para tabela vendas
                                                        //'DADOS_PAGTO_RECEBIDO' => 2
                                                        $sql_update = "update tb_venda_games set 
                                                                                        vg_pagto_valor_pago		= " . ($rs_transacoes_row['total'] / 100 + $rs_transacoes_row['taxas'] + $rs_transacoes_row['frete'] + $rs_transacoes_row['manuseio']) . ",
                                                                                        vg_pagto_tipo			= " . $vg_pagto_tipo . ",
                                                                                        vg_pagto_num_docto		= '" . $prefix_1 . $rs_transacoes_row['iforma'] . "_" . $rs_transacoes_row['numcompra'] . "', 
                                                                                        vg_pagto_data_inclusao	= '" . $rs_transacoes_row['datainicio'] . "',
                                                                                        vg_usuario_obs			= 'Pagamento Online " . $sBanco . " POR SONDA [" . $rs_transacoes_row['iforma'] . "] em " . date("Y-m-d H:i:s") . "',
                                                                                        vg_ultimo_status		= " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "
                                                                                where vg_id=" . $rs_transacoes_row['idvenda'] . ";";

                                                        $rs_update = SQLexecuteQuery($sql_update);
                                                        $sout = $rs_transacoes_row['datainicio'] . PHP_EOL . "   " . $rs_transacoes_row['numcompra'] . PHP_EOL . "   tipo: (" . $rs_transacoes_row['iforma'] . ") " . getDescricaoPagtoOnline($rs_transacoes_row['iforma']) . "," . PHP_EOL . "   idvenda: " . $rs_transacoes_row['idvenda'] . "." . PHP_EOL;

                                                        if (!$rs_update) {
                                                                $msg = "Erro atualizando registro POR SONDA (61a)." . $sout . $cReturn;
                                                                echo $msg;
                                                        } else {
                                                                echo "Pagamento atualizado POR SONDA com sucesso (4322)." . $sout . $cReturn;
                                                        }
                                                }

                                                //Finaliza transacao
                                                if ($msg == "") {
                                                        $sql = "COMMIT TRANSACTION ";
                                                        $ret = SQLexecuteQuery($sql);
                                                        if (!$ret)
                                                                $msg = "Erro ao comitar transa��o." . PHP_EOL;

                                                        $msg_sonda = "PROCESSADO POR SONDA";
                                                } else {
                                                        $sql = "ROLLBACK TRANSACTION ";
                                                        $ret = SQLexecuteQuery($sql);
                                                        if (!$ret)
                                                                $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;

                                                        $msg_sonda = "PROCESSAMENTO POR SONDA FALHOU (ROLLBACK TRANSACTION)";
                                                }

                                                echo $msg_sonda . ": Sonda='$s_sonda' forma:" . $rs_transacoes_row['iforma'] . ", numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . $rs_transacoes_row['idvenda'] . "- " . $rs_transacoes_row['datainicio'] . " - R\$" . number_format(($rs_transacoes_row['total'] / 100 - $rs_transacoes_row['taxas']), 2, '.', '.') . " (SYNC)" . $cReturn;

                                                if ($bDebug) {
                                                        echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 5b): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
                                                }
                                        } else {
                                                $total_pagto = $rs_transacoes_row['total'] / 100 - $rs_transacoes_row['taxas'];
                                                $total_pagtos_pendente += $total_pagto;
                                                $leading_zeros = (($total_pagto < 1000) ? (($total_pagto < 100) ? "00" : "0") : "");
                                                echo "N�o Processado por sonda: forma:" . $rs_transacoes_row['iforma'] . ", numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . str_pad($rs_transacoes_row['idvenda'], 8, '0', STR_PAD_LEFT) . " - " . $rs_transacoes_row['datainicio'] . " - R\$" . $leading_zeros . number_format(($total_pagto), 2, '.', '.') . " (NO SYNC) [" . number_format(getmicrotime() - $time_start_stats0_in, 2, '.', '.') . " s] [" . number_format(getmicrotime() - $time_start_stats0, 2, '.', '.') . " s]" . $cReturn;
                                        }
                                } else {
                                        echo "N�o processado: status!=3 e Sonda=false." . $cReturn . "numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . $rs_transacoes_row['idvenda'] . "- " . $rs_transacoes_row['datainicio'] . "" . $cReturn;
                                }
                        } // 
                        else {
                                echo "N�o processado: idvenda=0." . $cReturn . "numcompra: " . $rs_transacoes_row['numcompra'] . "- " . $rs_transacoes_row['datainicio'] . "" . $cReturn;
                        }
                } // End while loop 

                if (isset($bank_sonda)) {
                        echo str_repeat("-", 80) . PHP_EOL . "Lista bank_sonda[]" . PHP_EOL . $bank_sonda->list_registers(false) . PHP_EOL;
                        $aret = $bank_sonda->get_list_blocked_banks();

                        if (count($aret) > 0) {
                                $b_unblock_banks = true;
                                // Do an extra Sonda to monitor if Bank is online again
                                foreach ($aret as $key => $val) {
                                        echo "TEST SONDA in '$key' ('" . $val['time_waiting_for_sonda'] . "', '" . $val['last_numcompra'] . "')" . PHP_EOL;
                                        $bank_sonda->start_time_waiting_for_sonda($key);
                                        if ($b_unblock_banks) {
                                                switch ($key) {
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']:
                                                                $b_sonda_5s = getTransacaoPagamentoOK("Transf", $val['last_numcompra'], $aline5);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_FACIL_BRADESCO_DEBITO']:
                                                                $b_sonda_6s = getTransacaoPagamentoOK("PagtoFacil", $val['last_numcompra'], $aline6);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']:
                                                                $b_sonda_9s = getTransacaoPagamentoOK("BancodoBrasil", $val['last_numcompra'], $aline9);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']:
                                                                $sql = "select id_transacao_itau from tb_pag_compras where numcompra='" . $val['last_numcompra'] . "'";
                                                                $pedido = str_pad(getValueSingle($sql), 8, "0", STR_PAD_LEFT);
                                                                $pag_status_s = getSondaItau($pedido, $a_retorno_itau, $sitPag, $dtPag);
                                                                break;
                                                        case $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']:
                                                                $sql = "select status from tb_pag_compras where numcompra='" . $val['last_numcompra'] . "'";
                                                                $b_sonda_E_s = (getValueSingle($sql) == 3) ? true : false;
                                                                break;
                                                        case $GLOBALS['PAGAMENTO_HIPAY_ONLINE']:
                                                                break;
                                                        case $GLOBALS['PAGAMENTO_PAYPAL_ONLINE']:
                                                                break;
                                                        case $GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE']:
                                                                $sql = "select status from tb_pag_compras where numcompra='" . $val['last_numcompra'] . "'";
                                                                $b_sonda_Zs = (getValueSingle($sql) == 3) ? true : false;
                                                                break;
                                                        default:
                                                                if (b_IsPagtoCielo($key)) {
                                                                        $b_sonda_Cs = getTransacaoPagamentoOK("Cielo", $val['last_numcompra'], $alineC);
                                                                }
                                                }
                                        } else {
                                                echo "  ==  DUMMY - chamada a Sonda est� bloqueada1" . PHP_EOL;
                                        }
                                        $bank_sonda->stop_time_waiting_for_sonda($key);
                                        $bank_sonda->unblock_bank_if_normal($key);
                                }
                        } else {
                                echo "  ==  Sem chamada a Sonda - n�o tem Bancos bloqueados1" . PHP_EOL;
                        }
                }
        } // End if(rs)
        echo "Tempo m�dio de processamentoq: " . number_format((getmicrotime() - $time_start_stats0) / (($irows > 0) ? $irows : 1), 2, '.', '.') . " s/processamento (WSAa)" . $cReturn;
        echo "Total pagamentos pendentes: R\$" . number_format($total_pagtos_pendente, 2, '.', '.') . " em $npags pagamentos (WSATPa)" . $cReturn;


        echo str_repeat("=", 80) . PHP_EOL . "Entrando em conciliaAutomaticaMoneyDepositoSaldo() - vai conciliar " . date("Y-M-d H:i:s") . PHP_EOL;

        // Procura vendas para dep�sito em saldo de Gamer nem canceladas nem completas
        // No inner join com boletos_pendentes ->
        //		bol_documento pode ter um caracter extra no final que pode ser n�o numerico, ent�o testamos s� para tipo "6" que n�o tem esse problema
        $sql = "select * 
                        from tb_venda_games vg
                                left outer join boleto_bancario_games bbg on bbg.bbg_vg_id = vg.vg_id 
                                left outer join tb_pag_compras pg on pg.idvenda = vg.vg_id
                                left outer join boletos_pendentes bol on 
                                        (case  substr(bol_documento, 1, 1) 
                                                        when '6' 
                                                        then (case bol_banco 
                                                                when '033'
                                                                then substr(bol_documento, 2, length(bol_documento)-2)::bigint
                                                                when '237'
                                                                then substr(bol_documento, 2, length(bol_documento)-2)::bigint
                                                                when '461'
                                                                then substr(bol_documento, 2, length(bol_documento)-2)::bigint
                                                                else substr(bol_documento, 2, length(bol_documento))::bigint end)  
                                                        else 0 end) = vg.vg_id
                        where (not (vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . " or vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'] . ")) and vg_deposito_em_saldo = 1 
                        and vg_data_inclusao > (now() -'2 months'::interval)
                        and vg_pagto_tipo!=6
                         order by vg_data_inclusao desc";

        if ($bDebug)
                echo "sqlA1: $sql" . PHP_EOL;

        $rs_vendas = SQLexecuteQuery($sql);
        if (!$rs_vendas || pgNumRowsSafe($rs_vendas) == 0) {
                $msg = "Nenhuma venda de dep�sito em Saldo Gamer encontrada para concilia��o." . PHP_EOL;
                echo $msg;
        } else {
                echo "Encontradas " . pgNumRowsSafe($rs_vendas) . " vendas de dep�sito pendentes de concilia��o" . PHP_EOL;
                while ($rs_vendas_row = pgFetchArraySafe($rs_vendas)) {
                        $msg = "";
                        if ($bDebug)
                                echo str_repeat("-", 80) . PHP_EOL;

                        $boleto_id = (($rs_vendas_row['bbg_boleto_codigo']) ? $rs_vendas_row['bbg_boleto_codigo'] : 0);
                        $venda_id = $rs_vendas_row['vg_id'];
                        $usuario_id = $rs_vendas_row['vg_ug_id'];
                        $bbg_valor = $rs_vendas_row['bbg_valor'];
                        $bbg_valor_sem_taxa = $rs_vendas_row['bbg_valor'] - $rs_vendas_row['bbg_valor_taxa'];
                        $vg_deposito_em_saldo_valor = $rs_vendas_row['vg_deposito_em_saldo_valor'];
                        $vg_pagto_tipo = $rs_vendas_row['vg_pagto_tipo'];
                        $bol_codigo = $rs_vendas_row['bol_codigo'];

                        echo "Vai processar venda " . $venda_id . " do usu�rio " . $usuario_id . ", pagto_tipo: '" . $vg_pagto_tipo . "' , bbg_boleto_codigo: '" . $boleto_id . "', bol_codigo: " . $bol_codigo . " (valor: '" . $bbg_valor . "', valor sem taxa: '" . $bbg_valor_sem_taxa . "')" . PHP_EOL;

                        // Procura boletos
                        if ($vg_pagto_tipo == 2) {
                                echo "	vg_pagto_tipo $vg_pagto_tipo==2" . PHP_EOL;
                                if ($boleto_id > 0) {
                                        echo "Encontrado boleto " . $bol_codigo . " para venda " . $venda_id . PHP_EOL;

                                        // O retorno do banco com o boleto pode n�o ter sido importado ainda (nesse caso � null)
                                        $bol_valor = (($rs_vendas_row['bol_valor']) ? $rs_vendas_row['bol_valor'] : 0);
                                        $bol_banco = $rs_vendas_row['bol_banco'];
                                        $codigo_banco = $rs_vendas_row['vg_pagto_banco'];
                                        $venda_id = $rs_vendas_row['vg_id'];
                                        $usuario_id = $rs_vendas_row['vg_ug_id'];
                                        $parametros = array();

                                        if ($bDebug)
                                                echo "Banco: [" . $codigo_banco . "]" . PHP_EOL . " Valores Boleto: [(" . $bol_valor . "+" . $GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2'] . ") ? (" . $bbg_valor_sem_taxa . "+" . $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'] . ")]" . PHP_EOL;
                                        if (($bol_valor + $GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2']) == ($bbg_valor_sem_taxa + $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'])) {
                                                if ($bDebug)
                                                        echo "Vai conciliar Boleto (bol: $bol_codigo, vg: $venda_id, ug: $usuario_id)" . PHP_EOL;
                                                $parametros['valor'] = $bbg_valor_sem_taxa;

                                                $ret = conciliaMoneyDepositoSaldo_boleto($bol_codigo, $venda_id, $usuario_id, $parametros);
                                                if ($ret != "")
                                                        echo $ret;
                                                else
                                                        echo "Dep�sito por boleto conciliado com sucesso e saldo depositado" . PHP_EOL;
                                        } elseif ($codigo_banco == '033' || $codigo_banco == '237' || $codigo_banco == '461') {
                                                if ($bDebug)
                                                        echo "Vai conciliar Boleto Santander, Asaas OU Bradesco(bol: $bol_codigo, vg: $venda_id, ug: $usuario_id)" . PHP_EOL;

                                                $parametros['valor'] = $bbg_valor_sem_taxa;
                                                $parametros['banco'] = $codigo_banco;
                                                $ret = conciliaMoneyDepositoSaldo_boleto($bol_codigo, $venda_id, $usuario_id, $parametros);
                                                if ($ret != "")
                                                        echo $ret;
                                                else
                                                        echo "Dep�sito por boleto conciliado com sucesso e saldo depositado" . PHP_EOL;
                                        } else {
                                                if ($bDebug)
                                                        echo "N�O concilia Boleto ($boleto_id, $venda_id, $usuario_id)" . PHP_EOL;
                                        }
                                } else {
                                        if ($bDebug)
                                                echo "Nenhum Boleto encontrado ($venda_id, $usuario_id)" . PHP_EOL;
                                }
                        }
                        // Procura pagamentos Online
                        elseif ($vg_pagto_tipo > 4) {
                                $sql = "select * from tb_pag_compras pg where pg.idvenda = " . $venda_id . " and status = 3 and status_processed = 0";
                                echo "sqlB2: $sql" . PHP_EOL;
                                $rs_pagto = SQLexecuteQuery($sql);
                                if (!$rs_pagto || pgNumRowsSafe($rs_pagto) == 0)
                                        $msg = "Nenhum pagamento online encontrado para concilia��o (Saldo Gamer, vg_pagto_tipo: $vg_pagto_tipo)." . PHP_EOL;
                                else {
                                        if ($bDebug)
                                                echo "Encontrados " . pgNumRowsSafe($rs_pagto) . " pagtos para " . $rs_vendas_row['vg_id'] . PHP_EOL;
                                        $rs_pagto_row = pgFetchArraySafe($rs_pagto);
                                        // conciliaMoneyDepositoSaldo_PagtoOnline($venda_id, $usuario_id, $parametros)
                                        $venda_id = $rs_vendas_row['vg_id'];
                                        $usuario_id = $rs_vendas_row['vg_ug_id'];
                                        $total_pagto_sem_taxas = ($rs_pagto_row['total'] / 100 - $rs_pagto_row['taxas']);
                                        $parametros = array();
                                        if ($bDebug)
                                                echo "Valores PagtoOnline: [" . $total_pagto_sem_taxas . " ? " . $vg_deposito_em_saldo_valor . "]" . PHP_EOL;
                                        if (number_format($total_pagto_sem_taxas, 2, '.', '') == number_format($vg_deposito_em_saldo_valor, 2, '.', '')) {
                                                if ($bDebug)
                                                        echo "Vai conciliar PagtoOnline ($venda_id, $usuario_id)" . PHP_EOL;
                                                $parametros['valor'] = $total_pagto_sem_taxas;

                                                $ret = conciliaMoneyDepositoSaldo_PagtoOnline($venda_id, $usuario_id, $parametros);
                                                if ($ret != "")
                                                        echo $ret;
                                                else
                                                        echo "Dep�sito por pagamento online conciliado com sucesso e saldo depositado" . PHP_EOL;
                                        } else {
                                                if ($bDebug)
                                                        echo "N�O concilia PagtoOnline ($venda_id, $usuario_id)" . PHP_EOL;
                                        }
                                }
                        } else {
                                echo PHP_EOL . "   ******    Tipo de pagamento desconhecido (venda_id: $venda_id, usuario_id: $usuario_id, vg_pagto_tipo: " . $vg_pagto_tipo . ")" . PHP_EOL;
                        }

                        echo "Resumo: '" . $msg . "'" . PHP_EOL . PHP_EOL;
                }
        }
        echo str_repeat("_", 80) . PHP_EOL;
        $depositoPIX = conciliaAutomaticaMoneyDepositoSaldocomPIX();
        return 0;
} //end function conciliaAutomaticaMoneyDepositoSaldo()

function conciliaAutomaticaMoneyDepositoSaldocomPIX($webhook = false, $venda = 0)
{
        global $cReturn, $cSpaces, $sFontRedOpen, $sFontRedClose;

        $bDebug = false;
        $time_start_stats = getmicrotime();

        echo str_repeat("=", 80) . PHP_EOL . "Entrando em conciliaAutomaticaMoneyDepositoSaldocomPIX() - consulta Sondas " . date("Y-M-d H:i:s") . PHP_EOL;

        $minutes = 1440;
        echo PHP_EOL . "========================================================================" . PHP_EOL;
        echo "Prepara conciliacao de pagamentos online para deposito em Saldo com PIX (registros com idvenda>0 e nao processados nos ultimos " . $minutes . " minutos, desde " . date('Y-m-d H:i:s', strtotime("-" . $minutes . " minutes")) . ")" . PHP_EOL;
        // Prepara conciliacao de pagamentos online
        $date_ini = date('Y-m-d H:i:s', strtotime("-" . $minutes . " minutes"));
        $date_end = date("Y-m-d H:i:s");

        // O anterior esta demorando muito e nao e necessario consultar tb_venda_games, apenas tb_pag_compras 
        if ($webhook == true) {
                $sql = "select * from tb_pag_compras pgt where idvenda>0 and status_processed=0 and tipo_cliente='M' and idvenda = $1 and tipo_deposito = 2 AND (pgt.datainicio > (now() -'" . $minutes . " minutes'::interval)) AND status = 1 AND iforma=$2 ";
                $params = [$venda, $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']];
        } else {
                $sql = "select * from tb_pag_compras pgt where idvenda>0 and status_processed=0 and tipo_cliente='M' and tipo_deposito = 2 AND (pgt.datainicio > (now() -'" . $minutes . " minutes'::interval)) AND status = 1 AND iforma=$1 ";
                $params = [$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']];
        }

        $rs_total = SQLexecuteQueryParams($sql, $params);
        if ($bDebug) {
                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 0): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
        }

        if ($rs_total)
                $registros_total = pgNumRowsSafe($rs_total);
        $sql .= " order by pgt.datainicio desc ";

        if ($bDebug) {
                echo "DEBUG A1: " . $sql . $cReturn;
        }

        $time_start_stats0 = getmicrotime();
        $rs_transacoes = SQLexecuteQueryParams($sql, $params);
        if ($bDebug) {
                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 1): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
        }
        echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "TIME FOR FIRST QUERY: " . number_format(getmicrotime() - $time_start_stats0, 2, '.', '.') . "" . $cReturn;        //."$sql".$cReturn;

        if (!$rs_transacoes || pgNumRowsSafe($rs_transacoes) == 0)
                $msg = "Nenhuma transacao encontrada." . $cReturn;

        $time_start_stats0 = getmicrotime();
        $irows = 0;
        if ($rs_transacoes) {
                $npags = pgNumRowsSafe($rs_transacoes);
                echo "NPags: " . $npags . $cReturn;
                $total_pagtos_pendente = 0;
                while ($rs_transacoes_row = pgFetchArraySafe($rs_transacoes)) {
                        $time_start_stats0_in = getmicrotime();
                        $irows++;

                        $msgregister = $rs_transacoes_row['numcompra'] . " - " . $rs_transacoes_row['datainicio'] . " - " . $rs_transacoes_row['datacompra'] . " - " . $rs_transacoes_row['iforma'] . " - " . $rs_transacoes_row['idvenda'] . " - Proc: " . $rs_transacoes_row['status_processed'] . " - " . get_tipo_cliente_descricao($rs_transacoes_row['tipo_cliente']) . " -: R\$" . number_format($rs_transacoes_row['total'] / 100, 2, ',', '.') . " - '" . $rs_transacoes_row['cliente_nome'] . "'" . $cReturn;

                        if ($bDebug) {
                                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 2): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
                        }
                        $msg = "";
                        // Venda cadastrada
                        if ($rs_transacoes_row['idvenda'] > 0) {

                                if ($bDebug) {
                                        echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 4a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
                                        $time_start_stats_4a = getmicrotime();
                                }

                                // comeca aqui nova funcao getSondaBanco()
                                $dataconfirma = date("Y-m-d H:i:s");
                                unset($alinePIX);
                                $s_update_status_lr = "";

                                if ($webhook == true) {
                                        $b_sonda_PIX = null;
                                        $s_sonda = "none";
                                } else {
                                        $b_sonda_PIX = getTransacaoPagamentoOK($GLOBALS['PAGAMENTO_PIX_NOME_BANCO'], $rs_transacoes_row['numcompra'], $alinePIX);
                                        $s_sonda = (($b_sonda_PIX) ? "OK" : "none");
                                }

                                if ($b_sonda_PIX) {
                                        $sBanco = $GLOBALS['PAGAMENTO_PIX_NOME_BANCO'];
                                        //pegar a data do JSON
                                        $dataconfirma = "'" . substr(str_replace('T', ' ', $alinePIX), 0, 19) . "'";
                                } else if ($webhook == true) {
                                        $sBanco = $GLOBALS['PAGAMENTO_PIX_NOME_BANCO'];
                                        //pegar a data do JSON
                                        $dataconfirma = "CURRENT_TIMESTAMP";
                                }

                                $dataconfirma = str_replace("/", "-", $dataconfirma);

                                if ($bDebug) {
                                        echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 4b): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . " (waiting for Sonda: " . number_format(getmicrotime() - $time_start_stats_4a, 2, '.', '.') . ") " . $cReturn;
                                }

                                // Procura pagamentos em aberto no site do banco (Sonda), se (status=1 & sonda) => "NO SYNC"
                                $s_sync = (($b_sonda_PIX) ? "NO SYNC" : "");
                                $prefix_1 = getDocPrefix($rs_transacoes_row['iforma']);
                                $vg_pagto_tipo = getCodigoNumericoParaPagto($rs_transacoes_row['iforma']);

                                if ($bDebug) {
                                        echo "MSG_543443 - rs_transacoes_row['iforma']: '" . $rs_transacoes_row['iforma'] . "' -> '" . $vg_pagto_tipo . "', s_sync = '" . $s_sync . "' (8765)" . PHP_EOL;
                                }

                                // Se (!$s_sync), ou seja (status=1 & sonda) => completa a venda POR SONDA
                                if ($s_sync == "NO SYNC" || $webhook == true) {                        /////   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

                                        if ($bDebug) {
                                                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 5a): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
                                        }

                                        //Inicia transacao
                                        if ($msg == "") {
                                                $sql = "BEGIN TRANSACTION ";
                                                $ret = SQLexecuteQuery($sql);
                                                $ret = true;                                                                                                /////////////////////
                                                if (!$ret)
                                                        $msg = "Erro ao iniciar transacao." . PHP_EOL;
                                        }

                                        // Marca registro como processado (status_processed=1), e status=3, ja que se chegou aqui quer dizer que nao passou por confirmaBanco.php
                                        // Retira status_processed=1 para poder encontrar o registro depois em sqlB2: 
                                        $sql = "update tb_pag_compras set datacompra=CURRENT_TIMESTAMP, dataconfirma=" . $dataconfirma . ", status=3 " . $s_update_status_lr . " where numcompra=$1 ";
                                        echo PHP_EOL . " NO SYNC => " . $sql . " [" . $rs_transacoes_row['numcompra'] . "] " . PHP_EOL . "($msg)" . PHP_EOL;
                                        echo "DEBUG F (atualiza status_processed=1, vendaid = " . $rs_transacoes_row['idvenda'] . "): " . $sql . PHP_EOL;

                                        $rs_update2 = SQLexecuteQueryParams($sql, [$rs_transacoes_row['numcompra']]);
                                        if (!$rs_update2) {
                                                $msg = "Erro atualizando status de registro (62aa)." . $cReturn . "$sql" . $cReturn;
                                                echo $msg;
                                        }
                                        if (!$msg) {

                                                // Atualiza dados para tabela vendas
                                                //'DADOS_PAGTO_RECEBIDO' => 2
                                                $sql_update = "update tb_venda_games set 
                                                                                vg_pagto_valor_pago		= $1,
                                                                                vg_pagto_tipo			= $2,
                                                                                vg_pagto_num_docto		= $3, 
                                                                                vg_pagto_data_inclusao	= $4,
                                                                                vg_usuario_obs			= $5,
                                                                                vg_ultimo_status		= " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "
                                                                        where vg_id=$6;";

                                                $rs_update_params = [
                                                        ($rs_transacoes_row['total'] / 100 + $rs_transacoes_row['taxas'] + $rs_transacoes_row['frete'] + $rs_transacoes_row['manuseio']),
                                                        $vg_pagto_tipo,
                                                        $prefix_1 . $rs_transacoes_row['iforma'] . "_" . $rs_transacoes_row['numcompra'],
                                                        $rs_transacoes_row['datainicio'],
                                                        "Pagamento Online " . $sBanco . " POR SONDA [" . $rs_transacoes_row['iforma'] . "] em " . date("Y-m-d H:i:s"),
                                                        $rs_transacoes_row['idvenda']
                                                ];

                                                $rs_update = SQLexecuteQueryParams($sql_update, $rs_update_params);
                                                $sout = $rs_transacoes_row['datainicio'] . PHP_EOL . "   " . $rs_transacoes_row['numcompra'] . PHP_EOL . "   tipo: (" . $rs_transacoes_row['iforma'] . ") " . getDescricaoPagtoOnline($rs_transacoes_row['iforma']) . "," . PHP_EOL . "   idvenda: " . $rs_transacoes_row['idvenda'] . "." . PHP_EOL;

                                                if (!$rs_update) {
                                                        $msg = "Erro atualizando registro POR SONDA (61a)." . $sql_update . PHP_EOL . $sout . $cReturn;
                                                        echo $msg;
                                                } else {
                                                        echo "Pagamento atualizado POR SONDA com sucesso (4322)." . $sout . $cReturn;
                                                }
                                        }

                                        //Finaliza transacao
                                        if ($msg == "") {
                                                $sql = "COMMIT TRANSACTION ";
                                                $ret = SQLexecuteQuery($sql);
                                                if (!$ret)
                                                        $msg = "Erro ao comitar transacao." . PHP_EOL;

                                                $msg_sonda = "PROCESSADO POR SONDA";
                                        } else {
                                                $sql = "ROLLBACK TRANSACTION ";
                                                $ret = SQLexecuteQuery($sql);
                                                if (!$ret)
                                                        $msg = "Erro ao dar rollback na transacao." . PHP_EOL;

                                                $msg_sonda = "PROCESSAMENTO POR SONDA FALHOU (ROLLBACK TRANSACTION)";
                                        }

                                        echo $msg_sonda . ": Sonda='$s_sonda' forma:" . $rs_transacoes_row['iforma'] . ", numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . $rs_transacoes_row['idvenda'] . "- " . $rs_transacoes_row['datainicio'] . " - R\$" . number_format(($rs_transacoes_row['total'] / 100 - $rs_transacoes_row['taxas']), 2, '.', '.') . " (SYNC)" . $cReturn;

                                        if ($bDebug) {
                                                echo "" . $cReturn . "" . $cReturn . "" . $cReturn . "ELAPSED TOTAL TIME (Prev 5b): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "" . $cReturn;
                                        }
                                } else {
                                        $total_pagto = $rs_transacoes_row['total'] / 100 - $rs_transacoes_row['taxas'];
                                        $total_pagtos_pendente += $total_pagto;
                                        $leading_zeros = (($total_pagto < 1000) ? (($total_pagto < 100) ? "00" : "0") : "");
                                        echo "Nao Processado por sonda: forma:" . $rs_transacoes_row['iforma'] . ", numcompra: " . $rs_transacoes_row['numcompra'] . " - IDVenda: " . str_pad($rs_transacoes_row['idvenda'], 8, '0', STR_PAD_LEFT) . " - " . $rs_transacoes_row['datainicio'] . " - R\$" . $leading_zeros . number_format(($total_pagto), 2, '.', '.') . " (NO SYNC) [" . number_format(getmicrotime() - $time_start_stats0_in, 2, '.', '.') . " s] [" . number_format(getmicrotime() - $time_start_stats0, 2, '.', '.') . " s]" . $cReturn;
                                }
                        } // 
                        else {
                                echo "Nao processado: idvenda=0." . $cReturn . "numcompra: " . $rs_transacoes_row['numcompra'] . "- " . $rs_transacoes_row['datainicio'] . "" . $cReturn;
                        }
                } // End while loop 

        } // End if(rs)
        echo "Tempo medio de processamentoq: " . number_format((getmicrotime() - $time_start_stats0) / (($irows > 0) ? $irows : 1), 2, '.', '.') . " s/processamento (WSAa)" . $cReturn;
        echo "Total pagamentos pendentes: R\$" . number_format($total_pagtos_pendente, 2, '.', '.') . " em $npags pagamentos (WSATPa)" . $cReturn;


        echo str_repeat("=", 80) . PHP_EOL . "Entrando em conciliaAutomaticaMoneyDepositoSaldo() - vai conciliar " . date("Y-M-d H:i:s") . PHP_EOL;

        // Procura vendas para deposito em saldo de Gamer nem canceladas nem completas
        // No inner join com boletos_pendentes ->
        //		bol_documento pode ter um caracter extra no final que pode ser nao numerico, entao testamos so para tipo "6" que nao tem esse problema

        $params_vendas = [$GLOBALS['PAGAMENTO_PIX_NUMERIC']];
        if ($webhook == true) {
                $where_venda = " and vg.vg_id = $2 ";
                $params_vendas[] = $venda;
        } else {
                $where_venda = "";
        }
        $sql = "select * 
                        from tb_venda_games vg
                                left outer join boleto_bancario_games bbg on bbg.bbg_vg_id = vg.vg_id 
                                left outer join tb_pag_compras pg on pg.idvenda = vg.vg_id
                                left outer join boletos_pendentes bol on 
                                        (case  substr(bol_documento, 1, 1) 
                                                        when '6' 
                                                        then (case bol_banco 
                                                                when '033'
                                                                then substr(bol_documento, 2, length(bol_documento)-2)::bigint
                                                                when '237'
                                                                then substr(bol_documento, 2, length(bol_documento)-2)::bigint
                                                                when '461'
                                                                then substr(bol_documento, 2, length(bol_documento)-2)::bigint
                                                                else substr(bol_documento, 2, length(bol_documento))::bigint end)  
                                                        else 0 end) = vg.vg_id
                        where (not (vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . " or vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'] . ")) and vg_deposito_em_saldo = 1 
                        and vg_data_inclusao > (now() -'" . ($minutes + 60) . " minutes'::interval)
                        and vg_pagto_tipo=$1" . $where_venda . "
                         order by vg_data_inclusao desc";

        if ($bDebug)
                echo "sqlA1: $sql" . PHP_EOL;

        $rs_vendas = SQLexecuteQueryParams($sql, $params_vendas);
        if (!$rs_vendas || pgNumRowsSafe($rs_vendas) == 0) {
                $msg = "Nenhuma venda de deposito em Saldo Gamer encontrada para conciliacao." . PHP_EOL;
                echo $msg;
        } else {
                echo "Encontradas " . pgNumRowsSafe($rs_vendas) . " vendas de deposito pendentes de conciliacao" . PHP_EOL;
                while ($rs_vendas_row = pgFetchArraySafe($rs_vendas)) {
                        $msg = "";
                        if ($bDebug)
                                echo str_repeat("-", 80) . PHP_EOL;

                        $boleto_id = (($rs_vendas_row['bbg_boleto_codigo']) ? $rs_vendas_row['bbg_boleto_codigo'] : 0);
                        $venda_id = $rs_vendas_row['vg_id'];
                        $usuario_id = $rs_vendas_row['vg_ug_id'];
                        $bbg_valor = $rs_vendas_row['bbg_valor'];
                        $bbg_valor_sem_taxa = $rs_vendas_row['bbg_valor'] - $rs_vendas_row['bbg_valor_taxa'];
                        $vg_deposito_em_saldo_valor = $rs_vendas_row['vg_deposito_em_saldo_valor'];
                        $vg_pagto_tipo = $rs_vendas_row['vg_pagto_tipo'];
                        $bol_codigo = $rs_vendas_row['bol_codigo'];

                        echo "Vai processar venda " . $venda_id . " do usuario " . $usuario_id . ", pagto_tipo: '" . $vg_pagto_tipo . "' , bbg_boleto_codigo: '" . $boleto_id . "', bol_codigo: " . $bol_codigo . " (valor: '" . $bbg_valor . "', valor sem taxa: '" . $bbg_valor_sem_taxa . "')" . PHP_EOL;

                        $sql = "select * from tb_pag_compras pg where pg.idvenda = $1 and status = 3 and status_processed = 0";
                        echo "sqlB2: $sql" . PHP_EOL;
                        $rs_pagto = SQLexecuteQueryParams($sql, [$venda_id]);
                        if (!$rs_pagto || pgNumRowsSafe($rs_pagto) == 0)
                                $msg = "Nenhum pagamento online encontrado para conciliacao (Saldo Gamer, vg_pagto_tipo: $vg_pagto_tipo)." . PHP_EOL;
                        else {
                                if ($bDebug)
                                        echo "Encontrados " . pgNumRowsSafe($rs_pagto) . " pagtos para " . $rs_vendas_row['vg_id'] . PHP_EOL;
                                $rs_pagto_row = pgFetchArraySafe($rs_pagto);
                                // conciliaMoneyDepositoSaldo_PagtoOnline($venda_id, $usuario_id, $parametros)
                                $venda_id = $rs_vendas_row['vg_id'];
                                $usuario_id = $rs_vendas_row['vg_ug_id'];
                                $total_pagto_sem_taxas = ($rs_pagto_row['total'] / 100 - $rs_pagto_row['taxas']);
                                $parametros = array();
                                if ($bDebug)
                                        echo "Valores PagtoOnline: [" . $total_pagto_sem_taxas . " ? " . $vg_deposito_em_saldo_valor . "]" . PHP_EOL;
                                if (number_format($total_pagto_sem_taxas, 2, '.', '') == number_format($vg_deposito_em_saldo_valor, 2, '.', '')) {
                                        if ($bDebug)
                                                echo "Vai conciliar PagtoOnline ($venda_id, $usuario_id)" . PHP_EOL;
                                        $parametros['valor'] = $total_pagto_sem_taxas;

                                        $ret = conciliaMoneyDepositoSaldo_PagtoOnline($venda_id, $usuario_id, $parametros);
                                        if ($ret != "")
                                                echo $ret;
                                        else
                                                echo "Deposito por pagamento online conciliado com sucesso e saldo depositado" . PHP_EOL;
                                } else {
                                        if ($bDebug)
                                                echo "NAO concilia PagtoOnline ($venda_id, $usuario_id)" . PHP_EOL;
                                }
                        }

                        echo "Resumo: " . $msg . PHP_EOL . PHP_EOL;
                }
        }
        echo str_repeat("_", 80) . PHP_EOL;
        return 0;
} //end function conciliaAutomaticaMoneyDepositoSaldocomPIX()

function conciliaMoneyDepositoSaldo_PagtoOnline($venda_id, $usuario_id, $parametros)
{

        //Validacoes
        $msg = "";

        //Valida usuario_id
        if (!$usuario_id)
                $msg = "C�digo do usu�rio Gamer n�o fornecido." . PHP_EOL;
        elseif (!is_numeric($usuario_id))
                $msg = "C�digo do usu�rio Gamer inv�lido." . PHP_EOL;

        //Recupera o boleto pendente
        if ($msg == "") {
                $sql = "select * from tb_pag_compras pag where pag.idvenda = " . $venda_id;
                echo "sqlC1: $sql" . PHP_EOL;
                $rs_pagto = SQLexecuteQuery($sql);
                if (!$rs_pagto || pgNumRowsSafe($rs_pagto) == 0)
                        $msg = "Nenhum pagamento encontrado." . PHP_EOL;
                else {
                        $rs_pagto_row = pgFetchArraySafe($rs_pagto);
                        $pag_data = $rs_pagto_row['datainicio'];
                        $pag_valor = $rs_pagto_row['total'] / 100;
                        $pag_banco = $rs_pagto_row['banco'];
                        $pag_iforma = $rs_pagto_row['iforma'];
                        echo "pag_data: '$pag_data', pag_valor: $pag_valor, pag_banco: $pag_banco, pag_iforma: '$pag_iforma'" . PHP_EOL;        //	"pag_documento: '$pag_documento', "
                }
        }

        //Recupera o saldo do usu�rio
        if ($msg == "") {
                $sql = "select ug_perfil_saldo from usuarios_games where ug_id =" . $usuario_id;
                echo "sqlA1a: $sql" . PHP_EOL;
                $rs_saldo = SQLexecuteQuery($sql);
                if (!$rs_saldo || pgNumRowsSafe($rs_saldo) == 0)
                        $msg = "Nenhum usu�rio encontrado." . PHP_EOL;
                else {
                        $rs_saldo_row = pgFetchArraySafe($rs_saldo);
                        $ug_perfil_saldo_prev = $rs_saldo_row['ug_perfil_saldo'];
                        echo " ++++P [" . date("Y-m-d H:i:s") . "] para ug_id: " . $usuario_id . " ug_perfil_saldo_prev = '" . $ug_perfil_saldo_prev . "', valor = " . $parametros['valor'] . PHP_EOL;
                }
        }


        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transa��o." . PHP_EOL;
        }

        //Concilia pagto Online
        if ($msg == "") {
                // a esta altura j� deve estar status=3	
                $sql = "update tb_pag_compras set status_processed = 1, dataconfirma = CURRENT_TIMESTAMP where idvenda = " . $venda_id . "";
                echo "sqlAZ2: $sql [" . getmicrotime() . "]" . PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar boleto." . PHP_EOL;
        }

        //Credita valor do boleto no usu�rio Gamer
        if ($msg == "") {
                $sql = "update usuarios_games set ug_perfil_saldo = coalesce(ug_perfil_saldo,0) + " . $parametros['valor'] . "
                                where ug_id =" . $usuario_id;
                echo "sqlAZ3: $sql [" . getmicrotime() . "]" . PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao creditar valor do boleto no usu�rio Gamer." . PHP_EOL;
        }

        //Completa venda Money
        if ($msg == "") {
                $sql = "update tb_venda_games set vg_ultimo_status =  " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . ", vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP where vg_id =" . $venda_id . " and vg_ug_id =" . $usuario_id . ";";
                echo "sqlZ4: $sql" . PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao completar venda Money (PagtoOnline)." . PHP_EOL;
        }

        //Insere registro em saldo_fifo
        if ($msg == "") {
                $sql = "insert into saldo_composicao_fifo (ug_id,scf_data_deposito,scf_valor,scf_valor_disponivel,scf_canal,scf_comissao,scf_id_pagamento, vg_id) values (" . $usuario_id . ",CURRENT_TIMESTAMP," . $parametros['valor'] . "," . $parametros['valor'] . ",'G',0,'" . $pag_iforma . "', " . (($venda_id) ? $venda_id : 0) . ")";
                echo "sqlZ5: $sql" . PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao inserir Saldo FIFO (PagtoOnline)." . PHP_EOL;
        }

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transa��o." . PHP_EOL;
                // Log transaction's success
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transa��o." . PHP_EOL;
        }

        //Recupera o saldo do usu�rio no final
        if ($msg == "") {
                $sql = "select ug_perfil_saldo from usuarios_games where ug_id =" . $usuario_id;
                echo "sqlA1b: $sql" . PHP_EOL;
                $rs_saldo = SQLexecuteQuery($sql);
                if (!$rs_saldo || pgNumRowsSafe($rs_saldo) == 0)
                        $msg = "Nenhum usu�rio encontrado." . PHP_EOL;
                else {
                        $rs_saldo_row = pgFetchArraySafe($rs_saldo);
                        $ug_perfil_saldo_prev = $rs_saldo_row['ug_perfil_saldo'];
                        echo " ++++P2 [" . date("Y-m-d H:i:s") . "] para ug_id: " . $usuario_id . " ug_perfil_saldo_prev = '" . $ug_perfil_saldo_prev . "', valor = " . $parametros['valor'] . PHP_EOL;
                }
        }

        return $msg;
} //end function conciliaMoneyDepositoSaldo_PagtoOnline

function concilia_eppVariavel($vg_id, $ug_id)
{

        if (($_SERVER["REMOTE_ADDR"] ?? "") == "201.93.162.169" && $ug_id == 1333904) {
                $sql = "update tb_pag_compras set datacompra = '2023-06-29 10:14:00', status = 3,status_processed = 1,dataconfirma = '2023-06-29 10:14:00' 
		where idvenda = $vg_id;";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao conciliar venda." . PHP_EOL;
                $parametros['ultimo_status_obs'] = "Teste";
                processaVendaGames($vg_id, 1, $parametros);
                processaEmailVendaGames($vg_id, $parametros);
        }
}
