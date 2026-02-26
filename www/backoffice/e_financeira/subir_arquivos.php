<?php
// Configurações e Includes iniciais
set_time_limit(0); // Evita timeout caso envie muitos arquivos

require_once '/www/includes/constantes.php';
require_once __DIR__ . "/functions_e_financeira.php";

$mensagemHtml = '';

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivos_xml'])) {
    
    $efinanceira = new GerarEFinanceira();
    $arquivos = $_FILES['arquivos_xml'];
    
    $totalArquivos = count($arquivos['name']);
    $qtdSucesso = 0;
    $erros = [];

    // Loop passando por cada arquivo enviado
    for ($i = 0; $i < $totalArquivos; $i++) {
        
        // Pula se houver erro no upload desse arquivo específico
        if ($arquivos['error'][$i] !== UPLOAD_ERR_OK) {
            continue; 
        }

        $nomeArquivo = basename($arquivos['name'][$i]);
        $caminhoTemp = $arquivos['tmp_name'][$i];
        $conteudoXmlOriginal = file_get_contents($caminhoTemp);

        if (empty($conteudoXmlOriginal)) {
            $erros[] = "Conteúdo XML vazio para o arquivo: $nomeArquivo";
            continue;
        }

        try {
            // 1. Assinar o XML
            $lote_assinado = $efinanceira->assinarLoteEventos($conteudoXmlOriginal);

            // 2. Extrair os IDs do XML 
            // (Note que você chamou a função a partir da classe $efinanceira, 
            // então garanta que ela esteja criada dentro de GerarEFinanceira)
            $idsExtraidos = $efinanceira->extrairIdsDoXml($conteudoXmlOriginal);

            // 3. Atualizar no banco como PENDENTE e protocolo vazio
            if (!empty($idsExtraidos)) {
                $efinanceira->atualizarLoteStatus($idsExtraidos, "", $nomeArquivo, 'PENDENTE');
            }

            // 4. Salvar fisicamente na pasta de enviados
            $pathEnviados = '/www/arquivos_gerados/efinanceira/lotes_enviados';
            if (!is_dir($pathEnviados)) {
                mkdir($pathEnviados, 0755, true);
            }

            $caminhoFinal = $pathEnviados . '/' . $nomeArquivo;
            if (!file_exists($caminhoFinal)) {
                file_put_contents($caminhoFinal, $lote_assinado);
            }

            $qtdSucesso++;

        } catch (Exception $e) {
            $erros[] = "Falha no arquivo <strong>$nomeArquivo</strong>: " . $e->getMessage();
        }
    }

    // Monta o feedback visual
    if ($qtdSucesso > 0) {
        $mensagemHtml .= "<div class='alert alert-success'><strong>$qtdSucesso arquivo(s)</strong> processado(s), assinado(s) e registrado(s) como PENDENTE com sucesso!</div>";
    }
    
    if (!empty($erros)) {
        $mensagemHtml .= "<div class='alert alert-danger'><strong>Erros encontrados:</strong><ul>";
        foreach ($erros as $erro) {
            $mensagemHtml .= "<li>$erro</li>";
        }
        $mensagemHtml .= "</ul></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pré-carregamento de Lotes - E-Financeira</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container-upload {
            max-width: 600px;
            margin: 50px auto;
            background: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container container-upload">
    <h3 class="mb-4 text-center">Pré-carregar XMLs (Status Pendente)</h3>
    
    <?= $mensagemHtml ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="arquivos_xml"><strong>Selecione os Lotes (XML):</strong></label>
            <input type="file" name="arquivos_xml[]" id="arquivos_xml" class="form-control-file" accept=".xml" multiple required>
            <small class="form-text text-muted">Você pode selecionar múltiplos arquivos segurando CTRL ou arrastando o mouse.</small>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block mt-4">
            Assinar, Salvar e Atualizar BD
        </button>
    </form>
</div>

</body>
</html>