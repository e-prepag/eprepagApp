WITH parametros AS (
    -- CONFIGURAÇÕES: Ajuste os parâmetros aqui
    SELECT 
        '2025-08-01'::date AS data_inicio,
        '2025-08-05'::date AS data_fim,
        ARRAY[1, 2, 3]::integer[] AS usuarios_filtro -- NULL para todos os usuários, ou array com IDs específicos
),

-- Gerar todas as datas do período
datas_periodo AS (
    SELECT generate_series(
        (SELECT data_inicio FROM parametros),
        (SELECT data_fim FROM parametros),
        '1 day'::interval
    )::date AS data
),

-- Obter todos os usuários que tiveram movimentação no período ou que devem ser incluídos
usuarios_relevantes AS (
    SELECT DISTINCT dugsl_ug_id AS usuario_id
    FROM dist_usuarios_games_saldo_log
    WHERE dugsl_data_inclusao::date BETWEEN (SELECT data_inicio FROM parametros) AND (SELECT data_fim FROM parametros)
        AND ((SELECT usuarios_filtro FROM parametros) IS NULL 
             OR dugsl_ug_id = ANY(SELECT usuarios_filtro FROM parametros))
    
    UNION
    
    -- Incluir usuários específicos mesmo que não tenham movimentação no período
    SELECT unnest(usuarios_filtro) AS usuario_id
    FROM parametros
    WHERE usuarios_filtro IS NOT NULL
),

-- Criar combinação de todos os usuários com todas as datas
usuarios_datas AS (
    SELECT 
        ur.usuario_id,
        dp.data
    FROM usuarios_relevantes ur
    CROSS JOIN datas_periodo dp
),

-- Logs com informações adicionais
logs_com_detalhes AS (
    SELECT 
        dugsl_ug_id AS usuario_id,
        dugsl_data_inclusao::date AS data,
        dugsl_data_inclusao,
        dugsl_ug_perfil_saldo_antes,
        dugsl_ug_perfil_saldo,
        ROW_NUMBER() OVER (PARTITION BY dugsl_ug_id, dugsl_data_inclusao::date ORDER BY dugsl_data_inclusao ASC) AS primeiro_log_dia,
        ROW_NUMBER() OVER (PARTITION BY dugsl_ug_id, dugsl_data_inclusao::date ORDER BY dugsl_data_inclusao DESC) AS ultimo_log_dia
    FROM dist_usuarios_games_saldo_log
    WHERE ((SELECT usuarios_filtro FROM parametros) IS NULL 
           OR dugsl_ug_id = ANY(SELECT usuarios_filtro FROM parametros))
),

-- Obter primeiro e último log de cada dia por usuário
resumo_logs_dia AS (
    SELECT 
        usuario_id,
        data,
        MIN(CASE WHEN primeiro_log_dia = 1 THEN dugsl_ug_perfil_saldo_antes END) AS saldo_antes_primeiro_log,
        MAX(CASE WHEN ultimo_log_dia = 1 THEN dugsl_ug_perfil_saldo END) AS saldo_final_dia
    FROM logs_com_detalhes
    GROUP BY usuario_id, data
),

-- Obter o último saldo conhecido antes de cada data (saldo do dia anterior)
saldo_anterior AS (
    SELECT 
        ud.usuario_id,
        ud.data,
        (
            SELECT rld.saldo_final_dia
            FROM resumo_logs_dia rld
            WHERE rld.usuario_id = ud.usuario_id 
                AND rld.data < ud.data
            ORDER BY rld.data DESC
            LIMIT 1
        ) AS saldo_ultimo_dia_anterior
    FROM usuarios_datas ud
),

-- Calcular saldo inicial e final para cada dia
resultado_base AS (
    SELECT 
        ud.usuario_id,
        ud.data,
        COALESCE(
            sa.saldo_ultimo_dia_anterior,  -- Saldo final do último dia com movimento anterior
            rld.saldo_antes_primeiro_log,  -- Ou saldo antes do primeiro log do dia atual
            0  -- Default case (usuário nunca teve movimentação)
        ) AS saldo_inicial,
        COALESCE(
            rld.saldo_final_dia,  -- Saldo final do dia (se houve movimento)
            sa.saldo_ultimo_dia_anterior,  -- Ou mantém o saldo do último dia anterior
            rld.saldo_antes_primeiro_log,  -- Ou saldo antes do primeiro log histórico
            0  -- Default case
        ) AS saldo_final_calculado
    FROM usuarios_datas ud
    LEFT JOIN resumo_logs_dia rld ON ud.usuario_id = rld.usuario_id AND ud.data = rld.data
    LEFT JOIN saldo_anterior sa ON ud.usuario_id = sa.usuario_id AND ud.data = sa.data
),

-- Preencher gaps de saldo (forward fill) para dias sem movimentação
resultado_com_forward_fill AS (
    SELECT 
        usuario_id,
        data,
        saldo_inicial,
        saldo_final_calculado,
        -- Forward fill: usar o último saldo conhecido se o atual for null/zero
        COALESCE(
            NULLIF(saldo_final_calculado, 0),
            LAG(saldo_final_calculado) OVER (PARTITION BY usuario_id ORDER BY data),
            saldo_inicial
        ) AS saldo_final
    FROM resultado_base
)

-- Resultado final
SELECT 
    usuario_id,
    data,
    -- Corrigir saldo inicial baseado no saldo final do dia anterior (após forward fill)
    COALESCE(
        LAG(saldo_final) OVER (PARTITION BY usuario_id ORDER BY data),
        saldo_inicial
    ) AS saldo_inicial,
    saldo_final
FROM resultado_com_forward_fill
ORDER BY usuario_id, data;

-- INSTRUÇÕES DE USO:
-- 1. Ajuste os parâmetros na CTE 'parametros':
--    - data_inicio: data inicial do período
--    - data_fim: data final do período  
--    - usuarios_filtro: array com IDs dos usuários específicos ou NULL para todos
--
-- 2. Exemplos de configuração:
--    - Todos os usuários: usuarios_filtro NULL
--    - Usuários específicos: ARRAY[1, 2, 3]::integer[]
--    - Período: '2025-08-01' até '2025-08-05'