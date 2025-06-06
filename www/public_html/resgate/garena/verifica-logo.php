<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
header('Content-Type: application/json');

$referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '';
$host_permitido = '' . EPREPAG_URL_HTTPS . '';

if (stripos($referer, $host_permitido) !== 0) {
    http_response_code(403);
    exit;
}

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

// Verifica se o parâmetro 'pin' foi enviado
if (!isset($_GET['pin'])) {
    echo json_encode(['erro' => 'PIN não fornecido']);
    exit;
}

$pin = $_GET['pin'];

// Conexão com o banco (exemplo com PDO)
try {
    $pdo = ConnectionPDO::getConnection()->getLink();

    $stmt = $pdo->prepare("
        SELECT vm.vgm_ogp_id 
        FROM tb_dist_venda_games_modelo vm
        JOIN tb_dist_venda_games_modelo_pins vp ON vm.vgm_id = vp.vgmp_vgm_id
        JOIN pins p ON vp.vgmp_pin_codinterno = p.pin_codinterno
        WHERE p.pin_codigo = :pin
        LIMIT 1
    ");

    $stmt->execute([':pin' => $pin]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        // Retorna o identificador do jogo
        $jogoId = $resultado['vgm_ogp_id'];
        $jogo = ($jogoId == 355) ? 'free_fire' : (($jogoId == 498) ? 'delta_force' : 'desconhecido');

        echo json_encode(['jogo' => $jogo]);
    } else {
        echo json_encode(['jogo' => 'desconhecido']);
    }
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro na conexão: ' . $e->getMessage()]);
}
?>