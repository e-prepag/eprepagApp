select pin_codinterno, case_codigo, opr_nome, pin_valor, opr_codigo, pin_datavenda, pin_horavenda, vg_canal
FROM (         (
        select t0.pin_codinterno, pin_codigo as case_codigo, t1.opr_nome, t0.pin_valor, t0.opr_codigo, t0.pin_datavenda, t0.pin_horavenda, case when pin_status = '3' then 'G' when pin_status = '6' then 'L' when pin_status = '8' then 'L' end as vg_canal
        from pins t0, operadoras t1, pins_status t3
        where (t0.opr_codigo=t1.opr_codigo) and (t0.pin_status=t3.stat_codigo) and (pin_datavenda between '2025-07-16 00:00:00' and '2025-07-31 23:59:59') and (t0.opr_codigo=143)
        )
    UNION ALL
        (
        SELECT pih_pin_id as pin_codinterno, pin_codigo as case_codigo, o.opr_nome, pin_valor, pih_id as opr_codigo, to_char(pih_data,'YYYY-MM-DD')::timestamp as pin_datavenda, to_char(pih_data,'HH24:MI:SS') as pin_horavenda, 'C' as vg_canal
        FROM pins_integracao_card_historico pich inner join pins_card pc ON pin_codinterno=pih_pin_id inner join operadoras o ON pc.opr_codigo=o.opr_codigo
        WHERE pih_codretepp='2' and pich.pin_status =4 and pin_lote_codigo > 6 and pih_id = 143 and (pih_data between '2025-07-16 00:00:00' and '2025-07-31 23:59:59') ) ) as selection
order by pin_datavenda desc, pin_horavenda desc