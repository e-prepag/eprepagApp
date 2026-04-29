<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

function verificaFaltaCPFNome(array $vetorPublisher, mixed $diaLimite, mixed &$rs_dados_incompletos, ?array $vetorPublisherNovos = NULL): bool
{

    /*********************************************
     ***  Dia Limite para gerao dos arquivos 
     *********************************************
        $diaLimite
     */

    // Instanciando a variavel para verificao
    $verificadorPublishersNovos = is_array($vetorPublisherNovos) ? implode(",", $vetorPublisherNovos) : '';

    //=========  Ms/Ano
    if ((int)date('j') <= (int)$diaLimite) {
        $currentmonth = mktime(0, 0, 0, (int)date('n') - 1, 1, (int)date('Y'));
    } //end if(date('j') <= 10)
    else {
        $currentmonth = mktime(0, 0, 0, (int)date('n'), 1, (int)date('Y'));
    } //end else do if(date('j') <= 10)
    $mesAno = date('m/Y', (int)$currentmonth);

    // Split ano/mes
    list($mes, $ano) = explode("/", $mesAno);

    // Buscando informaes 
    $params = [];

    /* =======================
   QUERY BASE
======================= */

    $sql = "select 
            ug_cpf, 
            ug_nome,
            ug_id,
            ug_email,
            min(data) as data_transacao,
            tipo
        from ( 

            (select 
                    ug_cpf, 
                    ug_nome_cpf as ug_nome,
                    ug_id::character varying,
                    ug_email,
                    vg.vg_data_concilia as data,
                    'GAMER' as tipo
             from tb_venda_games vg 
                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id
             where vg.vg_ultimo_status = ?
               and vg.vg_data_concilia >= ?
               and vg.vg_data_concilia <= ?
               and vg.vg_ug_id != ?
               and (
                    ug_cpf is null OR
                    ug_nome_cpf is null OR
                    length(ug_cpf) < 14 OR
                    ug_nome_cpf = ''
               )
               and vgm_opr_codigo IN (" . implode(',', array_fill(0, count($vetorPublisher), '?')) . ")
             group by ug_cpf, ug_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo, vg_data_concilia)

            union all

            (select 
                    vgm_cpf as ug_cpf, 
                    vgm_nome_cpf as ug_nome, 
                    ug_id::character varying,
                    ug_email,
                    vg.vg_data_inclusao as data,
                    'LAN HOUSE' as tipo
             from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id
             where vg.vg_ultimo_status = ?
               and vg.vg_data_inclusao >= ?
               and vg.vg_data_inclusao <= ?
               and (
                    vgm_cpf is null OR
                    vgm_nome_cpf is null OR
                    length(vgm_cpf) < 14 OR
                    vgm_nome_cpf = ''
               )
               and vgm_opr_codigo IN (" . implode(',', array_fill(0, count($vetorPublisher), '?')) . ")
             group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo, vg_data_inclusao)

            union all

            (select 
                    picc_cpf as ug_cpf, 
                    picc_nome as ug_nome, 
                    'ID PIN:'||pih_pin_id as ug_id,
                    '' as ug_email,
                    pih_data as data,
                    'CARTAO' as tipo
             from pins_integracao_card_historico
                    left outer join pins_integracao_card_cpf ON pin_codinterno = pih_pin_id
             where pin_status = ?
               and pih_codretepp = ?
               and pih_data >= ?
               and pih_data <= ?
               and (
                    picc_cpf is null OR
                    picc_nome is null OR
                    length(picc_cpf) < 14 OR
                    picc_nome = ''
               )
               and pih_id IN (" . implode(',', array_fill(0, count($vetorPublisher), '?')) . ")
             group by picc_cpf, picc_nome, pih_pin_id, ug_email, pih_data, tipo)

            union all

            (select 
                    vgcbe_cpf as ug_cpf, 
                    vgcbe_nome_cpf as ug_nome, 
                    'ID Venda:'||vgcbe_vg_id as ug_id,
                    vgcbe_ex_email as ug_email,
                    vgcbe_data_inclusao as data,
                    'BOLETO EXPRESS' as tipo
             from tb_venda_games_cpf_boleto_express
                    inner join tb_venda_games ON vg_id = vgcbe_vg_id
                    inner join tb_venda_games_modelo ON vgm_vg_id = vg_id
             where vg_ultimo_status = ?
               and vgcbe_data_inclusao >= ?
               and vgcbe_data_inclusao <= ?
               and (
                    vgcbe_cpf is null OR
                    vgcbe_nome_cpf is null OR
                    length(vgcbe_cpf) < 14 OR
                    vgcbe_nome_cpf = ''
               )
               and vgm_opr_codigo IN (" . implode(',', array_fill(0, count($vetorPublisher), '?')) . ")
             group by vgcbe_cpf, vgcbe_nome_cpf, vgcbe_vg_id, vgcbe_ex_email, vgcbe_data_inclusao, tipo)
";

    /* =======================
   PARAMS BASE
======================= */

    $dataInicioMes = "$ano-$mes-01 00:00:00";
    $dataFimMes    = "$ano-$mes-" . date("t", mktime(0, 0, 0, $mes, 1, $ano)) . " 23:59:59";

    $params = array_merge(
        [
            $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'],
            $dataInicioMes,
            $dataFimMes,
            $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']
        ],
        $vetorPublisher,

        [
            $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'],
            $dataInicioMes,
            $dataFimMes
        ],
        $vetorPublisher,

        [
            '4',
            '2',
            $dataInicioMes,
            $dataFimMes
        ],
        $vetorPublisher,

        [
            $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'],
            $dataInicioMes,
            $dataFimMes
        ],
        $vetorPublisher
    );

    /* =======================
   IF PUBLISHERS NOVOS
======================= */

    if (!empty($verificadorPublishersNovos)) {

        foreach ($vetorPublisherNovos as $value) {

            $sql .= "

        union all

        (select 
            ug_cpf,
            ug_nome_cpf as ug_nome,
            ug_id::character varying,
            ug_email,
            vg.vg_data_concilia as data,
            'GAMER' as tipo
         from tb_venda_games vg
                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id
         where vg.vg_ultimo_status = ?
           and vg.vg_data_concilia >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ?)
           and vg.vg_data_concilia <= ?
           and vg.vg_ug_id != ?
           and (
                ug_cpf is null OR
                ug_nome_cpf is null OR
                length(ug_cpf) < 14 OR
                ug_nome_cpf = ''
           )
           and vgm_opr_codigo = ?
         group by ug_cpf, ug_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo, vg_data_concilia)
        ";

            $params[] = $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'];
            $params[] = $value;
            $params[] = $dataFimMes;
            $params[] = $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'];
            $params[] = $value;
        }
    }

    /* =======================
   FINAL QUERY
======================= */

    $sql .= "
        ) tabelaUnion
        group by ug_cpf, ug_nome, ug_id, ug_email, tipo
        order by tipo, ug_id
";

    /* =======================
   EXECU��O
======================= */

    $rs_dados_incompletos = SQLexecuteQueryParams($sql, $params);
    //echo pg_num_rows($rs_dados_incompletos)."<br>";
    if (!$rs_dados_incompletos) {
        echo "Erro na Query de Levantamento de CPFs e/ou Nome em Branco para os Publishers (" . implode(",", $vetorPublisher) . ") e Publishers Novos (" . (is_array($vetorPublisherNovos) ? implode(",", $vetorPublisherNovos) : "") . ").<br>" . PHP_EOL;
        return false;
    }
    if ((($rs_dados_incompletos) ? pg_num_rows($rs_dados_incompletos) : 0) == 0) {
        //echo "Vai retorna Falso. Ou seja, NO possui dados faltantes.<br>";
        return false;
    } //end if(!$rs_dados_incompletos || pg_num_rows($rs_dados_incompletos) == 0)
    else {
        //echo "Vai retorna verdadeiro. Ou seja, possui dados faltantes.<br>";
        return true;
    } //end else

} //end function verificaFaltaCPFNome

