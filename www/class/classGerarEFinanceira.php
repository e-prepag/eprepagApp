<?php
require_once '/www/db/connect.php';
require_once '/www/db/ConnectionPDO.php';
require_once '/www/includes/load_dotenv.php';

class GerarEFinanceira
{

    private $cnpjEPP;                            // CNPJ da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $razaoEPP;  // Razão Social da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $enderecoEPP;    // Endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $bairroEPP;
    private $numeroEPP;
    private $complementoEPP;
    private $cepEPP;                                   // CEP da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $ufEPP;                                          // UF da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $municipioEPP;
    private $nomeRespRMF;                  // Nome do responsável pela RMF
    private $cpfRespRMF;                    // CPF do responsável pela RMF
    private $foneRespRMF;                  // Telefone do responsável pela RMF
    private $dddRespRMF;                  // DDD do responsável pela RMF
    private $emailRespeFIN;                // Email do responsável pela e-financeira
    private $cpfRespeFIN;                // CPF do responsável pela e-financeira
    private $nomeRespeFIN;                // Nome do responsável pela e-financeira
    private $foneRespeFIN;                // Telefone do responsável pela e-financeira
    private $dddRespeFIN;                // DDD do responsável pela e-financeira
    private $setorRespeFIN;                // Setor do responsável pela e-financeira
    private $enderecoRespeFIN;                // Endereço do responsável pela e-financeira
    private $municipioRespeFIN;                // Município do responsável pela e-financeira
    private $ufRespeFIN;                // UF do responsável pela e-financeira
    private $cepRespeFIN;                // CEP do responsável pela e-financeira
    private $bairroRespeFIN;                // Bairro do responsável pela e-financeira
    private $complementoRespeFIN;                // Complemento do responsável pela e-financeira
    private $numeroRespeFIN;                // Número do responsável pela e-financeira
    private $setorReprLegal;
    private $telefoneReprLegal;
    private $dddTelefoneReprLegal;
    private $cpfReprLegal;
    private $codMunicipioEPP;
    private $certificado;
    private $senhaCertificado;
    private $caminhoCertificadoPublico;
    private $certificado_privado_epp;
    private $chave_privada_epp;
    private $versao_aplicacao;
    private $cacheIdsEventos = [];

    public function __construct()
    {
        $this->cnpjEPP = '19037276000172';
        $this->razaoEPP = 'E-prepag Administradora de Cartoes Ltda';
        $this->enderecoEPP = 'R. Dep. Lacerda Franco';
        $this->bairroEPP = 'Pinheiros';
        $this->numeroEPP = '300';
        $this->complementoEPP = 'conjuntos 26-A';
        $this->cepEPP = '05418000';
        $this->ufEPP = 'SP';
        $this->nomeRespRMF = 'Glaucia da Costa Gregio';
        $this->cpfRespRMF = '16806289843';
        $this->foneRespRMF = '51783224';
        $this->dddRespRMF = '11';
        $this->emailRespeFIN = 'rc@e-prepag.com.br';
        $this->municipioEPP = 'SAO PAULO';
        $this->enderecoRespeFIN = 'R. Dep. Lacerda Franco';
        $this->municipioRespeFIN = 'SAO PAULO';
        $this->ufRespeFIN = 'SP';
        $this->cepRespeFIN = '05418000';
        $this->bairroRespeFIN = 'Pinheiros';
        $this->complementoRespeFIN = 'conjuntos 26-A';
        $this->numeroRespeFIN = '300';
        $this->cpfRespeFIN = '38574409880';
        $this->nomeRespeFIN = 'THANIA LOPES FERREIRA';
        $this->dddRespeFIN = '11';
        $this->foneRespeFIN = '51783224';
        $this->setorRespeFIN = 'Risco e Compliance';
        $this->setorReprLegal = 'Diretoria';
        $this->telefoneReprLegal = '975687428';
        $this->dddTelefoneReprLegal = '11';
        $this->cpfReprLegal = '16806289843';
        $this->codMunicipioEPP = '3550308';
        $this->certificado = __DIR__ . '/../ssl/cert-eprepag.pfx';
        $this->senhaCertificado = getenv('senha_certificado_digital');
        $this->caminhoCertificadoPublico = '/www/ssl/pre-efinanceira-receita-fazenda-gov-br-2025.cer';
        $this->certificado_privado_epp = '/www/ssl/private-epp-cert.pem';
        $this->chave_privada_epp = '/www/ssl/key-epp-cert.pem';
        $this->versao_aplicacao = '00000000000000000001';
    }

