<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros

require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
require_once  '/www/includes/gamer/chave.php';
require_once  '/www/includes/gamer/AES.class.php';

class PinGenerator
{
    public $banks = [
        '0' => ['23456789', 8, 4],
        '1' => ['23456789abcdefghjkmnpqrstvwxyz', 16, 4],
        '2' => ['23456789ABCDEFGHIJKLMNPQRSTUVWXYZ', 16, 4],
        '3' => ['abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789', 16, 4],
        '4' => ['0123456789', 16, 4],
        '5' => ['0123456789', 14, 4],
        '6' => ['0123456789', 20, 4],
    ];

    public $sformato = '1';
    public $bank = '';
    public $nchars = 16;
    public $separador = 4;
    public $serial_length = 12;

    public function set_config($sformato)
    {
        $this->sformato = (string)$sformato;
        if (!isset($this->banks[$this->sformato])) {
            $this->sformato = '1';
        }
        $this->bank       = $this->banks[$this->sformato][0];
        $this->nchars     = (int)$this->banks[$this->sformato][1];
        $this->separador  = (int)$this->banks[$this->sformato][2];
    }

    public function gera_pin($sformato, $pin_valor)
    {
        $this->set_config($sformato);
        $return = "";
        $i = 0;
        while ($i < $this->nchars) {
            $char = substr($this->bank, mt_rand(0, strlen($this->bank) - 1), 1);
            if (!strstr($return, $char) || strlen($this->bank) < $this->nchars) {
                if (($i % $this->separador == 0) && ($i > 0)) {
                    $return .= "-";
                }
                $return .= $char;
                $i++;
            }
        }
        return $return;
    }
}

/*
 * Funções auxiliares
 */
function printFeedback($orig_pin, $spin_codigo, $vg_id_novo, $vg_id, $opr_nome, $nome_produto, $ug_nome, $pin_valor, $spin_serial, $status, $msg)
{
    echo "---------------------------------------------\n";
    echo "PIN Antigo      : $orig_pin\n";
    echo "PIN Novo        : $spin_codigo\n";
    echo "Pedido (vg_id) : $vg_id_novo\n";
    echo "Pedido Antigo (vg_id) : $vg_id\n";
    echo "Operadora       : $opr_nome\n";
    echo "Game       : $nome_produto\n";
    echo "Vendedor        : $ug_nome\n";
    echo "Valor do PIN    : $pin_valor\n";
    echo "Número de Série : $spin_serial\n";
    echo "Status          : $status\n";
    if ($msg) {
        echo "Mensagem        : $msg\n";
    }
    echo "---------------------------------------------\n\n";
}
function getFormato(PDO $pdo, $opr_codigo)
{
    $sql = "SELECT opr_pin_epp_formato FROM operadoras WHERE opr_codigo = :opr";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':opr' => $opr_codigo]);
    $row = $stmt->fetch();
    return $row ? $row['opr_pin_epp_formato'] : null;
}

function getNextLote(PDO $pdo, $opr_codigo)
{
    $sql = "SELECT max(pin_lote_codigo) AS max_pin_lote_codigo FROM pins WHERE opr_codigo = :opr";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':opr' => $opr_codigo]);
    $row = $stmt->fetch();
    return (!$row || $row['max_pin_lote_codigo'] === null) ? 1 : intval($row['max_pin_lote_codigo']) + 1;
}

function getNextSerial(PDO $pdo, $opr_codigo)
{
    $sql = "SELECT CAST(pin_serial AS BIGINT) AS max_serial
            FROM pins
            WHERE opr_codigo = :opr
            ORDER BY CAST(pin_serial AS BIGINT) DESC
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':opr' => $opr_codigo]);
    $row = $stmt->fetch();
    return ($row && $row['max_serial'] !== null) ? intval($row['max_serial']) : 1;
}

function existsPin(PDO $pdo, $spin_codigo, $opr_codigo)
{
    $sql = "SELECT 1 FROM pins WHERE pin_codigo = :pin AND opr_codigo = :opr LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pin' => $spin_codigo, ':opr' => $opr_codigo]);
    return (bool)$stmt->fetch();
}