function verificaCPFValido(array $vetorPublisher, mixed $diaLimite, mixed &$rs_dados, ?array $vetorPublisherNovos = NULL): bool
{

    /*********************************************
     ***  Dia Limite para gerao dos arquivos 
     *********************************************
        $diaLimite
     */

    // Instanciando a variavel para verificao
    $verificadorPublishersNovos = is_array($vetorPublisherNovos) ? implode(",", $vetorPublisherNovos) : '';

    //=========  Ms/Ano
    if ((int)date('j') <= (int)$diaLimite) {
        $currentmonth = mktime(0, 0, 0, (int)date('n') - 1, 1, (int)date('Y'));
    } //end if(date('j') <= 10)
    else {
        $currentmonth = mktime(0, 0, 0, (int)date('n'), 1, (int)date('Y'));
    } //end else do if(date('j') <= 10)
    $mesAno = date('m/Y', (int)$currentmonth);

    // Split ano/mes
    list($mes, $ano) = explode("/", $mesAno);

    // Buscando informaes 
    $params = [];

    /* =======================
   DATAS
======================= */

    $dataInicioMes = "$ano-$mes-01 00:00:00";
    $dataFimMes    = "$ano-$mes-" . date("t", mktime(0, 0, 0, $mes, 1, $ano)) . " 23:59:59";

    /* =======================
   QUERY BASE
======================= */

    $sql = "select 
            ug_cpf, 
            ug_nome,
            ug_id,
            ug_email,
            tipo
        from (

            (select 
                    ug_cpf,
                    ug_nome_cpf as ug_nome,
                    ug_id::character varying,
                    ug_email,
                    'GAMER' as tipo
             from tb_venda_games vg
                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                    inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id
             where vg.vg_ultimo_status = ?
               and vg.vg_data_concilia >= ?
               and vg.vg_data_concilia <= ?
               and vg.vg_ug_id != ?
               and (
                    ug_cpf is not null OR
                    ug_nome_cpf is not null OR
                    length(ug_cpf) = 14 OR
                    ug_nome_cpf != ''
               )
               and vgm_opr_codigo IN (" . implode(',', array_fill(0, count($vetorPublisher), '?')) . ")
             group by ug_cpf, ug_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo)

            union all

            (select 
                    vgm_cpf as ug_cpf,
                    vgm_nome_cpf as ug_nome,
                    ug_id::character varying,
                    ug_email,
                    'LAN HOUSE' as tipo
             from tb_dist_venda_games vg
                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                    inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id
             where vg.vg_ultimo_status = ?
               and vg.vg_data_inclusao >= ?
               and vg.vg_data_inclusao <= ?
               and (
                    vgm_cpf is not null OR
                    vgm_nome_cpf is not null OR
                    length(vgm_cpf) = 14 OR
                    vgm_nome_cpf != ''
               )
               and vgm_opr_codigo IN (" . implode(',', array_fill(0, count($vetorPublisher), '?')) . ")
             group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo)

            union all

            (select 
                    picc_cpf as ug_cpf,
                    picc_nome as ug_nome,
                    'ID PIN:'||pih_pin_id as ug_id,
                    '' as ug_email,
                    'CARTAO' as tipo
             from pins_integracao_card_historico
                    left outer join pins_integracao_card_cpf ON pin_codinterno = pih_pin_id
             where pin_status = ?
               and pih_codretepp = ?
               and pih_data >= ?
               and pih_data <= ?
               and (
                    picc_cpf is not null OR
                    picc_nome is not null OR
                    length(picc_cpf) = 14 OR
                    picc_nome != ''
               )
               and pih_id IN (" . implode(',', array_fill(0, count($vetorPublisher), '?')) . ")
             group by picc_cpf, picc_nome, pih_pin_id, ug_email, tipo)

            union all

            (select 
                    vgcbe_cpf as ug_cpf,
                    vgcbe_nome_cpf as ug_nome,
                    'ID Venda:'||vgcbe_vg_id as ug_id,
                    vgcbe_ex_email as ug_email,
                    'BOLETO EXPRESS' as tipo
             from tb_venda_games_cpf_boleto_express
                    inner join tb_venda_games ON vg_id = vgcbe_vg_id
                    inner join tb_venda_games_modelo ON vgm_vg_id = vg_id
             where vg_ultimo_status = ?
               and vgcbe_data_inclusao >= ?
               and vgcbe_data_inclusao <= ?
               and (
                    vgcbe_cpf is not null OR
                    vgcbe_nome_cpf is not null OR
                    length(vgcbe_cpf) = 14 OR
                    vgcbe_nome_cpf != ''
               )
               and vgm_opr_codigo IN (" . implode(',', array_fill(0, count($vetorPublisher), '?')) . ")
             group by vgcbe_cpf, vgcbe_nome_cpf, vgcbe_vg_id, vgcbe_ex_email, vgcbe_data_inclusao, tipo)
";

    /* =======================
   PARAMS BASE
======================= */

    $params = array_merge(
        [
            $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'],
            $dataInicioMes,
            $dataFimMes,
            $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']
        ],
        $vetorPublisher,

        [
            $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'],
            $dataInicioMes,
            $dataFimMes
        ],
        $vetorPublisher,

        [
            '4',
            '2',
            $dataInicioMes,
            $dataFimMes
        ],
        $vetorPublisher,

        [
            $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'],
            $dataInicioMes,
            $dataFimMes
        ],
        $vetorPublisher
    );

    /* =======================
   PUBLISHERS NOVOS
======================= */

    if (!empty($verificadorPublishersNovos)) {

        foreach ($vetorPublisherNovos as $value) {

            $sql .= "

        union all

        (select 
            ug_cpf,
            ug_nome_cpf as ug_nome,
            ug_id::character varying,
            ug_email,
            'GAMER' as tipo
         from tb_venda_games vg
                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id
         where vg.vg_ultimo_status = ?
           and vg.vg_data_concilia >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ?)
           and vg.vg_data_concilia <= ?
           and vg.vg_ug_id != ?
           and (
                ug_cpf is not null OR
                ug_nome_cpf is not null OR
                length(ug_cpf) = 14 OR
                ug_nome_cpf != ''
           )
           and vgm_opr_codigo = ?
         group by ug_cpf, ug_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo)

        union all

        (select 
            vgm_cpf as ug_cpf,
            vgm_nome_cpf as ug_nome,
            ug_id::character varying,
            ug_email,
            'LAN HOUSE' as tipo
         from tb_dist_venda_games vg
                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id
         where vg.vg_ultimo_status = ?
           and vg.vg_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ?)
           and vg.vg_data_inclusao <= ?
           and (
                vgm_cpf is not null OR
                vgm_nome_cpf is not null OR
                length(vgm_cpf) = 14 OR
                vgm_nome_cpf != ''
           )
           and vgm_opr_codigo = ?
         group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo)

        union all

        (select 
            picc_cpf as ug_cpf,
            picc_nome as ug_nome,
            'ID PIN:'||pih_pin_id as ug_id,
            '' as ug_email,
            'CARTAO' as tipo
         from pins_integracao_card_historico
                left outer join pins_integracao_card_cpf ON pin_codinterno = pih_pin_id
         where pin_status = ?
           and pih_codretepp = ?
           and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ?)
           and pih_data <= ?
           and (
                picc_cpf is not null OR
                picc_nome is not null OR
                length(picc_cpf) = 14 OR
                picc_nome != ''
           )
           and pih_id = ?
         group by picc_cpf, picc_nome, pih_pin_id, ug_email, tipo)

        union all

        (select 
            vgcbe_cpf as ug_cpf,
            vgcbe_nome_cpf as ug_nome,
            'ID Venda:'||vgcbe_vg_id as ug_id,
            vgcbe_ex_email as ug_email,
            'BOLETO EXPRESS' as tipo
         from tb_venda_games_cpf_boleto_express
                inner join tb_venda_games ON vg_id = vgcbe_vg_id
                inner join tb_venda_games_modelo ON vgm_vg_id = vg_id
         where vg_ultimo_status = ?
           and vgcbe_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ?)
           and vgcbe_data_inclusao <= ?
           and (
                vgcbe_cpf is not null OR
                vgcbe_nome_cpf is not null OR
                length(vgcbe_cpf) = 14 OR
                vgcbe_nome_cpf != ''
           )
           and vgm_opr_codigo = ?
         group by vgcbe_cpf, vgcbe_nome_cpf, vgcbe_vg_id, vgcbe_ex_email, vgcbe_data_inclusao, tipo)
        ";

            $params = array_merge($params, [
                $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'],
                $value,
                $dataFimMes,
                $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'],
                $value,

                $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'],
                $value,
                $dataFimMes,
                $value,

                '4',
                '2',
                $value,
                $dataFimMes,
                $value,

                $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'],
                $value,
                $dataFimMes,
                $value
            ]);
        }
    }

    /* =======================
   FINAL
======================= */

    $sql .= "
        ) tabelaUnion
        group by ug_cpf, ug_nome, ug_id, ug_email, tipo
        order by tipo, ug_id
