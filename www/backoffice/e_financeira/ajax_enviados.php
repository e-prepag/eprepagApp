<?php
require_once '/www/includes/constantes.php';
require_once __DIR__ . "/../includes/encoding.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
// Requer conexão com banco, supondo que você use algo como $pdo ou funções nativas
// Adapte a chamada da query para o padrão do seu projeto (PDO ou pg_query)

// FUNÇÕES DE FORMATAÇÃO
function gerarIdFormatado($numero)
{
    $prefixo = 'ID';
    $parteNumerica = '10...' . $numero;
    return $prefixo . $parteNumerica;
}

// Recebendo as variáveis do DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;

// Filtros do formulário
$envio_inicio = $_POST['envio_inicio'] ?? '';
$envio_fim = $_POST['envio_fim'] ?? '';
$cpfcnpj = preg_replace('/[^0-9]/', '', $_POST['cpfcnpj'] ?? ''); // Limpa máscara
$tipo = $_POST['tipo'] ?? '';
$status = $_POST['status'] ?? '';
$ano = $_POST['ano'] ?? '';
$semestre = $_POST['semestre'] ?? '';
$competencia = $_POST['competencia'] ?? '';

// Montando a cláusula WHERE
$where = ["1=1"];
$params = [];

if (!empty($envio_inicio)) {
    $where[] = "data_envio >= :envio_inicio";
    $params[':envio_inicio'] = $envio_inicio . " 00:00:00";
}
if (!empty($envio_fim)) {
    $where[] = "data_envio <= :envio_fim";
    $params[':envio_fim'] = $envio_fim . " 23:59:59";
}
if (!empty($tipo)) {
    $where[] = "tipo = :tipo";
    $params[':tipo'] = $tipo;
}
// CPF/CNPJ só aplica se o tipo for movimentação (ou se buscar geral)
if (!empty($cpfcnpj)) {
    $where[] = "cpfcnpj_declarado = :cpfcnpj";
    $params[':cpfcnpj'] = $cpfcnpj;
}
if (!empty($status)) {
    $where[] = "status_envio = :status";
    $params[':status'] = $status;
}
if (!empty($ano) && !empty($semestre)) {
    $where[] = "semestre_ano = :semestre_ano";
    $params[':semestre_ano'] = $ano . "." . $semestre;
}
if (!empty($competencia)) {
    $where[] = "data_anomes LIKE :competencia";
    $params[':competencia'] = "%" . $competencia . "%";
}

$whereClause = implode(" AND ", $where);

$pdo = ConnectionPDO::getConnection()->getLink();

$sqlTotal = "SELECT COUNT(id) FROM envios_e_financeira WHERE $whereClause";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);
$totalRecords = $stmtTotal->fetchColumn();

$sqlData = "SELECT * FROM envios_e_financeira WHERE $whereClause ORDER BY data_envio DESC LIMIT $length OFFSET $start";
$stmtData = $pdo->prepare($sqlData);
$stmtData->execute($params);
$resultados = $stmtData->fetchAll(PDO::FETCH_ASSOC);


