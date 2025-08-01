select
    canal,
    dia,
    publisher,
    sum(n) as n,
    sum(total_order) as total_order,
    round(cast(sum(total) as NUMERIC), 2) as total
from
    (
        (
            select
                'P' as canal,
                date_trunc ('day', ve_data_inclusao) as dia,
                CASE
                    WHEN ve_jogo = 'HB' THEN 16
                    WHEN ve_jogo = 'OG' THEN 13
                    WHEN ve_jogo = 'MU' THEN 34
                END as publisher,
                count(*) as n,
                count(
                    distinct (
                        '20' || to_char (ve_data_inclusao, 'YYMMDD') || lpad (cast(ve_id as text), 8, '0')
                    )
                ) as total_order,
                sum(ve_valor) as total
            from
                dist_vendas_pos
            where
                ve_data_inclusao >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
            group by
                dia,
                publisher
        )
        union all
        (
            select
                'P' as canal,
                date_trunc ('day', vg.vg_data_concilia) as dia,
                vgm_opr_codigo as publisher,
                sum(vgm.vgm_qtde) as n,
                count(
                    distinct (
                        '20' || to_char (vg.vg_data_concilia, 'YYMMDD') || lpad (cast(vg.vg_id as text), 8, '0')
                    )
                ) as total_order,
                sum(vgm.vgm_valor * vgm.vgm_qtde) as total
            from
                tb_venda_games vg
                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id
            where
                vg.vg_ultimo_status = '5'
                and vg.vg_data_concilia >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
                and SUBSTR (tvgpo.tvgpo_canal, 1, 1) = 'P'
                and vg.vg_pagto_tipo = 13
            group by
                dia,
                publisher
        )
        union all
        (
            select
                'P' as canal,
                date_trunc ('day', data_transacao) as dia,
                opr_codigo as publisher,
                count(*) as n,
                count(
                    distinct (
                        '20' || to_char (data_transacao, 'YYMMDD') || lpad (cast(id_transacao as text), 8, '0')
                    )
                ) as total_order,
                sum(valor) as total
            from
                pos_transacoes_ponto_certo
            where
                opr_codigo is not NULL
                and data_transacao >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
            group by
                dia,
                publisher
        )
        union all
        (
            select
                case
                    when vg.vg_ug_id = 7909 then 'E'
                    when vg.vg_ug_id != 7909 then 'M'
                end as canal,
                date_trunc ('day', vg.vg_data_concilia) as dia,
                vgm_opr_codigo as publisher,
                sum(vgm.vgm_qtde) as n,
                count(
                    distinct (
                        10 || to_char (vg.vg_data_concilia, 'YYMMDD') || lpad (cast(vg.vg_id as text), 8, '0')
                    )
                ) as total_order,
                sum(vgm.vgm_valor * vgm.vgm_qtde) as total
            from
                tb_venda_games vg
                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
            where
                vg.vg_ultimo_status = '5'
                and vg.vg_data_concilia >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
                and vg.vg_ultimo_status_obs like '%Pagamento via AtimoPay%'
                and vg.vg_pagto_tipo = 13
            group by
                dia,
                canal,
                publisher
        )
        union all
        (
            select
                case
                    when vg.vg_ug_id = '7909' then 'E'
                    when vg.vg_ug_id != '7909' then 'M'
                end as canal,
                date_trunc ('day', vg.vg_data_concilia) as dia,
                vgm_opr_codigo as publisher,
                sum(vgm.vgm_qtde) as n,
                count(
                    distinct (
                        '10' || to_char (vg.vg_data_concilia, 'YYMMDD') || lpad (cast(vg.vg_id as text), 8, '0')
                    )
                ) as total_order,
                sum(vgm.vgm_valor * vgm.vgm_qtde) as total
            from
                tb_venda_games vg
                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
            where
                vg.vg_ultimo_status = '5'
                and vg.vg_data_concilia >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
                and vg.vg_pagto_tipo != 13
            group by
                dia,
                canal,
                publisher
        )
        union all
        (
            select
                case
                    when vg.vg_ug_id = '7909' then 'E'
                    when vg.vg_ug_id != '7909' then 'M'
                end as canal,
                date_trunc ('day', vg.vg_data_concilia) as dia,
                vgm_opr_codigo as publisher,
                sum(vgm.vgm_qtde) as n,
                count(
                    distinct (
                        '10' || to_char (vg.vg_data_concilia, 'YYMMDD') || lpad (cast(vg.vg_id as text), 8, '0')
                    )
                ) as total_order,
                sum(vgm.vgm_valor * vgm.vgm_qtde) as total
            from
                tb_venda_games vg
                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id
            where
                vg.vg_ultimo_status = '5'
                and vg.vg_data_concilia >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
                and tvgpo.tvgpo_canal = 'G'
                and vg.vg_pagto_tipo = 13
            group by
                dia,
                canal,
                publisher
        )
        union all
        (
            select
                'L' as canal,
                date_trunc ('day', vg.vg_data_inclusao) as dia,
                vgm_opr_codigo as publisher,
                sum(vgm.vgm_qtde) as n,
                count(
                    distinct (
                        '10' || to_char (vg.vg_data_inclusao, 'YYMMDD') || lpad (cast(vg.vg_id as text), 8, '0')
                    )
                ) as total_order,
                sum(vgm.vgm_valor * vgm.vgm_qtde) as total
            from
                tb_dist_venda_games vg
                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
            where
                vg.vg_ultimo_status = '5'
                AND (
                    CASE
                        WHEN vgm.vgm_opr_codigo = 13 THEN vg.vg_data_inclusao < '2015-08-16 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 124 THEN vg.vg_data_inclusao < '2018-08-28 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 137 THEN vg.vg_data_inclusao < '2018-10-16 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 143 THEN vg.vg_data_inclusao < '2020-01-07 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 147 THEN vg.vg_data_inclusao < '2019-11-30 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 148 THEN vg.vg_data_inclusao < '2020-07-31 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 166 THEN vg.vg_data_inclusao < '2024-12-01 00:00:00'
                        ELSE vg.vg_data_inclusao > '2008-01-01 00:00:00'
                    END
                )
                and vg.vg_data_inclusao >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
            group by
                dia,
                publisher
        )
        union all
        (
            select
                'L' as canal,
                date_trunc ('day', pih_data) as dia,
                vgm_opr_codigo as publisher,
                count(*) as n,
                count(
                    distinct (
                        '20' || to_char (vg.vg_data_inclusao, 'YYMMDD') || lpad (cast(vg.vg_id as text), 8, '0')
                    )
                ) as total_order,
                sum(vgm.vgm_valor) as total
            from
                tb_dist_venda_games vg
                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id
                inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno
            where
                vg.vg_data_inclusao >= '2008-01-01 00:00:00'
                and vg.vg_ultimo_status = '5'
                and pin_status = '8'
                and pih_codretepp = '2'
                AND (
                    CASE
                        WHEN vgm.vgm_opr_codigo = 13 THEN vg.vg_data_inclusao >= '2015-08-16 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 124 THEN vg.vg_data_inclusao >= '2018-08-28 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 137 THEN vg.vg_data_inclusao >= '2018-10-16 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 143 THEN vg.vg_data_inclusao >= '2020-01-07 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 147 THEN vg.vg_data_inclusao >= '2019-11-30 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 148 THEN vg.vg_data_inclusao >= '2020-07-31 00:00:00'
                        WHEN vgm.vgm_opr_codigo = 166 THEN vg.vg_data_inclusao >= '2024-12-01
    00:00:00'
                        ELSE FALSE
                    END
                )
                and pih_data >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
                AND (
                    CASE
                        WHEN pih_id = 13 THEN pih_data >= '2015-08-16 00:00:00'
                        WHEN pih_id = 124 THEN pih_data >= '2018-08-28
    00:00:00'
                        WHEN pih_id = 137 THEN pih_data >= '2018-10-16 00:00:00'
                        WHEN pih_id = 143 THEN pih_data >= '2020-01-07
    00:00:00'
                        WHEN pih_id = 147 THEN pih_data >= '2019-11-30 00:00:00'
                        WHEN pih_id = 148 THEN pih_data >= '2020-07-31
    00:00:00'
                        WHEN pih_id = 166 THEN pih_data >= '2024-12-01 00:00:00'
                        ELSE FALSE
                    END
                )
            group by
                dia,
                publisher
        )
        union all
        (
            select
                'L' as canal,
                date_trunc ('day', vg.vg_data_concilia) as dia,
                vgm_opr_codigo as publisher,
                sum(vgm.vgm_qtde) as n,
                count(
                    distinct (
                        '20' || to_char (vg.vg_data_inclusao, 'YYMMDD') || lpad (cast(vg.vg_id as text), 8, '0')
                    )
                ) as total_order,
                sum(vgm.vgm_valor * vgm.vgm_qtde) as total
            from
                tb_venda_games vg
                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id
            where
                vg.vg_ultimo_status = '5'
                and vg.vg_data_concilia >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
                and tvgpo.tvgpo_canal = 'L'
                and vg.vg_pagto_tipo = 13
            group by
                dia,
                publisher
        )
        -- naun vai calcular os cartoes fisicos da webzen e ongame por conta da incoerencia de informações
        -- Contabilizando PINs GoCASH utilizado na loja como EPP CASH
        union all
        (
            select
                'C' as canal,
                date_trunc ('day', vg.vg_data_concilia) as dia,
                vgm_opr_codigo as publisher,
                sum(vgm.vgm_qtde) as n,
                count(
                    distinct (
                        '20' || to_char (vg.vg_data_concilia, 'YYMMDD') || lpad (cast(vg.vg_id as text), 8, '0')
                    )
                ) as total_order,
                sum(vgm.vgm_valor * vgm.vgm_qtde) as total
            from
                tb_venda_games vg
                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id
            where
                vg.vg_ultimo_status = '5'
                and vg.vg_data_concilia >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
                and tvgpo.tvgpo_canal = 'C'
                and vg.vg_pagto_tipo = 13
            group by
                dia,
                publisher
        )
        -- Contabilizando PINs GiftCards utilizados por Integração
        union all
        (
            select
                'C' as canal,
                date_trunc ('day', pih_data) as dia,
                pih_id as publisher,
                count(*) as n,
                count(
                    distinct (
                        '30' || to_char (pih_data, 'YYMMDD') || lpad (cast(pih_pin_id as text), 8, '0')
                    )
                ) as total_order,
                sum(pih_pin_valor / 100) as total
            from
                pins_integracao_card_historico
            where
                pin_status = '4'
                and pih_codretepp = '2'
                and pih_data >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
            group by
                dia,
                publisher
        )
        -- Contabilizando PINs GoCASH utilizado por Integração de Utilização
        union all
        (
            select
                'C' as canal,
                date_trunc ('day', pgc_pin_response_date) as dia,
                pgc_opr_codigo as publisher,
                count(*) as n,
                count(
                    distinct (
                        '30' || to_char (pgc_pin_response_date, 'YYMMDD') || lpad (cast(pgc_id as text), 8, '0')
                    )
                ) as total_order,
                CASE
                    WHEN (
                        select
                            opr_product_type
                        from
                            operadoras
                        where
                            opr_codigo = pgc_opr_codigo
                    ) = 5 THEN sum(pgc_real_amount)
                    WHEN (
                        (
                            select
                                opr_product_type
                            from
                                operadoras
                            where
                                opr_codigo = pgc_opr_codigo
                        ) = 7
                        OR (
                            select
                                opr_product_type
                            from
                                operadoras
                            where
                                opr_codigo = pgc_opr_codigo
                        ) = 4
                    ) THEN sum(pgc_face_amount)
                    ELSE sum(pgc_face_amount)
                END as total
            from
                pins_gocash
            where
                pgc_opr_codigo != 0
                and pgc_pin_response_date >= (
                    select
                        min(fp_date)
                    from
                        financial_processing
                        inner join operadoras on opr_codigo = fp_publisher
                    where
                        fp_freeze = 0
                        and opr_status = '1'
                )
            group by
                dia,
                publisher
        )
    ) t
where
    publisher NOT IN (49, 53, 78)
group by
    canal,
    dia,
    publisher
order by
    dia desc,
    canal,
    publisher;