";

    /* =======================
   EXECU��O
======================= */

    $rs_dados = SQLexecuteQueryParams($sql, $params);
    //echo pg_num_rows($rs_dados)."<br>";
    if (!$rs_dados) {
        echo "Erro na Query de Levantamento de CPFs e/ou Nome Preenchidos para os Publishers (" . implode(",", $vetorPublisher) . ") e Publishers Novos (" . (is_array($vetorPublisherNovos) ? implode(",", $vetorPublisherNovos) : "") . ").<br>" . PHP_EOL;
        return false;
    }
    if ((($rs_dados) ? pg_num_rows($rs_dados) : 0) == 0) {
        //echo "Vai retorna Falso. Ou seja, NO possui dados faltantes.<br>";
        return false;
    } //end if(!$rs_dados || pg_num_rows($rs_dados) == 0)
    else {
        //echo "Vai retorna verdadeiro. Ou seja, possui dados faltantes.<br>";
        return true;
    } //end else

} //end function verificaCPFValido

function verificaCPF_BACEN(mixed $cpf): bool|int
{
    $cpf = preg_replace('/[^0-9]/', '', (string)$cpf);

    $RecebeCPF = $cpf;

    if (strlen($RecebeCPF) != 11) {
        return 0;
    } else
		if ($RecebeCPF == "00000000000" || $RecebeCPF == "11111111111") {
        return 0;
    } else {
        $Numero[1] = intval(substr($RecebeCPF, 1 - 1, 1));
        $Numero[2] = intval(substr($RecebeCPF, 2 - 1, 1));
        $Numero[3] = intval(substr($RecebeCPF, 3 - 1, 1));
        $Numero[4] = intval(substr($RecebeCPF, 4 - 1, 1));
        $Numero[5] = intval(substr($RecebeCPF, 5 - 1, 1));
        $Numero[6] = intval(substr($RecebeCPF, 6 - 1, 1));
        $Numero[7] = intval(substr($RecebeCPF, 7 - 1, 1));
        $Numero[8] = intval(substr($RecebeCPF, 8 - 1, 1));
        $Numero[9] = intval(substr($RecebeCPF, 9 - 1, 1));
        $Numero[10] = intval(substr($RecebeCPF, 10 - 1, 1));
        $Numero[11] = intval(substr($RecebeCPF, 11 - 1, 1));

        $soma = 10 * $Numero[1] + 9 * $Numero[2] + 8 * $Numero[3] + 7 * $Numero[4] + 6 * $Numero[5] + 5 *
            $Numero[6] + 4 * $Numero[7] + 3 * $Numero[8] + 2 * $Numero[9];
        $soma = $soma - (11 * (intval($soma / 11)));

        if ($soma == 0 || $soma == 1) {
            $resultado1 = 0;
        } else {
            $resultado1 = 11 - $soma;
        }

        if ($resultado1 == $Numero[10]) {
            $soma = $Numero[1] * 11 + $Numero[2] * 10 + $Numero[3] * 9 + $Numero[4] * 8 + $Numero[5] * 7 + $Numero[6] * 6 + $Numero[7] * 5 +
                $Numero[8] * 4 + $Numero[9] * 3 + $Numero[10] * 2;
            $soma = $soma - (11 * (intval($soma / 11)));

            if ($soma == 0 || $soma == 1) {
                $resultado2 = 0;
            } else {
                $resultado2 = 11 - $soma;
            }
            if ($resultado2 == $Numero[11]) {
                return TRUE;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
} //end function verificaCPF_BACEN


function levantamentoPublisherOperantes(mixed $ano, mixed $mes, bool $variado = false): array
{

    // Buscando informaes 
    $params = [];

    /* =======================
   DATA FINAL
======================= */

    $dataFimMes = "$ano-$mes-" . date("t", mktime(0, 0, 0, $mes, 1, $ano)) . " 00:00:00";

    /* =======================
   QUERY
======================= */

    $sql = "select 
            opr_codigo,
            opr_nome
        from operadoras
        where opr_vinculo_empresa = ?
          and opr_data_inicio_operacoes is not null
          and opr_data_inicio_operacoes <= ?
          and opr_internacional_alicota != 0
          and opr_status = ?
          and opr_ja_contabilizou = ?";

    /* =======================
   PARAMS BASE
======================= */

    $params = [
        $GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO'],
        $dataFimMes,
        '1',
        $GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']
    ];

    /* =======================
   CONDI��O OPCIONAL
======================= */

    if ($variado) {
        $sql .= " and opr_cotacao_dolar = ?";
        $params[] = 1;
    }

    /* =======================
   FINAL
======================= */

    $sql .= " order by opr_nome";

    /* =======================
   EXECU��O
======================= */

    $rs_operadoras_operantes = SQLexecuteQueryParams($sql, $params);

    $aux_retorno = [];

    //echo pg_num_rows($rs_operadoras_operantes)."<br>";
    if (!$rs_operadoras_operantes) {
        echo "Erro na Query de Levantamento de Publishers INTERNacionais já em operação(" . $sql . ").<br>" . PHP_EOL;
        return array(0);
    }
    if ((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0) {
        echo "<b>Nenhum Publisher INTERNacional foi considerado na elaboração de arquivos de Complice BACEN em mêses anteriores</b><br><br>" . PHP_EOL;
        return array(0);
    } //end if((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0)
    else {
        echo "<b>Publishers INTERNacionais que já foram considerados na elaboração de arquivos de Complice BACEN em mêses anteriores:</b><br><br>" . PHP_EOL;
        while ($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
            $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
            echo " ID [" . $rs_operadoras_operantes_row['opr_codigo'] . "] => [" . $rs_operadoras_operantes_row['opr_nome'] . "]<br>" . PHP_EOL;
        } //end while
        echo "<br><br>" . PHP_EOL;
        return $aux_retorno;
    } //end else

} //end function levantamentoPublisherOperantes()


function levantamentoPublisherOperantesNacionais(mixed $ano, mixed $mes): array
{

    // Buscando informaes 
    $params = [];

    /* =======================
   DATA FINAL
======================= */

    $dataFimMes = "$ano-$mes-" . date("t", mktime(0, 0, 0, $mes, 1, $ano)) . " 00:00:00";

    /* =======================
   QUERY
======================= */

    $sql = "select 
            opr_codigo,
            opr_nome
        from operadoras
        where opr_vinculo_empresa = ?
          and opr_data_inicio_operacoes is not null
          and opr_data_inicio_operacoes <= ?
          and opr_internacional_alicota = 0
          and opr_status != ?
          and opr_ja_contabilizou = ?
        order by opr_nome";

    /* =======================
   PARAMS
======================= */

    $params = [
        $GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO'],
        $dataFimMes,
        '0',
        $GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']
    ];

    /* =======================
   EXECU��O
======================= */

    $rs_operadoras_operantes = SQLexecuteQueryParams($sql, $params);

    $aux_retorno = [];

    //echo pg_num_rows($rs_operadoras_operantes)."<br>";
    if (!$rs_operadoras_operantes) {
        echo "Erro na Query de Levantamento de Publishers já em operação NACIONAIS(" . $sql . ").<br>" . PHP_EOL;
        return array(0);
    }
    if ((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0) {
        echo "<b>Nenhum Publishers Nacional foi considerado na elaboração de arquivos de Complice BACEN em mêses anteriores</b><br><br>" . PHP_EOL;
        return array(0);
    } //end if((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0)
    else {
        echo "<b>Publishers NACIONAIS que já foram considerados na elaboração de arquivos de Complice BACEN em mêses anteriores:</b><br><br>" . PHP_EOL;
        while ($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
            $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
            echo " ID [" . $rs_operadoras_operantes_row['opr_codigo'] . "] => [" . $rs_operadoras_operantes_row['opr_nome'] . "]<br>" . PHP_EOL;
        } //end while
        echo "<br><br>" . PHP_EOL;
        return $aux_retorno;
    } //end else

} //end function levantamentoPublisherOperantesNacionais()


function levantamentoPublisherOperantesMunicipais(mixed $ano, mixed $mes): array
{

    $params = [];

    /* =======================
   DATA FINAL
======================= */

    $dataFimMes = "$ano-$mes-" . date("t", mktime(0, 0, 0, $mes, 1, $ano)) . " 00:00:00";

    /* =======================
   QUERY
======================= */

    $sql = "select
            opr_codigo,
            opr_nome
        from operadoras
        where opr_vinculo_empresa = ?
          and opr_data_inicio_operacoes is not null
          and opr_data_inicio_operacoes <= ?
          and opr_internacional_alicota = 0
          and opr_status != ?
          and UPPER(opr_estado) = ?
          and TRIM(opr_cidade) ilike ?
          and opr_ja_contabilizou = ?
        order by opr_nome";

    /* =======================
   PARAMS
======================= */

    $params = [
        $GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO'],
        $dataFimMes,
        '0',
        'SP',
        's%o Paulo',
        $GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']
    ];

    /* =======================
   EXECU��O
======================= */

    $rs_operadoras_operantes = SQLexecuteQueryParams($sql, $params);

    $aux_retorno = [];

    //echo pg_num_rows($rs_operadoras_operantes)."<br>";
    if (!$rs_operadoras_operantes) {
        echo "Erro na Query de Levantamento de Publishers já em operação(" . $sql . ").<br>" . PHP_EOL;
        return array(0);
    }
    if ((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0) {
        echo "<b>Nenhum Publishers foi considerado na elaboração de arquivos de Complice Municipal em mêses anteriores</b><br><br>" . PHP_EOL;
        return array(0);
    } //end if((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0)
    else {
        echo "<b>Publishers que já foram considerados na elaboração de arquivos de Complice Municipal em mêses anteriores:</b><br><br>" . PHP_EOL;
        while ($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {


            $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
            echo " ID [" . $rs_operadoras_operantes_row['opr_codigo'] . "] => [" . $rs_operadoras_operantes_row['opr_nome'] . "]<br>" . PHP_EOL;
        } //end while
        echo "<br><br>" . PHP_EOL;
        return $aux_retorno;
    } //end else

} //end function levantamentoPublisherOperantesMunicipais()


function levantamentoPublisherNovosOperantes(mixed $ano, mixed $mes, bool $variado = false): array
{

    // Buscando informaes 
    $params = [];

    /* =======================
   DATA FINAL
======================= */

    $dataFimMes = "$ano-$mes-" . date("t", mktime(0, 0, 0, $mes, 1, $ano)) . " 00:00:00";

    /* =======================
   QUERY
======================= */

    $sql = "select
            opr_codigo,
            opr_nome,
            to_char(opr_data_inicio_operacoes,'DD/MM/YYYY') as data_inicio
        from operadoras
        where opr_vinculo_empresa = ?
          and opr_data_inicio_operacoes is not null
          and opr_data_inicio_operacoes <= ?
          and opr_internacional_alicota != 0
          and opr_status = ?
          and opr_ja_contabilizou != ?";

    /* =======================
   PARAMS BASE
======================= */

    $params[] = $GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO'];
    $params[] = $dataFimMes;
    $params[] = '1';
    $params[] = $GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU'];

    /* =======================
   CONDI��O OPCIONAL
======================= */

    if ($variado) {
        $sql .= " and opr_cotacao_dolar = ?";
        $params[] = 1;
    }

    /* =======================
   ORDER BY
======================= */

    $sql .= " order by opr_nome";

    /* =======================
   EXECU��O
======================= */

    $rs_operadoras_operantes = SQLexecuteQueryParams($sql, $params);

    $aux_retorno = [];

    //echo pg_num_rows($rs_operadoras_operantes)."<br>";
    if (!$rs_operadoras_operantes) {
        echo "Erro na Query de Levantamento de Publishers INTERNacionais NOVO(" . $sql . ").<br>" . PHP_EOL;
        return array(0);
    }
    if ((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0) {
        echo "<b>Nenhum Publisher INTERNacional NOVO iniciou operações no Mês Anterior</b><br><br>" . PHP_EOL;
        return array(0);
    } //end if((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0)
    else {
        echo "<b>Publishers INTERNacionais NOVOs que serão considerados na elaboração de arquivos:</b><br><br>" . PHP_EOL;
        while ($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {


            $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
            echo " ID [" . $rs_operadoras_operantes_row['opr_codigo'] . "] => [" . $rs_operadoras_operantes_row['opr_nome'] . "] => Data Início das Operações [<b style='color: red'>" . $rs_operadoras_operantes_row['data_inicio'] . "</b>]<br>" . PHP_EOL;
        } //end while
        echo "<br><br>" . PHP_EOL;
        return $aux_retorno;
    } //end else

} //end function levantamentoPublisherNovosOperantes()


function levantamentoPublisherNovosOperantesNacionais(mixed $ano, mixed $mes): array
{

    // Buscando informaes 
    $dataFinalMes = date(
        'Y-m-t 00:00:00',
        mktime(0, 0, 0, (int)$mes, 1, (int)$ano)
    );

    $sql = "
    SELECT 
        opr_codigo,
        opr_nome,
        to_char(opr_data_inicio_operacoes,'DD/MM/YYYY') AS data_inicio
    FROM operadoras
    WHERE 
        opr_vinculo_empresa = :empresa
        AND opr_data_inicio_operacoes IS NOT NULL
        AND opr_data_inicio_operacoes <= :data_final_mes
        AND opr_internacional_alicota = 0
        AND opr_ja_contabilizou != :status_contabilizou
    ORDER BY opr_nome
";

    $params = [
        ':empresa'              => $GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO'],
        ':data_final_mes'       => $dataFinalMes,
        ':status_contabilizou'  => $GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU'],
    ];

    $rs_operadoras_operantes = SQLexecuteQueryParams($sql, $params);

    $aux_retorno = [];

    //echo pg_num_rows($rs_operadoras_operantes)."<br>";
    if (!$rs_operadoras_operantes) {
        echo "Erro na Query de Levantamento de Publishers NACIONAIS NOVO(" . $sql . ").<br>" . PHP_EOL;
        return array(0);
    }
    if ((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0) {
        echo "<b>Nenhum Publisher NACIONAL NOVO iniciou operações no Mês Anterior</b><br><br>" . PHP_EOL;
        return array(0);
    } //end if((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0)
    else {
        echo "<b>Publishers NACIONAIS NOVOs que serão considerados na elaboração de arquivos:</b><br><br>" . PHP_EOL;
        while ($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {


            $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
            echo " ID [" . $rs_operadoras_operantes_row['opr_codigo'] . "] => [" . $rs_operadoras_operantes_row['opr_nome'] . "] => Data Início das Operações [<b style='color: red'>" . $rs_operadoras_operantes_row['data_inicio'] . "</b>]<br>" . PHP_EOL;
        } //end while
        echo "<br><br>" . PHP_EOL;
        return $aux_retorno;
    } //end else

} //end function levantamentoPublisherNovosOperantesNacionais()


function levantamentoPublisherNovosOperantesMunicipais(mixed $ano, mixed $mes): array
{

    // Buscando informaes 
    $dataFinalMes = date(
        'Y-m-t 00:00:00',
        mktime(0, 0, 0, (int)$mes, 1, (int)$ano)
    );

    $sql = "
    SELECT 
        opr_codigo,
        opr_nome,
        to_char(opr_data_inicio_operacoes,'DD/MM/YYYY') AS data_inicio
    FROM operadoras
    WHERE 
        opr_vinculo_empresa = :empresa
        AND opr_data_inicio_operacoes IS NOT NULL
        AND opr_data_inicio_operacoes <= :data_final_mes
        AND opr_internacional_alicota = 0
        AND UPPER(opr_estado) = :estado
        AND TRIM(opr_cidade) ILIKE :cidade
        AND opr_ja_contabilizou != :status_contabilizou
    ORDER BY opr_nome
";

    $params = [
        'empresa'             => $GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO'],
        'data_final_mes'      => $dataFinalMes,
        'estado'              => 'SP',
        'cidade'              => 's%o Paulo',
        'status_contabilizou' => $GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU'],
    ];

    $rs_operadoras_operantes = SQLexecuteQueryParams($sql, $params);

    $aux_retorno = [];

    //echo pg_num_rows($rs_operadoras_operantes)."<br>";
    if (!$rs_operadoras_operantes) {
        echo "Erro na Query de Levantamento de Publishers já em operação(" . $sql . ").<br>" . PHP_EOL;
        return array(0);
    }
    if ((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0) {
        echo "<b>Nenhum Publishers NOVO iniciou operações no Mês Anterior</b><br><br>" . PHP_EOL;
        return array(0);
    } //end if((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0)
    else {
        echo "<b>Publishers NOVOs que serão considerados na elaboração de arquivos:</b><br><br>" . PHP_EOL;
        while ($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {


            $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
            echo " ID [" . $rs_operadoras_operantes_row['opr_codigo'] . "] => [" . $rs_operadoras_operantes_row['opr_nome'] . "] => Data Início das Operações [<b style='color: red'>" . $rs_operadoras_operantes_row['data_inicio'] . "</b>]<br>" . PHP_EOL;
        } //end while
        echo "<br><br>" . PHP_EOL;
        return $aux_retorno;
    } //end else

} //end function levantamentoPublisherNovosOperantesMunicipais()


function alteracaoPublisherNovosJaArquivoBACEN(array $vetorPublisherNovos): bool
{

    // Buscando informaes 
    $sql = "
    UPDATE operadoras
    SET opr_ja_contabilizou = :status_aguardando
    WHERE 
        opr_vinculo_empresa = :empresa
        AND opr_data_inicio_operacoes IS NOT NULL
        AND opr_ja_contabilizou != :status_contabilizou
        AND opr_internacional_alicota != 0
        AND opr_codigo IN (" . implode(',', array_fill(0, count($vetorPublisherNovos), '?')) . ")
";

    $params = array_merge(
        [
            'status_aguardando' => $GLOBALS['STATUS_ARQUIVO_BACEN']['AGUARDANDO_RETORNO_BACEN'],
            'empresa'           => $GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO'],
            'status_contabilizou' => $GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU'],
        ],
        $vetorPublisherNovos
    );

    $rs_update = SQLexecuteQueryParams($sql, $params);
    if (!$rs_update) {
        echo "Erro na Query de Alteração de Publishers para já em arquivo do BACEN (" . $sql . ").<br>" . PHP_EOL;
        return false;
    }
    if (pg_affected_rows($rs_update) === 0) {
        echo "<b>Nenhum Publishers NOVO foi alterado para já em arquivo do BACEN</b><br><br>" . PHP_EOL;
        return false;
    } //end if((($rs_update) ? pg_num_rows($rs_update) : 0) == 0)
    else {
        echo "<b>Publishers NOVOs foram alterados para já em arquivo do BACEN [" . implode(",", $vetorPublisherNovos) . "]</b><br><br>" . PHP_EOL;
        return true;
    } //end else

} //end function alteracaoPublisherNovosJaArquivoBACEN()

function alteracaoPublisherNovosJaArquivoMunicipais(array $vetorPublisherNovos): bool
{

    // Buscando informaes 
    $sql = "
    UPDATE operadoras
    SET opr_ja_contabilizou = :status_aguardando
    WHERE 
        opr_vinculo_empresa = :empresa
        AND opr_data_inicio_operacoes IS NOT NULL
        AND opr_ja_contabilizou != :status_contabilizou
        AND opr_internacional_alicota = 0
        AND UPPER(opr_estado) = 'SP'
        AND TRIM(opr_cidade) ILIKE 's%o Paulo'
        AND opr_codigo IN (" . implode(',', array_fill(0, count($vetorPublisherNovos), '?')) . ")
";

    $params = array_merge(
        [
            'status_aguardando'  => $GLOBALS['STATUS_ARQUIVO_BACEN']['AGUARDANDO_RETORNO_BACEN'],
            'empresa'            => $GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO'],
            'status_contabilizou' => $GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU'],
        ],
        $vetorPublisherNovos
    );

    $rs_update = SQLexecuteQueryParams($sql, $params);
    if (!$rs_update) {
        echo "Erro na Query de Alteração de Publishers para já em arquivo para Prefeitura (" . $sql . ").<br>" . PHP_EOL;
        return false;
    }
    if (pg_affected_rows($rs_update) === 0) {
        echo "<b>Nenhum Publishers NOVO foi alterado para já em arquivo para Prefeitura</b><br><br>" . PHP_EOL;
        return false;
    } //end if((($rs_update) ? pg_num_rows($rs_update) : 0) == 0)
    else {
        echo "<b>Publishers NOVOs foram alterados para já em arquivo para Prefeitura [" . implode(",", $vetorPublisherNovos) . "]</b><br><br>" . PHP_EOL;
        return true;
    } //end else

} //end function alteracaoPublisherNovosJaArquivoMunicipais()


function levantamentoPublisherEppPagamentosFacilitadora(mixed $ano, mixed $mes, bool $variado = false): array
{

    // Buscando informaes 
    $data_limite = $ano . "-" . $mes . "-" . date("t", (int)mktime(0, 0, 0, (int)$mes, 1, (int)$ano)) . " 00:00:00";

    $sql = "
    SELECT 
        opr_codigo, 
        opr_nome
    FROM operadoras
    WHERE 
        opr_vinculo_empresa = :empresa
        AND opr_data_inicio_operacoes IS NOT NULL
        AND opr_data_inicio_operacoes <= :data_limite
        AND (opr_internacional_alicota = :aliquota_padrao OR opr_internacional_alicota = :iof)
        AND opr_status = '1'
";

    if ($variado) {
        $sql .= " AND opr_cotacao_dolar = 1 ";
    }

    $sql .= " ORDER BY opr_nome";

    $params = [
        'empresa'          => $GLOBALS['IDENTIFICACAO_EMPRESA_PAGAMENTOS'],
        'data_limite'      => $data_limite,
        'aliquota_padrao'  => 0.38,
        'iof'              => IOF,
    ];

    $rs_operadoras_operantes = SQLexecuteQueryParams($sql, $params);

    $aux_retorno = [];

    //echo pg_num_rows($rs_operadoras_operantes)."<br>";
    if (!$rs_operadoras_operantes) {
        echo "Erro na Query de Levantamento de Publishers Epp Pagamentos Facilitadora já em operação(" . $sql . ").<br>" . PHP_EOL;
        return array(0);
    }
    if ((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0) {
        echo "<b>Nenhum Publisher Epp Pagamentos Facilitadora foi considerado em mêses anteriores</b><br><br>" . PHP_EOL;
        return array(0);
    } //end if((($rs_operadoras_operantes) ? pg_num_rows($rs_operadoras_operantes) : 0) == 0)
    else {
        echo "<b>Publishers Epp Pagamentos Facilitadora:</b><br><br>" . PHP_EOL;
        while ($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {


            $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
            echo " ID [" . $rs_operadoras_operantes_row['opr_codigo'] . "] => [" . $rs_operadoras_operantes_row['opr_nome'] . "]<br>" . PHP_EOL;
        } //end while
        echo "<br><br>" . PHP_EOL;
        return $aux_retorno;
    } //end else

} //end function levantamentoPublisherEppPagamentosFacilitadora()



function trimestre(mixed $mes = null): float|int
{
    $mes = is_null($mes) ? date('m') : $mes;
    $trim = floor(((int)$mes - 1) / 3) + 1;
    return $trim;
} //end function trimestre($mes=null)

function semestre(mixed $mes = null): float|int
{
    $mes = is_null($mes) ? date('m') : $mes;
    $trim = floor(((int)$mes - 1) / 6) + 1;
    return $trim;
} //end function semestre($mes=null)

function isTrimestral(mixed $mes): bool
{
    $mesesFechamentoTrimenstral = array(3, 6, 9, 12);

    if (in_array((int)$mes, $mesesFechamentoTrimenstral)) {
        return true;
    }
    return false;
} //end function isTrimestral($mes)

function isSemestral(mixed $mes): bool
{
    $mesesFechamentoSemenstral = array(6, 12);

    if (in_array((int)$mes, $mesesFechamentoSemenstral)) {
        return true;
    }
    return false;
} //end function isSemestral($mes)


function getStartDateTrimestral(mixed $mes, mixed $ano): string
{
    global $dataInicioOperacao, $testeData;
    $date = "";
    $trimestreAux = trimestre($mes);
    switch ($trimestreAux) {
        case 1:
            if ($testeData == $dataInicioOperacao) {
                $date = $ano . "-" . $mes . "-01";
            } //end if($testeData == $dataInicioOperacao)
            else {
                $date = $ano . "-01-01";
            } //end else do if($testeData == $dataInicioOperacao)
            break;
        case 2:
            $date = $ano . "-04-01";
            break;
        case 3:
            $date = $ano . "-07-01";
            break;
        case 4:
            $date = $ano . "-10-01";
            break;
    } //end switch
    return $date;
} //end function getStartDateTrimestral($mes,$ano)

function getEndDateTrimestral(mixed $mes, mixed $ano): string
{
    $date = "";
    $trimestreAux = trimestre($mes);
    switch ($trimestreAux) {
        case 1:
            $date = $ano . "-03-31";
            break;
        case 2:
            $date = $ano . "-06-30";
            break;
        case 3:
            $date = $ano . "-09-30";
            break;
        case 4:
            $date = $ano . "-12-31";
            break;
    } //end switch
    return $date;
} //end function getEndDateTrimestral($mes,$ano)

function getStartDateSemestral(mixed $mes, mixed $ano): string
{
    global $dataInicioOperacao, $testeData;
    $date = "";
    $trimestreAux = semestre($mes);
    switch ($trimestreAux) {
        case 1:
            if ($testeData == $dataInicioOperacao) {
                $date = $ano . "-" . $mes . "-01";
            } //end if($testeData == $dataInicioOperacao)
            else {
                $date = $ano . "-01-01";
            } //end else do if($testeData == $dataInicioOperacao)
            break;
        case 2:
            $date = $ano . "-07-01";
            break;
    } //end switch
    return $date;
} //end function getStartDateSemestral($mes,$ano)

function getEndDateSemestral(mixed $mes, mixed $ano): string
{
    $date = "";
    $trimestreAux = semestre($mes);
    switch ($trimestreAux) {
        case 1:
            $date = $ano . "-06-30";
            break;
        case 2:
            $date = $ano . "-12-31";
            break;
    } //end switch
    return $date;
} //end function getEndDateSemestral($mes,$ano)

function verificaLimiteDetalhamento(mixed $limite, mixed &$rs): bool
{
    // A varivel limite deve ser informada em DOLAR (USS). Ex.: $limite = 1000 significa $USS 1,000

    /* Calculando individualmente por publisher(PONDERAADA)
    // Calculado a Cotao Mdia
    $mediaCotacao = 0;
    foreach ($GLOBALS['vetorCotacaoUSS'] as $key => $value) {
        //echo $value."*<br>";
        $mediaCotacao += $value;
    }//end foreach
    $mediaCotacao = $mediaCotacao/count($GLOBALS['vetorCotacaoUSS']);
    //echo "[$mediaCotacao]<br>";
    */

    // Selecionando os usuarios que ultrapassaram o Limite
    $params = [];

    $data_inicio_mes = $GLOBALS['ano'] . "-" . $GLOBALS['mes'] . "-01 00:00:00";
    $data_fim_mes    = $GLOBALS['ano'] . "-" . $GLOBALS['mes'] . "-" .
        date("t", (int)mktime(0, 0, 0, (int)$GLOBALS['mes'], 1, (int)$GLOBALS['ano'])) . " 23:59:59";

    $sql = "
    SELECT 
        ug_cpf,
        SUM(n) AS qtde,
        SUM(total) AS total_geral
    FROM (
";

    $insere_union_all = 1;
    $i = 0;

    foreach ($GLOBALS['vetorPublisher'] as $key => $value) {

        $i++;

        if ($insere_union_all > 1) {
            $sql .= " UNION ALL ";
        }

        $params["status_$i"]        = $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'];
        $params["data_ini_$i"]      = $data_inicio_mes;
        $params["data_fim_$i"]      = $data_fim_mes;
        $params["money_user_$i"]    = $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'];
        $params["opr_$i"]           = $value;
        $params["cotacao_$i"]       = $GLOBALS['vetorCotacaoUSS'][$value];

        $sql .= "
        (
            SELECT 
                ug_cpf,
                SUM(vgm.vgm_qtde) AS n,
                SUM(vgm.vgm_valor * vgm.vgm_qtde)/:cotacao_$i AS total
            FROM tb_venda_games vg
                INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
                INNER JOIN usuarios_games ug ON ug.ug_id = vg.vg_ug_id
            WHERE vg.vg_ultimo_status = :status_$i
                AND vg.vg_data_concilia BETWEEN :data_ini_$i AND :data_fim_$i
                AND vg.vg_ug_id != :money_user_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY ug_cpf
        )

        UNION ALL

        (
            SELECT 
                vgm_cpf AS ug_cpf,
                SUM(vgm.vgm_qtde) AS n,
                SUM(vgm.vgm_valor * vgm.vgm_qtde)/:cotacao_$i AS total
            FROM tb_dist_venda_games vg
                INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
            WHERE vg.vg_ultimo_status = :status_$i
                AND vg.vg_data_inclusao BETWEEN :data_ini_$i AND :data_fim_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY vgm_cpf
        )

        UNION ALL

        (
            SELECT 
                picc_cpf AS ug_cpf,
                COUNT(*) AS n,
                SUM(pih_pin_valor/100)/:cotacao_$i AS total
            FROM pins_integracao_card_historico
                LEFT JOIN pins_integracao_card_cpf ON pin_codinterno = pih_pin_id
            WHERE pin_status = '4'
                AND pih_codretepp = '2'
                AND pih_data BETWEEN :data_ini_$i AND :data_fim_$i
                AND pih_id = :opr_$i
            GROUP BY picc_cpf
        )

        UNION ALL

        (
            SELECT 
                vgcbe_cpf AS ug_cpf,
                COUNT(*) AS n,
                SUM(vgm_valor * vgm_qtde)/:cotacao_$i AS total
            FROM tb_venda_games_cpf_boleto_express
                INNER JOIN tb_venda_games ON vg_id = vgcbe_vg_id
                INNER JOIN tb_venda_games_modelo ON vgm_vg_id = vg_id
            WHERE vg_ultimo_status = :status_$i
                AND vgcbe_data_inclusao BETWEEN :data_ini_$i AND :data_fim_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY vgcbe_cpf
        )
    ";

        $insere_union_all++;
    }

    if (!empty($GLOBALS['verificadorPublishersNovos'])) {

        foreach ($GLOBALS['vetorPublisherNovos'] as $key => $value) {

            $i++;

            $params["status_$i"]     = $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'];
            $params["data_fim_$i"]   = $data_fim_mes;
            $params["money_user_$i"] = $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'];
            $params["opr_$i"]        = $value;
            $params["cotacao_$i"]    = $GLOBALS['vetorCotacaoUSS'][$value];

            $sql .= "

        UNION ALL

        (
            SELECT 
                ug_cpf,
                SUM(vgm.vgm_qtde) AS n,
                SUM(vgm.vgm_valor * vgm.vgm_qtde)/:cotacao_$i AS total
            FROM tb_venda_games vg
                INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
                INNER JOIN usuarios_games ug ON ug.ug_id = vg.vg_ug_id
            WHERE vg.vg_ultimo_status = :status_$i
                AND vg.vg_data_concilia >= (
                    SELECT opr_data_inicio_operacoes 
                    FROM operadoras 
                    WHERE opr_codigo = :opr_$i
                )
                AND vg.vg_data_concilia <= :data_fim_$i
                AND vg.vg_ug_id != :money_user_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY ug_cpf
        )

        UNION ALL

        (
            SELECT 
                vgm_cpf AS ug_cpf,
                SUM(vgm.vgm_qtde) AS n,
                SUM(vgm.vgm_valor * vgm.vgm_qtde)/:cotacao_$i AS total
            FROM tb_dist_venda_games vg
                INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
            WHERE vg.vg_ultimo_status = :status_$i
                AND vg.vg_data_inclusao >= (
                    SELECT opr_data_inicio_operacoes 
                    FROM operadoras 
                    WHERE opr_codigo = :opr_$i
                )
                AND vg.vg_data_inclusao <= :data_fim_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY vgm_cpf
        )

        UNION ALL

        (
            SELECT 
                picc_cpf AS ug_cpf,
                COUNT(*) AS n,
                SUM(pih_pin_valor/100)/:cotacao_$i AS total
            FROM pins_integracao_card_historico
                LEFT JOIN pins_integracao_card_cpf ON pin_codinterno = pih_pin_id
            WHERE pin_status = '4'
                AND pih_codretepp = '2'
                AND pih_data >= (
                    SELECT opr_data_inicio_operacoes 
                    FROM operadoras 
                    WHERE opr_codigo = :opr_$i
                )
                AND pih_data <= :data_fim_$i
                AND pih_id = :opr_$i
            GROUP BY picc_cpf
        )

        UNION ALL

        (
            SELECT 
                vgcbe_cpf AS ug_cpf,
                COUNT(*) AS n,
                SUM(vgm_valor * vgm_qtde)/:cotacao_$i AS total
            FROM tb_venda_games_cpf_boleto_express
                INNER JOIN tb_venda_games ON vg_id = vgcbe_vg_id
                INNER JOIN tb_venda_games_modelo ON vgm_vg_id = vg_id
            WHERE vg_ultimo_status = :status_$i
                AND vgcbe_data_inclusao >= (
                    SELECT opr_data_inicio_operacoes 
                    FROM operadoras 
                    WHERE opr_codigo = :opr_$i
                )
                AND vgcbe_data_inclusao <= :data_fim_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY vgcbe_cpf
        )
        ";
        }
    }

    $sql .= "
    ) tabelaUnion
    GROUP BY ug_cpf
    HAVING SUM(total) > :limite
    ORDER BY total_geral DESC
";

    $params['limite'] = $limite;

    $rs = SQLexecuteQueryParams($sql, $params);
    if (!$rs || pg_num_rows($rs) == 0) {
        return false;
    } //end if(!$rs || pg_num_rows($rs) == 0)
    else {
        return true;
    } //end else

} //end function verificaLimiteDetalhamento

function verificaLimiteCOAF(mixed $limite, mixed &$rs): bool
{
    // A varivel limite deve ser informada em REAIS (R$). Ex.: $limite = 1000 means R$ 1.000,00

    // Selecionando os usuarios que ultrapassaram o Limite
    $params = [];

    $data_inicio_mes = $GLOBALS['ano'] . "-" . $GLOBALS['mes'] . "-01 00:00:00";
    $data_fim_mes    = $GLOBALS['ano'] . "-" . $GLOBALS['mes'] . "-" .
        date("t", (int)mktime(0, 0, 0, (int)$GLOBALS['mes'], 1, (int)$GLOBALS['ano'])) . " 23:59:59";

    $sql = "
    SELECT 
        ug_cpf,
        SUM(n) AS qtde,
        SUM(total) AS total_geral
    FROM (
";

    $insere_union_all = 1;
    $i = 0;

    foreach ($GLOBALS['vetorPublisher'] as $key => $value) {

        $i++;

        if ($insere_union_all > 1) {
            $sql .= " UNION ALL ";
        }

        $params["status_$i"]      = $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'];
        $params["data_ini_$i"]    = $data_inicio_mes;
        $params["data_fim_$i"]    = $data_fim_mes;
        $params["money_user_$i"]  = $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'];
        $params["opr_$i"]         = $value;

        $sql .= "
        (
            SELECT 
                ug_cpf,
                SUM(vgm.vgm_qtde) AS n,
                SUM(vgm.vgm_valor * vgm.vgm_qtde) AS total
            FROM tb_venda_games vg
                INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
                INNER JOIN usuarios_games ug ON ug.ug_id = vg.vg_ug_id
            WHERE vg.vg_ultimo_status = :status_$i
                AND vg.vg_data_concilia BETWEEN :data_ini_$i AND :data_fim_$i
                AND vg.vg_ug_id != :money_user_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY ug_cpf
        )

        UNION ALL

        (
            SELECT 
                vgm_cpf AS ug_cpf,
                SUM(vgm.vgm_qtde) AS n,
                SUM(vgm.vgm_valor * vgm.vgm_qtde) AS total
            FROM tb_dist_venda_games vg
                INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
            WHERE vg.vg_ultimo_status = :status_$i
                AND vg.vg_data_inclusao BETWEEN :data_ini_$i AND :data_fim_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY vgm_cpf
        )

        UNION ALL

        (
            SELECT 
                picc_cpf AS ug_cpf,
                COUNT(*) AS n,
                SUM(pih_pin_valor/100) AS total
            FROM pins_integracao_card_historico
                LEFT JOIN pins_integracao_card_cpf ON pin_codinterno = pih_pin_id
            WHERE pin_status = '4'
                AND pih_codretepp = '2'
                AND pih_data BETWEEN :data_ini_$i AND :data_fim_$i
                AND pih_id = :opr_$i
            GROUP BY picc_cpf
        )

        UNION ALL

        (
            SELECT 
                vgcbe_cpf AS ug_cpf,
                COUNT(*) AS n,
                SUM(vgm_valor * vgm_qtde) AS total
            FROM tb_venda_games_cpf_boleto_express
                INNER JOIN tb_venda_games ON vg_id = vgcbe_vg_id
                INNER JOIN tb_venda_games_modelo ON vgm_vg_id = vg_id
            WHERE vg_ultimo_status = :status_$i
                AND vgcbe_data_inclusao BETWEEN :data_ini_$i AND :data_fim_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY vgcbe_cpf
        )
    ";

        $insere_union_all++;
    }

    if (!empty($GLOBALS['verificadorPublishersNovos'])) {

        foreach ($GLOBALS['vetorPublisherNovos'] as $key => $value) {

            $i++;

            $params["status_$i"]     = $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'];
            $params["data_fim_$i"]   = $data_fim_mes;
            $params["money_user_$i"] = $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'];
            $params["opr_$i"]        = $value;

            $sql .= "

        UNION ALL

        (
            SELECT 
                ug_cpf,
                SUM(vgm.vgm_qtde) AS n,
                SUM(vgm.vgm_valor * vgm.vgm_qtde) AS total
            FROM tb_venda_games vg
                INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
                INNER JOIN usuarios_games ug ON ug.ug_id = vg.vg_ug_id
            WHERE vg.vg_ultimo_status = :status_$i
                AND vg.vg_data_concilia >= (
                    SELECT opr_data_inicio_operacoes 
                    FROM operadoras 
                    WHERE opr_codigo = :opr_$i
                )
                AND vg.vg_data_concilia <= :data_fim_$i
                AND vg.vg_ug_id != :money_user_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY ug_cpf
        )

        UNION ALL

        (
            SELECT 
                vgm_cpf AS ug_cpf,
                SUM(vgm.vgm_qtde) AS n,
                SUM(vgm.vgm_valor * vgm.vgm_qtde) AS total
            FROM tb_dist_venda_games vg
                INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
            WHERE vg.vg_ultimo_status = :status_$i
                AND vg.vg_data_inclusao >= (
                    SELECT opr_data_inicio_operacoes 
                    FROM operadoras 
                    WHERE opr_codigo = :opr_$i
                )
                AND vg.vg_data_inclusao <= :data_fim_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY vgm_cpf
        )

        UNION ALL

        (
            SELECT 
                picc_cpf AS ug_cpf,
                COUNT(*) AS n,
                SUM(pih_pin_valor/100) AS total
            FROM pins_integracao_card_historico
                LEFT JOIN pins_integracao_card_cpf ON pin_codinterno = pih_pin_id
            WHERE pin_status = '4'
                AND pih_codretepp = '2'
                AND pih_data >= (
                    SELECT opr_data_inicio_operacoes 
                    FROM operadoras 
                    WHERE opr_codigo = :opr_$i
                )
                AND pih_data <= :data_fim_$i
                AND pih_id = :opr_$i
            GROUP BY picc_cpf
        )

        UNION ALL

        (
            SELECT 
                vgcbe_cpf AS ug_cpf,
                COUNT(*) AS n,
                SUM(vgm_valor * vgm_qtde) AS total
            FROM tb_venda_games_cpf_boleto_express
                INNER JOIN tb_venda_games ON vg_id = vgcbe_vg_id
                INNER JOIN tb_venda_games_modelo ON vgm_vg_id = vg_id
            WHERE vg_ultimo_status = :status_$i
                AND vgcbe_data_inclusao >= (
                    SELECT opr_data_inicio_operacoes 
                    FROM operadoras 
                    WHERE opr_codigo = :opr_$i
                )
                AND vgcbe_data_inclusao <= :data_fim_$i
                AND vgm_opr_codigo = :opr_$i
            GROUP BY vgcbe_cpf
        )
        ";
        }
    }

    $sql .= "
    ) tabelaUnion
    GROUP BY ug_cpf
    HAVING SUM(total) > :limite
    ORDER BY total_geral DESC
";

    $params['limite'] = $limite;

    $rs = SQLexecuteQueryParams($sql, $params);
    if (!$rs || pg_num_rows($rs) == 0) {
        return false;
    } //end if(!$rs || pg_num_rows($rs) == 0)
    else {
        return true;
    } //end else

} //end function verificaLimiteCOAF
function levantamentoPublisherObrigatorioCPF(array &$vetorPublisherLegenda): array
{
    // Buscando informaes 
    $sql = "
    SELECT 
        opr_codigo,
        opr_nome
    FROM operadoras
    WHERE
        opr_data_inicio_operacoes IS NOT NULL
        AND opr_need_cpf_lh = :need_cpf
        AND opr_status = :status
    ORDER BY opr_nome
";

    $params = [
        'need_cpf' => 1,
        'status'   => '1',
    ];

    $rs_operadoras_obrigatorio_cpf = SQLexecuteQueryParams($sql, $params);

    $aux_retorno = [];

    //echo pg_num_rows($rs_operadoras_obrigatorio_cpf)."<br>";
    if (!$rs_operadoras_obrigatorio_cpf) {
        echo "Erro na Query de Levantamento de Publishers Exigem CPF na operação(" . $sql . ").<br>" . PHP_EOL;
        return array(0);
    }
    if ((($rs_operadoras_obrigatorio_cpf) ? pg_num_rows($rs_operadoras_obrigatorio_cpf) : 0) == 0) {
        echo "<b>Nenhum Publisher que Exige CPF foi considerado no seleção(" . $sql . ").</b><br><br>" . PHP_EOL;
        return array(0);
    } //end if((($rs_operadoras_obrigatorio_cpf) ? pg_num_rows($rs_operadoras_obrigatorio_cpf) : 0) == 0)
    else {
        //echo "<b>Publishers que Exigem CPF como Obrigatrio:</b><br><br>".PHP_EOL;
        while ($rs_operadoras_obrigatorio_cpf_row = pg_fetch_array($rs_operadoras_obrigatorio_cpf)) {


            $aux_retorno[] = $rs_operadoras_obrigatorio_cpf_row['opr_codigo'];
            $vetorPublisherLegenda[$rs_operadoras_obrigatorio_cpf_row['opr_codigo']] = $rs_operadoras_obrigatorio_cpf_row['opr_nome'];
            //echo " ID [".$rs_operadoras_obrigatorio_cpf_row['opr_codigo']."] => [".$rs_operadoras_obrigatorio_cpf_row['opr_nome']."]<br>".PHP_EOL;
        } //end while
        //echo "<br><br>".PHP_EOL;
        return $aux_retorno;
    } //end else
} //end function levantamentoPublisherObrigatorioCPF()
