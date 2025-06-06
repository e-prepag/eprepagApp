<?php

$nomeFantasia = urldecode($_GET['nome']);

if (!$nomeFantasia) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>

    <head>
        <title>404 Not Found</title>
    </head>

    <body>
        <h1>Not Found</h1>
        <p>Imagem não encontrada.</p>
    </body>

    </html>
    <?php
    exit;
}

require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$stmt = $pdo->prepare("SELECT ug_logo, ug_estilo FROM dist_usuarios_games WHERE ug_nome_fantasia ilike ?");
$stmt->execute([$nomeFantasia]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && !empty($usuario['ug_logo'])) {
    // Detecta o MIME com base no estilo
    $estilos = json_decode($usuario['ug_estilo'], true);
    $ext = isset($estilos['logo_extensao']) ? strtolower($estilos['logo_extensao']) : 'png';
    $mime = ($ext === 'jpg') ? 'image/jpeg' : 'image/' . $ext;

    $logoRaw = is_resource($usuario['ug_logo']) 
                ? stream_get_contents($usuario['ug_logo']) 
                : $usuario['ug_logo'];

    header("Content-Type: $mime");
    echo $logoRaw;
    exit;
} else {
    http_response_code(404);
    ?>
    <!DOCTYPE html>

    <head>
        <title>404 Not Found</title>
    </head>

    <body>
        <h1>Not Found</h1>
        <p>Imagem não encontrada.</p>
    </body>

    </html>
    <?php
}
?>