// Formatação dos Dados para o DataTables
$data = [];
foreach ($resultados as $row) {

    // 1. Status com Cores
    $status_upper = strtoupper($row['status_envio']);
    $badge = '';
    if ($status_upper === 'ENVIADO') {
        $badge = '<span class="status-enviado">ENVIADO</span>';
    } elseif ($status_upper === 'PENDENTE') {
        $badge = '<span class="status-pendente">PENDENTE</span>';
    } elseif ($status_upper === 'ERRO') {
        $badge = '<span class="status-erro">ERRO</span>';
    } else {
        $badge = '<span class="label label-default">' . $status_upper . '</span>';
    }

    // 2. Data de Envio (Horas e minutos, sem segundos)
    $data_envio = '';
    if (!empty($row['data_envio'])) {
        $data_envio = date('d/m/Y H:i', strtotime($row['data_envio']));
    }

    // 3. Formatação da Competência (Data Anomes)
    $data_anomes_fmt = $row['data_anomes'];
    if (!empty($data_anomes_fmt)) {
        if ($row['tipo'] === 'MOVIMENTACAO' && strpos($data_anomes_fmt, '-') !== false) {
            // 2024-03 -> 03/2024
            $partes = explode('-', (string)($data_anomes_fmt ?? ""));
            if ((is_countable($partes) ? count($partes) : 0) >= 2) {
                $data_anomes_fmt = $partes[1] . '/' . $partes[0];
            }
        } elseif (($row['tipo'] === 'ABERTURA' || $row['tipo'] === 'FECHAMENTO') && strpos($data_anomes_fmt, '_') !== false) {
            // 2024-07-01_2024-12-31 -> 01/07/24 - 31/12/24
            $partes = explode('_', (string)($data_anomes_fmt ?? ""));
            if ((is_countable($partes) ? count($partes) : 0) === 2) {
                $d1 = date('d/m/y', strtotime($partes[0]));
                $d2 = date('d/m/y', strtotime($partes[1]));
                $data_anomes_fmt = $d1 . ' - ' . $d2;
            }
        }
    }

    // 4. Lógica UI/UX dos Botões de Ação
    $acoes_html = '';
    if ($status_upper !== 'PENDENTE') {
        // Agora enviamos o $row['nome_arquivo'] para o Javascript
        $nome_arquivo_js = addslashes($row['nome_arquivo']); // Previne quebra de aspas se houver

        $acoes_html = '
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-primary" onclick="realizarDownload(\'lote\', \'' . $nome_arquivo_js . '\')" title="Baixar Lote original enviado">
                Lote
            </button>
            <button type="button" class="btn btn-sm btn-info" onclick="realizarDownload(\'resposta\', \'' . $nome_arquivo_js . '\')" title="Baixar recibo/resposta da Receita">
                Resposta
            </button>
        </div>';
    } else {
        $acoes_html = '<span class="text-muted" style="font-size: 11px; font-style: italic;">Não enviado</span>';
    }

    // 4. Monta o Array da Linha
    $data[] = [
        "id_formatado" => gerarIdFormatado($row['id']),
        "tipo" => $row['tipo'],
        "status_badge" => $badge,
        "nome_arquivo" => $row['nome_arquivo'],
        "data_envio" => $data_envio,
        "data_anomes_formatado" => $data_anomes_fmt,
        "retificado" => ($row['retificado'] == 't' || $row['retificado'] == 1 || $row['retificado'] === true) ? 'Sim' : 'Não',
        "cpfcnpj_declarado" => $row['cpfcnpj_declarado'],
        "num_protocolo" => $row['num_protocolo'],
        "acoes" => $acoes_html
    ];
}

// 1. Função recursiva para converter todos os textos do array para UTF-8
function converterArrayParaUtf8($dado)
{
    if (is_string($dado)) {
        // A MÁGICA AQUI: Verifica se a string NÃO é um UTF-8 válido.
        // Se falhar no teste, sabemos que veio do banco em ISO-8859-1 e precisa converter.
        if (!mb_check_encoding($dado, 'UTF-8')) {
            return backoffice_iso_to_utf8($dado);
        }

        // Se já for UTF-8 (ou não tiver acentos), devolve do jeito que está
        return $dado;
    } elseif (is_array($dado)) {
        $resultado = [];
        foreach ($dado as $chave => $valor) {
            $resultado[$chave] = converterArrayParaUtf8($valor);
        }
        return $resultado;
    }

    // Retorna números, booleanos ou nulos sem alterar
    return $dado;
}

// 2. Monta a resposta final
$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
];

// 3. Aplica a conversão em todo o pacote de resposta
$responseUtf8 = converterArrayParaUtf8($response);

// 4. Envia o JSON perfeitamente formatado
header('Content-Type: application/json; charset=utf-8');
echo json_encode($responseUtf8);