    public function obterDadosMovFinPJ($inicio, $fim)
    {
        $pdo = ConnectionPDO::getConnection()->getLink();
        $sql = "WITH -- Busca todas as movimentações no período, sem filtrar status do usuário ainda
                    MovimentacaoMensal AS (
                        SELECT 
                            ug.ug_id,
                            TO_CHAR(sl.dugsl_data_inclusao::date, 'YYYYMM') AS ano_mes_caixa,
                            SUM(CASE 
                                WHEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) > 0 
                                THEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) 
                                ELSE 0 
                            END) AS entradas,
                            ABS(SUM(CASE 
                                WHEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) < 0 
                                THEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) 
                                ELSE 0 
                            END)) AS saidas,
                            (
                                SUM(CASE WHEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) > 0 THEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) ELSE 0 END) +
                                ABS(SUM(CASE WHEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) < 0 THEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) ELSE 0 END))
                            ) AS total_movimentado_mes
                        FROM 
                            dist_usuarios_games ug
                        JOIN 
                            dist_usuarios_games_saldo_log sl ON ug.ug_id = sl.dugsl_ug_id
                        WHERE 
                            -- Alterado conforme solicitado: Apenas filtro de data do log
                            sl.dugsl_data_inclusao::date BETWEEN :data_inicio AND :data_fim
                        GROUP BY 
                            ug.ug_id,
                            TO_CHAR(sl.dugsl_data_inclusao::date, 'YYYYMM')
                    ),
                    -- Identifica qual foi o PRIMEIRO mês que o usuário estourou o limite (> 6000)
                    GatilhoLimiar AS (
                        SELECT 
                            ug_id, 
                            MIN(ano_mes_caixa) AS mes_primeiro_estouro
                        FROM MovimentacaoMensal
                        WHERE total_movimentado_mes > 6000
                        GROUP BY ug_id
                    ),
                    -- Dados cadastrais para o relatório (Trazemos todos que tiveram movimento OU estão ativos)
                    DadosUsuario AS (
                        SELECT 
                            ug.ug_id,
                            ug.ug_nome_fantasia,
                            ug.ug_razao_social,
                            regexp_replace(ug.ug_cnpj, '[^0-9]', '', 'g') AS ni_declarado,
                            ug.ug_endereco,
                            ug.ug_numero,
                            ug.ug_complemento,
                            ug.ug_bairro,
                            ug.ug_cidade,
                            ug.ug_estado,
                            ug.ug_cep,
                            ug.ug_perfil_saldo,
                            ug.ug_ativo, 
                            ug.ug_data_encerramento_conta,
                            TO_CHAR(ug.ug_data_encerramento_conta, 'YYYYMM') as mes_encerramento
                        FROM 
                            dist_usuarios_games ug
                        WHERE
                            -- Otimização: Traz usuários que tem movimentação NO PERÍODO ou Estão Ativos (para regra de Dezembro)
                            ug.ug_id IN (SELECT ug_id FROM MovimentacaoMensal)
                            OR ug.ug_ativo = 1
                            OR ug.ug_data_encerramento_conta::date BETWEEN :data_inicio AND :data_fim
                    ),
                    -- Gera a lista de meses do período selecionado
                    Calendario AS (
                        SELECT TO_CHAR(d, 'YYYYMM') as ano_mes_caixa
                        FROM generate_series(
                            :data_inicio::date, 
                            :data_fim::date, 
                            '1 month'::interval
                        ) d
                    ) 
                -- SELECT FINAL COM AS REGRAS DE NEGÓCIO APLICADAS NO FILTRO
                SELECT 
                    2 AS tipo_declarado,
                    d.ni_declarado,
                    d.ug_razao_social AS nome_declarado,
                    NULL AS data_nascimento,
                    d.ug_endereco,
                    d.ug_numero,
                    d.ug_complemento,
                    d.ug_bairro,
                    d.ug_cidade,
                    d.ug_estado,
                    d.ug_cep,
                    ('PD' || d.ug_id) AS id_conta, 
                    d.ug_nome_fantasia AS nome_conta, 
                    '1' AS tp_relacao,
                    d.ug_perfil_saldo AS saldo_atual_conta,
                    cal.ano_mes_caixa,
                    COALESCE(m.entradas, 0) AS entradas_conta,
                    COALESCE(m.saidas, 0) AS saidas_conta,
                    COALESCE(m.total_movimentado_mes, 0) AS total_movimentado_mes,
                    d.ug_ativo, 
                    d.ug_data_encerramento_conta
                FROM 
                    DadosUsuario d
                CROSS JOIN 
                    Calendario cal
                LEFT JOIN 
                    MovimentacaoMensal m ON d.ug_id = m.ug_id AND cal.ano_mes_caixa = m.ano_mes_caixa
                LEFT JOIN
                    GatilhoLimiar g ON d.ug_id = g.ug_id
                WHERE 
                    -- Se a conta encerrou, NUNCA mostrar meses posteriores ao encerramento
                    (d.mes_encerramento IS NULL OR cal.ano_mes_caixa <= d.mes_encerramento)
                    AND (
                        -- REGRA 1: LIMIAR (> 6000)
                        -- Se em algum momento estourou o limite, mostra o mês do estouro e todos os seguintes
                        (g.mes_primeiro_estouro IS NOT NULL AND cal.ano_mes_caixa >= g.mes_primeiro_estouro)
                        OR
                        -- REGRA 2: DEZEMBRO (OBRIGATÓRIO)
                        -- Se for Dezembro E (Usuário está Ativo OU Encerrou a conta neste exato Dezembro)
                        (
                            RIGHT(cal.ano_mes_caixa, 2) = '12' 
                            AND (d.ug_ativo = 1 OR d.mes_encerramento = cal.ano_mes_caixa)
                        )
                        OR
                        -- REGRA 3: MÊS DE ENCERRAMENTO CONTA
                        -- Se for o mês exato do encerramento, deve aparecer independente de valores
                        (d.mes_encerramento = cal.ano_mes_caixa)
                    )
                ORDER BY
                    cal.ano_mes_caixa, d.ni_declarado;";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':data_inicio', $inicio);
        $stmt->bindParam(':data_fim', $fim);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obterDadosMovFinPF($inicio, $fim)
    {
        $pdo = ConnectionPDO::getConnection()->getLink();

        $sqlReprLegal = "WITH 
                            -- Movimentação bruta (sem filtro de status)
                            MovimentacaoMensal AS (
                                SELECT 
                                    ug.ug_id,
                                    TO_CHAR(sl.dugsl_data_inclusao::date, 'YYYYMM') AS ano_mes_caixa,
                                    SUM(CASE 
                                        WHEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) > 0 
                                        THEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) 
                                        ELSE 0 
                                    END) AS entradas,
                                    ABS(SUM(CASE 
                                        WHEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) < 0 
                                        THEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) 
                                        ELSE 0 
                                    END)) AS saidas,
                                    (
                                        SUM(CASE WHEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) > 0 THEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) ELSE 0 END) +
                                        ABS(SUM(CASE WHEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) < 0 THEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) ELSE 0 END))
                                    ) AS total_movimentado_mes
                                FROM 
                                    dist_usuarios_games ug
                                JOIN 
                                    dist_usuarios_games_saldo_log sl ON ug.ug_id = sl.dugsl_ug_id
                                WHERE 
                                    sl.dugsl_data_inclusao::date BETWEEN :data_inicio AND :data_fim
                                GROUP BY 
                                    ug.ug_id,
                                    TO_CHAR(sl.dugsl_data_inclusao::date, 'YYYYMM')
                            ),
                            -- Identifica gatilho (> 6000)
                            GatilhoLimiar AS (
                                SELECT 
                                    ug_id, 
                                    MIN(ano_mes_caixa) AS mes_primeiro_estouro
                                FROM MovimentacaoMensal
                                WHERE total_movimentado_mes > 6000
                                GROUP BY ug_id
                            ),
                            -- Dados Cadastrais (Filtra quem tem CPF e nome preenchidos)
                            DadosUsuario AS (
                                SELECT 
                                    ug.ug_id,
                                    ug.ug_nome_fantasia,
                                    ug.ug_razao_social,
                                    ug.ug_cnpj,
                                    ug.ug_endereco,
                                    ug.ug_numero,
                                    ug.ug_complemento,
                                    ug.ug_bairro,
                                    ug.ug_cidade,
                                    ug.ug_estado,
                                    ug.ug_cep,
                                    ug.ug_perfil_saldo,
                                    ug.ug_repr_legal_nome,
                                    ug.ug_repr_legal_cpf,
                                    ug.ug_repr_venda_cpf,
                                    ug.ug_repr_legal_data_nascimento,
                                    ug.ug_ativo, 
                                    ug.ug_data_encerramento_conta,
                                    TO_CHAR(ug.ug_data_encerramento_conta, 'YYYYMM') as mes_encerramento
                                FROM 
                                    dist_usuarios_games ug
                                WHERE 
                                    (ug.ug_repr_legal_cpf IS NOT NULL OR ug.ug_repr_venda_cpf IS NOT NULL)
                                    AND ug.ug_repr_legal_nome IS NOT NULL
                                    AND (
                                        ug.ug_id IN (SELECT ug_id FROM MovimentacaoMensal)
                                        OR ug.ug_ativo = 1
                                        OR ug.ug_data_encerramento_conta::date BETWEEN :data_inicio AND :data_fim
                                    )
                            ),
                            -- Calendário
                            Calendario AS (
                                SELECT TO_CHAR(d, 'YYYYMM') as ano_mes_caixa
                                FROM generate_series(
                                    :data_inicio::date, 
                                    :data_fim::date, 
                                    '1 month'::interval
                                ) d
                            )
                        SELECT 
                            1 AS tipo_declarado,
                            CASE 
                                WHEN COALESCE(d.ug_repr_legal_cpf, '') ILIKE '%**%'
                                     AND SUBSTRING(COALESCE(d.ug_repr_legal_cpf, '') FROM LENGTH(COALESCE(d.ug_repr_legal_cpf, '')) - 1 FOR 2) = SUBSTRING(COALESCE(d.ug_repr_venda_cpf, '') FROM LENGTH(COALESCE(d.ug_repr_venda_cpf, '')) - 1 FOR 2)
                                     THEN regexp_replace(d.ug_repr_venda_cpf, '[^0-9]', '', 'g')
                                ELSE regexp_replace(d.ug_repr_legal_cpf, '[^0-9]', '', 'g')
                            END AS ni_declarado,
                            d.ug_repr_legal_nome AS nome_declarado,
                            d.ug_repr_legal_data_nascimento AS data_nascimento,
                            d.ug_endereco,
                            d.ug_numero,
                            d.ug_complemento,
                            d.ug_bairro,
                            d.ug_cidade,
                            d.ug_estado,
                            d.ug_cep,
                            ('PD' || d.ug_id) AS id_conta, 
                            d.ug_nome_fantasia AS nome_conta, 
                            '3' AS tp_relacao,
                            d.ug_perfil_saldo AS saldo_atual_conta,
                            cal.ano_mes_caixa,
                            COALESCE(m.entradas, 0) AS entradas_conta,
                            COALESCE(m.saidas, 0) AS saidas_conta,
                            COALESCE(m.total_movimentado_mes, 0) AS total_movimentado_mes,
                            d.ug_ativo, d.ug_data_encerramento_conta
                        FROM 
                            DadosUsuario d
                        CROSS JOIN 
                            Calendario cal
                        LEFT JOIN 
                            MovimentacaoMensal m ON d.ug_id = m.ug_id AND cal.ano_mes_caixa = m.ano_mes_caixa
                        LEFT JOIN
                            GatilhoLimiar g ON d.ug_id = g.ug_id
                        WHERE 
                            -- TRAVA GLOBAL: Se a conta encerrou, NUNCA mostrar meses posteriores ao encerramento
                            (d.mes_encerramento IS NULL OR cal.ano_mes_caixa <= d.mes_encerramento)
                            AND (
                                -- REGRA 1: LIMIAR (> 6000)
                                (g.mes_primeiro_estouro IS NOT NULL AND cal.ano_mes_caixa >= g.mes_primeiro_estouro)
                                OR
                                -- REGRA 2: DEZEMBRO (OBRIGATÓRIO)
                                (
                                    RIGHT(cal.ano_mes_caixa, 2) = '12' 
                                    AND (d.ug_ativo = 1 OR d.mes_encerramento = cal.ano_mes_caixa)
                                )
                                OR
                                -- REGRA 3: MÊS DE ENCERRAMENTO CONTA
                                (d.mes_encerramento = cal.ano_mes_caixa)
                            )
                        ORDER BY
                            cal.ano_mes_caixa, ni_declarado;";

        $sqlPFTitular = "WITH 
                            -- Movimentação por CONTA (bruta)
                            MovimentacaoPorContaMensal AS (
                                SELECT 
                                    ug.ug_id,
                                    ug.ug_cpf,
                                    TO_CHAR(sl.ugsl_data_inclusao::date, 'YYYYMM') AS ano_mes_caixa, 
                                    SUM(CASE 
                                        WHEN (sl.ugsl_ug_perfil_saldo - sl.ugsl_ug_perfil_saldo_antes) > 0 
                                        THEN (sl.ugsl_ug_perfil_saldo - sl.ugsl_ug_perfil_saldo_antes) ELSE 0 END
                                    ) AS entradas,
                                    ABS(SUM(CASE 
                                        WHEN (sl.ugsl_ug_perfil_saldo - sl.ugsl_ug_perfil_saldo_antes) < 0 
                                        THEN (sl.ugsl_ug_perfil_saldo - sl.ugsl_ug_perfil_saldo_antes) ELSE 0 END
                                    )) AS saidas,
                                    (
                                        SUM(CASE WHEN (sl.ugsl_ug_perfil_saldo - sl.ugsl_ug_perfil_saldo_antes) > 0 THEN (sl.ugsl_ug_perfil_saldo - sl.ugsl_ug_perfil_saldo_antes) ELSE 0 END) +
                                        ABS(SUM(CASE WHEN (sl.ugsl_ug_perfil_saldo - sl.ugsl_ug_perfil_saldo_antes) < 0 THEN (sl.ugsl_ug_perfil_saldo - sl.ugsl_ug_perfil_saldo_antes) ELSE 0 END))
                                    ) AS total_movimentado_conta_mes
                                FROM 
                                    usuarios_games ug
                                JOIN 
                                    usuarios_games_saldo_log sl ON ug.ug_id = sl.ugsl_ug_id
                                WHERE 
                                    sl.ugsl_data_inclusao::date BETWEEN :data_inicio AND :data_fim
                                GROUP BY 
                                    ug.ug_id, ug.ug_cpf,
                                    TO_CHAR(sl.ugsl_data_inclusao::date, 'YYYYMM')
                            ),
                            -- Movimentação por PESSOA (CPF) - Para checar o Limiar de 2000
                            MovimentacaoPessoaMes AS (
                                SELECT
                                    ug_cpf,
                                    ano_mes_caixa,
                                    SUM(total_movimentado_conta_mes) AS total_movimentado_pessoa_mes
                                FROM 
                                    MovimentacaoPorContaMensal
                                WHERE ug_cpf IS NOT NULL
                                GROUP BY ug_cpf, ano_mes_caixa
                            ),
                            -- Identifica gatilho por CPF (> 2000)
                            GatilhoLimiarCPF AS (
                                SELECT 
                                    ug_cpf, 
                                    MIN(ano_mes_caixa) AS mes_primeiro_estouro
                                FROM MovimentacaoPessoaMes
                                WHERE total_movimentado_pessoa_mes > 2000
                                GROUP BY ug_cpf
                            ),
                            -- Dados Cadastrais das Contas
                            DadosUsuarioContas AS (
                                SELECT 
                                    ug.ug_id, ug.ug_nome, ug.ug_data_nascimento, ug.ug_cpf,
                                    ug.ug_endereco, ug.ug_numero, ug.ug_complemento, ug.ug_bairro,
                                    ug.ug_cidade, ug.ug_estado, ug.ug_cep, ug.ug_perfil_saldo, 
                                    ug.ug_ativo, 
                                    ug.ug_data_encerramento_conta,
                                    TO_CHAR(ug.ug_data_encerramento_conta, 'YYYYMM') as mes_encerramento
                                FROM 
                                    usuarios_games ug
                                WHERE 
                                    ug.ug_cpf IS NOT NULL
                                    AND (
                                        ug.ug_id IN (SELECT ug_id FROM MovimentacaoPorContaMensal)
                                        OR ug.ug_ativo = 1
                                        OR ug.ug_data_encerramento_conta::date BETWEEN :data_inicio AND :data_fim
                                    )
                            ),
                            -- Calendário
                            Calendario AS (
                                SELECT TO_CHAR(d, 'YYYYMM') as ano_mes_caixa
                                FROM generate_series(
                                    :data_inicio::date, 
                                    :data_fim::date, 
                                    '1 month'::interval
                                ) d
                            )
                        SELECT 
                            1 AS tipo_declarado,
                            regexp_replace(d.ug_cpf, '[^0-9]', '', 'g') AS ni_declarado,
                            d.ug_nome AS nome_declarado,
                            d.ug_data_nascimento AS data_nascimento,
                            d.ug_endereco, d.ug_numero, d.ug_complemento, d.ug_bairro,
                            d.ug_cidade, d.ug_estado, d.ug_cep,
                            ('GM' || d.ug_id) AS id_conta, 
                            'Conta de Pagamento' AS nome_conta, 
                            '1' AS tp_relacao, 
                            d.ug_perfil_saldo AS saldo_atual_conta,
                            cal.ano_mes_caixa,
                            COALESCE(m.entradas, 0) AS entradas_conta,
                            COALESCE(m.saidas, 0) AS saidas_conta,
                            COALESCE(m.total_movimentado_conta_mes, 0) AS total_movimentado_conta,
                            d.ug_ativo, d.ug_data_encerramento_conta
                        FROM 
                            DadosUsuarioContas d
                        CROSS JOIN 
                            Calendario cal
                        LEFT JOIN 
                            MovimentacaoPorContaMensal m ON d.ug_id = m.ug_id AND cal.ano_mes_caixa = m.ano_mes_caixa
                        LEFT JOIN
                            GatilhoLimiarCPF g ON d.ug_cpf = g.ug_cpf
                        WHERE 
                            -- TRAVA GLOBAL: Se a CONTA ESPECÍFICA encerrou, não mostra meses posteriores
                            (d.mes_encerramento IS NULL OR cal.ano_mes_caixa <= d.mes_encerramento)
                            AND (
                                -- REGRA 1: LIMIAR CPF (> 2000)
                                -- Se o CPF estourou o limite, mostra todas as contas dele daquele mês em diante
                                (g.mes_primeiro_estouro IS NOT NULL AND cal.ano_mes_caixa >= g.mes_primeiro_estouro)
                                OR
                                -- REGRA 2: DEZEMBRO
                                -- Se é Dezembro e a conta está ativa ou fechou neste mês
                                (
                                    RIGHT(cal.ano_mes_caixa, 2) = '12' 
                                    AND (d.ug_ativo = 1 OR d.mes_encerramento = cal.ano_mes_caixa)
                                )
                                OR
                                -- REGRA 3: MÊS DE ENCERRAMENTO CONTA
                                (d.mes_encerramento = cal.ano_mes_caixa)
                            )
                        ORDER BY
                            cal.ano_mes_caixa, ni_declarado, id_conta;";

        $stmtReprLegal = $pdo->prepare($sqlReprLegal);
        $stmtReprLegal->bindParam(':data_inicio', $inicio);
        $stmtReprLegal->bindParam(':data_fim', $fim);
        $stmtReprLegal->execute();
        $resultReprLegal = $stmtReprLegal->fetchAll(PDO::FETCH_ASSOC);

        $stmtPFTitular = $pdo->prepare($sqlPFTitular);
        $stmtPFTitular->bindParam(':data_inicio', $inicio);
        $stmtPFTitular->bindParam(':data_fim', $fim);
        $stmtPFTitular->execute();
        $resultPFTitular = $stmtPFTitular->fetchAll(PDO::FETCH_ASSOC);

        return array_merge($resultReprLegal, $resultPFTitular);
    }

    private function buscarEnderecoPorCep($cep)
    {
        // Remove tudo que não for número
        $cep = preg_replace('/\D/', '', $cep);

        // Valida o formato do CEP (8 dígitos)
        if (strlen($cep) !== 8) {
            return ['erro' => true, 'mensagem' => 'CEP inválido'];
        }

        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'ConsultaCEP-PHP/1.0'
        ]);

        $resposta = curl_exec($ch);

        if (curl_errno($ch)) {
            $erro = curl_error($ch);
            curl_close($ch);
            return ['erro' => true, 'mensagem' => "Erro na requisição cURL: $erro"];
        }

        curl_close($ch);

        $dados = json_decode($resposta, true);

        // Se o CEP não foi encontrado
        if (isset($dados['erro']) && $dados['erro'] === true) {
            return ['erro' => true, 'mensagem' => 'CEP não encontrado'];
        }

        return [
            'erro' => false,
            'cep' => $dados['cep'] ?: '',
            'logradouro' => $dados['logradouro'] ?: '',
            'bairro' => $dados['bairro'] ?: '',
            'cidade' => $dados['localidade'] ?: '',
            'estado' => $dados['uf'] ?: '',
            'complemento' => $dados['complemento'] ?: '',
            'ibge' => $dados['ibge'] ?: ''
        ];
    }

    private function formatarEnderecoCompleto(
        $logradouro,
        $numero,
        $complemento,
        $bairro,
        $cidade,
        $estado,
        $cep
    ) {
        // Remove espaços em branco desnecessários
        $logradouro = trim($logradouro);
        $bairro = trim($bairro);
        $cidade = trim($cidade);
        $estado = trim($estado);
        $cep = preg_replace('/\D/', '', $cep);

        // Verifica se faltam dados críticos para o endereço
        if (empty($logradouro) || empty($bairro) || empty($cidade) || empty($estado)) {

            // Tenta buscar via API de CEP se o CEP for válido
            if (strlen($cep) === 8) {
                $dadosViaCep = $this->buscarEnderecoPorCep($cep);

                if ($dadosViaCep['erro'] === false) {

                    $logradouro  = empty($logradouro) ? $dadosViaCep['logradouro'] : $logradouro;
                    $bairro      = empty($bairro)     ? $dadosViaCep['bairro']     : $bairro;
                    $cidade      = empty($cidade)     ? $dadosViaCep['cidade']     : $cidade;
                    $estado      = empty($estado)     ? $dadosViaCep['estado']     : $estado;

                    // Preenche o complemento se o original estiver vazio E o ViaCEP retornar algo
                    if (empty($complemento) && !empty($dadosViaCep['complemento'])) {
                        $complemento = $dadosViaCep['complemento'];
                    }
                }
            }
        }
        if (empty($logradouro) && empty($bairro) && empty($cidade) && empty($estado)) {
            return "Endereco cliente nao encontrado";
        }

        return $this->garantirUtf8("$logradouro" . ($numero ? " $numero" : "") . ($complemento ? " $complemento" : "") . "/$cep/$cidade/$estado");
    }

    private function garantirUtf8($texto)
    {
        // Detecta o encoding atual
        $encoding = mb_detect_encoding($texto, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

        // Se não detectar, assume ISO-8859-1
        if ($encoding === false) {
            $encoding = 'ISO-8859-1';
        }

        // Se já estiver em UTF-8, apenas retorna
        if ($encoding === 'UTF-8') {
            return $texto;
        }

        // Converte para UTF-8
        return mb_convert_encoding($texto, 'UTF-8', $encoding);
    }

    private function agruparDadosEFinanceira(array $dadosPlanos)
    {
        $agrupados = [];

        foreach ($dadosPlanos as $registro) {
            // 1. Define as chaves de agrupamento
            $chaveDeclarado = $registro['ni_declarado']; // O CPF ou CNPJ
            $chaveMes = $registro['ano_mes_caixa'];      // O mês (ex: '202501')

            // 2. Cria o "envelope" do Declarado e do Mês, se ainda não existir
            if (!isset($agrupados[$chaveDeclarado])) {
                $agrupados[$chaveDeclarado] = [];
            }

            if (!isset($agrupados[$chaveDeclarado][$chaveMes])) {
                // Salva os dados do declarado (que são os mesmos para todas as linhas daquele CPF/Mês)
                $agrupados[$chaveDeclarado][$chaveMes] = [
                    'dadosDeclarado' => [
                        'tipo_declarado'  => $registro['tipo_declarado'],
                        'ni_declarado'    => $registro['ni_declarado'],
                        'nome_declarado'  => $registro['nome_declarado'],
                        'data_nascimento' => $registro['data_nascimento'],
                        'ug_endereco'     => $registro['ug_endereco'],
                        'ug_numero'       => $registro['ug_numero'],
                        'ug_complemento'  => $registro['ug_complemento'],
                        'ug_bairro'       => $registro['ug_bairro'],
                        'ug_cidade'       => $registro['ug_cidade'],
                        'ug_estado'       => $registro['ug_estado'],
                        'ug_cep'          => $registro['ug_cep'],
                    ],
                    'contas' => [] // Cria a lista de contas para este mês
                ];
            }

            // 3. Adiciona a conta atual (a linha do SQL) na lista de 'contas'
            //    daquele Declarado/Mês
            $agrupados[$chaveDeclarado][$chaveMes]['contas'][] = [
                'ug_id'          => $registro['id_conta'],
                'tipo_relacao'        => $registro['tp_relacao'],
                'entradas'          => $registro['entradas_conta'], // Nome padronizado
                'saidas'            => $registro['saidas_conta'],   // Nome padronizado
            ];
        }

        return $agrupados;
    }

    private function validarCpfCnpj($valor)
    {
        // Remove tudo que não for número
        $valor = preg_replace('/[^0-9]/', '', $valor);

        // Se for CPF
        if (strlen($valor) === 11) {
            return $this->validarCpf($valor);
        }

        // Se for CNPJ
        if (strlen($valor) === 14) {
            return $this->validarCnpj($valor);
        }

        return false;
    }

    private function validarCpf($cpf)
    {
        // Elimina CPFs inválidos conhecidos
        if (preg_match('/^(.)\1{10}$/', $cpf)) {
            return false;
        }

        // Calcula os dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    private function validarCnpj($cnpj)
    {
        // Elimina CNPJs inválidos conhecidos
        if (preg_match('/^(.)\1{13}$/', $cnpj)) {
            return false;
        }

        $tamanho = strlen($cnpj) - 2;
        $numeros = substr($cnpj, 0, $tamanho);
        $digitos = substr($cnpj, $tamanho);
        $soma = 0;
        $pos = $tamanho - 7;

        for ($i = $tamanho; $i >= 1; $i--) {
            $soma += $numeros[$tamanho - $i] * $pos--;
            if ($pos < 2) $pos = 9;
        }

        $resultado = ($soma % 11 < 2) ? 0 : 11 - ($soma % 11);
        if ($resultado != $digitos[0]) {
            return false;
        }

        $tamanho = $tamanho + 1;
        $numeros = substr($cnpj, 0, $tamanho);
        $soma = 0;
        $pos = $tamanho - 7;

        for ($i = $tamanho; $i >= 1; $i--) {
            $soma += $numeros[$tamanho - $i] * $pos--;
            if ($pos < 2) $pos = 9;
        }

        $resultado = ($soma % 11 < 2) ? 0 : 11 - ($soma % 11);
        return ($resultado == $digitos[1]);
    }


    private function inicioDoSemestre(string $data): string
    {
        $dt = new DateTime($data);
        $ano = $dt->format('Y');
        $mes = (int) $dt->format('m');

        if ($mes <= 6) {
            return "$ano-01-01";
        }

        return "$ano-07-01";
    }

    private function preCarregarIdsMovimentacoes(array $dadosAgrupados)
    {
        $pdo = ConnectionPDO::getConnection()->getLink();
        $listaParaVerificar = [];
        $chavesMap = [];

        // $dadosAgrupados vem no formato [cpf => [mes => dados]]
        foreach ($dadosAgrupados as $cpfCnpj => $meses) {
            foreach ($meses as $mes => $registro) {
                $chave = "{$mes}-{$cpfCnpj}";

                if (!isset($this->cacheIdsEventos[$chave])) {
                    $listaParaVerificar[$chave] = [
                        'anomes' => $mes,
                        'cpfcnpj' => $cpfCnpj
                    ];
                }
            }
        }

        if (empty($listaParaVerificar)) {
            return;
        }

        $filtros = [];
        $params = [];
        $i = 0;
        $placeholders = [];

        foreach ($listaParaVerificar as $item) {
            $placeholders[] = "(:anomes{$i}, :cpf{$i})";
            $params[":anomes{$i}"] = $item['anomes'];
            $params[":cpf{$i}"] = $item['cpfcnpj'];
            $i++;
        }

        // Vamos processar de 1000 em 1000 itens
        $chunks = array_chunk($listaParaVerificar, 1000, true);

        foreach ($chunks as $chunk) {
            $this->processarLoteSQL($chunk, $pdo);
        }
    }

    private function processarLoteSQL($itens, $pdo)
    {
        // Buscar Existentes
        $tupleStr = [];
        $params = [];
        $i = 0;

        foreach ($itens as $item) {
            $tupleStr[] = "(:a$i, :c$i)";
            $params[":a$i"] = $item['anomes'];
            $params[":c$i"] = $item['cpfcnpj'];
            $i++;
        }

        $sqlSelect = "SELECT id, data_anomes, cpfcnpj_declarado 
                  FROM public.envios_e_financeira 
                  WHERE tipo = 'MOVIMENTACAO' 
                  AND retificado = false 
                  AND (data_anomes, cpfcnpj_declarado) IN (" . implode(',', $tupleStr) . ")";

        $stmt = $pdo->prepare($sqlSelect);
        $stmt->execute($params);
        $encontrados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $chavesEncontradas = [];
        foreach ($encontrados as $row) {
            $chave = "{$row['data_anomes']}-{$row['cpfcnpj_declarado']}";
            $this->cacheIdsEventos[$chave] = $row['id'];
            $chavesEncontradas[$chave] = true;
        }

        // Identificar quem falta (Diff)
        $novosParaInserir = [];
        foreach ($itens as $item) {
            $chave = "{$item['anomes']}-{$item['cpfcnpj']}";
            if (!isset($chavesEncontradas[$chave])) {
                $novosParaInserir[] = $item;
            }
        }

        if (empty($novosParaInserir)) {
            return;
        }

        // Inserir Novos em Lote (Bulk Insert)
        $values = [];
        $insertParams = [];
        $j = 0;

        // Valores padrão
        $tipo = 'MOVIMENTACAO';
        $status = 'PENDENTE';
        $vEfin = 'v1_2_1';
        $vEpp = $this->versao_aplicacao;
        $retificado = 'false';
        $nomeArq = 'none';

        foreach ($novosParaInserir as $novo) {
            $semestre = $this->getSemestreFormatado($novo['anomes'] . "-01");

            $values[] = "(:tipo$j, :status$j, :vefin$j, :vepp$j, :arq$j, :anomes$j, :cpf$j, :ret$j, :sem$j)";

            $insertParams[":tipo$j"] = $tipo;
            $insertParams[":status$j"] = $status;
            $insertParams[":vefin$j"] = $vEfin;
            $insertParams[":vepp$j"] = $vEpp;
            $insertParams[":arq$j"] = $nomeArq;
            $insertParams[":anomes$j"] = $novo['anomes'];
            $insertParams[":cpf$j"] = $novo['cpfcnpj'];
            $insertParams[":ret$j"] = $retificado;
            $insertParams[":sem$j"] = $semestre;
            $j++;
        }

        $sqlInsert = "INSERT INTO envios_e_financeira 
                  (tipo, status_envio, versao_efin, versao_epp, nome_arquivo, data_anomes, cpfcnpj_declarado, retificado, semestre_ano)
                  VALUES " . implode(',', $values) . "
                  RETURNING id, data_anomes, cpfcnpj_declarado";

        try {
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute($insertParams);
            $inseridos = $stmtInsert->fetchAll(PDO::FETCH_ASSOC);

            // Adiciona os recém criados ao cache
            foreach ($inseridos as $row) {
                $chave = "{$row['data_anomes']}-{$row['cpfcnpj_declarado']}";
                $this->cacheIdsEventos[$chave] = $row['id'];
            }
        } catch (PDOException $e) {
            throw new Exception("Erro no Bulk Insert: " . $e->getMessage());
        }
    }

    public function gerarMovimentacaoFinanceiraCompleta($inicio, $fim)
    {
        $data_inicio = DateTime::createFromFormat('Y-m', $inicio);
        $data_fim = DateTime::createFromFormat('Y-m', $fim);

        $erros = DateTime::getLastErrors();

        if ($data_inicio === false || $data_fim === false || $erros['error_count'] > 0) {
            return false;
        }

        $inicio = $data_inicio->format('Y-m-01');
        $fim = $data_fim->format('Y-m-t');

        // 1. Obtenção e Agrupamento dos Dados
        $inicio_semestre = $this->inicioDoSemestre($inicio);

        $dadosPJ = $this->obterDadosMovFinPJ($inicio_semestre, $fim);
        $dadosPF = $this->obterDadosMovFinPF($inicio_semestre, $fim);

        $dadosPJAgrupados = $this->agruparDadosEFinanceira($dadosPJ);
        $dadosPFAgrupados = $this->agruparDadosEFinanceira($dadosPF);

        $this->preCarregarIdsMovimentacoes($dadosPJAgrupados);
        $this->preCarregarIdsMovimentacoes($dadosPFAgrupados);

        // 2. Array de Agrupamento Final: [ANO_MES] => [XMLs daquele mês]
        $movimentacoesAgrupadasPorMes = [];

        $inicioMesAno = substr(str_replace('-', '', $inicio), 0, 6);
        // --- Processa PJs ---
        foreach ($dadosPJAgrupados as $pessoa => $meses) {
            foreach ($meses as $mes => $registro) {

                if ($inicioMesAno >= $mes) {
                    continue;
                }

                if (!$this->validarCpfCnpj($registro['dadosDeclarado']['ni_declarado'])) {
                    continue;
                }
                // CRIA O XML (ou o objeto XML/Evento)
                $xmlOuEvento = $this->gerarMovimentacaoFinanceira(
                    $registro['dadosDeclarado']['tipo_declarado'],
                    $this->apenasNumeros($registro['dadosDeclarado']['ni_declarado']),
                    $this->garantirUtf8($registro['dadosDeclarado']['nome_declarado']),
                    null,
                    $this->formatarEnderecoCompleto(
                        $registro['dadosDeclarado']['ug_endereco'],
                        $registro['dadosDeclarado']['ug_numero'],
                        $registro['dadosDeclarado']['ug_complemento'],
                        $registro['dadosDeclarado']['ug_bairro'],
                        $registro['dadosDeclarado']['ug_cidade'],
                        $registro['dadosDeclarado']['ug_estado'],
                        $registro['dadosDeclarado']['ug_cep']
                    ),
                    substr($mes, 0, 4), // Ano
                    substr($mes, 4, 2), // Mês
                    $registro['contas']
                );

                // ADICIONA O XML AO GRUPO DO MÊS CORRETO
                $movimentacoesAgrupadasPorMes[$mes][] = $xmlOuEvento;
            }
        }

        // --- Processa PFs ---
        foreach ($dadosPFAgrupados as $pessoa => $meses) {
            foreach ($meses as $mes => $registro) {

                if ($inicioMesAno >= $mes) {
                    continue;
                }

                if (!$this->validarCpfCnpj($registro['dadosDeclarado']['ni_declarado'])) {
                    continue;
                }
                // CRIA O XML (ou o objeto XML/Evento)
                $xmlOuEvento = $this->gerarMovimentacaoFinanceira(
                    $registro['dadosDeclarado']['tipo_declarado'],
                    $this->apenasNumeros($registro['dadosDeclarado']['ni_declarado']),
                    $this->garantirUtf8($registro['dadosDeclarado']['nome_declarado']),
                    substr($registro['dadosDeclarado']['data_nascimento'], 0, 10),
                    $this->formatarEnderecoCompleto(
                        $registro['dadosDeclarado']['ug_endereco'],
                        $registro['dadosDeclarado']['ug_numero'],
                        $registro['dadosDeclarado']['ug_complemento'],
                        $registro['dadosDeclarado']['ug_bairro'],
                        $registro['dadosDeclarado']['ug_cidade'],
                        $registro['dadosDeclarado']['ug_estado'],
                        $registro['dadosDeclarado']['ug_cep']
                    ),
                    substr($mes, 0, 4), // Ano
                    substr($mes, 4, 2), // Mês
                    $registro['contas']
                );

                // ADICIONA O XML AO GRUPO DO MÊS CORRETO
                $movimentacoesAgrupadasPorMes[$mes][] = $xmlOuEvento;
            }
        }

        return $movimentacoesAgrupadasPorMes;
    }

    public function gerarMovimentacaoFinanceiraCompletaDados($inicio, $fim)
    {
        $data_inicio = DateTime::createFromFormat('Y-m', $inicio);
        $data_fim = DateTime::createFromFormat('Y-m', $fim);

        $erros = DateTime::getLastErrors();

        if ($data_inicio === false || $data_fim === false || $erros['error_count'] > 0) {
            return false;
        }

        $inicio = $data_inicio->format('Y-m-01');
        $fim = $data_fim->format('Y-m-t');

        // 1. Obtenção e Agrupamento dos Dados
        $inicio_semestre = $this->inicioDoSemestre($inicio);

        $dadosPJ = $this->obterDadosMovFinPJ($inicio_semestre, $fim);
        $dadosPF = $this->obterDadosMovFinPF($inicio_semestre, $fim);

        $dadosPJAgrupados = $this->agruparDadosEFinanceira($dadosPJ);
        $dadosPFAgrupados = $this->agruparDadosEFinanceira($dadosPF);

        // 2. Array de Agrupamento Final: [ANO_MES] => [XMLs daquele mês]
        $movimentacoesAgrupadasPorMes = [];

        $inicioMesAno = substr(str_replace('-', '', $inicio), 0, 6);
        // --- Processa PJs ---
        foreach ($dadosPJAgrupados as $pessoa => $meses) {
            foreach ($meses as $mes => $registro) {

                if ($inicioMesAno >= $mes) {
                    continue;
                }

                if (!$this->validarCpfCnpj($registro['dadosDeclarado']['ni_declarado'])) {
                    continue;
                }

                $movimentacoesAgrupadasPorMes[$mes][] = $registro;
            }
        }

        // --- Processa PFs ---
        foreach ($dadosPFAgrupados as $pessoa => $meses) {
            foreach ($meses as $mes => $registro) {

                if ($inicioMesAno >= $mes) {
                    continue;
                }

                if (!$this->validarCpfCnpj($registro['dadosDeclarado']['ni_declarado'])) {
                    continue;
                }

                $movimentacoesAgrupadasPorMes[$mes][] = $registro;
            }
        }

        return $movimentacoesAgrupadasPorMes;
    }

    private function compararDatas($data_inicial, $data_final)
    {
        if ($data_inicial == "" || $data_final == "") {
            return 0;
        }
        try {
            $d1 = new DateTime($data_inicial);
            $d2 = new DateTime($data_final);

            if ($d1 < $d2) {
                return 1;   // data inicial menor (normal)
            } else {
                return -1;  // data inicial maior ou igual
            }
        } catch (Exception $e) {
            return 0; // erro na data
        }
    }
    public function gerarXmlMovimentacao($data_inicial, $data_final)
    {
        if ($this->compararDatas($data_inicial, $data_final) < 1) {
            return [];
        }

        $efinanceira = new GerarEFinanceira();

        $movimentacoes = $efinanceira->gerarMovimentacaoFinanceiraCompleta($data_inicial, $data_final);

        $xmls = $efinanceira->gerarLotesMovsFinanceira($movimentacoes);

        return $xmls;
    }

    public function gerarLotesMovsFinanceira(array $movimentacoes, $tamanhoLote = 50, $debug = false)
    {
        $lotesArray = [];

        // O array $movimentacoes já está agrupado por mês (a chave é o anoMes, ex: '202501')
        foreach ($movimentacoes as $anoMes => $eventosDoMes) {

            // 1. Divide os eventos do mês em lotes menores (Chunks)
            // A função array_chunk() do PHP faz isso de forma eficiente.
            $chunksDeEventos = array_chunk($eventosDoMes, $tamanhoLote);

            $contadorLote = 1;

            // 2. Itera sobre cada lote de eventos
            foreach ($chunksDeEventos as $eventosDoLote) {

                // Log opcional para acompanhamento
                if ($debug)
                    echo "Criando Lote {$contadorLote} para o Mês {$anoMes} com " . count($eventosDoLote) . " eventos...\n";

                $xmlLoteFinal = $this->gerarLoteAssincrono($eventosDoLote);

                // 4. Adiciona o XML do lote final ao array de retorno
                $lotesArray[] = ['xml' => $xmlLoteFinal, 'ano_mes' => $anoMes, 'lote_numero' => $contadorLote];



                $contadorLote++;
            }
        }

        return $lotesArray;
    }

    private function obterUltimoIdEnvio()
    {
        $pdo = ConnectionPDO::getConnection()->getLink();

        $stmt = $pdo->prepare("SELECT MAX(id) AS ultimo_id FROM envios_e_financeira;");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['ultimo_id'] ?: 7;
    }

    private function gerarIdFormatado($numero)
    {
        // Define o prefixo
        $prefixo = 'ID';

        // Define o comprimento total da parte numérica
        $comprimentoNumerico = 17;

        // Usa str_pad para preencher o número com '0' à esquerda (STR_PAD_LEFT)
        // até que ele atinja o comprimento de 18 caracteres.
        $parteNumerica = '1' . str_pad($numero, $comprimentoNumerico, '0', STR_PAD_LEFT);

        // Concatena o prefixo com a parte numérica
        return $prefixo . $parteNumerica;
    }

    private function apenasNumeros($documento)
    {
        // A expressão regular /\D/ significa "qualquer caractere que NÃO seja um dígito".
        // A função preg_replace substitui todos eles por uma string vazia ('').
        return preg_replace('/\D/', '', $documento);
    }

    private function getSemestreFormatado($dataString)
    {
        $date = new DateTime($dataString);
        $ano = $date->format('Y');
        $mes = (int)$date->format('n'); // 'n' retorna o mês de 1 a 12 sem zeros à esquerda

        $semestre = ($mes <= 6) ? 1 : 2;

        return "{$ano}.{$semestre}";
    }

    private function buscar_movimentacoes(string $ano_mes, string $cpfcnpj): int
    {
        $pdo = ConnectionPDO::getConnection()->getLink();
        // 1. Tenta buscar o ID existente
        $sql = "SELECT id FROM public.envios_e_financeira 
            WHERE data_anomes = :anomes 
              AND cpfcnpj_declarado = :cpfcnpj 
              AND tipo = 'MOVIMENTACAO'
              AND retificado = false
            LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':anomes'  => $ano_mes,
            ':cpfcnpj' => $cpfcnpj
        ]);

        $idEncontrado = $stmt->fetchColumn();

        // Se encontrou, retorna o ID imediatamente
        if ($idEncontrado) {
            return (int) $idEncontrado;
        }

        // 2. Se não encontrou, prepara os dados para criar um novo

        $semetre = $this->getSemestreFormatado($ano_mes . "-01");

        $dadosParaCriar = [
            'tipo'              => 'MOVIMENTACAO',
            'status_envio'      => 'PENDENTE',      // Valor padrão
            'versao_efin'       => 'v1_2_1',         // Valor padrão obrigatório
            'versao_epp'        => $this->versao_aplicacao,         // Valor padrão obrigatório
            'nome_arquivo'      => "none",
            'cpfcnpj_declarado' => $cpfcnpj,
            'data_anomes'       => $ano_mes,
            'retificado'        => 'false',
            'semestre_ano'      => $semetre
        ];

        return $this->criarEnvioFinanceira($dadosParaCriar);
    }

    private function buscar_aberturas(string $data_inicial, string $data_final): int
    {
        $periodo = $data_inicial . "_" . $data_final;
        $pdo = ConnectionPDO::getConnection()->getLink();
        // 1. Tenta buscar o ID existente
        $sql = "SELECT id FROM public.envios_e_financeira 
            WHERE data_anomes = :anomes 
              AND tipo = 'ABERTURA'
              AND retificado = false
            LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':anomes'  => $periodo,
        ]);

        $idEncontrado = $stmt->fetchColumn();

        // Se encontrou, retorna o ID imediatamente
        if ($idEncontrado) {
            return (int) $idEncontrado;
        }

        // 2. Se não encontrou, prepara os dados para criar um novo

        $semetre = $this->getSemestreFormatado($data_inicial);

        $dadosParaCriar = [
            'tipo'              => 'ABERTURA',
            'status_envio'      => 'PENDENTE',      // Valor padrão
            'versao_efin'       => 'v1_2_1',         // Valor padrão obrigatório
            'versao_epp'        => $this->versao_aplicacao,         // Valor padrão obrigatório
            'nome_arquivo'      => "none",
            'data_anomes'       => $periodo,
            'retificado'        => 'false',
            'semestre_ano'      => $semetre
        ];

        return $this->criarEnvioFinanceira($dadosParaCriar);
    }

    private function buscar_fechamento(string $data_inicial, string $data_final): int
    {
        $periodo = $data_inicial . "_" . $data_final;
        $pdo = ConnectionPDO::getConnection()->getLink();
        // 1. Tenta buscar o ID existente
        $sql = "SELECT id FROM public.envios_e_financeira 
            WHERE data_anomes = :anomes 
              AND tipo = 'FECHAMENTO'
              AND retificado = false
            LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':anomes'  => $periodo,
        ]);

        $idEncontrado = $stmt->fetchColumn();

        // Se encontrou, retorna o ID imediatamente
        if ($idEncontrado) {
            return (int) $idEncontrado;
        }

        // 2. Se não encontrou, prepara os dados para criar um novo

        $semetre = $this->getSemestreFormatado($data_inicial);

        $dadosParaCriar = [
            'tipo'              => 'FECHAMENTO',
            'status_envio'      => 'PENDENTE',      // Valor padrão
            'versao_efin'       => 'v1_2_2',         // Valor padrão obrigatório
            'versao_epp'        => $this->versao_aplicacao,         // Valor padrão obrigatório
            'nome_arquivo'      => "none",
            'data_anomes'       => $periodo,
            'retificado'        => 'false',
            'semestre_ano'      => $semetre
        ];

        return $this->criarEnvioFinanceira($dadosParaCriar);
    }

    /**
     * @param array{
     *     ug_id: int|string,
     *     entradas: float|string|int,
     *     saidas: float|string|int,
     *     tipo_relacao: string
     * } $contas_user
     */
    public function gerarMovimentacaoFinanceira($tipoNI, $cpfCnpj, $nomeDeclarado, $dataNascimento = '', $enderecoCliente, $ano, $mes, array $contas_user)
    {
        // Criar o objeto DOMDocument
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false; // Deixa o XML formatado (quebras de linha e identação)
        $dom->preserveWhiteSpace = true;

        $namespace = 'http://www.eFinanceira.gov.br/schemas/evtMovOpFin/v1_2_1';
        // Criar o elemento raiz com namespace
        $eFinanceira = $dom->createElementNS(
            $namespace, // namespace
            'eFinanceira' // nome do elemento
        );
        $dom->appendChild($eFinanceira);

        $cpfCnpjNum = $this->apenasNumeros($cpfCnpj);
        $chaveCache = "{$ano}{$mes}-{$cpfCnpjNum}";

        if (isset($this->cacheIdsEventos[$chaveCache])) {
            $id_evento = $this->cacheIdsEventos[$chaveCache];
        } else {
            // Fallback: Se por algum motivo não estiver no cache, busca no banco
            $id_evento = $this->buscar_movimentacoes("{$ano}{$mes}", $cpfCnpjNum);
        }

        $id_formatado = $this->gerarIdFormatado($id_evento);

        $evtMovOpFin = $dom->createElementNS($namespace, 'evtMovOpFin');
        $evtMovOpFin->setAttribute('id', $id_formatado);
        $eFinanceira->appendChild($evtMovOpFin);

        // ideEvento - grupo
        $ideEvento = $dom->createElementNS($namespace, 'ideEvento');
        $evtMovOpFin->appendChild($ideEvento);

        // indRetificacao - 1 para original 2 para retificação 3 para retificação a pedido
        $indRetificacao = $dom->createElementNS($namespace, 'indRetificacao', '1');
        $ideEvento->appendChild($indRetificacao);

        // tpAmb
        $tpAmb = $dom->createElementNS($namespace, 'tpAmb', '1');
        $ideEvento->appendChild($tpAmb);

        // aplicEmi
        $aplicEmi = $dom->createElementNS($namespace, 'aplicEmi', '1');
        $ideEvento->appendChild($aplicEmi);

        // verAplic
        $verAplic = $dom->createElementNS($namespace, 'verAplic', $this->versao_aplicacao);
        $ideEvento->appendChild($verAplic);

        // ideDeclarante - grupo
        $ideDeclarante = $dom->createElementNS($namespace, 'ideDeclarante');
        $evtMovOpFin->appendChild($ideDeclarante);

        $cnpjDeclarante = $dom->createElementNS($namespace, 'cnpjDeclarante', $this->cnpjEPP);
        $ideDeclarante->appendChild($cnpjDeclarante);

        // ideDeclarado -  grupo
        $ideDeclarado = $dom->createElementNS($namespace, 'ideDeclarado');
        $evtMovOpFin->appendChild($ideDeclarado);

        // tipo NI 1-cpf 2-cnpj
        $tpNI = $dom->createElementNS($namespace, 'tpNI', $tipoNI);
        $ideDeclarado->appendChild($tpNI);

        // NIDeclarado cpf ou cnpj
        $NIDeclarado = $dom->createElementNS($namespace, 'NIDeclarado', $cpfCnpjNum);
        $ideDeclarado->appendChild($NIDeclarado);

        // NomeDeclarado
        $NomeDeclarado = $dom->createElementNS($namespace, 'NomeDeclarado', substr($nomeDeclarado, 0, 100));
        $ideDeclarado->appendChild($NomeDeclarado);

        if ($tipoNI == 1) {
            // DataNasc
            $DataNasc = $dom->createElementNS($namespace, 'DataNasc', $dataNascimento);
            $ideDeclarado->appendChild($DataNasc);
        }

        // EnderecoLivre
        $EnderecoLivre = $dom->createElementNS($namespace, 'EnderecoLivre', substr($enderecoCliente, 0, 200));
        $ideDeclarado->appendChild($EnderecoLivre);

        if ($enderecoCliente == "Endereco cliente nao encontrado") {
            // tpEndereco
            $tpEndereco = $dom->createElementNS($namespace, 'tpEndereco', 'OECD305');
            $ideDeclarado->appendChild($tpEndereco);
        }

        //PaisEndereco - grupo
        $PaisEndereco = $dom->createElementNS($namespace, 'PaisEndereco');
        $ideDeclarado->appendChild($PaisEndereco);

        //Pais
        $Pais = $dom->createElementNS($namespace, 'Pais', 'BR');
        $PaisEndereco->appendChild($Pais);

        //mesCaixa - grupo
        $mesCaixa = $dom->createElementNS($namespace, 'mesCaixa');
        $evtMovOpFin->appendChild($mesCaixa);

        //anoMesCaixa
        $anoMesCaixa = $dom->createElementNS($namespace, 'anoMesCaixa', "{$ano}{$mes}");
        $mesCaixa->appendChild($anoMesCaixa);

        //movOpFin - grupo
        $movOpFin = $dom->createElementNS($namespace, 'movOpFin');
        $mesCaixa->appendChild($movOpFin);

        //Conta - grupo
        foreach ($contas_user as $conta_user) {
            $ug_id = $conta_user['ug_id'];
            $entradas = $conta_user['entradas'];
            $saidas = $conta_user['saidas'];
            $tipo_relacao = $conta_user['tipo_relacao'];

            $Conta = $dom->createElementNS($namespace, 'Conta');
            $movOpFin->appendChild($Conta);

            //infoConta - grupo
            $infoConta = $dom->createElementNS($namespace, 'infoConta');
            $Conta->appendChild($infoConta);

            //Reportavel - grupo
            $Reportavel = $dom->createElementNS($namespace, 'Reportavel');
            $infoConta->appendChild($Reportavel);

            //Pais
            $PaisReportavel = $dom->createElementNS($namespace, 'Pais', 'BR');
            $Reportavel->appendChild($PaisReportavel);

            //tpConta 1 deposito
            $tpConta = $dom->createElementNS($namespace, 'tpConta', '1');
            $infoConta->appendChild($tpConta);

            //subTpConta 105 conta pré paga 
            $subTpConta = $dom->createElementNS($namespace, 'subTpConta', '105');
            $infoConta->appendChild($subTpConta);

            //tpNumConta
            $tpNumConta = $dom->createElementNS($namespace, 'tpNumConta', 'OECD602');
            $infoConta->appendChild($tpNumConta);

            //numConta
            $numConta = $dom->createElementNS($namespace, 'numConta', $ug_id);
            $infoConta->appendChild($numConta);

            //tpRelacaoDeclarado
            $tpRelacaoDeclarado = $dom->createElementNS($namespace, 'tpRelacaoDeclarado', $tipo_relacao);
            $infoConta->appendChild($tpRelacaoDeclarado);

            //BRL moeda
            $moeda = $dom->createElementNS($namespace, 'moeda', 'BRL');
            $infoConta->appendChild($moeda);

            //NoTitulares
            $NoTitulares = $dom->createElementNS($namespace, 'NoTitulares', '1');
            $infoConta->appendChild($NoTitulares);

            //dtEncerramentoConta RESOLVER DEPOIS

            //IndInatividade RESOLVER DEPOIS 6 ANOS INATIV

            //BalancoConta grupo
            $BalancoConta = $dom->createElementNS($namespace, 'BalancoConta');
            $infoConta->appendChild($BalancoConta);

            //totCreditos

            $entradas_formatadas = number_format($entradas, 2, ',', '');
            $totCreditos = $dom->createElementNS($namespace, 'totCreditos', $entradas_formatadas);
            $BalancoConta->appendChild($totCreditos);

            //totDebitos
            $saidas_formatadas = number_format($saidas, 2, ',', '');
            $totDebitos = $dom->createElementNS($namespace, 'totDebitos', $saidas_formatadas);
            $BalancoConta->appendChild($totDebitos);

            //totCreditosMesmaTitularidade
            $totCreditosMesmaTitularidade = $dom->createElementNS($namespace, 'totCreditosMesmaTitularidade', '0,00');
            $BalancoConta->appendChild($totCreditosMesmaTitularidade);

            //totDebitosMesmaTitularidade
            $totDebitosMesmaTitularidade = $dom->createElementNS($namespace, 'totDebitosMesmaTitularidade', '0,00');
            $BalancoConta->appendChild($totDebitosMesmaTitularidade);

            //vlrUltDia RESOLVER DEPOIS SO MES DEZEMBRO

            //PgtosAcum - grupo
            $PgtosAcum = $dom->createElementNS($namespace, 'PgtosAcum');
            $infoConta->appendChild($PgtosAcum);

            //tpPgto
            $tpPgto = $dom->createElementNS($namespace, 'tpPgto', '999');
            $PgtosAcum->appendChild($tpPgto);

            //totPgtosAcum
            $totPgtosAcum = $dom->createElementNS($namespace, 'totPgtosAcum', '0,00');
            $PgtosAcum->appendChild($totPgtosAcum);
        }

        // Gerar XML final
        return ['xml' => $dom, 'id' => $id_formatado];
    }

    public function gerarCadastroDeclarante()
    {
        $namespace = 'http://www.eFinanceira.gov.br/schemas/evtCadDeclarante/v1_2_0';

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = true;

        // Elemento raiz <eFinanceira> com namespace correto
        $eFinanceira = $dom->createElementNS($namespace, 'eFinanceira');
        $dom->appendChild($eFinanceira);

        // Gera ID único do evento
        $idNovo = $this->obterUltimoIdEnvio() + 1;
        $id_formatado = $this->gerarIdFormatado($idNovo);

        // Cria <evtCadDeclarante> dentro do <eFinanceira>
        $evtCadDeclarante = $dom->createElementNS($namespace, 'evtCadDeclarante');
        $evtCadDeclarante->setAttribute('id', $id_formatado);
        $eFinanceira->appendChild($evtCadDeclarante);

        // ideEvento
        $ideEvento = $dom->createElementNS($namespace, 'ideEvento');
        $ideEvento->appendChild($dom->createElementNS($namespace, 'indRetificacao', '1'));
        $ideEvento->appendChild($dom->createElementNS($namespace, 'tpAmb', '1'));
        $ideEvento->appendChild($dom->createElementNS($namespace, 'aplicEmi', '1'));
        $ideEvento->appendChild($dom->createElementNS($namespace, 'verAplic', $this->versao_aplicacao));
        $evtCadDeclarante->appendChild($ideEvento);

        // ideDeclarante
        $ideDeclarante = $dom->createElementNS($namespace, 'ideDeclarante');
        $ideDeclarante->appendChild($dom->createElementNS($namespace, 'cnpjDeclarante', $this->cnpjEPP));
        $evtCadDeclarante->appendChild($ideDeclarante);

        // infoCadastro
        $infoCadastro = $dom->createElementNS($namespace, 'infoCadastro');
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'nome', $this->razaoEPP));
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'enderecoLivre', $this->enderecoEPP));
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'municipio', $this->codMunicipioEPP));
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'UF', $this->ufEPP));
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'CEP', $this->cepEPP));
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'Pais', 'BR'));

        $paisResid = $dom->createElementNS($namespace, 'paisResid');
        $paisResid->appendChild($dom->createElementNS($namespace, 'Pais', 'BR'));
        $infoCadastro->appendChild($paisResid);

        $evtCadDeclarante->appendChild($infoCadastro);

        return ['xml' => $dom, 'id' => $id_formatado];
    }


    public function gerarAbertura($data_inicio, $data_fim)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = true;

        $namespace = 'http://www.eFinanceira.gov.br/schemas/evtAberturaeFinanceira/v1_2_1';

        // <eFinanceira>
        $eFinanceira = $dom->createElementNS($namespace, 'eFinanceira');
        $dom->appendChild($eFinanceira);

        // <evtAberturaeFinanceira id="...">
        $idNovo = $this->buscar_aberturas($data_inicio, $data_fim);

        $id_formatado = $this->gerarIdFormatado($idNovo);
        $evt = $dom->createElementNS($namespace, 'evtAberturaeFinanceira');
        $evt->setAttribute('id', $id_formatado); // Identificador único do evento
        $eFinanceira->appendChild($evt);

        // <ideEvento>
        $ideEvento = $dom->createElementNS($namespace, 'ideEvento');
        // Indicativo de Retificação: 1 - Original, 2 - Retificador, 3 - Retificador a Pedido
        $indRetificacao = $dom->createElementNS($namespace, 'indRetificacao', '1');
        $ideEvento->appendChild($indRetificacao);
        // Identificação do ambiente: 1 - Produção, 2 - Homologação
        $tpAmb = $dom->createElementNS($namespace, 'tpAmb', '1');
        $ideEvento->appendChild($tpAmb);
        // Processo de emissão do evento: 1 - Emissão com aplicativo da empresa
        $aplicEmi = $dom->createElementNS($namespace, 'aplicEmi', '1');
        $ideEvento->appendChild($aplicEmi);
        // Versão do aplicativo de emissão do evento
        $verAplic = $dom->createElementNS($namespace, 'verAplic', $this->versao_aplicacao);
        $ideEvento->appendChild($verAplic);

        $evt->appendChild($ideEvento);

        // <ideDeclarante>
        $ideDeclarante = $dom->createElementNS($namespace, 'ideDeclarante');
        // CNPJ da Entidade Declarante
        $cnpjDeclarante = $dom->createElementNS($namespace, 'cnpjDeclarante', $this->cnpjEPP);
        $ideDeclarante->appendChild($cnpjDeclarante);

        $evt->appendChild($ideDeclarante);

        // <infoAbertura>
        $infoAbertura = $dom->createElementNS($namespace, 'infoAbertura');
        // Data Inicial
        $dtInicio = $dom->createElementNS($namespace, 'dtInicio', $data_inicio);
        $infoAbertura->appendChild($dtInicio);
        // Data Final
        $dtFim = $dom->createElementNS($namespace, 'dtFim', $data_fim);
        $infoAbertura->appendChild($dtFim);

        $evt->appendChild($infoAbertura);

        // <AberturaMovOpFin> informações obrigatórias
        $aberturaMov = $dom->createElementNS($namespace, 'AberturaMovOpFin');

        // <ResponsavelRMF>
        $responsavel = $dom->createElementNS($namespace, 'ResponsavelRMF');
        // CNPJ da entidade responsável pela RMF
        $cnpj = $dom->createElementNS($namespace, 'CNPJ', $this->cnpjEPP);
        $responsavel->appendChild($cnpj);
        // CPF
        $cpf = $dom->createElementNS($namespace, 'CPF', $this->cpfRespRMF);
        $responsavel->appendChild($cpf);
        // Nome
        $nome = $dom->createElementNS($namespace, 'Nome', $this->nomeRespRMF);
        $responsavel->appendChild($nome);
        // Setor
        $setor = $dom->createElementNS($namespace, 'Setor', 'Financeiro');
        $responsavel->appendChild($setor);

        // Telefone
        $telefone = $dom->createElementNS($namespace, 'Telefone');
        // DDD
        $ddd = $dom->createElementNS($namespace, 'DDD', $this->dddRespRMF);
        $telefone->appendChild($ddd);
        // Número
        $numero = $dom->createElementNS($namespace, 'Numero', $this->foneRespRMF);
        $telefone->appendChild($numero);
        $responsavel->appendChild($telefone);

        // Endereço
        $endereco = $dom->createElementNS($namespace, 'Endereco');
        // Logradouro
        $logradouro = $dom->createElementNS($namespace, 'Logradouro', $this->enderecoEPP);
        $endereco->appendChild($logradouro);
        // Número
        $num = $dom->createElementNS($namespace, 'Numero', $this->numeroEPP);
        $endereco->appendChild($num);
        //Complemento
        $complemento = $dom->createElementNS($namespace, 'Complemento', $this->complementoEPP);
        $endereco->appendChild($complemento);
        // Bairro
        $bairro = $dom->createElementNS($namespace, 'Bairro', $this->bairroEPP);
        $endereco->appendChild($bairro);
        // CEP
        $cep = $dom->createElementNS($namespace, 'CEP', $this->cepEPP);
        $endereco->appendChild($cep);
        // Município
        $municipio = $dom->createElementNS($namespace, 'Municipio', $this->municipioEPP);
        $endereco->appendChild($municipio);
        // UF
        $uf = $dom->createElementNS($namespace, 'UF', $this->ufEPP);
        $endereco->appendChild($uf);

        $responsavel->appendChild($endereco);

        $aberturaMov->appendChild($responsavel);

        // <RespeFin> responsável pela e-Financeira
        $respeFin = $dom->createElementNS($namespace, 'RespeFin');
        // CPF
        $cpfRF = $dom->createElementNS($namespace, 'CPF', $this->cpfRespeFIN);
        $respeFin->appendChild($cpfRF);
        // Nome
        $nomeRF = $dom->createElementNS($namespace, 'Nome', $this->nomeRespeFIN);
        $respeFin->appendChild($nomeRF);
        // Setor
        $setorRF = $dom->createElementNS($namespace, 'Setor', $this->setorRespeFIN);
        $respeFin->appendChild($setorRF);
        // Telefone
        $telRF = $dom->createElementNS($namespace, 'Telefone');
        // DDD
        $dddRF = $dom->createElementNS($namespace, 'DDD', $this->dddRespeFIN);
        $telRF->appendChild($dddRF);
        // Número
        $numRF = $dom->createElementNS($namespace, 'Numero', $this->foneRespeFIN);
        $telRF->appendChild($numRF);
        $respeFin->appendChild($telRF);

        // Cria o nó <Endereco>
        $enderecoRespEfin = $dom->createElementNS($namespace, 'Endereco');

        // Adiciona os filhos de <Endereco>
        $logradouroEfin = $dom->createElementNS($namespace, 'Logradouro', $this->enderecoRespeFIN);
        $numeroEfin = $dom->createElementNS($namespace, 'Numero', $this->numeroRespeFIN);
        $bairroEfin = $dom->createElementNS($namespace, 'Bairro', $this->bairroRespeFIN);
        $cepEfin = $dom->createElementNS($namespace, 'CEP', $this->cepRespeFIN);
        $municipioEfin = $dom->createElementNS($namespace, 'Municipio', $this->municipioRespeFIN);
        $ufEfin = $dom->createElementNS($namespace, 'UF', $this->ufRespeFIN);
        $ComplementoEfin = $dom->createElementNS($namespace, 'Complemento', $this->complementoRespeFIN);

        // Monta o elemento <Endereco>
        $enderecoRespEfin->appendChild($logradouroEfin);
        $enderecoRespEfin->appendChild($numeroEfin);
        $enderecoRespEfin->appendChild($ComplementoEfin);
        $enderecoRespEfin->appendChild($bairroEfin);
        $enderecoRespEfin->appendChild($cepEfin);
        $enderecoRespEfin->appendChild($municipioEfin);
        $enderecoRespEfin->appendChild($ufEfin);

        // Cria o elemento <Email>
        $emaiRespEfin = $dom->createElementNS($namespace, 'Email', $this->emailRespeFIN);

        // Adiciona <Endereco> e <Email> dentro de <RespeFin>
        $respeFin->appendChild($enderecoRespEfin);
        $respeFin->appendChild($emaiRespEfin);

        $aberturaMov->appendChild($respeFin);

        //RepresLegal
        $represLegal = $dom->createElementNS($namespace, 'RepresLegal');

        // CPF
        $cpfRL = $dom->createElementNS($namespace, 'CPF', $this->cpfReprLegal);
        $represLegal->appendChild($cpfRL);
        // Setor
        $setorRL = $dom->createElementNS($namespace, 'Setor', $this->setorReprLegal);
        $represLegal->appendChild($setorRL);
        // Telefone
        $telRL = $dom->createElementNS($namespace, 'Telefone');
        // DDD
        $dddRL = $dom->createElementNS($namespace, 'DDD', $this->dddTelefoneReprLegal);
        $telRL->appendChild($dddRL);
        // Número
        $numRL = $dom->createElementNS($namespace, 'Numero', $this->telefoneReprLegal);
        $telRL->appendChild($numRL);
        $represLegal->appendChild($telRL);

        $aberturaMov->appendChild($represLegal);

        $evt->appendChild($aberturaMov);

        // Gerar XML
        return ['xml' => $dom, 'id' => $id_formatado];
    }

    /**
     * Gera o XML de Fechamento (Versão 1.3.0)
     * * @param string $dataInicioSemestre Formato YYYY-MM-DD
     * @param string $dataFimSemestre    Formato YYYY-MM-DD
     * @param bool   $temMovimento       true = Teve movimento, false = Sem movimento
     */
    public function gerarFechamento($dataInicioSemestre, $dataFimSemestre, $temMovimento)
    {
        // 1. Definição dos Namespaces (Versão 1.3.0)
        $ns = 'http://www.eFinanceira.gov.br/schemas/evtFechamentoeFinanceira/v1_3_0';

        // 2. Busca ID e Formata
        // Nota: Assumindo que você tem lógica para buscar/criar o ID de fechamento
        $idNovo = $this->buscar_fechamento($dataInicioSemestre, $dataFimSemestre);
        $id_formatado = $this->gerarIdFormatado($idNovo);

        $ambiente = '1'; // 1 = Produção, 2 = Homologação (Pode virar parâmetro se quiser)

        // 3. Criação do Documento DOM
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;
        $doc->preserveWhiteSpace = true;

        // 4. Elemento Raiz
        $eFinanceira = $doc->createElementNS($ns, 'eFinanceira');
        $doc->appendChild($eFinanceira);

        // 5. Evento
        $evtFechamento = $doc->createElementNS($ns, 'evtFechamentoeFinanceira');
        $evtFechamento->setAttribute('id', $id_formatado);
        $eFinanceira->appendChild($evtFechamento);

        // 6. ideEvento
        $ideEvento = $doc->createElementNS($ns, 'ideEvento');
        $evtFechamento->appendChild($ideEvento);

        $ideEvento->appendChild($doc->createElementNS($ns, 'indRetificacao', '1'));
        $ideEvento->appendChild($doc->createElementNS($ns, 'tpAmb', $ambiente));
        $ideEvento->appendChild($doc->createElementNS($ns, 'aplicEmi', '1'));
        $ideEvento->appendChild($doc->createElementNS($ns, 'verAplic', $this->versao_aplicacao));

        // 7. ideDeclarante
        $ideDeclarante = $doc->createElementNS($ns, 'ideDeclarante');
        $evtFechamento->appendChild($ideDeclarante);

        $ideDeclarante->appendChild($doc->createElementNS($ns, 'cnpjDeclarante', $this->cnpjEPP));

        // 8. infoFechamento
        $infoFechamento = $doc->createElementNS($ns, 'infoFechamento');
        $evtFechamento->appendChild($infoFechamento);

        $infoFechamento->appendChild($doc->createElementNS($ns, 'dtInicio', $dataInicioSemestre));
        $infoFechamento->appendChild($doc->createElementNS($ns, 'dtFim', $dataFimSemestre));
        $infoFechamento->appendChild($doc->createElementNS($ns, 'sitEspecial', '0'));

        // Se NÃO tiver movimento nenhum (nem financeiro, nem previdência, nada), 
        // a versão 1.3.0 permite usar a tag abaixo. 
        // Mas geralmente enviamos o grupo FechamentoMovOpFin zerado para ser mais específico.
        /*
        if (!$temMovimento) {
            $infoFechamento->appendChild($doc->createElementNS($ns, 'nadaADeclarar', '1'));
        }
        */

        // 9. Grupo: FechamentoMovOpFin
        // Define o indicador: '1' se true, '0' se false
        $indicador = $temMovimento ? '1' : '0';

        $fechamentoMovOpFinGroup = $doc->createElementNS($ns, 'FechamentoMovOpFin');
        $evtFechamento->appendChild($fechamentoMovOpFinGroup);

        // Tag Filha: FechamentoMovOpFin (Flag 0 ou 1)
        // Sim, o nome da tag é igual ao do grupo pai no Schema
        $fechamentoMovOpFinGroup->appendChild($doc->createElementNS($ns, 'FechamentoMovOpFin', $indicador));

        // Nota: Se houver info de FATCA (EntDecExterior), adiciona aqui dentro do $fechamentoMovOpFinGroup

        return ['xml' => $doc, 'id' => $id_formatado];
    }

    /**
     * Gera o XML do lote de eventos como uma STRING.
     * Espera que os eventos já sejam strings XML assinadas.
     *
     * @param array $eventos Array de eventos, ex: [['id' => 'ID0', 'xml' => '<eFinanceira...']]
     * @return string O XML completo do lote (sem criptografia).
     * @throws Exception
     */
    public function gerarLoteAssincrono(array $eventos)
    {
        $nsLote = 'http://www.eFinanceira.gov.br/schemas/envioLoteEventosAssincrono/v1_0_0';

        // 1. Inicia a string do XML com a estrutura do lote (como no exemplo)
        // Adiciona os atributos xsi e xsd que faltavam
        $xmlString = '<?xml version="1.0" encoding="utf-8"?>' .
            '<eFinanceira xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="' . $nsLote . '">' .
            '<loteEventosAssincrono>' .
            '<cnpjDeclarante>' . $this->cnpjEPP . '</cnpjDeclarante>' .
            '<eventos>';

        // 2. Loop para injetar as strings dos eventos
        foreach ($eventos as $ev) {
            if (!isset($ev['id'], $ev['xml'])) {
                throw new Exception('Não foi passado um evento válido para o lote.');
            }
            if (!is_string($ev['xml'])) {
                $ev['xml'] = $ev['xml']->saveXML($ev['xml']->documentElement);
            }

            // 3. Anexa o evento
            // Isso injeta a string <eFinanceira... do evento dentro da tag <evento>
            $xmlString .= '<evento id="' . $ev['id'] . '">';
            $xmlString .= $ev['xml']; // Injeta a string do evento assinado aqui
            $xmlString .= '</evento>';
        }

        // 4. Fecha as tags do lote
        $xmlString .= '</eventos>' .
            '</loteEventosAssincrono>' .
            '</eFinanceira>';

        // 5. Retorna a string XML completa
        return $xmlString;
    }

    private function obterTagEventoAssinar(DOMElement $eventoElement)
    {
        // Lista de tags de eventos da e-Financeira
        $tiposEventos = [
            'evtCadDeclarante',
            'evtAberturaeFinanceira',
            'evtCadIntermediario',
            'evtCadPatrocinado',
            'evtExclusaoeFinanceira',
            'evtExclusao',
            'evtFechamentoeFinanceira',
            'evtMovOpFin',
            'evtMovPP'
        ];

        $xml = $eventoElement->ownerDocument->saveXML($eventoElement);

        foreach ($tiposEventos as $tipo) {
            if (strpos($xml, $tipo) !== false) {
                return $tipo;
            }
        }

        return null;
    }


    private function buscarElementoEventoPorTag(DOMElement $eventoElement, $tagEvento)
    {
        // Busca o elemento por nome local (ignora namespace)
        $xpath = new DOMXPath($eventoElement->ownerDocument);

        // Procura em qualquer namespace: //*[local-name()='evtCadDeclarante' and @id]
        $query = sprintf(".//*[local-name()='%s' and @id]", $tagEvento);
        $resultado = $xpath->query($query, $eventoElement);

        if ($resultado->length > 0) {
            return $resultado->item(0);
        }

        return null;
    }


    private function buscarElementoEFinanceira(DOMElement $eventoElement)
    {
        // Busca o elemento <eFinanceira> dentro do <evento>
        // Pode ter qualquer namespace ou nenhum
        $xpath = new DOMXPath($eventoElement->ownerDocument);
        $query = ".//*[local-name()='eFinanceira']";
        $resultado = $xpath->query($query, $eventoElement);

        if ($resultado->length > 0) {
            return $resultado->item(0);
        }

        return null;
    }

    private function is_xml($data)
    {
        if (empty($data)) return false;

        // Desativa erros do libxml para não poluir o log e limpa depois
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($data);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return $doc !== false && empty($errors);
    }

    public function assinarLoteEventos($xml)
    {
        $senha = $this->senhaCertificado;

        $ch = curl_init('http://assinador:5000/assinar');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'xml'  => $xml,
            'senha' => $senha
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resposta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resposta === false) {
            throw new Exception("Falha ao chamar serviço de assinatura");
        }
        if ($httpCode !== 200) {
            throw new Exception("O serviço retornou um erro HTTP: $httpCode. Resposta: " . substr($resposta, 0, 100));
        }
        if (!$this->is_xml($resposta)) {
            throw new Exception("A resposta do serviço não é um XML válido.");
        }

        return $resposta;
    }

    public function criptografarLoteEF($xmlConteudo, $prod = false)
    {
        //$certPath = '/certs/efinanceira.cer'; // caminho do certificado público (ajuste conforme seu container)

        $ch = curl_init('http://assinador:5000/criptografar');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Envia via multipart/form-data, igual ao C# espera (ReadFormAsync)
        if ($prod) {
            $postFields = [
                'xml' => $xmlConteudo,
                'prod' => "true"
            ];
        } else {
            $postFields = [
                'xml' => $xmlConteudo
            ];
        }


        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $resposta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resposta === false || $httpCode !== 200) {
            throw new Exception("Falha ao chamar serviço de criptografia (HTTP $httpCode): " . $resposta);
        }
        if (!$this->is_xml($resposta)) {
            throw new Exception("A resposta do serviço não é um XML válido.");
        }

        return $resposta;
    }

    public function atualizarLoteParaEnviado(array $ids, $protocolo, $nomeArquivo)
    {
        if (empty($ids)) {
            return 0;
        }

        $pdo = ConnectionPDO::getConnection()->getLink();
        $totalLinhasAfetadas = 0;

        // Divide o array de IDs em pedaços de 1000
        $lotesDeIds = array_chunk($ids, 1000);

        foreach ($lotesDeIds as $loteAtual) {

            $placeholders = implode(',', array_fill(0, count($loteAtual), '?'));

            $sql = "UPDATE envios_e_financeira 
                SET status_envio = 'ENVIADO',
                    nome_arquivo = ?,
                    num_protocolo = ?,
                    data_envio = NOW()
                WHERE id IN ($placeholders)";

            try {
                $stmt = $pdo->prepare($sql);

                $params = [$nomeArquivo, $protocolo];

                $params = array_merge($params, $loteAtual);

                $stmt->execute($params);

                $totalLinhasAfetadas += $stmt->rowCount();
            } catch (PDOException $e) {
                throw new Exception("Erro no Bulk Update: " . $e->getMessage());
            }
        }

        return $totalLinhasAfetadas;
    }

    private function criarEnvioFinanceira(array $dados)
    {
        // 1. Definição da Query SQL
        $pdo = ConnectionPDO::getConnection()->getLink();

        $sql = "INSERT INTO envios_e_financeira (
            tipo, 
            status_envio, 
            versao_efin, 
            versao_epp, 
            nome_arquivo, 
            data_envio, 
            semestre_ano, 
            retificado, 
            descricao,
            cpfcnpj_declarado,
            data_anomes,
            id_retificacao,
            num_protocolo
        ) VALUES (
            :tipo, 
            :status_envio, 
            :versao_efin, 
            :versao_epp, 
            :nome_arquivo, 
            :data_envio, 
            :semestre_ano, 
            :retificado, 
            :descricao,
            :cpfcnpj_declarado,
            :data_anomes,
            :id_retificacao,
            :num_protocolo
        )";

        try {
            $stmt = $pdo->prepare($sql);

            $params = [
                // Campos Obrigatórios
                ':tipo'         => $dados['tipo'],
                ':status_envio' => $dados['status_envio'],
                ':versao_efin'  => $dados['versao_efin'],
                ':versao_epp'   => $dados['versao_epp'],
                ':nome_arquivo' => $dados['nome_arquivo'],

                // Campos Opcionais
                ':data_envio'   => $dados['data_envio'] ?? null,
                ':semestre_ano' => $dados['semestre_ano'] ?? null,
                ':retificado'   => $dados['retificado'] ?? 'false',
                ':descricao'    => $dados['descricao'] ?? null,
                ':cpfcnpj_declarado' => $dados['cpfcnpj_declarado'] ?? null,
                ':data_anomes'       => $dados['data_anomes'] ?? null,
                ':id_retificacao'    => $dados['id_retificacao'] ?? null,
                ':num_protocolo'     => $dados['num_protocolo'] ?? null,
            ];

            $stmt->execute($params);

            // Retornar o ID gerado
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erro ao inserir em envios_e_financeira: " . $e->getMessage());
        }
    }

    public function enviarLoteEFinanceira($xmlLoteCriptografado, $usarGzip = false, $producao = false)
    {
        // Definir endpoint
        if ($producao) {
            //$urlBase = 'https://efinanceira.receita.fazenda.gov.br/recepcao/lotes/';
        } else {
            $urlBase = 'https://pre-efinanceira.receita.fazenda.gov.br/recepcao/lotes/';
        }

        $endpoint = $urlBase . ($usarGzip ? 'criptoGzip' : 'cripto');

        // Garantir que o XML seja uma string
        if ($xmlLoteCriptografado instanceof DOMDocument) {
            $xmlLoteCriptografado->preserveWhiteSpace = true;
            $xmlLoteCriptografado->formatOutput = false;
            $xmlString = $xmlLoteCriptografado->saveXML();
        } else {
            $xmlString = $xmlLoteCriptografado;
        }

        // Configurar cURL com autenticação mútua TLS
        $ch = curl_init($endpoint);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xmlString,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/xml',
                'Content-Length: ' . strlen($xmlString)
            ],

            // Autenticação mútua TLS com PFX
            CURLOPT_SSLCERT => $this->certificado, // O caminho para /certs/cert-eprepag.pfx
            CURLOPT_SSLCERTPASSWD => $this->senhaCertificado,
            CURLOPT_SSLCERTTYPE => 'P12',
            // Segurança TLS
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,

            // Timeout
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 30,

            // Debug (remova em produção)
            CURLOPT_VERBOSE => true
        ]);

        // Executar requisição
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        // Verificar erros de conexão
        if ($response === false) {
            throw new Exception("Erro na conexão com e-Financeira: " . $curlError);
        }

        // Processar resposta
        return $response;
    }

    public function consultarLoteEFinanceira($numeroLote, $producao = false)
    {
        // Limpar o número (apenas dígitos)

        if (empty($numeroLote)) {
            throw new Exception("Número de protocolo inválido: vazio");
        }

        //echo "Consultando protocolo de lote: $numeroLote\n";

        // Definir endpoint CORRETO
        if ($producao) {
            $endpoint = "https://efinanceira.receita.fazenda.gov.br/consulta/lotes/{$numeroLote}";
        } else {
            $endpoint = "https://pre-efinanceira.receita.fazenda.gov.br/consulta/lotes/{$numeroLote}";
        }

        //echo "Endpoint: $endpoint\n";

        // IMPORTANTE: Aguardar pelo menos 30 segundos após envio
        // conforme recomendação da documentação

        // Verificar certificado
        if (!file_exists($this->certificado_privado_epp)) {
            throw new Exception("Certificado A1 não encontrado: " . $this->certificado_privado_epp);
        }

        $ch = curl_init($endpoint);

        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/xml',
            ],

            // Autenticação mútua TLS
            CURLOPT_SSLCERT => $this->certificado_privado_epp,
            CURLOPT_SSLCERTTYPE => 'PEM',

            // Segurança TLS
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,

            // Timeout
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 30,
        ];

        // Se usar chave privada separada
        if ($this->chave_privada_epp !== null) {
            $curlOptions[CURLOPT_SSLKEY] = $this->chave_privada_epp;
            $curlOptions[CURLOPT_SSLKEYTYPE] = 'PEM';
        }

        // Se tiver senha
        if (!empty($this->senhaCertificado)) {
            $curlOptions[CURLOPT_SSLCERTPASSWD] = $this->senhaCertificado;
            $curlOptions[CURLOPT_SSLKEYPASSWD] = $this->senhaCertificado;
        }

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new Exception("Erro na conexão: " . $curlError);
        }

        //return $this->processarRespostaConsulta($response, $httpCode);
        return $response;
    }

    /**
     * Consulta Informações Cadastrais
     */
    public function consultarInformacoesCadastrais($cnpj, $producao = false)
    {
        if (empty($cnpj)) {
            throw new Exception("CNPJ é obrigatório.");
        }

        $endpoint = "/informacoes-cadastrais";

        $params = [
            'cnpj' => $cnpj
        ];

        return $this->executarRequestPost($endpoint, $params, $producao);
    }

    /**
     * Consulta Lista e-Financeira (Mov. Financeira e Previdência)
     */
    public function consultarListaEFinanceira($cnpj, $situacaoInformacao, $dataInicio, $dataFim, $producao = false)
    {
        // Validações básicas
        if (empty($cnpj) || empty($dataInicio) || empty($dataFim)) {
            throw new Exception("CNPJ, Data Início e Data Fim são obrigatórios.");
        }

        $endpoint = "/lista-efinanceira-movimento";

        $params = [
            'cnpj'               => $cnpj,
            'situacaoInformacao' => $situacaoInformacao,
            'dataInicio'         => $dataInicio, // Formato esperado geralmente é AAAA-MM-DD ou AAAA-MM-DDTHH:MM:SS
            'dataFim'            => $dataFim
        ];

        return $this->executarRequestPost($endpoint, $params, $producao);
    }

    /**
     * Consulta Informações Movimento Operação Financeira
     */
    public function consultarMovimentoOpFin($cnpj, $situacaoInformacao, $anoMesInicio, $anoMesTermino, $tipoIdentificacao, $identificacao, $producao = false)
    {
        $endpoint = "/informacoes-mov-op-fin";

        $params = [
            'cnpj'                    => $cnpj,
            'situacaoInformacao'      => $situacaoInformacao,
            'anoMesInicioVigencia'    => $anoMesInicio,   // Ex: 202501
            'anoMesTerminoVigencia'   => $anoMesTermino,  // Ex: 202506
            'tipoIdentificacao'       => $tipoIdentificacao,
            'identificacao'           => $identificacao
        ];

        return $this->executarRequestPost($endpoint, $params, $producao);
    }

    /**
     * Consulta Informações Movimento Operação Financeira Anual
     */
    public function consultarMovimentoOpFinAnual($cnpj, $situacaoInformacao, $anoMesInicio, $anoMesTermino, $tipoIdentificacao, $identificacao, $producao = false)
    {
        $endpoint = "/informacoes-mov-op-fin-anual";

        $params = [
            'cnpj'                    => $cnpj,
            'situacaoInformacao'      => $situacaoInformacao,
            'anoMesInicioVigencia'    => $anoMesInicio,
            'anoMesTerminoVigencia'   => $anoMesTermino,
            'tipoIdentificacao'       => $tipoIdentificacao,
            'identificacao'           => $identificacao
        ];

        return $this->executarRequestPost($endpoint, $params, $producao);
    }

    /**
     * Método auxiliar privado para realizar o POST com cURL
     * Reutiliza a lógica de certificado do seu exemplo
     */
    private function executarRequestPost($endpointSuffix, $params, $producao)
    {
        // Define a URL Base
        $baseUrl = $producao
            ? "https://efinanceira.receita.fazenda.gov.br/consulta" // Ajustar se a base for diferente de /consulta
            : "https://pre-efinanceira.receita.fazenda.gov.br/consulta";

        $urlCompleta = $baseUrl . $endpointSuffix;

        // Verificar certificado (igual ao seu código original)
        if (!file_exists($this->certificado_privado_epp)) {
            throw new Exception("Certificado A1 não encontrado: " . $this->certificado_privado_epp);
        }

        $ch = curl_init($urlCompleta);

        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,

            // CONFIGURAÇÃO DE POST PARA FORM-URLENCODED
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params), // Transforma o array em string query

            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/xml', // Geralmente a resposta é XML
                'Content-Length: ' . strlen(http_build_query($params))
            ],

            // Configurações de SSL (Mantidas do seu exemplo)
            CURLOPT_SSLCERT        => $this->certificado_privado_epp,
            CURLOPT_SSLCERTTYPE    => 'PEM',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2,

            // Timeouts
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 30,
        ];

        // Se usar chave privada separada
        if ($this->chave_privada_epp !== null) {
            $curlOptions[CURLOPT_SSLKEY] = $this->chave_privada_epp;
            $curlOptions[CURLOPT_SSLKEYTYPE] = 'PEM';
        }

        // Se tiver senha
        if (!empty($this->senhaCertificado)) {
            $curlOptions[CURLOPT_SSLCERTPASSWD] = $this->senhaCertificado;
            $curlOptions[CURLOPT_SSLKEYPASSWD] = $this->senhaCertificado;
        }

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false) {
            throw new Exception("Erro na conexão cURL ({$endpointSuffix}): " . $curlError);
        }

        // Você pode tratar o HTTP Code aqui se quiser
        if ($httpCode >= 400) {
            // throw new Exception("Erro HTTP $httpCode: " . $response);
        }

        return $response;
    }

    public function consultarDetalhesPorProtocolo($tipo, $numeroProtocolo, $producao = false)
    {
        if (empty($numeroProtocolo)) {
            throw new Exception("Número de protocolo inválido ou vazio.");
        }

        // 2. Mapeamento dos Endpoints (Conforme sua lista)
        $mapaEndpoints = [
            'cadastro'      => 'informacoes-cadastrais',
            'lista'         => 'lista-efinanceira-movimento',
            'mov_fin'       => 'informacoes-mov-op-fin',
            'mov_fin_anual' => 'informacoes-mov-op-fin-anual',
        ];

        if (!array_key_exists($tipo, $mapaEndpoints)) {
            throw new Exception("Tipo de consulta por protocolo desconhecido: " . $tipo);
        }

        $sufixoUrl = $mapaEndpoints[$tipo];

        // 3. Definição da URL Base
        $baseUrl = $producao
            ? "https://efinanceira.receita.fazenda.gov.br/consulta"
            : "https://pre-efinanceira.receita.fazenda.gov.br/consulta";

        // Monta a URL final: {Base}/{Sufixo}/{Protocolo}
        $urlCompleta = "{$baseUrl}/{$sufixoUrl}/{$numeroProtocolo}";

        echo $urlCompleta;

        // 4. Executa a requisição GET
        return $this->executarRequestGet($urlCompleta);
    }

    /**
     * Método auxiliar privado para requisições GET (Reutiliza configurações de SSL)
     */
    private function executarRequestGet($url)
    {
        // Verificar certificado
        if (!file_exists($this->certificado_privado_epp)) {
            throw new Exception("Certificado A1 não encontrado: " . $this->certificado_privado_epp);
        }

        $ch = curl_init($url);

        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET        => true, // Força método GET
            CURLOPT_HTTPHEADER     => [
                'Accept: application/xml',
            ],

            // Autenticação mútua TLS (Mesma lógica do consultarLote)
            CURLOPT_SSLCERT        => $this->certificado_privado_epp,
            CURLOPT_SSLCERTTYPE    => 'PEM',

            // Segurança TLS
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2,

            // Timeouts
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 30,
        ];

        // Se usar chave privada separada
        if ($this->chave_privada_epp !== null) {
            $curlOptions[CURLOPT_SSLKEY] = $this->chave_privada_epp;
            $curlOptions[CURLOPT_SSLKEYTYPE] = 'PEM';
        }

        // Se tiver senha
        if (!empty($this->senhaCertificado)) {
            $curlOptions[CURLOPT_SSLCERTPASSWD] = $this->senhaCertificado;
            $curlOptions[CURLOPT_SSLKEYPASSWD] = $this->senhaCertificado;
        }

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false) {
            throw new Exception("Erro na conexão cURL: " . $curlError);
        }

        // Opcional: Tratar erros 404/500 se quiser lançar exception
        // if ($httpCode >= 400) { throw new Exception("Erro HTTP $httpCode"); }

        return $response;
    }

    public function validarLoteAssinado($xmlAssinado)
    {
        $senha = $this->senhaCertificado;

        $ch = curl_init('http://assinador:5000/validar');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'xml'  => $xmlAssinado,
            'senha' => $senha
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resposta = curl_exec($ch);
        curl_close($ch);

        if ($resposta === false) {
            throw new Exception("Falha ao chamar serviço de assinatura");
        }

        return $resposta;
    }
}