$chave256bits = new Chave();
$aes = new AES($chave256bits->retornaChave());
try {
    $pdo = ConnectionPDO::getConnection()->getLink();
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Lista de PINs recebida externamente
$inputPins = [];

$placeholders = implode(',', array_fill(0, count($inputPins), '?'));

$sql = "SELECT 
        p.pin_codinterno,
        p.pin_codigo,
        p.pin_valor,
        vm.vgm_pin_valor,
        p.opr_codigo,
        p.pin_status,
        p.pin_datavenda,
        p.pin_horavenda,
        vm.vgm_id,
        vg.vg_id,
        o.opr_nome,
        ug.ug_nome_fantasia,
        vm.vgm_nome_produto,
        vm.vgm_nome_modelo,
        ug.ug_id,
        vm.vgm_nome_cpf,
        vm.vgm_cpf,
        vm.vgm_cpf_data_nascimento,
        vm.vgm_ogp_id,
        vm.vgm_ogpm_id,
        vm.vgm_perc_desconto
    FROM pins p
    JOIN tb_dist_venda_games_modelo_pins vp 
        ON vp.vgmp_pin_codinterno = p.pin_codinterno
    JOIN tb_dist_venda_games_modelo vm 
        ON vm.vgm_id = vp.vgmp_vgm_id
    JOIN tb_dist_venda_games vg 
        ON vg.vg_id = vm.vgm_vg_id 
    JOIN dist_usuarios_games ug 
        ON ug.ug_id = vg.vg_ug_id
    JOIN operadoras o 
        ON o.opr_codigo = p.opr_codigo
    WHERE p.pin_codigo IN ($placeholders) and p.pin_desc <> 'Substituido'
    ORDER BY vm.vgm_pin_valor ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($inputPins);
$pinsEncontrados = $stmt->fetchAll();

// CSV amigável
$data_atual = date('Ymd_His');
$csvFile =  "/www/arquivos_gerados/logs/novos_pins_gerados_$data_atual.csv";
$fp = fopen($csvFile, 'w');
fputcsv($fp, [
    'PIN Antigo',
    'PIN Novo',
    'Num Venda Novo',
    'Numero Venda',
    'Publisher',
    'Game',
    'PDV',
    'Valor do PIN',
    'Número de Série',
    'Status',
    'Mensagem'
]);

$generator = new PinGenerator();
$operadorasCache = [];

$pin_valor_anterior = 0;
$vgm_id_anterior = 0;

foreach ($pinsEncontrados as $row) {
    $orig_pin   = $row['pin_codigo'];
    $pin_valor  = $row['pin_valor'] == 0 ? $row['vgm_pin_valor'] : $row['pin_valor'];
    $opr_codigo = $row['opr_codigo'];
    $vgm_id     = $row['vgm_id'];
    $vg_id     = $row['vg_id'];
    $opr_nome   = $row['opr_nome'];
    $ug_nome    = $row['ug_nome_fantasia'];
    $nome_produto = $row['vgm_nome_produto'];
    $ug_id      = $row['ug_id'];
    $vgm_ogp_id = $row['vgm_ogp_id'];
    $vgm_ogpm_id = $row['vgm_ogpm_id'];
    $vgm_nome_cpf = $row['vgm_nome_cpf'];
    $vgm_cpf = $row['vgm_cpf'];
    $vgm_cpf_data_nascimento = $row['vgm_cpf_data_nascimento'];
    $vgm_nome_modelo = $row['vgm_nome_modelo'];
    $vgm_perc_desconto = $row['vgm_perc_desconto'];
    $pin_status = $row['pin_status'];
    $pin_codinterno_old = $row['pin_codinterno'];
    $pin_datavenda = $row['pin_datavenda'];
    $pin_horavenda = $row['pin_horavenda'];

    // Se ainda não tem cache da operadora, inicializa
    if (!isset($operadorasCache[$opr_codigo])) {
        $sformato   = getFormato($pdo, $opr_codigo);
        $ilote      = getNextLote($pdo, $opr_codigo);
        $pin_serial = getNextSerial($pdo, $opr_codigo);

        $operadorasCache[$opr_codigo] = [
            'sformato' => $sformato,
            'lote'     => $ilote,
            'serial'   => $pin_serial
        ];
    }

    // usa o cache
    $sformato   = $operadorasCache[$opr_codigo]['sformato'];
    $ilote      = $operadorasCache[$opr_codigo]['lote'];
    $pin_serial = ++$operadorasCache[$opr_codigo]['serial']; // incrementa serial

    $spin_serial = str_pad($pin_serial, $generator->serial_length, "0", STR_PAD_LEFT);
    $spin_codigo = str_replace('-', '', $generator->gera_pin($sformato, $pin_valor));

    if (existsPin($pdo, $spin_codigo, $opr_codigo)) {
        //Tenta gerar novamente
        $spin_codigo = str_replace('-', '', $generator->gera_pin($sformato, $pin_valor));
        if (existsPin($pdo, $spin_codigo, $opr_codigo)) {
            fputcsv($fp, [$orig_pin, '', $vg_id_novo, $vg_id, $opr_nome, $nome_produto, $ug_nome, $pin_valor, '', 'erro', 'PIN já existe']);
            printFeedback($orig_pin, '', $vg_id_novo, $vg_id, $opr_nome, $nome_produto, $ug_nome, $pin_valor, '', 'erro', 'PIN já existe');
            continue;
        }
    }

    try {
        $pdo->beginTransaction();

        $hora_atual = date('H:i:s');
        // Inserção no pins com pin_desc = pin antigo
        $sqlPins = "
            INSERT INTO pins (
                pin_serial, pin_codigo, opr_codigo, pin_valor, pin_lote_codigo,
                pin_dataentrada, pin_canal, pin_horaentrada, pin_status,
                pin_validade, pin_est_codigo, pin_datavenda, pin_horavenda,
                pin_datapedido, pin_horapedido, pin_desc
            ) VALUES (
                :serial, :codigo, :opr, :valor, :lote,
                CURRENT_TIMESTAMP, 's', NOW(), '6',
                (NOW() + interval '6 month'), 1, CURRENT_DATE, '$hora_atual',
                CURRENT_DATE, '$hora_atual', :desc
            )
            RETURNING pin_codinterno
        ";
        $stmtPins = $pdo->prepare($sqlPins);
        $stmtPins->execute([
            ':serial' => $spin_serial,
            ':codigo' => $spin_codigo,
            ':opr'    => $opr_codigo,
            ':valor'  => $pin_valor,
            ':lote'   => $ilote,
            ':desc'   => ("Pin antigo: " . $orig_pin),
        ]);
        $novoPinRow = $stmtPins->fetch();
        $pin_codinterno = $novoPinRow['pin_codinterno'];

        $status = 'ok (PIN antigo marcado como substituído)';

        if ($pin_status == '6') {
            $query6 = "UPDATE pins SET pin_desc = 'Substituido', pin_status = '9', pin_valor = 0 WHERE pin_codigo = ?";
            $pdo->prepare($query6)->execute([$orig_pin]);

            $query7 = "UPDATE tb_dist_venda_games_modelo SET vgm_qtde = GREATEST(vgm_qtde - 1, 0) WHERE vgm_id = ?";
            $pdo->prepare($query7)->execute([$vgm_id]);

            $query9 = "DELETE FROM pins_dist WHERE pin_codigo = ?";
            $pdo->prepare($query9)->execute([$orig_pin]);

            $query10 = "DELETE FROM tb_dist_venda_games_modelo_pins WHERE vgmp_pin_codinterno = ?";
            $pdo->prepare($query10)->execute([$pin_codinterno_old]);

            $status = 'ok (PIN antigo cancelado)';
        } else if ($pin_status == '9') {

            $query6 = "UPDATE pins SET pin_desc = 'Substituido', pin_valor = 0 WHERE pin_codigo = ?";
            $pdo->prepare($query6)->execute([$orig_pin]);

            $query9 = "DELETE FROM pins_dist WHERE pin_codigo = ?";
            $pdo->prepare($query9)->execute([$orig_pin]);

            $query10 = "DELETE FROM tb_dist_venda_games_modelo_pins WHERE vgmp_pin_codinterno = ?";
            $pdo->prepare($query10)->execute([$pin_codinterno_old]);

            $status = 'ok (PIN antigo ja estava cancelado)';
        }

        if ($pin_valor != $pin_valor_anterior) {
            // venda
            $query1 = "INSERT INTO tb_dist_venda_games (vg_ug_id, vg_data_inclusao, vg_pagto_tipo, vg_ultimo_status, vg_id) VALUES (?, CURRENT_TIMESTAMP, 2, 5, (select (max(vg_id) + 1) from tb_dist_venda_games)) RETURNING vg_id";
            $stmtVenda = $pdo->prepare($query1);
            $stmtVenda->execute([$ug_id]);
            $novaVendaRow = $stmtVenda->fetch();
            $vg_id_novo = $novaVendaRow['vg_id'];

            // venda modelo
            $query2 = "INSERT INTO tb_dist_venda_games_modelo (vgm_vg_id, vgm_nome_produto, vgm_nome_modelo, vgm_valor, vgm_qtde, vgm_ogp_id, vgm_ogpm_id, vgm_opr_codigo, vgm_pin_valor, vgm_perc_desconto, vgm_cpf, vgm_cpf_data_nascimento, vgm_nome_cpf) VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING vgm_id";
            $stmtVendaModelo = $pdo->prepare($query2);
            $stmtVendaModelo->execute([$vg_id_novo, $nome_produto, $vgm_nome_modelo, $pin_valor, 1, $vgm_ogp_id, $vgm_ogpm_id, $opr_codigo, $pin_valor, $vgm_perc_desconto, $vgm_cpf, $vgm_cpf_data_nascimento, $vgm_nome_cpf]);
            $novaVendaModeloRow = $stmtVendaModelo->fetch();
            $vgm_id_novo = $novaVendaModeloRow['vgm_id'];
        } else {
            $query1 = "UPDATE tb_dist_venda_games_modelo SET vgm_qtde = vgm_qtde + 1 WHERE vgm_id = ?";
            $stmtVenda = $pdo->prepare($query1);
            $stmtVenda->execute([$vgm_id_anterior]);
            $novaVendaRow = $stmtVenda->fetch();
            $vgm_id_novo = $vgm_id_anterior;
        }
        // associa ao vgm_id
        $query3 = "INSERT INTO tb_dist_venda_games_modelo_pins (vgmp_vgm_id, vgmp_pin_codinterno) VALUES (?, ?)";
        $pdo->prepare($query3)->execute([$vgm_id_novo, $pin_codinterno]);

        // copia para pins_dist
        $query4 = "INSERT INTO pins_dist SELECT * FROM pins WHERE pin_codinterno = ?";
        $pdo->prepare($query4)->execute([$pin_codinterno]);

        $query6 = "UPDATE pins SET pin_desc = 'Substituido' WHERE pin_codigo = ?";
        $pdo->prepare($query6)->execute([$orig_pin]);

        $pdo->commit();

        $pin_valor_anterior = $pin_valor;
        $vgm_id_anterior = $vgm_id_novo;

        printFeedback($orig_pin, $spin_codigo, $vg_id_novo, $vg_id, $opr_nome, $nome_produto, $ug_nome, $pin_valor, $spin_serial, $status, '');
        fputcsv($fp, [$orig_pin, $spin_codigo, $vg_id_novo, $vg_id, $opr_nome, $nome_produto, $ug_nome, $pin_valor, $spin_serial, $status, '']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        fputcsv($fp, [$orig_pin, '', $vg_id_novo, $vg_id, $opr_nome, $nome_produto, $ug_nome, $pin_valor, '', 'erro', $e->getMessage()]);
        printFeedback($orig_pin, '', $vg_id_novo, $vg_id, $opr_nome, $nome_produto, $ug_nome, $pin_valor, '', 'erro', $e->getMessage());
    }
}

fclose($fp);
echo "CSV gerado em $csvFile\n";
