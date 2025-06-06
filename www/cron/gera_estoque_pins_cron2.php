<?php 

// Livrodjx did it right

header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php";
require_once '/www/includes/gamer/inc_sanitize.php'; 
require_once '/www/includes/gamer/chave.php';
require_once '/www/includes/gamer/AES.class.php';
$connection = ConnectionPDO::getConnection()->getLink(); 

$sql = "SELECT ogp_id, ogp_nome, ogpm_nome, ogpm_descricao, opr_nome, ogp_opr_codigo AS opr_codigo, 
               ogpm_valor, opr_pin_epp_formato AS formato, ogpm_pin_valor AS pin_valor_final, 
               COUNT(pins.pin_valor) AS quantidade 
        FROM tb_operadora_games_produto 
        INNER JOIN tb_operadora_games_produto_modelo ON ogpm_ogp_id = ogp_id
        INNER JOIN operadoras ON opr_codigo = ogp_opr_codigo
        INNER JOIN pins ON pin_valor = ogpm_pin_valor 
                      AND pins.pin_status = '1' 
                      AND pins.opr_codigo = ogp_opr_codigo 
        WHERE ogp_ativo = 1 
          AND opr_status = '1' 
          AND opr_pin_epp_formato IS NOT NULL 
          AND ogpm_ativo = 1 
        GROUP BY ogp_id, ogp_nome, ogpm_nome, ogpm_descricao, ogpm_valor, 
                 ogp_opr_codigo, ogpm_pin_valor, opr_pin_epp_formato, opr_nome
        HAVING COUNT(pins.pin_valor) <= 100;";

$query = $connection->prepare($sql);
$query->execute();
$ret = $query->fetchAll(PDO::FETCH_ASSOC);

// Valores globais e pré-calculados
$nchars = 20;
$separador = 4;
$serial_length = 10;
$chars = '0123456789';

function saveLog($pins, $lote, $operadora, $valor) {
    try {
        $file = fopen("/www/log/cron_estoque_pins.txt", "a+");
        fwrite($file, str_repeat("*", 50)."\n");
        fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
        fwrite($file, "OPERADORA: ".$operadora. "\n");
        fwrite($file, "VALOR DO PIN: ".$valor."\n");
        fwrite($file, "PIN(S): ".$pins."\n");
        fwrite($file, "LOTE: ".$lote."\n");
        fwrite($file, str_repeat("*", 50)."\n");
        fclose($file);
    } catch (Exception $e) {
        echo "Error(6) writing monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage().PHP_EOL;
    }
}

function gera_pin($chars, $nchars, $separador) {
    $return = '';

    for ($i = 0; $i < $nchars; $i++) {
        if ($i > 0 && $i % $separador == 0) {
            $return .= '-';
        }
        $return .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    return $return;
}

if (count($ret) > 0) {

    // Prepara a consulta que será reutilizada no loop
    $max_lote_query = $connection->prepare("SELECT max(pin_lote_codigo) as max_pin_lote_codigo FROM pins WHERE opr_codigo = :opr_codigo;");
    $max_serial_query = $connection->prepare("SELECT CAST(pin_serial AS BIGINT) as max_serial FROM pins WHERE opr_codigo = :opr_codigo ORDER BY CAST(pin_serial AS BIGINT) DESC LIMIT 1;");
    $check_pin_query = $connection->prepare("SELECT * FROM pins WHERE pin_codigo = :spin_codigo AND opr_codigo = :opr_codigo;");
    $check_store_query = $connection->prepare("SELECT * FROM pins_store WHERE pin_codigo = :encoded_pin;");

    // Instanciando as classes de criptografia fora do loop
    $chave256bits = new Chave();
    $aesPub = new AES($chave256bits->retornaChavePub());
    $aes = new AES($chave256bits->retornaChave());

    foreach ($ret as $key => $value) {

        if ($value["quantidade"] <= 100) { 

            // Executa a consulta para obter o lote
            $max_lote_query->bindValue(':opr_codigo', $value["opr_codigo"]);
            $max_lote_query->execute();
            $rs_lote = $max_lote_query->fetch(PDO::FETCH_ASSOC);
            if (!$rs_lote) $rs_lote = ["max_pin_lote_codigo" => 1];

            // Executa a consulta para obter o serial
            $max_serial_query->bindValue(':opr_codigo', $value["opr_codigo"]);
            $max_serial_query->execute();
            $rs_serial = $max_serial_query->fetch(PDO::FETCH_ASSOC);
            if (!$rs_serial) $rs_serial = ["max_serial" => 1];

            $i = 1;
            $pins_final = "";

            while ($i <= 50) {

                $spin_codigo = gera_pin($chars, $nchars, $separador);
                $spin_codigo = str_replace("-", "", $spin_codigo);

                $check_pin_query->bindValue(':spin_codigo', $spin_codigo);
                $check_pin_query->bindValue(':opr_codigo', $value["opr_codigo"]);
                $check_pin_query->execute();
                $rs_pins = $check_pin_query->fetchAll(PDO::FETCH_ASSOC);

                if (count($rs_pins) == 0) {
                    $encoded_pin = base64_encode($aes->encrypt($spin_codigo));
                    $check_store_query->bindValue(':encoded_pin', $encoded_pin);
                    $check_store_query->execute();
                    $rs_pins_store = $check_store_query->fetchAll(PDO::FETCH_ASSOC);

                    if (count($rs_pins_store) == 0) {
                        $spin_serial = str_pad(number_format($rs_serial["max_serial"]++, 0, '', ''), $serial_length, "0", STR_PAD_LEFT);

                        $sql_insert = "INSERT INTO pins (pin_serial, pin_codigo, opr_codigo, pin_valor, pin_lote_codigo, pin_dataentrada, pin_canal, pin_horaentrada, pin_status, pin_validade)
                                       VALUES (:spin_serial, :spin_codigo, :opr_codigo, :pin_valor, :pin_lote_codigo, CURRENT_TIMESTAMP, 's', NOW(), '1', (NOW() + interval '6 month'));";
                        $insert_query = $connection->prepare($sql_insert);
                        $insert_query->bindValue(':spin_serial', $spin_serial);
                        $insert_query->bindValue(':spin_codigo', $spin_codigo);
                        $insert_query->bindValue(':opr_codigo', $value["opr_codigo"]);
                        $insert_query->bindValue(':pin_valor', $value["ogpm_valor"]);
                        $insert_query->bindValue(':pin_lote_codigo', $rs_lote["max_pin_lote_codigo"]);
                        $insert_query->execute();

                        $rowCount = $insert_query->rowCount();

                        if ($rowCount == 0) {
                            die();
                        }

                        $pins_final .= $spin_codigo . ", ";
                        $i++;
                    }
                }
            }

            saveLog($pins_final, $rs_lote["max_pin_lote_codigo"], $value["opr_codigo"], $value["ogpm_valor"]);
        }
    }
}
?>
